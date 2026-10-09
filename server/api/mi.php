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

// Cada encendido: los 11 números de siempre y, desde la 0.2.17, 7 más (null = sin dato o placa más antigua)
$runs = array_map(fn($r) => array_map(fn($v) => $v === null ? null : (int)$v, array_values($r)),
    $q('SELECT seq, t0, dur, ml, cab0, cab1, cmax, vmin, src, endr, err, hum0, hum1, tgt, treach, c0, v0, pw
        FROM board_runs WHERE iid = ? ORDER BY seq', [$c])->fetchAll());
$days = array_map(fn($r) => [$r['day'], (float)$r['gas'], (int)$r['hsec'], (int)$r['nruns']],
    $q('SELECT day, gas, hsec, nruns FROM board_days WHERE iid = ? ORDER BY day', [$c])->fetchAll());
// Comparación con las demás placas (últimos 90 días): medianas por placa de horas y encendidos a la semana y de litros
// por hora. Solo con 3 placas o más (con menos, la «media» casi sería la de otra persona); nunca datos de una placa suelta
$since = time() - 90 * 86400;
$per = $q('SELECT iid, SUM(dur) AS s, COUNT(*) AS n, SUM(ml) AS ml FROM board_runs WHERE t0 >= ? GROUP BY iid', [$since])->fetchAll();
$med = function (array $v) { if (!$v) return null; sort($v); $m = intdiv(count($v), 2); return count($v) % 2 ? $v[$m] : ($v[$m - 1] + $v[$m]) / 2; };
$weeks = 90 / 7;
$com = ['n' => count($per)];
if (count($per) >= 3) {
    $com['hweek'] = round($med(array_map(fn($r) => $r['s'] / 3600 / $weeks, $per)), 2);
    $com['rweek'] = round($med(array_map(fn($r) => $r['n'] / $weeks, $per)), 2);
    $com['lph'] = round($med(array_map(fn($r) => $r['s'] ? $r['ml'] / 1000 / ($r['s'] / 3600) : 0, $per)), 3);
    $com['min'] = round($med(array_map(fn($r) => $r['s'] / 60 / $r['n'], $per)), 1);
}

$errs = [];
foreach ($runs as $r) if ($r[10]) { $k = sprintf('%02X', $r[10]); $errs[$k] = wttc_error_name($k) ?: ''; }
out(200, [
    'iid' => $c,
    'board' => ['first' => $b['first_seen'], 'last' => $b['last_seen'], 'fw' => $b['fw'], 'gas' => (float)$b['gas'],
                'hsec' => (int)$b['hsec'], 'nruns' => (int)$b['nruns'], 'sens' => (int)$b['sens'], 'oled' => (int)$b['oled']],
    'runs' => $runs, 'days' => $days, 'errnames' => $errs, 'comunidad' => $com,
]);
