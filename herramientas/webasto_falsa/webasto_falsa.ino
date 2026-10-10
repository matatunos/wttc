// webasto_falsa.ino — Un segundo ESP32 que se hace pasar por una Webasto Thermo Top C en el W-Bus, para probar WTTC
// en la mesa, sin furgoneta. Código generado íntegramente con Claude (Anthropic). Licencia GPL v3 o posterior.
//
// El modelo (fases de arranque, llama, regulación, postbarrido, averías, bloqueo) está en webasto_modelo.h: es el mismo
// que el simulador de la web y se prueba en GitHub junto con el firmware de WTTC (firmware/pruebas/webasto_test.cpp).
//
// CONEXIÓN (ver README.md de esta carpeta):
//   A) Directa, sin transceptores (lo más fácil): ESP32 falso TX (WF_TX) → WTTC IO16 · ESP32 falso RX (WF_RX) ← WTTC IO17
//      · GND con GND. Quita antes la placa TJA1020 de WTTC (o desconecta sus TX/RX). WF_ECO = true: en un bus de un solo
//      hilo cada aparato oye lo que manda, y WTTC lo espera; aquí lo devuelve el falso.
//   B) Por el bus de verdad: un segundo TJA1020 en el falso (su TX/RX a WF_TX/WF_RX, SLP a 3V3) y los dos LIN unidos,
//      con 12 V y masa comunes. WF_ECO = false (el eco ya lo da el bus).
//
// CONSOLA (USB, 115200 baudios; «ayuda» las lista):
//   estado · fuera <°C> · bateria <V> · fallo gasoil|llama|corriente|mudo|ninguno · borrar · rapido <x1..x60> ·
//   traza on|off · reiniciar
//
// Compila para cualquier ESP32 (el clásico o el S3) con el núcleo ESP32 3.x de Arduino. Ajusta WF_RX/WF_TX a tu placa.
#include "webasto_modelo.h"

// ---------- configuración ----------
#define WF_RX 16                // pin por el que llega lo que manda WTTC
#define WF_TX 17                // pin por el que contesta la Webasto falsa
const bool WF_ECO = true;       // conexión directa (A): devolver el eco; por el bus (B): false
const uint32_t RESP_DELAY_MS = 25;   // lo que tarda una Webasto en contestar (aproximado)

HardwareSerial bus(1);
wf::Webasto w;
wf::Reader rd;
uint32_t speed = 1;             // tiempo acelerado (×1 … ×60): las fases y el agua van más deprisa
bool trace = true;              // escribir cada trama en la consola
uint32_t lastTick = 0, lastState = 0;
wf::St lastSt = wf::OFF;

String hex(const uint8_t* b, int n) {
  String s; char t[4];
  for (int i = 0; i < n; i++) { snprintf(t, sizeof t, "%02X ", b[i]); s += t; }
  s.trim();
  return s;
}

void printState() {
  Serial.printf("[%s] agua %.1f °C · dentro %.1f °C · %.2f V · llama %d · %u W · orden %s · quedan %ld s · averías %d%s%s%s%s · ×%lu\n",
                wf::ST_NAME[w.st], w.temp, w.cab, w.volt(), w.flame(), (unsigned)w.pw, w.cmd ? "sí" : "no",
                (long)(w.runLeft > 0 && w.cmd ? w.runLeft / 1000 : 0), w.errN,
                w.env.noFuel ? " · SIN GASOIL" : "", w.env.flameOut ? " · SE APAGARÁ LA LLAMA" : "",
                w.env.noPower ? " · SIN CORRIENTE" : "", w.env.mute ? " · BUS MUDO" : "", (unsigned long)speed);
}

void help() {
  Serial.println("Órdenes: estado | fuera <°C> | bateria <V> | fallo gasoil|llama|corriente|mudo|ninguno | borrar |");
  Serial.println("         rapido <1..60> | traza on|off | reiniciar");
}

