<?php
// wttc/instalar.php — Instalar el firmware de WTTC desde el navegador (ESP Web Tools), en español, inglés y alemán.
// Código y contenido generados íntegramente con Claude (Anthropic).
// URLs: /instalar.php (es), /en/install.php (en) y /de/installieren.php (de); los dos últimos solo fijan $lang.
// Las piezas de cada placa las copia de la última Release /usr/local/bin/wttc-instalador.sh (cron) a
// descargas/instalar/, con su manifest.json. ESP Web Tools (vendor/esp-web-tools, Apache-2.0) graba por Web Serial
// desde el navegador de quien instala: el servidor solo sirve ficheros estáticos.
require_once __DIR__ . '/api/db.php';
wttc_public_only();
$lang = in_array($lang ?? 'es', ['es', 'en', 'de'], true) ? ($lang ?? 'es') : 'es';
wttc_visit($lang === 'es' ? 'instalar' : 'instalar-' . $lang);
$URLS = ['es' => 'https://wttc.favala.es/instalar.php', 'en' => 'https://wttc.favala.es/en/install.php', 'de' => 'https://wttc.favala.es/de/installieren.php'];
$HOME = ['es' => '/', 'en' => '/en/', 'de' => '/de/'];
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$man = @json_decode((string)@file_get_contents(__DIR__ . '/descargas/instalar/manifest.json'), true);
$ver = is_array($man) ? (string)($man['version'] ?? '') : '';
// Manifiesto de esa misma versión (wttc-instalador.sh, desde la 0.3.10): lo que la página dice y lo que se graba
// coinciden aunque salga otra versión con la página abierta. Si no está, el de siempre
$vdir = '/descargas/instalar/' . rawurlencode($ver);
$manW = $ver && is_file(__DIR__ . "$vdir/wttc.json") ? "$vdir/wttc.json" : '/descargas/instalar/manifest.json';
$manF = $ver && is_file(__DIR__ . "$vdir/falsa.json") ? "$vdir/falsa.json" : '';   // la Webasto falsa (0.3.11+)

