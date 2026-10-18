# -*- coding: utf-8 -*-
"""
pcb.py — Coloca las piezas de circuito.py en la placa, la ruta con Freerouting, comprueba las reglas de diseño (DRC) y
saca los ficheros para encargarla (JLCPCB). Generado con Claude (Anthropic). Se ejecuta en el contenedor (generar.sh).

Placa de 95 × 62 mm, dos caras, 1,6 mm. La ESP32-S3-DevKitC-1 va pinchada arriba con la antena hacia el borde derecho,
sobresaliendo (sin cobre debajo de la antena). Todo lo demás, en la cara de arriba. Planos de masa en las dos caras.
"""
import json
import os
import re
import subprocess
import sys

import pcbnew

sys.path.insert(0, os.path.dirname(__file__))
from circuito import PIEZAS  # noqa: E402

AQUI = os.path.dirname(os.path.abspath(__file__))
PROY = os.path.dirname(AQUI)
SAL = os.path.join(PROY, "salida")
FAB = os.path.join(PROY, "fabricacion")
PCB = os.path.join(PROY, "wttc-placa.kicad_pcb")
FPLIB = "/usr/share/kicad/footprints"
O = (50.0, 50.0)            # origen de la placa en la hoja
ANCHO, ALTO = 95.0, 62.0

# Posición (mm desde la esquina de arriba a la izquierda) y giro de cada pieza
SITIO = {
    # Entrada y protecciones (arriba a la izquierda)
    "J1": (7.0, 31.0, 270), "F1": (14.0, 6.0, 0), "D1": (21.5, 6.0, 0), "D2": (14.5, 12.0, 180),
    "C1": (22.0, 12.0, 0), "C2": (27.5, 12.0, 0),
    # W-Bus (centro izquierda)
    "U1": (21.0, 26.0, 0), "R4": (14.5, 21.0, 90), "R5": (27.0, 21.0, 90), "C7": (14.5, 26.0, 90),
    "C8": (28.0, 30.0, 90), "D4": (17.8, 31.5, 90), "R6": (20.0, 36.2, 0), "JP1": (25.0, 36.2, 0),
    # 5 V (abajo a la izquierda)
    "U2": (17.0, 47.0, 0), "C3": (10.5, 47.0, 90), "R1": (10.5, 53.0, 90), "C4": (17.0, 42.5, 0),
    "L1": (24.5, 47.0, 0), "D3": (17.0, 53.0, 0), "R2": (29.5, 44.0, 90), "R3": (33.5, 44.0, 90),
    "C5": (30.5, 48.5, 0), "C6": (30.5, 51.5, 0),
    # ESP32-S3-DevKitC-1: pin 1 de las dos filas en el lado de la antena (derecha); filas a 25,4 mm
    "J2": (89.0, 10.0, 270), "J3": (89.0, 35.4, 270),
    # Opcionales (abajo a la derecha)
    # tiras de pines con el pin 1 a la izquierda (giro 90: los pines van hacia +x), como se lee su rótulo
    # Qwiic en el borde de abajo, con la boca hacia fuera (el cable no pasa por encima de las tiras)
    "J4": (34.5, 58.6, 0), "J5": (42.5, 57.5, 90), "J6": (55.5, 57.5, 90), "J7": (68.5, 57.5, 90),
    "J8": (81.5, 57.5, 90), "SW1": (88.0, 46.5, 0),
    # Taladros
    "H1": (4.0, 4.0, 0), "H2": (91.0, 4.0, 0), "H3": (4.0, 58.0, 0), "H4": (91.0, 58.0, 0),
}

