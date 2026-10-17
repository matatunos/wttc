#!/bin/bash
# paquete_instalar.sh — Empaqueta un programa ya compilado para instalarlo desde el navegador (wttc.favala.es/instalar.php,
# con ESP Web Tools): cargador de arranque, tabla de particiones, boot_app0 y programa, más placa.json con la versión,
# el chip y la dirección del cargador. La zona de configuración (NVS) no va: reinstalar así no borra lo guardado.
# Código generado íntegramente con Claude (Anthropic). Lo usa .github/workflows/firmware.yml para WTTC y para la
# Webasto falsa; wttc-instalador.sh (server/scripts) lo desempaqueta en el servidor.
#
#   paquete_instalar.sh <carpeta de la compilación> <sketch> <nombre> <chip> <chipFamily> <dirección del cargador>
#   p. ej.: paquete_instalar.sh build WTTC.ino instalar-esp32s3 esp32s3 ESP32-S3 0
# Deja build/WTTC-<versión>-<nombre>.zip (con la carpeta <nombre>/ dentro) y escribe su ruta en la salida.
set -euo pipefail
dir=$1 sketch=$2 name=$3 chip=$4 family=$5 boot=$6
v=$(tr -d ' \r\n' < VERSION)
d="$dir/$name"; rm -rf "$d"; mkdir -p "$d"
cp "$dir/$sketch.bootloader.bin" "$d/bootloader.bin"
cp "$dir/$sketch.partitions.bin" "$d/partitions.bin"
cp "$(find ~/.arduino15/packages/esp32/hardware/esp32 -name boot_app0.bin | head -1)" "$d/boot_app0.bin"
cp "$dir/$sketch.bin" "$d/programa.bin"
printf '{"version":"%s","chip":"%s","chipFamily":"%s","bootloader":%d}\n' "$v" "$chip" "$family" "$boot" > "$d/placa.json"
(cd "$dir" && zip -qr "WTTC-$v-$name.zip" "$name")
echo "::notice title=Paquete $name::$(cd "$d" && for f in *; do printf '%s %s B · ' "$f" "$(stat -c %s "$f")"; done)" >&2
echo "$dir/WTTC-$v-$name.zip"
