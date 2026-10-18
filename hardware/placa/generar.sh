#!/bin/bash
# generar.sh — Rehace la placa portadora de WTTC desde generar/circuito.py: esquema, comprobaciones, placa rutada y
# ficheros de fabricación (JLCPCB). Todo dentro del contenedor oficial de KiCad 9 (más Java para Freerouting), con
# topes de memoria y CPU. Uso: hardware/placa/generar.sh [esquema|placa|todo]   (generado con Claude, Anthropic)
set -euo pipefail
cd "$(dirname "$0")"
IMG=wttc-kicad:9
docker image inspect $IMG >/dev/null 2>&1 || docker build -q -t $IMG generar/ >/dev/null
docker run -i --rm --memory 3g --memory-swap 3g --cpus 2 --user "$(id -u):$(id -g)" -e HOME=/tmp/h \
  -v "$PWD:/p" -w /p $IMG bash -s "${1:-todo}" <<'DENTRO'
set -euo pipefail
paso=$1
mkdir -p $HOME/.config/kicad/9.0
cp /usr/share/kicad/template/sym-lib-table /usr/share/kicad/template/fp-lib-table $HOME/.config/kicad/9.0/
python3 generar/esquema.py
kicad-cli sch erc --severity-all --format report -o salida/erc.rpt wttc-placa.kicad_sch >/dev/null || true
grep -E "^\[" salida/erc.rpt | sort | uniq -c || echo "ERC: sin avisos"
kicad-cli sch export netlist --format kicadsexpr -o salida/wttc-placa.net wttc-placa.kicad_sch >/dev/null
kicad-cli sch export pdf -o salida/esquema.pdf wttc-placa.kicad_sch >/dev/null
[ "$paso" = esquema ] && exit 0
python3 generar/pcb.py "$paso"
DENTRO
