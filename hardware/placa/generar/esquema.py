# -*- coding: utf-8 -*-
"""
esquema.py — Escribe el esquema de KiCad 9 (../wttc-placa.kicad_sch) a partir de circuito.py. Generado con Claude.

Se ejecuta dentro del contenedor de KiCad (generar.sh), donde están las bibliotecas oficiales de símbolos. Cada pieza
se coloca en su bloque y cada pin conectado lleva un trocito de cable con la etiqueta de su red (las redes se unen por
nombre); los pines libres, su marca de «sin conectar». Así el esquema se lee por bloques y la comprobación eléctrica
(ERC) sale limpia sin dibujar cables largos.
"""
import os
import re
import sys
import uuid

sys.path.insert(0, os.path.dirname(__file__))
from circuito import PIEZAS, ALIMENTADAS  # noqa: E402

LIBS = "/usr/share/kicad/symbols"
AQUI = os.path.dirname(os.path.abspath(__file__))
PROY = os.path.dirname(AQUI)
NOMBRE = "wttc-placa"
RAIZ = str(uuid.uuid5(uuid.NAMESPACE_URL, "wttc-placa-esquema"))   # estable: el esquema no cambia de identidad


def uid(*k):
    return str(uuid.uuid5(uuid.NAMESPACE_URL, "wttc-placa/" + "/".join(map(str, k))))


# ---------- S-expresiones: lo justo para copiar símbolos de las bibliotecas ----------
def bloque(txt, i):
    """Devuelve el final (exclusivo) de la expresión que empieza en txt[i] == '(' (respeta las cadenas)."""
    prof, dentro, k = 0, False, i
    while k < len(txt):
        c = txt[k]
        if dentro:
            if c == "\\":
                k += 1
            elif c == '"':
                dentro = False
        elif c == '"':
            dentro = True
        elif c == "(":
            prof += 1
        elif c == ")":
            prof -= 1
            if prof == 0:
                return k + 1
        k += 1
    raise ValueError("paréntesis sin cerrar")


_cache = {}


def simbolo(lib_id):
    """Texto del símbolo (renombrado a «Biblioteca:Nombre») y sus pines [(número, x, y, ángulo, tipo)]."""
    if lib_id in _cache:
        return _cache[lib_id]
    lib, nom = lib_id.split(":")
    ruta = os.path.join(PROY, lib + ".kicad_sym") if lib == NOMBRE else os.path.join(LIBS, lib + ".kicad_sym")
    txt = open(ruta, encoding="utf-8").read()
    m = re.search(r'\(symbol "%s"' % re.escape(nom), txt)
    if not m:
        raise SystemExit(f"no está el símbolo {lib_id}")
    s = txt[m.start():bloque(txt, m.start())]
    if "(extends " in s[:300]:
        raise SystemExit(f"{lib_id} hereda de otro símbolo: no soportado")
    s = s.replace(f'(symbol "{nom}"', f'(symbol "{lib_id}"', 1)
    pines = []
    for p in re.finditer(r'\(pin (\w+) \w+\s*\(at ([-\d.]+) ([-\d.]+) ([-\d.]+)\)', s):
        fin = bloque(s, p.start())
        num = re.search(r'\(number "([^"]*)"', s[p.start():fin]).group(1)
        pines.append((num, float(p.group(2)), float(p.group(3)), int(float(p.group(4))), p.group(1)))
    _cache[lib_id] = (s, pines)
    return _cache[lib_id]


# ---------- Colocación por bloques (mm en la hoja A3; rejilla de 2,54) ----------
BLOQUES = {   # título, origen, columnas, separación x, separación y
    "entrada": ("Entrada del vehículo y protecciones", (25.4, 30.48), 6, 33.02, 33.02),
    "5v": ("5 V: conmutado LMR16006X (ejemplo de 5 V de TI)", (25.4, 96.52), 5, 40.64, 33.02),
    "wbus": ("W-Bus: transceptor TJA1021T/20 y resistencia de maestro", (25.4, 167.64), 6, 33.02, 35.56),
    "esp": ("ESP32-S3-DevKitC-1 (zócalos)", (259.08, 30.48), 2, 63.5, 0),
    "opc": ("Piezas opcionales: I2C, sensor de CO, botón", (25.4, 233.68), 6, 38.1, 0),
    "mec": ("Taladros M3", (259.08, 205.74), 4, 15.24, 0),
}


