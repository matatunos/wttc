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
// CONSOLA (USB, 115200 baudios; «ayuda» las lista con su explicación):
//   estado · resumen · fuera <°C> · bateria <V> · fallo gasoil|llama|corriente|mudo|ninguno · borrar · rapido <1..60> ·
//   log 0..3 · traza on|off · eco si|no · pines <rx> <tx> · reiniciar
//
// REGISTRO: cada línea lleva la hora desde que arrancó [hh:mm:ss.mmm] (y la simulada si el tiempo va acelerado) y una
// marca: «<» lo que manda WTTC (traducido), «>» lo que contesta, «.» lo que hace la caldera y por qué (webasto_modelo.h),
// «!» avisos (averías, tramas malas, falta el mantenimiento, no llega nada), «$» lo escrito en la consola, «=» estado.
// Cuánto se escribe: «log 0» solo lo importante, «log 1» (de fábrica) además cada orden y su respuesta, «log 2» además
// los bytes y el ruido del bus, «log 3» además el estado cada 10 s. Se queda guardado.
//
// Compila para cualquier ESP32 (el clásico o el S3) con el núcleo ESP32 3.x de Arduino. También se instala desde el
// navegador, ya compilada (wttc.favala.es/instalar.php): por eso el eco y los pines se cambian desde la consola
// («eco no» para la conexión B, «pines 16 17») y se quedan guardados; WF_RX, WF_TX y WF_ECO son solo los de fábrica.
#include <Preferences.h>
#include "webasto_modelo.h"

// ---------- configuración ----------
#define WF_RX 16                // pin por el que llega lo que manda WTTC
#define WF_TX 17                // pin por el que contesta la Webasto falsa
const bool WF_ECO = true;       // conexión directa (A): devolver el eco; por el bus (B): false
// Lo que se usa de verdad: lo de arriba o lo guardado desde la consola (eco, pines, nivel del registro)
bool eco = WF_ECO;
int pinRx = WF_RX, pinTx = WF_TX;
int logLvl = 1;                 // 0 importante · 1 + órdenes y respuestas · 2 + bytes y ruido · 3 + estado cada 10 s
Preferences prefs;
const uint32_t RESP_DELAY_MS = 25;   // lo que tarda una Webasto en contestar (aproximado)

HardwareSerial bus(1);
wf::Webasto w;
wf::Reader rd;
uint32_t speed = 1;             // tiempo acelerado (×1 … ×60): las fases y el agua van más deprisa
uint64_t simMs = 0;             // tiempo simulado (con la aceleración)
uint32_t lastTick = 0, lastState = 0;

// Contadores para «resumen» y para los avisos
struct {
  uint32_t frames = 0, answered = 0, silent = 0, bad = 0, noise = 0, ignoredOff = 0;
  uint32_t lastFrameAt = 0, lastKaAt = 0;
} st;
uint32_t noiseRun = 0;          // bytes sueltos antes de la trama en curso (el «despertar» del bus o ruido)
bool kaWarned = false, quietWarned = false;
uint32_t ownEchoUntil = 0;       // por el bus (B) oye su propia respuesta: esos bytes no son de WTTC

void savePrefs() {
  prefs.begin("wfalsa", false);
  prefs.putBool("eco", eco); prefs.putInt("rx", pinRx); prefs.putInt("tx", pinTx); prefs.putInt("log", logLvl);
  prefs.end();
}

// ---------- registro ----------
String stamp() {
  char b[40]; uint32_t ms = millis();
  int n = snprintf(b, sizeof b, "[%02lu:%02lu:%02lu.%03lu", (unsigned long)(ms / 3600000), (unsigned long)(ms / 60000 % 60),
                   (unsigned long)(ms / 1000 % 60), (unsigned long)(ms % 1000));
  if (speed > 1) {
    uint64_t s = simMs / 1000;
    snprintf(b + n, sizeof b - n, " · sim %02lu:%02lu:%02lu", (unsigned long)(s / 3600), (unsigned long)(s / 60 % 60), (unsigned long)(s % 60));
  }
  return String(b) + "] ";
}
void lg(char mark, const char* fmt, ...) {
  char m[240]; va_list a; va_start(a, fmt); vsnprintf(m, sizeof m, fmt, a); va_end(a);
  Serial.print(stamp()); Serial.print(mark); Serial.print(' '); Serial.println(m);
}
void modelLog(const char* m) { lg(m[0] == 'A' ? '!' : '.', "%s", m); }   // las del modelo; «AVERÍA…» como aviso

