/*
  WTTC — controlador W-Bus para Webasto Thermo Top C (VW 7H0 010 398 J)

  Placa:        ESP32 DevKitC (ESP-WROOM-32)
  Código:       https://github.com/matatunos/wttc  ·  web: https://wttc.favala.es
  Transceptor:  TJA1020 (TTL <-> K-Line / LIN)
  Arduino IDE:  placa "ESP32 Dev Module", core ESP32 2.x o 3.x. Sin librerías externas.
                Esquema de partición: "Huge APP (3MB No OTA/1MB SPIFFS)" (con Bluetooth + Wi-Fi no cabe en la normal).
                arduino-cli: --fqbn esp32:esp32:esp32:PartitionScheme=huge_app

  Acceso:
    - Bluetooth LE (principal): app Android «WTTC». Emparejamiento con PIN de 6 cifras;
      el PIN se genera al primer arranque y sale por la consola serie y en la web (Configuración).
    - Wi-Fi propia "WTTC" -> http://192.168.4.1 (segunda opción). Se puede dejar siempre encendida,
      solo mientras calienta o solo a petición, para ahorrar batería. Tras arrancar siempre está 10 min encendida.
    - Si se configura una red con internet (casa o punto de acceso del móvil): http://wttc.local
  Consola serie (115200): on [min] | off | status | errores | cfg
  Avisos por Telegram (opcional): token del bot y chat ID en Configuración.
    Solo salen si el ESP32 llega a una red con internet; para enviarlos enciende la Wi-Fi unos minutos.
  Todo lo configurable (nombre, claves, PIN, modo de la Wi-Fi, Telegram, batería mínima) se cambia
  desde la web o la app y se guarda en la placa: no hace falta tocar el código.
*/
#include <WiFi.h>
#include <WebServer.h>
#include <ESPmDNS.h>
#include <Preferences.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <BLEDevice.h>
#include <BLEServer.h>
#include <BLE2902.h>
#include <BLESecurity.h>
#include <esp_gap_ble_api.h>
#include <time.h>
#include <sys/time.h>
#include "web.h"

// ================== CONFIGURACIÓN FIJA ==================
// (lo demás se cambia desde la web o la app, apartado Configuración)
#define WBUS_RX 16                    // <- RXD del TJA1020
#define WBUS_TX 17                    // -> TXD del TJA1020
const char*    HOSTNAME     = "wttc";                 // http://wttc.local
const char*    TZ_INFO      = "CET-1CEST,M3.5.0,M10.5.0/3";  // Europe/Madrid
const uint16_t MAX_MIN      = 60;     // duración máxima por encendido (y por programa)
const uint32_t KEEPALIVE_MS = 5000;   // cada cuánto se confirma a la Webasto que siga
const uint32_t SENSOR_MS    = 8000;   // lectura de sensores (solo encendida o con la web/app abierta)
const int      PAUSE_TEMP   = 65;     // sin llama y con el agua por encima: pausa de regulación (normal)
const uint32_t NOFLAME_MS   = 300000; // sin llama tanto tiempo (y con el agua fría): se da por apagada
const float    FUEL_L_KWH   = 0.124;  // gasoil por kWh de calor: Thermo Top C ≈ 0,62 l/h a 5 kW (ficha). Estimación, ±20 %
const uint32_t WIFI_BOOT_MS = 600000; // Wi-Fi encendida tras arrancar, en cualquier modo (rescate)
const uint32_t WIFI_ASK_MS  = 900000; // Wi-Fi encendida al pedirla desde la app
const uint32_t WIFI_TAIL_MS = 600000; // modo «mientras calienta»: sigue encendida tras apagarse
#define FW_VERSION "1.0.0"
// UUID del servicio Bluetooth (los mismos en la app)
#define BLE_SVC   "6e0a0001-7c1d-4b9a-9f3e-5a2c8d7e4b10"
#define BLE_STATE "6e0a0002-7c1d-4b9a-9f3e-5a2c8d7e4b10"   // lectura + notificación: estado en JSON
#define BLE_CMD   "6e0a0003-7c1d-4b9a-9f3e-5a2c8d7e4b10"   // escritura: órdenes en texto
#define BLE_RESP  "6e0a0004-7c1d-4b9a-9f3e-5a2c8d7e4b10"   // lectura + notificación: respuesta "orden:datos"
// ========================================================

// Configuración guardada en la placa (valores por defecto)
enum { WM_ALWAYS, WM_HEAT, WM_DEMAND };
char cfgName[30]   = "WTTC";          // red Wi-Fi propia y nombre Bluetooth
char cfgApPass[64] = "calefaccion";   // clave de la red propia (mínimo 8)
uint32_t blePin    = 0;               // PIN Bluetooth; 0 = generar uno al azar en el primer arranque
uint8_t wifiMode   = WM_HEAT;
float minVolt      = 12.0;            // por debajo, los programas no arrancan
char staSsid[33]   = "", staPass[64] = "";

// Wi-Fi bajo demanda
bool wifiActive = false;
uint32_t wifiUntil = 0;               // hasta cuándo se quiere encendida (arranque, petición, avisos)
uint32_t lastHeatOff = 0, lastWeb = 0;

