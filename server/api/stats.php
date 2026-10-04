<?php
// wttc/api/stats.php — Recoge el informe diario (opcional, con permiso) de la app WTTC.
// POST JSON: {"id","app","fw","sdk","kind","country","starts_app","starts_prog","self_stops","errors":{"02":1}}
// o {"id","borrar":true} para borrar los datos de esa instalación.
// Un informe por instalación y día; los contadores son incrementos desde el último informe aceptado.
// No se lee ni se guarda la IP (y Caddy no registra esta ruta).
require_once __DIR__ . '/db.php';
ini_set('display_errors', '0');
header('Content-Type: application/json');
header('Cache-Control: no-store');

function out(int $code, array $j) { http_response_code($code); echo json_encode($j); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, ['ok' => false, 'error' => 'solo POST']);
$raw = file_get_contents('php://input', false, null, 0, 2049);
if ($raw === false || strlen($raw) > 2048) out(413, ['ok' => false, 'error' => 'demasiado grande']);
$in = json_decode($raw, true);
if (!is_array($in)) out(400, ['ok' => false, 'error' => 'JSON no válido']);

$id = $in['id'] ?? '';
if (!is_string($id) || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $id)) out(400, ['ok' => false, 'error' => 'id']);

try { $db = wttc_db(); } catch (Throwable $e) { error_log('wttc stats: ' . $e->getMessage()); out(503, ['ok' => false, 'error' => 'no disponible']); }
$today = gmdate('Y-m-d');

if (!empty($in['borrar'])) {
    $db->prepare('DELETE FROM installs WHERE id = ?')->execute([$id]);
    $db->prepare('DELETE FROM pings WHERE id = ?')->execute([$id]);
    out(200, ['ok' => true, 'borrado' => true]);
}

$str = fn($v, $re) => (is_string($v) && preg_match($re, $v)) ? $v : '';
$int = fn($v, $min, $max) => (is_int($v) && $v >= $min && $v <= $max) ? $v : 0;
$app = $str($in['app'] ?? '', '/^[0-9]{1,3}(\.[0-9]{1,4}){0,3}$/');
$fw = $str($in['fw'] ?? '', '/^[0-9]{1,3}(\.[0-9]{1,4}){0,3}$/');
$sdk = $int($in['sdk'] ?? 0, 21, 60);
$kind = in_array($in['kind'] ?? '', WTTC_KINDS, true) ? $in['kind'] : '';
$country = $str($in['country'] ?? '', '/^[A-Z]{2}$/');
$cnt = ['starts_app' => $int($in['starts_app'] ?? 0, 0, 300), 'starts_prog' => $int($in['starts_prog'] ?? 0, 0, 300),
        'self_stops' => $int($in['self_stops'] ?? 0, 0, 100)];
$errs = [];
if (isset($in['errors']) && is_array($in['errors'])) {
    foreach (array_slice($in['errors'], 0, 12, true) as $c => $n) {
        if (is_string($c) && preg_match('/^[0-9A-F]{2}$/', $c) && is_int($n) && $n >= 1 && $n <= 50) $errs[$c] = $n;
    }
}

$db->beginTransaction();
$known = $db->prepare('SELECT 1 FROM installs WHERE id = ?');
$known->execute([$id]);
if (!$known->fetchColumn()) {
    // Freno a identificadores inventados en masa: como mucho 300 instalaciones nuevas al día
    $new = $db->prepare('SELECT COUNT(*) FROM installs WHERE first_seen = ?');
    $new->execute([$today]);
    if ($new->fetchColumn() >= 300) { $db->rollBack(); out(429, ['ok' => false, 'error' => 'límite diario']); }
}
$p = $db->prepare('INSERT OR IGNORE INTO pings (day, id) VALUES (?, ?)');
$p->execute([$today, $id]);
if ($p->rowCount() === 0) { $db->rollBack(); out(200, ['ok' => true, 'dup' => true]); }   // ya informó hoy: sin sumar

$db->prepare('INSERT INTO installs (id, first_seen, last_seen, app, fw, sdk, kind, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
              ON CONFLICT(id) DO UPDATE SET last_seen = excluded.last_seen, app = excluded.app,
              fw = CASE WHEN excluded.fw <> \'\' THEN excluded.fw ELSE installs.fw END,
              sdk = excluded.sdk, kind = excluded.kind, country = excluded.country')
   ->execute([$id, $today, $today, $app, $fw, $sdk, $kind, $country]);
$t = $db->prepare('INSERT INTO totals (k, v) VALUES (?, ?) ON CONFLICT(k) DO UPDATE SET v = v + excluded.v');
foreach ($cnt as $k => $v) if ($v) $t->execute([$k, $v]);
$e = $db->prepare('INSERT INTO errors (code, n) VALUES (?, ?) ON CONFLICT(code) DO UPDATE SET n = n + excluded.n');
foreach ($errs as $c => $n) $e->execute([$c, $n]);
$db->commit();
out(200, ['ok' => true]);
