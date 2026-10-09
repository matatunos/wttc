<?php
// wttc/api/placa.php — Recibe las estadísticas de una placa (opcionales: «Enviar las estadísticas de esta placa»).
// Código generado íntegramente con Claude (Anthropic).
// Las envía la propia placa si tiene internet, o la app si no (el mismo JSON, que la placa le da por Bluetooth).
// POST JSON: {"iid","fw","lang","gas","hsec","nruns","oled","sens","runs":[[seq,t0,dur,ml,cab0,cab1,cmax,vmin,src,end,err],…]}
//   desde la 0.2.17, cada encendido trae además [hum0,hum1,tgt,treach,c0,v0,pw] (18 números; 255 = sin dato)
//   iid = código de instalación (al azar, lo crea la placa); runs = encendidos aún no enviados, con su número
//   (los repetidos se ignoran). Responde {"ok":true,"ack":N}: la placa ya no vuelve a mandar hasta el N.
// No se lee ni se guarda la IP (y Caddy no registra /api/*). Los datos se ven en mi.php con el código, y se borran ahí.
require_once __DIR__ . '/db.php';
ini_set('display_errors', '0');
header('Content-Type: application/json');
header('Cache-Control: no-store');

function out(int $code, array $j) { http_response_code($code); echo json_encode($j); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(405, ['ok' => false, 'error' => 'solo POST']);
$raw = file_get_contents('php://input', false, null, 0, 8193);
if ($raw === false || strlen($raw) > 8192) out(413, ['ok' => false, 'error' => 'demasiado grande']);
$in = json_decode($raw, true);
if (!is_array($in)) out(400, ['ok' => false, 'error' => 'JSON no válido']);
$iid = $in['iid'] ?? '';
if (!is_string($iid) || !preg_match(WTTC_IID_RE, $iid)) out(400, ['ok' => false, 'error' => 'iid']);

$int = fn($v, $min, $max) => (is_int($v) && $v >= $min && $v <= $max) ? $v : null;
$fw = (is_string($in['fw'] ?? null) && preg_match('/^[0-9]{1,3}(\.[0-9]{1,4}){0,3}$/', $in['fw'])) ? $in['fw'] : '';
$lang = in_array($in['lang'] ?? '', ['es', 'en', 'de'], true) ? $in['lang'] : '';
$gas = (is_int($in['gas'] ?? null) || is_float($in['gas'] ?? null)) && $in['gas'] >= 0 && $in['gas'] < 100000 ? round((float)$in['gas'], 2) : null;
$hsec = $int($in['hsec'] ?? null, 0, 2000000000);
$nruns = $int($in['nruns'] ?? null, 0, 100000000);
$oled = $int($in['oled'] ?? null, 0, 9);
$sens = $int($in['sens'] ?? null, 0, 1);

try { $db = wttc_db(); } catch (Throwable $e) { error_log('wttc placa: ' . $e->getMessage()); out(503, ['ok' => false, 'error' => 'no disponible']); }
$today = gmdate('Y-m-d');
$now = time();

$db->beginTransaction();
$known = $db->prepare('SELECT 1 FROM boards WHERE iid = ?');
$known->execute([$iid]);
if (!$known->fetchColumn()) {
    // Freno a códigos inventados en masa: como mucho 300 placas nuevas al día
    $new = $db->prepare('SELECT COUNT(*) FROM boards WHERE first_seen = ?');
    $new->execute([$today]);
    if ($new->fetchColumn() >= 300) { $db->rollBack(); out(429, ['ok' => false, 'error' => 'límite diario']); }
}
$db->prepare('INSERT INTO boards (iid, first_seen, last_seen, fw, lang, gas, hsec, nruns, oled, sens) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
              ON CONFLICT(iid) DO UPDATE SET last_seen = excluded.last_seen,
              fw = COALESCE(NULLIF(excluded.fw, \'\'), boards.fw), lang = COALESCE(NULLIF(excluded.lang, \'\'), boards.lang),
              gas = COALESCE(excluded.gas, boards.gas), hsec = COALESCE(excluded.hsec, boards.hsec),
              nruns = COALESCE(excluded.nruns, boards.nruns), oled = COALESCE(excluded.oled, boards.oled),
              sens = COALESCE(excluded.sens, boards.sens)')
   ->execute([$iid, $today, $today, $fw, $lang, $gas, $hsec, $nruns, $oled, $sens]);
// Foto del día de los contadores de la placa (para la evolución, aunque no se apunten encendidos sueltos)
$db->prepare('INSERT INTO board_days (iid, day, gas, hsec, nruns) VALUES (?, ?, ?, ?, ?)
              ON CONFLICT(iid, day) DO UPDATE SET gas = excluded.gas, hsec = excluded.hsec, nruns = excluded.nruns')
   ->execute([$iid, $today, $gas, $hsec, $nruns]);

$ack = 0;
$ins = $db->prepare('INSERT OR IGNORE INTO board_runs (iid, seq, t0, dur, ml, cab0, cab1, cmax, vmin, src, endr, err, got,
                     hum0, hum1, tgt, treach, c0, v0, pw) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
foreach (array_slice(is_array($in['runs'] ?? null) ? $in['runs'] : [], 0, 64) as $r) {
    if (!is_array($r) || (count($r) !== 11 && count($r) !== 18)) continue;
    $seq = $int($r[0], 1, 1000000000);
    if ($seq === null) continue;
    $ack = max($ack, $seq);                       // aunque un encendido venga mal, no se vuelve a pedir
    $t0 = $int($r[1], 0, $now + 86400);
    if ($t0 !== null && $t0 && $t0 < 1600000000) $t0 = 0;   // placa sin hora: 0
    $v = [$t0, $int($r[2], 0, 65535), $int($r[3], 0, 65535), $int($r[4], -128, 90), $int($r[5], -128, 90),
          $int($r[6], 0, 255), $int($r[7], 0, 255), $int($r[8], 0, 255), $int($r[9], 0, 15), $int($r[10], 0, 255)];
    if (in_array(null, $v, true)) continue;
    // Datos de la 0.2.17+ (255 = sin dato → NULL); con 11 números, todos NULL
    $x = [];
    foreach ([[11, 0, 100], [12, 0, 100], [13, 1, 40], [14, 0, 254], [15, 1, 254], [16, 1, 254], [17, 0, 254]] as [$i, $lo, $hi])
        $x[] = (count($r) === 18 && is_int($r[$i]) && $r[$i] >= $lo && $r[$i] <= $hi) ? $r[$i] : null;
    $ins->execute(array_merge([$iid, $seq], $v, [$today], $x));
}
$db->commit();
out(200, ['ok' => true, 'ack' => $ack]);