// Bluetooth
struct BleCmd { char c[200]; };
QueueHandle_t bleQueue = nullptr;
volatile int bleConn = 0;
volatile bool bleAdvPending = false;
BLECharacteristic *chState = nullptr, *chResp = nullptr;
uint32_t lastBleState = 0;
bool rebootPending = false;

const uint8_t MAX_SCHED = 8;
HardwareSerial wbus(2);
WebServer server(80);
Preferences prefs;

struct Sched { uint8_t en, days; uint16_t start, dur; };   // days: bit0=lunes .. bit6=domingo
Sched sch[MAX_SCHED];
uint8_t nSch = 0;
bool autoOn = true;

bool heaterOn = false;
uint32_t onUntil = 0, onTotal = 0, lastKA = 0, lastSensor = 0, lastUi = 0, lastComm = 0;
String onSrc = "";
int kaFails = 0, kaOff = 0;
// Estado real: 0 apagada, 1 arrancando, 2 con llama, 3 pausa de regulación, 4 sin respuesta
enum { PH_OFF, PH_START, PH_FLAME, PH_PAUSE, PH_LOST };
int phase = PH_OFF;
bool flameSeen = false;
uint32_t noFlameSince = 0;
// Gasoil estimado (litros): encendido actual, último encendido, mes en curso y total
float gasCur = 0, gasLast = 0, gasMonth = 0, gasTotal = 0, gasRate = 0;   // gasRate en l/h
uint32_t gasMonthKey = 0, lastGasT = 0, lastGasSave = 0;
String stopNote = "";                 // por qué se apagó sola (se muestra en la web hasta el próximo encendido)
int busState = -1;                    // -1 sin probar, 0 sin respuesta, 1 OK
int tempC = -999, flame = -1, power = -1;
float volt = -1;
String lastTx, lastRx;
int lastMinute = -1;

String logBuf[20];
int logN = 0;

// ---------- utilidades ----------
bool timeValid() { return time(nullptr) > 1700000000; }

String hexs(const uint8_t* b, int n) {
  String s; char t[4];
  for (int i = 0; i < n; i++) { sprintf(t, "%02X ", b[i]); s += t; }
  s.trim();
  return s;
}

String js(const String& s) {
  String o = "\"";
  for (unsigned i = 0; i < s.length(); i++) {
    char c = s[i];
    if (c == '"' || c == '\\') { o += '\\'; o += c; }
    else if ((uint8_t)c < 0x20) o += ' ';
    else o += c;
  }
  return o + "\"";
}

void addLog(const String& m) {
  char ts[20] = "--/-- --:--";
  if (timeValid()) {
    time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
    strftime(ts, sizeof ts, "%d/%m %H:%M", &tm);
  }
  String e = String(ts) + "  " + m;
  Serial.println(e);
  if (logN < 20) logBuf[logN++] = e;
  else { for (int i = 1; i < 20; i++) logBuf[i - 1] = logBuf[i]; logBuf[19] = e; }
}

String hhmm() {                       // "07:42 " o vacío si no hay hora
  if (!timeValid()) return "";
  char ts[8]; time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  strftime(ts, sizeof ts, "%H:%M ", &tm);
  return ts;
}

// ---------- avisos por Telegram ----------
// Se encolan y los envía una tarea aparte: una conexión lenta no retrasa el keep-alive del W-Bus
struct Msg { char t[240]; };
QueueHandle_t tgQueue = nullptr;
char tgToken[64] = "", tgChat[24] = "";
char tgLast[280] = "";                // resultado del último envío (lo escribe la tarea, lo lee la web)

String urlenc(const char* s) {
  String o; char h[4];
  for (; *s; s++) {
    uint8_t c = *s;
    if (isalnum(c) || c == '-' || c == '_' || c == '.' || c == '~') o += (char)c;
    else { sprintf(h, "%%%02X", c); o += h; }
  }
  return o;
}

void notify(const String& m) {
  if (!tgQueue || !tgToken[0] || !tgChat[0]) return;
  Msg x;
  snprintf(x.t, sizeof x.t, "Webasto · %s%s", hhmm().c_str(), m.c_str());
  if (xQueueSend(tgQueue, &x, 0) != pdTRUE) strlcpy(tgLast, "Cola de avisos llena: se ha perdido uno", sizeof tgLast);
  // Con la Wi-Fi apagada por ahorro, se enciende unos minutos para que el aviso pueda salir
  if (staSsid[0] && (int32_t)(wifiUntil - (millis() + 180000)) < 0) wifiUntil = millis() + 180000;
}

