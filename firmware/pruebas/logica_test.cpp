// logica_test.cpp — Pruebas de firmware/WTTC/logica.h en un ordenador (no en la placa).
// Código generado íntegramente con Claude (Anthropic).
//
// Las compila y ejecuta GitHub Actions en cada cambio del firmware (.github/workflows/firmware.yml), con los detectores
// de errores de memoria y de comportamiento indefinido de GCC (-fsanitize=address,undefined). Si una falla, no se publica.
//   g++ -std=c++17 -Wall -Wextra -Werror -fsanitize=address,undefined firmware/pruebas/logica_test.cpp -o t && ./t
// Está fuera de firmware/WTTC/ a propósito: Arduino compila todos los .cpp de la carpeta del sketch.
#include <cstring>
#include "../WTTC/logica.h"
#include "../WTTC/averias.h"
#include <cstdio>
#include <cmath>

static int fallos = 0, pruebas = 0;
#define CHECK(c) do { pruebas++; if (!(c)) { fallos++; printf("FALLA %s:%d: %s\n", __FILE__, __LINE__, #c); } } while (0)

// ---------- W-Bus ----------
static void pruebasWbus() {
  uint8_t f[40];
  const uint8_t d05[1] = {0x05};
  // Trama real de la placa (captura de Diagnóstico): leer sensores = F4 03 50 05 A2
  CHECK(wbBuild(f, sizeof f, 0x50, d05, 1) == 5);
  CHECK(f[0] == 0xF4 && f[1] == 0x03 && f[2] == 0x50 && f[3] == 0x05 && f[4] == 0xA2);
  // Sin datos: F4 02 10 + XOR (apagar)
  CHECK(wbBuild(f, sizeof f, 0x10, nullptr, 0) == 4);
  CHECK(f[3] == (0xF4 ^ 0x02 ^ 0x10));
  // No cabe, faltan los datos o el destino no existe: no se arma nada
  uint8_t big[40] = {};
  CHECK(wbBuild(f, sizeof f, 0x21, big, 37) == -1);
  CHECK(wbBuild(f, sizeof f, 0x21, big, 36) == 40);
  CHECK(wbBuild(f, sizeof f, 0x21, nullptr, 1) == -1);
  CHECK(wbBuild(nullptr, 40, 0x21, d05, 1) == -1);
  CHECK(wbBuild(f, sizeof f, 0x21, d05, -1) == -1);

  // Respuesta buena a 0x50: 4F 04 D0 05 2A cs (2 bytes de datos)
  uint8_t r[8] = {0x4F, 0x04, 0xD0, 0x05, 0x2A, 0};
  r[5] = wbXor(r, 5);
  CHECK(wbCheck(r, 6, 0x50) == 2);
  CHECK(wbCheck(r, 7, 0x50) == 2);              // bytes de más detrás: no importan
  CHECK(wbCheck(r, 5, 0x50) == -1);             // cortada
  CHECK(wbCheck(r, 6, 0x10) == -1);             // es la respuesta a otra orden
  uint8_t mal[6]; memcpy(mal, r, 6); mal[5] ^= 1;
  CHECK(wbCheck(mal, 6, 0x50) == -1);           // checksum mal
  memcpy(mal, r, 6); mal[0] = 0xF4;
  CHECK(wbCheck(mal, 6, 0x50) == -1);           // no viene de la Webasto
  const uint8_t corta[3] = {0x4F, 0x01, 0xD0};
  CHECK(wbCheck(corta, 3, 0x50) == -1);         // longitud imposible (sin sitio para el checksum)
  CHECK(wbCheck(r, 1, 0x50) == -1);
  CHECK(wbCheck(nullptr, 6, 0x50) == -1);
  uint8_t larga[64] = {0x4F, 0xFF, 0xD0};       // anuncia 257 bytes: nunca completa en 64
  CHECK(wbCheck(larga, 64, 0x50) == -1);
  // Sin datos (solo comando y checksum): 4F 02 90 cs
  uint8_t r0[4] = {0x4F, 0x02, 0x90, 0};
  r0[3] = wbXor(r0, 3);
  CHECK(wbCheck(r0, 4, 0x10) == 0);
}