# Textos de la serigrafía
TEXTOS = [
    (57.0, 3.0, 0, 1.3, "WTTC · placa portadora v0.1 · DISEÑO SIN PROBAR"),
    (57.0, 5.1, 0, 0.9, "github.com/matatunos/wttc · wttc.favala.es"),
    (60.0, 22.7, 0, 1.4, "ESP32-S3-DevKitC-1  ·  USB ←   → antena"),
    (7.0, 26.5, 0, 0.9, "VEHÍCULO"), (14.0, 31.0, 0, 0.8, "12V"), (14.0, 36.08, 0, 0.8, "GND"), (14.3, 41.16, 0, 0.8, "WBUS"),
    (46.3, 54.0, 0, 0.8, "GND 3V3 SCL SDA"), (59.3, 54.0, 0, 0.8, "3V3 GND SCL SDA"),
    (72.3, 54.0, 0, 0.8, "5V GND TXD RXD"), (82.8, 54.0, 0, 0.8, "BOT GND"),
    (46.3, 60.6, 0, 0.7, "PANTALLA"), (59.3, 60.6, 0, 0.7, "TERMÓMETRO"), (72.3, 60.6, 0, 0.7, "SENSOR CO"),
    (82.8, 60.6, 0, 0.7, "BOTÓN"), (34.5, 54.6, 0, 0.7, "I2C Qwiic"), (88.0, 51.0, 0, 0.8, "CALENTAR"),
    (23.0, 38.7, 0, 0.6, "MAESTRO: cortar = sin 1k"),
]


def mm(x, y):
    return pcbnew.VECTOR2I(pcbnew.FromMM(O[0] + x), pcbnew.FromMM(O[1] + y))


def redes_de_netlist():
    """{(ref, pin): red} del netlist exportado por kicad-cli (nombres tal cual: «/SDA»…)."""
    t = open(os.path.join(SAL, "wttc-placa.net"), encoding="utf-8").read()
    r = {}
    for trozo in t.split("(net (code ")[1:]:
        nombre = re.search(r'\(name "([^"]*)"\)', trozo).group(1)
        for n in re.finditer(r'\(node \(ref "([^"]+)"\) \(pin "([^"]+)"\)', trozo):
            r[(n.group(1), n.group(2))] = nombre
    return r


def linea(b, capa, a, z, ancho=0.1):
    s = pcbnew.PCB_SHAPE(b)
    s.SetShape(pcbnew.SHAPE_T_SEGMENT)
    s.SetStart(mm(*a)); s.SetEnd(mm(*z))
    s.SetLayer(capa); s.SetWidth(pcbnew.FromMM(ancho))
    b.Add(s)


