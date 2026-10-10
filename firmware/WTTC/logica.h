// logica.h — Cálculos de WTTC sin hardware ni red: tramas del W-Bus, versiones, código de instalación, antelación de
// la salida y la decisión del termostato. Código generado íntegramente con Claude (Anthropic).
//
// Van aparte para poder probarlos en un ordenador (firmware/pruebas/logica_test.cpp, en GitHub Actions) con muchos
// casos, sin la placa. Aquí no hay nada de Arduino: solo C++ estándar. WTTC.ino los usa tal cual.
#pragma once
#include <stdint.h>
#include <string.h>
#include <stdio.h>
#include <ctype.h>
#include <math.h>

// ---------- Termostato: constantes (las usa el firmware y las pruebas) ----------
const float    TH_HYST        = 1.5;     // °C: con el termostato, vuelve a encender al bajar esto por debajo del objetivo
const uint32_t TH_MINRUN      = 900000;  // ms: mínimo por encendido con termostato y el agua fría (las Webasto no llevan
                                         // bien los encendidos cortos: hollín)
const uint32_t TH_MINRUN_WARM = 300000;  // ms: el mínimo si arrancó con el agua ya caliente (ciclos del termostato): así
const int      TH_WARM_C      = 30;      // no se pasa tanto del objetivo. °C del agua a partir de los que cuenta como caliente
const uint32_t TH_REST        = 180000;  // ms: tras apagarse, espera antes de volver a encender (termina su postbarrido)
const uint32_t TH_STALL       = 1500000; // ms: calentando sin que dentro suba TH_STALL_C, el termostato se da por vencido
const float    TH_STALL_C     = 0.5;     // °C (si hace demasiado frío fuera o el termómetro está mal puesto, no gasta en balde)

// ---------- W-Bus ----------
// XOR de n bytes (el checksum del W-Bus)
inline uint8_t wbXor(const uint8_t* b, int n) { uint8_t c = 0; for (int i = 0; i < n; i++) c ^= b[i]; return c; }

// Arma una trama hacia la Webasto: F4 · longitud (comando + datos + checksum) · comando · datos · XOR.
// Devuelve su longitud, o -1 si no cabe en cap bytes o faltan los datos
inline int wbBuild(uint8_t* f, int cap, uint8_t cmd, const uint8_t* d, int n) {
  if (!f || n < 0 || n + 4 > cap || (n && !d)) return -1;
  f[0] = 0xF4; f[1] = (uint8_t)(n + 2); f[2] = cmd;
  if (n) memcpy(f + 3, d, n);
  f[n + 3] = wbXor(f, n + 3);
  return n + 4;
}

// Comprueba una respuesta de la Webasto (4F · longitud · comando|0x80 · datos · XOR) a la orden cmd.
// Devuelve cuántos bytes de datos trae (empiezan en b + 3), o -1 si está incompleta, mal o es de otra orden
inline int wbCheck(const uint8_t* b, int len, uint8_t cmd) {
  if (!b || len < 2 || b[0] != 0x4F) return -1;
  int need = b[1] + 2;                                 // bytes de la trama completa
  if (need <= 3 || len < need) return -1;              // sin sitio para comando y checksum, o cortada
  if (wbXor(b, need - 1) != b[need - 1]) return -1;    // checksum
  if (b[2] != (uint8_t)(cmd | 0x80)) return -1;        // responde a otra orden
  return b[1] - 2;
}

// ---------- Versiones «a.b.c» ----------
// -1 si a < b, 0 si iguales, 1 si a > b. Lo que falta o no es un número cuenta como 0
inline int verCmpC(const char* a, const char* b) {
  int x[3] = {0, 0, 0}, y[3] = {0, 0, 0};
  int k = 0;                                           // cuántas partes se leyeron: no hace falta (lo que falta es 0)
  if (a) k += sscanf(a, "%d.%d.%d", &x[0], &x[1], &x[2]);
  if (b) k += sscanf(b, "%d.%d.%d", &y[0], &y[1], &y[2]);
  (void)k;
  for (int i = 0; i < 3; i++) if (x[i] != y[i]) return x[i] < y[i] ? -1 : 1;
  return 0;
}