// ---------- Versiones ----------
static void pruebasVersiones() {
  CHECK(verCmpC("0.2.10", "0.2.9") == 1);       // por número, no por texto
  CHECK(verCmpC("0.2.9", "0.2.10") == -1);
  CHECK(verCmpC("0.3.0", "0.3.0") == 0);
  CHECK(verCmpC("1.0", "0.9.9") == 1);          // lo que falta cuenta como 0
  CHECK(verCmpC("0.3", "0.3.0") == 0);
  CHECK(verCmpC("", "0.0.1") == -1);
  CHECK(verCmpC(nullptr, "0.0.0") == 0);
  CHECK(verCmpC("basura", "0.0.0") == 0);
}

// ---------- Código de instalación ----------
static void pruebasCodigo() {
  char o[20];
  CHECK(iidNorm("abcd-2345-efgh-6789", o) && !strcmp(o, "ABCD-2345-EFGH-6789"));
  CHECK(iidNorm("ABCD2345EFGH6789", o) && !strcmp(o, "ABCD-2345-EFGH-6789"));
  CHECK(iidNorm(" abcd 2345 efgh 6789 ", o) && !strcmp(o, "ABCD-2345-EFGH-6789"));
  CHECK(!iidNorm("ABCD-2345-EFGH-678", o));     // 15 caracteres
  CHECK(!iidNorm("ABCD-2345-EFGH-67899", o));   // 17
  CHECK(!iidNorm("ABCD-2345-EFGH-678O", o));    // la O no vale (se confunde con el 0)
  CHECK(!iidNorm("ABCD-2345-EFGH-6781", o));    // el 1 no vale (se confunde con la I)
  CHECK(!iidNorm("ABCD-2345-EFGH-678!", o));
  CHECK(!iidNorm("", o));
  CHECK(!iidNorm(nullptr, o));
}

// ---------- Hora de salida ----------
static void pruebasSalida() {
  CHECK(depLeadMin(NAN) == 30);
  CHECK(depLeadMin(15) == 15);
  CHECK(depLeadMin(25) == 15);                  // con calor, el mínimo
  CHECK(depLeadMin(0) == 38);                   // 15 + 15 × 1,5 = 37,5 → 38
  CHECK(depLeadMin(-15) == 60);
  CHECK(depLeadMin(-40) == 60);                 // nunca más de 60
}

