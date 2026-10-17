// webasto_modelo.h — Una Webasto Thermo Top C de mentira, en el W-Bus: lo que contesta y cómo evoluciona.
// Código generado íntegramente con Claude (Anthropic).
//
// Es el mismo modelo que el simulador de la web (web/index.php: hTick/hHandle), pasado a C++ sin nada de Arduino, para
// que lo usen el emulador (webasto_falsa.ino, en un segundo ESP32) y las pruebas en un ordenador
// (firmware/pruebas/webasto_test.cpp). Es una aproximación para probar WTTC sin furgoneta: fases y tiempos de arranque,
// llama, regulación por temperatura, postbarrido, averías y bloqueo; NO es el comportamiento exacto de una Webasto.
// Contrastado con libwbus (Manuel Jander, sourceforge.net/projects/libwbus: wbus_server.c y poeli.c, el «lado
// calefacción» de referencia del W-Bus): encender solo desde apagada, la orden se mantiene hasta apagarse del todo
// (0x44 sigue diciendo «sigo» en el postbarrido) y sin renovación en 20 s se apaga con la avería 0x92.
//
// Órdenes que entiende (las que usa WTTC):
//   0x21 n   encender n minutos            → responde [n] (solo enciende si está apagada del todo; si no, se ignora)
//   0x44 c 0 mantenimiento («sigue con c») → [0] si tiene esa orden (hasta apagarse del todo); [1] si no
//   0x10     apagar                          → [0]
//   0x50 05  sensores                         → [05, temp+50, mV alto, mV bajo, llama, W alto, W bajo, bujía alto, bajo]
//   0x56 01  lista de averías                 → [01, cuántas, código, veces, …];  0x56 03 → borrarlas → [03]
//
// Registro: si se le da una función en onLog, cuenta en texto cada cosa que hace y por qué (cambios de estado, averías,
// órdenes que ignora). El emulador la escribe por la consola; las pruebas la dejan vacía.
#pragma once
#include <stdint.h>
#include <string.h>
#include <stdio.h>
#include <stdarg.h>

namespace wf {

// Estados (con el código que daría la Webasto) y cuánto duran los que acaban solos (ms)
enum St { OFF, FAN, GLOW, IGN, STAB, FULL, PART, PAUSE, AFTER, FAIL, LOCK, ST_N };
static const char* const ST_NAME[ST_N] = {"apagada", "ventilador", "bujía", "encendido", "estabilizando", "a tope",
                                          "carga parcial", "pausa", "postbarrido", "fallo", "bloqueada"};
static const uint32_t ST_DUR[ST_N] = {0, 12000, 45000, 30000, 60000, 0, 0, 0, 120000, 90000, 0};
static const float AMPS[ST_N] = {0.02f, 2, 8.5f, 9, 4, 3.5f, 2.6f, 1.6f, 2, 2, 0.02f};
const float T_PART = 75, T_PAUSE = 85, T_RESUME = 70;   // °C del agua: baja a parcial, pausa y vuelve
const uint32_t REFRESH_MS = 20000;                      // sin mantenimiento (0x44) en este tiempo, se apaga sola

inline bool active(St s) { return s >= FAN && s <= PAUSE; }

// Nombre de las averías que puede dar este modelo (las demás, solo con su código)
inline const char* errName(uint8_t c) {
  switch (c) {
    case 0x02: return "no arranca (sin gasoil)";
    case 0x03: return "se apagó la llama";
    case 0x07: return "bloqueada (demasiados arranques fallidos)";
    case 0x92: return "nadie renovó la orden (falta el mantenimiento 0x44)";
    default:   return "avería";
  }
}

// Averías que se pueden provocar desde la consola del emulador (o en las pruebas)
struct Env {
  float amb = 5;          // °C fuera
  float batt = 12.6f;     // V de la batería sin carga
  bool noFuel = false;    // no prende (sin gasoil): avería 0x02, y bloqueo a la tercera
  bool flameOut = false;  // se le apaga la llama calentando: avería 0x03
  bool noPower = false;   // sin corriente (fusible): no contesta a nada
  bool mute = false;      // el W-Bus no contesta (cable suelto)
};

struct Webasto {
  Env env;
  St st = OFF;
  uint32_t t = 0;                   // ms en el estado actual
  float temp = 5, cab = 7;          // °C del agua y de dentro
  uint8_t cmd = 0;                  // orden vigente (0x21) o 0
  int32_t runLeft = 0;              // ms que quedan de la orden
  uint32_t refresh = 0;             // ms desde el último mantenimiento
  int tries = 0, fails = 0;         // intentos de encendido en este arranque y arranques fallidos seguidos
  uint16_t pw = 0;                  // W de potencia
  uint8_t errCode[8] = {}, errCnt[8] = {}; int errN = 0;   // averías guardadas (código, veces)
  void (*onLog)(const char* msg) = nullptr;   // registro en texto (ver arriba); nullptr = callado
  uint32_t starts = 0;              // arranques desde que se encendió el emulador (para el resumen)