String hex(const uint8_t* b, int n) {
  String s; char t[4];
  for (int i = 0; i < n; i++) { snprintf(t, sizeof t, "%02X ", b[i]); s += t; }
  s.trim();
  return s;
}
String secs(uint32_t ms) { char b[16]; snprintf(b, sizeof b, "%.1f s", ms / 1000.0f); return String(b); }

// Qué pide WTTC, en texto
String asks(uint8_t c, const uint8_t* d, int n) {
  char b[90];
  switch (c) {
    case 0x21: snprintf(b, sizeof b, "encender %d min", n ? d[0] : 0); break;
    case 0x10: snprintf(b, sizeof b, "apagar"); break;
    case 0x44: {
      uint32_t ago = st.lastKaAt ? millis() - st.lastKaAt : 0;
      snprintf(b, sizeof b, "mantenimiento: «sigue con la orden 0x%02X»%s%s", n ? d[0] : 0, ago ? " · el anterior hace " : "", ago ? secs(ago).c_str() : "");
      break;
    }
    case 0x50: snprintf(b, sizeof b, n && d[0] == 0x05 ? "pide los sensores (0x50 05)" : "pide el registro 0x50 %02X", n ? d[0] : 0); break;
    case 0x51: snprintf(b, sizeof b, "pide la identificación 0x51 %02X", n ? d[0] : 0); break;
    case 0x56:
      if (n && d[0] == 0x01) snprintf(b, sizeof b, "pide la lista de averías");
      else if (n && d[0] == 0x02) snprintf(b, sizeof b, "pide el detalle de la avería 0x%02X", n > 1 ? d[1] : 0);
      else if (n && d[0] == 0x03) snprintf(b, sizeof b, "BORRA las averías");
      else snprintf(b, sizeof b, "averías 0x56 %02X", n ? d[0] : 0);
      break;
    case 0x45: snprintf(b, sizeof b, "prueba de componente 0x45 (WTTC no debería mandarla sola)"); break;
    default: snprintf(b, sizeof b, "orden 0x%02X", c);
  }
  return b;
}

// Qué le contesta, en texto
String says(uint8_t c, const uint8_t* d, int n) {
  char b[160];
  if (c == 0x21) snprintf(b, sizeof b, "«vale, %d min»%s", n ? d[0] : 0, w.cmd && wf::active(w.st) ? "" : " (pero no ha arrancado: ver arriba)");
  else if (c == 0x10) snprintf(b, sizeof b, "«apagando» → %s", wf::ST_NAME[w.st]);
  else if (c == 0x44) snprintf(b, sizeof b, n && d[0] == 0 ? "«sigo con la orden»" : "«no tengo esa orden» (WTTC entenderá que se ha apagado)");
  else if (c == 0x50 && n >= 9 && d[0] == 0x05)
    snprintf(b, sizeof b, "agua %d °C · %.2f V · llama %s · %d W · bujía %.2f Ω", d[1] - 50, ((d[2] << 8) | d[3]) / 1000.0f,
             d[4] ? "sí" : "no", (d[5] << 8) | d[6], ((d[7] << 8) | d[8]) / 1000.0f);
  else if (c == 0x56 && n >= 2 && d[0] == 0x01) {
    if (!d[1]) snprintf(b, sizeof b, "ninguna avería guardada");
    else {
      int k = snprintf(b, sizeof b, "%d averías:", d[1]);
      for (int i = 0; i < d[1] && 3 + 2 * i < n && k < (int)sizeof b - 20; i++)
        k += snprintf(b + k, sizeof b - k, " 0x%02X×%d", d[2 + 2 * i], d[3 + 2 * i]);
    }
  }
  else if (c == 0x56 && n && d[0] == 0x03) snprintf(b, sizeof b, "averías borradas");
  else snprintf(b, sizeof b, "%d bytes de datos", n);
  return b;
}

