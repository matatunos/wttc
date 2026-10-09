/*
  ============================================================================================================
  WTTC — controlador W-Bus para Webasto Thermo Top C (VW 7H0 010 398 J)
  ============================================================================================================

  >>> Código generado íntegramente con Claude (Anthropic), a partir de las indicaciones del autor del proyecto.
  >>> Todo el contenido de este repositorio (firmware, app, servidor, web y documentación) está generado con Claude.

  Copyright (C) 2026 matatunos. Software libre: se puede redistribuir y modificar bajo la GNU General Public License
  versión 3 o posterior (GPL-3.0-or-later), publicada por la Free Software Foundation; texto completo en LICENSE.
  Se distribuye SIN NINGUNA GARANTÍA, ni siquiera la implícita de comerciabilidad o idoneidad para un fin concreto.

  Qué es
  ------
  Sustituye al temporizador original de la calefacción auxiliar de agua Webasto Thermo Top C (la que montan
  de fábrica, por ejemplo, las VW T5) por un ESP32. El ESP32 habla el protocolo W-Bus de la Webasto a través
  de un transceptor TJA1020 y se maneja desde una app Android (Bluetooth LE) o desde el navegador (Wi-Fi).

  Hardware
  --------
    Placa:        ESP32-S3 DevKitC-1 N16R8 (16 MB de flash). Solo ESP32-S3: el ESP32 clásico dejó de estar
                  soportado en la versión 0.2.0 (no compila: ver el #error de abajo).
    Transceptor:  TJA1020 (TTL de 3,3 V <-> bus K-Line/LIN de un hilo a 12 V), borna LIN al cable W-Bus
    Alimentación: regulador LM2596 a 5,0 V desde el +12 V permanente del conector del temporizador
    Conexiones:   TX del TJA1020 -> IO16 (RX2 del ESP32) · RX del TJA1020 <- IO17 (TX2) · SLP -> 3V3
    Opcional:     pantalla OLED I2C: SSD1327 de 1,5" (128×128, 16 grises), SH1106 de 1,3" o SSD1306 de 0,96" (128×64),
                  y termómetro I2C (SHT31 o AHT20),
                  los dos en el mismo bus: SDA -> IO4, SCL -> IO5, más 3V3 y GND. La placa los detecta al arrancar
                  (y cada medio minuto si faltan); sin ellos funciona igual. El LED RGB de la placa (IO48) da el
                  estado de un vistazo y el botón BOOT (IO0) enciende la pantalla.

  Código y documentación
  ----------------------
    Repositorio:  https://github.com/matatunos/wttc
    Web:          https://wttc.favala.es (simulador, esquema, guías de instalación y uso)

  Compilar
  --------
    Arduino IDE:  placa "ESP32S3 Dev Module" (Flash Size 16MB, PSRAM «OPI PSRAM»), núcleo ESP32 2.x o 3.x.
                  La PSRAM (8 MB en la N16R8) da margen a las conexiones seguras con Bluetooth y Wi-Fi a la vez.
                  Sin librerías externas. Esquema de partición: "Huge APP" (solo para el límite de tamaño del IDE:
                  la tabla que se graba es partitions.csv, con dos huecos para las actualizaciones sin cable).
    arduino-cli:  --fqbn esp32:esp32:esp32s3:FlashSize=16M,PSRAM=opi,PartitionScheme=huge_app
    La carpeta debe llamarse WTTC y contener WTTC.ino y web.h (la página web que sirve la placa).

  Cómo se maneja
  --------------
    - Bluetooth LE (principal): app Android «WTTC». Emparejamiento con PIN de 6 cifras; el PIN se genera
      al azar en el primer arranque y sale por la consola serie y en la web (apartado Configuración).
    - Wi-Fi propia "WTTC" -> http://192.168.4.1 (segunda opción). Es un portal cautivo: al conectarse, el móvil abre
      solo la web de la placa (como la Wi-Fi de un hotel). Se puede dejar siempre encendida,
      solo mientras calienta o solo a petición, para ahorrar batería. Tras arrancar siempre está 10 min encendida.
    - Si se configura una red con internet (casa o punto de acceso del móvil): http://wttc.local
    - Consola serie (115200 baudios): on [min] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset
    - Con el termómetro: «calienta hasta 20 °C» (termostato; cada encendido, 15 min como mínimo con el agua fría y 5 con
      el agua caliente) y la hora de salida
      («salgo a las 8:00»: la placa decide cuánto antes encender según el frío que haga), sueltos o en los programas.
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
    Calentando, si la batería baja de la mínima (menos BATT_RUN_DROP) se apaga para poder arrancar el motor.
  ============================================================================================================
*/

// ---------- bibliotecas (todas vienen con el núcleo ESP32; no hay que instalar nada) ----------
#include <WiFi.h>               // Wi-Fi: punto de acceso propio y conexión a otra red
#include <WebServer.h>          // servidor HTTP para la web de la placa
#include <ESPmDNS.h>            // nombre wttc.local en la red local
#include <DNSServer.h>          // portal cautivo: en la Wi-Fi propia, cualquier nombre lleva a la placa
#include <atomic>               // contador de tareas que salen a internet (portal en pausa mientras tanto)
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
#include <Wire.h>               // bus I2C: pantalla y termómetro opcionales
#include <math.h>               // NAN / isnan(): «sin dato» del termómetro
#if !defined(CONFIG_IDF_TARGET_ESP32S3)
#error "WTTC necesita un ESP32-S3: en el IDE, placa ESP32S3 Dev Module (Flash Size 16MB). El ESP32 clásico no está soportado desde la 0.2.0."
#endif
#include "web.h"                // INDEX_HTML: la página web completa (va aparte para que el preprocesador no la toque)
#include "fuentes.h"            // letras suavizadas de la pantalla SSD1327 (generadas con herramientas/generar_fuentes.py)

// ================== CONFIGURACIÓN FIJA ==================
// Valores que no cambian de una placa a otra. Lo demás se cambia desde la web o la app (Configuración).
#define WBUS_RX 16                    // pin del ESP32 que recibe del TJA1020 (borna TX de la placa)
#define WBUS_TX 17                    // pin del ESP32 que transmite al TJA1020 (borna RX de la placa)
#define I2C_SDA 4                     // bus I2C de la pantalla y el termómetro (opcionales): datos
#define I2C_SCL 5                     // bus I2C: reloj
#define LED_PIN 48                    // LED RGB WS2812 de la placa ESP32-S3 N16R8
#define BTN_PIN 0                     // botón BOOT de la placa: enciende la pantalla un minuto
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
const uint8_t  TGT_MIN      = 5;      // °C: objetivos admitidos para el termostato (más de 25 °C dentro no tiene sentido)
const uint8_t  TGT_MAX      = 25;
const uint16_t MAX_SESSION  = 240;    // min: ventana máxima de «calentar hasta X °C» (se enciende y apaga dentro de ella)
const float    TH_HYST      = 1.5;    // °C: con el termostato, vuelve a encender al bajar esto por debajo del objetivo
const uint32_t TH_MINRUN    = 900000; // ms: mínimo por encendido con termostato y el agua fría (las Webasto no llevan bien
                                      // los arranques cortos: la cámara de combustión tiene que coger temperatura)
const uint32_t TH_MINRUN_WARM = 300000; // ms: el mínimo si arrancó con el agua ya caliente (ciclos del termostato): así
const int      TH_WARM_C    = 30;     // no se pasa tanto del objetivo. °C del agua a partir de los que cuenta como caliente
const uint32_t TH_REST      = 180000; // ms: tras apagarse, espera antes de volver a encender (termina su postbarrido)
const uint32_t TH_STALL     = 1500000;// ms: calentando sin que dentro suba TH_STALL_C, el termostato se da por vencido
const float    TH_STALL_C   = 0.5;    // °C (si hace demasiado frío fuera o el termómetro está mal puesto, no gasta en balde)
const float    BATT_RUN_DROP = 0.5;   // V: calentando, se apaga si la batería baja de la mínima menos esto (con carga baja más)
const uint32_t BATT_GRACE   = 180000; // ms: al arrancar la bujía tira mucho; la batería no se vigila hasta pasado este tiempo
const uint32_t DISP_MS      = 60000;  // ms que la pantalla sigue encendida (modo automático) tras el último motivo
#define FW_VERSION "0.2.18"   // debe coincidir con el fichero VERSION de la raíz del repo (lo comprueba la CI)

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
uint8_t wifiMode   = WM_ALWAYS;       // por defecto: Wi-Fi siempre encendida (desde la 0.2.14; antes, solo mientras calienta)
float minVolt      = 12.0;            // V: con la batería por debajo, los programas no arrancan
char staSsid[33]   = "", staPass[64] = "";   // red con internet a la que unirse (opcional), y su contraseña
// Login de la web cuando se entra desde otra red (casa, camping…): por la Wi-Fi propia ya se ha puesto su clave.
// De fábrica «wttc» / «wttc» (públicos): con ellos, desde otra red la web obliga a cambiarlos antes de dejar hacer nada
#define WEB_DEFAULT "wttc"
char webUser[24]   = WEB_DEFAULT, webPass[64] = WEB_DEFAULT;
// Código de instalación: al azar, creado una vez y guardado para siempre (sobrevive a las actualizaciones). Identifica
// las estadísticas de esta placa en https://wttc.favala.es/mi.php; al cambiar de placa se escribe el de la vieja.
// No sale del hardware (la MAC se emite por Bluetooth y Wi-Fi: cualquiera cerca podría calcularlo)
char iid[20] = "";                    // XXXX-XXXX-XXXX-XXXX
bool statsOn = false;                 // enviar las estadísticas de esta placa (opcional, apagado de fábrica)
// Pantalla, LED y termómetro (opcionales)
// Tipo de pantalla: 1,3" (SH1106), 0,96" (SSD1306) o 1,5" en grises (SSD1327). No se distinguen por I2C: es un ajuste
enum { OLED_SH1106, OLED_SSD1306, OLED_SSD1327 };
enum { DISP_OFF, DISP_AUTO, DISP_ALWAYS };
uint8_t oledType   = OLED_SH1106;
uint8_t dispMode   = DISP_AUTO;       // apagada (solo con el botón) / automática (se enciende con algo que ver y se apaga al minuto) / siempre
uint8_t ledLvl     = 1;               // brillo del LED: 0 apagado, 1 bajo, 2 medio, 3 alto
float tOff         = 0;               // °C que se suman a la lectura del termómetro (corrección; el de la placa calienta)
uint8_t warmC      = 0;               // °C del agua para el aviso «ya está caliente» (0 = sin aviso)

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
uint32_t otaAutoLast = 0;             // última búsqueda automática (al arrancar y luego una al día)
// Actualizaciones automáticas: 0 = no buscar, 1 = buscar y avisar (web, app y Telegram), 2 = buscar e instalar sola
// (nunca calentando ni con el termostato en marcha). otaAvail = versión nueva encontrada ("" = ninguna)
enum { OA_OFF, OA_NOTIFY, OA_INSTALL };
uint8_t otaAuto = OA_NOTIFY;
char otaAvail[17] = "";

// ---------- objetos globales ----------
const uint8_t MAX_SCHED = 8;          // número máximo de programas semanales
HardwareSerial wbus(2);               // UART2 del ESP32: la del W-Bus
WebServer server(80);                 // servidor web en el puerto 80
DNSServer dns;                        // portal cautivo: responde a cualquier nombre con la IP de la red propia
volatile bool dnsOn = false;
// Tareas que están saliendo a internet (actualizaciones, Telegram). Mientras haya alguna, loop() deja el portal
// cautivo en pausa, para que su servidor de nombres no se cruce con las búsquedas de nombres de la propia placa
std::atomic<int> netUse{0};
Preferences prefs;                    // acceso a la memoria no volátil (espacio de nombres "webasto")

// Un programa semanal: activo, días (bit0 = lunes … bit6 = domingo), hora de inicio en minutos y duración
struct Sched { uint8_t en, days; uint16_t start, dur; };
Sched sch[MAX_SCHED];
// Opciones de cada programa (aparte de Sched para no cambiar lo guardado por versiones anteriores):
// bits 0–5 = temperatura objetivo en °C (0 = sin termostato), bit 7 = la hora es la de salida (enciende antes)
uint8_t schX[MAX_SCHED];
const uint8_t SX_DEP = 0x80, SX_TGT = 0x3F;
uint32_t depDone[MAX_SCHED];          // última salida ya atendida de cada programa (minuto absoluto), para no repetir
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
uint32_t heatStart = 0;               // millis() del último encendido (mínimo con termostato, gracia de la batería)
uint32_t lastSensorOk = 0;            // última lectura de sensores de la Webasto que salió bien
int lowBatt = 0;                      // lecturas seguidas con la batería baja calentando
bool warmSent = false;                // ya se avisó de «agua caliente» en este encendido
bool quietStart = false;              // encendidos del termostato: al registro, pero sin aviso por Telegram
bool heatWarm = false;                // este encendido empezó con el agua caliente (mínimo de TH_MINRUN_WARM)

// ---------- termómetro del habitáculo (opcional, I2C) ----------
enum { SN_NONE, SN_SHT31, SN_AHT20 };
uint8_t snType = SN_NONE, snAddr = 0;
float cabT = NAN, cabH = NAN;         // °C (ya con la corrección tOff) y % de humedad; NAN = sin dato
uint32_t snLast = 0, snTrig = 0, hwProbeAt = 0;
bool snWait = false;                  // medida pedida, esperando a que el sensor la termine
int snFails = 0;

// ---------- «calentar hasta X °C» (termostato) ----------
// Una sesión tiene una ventana (thUntil) y una temperatura objetivo: dentro de ella la calefacción se enciende si
// hace frío y se apaga al llegar, cada vez como mínimo TH_MINRUN. Los encendidos sueltos siguen sin sesión.
bool thActive = false, thReached = false;
uint8_t thTarget = 0;
uint32_t thUntil = 0;
String thSrc;
float thBest = NAN;                   // la temperatura más alta de dentro en este encendido, y cuándo subió por última vez
uint32_t thBestAt = 0;
// Salida suelta («salgo a las 8:00»): minuto absoluto (time()/60) de la salida y su objetivo; 0 = ninguna
uint32_t depOnce = 0, depOnceDone = 0;
uint8_t depOnceT = 0;

// ---------- pantalla y LED ----------
bool oledOk = false, dispIsOn = false;
uint32_t dispUntil = 0, lastDraw = 0;
uint32_t btnUntil = 0;                // pulsado el botón BOOT: encendida hasta aquí aunque el modo sea «apagada»
uint8_t fb[1024];                     // imagen de la pantalla de 128×64: 128 columnas × 8 páginas de 8 píxeles

// ---------- sesiones de la web (login desde otra red) ----------
char sess[4][33];                     // fichas de sesión vigentes (en RAM: al reiniciar la placa hay que volver a entrar)
uint8_t sessNext = 0;
uint8_t loginFails = 0;               // intentos fallidos seguidos; con 5, 5 min de espera
uint32_t loginLockUntil = 0;