def construir():
    b = pcbnew.NewBoard(PCB)
    ds = b.GetDesignSettings()
    ds.SetCopperLayerCount(2)
    nets = redes_de_netlist()
    netinfo = {}
    for nombre in sorted(set(nets.values())):
        ni = pcbnew.NETINFO_ITEM(b, nombre)
        b.Add(ni)
        netinfo[nombre] = ni

    for p in PIEZAS:
        lib, nom = p["fp"].split(":")
        fp = pcbnew.FootprintLoad(os.path.join(FPLIB, lib + ".pretty"), nom)
        if fp is None:
            raise SystemExit(f"no está la huella {p['fp']}")
        fp.SetFPID(pcbnew.LIB_ID(lib, nom))
        fp.SetReference(p["ref"]); fp.SetValue(p["val"])
        x, y, rot = SITIO[p["ref"]]
        fp.SetPosition(mm(x, y)); fp.SetOrientationDegrees(rot)
        fp.Value().SetVisible(False)
        if p["ref"].startswith("H") or p["ref"] in ("J1", "J4"):
            fp.Reference().SetVisible(False)
        fp.Reference().SetTextSize(pcbnew.VECTOR2I(pcbnew.FromMM(0.8), pcbnew.FromMM(0.8)))
        fp.Reference().SetTextThickness(pcbnew.FromMM(0.12))
        for pad in fp.Pads():
            n = nets.get((p["ref"], pad.GetNumber()))
            if p["ref"].startswith("H"):
                n = "/GND"                         # taladros metalizados, a masa
            if n:
                pad.SetNet(netinfo[n])
                # pads de masa: unión maciza al plano (con radios, algunos se quedaban con uno solo entre pistas;
                # los de agujero pasante piden algo más de soldador al montarlos a mano)
                if n == "/GND":
                    pad.SetLocalZoneConnection(pcbnew.ZONE_CONNECTION_FULL)
        b.Add(fp)

    # Contorno con esquinas redondeadas (radio 2 mm)
    r = 2.0
    for a, z in [((r, 0), (ANCHO - r, 0)), ((ANCHO, r), (ANCHO, ALTO - r)), ((ANCHO - r, ALTO), (r, ALTO)), ((0, ALTO - r), (0, r))]:
        linea(b, pcbnew.Edge_Cuts, a, z, 0.05)
    for cx, cy, a0 in [(r, r, 180), (ANCHO - r, r, 270), (ANCHO - r, ALTO - r, 0), (r, ALTO - r, 90)]:
        s = pcbnew.PCB_SHAPE(b); s.SetShape(pcbnew.SHAPE_T_ARC); s.SetLayer(pcbnew.Edge_Cuts); s.SetWidth(pcbnew.FromMM(0.05))
        s.SetCenter(mm(cx, cy))
        import math
        s.SetStart(mm(cx + r * math.cos(math.radians(a0)), cy + r * math.sin(math.radians(a0))))
        s.SetArcAngleAndEnd(pcbnew.EDA_ANGLE(90, pcbnew.DEGREES_T))
        b.Add(s)

    # Silueta de la DevKit en la serigrafía (65 × 28 mm; la antena sobresale por la derecha)
    x0, x1, y0, y1 = 89.0 - 53.34 - 4.3, 94.0, 22.7 - 14.8, 22.7 + 14.8   # un poco más ancha: no pisa los zócalos
    for a, z in [((x0, y0), (x1, y0)), ((x0, y1), (x1, y1)), ((x0, y0), (x0, y1))]:
        linea(b, pcbnew.F_SilkS, a, z, 0.15)
    for x, y, rot, h, t in TEXTOS:
        tx = pcbnew.PCB_TEXT(b); tx.SetText(t); tx.SetPosition(mm(x, y)); tx.SetLayer(pcbnew.F_SilkS)
        tx.SetTextSize(pcbnew.VECTOR2I(pcbnew.FromMM(h), pcbnew.FromMM(h))); tx.SetTextThickness(pcbnew.FromMM(max(0.12, h * 0.15)))
        tx.SetTextAngleDegrees(rot)
        b.Add(tx)

    pcbnew.SaveBoard(PCB, b)
    return b


def planos(b, netinfo_gnd):
    """Planos de masa en las dos caras (después de rutar)."""
    for capa in (pcbnew.F_Cu, pcbnew.B_Cu):
        z = pcbnew.ZONE(b)
        ls = pcbnew.LSET(); ls.AddLayer(capa)
        z.SetLayerSet(ls); z.SetNet(netinfo_gnd)   # SetLayer() no cambia la capa de una zona en KiCad 9
        z.SetLocalClearance(pcbnew.FromMM(0.3))
        z.SetIslandRemovalMode(pcbnew.ISLAND_REMOVAL_MODE_ALWAYS)   # trozos sin nada que los una: fuera
        z.SetMinThickness(pcbnew.FromMM(0.25)); z.SetPadConnection(pcbnew.ZONE_CONNECTION_THERMAL)
        z.SetThermalReliefGap(pcbnew.FromMM(0.3)); z.SetThermalReliefSpokeWidth(pcbnew.FromMM(0.4))
        ol = z.Outline(); ol.NewOutline()
        for x, y in [(0.3, 0.3), (ANCHO - 0.3, 0.3), (ANCHO - 0.3, ALTO - 0.3), (0.3, ALTO - 0.3)]:
            ol.Append(mm(x, y))
        b.Add(z)