  void say(const char* fmt, ...) {
    if (!onLog) return;
    char m[200]; va_list a; va_start(a, fmt); vsnprintf(m, sizeof m, fmt, a); va_end(a);
    onLog(m);
  }
  // Cambia de estado; why = por qué (sale en el registro)
  void set(St s, const char* why = nullptr) {
    if (s != st) say("estado: %s → %s%s%s%s", ST_NAME[st], ST_NAME[s], why ? " (" : "", why ? why : "", why ? ")" : "");
    st = s; t = 0;
  }
  void err(uint8_t c) {
    int i = 0;
    while (i < errN && errCode[i] != c) i++;
    if (i < errN) { if (errCnt[i] < 255) errCnt[i]++; }
    else if (errN < 8) { errCode[errN] = c; errCnt[errN] = 1; errN++; }
    else { say("avería 0x%02X: la memoria de averías está llena (8), no se guarda", c); return; }
    say("AVERÍA 0x%02X guardada: %s (%d %s)", c, errName(c), errCnt[i], errCnt[i] == 1 ? "vez" : "veces");
  }
  int flame() const { return st == STAB || st == FULL || st == PART ? 1 : 0; }
  float volt() const { if (env.noPower) return 0; float v = env.batt - AMPS[st] * 0.06f; return v < 8 ? 8 : v; }
  // Apagar: a postbarrido. La orden se mantiene hasta apagarse del todo (como libwbus: 0x44 sigue diciendo «sigo»)
  void stop(const char* why = "orden de apagar") {
    if (active(st)) set(AFTER, why);
    else say("apagar: ya estaba %s, no cambia nada", ST_NAME[st]);
  }
  // Encender: solo desde apagada del todo; calentando, en postbarrido o en fallo se ignora (no alarga el tiempo)
  void on(uint8_t min) {
    if (st == LOCK) { say("encender %d min: IGNORADO, está bloqueada (se borra con 0x56 03 o «borrar»)", min); err(0x07); return; }
    if (st != OFF) { say("encender %d min: IGNORADO, está %s (solo enciende desde apagada del todo)", min, ST_NAME[st]); return; }
    cmd = 0x21; runLeft = (int32_t)min * 60000; refresh = 0;
    tries = 0; starts++;
    say("encender %d min: aceptado (arranque nº %lu; mantenimiento cada <20 s o se apaga)", min, (unsigned long)starts);
    set(FAN, "empieza el arranque");
  }

  // Avanza dt ms (el emulador lo llama en cada vuelta, multiplicado si el tiempo va acelerado)
  void tick(uint32_t dt) {
    if (env.noPower) { if (st != LOCK) { st = OFF; cmd = 0; } }
    else {
      t += dt;
      if (active(st) && cmd) {
        runLeft -= (int32_t)dt; refresh += dt;
        if (refresh > REFRESH_MS) { err(0x92); cmd = 0; set(AFTER, "20 s sin mantenimiento 0x44"); }   // nadie renueva la orden
        else if (runLeft <= 0) stop("se acabó el tiempo pedido");
      }
      uint32_t d = ST_DUR[st];
      switch (st) {
        case FAN:  if (t >= d) set(GLOW, "ventilador listo: calienta la bujía"); break;
        case GLOW: if (t >= d) set(IGN, "bujía caliente: bomba de gasoil en marcha"); break;
        case IGN:
          if (t >= d) {
            if (!env.noFuel) { fails = 0; set(STAB, "¡llama!"); }
            else if (++tries < 2) set(GLOW, "no prende (sin gasoil): segundo intento");
            else {
              err(0x02);
              if (++fails >= 3) { err(0x07); cmd = 0; set(LOCK, "3 arranques fallidos seguidos"); }
              else { char w[100]; snprintf(w, sizeof w, "no prende tras 2 intentos; arranques fallidos seguidos: %d de 3", fails); set(FAIL, w); }
            }
          }
          break;
        case STAB: case FULL: case PART:
          if (env.flameOut) { err(0x03); set(FAIL, "se apagó la llama"); env.flameOut = false; break; }
          if (st == STAB && t >= d) set(temp >= T_PART ? PART : FULL, temp >= T_PART ? "llama estable y agua ya a 75 °C" : "llama estable");
          else if (st == FULL && temp >= T_PART) set(PART, "agua a 75 °C: baja la potencia");
          else if (st == PART && temp >= T_PAUSE) set(PAUSE, "agua a 85 °C: apaga la llama y espera");
          break;
        case PAUSE: if (temp <= T_RESUME) { tries = 0; set(FAN, "agua bajó a 70 °C: vuelve a arrancar"); } break;
        case AFTER: case FAIL:
          if (t >= d) { set(OFF, cmd ? "fin del postbarrido; suelta la orden" : "fin del postbarrido"); cmd = 0; }
          break;
        default: break;
      }
    }
    // Potencia y agua (≈ 9 L + bloque; aproximado) y habitáculo (si hay termómetro)
    pw = st == FULL ? 5000 : st == PART ? 2500 : st == STAB ? (uint16_t)(2500 + 2500 * (t > 60000 ? 1.0f : t / 60000.0f)) : 0;
    float loss = (active(st) ? 38 : 12) * (temp - env.amb);
    temp += (pw - loss) * (dt / 1000.0f) / 75000.0f;
    float heat = active(st) && temp > 30 ? (temp - cab) * 0.00012f : 0;
    cab += (heat - (cab - env.amb) * 0.00006f) * dt / 1000.0f;
  }

