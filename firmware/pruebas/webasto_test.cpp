// webasto_test.cpp — Pruebas del protocolo W-Bus de punta a punta, en un ordenador: WTTC (firmware/WTTC/logica.h, el
// mismo código que arma y comprueba las tramas en la placa) hablando con la Webasto falsa
// (herramientas/webasto_falsa/webasto_modelo.h, la del emulador en un segundo ESP32).
// Código generado íntegramente con Claude (Anthropic). Lo ejecuta GitHub Actions como logica_test.cpp.
#include "../WTTC/logica.h"
#include "../../herramientas/webasto_falsa/webasto_modelo.h"
#include <cstdio>

static int fallos = 0, pruebas = 0;
#define CHECK(c) do { pruebas++; if (!(c)) { fallos++; printf("FALLA %s:%d: %s\n", __FILE__, __LINE__, #c); } } while (0)

// Una orden de WTTC a la Webasto falsa, por bytes, como por el bus. Devuelve los datos de la respuesta (en r) o -1
static int bus(wf::Webasto& w, uint8_t cmd, const uint8_t* d, int n, uint8_t* r) {
  uint8_t f[40]; int fl = wbBuild(f, sizeof f, cmd, d, n);
  if (fl < 0) return -1;
  wf::Reader rd;
  bool done = false;
  for (int i = 0; i < fl; i++) done = rd.feed(f[i]);
  if (!done) return -1;
  uint8_t out[64], resp[64];
  int k = w.handle(rd.cmd(), rd.data(), rd.dataLen(), out);
  if (k < 0) return -1;
  int rl = wf::reply(resp, sizeof resp, rd.cmd(), out, k);
  int dl = wbCheck(resp, rl, cmd);                 // lo comprueba el código de la placa
  if (dl >= 0) memcpy(r, resp + 3, dl);
  return dl;
}
// Pasa el tiempo de segundo en segundo; con ka, WTTC manda el mantenimiento cada 5 s (como KEEPALIVE_MS)
static void run(wf::Webasto& w, uint32_t s, bool ka) {
  uint8_t r[64]; const uint8_t k[2] = {0x21, 0x00};
  for (uint32_t i = 1; i <= s; i++) { w.tick(1000); if (ka && i % 5 == 0) bus(w, 0x44, k, 2, r); }
}
static int sensors(wf::Webasto& w, int& temp, float& volt, int& flame, int& pw) {
  uint8_t r[64]; const uint8_t d[1] = {0x05};
  int n = bus(w, 0x50, d, 1, r);
  if (n < 4 || r[0] != 0x05) return -1;            // como readSensors() en WTTC.ino
  temp = r[1] - 50; volt = ((r[2] << 8) | r[3]) / 1000.0f; flame = n >= 5 ? r[4] : -1; pw = n >= 7 ? (r[5] << 8) | r[6] : -1;
  return n;
}

static void pruebasLector() {
  wf::Reader rd;
  const uint8_t basura[4] = {0x00, 0x55, 0xFF, 0x12};      // el «despertar» del bus y ruido: se ignoran
  for (uint8_t c : basura) CHECK(!rd.feed(c));
  uint8_t f[40]; const uint8_t d[1] = {0x05};
  int fl = wbBuild(f, sizeof f, 0x50, d, 1);
  bool ok = false; for (int i = 0; i < fl; i++) ok = rd.feed(f[i]);
  CHECK(ok && rd.cmd() == 0x50 && rd.dataLen() == 1 && rd.data()[0] == 0x05);
  rd.reset();
  f[fl - 1] ^= 1; ok = false; for (int i = 0; i < fl; i++) ok = rd.feed(f[i]);
  CHECK(!ok);                                               // checksum mal: no se contesta
  f[fl - 1] ^= 1; ok = false; for (int i = 0; i < fl; i++) ok = rd.feed(f[i]);
  CHECK(ok);                                                // y la siguiente buena sí se lee
  rd.reset();
  const uint8_t imposible[2] = {0xF4, 0x01};                // longitud imposible: vuelve a buscar F4
  CHECK(!rd.feed(imposible[0]) && !rd.feed(imposible[1]) && rd.len == 0);
}

static void pruebasArranque() {
  wf::Webasto w; w.temp = w.cab = w.env.amb = 5;
  uint8_t r[64]; int t, fl, pw; float v;
  CHECK(sensors(w, t, v, fl, pw) == 9 && t == 5 && fl == 0 && pw == 0 && v > 12.5f);
  const uint8_t m30[1] = {30};
  CHECK(bus(w, 0x21, m30, 1, r) == 1 && r[0] == 30);       // encender 30 min: lo acepta
  CHECK(w.st == wf::FAN);
  run(w, 30, true);                                         // ventilador 12 s; luego bujía 45 s
  CHECK(w.st == wf::GLOW && sensors(w, t, v, fl, pw) > 0 && fl == 0 && v < 12.2f);   // la bujía tira de la batería
  run(w, 60, true);                                         // 12 + 45 + 30 s: prende
  CHECK(w.st == wf::STAB && sensors(w, t, v, fl, pw) > 0 && fl == 1);
  run(w, 600, true);
  CHECK(w.flame() == 1 && w.temp > 30);                     // diez minutos: calentando
  const uint8_t k[2] = {0x21, 0x00};
  CHECK(bus(w, 0x44, k, 2, r) == 1 && r[0] == 0x00);        // el mantenimiento dice «sigo»
  CHECK(bus(w, 0x10, nullptr, 0, r) == 1);                  // apagar
  CHECK(w.st == wf::AFTER && bus(w, 0x44, k, 2, r) == 1 && r[0] == 0x00);   // en el postbarrido aún «sigue» (libwbus)
  CHECK(bus(w, 0x21, m30, 1, r) == 1 && w.st == wf::AFTER); // encender en postbarrido: se ignora
  run(w, 121, false);
  CHECK(w.st == wf::OFF && bus(w, 0x44, k, 2, r) == 1 && r[0] == 0x01);     // apagada del todo: ya no tiene la orden
}

