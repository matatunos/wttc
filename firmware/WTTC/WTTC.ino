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
    Placa:        ESP32-S3 DevKitC-1 N16R8 (16 MB de flash; la de referencia) o ESP32 DevKitC (ESP-WROOM-32, 4 MB).
                  El mismo código vale para las dos; cada una necesita su propio programa compilado.
    Transceptor:  TJA1020 (TTL de 3,3 V <-> bus K-Line/LIN de un hilo a 12 V), borna LIN al cable W-Bus
    Alimentación: regulador LM2596 a 5,0 V desde el +12 V permanente del conector del temporizador
    Conexiones:   TX del TJA1020 -> IO16 (RX2 del ESP32) · RX del TJA1020 <- IO17 (TX2) · SLP -> 3V3

  Código y documentación
  ----------------------
    Repositorio:  https://github.com/matatunos/wttc
    Web:          https://wttc.favala.es (simulador, esquema, guías de instalación y uso)

  Compilar
  --------
    Arduino IDE:  placa "ESP32S3 Dev Module" (Flash Size 16MB) o "ESP32 Dev Module", núcleo ESP32 2.x o 3.x.
                  Sin librerías externas. Esquema de partición: "Huge APP" (solo para el límite de tamaño del IDE:
                  la tabla que se graba es partitions.csv, con dos huecos para las actualizaciones sin cable).
    arduino-cli:  --fqbn esp32:esp32:esp32s3:FlashSize=16M,PartitionScheme=huge_app
                  --fqbn esp32:esp32:esp32:PartitionScheme=huge_app             (ESP32 DevKitC)
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
    - Todo lo configurable (idioma, nombre, claves, PIN, modo de la Wi-Fi, Telegram, batería mínima) se cambia
      desde la web o la app y se guarda en la memoria de la placa: no hace falta tocar el código.
    - Idioma: español, inglés o alemán (registro, avisos, mensajes y web). La app pone el del móvil al conectar.
      La consola serie sigue en español.

  Protocolo W-Bus (resumen)
  -------------------------
    2400 baudios, 8 bits, paridad par, 1 bit de parada (8E1), un solo hilo: cada byte que enviamos vuelve
    como eco por RX. Trama de petición:  F4 LL CMD DATOS… XOR   (F = emisor diagnóstico, 4 = calefactor)
    Respuesta de la Webasto:             4F LL CMD|0x80 DATOS… XOR
    LL = número de bytes que siguen (comando + datos + checksum). XOR = o-exclusivo de todos los anteriores.
    Órdenes usadas: 0x21 encender (minutos) · 0x44 mantener (cada 5 s) · 0x10 apagar ·
                    0x50 05 leer sensores · 0x56 01 leer averías. Basado en la documentación de libwbus.
    Se habla como el programa de taller ThermoTest (emisor F). En una T5 GP con Thermo Top C, enkor (t6forum, 2025)
    comprobó con su propio ESP32 que «0x21 con la dirección de ThermoTest» la enciende y además pone en marcha el
    Climatronic (ventilador del habitáculo); el Telestart T90 manda 0x20, que esa calefacción no obedece.

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
// Emparejamientos: el ESP32 (y el S3 con núcleo 2.x) usa la pila Bluetooth Bluedroid; el ESP32-S3 con núcleo 3.x usa
// NimBLE. Cada una tiene sus funciones para contar y borrar emparejamientos (ver bondCount y bleForgetAll)
#if defined(CONFIG_BLUEDROID_ENABLED)
#include <esp_gap_ble_api.h>
#else
#include <host/ble_store.h>
#endif
#include <time.h>               // hora local (programas, registro)
#include <stdarg.h>             // trf(): textos traducidos con datos (printf)
#include <Update.h>             // actualización sin cable (OTA): escribe el programa nuevo en el hueco libre
#include <esp_ota_ops.h>        // confirmar el programa nuevo (o volver al anterior si no arranca)
#include <mbedtls/pk.h>         // comprobar la firma de las actualizaciones (ECDSA P-256)
#include <mbedtls/md.h>         // SHA-256 de la actualización
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
#define FW_VERSION "0.1.5"   // debe coincidir con el fichero VERSION de la raíz del repo (lo comprueba la CI)

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
#define AP_PASS_DEFAULT "calefaccion"    // clave de fábrica de la red propia: pública (sale en la documentación)
char cfgApPass[64] = AP_PASS_DEFAULT;    // clave de la red propia (WPA2 exige 8 caracteres como mínimo)
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

// ---------- actualización por internet (ver «Buscar e instalar por internet») ----------
volatile bool otaNetBusy = false;     // hay una búsqueda o descarga en marcha (en otra tarea)
volatile int otaProg = -1;            // progreso de la descarga (0–100); -1 = ninguna
volatile bool otaNetEnd = false;      // la tarea ha terminado: loop() responde y, si se instaló, reinicia
String otaNetMsg;                     // texto del resultado (lo escribe la tarea antes de otaNetEnd)
String otaNetNotes;                   // novedades de la versión nueva (de ota.json)
String lastWebMsg;                    // último resultado para la web (estado «om»)
bool otaNetOk = false, otaNetInstall = false, otaNetAuto = false, otaNetNew = false;
char otaNetVer[17] = "";              // versión encontrada
uint32_t otaAutoLast = 0;             // última búsqueda automática (una al día, para avisar por Telegram)

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
// Idioma: español, inglés o alemán (ajuste "lang"; la app pone el del móvil al conectar, la web lo deja elegir)
// Todos los textos que ve el usuario (registro, avisos, motivos de apagado y errores) salen de esta tabla.
// Las palabras del protocolo ("programa", "app"…, en onSrc) no se traducen: la app y la web las reconocen.
// ============================================================================================================
enum { L_ES, L_EN, L_DE, L_N };
uint8_t lang = L_ES;
const char* const LANG_CODES[L_N] = {"es", "en", "de"};

enum Txt {
  T_SRC_APP, T_SRC_MANUAL, T_SRC_CONSOLE, T_SRC_PROG,
  T_LOG_ON, T_LOG_ON_FAIL, T_TG_ON_FAIL, T_OFF, T_OFF_NOCONF, T_GAS_SUFFIX,
  T_FAULTS, T_NOTE_QUIT, T_TG_QUIT, T_ERR_NORESP, T_ERR_NONE,
  T_WHY_FLAME_OUT, T_WHY_NO_IGNITION, T_WHY_NO_ORDER, T_WHY_END, T_WHY_NO_COMM,
  T_LOG_SCHED, T_LOG_WIFI_ON, T_LOG_WIFI_OFF, T_LOG_SKIP, T_LOG_FORGET, T_LOG_TIME_APP, T_LOG_TIME_WEB,
  T_LOG_GASRESET, T_LOG_CFG, T_LOG_BOOT, T_LOG_BUS_BACK, T_LOG_BUS_LOST, T_TG_BUS_LOST, T_NOTE_LOST, T_TG_LOST,
  T_TG_TEST, T_TG_QUEUE_FULL, T_TG_NO_NET, T_TG_SENT, T_TG_ERROR,
  T_E_NAME, T_E_APPASS, T_E_PIN, T_E_WIFIMODE, T_E_SSID, T_E_PASS, T_E_TOKEN, T_E_CHAT, T_E_MINVOLT, T_E_LANG, T_E_UNKNOWN_SET,
  T_E_ON, T_OFF_NOCONF_SHORT, T_E_TIME, T_E_FORMAT, T_E_TG_CFG, T_E_TG_NET, T_E_REBOOT_HEAT, T_E_UNKNOWN_CMD,
  T_W_OFF_NOCONF, T_W_SAVED, T_W_SAVED_LATER, T_W_SAVED_REBOOT, T_W_TG_SENDING, T_W_GASRESET, T_W_FORGOT,
  T_W_ORIGIN, T_W_SETUP, T_E_APPASS_DEF,
  T_OTA_HEAT, T_OTA_FORMAT, T_OTA_NOSLOT, T_OTA_WRITE, T_OTA_SIG, T_OTA_OLD, T_OTA_OK, T_LOG_OTA, T_LOG_OTA_OK,
  T_OTA_NONET, T_OTA_LATEST, T_OTA_NEW, T_OTA_NOTYET, T_OTA_BUSY, T_OTA_DL, T_TG_OTA_NEW,
  T_COUNT
};

