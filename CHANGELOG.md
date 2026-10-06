# Cambios

Todas las versiones de WTTC (firmware del ESP32 y app Android van con el mismo número).
Formato: la versión más reciente arriba. Cada versión publicada tiene su Release en GitHub con el APK y el firmware.

**Mientras la versión empiece por 0, es una versión de prueba**: compila y funciona en el simulador, pero aún no se
ha comprobado con una Webasto real. La 1.0.0 llegará cuando alguien lo haya probado montado en un vehículo.

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
