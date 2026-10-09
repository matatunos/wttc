# Cambios

Todas las versiones de WTTC (firmware del ESP32 y app Android van con el mismo número).
Formato: la versión más reciente arriba. Cada versión publicada tiene su Release en GitHub con el APK y el firmware.

**Mientras la versión empiece por 0, es una versión de prueba**: compila y funciona en el simulador, pero aún no se
ha comprobado con una Webasto real. La 1.0.0 llegará cuando alguien lo haya probado montado en un vehículo.

## 0.2.16 — 2026-10-09

Versión de prueba (sin probar con una Webasto real).

- **Login en la web de la placa desde otra red.** Si la placa está unida a otra red (casa, camping, el móvil como punto de acceso…), antes cualquiera en esa red podía abrir su web y manejar la calefacción. Ahora pide **usuario y clave** (de fábrica, `wttc` / `wttc`), y con los de fábrica obliga a cambiarlos antes de dejar hacer nada. La sesión dura 30 días en ese navegador (hasta que la placa se reinicie); 5 intentos fallidos, 5 minutos de espera. Por la Wi-Fi propia de la placa no hace falta: ya se puso su clave. Se cambian en Configuración (web y app).
- **Mis estadísticas.** La placa apunta cada encendido: hora, duración, gasoil, temperatura dentro al empezar y al acabar, máxima del agua, batería mínima, quién la encendió, por qué se apagó y la avería, si la hubo (los 48 últimos, en su memoria).
  - **Código de instalación** al azar, creado una vez y para siempre (Configuración → Mis estadísticas). Al cambiar de placa se escribe en la nueva el de la vieja y se siguen las mismas estadísticas.
  - **«Enviar las estadísticas de esta placa»** (apagado de fábrica): la placa las envía si tiene internet; si no, la app al conectarse.
  - **https://wttc.favala.es/mi.php**: con el código, gráficas de horas y gasoil por mes, encendidos por día, a qué hora y qué día enciende, quién la enciende, por qué se apaga, temperatura dentro, agua y batería, duración, averías y los últimos encendidos; coste estimado con el precio del gasoil que elijas. Sin el código no se puede ver nada, y se pueden borrar los datos desde la misma página.
  - **«Enviar el código por Telegram»**: llega solo en un mensaje, para copiarlo.

## 0.2.15 — 2026-10-09

Versión de prueba (sin probar con una Webasto real).

- **Actualizaciones automáticas** (Configuración, en la web de la placa y en la app): «No buscar», «Buscar y avisar» (por defecto) o «Buscar e instalar sola». Con la placa unida a una red con internet, busca a los 2 minutos de arrancar y luego una vez al día. Si hay versión nueva, la web y la app lo avisan con un botón para actualizar (y Telegram, si está configurado); con «instalar sola» se instala sin preguntar, nunca calentando ni con el termostato en marcha. Antes solo buscaba con Telegram configurado, y solo avisaba por ahí.
- «Más tarde» aparca el aviso, no lo descarta: el de firmware nuevo vuelve a salir cuando la placa se reinicia (o al volver a abrir la app), y el de app nueva, la próxima vez que se abre la app.

## 0.2.14 — 2026-10-09

Versión de prueba (sin probar con una Webasto real).

- **La Wi-Fi de la placa viene siempre encendida por defecto** (antes, solo mientras calentaba y 10 minutos después): la web de la placa y `wttc.local` responden siempre. Gasta algo más con la furgo parada (≈ 40–60 mA, 1–1,5 Ah al día): si va a estar parada días, mejor «Solo mientras calienta» en Configuración. Las placas en las que ya se eligió un modo lo conservan.

## 0.2.13 — 2026-10-09

Versión de prueba (sin probar con una Webasto real). La 0.2.12 fue la primera actualización sin cable hecha en una placa real (subiendo el .ota desde su web).