const char* const TXT[T_COUNT][L_N] = {
  /* T_SRC_APP */          {"app", "app", "App"},
  /* T_SRC_MANUAL */       {"manual", "manual", "manuell"},
  /* T_SRC_CONSOLE */      {"consola", "console", "Konsole"},
  /* T_SRC_PROG */         {"programa", "schedule", "Zeitplan"},
  /* T_LOG_ON */           {"Encendida (%s, %d min)", "Switched on (%s, %d min)", "Eingeschaltet (%s, %d min)"},
  /* T_LOG_ON_FAIL */      {"Error: la Webasto no respondió al encendido", "Error: the Webasto did not answer the start command", "Fehler: Webasto hat auf den Einschaltbefehl nicht geantwortet"},
  /* T_TG_ON_FAIL */       {"No se pudo encender (%s): la Webasto no responde por W-Bus", "Could not switch on (%s): the Webasto does not answer on the W-Bus", "Einschalten nicht möglich (%s): Webasto antwortet nicht über W-Bus"},
  /* T_OFF */              {"Apagada (%s)", "Switched off (%s)", "Ausgeschaltet (%s)"},
  /* T_OFF_NOCONF */       {"Apagada (%s, sin confirmación)", "Switched off (%s, not confirmed)", "Ausgeschaltet (%s, ohne Bestätigung)"},
  /* T_GAS_SUFFIX */       {" · gasoil ≈ %s", " · diesel ≈ %s", " · Diesel ≈ %s"},
  /* T_FAULTS */           {"Averías: %s", "Faults: %s", "Fehler: %s"},
  /* T_NOTE_QUIT */        {"Se ha apagado sola: %s. Averías: %s.", "It switched itself off: %s. Faults: %s.", "Hat sich selbst abgeschaltet: %s. Fehler: %s."},
  /* T_TG_QUIT */          {"Se ha apagado sola: %s. Averías: %s. Gasoil ≈ %s", "It switched itself off: %s. Faults: %s. Diesel ≈ %s", "Hat sich selbst abgeschaltet: %s. Fehler: %s. Diesel ≈ %s"},
  /* T_ERR_NORESP */       {"sin respuesta", "no answer", "keine Antwort"},
  /* T_ERR_NONE */         {"ninguna guardada", "none stored", "keine gespeichert"},
  /* T_WHY_FLAME_OUT */    {"se ha apagado la llama", "the flame went out", "die Flamme ist erloschen"},
  /* T_WHY_NO_IGNITION */  {"no ha llegado a prender", "it never ignited", "sie hat nicht gezündet"},
  /* T_WHY_NO_ORDER */     {"ya no tiene la orden de calentar", "it no longer has the heating command", "sie hat den Heizbefehl nicht mehr"},
  /* T_WHY_END */          {"fin de tiempo", "time is up", "Zeit abgelaufen"},
  /* T_WHY_NO_COMM */      {"sin comunicación con la Webasto", "no communication with the Webasto", "keine Verbindung zur Webasto"},
  /* T_LOG_SCHED */        {"Programas guardados (%d)", "Schedules saved (%d)", "Zeitpläne gespeichert (%d)"},
  /* T_LOG_WIFI_ON */      {"Wi-Fi encendida", "Wi-Fi on", "WLAN an"},
  /* T_LOG_WIFI_OFF */     {"Wi-Fi apagada para ahorrar", "Wi-Fi off to save power", "WLAN aus (Strom sparen)"},
  /* T_LOG_SKIP */         {"Programa omitido: batería a %s V", "Schedule skipped: battery at %s V", "Zeitplan ausgelassen: Batterie bei %s V"},
  /* T_LOG_FORGET */       {"Emparejamientos Bluetooth borrados (%d)", "Bluetooth pairings deleted (%d)", "Bluetooth-Kopplungen gelöscht (%d)"},
  /* T_LOG_TIME_APP */     {"Hora ajustada desde la app", "Clock set from the app", "Uhrzeit von der App gestellt"},
  /* T_LOG_TIME_WEB */     {"Hora ajustada desde el móvil", "Clock set from the phone", "Uhrzeit vom Handy gestellt"},
  /* T_LOG_GASRESET */     {"Contador de gasoil a cero", "Diesel counter reset", "Dieselzähler zurückgesetzt"},
  /* T_LOG_CFG */          {"Configuración guardada", "Settings saved", "Einstellungen gespeichert"},
  /* T_LOG_BOOT */         {"Arranque, firmware %s", "Boot, firmware %s", "Start, Firmware %s"},
  /* T_LOG_BUS_BACK */     {"La Webasto vuelve a responder", "The Webasto is answering again", "Webasto antwortet wieder"},
  /* T_LOG_BUS_LOST */     {"Aviso: la Webasto no responde al mantenimiento", "Warning: the Webasto does not answer the keep-alive", "Warnung: Webasto antwortet nicht auf das Keep-Alive"},
  /* T_TG_BUS_LOST */      {"La Webasto no responde por W-Bus. Sin mantenimiento se apaga sola en unos segundos; revisa el cableado.",
                            "The Webasto does not answer on the W-Bus. Without keep-alive it switches itself off within seconds; check the wiring.",
                            "Webasto antwortet nicht über W-Bus. Ohne Keep-Alive schaltet sie sich in Sekunden selbst ab; Verkabelung prüfen."},
  /* T_NOTE_LOST */        {"Se perdió la comunicación con la Webasto. Sin mantenimiento se apaga sola, pero compruébalo.",
                            "Lost communication with the Webasto. Without keep-alive it switches itself off, but check it.",
                            "Verbindung zur Webasto verloren. Ohne Keep-Alive schaltet sie sich selbst ab, bitte trotzdem prüfen."},
  /* T_TG_LOST */          {"Sigue sin responder tras 2 minutos: la doy por apagada. Compruébalo en la furgo.",
                            "Still no answer after 2 minutes: assuming it is off. Check it in the van.",
                            "Nach 2 Minuten immer noch keine Antwort: gilt als aus. Bitte im Fahrzeug prüfen."},
  /* T_TG_TEST */          {"Prueba de aviso", "Test notification", "Testbenachrichtigung"},
  /* T_TG_QUEUE_FULL */    {"Cola de avisos llena: se ha perdido uno", "Notification queue full: one was lost", "Benachrichtigungen voll: eine ging verloren"},
  /* T_TG_NO_NET */        {"Perdido, sin red: %s", "Lost, no network: %s", "Verloren, kein Netz: %s"},
  /* T_TG_SENT */          {"Enviado: %s", "Sent: %s", "Gesendet: %s"},
  /* T_TG_ERROR */         {"Error %d (sin internet, o token/chat mal): %s", "Error %d (no internet, or wrong token/chat): %s", "Fehler %d (kein Internet oder Token/Chat falsch): %s"},
  /* T_E_NAME */           {"El nombre debe tener entre 1 y 29 caracteres.", "The name must be 1 to 29 characters long.", "Der Name muss 1 bis 29 Zeichen lang sein."},
  /* T_E_APPASS */         {"La clave de la Wi-Fi debe tener entre 8 y 63 caracteres.", "The Wi-Fi password must be 8 to 63 characters long.", "Das WLAN-Passwort muss 8 bis 63 Zeichen lang sein."},
  /* T_E_PIN */            {"El PIN debe tener 6 cifras y no empezar por 0.", "The PIN must have 6 digits and not start with 0.", "Die PIN muss 6 Ziffern haben und darf nicht mit 0 beginnen."},
  /* T_E_WIFIMODE */       {"Modo de Wi-Fi no válido.", "Invalid Wi-Fi mode.", "Ungültiger WLAN-Modus."},
  /* T_E_SSID */           {"Nombre de red demasiado largo.", "Network name too long.", "Netzwerkname zu lang."},
  /* T_E_PASS */           {"Contraseña demasiado larga.", "Password too long.", "Passwort zu lang."},
  /* T_E_TOKEN */          {"Token demasiado largo.", "Token too long.", "Token zu lang."},
  /* T_E_CHAT */           {"Chat ID demasiado largo.", "Chat ID too long.", "Chat-ID zu lang."},
  /* T_E_MINVOLT */        {"La batería mínima debe estar entre 10,5 y 13,0 V.", "The minimum battery must be between 10.5 and 13.0 V.", "Die Mindestspannung muss zwischen 10,5 und 13,0 V liegen."},
  /* T_E_LANG */           {"Idioma no válido.", "Invalid language.", "Ungültige Sprache."},
  /* T_E_UNKNOWN_SET */    {"Ajuste desconocido: %s", "Unknown setting: %s", "Unbekannte Einstellung: %s"},
  /* T_E_ON */             {"La Webasto no respondió al encendido.", "The Webasto did not answer the start command.", "Webasto hat auf den Einschaltbefehl nicht geantwortet."},
  /* T_OFF_NOCONF_SHORT */ {"sin confirmación de la Webasto", "not confirmed by the Webasto", "ohne Bestätigung der Webasto"},
  /* T_E_TIME */           {"Hora no válida", "Invalid time", "Ungültige Uhrzeit"},
  /* T_E_FORMAT */         {"Formato", "Format", "Format"},
  /* T_E_TG_CFG */         {"Primero guarda el token y el chat ID.", "Save the token and the chat ID first.", "Zuerst Token und Chat-ID speichern."},
  /* T_E_TG_NET */         {"Falta la red con internet (Configuración).", "No internet network set (Settings).", "Kein Netzwerk mit Internet eingestellt (Einstellungen)."},
  /* T_E_REBOOT_HEAT */    {"Está calentando: reiniciar la apagaría.", "It is heating: restarting would switch it off.", "Sie heizt gerade: ein Neustart würde sie abschalten."},
  /* T_E_UNKNOWN_CMD */    {"Orden desconocida", "Unknown command", "Unbekannter Befehl"},
  /* T_W_OFF_NOCONF */     {"Apagada sin confirmación de la Webasto.", "Switched off, not confirmed by the Webasto.", "Ausgeschaltet, ohne Bestätigung der Webasto."},
  /* T_W_SAVED */          {"Guardado.", "Saved.", "Gespeichert."},
  /* T_W_SAVED_LATER */    {"Guardado. Se aplicará al reiniciar (ahora está calentando: reiniciar la apagaría).",
                            "Saved. It will apply after a restart (it is heating now: restarting would switch it off).",
                            "Gespeichert. Wird nach einem Neustart wirksam (sie heizt gerade: ein Neustart würde sie abschalten)."},
  /* T_W_SAVED_REBOOT */   {"Guardado. Reiniciando para aplicarlo; vuelve a conectarte en unos segundos (si cambiaste el nombre o la clave de la Wi-Fi, con los nuevos).",
                            "Saved. Restarting to apply it; reconnect in a few seconds (with the new name or Wi-Fi password if you changed them).",
                            "Gespeichert. Neustart zum Übernehmen; in ein paar Sekunden neu verbinden (mit neuem Namen bzw. WLAN-Passwort, falls geändert)."},
  /* T_W_TG_SENDING */     {"Enviando… mira «Último aviso» en unos segundos.", "Sending… check “Last notification” in a few seconds.", "Wird gesendet… in ein paar Sekunden „Letzte Benachrichtigung“ prüfen."},
  /* T_W_GASRESET */       {"Contador de gasoil a cero.", "Diesel counter reset.", "Dieselzähler zurückgesetzt."},
  /* T_W_FORGOT */         {"Borrados %d emparejamientos.", "Deleted %d pairings.", "%d Kopplungen gelöscht."},
  /* T_W_ORIGIN */         {"Petición rechazada: viene de otra web.", "Request rejected: it comes from another website.", "Anfrage abgelehnt: sie kommt von einer anderen Webseite."},
  /* T_W_SETUP */          {"Primer uso: antes de manejarla, cambia la clave de la Wi-Fi de la placa.", "First use: change the board's Wi-Fi password before using it.", "Erste Nutzung: zuerst das WLAN-Passwort der Platine ändern."},
  /* T_E_APPASS_DEF */     {"Elige una clave distinta de la de fábrica.", "Choose a password other than the factory one.", "Wähle ein anderes Passwort als das ab Werk."},
  /* T_OTA_HEAT */         {"Está calentando: actualiza cuando esté apagada.", "It is heating: update when it is off.", "Sie heizt gerade: aktualisieren, wenn sie aus ist."},
  /* T_OTA_FORMAT */       {"Ese fichero no es una actualización de WTTC (.ota).", "That file is not a WTTC update (.ota).", "Diese Datei ist kein WTTC-Update (.ota)."},
  /* T_OTA_NOSLOT */       {"Esta placa no tiene hueco para actualizar sin cable: hay que grabarla una vez por USB con esta versión.", "This board has no room to update without a cable: flash it once over USB with this version.", "Diese Platine hat keinen Platz für Updates ohne Kabel: einmal per USB mit dieser Version flashen."},
  /* T_OTA_WRITE */        {"Error al grabar la actualización.", "Error writing the update.", "Fehler beim Schreiben des Updates."},
  /* T_OTA_SIG */          {"Firma no válida: la actualización no es oficial o está dañada. No se ha instalado.", "Invalid signature: the update is not official or is damaged. It was not installed.", "Ungültige Signatur: das Update ist nicht offiziell oder beschädigt. Nicht installiert."},
  /* T_OTA_OLD */          {"Esa versión es más antigua que la instalada (%s). No se ha instalado.", "That version is older than the installed one (%s). It was not installed.", "Diese Version ist älter als die installierte (%s). Nicht installiert."},
  /* T_OTA_OK */           {"Actualización %s instalada. Reiniciando; vuelve a conectarte en unos segundos.", "Update %s installed. Restarting; reconnect in a few seconds.", "Update %s installiert. Neustart; in ein paar Sekunden neu verbinden."},
  /* T_LOG_OTA */          {"Actualización instalada: %s", "Update installed: %s", "Update installiert: %s"},
  /* T_LOG_OTA_OK */       {"Firmware %s confirmado tras la actualización", "Firmware %s confirmed after the update", "Firmware %s nach dem Update bestätigt"},
  /* T_OTA_NONET */        {"La placa no tiene internet: únela a una red con internet (Configuración) o sube el fichero .ota desde su web.", "The board has no internet: join it to a network with internet (Settings) or upload the .ota file from its web page.", "Die Platine hat kein Internet: mit einem Netz mit Internet verbinden (Einstellungen) oder die .ota-Datei über ihre Webseite hochladen."},
  /* T_OTA_LATEST */       {"Ya tiene la última versión (%s).", "It already has the latest version (%s).", "Sie hat bereits die neueste Version (%s)."},
  /* T_OTA_NEW */          {"Hay una versión nueva: %s (tiene la %s).", "There is a new version: %s (it has %s).", "Es gibt eine neue Version: %s (installiert ist %s)."},
  /* T_OTA_NOTYET */       {"La actualización %s aún no está publicada; prueba dentro de un rato.", "Update %s is not published yet; try again in a while.", "Update %s ist noch nicht veröffentlicht; später erneut versuchen."},
  /* T_OTA_BUSY */         {"Ya hay una actualización en marcha.", "An update is already in progress.", "Es läuft bereits ein Update."},
  /* T_OTA_DL */           {"Descargando la versión %s…", "Downloading version %s…", "Lade Version %s herunter…"},
  /* T_TG_OTA_NEW */       {"Hay una versión nueva de WTTC: %s. Se instala desde la app o la web de la placa (Buscar actualizaciones). Novedades: %s",
                            "A new WTTC version is available: %s. Install it from the app or the board's web page (Check for updates). What's new: %s",
                            "Neue WTTC-Version verfügbar: %s. Installieren über die App oder die Webseite der Platine (Nach Updates suchen). Neu: %s"},
};

