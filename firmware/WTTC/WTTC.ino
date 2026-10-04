/*
  ============================================================================================================
  WTTC — controlador W-Bus para Webasto Thermo Top C (VW 7H0 010 398 J)
  ============================================================================================================

  >>> Código generado íntegramente con Claude (Anthropic), a partir de las indicaciones del autor del proyecto.
  >>> Todo el contenido de este repositorio (firmware, app, servidor, web y documentación) está generado con Claude.

  Qué es
  ------
  Sustituye al temporizador original de la calefacción auxiliar de agua Webasto Thermo Top C (la que montan
  de fábrica, por ejemplo, las VW T5) por un ESP32. El ESP32 habla el protocolo W-Bus de la Webasto a través
  de un transceptor TJA1020 y se maneja desde una app Android (Bluetooth LE) o desde el navegador (Wi-Fi).

  Hardware
  --------
    Placa:        ESP32 DevKitC (ESP-WROOM-32)
    Transceptor:  TJA1020 (TTL de 3,3 V <-> bus K-Line/LIN de un hilo a 12 V), borna LIN al cable W-Bus
    Alimentación: regulador LM2596 a 5,0 V desde el +12 V permanente del conector del temporizador
    Conexiones:   TX del TJA1020 -> IO16 (RX2 del ESP32) · RX del TJA1020 <- IO17 (TX2) · SLP -> 3V3

  Código y documentación
  ----------------------
    Repositorio:  https://github.com/matatunos/wttc
    Web:          https://wttc.favala.es (simulador, esquema, guías de instalación y uso)

  Compilar
  --------
    Arduino IDE:  placa "ESP32 Dev Module", núcleo ESP32 2.x o 3.x. Sin librerías externas.
                  Esquema de partición: "Huge APP (3MB No OTA/1MB SPIFFS)" (con Bluetooth + Wi-Fi no cabe en la normal).
    arduino-cli:  --fqbn esp32:esp32:esp32:PartitionScheme=huge_app
    La carpeta debe llamarse WTTC y contener WTTC.ino y web.h (la página web que sirve la placa).

  Cómo se maneja
  --------------
    - Bluetooth LE (principal): app Android «WTTC». Emparejamiento con PIN de 6 cifras; el PIN se genera
      al azar en el primer arranque y sale por la consola serie y en la web (apartado Configuración).
    - Wi-Fi propia "WTTC" -> http://192.168.4.1 (segunda opción). Se puede dejar siempre encendida,
      solo mientras calienta o solo a petición, para ahorrar batería. Tras arrancar siempre está 10 min encendida.
    - Si se configura una red con internet (casa o punto de acceso del móvil): http://wttc.local
    - Consola serie (115200 baudios): on [min] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset
    - Avisos por Telegram (opcional): token del bot y chat ID en Configuración. Solo salen si el ESP32 llega
      a una red con internet; para enviarlos enciende la Wi-Fi unos minutos.
    - Todo lo configurable (nombre, claves, PIN, modo de la Wi-Fi, Telegram, batería mínima) se cambia desde
      la web o la app y se guarda en la memoria de la placa: no hace falta tocar el código.

  Protocolo W-Bus (resumen)
  -------------------------
    2400 baudios, 8 bits, paridad par, 1 bit de parada (8E1), un solo hilo: cada byte que enviamos vuelve
    como eco por RX. Trama de petición:  F4 LL CMD DATOS… XOR   (F = emisor diagnóstico, 4 = calefactor)
    Respuesta de la Webasto:             4F LL CMD|0x80 DATOS… XOR
    LL = número de bytes que siguen (comando + datos + checksum). XOR = o-exclusivo de todos los anteriores.
    Órdenes usadas: 0x21 encender (minutos) · 0x44 mantener (cada 5 s) · 0x10 apagar ·
                    0x50 05 leer sensores · 0x56 01 leer averías. Basado en la documentación de libwbus.

  Seguridad
  ---------
    La propia Webasto necesita el mensaje de mantenimiento (0x44) cada pocos segundos: si la placa se cuelga
    o se desconecta, se apaga sola con su postbarrido. Cada encendido lleva su duración (máximo MAX_MIN).
  ============================================================================================================
*/

// ---------- bibliotecas (todas vienen con el núcleo ESP32; no hay que instalar nada) ----------
#include <WiFi.h>               // Wi-Fi: punto de acceso propio y conexión a otra red
#include <WebServer.h>          // servidor HTTP para la web de la placa
#include <ESPmDNS.h>            // nombre wttc.local en la red local
#include <Preferences.h>        // memoria no volátil (NVS): configuración, programas y contadores
#include <HTTPClient.h>         // cliente HTTP para enviar los avisos a Telegram
#include <WiFiClientSecure.h>   // HTTPS para Telegram
#include <BLEDevice.h>          // Bluetooth LE: dispositivo
#include <BLEServer.h>          // Bluetooth LE: servidor GATT (servicio y características)
#include <BLE2902.h>            // descriptor CCCD, necesario para las notificaciones
#include <BLESecurity.h>        // emparejamiento con PIN
#include <esp_gap_ble_api.h>    // funciones de bajo nivel para listar y borrar emparejamientos
#include <time.h>               // hora local (programas, registro)
#include <sys/time.h>           // settimeofday(): poner en hora desde el móvil
#include "web.h"                // INDEX_HTML: la página web completa (va aparte para que el preprocesador no la toque)

// ================== CONFIGURACIÓN FIJA ==================
// Valores que no cambian de una placa a otra. Lo demás se cambia desde la web o la app (Configuración).
#define WBUS_RX 16                    // pin del ESP32 que recibe del TJA1020 (borna TX de la placa)
#define WBUS_TX 17                    // pin del ESP32 que transmite al TJA1020 (borna RX de la placa)
const char*    HOSTNAME     = "wttc";                 // nombre en la red: http://wttc.local
const char*    TZ_INFO      = "CET-1CEST,M3.5.0,M10.5.0/3";  // zona horaria POSIX de Europe/Madrid (horario de verano incluido)
const uint16_t MAX_MIN      = 60;     // duración máxima por encendido (y por programa), en minutos
const uint32_t KEEPALIVE_MS = 5000;   // cada cuánto se le confirma a la Webasto que siga (mensaje 0x44), en ms
const uint32_t SENSOR_MS    = 8000;   // cada cuánto se leen los sensores (solo encendida o con la web/app abierta)
const int      PAUSE_TEMP   = 65;     // °C: sin llama y con el agua por encima de esto = pausa de regulación (normal)
const uint32_t NOFLAME_MS   = 300000; // ms: sin llama tanto tiempo (y con el agua fría) = se da por apagada
const float    FUEL_L_KWH   = 0.124;  // gasoil por kWh de calor: Thermo Top C ≈ 0,62 l/h a 5 kW (ficha). Estimación, ±20 %
const uint32_t WIFI_BOOT_MS = 600000; // ms que la Wi-Fi está encendida tras arrancar, en cualquier modo (rescate)
const uint32_t WIFI_ASK_MS  = 900000; // ms que la Wi-Fi está encendida al pedirla desde la app
const uint32_t WIFI_TAIL_MS = 600000; // modo «mientras calienta»: ms que sigue encendida tras apagarse la calefacción
#define FW_VERSION "0.1.2"   // debe coincidir con el fichero VERSION de la raíz del repo (lo comprueba la CI)

// UUID del servicio Bluetooth y sus tres características (la app Android usa exactamente los mismos)
#define BLE_SVC   "6e0a0001-7c1d-4b9a-9f3e-5a2c8d7e4b10"   // servicio WTTC (la app busca placas por este UUID)
#define BLE_STATE "6e0a0002-7c1d-4b9a-9f3e-5a2c8d7e4b10"   // lectura + notificación: estado en JSON
#define BLE_CMD   "6e0a0003-7c1d-4b9a-9f3e-5a2c8d7e4b10"   // escritura: órdenes en texto ("on 30", "off", "cfg"…)
#define BLE_RESP  "6e0a0004-7c1d-4b9a-9f3e-5a2c8d7e4b10"   // lectura + notificación: respuesta "orden:datos"
// ========================================================