// ---------- registro de encendidos (para las estadísticas de esta placa) ----------
// Cada encendido de la Webasto, al apagarse, deja un registro de 28 bytes en un anillo de RUNS guardado en la flash
// (clave "runs2"; hasta la 0.2.16 eran 20 bytes en "runs", y al arrancar se pasan al formato nuevo).
// seq numera los encendidos desde 1; los ya enviados al servidor llegan hasta runAck.
#define RUNS 48
enum { RE_TIME, RE_USER, RE_TARGET, RE_BATT, RE_FAULT, RE_NOCOMM, RE_STALL };   // por qué se apagó
enum { RS_WEB, RS_APP, RS_PROG, RS_CONSOLE, RS_DEP, RS_OTHER };         // quién la encendió (+0x80: con termostato)
struct Run {
  uint32_t seq, t0;                   // número de encendido y hora de inicio (segundos UNIX; 0 = sin hora)
  uint16_t dur, ml;                   // duración (s) y gasoil estimado (ml)
  int8_t cab0, cab1;                  // °C dentro al empezar y al acabar (-128 = sin termómetro)
  uint8_t cmax, vmin, src, end, err, pad;   // °C máx. del agua, tensión mín. (décimas de V; 0 = sin dato), RS_*, RE_*, avería
  // Desde la 0.2.17 (255 = sin dato):
  uint8_t hum0, hum1;                 // % de humedad dentro al empezar y al acabar
  uint8_t tgt, treach;                // objetivo del termostato (°C; 0 = sin termostato) y minutos hasta llegar
  uint8_t c0, v0;                     // agua al empezar (°C + 50) y batería al empezar (décimas de V)
  uint8_t pw, pad2;                   // potencia media de la Webasto (en pasos de 25 W)
};
struct RunV1 {                        // formato hasta la 0.2.16, solo para pasar los guardados al nuevo
  uint32_t seq, t0; uint16_t dur, ml; int8_t cab0, cab1; uint8_t cmax, vmin, src, end, err, pad;
};
Run runs[RUNS];
uint32_t runSeq = 0, runAck = 0, heatSecTot = 0;   // último encendido apuntado, último enviado, segundos calentando en total
uint8_t runEnd = RE_USER, runSrc = RS_WEB, runErr = 0;
int runCmax = -999; float runVmin = 99, runCab0 = NAN;
uint32_t runT0 = 0;
float runHum0 = NAN;                  // humedad dentro al empezar
uint8_t runReach = 255, runC0 = 0, runV0 = 0;   // minutos hasta el objetivo; agua y batería al empezar (como en Run)
uint32_t runPwSum = 0, runPwN = 0;    // para la potencia media
bool runDep = false;                  // el encendido que va a empezar es por hora de salida
volatile bool statsBusy = false;      // envío de estadísticas en marcha (tarea aparte)
uint32_t statsLast = 0, statsOkAt = 0;  // último intento y último envío correcto (millis)
uint8_t gb[8192], gbPrev[8192];       // imagen de la SSD1327 (dos píxeles por byte) y la última enviada
uint8_t oledAddr = 0x3C;              // dirección I2C de la pantalla (0x3C o 0x3D, según el módulo)

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
  T_LOG_HW, T_LOG_TH_ON, T_LOG_TH_WAIT, T_LOG_TH_END, T_LOG_TH_NOSENS, T_TG_TH_REACHED, T_WHY_TARGET,
  T_E_NOSENS, T_E_TARGET, T_WHY_BATT, T_NOTE_BATT, T_TG_WARM, T_LOG_DEP, T_LOG_DEP_SET, T_LOG_DEP_OFF, T_E_DEP,
  T_E_WARM, T_E_TOFF, T_E_VALUE, T_TG_TH_STALL, T_OTA_NOSTA, T_OTA_NETERR, T_LOG_OTA_AUTO, T_LOG_OTA_FAIL,
  T_W_LOGIN, T_W_LOGINBAD, T_W_LOGINLOCK, T_W_SETUPWEB, T_E_WEBUSER, T_E_WEBPASS, T_E_WEBDEF, T_LOG_LOGIN,
  T_E_IID, T_TG_IID, T_E_NOTG, T_LOG_IID,
  T_D_OFF, T_D_START, T_D_HEAT, T_D_PAUSE, T_D_LOST, T_D_WAIT, T_D_WATER, T_D_IN, T_D_NEXT, T_D_DEP, T_D_UNTIL,
  T_D_DAYS, T_D_NOTE,
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
  /* T_LOG_HW */           {"Detectado: %s", "Detected: %s", "Erkannt: %s"},
  /* T_LOG_TH_ON */        {"Termostato: hasta %s, durante %d min como mucho", "Thermostat: up to %s, for at most %d min", "Thermostat: bis %s, höchstens %d min"},
  /* T_LOG_TH_WAIT */      {"Termostato: ya hay %s dentro, espera sin calentar", "Thermostat: already %s inside, waiting without heating", "Thermostat: schon %s innen, wartet ohne zu heizen"},
  /* T_LOG_TH_END */       {"Termostato terminado", "Thermostat finished", "Thermostat beendet"},
  /* T_LOG_TH_NOSENS */    {"Termostato cancelado: no hay lectura del termómetro", "Thermostat cancelled: no thermometer reading", "Thermostat abgebrochen: kein Thermometerwert"},
  /* T_TG_TH_REACHED */    {"Ya hay %s dentro. Se mantiene hasta que acabe el tiempo.", "It is already %s inside. It will be kept until the time is up.", "Innen sind schon %s. Wird bis zum Ende der Zeit gehalten."},
  /* T_WHY_TARGET */       {"%s alcanzados", "%s reached", "%s erreicht"},
  /* T_E_NOSENS */         {"No hay termómetro: conecta uno (SHT31 o AHT20) para calentar hasta una temperatura.", "No thermometer: connect one (SHT31 or AHT20) to heat up to a temperature.", "Kein Thermometer: eines anschließen (SHT31 oder AHT20), um bis zu einer Temperatur zu heizen."},
  /* T_E_TARGET */         {"La temperatura objetivo debe estar entre 5 y 25 °C.", "The target temperature must be between 5 and 25 °C.", "Die Zieltemperatur muss zwischen 5 und 25 °C liegen."},
  /* T_WHY_BATT */         {"batería baja (%s V)", "low battery (%s V)", "Batterie schwach (%s V)"},
  /* T_NOTE_BATT */        {"Se apagó para proteger la batería: %s V con la calefacción en marcha.", "It was switched off to protect the battery: %s V while heating.", "Zum Schutz der Batterie ausgeschaltet: %s V während des Heizens."},
  /* T_TG_WARM */          {"Ya está caliente: el agua del motor está a %d °C.", "It is warm: the engine coolant is at %d °C.", "Es ist warm: das Kühlwasser hat %d °C."},
  /* T_LOG_DEP */          {"Salida a las %s: enciende %d min antes (%s)", "Departure at %s: switching on %d min before (%s)", "Abfahrt um %s: schaltet %d min vorher ein (%s)"},
  /* T_LOG_DEP_SET */      {"Salida programada: %s", "Departure set: %s", "Abfahrt eingestellt: %s"},
  /* T_LOG_DEP_OFF */      {"Salida cancelada", "Departure cancelled", "Abfahrt gelöscht"},
  /* T_E_DEP */            {"Hora de salida no válida (o la placa no está en hora).", "Invalid departure time (or the board's clock is not set).", "Ungültige Abfahrtszeit (oder die Uhr der Platine ist nicht gestellt)."},
  /* T_E_WARM */           {"El aviso de agua caliente debe estar entre 30 y 80 °C (0 = sin aviso).", "The warm-water notice must be between 30 and 80 °C (0 = none).", "Die Warmwasser-Meldung muss zwischen 30 und 80 °C liegen (0 = keine)."},
  /* T_E_TOFF */           {"La corrección del termómetro debe estar entre -5 y 5 °C.", "The thermometer correction must be between -5 and 5 °C.", "Die Thermometerkorrektur muss zwischen -5 und 5 °C liegen."},
  /* T_E_VALUE */          {"Valor no válido: %s", "Invalid value: %s", "Ungültiger Wert: %s"},
  /* T_TG_TH_STALL */      {"Termostato parado: dentro no pasa de %s en %d min y no llegará a %s. Se ha apagado para no gastar en balde.",
                            "Thermostat stopped: inside it stays at %s after %d min and will not reach %s. Switched off so as not to waste fuel.",
                            "Thermostat gestoppt: innen bleibt es bei %s (nach %d min) und erreicht %s nicht. Ausgeschaltet, um nichts zu verschwenden."},
  /* T_OTA_NOSTA */        {"La placa no consigue unirse a «%s»: ¿están bien el nombre y la contraseña, y llega la señal? Sin esa red no puede buscar actualizaciones.",
                            "The board cannot join “%s”: are the name and password right, and does the signal reach? Without that network it cannot check for updates.",
                            "Die Platine kann sich nicht mit „%s“ verbinden: stimmen Name und Passwort, und reicht das Signal? Ohne dieses Netz kann sie nicht nach Updates suchen."},
  /* T_OTA_NETERR */       {"La placa está en la red pero no llega a wttc.favala.es (código %d · ese nombre le da %s · DNS %s · router %s).",
                            "The board is on the network but cannot reach wttc.favala.es (code %d · that name gives %s · DNS %s · router %s).",
                            "Die Platine ist im Netz, erreicht aber wttc.favala.es nicht (Code %d · der Name ergibt %s · DNS %s · Router %s)."},
  /* T_LOG_OTA_AUTO */     {"Instalando sola la versión %s (actualizaciones automáticas)", "Installing version %s by itself (automatic updates)", "Installiert Version %s selbst (automatische Updates)"},
  /* T_LOG_OTA_FAIL */     {"La actualización automática falló: %s", "The automatic update failed: %s", "Das automatische Update ist fehlgeschlagen: %s"},
  /* T_W_LOGIN */          {"Hace falta entrar con usuario y clave.", "You need to log in with user and password.", "Anmeldung mit Benutzer und Passwort nötig."},
  /* T_W_LOGINBAD */       {"Usuario o clave incorrectos.", "Wrong user or password.", "Benutzer oder Passwort falsch."},
  /* T_W_LOGINLOCK */      {"Demasiados intentos: espera %d min.", "Too many attempts: wait %d min.", "Zu viele Versuche: %d min warten."},
  /* T_W_SETUPWEB */       {"Entras desde otra red con el usuario y la clave de fábrica (wttc / wttc), que son públicos: cámbialos en Configuración antes de nada.",
                            "You are connected from another network with the factory user and password (wttc / wttc), which are public: change them in Settings first.",
                            "Du bist aus einem anderen Netz mit Benutzer und Passwort ab Werk (wttc / wttc) verbunden, die öffentlich sind: zuerst in den Einstellungen ändern."},
  /* T_E_WEBUSER */        {"El usuario de la web debe tener entre 1 y 23 caracteres.", "The web user must be 1 to 23 characters long.", "Der Web-Benutzer muss 1 bis 23 Zeichen lang sein."},
  /* T_E_WEBPASS */        {"La clave de la web debe tener entre 6 y 63 caracteres.", "The web password must be 6 to 63 characters long.", "Das Web-Passwort muss 6 bis 63 Zeichen lang sein."},
  /* T_E_WEBDEF */         {"Elige una clave de la web distinta de la de fábrica (wttc).", "Choose a web password other than the factory one (wttc).", "Wähle ein anderes Web-Passwort als das ab Werk (wttc)."},
  /* T_LOG_LOGIN */        {"Entrada en la web desde otra red (%s)", "Web login from another network (%s)", "Web-Anmeldung aus einem anderen Netz (%s)"},
  /* T_E_IID */            {"Código de instalación no válido: son 16 letras y números, como ABCD-2345-EFGH-6789.", "Invalid installation code: 16 letters and digits, like ABCD-2345-EFGH-6789.", "Ungültiger Installationscode: 16 Buchstaben und Ziffern, wie ABCD-2345-EFGH-6789."},
  /* T_TG_IID */           {"Código de instalación de «%s» (va solo en el mensaje siguiente, para copiarlo). Tus estadísticas: https://wttc.favala.es/mi.php#%s",
                            "Installation code of “%s” (alone in the next message, to copy it). Your statistics: https://wttc.favala.es/mi.php#%s",
                            "Installationscode von „%s“ (allein in der nächsten Nachricht, zum Kopieren). Deine Statistiken: https://wttc.favala.es/mi.php#%s"},
  /* T_E_NOTG */           {"Primero configura Telegram (token del bot y chat ID).", "Set up Telegram first (bot token and chat ID).", "Zuerst Telegram einrichten (Bot-Token und Chat-ID)."},
  /* T_LOG_IID */          {"Código de instalación cambiado (estadísticas de otra placa)", "Installation code changed (statistics of another board)", "Installationscode geändert (Statistiken einer anderen Platine)"},
  /* T_D_OFF */            {"Apagada", "Off", "Aus"},
  /* T_D_START */          {"Arrancando", "Starting", "Startet"},
  /* T_D_HEAT */           {"Calentando", "Heating", "Heizt"},
  /* T_D_PAUSE */          {"En pausa", "Paused", "Pause"},
  /* T_D_LOST */           {"Sin respuesta", "No answer", "Keine Antwort"},
  /* T_D_WAIT */           {"Esperando", "Waiting", "Wartet"},
  /* T_D_WATER */          {"Agua %d°", "Water %d°", "Wasser %d°"},
  /* T_D_IN */             {"dentro", "inside", "innen"},
  /* T_D_NEXT */           {"Próx. %s", "Next %s", "Nächst %s"},
  /* T_D_DEP */            {"Salida %s", "Leave %s", "Abfahrt %s"},
  /* T_D_UNTIL */          {"Hasta %s", "Up to %s", "Bis %s"},
  /* T_D_DAYS */           {"Lu,Ma,Mi,Ju,Vi,Sa,Do", "Mo,Tu,We,Th,Fr,Sa,Su", "Mo,Di,Mi,Do,Fr,Sa,So"},
  /* T_D_NOTE */           {"Aviso: mira la app", "Notice: see the app", "Hinweis: siehe App"},
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
// raw = el texto tal cual, sin «Webasto · hora» delante (para mandar algo que se vaya a copiar, como el código)
void notifyMsg(const String& m, bool raw) {
  if (!tgQueue || !tgToken[0] || !tgChat[0]) return;
  Msg x;
  if (raw) strlcpy(x.t, m.c_str(), sizeof x.t);
  else snprintf(x.t, sizeof x.t, "Webasto · %s%s", hhmm().c_str(), m.c_str());   // "Webasto · 07:42 Encendida…"
  if (xQueueSend(tgQueue, &x, 0) != pdTRUE) strlcpy(tgLast, tr(T_TG_QUEUE_FULL), sizeof tgLast);
  // Con la Wi-Fi apagada por ahorro, se enciende unos minutos (3) para que el aviso pueda salir
  if (staSsid[0] && (int32_t)(wifiUntil - (millis() + 180000)) < 0) wifiUntil = millis() + 180000;
}
void notify(const String& m) { notifyMsg(m, false); }

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
    netBegin();
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
    netEnd();
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
    lastSensorOk = millis();
    if (heaterOn) {                            // para el registro del encendido: agua máx. y batería mín.
      if (tempC > runCmax) runCmax = tempC;
      if (power >= 0) { runPwSum += power; runPwN++; }
      if (volt > 5 && volt < runVmin) runVmin = volt;
    }
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

// ---------- Registro de encendidos ----------
// Primera avería guardada en la Webasto (0 = ninguna o no responde)
uint8_t firstFault() {
  uint8_t d[1] = {0x01}, r[64], n;
  if (wbusCmd(0x56, d, 1, r, n) && n >= 3 && r[1] > 0) return r[2];
  return 0;
}

// Al apagarse: apunta el encendido en el anillo y lo guarda (con el total de horas)
void runSave() {
  Run& x = runs[runSeq % RUNS];
  x.seq = ++runSeq;
  x.t0 = runT0;
  uint32_t d = (millis() - heatStart) / 1000;
  x.dur = d > 65535 ? 65535 : d;
  float ml = gasCur * 1000; x.ml = ml < 0 ? 0 : ml > 65535 ? 65535 : (uint16_t)ml;
  auto c8 = [](float t) -> int8_t { return isnan(t) ? -128 : (int8_t)constrain((int)lroundf(t), -60, 90); };
  x.cab0 = c8(runCab0); x.cab1 = c8(cabT);
  x.cmax = runCmax > -50 ? (uint8_t)constrain(runCmax + 50, 1, 255) : 0;     // +50, como en el W-Bus; 0 = sin dato
  x.vmin = runVmin < 99 ? (uint8_t)constrain((int)lroundf(runVmin * 10), 1, 255) : 0;
  x.src = runSrc; x.end = runEnd; x.err = runErr; x.pad = 0;
  auto h8 = [](float h) -> uint8_t { return isnan(h) ? 255 : (uint8_t)constrain((int)lroundf(h), 0, 100); };
  x.hum0 = h8(runHum0); x.hum1 = h8(cabH);
  x.tgt = (runSrc & 0x80) ? thTarget : 0; x.treach = runReach;
  x.c0 = runC0; x.v0 = runV0;
  x.pw = runPwN ? (uint8_t)min<uint32_t>(254, runPwSum / runPwN / 25) : 255; x.pad2 = 0;
  heatSecTot += d;
  prefs.begin("webasto", false);
  prefs.putBytes("runs2", runs, sizeof runs);
  if (prefs.isKey("runs")) prefs.remove("runs");      // el formato de la 0.2.16, ya pasado al nuevo
  prefs.putUInt("rseq", runSeq);
  prefs.putUInt("hsec", heatSecTot);
  prefs.end();
}

