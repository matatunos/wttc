<?php
// esquema.php — Esquema de conexiones de WTTC para imprimir (dibujo, lista de cables y pasos), en A4 y en claro.
// Código y contenido generados íntegramente con Claude (Anthropic).
// De esta página salen los PDF descargas/WTTC-esquema-<idioma>.pdf: los hace wttc-publicar.sh con Gotenberg (Chromium)
// cada vez que cambia el esquema. El contenido es el mismo que la sección «Montaje» de la portada (montaje.inc.php).
// Idioma: ?l=es|en|de.
$lang = $_GET['l'] ?? 'es';
if (!in_array($lang, ['es', 'en', 'de'], true)) $lang = 'es';
$version = trim((string)@file_get_contents(__DIR__ . '/descargas/VERSION')) ?: '';
require __DIR__ . '/i18n/t.php';
$home = 'https://wttc.favala.es/' . ($lang === 'es' ? '' : "$lang/");
$date = ['es' => 'j/n/Y', 'en' => 'Y-m-d', 'de' => 'j.n.Y'][$lang];
$foot = [
    'es' => 'Lee antes la descarga de responsabilidad: %s#responsabilidad. Un sensor de CO conectado a la placa no sustituye a un detector de CO homologado con su propia alarma.',
    'en' => 'Read the disclaimer first: %s#responsabilidad. A CO sensor wired to the board does not replace a certified CO detector with its own alarm.',
    'de' => 'Vorher den Haftungsausschluss lesen: %s#responsabilidad. Ein an die Platine angeschlossener CO-Sensor ersetzt keinen zugelassenen CO-Melder mit eigenem Alarm.',
][$lang];
?><!doctype html>
<html lang="<?= $lang ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title>WTTC · <?= htmlspecialchars(strip_tags(t('wiring'))) ?></title>
<link rel="stylesheet" href="/montaje.css">
<style>
  @page{size:A4;margin:12mm 11mm 14mm}
  :root{--bg-page:#fff;--bg-card:#fff;--bg-inner:#f3f5f9;--text:#16233a;--muted:#5b6b82;--border:#d5dbe5;--bad:#d64545;--acc:#1f6fc0}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#fff;color:var(--text);font:10pt/1.45 -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:16px}
  header{display:flex;justify-content:space-between;align-items:baseline;border-bottom:2px solid #ff9f1c;padding-bottom:6px;margin-bottom:10px}
  header h1{font-size:15pt}
  header .v{color:var(--muted);font-size:9pt;text-align:right}
  svg.wire{min-width:0}
  .wirebox{overflow:visible;break-inside:avoid}
  .hint{color:var(--muted);font-size:8.5pt;margin:8px 0 12px}
  .row2{display:block}
  .row2>div{margin-top:10px}
  h2.sec{font-size:10pt;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin:10px 0 6px;break-after:avoid}
  table{width:100%;border-collapse:collapse;font-size:8.6pt}
  th,td{border-bottom:1px solid var(--border);padding:3px 5px;text-align:left;vertical-align:top}
  tr{break-inside:avoid}
  .sw-c{border-color:rgba(0,0,0,.25)}
  .pasos{font-size:8.8pt}
  a{color:var(--acc)}
  footer{margin-top:12px;border-top:1px solid var(--border);padding-top:6px;color:var(--muted);font-size:8pt}
</style>
</head>
<body>
<header><h1>WTTC · <?= t('wiring') ?></h1><div class="v"><?= $version ? 'v' . htmlspecialchars($version) . ' · ' : '' ?><?= date($date) ?><br><?= htmlspecialchars($home) ?></div></header>
<?php require __DIR__ . '/montaje.inc.php'; ?>
<footer><?= htmlspecialchars(sprintf($foot, $home)) ?></footer>
</body>
</html>