// Texto en el idioma elegido
const char* tr(Txt t) { return TXT[t][lang]; }

// Texto con datos (printf), en el idioma elegido. t es int y no Txt: va_start no admite un enum como último parámetro
String trf(int t, ...) {
  char b[300];
  va_list ap; va_start(ap, t);
  vsnprintf(b, sizeof b, TXT[t][lang], ap);
  va_end(ap);
  return b;
}

// Quién la encendió, para mostrarlo (onSrc guarda la palabra del protocolo)
const char* srcName(const String& s) {
  if (s == "programa") return tr(T_SRC_PROG);
  if (s == "app") return tr(T_SRC_APP);
  if (s == "consola") return tr(T_SRC_CONSOLE);
  return tr(T_SRC_MANUAL);
}

// Número con un decimal o los que se pidan, con la coma o el punto que toca según el idioma
String num(float v, int dec) { String s = String(v, dec); if (lang != L_EN) s.replace('.', ','); return s; }

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
struct Msg { char t[640]; };          // un aviso pendiente de enviar (cabe el de versión nueva con sus novedades)
// Raíz de la cadena de api.telegram.org: Go Daddy Root Certificate Authority - G2 (caduca en 2037).
// SHA-256 45:14:0B:32:47:EB:9C:C8:C5:B4:F0:D7:B5:30:91:F7:32:92:08:9E:6E:5A:63:E2:74:9D:D3:AC:A9:19:8E:DA
// Con ella se comprueba el certificado: en una red ajena nadie puede hacerse pasar por Telegram y leer el token.
// Si Telegram cambiara de autoridad, los avisos fallarían («Último aviso» lo diría) hasta actualizar el firmware.
static const char TG_ROOT_CA[] PROGMEM =
"-----BEGIN CERTIFICATE-----\n"
"MIIDxTCCAq2gAwIBAgIBADANBgkqhkiG9w0BAQsFADCBgzELMAkGA1UEBhMCVVMx\n"
"EDAOBgNVBAgTB0FyaXpvbmExEzARBgNVBAcTClNjb3R0c2RhbGUxGjAYBgNVBAoT\n"
"EUdvRGFkZHkuY29tLCBJbmMuMTEwLwYDVQQDEyhHbyBEYWRkeSBSb290IENlcnRp\n"
"ZmljYXRlIEF1dGhvcml0eSAtIEcyMB4XDTA5MDkwMTAwMDAwMFoXDTM3MTIzMTIz\n"
"NTk1OVowgYMxCzAJBgNVBAYTAlVTMRAwDgYDVQQIEwdBcml6b25hMRMwEQYDVQQH\n"
"EwpTY290dHNkYWxlMRowGAYDVQQKExFHb0RhZGR5LmNvbSwgSW5jLjExMC8GA1UE\n"
"AxMoR28gRGFkZHkgUm9vdCBDZXJ0aWZpY2F0ZSBBdXRob3JpdHkgLSBHMjCCASIw\n"
"DQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAL9xYgjx+lk09xvJGKP3gElY6SKD\n"
"E6bFIEMBO4Tx5oVJnyfq9oQbTqC023CYxzIBsQU+B07u9PpPL1kwIuerGVZr4oAH\n"
"/PMWdYA5UXvl+TW2dE6pjYIT5LY/qQOD+qK+ihVqf94Lw7YZFAXK6sOoBJQ7Rnwy\n"
"DfMAZiLIjWltNowRGLfTshxgtDj6AozO091GB94KPutdfMh8+7ArU6SSYmlRJQVh\n"
"GkSBjCypQ5Yj36w6gZoOKcUcqeldHraenjAKOc7xiID7S13MMuyFYkMlNAJWJwGR\n"
"tDtwKj9useiciAF9n9T521NtYJ2/LOdYq7hfRvzOxBsDPAnrSTFcaUaz4EcCAwEA\n"
"AaNCMEAwDwYDVR0TAQH/BAUwAwEB/zAOBgNVHQ8BAf8EBAMCAQYwHQYDVR0OBBYE\n"
"FDqahQcQZyi27/a9BUFuIMGU2g/eMA0GCSqGSIb3DQEBCwUAA4IBAQCZ21151fmX\n"
"WWcDYfF+OwYxdS2hII5PZYe096acvNjpL9DbWu7PdIxztDhC2gV7+AJ1uP2lsdeu\n"
"9tfeE8tTEH6KRtGX+rcuKxGrkLAngPnon1rpN5+r5N9ss4UXnT3ZJE95kTXWXwTr\n"
"gIOrmgIttRD02JDHBHNA7XIloKmf7J6raBKZV8aPEjoJpL1E/QYVN8Gb5DKj7Tjo\n"
"2GTzLH4U/ALqn83/B2gX2yKQOC16jdFU8WnjXzPKej17CuPKf1855eJ1usV2GDPO\n"
"LPAvTK33sefOT6jEm0pUBsV/fdUID+Ic/n4XuKxe9tQWskMJDE32p2u0mYRlynqI\n"
"4uJEvlz36hz1\n"
"-----END CERTIFICATE-----\n";
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
  if (xQueueSend(tgQueue, &x, 0) != pdTRUE) strlcpy(tgLast, tr(T_TG_QUEUE_FULL), sizeof tgLast);
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
    if (WiFi.status() != WL_CONNECTED) { strlcpy(tgLast, trf(T_TG_NO_NET, x.t).c_str(), sizeof tgLast); continue; }
    // Copia local del token y el chat: la configuración puede cambiar desde otra tarea mientras se envía
    char tok[64], chat[24];
    strlcpy(tok, tgToken, sizeof tok);
    strlcpy(chat, tgChat, sizeof chat);
    if (!tok[0] || !chat[0]) continue;
    WiFiClientSecure cli;
    cli.setCACert(TG_ROOT_CA);        // solo vale un certificado firmado por la raíz de Telegram (ver arriba)
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
    if (code == 200) strlcpy(tgLast, trf(T_TG_SENT, x.t).c_str(), sizeof tgLast);
    else strlcpy(tgLast, trf(T_TG_ERROR, code, x.t).c_str(), sizeof tgLast);
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
  if (!wbusCmd(0x56, d, 1, r, n)) return tr(T_ERR_NORESP);
  if (n < 2 || !r[1]) return tr(T_ERR_NONE);
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