- **«Buscar actualizaciones» ya no se queda sin memoria**: la tarea que busca y descarga no liberaba su conexión segura al terminar (unos 40 KB por búsqueda); tras un par de intentos la descarga fallaba con «Error al grabar la actualización» y luego ni empezaba.
- **Se usa la PSRAM** de la placa (8 MB en la N16R8, «OPI PSRAM» al compilar): más margen para las conexiones seguras con Bluetooth y Wi-Fi a la vez.
- Los errores al buscar o grabar dicen cuánto se ha descargado y cuánta memoria queda.
- Las actualizaciones se descargan de wttc.favala.es (copia exacta de la Release, firmada igual) en vez de directamente de GitHub, cuyas descargas redirigen a otro servidor. Esto ya vale también para las placas con versiones anteriores.
- En la web pública, la actualización (.ota) se puede descargar desde «Descargas», para subirla a mano desde la web de la placa.
- **App en pantallas anchas** (tablets, radios de coche, televisores como el Fire TV): dos columnas, con el estado y el botón a la izquierda y los programas y la configuración a la derecha, en vez de una columna estrecha en el centro. Se recoloca al girar la pantalla.

## 0.2.12 — 2026-10-07

Versión de prueba (sin probar con una Webasto real).

- **Buscar actualizaciones y Telegram con el portal cautivo**: desde la 0.2.9 la placa, aun unida a la red, decía «no tiene internet». Mientras sale a internet, ahora deja un momento en pausa el servidor de nombres del portal.
- Si aun así no llega al servidor, el error dice por qué: código, a qué dirección le lleva el nombre, qué servidor de nombres usa y cuál es su router.

## 0.2.11 — 2026-10-07

Versión de prueba (sin probar con una Webasto real).

### App
- **Avisa cuando hay una versión nueva de la app**: al abrirla (una vez al día como mucho) mira cuál es la última y, si ya está publicada, enseña sus novedades con un botón para descargar el WTTC.apk, que se instala encima conservando el emparejamiento y los ajustes. «Más tarde» no vuelve a avisar de esa versión. En «Ajustes de la app» se puede buscar a mano o desactivar el aviso. Solo lee la versión en wttc.favala.es; no envía nada.

## 0.2.10 — 2026-10-07

Versión de prueba (sin probar con una Webasto real).

- **La web de la placa funciona dentro de la ventanita del portal cautivo**: los avisos y las preguntas («¿Actualizar ahora?», «Guardado», errores) salen ahora en un recuadro de la propia página. Esa ventanita (Android, iPhone) no muestra los del navegador, y botones como «Buscar actualizaciones» parecían no hacer nada.
- **Barra de progreso** al actualizar, en la web de la placa (también al subir el fichero .ota) y en la app.
- Si algo falla en la página, sale un aviso con el error en vez de quedarse callada.
- «Buscar actualizaciones» avisa de que puede tardar hasta un minuto, y si la placa no consigue unirse a la red con internet lo dice enseguida y con el nombre de la red, en vez de esperar 40 segundos y decir «sin internet».

## 0.2.9 — 2026-10-07

Versión de prueba (sin probar con una Webasto real).

- **Portal cautivo en la Wi-Fi de la placa**: al conectarse a la red «WTTC», el móvil (Android, iPhone) o el ordenador abre solo la web de la placa, como en la Wi-Fi de un hotel; ya no hace falta escribir 192.168.4.1. Cualquier dirección que se escriba en esa red lleva también a la placa. En el iPhone, si se cierra esa ventana con «Cancelar» se desconecta de la red: hay que elegir «Usar sin internet».

## 0.2.8 — 2026-10-07

Versión de prueba (sin probar con una Webasto real). La primera grabada en una placa real: el instalador desde el navegador funciona.

- **Buscar redes Wi-Fi** al configurar la red con internet, en la web de la placa y en la app: la placa busca las cercanas y las enseña por orden de señal (con candado si llevan clave); al tocar una se rellena su nombre. Si la Wi-Fi estaba apagada por ahorro, la enciende unos minutos para buscar.

## 0.2.7 — 2026-10-06

Versión de prueba (sin probar con una Webasto real). **Cambio de licencia.**

- **WTTC pasa a ser software libre con licencia GPL v3 o posterior** (antes, MIT). Se puede usar, modificar y distribuir, pero quien distribuya una versión, modificada o no, tiene que hacerlo con la misma licencia y dar su código fuente: así el proyecto y sus derivados siguen siendo libres. Las versiones hasta la 0.2.6 siguen teniendo la licencia MIT con la que se publicaron.

## 0.2.6 — 2026-10-06

Versión de prueba (sin probar con una Webasto real).

