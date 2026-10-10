#!/bin/bash
# deploy-web.sh — Copia del repo a producción la web pública de WTTC (https://wttc.favala.es) y lo que la mantiene.
# Código generado íntegramente con Claude (Anthropic).
#
# El repo es la fuente: en producción no se edita nada a mano. Lo lanza el hook post-commit en main
# (server/scripts/post-commit) antes de wttc-publicar.sh; también se puede lanzar suelto.
#
#   web/                        → WEB_DIR            (páginas, textos en 3 idiomas, simulador, informes PDF, iconos)
#   server/*.php, server/api/   → WEB_DIR, WEB_DIR/api (estadísticas públicas, mis estadísticas y sus API)
#   server/privado/wttc-stats/  → TOOLS_DIR/wttc-stats (vista privada, con login del portal)
#   server/scripts/*.sh         → /usr/local/bin     (publicación del firmware y del instalador web, avisos por Telegram)
#   server/scripts/cron.d-*     → /etc/cron.d/
#   server/scripts/post-commit, post-merge → .git/hooks/ (los hooks no viajan con git: se instalan desde aquí)
#
# No se borra nada en producción (sin --delete): allí también viven ficheros GENERADOS que no están en el repo
# (movil.php y descargas/, de wttc-publicar.sh y wttc-instalador.sh) y librerías de terceros (vendor/).
set -euo pipefail
export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

# ---------- configuración ----------
REPO=/root/wttc
TOOLS_DIR=/root/tools-vps/appdata/www/tools
WEB_DIR="$TOOLS_DIR/wttc"
BIN_DIR=/usr/local/bin
CRON_DIR=/etc/cron.d

# ---------- web pública ----------
rsync -a --checksum "$REPO/web/" "$WEB_DIR/"
rsync -a --checksum "$REPO/server/estadisticas.php" "$REPO/server/mi.php" "$WEB_DIR/"
rsync -a --checksum "$REPO/server/api/" "$WEB_DIR/api/"

# ---------- vista privada ----------
mkdir -p "$TOOLS_DIR/wttc-stats"
rsync -a --checksum "$REPO/server/privado/wttc-stats/" "$TOOLS_DIR/wttc-stats/"

# ---------- scripts, cron y hook ----------
for f in wttc-publicar.sh wttc-instalador.sh wttc-aviso.sh wttc-orden.sh; do
    cmp -s "$REPO/server/scripts/$f" "$BIN_DIR/$f" || install -m 755 "$REPO/server/scripts/$f" "$BIN_DIR/$f"
done
cmp -s "$REPO/server/scripts/cron.d-wttc-instalador" "$CRON_DIR/wttc-instalador" \
    || install -m 644 "$REPO/server/scripts/cron.d-wttc-instalador" "$CRON_DIR/wttc-instalador"
for h in post-commit post-merge; do              # tras un commit y tras una fusión (git merge / git pull) en main
    cmp -s "$REPO/server/scripts/$h" "$REPO/.git/hooks/$h" || install -m 755 "$REPO/server/scripts/$h" "$REPO/.git/hooks/$h"
done

echo "Web de WTTC desplegada desde el repo"