def bajadas(b, piezas=("U1", "U2", "D3")):
    """Una vía de masa pegada a cada pad SMD de masa, con su pista corta, antes de rutar: el pin de masa del regulador
    (el del medio de su SOT-23-6) se quedaba en una isla del plano, encerrado entre las pistas de CB y FB."""
    import math
    gnd = b.FindNet("/GND")
    r, hol = pcbnew.FromMM(0.35), pcbnew.FromMM(0.3)
    bb = b.GetBoardEdgesBoundingBox()
    dentro = pcbnew.BOX2I(bb.GetOrigin(), pcbnew.VECTOR2I(bb.GetWidth(), bb.GetHeight()))
    dentro.Inflate(-pcbnew.FromMM(1.0))           # Inflate() cambia el recuadro: una sola vez, sobre una copia
    obst = []
    for fp in b.GetFootprints():
        for pad in fp.Pads():
            capa = pcbnew.F_Cu if pad.IsOnLayer(pcbnew.F_Cu) else pcbnew.B_Cu
            obst.append((pad, pad.GetEffectiveShape(capa)))
    n = 0
    for fp in b.GetFootprints():
        for pad in fp.Pads():
            if (fp.GetReference() not in piezas or pad.GetNetCode() != gnd.GetNetCode()
                    or pad.GetAttribute() != pcbnew.PAD_ATTRIB_SMD):
                continue
            c0 = pad.GetPosition()
            fuera = math.degrees(math.atan2(c0.y - fp.GetPosition().y, c0.x - fp.GetPosition().x))
            sitio = None
            for dist in (1.2, 1.5, 1.8, 2.2, 2.6):
                # primero hacia fuera de la pieza (no estorba a las pistas de sus otros pines)
                for ang in sorted(range(0, 360, 20), key=lambda a: abs((a - fuera + 180) % 360 - 180)):
                    c = pcbnew.VECTOR2I(int(c0.x + pcbnew.FromMM(dist) * math.cos(math.radians(ang))),
                                        int(c0.y + pcbnew.FromMM(dist) * math.sin(math.radians(ang))))
                    if not dentro.Contains(c):
                        continue
                    via = pcbnew.SHAPE_CIRCLE(c, r)
                    pista = pcbnew.SHAPE_SEGMENT(c0, c, pcbnew.FromMM(0.5))
                    if any(o.Collide(via, hol) or (q is not pad and q.GetNetCode() != gnd.GetNetCode() and o.Collide(pista, hol))
                           for q, o in obst if q is not pad):
                        continue
                    sitio = c
                    break
                if sitio:
                    break
            if not sitio:
                print(f"  sin sitio para la vía de masa de {fp.GetReference()}.{pad.GetNumber()}")
                continue
            v = pcbnew.PCB_VIA(b); v.SetPosition(sitio); v.SetWidth(2 * r); v.SetDrill(pcbnew.FromMM(0.35)); v.SetNet(gnd)
            t = pcbnew.PCB_TRACK(b); t.SetStart(c0); t.SetEnd(sitio); t.SetWidth(pcbnew.FromMM(0.5))
            t.SetLayer(pcbnew.F_Cu); t.SetNet(gnd)
            b.Add(v); b.Add(t)
            obst.append((v, v.GetEffectiveShape(pcbnew.F_Cu)))
            n += 1
    print(f"bajadas: {n} vías junto a pads de masa")


def rutar(b):
    bajadas(b)
    # La masa se ruta como una red más (con el plano en el DSN, Freerouting dejaba pads de masa sin bajar al plano);
    # los planos se añaden después, por encima
    dsn, ses = os.path.join(SAL, "placa.dsn"), os.path.join(SAL, "placa.ses")
    if not pcbnew.ExportSpecctraDSN(b, dsn):
        raise SystemExit("no se pudo exportar el DSN")
    subprocess.run(["xvfb-run", "-a", "java", "-Xmx1g", "-jar", "/opt/freerouting.jar", "-de", dsn, "-do", ses,
                    "-mp", "40", "-dct", "0"], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.STDOUT,
                   timeout=900)
    if not pcbnew.ImportSpecctraSES(b, ses):
        raise SystemExit("no se pudo importar el SES")
    for t in list(b.GetTracks()):               # todas las vías iguales: 0,7 mm con taladro de 0,35 (anillo de 0,175)
        if t.Type() == pcbnew.PCB_VIA_T:
            t.SetWidth(pcbnew.FromMM(0.7)); t.SetDrill(pcbnew.FromMM(0.35))
        elif t.GetLength() < pcbnew.FromMM(0.1):
            b.Remove(t)                         # restos de Freerouting (trocitos de 0,05 mm sueltos)
    planos(b, b.FindNet("/GND"))
    costuras(b)
    pcbnew.SaveBoard(PCB, b)
    rellenar()