void printState() {
  const char mark = '=';
  String e;
  for (int i = 0; i < w.errN; i++) { char t[16]; snprintf(t, sizeof t, " 0x%02X×%d", w.errCode[i], w.errCnt[i]); e += t; }
  lg(mark, "%s · agua %.1f °C · dentro %.1f °C · fuera %.1f °C · %.2f V · llama %s · %u W", wf::ST_NAME[w.st], w.temp, w.cab,
       w.env.amb, w.volt(), w.flame() ? "sí" : "no", (unsigned)w.pw);
  // Apagándose (postbarrido) la orden sigue vigente pero ya no hace falta mantenerla: el tiempo desde el último
  // mantenimiento deja de contar, y enseñarlo parecía decir que WTTC lo seguía mandando (prueba del 18/10/2026)
  if (w.cmd && !wf::active(w.st)) lg(mark, "  orden 0x21: apagándose, ya no hace falta mantenimiento (se suelta al acabar el postbarrido)");
  else if (w.cmd) lg(mark, "  orden 0x21: quedan %ld s · último mantenimiento hace %s (a los 20 s se apaga)",
                  (long)(w.runLeft > 0 ? w.runLeft / 1000 : 0), secs(w.refresh).c_str());
  else lg(mark, "  sin orden de calentar");
  lg(mark, "  averías:%s · intentos en este arranque %d · arranques fallidos seguidos %d de 3", w.errN ? e.c_str() : " ninguna", w.tries, w.fails);
  String f;
  if (w.env.noFuel) f += " SIN GASOIL";
  if (w.env.flameOut) f += " SE APAGARÁ LA LLAMA";
  if (w.env.noPower) f += " SIN CORRIENTE";
  if (w.env.mute) f += " BUS MUDO";
  lg(mark, "  fallos provocados:%s · tiempo ×%lu · eco %s · RX %d TX %d · log %d", f.length() ? f.c_str() : " ninguno",
       (unsigned long)speed, eco ? "sí" : "no", pinRx, pinTx, logLvl);
}

void summary() {
  uint32_t ago = st.lastFrameAt ? millis() - st.lastFrameAt : 0;
  lg('=', "tramas de WTTC: %lu · contestadas %lu · sin contestar %lu · ignoradas (sin corriente/mudo) %lu",
       (unsigned long)st.frames, (unsigned long)st.answered, (unsigned long)st.silent, (unsigned long)st.ignoredOff);
  lg('=', "tramas malas (checksum o longitud) %lu · bytes sueltos %lu · arranques %lu · la última trama hace %s",
       (unsigned long)st.bad, (unsigned long)st.noise, (unsigned long)w.starts, st.lastFrameAt ? secs(ago).c_str() : "(ninguna)");
  printState();
}

void help() {
  Serial.println(F("Órdenes de la consola:"));
  Serial.println(F("  estado            todo lo que tiene ahora: estado, agua, batería, orden, mantenimiento, averías, fallos"));
  Serial.println(F("  resumen           cuántas tramas han llegado, contestado, malas… y el estado"));
  Serial.println(F("  fuera <°C>        temperatura de fuera (cuánto tarda en calentar y en enfriarse)"));
  Serial.println(F("  bateria <V>       tensión de la batería sin carga (8–15 V)"));
  Serial.println(F("  fallo gasoil      no prende: avería 02 cada arranque; al tercero seguido, bloqueada (07)"));
  Serial.println(F("  fallo llama       se le apaga la llama calentando: avería 03"));
  Serial.println(F("  fallo corriente   sin corriente (fusible): no contesta a nada"));
  Serial.println(F("  fallo mudo        el W-Bus no contesta (cable suelto)"));
  Serial.println(F("  fallo ninguno     quita los fallos provocados"));
  Serial.println(F("  borrar            borra las averías y la desbloquea"));
  Serial.println(F("  rapido <1..60>    el tiempo va N veces más deprisa"));
  Serial.println(F("  log <0..3>        0 importante · 1 + órdenes (de fábrica) · 2 + bytes · 3 + estado cada 10 s"));
  Serial.println(F("  traza on|off      atajo: log 2 / log 0"));
  Serial.println(F("  eco si|no         sí: conexión directa (A) · no: por el bus con TJA1020 (B)"));
  Serial.println(F("  pines <rx> <tx>   pines del W-Bus (de fábrica 16 17)"));
  Serial.println(F("  reiniciar"));
}

