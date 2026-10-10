// logica_test.cpp — Pruebas de firmware/WTTC/logica.h en un ordenador (no en la placa).
// Código generado íntegramente con Claude (Anthropic).
//
// Las compila y ejecuta GitHub Actions en cada cambio del firmware (.github/workflows/firmware.yml), con los detectores
// de errores de memoria y de comportamiento indefinido de GCC (-fsanitize=address,undefined). Si una falla, no se publica.
//   g++ -std=c++17 -Wall -Wextra -Werror -fsanitize=address,undefined firmware/pruebas/logica_test.cpp -o t && ./t
// Está fuera de firmware/WTTC/ a propósito: Arduino compila todos los .cpp de la carpeta del sketch.
#include "../WTTC/logica.h"
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

int main() {
  pruebasWbus();
  pruebasVersiones();
  pruebasCodigo();
  pruebasSalida();
  pruebasTermostato();
  printf("%d pruebas, %d fallos\n", pruebas, fallos);
  return fallos ? 1 : 0;
}