void tgTask(void*) {
  Msg x;
  for (;;) {
    if (xQueueReceive(tgQueue, &x, portMAX_DELAY) != pdTRUE) continue;
    // Espera a tener red (hasta 10 min); si no llega, el aviso se pierde
    for (int i = 0; i < 120 && WiFi.status() != WL_CONNECTED; i++) vTaskDelay(pdMS_TO_TICKS(5000));
    if (WiFi.status() != WL_CONNECTED) { snprintf(tgLast, sizeof tgLast, "Perdido, sin red: %s", x.t); continue; }
    char tok[64], chat[24];
    strlcpy(tok, tgToken, sizeof tok);
    strlcpy(chat, tgChat, sizeof chat);
    if (!tok[0] || !chat[0]) continue;
    WiFiClientSecure cli;
    cli.setInsecure();                // sin comprobar el certificado: la placa no lleva CA
    HTTPClient http;
    http.setConnectTimeout(8000);
    http.setTimeout(8000);
    int code = -1;
    if (http.begin(cli, String("https://api.telegram.org/bot") + tok + "/sendMessage")) {
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");
      code = http.POST(String("chat_id=") + urlenc(chat) + "&text=" + urlenc(x.t));
      http.end();
    }
    if (code == 200) snprintf(tgLast, sizeof tgLast, "Enviado: %s", x.t);
    else snprintf(tgLast, sizeof tgLast, "Error %d (sin internet, o token/chat mal): %s", code, x.t);
  }
}

// ---------- W-Bus ----------
// Pulso de "despertar" (break) antes de hablar tras un rato de silencio
void wbusBreak() {
  wbus.end();
  pinMode(WBUS_TX, OUTPUT);
  digitalWrite(WBUS_TX, LOW);  delay(50);
  digitalWrite(WBUS_TX, HIGH); delay(50);
  wbus.begin(2400, SERIAL_8E1, WBUS_RX, WBUS_TX);
}

// Envía F4 LL CMD DATA.. CS, descarta el eco y lee la respuesta 4F LL (CMD|0x80) DATA.. CS
// resp recibe los bytes de datos (sin cabecera, longitud, comando ni checksum)
bool wbusCmd(uint8_t cmd, const uint8_t* data, uint8_t n, uint8_t* resp, uint8_t& rlen) {
  rlen = 0;
  if (millis() - lastComm > 10000) wbusBreak();

  uint8_t f[40];
  f[0] = 0xF4; f[1] = n + 2; f[2] = cmd;
  if (n) memcpy(f + 3, data, n);
  uint8_t cs = 0;
  for (int i = 0; i < n + 3; i++) cs ^= f[i];
  f[n + 3] = cs;
  int flen = n + 4;

  while (wbus.available()) wbus.read();
  wbus.write(f, flen);
  wbus.flush();

  // Eco: en bus de un solo hilo recibimos lo que acabamos de enviar
  uint32_t t = millis(); int got = 0;
  while (got < flen && millis() - t < 200) if (wbus.available()) { wbus.read(); got++; }

  uint8_t b[64]; int len = 0, need = -1;
  t = millis();
  while (millis() - t < 500) {
    if (!wbus.available()) continue;
    uint8_t c = wbus.read();
    if (len == 0 && c != 0x4F) continue;
    b[len++] = c;
    if (len == 2) need = b[1] + 2;
    if ((need > 0 && len >= need) || len >= 64) break;
  }
  lastComm = millis();
  lastTx = hexs(f, flen);
  lastRx = len ? hexs(b, len) : "(sin respuesta)";

  bool ok = need > 3 && len >= need;
  if (ok) {
    uint8_t c = 0;
    for (int i = 0; i < need - 1; i++) c ^= b[i];
    ok = (c == b[need - 1]) && (b[2] == (cmd | 0x80));
  }
  busState = ok ? 1 : 0;
  if (!ok) return false;
  rlen = b[1] - 2;
  memcpy(resp, b + 3, rlen);
  return true;
}

bool readSensors() {
  uint8_t d[1] = {0x05}, r[64], n;
  bool ok = wbusCmd(0x50, d, 1, r, n) && n >= 4 && r[0] == 0x05;
  if (ok) {
    tempC = (int)r[1] - 50;
    volt  = ((r[2] << 8) | r[3]) / 1000.0;
    if (n >= 5) flame = r[4];
    if (n >= 7) power = (r[5] << 8) | r[6];
  }
  lastSensor = millis();
  return ok;
}

// Lista de averías guardadas en la Webasto, en texto: "0x02 (3), 0x07 (1)"
String errorsText() {
  uint8_t d[1] = {0x01}, r[64], n;
  if (!wbusCmd(0x56, d, 1, r, n)) return "sin respuesta";
  if (n < 2 || !r[1]) return "ninguna guardada";
  String s; char c[20];
  for (int i = 0; i < r[1] && 3 + 2 * i < n; i++) {
    sprintf(c, "%s0x%02X (%d)", i ? ", " : "", r[2 + 2 * i], r[3 + 2 * i]);
    s += c;
  }
  return s;
}

// ---------- gasoil estimado ----------
// Se integra la potencia que informa la Webasto (W) por el tiempo; en pausa o sin llama no cuenta.
String litros(float l) { String s = String(l, l < 10 ? 2 : 1); s.replace('.', ','); return s + " l"; }

void gasSave() {
  prefs.begin("webasto", false);
  prefs.putFloat("glast", gasLast);
  prefs.putFloat("gmon", gasMonth);
  prefs.putFloat("gtot", gasTotal);
  prefs.putUInt("gkey", gasMonthKey);
  prefs.end();
  lastGasSave = millis();
}