// Las letras se juntan hasta el Enter (wf::lineaFeed): vale el Monitor serie de Arduino y PuTTY o screen, que mandan
// cada tecla al pulsarla
void console() {
  static wf::LineaSerie lin = {};
  bool lista = false;
  while (!lista && Serial.available()) lista = wf::lineaFeed(lin, (char)Serial.read());
  if (!lista) return;
  String l = lin.buf;
  l.trim(); l.toLowerCase();
  if (!l.length()) return;
  lg('$', "%s", l.c_str());
  int sp = l.indexOf(' ');
  String k = sp < 0 ? l : l.substring(0, sp), a = sp < 0 ? String("") : l.substring(sp + 1);
  if (k == "estado") printState();
  else if (k == "resumen") summary();
  else if (k == "fuera") { w.env.amb = a.toFloat(); lg('.', "fuera: %.1f °C", w.env.amb); }
  else if (k == "bateria") { float v = a.toFloat(); if (v >= 8 && v <= 15) { w.env.batt = v; lg('.', "batería: %.2f V (en reposo; calentando baja con el consumo)", v); } else lg('!', "la batería, entre 8 y 15 V"); }
  else if (k == "fallo") {
    if (a == "gasoil") { w.env.noFuel = true; lg('!', "fallo provocado: SIN GASOIL (no prenderá; a los 3 arranques, bloqueada)"); }
    else if (a == "llama") { w.env.flameOut = true; lg('!', "fallo provocado: se le APAGARÁ LA LLAMA en cuanto la tenga"); }
    else if (a == "corriente") { w.env.noPower = true; lg('!', "fallo provocado: SIN CORRIENTE (no contesta; se apaga y pierde la orden)"); }
    else if (a == "mudo") { w.env.mute = true; lg('!', "fallo provocado: BUS MUDO (oye pero no contesta; la caldera sigue a lo suyo)"); }
    else if (a == "ninguno") { w.env.noFuel = w.env.flameOut = w.env.noPower = w.env.mute = false; lg('.', "sin fallos provocados"); }
    else { lg('!', "fallo gasoil|llama|corriente|mudo|ninguno"); return; }
  }
  else if (k == "borrar") { w.errN = 0; w.fails = 0; if (w.st == wf::LOCK) w.set(wf::OFF, "averías borradas desde la consola"); lg('.', "averías borradas (y desbloqueada)"); }
  else if (k == "rapido") { long x = a.toInt(); if (x >= 1 && x <= 60) { speed = x; lg('.', "tiempo ×%ld", x); } else lg('!', "rapido entre 1 y 60"); }
  else if (k == "log" || k == "traza") {
    int n = k == "traza" ? (a == "off" ? 0 : 2) : a.toInt();
    if (n < 0 || n > 3 || (k == "log" && !a.length())) { lg('!', "log 0..3"); return; }
    logLvl = n; savePrefs();
    lg('.', "registro nivel %d (guardado)", logLvl);
  }
  else if (k == "eco") {                         // A (directa): sí · B (por el bus, con TJA1020): no
    eco = a == "si" || a == "sí" || a == "on";
    savePrefs();
    lg('.', "eco %s (guardado)", eco ? "sí: conexión directa" : "no: por el bus");
  }
  else if (k == "pines") {                       // pines <rx> <tx>: los de la UART del W-Bus, guardados
    int b = a.indexOf(' '), rx = a.substring(0, b).toInt(), tx = b < 0 ? -1 : a.substring(b + 1).toInt();
    if (b < 0 || rx < 0 || tx < 0 || rx > 48 || tx > 48 || rx == tx) { lg('!', "pines <rx> <tx>, por ejemplo: pines 16 17"); return; }
    pinRx = rx; pinTx = tx; savePrefs();
    bus.end(); bus.begin(2400, SERIAL_8E1, pinRx, pinTx);
    lg('.', "RX %d · TX %d (guardado)", pinRx, pinTx);
  }
  else if (k == "reiniciar") { lg('.', "reiniciando…"); Serial.flush(); ESP.restart(); }
  else help();
}

