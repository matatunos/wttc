<?php
// wttc/averias.php — Códigos de avería de la Webasto Thermo Top C (W-Bus), en español, inglés y alemán.
// Código y contenido generados íntegramente con Claude (Anthropic).
// URLs: /averias.php (es), /en/fault-codes.php (en) y /de/fehlercodes.php (de); los dos últimos solo fijan $lang.
// Nombres de los códigos: tabla de errores de libwbus (wbus/wbus_const.h, Manuel Jander), solo los que aplican a una
// calefacción de agua como la Thermo Top C. «Qué revisar» son indicaciones generales: manda el manual de taller.
require_once __DIR__ . '/api/db.php';
wttc_public_only();
$lang = in_array($lang ?? 'es', ['es', 'en', 'de'], true) ? ($lang ?? 'es') : 'es';
wttc_visit($lang === 'es' ? 'averias' : 'averias-' . $lang);
$URLS = ['es' => 'https://wttc.favala.es/averias.php', 'en' => 'https://wttc.favala.es/en/fault-codes.php', 'de' => 'https://wttc.favala.es/de/fehlercodes.php'];
$HOME = ['es' => '/', 'en' => '/en/', 'de' => '/de/'];
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

// ---------- textos de la página ----------
$TX = [
'es' => [
  'title' => 'Códigos de avería Webasto Thermo Top C y qué revisar',
  'desc' => 'Códigos de avería de la Webasto Thermo Top C (VW T5 y otras): 0x02 no arranca, 0x07 bloqueada, 0x12 W-Bus… Qué significa cada uno y qué revisar.',
  'h1' => 'Códigos de avería de la Webasto Thermo Top C',
  'intro' => 'La Thermo Top C guarda sus averías en memoria y las da por el <b>W-Bus</b> (orden <code>0x56 01</code>). Los códigos son los mismos que lee VCDS en el módulo 18 (calefacción auxiliar), la app y la web de <a href="{home}">WTTC</a> o cualquier lector W-Bus. Cada código va en hexadecimal; muchos tienen dos versiones: <b>0x0_</b> para cortocircuito y <b>0x8_</b> para circuito abierto (cable cortado o conector suelto).',
  'aviso' => '<b>Antes de tocar nada:</b> desconecta la calefacción y deja que se enfríe. Estas indicaciones son orientativas y generales; si no te manejas con seguridad en 12 V, gasoil o el circuito de refrigeración, llévala a un taller. Nunca la pruebes en un garaje cerrado: el escape tiene monóxido de carbono.',
  'thCode' => 'Código', 'thName' => 'Significado', 'thCheck' => 'Qué revisar',
  'howTitle' => 'Cómo leer y borrar las averías',
  'how' => '<li><b>Con WTTC:</b> en la app, «Diagnóstico → Leer averías»; en la web de la placa, «Diagnóstico → Leer averías». Si se apaga sola, la app y Telegram ya avisan con sus códigos.</li><li><b>Con VCDS / OBD:</b> módulo 18 «Calefacción auxiliar». Desde ahí también se borran.</li><li><b>Prueba sin hardware:</b> en el <a href="{home}">simulador</a> marca «Sin gasoil / bomba atascada»: verás cómo aparece la 0x02 y, tras tres arranques fallidos, la 0x07.</li>',
  'lockTitle' => 'Si está bloqueada (0x07 o 0x87)',
  'lock' => 'Tras varios arranques fallidos seguidos o un sobrecalentamiento, la Thermo Top C se bloquea y deja de obedecer hasta que se desbloquea. Se hace borrando la memoria de averías con un equipo de diagnóstico (VCDS, módulo 18) o con el procedimiento que indique el manual de tu instalación. Antes, busca la causa: si no, volverá a bloquearse.',
  'source' => 'Nombres de los códigos según la tabla de errores de <a href="https://sourceforge.net/p/libwbus/libwbus/ci/master/tree/wbus/wbus_const.h" target="_blank" rel="noopener">libwbus</a>. Webasto y Thermo Top son marcas de sus propietarios; WTTC no tiene relación con ellos.',
  'back' => '← WTTC: controla la Thermo Top C desde el móvil',
],
'en' => [
  'title' => 'Webasto Thermo Top C fault codes and what to check',
  'desc' => 'Fault codes of the Webasto Thermo Top C heater (VW T5 and others): 0x02 no start, 0x07 locked out, 0x12 W-Bus… What each means and what to check.',
  'h1' => 'Webasto Thermo Top C fault codes',
  'intro' => 'The Thermo Top C stores its faults in memory and reports them over the <b>W-Bus</b> (command <code>0x56 01</code>). The codes are the same ones VCDS reads on module 18 (auxiliary heater), the <a href="{home}">WTTC</a> app and web page, or any W-Bus reader. Each code is in hexadecimal; many come in two versions: <b>0x0_</b> for a short circuit and <b>0x8_</b> for an open circuit (broken wire or loose connector).',
  'aviso' => '<b>Before touching anything:</b> disconnect the heater and let it cool down. These hints are general guidance only; if you are not confident working safely with 12 V, diesel or the cooling circuit, take it to a workshop. Never test it in an enclosed garage: the exhaust contains carbon monoxide.',
  'thCode' => 'Code', 'thName' => 'Meaning', 'thCheck' => 'What to check',
  'howTitle' => 'How to read and clear the faults',
  'how' => '<li><b>With WTTC:</b> in the app, “Diagnostics → Read faults”; on the board\'s web page, “Diagnostics → Read faults”. If it switches itself off, the app and Telegram already notify you with its codes.</li><li><b>With VCDS / OBD:</b> module 18 “Auxiliary heater”. You can also clear them from there.</li><li><b>Try it without hardware:</b> in the <a href="{home}">simulator</a> tick “No diesel / pump stuck”: you will see 0x02 appear and, after three failed starts, 0x07.</li>',
  'lockTitle' => 'If it is locked out (0x07 or 0x87)',
  'lock' => 'After several failed starts in a row or overheating, the Thermo Top C locks itself out and stops obeying until it is unlocked. This is done by clearing the fault memory with a diagnostic tool (VCDS, module 18) or with the procedure given in your installation\'s manual. Find the cause first: otherwise it will lock out again.',
  'source' => 'Code names according to the error table of <a href="https://sourceforge.net/p/libwbus/libwbus/ci/master/tree/wbus/wbus_const.h" target="_blank" rel="noopener">libwbus</a>. Webasto and Thermo Top are trademarks of their owners; WTTC is not related to them.',
  'back' => '← WTTC: control the Thermo Top C from your phone',
],
'de' => [
  'title' => 'Webasto Thermo Top C Fehlercodes und was zu prüfen ist',
  'desc' => 'Fehlercodes der Standheizung Webasto Thermo Top C (VW T5 u. a.): 0x02 kein Start, 0x07 verriegelt, 0x12 W-Bus… Bedeutung und was zu prüfen ist.',
  'h1' => 'Fehlercodes der Webasto Thermo Top C',
  'intro' => 'Die Thermo Top C speichert ihre Fehler und meldet sie über den <b>W-Bus</b> (Befehl <code>0x56 01</code>). Es sind dieselben Codes, die VCDS im Steuergerät 18 (Zusatzheizung) ausliest, die App und Webseite von <a href="{home}">WTTC</a> oder jedes W-Bus-Lesegerät. Jeder Code ist hexadezimal; viele gibt es zweimal: <b>0x0_</b> für Kurzschluss und <b>0x8_</b> für Unterbrechung (Kabel gebrochen oder Stecker lose).',
  'aviso' => '<b>Bevor du etwas anfasst:</b> die Heizung trennen und abkühlen lassen. Diese Hinweise sind nur allgemeine Orientierung; wenn du nicht sicher mit 12 V, Diesel oder dem Kühlkreislauf umgehst, bring sie in eine Werkstatt. Nie in einer geschlossenen Garage testen: das Abgas enthält Kohlenmonoxid.',
  'thCode' => 'Code', 'thName' => 'Bedeutung', 'thCheck' => 'Was prüfen',
  'howTitle' => 'Fehler auslesen und löschen',
  'how' => '<li><b>Mit WTTC:</b> in der App „Diagnose → Fehler auslesen“; auf der Webseite der Platine „Diagnose → Fehler auslesen“. Schaltet sie sich selbst ab, melden App und Telegram das schon mit den Codes.</li><li><b>Mit VCDS / OBD:</b> Steuergerät 18 „Zusatzheizung“. Dort lassen sie sich auch löschen.</li><li><b>Ohne Hardware ausprobieren:</b> im <a href="{home}">Simulator</a> „Kein Diesel / Pumpe klemmt“ ankreuzen: du siehst 0x02 erscheinen und nach drei Fehlstarts 0x07.</li>',
  'lockTitle' => 'Wenn sie verriegelt ist (0x07 oder 0x87)',
  'lock' => 'Nach mehreren Fehlstarts hintereinander oder einer Überhitzung verriegelt sich die Thermo Top C und gehorcht nicht mehr, bis sie entriegelt wird. Das geschieht durch Löschen des Fehlerspeichers mit einem Diagnosegerät (VCDS, Steuergerät 18) oder mit dem Verfahren aus dem Handbuch deiner Installation. Vorher die Ursache suchen: sonst verriegelt sie sich wieder.',
  'source' => 'Namen der Codes laut Fehlertabelle von <a href="https://sourceforge.net/p/libwbus/libwbus/ci/master/tree/wbus/wbus_const.h" target="_blank" rel="noopener">libwbus</a>. Webasto und Thermo Top sind Marken ihrer Inhaber; WTTC steht in keiner Beziehung zu ihnen.',
  'back' => '← WTTC: Thermo Top C per Handy steuern',
],
];

