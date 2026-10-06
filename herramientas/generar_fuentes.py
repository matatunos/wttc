#!/usr/bin/env python3
"""
generar_fuentes.py — Letras suavizadas (16 grises) para la pantalla SSD1327 de 128×128.
Código generado íntegramente con Claude (Anthropic).

Rasteriza DejaVu Sans (libre: licencia Bitstream Vera; los cambios de DejaVu son de dominio público) en tres tamaños y
escribe:
  firmware/WTTC/fuentes.h    tablas para el firmware (sin librerías: las dibuja oledG*() en WTTC.ino)
  <salida>.json              las mismas tablas para el simulador de la web (opcional, segundo argumento)

Formato de cada letra: código Unicode, ancho y alto del dibujo, desplazamiento x e y respecto a la línea base (y
negativo = hacia arriba), avance y posición en el mapa de bits. El mapa de bits va a 4 bits por píxel (0 = nada,
15 = lleno), fila a fila y sin relleno; cada letra empieza en un byte nuevo.
Uso: python3 herramientas/generar_fuentes.py [ruta/del/simulador.json]    (necesita Pillow y fonts-dejavu-core)
"""
import json, os, sys
from PIL import Image, ImageDraw, ImageFont

DIR = '/usr/share/fonts/truetype/dejavu/'
LATIN = 'áéíóúüñÁÉÍÓÚÜÑäöÄÖßàèòçº°·'
FONTS = [
    # nombre, fichero, tamaño en píxeles, caracteres
    ('F_BIG', 'DejaVuSans-Bold.ttf', 40, '0123456789,.-°%'),
    ('F_MED', 'DejaVuSans-Bold.ttf', 14, ''.join(chr(c) for c in range(32, 127)) + LATIN),
    ('F_SMALL', 'DejaVuSans.ttf', 11, ''.join(chr(c) for c in range(32, 127)) + LATIN),
]

def build(name, ttf, size, chars):
    f = ImageFont.truetype(DIR + ttf, size)
    asc, desc = f.getmetrics()
    glyphs, bits = [], bytearray()
    for ch in sorted(set(chars), key=ord):
        x0, y0, x1, y1 = f.getbbox(ch, anchor='ls')
        w, h = max(0, x1 - x0), max(0, y1 - y0)
        adv = round(f.getlength(ch))
        off = len(bits)
        if w and h:
            im = Image.new('L', (w, h), 0)
            ImageDraw.Draw(im).text((-x0, -y0), ch, font=f, fill=255, anchor='ls')
            px = [round(v * 15 / 255) for v in im.getdata()]
            if len(px) % 2: px.append(0)
            for i in range(0, len(px), 2): bits.append(px[i] << 4 | px[i + 1])
        glyphs.append(dict(cp=ord(ch), w=w, h=h, xo=x0, yo=y0, adv=adv, off=off))
    assert len(bits) < 65536, name
    return dict(name=name, asc=asc, desc=desc, glyphs=glyphs, bits=list(bits))

fonts = [build(*f) for f in FONTS]
root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
out = ['// fuentes.h — GENERADO por herramientas/generar_fuentes.py: no editar a mano.',
       '// Letras suavizadas (16 grises) para la pantalla SSD1327 de 128×128 (ver oledG*() en WTTC.ino).',
       '// Rasterizadas de DejaVu Sans. Copyright (c) 2003 by Bitstream, Inc. All Rights Reserved. Bitstream Vera is a',
       '// trademark of Bitstream, Inc. DejaVu changes are in public domain. Licencia: https://dejavu-fonts.github.io/License.html',
       '#pragma once', '#include <Arduino.h>', '',
       '// Una letra: código, ancho y alto del dibujo, desplazamiento respecto a la línea base, avance y posición en el mapa',
       'struct Glyph { uint16_t cp; uint8_t w, h; int8_t xo, yo; uint8_t adv; uint16_t off; };',
       '// Un tipo de letra: sus letras (ordenadas por código), cuántas, el mapa de bits (4 bits por píxel) y la altura',
       'struct Font { const Glyph* g; uint16_t n; const uint8_t* bits; uint8_t asc, desc; };', '']
for f in fonts:
    n = f['name']
    out.append(f'// {n}: {len(f["glyphs"])} letras, {len(f["bits"])} bytes')
    out.append(f'static const Glyph {n}_G[] = {{')
    out += ['  {%d, %d, %d, %d, %d, %d, %d},' % (g['cp'], g['w'], g['h'], g['xo'], g['yo'], g['adv'], g['off']) for g in f['glyphs']]
    out.append('};')
    out.append(f'static const uint8_t {n}_B[] = {{')
    b = f['bits']
    out += ['  ' + ','.join(str(v) for v in b[i:i + 32]) + ',' for i in range(0, len(b), 32)]
    out.append('};')
    out.append(f'static const Font {n} = {{{n}_G, {len(f["glyphs"])}, {n}_B, {f["asc"]}, {f["desc"]}}};')
    out.append('')
open(os.path.join(root, 'firmware', 'WTTC', 'fuentes.h'), 'w', encoding='utf-8').write('\n'.join(out))
if len(sys.argv) > 1:
    json.dump({f['name']: f for f in fonts}, open(sys.argv[1], 'w'), separators=(',', ':'))
print(', '.join(f'{f["name"]}: {len(f["glyphs"])} letras, {len(f["bits"])} B' for f in fonts))
