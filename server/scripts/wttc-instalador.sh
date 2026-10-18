#!/bin/bash
# wttc-instalador.sh — (fuente: server/scripts/ del repo; lo instala deploy-web.sh) Prepara en https://wttc.favala.es/instalar.php la instalación del firmware desde el navegador.
# (generado con Claude, Anthropic; lo lanza cron cada 5 minutos)
# Copia de la Release de GitHub de la versión publicada (descargas/VERSION) los paquetes WTTC-<v>-instalar-<chip>.zip
# (WTTC) y WTTC-<v>-falsa-<chip>.zip (la Webasto falsa, desde la 0.3.11)
# que adjunta el workflow del firmware, los descomprime en descargas/instalar/<v>/<paquete>/ y escribe un manifiesto por
# versión (<v>/wttc.json y <v>/falsa.json, los que enlaza instalar.php) y
# descargas/instalar/manifest.json para ESP Web Tools. Si ya está hecho para esa versión, sale sin hacer nada; si la
# Release aún no tiene los paquetes, lo vuelve a intentar en la siguiente pasada. Guarda solo las dos últimas versiones.
# Nada se compila aquí: solo se descargan y copian ficheros (unos pocos MB).
set -euo pipefail
# Una sola pasada a la vez: dos a la vez (la del cron y una lanzada a mano) se pisaban la carpeta temporal y la 0.3.17
# se publicó sin el paquete de WTTC (wttc.json y instalar-esp32s3/ no estaban: «bootloader.bin failed: 404»)
exec 9>/run/wttc-instalador.lock
flock -n 9 || exit 0
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
# Qué se instala desde el navegador: WTTC (la placa de la furgo, solo ESP32-S3 desde la 0.2.0) y, desde la 0.3.11, la
# Webasto falsa (un segundo ESP32, el clásico o el S3, para probar WTTC en la mesa). Paquetes de la Release:
# WTTC-<v>-<paquete>.zip con la carpeta <paquete>/ (los hace .github/scripts/paquete_instalar.sh)
PRODS = {
    'wttc':  {'name': 'WTTC', 'pkgs': ['instalar-esp32s3'], 'needed': True},
    'falsa': {'name': 'Webasto falsa (WTTC)', 'pkgs': ['falsa-esp32', 'falsa-esp32s3'], 'needed': False},
}
FAMS = {'esp32': 'ESP32', 'esp32s3': 'ESP32-S3'}
tmp = os.path.join(dst, f'.{v}.tmp'); shutil.rmtree(tmp, ignore_errors=True); os.makedirs(tmp)

def fetch(pkg):
    url = f'https://github.com/matatunos/wttc/releases/download/v{v}/WTTC-{v}-{pkg}.zip'
    try:
        data = urllib.request.urlopen(url, timeout=60).read()
    except urllib.error.HTTPError as e:
        if e.code == 404: return None          # la Release aún no lo tiene (o esta versión no lo trae)
        raise
    if len(data) > 8 * 1024 * 1024: raise SystemExit(f'{pkg}: paquete demasiado grande')
    z = zipfile.ZipFile(io.BytesIO(data))
    out = os.path.join(tmp, pkg); os.makedirs(out)
    names = set(z.namelist())
    app = 'programa.bin' if f'{pkg}/programa.bin' in names else 'wttc.bin'    # hasta la 0.3.10 se llamaba wttc.bin
    for f in ('bootloader.bin', 'partitions.bin', 'boot_app0.bin', app, 'placa.json'):   # solo lo esperado, sin rutas raras
        open(os.path.join(out, 'programa.bin' if f == app else f), 'wb').write(z.read(f'{pkg}/{f}'))
    p = json.load(open(os.path.join(out, 'placa.json')))
    fam = p['chipFamily']
    assert p['version'] == v and fam in FAMS.values() and p['bootloader'] in (0, 4096), p
    assert open(os.path.join(out, 'programa.bin'), 'rb').read(1) == b'\xe9', f'{pkg}: el programa no parece de ESP32'
    os.remove(os.path.join(out, 'placa.json'))
    return {'chipFamily': fam, 'parts': [
        {'path': f'{pkg}/bootloader.bin', 'offset': p['bootloader']},
        {'path': f'{pkg}/partitions.bin', 'offset': 0x8000},
        {'path': f'{pkg}/boot_app0.bin', 'offset': 0xE000},
        {'path': f'{pkg}/programa.bin', 'offset': 0x10000}]}

man = {}
for key, prod in PRODS.items():
    builds = [b for b in (fetch(p) for p in prod['pkgs']) if b]
    if not builds:
        if prod['needed']: shutil.rmtree(tmp, ignore_errors=True); sys.exit(0)   # se reintenta en la siguiente pasada
        continue
    # new_install_prompt_erase=false: no se ofrece borrar toda la flash (la configuración se conserva)
    man[key] = {'name': prod['name'], 'version': v, 'new_install_prompt_erase': False, 'builds': builds}
    # Un manifiesto por versión (rutas relativas a él): la página enlaza el de la versión que anuncia, así lo que dice
    # y lo que se graba coinciden aunque salga otra versión mientras está abierta
    open(os.path.join(tmp, f'{key}.json'), 'w').write(json.dumps(man[key], indent=1) + '\n')
for root, dirs, files in os.walk(tmp):
    os.chmod(root, 0o755)
    for f in files: os.chmod(os.path.join(root, f), 0o644)
final = os.path.join(dst, v); shutil.rmtree(final, ignore_errors=True); os.replace(tmp, final)
# manifest.json de siempre (el de WTTC, con rutas desde descargas/instalar/), por si algo lo enlaza aún
w = dict(man['wttc'], builds=[dict(b, parts=[dict(x, path=f'{v}/' + x['path']) for x in b['parts']]) for b in man['wttc']['builds']])
t = os.path.join(dst, '.manifest.json'); open(t, 'w').write(json.dumps(w, indent=1) + '\n'); os.chmod(t, 0o644)
os.replace(t, os.path.join(dst, 'manifest.json'))
open(os.path.join(final, '.listo'), 'w').close()
# solo las dos últimas versiones
vers = sorted((d for d in os.listdir(dst) if d[0].isdigit()), key=lambda s: tuple(int(x) for x in s.split('.')))
for old in vers[:-2]: shutil.rmtree(os.path.join(dst, old), ignore_errors=True)
print(f'Instalador listo: versión {v}: ' + '; '.join(f'{m["name"]} ({", ".join(b["chipFamily"] for b in m["builds"])})' for m in man.values()))
PY