// ---------- configuración guardada en la placa (estos son los valores por defecto) ----------
// Modos de la Wi-Fi propia: siempre encendida, solo mientras calienta, o solo cuando se pide
enum { WM_ALWAYS, WM_HEAT, WM_DEMAND };
char cfgName[30]   = "WTTC";          // nombre de la red Wi-Fi propia y del dispositivo Bluetooth
char cfgApPass[64] = "calefaccion";   // clave de la red propia (WPA2 exige 8 caracteres como mínimo)
uint32_t blePin    = 0;               // PIN Bluetooth de 6 cifras; 0 = generar uno al azar en el primer arranque
uint8_t wifiMode   = WM_HEAT;         // por defecto: Wi-Fi solo mientras calienta (y 10 min después)
float minVolt      = 12.0;            // V: con la batería por debajo, los programas no arrancan
char staSsid[33]   = "", staPass[64] = "";   // red con internet a la que unirse (opcional), y su contraseña

// ---------- Wi-Fi bajo demanda ----------
bool wifiActive = false;              // ¿está ahora mismo encendida la Wi-Fi?
uint32_t wifiUntil = 0;               // millis() hasta el que se quiere encendida (arranque, petición, avisos)
uint32_t lastHeatOff = 0, lastWeb = 0; // última vez que se apagó la calefacción / que alguien pidió la web

// ---------- Bluetooth ----------
// Las órdenes que llegan por Bluetooth se copian en esta estructura y viajan por una cola de FreeRTOS
struct BleCmd { char c[200]; };
QueueHandle_t bleQueue = nullptr;     // cola de órdenes: la llena la tarea del Bluetooth, la vacía loop()
volatile int bleConn = 0;             // número de móviles conectados ahora (volatile: lo cambia otra tarea)
volatile bool bleAdvPending = false;  // tras una desconexión hay que volver a anunciarse
BLECharacteristic *chState = nullptr, *chResp = nullptr;   // características de estado y de respuesta
uint32_t lastBleState = 0;            // última vez que se envió el estado por Bluetooth
bool rebootPending = false;           // reiniciar en la próxima vuelta de loop() (tras responder)

// ---------- objetos globales ----------
const uint8_t MAX_SCHED = 8;          // número máximo de programas semanales
HardwareSerial wbus(2);               // UART2 del ESP32: la del W-Bus
WebServer server(80);                 // servidor web en el puerto 80
Preferences prefs;                    // acceso a la memoria no volátil (espacio de nombres "webasto")

// Un programa semanal: activo, días (bit0 = lunes … bit6 = domingo), hora de inicio en minutos y duración
struct Sched { uint8_t en, days; uint16_t start, dur; };
Sched sch[MAX_SCHED];
uint8_t nSch = 0;                     // cuántos programas hay guardados
bool autoOn = true;                   // interruptor general de los programas

// ---------- estado de la calefacción ----------
bool heaterOn = false;                // ¿le hemos dado la orden de calentar y sigue vigente?
uint32_t onUntil = 0, onTotal = 0;    // millis() en que termina el encendido / duración total en segundos
uint32_t lastKA = 0, lastSensor = 0, lastUi = 0, lastComm = 0;   // última vez de: mantenimiento, sensores, web/app, bus
String onSrc = "";                    // quién la encendió: "manual", "app", "programa", "consola"
int kaFails = 0, kaOff = 0;           // mantenimientos seguidos sin respuesta / con respuesta «ya no la tengo»
// Estado real: 0 apagada, 1 arrancando, 2 con llama, 3 pausa de regulación, 4 sin respuesta
enum { PH_OFF, PH_START, PH_FLAME, PH_PAUSE, PH_LOST };
int phase = PH_OFF;
bool flameSeen = false;               // ¿ha habido llama en este encendido?
uint32_t noFlameSince = 0;            // desde cuándo no hay llama (0 = hay llama)
// Gasoil estimado (litros): encendido actual, último encendido, mes en curso y total
float gasCur = 0, gasLast = 0, gasMonth = 0, gasTotal = 0, gasRate = 0;   // gasRate en l/h
uint32_t gasMonthKey = 0, lastGasT = 0, lastGasSave = 0;  // mes contado (AAAAMM), última integración, último guardado
String stopNote = "";                 // por qué se apagó sola (se muestra en la web hasta el próximo encendido)
int busState = -1;                    // última comunicación por W-Bus: -1 sin probar, 0 sin respuesta, 1 OK
int tempC = -999, flame = -1, power = -1;   // último dato de sensores: °C del agua, llama (0/1), potencia (W); negativo = sin dato
float volt = -1;                      // tensión de la batería según la Webasto (V); -1 = sin dato
String lastTx, lastRx;                // última trama enviada y recibida, en hexadecimal (diagnóstico)
int lastMinute = -1;                  // último minuto en que se revisaron los programas

// Registro de los 20 últimos eventos (en RAM: se pierde al reiniciar)
String logBuf[20];
int logN = 0;

// ============================================================================================================
// Utilidades
// ============================================================================================================

// ¿Está la placa en hora? (no tiene pila de reloj: la hora llega por NTP o desde el móvil al conectar)
bool timeValid() { return time(nullptr) > 1700000000; }   // cualquier fecha posterior a noviembre de 2023

// Bytes en hexadecimal separados por espacios ("F4 03 21 1E C8"), para el diagnóstico
String hexs(const uint8_t* b, int n) {
  String s; char t[4];
  for (int i = 0; i < n; i++) { sprintf(t, "%02X ", b[i]); s += t; }
  s.trim();
  return s;
}

// Texto como cadena JSON entre comillas, escapando comillas y barras y quitando caracteres de control
String js(const String& s) {
  String o = "\"";
  for (unsigned i = 0; i < s.length(); i++) {
    char c = s[i];
    if (c == '"' || c == '\\') { o += '\\'; o += c; }   // \" y \\ en JSON
    else if ((uint8_t)c < 0x20) o += ' ';              // saltos de línea y demás: un espacio
    else o += c;
  }
  return o + "\"";
}

// Añade un evento al registro (con fecha si la hay) y lo saca también por la consola serie
void addLog(const String& m) {
  char ts[20] = "--/-- --:--";        // sin hora: guiones
  if (timeValid()) {
    time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
    strftime(ts, sizeof ts, "%d/%m %H:%M", &tm);
  }
  String e = String(ts) + "  " + m;
  Serial.println(e);
  if (logN < 20) logBuf[logN++] = e;  // aún hay hueco
  else { for (int i = 1; i < 20; i++) logBuf[i - 1] = logBuf[i]; logBuf[19] = e; }   // lleno: se desplaza y se pierde el más viejo
}

// Hora actual como "07:42 " (con espacio al final) o vacío si la placa no está en hora
String hhmm() {
  if (!timeValid()) return "";
  char ts[8]; time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  strftime(ts, sizeof ts, "%H:%M ", &tm);
  return ts;
}

// ============================================================================================================
// Avisos por Telegram
// Se encolan y los envía una tarea aparte: una conexión lenta (TLS tarda un par de segundos) no debe
// retrasar el mensaje de mantenimiento del W-Bus, que tiene que salir cada 5 s.
// ============================================================================================================
struct Msg { char t[240]; };          // un aviso pendiente de enviar
QueueHandle_t tgQueue = nullptr;      // cola de avisos (la llena notify(), la vacía tgTask())
char tgToken[64] = "", tgChat[24] = "";   // token del bot y chat ID (vacíos = avisos desactivados)
char tgLast[280] = "";                // resultado del último envío (lo escribe la tarea, lo lee la web)

// Codifica un texto para mandarlo en un formulario HTTP (los acentos y espacios van como %XX)
String urlenc(const char* s) {
  String o; char h[4];
  for (; *s; s++) {
    uint8_t c = *s;
    if (isalnum(c) || c == '-' || c == '_' || c == '.' || c == '~') o += (char)c;   // caracteres que no hace falta codificar
    else { sprintf(h, "%%%02X", c); o += h; }
  }
  return o;
}

// Pone un aviso en la cola (no bloquea). Si Telegram no está configurado, no hace nada.
void notify(const String& m) {
  if (!tgQueue || !tgToken[0] || !tgChat[0]) return;
  Msg x;
  snprintf(x.t, sizeof x.t, "Webasto · %s%s", hhmm().c_str(), m.c_str());   // "Webasto · 07:42 Encendida…"
  if (xQueueSend(tgQueue, &x, 0) != pdTRUE) strlcpy(tgLast, "Cola de avisos llena: se ha perdido uno", sizeof tgLast);
  // Con la Wi-Fi apagada por ahorro, se enciende unos minutos (3) para que el aviso pueda salir
  if (staSsid[0] && (int32_t)(wifiUntil - (millis() + 180000)) < 0) wifiUntil = millis() + 180000;
}