void gasMonthCheck() {                // al cambiar de mes, el contador del mes empieza de cero
  if (!timeValid()) return;
  time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  uint32_t key = (tm.tm_year + 1900) * 100 + tm.tm_mon + 1;
  if (gasMonthKey && key != gasMonthKey) gasMonth = 0;
  gasMonthKey = key;
}

void gasTick() {
  uint32_t now = millis();
  if (lastGasT) {
    float l = gasRate * (now - lastGasT) / 3600000.0;
    gasCur += l; gasMonth += l; gasTotal += l;
  }
  lastGasT = now;
  // Ritmo hasta la próxima lectura: según la potencia que da la Webasto; con llama y sin dato, carga media
  gasRate = flame > 0 ? (power > 0 ? power / 1000.0 : 3.75) * FUEL_L_KWH : 0;
  gasMonthCheck();
  if (now - lastGasSave >= 900000) gasSave();     // cada 15 min como mucho: la flash tiene ciclos limitados
}

bool startHeater(uint16_t minutes, const char* src) {
  minutes = constrain(minutes, 1, MAX_MIN);
  uint8_t d[1] = {(uint8_t)minutes}, r[64], n;
  for (int i = 0; i < 3; i++) {
    if (wbusCmd(0x21, d, 1, r, n)) {
      heaterOn = true;
      onTotal = minutes * 60UL;
      onUntil = millis() + minutes * 60000UL;
      lastKA = millis(); kaFails = 0; kaOff = 0;
      phase = PH_START; flameSeen = false; noFlameSince = millis();
      stopNote = "";
      gasCur = 0; gasRate = 0; lastGasT = 0;
      onSrc = src;
      addLog(String("Encendida (") + src + ", " + minutes + " min)");
      notify(String("Encendida (") + src + ", " + minutes + " min)");
      return true;
    }
    delay(300);
  }
  addLog("Error: la Webasto no respondió al encendido");
  notify(String("No se pudo encender (") + src + "): la Webasto no responde por W-Bus");
  return false;
}

bool stopHeater(const char* why, bool tell) {
  uint8_t r[64], n; bool ok = false;
  bool was = heaterOn;
  for (int i = 0; i < 3 && !ok; i++) { ok = wbusCmd(0x10, nullptr, 0, r, n); if (!ok) delay(300); }
  if (was) { gasTick(); gasRate = 0; lastGasT = 0; gasLast = gasCur; gasSave(); }
  heaterOn = false;
  phase = PH_OFF;
  lastHeatOff = millis();
  String g = was ? String(" · gasoil ≈ ") + litros(gasLast) : String("");
  addLog(String("Apagada (") + why + (ok ? ")" : ", sin confirmación)") + g);
  if (tell) notify(String("Apagada (") + why + ")" + g);
  return ok;
}

// La Webasto ha dejado de calentar por su cuenta: se apunta, se leen sus averías y se avisa
void heaterQuit(const char* why) {
  stopHeater(why, false);
  String e = errorsText();
  addLog(String("Averías: ") + e);
  stopNote = hhmm() + "Se ha apagado sola: " + why + ". Averías: " + e + ".";
  notify(String("Se ha apagado sola: ") + why + ". Averías: " + e + ". Gasoil ≈ " + litros(gasLast));
}

// Estado real a partir de la llama y la temperatura que da la propia Webasto (tras cada lectura)
void evalHeater() {
  if (flame > 0) { flameSeen = true; noFlameSince = 0; phase = PH_FLAME; return; }
  if (flame < 0) return;                                        // la Webasto no da el dato
  if (flameSeen && tempC >= PAUSE_TEMP) { phase = PH_PAUSE; noFlameSince = 0; return; }
  phase = PH_START;                                             // arrancando o reintentando
  if (!noFlameSince) noFlameSince = millis();
  if (millis() - noFlameSince >= NOFLAME_MS)
    heaterQuit(flameSeen ? "se ha apagado la llama" : "no ha llegado a prender");
}

// ---------- configuración guardada ----------
void loadCfg() {
  prefs.begin("webasto", true);
  nSch = prefs.getUChar("n", 0);
  if (nSch > MAX_SCHED) nSch = 0;
  if (nSch) prefs.getBytes("sch", sch, sizeof(Sched) * nSch);
  for (int i = 0; i < nSch; i++) if (sch[i].dur > MAX_MIN) sch[i].dur = MAX_MIN;   // programas de versiones anteriores
  autoOn = prefs.getBool("auto", true);
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
  if (wifiMode > WM_DEMAND) wifiMode = WM_HEAT;
  if (blePin < 100000 || blePin > 999999) {       // primer arranque: PIN al azar, distinto en cada placa
    blePin = 100000 + esp_random() % 900000;
    prefs.begin("webasto", false);
    prefs.putUInt("pin", blePin);
    prefs.end();
  }
}

void saveSched() {
  prefs.begin("webasto", false);
  prefs.putUChar("n", nSch);
  if (nSch) prefs.putBytes("sch", sch, sizeof(Sched) * nSch);
  else prefs.remove("sch");
  prefs.putBool("auto", autoOn);
  prefs.end();
}