void console() {
  if (!Serial.available()) return;
  String l = Serial.readStringUntil('\n');
  l.trim(); l.toLowerCase();
  if (!l.length()) return;
  int sp = l.indexOf(' ');
  String k = sp < 0 ? l : l.substring(0, sp), a = sp < 0 ? String("") : l.substring(sp + 1);
  if (k == "estado") printState();
  else if (k == "fuera") { w.env.amb = a.toFloat(); Serial.printf("Fuera: %.1f °C\n", w.env.amb); }
  else if (k == "bateria") { float v = a.toFloat(); if (v >= 8 && v <= 15) { w.env.batt = v; Serial.printf("Batería: %.2f V\n", v); } else Serial.println("Entre 8 y 15 V"); }
  else if (k == "fallo") {
    if (a == "gasoil") w.env.noFuel = true;
    else if (a == "llama") w.env.flameOut = true;
    else if (a == "corriente") w.env.noPower = true;
    else if (a == "mudo") w.env.mute = true;
    else if (a == "ninguno") w.env.noFuel = w.env.flameOut = w.env.noPower = w.env.mute = false;
    else { Serial.println("fallo gasoil|llama|corriente|mudo|ninguno"); return; }
    printState();
  }
  else if (k == "borrar") { w.errN = 0; if (w.st == wf::LOCK) w.set(wf::OFF); w.fails = 0; Serial.println("Averías borradas (y desbloqueada)"); }
  else if (k == "rapido") { long x = a.toInt(); if (x >= 1 && x <= 60) { speed = x; Serial.printf("Tiempo ×%ld\n", x); } else Serial.println("Entre 1 y 60"); }
  else if (k == "traza") { trace = a != "off"; Serial.printf("Traza %s\n", trace ? "activada" : "desactivada"); }
  else if (k == "reiniciar") { ESP.restart(); }
  else help();
}

// Llega una trama buena de WTTC: (eco) + respuesta, si la Webasto falsa contesta a eso
void answer() {
  uint8_t out[64], resp[64];
  int n = w.handle(rd.cmd(), rd.data(), rd.dataLen(), out);
  int rl = n >= 0 ? wf::reply(resp, sizeof resp, rd.cmd(), out, n) : -1;
  if (trace) Serial.printf("← %s%s\n", hex(rd.b, rd.frameLen()).c_str(), rl < 0 ? "  (sin respuesta)" : "");
  if (WF_ECO) bus.write(rd.b, rd.frameLen());    // el eco que daría el bus de un solo hilo
  if (rl > 0) {
    bus.flush();
    delay(RESP_DELAY_MS);
    bus.write(resp, rl);
    if (trace) Serial.printf("→ %s\n", hex(resp, rl).c_str());
  }
  rd.reset();
}

void setup() {
  Serial.begin(115200);
  Serial.setTimeout(200);
  bus.begin(2400, SERIAL_8E1, WF_RX, WF_TX);
  w.temp = w.cab = w.env.amb;
  Serial.println();
  Serial.println("=== Webasto falsa (W-Bus, 2400 8E1) para probar WTTC ===");
  Serial.printf("RX %d · TX %d · eco %s\n", WF_RX, WF_TX, WF_ECO ? "sí (conexión directa)" : "no (por el bus)");
  help();
  lastTick = millis();
}

void loop() {
  uint32_t now = millis(), dt = now - lastTick;
  lastTick = now;
  w.tick(dt * speed);
  while (bus.available()) {
    uint8_t c = bus.read();
    if (w.env.noPower || w.env.mute) continue;    // sin corriente o con el cable suelto: no contesta nada
    if (rd.feed(c)) answer();
  }
  if (w.st != lastSt) { Serial.printf("· %s\n", wf::ST_NAME[w.st]); lastSt = w.st; }
  if (now - lastState > 30000) { lastState = now; printState(); }
  console();
  delay(2);
}