// Llega una trama buena de WTTC: (eco) + respuesta, si la Webasto falsa contesta a eso
void answer() {
  uint8_t out[64], resp[64];
  uint8_t c = rd.cmd(); const uint8_t* d = rd.data(); int dn = rd.dataLen();
  st.frames++;
  if (st.lastFrameAt == 0) lg('.', "primera trama de WTTC: el cableado y los baudios están bien");
  st.lastFrameAt = millis();
  String q = asks(c, d, dn);                     // antes de handle(): el mantenimiento mide desde el anterior
  if (c == 0x44) st.lastKaAt = millis();
  if (logLvl >= 2 && noiseRun) lg('.', "(antes, %lu bytes sueltos: el «despertar» del bus de WTTC o ruido)", (unsigned long)noiseRun);
  noiseRun = 0;
  if (w.env.noPower || w.env.mute) {
    st.ignoredOff++;
    if (logLvl >= 1) lg('<', "%s%s  — NO contesta: %s", q.c_str(), logLvl >= 2 ? ("  [" + hex(rd.b, rd.frameLen()) + "]").c_str() : "",
                          w.env.noPower ? "sin corriente" : "bus mudo");
    rd.reset();
    return;
  }
  if (logLvl >= 1) lg('<', "%s%s", q.c_str(), logLvl >= 2 ? ("  [" + hex(rd.b, rd.frameLen()) + "]").c_str() : "");
  int n = w.handle(c, d, dn, out);
  int rl = n >= 0 ? wf::reply(resp, sizeof resp, c, out, n) : -1;
  if (eco) bus.write(rd.b, rd.frameLen());       // el eco que daría el bus de un solo hilo
  if (rl > 0) {
    bus.flush();
    delay(RESP_DELAY_MS);
    bus.write(resp, rl);
    bus.flush();
    if (!eco) ownEchoUntil = millis() + 20;       // B: lo que vuelve ahora es su propia respuesta
    st.answered++;
    if (c == 0x44 && w.cmd) kaWarned = false;
    if (logLvl >= 1) lg('>', "%s%s", says(c, out, n).c_str(), logLvl >= 2 ? ("  [" + hex(resp, rl) + "]").c_str() : "");
  } else {
    st.silent++;
    lg('!', "no contesta a «%s»: este modelo no la conoce (una Webasto real quizá sí)", q.c_str());
  }
  rd.reset();
}

void setup() {
  Serial.begin(115200);
  Serial.setTimeout(200);
  prefs.begin("wfalsa", true);
  eco = prefs.getBool("eco", WF_ECO);
  pinRx = prefs.getInt("rx", WF_RX); pinTx = prefs.getInt("tx", WF_TX);
  logLvl = prefs.getInt("log", 1);
  prefs.end();
  bus.begin(2400, SERIAL_8E1, pinRx, pinTx);
  w.temp = w.cab = w.env.amb;
  w.onLog = modelLog;
  delay(300);
  Serial.println();
  Serial.println(F("=== Webasto falsa: una Thermo Top C de mentira en el W-Bus, para probar WTTC ==="));
  Serial.printf("Chip %s · compilada el %s %s · W-Bus 2400 8E1 · RX %d · TX %d\n", ESP.getChipModel(), __DATE__, __TIME__, pinRx, pinTx);
  Serial.printf("Eco %s · registro nivel %d (log 0..3) · «ayuda» para las órdenes\n",
                eco ? "sí (conexión directa A; «eco no» para la B, por el bus)" : "no (por el bus, B; «eco si» para la directa)", logLvl);
  Serial.println(F("Marcas: < WTTC pide · > contesta · . la caldera hace (y por qué) · ! avisos · $ consola · = estado"));
  printState();
  lastTick = millis();
}

void loop() {
  uint32_t now = millis(), dt = now - lastTick;
  lastTick = now;
  w.tick(dt * speed);
  simMs += (uint64_t)dt * speed;
  while (bus.available()) {
    uint8_t c = bus.read();
    if (millis() < ownEchoUntil) continue;        // su propia respuesta, de vuelta por el bus (B)
    int before = rd.len;
    bool done = rd.feed(c);
    if (!done && before == 0 && rd.len == 0) { st.noise++; noiseRun++; if (logLvl >= 2 && noiseRun == 1) lg('.', "byte suelto 0x%02X (no empieza por F4)", c); continue; }
    if (!done && before > 0 && rd.len == 0) {
      st.bad++;
      lg('!', "trama descartada (checksum mal o longitud imposible): [%s] — ¿baudios/paridad, eco doble o cables?", hex(rd.b, before + 1).c_str());
      continue;
    }
    if (done) answer();
  }
  // Avisos: la orden sigue y WTTC no la renueva; o no llega nada de WTTC
  if (w.cmd && wf::active(w.st) && w.refresh > 15000 && !kaWarned) {
    kaWarned = true;
    lg('!', "WTTC lleva %s sin mandar el mantenimiento (0x44): a los 20 s se apaga con la avería 0x92", secs(w.refresh).c_str());
  }
  if (!st.frames && !quietWarned && now > 30000) {
    quietWarned = true;
    lg('!', "30 s sin recibir nada de WTTC: revisa TX↔RX (cruzados), la masa común, «pines» y «eco» (A: sí, B: no)");
  }
  uint32_t every = logLvl >= 3 ? 10000 : wf::active(w.st) || w.st == wf::AFTER ? 60000 : 300000;
  if (logLvl >= 1 && now - lastState > every) { lastState = now; printState(); }
  console();
  delay(2);
}