static void pruebasMantenimiento() {
  wf::Webasto w; uint8_t r[64]; const uint8_t m[1] = {60};
  bus(w, 0x21, m, 1, r);
  run(w, 19, false);
  CHECK(wf::active(w.st));
  run(w, 2, false);                                         // 21 s sin mantenimiento: se apaga sola, avería 0x92
  CHECK(w.st == wf::AFTER && w.cmd == 0 && w.errN == 1 && w.errCode[0] == 0x92);
}

static void pruebasTiempo() {
  wf::Webasto w; uint8_t r[64]; const uint8_t m[1] = {1};
  bus(w, 0x21, m, 1, r);
  run(w, 59, true);
  CHECK(wf::active(w.st));
  run(w, 2, true);                                          // se acabó el minuto pedido
  CHECK(w.st == wf::AFTER);
}

static void pruebasAverias() {
  wf::Webasto w; uint8_t r[64]; const uint8_t m[1] = {30}, k[2] = {0x21, 0x00}, lista[1] = {0x01}, borra[1] = {0x03};
  w.env.noFuel = true;
  bus(w, 0x21, m, 1, r);
  run(w, 170, true);                                        // dos intentos sin prender: avería 02
  CHECK(w.st == wf::FAIL && bus(w, 0x44, k, 2, r) == 1 && r[0] == 0x00);   // en el fallo aún «sigue» (libwbus)
  int n = bus(w, 0x56, lista, 1, r);
  CHECK(n == 4 && r[0] == 0x01 && r[1] == 1 && r[2] == 0x02 && r[3] == 1);   // como errorsJson() la lee
  run(w, 91, false);                                        // fin del fallo: apagada del todo, suelta la orden
  CHECK(w.st == wf::OFF && bus(w, 0x44, k, 2, r) == 1 && r[0] == 0x01);
  bus(w, 0x21, m, 1, r); run(w, 170, true);
  run(w, 91, false); bus(w, 0x21, m, 1, r); run(w, 170, true);
  CHECK(w.st == wf::LOCK);                                  // a la tercera, bloqueada (avería 07)
  bus(w, 0x21, m, 1, r);
  CHECK(w.st == wf::LOCK);                                  // bloqueada no arranca
  n = bus(w, 0x56, lista, 1, r);
  CHECK(n == 6 && r[1] == 2 && r[2] == 0x02 && r[3] == 3 && r[4] == 0x07);
  CHECK(bus(w, 0x56, borra, 1, r) == 1 && w.errN == 0);

  wf::Webasto w2; bus(w2, 0x21, m, 1, r);
  run(w2, 150, true);
  CHECK(w2.flame() == 1);
  w2.env.flameOut = true;                                   // se le apaga la llama: avería 03
  run(w2, 1, true);
  CHECK(w2.st == wf::FAIL && w2.errN == 1 && w2.errCode[0] == 0x03);
  run(w2, 91, true);                                        // WTTC se entera al apagarse del todo: 0x44 → «ya no»
  CHECK(w2.st == wf::OFF && bus(w2, 0x44, k, 2, r) == 1 && r[0] == 0x01);

  wf::Webasto w3; w3.env.mute = true;                       // (el emulador no contesta: eso lo hace el sketch)
  w3.env.noPower = true; bus(w3, 0x21, m, 1, r); w3.tick(1000);
  CHECK(w3.st == wf::OFF && w3.volt() == 0);
}

static void pruebasRegulacion() {
  wf::Webasto w; uint8_t r[64]; const uint8_t m[1] = {255};
  w.temp = w.cab = w.env.amb = 25;                          // con 15 °C, a carga parcial se equilibra en ~81 °C: sin pausas
  bus(w, 0x21, m, 1, r);
  bool part = false, pause = false; float tmax = 0;
  for (int s = 0; s < 4 * 3600; s++) {                     // cuatro horas, renovando la orden cada hora
    w.tick(1000);
    if (s % 5 == 4) { const uint8_t k[2] = {0x21, 0x00}; bus(w, 0x44, k, 2, r); }
    if (s % 3600 == 3599) bus(w, 0x21, m, 1, r);
    part |= w.st == wf::PART; pause |= w.st == wf::PAUSE; if (w.temp > tmax) tmax = w.temp;
  }
  CHECK(part && pause);                                     // regula: carga parcial y pausas
  CHECK(tmax > 80 && tmax < 95);                            // sin pasarse
  CHECK(w.cab > w.env.amb + 10);                            // y dentro ha subido
}

int main() {
  pruebasLector();
  pruebasArranque();
  pruebasMantenimiento();
  pruebasTiempo();
  pruebasAverias();
  pruebasRegulacion();
  printf("%d pruebas, %d fallos\n", pruebas, fallos);
  return fallos ? 1 : 0;
}