$TX = [
'es' => [
  'title' => 'Instalar WTTC desde el navegador (ESP32-S3) y la Webasto falsa',
  'desc' => 'Graba el firmware de WTTC en tu ESP32 o ESP32-S3 desde Chrome o Edge, sin Arduino IDE: pincha la placa por USB, pulsa Instalar y en un minuto está lista.',
  'h1' => 'Instalar WTTC desde el navegador',
  'intro' => 'Sin Arduino IDE ni nada que instalar: pincha la placa al ordenador por USB, pulsa <b>Instalar</b> y el navegador graba la última versión de WTTC. WTTC necesita un <b>ESP32-S3</b>; la Webasto falsa (más abajo) vale para cualquier ESP32.',
  'need' => 'Qué necesitas',
  'needList' => '<li><b>Chrome, Edge u Opera en un ordenador</b> (Windows, Linux o macOS). Firefox y Safari no pueden hablar con el puerto USB.</li><li>Un <b>cable USB de datos</b> (muchos de cargar no llevan datos).</li><li>En el <b>ESP32-S3</b>, el USB marcado <b>UART</b> o <b>COM</b> (el otro es el USB del propio chip).</li><li>En Windows, si no aparece ningún puerto, el driver del chip USB: <a href="https://www.silabs.com/developers/usb-to-uart-bridge-vcp-drivers" target="_blank" rel="noopener">CP210x</a> o <a href="https://www.wch.cn/downloads/CH343SER_ZIP.html" target="_blank" rel="noopener">CH343</a>.</li>',
  'btn' => 'Instalar WTTC',
  'version' => 'Versión que se instala: %s',
  'noPkg' => 'Todavía no hay paquete para instalar desde el navegador (llega con la próxima versión). Mientras tanto, instálalo con Arduino IDE: <a href="{home}#descargas">descargas e instrucciones</a>.',
  'unsupported' => 'Este navegador no puede grabar por USB. Abre esta página con Chrome, Edge u Opera en un ordenador.',
  'notAllowed' => 'Hace falta abrir la página por https (como aquí) para grabar por USB.',
  'steps' => 'Pasos',
  'stepsList' => '<li>Pincha la placa al ordenador y pulsa <b>Instalar WTTC</b>. Elige su puerto en la ventana del navegador (suele llamarse «CP2102», «USB Single Serial», «CH343» o similar).</li><li>Pulsa <b>Install</b> y espera un minuto sin desenchufarla. Si se queda en «Connecting», mantén pulsado el botón <b>BOOT</b> de la placa hasta que empiece.</li><li>Al terminar, pulsa <b>Logs &amp; Console</b> y luego el botón <b>RST</b> de la placa: verás un resumen con la versión, el <b>PIN Bluetooth</b>, la Wi-Fi de la placa y su clave, y el usuario y la clave de la web (apúntalos; con <code>info</code> sale otra vez). Ahí mismo puedes escribir <code>status</code> para la primera prueba con la Webasto.</li><li>Empareja la app con ese PIN, o entra en su Wi-Fi <b>WTTC</b>: la primera vez te pedirá cambiar la clave de fábrica.</li>',
  'notes' => 'A tener en cuenta',
  'notesList' => '<li>Reinstalar así <b>no borra</b> la configuración, los programas ni el PIN.</li><li>Las versiones siguientes se instalan sin cable: app o web de la placa → <b>Buscar actualizaciones</b>.</li><li>La ventana de instalación está en inglés (es la herramienta <a href="https://esphome.github.io/esp-web-tools/" target="_blank" rel="noopener">ESP Web Tools</a>).</li><li>Antes de montarla en la furgo, mira el <a href="{home}#montaje">esquema de conexiones</a> y la <a href="{home}#responsabilidad">descarga de responsabilidad</a>.</li>',
  'fTitle' => 'La Webasto falsa: un segundo ESP32 que hace de caldera',
  'fIntro' => 'Para probar WTTC en la mesa, sin furgoneta ni gasoil: otro ESP32 (vale el clásico o un S3) se hace pasar por la Thermo Top C en el W-Bus. Arranca, prende, regula, hace el postbarrido y da averías; por su consola USB (115200) se le provocan fallos y se acelera el tiempo. Se instala igual que WTTC, en <b>la otra placa</b>. Cómo conectarla, en el dibujo de abajo; sus órdenes, en <a href="https://github.com/matatunos/wttc/tree/main/herramientas/webasto_falsa" target="_blank" rel="noopener">su README</a>. De fábrica escucha por IO16, contesta por IO17 y devuelve el eco (conexión directa); para la conexión por el bus, escribe <code>eco no</code> en su consola, y <code>pines 16 17</code> para cambiar los pines.',
  'fBtn' => 'Instalar la Webasto falsa',
  'fNoPkg' => 'Llega con la próxima versión de WTTC. Mientras tanto, con Arduino IDE: herramientas/webasto_falsa del repositorio.',
  'back' => '← WTTC: controla la Thermo Top C desde el móvil',
],
'en' => [
  'title' => 'Install WTTC from the browser (ESP32-S3) and the fake Webasto',
  'desc' => 'Flash the WTTC firmware to your ESP32 or ESP32-S3 from Chrome or Edge, no Arduino IDE needed: plug the board in over USB, click Install and it is ready in a minute.',
  'h1' => 'Install WTTC from the browser',
  'intro' => 'No Arduino IDE and nothing to install: plug the board into your computer over USB, click <b>Install</b> and the browser flashes the latest WTTC version. WTTC needs an <b>ESP32-S3</b>; the fake Webasto (below) works on any ESP32.',
  'need' => 'What you need',
  'needList' => '<li><b>Chrome, Edge or Opera on a computer</b> (Windows, Linux or macOS). Firefox and Safari cannot talk to the USB port.</li><li>A <b>USB data cable</b> (many charging cables carry no data).</li><li>On the <b>ESP32-S3</b>, the USB port marked <b>UART</b> or <b>COM</b> (the other one is the chip\'s own USB).</li><li>On Windows, if no port shows up, the driver for the USB chip: <a href="https://www.silabs.com/developers/usb-to-uart-bridge-vcp-drivers" target="_blank" rel="noopener">CP210x</a> or <a href="https://www.wch.cn/downloads/CH343SER_ZIP.html" target="_blank" rel="noopener">CH343</a>.</li>',
  'btn' => 'Install WTTC',
  'version' => 'Version to be installed: %s',
  'noPkg' => 'There is no package to install from the browser yet (it comes with the next version). Meanwhile, install it with Arduino IDE: <a href="{home}#descargas">downloads and instructions</a>.',
  'unsupported' => 'This browser cannot flash over USB. Open this page with Chrome, Edge or Opera on a computer.',
  'notAllowed' => 'The page must be opened over https (as here) to flash over USB.',
  'steps' => 'Steps',
  'stepsList' => '<li>Plug the board into the computer and click <b>Install WTTC</b>. Choose its port in the browser window (usually called “CP2102”, “USB Single Serial”, “CH343” or similar).</li><li>Click <b>Install</b> and wait a minute without unplugging it. If it hangs at “Connecting”, hold the board\'s <b>BOOT</b> button until it starts.</li><li>When it finishes, click <b>Logs &amp; Console</b> and then the board\'s <b>RST</b> button: you will see a summary with the version, the <b>Bluetooth PIN</b>, the board\'s Wi-Fi and its password, and the web user and password (write them down; <code>info</code> shows it again). You can type <code>status</code> right there for the first test with the Webasto.</li><li>Pair the app with that PIN, or join its <b>WTTC</b> Wi-Fi: the first time it asks you to change the factory password.</li>',
  'notes' => 'Good to know',
  'notesList' => '<li>Reinstalling this way <b>does not erase</b> the settings, schedules or PIN.</li><li>Later versions are installed without a cable: app or the board\'s web page → <b>Check for updates</b>.</li><li>The installation window is in English (it is the <a href="https://esphome.github.io/esp-web-tools/" target="_blank" rel="noopener">ESP Web Tools</a> tool).</li><li>Before fitting it in the van, look at the <a href="{home}#montaje">wiring diagram</a> and the <a href="{home}#responsabilidad">disclaimer of liability</a>.</li>',
  'fTitle' => 'The fake Webasto: a second ESP32 acting as the heater',
  'fIntro' => 'To test WTTC on the bench, without a van or diesel: another ESP32 (a classic one or an S3) pretends to be the Thermo Top C on the W-Bus. It starts, ignites, regulates, runs the after-run and reports faults; from its USB console (115200) you trigger failures and speed up time. It installs like WTTC, on <b>the other board</b>. How to wire it, in the drawing below; its commands, in <a href="https://github.com/matatunos/wttc/tree/main/herramientas/webasto_falsa" target="_blank" rel="noopener">its README</a>. By default it listens on IO16, answers on IO17 and returns the echo (direct wiring); for wiring through the bus, type <code>eco no</code> in its console, and <code>pines 16 17</code> to change the pins.',
  'fBtn' => 'Install the fake Webasto',
  'fNoPkg' => 'It comes with the next WTTC version. Meanwhile, use Arduino IDE: herramientas/webasto_falsa in the repository.',
  'back' => '← WTTC: control the Thermo Top C from your phone',
],
'de' => [
  'title' => 'WTTC im Browser installieren (ESP32-S3) und die falsche Webasto',
  'desc' => 'Die WTTC-Firmware aus Chrome oder Edge auf deinen ESP32 oder ESP32-S3 flashen, ohne Arduino IDE: Platine per USB anschließen, Installieren klicken, in einer Minute fertig.',
  'h1' => 'WTTC im Browser installieren',
  'intro' => 'Ohne Arduino IDE und ohne etwas zu installieren: die Platine per USB an den Computer anschließen, <b>Installieren</b> klicken, und der Browser flasht die neueste WTTC-Version. WTTC braucht einen <b>ESP32-S3</b>; die falsche Webasto (weiter unten) läuft auf jedem ESP32.',
  'need' => 'Was du brauchst',
  'needList' => '<li><b>Chrome, Edge oder Opera auf einem Computer</b> (Windows, Linux oder macOS). Firefox und Safari können nicht mit dem USB-Anschluss sprechen.</li><li>Ein <b>USB-Datenkabel</b> (viele Ladekabel übertragen keine Daten).</li><li>Beim <b>ESP32-S3</b> den mit <b>UART</b> oder <b>COM</b> beschrifteten USB-Anschluss (der andere ist das USB des Chips selbst).</li><li>Unter Windows, falls kein Port erscheint, den Treiber des USB-Chips: <a href="https://www.silabs.com/developers/usb-to-uart-bridge-vcp-drivers" target="_blank" rel="noopener">CP210x</a> oder <a href="https://www.wch.cn/downloads/CH343SER_ZIP.html" target="_blank" rel="noopener">CH343</a>.</li>',
  'btn' => 'WTTC installieren',
  'version' => 'Zu installierende Version: %s',
  'noPkg' => 'Noch gibt es kein Paket für die Installation im Browser (es kommt mit der nächsten Version). Bis dahin mit der Arduino IDE installieren: <a href="{home}#descargas">Downloads und Anleitung</a>.',
  'unsupported' => 'Dieser Browser kann nicht per USB flashen. Diese Seite mit Chrome, Edge oder Opera auf einem Computer öffnen.',
  'notAllowed' => 'Zum Flashen per USB muss die Seite über https geöffnet sein (wie hier).',
  'steps' => 'Schritte',
  'stepsList' => '<li>Die Platine an den Computer anschließen und <b>WTTC installieren</b> klicken. Im Browserfenster ihren Port wählen (meist „CP2102“, „USB Single Serial“, „CH343“ o. ä.).</li><li><b>Install</b> klicken und eine Minute warten, ohne sie abzuziehen. Bleibt es bei „Connecting“ hängen, die Taste <b>BOOT</b> der Platine gedrückt halten, bis es losgeht.</li><li>Danach <b>Logs &amp; Console</b> klicken und dann die Taste <b>RST</b> der Platine: eine Übersicht mit Version, <b>Bluetooth-PIN</b>, WLAN der Platine und Passwort sowie Web-Benutzer und -Passwort erscheint (notieren; <code>info</code> zeigt sie erneut). Dort kannst du gleich <code>status</code> für den ersten Test mit der Webasto eingeben.</li><li>Die App mit dieser PIN koppeln oder ihr WLAN <b>WTTC</b> verbinden: beim ersten Mal verlangt sie, das Passwort ab Werk zu ändern.</li>',
  'notes' => 'Gut zu wissen',
  'notesList' => '<li>Eine Neuinstallation auf diese Weise <b>löscht nicht</b> Einstellungen, Zeitpläne oder PIN.</li><li>Spätere Versionen werden ohne Kabel installiert: App oder Webseite der Platine → <b>Nach Updates suchen</b>.</li><li>Das Installationsfenster ist auf Englisch (es ist das Werkzeug <a href="https://esphome.github.io/esp-web-tools/" target="_blank" rel="noopener">ESP Web Tools</a>).</li><li>Vor dem Einbau den <a href="{home}#montaje">Schaltplan</a> und den <a href="{home}#responsabilidad">Haftungsausschluss</a> ansehen.</li>',
  'fTitle' => 'Die falsche Webasto: ein zweiter ESP32 als Heizung',
  'fIntro' => 'Um WTTC auf dem Tisch zu testen, ohne Bus und ohne Diesel: ein weiterer ESP32 (ein klassischer oder ein S3) gibt sich am W-Bus als Thermo Top C aus. Er startet, zündet, regelt, macht den Nachlauf und meldet Fehler; über seine USB-Konsole (115200) löst man Fehler aus und beschleunigt die Zeit. Er wird wie WTTC installiert, auf <b>der anderen Platine</b>. Wie man ihn anschließt, zeigt die Zeichnung unten; seine Befehle stehen in <a href="https://github.com/matatunos/wttc/tree/main/herramientas/webasto_falsa" target="_blank" rel="noopener">seiner README</a>. Ab Werk hört er auf IO16, antwortet auf IO17 und gibt das Echo zurück (direkte Verbindung); für die Verbindung über den Bus in seiner Konsole <code>eco no</code> eingeben, und <code>pines 16 17</code>, um die Pins zu ändern.',
  'fBtn' => 'Falsche Webasto installieren',
  'fNoPkg' => 'Kommt mit der nächsten WTTC-Version. Bis dahin mit der Arduino IDE: herramientas/webasto_falsa im Repository.',
  'back' => '← WTTC: Thermo Top C per Handy steuern',
],
];
$T = $TX[$lang];
$tr = fn($k) => strtr($T[$k], ['{home}' => $HOME[$lang]]);
?><!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $h($T['title']) ?></title>
<meta name="description" content="<?= $h($T['desc']) ?>">
<link rel="canonical" href="<?= $URLS[$lang] ?>">
<?php foreach ($URLS as $l => $u): ?><link rel="alternate" hreflang="<?= $l ?>" href="<?= $u ?>">
<?php endforeach; ?><link rel="alternate" hreflang="x-default" href="<?= $URLS['en'] ?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="WTTC">
<meta property="og:url" content="<?= $URLS[$lang] ?>">
<meta property="og:title" content="<?= $h($T['h1']) ?>">
<meta property="og:description" content="<?= $h($T['desc']) ?>">
<meta property="og:image" content="https://wttc.favala.es/og.png">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="stylesheet" href="/montaje.css">
<?php if ($ver): ?><script type="module" src="/vendor/esp-web-tools/10.4.0/install-button.js"></script><?php endif; ?>
<style>
  :root{--bg-page:#0f1117;--bg-card:#1a1d27;--text:#e2e8f8;--muted:#7a84a8;--border:#2e3350;--warn:#e0a93a;--warn-bg:rgba(224,169,58,.12);
    --fl:#ff9f1c;--ice:#5bc0eb;--acc:#3a8ee0}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:var(--bg-page);color:var(--text);font:15px/1.55 -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:20px 16px}
  .wrap{max-width:820px;margin:0 auto}
  a{color:var(--ice)} a.back{color:var(--muted);font-size:.85rem;text-decoration:none}
  .langs{float:right;display:flex;gap:4px;font-size:.82rem}
  .langs a{padding:3px 9px;border-radius:999px;border:1px solid var(--border);color:var(--muted);text-decoration:none}
  .langs a[aria-current]{background:var(--bg-card);color:var(--text);border-color:var(--acc)}
  h1{font-size:1.5rem;font-weight:800;margin:14px 0 8px;background:linear-gradient(135deg,#ff9f1c,#5bc0eb);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  h2{font-size:1.05rem;margin:22px 0 8px}
  p,li{color:#c9d1e8} ol,ul{padding-left:22px} li{margin:6px 0}
  .card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:18px;margin:16px 0;text-align:center}
  .card .muted{color:var(--muted);font-size:.85rem;margin-top:10px}
  .aviso{background:var(--warn-bg);border:1px solid var(--warn);border-radius:12px;padding:10px 14px;font-size:.9rem;text-align:left}
  esp-web-install-button button.go{background:var(--fl);color:#1a1000;border:0;border-radius:12px;padding:14px 28px;font:inherit;
    font-size:1.05rem;font-weight:700;cursor:pointer}
  esp-web-install-button button.go:hover{filter:brightness(1.08)}
  code{font-family:ui-monospace,monospace;font-size:.88em}
  .wirebox{margin:12px 0}
  @media (min-width:700px){svg.wire{min-width:0}}   /* aquí cabe entero: se ajusta al ancho (en el móvil, se desliza) */
</style>
</head>
<body><div class="wrap">
<nav class="langs" aria-label="Idioma · Language · Sprache"><?php foreach (['es' => 'Español', 'en' => 'English', 'de' => 'Deutsch'] as $l => $n): ?>
  <a href="<?= substr($URLS[$l], strlen('https://wttc.favala.es')) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>"<?= $l === $lang ? ' aria-current="page"' : '' ?>><?= $n ?></a><?php endforeach; ?>
</nav>
<a class="back" href="<?= $HOME[$lang] ?>"><?= $h($T['back']) ?></a>
<h1><?= $h($T['h1']) ?></h1>
<p><?= $T['intro'] ?></p>
<?php
// Botón de ESP Web Tools para un manifiesto. $ev: prefijo de los eventos de estadísticas (api/evento.php)
$installBtn = function (string $manifest, string $label, string $ev) use ($h, $T) { ?>
  <esp-web-install-button manifest="<?= $h($manifest) ?>" data-ev="<?= $h($ev) ?>">
    <button slot="activate" class="go">⚡ <?= $h($label) ?></button>
    <div slot="unsupported" class="aviso"><?= $h($T['unsupported']) ?></div>
    <div slot="not-allowed" class="aviso"><?= $h($T['notAllowed']) ?></div>
  </esp-web-install-button>
<?php }; ?>
<div class="card">
<?php if ($ver): ?>
  <?php $installBtn($manW, $T['btn'], ''); ?>
  <p class="muted"><?= $h(sprintf($T['version'], $ver)) ?></p>
<?php else: ?>
  <div class="aviso"><?= $tr('noPkg') ?></div>
<?php endif; ?>
</div>
<h2><?= $h($T['need']) ?></h2>
<ul><?= $T['needList'] ?></ul>
<h2><?= $h($T['steps']) ?></h2>
<ol><?= $T['stepsList'] ?></ol>
<h2><?= $h($T['notes']) ?></h2>
<ul><?= $tr('notesList') ?></ul>
<h2 id="webasto-falsa"><?= $h($T['fTitle']) ?></h2>
<p><?= $tr('fIntro') ?></p>
<?php $version = $ver; require __DIR__ . '/i18n/t.php'; ?>
<!-- Cómo conectarla con la placa de WTTC: el mismo dibujo que la portada (falsa.inc.php) -->
<?php require __DIR__ . '/falsa.inc.php'; ?>
<div class="card">
<?php if ($manF): ?>
  <?php $installBtn($manF, $T['fBtn'], 'falsa'); ?>
  <p class="muted"><?= $h(sprintf($T['version'], $ver)) ?> · ESP32 / ESP32-S3</p>
<?php else: ?>
  <div class="aviso"><?= $h($T['fNoPkg']) ?></div>
<?php endif; ?>
</div>
<?php if ($ver): ?>
<script>
  // Estadísticas (api/evento.php: solo sumas por día, sin cookies ni IP): cada «Instalar» pulsado y cómo termina.
  // ESP Web Tools no avisa a la página, pero su ventana (ewt-install-dialog, que se añade al body) guarda el estado en
  // _installState: «preparing», «writing», «finished», «error». Se mira cada segundo mientras está abierta. Depende de
  // la versión alojada (10.4.0): al cambiarla, comprobar que la propiedad sigue existiendo. p=falsa: la Webasto falsa
  let ev = '';
  document.querySelectorAll('esp-web-install-button').forEach(b => b.querySelector('button.go').addEventListener('click', () => {
    ev = b.dataset.ev;
    fetch('/api/evento.php?e=instalar' + (ev ? '&p=' + ev : ''), { keepalive: true }).catch(() => {});
  }));
  new MutationObserver(ms => ms.forEach(m => m.addedNodes.forEach(n => {
    if (n.tagName !== 'EWT-INSTALL-DIALOG') return;
    let last = '';
    const t = setInterval(() => {
      if (!n.isConnected) return clearInterval(t);
      const s = n._installState, st = s ? s.state : '';
      if (st === last) return;
      last = st;
      if (st !== 'finished' && st !== 'error') return;
      const c = s.chipFamily === 'ESP32-S3' ? 's3' : s.chipFamily === 'ESP32' ? 'esp32' : 'otro';
      fetch('/api/evento.php?e=' + (st === 'finished' ? 'instalado' : 'fallo') + '&c=' + c + (ev ? '&p=' + ev : ''), { keepalive: true }).catch(() => {});
    }, 1000);
  }))).observe(document.body, { childList: true });
</script>
<?php endif; ?>
</div></body></html>
