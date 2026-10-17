# Encendido de 15 minutos (2026-10-17)

- **WTTC**: ESP32-S3 N16R8, firmware 0.3.14, encendido a mano desde la web de la placa.
- **Webasto falsa**: ESP32-S3, firmware 0.3.14, conexión directa (A: tres cables, eco en la falsa), registro nivel 1.
- **Consola de la falsa**: [2026-10-17-encendido-15-min-webasto-falsa.log](2026-10-17-encendido-15-min-webasto-falsa.log)
  (capturada con la consola del instalador web, de 14:25 a 15:00).

## Resultado: ✅ todo correcto

| Hora | Webasto falsa | WTTC (registro de la placa) |
|---|---|---|
| 14:26:13 | «encender 15 min: aceptado» → ventilador | «Encendida (manual, 15 min)» |
| 14:26:25 | ventilador → bujía (batería a 12,09 V por la bujía) | |
| 14:27:10 | bujía → encendido (bomba de gasoil) | |
| 14:27:40 | encendido → estabilizando (llama) | |
| 14:28:40 | estabilizando → a tope (5000 W) | |
| 14:41:13 | «se acabó el tiempo pedido» → postbarrido | «Apagada (fin de tiempo) · gasoil ≈ 0,14 l» |
| 14:41:14 | contesta al chequeo (sensores y averías) | «Chequeo de la Webasto: todo correcto ✅» |
| 14:43:13 | postbarrido → apagada (suelta la orden) | |

- **Mantenimiento (0x44)**: 179 envíos, uno cada 5,0 s (entre 5,0 y 5,4 s); las 179 respuestas, «sigo con la orden».
  Ningún aviso de la falsa, ninguna trama mala.
- **Agua**: de 5 °C a 49 °C a ritmo constante (unos 3,5 °C por minuto a 5000 W, con 5 °C fuera); al apagarse baja
  despacio (42 °C a las 15:00).
- **Batería**: 12,60 V en reposo, 12,09 V con la bujía, 12,39 V con llama.
- **Gasoil estimado**: 0,14 l en 15 min (una Thermo Top C a tope gasta unos 0,6 l/h).
- **Estadísticas de la placa**: 900 s, 136 ml, batería mínima 12,1 V, motivo «se acabó el tiempo».

## Lo que se vio además

- **Corte de la Webasto con la calefacción encendida** (14:24, sin querer: se reinstaló la falsa durante un encendido
  de 30 min): WTTC avisó «la Webasto no responde al mantenimiento», luego «vuelve a responder» y «Apagada (ya no tiene
  la orden de calentar)», y leyó las averías (ninguna). Es lo que tiene que hacer si a la Webasto le falla la corriente.
- **Chequeo**: la falsa no contesta a la identificación (0x51 0B, 05, 0A) ni a los contadores (0x50 06), así que el
  chequeo sale bien pero sin modelo, fecha ni horas de uso. Pendiente: que la falsa las conteste.
- **Wi-Fi de WTTC**: −83 dBm, justa. En la furgo, la placa cerca de una ventana o con antena externa.
