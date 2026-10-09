#!/bin/bash
# wttc-publicar.sh — (fuente: server/scripts/ del repo; lo instala deploy-web.sh) Publica en https://wttc.favala.es lo que hay en el repo local /root/wttc:
# (generado con Claude, Anthropic; lo lanza el hook post-commit del repo local)
#   - descargas/WTTC-firmware.zip  (carpeta firmware/WTTC: WTTC.ino + web.h)
#   - movil.php                    (web del firmware para el simulador, desde web.h)
#   - descargas/VERSION y CHANGELOG.md (sección «Versiones»)
#   - descargas/fwtextos.json      (textos del firmware en es/en/de, para el ESP32 simulado)
#   - descargas/ota.json           (última versión y dirección de su .ota, para la actualización sin cable; solo cuando su
#                                    .ota ya está en el espejo: si no, queda en ota-pendiente.json y lo publica wttc-instalador.sh)
# El APK no se copia: la web enlaza al de su versión en GitHub (releases/download/v<VERSION>/WTTC.apk;
# no releases/latest, que da 404 mientras todas las Releases sean de prueba).
set -euo pipefail
REPO=/root/wttc
WEB=/root/tools-vps/appdata/www/tools/wttc
mkdir -p "$WEB/descargas"
python3 - "$REPO" "$WEB" <<'PY'
import sys, zipfile, os, time
repo, web = sys.argv[1], sys.argv[2]
src = os.path.join(repo, 'firmware', 'WTTC')
tmp = os.path.join(web, 'descargas', '.WTTC-firmware.zip')
with zipfile.ZipFile(tmp, 'w', zipfile.ZIP_DEFLATED) as z:
    for f in sorted(os.listdir(src)):
        p = os.path.join(src, f)
        if os.path.isfile(p):
            z.write(p, 'WTTC/' + f)
    z.write(os.path.join(repo, 'LICENSE'), 'LICENSE')   # GPL v3: el código se distribuye con su licencia
os.replace(tmp, os.path.join(web, 'descargas', 'WTTC-firmware.zip'))

h = open(os.path.join(src, 'web.h'), encoding='utf-8').read()
i0 = h.index('PROGMEM = R"HTML(') + len('PROGMEM = R"HTML(')
html = h[i0:h.index(')HTML";', i0)]
a = 'async function api(p,b){const r=await fetch('
assert html.count(a) == 1, 'api() no encontrada en web.h'
html = html.replace(a, 'async function api(p,b){const r=await parent.WB.fetch(')
b = '</main>\n<script>\n'
assert html.count(b) == 1, '<script> no encontrado en web.h'
html = html.replace(b, '</main>\n<script>if(window.parent===window)location.replace("./");</script><script>\n')
head = ('<?php\n'
        '// wttc/movil.php — GENERADO por /usr/local/bin/wttc-publicar.sh desde firmware/WTTC/web.h: no editar a mano.\n'
        '// Cambios respecto al firmware: api() llama a parent.WB.fetch() (ESP32 simulado en index.php)\n'
        '// y, abierta suelta (fuera del iframe), redirige al simulador.\n'
        '?>')
tmp = os.path.join(web, '.movil.php')
open(tmp, 'w', encoding='utf-8').write(head + html + '\n')
os.replace(tmp, os.path.join(web, 'movil.php'))

# Textos del firmware (tabla TXT de WTTC.ino) para el ESP32 simulado: {"T_LOG_ON": ["es", "en", "de"], …}
import re, json
ino = open(os.path.join(src, 'WTTC.ino'), encoding='utf-8').read()
i0 = ino.find('const char* const TXT[T_COUNT][L_N] = {')
txt = {}
if i0 >= 0:
    body = ino[i0:ino.index('\n};', i0)]
    parts = re.split(r'/\* (T_[A-Z_]+) \*/', body)[1:]
    for name, blk in zip(parts[0::2], parts[1::2]):
        strs = re.findall(r'"((?:[^"\\]|\\.)*)"', blk)
        assert len(strs) == 3, name
        txt[name] = [x.replace('\\"', '"') for x in strs]
tmp = os.path.join(web, 'descargas', '.fwtextos.json')
open(tmp, 'w', encoding='utf-8').write(json.dumps(txt, ensure_ascii=False))
os.replace(tmp, os.path.join(web, 'descargas', 'fwtextos.json'))
PY
# Versión y changelog para la sección «Versiones» de la web
install -m 644 "$REPO/VERSION" "$WEB/descargas/VERSION"
# Última versión para la actualización sin cable: la app y la placa lo consultan. El .ota lo adjunta a la Release el
# workflow del firmware (firmado) y lo copia al espejo wttc-instalador.sh, unos minutos después. Mientras no está, el
# anuncio se guarda en ota-pendiente.json y ota.json sigue con la versión anterior: así la placa no ve «hay versión
# nueva» para luego no poder descargarla.
# «notas»: la sección de esa versión del CHANGELOG en una línea (sin comillas dobles ni saltos: la placa lee este
# JSON con un análisis sencillo y lo manda tal cual por Telegram), recortada a 450 caracteres.
python3 - "$REPO" "$WEB" <<'PY'
import sys, os, re, json
repo, web = sys.argv[1], sys.argv[2]
v = open(os.path.join(repo, 'VERSION')).read().strip()
notes, on = [], False
for line in open(os.path.join(repo, 'CHANGELOG.md'), encoding='utf-8'):
    if re.match(r'^## ' + re.escape(v) + r' ', line): on = True; continue
    if on and line.startswith('## '): break
    if on and line.startswith('- '):
        t = re.sub(r'[*`]', '', line[2:].strip()).replace('"', "'")
        notes.append(t)
txt = ' · '.join(notes)
if len(txt) > 450: txt = txt[:447].rsplit(' ', 1)[0] + '…'
# La placa descarga el .ota del espejo de esta web (lo copia wttc-instalador.sh desde la Release en unos minutos;
# mientras no está, la placa dice que aún no está publicada). Hasta el 9/10/2026 se bajaba de GitHub, cuyas descargas
# redirigen a otro servidor y a la placa se le cortaban
m = {'version': v, 'ota_s3': f'https://wttc.favala.es/descargas/ota/WTTC-{v}-s3.ota', 'notas': txt}   # ota_s3: ESP32-S3
ready = os.path.isfile(os.path.join(web, 'descargas', 'ota', f'WTTC-{v}-s3.ota'))
name = 'ota.json' if ready else 'ota-pendiente.json'
tmp = os.path.join(web, 'descargas', '.' + name)
open(tmp, 'w', encoding='utf-8').write(json.dumps(m, ensure_ascii=False, separators=(',', ':')) + '\n')
os.chmod(tmp, 0o644); os.replace(tmp, os.path.join(web, 'descargas', name))
if ready:
    try: os.remove(os.path.join(web, 'descargas', 'ota-pendiente.json'))
    except FileNotFoundError: pass
PY
install -m 644 "$REPO/CHANGELOG.md" "$WEB/descargas/CHANGELOG.md"
chmod 644 "$WEB/movil.php" "$WEB/descargas/WTTC-firmware.zip"
echo "Publicado: $(cd "$REPO" && git log -1 --format='%h %s')"
