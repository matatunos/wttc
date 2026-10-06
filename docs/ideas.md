# Ideas para el futuro

Cuaderno de ideas de WTTC: cosas que podrían venir **después** de que lo básico funcione montado en una furgo de
verdad. Nada de esto está hecho ni decidido; son planes a grandes rasgos para no perderlos.
Contenido generado con Claude (Anthropic) a partir de las conversaciones con el autor del proyecto.

Prioridad actual: que la versión 0.x funcione con una Webasto real. Hasta entonces, esto solo se apunta.

## Prioridades (cuando lo básico funcione)

| Prioridad | Idea |
|---|---|
| **Imprescindible** | [10. Actualizar el firmware sin cable](#10-actualizar-el-firmware-sin-cable-ota) |
| Favoritas | [4. Proteger la batería mientras calienta](#4-proteger-la-batería-mientras-calienta) · [5. Mandar por Telegram](#5-mandar-por-telegram-no-solo-recibir-avisos) · [12. Placa de circuito propia](#12-placa-de-circuito-propia) |
| Útiles | [6. Horas y arranques de la Webasto](#6-horas-de-funcionamiento-y-arranques-de-la-webasto) · [7. Aviso de «ya está caliente»](#7-aviso-de-ya-está-caliente) |
| Algún día | Las demás |

---

## 1. Mando a distancia por LoRa

**La idea:** encender, apagar y ver el estado de la calefacción **desde lejos**, sin cobertura móvil ni Wi-Fi:
desde casa con la furgo en la calle, desde la playa con la furgo en el parking, etc. El Bluetooth llega a 10–20 m;
LoRa puede llegar a cientos de metros en ciudad y a varios kilómetros en campo abierto.

Inspiración: [WBusLoraRemote](https://gitlab.com/jfk344/wbusloraremote) (dos ESP32 con LoRa como alternativa casera
al Telestart de Webasto). El autor tiene un par de placas **Heltec WiFi LoRa 32** por casa.

### Las dos partes

```
  [ Mando ]  ~~~ LoRa 868 MHz ~~~>  [ Furgo: WTTC + LoRa ]  --W-Bus-->  [ Webasto ]
  botones, pantalla, batería   <~~~ estado ~~~
```

**En la furgo**, dos caminos:

- **A. Una sola placa:** sustituir el ESP32 DevKitC por una Heltec (lleva ESP32/ESP32-S3, radio LoRa, Bluetooth y
  Wi-Fi). El W-Bus, la app y la web seguirían igual; LoRa sería una tercera vía de órdenes, como el Bluetooth.
  Ventaja: un único aparato. Inconveniente: cambia el hardware de referencia y hay que adaptar el firmware.
- **B. Un puente aparte:** dejar WTTC como está y añadir una Heltec que reciba por LoRa y se lo pase a WTTC
  (por un cable serie o por Bluetooth, como si fuera la app). Ventaja: no toca lo que ya funciona. Inconveniente:
  dos aparatos y otro enlace más que puede fallar.

En los dos casos, lo que llega por LoRa serían las mismas órdenes de texto que ya entiende el firmware
(`on 30`, `off`, `state`…), así que la lógica de la calefacción no cambia.

**El mando:** otra Heltec con batería, su pantallita OLED y dos o tres botones (encender / apagar / duración).
Al pulsar, despierta, manda la orden, espera la respuesta y enseña el estado (temperatura, si hay llama, cuánto
queda); luego vuelve a dormir. Con una caja impresa en 3D y una batería pequeña podría ir en el llavero o en casa.

### Lo que habría que resolver

- **Seguridad (lo primero):** cualquiera con una radio LoRa podría mandar órdenes. Haría falta una clave
  compartida entre mando y furgo (emparejarlos por USB o desde la app), mensajes cifrados y autenticados, y un
  contador para que no se pueda **grabar y repetir** una orden de encendido.
- **Consumo en la furgo:** la radio escuchando todo el tiempo gasta poco, pero la furgo puede estar semanas parada.
  Habría que escuchar a ratos (unos milisegundos cada pocos segundos) y que el mando mande un aviso largo para
  despertarla. Medirlo de verdad antes de decidir.
- **Alcance real:** la carrocería es una jaula de metal; la antena tendría que ir junto a un cristal o fuera.
  Primero, una prueba de alcance con las dos placas tal cual (ping de ida y vuelta) desde casa hasta donde se
  aparca.
- **Normativa:** en Europa, banda de 868 MHz con un límite de tiempo de emisión (1 % en la mayoría de canales).
  Para unas pocas órdenes al día sobra, pero conviene tenerlo en cuenta en el diseño.
- **Confirmación:** el mando debe saber si la orden llegó y si la Webasto arrancó de verdad (no basta con
  «enviado»). La respuesta con el estado resuelve las dos cosas.

### Otras formas de hacerlo (descartadas o para pensar)

- **Meshtastic:** firmware ya hecho para estas placas, con app de móvil y red en malla. Se podría mandar la orden
  como un mensaje de texto desde la app de Meshtastic. Interesante si ya hay red Meshtastic cerca; menos control
  sobre la seguridad.
- **LoRaWAN / The Things Network:** depende de que haya una pasarela cerca y las respuestas tardan; peor para un
  mando que tiene que contestar al momento.
- **GSM / 4G:** alcance ilimitado, pero necesita tarjeta SIM y gasta más. Es lo que hace el ThermoConnect comercial.

### Material aproximado

- 2 × Heltec WiFi LoRa 32 (V3 o la que haya por casa), con sus antenas de 868 MHz.
- Para el mando: batería LiPo pequeña, 2–3 pulsadores, caja.
- Para la furgo (camino A): la Heltec en lugar del DevKitC; el resto (TJA1020, LM2596) igual. Quizá una antena
  con cable para sacarla junto a un cristal.

### Pasos, si algún día se hace

1. WTTC funcionando en la furgo (lo de ahora).
2. Prueba de alcance con las dos placas: hasta dónde llega desde casa y desde dentro de la furgo.
3. Elegir camino A o B.
4. Enlace mínimo: el mando manda `state` y enseña la respuesta, sin cifrado todavía.
5. Seguridad: clave compartida, cifrado y contador contra repeticiones. **Antes de permitir encender.**
6. Encender y apagar desde el mando.
7. Consumo: escucha a ratos en la furgo y sueño profundo en el mando; medirlo.

---

## 2. Dormir con el contacto quitado (cable amarillo = positivo bajo llave)

**La idea:** si el cable amarillo del conector del mando es un **positivo bajo llave** (borne 15), usarlo para que
el ESP32 pase casi todo el tiempo en sueño profundo y se despierte solo cuando hace falta: al dar contacto o a la
hora del próximo programa. Con la furgo parada semanas, el consumo es lo que más preocupa.

**Primero, medir qué es el amarillo:** 0 V sin contacto y 12 V con contacto → borne 15 (sirve). Si sigue a las
luces, es la iluminación del mando (borne 58) y no vale.

### Cómo funcionaría

- **Contacto quitado y sin calentar:** el ESP32 duerme. Se despierta por **temporizador** (próximo programa) o por
  el **amarillo** al dar contacto.
- **Con contacto:** despierto y todo como ahora (app, web, Telegram). Además, la placa sabría si el motor está en
  marcha.
- **Calentando:** despierto siempre, porque la Webasto necesita el mantenimiento cada 5 s.
- El amarillo **no puede ir directo** al ESP32 (3,3 V y picos de tensión del coche): a través de un optoacoplador
  (tipo PC817) o divisor con zener y protección, a un pin de los que despiertan al ESP32 (los de RTC).

### Lo que habría que resolver

- **El hardware manda en el ahorro.** El chip ESP32 dormido gasta ~10 µA, pero la placa DevKitC (regulador, chip
  USB, LED) sigue en varios mA y el LM2596 también tiene su consumo en reposo. Con el montaje actual se pasaría de
  ~15–25 mA a ~10–15 mA. Para bajar de 1 mA: regulador de bajo consumo en reposo y placa ESP32 de bajo consumo.
- **El reloj.** Dormido, el ESP32 cuenta el tiempo con un oscilador interno que se desvía minutos al día: en
  semanas, los programas saldrían corridos. Y hoy, sin pila, la placa pierde la hora si se corta la corriente.
  Un **módulo RTC DS3231** (con pila y salida de alarma para despertar al ESP32) arregla las dos cosas; valdría la
  pena incluso sin el amarillo.
- **Lo que se pierde dormida:** Bluetooth y Wi-Fi. La app no podría encenderla al momento desde fuera de la furgo;
  solo los programas. Por eso, como **modo «ahorro máximo»** elegible, no como único comportamiento. Variante
  intermedia: despertar un instante cada pocos segundos para anunciarse por Bluetooth (la app tardaría algo más en
  conectar).
- **LoRa (idea 1):** si algún día se añade, la radio también tendría que escuchar a ratos para no anular el ahorro.

### Material aproximado

- Optoacoplador PC817 + resistencias (o divisor + zener + TVS) para el amarillo.
- Módulo RTC DS3231 con pila (I²C + pin de alarma).
- Para el ahorro de verdad: regulador reductor de bajo consumo en reposo en lugar del LM2596 y placa ESP32 de bajo
  consumo en lugar del DevKitC.

### Pasos, si algún día se hace

1. Medir el amarillo con el polímetro.
2. Medir el consumo real del montaje actual (polímetro en serie), para saber de qué se parte.
3. Añadir el DS3231: hora fiable aunque se corte la corriente (útil por sí solo).
4. Entrada del amarillo con optoacoplador; el firmware solo la lee (¿hay contacto?).
5. Modo «ahorro máximo»: sueño profundo con despertar por alarma del RTC o por el amarillo.
6. Si el ahorro se queda corto: cambiar regulador y placa.

---

## 3. Encender por «hora de salida»

**✅ Hecho en la 0.2.0.**

**La idea:** en lugar de «encender a las 7:30 durante 30 min», decir «quiero salir a las 8:00». La placa decide
cuándo arrancar según lo que ha tardado otras veces en calentar el agua y la temperatura de partida (el agua del
motor, y la del habitáculo si hay sensor, idea 11). Es como lo hacen los temporizadores oficiales de Webasto.

**A tener en cuenta:** necesita guardar un histórico corto de encendidos (temperatura inicial → minutos hasta
llegar a, por ejemplo, 50 °C) y un margen de seguridad. Los primeros días, sin histórico, usar una tabla fija.
El programa sigue teniendo una duración máxima (60 min).

---

## 4. Proteger la batería mientras calienta

**✅ Hecho en la 0.2.0.**

**La idea:** hoy la batería mínima solo se mira **al arrancar un programa**. Vigilarla también durante todo el
encendido y apagar (con aviso por la app y Telegram) si cae por debajo del límite durante un rato seguido.
Evita quedarse sin poder arrancar el motor.

**A tener en cuenta:** la bujía y la bomba tiran mucho al arrancar y la tensión baja unos segundos: hay que
mirar una media de, por ejemplo, 1–2 minutos, no lecturas sueltas, y no contar el arranque. La tensión ya la da
la propia Webasto por W-Bus. Cambio pequeño en el firmware.

---

## 5. Mandar por Telegram, no solo recibir avisos

**La idea:** si la furgo tiene internet (router 4G o el punto de acceso de un móvil que se queda dentro), encender
y apagar desde cualquier sitio con mensajes al bot: `/encender 30`, `/apagar`, `/estado`. Sin hardware extra; es
lo que hace el ThermoConnect de pago.

**A tener en cuenta:**
- Solo obedecer al **chat ID configurado**; cualquier otro mensaje se ignora.
- La placa tiene que preguntar a Telegram cada poco si hay mensajes nuevos: eso exige la Wi-Fi encendida con red
  (más consumo) y choca con el modo «ahorro máximo» de la idea 2. Quizá solo en el modo «siempre encendida».
- Confirmar cada orden con la respuesta del estado real (si arrancó o no).

---

## 6. Horas de funcionamiento y arranques de la Webasto

**La idea:** leer por W-Bus los contadores internos de la Webasto (horas de funcionamiento, número de arranques)
y enseñarlos en la app y la web. Sirven para saber cuándo toca revisión y como dato de diagnóstico.

**A tener en cuenta:** según libwbus están en los registros de información de funcionamiento (orden 0x50); hay
que comprobar en una Thermo Top C real qué registros contesta. Si se añaden a las **estadísticas anónimas**
(horas y arranques acumulados), darían una idea del uso real; habría que contarlo en el texto del permiso.

---

## 7. Aviso de «ya está caliente»

**✅ Hecho en la 0.2.0.**

**La idea:** una notificación (app y Telegram) cuando el agua del motor llega a una temperatura, por ejemplo
50 °C, para saber que ya se puede salir o entrar.

**A tener en cuenta:** por Telegram solo llega si la placa tiene internet en ese momento; en la app, solo si
está conectada por Bluetooth. Un aviso por encendido, no uno cada vez que la temperatura sube y baja.

---

## 8. Calentar antes de que suene el despertador

**La idea:** Android sabe a qué hora suena la próxima alarma del móvil. La app podría programar el encendido
para 30 minutos antes, sin tener que crear un programa.

**A tener en cuenta:** el móvil tiene que estar al alcance del Bluetooth un rato antes para mandarle la orden a la
placa, o mandarle un programa puntual la noche anterior (mejor). Que sea opcional y se vea claro.

---

## 9. Accesos rápidos en Android

**✅ Hecho en la 0.2.0.**

**La idea:** un widget en la pantalla de inicio y un botón en los ajustes rápidos («Webasto 30 min») para encender
con un toque, sin abrir la app.

**A tener en cuenta:** tiene que conectar por Bluetooth en segundo plano y confirmar si la orden llegó.

---

## 10. Actualizar el firmware sin cable (OTA)

**✅ Hecho en la 0.1.5 (firmadas, con vuelta atrás y búsqueda desde la app y la web).**

**La idea:** poder actualizar la placa sin desmontarla ni llevar un PC: desde la web de la placa (subir el fichero)
o desde la app por Bluetooth, con la versión de las Releases de GitHub. **Fundamental** para que la gente actualice
de verdad (correcciones de seguridad incluidas).

**A tener en cuenta:**
- **Lo primero: la memoria.** El esquema de particiones actual (*Huge APP, No OTA*) no deja sitio para una
  segunda copia del programa, que es lo que necesita una actualización sin cable. Habría que pasar a uno con dos
  huecos y comprobar que el firmware (Bluetooth + Wi-Fi) cabe en cada uno. Quien ya la tenga montada tendría que
  cambiarlo **una última vez por USB**.
- **Seguridad:** que solo acepte firmware **firmado** por el proyecto (si no, quien entre en la Wi-Fi podría
  meterle cualquier cosa) y que vuelva a la versión anterior si la nueva no arranca bien.
- **Nunca mientras calienta.**

---

## 11. Sensor de temperatura del habitáculo

**✅ Hecho en la 0.2.0 (con SHT31 o AHT20 en vez del DS18B20, y pantalla).**

**La idea:** un sensor DS18B20 (menos de 2 €, un cable) para ver la temperatura dentro de la furgo en la app y la
web, y afinar la «hora de salida» (idea 3).

**A tener en cuenta:** la Thermo Top C calienta el agua del motor; el aire de dentro lo mueve el ventilador del
coche. El sensor informa, no controla. Ponerlo lejos de las salidas de aire.

---

## 12. Placa de circuito propia

**La idea:** diseñar una placa (PCB) con todo integrado (ESP32, transceptor W-Bus, regulador y protecciones) que se
enchufe directamente en el conector del temporizador, sin cables dupont ni empalmes. El montaje pasaría de
«electrónica» a «enchufar»: es lo que más ayudaría a que otros lo usen.

**A tener en cuenta:**
- El **conector** del temporizador ya está identificado: TE (AMP) **AMPMODU** de 4 vías, 2×2, paso de 2,54 mm,
  con clip («Lock-Clip»); carcasa TE 965082-1 (Webasto 67769A) y contactos hembra TE 87191-1 (Webasto 24990A).
  La placa llevaría la hembra, o un pin header 2×2 de 2,54 mm con su carcasa.
- Aprovechar para meter lo de las ideas 2 y 11: regulador de bajo consumo en reposo, entrada del contacto con
  optoacoplador, reloj DS3231 con pila y conector para el sensor.
- Protecciones de automoción (fusible, picos de tensión, polaridad inversa).
- Hacerla abierta (KiCad) y fabricable en servicios tipo JLCPCB.
- Esperar a tener el montaje con módulos **probado en una furgo**: la placa sería la versión «definitiva».

---

## 13. Más idiomas

**La idea:** francés, italiano, neerlandés… Con lo que ya está montado (tabla del firmware, diccionario de su web,
`strings.xml` de la app y textos de la web pública), es sobre todo traducir.

**A tener en cuenta:** cada idioma nuevo hay que mantenerlo en cuatro sitios a la vez. Mejor cuando los textos
estén estables.

---

## Otras ideas apuntadas

- **Contactar con quien ya lo ha hecho:** en
  [este hilo de t6forum](https://www.t6forum.com/threads/retrofit-telestart-t90-on-t5gp-thermotopc-recognized-but-does-not-start.46420/page-3)
  alguien enciende una Thermo Top C de T5 GP con un ESP mandando `0x21` y manteniendo la orden, que es lo que hace
  WTTC. Podría ser el primer probador.
- ✅ *(hecho en la 0.2.0)* **Usar el LED RGB de la placa** (WS2812 en la ESP32-S3 N16R8, ver
  [docs/hardware/esp32-s3-n16r8](hardware/esp32-s3-n16r8/README.md)): ver el estado de un vistazo sin el móvil. Por
  ejemplo: azul tenue esperando, verde respirando con la Webasto encendida, rojo parpadeando si hay avería o no
  contesta el W-Bus, y un destello al conectar el móvil. Con brillo bajo y apagable desde la configuración (dentro de
  la furgo de noche molesta). Poco código; la librería del núcleo de ESP32 ya trae `neopixelWrite()`.
- **Home Assistant:** publicar el estado y aceptar órdenes por MQTT cuando la placa tenga red, o un componente de
  ESPHome. Lo piden a menudo en sus foros y no hay nada hecho para la Thermo Top por W-Bus.
