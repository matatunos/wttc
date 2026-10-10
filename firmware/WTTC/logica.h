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

// ---------- Órdenes remotas (ver cmdPoll en WTTC.ino y server/api/orden.php) ----------
// El servidor devuelve dos líneas: el mensaje y su firma ECDSA P-256 (DER, en hexadecimal), hecha con la misma clave
// que las actualizaciones. Mensaje: «WTTCCMD1|<código de instalación>|<número>|<caduca (s UNIX)>|<orden>».
// Aquí solo se separa y se comprueba el formato; la firma la verifica el firmware (mbedtls)
enum CmdKind { CMD_NONE, CMD_UPDATE, CMD_CHECK, CMD_LOGSEND, CMD_REBOOT, CMD_DIAG_ON, CMD_DIAG_OFF };
enum CmdErr { CE_OK, CE_FORMAT, CE_OTHER_BOARD, CE_OLD, CE_EXPIRED, CE_UNKNOWN };
struct Cmd { char iid[20]; uint32_t seq; uint32_t exp; CmdKind kind; };
const uint32_t CMD_MAX_LIFE = 3600;    // s: una orden no puede caducar más tarde de una hora desde ahora

// Hexadecimal a bytes (sin espacios). Devuelve cuántos, o -1 si no es hexadecimal o no cabe
inline int hexDecode(const char* h, uint8_t* out, int cap) {
  if (!h || !out) return -1;
  int n = 0;
  for (; h[0] && h[1]; h += 2) {
    auto v = [](char c) -> int { return c >= '0' && c <= '9' ? c - '0' : c >= 'a' && c <= 'f' ? c - 'a' + 10 : c >= 'A' && c <= 'F' ? c - 'A' + 10 : -1; };
    int a = v(h[0]), b = v(h[1]);
    if (a < 0 || b < 0 || n >= cap) return -1;
    out[n++] = (uint8_t)(a << 4 | b);
  }
  return *h ? -1 : n;                                  // número impar de cifras
}

// Separa la respuesta en mensaje (msg, hasta msgCap bytes con el cero) y firma (sig, hasta sigCap bytes)
inline bool cmdSplit(const char* body, char* msg, int msgCap, uint8_t* sig, int sigCap, int& sigLen) {
  sigLen = 0;
  if (!body || !msg || msgCap < 2) return false;
  const char* nl = strchr(body, '\n');
  if (!nl) return false;
  int ml = (int)(nl - body);
  if (ml < 1 || ml >= msgCap) return false;
  memcpy(msg, body, ml); msg[ml] = 0;
  char hx[300]; int hl = 0;                            // la firma, sin el salto final ni espacios
  for (const char* p = nl + 1; *p && *p != '\n' && *p != '\r'; p++) { if (hl >= (int)sizeof hx - 1) return false; hx[hl++] = *p; }
  hx[hl] = 0;
  sigLen = hexDecode(hx, sig, sigCap);
  return sigLen >= 8;
}

// Lee el mensaje. CE_FORMAT si no tiene la forma esperada; CE_UNKNOWN si la orden no es de la lista cerrada
inline CmdErr cmdParse(const char* m, Cmd& c) {
  memset(&c, 0, sizeof c);
  if (!m || strncmp(m, "WTTCCMD1|", 9)) return CE_FORMAT;
  const char* p = m + 9;
  const char* bar = strchr(p, '|');
  if (!bar || bar - p != 19) return CE_FORMAT;
  memcpy(c.iid, p, 19); c.iid[19] = 0;
  p = bar + 1;
  auto num = [](const char*& q, uint32_t& out) -> bool {   // cifras hasta «|», sin pasarse de 32 bits
    uint64_t v = 0; int k = 0;
    while (*q >= '0' && *q <= '9') { v = v * 10 + (uint64_t)(*q - '0'); if (v > 0xFFFFFFFFull || ++k > 10) return false; q++; }
    if (!k || *q != '|') return false;
    q++; out = (uint32_t)v; return true;
  };
  if (!num(p, c.seq) || !num(p, c.exp)) return CE_FORMAT;
  static const struct { const char* s; CmdKind k; } L[] = {
    {"update", CMD_UPDATE}, {"check", CMD_CHECK}, {"logsend", CMD_LOGSEND}, {"reboot", CMD_REBOOT},
    {"diag-on", CMD_DIAG_ON}, {"diag-off", CMD_DIAG_OFF}};
  for (const auto& e : L) if (!strcmp(p, e.s)) { c.kind = e.k; return CE_OK; }
  return CE_UNKNOWN;
}