// Informe de estadísticas (lo envía la placa, o la app si la placa no tiene internet): totales y hasta «max» encendidos
// aún no enviados, sin pasar de maxLen bytes (por Bluetooth una respuesta no puede pasar de 512: caben unos 4).
// Los encendidos van con su número: el servidor ignora los repetidos
String statsJson(int max, unsigned maxLen) {
  String j = "{\"iid\":"; j += js(String(iid));
  j += ",\"fw\":\""; j += FW_VERSION; j += "\"";
  j += ",\"lang\":\""; j += LANG_CODES[lang]; j += "\"";
  j += ",\"gas\":"; j += String(gasTotal, 2);
  j += ",\"hsec\":"; j += heatSecTot;
  j += ",\"nruns\":"; j += runSeq;
  j += ",\"oled\":"; j += (int)oledType; j += ",\"sens\":"; j += isnan(cabT) ? 0 : 1;
  j += ",\"runs\":[";
  uint32_t from = runAck + 1;
  if (runSeq > RUNS && from < runSeq - RUNS + 1) from = runSeq - RUNS + 1;   // los más viejos ya no están en el anillo
  int n = 0;
  for (uint32_t q = from; q <= runSeq && n < max; q++) {
    const Run& x = runs[(q - 1) % RUNS];
    if (x.seq != q) continue;
    char b[128];
    snprintf(b, sizeof b, "[%lu,%lu,%u,%u,%d,%d,%u,%u,%u,%u,%u,%u,%u,%u,%u,%u,%u,%u]", (unsigned long)x.seq, (unsigned long)x.t0,
             x.dur, x.ml, x.cab0, x.cab1, x.cmax, x.vmin, x.src, x.end, x.err, x.hum0, x.hum1, x.tgt, x.treach, x.c0, x.v0, x.pw);
    if (j.length() + strlen(b) + 3 > maxLen) break;
    if (n++) j += ",";
    j += b;
  }
  j += "]}";
  return j;
}

void runAckSet(uint32_t a) {
  if (a > runSeq) a = runSeq;
  if (a <= runAck) return;
  runAck = a;
  prefs.begin("webasto", false); prefs.putUInt("rack", runAck); prefs.end();
}

// Código de instalación: 16 caracteres sin los que se confunden (0/O, 1/I), en grupos de 4
const char IID_ABC[] = "23456789ABCDEFGHJKLMNPQRSTUVWXYZ";
// Normaliza lo escrito (mayúsculas, sin guiones ni espacios) y lo deja en out con guiones; false si no vale
bool iidParse(const String& v, char* out) {
  char c16[17]; int n = 0;
  for (unsigned i = 0; i < v.length(); i++) {
    char c = toupper(v[i]);
    if (c == '-' || c == ' ') continue;
    if (!strchr(IID_ABC, c) || n >= 16) return false;
    c16[n++] = c;
  }
  if (n != 16) return false;
  for (int g = 0, k = 0; g < 4; g++) { for (int i = 0; i < 4; i++) out[k++] = c16[g * 4 + i]; out[k++] = g < 3 ? '-' : 0; }
  return true;
}

// ---------- Envío de las estadísticas por la placa (tarea aparte, como la actualización) ----------
String statsBody;                     // lo prepara el bucle principal; la tarea solo lo envía
volatile uint32_t statsAckNew = 0;
volatile bool statsEnd = false;
void statsWork() {
  netBegin();
  WiFiClientSecure cli; cli.setInsecure();         // como la actualización: no se manda nada secreto (el código, sí,
  HTTPClient http;                                 // pero sin él no se ve más que estadísticas)
  http.setConnectTimeout(10000); http.setTimeout(15000);
  if (http.begin(cli, "https://wttc.favala.es/api/placa.php")) {
    http.addHeader("Content-Type", "application/json");
    if (http.POST(statsBody) == 200) {
      String r = http.getString();
      int i = r.indexOf("\"ack\":");
      if (i >= 0) statsAckNew = strtoul(r.c_str() + i + 6, nullptr, 10);
    }
    http.end();
  }
  netEnd();
}
void statsTask(void*) { statsWork(); statsEnd = true; vTaskDelete(nullptr); }

// Desde el bucle: con «enviar estadísticas» activado y red con internet, a los 3 min de arrancar, cada 10 min mientras
// haya encendidos sin enviar, y una vez al día para lo demás
void statsPoll() {
  if (statsEnd) {
    statsEnd = false; statsBusy = false;
    if (statsAckNew) { runAckSet(statsAckNew); statsOkAt = millis(); statsAckNew = 0; }
    statsBody = "";
    return;
  }
  if (!statsOn || statsBusy || otaNetBusy || !iid[0] || WiFi.status() != WL_CONNECTED || millis() < 180000) return;
  uint32_t wait = runSeq > runAck ? 600000UL : 86400000UL;
  if (statsLast && millis() - statsLast < wait) return;
  statsLast = millis();
  statsBody = statsJson(RUNS, 8000);
  statsBusy = true;
  if (xTaskCreatePinnedToCore(statsTask, "stats", 12288, nullptr, 1, nullptr, 0) != pdPASS) statsBusy = false;
}

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
      // Datos del registro de este encendido (se guarda al apagarse, ver runSave)
      runT0 = timeValid() ? (uint32_t)time(nullptr) : 0; runCab0 = cabT; runCmax = tempC; runVmin = volt > 5 ? volt : 99;
      runHum0 = cabH; runReach = 255; runPwSum = runPwN = 0;
      runC0 = tempC > -50 ? (uint8_t)constrain(tempC + 50, 1, 255) : 0;
      runV0 = volt > 5 ? (uint8_t)constrain((int)lroundf(volt * 10), 1, 255) : 0;
      runSrc = runDep ? RS_DEP : !strcmp(src, "app") ? RS_APP : !strcmp(src, "programa") ? RS_PROG
             : !strcmp(src, "consola") ? RS_CONSOLE : !strcmp(src, "manual") ? RS_WEB : RS_OTHER;
      if (thActive) runSrc |= 0x80;
      runEnd = RE_USER; runErr = 0;
      heatStart = millis(); warmSent = false; lowBatt = 0;   // mínimo del termostato, aviso del agua y batería
      heatWarm = tempC >= TH_WARM_C && lastSensorOk && millis() - lastSensorOk < 120000;
      thBest = cabT; thBestAt = millis();         // para ver si dentro sube (termostato)
      dispWake();
      addLog(trf(T_LOG_ON, srcName(src), minutes));
      if (!quietStart) notify(trf(T_LOG_ON, srcName(src), minutes));   // los del termostato van solo al registro
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
  if (was) { gasTick(); gasRate = 0; lastGasT = 0; gasLast = gasCur; gasSave(); runSave(); }
  runEnd = RE_USER; runErr = 0;
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
  endSession(true);                               // con una avería no se vuelve a intentar sola
  runEnd = RE_FAULT; runErr = firstFault();      // para el registro del encendido
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
// Termómetro del habitáculo (opcional): SHT31 (dirección 0x44 o 0x45) o AHT20 (0x38), en el bus I2C de IO4/IO5.
// La medida se pide y se lee en dos pasos (el sensor tarda 15–80 ms) para no parar loop() esperándola.
// ============================================================================================================
bool i2cPing(uint8_t a) { Wire.beginTransmission(a); return Wire.endTransmission() == 0; }

// CRC-8 de los sensores de Sensirion (polinomio 0x31, valor inicial 0xFF)
uint8_t crc8(const uint8_t* d, int n) {
  uint8_t c = 0xFF;
  for (int i = 0; i < n; i++) {
    c ^= d[i];
    for (int b = 0; b < 8; b++) c = (c & 0x80) ? (uint8_t)((c << 1) ^ 0x31) : (uint8_t)(c << 1);
  }
  return c;
}

const char* snName() { return snType == SN_SHT31 ? "SHT31" : snType == SN_AHT20 ? "AHT20" : ""; }

// Busca el termómetro en el bus
void snProbe() {
  snType = SN_NONE;
  if (i2cPing(0x44)) { snType = SN_SHT31; snAddr = 0x44; }
  else if (i2cPing(0x45)) { snType = SN_SHT31; snAddr = 0x45; }
  else if (i2cPing(0x38)) {
    snType = SN_AHT20; snAddr = 0x38;
    // Si no está calibrado (bit 3 del estado a 0) se le manda la orden de inicializar
    Wire.requestFrom((int)snAddr, 1);
    uint8_t st = Wire.available() ? Wire.read() : 0;
    if (!(st & 0x08)) {
      Wire.beginTransmission(snAddr); Wire.write((uint8_t)0xBE); Wire.write((uint8_t)0x08); Wire.write((uint8_t)0x00); Wire.endTransmission();
      delay(10);
    }
  }
  snWait = false; snFails = 0; snLast = 0;
}

// Lectura fallida: tras 3 seguidas, sin dato; tras 6, se da por desconectado y se vuelve a buscar
void snFail() {
  snLast = millis();
  if (++snFails >= 3) cabT = cabH = NAN;
  if (snFails >= 6) snType = SN_NONE;
}

// Una medida cada 10 s: primero se pide, y en una vuelta posterior de loop() se lee
void snTick() {
  if (snType == SN_NONE) return;
  uint32_t now = millis();
  if (!snWait) {
    if (snLast && now - snLast < 10000) return;
    Wire.beginTransmission(snAddr);
    if (snType == SN_SHT31) { Wire.write((uint8_t)0x24); Wire.write((uint8_t)0x00); }     // medida única, precisión alta
    else { Wire.write((uint8_t)0xAC); Wire.write((uint8_t)0x33); Wire.write((uint8_t)0x00); }     // AHT20: medir
    bool ok = Wire.endTransmission() == 0;
    snTrig = now; snWait = ok;
    if (!ok) snFail();
    return;
  }
  if (now - snTrig < (snType == SN_SHT31 ? 30u : 100u)) return;      // tiempo de medida: 15 ms y 80 ms
  snWait = false; snLast = now;
  uint8_t d[7]; int n = snType == SN_SHT31 ? 6 : 7, got = 0;
  Wire.requestFrom((int)snAddr, n);
  while (Wire.available() && got < n) d[got++] = Wire.read();
  float t = NAN, h = NAN;
  if (got == n) {
    if (snType == SN_SHT31) {
      if (crc8(d, 2) == d[2] && crc8(d + 3, 2) == d[5]) {
        t = -45 + 175.0f * ((d[0] << 8) | d[1]) / 65535.0f;
        h = 100.0f * ((d[3] << 8) | d[4]) / 65535.0f;
      }
    } else if (!(d[0] & 0x80)) {                                       // bit 7 a 1 = aún midiendo
      // Sin comprobar el CRC: algunos AHT20 de imitación no lo mandan bien. Lo filtra el rango de abajo
      uint32_t rh = ((uint32_t)d[1] << 12) | ((uint32_t)d[2] << 4) | (d[3] >> 4);
      uint32_t rt = ((uint32_t)(d[3] & 0x0F) << 16) | ((uint32_t)d[4] << 8) | d[5];
      h = rh * 100.0f / 1048576.0f;
      t = rt * 200.0f / 1048576.0f - 50;
    }
  }
  if (isnan(t) || t < -40 || t > 85) { snFail(); return; }
  snFails = 0;
  cabT = t + tOff;
  cabH = constrain(h, 0.0f, 100.0f);
}

// ============================================================================================================
// Pantalla OLED I2C de 128×64 (opcional; dirección 0x3C). Controlador mínimo propio para no depender de librerías:
// la imagen se compone en fb[] y se manda entera. La SH1106 (1,3") y la SSD1306 (0,96") se manejan casi igual
// (modo de páginas); cambian el arranque y que la SH1106 tiene 132 columnas. No se distinguen por I2C: es un ajuste.
// ============================================================================================================
// Tipo de letra 5×7 (columnas, bit 0 arriba): ASCII de 0x20 a 0x7E y 0x7F = símbolo de grado
static const uint8_t FONT[] PROGMEM = {
  0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x5F, 0x00, 0x00, 0x00, 0x07, 0x00, 0x07, 0x00, 0x14, 0x7F, 0x14, 0x7F, 0x14,
  0x24, 0x2A, 0x7F, 0x2A, 0x12, 0x23, 0x13, 0x08, 0x64, 0x62, 0x36, 0x49, 0x55, 0x22, 0x50, 0x00, 0x05, 0x03, 0x00, 0x00,
  0x00, 0x1C, 0x22, 0x41, 0x00, 0x00, 0x41, 0x22, 0x1C, 0x00, 0x14, 0x08, 0x3E, 0x08, 0x14, 0x08, 0x08, 0x3E, 0x08, 0x08,
  0x00, 0x50, 0x30, 0x00, 0x00, 0x08, 0x08, 0x08, 0x08, 0x08, 0x00, 0x60, 0x60, 0x00, 0x00, 0x20, 0x10, 0x08, 0x04, 0x02,
  0x3E, 0x51, 0x49, 0x45, 0x3E, 0x00, 0x42, 0x7F, 0x40, 0x00, 0x42, 0x61, 0x51, 0x49, 0x46, 0x21, 0x41, 0x45, 0x4B, 0x31,
  0x18, 0x14, 0x12, 0x7F, 0x10, 0x27, 0x45, 0x45, 0x45, 0x39, 0x3C, 0x4A, 0x49, 0x49, 0x30, 0x01, 0x71, 0x09, 0x05, 0x03,
  0x36, 0x49, 0x49, 0x49, 0x36, 0x06, 0x49, 0x49, 0x29, 0x1E, 0x00, 0x36, 0x36, 0x00, 0x00, 0x00, 0x56, 0x36, 0x00, 0x00,
  0x08, 0x14, 0x22, 0x41, 0x00, 0x14, 0x14, 0x14, 0x14, 0x14, 0x00, 0x41, 0x22, 0x14, 0x08, 0x02, 0x01, 0x51, 0x09, 0x06,
  0x32, 0x49, 0x79, 0x41, 0x3E, 0x7E, 0x11, 0x11, 0x11, 0x7E, 0x7F, 0x49, 0x49, 0x49, 0x36, 0x3E, 0x41, 0x41, 0x41, 0x22,
  0x7F, 0x41, 0x41, 0x22, 0x1C, 0x7F, 0x49, 0x49, 0x49, 0x41, 0x7F, 0x09, 0x09, 0x09, 0x01, 0x3E, 0x41, 0x49, 0x49, 0x7A,
  0x7F, 0x08, 0x08, 0x08, 0x7F, 0x00, 0x41, 0x7F, 0x41, 0x00, 0x20, 0x40, 0x41, 0x3F, 0x01, 0x7F, 0x08, 0x14, 0x22, 0x41,
  0x7F, 0x40, 0x40, 0x40, 0x40, 0x7F, 0x02, 0x0C, 0x02, 0x7F, 0x7F, 0x04, 0x08, 0x10, 0x7F, 0x3E, 0x41, 0x41, 0x41, 0x3E,
  0x7F, 0x09, 0x09, 0x09, 0x06, 0x3E, 0x41, 0x51, 0x21, 0x5E, 0x7F, 0x09, 0x19, 0x29, 0x46, 0x46, 0x49, 0x49, 0x49, 0x31,
  0x01, 0x01, 0x7F, 0x01, 0x01, 0x3F, 0x40, 0x40, 0x40, 0x3F, 0x1F, 0x20, 0x40, 0x20, 0x1F, 0x3F, 0x40, 0x38, 0x40, 0x3F,
  0x63, 0x14, 0x08, 0x14, 0x63, 0x07, 0x08, 0x70, 0x08, 0x07, 0x61, 0x51, 0x49, 0x45, 0x43, 0x00, 0x7F, 0x41, 0x41, 0x00,
  0x02, 0x04, 0x08, 0x10, 0x20, 0x00, 0x41, 0x41, 0x7F, 0x00, 0x04, 0x02, 0x01, 0x02, 0x04, 0x40, 0x40, 0x40, 0x40, 0x40,
  0x00, 0x01, 0x02, 0x04, 0x00, 0x20, 0x54, 0x54, 0x54, 0x78, 0x7F, 0x48, 0x44, 0x44, 0x38, 0x38, 0x44, 0x44, 0x44, 0x20,
  0x38, 0x44, 0x44, 0x48, 0x7F, 0x38, 0x54, 0x54, 0x54, 0x18, 0x08, 0x7E, 0x09, 0x01, 0x02, 0x0C, 0x52, 0x52, 0x52, 0x3E,
  0x7F, 0x08, 0x04, 0x04, 0x78, 0x00, 0x44, 0x7D, 0x40, 0x00, 0x20, 0x40, 0x44, 0x3D, 0x00, 0x7F, 0x10, 0x28, 0x44, 0x00,
  0x00, 0x41, 0x7F, 0x40, 0x00, 0x7C, 0x04, 0x18, 0x04, 0x78, 0x7C, 0x08, 0x04, 0x04, 0x78, 0x38, 0x44, 0x44, 0x44, 0x38,
  0x7C, 0x14, 0x14, 0x14, 0x08, 0x08, 0x14, 0x14, 0x18, 0x7C, 0x7C, 0x08, 0x04, 0x04, 0x08, 0x48, 0x54, 0x54, 0x54, 0x20,
  0x04, 0x3F, 0x44, 0x40, 0x20, 0x3C, 0x40, 0x40, 0x20, 0x7C, 0x1C, 0x20, 0x40, 0x20, 0x1C, 0x3C, 0x40, 0x30, 0x40, 0x3C,
  0x44, 0x28, 0x10, 0x28, 0x44, 0x0C, 0x50, 0x50, 0x50, 0x3C, 0x44, 0x64, 0x54, 0x4C, 0x44, 0x00, 0x08, 0x36, 0x41, 0x00,
  0x00, 0x00, 0x7F, 0x00, 0x00, 0x00, 0x41, 0x36, 0x08, 0x00, 0x08, 0x04, 0x08, 0x10, 0x08, 0x00, 0x06, 0x09, 0x09, 0x06,
};