// Tarea de FreeRTOS que envía los avisos de la cola, uno tras otro, por HTTPS a la API de Telegram
void tgTask(void*) {
  Msg x;
  for (;;) {
    if (xQueueReceive(tgQueue, &x, portMAX_DELAY) != pdTRUE) continue;   // espera (dormida) a que haya un aviso
    // Espera a tener red (hasta 10 min: 120 × 5 s); si no llega, el aviso se pierde
    for (int i = 0; i < 120 && WiFi.status() != WL_CONNECTED; i++) vTaskDelay(pdMS_TO_TICKS(5000));
    if (WiFi.status() != WL_CONNECTED) { snprintf(tgLast, sizeof tgLast, "Perdido, sin red: %s", x.t); continue; }
    // Copia local del token y el chat: la configuración puede cambiar desde otra tarea mientras se envía
    char tok[64], chat[24];
    strlcpy(tok, tgToken, sizeof tok);
    strlcpy(chat, tgChat, sizeof chat);
    if (!tok[0] || !chat[0]) continue;
    WiFiClientSecure cli;
    cli.setInsecure();                // sin comprobar el certificado: la placa no lleva certificados raíz
    HTTPClient http;
    http.setConnectTimeout(8000);
    http.setTimeout(8000);
    int code = -1;
    if (http.begin(cli, String("https://api.telegram.org/bot") + tok + "/sendMessage")) {
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");
      code = http.POST(String("chat_id=") + urlenc(chat) + "&text=" + urlenc(x.t));
      http.end();
    }
    // El resultado se muestra en la web (Configuración → «Último aviso»)
    if (code == 200) snprintf(tgLast, sizeof tgLast, "Enviado: %s", x.t);
    else snprintf(tgLast, sizeof tgLast, "Error %d (sin internet, o token/chat mal): %s", code, x.t);
  }
}

// ============================================================================================================
// W-Bus: comunicación con la Webasto
// ============================================================================================================

// Pulso de "despertar" (break): antes de hablar tras un rato de silencio, la línea se pone a nivel bajo
// 50 ms y luego alto 50 ms. Para ello se suelta la UART y se maneja el pin TX a mano.
void wbusBreak() {
  wbus.end();
  pinMode(WBUS_TX, OUTPUT);
  digitalWrite(WBUS_TX, LOW);  delay(50);
  digitalWrite(WBUS_TX, HIGH); delay(50);
  wbus.begin(2400, SERIAL_8E1, WBUS_RX, WBUS_TX);
}

// Envía una orden y espera la respuesta.
//   Envía:  F4 LL CMD DATA.. CS     Descarta el eco.     Lee:  4F LL (CMD|0x80) DATA.. CS
//   resp recibe solo los bytes de datos (sin cabecera, longitud, comando ni checksum) y rlen su número.
//   Devuelve true si llegó una respuesta completa, con checksum correcto y del comando pedido.
bool wbusCmd(uint8_t cmd, const uint8_t* data, uint8_t n, uint8_t* resp, uint8_t& rlen) {
  rlen = 0;
  if (millis() - lastComm > 10000) wbusBreak();   // más de 10 s sin hablar: despertar el bus

  // Montar la trama: cabecera F4, longitud (comando + datos + checksum), comando, datos y XOR
  uint8_t f[40];
  f[0] = 0xF4; f[1] = n + 2; f[2] = cmd;
  if (n) memcpy(f + 3, data, n);
  uint8_t cs = 0;
  for (int i = 0; i < n + 3; i++) cs ^= f[i];
  f[n + 3] = cs;
  int flen = n + 4;

  while (wbus.available()) wbus.read();   // vaciar lo que hubiera pendiente
  wbus.write(f, flen);
  wbus.flush();                            // esperar a que salga el último bit

  // Eco: en un bus de un solo hilo recibimos lo que acabamos de enviar; se lee y se tira (máximo 200 ms)
  uint32_t t = millis(); int got = 0;
  while (got < flen && millis() - t < 200) if (wbus.available()) { wbus.read(); got++; }

  // Respuesta: se espera hasta 500 ms. Se ignora todo hasta un 0x4F (cabecera de la Webasto hacia nosotros).
  uint8_t b[64]; int len = 0, need = -1;
  t = millis();
  while (millis() - t < 500) {
    if (!wbus.available()) continue;
    uint8_t c = wbus.read();
    if (len == 0 && c != 0x4F) continue;
    b[len++] = c;
    if (len == 2) need = b[1] + 2;                        // ya sabemos cuántos bytes tendrá la respuesta
    if ((need > 0 && len >= need) || len >= 64) break;    // completa (o demasiado larga)
  }
  lastComm = millis();
  lastTx = hexs(f, flen);                                  // para la web: «Último envío»
  lastRx = len ? hexs(b, len) : "(sin respuesta)";         // para la web: «Última respuesta»

  // Validar: longitud completa, checksum (XOR de todo menos el último byte) y que responde a nuestro comando
  bool ok = need > 3 && len >= need;
  if (ok) {
    uint8_t c = 0;
    for (int i = 0; i < need - 1; i++) c ^= b[i];
    ok = (c == b[need - 1]) && (b[2] == (cmd | 0x80));
  }
  busState = ok ? 1 : 0;
  if (!ok) return false;
  rlen = b[1] - 2;                                         // datos = longitud − comando − checksum
  memcpy(resp, b + 3, rlen);
  return true;
}

// Lee los sensores de la Webasto (orden 0x50, registro 0x05). Formato de la respuesta (según libwbus):
//   05 · temperatura+50 · tensión (mV, 2 bytes) · llama (0/1) · potencia (W, 2 bytes) · …
bool readSensors() {
  uint8_t d[1] = {0x05}, r[64], n;
  bool ok = wbusCmd(0x50, d, 1, r, n) && n >= 4 && r[0] == 0x05;
  if (ok) {
    tempC = (int)r[1] - 50;                    // la temperatura viene desplazada 50 °C
    volt  = ((r[2] << 8) | r[3]) / 1000.0;     // milivoltios -> voltios
    if (n >= 5) flame = r[4];
    if (n >= 7) power = (r[5] << 8) | r[6];
  }
  lastSensor = millis();
  return ok;
}

// Lista de averías guardadas en la Webasto (orden 0x56 01), en texto: "0x02 (3), 0x07 (1)"
// Respuesta: 01 · número de averías · [código · veces] × número
String errorsText() {
  uint8_t d[1] = {0x01}, r[64], n;
  if (!wbusCmd(0x56, d, 1, r, n)) return "sin respuesta";
  if (n < 2 || !r[1]) return "ninguna guardada";
  String s; char c[20];
  for (int i = 0; i < r[1] && 3 + 2 * i < n; i++) {   // sin pasarse del final de la respuesta
    sprintf(c, "%s0x%02X (%d)", i ? ", " : "", r[2 + 2 * i], r[3 + 2 * i]);
    s += c;
  }
  return s;
}

// ============================================================================================================
// Gasoil estimado
// Se integra la potencia que informa la Webasto (W) por el tiempo; en pausa o sin llama no cuenta.
// No es una medida del depósito: la Webasto no sabe cuánto queda, solo cuánto quema.
// ============================================================================================================

// "0,25 l" (dos decimales por debajo de 10 litros, uno por encima), con coma decimal
String litros(float l) { String s = String(l, l < 10 ? 2 : 1); s.replace('.', ','); return s + " l"; }

// Guarda los contadores en la memoria no volátil
void gasSave() {
  prefs.begin("webasto", false);
  prefs.putFloat("glast", gasLast);
  prefs.putFloat("gmon", gasMonth);
  prefs.putFloat("gtot", gasTotal);
  prefs.putUInt("gkey", gasMonthKey);
  prefs.end();
  lastGasSave = millis();
}

// Al cambiar de mes, el contador del mes empieza de cero (solo si la placa está en hora)
void gasMonthCheck() {
  if (!timeValid()) return;
  time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  uint32_t key = (tm.tm_year + 1900) * 100 + tm.tm_mon + 1;   // p. ej. 202610 para octubre de 2026
  if (gasMonthKey && key != gasMonthKey) gasMonth = 0;
  gasMonthKey = key;
}

