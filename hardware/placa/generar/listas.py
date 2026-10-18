# -*- coding: utf-8 -*-
"""
listas.py — Listas de piezas (BOM) y de posiciones (CPL) para encargar la placa montada en JLCPCB, en dos juegos, y la
lista de materiales completa. No necesita KiCad: lee las posiciones que exporta pcb.py (salida/pos.csv) y circuito.py.
Generado con Claude (Anthropic). Uso: python3 hardware/placa/generar/listas.py

  - «smd»:  solo las piezas de superficie (montaje Economic, el barato). La borna, los zócalos y las tiras, a mano.
  - «todo»: también las de agujero pasante (montaje Standard). Llega entera: solo hay que pinchar la ESP32-S3.
"""
import csv
import os
import sys

sys.path.insert(0, os.path.dirname(__file__))
from circuito import PIEZAS  # noqa: E402

PROY = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
FAB = os.path.join(PROY, "fabricacion")
JUEGOS = {"smd": ("smd",), "todo": ("smd", "tht")}


def escribir():
    pos = {r["Ref"]: r for r in csv.DictReader(open(os.path.join(PROY, "salida", "pos.csv"), encoding="utf-8"))}
    for viejo in ("wttc-placa-bom-jlcpcb.csv", "wttc-placa-cpl.csv"):    # nombres de antes de los dos juegos
        if os.path.exists(os.path.join(FAB, viejo)):
            os.remove(os.path.join(FAB, viejo))
    for juego, montajes in JUEGOS.items():
        piezas = [p for p in PIEZAS if p["montaje"] in montajes and p["lcsc"]]
        with open(os.path.join(FAB, f"wttc-placa-cpl-{juego}.csv"), "w", newline="", encoding="utf-8") as f:
            w = csv.writer(f); w.writerow(["Designator", "Mid X", "Mid Y", "Layer", "Rotation"])
            for p in piezas:
                r = pos[p["ref"]]
                w.writerow([p["ref"], r["PosX"] + "mm", r["PosY"] + "mm", "Top", r["Rot"]])
        jlc = {}                                    # una línea por pieza (código LCSC)
        for p in piezas:
            nombre = p["mpn"] if p["montaje"] == "tht" else p["val"]   # tiras y zócalos: su referencia, no su uso
            jlc.setdefault(p["lcsc"], [nombre, [], p["fp"].split(":")[1]])[1].append(p["ref"])
        with open(os.path.join(FAB, f"wttc-placa-bom-jlcpcb-{juego}.csv"), "w", newline="", encoding="utf-8") as f:
            w = csv.writer(f); w.writerow(["Comment", "Designator", "Footprint", "LCSC Part #"])
            for lcsc, (val, refs, fpn) in jlc.items():
                w.writerow([val, ",".join(refs), fpn, lcsc])
        print(f"juego «{juego}»: {len(piezas)} piezas, {len(jlc)} distintas")
    grupos = {}
    for p in PIEZAS:
        if p["montaje"] == "pcb":
            continue
        k = (p["val"], p["fp"].split(":")[1], p["lcsc"], p["mpn"], p["montaje"], p["desc"])
        grupos.setdefault(k, []).append(p["ref"])
    quien = {"smd": "JLCPCB (los dos juegos)", "tht": "JLCPCB (juego «todo») o a mano (juego «smd»)"}
    with open(os.path.join(FAB, "lista-de-materiales.csv"), "w", newline="", encoding="utf-8") as f:
        w = csv.writer(f); w.writerow(["Referencias", "Cantidad", "Valor", "Pieza", "LCSC", "Montaje", "Descripción", "Huella"])
        for (val, fpn, lcsc, mpn, mon, desc), refs in grupos.items():
            w.writerow([" ".join(refs), len(refs), val, mpn, lcsc, quien[mon], desc, fpn])
    paquetes()


LEEME = {
    "todo": "Juego «todo»: llega ENTERA, solo hay que pinchar la ESP32-S3-DevKitC-1 (se compra aparte).\n"
            "Montaje en JLCPCB: «PCB Assembly», Standard, cara de arriba (Top side).\n",
    "smd": "Juego «smd»: llega con las piezas pequeñas; la borna, los dos zócalos de 22 y las tiras se sueldan a mano.\n"
           "Montaje en JLCPCB: «PCB Assembly», Economic, cara de arriba (Top side).\n",
}


def paquetes():
    """Un .zip por juego con lo que hay que subir a JLCPCB: Gerber, BOM, CPL y un LEEME."""
    import zipfile
    for juego, txt in LEEME.items():
        z = os.path.join(FAB, f"wttc-placa-encargo-{juego}.zip")
        with zipfile.ZipFile(z, "w", zipfile.ZIP_DEFLATED) as f:
            for nom in ("wttc-placa-gerber.zip", f"wttc-placa-bom-jlcpcb-{juego}.csv", f"wttc-placa-cpl-{juego}.csv"):
                f.write(os.path.join(FAB, nom), nom)
            f.writestr("LEEME.txt", "WTTC · placa portadora v0.1 (DISEÑO SIN PROBAR) · https://wttc.favala.es/placa.php\n\n" + txt +
                       "\n1. En jlcpcb.com → Order now: sube wttc-placa-gerber.zip (tal cual, sin descomprimir).\n"
                       f"2. Activa PCB Assembly y sube wttc-placa-bom-jlcpcb-{juego}.csv (BOM) y wttc-placa-cpl-{juego}.csv (CPL).\n"
                       "3. Revisa el giro de las piezas en su vista previa (pin 1 y raya del cátodo como en la serigrafía).\n"
                       "La ESP32-S3 y los módulos opcionales (pantalla, termómetro, sensor de CO) no van en el encargo.\n")
        print(f"paquete: {os.path.basename(z)}")


if __name__ == "__main__":
    escribir()
