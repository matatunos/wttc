# Servidor de estadísticas (wttc.favala.es)

Código tal cual se ejecuta en https://wttc.favala.es (PHP 8 + SQLite), publicado para que se pueda comprobar qué se guarda.

- `api/stats.php`: recibe el informe diario de la app **solo si el usuario lo ha aceptado**. Valida cada campo, admite un
  informe por instalación y día y borra los datos de una instalación cuando la app lo pide al retirar el permiso.
  No lee la IP, y el proxy (Caddy) tiene desactivado el registro de esta ruta (`log_skip`).
- `api/db.php`: esquema SQLite (instalaciones, días con informe, totales y averías).
- `estadisticas.php`: la página pública con los datos agregados.

Qué envía la app: identificador aleatorio de instalación, versión de la app y del firmware, versión de Android, tipo de
dispositivo (móvil, tablet o radio), país según el idioma del sistema, encendidos (desde la app y por programa), veces
que se apagó sola y sus códigos de avería. Ver `android/app/src/main/java/es/favala/wttc/Stats.kt`.
