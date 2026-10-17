# Consola serie · 17/10/2026, 4:36:18 p.m.

- **WTTC**: 34 líneas, 115200 velocidad
- **Webasto falsa**: 2097 líneas, 115200 velocidad
- AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36

## Resumen

### WTTC

- Duración: 52 min 44 s
- avisos: 1

### Webasto falsa

- Duración: 53 min 49 s
- órdenes de WTTC: 930 · respuestas: 930 · avisos: 3
- Cambios de estado: `apagada → ventilador (empieza el arranque)` · `ventilador → bujía (ventilador listo: calienta la bujía)` · `bujía → encendido (bujía caliente: bomba de gasoil en marcha)` · `encendido → estabilizando (¡llama!)` · `estabilizando → a tope (llama estable)` · `a tope → postbarrido (se acabó el tiempo pedido)` · `postbarrido → apagada (fin del postbarrido; suelta la orden)` · `apagada → ventilador (empieza el arranque)` · `ventilador → bujía (ventilador listo: calienta la bujía)` · `bujía → encendido (bujía caliente: bomba de gasoil en marcha)` · `encendido → estabilizando (¡llama!)` · `estabilizando → a tope (llama estable)` · `a tope → carga parcial (agua a 75 °C: baja la potencia)` · `carga parcial → postbarrido (se acabó el tiempo pedido)`

## Avisos y errores

**WTTC**

- `  Órdenes: on [min] [°C] | off | status | info | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset`

**Webasto falsa**

- `  fallo gasoil      no prende: avería 02 cada arranque; al tercero seguido, bloqueada (07)`
- `  fallo corriente   sin corriente (fusible): no contesta a nada`
- `  fallo mudo        el W-Bus no contesta (cable suelto)`

## Las dos placas por hora