// Tras cada lectura de sensores con la calefacción encendida: suma lo gastado desde la anterior
// (con el ritmo calculado entonces) y calcula el ritmo nuevo para el siguiente intervalo.
void gasTick() {
  uint32_t now = millis();
  if (lastGasT) {
    float l = gasRate * (now - lastGasT) / 3600000.0;   // l/h × horas transcurridas
    gasCur += l; gasMonth += l; gasTotal += l;
  }
  lastGasT = now;
  // Ritmo hasta la próxima lectura: según la potencia que da la Webasto; con llama y sin dato, carga media (3,75 kW)
  gasRate = flame > 0 ? (power > 0 ? power / 1000.0 : 3.75) * FUEL_L_KWH : 0;
  gasMonthCheck();
  if (now - lastGasSave >= 900000) gasSave();     // cada 15 min como mucho: la flash tiene ciclos de escritura limitados
}

// ============================================================================================================
// Encender y apagar
// ============================================================================================================

// Enciende la calefacción durante «minutes» minutos (máximo MAX_MIN). src dice quién la enciende.
// Lo intenta 3 veces; devuelve true si la Webasto aceptó la orden.
bool startHeater(uint16_t minutes, const char* src) {
  minutes = constrain(minutes, 1, MAX_MIN);
  uint8_t d[1] = {(uint8_t)minutes}, r[64], n;
  for (int i = 0; i < 3; i++) {
    if (wbusCmd(0x21, d, 1, r, n)) {              // 0x21 = calefacción de estacionamiento, con su duración
      heaterOn = true;
      onTotal = minutes * 60UL;
      onUntil = millis() + minutes * 60000UL;
      lastKA = millis(); kaFails = 0; kaOff = 0;  // el mantenimiento empieza a contar desde ahora
      phase = PH_START; flameSeen = false; noFlameSince = millis();   // aún sin llama: arrancando
      stopNote = "";                              // se borra el aviso de un apagado anterior
      gasCur = 0; gasRate = 0; lastGasT = 0;      // gasoil de este encendido desde cero
      onSrc = src;
      addLog(String("Encendida (") + src + ", " + minutes + " min)");
      notify(String("Encendida (") + src + ", " + minutes + " min)");
      return true;
    }
    delay(300);                                   // pequeña pausa antes de reintentar
  }
  addLog("Error: la Webasto no respondió al encendido");
  notify(String("No se pudo encender (") + src + "): la Webasto no responde por W-Bus");
  return false;
}

// Apaga la calefacción (orden 0x10; la Webasto hace su postbarrido). why dice el motivo; tell = avisar por Telegram.
// Devuelve true si la Webasto confirmó el apagado.
bool stopHeater(const char* why, bool tell) {
  uint8_t r[64], n; bool ok = false;
  bool was = heaterOn;                            // ¿estaba encendida? (se puede pedir apagar estando ya apagada)
  for (int i = 0; i < 3 && !ok; i++) { ok = wbusCmd(0x10, nullptr, 0, r, n); if (!ok) delay(300); }
  // Cierra la cuenta del gasoil de este encendido y la guarda
  if (was) { gasTick(); gasRate = 0; lastGasT = 0; gasLast = gasCur; gasSave(); }
  heaterOn = false;
  phase = PH_OFF;
  lastHeatOff = millis();                         // para el modo de Wi-Fi «mientras calienta»
  String g = was ? String(" · gasoil ≈ ") + litros(gasLast) : String("");
  addLog(String("Apagada (") + why + (ok ? ")" : ", sin confirmación)") + g);
  if (tell) notify(String("Apagada (") + why + ")" + g);
  return ok;
}

// La Webasto ha dejado de calentar por su cuenta: se apunta, se leen sus averías y se avisa
void heaterQuit(const char* why) {
  stopHeater(why, false);                         // sin aviso propio: va uno más completo abajo
  String e = errorsText();
  addLog(String("Averías: ") + e);
  stopNote = hhmm() + "Se ha apagado sola: " + why + ". Averías: " + e + ".";   // se queda en la web y la app
  notify(String("Se ha apagado sola: ") + why + ". Averías: " + e + ". Gasoil ≈ " + litros(gasLast));
}

// Estado real a partir de la llama y la temperatura que da la propia Webasto (se llama tras cada lectura)
void evalHeater() {
  if (flame > 0) { flameSeen = true; noFlameSince = 0; phase = PH_FLAME; return; }   // hay llama: calentando
  if (flame < 0) return;                                        // la Webasto no da el dato: no se deduce nada
  // Sin llama pero con el agua caliente tras haber tenido llama: pausa de regulación (vuelve a prender sola)
  if (flameSeen && tempC >= PAUSE_TEMP) { phase = PH_PAUSE; noFlameSince = 0; return; }
  phase = PH_START;                                             // sin llama y agua fría: arrancando o reintentando
  if (!noFlameSince) noFlameSince = millis();
  // Respaldo: demasiado tiempo sin llama con el agua fría = se da por apagada
  if (millis() - noFlameSince >= NOFLAME_MS)
    heaterQuit(flameSeen ? "se ha apagado la llama" : "no ha llegado a prender");
}

// ============================================================================================================
// Configuración guardada (memoria no volátil, espacio de nombres "webasto")
// ============================================================================================================

// Lee todo lo guardado al arrancar. Lo que no exista se queda con el valor por defecto.
void loadCfg() {
  prefs.begin("webasto", true);                   // true = solo lectura
  nSch = prefs.getUChar("n", 0);
  if (nSch > MAX_SCHED) nSch = 0;                 // dato corrupto: sin programas
  if (nSch) prefs.getBytes("sch", sch, sizeof(Sched) * nSch);
  for (int i = 0; i < nSch; i++) if (sch[i].dur > MAX_MIN) sch[i].dur = MAX_MIN;   // programas de versiones anteriores (antes se permitían 4 h)
  autoOn = prefs.getBool("auto", true);
  // isKey() evita mensajes de error en la consola por claves que aún no existen
  if (prefs.isKey("tgtok"))  prefs.getString("tgtok", tgToken, sizeof tgToken);
  if (prefs.isKey("tgchat")) prefs.getString("tgchat", tgChat, sizeof tgChat);
  if (prefs.isKey("name"))   prefs.getString("name", cfgName, sizeof cfgName);
  if (prefs.isKey("appass")) prefs.getString("appass", cfgApPass, sizeof cfgApPass);
  if (prefs.isKey("ssid"))   prefs.getString("ssid", staSsid, sizeof staSsid);
  if (prefs.isKey("pass"))   prefs.getString("pass", staPass, sizeof staPass);
  blePin   = prefs.getUInt("pin", 0);
  gasLast  = prefs.getFloat("glast", 0);
  gasMonth = prefs.getFloat("gmon", 0);
  gasTotal = prefs.getFloat("gtot", 0);
  gasMonthKey = prefs.getUInt("gkey", 0);
  wifiMode = prefs.getUChar("wmode", WM_HEAT);
  minVolt  = prefs.getFloat("minv", 12.0);
  prefs.end();
  if (wifiMode > WM_DEMAND) wifiMode = WM_HEAT;   // valor imposible: el de por defecto
  if (blePin < 100000 || blePin > 999999) {       // primer arranque: PIN al azar, distinto en cada placa
    blePin = 100000 + esp_random() % 900000;      // esp_random() usa el generador de números aleatorios por hardware
    prefs.begin("webasto", false);
    prefs.putUInt("pin", blePin);
    prefs.end();
  }
}

// Guarda los programas y el interruptor general
void saveSched() {
  prefs.begin("webasto", false);
  prefs.putUChar("n", nSch);
  if (nSch) prefs.putBytes("sch", sch, sizeof(Sched) * nSch);
  else prefs.remove("sch");
  prefs.putBool("auto", autoOn);
  prefs.end();
}

// Programas en texto (los manda la web o la app): auto = "1"/"0"; lista = "en,días,inicio,duración;..."
// Se descartan las entradas mal formadas o fuera de rango; la duración se limita a MAX_MIN.
void applySched(const String& a, const String& L) {
  autoOn = a == "1";
  nSch = 0;
  int p = 0;
  while (p < (int)L.length() && nSch < MAX_SCHED) {
    int q = L.indexOf(';', p);
    if (q < 0) q = L.length();
    String it = L.substring(p, q);
    p = q + 1;
    int e, d, st, du;
    if (sscanf(it.c_str(), "%d,%d,%d,%d", &e, &d, &st, &du) == 4 && st >= 0 && st < 1440 && du > 0) {
      sch[nSch].en = e ? 1 : 0;
      sch[nSch].days = d & 0x7F;                  // solo los 7 bits de los días
      sch[nSch].start = st;                       // minutos desde las 00:00
      sch[nSch].dur = min(du, (int)MAX_MIN);
      nSch++;
    }
  }
  saveSched();
  addLog(String("Programas guardados (") + nSch + ")");
}