- Termostato: el agua cuenta como caliente (mínimo de 5 min por encendido) desde 30 °C, no desde 50 °C. Con pausas largas (fuera templado) el agua bajaba de 50 °C entre ciclos, volvía el mínimo de 15 min y se pasaba del objetivo. En el simulador, «hasta 20 °C» durante 4 h queda entre 18 y 20 °C con cualquier temperatura de fuera (de −10 a 15 °C).

## 0.2.5 — 2026-10-06

Versión de prueba (sin probar con una Webasto real).

- **Termostato más fino**: el mínimo de 15 min por encendido queda solo para cuando arranca con el agua fría. Si el agua ya está caliente (50 °C o más, lo normal en los ciclos del termostato), el mínimo es de 5 min y apaga al llegar. Antes, con «hasta 25 °C», cada ciclo se pasaba hasta unos 27 °C; ahora se queda entre 23,5 y 25 °C y gasta menos.
- Simulador: a velocidad ×10 o ×60 la pantalla ya no se apaga y enciende sola (la web del simulador consulta en tiempo real, no en tiempo simulado).

## 0.2.4 — 2026-10-06

Versión de prueba (sin probar con una Webasto real).

- El gasoil del mes y el total se mandan con dos decimales, como el del encendido: redondeados a uno, el total podía salir menor que el encendido (0,24 l frente a 0,2 l).
- «Gasoil a cero» pone a cero también el encendido en marcha: antes, pulsado mientras calentaba, el gasoil de ese encendido podía salir mayor que el del mes y el total.

## 0.2.3 — 2026-10-06

Versión de prueba (sin probar con una Webasto real).

- **Pantalla SSD1327 de 1,5" (128×128, 16 grises)**: por I2C con los mismos cuatro cables, con letras suavizadas (DejaVu Sans, generadas con `herramientas/generar_fuentes.py`, sin librerías), barra del tiempo que queda y franja de aviso. Solo se mandan las filas que cambian. Se elige en Configuración → Tipo de pantalla; las de 128×64 siguen valiendo.
- Simulador: dibuja la SSD1327 en grises (la que lleva por defecto) igual que la placa.

## 0.2.2 — 2026-10-06

Versión de prueba (sin probar con una Webasto real).

- **El botón BOOT enciende la pantalla un minuto en cualquier modo**, también con la pantalla «apagada» en Configuración (que pasa a ser «solo con el botón»). Antes, en ese modo, no había forma de verla.
- Simulador: avisa de que la pantalla está desactivada o de que se está detectando (la placa busca piezas nuevas cada 30 s).

## 0.2.1 — 2026-10-06

Versión de prueba (sin probar con una Webasto real). Arreglo de la web de la placa.

### Web de la placa
- **La lista de programas volvía a salir**: desde la traducción a tres idiomas (0.1.x) no se dibujaba, y el estado salía como una sola letra («a» en vez de «Calentando»). La app no estaba afectada.
- La fila de «Salgo a las» se ve entera en el móvil.
- La compilación en GitHub prueba ahora la web de la placa en los tres idiomas antes de publicar nada.

### Simulador (wttc.favala.es)
- Pantalla OLED dibujada píxel a píxel como en la placa, LED de estado y botón BOOT.
- Pantalla y termómetro se pueden conectar y quitar, y la temperatura de dentro se puede poner a mano.

## 0.2.0 — 2026-10-06

Versión de prueba (sin probar con una Webasto real). **Pantalla, termómetro y termostato.** Solo ESP32-S3.

### Placas
- **Solo ESP32-S3** (DevKitC-1 N16R8). El ESP32 clásico deja de estar soportado: el firmware ya no compila para él y no hay actualización para esa placa. La 0.1.6 sigue disponible para quien lo tenga.

### Opcional: pantalla, termómetro y LED
- **Pantalla OLED I2C** de 128×64 (1,3" SH1106 o 0,96" SSD1306): temperatura de dentro, estado, agua, batería y lo siguiente que va a pasar. Se enciende con la calefacción, con el móvil conectado o con el botón BOOT, y se apaga al minuto (o siempre encendida, o nunca).
- **Termómetro de dentro** (SHT31 o AHT20, en el mismo bus que la pantalla): temperatura y humedad en la app, la web y la pantalla. Con corrección por si mide de más.
- **LED RGB de la placa** con el estado: naranja calentando, verde ya caliente, rojo si la Webasto no responde o se apagó sola, azul con la app conectada. Brillo ajustable o apagado.
- Todo se detecta solo; sin ello, la placa funciona igual que antes.

