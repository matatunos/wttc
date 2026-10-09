#!/usr/bin/env python3
"""descifrar_cuelgue.py — Traduce el «Informe del cuelgue» del registro de la placa a nombres de función.

Código generado íntegramente con Claude (Anthropic).

La placa, al arrancar tras colgarse, apunta en su registro algo como:
    Informe del cuelgue (firmware 0.2.20): tarea «loopTask», PC 0x42012abc, pila 0x42012abc 0x4200f1e0 …
Este script baja de la Release de esa versión el WTTC-<versión>-s3.elf (el mismo programa, con sus símbolos) y busca
en qué función cae cada dirección. Solo lee el fichero ELF con Python: no necesita las herramientas del ESP32.

Uso:
    python3 herramientas/descifrar_cuelgue.py "Informe del cuelgue (firmware 0.2.20): tarea «loopTask», PC 0x…"
    python3 herramientas/descifrar_cuelgue.py --elf WTTC-0.2.20-s3.elf 0x42012abc 0x4200f1e0
"""
import os, re, shutil, struct, subprocess, sys, urllib.request

RELEASE_URL = 'https://github.com/matatunos/wttc/releases/download/v{v}/WTTC-{v}-s3.elf'
CACHE_DIR = os.path.expanduser('~/.cache/wttc-elf')


def funciones(elf_path):
    """Lista ordenada de (dirección, tamaño, nombre) de las funciones de la tabla de símbolos (ELF de 32 bits)."""
    d = open(elf_path, 'rb').read()
    if d[:4] != b'\x7fELF' or d[4] != 1:
        sys.exit(f'{elf_path}: no es un ELF de 32 bits')
    e = '<' if d[5] == 1 else '>'
    shoff, = struct.unpack_from(e + 'I', d, 0x20)
    shentsize, shnum = struct.unpack_from(e + 'HH', d, 0x2E)
    secs = [struct.unpack_from(e + 'IIIIIIIIII', d, shoff + i * shentsize) for i in range(shnum)]
    out = []
    for s in secs:
        if s[1] != 2:                                  # SHT_SYMTAB
            continue
        strtab = secs[s[6]]                            # sh_link: tabla de nombres
        for off in range(s[4], s[4] + s[5], 16):
            name, value, size, info, _, _ = struct.unpack_from(e + 'IIIBBH', d, off)
            if info & 0xF != 2 or not value:          # STT_FUNC
                continue
            p = strtab[4] + name
            out.append((value, size, d[p:d.index(b'\0', p)].decode(errors='replace')))
    return sorted(out)


def buscar(funcs, addr):
    """La función que contiene addr (o la anterior más cercana), con el desplazamiento dentro de ella."""
    lo, hi, best = 0, len(funcs) - 1, None
    while lo <= hi:
        mid = (lo + hi) // 2
        if funcs[mid][0] <= addr:
            best, lo = funcs[mid], mid + 1
        else:
            hi = mid - 1
    if not best:
        return None
    v, size, name = best
    return name, addr - v, size == 0 or addr < v + size


def legible(nombres):
    """Nombres de C++ legibles con c++filt, si está instalado (viene con binutils); si no, tal cual."""
    if not shutil.which('c++filt'):
        return nombres
    r = subprocess.run(['c++filt'], input='\n'.join(nombres), capture_output=True, text=True)
    return r.stdout.splitlines() if r.returncode == 0 else nombres


def main():
    args = sys.argv[1:]
    elf = None
    if args[:1] == ['--elf']:
        elf, args = args[1], args[2:]
    texto = ' '.join(args)
    if not texto:
        sys.exit(__doc__)
    if not elf:
        m = re.search(r'firmware\s+([0-9.]+)', texto)
        if not m:
            sys.exit('No veo la versión («firmware 0.2.20»): pásala con --elf <fichero>')
        v = m.group(1)
        os.makedirs(CACHE_DIR, exist_ok=True)
        elf = os.path.join(CACHE_DIR, f'WTTC-{v}-s3.elf')
        if not os.path.exists(elf):
            print(f'Bajando el .elf de la {v}…', file=sys.stderr)
            urllib.request.urlretrieve(RELEASE_URL.format(v=v), elf)
    tarea = re.search(r'tarea\s+«([^»]+)»', texto)
    if tarea:
        print(f'Tarea: {tarea.group(1)}')
    funcs = funciones(elf)
    dirs = [int(x, 16) for x in re.findall(r'0x([0-9a-fA-F]{8})', texto)]
    res = [buscar(funcs, a) for a in dirs]
    nombres = legible([r[0] if r else '?' for r in res])
    for a, r, n in zip(dirs, res, nombres):
        print(f'0x{a:08x}  ' + (f'{n} +0x{r[1]:x}' + ('' if r[2] else '  (fuera de la función: dirección dudosa)') if r else 'no está en el programa'))


if __name__ == '__main__':
    main()