// Los programas en el mismo formato de texto, para la app ("1|1,31,420,30;0,96,600,15")
String schedText() {
  String s = autoOn ? "1|" : "0|";
  for (int i = 0; i < nSch; i++) {
    if (i) s += ";";
    s += sch[i].en; s += ","; s += sch[i].days; s += ","; s += sch[i].start; s += ","; s += sch[i].dur;
  }
  return s;
}

// Cambia un ajuste y lo guarda. Devuelve 0 si el valor no vale (err dice por qué), 1 si ya se aplica,
// 2 si hace falta reiniciar (nombre, claves, PIN y red externa solo se aplican al arrancar).
// Las claves vacías (appass, pass, tgtok) no cambian lo guardado: así no hay que volver a escribirlas.
int cfgSet(String k, String v, String& err) {
  v.trim();
  prefs.begin("webasto", false);
  int r = 1;
  if (k == "name") {
    if (v.length() < 1 || v.length() >= sizeof cfgName) { err = "El nombre debe tener entre 1 y 29 caracteres."; r = 0; }
    else if (v != cfgName) { strlcpy(cfgName, v.c_str(), sizeof cfgName); prefs.putString("name", cfgName); r = 2; }
  } else if (k == "appass") {
    if (!v.length()) r = 1;                       // vacío: se queda la clave que había
    else if (v.length() < 8 || v.length() >= sizeof cfgApPass) { err = "La clave de la Wi-Fi debe tener entre 8 y 63 caracteres."; r = 0; }
    else { strlcpy(cfgApPass, v.c_str(), sizeof cfgApPass); prefs.putString("appass", cfgApPass); r = 2; }
  } else if (k == "pin") {
    long n = v.toInt();
    if (v.length() != 6 || n < 100000 || n > 999999) { err = "El PIN debe tener 6 cifras y no empezar por 0."; r = 0; }
    else if ((uint32_t)n != blePin) { blePin = n; prefs.putUInt("pin", blePin); r = 2; }
  } else if (k == "wifimode") {
    int m = v.toInt();
    if (m < WM_ALWAYS || m > WM_DEMAND || !v.length()) { err = "Modo de Wi-Fi no válido."; r = 0; }
    else { wifiMode = m; prefs.putUChar("wmode", wifiMode); }   // se aplica al momento (loop() enciende o apaga)
  } else if (k == "ssid") {
    if (v.length() >= sizeof staSsid) { err = "Nombre de red demasiado largo."; r = 0; }
    else if (v != staSsid) { strlcpy(staSsid, v.c_str(), sizeof staSsid); prefs.putString("ssid", staSsid); r = 2; }
  } else if (k == "pass") {
    if (v.length() >= sizeof staPass) { err = "Contraseña demasiado larga."; r = 0; }
    else if (v.length()) { strlcpy(staPass, v.c_str(), sizeof staPass); prefs.putString("pass", staPass); r = 2; }
  } else if (k == "tgtok") {
    if (v.length() >= sizeof tgToken) { err = "Token demasiado largo."; r = 0; }
    else if (v.length()) { strlcpy(tgToken, v.c_str(), sizeof tgToken); prefs.putString("tgtok", tgToken); }
  } else if (k == "tgchat") {
    if (v.length() >= sizeof tgChat) { err = "Chat ID demasiado largo."; r = 0; }
    else { strlcpy(tgChat, v.c_str(), sizeof tgChat); prefs.putString("tgchat", tgChat); }   // vacío = avisos desactivados
  } else if (k == "minvolt") {
    float f = v.toFloat();
    if (f < 10.5 || f > 13.0) { err = "La batería mínima debe estar entre 10,5 y 13,0 V."; r = 0; }
    else { minVolt = f; prefs.putFloat("minv", minVolt); }
  } else { err = "Ajuste desconocido: " + k; r = 0; }
  prefs.end();
  return r;
}

// Ajustes que admite el formulario de configuración de la web (en este orden)
const char* CFG_KEYS[] = {"name", "appass", "pin", "wifimode", "ssid", "pass", "tgtok", "tgchat", "minvolt"};

// La configuración en JSON para la web y la app. Las claves (Wi-Fi y token) no se devuelven nunca.
String cfgJson() {
  String j = "{\"name\":"; j += js(String(cfgName));
  j += ",\"pin\":";      j += blePin;
  j += ",\"wifimode\":"; j += wifiMode;
  j += ",\"ssid\":";     j += js(String(staSsid));
  j += ",\"tg\":";       j += tgToken[0] ? "true" : "false";      // solo si hay token guardado, no el token
  j += ",\"tgchat\":";   j += js(String(tgChat));
  j += ",\"minvolt\":";  j += String(minVolt, 1);
  j += ",\"bonds\":";    j += esp_ble_get_bond_device_num();     // cuántos móviles están emparejados
  j += ",\"ver\":\"" FW_VERSION "\"}";
  return j;
}

// ============================================================================================================
// Wi-Fi bajo demanda
// La Wi-Fi es lo que más gasta con la furgoneta aparcada; según el modo se enciende solo cuando hace falta.
// ============================================================================================================

// ¿Debería estar encendida ahora la Wi-Fi?
bool wifiWanted() {
  uint32_t now = millis();
  if (wifiMode == WM_ALWAYS) return true;
  if ((int32_t)(wifiUntil - now) > 0) return true;               // arranque, petición desde la app o avisos pendientes
  if (lastWeb && now - lastWeb < 120000) return true;             // alguien está usando la web (2 min de margen)
  if (wifiMode == WM_HEAT && (heaterOn || (lastHeatOff && now - lastHeatOff < WIFI_TAIL_MS))) return true;
  return false;
}

// Enciende la red propia, se une a la red externa (si la hay) y arranca el servidor web y wttc.local
void wifiStart() {
  WiFi.setHostname(HOSTNAME);
  WiFi.mode(WIFI_AP_STA);                         // a la vez punto de acceso propio y cliente de otra red
  WiFi.softAP(cfgName, cfgApPass);
  if (staSsid[0]) WiFi.begin(staSsid, staPass);
  WiFi.setAutoReconnect(true);
  MDNS.begin(HOSTNAME);
  MDNS.addService("http", "tcp", 80);
  server.begin();
  wifiActive = true;
  addLog("Wi-Fi encendida");
}

// Apaga todo lo de la Wi-Fi para ahorrar (el Bluetooth sigue funcionando)
void wifiStop() {
  server.stop();
  MDNS.end();
  WiFi.disconnect(true);
  WiFi.softAPdisconnect(true);
  WiFi.mode(WIFI_OFF);
  wifiActive = false;
  lastWeb = 0;
  addLog("Wi-Fi apagada para ahorrar");
}

// ============================================================================================================
// Programas semanales: una vez por minuto se mira si toca encender
// ============================================================================================================
void checkSchedule() {
  if (!timeValid()) return;                       // sin hora no se puede saber si toca
  time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  if (tm.tm_min == lastMinute) return;            // ya revisado este minuto
  lastMinute = tm.tm_min;
  if (!autoOn || heaterOn) return;                // programas apagados, o ya está calentando

  uint8_t wd = (tm.tm_wday + 6) % 7;             // día de la semana con 0 = lunes (tm_wday tiene 0 = domingo)
  uint16_t m = tm.tm_hour * 60 + tm.tm_min;      // minuto del día
  for (int i = 0; i < nSch; i++) {
    if (sch[i].en && (sch[i].days & (1 << wd)) && sch[i].start == m) {
      // Antes de encender se mira la batería: con poca tensión, no se arranca (para poder arrancar el motor)
      readSensors();
      if (volt > 0 && volt < minVolt) {
        addLog(String("Programa omitido: batería a ") + String(volt, 1) + " V");
        notify(String("Programa omitido: batería a ") + String(volt, 1) + " V");
        return;
      }
      startHeater(sch[i].dur, "programa");
      return;
    }
  }
}

