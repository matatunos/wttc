<?php
// wttc/estadisticas.php — Estadísticas públicas y agregadas de la app WTTC (https://wttc.favala.es/estadisticas.php).
// Solo cuenta instalaciones que han aceptado enviar datos anónimos; nada identifica a nadie (ver api/stats.php).
require_once __DIR__ . '/api/db.php';
ini_set('display_errors', '0');
header('Cache-Control: public, max-age=300');

$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$n = fn($v) => number_format((int)$v, 0, ',', '.');

$ok = true;
try {
    $db = wttc_db();
    $since30 = gmdate('Y-m-d', time() - 30 * 86400);
    $q = fn($sql, $p = []) => (function () use ($db, $sql, $p) { $s = $db->prepare($sql); $s->execute($p); return $s; })();

    $active = (int)$q('SELECT COUNT(*) FROM installs WHERE last_seen >= ?', [$since30])->fetchColumn();
    $total = (int)$q('SELECT COUNT(*) FROM installs')->fetchColumn();
    $tot = array_column($q('SELECT k, v FROM totals')->fetchAll(), 'v', 'k');

    // Instalaciones distintas que han informado cada semana (últimas 26)
    $weeks = []; $seen = [];
    for ($i = 25; $i >= 0; $i--) {
        $mon = strtotime('monday this week', time()) - $i * 7 * 86400;
        $weeks[gmdate('Y-m-d', $mon)] = 0;
    }
    $first = array_key_first($weeks);
    foreach ($q("SELECT day, id FROM pings WHERE day >= ?", [$first])->fetchAll() as $r) {
        $mon = gmdate('Y-m-d', strtotime('monday this week', strtotime($r['day'] . ' 12:00')));
        $seen[$mon][$r['id']] = 1;
    }
    foreach ($weeks as $k => $_) $weeks[$k] = isset($seen[$k]) ? count($seen[$k]) : 0;

    // Reparto entre las instalaciones activas
    $group = function (string $col) use ($q, $since30) {
        return $q("SELECT $col AS k, COUNT(*) AS n FROM installs WHERE last_seen >= ? GROUP BY $col ORDER BY n DESC, k", [$since30])->fetchAll();
    };
    $byApp = $group('app'); $byFw = $group('fw'); $bySdk = $group('sdk'); $byKind = $group('kind'); $byCountry = $group('country');
    $errs = $q('SELECT code, n FROM errors ORDER BY n DESC, code LIMIT 10')->fetchAll();
} catch (Throwable $e) {
    error_log('wttc estadisticas: ' . $e->getMessage());
    $ok = false;
}

$kindName = ['movil' => 'Móvil', 'tablet' => 'Tablet', 'radio' => 'Radio de coche'];
$countryName = fn(string $c): string => wttc_country($c);