def costuras(b, paso=4.0, margen=1.6, holgura=0.35):
    """Vías de masa entre las dos caras donde no tocan nada (pistas, pads, otras vías, piezas ni el borde): unen los
    trozos del plano de arriba que las pistas dejan aislados."""
    gnd = b.FindNet("/GND")
    r = pcbnew.FromMM(0.35)                       # vía de 0,7 mm con taladro de 0,35
    obst = []
    for t in b.GetTracks():
        for capa in (pcbnew.F_Cu, pcbnew.B_Cu):
            if t.IsOnLayer(capa) and t.GetNetCode() != gnd.GetNetCode():
                obst.append(t.GetEffectiveShape(capa))
        if t.Type() == pcbnew.PCB_VIA_T:
            obst.append(t.GetEffectiveShape(pcbnew.F_Cu))
    for fp in b.GetFootprints():
        for pad in fp.Pads():
            obst.append(pad.GetEffectiveShape(pcbnew.F_Cu) if pad.IsOnLayer(pcbnew.F_Cu) else pad.GetEffectiveShape(pcbnew.B_Cu))
        cy = fp.GetCourtyard(pcbnew.F_CrtYd)
        if cy.OutlineCount():
            obst.append(cy)
    n = 0
    y = margen + 1.0
    while y < ALTO - margen:
        x = margen + 1.0
        while x < ANCHO - margen:
            c = pcbnew.SHAPE_CIRCLE(mm(x, y), r)
            if (x > 86.0 and 7.5 < y < 38.0) or (34.0 < x < 86.0 and y < 6.8):   # antena de la DevKit y título
                x += paso; continue
            libre = True
            for o in obst:
                if isinstance(o, pcbnew.SHAPE_POLY_SET):
                    if o.Collide(pcbnew.VECTOR2I(c.GetCenter()), r + pcbnew.FromMM(holgura)):
                        libre = False; break
                elif o.Collide(c, pcbnew.FromMM(holgura)):
                    libre = False; break
            if libre:
                v = pcbnew.PCB_VIA(b); v.SetPosition(mm(x, y)); v.SetWidth(2 * r); v.SetDrill(pcbnew.FromMM(0.35))
                v.SetNet(gnd); b.Add(v); n += 1
                obst.append(v.GetEffectiveShape(pcbnew.F_Cu))
            x += paso
        y += paso
    print(f"costuras: {n} vías de masa")


def rellenar():
    """Rellena los planos con las reglas del proyecto: hay que volver a abrir la placa (NewBoard no las carga)."""
    b = pcbnew.LoadBoard(PCB)
    pcbnew.ZONE_FILLER(b).Fill(b.Zones())
    pcbnew.SaveBoard(PCB, b)


def drc():
    out = os.path.join(SAL, "drc.json")
    if os.path.exists(out):
        os.remove(out)                     # que un fallo del DRC no deje leer el informe anterior
    subprocess.run(["kicad-cli", "pcb", "drc", "--severity-all", "--format", "json", "-o", out, PCB],
                   check=True, stdout=subprocess.DEVNULL)
    d = json.load(open(out))
    malos = [v for v in d.get("violations", []) if v.get("severity") == "error"]
    sin = d.get("unconnected_items", [])
    avisos = [v for v in d.get("violations", []) if v.get("severity") != "error"]
    tipos = {}
    for v in malos + avisos:
        tipos[(v["severity"], v["type"])] = tipos.get((v["severity"], v["type"]), 0) + 1
    print(f"DRC: {len(malos)} errores, {len(avisos)} avisos, {len(sin)} sin conectar")
    for (sev, t), n in sorted(tipos.items()):
        print(f"  {sev:8} {t}: {n}")
    for v in avisos[:20]:
        print("   ~", v["type"], "|", " / ".join(i.get("description", "") for i in v.get("items", []))[:150])
    for v in malos[:12]:
        print("   ·", v["description"], "|", " / ".join(i.get("description", "") for i in v.get("items", []))[:160])
    for v in sin[:12]:
        print("   · sin conectar:", " / ".join(i.get("description", "") for i in v.get("items", []))[:160])
    return len(malos), len(sin)