// Programas en texto: auto = "1"/"0"; lista = "en,días,inicio,duración;..."
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
      sch[nSch].days = d & 0x7F;
      sch[nSch].start = st;
      sch[nSch].dur = min(du, (int)MAX_MIN);
      nSch++;
    }
  }
  saveSched();
  addLog(String("Programas guardados (") + nSch + ")");
}

String schedText() {
  String s = autoOn ? "1|" : "0|";
  for (int i = 0; i < nSch; i++) {
    if (i) s += ";";
    s += sch[i].en; s += ","; s += sch[i].days; s += ","; s += sch[i].start; s += ","; s += sch[i].dur;
  }
  return s;
}

// Cambia un ajuste y lo guarda. Devuelve 0 si el valor no vale, 1 si ya se aplica, 2 si hace falta reiniciar.
// Las claves vacías (appass, pass, tgtok) no cambian lo guardado.
int cfgSet(String k, String v, String& err) {
  v.trim();
  prefs.begin("webasto", false);
  int r = 1;
  if (k == "name") {
    if (v.length() < 1 || v.length() >= sizeof cfgName) { err = "El nombre debe tener entre 1 y 29 caracteres."; r = 0; }
    else if (v != cfgName) { strlcpy(cfgName, v.c_str(), sizeof cfgName); prefs.putString("name", cfgName); r = 2; }
  } else if (k == "appass") {
    if (!v.length()) r = 1;
    else if (v.length() < 8 || v.length() >= sizeof cfgApPass) { err = "La clave de la Wi-Fi debe tener entre 8 y 63 caracteres."; r = 0; }
    else { strlcpy(cfgApPass, v.c_str(), sizeof cfgApPass); prefs.putString("appass", cfgApPass); r = 2; }
  } else if (k == "pin") {
    long n = v.toInt();
    if (v.length() != 6 || n < 100000 || n > 999999) { err = "El PIN debe tener 6 cifras y no empezar por 0."; r = 0; }
    else if ((uint32_t)n != blePin) { blePin = n; prefs.putUInt("pin", blePin); r = 2; }
  } else if (k == "wifimode") {
    int m = v.toInt();
    if (m < WM_ALWAYS || m > WM_DEMAND || !v.length()) { err = "Modo de Wi-Fi no válido."; r = 0; }
    else { wifiMode = m; prefs.putUChar("wmode", wifiMode); }
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
    else { strlcpy(tgChat, v.c_str(), sizeof tgChat); prefs.putString("tgchat", tgChat); }
  } else if (k == "minvolt") {
    float f = v.toFloat();
    if (f < 10.5 || f > 13.0) { err = "La batería mínima debe estar entre 10,5 y 13,0 V."; r = 0; }
    else { minVolt = f; prefs.putFloat("minv", minVolt); }
  } else { err = "Ajuste desconocido: " + k; r = 0; }
  prefs.end();
  return r;
}

const char* CFG_KEYS[] = {"name", "appass", "pin", "wifimode", "ssid", "pass", "tgtok", "tgchat", "minvolt"};

String cfgJson() {
  String j = "{\"name\":"; j += js(String(cfgName));
  j += ",\"pin\":";      j += blePin;
  j += ",\"wifimode\":"; j += wifiMode;
  j += ",\"ssid\":";     j += js(String(staSsid));
  j += ",\"tg\":";       j += tgToken[0] ? "true" : "false";      // el token no se devuelve nunca
  j += ",\"tgchat\":";   j += js(String(tgChat));
  j += ",\"minvolt\":";  j += String(minVolt, 1);
  j += ",\"bonds\":";    j += esp_ble_get_bond_device_num();
  j += ",\"ver\":\"" FW_VERSION "\"}";
  return j;
}

// ---------- Wi-Fi bajo demanda ----------
bool wifiWanted() {
  uint32_t now = millis();
  if (wifiMode == WM_ALWAYS) return true;
  if ((int32_t)(wifiUntil - now) > 0) return true;               // arranque, petición desde la app o avisos
  if (lastWeb && now - lastWeb < 120000) return true;             // alguien está usando la web
  if (wifiMode == WM_HEAT && (heaterOn || (lastHeatOff && now - lastHeatOff < WIFI_TAIL_MS))) return true;
  return false;
}

void wifiStart() {
  WiFi.setHostname(HOSTNAME);
  WiFi.mode(WIFI_AP_STA);
  WiFi.softAP(cfgName, cfgApPass);
  if (staSsid[0]) WiFi.begin(staSsid, staPass);
  WiFi.setAutoReconnect(true);
  MDNS.begin(HOSTNAME);
  MDNS.addService("http", "tcp", 80);
  server.begin();
  wifiActive = true;
  addLog("Wi-Fi encendida");
}

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