// Lista de barras horizontales (una sola serie: un solo color)
function bars(array $rows, callable $label, string $empty = 'Sin datos todavía.'): string {
    if (!$rows) return '<p class="muted">' . $empty . '</p>';
    $max = max(array_map(fn($r) => (int)$r['n'], $rows)) ?: 1;
    $sum = array_sum(array_map(fn($r) => (int)$r['n'], $rows)) ?: 1;
    $o = '<div class="bars" role="table">';
    foreach ($rows as $r) {
        $v = (int)$r['n']; $l = htmlspecialchars($label($r), ENT_QUOTES, 'UTF-8');
        $pct = round($v / $sum * 100);
        $o .= '<div class="bar" role="row" title="' . $l . ': ' . $v . ' (' . $pct . ' %)"><span class="bl" role="cell">' . $l . '</span>'
            . '<span class="bt" role="cell"><i style="width:' . max(1, round($v / $max * 100)) . '%"></i></span>'
            . '<span class="bv" role="cell">' . number_format($v, 0, ',', '.') . '</span></div>';
    }
    return $o . '</div>';
}
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Estadísticas de WTTC</title>
<meta name="description" content="Uso público y anónimo de la app WTTC para la Webasto Thermo Top C.">
<style>
  :root{
    --bg-page:#0f1117; --bg-card:#1a1d27; --bg-inner:#13151f;
    --text:#e2e8f8; --muted:#7a84a8; --border:#2e3350; --acc:#3a8ee0; --grid:rgba(122,132,168,.16);
  }
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg-page);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;line-height:1.45}
  .wrap{max-width:1100px;margin:0 auto;padding:20px 16px 40px}
  a{color:var(--acc)}
  h1{font-size:1.5rem;margin:6px 0 4px}
  .sub{color:var(--muted);font-size:.9rem;max-width:820px}
  .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px;margin-top:16px}
  h2{font-size:.85rem;color:var(--muted);font-weight:700;margin:0 0 12px;text-transform:uppercase;letter-spacing:.05em}
  .kpis{display:grid;grid-template-columns:2fr repeat(4,1fr);gap:12px;margin-top:16px}
  @media (max-width:820px){.kpis{grid-template-columns:1fr 1fr}.kpis .hero{grid-column:1/-1}}
  .kpi{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:14px 16px}
  .kpi .l{color:var(--muted);font-size:.82rem}
  .kpi .v{font-size:1.9rem;font-weight:600;margin-top:2px}
  .kpi.hero .v{font-size:3.2rem;line-height:1.1}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
  @media (max-width:900px){.grid2,.grid3{grid-template-columns:1fr}}
  .grid2 .card,.grid3 .card{margin-top:0}
  .muted{color:var(--muted);font-size:.88rem}
  .bars{display:flex;flex-direction:column;gap:7px}
  .bar{display:grid;grid-template-columns:minmax(90px,38%) 1fr auto;gap:10px;align-items:center;font-size:.86rem}
  .bl{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .bt{height:10px;background:var(--bg-inner);border-radius:4px;overflow:hidden}
  .bt i{display:block;height:100%;background:var(--acc);border-radius:0 4px 4px 0}
  .bv{font-variant-numeric:tabular-nums;color:var(--muted);min-width:2.5em;text-align:right}
  .chart{position:relative}
  .chart svg{display:block;width:100%;height:220px}
  .tip{position:absolute;pointer-events:none;background:var(--bg-inner);border:1px solid var(--border);border-radius:8px;padding:6px 9px;font-size:.8rem;white-space:nowrap;display:none}
  table.t{width:100%;border-collapse:collapse;font-size:.82rem;margin-top:10px}
  table.t th,table.t td{text-align:left;padding:4px 6px;border-top:1px solid var(--border)}
  table.t td:last-child,table.t th:last-child{text-align:right;font-variant-numeric:tabular-nums}
  details summary{cursor:pointer;color:var(--muted);font-size:.84rem;margin-top:8px}
  ul.priv{margin:6px 0 0;padding-left:20px;font-size:.88rem}
  ul.priv li{margin-bottom:4px}
</style>
</head>
<body>
<div class="wrap">
  <a href="./" style="font-size:.85rem;text-decoration:none">← WTTC</a>
  <h1>Estadísticas de uso</h1>
  <div class="sub">Datos anónimos y agregados de las instalaciones de la app que han aceptado enviarlos. Se actualiza en cuanto llegan
    (como mucho, un informe por instalación y día).</div>

<?php if (!$ok): ?>
  <div class="card"><p class="muted">Las estadísticas no están disponibles ahora mismo. Vuelve a intentarlo más tarde.</p></div>
<?php else: ?>
  <div class="kpis">
    <div class="kpi hero"><div class="l">Instalaciones activas (30 días)</div><div class="v"><?= $n($active) ?></div></div>
    <div class="kpi"><div class="l">Instalaciones en total</div><div class="v"><?= $n($total) ?></div></div>
    <div class="kpi"><div class="l">Encendidos desde la app</div><div class="v"><?= $n($tot['starts_app'] ?? 0) ?></div></div>
    <div class="kpi"><div class="l">Encendidos por programa</div><div class="v"><?= $n($tot['starts_prog'] ?? 0) ?></div></div>
    <div class="kpi"><div class="l">Veces que se apagó sola</div><div class="v"><?= $n($tot['self_stops'] ?? 0) ?></div></div>
  </div>

  <div class="card">
    <h2>Instalaciones activas por semana</h2>
    <div class="chart" id="wk"><svg role="img" aria-label="Instalaciones que han informado cada semana, últimas 26 semanas"></svg><div class="tip"></div></div>
    <details><summary>Ver como tabla</summary>
      <table class="t"><thead><tr><th>Semana del</th><th>Instalaciones</th></tr></thead><tbody>
      <?php foreach (array_reverse($weeks, true) as $k => $v): ?><tr><td><?= $h(date('d/m/Y', strtotime($k))) ?></td><td><?= $n($v) ?></td></tr><?php endforeach; ?>
      </tbody></table></details>
  </div>

  <div class="grid3" style="margin-top:16px">
    <div class="card"><h2>Dispositivo</h2><?= bars($byKind, fn($r) => $kindName[$r['k']] ?? 'Sin indicar') ?></div>
    <div class="card"><h2>Versión de Android</h2><?= bars($bySdk, fn($r) => $r['k'] ? wttc_android((int)$r['k']) : 'Sin indicar') ?></div>
    <div class="card"><h2>País</h2><?= bars($byCountry, fn($r) => $countryName((string)$r['k'])) ?></div>
  </div>
  <div class="grid3" style="margin-top:16px">
    <div class="card"><h2>Versión de la app</h2><?= bars($byApp, fn($r) => $r['k'] ?: 'Sin indicar') ?></div>
    <div class="card"><h2>Versión del firmware</h2><?= bars($byFw, fn($r) => $r['k'] ?: 'Sin conectar aún') ?></div>
    <div class="card"><h2>Averías más comunes</h2><?= bars($errs ? array_map(fn($e) => ['k' => $e['code'], 'n' => $e['n']], $errs) : [],
        fn($r) => '0x' . $r['k'] . (wttc_error_name($r['k']) ? ' · ' . wttc_error_name($r['k']) : ''), 'Ninguna avería registrada.') ?></div>
  </div>
  <p class="muted" style="margin-top:8px">Los repartos cuentan las instalaciones activas en los últimos 30 días. Las averías, desde el principio:
    códigos que la Webasto guardó cuando se apagó sola con la app conectada.</p>
<?php endif; ?>

  <div class="card">
    <h2>Qué se envía y qué no</h2>
    <ul class="priv">
      <li>Solo si aceptas al abrir la app por primera vez (los dos botones son iguales; puedes cambiarlo en «Este dispositivo»).</li>
      <li>Un informe al día como mucho: identificador aleatorio de instalación (no es el del móvil ni el de tu cuenta), versión de la app y del firmware,
        versión de Android, tipo de dispositivo (móvil, tablet o radio), país según el idioma del sistema, número de encendidos (desde la app y por
        programa), veces que se apagó sola y sus códigos de avería.</li>
      <li>Nunca: ubicación, nombres, redes Wi-Fi, PIN, tokens, horarios ni nada de tus programas.</li>
      <li>No se guarda tu IP: el servidor no registra estas peticiones.</li>
      <li>Si retiras el permiso, la app pide borrar los datos de su instalación (los totales ya sumados quedan, sin nada que los relacione contigo).</li>
      <li>El código del servidor y de la app es público: <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">github.com/matatunos/wttc</a>.</li>
    </ul>
  </div>
</div>

<?php if ($ok): ?>
<script>
// Línea de una sola serie con cruceta y tooltip (sin librerías)
(() => {
  const data = <?= json_encode(array_map(fn($k, $v) => [$k, $v], array_keys($weeks), array_values($weeks))) ?>;
  const box = document.getElementById('wk'), svg = box.querySelector('svg'), tip = box.querySelector('.tip');
  const NS = 'http://www.w3.org/2000/svg', el = (t, a) => { const e = document.createElementNS(NS, t); for (const k in a) e.setAttribute(k, a[k]); return e; };
  const fmt = d => { const [y, m, dd] = d.split('-'); return dd + '/' + m; };
  function draw() {
    svg.innerHTML = '';
    const W = box.clientWidth, H = 220, L = 34, R = 10, T = 12, B = 26;
    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
    const max = Math.max(4, ...data.map(d => d[1])), step = Math.ceil(max / 4);
    const top = step * 4, x = i => L + i * (W - L - R) / (data.length - 1), y = v => T + (H - T - B) * (1 - v / top);
    for (let v = 0; v <= top; v += step) {
      svg.appendChild(el('line', { x1: L, x2: W - R, y1: y(v), y2: y(v), stroke: 'var(--grid)', 'stroke-width': 1 }));
      const t = el('text', { x: L - 6, y: y(v) + 4, 'text-anchor': 'end', 'font-size': 11, fill: 'var(--muted)' }); t.textContent = v; svg.appendChild(t);
    }
    data.forEach((d, i) => { if (i % 4 === 0 || i === data.length - 1) {
      const t = el('text', { x: x(i), y: H - 8, 'text-anchor': 'middle', 'font-size': 11, fill: 'var(--muted)' }); t.textContent = fmt(d[0]); svg.appendChild(t); } });
    const pts = data.map((d, i) => `${x(i)},${y(d[1])}`).join(' ');
    svg.appendChild(el('polygon', { points: `${x(0)},${y(0)} ${pts} ${x(data.length - 1)},${y(0)}`, fill: 'var(--acc)', 'fill-opacity': .12 }));
    svg.appendChild(el('polyline', { points: pts, fill: 'none', stroke: 'var(--acc)', 'stroke-width': 2, 'stroke-linejoin': 'round', 'stroke-linecap': 'round' }));
    const last = data.length - 1;
    svg.appendChild(el('circle', { cx: x(last), cy: y(data[last][1]), r: 4, fill: 'var(--acc)', stroke: 'var(--bg-card)', 'stroke-width': 2 }));
    const cross = el('line', { y1: T, y2: H - B, stroke: 'var(--muted)', 'stroke-width': 1, 'stroke-dasharray': '3 3', visibility: 'hidden' });
    const dot = el('circle', { r: 5, fill: 'var(--acc)', stroke: 'var(--bg-card)', 'stroke-width': 2, visibility: 'hidden' });
    svg.append(cross, dot);
    const hit = el('rect', { x: L, y: 0, width: W - L - R, height: H, fill: 'transparent' });
    svg.appendChild(hit);
    const move = ev => {
      const r = svg.getBoundingClientRect(), px = (ev.touches ? ev.touches[0].clientX : ev.clientX) - r.left;
      const i = Math.max(0, Math.min(last, Math.round((px - L) / ((W - L - R) / last))));
      cross.setAttribute('x1', x(i)); cross.setAttribute('x2', x(i)); cross.setAttribute('visibility', 'visible');
      dot.setAttribute('cx', x(i)); dot.setAttribute('cy', y(data[i][1])); dot.setAttribute('visibility', 'visible');
      tip.innerHTML = `Semana del ${fmt(data[i][0])}<br><b>${data[i][1]}</b> instalaciones`;
      tip.style.display = 'block';
      tip.style.left = Math.min(W - tip.offsetWidth, Math.max(0, x(i) - tip.offsetWidth / 2)) + 'px';
      tip.style.top = Math.max(0, y(data[i][1]) - tip.offsetHeight - 12) + 'px';
    };
    const out = () => { cross.setAttribute('visibility', 'hidden'); dot.setAttribute('visibility', 'hidden'); tip.style.display = 'none'; };
    hit.addEventListener('mousemove', move); hit.addEventListener('touchstart', move, { passive: true });
    hit.addEventListener('touchmove', move, { passive: true }); hit.addEventListener('mouseleave', out);
  }
  draw();
  let rt; addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(draw, 150); });
})();
</script>
<?php endif; ?>
</body>
</html>
