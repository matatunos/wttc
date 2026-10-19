# Pruebas en la mesa

Pruebas de WTTC con la [Webasto falsa](../../herramientas/webasto_falsa/) (un segundo ESP32 que hace de Thermo Top C en
el W-Bus). Cada prueba tiene su resumen y la consola completa de la Webasto falsa (`.log`). Del registro de WTTC solo se
copia lo que hace falta: el código de instalación de la placa no se publica (con él se ven sus estadísticas).

| Fecha | Prueba | Firmware | Resultado |
|---|---|---|---|
| 2026-10-17 | [Encendido de 15 minutos](2026-10-17-encendido-15-min.md) | WTTC 0.3.14 · Webasto falsa 0.3.14 | ✅ |
| 2026-10-17 | [Encendido a mano y programa](2026-10-17-encendido-y-programa.md) | WTTC 0.3.14 · Webasto falsa 0.3.14 | ✅ |
| 2026-10-18 | [Jornada larga: encendido, actualización a 0.3.17 y programa](2026-10-18-jornada-larga.md) | WTTC 0.3.16 → 0.3.17 · Webasto falsa 0.3.16 | ✅ |
| 2026-10-19 | [25 horas con la 0.3.18: encendido, programas y «salgo a las…»](2026-10-19-noche-0318.md) | WTTC 0.3.18 · Webasto falsa 0.3.16 | ✅ |
