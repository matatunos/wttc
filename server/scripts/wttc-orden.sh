#!/bin/bash
# wttc-orden.sh — Deja una orden remota firmada para una placa y espera a ver qué ha pasado.
# Código generado íntegramente con Claude (Anthropic). Fuente: server/scripts/ del repo; lo instala deploy-web.sh.
#
# Uso: wttc-orden.sh <código de instalación> update|check|logsend|reboot|diag-on|diag-off [minutos de espera]
#
# La placa (con «Permitir órdenes remotas» activado) pregunta cada 2 min a https://wttc.favala.es/api/orden.php. La
# orden se firma con la clave de las actualizaciones (ECDSA P-256, la misma que comprueba la placa con OTA_PUBKEY):
#   WTTCCMD1|<código>|<número>|<caduca (s UNIX)>|<orden>
# El número es la hora UNIX (crece siempre: la placa no repite una orden ya hecha) y caduca a los 15 min. Nunca hay
# órdenes para la calefacción: la placa ni las entendería (lista cerrada en firmware/WTTC/logica.h).
# Después espera (por defecto 10 min) a que la placa recoja la orden y mande su registro, y lo resume.
set -euo pipefail
export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

# ---------- configuración ----------
KEY=/root/.config/wttc/fw-sign.pem     # clave privada de firma: solo root; nunca se imprime
CONTAINER=tools                        # contenedor de la web (la base de datos es suya: se escribe como www-data)
DB_PHP=/var/www/tools/wttc/api/db.php
LIFE=900                               # s que vale la orden

code="${1:-}"; cmd="${2:-}"; wait_min="${3:-10}"
[[ "$code" =~ ^[2-9A-HJ-NP-Z]{4}(-[2-9A-HJ-NP-Z]{4}){3}$ ]] || { echo "Código de instalación no válido: $code" >&2; exit 2; }
case "$cmd" in update|check|logsend|reboot|diag-on|diag-off) ;; *) echo "Orden no válida: $cmd (update|check|logsend|reboot|diag-on|diag-off)" >&2; exit 2;; esac
[ -r "$KEY" ] || { echo "No encuentro la clave de firma" >&2; exit 1; }

now=$(date +%s); seq=$now; exp=$((now + LIFE))
msg="WTTCCMD1|$code|$seq|$exp|$cmd"
sig=$(printf '%s' "$msg" | openssl dgst -sha256 -sign "$KEY" | od -An -tx1 -v | tr -d ' \n')
# Comprobación local con la clave pública antes de dejarla (si no cuadra, no se deja nada)
printf '%s' "$msg" | openssl dgst -sha256 -verify <(openssl pkey -in "$KEY" -pubout) \
  -signature <(printf '%s' "$sig" | python3 -c 'import sys,binascii; sys.stdout.buffer.write(binascii.unhexlify(sys.stdin.read().strip()))') >/dev/null || { echo "La firma no se comprueba: no se deja la orden" >&2; exit 1; }

docker exec -u www-data -i "$CONTAINER" php -r '
  require "'"$DB_PHP"'";
  $in = json_decode(stream_get_contents(STDIN), true);
  $db = wttc_db();
  $db->prepare("INSERT INTO board_cmds (iid, msg, sig, created, expires) VALUES (?, ?, ?, ?, ?)")
     ->execute([$in["c"], $in["m"], $in["s"], gmdate("Y-m-d H:i:s"), $in["e"]]);
  echo "Orden n.º ", $db->lastInsertId(), " guardada\n";
' <<< "{\"c\":\"$code\",\"m\":\"$msg\",\"s\":\"$sig\",\"e\":$exp}"
echo "Esperando a que la placa la recoja (pregunta cada 2 min; hasta $wait_min min)…"

# Espera: entrega de la orden y, después, un registro enviado por la placa más nuevo que la orden
q() { docker exec -u www-data -i "$CONTAINER" php -r 'require "'"$DB_PHP"'"; $db = wttc_db(); '"$1"; }
for i in $(seq 1 $((wait_min * 4))); do
  d=$(q '$s=$db->prepare("SELECT delivered FROM board_cmds WHERE iid=? AND msg=?"); $s->execute(["'"$code"'","'"$msg"'"]); echo $s->fetchColumn();')
  [ -n "$d" ] && { echo "Recogida por la placa: $d UTC"; break; }
  sleep 15
done
[ -n "${d:-}" ] || { echo "La placa no la ha recogido en $wait_min min (¿apagada, sin internet o sin «Permitir órdenes remotas»?). Caduca sola a los 15 min."; exit 3; }
since=$(date -u -d "@$now" '+%Y-%m-%d %H:%M:%S')
for i in $(seq 1 $((wait_min * 4))); do
  r=$(q '$s=$db->prepare("SELECT at, fw, body FROM board_logs WHERE iid=? AND at>=? ORDER BY id DESC LIMIT 1"); $s->execute(["'"$code"'","'"$since"'"]);
        if ($x=$s->fetch()) { $b=json_decode($x["body"],true); echo $x["at"]," UTC · firmware ",$x["fw"]," · arranque: ",$b["rr"]??"?","\n", implode("\n", array_slice(explode("\n",$b["log"]??""),-12)); }')
  [ -n "$r" ] && { echo "Registro de la placa:"; echo "$r"; exit 0; }
  sleep 15
done
echo "Recogida, pero aún no ha llegado su registro (si se está actualizando, llega unos 3 min después de reiniciar)."