// ---------- Termostato ----------
static ThIn base() {
  ThIn s;
  s.heaterOn = true; s.now = 10000000; s.until = 20000000; s.heatStart = 9000000; s.lastHeatOff = 0;
  s.cab = 10; s.target = 20; s.best = 10; s.bestAt = 9500000; s.warm = false; s.reached = false;
  return s;
}
static void pruebasTermostato() {
  ThIn s = base();
  CHECK(thDecide(s).act == TH_NONE);                        // calentando, aún lejos, subiendo hace poco

  // Fin de la sesión (también justo en el límite)
  s = base(); s.now = s.until;
  CHECK(thDecide(s).act == TH_END_WINDOW);
  s = base(); s.now = s.until + 1; s.heaterOn = false;
  CHECK(thDecide(s).act == TH_END_WINDOW);

  // Sin termómetro
  s = base(); s.cab = NAN;
  CHECK(thDecide(s).act == TH_NOSENS);

  // Dentro sube: nuevo máximo
  s = base(); s.cab = 10.6;
  ThOut o = thDecide(s);
  CHECK(o.newBest && o.act == TH_NONE);
  s = base(); s.best = NAN;
  CHECK(thDecide(s).newBest);                               // el primer dato siempre cuenta

  // No sube en TH_STALL: se rinde. Justo antes, no
  s = base(); s.bestAt = s.now - TH_STALL;
  CHECK(thDecide(s).act == TH_GIVE_UP);
  s = base(); s.bestAt = s.now - TH_STALL + 1;
  CHECK(thDecide(s).act == TH_NONE);
  // …salvo que acabe de subir (el nuevo máximo cuenta desde ahora)
  s = base(); s.bestAt = s.now - TH_STALL; s.cab = 10.5;
  CHECK(thDecide(s).act == TH_NONE);
  // …o que ya esté en el objetivo (entonces decide el mínimo de encendido)
  s = base(); s.bestAt = s.now - TH_STALL; s.cab = 20; s.heatStart = s.now - 60000;
  CHECK(thDecide(s).act == TH_NONE);

  // Llega al objetivo: apaga solo si ya lleva el mínimo (más corto si el agua estaba caliente)
  s = base(); s.cab = 20; s.heatStart = s.now - (TH_MINRUN - 1);
  o = thDecide(s);
  CHECK(o.act == TH_NONE && o.atTarget);
  s.heatStart = s.now - TH_MINRUN;
  CHECK(thDecide(s).act == TH_REACHED);
  s = base(); s.cab = 21; s.warm = true; s.heatStart = s.now - TH_MINRUN_WARM;
  CHECK(thDecide(s).act == TH_REACHED);

  // Apagada: vuelve a encender al enfriarse (con histéresis si ya llegó una vez)
  s = base(); s.heaterOn = false; s.reached = true; s.lastHeatOff = s.now - TH_REST;
  s.cab = 19;                                               // solo 1 °C por debajo: espera
  CHECK(thDecide(s).act == TH_NONE);
  s.cab = 18.5;                                             // TH_HYST por debajo: enciende
  CHECK(thDecide(s).act == TH_RESTART);
  s.reached = false; s.cab = 19.9;                          // aún no había llegado: sigue intentándolo
  CHECK(thDecide(s).act == TH_RESTART);
  s.lastHeatOff = s.now - TH_REST + 1;                      // aún en el postbarrido
  CHECK(thDecide(s).act == TH_NONE);
  s.lastHeatOff = 0;                                        // nunca se apagó: puede encender
  CHECK(thDecide(s).act == TH_RESTART);
  s.until = s.now + TH_MINRUN - 1;                          // no queda tiempo para un encendido mínimo
  CHECK(thDecide(s).act == TH_NONE);

  // millis() da la vuelta (cada 49,7 días): las cuentas con unsigned siguen bien
  s = base(); s.heatStart = 0xFFFFFF00u; s.now = s.heatStart + TH_MINRUN; s.until = s.now + 3600000;
  s.bestAt = s.now - 1000; s.cab = 20;
  CHECK(thDecide(s).act == TH_REACHED);
  s = base(); s.until = 0x00000100u; s.now = 0xFFFFFF00u;   // la sesión acaba justo después de la vuelta
  CHECK(thDecide(s).act != TH_END_WINDOW);
}

