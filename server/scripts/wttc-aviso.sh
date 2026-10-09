#!/bin/bash
# wttc-aviso.sh — Manda un aviso por Telegram (bot @Wttc_favala_bot) al terminar tareas largas o en segundo plano.
# Código generado íntegramente con Claude (Anthropic). Fuente: server/scripts/ del repo; lo instala deploy-web.sh.
# Uso: wttc-aviso.sh "texto"   (o por la entrada estándar). El token y el chat están en /root/.env (600, fuera del repo):
#   WTTC_BOT_TOKEN=…   WTTC_CHAT_ID=…
set -euo pipefail
export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
ENV_FILE=/root/.env

# shellcheck disable=SC1090
. "$ENV_FILE"
text="${1:-$(cat)}"
[ -n "$text" ] || { echo "wttc-aviso.sh: sin texto" >&2; exit 1; }
curl -fsS -X POST "https://api.telegram.org/bot${WTTC_BOT_TOKEN}/sendMessage" \
    -d chat_id="$WTTC_CHAT_ID" --data-urlencode text="$text" -o /dev/null