// ---------- Código de instalación ----------
// 16 caracteres sin los que se confunden (0/O, 1/I), en grupos de 4
static const char IID_ABC[] = "23456789ABCDEFGHJKLMNPQRSTUVWXYZ";
// Normaliza lo escrito (mayúsculas, sin guiones ni espacios) y lo deja en out (20 bytes) con guiones; false si no vale
inline bool iidNorm(const char* in, char* out) {
  if (!in || !out) return false;
  char c16[17]; int n = 0;
  for (const char* p = in; *p; p++) {
    char c = (char)toupper((unsigned char)*p);
    if (c == '-' || c == ' ') continue;
    if (!c || !strchr(IID_ABC, c) || n >= 16) return false;
    c16[n++] = c;
  }
  if (n != 16) return false;
  for (int g = 0, k = 0; g < 4; g++) { for (int i = 0; i < 4; i++) out[k++] = c16[g * 4 + i]; out[k++] = g < 3 ? '-' : 0; }
  return true;
}

// ---------- Hora de salida ----------
// Minutos de antelación según la temperatura: 15 min a 15 °C o más, 1,5 min más por cada grado menos, hasta 60 (a
// −15 °C). Sin dato, 30. Es una estimación sencilla, no un cálculo del motor
inline int depLeadMin(float t) {
  if (isnan(t)) return 30;
  long v = lround(15 + (15 - t) * 1.5);
  return v < 15 ? 15 : v > 60 ? 60 : (int)v;
}

// ---------- Termostato: qué hacer (sin hacerlo) ----------
// thermoTick() en WTTC.ino le pasa el estado cada 5 s y hace lo que diga (encender, apagar, avisar)
enum ThAct {
  TH_NONE,          // nada
  TH_END_WINDOW,    // se acabó el tiempo de la sesión: terminar (y apagar si calienta)
  TH_NOSENS,        // sin termómetro: el termostato se deja (el encendido en marcha sigue hasta su fin)
  TH_GIVE_UP,       // calentando, dentro no sube: se da por vencido y apaga
  TH_REACHED,       // calentando, ha llegado (y lleva el mínimo): apaga
  TH_RESTART,       // apagada dentro de la sesión y se ha enfriado: volver a encender
};
struct ThIn {
  bool heaterOn;                      // calentando ahora
  uint32_t now, until;                // millis() de ahora y del fin de la sesión
  uint32_t heatStart, lastHeatOff;    // millis() del último encendido y del último apagado (0 = nunca)
  float cab, target;                  // °C de dentro (NAN = sin dato) y objetivo
  float best; uint32_t bestAt;        // lo más alto que ha llegado dentro en este encendido, y cuándo (NAN = aún nada)
  bool warm;                          // este encendido empezó con el agua caliente (mínimo más corto)
  bool reached;                       // en esta sesión ya llegó alguna vez al objetivo
};
struct ThOut {
  ThAct act = TH_NONE;
  bool newBest = false;               // dentro ha subido lo bastante: best = cab, bestAt = now
  bool atTarget = false;              // dentro ya está en el objetivo (para apuntar los minutos hasta llegar)
};
inline ThOut thDecide(const ThIn& s) {
  ThOut o;
  if ((int32_t)(s.now - s.until) >= 0) { o.act = TH_END_WINDOW; return o; }
  if (isnan(s.cab)) { o.act = TH_NOSENS; return o; }
  if (s.heaterOn) {
    o.atTarget = s.cab >= s.target;
    // ¿Sube la temperatura de dentro? Si en TH_STALL no ha subido TH_STALL_C y aún no llega, no se insiste
    o.newBest = isnan(s.best) || s.cab >= s.best + TH_STALL_C;
    uint32_t bestAt = o.newBest ? s.now : s.bestAt;
    if (!o.atTarget && s.now - bestAt >= TH_STALL) { o.act = TH_GIVE_UP; return o; }
    if (o.atTarget && s.now - s.heatStart >= (s.warm ? TH_MINRUN_WARM : TH_MINRUN)) o.act = TH_REACHED;
    return o;
  }
  // Apagada dentro de la sesión: vuelve a encender si se ha enfriado (o si aún no había llegado: un encendido dura
  // como mucho MAX_MIN), queda margen para un encendido mínimo y ya terminó el postbarrido
  uint32_t left = s.until - s.now;
  bool cold = s.cab <= s.target - TH_HYST || (!s.reached && s.cab < s.target);
  if (cold && left >= TH_MINRUN && (!s.lastHeatOff || s.now - s.lastHeatOff >= TH_REST)) o.act = TH_RESTART;
  return o;
}