// ---------- Órdenes remotas ----------
static void pruebasOrdenes() {
  uint8_t b[80];
  CHECK(hexDecode("00ff10Ab", b, 80) == 4 && b[1] == 0xFF && b[3] == 0xAB);
  CHECK(hexDecode("0", b, 80) == -1);                       // impar
  CHECK(hexDecode("zz", b, 80) == -1);
  CHECK(hexDecode("0011", b, 1) == -1);                     // no cabe
  CHECK(hexDecode("", b, 80) == 0);

  char m[200]; uint8_t sig[80]; int sl;
  CHECK(cmdSplit("WTTCCMD1|X\n30450221000102030405\n", m, sizeof m, sig, sizeof sig, sl) && !strcmp(m, "WTTCCMD1|X") && sl == 10);
  CHECK(cmdSplit("msg\n30450221000102030405\r\n", m, sizeof m, sig, sizeof sig, sl) && sl == 10);   // fin de línea de Windows
  CHECK(!cmdSplit("sin salto", m, sizeof m, sig, sizeof sig, sl));
  CHECK(!cmdSplit("msg\nnohex", m, sizeof m, sig, sizeof sig, sl));
  CHECK(!cmdSplit("msg\n30", m, sizeof m, sig, sizeof sig, sl));   // firma demasiado corta
  CHECK(!cmdSplit("\n3045022100aa", m, sizeof m, sig, sizeof sig, sl));   // sin mensaje
  CHECK(!cmdSplit("mensaje demasiado largo para el sitio\n3045022100aa", m, 8, sig, sizeof sig, sl));

  Cmd c;
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|17|1800000000|update", c) == CE_OK && c.kind == CMD_UPDATE && c.seq == 17 && c.exp == 1800000000u
        && !strcmp(c.iid, "ABCD-2345-EFGH-6789"));
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|1|1|diag-off", c) == CE_OK && c.kind == CMD_DIAG_OFF);
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|1|1|heat", c) == CE_UNKNOWN);          // nunca la calefacción
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|1|1|update extra", c) == CE_UNKNOWN);
  CHECK(cmdParse("WTTCCMD2|ABCD-2345-EFGH-6789|1|1|update", c) == CE_FORMAT);          // otra versión del formato
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-678|1|1|update", c) == CE_FORMAT);           // código corto
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|x|1|update", c) == CE_FORMAT);
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|99999999999|1|update", c) == CE_FORMAT);   // más de 32 bits
  CHECK(cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|1|1", c) == CE_FORMAT);
  CHECK(cmdParse(nullptr, c) == CE_FORMAT);

  cmdParse("WTTCCMD1|ABCD-2345-EFGH-6789|17|1800000600|update", c);
  CHECK(cmdCheck(c, "ABCD-2345-EFGH-6789", 16, 1800000000) == CE_OK);
  CHECK(cmdCheck(c, "ZZZZ-2345-EFGH-6789", 16, 1800000000) == CE_OTHER_BOARD);
  CHECK(cmdCheck(c, "ABCD-2345-EFGH-6789", 17, 1800000000) == CE_OLD);                 // ya ejecutada
  CHECK(cmdCheck(c, "ABCD-2345-EFGH-6789", 16, 1800000601) == CE_EXPIRED);
  CHECK(cmdCheck(c, "ABCD-2345-EFGH-6789", 16, 1800000600 - CMD_MAX_LIFE - 1) == CE_EXPIRED);   // caduca demasiado tarde
  CHECK(cmdCheck(c, nullptr, 16, 1800000000) == CE_OTHER_BOARD);
}

// ---------- Programador ----------
static void pruebasProgramador() {
  // Cinco encendidos: de 2 a 20 °C en 36 min (0,5 °C/min), etc.
  int8_t c0[6] = {2, 5, -128, 10, 0, 4}; uint8_t tg[6] = {20, 20, 20, 21, 0, 20}; uint8_t tr[6] = {36, 30, 40, 22, 30, 32};
  int used = 0;
  float r = heatRate(c0, tg, tr, 6, used);
  CHECK(used == 4);                                         // sin termómetro (-128) y sin objetivo (0): no cuentan
  CHECK(r > 0.49f && r < 0.51f);                            // mediana de 0,5 · 0,5 · 0,5 · 0,5
  CHECK(depLeadLearned(r, 2, 20, 30) == 46);                // 18 °C / 0,5 × 1,15 + 5 = 46,4 → 46
  CHECK(depLeadLearned(r, -10, 20, 30) == 74);              // 30 °C / 0,5 × 1,15 + 5
  CHECK(depLeadLearned(r, -40, 20, 30) == DEP_MAX_LEAD);    // muchísimo frío: el máximo
  CHECK(depLeadLearned(r, 19, 20, 30) == 10);               // casi: el mínimo
  CHECK(depLeadLearned(r, 22, 20, 30) == 10);               // ya está caliente
  CHECK(depLeadLearned(0, 2, 20, 33) == 33);                // sin aprender: la fórmula
  CHECK(depLeadLearned(r, NAN, 20, 33) == 33);              // sin temperatura: la fórmula
  CHECK(depLeadLearned(r, 2, 0, 33) == 33);                 // sin objetivo: la fórmula
  int8_t c1[2] = {2, 3}; uint8_t t1[2] = {20, 20}, r1[2] = {30, 30};
  CHECK(heatRate(c1, t1, r1, 2, used) == 0 && used == 2);   // menos de 3: no se aprende
  int8_t c2[3] = {19, 18, 2}; uint8_t t2[3] = {20, 20, 20}, r2[3] = {5, 4, 255};
  CHECK(heatRate(c2, t2, r2, 3, used) == 0 && used == 0);   // subir menos de 3 °C o «no llegó» (255): no cuentan
  int8_t c3[12]; uint8_t t3[12], r3[12];
  for (int i = 0; i < 12; i++) { c3[i] = 0; t3[i] = 20; r3[i] = (uint8_t)(i < 2 ? 200 : 40); }
  heatRate(c3, t3, r3, 12, used);
  CHECK(used == LEARN_MAX);                                 // solo los 10 más recientes

  CHECK(!coldSkip(SCH_NOCOLD, 25));                         // sin condición: siempre
  CHECK(coldSkip(10, 12) && coldSkip(10, 10));              // 10 °C o más dentro: no hace falta
  CHECK(!coldSkip(10, 9.9f));
  CHECK(!coldSkip(10, NAN));                                // sin dato: se enciende
}