void oCmds(const uint8_t* c, int n) {
  Wire.beginTransmission(oledAddr); Wire.write((uint8_t)0x00);       // 0x00 = lo que sigue son órdenes
  for (int i = 0; i < n; i++) Wire.write(c[i]);
  Wire.endTransmission();
}

// Manda fb[] a la pantalla: 8 páginas de 128 bytes, en trozos de 16 (el búfer de Wire es de 128)
void oledFlush() {
  uint8_t col = oledType == OLED_SH1106 ? 2 : 0;           // la SH1106 tiene 132 columnas: la imagen empieza en la 2
  for (int pg = 0; pg < 8; pg++) {
    uint8_t c[3] = {(uint8_t)(0xB0 + pg), (uint8_t)(col & 0x0F), (uint8_t)(0x10 | (col >> 4))};
    oCmds(c, 3);
    for (int x = 0; x < 128; x += 16) {
      Wire.beginTransmission(oledAddr); Wire.write((uint8_t)0x40);   // 0x40 = lo que sigue es imagen
      Wire.write(fb + pg * 128 + x, 16);
      if (Wire.endTransmission() != 0) { oledOk = false; dispIsOn = false; return; }   // desconectada: se vuelve a buscar
    }
  }
}

// Busca la pantalla y la prepara (apagada; dispTick() la enciende cuando toca)
void oledInit() {
  dispIsOn = false;
  oledAddr = i2cPing(0x3C) ? 0x3C : 0x3D;
  oledOk = i2cPing(oledAddr);
  if (!oledOk) return;
  if (oledType == OLED_SSD1327) {
    // Desbloquear, apagar, contraste, giro (columnas y filas), línea 0, sin desplazamiento, normal, 128 filas, fases,
    // reloj, regulador interno, segunda precarga, VCOMH, precarga y selección de funciones (como el módulo de 1,5")
    static const uint8_t C4[] = {0xFD, 0x12, 0xAE, 0x81, 0x60, 0xA0, 0x51, 0xA1, 0x00, 0xA2, 0x00, 0xA4, 0xA8, 0x7F,
                                 0xB1, 0x11, 0xB3, 0x00, 0xAB, 0x01, 0xB6, 0x04, 0xBE, 0x0F, 0xBC, 0x08, 0xD5, 0x62};
    oCmds(C4, sizeof C4);
    memset(gb, 0, sizeof gb);
    memset(gbPrev, 0xFF, sizeof gbPrev);          // la memoria de la pantalla no se conoce: se manda todo
    gFlush();
    return;
  }
  // Apagar, reloj, 64 líneas, sin desplazamiento, línea 0, giro horizontal y vertical (conector arriba), pines COM,
  // contraste bajo (alarga la vida de la OLED y de noche sobra), sin invertir
  static const uint8_t C1[] = {0xAE, 0xD5, 0x80, 0xA8, 0x3F, 0xD3, 0x00, 0x40, 0xA1, 0xC8, 0xDA, 0x12, 0x81, 0x50, 0xA4, 0xA6};
  oCmds(C1, sizeof C1);
  if (oledType == OLED_SSD1306) { static const uint8_t C2[] = {0x8D, 0x14, 0x20, 0x02, 0xD9, 0xF1, 0xDB, 0x40}; oCmds(C2, sizeof C2); }
  else { static const uint8_t C3[] = {0xAD, 0x8B, 0xD9, 0x22, 0xDB, 0x35}; oCmds(C3, sizeof C3); }
  memset(fb, 0, sizeof fb);
  oledFlush();
}

void oledPower(bool on) {
  if (!oledOk || on == dispIsOn) return;
  uint8_t c = on ? 0xAF : 0xAE;
  oCmds(&c, 1);
  dispIsOn = on;
}

// Busca la pantalla y el termómetro: al arrancar y, si falta alguno, cada 30 s (se pueden conectar después)
void hwProbe() {
  hwProbeAt = millis();
  bool hadO = oledOk, hadS = snType != SN_NONE;
  if (!hadS) snProbe();
  if (!hadO) oledInit();
  String w;
  if (snType != SN_NONE && !hadS) w = snName();
  if (oledOk && !hadO) { if (w.length()) w += ", "; w += oledType == OLED_SH1106 ? "OLED SH1106" : oledType == OLED_SSD1306 ? "OLED SSD1306" : "OLED SSD1327"; }
  if (w.length()) addLog(trf(T_LOG_HW, w.c_str()));
}

void dPix(int x, int y) { if (x >= 0 && x < 128 && y >= 0 && y < 64) fb[x + (y >> 3) * 128] |= 1 << (y & 7); }

// Escribe un texto (ya pasado por plain()) con escala sc (1 = 6 × 8 píxeles por letra). Devuelve dónde acaba
int dText(int x, int y, const String& t, int sc) {
  for (unsigned i = 0; i < t.length(); i++) {
    uint8_t c = t[i];
    if (c < 32 || c > 127) c = '?';
    for (int cx = 0; cx < 5; cx++) {
      uint8_t col = pgm_read_byte(FONT + (c - 32) * 5 + cx);
      for (int cy = 0; cy < 7; cy++)
        if (col >> cy & 1) for (int a = 0; a < sc; a++) for (int b = 0; b < sc; b++) dPix(x + cx * sc + a, y + cy * sc + b);
    }
    x += 6 * sc;
  }
  return x;
}
int dWidth(const String& t, int sc) { return t.length() ? (int)t.length() * 6 * sc - sc : 0; }

// La pantalla solo tiene ASCII: quita los acentos (UTF-8) y pasa «°» al símbolo propio (0x7F)
String plain(const String& s) {
  static const char MAP[] = "AAAAAAACEEEEIIIIDNOOOOOxOUUUUYTsaaaaaaaceeeeiiiidnooooo/ouuuuyty";   // U+00C0 … U+00FF
  String o;
  for (unsigned i = 0; i < s.length(); i++) {
    uint8_t c = s[i];
    if (c < 0x80) { o += (char)c; continue; }
    uint8_t d = i + 1 < s.length() ? (uint8_t)s[i + 1] : 0;
    if (c == 0xC2 && d == 0xB0) { o += (char)0x7F; i++; continue; }
    if (c == 0xC3 && d >= 0x80 && d <= 0xBF) { o += MAP[d - 0x80]; i++; continue; }
    if (c >= 0xC0) o += '?';                                   // otro carácter: un «?» y se saltan sus bytes de continuación
  }
  return o;
}

void dispWake() { dispUntil = millis() + DISP_MS; lastDraw = 0; }

// Próximo programa como «Lu 07:30» (vacío si no hay); isDep = es una hora de salida
String nextSched(bool& isDep) {
  isDep = false;
  if (!autoOn || !timeValid()) return "";
  time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  int wd = (tm.tm_wday + 6) % 7, m = tm.tm_hour * 60 + tm.tm_min, best = -1, bi = -1, bd = 0;
  for (int i = 0; i < nSch; i++) {
    if (!sch[i].en) continue;
    for (int k = 0; k < 8; k++) {
      int d = (wd + k) % 7;
      if (!(sch[i].days >> d & 1) || (k == 0 && sch[i].start <= m)) continue;
      int dt = k * 1440 + sch[i].start - m;
      if (best < 0 || dt < best) { best = dt; bi = i; bd = d; }
      break;
    }
  }
  if (bi < 0) return "";
  isDep = schX[bi] & SX_DEP;
  String days = tr(T_D_DAYS);
  char h[6]; snprintf(h, sizeof h, "%02d:%02d", sch[bi].start / 60, sch[bi].start % 60);
  return days.substring(bd * 3, bd * 3 + 2) + " " + h;
}

// Compone la pantalla: nombre y hora; temperatura grande (la de dentro o, sin termómetro, la del agua); estado;
// agua y batería; y abajo un aviso, el objetivo del termostato, la salida o el próximo programa
void dispDraw() {
  memset(fb, 0, sizeof fb);
  dText(0, 0, plain(String(cfgName).substring(0, 12)), 1);
  if (timeValid()) { String h = hhmm(); h.trim(); dText(128 - dWidth(h, 1), 0, h, 1); }
  String big, s1, s2;
  if (!isnan(cabT)) {
    big = plain(num(cabT, 1)) + (char)0x7F;
    if (!isnan(cabH)) s1 = String((int)lround(cabH)) + "%";
    s2 = plain(tr(T_D_IN));
  } else if (tempC > -100) {
    big = String(tempC) + (char)0x7F;
    String w = plain(tr(T_D_WATER)); s2 = w.substring(0, w.indexOf(' '));   // «Agua %d°» -> «Agua»
  } else big = "--";
  int x = dText(0, 12, big, 3) + 3;
  if (x + dWidth(s1, 1) <= 128) dText(x, 14, s1, 1);
  if (x + dWidth(s2, 1) <= 128) dText(x, s1.length() ? 25 : 14, s2, 1);
  String st;
  if (heaterOn) {
    st = tr(phase == PH_FLAME ? T_D_HEAT : phase == PH_PAUSE ? T_D_PAUSE : phase == PH_LOST ? T_D_LOST : T_D_START);
    int32_t rem = (int32_t)(onUntil - millis()) / 60000 + 1;
    if (rem > 0) st += " " + String(rem) + "'";
  } else st = tr(thActive ? T_D_WAIT : T_D_OFF);
  dText(0, 36, plain(st), 1);
  String inf;
  if (tempC > -100 && !isnan(cabT)) inf = plain(trf(T_D_WATER, tempC));
  if (volt > 0) { if (inf.length()) inf += "  "; inf += plain(num(volt, 1)) + "V"; }
  dText(0, 46, inf, 1);
  String bot;
  bool dep;
  if (!heaterOn && stopNote.length()) bot = tr(T_D_NOTE);
  else if (thActive) bot = trf(T_D_UNTIL, (String(thTarget) + "°").c_str());
  else if (depOnce) {
    time_t dt = (time_t)depOnce * 60; struct tm tm; localtime_r(&dt, &tm);
    char h[6]; strftime(h, sizeof h, "%H:%M", &tm);
    bot = trf(T_D_DEP, h);
  } else {
    String n = nextSched(dep);
    if (n.length()) bot = trf(dep ? T_D_DEP : T_D_NEXT, n.c_str());
  }
  dText(0, 56, plain(bot), 1);
}

// ---------- Pantalla SSD1327 de 128×128 en 16 grises ----------
// Dos píxeles por byte (el de la izquierda en los 4 bits altos), fila a fila: 64 bytes por fila. Las letras son
// suavizadas (fuentes.h): cada píxel se mezcla con el fondo según su opacidad. Por I2C una imagen entera tarda unos
// 0,2 s, así que gFlush() solo manda las filas que han cambiado desde la última vez (de cada segundo, casi solo la hora).
// El dibujo es el mismo que el del simulador de la web (gDraw() en su JavaScript).
void gPix(int x, int y, uint8_t v) {
  if ((unsigned)x > 127 || (unsigned)y > 127) return;
  uint8_t& b = gb[(y << 6) | (x >> 1)];
  b = (x & 1) ? (uint8_t)((b & 0xF0) | v) : (uint8_t)((b & 0x0F) | (v << 4));
}
uint8_t gGet(int x, int y) { uint8_t b = gb[(y << 6) | (x >> 1)]; return (x & 1) ? b & 0x0F : b >> 4; }
void gRect(int x, int y, int w, int h, uint8_t v) { for (int j = y; j < y + h; j++) for (int i = x; i < x + w; i++) gPix(i, j, v); }

// Letra de un código (búsqueda binaria: están ordenadas); si no está, la de «?»
const Glyph* gFind(const Font& f, uint16_t cp) {
  int lo = 0, hi = f.n - 1;
  while (lo <= hi) {
    int m = (lo + hi) >> 1;
    if (f.g[m].cp == cp) return &f.g[m];
    if (f.g[m].cp < cp) lo = m + 1; else hi = m - 1;
  }
  return cp == '?' ? nullptr : gFind(f, '?');
}

// Siguiente carácter de un texto UTF-8 (hasta U+FFFF, que es lo que hay en los textos)
uint16_t utf8Next(const char*& p) {
  uint8_t c = *p++;
  if (c < 0x80) return c;
  if ((c & 0xE0) == 0xC0 && *p) return (uint16_t)((c & 0x1F) << 6 | (*p++ & 0x3F));
  if ((c & 0xF0) == 0xE0 && p[0] && p[1]) { uint16_t r = (c & 0x0F) << 12 | (p[0] & 0x3F) << 6 | (p[1] & 0x3F); p += 2; return r; }
  return '?';
}

// Escribe un texto con la línea base en y, en el gris col (0–15). Devuelve dónde acaba
int gText(int x, int y, const String& t, const Font& f, uint8_t col) {
  const char* p = t.c_str();
  while (*p) {
    const Glyph* g = gFind(f, utf8Next(p));
    if (!g) continue;
    for (int j = 0; j < g->h; j++)
      for (int i = 0; i < g->w; i++) {
        int k = j * g->w + i;
        uint8_t a = (f.bits[g->off + (k >> 1)] >> ((k & 1) ? 0 : 4)) & 15;   // opacidad de este píxel
        if (!a) continue;
        int px = x + g->xo + i, py = y + g->yo + j;
        if ((unsigned)px > 127 || (unsigned)py > 127) continue;
        int bg = gGet(px, py), d = (col - bg) * a;
        gPix(px, py, bg + (d >= 0 ? (d + 7) / 15 : -((-d + 7) / 15)));
      }
    x += g->adv;
  }
  return x;
}
int gWidth(const String& t, const Font& f) {
  int w = 0;
  const char* p = t.c_str();
  while (*p) { const Glyph* g = gFind(f, utf8Next(p)); if (g) w += g->adv; }
  return w;
}