// ============================================================================================================
// Estado compacto para la app (por Bluetooth): tiene que caber en una notificación (MTU de hasta 517 bytes)
// ============================================================================================================
String stateJson() {
  int32_t rem = heaterOn ? (int32_t)(onUntil - millis()) / 1000 : 0;   // segundos que quedan
  if (rem < 0) rem = 0;
  String j = "{\"on\":"; j += heaterOn ? 1 : 0;   // encendida (1/0)
  j += ",\"ph\":";   j += phase;                  // estado real (PH_*)
  j += ",\"rem\":";  j += rem;                    // segundos restantes
  j += ",\"tot\":";  j += onTotal;                // duración total del encendido
  j += ",\"src\":";  j += js(onSrc);              // quién la encendió
  j += ",\"t\":";    j += tempC;                  // temperatura del agua
  j += ",\"v\":";    j += String(volt, 1);        // tensión de la batería
  j += ",\"fl\":";   j += flame;                  // llama
  j += ",\"pw\":";   j += power;                  // potencia
  j += ",\"bus\":";  j += busState;               // estado del W-Bus
  j += ",\"tv\":";   j += timeValid() ? 1 : 0;    // ¿en hora?
  j += ",\"time\":"; j += (uint32_t)time(nullptr);
  j += ",\"auto\":"; j += autoOn ? 1 : 0;         // programas activos
  j += ",\"wf\":";   j += wifiActive ? 1 : 0;     // ¿Wi-Fi encendida?
  j += ",\"wm\":";   j += wifiMode;               // modo de la Wi-Fi
  // Gasoil estimado: [encendido actual, último, mes, total]
  j += ",\"gas\":[";  j += String(gasCur, 2); j += ","; j += String(gasLast, 2); j += ",";
  j += String(gasMonth, 1); j += ","; j += String(gasTotal, 1); j += "]";
  j += ",\"note\":"; j += js(stopNote.substring(0, 200));   // recortado para que quepa
  j += "}";
  return j;
}

// ============================================================================================================
// Bluetooth LE
// Las órdenes llegan por la tarea del Bluetooth; se pasan por una cola y se ejecutan en loop(),
// para no hablar con la Webasto desde dos tareas a la vez (el W-Bus no admite dos conversaciones).
// ============================================================================================================

// Avisos de conexión y desconexión de un móvil
class SrvCb : public BLEServerCallbacks {
  void onConnect(BLEServer*) override { bleConn++; }
  // Al desconectarse se deja de anunciar; loop() vuelve a anunciarse (no se hace aquí, desde la tarea del Bluetooth)
  void onDisconnect(BLEServer*) override { if (bleConn > 0) bleConn--; bleAdvPending = true; }
};

// Cuando la app escribe una orden en la característica CMD: se copia y se encola
class CmdCb : public BLECharacteristicCallbacks {
  void onWrite(BLECharacteristic* c) override {
    auto v = c->getValue();                       // std::string en el núcleo 2.x, String en el 3.x: ambos tienen length() y c_str()
    BleCmd x;
    size_t n = v.length();
    if (n >= sizeof x.c) n = sizeof x.c - 1;      // recortar si es demasiado larga
    memcpy(x.c, v.c_str(), n);
    x.c[n] = 0;
    if (bleQueue) xQueueSend(bleQueue, &x, 0);    // sin esperar: si la cola está llena, la orden se pierde
  }
};

// Pone un valor en una característica y, si hay un móvil conectado, se lo notifica
void bleSet(BLECharacteristic* c, const String& v) {
  if (!c) return;
  c->setValue((uint8_t*)v.c_str(), v.length());
  if (bleConn > 0) c->notify();
}

// Arranca el Bluetooth: seguridad, servicio, características y anuncio
void bleInit() {
  BLEDevice::init(cfgName);                       // nombre con el que aparece en el móvil
  BLEDevice::setMTU(517);                         // paquetes grandes: el estado cabe en una sola notificación
  // Emparejamiento con PIN fijo de 6 cifras, con vínculo guardado (bonding) y cifrado.
  // La API cambió entre el núcleo 2.x y el 3.x: se compila la versión que toque.
  BLESecurity* sec = new BLESecurity();
#if ESP_ARDUINO_VERSION_MAJOR >= 3
  sec->setPassKey(true, blePin);                  // PIN fijo (no uno nuevo en cada conexión)
  sec->setCapability(ESP_IO_CAP_OUT);             // la placa «muestra» el PIN; el móvil lo pide al usuario
  sec->setAuthenticationMode(true, true, true);   // bonding, protección MITM, Secure Connections
#else
  sec->setStaticPIN(blePin);
  sec->setAuthenticationMode(ESP_LE_AUTH_REQ_SC_MITM_BOND);
#endif
  BLEServer* srv = BLEDevice::createServer();
  srv->setCallbacks(new SrvCb());
  BLEService* svc = srv->createService(BLE_SVC);
  // Permisos «cifrado con autenticación» en todo: sin emparejar no se puede leer ni escribir nada
  const uint16_t P = ESP_GATT_PERM_READ_ENC_MITM | ESP_GATT_PERM_WRITE_ENC_MITM;
  chState = svc->createCharacteristic(BLE_STATE, BLECharacteristic::PROPERTY_READ | BLECharacteristic::PROPERTY_NOTIFY);
  BLECharacteristic* chCmd = svc->createCharacteristic(BLE_CMD, BLECharacteristic::PROPERTY_WRITE);
  chResp  = svc->createCharacteristic(BLE_RESP, BLECharacteristic::PROPERTY_READ | BLECharacteristic::PROPERTY_NOTIFY);
  chState->setAccessPermissions(P);
  chCmd->setAccessPermissions(P);
  chResp->setAccessPermissions(P);
  // Descriptores 0x2902 (CCCD): la app los escribe para activar las notificaciones; también protegidos
  BLE2902* d1 = new BLE2902(); d1->setAccessPermissions(P); chState->addDescriptor(d1);
  BLE2902* d2 = new BLE2902(); d2->setAccessPermissions(P); chResp->addDescriptor(d2);
  chCmd->setCallbacks(new CmdCb());
  svc->start();
  // Anuncio: incluye el UUID del servicio para que la app encuentre las placas WTTC
  BLEAdvertising* adv = BLEDevice::getAdvertising();
  adv->addServiceUUID(BLE_SVC);
  adv->setScanResponse(true);
  adv->setMinInterval(800);           // anuncio cada 0,5–1 s (unidades de 0,625 ms): menos consumo esperando conexión
  adv->setMaxInterval(1600);
  BLEDevice::startAdvertising();
}

// Borra todos los dispositivos emparejados (si se pierde un móvil). Devuelve cuántos había.
int bleForgetAll() {
  int n = esp_ble_get_bond_device_num();
  if (n <= 0) return 0;
  esp_ble_bond_dev_t* l = (esp_ble_bond_dev_t*)malloc(sizeof(esp_ble_bond_dev_t) * n);
  if (!l) return 0;
  if (esp_ble_get_bond_device_list(&n, l) == ESP_OK)
    for (int i = 0; i < n; i++) esp_ble_remove_bond_device(l[i].bd_addr);
  free(l);
  addLog(String("Emparejamientos Bluetooth borrados (") + n + ")");
  return n;
}

