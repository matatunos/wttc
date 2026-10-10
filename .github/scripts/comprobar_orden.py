#!/usr/bin/env python3
"""comprobar_orden.py — Comprobaciones rápidas de WTTC.ino antes de compilar (sin compilar nada).

Código generado íntegramente con Claude (Anthropic).

1. Ninguna función definida antes de «enum Txt {»: Arduino pone las declaraciones automáticas de TODAS las funciones
   justo antes de la primera que encuentra, y si esa queda antes de los textos, las que usan Txt no compilan (pasó
   en la 0.3.0 con stackMark y nvsOpen; los errores salen en cadena y despistan).
2. La tabla de textos TXT en el mismo orden que el enum Txt y con tres idiomas por texto (si no, un texto sale en el
   sitio de otro, sin ningún error de compilación).
3. Ninguna variable global usada antes de su declaración (pasó en la 0.3.3 con tlsInPsram).
"""
import re, sys

ino = open(sys.argv[1] if len(sys.argv) > 1 else 'firmware/WTTC/WTTC.ino', encoding='utf-8').read()
fallos = []
enum_at = ino.index('enum Txt {')
fun = re.compile(r'^[A-Za-z_][\w<>:*& ]*[ *&]+[A-Za-z_]\w*\([^;{]*\)\s*(const\s*)?\{', re.M)
for m in fun.finditer(ino, 0, enum_at):
    linea = ino.count('\n', 0, m.start()) + 1
    fallos.append(f'línea {linea}: función antes de «enum Txt {{»: {m.group(0).strip()[:80]}')

i0 = ino.index('const char* const TXT[T_COUNT][L_N] = {')
body = ino[i0:ino.index('\n};', i0)]
parts = re.split(r'/\* (T_[A-Z0-9_]+) \*/', body)[1:]
enum = re.findall(r'\bT_[A-Z0-9_]+', ino[enum_at:ino.index('T_COUNT', enum_at)])
tabla = parts[0::2]
for k, (a, b) in enumerate(zip(enum, tabla)):
    if a != b:
        fallos.append(f'texto {k}: el enum dice {a} y la tabla {b} (orden distinto)')
        break
if len(enum) != len(tabla):
    fallos.append(f'{len(enum)} textos en el enum y {len(tabla)} en la tabla')
for name, blk in zip(parts[0::2], parts[1::2]):
    n = len(re.findall(r'"((?:[^"\\]|\\.)*)"', blk))
    if n != 3:
        fallos.append(f'{name}: {n} cadenas (deben ser 3: es, en, de)')

# 3. Variables globales usadas antes de declararse (el compilador lo rechaza; Arduino solo adelanta las funciones).
#    Se mira el código sin comentarios ni cadenas, para no confundir una mención en un comentario con un uso
codigo = re.sub(r'"(?:[^"\\\n]|\\.)*"', '""', ino)                    # cadenas
codigo = re.sub(r'//[^\n]*', '', codigo)                                    # comentarios de línea
codigo = re.sub(r'/\*.*?\*/', lambda m: '\n' * m.group(0).count('\n'), codigo, flags=re.S)   # de bloque (mismas líneas)
decl = re.compile(r'^(?:static\s+|volatile\s+|const\s+|RTC_NOINIT_ATTR\s+)*[A-Za-z_][\w:<>]*\s*\*?\s+(\w+)\s*(?:\[[^\]]*\])?\s*(?:=[^;]*)?[;,]', re.M)
for m in decl.finditer(codigo):
    nombre = m.group(1)
    if nombre in ('return', 'else'): continue
    primero = re.search(r'\b' + re.escape(nombre) + r'\b', codigo)
    if primero and primero.start() < m.start():
        fallos.append(f'línea {codigo.count(chr(10), 0, primero.start()) + 1}: «{nombre}» se usa antes de declararse '
                      f'(línea {codigo.count(chr(10), 0, m.start()) + 1})')

for f in fallos:
    print(f'::error file=firmware/WTTC/WTTC.ino::{f}')
print(f'WTTC.ino: {len(enum)} textos, {"OK" if not fallos else str(len(fallos)) + " fallos"}')
sys.exit(1 if fallos else 0)
