# Servidor de estadísticas (wttc.favala.es)

Código tal cual se ejecuta en https://wttc.favala.es (PHP 8 + SQLite), publicado para que se pueda comprobar qué se guarda.

- `api/stats.php`: recibe el informe diario de la app **solo si el usuario lo ha aceptado**. Valida cada campo, admite un
  informe por instalación y día y borra los datos de una instalación cuando la app lo pide al retirar el permiso.
  No lee la IP, y el proxy (Caddy) tiene desactivado el registro de esta ruta (`log_skip`).
- `api/placa.php`: recibe las estadísticas de una placa **solo si se activa** «Enviar las estadísticas de esta placa»
  (las manda la placa, o la app si la placa no tiene internet): código de instalación, totales y cada encendido (hora,
  duración, gasoil estimado, temperaturas, quién la encendió, por qué se apagó y avería). Los repetidos se ignoran.
- `api/mi.php` y `mi.php`: la página con las estadísticas de una placa; el código va en el `#` de la dirección (no llega al
  servidor) y se pide por POST. Sin el código no hay forma de ver nada, y desde ahí se borran.
- `api/db.php`: esquema SQLite (instalaciones, días con informe, totales y averías).
- `estadisticas.php`: la página pública con los datos agregados.
- Visitas a la web: `wttc_visit()` en `api/db.php` suma por día las páginas vistas y las llegadas desde fuera de la web,
  con el dominio del que vienen (nunca la URL entera), sin cookies ni IP y sin contar bots. Solo se ve en la vista privada.

Qué envía la app: identificador aleatorio de instalación, versión de la app y del firmware, versión de Android, tipo de
dispositivo (móvil, tablet o radio), país según el idioma del sistema, encendidos (desde la app y por programa), veces
que se apagó sola y sus códigos de avería. Ver `android/app/src/main/java/es/favala/wttc/Stats.kt`.

## Dónde vive cada cosa y cómo se despliega

Todo el código de la web está en este repo; en producción no se edita nada a mano.

- `web/`: la web pública (portada en es/en/de con su simulador, `i18n/` con los textos, códigos de avería, instalación
  desde el navegador, `informe.js` para los informes PDF del simulador, iconos, `robots.txt`, `sitemap.xml`).
- `server/`: estadísticas (`estadisticas.php`, `mi.php`, `api/`) y la vista privada `privado/wttc-stats/` (con login del portal).
- `server/scripts/`:
  - `deploy-web.sh` copia todo lo anterior a producción, e instala los scripts, el cron y el hook.
  - `wttc-publicar.sh` genera `movil.php` (la web del firmware para el simulador, desde `firmware/WTTC/web.h`) y
    `descargas/` (firmware, versión, notas, textos, `ota.json`).
  - `wttc-instalador.sh` (cron cada 15 min, `cron.d-wttc-instalador`) prepara el instalador web y la copia del `.ota`.
  - `wttc-aviso.sh "texto"` manda un aviso por Telegram (bot @Wttc_favala_bot; token y chat en `/root/.env`, fuera del repo).
  - `post-commit` y `post-merge` son los hooks locales: en cada commit o fusión en `main` lanzan `deploy-web.sh` y luego
    `wttc-publicar.sh`.
- Fuera del repo, en producción, solo hay lo **generado** (`movil.php`, `descargas/`) y librerías de terceros
  (`vendor/`: jsPDF 2.5.1 y ESP Web Tools 10.4.0). Chart.js y `charts.js` son los comunes del portal.
