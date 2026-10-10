<?php
// wttc/mi.php — Estadísticas de UNA placa, con su código de instalación (https://wttc.favala.es/mi.php#XXXX-XXXX-XXXX-XXXX).
// Código generado íntegramente con Claude (Anthropic).
// Selector de periodo y fechas concretas: el común del portal (TCharts.rangePicker de /charts.js).
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
<!-- iPhone: icono opaco de 180 px (iOS redondea las esquinas; con transparencia puede no salir) -->
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<!-- Instalable como app (PWA): Android «Instalar app» / iPhone Compartir → «Añadir a pantalla de inicio» -->
<link rel="manifest" href="/mi-manifest.json">
<meta name="theme-color" content="#0f1117">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="WTTC">
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
  .rangebox{display:flex;gap:.35rem;align-items:center;flex-wrap:wrap}
  .rangebox input[type=date]{background:var(--bg-card);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:.3rem .5rem;font-size:.8rem;font-family:inherit;color-scheme:dark}
  .rangebox button{background:var(--acc);border:none;color:#fff;border-radius:8px;padding:.36rem .8rem;font-size:.8rem;font-weight:800;cursor:pointer;font-family:inherit}
  .rangebox.act input[type=date]{border-color:var(--acc)}
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
  /* Récords: baldosas */
  .recs{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
  .rec{background:var(--bg-inner);border:1px solid var(--border);border-radius:12px;padding:10px 12px}
  .rec .i{font-size:1.2rem}.rec .v{font-size:1.25rem;font-weight:650;margin-top:2px;font-variant-numeric:tabular-nums}
  .rec .l{color:var(--muted);font-size:.78rem;margin-top:2px}
  /* Calendario de uso (horas por día), una columna por semana */
  .cal{display:grid;grid-template-rows:repeat(7,13px);grid-auto-flow:column;grid-auto-columns:13px;gap:3px;overflow-x:auto;padding-bottom:4px}
  .cal i{border-radius:3px;background:var(--bg-inner)}
  .calwrap{display:flex;gap:6px}.calwrap .wd{display:grid;grid-template-rows:repeat(7,13px);gap:3px;font-size:.66rem;color:var(--muted)}
  .legend{display:flex;gap:4px;align-items:center;font-size:.75rem;color:var(--muted);margin-top:8px}
  .legend i{width:12px;height:12px;border-radius:3px;display:inline-block}
  /* Mapa día × hora */
  .hm{display:grid;grid-template-columns:34px repeat(24,1fr);gap:2px;font-size:.66rem;color:var(--muted)}
  .hm i{aspect-ratio:1;border-radius:3px;background:var(--bg-inner)}
  .hm span{display:flex;align-items:center}
  .cmp{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px}
  .cmp .row{background:var(--bg-inner);border:1px solid var(--border);border-radius:12px;padding:12px}
  .cmp .row b{font-size:1.2rem}.up{color:var(--warm)}.down{color:var(--ice)}
  /* Consejos */
  .tips{list-style:none;margin:0;padding:0;display:grid;gap:10px}
  .tips li{display:grid;grid-template-columns:28px 1fr;gap:8px;background:var(--bg-inner);border:1px solid var(--border);border-radius:12px;padding:10px 12px;font-size:.92rem}
  .tips li .i{font-size:1.2rem;line-height:1.3}
  .tips li b{color:#ffd2b3}
  details.log{background:var(--bg-inner);border:1px solid var(--border);border-radius:12px;padding:10px 12px;margin-top:10px}
  details.log summary{cursor:pointer;font-weight:600}
  details.log pre{white-space:pre-wrap;word-break:break-word;font-size:.78rem;max-height:420px;overflow:auto;background:var(--bg-page);padding:10px;border-radius:8px}
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
      <button class="btn" id="go" style="margin-top:4px">Ver mis estadísticas</button>
      <button class="btn ghost" id="demo" type="button" style="margin-top:4px">Ver un ejemplo</button><br>
      <label class="rem"><input type="checkbox" id="remember" checked> Recordar en este navegador</label>
    </form>
  </div>

  <!-- Estadísticas -->
  <div id="main" hidden>
    <div class="top">
      <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"><span class="code" id="shownCode"></span><span class="muted" id="seen"></span></div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <div class="periods" id="periods"></div>
        <div class="rangebox">
          <input type="date" id="rfrom" aria-label="Desde"><span class="muted">→</span>
          <input type="date" id="rto" aria-label="Hasta"><button id="rapply">Aplicar</button>
        </div>
      </div>
    </div>
    <div class="kpis" id="kpis"></div>
    <div class="empty card" id="noRuns" hidden>Aún no ha llegado ningún encendido. La placa los envía a los pocos minutos de apagarse
      (si tiene internet) o la app al conectarse a ella. Las cifras de arriba son los contadores de la placa.</div>
    <div id="charts">
      <div class="card" id="tipsCard"><h2>Consejos para ti</h2><ul class="tips" id="tips"></ul></div>
      <div class="card"><h2>Récords</h2><div class="recs" id="recs"></div></div>
      <div class="card"><h2 id="hCal">Calendario de uso</h2><div class="calwrap"><div class="wd"><span></span><span>L</span><span></span><span>X</span><span></span><span>V</span><span></span></div><div class="cal" id="cal"></div></div>
        <div class="legend">menos <i style="background:var(--bg-inner)"></i><i style="background:#5a3418"></i><i style="background:#9a5520"></i><i style="background:#d9772c"></i><i style="background:#ffa25a"></i> más horas</div>
        <div class="muted" id="streak" style="margin-top:8px"></div></div>
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
        <div class="card"><h2>Humedad dentro: al empezar y al acabar</h2><div class="chartbox"><canvas id="chHum" role="img" aria-label="Humedad dentro de la furgoneta al empezar y al acabar cada encendido"></canvas></div>
          <div class="muted" id="humNote" style="margin-top:6px"></div></div>
      </div>
      <div class="grid2">
        <div class="card"><h2>Agua del motor</h2><div class="chartbox"><canvas id="chEng" role="img" aria-label="Temperatura del agua del motor al empezar y máxima en cada encendido"></canvas></div></div>
        <div class="card"><h2>Batería</h2><div class="chartbox"><canvas id="chBatt" role="img" aria-label="Tensión de la batería al empezar y mínima en cada encendido"></canvas></div>
          <div class="muted" id="battNote" style="margin-top:6px"></div></div>
      </div>
      <div class="grid2">
        <div class="card"><h2>Día y hora</h2><div class="hm" id="hm"></div><div class="muted" style="margin-top:8px">Cuántas veces enciende cada día de la semana a cada hora.</div></div>
        <div class="card"><h2>Cuánto tarda en calentar</h2><div class="chartbox"><canvas id="chSpeed" role="img" aria-label="Grados que sube dentro cada 10 minutos según la temperatura de dentro al empezar"></canvas></div>
          <div class="muted" id="speedNote" style="margin-top:6px"></div></div>
      </div>
      <div class="grid2">
        <div class="card"><h2>Coste y consumo por mes</h2><div class="chartbox"><canvas id="chCost" role="img" aria-label="Coste estimado del gasoil y litros por hora en cada mes"></canvas></div></div>
        <div class="card"><h2>Termostato («calentar hasta»)</h2><div id="thermo"></div></div>
      </div>
      <div class="card" id="cmpCard"><h2>Comparado con las demás placas</h2><div id="cmp"></div></div>
      <div class="grid2">
        <div class="card"><h2>Duración de cada encendido</h2><div class="chartbox"><canvas id="chDur" role="img" aria-label="Minutos de cada encendido"></canvas></div></div>
        <div class="card"><h2>Averías</h2><div id="bErr"></div></div>
      </div>
      <div class="card"><h2>Últimos encendidos</h2><div class="tblwrap"><table class="tbl" id="tbl"></table></div>
        <div style="margin-top:12px"><button class="btn ghost" id="csv" type="button">Descargar todos (CSV, para Excel)</button></div></div>
    </div>
    <div class="card" id="logsCard" hidden><h2>Registros enviados desde la placa</h2>
      <div class="muted">Los que se mandan desde Diagnóstico → «Enviar el registro» (modo diagnóstico). Se guardan los 10 últimos.</div>
      <div id="logs"></div></div>
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
<script src="/charts.js"></script>
<script>
"use strict";
const $ = id => document.getElementById(id);
const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const css = v => getComputedStyle(document.documentElement).getPropertyValue(v).trim();
const nf = (n, d = 0) => Number(n).toLocaleString('es-ES', { minimumFractionDigits: d, maximumFractionDigits: d });
const store = { get: k => { try { return localStorage.getItem(k) } catch (e) { return null } },
                set: (k, v) => { try { v == null ? localStorage.removeItem(k) : localStorage.setItem(k, v) } catch (e) {} } };
// Quién la encendió (bit 7: con termostato) y por qué se apagó: como RS_* y RE_* en WTTC.ino
const SRC = ['Web de la placa', 'App', 'Programa', 'Consola', 'Hora de salida', 'Otro', 'Botón'];
const SRCC = ['#5bc0eb', '#3a8ee0', '#a78bfa', '#7a84a8', '#ff8a3d', '#4b5275', '#3ecf8e'];
// El último (7) no es un apagado: el arranque falló porque la Webasto no contestó por el W-Bus (firmware 0.2.19+)
const END = ['Se acabó el tiempo', 'Apagada a mano', 'Llegó a la temperatura', 'Batería baja', 'Se apagó sola (avería)', 'Sin comunicación', 'Dentro no subía', 'No respondió (W-Bus)'];
const ENDC = ['#5bc0eb', '#7a84a8', '#3ecf8e', '#ffcc4d', '#ff5d5d', '#ff8f9a', '#ff8a3d', '#c2185b'];
const NOWBUS = 7;
const WD = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
const MES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
let data = null, period = '90d', custom = null, charts = {};

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
    $('del').hidden = false;
    render();
  } catch (e) { $('gerr').textContent = e.message; $('gate').hidden = false; $('main').hidden = true; }
  $('go').disabled = false;
}
$('gform').onsubmit = e => { e.preventDefault(); const c = norm($('code').value); if (!c) { $('gerr').textContent = 'El código son 16 letras y números, como ABCD-2345-EFGH-6789.'; return; } openCode(c); };
// Ejemplo con datos inventados (una furgoneta en otoño): para ver cómo queda sin tener placa
$('demo').onclick = () => {
  let seed = 7; const rnd = () => (seed = (seed * 16807) % 2147483647) / 2147483647;
  const now = Math.floor(Date.now() / 1000), runs = [];
  for (let s = 1; s <= 70; s++) {
    const day = new Date((now - (71 - s) * 86400 * 1.3) * 1000); day.setHours([6, 7, 7, 7, 8, 18, 19, 20][Math.floor(rnd() * 8)], Math.floor(rnd() * 60), 0, 0);
    const dur = Math.round(15 + rnd() * 45) * 60, c0 = Math.round(-3 + rnd() * 12), th = rnd() < .55;
    const src = [0, 1, 1, 2, 2, 4][Math.floor(rnd() * 6)];
    let end = th ? (rnd() < .9 ? 2 : 6) : [0, 0, 1][Math.floor(rnd() * 3)], err = 0;
    if (s === 41) { end = 4; err = 3; }
    if (s === 55 || s === 56) { runs.push([s, Math.floor(day / 1000) - 120, 0, 0, c0, c0, 0, 124, src, NOWBUS, 0, 255, 255, 0, 255, 0, 124, 255]); continue; }
    const h0 = Math.round(65 + rnd() * 25), v0 = Math.round(124 + rnd() * 6);
    runs.push([s, Math.floor(day / 1000), dur, Math.round(dur / 3600 * (330 + rnd() * 60)), c0, c0 + Math.round(5 + rnd() * 10),
      Math.round(55 + rnd() * 25) + 50, v0 - Math.round(2 + rnd() * 5), src | (th ? 0x80 : 0), end, err,
      h0, h0 - Math.round(15 + rnd() * 25), th ? [18, 20, 20, 21, 22][Math.floor(rnd() * 5)] : 0, th ? Math.round(dur / 60 * (.6 + rnd() * .35)) : 255,
      Math.round(c0 + rnd() * 6) + 50, v0, Math.round(140 + rnd() * 60)]);
  }
  data = { iid: 'EJEMPLO', demo: true, board: { fw: '0.2.16', last: new Date().toISOString().slice(0, 10), gas: 9.8, hsec: 160000, nruns: 70, sens: 1 },
    runs, days: [], errnames: { '03': 'fallo de llama' } };
  $('del').hidden = true; $('gate').hidden = true; $('main').hidden = false; $('shownCode').textContent = 'EJEMPLO · datos inventados'; render();
};
// Descarga de todos los encendidos en CSV (con «;» y coma decimal: se abre bien en Excel en español)
$('csv').onclick = () => {
  if (!data) return;
  const n2 = v => String(v).replace('.', ',');
  const rows = [['n', 'fecha', 'hora', 'minutos', 'gasoil_l', 'dentro_inicio_c', 'dentro_fin_c', 'agua_max_c', 'bateria_min_v', 'quien', 'termostato', 'final', 'averia',
                 'humedad_inicio', 'humedad_fin', 'objetivo_c', 'min_hasta_objetivo', 'agua_inicio_c', 'bateria_inicio_v', 'potencia_w']];
  const opt = (v, f) => v == null || v === 255 ? '' : f ? f(v) : v;
  for (const r of data.runs) {
    const d = r[1] ? new Date(r[1] * 1000) : null;
    rows.push([r[0], d ? dayKey(d) : '', d ? String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') : '',
      n2((r[2] / 60).toFixed(1)), n2((r[3] / 1000).toFixed(3)), r[4] > -128 ? r[4] : '', r[5] > -128 ? r[5] : '', r[6] ? r[6] - 50 : '',
      r[7] ? n2(r[7] / 10) : '', SRC[Math.min(r[8] & 0x7f, SRC.length - 1)], r[8] & 0x80 ? 'sí' : 'no', END[r[9]] || '', r[10] ? '0x' + r[10].toString(16).toUpperCase().padStart(2, '0') : '',
      opt(r[11]), opt(r[12]), r[13] || '', opt(r[14]), r[15] ? r[15] - 50 : '', r[16] ? n2(r[16] / 10) : '', opt(r[17], v => v * 25)]);
  }
  const blob = new Blob(['\ufeff' + rows.map(r => r.join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8' });
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'wttc-' + (data.demo ? 'ejemplo' : data.iid) + '.csv'; a.click();
  setTimeout(() => URL.revokeObjectURL(a.href), 1000);
};
$('other').onclick = () => { data = null; store.set('wttc_iid', null); history.replaceState(null, '', location.pathname); $('code').value = ''; $('main').hidden = true; $('gate').hidden = false; $('code').focus(); };
$('del').onclick = async () => {
  if (data && data.demo) return;
  if (!data || !confirm('¿Borrar del servidor todas las estadísticas de esta placa? No se puede deshacer.\n\nSi la placa sigue enviando, volverá a empezar de cero; para que no envíe más, desactívalo en su Configuración.')) return;
  try { await api({ c: data.iid, borrar: true }); store.set('wttc_iid', null); alert('Borrado.'); $('other').onclick(); } catch (e) { alert(e.message); }
};

// ---------- periodo (selector común del portal, con fechas concretas) ----------
const PERIODS = [['30d', '30 días', 30], ['90d', '90 días', 90], ['1y', '1 año', 365], ['all', 'Todo', 0]];
period = PERIODS.some(p => p[0] === store.get('wttc_period')) ? store.get('wttc_period') : period;
TCharts.rangePicker({
  periods: PERIODS.map(p => [p[0], p[1]]),
  initialPeriod: period,
  onChange: (p, c) => { period = p; custom = c; if (!c) store.set('wttc_period', p); if (data) render(); },
  onError: m => alert(m),
});

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
  const nd = v => v == null || v === 255 ? null : v;   // 255 = sin dato (como lo manda la placa)
  const B = data.board, every = data.runs.map(r => ({ seq: r[0], t0: r[1], dur: r[2], ml: r[3], cab0: r[4], cab1: r[5],
    cmax: r[6] ? r[6] - 50 : null, vmin: r[7] ? r[7] / 10 : null, src: r[8] & 0x7f, th: !!(r[8] & 0x80), end: r[9], err: r[10],
    // Desde el firmware 0.2.17 (null = sin dato o placa más antigua)
    hum0: nd(r[11]), hum1: nd(r[12]), tgt: r[13] || null, treach: nd(r[14]),
    c0: r[15] ? r[15] - 50 : null, v0: r[16] ? r[16] / 10 : null, pw: nd(r[17]) != null ? r[17] * 25 : null }));
  // Rango: fechas concretas (de 00:00 a 23:59) o los últimos N días; sin hora, solo cuentan en «Todo»
  let since = 0, until = Infinity, days = 0;
  if (custom) { since = new Date(custom.from + 'T00:00').getTime() / 1000; until = new Date(custom.to + 'T23:59:59').getTime() / 1000;
    days = Math.max(1, Math.round((until - since) / 86400)); }
  else { days = (PERIODS.find(p => p[0] === period) || PERIODS[1])[2]; since = days ? Date.now() / 1000 - days * 86400 : 0; }
  const inRange = r => !(custom || days) || (r.t0 && r.t0 >= since && r.t0 <= until);
  // Los arranques fallidos (la Webasto no contestó) van aparte: no son encendidos y no deben bajar las medias
  const all = every.filter(r => r.end !== NOWBUS), F = every.filter(r => r.end === NOWBUS && inRange(r));
  const R = all.filter(inRange);
  const T = R.filter(r => r.t0);
  $('seen').textContent = 'Firmware ' + (B.fw || '?') + ' · último envío ' + B.last.split('-').reverse().join('/');

  // Cifras
  const sec = R.reduce((a, r) => a + r.dur, 0), lit = R.reduce((a, r) => a + r.ml, 0) / 1000;
  const price = parseFloat(store.get('wttc_price')) || 1.45;
  const withCab = R.filter(r => r.cab0 > -128 && r.cab1 > -128 && r.dur >= 300);
  const gain = withCab.length ? withCab.reduce((a, r) => a + (r.cab1 - r.cab0), 0) / withCab.length : null;
  const reached = R.filter(r => r.th).length ? R.filter(r => r.th && r.end === 2).length / R.filter(r => r.th).length : null;
  const fd = d => d.split('-').reverse().join('/');
  const per = custom ? 'del ' + fd(custom.from) + ' al ' + fd(custom.to) : period === 'all' ? 'desde que se envían' : 'en ' + PERIODS.find(p => p[0] === period)[1];
  // Mismo número de días justo antes: «↑ 12 % que los 30 días anteriores» (no en «Todo»)
  const span = custom || days ? (until === Infinity ? Date.now() / 1000 : until) - since : 0;
  const P = span ? all.filter(r => r.t0 && r.t0 >= since - span && r.t0 < since) : [];
  const vs = (now, before) => !span || !before ? '' : (() => { const d = Math.round((now - before) / before * 100);
    return ` · <span class="${d > 0 ? 'up' : 'down'}">${d > 0 ? '↑' : d < 0 ? '↓' : '='} ${Math.abs(d)} %</span> que el periodo anterior`; })();
  const sumP = P.reduce((a, r) => a + r.dur, 0), litP = P.reduce((a, r) => a + r.ml, 0) / 1000;
  const k = (l, v, s, cls) => `<div class="kpi${cls ? ' ' + cls : ''}"><div class="l">${l}</div><div class="v">${v}</div>${s ? `<div class="s">${s}</div>` : ''}</div>`;
  $('kpis').innerHTML =
    k('Calefacción ' + per, sec >= 3600 ? nf(sec / 3600, 1) + ' h' : Math.round(sec / 60) + ' min',
      'En total la placa lleva ' + nf(B.hsec / 3600, 1) + ' h y ' + nf(B.nruns) + ' encendidos' + vs(sec, sumP), 'hero') +
    k('Encendidos', nf(R.length), (R.length ? 'media de ' + hm(sec / R.length) : '') + vs(R.length, P.length) +
      (F.length ? ` · <span class="up">${F.length} sin respuesta de la Webasto</span>` : '')) +
    k('Gasoil estimado', nf(lit, 2) + ' L', `≈ ${nf(lit * price, 2)} € a <input id="price" type="number" step="0.01" min="0.5" max="5" value="${price}"> €/L` + vs(lit, litP)) +
    k('Dentro sube', gain == null ? '—' : (gain >= 0 ? '+' : '') + nf(gain, 1) + ' °C', gain == null ? 'hace falta el termómetro' :
      'de media por encendido' + (reached != null ? ' · llega al objetivo el ' + Math.round(reached * 100) + ' %' : ''));
  $('price').onchange = e => { store.set('wttc_price', e.target.value); render(); };

  // Registros enviados desde la placa (Diagnóstico → «Enviar el registro»). Hasta la 0.3.1 la placa los mandaba en una
  // sola línea (cambiaba los saltos por espacios): se vuelven a partir antes de cada fecha del registro («10/10 08:08  »,
  // «--/-- --:--  »), de cada separador de arranque y de cada trama del W-Bus («-123 s  TX …»)
  const lines = t => !t || t.includes('\n') ? (t || '') : t
    .replace(/ (?=(\d\d\/\d\d \d\d:\d\d|--\/-- --:--)  )/g, '\n')
    .replace(/ ?(· (?:· ){10,}·?) ?/g, '\n$1\n')
    .replace(/ (?=-\d+ s  TX )/g, '\n')
    .replace(/\n{2,}/g, '\n');
  const LG = data.logs || [];
  $('logsCard').hidden = !LG.length;
  $('logs').innerHTML = LG.map((g, i) => { const d = g.d || {};
    return `<details class="log"${i ? '' : ' open'}><summary>${esc(g.at.replace(' ', ' · '))} UTC · firmware ${esc(g.fw || '?')}</summary>` +
      `<p class="muted">Encendida desde hacía ${d.up != null ? hm(d.up) : '?'} · arranque: ${esc(d.rr || '?')} · memoria ${d.heap && d.heap[0] != null ? Math.round(d.heap[0] / 1024) + ' KB (mínima ' + Math.round(d.heap[1] / 1024) + ' KB)' : '?'}` +
      (d.heap && d.heap[2] != null ? ' · mayor bloque ' + Math.round(d.heap[2] / 1024) + ' KB' : '') +
      (d.heap && d.heap[3] != null ? ' · PSRAM ' + Math.round(d.heap[3] / 1024) + ' KB' + (d.tlsps ? ' (con las conexiones)' : '') : '') +
      `${d.rssi ? ' · Wi-Fi ' + d.rssi + ' dBm' : ''}</p><pre>${esc(lines(d.log))}</pre>` +
      (d.wbus ? `<p class="muted" style="margin:8px 0 4px">Tramas del W-Bus</p><pre>${esc(lines(d.wbus))}</pre>` : '') + '</details>'; }).join('');
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

  // Encendidos en el tiempo, apilados por quién la enciende. Agrupación según el rango (norma «Charts UI»):
  // por días hasta 120 días, por semanas (lunes) hasta 2 años y por meses a partir de ahí. «Todo» = desde el primero
  const end = custom ? until * 1000 : Date.now();
  const first = custom ? since * 1000 : days ? end - days * 86400000 : Math.min(...T.map(r => r.t0 * 1000), end);
  const spanDays = Math.max(1, Math.ceil((end - first) / 86400000));
  const unit = spanDays <= 120 ? 'day' : spanDays <= 730 ? 'week' : 'month';
  const keyOf = t => { const d = new Date(t); d.setHours(12, 0, 0, 0);
    if (unit === 'week') d.setDate(d.getDate() - (d.getDay() + 6) % 7);
    if (unit === 'month') d.setDate(1);
    return dayKey(d); };
  const axis = [];
  for (let t = first; t <= end; t += 86400000) { const k = keyOf(t); if (axis[axis.length - 1] !== k && !axis.includes(k)) axis.push(k); }
  const idx = Object.fromEntries(axis.map((d, i) => [d, i]));
  const per_src = SRC.map(() => axis.map(() => 0));
  for (const r of T) { const i = idx[keyOf(r.t0 * 1000)]; if (i != null) per_src[Math.min(r.src, SRC.length - 1)][i]++; }
  $('hDay').textContent = 'Encendidos por ' + { day: 'día', week: 'semana', month: 'mes' }[unit];
  const axLabel = d => unit === 'month' ? MES[+d.slice(5, 7) - 1] + ' ' + d.slice(2, 4) : (unit === 'week' ? 'sem. ' : '') + +d.slice(8) + ' ' + MES[+d.slice(5, 7) - 1];
  chart('chDay', { type: 'bar', data: { labels: axis.map(axLabel),
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
  for (const r of R) { cs[Math.min(r.src, SRC.length - 1)]++; if (r.end < END.length) ce[r.end]++; if (r.th) thN++; }
  bars('bSrc', SRC.map((l, i) => [l, cs[i], SRCC[i]]).sort((a, b) => b[1] - a[1]));
  if (thN) $('bSrc').insertAdjacentHTML('beforeend', `<p class="muted" style="margin:10px 0 0">${nf(thN)} con «calentar hasta» (termostato).</p>`);
  ce[NOWBUS] = F.length;
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
    { label: 'Al empezar', data: L.map(r => r.c0), borderColor: css('--ice'), backgroundColor: css('--ice'), tension: .3, spanGaps: true, pointRadius: 2 },
    { label: 'Máxima', data: L.map(r => r.cmax), borderColor: css('--hot'), backgroundColor: css('--hot'), tension: .3, spanGaps: true, pointRadius: 2 }] },
    options: { scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { title: { display: true, text: '°C' } } } } });
  chart('chBatt', { type: 'line', data: { labels: lab, datasets: [
    { label: 'Al empezar', data: L.map(r => r.v0), borderColor: '#3ecf8e', backgroundColor: '#3ecf8e', tension: .3, spanGaps: true, pointRadius: 2 },
    { label: 'Mínima', data: L.map(r => r.vmin), borderColor: '#ffcc4d', backgroundColor: 'rgba(255,204,77,.15)', fill: '-1', tension: .3, spanGaps: true, pointRadius: 2 }] },
    options: { scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { suggestedMin: 11, suggestedMax: 13.5, title: { display: true, text: 'V' } } } } });
  const drops = R.filter(r => r.v0 != null && r.vmin != null).map(r => r.v0 - r.vmin), lowB = R.filter(r => r.vmin != null && r.vmin < 11.8).length;
  $('battNote').textContent = (drops.length ? `Baja de media ${nf(drops.reduce((a, d) => a + d, 0) / drops.length, 2)} V mientras calienta (la bujía tira al arrancar). ` : '') +
    (lowB ? `${lowB} encendidos bajaron de 11,8 V.` : R.some(r => r.vmin != null) ? 'Nunca ha bajado de 11,8 V.' : '');
  const H = L.some(r => r.hum0 != null);
  chart('chHum', { type: 'line', data: { labels: lab, datasets: [
    { label: 'Al empezar', data: L.map(r => r.hum0), borderColor: '#a78bfa', backgroundColor: 'rgba(167,139,250,.15)', tension: .3, spanGaps: true, pointRadius: 2 },
    { label: 'Al acabar', data: L.map(r => r.hum1), borderColor: css('--ice'), backgroundColor: 'rgba(91,192,235,.18)', fill: '-1', tension: .3, spanGaps: true, pointRadius: 2 }] },
    options: { scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { min: 0, max: 100, title: { display: true, text: '%' } } } } });
  const hd = R.filter(r => r.hum0 != null && r.hum1 != null);
  $('humNote').textContent = hd.length ? `De media pasa del ${Math.round(hd.reduce((a, r) => a + r.hum0, 0) / hd.length)} % al ${Math.round(hd.reduce((a, r) => a + r.hum1, 0) / hd.length)} %: menos humedad, menos vaho en los cristales.`
    : H ? '' : 'Llega con el firmware 0.2.17 o posterior y un termómetro con humedad (SHT31 o AHT20).';
  chart('chDur', { type: 'bar', data: { labels: lab, datasets: [{ label: 'Minutos', data: L.map(r => +(r.dur / 60).toFixed(1)),
    backgroundColor: L.map(r => ENDC[r.end] || css('--acc')), borderRadius: 3 }] },
    options: { plugins: { legend: { display: false }, tooltip: { callbacks: { afterLabel: c => END[L[c.dataIndex].end] || '' } } },
      scales: { x: { ticks: { maxTicksLimit: 8 } }, y: { beginAtZero: true, title: { display: true, text: 'min' } } } } });

  // Averías
  const ef = {};
  for (const r of R) if (r.err) { const c = r.err.toString(16).toUpperCase().padStart(2, '0'); ef[c] = (ef[c] || 0) + 1; }
  bars('bErr', Object.entries(ef).map(([c, n]) => ['0x' + c + (data.errnames[c] ? ' · ' + data.errnames[c] : ''), n, css('--hot')]).sort((a, b) => b[1] - a[1]),
    null, '✅ Ninguna avería en este periodo.');

  // ---------- Récords del periodo ----------
  const best = (arr, f) => arr.length ? arr.reduce((a, r) => f(r) > f(a) ? r : a) : null;
  const when = r => r && r.t0 ? fdate(r.t0) : '';
  const byDay = {};
  for (const r of T) { const d = dayKey(new Date(r.t0 * 1000)); (byDay[d] = byDay[d] || { n: 0, s: 0 }); byDay[d].n++; byDay[d].s += r.dur; }
  const topDay = Object.entries(byDay).sort((a, b) => b[1].n - a[1].n)[0];
  const rLong = best(R, r => r.dur), rCold = best(R.filter(r => r.cab0 > -128), r => -r.cab0),
        rRise = best(withCab, r => r.cab1 - r.cab0), rHot = best(R.filter(r => r.cmax != null), r => r.cmax),
        rBatt = best(R.filter(r => r.vmin != null), r => -r.vmin);
  const tile = (i, v, l, w) => `<div class="rec"><div class="i">${i}</div><div class="v">${v}</div><div class="l">${l}${w ? '<br>' + w : ''}</div></div>`;
  $('recs').innerHTML = [
    rLong && tile('⏱️', hm(rLong.dur), 'el encendido más largo', when(rLong)),
    rCold && tile('🥶', rCold.cab0 + ' °C', 'la mañana más fría dentro', when(rCold)),
    rRise && tile('📈', '+' + (rRise.cab1 - rRise.cab0) + ' °C', 'lo que más ha subido dentro', when(rRise)),
    rHot && tile('🌡️', rHot.cmax + ' °C', 'el agua más caliente', when(rHot)),
    rBatt && tile('🔋', nf(rBatt.vmin, 1) + ' V', 'la batería más baja', when(rBatt)),
    (() => { const r = best(R.filter(r => r.hum0 != null && r.hum1 != null), r => r.hum0 - r.hum1);
      return r && r.hum0 - r.hum1 > 0 ? tile('💧', '−' + (r.hum0 - r.hum1) + ' %', 'lo que más ha bajado la humedad', when(r)) : null; })(),
    topDay && tile('🔥', topDay[1].n + (topDay[1].n === 1 ? ' vez' : ' veces'), 'el día que más se encendió', topDay[0].split('-').reverse().join('/')),
  ].filter(Boolean).join('') || '<p class="muted">Sin datos en este periodo.</p>';

  // ---------- Calendario de uso (horas por día) y rachas ----------
  // Del periodo elegido, como mucho el último año; columnas = semanas (de lunes a domingo)
  const calEnd = new Date(custom ? until * 1000 : Date.now()); calEnd.setHours(12, 0, 0, 0);
  const calDays = Math.min(371, Math.max(28, span ? Math.ceil(span / 86400) : Math.ceil((calEnd - Math.min(...T.map(r => r.t0 * 1000), calEnd)) / 86400000) + 1));
  const calStart = new Date(calEnd - (calDays - 1) * 86400000); calStart.setDate(calStart.getDate() - (calStart.getDay() + 6) % 7);
  const shade = h => h <= 0 ? 'var(--bg-inner)' : h < .4 ? '#5a3418' : h < .8 ? '#9a5520' : h < 1.5 ? '#d9772c' : '#ffa25a';
  let cells = '';
  for (let d = new Date(calStart); d <= calEnd; d.setDate(d.getDate() + 1)) {
    const k2 = dayKey(d), h = byDay[k2] ? byDay[k2].s / 3600 : 0;
    cells += `<i style="background:${shade(h)}" title="${d.getDate()} ${MES[d.getMonth()]}: ${h ? hm(h * 3600) + ', ' + byDay[k2].n + ' encendidos' : 'sin usar'}"></i>`;
  }
  $('cal').innerHTML = cells;
  $('hCal').textContent = 'Calendario de uso';
  // Rachas: días seguidos con algún encendido (la actual cuenta si se usó hoy o ayer)
  const used = Object.keys(byDay).sort();
  let longest = 0, run = 0, prev = null;
  for (const d of used) { const t = new Date(d + 'T12:00'); run = prev && (t - prev) / 86400000 === 1 ? run + 1 : 1; longest = Math.max(longest, run); prev = t; }
  let cur = 0; for (let d = new Date(); ; d.setDate(d.getDate() - 1)) { if (byDay[dayKey(d)]) cur++; else if (cur || dayKey(d) !== dayKey(new Date())) break; }
  $('streak').innerHTML = `Usada <b>${used.length}</b> días · racha más larga: <b>${longest}</b> días seguidos` + (cur ? ` · racha actual: <b>${cur}</b>` : '');

  // ---------- Día de la semana × hora ----------
  const grid = WD.map(() => Array(24).fill(0));
  for (const r of T) { const d = new Date(r.t0 * 1000); grid[(d.getDay() + 6) % 7][d.getHours()]++; }
  const gmax = Math.max(1, ...grid.flat());
  $('hm').innerHTML = '<span></span>' + Array.from({ length: 24 }, (_, h) => `<span style="justify-content:center">${h % 3 ? '' : h}</span>`).join('') +
    WD.map((w, i) => `<span>${w}</span>` + grid[i].map((n, h) => `<i title="${w} ${h}:00 · ${n} encendidos" style="${n ? `background:rgba(255,138,61,${.18 + .82 * n / gmax})` : ''}"></i>`).join('')).join('');

  // ---------- Cuánto tarda en calentar: grados por cada 10 min según el frío de dentro al empezar ----------
  const sp = withCab.map(r => ({ x: r.cab0, y: +((r.cab1 - r.cab0) / (r.dur / 600)).toFixed(2) }));
  chart('chSpeed', { type: 'scatter', data: { datasets: [{ label: '°C cada 10 min', data: sp, backgroundColor: 'rgba(255,138,61,.75)', pointRadius: 4 }] },
    options: { interaction: { mode: 'nearest', intersect: true }, plugins: { legend: { display: false } },
      scales: { x: { title: { display: true, text: 'dentro al empezar (°C)' } }, y: { beginAtZero: true, title: { display: true, text: '°C cada 10 min' } } } } });
  if (sp.length) {
    const avg = sp.reduce((a, p) => a + p.y, 0) / sp.length, cold = sp.filter(p => p.x <= 3), warm = sp.filter(p => p.x > 3);
    const m = a => a.length ? nf(a.reduce((x, p) => x + p.y, 0) / a.length, 1) : '—';
    $('speedNote').textContent = `De media sube ${nf(avg, 1)} °C cada 10 minutos (empezando con 3 °C o menos: ${m(cold)}; con más: ${m(warm)}). Para subir 10 °C tarda unos ${Math.round(100 / Math.max(avg, .1))} min.`;
  } else $('speedNote').textContent = 'Hace falta el termómetro interior (SHT31 o AHT20).';

  // ---------- Coste y consumo por mes ----------
  chart('chCost', { data: { labels: mk.map(m => MES[+m.slice(5) - 1] + ' ' + m.slice(2, 4)), datasets: [
    { type: 'bar', label: 'Coste (€)', data: mk.map(m => +(months[m].l * price).toFixed(2)), backgroundColor: '#3ecf8e', borderRadius: 6, yAxisID: 'y' },
    { type: 'line', label: 'Litros por hora', data: mk.map(m => months[m].h ? +(months[m].l / months[m].h).toFixed(3) : null), borderColor: css('--ice'), backgroundColor: css('--ice'), tension: .35, yAxisID: 'y1' }] },
    options: { scales: { y: { beginAtZero: true, title: { display: true, text: '€' } }, y1: { beginAtZero: true, position: 'right', grid: { display: false }, title: { display: true, text: 'L/h' } } } } });

  // ---------- Termostato ----------
  const TH = R.filter(r => r.th);
  if (!TH.length) $('thermo').innerHTML = '<p class="muted">No se ha usado «calentar hasta» en este periodo (hace falta el termómetro interior).</p>';
  else {
    const ok = TH.filter(r => r.end === 2), stall = TH.filter(r => r.end === 6);
    const row = (l, v) => `<div class="bar" style="grid-template-columns:1fr auto"><span class="bl">${l}</span><span><b>${v}</b></span></div>`;
    $('thermo').innerHTML = '<div class="bars">' +
      row('Encendidos con termostato', nf(TH.length) + ' (' + Math.round(TH.length / R.length * 100) + ' %)') +
      row('Llegó a la temperatura', nf(ok.length) + ' (' + Math.round(ok.length / TH.length * 100) + ' %)') +
      row('Tiempo medio hasta llegar', (() => { const t = TH.filter(r => r.treach != null && r.treach < 255);
        return t.length ? Math.round(t.reduce((a, r) => a + r.treach, 0) / t.length) + ' min' : ok.length ? hm(ok.reduce((a, r) => a + r.dur, 0) / ok.length) : '—'; })()) +
      (() => { const c = {}; for (const r of TH) if (r.tgt) c[r.tgt] = (c[r.tgt] || 0) + 1;
        const top = Object.entries(c).sort((a, b) => b[1] - a[1]).slice(0, 3);
        return top.length ? row('Objetivos que más usas', top.map(([t, n]) => t + ' °C (' + n + ')').join(' · ')) : ''; })() +
      row('Se rindió porque dentro no subía', nf(stall.length)) +
      row('Gasoil por encendido con termostato', nf(TH.reduce((a, r) => a + r.ml, 0) / 1000 / TH.length, 2) + ' L') +
      row('Gasoil por encendido sin termostato', R.length > TH.length ? nf(R.filter(r => !r.th).reduce((a, r) => a + r.ml, 0) / 1000 / (R.length - TH.length), 2) + ' L' : '—') +
      '</div>';
  }

  // ---------- Comparado con las demás placas (medianas de los últimos 90 días, de api/mi.php) ----------
  const C = data.comunidad || { n: 0 };
  if (data.demo || C.n < 3) $('cmp').innerHTML = `<p class="muted">${data.demo ? 'En el ejemplo no hay comparación.' :
    'Hace falta que envíen estadísticas al menos 3 placas para comparar sin que la «media» sea la de otra persona (ahora: ' + C.n + ').'}</p>`;
  else {
    const R90 = all.filter(r => r.t0 && r.t0 >= Date.now() / 1000 - 90 * 86400), w = 90 / 7;
    const mine = { hweek: R90.reduce((a, r) => a + r.dur, 0) / 3600 / w, rweek: R90.length / w,
      lph: R90.reduce((a, r) => a + r.dur, 0) ? R90.reduce((a, r) => a + r.ml, 0) / 1000 / (R90.reduce((a, r) => a + r.dur, 0) / 3600) : 0,
      min: R90.length ? R90.reduce((a, r) => a + r.dur, 0) / 60 / R90.length : 0 };
    const cmpRow = (l, a, b, f) => { const d = b ? Math.round((a - b) / b * 100) : 0;
      return `<div class="row"><div class="muted">${l}</div><b>${f(a)}</b> <span class="muted">tú · media ${f(b)}</span><div class="${d > 0 ? 'up' : 'down'}" style="font-size:.85rem;margin-top:2px">${d ? (d > 0 ? '↑ ' : '↓ ') + Math.abs(d) + ' % ' + (d > 0 ? 'más' : 'menos') + ' que la media' : 'como la media'}</div></div>`; };
    $('cmp').innerHTML = '<div class="cmp">' +
      cmpRow('Horas a la semana', mine.hweek, C.hweek, v => nf(v, 1) + ' h') +
      cmpRow('Encendidos a la semana', mine.rweek, C.rweek, v => nf(v, 1)) +
      cmpRow('Duración media', mine.min, C.min, v => Math.round(v) + ' min') +
      cmpRow('Gasoil por hora', mine.lph, C.lph, v => nf(v, 2) + ' L/h') +
      `</div><p class="muted" style="margin-top:10px">Medianas de las ${C.n} placas que envían estadísticas, en los últimos 90 días. Nunca se ven datos de otra placa.</p>`;
  }

  // ---------- Consejos para ti: lo que dicen tus datos, con algo que hacer ----------
  const tips = [];
  const tip = (i, html) => tips.push(`<li><span class="i">${i}</span><span>${html}</span></li>`);
  // 1. Cuánto antes encender según el frío: minutos reales hasta el objetivo (0.2.17+) o, si no, por la velocidad media
  const reachData = R.filter(r => r.tgt && r.treach != null && r.treach < 255 && r.cab0 > -128);
  const minsFor = cold => { const a = reachData.filter(r => cold ? r.cab0 <= 3 : r.cab0 > 3); return a.length >= 3 ? Math.round(a.reduce((x, r) => x + r.treach, 0) / a.length) : null; };
  const mCold = minsFor(true), mWarm = minsFor(false);
  if (mCold || mWarm) tip('⏰', `Para llegar a tu temperatura tardas <b>${mCold ? mCold + ' min' : '—'}</b> cuando dentro hay 3 °C o menos` +
    (mWarm ? ` y <b>${mWarm} min</b> si hace menos frío` : '') + '. Con «Salgo a las…» la placa lo calcula sola; en un programa, empieza ese tiempo antes.');
  else if (sp.length >= 5) { const avg = sp.reduce((a, p) => a + p.y, 0) / sp.length;
    tip('⏰', `Dentro sube unos <b>${nf(avg, 1)} °C cada 10 min</b>: para pasar de 2 °C a 20 °C necesita unos <b>${Math.round(180 / Math.max(avg, .1))} min</b>. Tenlo en cuenta al programar.`); }
  // 2. Se acaba el tiempo antes de llegar a una temperatura cómoda: mejor «calentar hasta»
  const short = R.filter(r => !r.th && r.end === 0 && r.cab1 > -128 && r.cab1 < 16);
  if (short.length >= 3 && short.length / Math.max(1, R.filter(r => !r.th).length) > .3)
    tip('🎯', `En <b>${short.length}</b> encendidos se acabó el tiempo con menos de 16 °C dentro. Prueba «calentar hasta» (por ejemplo 20 °C): se apaga al llegar y no se queda corta.`);
  // 3. Gasto con y sin termostato
  const thR = R.filter(r => r.th), noR = R.filter(r => !r.th);
  if (thR.length >= 3 && noR.length >= 3) {
    const lt = thR.reduce((a, r) => a + r.ml, 0) / thR.length, ln = noR.reduce((a, r) => a + r.ml, 0) / noR.length, d = Math.round((ln - lt) / ln * 100);
    if (Math.abs(d) >= 5) tip('⛽', d > 0 ? `Con «calentar hasta» gastas un <b>${d} % menos</b> por encendido que con tiempo fijo.`
      : `Con «calentar hasta» gastas un <b>${-d} % más</b> por encendido: quizá el objetivo es alto; prueba con 1 o 2 °C menos.`);
  }
  // 4. Dentro no sube (el termostato se rinde)
  const stalls = thR.filter(r => r.end === 6).length;
  if (stalls >= 2 && stalls / Math.max(1, thR.length) >= .15)
    tip('🧊', `<b>${stalls}</b> veces el termostato se rindió porque dentro no subía. Puede ser mucho frío, una ventana abierta o el objetivo demasiado alto para esa noche.`);
  // 5. Batería
  if (F.length) tip('🔌', `<b>${F.length}</b> ${F.length === 1 ? 'vez' : 'veces'} la Webasto no contestó al intentar encenderla${F.length >= 2 ? '' : ' (' + (F[0].t0 ? fdate(F[0].t0) : 'sin hora') + ')'}. ` +
    'Si se repite, revisa el cable del W-Bus (el hilo que va al mando), el fusible de la Webasto y que el conector esté bien encajado; en Diagnóstico, «Leer averías» dice si al menos contesta.');
  if (lowB) tip('🔋', `La batería bajó de 11,8 V en <b>${lowB}</b> encendidos. Si arrancas el motor con dificultad, sube la «batería mínima» en Configuración o calienta menos rato.`);
  else if (drops.length >= 5 && drops.reduce((a, d) => a + d, 0) / drops.length > .8) tip('🔋', 'La batería baja más de 0,8 V de media mientras calienta: puede estar cansada. Vale la pena medirla.');
  // 6. Humedad que se queda alta
  const wet = R.filter(r => r.hum1 != null && r.hum1 >= 70).length;
  if (wet >= 3) tip('💧', `En <b>${wet}</b> encendidos la humedad seguía en el 70 % o más al acabar. Ventila un minuto al entrar: se empañan menos los cristales.`);
  // 7. Siempre a la misma hora a mano: un programa lo haría solo
  const manual = T.filter(r => r.src === 0 || r.src === 1), hc = {};
  for (const r of manual) { const h = new Date(r.t0 * 1000).getHours(); hc[h] = (hc[h] || 0) + 1; }
  const topH = Object.entries(hc).sort((a, b) => b[1] - a[1])[0];
  if (topH && topH[1] >= 5) tip('📅', `La has encendido a mano <b>${topH[1]} veces</b> sobre las <b>${topH[0]}:00</b>. Un programa a esa hora (o «Salgo a las…») lo haría solo.`);
  // 8. Avería que se repite
  const top = Object.entries(ef).sort((a, b) => b[1] - a[1])[0];
  if (top && top[1] >= 2) tip('🛠️', `La avería <b>0x${top[0]}</b>${data.errnames[top[0]] ? ' (' + data.errnames[top[0]] + ')' : ''} se ha repetido ${top[1]} veces. Mira qué significa y cómo arreglarla en <a href="/averias.php">códigos de avería</a>.`);
  // 9. Este mes: gasto y previsión al ritmo actual
  const nowD = new Date(), m0 = new Date(nowD.getFullYear(), nowD.getMonth(), 1) / 1000;
  const thisM = all.filter(r => r.t0 >= m0), lm = thisM.reduce((a, r) => a + r.ml, 0) / 1000;
  if (thisM.length) { const dim = new Date(nowD.getFullYear(), nowD.getMonth() + 1, 0).getDate(), f = dim / Math.max(1, nowD.getDate());
    tip('💶', `Este mes llevas <b>${nf(lm, 2)} L</b> (≈ ${nf(lm * price, 2)} €). Al ritmo actual acabarás el mes con unos <b>${nf(lm * f, 1)} L</b> (≈ ${nf(lm * f * price, 0)} €).`); }
  $('tips').innerHTML = tips.join('') || '<li><span class="i">👍</span><span>Nada que mejorar con los datos de este periodo.</span></li>';

  // Tabla
  const last = R.concat(F).sort((a, b) => a.seq - b.seq).slice(-25).reverse();   // con los arranques fallidos
  $('tbl').innerHTML = '<tr><th>Cuándo</th><th>Duración</th><th>Gasoil</th><th>Dentro</th><th>Agua</th><th>Potencia</th><th>Quién</th><th>Final</th></tr>' +
    last.map(r => `<tr><td>${r.t0 ? fdate(r.t0) : 'sin hora'}</td><td>${r.end === NOWBUS ? '—' : hm(r.dur)}</td><td>${nf(r.ml / 1000, 2)} L</td>` +
      `<td>${r.cab0 > -128 ? r.cab0 + ' → ' + (r.cab1 > -128 ? r.cab1 : '?') + ' °C' : '—'}</td><td>${r.cmax != null ? r.cmax + ' °C' : '—'}</td><td>${r.pw != null ? nf(r.pw / 1000, 1) + ' kW' : '—'}</td>` +
      `<td>${SRC[Math.min(r.src, SRC.length - 1)]}${r.th ? ' · termostato' : ''}</td>` +
      `<td><span class="tag ${r.end === 4 || r.end === 5 || r.end === NOWBUS ? 'bad' : r.end === 2 ? 'ok' : ''}">${END[r.end] || '?'}${r.err ? ' 0x' + r.err.toString(16).toUpperCase().padStart(2, '0') : ''}</span></td></tr>`).join('');
}

// Arranque: el código del «#» de la dirección, o el recordado
const start = norm(decodeURIComponent(location.hash.slice(1))) || norm(store.get('wttc_iid') || '');
if (start) { $('code').value = start; openCode(start); } else $('code').focus();
// PWA: service worker mínimo (solo para poder instalarla), limitado a esta página
if ('serviceWorker' in navigator) navigator.serviceWorker.register('/mi-sw.js', { scope: '/mi.php' }).catch(() => {});
window.addEventListener('hashchange', () => { const c = norm(decodeURIComponent(location.hash.slice(1))); if (c && (!data || data.iid !== c)) openCode(c); });
</script>
</body>
</html>