// ¿Vale para esta placa, ahora? (la firma ya comprobada aparte)
inline CmdErr cmdCheck(const Cmd& c, const char* myIid, uint32_t lastSeq, uint32_t nowEpoch) {
  if (!myIid || strcmp(c.iid, myIid)) return CE_OTHER_BOARD;
  if (c.seq <= lastSeq) return CE_OLD;                 // ya ejecutada (o una vieja reenviada)
  if (nowEpoch > c.exp || c.exp > nowEpoch + CMD_MAX_LIFE) return CE_EXPIRED;
  return CE_OK;
}

// ---------- Hora de salida: antelación aprendida ----------
// De los encendidos con objetivo que llegaron (registro de encendidos: dentro al empezar, objetivo, minutos hasta
// llegar), a cuántos °C por minuto calienta esta furgoneta: la mediana de (objetivo − dentro) / minutos de los más
// recientes que valen (subir 3 °C o más, en 3–240 min). Devuelve la tasa, o 0 si hay menos de LEARN_MIN encendidos
const int LEARN_MIN = 3, LEARN_MAX = 10;
inline float heatRate(const int8_t* cab0, const uint8_t* tgt, const uint8_t* treach, int n, int& used) {
  float r[LEARN_MAX]; used = 0;
  for (int i = n - 1; i >= 0 && used < LEARN_MAX; i--) {      // del más reciente al más antiguo
    if (!tgt[i] || cab0[i] == -128 || treach[i] < 3 || treach[i] > 240) continue;
    int d = (int)tgt[i] - cab0[i];
    if (d < 3) continue;
    r[used++] = (float)d / treach[i];
  }
  if (used < LEARN_MIN) return 0;
  for (int a = 1; a < used; a++) for (int b = a; b > 0 && r[b] < r[b - 1]; b--) { float t = r[b]; r[b] = r[b - 1]; r[b - 1] = t; }
  return used % 2 ? r[used / 2] : (r[used / 2 - 1] + r[used / 2]) / 2;
}
// Minutos de antelación para llegar a tgt desde tNow con esa tasa: +15 % y 5 min de margen (el arranque), entre 10 y
// DEP_MAX_LEAD. Sin tasa aprendida o sin temperatura, la fórmula fija (depLeadMin)
const int DEP_MAX_LEAD = 90;
inline int depLeadLearned(float rate, float tNow, int tgt, int fallback) {
  if (rate <= 0 || isnan(tNow) || !tgt) return fallback;
  float need = tgt - tNow;
  if (need <= 0) return 10;
  long m = lround(need / rate * 1.15f) + 5;
  return m < 10 ? 10 : m > DEP_MAX_LEAD ? DEP_MAX_LEAD : (int)m;
}

// ---------- Programas: «solo si hace frío» ----------
// cold = umbral en °C (SCH_NOCOLD = sin condición); t = temperatura de dentro (o del agua sin termómetro; NAN = sin
// dato). ¿Se salta? Solo si hay dato y no hace frío: sin dato se enciende (mejor gastar que quedarse sin calefacción)
const int8_t SCH_NOCOLD = 127;
inline bool coldSkip(int8_t cold, float t) { return cold != SCH_NOCOLD && !isnan(t) && t >= cold; }