// "0,25 l" (dos decimales por debajo de 10 litros, uno por encima), con la coma o el punto del idioma
String litros(float l) { return num(l, l < 10 ? 2 : 1) + " l"; }

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
      addLog(trf(T_LOG_ON, srcName(src), minutes));
      notify(trf(T_LOG_ON, srcName(src), minutes));
      return true;
    }
    delay(300);                                   // pequeña pausa antes de reintentar
  }
  addLog(tr(T_LOG_ON_FAIL));
  notify(trf(T_TG_ON_FAIL, srcName(src)));
  return false;
}

// Apaga la calefacción (orden 0x10; la Webasto hace su postbarrido). why dice el motivo, ya en el idioma elegido;
// tell = avisar por Telegram.
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
  String g = was ? trf(T_GAS_SUFFIX, litros(gasLast).c_str()) : String("");
  addLog(trf(ok ? T_OFF : T_OFF_NOCONF, why) + g);
  if (tell) notify(trf(T_OFF, why) + g);
  return ok;
}

// La Webasto ha dejado de calentar por su cuenta: se apunta, se leen sus averías y se avisa
void heaterQuit(const char* why) {
  stopHeater(why, false);                         // sin aviso propio: va uno más completo abajo
  String e = errorsText();
  addLog(trf(T_FAULTS, e.c_str()));
  stopNote = hhmm() + trf(T_NOTE_QUIT, why, e.c_str());   // se queda en la web y la app
  notify(trf(T_TG_QUIT, why, e.c_str(), litros(gasLast).c_str()));
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
    heaterQuit(tr(flameSeen ? T_WHY_FLAME_OUT : T_WHY_NO_IGNITION));
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
  lang     = prefs.getUChar("lang", L_ES);
  prefs.end();
  if (lang >= L_N) lang = L_ES;
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
  addLog(trf(T_LOG_SCHED, nSch));
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
    if (v.length() < 1 || v.length() >= sizeof cfgName) { err = tr(T_E_NAME); r = 0; }
    else if (v != cfgName) { strlcpy(cfgName, v.c_str(), sizeof cfgName); prefs.putString("name", cfgName); r = 2; }
  } else if (k == "appass") {
    if (!v.length()) r = 1;                       // vacío: se queda la clave que había
    else if (v.length() < 8 || v.length() >= sizeof cfgApPass) { err = tr(T_E_APPASS); r = 0; }
    else if (v == AP_PASS_DEFAULT) { err = tr(T_E_APPASS_DEF); r = 0; }   // la de fábrica es pública: no se puede volver a ella
    else { strlcpy(cfgApPass, v.c_str(), sizeof cfgApPass); prefs.putString("appass", cfgApPass); r = 2; }
  } else if (k == "pin") {
    long n = v.toInt();
    if (v.length() != 6 || n < 100000 || n > 999999) { err = tr(T_E_PIN); r = 0; }
    else if ((uint32_t)n != blePin) { blePin = n; prefs.putUInt("pin", blePin); r = 2; }
  } else if (k == "wifimode") {
    int m = v.toInt();
    if (m < WM_ALWAYS || m > WM_DEMAND || !v.length()) { err = tr(T_E_WIFIMODE); r = 0; }
    else { wifiMode = m; prefs.putUChar("wmode", wifiMode); }   // se aplica al momento (loop() enciende o apaga)
  } else if (k == "ssid") {
    if (v.length() >= sizeof staSsid) { err = tr(T_E_SSID); r = 0; }
    else if (v != staSsid) { strlcpy(staSsid, v.c_str(), sizeof staSsid); prefs.putString("ssid", staSsid); r = 2; }
  } else if (k == "pass") {
    if (v.length() >= sizeof staPass) { err = tr(T_E_PASS); r = 0; }
    else if (v.length()) { strlcpy(staPass, v.c_str(), sizeof staPass); prefs.putString("pass", staPass); r = 2; }
  } else if (k == "tgtok") {
    if (v.length() >= sizeof tgToken) { err = tr(T_E_TOKEN); r = 0; }
    else if (v.length()) { strlcpy(tgToken, v.c_str(), sizeof tgToken); prefs.putString("tgtok", tgToken); }
  } else if (k == "tgchat") {
    if (v.length() >= sizeof tgChat) { err = tr(T_E_CHAT); r = 0; }
    else { strlcpy(tgChat, v.c_str(), sizeof tgChat); prefs.putString("tgchat", tgChat); }   // vacío = avisos desactivados
  } else if (k == "minvolt") {
    float f = v.toFloat();
    if (f < 10.5 || f > 13.0) { err = tr(T_E_MINVOLT); r = 0; }
    else { minVolt = f; prefs.putFloat("minv", minVolt); }
  } else if (k == "lang") {
    int l = -1;
    for (int i = 0; i < L_N; i++) if (v == LANG_CODES[i]) l = i;
    if (l < 0) { err = tr(T_E_LANG); r = 0; }
    else if (l != lang) { lang = l; prefs.putUChar("lang", lang); }   // se aplica al momento; solo se escribe si cambia
  } else { err = trf(T_E_UNKNOWN_SET, k.c_str()); r = 0; }
  prefs.end();
  return r;
}