// ---------- Chequeo de la Webasto ----------
static void pruebasChequeo() {
  // Sensores: 05 · 20 °C (70) · 12,60 V (0x3138) · sin llama · 0 W · bujía 520 mΩ (0x0208)
  const uint8_t s1[] = {0x05, 70, 0x31, 0x38, 0, 0, 0, 0x02, 0x08};
  WbSensors s{};
  CHECK(wbParseSensors(s1, sizeof s1, s) && s.tempC == 20 && fabs(s.volt - 12.6f) < 0.01f && s.flame == 0 && s.power == 0);
  CHECK(s.glowMilliOhm == 520);
  // Respuesta corta (sin bujía ni potencia): lo que hay, el resto -1
  const uint8_t s2[] = {0x05, 40, 0x2F, 0x44};
  CHECK(wbParseSensors(s2, sizeof s2, s) && s.tempC == -10 && s.flame == -1 && s.power == -1 && s.glowMilliOhm == -1);
  const uint8_t sBad[] = {0x06, 70, 0x31};
  CHECK(!wbParseSensors(sBad, sizeof sBad, s) && !wbParseSensors(nullptr, 9, s));

  // Contadores: 06 · 312 h 15 min de trabajo · 290 h 5 min de funcionamiento · 1450 arranques
  const uint8_t c1[] = {0x06, 0x01, 0x38, 15, 0x01, 0x22, 5, 0x05, 0xAA};
  WbCounters c{};
  CHECK(wbParseCounters(c1, sizeof c1, c) && c.workH == 312 && c.workMin == 15 && c.opH == 290 && c.opMin == 5 && c.starts == 1450);
  CHECK(!wbParseCounters(c1, 8, c));

  // Lista de averías: 01 · 2 · [0x03 × 3] [0x87 × 1]; con max=1 solo copia una; número mayor que los datos: no se pasa
  const uint8_t l1[] = {0x01, 2, 0x03, 3, 0x87, 1};
  uint8_t codes[8], counts[8];
  CHECK(wbParseFaultList(l1, sizeof l1, codes, counts, 8) == 2 && codes[0] == 0x03 && counts[0] == 3 && codes[1] == 0x87);
  CHECK(wbParseFaultList(l1, sizeof l1, codes, counts, 1) == 1);
  const uint8_t l2[] = {0x01, 5, 0x02, 1};
  CHECK(wbParseFaultList(l2, sizeof l2, codes, counts, 8) == 1);
  const uint8_t l0[] = {0x01, 0};
  CHECK(wbParseFaultList(l0, sizeof l0, codes, counts, 8) == 0);

  // Detalle: 02 · 0x83 · flags · 2 veces · estado · 5 °C · 11,80 V · 310 h 40 min
  const uint8_t d1[] = {0x02, 0x83, 0x01, 2, 0x04, 0x00, 55, 0x2E, 0x18, 0x01, 0x36, 40};
  WbFaultInfo f{};
  CHECK(wbParseFaultInfo(d1, sizeof d1, f) && f.code == 0x83 && f.count == 2 && f.tempC == 5 && fabs(f.volt - 11.8f) < 0.01f);
  CHECK(f.hours == 310 && f.minutes == 40);
  CHECK(!wbParseFaultInfo(d1, 11, f));

  // Fecha 15/03/09 → 03/2009; mes 13: no vale
  int m = 0, y = 0;
  const uint8_t dt[] = {0x05, 15, 3, 9}, dtBad[] = {0x05, 15, 13, 9};
  CHECK(wbParseDate(dt, sizeof dt, m, y) && m == 3 && y == 2009);
  CHECK(!wbParseDate(dtBad, sizeof dtBad, m, y));

  // Nombre: «TTC     » con espacios y un byte no imprimible
  char name[12];
  const uint8_t nm[] = {0x0B, 'T', 'T', '-', 'C', 0x01, ' ', ' ', ' '};
  wbParseName(nm, sizeof nm, name, sizeof name);
  CHECK(strcmp(name, "TT-C") == 0);
  wbParseName(nm, 1, name, sizeof name);
  CHECK(name[0] == 0);

  // Gravedad y tipos
  CHECK(wbFaultSeverity(0x87) == 2 && wbFaultSeverity(0x03) == 1);
  CHECK(wbFaultLocks(0x87) && wbFaultLocks(0x07) && !wbFaultLocks(0x03));
  CHECK(wbFaultStart(0x83) && wbFaultStart(0x88) && !wbFaultStart(0x06));

  // Valoraciones
  CHECK(chkVolt(12.6f) == CHK_OK && chkVolt(12.0f) == CHK_WARN && chkVolt(11.2f) == CHK_BAD && chkVolt(15.5f) == CHK_BAD);
  CHECK(chkVolt(0) == CHK_NA);
  CHECK(chkTemp(20) == CHK_OK && chkTemp(-50) == CHK_BAD && chkTemp(150) == CHK_BAD);
  CHECK(chkGlow(520) == CHK_OK && chkGlow(20) == CHK_BAD && chkGlow(9000) == CHK_BAD && chkGlow(-1) == CHK_NA);
  CHECK(chkFlameIdle(1, false) == CHK_WARN && chkFlameIdle(1, true) == CHK_OK && chkFlameIdle(0, false) == CHK_OK);
  CHECK(chkWorst(CHK_OK, CHK_WARN) == CHK_WARN && chkWorst(CHK_NA, CHK_OK) == CHK_OK && chkWorst(CHK_BAD, CHK_NA) == CHK_BAD);
}