```text
15:42:16.387 [WTTC] » Preguntando quién es («info»)…
15:42:16.401 [WTTC] 
15:42:16.406 [WTTC] ================================================================
15:42:16.412 [WTTC]   WTTC 0.3.14 · control de la Webasto Thermo Top C · github.com/matatunos/wttc
15:42:16.417 [WTTC] ================================================================
15:42:16.423 [WTTC]   Bluetooth (app WTTC) .. nombre "WTTC" · PIN ······
15:42:16.429 [WTTC]   Wi-Fi propia .......... red "WTTC" · clave "••••"
15:42:16.434 [WTTC]                           web http://192.168.4.1 · siempre encendida · se oculta estando en la red con internet
15:42:16.440 [WTTC]   Red con internet ...... "WIFI" · conectada, http://192.168.1.194
15:42:16.451 [WTTC]   Web desde esa red ..... http://wttc.local · usuario "wttc" · clave "••••"
15:42:16.451 [WTTC]   Telegram .............. sin configurar
15:42:16.456 [WTTC]   Mis estadísticas ...... código XXXX-XXXX-XXXX-XXXX · se envían
15:42:16.467 [WTTC]                           https://wttc.favala.es/mi.php#XXXX-XXXX-XXXX-XXXX
15:42:16.468 [WTTC]   Piezas opcionales ..... pantalla no · termómetro no
15:42:16.473 [WTTC]   Botón «calentar» ...... IO7 a GND · 30 min
15:42:16.490 [WTTC]   Este arranque ......... reinicio pedido (actualización, ajustes o la orden reboot) · memoria libre 92 KB · PSRAM 8165 KB (conexiones seguras ahí)
15:42:16.495 [WTTC]   Órdenes remotas ....... permitidas (firmadas por el proyecto; nunca la calefacción)
15:42:16.501 [WTTC]   Actualizaciones ....... buscar y avisar
15:42:16.506 [WTTC] ----------------------------------------------------------------
15:42:16.518 [WTTC]   Órdenes: on [min] [°C] | off | status | info | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset
15:42:16.524 [WTTC] ================================================================
15:42:22.539 [Webasto falsa] » Preguntando quién es («info»)…
15:42:22.548 [Webasto falsa] [00:42:31.437] $ info
15:42:22.549 [Webasto falsa] Órdenes de la consola:
15:42:22.558 [Webasto falsa]   estado            todo lo que tiene ahora: estado, agua, batería, orden, mantenimiento, averías, fallos
15:42:22.564 [Webasto falsa]   resumen           cuántas tramas han llegado, contestado, malas… y el estado
15:42:22.575 [Webasto falsa]   fuera <°C>        temperatura de fuera (cuánto tarda en calentar y en enfriarse)
15:42:22.581 [Webasto falsa]   bateria <V>       tensión de la batería sin carga (8–15 V)
15:42:22.586 [Webasto falsa]   fallo gasoil      no prende: avería 02 cada arranque; al tercero seguido, bloqueada (07)
15:42:22.592 [Webasto falsa]   fallo llama       se le apaga la llama calentando: avería 03
15:42:22.598 [Webasto falsa]   fallo corriente   sin corriente (fusible): no contesta a nada
15:42:22.603 [Webasto falsa]   fallo mudo        el W-Bus no contesta (cable suelto)
15:42:22.608 [Webasto falsa]   fallo ninguno     quita los fallos provocados
15:42:22.614 [Webasto falsa]   borrar            borra las averías y la desbloquea
15:42:22.615 [Webasto falsa]   rapido <1..60>    el tiempo va N veces más deprisa
15:42:22.625 [Webasto falsa]   log <0..3>        0 importante · 1 + órdenes (de fábrica) · 2 + bytes · 3 + estado cada 10 s
15:42:22.631 [Webasto falsa]   traza on|off      atajo: log 2 / log 0
15:42:22.636 [Webasto falsa]   eco si|no         sí: conexión directa (A) · no: por el bus con TJA1020 (B)
15:42:22.640 [Webasto falsa]   pines <rx> <tx>   pines del W-Bus (de fábrica 16 17)
15:42:22.640 [Webasto falsa]   reiniciar
15:42:49.205 [Webasto falsa] [00:42:58.096] < pide los sensores (0x50 05)
15:42:49.315 [Webasto falsa] [00:42:58.204] > agua 5 °C · 12.60 V · llama no · 0 W · bujía 1.13 Ω
15:42:53.285 [Webasto falsa] [00:43:02.174] < encender 15 min
15:42:53.295 [Webasto falsa] [00:43:02.175] . encender 15 min: aceptado (arranque nº 1; mantenimiento cada <20 s o se apaga)
15:42:53.296 [Webasto falsa] [00:43:02.175] . estado: apagada → ventilador (empieza el arranque)
15:42:53.367 [WTTC] 17/10 15:42  Encendida (manual, 15 min)
15:42:53.368 [Webasto falsa] [00:43:02.257] > «vale, 15 min»
15:42:53.378 [Webasto falsa] [00:43:02.258] = ventilador · agua 5.0 °C · dentro 5.0 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
15:42:53.384 [Webasto falsa] [00:43:02.269] =   orden 0x21: quedan 900 s · último mantenimiento hace 0.0 s (a los 20 s se apaga)
15:42:53.395 [Webasto falsa] [00:43:02.269] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:42:53.401 [Webasto falsa] [00:43:02.280] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:42:57.338 [Webasto falsa] [00:43:06.229] < pide los sensores (0x50 05)
15:42:57.448 [Webasto falsa] [00:43:06.337] > agua 5 °C · 12.48 V · llama no · 0 W · bujía 1.13 Ω
15:42:58.421 [Webasto falsa] [00:43:07.311] < mantenimiento: «sigue con la orden 0x21»
15:42:58.495 [Webasto falsa] [00:43:07.387] > «sigo con la orden»
15:43:03.423 [Webasto falsa] [00:43:12.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:03.495 [Webasto falsa] [00:43:12.387] > «sigo con la orden»
15:43:05.287 [Webasto falsa] [00:43:14.175] . estado: ventilador → bujía (ventilador listo: calienta la bujía)
15:43:05.470 [Webasto falsa] [00:43:14.361] < pide los sensores (0x50 05)
15:43:05.580 [Webasto falsa] [00:43:14.469] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:08.423 [Webasto falsa] [00:43:17.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:08.495 [Webasto falsa] [00:43:17.387] > «sigo con la orden»
15:43:13.423 [Webasto falsa] [00:43:22.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:13.495 [Webasto falsa] [00:43:22.387] > «sigo con la orden»
15:43:13.602 [Webasto falsa] [00:43:22.493] < pide los sensores (0x50 05)
15:43:13.712 [Webasto falsa] [00:43:22.601] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:18.423 [Webasto falsa] [00:43:27.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:18.495 [Webasto falsa] [00:43:27.387] > «sigo con la orden»
15:43:21.734 [Webasto falsa] [00:43:30.625] < pide los sensores (0x50 05)
15:43:21.843 [Webasto falsa] [00:43:30.733] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:23.423 [Webasto falsa] [00:43:32.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:23.494 [Webasto falsa] [00:43:32.387] > «sigo con la orden»
15:43:28.423 [Webasto falsa] [00:43:37.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:28.495 [Webasto falsa] [00:43:37.387] > «sigo con la orden»
15:43:29.866 [Webasto falsa] [00:43:38.757] < pide los sensores (0x50 05)
15:43:29.975 [Webasto falsa] [00:43:38.865] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:33.425 [Webasto falsa] [00:43:42.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:33.496 [Webasto falsa] [00:43:42.389] > «sigo con la orden»
15:43:37.997 [Webasto falsa] [00:43:46.889] < pide los sensores (0x50 05)
15:43:38.108 [Webasto falsa] [00:43:46.997] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:38.425 [Webasto falsa] [00:43:47.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:38.497 [Webasto falsa] [00:43:47.389] > «sigo con la orden»
15:43:43.425 [Webasto falsa] [00:43:52.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:43.497 [Webasto falsa] [00:43:52.389] > «sigo con la orden»
15:43:46.129 [Webasto falsa] [00:43:55.021] < pide los sensores (0x50 05)
15:43:46.240 [Webasto falsa] [00:43:55.129] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:48.425 [Webasto falsa] [00:43:57.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:48.497 [Webasto falsa] [00:43:57.389] > «sigo con la orden»
15:43:50.287 [Webasto falsa] [00:43:59.175] . estado: bujía → encendido (bujía caliente: bomba de gasoil en marcha)
15:43:53.290 [Webasto falsa] [00:44:02.175] = encendido · agua 5.0 °C · dentro 5.0 °C · fuera 5.0 °C · 12.06 V · llama no · 0 W
15:43:53.302 [Webasto falsa] [00:44:02.176] =   orden 0x21: quedan 839 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:43:53.307 [Webasto falsa] [00:44:02.187] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:43:53.315 [Webasto falsa] [00:44:02.198] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:43:53.425 [Webasto falsa] [00:44:02.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:53.497 [Webasto falsa] [00:44:02.389] > «sigo con la orden»
15:43:54.263 [Webasto falsa] [00:44:03.155] < pide los sensores (0x50 05)
15:43:54.374 [Webasto falsa] [00:44:03.263] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:43:58.425 [Webasto falsa] [00:44:07.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:58.497 [Webasto falsa] [00:44:07.389] > «sigo con la orden»
15:44:02.395 [Webasto falsa] [00:44:11.287] < pide los sensores (0x50 05)
15:44:02.505 [Webasto falsa] [00:44:11.395] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:44:03.424 [Webasto falsa] [00:44:12.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:03.496 [Webasto falsa] [00:44:12.389] > «sigo con la orden»
15:44:08.424 [Webasto falsa] [00:44:17.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:08.496 [Webasto falsa] [00:44:17.389] > «sigo con la orden»
15:44:10.527 [Webasto falsa] [00:44:19.419] < pide los sensores (0x50 05)
15:44:10.637 [Webasto falsa] [00:44:19.527] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:44:13.424 [Webasto falsa] [00:44:22.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:13.496 [Webasto falsa] [00:44:22.389] > «sigo con la orden»
15:44:18.426 [Webasto falsa] [00:44:27.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:18.498 [Webasto falsa] [00:44:27.391] > «sigo con la orden»
15:44:18.661 [Webasto falsa] [00:44:27.553] < pide los sensores (0x50 05)
15:44:18.771 [Webasto falsa] [00:44:27.661] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:44:20.284 [Webasto falsa] [00:44:29.175] . estado: encendido → estabilizando (¡llama!)
15:44:23.426 [Webasto falsa] [00:44:32.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:23.498 [Webasto falsa] [00:44:32.391] > «sigo con la orden»
15:44:26.793 [Webasto falsa] [00:44:35.685] < pide los sensores (0x50 05)
15:44:26.903 [Webasto falsa] [00:44:35.793] > agua 5 °C · 12.36 V · llama sí · 2771 W · bujía 1.13 Ω
15:44:28.426 [Webasto falsa] [00:44:37.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:28.498 [Webasto falsa] [00:44:37.391] > «sigo con la orden»
15:44:33.426 [Webasto falsa] [00:44:42.316] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:33.498 [Webasto falsa] [00:44:42.391] > «sigo con la orden»
15:44:34.925 [Webasto falsa] [00:44:43.817] < pide los sensores (0x50 05)
15:44:35.035 [Webasto falsa] [00:44:43.925] > agua 6 °C · 12.36 V · llama sí · 3110 W · bujía 1.13 Ω
15:44:38.426 [Webasto falsa] [00:44:47.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:38.498 [Webasto falsa] [00:44:47.391] > «sigo con la orden»
15:44:43.057 [Webasto falsa] [00:44:51.949] < pide los sensores (0x50 05)
15:44:43.167 [Webasto falsa] [00:44:52.057] > agua 6 °C · 12.36 V · llama sí · 3448 W · bujía 1.13 Ω
15:44:43.426 [Webasto falsa] [00:44:52.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:43.498 [Webasto falsa] [00:44:52.391] > «sigo con la orden»
15:44:48.426 [Webasto falsa] [00:44:57.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:48.498 [Webasto falsa] [00:44:57.391] > «sigo con la orden»
15:44:51.189 [Webasto falsa] [00:45:00.081] < pide los sensores (0x50 05)
15:44:51.299 [Webasto falsa] [00:45:00.189] > agua 6 °C · 12.36 V · llama sí · 3787 W · bujía 1.14 Ω
15:44:53.291 [Webasto falsa] [00:45:02.177] = estabilizando · agua 6.4 °C · dentro 5.0 °C · fuera 5.0 °C · 12.36 V · llama sí · 3875 W
15:44:53.303 [Webasto falsa] [00:45:02.178] =   orden 0x21: quedan 779 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:44:53.314 [Webasto falsa] [00:45:02.189] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:44:53.317 [Webasto falsa] [00:45:02.200] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:44:53.426 [Webasto falsa] [00:45:02.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:53.498 [Webasto falsa] [00:45:02.391] > «sigo con la orden»
15:44:58.426 [Webasto falsa] [00:45:07.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:58.498 [Webasto falsa] [00:45:07.391] > «sigo con la orden»
15:44:59.321 [Webasto falsa] [00:45:08.213] < pide los sensores (0x50 05)
15:44:59.431 [Webasto falsa] [00:45:08.321] > agua 7 °C · 12.36 V · llama sí · 4126 W · bujía 1.14 Ω
15:45:03.426 [Webasto falsa] [00:45:12.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:03.497 [Webasto falsa] [00:45:12.391] > «sigo con la orden»
15:45:07.452 [Webasto falsa] [00:45:16.345] < pide los sensores (0x50 05)
15:45:07.563 [Webasto falsa] [00:45:16.453] > agua 7 °C · 12.36 V · llama sí · 4465 W · bujía 1.14 Ω
15:45:08.425 [Webasto falsa] [00:45:17.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:08.497 [Webasto falsa] [00:45:17.391] > «sigo con la orden»
15:45:13.428 [Webasto falsa] [00:45:22.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:13.499 [Webasto falsa] [00:45:22.393] > «sigo con la orden»
15:45:15.584 [Webasto falsa] [00:45:24.477] < pide los sensores (0x50 05)
15:45:15.695 [Webasto falsa] [00:45:24.585] > agua 8 °C · 12.36 V · llama sí · 4804 W · bujía 1.14 Ω
15:45:18.428 [Webasto falsa] [00:45:27.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:18.499 [Webasto falsa] [00:45:27.393] > «sigo con la orden»
15:45:20.283 [Webasto falsa] [00:45:29.175] . estado: estabilizando → a tope (llama estable)
15:45:23.427 [Webasto falsa] [00:45:32.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:23.499 [Webasto falsa] [00:45:32.393] > «sigo con la orden»
15:45:23.716 [Webasto falsa] [00:45:32.609] < pide los sensores (0x50 05)
15:45:23.827 [Webasto falsa] [00:45:32.717] > agua 8 °C · 12.39 V · llama sí · 5000 W · bujía 1.15 Ω
15:45:28.427 [Webasto falsa] [00:45:37.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:28.499 [Webasto falsa] [00:45:37.393] > «sigo con la orden»
15:45:31.848 [Webasto falsa] [00:45:40.741] < pide los sensores (0x50 05)
15:45:31.959 [Webasto falsa] [00:45:40.849] > agua 9 °C · 12.39 V · llama sí · 5000 W · bujía 1.15 Ω
15:45:33.427 [Webasto falsa] [00:45:42.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:33.499 [Webasto falsa] [00:45:42.393] > «sigo con la orden»
15:45:38.428 [Webasto falsa] [00:45:47.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:38.499 [Webasto falsa] [00:45:47.393] > «sigo con la orden»
15:45:39.980 [Webasto falsa] [00:45:48.873] < pide los sensores (0x50 05)
15:45:40.091 [Webasto falsa] [00:45:48.981] > agua 9 °C · 12.39 V · llama sí · 5000 W · bujía 1.15 Ω
15:45:43.427 [Webasto falsa] [00:45:52.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:43.499 [Webasto falsa] [00:45:52.393] > «sigo con la orden»
15:45:48.112 [Webasto falsa] [00:45:57.005] < pide los sensores (0x50 05)
15:45:48.222 [Webasto falsa] [00:45:57.113] > agua 10 °C · 12.39 V · llama sí · 5000 W · bujía 1.16 Ω
15:45:48.427 [Webasto falsa] [00:45:57.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:48.499 [Webasto falsa] [00:45:57.393] > «sigo con la orden»
15:45:53.293 [Webasto falsa] [00:46:02.179] = a tope · agua 10.1 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:45:53.304 [Webasto falsa] [00:46:02.180] =   orden 0x21: quedan 719 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:45:53.315 [Webasto falsa] [00:46:02.191] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:45:53.318 [Webasto falsa] [00:46:02.202] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:45:53.427 [Webasto falsa] [00:46:02.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:53.499 [Webasto falsa] [00:46:02.393] > «sigo con la orden»
15:45:56.244 [Webasto falsa] [00:46:05.137] < pide los sensores (0x50 05)
15:45:56.354 [Webasto falsa] [00:46:05.245] > agua 10 °C · 12.39 V · llama sí · 5000 W · bujía 1.16 Ω
15:45:58.427 [Webasto falsa] [00:46:07.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:58.499 [Webasto falsa] [00:46:07.393] > «sigo con la orden»
15:46:03.427 [Webasto falsa] [00:46:12.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:03.499 [Webasto falsa] [00:46:12.393] > «sigo con la orden»
15:46:04.375 [Webasto falsa] [00:46:13.269] < pide los sensores (0x50 05)
15:46:04.486 [Webasto falsa] [00:46:13.377] > agua 11 °C · 12.39 V · llama sí · 5000 W · bujía 1.16 Ω
15:46:08.427 [Webasto falsa] [00:46:17.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:08.499 [Webasto falsa] [00:46:17.393] > «sigo con la orden»
15:46:12.507 [Webasto falsa] [00:46:21.401] < pide los sensores (0x50 05)
15:46:12.618 [Webasto falsa] [00:46:21.509] > agua 11 °C · 12.39 V · llama sí · 5000 W · bujía 1.17 Ω
15:46:13.427 [Webasto falsa] [00:46:22.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:13.499 [Webasto falsa] [00:46:22.393] > «sigo con la orden»
15:46:18.427 [Webasto falsa] [00:46:27.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:18.498 [Webasto falsa] [00:46:27.393] > «sigo con la orden»
15:46:20.639 [Webasto falsa] [00:46:29.533] < pide los sensores (0x50 05)
15:46:20.750 [Webasto falsa] [00:46:29.641] > agua 12 °C · 12.39 V · llama sí · 5000 W · bujía 1.17 Ω
15:46:23.427 [Webasto falsa] [00:46:32.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:23.499 [Webasto falsa] [00:46:32.393] > «sigo con la orden»
15:46:28.426 [Webasto falsa] [00:46:37.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:28.498 [Webasto falsa] [00:46:37.393] > «sigo con la orden»
15:46:28.771 [Webasto falsa] [00:46:37.665] < pide los sensores (0x50 05)
15:46:28.882 [Webasto falsa] [00:46:37.773] > agua 12 °C · 12.39 V · llama sí · 5000 W · bujía 1.17 Ω
15:46:33.427 [Webasto falsa] [00:46:42.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:33.499 [Webasto falsa] [00:46:42.393] > «sigo con la orden»
15:46:36.903 [Webasto falsa] [00:46:45.797] < pide los sensores (0x50 05)
15:46:37.014 [Webasto falsa] [00:46:45.905] > agua 13 °C · 12.39 V · llama sí · 5000 W · bujía 1.18 Ω
15:46:38.427 [Webasto falsa] [00:46:47.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:38.498 [Webasto falsa] [00:46:47.393] > «sigo con la orden»
15:46:43.427 [Webasto falsa] [00:46:52.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:43.498 [Webasto falsa] [00:46:52.393] > «sigo con la orden»
15:46:45.035 [Webasto falsa] [00:46:53.929] < pide los sensores (0x50 05)
15:46:45.146 [Webasto falsa] [00:46:54.037] > agua 13 °C · 12.39 V · llama sí · 5000 W · bujía 1.18 Ω
15:46:48.427 [Webasto falsa] [00:46:57.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:48.498 [Webasto falsa] [00:46:57.393] > «sigo con la orden»
15:46:53.167 [Webasto falsa] [00:47:02.061] < pide los sensores (0x50 05)
15:46:53.278 [Webasto falsa] [00:47:02.169] > agua 14 °C · 12.39 V · llama sí · 5000 W · bujía 1.18 Ω
15:46:53.294 [Webasto falsa] [00:47:02.181] = a tope · agua 13.9 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:46:53.305 [Webasto falsa] [00:47:02.182] =   orden 0x21: quedan 659 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:46:53.316 [Webasto falsa] [00:47:02.193] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:46:53.319 [Webasto falsa] [00:47:02.204] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:46:53.427 [Webasto falsa] [00:47:02.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:53.498 [Webasto falsa] [00:47:02.393] > «sigo con la orden»
15:46:58.427 [Webasto falsa] [00:47:07.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:58.498 [Webasto falsa] [00:47:07.393] > «sigo con la orden»
15:47:01.299 [Webasto falsa] [00:47:10.193] < pide los sensores (0x50 05)
15:47:01.410 [Webasto falsa] [00:47:10.301] > agua 14 °C · 12.39 V · llama sí · 5000 W · bujía 1.19 Ω
15:47:03.427 [Webasto falsa] [00:47:12.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:03.498 [Webasto falsa] [00:47:12.393] > «sigo con la orden»
15:47:08.426 [Webasto falsa] [00:47:17.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:08.498 [Webasto falsa] [00:47:17.393] > «sigo con la orden»
15:47:09.431 [Webasto falsa] [00:47:18.325] < pide los sensores (0x50 05)
15:47:09.541 [Webasto falsa] [00:47:18.433] > agua 15 °C · 12.39 V · llama sí · 5000 W · bujía 1.19 Ω
15:47:13.426 [Webasto falsa] [00:47:22.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:13.498 [Webasto falsa] [00:47:22.393] > «sigo con la orden»
15:47:17.563 [Webasto falsa] [00:47:26.457] < pide los sensores (0x50 05)
15:47:17.673 [Webasto falsa] [00:47:26.565] > agua 15 °C · 12.39 V · llama sí · 5000 W · bujía 1.19 Ω
15:47:18.426 [Webasto falsa] [00:47:27.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:18.498 [Webasto falsa] [00:47:27.393] > «sigo con la orden»
15:47:23.428 [Webasto falsa] [00:47:32.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:23.500 [Webasto falsa] [00:47:32.395] > «sigo con la orden»
15:47:25.694 [Webasto falsa] [00:47:34.589] < pide los sensores (0x50 05)
15:47:25.805 [Webasto falsa] [00:47:34.697] > agua 16 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:28.428 [Webasto falsa] [00:47:37.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:28.500 [Webasto falsa] [00:47:37.395] > «sigo con la orden»
15:47:33.428 [Webasto falsa] [00:47:42.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:33.500 [Webasto falsa] [00:47:42.395] > «sigo con la orden»
15:47:33.826 [Webasto falsa] [00:47:42.721] < pide los sensores (0x50 05)
15:47:33.937 [Webasto falsa] [00:47:42.829] > agua 16 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:38.428 [Webasto falsa] [00:47:47.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:38.499 [Webasto falsa] [00:47:47.395] > «sigo con la orden»
15:47:41.960 [Webasto falsa] [00:47:50.855] < pide los sensores (0x50 05)
15:47:42.071 [Webasto falsa] [00:47:50.963] > agua 17 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:43.428 [Webasto falsa] [00:47:52.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:43.500 [Webasto falsa] [00:47:52.395] > «sigo con la orden»
15:47:48.428 [Webasto falsa] [00:47:57.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:48.500 [Webasto falsa] [00:47:57.395] > «sigo con la orden»
15:47:50.092 [Webasto falsa] [00:47:58.987] < pide los sensores (0x50 05)
15:47:50.203 [Webasto falsa] [00:47:59.095] > agua 17 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:53.295 [Webasto falsa] [00:48:02.183] = a tope · agua 17.6 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:47:53.306 [Webasto falsa] [00:48:02.184] =   orden 0x21: quedan 599 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:47:53.317 [Webasto falsa] [00:48:02.195] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:47:53.320 [Webasto falsa] [00:48:02.206] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:47:53.427 [Webasto falsa] [00:48:02.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:53.499 [Webasto falsa] [00:48:02.395] > «sigo con la orden»
15:47:58.226 [Webasto falsa] [00:48:07.121] < pide los sensores (0x50 05)
15:47:58.337 [Webasto falsa] [00:48:07.229] > agua 18 °C · 12.39 V · llama sí · 5000 W · bujía 1.21 Ω
15:47:58.428 [Webasto falsa] [00:48:07.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:58.500 [Webasto falsa] [00:48:07.395] > «sigo con la orden»
15:48:03.427 [Webasto falsa] [00:48:12.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:03.499 [Webasto falsa] [00:48:12.395] > «sigo con la orden»
15:48:06.358 [Webasto falsa] [00:48:15.253] < pide los sensores (0x50 05)
15:48:06.469 [Webasto falsa] [00:48:15.361] > agua 18 °C · 12.39 V · llama sí · 5000 W · bujía 1.21 Ω
15:48:08.427 [Webasto falsa] [00:48:17.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:08.499 [Webasto falsa] [00:48:17.395] > «sigo con la orden»
15:48:13.427 [Webasto falsa] [00:48:22.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:13.499 [Webasto falsa] [00:48:22.395] > «sigo con la orden»
15:48:14.489 [Webasto falsa] [00:48:23.385] < pide los sensores (0x50 05)
15:48:14.601 [Webasto falsa] [00:48:23.493] > agua 19 °C · 12.39 V · llama sí · 5000 W · bujía 1.21 Ω
15:48:18.427 [Webasto falsa] [00:48:27.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:18.499 [Webasto falsa] [00:48:27.395] > «sigo con la orden»
15:48:22.622 [Webasto falsa] [00:48:31.517] < pide los sensores (0x50 05)
15:48:22.733 [Webasto falsa] [00:48:31.625] > agua 19 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:23.427 [Webasto falsa] [00:48:32.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:23.499 [Webasto falsa] [00:48:32.395] > «sigo con la orden»
15:48:28.427 [Webasto falsa] [00:48:37.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:28.499 [Webasto falsa] [00:48:37.395] > «sigo con la orden»
15:48:30.756 [Webasto falsa] [00:48:39.651] < pide los sensores (0x50 05)
15:48:30.866 [Webasto falsa] [00:48:39.759] > agua 20 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:33.427 [Webasto falsa] [00:48:42.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:33.499 [Webasto falsa] [00:48:42.395] > «sigo con la orden»
15:48:38.429 [Webasto falsa] [00:48:47.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:38.501 [Webasto falsa] [00:48:47.397] > «sigo con la orden»
15:48:38.888 [Webasto falsa] [00:48:47.783] < pide los sensores (0x50 05)
15:48:38.998 [Webasto falsa] [00:48:47.891] > agua 20 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:43.429 [Webasto falsa] [00:48:52.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:43.501 [Webasto falsa] [00:48:52.397] > «sigo con la orden»
15:48:47.020 [Webasto falsa] [00:48:55.915] < pide los sensores (0x50 05)
15:48:47.130 [Webasto falsa] [00:48:56.023] > agua 21 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:48.429 [Webasto falsa] [00:48:57.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:48.501 [Webasto falsa] [00:48:57.397] > «sigo con la orden»
15:48:53.296 [Webasto falsa] [00:49:02.185] = a tope · agua 21.1 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:48:53.308 [Webasto falsa] [00:49:02.186] =   orden 0x21: quedan 539 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:48:53.318 [Webasto falsa] [00:49:02.197] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:48:53.322 [Webasto falsa] [00:49:02.208] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:48:53.429 [Webasto falsa] [00:49:02.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:53.501 [Webasto falsa] [00:49:02.397] > «sigo con la orden»
15:48:55.152 [Webasto falsa] [00:49:04.047] < pide los sensores (0x50 05)
15:48:55.262 [Webasto falsa] [00:49:04.155] > agua 21 °C · 12.39 V · llama sí · 5000 W · bujía 1.23 Ω
15:48:58.429 [Webasto falsa] [00:49:07.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:58.501 [Webasto falsa] [00:49:07.397] > «sigo con la orden»
15:49:03.284 [Webasto falsa] [00:49:12.179] < pide los sensores (0x50 05)
15:49:03.394 [Webasto falsa] [00:49:12.287] > agua 22 °C · 12.39 V · llama sí · 5000 W · bujía 1.23 Ω
15:49:03.429 [Webasto falsa] [00:49:12.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:03.500 [Webasto falsa] [00:49:12.397] > «sigo con la orden»
15:49:08.429 [Webasto falsa] [00:49:17.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:08.500 [Webasto falsa] [00:49:17.397] > «sigo con la orden»
15:49:11.415 [Webasto falsa] [00:49:20.311] < pide los sensores (0x50 05)
15:49:11.526 [Webasto falsa] [00:49:20.419] > agua 22 °C · 12.39 V · llama sí · 5000 W · bujía 1.23 Ω
15:49:13.429 [Webasto falsa] [00:49:22.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:13.500 [Webasto falsa] [00:49:22.397] > «sigo con la orden»
15:49:18.429 [Webasto falsa] [00:49:27.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:18.500 [Webasto falsa] [00:49:27.397] > «sigo con la orden»
15:49:19.547 [Webasto falsa] [00:49:28.443] < pide los sensores (0x50 05)
15:49:19.658 [Webasto falsa] [00:49:28.551] > agua 23 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:23.429 [Webasto falsa] [00:49:32.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:23.500 [Webasto falsa] [00:49:32.397] > «sigo con la orden»
15:49:27.679 [Webasto falsa] [00:49:36.575] < pide los sensores (0x50 05)
15:49:27.790 [Webasto falsa] [00:49:36.683] > agua 23 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:28.428 [Webasto falsa] [00:49:37.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:28.500 [Webasto falsa] [00:49:37.397] > «sigo con la orden»
15:49:33.429 [Webasto falsa] [00:49:42.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:33.500 [Webasto falsa] [00:49:42.397] > «sigo con la orden»
15:49:35.811 [Webasto falsa] [00:49:44.707] < pide los sensores (0x50 05)
15:49:35.921 [Webasto falsa] [00:49:44.815] > agua 24 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:38.429 [Webasto falsa] [00:49:47.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:38.500 [Webasto falsa] [00:49:47.397] > «sigo con la orden»
15:49:43.428 [Webasto falsa] [00:49:52.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:43.500 [Webasto falsa] [00:49:52.397] > «sigo con la orden»
15:49:43.943 [Webasto falsa] [00:49:52.839] < pide los sensores (0x50 05)
15:49:44.054 [Webasto falsa] [00:49:52.947] > agua 24 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:48.428 [Webasto falsa] [00:49:57.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:48.500 [Webasto falsa] [00:49:57.397] > «sigo con la orden»
15:49:52.075 [Webasto falsa] [00:50:00.971] < pide los sensores (0x50 05)
15:49:52.186 [Webasto falsa] [00:50:01.079] > agua 25 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:49:53.298 [Webasto falsa] [00:50:02.187] = a tope · agua 24.6 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:49:53.309 [Webasto falsa] [00:50:02.188] =   orden 0x21: quedan 479 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:49:53.320 [Webasto falsa] [00:50:02.199] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:49:53.323 [Webasto falsa] [00:50:02.210] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:49:53.428 [Webasto falsa] [00:50:02.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:53.500 [Webasto falsa] [00:50:02.397] > «sigo con la orden»
15:49:58.428 [Webasto falsa] [00:50:07.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:58.500 [Webasto falsa] [00:50:07.397] > «sigo con la orden»
15:50:00.207 [Webasto falsa] [00:50:09.103] < pide los sensores (0x50 05)
15:50:00.318 [Webasto falsa] [00:50:09.211] > agua 25 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:50:03.428 [Webasto falsa] [00:50:12.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:03.500 [Webasto falsa] [00:50:12.397] > «sigo con la orden»
15:50:08.338 [Webasto falsa] [00:50:17.235] < pide los sensores (0x50 05)
15:50:08.449 [Webasto falsa] [00:50:17.343] > agua 25 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:50:08.482 [Webasto falsa] [00:50:17.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:50:08.554 [Webasto falsa] [00:50:17.451] > «sigo con la orden»
15:50:13.482 [Webasto falsa] [00:50:22.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:13.554 [Webasto falsa] [00:50:22.451] > «sigo con la orden»
15:50:16.470 [Webasto falsa] [00:50:25.367] < pide los sensores (0x50 05)
15:50:16.581 [Webasto falsa] [00:50:25.475] > agua 26 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:50:18.482 [Webasto falsa] [00:50:27.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:18.554 [Webasto falsa] [00:50:27.451] > «sigo con la orden»
15:50:23.482 [Webasto falsa] [00:50:32.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:23.554 [Webasto falsa] [00:50:32.451] > «sigo con la orden»
15:50:24.602 [Webasto falsa] [00:50:33.499] < pide los sensores (0x50 05)
15:50:24.713 [Webasto falsa] [00:50:33.607] > agua 26 °C · 12.39 V · llama sí · 5000 W · bujía 1.26 Ω
15:50:28.482 [Webasto falsa] [00:50:37.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:28.554 [Webasto falsa] [00:50:37.451] > «sigo con la orden»
15:50:32.736 [Webasto falsa] [00:50:41.633] < pide los sensores (0x50 05)
15:50:32.847 [Webasto falsa] [00:50:41.741] > agua 27 °C · 12.39 V · llama sí · 5000 W · bujía 1.26 Ω
15:50:33.482 [Webasto falsa] [00:50:42.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:33.554 [Webasto falsa] [00:50:42.451] > «sigo con la orden»
15:50:38.482 [Webasto falsa] [00:50:47.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:38.553 [Webasto falsa] [00:50:47.451] > «sigo con la orden»
15:50:40.868 [Webasto falsa] [00:50:49.765] < pide los sensores (0x50 05)
15:50:40.979 [Webasto falsa] [00:50:49.873] > agua 27 °C · 12.39 V · llama sí · 5000 W · bujía 1.26 Ω
15:50:43.484 [Webasto falsa] [00:50:52.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:43.556 [Webasto falsa] [00:50:52.453] > «sigo con la orden»
15:50:48.484 [Webasto falsa] [00:50:57.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:48.555 [Webasto falsa] [00:50:57.453] > «sigo con la orden»
15:50:49.000 [Webasto falsa] [00:50:57.897] < pide los sensores (0x50 05)
15:50:49.110 [Webasto falsa] [00:50:58.005] > agua 28 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:50:53.299 [Webasto falsa] [00:51:02.189] = a tope · agua 27.9 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:50:53.310 [Webasto falsa] [00:51:02.190] =   orden 0x21: quedan 419 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
15:50:53.321 [Webasto falsa] [00:51:02.201] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:50:53.324 [Webasto falsa] [00:51:02.212] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:50:53.484 [Webasto falsa] [00:51:02.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:53.555 [Webasto falsa] [00:51:02.453] > «sigo con la orden»
15:50:57.132 [Webasto falsa] [00:51:06.029] < pide los sensores (0x50 05)
15:50:57.243 [Webasto falsa] [00:51:06.137] > agua 28 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:50:58.484 [Webasto falsa] [00:51:07.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:58.555 [Webasto falsa] [00:51:07.453] > «sigo con la orden»
15:51:03.484 [Webasto falsa] [00:51:12.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:03.555 [Webasto falsa] [00:51:12.453] > «sigo con la orden»
15:51:05.263 [Webasto falsa] [00:51:14.161] < pide los sensores (0x50 05)
15:51:05.374 [Webasto falsa] [00:51:14.269] > agua 29 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:51:08.483 [Webasto falsa] [00:51:17.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:08.555 [Webasto falsa] [00:51:17.453] > «sigo con la orden»
15:51:13.396 [Webasto falsa] [00:51:22.293] < pide los sensores (0x50 05)
15:51:13.506 [Webasto falsa] [00:51:22.401] > agua 29 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:51:13.539 [Webasto falsa] [00:51:22.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:51:13.611 [Webasto falsa] [00:51:22.509] > «sigo con la orden»
15:51:18.539 [Webasto falsa] [00:51:27.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:18.611 [Webasto falsa] [00:51:27.509] > «sigo con la orden»
15:51:21.528 [Webasto falsa] [00:51:30.425] < pide los sensores (0x50 05)
15:51:21.638 [Webasto falsa] [00:51:30.533] > agua 29 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:23.539 [Webasto falsa] [00:51:32.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:23.611 [Webasto falsa] [00:51:32.509] > «sigo con la orden»
15:51:28.539 [Webasto falsa] [00:51:37.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:28.611 [Webasto falsa] [00:51:37.509] > «sigo con la orden»
15:51:29.659 [Webasto falsa] [00:51:38.557] < pide los sensores (0x50 05)
15:51:29.770 [Webasto falsa] [00:51:38.665] > agua 30 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:33.539 [Webasto falsa] [00:51:42.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:33.610 [Webasto falsa] [00:51:42.509] > «sigo con la orden»
15:51:37.793 [Webasto falsa] [00:51:46.691] < pide los sensores (0x50 05)
15:51:37.904 [Webasto falsa] [00:51:46.799] > agua 30 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:38.539 [Webasto falsa] [00:51:47.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:38.611 [Webasto falsa] [00:51:47.509] > «sigo con la orden»
15:51:43.539 [Webasto falsa] [00:51:52.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:43.611 [Webasto falsa] [00:51:52.509] > «sigo con la orden»
15:51:45.927 [Webasto falsa] [00:51:54.825] < pide los sensores (0x50 05)
15:51:46.038 [Webasto falsa] [00:51:54.933] > agua 31 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:48.539 [Webasto falsa] [00:51:57.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:48.610 [Webasto falsa] [00:51:57.509] > «sigo con la orden»
15:51:53.300 [Webasto falsa] [00:52:02.191] = a tope · agua 31.2 °C · dentro 5.1 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:51:53.312 [Webasto falsa] [00:52:02.192] =   orden 0x21: quedan 359 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
15:51:53.322 [Webasto falsa] [00:52:02.203] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:51:53.325 [Webasto falsa] [00:52:02.214] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:51:53.539 [Webasto falsa] [00:52:02.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:53.611 [Webasto falsa] [00:52:02.509] > «sigo con la orden»
15:51:54.059 [Webasto falsa] [00:52:02.957] < pide los sensores (0x50 05)
15:51:54.170 [Webasto falsa] [00:52:03.065] > agua 31 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:51:58.539 [Webasto falsa] [00:52:07.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:58.611 [Webasto falsa] [00:52:07.509] > «sigo con la orden»
15:52:02.191 [Webasto falsa] [00:52:11.089] < pide los sensores (0x50 05)
15:52:02.302 [Webasto falsa] [00:52:11.197] > agua 32 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:52:03.539 [Webasto falsa] [00:52:12.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:03.610 [Webasto falsa] [00:52:12.509] > «sigo con la orden»
15:52:08.538 [Webasto falsa] [00:52:17.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:08.610 [Webasto falsa] [00:52:17.509] > «sigo con la orden»
15:52:10.321 [Webasto falsa] [00:52:19.219] < pide los sensores (0x50 05)
15:52:10.432 [Webasto falsa] [00:52:19.327] > agua 32 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:52:13.538 [Webasto falsa] [00:52:22.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:13.610 [Webasto falsa] [00:52:22.509] > «sigo con la orden»
15:52:18.453 [Webasto falsa] [00:52:27.351] < pide los sensores (0x50 05)
15:52:18.564 [Webasto falsa] [00:52:27.459] > agua 33 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:52:18.594 [Webasto falsa] [00:52:27.489] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:52:18.666 [Webasto falsa] [00:52:27.565] > «sigo con la orden»
15:52:23.597 [Webasto falsa] [00:52:32.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:23.668 [Webasto falsa] [00:52:32.567] > «sigo con la orden»
15:52:26.585 [Webasto falsa] [00:52:35.483] < pide los sensores (0x50 05)
15:52:26.696 [Webasto falsa] [00:52:35.591] > agua 33 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:28.596 [Webasto falsa] [00:52:37.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:28.668 [Webasto falsa] [00:52:37.567] > «sigo con la orden»
15:52:33.596 [Webasto falsa] [00:52:42.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:33.668 [Webasto falsa] [00:52:42.567] > «sigo con la orden»
15:52:34.717 [Webasto falsa] [00:52:43.615] < pide los sensores (0x50 05)
15:52:34.828 [Webasto falsa] [00:52:43.723] > agua 33 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:38.596 [Webasto falsa] [00:52:47.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:38.668 [Webasto falsa] [00:52:47.567] > «sigo con la orden»
15:52:42.848 [Webasto falsa] [00:52:51.747] < pide los sensores (0x50 05)
15:52:42.959 [Webasto falsa] [00:52:51.855] > agua 34 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:43.596 [Webasto falsa] [00:52:52.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:43.668 [Webasto falsa] [00:52:52.567] > «sigo con la orden»
15:52:48.596 [Webasto falsa] [00:52:57.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:48.668 [Webasto falsa] [00:52:57.567] > «sigo con la orden»
15:52:50.981 [Webasto falsa] [00:52:59.879] < pide los sensores (0x50 05)
15:52:51.091 [Webasto falsa] [00:52:59.987] > agua 34 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:53.302 [Webasto falsa] [00:53:02.193] = a tope · agua 34.3 °C · dentro 5.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:52:53.313 [Webasto falsa] [00:53:02.194] =   orden 0x21: quedan 299 s · último mantenimiento hace 4.7 s (a los 20 s se apaga)
15:52:53.324 [Webasto falsa] [00:53:02.205] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:52:53.327 [Webasto falsa] [00:53:02.216] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:52:53.596 [Webasto falsa] [00:53:02.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:53.668 [Webasto falsa] [00:53:02.567] > «sigo con la orden»
15:52:58.596 [Webasto falsa] [00:53:07.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:58.668 [Webasto falsa] [00:53:07.567] > «sigo con la orden»
15:52:59.113 [Webasto falsa] [00:53:08.011] < pide los sensores (0x50 05)
15:52:59.223 [Webasto falsa] [00:53:08.119] > agua 35 °C · 12.39 V · llama sí · 5000 W · bujía 1.31 Ω
15:53:03.598 [Webasto falsa] [00:53:12.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:03.670 [Webasto falsa] [00:53:12.569] > «sigo con la orden»
15:53:07.244 [Webasto falsa] [00:53:16.143] < pide los sensores (0x50 05)
15:53:07.355 [Webasto falsa] [00:53:16.251] > agua 35 °C · 12.39 V · llama sí · 5000 W · bujía 1.31 Ω
15:53:08.598 [Webasto falsa] [00:53:17.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:08.669 [Webasto falsa] [00:53:17.569] > «sigo con la orden»
15:53:13.598 [Webasto falsa] [00:53:22.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:13.669 [Webasto falsa] [00:53:22.569] > «sigo con la orden»
15:53:15.376 [Webasto falsa] [00:53:24.275] < pide los sensores (0x50 05)
15:53:15.486 [Webasto falsa] [00:53:24.383] > agua 35 °C · 12.39 V · llama sí · 5000 W · bujía 1.31 Ω
15:53:18.598 [Webasto falsa] [00:53:27.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:18.669 [Webasto falsa] [00:53:27.569] > «sigo con la orden»
15:53:23.508 [Webasto falsa] [00:53:32.407] < pide los sensores (0x50 05)
15:53:23.619 [Webasto falsa] [00:53:32.515] > agua 36 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:23.652 [Webasto falsa] [00:53:32.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:53:23.723 [Webasto falsa] [00:53:32.623] > «sigo con la orden»
15:53:28.652 [Webasto falsa] [00:53:37.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:28.723 [Webasto falsa] [00:53:37.623] > «sigo con la orden»
15:53:31.640 [Webasto falsa] [00:53:40.539] < pide los sensores (0x50 05)
15:53:31.751 [Webasto falsa] [00:53:40.647] > agua 36 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:33.652 [Webasto falsa] [00:53:42.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:33.723 [Webasto falsa] [00:53:42.623] > «sigo con la orden»
15:53:38.652 [Webasto falsa] [00:53:47.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:38.723 [Webasto falsa] [00:53:47.623] > «sigo con la orden»
15:53:39.772 [Webasto falsa] [00:53:48.671] < pide los sensores (0x50 05)
15:53:39.883 [Webasto falsa] [00:53:48.779] > agua 37 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:43.652 [Webasto falsa] [00:53:52.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:43.723 [Webasto falsa] [00:53:52.623] > «sigo con la orden»
15:53:47.904 [Webasto falsa] [00:53:56.803] < pide los sensores (0x50 05)
15:53:48.015 [Webasto falsa] [00:53:56.911] > agua 37 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:48.651 [Webasto falsa] [00:53:57.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:48.723 [Webasto falsa] [00:53:57.623] > «sigo con la orden»
15:53:53.303 [Webasto falsa] [00:54:02.195] = a tope · agua 37.4 °C · dentro 5.5 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:53:53.314 [Webasto falsa] [00:54:02.196] =   orden 0x21: quedan 239 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
15:53:53.325 [Webasto falsa] [00:54:02.207] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:53:53.328 [Webasto falsa] [00:54:02.218] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:53:53.651 [Webasto falsa] [00:54:02.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:53.723 [Webasto falsa] [00:54:02.623] > «sigo con la orden»
15:53:56.036 [Webasto falsa] [00:54:04.935] < pide los sensores (0x50 05)
15:53:56.147 [Webasto falsa] [00:54:05.043] > agua 38 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:53:58.651 [Webasto falsa] [00:54:07.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:58.723 [Webasto falsa] [00:54:07.623] > «sigo con la orden»
15:54:03.651 [Webasto falsa] [00:54:12.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:03.723 [Webasto falsa] [00:54:12.623] > «sigo con la orden»
15:54:04.167 [Webasto falsa] [00:54:13.067] < pide los sensores (0x50 05)
15:54:04.278 [Webasto falsa] [00:54:13.175] > agua 38 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:08.651 [Webasto falsa] [00:54:17.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:08.723 [Webasto falsa] [00:54:17.623] > «sigo con la orden»
15:54:12.299 [Webasto falsa] [00:54:21.199] < pide los sensores (0x50 05)
15:54:12.410 [Webasto falsa] [00:54:21.307] > agua 38 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:13.651 [Webasto falsa] [00:54:22.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:13.723 [Webasto falsa] [00:54:22.623] > «sigo con la orden»
15:54:18.651 [Webasto falsa] [00:54:27.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:18.723 [Webasto falsa] [00:54:27.623] > «sigo con la orden»
15:54:20.431 [Webasto falsa] [00:54:29.331] < pide los sensores (0x50 05)
15:54:20.542 [Webasto falsa] [00:54:29.439] > agua 39 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:23.651 [Webasto falsa] [00:54:32.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:23.723 [Webasto falsa] [00:54:32.623] > «sigo con la orden»
15:54:28.563 [Webasto falsa] [00:54:37.463] < pide los sensores (0x50 05)
15:54:28.674 [Webasto falsa] [00:54:37.571] > agua 39 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:28.707 [Webasto falsa] [00:54:37.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:54:28.779 [Webasto falsa] [00:54:37.679] > «sigo con la orden»
15:54:33.707 [Webasto falsa] [00:54:42.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:33.778 [Webasto falsa] [00:54:42.679] > «sigo con la orden»
15:54:36.695 [Webasto falsa] [00:54:45.595] < pide los sensores (0x50 05)
15:54:36.806 [Webasto falsa] [00:54:45.703] > agua 40 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:54:38.707 [Webasto falsa] [00:54:47.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:38.779 [Webasto falsa] [00:54:47.679] > «sigo con la orden»
15:54:43.707 [Webasto falsa] [00:54:52.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:43.779 [Webasto falsa] [00:54:52.679] > «sigo con la orden»
15:54:44.827 [Webasto falsa] [00:54:53.727] < pide los sensores (0x50 05)
15:54:44.938 [Webasto falsa] [00:54:53.835] > agua 40 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:54:48.707 [Webasto falsa] [00:54:57.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:48.778 [Webasto falsa] [00:54:57.679] > «sigo con la orden»
15:54:52.959 [Webasto falsa] [00:55:01.859] < pide los sensores (0x50 05)
15:54:53.070 [Webasto falsa] [00:55:01.967] > agua 40 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:54:53.304 [Webasto falsa] [00:55:02.197] = a tope · agua 40.4 °C · dentro 5.7 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:54:53.315 [Webasto falsa] [00:55:02.198] =   orden 0x21: quedan 179 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
15:54:53.326 [Webasto falsa] [00:55:02.209] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:54:53.329 [Webasto falsa] [00:55:02.220] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:54:53.706 [Webasto falsa] [00:55:02.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:53.778 [Webasto falsa] [00:55:02.679] > «sigo con la orden»
15:54:58.707 [Webasto falsa] [00:55:07.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:58.778 [Webasto falsa] [00:55:07.679] > «sigo con la orden»
15:55:01.091 [Webasto falsa] [00:55:09.991] < pide los sensores (0x50 05)
15:55:01.201 [Webasto falsa] [00:55:10.099] > agua 41 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:55:03.707 [Webasto falsa] [00:55:12.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:03.778 [Webasto falsa] [00:55:12.679] > «sigo con la orden»
15:55:08.706 [Webasto falsa] [00:55:17.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:08.778 [Webasto falsa] [00:55:17.679] > «sigo con la orden»
15:55:09.223 [Webasto falsa] [00:55:18.123] < pide los sensores (0x50 05)
15:55:09.333 [Webasto falsa] [00:55:18.231] > agua 41 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:13.706 [Webasto falsa] [00:55:22.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:13.778 [Webasto falsa] [00:55:22.679] > «sigo con la orden»
15:55:17.354 [Webasto falsa] [00:55:26.255] < pide los sensores (0x50 05)
15:55:17.465 [Webasto falsa] [00:55:26.363] > agua 42 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:18.706 [Webasto falsa] [00:55:27.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:18.778 [Webasto falsa] [00:55:27.679] > «sigo con la orden»
15:55:23.706 [Webasto falsa] [00:55:32.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:23.778 [Webasto falsa] [00:55:32.679] > «sigo con la orden»
15:55:25.486 [Webasto falsa] [00:55:34.387] < pide los sensores (0x50 05)
15:55:25.597 [Webasto falsa] [00:55:34.495] > agua 42 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:28.706 [Webasto falsa] [00:55:37.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:28.778 [Webasto falsa] [00:55:37.679] > «sigo con la orden»
15:55:33.618 [Webasto falsa] [00:55:42.519] < pide los sensores (0x50 05)
15:55:33.729 [Webasto falsa] [00:55:42.627] > agua 42 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:33.760 [Webasto falsa] [00:55:42.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:55:33.832 [Webasto falsa] [00:55:42.733] > «sigo con la orden»
15:55:38.760 [Webasto falsa] [00:55:47.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:38.832 [Webasto falsa] [00:55:47.733] > «sigo con la orden»
15:55:41.750 [Webasto falsa] [00:55:50.651] < pide los sensores (0x50 05)
15:55:41.861 [Webasto falsa] [00:55:50.759] > agua 43 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:55:43.760 [Webasto falsa] [00:55:52.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:43.831 [Webasto falsa] [00:55:52.733] > «sigo con la orden»
15:55:48.760 [Webasto falsa] [00:55:57.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:48.832 [Webasto falsa] [00:55:57.733] > «sigo con la orden»
15:55:49.883 [Webasto falsa] [00:55:58.783] < pide los sensores (0x50 05)
15:55:49.993 [Webasto falsa] [00:55:58.891] > agua 43 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:55:53.306 [Webasto falsa] [00:56:02.199] = a tope · agua 43.3 °C · dentro 6.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:55:53.317 [Webasto falsa] [00:56:02.200] =   orden 0x21: quedan 119 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
15:55:53.328 [Webasto falsa] [00:56:02.211] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:55:53.331 [Webasto falsa] [00:56:02.222] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:55:53.760 [Webasto falsa] [00:56:02.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:53.832 [Webasto falsa] [00:56:02.733] > «sigo con la orden»
15:55:58.014 [Webasto falsa] [00:56:06.915] < pide los sensores (0x50 05)
15:55:58.125 [Webasto falsa] [00:56:07.023] > agua 43 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:55:58.760 [Webasto falsa] [00:56:07.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:58.832 [Webasto falsa] [00:56:07.733] > «sigo con la orden»
15:56:03.760 [Webasto falsa] [00:56:12.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:03.831 [Webasto falsa] [00:56:12.733] > «sigo con la orden»
15:56:06.146 [Webasto falsa] [00:56:15.047] < pide los sensores (0x50 05)
15:56:06.257 [Webasto falsa] [00:56:15.155] > agua 44 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:56:08.759 [Webasto falsa] [00:56:17.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:08.831 [Webasto falsa] [00:56:17.733] > «sigo con la orden»
15:56:13.761 [Webasto falsa] [00:56:22.659] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:13.833 [Webasto falsa] [00:56:22.735] > «sigo con la orden»
15:56:14.278 [Webasto falsa] [00:56:23.179] < pide los sensores (0x50 05)
15:56:14.389 [Webasto falsa] [00:56:23.287] > agua 44 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:18.761 [Webasto falsa] [00:56:27.659] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:18.833 [Webasto falsa] [00:56:27.735] > «sigo con la orden»
15:56:22.410 [Webasto falsa] [00:56:31.311] < pide los sensores (0x50 05)
15:56:22.521 [Webasto falsa] [00:56:31.419] > agua 45 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:23.762 [Webasto falsa] [00:56:32.659] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:23.833 [Webasto falsa] [00:56:32.735] > «sigo con la orden»
15:56:28.763 [Webasto falsa] [00:56:37.661] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:28.835 [Webasto falsa] [00:56:37.737] > «sigo con la orden»
15:56:30.542 [Webasto falsa] [00:56:39.443] < pide los sensores (0x50 05)
15:56:30.653 [Webasto falsa] [00:56:39.551] > agua 45 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:33.763 [Webasto falsa] [00:56:42.661] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:33.835 [Webasto falsa] [00:56:42.737] > «sigo con la orden»
15:56:38.674 [Webasto falsa] [00:56:47.575] < pide los sensores (0x50 05)
15:56:38.784 [Webasto falsa] [00:56:47.683] > agua 45 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:38.817 [Webasto falsa] [00:56:47.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:56:38.889 [Webasto falsa] [00:56:47.791] > «sigo con la orden»
15:56:43.817 [Webasto falsa] [00:56:52.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:43.889 [Webasto falsa] [00:56:52.791] > «sigo con la orden»
15:56:46.806 [Webasto falsa] [00:56:55.707] < pide los sensores (0x50 05)
15:56:46.916 [Webasto falsa] [00:56:55.815] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:48.817 [Webasto falsa] [00:56:57.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:48.889 [Webasto falsa] [00:56:57.791] > «sigo con la orden»
15:56:53.307 [Webasto falsa] [00:57:02.201] = a tope · agua 46.0 °C · dentro 6.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:56:53.318 [Webasto falsa] [00:57:02.202] =   orden 0x21: quedan 59 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
15:56:53.324 [Webasto falsa] [00:57:02.213] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:56:53.332 [Webasto falsa] [00:57:02.224] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:56:53.817 [Webasto falsa] [00:57:02.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:53.889 [Webasto falsa] [00:57:02.791] > «sigo con la orden»
15:56:54.938 [Webasto falsa] [00:57:03.839] < pide los sensores (0x50 05)
15:56:55.048 [Webasto falsa] [00:57:03.947] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:56:58.817 [Webasto falsa] [00:57:07.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:58.889 [Webasto falsa] [00:57:07.791] > «sigo con la orden»
15:57:03.069 [Webasto falsa] [00:57:11.971] < pide los sensores (0x50 05)
15:57:03.180 [Webasto falsa] [00:57:12.079] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:03.817 [Webasto falsa] [00:57:12.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:03.889 [Webasto falsa] [00:57:12.791] > «sigo con la orden»
15:57:08.817 [Webasto falsa] [00:57:17.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:08.889 [Webasto falsa] [00:57:17.791] > «sigo con la orden»
15:57:11.201 [Webasto falsa] [00:57:20.103] < pide los sensores (0x50 05)
15:57:11.312 [Webasto falsa] [00:57:20.211] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:13.817 [Webasto falsa] [00:57:22.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:13.889 [Webasto falsa] [00:57:22.791] > «sigo con la orden»
15:57:18.817 [Webasto falsa] [00:57:27.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:18.888 [Webasto falsa] [00:57:27.791] > «sigo con la orden»
15:57:19.333 [Webasto falsa] [00:57:28.235] < pide los sensores (0x50 05)
15:57:19.444 [Webasto falsa] [00:57:28.343] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:23.816 [Webasto falsa] [00:57:32.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:23.888 [Webasto falsa] [00:57:32.791] > «sigo con la orden»
15:57:27.465 [Webasto falsa] [00:57:36.367] < pide los sensores (0x50 05)
15:57:27.576 [Webasto falsa] [00:57:36.475] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:28.817 [Webasto falsa] [00:57:37.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:28.888 [Webasto falsa] [00:57:37.791] > «sigo con la orden»
15:57:33.817 [Webasto falsa] [00:57:42.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:33.888 [Webasto falsa] [00:57:42.791] > «sigo con la orden»
15:57:35.597 [Webasto falsa] [00:57:44.499] < pide los sensores (0x50 05)
15:57:35.708 [Webasto falsa] [00:57:44.607] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
15:57:38.817 [Webasto falsa] [00:57:47.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:38.888 [Webasto falsa] [00:57:47.791] > «sigo con la orden»
15:57:43.729 [Webasto falsa] [00:57:52.631] < pide los sensores (0x50 05)
15:57:43.840 [Webasto falsa] [00:57:52.739] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
15:57:43.873 [Webasto falsa] [00:57:52.771] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:57:43.944 [Webasto falsa] [00:57:52.847] > «sigo con la orden»
15:57:48.873 [Webasto falsa] [00:57:57.771] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:48.944 [Webasto falsa] [00:57:57.847] > «sigo con la orden»
15:57:51.861 [Webasto falsa] [00:58:00.763] < pide los sensores (0x50 05)
15:57:51.972 [Webasto falsa] [00:58:00.871] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
15:57:53.275 [Webasto falsa] [00:58:02.175] . estado: a tope → postbarrido (se acabó el tiempo pedido)
15:57:53.308 [Webasto falsa] [00:58:02.203] = postbarrido · agua 48.8 °C · dentro 6.5 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
15:57:53.319 [Webasto falsa] [00:58:02.204] =   orden 0x21: quedan 0 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
15:57:53.325 [Webasto falsa] [00:58:02.215] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:57:53.333 [Webasto falsa] [00:58:02.226] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:57:53.380 [Webasto falsa] [00:58:02.281] < apagar
15:57:53.382 [Webasto falsa] [00:58:02.282] . apagar: ya estaba postbarrido, no cambia nada
15:57:53.446 [Webasto falsa] [00:58:02.348] > «apagando» → postbarrido
15:57:53.559 [WTTC] 17/10 15:57  Apagada (fin de tiempo) · gasoil ≈ 0,14 l
15:58:52.988 [Webasto falsa] [00:59:01.890] < pide los sensores (0x50 05)
15:58:53.098 [Webasto falsa] [00:59:01.998] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:58:53.308 [Webasto falsa] [00:59:02.204] = postbarrido · agua 48.3 °C · dentro 6.5 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
15:58:53.319 [Webasto falsa] [00:59:02.205] =   orden 0x21: quedan 0 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
15:58:53.325 [Webasto falsa] [00:59:02.216] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:58:53.333 [Webasto falsa] [00:59:02.227] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:59:01.121 [Webasto falsa] [00:59:10.024] < pide los sensores (0x50 05)
15:59:01.231 [Webasto falsa] [00:59:10.132] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:12.225 [Webasto falsa] [00:59:21.128] < pide los sensores (0x50 05)
15:59:12.335 [Webasto falsa] [00:59:21.236] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:20.357 [Webasto falsa] [00:59:29.260] < pide los sensores (0x50 05)
15:59:20.467 [Webasto falsa] [00:59:29.368] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:28.488 [Webasto falsa] [00:59:37.392] < pide los sensores (0x50 05)
15:59:28.599 [Webasto falsa] [00:59:37.500] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:30.702 [WTTC] 17/10 15:59  Salida programada: 18/10 08:00
15:59:36.620 [Webasto falsa] [00:59:45.524] < pide los sensores (0x50 05)
15:59:36.730 [Webasto falsa] [00:59:45.632] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:44.752 [Webasto falsa] [00:59:53.656] < pide los sensores (0x50 05)
15:59:44.863 [Webasto falsa] [00:59:53.764] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:52.884 [Webasto falsa] [01:00:01.788] < pide los sensores (0x50 05)
15:59:52.995 [Webasto falsa] [01:00:01.896] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:53.276 [Webasto falsa] [01:00:02.176] . estado: postbarrido → apagada (fin del postbarrido; suelta la orden)
16:00:01.040 [Webasto falsa] [01:00:09.944] < pide los sensores (0x50 05)
16:00:01.150 [Webasto falsa] [01:00:10.052] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.39 Ω
16:00:09.172 [Webasto falsa] [01:00:18.076] < pide los sensores (0x50 05)
16:00:09.283 [Webasto falsa] [01:00:18.184] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.39 Ω
16:00:17.304 [Webasto falsa] [01:00:26.208] < pide los sensores (0x50 05)
16:00:17.415 [Webasto falsa] [01:00:26.316] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:25.436 [Webasto falsa] [01:00:34.340] < pide los sensores (0x50 05)
16:00:25.547 [Webasto falsa] [01:00:34.448] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:33.568 [Webasto falsa] [01:00:42.472] < pide los sensores (0x50 05)
16:00:33.678 [Webasto falsa] [01:00:42.580] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:41.700 [Webasto falsa] [01:00:50.604] < pide los sensores (0x50 05)
16:00:41.810 [Webasto falsa] [01:00:50.712] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:49.832 [Webasto falsa] [01:00:58.736] < pide los sensores (0x50 05)
16:00:49.942 [Webasto falsa] [01:00:58.844] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:57.964 [Webasto falsa] [01:01:06.868] < pide los sensores (0x50 05)
16:00:58.074 [Webasto falsa] [01:01:06.976] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:06.096 [Webasto falsa] [01:01:15.000] < pide los sensores (0x50 05)
16:01:06.206 [Webasto falsa] [01:01:15.108] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:08.238 [WTTC] 17/10 16:01  Programas guardados (1)
16:01:14.227 [Webasto falsa] [01:01:23.132] < pide los sensores (0x50 05)
16:01:14.338 [Webasto falsa] [01:01:23.240] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:22.359 [Webasto falsa] [01:01:31.264] < pide los sensores (0x50 05)
16:01:22.470 [Webasto falsa] [01:01:31.372] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:30.491 [Webasto falsa] [01:01:39.396] < pide los sensores (0x50 05)
16:01:30.601 [Webasto falsa] [01:01:39.504] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:38.623 [Webasto falsa] [01:01:47.528] < pide los sensores (0x50 05)
16:01:38.733 [Webasto falsa] [01:01:47.636] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:46.755 [Webasto falsa] [01:01:55.660] < pide los sensores (0x50 05)
16:01:46.865 [Webasto falsa] [01:01:55.768] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:54.887 [Webasto falsa] [01:02:03.792] < pide los sensores (0x50 05)
16:01:54.997 [Webasto falsa] [01:02:03.900] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:03.018 [Webasto falsa] [01:02:11.924] < pide los sensores (0x50 05)
16:02:03.129 [Webasto falsa] [01:02:12.032] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:11.151 [Webasto falsa] [01:02:20.056] < pide los sensores (0x50 05)
16:02:11.261 [Webasto falsa] [01:02:20.164] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:19.283 [Webasto falsa] [01:02:28.188] < pide los sensores (0x50 05)
16:02:19.393 [Webasto falsa] [01:02:28.296] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:27.415 [Webasto falsa] [01:02:36.320] < pide los sensores (0x50 05)
16:02:27.525 [Webasto falsa] [01:02:36.428] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:35.548 [Webasto falsa] [01:02:44.454] < pide los sensores (0x50 05)
16:02:35.659 [Webasto falsa] [01:02:44.562] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:43.680 [Webasto falsa] [01:02:52.586] < pide los sensores (0x50 05)
16:02:43.790 [Webasto falsa] [01:02:52.694] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:51.812 [Webasto falsa] [01:03:00.718] < pide los sensores (0x50 05)
16:02:51.922 [Webasto falsa] [01:03:00.826] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:59.944 [Webasto falsa] [01:03:08.850] < pide los sensores (0x50 05)
16:03:00.054 [Webasto falsa] [01:03:08.958] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:08.076 [Webasto falsa] [01:03:16.982] < pide los sensores (0x50 05)
16:03:08.186 [Webasto falsa] [01:03:17.090] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:16.208 [Webasto falsa] [01:03:25.114] < pide los sensores (0x50 05)
16:03:16.318 [Webasto falsa] [01:03:25.222] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:24.340 [Webasto falsa] [01:03:33.246] < pide los sensores (0x50 05)
16:03:24.450 [Webasto falsa] [01:03:33.354] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:32.472 [Webasto falsa] [01:03:41.378] < pide los sensores (0x50 05)
16:03:32.582 [Webasto falsa] [01:03:41.486] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:40.603 [Webasto falsa] [01:03:49.510] < pide los sensores (0x50 05)
16:03:40.714 [Webasto falsa] [01:03:49.618] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:48.735 [Webasto falsa] [01:03:57.642] < pide los sensores (0x50 05)
16:03:48.846 [Webasto falsa] [01:03:57.750] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:53.306 [Webasto falsa] [01:04:02.206] = apagada · agua 46.2 °C · dentro 6.5 °C · fuera 5.0 °C · 12.60 V · llama no · 0 W
16:03:53.312 [Webasto falsa] [01:04:02.207] =   sin orden de calentar
16:03:53.323 [Webasto falsa] [01:04:02.218] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:03:53.326 [Webasto falsa] [01:04:02.218] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:03:56.866 [Webasto falsa] [01:04:05.773] < pide los sensores (0x50 05)
16:03:56.977 [Webasto falsa] [01:04:05.881] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:04.998 [Webasto falsa] [01:04:13.905] < pide los sensores (0x50 05)
16:04:05.109 [Webasto falsa] [01:04:14.013] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:13.130 [Webasto falsa] [01:04:22.037] < pide los sensores (0x50 05)
16:04:13.241 [Webasto falsa] [01:04:22.145] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:21.262 [Webasto falsa] [01:04:30.169] < pide los sensores (0x50 05)
16:04:21.372 [Webasto falsa] [01:04:30.277] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:24.138 [WTTC] 17/10 16:04  Programas guardados (1)
16:04:24.251 [WTTC] 17/10 16:04  Programas guardados (1)
16:04:24.312 [WTTC] 17/10 16:04  Programas guardados (1)
16:04:24.345 [WTTC] 17/10 16:04  Programas guardados (1)
16:04:29.394 [Webasto falsa] [01:04:38.301] < pide los sensores (0x50 05)
16:04:29.505 [Webasto falsa] [01:04:38.409] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:37.526 [Webasto falsa] [01:04:46.433] < pide los sensores (0x50 05)
16:04:37.637 [Webasto falsa] [01:04:46.541] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:45.658 [Webasto falsa] [01:04:54.565] < pide los sensores (0x50 05)
16:04:45.768 [Webasto falsa] [01:04:54.673] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:53.790 [Webasto falsa] [01:05:02.697] < pide los sensores (0x50 05)
16:04:53.900 [Webasto falsa] [01:05:02.805] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:05:00.076 [Webasto falsa] [01:05:08.983] < pide los sensores (0x50 05)
16:05:00.186 [Webasto falsa] [01:05:09.091] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.37 Ω
16:05:00.209 [Webasto falsa] [01:05:09.115] < encender 30 min
16:05:00.220 [Webasto falsa] [01:05:09.116] . encender 30 min: aceptado (arranque nº 2; mantenimiento cada <20 s o se apaga)
16:05:00.222 [Webasto falsa] [01:05:09.127] . estado: apagada → ventilador (empieza el arranque)
16:05:00.291 [WTTC] 17/10 16:05  Encendida (programa, 30 min)
16:05:00.292 [Webasto falsa] [01:05:09.198] > «vale, 30 min»
16:05:00.303 [Webasto falsa] [01:05:09.199] = ventilador · agua 45.8 °C · dentro 6.5 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
16:05:00.308 [Webasto falsa] [01:05:09.210] =   orden 0x21: quedan 1800 s · último mantenimiento hace 0.0 s (a los 20 s se apaga)
16:05:00.320 [Webasto falsa] [01:05:09.210] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:05:00.325 [Webasto falsa] [01:05:09.221] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:05:05.322 [Webasto falsa] [01:05:14.227] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 436.5 s
16:05:05.394 [Webasto falsa] [01:05:14.302] > «sigo con la orden»
16:05:08.208 [Webasto falsa] [01:05:17.116] < pide los sensores (0x50 05)
16:05:08.319 [Webasto falsa] [01:05:17.224] > agua 46 °C · 12.48 V · llama no · 0 W · bujía 1.37 Ω
16:05:10.322 [Webasto falsa] [01:05:19.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:10.394 [Webasto falsa] [01:05:19.302] > «sigo con la orden»
16:05:12.212 [Webasto falsa] [01:05:21.116] . estado: ventilador → bujía (ventilador listo: calienta la bujía)
16:05:15.322 [Webasto falsa] [01:05:24.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:15.394 [Webasto falsa] [01:05:24.302] > «sigo con la orden»
16:05:16.341 [Webasto falsa] [01:05:25.248] < pide los sensores (0x50 05)
16:05:16.451 [Webasto falsa] [01:05:25.356] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:20.322 [Webasto falsa] [01:05:29.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:20.394 [Webasto falsa] [01:05:29.302] > «sigo con la orden»
16:05:24.472 [Webasto falsa] [01:05:33.380] < pide los sensores (0x50 05)
16:05:24.583 [Webasto falsa] [01:05:33.488] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:25.322 [Webasto falsa] [01:05:34.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:25.394 [Webasto falsa] [01:05:34.302] > «sigo con la orden»
16:05:30.322 [Webasto falsa] [01:05:39.227] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:30.394 [Webasto falsa] [01:05:39.302] > «sigo con la orden»
16:05:32.604 [Webasto falsa] [01:05:41.512] < pide los sensores (0x50 05)
16:05:32.715 [Webasto falsa] [01:05:41.620] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:35.322 [Webasto falsa] [01:05:44.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:35.394 [Webasto falsa] [01:05:44.302] > «sigo con la orden»
16:05:40.322 [Webasto falsa] [01:05:49.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:40.394 [Webasto falsa] [01:05:49.302] > «sigo con la orden»
16:05:40.736 [Webasto falsa] [01:05:49.644] < pide los sensores (0x50 05)
16:05:40.847 [Webasto falsa] [01:05:49.752] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:45.322 [Webasto falsa] [01:05:54.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:45.393 [Webasto falsa] [01:05:54.302] > «sigo con la orden»
16:05:48.868 [Webasto falsa] [01:05:57.776] < pide los sensores (0x50 05)
16:05:48.979 [Webasto falsa] [01:05:57.884] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:50.321 [Webasto falsa] [01:05:59.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:50.394 [Webasto falsa] [01:05:59.302] > «sigo con la orden»
16:05:55.322 [Webasto falsa] [01:06:04.227] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:55.393 [Webasto falsa] [01:06:04.302] > «sigo con la orden»
16:05:57.000 [Webasto falsa] [01:06:05.908] < pide los sensores (0x50 05)
16:05:57.110 [Webasto falsa] [01:06:06.016] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:57.212 [Webasto falsa] [01:06:06.116] . estado: bujía → encendido (bujía caliente: bomba de gasoil en marcha)
16:06:00.215 [Webasto falsa] [01:06:09.116] = encendido · agua 44.5 °C · dentro 6.8 °C · fuera 5.0 °C · 12.06 V · llama no · 0 W
16:06:00.226 [Webasto falsa] [01:06:09.117] =   orden 0x21: quedan 1739 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
16:06:00.237 [Webasto falsa] [01:06:09.128] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:06:00.240 [Webasto falsa] [01:06:09.139] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:06:00.322 [Webasto falsa] [01:06:09.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:00.393 [Webasto falsa] [01:06:09.302] > «sigo con la orden»
16:06:05.132 [Webasto falsa] [01:06:14.040] < pide los sensores (0x50 05)
16:06:05.242 [Webasto falsa] [01:06:14.148] > agua 44 °C · 12.06 V · llama no · 0 W · bujía 1.37 Ω
16:06:05.322 [Webasto falsa] [01:06:14.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:05.393 [Webasto falsa] [01:06:14.302] > «sigo con la orden»
16:06:10.323 [Webasto falsa] [01:06:19.229] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:10.395 [Webasto falsa] [01:06:19.304] > «sigo con la orden»
16:06:13.264 [Webasto falsa] [01:06:22.172] < pide los sensores (0x50 05)
16:06:13.374 [Webasto falsa] [01:06:22.280] > agua 44 °C · 12.06 V · llama no · 0 W · bujía 1.37 Ω
16:06:15.323 [Webasto falsa] [01:06:24.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:15.395 [Webasto falsa] [01:06:24.304] > «sigo con la orden»
16:06:20.323 [Webasto falsa] [01:06:29.229] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:20.395 [Webasto falsa] [01:06:29.304] > «sigo con la orden»
16:06:21.398 [Webasto falsa] [01:06:30.306] < pide los sensores (0x50 05)
16:06:21.508 [Webasto falsa] [01:06:30.414] > agua 44 °C · 12.06 V · llama no · 0 W · bujía 1.36 Ω
16:06:25.323 [Webasto falsa] [01:06:34.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:25.395 [Webasto falsa] [01:06:34.304] > «sigo con la orden»
16:06:27.209 [Webasto falsa] [01:06:36.116] . estado: encendido → estabilizando (¡llama!)
16:06:29.531 [Webasto falsa] [01:06:38.440] < pide los sensores (0x50 05)
16:06:29.643 [Webasto falsa] [01:06:38.548] > agua 44 °C · 12.36 V · llama sí · 2596 W · bujía 1.36 Ω
16:06:30.323 [Webasto falsa] [01:06:39.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:30.395 [Webasto falsa] [01:06:39.304] > «sigo con la orden»
16:06:35.323 [Webasto falsa] [01:06:44.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:35.395 [Webasto falsa] [01:06:44.304] > «sigo con la orden»
16:06:37.666 [Webasto falsa] [01:06:46.574] < pide los sensores (0x50 05)
16:06:37.776 [Webasto falsa] [01:06:46.682] > agua 44 °C · 12.36 V · llama sí · 2935 W · bujía 1.37 Ω
16:06:40.323 [Webasto falsa] [01:06:49.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:40.395 [Webasto falsa] [01:06:49.304] > «sigo con la orden»
16:06:45.323 [Webasto falsa] [01:06:54.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:45.395 [Webasto falsa] [01:06:54.304] > «sigo con la orden»
16:06:45.798 [Webasto falsa] [01:06:54.706] < pide los sensores (0x50 05)
16:06:45.908 [Webasto falsa] [01:06:54.814] > agua 44 °C · 12.36 V · llama sí · 3274 W · bujía 1.37 Ω
16:06:50.323 [Webasto falsa] [01:06:59.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:50.395 [Webasto falsa] [01:06:59.304] > «sigo con la orden»
16:06:53.929 [Webasto falsa] [01:07:02.838] < pide los sensores (0x50 05)
16:06:54.040 [Webasto falsa] [01:07:02.946] > agua 45 °C · 12.36 V · llama sí · 3613 W · bujía 1.37 Ω
16:06:55.323 [Webasto falsa] [01:07:04.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:55.394 [Webasto falsa] [01:07:04.304] > «sigo con la orden»
16:07:00.216 [Webasto falsa] [01:07:09.118] = estabilizando · agua 44.8 °C · dentro 7.1 °C · fuera 5.0 °C · 12.36 V · llama sí · 3875 W
16:07:00.227 [Webasto falsa] [01:07:09.119] =   orden 0x21: quedan 1679 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
16:07:00.239 [Webasto falsa] [01:07:09.130] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:07:00.242 [Webasto falsa] [01:07:09.141] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:07:00.323 [Webasto falsa] [01:07:09.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:00.395 [Webasto falsa] [01:07:09.304] > «sigo con la orden»
16:07:02.061 [Webasto falsa] [01:07:10.970] < pide los sensores (0x50 05)
16:07:02.172 [Webasto falsa] [01:07:11.078] > agua 45 °C · 12.36 V · llama sí · 3952 W · bujía 1.37 Ω
16:07:05.323 [Webasto falsa] [01:07:14.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:05.395 [Webasto falsa] [01:07:14.304] > «sigo con la orden»
16:07:10.193 [Webasto falsa] [01:07:19.102] < pide los sensores (0x50 05)
16:07:10.304 [Webasto falsa] [01:07:19.210] > agua 45 °C · 12.36 V · llama sí · 4291 W · bujía 1.37 Ω
16:07:10.335 [Webasto falsa] [01:07:19.240] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:10.407 [Webasto falsa] [01:07:19.316] > «sigo con la orden»
16:07:15.334 [Webasto falsa] [01:07:24.240] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:15.407 [Webasto falsa] [01:07:24.316] > «sigo con la orden»
16:07:18.325 [Webasto falsa] [01:07:27.234] < pide los sensores (0x50 05)
16:07:18.436 [Webasto falsa] [01:07:27.342] > agua 45 °C · 12.36 V · llama sí · 4629 W · bujía 1.37 Ω
16:07:20.335 [Webasto falsa] [01:07:29.240] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:20.406 [Webasto falsa] [01:07:29.316] > «sigo con la orden»
16:07:25.337 [Webasto falsa] [01:07:34.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:25.408 [Webasto falsa] [01:07:34.318] > «sigo con la orden»
16:07:26.457 [Webasto falsa] [01:07:35.366] < pide los sensores (0x50 05)
16:07:26.568 [Webasto falsa] [01:07:35.474] > agua 46 °C · 12.36 V · llama sí · 4968 W · bujía 1.37 Ω
16:07:27.209 [Webasto falsa] [01:07:36.116] . estado: estabilizando → a tope (llama estable)
16:07:30.337 [Webasto falsa] [01:07:39.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:30.408 [Webasto falsa] [01:07:39.318] > «sigo con la orden»
16:07:34.589 [Webasto falsa] [01:07:43.498] < pide los sensores (0x50 05)
16:07:34.699 [Webasto falsa] [01:07:43.606] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:07:35.336 [Webasto falsa] [01:07:44.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:35.408 [Webasto falsa] [01:07:44.318] > «sigo con la orden»
16:07:40.337 [Webasto falsa] [01:07:49.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:40.408 [Webasto falsa] [01:07:49.318] > «sigo con la orden»
16:07:42.721 [Webasto falsa] [01:07:51.630] < pide los sensores (0x50 05)
16:07:42.832 [Webasto falsa] [01:07:51.738] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:07:45.337 [Webasto falsa] [01:07:54.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:45.408 [Webasto falsa] [01:07:54.318] > «sigo con la orden»
16:07:50.336 [Webasto falsa] [01:07:59.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:50.408 [Webasto falsa] [01:07:59.318] > «sigo con la orden»
16:07:50.853 [Webasto falsa] [01:07:59.762] < pide los sensores (0x50 05)
16:07:50.964 [Webasto falsa] [01:07:59.870] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:07:55.336 [Webasto falsa] [01:08:04.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:55.408 [Webasto falsa] [01:08:04.318] > «sigo con la orden»
16:07:58.985 [Webasto falsa] [01:08:07.894] < pide los sensores (0x50 05)
16:07:59.095 [Webasto falsa] [01:08:08.002] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:08:00.218 [Webasto falsa] [01:08:09.120] = a tope · agua 47.3 °C · dentro 7.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:08:00.229 [Webasto falsa] [01:08:09.121] =   orden 0x21: quedan 1619 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
16:08:00.240 [Webasto falsa] [01:08:09.132] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:08:00.242 [Webasto falsa] [01:08:09.143] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:08:00.336 [Webasto falsa] [01:08:09.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:00.408 [Webasto falsa] [01:08:09.318] > «sigo con la orden»
16:08:05.336 [Webasto falsa] [01:08:14.243] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:05.408 [Webasto falsa] [01:08:14.318] > «sigo con la orden»
16:08:07.116 [Webasto falsa] [01:08:16.026] < pide los sensores (0x50 05)
16:08:07.227 [Webasto falsa] [01:08:16.134] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:08:10.336 [Webasto falsa] [01:08:19.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:10.408 [Webasto falsa] [01:08:19.318] > «sigo con la orden»
16:08:15.248 [Webasto falsa] [01:08:24.158] < pide los sensores (0x50 05)
16:08:15.359 [Webasto falsa] [01:08:24.266] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:15.392 [Webasto falsa] [01:08:24.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:08:15.463 [Webasto falsa] [01:08:24.374] > «sigo con la orden»
16:08:20.392 [Webasto falsa] [01:08:29.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:20.464 [Webasto falsa] [01:08:29.374] > «sigo con la orden»
16:08:23.380 [Webasto falsa] [01:08:32.290] < pide los sensores (0x50 05)
16:08:23.491 [Webasto falsa] [01:08:32.398] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:25.392 [Webasto falsa] [01:08:34.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:25.464 [Webasto falsa] [01:08:34.374] > «sigo con la orden»
16:08:30.392 [Webasto falsa] [01:08:39.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:30.464 [Webasto falsa] [01:08:39.374] > «sigo con la orden»
16:08:31.512 [Webasto falsa] [01:08:40.422] < pide los sensores (0x50 05)
16:08:31.623 [Webasto falsa] [01:08:40.530] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:35.392 [Webasto falsa] [01:08:44.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:35.464 [Webasto falsa] [01:08:44.374] > «sigo con la orden»
16:08:39.644 [Webasto falsa] [01:08:48.554] < pide los sensores (0x50 05)
16:08:39.755 [Webasto falsa] [01:08:48.662] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:40.395 [Webasto falsa] [01:08:49.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:40.464 [Webasto falsa] [01:08:49.374] > «sigo con la orden»
16:08:45.392 [Webasto falsa] [01:08:54.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:45.463 [Webasto falsa] [01:08:54.374] > «sigo con la orden»
16:08:47.776 [Webasto falsa] [01:08:56.686] < pide los sensores (0x50 05)
16:08:47.887 [Webasto falsa] [01:08:56.794] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:08:50.391 [Webasto falsa] [01:08:59.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:50.464 [Webasto falsa] [01:08:59.374] > «sigo con la orden»
16:08:55.392 [Webasto falsa] [01:09:04.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:55.463 [Webasto falsa] [01:09:04.374] > «sigo con la orden»
16:08:55.908 [Webasto falsa] [01:09:04.818] < pide los sensores (0x50 05)
16:08:56.018 [Webasto falsa] [01:09:04.926] > agua 50 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:00.219 [Webasto falsa] [01:09:09.122] = a tope · agua 50.0 °C · dentro 7.6 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:09:00.230 [Webasto falsa] [01:09:09.123] =   orden 0x21: quedan 1559 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
16:09:00.241 [Webasto falsa] [01:09:09.134] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:09:00.244 [Webasto falsa] [01:09:09.145] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:09:00.393 [Webasto falsa] [01:09:09.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:00.463 [Webasto falsa] [01:09:09.374] > «sigo con la orden»
16:09:04.040 [Webasto falsa] [01:09:12.950] < pide los sensores (0x50 05)
16:09:04.151 [Webasto falsa] [01:09:13.058] > agua 50 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:05.391 [Webasto falsa] [01:09:14.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:05.463 [Webasto falsa] [01:09:14.374] > «sigo con la orden»
16:09:10.391 [Webasto falsa] [01:09:19.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:10.463 [Webasto falsa] [01:09:19.374] > «sigo con la orden»
16:09:12.172 [Webasto falsa] [01:09:21.082] < pide los sensores (0x50 05)
16:09:12.283 [Webasto falsa] [01:09:21.190] > agua 51 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:15.391 [Webasto falsa] [01:09:24.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:15.463 [Webasto falsa] [01:09:24.374] > «sigo con la orden»
16:09:20.303 [Webasto falsa] [01:09:29.214] < pide los sensores (0x50 05)
16:09:20.414 [Webasto falsa] [01:09:29.322] > agua 51 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:20.447 [Webasto falsa] [01:09:29.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:09:20.519 [Webasto falsa] [01:09:29.430] > «sigo con la orden»
16:09:25.447 [Webasto falsa] [01:09:34.355] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:25.519 [Webasto falsa] [01:09:34.430] > «sigo con la orden»
16:09:28.436 [Webasto falsa] [01:09:37.346] < pide los sensores (0x50 05)
16:09:28.546 [Webasto falsa] [01:09:37.454] > agua 51 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:30.447 [Webasto falsa] [01:09:39.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:30.519 [Webasto falsa] [01:09:39.430] > «sigo con la orden»
16:09:35.447 [Webasto falsa] [01:09:44.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:35.519 [Webasto falsa] [01:09:44.430] > «sigo con la orden»
16:09:36.570 [Webasto falsa] [01:09:45.480] < pide los sensores (0x50 05)
16:09:36.680 [Webasto falsa] [01:09:45.588] > agua 52 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:40.447 [Webasto falsa] [01:09:49.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:40.519 [Webasto falsa] [01:09:49.430] > «sigo con la orden»
16:09:44.704 [Webasto falsa] [01:09:53.614] < pide los sensores (0x50 05)
16:09:44.814 [Webasto falsa] [01:09:53.722] > agua 52 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:45.447 [Webasto falsa] [01:09:54.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:45.519 [Webasto falsa] [01:09:54.430] > «sigo con la orden»
16:09:50.447 [Webasto falsa] [01:09:59.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:50.519 [Webasto falsa] [01:09:59.430] > «sigo con la orden»
16:09:52.835 [Webasto falsa] [01:10:01.746] < pide los sensores (0x50 05)
16:09:52.946 [Webasto falsa] [01:10:01.854] > agua 52 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:55.447 [Webasto falsa] [01:10:04.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:55.519 [Webasto falsa] [01:10:04.430] > «sigo con la orden»
16:10:00.220 [Webasto falsa] [01:10:09.124] = a tope · agua 52.6 °C · dentro 7.9 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:10:00.231 [Webasto falsa] [01:10:09.125] =   orden 0x21: quedan 1499 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
16:10:00.242 [Webasto falsa] [01:10:09.136] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:10:00.245 [Webasto falsa] [01:10:09.147] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:10:00.447 [Webasto falsa] [01:10:09.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:00.519 [Webasto falsa] [01:10:09.430] > «sigo con la orden»
16:10:00.967 [Webasto falsa] [01:10:09.878] < pide los sensores (0x50 05)
16:10:01.078 [Webasto falsa] [01:10:09.986] > agua 53 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:10:05.447 [Webasto falsa] [01:10:14.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:05.519 [Webasto falsa] [01:10:14.430] > «sigo con la orden»
16:10:09.099 [Webasto falsa] [01:10:18.010] < pide los sensores (0x50 05)
16:10:09.210 [Webasto falsa] [01:10:18.118] > agua 53 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:10.447 [Webasto falsa] [01:10:19.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:10.518 [Webasto falsa] [01:10:19.430] > «sigo con la orden»
16:10:15.447 [Webasto falsa] [01:10:24.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:15.518 [Webasto falsa] [01:10:24.430] > «sigo con la orden»
16:10:17.231 [Webasto falsa] [01:10:26.142] < pide los sensores (0x50 05)
16:10:17.342 [Webasto falsa] [01:10:26.250] > agua 53 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:20.449 [Webasto falsa] [01:10:29.356] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:20.520 [Webasto falsa] [01:10:29.432] > «sigo con la orden»
16:10:25.363 [Webasto falsa] [01:10:34.274] < pide los sensores (0x50 05)
16:10:25.473 [Webasto falsa] [01:10:34.382] > agua 54 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:25.504 [Webasto falsa] [01:10:34.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:10:25.576 [Webasto falsa] [01:10:34.488] > «sigo con la orden»
16:10:30.505 [Webasto falsa] [01:10:39.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:30.576 [Webasto falsa] [01:10:39.488] > «sigo con la orden»
16:10:33.495 [Webasto falsa] [01:10:42.406] < pide los sensores (0x50 05)
16:10:33.606 [Webasto falsa] [01:10:42.514] > agua 54 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:35.504 [Webasto falsa] [01:10:44.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:35.576 [Webasto falsa] [01:10:44.488] > «sigo con la orden»
16:10:40.505 [Webasto falsa] [01:10:49.413] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:40.576 [Webasto falsa] [01:10:49.488] > «sigo con la orden»
16:10:41.627 [Webasto falsa] [01:10:50.538] < pide los sensores (0x50 05)
16:10:41.738 [Webasto falsa] [01:10:50.646] > agua 54 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:10:45.505 [Webasto falsa] [01:10:54.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:45.576 [Webasto falsa] [01:10:54.488] > «sigo con la orden»
16:10:49.759 [Webasto falsa] [01:10:58.670] < pide los sensores (0x50 05)
16:10:49.869 [Webasto falsa] [01:10:58.778] > agua 55 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:10:50.505 [Webasto falsa] [01:10:59.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:50.576 [Webasto falsa] [01:10:59.488] > «sigo con la orden»
16:10:55.504 [Webasto falsa] [01:11:04.413] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:55.576 [Webasto falsa] [01:11:04.488] > «sigo con la orden»
16:10:57.891 [Webasto falsa] [01:11:06.802] < pide los sensores (0x50 05)
16:10:58.001 [Webasto falsa] [01:11:06.910] > agua 55 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:11:00.222 [Webasto falsa] [01:11:09.126] = a tope · agua 55.1 °C · dentro 8.2 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:11:00.233 [Webasto falsa] [01:11:09.127] =   orden 0x21: quedan 1439 s · último mantenimiento hace 4.7 s (a los 20 s se apaga)
16:11:00.249 [Webasto falsa] [01:11:09.138] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:11:00.252 [Webasto falsa] [01:11:09.149] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:11:00.504 [Webasto falsa] [01:11:09.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:00.576 [Webasto falsa] [01:11:09.488] > «sigo con la orden»
16:11:05.504 [Webasto falsa] [01:11:14.413] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:05.576 [Webasto falsa] [01:11:14.488] > «sigo con la orden»
16:11:06.023 [Webasto falsa] [01:11:14.934] < pide los sensores (0x50 05)
16:11:06.133 [Webasto falsa] [01:11:15.042] > agua 55 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:11:10.506 [Webasto falsa] [01:11:19.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:10.578 [Webasto falsa] [01:11:19.490] > «sigo con la orden»
16:11:14.155 [Webasto falsa] [01:11:23.066] < pide los sensores (0x50 05)
16:11:14.265 [Webasto falsa] [01:11:23.174] > agua 56 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:11:15.506 [Webasto falsa] [01:11:24.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:15.578 [Webasto falsa] [01:11:24.490] > «sigo con la orden»
16:11:20.506 [Webasto falsa] [01:11:29.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:20.577 [Webasto falsa] [01:11:29.490] > «sigo con la orden»
16:11:22.286 [Webasto falsa] [01:11:31.198] < pide los sensores (0x50 05)
16:11:22.397 [Webasto falsa] [01:11:31.306] > agua 56 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:25.506 [Webasto falsa] [01:11:34.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:25.578 [Webasto falsa] [01:11:34.490] > «sigo con la orden»
16:11:30.418 [Webasto falsa] [01:11:39.330] < pide los sensores (0x50 05)
16:11:30.529 [Webasto falsa] [01:11:39.438] > agua 56 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:30.562 [Webasto falsa] [01:11:39.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:11:30.633 [Webasto falsa] [01:11:39.546] > «sigo con la orden»
16:11:35.588 [Webasto falsa] [01:11:44.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:35.659 [Webasto falsa] [01:11:44.572] > «sigo con la orden»
16:11:38.550 [Webasto falsa] [01:11:47.462] < pide los sensores (0x50 05)
16:11:38.661 [Webasto falsa] [01:11:47.570] > agua 57 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:40.588 [Webasto falsa] [01:11:49.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:40.660 [Webasto falsa] [01:11:49.572] > «sigo con la orden»
16:11:45.588 [Webasto falsa] [01:11:54.497] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:45.659 [Webasto falsa] [01:11:54.572] > «sigo con la orden»
16:11:46.682 [Webasto falsa] [01:11:55.594] < pide los sensores (0x50 05)
16:11:46.793 [Webasto falsa] [01:11:55.702] > agua 57 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:50.588 [Webasto falsa] [01:11:59.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:50.659 [Webasto falsa] [01:11:59.572] > «sigo con la orden»
16:11:54.814 [Webasto falsa] [01:12:03.726] < pide los sensores (0x50 05)
16:11:54.925 [Webasto falsa] [01:12:03.834] > agua 57 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:55.588 [Webasto falsa] [01:12:04.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:55.659 [Webasto falsa] [01:12:04.572] > «sigo con la orden»
16:12:00.223 [Webasto falsa] [01:12:09.128] = a tope · agua 57.5 °C · dentro 8.6 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:12:00.234 [Webasto falsa] [01:12:09.129] =   orden 0x21: quedan 1379 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
16:12:00.245 [Webasto falsa] [01:12:09.140] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:12:00.248 [Webasto falsa] [01:12:09.151] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:12:00.588 [Webasto falsa] [01:12:09.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:00.659 [Webasto falsa] [01:12:09.572] > «sigo con la orden»
16:12:02.948 [Webasto falsa] [01:12:11.860] < pide los sensores (0x50 05)
16:12:03.059 [Webasto falsa] [01:12:11.968] > agua 58 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:05.587 [Webasto falsa] [01:12:14.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:05.659 [Webasto falsa] [01:12:14.572] > «sigo con la orden»
16:12:10.587 [Webasto falsa] [01:12:19.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:10.659 [Webasto falsa] [01:12:19.572] > «sigo con la orden»
16:12:11.080 [Webasto falsa] [01:12:19.992] < pide los sensores (0x50 05)
16:12:11.191 [Webasto falsa] [01:12:20.100] > agua 58 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:15.587 [Webasto falsa] [01:12:24.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:15.659 [Webasto falsa] [01:12:24.572] > «sigo con la orden»
16:12:19.212 [Webasto falsa] [01:12:28.124] < pide los sensores (0x50 05)
16:12:19.322 [Webasto falsa] [01:12:28.232] > agua 58 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:20.589 [Webasto falsa] [01:12:29.498] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:20.661 [Webasto falsa] [01:12:29.574] > «sigo con la orden»
16:12:25.589 [Webasto falsa] [01:12:34.498] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:25.661 [Webasto falsa] [01:12:34.574] > «sigo con la orden»
16:12:27.343 [Webasto falsa] [01:12:36.256] < pide los sensores (0x50 05)
16:12:27.455 [Webasto falsa] [01:12:36.364] > agua 59 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:30.589 [Webasto falsa] [01:12:39.498] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:30.661 [Webasto falsa] [01:12:39.574] > «sigo con la orden»
16:12:35.475 [Webasto falsa] [01:12:44.388] < pide los sensores (0x50 05)
16:12:35.586 [Webasto falsa] [01:12:44.496] > agua 59 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:35.619 [Webasto falsa] [01:12:44.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:35.691 [Webasto falsa] [01:12:44.604] > «sigo con la orden»
16:12:38.694 [WTTC] 17/10 16:12  Programas guardados (4)
16:12:40.619 [Webasto falsa] [01:12:49.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:40.691 [Webasto falsa] [01:12:49.604] > «sigo con la orden»
16:12:43.607 [Webasto falsa] [01:12:52.520] < pide los sensores (0x50 05)
16:12:43.718 [Webasto falsa] [01:12:52.628] > agua 59 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:12:45.339 [WTTC] 17/10 16:12  Programas guardados (4)
16:12:45.619 [Webasto falsa] [01:12:54.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:45.691 [Webasto falsa] [01:12:54.604] > «sigo con la orden»
16:12:50.619 [Webasto falsa] [01:12:59.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:50.691 [Webasto falsa] [01:12:59.604] > «sigo con la orden»
16:12:51.739 [Webasto falsa] [01:13:00.652] < pide los sensores (0x50 05)
16:12:51.850 [Webasto falsa] [01:13:00.760] > agua 60 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:12:55.619 [Webasto falsa] [01:13:04.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:55.691 [Webasto falsa] [01:13:04.604] > «sigo con la orden»
16:12:59.871 [Webasto falsa] [01:13:08.784] < pide los sensores (0x50 05)
16:12:59.982 [Webasto falsa] [01:13:08.892] > agua 60 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:13:00.224 [Webasto falsa] [01:13:09.130] = a tope · agua 59.9 °C · dentro 8.9 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:13:00.235 [Webasto falsa] [01:13:09.131] =   orden 0x21: quedan 1319 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
16:13:00.246 [Webasto falsa] [01:13:09.142] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:13:00.250 [Webasto falsa] [01:13:09.153] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:13:00.619 [Webasto falsa] [01:13:09.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:00.691 [Webasto falsa] [01:13:09.604] > «sigo con la orden»
16:13:05.619 [Webasto falsa] [01:13:14.529] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:05.691 [Webasto falsa] [01:13:14.604] > «sigo con la orden»
16:13:08.003 [Webasto falsa] [01:13:16.916] < pide los sensores (0x50 05)
16:13:08.114 [Webasto falsa] [01:13:17.024] > agua 60 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:13:10.619 [Webasto falsa] [01:13:19.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:10.691 [Webasto falsa] [01:13:19.604] > «sigo con la orden»
16:13:15.619 [Webasto falsa] [01:13:24.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:15.690 [Webasto falsa] [01:13:24.604] > «sigo con la orden»
16:13:16.135 [Webasto falsa] [01:13:25.048] < pide los sensores (0x50 05)
16:13:16.246 [Webasto falsa] [01:13:25.156] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:13:20.619 [Webasto falsa] [01:13:29.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:20.690 [Webasto falsa] [01:13:29.604] > «sigo con la orden»
16:13:24.267 [Webasto falsa] [01:13:33.180] < pide los sensores (0x50 05)
16:13:24.378 [Webasto falsa] [01:13:33.288] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:25.619 [Webasto falsa] [01:13:34.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:25.690 [Webasto falsa] [01:13:34.604] > «sigo con la orden»
16:13:30.618 [Webasto falsa] [01:13:39.529] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:30.690 [Webasto falsa] [01:13:39.604] > «sigo con la orden»
16:13:32.399 [Webasto falsa] [01:13:41.312] < pide los sensores (0x50 05)
16:13:32.510 [Webasto falsa] [01:13:41.420] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:35.619 [Webasto falsa] [01:13:44.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:35.690 [Webasto falsa] [01:13:44.604] > «sigo con la orden»
16:13:40.531 [Webasto falsa] [01:13:49.444] < pide los sensores (0x50 05)
16:13:40.642 [Webasto falsa] [01:13:49.552] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:40.674 [Webasto falsa] [01:13:49.584] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:13:40.746 [Webasto falsa] [01:13:49.660] > «sigo con la orden»
16:13:45.675 [Webasto falsa] [01:13:54.585] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:45.746 [Webasto falsa] [01:13:54.660] > «sigo con la orden»
16:13:48.663 [Webasto falsa] [01:13:57.576] < pide los sensores (0x50 05)
16:13:48.773 [Webasto falsa] [01:13:57.684] > agua 62 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:50.674 [Webasto falsa] [01:13:59.584] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:50.746 [Webasto falsa] [01:13:59.660] > «sigo con la orden»
16:13:55.692 [Webasto falsa] [01:14:04.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:55.764 [Webasto falsa] [01:14:04.678] > «sigo con la orden»
16:13:56.795 [Webasto falsa] [01:14:05.708] < pide los sensores (0x50 05)
16:13:56.905 [Webasto falsa] [01:14:05.816] > agua 62 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:14:00.226 [Webasto falsa] [01:14:09.132] = a tope · agua 62.2 °C · dentro 9.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:14:00.237 [Webasto falsa] [01:14:09.133] =   orden 0x21: quedan 1259 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
16:14:00.248 [Webasto falsa] [01:14:09.144] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:14:00.250 [Webasto falsa] [01:14:09.155] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:14:00.692 [Webasto falsa] [01:14:09.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:00.764 [Webasto falsa] [01:14:09.678] > «sigo con la orden»
16:14:04.927 [Webasto falsa] [01:14:13.840] < pide los sensores (0x50 05)
16:14:05.037 [Webasto falsa] [01:14:13.948] > agua 62 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:14:05.692 [Webasto falsa] [01:14:14.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:05.764 [Webasto falsa] [01:14:14.678] > «sigo con la orden»
16:14:10.692 [Webasto falsa] [01:14:19.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:10.764 [Webasto falsa] [01:14:19.678] > «sigo con la orden»
16:14:13.058 [Webasto falsa] [01:14:21.972] < pide los sensores (0x50 05)
16:14:13.169 [Webasto falsa] [01:14:22.080] > agua 63 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:15.692 [Webasto falsa] [01:14:24.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:15.764 [Webasto falsa] [01:14:24.678] > «sigo con la orden»
16:14:20.692 [Webasto falsa] [01:14:29.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:20.764 [Webasto falsa] [01:14:29.678] > «sigo con la orden»
16:14:21.190 [Webasto falsa] [01:14:30.104] < pide los sensores (0x50 05)
16:14:21.301 [Webasto falsa] [01:14:30.212] > agua 63 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:25.601 [WTTC] 17/10 16:14  Programas guardados (5)
16:14:25.692 [Webasto falsa] [01:14:34.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:25.764 [Webasto falsa] [01:14:34.678] > «sigo con la orden»
16:14:29.322 [Webasto falsa] [01:14:38.236] < pide los sensores (0x50 05)
16:14:29.433 [Webasto falsa] [01:14:38.344] > agua 63 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:30.692 [Webasto falsa] [01:14:39.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:30.764 [Webasto falsa] [01:14:39.678] > «sigo con la orden»
16:14:35.692 [Webasto falsa] [01:14:44.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:35.764 [Webasto falsa] [01:14:44.678] > «sigo con la orden»
16:14:37.454 [Webasto falsa] [01:14:46.368] < pide los sensores (0x50 05)
16:14:37.565 [Webasto falsa] [01:14:46.476] > agua 64 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:40.692 [Webasto falsa] [01:14:49.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:40.763 [Webasto falsa] [01:14:49.678] > «sigo con la orden»
16:14:45.586 [Webasto falsa] [01:14:54.500] < pide los sensores (0x50 05)
16:14:45.697 [Webasto falsa] [01:14:54.608] > agua 64 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:45.728 [Webasto falsa] [01:14:54.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:45.799 [Webasto falsa] [01:14:54.714] > «sigo con la orden»
16:14:50.727 [Webasto falsa] [01:14:59.639] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:50.800 [Webasto falsa] [01:14:59.714] > «sigo con la orden»
16:14:53.718 [Webasto falsa] [01:15:02.632] < pide los sensores (0x50 05)
16:14:53.829 [Webasto falsa] [01:15:02.740] > agua 64 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:14:55.728 [Webasto falsa] [01:15:04.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:55.799 [Webasto falsa] [01:15:04.714] > «sigo con la orden»
16:15:00.227 [Webasto falsa] [01:15:09.134] = a tope · agua 64.5 °C · dentro 9.7 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:15:00.238 [Webasto falsa] [01:15:09.135] =   orden 0x21: quedan 1199 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
16:15:00.249 [Webasto falsa] [01:15:09.146] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:15:00.252 [Webasto falsa] [01:15:09.157] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:15:00.729 [Webasto falsa] [01:15:09.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:00.801 [Webasto falsa] [01:15:09.716] > «sigo con la orden»
16:15:01.850 [Webasto falsa] [01:15:10.764] < pide los sensores (0x50 05)
16:15:01.960 [Webasto falsa] [01:15:10.872] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:05.729 [Webasto falsa] [01:15:14.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:05.801 [Webasto falsa] [01:15:14.716] > «sigo con la orden»
16:15:09.982 [Webasto falsa] [01:15:18.896] < pide los sensores (0x50 05)
16:15:10.093 [Webasto falsa] [01:15:19.004] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:10.729 [Webasto falsa] [01:15:19.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:10.801 [Webasto falsa] [01:15:19.716] > «sigo con la orden»
16:15:15.729 [Webasto falsa] [01:15:24.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:15.801 [Webasto falsa] [01:15:24.716] > «sigo con la orden»
16:15:18.114 [Webasto falsa] [01:15:27.028] < pide los sensores (0x50 05)
16:15:18.225 [Webasto falsa] [01:15:27.136] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:20.730 [Webasto falsa] [01:15:29.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:20.801 [Webasto falsa] [01:15:29.716] > «sigo con la orden»
16:15:25.729 [Webasto falsa] [01:15:34.641] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:25.801 [Webasto falsa] [01:15:34.716] > «sigo con la orden»
16:15:26.245 [Webasto falsa] [01:15:35.160] < pide los sensores (0x50 05)
16:15:26.356 [Webasto falsa] [01:15:35.268] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:30.729 [Webasto falsa] [01:15:39.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:30.801 [Webasto falsa] [01:15:39.716] > «sigo con la orden»
16:15:34.378 [Webasto falsa] [01:15:43.292] < pide los sensores (0x50 05)
16:15:34.488 [Webasto falsa] [01:15:43.400] > agua 66 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:35.729 [Webasto falsa] [01:15:44.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:35.801 [Webasto falsa] [01:15:44.716] > «sigo con la orden»
16:15:40.729 [Webasto falsa] [01:15:49.641] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:40.801 [Webasto falsa] [01:15:49.716] > «sigo con la orden»
16:15:42.511 [Webasto falsa] [01:15:51.424] < pide los sensores (0x50 05)
16:15:42.620 [Webasto falsa] [01:15:51.532] > agua 66 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:15:45.729 [Webasto falsa] [01:15:54.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:45.801 [Webasto falsa] [01:15:54.716] > «sigo con la orden»
16:15:50.642 [Webasto falsa] [01:15:59.556] < pide los sensores (0x50 05)
16:15:50.752 [Webasto falsa] [01:15:59.664] > agua 66 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:15:50.785 [Webasto falsa] [01:15:59.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:15:50.857 [Webasto falsa] [01:15:59.772] > «sigo con la orden»
16:15:55.785 [Webasto falsa] [01:16:04.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:55.857 [Webasto falsa] [01:16:04.772] > «sigo con la orden»
16:15:58.773 [Webasto falsa] [01:16:07.688] < pide los sensores (0x50 05)
16:15:58.884 [Webasto falsa] [01:16:07.796] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:00.228 [Webasto falsa] [01:16:09.136] = a tope · agua 66.6 °C · dentro 10.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:16:00.239 [Webasto falsa] [01:16:09.137] =   orden 0x21: quedan 1139 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
16:16:00.250 [Webasto falsa] [01:16:09.148] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:16:00.253 [Webasto falsa] [01:16:09.159] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:16:00.785 [Webasto falsa] [01:16:09.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:00.857 [Webasto falsa] [01:16:09.772] > «sigo con la orden»
16:16:05.785 [Webasto falsa] [01:16:14.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:05.856 [Webasto falsa] [01:16:14.772] > «sigo con la orden»
16:16:06.905 [Webasto falsa] [01:16:15.820] < pide los sensores (0x50 05)
16:16:07.016 [Webasto falsa] [01:16:15.928] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:10.785 [Webasto falsa] [01:16:19.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:10.856 [Webasto falsa] [01:16:19.772] > «sigo con la orden»
16:16:15.037 [Webasto falsa] [01:16:23.952] < pide los sensores (0x50 05)
16:16:15.148 [Webasto falsa] [01:16:24.060] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:15.784 [Webasto falsa] [01:16:24.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:15.857 [Webasto falsa] [01:16:24.772] > «sigo con la orden»
16:16:20.785 [Webasto falsa] [01:16:29.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:20.856 [Webasto falsa] [01:16:29.772] > «sigo con la orden»
16:16:23.169 [Webasto falsa] [01:16:32.084] < pide los sensores (0x50 05)
16:16:23.280 [Webasto falsa] [01:16:32.192] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:25.785 [Webasto falsa] [01:16:34.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:25.856 [Webasto falsa] [01:16:34.772] > «sigo con la orden»
16:16:30.784 [Webasto falsa] [01:16:39.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:30.856 [Webasto falsa] [01:16:39.772] > «sigo con la orden»
16:16:31.301 [Webasto falsa] [01:16:40.216] < pide los sensores (0x50 05)
16:16:31.412 [Webasto falsa] [01:16:40.324] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:35.784 [Webasto falsa] [01:16:44.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:35.856 [Webasto falsa] [01:16:44.772] > «sigo con la orden»
16:16:39.433 [Webasto falsa] [01:16:48.348] < pide los sensores (0x50 05)
16:16:39.544 [Webasto falsa] [01:16:48.456] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:16:40.785 [Webasto falsa] [01:16:49.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:40.856 [Webasto falsa] [01:16:49.772] > «sigo con la orden»
16:16:45.785 [Webasto falsa] [01:16:54.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:45.856 [Webasto falsa] [01:16:54.772] > «sigo con la orden»
16:16:47.565 [Webasto falsa] [01:16:56.480] < pide los sensores (0x50 05)
16:16:47.676 [Webasto falsa] [01:16:56.588] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:16:50.784 [Webasto falsa] [01:16:59.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:50.856 [Webasto falsa] [01:16:59.772] > «sigo con la orden»
16:16:55.697 [Webasto falsa] [01:17:04.612] < pide los sensores (0x50 05)
16:16:55.808 [Webasto falsa] [01:17:04.720] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:16:55.840 [Webasto falsa] [01:17:04.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:16:55.912 [Webasto falsa] [01:17:04.828] > «sigo con la orden»
16:17:00.231 [Webasto falsa] [01:17:09.138] = a tope · agua 68.6 °C · dentro 10.4 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:17:00.241 [Webasto falsa] [01:17:09.139] =   orden 0x21: quedan 1079 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
16:17:00.252 [Webasto falsa] [01:17:09.150] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:17:00.255 [Webasto falsa] [01:17:09.161] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:17:00.840 [Webasto falsa] [01:17:09.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:00.912 [Webasto falsa] [01:17:09.828] > «sigo con la orden»
16:17:03.828 [Webasto falsa] [01:17:12.744] < pide los sensores (0x50 05)
16:17:03.939 [Webasto falsa] [01:17:12.852] > agua 69 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:17:05.840 [Webasto falsa] [01:17:14.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:05.912 [Webasto falsa] [01:17:14.828] > «sigo con la orden»
16:17:10.840 [Webasto falsa] [01:17:19.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:10.912 [Webasto falsa] [01:17:19.828] > «sigo con la orden»
16:17:11.961 [Webasto falsa] [01:17:20.876] < pide los sensores (0x50 05)
16:17:12.071 [Webasto falsa] [01:17:20.984] > agua 69 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:17:15.840 [Webasto falsa] [01:17:24.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:15.912 [Webasto falsa] [01:17:24.828] > «sigo con la orden»
16:17:20.092 [Webasto falsa] [01:17:29.008] < pide los sensores (0x50 05)
16:17:20.203 [Webasto falsa] [01:17:29.116] > agua 69 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:17:20.840 [Webasto falsa] [01:17:29.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:20.912 [Webasto falsa] [01:17:29.828] > «sigo con la orden»
16:17:25.840 [Webasto falsa] [01:17:34.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:25.911 [Webasto falsa] [01:17:34.828] > «sigo con la orden»
16:17:28.224 [Webasto falsa] [01:17:37.140] < pide los sensores (0x50 05)
16:17:28.335 [Webasto falsa] [01:17:37.248] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:30.840 [Webasto falsa] [01:17:39.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:30.912 [Webasto falsa] [01:17:39.828] > «sigo con la orden»
16:17:35.840 [Webasto falsa] [01:17:44.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:35.912 [Webasto falsa] [01:17:44.828] > «sigo con la orden»
16:17:36.356 [Webasto falsa] [01:17:45.272] < pide los sensores (0x50 05)
16:17:36.467 [Webasto falsa] [01:17:45.380] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:40.840 [Webasto falsa] [01:17:49.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:40.912 [Webasto falsa] [01:17:49.828] > «sigo con la orden»
16:17:44.490 [Webasto falsa] [01:17:53.406] < pide los sensores (0x50 05)
16:17:44.601 [Webasto falsa] [01:17:53.514] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:45.839 [Webasto falsa] [01:17:54.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:45.912 [Webasto falsa] [01:17:54.828] > «sigo con la orden»
16:17:50.842 [Webasto falsa] [01:17:59.755] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:50.914 [Webasto falsa] [01:17:59.830] > «sigo con la orden»
16:17:52.622 [Webasto falsa] [01:18:01.538] < pide los sensores (0x50 05)
16:17:52.733 [Webasto falsa] [01:18:01.646] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:55.841 [Webasto falsa] [01:18:04.754] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:55.913 [Webasto falsa] [01:18:04.830] > «sigo con la orden»
16:18:00.231 [Webasto falsa] [01:18:09.140] = a tope · agua 70.7 °C · dentro 10.8 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:18:00.242 [Webasto falsa] [01:18:09.141] =   orden 0x21: quedan 1019 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
16:18:00.253 [Webasto falsa] [01:18:09.152] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:18:00.256 [Webasto falsa] [01:18:09.163] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:18:00.754 [Webasto falsa] [01:18:09.670] < pide los sensores (0x50 05)
16:18:00.864 [Webasto falsa] [01:18:09.778] > agua 71 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:18:00.895 [Webasto falsa] [01:18:09.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:18:00.967 [Webasto falsa] [01:18:09.884] > «sigo con la orden»
16:18:05.897 [Webasto falsa] [01:18:14.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:05.967 [Webasto falsa] [01:18:14.884] > «sigo con la orden»
16:18:08.886 [Webasto falsa] [01:18:17.802] < pide los sensores (0x50 05)
16:18:08.996 [Webasto falsa] [01:18:17.910] > agua 71 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:18:10.895 [Webasto falsa] [01:18:19.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:10.967 [Webasto falsa] [01:18:19.884] > «sigo con la orden»
16:18:15.895 [Webasto falsa] [01:18:24.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:15.967 [Webasto falsa] [01:18:24.884] > «sigo con la orden»
16:18:17.018 [Webasto falsa] [01:18:25.934] < pide los sensores (0x50 05)
16:18:17.129 [Webasto falsa] [01:18:26.042] > agua 71 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:20.895 [Webasto falsa] [01:18:29.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:20.967 [Webasto falsa] [01:18:29.884] > «sigo con la orden»
16:18:25.149 [Webasto falsa] [01:18:34.066] < pide los sensores (0x50 05)
16:18:25.260 [Webasto falsa] [01:18:34.174] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:25.897 [Webasto falsa] [01:18:34.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:25.969 [Webasto falsa] [01:18:34.886] > «sigo con la orden»
16:18:30.897 [Webasto falsa] [01:18:39.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:30.969 [Webasto falsa] [01:18:39.886] > «sigo con la orden»
16:18:33.281 [Webasto falsa] [01:18:42.198] < pide los sensores (0x50 05)
16:18:33.392 [Webasto falsa] [01:18:42.306] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:35.897 [Webasto falsa] [01:18:44.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:35.969 [Webasto falsa] [01:18:44.886] > «sigo con la orden»
16:18:40.897 [Webasto falsa] [01:18:49.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:40.969 [Webasto falsa] [01:18:49.886] > «sigo con la orden»
16:18:41.413 [Webasto falsa] [01:18:50.330] < pide los sensores (0x50 05)
16:18:41.524 [Webasto falsa] [01:18:50.438] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:45.897 [Webasto falsa] [01:18:54.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:45.969 [Webasto falsa] [01:18:54.886] > «sigo con la orden»
16:18:49.545 [Webasto falsa] [01:18:58.462] < pide los sensores (0x50 05)
16:18:49.656 [Webasto falsa] [01:18:58.570] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:50.897 [Webasto falsa] [01:18:59.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:50.969 [Webasto falsa] [01:18:59.886] > «sigo con la orden»
16:18:55.897 [Webasto falsa] [01:19:04.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:55.969 [Webasto falsa] [01:19:04.886] > «sigo con la orden»
16:18:57.677 [Webasto falsa] [01:19:06.594] < pide los sensores (0x50 05)
16:18:57.788 [Webasto falsa] [01:19:06.702] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:19:00.232 [Webasto falsa] [01:19:09.142] = a tope · agua 72.7 °C · dentro 11.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:19:00.243 [Webasto falsa] [01:19:09.143] =   orden 0x21: quedan 959 s · último mantenimiento hace 4.3 s (a los 20 s se apaga)
16:19:00.254 [Webasto falsa] [01:19:09.154] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:19:00.258 [Webasto falsa] [01:19:09.165] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:19:00.897 [Webasto falsa] [01:19:09.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:00.968 [Webasto falsa] [01:19:09.886] > «sigo con la orden»
16:19:05.810 [Webasto falsa] [01:19:14.726] < pide los sensores (0x50 05)
16:19:05.920 [Webasto falsa] [01:19:14.834] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:05.953 [Webasto falsa] [01:19:14.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:19:06.029 [Webasto falsa] [01:19:14.942] > «sigo con la orden»
16:19:10.952 [Webasto falsa] [01:19:19.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:11.024 [Webasto falsa] [01:19:19.942] > «sigo con la orden»
16:19:13.941 [Webasto falsa] [01:19:22.858] < pide los sensores (0x50 05)
16:19:14.052 [Webasto falsa] [01:19:22.966] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:15.952 [Webasto falsa] [01:19:24.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:16.024 [Webasto falsa] [01:19:24.942] > «sigo con la orden»
16:19:20.953 [Webasto falsa] [01:19:29.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:21.024 [Webasto falsa] [01:19:29.942] > «sigo con la orden»
16:19:22.073 [Webasto falsa] [01:19:30.990] < pide los sensores (0x50 05)
16:19:22.184 [Webasto falsa] [01:19:31.098] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:25.953 [Webasto falsa] [01:19:34.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:26.024 [Webasto falsa] [01:19:34.942] > «sigo con la orden»
16:19:30.274 [Webasto falsa] [01:19:39.122] < pide los sensores (0x50 05)
16:19:30.315 [Webasto falsa] [01:19:39.230] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:30.952 [Webasto falsa] [01:19:39.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:31.024 [Webasto falsa] [01:19:39.942] > «sigo con la orden»
16:19:35.952 [Webasto falsa] [01:19:44.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:36.024 [Webasto falsa] [01:19:44.942] > «sigo con la orden»
16:19:38.337 [Webasto falsa] [01:19:47.254] < pide los sensores (0x50 05)
16:19:38.447 [Webasto falsa] [01:19:47.362] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:40.952 [Webasto falsa] [01:19:49.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:41.024 [Webasto falsa] [01:19:49.942] > «sigo con la orden»
16:19:45.952 [Webasto falsa] [01:19:54.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:46.024 [Webasto falsa] [01:19:54.942] > «sigo con la orden»
16:19:46.469 [Webasto falsa] [01:19:55.386] < pide los sensores (0x50 05)
16:19:46.580 [Webasto falsa] [01:19:55.494] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:50.952 [Webasto falsa] [01:19:59.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:51.024 [Webasto falsa] [01:19:59.942] > «sigo con la orden»
16:19:54.601 [Webasto falsa] [01:20:03.518] < pide los sensores (0x50 05)
16:19:54.712 [Webasto falsa] [01:20:03.626] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.55 Ω
16:19:55.952 [Webasto falsa] [01:20:04.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:56.024 [Webasto falsa] [01:20:04.942] > «sigo con la orden»
16:20:00.256 [Webasto falsa] [01:20:09.144] = a tope · agua 74.6 °C · dentro 11.7 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:20:00.268 [Webasto falsa] [01:20:09.145] =   orden 0x21: quedan 899 s · último mantenimiento hace 4.3 s (a los 20 s se apaga)
16:20:00.274 [Webasto falsa] [01:20:09.156] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:20:00.293 [Webasto falsa] [01:20:09.167] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:20:00.952 [Webasto falsa] [01:20:09.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:01.024 [Webasto falsa] [01:20:09.942] > «sigo con la orden»
16:20:02.732 [Webasto falsa] [01:20:11.650] < pide los sensores (0x50 05)
16:20:02.843 [Webasto falsa] [01:20:11.758] > agua 75 °C · 12.39 V · llama sí · 5000 W · bujía 1.55 Ω
16:20:05.952 [Webasto falsa] [01:20:14.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:06.024 [Webasto falsa] [01:20:14.942] > «sigo con la orden»
16:20:10.864 [Webasto falsa] [01:20:19.782] < pide los sensores (0x50 05)
16:20:10.975 [Webasto falsa] [01:20:19.890] > agua 75 °C · 12.39 V · llama sí · 5000 W · bujía 1.55 Ω
16:20:11.008 [Webasto falsa] [01:20:19.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:20:11.080 [Webasto falsa] [01:20:19.998] > «sigo con la orden»
16:20:14.378 [Webasto falsa] [01:20:23.292] . estado: a tope → carga parcial (agua a 75 °C: baja la potencia)
16:20:16.050 [Webasto falsa] [01:20:24.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:16.080 [Webasto falsa] [01:20:24.998] > «sigo con la orden»
16:20:19.002 [Webasto falsa] [01:20:27.914] < pide los sensores (0x50 05)
16:20:19.107 [Webasto falsa] [01:20:28.022] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:21.008 [Webasto falsa] [01:20:29.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:21.080 [Webasto falsa] [01:20:29.998] > «sigo con la orden»
16:20:26.008 [Webasto falsa] [01:20:34.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:26.079 [Webasto falsa] [01:20:34.998] > «sigo con la orden»
16:20:27.128 [Webasto falsa] [01:20:36.046] < pide los sensores (0x50 05)
16:20:27.239 [Webasto falsa] [01:20:36.154] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:31.008 [Webasto falsa] [01:20:39.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:31.080 [Webasto falsa] [01:20:39.998] > «sigo con la orden»
16:20:35.260 [Webasto falsa] [01:20:44.178] < pide los sensores (0x50 05)
16:20:35.371 [Webasto falsa] [01:20:44.286] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:36.008 [Webasto falsa] [01:20:44.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:36.080 [Webasto falsa] [01:20:44.998] > «sigo con la orden»
16:20:41.008 [Webasto falsa] [01:20:49.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:41.079 [Webasto falsa] [01:20:49.998] > «sigo con la orden»
16:20:43.392 [Webasto falsa] [01:20:52.310] < pide los sensores (0x50 05)
16:20:43.503 [Webasto falsa] [01:20:52.418] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:46.008 [Webasto falsa] [01:20:54.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:46.079 [Webasto falsa] [01:20:54.998] > «sigo con la orden»
16:20:51.008 [Webasto falsa] [01:20:59.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:51.079 [Webasto falsa] [01:20:59.998] > «sigo con la orden»
16:20:51.524 [Webasto falsa] [01:21:00.442] < pide los sensores (0x50 05)
16:20:51.635 [Webasto falsa] [01:21:00.550] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:56.007 [Webasto falsa] [01:21:04.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:56.079 [Webasto falsa] [01:21:04.998] > «sigo con la orden»
16:20:59.656 [Webasto falsa] [01:21:08.574] < pide los sensores (0x50 05)
16:20:59.766 [Webasto falsa] [01:21:08.682] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:00.235 [Webasto falsa] [01:21:09.146] = carga parcial · agua 74.8 °C · dentro 12.1 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:21:00.246 [Webasto falsa] [01:21:09.147] =   orden 0x21: quedan 839 s · último mantenimiento hace 4.2 s (a los 20 s se apaga)
16:21:00.257 [Webasto falsa] [01:21:09.158] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:21:00.260 [Webasto falsa] [01:21:09.169] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:21:01.007 [Webasto falsa] [01:21:09.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:01.079 [Webasto falsa] [01:21:09.998] > «sigo con la orden»
16:21:06.007 [Webasto falsa] [01:21:14.923] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:06.079 [Webasto falsa] [01:21:14.998] > «sigo con la orden»
16:21:07.788 [Webasto falsa] [01:21:16.706] < pide los sensores (0x50 05)
16:21:07.898 [Webasto falsa] [01:21:16.814] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:11.007 [Webasto falsa] [01:21:19.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:11.079 [Webasto falsa] [01:21:19.998] > «sigo con la orden»
16:21:15.920 [Webasto falsa] [01:21:24.838] < pide los sensores (0x50 05)
16:21:16.030 [Webasto falsa] [01:21:24.946] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:16.063 [Webasto falsa] [01:21:24.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:21:16.135 [Webasto falsa] [01:21:25.054] > «sigo con la orden»
16:21:21.063 [Webasto falsa] [01:21:29.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:21.135 [Webasto falsa] [01:21:30.054] > «sigo con la orden»
16:21:24.051 [Webasto falsa] [01:21:32.970] < pide los sensores (0x50 05)
16:21:24.162 [Webasto falsa] [01:21:33.078] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:26.063 [Webasto falsa] [01:21:34.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:26.135 [Webasto falsa] [01:21:35.054] > «sigo con la orden»
16:21:31.063 [Webasto falsa] [01:21:39.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:31.135 [Webasto falsa] [01:21:40.054] > «sigo con la orden»
16:21:32.183 [Webasto falsa] [01:21:41.102] < pide los sensores (0x50 05)
16:21:32.295 [Webasto falsa] [01:21:41.210] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:36.063 [Webasto falsa] [01:21:44.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:36.135 [Webasto falsa] [01:21:45.054] > «sigo con la orden»
16:21:40.316 [Webasto falsa] [01:21:49.234] < pide los sensores (0x50 05)
16:21:40.426 [Webasto falsa] [01:21:49.342] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:41.067 [Webasto falsa] [01:21:49.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:41.135 [Webasto falsa] [01:21:50.054] > «sigo con la orden»
16:21:46.063 [Webasto falsa] [01:21:54.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:46.135 [Webasto falsa] [01:21:55.054] > «sigo con la orden»
16:21:48.448 [Webasto falsa] [01:21:57.366] < pide los sensores (0x50 05)
16:21:48.559 [Webasto falsa] [01:21:57.474] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:51.063 [Webasto falsa] [01:21:59.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:51.135 [Webasto falsa] [01:22:00.054] > «sigo con la orden»
16:21:56.063 [Webasto falsa] [01:22:04.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:56.135 [Webasto falsa] [01:22:05.054] > «sigo con la orden»
16:21:56.580 [Webasto falsa] [01:22:05.498] < pide los sensores (0x50 05)
16:21:56.690 [Webasto falsa] [01:22:05.606] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:00.236 [Webasto falsa] [01:22:09.148] = carga parcial · agua 74.6 °C · dentro 12.5 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:22:00.247 [Webasto falsa] [01:22:09.149] =   orden 0x21: quedan 779 s · último mantenimiento hace 4.2 s (a los 20 s se apaga)
16:22:00.258 [Webasto falsa] [01:22:09.160] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:22:00.262 [Webasto falsa] [01:22:09.171] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:22:01.064 [Webasto falsa] [01:22:09.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:01.134 [Webasto falsa] [01:22:10.054] > «sigo con la orden»
16:22:04.711 [Webasto falsa] [01:22:13.630] < pide los sensores (0x50 05)
16:22:04.822 [Webasto falsa] [01:22:13.738] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:06.063 [Webasto falsa] [01:22:14.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:06.135 [Webasto falsa] [01:22:15.054] > «sigo con la orden»
16:22:11.063 [Webasto falsa] [01:22:19.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:11.134 [Webasto falsa] [01:22:20.054] > «sigo con la orden»
16:22:12.843 [Webasto falsa] [01:22:21.762] < pide los sensores (0x50 05)
16:22:12.954 [Webasto falsa] [01:22:21.870] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:16.062 [Webasto falsa] [01:22:24.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:16.134 [Webasto falsa] [01:22:25.054] > «sigo con la orden»
16:22:20.975 [Webasto falsa] [01:22:29.894] < pide los sensores (0x50 05)
16:22:21.086 [Webasto falsa] [01:22:30.002] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:21.116 [Webasto falsa] [01:22:30.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:22:21.188 [Webasto falsa] [01:22:30.108] > «sigo con la orden»
16:22:26.117 [Webasto falsa] [01:22:35.033] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:26.188 [Webasto falsa] [01:22:35.108] > «sigo con la orden»
16:22:29.107 [Webasto falsa] [01:22:38.026] < pide los sensores (0x50 05)
16:22:29.218 [Webasto falsa] [01:22:38.134] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:31.117 [Webasto falsa] [01:22:40.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:31.188 [Webasto falsa] [01:22:40.108] > «sigo con la orden»
16:22:36.123 [Webasto falsa] [01:22:45.033] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:36.188 [Webasto falsa] [01:22:45.108] > «sigo con la orden»
16:22:37.239 [Webasto falsa] [01:22:46.158] < pide los sensores (0x50 05)
16:22:37.349 [Webasto falsa] [01:22:46.266] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:41.117 [Webasto falsa] [01:22:50.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:41.188 [Webasto falsa] [01:22:50.108] > «sigo con la orden»
16:22:45.371 [Webasto falsa] [01:22:54.290] < pide los sensores (0x50 05)
16:22:45.481 [Webasto falsa] [01:22:54.398] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:46.116 [Webasto falsa] [01:22:55.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:46.188 [Webasto falsa] [01:22:55.108] > «sigo con la orden»
16:22:51.119 [Webasto falsa] [01:23:00.035] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:51.190 [Webasto falsa] [01:23:00.110] > «sigo con la orden»
16:22:53.503 [Webasto falsa] [01:23:02.422] < pide los sensores (0x50 05)
16:22:53.613 [Webasto falsa] [01:23:02.530] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:56.118 [Webasto falsa] [01:23:05.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:56.190 [Webasto falsa] [01:23:05.110] > «sigo con la orden»
16:23:00.238 [Webasto falsa] [01:23:09.150] = carga parcial · agua 74.6 °C · dentro 13.0 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:23:00.248 [Webasto falsa] [01:23:09.151] =   orden 0x21: quedan 719 s · último mantenimiento hace 4.1 s (a los 20 s se apaga)
16:23:00.260 [Webasto falsa] [01:23:09.162] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:23:00.263 [Webasto falsa] [01:23:09.173] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:23:01.118 [Webasto falsa] [01:23:10.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:01.190 [Webasto falsa] [01:23:10.110] > «sigo con la orden»
16:23:01.634 [Webasto falsa] [01:23:10.554] < pide los sensores (0x50 05)
16:23:01.745 [Webasto falsa] [01:23:10.662] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:06.118 [Webasto falsa] [01:23:15.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:06.190 [Webasto falsa] [01:23:15.110] > «sigo con la orden»
16:23:09.767 [Webasto falsa] [01:23:18.686] < pide los sensores (0x50 05)
16:23:09.877 [Webasto falsa] [01:23:18.794] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:11.118 [Webasto falsa] [01:23:20.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:11.190 [Webasto falsa] [01:23:20.110] > «sigo con la orden»
16:23:16.118 [Webasto falsa] [01:23:25.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:16.190 [Webasto falsa] [01:23:25.110] > «sigo con la orden»
16:23:17.898 [Webasto falsa] [01:23:26.818] < pide los sensores (0x50 05)
16:23:18.009 [Webasto falsa] [01:23:26.926] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:21.118 [Webasto falsa] [01:23:30.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:21.189 [Webasto falsa] [01:23:30.110] > «sigo con la orden»
16:23:26.030 [Webasto falsa] [01:23:34.950] < pide los sensores (0x50 05)
16:23:26.141 [Webasto falsa] [01:23:35.058] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:26.174 [Webasto falsa] [01:23:35.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:23:26.246 [Webasto falsa] [01:23:35.166] > «sigo con la orden»
16:23:31.174 [Webasto falsa] [01:23:40.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:31.245 [Webasto falsa] [01:23:40.166] > «sigo con la orden»
16:23:34.162 [Webasto falsa] [01:23:43.082] < pide los sensores (0x50 05)
16:23:34.273 [Webasto falsa] [01:23:43.190] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:36.174 [Webasto falsa] [01:23:45.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:36.245 [Webasto falsa] [01:23:45.166] > «sigo con la orden»
16:23:41.174 [Webasto falsa] [01:23:50.091] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:41.245 [Webasto falsa] [01:23:50.166] > «sigo con la orden»
16:23:42.294 [Webasto falsa] [01:23:51.214] < pide los sensores (0x50 05)
16:23:42.405 [Webasto falsa] [01:23:51.322] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:46.174 [Webasto falsa] [01:23:55.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:46.245 [Webasto falsa] [01:23:55.166] > «sigo con la orden»
16:23:50.426 [Webasto falsa] [01:23:59.346] < pide los sensores (0x50 05)
16:23:50.537 [Webasto falsa] [01:23:59.454] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:51.174 [Webasto falsa] [01:24:00.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:51.246 [Webasto falsa] [01:24:00.166] > «sigo con la orden»
16:23:56.173 [Webasto falsa] [01:24:05.091] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:56.245 [Webasto falsa] [01:24:05.166] > «sigo con la orden»
16:23:58.558 [Webasto falsa] [01:24:07.478] < pide los sensores (0x50 05)
16:23:58.669 [Webasto falsa] [01:24:07.586] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:00.239 [Webasto falsa] [01:24:09.152] = carga parcial · agua 74.5 °C · dentro 13.4 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:24:00.250 [Webasto falsa] [01:24:09.153] =   orden 0x21: quedan 659 s · último mantenimiento hace 4.1 s (a los 20 s se apaga)
16:24:00.261 [Webasto falsa] [01:24:09.164] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:24:00.265 [Webasto falsa] [01:24:09.175] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:24:01.173 [Webasto falsa] [01:24:10.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:01.245 [Webasto falsa] [01:24:10.166] > «sigo con la orden»
16:24:06.173 [Webasto falsa] [01:24:15.091] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:06.245 [Webasto falsa] [01:24:15.166] > «sigo con la orden»
16:24:06.690 [Webasto falsa] [01:24:15.610] < pide los sensores (0x50 05)
16:24:06.800 [Webasto falsa] [01:24:15.718] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:11.173 [Webasto falsa] [01:24:20.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:11.245 [Webasto falsa] [01:24:20.166] > «sigo con la orden»
16:24:14.822 [Webasto falsa] [01:24:23.742] < pide los sensores (0x50 05)
16:24:14.932 [Webasto falsa] [01:24:23.850] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:16.173 [Webasto falsa] [01:24:25.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:16.245 [Webasto falsa] [01:24:25.166] > «sigo con la orden»
16:24:21.195 [Webasto falsa] [01:24:30.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:21.245 [Webasto falsa] [01:24:30.166] > «sigo con la orden»
16:24:22.954 [Webasto falsa] [01:24:31.874] < pide los sensores (0x50 05)
16:24:23.064 [Webasto falsa] [01:24:31.982] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:26.173 [Webasto falsa] [01:24:35.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:26.245 [Webasto falsa] [01:24:35.166] > «sigo con la orden»
16:24:31.086 [Webasto falsa] [01:24:40.006] < pide los sensores (0x50 05)
16:24:31.196 [Webasto falsa] [01:24:40.114] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:31.229 [Webasto falsa] [01:24:40.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:24:31.301 [Webasto falsa] [01:24:40.222] > «sigo con la orden»
16:24:36.229 [Webasto falsa] [01:24:45.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:36.301 [Webasto falsa] [01:24:45.222] > «sigo con la orden»
16:24:39.217 [Webasto falsa] [01:24:48.138] < pide los sensores (0x50 05)
16:24:39.328 [Webasto falsa] [01:24:48.246] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:41.229 [Webasto falsa] [01:24:50.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:41.301 [Webasto falsa] [01:24:50.222] > «sigo con la orden»
16:24:46.229 [Webasto falsa] [01:24:55.147] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:46.301 [Webasto falsa] [01:24:55.222] > «sigo con la orden»
16:24:47.350 [Webasto falsa] [01:24:56.270] < pide los sensores (0x50 05)
16:24:47.460 [Webasto falsa] [01:24:56.378] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:51.229 [Webasto falsa] [01:25:00.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:51.301 [Webasto falsa] [01:25:00.222] > «sigo con la orden»
16:24:55.481 [Webasto falsa] [01:25:04.402] < pide los sensores (0x50 05)
16:24:55.592 [Webasto falsa] [01:25:04.510] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:56.229 [Webasto falsa] [01:25:05.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:56.300 [Webasto falsa] [01:25:05.222] > «sigo con la orden»
16:25:00.240 [Webasto falsa] [01:25:09.154] = carga parcial · agua 74.5 °C · dentro 13.8 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:25:00.251 [Webasto falsa] [01:25:09.155] =   orden 0x21: quedan 599 s · último mantenimiento hace 4.0 s (a los 20 s se apaga)
16:25:00.262 [Webasto falsa] [01:25:09.166] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:25:00.266 [Webasto falsa] [01:25:09.177] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:25:01.228 [Webasto falsa] [01:25:10.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:01.300 [Webasto falsa] [01:25:10.222] > «sigo con la orden»
16:25:03.613 [Webasto falsa] [01:25:12.534] < pide los sensores (0x50 05)
16:25:03.724 [Webasto falsa] [01:25:12.642] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:06.228 [Webasto falsa] [01:25:15.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:06.301 [Webasto falsa] [01:25:15.222] > «sigo con la orden»
16:25:11.229 [Webasto falsa] [01:25:20.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:11.301 [Webasto falsa] [01:25:20.222] > «sigo con la orden»
16:25:11.745 [Webasto falsa] [01:25:20.666] < pide los sensores (0x50 05)
16:25:11.856 [Webasto falsa] [01:25:20.774] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:16.228 [Webasto falsa] [01:25:25.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:16.300 [Webasto falsa] [01:25:25.222] > «sigo con la orden»
16:25:19.877 [Webasto falsa] [01:25:28.798] < pide los sensores (0x50 05)
16:25:19.987 [Webasto falsa] [01:25:28.906] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:21.229 [Webasto falsa] [01:25:30.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:21.300 [Webasto falsa] [01:25:30.222] > «sigo con la orden»
16:25:26.228 [Webasto falsa] [01:25:35.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:26.300 [Webasto falsa] [01:25:35.222] > «sigo con la orden»
16:25:28.009 [Webasto falsa] [01:25:36.930] < pide los sensores (0x50 05)
16:25:28.119 [Webasto falsa] [01:25:37.038] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:31.229 [Webasto falsa] [01:25:40.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:31.300 [Webasto falsa] [01:25:40.222] > «sigo con la orden»
16:25:36.141 [Webasto falsa] [01:25:45.062] < pide los sensores (0x50 05)
16:25:36.252 [Webasto falsa] [01:25:45.170] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:36.284 [Webasto falsa] [01:25:45.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:25:36.356 [Webasto falsa] [01:25:45.278] > «sigo con la orden»
16:25:41.285 [Webasto falsa] [01:25:50.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:41.356 [Webasto falsa] [01:25:50.278] > «sigo con la orden»
16:25:44.273 [Webasto falsa] [01:25:53.194] < pide los sensores (0x50 05)
16:25:44.384 [Webasto falsa] [01:25:53.302] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:46.284 [Webasto falsa] [01:25:55.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:46.356 [Webasto falsa] [01:25:55.278] > «sigo con la orden»
16:25:51.285 [Webasto falsa] [01:26:00.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:51.356 [Webasto falsa] [01:26:00.278] > «sigo con la orden»
16:25:52.407 [Webasto falsa] [01:26:01.328] < pide los sensores (0x50 05)
16:25:52.518 [Webasto falsa] [01:26:01.436] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:56.284 [Webasto falsa] [01:26:05.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:56.356 [Webasto falsa] [01:26:05.278] > «sigo con la orden»
16:26:00.242 [Webasto falsa] [01:26:09.156] = carga parcial · agua 74.5 °C · dentro 14.2 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:26:00.253 [Webasto falsa] [01:26:09.157] =   orden 0x21: quedan 539 s · último mantenimiento hace 4.0 s (a los 20 s se apaga)
16:26:00.264 [Webasto falsa] [01:26:09.168] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:26:00.269 [Webasto falsa] [01:26:09.179] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:26:00.539 [Webasto falsa] [01:26:09.460] < pide los sensores (0x50 05)
16:26:00.649 [Webasto falsa] [01:26:09.568] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:01.284 [Webasto falsa] [01:26:10.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:01.356 [Webasto falsa] [01:26:10.278] > «sigo con la orden»
16:26:06.284 [Webasto falsa] [01:26:15.203] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:06.356 [Webasto falsa] [01:26:15.278] > «sigo con la orden»
16:26:08.690 [Webasto falsa] [01:26:17.612] < pide los sensores (0x50 05)
16:26:08.801 [Webasto falsa] [01:26:17.720] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:11.284 [Webasto falsa] [01:26:20.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:11.356 [Webasto falsa] [01:26:20.278] > «sigo con la orden»
16:26:16.284 [Webasto falsa] [01:26:25.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:16.356 [Webasto falsa] [01:26:25.278] > «sigo con la orden»
16:26:16.822 [Webasto falsa] [01:26:25.744] < pide los sensores (0x50 05)
16:26:16.933 [Webasto falsa] [01:26:25.852] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:21.284 [Webasto falsa] [01:26:30.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:21.356 [Webasto falsa] [01:26:30.278] > «sigo con la orden»
16:26:24.954 [Webasto falsa] [01:26:33.876] < pide los sensores (0x50 05)
16:26:25.065 [Webasto falsa] [01:26:33.984] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:26.284 [Webasto falsa] [01:26:35.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:26.356 [Webasto falsa] [01:26:35.278] > «sigo con la orden»
16:26:31.284 [Webasto falsa] [01:26:40.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:31.356 [Webasto falsa] [01:26:40.278] > «sigo con la orden»
16:26:33.086 [Webasto falsa] [01:26:42.008] < pide los sensores (0x50 05)
16:26:33.197 [Webasto falsa] [01:26:42.116] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:36.284 [Webasto falsa] [01:26:45.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:36.356 [Webasto falsa] [01:26:45.278] > «sigo con la orden»
16:26:41.218 [Webasto falsa] [01:26:50.140] < pide los sensores (0x50 05)
16:26:41.329 [Webasto falsa] [01:26:50.248] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:41.361 [Webasto falsa] [01:26:50.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:26:41.433 [Webasto falsa] [01:26:50.356] > «sigo con la orden»
16:26:46.362 [Webasto falsa] [01:26:55.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:46.433 [Webasto falsa] [01:26:55.356] > «sigo con la orden»
16:26:49.350 [Webasto falsa] [01:26:58.272] < pide los sensores (0x50 05)
16:26:49.461 [Webasto falsa] [01:26:58.380] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:51.362 [Webasto falsa] [01:27:00.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:51.434 [Webasto falsa] [01:27:00.356] > «sigo con la orden»
16:26:56.362 [Webasto falsa] [01:27:05.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:56.433 [Webasto falsa] [01:27:05.356] > «sigo con la orden»
16:26:57.482 [Webasto falsa] [01:27:06.404] < pide los sensores (0x50 05)
16:26:57.593 [Webasto falsa] [01:27:06.512] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:00.243 [Webasto falsa] [01:27:09.158] = carga parcial · agua 74.5 °C · dentro 14.6 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:27:00.254 [Webasto falsa] [01:27:09.159] =   orden 0x21: quedan 479 s · último mantenimiento hace 3.9 s (a los 20 s se apaga)
16:27:00.265 [Webasto falsa] [01:27:09.170] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:27:00.269 [Webasto falsa] [01:27:09.181] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:27:01.361 [Webasto falsa] [01:27:10.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:01.433 [Webasto falsa] [01:27:10.356] > «sigo con la orden»
16:27:05.614 [Webasto falsa] [01:27:14.536] < pide los sensores (0x50 05)
16:27:05.725 [Webasto falsa] [01:27:14.644] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:06.361 [Webasto falsa] [01:27:15.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:06.433 [Webasto falsa] [01:27:15.356] > «sigo con la orden»
16:27:11.361 [Webasto falsa] [01:27:20.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:11.433 [Webasto falsa] [01:27:20.356] > «sigo con la orden»
16:27:13.745 [Webasto falsa] [01:27:22.668] < pide los sensores (0x50 05)
16:27:13.856 [Webasto falsa] [01:27:22.776] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:16.361 [Webasto falsa] [01:27:25.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:16.433 [Webasto falsa] [01:27:25.356] > «sigo con la orden»
16:27:21.361 [Webasto falsa] [01:27:30.281] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:21.433 [Webasto falsa] [01:27:30.356] > «sigo con la orden»
16:27:21.878 [Webasto falsa] [01:27:30.800] < pide los sensores (0x50 05)
16:27:21.988 [Webasto falsa] [01:27:30.908] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:26.361 [Webasto falsa] [01:27:35.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:26.433 [Webasto falsa] [01:27:35.356] > «sigo con la orden»
16:27:30.010 [Webasto falsa] [01:27:38.932] < pide los sensores (0x50 05)
16:27:30.120 [Webasto falsa] [01:27:39.040] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:31.361 [Webasto falsa] [01:27:40.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:31.433 [Webasto falsa] [01:27:40.356] > «sigo con la orden»
16:27:36.361 [Webasto falsa] [01:27:45.281] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:36.433 [Webasto falsa] [01:27:45.356] > «sigo con la orden»
16:27:38.142 [Webasto falsa] [01:27:47.064] < pide los sensores (0x50 05)
16:27:38.252 [Webasto falsa] [01:27:47.172] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:41.361 [Webasto falsa] [01:27:50.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:41.433 [Webasto falsa] [01:27:50.356] > «sigo con la orden»
16:27:46.273 [Webasto falsa] [01:27:55.196] < pide los sensores (0x50 05)
16:27:46.384 [Webasto falsa] [01:27:55.304] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:46.417 [Webasto falsa] [01:27:55.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:27:46.489 [Webasto falsa] [01:27:55.412] > «sigo con la orden»
16:27:51.417 [Webasto falsa] [01:28:00.337] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:51.489 [Webasto falsa] [01:28:00.412] > «sigo con la orden»
16:27:54.405 [Webasto falsa] [01:28:03.328] < pide los sensores (0x50 05)
16:27:54.516 [Webasto falsa] [01:28:03.436] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:56.417 [Webasto falsa] [01:28:05.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:56.489 [Webasto falsa] [01:28:05.412] > «sigo con la orden»
16:28:00.244 [Webasto falsa] [01:28:09.160] = carga parcial · agua 74.5 °C · dentro 15.0 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:28:00.255 [Webasto falsa] [01:28:09.161] =   orden 0x21: quedan 419 s · último mantenimiento hace 3.8 s (a los 20 s se apaga)
16:28:00.269 [Webasto falsa] [01:28:09.172] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:28:00.273 [Webasto falsa] [01:28:09.183] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:28:01.416 [Webasto falsa] [01:28:10.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:01.489 [Webasto falsa] [01:28:10.412] > «sigo con la orden»
16:28:02.537 [Webasto falsa] [01:28:11.460] < pide los sensores (0x50 05)
16:28:02.648 [Webasto falsa] [01:28:11.568] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:06.417 [Webasto falsa] [01:28:15.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:06.488 [Webasto falsa] [01:28:15.412] > «sigo con la orden»
16:28:10.669 [Webasto falsa] [01:28:19.592] < pide los sensores (0x50 05)
16:28:10.780 [Webasto falsa] [01:28:19.700] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:11.417 [Webasto falsa] [01:28:20.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:11.489 [Webasto falsa] [01:28:20.412] > «sigo con la orden»
16:28:16.417 [Webasto falsa] [01:28:25.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:16.488 [Webasto falsa] [01:28:25.412] > «sigo con la orden»
16:28:18.801 [Webasto falsa] [01:28:27.724] < pide los sensores (0x50 05)
16:28:18.912 [Webasto falsa] [01:28:27.832] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:21.417 [Webasto falsa] [01:28:30.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:21.488 [Webasto falsa] [01:28:30.412] > «sigo con la orden»
16:28:26.417 [Webasto falsa] [01:28:35.337] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:26.488 [Webasto falsa] [01:28:35.412] > «sigo con la orden»
16:28:26.933 [Webasto falsa] [01:28:35.856] < pide los sensores (0x50 05)
16:28:27.043 [Webasto falsa] [01:28:35.964] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:31.417 [Webasto falsa] [01:28:40.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:31.488 [Webasto falsa] [01:28:40.412] > «sigo con la orden»
16:28:35.065 [Webasto falsa] [01:28:43.988] < pide los sensores (0x50 05)
16:28:35.176 [Webasto falsa] [01:28:44.096] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:36.417 [Webasto falsa] [01:28:45.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:36.488 [Webasto falsa] [01:28:45.412] > «sigo con la orden»
16:28:41.416 [Webasto falsa] [01:28:50.337] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:41.488 [Webasto falsa] [01:28:50.412] > «sigo con la orden»
16:28:43.211 [Webasto falsa] [01:28:52.134] < pide los sensores (0x50 05)
16:28:43.322 [Webasto falsa] [01:28:52.242] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:46.416 [Webasto falsa] [01:28:55.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:46.488 [Webasto falsa] [01:28:55.412] > «sigo con la orden»
16:28:51.343 [Webasto falsa] [01:29:00.266] < pide los sensores (0x50 05)
16:28:51.454 [Webasto falsa] [01:29:00.374] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:51.486 [Webasto falsa] [01:29:00.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:28:51.558 [Webasto falsa] [01:29:00.482] > «sigo con la orden»
16:28:56.486 [Webasto falsa] [01:29:05.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:56.558 [Webasto falsa] [01:29:05.482] > «sigo con la orden»
16:28:59.475 [Webasto falsa] [01:29:08.398] < pide los sensores (0x50 05)
16:28:59.585 [Webasto falsa] [01:29:08.506] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:00.246 [Webasto falsa] [01:29:09.162] = carga parcial · agua 74.5 °C · dentro 15.4 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:29:00.257 [Webasto falsa] [01:29:09.163] =   orden 0x21: quedan 359 s · último mantenimiento hace 3.8 s (a los 20 s se apaga)
16:29:00.271 [Webasto falsa] [01:29:09.174] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:29:00.274 [Webasto falsa] [01:29:09.185] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:29:01.486 [Webasto falsa] [01:29:10.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:01.558 [Webasto falsa] [01:29:10.482] > «sigo con la orden»
16:29:06.486 [Webasto falsa] [01:29:15.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:06.558 [Webasto falsa] [01:29:15.482] > «sigo con la orden»
16:29:07.607 [Webasto falsa] [01:29:16.530] < pide los sensores (0x50 05)
16:29:07.717 [Webasto falsa] [01:29:16.638] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:11.486 [Webasto falsa] [01:29:20.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:11.557 [Webasto falsa] [01:29:20.482] > «sigo con la orden»
16:29:15.740 [Webasto falsa] [01:29:24.664] < pide los sensores (0x50 05)
16:29:15.851 [Webasto falsa] [01:29:24.772] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:16.486 [Webasto falsa] [01:29:25.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:16.558 [Webasto falsa] [01:29:25.482] > «sigo con la orden»
16:29:21.486 [Webasto falsa] [01:29:30.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:21.558 [Webasto falsa] [01:29:30.482] > «sigo con la orden»
16:29:23.872 [Webasto falsa] [01:29:32.796] < pide los sensores (0x50 05)
16:29:23.983 [Webasto falsa] [01:29:32.904] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:26.486 [Webasto falsa] [01:29:35.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:26.558 [Webasto falsa] [01:29:35.482] > «sigo con la orden»
16:29:31.486 [Webasto falsa] [01:29:40.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:31.557 [Webasto falsa] [01:29:40.482] > «sigo con la orden»
16:29:32.006 [Webasto falsa] [01:29:40.930] < pide los sensores (0x50 05)
16:29:32.117 [Webasto falsa] [01:29:41.038] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:36.486 [Webasto falsa] [01:29:45.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:36.558 [Webasto falsa] [01:29:45.482] > «sigo con la orden»
16:29:40.140 [Webasto falsa] [01:29:49.064] < pide los sensores (0x50 05)
16:29:40.251 [Webasto falsa] [01:29:49.172] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:41.486 [Webasto falsa] [01:29:50.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:41.558 [Webasto falsa] [01:29:50.482] > «sigo con la orden»
16:29:46.486 [Webasto falsa] [01:29:55.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:46.558 [Webasto falsa] [01:29:55.482] > «sigo con la orden»
16:29:48.272 [Webasto falsa] [01:29:57.196] < pide los sensores (0x50 05)
16:29:48.383 [Webasto falsa] [01:29:57.304] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:51.486 [Webasto falsa] [01:30:00.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:51.558 [Webasto falsa] [01:30:00.482] > «sigo con la orden»
16:29:56.404 [Webasto falsa] [01:30:05.328] < pide los sensores (0x50 05)
16:29:56.515 [Webasto falsa] [01:30:05.436] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:56.546 [Webasto falsa] [01:30:05.466] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:29:56.617 [Webasto falsa] [01:30:05.542] > «sigo con la orden»
16:30:00.247 [Webasto falsa] [01:30:09.164] = carga parcial · agua 74.5 °C · dentro 15.8 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:30:00.258 [Webasto falsa] [01:30:09.165] =   orden 0x21: quedan 299 s · último mantenimiento hace 3.7 s (a los 20 s se apaga)
16:30:00.272 [Webasto falsa] [01:30:09.176] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:30:00.276 [Webasto falsa] [01:30:09.187] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:30:01.546 [Webasto falsa] [01:30:10.466] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:01.617 [Webasto falsa] [01:30:10.542] > «sigo con la orden»
16:30:04.536 [Webasto falsa] [01:30:13.460] < pide los sensores (0x50 05)
16:30:04.646 [Webasto falsa] [01:30:13.568] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:06.546 [Webasto falsa] [01:30:15.466] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:06.617 [Webasto falsa] [01:30:15.542] > «sigo con la orden»
16:30:11.546 [Webasto falsa] [01:30:20.467] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:11.617 [Webasto falsa] [01:30:20.542] > «sigo con la orden»
16:30:12.668 [Webasto falsa] [01:30:21.592] < pide los sensores (0x50 05)
16:30:12.779 [Webasto falsa] [01:30:21.700] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:16.547 [Webasto falsa] [01:30:25.468] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:16.619 [Webasto falsa] [01:30:25.544] > «sigo con la orden»
16:30:20.800 [Webasto falsa] [01:30:29.724] < pide los sensores (0x50 05)
16:30:20.911 [Webasto falsa] [01:30:29.832] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:21.547 [Webasto falsa] [01:30:30.468] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:21.619 [Webasto falsa] [01:30:30.544] > «sigo con la orden»
16:30:26.547 [Webasto falsa] [01:30:35.469] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:26.619 [Webasto falsa] [01:30:35.544] > «sigo con la orden»
16:30:28.932 [Webasto falsa] [01:30:37.856] < pide los sensores (0x50 05)
16:30:29.042 [Webasto falsa] [01:30:37.964] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:31.549 [Webasto falsa] [01:30:40.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:31.621 [Webasto falsa] [01:30:40.546] > «sigo con la orden»
16:30:36.549 [Webasto falsa] [01:30:45.471] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:36.621 [Webasto falsa] [01:30:45.546] > «sigo con la orden»
16:30:37.063 [Webasto falsa] [01:30:45.988] < pide los sensores (0x50 05)
16:30:37.174 [Webasto falsa] [01:30:46.096] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:41.549 [Webasto falsa] [01:30:50.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:41.621 [Webasto falsa] [01:30:50.546] > «sigo con la orden»
16:30:45.196 [Webasto falsa] [01:30:54.120] < pide los sensores (0x50 05)
16:30:45.306 [Webasto falsa] [01:30:54.228] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:46.549 [Webasto falsa] [01:30:55.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:46.621 [Webasto falsa] [01:30:55.546] > «sigo con la orden»
16:30:51.549 [Webasto falsa] [01:31:00.471] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:51.621 [Webasto falsa] [01:31:00.546] > «sigo con la orden»
16:30:53.328 [Webasto falsa] [01:31:02.252] < pide los sensores (0x50 05)
16:30:53.438 [Webasto falsa] [01:31:02.360] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:56.549 [Webasto falsa] [01:31:05.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:56.621 [Webasto falsa] [01:31:05.546] > «sigo con la orden»
16:31:00.248 [Webasto falsa] [01:31:09.166] = carga parcial · agua 74.5 °C · dentro 16.1 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:31:00.259 [Webasto falsa] [01:31:09.167] =   orden 0x21: quedan 239 s · último mantenimiento hace 3.7 s (a los 20 s se apaga)
16:31:00.271 [Webasto falsa] [01:31:09.178] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:31:00.275 [Webasto falsa] [01:31:09.189] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:31:01.459 [Webasto falsa] [01:31:10.384] < pide los sensores (0x50 05)
16:31:01.570 [Webasto falsa] [01:31:10.492] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:01.603 [Webasto falsa] [01:31:10.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:31:01.675 [Webasto falsa] [01:31:10.600] > «sigo con la orden»
16:31:06.603 [Webasto falsa] [01:31:15.525] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:06.675 [Webasto falsa] [01:31:15.600] > «sigo con la orden»
16:31:09.591 [Webasto falsa] [01:31:18.516] < pide los sensores (0x50 05)
16:31:09.702 [Webasto falsa] [01:31:18.624] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:11.603 [Webasto falsa] [01:31:20.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:11.675 [Webasto falsa] [01:31:20.600] > «sigo con la orden»
16:31:16.603 [Webasto falsa] [01:31:25.525] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:16.674 [Webasto falsa] [01:31:25.600] > «sigo con la orden»
16:31:17.723 [Webasto falsa] [01:31:26.648] < pide los sensores (0x50 05)
16:31:17.834 [Webasto falsa] [01:31:26.756] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:21.603 [Webasto falsa] [01:31:30.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:21.674 [Webasto falsa] [01:31:30.600] > «sigo con la orden»
16:31:25.855 [Webasto falsa] [01:31:34.780] < pide los sensores (0x50 05)
16:31:25.966 [Webasto falsa] [01:31:34.888] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:26.603 [Webasto falsa] [01:31:35.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:26.674 [Webasto falsa] [01:31:35.600] > «sigo con la orden»
16:31:31.602 [Webasto falsa] [01:31:40.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:31.674 [Webasto falsa] [01:31:40.600] > «sigo con la orden»
16:31:33.987 [Webasto falsa] [01:31:42.912] < pide los sensores (0x50 05)
16:31:34.098 [Webasto falsa] [01:31:43.020] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:36.602 [Webasto falsa] [01:31:45.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:36.674 [Webasto falsa] [01:31:45.600] > «sigo con la orden»
16:31:41.603 [Webasto falsa] [01:31:50.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:41.674 [Webasto falsa] [01:31:50.600] > «sigo con la orden»
16:31:42.119 [Webasto falsa] [01:31:51.044] < pide los sensores (0x50 05)
16:31:42.230 [Webasto falsa] [01:31:51.152] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:46.603 [Webasto falsa] [01:31:55.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:46.674 [Webasto falsa] [01:31:55.600] > «sigo con la orden»
16:31:50.251 [Webasto falsa] [01:31:59.176] < pide los sensores (0x50 05)
16:31:50.361 [Webasto falsa] [01:31:59.284] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:51.603 [Webasto falsa] [01:32:00.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:51.674 [Webasto falsa] [01:32:00.600] > «sigo con la orden»
16:31:56.602 [Webasto falsa] [01:32:05.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:56.674 [Webasto falsa] [01:32:05.600] > «sigo con la orden»
16:31:58.383 [Webasto falsa] [01:32:07.308] < pide los sensores (0x50 05)
16:31:58.493 [Webasto falsa] [01:32:07.416] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:00.250 [Webasto falsa] [01:32:09.168] = carga parcial · agua 74.5 °C · dentro 16.5 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:32:00.261 [Webasto falsa] [01:32:09.169] =   orden 0x21: quedan 179 s · último mantenimiento hace 3.6 s (a los 20 s se apaga)
16:32:00.273 [Webasto falsa] [01:32:09.180] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:32:00.276 [Webasto falsa] [01:32:09.191] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:32:01.602 [Webasto falsa] [01:32:10.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:01.674 [Webasto falsa] [01:32:10.600] > «sigo con la orden»
16:32:06.515 [Webasto falsa] [01:32:15.440] < pide los sensores (0x50 05)
16:32:06.625 [Webasto falsa] [01:32:15.548] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:06.658 [Webasto falsa] [01:32:15.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:32:06.730 [Webasto falsa] [01:32:15.656] > «sigo con la orden»
16:32:11.658 [Webasto falsa] [01:32:20.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:11.730 [Webasto falsa] [01:32:20.656] > «sigo con la orden»
16:32:14.647 [Webasto falsa] [01:32:23.572] < pide los sensores (0x50 05)
16:32:14.757 [Webasto falsa] [01:32:23.680] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:16.658 [Webasto falsa] [01:32:25.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:16.730 [Webasto falsa] [01:32:25.656] > «sigo con la orden»
16:32:21.658 [Webasto falsa] [01:32:30.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:21.730 [Webasto falsa] [01:32:30.656] > «sigo con la orden»
16:32:22.781 [Webasto falsa] [01:32:31.706] < pide los sensores (0x50 05)
16:32:22.891 [Webasto falsa] [01:32:31.814] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:26.658 [Webasto falsa] [01:32:35.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:26.730 [Webasto falsa] [01:32:35.656] > «sigo con la orden»
16:32:30.912 [Webasto falsa] [01:32:39.838] < pide los sensores (0x50 05)
16:32:31.023 [Webasto falsa] [01:32:39.946] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:31.658 [Webasto falsa] [01:32:40.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:31.730 [Webasto falsa] [01:32:40.656] > «sigo con la orden»
16:32:36.658 [Webasto falsa] [01:32:45.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:36.730 [Webasto falsa] [01:32:45.656] > «sigo con la orden»
16:32:39.044 [Webasto falsa] [01:32:47.970] < pide los sensores (0x50 05)
16:32:39.155 [Webasto falsa] [01:32:48.078] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:41.658 [Webasto falsa] [01:32:50.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:41.729 [Webasto falsa] [01:32:50.656] > «sigo con la orden»
16:32:46.658 [Webasto falsa] [01:32:55.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:46.730 [Webasto falsa] [01:32:55.656] > «sigo con la orden»
16:32:47.176 [Webasto falsa] [01:32:56.102] < pide los sensores (0x50 05)
16:32:47.287 [Webasto falsa] [01:32:56.210] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:51.658 [Webasto falsa] [01:33:00.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:51.730 [Webasto falsa] [01:33:00.656] > «sigo con la orden»
16:32:55.308 [Webasto falsa] [01:33:04.234] < pide los sensores (0x50 05)
16:32:55.419 [Webasto falsa] [01:33:04.342] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:56.658 [Webasto falsa] [01:33:05.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:56.729 [Webasto falsa] [01:33:05.656] > «sigo con la orden»
16:33:00.251 [Webasto falsa] [01:33:09.170] = carga parcial · agua 74.5 °C · dentro 16.9 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:33:00.262 [Webasto falsa] [01:33:09.171] =   orden 0x21: quedan 119 s · último mantenimiento hace 3.6 s (a los 20 s se apaga)
16:33:00.273 [Webasto falsa] [01:33:09.182] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:33:00.277 [Webasto falsa] [01:33:09.193] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:33:01.658 [Webasto falsa] [01:33:10.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:01.729 [Webasto falsa] [01:33:10.656] > «sigo con la orden»
16:33:03.440 [Webasto falsa] [01:33:12.366] < pide los sensores (0x50 05)
16:33:03.551 [Webasto falsa] [01:33:12.474] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:06.658 [Webasto falsa] [01:33:15.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:06.729 [Webasto falsa] [01:33:15.656] > «sigo con la orden»
16:33:11.572 [Webasto falsa] [01:33:20.498] < pide los sensores (0x50 05)
16:33:11.682 [Webasto falsa] [01:33:20.606] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:11.716 [Webasto falsa] [01:33:20.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:33:11.787 [Webasto falsa] [01:33:20.714] > «sigo con la orden»
16:33:16.716 [Webasto falsa] [01:33:25.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:16.787 [Webasto falsa] [01:33:25.714] > «sigo con la orden»
16:33:19.703 [Webasto falsa] [01:33:28.630] < pide los sensores (0x50 05)
16:33:19.815 [Webasto falsa] [01:33:28.738] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:21.733 [Webasto falsa] [01:33:30.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:21.787 [Webasto falsa] [01:33:30.714] > «sigo con la orden»
16:33:26.715 [Webasto falsa] [01:33:35.639] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:26.787 [Webasto falsa] [01:33:35.714] > «sigo con la orden»
16:33:28.474 [Webasto falsa] [01:33:37.400] < pide los sensores (0x50 05)
16:33:28.584 [Webasto falsa] [01:33:37.508] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:31.715 [Webasto falsa] [01:33:40.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:31.787 [Webasto falsa] [01:33:40.714] > «sigo con la orden»
16:33:36.606 [Webasto falsa] [01:33:45.532] < pide los sensores (0x50 05)
16:33:36.716 [Webasto falsa] [01:33:45.640] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:36.747 [Webasto falsa] [01:33:45.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:36.819 [Webasto falsa] [01:33:45.746] > «sigo con la orden»
16:33:41.747 [Webasto falsa] [01:33:50.671] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:41.819 [Webasto falsa] [01:33:50.746] > «sigo con la orden»
16:33:44.740 [Webasto falsa] [01:33:53.666] < pide los sensores (0x50 05)
16:33:44.850 [Webasto falsa] [01:33:53.774] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:46.747 [Webasto falsa] [01:33:55.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:46.819 [Webasto falsa] [01:33:55.746] > «sigo con la orden»
16:33:51.747 [Webasto falsa] [01:34:00.671] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:51.819 [Webasto falsa] [01:34:00.746] > «sigo con la orden»
16:33:52.871 [Webasto falsa] [01:34:01.798] < pide los sensores (0x50 05)
16:33:52.983 [Webasto falsa] [01:34:01.906] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:56.747 [Webasto falsa] [01:34:05.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:56.818 [Webasto falsa] [01:34:05.746] > «sigo con la orden»
16:34:00.252 [Webasto falsa] [01:34:09.172] = carga parcial · agua 74.5 °C · dentro 17.3 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:34:00.263 [Webasto falsa] [01:34:09.173] =   orden 0x21: quedan 59 s · último mantenimiento hace 3.5 s (a los 20 s se apaga)
16:34:00.275 [Webasto falsa] [01:34:09.184] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:34:00.280 [Webasto falsa] [01:34:09.195] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:34:01.003 [Webasto falsa] [01:34:09.930] < pide los sensores (0x50 05)
16:34:01.114 [Webasto falsa] [01:34:10.038] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:01.747 [Webasto falsa] [01:34:10.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:01.818 [Webasto falsa] [01:34:10.746] > «sigo con la orden»
16:34:06.749 [Webasto falsa] [01:34:15.673] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:06.820 [Webasto falsa] [01:34:15.748] > «sigo con la orden»
16:34:09.135 [Webasto falsa] [01:34:18.062] < pide los sensores (0x50 05)
16:34:09.247 [Webasto falsa] [01:34:18.170] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:11.749 [Webasto falsa] [01:34:20.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:11.821 [Webasto falsa] [01:34:20.748] > «sigo con la orden»
16:34:16.749 [Webasto falsa] [01:34:25.673] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:16.821 [Webasto falsa] [01:34:25.748] > «sigo con la orden»
16:34:17.267 [Webasto falsa] [01:34:26.194] < pide los sensores (0x50 05)
16:34:17.378 [Webasto falsa] [01:34:26.302] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:21.749 [Webasto falsa] [01:34:30.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:21.821 [Webasto falsa] [01:34:30.748] > «sigo con la orden»
16:34:25.399 [Webasto falsa] [01:34:34.326] < pide los sensores (0x50 05)
16:34:25.510 [Webasto falsa] [01:34:34.434] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:26.749 [Webasto falsa] [01:34:35.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:26.820 [Webasto falsa] [01:34:35.748] > «sigo con la orden»
16:34:31.746 [Webasto falsa] [01:34:40.671] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:31.818 [Webasto falsa] [01:34:40.746] > «sigo con la orden»
16:34:33.531 [Webasto falsa] [01:34:42.458] < pide los sensores (0x50 05)
16:34:33.642 [Webasto falsa] [01:34:42.566] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:36.749 [Webasto falsa] [01:34:45.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:36.820 [Webasto falsa] [01:34:45.748] > «sigo con la orden»
16:34:41.663 [Webasto falsa] [01:34:50.590] < pide los sensores (0x50 05)
16:34:41.773 [Webasto falsa] [01:34:50.698] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:41.807 [Webasto falsa] [01:34:50.730] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:34:41.878 [Webasto falsa] [01:34:50.806] > «sigo con la orden»
16:34:46.807 [Webasto falsa] [01:34:55.731] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:46.878 [Webasto falsa] [01:34:55.806] > «sigo con la orden»
16:34:49.795 [Webasto falsa] [01:34:58.722] < pide los sensores (0x50 05)
16:34:49.905 [Webasto falsa] [01:34:58.830] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:51.807 [Webasto falsa] [01:35:00.730] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:51.879 [Webasto falsa] [01:35:00.806] > «sigo con la orden»
16:34:56.806 [Webasto falsa] [01:35:05.731] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:56.878 [Webasto falsa] [01:35:05.806] > «sigo con la orden»
16:34:57.926 [Webasto falsa] [01:35:06.854] < pide los sensores (0x50 05)
16:34:58.037 [Webasto falsa] [01:35:06.962] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:35:00.192 [Webasto falsa] [01:35:09.116] . estado: carga parcial → postbarrido (se acabó el tiempo pedido)
16:35:00.254 [Webasto falsa] [01:35:09.174] = postbarrido · agua 74.5 °C · dentro 17.6 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
16:35:00.265 [Webasto falsa] [01:35:09.175] =   orden 0x21: quedan 0 s · último mantenimiento hace 3.4 s (a los 20 s se apaga)
16:35:00.276 [Webasto falsa] [01:35:09.186] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:35:00.279 [Webasto falsa] [01:35:09.197] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:35:00.300 [Webasto falsa] [01:35:09.226] < apagar
16:35:00.310 [Webasto falsa] [01:35:09.227] . apagar: ya estaba postbarrido, no cambia nada
16:35:00.365 [Webasto falsa] [01:35:09.293] > «apagando» → postbarrido
16:35:00.400 [WTTC] 17/10 16:35  Apagada (fin de tiempo) · gasoil ≈ 0,22 l
16:35:06.058 [Webasto falsa] [01:35:14.985] < pide los sensores (0x50 05)
16:35:06.168 [Webasto falsa] [01:35:15.093] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.55 Ω
16:35:14.191 [Webasto falsa] [01:35:23.119] < pide los sensores (0x50 05)
16:35:14.302 [Webasto falsa] [01:35:23.227] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.55 Ω
16:35:22.323 [Webasto falsa] [01:35:31.251] < pide los sensores (0x50 05)
16:35:22.434 [Webasto falsa] [01:35:31.359] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:30.458 [Webasto falsa] [01:35:39.383] < pide los sensores (0x50 05)
16:35:30.566 [Webasto falsa] [01:35:39.491] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:38.587 [Webasto falsa] [01:35:47.515] < pide los sensores (0x50 05)
16:35:38.698 [Webasto falsa] [01:35:47.623] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:46.719 [Webasto falsa] [01:35:55.647] < pide los sensores (0x50 05)
16:35:46.830 [Webasto falsa] [01:35:55.755] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:54.851 [Webasto falsa] [01:36:03.779] < pide los sensores (0x50 05)
16:35:54.961 [Webasto falsa] [01:36:03.887] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:36:00.254 [Webasto falsa] [01:36:09.175] = postbarrido · agua 73.8 °C · dentro 17.6 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
16:36:00.265 [Webasto falsa] [01:36:09.176] =   orden 0x21: quedan 0 s · último mantenimiento hace 3.4 s (a los 20 s se apaga)
16:36:00.277 [Webasto falsa] [01:36:09.187] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:36:00.279 [Webasto falsa] [01:36:09.198] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:36:02.983 [Webasto falsa] [01:36:11.911] < pide los sensores (0x50 05)
16:36:03.093 [Webasto falsa] [01:36:12.019] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:36:11.114 [Webasto falsa] [01:36:20.043] < pide los sensores (0x50 05)
16:36:11.225 [Webasto falsa] [01:36:20.151] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
```