// Compone la pantalla: cabecera con nombre y hora; temperatura grande y debajo qué es (con la humedad); estado y, si
// calienta, barra del tiempo que queda; agua y batería; lo siguiente (objetivo, salida o programa); y una franja de aviso
void gDraw() {
  memset(gb, 0, sizeof gb);
  gText(0, 10, String(cfgName).substring(0, 14), F_SMALL, 9);
  if (timeValid()) { String h = hhmm(); h.trim(); gText(128 - gWidth(h, F_SMALL), 10, h, F_SMALL, 9); }
  gRect(0, 14, 128, 1, 3);
  String big = "--", lab;
  if (!isnan(cabT)) {
    big = num(cabT, 1);
    lab = tr(T_D_IN);
    if (!isnan(cabH)) lab += " · " + String((int)lround(cabH)) + " %";
  } else if (tempC > -100) {
    big = String(tempC);
    String w = tr(T_D_WATER); lab = w.substring(0, w.indexOf(' '));   // «Agua %d°» -> «Agua»
  }
  int x = gText(1, 50, big, F_BIG, 15);
  if (big != "--") gText(x + 1, 50, "°", F_BIG, 15);
  gText(1, 63, lab, F_SMALL, 8);
  String st;
  if (heaterOn) {
    st = tr(phase == PH_FLAME ? T_D_HEAT : phase == PH_PAUSE ? T_D_PAUSE : phase == PH_LOST ? T_D_LOST : T_D_START);
    int32_t rem = (int32_t)(onUntil - millis()) / 1000;
    if (rem < 0) rem = 0;
    String rt = String((rem + 59) / 60) + " min";
    int w = 128 - gWidth(rt, F_SMALL) - 5;
    gRect(0, 83, w, 4, 2);
    gRect(0, 83, onTotal ? (int)((int64_t)w * rem / onTotal) : 0, 4, 12);
    gText(128 - gWidth(rt, F_SMALL), 88, rt, F_SMALL, 10);
  } else st = tr(thActive ? T_D_WAIT : T_D_OFF);
  gText(0, 79, st, F_MED, heaterOn ? 15 : 11);
  if (tempC > -100 && !isnan(cabT)) gText(0, 100, trf(T_D_WATER, tempC), F_SMALL, 9);
  if (volt > 0) { String v = num(volt, 1) + " V"; gText(128 - gWidth(v, F_SMALL), 100, v, F_SMALL, 9); }
  String nx;
  bool dep;
  if (thActive) nx = trf(T_D_UNTIL, (String(thTarget) + " °C").c_str());
  else if (depOnce) {
    time_t dt = (time_t)depOnce * 60; struct tm tm; localtime_r(&dt, &tm);
    char h[6]; strftime(h, sizeof h, "%H:%M", &tm);
    nx = trf(T_D_DEP, h);
  } else {
    String n = nextSched(dep);
    if (n.length()) nx = trf(dep ? T_D_DEP : T_D_NEXT, n.c_str());
  }
  gText(0, 113, nx, F_SMALL, 12);
  if (!heaterOn && stopNote.length()) {           // se apagó sola o por batería: franja clara con letra oscura
    String t = tr(T_D_NOTE);
    gRect(0, 117, 128, 11, 12);
    gText((128 - gWidth(t, F_SMALL)) / 2, 126, t, F_SMALL, 0);
  }
}

// Manda las filas que han cambiado (agrupadas en bloques seguidos) y se las apunta como enviadas
void gFlush() {
  for (int y = 0; y < 128;) {
    if (!memcmp(gb + y * 64, gbPrev + y * 64, 64)) { y++; continue; }
    int y1 = y;
    while (y1 + 1 < 128 && memcmp(gb + (y1 + 1) * 64, gbPrev + (y1 + 1) * 64, 64)) y1++;
    uint8_t c[6] = {0x15, 0x00, 0x3F, 0x75, (uint8_t)y, (uint8_t)y1};   // columnas 0–63 (de dos en dos) y filas y–y1
    oCmds(c, 6);
    for (int r = y; r <= y1; r++) {
      Wire.beginTransmission(oledAddr); Wire.write((uint8_t)0x40);
      Wire.write(gb + r * 64, 64);
      if (Wire.endTransmission() != 0) { oledOk = false; dispIsOn = false; return; }   // desconectada: se vuelve a buscar
    }
    memcpy(gbPrev + y * 64, gb + y * 64, (y1 - y + 1) * 64);
    y = y1 + 1;
  }
}

// Enciende, refresca (cada segundo) y apaga la pantalla según el modo. El botón BOOT la enciende un minuto en
// cualquier modo (también en «apagada»: quien lo pulsa quiere verla)
void dispTick() {
  if (!oledOk) return;
  uint32_t now = millis();
  bool want = dispMode == DISP_ALWAYS || (int32_t)(btnUntil - now) > 0 ||
              (dispMode == DISP_AUTO && (heaterOn || (int32_t)(dispUntil - now) > 0 || now - lastUi < 15000));
  if (!want) { oledPower(false); return; }
  if (dispIsOn && now - lastDraw < 1000) return;
  lastDraw = now;
  if (oledType == OLED_SSD1327) { gDraw(); gFlush(); }
  else { dispDraw(); oledFlush(); }
  oledPower(true);
}

// Botón BOOT: enciende la pantalla un minuto (en cualquier modo)
void btnTick() {
  static bool was = false;
  static uint32_t t = 0;
  bool p = digitalRead(BTN_PIN) == LOW;
  if (p != was && millis() - t > 50) { t = millis(); was = p; if (p) { dispWake(); btnUntil = millis() + DISP_MS; } }
}

// ============================================================================================================
// LED RGB de la placa: el estado de un vistazo, sin el móvil
//   naranja = calentando (parpadea arrancando) · verde = ya está caliente (aviso del agua) · rojo parpadeando = la
//   Webasto no responde · destello rojo cada 3 s = se apagó sola o por batería (hasta el próximo encendido) ·
//   azul claro = termostato esperando · azul = app conectada · apagado = nada que contar
// ============================================================================================================
void ledTick() {
  static uint32_t last = 0, cur = 0xFFFFFFFF;
  uint32_t now = millis();
  if (now - last < 100) return;
  last = now;
  bool blink = (now / 500) & 1;
  uint8_t r = 0, g = 0, b = 0;
  if (heaterOn && (phase == PH_LOST || busState == 0)) { if (blink) r = 255; }
  else if (heaterOn && warmSent) g = 255;
  else if (heaterOn && phase == PH_START) { if (blink) { r = 255; g = 70; } }
  else if (heaterOn) { r = 255; g = 70; }
  else if (stopNote.length()) { if ((now / 250) % 12 == 0) r = 255; }
  else if (thActive) { g = 120; b = 255; }
  else if (bleConn > 0) b = 255;
  static const uint8_t DIV[] = {1, 16, 4, 1};                // brillo bajo, medio y alto
  uint8_t lv = ledLvl > 3 ? 3 : ledLvl;
  if (!lv) r = g = b = 0;
  else { r /= DIV[lv]; g /= DIV[lv]; b /= DIV[lv]; }
  uint32_t c = (uint32_t)r << 16 | (uint32_t)g << 8 | b;
  if (c == cur) return;                                      // solo se escribe al cambiar
  cur = c;
#if ESP_ARDUINO_VERSION_MAJOR >= 3
  rgbLedWrite(LED_PIN, r, g, b);
#else
  neopixelWrite(LED_PIN, r, g, b);
#endif
}

// ============================================================================================================
// Batería, aviso de agua caliente, termostato y hora de salida
// ============================================================================================================
String degs(float t, int dec) { return num(t, dec) + " °C"; }

// Antes de encender sin nadie delante (programa, salida, termostato): con poca batería no se arranca
bool battOk() {
  readSensors();
  if (volt > 0 && volt < minVolt) {
    addLog(trf(T_LOG_SKIP, num(volt, 1).c_str()));
    notify(trf(T_LOG_SKIP, num(volt, 1).c_str()));
    return false;
  }
  return true;
}

// Calentando: si la batería baja de la mínima (menos BATT_RUN_DROP: con la calefacción en marcha cae algo) tres
// lecturas seguidas, se apaga para que luego arranque el motor. Los primeros minutos no, por el tirón de la bujía.
void battRunCheck() {
  if (!heaterOn || millis() - heatStart < BATT_GRACE || volt <= 0 || volt >= minVolt - BATT_RUN_DROP) { lowBatt = 0; return; }
  if (++lowBatt < 3) return;
  String v = num(volt, 1);
  endSession(false);
  runEnd = RE_BATT;
  stopHeater(trf(T_WHY_BATT, v.c_str()).c_str(), true);
  stopNote = hhmm() + trf(T_NOTE_BATT, v.c_str());
  lowBatt = 0;
}

// Aviso de «ya está caliente»: una vez por encendido, al llegar el agua a warmC. Con el termostato, solo el del
// primer encendido va a Telegram (los siguientes, al registro)
void warmCheck() {
  if (!heaterOn || !warmC || warmSent || tempC < warmC) return;
  warmSent = true;
  dispWake();
  addLog(trf(T_TG_WARM, tempC));
  if (!(thActive && thReached)) notify(trf(T_TG_WARM, tempC));
}

void endSession(bool log) {
  if (!thActive) return;
  thActive = false;
  if (log) addLog(tr(T_LOG_TH_END));
}

// Empieza «calentar hasta tgt °C» durante como mucho «minutes» minutos. Necesita el termómetro.
bool startSession(uint16_t minutes, uint8_t tgt, const char* src) {
  if (isnan(cabT)) { addLog(tr(T_LOG_TH_NOSENS)); return false; }
  minutes = constrain(minutes, 1, MAX_SESSION);
  thActive = true; thReached = false; thTarget = tgt; thSrc = src;
  thUntil = millis() + minutes * 60000UL;
  addLog(trf(T_LOG_TH_ON, degs(tgt, 0).c_str(), minutes));
  dispWake();
  if (cabT >= tgt) { thReached = true; addLog(trf(T_LOG_TH_WAIT, degs(cabT, 1).c_str())); return true; }
  if (!startHeater(min(minutes, MAX_MIN), src)) { thActive = false; return false; }
  return true;
}

// Termostato (cada 5 s): apaga al llegar (si ya lleva el mínimo) y vuelve a encender si se enfría
void thermoTick() {
  static uint32_t last = 0;
  uint32_t now = millis();
  if (!thActive || now - last < 5000) return;
  last = now;
  if ((int32_t)(now - thUntil) >= 0) {                      // se acabó la ventana
    endSession(true);
    if (heaterOn) { runEnd = RE_TIME; stopHeater(tr(T_WHY_END), true); }
    return;
  }
  if (isnan(cabT)) { addLog(tr(T_LOG_TH_NOSENS)); thActive = false; return; }   // el encendido en marcha sigue hasta su fin
  if (heaterOn) {
    // ¿Sube la temperatura de dentro? Si en TH_STALL no ha subido TH_STALL_C y aún no llega, no se insiste
    if (isnan(thBest) || cabT >= thBest + TH_STALL_C) { thBest = cabT; thBestAt = now; }
    if (runReach == 255 && cabT >= thTarget) runReach = min<uint32_t>(254, (now - heatStart) / 60000);   // para el registro
    else if (cabT < thTarget && now - thBestAt >= TH_STALL) {
      String m = trf(T_TG_TH_STALL, degs(cabT, 1).c_str(), (int)((now - heatStart) / 60000), degs(thTarget, 0).c_str());
      endSession(false);
      runEnd = RE_STALL;
      stopHeater(tr(T_LOG_TH_END), false);
      addLog(m);
      notify(m);
      stopNote = hhmm() + m;
      return;
    }
    if (cabT >= thTarget && now - heatStart >= (heatWarm ? TH_MINRUN_WARM : TH_MINRUN)) {
      String t = degs(cabT, 1);
      if (!thReached) notify(trf(T_TG_TH_REACHED, t.c_str()));   // solo la primera vez: luego mantiene en silencio
      thReached = true;
      runEnd = RE_TARGET;
      stopHeater(trf(T_WHY_TARGET, t.c_str()).c_str(), false);
    }
    return;
  }
  // Apagada dentro de la ventana: vuelve a encender si se ha enfriado (o si aún no había llegado: un encendido dura
  // como mucho MAX_MIN), queda margen y ya terminó el postbarrido
  uint32_t left = thUntil - now;
  bool cold = cabT <= thTarget - TH_HYST || (!thReached && cabT < thTarget);
  if (cold && left >= TH_MINRUN && (!lastHeatOff || now - lastHeatOff >= TH_REST)) {
    if (!battOk()) { endSession(true); return; }
    quietStart = true;
    bool ok = startHeater(min((uint16_t)(left / 60000), MAX_MIN), thSrc.c_str());
    quietStart = false;
    if (!ok) endSession(true);
  }
}

// Minutos de antelación para una salida según la temperatura: 15 min a 15 °C o más, 1,5 min más por cada grado
// menos, hasta 60 (a −15 °C). Sin dato, 30. Es una estimación sencilla, no un cálculo del motor.
int depLead(float t) {
  if (isnan(t)) return 30;
  return constrain((int)lround(15 + (15 - t) * 1.5), 15, 60);
}

// Temperatura para decidirlo: la de dentro; sin termómetro, la del agua del motor (con el motor frío se parece a la
// de fuera) si es reciente
float depTemp() {
  if (!isnan(cabT)) return cabT;
  if (tempC > -100 && lastSensorOk && millis() - lastSensorOk < 600000) return tempC;
  return NAN;
}

// ¿Toca encender para una salida? depMin = minuto absoluto (time()/60) de la salida; done = la última ya atendida
void depCheck(uint32_t depMin, uint8_t tgt, uint32_t& done, const char* src) {
  uint32_t nowMin = time(nullptr) / 60;
  if (depMin <= nowMin || done == depMin) return;
  uint32_t left = depMin - nowMin;
  if (left > 60) return;
  if (isnan(cabT) && millis() - lastSensor > 300000) readSensors();   // sin termómetro: leer el agua (cada 5 min)
  float t = depTemp();
  int lead = depLead(t);
  if (left > (uint32_t)lead) return;
  done = depMin;                                            // se atiende una sola vez, encienda o no
  if (heaterOn || thActive || left < 10) return;            // ya está calentando, o queda demasiado poco
  if (tgt && !isnan(cabT) && cabT >= tgt) { addLog(trf(T_LOG_TH_WAIT, degs(cabT, 1).c_str())); return; }
  if (!battOk()) return;
  time_t dt = (time_t)depMin * 60; struct tm tm; localtime_r(&dt, &tm);
  char h[6]; strftime(h, sizeof h, "%H:%M", &tm);
  addLog(trf(T_LOG_DEP, h, (int)left, isnan(t) ? "?" : degs(t, 0).c_str()));
  runDep = true;
  if (tgt && !isnan(cabT)) startSession(left, tgt, src);
  else startHeater(min((uint16_t)left, MAX_MIN), src);
  runDep = false;
}

// Próxima vez que el reloj marque hh:mm (hoy o mañana), como minuto absoluto
uint32_t depNext(int h, int mi) {
  time_t t = time(nullptr); struct tm tm; localtime_r(&t, &tm);
  tm.tm_hour = h; tm.tm_min = mi; tm.tm_sec = 0; tm.tm_isdst = -1;
  time_t d = mktime(&tm);
  if (d <= t + 60) { tm.tm_mday += 1; tm.tm_isdst = -1; d = mktime(&tm); }
  return d / 60;
}

// Guarda la salida suelta (0 = ninguna)
void depSet(uint32_t m, uint8_t tgt) {
  depOnce = m; depOnceT = m ? tgt : 0; depOnceDone = 0;
  prefs.begin("webasto", false);
  prefs.putUInt("dep", depOnce);
  prefs.putUChar("dept", depOnceT);
  prefs.end();
  if (!m) return;
  time_t dt = (time_t)m * 60; struct tm tm; localtime_r(&dt, &tm);
  char h[16]; strftime(h, sizeof h, "%d/%m %H:%M", &tm);
  addLog(trf(T_LOG_DEP_SET, (String(h) + (tgt ? " · " + degs(tgt, 0) : String(""))).c_str()));
}