void checkSchedule() {
  if (!timeValid()) return;
  time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  if (tm.tm_min == lastMinute) return;
  lastMinute = tm.tm_min;
  if (!autoOn || heaterOn) return;

  uint8_t wd = (tm.tm_wday + 6) % 7;             // 0 = lunes
  uint16_t m = tm.tm_hour * 60 + tm.tm_min;
  for (int i = 0; i < nSch; i++) {
    if (sch[i].en && (sch[i].days & (1 << wd)) && sch[i].start == m) {
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

// ---------- estado compacto (app por Bluetooth) ----------
String stateJson() {
  int32_t rem = heaterOn ? (int32_t)(onUntil - millis()) / 1000 : 0;
  if (rem < 0) rem = 0;
  String j = "{\"on\":"; j += heaterOn ? 1 : 0;
  j += ",\"ph\":";   j += phase;
  j += ",\"rem\":";  j += rem;
  j += ",\"tot\":";  j += onTotal;
  j += ",\"src\":";  j += js(onSrc);
  j += ",\"t\":";    j += tempC;
  j += ",\"v\":";    j += String(volt, 1);
  j += ",\"fl\":";   j += flame;
  j += ",\"pw\":";   j += power;
  j += ",\"bus\":";  j += busState;
  j += ",\"tv\":";   j += timeValid() ? 1 : 0;
  j += ",\"time\":"; j += (uint32_t)time(nullptr);
  j += ",\"auto\":"; j += autoOn ? 1 : 0;
  j += ",\"wf\":";   j += wifiActive ? 1 : 0;
  j += ",\"wm\":";   j += wifiMode;
  j += ",\"gas\":[";  j += String(gasCur, 2); j += ","; j += String(gasLast, 2); j += ",";
  j += String(gasMonth, 1); j += ","; j += String(gasTotal, 1); j += "]";
  j += ",\"note\":"; j += js(stopNote.substring(0, 200));
  j += "}";
  return j;
}

// ---------- Bluetooth LE ----------
// Las órdenes llegan por la tarea del Bluetooth; se pasan por una cola y se ejecutan en loop(),
// para no hablar con la Webasto desde dos tareas a la vez.
class SrvCb : public BLEServerCallbacks {
  void onConnect(BLEServer*) override { bleConn++; }
  void onDisconnect(BLEServer*) override { if (bleConn > 0) bleConn--; bleAdvPending = true; }
};

class CmdCb : public BLECharacteristicCallbacks {
  void onWrite(BLECharacteristic* c) override {
    auto v = c->getValue();
    BleCmd x;
    size_t n = v.length();
    if (n >= sizeof x.c) n = sizeof x.c - 1;
    memcpy(x.c, v.c_str(), n);
    x.c[n] = 0;
    if (bleQueue) xQueueSend(bleQueue, &x, 0);
  }
};

void bleSet(BLECharacteristic* c, const String& v) {
  if (!c) return;
  c->setValue((uint8_t*)v.c_str(), v.length());
  if (bleConn > 0) c->notify();
}

void bleInit() {
  BLEDevice::init(cfgName);
  BLEDevice::setMTU(517);
  // Emparejamiento con PIN fijo de 6 cifras, con vínculo guardado (bonding) y cifrado
  BLESecurity* sec = new BLESecurity();
#if ESP_ARDUINO_VERSION_MAJOR >= 3
  sec->setPassKey(true, blePin);
  sec->setCapability(ESP_IO_CAP_OUT);
  sec->setAuthenticationMode(true, true, true);
#else
  sec->setStaticPIN(blePin);
  sec->setAuthenticationMode(ESP_LE_AUTH_REQ_SC_MITM_BOND);
#endif
  BLEServer* srv = BLEDevice::createServer();
  srv->setCallbacks(new SrvCb());
  BLEService* svc = srv->createService(BLE_SVC);
  const uint16_t P = ESP_GATT_PERM_READ_ENC_MITM | ESP_GATT_PERM_WRITE_ENC_MITM;   // solo dispositivos emparejados
  chState = svc->createCharacteristic(BLE_STATE, BLECharacteristic::PROPERTY_READ | BLECharacteristic::PROPERTY_NOTIFY);
  BLECharacteristic* chCmd = svc->createCharacteristic(BLE_CMD, BLECharacteristic::PROPERTY_WRITE);
  chResp  = svc->createCharacteristic(BLE_RESP, BLECharacteristic::PROPERTY_READ | BLECharacteristic::PROPERTY_NOTIFY);
  chState->setAccessPermissions(P);
  chCmd->setAccessPermissions(P);
  chResp->setAccessPermissions(P);
  BLE2902* d1 = new BLE2902(); d1->setAccessPermissions(P); chState->addDescriptor(d1);
  BLE2902* d2 = new BLE2902(); d2->setAccessPermissions(P); chResp->addDescriptor(d2);
  chCmd->setCallbacks(new CmdCb());
  svc->start();
  BLEAdvertising* adv = BLEDevice::getAdvertising();
  adv->addServiceUUID(BLE_SVC);
  adv->setScanResponse(true);
  adv->setMinInterval(800);           // anuncio cada 0,5–1 s: menos consumo esperando conexión
  adv->setMaxInterval(1600);
  BLEDevice::startAdvertising();
}

// Borra todos los dispositivos emparejados (si se pierde un móvil)
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

// ---------- órdenes en texto (app por Bluetooth y consola serie) ----------
// Respuesta "orden:datos"; "orden:err texto" si algo falla
String runCmd(String c) {
  c.trim();
  int sp = c.indexOf(' ');
  String k = sp < 0 ? c : c.substring(0, sp), a = sp < 0 ? String("") : c.substring(sp + 1);
  k.toLowerCase();
  if (k == "on") {
    int m = a.toInt();
    return startHeater(m > 0 ? m : 30, "app") ? "on:ok" : "on:err La Webasto no respondió al encendido.";
  }
  if (k == "off") return stopHeater("app", true) ? "off:ok" : "off:ok sin confirmación de la Webasto";
  if (k == "state") return "state:" + stateJson();
  if (k == "time") {
    long e = a.toInt();
    if (e < 1700000000) return "time:err Hora no válida";
    struct timeval tv = { (time_t)e, 0 };
    settimeofday(&tv, nullptr);
    addLog("Hora ajustada desde la app");
    return "time:ok";
  }
  if (k == "sched") return "sched:" + schedText();
  if (k == "setsched") {
    int b = a.indexOf('|');
    if (b < 0) return "setsched:err Formato";
    applySched(a.substring(0, b), a.substring(b + 1));
    return "setsched:ok";
  }
  if (k == "errors") return "errors:" + errorsJson();
  if (k == "log") {
    String l;
    for (int i = logN - 1; i >= 0 && l.length() + logBuf[i].length() < 480; i--) { if (l.length()) l += "\n"; l += logBuf[i]; }
    return "log:" + l;
  }
  if (k == "cfg") return "cfg:" + cfgJson();
  if (k == "set") {                    // set clave=valor
    int e = a.indexOf('=');
    if (e < 0) return "set:err Formato";
    String err;
    int r = cfgSet(a.substring(0, e), a.substring(e + 1), err);
    if (r == 0) return String("set:err ") + err;
    return r == 2 ? String("set:restart") : String("set:ok");
  }
  if (k == "wifi") { wifiUntil = millis() + WIFI_ASK_MS; return "wifi:ok"; }
  if (k == "tgtest") {
    if (!tgToken[0] || !tgChat[0]) return "tgtest:err Primero guarda el token y el chat ID.";
    if (!staSsid[0]) return "tgtest:err Falta la red con internet (Configuración).";
    notify("Prueba de aviso");
    return "tgtest:ok";
  }
  if (k == "forget") { bleForgetAll(); return "forget:ok"; }
  if (k == "gasreset") { gasMonth = gasTotal = gasLast = 0; gasSave(); addLog("Contador de gasoil a cero"); return "gasreset:ok"; }
  if (k == "reboot") {
    if (heaterOn) return "reboot:err Está calentando: reiniciar la apagaría.";
    rebootPending = true;
    return "reboot:ok";
  }
  return k + ":err Orden desconocida";
}

// ---------- web ----------
// La página está en web.h (INDEX_HTML)

void handleState() {
  lastUi = millis();
  lastWeb = millis();
  uint32_t now = millis();
  int32_t rem = heaterOn ? (int32_t)(onUntil - now) / 1000 : 0;
  if (rem < 0) rem = 0;

  String j; j.reserve(3000);
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
  bool sta = WiFi.status() == WL_CONNECTED;
  j += ",\"sta\":";    j += sta ? "true" : "false";
  j += ",\"ssid\":";   j += js(sta ? WiFi.SSID() : String(""));
  j += ",\"ip\":";     j += js(sta ? WiFi.localIP().toString() : String(""));
  j += ",\"rssi\":";   j += sta ? WiFi.RSSI() : 0;
  j += ",\"tx\":";     j += js(lastTx);
  j += ",\"rx\":";     j += js(lastRx);
  j += ",\"sch\":[";
  for (int i = 0; i < nSch; i++) {
    if (i) j += ",";
    j += "["; j += sch[i].en; j += ","; j += sch[i].days; j += ",";
    j += sch[i].start; j += ","; j += sch[i].dur; j += "]";
  }
  j += "],\"log\":[";
  for (int i = logN - 1; i >= 0; i--) { j += js(logBuf[i]); if (i) j += ","; }
  j += "]}";
  server.send(200, "application/json", j);
}

void handleOn() {
  int m = server.arg("min").toInt();
  if (m <= 0) m = 30;
  bool ok = startHeater(m, "manual");
  server.send(ok ? 200 : 502, "text/plain", ok ? "ok" : "La Webasto no respondió al encendido.");
}

void handleOff() {
  bool ok = stopHeater("manual", true);
  server.send(200, "text/plain", ok ? "ok" : "Apagada sin confirmación de la Webasto.");
}

void handleSched() {
  applySched(server.arg("auto"), server.arg("list"));
  server.send(200, "text/plain", "ok");
}

void handleTime() {
  long e = server.arg("epoch").toInt();
  if (e < 1700000000) { server.send(400, "text/plain", "Hora no válida"); return; }
  struct timeval tv = { (time_t)e, 0 };
  settimeofday(&tv, nullptr);
  addLog("Hora ajustada desde el móvil");
  server.send(200, "text/plain", "ok");
}

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

// Guarda los ajustes que vengan en el formulario; si alguno necesita reiniciar, reinicia (salvo calentando)
void handleCfgPost() {
  bool restart = false;
  String err;
  for (const char* k : CFG_KEYS) {
    if (!server.hasArg(k)) continue;
    int r = cfgSet(k, server.arg(k), err);
    if (r == 0) { server.send(400, "text/plain", err); return; }
    if (r == 2) restart = true;
  }
  addLog("Configuración guardada");
  if (!restart) { server.send(200, "text/plain", "Guardado."); return; }
  if (heaterOn) { server.send(200, "text/plain", "Guardado. Se aplicará al reiniciar (ahora está calentando: reiniciar la apagaría)."); return; }
  server.send(200, "text/plain", "Guardado. Reiniciando para aplicarlo; vuelve a conectarte en unos segundos (si cambiaste el nombre o la clave de la Wi-Fi, con los nuevos).");
  rebootPending = true;
}

void handleTgTest() {
  String r = runCmd("tgtest");
  if (r.startsWith("tgtest:err ")) { server.send(400, "text/plain", r.substring(11)); return; }
  server.send(200, "text/plain", "Enviando… mira «Último aviso» en unos segundos.");
}

// ---------- consola serie ----------
void serialCli() {
  if (!Serial.available()) return;
  String c = Serial.readStringUntil('\n');
  c.trim();
  String l = c; l.toLowerCase();
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

// ---------- arranque ----------
void setup() {
  Serial.begin(115200);
  Serial.setTimeout(200);
  setCpuFrequencyMhz(80);             // suficiente para W-Bus, web y Bluetooth; gasta menos que a 240 MHz
  wbus.begin(2400, SERIAL_8E1, WBUS_RX, WBUS_TX);
  loadCfg();
  tgQueue = xQueueCreate(6, sizeof(Msg));
  bleQueue = xQueueCreate(4, sizeof(BleCmd));
  xTaskCreatePinnedToCore(tgTask, "telegram", 10240, nullptr, 1, nullptr, 0);

  configTzTime(TZ_INFO, "pool.ntp.org", "time.google.com");

  server.on("/", HTTP_GET, [] { server.send_P(200, "text/html", INDEX_HTML); });
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
  server.onNotFound([] { server.sendHeader("Location", "/"); server.send(302); });

  bleInit();
  wifiUntil = millis() + WIFI_BOOT_MS;   // rescate: Wi-Fi encendida los primeros minutos en cualquier modo
  wifiStart();

  addLog(String("Arranque, firmware " FW_VERSION));
  Serial.printf("WTTC %s | Bluetooth y Wi-Fi: \"%s\" | PIN Bluetooth: %06u\n", FW_VERSION, cfgName, (unsigned)blePin);
  Serial.println("Consola: on [min] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset");
}

void loop() {
  if (wifiActive) server.handleClient();
  serialCli();

  // Órdenes de la app (llegan por la tarea del Bluetooth)
  BleCmd bc;
  while (bleQueue && xQueueReceive(bleQueue, &bc, 0) == pdTRUE) {
    bleSet(chResp, runCmd(String(bc.c)));
    lastBleState = 0;                  // estado nuevo enseguida
  }
  if (bleConn > 0) {
    lastUi = millis();                 // con la app conectada se leen los sensores como con la web abierta
    if (millis() - lastBleState >= 2000) { bleSet(chState, stateJson()); lastBleState = millis(); }
  }
  if (bleAdvPending && bleConn == 0) { delay(100); BLEDevice::startAdvertising(); bleAdvPending = false; }

  // Wi-Fi según el modo elegido
  bool want = wifiWanted();
  if (want && !wifiActive) wifiStart();
  else if (!want && wifiActive) wifiStop();

  if (rebootPending) { delay(800); ESP.restart(); }

  uint32_t now = millis();
  if (heaterOn) {
    if ((int32_t)(now - onUntil) >= 0) stopHeater("fin de tiempo", true);
    else if (now - lastKA >= KEEPALIVE_MS) {
      uint8_t d[2] = {0x21, 0x00}, r[64], n;
      lastKA = now;
      if (wbusCmd(0x44, d, 2, r, n)) {
        if (kaFails >= 5) addLog("La Webasto vuelve a responder");
        kaFails = 0;
        // Respuesta 00: sigue con la orden. 01: ya no la tiene, se ha apagado por su cuenta
        if (n >= 1 && r[0] == 0x01) { if (++kaOff >= 2) heaterQuit("ya no tiene la orden de calentar"); }
        else kaOff = 0;
      } else if (++kaFails == 5) {
        phase = PH_LOST;
        addLog("Aviso: la Webasto no responde al mantenimiento");
        notify("La Webasto no responde por W-Bus. Sin mantenimiento se apaga sola en unos segundos; revisa el cableado.");
      } else if (kaFails >= 24) {                    // 2 minutos sin respuesta
        stopHeater("sin comunicación con la Webasto", false);
        stopNote = hhmm() + "Se perdió la comunicación con la Webasto. Sin mantenimiento se apaga sola, pero compruébalo.";
        notify("Sigue sin responder tras 2 minutos: la doy por apagada. Compruébalo en la furgo.");
      }
    }
  }
  if ((heaterOn || now - lastUi < 15000) && now - lastSensor >= SENSOR_MS) {
    if (readSensors() && heaterOn) { gasTick(); evalHeater(); }
  }
  checkSchedule();
}