## Registro: WTTC

```text
15:42:16.387 » Preguntando quién es («info»)…
15:42:16.401 
15:42:16.406 ================================================================
15:42:16.412   WTTC 0.3.14 · control de la Webasto Thermo Top C · github.com/matatunos/wttc
15:42:16.417 ================================================================
15:42:16.423   Bluetooth (app WTTC) .. nombre "WTTC" · PIN ······
15:42:16.429   Wi-Fi propia .......... red "WTTC" · clave "••••"
15:42:16.434                           web http://192.168.4.1 · siempre encendida · se oculta estando en la red con internet
15:42:16.440   Red con internet ...... "WIFI" · conectada, http://192.168.1.194
15:42:16.451   Web desde esa red ..... http://wttc.local · usuario "wttc" · clave "••••"
15:42:16.451   Telegram .............. sin configurar
15:42:16.456   Mis estadísticas ...... código XXXX-XXXX-XXXX-XXXX · se envían
15:42:16.467                           https://wttc.favala.es/mi.php#XXXX-XXXX-XXXX-XXXX
15:42:16.468   Piezas opcionales ..... pantalla no · termómetro no
15:42:16.473   Botón «calentar» ...... IO7 a GND · 30 min
15:42:16.490   Este arranque ......... reinicio pedido (actualización, ajustes o la orden reboot) · memoria libre 92 KB · PSRAM 8165 KB (conexiones seguras ahí)
15:42:16.495   Órdenes remotas ....... permitidas (firmadas por el proyecto; nunca la calefacción)
15:42:16.501   Actualizaciones ....... buscar y avisar
15:42:16.506 ----------------------------------------------------------------
15:42:16.518   Órdenes: on [min] [°C] | off | status | info | errores | cfg | set clave=valor | wifi | forget | reboot | gasreset
15:42:16.524 ================================================================
15:42:53.367 17/10 15:42  Encendida (manual, 15 min)
15:57:53.559 17/10 15:57  Apagada (fin de tiempo) · gasoil ≈ 0,14 l
15:59:30.702 17/10 15:59  Salida programada: 18/10 08:00
16:01:08.238 17/10 16:01  Programas guardados (1)
16:04:24.138 17/10 16:04  Programas guardados (1)
16:04:24.251 17/10 16:04  Programas guardados (1)
16:04:24.312 17/10 16:04  Programas guardados (1)
16:04:24.345 17/10 16:04  Programas guardados (1)
16:05:00.291 17/10 16:05  Encendida (programa, 30 min)
16:12:38.694 17/10 16:12  Programas guardados (4)
16:12:45.339 17/10 16:12  Programas guardados (4)
16:14:25.601 17/10 16:14  Programas guardados (5)
16:35:00.400 17/10 16:35  Apagada (fin de tiempo) · gasoil ≈ 0,22 l
```