// Encender desde la app, la web o la consola; con objetivo, termostato. Devuelve "" si va bien, o el error
String heatOn(int m, int tg, const char* src) {
  if (tg) {
    if (tg < TGT_MIN || tg > TGT_MAX) return tr(T_E_TARGET);
    if (isnan(cabT)) return tr(T_E_NOSENS);
    endSession(false);
    return startSession(m > 0 ? m : 60, tg, src) ? "" : tr(T_E_ON);
  }
  endSession(false);
  return startHeater(m > 0 ? m : 30, src) ? "" : tr(T_E_ON);
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
  memset(schX, 0, sizeof schX);
  if (nSch && prefs.isKey("schx")) prefs.getBytes("schx", schX, nSch);
  for (int i = 0; i < nSch; i++) {
    int tg = schX[i] & SX_TGT;
    if (tg && (tg < TGT_MIN || tg > TGT_MAX)) schX[i] &= SX_DEP;  // objetivo fuera de rango: sin termostato
    uint16_t lim = (schX[i] & SX_TGT) ? MAX_SESSION : MAX_MIN;     // con termostato, la ventana puede ser más larga
    if (sch[i].dur > lim) sch[i].dur = lim;       // programas de versiones anteriores (antes se permitían 4 h)
  }
  autoOn = prefs.getBool("auto", true);
  // isKey() evita mensajes de error en la consola por claves que aún no existen
  if (prefs.isKey("tgtok"))  prefs.getString("tgtok", tgToken, sizeof tgToken);
  if (prefs.isKey("tgchat")) prefs.getString("tgchat", tgChat, sizeof tgChat);
  if (prefs.isKey("name"))   prefs.getString("name", cfgName, sizeof cfgName);
  if (prefs.isKey("appass")) prefs.getString("appass", cfgApPass, sizeof cfgApPass);
  if (prefs.isKey("ssid"))   prefs.getString("ssid", staSsid, sizeof staSsid);
  if (prefs.isKey("pass"))   prefs.getString("pass", staPass, sizeof staPass);
  if (prefs.isKey("wuser"))  prefs.getString("wuser", webUser, sizeof webUser);
  if (prefs.isKey("wpass"))  prefs.getString("wpass", webPass, sizeof webPass);
  if (prefs.isKey("iid"))    prefs.getString("iid", iid, sizeof iid);
  statsOn  = prefs.getBool("stats", false);
  if (prefs.isKey("runs2") && prefs.getBytesLength("runs2") == sizeof runs) prefs.getBytes("runs2", runs, sizeof runs);
  else if (prefs.isKey("runs") && prefs.getBytesLength("runs") == sizeof(RunV1) * RUNS) {   // de la 0.2.16: al formato nuevo
    RunV1* old = new RunV1[RUNS];
    prefs.getBytes("runs", old, sizeof(RunV1) * RUNS);
    for (int i = 0; i < RUNS; i++) {
      Run& x = runs[i]; const RunV1& o = old[i];
      x.seq = o.seq; x.t0 = o.t0; x.dur = o.dur; x.ml = o.ml; x.cab0 = o.cab0; x.cab1 = o.cab1;
      x.cmax = o.cmax; x.vmin = o.vmin; x.src = o.src; x.end = o.end; x.err = o.err; x.pad = 0;
      x.hum0 = x.hum1 = x.treach = x.pw = 255; x.tgt = x.c0 = x.v0 = x.pad2 = 0;
    }
    delete[] old;
  }
  runSeq   = prefs.getUInt("rseq", 0);
  runAck   = prefs.getUInt("rack", 0);
  heatSecTot = prefs.getUInt("hsec", 0);
  blePin   = prefs.getUInt("pin", 0);
  gasLast  = prefs.getFloat("glast", 0);
  gasMonth = prefs.getFloat("gmon", 0);
  gasTotal = prefs.getFloat("gtot", 0);
  gasMonthKey = prefs.getUInt("gkey", 0);
  wifiMode = prefs.getUChar("wmode", WM_ALWAYS);   // si nunca se ha elegido: siempre encendida
  minVolt  = prefs.getFloat("minv", 12.0);
  lang     = prefs.getUChar("lang", L_ES);
  oledType = prefs.getUChar("oled", OLED_SH1106);
  dispMode = prefs.getUChar("disp", DISP_AUTO);
  ledLvl   = prefs.getUChar("led", 1);
  tOff     = prefs.getFloat("toff", 0);
  warmC    = prefs.getUChar("warm", 0);
  otaAuto  = prefs.getUChar("otaa", OA_NOTIFY);
  depOnce  = prefs.getUInt("dep", 0);
  depOnceT = prefs.getUChar("dept", 0);
  prefs.end();
  if (oledType > OLED_SSD1327) oledType = OLED_SH1106;
  if (dispMode > DISP_ALWAYS) dispMode = DISP_AUTO;
  if (ledLvl > 3) ledLvl = 1;
  if (otaAuto > OA_INSTALL) otaAuto = OA_NOTIFY;
  if (isnan(tOff) || tOff < -5 || tOff > 5) tOff = 0;
  if (lang >= L_N) lang = L_ES;
  if (wifiMode > WM_DEMAND) wifiMode = WM_ALWAYS; // valor imposible: el de por defecto
  char ok[20];
  if (!iidParse(String(iid), ok)) {               // primer arranque: código de instalación al azar (80 bits)
    String c;
    for (int i = 0; i < 16; i++) c += IID_ABC[esp_random() & 31];
    iidParse(c, iid);
    prefs.begin("webasto", false);
    prefs.putString("iid", iid);
    prefs.end();
  }
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
  if (nSch) { prefs.putBytes("sch", sch, sizeof(Sched) * nSch); prefs.putBytes("schx", schX, nSch); }
  else { prefs.remove("sch"); prefs.remove("schx"); }
  prefs.putBool("auto", autoOn);
  prefs.end();
}

// Programas en texto (los manda la web o la app): auto = "1"/"0"; lista = "en,días,inicio,duración[,opciones];..."
// opciones = schX (objetivo en °C en los bits 0–5, bit 7 = hora de salida); sin ellas, 0 (como en versiones anteriores).
// Se descartan las entradas mal formadas o fuera de rango; la duración se limita a MAX_MIN (MAX_SESSION con objetivo).
void applySched(const String& a, const String& L) {
  autoOn = a == "1";
  nSch = 0;
  int p = 0;
  while (p < (int)L.length() && nSch < MAX_SCHED) {
    int q = L.indexOf(';', p);
    if (q < 0) q = L.length();
    String it = L.substring(p, q);
    p = q + 1;
    int e, d, st, du, x = 0;
    if (sscanf(it.c_str(), "%d,%d,%d,%d,%d", &e, &d, &st, &du, &x) >= 4 && st >= 0 && st < 1440 && du > 0) {
      x &= SX_DEP | SX_TGT;
      int tg = x & SX_TGT;
      if (tg && (tg < TGT_MIN || tg > TGT_MAX)) x &= SX_DEP;   // objetivo fuera de rango: sin termostato
      sch[nSch].en = e ? 1 : 0;
      sch[nSch].days = d & 0x7F;                  // solo los 7 bits de los días
      sch[nSch].start = st;                       // minutos desde las 00:00
      sch[nSch].dur = min(du, (int)((x & SX_TGT) ? MAX_SESSION : MAX_MIN));
      schX[nSch] = x;
      depDone[nSch] = 0;
      nSch++;
    }
  }
  saveSched();
  addLog(trf(T_LOG_SCHED, nSch));
}

// Los programas en el mismo formato de texto, para la app ("1|1,31,420,30;0,96,600,15,148"); las opciones solo si hay
String schedText() {
  String s = autoOn ? "1|" : "0|";
  for (int i = 0; i < nSch; i++) {
    if (i) s += ";";
    s += sch[i].en; s += ","; s += sch[i].days; s += ","; s += sch[i].start; s += ","; s += sch[i].dur;
    if (schX[i]) { s += ","; s += schX[i]; }
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
  } else if (k == "oled") {                       // 0 = SH1106 (1,3"), 1 = SSD1306 (0,96"), 2 = SSD1327 (1,5", grises)
    int m = v.toInt();
    if (!v.length() || m < OLED_SH1106 || m > OLED_SSD1327) { err = trf(T_E_VALUE, k.c_str()); r = 0; }
    else if (m != oledType) { oledType = m; prefs.putUChar("oled", oledType); oledInit(); dispWake(); }
  } else if (k == "disp") {                       // pantalla: 0 apagada (solo con el botón), 1 automática, 2 siempre encendida
    int m = v.toInt();
    if (!v.length() || m < DISP_OFF || m > DISP_ALWAYS) { err = trf(T_E_VALUE, k.c_str()); r = 0; }
    else { dispMode = m; prefs.putUChar("disp", dispMode); dispWake(); }
  } else if (k == "led") {                        // brillo del LED: 0 apagado … 3 alto
    int m = v.toInt();
    if (!v.length() || m < 0 || m > 3) { err = trf(T_E_VALUE, k.c_str()); r = 0; }
    else { ledLvl = m; prefs.putUChar("led", ledLvl); }
  } else if (k == "toff") {                       // corrección del termómetro (°C)
    float f = v.toFloat();
    if (!v.length() || f < -5 || f > 5) { err = tr(T_E_TOFF); r = 0; }
    else { if (!isnan(cabT)) cabT += f - tOff; tOff = f; prefs.putFloat("toff", tOff); }
  } else if (k == "webuser") {                    // usuario de la web desde otra red
    if (v.length() < 1 || v.length() >= sizeof webUser) { err = tr(T_E_WEBUSER); r = 0; }
    else if (v != webUser) { strlcpy(webUser, v.c_str(), sizeof webUser); prefs.putString("wuser", webUser); }
  } else if (k == "webpass") {                    // su clave (vacía: se queda la que había)
    if (!v.length()) r = 1;
    else if (v.length() < 6 || v.length() >= sizeof webPass) { err = tr(T_E_WEBPASS); r = 0; }
    else if (v == WEB_DEFAULT) { err = tr(T_E_WEBDEF); r = 0; }
    else if (v != webPass) { strlcpy(webPass, v.c_str(), sizeof webPass); prefs.putString("wpass", webPass); }
  } else if (k == "iid") {                        // código de instalación (el de la placa anterior, para seguir sus estadísticas)
    char n[20];
    if (!iidParse(v, n)) { err = tr(T_E_IID); r = 0; }
    else if (strcmp(n, iid)) {
      strlcpy(iid, n, sizeof iid); prefs.putString("iid", iid);
      runAck = 0; prefs.putUInt("rack", 0);       // los encendidos guardados se mandan otra vez, con el código nuevo
      statsLast = 0;
      addLog(tr(T_LOG_IID));
    }
  } else if (k == "stats") {                      // enviar las estadísticas de esta placa: 1 / 0
    bool on = v == "1" || v == "true";
    if (on != statsOn) { statsOn = on; prefs.putBool("stats", statsOn); statsLast = 0; }
  } else if (k == "otaauto") {                    // actualizaciones automáticas: 0 no, 1 avisar, 2 instalar sola
    int m = v.toInt();
    if (!v.length() || m < OA_OFF || m > OA_INSTALL) { err = trf(T_E_VALUE, k.c_str()); r = 0; }
    else if (m != otaAuto) { otaAuto = m; prefs.putUChar("otaa", otaAuto); }
  } else if (k == "warm") {                       // aviso de agua caliente (°C; 0 = sin aviso)
    int w = v.toInt();
    if (!v.length() || (w != 0 && (w < 30 || w > 80))) { err = tr(T_E_WARM); r = 0; }
    else { warmC = w; prefs.putUChar("warm", warmC); }
  } else { err = trf(T_E_UNKNOWN_SET, k.c_str()); r = 0; }
  prefs.end();
  return r;
}

// Ajustes que admite el formulario de configuración de la web (en este orden)
const char* CFG_KEYS[] = {"lang", "name", "appass", "pin", "wifimode", "ssid", "pass", "tgtok", "tgchat", "minvolt",
                          "oled", "disp", "led", "toff", "warm", "otaauto",
                          "webuser", "webpass", "iid", "stats"};

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
  j += ",\"th\":1";                                             // sabe termostato y hora de salida (0.2.0+)
  j += ",\"otaauto\":"; j += (int)otaAuto;                      // actualizaciones automáticas (0.2.15+)
  j += ",\"webuser\":"; j += js(String(webUser));                // usuario de la web desde otra red (0.2.16+)
  j += ",\"webdef\":"; j += webDefault() ? "true" : "false";     // usuario y clave de fábrica
  j += ",\"iid\":";   j += js(String(iid));                      // código de instalación
  j += ",\"stats\":"; j += statsOn ? 1 : 0;                       // enviar las estadísticas de esta placa
  j += ",\"nruns\":"; j += runSeq; j += ",\"rack\":"; j += runAck;   // encendidos apuntados y ya enviados
  j += ",\"stok\":"; if (statsOkAt) j += (millis() - statsOkAt) / 1000; else j += "-1";   // s desde el último envío
  j += ",\"oled\":";   j += (int)oledType;
  j += ",\"disp\":";   j += (int)dispMode;
  j += ",\"led\":";    j += (int)ledLvl;
  j += ",\"toff\":";   j += String(tOff, 1);
  j += ",\"warm\":";   j += (int)warmC;
  j += ",\"sens\":";   j += js(String(snName()));               // termómetro detectado ("" = ninguno)
  j += ",\"scr\":";    j += oledOk ? "true" : "false";           // ¿hay pantalla?
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

// Redes Wi-Fi cercanas, para elegir la red con internet sin escribir su nombre. La búsqueda tarda unos segundos y va en
// segundo plano: mientras dura devuelve "" (y la primera llamada la lanza); al acabar, la lista en JSON
// [{"s":"nombre","r":-60,"e":1},…] con las 20 de más señal, sin repetir nombre ni las ocultas (e = lleva clave).
// Con la Wi-Fi apagada por ahorro, la enciende unos minutos (loop()) y la búsqueda empieza en la siguiente llamada.
String scanNets() {
  if (!wifiActive) {
    if ((int32_t)(wifiUntil - (millis() + 180000)) < 0) wifiUntil = millis() + 180000;
    return "";
  }
  int n = WiFi.scanComplete();
  if (n == WIFI_SCAN_RUNNING) return "";
  if (n < 0) { WiFi.scanNetworks(true); return ""; }    // sin búsqueda (o falló): se lanza
  int idx[64], m = 0;
  for (int i = 0; i < n && m < 64; i++) if (WiFi.SSID(i).length()) idx[m++] = i;
  for (int a = 1; a < m; a++)                            // de más a menos señal
    for (int b = a; b > 0 && WiFi.RSSI(idx[b]) > WiFi.RSSI(idx[b - 1]); b--) { int t = idx[b]; idx[b] = idx[b - 1]; idx[b - 1] = t; }
  String j = "[", seen = "\n";
  int c = 0;
  for (int k = 0; k < m && c < 20; k++) {
    String s = WiFi.SSID(idx[k]);
    if (seen.indexOf("\n" + s + "\n") >= 0) continue;   // la misma red en varios puntos de acceso: solo la más fuerte
    seen += s + "\n";
    if (c++) j += ",";
    j += "{\"s\":" + js(s) + ",\"r\":" + String(WiFi.RSSI(idx[k])) + ",\"e\":" + (WiFi.encryptionType(idx[k]) == WIFI_AUTH_OPEN ? "0" : "1") + "}";
  }
  WiFi.scanDelete();
  return j + "]";
}

// Resumen de arranque por la consola serie (y con la orden «info»): cómo conectarse y con qué claves.
// La consola solo la ve quien tiene la placa enchufada por USB (acceso físico, que ya permite reinstalarla), así que
// enseña también las claves cambiadas: es la forma de recuperarlas si se olvidan.
void printMotd() {
  const char* WM[] = {"siempre encendida", "solo mientras calienta", "solo a petición"};
  bool apDef = apDefault(), webDef = webDefault();
  Serial.println();
  Serial.println("================================================================");
  Serial.printf("  WTTC %s · control de la Webasto Thermo Top C · github.com/matatunos/wttc\n", FW_VERSION);
  Serial.println("================================================================");
  Serial.printf("  Bluetooth (app WTTC) .. nombre \"%s\" · PIN %06u\n", cfgName, (unsigned)blePin);
  Serial.printf("  Wi-Fi propia .......... red \"%s\" · clave \"%s\"%s\n", cfgName, cfgApPass, apDef ? "  <- DE FÁBRICA: cámbiala" : "");
  Serial.printf("                          web http://192.168.4.1 · %s\n", WM[wifiMode <= WM_DEMAND ? wifiMode : 0]);
  if (staSsid[0])
    Serial.printf("  Red con internet ...... \"%s\" · %s\n", staSsid,
                  WiFi.status() == WL_CONNECTED ? (String("conectada, http://") + WiFi.localIP().toString()).c_str() : "conectando…");
  else Serial.println("  Red con internet ...... sin configurar (Configuración → Red con internet)");
  Serial.printf("  Web desde esa red ..... http://%s.local · usuario \"%s\" · clave \"%s\"%s\n", HOSTNAME, webUser, webPass,
                webDef ? "  <- DE FÁBRICA: cámbialos" : "");
  Serial.printf("  Telegram .............. %s\n", tgToken[0] && tgChat[0] ? (String("avisos al chat ") + tgChat).c_str() : "sin configurar");
  Serial.printf("  Mis estadísticas ...... código %s · %s\n", iid, statsOn ? "se envían" : "no se envían");
  Serial.printf("                          https://wttc.favala.es/mi.php#%s\n", iid);
  Serial.printf("  Piezas opcionales ..... pantalla %s · termómetro %s\n", oledOk ? "sí" : "no", snName()[0] ? snName() : "no");
  Serial.printf("  Actualizaciones ....... %s\n", otaAuto == OA_OFF ? "no se buscan" : otaAuto == OA_NOTIFY ? "buscar y avisar" : "buscar e instalar sola");
  Serial.println("----------------------------------------------------------------");
  Serial.println("  Órdenes: on [min] [°C] | off | status | info | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset");
  Serial.println("================================================================");
}

