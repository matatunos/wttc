#!/bin/bash
# publicar-github.sh — Sube apps/wttc de homelab-scripts a su espejo público https://github.com/matatunos/wttc
# (de ahí salen las Releases con el APK y el firmware, y corre su CI). El código vive en homelab-scripts: el espejo no se
# toca a mano. `git subtree split` rehace la historia de apps/wttc con los mismos commits que ya tiene el espejo, así
# que la subida siempre avanza sin reescribir nada.
#
# Versiones: se etiquetan en homelab-scripts como wttc/vX.Y.Z y llegan al espejo como vX.Y.Z (las de antes de la
# integración, del 2026-11-07, ya apuntan a commits del espejo y se suben tal cual).
#
#   publicar-github.sh            sube main y las etiquetas que falten
set -euo pipefail
HS=/root/hs-repo
P=apps/wttc
REMOTO=git@github-wttc:matatunos/wttc.git

cd "$HS"
main=$(git subtree split -q --prefix="$P" main)
git push -q "$REMOTO" "$main:refs/heads/main"
echo "main → $(git log -1 --format='%h %s' "$main")"

ya=$(git ls-remote --tags "$REMOTO" | awk '{print $2}' | sed 's#^refs/tags/##; s#\^{}$##' | sort -u)
for t in $(git tag -l 'wttc/*'); do
    v=${t#wttc/}
    grep -qxF "$v" <<<"$ya" && continue
    c=$(git rev-list -n1 "$t")
    if git cat-file -e "$c:$P" 2>/dev/null; then     # etiqueta puesta en homelab-scripts: su commit en el espejo
        c=$(git subtree split -q --prefix="$P" "$c")
    fi
    git push -q "$REMOTO" "$c:refs/tags/$v"
    echo "etiqueta $v → $(git log -1 --format=%h "$c")"
done