def fabricacion():
    os.makedirs(FAB, exist_ok=True)
    g = os.path.join(FAB, "gerber")
    os.makedirs(g, exist_ok=True)
    for f in os.listdir(g):
        os.remove(os.path.join(g, f))
    capas = "F.Cu,B.Cu,F.Paste,B.Paste,F.Silkscreen,B.Silkscreen,F.Mask,B.Mask,Edge.Cuts"
    subprocess.run(["kicad-cli", "pcb", "export", "gerbers", "--layers", capas, "--no-x2", "-o", g + "/", PCB], check=True,
                   stdout=subprocess.DEVNULL)
    subprocess.run(["kicad-cli", "pcb", "export", "drill", "--format", "excellon", "--excellon-separate-th",
                    "--generate-map", "--map-format", "gerberx2", "-o", g + "/", PCB], check=True, stdout=subprocess.DEVNULL)
    zipf = os.path.join(FAB, "wttc-placa-gerber.zip")
    if os.path.exists(zipf):
        os.remove(zipf)
    subprocess.run(["sh", "-c", f"cd '{g}' && zip -q -X '{zipf}' *"], check=True)
    # Posiciones (CPL) y lista de materiales en el formato de JLCPCB: solo lo que montan ellos (SMD con código LCSC)
    pos = os.path.join(SAL, "pos.csv")
    subprocess.run(["kicad-cli", "pcb", "export", "pos", "--side", "front", "--format", "csv", "--units", "mm",
                    "-o", pos, PCB], check=True, stdout=subprocess.DEVNULL)
    import listas                               # BOM y CPL en dos juegos (smd / todo) y lista de materiales
    listas.escribir()
    # Imágenes: placa vista desde arriba y desde abajo, y el esquema en SVG para la web
    for lado in ("top", "bottom"):
        subprocess.run(["kicad-cli", "pcb", "render", "--side", lado, "--width", "1600", "--height", "1100",
                        "--background", "transparent", "--quality", "high", "-o", os.path.join(FAB, f"placa-{lado}.png"), PCB],
                       check=False, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    subprocess.run(["kicad-cli", "pcb", "render", "--side", "top", "--width", "1600", "--height", "1100", "--rotate",
                    "-35,0,-20", "--background", "transparent", "--quality", "high", "-o", os.path.join(FAB, "placa-3d.png"), PCB],
                   check=False, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    subprocess.run(["kicad-cli", "sch", "export", "pdf", "-o", os.path.join(FAB, "wttc-placa-esquema.pdf"),
                    os.path.join(PROY, "wttc-placa.kicad_sch")], check=True, stdout=subprocess.DEVNULL)


if __name__ == "__main__":
    paso = sys.argv[1] if len(sys.argv) > 1 else "todo"
    b = construir()
    print(f"placa: {len(PIEZAS)} piezas colocadas")
    if paso == "colocar":
        rellenar(); drc(); sys.exit(0)
    # Freerouting no sale igual cada vez: hasta 4 intentos, hasta una placa sin errores ni nada sin conectar
    for intento in range(1, 5):
        rutar(b)
        e, s = drc()
        if not e and not s:
            break
        print(f"intento {intento}: {e} errores, {s} sin conectar" + ("; otra vez" if intento < 4 else ""))
        if intento < 4:
            b = construir()
    fabricacion()
    print("fabricación: " + ", ".join(sorted(os.listdir(FAB))))
    if e or s:
        sys.exit(1)
