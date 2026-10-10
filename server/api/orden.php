<?php
// wttc/api/orden.php — Entrega a una placa su orden remota pendiente (si tiene «Permitir órdenes remotas» activado,
// pregunta cada 2 min con su código de instalación). Código generado íntegramente con Claude (Anthropic).
// GET ?c=XXXX-XXXX-XXXX-XXXX → 200 con dos líneas (mensaje y firma en hexadecimal) o 204 si no hay nada.
// La orden la deja firmada server/scripts/wttc-orden.sh; la placa comprueba la firma, que es para ella, que no la ha
// hecho ya y que no ha caducado. Aquí no se firma nada: sin la firma, conocer el código no sirve para mandar órdenes.
require_once __DIR__ . '/db.php';
ini_set('display_errors', '0');
header('Cache-Control: no-store');

$c = (string)($_GET['c'] ?? '');
if (!preg_match(WTTC_IID_RE, $c)) { http_response_code(400); exit; }
try { $db = wttc_db(); } catch (Throwable $e) { error_log('wttc orden: ' . $e->getMessage()); http_response_code(503); exit; }
$db->beginTransaction();
$q = $db->prepare('SELECT id, msg, sig FROM board_cmds WHERE iid = ? AND delivered IS NULL AND expires > ? ORDER BY id LIMIT 1');
$q->execute([$c, time()]);
$r = $q->fetch();
if (!$r) { $db->commit(); http_response_code(204); exit; }
$db->prepare('UPDATE board_cmds SET delivered = ? WHERE id = ?')->execute([gmdate('Y-m-d H:i:s'), $r['id']]);
$db->commit();
header('Content-Type: text/plain; charset=utf-8');
echo $r['msg'], "\n", $r['sig'], "\n";