  // Una orden ya comprobada → datos de la respuesta (en out; devuelve cuántos), o -1 si no la entiende (no contesta)
  int handle(uint8_t c, const uint8_t* d, int n, uint8_t* out) {
    switch (c) {
      case 0x10: stop(); out[0] = 0; return 1;
      case 0x21: { uint8_t m = n ? d[0] : 0; on(m ? m : 1); out[0] = m; return 1; }
      case 0x44: { bool ok = n && cmd && cmd == d[0]; if (ok) refresh = 0; out[0] = ok ? 0 : 1; return 1; }
      case 0x50: {
        if (!n || d[0] != 0x05) return -1;
        int mv = (int)(volt() * 1000 + 0.5f), tt = (int)(temp + 0.5f) + 50, gpr = 1100 + (int)(temp * 6);
        if (tt < 0) tt = 0;
        if (tt > 255) tt = 255;
        uint8_t r[9] = {0x05, (uint8_t)tt, (uint8_t)(mv >> 8), (uint8_t)mv, (uint8_t)flame(), (uint8_t)(pw >> 8), (uint8_t)pw,
                        (uint8_t)(gpr >> 8), (uint8_t)gpr};
        memcpy(out, r, 9); return 9;
      }
      case 0x56:
        if (n && d[0] == 0x01) { int k = 0; out[k++] = 0x01; out[k++] = (uint8_t)errN; for (int i = 0; i < errN; i++) { out[k++] = errCode[i]; out[k++] = errCnt[i]; } return k; }
        if (n && d[0] == 0x03) { errN = 0; out[0] = 0x03; return 1; }
        return -1;
    }
    return -1;
  }
};

// ---------- Tramas ----------
// Lector de tramas hacia la Webasto (F4 · longitud · orden · datos · XOR), byte a byte: ignora lo que no empiece por
// F4 (el «despertar» del bus, ruido) y descarta las de checksum mal. Cuando hay una completa y buena, devuelve true
struct Reader {
  uint8_t b[64] = {}; int len = 0;
  bool feed(uint8_t c) {
    if (len == 0 && c != 0xF4) return false;
    b[len++] = c;
    if (len >= 2) {
      int need = b[1] + 2;
      if (need < 4 || need > (int)sizeof b) { len = 0; return false; }   // longitud imposible: se vuelve a buscar F4
      if (len == need) {
        uint8_t x = 0; for (int i = 0; i < need - 1; i++) x ^= b[i];
        bool ok = x == b[need - 1];
        if (!ok) { len = 0; return false; }
        return true;                                    // completa: frame() la da; reset() para la siguiente
      }
    }
    if (len >= (int)sizeof b) len = 0;
    return false;
  }
  uint8_t cmd() const { return b[2]; }
  const uint8_t* data() const { return b + 3; }
  int dataLen() const { return b[1] - 2; }
  int frameLen() const { return b[1] + 2; }
  void reset() { len = 0; }
};

// Arma la respuesta 4F · longitud · orden|0x80 · datos · XOR en out (hasta cap bytes). Devuelve su longitud o -1
inline int reply(uint8_t* out, int cap, uint8_t c, const uint8_t* d, int n) {
  if (n < 0 || n + 4 > cap) return -1;
  out[0] = 0x4F; out[1] = (uint8_t)(n + 2); out[2] = (uint8_t)(c | 0x80);
  if (n) memcpy(out + 3, d, n);
  uint8_t x = 0; for (int i = 0; i < n + 3; i++) x ^= out[i];
  out[n + 3] = x;
  return n + 4;
}

// La misma función está en firmware/WTTC/logica.h (WTTC): el sketch de la Webasto falsa no puede incluir ficheros de
// fuera de su carpeta, así que va repetida (8 líneas, con prueba en los dos sitios).
// Consola serie: junta lo que llega letra a letra (PuTTY, screen) o de golpe (Monitor serie de Arduino) hasta el Enter
// (CR, LF o los dos). Borrar (BS o DEL) quita la última letra. true = hay una línea completa en L.buf (cópiala antes
// de seguir leyendo: la siguiente letra empieza la línea nueva encima)
struct LineaSerie { char buf[160]; int n; };
inline bool lineaFeed(LineaSerie& L, char c) {
  if (c == '\r' || c == '\n') { if (!L.n) return false; L.buf[L.n] = 0; L.n = 0; return true; }
  if (c == 8 || c == 127) { if (L.n) L.n--; return false; }
  if ((unsigned char)c < 32) return false;
  if (L.n < (int)sizeof L.buf - 1) L.buf[L.n++] = c;
  return false;
}

}  // namespace wf
