# Jornada larga: encendido, actualización a 0.3.17 y programa (18/10/2026)

- **Duración**: 9 h 28 min de la Webasto falsa (09:49–19:18) y 6 h 44 min de WTTC en la
  [consola de la web](https://wttc.favala.es/consola.php), las dos placas a la vez.
- **Firmware**: WTTC 0.3.16 → **0.3.17** (actualizada desde la web de la placa a las 13:18) · Webasto falsa 0.3.16.
- **Consola completa**: [2026-10-18-jornada-larga-consola.md](2026-10-18-jornada-larga-consola.md) (código de
  instalación, PIN y claves tapados por la propia consola).

## Qué se probó

| Hora | Qué | Resultado |
|---|---|---|
| 09:49–10:20 | Encendido de 30 min: ventilador → bujía → llama → a tope → carga parcial (agua a 75 °C) → postbarrido | ✅ |
| 10:20 | Chequeo de la Webasto al acabar | ✅ todo correcto |
| 13:18 | Actualización a la 0.3.17 desde la web de la placa (descarga, firma, reinicio) | ✅ confirmada al minuto |
| 13:19 | Chequeo tras la actualización | ✅ todo correcto |
| 16:05–16:37 | Programa de 30 min (0.3.17): arranque completo, carga parcial a las 16:31, apagado a su hora y postbarrido de 2 min | ✅ |

- W-Bus: 3585 órdenes de WTTC y 3577 respuestas. Las 8 sin respuesta son las 4 preguntas de identificación del chequeo
  (`0x51 0B`, `0x51 05`, `0x51 0A`, `0x50 06`), dos veces: este modelo de la falsa no las conoce (una Webasto real sí
  puede). No es un fallo de WTTC.
- Mantenimiento (`0x44`) cada 5,0 s durante todo el encendido, y ninguno después de «apagar».
- WTTC: 0 avisos. A las 16:04 se cortó la Wi-Fi de casa 41 s (`E wifi:sta is connecting, cannot set config` del núcleo
  y «Red perdida»); volvió sola y el programa de las 16:05 arrancó a su hora.

## Cambios a raíz de la prueba

- **Webasto falsa**: en el postbarrido su registro seguía diciendo «último mantenimiento hace 3,3 s» (el contador deja de
  correr porque apagándose ya no hace falta mantenimiento), y parecía que WTTC lo seguía mandando. Ahora dice
  «apagándose, ya no hace falta mantenimiento». Sale con la próxima versión.
