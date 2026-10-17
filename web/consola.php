<?php
// wttc/consola.php — Consola serie en el navegador (Web Serial): ver, filtrar y guardar lo que cuentan por USB la
// placa de WTTC y la Webasto falsa, sin instalar nada ni pulsar «Instalar». Dos puertos a la vez (uno por panel), con
// detección de cuál es cada placa, colores según el tipo de línea, envío de órdenes con historial, vista conjunta por
// hora y exportación a .txt o .md (con resumen; oculta PIN y claves por defecto). Código generado íntegramente con
// Claude (Anthropic). 2026-10-17. Idioma: ?l=es|en|de. Necesita Chrome, Edge u Opera en un ordenador (Web Serial) y que
// Caddy permita «serial» a esta web (Permissions-Policy de wttc.favala.es).
require_once __DIR__ . '/api/db.php';
wttc_public_only();
$lang = $_GET['l'] ?? 'es';
if (!in_array($lang, ['es', 'en', 'de'], true)) $lang = 'es';
wttc_visit($lang === 'es' ? 'consola' : 'consola-' . $lang);
$HOME = ['es' => '/', 'en' => '/en/', 'de' => '/de/'][$lang];
$INST = ['es' => '/instalar.php', 'en' => '/en/install.php', 'de' => '/de/installieren.php'][$lang];
$TX = [
'es' => ['title' => 'Consola serie · WTTC', 'h1' => 'Consola serie',
  'intro' => 'Conecta por USB la placa de WTTC, la Webasto falsa o las dos (cada una en su panel) y mira lo que cuentan, en colores. Puedes escribirles órdenes, filtrar, juntar las dos por hora y guardarlo en .txt o .md. No instala nada.',
  'connect' => '🔌 Conectar puerto', 'disconnect' => 'Desconectar', 'reconnect' => 'Reconectar', 'reset' => '↻ Reiniciar placa',
  'clear' => 'Borrar', 'pause' => '⏸ Pausar', 'resume' => '▶ Seguir', 'filter' => 'Filtrar…', 'send' => 'Enviar',
  'cmdPh' => 'Orden (Enter para enviar; ↑ ↓ historial)', 'panel' => 'Puerto', 'unknown' => 'Sin identificar', 'wttc' => 'WTTC', 'fake' => 'Webasto falsa',
  'waiting' => 'Pulsa «Conectar puerto» y elige el de la placa (CP210x, CH340/CH343, «USB Serial»…).',
  'lines' => 'líneas', 'joined' => '🔀 Juntar los dos por hora', 'exportTxt' => '⬇ .txt', 'exportMd' => '⬇ .md (con resumen)',
  'redact' => 'Ocultar PIN y claves al exportar', 'stamps' => 'Hora del ordenador en cada línea',
  'unsupported' => 'Este navegador no puede usar el puerto serie. Abre esta página con Chrome, Edge u Opera en un ordenador.',
  'busy' => 'No se pudo abrir: ¿lo tiene abierto otro programa (Monitor serie de Arduino, PuTTY, la ventana del instalador)? Ciérralo y prueba otra vez.',
  'lost' => 'Se ha desconectado el puerto.', 'probe' => 'Preguntando quién es («info»)…',
  'leave' => 'Hay registro sin guardar.', 'back' => '← Instalar', 'help' => 'Cómo leer los colores',
  'legend' => '<b class="k-req">&lt;</b> WTTC pide · <b class="k-res">&gt;</b> la Webasto (falsa) contesta · <b class="k-ev">.</b> lo que hace la caldera y por qué · <b class="k-warn">!</b> avisos · <b class="k-cmd">$</b> lo escrito en la consola · <b class="k-st">=</b> estado · <span class="k-hex">F4 03 50</span> tramas · <span class="k-boot">ESP-ROM…</span> arranque del chip',
  'mdTitle' => 'Consola serie', 'mdSummary' => 'Resumen', 'mdWarn' => 'Avisos y errores', 'mdLog' => 'Registro', 'mdJoined' => 'Las dos placas por hora',
  'mdReq' => 'órdenes de WTTC', 'mdRes' => 'respuestas', 'mdWarnN' => 'avisos', 'mdStates' => 'Cambios de estado', 'mdNone' => 'Ninguno.', 'mdDur' => 'Duración', 'mdBaud' => 'velocidad'],
'en' => ['title' => 'Serial console · WTTC', 'h1' => 'Serial console',
  'intro' => 'Plug the WTTC board, the fake Webasto or both (each in its own panel) into USB and see what they report, in colour. You can send them commands, filter, merge both by time and save it as .txt or .md. Nothing is installed.',
  'connect' => '🔌 Connect port', 'disconnect' => 'Disconnect', 'reconnect' => 'Reconnect', 'reset' => '↻ Restart board',
  'clear' => 'Clear', 'pause' => '⏸ Pause', 'resume' => '▶ Resume', 'filter' => 'Filter…', 'send' => 'Send',
  'cmdPh' => 'Command (Enter to send; ↑ ↓ history)', 'panel' => 'Port', 'unknown' => 'Unidentified', 'wttc' => 'WTTC', 'fake' => 'Fake Webasto',
  'waiting' => 'Click “Connect port” and pick the board’s one (CP210x, CH340/CH343, “USB Serial”…).',
  'lines' => 'lines', 'joined' => '🔀 Merge both by time', 'exportTxt' => '⬇ .txt', 'exportMd' => '⬇ .md (with summary)',
  'redact' => 'Hide PIN and passwords when exporting', 'stamps' => 'Computer time on every line',
  'unsupported' => 'This browser cannot use the serial port. Open this page with Chrome, Edge or Opera on a computer.',
  'busy' => 'Could not open it: is another program using it (Arduino Serial Monitor, PuTTY, the installer window)? Close it and try again.',
  'lost' => 'The port was disconnected.', 'probe' => 'Asking who it is (“info”)…',
  'leave' => 'There is an unsaved log.', 'back' => '← Install', 'help' => 'How to read the colours',
  'legend' => '<b class="k-req">&lt;</b> WTTC asks · <b class="k-res">&gt;</b> the (fake) Webasto answers · <b class="k-ev">.</b> what the heater does and why · <b class="k-warn">!</b> warnings · <b class="k-cmd">$</b> typed in the console · <b class="k-st">=</b> status · <span class="k-hex">F4 03 50</span> frames · <span class="k-boot">ESP-ROM…</span> chip boot',
  'mdTitle' => 'Serial console', 'mdSummary' => 'Summary', 'mdWarn' => 'Warnings and errors', 'mdLog' => 'Log', 'mdJoined' => 'Both boards by time',
  'mdReq' => 'WTTC requests', 'mdRes' => 'answers', 'mdWarnN' => 'warnings', 'mdStates' => 'State changes', 'mdNone' => 'None.', 'mdDur' => 'Duration', 'mdBaud' => 'baud'],
'de' => ['title' => 'Serielle Konsole · WTTC', 'h1' => 'Serielle Konsole',
  'intro' => 'Die WTTC-Platine, die falsche Webasto oder beide (jede in ihrem Feld) per USB anschließen und farbig sehen, was sie melden. Man kann Befehle senden, filtern, beide nach Zeit zusammenführen und als .txt oder .md speichern. Es wird nichts installiert.',
  'connect' => '🔌 Port verbinden', 'disconnect' => 'Trennen', 'reconnect' => 'Neu verbinden', 'reset' => '↻ Platine neu starten',
  'clear' => 'Leeren', 'pause' => '⏸ Pause', 'resume' => '▶ Weiter', 'filter' => 'Filtern…', 'send' => 'Senden',
  'cmdPh' => 'Befehl (Enter zum Senden; ↑ ↓ Verlauf)', 'panel' => 'Port', 'unknown' => 'Nicht erkannt', 'wttc' => 'WTTC', 'fake' => 'Falsche Webasto',
  'waiting' => '„Port verbinden“ klicken und den der Platine wählen (CP210x, CH340/CH343, „USB Serial“…).',
  'lines' => 'Zeilen', 'joined' => '🔀 Beide nach Zeit zusammenführen', 'exportTxt' => '⬇ .txt', 'exportMd' => '⬇ .md (mit Zusammenfassung)',
  'redact' => 'PIN und Passwörter beim Export ausblenden', 'stamps' => 'Uhrzeit des Computers in jeder Zeile',
  'unsupported' => 'Dieser Browser kann den seriellen Port nicht nutzen. Diese Seite mit Chrome, Edge oder Opera auf einem Computer öffnen.',
  'busy' => 'Konnte nicht geöffnet werden: nutzt ihn ein anderes Programm (serieller Monitor von Arduino, PuTTY, das Installationsfenster)? Schließen und erneut versuchen.',
  'lost' => 'Der Port wurde getrennt.', 'probe' => 'Frage, wer es ist („info“)…',
  'leave' => 'Es gibt ein ungespeichertes Protokoll.', 'back' => '← Installieren', 'help' => 'Wie man die Farben liest',
  'legend' => '<b class="k-req">&lt;</b> WTTC fragt · <b class="k-res">&gt;</b> die (falsche) Webasto antwortet · <b class="k-ev">.</b> was die Heizung tut und warum · <b class="k-warn">!</b> Warnungen · <b class="k-cmd">$</b> in der Konsole getippt · <b class="k-st">=</b> Status · <span class="k-hex">F4 03 50</span> Telegramme · <span class="k-boot">ESP-ROM…</span> Start des Chips',
  'mdTitle' => 'Serielle Konsole', 'mdSummary' => 'Zusammenfassung', 'mdWarn' => 'Warnungen und Fehler', 'mdLog' => 'Protokoll', 'mdJoined' => 'Beide Platinen nach Zeit',
  'mdReq' => 'WTTC-Anfragen', 'mdRes' => 'Antworten', 'mdWarnN' => 'Warnungen', 'mdStates' => 'Zustandswechsel', 'mdNone' => 'Keine.', 'mdDur' => 'Dauer', 'mdBaud' => 'Baud'],
][$lang];
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $h($TX['title']) ?></title>
<meta name="robots" content="noindex">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<style>
  :root{--bg:#0f1117;--card:#1a1d27;--inner:#0b0d13;--border:#2e3350;--text:#e2e8f8;--muted:#7a84a8;--acc:#3a8ee0;--fl:#ff9f1c}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:var(--bg);color:var(--text);font:15px/1.5 -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:16px}
  .wrap{max-width:1500px;margin:0 auto}
  a{color:#5bc0eb} a.back{color:var(--muted);font-size:.85rem;text-decoration:none}
  h1{font-size:1.4rem;margin:8px 0 4px;background:linear-gradient(135deg,#ff9f1c,#5bc0eb);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  .sub{color:var(--muted);font-size:.9rem;max-width:900px}
  .bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:12px 0}
  button,select,input[type=text],input[type=search]{background:var(--card);color:var(--text);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font:inherit;font-size:.86rem}
  button{cursor:pointer} button.pri{background:var(--fl);color:#1a1000;border-color:var(--fl);font-weight:700}
  button:disabled{opacity:.45;cursor:default}
  label.ck{font-size:.82rem;color:var(--muted);display:flex;gap:5px;align-items:center}
  .panels{display:grid;grid-template-columns:repeat(auto-fit,minmax(520px,1fr));gap:12px}
  @media (max-width:700px){.panels{grid-template-columns:1fr}}
  .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;display:flex;flex-direction:column;min-width:0}
  .ph{display:flex;gap:6px;flex-wrap:wrap;align-items:center;padding:8px 10px;border-bottom:1px solid var(--border)}
  .ph .who{font-weight:700;margin-right:auto;display:flex;gap:6px;align-items:center}
  .dot{width:10px;height:10px;border-radius:50%;background:#555}
  .dot.on{background:#3ab97a;box-shadow:0 0 6px #3ab97a}
  .badge{font-size:.72rem;border-radius:999px;padding:1px 8px;background:#2e3350}
  .badge.wttc{background:#1d4f91} .badge.fake{background:#7a4a10}
  .log{height:62vh;overflow:auto;background:var(--inner);font:12.5px/1.45 ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;padding:8px 10px;white-space:pre-wrap;word-break:break-word}
  .log .ln{display:block}
  .log .t{color:#4b5470} .log .src{font-weight:700;margin-right:4px}
  .src.wttc{color:#6aa8ff} .src.fake{color:#ffb35c}
  .k-req{color:#6aa8ff} .k-res{color:#4fd18b} .k-ev{color:#d0c4ff} .k-warn{color:#ff8a65;font-weight:700} .k-cmd{color:#56d4dd}
  .k-st{color:#8f98b8} .k-hex{color:#e9c46a} .k-boot{color:#4b5470;font-style:italic} .k-num{color:#fff;font-weight:600}
  .k-bad{color:#ff6b6b;font-weight:700} .k-good{color:#4fd18b;font-weight:700} .k-on{color:#ffb35c;font-weight:700} .k-state{color:#ffd166}
  .k-motd{color:#9ecbff} .k-mine{color:#56d4dd;font-style:italic}
  .pf{display:flex;gap:6px;padding:8px 10px;border-top:1px solid var(--border)}
  .pf input{flex:1;min-width:0}
  .foot{color:var(--muted);font-size:.75rem;padding:0 10px 8px}
  details.leg{margin:8px 0;color:var(--muted);font-size:.85rem} details.leg summary{cursor:pointer}
  .aviso{background:rgba(224,169,58,.12);border:1px solid #e0a93a;border-radius:10px;padding:10px 14px;margin:12px 0}
  #joined{display:none;margin-top:12px} #joined.on{display:flex}
</style>
</head>
<body><div class="wrap">
<a class="back" href="<?= $INST ?>"><?= $h($TX['back']) ?></a>
<h1>🖥️ <?= $h($TX['h1']) ?></h1>
<p class="sub"><?= $h($TX['intro']) ?></p>
<div class="aviso" id="nosup" hidden><?= $h($TX['unsupported']) ?></div>
<div class="bar">
  <button id="add">＋ <?= $h($TX['panel']) ?></button>
  <button id="bJoin" disabled><?= $h($TX['joined']) ?></button>
  <label class="ck"><input type="checkbox" id="stamps" checked> <?= $h($TX['stamps']) ?></label>
  <label class="ck"><input type="checkbox" id="redact" checked> <?= $h($TX['redact']) ?></label>
  <button id="xTxt"><?= $h($TX['exportTxt']) ?></button>
  <button id="xMd" class="pri"><?= $h($TX['exportMd']) ?></button>
</div>
<details class="leg"><summary><?= $h($TX['help']) ?></summary><p style="margin-top:6px"><?= $TX['legend'] ?></p></details>
<div class="panels" id="panels"></div>
<div class="panel" id="joined"><div class="ph"><span class="who">🔀 <?= $h($TX['joined']) ?></span></div><div class="log" id="jlog"></div></div>
</div>
<script>
const TX = <?= json_encode($TX, JSON_UNESCAPED_UNICODE) ?>;
const $ = id => document.getElementById(id);
const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const pad = (n, k = 2) => String(n).padStart(k, '0');
const hora = t => { const d = new Date(t); return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}.${pad(d.getMilliseconds(), 3)}`; };
const MAXL = 50000;                 // líneas por panel (de sobra para horas de prueba)
const panels = [];
let sinGuardar = false;

// ---------- colores: una línea → HTML ----------
function pinta(raw){
  let s = esc(raw);
  if (/^(ESP-ROM|Build:|rst:|SPIWP|mode:|load:|entry |ets |configsip|clk_drv)/.test(raw)) return `<span class="k-boot">${s}</span>`;
  if (/^\s*=====|^\s+WTTC \d|·\s*control de la Webasto/.test(raw)) return `<span class="k-motd">${s}</span>`;
  // Webasto falsa: [hh:mm:ss.mmm] (sim …) MARCA texto
  const m = s.match(/^(\[\d{2}:\d{2}:\d{2}\.\d{3}(?: · sim [\d:]+)?\] )(&lt;|&gt;|[.!$=])( .*)$/);
  let pre = '', cuerpo = s, cls = '';
  if (m) {
    pre = `<span class="t">${m[1]}</span>`;
    const k = m[2];
    cls = k === '&lt;' ? 'k-req' : k === '&gt;' ? 'k-res' : k === '!' ? 'k-warn' : k === '$' ? 'k-cmd' : k === '=' ? 'k-st' : 'k-ev';
    cuerpo = `<b class="${cls}">${k}</b>${m[3]}`;
  }
  cuerpo = cuerpo
    .replace(/((?:\b[0-9A-F]{2} ){3,}[0-9A-F]{2}\b)/g, '<span class="k-hex">$1</span>')
    .replace(/(estado: )([^→]+→ [^(]+)/, '$1<span class="k-state">$2</span>')
    .replace(/(AVER[IÍ]A[^·]*|averías?: (?!ninguna)[^·]+|no responde[^·]*|no contesta[^·]*|NO contesta[^·]*|[Ee]rror[^·]*|falló[^·]*|FALLA[^·]*|bloquead[ao][^·]*|⛔[^·]*)/g, '<span class="k-bad">$1</span>')
    .replace(/(todo correcto ✅|¡llama!|vuelve a responder|confirmado)/g, '<span class="k-good">$1</span>')
    .replace(/(Encendida \([^)]*\)|Apagada \([^)]*\))/g, '<span class="k-on">$1</span>')
    .replace(/(-?\d+(?:[.,]\d+)?)( ?(?:°C|V|W|Ω|ppm|min|ms|s|l|%|KB))(?![\w])/g, '<span class="k-num">$1$2</span>');
  return pre + cuerpo;
}

// ---------- qué placa es ----------
function identifica(p, raw){
  if (p.tipo) return;
  if (/Webasto falsa|^\[\d{2}:\d{2}:\d{2}\.\d{3}\] [<>.!$=] |Órdenes de la consola/.test(raw)) ponTipo(p, 'fake');
  else if (/WTTC \d+\.\d+|control de la Webasto|Motivo del arranque|PIN Bluetooth|Bluetooth \(app WTTC\)|Comandos: on/.test(raw)) ponTipo(p, 'wttc');
}
function ponTipo(p, t){
  p.tipo = t;
  p.el.querySelector('.badge').className = 'badge ' + t;
  p.el.querySelector('.badge').textContent = TX[t];
  pintaJunto();
}

// ---------- un panel = un puerto ----------
function nuevoPanel(){
  const id = panels.length + 1;
  const el = document.createElement('div');
  el.className = 'panel';
  el.innerHTML = `<div class="ph"><span class="who"><span class="dot"></span>${esc(TX.panel)} ${id} <span class="badge">${esc(TX.unknown)}</span></span>
      <select class="baud" title="Baudios"><option>115200</option><option>9600</option><option>57600</option><option>230400</option><option>921600</option></select>
      <button class="pri con">${esc(TX.connect)}</button><button class="rst" disabled>${esc(TX.reset)}</button>
      <button class="pau">${esc(TX.pause)}</button><button class="clr">${esc(TX.clear)}</button>
      <input type="search" class="fil" placeholder="${esc(TX.filter)}" style="width:130px"></div>
    <div class="log"><span class="ln k-mine">${esc(TX.waiting)}</span></div>
    <div class="pf"><input type="text" class="cmd" placeholder="${esc(TX.cmdPh)}" disabled><button class="snd" disabled>${esc(TX.send)}</button></div>
    <div class="foot"><span class="cnt">0</span> ${esc(TX.lines)}</div>`;
  $('panels').appendChild(el);
  const p = {id, el, port: null, reader: null, lines: [], tipo: '', pausa: false, filtro: '', hist: [], hi: 0, buf: '', abierto: false, t0: 0};
  panels.push(p);
  const q = s => el.querySelector(s);
  q('.con').onclick = () => p.abierto ? desconecta(p) : conecta(p);
  q('.rst').onclick = () => reinicia(p);
  q('.clr').onclick = () => { p.lines = []; q('.log').innerHTML = ''; q('.cnt').textContent = 0; pintaJunto(); };
  q('.pau').onclick = () => { p.pausa = !p.pausa; q('.pau').textContent = p.pausa ? TX.resume : TX.pause; if (!p.pausa) repinta(p); };
  q('.fil').oninput = e => { p.filtro = e.target.value.toLowerCase(); repinta(p); };
  const cmd = q('.cmd');
  const enviar = () => { const v = cmd.value; if (!v.trim()) return; escribe(p, v); p.hist.push(v); p.hi = p.hist.length; cmd.value = ''; };
  q('.snd').onclick = enviar;
  cmd.onkeydown = e => {
    if (e.key === 'Enter') enviar();
    else if (e.key === 'ArrowUp' && p.hist.length) { p.hi = Math.max(0, p.hi - 1); cmd.value = p.hist[p.hi]; e.preventDefault(); }
    else if (e.key === 'ArrowDown' && p.hist.length) { p.hi = Math.min(p.hist.length, p.hi + 1); cmd.value = p.hist[p.hi] || ''; e.preventDefault(); }
  };
  $('bJoin').disabled = panels.length < 2;
  return p;
}

function linea(p, txt, mia){
  const l = {t: Date.now(), s: txt, mia: !!mia};
  p.lines.push(l);
  if (p.lines.length > MAXL) p.lines.splice(0, p.lines.length - MAXL);
  if (!mia) identifica(p, txt);
  sinGuardar = true;
  p.el.querySelector('.cnt').textContent = p.lines.length;
  if (!p.pausa && (!p.filtro || txt.toLowerCase().includes(p.filtro))) {
    const box = p.el.querySelector('.log');
    const abajo = box.scrollHeight - box.scrollTop - box.clientHeight < 40;
    box.insertAdjacentHTML('beforeend', htmlLinea(l));
    while (box.childElementCount > 4000) box.firstElementChild.remove();      // lo visible; el registro guarda todo
    if (abajo) box.scrollTop = box.scrollHeight;
  }
  if ($('joined').classList.contains('on')) anadeJunto(p, l);
}
const htmlLinea = (l, src) => `<span class="ln">${$('stamps').checked ? `<span class="t">${hora(l.t)} </span>` : ''}${src || ''}${l.mia ? `<span class="k-mine">» ${esc(l.s)}</span>` : pinta(l.s)}</span>`;
function repinta(p){
  const box = p.el.querySelector('.log');
  const ls = p.lines.filter(l => !p.filtro || l.s.toLowerCase().includes(p.filtro)).slice(-4000);
  box.innerHTML = ls.map(l => htmlLinea(l)).join('');
  box.scrollTop = box.scrollHeight;
}

async function conecta(p){
  if (!('serial' in navigator)) return;
  try {
    const port = p.port || await navigator.serial.requestPort();
    await port.open({baudRate: +p.el.querySelector('.baud').value, bufferSize: 65536});
    p.port = port; p.abierto = true; p.t0 = p.t0 || Date.now();
    estado(p, true);
    lee(p);
    // Si en 2,5 s no se ha presentado, se le pregunta: «info» es inofensivo en las dos placas
    setTimeout(() => { if (p.abierto && !p.tipo) { linea(p, TX.probe, true); escribe(p, 'info', true); } }, 2500);
  } catch (e) {
    if (e.name !== 'NotFoundError') linea(p, (e.name === 'InvalidStateError' || e.name === 'NetworkError' ? TX.busy : e.message), true);
  }
}
function estado(p, on){
  const q = s => p.el.querySelector(s);
  q('.dot').classList.toggle('on', on);
  q('.con').textContent = on ? TX.disconnect : (p.port ? TX.reconnect : TX.connect);
  q('.con').classList.toggle('pri', !on);
  q('.baud').disabled = on;
  ['.rst', '.cmd', '.snd'].forEach(s => q(s).disabled = !on);
}
async function lee(p){
  const dec = new TextDecoder();
  while (p.abierto && p.port.readable) {
    p.reader = p.port.readable.getReader();
    try {
      for (;;) {
        const {value, done} = await p.reader.read();
        if (done) break;
        p.buf += dec.decode(value, {stream: true});
        let i;
        while ((i = p.buf.search(/\r?\n/)) >= 0) {
          const l = p.buf.slice(0, i).replace(/\r$/, '');
          p.buf = p.buf.slice(p.buf[i] === '\r' ? i + 2 : i + 1);
          linea(p, l);
        }
        if (p.buf.length > 4000) { linea(p, p.buf); p.buf = ''; }
      }
    } catch (e) {
      if (p.abierto) linea(p, TX.lost + ' (' + e.message + ')', true);
      break;
    } finally { try { p.reader.releaseLock(); } catch (e) {} }
  }
  if (p.abierto) { p.abierto = false; try { await p.port.close(); } catch (e) {} estado(p, false); }
}
async function desconecta(p){
  p.abierto = false;
  try { await p.reader.cancel(); } catch (e) {}
  try { await p.port.close(); } catch (e) {}
  estado(p, false);
}
async function escribe(p, txt, silencio){
  if (!p.port || !p.port.writable) return;
  if (!silencio) linea(p, txt, true);
  const w = p.port.writable.getWriter();
  try { await w.write(new TextEncoder().encode(txt + '\r\n')); } finally { w.releaseLock(); }
}
// Reiniciar: como el botón RST (esptool): EN a masa un momento por RTS, con DTR suelto (si no, entra en modo grabación)
async function reinicia(p){
  try {
    await p.port.setSignals({dataTerminalReady: false, requestToSend: true});
    await new Promise(r => setTimeout(r, 120));
    await p.port.setSignals({dataTerminalReady: false, requestToSend: false});
    p.tipo = ''; p.el.querySelector('.badge').className = 'badge'; p.el.querySelector('.badge').textContent = TX.unknown;
  } catch (e) { linea(p, e.message, true); }
}

// ---------- las dos por hora ----------
const srcTag = p => `<span class="src ${p.tipo}">[${esc(p.tipo ? TX[p.tipo] : TX.panel + ' ' + p.id)}]</span>`;
function pintaJunto(){
  if (!$('joined').classList.contains('on')) return;
  const todas = [];
  panels.forEach(p => p.lines.forEach(l => todas.push([l, p])));
  todas.sort((a, b) => a[0].t - b[0].t);
  $('jlog').innerHTML = todas.slice(-6000).map(([l, p]) => htmlLinea(l, srcTag(p))).join('');
  $('jlog').scrollTop = $('jlog').scrollHeight;
}
function anadeJunto(p, l){
  const box = $('jlog'), abajo = box.scrollHeight - box.scrollTop - box.clientHeight < 40;
  box.insertAdjacentHTML('beforeend', htmlLinea(l, srcTag(p)));
  if (abajo) box.scrollTop = box.scrollHeight;
}
$('bJoin').onclick = () => { $('joined').classList.toggle('on'); pintaJunto(); };
$('stamps').onchange = () => { panels.forEach(repinta); pintaJunto(); };

// ---------- exportar ----------
// Oculta lo que no debe salir de casa: PIN Bluetooth, claves de la Wi-Fi y de la web, token de Telegram y el código de
// instalación (con él se ven las estadísticas de la placa)
function tapa(s){
  if (!$('redact').checked) return s;
  return s.replace(/(PIN\s*)(\d{6})/gi, '$1······')
    .replace(/(clave|password|contraseña|pass|Kennwort|Passwort)(\s*[:=]?\s*")([^"]*)(")/gi, '$1$2••••$4')
    .replace(/(token\S*\s*[:=]?\s*)(\S+)/gi, '$1••••')
    .replace(/\b[0-9A-Z]{4}-[0-9A-Z]{4}-[0-9A-Z]{4}-[0-9A-Z]{4}\b/g, 'XXXX-XXXX-XXXX-XXXX');
}
const nombreP = p => p.tipo ? TX[p.tipo] : TX.panel + ' ' + p.id;
const conLineas = () => panels.filter(p => p.lines.length);
const fechaFich = () => { const d = new Date(); return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}-${pad(d.getHours())}${pad(d.getMinutes())}`; };
const txtDe = (p, src) => p.lines.map(l => ($('stamps').checked ? hora(l.t) + ' ' : '') + (src ? `[${nombreP(p)}] ` : '') + (l.mia ? '» ' : '') + tapa(l.s));
function bajar(nombre, texto, tipo){
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([texto], {type: tipo + ';charset=utf-8'}));
  a.download = nombre; document.body.appendChild(a); a.click(); a.remove();
  setTimeout(() => URL.revokeObjectURL(a.href), 5000);
  sinGuardar = false;
}
$('xTxt').onclick = () => {
  const ps = conLineas(); if (!ps.length) return;
  const txt = ps.map(p => `===== ${nombreP(p)} =====\n` + txtDe(p).join('\n')).join('\n\n') + '\n';
  bajar(`consola-wttc-${fechaFich()}.txt`, txt, 'text/plain');
};
function resumen(p){
  const L = p.lines.filter(l => !l.mia).map(l => l.s);
  const n = re => L.filter(s => re.test(s)).length;
  // Avisos: en la falsa, sus líneas «!»; en WTTC, las de su registro («17/10 15:42  …») con palabras de fallo. No cuentan
  // la ayuda ni la cabecera (líneas que empiezan por espacios, con palabras como «bloqueada» o «errores»)
  const avisos = L.filter(s => /^\[\d{2}:\d{2}:\d{2}\.\d{3}(?: · sim [\d:]+)?\] ! /.test(s)
    || (/^\d{2}\/\d{2} \d{2}:\d{2} /.test(s) && /AVER[IÍ]A|[Aa]verías: (?!ninguna)|[Ee]rror|no responde|falló|⛔|bloquead|batería baja/.test(s)));
  const estados = L.filter(s => /estado: .*→/.test(s)).map(s => s.replace(/^.*estado: /, ''));
  const t = p.lines.length ? (p.lines[p.lines.length - 1].t - p.lines[0].t) / 1000 : 0;
  return {L, avisos, estados, req: n(/\] < /), res: n(/\] > /), dur: `${Math.floor(t / 60)} min ${Math.round(t % 60)} s`};
}
$('xMd').onclick = () => {
  const ps = conLineas(); if (!ps.length) return;
  const d = new Date();
  let md = `# ${TX.mdTitle} · ${d.toLocaleString()}\n\n`;
  md += `- ${ps.map(p => `**${nombreP(p)}**: ${p.lines.length} ${TX.lines}, ${p.el.querySelector('.baud').value} ${TX.mdBaud}`).join('\n- ')}\n`;
  md += `- ${navigator.userAgent.replace(/^.*?\) /, '')}\n\n## ${TX.mdSummary}\n\n`;
  for (const p of ps) {
    const r = resumen(p);
    md += `### ${nombreP(p)}\n\n- ${TX.mdDur}: ${r.dur}\n`;
    if (p.tipo === 'fake') md += `- ${TX.mdReq}: ${r.req} · ${TX.mdRes}: ${r.res} · ${TX.mdWarnN}: ${r.avisos.length}\n`;
    else md += `- ${TX.mdWarnN}: ${r.avisos.length}\n`;
    if (r.estados.length) md += `- ${TX.mdStates}: ${r.estados.slice(0, 40).map(s => '`' + tapa(s) + '`').join(' · ')}\n`;
    md += '\n';
  }
  md += `## ${TX.mdWarn}\n\n`;
  let alguno = false;
  for (const p of ps) {
    const r = resumen(p);
    if (!r.avisos.length) continue;
    alguno = true;
    md += `**${nombreP(p)}**\n\n` + r.avisos.slice(0, 200).map(s => '- `' + tapa(s).replace(/`/g, "'") + '`').join('\n') + '\n\n';
  }
  if (!alguno) md += TX.mdNone + '\n\n';
  if (ps.length > 1) {
    const todas = [];
    ps.forEach(p => p.lines.forEach(l => todas.push([l, p])));
    todas.sort((a, b) => a[0].t - b[0].t);
    md += `## ${TX.mdJoined}\n\n\`\`\`text\n` + todas.map(([l, p]) => `${hora(l.t)} [${nombreP(p)}] ${l.mia ? '» ' : ''}${tapa(l.s)}`).join('\n') + '\n```\n\n';
  }
  for (const p of ps) md += `## ${TX.mdLog}: ${nombreP(p)}\n\n\`\`\`text\n${txtDe(p).join('\n').replace(/```/g, "'''")}\n\`\`\`\n\n`;
  bajar(`consola-wttc-${fechaFich()}.md`, md, 'text/markdown');
};

// ---------- arranque ----------
if (!('serial' in navigator)) { $('nosup').hidden = false; document.querySelectorAll('.bar button').forEach(b => b.disabled = true); }
nuevoPanel(); nuevoPanel();
$('add').onclick = () => nuevoPanel();
// Puertos ya autorizados en otra visita: se ofrecen en los paneles («Reconectar», sin volver a elegir)
if ('serial' in navigator) navigator.serial.getPorts().then(ps => ps.forEach((port, i) => {
  const p = panels[i] || nuevoPanel(); p.port = port; estado(p, false);
}));
navigator.serial && navigator.serial.addEventListener('disconnect', e => {
  const p = panels.find(p => p.port === e.target); if (p && p.abierto) { p.abierto = false; linea(p, TX.lost, true); estado(p, false); }
});
window.addEventListener('beforeunload', e => { if (sinGuardar) { e.preventDefault(); e.returnValue = TX.leave; } });
</script>
</body>
</html>