// ============================================================================================================
// Órdenes en texto (las usan la app por Bluetooth y, en parte, la consola serie)
// Respuesta "orden:datos"; "orden:err texto" si algo falla. La app reconoce la respuesta por el prefijo.
// ============================================================================================================
String runCmd(String c) {
  c.trim();
  int sp = c.indexOf(' ');
  // k = la orden (primera palabra), a = el resto (argumentos)
  String k = sp < 0 ? c : c.substring(0, sp), a = sp < 0 ? String("") : c.substring(sp + 1);
  k.toLowerCase();
  if (k == "on") {                                 // on [minutos]
    int m = a.toInt();
    return startHeater(m > 0 ? m : 30, "app") ? "on:ok" : "on:err La Webasto no respondió al encendido.";
  }
  if (k == "off") return stopHeater("app", true) ? "off:ok" : "off:ok sin confirmación de la Webasto";
  if (k == "state") return "state:" + stateJson();
  if (k == "time") {                               // time <segundos desde 1970>: la app pone la placa en hora
    long e = a.toInt();
    if (e < 1700000000) return "time:err Hora no válida";
    struct timeval tv = { (time_t)e, 0 };
    settimeofday(&tv, nullptr);
    addLog("Hora ajustada desde la app");
    return "time:ok";
  }
  if (k == "sched") return "sched:" + schedText();
  if (k == "setsched") {                           // setsched <auto>|<lista>
    int b = a.indexOf('|');
    if (b < 0) return "setsched:err Formato";
    applySched(a.substring(0, b), a.substring(b + 1));
    return "setsched:ok";
  }
  if (k == "errors") return "errors:" + errorsJson();
  if (k == "log") {                                // los eventos más recientes que quepan en ~480 bytes
    String l;
    for (int i = logN - 1; i >= 0 && l.length() + logBuf[i].length() < 480; i--) { if (l.length()) l += "\n"; l += logBuf[i]; }
    return "log:" + l;
  }
  if (k == "cfg") return "cfg:" + cfgJson();
  if (k == "set") {                                // set clave=valor
    int e = a.indexOf('=');
    if (e < 0) return "set:err Formato";
    String err;
    int r = cfgSet(a.substring(0, e), a.substring(e + 1), err);
    if (r == 0) return String("set:err ") + err;
    return r == 2 ? String("set:restart") : String("set:ok");   // restart = se aplica al reiniciar
  }
  if (k == "wifi") { wifiUntil = millis() + WIFI_ASK_MS; return "wifi:ok"; }   // encender la Wi-Fi 15 min
  if (k == "tgtest") {                             // aviso de prueba por Telegram
    if (!tgToken[0] || !tgChat[0]) return "tgtest:err Primero guarda el token y el chat ID.";
    if (!staSsid[0]) return "tgtest:err Falta la red con internet (Configuración).";
    notify("Prueba de aviso");
    return "tgtest:ok";
  }
  if (k == "forget") { bleForgetAll(); return "forget:ok"; }
  if (k == "gasreset") { gasMonth = gasTotal = gasLast = 0; gasSave(); addLog("Contador de gasoil a cero"); return "gasreset:ok"; }
  if (k == "reboot") {
    if (heaterOn) return "reboot:err Está calentando: reiniciar la apagaría.";   // sin placa, la Webasto se apaga en segundos
    rebootPending = true;                          // se reinicia en loop(), después de mandar esta respuesta
    return "reboot:ok";
  }
  return k + ":err Orden desconocida";
}

// ============================================================================================================
// Servidor web (Wi-Fi). La página está en web.h (INDEX_HTML); aquí solo la API que usa.
// ============================================================================================================

// GET /api/state: estado completo para la web (la consulta cada 3 s mientras está abierta)
void handleState() {
  lastUi = millis();                              // con la web abierta se leen los sensores
  lastWeb = millis();                             // y la Wi-Fi no se apaga
  uint32_t now = millis();
  int32_t rem = heaterOn ? (int32_t)(onUntil - now) / 1000 : 0;
  if (rem < 0) rem = 0;

  String j; j.reserve(3000);                      // reservar memoria de una vez evita trocearla
  j += "{\"on\":";   j += heaterOn ? "true" : "false";
  j += ",\"remain\":"; j += rem;
  j += ",\"total\":";  j += onTotal;
  j += ",\"src\":";    j += js(onSrc);
  j += ",\"temp\":";   j += tempC;
  j += ",\"volt\":";   j += String(volt, 2);
  j += ",\"flame\":";  j += flame;
  j += ",\"pw\":";     j += power;
  j += ",\"bus\":";    j += busState;
  j += ",\"time\":";   j += (uint32_t)time(nullptr);
  j += ",\"tv\":";     j += timeValid() ? "true" : "false";
  j += ",\"auto\":";   j += autoOn ? "true" : "false";
  j += ",\"ph\":";     j += phase;
  j += ",\"gas\":[";   j += String(gasCur, 2); j += ","; j += String(gasLast, 2); j += ",";
  j += String(gasMonth, 1); j += ","; j += String(gasTotal, 1); j += "]";
  j += ",\"note\":";   j += js(stopNote);
  j += ",\"tg\":";     j += (tgToken[0] && tgChat[0]) ? "true" : "false";
  j += ",\"tgchat\":"; j += js(String(tgChat));
  j += ",\"tgl\":";    j += js(String(tgLast));
  j += ",\"wm\":";     j += wifiMode;
  j += ",\"ble\":";    j += bleConn;
  j += ",\"name\":";   j += js(String(cfgName));
  bool sta = WiFi.status() == WL_CONNECTED;       // ¿unida a la red con internet?
  j += ",\"sta\":";    j += sta ? "true" : "false";
  j += ",\"ssid\":";   j += js(sta ? WiFi.SSID() : String(""));
  j += ",\"ip\":";     j += js(sta ? WiFi.localIP().toString() : String(""));
  j += ",\"rssi\":";   j += sta ? WiFi.RSSI() : 0;   // intensidad de la señal (dBm)
  j += ",\"tx\":";     j += js(lastTx);
  j += ",\"rx\":";     j += js(lastRx);
  j += ",\"sch\":[";                              // programas como [activo, días, inicio, duración]
  for (int i = 0; i < nSch; i++) {
    if (i) j += ",";
    j += "["; j += sch[i].en; j += ","; j += sch[i].days; j += ",";
    j += sch[i].start; j += ","; j += sch[i].dur; j += "]";
  }
  j += "],\"log\":[";                             // registro, del más reciente al más antiguo
  for (int i = logN - 1; i >= 0; i--) { j += js(logBuf[i]); if (i) j += ","; }
  j += "]}";
  server.send(200, "application/json", j);
}

// POST /api/on (min=minutos): encender
void handleOn() {
  int m = server.arg("min").toInt();
  if (m <= 0) m = 30;
  bool ok = startHeater(m, "manual");
  server.send(ok ? 200 : 502, "text/plain", ok ? "ok" : "La Webasto no respondió al encendido.");
}

// POST /api/off: apagar
void handleOff() {
  bool ok = stopHeater("manual", true);
  server.send(200, "text/plain", ok ? "ok" : "Apagada sin confirmación de la Webasto.");
}

// POST /api/sched (auto, list): guardar programas
void handleSched() {
  applySched(server.arg("auto"), server.arg("list"));
  server.send(200, "text/plain", "ok");
}

// POST /api/time (epoch): poner en hora desde el navegador
void handleTime() {
  long e = server.arg("epoch").toInt();
  if (e < 1700000000) { server.send(400, "text/plain", "Hora no válida"); return; }
  struct timeval tv = { (time_t)e, 0 };
  settimeofday(&tv, nullptr);
  addLog("Hora ajustada desde el móvil");
  server.send(200, "text/plain", "ok");
}

// Averías en JSON: {"ok":…, "raw":"<trama>", "codes":[{"c":"02","n":3}, …]}
String errorsJson() {
  uint8_t d[1] = {0x01}, r[64], n;
  bool ok = wbusCmd(0x56, d, 1, r, n);
  String j = "{\"ok\":"; j += ok ? "true" : "false";
  j += ",\"raw\":"; j += js(lastRx);
  j += ",\"codes\":[";
  if (ok && n >= 2) {
    uint8_t cnt = r[1];
    for (int i = 0; i < cnt && 3 + 2 * i < n; i++) {
      char c[3]; sprintf(c, "%02X", r[2 + 2 * i]);
      if (i) j += ",";
      j += "{\"c\":\""; j += c; j += "\",\"n\":"; j += r[3 + 2 * i]; j += "}";
    }
  }
  j += "]}";
  return j;
}

// POST /api/cfg: guarda los ajustes que vengan en el formulario; si alguno necesita reiniciar, reinicia (salvo calentando)
void handleCfgPost() {
  bool restart = false;
  String err;
  for (const char* k : CFG_KEYS) {
    if (!server.hasArg(k)) continue;              // solo los campos que se han enviado
    int r = cfgSet(k, server.arg(k), err);
    if (r == 0) { server.send(400, "text/plain", err); return; }   // el primer valor incorrecto corta y se explica
    if (r == 2) restart = true;
  }
  addLog("Configuración guardada");
  if (!restart) { server.send(200, "text/plain", "Guardado."); return; }
  if (heaterOn) { server.send(200, "text/plain", "Guardado. Se aplicará al reiniciar (ahora está calentando: reiniciar la apagaría)."); return; }
  server.send(200, "text/plain", "Guardado. Reiniciando para aplicarlo; vuelve a conectarte en unos segundos (si cambiaste el nombre o la clave de la Wi-Fi, con los nuevos).");
  rebootPending = true;
}