// ---------- códigos: [código, [significado es, en, de], [qué revisar es, en, de]] ----------
$FUEL = ['Combustible: nivel del depósito, tubo y filtro, aire en la línea; bomba dosificadora y su cable; entrada de aire y escape sin obstruir; bujía.',
         'Fuel: tank level, line and filter, air in the line; metering pump and its wiring; air intake and exhaust not blocked; glow plug.',
         'Kraftstoff: Tankfüllstand, Leitung und Filter, Luft in der Leitung; Dosierpumpe und ihr Kabel; Luftansaugung und Abgas frei; Glühstift.'];
$CODES = [
  ['01', ['Unidad de control defectuosa', 'Defective control unit', 'Steuergerät defekt'],
         ['Quita y vuelve a poner su fusible; si vuelve a salir, la unidad de control necesita taller.', 'Pull and refit its fuse; if it comes back, the control unit needs a workshop.', 'Sicherung ziehen und wieder einsetzen; kommt er wieder, muss das Steuergerät in die Werkstatt.']],
  ['02', ['No arranca', 'No start', 'Kein Start'], $FUEL],
  ['03', ['Fallo de llama (se apaga durante el arranque)', 'Flame failure (goes out during start-up)', 'Flammabriss (beim Start)'], $FUEL],
  ['83', ['Fallo de llama (en funcionamiento)', 'Flame failure (while running)', 'Flammabriss (im Betrieb)'], $FUEL],
  ['04', ['Tensión de alimentación demasiado alta', 'Supply voltage too high', 'Versorgungsspannung zu hoch'],
         ['Regulador del alternador y tensión con el motor en marcha (no debería pasar de ~15 V).', 'Alternator regulator and voltage with the engine running (should not exceed ~15 V).', 'Lichtmaschinenregler und Spannung bei laufendem Motor (sollte ~15 V nicht übersteigen).']],
  ['84', ['Tensión de funcionamiento demasiado baja', 'Operating voltage too low', 'Betriebsspannung zu niedrig'],
         ['Carga de la batería, bornes, masas y caída de tensión en el cableado (la bujía pide mucha corriente al arrancar).', 'Battery charge, terminals, grounds and voltage drop in the wiring (the glow plug draws a lot of current at start-up).', 'Batterieladung, Pole, Massen und Spannungsabfall in der Verkabelung (der Glühstift zieht beim Start viel Strom).']],
  ['9C', ['Detección inteligente de baja tensión', 'Intelligent undervoltage detection', 'Intelligente Unterspannungserkennung'],
         ['Como la 0x84: batería y conexiones.', 'As for 0x84: battery and connections.', 'Wie bei 0x84: Batterie und Anschlüsse.']],
  ['05', ['Llama detectada antes de la combustión', 'Flame detected before combustion', 'Flamme vor der Verbrennung erkannt'],
         ['Sensor de llama / bujía; restos de gasoil en la cámara tras arranques fallidos.', 'Flame sensor / glow plug; leftover diesel in the chamber after failed starts.', 'Flammwächter / Glühstift; Dieselreste in der Brennkammer nach Fehlstarts.']],
  ['85', ['Llama detectada después de la combustión', 'Flame detected after combustion', 'Flamme nach der Verbrennung erkannt'],
         ['Sensor de llama / bujía y que la bomba dosificadora no gotee al parar.', 'Flame sensor / glow plug, and that the metering pump does not drip when stopping.', 'Flammwächter / Glühstift und ob die Dosierpumpe beim Abschalten nachtropft.']],
  ['06', ['Sobrecalentamiento', 'Overheating', 'Überhitzung'],
         ['Nivel de refrigerante, aire en el circuito, bomba de agua, manguitos doblados o termostato.', 'Coolant level, air in the circuit, water pump, kinked hoses or thermostat.', 'Kühlmittelstand, Luft im Kreislauf, Wasserpumpe, geknickte Schläuche oder Thermostat.']],
  ['07', ['Bloqueada (interlock)', 'Locked out (interlock)', 'Verriegelt (Interlock)'],
         ['Se bloquea tras varios arranques fallidos o un sobrecalentamiento: busca la causa (otros códigos) y desbloquéala (ver abajo).', 'It locks out after several failed starts or overheating: find the cause (other codes) and unlock it (see below).', 'Sie verriegelt nach mehreren Fehlstarts oder Überhitzung: Ursache suchen (andere Codes) und entriegeln (siehe unten).']],
  ['87', ['Bloqueo permanente', 'Permanent lock-out', 'Dauerhafte Verriegelung'],
         ['Igual que la 0x07, pero hace falta equipo de diagnóstico para desbloquearla.', 'As for 0x07, but a diagnostic tool is needed to unlock it.', 'Wie 0x07, aber zum Entriegeln ist ein Diagnosegerät nötig.']],
  ['08', ['Bomba dosificadora: cortocircuito', 'Metering pump: short circuit', 'Dosierpumpe: Kurzschluss'],
         ['Cable y conector de la bomba dosificadora (suele ir junto al depósito).', 'Wiring and connector of the metering pump (usually near the tank).', 'Kabel und Stecker der Dosierpumpe (meist am Tank).']],
  ['88', ['Bomba dosificadora: fallo / circuito abierto', 'Metering pump: failure / open circuit', 'Dosierpumpe: Fehler / Unterbrechung'],
         ['Conector de la bomba dosificadora, cable cortado o bomba averiada.', 'Metering pump connector, broken wire or faulty pump.', 'Stecker der Dosierpumpe, Kabelbruch oder Pumpe defekt.']],
  ['09', ['Ventilador de aire de combustión: cortocircuito', 'Combustion air fan: short circuit', 'Brennluftgebläse: Kurzschluss'],
         ['Motor del ventilador y su cableado.', 'Fan motor and its wiring.', 'Gebläsemotor und seine Verkabelung.']],
  ['89', ['Ventilador de aire de combustión: circuito abierto', 'Combustion air fan: open circuit', 'Brennluftgebläse: Unterbrechung'],
         ['Conector y cableado del ventilador.', 'Fan connector and wiring.', 'Stecker und Verkabelung des Gebläses.']],
  ['15', ['Ventilador de aire de combustión bloqueado', 'Combustion air fan blocked', 'Brennluftgebläse blockiert'],
         ['Algo que impide girar al ventilador (suciedad, óxido, hollín).', 'Something stopping the fan from turning (dirt, rust, soot).', 'Etwas hindert das Gebläse am Drehen (Schmutz, Rost, Ruß).']],
  ['0A', ['Bujía / monitor de llama: cortocircuito', 'Glow plug / flame monitor: short circuit', 'Glühstift / Flammwächter: Kurzschluss'],
         ['Bujía y su cableado.', 'Glow plug and its wiring.', 'Glühstift und seine Verkabelung.']],
  ['8A', ['Bujía / monitor de llama: circuito abierto', 'Glow plug / flame monitor: open circuit', 'Glühstift / Flammwächter: Unterbrechung'],
         ['Bujía fundida o conector suelto.', 'Burnt-out glow plug or loose connector.', 'Glühstift durchgebrannt oder Stecker lose.']],
  ['0B', ['Bomba de agua (circulación): cortocircuito', 'Circulation pump: short circuit', 'Umwälzpumpe: Kurzschluss'],
         ['Bomba de agua y su cableado.', 'Water pump and its wiring.', 'Wasserpumpe und ihre Verkabelung.']],
  ['8B', ['Bomba de agua (circulación): circuito abierto', 'Circulation pump: open circuit', 'Umwälzpumpe: Unterbrechung'],
         ['Conector de la bomba de agua o bomba averiada.', 'Water pump connector or faulty pump.', 'Stecker der Wasserpumpe oder Pumpe defekt.']],
  ['14', ['Sensor de temperatura: cortocircuito', 'Temperature sensor: short circuit', 'Temperaturfühler: Kurzschluss'],
         ['Sensor de temperatura de la calefacción y su cableado.', 'The heater\'s temperature sensor and its wiring.', 'Temperaturfühler der Heizung und seine Verkabelung.']],
  ['94', ['Sensor de temperatura: circuito abierto', 'Temperature sensor: open circuit', 'Temperaturfühler: Unterbrechung'],
         ['Conector del sensor de temperatura o cable cortado.', 'Temperature sensor connector or broken wire.', 'Stecker des Temperaturfühlers oder Kabelbruch.']],
  ['97', ['Posición del sensor de sobrecalentamiento incorrecta', 'Overheat sensor position wrong', 'Position des Überhitzungsfühlers falsch'],
         ['Que el sensor esté bien montado en su alojamiento.', 'That the sensor is correctly seated in its housing.', 'Ob der Fühler richtig in seiner Aufnahme sitzt.']],
  ['12', ['Fallo de comunicación por W-Bus', 'W-Bus communication failure', 'W-Bus-Kommunikationsfehler'],
         ['Cable del W-Bus (el «negro» del conector del mando), masa común y, con WTTC, el módulo TJA1020.', 'The W-Bus wire (the “black” one on the controller connector), common ground and, with WTTC, the TJA1020 module.', 'W-Bus-Kabel (das „schwarze“ am Stecker der Bedieneinheit), gemeinsame Masse und, mit WTTC, das TJA1020-Modul.']],
  ['92', ['Fallo de refresco de la orden', 'Command refresh failure', 'Befehlsauffrischung fehlgeschlagen'],
         ['El mando dejó de mandar el mantenimiento (keep-alive) durante el encendido: cable W-Bus o el propio mando/temporizador.', 'The controller stopped sending the keep-alive while it was running: W-Bus wire or the controller/timer itself.', 'Die Bedieneinheit hat während des Betriebs kein Keep-Alive mehr gesendet: W-Bus-Kabel oder die Bedieneinheit/Zeitschaltuhr selbst.']],
  ['98', ['Interrupción de la alimentación', 'Power supply interruption', 'Unterbrechung der Stromversorgung'],
         ['Fusible, bornes y cableado de +12 V y masa de la calefacción.', 'Fuse, terminals and the heater\'s +12 V and ground wiring.', 'Sicherung, Anschlüsse sowie +12-V- und Masseleitung der Heizung.']],
  ['82', ['No arranca durante la prueba de funcionamiento', 'No start during test run', 'Kein Start beim Testlauf'], $FUEL],
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
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'TechArticle', 'headline' => $T['h1'], 'description' => $T['desc'],
    'inLanguage' => $lang, 'url' => $URLS[$lang], 'about' => 'Webasto Thermo Top C',
    'isPartOf' => ['@type' => 'WebSite', 'name' => 'WTTC', 'url' => 'https://wttc.favala.es' . $HOME[$lang]],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<style>
  :root{--bg-page:#0f1117;--bg-card:#1a1d27;--bg-inner:#13151f;--text:#e2e8f8;--muted:#7a84a8;--border:#2e3350;
    --warn:#e0a93a;--warn-bg:rgba(224,169,58,.12);--fl:#ff9f1c;--ice:#5bc0eb;--acc:#3a8ee0;}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:var(--bg-page);color:var(--text);font:15px/1.55 -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:20px 16px}
  .wrap{max-width:980px;margin:0 auto}
  a{color:var(--ice)}
  a.back{color:var(--muted);font-size:.85rem;text-decoration:none}
  .langs{float:right;display:flex;gap:4px;font-size:.82rem}
  .langs a{padding:3px 9px;border-radius:999px;border:1px solid var(--border);color:var(--muted);text-decoration:none}
  .langs a[aria-current]{background:var(--bg-card);color:var(--text);border-color:var(--acc)}
  h1{font-size:1.5rem;font-weight:800;margin:14px 0 8px;background:linear-gradient(135deg,#ff9f1c,#5bc0eb);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  h2{font-size:1.05rem;margin:22px 0 8px}
  p,li{color:#c9d1e8}
  .aviso{background:var(--warn-bg);border:1px solid var(--warn);border-radius:12px;padding:10px 14px;margin:14px 0;font-size:.9rem}
  .card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:6px 10px;overflow-x:auto}
  table{width:100%;border-collapse:collapse;font-size:.88rem}
  th,td{text-align:left;padding:8px 6px;border-top:1px solid var(--border);vertical-align:top}
  th{color:var(--muted);border-top:0;font-weight:600}
  td.c{font-family:ui-monospace,monospace;font-weight:700;color:var(--fl);white-space:nowrap}
  td.n{font-weight:600;min-width:180px}
  ul{padding-left:20px} li{margin:4px 0}
  .muted{color:var(--muted);font-size:.8rem;margin-top:18px}
  code{font-family:ui-monospace,monospace;font-size:.85em}
</style>
</head>
<body><div class="wrap">
<nav class="langs" aria-label="Idioma · Language · Sprache"><?php foreach (['es' => 'Español', 'en' => 'English', 'de' => 'Deutsch'] as $l => $n): ?>
  <a href="<?= substr($URLS[$l], strlen('https://wttc.favala.es')) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>"<?= $l === $lang ? ' aria-current="page"' : '' ?>><?= $n ?></a><?php endforeach; ?>
</nav>
<a class="back" href="<?= $HOME[$lang] ?>"><?= $h($T['back']) ?></a>
<h1><?= $h($T['h1']) ?></h1>
<p><?= $tr('intro') ?></p>
<div class="aviso" role="note"><?= $tr('aviso') ?></div>
<div class="card"><table>
  <thead><tr><th><?= $T['thCode'] ?></th><th><?= $T['thName'] ?></th><th><?= $T['thCheck'] ?></th></tr></thead>
  <tbody>
<?php $li = ['es' => 0, 'en' => 1, 'de' => 2][$lang];
usort($CODES, fn($a, $b) => (hexdec($a[0]) & 0x7F) <=> (hexdec($b[0]) & 0x7F) ?: hexdec($a[0]) <=> hexdec($b[0]));
foreach ($CODES as [$c, $n, $chk]): ?>
    <tr id="c<?= $c ?>"><td class="c">0x<?= $c ?></td><td class="n"><?= $h($n[$li]) ?></td><td><?= $h($chk[$li]) ?></td></tr>
<?php endforeach; ?>
  </tbody>
</table></div>
<h2><?= $h($T['howTitle']) ?></h2>
<ul><?= $tr('how') ?></ul>
<h2><?= $h($T['lockTitle']) ?></h2>
<p><?= $tr('lock') ?></p>
<p class="muted"><?= $tr('source') ?></p>
</div></body></html>
