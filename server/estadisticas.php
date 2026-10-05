<?php
// wttc/estadisticas.php — Estadísticas públicas y agregadas de la app WTTC (https://wttc.favala.es/estadisticas.php).
// Código generado íntegramente con Claude (Anthropic).
// La página carga vacía y pide los datos a ?action=data&period=… (patrón común del portal, con tools/charts.js);
// las gráficas usan Chart.js servido desde /vendor/. Los repartos son listas de barras de una sola serie.
// Solo cuenta instalaciones que han aceptado enviar datos anónimos; nada identifica a nadie (ver api/stats.php).
require_once __DIR__ . '/api/db.php';
ini_set('display_errors', '0');
wttc_public_only();

// ---------- datos para las gráficas (mismo patrón que el resto del portal: ?action=data&period=…) ----------
if (($_GET['action'] ?? '') === 'data') {
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    try {
        $db = wttc_db();
        [$from, $to] = wttc_range($_GET);
        $q = function ($sql, $p = []) use ($db) { $s = $db->prepare($sql); $s->execute($p); return $s; };
        $days = (int)((strtotime($to) - strtotime($from)) / 86400) + 1;
        $weekly = $days > 120;                                   // rangos largos: por semanas
        $bucket = fn($d) => $weekly ? gmdate('Y-m-d', strtotime('monday this week', strtotime($d . ' 12:00'))) : $d;
        $axis = [];
        for ($t = strtotime($from . ' 12:00'); $t <= strtotime($to . ' 12:00'); $t += 86400) $axis[$bucket(gmdate('Y-m-d', $t))] = 0;
        $active = $axis; $seen = [];
        foreach ($q('SELECT day, id FROM pings WHERE day BETWEEN ? AND ?', [$from, $to])->fetchAll() as $r) $seen[$bucket($r['day'])][$r['id']] = 1;
        foreach ($active as $k => $_) $active[$k] = isset($seen[$k]) ? count($seen[$k]) : 0;
        $starts = ['starts_app' => $axis, 'starts_prog' => $axis];
        $sum = ['starts_app' => 0, 'starts_prog' => 0, 'self_stops' => 0];
        foreach ($q('SELECT day, k, v FROM daily WHERE day BETWEEN ? AND ?', [$from, $to])->fetchAll() as $r) {
            $bk = $bucket($r['day']);
            if (isset($starts[$r['k']][$bk])) $starts[$r['k']][$bk] += (int)$r['v'];
            if (isset($sum[$r['k']])) $sum[$r['k']] += (int)$r['v'];
        }
        $ids = (int)$q('SELECT COUNT(DISTINCT id) FROM pings WHERE day BETWEEN ? AND ?', [$from, $to])->fetchColumn();
        $new = (int)$q('SELECT COUNT(*) FROM installs WHERE first_seen BETWEEN ? AND ?', [$from, $to])->fetchColumn();
        // Repartos entre las instalaciones que informaron en el periodo
        $grp = fn($col) => $q("SELECT $col AS k, COUNT(*) AS n FROM installs WHERE id IN (SELECT DISTINCT id FROM pings WHERE day BETWEEN ? AND ?)
                               GROUP BY $col ORDER BY n DESC, k", [$from, $to])->fetchAll();
        $kinds = ['movil' => 'Móvil', 'tablet' => 'Tablet', 'radio' => 'Radio de coche'];
        $lab = fn($rows, $f) => array_map(fn($r) => [$f($r['k']), (int)$r['n']], $rows);
        echo json_encode([
            'from' => $from, 'to' => $to, 'weekly' => $weekly,
            'kpi' => ['active' => $ids, 'new' => $new, 'total' => (int)$q('SELECT COUNT(*) FROM installs')->fetchColumn()] + $sum,
            'axis' => array_keys($axis), 'active' => array_values($active),
            'app' => array_values($starts['starts_app']), 'prog' => array_values($starts['starts_prog']),
            'by' => [
                'kind' => $lab($grp('kind'), fn($k) => $kinds[$k] ?? 'Sin indicar'),
                'sdk' => $lab($grp('sdk'), fn($k) => $k ? wttc_android((int)$k) : 'Sin indicar'),
                'country' => $lab($grp('country'), fn($k) => wttc_country((string)$k)),
                'app' => $lab($grp('app'), fn($k) => $k ?: 'Sin indicar'),
                'fw' => $lab($grp('fw'), fn($k) => $k ?: 'Sin conectar aún'),
                'err' => array_map(fn($r) => ['0x' . $r['code'] . (wttc_error_name($r['code']) ? ' · ' . wttc_error_name($r['code']) : ''), (int)$r['n']],
                    $q('SELECT code, SUM(n) AS n FROM err_daily WHERE day BETWEEN ? AND ? GROUP BY code ORDER BY n DESC LIMIT 10', [$from, $to])->fetchAll()),
            ],
        ]);
    } catch (Throwable $e) {
        error_log('wttc estadisticas: ' . $e->getMessage());
        http_response_code(503);
        echo json_encode(['error' => 'no disponible']);
    }
    exit;
}
wttc_visit('estadisticas');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Estadísticas de WTTC</title>
<meta name="description" content="Uso público y anónimo de la app WTTC para la Webasto Thermo Top C.">
<link rel="canonical" href="https://wttc.favala.es/estadisticas.php">
<style>
  :root{
    --bg-page:#0f1117; --bg-card:#1a1d27; --bg-inner:#13151f;
    --text:#e2e8f8; --muted:#7a84a8; --border:#2e3350; --acc:#3a8ee0; --ice:#5bc0eb; --grid:rgba(122,132,168,.16);
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
  .chartbox{position:relative;height:240px}
  .ctrls{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-top:16px}
  .periods{display:flex;gap:.3rem;flex-wrap:wrap}
  .periods button{background:var(--bg-card);border:1px solid var(--border);color:var(--muted);border-radius:9px;padding:.35rem .7rem;font-size:.82rem;font-weight:700;cursor:pointer;font-family:inherit}
  .periods button.act{background:var(--acc);border-color:var(--acc);color:#fff}
  .rangebox{display:flex;gap:.35rem;align-items:center;flex-wrap:wrap}
  .rangebox input[type=date]{background:var(--bg-card);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:.3rem .5rem;font-size:.8rem;font-family:inherit;color-scheme:dark}
  .rangebox button{background:var(--acc);border:none;color:#fff;border-radius:8px;padding:.36rem .8rem;font-size:.8rem;font-weight:800;cursor:pointer;font-family:inherit}
  .rangebox.act input[type=date]{border-color:var(--acc)}
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

  <div class="ctrls">
    <div class="periods" id="periods"></div>
    <div class="rangebox">
      <input type="date" id="rfrom" aria-label="Desde"><span class="muted">→</span>
      <input type="date" id="rto" aria-label="Hasta"><button id="rapply">Aplicar</button>
    </div>
  </div>

  <div class="kpis" id="kpis"></div>

  <div class="grid2" style="margin-top:16px">
    <div class="card"><h2 id="hAct">Instalaciones activas</h2><div class="chartbox"><canvas id="chAct" role="img" aria-label="Instalaciones que enviaron informe en cada día o semana del periodo"></canvas></div></div>
    <div class="card"><h2 id="hSt">Encendidos</h2><div class="chartbox"><canvas id="chSt" role="img" aria-label="Encendidos desde la app y por programa en cada día o semana del periodo"></canvas></div></div>
  </div>
  <div class="grid3" style="margin-top:16px">
    <div class="card"><h2>Dispositivo</h2><div id="bKind"></div></div>
    <div class="card"><h2>Versión de Android</h2><div id="bSdk"></div></div>
    <div class="card"><h2>País</h2><div id="bCountry"></div></div>
  </div>
  <div class="grid3" style="margin-top:16px">
    <div class="card"><h2>Versión de la app</h2><div id="bApp"></div></div>
    <div class="card"><h2>Versión del firmware</h2><div id="bFw"></div></div>
    <div class="card"><h2>Averías al apagarse sola</h2><div id="bErr"></div></div>
  </div>
  <p class="muted" style="margin-top:8px">Todo se refiere al periodo elegido: instalaciones que enviaron algún informe en esas fechas y lo que contaron.</p>

  <div class="card">
    <h2>Qué se envía y qué no</h2>
    <ul class="priv">
      <li>Solo si aceptas al abrir la app por primera vez (los dos botones son iguales; puedes cambiar de idea en «Ajustes de la app»).</li>
      <li>Un informe al día como mucho: identificador aleatorio de instalación (no es el del móvil ni el de tu cuenta), versión de la app y del firmware,
        versión de Android, tipo de dispositivo (móvil, tablet o radio), país según el idioma del sistema, número de encendidos (desde la app y por
        programa), veces que se apagó sola y sus códigos de avería.</li>
      <li>Nunca: ubicación, nombres, redes Wi-Fi, PIN, tokens, horarios ni nada de tus programas.</li>
      <li>No se guarda tu IP: el servidor no registra estas peticiones.</li>
      <li>Si retiras el permiso, la app pide borrar los datos de su instalación (los totales ya sumados quedan, sin nada que los relacione contigo).</li>
      <li>El código del servidor y de la app es público: <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">github.com/matatunos/wttc</a>.</li>
      <li>Todo el código y el contenido de WTTC (firmware, app, servidor, web y documentación) están generados íntegramente con Claude (Anthropic).</li>
    </ul>
  </div>
</div>

<script src="/vendor/chartjs/4.4.1/chart.umd.min.js"></script>
<script src="/charts.js"></script>
<script>
"use strict";
// Gráficas con la librería común del portal: Chart.js + tools/charts.js (selector de periodo y fechas concretas)
const css = v => getComputedStyle(document.documentElement).getPropertyValue(v).trim();
const $ = id => document.getElementById(id);
const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const nf = n => Number(n).toLocaleString('es-ES');
let period = '30d', custom = null, chAct = null, chSt = null;

TCharts.rangePicker({
  periods: [['7d', '7 días'], ['30d', '30 días'], ['90d', '90 días'], ['1y', '1 año'], ['all', 'Todo']],
  initialPeriod: period,
  onChange: (p, c) => { period = p; custom = c; load(); },
  onError: m => alert(m),
});

const kpi = (l, v, hero) => `<div class="kpi${hero ? ' hero' : ''}"><div class="l">${l}</div><div class="v">${v}</div></div>`;

// Repartos: lista de barras de una sola serie (un solo color), con el número al final
function bars(el, rows, empty) {
  if (!rows.length) { $(el).innerHTML = `<p class="muted">${empty || 'Sin datos en este periodo.'}</p>`; return; }
  const max = Math.max(...rows.map(r => r[1])) || 1, sum = rows.reduce((a, r) => a + r[1], 0) || 1;
  $(el).innerHTML = '<div class="bars">' + rows.map(([l, n]) =>
    `<div class="bar" title="${esc(l)}: ${n} (${Math.round(n / sum * 100)} %)"><span class="bl">${esc(l)}</span>` +
    `<span class="bt"><i style="width:${Math.max(1, Math.round(n / max * 100))}%"></i></span><span class="bv">${nf(n)}</span></div>`).join('') + '</div>';
}

const fmtDay = d => { const [, m, dd] = d.split('-'); return `${dd}/${m}`; };
const chart = (old, id, cfg) => { if (old) { old.data = cfg.data; old.options = cfg.options; old.update(); return old; } return new Chart($(id), cfg); };

function render(d) {
  $('kpis').innerHTML = kpi('Instalaciones activas en el periodo', nf(d.kpi.active), true) + kpi('Nuevas', nf(d.kpi.new)) +
    kpi('Encendidos desde la app', nf(d.kpi.starts_app)) + kpi('Encendidos por programa', nf(d.kpi.starts_prog)) +
    kpi('Veces que se apagó sola', nf(d.kpi.self_stops));
  const unit = d.weekly ? 'por semana' : 'por día';
  $('hAct').textContent = 'Instalaciones activas ' + unit;
  $('hSt').textContent = 'Encendidos ' + unit;
  const acc = css('--acc'), ice = css('--ice'), mut = css('--muted'), grid = css('--grid'), labels = d.axis.map(fmtDay);
  const title = c => (d.weekly ? 'Semana del ' : '') + c[0].label;
  const scales = (stacked) => ({
    x: { stacked, grid: { display: false }, ticks: { color: mut, maxTicksLimit: 8 } },
    y: { stacked, beginAtZero: true, grid: { color: grid }, ticks: { color: mut, precision: 0 } },
  });
  const base = { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false } };

  // Una serie: sin leyenda (el título dice qué es)
  chAct = chart(chAct, 'chAct', { type: 'line',
    data: { labels, datasets: [{ label: 'Instalaciones', data: d.active, borderColor: acc, backgroundColor: acc + '22', fill: true,
      borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 12, tension: .25 }] },
    options: { ...base, scales: scales(false), plugins: { legend: { display: false },
      tooltip: { callbacks: { title, label: c => ` ${c.parsed.y} instalaciones` } } } } });

  // Dos series apiladas: leyenda siempre
  chSt = chart(chSt, 'chSt', { type: 'bar',
    data: { labels, datasets: [
      { label: 'Desde la app', data: d.app, backgroundColor: acc, borderRadius: 4, borderSkipped: 'bottom', borderColor: css('--bg-card'), borderWidth: 1 },
      { label: 'Por programa', data: d.prog, backgroundColor: ice, borderRadius: 4, borderSkipped: 'bottom', borderColor: css('--bg-card'), borderWidth: 1 }] },
    options: { ...base, scales: scales(true), plugins: { legend: { labels: { color: mut, boxWidth: 12 } }, tooltip: { callbacks: { title } } } } });

  bars('bKind', d.by.kind); bars('bSdk', d.by.sdk); bars('bCountry', d.by.country);
  bars('bApp', d.by.app); bars('bFw', d.by.fw); bars('bErr', d.by.err, 'Ninguna avería en este periodo.');
}

function load() {
  const url = custom ? `estadisticas.php?action=data&from=${encodeURIComponent(custom.from)}&to=${encodeURIComponent(custom.to)}`
                     : `estadisticas.php?action=data&period=${period}`;
  fetch(url).then(r => r.json()).then(d => { if (d.error) throw new Error(d.error); render(d); })
    .catch(() => { $('kpis').innerHTML = kpi('Estadísticas', '<span style="font-size:1rem">no disponibles ahora mismo</span>', true); });
}
load();
</script>
</body>
</html>