### Calefacción
- **Calentar hasta una temperatura** (solo con el termómetro; de 5 a 25 °C): se enciende, se apaga al llegar y vuelve a encender si se enfría, dentro de un tiempo máximo de hasta 4 h. Cada encendido dura como mínimo 15 min (las Webasto no llevan bien los arranques cortos). Si dentro no sube medio grado en 25 min y aún no llega (mucho frío fuera, termómetro mal puesto), se apaga y avisa en vez de gastar en balde.
- **Hora de salida**: «salgo a las 8:00» y la placa decide cuánto antes encender según el frío que haga (de 15 min a 1 h). Suelta o como tipo de programa, con o sin temperatura objetivo.
- **Batería vigilada mientras calienta**: si baja medio voltio por debajo de la mínima, se apaga para que luego arranque el motor.
- **Aviso de «ya está caliente»** por Telegram cuando el agua llega a la temperatura elegida.

### App
- Temperatura y humedad de dentro, «Hasta X °C», «Salgo a las…» y las opciones nuevas de los programas (hora de salida, temperatura objetivo). Lo que depende de una pieza opcional solo aparece si la placa la tiene.
- **Acceso rápido**: botón «Webasto» en los ajustes rápidos de Android y widget para la pantalla de inicio. Se conectan a la placa y encienden (con la duración elegida en la app) o apagan si ya está encendida.
- Notificación de «ya está caliente» con la app abierta.

## 0.1.6 — 2026-10-05

Versión de prueba (sin probar con una Webasto real). Primera que se puede instalar sin cable desde la 0.1.5.

### Placas
- **ESP32-S3** (DevKitC-1 N16R8, 16 MB) como placa de referencia; el **ESP32 DevKitC** sigue valiendo. Cada una tiene su propio programa y su propia actualización sin cable; la placa descarga la suya.
- Con los núcleos 3.x, el Bluetooth del ESP32-S3 usa la pila NimBLE: emparejar, contar y borrar emparejamientos funciona igual.

### Instalación
- **Instalar desde el navegador** (wttc.favala.es/instalar.php): con Chrome o Edge, pinchar la placa por USB y pulsar «Instalar». Sin Arduino IDE. Reinstalar así no borra la configuración ni el PIN.
- Las Releases llevan ya la actualización firmada de cada placa (`.ota`) y el paquete para instalar desde el navegador.

## 0.1.5 — 2026-10-05

Versión de prueba (sin probar con una Webasto real). **Actualizaciones sin cable.** Esta versión hay que grabarla una
vez por USB (trae una tabla de particiones nueva); a partir de ella, las siguientes se instalan desde la app o la web.

### Firmware
- **Buscar actualizaciones**: con la placa unida a una red con internet, busca la versión nueva, enseña sus novedades y, si se acepta, la descarga, la instala y se reinicia sola.
- También se puede subir el fichero `.ota` de una Release desde la web de la placa (Configuración → Actualizar firmware).
- Las actualizaciones van **firmadas**: solo se instalan las oficiales, nunca una versión más antigua ni mientras calienta. Si la nueva no aguanta un minuto funcionando, la placa vuelve sola a la anterior.
- Con Telegram configurado, avisa una vez al día como mucho cuando hay versión nueva, con sus novedades.
- Tabla de particiones propia (`partitions.csv`, Arduino la usa sola). La configuración y el PIN no se pierden al cambiarla.

### App Android
- **Buscar actualizaciones** en Configuración: consulta la última versión, enseña las novedades y la placa hace el resto.

## 0.1.4 — 2026-10-05

Versión de prueba (sin probar con una Webasto real). Mejoras de seguridad.

### Firmware
- **Primer uso**: mientras la Wi-Fi de la placa tenga la clave de fábrica (`calefaccion`, que es pública), su web solo deja elegir una nueva: no admite órdenes ni enseña el PIN de Bluetooth. No se puede volver a poner la de fábrica.
- La web de la placa rechaza órdenes enviadas desde otras webs abiertas en el móvil.
- Los avisos de Telegram comprueban el certificado del servidor: en una red ajena nadie puede hacerse pasar por Telegram y leer el token del bot.

