#!/bin/bash
# wttc-instalador.sh — (fuente: server/scripts/ del repo; lo instala deploy-web.sh) Prepara en https://wttc.favala.es/instalar.php la instalación del firmware desde el navegador.
# (generado con Claude, Anthropic; lo lanza cron cada 5 minutos)
# Copia de la Release de GitHub de la versión publicada (descargas/VERSION) los paquetes WTTC-<v>-instalar-<chip>.zip
# que adjunta el workflow del firmware, los descomprime en descargas/instalar/<v>/<chip>/ y escribe
# descargas/instalar/manifest.json para ESP Web Tools. Si ya está hecho para esa versión, sale sin hacer nada; si la
# Release aún no tiene los paquetes, lo vuelve a intentar en la siguiente pasada. Guarda solo las dos últimas versiones.
# Nada se compila aquí: solo se descargan y copian ficheros (unos pocos MB).
set -euo pipefail
WEB=/root/tools-vps/appdata/www/tools/wttc
DST=$WEB/descargas/instalar
v=$(tr -d ' \r\n' < "$WEB/descargas/VERSION")
[[ "$v" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] || exit 0
# Espejo de la actualización firmada (.ota) de esa versión en descargas/ota/: las placas la descargan de aquí y no de
# GitHub (sus descargas redirigen a otro servidor y a la placa se le cortaban). Se comprueba que es un .ota de WTTC de
# esa versión y de un tamaño razonable; la firma la comprueba la propia placa. Se guardan los dos últimos.
OTA_DIR=$WEB/descargas/ota
if [ ! -f "$OTA_DIR/WTTC-$v-s3.ota" ]; then
  mkdir -p "$OTA_DIR"; chmod 755 "$OTA_DIR"
  t=$(mktemp "$OTA_DIR/.dl.XXXXXX")
  if curl -sfL --max-time 180 -o "$t" "https://github.com/matatunos/wttc/releases/download/v$v/WTTC-$v-s3.ota" \
     && [ "$(head -c 8 "$t")" = "WTTCOTA1" ] \
     && [ "$(dd if="$t" bs=1 skip=8 count=16 2>/dev/null | tr -d '\0')" = "$v" ] \
     && [ "$(stat -c %s "$t")" -gt 500000 ] && [ "$(stat -c %s "$t")" -lt 2100000 ]; then
    chmod 644 "$t"; mv "$t" "$OTA_DIR/WTTC-$v-s3.ota"
    ls -1t "$OTA_DIR"/WTTC-*-s3.ota | tail -n +3 | xargs -r rm -f
    echo "Actualización $v en el espejo"
  else rm -f "$t"; fi
fi
# Con el .ota ya en el espejo, se publica el anuncio que dejó pendiente wttc-publicar.sh (ver allí)
PEND=$WEB/descargas/ota-pendiente.json
if [ -f "$OTA_DIR/WTTC-$v-s3.ota" ] && [ -f "$PEND" ] && grep -q "\"version\":\"$v\"" "$PEND"; then
  mv "$PEND" "$WEB/descargas/ota.json"
  echo "ota.json anuncia ya la $v"
fi
[ -f "$DST/$v/.listo" ] && exit 0
mkdir -p "$DST"
python3 - "$v" "$DST" <<'PY'
import io, json, os, shutil, sys, urllib.request, urllib.error, zipfile
v, dst = sys.argv[1], sys.argv[2]
# chip del paquete -> chipFamily de ESP Web Tools (la dirección del cargador viene en placa.json)
# Desde la 0.2.0 solo hay ESP32-S3 (hasta la 0.1.6 también había 'esp32': 'ESP32')
CHIPS = {'esp32s3': 'ESP32-S3'}
FILES = ('bootloader.bin', 'partitions.bin', 'boot_app0.bin', 'wttc.bin', 'placa.json')
tmp = os.path.join(dst, f'.{v}.tmp'); shutil.rmtree(tmp, ignore_errors=True)
builds = []
for chip, fam in CHIPS.items():
    url = f'https://github.com/matatunos/wttc/releases/download/v{v}/WTTC-{v}-instalar-{chip}.zip'
    try:
        data = urllib.request.urlopen(url, timeout=60).read()
    except urllib.error.HTTPError as e:
        if e.code == 404: sys.exit(0)          # la Release aún no tiene el paquete: se reintenta luego
        raise
    if len(data) > 8 * 1024 * 1024: raise SystemExit(f'{chip}: paquete demasiado grande')
    z = zipfile.ZipFile(io.BytesIO(data))
    out = os.path.join(tmp, chip); os.makedirs(out)
    for f in FILES:                            # solo los ficheros esperados, sin rutas raras
        open(os.path.join(out, f), 'wb').write(z.read(f'instalar-{chip}/{f}'))
    p = json.load(open(os.path.join(out, 'placa.json')))
    assert p['version'] == v and p['chipFamily'] == fam and p['bootloader'] in (0, 4096), p
    assert open(os.path.join(out, 'wttc.bin'), 'rb').read(1) == b'\xe9', f'{chip}: el programa no parece de ESP32'
    os.remove(os.path.join(out, 'placa.json'))
    builds.append({'chipFamily': fam, 'parts': [
        {'path': f'{v}/{chip}/bootloader.bin', 'offset': p['bootloader']},
        {'path': f'{v}/{chip}/partitions.bin', 'offset': 0x8000},
        {'path': f'{v}/{chip}/boot_app0.bin', 'offset': 0xE000},
        {'path': f'{v}/{chip}/wttc.bin', 'offset': 0x10000}]})
for root, dirs, files in os.walk(tmp):
    os.chmod(root, 0o755)
    for f in files: os.chmod(os.path.join(root, f), 0o644)
final = os.path.join(dst, v); shutil.rmtree(final, ignore_errors=True); os.replace(tmp, final)
# new_install_prompt_erase=false: no se ofrece borrar toda la flash (la configuración y el PIN se conservan)
man = {'name': 'WTTC', 'version': v, 'new_install_prompt_erase': False, 'builds': builds}
t = os.path.join(dst, '.manifest.json'); open(t, 'w').write(json.dumps(man, indent=1) + '\n'); os.chmod(t, 0o644)
os.replace(t, os.path.join(dst, 'manifest.json'))
open(os.path.join(final, '.listo'), 'w').close()
# solo las dos últimas versiones
vers = sorted((d for d in os.listdir(dst) if d[0].isdigit()), key=lambda s: tuple(int(x) for x in s.split('.')))
for old in vers[:-2]: shutil.rmtree(os.path.join(dst, old), ignore_errors=True)
print(f'Instalador listo: versión {v}, placas {", ".join(b["chipFamily"] for b in builds)}')
PY
