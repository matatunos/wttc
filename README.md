# WTTC — control por Bluetooth y Wi-Fi para la Webasto Thermo Top C

> 🤖 **Todo el código y el contenido de este repositorio están generados íntegramente con [Claude](https://claude.ai) (Anthropic)**:
> firmware del ESP32, app Android, servidor de estadísticas, web, workflows de compilación y documentación.
> El autor del proyecto ha dado las indicaciones; el código no está escrito a mano. Va comentado en abundancia a
> propósito, para que se pueda seguir y revisar.

Web con simulador, esquema y guías: **https://wttc.favala.es** · Versiones: [CHANGELOG.md](CHANGELOG.md) · Descargas: [Releases](../../releases)

Idiomas: firmware, app y web en español, inglés y alemán · *English: [wttc.favala.es/en](https://wttc.favala.es/en/)* ·
*Deutsch: [wttc.favala.es/de](https://wttc.favala.es/de/)*

Sustituye al temporizador original de la calefacción auxiliar **Webasto Thermo Top C** (la de agua que montan de
fábrica, por ejemplo, las VW T5) por un **ESP32** que habla su protocolo **W-Bus**. Se maneja desde una **app Android**
por Bluetooth o desde el navegador por Wi-Fi.

> Proyecto personal, sin relación con Webasto ni con Volkswagen. Úsalo bajo tu responsabilidad (ver el aviso más abajo).
> La Webasto conserva todas sus protecciones (sobrecalentamiento, llama, tensión, bloqueo por fallos).

## ⚠️ Aviso de responsabilidad
WTTC es un proyecto personal, hecho por afición y compartido por si a alguien le sirve: **no es un producto**, no tiene
servicio técnico ni homologación, y se ofrece *tal cual*, sin garantía de ningún tipo (licencia MIT). Mientras la versión
empiece por 0 es **de prueba**: aún no se ha probado montado con una Webasto real.

Una calefacción de gasoil quema combustible, genera humos y tira de la batería. Un montaje mal hecho o un fallo del
programa pueden acabar en una batería descargada, en averías de la calefacción o del vehículo, en la pérdida de su
garantía o incluso en un incendio.

- **Nunca la programes ni la enciendas a distancia con el vehículo en un garaje o lugar cerrado**: el monóxido de carbono
  no huele y mata.
- Si no te manejas con seguridad en instalaciones de 12 V, no lo montes tú. Mide cada cable antes de conectar, pon fusible
  y conserva siempre una forma de apagarla sin la app.

**Si lo montas, el responsable eres tú.** El autor no se hace cargo de ningún daño, avería o perjuicio, directo o indirecto,
que pueda derivarse de usar, montar o modificar WTTC. Webasto, Thermo Top y Volkswagen son marcas de sus propietarios; WTTC
no tiene relación con ellos. El texto completo está en https://wttc.favala.es/#responsabilidad

## Capturas
Hechas automáticamente: la web con el simulador y la app en un emulador Android con la placa simulada (sin hardware).

| Simulador: la web de la placa y una Webasto virtual |
|---|
| ![Simulador](docs/capturas/simulador.png) |

| Web de la placa (móvil) | Esquema de montaje |
|---|---|
| ![Web de la placa](docs/capturas/web-placa.png) | ![Esquema de conexiones](docs/capturas/esquema.png) |

| App Android (modo demostración) | Programas |
|---|---|
| ![App: estado](docs/capturas/app-estado.png) | ![App: programas](docs/capturas/app-programas.png) |

| App en una pantalla de radio de coche (1280×720) |
|---|
| ![App en una radio](docs/capturas/app-radio.png) |

| Estadísticas públicas |
|---|
| ![Estadísticas](docs/capturas/estadisticas.png) |

## Qué hace
- Encender y apagar (15–60 min) y hasta 8 programas semanales.
- **Estado real**: arrancando, calentando, en pausa (agua caliente) o sin respuesta. Si la Webasto se apaga por su
  cuenta, lo detecta y muestra sus códigos de avería.
- Temperatura del agua, tensión de batería, llama y potencia.
- No arranca un programa si la batería está por debajo del mínimo configurado.
- **Bluetooth LE** con emparejamiento por PIN (generado al azar en cada placa) como vía principal.
- **Wi-Fi** propia como segunda opción: siempre, solo mientras calienta o solo a petición (para gastar menos).
- Avisos opcionales por **Telegram** (bot propio) si el ESP32 llega a una red con internet.
- Todo se configura desde la app o la web, sin tocar el código.

## Hardware
| Pieza | Modelo usado | Para qué |
|---|---|---|
| Calefactor | Webasto Thermo Top C de fábrica (ref. VW 7H0 010 398 J), mandada por W-Bus | Lo que se controla; el ESP32 se enchufa en el conector del temporizador original |
| Microcontrolador | ESP32 DevKitC con ESP-WROOM-32 (38 pines, USB CP2102) | Bluetooth, Wi-Fi, programas y W-Bus por UART2 (IO16/IO17) |
| Transceptor | Módulo UART ↔ LIN/K-Line con **TJA1020** (o TJA1021, MCP2003, L9637D) | Adapta los 3,3 V del ESP32 al bus de un hilo a 12 V |
| Alimentación | Regulador **LM2596** ajustado a **5,0 V** | 5 V para el ESP32 desde el +12 V permanente |

Conexiones: +12 V permanente y masa del conector a la placa TJA1020 y al LM2596; W-Bus a la borna LIN; TX de la placa
a IO16, RX a IO17, SLP a 3V3. El cable de contacto (borne 15) no se usa. **Mide los cables con el polímetro**: los
colores cambian entre vehículos.

## Instalar el firmware
1. Arduino IDE 2 (o arduino-cli) con el núcleo **esp32 de Espressif** (2.x o 3.x). Sin librerías externas.
2. Placa **ESP32 Dev Module** y esquema de partición **Huge APP (3MB No OTA/1MB SPIFFS)**: con Bluetooth y Wi-Fi no
   cabe en la partición normal.
3. Abre `firmware/WTTC/WTTC.ino` (la carpeta entera: lleva también `web.h`) y súbelo. En el monitor serie (115200) aparece el **PIN Bluetooth**.

```sh
arduino-cli compile --fqbn esp32:esp32:esp32:PartitionScheme=huge_app firmware/WTTC
arduino-cli upload  --fqbn esp32:esp32:esp32:PartitionScheme=huge_app -p /dev/ttyUSB0 firmware/WTTC
```

Cada cambio se compila automáticamente en GitHub Actions con los núcleos 2.0.17 y 3.3.12.

## App Android
Se descarga como APK en [Releases](../../releases). Busca el ESP32 por Bluetooth, empareja con el PIN y se conecta
sola cuando está cerca. Sin nada montado, **«Probar sin placa»** abre un modo demostración con una placa simulada.

> **Versiones 0.x = de prueba**: compilan y funcionan en el simulador, pero aún no se han probado con una Webasto real. Funciona también en radios Android de coche si su Bluetooth es visible para las apps (compruébalo
antes con «nRF Connect»: si ve dispositivos BLE, la app funcionará).

## Estadísticas anónimas
La app pregunta al abrirla por primera vez si quieres enviar estadísticas anónimas (dos botones iguales, nada marcado de
antemano). Qué se envía, qué no y el código del servidor: [`server/`](server/). Resultados públicos en
https://wttc.favala.es/estadisticas.php

## Protocolo W-Bus
2400 baudios 8E1, un solo hilo (cada byte enviado vuelve como eco). Trama `F4 LL CMD DATOS… XOR`; respuesta `4F LL
CMD|0x80 DATOS… XOR`. Órdenes usadas: `0x21` encender (minutos), `0x44` mantener (cada 5 s), `0x10` apagar,
`0x50 05` sensores, `0x56 01` averías. Basado en la documentación del proyecto libwbus.

## Versiones
Firmware y app llevan el mismo número, el del fichero [`VERSION`](VERSION). Cada versión nueva tiene su sección en
[`CHANGELOG.md`](CHANGELOG.md) y, al subirla, GitHub Actions publica la Release con el APK y el firmware.

## Licencia
MIT. Código generado con Claude (Anthropic).
