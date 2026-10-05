#!/usr/bin/env python3
# firmar_ota.py — Monta el fichero de actualización sin cable (OTA) de WTTC y lo firma. Generado con Claude (Anthropic).
# Uso: firmar_ota.py <programa.bin> <versión> <clave_privada.pem> <salida.ota>
# Formato (lo lee otaFeed() en WTTC.ino): «WTTCOTA1» · versión (16 bytes, rellena con ceros) · longitud de la firma
# (2 bytes, big-endian) · firma ECDSA P-256 en DER sobre SHA-256(versión + programa) · programa.
import struct, subprocess, sys, tempfile, os
binf, ver, key, out = sys.argv[1:5]
v = ver.encode()
assert 0 < len(v) <= 16, 'versión demasiado larga'
v16 = v.ljust(16, b'\0')
img = open(binf, 'rb').read()
assert img[:1] == b'\xe9', 'no parece un programa de ESP32 (falta la marca 0xE9)'
with tempfile.NamedTemporaryFile(delete=False) as t:
    t.write(v16 + img)
try:
    sig = subprocess.run(['openssl', 'dgst', '-sha256', '-sign', key, t.name], check=True, capture_output=True).stdout
finally:
    os.unlink(t.name)
assert 8 <= len(sig) <= 80, f'firma de tamaño raro ({len(sig)})'
open(out, 'wb').write(b'WTTCOTA1' + v16 + struct.pack('>H', len(sig)) + sig + img)
print(f'{out}: versión {ver}, programa {len(img)} bytes, firma {len(sig)} bytes')