## Registro: Webasto falsa

```text
15:42:22.539 » Preguntando quién es («info»)…
15:42:22.548 [00:42:31.437] $ info
15:42:22.549 Órdenes de la consola:
15:42:22.558   estado            todo lo que tiene ahora: estado, agua, batería, orden, mantenimiento, averías, fallos
15:42:22.564   resumen           cuántas tramas han llegado, contestado, malas… y el estado
15:42:22.575   fuera <°C>        temperatura de fuera (cuánto tarda en calentar y en enfriarse)
15:42:22.581   bateria <V>       tensión de la batería sin carga (8–15 V)
15:42:22.586   fallo gasoil      no prende: avería 02 cada arranque; al tercero seguido, bloqueada (07)
15:42:22.592   fallo llama       se le apaga la llama calentando: avería 03
15:42:22.598   fallo corriente   sin corriente (fusible): no contesta a nada
15:42:22.603   fallo mudo        el W-Bus no contesta (cable suelto)
15:42:22.608   fallo ninguno     quita los fallos provocados
15:42:22.614   borrar            borra las averías y la desbloquea
15:42:22.615   rapido <1..60>    el tiempo va N veces más deprisa
15:42:22.625   log <0..3>        0 importante · 1 + órdenes (de fábrica) · 2 + bytes · 3 + estado cada 10 s
15:42:22.631   traza on|off      atajo: log 2 / log 0
15:42:22.636   eco si|no         sí: conexión directa (A) · no: por el bus con TJA1020 (B)
15:42:22.640   pines <rx> <tx>   pines del W-Bus (de fábrica 16 17)
15:42:22.640   reiniciar
15:42:49.205 [00:42:58.096] < pide los sensores (0x50 05)
15:42:49.315 [00:42:58.204] > agua 5 °C · 12.60 V · llama no · 0 W · bujía 1.13 Ω
15:42:53.285 [00:43:02.174] < encender 15 min
15:42:53.295 [00:43:02.175] . encender 15 min: aceptado (arranque nº 1; mantenimiento cada <20 s o se apaga)
15:42:53.296 [00:43:02.175] . estado: apagada → ventilador (empieza el arranque)
15:42:53.368 [00:43:02.257] > «vale, 15 min»
15:42:53.378 [00:43:02.258] = ventilador · agua 5.0 °C · dentro 5.0 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
15:42:53.384 [00:43:02.269] =   orden 0x21: quedan 900 s · último mantenimiento hace 0.0 s (a los 20 s se apaga)
15:42:53.395 [00:43:02.269] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:42:53.401 [00:43:02.280] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:42:57.338 [00:43:06.229] < pide los sensores (0x50 05)
15:42:57.448 [00:43:06.337] > agua 5 °C · 12.48 V · llama no · 0 W · bujía 1.13 Ω
15:42:58.421 [00:43:07.311] < mantenimiento: «sigue con la orden 0x21»
15:42:58.495 [00:43:07.387] > «sigo con la orden»
15:43:03.423 [00:43:12.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:03.495 [00:43:12.387] > «sigo con la orden»
15:43:05.287 [00:43:14.175] . estado: ventilador → bujía (ventilador listo: calienta la bujía)
15:43:05.470 [00:43:14.361] < pide los sensores (0x50 05)
15:43:05.580 [00:43:14.469] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:08.423 [00:43:17.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:08.495 [00:43:17.387] > «sigo con la orden»
15:43:13.423 [00:43:22.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:13.495 [00:43:22.387] > «sigo con la orden»
15:43:13.602 [00:43:22.493] < pide los sensores (0x50 05)
15:43:13.712 [00:43:22.601] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:18.423 [00:43:27.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:18.495 [00:43:27.387] > «sigo con la orden»
15:43:21.734 [00:43:30.625] < pide los sensores (0x50 05)
15:43:21.843 [00:43:30.733] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:23.423 [00:43:32.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:23.494 [00:43:32.387] > «sigo con la orden»
15:43:28.423 [00:43:37.311] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:28.495 [00:43:37.387] > «sigo con la orden»
15:43:29.866 [00:43:38.757] < pide los sensores (0x50 05)
15:43:29.975 [00:43:38.865] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:33.425 [00:43:42.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:33.496 [00:43:42.389] > «sigo con la orden»
15:43:37.997 [00:43:46.889] < pide los sensores (0x50 05)
15:43:38.108 [00:43:46.997] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:38.425 [00:43:47.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:38.497 [00:43:47.389] > «sigo con la orden»
15:43:43.425 [00:43:52.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:43.497 [00:43:52.389] > «sigo con la orden»
15:43:46.129 [00:43:55.021] < pide los sensores (0x50 05)
15:43:46.240 [00:43:55.129] > agua 5 °C · 12.09 V · llama no · 0 W · bujía 1.13 Ω
15:43:48.425 [00:43:57.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:48.497 [00:43:57.389] > «sigo con la orden»
15:43:50.287 [00:43:59.175] . estado: bujía → encendido (bujía caliente: bomba de gasoil en marcha)
15:43:53.290 [00:44:02.175] = encendido · agua 5.0 °C · dentro 5.0 °C · fuera 5.0 °C · 12.06 V · llama no · 0 W
15:43:53.302 [00:44:02.176] =   orden 0x21: quedan 839 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:43:53.307 [00:44:02.187] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:43:53.315 [00:44:02.198] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:43:53.425 [00:44:02.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:53.497 [00:44:02.389] > «sigo con la orden»
15:43:54.263 [00:44:03.155] < pide los sensores (0x50 05)
15:43:54.374 [00:44:03.263] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:43:58.425 [00:44:07.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:43:58.497 [00:44:07.389] > «sigo con la orden»
15:44:02.395 [00:44:11.287] < pide los sensores (0x50 05)
15:44:02.505 [00:44:11.395] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:44:03.424 [00:44:12.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:03.496 [00:44:12.389] > «sigo con la orden»
15:44:08.424 [00:44:17.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:08.496 [00:44:17.389] > «sigo con la orden»
15:44:10.527 [00:44:19.419] < pide los sensores (0x50 05)
15:44:10.637 [00:44:19.527] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:44:13.424 [00:44:22.313] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:13.496 [00:44:22.389] > «sigo con la orden»
15:44:18.426 [00:44:27.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:18.498 [00:44:27.391] > «sigo con la orden»
15:44:18.661 [00:44:27.553] < pide los sensores (0x50 05)
15:44:18.771 [00:44:27.661] > agua 5 °C · 12.06 V · llama no · 0 W · bujía 1.13 Ω
15:44:20.284 [00:44:29.175] . estado: encendido → estabilizando (¡llama!)
15:44:23.426 [00:44:32.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:23.498 [00:44:32.391] > «sigo con la orden»
15:44:26.793 [00:44:35.685] < pide los sensores (0x50 05)
15:44:26.903 [00:44:35.793] > agua 5 °C · 12.36 V · llama sí · 2771 W · bujía 1.13 Ω
15:44:28.426 [00:44:37.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:28.498 [00:44:37.391] > «sigo con la orden»
15:44:33.426 [00:44:42.316] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:33.498 [00:44:42.391] > «sigo con la orden»
15:44:34.925 [00:44:43.817] < pide los sensores (0x50 05)
15:44:35.035 [00:44:43.925] > agua 6 °C · 12.36 V · llama sí · 3110 W · bujía 1.13 Ω
15:44:38.426 [00:44:47.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:38.498 [00:44:47.391] > «sigo con la orden»
15:44:43.057 [00:44:51.949] < pide los sensores (0x50 05)
15:44:43.167 [00:44:52.057] > agua 6 °C · 12.36 V · llama sí · 3448 W · bujía 1.13 Ω
15:44:43.426 [00:44:52.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:43.498 [00:44:52.391] > «sigo con la orden»
15:44:48.426 [00:44:57.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:48.498 [00:44:57.391] > «sigo con la orden»
15:44:51.189 [00:45:00.081] < pide los sensores (0x50 05)
15:44:51.299 [00:45:00.189] > agua 6 °C · 12.36 V · llama sí · 3787 W · bujía 1.14 Ω
15:44:53.291 [00:45:02.177] = estabilizando · agua 6.4 °C · dentro 5.0 °C · fuera 5.0 °C · 12.36 V · llama sí · 3875 W
15:44:53.303 [00:45:02.178] =   orden 0x21: quedan 779 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:44:53.314 [00:45:02.189] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:44:53.317 [00:45:02.200] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:44:53.426 [00:45:02.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:53.498 [00:45:02.391] > «sigo con la orden»
15:44:58.426 [00:45:07.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:44:58.498 [00:45:07.391] > «sigo con la orden»
15:44:59.321 [00:45:08.213] < pide los sensores (0x50 05)
15:44:59.431 [00:45:08.321] > agua 7 °C · 12.36 V · llama sí · 4126 W · bujía 1.14 Ω
15:45:03.426 [00:45:12.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:03.497 [00:45:12.391] > «sigo con la orden»
15:45:07.452 [00:45:16.345] < pide los sensores (0x50 05)
15:45:07.563 [00:45:16.453] > agua 7 °C · 12.36 V · llama sí · 4465 W · bujía 1.14 Ω
15:45:08.425 [00:45:17.315] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:08.497 [00:45:17.391] > «sigo con la orden»
15:45:13.428 [00:45:22.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:13.499 [00:45:22.393] > «sigo con la orden»
15:45:15.584 [00:45:24.477] < pide los sensores (0x50 05)
15:45:15.695 [00:45:24.585] > agua 8 °C · 12.36 V · llama sí · 4804 W · bujía 1.14 Ω
15:45:18.428 [00:45:27.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:18.499 [00:45:27.393] > «sigo con la orden»
15:45:20.283 [00:45:29.175] . estado: estabilizando → a tope (llama estable)
15:45:23.427 [00:45:32.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:23.499 [00:45:32.393] > «sigo con la orden»
15:45:23.716 [00:45:32.609] < pide los sensores (0x50 05)
15:45:23.827 [00:45:32.717] > agua 8 °C · 12.39 V · llama sí · 5000 W · bujía 1.15 Ω
15:45:28.427 [00:45:37.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:28.499 [00:45:37.393] > «sigo con la orden»
15:45:31.848 [00:45:40.741] < pide los sensores (0x50 05)
15:45:31.959 [00:45:40.849] > agua 9 °C · 12.39 V · llama sí · 5000 W · bujía 1.15 Ω
15:45:33.427 [00:45:42.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:33.499 [00:45:42.393] > «sigo con la orden»
15:45:38.428 [00:45:47.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:38.499 [00:45:47.393] > «sigo con la orden»
15:45:39.980 [00:45:48.873] < pide los sensores (0x50 05)
15:45:40.091 [00:45:48.981] > agua 9 °C · 12.39 V · llama sí · 5000 W · bujía 1.15 Ω
15:45:43.427 [00:45:52.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:43.499 [00:45:52.393] > «sigo con la orden»
15:45:48.112 [00:45:57.005] < pide los sensores (0x50 05)
15:45:48.222 [00:45:57.113] > agua 10 °C · 12.39 V · llama sí · 5000 W · bujía 1.16 Ω
15:45:48.427 [00:45:57.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:48.499 [00:45:57.393] > «sigo con la orden»
15:45:53.293 [00:46:02.179] = a tope · agua 10.1 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:45:53.304 [00:46:02.180] =   orden 0x21: quedan 719 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:45:53.315 [00:46:02.191] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:45:53.318 [00:46:02.202] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:45:53.427 [00:46:02.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:53.499 [00:46:02.393] > «sigo con la orden»
15:45:56.244 [00:46:05.137] < pide los sensores (0x50 05)
15:45:56.354 [00:46:05.245] > agua 10 °C · 12.39 V · llama sí · 5000 W · bujía 1.16 Ω
15:45:58.427 [00:46:07.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:45:58.499 [00:46:07.393] > «sigo con la orden»
15:46:03.427 [00:46:12.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:03.499 [00:46:12.393] > «sigo con la orden»
15:46:04.375 [00:46:13.269] < pide los sensores (0x50 05)
15:46:04.486 [00:46:13.377] > agua 11 °C · 12.39 V · llama sí · 5000 W · bujía 1.16 Ω
15:46:08.427 [00:46:17.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:08.499 [00:46:17.393] > «sigo con la orden»
15:46:12.507 [00:46:21.401] < pide los sensores (0x50 05)
15:46:12.618 [00:46:21.509] > agua 11 °C · 12.39 V · llama sí · 5000 W · bujía 1.17 Ω
15:46:13.427 [00:46:22.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:13.499 [00:46:22.393] > «sigo con la orden»
15:46:18.427 [00:46:27.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:18.498 [00:46:27.393] > «sigo con la orden»
15:46:20.639 [00:46:29.533] < pide los sensores (0x50 05)
15:46:20.750 [00:46:29.641] > agua 12 °C · 12.39 V · llama sí · 5000 W · bujía 1.17 Ω
15:46:23.427 [00:46:32.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:23.499 [00:46:32.393] > «sigo con la orden»
15:46:28.426 [00:46:37.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:28.498 [00:46:37.393] > «sigo con la orden»
15:46:28.771 [00:46:37.665] < pide los sensores (0x50 05)
15:46:28.882 [00:46:37.773] > agua 12 °C · 12.39 V · llama sí · 5000 W · bujía 1.17 Ω
15:46:33.427 [00:46:42.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:33.499 [00:46:42.393] > «sigo con la orden»
15:46:36.903 [00:46:45.797] < pide los sensores (0x50 05)
15:46:37.014 [00:46:45.905] > agua 13 °C · 12.39 V · llama sí · 5000 W · bujía 1.18 Ω
15:46:38.427 [00:46:47.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:38.498 [00:46:47.393] > «sigo con la orden»
15:46:43.427 [00:46:52.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:43.498 [00:46:52.393] > «sigo con la orden»
15:46:45.035 [00:46:53.929] < pide los sensores (0x50 05)
15:46:45.146 [00:46:54.037] > agua 13 °C · 12.39 V · llama sí · 5000 W · bujía 1.18 Ω
15:46:48.427 [00:46:57.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:48.498 [00:46:57.393] > «sigo con la orden»
15:46:53.167 [00:47:02.061] < pide los sensores (0x50 05)
15:46:53.278 [00:47:02.169] > agua 14 °C · 12.39 V · llama sí · 5000 W · bujía 1.18 Ω
15:46:53.294 [00:47:02.181] = a tope · agua 13.9 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:46:53.305 [00:47:02.182] =   orden 0x21: quedan 659 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:46:53.316 [00:47:02.193] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:46:53.319 [00:47:02.204] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:46:53.427 [00:47:02.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:53.498 [00:47:02.393] > «sigo con la orden»
15:46:58.427 [00:47:07.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:46:58.498 [00:47:07.393] > «sigo con la orden»
15:47:01.299 [00:47:10.193] < pide los sensores (0x50 05)
15:47:01.410 [00:47:10.301] > agua 14 °C · 12.39 V · llama sí · 5000 W · bujía 1.19 Ω
15:47:03.427 [00:47:12.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:03.498 [00:47:12.393] > «sigo con la orden»
15:47:08.426 [00:47:17.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:08.498 [00:47:17.393] > «sigo con la orden»
15:47:09.431 [00:47:18.325] < pide los sensores (0x50 05)
15:47:09.541 [00:47:18.433] > agua 15 °C · 12.39 V · llama sí · 5000 W · bujía 1.19 Ω
15:47:13.426 [00:47:22.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:13.498 [00:47:22.393] > «sigo con la orden»
15:47:17.563 [00:47:26.457] < pide los sensores (0x50 05)
15:47:17.673 [00:47:26.565] > agua 15 °C · 12.39 V · llama sí · 5000 W · bujía 1.19 Ω
15:47:18.426 [00:47:27.317] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:18.498 [00:47:27.393] > «sigo con la orden»
15:47:23.428 [00:47:32.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:23.500 [00:47:32.395] > «sigo con la orden»
15:47:25.694 [00:47:34.589] < pide los sensores (0x50 05)
15:47:25.805 [00:47:34.697] > agua 16 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:28.428 [00:47:37.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:28.500 [00:47:37.395] > «sigo con la orden»
15:47:33.428 [00:47:42.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:33.500 [00:47:42.395] > «sigo con la orden»
15:47:33.826 [00:47:42.721] < pide los sensores (0x50 05)
15:47:33.937 [00:47:42.829] > agua 16 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:38.428 [00:47:47.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:38.499 [00:47:47.395] > «sigo con la orden»
15:47:41.960 [00:47:50.855] < pide los sensores (0x50 05)
15:47:42.071 [00:47:50.963] > agua 17 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:43.428 [00:47:52.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:43.500 [00:47:52.395] > «sigo con la orden»
15:47:48.428 [00:47:57.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:48.500 [00:47:57.395] > «sigo con la orden»
15:47:50.092 [00:47:58.987] < pide los sensores (0x50 05)
15:47:50.203 [00:47:59.095] > agua 17 °C · 12.39 V · llama sí · 5000 W · bujía 1.20 Ω
15:47:53.295 [00:48:02.183] = a tope · agua 17.6 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:47:53.306 [00:48:02.184] =   orden 0x21: quedan 599 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:47:53.317 [00:48:02.195] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:47:53.320 [00:48:02.206] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:47:53.427 [00:48:02.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:53.499 [00:48:02.395] > «sigo con la orden»
15:47:58.226 [00:48:07.121] < pide los sensores (0x50 05)
15:47:58.337 [00:48:07.229] > agua 18 °C · 12.39 V · llama sí · 5000 W · bujía 1.21 Ω
15:47:58.428 [00:48:07.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:47:58.500 [00:48:07.395] > «sigo con la orden»
15:48:03.427 [00:48:12.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:03.499 [00:48:12.395] > «sigo con la orden»
15:48:06.358 [00:48:15.253] < pide los sensores (0x50 05)
15:48:06.469 [00:48:15.361] > agua 18 °C · 12.39 V · llama sí · 5000 W · bujía 1.21 Ω
15:48:08.427 [00:48:17.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:08.499 [00:48:17.395] > «sigo con la orden»
15:48:13.427 [00:48:22.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:13.499 [00:48:22.395] > «sigo con la orden»
15:48:14.489 [00:48:23.385] < pide los sensores (0x50 05)
15:48:14.601 [00:48:23.493] > agua 19 °C · 12.39 V · llama sí · 5000 W · bujía 1.21 Ω
15:48:18.427 [00:48:27.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:18.499 [00:48:27.395] > «sigo con la orden»
15:48:22.622 [00:48:31.517] < pide los sensores (0x50 05)
15:48:22.733 [00:48:31.625] > agua 19 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:23.427 [00:48:32.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:23.499 [00:48:32.395] > «sigo con la orden»
15:48:28.427 [00:48:37.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:28.499 [00:48:37.395] > «sigo con la orden»
15:48:30.756 [00:48:39.651] < pide los sensores (0x50 05)
15:48:30.866 [00:48:39.759] > agua 20 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:33.427 [00:48:42.319] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:33.499 [00:48:42.395] > «sigo con la orden»
15:48:38.429 [00:48:47.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:38.501 [00:48:47.397] > «sigo con la orden»
15:48:38.888 [00:48:47.783] < pide los sensores (0x50 05)
15:48:38.998 [00:48:47.891] > agua 20 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:43.429 [00:48:52.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:43.501 [00:48:52.397] > «sigo con la orden»
15:48:47.020 [00:48:55.915] < pide los sensores (0x50 05)
15:48:47.130 [00:48:56.023] > agua 21 °C · 12.39 V · llama sí · 5000 W · bujía 1.22 Ω
15:48:48.429 [00:48:57.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:48.501 [00:48:57.397] > «sigo con la orden»
15:48:53.296 [00:49:02.185] = a tope · agua 21.1 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:48:53.308 [00:49:02.186] =   orden 0x21: quedan 539 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:48:53.318 [00:49:02.197] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:48:53.322 [00:49:02.208] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:48:53.429 [00:49:02.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:53.501 [00:49:02.397] > «sigo con la orden»
15:48:55.152 [00:49:04.047] < pide los sensores (0x50 05)
15:48:55.262 [00:49:04.155] > agua 21 °C · 12.39 V · llama sí · 5000 W · bujía 1.23 Ω
15:48:58.429 [00:49:07.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:48:58.501 [00:49:07.397] > «sigo con la orden»
15:49:03.284 [00:49:12.179] < pide los sensores (0x50 05)
15:49:03.394 [00:49:12.287] > agua 22 °C · 12.39 V · llama sí · 5000 W · bujía 1.23 Ω
15:49:03.429 [00:49:12.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:03.500 [00:49:12.397] > «sigo con la orden»
15:49:08.429 [00:49:17.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:08.500 [00:49:17.397] > «sigo con la orden»
15:49:11.415 [00:49:20.311] < pide los sensores (0x50 05)
15:49:11.526 [00:49:20.419] > agua 22 °C · 12.39 V · llama sí · 5000 W · bujía 1.23 Ω
15:49:13.429 [00:49:22.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:13.500 [00:49:22.397] > «sigo con la orden»
15:49:18.429 [00:49:27.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:18.500 [00:49:27.397] > «sigo con la orden»
15:49:19.547 [00:49:28.443] < pide los sensores (0x50 05)
15:49:19.658 [00:49:28.551] > agua 23 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:23.429 [00:49:32.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:23.500 [00:49:32.397] > «sigo con la orden»
15:49:27.679 [00:49:36.575] < pide los sensores (0x50 05)
15:49:27.790 [00:49:36.683] > agua 23 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:28.428 [00:49:37.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:28.500 [00:49:37.397] > «sigo con la orden»
15:49:33.429 [00:49:42.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:33.500 [00:49:42.397] > «sigo con la orden»
15:49:35.811 [00:49:44.707] < pide los sensores (0x50 05)
15:49:35.921 [00:49:44.815] > agua 24 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:38.429 [00:49:47.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:38.500 [00:49:47.397] > «sigo con la orden»
15:49:43.428 [00:49:52.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:43.500 [00:49:52.397] > «sigo con la orden»
15:49:43.943 [00:49:52.839] < pide los sensores (0x50 05)
15:49:44.054 [00:49:52.947] > agua 24 °C · 12.39 V · llama sí · 5000 W · bujía 1.24 Ω
15:49:48.428 [00:49:57.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:48.500 [00:49:57.397] > «sigo con la orden»
15:49:52.075 [00:50:00.971] < pide los sensores (0x50 05)
15:49:52.186 [00:50:01.079] > agua 25 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:49:53.298 [00:50:02.187] = a tope · agua 24.6 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:49:53.309 [00:50:02.188] =   orden 0x21: quedan 479 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
15:49:53.320 [00:50:02.199] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:49:53.323 [00:50:02.210] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:49:53.428 [00:50:02.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:53.500 [00:50:02.397] > «sigo con la orden»
15:49:58.428 [00:50:07.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:49:58.500 [00:50:07.397] > «sigo con la orden»
15:50:00.207 [00:50:09.103] < pide los sensores (0x50 05)
15:50:00.318 [00:50:09.211] > agua 25 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:50:03.428 [00:50:12.321] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:03.500 [00:50:12.397] > «sigo con la orden»
15:50:08.338 [00:50:17.235] < pide los sensores (0x50 05)
15:50:08.449 [00:50:17.343] > agua 25 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:50:08.482 [00:50:17.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:50:08.554 [00:50:17.451] > «sigo con la orden»
15:50:13.482 [00:50:22.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:13.554 [00:50:22.451] > «sigo con la orden»
15:50:16.470 [00:50:25.367] < pide los sensores (0x50 05)
15:50:16.581 [00:50:25.475] > agua 26 °C · 12.39 V · llama sí · 5000 W · bujía 1.25 Ω
15:50:18.482 [00:50:27.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:18.554 [00:50:27.451] > «sigo con la orden»
15:50:23.482 [00:50:32.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:23.554 [00:50:32.451] > «sigo con la orden»
15:50:24.602 [00:50:33.499] < pide los sensores (0x50 05)
15:50:24.713 [00:50:33.607] > agua 26 °C · 12.39 V · llama sí · 5000 W · bujía 1.26 Ω
15:50:28.482 [00:50:37.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:28.554 [00:50:37.451] > «sigo con la orden»
15:50:32.736 [00:50:41.633] < pide los sensores (0x50 05)
15:50:32.847 [00:50:41.741] > agua 27 °C · 12.39 V · llama sí · 5000 W · bujía 1.26 Ω
15:50:33.482 [00:50:42.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:33.554 [00:50:42.451] > «sigo con la orden»
15:50:38.482 [00:50:47.375] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:38.553 [00:50:47.451] > «sigo con la orden»
15:50:40.868 [00:50:49.765] < pide los sensores (0x50 05)
15:50:40.979 [00:50:49.873] > agua 27 °C · 12.39 V · llama sí · 5000 W · bujía 1.26 Ω
15:50:43.484 [00:50:52.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:43.556 [00:50:52.453] > «sigo con la orden»
15:50:48.484 [00:50:57.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:48.555 [00:50:57.453] > «sigo con la orden»
15:50:49.000 [00:50:57.897] < pide los sensores (0x50 05)
15:50:49.110 [00:50:58.005] > agua 28 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:50:53.299 [00:51:02.189] = a tope · agua 27.9 °C · dentro 5.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:50:53.310 [00:51:02.190] =   orden 0x21: quedan 419 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
15:50:53.321 [00:51:02.201] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:50:53.324 [00:51:02.212] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:50:53.484 [00:51:02.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:53.555 [00:51:02.453] > «sigo con la orden»
15:50:57.132 [00:51:06.029] < pide los sensores (0x50 05)
15:50:57.243 [00:51:06.137] > agua 28 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:50:58.484 [00:51:07.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:50:58.555 [00:51:07.453] > «sigo con la orden»
15:51:03.484 [00:51:12.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:03.555 [00:51:12.453] > «sigo con la orden»
15:51:05.263 [00:51:14.161] < pide los sensores (0x50 05)
15:51:05.374 [00:51:14.269] > agua 29 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:51:08.483 [00:51:17.377] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:08.555 [00:51:17.453] > «sigo con la orden»
15:51:13.396 [00:51:22.293] < pide los sensores (0x50 05)
15:51:13.506 [00:51:22.401] > agua 29 °C · 12.39 V · llama sí · 5000 W · bujía 1.27 Ω
15:51:13.539 [00:51:22.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:51:13.611 [00:51:22.509] > «sigo con la orden»
15:51:18.539 [00:51:27.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:18.611 [00:51:27.509] > «sigo con la orden»
15:51:21.528 [00:51:30.425] < pide los sensores (0x50 05)
15:51:21.638 [00:51:30.533] > agua 29 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:23.539 [00:51:32.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:23.611 [00:51:32.509] > «sigo con la orden»
15:51:28.539 [00:51:37.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:28.611 [00:51:37.509] > «sigo con la orden»
15:51:29.659 [00:51:38.557] < pide los sensores (0x50 05)
15:51:29.770 [00:51:38.665] > agua 30 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:33.539 [00:51:42.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:33.610 [00:51:42.509] > «sigo con la orden»
15:51:37.793 [00:51:46.691] < pide los sensores (0x50 05)
15:51:37.904 [00:51:46.799] > agua 30 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:38.539 [00:51:47.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:38.611 [00:51:47.509] > «sigo con la orden»
15:51:43.539 [00:51:52.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:43.611 [00:51:52.509] > «sigo con la orden»
15:51:45.927 [00:51:54.825] < pide los sensores (0x50 05)
15:51:46.038 [00:51:54.933] > agua 31 °C · 12.39 V · llama sí · 5000 W · bujía 1.28 Ω
15:51:48.539 [00:51:57.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:48.610 [00:51:57.509] > «sigo con la orden»
15:51:53.300 [00:52:02.191] = a tope · agua 31.2 °C · dentro 5.1 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:51:53.312 [00:52:02.192] =   orden 0x21: quedan 359 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
15:51:53.322 [00:52:02.203] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:51:53.325 [00:52:02.214] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:51:53.539 [00:52:02.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:53.611 [00:52:02.509] > «sigo con la orden»
15:51:54.059 [00:52:02.957] < pide los sensores (0x50 05)
15:51:54.170 [00:52:03.065] > agua 31 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:51:58.539 [00:52:07.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:51:58.611 [00:52:07.509] > «sigo con la orden»
15:52:02.191 [00:52:11.089] < pide los sensores (0x50 05)
15:52:02.302 [00:52:11.197] > agua 32 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:52:03.539 [00:52:12.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:03.610 [00:52:12.509] > «sigo con la orden»
15:52:08.538 [00:52:17.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:08.610 [00:52:17.509] > «sigo con la orden»
15:52:10.321 [00:52:19.219] < pide los sensores (0x50 05)
15:52:10.432 [00:52:19.327] > agua 32 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:52:13.538 [00:52:22.433] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:13.610 [00:52:22.509] > «sigo con la orden»
15:52:18.453 [00:52:27.351] < pide los sensores (0x50 05)
15:52:18.564 [00:52:27.459] > agua 33 °C · 12.39 V · llama sí · 5000 W · bujía 1.29 Ω
15:52:18.594 [00:52:27.489] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:52:18.666 [00:52:27.565] > «sigo con la orden»
15:52:23.597 [00:52:32.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:23.668 [00:52:32.567] > «sigo con la orden»
15:52:26.585 [00:52:35.483] < pide los sensores (0x50 05)
15:52:26.696 [00:52:35.591] > agua 33 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:28.596 [00:52:37.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:28.668 [00:52:37.567] > «sigo con la orden»
15:52:33.596 [00:52:42.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:33.668 [00:52:42.567] > «sigo con la orden»
15:52:34.717 [00:52:43.615] < pide los sensores (0x50 05)
15:52:34.828 [00:52:43.723] > agua 33 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:38.596 [00:52:47.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:38.668 [00:52:47.567] > «sigo con la orden»
15:52:42.848 [00:52:51.747] < pide los sensores (0x50 05)
15:52:42.959 [00:52:51.855] > agua 34 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:43.596 [00:52:52.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:43.668 [00:52:52.567] > «sigo con la orden»
15:52:48.596 [00:52:57.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:48.668 [00:52:57.567] > «sigo con la orden»
15:52:50.981 [00:52:59.879] < pide los sensores (0x50 05)
15:52:51.091 [00:52:59.987] > agua 34 °C · 12.39 V · llama sí · 5000 W · bujía 1.30 Ω
15:52:53.302 [00:53:02.193] = a tope · agua 34.3 °C · dentro 5.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:52:53.313 [00:53:02.194] =   orden 0x21: quedan 299 s · último mantenimiento hace 4.7 s (a los 20 s se apaga)
15:52:53.324 [00:53:02.205] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:52:53.327 [00:53:02.216] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:52:53.596 [00:53:02.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:53.668 [00:53:02.567] > «sigo con la orden»
15:52:58.596 [00:53:07.491] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:52:58.668 [00:53:07.567] > «sigo con la orden»
15:52:59.113 [00:53:08.011] < pide los sensores (0x50 05)
15:52:59.223 [00:53:08.119] > agua 35 °C · 12.39 V · llama sí · 5000 W · bujía 1.31 Ω
15:53:03.598 [00:53:12.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:03.670 [00:53:12.569] > «sigo con la orden»
15:53:07.244 [00:53:16.143] < pide los sensores (0x50 05)
15:53:07.355 [00:53:16.251] > agua 35 °C · 12.39 V · llama sí · 5000 W · bujía 1.31 Ω
15:53:08.598 [00:53:17.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:08.669 [00:53:17.569] > «sigo con la orden»
15:53:13.598 [00:53:22.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:13.669 [00:53:22.569] > «sigo con la orden»
15:53:15.376 [00:53:24.275] < pide los sensores (0x50 05)
15:53:15.486 [00:53:24.383] > agua 35 °C · 12.39 V · llama sí · 5000 W · bujía 1.31 Ω
15:53:18.598 [00:53:27.493] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:18.669 [00:53:27.569] > «sigo con la orden»
15:53:23.508 [00:53:32.407] < pide los sensores (0x50 05)
15:53:23.619 [00:53:32.515] > agua 36 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:23.652 [00:53:32.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:53:23.723 [00:53:32.623] > «sigo con la orden»
15:53:28.652 [00:53:37.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:28.723 [00:53:37.623] > «sigo con la orden»
15:53:31.640 [00:53:40.539] < pide los sensores (0x50 05)
15:53:31.751 [00:53:40.647] > agua 36 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:33.652 [00:53:42.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:33.723 [00:53:42.623] > «sigo con la orden»
15:53:38.652 [00:53:47.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:38.723 [00:53:47.623] > «sigo con la orden»
15:53:39.772 [00:53:48.671] < pide los sensores (0x50 05)
15:53:39.883 [00:53:48.779] > agua 37 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:43.652 [00:53:52.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:43.723 [00:53:52.623] > «sigo con la orden»
15:53:47.904 [00:53:56.803] < pide los sensores (0x50 05)
15:53:48.015 [00:53:56.911] > agua 37 °C · 12.39 V · llama sí · 5000 W · bujía 1.32 Ω
15:53:48.651 [00:53:57.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:48.723 [00:53:57.623] > «sigo con la orden»
15:53:53.303 [00:54:02.195] = a tope · agua 37.4 °C · dentro 5.5 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:53:53.314 [00:54:02.196] =   orden 0x21: quedan 239 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
15:53:53.325 [00:54:02.207] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:53:53.328 [00:54:02.218] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:53:53.651 [00:54:02.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:53.723 [00:54:02.623] > «sigo con la orden»
15:53:56.036 [00:54:04.935] < pide los sensores (0x50 05)
15:53:56.147 [00:54:05.043] > agua 38 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:53:58.651 [00:54:07.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:53:58.723 [00:54:07.623] > «sigo con la orden»
15:54:03.651 [00:54:12.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:03.723 [00:54:12.623] > «sigo con la orden»
15:54:04.167 [00:54:13.067] < pide los sensores (0x50 05)
15:54:04.278 [00:54:13.175] > agua 38 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:08.651 [00:54:17.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:08.723 [00:54:17.623] > «sigo con la orden»
15:54:12.299 [00:54:21.199] < pide los sensores (0x50 05)
15:54:12.410 [00:54:21.307] > agua 38 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:13.651 [00:54:22.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:13.723 [00:54:22.623] > «sigo con la orden»
15:54:18.651 [00:54:27.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:18.723 [00:54:27.623] > «sigo con la orden»
15:54:20.431 [00:54:29.331] < pide los sensores (0x50 05)
15:54:20.542 [00:54:29.439] > agua 39 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:23.651 [00:54:32.547] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:23.723 [00:54:32.623] > «sigo con la orden»
15:54:28.563 [00:54:37.463] < pide los sensores (0x50 05)
15:54:28.674 [00:54:37.571] > agua 39 °C · 12.39 V · llama sí · 5000 W · bujía 1.33 Ω
15:54:28.707 [00:54:37.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:54:28.779 [00:54:37.679] > «sigo con la orden»
15:54:33.707 [00:54:42.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:33.778 [00:54:42.679] > «sigo con la orden»
15:54:36.695 [00:54:45.595] < pide los sensores (0x50 05)
15:54:36.806 [00:54:45.703] > agua 40 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:54:38.707 [00:54:47.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:38.779 [00:54:47.679] > «sigo con la orden»
15:54:43.707 [00:54:52.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:43.779 [00:54:52.679] > «sigo con la orden»
15:54:44.827 [00:54:53.727] < pide los sensores (0x50 05)
15:54:44.938 [00:54:53.835] > agua 40 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:54:48.707 [00:54:57.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:48.778 [00:54:57.679] > «sigo con la orden»
15:54:52.959 [00:55:01.859] < pide los sensores (0x50 05)
15:54:53.070 [00:55:01.967] > agua 40 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:54:53.304 [00:55:02.197] = a tope · agua 40.4 °C · dentro 5.7 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:54:53.315 [00:55:02.198] =   orden 0x21: quedan 179 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
15:54:53.326 [00:55:02.209] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:54:53.329 [00:55:02.220] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:54:53.706 [00:55:02.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:53.778 [00:55:02.679] > «sigo con la orden»
15:54:58.707 [00:55:07.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:54:58.778 [00:55:07.679] > «sigo con la orden»
15:55:01.091 [00:55:09.991] < pide los sensores (0x50 05)
15:55:01.201 [00:55:10.099] > agua 41 °C · 12.39 V · llama sí · 5000 W · bujía 1.34 Ω
15:55:03.707 [00:55:12.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:03.778 [00:55:12.679] > «sigo con la orden»
15:55:08.706 [00:55:17.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:08.778 [00:55:17.679] > «sigo con la orden»
15:55:09.223 [00:55:18.123] < pide los sensores (0x50 05)
15:55:09.333 [00:55:18.231] > agua 41 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:13.706 [00:55:22.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:13.778 [00:55:22.679] > «sigo con la orden»
15:55:17.354 [00:55:26.255] < pide los sensores (0x50 05)
15:55:17.465 [00:55:26.363] > agua 42 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:18.706 [00:55:27.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:18.778 [00:55:27.679] > «sigo con la orden»
15:55:23.706 [00:55:32.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:23.778 [00:55:32.679] > «sigo con la orden»
15:55:25.486 [00:55:34.387] < pide los sensores (0x50 05)
15:55:25.597 [00:55:34.495] > agua 42 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:28.706 [00:55:37.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:28.778 [00:55:37.679] > «sigo con la orden»
15:55:33.618 [00:55:42.519] < pide los sensores (0x50 05)
15:55:33.729 [00:55:42.627] > agua 42 °C · 12.39 V · llama sí · 5000 W · bujía 1.35 Ω
15:55:33.760 [00:55:42.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:55:33.832 [00:55:42.733] > «sigo con la orden»
15:55:38.760 [00:55:47.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:38.832 [00:55:47.733] > «sigo con la orden»
15:55:41.750 [00:55:50.651] < pide los sensores (0x50 05)
15:55:41.861 [00:55:50.759] > agua 43 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:55:43.760 [00:55:52.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:43.831 [00:55:52.733] > «sigo con la orden»
15:55:48.760 [00:55:57.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:48.832 [00:55:57.733] > «sigo con la orden»
15:55:49.883 [00:55:58.783] < pide los sensores (0x50 05)
15:55:49.993 [00:55:58.891] > agua 43 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:55:53.306 [00:56:02.199] = a tope · agua 43.3 °C · dentro 6.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:55:53.317 [00:56:02.200] =   orden 0x21: quedan 119 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
15:55:53.328 [00:56:02.211] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:55:53.331 [00:56:02.222] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:55:53.760 [00:56:02.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:53.832 [00:56:02.733] > «sigo con la orden»
15:55:58.014 [00:56:06.915] < pide los sensores (0x50 05)
15:55:58.125 [00:56:07.023] > agua 43 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:55:58.760 [00:56:07.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:55:58.832 [00:56:07.733] > «sigo con la orden»
15:56:03.760 [00:56:12.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:03.831 [00:56:12.733] > «sigo con la orden»
15:56:06.146 [00:56:15.047] < pide los sensores (0x50 05)
15:56:06.257 [00:56:15.155] > agua 44 °C · 12.39 V · llama sí · 5000 W · bujía 1.36 Ω
15:56:08.759 [00:56:17.657] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:08.831 [00:56:17.733] > «sigo con la orden»
15:56:13.761 [00:56:22.659] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:13.833 [00:56:22.735] > «sigo con la orden»
15:56:14.278 [00:56:23.179] < pide los sensores (0x50 05)
15:56:14.389 [00:56:23.287] > agua 44 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:18.761 [00:56:27.659] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:18.833 [00:56:27.735] > «sigo con la orden»
15:56:22.410 [00:56:31.311] < pide los sensores (0x50 05)
15:56:22.521 [00:56:31.419] > agua 45 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:23.762 [00:56:32.659] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:23.833 [00:56:32.735] > «sigo con la orden»
15:56:28.763 [00:56:37.661] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:28.835 [00:56:37.737] > «sigo con la orden»
15:56:30.542 [00:56:39.443] < pide los sensores (0x50 05)
15:56:30.653 [00:56:39.551] > agua 45 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:33.763 [00:56:42.661] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:33.835 [00:56:42.737] > «sigo con la orden»
15:56:38.674 [00:56:47.575] < pide los sensores (0x50 05)
15:56:38.784 [00:56:47.683] > agua 45 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:38.817 [00:56:47.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:56:38.889 [00:56:47.791] > «sigo con la orden»
15:56:43.817 [00:56:52.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:43.889 [00:56:52.791] > «sigo con la orden»
15:56:46.806 [00:56:55.707] < pide los sensores (0x50 05)
15:56:46.916 [00:56:55.815] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.37 Ω
15:56:48.817 [00:56:57.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:48.889 [00:56:57.791] > «sigo con la orden»
15:56:53.307 [00:57:02.201] = a tope · agua 46.0 °C · dentro 6.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
15:56:53.318 [00:57:02.202] =   orden 0x21: quedan 59 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
15:56:53.324 [00:57:02.213] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:56:53.332 [00:57:02.224] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:56:53.817 [00:57:02.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:53.889 [00:57:02.791] > «sigo con la orden»
15:56:54.938 [00:57:03.839] < pide los sensores (0x50 05)
15:56:55.048 [00:57:03.947] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:56:58.817 [00:57:07.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:56:58.889 [00:57:07.791] > «sigo con la orden»
15:57:03.069 [00:57:11.971] < pide los sensores (0x50 05)
15:57:03.180 [00:57:12.079] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:03.817 [00:57:12.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:03.889 [00:57:12.791] > «sigo con la orden»
15:57:08.817 [00:57:17.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:08.889 [00:57:17.791] > «sigo con la orden»
15:57:11.201 [00:57:20.103] < pide los sensores (0x50 05)
15:57:11.312 [00:57:20.211] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:13.817 [00:57:22.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:13.889 [00:57:22.791] > «sigo con la orden»
15:57:18.817 [00:57:27.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:18.888 [00:57:27.791] > «sigo con la orden»
15:57:19.333 [00:57:28.235] < pide los sensores (0x50 05)
15:57:19.444 [00:57:28.343] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:23.816 [00:57:32.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:23.888 [00:57:32.791] > «sigo con la orden»
15:57:27.465 [00:57:36.367] < pide los sensores (0x50 05)
15:57:27.576 [00:57:36.475] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
15:57:28.817 [00:57:37.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:28.888 [00:57:37.791] > «sigo con la orden»
15:57:33.817 [00:57:42.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:33.888 [00:57:42.791] > «sigo con la orden»
15:57:35.597 [00:57:44.499] < pide los sensores (0x50 05)
15:57:35.708 [00:57:44.607] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
15:57:38.817 [00:57:47.715] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:38.888 [00:57:47.791] > «sigo con la orden»
15:57:43.729 [00:57:52.631] < pide los sensores (0x50 05)
15:57:43.840 [00:57:52.739] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
15:57:43.873 [00:57:52.771] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
15:57:43.944 [00:57:52.847] > «sigo con la orden»
15:57:48.873 [00:57:57.771] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
15:57:48.944 [00:57:57.847] > «sigo con la orden»
15:57:51.861 [00:58:00.763] < pide los sensores (0x50 05)
15:57:51.972 [00:58:00.871] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
15:57:53.275 [00:58:02.175] . estado: a tope → postbarrido (se acabó el tiempo pedido)
15:57:53.308 [00:58:02.203] = postbarrido · agua 48.8 °C · dentro 6.5 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
15:57:53.319 [00:58:02.204] =   orden 0x21: quedan 0 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
15:57:53.325 [00:58:02.215] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:57:53.333 [00:58:02.226] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:57:53.380 [00:58:02.281] < apagar
15:57:53.382 [00:58:02.282] . apagar: ya estaba postbarrido, no cambia nada
15:57:53.446 [00:58:02.348] > «apagando» → postbarrido
15:58:52.988 [00:59:01.890] < pide los sensores (0x50 05)
15:58:53.098 [00:59:01.998] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:58:53.308 [00:59:02.204] = postbarrido · agua 48.3 °C · dentro 6.5 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
15:58:53.319 [00:59:02.205] =   orden 0x21: quedan 0 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
15:58:53.325 [00:59:02.216] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
15:58:53.333 [00:59:02.227] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
15:59:01.121 [00:59:10.024] < pide los sensores (0x50 05)
15:59:01.231 [00:59:10.132] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:12.225 [00:59:21.128] < pide los sensores (0x50 05)
15:59:12.335 [00:59:21.236] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:20.357 [00:59:29.260] < pide los sensores (0x50 05)
15:59:20.467 [00:59:29.368] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:28.488 [00:59:37.392] < pide los sensores (0x50 05)
15:59:28.599 [00:59:37.500] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:36.620 [00:59:45.524] < pide los sensores (0x50 05)
15:59:36.730 [00:59:45.632] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:44.752 [00:59:53.656] < pide los sensores (0x50 05)
15:59:44.863 [00:59:53.764] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:52.884 [01:00:01.788] < pide los sensores (0x50 05)
15:59:52.995 [01:00:01.896] > agua 48 °C · 12.48 V · llama no · 0 W · bujía 1.39 Ω
15:59:53.276 [01:00:02.176] . estado: postbarrido → apagada (fin del postbarrido; suelta la orden)
16:00:01.040 [01:00:09.944] < pide los sensores (0x50 05)
16:00:01.150 [01:00:10.052] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.39 Ω
16:00:09.172 [01:00:18.076] < pide los sensores (0x50 05)
16:00:09.283 [01:00:18.184] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.39 Ω
16:00:17.304 [01:00:26.208] < pide los sensores (0x50 05)
16:00:17.415 [01:00:26.316] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:25.436 [01:00:34.340] < pide los sensores (0x50 05)
16:00:25.547 [01:00:34.448] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:33.568 [01:00:42.472] < pide los sensores (0x50 05)
16:00:33.678 [01:00:42.580] > agua 48 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:41.700 [01:00:50.604] < pide los sensores (0x50 05)
16:00:41.810 [01:00:50.712] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:49.832 [01:00:58.736] < pide los sensores (0x50 05)
16:00:49.942 [01:00:58.844] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:00:57.964 [01:01:06.868] < pide los sensores (0x50 05)
16:00:58.074 [01:01:06.976] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:06.096 [01:01:15.000] < pide los sensores (0x50 05)
16:01:06.206 [01:01:15.108] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:14.227 [01:01:23.132] < pide los sensores (0x50 05)
16:01:14.338 [01:01:23.240] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:22.359 [01:01:31.264] < pide los sensores (0x50 05)
16:01:22.470 [01:01:31.372] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:30.491 [01:01:39.396] < pide los sensores (0x50 05)
16:01:30.601 [01:01:39.504] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:38.623 [01:01:47.528] < pide los sensores (0x50 05)
16:01:38.733 [01:01:47.636] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:46.755 [01:01:55.660] < pide los sensores (0x50 05)
16:01:46.865 [01:01:55.768] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:01:54.887 [01:02:03.792] < pide los sensores (0x50 05)
16:01:54.997 [01:02:03.900] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:03.018 [01:02:11.924] < pide los sensores (0x50 05)
16:02:03.129 [01:02:12.032] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:11.151 [01:02:20.056] < pide los sensores (0x50 05)
16:02:11.261 [01:02:20.164] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:19.283 [01:02:28.188] < pide los sensores (0x50 05)
16:02:19.393 [01:02:28.296] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:27.415 [01:02:36.320] < pide los sensores (0x50 05)
16:02:27.525 [01:02:36.428] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:35.548 [01:02:44.454] < pide los sensores (0x50 05)
16:02:35.659 [01:02:44.562] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:43.680 [01:02:52.586] < pide los sensores (0x50 05)
16:02:43.790 [01:02:52.694] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:51.812 [01:03:00.718] < pide los sensores (0x50 05)
16:02:51.922 [01:03:00.826] > agua 47 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:02:59.944 [01:03:08.850] < pide los sensores (0x50 05)
16:03:00.054 [01:03:08.958] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:08.076 [01:03:16.982] < pide los sensores (0x50 05)
16:03:08.186 [01:03:17.090] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:16.208 [01:03:25.114] < pide los sensores (0x50 05)
16:03:16.318 [01:03:25.222] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:24.340 [01:03:33.246] < pide los sensores (0x50 05)
16:03:24.450 [01:03:33.354] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:32.472 [01:03:41.378] < pide los sensores (0x50 05)
16:03:32.582 [01:03:41.486] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:40.603 [01:03:49.510] < pide los sensores (0x50 05)
16:03:40.714 [01:03:49.618] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:48.735 [01:03:57.642] < pide los sensores (0x50 05)
16:03:48.846 [01:03:57.750] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:03:53.306 [01:04:02.206] = apagada · agua 46.2 °C · dentro 6.5 °C · fuera 5.0 °C · 12.60 V · llama no · 0 W
16:03:53.312 [01:04:02.207] =   sin orden de calentar
16:03:53.323 [01:04:02.218] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:03:53.326 [01:04:02.218] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:03:56.866 [01:04:05.773] < pide los sensores (0x50 05)
16:03:56.977 [01:04:05.881] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:04.998 [01:04:13.905] < pide los sensores (0x50 05)
16:04:05.109 [01:04:14.013] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:13.130 [01:04:22.037] < pide los sensores (0x50 05)
16:04:13.241 [01:04:22.145] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:21.262 [01:04:30.169] < pide los sensores (0x50 05)
16:04:21.372 [01:04:30.277] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:29.394 [01:04:38.301] < pide los sensores (0x50 05)
16:04:29.505 [01:04:38.409] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:37.526 [01:04:46.433] < pide los sensores (0x50 05)
16:04:37.637 [01:04:46.541] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:45.658 [01:04:54.565] < pide los sensores (0x50 05)
16:04:45.768 [01:04:54.673] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:04:53.790 [01:05:02.697] < pide los sensores (0x50 05)
16:04:53.900 [01:05:02.805] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.38 Ω
16:05:00.076 [01:05:08.983] < pide los sensores (0x50 05)
16:05:00.186 [01:05:09.091] > agua 46 °C · 12.60 V · llama no · 0 W · bujía 1.37 Ω
16:05:00.209 [01:05:09.115] < encender 30 min
16:05:00.220 [01:05:09.116] . encender 30 min: aceptado (arranque nº 2; mantenimiento cada <20 s o se apaga)
16:05:00.222 [01:05:09.127] . estado: apagada → ventilador (empieza el arranque)
16:05:00.292 [01:05:09.198] > «vale, 30 min»
16:05:00.303 [01:05:09.199] = ventilador · agua 45.8 °C · dentro 6.5 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
16:05:00.308 [01:05:09.210] =   orden 0x21: quedan 1800 s · último mantenimiento hace 0.0 s (a los 20 s se apaga)
16:05:00.320 [01:05:09.210] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:05:00.325 [01:05:09.221] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:05:05.322 [01:05:14.227] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 436.5 s
16:05:05.394 [01:05:14.302] > «sigo con la orden»
16:05:08.208 [01:05:17.116] < pide los sensores (0x50 05)
16:05:08.319 [01:05:17.224] > agua 46 °C · 12.48 V · llama no · 0 W · bujía 1.37 Ω
16:05:10.322 [01:05:19.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:10.394 [01:05:19.302] > «sigo con la orden»
16:05:12.212 [01:05:21.116] . estado: ventilador → bujía (ventilador listo: calienta la bujía)
16:05:15.322 [01:05:24.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:15.394 [01:05:24.302] > «sigo con la orden»
16:05:16.341 [01:05:25.248] < pide los sensores (0x50 05)
16:05:16.451 [01:05:25.356] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:20.322 [01:05:29.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:20.394 [01:05:29.302] > «sigo con la orden»
16:05:24.472 [01:05:33.380] < pide los sensores (0x50 05)
16:05:24.583 [01:05:33.488] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:25.322 [01:05:34.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:25.394 [01:05:34.302] > «sigo con la orden»
16:05:30.322 [01:05:39.227] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:30.394 [01:05:39.302] > «sigo con la orden»
16:05:32.604 [01:05:41.512] < pide los sensores (0x50 05)
16:05:32.715 [01:05:41.620] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:35.322 [01:05:44.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:35.394 [01:05:44.302] > «sigo con la orden»
16:05:40.322 [01:05:49.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:40.394 [01:05:49.302] > «sigo con la orden»
16:05:40.736 [01:05:49.644] < pide los sensores (0x50 05)
16:05:40.847 [01:05:49.752] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:45.322 [01:05:54.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:45.393 [01:05:54.302] > «sigo con la orden»
16:05:48.868 [01:05:57.776] < pide los sensores (0x50 05)
16:05:48.979 [01:05:57.884] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:50.321 [01:05:59.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:50.394 [01:05:59.302] > «sigo con la orden»
16:05:55.322 [01:06:04.227] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:05:55.393 [01:06:04.302] > «sigo con la orden»
16:05:57.000 [01:06:05.908] < pide los sensores (0x50 05)
16:05:57.110 [01:06:06.016] > agua 45 °C · 12.09 V · llama no · 0 W · bujía 1.37 Ω
16:05:57.212 [01:06:06.116] . estado: bujía → encendido (bujía caliente: bomba de gasoil en marcha)
16:06:00.215 [01:06:09.116] = encendido · agua 44.5 °C · dentro 6.8 °C · fuera 5.0 °C · 12.06 V · llama no · 0 W
16:06:00.226 [01:06:09.117] =   orden 0x21: quedan 1739 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
16:06:00.237 [01:06:09.128] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:06:00.240 [01:06:09.139] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:06:00.322 [01:06:09.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:00.393 [01:06:09.302] > «sigo con la orden»
16:06:05.132 [01:06:14.040] < pide los sensores (0x50 05)
16:06:05.242 [01:06:14.148] > agua 44 °C · 12.06 V · llama no · 0 W · bujía 1.37 Ω
16:06:05.322 [01:06:14.226] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:05.393 [01:06:14.302] > «sigo con la orden»
16:06:10.323 [01:06:19.229] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:10.395 [01:06:19.304] > «sigo con la orden»
16:06:13.264 [01:06:22.172] < pide los sensores (0x50 05)
16:06:13.374 [01:06:22.280] > agua 44 °C · 12.06 V · llama no · 0 W · bujía 1.37 Ω
16:06:15.323 [01:06:24.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:15.395 [01:06:24.304] > «sigo con la orden»
16:06:20.323 [01:06:29.229] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:20.395 [01:06:29.304] > «sigo con la orden»
16:06:21.398 [01:06:30.306] < pide los sensores (0x50 05)
16:06:21.508 [01:06:30.414] > agua 44 °C · 12.06 V · llama no · 0 W · bujía 1.36 Ω
16:06:25.323 [01:06:34.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:25.395 [01:06:34.304] > «sigo con la orden»
16:06:27.209 [01:06:36.116] . estado: encendido → estabilizando (¡llama!)
16:06:29.531 [01:06:38.440] < pide los sensores (0x50 05)
16:06:29.643 [01:06:38.548] > agua 44 °C · 12.36 V · llama sí · 2596 W · bujía 1.36 Ω
16:06:30.323 [01:06:39.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:30.395 [01:06:39.304] > «sigo con la orden»
16:06:35.323 [01:06:44.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:35.395 [01:06:44.304] > «sigo con la orden»
16:06:37.666 [01:06:46.574] < pide los sensores (0x50 05)
16:06:37.776 [01:06:46.682] > agua 44 °C · 12.36 V · llama sí · 2935 W · bujía 1.37 Ω
16:06:40.323 [01:06:49.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:40.395 [01:06:49.304] > «sigo con la orden»
16:06:45.323 [01:06:54.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:45.395 [01:06:54.304] > «sigo con la orden»
16:06:45.798 [01:06:54.706] < pide los sensores (0x50 05)
16:06:45.908 [01:06:54.814] > agua 44 °C · 12.36 V · llama sí · 3274 W · bujía 1.37 Ω
16:06:50.323 [01:06:59.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:50.395 [01:06:59.304] > «sigo con la orden»
16:06:53.929 [01:07:02.838] < pide los sensores (0x50 05)
16:06:54.040 [01:07:02.946] > agua 45 °C · 12.36 V · llama sí · 3613 W · bujía 1.37 Ω
16:06:55.323 [01:07:04.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:06:55.394 [01:07:04.304] > «sigo con la orden»
16:07:00.216 [01:07:09.118] = estabilizando · agua 44.8 °C · dentro 7.1 °C · fuera 5.0 °C · 12.36 V · llama sí · 3875 W
16:07:00.227 [01:07:09.119] =   orden 0x21: quedan 1679 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
16:07:00.239 [01:07:09.130] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:07:00.242 [01:07:09.141] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:07:00.323 [01:07:09.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:00.395 [01:07:09.304] > «sigo con la orden»
16:07:02.061 [01:07:10.970] < pide los sensores (0x50 05)
16:07:02.172 [01:07:11.078] > agua 45 °C · 12.36 V · llama sí · 3952 W · bujía 1.37 Ω
16:07:05.323 [01:07:14.228] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:05.395 [01:07:14.304] > «sigo con la orden»
16:07:10.193 [01:07:19.102] < pide los sensores (0x50 05)
16:07:10.304 [01:07:19.210] > agua 45 °C · 12.36 V · llama sí · 4291 W · bujía 1.37 Ω
16:07:10.335 [01:07:19.240] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:10.407 [01:07:19.316] > «sigo con la orden»
16:07:15.334 [01:07:24.240] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:15.407 [01:07:24.316] > «sigo con la orden»
16:07:18.325 [01:07:27.234] < pide los sensores (0x50 05)
16:07:18.436 [01:07:27.342] > agua 45 °C · 12.36 V · llama sí · 4629 W · bujía 1.37 Ω
16:07:20.335 [01:07:29.240] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:20.406 [01:07:29.316] > «sigo con la orden»
16:07:25.337 [01:07:34.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:25.408 [01:07:34.318] > «sigo con la orden»
16:07:26.457 [01:07:35.366] < pide los sensores (0x50 05)
16:07:26.568 [01:07:35.474] > agua 46 °C · 12.36 V · llama sí · 4968 W · bujía 1.37 Ω
16:07:27.209 [01:07:36.116] . estado: estabilizando → a tope (llama estable)
16:07:30.337 [01:07:39.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:30.408 [01:07:39.318] > «sigo con la orden»
16:07:34.589 [01:07:43.498] < pide los sensores (0x50 05)
16:07:34.699 [01:07:43.606] > agua 46 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:07:35.336 [01:07:44.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:35.408 [01:07:44.318] > «sigo con la orden»
16:07:40.337 [01:07:49.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:40.408 [01:07:49.318] > «sigo con la orden»
16:07:42.721 [01:07:51.630] < pide los sensores (0x50 05)
16:07:42.832 [01:07:51.738] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:07:45.337 [01:07:54.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:45.408 [01:07:54.318] > «sigo con la orden»
16:07:50.336 [01:07:59.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:50.408 [01:07:59.318] > «sigo con la orden»
16:07:50.853 [01:07:59.762] < pide los sensores (0x50 05)
16:07:50.964 [01:07:59.870] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:07:55.336 [01:08:04.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:07:55.408 [01:08:04.318] > «sigo con la orden»
16:07:58.985 [01:08:07.894] < pide los sensores (0x50 05)
16:07:59.095 [01:08:08.002] > agua 47 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:08:00.218 [01:08:09.120] = a tope · agua 47.3 °C · dentro 7.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:08:00.229 [01:08:09.121] =   orden 0x21: quedan 1619 s · último mantenimiento hace 4.9 s (a los 20 s se apaga)
16:08:00.240 [01:08:09.132] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:08:00.242 [01:08:09.143] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:08:00.336 [01:08:09.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:00.408 [01:08:09.318] > «sigo con la orden»
16:08:05.336 [01:08:14.243] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:05.408 [01:08:14.318] > «sigo con la orden»
16:08:07.116 [01:08:16.026] < pide los sensores (0x50 05)
16:08:07.227 [01:08:16.134] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.38 Ω
16:08:10.336 [01:08:19.242] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:10.408 [01:08:19.318] > «sigo con la orden»
16:08:15.248 [01:08:24.158] < pide los sensores (0x50 05)
16:08:15.359 [01:08:24.266] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:15.392 [01:08:24.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:08:15.463 [01:08:24.374] > «sigo con la orden»
16:08:20.392 [01:08:29.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:20.464 [01:08:29.374] > «sigo con la orden»
16:08:23.380 [01:08:32.290] < pide los sensores (0x50 05)
16:08:23.491 [01:08:32.398] > agua 48 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:25.392 [01:08:34.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:25.464 [01:08:34.374] > «sigo con la orden»
16:08:30.392 [01:08:39.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:30.464 [01:08:39.374] > «sigo con la orden»
16:08:31.512 [01:08:40.422] < pide los sensores (0x50 05)
16:08:31.623 [01:08:40.530] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:35.392 [01:08:44.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:35.464 [01:08:44.374] > «sigo con la orden»
16:08:39.644 [01:08:48.554] < pide los sensores (0x50 05)
16:08:39.755 [01:08:48.662] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.39 Ω
16:08:40.395 [01:08:49.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:40.464 [01:08:49.374] > «sigo con la orden»
16:08:45.392 [01:08:54.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:45.463 [01:08:54.374] > «sigo con la orden»
16:08:47.776 [01:08:56.686] < pide los sensores (0x50 05)
16:08:47.887 [01:08:56.794] > agua 49 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:08:50.391 [01:08:59.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:50.464 [01:08:59.374] > «sigo con la orden»
16:08:55.392 [01:09:04.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:08:55.463 [01:09:04.374] > «sigo con la orden»
16:08:55.908 [01:09:04.818] < pide los sensores (0x50 05)
16:08:56.018 [01:09:04.926] > agua 50 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:00.219 [01:09:09.122] = a tope · agua 50.0 °C · dentro 7.6 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:09:00.230 [01:09:09.123] =   orden 0x21: quedan 1559 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
16:09:00.241 [01:09:09.134] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:09:00.244 [01:09:09.145] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:09:00.393 [01:09:09.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:00.463 [01:09:09.374] > «sigo con la orden»
16:09:04.040 [01:09:12.950] < pide los sensores (0x50 05)
16:09:04.151 [01:09:13.058] > agua 50 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:05.391 [01:09:14.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:05.463 [01:09:14.374] > «sigo con la orden»
16:09:10.391 [01:09:19.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:10.463 [01:09:19.374] > «sigo con la orden»
16:09:12.172 [01:09:21.082] < pide los sensores (0x50 05)
16:09:12.283 [01:09:21.190] > agua 51 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:15.391 [01:09:24.298] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:15.463 [01:09:24.374] > «sigo con la orden»
16:09:20.303 [01:09:29.214] < pide los sensores (0x50 05)
16:09:20.414 [01:09:29.322] > agua 51 °C · 12.39 V · llama sí · 5000 W · bujía 1.40 Ω
16:09:20.447 [01:09:29.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:09:20.519 [01:09:29.430] > «sigo con la orden»
16:09:25.447 [01:09:34.355] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:25.519 [01:09:34.430] > «sigo con la orden»
16:09:28.436 [01:09:37.346] < pide los sensores (0x50 05)
16:09:28.546 [01:09:37.454] > agua 51 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:30.447 [01:09:39.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:30.519 [01:09:39.430] > «sigo con la orden»
16:09:35.447 [01:09:44.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:35.519 [01:09:44.430] > «sigo con la orden»
16:09:36.570 [01:09:45.480] < pide los sensores (0x50 05)
16:09:36.680 [01:09:45.588] > agua 52 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:40.447 [01:09:49.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:40.519 [01:09:49.430] > «sigo con la orden»
16:09:44.704 [01:09:53.614] < pide los sensores (0x50 05)
16:09:44.814 [01:09:53.722] > agua 52 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:45.447 [01:09:54.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:45.519 [01:09:54.430] > «sigo con la orden»
16:09:50.447 [01:09:59.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:50.519 [01:09:59.430] > «sigo con la orden»
16:09:52.835 [01:10:01.746] < pide los sensores (0x50 05)
16:09:52.946 [01:10:01.854] > agua 52 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:09:55.447 [01:10:04.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:09:55.519 [01:10:04.430] > «sigo con la orden»
16:10:00.220 [01:10:09.124] = a tope · agua 52.6 °C · dentro 7.9 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:10:00.231 [01:10:09.125] =   orden 0x21: quedan 1499 s · último mantenimiento hace 4.8 s (a los 20 s se apaga)
16:10:00.242 [01:10:09.136] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:10:00.245 [01:10:09.147] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:10:00.447 [01:10:09.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:00.519 [01:10:09.430] > «sigo con la orden»
16:10:00.967 [01:10:09.878] < pide los sensores (0x50 05)
16:10:01.078 [01:10:09.986] > agua 53 °C · 12.39 V · llama sí · 5000 W · bujía 1.41 Ω
16:10:05.447 [01:10:14.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:05.519 [01:10:14.430] > «sigo con la orden»
16:10:09.099 [01:10:18.010] < pide los sensores (0x50 05)
16:10:09.210 [01:10:18.118] > agua 53 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:10.447 [01:10:19.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:10.518 [01:10:19.430] > «sigo con la orden»
16:10:15.447 [01:10:24.354] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:15.518 [01:10:24.430] > «sigo con la orden»
16:10:17.231 [01:10:26.142] < pide los sensores (0x50 05)
16:10:17.342 [01:10:26.250] > agua 53 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:20.449 [01:10:29.356] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:20.520 [01:10:29.432] > «sigo con la orden»
16:10:25.363 [01:10:34.274] < pide los sensores (0x50 05)
16:10:25.473 [01:10:34.382] > agua 54 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:25.504 [01:10:34.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:10:25.576 [01:10:34.488] > «sigo con la orden»
16:10:30.505 [01:10:39.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:30.576 [01:10:39.488] > «sigo con la orden»
16:10:33.495 [01:10:42.406] < pide los sensores (0x50 05)
16:10:33.606 [01:10:42.514] > agua 54 °C · 12.39 V · llama sí · 5000 W · bujía 1.42 Ω
16:10:35.504 [01:10:44.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:35.576 [01:10:44.488] > «sigo con la orden»
16:10:40.505 [01:10:49.413] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:40.576 [01:10:49.488] > «sigo con la orden»
16:10:41.627 [01:10:50.538] < pide los sensores (0x50 05)
16:10:41.738 [01:10:50.646] > agua 54 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:10:45.505 [01:10:54.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:45.576 [01:10:54.488] > «sigo con la orden»
16:10:49.759 [01:10:58.670] < pide los sensores (0x50 05)
16:10:49.869 [01:10:58.778] > agua 55 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:10:50.505 [01:10:59.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:50.576 [01:10:59.488] > «sigo con la orden»
16:10:55.504 [01:11:04.413] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:10:55.576 [01:11:04.488] > «sigo con la orden»
16:10:57.891 [01:11:06.802] < pide los sensores (0x50 05)
16:10:58.001 [01:11:06.910] > agua 55 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:11:00.222 [01:11:09.126] = a tope · agua 55.1 °C · dentro 8.2 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:11:00.233 [01:11:09.127] =   orden 0x21: quedan 1439 s · último mantenimiento hace 4.7 s (a los 20 s se apaga)
16:11:00.249 [01:11:09.138] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:11:00.252 [01:11:09.149] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:11:00.504 [01:11:09.412] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:00.576 [01:11:09.488] > «sigo con la orden»
16:11:05.504 [01:11:14.413] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:05.576 [01:11:14.488] > «sigo con la orden»
16:11:06.023 [01:11:14.934] < pide los sensores (0x50 05)
16:11:06.133 [01:11:15.042] > agua 55 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:11:10.506 [01:11:19.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:10.578 [01:11:19.490] > «sigo con la orden»
16:11:14.155 [01:11:23.066] < pide los sensores (0x50 05)
16:11:14.265 [01:11:23.174] > agua 56 °C · 12.39 V · llama sí · 5000 W · bujía 1.43 Ω
16:11:15.506 [01:11:24.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:15.578 [01:11:24.490] > «sigo con la orden»
16:11:20.506 [01:11:29.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:20.577 [01:11:29.490] > «sigo con la orden»
16:11:22.286 [01:11:31.198] < pide los sensores (0x50 05)
16:11:22.397 [01:11:31.306] > agua 56 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:25.506 [01:11:34.414] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:25.578 [01:11:34.490] > «sigo con la orden»
16:11:30.418 [01:11:39.330] < pide los sensores (0x50 05)
16:11:30.529 [01:11:39.438] > agua 56 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:30.562 [01:11:39.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:11:30.633 [01:11:39.546] > «sigo con la orden»
16:11:35.588 [01:11:44.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:35.659 [01:11:44.572] > «sigo con la orden»
16:11:38.550 [01:11:47.462] < pide los sensores (0x50 05)
16:11:38.661 [01:11:47.570] > agua 57 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:40.588 [01:11:49.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:40.660 [01:11:49.572] > «sigo con la orden»
16:11:45.588 [01:11:54.497] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:45.659 [01:11:54.572] > «sigo con la orden»
16:11:46.682 [01:11:55.594] < pide los sensores (0x50 05)
16:11:46.793 [01:11:55.702] > agua 57 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:50.588 [01:11:59.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:50.659 [01:11:59.572] > «sigo con la orden»
16:11:54.814 [01:12:03.726] < pide los sensores (0x50 05)
16:11:54.925 [01:12:03.834] > agua 57 °C · 12.39 V · llama sí · 5000 W · bujía 1.44 Ω
16:11:55.588 [01:12:04.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:11:55.659 [01:12:04.572] > «sigo con la orden»
16:12:00.223 [01:12:09.128] = a tope · agua 57.5 °C · dentro 8.6 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:12:00.234 [01:12:09.129] =   orden 0x21: quedan 1379 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
16:12:00.245 [01:12:09.140] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:12:00.248 [01:12:09.151] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:12:00.588 [01:12:09.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:00.659 [01:12:09.572] > «sigo con la orden»
16:12:02.948 [01:12:11.860] < pide los sensores (0x50 05)
16:12:03.059 [01:12:11.968] > agua 58 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:05.587 [01:12:14.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:05.659 [01:12:14.572] > «sigo con la orden»
16:12:10.587 [01:12:19.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:10.659 [01:12:19.572] > «sigo con la orden»
16:12:11.080 [01:12:19.992] < pide los sensores (0x50 05)
16:12:11.191 [01:12:20.100] > agua 58 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:15.587 [01:12:24.496] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:15.659 [01:12:24.572] > «sigo con la orden»
16:12:19.212 [01:12:28.124] < pide los sensores (0x50 05)
16:12:19.322 [01:12:28.232] > agua 58 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:20.589 [01:12:29.498] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:20.661 [01:12:29.574] > «sigo con la orden»
16:12:25.589 [01:12:34.498] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:25.661 [01:12:34.574] > «sigo con la orden»
16:12:27.343 [01:12:36.256] < pide los sensores (0x50 05)
16:12:27.455 [01:12:36.364] > agua 59 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:30.589 [01:12:39.498] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:30.661 [01:12:39.574] > «sigo con la orden»
16:12:35.475 [01:12:44.388] < pide los sensores (0x50 05)
16:12:35.586 [01:12:44.496] > agua 59 °C · 12.39 V · llama sí · 5000 W · bujía 1.45 Ω
16:12:35.619 [01:12:44.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:35.691 [01:12:44.604] > «sigo con la orden»
16:12:40.619 [01:12:49.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:40.691 [01:12:49.604] > «sigo con la orden»
16:12:43.607 [01:12:52.520] < pide los sensores (0x50 05)
16:12:43.718 [01:12:52.628] > agua 59 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:12:45.619 [01:12:54.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:45.691 [01:12:54.604] > «sigo con la orden»
16:12:50.619 [01:12:59.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:50.691 [01:12:59.604] > «sigo con la orden»
16:12:51.739 [01:13:00.652] < pide los sensores (0x50 05)
16:12:51.850 [01:13:00.760] > agua 60 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:12:55.619 [01:13:04.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:12:55.691 [01:13:04.604] > «sigo con la orden»
16:12:59.871 [01:13:08.784] < pide los sensores (0x50 05)
16:12:59.982 [01:13:08.892] > agua 60 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:13:00.224 [01:13:09.130] = a tope · agua 59.9 °C · dentro 8.9 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:13:00.235 [01:13:09.131] =   orden 0x21: quedan 1319 s · último mantenimiento hace 4.6 s (a los 20 s se apaga)
16:13:00.246 [01:13:09.142] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:13:00.250 [01:13:09.153] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:13:00.619 [01:13:09.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:00.691 [01:13:09.604] > «sigo con la orden»
16:13:05.619 [01:13:14.529] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:05.691 [01:13:14.604] > «sigo con la orden»
16:13:08.003 [01:13:16.916] < pide los sensores (0x50 05)
16:13:08.114 [01:13:17.024] > agua 60 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:13:10.619 [01:13:19.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:10.691 [01:13:19.604] > «sigo con la orden»
16:13:15.619 [01:13:24.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:15.690 [01:13:24.604] > «sigo con la orden»
16:13:16.135 [01:13:25.048] < pide los sensores (0x50 05)
16:13:16.246 [01:13:25.156] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.46 Ω
16:13:20.619 [01:13:29.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:20.690 [01:13:29.604] > «sigo con la orden»
16:13:24.267 [01:13:33.180] < pide los sensores (0x50 05)
16:13:24.378 [01:13:33.288] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:25.619 [01:13:34.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:25.690 [01:13:34.604] > «sigo con la orden»
16:13:30.618 [01:13:39.529] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:30.690 [01:13:39.604] > «sigo con la orden»
16:13:32.399 [01:13:41.312] < pide los sensores (0x50 05)
16:13:32.510 [01:13:41.420] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:35.619 [01:13:44.528] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:35.690 [01:13:44.604] > «sigo con la orden»
16:13:40.531 [01:13:49.444] < pide los sensores (0x50 05)
16:13:40.642 [01:13:49.552] > agua 61 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:40.674 [01:13:49.584] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:13:40.746 [01:13:49.660] > «sigo con la orden»
16:13:45.675 [01:13:54.585] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:45.746 [01:13:54.660] > «sigo con la orden»
16:13:48.663 [01:13:57.576] < pide los sensores (0x50 05)
16:13:48.773 [01:13:57.684] > agua 62 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:13:50.674 [01:13:59.584] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:50.746 [01:13:59.660] > «sigo con la orden»
16:13:55.692 [01:14:04.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:13:55.764 [01:14:04.678] > «sigo con la orden»
16:13:56.795 [01:14:05.708] < pide los sensores (0x50 05)
16:13:56.905 [01:14:05.816] > agua 62 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:14:00.226 [01:14:09.132] = a tope · agua 62.2 °C · dentro 9.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:14:00.237 [01:14:09.133] =   orden 0x21: quedan 1259 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
16:14:00.248 [01:14:09.144] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:14:00.250 [01:14:09.155] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:14:00.692 [01:14:09.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:00.764 [01:14:09.678] > «sigo con la orden»
16:14:04.927 [01:14:13.840] < pide los sensores (0x50 05)
16:14:05.037 [01:14:13.948] > agua 62 °C · 12.39 V · llama sí · 5000 W · bujía 1.47 Ω
16:14:05.692 [01:14:14.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:05.764 [01:14:14.678] > «sigo con la orden»
16:14:10.692 [01:14:19.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:10.764 [01:14:19.678] > «sigo con la orden»
16:14:13.058 [01:14:21.972] < pide los sensores (0x50 05)
16:14:13.169 [01:14:22.080] > agua 63 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:15.692 [01:14:24.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:15.764 [01:14:24.678] > «sigo con la orden»
16:14:20.692 [01:14:29.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:20.764 [01:14:29.678] > «sigo con la orden»
16:14:21.190 [01:14:30.104] < pide los sensores (0x50 05)
16:14:21.301 [01:14:30.212] > agua 63 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:25.692 [01:14:34.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:25.764 [01:14:34.678] > «sigo con la orden»
16:14:29.322 [01:14:38.236] < pide los sensores (0x50 05)
16:14:29.433 [01:14:38.344] > agua 63 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:30.692 [01:14:39.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:30.764 [01:14:39.678] > «sigo con la orden»
16:14:35.692 [01:14:44.603] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:35.764 [01:14:44.678] > «sigo con la orden»
16:14:37.454 [01:14:46.368] < pide los sensores (0x50 05)
16:14:37.565 [01:14:46.476] > agua 64 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:40.692 [01:14:49.602] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:40.763 [01:14:49.678] > «sigo con la orden»
16:14:45.586 [01:14:54.500] < pide los sensores (0x50 05)
16:14:45.697 [01:14:54.608] > agua 64 °C · 12.39 V · llama sí · 5000 W · bujía 1.48 Ω
16:14:45.728 [01:14:54.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:45.799 [01:14:54.714] > «sigo con la orden»
16:14:50.727 [01:14:59.639] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:50.800 [01:14:59.714] > «sigo con la orden»
16:14:53.718 [01:15:02.632] < pide los sensores (0x50 05)
16:14:53.829 [01:15:02.740] > agua 64 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:14:55.728 [01:15:04.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:14:55.799 [01:15:04.714] > «sigo con la orden»
16:15:00.227 [01:15:09.134] = a tope · agua 64.5 °C · dentro 9.7 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:15:00.238 [01:15:09.135] =   orden 0x21: quedan 1199 s · último mantenimiento hace 4.5 s (a los 20 s se apaga)
16:15:00.249 [01:15:09.146] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:15:00.252 [01:15:09.157] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:15:00.729 [01:15:09.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:00.801 [01:15:09.716] > «sigo con la orden»
16:15:01.850 [01:15:10.764] < pide los sensores (0x50 05)
16:15:01.960 [01:15:10.872] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:05.729 [01:15:14.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:05.801 [01:15:14.716] > «sigo con la orden»
16:15:09.982 [01:15:18.896] < pide los sensores (0x50 05)
16:15:10.093 [01:15:19.004] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:10.729 [01:15:19.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:10.801 [01:15:19.716] > «sigo con la orden»
16:15:15.729 [01:15:24.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:15.801 [01:15:24.716] > «sigo con la orden»
16:15:18.114 [01:15:27.028] < pide los sensores (0x50 05)
16:15:18.225 [01:15:27.136] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:20.730 [01:15:29.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:20.801 [01:15:29.716] > «sigo con la orden»
16:15:25.729 [01:15:34.641] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:25.801 [01:15:34.716] > «sigo con la orden»
16:15:26.245 [01:15:35.160] < pide los sensores (0x50 05)
16:15:26.356 [01:15:35.268] > agua 65 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:30.729 [01:15:39.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:30.801 [01:15:39.716] > «sigo con la orden»
16:15:34.378 [01:15:43.292] < pide los sensores (0x50 05)
16:15:34.488 [01:15:43.400] > agua 66 °C · 12.39 V · llama sí · 5000 W · bujía 1.49 Ω
16:15:35.729 [01:15:44.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:35.801 [01:15:44.716] > «sigo con la orden»
16:15:40.729 [01:15:49.641] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:40.801 [01:15:49.716] > «sigo con la orden»
16:15:42.511 [01:15:51.424] < pide los sensores (0x50 05)
16:15:42.620 [01:15:51.532] > agua 66 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:15:45.729 [01:15:54.640] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:45.801 [01:15:54.716] > «sigo con la orden»
16:15:50.642 [01:15:59.556] < pide los sensores (0x50 05)
16:15:50.752 [01:15:59.664] > agua 66 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:15:50.785 [01:15:59.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:15:50.857 [01:15:59.772] > «sigo con la orden»
16:15:55.785 [01:16:04.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:15:55.857 [01:16:04.772] > «sigo con la orden»
16:15:58.773 [01:16:07.688] < pide los sensores (0x50 05)
16:15:58.884 [01:16:07.796] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:00.228 [01:16:09.136] = a tope · agua 66.6 °C · dentro 10.0 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:16:00.239 [01:16:09.137] =   orden 0x21: quedan 1139 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
16:16:00.250 [01:16:09.148] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:16:00.253 [01:16:09.159] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:16:00.785 [01:16:09.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:00.857 [01:16:09.772] > «sigo con la orden»
16:16:05.785 [01:16:14.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:05.856 [01:16:14.772] > «sigo con la orden»
16:16:06.905 [01:16:15.820] < pide los sensores (0x50 05)
16:16:07.016 [01:16:15.928] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:10.785 [01:16:19.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:10.856 [01:16:19.772] > «sigo con la orden»
16:16:15.037 [01:16:23.952] < pide los sensores (0x50 05)
16:16:15.148 [01:16:24.060] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:15.784 [01:16:24.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:15.857 [01:16:24.772] > «sigo con la orden»
16:16:20.785 [01:16:29.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:20.856 [01:16:29.772] > «sigo con la orden»
16:16:23.169 [01:16:32.084] < pide los sensores (0x50 05)
16:16:23.280 [01:16:32.192] > agua 67 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:25.785 [01:16:34.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:25.856 [01:16:34.772] > «sigo con la orden»
16:16:30.784 [01:16:39.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:30.856 [01:16:39.772] > «sigo con la orden»
16:16:31.301 [01:16:40.216] < pide los sensores (0x50 05)
16:16:31.412 [01:16:40.324] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.50 Ω
16:16:35.784 [01:16:44.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:35.856 [01:16:44.772] > «sigo con la orden»
16:16:39.433 [01:16:48.348] < pide los sensores (0x50 05)
16:16:39.544 [01:16:48.456] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:16:40.785 [01:16:49.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:40.856 [01:16:49.772] > «sigo con la orden»
16:16:45.785 [01:16:54.697] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:45.856 [01:16:54.772] > «sigo con la orden»
16:16:47.565 [01:16:56.480] < pide los sensores (0x50 05)
16:16:47.676 [01:16:56.588] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:16:50.784 [01:16:59.696] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:16:50.856 [01:16:59.772] > «sigo con la orden»
16:16:55.697 [01:17:04.612] < pide los sensores (0x50 05)
16:16:55.808 [01:17:04.720] > agua 68 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:16:55.840 [01:17:04.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:16:55.912 [01:17:04.828] > «sigo con la orden»
16:17:00.231 [01:17:09.138] = a tope · agua 68.6 °C · dentro 10.4 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:17:00.241 [01:17:09.139] =   orden 0x21: quedan 1079 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
16:17:00.252 [01:17:09.150] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:17:00.255 [01:17:09.161] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:17:00.840 [01:17:09.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:00.912 [01:17:09.828] > «sigo con la orden»
16:17:03.828 [01:17:12.744] < pide los sensores (0x50 05)
16:17:03.939 [01:17:12.852] > agua 69 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:17:05.840 [01:17:14.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:05.912 [01:17:14.828] > «sigo con la orden»
16:17:10.840 [01:17:19.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:10.912 [01:17:19.828] > «sigo con la orden»
16:17:11.961 [01:17:20.876] < pide los sensores (0x50 05)
16:17:12.071 [01:17:20.984] > agua 69 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:17:15.840 [01:17:24.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:15.912 [01:17:24.828] > «sigo con la orden»
16:17:20.092 [01:17:29.008] < pide los sensores (0x50 05)
16:17:20.203 [01:17:29.116] > agua 69 °C · 12.39 V · llama sí · 5000 W · bujía 1.51 Ω
16:17:20.840 [01:17:29.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:20.912 [01:17:29.828] > «sigo con la orden»
16:17:25.840 [01:17:34.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:25.911 [01:17:34.828] > «sigo con la orden»
16:17:28.224 [01:17:37.140] < pide los sensores (0x50 05)
16:17:28.335 [01:17:37.248] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:30.840 [01:17:39.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:30.912 [01:17:39.828] > «sigo con la orden»
16:17:35.840 [01:17:44.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:35.912 [01:17:44.828] > «sigo con la orden»
16:17:36.356 [01:17:45.272] < pide los sensores (0x50 05)
16:17:36.467 [01:17:45.380] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:40.840 [01:17:49.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:40.912 [01:17:49.828] > «sigo con la orden»
16:17:44.490 [01:17:53.406] < pide los sensores (0x50 05)
16:17:44.601 [01:17:53.514] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:45.839 [01:17:54.752] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:45.912 [01:17:54.828] > «sigo con la orden»
16:17:50.842 [01:17:59.755] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:50.914 [01:17:59.830] > «sigo con la orden»
16:17:52.622 [01:18:01.538] < pide los sensores (0x50 05)
16:17:52.733 [01:18:01.646] > agua 70 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:17:55.841 [01:18:04.754] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:17:55.913 [01:18:04.830] > «sigo con la orden»
16:18:00.231 [01:18:09.140] = a tope · agua 70.7 °C · dentro 10.8 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:18:00.242 [01:18:09.141] =   orden 0x21: quedan 1019 s · último mantenimiento hace 4.4 s (a los 20 s se apaga)
16:18:00.253 [01:18:09.152] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:18:00.256 [01:18:09.163] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:18:00.754 [01:18:09.670] < pide los sensores (0x50 05)
16:18:00.864 [01:18:09.778] > agua 71 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:18:00.895 [01:18:09.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:18:00.967 [01:18:09.884] > «sigo con la orden»
16:18:05.897 [01:18:14.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:05.967 [01:18:14.884] > «sigo con la orden»
16:18:08.886 [01:18:17.802] < pide los sensores (0x50 05)
16:18:08.996 [01:18:17.910] > agua 71 °C · 12.39 V · llama sí · 5000 W · bujía 1.52 Ω
16:18:10.895 [01:18:19.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:10.967 [01:18:19.884] > «sigo con la orden»
16:18:15.895 [01:18:24.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:15.967 [01:18:24.884] > «sigo con la orden»
16:18:17.018 [01:18:25.934] < pide los sensores (0x50 05)
16:18:17.129 [01:18:26.042] > agua 71 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:20.895 [01:18:29.808] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:20.967 [01:18:29.884] > «sigo con la orden»
16:18:25.149 [01:18:34.066] < pide los sensores (0x50 05)
16:18:25.260 [01:18:34.174] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:25.897 [01:18:34.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:25.969 [01:18:34.886] > «sigo con la orden»
16:18:30.897 [01:18:39.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:30.969 [01:18:39.886] > «sigo con la orden»
16:18:33.281 [01:18:42.198] < pide los sensores (0x50 05)
16:18:33.392 [01:18:42.306] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:35.897 [01:18:44.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:35.969 [01:18:44.886] > «sigo con la orden»
16:18:40.897 [01:18:49.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:40.969 [01:18:49.886] > «sigo con la orden»
16:18:41.413 [01:18:50.330] < pide los sensores (0x50 05)
16:18:41.524 [01:18:50.438] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:45.897 [01:18:54.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:45.969 [01:18:54.886] > «sigo con la orden»
16:18:49.545 [01:18:58.462] < pide los sensores (0x50 05)
16:18:49.656 [01:18:58.570] > agua 72 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:18:50.897 [01:18:59.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:50.969 [01:18:59.886] > «sigo con la orden»
16:18:55.897 [01:19:04.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:18:55.969 [01:19:04.886] > «sigo con la orden»
16:18:57.677 [01:19:06.594] < pide los sensores (0x50 05)
16:18:57.788 [01:19:06.702] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.53 Ω
16:19:00.232 [01:19:09.142] = a tope · agua 72.7 °C · dentro 11.3 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:19:00.243 [01:19:09.143] =   orden 0x21: quedan 959 s · último mantenimiento hace 4.3 s (a los 20 s se apaga)
16:19:00.254 [01:19:09.154] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:19:00.258 [01:19:09.165] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:19:00.897 [01:19:09.810] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:00.968 [01:19:09.886] > «sigo con la orden»
16:19:05.810 [01:19:14.726] < pide los sensores (0x50 05)
16:19:05.920 [01:19:14.834] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:05.953 [01:19:14.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:19:06.029 [01:19:14.942] > «sigo con la orden»
16:19:10.952 [01:19:19.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:11.024 [01:19:19.942] > «sigo con la orden»
16:19:13.941 [01:19:22.858] < pide los sensores (0x50 05)
16:19:14.052 [01:19:22.966] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:15.952 [01:19:24.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:16.024 [01:19:24.942] > «sigo con la orden»
16:19:20.953 [01:19:29.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:21.024 [01:19:29.942] > «sigo con la orden»
16:19:22.073 [01:19:30.990] < pide los sensores (0x50 05)
16:19:22.184 [01:19:31.098] > agua 73 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:25.953 [01:19:34.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:26.024 [01:19:34.942] > «sigo con la orden»
16:19:30.274 [01:19:39.122] < pide los sensores (0x50 05)
16:19:30.315 [01:19:39.230] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:30.952 [01:19:39.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:31.024 [01:19:39.942] > «sigo con la orden»
16:19:35.952 [01:19:44.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:36.024 [01:19:44.942] > «sigo con la orden»
16:19:38.337 [01:19:47.254] < pide los sensores (0x50 05)
16:19:38.447 [01:19:47.362] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:40.952 [01:19:49.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:41.024 [01:19:49.942] > «sigo con la orden»
16:19:45.952 [01:19:54.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:46.024 [01:19:54.942] > «sigo con la orden»
16:19:46.469 [01:19:55.386] < pide los sensores (0x50 05)
16:19:46.580 [01:19:55.494] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.54 Ω
16:19:50.952 [01:19:59.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:51.024 [01:19:59.942] > «sigo con la orden»
16:19:54.601 [01:20:03.518] < pide los sensores (0x50 05)
16:19:54.712 [01:20:03.626] > agua 74 °C · 12.39 V · llama sí · 5000 W · bujía 1.55 Ω
16:19:55.952 [01:20:04.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:19:56.024 [01:20:04.942] > «sigo con la orden»
16:20:00.256 [01:20:09.144] = a tope · agua 74.6 °C · dentro 11.7 °C · fuera 5.0 °C · 12.39 V · llama sí · 5000 W
16:20:00.268 [01:20:09.145] =   orden 0x21: quedan 899 s · último mantenimiento hace 4.3 s (a los 20 s se apaga)
16:20:00.274 [01:20:09.156] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:20:00.293 [01:20:09.167] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:20:00.952 [01:20:09.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:01.024 [01:20:09.942] > «sigo con la orden»
16:20:02.732 [01:20:11.650] < pide los sensores (0x50 05)
16:20:02.843 [01:20:11.758] > agua 75 °C · 12.39 V · llama sí · 5000 W · bujía 1.55 Ω
16:20:05.952 [01:20:14.866] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:06.024 [01:20:14.942] > «sigo con la orden»
16:20:10.864 [01:20:19.782] < pide los sensores (0x50 05)
16:20:10.975 [01:20:19.890] > agua 75 °C · 12.39 V · llama sí · 5000 W · bujía 1.55 Ω
16:20:11.008 [01:20:19.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:20:11.080 [01:20:19.998] > «sigo con la orden»
16:20:14.378 [01:20:23.292] . estado: a tope → carga parcial (agua a 75 °C: baja la potencia)
16:20:16.050 [01:20:24.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:16.080 [01:20:24.998] > «sigo con la orden»
16:20:19.002 [01:20:27.914] < pide los sensores (0x50 05)
16:20:19.107 [01:20:28.022] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:21.008 [01:20:29.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:21.080 [01:20:29.998] > «sigo con la orden»
16:20:26.008 [01:20:34.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:26.079 [01:20:34.998] > «sigo con la orden»
16:20:27.128 [01:20:36.046] < pide los sensores (0x50 05)
16:20:27.239 [01:20:36.154] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:31.008 [01:20:39.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:31.080 [01:20:39.998] > «sigo con la orden»
16:20:35.260 [01:20:44.178] < pide los sensores (0x50 05)
16:20:35.371 [01:20:44.286] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:36.008 [01:20:44.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:36.080 [01:20:44.998] > «sigo con la orden»
16:20:41.008 [01:20:49.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:41.079 [01:20:49.998] > «sigo con la orden»
16:20:43.392 [01:20:52.310] < pide los sensores (0x50 05)
16:20:43.503 [01:20:52.418] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:46.008 [01:20:54.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:46.079 [01:20:54.998] > «sigo con la orden»
16:20:51.008 [01:20:59.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:51.079 [01:20:59.998] > «sigo con la orden»
16:20:51.524 [01:21:00.442] < pide los sensores (0x50 05)
16:20:51.635 [01:21:00.550] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:20:56.007 [01:21:04.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:20:56.079 [01:21:04.998] > «sigo con la orden»
16:20:59.656 [01:21:08.574] < pide los sensores (0x50 05)
16:20:59.766 [01:21:08.682] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:00.235 [01:21:09.146] = carga parcial · agua 74.8 °C · dentro 12.1 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:21:00.246 [01:21:09.147] =   orden 0x21: quedan 839 s · último mantenimiento hace 4.2 s (a los 20 s se apaga)
16:21:00.257 [01:21:09.158] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:21:00.260 [01:21:09.169] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:21:01.007 [01:21:09.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:01.079 [01:21:09.998] > «sigo con la orden»
16:21:06.007 [01:21:14.923] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:06.079 [01:21:14.998] > «sigo con la orden»
16:21:07.788 [01:21:16.706] < pide los sensores (0x50 05)
16:21:07.898 [01:21:16.814] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:11.007 [01:21:19.922] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:11.079 [01:21:19.998] > «sigo con la orden»
16:21:15.920 [01:21:24.838] < pide los sensores (0x50 05)
16:21:16.030 [01:21:24.946] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:16.063 [01:21:24.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:21:16.135 [01:21:25.054] > «sigo con la orden»
16:21:21.063 [01:21:29.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:21.135 [01:21:30.054] > «sigo con la orden»
16:21:24.051 [01:21:32.970] < pide los sensores (0x50 05)
16:21:24.162 [01:21:33.078] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:26.063 [01:21:34.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:26.135 [01:21:35.054] > «sigo con la orden»
16:21:31.063 [01:21:39.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:31.135 [01:21:40.054] > «sigo con la orden»
16:21:32.183 [01:21:41.102] < pide los sensores (0x50 05)
16:21:32.295 [01:21:41.210] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:36.063 [01:21:44.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:36.135 [01:21:45.054] > «sigo con la orden»
16:21:40.316 [01:21:49.234] < pide los sensores (0x50 05)
16:21:40.426 [01:21:49.342] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:41.067 [01:21:49.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:41.135 [01:21:50.054] > «sigo con la orden»
16:21:46.063 [01:21:54.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:46.135 [01:21:55.054] > «sigo con la orden»
16:21:48.448 [01:21:57.366] < pide los sensores (0x50 05)
16:21:48.559 [01:21:57.474] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:21:51.063 [01:21:59.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:51.135 [01:22:00.054] > «sigo con la orden»
16:21:56.063 [01:22:04.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:21:56.135 [01:22:05.054] > «sigo con la orden»
16:21:56.580 [01:22:05.498] < pide los sensores (0x50 05)
16:21:56.690 [01:22:05.606] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:00.236 [01:22:09.148] = carga parcial · agua 74.6 °C · dentro 12.5 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:22:00.247 [01:22:09.149] =   orden 0x21: quedan 779 s · último mantenimiento hace 4.2 s (a los 20 s se apaga)
16:22:00.258 [01:22:09.160] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:22:00.262 [01:22:09.171] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:22:01.064 [01:22:09.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:01.134 [01:22:10.054] > «sigo con la orden»
16:22:04.711 [01:22:13.630] < pide los sensores (0x50 05)
16:22:04.822 [01:22:13.738] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:06.063 [01:22:14.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:06.135 [01:22:15.054] > «sigo con la orden»
16:22:11.063 [01:22:19.979] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:11.134 [01:22:20.054] > «sigo con la orden»
16:22:12.843 [01:22:21.762] < pide los sensores (0x50 05)
16:22:12.954 [01:22:21.870] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:16.062 [01:22:24.978] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:16.134 [01:22:25.054] > «sigo con la orden»
16:22:20.975 [01:22:29.894] < pide los sensores (0x50 05)
16:22:21.086 [01:22:30.002] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:21.116 [01:22:30.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:22:21.188 [01:22:30.108] > «sigo con la orden»
16:22:26.117 [01:22:35.033] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:26.188 [01:22:35.108] > «sigo con la orden»
16:22:29.107 [01:22:38.026] < pide los sensores (0x50 05)
16:22:29.218 [01:22:38.134] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:31.117 [01:22:40.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:31.188 [01:22:40.108] > «sigo con la orden»
16:22:36.123 [01:22:45.033] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:36.188 [01:22:45.108] > «sigo con la orden»
16:22:37.239 [01:22:46.158] < pide los sensores (0x50 05)
16:22:37.349 [01:22:46.266] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:41.117 [01:22:50.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:41.188 [01:22:50.108] > «sigo con la orden»
16:22:45.371 [01:22:54.290] < pide los sensores (0x50 05)
16:22:45.481 [01:22:54.398] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:46.116 [01:22:55.032] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:46.188 [01:22:55.108] > «sigo con la orden»
16:22:51.119 [01:23:00.035] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:51.190 [01:23:00.110] > «sigo con la orden»
16:22:53.503 [01:23:02.422] < pide los sensores (0x50 05)
16:22:53.613 [01:23:02.530] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:22:56.118 [01:23:05.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:22:56.190 [01:23:05.110] > «sigo con la orden»
16:23:00.238 [01:23:09.150] = carga parcial · agua 74.6 °C · dentro 13.0 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:23:00.248 [01:23:09.151] =   orden 0x21: quedan 719 s · último mantenimiento hace 4.1 s (a los 20 s se apaga)
16:23:00.260 [01:23:09.162] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:23:00.263 [01:23:09.173] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:23:01.118 [01:23:10.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:01.190 [01:23:10.110] > «sigo con la orden»
16:23:01.634 [01:23:10.554] < pide los sensores (0x50 05)
16:23:01.745 [01:23:10.662] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:06.118 [01:23:15.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:06.190 [01:23:15.110] > «sigo con la orden»
16:23:09.767 [01:23:18.686] < pide los sensores (0x50 05)
16:23:09.877 [01:23:18.794] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:11.118 [01:23:20.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:11.190 [01:23:20.110] > «sigo con la orden»
16:23:16.118 [01:23:25.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:16.190 [01:23:25.110] > «sigo con la orden»
16:23:17.898 [01:23:26.818] < pide los sensores (0x50 05)
16:23:18.009 [01:23:26.926] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:21.118 [01:23:30.034] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:21.189 [01:23:30.110] > «sigo con la orden»
16:23:26.030 [01:23:34.950] < pide los sensores (0x50 05)
16:23:26.141 [01:23:35.058] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:26.174 [01:23:35.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:23:26.246 [01:23:35.166] > «sigo con la orden»
16:23:31.174 [01:23:40.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:31.245 [01:23:40.166] > «sigo con la orden»
16:23:34.162 [01:23:43.082] < pide los sensores (0x50 05)
16:23:34.273 [01:23:43.190] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:36.174 [01:23:45.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:36.245 [01:23:45.166] > «sigo con la orden»
16:23:41.174 [01:23:50.091] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:41.245 [01:23:50.166] > «sigo con la orden»
16:23:42.294 [01:23:51.214] < pide los sensores (0x50 05)
16:23:42.405 [01:23:51.322] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:46.174 [01:23:55.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:46.245 [01:23:55.166] > «sigo con la orden»
16:23:50.426 [01:23:59.346] < pide los sensores (0x50 05)
16:23:50.537 [01:23:59.454] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:23:51.174 [01:24:00.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:51.246 [01:24:00.166] > «sigo con la orden»
16:23:56.173 [01:24:05.091] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:23:56.245 [01:24:05.166] > «sigo con la orden»
16:23:58.558 [01:24:07.478] < pide los sensores (0x50 05)
16:23:58.669 [01:24:07.586] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:00.239 [01:24:09.152] = carga parcial · agua 74.5 °C · dentro 13.4 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:24:00.250 [01:24:09.153] =   orden 0x21: quedan 659 s · último mantenimiento hace 4.1 s (a los 20 s se apaga)
16:24:00.261 [01:24:09.164] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:24:00.265 [01:24:09.175] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:24:01.173 [01:24:10.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:01.245 [01:24:10.166] > «sigo con la orden»
16:24:06.173 [01:24:15.091] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:06.245 [01:24:15.166] > «sigo con la orden»
16:24:06.690 [01:24:15.610] < pide los sensores (0x50 05)
16:24:06.800 [01:24:15.718] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:11.173 [01:24:20.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:11.245 [01:24:20.166] > «sigo con la orden»
16:24:14.822 [01:24:23.742] < pide los sensores (0x50 05)
16:24:14.932 [01:24:23.850] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:16.173 [01:24:25.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:16.245 [01:24:25.166] > «sigo con la orden»
16:24:21.195 [01:24:30.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:21.245 [01:24:30.166] > «sigo con la orden»
16:24:22.954 [01:24:31.874] < pide los sensores (0x50 05)
16:24:23.064 [01:24:31.982] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:26.173 [01:24:35.090] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:26.245 [01:24:35.166] > «sigo con la orden»
16:24:31.086 [01:24:40.006] < pide los sensores (0x50 05)
16:24:31.196 [01:24:40.114] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:31.229 [01:24:40.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:24:31.301 [01:24:40.222] > «sigo con la orden»
16:24:36.229 [01:24:45.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:36.301 [01:24:45.222] > «sigo con la orden»
16:24:39.217 [01:24:48.138] < pide los sensores (0x50 05)
16:24:39.328 [01:24:48.246] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:41.229 [01:24:50.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:41.301 [01:24:50.222] > «sigo con la orden»
16:24:46.229 [01:24:55.147] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:46.301 [01:24:55.222] > «sigo con la orden»
16:24:47.350 [01:24:56.270] < pide los sensores (0x50 05)
16:24:47.460 [01:24:56.378] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:51.229 [01:25:00.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:51.301 [01:25:00.222] > «sigo con la orden»
16:24:55.481 [01:25:04.402] < pide los sensores (0x50 05)
16:24:55.592 [01:25:04.510] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:24:56.229 [01:25:05.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:24:56.300 [01:25:05.222] > «sigo con la orden»
16:25:00.240 [01:25:09.154] = carga parcial · agua 74.5 °C · dentro 13.8 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:25:00.251 [01:25:09.155] =   orden 0x21: quedan 599 s · último mantenimiento hace 4.0 s (a los 20 s se apaga)
16:25:00.262 [01:25:09.166] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:25:00.266 [01:25:09.177] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:25:01.228 [01:25:10.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:01.300 [01:25:10.222] > «sigo con la orden»
16:25:03.613 [01:25:12.534] < pide los sensores (0x50 05)
16:25:03.724 [01:25:12.642] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:06.228 [01:25:15.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:06.301 [01:25:15.222] > «sigo con la orden»
16:25:11.229 [01:25:20.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:11.301 [01:25:20.222] > «sigo con la orden»
16:25:11.745 [01:25:20.666] < pide los sensores (0x50 05)
16:25:11.856 [01:25:20.774] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:16.228 [01:25:25.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:16.300 [01:25:25.222] > «sigo con la orden»
16:25:19.877 [01:25:28.798] < pide los sensores (0x50 05)
16:25:19.987 [01:25:28.906] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:21.229 [01:25:30.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:21.300 [01:25:30.222] > «sigo con la orden»
16:25:26.228 [01:25:35.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:26.300 [01:25:35.222] > «sigo con la orden»
16:25:28.009 [01:25:36.930] < pide los sensores (0x50 05)
16:25:28.119 [01:25:37.038] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:31.229 [01:25:40.146] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:31.300 [01:25:40.222] > «sigo con la orden»
16:25:36.141 [01:25:45.062] < pide los sensores (0x50 05)
16:25:36.252 [01:25:45.170] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:36.284 [01:25:45.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:25:36.356 [01:25:45.278] > «sigo con la orden»
16:25:41.285 [01:25:50.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:41.356 [01:25:50.278] > «sigo con la orden»
16:25:44.273 [01:25:53.194] < pide los sensores (0x50 05)
16:25:44.384 [01:25:53.302] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:46.284 [01:25:55.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:46.356 [01:25:55.278] > «sigo con la orden»
16:25:51.285 [01:26:00.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:51.356 [01:26:00.278] > «sigo con la orden»
16:25:52.407 [01:26:01.328] < pide los sensores (0x50 05)
16:25:52.518 [01:26:01.436] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:25:56.284 [01:26:05.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:25:56.356 [01:26:05.278] > «sigo con la orden»
16:26:00.242 [01:26:09.156] = carga parcial · agua 74.5 °C · dentro 14.2 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:26:00.253 [01:26:09.157] =   orden 0x21: quedan 539 s · último mantenimiento hace 4.0 s (a los 20 s se apaga)
16:26:00.264 [01:26:09.168] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:26:00.269 [01:26:09.179] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:26:00.539 [01:26:09.460] < pide los sensores (0x50 05)
16:26:00.649 [01:26:09.568] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:01.284 [01:26:10.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:01.356 [01:26:10.278] > «sigo con la orden»
16:26:06.284 [01:26:15.203] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:06.356 [01:26:15.278] > «sigo con la orden»
16:26:08.690 [01:26:17.612] < pide los sensores (0x50 05)
16:26:08.801 [01:26:17.720] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:11.284 [01:26:20.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:11.356 [01:26:20.278] > «sigo con la orden»
16:26:16.284 [01:26:25.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:16.356 [01:26:25.278] > «sigo con la orden»
16:26:16.822 [01:26:25.744] < pide los sensores (0x50 05)
16:26:16.933 [01:26:25.852] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:21.284 [01:26:30.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:21.356 [01:26:30.278] > «sigo con la orden»
16:26:24.954 [01:26:33.876] < pide los sensores (0x50 05)
16:26:25.065 [01:26:33.984] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:26.284 [01:26:35.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:26.356 [01:26:35.278] > «sigo con la orden»
16:26:31.284 [01:26:40.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:31.356 [01:26:40.278] > «sigo con la orden»
16:26:33.086 [01:26:42.008] < pide los sensores (0x50 05)
16:26:33.197 [01:26:42.116] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:36.284 [01:26:45.202] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:36.356 [01:26:45.278] > «sigo con la orden»
16:26:41.218 [01:26:50.140] < pide los sensores (0x50 05)
16:26:41.329 [01:26:50.248] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:41.361 [01:26:50.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:26:41.433 [01:26:50.356] > «sigo con la orden»
16:26:46.362 [01:26:55.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:46.433 [01:26:55.356] > «sigo con la orden»
16:26:49.350 [01:26:58.272] < pide los sensores (0x50 05)
16:26:49.461 [01:26:58.380] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:26:51.362 [01:27:00.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:51.434 [01:27:00.356] > «sigo con la orden»
16:26:56.362 [01:27:05.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:26:56.433 [01:27:05.356] > «sigo con la orden»
16:26:57.482 [01:27:06.404] < pide los sensores (0x50 05)
16:26:57.593 [01:27:06.512] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:00.243 [01:27:09.158] = carga parcial · agua 74.5 °C · dentro 14.6 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:27:00.254 [01:27:09.159] =   orden 0x21: quedan 479 s · último mantenimiento hace 3.9 s (a los 20 s se apaga)
16:27:00.265 [01:27:09.170] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:27:00.269 [01:27:09.181] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:27:01.361 [01:27:10.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:01.433 [01:27:10.356] > «sigo con la orden»
16:27:05.614 [01:27:14.536] < pide los sensores (0x50 05)
16:27:05.725 [01:27:14.644] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:06.361 [01:27:15.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:06.433 [01:27:15.356] > «sigo con la orden»
16:27:11.361 [01:27:20.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:11.433 [01:27:20.356] > «sigo con la orden»
16:27:13.745 [01:27:22.668] < pide los sensores (0x50 05)
16:27:13.856 [01:27:22.776] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:16.361 [01:27:25.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:16.433 [01:27:25.356] > «sigo con la orden»
16:27:21.361 [01:27:30.281] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:21.433 [01:27:30.356] > «sigo con la orden»
16:27:21.878 [01:27:30.800] < pide los sensores (0x50 05)
16:27:21.988 [01:27:30.908] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:26.361 [01:27:35.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:26.433 [01:27:35.356] > «sigo con la orden»
16:27:30.010 [01:27:38.932] < pide los sensores (0x50 05)
16:27:30.120 [01:27:39.040] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:31.361 [01:27:40.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:31.433 [01:27:40.356] > «sigo con la orden»
16:27:36.361 [01:27:45.281] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:36.433 [01:27:45.356] > «sigo con la orden»
16:27:38.142 [01:27:47.064] < pide los sensores (0x50 05)
16:27:38.252 [01:27:47.172] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:41.361 [01:27:50.280] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:41.433 [01:27:50.356] > «sigo con la orden»
16:27:46.273 [01:27:55.196] < pide los sensores (0x50 05)
16:27:46.384 [01:27:55.304] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:46.417 [01:27:55.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:27:46.489 [01:27:55.412] > «sigo con la orden»
16:27:51.417 [01:28:00.337] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:51.489 [01:28:00.412] > «sigo con la orden»
16:27:54.405 [01:28:03.328] < pide los sensores (0x50 05)
16:27:54.516 [01:28:03.436] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:27:56.417 [01:28:05.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:27:56.489 [01:28:05.412] > «sigo con la orden»
16:28:00.244 [01:28:09.160] = carga parcial · agua 74.5 °C · dentro 15.0 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:28:00.255 [01:28:09.161] =   orden 0x21: quedan 419 s · último mantenimiento hace 3.8 s (a los 20 s se apaga)
16:28:00.269 [01:28:09.172] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:28:00.273 [01:28:09.183] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:28:01.416 [01:28:10.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:01.489 [01:28:10.412] > «sigo con la orden»
16:28:02.537 [01:28:11.460] < pide los sensores (0x50 05)
16:28:02.648 [01:28:11.568] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:06.417 [01:28:15.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:06.488 [01:28:15.412] > «sigo con la orden»
16:28:10.669 [01:28:19.592] < pide los sensores (0x50 05)
16:28:10.780 [01:28:19.700] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:11.417 [01:28:20.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:11.489 [01:28:20.412] > «sigo con la orden»
16:28:16.417 [01:28:25.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:16.488 [01:28:25.412] > «sigo con la orden»
16:28:18.801 [01:28:27.724] < pide los sensores (0x50 05)
16:28:18.912 [01:28:27.832] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:21.417 [01:28:30.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:21.488 [01:28:30.412] > «sigo con la orden»
16:28:26.417 [01:28:35.337] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:26.488 [01:28:35.412] > «sigo con la orden»
16:28:26.933 [01:28:35.856] < pide los sensores (0x50 05)
16:28:27.043 [01:28:35.964] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:31.417 [01:28:40.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:31.488 [01:28:40.412] > «sigo con la orden»
16:28:35.065 [01:28:43.988] < pide los sensores (0x50 05)
16:28:35.176 [01:28:44.096] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:36.417 [01:28:45.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:36.488 [01:28:45.412] > «sigo con la orden»
16:28:41.416 [01:28:50.337] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:41.488 [01:28:50.412] > «sigo con la orden»
16:28:43.211 [01:28:52.134] < pide los sensores (0x50 05)
16:28:43.322 [01:28:52.242] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:46.416 [01:28:55.336] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:46.488 [01:28:55.412] > «sigo con la orden»
16:28:51.343 [01:29:00.266] < pide los sensores (0x50 05)
16:28:51.454 [01:29:00.374] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:28:51.486 [01:29:00.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:28:51.558 [01:29:00.482] > «sigo con la orden»
16:28:56.486 [01:29:05.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:28:56.558 [01:29:05.482] > «sigo con la orden»
16:28:59.475 [01:29:08.398] < pide los sensores (0x50 05)
16:28:59.585 [01:29:08.506] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:00.246 [01:29:09.162] = carga parcial · agua 74.5 °C · dentro 15.4 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:29:00.257 [01:29:09.163] =   orden 0x21: quedan 359 s · último mantenimiento hace 3.8 s (a los 20 s se apaga)
16:29:00.271 [01:29:09.174] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:29:00.274 [01:29:09.185] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:29:01.486 [01:29:10.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:01.558 [01:29:10.482] > «sigo con la orden»
16:29:06.486 [01:29:15.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:06.558 [01:29:15.482] > «sigo con la orden»
16:29:07.607 [01:29:16.530] < pide los sensores (0x50 05)
16:29:07.717 [01:29:16.638] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:11.486 [01:29:20.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:11.557 [01:29:20.482] > «sigo con la orden»
16:29:15.740 [01:29:24.664] < pide los sensores (0x50 05)
16:29:15.851 [01:29:24.772] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:16.486 [01:29:25.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:16.558 [01:29:25.482] > «sigo con la orden»
16:29:21.486 [01:29:30.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:21.558 [01:29:30.482] > «sigo con la orden»
16:29:23.872 [01:29:32.796] < pide los sensores (0x50 05)
16:29:23.983 [01:29:32.904] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:26.486 [01:29:35.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:26.558 [01:29:35.482] > «sigo con la orden»
16:29:31.486 [01:29:40.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:31.557 [01:29:40.482] > «sigo con la orden»
16:29:32.006 [01:29:40.930] < pide los sensores (0x50 05)
16:29:32.117 [01:29:41.038] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:36.486 [01:29:45.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:36.558 [01:29:45.482] > «sigo con la orden»
16:29:40.140 [01:29:49.064] < pide los sensores (0x50 05)
16:29:40.251 [01:29:49.172] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:41.486 [01:29:50.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:41.558 [01:29:50.482] > «sigo con la orden»
16:29:46.486 [01:29:55.407] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:46.558 [01:29:55.482] > «sigo con la orden»
16:29:48.272 [01:29:57.196] < pide los sensores (0x50 05)
16:29:48.383 [01:29:57.304] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:51.486 [01:30:00.406] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:29:51.558 [01:30:00.482] > «sigo con la orden»
16:29:56.404 [01:30:05.328] < pide los sensores (0x50 05)
16:29:56.515 [01:30:05.436] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:29:56.546 [01:30:05.466] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:29:56.617 [01:30:05.542] > «sigo con la orden»
16:30:00.247 [01:30:09.164] = carga parcial · agua 74.5 °C · dentro 15.8 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:30:00.258 [01:30:09.165] =   orden 0x21: quedan 299 s · último mantenimiento hace 3.7 s (a los 20 s se apaga)
16:30:00.272 [01:30:09.176] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:30:00.276 [01:30:09.187] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:30:01.546 [01:30:10.466] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:01.617 [01:30:10.542] > «sigo con la orden»
16:30:04.536 [01:30:13.460] < pide los sensores (0x50 05)
16:30:04.646 [01:30:13.568] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:06.546 [01:30:15.466] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:06.617 [01:30:15.542] > «sigo con la orden»
16:30:11.546 [01:30:20.467] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:11.617 [01:30:20.542] > «sigo con la orden»
16:30:12.668 [01:30:21.592] < pide los sensores (0x50 05)
16:30:12.779 [01:30:21.700] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:16.547 [01:30:25.468] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:16.619 [01:30:25.544] > «sigo con la orden»
16:30:20.800 [01:30:29.724] < pide los sensores (0x50 05)
16:30:20.911 [01:30:29.832] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:21.547 [01:30:30.468] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:21.619 [01:30:30.544] > «sigo con la orden»
16:30:26.547 [01:30:35.469] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:26.619 [01:30:35.544] > «sigo con la orden»
16:30:28.932 [01:30:37.856] < pide los sensores (0x50 05)
16:30:29.042 [01:30:37.964] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:31.549 [01:30:40.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:31.621 [01:30:40.546] > «sigo con la orden»
16:30:36.549 [01:30:45.471] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:36.621 [01:30:45.546] > «sigo con la orden»
16:30:37.063 [01:30:45.988] < pide los sensores (0x50 05)
16:30:37.174 [01:30:46.096] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:41.549 [01:30:50.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:41.621 [01:30:50.546] > «sigo con la orden»
16:30:45.196 [01:30:54.120] < pide los sensores (0x50 05)
16:30:45.306 [01:30:54.228] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:46.549 [01:30:55.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:46.621 [01:30:55.546] > «sigo con la orden»
16:30:51.549 [01:31:00.471] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:51.621 [01:31:00.546] > «sigo con la orden»
16:30:53.328 [01:31:02.252] < pide los sensores (0x50 05)
16:30:53.438 [01:31:02.360] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:30:56.549 [01:31:05.470] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:30:56.621 [01:31:05.546] > «sigo con la orden»
16:31:00.248 [01:31:09.166] = carga parcial · agua 74.5 °C · dentro 16.1 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:31:00.259 [01:31:09.167] =   orden 0x21: quedan 239 s · último mantenimiento hace 3.7 s (a los 20 s se apaga)
16:31:00.271 [01:31:09.178] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:31:00.275 [01:31:09.189] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:31:01.459 [01:31:10.384] < pide los sensores (0x50 05)
16:31:01.570 [01:31:10.492] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:01.603 [01:31:10.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:31:01.675 [01:31:10.600] > «sigo con la orden»
16:31:06.603 [01:31:15.525] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:06.675 [01:31:15.600] > «sigo con la orden»
16:31:09.591 [01:31:18.516] < pide los sensores (0x50 05)
16:31:09.702 [01:31:18.624] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:11.603 [01:31:20.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:11.675 [01:31:20.600] > «sigo con la orden»
16:31:16.603 [01:31:25.525] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:16.674 [01:31:25.600] > «sigo con la orden»
16:31:17.723 [01:31:26.648] < pide los sensores (0x50 05)
16:31:17.834 [01:31:26.756] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:21.603 [01:31:30.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:21.674 [01:31:30.600] > «sigo con la orden»
16:31:25.855 [01:31:34.780] < pide los sensores (0x50 05)
16:31:25.966 [01:31:34.888] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:26.603 [01:31:35.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:26.674 [01:31:35.600] > «sigo con la orden»
16:31:31.602 [01:31:40.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:31.674 [01:31:40.600] > «sigo con la orden»
16:31:33.987 [01:31:42.912] < pide los sensores (0x50 05)
16:31:34.098 [01:31:43.020] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:36.602 [01:31:45.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:36.674 [01:31:45.600] > «sigo con la orden»
16:31:41.603 [01:31:50.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:41.674 [01:31:50.600] > «sigo con la orden»
16:31:42.119 [01:31:51.044] < pide los sensores (0x50 05)
16:31:42.230 [01:31:51.152] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:46.603 [01:31:55.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:46.674 [01:31:55.600] > «sigo con la orden»
16:31:50.251 [01:31:59.176] < pide los sensores (0x50 05)
16:31:50.361 [01:31:59.284] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:31:51.603 [01:32:00.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:51.674 [01:32:00.600] > «sigo con la orden»
16:31:56.602 [01:32:05.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:31:56.674 [01:32:05.600] > «sigo con la orden»
16:31:58.383 [01:32:07.308] < pide los sensores (0x50 05)
16:31:58.493 [01:32:07.416] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:00.250 [01:32:09.168] = carga parcial · agua 74.5 °C · dentro 16.5 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:32:00.261 [01:32:09.169] =   orden 0x21: quedan 179 s · último mantenimiento hace 3.6 s (a los 20 s se apaga)
16:32:00.273 [01:32:09.180] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:32:00.276 [01:32:09.191] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:32:01.602 [01:32:10.524] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:01.674 [01:32:10.600] > «sigo con la orden»
16:32:06.515 [01:32:15.440] < pide los sensores (0x50 05)
16:32:06.625 [01:32:15.548] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:06.658 [01:32:15.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:32:06.730 [01:32:15.656] > «sigo con la orden»
16:32:11.658 [01:32:20.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:11.730 [01:32:20.656] > «sigo con la orden»
16:32:14.647 [01:32:23.572] < pide los sensores (0x50 05)
16:32:14.757 [01:32:23.680] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:16.658 [01:32:25.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:16.730 [01:32:25.656] > «sigo con la orden»
16:32:21.658 [01:32:30.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:21.730 [01:32:30.656] > «sigo con la orden»
16:32:22.781 [01:32:31.706] < pide los sensores (0x50 05)
16:32:22.891 [01:32:31.814] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:26.658 [01:32:35.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:26.730 [01:32:35.656] > «sigo con la orden»
16:32:30.912 [01:32:39.838] < pide los sensores (0x50 05)
16:32:31.023 [01:32:39.946] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:31.658 [01:32:40.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:31.730 [01:32:40.656] > «sigo con la orden»
16:32:36.658 [01:32:45.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:36.730 [01:32:45.656] > «sigo con la orden»
16:32:39.044 [01:32:47.970] < pide los sensores (0x50 05)
16:32:39.155 [01:32:48.078] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:41.658 [01:32:50.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:41.729 [01:32:50.656] > «sigo con la orden»
16:32:46.658 [01:32:55.581] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:46.730 [01:32:55.656] > «sigo con la orden»
16:32:47.176 [01:32:56.102] < pide los sensores (0x50 05)
16:32:47.287 [01:32:56.210] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:51.658 [01:33:00.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:51.730 [01:33:00.656] > «sigo con la orden»
16:32:55.308 [01:33:04.234] < pide los sensores (0x50 05)
16:32:55.419 [01:33:04.342] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:32:56.658 [01:33:05.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:32:56.729 [01:33:05.656] > «sigo con la orden»
16:33:00.251 [01:33:09.170] = carga parcial · agua 74.5 °C · dentro 16.9 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:33:00.262 [01:33:09.171] =   orden 0x21: quedan 119 s · último mantenimiento hace 3.6 s (a los 20 s se apaga)
16:33:00.273 [01:33:09.182] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:33:00.277 [01:33:09.193] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:33:01.658 [01:33:10.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:01.729 [01:33:10.656] > «sigo con la orden»
16:33:03.440 [01:33:12.366] < pide los sensores (0x50 05)
16:33:03.551 [01:33:12.474] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:06.658 [01:33:15.580] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:06.729 [01:33:15.656] > «sigo con la orden»
16:33:11.572 [01:33:20.498] < pide los sensores (0x50 05)
16:33:11.682 [01:33:20.606] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:11.716 [01:33:20.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:33:11.787 [01:33:20.714] > «sigo con la orden»
16:33:16.716 [01:33:25.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:16.787 [01:33:25.714] > «sigo con la orden»
16:33:19.703 [01:33:28.630] < pide los sensores (0x50 05)
16:33:19.815 [01:33:28.738] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:21.733 [01:33:30.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:21.787 [01:33:30.714] > «sigo con la orden»
16:33:26.715 [01:33:35.639] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:26.787 [01:33:35.714] > «sigo con la orden»
16:33:28.474 [01:33:37.400] < pide los sensores (0x50 05)
16:33:28.584 [01:33:37.508] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:31.715 [01:33:40.638] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:31.787 [01:33:40.714] > «sigo con la orden»
16:33:36.606 [01:33:45.532] < pide los sensores (0x50 05)
16:33:36.716 [01:33:45.640] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:36.747 [01:33:45.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:36.819 [01:33:45.746] > «sigo con la orden»
16:33:41.747 [01:33:50.671] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:41.819 [01:33:50.746] > «sigo con la orden»
16:33:44.740 [01:33:53.666] < pide los sensores (0x50 05)
16:33:44.850 [01:33:53.774] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:46.747 [01:33:55.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:46.819 [01:33:55.746] > «sigo con la orden»
16:33:51.747 [01:34:00.671] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:51.819 [01:34:00.746] > «sigo con la orden»
16:33:52.871 [01:34:01.798] < pide los sensores (0x50 05)
16:33:52.983 [01:34:01.906] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:33:56.747 [01:34:05.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:33:56.818 [01:34:05.746] > «sigo con la orden»
16:34:00.252 [01:34:09.172] = carga parcial · agua 74.5 °C · dentro 17.3 °C · fuera 5.0 °C · 12.44 V · llama sí · 2500 W
16:34:00.263 [01:34:09.173] =   orden 0x21: quedan 59 s · último mantenimiento hace 3.5 s (a los 20 s se apaga)
16:34:00.275 [01:34:09.184] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:34:00.280 [01:34:09.195] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:34:01.003 [01:34:09.930] < pide los sensores (0x50 05)
16:34:01.114 [01:34:10.038] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:01.747 [01:34:10.670] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:01.818 [01:34:10.746] > «sigo con la orden»
16:34:06.749 [01:34:15.673] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:06.820 [01:34:15.748] > «sigo con la orden»
16:34:09.135 [01:34:18.062] < pide los sensores (0x50 05)
16:34:09.247 [01:34:18.170] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:11.749 [01:34:20.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:11.821 [01:34:20.748] > «sigo con la orden»
16:34:16.749 [01:34:25.673] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:16.821 [01:34:25.748] > «sigo con la orden»
16:34:17.267 [01:34:26.194] < pide los sensores (0x50 05)
16:34:17.378 [01:34:26.302] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:21.749 [01:34:30.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:21.821 [01:34:30.748] > «sigo con la orden»
16:34:25.399 [01:34:34.326] < pide los sensores (0x50 05)
16:34:25.510 [01:34:34.434] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:26.749 [01:34:35.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:26.820 [01:34:35.748] > «sigo con la orden»
16:34:31.746 [01:34:40.671] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:31.818 [01:34:40.746] > «sigo con la orden»
16:34:33.531 [01:34:42.458] < pide los sensores (0x50 05)
16:34:33.642 [01:34:42.566] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:36.749 [01:34:45.672] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:36.820 [01:34:45.748] > «sigo con la orden»
16:34:41.663 [01:34:50.590] < pide los sensores (0x50 05)
16:34:41.773 [01:34:50.698] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:41.807 [01:34:50.730] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.1 s
16:34:41.878 [01:34:50.806] > «sigo con la orden»
16:34:46.807 [01:34:55.731] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:46.878 [01:34:55.806] > «sigo con la orden»
16:34:49.795 [01:34:58.722] < pide los sensores (0x50 05)
16:34:49.905 [01:34:58.830] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:34:51.807 [01:35:00.730] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:51.879 [01:35:00.806] > «sigo con la orden»
16:34:56.806 [01:35:05.731] < mantenimiento: «sigue con la orden 0x21» · el anterior hace 5.0 s
16:34:56.878 [01:35:05.806] > «sigo con la orden»
16:34:57.926 [01:35:06.854] < pide los sensores (0x50 05)
16:34:58.037 [01:35:06.962] > agua 75 °C · 12.44 V · llama sí · 2500 W · bujía 1.55 Ω
16:35:00.192 [01:35:09.116] . estado: carga parcial → postbarrido (se acabó el tiempo pedido)
16:35:00.254 [01:35:09.174] = postbarrido · agua 74.5 °C · dentro 17.6 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
16:35:00.265 [01:35:09.175] =   orden 0x21: quedan 0 s · último mantenimiento hace 3.4 s (a los 20 s se apaga)
16:35:00.276 [01:35:09.186] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:35:00.279 [01:35:09.197] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:35:00.300 [01:35:09.226] < apagar
16:35:00.310 [01:35:09.227] . apagar: ya estaba postbarrido, no cambia nada
16:35:00.365 [01:35:09.293] > «apagando» → postbarrido
16:35:06.058 [01:35:14.985] < pide los sensores (0x50 05)
16:35:06.168 [01:35:15.093] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.55 Ω
16:35:14.191 [01:35:23.119] < pide los sensores (0x50 05)
16:35:14.302 [01:35:23.227] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.55 Ω
16:35:22.323 [01:35:31.251] < pide los sensores (0x50 05)
16:35:22.434 [01:35:31.359] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:30.458 [01:35:39.383] < pide los sensores (0x50 05)
16:35:30.566 [01:35:39.491] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:38.587 [01:35:47.515] < pide los sensores (0x50 05)
16:35:38.698 [01:35:47.623] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:46.719 [01:35:55.647] < pide los sensores (0x50 05)
16:35:46.830 [01:35:55.755] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:35:54.851 [01:36:03.779] < pide los sensores (0x50 05)
16:35:54.961 [01:36:03.887] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:36:00.254 [01:36:09.175] = postbarrido · agua 73.8 °C · dentro 17.6 °C · fuera 5.0 °C · 12.48 V · llama no · 0 W
16:36:00.265 [01:36:09.176] =   orden 0x21: quedan 0 s · último mantenimiento hace 3.4 s (a los 20 s se apaga)
16:36:00.277 [01:36:09.187] =   averías: ninguna · intentos en este arranque 0 · arranques fallidos seguidos 0 de 3
16:36:00.279 [01:36:09.198] =   fallos provocados: ninguno · tiempo ×1 · eco sí · RX 16 TX 17 · log 1
16:36:02.983 [01:36:11.911] < pide los sensores (0x50 05)
16:36:03.093 [01:36:12.019] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
16:36:11.114 [01:36:20.043] < pide los sensores (0x50 05)
16:36:11.225 [01:36:20.151] > agua 74 °C · 12.48 V · llama no · 0 W · bujía 1.54 Ω
```