### App Android
- Si la placa sigue con la clave de fábrica de la Wi-Fi, la app la pide al conectar.
- La duración de cada programa ya no sale cortada.

## 0.1.3 — 2026-10-05

Versión de prueba (sin probar con una Webasto real).

### Idiomas
- **Firmware**: habla español, inglés o alemán en el registro, los avisos de Telegram, los mensajes y su web. Se elige en Configuración; la app le pone el del móvil al conectar. La consola serie sigue en español.
- **App Android**: en inglés, español y alemán según el idioma del móvil (inglés si no es ninguno de los tres). En Android 13 o posterior se puede elegir en los ajustes de la app. Los números van con la coma o el punto de ese idioma.
- **Web**: wttc.favala.es también en inglés (`/en/`) y alemán (`/de/`), simulador incluido.

## 0.1.2 — 2026-10-04

Versión de prueba (sin probar con una Webasto real).

### App Android
- Bluetooth: una respuesta de la placa que llega tarde ya no da por terminada la orden siguiente.
- Bluetooth: buscar placas dos veces seguidas ya no corta la segunda búsqueda antes de tiempo.
- Estadísticas: ya no se pierden los contadores sumados mientras se envía el informe del día.

## 0.1.1 — 2026-10-04

Versión de prueba (sin probar con una Webasto real).

### App Android
- Modo demostración: ahora carga los programas y la configuración de ejemplo.
- Los números salen siempre con coma decimal, aunque el móvil esté en otro idioma.

## 0.1.0 — 2026-10-04

Primera versión pública como WTTC. **Versión de prueba: aún sin probar con una Webasto real.**

### Firmware
- **Bluetooth LE** como vía principal, con emparejamiento por PIN de 6 cifras (generado al azar en cada placa) y conexión cifrada.
- **Wi-Fi bajo demanda** para ahorrar batería: siempre encendida, solo mientras calienta o solo a petición. Tras arrancar, 10 minutos encendida en cualquier modo.
- **Estado real** de la Webasto: arrancando, calentando, en pausa (agua caliente) o sin respuesta. Detecta cuando se apaga sola y lee sus averías.
- **Gasoil estimado** a partir de la potencia que informa la Webasto: encendido actual, último, mes y total.
- **Avisos por Telegram** (bot propio, opcional) al encender, al apagar, si se apaga sola, si deja de responder y si un programa no arranca por batería baja.
- **Configuración** desde la web o la app: nombre, PIN, clave de la Wi-Fi, modo de la Wi-Fi, red con internet, Telegram y batería mínima.
- Duración máxima por encendido: 60 minutos.
- Procesador a 80 MHz y anuncio Bluetooth espaciado para gastar menos.
- La web de la placa va en `web.h` y muestra la fecha completa en la cabecera.
- Requiere el esquema de partición **Huge APP**.

### App Android
- Búsqueda y emparejamiento con la placa; se reconecta sola cuando está al alcance.
- Estado, encendido y apagado, programas, averías, registro, gasoil estimado y configuración de la placa.
- Avisos del sistema si la calefacción se apaga sola.
- Estadísticas anónimas opcionales (dos botones iguales en el primer inicio; se cambia en «Ajustes de la app»).
- **Modo demostración** («Probar sin placa»): una placa simulada dentro de la app, para probarla sin nada montado.

### Web y proyecto
- Web pública https://wttc.favala.es: simulador del firmware, esquema de montaje, guías de instalación (Arduino IDE y
  arduino-cli en Linux y Windows) y de uso (móvil Android, iPhone y radio de coche), descargas y versiones.
- Estadísticas públicas y agregadas en https://wttc.favala.es/estadisticas.php (sin IP).
- Compilación automática del firmware y de la app en GitHub Actions, Releases firmadas y capturas del README.
- Todo el código y el contenido están generados íntegramente con Claude (Anthropic) y van comentados en abundancia.

## 0.0.1 — 2026-09-30

Versión inicial (no publicada) (como `Webastot5.ino`): solo Wi-Fi y web, encendido y apagado, programas semanales, temperatura, tensión, llama, potencia, averías y consola serie.