// POST /api/tgtest: aviso de prueba por Telegram
void handleTgTest() {
  String r = runCmd("tgtest");
  if (r.startsWith("tgtest:err ")) { server.send(400, "text/plain", r.substring(11)); return; }
  server.send(200, "text/plain", "Enviando… mira «Último aviso» en unos segundos.");
}

// ============================================================================================================
// Consola serie (115200 baudios): para la primera prueba con el ESP32 conectado al PC por USB
// ============================================================================================================
void serialCli() {
  if (!Serial.available()) return;
  String c = Serial.readStringUntil('\n');
  c.trim();
  String l = c; l.toLowerCase();                  // l en minúsculas para comparar; c conserva el original (claves)
  if (l.startsWith("on")) { int m = l.substring(2).toInt(); startHeater(m > 0 ? m : 30, "consola"); }
  else if (l == "off") stopHeater("consola", true);
  else if (l == "status") {
    readSensors();
    static const char* PH[] = {"apagada", "arrancando", "con llama", "pausa de regulación", "sin respuesta"};
    Serial.printf("Estado: %s | Temp %d C | %.2f V | llama %d | %d W\nTX: %s\nRX: %s\n",
                  PH[phase], tempC, volt, flame, power, lastTx.c_str(), lastRx.c_str());
    Serial.println(String("Gasoil estimado: encendido ") + litros(gasCur) + " | último " + litros(gasLast) +
                   " | mes " + litros(gasMonth) + " | total " + litros(gasTotal));
    if (stopNote.length()) Serial.println(stopNote);
  }
  else if (l == "errores") Serial.println(errorsJson());
  else if (l == "cfg") Serial.println(cfgJson());
  else if (l.startsWith("set ") || l == "wifi" || l == "forget" || l == "reboot" || l == "gasreset") Serial.println(runCmd(c));
  else if (l.length()) Serial.println("Comandos: on [min] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset");
}

// ============================================================================================================
// Arranque
// ============================================================================================================
void setup() {
  Serial.begin(115200);
  Serial.setTimeout(200);                         // readStringUntil() no espera más de 200 ms
  setCpuFrequencyMhz(80);             // suficiente para W-Bus, web y Bluetooth; gasta menos que a 240 MHz
  wbus.begin(2400, SERIAL_8E1, WBUS_RX, WBUS_TX);
  loadCfg();
  tgQueue = xQueueCreate(6, sizeof(Msg));         // hasta 6 avisos en espera
  bleQueue = xQueueCreate(4, sizeof(BleCmd));     // hasta 4 órdenes de la app en espera
  // Tarea de Telegram en el núcleo 0 (el del Wi-Fi); loop() corre en el núcleo 1
  xTaskCreatePinnedToCore(tgTask, "telegram", 10240, nullptr, 1, nullptr, 0);

  configTzTime(TZ_INFO, "pool.ntp.org", "time.google.com");   // hora por internet cuando haya red

  // Rutas del servidor web
  server.on("/", HTTP_GET, [] { server.send_P(200, "text/html", INDEX_HTML); });   // la página (desde la flash)
  server.on("/api/state", HTTP_GET, handleState);
  server.on("/api/on", HTTP_POST, handleOn);
  server.on("/api/off", HTTP_POST, handleOff);
  server.on("/api/sched", HTTP_POST, handleSched);
  server.on("/api/time", HTTP_POST, handleTime);
  server.on("/api/errors", HTTP_GET, [] { server.send(200, "application/json", errorsJson()); });
  server.on("/api/cfg", HTTP_GET, [] { server.send(200, "application/json", cfgJson()); });
  server.on("/api/cfg", HTTP_POST, handleCfgPost);
  server.on("/api/tgtest", HTTP_POST, handleTgTest);
  server.on("/api/gasreset", HTTP_POST, [] { runCmd("gasreset"); server.send(200, "text/plain", "Contador de gasoil a cero."); });
  server.on("/api/forget", HTTP_POST, [] { int n = bleForgetAll(); server.send(200, "text/plain", String("Borrados ") + n + " emparejamientos."); });
  server.onNotFound([] { server.sendHeader("Location", "/"); server.send(302); });   // cualquier otra ruta: a la página

  bleInit();
  wifiUntil = millis() + WIFI_BOOT_MS;   // rescate: Wi-Fi encendida los primeros minutos en cualquier modo
  wifiStart();

  addLog(String("Arranque, firmware " FW_VERSION));
  // El PIN Bluetooth sale aquí: es la forma de conocerlo la primera vez (o en la web, Configuración)
  Serial.printf("WTTC %s | Bluetooth y Wi-Fi: \"%s\" | PIN Bluetooth: %06u\n", FW_VERSION, cfgName, (unsigned)blePin);
  Serial.println("Consola: on [min] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset");
}

// ============================================================================================================
// Bucle principal: atiende la web, la consola y la app, decide la Wi-Fi, mantiene viva la orden de calentar
// y revisa los programas. Nada aquí debe bloquear mucho: el mantenimiento tiene que salir cada 5 s.
// ============================================================================================================
void loop() {
  if (wifiActive) server.handleClient();          // peticiones de la web
  serialCli();                                    // órdenes de la consola serie

  // Órdenes de la app (llegan por la tarea del Bluetooth); la respuesta va por la característica RESP
  BleCmd bc;
  while (bleQueue && xQueueReceive(bleQueue, &bc, 0) == pdTRUE) {
    bleSet(chResp, runCmd(String(bc.c)));
    lastBleState = 0;                  // estado nuevo enseguida
  }
  if (bleConn > 0) {
    lastUi = millis();                 // con la app conectada se leen los sensores como con la web abierta
    if (millis() - lastBleState >= 2000) { bleSet(chState, stateJson()); lastBleState = millis(); }   // estado cada 2 s
  }
  if (bleAdvPending && bleConn == 0) { delay(100); BLEDevice::startAdvertising(); bleAdvPending = false; }   // volver a anunciarse

  // Wi-Fi según el modo elegido
  bool want = wifiWanted();
  if (want && !wifiActive) wifiStart();
  else if (!want && wifiActive) wifiStop();

  if (rebootPending) { delay(800); ESP.restart(); }   // la respuesta ya ha salido: reiniciar

  uint32_t now = millis();
  if (heaterOn) {
    if ((int32_t)(now - onUntil) >= 0) stopHeater("fin de tiempo", true);   // se acabó el tiempo pedido
    else if (now - lastKA >= KEEPALIVE_MS) {
      // Mensaje de mantenimiento: «sigue con la orden 0x21». Sin él, la Webasto se apaga sola.
      uint8_t d[2] = {0x21, 0x00}, r[64], n;
      lastKA = now;
      if (wbusCmd(0x44, d, 2, r, n)) {
        if (kaFails >= 5) addLog("La Webasto vuelve a responder");
        kaFails = 0;
        // Respuesta 00: sigue con la orden. 01: ya no la tiene, se ha apagado por su cuenta (dos seguidas para descartar errores)
        if (n >= 1 && r[0] == 0x01) { if (++kaOff >= 2) heaterQuit("ya no tiene la orden de calentar"); }
        else kaOff = 0;
      } else if (++kaFails == 5) {                   // 25 s sin respuesta: avisar
        phase = PH_LOST;
        addLog("Aviso: la Webasto no responde al mantenimiento");
        notify("La Webasto no responde por W-Bus. Sin mantenimiento se apaga sola en unos segundos; revisa el cableado.");
      } else if (kaFails >= 24) {                    // 2 minutos sin respuesta: se da por apagada
        stopHeater("sin comunicación con la Webasto", false);
        stopNote = hhmm() + "Se perdió la comunicación con la Webasto. Sin mantenimiento se apaga sola, pero compruébalo.";
        notify("Sigue sin responder tras 2 minutos: la doy por apagada. Compruébalo en la furgo.");
      }
    }
  }
  // Sensores: cada SENSOR_MS si está encendida o hay alguien mirando (web abierta o app conectada en los últimos 15 s)
  if ((heaterOn || now - lastUi < 15000) && now - lastSensor >= SENSOR_MS) {
    if (readSensors() && heaterOn) { gasTick(); evalHeater(); }
  }
  checkSchedule();                                // ¿toca encender por programa?
}
