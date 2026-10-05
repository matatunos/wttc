# Ideas para el futuro

Cuaderno de ideas de WTTC: cosas que podrían venir **después** de que lo básico funcione montado en una furgo de
verdad. Nada de esto está hecho ni decidido; son planes a grandes rasgos para no perderlos.
Contenido generado con Claude (Anthropic) a partir de las conversaciones con el autor del proyecto.

Prioridad actual: que la versión 0.x funcione con una Webasto real. Hasta entonces, esto solo se apunta.

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

## Otras ideas apuntadas

- **Contactar con quien ya lo ha hecho:** en
  [este hilo de t6forum](https://www.t6forum.com/threads/retrofit-telestart-t90-on-t5gp-thermotopc-recognized-but-does-not-start.46420/page-3)
  alguien enciende una Thermo Top C de T5 GP con un ESP mandando `0x21` y manteniendo la orden, que es lo que hace
  WTTC. Podría ser el primer probador.
- **Comprobar que la calefacción es de W-Bus antes de montar:** hay quien dice que algunas Thermo Top C antiguas no
  hablan W-Bus. La fase de «solo leer» (`status`) sirve para salir de dudas; avisarlo en la web.
- **Home Assistant:** publicar el estado y aceptar órdenes por MQTT cuando la placa tenga red, o un componente de
  ESPHome. Lo piden a menudo en sus foros y no hay nada hecho para la Thermo Top por W-Bus.