// Tabla de averías: códigos sin repetir, los tres idiomas con texto, y los que el chequeo trata como graves, con nombre
static void pruebasAverias() {
  const int n = sizeof AVERIAS / sizeof AVERIAS[0];
  bool rep = false, vacio = false;
  for (int i = 0; i < n; i++) {
    for (int j = i + 1; j < n; j++) rep |= AVERIAS[i].code == AVERIAS[j].code;
    for (int l = 0; l < 3; l++) { const char* t = averiaTexto(AVERIAS[i].code, l); vacio |= !t || !*t; }
  }
  CHECK(n > 50 && !rep && !vacio);
  const uint8_t graves[] = {0x01, 0x07, 0x11, 0x14, 0x23, 0x57, 0x87, 0x91, 0x94};
  for (uint8_t c : graves) CHECK(averiaTexto(c, 0) != nullptr && wbFaultSeverity(c) == 2);
  CHECK(averiaTexto(0x07, 1) != nullptr && averiaTexto(0x07, 2) != nullptr);
}

// Sensor de CO: tramas del ZE07-CO y alarma
static void coTrama(uint8_t* f, int decimas) {
  const uint8_t b[9] = {0xFF, 0x04, 0x03, 0x01, (uint8_t)(decimas >> 8), (uint8_t)decimas, 0x13, 0x88, 0};
  for (int i = 0; i < 9; i++) f[i] = b[i];
  f[8] = coSum(f);
}
static void pruebasCo() {
  uint8_t f[9]; float ppm = -1;
  coTrama(f, 123);
  CHECK(coParse(f, ppm) && fabs(ppm - 12.3f) < 0.01f);
  f[5] ^= 1; CHECK(!coParse(f, ppm));                        // suma mal
  coTrama(f, 20000); CHECK(!coParse(f, ppm));                // 2000 ppm: fuera de escala
  coTrama(f, 0); f[1] = 0x86; f[8] = coSum(f); CHECK(!coParse(f, ppm));   // otra trama
  // Lector: basura delante, una trama cortada y luego una buena
  CoRx rx{}; int ok = 0;
  const uint8_t basura[] = {0x12, 0x00, 0xFF, 0x04, 0x03};
  for (uint8_t c : basura) ok += coFeed(rx, c, ppm);
  coTrama(f, 456);
  for (uint8_t c : f) ok += coFeed(rx, c, ppm);
  CHECK(ok == 1 && fabs(ppm - 45.6f) < 0.01f);
  coTrama(f, 7); ok = 0;
  for (uint8_t c : f) ok += coFeed(rx, c, ppm);
  CHECK(ok == 1 && fabs(ppm - 0.7f) < 0.01f);
  // Alarma (una lectura por segundo)
  CoAlarm a{};
  for (int i = 0; i < 600; i++) coStep(a, 5, 1000);
  CHECK(a.lvl == CO_OK);
  for (int i = 0; i < 119; i++) coStep(a, 35, 1000);
  CHECK(a.lvl == CO_OK);
  coStep(a, 35, 1000); CHECK(a.lvl == CO_WARN);              // 2 min por encima de 30
  for (int i = 0; i < 59; i++) coStep(a, 10, 1000);
  CHECK(a.lvl == CO_WARN);
  coStep(a, 10, 1000); CHECK(a.lvl == CO_OK);                // 1 min por debajo de 20
  a = CoAlarm{};
  coStep(a, 350, 1000); CHECK(a.lvl == CO_OK);               // un pico suelto no basta
  coStep(a, 350, 1000); CHECK(a.lvl == CO_ALARM);            // dos seguidas de 300 o más
  for (int i = 0; i < 299; i++) coStep(a, 10, 1000);
  CHECK(a.lvl == CO_ALARM);
  coStep(a, 25, 1000); for (int i = 0; i < 299; i++) coStep(a, 10, 1000);
  CHECK(a.lvl == CO_ALARM);                                  // una lectura de 25 reinicia la cuenta
  coStep(a, 10, 1000); CHECK(a.lvl == CO_OK);                // 5 min seguidos por debajo de 20
  a = CoAlarm{};
  for (int i = 0; i < 29; i++) coStep(a, 120, 1000);
  CHECK(a.lvl != CO_ALARM);
  coStep(a, 120, 1000); CHECK(a.lvl == CO_ALARM);            // 100 ppm 30 s
  a = CoAlarm{};
  for (int i = 0; i < 179; i++) coStep(a, 60, 1000);
  CHECK(a.lvl == CO_WARN);
  coStep(a, 60, 1000); CHECK(a.lvl == CO_ALARM);             // 50 ppm 3 min
  a = CoAlarm{};
  coStep(a, 350, 1000); coStep(a, 350, 600000); CHECK(a.lvl == CO_ALARM);  // un hueco largo cuenta como 5 s
  a = CoAlarm{};
  coStep(a, 60, 1000); coStep(a, 60, 3600000); CHECK(a.lvl == CO_OK);       // y no dispara la de 3 min
}


