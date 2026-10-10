<?php
// wttc/api/registro.php — Recibe el registro de una placa cuando su usuario lo envía (Diagnóstico → «Enviar el registro»).
// Código generado íntegramente con Claude (Anthropic).
// POST JSON: {"iid","fw","up","rr","heap":[libre,mínima],"nvs","stk":[stats,ota,telegram],"rssi","log":"…","wbus":"…"}
// Se guarda tal cual (validado y recortado) con el código de instalación; se ve en mi.php con ese código y en la vista
// privada. Como mucho 20 envíos por placa y día, y se guardan los 10 últimos de cada placa. No se lee ni se guarda la IP.
require_once __DIR__ . '/db.php';
ini_set('display_errors', '0');
header('Content-Type: application/json');
header('Cache-Control: no-store');

function out(int $code, array $j) { http_response_code($code); echo json_encode($j); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, ['ok' => false, 'error' => 'solo POST']);
$raw = file_get_contents('php://input', false, null, 0, 16385);
if ($raw === false || strlen($raw) > 16384) out(413, ['ok' => false, 'error' => 'demasiado grande']);
$in = json_decode($raw, true);
if (!is_array($in)) out(400, ['ok' => false, 'error' => 'JSON no válido']);
$iid = $in['iid'] ?? '';
if (!is_string($iid) || !preg_match(WTTC_IID_RE, $iid)) out(400, ['ok' => false, 'error' => 'iid']);
$fw = (is_string($in['fw'] ?? null) && preg_match('/^[0-9]{1,3}(\.[0-9]{1,4}){0,3}$/', $in['fw'])) ? $in['fw'] : '';

// Solo los campos conocidos, con su tipo; los textos, sin caracteres de control (salvo saltos de línea) y recortados
$txt = fn($v, $max) => is_string($v) ? mb_substr(preg_replace('/[^\P{C}\n]/u', '', $v), 0, $max) : '';
$num = fn($v) => is_int($v) ? $v : null;
$body = [
    'up' => $num($in['up'] ?? null), 'rr' => $txt($in['rr'] ?? '', 120),
    'heap' => is_array($in['heap'] ?? null) ? array_map($num, array_slice($in['heap'], 0, 2)) : [],
    'nvs' => $num($in['nvs'] ?? null),
    'stk' => is_array($in['stk'] ?? null) ? array_map($num, array_slice($in['stk'], 0, 3)) : [],
    'rssi' => $num($in['rssi'] ?? null),
    'log' => $txt($in['log'] ?? '', 6000), 'wbus' => $txt($in['wbus'] ?? '', 6000),
];

try { $db = wttc_db(); } catch (Throwable $e) { error_log('wttc registro: ' . $e->getMessage()); out(503, ['ok' => false, 'error' => 'no disponible']); }
$today = gmdate('Y-m-d');
$n = $db->prepare('SELECT COUNT(*) FROM board_logs WHERE iid = ? AND at >= ?');
$n->execute([$iid, $today]);
if ($n->fetchColumn() >= 20) out(429, ['ok' => false, 'error' => 'límite diario']);
$db->beginTransaction();
$db->prepare('INSERT INTO board_logs (iid, at, fw, body) VALUES (?, ?, ?, ?)')
   ->execute([$iid, gmdate('Y-m-d H:i:s'), $fw, json_encode($body, JSON_UNESCAPED_UNICODE)]);
$db->prepare('DELETE FROM board_logs WHERE iid = ? AND id NOT IN (SELECT id FROM board_logs WHERE iid = ? ORDER BY id DESC LIMIT 10)')
   ->execute([$iid, $iid]);
$db->commit();
out(200, ['ok' => true]);