// Enciende la red propia, se une a la red externa (si la hay) y arranca el servidor web y wttc.local
void wifiStart() {
  WiFi.setHostname(HOSTNAME);
  WiFi.mode(WIFI_AP_STA);                         // a la vez punto de acceso propio y cliente de otra red
  WiFi.softAP(cfgName, cfgApPass);
  // Portal cautivo: todos los nombres se resuelven a la placa. Android, iPhone y Windows, al conectarse, comprueban si
  // hay internet pidiendo una página suya; les llega la de la placa y la abren solos (ver onNotFound en setup())
  dns.setErrorReplyCode(DNSReplyCode::NoError);
  dnsOn = dns.start(53, "*", WiFi.softAPIP());
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
  if (dnsOn) { dns.stop(); dnsOn = false; }
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
  uint32_t nowMin = t / 60;
  // Salida suelta: se atiende aunque los programas estén desactivados; pasada la hora se borra
  if (depOnce) {
    depCheck(depOnce, depOnceT, depOnceDone, "app");
    if (nowMin >= depOnce) depSet(0, 0);
  }
  if (!autoOn) return;                            // programas apagados

  uint8_t wd = (tm.tm_wday + 6) % 7;             // día de la semana con 0 = lunes (tm_wday tiene 0 = domingo)
  uint16_t m = tm.tm_hour * 60 + tm.tm_min;      // minuto del día
  for (int i = 0; i < nSch; i++) {
    if (!sch[i].en) continue;
    uint8_t tgt = schX[i] & SX_TGT;
    if (schX[i] & SX_DEP) {
      // La hora es la de salida: se mira la de hoy y la de mañana (por si cae pasada la medianoche) y depCheck()
      // decide cuánto antes encender según la temperatura
      for (int k = 0; k < 2; k++) {
        int32_t diff = k * 1440 + sch[i].start - m;
        if ((sch[i].days >> ((wd + k) % 7) & 1) && diff > 0 && diff <= 60) depCheck(nowMin + diff, tgt, depDone[i], "programa");
      }
      continue;
    }
    if (heaterOn || thActive) continue;           // ya está calentando
    if ((sch[i].days & (1 << wd)) && sch[i].start == m) {
      // Antes de encender se mira la batería: con poca tensión, no se arranca (para poder arrancar el motor)
      if (!battOk()) return;
      if (tgt && !isnan(cabT)) startSession(sch[i].dur, tgt, "programa");   // con objetivo: termostato
      else startHeater(min(sch[i].dur, MAX_MIN), "programa");
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
  j += String(gasMonth, 2); j += ","; j += String(gasTotal, 2); j += "]";   // con 2 decimales, como el encendido: si no, el total redondeado podía salir menor
  j += ",\"op\":";   j += otaProg;                // actualización por internet: 0–100 %, -1 = ninguna
  j += ",\"nv\":";   j += js(String(otaAvail));   // versión nueva encontrada ("" = ninguna)
  j += ",\"up\":";   j += (uint32_t)(millis() / 1000);   // segundos encendida (la app nota así los reinicios)
  j += ",\"ct\":";   j += isnan(cabT) ? -999 : (int)lround(cabT * 10);   // °C × 10 de dentro (-999 = sin termómetro)
  j += ",\"ch\":";   j += isnan(cabH) ? -1 : (int)lround(cabH);          // humedad (%)
  j += ",\"tg\":";   j += thActive ? (int)thTarget : 0;                   // termostato: objetivo (0 = sin él)
  j += ",\"tu\":";   j += thActive ? (int32_t)(thUntil - millis()) / 1000 : 0;   // y segundos que le quedan
  j += ",\"dp\":";   j += depOnce * 60;            // salida suelta (segundos desde 1970; 0 = ninguna)
  j += ",\"dt\":";   j += (int)depOnceT;           // y su objetivo
  j += ",\"wa\":";   j += (heaterOn && warmSent) ? 1 : 0;   // ya avisó de «agua caliente» en este encendido
  j += ",\"note\":"; j += js(stopNote.substring(0, 150));   // recortado para que quepa
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
  if (k == "on") {                                 // on [minutos] [objetivo °C]: con objetivo, termostato
    int m = a.toInt(), b = a.indexOf(' ');
    String e = heatOn(m, b > 0 ? a.substring(b + 1).toInt() : 0, "app");
    return e.length() ? "on:err " + e : String("on:ok");
  }
  if (k == "off") { endSession(true); return stopHeater(tr(T_SRC_APP), true) ? String("off:ok") : String("off:ok ") + tr(T_OFF_NOCONF_SHORT); }
  if (k == "dep") {                                // dep HH:MM [objetivo °C] | dep off: salida suelta
    if (a == "off" || a == "0") { if (depOnce) addLog(tr(T_LOG_DEP_OFF)); depSet(0, 0); return "dep:ok"; }
    int h = -1, mi = -1, tg = 0;
    int nf = sscanf(a.c_str(), "%d:%d %d", &h, &mi, &tg);
    if (!timeValid() || nf < 2 || h < 0 || h > 23 || mi < 0 || mi > 59 || (tg && (tg < TGT_MIN || tg > TGT_MAX)))
      return String("dep:err ") + tr(T_E_DEP);
    depSet(depNext(h, mi), tg);
    return "dep:ok";
  }
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
  if (k == "iidtg") {                              // código de instalación por Telegram
    if (!tgToken[0] || !tgChat[0]) return "iidtg:err " + String(tr(T_E_NOTG));
    notifyMsg(trf(T_TG_IID, cfgName, iid), true);
    notifyMsg(String(iid), true);
    return "iidtg:ok";
  }
  // La app lleva las estadísticas al servidor si la placa no tiene internet: «report» da el informe (6 encendidos
  // como mucho) y «runsack N» apunta hasta cuál ha aceptado el servidor
  if (k == "report") return statsOn ? "report:" + statsJson(6, 500) : String("report:off");
  if (k == "runsack") { runAckSet(strtoul(a.c_str(), nullptr, 10)); return "runsack:ok"; }
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
  if (k == "scan") { String r = scanNets(); return r.length() ? "scan:" + r : String("scan:run"); }   // redes cercanas (repetir hasta tener la lista)
  if (k == "tgtest") {                             // aviso de prueba por Telegram
    if (!tgToken[0] || !tgChat[0]) return String("tgtest:err ") + tr(T_E_TG_CFG);
    if (!staSsid[0]) return String("tgtest:err ") + tr(T_E_TG_NET);
    notify(tr(T_TG_TEST));
    return "tgtest:ok";
  }
  if (k == "forget") { bleForgetAll(); return "forget:ok"; }
  // Gasoil a cero: todo, también el encendido en marcha (si no, este podría pasar del total)
  if (k == "gasreset") { gasCur = gasMonth = gasTotal = gasLast = 0; gasSave(); addLog(tr(T_LOG_GASRESET)); return "gasreset:ok"; }
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
  j += String(gasMonth, 2); j += ","; j += String(gasTotal, 2); j += "]";   // con 2 decimales, como el encendido: si no, el total redondeado podía salir menor
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
  j += ",\"nv\":";     j += js(String(otaAvail));                // versión nueva que ha visto la placa ("" = ninguna)
  j += ",\"lan\":";    j += viaAp() ? "false" : "true";          // entra desde otra red (con login)
  j += ",\"wdef\":";   j += (!viaAp() && webDefault()) ? "true" : "false";   // y con el usuario y la clave de fábrica
  j += ",\"ct\":";     j += isnan(cabT) ? String("null") : String(cabT, 1);   // dentro (°C); null = sin termómetro
  j += ",\"ch\":";     j += isnan(cabH) ? String("null") : String((int)lround(cabH));
  j += ",\"tgt\":";    j += thActive ? (int)thTarget : 0;                     // termostato: objetivo y segundos que quedan
  j += ",\"tun\":";    j += thActive ? (int32_t)(thUntil - now) / 1000 : 0;
  j += ",\"dep\":";    j += depOnce * 60;                                     // salida suelta (0 = ninguna) y su objetivo
  j += ",\"dept\":";   j += (int)depOnceT;
  j += ",\"wa\":";     j += (heaterOn && warmSent) ? "true" : "false";
  bool sta = WiFi.status() == WL_CONNECTED;       // ¿unida a la red con internet?
  j += ",\"sta\":";    j += sta ? "true" : "false";
  j += ",\"ssid\":";   j += js(sta ? WiFi.SSID() : String(""));
  j += ",\"ip\":";     j += js(sta ? WiFi.localIP().toString() : String(""));
  j += ",\"rssi\":";   j += sta ? WiFi.RSSI() : 0;   // intensidad de la señal (dBm)
  j += ",\"tx\":";     j += js(lastTx);
  j += ",\"rx\":";     j += js(lastRx);
  j += ",\"sch\":[";                              // programas como [activo, días, inicio, duración, opciones]
  for (int i = 0; i < nSch; i++) {
    if (i) j += ",";
    j += "["; j += sch[i].en; j += ","; j += sch[i].days; j += ",";
    j += sch[i].start; j += ","; j += sch[i].dur; j += ","; j += (int)schX[i]; j += "]";
  }
  j += "],\"log\":[";                             // registro, del más reciente al más antiguo
  for (int i = logN - 1; i >= 0; i--) { j += js(logBuf[i]); if (i) j += ","; }
  j += "]}";
  server.send(200, "application/json", j);
}

// ¿Han pedido la web con un nombre que no es el de la placa? (en la Wi-Fi propia, por el portal cautivo, cualquier
// nombre llega aquí). Los suyos: su IP en la red propia, su IP en la red de casa y wttc.local
bool foreignHost() {
  String h = server.hostHeader();
  int c = h.indexOf(':'); if (c >= 0) h = h.substring(0, c);
  if (h == WiFi.softAPIP().toString() || h.equalsIgnoreCase(String(HOSTNAME) + ".local") || h.equalsIgnoreCase(HOSTNAME)) return false;
  if (WiFi.status() == WL_CONNECTED && h == WiFi.localIP().toString()) return false;
  return h.length() > 0;
}
// A la página de la placa: con su dirección si venían con otro nombre (portal cautivo), o a «/» si ya era la suya
void toPortal() {
  server.sendHeader("Location", foreignHost() ? "http://" + WiFi.softAPIP().toString() + "/" : String("/"));
  server.send(302);
}

// ---------- Login desde otra red ----------
// ¿Ha llegado la petición por la Wi-Fi propia de la placa? (entonces ya se puso su clave: no hace falta login)
bool viaAp() { return server.client().localIP() == WiFi.softAPIP(); }
bool webDefault() { return !strcmp(webUser, WEB_DEFAULT) && !strcmp(webPass, WEB_DEFAULT); }
// Ficha de sesión de la cookie «wttcs» ("" si no hay)
String cookieTok() {
  String c = server.header("Cookie");
  int i = c.indexOf("wttcs=");
  return i < 0 ? String("") : c.substring(i + 6, i + 6 + 32);
}
bool hasSession() {
  String t = cookieTok();
  if (t.length() != 32) return false;
  for (auto& k : sess) if (k[0] && t == k) return true;
  return false;
}
bool loggedIn() { return viaAp() || hasSession(); }
// Para cada ruta de la API: por la Wi-Fi propia, siempre; desde otra red, solo con sesión (si no, 401 y la web pide entrar)
bool authed() {
  if (loggedIn()) return true;
  server.send(401, "text/plain", tr(T_W_LOGIN));
  return false;
}
// POST /api/login (user, pass): abre la sesión (cookie de 30 días; en la placa vale hasta que se reinicie)
void handleLogin() {
  if ((int32_t)(loginLockUntil - millis()) > 0) {
    server.send(429, "text/plain", trf(T_W_LOGINLOCK, (int)((loginLockUntil - millis()) / 60000) + 1)); return;
  }
  if (server.arg("user") != webUser || server.arg("pass") != webPass) {
    if (++loginFails >= 5) { loginFails = 0; loginLockUntil = millis() + 300000; }
    delay(400);                                   // frena las pruebas a ciegas
    server.send(403, "text/plain", tr(T_W_LOGINBAD));
    return;
  }
  loginFails = 0;
  char* t = sess[sessNext]; sessNext = (sessNext + 1) % 4;   // como mucho 4 sesiones: la más antigua se pierde
  for (int i = 0; i < 4; i++) snprintf(t + i * 8, 9, "%08lx", (unsigned long)esp_random());
  server.sendHeader("Set-Cookie", String("wttcs=") + t + "; Path=/; Max-Age=2592000; HttpOnly; SameSite=Strict");
  addLog(trf(T_LOG_LOGIN, server.client().remoteIP().toString().c_str()));
  server.send(200, "text/plain", "ok");
}
// POST /api/logout: cierra la sesión de este navegador
void handleLogout() {
  String t = cookieTok();
  if (t.length() == 32) for (auto& k : sess) if (t == k) k[0] = 0;
  server.sendHeader("Set-Cookie", "wttcs=; Path=/; Max-Age=0");
  server.send(200, "text/plain", "ok");
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
  for (int i = 0; i < 40 && WiFi.status() != WL_CONNECTED; i++) {
    wl_status_t s = WiFi.status();
    if (i >= 8 && (s == WL_NO_SSID_AVAIL || s == WL_CONNECT_FAILED)) break;   // no ve la red o la clave no vale: no esperar más
    vTaskDelay(pdMS_TO_TICKS(1000));
  }
  return WiFi.status() == WL_CONNECTED;
}

bool otaNetHeld = false;              // la búsqueda o descarga tiene pedida la pausa del portal (netBegin)
void otaNetDone(bool ok, const String& msg) { if (otaNetHeld) { netEnd(); otaNetHeld = false; } otaNetOk = ok; otaNetMsg = msg; otaProg = -1; otaNetEnd = true; }

// Para salir a internet desde una tarea: pide la pausa del portal cautivo (y espera a que loop() la haga, 0,5 s como
// mucho) y luego la devuelve. Cada netBegin() lleva su netEnd(); el contador no baja de cero por si acaso
void netBegin() { netUse++; for (int i = 0; i < 20 && dnsOn; i++) vTaskDelay(pdMS_TO_TICKS(25)); }
void netEnd() { int n = netUse.load(); while (n > 0 && !netUse.compare_exchange_weak(n, n - 1)) {} }

// Memoria libre, para los mensajes de error: « · memoria 123 KB (bloque 60 KB, PSRAM 8000 KB)»
String memInfo() {
  return " · memoria " + String(ESP.getFreeHeap() / 1024) + " KB (bloque " + String(ESP.getMaxAllocHeap() / 1024) + " KB, PSRAM "
         + String(ESP.getFreePsram() / 1024) + " KB)";
}

// Por qué no llega al servidor de actualizaciones, en datos (para el mensaje de error)
String netDiag(int code) {
  IPAddress ip;
  bool ok = WiFi.hostByName("wttc.favala.es", ip) == 1;
  return trf(T_OTA_NETERR, code, ok ? ip.toString().c_str() : "?", WiFi.dnsIP(0).toString().c_str(), WiFi.gatewayIP().toString().c_str())
         + memInfo();
}

// El trabajo de la tarea, en su propia función: al volver se destruyen la conexión segura (WiFiClientSecure, unos
// 40 KB), el cliente HTTP y los textos. Hasta la 0.2.12 la tarea se cerraba dentro de este mismo código, y cerrar una
// tarea así no llama a los destructores: cada búsqueda perdía esa memoria y, tras un par de ellas, ya no quedaba ni
// para arrancar la siguiente («Error al grabar la actualización» nada más pulsar)
void otaNetWork() {
  bool install = otaNetInstall;
  if (!otaNetWait()) { otaNetDone(false, trf(T_OTA_NOSTA, staSsid)); return; }
  WiFiClientSecure cli; cli.setInsecure();
  HTTPClient http; http.setConnectTimeout(10000); http.setTimeout(15000);
  http.setFollowRedirects(HTTPC_STRICT_FOLLOW_REDIRECTS);
  // 1) ¿qué versión hay?
  String body;
  netBegin(); otaNetHeld = true;
  int mcode = -100;                                       // -100: no se pudo ni empezar la petición
  if (http.begin(cli, OTA_MANIFEST)) { mcode = http.GET(); if (mcode == 200) body = http.getString(); http.end(); }
  String ver = jsonStr(body, "version"), url = jsonStr(body, OTA_KEY);
  otaNetNotes = jsonStr(body, "notas");                   // novedades de esa versión (del CHANGELOG)
  if (!ver.length() || !url.startsWith("https://") || ver.length() > 16) { String d = netDiag(mcode); otaNetDone(false, d); return; }
  strlcpy(otaNetVer, ver.c_str(), sizeof otaNetVer);
  if (verCmp(otaNetVer, FW_VERSION) <= 0) { otaNetDone(true, trf(T_OTA_LATEST, FW_VERSION)); return; }
  otaNetNew = true;
  if (!install) { otaNetDone(true, trf(T_OTA_NEW, otaNetVer, FW_VERSION) + (otaNetNotes.length() ? " " + otaNetNotes : String(""))); return; }
  // 2) descargar e instalar
  if (heaterOn) { otaNetDone(false, tr(T_OTA_HEAT)); return; }
  int code = -1;
  if (http.begin(cli, url)) code = http.GET();
  if (code != 200) { http.end(); otaNetDone(false, code == 404 ? trf(T_OTA_NOTYET, otaNetVer) : String(tr(T_OTA_NONET))); return; }
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
  otaNetDone(ota.done, ota.done ? trf(T_OTA_OK, otaNetVer) : String(ota.err ? ota.err : tr(T_OTA_WRITE)) + " (" + String(got) + "/" + String(total) + " B" + memInfo() + ")");
}

void otaNetTask(void*) {
  otaNetWork();
  vTaskDelete(nullptr);
}

// Lanza la búsqueda (install = false) o la actualización completa (install = true). Devuelve el error, o "" si arranca
// autoCheck = búsqueda diaria para avisar por Telegram: solo si la Wi-Fi ya está conectada (no la enciende).
String otaNetStart(bool install, bool autoCheck) {
  if (otaNetBusy) return tr(T_OTA_BUSY);
  if (install && heaterOn) return tr(T_OTA_HEAT);
  if (!staSsid[0]) return tr(T_OTA_NONET);
  // Wi-Fi encendida mientras dura (la búsqueda automática solo corre con ella ya encendida, pero instalar puede tardar)
  if ((!autoCheck || install) && (int32_t)(wifiUntil - (millis() + 120000)) < 0) wifiUntil = millis() + 120000;
  otaNetBusy = true; otaNetEnd = false; otaNetInstall = install; otaNetAuto = autoCheck; otaNetNew = false; otaNetNotes = "";
  if (!autoCheck) lastWebMsg = "";                    // la web espera a que aparezca el resultado nuevo
  if (xTaskCreatePinnedToCore(otaNetTask, "ota", 12288, nullptr, 1, nullptr, 0) != pdPASS) { otaNetBusy = false; return String(tr(T_OTA_WRITE)) + " (" + memInfo() + ")"; }
  return "";
}

// En loop(): cuando la tarea termina, se apunta, se responde a la app y, si se instaló, se reinicia
void otaNetPoll() {
  // Búsqueda automática (ajuste «otaauto»): con la red con internet ya conectada, a los 2 min de arrancar (ya confirmada
  // la versión que corre, ver otaConfirm) y luego una vez al día. Nunca calentando
  if (otaAuto != OA_OFF && !otaNetBusy && WiFi.status() == WL_CONNECTED && !heaterOn && !thActive
      && (otaAutoLast ? millis() - otaAutoLast > 86400000UL : millis() > 120000)) {
    otaAutoLast = millis();
    otaNetStart(false, true);
  }
  if (!otaNetEnd) return;
  otaNetEnd = false; otaNetBusy = false;
  if (otaNetAuto) {
    if (otaNetInstall) {                                  // instalación automática: reiniciar si salió bien
      if (otaNetOk) { addLog(trf(T_LOG_OTA, otaNetVer)); rebootPending = true; }
      else addLog(trf(T_LOG_OTA_FAIL, otaNetMsg.c_str()));
      lastWebMsg = otaNetMsg;
      return;
    }
    if (!otaNetOk) return;                                // sin red, servidor caído…: ya se probará mañana
    if (!otaNetNew) { otaAvail[0] = 0; return; }
    strlcpy(otaAvail, otaNetVer, sizeof otaAvail);        // la web y la app lo enseñan con un botón «Actualizar»
    // Aviso por Telegram, una sola vez por versión
    prefs.begin("webasto", false);
    String last = prefs.isKey("otanv") ? prefs.getString("otanv") : String("");
    if (last != otaNetVer) {
      notify(trf(T_TG_OTA_NEW, otaNetVer, otaNetNotes.length() ? otaNetNotes.c_str() : "-"));
      prefs.putString("otanv", otaNetVer);
    }
    prefs.end();
    // Instalar sola: nunca calentando ni con el termostato en marcha (si no, se intenta en la próxima búsqueda)
    if (otaAuto == OA_INSTALL && !heaterOn && !thActive) {
      addLog(trf(T_LOG_OTA_AUTO, otaNetVer));
      otaNetStart(true, true);
    }
    return;
  }
  if (otaNetOk && !otaNetInstall) {                       // búsqueda a mano: también se apunta lo encontrado
    if (otaNetNew) strlcpy(otaAvail, otaNetVer, sizeof otaAvail); else otaAvail[0] = 0;
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
// Desde otra red, lo mismo con el usuario y la clave de la web de fábrica (wttc / wttc)
bool setupDone() {
  if (apDefault()) { server.send(403, "text/plain", tr(T_W_SETUP)); return false; }
  if (!viaAp() && webDefault()) { server.send(403, "text/plain", tr(T_W_SETUPWEB)); return false; }
  return true;
}

// POST /api/on (min=minutos, tgt=objetivo °C opcional): encender
void handleOn() {
  String e = heatOn(server.arg("min").toInt(), server.arg("tgt").toInt(), "manual");
  server.send(e.length() ? 400 : 200, "text/plain", e.length() ? e : String("ok"));
}

// POST /api/dep (t=HH:MM u «off», tgt=objetivo opcional): salida suelta
void handleDep() {
  String t = server.arg("t"), g = server.arg("tgt");
  String r = runCmd("dep " + t + (g.length() && g != "0" ? " " + g : String("")));
  if (r.startsWith("dep:err ")) { server.send(400, "text/plain", r.substring(8)); return; }
  server.send(200, "text/plain", "ok");
}

// POST /api/off: apagar
void handleOff() {
  endSession(true);
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
  // Desde otra red con el usuario y la clave de la web de fábrica: solo se acepta si trae la clave nueva de la web
  if (!viaAp() && webDefault() && !server.arg("webpass").length()) { server.send(403, "text/plain", tr(T_W_SETUPWEB)); return; }
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
  if (l.startsWith("on")) { String a = l.substring(2); a.trim(); int b = a.indexOf(' ');
    String e = heatOn(a.toInt(), b > 0 ? a.substring(b + 1).toInt() : 0, "consola"); if (e.length()) Serial.println(e); }
  else if (l == "off") { endSession(true); stopHeater(tr(T_SRC_CONSOLE), true); }
  else if (l == "info" || l == "motd") printMotd();
  else if (l == "status") {
    readSensors();
    static const char* PH[] = {"apagada", "arrancando", "con llama", "pausa de regulación", "sin respuesta"};
    Serial.printf("Estado: %s | Temp %d C | %.2f V | llama %d | %d W\nTX: %s\nRX: %s\n",
                  PH[phase], tempC, volt, flame, power, lastTx.c_str(), lastRx.c_str());
    Serial.println(String("Gasoil estimado: encendido ") + litros(gasCur) + " | último " + litros(gasLast) +
                   " | mes " + litros(gasMonth) + " | total " + litros(gasTotal));
    if (snType != SN_NONE) Serial.printf("Dentro (%s): %.1f C, %.0f %% | termostato %s\n", snName(), cabT, cabH,
                                         thActive ? (String(thTarget) + " C").c_str() : "no");
    Serial.printf("Pantalla: %s\n", oledOk ? "sí" : "no");
    if (stopNote.length()) Serial.println(stopNote);
  }
  else if (l == "errores") Serial.println(errorsJson());
  else if (l == "cfg") Serial.println(cfgJson(true));
  else if (l.startsWith("set ") || l == "wifi" || l == "forget" || l == "reboot" || l == "gasreset") Serial.println(runCmd(c));
  else if (l.length()) Serial.println("Comandos: on [min] [°C] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset");
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
  // La página (desde la flash). Pedida con otro nombre (portal cautivo), se manda a la dirección de la placa: así la web
  // y sus órdenes van siempre con el mismo origen
  server.on("/", HTTP_GET, [] { if (foreignHost()) { toPortal(); return; } server.send_P(200, "text/html", INDEX_HTML); });
  server.on("/api/state", HTTP_GET, [] { if (authed()) handleState(); });
  // Las órdenes (POST) solo se aceptan desde la propia web de la placa (ver sameOrigin)
  server.on("/api/on", HTTP_POST, [] { if (authed() && sameOrigin() && setupDone()) handleOn(); });
  server.on("/api/off", HTTP_POST, [] { if (authed() && sameOrigin() && setupDone()) handleOff(); });
  server.on("/api/sched", HTTP_POST, [] { if (authed() && sameOrigin() && setupDone()) handleSched(); });
  server.on("/api/dep", HTTP_POST, [] { if (authed() && sameOrigin() && setupDone()) handleDep(); });
  server.on("/api/time", HTTP_POST, [] { if (authed() && sameOrigin()) handleTime(); });
  // Redes Wi-Fi cercanas: {"run":true} mientras busca; {"nets":[…]} al acabar (solo lectura: no hace falta el origen)
  server.on("/api/scan", HTTP_GET, [] { if (!authed()) return; String r = scanNets(); server.send(200, "application/json", r.length() ? "{\"nets\":" + r + "}" : String("{\"run\":true}")); });
  server.on("/api/errors", HTTP_GET, [] { if (authed()) server.send(200, "application/json", errorsJson()); });
  server.on("/api/cfg", HTTP_GET, [] { if (authed()) server.send(200, "application/json", cfgJson(!apDefault())); });
  server.on("/api/cfg", HTTP_POST, [] { if (authed() && sameOrigin()) handleCfgPost(); });
  server.on("/api/tgtest", HTTP_POST, [] { if (authed() && sameOrigin() && setupDone()) handleTgTest(); });
  server.on("/api/gasreset", HTTP_POST, [] { if (!authed() || !sameOrigin() || !setupDone()) return; runCmd("gasreset"); server.send(200, "text/plain", tr(T_W_GASRESET)); });
  server.on("/api/forget", HTTP_POST, [] { if (!authed() || !sameOrigin() || !setupDone()) return; int n = bleForgetAll(); server.send(200, "text/plain", trf(T_W_FORGOT, n)); });
  // Actualización sin cable: misma protección que las órdenes; el primer manejador responde, el segundo recibe el fichero
  server.on("/api/update", HTTP_POST, [] { if (authed() && sameOrigin() && setupDone()) handleUpdateDone(); },
            [] { if (originOk() && !apDefault() && loggedIn() && (viaAp() || !webDefault())) handleUpdateUpload(); });
  server.on("/api/otacheck", HTTP_POST, [] { if (!authed() || !sameOrigin() || !setupDone()) return; String e = otaNetStart(false, false);
    server.send(e.length() ? 400 : 202, "text/plain", e.length() ? e : String("")); });
  server.on("/api/otaupdate", HTTP_POST, [] { if (!authed() || !sameOrigin() || !setupDone()) return; String e = otaNetStart(true, false);
    server.send(e.length() ? 400 : 202, "text/plain", e.length() ? e : String("")); });
  // Código de instalación por Telegram (dos mensajes: explicación y el código solo, para copiarlo)
  server.on("/api/iidtg", HTTP_POST, [] { if (!authed() || !sameOrigin() || !setupDone()) return; String r = runCmd("iidtg");
    if (r.startsWith("iidtg:err ")) server.send(400, "text/plain", r.substring(10)); else server.send(200, "text/plain", tr(T_W_TG_SENDING)); });
  // Login desde otra red (desde la Wi-Fi propia no hace falta)
  server.on("/api/login", HTTP_POST, [] { if (sameOrigin()) handleLogin(); });
  server.on("/api/logout", HTTP_POST, [] { if (sameOrigin()) handleLogout(); });
  static const char* HDRS[] = {"Origin", "Cookie"};   // cabeceras que el servidor guarda para leerlas en los manejadores
  server.collectHeaders(HDRS, 2);
  // Cualquier otra ruta, incluidas las comprobaciones de internet de los móviles (/generate_204, /hotspot-detect.html,
  // /connecttest.txt…): a la página de la placa. Al no recibir lo que esperan, los móviles la abren como portal
  server.onNotFound(toPortal);

  bleInit();
  wifiUntil = millis() + WIFI_BOOT_MS;   // rescate: Wi-Fi encendida los primeros minutos en cualquier modo
  wifiStart();

  addLog(trf(T_LOG_BOOT, FW_VERSION));
  // Pantalla y termómetro (opcionales) en el bus I2C; botón BOOT para encender la pantalla
  Wire.begin(I2C_SDA, I2C_SCL, (uint32_t)400000);
  pinMode(BTN_PIN, INPUT_PULLUP);
  hwProbe();
  dispWake();
  // El PIN Bluetooth sale aquí: es la forma de conocerlo la primera vez (o en la web, Configuración)
  printMotd();                                    // cómo conectarse y con qué claves (también con la orden «info»)
}

// ============================================================================================================
// Bucle principal: atiende la web, la consola y la app, decide la Wi-Fi, mantiene viva la orden de calentar
// y revisa los programas. Nada aquí debe bloquear mucho: el mantenimiento tiene que salir cada 5 s.
// ============================================================================================================
void loop() {
  if (wifiActive) server.handleClient();          // peticiones de la web
  // Portal cautivo: en pausa mientras una tarea sale a internet; luego vuelve (si no arranca, se reintenta a los 5 s)
  static uint32_t dnsTry = 0;
  if (dnsOn && netUse > 0) { dns.stop(); dnsOn = false; }
  else if (!dnsOn && wifiActive && netUse == 0 && millis() - dnsTry > 5000) { dnsTry = millis(); dnsOn = dns.start(53, "*", WiFi.softAPIP()); }
  if (dnsOn) dns.processNextRequest();            // preguntas de nombres en la red propia
  serialCli();                                    // órdenes de la consola serie
  // En la consola, al unirse o perderse la red con internet: su dirección, para entrar a la web desde esa red
  static bool staWas = false;
  bool staNow = WiFi.status() == WL_CONNECTED;
  if (staNow != staWas) {
    staWas = staNow;
    if (staNow) Serial.printf("Red \"%s\" conectada: http://%s · http://%s.local\n", staSsid, WiFi.localIP().toString().c_str(), HOSTNAME);
    else if (staSsid[0]) Serial.printf("Red \"%s\" perdida (se reintenta sola)\n", staSsid);
  }

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
    if ((int32_t)(now - onUntil) >= 0) { runEnd = RE_TIME; stopHeater(tr(T_WHY_END), !thActive); }   // se acabó el tiempo (con termostato, sin aviso: sigue)
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
        endSession(true);
        runEnd = RE_NOCOMM;
        stopHeater(tr(T_WHY_NO_COMM), false);
        stopNote = hhmm() + tr(T_NOTE_LOST);
        notify(tr(T_TG_LOST));
      }
    }
  }
  // Sensores: cada SENSOR_MS si está encendida o hay alguien mirando (web abierta o app conectada en los últimos 15 s)
  if ((heaterOn || now - lastUi < 15000) && now - lastSensor >= SENSOR_MS) {
    if (readSensors() && heaterOn) { gasTick(); evalHeater(); battRunCheck(); warmCheck(); }
  }
  snTick();                                       // termómetro de dentro (si lo hay)
  thermoTick();                                   // «calentar hasta X °C»
  if ((!oledOk || snType == SN_NONE) && millis() - hwProbeAt > 30000) hwProbe();   // ¿se ha conectado algo?
  dispTick();                                     // pantalla
  ledTick();                                      // LED de estado
  btnTick();                                      // botón BOOT
  checkSchedule();                                // ¿toca encender por programa?
  otaConfirm();                                   // tras una actualización: confirmarla al minuto de funcionar
  otaNetPoll();                                   // ¿ha terminado una búsqueda o descarga por internet?
  statsPoll();                                    // estadísticas de esta placa (si están activadas)
}
