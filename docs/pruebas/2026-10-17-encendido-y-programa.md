# Encendido a mano y programa (2026-10-17, tarde)

- **WTTC** 0.3.14 y **Webasto falsa** 0.3.14, conexión directa, las dos a la vez en la
  [consola de la web](https://wttc.favala.es/consola.php) (vista conjunta por hora).
- Consola exportada (.md, con PIN, claves y código de instalación tapados):
  [2026-10-17-encendido-y-programa-consola.md](2026-10-17-encendido-y-programa-consola.md)

## Resultado: ✅ todo correcto

| Hora | Webasto falsa | WTTC |
|---|---|---|
| 15:42:53 | «encender 15 min: aceptado» → ventilador | «Encendida (manual, 15 min)» |
| 15:44:20 | ¡llama! | |
| 15:57:53 | se acabó el tiempo → postbarrido | «Apagada (fin de tiempo) · gasoil ≈ 0,14 l» |
| 15:59:53 | postbarrido → apagada | |
| 16:05:00 | «encender 30 min: aceptado» (en el segundo 0) | «Encendida (programa, 30 min)» |
| 16:06:27 | ¡llama! | |
| 16:20:14 | agua a 75 °C → carga parcial | |
| 16:35:00 | se acabó el tiempo → postbarrido | «Apagada (fin de tiempo) · gasoil ≈ 0,22 l» |

- **Mantenimiento y sensores**: 930 órdenes de WTTC, 930 respuestas. Ningún aviso real (los tres «avisos» del
  resumen eran líneas de la ayuda: corregido en la consola).
- **Programa normal**: enciende a su hora exacta. Un programa con «hora de salida» enciende ANTES (lo creado a las
  16:02 para las 16:02 pasa a mañana: es lo esperado).

## Lo que se vio además

- «Programas guardados» cuatro veces en 0,2 s (16:04:24): el botón «Guardar programas» seguía activo mientras
  guardaba. Corregido en la 0.3.15 (se bloquea hasta que la placa contesta).
