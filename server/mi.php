<?php
// wttc/mi.php — Estadísticas de UNA placa, con su código de instalación (https://wttc.favala.es/mi.php#XXXX-XXXX-XXXX-XXXX).
// Código generado íntegramente con Claude (Anthropic).
// La página no lleva datos: el código va en el «#» de la dirección (el navegador no lo manda al servidor) o se escribe,
// y la página pide los datos a api/mi.php por POST. Gráficas con Chart.js servido desde /vendor/.
require_once __DIR__ . '/api/db.php';
ini_set('display_errors', '0');
wttc_public_only();
wttc_visit('mi');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mis estadísticas · WTTC</title>
<meta name="description" content="Las estadísticas de tu calefacción Webasto con WTTC: horas, encendidos, gasoil, temperaturas y averías, con tu código de instalación.">
<meta name="robots" content="noindex">
<link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<style>
  :root{
    --bg-page:#0f1117; --bg-card:#1a1d27; --bg-inner:#13151f;
    --text:#e2e8f8; --muted:#7a84a8; --border:#2e3350; --acc:#3a8ee0; --ice:#5bc0eb; --warm:#ff8a3d; --hot:#ff5d5d;
    --ok:#3ecf8e; --grid:rgba(122,132,168,.16);
  }
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg-page);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;line-height:1.45}
  .wrap{max-width:1100px;margin:0 auto;padding:20px 16px 48px}
  a{color:var(--acc)}
  h1{font-size:1.5rem;margin:6px 0 4px}
  .sub{color:var(--muted);font-size:.9rem;max-width:820px}
  .card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:16px;margin-top:16px}
  h2{font-size:.85rem;color:var(--muted);font-weight:700;margin:0 0 12px;text-transform:uppercase;letter-spacing:.05em}
  .muted{color:var(--muted);font-size:.88rem}
  /* Entrada del código */
  .gate{max-width:560px;margin:28px auto 0;text-align:center;padding:26px 20px}
  .gate .flame{font-size:2.6rem;line-height:1}
  .gate input{width:100%;margin-top:14px;background:var(--bg-inner);border:1px solid var(--border);color:var(--text);border-radius:12px;
    padding:14px;font:600 1.25rem ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.12em;text-align:center;text-transform:uppercase}
  .gate input:focus{outline:2px solid var(--acc);border-color:transparent}
  .btn{background:var(--acc);border:none;color:#fff;border-radius:10px;padding:.65rem 1.2rem;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit}
  .btn.ghost{background:var(--bg-inner);border:1px solid var(--border);color:var(--text)}
  .btn.danger{background:transparent;border:1px solid #6b2a33;color:#ff8f9a}
  .btn:disabled{opacity:.5;cursor:default}
  .err{color:#ff8f9a;min-height:1.3em;margin-top:8px;font-size:.9rem}
  label.rem{display:inline-flex;gap:6px;align-items:center;color:var(--muted);font-size:.85rem;margin-top:10px}
  /* Cabecera de la placa */
  .top{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-top:14px}
  .code{font:600 .95rem ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.08em;background:var(--bg-card);border:1px solid var(--border);
    border-radius:9px;padding:.35rem .65rem}
  .periods{display:flex;gap:.3rem;flex-wrap:wrap}
  .periods button{background:var(--bg-card);border:1px solid var(--border);color:var(--muted);border-radius:9px;padding:.35rem .7rem;font-size:.82rem;
    font-weight:700;cursor:pointer;font-family:inherit}
  .periods button.act{background:var(--acc);border-color:var(--acc);color:#fff}
  /* Cifras */
  .kpis{display:grid;grid-template-columns:1.6fr repeat(3,1fr);gap:12px;margin-top:16px}
  @media (max-width:820px){.kpis{grid-template-columns:1fr 1fr}.kpis .hero{grid-column:1/-1}}
  .kpi{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:14px 16px;position:relative;overflow:hidden}
  .kpi .l{color:var(--muted);font-size:.82rem}
  .kpi .v{font-size:1.8rem;font-weight:650;margin-top:2px;font-variant-numeric:tabular-nums}
  .kpi .s{color:var(--muted);font-size:.8rem;margin-top:2px}
  .kpi.hero{background:linear-gradient(135deg,#2a1a12 0%,#1a1d27 70%);border-color:#4a2e1f}
  .kpi.hero .v{font-size:3rem;line-height:1.1;color:#ffd2b3}
  .kpi.hero::after{content:"";position:absolute;right:-30px;top:-30px;width:140px;height:140px;border-radius:50%;
    background:radial-gradient(circle,rgba(255,138,61,.35),transparent 70%)}
  .kpi input{width:4.2em;background:var(--bg-inner);border:1px solid var(--border);color:var(--text);border-radius:6px;padding:1px 4px;font:inherit}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px}
  @media (max-width:900px){.grid2{grid-template-columns:1fr}}
  .grid2 .card{margin-top:0}
  .chartbox{position:relative;height:250px}
  .bars{display:flex;flex-direction:column;gap:8px}
  .bar{display:grid;grid-template-columns:minmax(110px,40%) 1fr auto;gap:10px;align-items:center;font-size:.88rem}
  .bl{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .bt{height:10px;background:var(--bg-inner);border-radius:5px;overflow:hidden}
  .bt i{display:block;height:100%;border-radius:0 5px 5px 0}
  .bv{font-variant-numeric:tabular-nums;color:var(--muted);min-width:2.5em;text-align:right}
  .tbl{width:100%;border-collapse:collapse;font-size:.86rem}
  .tbl th{color:var(--muted);font-weight:600;text-align:left;padding:6px 8px;border-bottom:1px solid var(--border);white-space:nowrap}
  .tbl td{padding:7px 8px;border-bottom:1px solid var(--grid);font-variant-numeric:tabular-nums;white-space:nowrap}
  .tblwrap{overflow-x:auto}
  .tag{display:inline-block;padding:1px 7px;border-radius:20px;font-size:.76rem;background:var(--bg-inner);border:1px solid var(--border)}
  .tag.ok{color:var(--ok);border-color:#1f5a40}.tag.bad{color:#ff8f9a;border-color:#6b2a33}
  .empty{text-align:center;padding:30px 10px;color:var(--muted)}
  ul.priv{margin:6px 0 0;padding-left:20px;font-size:.88rem}
  ul.priv li{margin-bottom:4px}
  [hidden]{display:none!important}
</style>
</head>
<body>
<div class="wrap">
  <a href="./" style="font-size:.85rem;text-decoration:none">← WTTC</a>
  <h1>Mis estadísticas</h1>
  <div class="sub">Todo lo que ha hecho tu calefacción: horas, encendidos, gasoil, temperaturas y averías. Solo lo ve quien tiene el código de la placa.</div>

  <!-- Entrada del código -->
  <div class="card gate" id="gate">
    <div class="flame">🔥</div>
    <h2 style="margin:10px 0 4px">Código de instalación</h2>
    <div class="muted">Está en la web de la placa y en la app: <b>Configuración → Mis estadísticas</b>. Con «Enviar el código por Telegram» te llega al móvil, listo para copiar.</div>
    <form id="gform" autocomplete="off">
      <input id="code" placeholder="XXXX-XXXX-XXXX-XXXX" maxlength="19" spellcheck="false" autocapitalize="characters" aria-label="Código de instalación">
      <div class="err" id="gerr"></div>
      <button class="btn" id="go" style="margin-top:4px">Ver mis estadísticas</button><br>
      <label class="rem"><input type="checkbox" id="remember" checked> Recordar en este navegador</label>
    </form>
  </div>

  <!-- Estadísticas -->
  <div id="main" hidden>
    <div class="top">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"><span class="code" id="shownCode"></span><span class="muted" id="seen"></span></div>
      <div class="periods" id="periods"></div>
    </div>
    <div class="kpis" id="kpis"></div>
    <div class="empty card" id="noRuns" hidden>Aún no ha llegado ningún encendido. La placa los envía a los pocos minutos de apagarse
      (si tiene internet) o la app al conectarse a ella. Las cifras de arriba son los contadores de la placa.</div>
    <div id="charts">
      <div class="grid2">
        <div class="card"><h2>Horas y gasoil por mes</h2><div class="chartbox"><canvas id="chMonth" role="img" aria-label="Horas de calefacción y litros de gasoil por mes"></canvas></div></div>
        <div class="card"><h2 id="hDay">Encendidos por día</h2><div class="chartbox"><canvas id="chDay" role="img" aria-label="Encendidos por día, según quién los pidió"></canvas></div></div>
      </div>
      <div class="grid2">
        <div class="card"><h2>A qué hora enciende</h2><div class="chartbox"><canvas id="chHour" role="img" aria-label="Encendidos según la hora del día"></canvas></div></div>
        <div class="card"><h2>Qué día de la semana</h2><div class="chartbox"><canvas id="chWday" role="img" aria-label="Encendidos según el día de la semana"></canvas></div></div>
      </div>
      <div class="grid2">
        <div class="card"><h2>Quién la enciende</h2><div id="bSrc"></div></div>
        <div class="card"><h2>Por qué se apaga</h2><div id="bEnd"></div></div>
      </div>
      <div class="grid2">
        <div class="card"><h2>Temperatura dentro: al empezar y al acabar</h2><div class="chartbox"><canvas id="chCab" role="img" aria-label="Temperatura dentro de la furgoneta al empezar y al acabar cada encendido"></canvas></div>
          <div class="muted" id="cabNote" style="margin-top:6px"></div></div>
        <div class="card"><h2>Agua del motor y batería</h2><div class="chartbox"><canvas id="chEng" role="img" aria-label="Temperatura máxima del agua y tensión mínima de la batería en cada encendido"></canvas></div></div>
      </div>
      <div class="grid2">
        <div class="card"><h2>Duración de cada encendido</h2><div class="chartbox"><canvas id="chDur" role="img" aria-label="Minutos de cada encendido"></canvas></div></div>
        <div class="card"><h2>Averías</h2><div id="bErr"></div></div>
      </div>
      <div class="card"><h2>Últimos encendidos</h2><div class="tblwrap"><table class="tbl" id="tbl"></table></div></div>
    </div>
    <div class="card">
      <h2>Tus datos</h2>
      <ul class="priv">
        <li>Los envía tu placa (o la app) solo si activaste «Enviar las estadísticas de esta placa». Se desactiva en el mismo sitio.</li>
        <li>Por cada encendido: hora, duración, gasoil estimado, temperatura dentro al empezar y al acabar, máxima del agua, batería mínima,
          quién la encendió, por qué se apagó y la avería, si la hubo. Y los totales de la placa. Nunca ubicación, nombres, redes, claves ni horarios.</li>
        <li>No se guarda tu IP. No hay ningún listado de placas: sin el código, nadie puede ver esto.</li>
        <li>El gasoil es una estimación (según la potencia a la que trabaja la Webasto), no una medida.</li>
      </ul>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
        <button class="btn ghost" id="other">Ver otro código</button>
        <button class="btn danger" id="del">Borrar mis datos del servidor</button>
      </div>
    </div>
  </div>
</div>

<script src="/vendor/chartjs/4.4.1/chart.umd.min.js"></script>
<script>
"use strict";
const $ = id => document.getElementById(id);
const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const css = v => getComputedStyle(document.documentElement).getPropertyValue(v).trim();
const nf = (n, d = 0) => Number(n).toLocaleString('es-ES', { minimumFractionDigits: d, maximumFractionDigits: d });
const store = { get: k => { try { return localStorage.getItem(k) } catch (e) { return null } },
                set: (k, v) => { try { v == null ? localStorage.removeItem(k) : localStorage.setItem(k, v) } catch (e) {} } };
// Quién la encendió (bit 7: con termostato) y por qué se apagó: como RS_* y RE_* en WTTC.ino
const SRC = ['Web de la placa', 'App', 'Programa', 'Consola', 'Hora de salida', 'Otro'];
const SRCC = ['#5bc0eb', '#3a8ee0', '#a78bfa', '#7a84a8', '#ff8a3d', '#4b5275'];
const END = ['Se acabó el tiempo', 'Apagada a mano', 'Llegó a la temperatura', 'Batería baja', 'Se apagó sola (avería)', 'Sin comunicación', 'Dentro no subía'];
const ENDC = ['#5bc0eb', '#7a84a8', '#3ecf8e', '#ffcc4d', '#ff5d5d', '#ff8f9a', '#ff8a3d'];
const WD = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const MES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
let data = null, period = '90d', charts = {};

// ---------- código ----------
const norm = s => { const c = String(s).toUpperCase().replace(/[^0-9A-Z]/g, ''); return c.length === 16 ? c.match(/.{4}/g).join('-') : null; };
$('code').addEventListener('input', e => {        // guiones solos al escribir
  const c = e.target.value.toUpperCase().replace(/[^0-9A-Z]/g, '').slice(0, 16);
  e.target.value = (c.match(/.{1,4}/g) || []).join('-');
});
async function api(body) {
  const r = await fetch('api/mi.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
  const j = await r.json().catch(() => ({}));
  if (r.status === 404) throw new Error('No hay datos con ese código. ¿Está activado «Enviar las estadísticas de esta placa»? El primer envío llega unos minutos después de activarlo, con la placa en una red con internet.');
  if (r.status === 400) throw new Error('El código son 16 letras y números, como ABCD-2345-EFGH-6789.');
  if (!r.ok) throw new Error('El servidor no responde ahora mismo. Prueba en un rato.');
  return j;
}
async function openCode(code) {
  $('go').disabled = true; $('gerr').textContent = '';
  try {
    data = await api({ c: code });
    if ($('remember').checked) store.set('wttc_iid', data.iid); else store.set('wttc_iid', null);
    history.replaceState(null, '', '#' + data.iid);
    $('gate').hidden = true; $('main').hidden = false;
    $('shownCode').textContent = data.iid;
    render();
  } catch (e) { $('gerr').textContent = e.message; $('gate').hidden = false; $('main').hidden = true; }
  $('go').disabled = false;
}
$('gform').onsubmit = e => { e.preventDefault(); const c = norm($('code').value); if (!c) { $('gerr').textContent = 'El código son 16 letras y números, como ABCD-2345-EFGH-6789.'; return; } openCode(c); };
$('other').onclick = () => { data = null; store.set('wttc_iid', null); history.replaceState(null, '', location.pathname); $('code').value = ''; $('main').hidden = true; $('gate').hidden = false; $('code').focus(); };
$('del').onclick = async () => {
  if (!data || !confirm('¿Borrar del servidor todas las estadísticas de esta placa? No se puede deshacer.\n\nSi la placa sigue enviando, volverá a empezar de cero; para que no envíe más, desactívalo en su Configuración.')) return;
  try { await api({ c: data.iid, borrar: true }); store.set('wttc_iid', null); alert('Borrado.'); $('other').onclick(); } catch (e) { alert(e.message); }
};

// ---------- periodo ----------
const PERIODS = [['30d', '30 días', 30], ['90d', '90 días', 90], ['1y', '1 año', 365], ['all', 'Todo', 0]];
$('periods').innerHTML = PERIODS.map(([k, l]) => `<button data-p="${k}">${l}</button>`).join('');
$('periods').onclick = e => { const p = e.target.dataset.p; if (p) { period = p; store.set('wttc_period', p); render(); } };
period = store.get('wttc_period') || period;

// ---------- dibujo ----------
Chart.defaults.color = css('--muted');
Chart.defaults.borderColor = css('--grid');
Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.plugins.legend.labels.boxWidth = 12;
function chart(id, cfg) {
  if (charts[id]) charts[id].destroy();
  cfg.options = Object.assign({ responsive: true, maintainAspectRatio: false, animation: { duration: 500 },
    interaction: { mode: 'index', intersect: false } }, cfg.options || {});
  charts[id] = new Chart($(id), cfg);
}
function bars(el, rows, colors, empty) {
  rows = rows.filter(r => r[1] > 0);
  if (!rows.length) { $(el).innerHTML = `<p class="muted">${empty || 'Sin datos en este periodo.'}</p>`; return; }
  const max = Math.max(...rows.map(r => r[1])), sum = rows.reduce((a, r) => a + r[1], 0);
  $(el).innerHTML = '<div class="bars">' + rows.map(([l, n, c]) =>
    `<div class="bar"><span class="bl" title="${esc(l)}">${esc(l)}</span><span class="bt"><i style="width:${Math.max(3, n / max * 100)}%;background:${c}"></i></span>` +
    `<span class="bv">${nf(n)} · ${Math.round(n / sum * 100)} %</span></div>`).join('') + '</div>';
}
const hm = s => { const m = Math.round(s / 60); return m < 60 ? m + ' min' : Math.floor(m / 60) + ' h ' + String(m % 60).padStart(2, '0') + ' min'; };
const fdate = t => { const d = new Date(t * 1000); return d.getDate() + ' ' + MES[d.getMonth()] + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'); };
const dayKey = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

function render() {
  document.querySelectorAll('#periods button').forEach(b => b.classList.toggle('act', b.dataset.p === period));
  const B = data.board, all = data.runs.map(r => ({ seq: r[0], t0: r[1], dur: r[2], ml: r[3], cab0: r[4], cab1: r[5],
    cmax: r[6] ? r[6] - 50 : null, vmin: r[7] ? r[7] / 10 : null, src: r[8] & 0x7f, th: !!(r[8] & 0x80), end: r[9], err: r[10] }));
  const days = (PERIODS.find(p => p[0] === period) || PERIODS[1])[2];
  const since = days ? Date.now() / 1000 - days * 86400 : 0;
  const R = all.filter(r => !days || (r.t0 && r.t0 >= since));      // sin hora: solo cuentan en «Todo»
  const T = R.filter(r => r.t0);
  $('seen').textContent = 'Firmware ' + (B.fw || '?') + ' · último envío ' + B.last.split('-').reverse().join('/');

  // Cifras
  const sec = R.reduce((a, r) => a + r.dur, 0), lit = R.reduce((a, r) => a + r.ml, 0) / 1000;
  const price = parseFloat(store.get('wttc_price')) || 1.45;
  const withCab = R.filter(r => r.cab0 > -128 && r.cab1 > -128 && r.dur >= 300);
  const gain = withCab.length ? withCab.reduce((a, r) => a + (r.cab1 - r.cab0), 0) / withCab.length : null;
  const reached = R.filter(r => r.th).length ? R.filter(r => r.th && r.end === 2).length / R.filter(r => r.th).length : null;
  const per = period === 'all' ? 'desde que se envían' : 'en ' + PERIODS.find(p => p[0] === period)[1];
  const k = (l, v, s, cls) => `<div class="kpi${cls ? ' ' + cls : ''}"><div class="l">${l}</div><div class="v">${v}</div>${s ? `<div class="s">${s}</div>` : ''}</div>`;
  $('kpis').innerHTML =
    k('Calefacción ' + per, sec >= 3600 ? nf(sec / 3600, 1) + ' h' : Math.round(sec / 60) + ' min',
      'En total la placa lleva ' + nf(B.hsec / 3600, 1) + ' h y ' + nf(B.nruns) + ' encendidos', 'hero') +
    k('Encendidos', nf(R.length), R.length ? 'media de ' + hm(sec / R.length) : '') +
    k('Gasoil estimado', nf(lit, 2) + ' L', `≈ ${nf(lit * price, 2)} € a <input id="price" type="number" step="0.01" min="0.5" max="5" value="${price}"> €/L`) +
    k('Dentro sube', gain == null ? '—' : (gain >= 0 ? '+' : '') + nf(gain, 1) + ' °C', gain == null ? 'hace falta el termómetro' :
      'de media por encendido' + (reached != null ? ' · llega al objetivo el ' + Math.round(reached * 100) + ' %' : ''));
  $('price').onchange = e => { store.set('wttc_price', e.target.value); render(); };

  $('noRuns').hidden = all.length > 0;
  $('charts').hidden = all.length === 0;
  if (!all.length) return;

  // Por mes: horas (barras) y litros (línea)
  const months = {};
  for (const r of T) { const d = new Date(r.t0 * 1000), m = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
    (months[m] = months[m] || { h: 0, l: 0 }); months[m].h += r.dur / 3600; months[m].l += r.ml / 1000; }
  const mk = Object.keys(months).sort();
  chart('chMonth', { data: { labels: mk.map(m => MES[+m.slice(5) - 1] + ' ' + m.slice(2, 4)), datasets: [
    { type: 'bar', label: 'Horas', data: mk.map(m => +months[m].h.toFixed(2)), backgroundColor: css('--warm'), borderRadius: 6, yAxisID: 'y' },
    { type: 'line', label: 'Litros', data: mk.map(m => +months[m].l.toFixed(2)), borderColor: css('--ice'), backgroundColor: css('--ice'), tension: .35, yAxisID: 'y1' }] },
    options: { scales: { y: { beginAtZero: true, title: { display: true, text: 'h' } }, y1: { beginAtZero: true, position: 'right', grid: { display: false }, title: { display: true, text: 'L' } } } } });

  // Por día, apilado por quién la enciende
  const nd = days || Math.min(365, Math.max(30, Math.ceil((Date.now() / 1000 - Math.min(...T.map(r => r.t0), Date.now() / 1000)) / 86400) + 1));
  const axis = []; for (let i = nd - 1; i >= 0; i--) axis.push(dayKey(new Date(Date.now() - i * 86400000)));
  const idx = Object.fromEntries(axis.map((d, i) => [d, i]));
  const per_src = SRC.map(() => axis.map(() => 0));
  for (const r of T) { const i = idx[dayKey(new Date(r.t0 * 1000))]; if (i != null) per_src[Math.min(r.src, 5)][i]++; }
  $('hDay').textContent = 'Encendidos por día';
  chart('chDay', { type: 'bar', data: { labels: axis.map(d => +d.slice(8) + ' ' + MES[+d.slice(5, 7) - 1]),
    datasets: SRC.map((l, s) => ({ label: l, data: per_src[s], backgroundColor: SRCC[s], borderRadius: 3, stack: 'a' })).filter(d => d.data.some(v => v)) },
    options: { scales: { x: { stacked: true, ticks: { maxTicksLimit: 10 } }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } } } });

  // Hora del día y día de la semana
  const hours = Array(24).fill(0), wd = Array(7).fill(0);
  for (const r of T) { const d = new Date(r.t0 * 1000); hours[d.getHours()]++; wd[(d.getDay() + 6) % 7]++; }
  const hmax = Math.max(...hours);
  chart('chHour', { type: 'bar', data: { labels: hours.map((_, h) => h + 'h'), datasets: [{ label: 'Encendidos', data: hours,
    backgroundColor: hours.map(v => v === hmax && v ? css('--warm') : css('--acc')), borderRadius: 4 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
  chart('chWday', { type: 'bar', data: { labels: WD, datasets: [{ label: 'Encendidos', data: wd, backgroundColor: wd.map((_, i) => i >= 5 ? css('--warm') : css('--acc')), borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });

  // Quién y por qué
  const cs = SRC.map(() => 0), ce = END.map(() => 0); let thN = 0;
  for (const r of R) { cs[Math.min(r.src, 5)]++; if (r.end < END.length) ce[r.end]++; if (r.th) thN++; }
  bars('bSrc', SRC.map((l, i) => [l, cs[i], SRCC[i]]).sort((a, b) => b[1] - a[1]));
  if (thN) $('bSrc').insertAdjacentHTML('beforeend', `<p class="muted" style="margin:10px 0 0">${nf(thN)} con «calentar hasta» (termostato).</p>`);
  bars('bEnd', END.map((l, i) => [l, ce[i], ENDC[i]]).sort((a, b) => b[1] - a[1]));

  // Temperaturas por encendido (los últimos 60 del periodo)
  const L = R.slice(-60), lab = L.map(r => r.t0 ? fdate(r.t0) : '#' + r.seq);
  const cabOk = L.some(r => r.cab0 > -128);
  chart('chCab', { type: 'line', data: { labels: lab, datasets: [
    { label: 'Al empezar', data: L.map(r => r.cab0 > -128 ? r.cab0 : null), borderColor: css('--ice'), backgroundColor: 'rgba(91,192,235,.15)', fill: false, tension: .3, spanGaps: true, pointRadius: 2 },
    { label: 'Al acabar', data: L.map(r => r.cab1 > -128 ? r.cab1 : null), borderColor: css('--warm'), backgroundColor: 'rgba(255,138,61,.18)', fill: '-1', tension: .3, spanGaps: true, pointRadius: 2 }] },
    options: { scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { title: { display: true, text: '°C' } } } } });
  $('cabNote').textContent = cabOk ? 'La zona naranja es lo que ha subido dentro en cada encendido.' : 'Sin termómetro interior (SHT31 o AHT20) no hay temperatura de dentro.';
  chart('chEng', { type: 'line', data: { labels: lab, datasets: [
    { label: 'Agua máx. (°C)', data: L.map(r => r.cmax), borderColor: css('--hot'), backgroundColor: css('--hot'), tension: .3, spanGaps: true, pointRadius: 2, yAxisID: 'y' },
    { label: 'Batería mín. (V)', data: L.map(r => r.vmin), borderColor: '#ffcc4d', backgroundColor: '#ffcc4d', tension: .3, spanGaps: true, pointRadius: 2, yAxisID: 'y1' }] },
    options: { scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { title: { display: true, text: '°C' } },
      y1: { position: 'right', grid: { display: false }, suggestedMin: 11, suggestedMax: 13.5, title: { display: true, text: 'V' } } } } });
  chart('chDur', { type: 'bar', data: { labels: lab, datasets: [{ label: 'Minutos', data: L.map(r => +(r.dur / 60).toFixed(1)),
    backgroundColor: L.map(r => ENDC[r.end] || css('--acc')), borderRadius: 3 }] },
    options: { plugins: { legend: { display: false }, tooltip: { callbacks: { afterLabel: c => END[L[c.dataIndex].end] || '' } } },
      scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { beginAtZero: true, title: { display: true, text: 'min' } } } } });

  // Averías
  const ef = {};
  for (const r of R) if (r.err) { const c = r.err.toString(16).toUpperCase().padStart(2, '0'); ef[c] = (ef[c] || 0) + 1; }
  bars('bErr', Object.entries(ef).map(([c, n]) => ['0x' + c + (data.errnames[c] ? ' · ' + data.errnames[c] : ''), n, css('--hot')]).sort((a, b) => b[1] - a[1]),
    null, '✅ Ninguna avería en este periodo.');

  // Tabla
  const last = R.slice(-25).reverse();
  $('tbl').innerHTML = '<tr><th>Cuándo</th><th>Duración</th><th>Gasoil</th><th>Dentro</th><th>Agua</th><th>Quién</th><th>Final</th></tr>' +
    last.map(r => `<tr><td>${r.t0 ? fdate(r.t0) : 'sin hora'}</td><td>${hm(r.dur)}</td><td>${nf(r.ml / 1000, 2)} L</td>` +
      `<td>${r.cab0 > -128 ? r.cab0 + ' → ' + (r.cab1 > -128 ? r.cab1 : '?') + ' °C' : '—'}</td><td>${r.cmax != null ? r.cmax + ' °C' : '—'}</td>` +
      `<td>${SRC[Math.min(r.src, 5)]}${r.th ? ' · termostato' : ''}</td>` +
      `<td><span class="tag ${r.end === 4 || r.end === 5 ? 'bad' : r.end === 2 ? 'ok' : ''}">${END[r.end] || '?'}${r.err ? ' 0x' + r.err.toString(16).toUpperCase().padStart(2, '0') : ''}</span></td></tr>`).join('');
}

// Arranque: el código del «#» de la dirección, o el recordado
const start = norm(decodeURIComponent(location.hash.slice(1))) || norm(store.get('wttc_iid') || '');
if (start) { $('code').value = start; openCode(start); } else $('code').focus();
window.addEventListener('hashchange', () => { const c = norm(decodeURIComponent(location.hash.slice(1))); if (c && (!data || data.iid !== c)) openCode(c); });
</script>
</body>
</html>