// Consola serie: letra a letra (PuTTY) o de golpe (Monitor serie), con CR, LF o los dos, y borrar
static int lineas(LineaSerie& L, const char* s, char* ult) {
  int k = 0;
  for (; *s; s++) if (lineaFeed(L, *s)) { k++; strcpy(ult, L.buf); }
  return k;
}
static void pruebasConsola() {
  LineaSerie L{}; char u[200] = "";
  CHECK(lineas(L, "status\r", u) == 1 && !strcmp(u, "status"));
  CHECK(lineas(L, "on 30\r\n", u) == 1 && !strcmp(u, "on 30"));        // CR+LF: una sola línea
  CHECK(lineas(L, "\n\r\n", u) == 0);                                   // Enter sin nada: nada
  CHECK(lineas(L, "erorr\b\b\bror\x7f" "res\n", u) == 1 && !strcmp(u, "errores"));
  char largo[400]; memset(largo, 'a', 399); largo[399] = 0;
  CHECK(lineas(L, largo, u) == 0 && lineas(L, "\n", u) == 1 && strlen(u) == 159);   // recorta, no se sale
}


// Programas que se pisan (prohibido): ventanas de la semana, separación de SCHED_GAP, salida y medianoche
static void pruebasChoque() {
  int i, j;
  const uint8_t TODOS = 0x7F, LUN = 1, MAR = 2, DOM = 64;
  ProgV a[] = {{true, TODOS, 16 * 60 + 5, 30, false}, {true, TODOS, 16 * 60 + 15, 30, false}};
  CHECK(progChoque(a, 2, i, j) && i == 0 && j == 1);          // 16:05–16:35 y 16:15: se pisan (el caso de la mesa)
  a[1].start = 16 * 60 + 38;                                   // 16:38: solo 3 min tras el final → falta separación
  CHECK(progChoque(a, 2, i, j));
  a[1].start = 16 * 60 + 40;                                   // 16:40: 5 min tras el final → vale
  CHECK(!progChoque(a, 2, i, j));
  a[1].en = false; a[1].start = 16 * 60 + 15;                  // desactivado: no cuenta
  CHECK(!progChoque(a, 2, i, j));
  a[1].en = true; a[0].days = LUN; a[1].days = MAR;            // mismos horarios, días distintos: vale
  CHECK(!progChoque(a, 2, i, j));
  ProgV m[] = {{true, LUN, 23 * 60 + 50, 30, false}, {true, MAR, 10, 30, false}};
  CHECK(progChoque(m, 2, i, j));                               // lunes 23:50–00:20 del martes y martes 00:10
  ProgV dom[] = {{true, DOM, 23 * 60 + 40, 30, false}, {true, LUN, 5, 30, false}};
  CHECK(progChoque(dom, 2, i, j));                             // domingo 23:40 → lunes 00:10 pisa el lunes 00:05
  ProgV s[] = {{true, TODOS, 7 * 60, 30, false}, {true, TODOS, 8 * 60, 30, true}};
  CHECK(progChoque(s, 2, i, j));                               // salida a las 8:00: puede encender desde las 6:30
  s[0].start = 6 * 60;                                         // 6:00–6:30 y salida 8:00 (desde 6:30): falta separación
  CHECK(progChoque(s, 2, i, j));
  s[0].start = 5 * 60 + 50;                                    // 5:50–6:20 y salida 8:00: 10 min de margen → vale
  CHECK(!progChoque(s, 2, i, j));
  int k;
  CHECK(ventanaChocaProg(s, 2, 2 * 1440 + 6 * 60, 10, k) && k == 0);        // salida suelta el miércoles 6:00 (5:50–6:20)
  CHECK(ventanaChocaProg(s, 2, 2 * 1440 + 6 * 60 + 25, 10, k) && k == 1);   // 6:25: choca con la salida de las 8:00
  CHECK(!ventanaChocaProg(s, 2, 2 * 1440 + 12 * 60, 30, k));
}

int main() {
  pruebasChoque();
  pruebasConsola();
  pruebasCo();
  pruebasAverias();
  pruebasChequeo();
  pruebasWbus();
  pruebasVersiones();
  pruebasCodigo();
  pruebasSalida();
  pruebasTermostato();
  pruebasOrdenes();
  pruebasProgramador();
  printf("%d pruebas, %d fallos\n", pruebas, fallos);
  return fallos ? 1 : 0;
}
