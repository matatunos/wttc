<?php
// wttc/api/mi.php — Datos de una placa para mi.php, con su código de instalación (que va en el cuerpo, no en la URL).
// Código generado íntegramente con Claude (Anthropic).
// POST JSON {"c":"XXXX-XXXX-XXXX-XXXX"} → la placa, sus encendidos y la evolución diaria de sus contadores.
// POST JSON {"c":…, "borrar":true} → borra todo lo de esa placa (si vuelve a enviar, empieza de cero).
// Sin el código no se puede ver nada (16 caracteres al azar: 80 bits) y no hay ningún listado de placas.
require_once __DIR__ . '/db.php';
ini_set('display_errors', '0');
header('Content-Type: application/json');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

function out(int $code, array $j) { http_response_code($code); echo json_encode($j); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, ['error' => 'solo POST']);
$in = json_decode((string)file_get_contents('php://input', false, null, 0, 512), true);
$c = strtoupper(preg_replace('/[\s-]+/', '', (string)($in['c'] ?? '')));
if (strlen($c) === 16) $c = implode('-', str_split($c, 4));
if (!preg_match(WTTC_IID_RE, $c)) out(400, ['error' => 'codigo']);

try { $db = wttc_db(); } catch (Throwable $e) { error_log('wttc mi: ' . $e->getMessage()); out(503, ['error' => 'no disponible']); }
$q = function ($sql, $p = []) use ($db) { $s = $db->prepare($sql); $s->execute($p); return $s; };

if (!empty($in['borrar'])) {
    $db->beginTransaction();
    foreach (['boards', 'board_runs', 'board_days'] as $t) $q("DELETE FROM $t WHERE iid = ?", [$c]);
    $db->commit();
    out(200, ['ok' => true, 'borrado' => true]);
}

$b = $q('SELECT first_seen, last_seen, fw, lang, gas, hsec, nruns, oled, sens FROM boards WHERE iid = ?', [$c])->fetch();
if (!$b) { usleep(300000); out(404, ['error' => 'no']); }   // un poco de espera: probar códigos a ciegas no compensa

$runs = array_map(fn($r) => array_map('intval', array_values($r)),
    $q('SELECT seq, t0, dur, ml, cab0, cab1, cmax, vmin, src, endr, err FROM board_runs WHERE iid = ? ORDER BY seq', [$c])->fetchAll());
$days = array_map(fn($r) => [$r['day'], (float)$r['gas'], (int)$r['hsec'], (int)$r['nruns']],
    $q('SELECT day, gas, hsec, nruns FROM board_days WHERE iid = ? ORDER BY day', [$c])->fetchAll());
$errs = [];
foreach ($runs as $r) if ($r[10]) { $k = sprintf('%02X', $r[10]); $errs[$k] = wttc_error_name($k) ?: ''; }
out(200, [
    'iid' => $c,
    'board' => ['first' => $b['first_seen'], 'last' => $b['last_seen'], 'fw' => $b['fw'], 'gas' => (float)$b['gas'],
                'hsec' => (int)$b['hsec'], 'nruns' => (int)$b['nruns'], 'sens' => (int)$b['sens'], 'oled' => (int)$b['oled']],
    'runs' => $runs, 'days' => $days, 'errnames' => $errs,
]);