// Ajustes que admite el formulario de configuración de la web (en este orden)
const char* CFG_KEYS[] = {"lang", "name", "appass", "pin", "wifimode", "ssid", "pass", "tgtok", "tgchat", "minvolt"};

// ¿Sigue la Wi-Fi propia con la clave de fábrica? Entonces cualquiera cerca puede entrar: la web obliga a cambiarla
bool apDefault() { return strcmp(cfgApPass, AP_PASS_DEFAULT) == 0; }

// La configuración en JSON para la web y la app. Las claves (Wi-Fi y token) no se devuelven nunca.
// El PIN de Bluetooth solo va a la app (conexión ya cifrada) o a la web cuando la Wi-Fi tiene clave propia.
String cfgJson(bool withPin) {
  String j = "{\"name\":"; j += js(String(cfgName));
  j += ",\"pin\":";      if (withPin) j += blePin; else j += "null";
  j += ",\"apdef\":";    j += apDefault() ? "true" : "false";   // clave de fábrica: la web y la app piden cambiarla
  j += ",\"wifimode\":"; j += wifiMode;
  j += ",\"ssid\":";     j += js(String(staSsid));
  j += ",\"tg\":";       j += tgToken[0] ? "true" : "false";      // solo si hay token guardado, no el token
  j += ",\"tgchat\":";   j += js(String(tgChat));
  j += ",\"minvolt\":";  j += String(minVolt, 1);
  j += ",\"bonds\":";    j += bondCount();                       // cuántos móviles están emparejados
  j += ",\"lang\":\"";  j += LANG_CODES[lang]; j += "\"";     // idioma de la placa (la app lo iguala al del móvil)
  j += ",\"ota\":1";                                            // sabe buscar y actualizar por internet
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
  addLog(tr(T_LOG_WIFI_ON));
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
  addLog(tr(T_LOG_WIFI_OFF));
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
        addLog(trf(T_LOG_SKIP, num(volt, 1).c_str()));
        notify(trf(T_LOG_SKIP, num(volt, 1).c_str()));
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
  j += ",\"op\":";   j += otaProg;                // actualización por internet: 0–100 %, -1 = ninguna
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

// Cuántos móviles hay emparejados con la placa
int bondCount() {
#if defined(CONFIG_BLUEDROID_ENABLED)
  return esp_ble_get_bond_device_num();
#else
  ble_addr_t a[16]; int n = 0;
  return ble_store_util_bonded_peers(a, &n, 16) == 0 ? n : 0;
#endif
}

// Borra todos los dispositivos emparejados (si se pierde un móvil). Devuelve cuántos había.
int bleForgetAll() {
  int n = bondCount();
  if (n <= 0) return 0;
#if defined(CONFIG_BLUEDROID_ENABLED)
  esp_ble_bond_dev_t* l = (esp_ble_bond_dev_t*)malloc(sizeof(esp_ble_bond_dev_t) * n);
  if (!l) return 0;
  if (esp_ble_get_bond_device_list(&n, l) == ESP_OK)
    for (int i = 0; i < n; i++) esp_ble_remove_bond_device(l[i].bd_addr);
  free(l);
#else
  ble_store_clear();
#endif
  addLog(trf(T_LOG_FORGET, n));
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
    return startHeater(m > 0 ? m : 30, "app") ? String("on:ok") : String("on:err ") + tr(T_E_ON);
  }
  if (k == "off") return stopHeater(tr(T_SRC_APP), true) ? String("off:ok") : String("off:ok ") + tr(T_OFF_NOCONF_SHORT);
  if (k == "state") return "state:" + stateJson();
  if (k == "time") {                               // time <segundos desde 1970>: la app pone la placa en hora
    long e = a.toInt();
    if (e < 1700000000) return String("time:err ") + tr(T_E_TIME);
    struct timeval tv = { (time_t)e, 0 };
    settimeofday(&tv, nullptr);
    addLog(tr(T_LOG_TIME_APP));
    return "time:ok";
  }
  if (k == "sched") return "sched:" + schedText();
  if (k == "setsched") {                           // setsched <auto>|<lista>
    int b = a.indexOf('|');
    if (b < 0) return String("setsched:err ") + tr(T_E_FORMAT);
    applySched(a.substring(0, b), a.substring(b + 1));
    return "setsched:ok";
  }
  if (k == "errors") return "errors:" + errorsJson();
  if (k == "log") {                                // los eventos más recientes que quepan en ~480 bytes
    String l;
    for (int i = logN - 1; i >= 0 && l.length() + logBuf[i].length() < 480; i--) { if (l.length()) l += "\n"; l += logBuf[i]; }
    return "log:" + l;
  }
  if (k == "cfg") return "cfg:" + cfgJson(true);
  if (k == "set") {                                // set clave=valor
    int e = a.indexOf('=');
    if (e < 0) return String("set:err ") + tr(T_E_FORMAT);
    String err;
    int r = cfgSet(a.substring(0, e), a.substring(e + 1), err);
    if (r == 0) return String("set:err ") + err;
    return r == 2 ? String("set:restart") : String("set:ok");   // restart = se aplica al reiniciar
  }
  if (k == "wifi") { wifiUntil = millis() + WIFI_ASK_MS; return "wifi:ok"; }   // encender la Wi-Fi 15 min
  if (k == "tgtest") {                             // aviso de prueba por Telegram
    if (!tgToken[0] || !tgChat[0]) return String("tgtest:err ") + tr(T_E_TG_CFG);
    if (!staSsid[0]) return String("tgtest:err ") + tr(T_E_TG_NET);
    notify(tr(T_TG_TEST));
    return "tgtest:ok";
  }
  if (k == "forget") { bleForgetAll(); return "forget:ok"; }
  if (k == "gasreset") { gasMonth = gasTotal = gasLast = 0; gasSave(); addLog(tr(T_LOG_GASRESET)); return "gasreset:ok"; }
  if (k == "otacheck" || k == "update") {         // buscar actualización / buscar e instalar (va en otra tarea)
    String e = otaNetStart(k == "update", false);
    return e.length() ? k + ":err " + e : k + ":busy";   // «busy» = en marcha; el resultado llega luego como k:…
  }
  if (k == "reboot") {
    if (heaterOn) return String("reboot:err ") + tr(T_E_REBOOT_HEAT);   // sin placa, la Webasto se apaga en segundos
    rebootPending = true;                          // se reinicia en loop(), después de mandar esta respuesta
    return "reboot:ok";
  }
  return k + ":err " + tr(T_E_UNKNOWN_CMD);
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
  j += ",\"lang\":";   j += js(String(LANG_CODES[lang]));
  j += ",\"apdef\":";  j += apDefault() ? "true" : "false";     // primer uso: la web pide la clave nueva
  j += ",\"op\":";     j += otaProg;                             // actualización por internet en curso (0–100)
  j += ",\"om\":";     j += js(lastWebMsg);                      // último resultado de buscar o actualizar
  j += ",\"onew\":";   j += (otaNetNew && !otaNetBusy) ? "true" : "false";   // ese resultado es «hay versión nueva»
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

// Protección CSRF de la API web. Una página ajena abierta en el móvil (conectado a la Wi-Fi de la placa o a la red
// donde está wttc.local) podría mandar formularios a /api/… y, por ejemplo, encender la calefacción. Los navegadores
// ponen la cabecera Origin en esas peticiones: si viene y no es la propia placa, se rechaza. Sin Origin (curl,
// navegadores antiguos) se admite. Origin solo se lee porque setup() lo pide con collectHeaders().
bool originOk() {
  String o = server.header("Origin");
  if (!o.length()) return true;
  int p = o.indexOf("://");
  return p >= 0 && o.substring(p + 3) == server.hostHeader();
}
bool sameOrigin() {
  if (originOk()) return true;
  server.send(403, "text/plain", tr(T_W_ORIGIN));
  return false;
}

// ============================================================================================================
// Actualización sin cable (OTA) desde la web de la placa
// El fichero .ota de las Releases es: «WTTCOTA1» (8 bytes) · versión (16 bytes, rellena con ceros) · longitud de la
// firma (2 bytes) · firma ECDSA P-256 (DER) · programa (.bin). La firma cubre versión + programa y la hace el proyecto
// con su clave privada (GitHub Actions); aquí solo está la pública. Sin firma válida no se instala nada, y no se
// admiten versiones más antiguas que la instalada (se podrían usar para volver a meter fallos ya corregidos).
// El programa nuevo va al hueco libre (ver partitions.csv); si al arrancar no aguanta un minuto, la placa vuelve
// sola al anterior (vuelta atrás del cargador de arranque, activada en los núcleos ESP32 de Arduino).
// ============================================================================================================
static const char OTA_PUBKEY[] =
"-----BEGIN PUBLIC KEY-----\n"
"MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAELD21sm+qZ9fG+Ram4uSyo9QFJhhq\n"
"unPX8rxq+mIPsEoTbYT3JXlkqKI4MhCEUkpA+30W5Evjp/5R0cEzHpFktA==\n"
"-----END PUBLIC KEY-----\n";

struct Ota {
  bool active = false, failed = false, done = false;
  const char* err = nullptr;          // texto del error (de la tabla TXT)
  uint8_t head[26]; size_t headN = 0; // cabecera: marca, versión y longitud de la firma
  uint8_t sig[80]; size_t sigLen = 0, sigN = 0;
  char ver[17] = "";
  mbedtls_md_context_t md;
  bool mdOn = false;
} ota;

// Compara versiones «a.b.c»: <0 si a es más antigua que b
int verCmp(const char* a, const char* b) {
  int x[3] = {0, 0, 0}, y[3] = {0, 0, 0};
  sscanf(a, "%d.%d.%d", &x[0], &x[1], &x[2]);
  sscanf(b, "%d.%d.%d", &y[0], &y[1], &y[2]);
  for (int i = 0; i < 3; i++) if (x[i] != y[i]) return x[i] < y[i] ? -1 : 1;
  return 0;
}

void otaFail(Txt t) {
  if (!ota.failed) { ota.failed = true; ota.err = tr(t); }
  if (Update.isRunning()) Update.abort();
  if (ota.mdOn) { mbedtls_md_free(&ota.md); ota.mdOn = false; }
}

// Va recibiendo el fichero a trozos: cabecera, firma y programa (este se graba y se resume con SHA-256 a la vez)
void otaFeed(const uint8_t* d, size_t n) {
  while (n && !ota.failed) {
    if (ota.headN < sizeof ota.head) {                       // 1) cabecera
      size_t k = min(n, sizeof ota.head - ota.headN);
      memcpy(ota.head + ota.headN, d, k); ota.headN += k; d += k; n -= k;
      if (ota.headN < sizeof ota.head) return;
      if (memcmp(ota.head, "WTTCOTA1", 8)) { otaFail(T_OTA_FORMAT); return; }
      memcpy(ota.ver, ota.head + 8, 16); ota.ver[16] = 0;
      ota.sigLen = (ota.head[24] << 8) | ota.head[25];
      if (!ota.sigLen || ota.sigLen > sizeof ota.sig) { otaFail(T_OTA_FORMAT); return; }
      if (verCmp(ota.ver, FW_VERSION) < 0) { otaFail(T_OTA_OLD); return; }
      continue;
    }
    if (ota.sigN < ota.sigLen) {                              // 2) firma
      size_t k = min(n, ota.sigLen - ota.sigN);
      memcpy(ota.sig + ota.sigN, d, k); ota.sigN += k; d += k; n -= k;
      if (ota.sigN < ota.sigLen) return;
      // Placa grabada con la tabla antigua (un solo hueco): no hay dónde poner el programa nuevo
      const esp_partition_t* nx = esp_ota_get_next_update_partition(nullptr);
      if (!nx || nx == esp_ota_get_running_partition()) { otaFail(T_OTA_NOSLOT); return; }
      if (!Update.begin(UPDATE_SIZE_UNKNOWN)) { otaFail(T_OTA_WRITE); return; }
      mbedtls_md_init(&ota.md); ota.mdOn = true;
      if (mbedtls_md_setup(&ota.md, mbedtls_md_info_from_type(MBEDTLS_MD_SHA256), 0) || mbedtls_md_starts(&ota.md)) { otaFail(T_OTA_WRITE); return; }
      mbedtls_md_update(&ota.md, ota.head + 8, 16);          // la firma cubre también la versión
      continue;
    }
    mbedtls_md_update(&ota.md, d, n);                         // 3) programa
    if (Update.write((uint8_t*)d, n) != n) { otaFail(T_OTA_WRITE); return; }
    return;
  }
}

// Al terminar de recibir: comprobar la firma y, solo si vale, dar el programa nuevo por bueno para el próximo arranque
void otaFinish() {
  if (ota.failed) return;
  if (!ota.mdOn) { otaFail(T_OTA_FORMAT); return; }
  uint8_t hash[32];
  mbedtls_md_finish(&ota.md, hash); mbedtls_md_free(&ota.md); ota.mdOn = false;
  mbedtls_pk_context pk; mbedtls_pk_init(&pk);
  bool ok = mbedtls_pk_parse_public_key(&pk, (const unsigned char*)OTA_PUBKEY, strlen(OTA_PUBKEY) + 1) == 0
         && mbedtls_pk_verify(&pk, MBEDTLS_MD_SHA256, hash, sizeof hash, ota.sig, ota.sigLen) == 0;
  mbedtls_pk_free(&pk);
  if (!ok) { otaFail(T_OTA_SIG); return; }
  if (!Update.end(true)) { otaFail(T_OTA_WRITE); return; }
  ota.done = true;
}

// Manejador de la subida (POST /api/update, multipart): lo llama el servidor web con cada trozo del fichero
void handleUpdateUpload() {
  HTTPUpload& u = server.upload();
  lastWeb = millis();                                         // que la Wi-Fi no se apague a mitad
  if (u.status == UPLOAD_FILE_START) {
    if (otaNetBusy) { ota.active = true; ota.failed = true; ota.err = tr(T_OTA_BUSY); return; }   // ya descarga por internet
    if (ota.mdOn) mbedtls_md_free(&ota.md);
    ota = Ota();
    ota.active = true;
    if (heaterOn) otaFail(T_OTA_HEAT);                        // nunca mientras calienta
  } else if (u.status == UPLOAD_FILE_WRITE) {
    if (ota.active && !otaNetBusy) otaFeed(u.buf, u.currentSize);
  } else if (u.status == UPLOAD_FILE_END) {
    if (ota.active && !otaNetBusy) otaFinish();
  } else if (u.status == UPLOAD_FILE_ABORTED) {
    otaFail(T_OTA_WRITE);
  }
}

// Fin de la petición: respuesta y, si se instaló, reinicio
void handleUpdateDone() {
  if (!ota.active) { server.send(400, "text/plain", tr(T_OTA_FORMAT)); return; }
  ota.active = false;
  if (!ota.done) { server.send(400, "text/plain", ota.err ? ota.err : tr(T_OTA_WRITE)); return; }
  addLog(trf(T_LOG_OTA, ota.ver));
  server.send(200, "text/plain", trf(T_OTA_OK, ota.ver));
  rebootPending = true;
}

// ---------- Buscar e instalar por internet (la placa unida a una red con internet) ----------
// «Buscar actualizaciones» (orden otacheck) lee ota.json en la web del proyecto: última versión y dirección de su .ota.
// «Actualizar» (orden update) lo descarga de las Releases de GitHub y lo instala con el mismo código que la subida
// por la web (otaFeed/otaFinish): firma y versión se comprueban igual. Va en una tarea aparte para que el Bluetooth
// y la web sigan respondiendo; el progreso sale en el estado («op», 0–100) y el resultado como respuesta «update:…».
// TLS sin comprobar el certificado a propósito: lo que garantiza que la actualización es buena es su firma, no el
// servidor del que venga (y las descargas de GitHub saltan entre varios dominios con certificados distintos).
#define OTA_MANIFEST "https://wttc.favala.es/descargas/ota.json"
// Cada placa descarga su propio programa (el de un ESP32 no arranca en un ESP32-S3 y viceversa; el cargador de
// arranque además lo rechaza por el identificador de chip de la cabecera)
#if defined(CONFIG_IDF_TARGET_ESP32S3)
#define OTA_KEY "ota_s3"
#else
#define OTA_KEY "ota"
#endif

// Valor de una clave de un JSON sencillo y plano: {"version":"0.1.5","ota":"https://…"}
String jsonStr(const String& j, const char* k) {
  String key = String("\"") + k + "\":\"";
  int a = j.indexOf(key); if (a < 0) return "";
  a += key.length(); int b = j.indexOf('"', a);
  return b < 0 ? "" : j.substring(a, b);
}

// Espera a la red con internet (la Wi-Fi puede estar apagada por ahorro: loop() la enciende con wifiUntil)
bool otaNetWait() {
  for (int i = 0; i < 40 && WiFi.status() != WL_CONNECTED; i++) vTaskDelay(pdMS_TO_TICKS(1000));
  return WiFi.status() == WL_CONNECTED;
}

void otaNetDone(bool ok, const String& msg) { otaNetOk = ok; otaNetMsg = msg; otaProg = -1; otaNetEnd = true; }

void otaNetTask(void*) {
  bool install = otaNetInstall;
  if (!otaNetWait()) { otaNetDone(false, tr(T_OTA_NONET)); vTaskDelete(nullptr); return; }
  WiFiClientSecure cli; cli.setInsecure();
  HTTPClient http; http.setConnectTimeout(10000); http.setTimeout(15000);
  http.setFollowRedirects(HTTPC_STRICT_FOLLOW_REDIRECTS);
  // 1) ¿qué versión hay?
  String body;
  if (http.begin(cli, OTA_MANIFEST)) { if (http.GET() == 200) body = http.getString(); http.end(); }
  String ver = jsonStr(body, "version"), url = jsonStr(body, OTA_KEY);
  otaNetNotes = jsonStr(body, "notas");                   // novedades de esa versión (del CHANGELOG)
  if (!ver.length() || !url.startsWith("https://") || ver.length() > 16) { otaNetDone(false, tr(T_OTA_NONET)); vTaskDelete(nullptr); return; }
  strlcpy(otaNetVer, ver.c_str(), sizeof otaNetVer);
  if (verCmp(otaNetVer, FW_VERSION) <= 0) { otaNetDone(true, trf(T_OTA_LATEST, FW_VERSION)); vTaskDelete(nullptr); return; }
  otaNetNew = true;
  if (!install) { otaNetDone(true, trf(T_OTA_NEW, otaNetVer, FW_VERSION) + (otaNetNotes.length() ? " " + otaNetNotes : String(""))); vTaskDelete(nullptr); return; }
  // 2) descargar e instalar
  if (heaterOn) { otaNetDone(false, tr(T_OTA_HEAT)); vTaskDelete(nullptr); return; }
  int code = -1;
  if (http.begin(cli, url)) code = http.GET();
  if (code != 200) { http.end(); otaNetDone(false, code == 404 ? trf(T_OTA_NOTYET, otaNetVer) : String(tr(T_OTA_NONET))); vTaskDelete(nullptr); return; }
  int total = http.getSize(), got = 0;
  WiFiClient* st = http.getStreamPtr();
  if (ota.mdOn) mbedtls_md_free(&ota.md);
  ota = Ota(); ota.active = true; otaProg = 0;
  uint8_t buf[1024]; uint32_t last = millis();
  while (!ota.failed && (total < 0 || got < total) && millis() - last < 20000) {
    if (heaterOn) { otaFail(T_OTA_HEAT); break; }               // un programa ha encendido la calefacción: se deja
    size_t av = st->available();
    if (!av) { if (!http.connected()) break; vTaskDelay(pdMS_TO_TICKS(5)); continue; }
    int n = st->readBytes(buf, min(av, sizeof buf));
    if (n <= 0) continue;
    otaFeed(buf, n); got += n; last = millis();
    if (total > 0) otaProg = (int)((int64_t)got * 100 / total);
  }
  http.end();
  if (!ota.failed && total > 0 && got < total) otaFail(T_OTA_WRITE);   // descarga cortada
  if (!ota.failed) otaFinish();
  if (ota.done && heaterOn) { ota.done = false; esp_ota_set_boot_partition(esp_ota_get_running_partition()); otaFail(T_OTA_HEAT); }
  ota.active = false;
  otaNetDone(ota.done, ota.done ? trf(T_OTA_OK, otaNetVer) : String(ota.err ? ota.err : tr(T_OTA_WRITE)));
  vTaskDelete(nullptr);
}

// Lanza la búsqueda (install = false) o la actualización completa (install = true). Devuelve el error, o "" si arranca
// autoCheck = búsqueda diaria para avisar por Telegram: solo si la Wi-Fi ya está conectada (no la enciende).
String otaNetStart(bool install, bool autoCheck) {
  if (otaNetBusy) return tr(T_OTA_BUSY);
  if (install && heaterOn) return tr(T_OTA_HEAT);
  if (!staSsid[0]) return tr(T_OTA_NONET);
  if (!autoCheck && (int32_t)(wifiUntil - (millis() + 120000)) < 0) wifiUntil = millis() + 120000;   // Wi-Fi encendida mientras dura
  otaNetBusy = true; otaNetEnd = false; otaNetInstall = install; otaNetAuto = autoCheck; otaNetNew = false; otaNetNotes = "";
  if (!autoCheck) lastWebMsg = "";                    // la web espera a que aparezca el resultado nuevo
  if (xTaskCreatePinnedToCore(otaNetTask, "ota", 12288, nullptr, 1, nullptr, 0) != pdPASS) { otaNetBusy = false; return tr(T_OTA_WRITE); }
  return "";
}

// En loop(): cuando la tarea termina, se apunta, se responde a la app y, si se instaló, se reinicia
void otaNetPoll() {
  // Búsqueda diaria: con Telegram configurado y la Wi-Fi ya conectada; la primera, 5 min después de arrancar
  if (!otaNetBusy && tgToken[0] && tgChat[0] && WiFi.status() == WL_CONNECTED && !heaterOn
      && (otaAutoLast ? millis() - otaAutoLast > 86400000UL : millis() > 300000)) {
    otaAutoLast = millis();
    otaNetStart(false, true);
  }
  if (!otaNetEnd) return;
  otaNetEnd = false; otaNetBusy = false;
  if (otaNetAuto) {                                       // aviso por Telegram, una sola vez por versión
    if (otaNetOk && otaNetNew) {
      prefs.begin("webasto", false);
      String last = prefs.isKey("otanv") ? prefs.getString("otanv") : String("");
      if (last != otaNetVer) {
        notify(trf(T_TG_OTA_NEW, otaNetVer, otaNetNotes.length() ? otaNetNotes.c_str() : "-"));
        prefs.putString("otanv", otaNetVer);
      }
      prefs.end();
    }
    return;
  }
  if (otaNetOk && otaNetInstall) { addLog(trf(T_LOG_OTA, otaNetVer)); rebootPending = true; }
  bleSet(chResp, String(otaNetInstall ? "update:" : "otacheck:") + (otaNetOk ? "" : "err ") + otaNetMsg);
  lastWebMsg = otaNetMsg;
}

// Vuelta atrás: el núcleo confirmaría el programa nuevo nada más arrancar, pero rollback.cpp le dice «más tarde».
// Lo confirmamos aquí tras un minuto funcionando; si antes se cuelga o se reinicia, arranca el anterior.
bool otaChecked = false;
void otaConfirm() {
  if (otaChecked || millis() < 60000) return;
  otaChecked = true;
  esp_ota_img_states_t st;
  if (esp_ota_get_state_partition(esp_ota_get_running_partition(), &st) == ESP_OK && st == ESP_OTA_IMG_PENDING_VERIFY) {
    esp_ota_mark_app_valid_cancel_rollback();
    addLog(trf(T_LOG_OTA_OK, FW_VERSION));
  }
}

// Primer uso: con la clave de fábrica de la Wi-Fi, la web no admite órdenes (solo cambiar la clave y poner la hora)
bool setupDone() {
  if (!apDefault()) return true;
  server.send(403, "text/plain", tr(T_W_SETUP));
  return false;
}

// POST /api/on (min=minutos): encender
void handleOn() {
  int m = server.arg("min").toInt();
  if (m <= 0) m = 30;
  bool ok = startHeater(m, "manual");
  server.send(ok ? 200 : 502, "text/plain", ok ? "ok" : tr(T_E_ON));
}

// POST /api/off: apagar
void handleOff() {
  bool ok = stopHeater(tr(T_SRC_MANUAL), true);
  server.send(200, "text/plain", ok ? "ok" : tr(T_W_OFF_NOCONF));
}

// POST /api/sched (auto, list): guardar programas
void handleSched() {
  applySched(server.arg("auto"), server.arg("list"));
  server.send(200, "text/plain", "ok");
}

// POST /api/time (epoch): poner en hora desde el navegador
void handleTime() {
  long e = server.arg("epoch").toInt();
  if (e < 1700000000) { server.send(400, "text/plain", tr(T_E_TIME)); return; }
  struct timeval tv = { (time_t)e, 0 };
  settimeofday(&tv, nullptr);
  addLog(tr(T_LOG_TIME_WEB));
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
  // Primer uso (clave de fábrica): solo se acepta si trae la clave nueva; lo demás se configura después
  if (apDefault() && !server.arg("appass").length()) { server.send(403, "text/plain", tr(T_W_SETUP)); return; }
  for (const char* k : CFG_KEYS) {
    if (!server.hasArg(k)) continue;              // solo los campos que se han enviado
    int r = cfgSet(k, server.arg(k), err);
    if (r == 0) { server.send(400, "text/plain", err); return; }   // el primer valor incorrecto corta y se explica
    if (r == 2) restart = true;
  }
  addLog(tr(T_LOG_CFG));
  if (!restart) { server.send(200, "text/plain", tr(T_W_SAVED)); return; }
  if (heaterOn) { server.send(200, "text/plain", tr(T_W_SAVED_LATER)); return; }
  server.send(200, "text/plain", tr(T_W_SAVED_REBOOT));
  rebootPending = true;
}

// POST /api/tgtest: aviso de prueba por Telegram
void handleTgTest() {
  String r = runCmd("tgtest");
  if (r.startsWith("tgtest:err ")) { server.send(400, "text/plain", r.substring(11)); return; }
  server.send(200, "text/plain", tr(T_W_TG_SENDING));
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
  else if (l == "off") stopHeater(tr(T_SRC_CONSOLE), true);
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
  else if (l == "cfg") Serial.println(cfgJson(true));
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
  // Las órdenes (POST) solo se aceptan desde la propia web de la placa (ver sameOrigin)
  server.on("/api/on", HTTP_POST, [] { if (sameOrigin() && setupDone()) handleOn(); });
  server.on("/api/off", HTTP_POST, [] { if (sameOrigin() && setupDone()) handleOff(); });
  server.on("/api/sched", HTTP_POST, [] { if (sameOrigin() && setupDone()) handleSched(); });
  server.on("/api/time", HTTP_POST, [] { if (sameOrigin()) handleTime(); });
  server.on("/api/errors", HTTP_GET, [] { server.send(200, "application/json", errorsJson()); });
  server.on("/api/cfg", HTTP_GET, [] { server.send(200, "application/json", cfgJson(!apDefault())); });
  server.on("/api/cfg", HTTP_POST, [] { if (sameOrigin()) handleCfgPost(); });
  server.on("/api/tgtest", HTTP_POST, [] { if (sameOrigin() && setupDone()) handleTgTest(); });
  server.on("/api/gasreset", HTTP_POST, [] { if (!sameOrigin() || !setupDone()) return; runCmd("gasreset"); server.send(200, "text/plain", tr(T_W_GASRESET)); });
  server.on("/api/forget", HTTP_POST, [] { if (!sameOrigin() || !setupDone()) return; int n = bleForgetAll(); server.send(200, "text/plain", trf(T_W_FORGOT, n)); });
  // Actualización sin cable: misma protección que las órdenes; el primer manejador responde, el segundo recibe el fichero
  server.on("/api/update", HTTP_POST, [] { if (sameOrigin() && setupDone()) handleUpdateDone(); },
            [] { if (originOk() && !apDefault()) handleUpdateUpload(); });
  server.on("/api/otacheck", HTTP_POST, [] { if (!sameOrigin() || !setupDone()) return; String e = otaNetStart(false, false);
    server.send(e.length() ? 400 : 202, "text/plain", e.length() ? e : String("")); });
  server.on("/api/otaupdate", HTTP_POST, [] { if (!sameOrigin() || !setupDone()) return; String e = otaNetStart(true, false);
    server.send(e.length() ? 400 : 202, "text/plain", e.length() ? e : String("")); });
  static const char* HDRS[] = {"Origin"};          // cabeceras que el servidor guarda para leerlas en los manejadores
  server.collectHeaders(HDRS, 1);
  server.onNotFound([] { server.sendHeader("Location", "/"); server.send(302); });   // cualquier otra ruta: a la página

  bleInit();
  wifiUntil = millis() + WIFI_BOOT_MS;   // rescate: Wi-Fi encendida los primeros minutos en cualquier modo
  wifiStart();

  addLog(trf(T_LOG_BOOT, FW_VERSION));
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
    if ((int32_t)(now - onUntil) >= 0) stopHeater(tr(T_WHY_END), true);   // se acabó el tiempo pedido
    else if (now - lastKA >= KEEPALIVE_MS) {
      // Mensaje de mantenimiento: «sigue con la orden 0x21». Sin él, la Webasto se apaga sola.
      uint8_t d[2] = {0x21, 0x00}, r[64], n;
      lastKA = now;
      if (wbusCmd(0x44, d, 2, r, n)) {
        if (kaFails >= 5) addLog(tr(T_LOG_BUS_BACK));
        kaFails = 0;
        // Respuesta 00: sigue con la orden. 01: ya no la tiene, se ha apagado por su cuenta (dos seguidas para descartar errores)
        if (n >= 1 && r[0] == 0x01) { if (++kaOff >= 2) heaterQuit(tr(T_WHY_NO_ORDER)); }
        else kaOff = 0;
      } else if (++kaFails == 5) {                   // 25 s sin respuesta: avisar
        phase = PH_LOST;
        addLog(tr(T_LOG_BUS_LOST));
        notify(tr(T_TG_BUS_LOST));
      } else if (kaFails >= 24) {                    // 2 minutos sin respuesta: se da por apagada
        stopHeater(tr(T_WHY_NO_COMM), false);
        stopNote = hhmm() + tr(T_NOTE_LOST);
        notify(tr(T_TG_LOST));
      }
    }
  }
  // Sensores: cada SENSOR_MS si está encendida o hay alguien mirando (web abierta o app conectada en los últimos 15 s)
  if ((heaterOn || now - lastUi < 15000) && now - lastSensor >= SENSOR_MS) {
    if (readSensors() && heaterOn) { gasTick(); evalHeater(); }
  }
  checkSchedule();                                // ¿toca encender por programa?
  otaConfirm();                                   // tras una actualización: confirmarla al minuto de funcionar
  otaNetPoll();                                   // ¿ha terminado una búsqueda o descarga por internet?
}