def g(v):   # a la rejilla de 1,27 mm
    return round(round(v / 1.27) * 1.27, 4)


def main():
    out = []
    usados = []
    pos = {}
    cuenta = {}
    for p in PIEZAS:
        b = p["bloque"]
        _, (x0, y0), cols, dx, dy = BLOQUES[b]
        n = cuenta.get(b, 0)
        cuenta[b] = n + 1
        x = x0 + (n % cols) * dx + 12.7
        y = y0 + (n // cols) * dy + 12.7
        if b == "esp":
            y = y0 + 30.48
        pos[p["ref"]] = (g(x), g(y))
        if p["sym"] not in usados:
            usados.append(p["sym"])
    usados.append("power:PWR_FLAG")

    out.append(f'(kicad_sch\n\t(version 20250114)\n\t(generator "wttc")\n\t(generator_version "9.0")\n\t(uuid "{RAIZ}")\n\t(paper "A3")')
    out.append('\t(title_block\n\t\t(title "WTTC · placa portadora")\n\t\t(date "2026-10-18")\n\t\t(rev "0.1")'
               '\n\t\t(company "github.com/matatunos/wttc")'
               '\n\t\t(comment 1 "DISEÑO SIN PROBAR: nadie la ha encargado ni montado todavía")'
               '\n\t\t(comment 2 "Generado por hardware/placa/generar (circuito.py → esquema.py); no editar a mano")\n\t)')
    out.append("\t(lib_symbols")
    for s in usados:
        out.append(simbolo(s)[0])
    out.append("\t)")

    for b, (tit, (x0, y0), *_r) in BLOQUES.items():
        out.append(f'\t(text "{tit}"\n\t\t(exclude_from_sim no)\n\t\t(at {g(x0)} {g(y0 - 2.54)} 0)\n\t\t(effects (font (size 2.54 2.54) (thickness 0.508) (bold yes)) (justify left bottom))\n\t\t(uuid "{uid("t", b)}")\n\t)')

    def etiqueta(net, x, y, ox, oy, k):
        # trocito de cable desde el pin hacia fuera y la etiqueta en su punta
        ex, ey = g(x + ox * 2.54), g(y + oy * 2.54)
        out.append(f'\t(wire\n\t\t(pts (xy {x} {y}) (xy {ex} {ey}))\n\t\t(stroke (width 0) (type default))\n\t\t(uuid "{uid("w", *k)}")\n\t)')
        ang = {(-1, 0): 180, (1, 0): 0, (0, -1): 90, (0, 1): 270}[(ox, oy)]
        just = "right" if ang in (180, 270) else "left"
        out.append(f'\t(label "{net}"\n\t\t(at {ex} {ey} {ang})\n\t\t(fields_autoplaced yes)\n\t\t(effects (font (size 1.27 1.27)) (justify {just} bottom))\n\t\t(uuid "{uid("l", *k)}")\n\t)')

    for p in PIEZAS:
        X, Y = pos[p["ref"]]
        _, pines = simbolo(p["sym"])
        props = [("Reference", p["ref"], 0, -1, False), ("Value", p["val"], 0, 1, False),
                 ("Footprint", p["fp"], 0, 0, True), ("Datasheet", "~", 0, 0, True),
                 ("LCSC", p["lcsc"], 0, 0, True), ("MPN", p["mpn"], 0, 0, True), ("Montaje", p["montaje"], 0, 0, True)]
        alto = max([abs(py) for _, _, py, _, _ in pines] + [5.08])
        o = [f'\t(symbol\n\t\t(lib_id "{p["sym"]}")\n\t\t(at {X} {Y} 0)\n\t\t(unit 1)\n\t\t(exclude_from_sim no)'
             f'\n\t\t(in_bom {"no" if p["montaje"] == "pcb" else "yes"})\n\t\t(on_board yes)\n\t\t(dnp no)\n\t\t(uuid "{uid("s", p["ref"])}")']
        for nom, val, _, dyy, oculta in props:
            yy = g(Y + dyy * (alto + 2.54)) if dyy else Y
            o.append(f'\t\t(property "{nom}" "{val}"\n\t\t\t(at {X} {yy} 0)\n\t\t\t(effects (font (size 1.27 1.27)){" (hide yes)" if oculta else ""})\n\t\t)')
        for num, *_ in pines:
            o.append(f'\t\t(pin "{num}" (uuid "{uid("p", p["ref"], num)}"))')
        o.append(f'\t\t(instances (project "{NOMBRE}" (path "/{RAIZ}" (reference "{p["ref"]}") (unit 1))))\n\t)')
        out.append("\n".join(o))
        for num, px, py, ang, tipo in pines:
            x, y = g(X + px), g(Y - py)
            ox, oy = {0: (-1, 0), 180: (1, 0), 90: (0, 1), 270: (0, -1)}[ang]
            net = p["pines"].get(num) if p["pines"] else None
            if num not in (p["pines"] or {}) and p["pines"]:
                raise SystemExit(f'{p["ref"]}: falta el pin {num} en circuito.py')
            if net:
                etiqueta(net, x, y, ox, oy, (p["ref"], num))
            elif tipo != "no_connect" and p["pines"] is not None and p["bloque"] != "mec":
                out.append(f'\t(no_connect (at {x} {y}) (uuid "{uid("nc", p["ref"], num)}"))')

    # PWR_FLAG: las redes de alimentación «vienen» de fuera (borna, regulador de la DevKit): marca para el ERC
    for i, net in enumerate(ALIMENTADAS):
        X, Y = g(259.08 + 12.7 + i * 20.32), g(259.08)
        out.append(f'\t(symbol\n\t\t(lib_id "power:PWR_FLAG")\n\t\t(at {X} {Y} 0)\n\t\t(unit 1)\n\t\t(exclude_from_sim no)\n\t\t(in_bom no)\n\t\t(on_board yes)\n\t\t(dnp no)\n\t\t(uuid "{uid("pf", net)}")'
                   f'\n\t\t(property "Reference" "#FLG0{i + 1}" (at {X} {g(Y - 5.08)} 0) (effects (font (size 1.27 1.27)) (hide yes)))'
                   f'\n\t\t(property "Value" "PWR_FLAG" (at {X} {g(Y - 3.81)} 0) (effects (font (size 1.27 1.27))))'
                   f'\n\t\t(property "Footprint" "" (at {X} {Y} 0) (effects (font (size 1.27 1.27)) (hide yes)))'
                   f'\n\t\t(property "Datasheet" "~" (at {X} {Y} 0) (effects (font (size 1.27 1.27)) (hide yes)))'
                   f'\n\t\t(pin "1" (uuid "{uid("pfp", net)}"))'
                   f'\n\t\t(instances (project "{NOMBRE}" (path "/{RAIZ}" (reference "#FLG0{i + 1}") (unit 1))))\n\t)')
        etiqueta(net, X, Y, 0, 1, ("pf", net))

    out.append(f'\t(sheet_instances\n\t\t(path "/" (page "1"))\n\t)\n\t(embedded_fonts no)\n)')
    open(os.path.join(PROY, NOMBRE + ".kicad_sch"), "w", encoding="utf-8").write("\n".join(out) + "\n")
    print(f"esquema: {len(PIEZAS)} piezas, {len(usados)} símbolos")


if __name__ == "__main__":
    main()
