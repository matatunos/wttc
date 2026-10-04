# Cambios

Todas las versiones de WTTC (firmware del ESP32 y app Android van con el mismo número).
Formato: la versión más reciente arriba. Cada versión publicada tiene su Release en GitHub con el APK y el firmware.

**Mientras la versión empiece por 0, es una versión de prueba**: compila y funciona en el simulador, pero aún no se
ha comprobado con una Webasto real. La 1.0.0 llegará cuando alguien lo haya probado montado en un vehículo.

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
