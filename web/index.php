<?php
// wttc/index.php — Web pública de WTTC (https://wttc.favala.es, sin login), en español, inglés y alemán.
// Código y contenido generados íntegramente con Claude (Anthropic).
// Idiomas: / en español, /en/ en inglés y /de/ en alemán. en/index.php y de/index.php solo fijan $lang e incluyen
// esta plantilla; los textos están en i18n/<idioma>.php (página y simulador). Lo que falte en un idioma sale en español.
// Simulador en el navegador del firmware WTTC (ESP32 + TJA1020 por W-Bus) frente a una Thermo Top C virtual,
// más la documentación de montaje, instalación y uso. La web del móvil (movil.php) es la del firmware
// (firmware/WTTC/web.h) con fetch() desviado a WB.fetch(). Se regenera con /usr/local/bin/wttc-publicar.sh.
// Tramas según libwbus; la dinámica térmica y los tiempos de arranque son aproximados (ver «Qué se simula»).
// Código: https://github.com/matatunos/wttc
require_once __DIR__ . '/api/db.php';
wttc_public_only();
$lang = in_array($lang ?? 'es', ['es', 'en', 'de'], true) ? ($lang ?? 'es') : 'es';
wttc_visit($lang === 'es' ? 'portada' : 'portada-' . $lang);
$T_ES = require __DIR__ . '/i18n/es.php';
$T = $lang === 'es' ? $T_ES : array_replace_recursive($T_ES, require __DIR__ . "/i18n/$lang.php");
$base = 'https://wttc.favala.es/' . ($lang === 'es' ? '' : "$lang/");   // URL de esta página (canónica)
$fwZip = __DIR__ . '/descargas/WTTC-firmware.zip';
$version = trim((string)@file_get_contents(__DIR__ . '/descargas/VERSION')) ?: '';

// Texto de la página en el idioma elegido. {apk} y {stats} son enlaces que dependen de la versión o del idioma
function t(string $k): string {
    global $T, $version;
    return strtr((string)($T[$k] ?? $k), [
        '{apk}' => 'https://github.com/matatunos/wttc/releases/download/v' . htmlspecialchars($version) . '/WTTC.apk',
        '{stats}' => '/estadisticas.php',
        '{faults}' => ['es' => '/averias.php', 'en' => '/en/fault-codes.php', 'de' => '/de/fehlercodes.php'][$GLOBALS['lang']],
        '{install}' => ['es' => '/instalar.php', 'en' => '/en/install.php', 'de' => '/de/installieren.php'][$GLOBALS['lang']],
    ]);
}

// CHANGELOG.md → HTML (solo lo que usa: ## versión, ### apartado, viñetas, **negrita** y `código`)
function wttc_changelog(string $md): string {
    $o = ''; $inList = false;
    $inline = function (string $t): string {
        $t = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
        $t = preg_replace('/\*\*(.+?)\*\*/', '<b>$1</b>', $t);
        return preg_replace('/`(.+?)`/', '<code>$1</code>', $t);
    };
    foreach (preg_split('/\R/', $md) as $line) {
        if (preg_match('/^- (.*)$/', $line, $m)) { if (!$inList) { $o .= '<ul class="pasos">'; $inList = true; } $o .= '<li>' . $inline($m[1]) . '</li>'; continue; }
        if ($inList) { $o .= '</ul>'; $inList = false; }
        if (preg_match('/^## ([0-9.]+) — (.*)$/', $line, $m)) {
            $rel = version_compare($m[1], '0.1.0', '>=')
                ? ' · <a href="https://github.com/matatunos/wttc/releases/tag/v' . $m[1] . '" target="_blank" rel="noopener">' . t('relGithub') . '</a>' : '';
            $o .= '<h3 class="ver">' . t('verWord') . ' ' . $m[1] . ' <span class="muted">· ' . htmlspecialchars($m[2]) . $rel . '</span></h3>';
        } elseif (preg_match('/^### (.*)$/', $line, $m)) $o .= '<h4 class="verh">' . $inline($m[1]) . '</h4>';
        elseif (trim($line) !== '' && !preg_match('/^# /', $line)) $o .= '<p class="muted">' . $inline($line) . '</p>';
    }
    return $o . ($inList ? '</ul>' : '');
}
?><!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= t('title') ?></title>
<?php $desc = t('desc'); ?>
<meta name="description" content="<?= $desc ?>">
<link rel="canonical" href="<?= $base ?>">
<link rel="alternate" hreflang="es" href="https://wttc.favala.es/">
<link rel="alternate" hreflang="en" href="https://wttc.favala.es/en/">
<link rel="alternate" hreflang="de" href="https://wttc.favala.es/de/">
<link rel="alternate" hreflang="x-default" href="https://wttc.favala.es/en/">
<meta property="og:type" content="website">
<meta property="og:site_name" content="WTTC">
<meta property="og:locale" content="<?= ['es' => 'es_ES', 'en' => 'en_GB', 'de' => 'de_DE'][$lang] ?>">
<meta property="og:url" content="<?= $base ?>">
<meta property="og:title" content="<?= t('ogTitle') ?>">
<meta property="og:description" content="<?= $desc ?>">
<meta property="og:image" content="https://wttc.favala.es/og.png">
<meta name="twitter:card" content="summary_large_image">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'SoftwareApplication',
    'name' => 'WTTC', 'url' => $base, 'description' => $desc, 'inLanguage' => $lang,
    'applicationCategory' => 'UtilitiesApplication', 'operatingSystem' => 'Android',
    'softwareVersion' => $version ?: null, 'isAccessibleForFree' => true, 'license' => 'https://github.com/matatunos/wttc/blob/main/LICENSE',
    'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'EUR'],
    'downloadUrl' => $version ? "https://github.com/matatunos/wttc/releases/download/v$version/WTTC.apk" : 'https://github.com/matatunos/wttc/releases',
    'screenshot' => 'https://wttc.favala.es/og.png',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<style>
  :root{
    --bg-page:#0f1117; --bg-card:#1a1d27; --bg-inner:#13151f;
    --text:#e2e8f8; --muted:#7a84a8; --border:#2e3350;
    --ok:#3ab97a; --ok-bg:rgba(58,185,122,.12);
    --warn:#e0a93a; --warn-bg:rgba(224,169,58,.12);
    --bad:#e05252; --bad-bg:rgba(224,82,82,.14);
    --fl:#ff9f1c; --ice:#5bc0eb; --acc:#3a8ee0;
    --w-red:#e05252; --w-brown:#a0703c; --w-black:#c9cfe0; --w-yellow:#e8d23a;
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  body{background:var(--bg-page);color:var(--text);
       font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
       min-height:100vh;padding:20px 16px;}
  .wrap{max-width:1180px;margin:0 auto;}
  a{color:var(--ice);}
  a.back{color:var(--muted);font-size:.82rem;text-decoration:none;}
  a.back:hover{color:var(--text);}
  header{margin:6px 0 16px;}
  header h1{font-size:1.5rem;font-weight:800;
    background:linear-gradient(135deg,#ff9f1c,#5bc0eb);-webkit-background-clip:text;
    -webkit-text-fill-color:transparent;background-clip:text;}
  header .sub{color:var(--muted);font-size:.88rem;margin-top:4px;line-height:1.5;max-width:880px;}
  h2.sec{font-size:.85rem;color:var(--muted);font-weight:700;margin:0 0 10px;
    text-transform:uppercase;letter-spacing:.06em;}
  .card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:14px;}
  .grid{display:grid;grid-template-columns:400px minmax(0,1fr);gap:16px;align-items:start;}
  .stack{display:flex;flex-direction:column;gap:16px;min-width:0;}
  .row2{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:16px;margin-top:16px;}
  @media (max-width:980px){ .grid,.row2{grid-template-columns:minmax(0,1fr);} }

  /* Móvil */
  .phone{width:100%;max-width:390px;margin:0 auto;border:10px solid #05070c;border-radius:42px;
    overflow:hidden;background:#0F1A2A;box-shadow:0 0 0 1px var(--border),0 18px 40px rgba(0,0,0,.45);}
  .phone .bar{height:26px;background:#05070c;display:flex;justify-content:center;align-items:center;
    color:#5d6680;font-size:.7rem;}
  .phone iframe{display:block;width:100%;height:740px;border:0;background:#0F1A2A;}
  .phone-cap{color:var(--muted);font-size:.78rem;text-align:center;margin-top:8px;line-height:1.45;}

  /* Controles de tiempo */
  .ctl{display:flex;flex-wrap:wrap;gap:8px;align-items:center;}
  .ctl .clock{font-variant-numeric:tabular-nums;font-weight:700;font-size:1.05rem;margin-right:auto;}
  .ctl .clock small{display:block;color:var(--muted);font-weight:400;font-size:.72rem;}
  button.b,a.b{background:var(--bg-inner);border:1px solid var(--border);color:var(--text);
    border-radius:8px;padding:7px 11px;font:inherit;font-size:.8rem;cursor:pointer;}
  button.b:hover,a.b:hover{border-color:var(--acc);}
  button.b.on{border-color:var(--acc);background:rgba(58,142,224,.16);}
  button.b.warn{border-color:var(--warn);color:var(--warn);}
  button:focus-visible,input:focus-visible,select:focus-visible{outline:2px solid var(--ice);outline-offset:2px;}

  /* Webasto */
  .hv{display:grid;grid-template-columns:auto minmax(0,1fr);gap:16px;align-items:center;}
  .hv .t{font-size:3rem;font-weight:200;font-variant-numeric:tabular-nums;line-height:1;}
  .hv .t small{font-size:.8rem;color:var(--muted);display:block;font-weight:400;margin-top:4px;}
  .state{font-weight:700;font-size:1.05rem;}
  .state .code{color:var(--muted);font-weight:400;font-size:.75rem;font-family:ui-monospace,monospace;margin-left:6px;}
  .state.burn{color:var(--fl);} .state.lock{color:var(--bad);}
  .comps{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
  .comps span{font-size:.72rem;padding:3px 8px;border-radius:999px;background:var(--bg-inner);
    border:1px solid var(--border);color:var(--muted);}
  .comps span.on{color:var(--text);border-color:var(--fl);background:rgba(255,159,28,.12);}
  .kv{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:12px;}
  .kv div{background:var(--bg-inner);border-radius:10px;padding:8px 10px;}
  .kv .k{font-size:.66rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;}
  .kv .v{font-size:1rem;font-weight:700;font-variant-numeric:tabular-nums;margin-top:2px;}
  @media (max-width:560px){ .kv{grid-template-columns:repeat(2,minmax(0,1fr));} .hv{grid-template-columns:minmax(0,1fr);} }
  canvas#chart{width:100%;height:120px;display:block;margin-top:12px;background:var(--bg-inner);border-radius:10px;}
  .errs{margin-top:10px;font-size:.8rem;color:var(--muted);}
  .errs b{color:var(--text);}

  /* Averías provocadas */
  .faults{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;}
  @media (max-width:560px){ .faults{grid-template-columns:minmax(0,1fr);} }
  .faults label{display:flex;gap:9px;align-items:flex-start;background:var(--bg-inner);border:1px solid var(--border);
    border-radius:10px;padding:9px 10px;font-size:.82rem;cursor:pointer;line-height:1.35;}
  .faults label small{display:block;color:var(--muted);font-size:.74rem;}
  .faults input{margin-top:3px;accent-color:var(--bad);}
  .faults label:has(input:checked){border-color:var(--bad);background:var(--bad-bg);}
  .env{display:flex;flex-wrap:wrap;gap:16px;margin-top:12px;font-size:.82rem;color:var(--muted);align-items:center;}
  .oledrow{display:flex;gap:16px;align-items:center;flex-wrap:wrap;}
  .oled{position:relative;background:#05070b;border:7px solid #1b1e26;border-radius:6px;padding:5px;line-height:0;}
  .oled canvas{width:384px;max-width:calc(100vw - 90px);height:auto;image-rendering:pixelated;display:block;background:#000;}
  #oledMsg{white-space:pre-line;position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#5d6680;font-size:.85rem;line-height:1.3;text-align:center;}
  .oledside{display:flex;flex-direction:column;gap:8px;font-size:.82rem;color:var(--muted);}
  .oledside b{color:var(--text);font-size:1.1rem;font-variant-numeric:tabular-nums;}
  .led{display:inline-block;width:18px;height:18px;border-radius:50%;background:#1d2029;border:1px solid #3a3f4f;vertical-align:middle;margin-right:6px;transition:background .1s,box-shadow .1s;}
  .env input[type=range]{vertical-align:middle;width:130px;accent-color:var(--acc);}
  .env b{color:var(--text);font-variant-numeric:tabular-nums;}

  /* Traza del bus */
  .trace{height:360px;overflow:auto;background:var(--bg-inner);border-radius:10px;padding:6px 0;
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.74rem;}
  .tr{display:grid;grid-template-columns:76px 48px minmax(120px,auto) minmax(0,1fr);gap:8px;padding:3px 10px;}
  .tr:hover{background:rgba(255,255,255,.03);}
  .tr .ts{color:var(--muted);}
  .tr .d{font-weight:700;}
  .tr.tx .d{color:var(--ice);} .tr.rx .d{color:var(--ok);} .tr.echo .d,.tr.brk .d{color:var(--muted);}
  .tr.bad .d,.tr.bad .x{color:var(--bad);}
  .tr .by{white-space:nowrap;}
  .tr .x{color:var(--muted);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;}
  .tr.gap{border-top:1px solid var(--border);margin-top:3px;padding-top:5px;}
  @media (max-width:640px){ .tr{grid-template-columns:62px 40px minmax(0,1fr);} .tr .x{grid-column:2 / -1;} }
  .topline{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;}
  .topline h2.sec{margin:0;}
  .topline label{font-size:.78rem;color:var(--muted);display:flex;gap:6px;align-items:center;}

  /* Consola serie */
  .con{height:300px;overflow:auto;background:#05070c;border-radius:10px;padding:8px 10px;
    font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.74rem;color:#b9f6ca;white-space:pre-wrap;word-break:break-word;}
  .con .in{color:var(--ice);}
  .conin{display:flex;gap:8px;margin-top:8px;}
  .conin input{flex:1;min-width:0;background:var(--bg-inner);border:1px solid var(--border);border-radius:8px;
    color:var(--text);padding:8px 10px;font-family:ui-monospace,monospace;font-size:.82rem;}
  .hint{color:var(--muted);font-size:.76rem;margin-top:8px;line-height:1.45;}

  /* Esquema */
  .wirebox{overflow-x:auto;border-radius:10px;}
  svg.wire{width:100%;min-width:900px;height:auto;display:block;background:var(--bg-inner);border-radius:10px;}
  svg.wire text{fill:var(--text);font-size:12px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;}
  svg.wire text.t{font-size:14px;font-weight:700;}
  svg.wire text.m{fill:var(--muted);font-size:11px;}
  svg.wire text.silk{fill:#fff;font-size:11px;font-weight:700;}
  svg.wire text.pin{fill:#c9cfe0;font-size:8.5px;font-family:ui-monospace,monospace;}
  svg.wire text.pin.use{fill:#fff;font-size:9.5px;font-weight:800;}
  svg.wire text.bad{fill:var(--bad);font-weight:700;}
  svg.wire text.tag{font-size:11px;font-weight:800;text-anchor:middle;dominant-baseline:central;}
  svg.wire .halo{fill:none;stroke:var(--bg-inner);stroke-width:10;stroke-linejoin:round;}
  svg.wire .cw{fill:none;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;}
  svg.wire .xx{stroke:var(--bad);stroke-width:4;}
  .hide{display:none;}
  table.cables td:first-child{font-weight:800;text-align:center;width:34px;}
  table.cables tr.opt td{font-weight:400;text-align:left;width:auto;color:var(--muted);border-top:2px dashed var(--border);padding-top:12px;}
  .sw-c{display:inline-block;width:26px;height:8px;border-radius:4px;vertical-align:middle;margin-right:6px;border:1px solid rgba(255,255,255,.25);}
  .pasos{padding-left:20px;font-size:.84rem;line-height:1.55;}
  .pasos li{margin-bottom:6px;}

  /* Calculadora / tablas */
  table{width:100%;border-collapse:collapse;font-size:.8rem;}
  th{text-align:left;padding:6px 8px;color:var(--muted);font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--border);}
  td{padding:6px 8px;border-top:1px solid var(--border);}
  td code,.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;}
  .calc{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;align-items:flex-end;}
  .calc label{font-size:.72rem;color:var(--muted);display:flex;flex-direction:column;gap:4px;}
  .calc input{background:var(--bg-inner);border:1px solid var(--border);border-radius:8px;color:var(--text);
    padding:7px 9px;font-family:ui-monospace,monospace;font-size:.85rem;width:90px;}
  .calc input.wide{width:150px;}
  .calc .out{font-family:ui-monospace,monospace;font-size:.95rem;font-weight:700;color:var(--ice);padding:7px 0;}
  ul.notes{padding-left:18px;font-size:.84rem;line-height:1.55;}
  ul.notes li{margin-bottom:6px;}
  ul.notes li b{color:var(--text);}
  .muted{color:var(--muted);}
  .sp{margin-top:16px;}
  .credito{color:var(--muted);font-size:.8rem;text-align:center;margin:26px 0 0}
  .aviso{margin-top:14px;background:rgba(224,169,58,.12);border:1px solid rgba(224,169,58,.45);border-radius:10px;padding:12px 14px;font-size:.88rem;line-height:1.5;max-width:880px}
  .aviso a{color:var(--warn,#e0a93a);font-weight:700}
  .legal p{font-size:.88rem;line-height:1.6;margin:0 0 10px;max-width:900px}
  .legal ul{max-width:900px}
  .credito a{color:var(--acc)}
  .dl3,.row3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
  .dl3{grid-template-columns:repeat(4,1fr)}
  @media (max-width:1100px){.dl3{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:900px){.row3{grid-template-columns:1fr}}
  @media (max-width:600px){.dl3{grid-template-columns:1fr}}
  .dlc{display:flex;flex-direction:column;gap:6px;background:var(--bg-inner);border:1px solid var(--border);border-radius:10px;padding:12px}
  .dlc a.b{align-self:flex-start;text-decoration:none}
  h3.sub3{font-size:.95rem;margin:4px 0 6px}
  details.vers{margin-top:12px;background:var(--bg-inner);border:1px solid var(--border);border-radius:10px;padding:10px 14px}
  details.vers summary{cursor:pointer;font-weight:700}
  h3.ver{font-size:1rem;margin:16px 0 4px} h4.verh{font-size:.85rem;margin:10px 0 2px;color:var(--muted)}
  .sub a,.hint a,.pasos a{color:var(--acc)}
  .fw-dl{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:8px}
  .fw-dl a.b{text-decoration:none}
  pre.cmd{background:rgba(0,0,0,.28);border:1px solid var(--border);border-radius:8px;padding:10px 12px;overflow-x:auto;font-size:.78rem;line-height:1.5;margin:0}
  code.sel{user-select:all;word-break:break-all}
  table.hw td:first-child{white-space:nowrap;color:var(--muted)}
  @media (max-width:640px){table.hw td:first-child{white-space:normal}}
  .tgchat{max-height:170px;overflow:auto;display:flex;flex-direction:column;gap:6px;font-size:.85rem}
  .tgchat .msg{background:var(--card2,rgba(122,132,168,.12));border-radius:10px;padding:6px 10px}
  .tgchat .ts{color:var(--muted);font-size:.75rem;margin-right:8px;font-variant-numeric:tabular-nums}
  .langs{float:right;display:flex;gap:4px;font-size:.82rem;margin:4px 0 6px 12px}
  .langs a{padding:3px 9px;border-radius:999px;border:1px solid var(--border);color:var(--muted);text-decoration:none}
  .langs a[aria-current]{background:var(--bg-card);color:var(--text);border-color:var(--acc)}
</style>
</head>
<body>
<div class="wrap">
    <header>
    <nav class="langs" aria-label="Idioma · Language · Sprache"><?php foreach (['es' => 'Español', 'en' => 'English', 'de' => 'Deutsch'] as $l => $n): ?>
      <a href="/<?= $l === 'es' ? '' : "$l/" ?>" hreflang="<?= $l ?>" lang="<?= $l ?>"<?= $l === $lang ? ' aria-current="page"' : '' ?>><?= $n ?></a><?php endforeach; ?>
    </nav>
    <h1><?= t('h1') ?></h1>
    <div class="sub"><?= t('intro') ?></div>
  </header>

  <div class="aviso" role="note">
<?= t('aviso') ?>
  </div>

  <div class="grid">
    <div>
      <div class="phone"><div class="bar">WTTC · 192.168.4.1</div><iframe id="movil" title="<?= t('phoneTitle') ?>"></iframe></div>
      <div class="phone-cap"><?= t('phoneCap') ?></div>
    </div>

    <div class="stack">
      <div class="card">
        <div class="ctl">
          <div class="clock"><span id="simclock">--:--:--</span><small id="simsub"><?= t('simTime') ?></small></div>
          <button class="b" data-speed="0"><?= t('pause') ?></button>
          <button class="b on" data-speed="1">×1</button>
          <button class="b" data-speed="10">×10</button>
          <button class="b" data-speed="60">×60</button>
          <button class="b" id="jump10">+10 min</button>
          <button class="b warn" id="reboot" title="<?= t('rebootTitle') ?>"><?= t('rebootBtn') ?></button>
        </div>
      </div>

      <div class="card">
        <h2 class="sec"><?= t('heaterVirtual') ?></h2>
        <div class="hv">
          <div class="t"><span id="hTemp">--</span>°<small><?= t('water') ?></small></div>
          <div>
            <div class="state" id="hState"><?= t('off') ?></div>
            <div class="comps" id="hComps"></div>
          </div>
        </div>
        <div class="kv">
          <div><div class="k"><?= t('kPower') ?></div><div class="v" id="hPow">0 W</div></div>
          <div><div class="k"><?= t('kBatt') ?></div><div class="v" id="hVolt">--</div></div>
          <div><div class="k"><?= t('kAmp') ?></div><div class="v" id="hAmp">--</div></div>
          <div><div class="k"><?= t('kRun') ?></div><div class="v" id="hRun">—</div></div>
        </div>
        <canvas id="chart" width="700" height="120" aria-label="<?= t('chartAria') ?>"></canvas>
        <div class="errs" id="hErrs"></div>
      </div>

      <!-- Placa: pantalla OLED (dibujada como en el firmware), LED, botón BOOT y piezas opcionales -->
      <div class="card">
        <h2 class="sec"><?= t('boardSim') ?></h2>
        <div class="oledrow">
          <div class="oled"><canvas id="oled" width="128" height="64" role="img" aria-label="<?= t('oledAria') ?>"></canvas><span id="oledMsg"></span></div>
          <div class="oledside">
            <div><span class="led" id="led"></span><?= t('ledLbl') ?></div>
            <button class="b" id="bootBtn" title="<?= t('bootTitle') ?>">BOOT</button>
            <div><?= t('cabNow') ?><br><b id="cabv">--</b></div>
          </div>
        </div>
        <div class="env">
          <label><input type="checkbox" id="hwScr" checked> <?= t('hwScr') ?></label>
          <label><input type="checkbox" id="hwSens" checked> <?= t('hwSens') ?></label>
          <span><?= t('cabSet') ?> <input type="range" id="cab" min="-10" max="30" step="1" value="7"></span>
        </div>
        <div class="hint"><?= t('boardHint') ?></div>
      </div>

      <!-- Pruebas: una prueba automática (al momento) o grabar lo que se hace, con informe en PDF -->
      <div class="card">
        <h2 class="sec"><?= t('testTitle') ?></h2>
        <div class="env">
          <span><?= t('tAmb') ?> <input type="number" id="tAmb" value="5" min="-25" max="40" step="1" style="width:4.2em"> °C</span>
          <span><?= t('tCab') ?> <input type="number" id="tCab" value="8" min="-25" max="40" step="1" style="width:4.2em"> °C</span>
          <span><?= t('tTgt') ?> <select id="tTgt"><option value="0"><?= t('tNoTgt') ?></option><?php foreach ([10, 12, 14, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25] as $v) echo "<option value=\"$v\"" . ($v === 20 ? ' selected' : '') . ">$v °C</option>"; ?></select></span>
          <span><?= t('tDur') ?> <select id="tDur"><option value="30">30 min</option><option value="60">1 h</option><option value="120">2 h</option><option value="180">3 h</option><option value="240" selected>4 h</option></select></span>
          <span><?= t('tBatt') ?> <input type="number" id="tBatt" value="12.6" min="11" max="13.5" step="0.1" style="width:4.2em"> V</span>
        </div>
        <div class="ctl" style="margin-top:12px"><button class="b on" id="tRun"><?= t('tRun') ?></button><button class="b" id="tRec"><?= t('tRecStart') ?></button></div>
        <div class="hint" id="tMsg"></div>
        <div class="hint"><?= t('testHint') ?></div>
      </div>

      <div class="card">
        <h2 class="sec"><?= t('tgSim') ?></h2>
        <div class="tgchat" id="tgfeed" role="log" aria-live="polite"></div>
        <div class="hint"><?= t('tgHint') ?></div>
      </div>

      <div class="card">
        <h2 class="sec"><?= t('faultsTitle') ?></h2>
        <div class="faults">
<?= t('faults') ?>        </div>
        <div class="env">
<?= t('env') ?>        </div>
      </div>
    </div>
  </div>

  <div class="row2">
    <div class="card">
      <div class="topline">
        <h2 class="sec"><?= t('traffic') ?></h2>
        <span style="display:flex;gap:12px;flex-wrap:wrap">
          <label><input type="checkbox" id="trRep" checked><?= t('trRep') ?></label>
          <button class="b" id="trClr"><?= t('clear') ?></button>
        </span>
      </div>
      <div class="trace" id="trace" role="log" aria-live="off"></div>
      <div class="hint"><?= t('traceHint') ?></div>
    </div>
    <div class="card">
      <h2 class="sec"><?= t('serial') ?></h2>
      <div class="con" id="con"></div>
      <form class="conin" id="conf"><input id="conx" placeholder="<?= t('conPh') ?>" autocomplete="off" autocapitalize="off" spellcheck="false" aria-label="<?= t('conAria') ?>"><button class="b"><?= t('send') ?></button></form>
      <div class="hint"><?= t('conHint') ?></div>
    </div>
  </div>

  <div class="card sp">
    <h2 class="sec" id="montaje"><?= t('wiring') ?></h2>
<div class="wirebox"><svg class="wire" viewBox="0 120 1130 775" role="img" aria-label="<?= t('svgAria') ?>">
<text x="20" y="200" class="t"><?= t('svgConn') ?></text><text x="20" y="216" class="m"><?= t('svgConnSub') ?></text>
<rect x="20" y="230" width="110" height="220" rx="8" fill="#3a3f4f" stroke="#5d6680" stroke-width="2"/>
<rect x="112" y="252" width="18" height="16" rx="3" fill="#252936"/>
<rect x="112" y="292" width="18" height="16" rx="3" fill="#252936"/>
<rect x="112" y="372" width="18" height="16" rx="3" fill="#252936"/>
<rect x="112" y="412" width="18" height="16" rx="3" fill="#252936"/>
<?= t('svgSilk') ?>
<g id="xPower" class="hide"><text x="20" y="182" class="bad"><?= t('svgNoPower') ?></text></g>
<text x="350" y="192" class="t"><?= t('svgBoard') ?></text><text x="350" y="208" class="m"><?= t('svgBoardSub') ?></text>
<rect x="350" y="220" width="220" height="200" rx="6" fill="#1f6f3a" stroke="#2c8a4c" stroke-width="2"/>
<rect x="350" y="235" width="30" height="170" rx="3" fill="#2fb37a" stroke="#1b7a50"/>
<rect x="540" y="235" width="30" height="170" rx="3" fill="#2fb37a" stroke="#1b7a50"/>
<circle cx="365" cy="260" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="254" x2="371" y2="266" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="260" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="254" x2="561" y2="266" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="264" class="silk">GND</text><text x="532" y="264" class="silk" text-anchor="end">GND</text>
<circle cx="365" cy="300" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="294" x2="371" y2="306" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="300" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="294" x2="561" y2="306" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="304" class="silk">LIN</text><text x="532" y="304" class="silk" text-anchor="end">RX</text>
<circle cx="365" cy="340" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="334" x2="371" y2="346" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="340" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="334" x2="561" y2="346" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="344" class="silk">INH</text><text x="532" y="344" class="silk" text-anchor="end">SLP</text>
<circle cx="365" cy="380" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="374" x2="371" y2="386" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="380" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="374" x2="561" y2="386" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="384" class="silk">12V</text><text x="532" y="384" class="silk" text-anchor="end">TX</text>
<rect x="435" y="260" width="50" height="30" rx="2" fill="#111"/><text x="460" y="279" class="pin" text-anchor="middle">TJA1020</text>
<circle cx="460" cy="360" r="18" fill="#2b2b2b"/><text x="460" y="364" class="pin" text-anchor="middle">47µF</text>
<text x="460" y="408" class="m" text-anchor="middle"><?= t('svgInh') ?></text>
<text x="350" y="488" class="t"><?= t('svgReg') ?></text>
<rect x="350" y="500" width="220" height="120" rx="6" fill="#1d4f91" stroke="#2f6cc0" stroke-width="2"/>
<rect x="357" y="507" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="365" cy="515" r="4" fill="#5d6680"/>
<text x="379" y="519" class="silk" text-anchor="start">IN−</text>
<rect x="357" y="597" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="365" cy="605" r="4" fill="#5d6680"/>
<text x="379" y="609" class="silk" text-anchor="start">IN+</text>
<rect x="547" y="507" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="555" cy="515" r="4" fill="#5d6680"/>
<text x="541" y="519" class="silk" text-anchor="end">OUT+</text>
<rect x="547" y="597" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="555" cy="605" r="4" fill="#5d6680"/>
<text x="541" y="609" class="silk" text-anchor="end">OUT−</text>
<rect x="428" y="532" width="56" height="56" rx="10" fill="#1a1a1a"/><text x="456" y="564" class="pin" text-anchor="middle">470</text>
<circle cx="402" cy="560" r="15" fill="#b9bfcc"/><text x="402" y="563" class="pin" text-anchor="middle" style="fill:#333">100µF</text>
<rect x="500" y="540" width="34" height="26" rx="2" fill="#2f6cc0" stroke="#7fb0ff"/><circle cx="517" cy="553" r="6" fill="#e0b84a"/>
<text x="517" y="580" class="pin" text-anchor="middle"><?= t('svgScrew') ?></text><text x="517" y="591" class="pin" text-anchor="middle"><?= t('svg5v') ?></text>
<text x="650" y="150" class="t"><?= t('svgEsp') ?></text><text x="650" y="166" class="m"><?= t('svgEspSub') ?></text>
<rect x="650" y="285" width="460" height="170" rx="8" fill="#16181e" stroke="#3a3f4f" stroke-width="2"/>
<path d="M660 330 h26 v10 h-26 v10 h26 v10 h-26 v10 h26 v10 h-26 v10 h26" fill="none" stroke="#c9a54a" stroke-width="3"/>
<rect x="700" y="330" width="220" height="80" rx="3" fill="#9aa0ac"/><text x="810" y="364" text-anchor="middle" style="fill:#222;font-weight:700">ESP32-S3-WROOM-1</text><text x="810" y="382" text-anchor="middle" style="fill:#333;font-size:12px">N16R8 · 16 MB</text>
<rect x="955" y="345" width="16" height="12" rx="2" fill="#2b2b2b"/><text x="963" y="372" class="pin" text-anchor="middle">BOOT</text><rect x="1000" y="345" width="16" height="12" rx="2" fill="#2b2b2b"/><text x="1008" y="372" class="pin" text-anchor="middle">RST</text>
<rect x="1098" y="333" width="28" height="22" rx="4" fill="#9aa0ac"/><text x="1090" y="348" class="pin use" text-anchor="end">UART</text><rect x="1098" y="385" width="28" height="22" rx="4" fill="#9aa0ac"/><text x="1090" y="400" class="pin" text-anchor="end">USB</text>
<circle cx="665" cy="297" r="6" fill="#e0b84a" stroke="#9aa3b5" stroke-width="3"/>
<text class="pin use" transform="translate(668,308) rotate(90)">GND</text>
<circle cx="685" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(688,308) rotate(90)">TX</text>
<circle cx="705" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(708,308) rotate(90)">RX</text>
<circle cx="725" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(728,308) rotate(90)">IO1</text>
<circle cx="745" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(748,308) rotate(90)">IO2</text>
<circle cx="765" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(768,308) rotate(90)">IO42</text>
<circle cx="785" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(788,308) rotate(90)">IO41</text>
<circle cx="805" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(808,308) rotate(90)">IO40</text>
<circle cx="825" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(828,308) rotate(90)">IO39</text>
<circle cx="845" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(848,308) rotate(90)">IO38</text>
<circle cx="865" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(868,308) rotate(90)">IO37</text>
<circle cx="885" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(888,308) rotate(90)">IO36</text>
<circle cx="905" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(908,308) rotate(90)">IO35</text>
<circle cx="925" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(928,308) rotate(90)">IO0</text>
<circle cx="945" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(948,308) rotate(90)">IO45</text>
<circle cx="965" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(968,308) rotate(90)">IO48</text>
<circle cx="985" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(988,308) rotate(90)">IO47</text>
<circle cx="1005" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1008,308) rotate(90)">IO21</text>
<circle cx="1025" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1028,308) rotate(90)">IO20</text>
<circle cx="1045" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1048,308) rotate(90)">IO19</text>
<circle cx="1065" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1068,308) rotate(90)">GND</text>
<circle cx="1085" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1088,308) rotate(90)">GND</text>
<circle cx="665" cy="443" r="6" fill="#e0b84a" stroke="#b57bff" stroke-width="3"/>
<text class="pin use" transform="translate(668,432) rotate(-90)">3V3</text>
<circle cx="685" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(688,432) rotate(-90)">3V3</text>
<circle cx="705" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(708,432) rotate(-90)">RST</text>
<circle cx="725" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(728,432) rotate(-90)">IO4</text>
<circle cx="745" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(748,432) rotate(-90)">IO5</text>
<circle cx="765" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(768,432) rotate(-90)">IO6</text>
<circle cx="785" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(788,432) rotate(-90)">IO7</text>
<circle cx="805" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(808,432) rotate(-90)">IO15</text>
<circle cx="825" cy="443" r="6" fill="#e0b84a" stroke="#3ecf8e" stroke-width="3"/>
<text class="pin use" transform="translate(828,432) rotate(-90)">IO16</text>
<circle cx="845" cy="443" r="6" fill="#e0b84a" stroke="#4aa8ff" stroke-width="3"/>
<text class="pin use" transform="translate(848,432) rotate(-90)">IO17</text>
<circle cx="865" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(868,432) rotate(-90)">IO18</text>
<circle cx="885" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(888,432) rotate(-90)">IO8</text>
<circle cx="905" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(908,432) rotate(-90)">IO3</text>
<circle cx="925" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(928,432) rotate(-90)">IO46</text>
<circle cx="945" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(948,432) rotate(-90)">IO9</text>
<circle cx="965" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(968,432) rotate(-90)">IO10</text>
<circle cx="985" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(988,432) rotate(-90)">IO11</text>
<circle cx="1005" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1008,432) rotate(-90)">IO12</text>
<circle cx="1025" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1028,432) rotate(-90)">IO13</text>
<circle cx="1045" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1048,432) rotate(-90)">IO14</text>
<circle cx="1065" cy="443" r="6" fill="#e0b84a" stroke="#ff9f1c" stroke-width="3"/>
<text class="pin use" transform="translate(1068,432) rotate(-90)">5V</text>
<circle cx="1085" cy="443" r="6" fill="#e0b84a" stroke="#9aa3b5" stroke-width="3"/>
<text class="pin use" transform="translate(1088,432) rotate(-90)">GND</text>
<g><path class="halo" d="M230 260 V515 H365"/><path class="cw" d="M230 260 V515 H365" stroke="#a0703c"/><circle cx="230" cy="455" r="11" fill="var(--bg-card)" stroke="#a0703c" stroke-width="2.5"/><text x="230" y="455.5" class="tag" style="fill:#a0703c">5</text></g>
<g><path class="halo" d="M200 380 V605 H365"/><path class="cw" d="M200 380 V605 H365" stroke="#e04848"/><circle cx="200" cy="560" r="11" fill="var(--bg-card)" stroke="#e04848" stroke-width="2.5"/><text x="200" y="560.5" class="tag" style="fill:#e04848">4</text></g>
<g><path class="halo" d="M130 260 H365"/><path class="cw" d="M130 260 H365" stroke="#a0703c"/><circle cx="300" cy="260" r="11" fill="var(--bg-card)" stroke="#a0703c" stroke-width="2.5"/><text x="300" y="260.5" class="tag" style="fill:#a0703c">1</text></g>
<g><path class="halo" d="M130 300 H365"/><path class="cw" d="M130 300 H365" stroke="#9aa3bd" style="stroke-width:8"/><path class="cw" d="M130 300 H365" stroke="#111318"/><circle cx="240" cy="300" r="11" fill="var(--bg-card)" stroke="#fff" stroke-width="2.5"/><text x="240" y="300.5" class="tag" style="fill:#fff">2</text></g>
<g><path class="halo" d="M130 380 H365"/><path class="cw" d="M130 380 H365" stroke="#e04848"/><circle cx="300" cy="380" r="11" fill="var(--bg-card)" stroke="#e04848" stroke-width="2.5"/><text x="300" y="380.5" class="tag" style="fill:#e04848">3</text></g>
<g><path class="halo" d="M130 420 H168"/><path class="cw" d="M130 420 H168" stroke="#e8d23a"/></g>
<rect x="166" y="411" width="22" height="18" rx="4" fill="#5d6680"/><text x="20" y="648" class="m"><?= t('svgYellow') ?></text>
<circle cx="230" cy="260" r="6" fill="#a0703c" stroke="var(--bg-card)" stroke-width="2"/><circle cx="200" cy="380" r="6" fill="#e04848" stroke="var(--bg-card)" stroke-width="2"/>
<g id="xWbus" class="hide"><line class="xx" x1="290" y1="288" x2="314" y2="312"/><line class="xx" x1="314" y1="288" x2="290" y2="312"/><text x="270" y="330" class="bad"><?= t('svgWbus') ?></text></g>
<g><path class="halo" d="M555 605 H1085 V443"/><path class="cw" d="M555 605 H1085 V443" stroke="#9aa3b5"/><circle cx="800" cy="605" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5"/><text x="800" y="605.5" class="tag" style="fill:#9aa3b5">7</text></g>
<g><path class="halo" d="M555 515 H1065 V443"/><path class="cw" d="M555 515 H1065 V443" stroke="#ff9f1c"/><circle cx="800" cy="515" r="11" fill="var(--bg-card)" stroke="#ff9f1c" stroke-width="2.5"/><text x="800" y="515.5" class="tag" style="fill:#ff9f1c">6</text></g>
<g id="slpW"><g><path class="halo" d="M555 340 H610 V468 H665 V443"/><path class="cw" d="M555 340 H610 V468 H665 V443" stroke="#b57bff"/><circle cx="610" cy="415" r="11" fill="var(--bg-card)" stroke="#b57bff" stroke-width="2.5"/><text x="610" y="415.5" class="tag" style="fill:#b57bff">10</text></g></g>
<g id="slpX" class="hide"><path d="M555 340 H595" stroke="var(--bad)" stroke-width="3" stroke-dasharray="5 4"/><text x="620" y="560" class="bad"><?= t('svgSlp') ?></text></g>
<g><path class="halo" d="M555 260 H665 V297"/><path class="cw" d="M555 260 H665 V297" stroke="#9aa3b5"/><circle cx="615" cy="260" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5"/><text x="615" y="260.5" class="tag" style="fill:#9aa3b5">11</text></g>
<g id="sigOk"><g><path class="halo" d="M555 300 H630 V484 H845 V443"/><path class="cw" d="M555 300 H630 V484 H845 V443" stroke="#4aa8ff"/><circle cx="760" cy="484" r="11" fill="var(--bg-card)" stroke="#4aa8ff" stroke-width="2.5"/><text x="760" y="484.5" class="tag" style="fill:#4aa8ff">9</text></g><g><path class="halo" d="M555 380 H590 V500 H825 V443"/><path class="cw" d="M555 380 H590 V500 H825 V443" stroke="#3ecf8e"/><circle cx="700" cy="500" r="11" fill="var(--bg-card)" stroke="#3ecf8e" stroke-width="2.5"/><text x="700" y="500.5" class="tag" style="fill:#3ecf8e">8</text></g></g>
<g id="sigX" class="hide"><g><path class="halo" d="M555 300 H630 V484 H825 V443"/><path class="cw" d="M555 300 H630 V484 H825 V443" stroke="var(--bad)" stroke-dasharray="10 6"/><circle cx="760" cy="484" r="11" fill="var(--bg-card)" stroke="var(--bad)" stroke-width="2.5"/><text x="760" y="484.5" class="tag" style="fill:var(--bad)">9</text></g><g><path class="halo" d="M555 380 H590 V500 H845 V443"/><path class="cw" d="M555 380 H590 V500 H845 V443" stroke="var(--bad)" stroke-dasharray="10 6"/><circle cx="700" cy="500" r="11" fill="var(--bg-card)" stroke="var(--bad)" stroke-width="2.5"/><text x="700" y="500.5" class="tag" style="fill:var(--bad)">8</text></g><text x="870" y="540" class="bad"><?= t('svgCross') ?></text></g>
<!-- Opcional: pantalla OLED y termómetro en el bus I2C (IO4/IO5). Recuadro y cables discontinuos: el montaje funciona sin ellos -->
<g id="optHw">
<rect x="15" y="668" width="1107" height="222" rx="12" fill="none" stroke="#5d6680" stroke-width="2" stroke-dasharray="10 7"/>
<text x="35" y="698" class="t"><?= t('svgOpt') ?></text><text x="35" y="716" class="m"><?= t('svgOptSub') ?></text>
<circle cx="685" cy="443" r="7" fill="none" stroke="#ff7aa8" stroke-width="2.5" stroke-dasharray="3 2"/>
<circle cx="725" cy="443" r="7" fill="none" stroke="#2ec4b6" stroke-width="2.5" stroke-dasharray="3 2"/>
<circle cx="745" cy="443" r="7" fill="none" stroke="#e9c46a" stroke-width="2.5" stroke-dasharray="3 2"/>
<g><path class="halo" d="M685 443 V725"/><path class="cw" d="M685 443 V725" stroke="#ff7aa8" stroke-dasharray="9 6"/><circle cx="685" cy="560" r="11" fill="var(--bg-card)" stroke="#ff7aa8" stroke-width="2.5" stroke-dasharray="3 2"/><text x="685" y="560.5" class="tag" style="fill:#ff7aa8">12</text></g>
<g><path class="halo" d="M1085 605 V740"/><path class="cw" d="M1085 605 V740" stroke="#9aa3b5" stroke-dasharray="9 6"/><circle cx="1085" cy="650" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5" stroke-dasharray="3 2"/><text x="1085" y="650.5" class="tag" style="fill:#9aa3b5">13</text></g>
<circle cx="1085" cy="605" r="4" fill="#9aa3b5"/>
<g><path class="halo" d="M725 443 V755"/><path class="cw" d="M725 443 V755" stroke="#2ec4b6" stroke-dasharray="9 6"/><circle cx="725" cy="640" r="11" fill="var(--bg-card)" stroke="#2ec4b6" stroke-width="2.5" stroke-dasharray="3 2"/><text x="725" y="640.5" class="tag" style="fill:#2ec4b6">14</text></g>
<g><path class="halo" d="M745 443 V770"/><path class="cw" d="M745 443 V770" stroke="#e9c46a" stroke-dasharray="9 6"/><circle cx="745" cy="560" r="11" fill="var(--bg-card)" stroke="#e9c46a" stroke-width="2.5" stroke-dasharray="3 2"/><text x="745" y="560.5" class="tag" style="fill:#e9c46a">15</text></g>
<path class="cw" d="M685 725 H920" stroke="#ff7aa8" stroke-dasharray="9 6" style="stroke-width:3"/>
<path class="cw" d="M680 740 H1085" stroke="#9aa3b5" stroke-dasharray="9 6" style="stroke-width:3"/>
<path class="cw" d="M725 755 H995" stroke="#2ec4b6" stroke-dasharray="9 6" style="stroke-width:3"/>
<path class="cw" d="M730 770 H970" stroke="#e9c46a" stroke-dasharray="9 6" style="stroke-width:3"/>
<rect x="640" y="790" width="180" height="78" rx="6" fill="#0b0d12" stroke="#3a3f4f" stroke-width="2"/>
<rect x="655" y="812" width="150" height="46" rx="3" fill="#061424" stroke="#2a4a6a"/><text x="730" y="842" text-anchor="middle" style="fill:#5BC0EB;font-size:18px;font-weight:700">21,4°</text>
<path class="cw" d="M680 790 V740" stroke="#9aa3b5" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="680" cy="740" r="4" fill="#9aa3b5"/>
<circle cx="680" cy="795" r="4" fill="#e0b84a"/><text x="680" y="808" class="pin" text-anchor="middle">GND</text>
<path class="cw" d="M705 790 V725" stroke="#ff7aa8" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="705" cy="725" r="4" fill="#ff7aa8"/>
<circle cx="705" cy="795" r="4" fill="#e0b84a"/><text x="705" y="808" class="pin" text-anchor="middle">VCC</text>
<path class="cw" d="M730 790 V770" stroke="#e9c46a" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="730" cy="770" r="4" fill="#e9c46a"/>
<circle cx="730" cy="795" r="4" fill="#e0b84a"/><text x="730" y="808" class="pin" text-anchor="middle">SCL</text>
<path class="cw" d="M755 790 V755" stroke="#2ec4b6" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="755" cy="755" r="4" fill="#2ec4b6"/>
<circle cx="755" cy="795" r="4" fill="#e0b84a"/><text x="755" y="808" class="pin" text-anchor="middle">SDA</text>
<text x="730" y="884" class="m" text-anchor="middle"><?= t('svgOled') ?></text>
<rect x="900" y="790" width="120" height="70" rx="6" fill="#1f4e8c" stroke="#2f6ab0" stroke-width="2"/><rect x="948" y="824" width="24" height="18" rx="2" fill="#c9cfe0"/>
<path class="cw" d="M920 790 V725" stroke="#ff7aa8" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="920" cy="725" r="4" fill="#ff7aa8"/>
<circle cx="920" cy="795" r="4" fill="#e0b84a"/><text x="920" y="808" class="pin" text-anchor="middle">VIN</text>
<path class="cw" d="M945 790 V740" stroke="#9aa3b5" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="945" cy="740" r="4" fill="#9aa3b5"/>
<circle cx="945" cy="795" r="4" fill="#e0b84a"/><text x="945" y="808" class="pin" text-anchor="middle">GND</text>
<path class="cw" d="M970 790 V770" stroke="#e9c46a" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="970" cy="770" r="4" fill="#e9c46a"/>
<circle cx="970" cy="795" r="4" fill="#e0b84a"/><text x="970" y="808" class="pin" text-anchor="middle">SCL</text>
<path class="cw" d="M995 790 V755" stroke="#2ec4b6" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="995" cy="755" r="4" fill="#2ec4b6"/>
<circle cx="995" cy="795" r="4" fill="#e0b84a"/><text x="995" y="808" class="pin" text-anchor="middle">SDA</text>
<text x="960" y="884" class="m" text-anchor="middle"><?= t('svgSens') ?></text>
</g>
</svg></div>
<div class="hint"><?= t('wireHint') ?></div>
<div class="row2" style="margin-top:14px">
<div>
<?= t('cables') ?></div>
<div>
<?= t('steps') ?></div></div>

</div>

  <div class="card sp" id="descargas">
    <h2 class="sec"><?= t('dlTitle') ?><?= $version ? ' · ' . t('version') . ' ' . htmlspecialchars($version) : '' ?></h2>
    <div class="dl3">
      <div class="dlc"><b><?= t('dlFw') ?></b>
        <span class="muted"><?= t('dlFwSub') ?><?php if (is_file($fwZip)): ?> · <?= number_format(filesize($fwZip) / 1024, 0, ',', '.') ?> KB · <?= date('d/m/Y', filemtime($fwZip)) ?><?php endif; ?></span>
        <a class="b" href="/descargas/WTTC-firmware.zip">⬇ WTTC-firmware.zip</a></div>
      <div class="dlc"><b><?= t('dlApp') ?></b>
        <span class="muted"><?= t('dlAppSub') ?></span>
        <a class="b" href="https://github.com/matatunos/wttc/releases/download/v<?= htmlspecialchars($version) ?>/WTTC.apk">⬇ WTTC.apk</a></div>
<?php $otaF = 'WTTC-' . $version . '-s3.ota'; $otaP = __DIR__ . '/descargas/ota/' . $otaF; ?>
      <div class="dlc"><b><?= t('dlOta') ?></b>
        <span class="muted"><?= t('dlOtaSub') ?><?php if (is_file($otaP)): ?> · <?= number_format(filesize($otaP) / 1024, 0, ',', '.') ?> KB<?php endif; ?></span>
        <?php if (is_file($otaP)): ?><a class="b" href="/descargas/ota/<?= htmlspecialchars($otaF) ?>" download>⬇ <?= htmlspecialchars($otaF) ?></a><?php else: ?><span class="muted"><?= t('dlOtaSoon') ?></span><?php endif; ?></div>
      <div class="dlc"><b><?= t('dlSrc') ?></b>
        <span class="muted"><?= t('dlSrcSub') ?></span>
        <a class="b" href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">GitHub ↗</a></div>
    </div>
    <details class="vers"><summary><?= t('versions') ?></summary>
      <?= is_file(__DIR__ . '/descargas/CHANGELOG.md') ? wttc_changelog(file_get_contents(__DIR__ . '/descargas/CHANGELOG.md')) : '<p class="muted">' . t('noData') . '</p>' ?>
      <p class="muted"><?= t('allVersions') ?><a href="https://github.com/matatunos/wttc/releases" target="_blank" rel="noopener">github.com/matatunos/wttc/releases</a></p>
    </details>
    <div class="hint"><?= t('dlHint') ?></div>

<?= t('hw') ?>
<div class="hint"><?= t('hwHint') ?></div>


<div class="row2" style="margin-top:14px">
<div>
<?= t('instNeed') ?>
</div>
<div>
<?= t('instIde') ?>
</div>
</div>

<div class="row2" style="margin-top:14px">
<div>
<?= t('cliLinux') ?>
</div>
<div>
<?= t('cliWin') ?>
</div>
</div>
  </div>

  <div class="card sp" id="uso">
    <h2 class="sec"><?= t('howTo') ?></h2>
    <div class="row3">
      <div>
        <?= t('useAndroid') ?>
      </div>
      <div>
        <?= t('useIphone') ?>
      </div>
      <div>
        <?= t('useRadio') ?>
      </div>
    </div>

    <div class="row2" style="margin-top:14px">
      <div>
        <?= t('power') ?>
      </div>
      <div>
        <?= t('tgHow') ?>
      </div>
    </div>

    <?= t('security') ?>
  </div>

  <div class="row2">
    <div class="card">
      <?= t('framesHead') ?>
        <tbody id="frames"></tbody>
      </table>
      <form class="calc" id="calc">
        <label><?= t('cCmd') ?><input id="cCmd" value="21" maxlength="2"></label>
        <label><?= t('cDat') ?><input id="cDat" class="wide" value="1E"></label>
        <div><div class="muted" style="font-size:.72rem"><?= t('cOut') ?></div><div class="out" id="cOut">—</div></div>
      </form>
    </div>
    <div class="card">
      <?= t('simNotes') ?>
    </div>
  </div>
  <div class="card sp" id="responsabilidad">
    <?= t('legal') ?>
    </div>
  </div>

  <p class="credito"><?= t('credit') ?></p>
</div>

<script>
(() => {
'use strict';
const $ = id => document.getElementById(id);
// ---- Idioma de la página y textos ----
// S: textos del simulador (i18n/<idioma>.php → sim). FT: textos del firmware, sacados de la tabla TXT de WTTC.ino
// por wttc-publicar.sh (descargas/fwtextos.json): el ESP32 simulado dice exactamente lo mismo que el real.
const PAGE_LANG = <?= json_encode($lang) ?>, LANGS = ['es', 'en', 'de'];
const S = <?= json_encode($T['sim'], JSON_UNESCAPED_UNICODE) ?>;
const FT = <?= is_file(__DIR__ . '/descargas/fwtextos.json') ? file_get_contents(__DIR__ . '/descargas/fwtextos.json') : '{}' ?>;
const F = (s, ...a) => String(s).replace(/\{(\d)\}/g, (m, i) => a[i]);
// Texto del firmware en el idioma de la placa simulada; trf() rellena %s y %d como el printf del firmware
const tf = k => (FT[k] || [k])[prefs.lang] || (FT[k] || [k])[0];
const trf = (k, ...a) => { let i = 0; return tf(k).replace(/%[sd]/g, () => a[i++]); };
// Números del ESP32 simulado: con la coma o el punto del idioma de la placa, como num() del firmware
const fwLoc = () => ({ es: 'es-ES', en: 'en-GB', de: 'de-DE' })[LANGS[prefs.lang]];
const srcName = s => tf(s === 'programa' ? 'T_SRC_PROG' : s === 'app' ? 'T_SRC_APP' : s === 'consola' ? 'T_SRC_CONSOLE' : 'T_SRC_MANUAL');
const hx = b => b.toString(16).toUpperCase().padStart(2, '0');
const hexs = a => a.map(hx).join(' ');
const xor = a => a.reduce((c, b) => c ^ b, 0);
const pad = n => String(n).padStart(2, '0');
const fmt1 = v => v.toLocaleString(S.loc, { minimumFractionDigits: 1, maximumFractionDigits: 1 });

// ================= Tiempo simulado =================
// simMs = millis() del ESP32 desde su último arranque; worldMs = reloj del mundo (no se reinicia)
let simMs = 0, worldMs = Date.now(), speed = 1;

// ================= Entorno y averías =================
const env = { amb: 5, batt: 12.6, scr: true, sens: true, f: { wbus: false, slp: false, cross: false, power: false, fuel: false, noise: false } };

// ================= Webasto virtual =================
const ST = {
  OFF:   { c: 0x04, t: S.st.OFF },
  FAN:   { c: 0x13, t: S.st.FAN, dur: 12000 },
  GLOW:  { c: 0x2e, t: S.st.GLOW, dur: 45000 },
  IGN:   { c: 0x34, t: S.st.IGN, dur: 30000 },
  STAB:  { c: 0x40, t: S.st.STAB, dur: 60000 },
  FULL:  { c: 0x06, t: S.st.FULL },
  PART:  { c: 0x05, t: S.st.PART },
  PAUSE: { c: 0x20, t: S.st.PAUSE },
  AFTER: { c: 0x45, t: S.st.AFTER, dur: 120000 },
  FAIL:  { c: 0x47, t: S.st.FAIL, dur: 90000 },
  LOCK:  { c: 0x15, t: S.st.LOCK },
};
const ACTIVE = ['FAN', 'GLOW', 'IGN', 'STAB', 'FULL', 'PART', 'PAUSE'];
const T_PART = 75, T_PAUSE = 85, T_RESUME = 70, REFRESH_MS = 20000;
const AMPS = { OFF: 0.02, FAN: 2, GLOW: 8.5, IGN: 9, STAB: 4, FULL: 3.5, PART: 2.6, PAUSE: 1.6, AFTER: 2, FAIL: 2, LOCK: 0.02 };
const ERRN = S.errn;   // nombres de las averías (claves: código en decimal)

const H = { st: 'OFF', t: 0, temp: env.amb, cab: env.amb + 2, cmd: 0, runLeft: 0, refresh: 0, tries: 0, fails: 0, errs: new Map(), pw: 0, hist: [], lastH: -1e12 };

function hSet(st) { H.st = st; H.t = 0; }
function hErr(code) { H.errs.set(code, Math.min(255, (H.errs.get(code) || 0) + 1)); }
function hFlame() { return ['STAB', 'FULL', 'PART'].includes(H.st) ? 1 : 0; }
function hVolt() { return env.f.power ? 0 : Math.max(8, env.batt - AMPS[H.st] * 0.06); }
function hStop(reason) {
  H.cmd = 0;
  if (['FAN', 'GLOW', 'IGN', 'STAB', 'FULL', 'PART', 'PAUSE'].includes(H.st)) hSet('AFTER');
  if (reason) H.lastStop = reason;
}
function hOn(min) {
  H.cmd = 0x21; H.runLeft = min * 60000; H.refresh = 0;
  if (H.st === 'LOCK') { hErr(0x07); return; }
  if (!ACTIVE.includes(H.st)) { H.tries = 0; hSet('FAN'); }
}
function hTick(dt) {
  if (env.f.power) { if (H.st !== 'LOCK') { H.st = 'OFF'; H.cmd = 0; } }
  else {
    H.t += dt;
    if (ACTIVE.includes(H.st) && H.cmd) {
      H.runLeft -= dt; H.refresh += dt;
      if (H.runLeft <= 0) hStop(S.stopTime);
      else if (H.refresh > REFRESH_MS) hStop(S.stopKa);
    }
    const d = ST[H.st].dur;
    switch (H.st) {
      case 'FAN':  if (H.t >= d) hSet('GLOW'); break;
      case 'GLOW': if (H.t >= d) hSet('IGN'); break;
      case 'IGN':
        if (H.t >= d) {
          if (!env.f.fuel) { H.fails = 0; hSet('STAB'); }
          else if (++H.tries < 2) hSet('GLOW');           // segundo intento dentro del mismo arranque
          else {
            hErr(0x02); H.cmd = 0;
            if (++H.fails >= 3) { hErr(0x07); hSet('LOCK'); } else hSet('FAIL');
          }
        }
        break;
      case 'STAB':  if (H.t >= d) hSet(H.temp >= T_PART ? 'PART' : 'FULL'); break;
      case 'FULL':  if (H.temp >= T_PART) hSet('PART'); break;
      case 'PART':  if (H.temp >= T_PAUSE) hSet('PAUSE'); break;
      case 'PAUSE': if (H.temp <= T_RESUME) { H.tries = 0; hSet('FAN'); } break;
      case 'AFTER': case 'FAIL': if (H.t >= d) hSet('OFF'); break;
    }
  }
  // Potencia y circuito de refrigeración (≈ 9 L + bloque; aproximado)
  H.pw = H.st === 'FULL' ? 5000 : H.st === 'PART' ? 2500 : H.st === 'STAB' ? Math.round(2500 + 2500 * Math.min(1, H.t / 60000)) : 0;
  const loss = (ACTIVE.includes(H.st) ? 38 : 12) * (H.temp - env.amb);
  H.temp += (H.pw - loss) * (dt / 1000) / 75000;
  // Habitáculo (para el termómetro opcional): con la Webasto en marcha el ventilador mete el calor del agua; si no,
  // se enfría hacia la temperatura de fuera. Muy aproximado: ~0,5 °C/min calentando con el agua a 75 °C
  const heat = ACTIVE.includes(H.st) && H.temp > 30 ? (H.temp - H.cab) * 0.00012 : 0;
  H.cab += (heat - (H.cab - env.amb) * 0.00006) * dt / 1000;
  if (worldMs - H.lastH >= 15000) { H.hist.push([worldMs, H.temp]); H.lastH = worldMs; if (H.hist.length > 600) H.hist.shift(); }
}
// Procesa una trama válida dirigida a la Webasto; devuelve los datos de la respuesta o null
function hHandle(cmd, d) {
  switch (cmd) {
    case 0x10: hStop(S.stopCmd); return [];
    case 0x21: hOn(Math.max(1, d[0] || 0)); return [d[0] || 0];
    case 0x44: {
      const ok = H.cmd === d[0] && ACTIVE.includes(H.st);
      if (ok) H.refresh = 0;
      return [ok ? 0 : 1];
    }
    case 0x50:
      if (d[0] !== 0x05) return null;
      { const mv = Math.round(hVolt() * 1000), t = Math.max(0, Math.min(255, Math.round(H.temp) + 50)), gpr = 1100 + Math.round(H.temp * 6);
        return [0x05, t, mv >> 8, mv & 255, hFlame(), H.pw >> 8, H.pw & 255, gpr >> 8, gpr & 255]; }
    case 0x56:
      if (d[0] === 0x01) { const r = [0x01, H.errs.size]; for (const [c, n] of H.errs) r.push(c, n); return r; }
      if (d[0] === 0x03) { H.errs.clear(); return [0x03]; }
      return null;
  }
  return null;
}

// ================= Bus: TJA1020 + cable + Webasto =================
const trace = [];
let trDirty = true;
function tr(k, bytes, txt, rep, bad) { trace.push({ ts: worldMs, k, bytes, txt, rep, bad }); if (trace.length > 400) trace.shift(); trDirty = true; }

function decodeTx(f) {
  const c = f[2], d = f.slice(3, -1);
  if (c === 0x21) return F(S.txOn, d[0]);
  if (c === 0x10) return S.txOff;
  if (c === 0x44) return F(S.txKa, hx(d[0]));
  if (c === 0x50) return d[0] === 5 ? S.txSens : F(S.txReg, hx(d[0]));
  if (c === 0x56) return d[0] === 1 ? S.txErrList : d[0] === 3 ? S.txErrClr : S.txErr;
  return F(S.txCmd, hx(c));
}
function decodeRx(b) {
  const c = b[2] & 0x7F, d = b.slice(3, -1);
  if (c === 0x21) return F(S.rxOn, d[0]);
  if (c === 0x10) return S.rxOff;
  if (c === 0x44) return d[0] ? S.rxKaOff : S.rxKaOn;
  if (c === 0x50 && d[0] === 5) {
    const v = ((d[2] << 8) | d[3]) / 1000;
    return F(S.rxSens, d[1] - 50, fmt1(v), d[4] ? S.yes : S.no, (d[5] << 8) | d[6]);
  }
  if (c === 0x56 && d[0] === 1) {
    if (!d[1]) return S.rxNoErr;
    const l = []; for (let i = 0; i < d[1]; i++) l.push(`0x${hx(d[2 + 2 * i])} ×${d[3 + 2 * i]}`);
    return F(S.rxErrs, d[1], l.join(', '));
  }
  return S.rxResp;
}

// Lo que el ESP32 lee por RX al transmitir la trama f
function busTransact(f, rep) {
  const silent = env.f.slp || env.f.cross;
  tr('tx', f, decodeTx(f), rep);
  if (silent) { tr('echo', [], env.f.slp ? S.noEchoSlp : S.noEchoCross, rep, true); return []; }
  tr('echo', f, S.echo, rep);
  if (env.f.wbus || env.f.power || f[0] !== 0xF4 || xor(f.slice(0, -1)) !== f[f.length - 1]) {
    tr('rx', [], env.f.wbus ? S.noRxWbus : S.noRxPower, rep, true);
    return [];
  }
  const data = hHandle(f[2], f.slice(3, -1));
  if (!data) { tr('rx', [], S.noRxCmd, rep, true); return []; }
  const r = [0x4F, data.length + 2, f[2] | 0x80, ...data];
  r.push(xor(r));
  if (env.f.noise && Math.random() < 0.25) {
    const i = 3 + Math.floor(Math.random() * (r.length - 3)); r[i] ^= 1 << Math.floor(Math.random() * 8);
    tr('rx', r, S.noise, rep, true);
  } else tr('rx', r, decodeRx(r), rep);
  return r;
}

// ================= ESP32 (traducción de firmware/WTTC/WTTC.ino) =================
const FW = <?= json_encode($version ?: '0.1.0') ?>;   // versión del firmware simulado (fichero VERSION)
const FUEL_L_KWH = 0.124, MAX_MIN = 60, KEEPALIVE_MS = 5000, SENSOR_MS = 8000, MAX_SCHED = 8, PAUSE_TEMP = 65, NOFLAME_MS = 300000;
const TH_STALL = 1500000, TH_STALL_C = 0.5;
const TH_MINRUN_WARM = 300000, TH_WARM_C = 30;
const MAX_SESSION = 240, TH_HYST = 1.5, TH_MINRUN = 900000, TH_REST = 180000, BATT_RUN_DROP = 0.5, BATT_GRACE = 180000, SX_DEP = 0x80, SX_TGT = 0x3F;
const PH_OFF = 0, PH_START = 1, PH_FLAME = 2, PH_PAUSE = 3, PH_LOST = 4;
const prefs = { sch: [], auto: true, ssid: '', pass: '', tgtok: '', tgchat: '', name: 'WTTC', appass: 'calefaccion',
  pin: 100000 + Math.floor(Math.random() * 900000), wifimode: 0, minvolt: 12.0, lang: Math.max(0, LANGS.indexOf(PAGE_LANG)),
  gasLast: 0, gasMonth: 0, gasTotal: 0, gasKey: 0, oled: 2, disp: 1, led: 1, toff: 0, warm: 0, dep: 0, dept: 0 };   // NVS: sobrevive a los reinicios
const tgSent = [];   // lo que llegaría al chat de Telegram (fuera del ESP32: no se borra al reiniciar)
let E;
const con = [];
let conDirty = true;
function serialPrint(s, cls) { for (const l of String(s).split('\n')) con.push({ l, cls }); if (con.length > 500) con.splice(0, con.length - 500); conDirty = true; }

function espBoot() {
  simMs = 0;
  E = { heaterOn: false, onUntil: 0, onTotal: 0, lastKA: 0, lastSensor: 0, lastUi: -1e9, lastComm: 0, onSrc: '',
    kaFails: 0, kaOff: 0, gasCur: 0, gasRate: 0, lastGasT: 0, phase: PH_OFF, flameSeen: false, noFlameSince: 0, stopNote: '',
    busState: -1, tempC: -999, flame: -1, power: -1, volt: -1, lastTx: '', lastRx: '', lastMinute: -1,
    log: [], epoch0: null, epochAt: 0, sta: false, down: false, tgQueue: [], tgLast: '',
    heatStart: 0, lastSensorOk: 0, lowBatt: 0, warmSent: false, quietStart: false, lastHeatOff: 0,
    cabT: NaN, cabH: NaN, snLast: -1e9, thActive: false, thReached: false, thTarget: 0, thUntil: 0, thSrc: '', thLast: 0,
    depDone: [], depOnceDone: 0, btnUntil: 0, snOk: false, snFails: 0, oledOk: false, dispOn: false, dispUntil: 0, lastDraw: -1e9, hwProbeAt: 0 };
  addLog(trf('T_LOG_BOOT', FW));
  hwProbe();   // pantalla y termómetro, según lo marcado en la tarjeta «Placa»
  E.dispUntil = simMs + 60000;
  serialPrint(`WTTC ${FW} | Bluetooth y Wi-Fi: "${prefs.name}" | PIN Bluetooth: ${prefs.pin}`);
  serialPrint('Consola: on [min] [°C] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot');
  if (prefs.ssid) setTimeout(() => {   // se une a la red guardada y coge la hora por NTP
    if (!E.down) { E.sta = true; setEpoch(Math.floor(worldMs / 1000)); }
  }, 1500);
}
function espTime() { return E.epoch0 == null ? Math.floor(simMs / 1000) : E.epoch0 + Math.floor((simMs - E.epochAt) / 1000); }
function timeValid() { return espTime() > 1700000000; }
function setEpoch(e) { E.epoch0 = e; E.epochAt = simMs; }
function addLog(m) {
  if (REC.on) REC.ev.push({ t: (simMs - REC.t0) / 60000, m });
  let ts = '--/-- --:--';
  if (timeValid()) { const d = new Date(espTime() * 1000); ts = `${pad(d.getDate())}/${pad(d.getMonth() + 1)} ${pad(d.getHours())}:${pad(d.getMinutes())}`; }
  const e = ts + '  ' + m;
  serialPrint(e);
  E.log.push(e); if (E.log.length > 20) E.log.shift();
}
function hhmm() { if (!timeValid()) return ''; const d = new Date(espTime() * 1000); return `${pad(d.getHours())}:${pad(d.getMinutes())} `; }

// Avisos por Telegram: cola en RAM; se "envían" cuando el ESP32 está conectado a una red (hasta 10 min)
function notify(m) {
  if (REC.on && prefs.tgtok && prefs.tgchat) REC.tg.push({ t: (simMs - REC.t0) / 60000, m });
  if (!prefs.tgtok || !prefs.tgchat) return;
  if (E.tgQueue.length >= 6) { E.tgLast = tf('T_TG_QUEUE_FULL'); return; }
  E.tgQueue.push({ t: `Webasto · ${hhmm()}${m}`, at: simMs });
}
function tgPump() {
  while (E.tgQueue.length) {
    const x = E.tgQueue[0];
    if (E.sta) { tgSent.push({ ts: worldMs, t: x.t }); if (tgSent.length > 50) tgSent.shift(); E.tgLast = trf('T_TG_SENT', x.t); tgDirty = true; }
    else if (simMs - x.at >= 600000) E.tgLast = trf('T_TG_NO_NET', x.t);
    else return;
    E.tgQueue.shift();
  }
}

function wbusCmd(cmd, data, rep) {
  if (simMs - E.lastComm > 10000) tr('brk', [], S.brk, rep);
  const f = [0xF4, data.length + 2, cmd, ...data]; f.push(xor(f));
  const b = busTransact(f, rep);
  E.lastComm = simMs;
  E.lastTx = hexs(f);
  E.lastRx = b.length ? hexs(b) : '(sin respuesta)';
  const need = b.length >= 2 ? b[1] + 2 : -1;
  let ok = need > 3 && b.length >= need;
  if (ok) ok = xor(b.slice(0, need - 1)) === b[need - 1] && b[2] === (cmd | 0x80);
  E.busState = ok ? 1 : 0;
  return ok ? b.slice(3, 3 + b[1] - 2) : null;
}
function readSensors() {
  const r = wbusCmd(0x50, [0x05], true);
  const ok = !!(r && r.length >= 4 && r[0] === 0x05);
  if (ok) {
    E.tempC = r[1] - 50;
    E.volt = ((r[2] << 8) | r[3]) / 1000;
    if (r.length >= 5) E.flame = r[4];
    if (r.length >= 7) E.power = (r[5] << 8) | r[6];
    E.lastSensorOk = simMs;
  }
  E.lastSensor = simMs;
  return ok;
}
function errorsText() {
  const r = wbusCmd(0x56, [0x01]);
  if (!r) return tf('T_ERR_NORESP');
  if (r.length < 2 || !r[1]) return tf('T_ERR_NONE');
  const l = []; for (let i = 0; i < r[1] && 3 + 2 * i < r.length; i++) l.push(`0x${hx(r[2 + 2 * i])} (${r[3 + 2 * i]})`);
  return l.join(', ');
}
// Gasoil estimado: potencia que informa la Webasto × tiempo (igual que gasTick() del firmware)
const litros = l => l.toLocaleString(fwLoc(), { minimumFractionDigits: l < 10 ? 2 : 1, maximumFractionDigits: l < 10 ? 2 : 1 }) + ' l';
function gasTick() {
  if (E.lastGasT) { const l = E.gasRate * (simMs - E.lastGasT) / 3600000; E.gasCur += l; prefs.gasMonth += l; prefs.gasTotal += l; }
  E.lastGasT = simMs;
  E.gasRate = E.flame > 0 ? (E.power > 0 ? E.power / 1000 : 3.75) * FUEL_L_KWH : 0;
  if (timeValid()) { const d = new Date(espTime() * 1000), key = d.getFullYear() * 100 + d.getMonth() + 1;
    if (prefs.gasKey && key !== prefs.gasKey) prefs.gasMonth = 0; prefs.gasKey = key; }
}
function startHeater(minutes, src) {
  minutes = Math.max(1, Math.min(MAX_MIN, minutes));
  for (let i = 0; i < 3; i++) {
    if (wbusCmd(0x21, [minutes & 255])) {
      E.heaterOn = true; E.onTotal = minutes * 60; E.onUntil = simMs + minutes * 60000;
      E.lastKA = simMs; E.kaFails = 0; E.kaOff = 0; E.onSrc = src;
      E.phase = PH_START; E.flameSeen = false; E.noFlameSince = simMs; E.stopNote = '';
      E.gasCur = 0; E.gasRate = 0; E.lastGasT = 0;
      E.heatWarm = E.tempC >= TH_WARM_C && E.lastSensorOk && simMs - E.lastSensorOk < 120000;
      E.heatStart = simMs; E.warmSent = false; E.lowBatt = 0; E.thBest = E.cabT; E.thBestAt = simMs; E.dispUntil = simMs + 60000;
      addLog(trf('T_LOG_ON', srcName(src), minutes));
      if (!E.quietStart) notify(trf('T_LOG_ON', srcName(src), minutes));
      return true;
    }
  }
  addLog(tf('T_LOG_ON_FAIL'));
  notify(trf('T_TG_ON_FAIL', srcName(src)));
  return false;
}
function stopHeater(why, tell) {
  let ok = false;
  const was = E.heaterOn;
  for (let i = 0; i < 3 && !ok; i++) ok = !!wbusCmd(0x10, []);
  if (was) { gasTick(); E.gasRate = 0; E.lastGasT = 0; prefs.gasLast = E.gasCur; }
  E.heaterOn = false; E.phase = PH_OFF; E.lastHeatOff = simMs;
  const g = was ? trf('T_GAS_SUFFIX', litros(prefs.gasLast)) : '';
  addLog(trf(ok ? 'T_OFF' : 'T_OFF_NOCONF', why) + g);
  if (tell) notify(trf('T_OFF', why) + g);
  return ok;
}
function heaterQuit(why) {
  endSession(true);
  stopHeater(why, false);
  const e = errorsText();
  addLog(trf('T_FAULTS', e));
  E.stopNote = hhmm() + trf('T_NOTE_QUIT', why, e);
  notify(trf('T_TG_QUIT', why, e, litros(prefs.gasLast)));
}
function evalHeater() {
  if (E.flame > 0) { E.flameSeen = true; E.noFlameSince = 0; E.phase = PH_FLAME; return; }
  if (E.flame < 0) return;
  if (E.flameSeen && E.tempC >= PAUSE_TEMP) { E.phase = PH_PAUSE; E.noFlameSince = 0; return; }
  E.phase = PH_START;
  if (!E.noFlameSince) E.noFlameSince = simMs;
  if (simMs - E.noFlameSince >= NOFLAME_MS) heaterQuit(tf(E.flameSeen ? 'T_WHY_FLAME_OUT' : 'T_WHY_NO_IGNITION'));
}
// ---------- termómetro, batería, aviso de agua caliente, termostato y hora de salida (como en el firmware) ----------
const numf = (v, d) => v.toLocaleString(fwLoc(), { minimumFractionDigits: d, maximumFractionDigits: d });
const degs = (t, d) => numf(t, d) + ' °C';
function hwProbe() {
  E.hwProbeAt = simMs;
  const w = [];
  if (!E.snOk && env.sens) { E.snOk = true; E.snFails = 0; E.snLast = -1e9; w.push('SHT31'); }
  if (!E.oledOk && env.scr) { E.oledOk = true; w.push(['OLED SH1106', 'OLED SSD1306', 'OLED SSD1327'][prefs.oled]); }
  if (w.length) addLog(trf('T_LOG_HW', w.join(', ')));
}
function snTick() {
  if (!E.snOk || simMs - E.snLast < 10000) return;
  E.snLast = simMs;
  // Termómetro quitado: como en el firmware, sin dato tras 3 lecturas fallidas y se da por desconectado tras 6
  if (!env.sens) { if (++E.snFails >= 3) E.cabT = E.cabH = NaN; if (E.snFails >= 6) E.snOk = false; return; }
  E.snFails = 0;
  E.cabT = Math.round((H.cab + (Math.random() - 0.5) * 0.1) * 10) / 10 + prefs.toff;
  E.cabH = Math.max(20, Math.min(90, Math.round(65 - (H.cab - env.amb) * 1.2)));
}
function battOk() {
  readSensors();
  if (E.volt > 0 && E.volt < prefs.minvolt) { const v = numf(E.volt, 1); addLog(trf('T_LOG_SKIP', v)); notify(trf('T_LOG_SKIP', v)); return false; }
  return true;
}
function battRunCheck() {
  if (!E.heaterOn || simMs - E.heatStart < BATT_GRACE || E.volt <= 0 || E.volt >= prefs.minvolt - BATT_RUN_DROP) { E.lowBatt = 0; return; }
  if (++E.lowBatt < 3) return;
  const v = numf(E.volt, 1);
  endSession(false);
  stopHeater(trf('T_WHY_BATT', v), true);
  E.stopNote = hhmm() + trf('T_NOTE_BATT', v);
  E.lowBatt = 0;
}
function warmCheck() {
  if (!E.heaterOn || !prefs.warm || E.warmSent || E.tempC < prefs.warm) return;
  E.warmSent = true; E.dispUntil = simMs + 60000;
  addLog(trf('T_TG_WARM', E.tempC)); if (!(E.thActive && E.thReached)) notify(trf('T_TG_WARM', E.tempC));
}
function endSession(log) { if (!E.thActive) return; E.thActive = false; if (log) addLog(tf('T_LOG_TH_END')); }
function startSession(minutes, tgt, src) {
  if (isNaN(E.cabT)) { addLog(tf('T_LOG_TH_NOSENS')); return false; }
  minutes = Math.max(1, Math.min(MAX_SESSION, minutes));
  Object.assign(E, { thActive: true, thReached: false, thTarget: tgt, thSrc: src, thUntil: simMs + minutes * 60000 });
  addLog(trf('T_LOG_TH_ON', degs(tgt, 0), minutes));
  if (E.cabT >= tgt) { E.thReached = true; addLog(trf('T_LOG_TH_WAIT', degs(E.cabT, 1))); return true; }
  if (!startHeater(Math.min(minutes, MAX_MIN), src)) { E.thActive = false; return false; }
  return true;
}
function thermoTick() {
  if (!E.thActive || simMs - E.thLast < 5000) return;
  E.thLast = simMs;
  if (simMs >= E.thUntil) { endSession(true); if (E.heaterOn) stopHeater(tf('T_WHY_END'), true); return; }
  if (isNaN(E.cabT)) { addLog(tf('T_LOG_TH_NOSENS')); E.thActive = false; return; }
  if (E.heaterOn) {
    if (isNaN(E.thBest) || E.cabT >= E.thBest + TH_STALL_C) { E.thBest = E.cabT; E.thBestAt = simMs; }
    else if (E.cabT < E.thTarget && simMs - E.thBestAt >= TH_STALL) {
      const m = trf('T_TG_TH_STALL', degs(E.cabT, 1), Math.floor((simMs - E.heatStart) / 60000), degs(E.thTarget, 0));
      endSession(false); stopHeater(tf('T_LOG_TH_END'), false); addLog(m); notify(m); E.stopNote = hhmm() + m;
      return;
    }
    if (E.cabT >= E.thTarget && simMs - E.heatStart >= (E.heatWarm ? TH_MINRUN_WARM : TH_MINRUN)) {
      const t = degs(E.cabT, 1);
      if (!E.thReached) notify(trf('T_TG_TH_REACHED', t));
      E.thReached = true;
      stopHeater(trf('T_WHY_TARGET', t), false);
    }
    return;
  }
  const left = E.thUntil - simMs;
  const cold = E.cabT <= E.thTarget - TH_HYST || (!E.thReached && E.cabT < E.thTarget);
  if (cold && left >= TH_MINRUN && (!E.lastHeatOff || simMs - E.lastHeatOff >= TH_REST)) {
    if (!battOk()) { endSession(true); return; }
    E.quietStart = true;
    const ok = startHeater(Math.min(Math.floor(left / 60000), MAX_MIN), E.thSrc);
    E.quietStart = false;
    if (!ok) endSession(true);
  }
}
const depLead = t => isNaN(t) ? 30 : Math.max(15, Math.min(60, Math.round(15 + (15 - t) * 1.5)));
function depTemp() {
  if (!isNaN(E.cabT)) return E.cabT;
  if (E.tempC > -100 && E.lastSensorOk && simMs - E.lastSensorOk < 600000) return E.tempC;
  return NaN;
}
// done: objeto { v } (el firmware usa una referencia)
function depCheck(depMin, tgt, done, src) {
  const nowMin = Math.floor(espTime() / 60);
  if (depMin <= nowMin || done.v === depMin) return;
  const left = depMin - nowMin;
  if (left > 60) return;
  if (isNaN(E.cabT) && simMs - E.lastSensor > 300000) readSensors();
  const t = depTemp(), lead = depLead(t);
  if (left > lead) return;
  done.v = depMin;
  if (E.heaterOn || E.thActive || left < 10) return;
  if (tgt && !isNaN(E.cabT) && E.cabT >= tgt) { addLog(trf('T_LOG_TH_WAIT', degs(E.cabT, 1))); return; }
  if (!battOk()) return;
  const d = new Date(depMin * 60000);
  addLog(trf('T_LOG_DEP', `${pad(d.getHours())}:${pad(d.getMinutes())}`, left, isNaN(t) ? '?' : degs(t, 0)));
  if (tgt && !isNaN(E.cabT)) startSession(left, tgt, src); else startHeater(Math.min(left, MAX_MIN), src);
}
function depNext(h, mi) {
  const now = espTime(), d = new Date(now * 1000); d.setHours(h, mi, 0, 0);
  if (d.getTime() / 1000 <= now + 60) d.setDate(d.getDate() + 1);
  return Math.floor(d.getTime() / 60000);
}
function depSet(m, tgt) {
  prefs.dep = m; prefs.dept = m ? tgt : 0; E.depOnceDone = 0;
  if (!m) return;
  const d = new Date(m * 60000);
  addLog(trf('T_LOG_DEP_SET', `${pad(d.getDate())}/${pad(d.getMonth() + 1)} ${pad(d.getHours())}:${pad(d.getMinutes())}` + (tgt ? ' · ' + degs(tgt, 0) : '')));
}
function heatOn(m, tg, src) {
  if (tg) {
    if (tg < 5 || tg > 25) return tf('T_E_TARGET');
    if (isNaN(E.cabT)) return tf('T_E_NOSENS');
    endSession(false);
    return startSession(m > 0 ? m : 60, tg, src) ? '' : tf('T_E_ON');
  }
  endSession(false);
  return startHeater(m > 0 ? m : 30, src) ? '' : tf('T_E_ON');
}
// ---------- Pruebas e informe en PDF ----------
// Grabadora: cada 30 s simulados apunta lo que pasa (y el registro y los avisos); runTest() hace una prueba completa al
// momento (el tiempo simulado avanza de golpe) y savePdf() saca el informe con informe.js y jsPDF (los dos alojados aquí)
const REC = { on: false };
function recStart(p) { Object.assign(REC, { on: true, t0: simMs, next: simMs, p, s: [], ev: [], tg: [], gas0: prefs.gasTotal }); }
function recSample() {
  REC.s.push({ t: (simMs - REC.t0) / 60000, amb: env.amb, cab: H.cab, cabR: isNaN(E.cabT) ? null : E.cabT, water: H.temp, on: E.heaterOn,
    ph: E.phase, pw: H.pw, volt: env.f.power ? 0 : hVolt(), gas: Math.max(0, prefs.gasTotal - REC.gas0), tgt: E.thActive ? E.thTarget : 0 });
}
function recStop() {
  REC.on = false; recSample();
  const p = Object.assign({}, REC.p); if (p.manual) p.tgt = Math.max(0, ...REC.s.map(x => x.tgt));
  return { p, s: REC.s, ev: REC.ev, tg: REC.tg };
}
function runTest(p) {
  if (E.down) return null;
  if (E.heaterOn || E.thActive) { endSession(false); stopHeater(tf('T_SRC_MANUAL'), false); }
  hSet('OFF'); H.cmd = 0;
  env.amb = p.amb; env.batt = p.batt; H.cab = p.cab0; H.temp = p.amb;   // motor frío: a la temperatura de fuera
  if (!timeValid()) setEpoch(Math.floor(worldMs / 1000));
  E.snLast = -1e9; snTick();
  E.fast = true;
  recStart(p);
  route('/api/on', 'POST', new URLSearchParams({ min: p.min, tgt: p.tgt }));
  advance(p.min * 60000 + 60000);
  const r = recStop();
  E.fast = false; E.lastDraw = -1e9;
  return r;
}
const loadJs = src => new Promise((res, rej) => { const s = document.createElement('script'); s.src = src; s.onload = res; s.onerror = rej; document.head.appendChild(s); });
async function savePdf(runs) {
  if (!window.jspdf) await loadJs('/vendor/jspdf/2.5.1/jspdf.umd.min.js');
  if (!window.WTTCInforme) await loadJs('/informe.js');
  const d = new Date(worldMs), stamp = `${d.getFullYear()}${pad(d.getMonth() + 1)}${pad(d.getDate())}-${pad(d.getHours())}${pad(d.getMinutes())}`;
  WTTCInforme.build(window.jspdf.jsPDF, runs, PAGE_LANG, FW).save(`wttc-prueba-${stamp}.pdf`);
}

// ---------- Pantalla OLED y LED de la placa (como dispDraw(), dispTick() y ledTick() del firmware) ----------
const FONT = [0, 0, 0, 0, 0, 0, 0, 95, 0, 0, 0, 7, 0, 7, 0, 20, 127, 20, 127, 20, 36, 42, 127, 42, 18, 35, 19, 8, 100, 98, 54, 73, 85, 34, 80, 0, 5, 3, 0, 0, 0, 28, 34, 65, 0, 0, 65, 34, 28, 0, 20, 8, 62, 8, 20, 8, 8, 62, 8, 8, 0, 80, 48, 0, 0, 8, 8, 8, 8, 8, 0, 96, 96, 0, 0, 32, 16, 8, 4, 2, 62, 81, 73, 69, 62, 0, 66, 127, 64, 0, 66, 97, 81, 73, 70, 33, 65, 69, 75, 49, 24, 20, 18, 127, 16, 39, 69, 69, 69, 57, 60, 74, 73, 73, 48, 1, 113, 9, 5, 3, 54, 73, 73, 73, 54, 6, 73, 73, 41, 30, 0, 54, 54, 0, 0, 0, 86, 54, 0, 0, 8, 20, 34, 65, 0, 20, 20, 20, 20, 20, 0, 65, 34, 20, 8, 2, 1, 81, 9, 6, 50, 73, 121, 65, 62, 126, 17, 17, 17, 126, 127, 73, 73, 73, 54, 62, 65, 65, 65, 34, 127, 65, 65, 34, 28, 127, 73, 73, 73, 65, 127, 9, 9, 9, 1, 62, 65, 73, 73, 122, 127, 8, 8, 8, 127, 0, 65, 127, 65, 0, 32, 64, 65, 63, 1, 127, 8, 20, 34, 65, 127, 64, 64, 64, 64, 127, 2, 12, 2, 127, 127, 4, 8, 16, 127, 62, 65, 65, 65, 62, 127, 9, 9, 9, 6, 62, 65, 81, 33, 94, 127, 9, 25, 41, 70, 70, 73, 73, 73, 49, 1, 1, 127, 1, 1, 63, 64, 64, 64, 63, 31, 32, 64, 32, 31, 63, 64, 56, 64, 63, 99, 20, 8, 20, 99, 7, 8, 112, 8, 7, 97, 81, 73, 69, 67, 0, 127, 65, 65, 0, 2, 4, 8, 16, 32, 0, 65, 65, 127, 0, 4, 2, 1, 2, 4, 64, 64, 64, 64, 64, 0, 1, 2, 4, 0, 32, 84, 84, 84, 120, 127, 72, 68, 68, 56, 56, 68, 68, 68, 32, 56, 68, 68, 72, 127, 56, 84, 84, 84, 24, 8, 126, 9, 1, 2, 12, 82, 82, 82, 62, 127, 8, 4, 4, 120, 0, 68, 125, 64, 0, 32, 64, 68, 61, 0, 127, 16, 40, 68, 0, 0, 65, 127, 64, 0, 124, 4, 24, 4, 120, 124, 8, 4, 4, 120, 56, 68, 68, 68, 56, 124, 20, 20, 20, 8, 8, 20, 20, 24, 124, 124, 8, 4, 4, 8, 72, 84, 84, 84, 32, 4, 63, 68, 64, 32, 60, 64, 64, 32, 124, 28, 32, 64, 32, 28, 60, 64, 48, 64, 60, 68, 40, 16, 40, 68, 12, 80, 80, 80, 60, 68, 100, 84, 76, 68, 0, 8, 54, 65, 0, 0, 0, 127, 0, 0, 0, 65, 54, 8, 0, 8, 4, 8, 16, 8, 0, 6, 9, 9, 6];   // 5×7, columnas con el bit 0 arriba; 0x7F = °
const fb = new Uint8Array(1024);   // 128 columnas × 8 páginas, igual que en el ESP32
const dPix = (x, y) => { if (x >= 0 && x < 128 && y >= 0 && y < 64) fb[x + (y >> 3) * 128] |= 1 << (y & 7); };
function dText(x, y, t, sc) {
  for (const ch of t) {
    let c = ch.charCodeAt(0); if (c < 32 || c > 127) c = 63;
    for (let cx = 0; cx < 5; cx++) { const col = FONT[(c - 32) * 5 + cx];
      for (let cy = 0; cy < 7; cy++) if (col >> cy & 1) for (let a = 0; a < sc; a++) for (let b = 0; b < sc; b++) dPix(x + cx * sc + a, y + cy * sc + b); }
    x += 6 * sc;
  }
  return x;
}
const dWidth = (t, sc) => t.length ? t.length * 6 * sc - sc : 0;
// Solo ASCII, como la pantalla real: sin acentos y «°» con el símbolo propio
const plain = s => String(s).normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/°/g, '\x7f').replace(/ß/g, 's').replace(/[^\x20-\x7f]/g, '?');
function nextSched() {
  if (!prefs.auto || !timeValid()) return null;
  const d = new Date(espTime() * 1000), wd = (d.getDay() + 6) % 7, m = d.getHours() * 60 + d.getMinutes();
  let best = null;
  for (const s of prefs.sch) {
    if (!s.en) continue;
    for (let k = 0; k < 8; k++) { const dd = (wd + k) % 7; if (!(s.days >> dd & 1) || (k === 0 && s.start <= m)) continue;
      const dt = k * 1440 + s.start - m; if (!best || dt < best.dt) best = { dt, s, dd }; break; }
  }
  if (!best) return null;
  return { dep: !!(best.s.x & SX_DEP), t: tf('T_D_DAYS').split(',')[best.dd] + ' ' + pad(Math.floor(best.s.start / 60)) + ':' + pad(best.s.start % 60) };
}
function dispDraw() {
  fb.fill(0);
  dText(0, 0, plain(prefs.name.substring(0, 12)), 1);
  if (timeValid()) { const h = hhmm().trim(); dText(128 - dWidth(h, 1), 0, h, 1); }
  let big = '--', s1 = '', s2 = '';
  if (!isNaN(E.cabT)) { big = plain(numf(E.cabT, 1)) + '\x7f'; if (!isNaN(E.cabH)) s1 = Math.round(E.cabH) + '%'; s2 = plain(tf('T_D_IN')); }
  else if (E.tempC > -100) { big = E.tempC + '\x7f'; s2 = plain(tf('T_D_WATER')).split(' ')[0]; }
  const x = dText(0, 12, big, 3) + 3;
  if (x + dWidth(s1, 1) <= 128) dText(x, 14, s1, 1);
  if (x + dWidth(s2, 1) <= 128) dText(x, s1 ? 25 : 14, s2, 1);
  let st;
  if (E.heaterOn) {
    st = tf(E.phase === PH_FLAME ? 'T_D_HEAT' : E.phase === PH_PAUSE ? 'T_D_PAUSE' : E.phase === PH_LOST ? 'T_D_LOST' : 'T_D_START');
    const rem = Math.trunc((E.onUntil - simMs) / 60000) + 1; if (rem > 0) st += ' ' + rem + "'";
  } else st = tf(E.thActive ? 'T_D_WAIT' : 'T_D_OFF');
  dText(0, 36, plain(st), 1);
  let inf = '';
  if (E.tempC > -100 && !isNaN(E.cabT)) inf = plain(trf('T_D_WATER', E.tempC));
  if (E.volt > 0) { if (inf) inf += '  '; inf += plain(numf(E.volt, 1)) + 'V'; }
  dText(0, 46, inf, 1);
  let bot = '';
  if (!E.heaterOn && E.stopNote) bot = tf('T_D_NOTE');
  else if (E.thActive) bot = trf('T_D_UNTIL', E.thTarget + '°');
  else if (prefs.dep) { const d = new Date(prefs.dep * 60000); bot = trf('T_D_DEP', pad(d.getHours()) + ':' + pad(d.getMinutes())); }
  else { const n = nextSched(); if (n) bot = trf(n.dep ? 'T_D_DEP' : 'T_D_NEXT', n.t); }
  dText(0, 56, plain(bot), 1);
}
// ---------- Pantalla SSD1327 de 128×128 en 16 grises (como oledG*() y gDraw() del firmware) ----------
const GF = {"F_BIG":{"g":[[37,40,30,0,-30,40,0],[44,15,13,0,-8,15,600],[45,17,14,0,-14,17,698],[46,15,8,0,-8,15,817],[48,28,30,0,-30,28,877],[49,28,29,0,-29,28,1297],[50,28,30,0,-30,28,1703],[51,28,30,0,-30,28,2123],[52,28,29,0,-29,28,2543],[53,28,29,0,-29,28,2949],[54,28,30,0,-30,28,3355],[55,28,29,0,-29,28,3775],[56,28,30,0,-30,28,4181],[57,28,30,0,-30,28,4601],[176,20,30,0,-30,20,5021]],"b":[0,0,4,173,239,218,80,0,0,0,0,0,0,95,255,225,0,0,0,0,0,1,191,255,255,255,253,32,0,0,0,0,1,223,255,96,0,0,0,0,0,29,255,255,255,255,255,226,0,0,0,0,8,255,252,0,0,0,0,0,0,159,255,249,17,143,255,252,0,0,0,0,47,255,243,0,0,0,0,0,2,255,255,192,0,10,255,255,64,0,0,0,191,255,144,0,0,0,0,0,6,255,255,112,0,4,255,255,128,0,0,5,255,254,16,0,0,0,0,0,9,255,255,64,0,2,255,255,176,0,0,30,255,246,0,0,0,0,0,0,10,255,255,48,0,0,255,255,208,0,0,159,255,176,0,0,0,0,0,0,10,255,255,48,0,0,255,255,208,0,3,255,255,48,0,0,0,0,0,0,9,255,255,64,0,2,255,255,176,0,12,255,248,0,0,0,0,0,0,0,6,255,255,112,0,4,255,255,128,0,111,255,209,0,0,0,0,0,0,0,2,255,255,192,0,10,255,255,64,1,239,255,80,0,0,0,0,0,0,0,0,159,255,249,17,127,255,252,0,9,255,251,0,0,0,0,0,0,0,0,0,29,255,255,255,255,255,226,0,63,255,242,0,0,0,0,0,0,0,0,0,1,191,255,255,255,253,32,0,207,255,128,0,5,173,238,218,80,0,0,0,0,4,173,239,218,96,0,6,255,253,0,2,207,255,255,255,252,32,0,0,0,0,0,0,0,0,0,30,255,244,0,46,255,255,255,255,255,226,0,0,0,0,0,0,0,0,0,175,255,160,0,191,255,249,17,159,255,251,0,0,0,0,0,0,0,0,4,255,254,32,3,255,255,176,0,12,255,255,48,0,0,0,0,0,0,0,13,255,247,0,8,255,255,96,0,6,255,255,128,0,0,0,0,0,0,0,127,255,208,0,11,255,255,48,0,3,255,255,160,0,0,0,0,0,0,2,239,255,64,0,12,255,255,32,0,2,255,255,192,0,0,0,0,0,0,10,255,250,0,0,12,255,255,32,0,2,255,255,192,0,0,0,0,0,0,79,255,225,0,0,11,255,255,48,0,3,255,255,160,0,0,0,0,0,0,223,255,96,0,0,8,255,255,96,0,6,255,255,112,0,0,0,0,0,8,255,252,0,0,0,3,255,255,176,0,12,255,255,48,0,0,0,0,0,47,255,243,0,0,0,0,191,255,248,17,159,255,250,0,0,0,0,0,0,191,255,144,0,0,0,0,30,255,255,255,255,255,209,0,0,0,0,0,5,255,254,16,0,0,0,0,2,207,255,255,255,252,32,0,0,0,0,0,29,255,246,0,0,0,0,0,0,5,173,238,218,80,0,0,0,0,239,255,255,242,0,0,0,14,255,255,255,32,0,0,0,239,255,255,242,0,0,0,14,255,255,255,32,0,0,0,239,255,255,242,0,0,0,14,255,255,255,16,0,0,2,255,255,255,160,0,0,0,111,255,255,225,0,0,0,10,255,255,245,0,0,0,0,239,255,250,0,0,0,0,63,255,254,16,0,0,0,7,255,255,80,0,0,0,0,191,255,160,0,0,0,0,0,207,255,255,255,255,255,112,0,12,255,255,255,255,255,247,0,0,207,255,255,255,255,255,112,0,12,255,255,255,255,255,247,0,0,207,255,255,255,255,255,112,0,12,255,255,255,255,255,247,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,239,255,255,242,0,0,0,14,255,255,255,32,0,0,0,239,255,255,242,0,0,0,14,255,255,255,32,0,0,0,239,255,255,242,0,0,0,14,255,255,255,32,0,0,0,239,255,255,242,0,0,0,14,255,255,255,32,0,0,0,0,0,3,140,222,237,183,48,0,0,0,0,0,0,0,4,207,255,255,255,255,251,32,0,0,0,0,0,0,143,255,255,255,255,255,255,246,0,0,0,0,0,8,255,255,255,255,255,255,255,255,96,0,0,0,0,95,255,255,255,255,255,255,255,255,243,0,0,0,1,239,255,255,252,65,21,223,255,255,252,0,0,0,7,255,255,255,209,0,0,46,255,255,255,80,0,0,13,255,255,255,96,0,0,8,255,255,255,176,0,0,79,255,255,255,16,0,0,3,255,255,255,241,0,0,127,255,255,253,0,0,0,0,255,255,255,245,0,0,175,255,255,250,0,0,0,0,223,255,255,248,0,0,223,255,255,249,0,0,0,0,191,255,255,250,0,0,239,255,255,248,0,0,0,0,175,255,255,252,0,0,255,255,255,247,0,0,0,0,175,255,255,253,0,1,255,255,255,247,0,0,0,0,159,255,255,253,0,1,255,255,255,247,0,0,0,0,159,255,255,253,0,0,255,255,255,247,0,0,0,0,175,255,255,253,0,0,239,255,255,248,0,0,0,0,175,255,255,252,0,0,223,255,255,249,0,0,0,0,191,255,255,250,0,0,175,255,255,250,0,0,0,0,223,255,255,248,0,0,127,255,255,253,0,0,0,0,255,255,255,245,0,0,79,255,255,255,16,0,0,3,255,255,255,241,0,0,13,255,255,255,96,0,0,9,255,255,255,176,0,0,7,255,255,255,209,0,0,47,255,255,255,80,0,0,1,239,255,255,252,65,21,239,255,255,252,0,0,0,0,95,255,255,255,255,255,255,255,255,243,0,0,0,0,8,255,255,255,255,255,255,255,255,96,0,0,0,0,0,143,255,255,255,255,255,255,246,0,0,0,0,0,0,4,207,255,255,255,255,251,32,0,0,0,0,0,0,0,3,140,222,237,183,48,0,0,0,0,0,0,2,71,155,223,255,255,255,112,0,0,0,0,0,0,127,255,255,255,255,255,255,112,0,0,0,0,0,0,127,255,255,255,255,255,255,112,0,0,0,0,0,0,127,255,255,255,255,255,255,112,0,0,0,0,0,0,127,255,255,255,255,255,255,112,0,0,0,0,0,0,125,184,100,42,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,0,0,0,10,255,255,255,112,0,0,0,0,0,0,95,255,255,255,255,255,255,255,255,255,241,0,0,0,95,255,255,255,255,255,255,255,255,255,241,0,0,0,95,255,255,255,255,255,255,255,255,255,241,0,0,0,95,255,255,255,255,255,255,255,255,255,241,0,0,0,95,255,255,255,255,255,255,255,255,255,241,0,0,0,0,20,138,206,255,237,167,32,0,0,0,0,0,1,108,255,255,255,255,255,255,251,48,0,0,0,0,11,255,255,255,255,255,255,255,255,247,0,0,0,0,11,255,255,255,255,255,255,255,255,255,96,0,0,0,11,255,255,255,255,255,255,255,255,255,242,0,0,0,11,255,253,132,32,20,191,255,255,255,249,0,0,0,11,251,64,0,0,0,8,255,255,255,253,0,0,0,9,80,0,0,0,0,0,223,255,255,255,16,0,0,0,0,0,0,0,0,0,143,255,255,255,32,0,0,0,0,0,0,0,0,0,111,255,255,255,16,0,0,0,0,0,0,0,0,0,143,255,255,254,0,0,0,0,0,0,0,0,0,0,207,255,255,250,0,0,0,0,0,0,0,0,0,5,255,255,255,243,0,0,0,0,0,0,0,0,0,62,255,255,255,144,0,0,0,0,0,0,0,0,3,239,255,255,252,16,0,0,0,0,0,0,0,0,78,255,255,255,210,0,0,0,0,0,0,0,0,5,255,255,255,253,32,0,0,0,0,0,0,0,0,127,255,255,255,193,0,0,0,0,0,0,0,0,9,255,255,255,251,16,0,0,0,0,0,0,0,1,175,255,255,255,160,0,0,0,0,0,0,0,0,28,255,255,255,248,0,0,0,0,0,0,0,0,2,223,255,255,255,96,0,0,0,0,0,0,0,0,62,255,255,255,228,0,0,0,0,0,0,0,0,5,239,255,255,254,48,0,0,0,0,0,0,0,0,12,255,255,255,255,255,255,255,255,255,255,80,0,0,12,255,255,255,255,255,255,255,255,255,255,80,0,0,12,255,255,255,255,255,255,255,255,255,255,80,0,0,12,255,255,255,255,255,255,255,255,255,255,80,0,0,12,255,255,255,255,255,255,255,255,255,255,80,0,0,12,255,255,255,255,255,255,255,255,255,255,80,0,0,0,20,121,188,222,254,237,184,64,0,0,0,0,0,1,255,255,255,255,255,255,255,254,113,0,0,0,0,1,255,255,255,255,255,255,255,255,252,32,0,0,0,1,255,255,255,255,255,255,255,255,255,192,0,0,0,1,255,255,255,255,255,255,255,255,255,246,0,0,0,1,255,216,82,16,20,159,255,255,255,251,0,0,0,1,180,0,0,0,0,4,255,255,255,253,0,0,0,0,0,0,0,0,0,0,191,255,255,254,0,0,0,0,0,0,0,0,0,0,159,255,255,253,0,0,0,0,0,0,0,0,0,0,191,255,255,250,0,0,0,0,0,0,0,0,0,4,255,255,255,244,0,0,0,0,0,0,0,0,36,159,255,255,255,144,0,0,0,0,0,5,255,255,255,255,255,255,248,0,0,0,0,0,0,5,255,255,255,255,255,232,32,0,0,0,0,0,0,5,255,255,255,255,255,254,129,0,0,0,0,0,0,5,255,255,255,255,255,255,254,64,0,0,0,0,0,5,255,255,255,255,255,255,255,227,0,0,0,0,0,0,0,1,36,142,255,255,255,252,0,0,0,0,0,0,0,0,0,2,207,255,255,255,48,0,0,0,0,0,0,0,0,0,63,255,255,255,112,0,0,0,0,0,0,0,0,0,14,255,255,255,144,0,0,0,0,0,0,0,0,0,14,255,255,255,144,0,0,0,0,0,0,0,0,0,63,255,255,255,128,0,0,90,64,0,0,0,0,2,207,255,255,255,80,0,0,95,254,150,49,0,20,142,255,255,255,254,16,0,0,95,255,255,255,255,255,255,255,255,255,247,0,0,0,95,255,255,255,255,255,255,255,255,255,176,0,0,0,95,255,255,255,255,255,255,255,255,249,0,0,0,0,95,255,255,255,255,255,255,255,251,48,0,0,0,0,2,87,172,205,239,238,219,133,16,0,0,0,0,0,0,0,0,0,0,12,255,255,255,253,0,0,0,0,0,0,0,0,0,143,255,255,255,253,0,0,0,0,0,0,0,0,4,255,255,255,255,253,0,0,0,0,0,0,0,0,29,255,255,255,255,253,0,0,0,0,0,0,0,0,175,255,255,255,255,253,0,0,0,0,0,0,0,5,255,255,255,255,255,253,0,0,0,0,0,0,0,46,255,255,175,255,255,253,0,0,0,0,0,0,0,191,255,251,79,255,255,253,0,0,0,0,0,0,7,255,255,226,79,255,255,253,0,0,0,0,0,0,47,255,255,80,79,255,255,253,0,0,0,0,0,0,207,255,250,0,79,255,255,253,0,0,0,0,0,8,255,255,225,0,79,255,255,253,0,0,0,0,0,63,255,255,64,0,79,255,255,253,0,0,0,0,1,223,255,249,0,0,79,255,255,253,0,0,0,0,9,255,255,209,0,0,79,255,255,253,0,0,0,0,95,255,255,48,0,0,79,255,255,253,0,0,0,1,239,255,248,0,0,0,79,255,255,253,0,0,0,3,255,255,192,0,0,0,79,255,255,253,0,0,0,3,255,255,255,255,255,255,255,255,255,255,255,255,0,3,255,255,255,255,255,255,255,255,255,255,255,255,0,3,255,255,255,255,255,255,255,255,255,255,255,255,0,3,255,255,255,255,255,255,255,255,255,255,255,255,0,3,255,255,255,255,255,255,255,255,255,255,255,255,0,0,0,0,0,0,0,0,79,255,255,253,0,0,0,0,0,0,0,0,0,0,79,255,255,253,0,0,0,0,0,0,0,0,0,0,79,255,255,253,0,0,0,0,0,0,0,0,0,0,79,255,255,253,0,0,0,0,0,0,0,0,0,0,79,255,255,253,0,0,0,0,0,0,0,0,0,0,79,255,255,253,0,0,0,0,0,207,255,255,255,255,255,255,255,255,224,0,0,0,0,207,255,255,255,255,255,255,255,255,224,0,0,0,0,207,255,255,255,255,255,255,255,255,224,0,0,0,0,207,255,255,255,255,255,255,255,255,224,0,0,0,0,207,255,255,255,255,255,255,255,255,224,0,0,0,0,207,255,255,255,255,255,255,255,255,224,0,0,0,0,207,255,255,64,0,0,0,0,0,0,0,0,0,0,207,255,255,64,0,0,0,0,0,0,0,0,0,0,207,255,255,64,0,0,0,0,0,0,0,0,0,0,207,255,255,188,239,237,200,80,0,0,0,0,0,0,207,255,255,255,255,255,255,253,112,0,0,0,0,0,207,255,255,255,255,255,255,255,251,16,0,0,0,0,207,255,255,255,255,255,255,255,255,193,0,0,0,0,207,255,255,255,255,255,255,255,255,249,0,0,0,0,207,251,115,16,19,142,255,255,255,255,32,0,0,0,167,16,0,0,0,2,223,255,255,255,112,0,0,0,0,0,0,0,0,0,47,255,255,255,192,0,0,0,0,0,0,0,0,0,11,255,255,255,224,0,0,0,0,0,0,0,0,0,8,255,255,255,240,0,0,0,0,0,0,0,0,0,8,255,255,255,240,0,0,0,0,0,0,0,0,0,11,255,255,255,224,0,0,10,48,0,0,0,0,0,47,255,255,255,176,0,0,14,250,48,0,0,0,2,223,255,255,255,112,0,0,14,255,252,132,32,19,142,255,255,255,254,16,0,0,14,255,255,255,255,255,255,255,255,255,247,0,0,0,14,255,255,255,255,255,255,255,255,255,144,0,0,0,14,255,255,255,255,255,255,255,255,248,0,0,0,0,2,141,255,255,255,255,255,255,251,48,0,0,0,0,0,0,38,155,222,254,236,166,32,0,0,0,0,0,0,0,0,0,21,156,239,254,201,81,0,0,0,0,0,0,0,24,239,255,255,255,255,254,146,0,0,0,0,0,3,223,255,255,255,255,255,255,252,0,0,0,0,0,79,255,255,255,255,255,255,255,252,0,0,0,0,3,255,255,255,255,255,255,255,255,252,0,0,0,0,12,255,255,255,251,82,16,37,158,252,0,0,0,0,143,255,255,254,64,0,0,0,0,105,0,0,0,1,239,255,255,244,0,0,0,0,0,0,0,0,0,6,255,255,255,144,0,0,0,0,0,0,0,0,0,11,255,255,255,48,0,0,0,0,0,0,0,0,0,14,255,255,253,2,123,239,237,166,16,0,0,0,0,63,255,255,252,159,255,255,255,255,231,0,0,0,0,95,255,255,255,255,255,255,255,255,255,160,0,0,0,111,255,255,255,255,255,255,255,255,255,250,0,0,0,127,255,255,255,255,255,255,255,255,255,255,96,0,0,143,255,255,255,255,114,2,127,255,255,255,208,0,0,127,255,255,255,246,0,0,6,255,255,255,243,0,0,111,255,255,255,208,0,0,0,239,255,255,247,0,0,95,255,255,255,160,0,0,0,175,255,255,249,0,0,47,255,255,255,128,0,0,0,159,255,255,250,0,0,14,255,255,255,128,0,0,0,159,255,255,249,0,0,11,255,255,255,160,0,0,0,175,255,255,248,0,0,6,255,255,255,208,0,0,0,239,255,255,244,0,0,1,239,255,255,246,0,0,6,255,255,255,241,0,0,0,127,255,255,255,114,2,127,255,255,255,128,0,0,0,12,255,255,255,255,255,255,255,255,254,16,0,0,0,3,239,255,255,255,255,255,255,255,243,0,0,0,0,0,45,255,255,255,255,255,255,254,64,0,0,0,0,0,1,143,255,255,255,255,255,145,0,0,0,0,0,0,0,1,106,222,254,218,98,0,0,0,0,0,95,255,255,255,255,255,255,255,255,255,255,160,0,0,95,255,255,255,255,255,255,255,255,255,255,160,0,0,95,255,255,255,255,255,255,255,255,255,255,160,0,0,95,255,255,255,255,255,255,255,255,255,255,160,0,0,95,255,255,255,255,255,255,255,255,255,255,128,0,0,95,255,255,255,255,255,255,255,255,255,255,16,0,0,0,0,0,0,0,0,0,143,255,255,249,0,0,0,0,0,0,0,0,0,1,239,255,255,242,0,0,0,0,0,0,0,0,0,7,255,255,255,160,0,0,0,0,0,0,0,0,0,13,255,255,255,48,0,0,0,0,0,0,0,0,0,111,255,255,252,0,0,0,0,0,0,0,0,0,0,223,255,255,245,0,0,0,0,0,0,0,0,0,5,255,255,255,208,0,0,0,0,0,0,0,0,0,12,255,255,255,96,0,0,0,0,0,0,0,0,0,79,255,255,254,0,0,0,0,0,0,0,0,0,0,191,255,255,247,0,0,0,0,0,0,0,0,0,3,255,255,255,225,0,0,0,0,0,0,0,0,0,10,255,255,255,128,0,0,0,0,0,0,0,0,0,47,255,255,255,32,0,0,0,0,0,0,0,0,0,159,255,255,250,0,0,0,0,0,0,0,0,0,1,255,255,255,243,0,0,0,0,0,0,0,0,0,8,255,255,255,176,0,0,0,0,0,0,0,0,0,30,255,255,255,64,0,0,0,0,0,0,0,0,0,127,255,255,252,0,0,0,0,0,0,0,0,0,0,239,255,255,245,0,0,0,0,0,0,0,0,0,6,255,255,255,208,0,0,0,0,0,0,0,0,0,13,255,255,255,96,0,0,0,0,0,0,0,0,0,95,255,255,254,16,0,0,0,0,0,0,0,0,0,207,255,255,248,0,0,0,0,0,0,0,0,0,0,0,72,189,239,254,218,131,0,0,0,0,0,0,0,109,255,255,255,255,255,255,213,0,0,0,0,0,27,255,255,255,255,255,255,255,255,144,0,0,0,0,175,255,255,255,255,255,255,255,255,247,0,0,0,4,255,255,255,255,255,255,255,255,255,255,16,0,0,9,255,255,255,251,65,20,207,255,255,255,96,0,0,12,255,255,255,192,0,0,30,255,255,255,144,0,0,13,255,255,255,80,0,0,8,255,255,255,176,0,0,13,255,255,255,64,0,0,7,255,255,255,160,0,0,10,255,255,255,80,0,0,9,255,255,255,128,0,0,5,255,255,255,192,0,0,30,255,255,255,48,0,0,0,207,255,255,251,49,20,207,255,255,249,0,0,0,0,45,255,255,255,255,255,255,255,255,176,0,0,0,0,1,142,255,255,255,255,255,255,230,0,0,0,0,0,0,6,239,255,255,255,255,253,64,0,0,0,0,0,5,223,255,255,255,255,255,255,252,48,0,0,0,0,159,255,255,255,255,255,255,255,255,246,0,0,0,6,255,255,255,248,32,19,159,255,255,255,48,0,0,30,255,255,255,80,0,0,8,255,255,255,176,0,0,95,255,255,252,0,0,0,0,239,255,255,242,0,0,127,255,255,249,0,0,0,0,207,255,255,244,0,0,143,255,255,249,0,0,0,0,207,255,255,245,0,0,127,255,255,252,0,0,0,0,239,255,255,244,0,0,95,255,255,255,64,0,0,7,255,255,255,242,0,0,31,255,255,255,248,32,19,159,255,255,255,208,0,0,10,255,255,255,255,255,255,255,255,255,255,96,0,0,1,239,255,255,255,255,255,255,255,255,252,0,0,0,0,45,255,255,255,255,255,255,255,255,177,0,0,0,0,1,142,255,255,255,255,255,255,230,0,0,0,0,0,0,0,88,189,239,254,219,132,0,0,0,0,0,0,0,0,55,189,239,220,149,16,0,0,0,0,0,0,0,59,255,255,255,255,255,230,0,0,0,0,0,0,6,255,255,255,255,255,255,255,177,0,0,0,0,0,111,255,255,255,255,255,255,255,251,16,0,0,0,4,255,255,255,255,255,255,255,255,255,144,0,0,0,12,255,255,255,230,16,57,255,255,255,244,0,0,0,79,255,255,255,48,0,0,159,255,255,251,0,0,0,143,255,255,250,0,0,0,47,255,255,255,32,0,0,191,255,255,247,0,0,0,13,255,255,255,112,0,0,223,255,255,245,0,0,0,12,255,255,255,176,0,0,239,255,255,245,0,0,0,12,255,255,255,224,0,0,223,255,255,247,0,0,0,13,255,255,255,241,0,0,191,255,255,250,0,0,0,47,255,255,255,242,0,0,127,255,255,255,48,0,0,159,255,255,255,243,0,0,47,255,255,255,230,16,57,255,255,255,255,244,0,0,9,255,255,255,255,255,255,255,255,255,255,243,0,0,1,223,255,255,255,255,255,255,255,255,255,242,0,0,0,45,255,255,255,255,255,255,255,255,255,241,0,0,0,1,159,255,255,255,255,230,239,255,255,224,0,0,0,0,2,123,222,253,166,18,255,255,255,176,0,0,0,0,0,0,0,0,0,6,255,255,255,112,0,0,0,0,0,0,0,0,0,12,255,255,255,32,0,0,0,0,0,0,0,0,0,127,255,255,251,0,0,0,0,180,0,0,0,0,7,255,255,255,244,0,0,0,0,255,215,65,1,54,207,255,255,255,144,0,0,0,0,255,255,255,255,255,255,255,255,253,16,0,0,0,0,255,255,255,255,255,255,255,255,210,0,0,0,0,0,255,255,255,255,255,255,255,251,16,0,0,0,0,0,75,255,255,255,255,255,253,96,0,0,0,0,0,0,0,39,173,239,253,184,64,0,0,0,0,0,0,0,0,24,206,236,113,0,0,0,0,0,4,239,255,255,253,48,0,0,0,0,62,255,255,255,255,226,0,0,0,0,207,254,97,22,239,251,0,0,0,3,255,245,0,0,95,255,32,0,0,6,255,192,0,0,12,255,96,0,0,7,255,160,0,0,10,255,112,0,0,6,255,192,0,0,12,255,96,0,0,3,255,244,0,0,95,255,32,0,0,0,207,254,97,22,239,251,0,0,0,0,63,255,255,255,255,226,0,0,0,0,4,239,255,255,254,48,0,0,0,0,0,24,206,236,113,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]},"F_MED":{"g":[[32,5,0,0,0,5,0],[33,6,10,0,-10,6,0],[34,7,10,0,-10,7,30],[35,12,10,0,-10,12,65],[36,10,13,0,-11,10,125],[37,14,10,0,-10,14,190],[38,12,10,0,-10,12,260],[39,4,10,0,-10,4,320],[40,6,12,0,-11,6,340],[41,6,12,0,-11,6,376],[42,8,10,0,-10,7,412],[43,12,8,0,-8,12,452],[44,5,5,0,-3,5,500],[45,6,5,0,-5,6,513],[46,5,3,0,-3,5,528],[47,6,12,0,-10,5,536],[48,10,10,0,-10,10,572],[49,10,10,0,-10,10,622],[50,10,10,0,-10,10,672],[51,10,10,0,-10,10,722],[52,10,10,0,-10,10,772],[53,10,10,0,-10,10,822],[54,10,10,0,-10,10,872],[55,10,10,0,-10,10,922],[56,10,10,0,-10,10,972],[57,10,10,0,-10,10,1022],[58,6,8,0,-8,6,1072],[59,6,10,0,-8,6,1096],[60,12,8,0,-8,12,1126],[61,12,8,0,-8,12,1174],[62,12,8,0,-8,12,1222],[63,8,10,0,-10,8,1270],[64,14,12,0,-10,14,1310],[65,11,10,0,-10,11,1394],[66,11,10,0,-10,11,1449],[67,10,10,0,-10,10,1504],[68,12,10,0,-10,12,1554],[69,10,10,0,-10,10,1614],[70,10,10,0,-10,10,1664],[71,11,10,0,-10,11,1714],[72,12,10,0,-10,12,1769],[73,5,10,0,-10,5,1829],[74,6,13,-1,-10,5,1854],[75,12,10,0,-10,11,1893],[76,9,10,0,-10,9,1953],[77,14,10,0,-10,14,1998],[78,12,10,0,-10,12,2068],[79,12,10,0,-10,12,2128],[80,10,10,0,-10,10,2188],[81,12,12,0,-10,12,2238],[82,11,10,0,-10,11,2310],[83,10,10,0,-10,10,2365],[84,10,10,0,-10,10,2415],[85,11,10,0,-10,11,2465],[86,11,10,0,-10,11,2520],[87,15,10,0,-10,15,2575],[88,11,10,0,-10,11,2650],[89,12,10,-1,-10,10,2705],[90,10,10,0,-10,10,2765],[91,6,12,0,-11,6,2815],[92,6,12,0,-10,5,2851],[93,6,12,0,-11,6,2887],[94,12,10,0,-10,12,2923],[95,7,3,0,0,7,2983],[96,7,11,0,-11,7,2994],[97,9,8,0,-8,9,3033],[98,10,11,0,-11,10,3069],[99,8,8,0,-8,8,3124],[100,10,11,0,-11,10,3156],[101,10,8,0,-8,10,3211],[102,7,11,0,-11,6,3251],[103,10,11,0,-8,10,3290],[104,10,11,0,-11,10,3345],[105,5,11,0,-11,5,3400],[106,6,14,-1,-11,5,3428],[107,10,11,0,-11,9,3470],[108,5,11,0,-11,5,3525],[109,15,8,0,-8,15,3553],[110,10,8,0,-8,10,3613],[111,10,8,0,-8,10,3653],[112,10,11,0,-8,10,3693],[113,10,11,0,-8,10,3748],[114,7,8,0,-8,7,3803],[115,8,8,0,-8,8,3831],[116,7,10,0,-10,7,3863],[117,10,8,0,-8,10,3898],[118,9,8,0,-8,9,3938],[119,13,8,0,-8,13,3974],[120,9,8,0,-8,9,4026],[121,9,11,0,-8,9,4062],[122,8,8,0,-8,8,4112],[123,10,13,0,-11,10,4144],[124,5,14,0,-11,5,4209],[125,10,13,0,-11,10,4244],[126,12,7,0,-7,12,4309],[176,7,10,0,-10,7,4351],[183,5,6,0,-6,5,4386],[186,8,10,0,-10,8,4401],[193,11,14,0,-14,11,4441],[196,11,14,0,-14,11,4518],[201,10,14,0,-14,10,4595],[205,5,14,0,-14,5,4665],[209,12,14,0,-14,12,4700],[211,12,14,0,-14,12,4784],[214,12,14,0,-14,12,4868],[218,11,14,0,-14,11,4952],[220,11,14,0,-14,11,5029],[223,10,11,0,-11,10,5106],[224,9,11,0,-11,9,5161],[225,9,11,0,-11,9,5211],[228,9,11,0,-11,9,5261],[231,8,11,0,-8,8,5311],[232,10,11,0,-11,10,5355],[233,10,11,0,-11,10,5410],[237,6,11,0,-11,5,5465],[241,10,11,0,-11,10,5498],[242,10,11,0,-11,10,5553],[243,10,11,0,-11,10,5608],[246,10,11,0,-11,10,5663],[250,10,11,0,-11,10,5718],[252,10,11,0,-11,10,5773]],"b":[0,255,96,0,255,96,0,255,96,0,255,96,0,255,96,0,223,64,0,191,32,0,0,0,0,255,96,0,255,96,10,224,175,0,174,10,240,10,224,175,0,174,10,240,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,111,33,247,0,0,0,158,4,243,0,0,0,218,8,240,0,4,255,255,255,255,192,0,7,241,47,96,0,0,10,208,95,32,0,31,255,255,255,255,0,0,79,64,233,0,0,0,127,18,246,0,0,0,172,5,242,0,0,0,0,167,0,0,0,0,167,0,0,0,124,254,181,0,8,248,167,74,32,13,245,167,0,0,12,254,234,64,0,4,239,255,253,32,0,22,222,255,160,0,0,167,143,176,11,98,167,159,112,3,157,255,199,0,0,0,167,0,0,0,0,167,0,0,6,222,161,0,13,160,0,63,131,248,0,142,16,0,127,48,221,2,246,0,0,127,48,220,12,176,0,0,63,131,248,110,58,237,96,6,222,163,231,143,55,244,0,0,11,192,205,3,248,0,0,95,48,205,3,248,0,1,232,0,143,55,244,0,9,209,0,26,237,96,0,7,222,198,0,0,0,111,226,40,48,0,0,159,225,0,0,0,0,79,249,0,0,0,3,239,255,112,31,241,13,252,159,245,63,224,47,244,11,254,207,144,31,245,1,223,255,48,9,253,49,159,253,16,0,108,238,218,207,210,10,224,10,224,10,224,10,224,0,0,0,0,0,0,0,0,0,0,0,0,0,30,225,0,159,128,1,255,48,6,253,0,10,250,0,11,249,0,11,249,0,10,250,0,6,253,0,1,255,32,0,159,128,0,30,225,9,247,0,2,254,16,0,207,112,0,127,192,0,79,241,0,47,242,0,47,242,0,79,241,0,127,192,0,191,112,2,254,16,9,247,0,0,14,64,0,104,30,69,160,77,223,222,112,1,207,243,0,77,223,206,112,104,30,69,160,0,14,64,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,14,160,0,0,0,0,14,160,0,0,0,0,14,160,0,0,8,255,255,255,255,64,8,255,255,255,255,64,0,0,14,160,0,0,0,0,14,160,0,0,0,0,14,160,0,0,9,253,0,159,208,10,251,0,223,32,47,128,0,79,255,241,79,255,241,0,0,0,0,0,0,0,0,0,9,253,0,159,208,9,253,0,0,8,224,0,13,160,0,47,80,0,127,16,0,187,0,1,247,0,5,242,0,10,208,0,14,128,0,79,64,0,142,0,0,218,0,0,0,92,254,179,0,5,253,36,254,32,13,247,0,191,160,63,245,0,159,224,79,245,0,159,241,95,245,0,159,241,63,245,0,159,224,13,247,0,207,160,5,253,36,254,32,0,92,254,179,0,1,108,255,112,0,6,147,255,112,0,0,0,255,112,0,0,0,255,112,0,0,0,255,112,0,0,0,255,112,0,0,0,255,112,0,0,0,255,112,0,0,0,255,112,0,5,255,255,255,192,2,157,253,162,0,10,82,27,254,16,0,0,4,255,96,0,0,5,255,96,0,0,28,254,32,0,0,175,245,0,0,9,255,80,0,0,143,246,0,0,7,255,112,0,0,13,255,255,255,128,1,141,254,180,0,8,81,26,255,32,0,0,4,255,80,0,0,26,253,16,0,95,255,228,0,0,0,42,255,48,0,0,1,255,128,0,0,1,255,128,27,65,42,254,48,4,190,253,163,0,0,0,175,250,0,0,7,255,250,0,0,79,205,250,0,2,238,45,250,0,29,245,13,250,0,111,144,13,250,0,111,255,255,255,241,0,0,13,250,0,0,0,13,250,0,0,0,13,250,0,8,255,255,255,0,8,249,0,0,0,8,249,0,0,0,8,255,254,179,0,6,98,26,255,48,0,0,1,255,144,0,0,0,239,176,0,0,1,255,144,11,81,26,254,48,3,157,253,162,0,0,24,223,215,0,1,223,97,39,64,9,249,0,0,0,14,252,223,214,0,31,255,66,239,96,47,252,0,175,208,15,251,0,159,224,11,252,0,175,192,3,255,66,239,80,0,59,238,180,0,31,255,255,255,144,0,0,5,255,128,0,0,12,255,32,0,0,63,250,0,0,0,159,243,0,0,1,239,176,0,0,7,255,64,0,0,13,252,0,0,0,79,245,0,0,0,191,208,0,0,1,157,238,198,0,10,253,19,255,96,13,249,0,223,144,8,253,19,255,64,0,175,255,246,0,9,252,19,239,80,31,246,0,175,192,31,246,0,175,192,11,252,19,239,112,1,141,238,198,0,0,108,238,145,0,9,251,23,253,16,47,245,1,255,112,79,244,0,255,176,47,245,1,255,192,11,251,23,255,192,1,157,253,239,160,0,0,0,223,64,7,81,41,250,0,2,158,252,96,0,7,255,0,7,255,0,7,255,0,0,0,0,0,0,0,7,255,0,7,255,0,7,255,0,7,255,0,7,255,0,7,255,0,0,0,0,0,0,0,7,255,0,7,255,0,8,253,0,11,244,0,15,160,0,0,0,0,1,91,64,0,0,4,174,254,48,0,56,223,234,80,0,7,255,181,16,0,0,7,255,181,16,0,0,0,56,239,234,80,0,0,0,4,174,254,48,0,0,0,1,91,64,8,255,255,255,255,64,8,255,255,255,255,64,0,0,0,0,0,0,0,0,0,0,0,0,8,255,255,255,255,64,8,255,255,255,255,64,0,0,0,0,0,0,0,0,0,0,0,0,7,148,0,0,0,0,6,255,216,48,0,0,0,22,191,252,113,0,0,0,2,124,255,48,0,0,2,124,255,48,0,22,191,252,113,0,6,255,216,48,0,0,7,164,0,0,0,0,4,190,235,48,11,49,143,224,0,0,95,243,0,1,223,225,0,11,255,64,0,111,245,0,0,159,208,0,0,0,0,0,0,159,208,0,0,159,208,0,0,0,108,238,216,16,0,0,45,197,33,57,228,0,1,217,0,0,0,111,32,7,208,25,237,190,11,160,13,112,143,51,254,6,224,15,64,203,0,190,5,240,15,64,203,0,190,7,192,13,96,143,51,238,78,80,8,192,25,237,190,180,0,1,217,0,0,0,0,0,0,45,180,16,57,160,0,0,1,124,239,217,32,0,0,5,255,243,0,0,0,191,255,128,0,0,47,252,254,0,0,7,254,47,245,0,0,223,144,191,160,0,79,243,6,255,16,9,255,255,255,247,1,239,64,0,111,192,111,241,0,3,255,59,252,0,0,14,249,11,255,255,235,48,0,191,224,26,254,16,11,254,0,111,243,0,191,224,10,254,16,11,255,255,255,96,0,191,224,7,255,64,11,254,0,15,249,0,191,224,0,255,144,11,254,0,127,244,0,191,255,254,197,0,0,23,206,254,196,1,223,179,19,133,10,254,16,0,0,31,249,0,0,0,79,247,0,0,0,79,247,0,0,0,31,249,0,0,0,10,254,16,0,0,1,223,179,2,117,0,23,206,254,196,11,255,254,218,64,0,11,254,1,110,247,0,11,254,0,6,255,64,11,254,0,0,255,160,11,254,0,0,223,208,11,254,0,0,223,208,11,254,0,1,255,160,11,254,0,7,255,64,11,254,1,110,247,0,11,255,254,218,64,0,11,255,255,255,96,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,255,255,255,32,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,255,255,255,128,11,255,255,255,96,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,255,255,255,32,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,0,7,206,254,219,16,28,252,48,37,177,10,254,16,0,0,1,255,144,0,0,0,79,247,0,175,255,116,255,112,0,15,247,31,249,0,0,255,112,175,225,0,15,247,1,223,179,3,255,112,1,124,239,237,164,11,254,0,3,255,112,11,254,0,3,255,112,11,254,0,3,255,112,11,254,0,3,255,112,11,255,255,255,255,112,11,254,0,3,255,112,11,254,0,3,255,112,11,254,0,3,255,112,11,254,0,3,255,112,11,254,0,3,255,112,11,254,0,191,224,11,254,0,191,224,11,254,0,191,224,11,254,0,191,224,11,254,0,191,224,0,191,224,0,191,224,0,191,224,0,191,224,0,191,224,0,191,224,0,191,224,0,191,224,0,191,224,0,191,224,0,207,208,4,255,128,206,199,0,11,254,0,27,254,64,11,254,1,207,228,0,11,254,28,254,48,0,11,254,207,227,0,0,11,255,255,48,0,0,11,255,255,144,0,0,11,254,159,250,0,0,11,254,9,255,160,0,11,254,0,159,250,0,11,254,0,8,255,176,11,254,0,0,0,191,224,0,0,11,254,0,0,0,191,224,0,0,11,254,0,0,0,191,224,0,0,11,254,0,0,0,191,224,0,0,11,254,0,0,0,191,255,255,248,11,255,208,0,14,255,160,11,255,244,0,111,255,160,11,255,251,0,207,255,160,11,252,223,52,252,223,160,11,252,111,154,245,223,160,11,252,30,255,208,223,160,11,252,8,255,112,223,160,11,252,2,254,16,223,160,11,252,0,0,0,223,160,11,252,0,0,0,223,160,11,255,112,1,255,112,11,255,225,1,255,112,11,255,249,1,255,112,11,253,239,33,255,112,11,252,127,161,255,112,11,252,30,244,255,112,11,252,6,252,255,112,11,252,0,223,255,112,11,252,0,95,255,112,11,252,0,12,255,112,0,24,222,236,129,0,2,223,145,26,253,16,11,253,0,1,239,160,47,249,0,0,175,241,79,247,0,0,159,242,79,247,0,0,159,242,47,249,0,0,175,241,11,253,0,1,239,160,2,223,145,26,253,16,0,24,223,252,129,0,11,255,255,218,48,11,254,1,175,226,11,254,0,47,248,11,254,0,31,250,11,254,0,47,248,11,254,1,175,226,11,255,255,218,48,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,0,24,222,236,113,0,2,223,145,26,253,16,11,253,0,1,239,144,47,249,0,0,175,224,79,247,0,0,159,242,79,247,0,0,159,242,47,249,0,0,175,241,11,253,0,1,239,160,2,239,145,26,253,32,0,24,223,255,178,0,0,0,0,111,210,0,0,0,0,8,253,16,11,255,255,234,32,0,191,224,28,253,0,11,254,0,127,242,0,191,224,7,255,32,11,254,1,207,160,0,191,255,255,177,0,11,254,4,239,160,0,191,224,7,255,80,11,254,0,30,252,0,191,224,0,143,244,0,124,239,237,64,8,251,33,57,80,13,247,0,0,0,14,254,115,0,0,10,255,255,233,16,1,175,255,255,176,0,1,90,255,240,0,0,0,159,224,11,115,18,207,144,9,206,255,216,0,239,255,255,255,247,0,8,255,16,0,0,8,255,16,0,0,8,255,16,0,0,8,255,16,0,0,8,255,16,0,0,8,255,16,0,0,8,255,16,0,0,8,255,16,0,0,8,255,16,0,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,159,240,0,175,224,3,255,129,62,248,0,3,174,254,198,0,191,208,0,1,255,150,255,64,0,111,243,30,250,0,12,252,0,159,225,2,255,112,4,255,96,143,241,0,13,251,13,250,0,0,127,245,255,80,0,2,255,239,224,0,0,11,255,248,0,0,0,95,255,48,0,127,241,0,175,242,0,159,211,255,80,14,255,80,13,250,14,248,2,254,249,2,255,96,191,192,111,127,192,95,242,7,255,26,243,207,25,254,0,79,244,222,8,245,223,160,0,255,159,176,79,159,247,0,12,255,247,1,255,255,48,0,143,255,64,12,255,224,0,4,255,240,0,143,251,0,79,248,0,11,254,16,143,243,6,255,80,0,207,211,239,160,0,3,255,255,209,0,0,7,255,244,0,0,0,159,255,96,0,0,95,253,254,32,0,30,251,29,252,0,10,254,32,79,247,6,255,80,0,159,243,12,254,32,1,223,209,2,255,176,9,255,64,0,127,245,79,249,0,0,12,254,223,209,0,0,2,239,255,64,0,0,0,127,249,0,0,0,0,79,246,0,0,0,0,79,246,0,0,0,0,79,246,0,0,0,0,79,246,0,0,63,255,255,255,245,0,0,4,255,245,0,0,46,255,192,0,0,207,254,32,0,8,255,244,0,0,79,255,128,0,2,239,251,0,0,12,255,226,0,0,95,255,64,0,0,111,255,255,255,248,12,255,247,12,248,0,12,248,0,12,248,0,12,248,0,12,248,0,12,248,0,12,248,0,12,248,0,12,248,0,12,248,0,12,255,247,218,0,0,142,0,0,79,64,0,14,128,0,10,208,0,5,242,0,1,247,0,0,187,0,0,127,16,0,47,80,0,13,160,0,8,224,31,255,243,0,47,243,0,47,243,0,47,243,0,47,243,0,47,243,0,47,243,0,47,243,0,47,243,0,47,243,0,47,243,31,255,243,0,0,111,227,0,0,0,5,255,254,32,0,0,79,210,94,209,0,3,234,16,2,220,16,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,255,255,255,240,29,209,0,0,46,128,0,0,79,48,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,11,255,254,162,0,0,1,143,208,0,0,2,255,48,76,239,255,245,47,247,2,255,85,255,16,79,245,63,246,28,255,80,109,252,127,245,12,249,0,0,0,12,249,0,0,0,12,249,0,0,0,12,250,157,234,16,12,255,81,207,160,12,252,0,95,242,12,250,0,63,245,12,250,0,63,245,12,252,0,95,242,12,255,81,207,160,12,250,157,234,16,0,108,237,129,9,254,49,85,47,246,0,0,95,243,0,0,95,243,0,0,47,246,0,0,9,253,49,101,0,108,253,129,0,0,0,159,208,0,0,0,159,208,0,0,0,159,208,1,174,217,175,208,10,252,21,255,208,47,245,0,207,208,95,243,0,175,208,95,243,0,175,208,47,245,0,207,208,10,252,21,255,208,1,174,217,175,208,0,124,254,163,0,9,251,20,255,48,47,244,0,207,160,95,255,255,255,192,95,243,0,0,0,47,246,0,0,0,9,253,49,57,96,0,124,254,198,16,0,158,255,48,111,242,0,8,254,0,11,255,255,240,8,254,0,0,143,224,0,8,254,0,0,143,224,0,8,254,0,0,143,224,0,8,254,0,0,1,174,217,175,208,10,252,21,255,208,47,245,0,207,208,95,243,0,175,208,95,243,0,175,208,47,245,0,207,208,10,252,21,255,208,1,174,217,175,192,0,0,0,207,160,6,114,22,255,48,1,141,254,163,0,12,249,0,0,0,12,249,0,0,0,12,249,0,0,0,12,250,157,235,32,12,255,81,223,160,12,252,0,159,208,12,250,0,159,208,12,249,0,159,208,12,249,0,159,208,12,249,0,159,208,12,249,0,159,208,12,249,0,207,144,0,0,0,207,144,12,249,0,207,144,12,249,0,207,144,12,249,0,207,144,12,249,0,0,207,144,0,207,144,0,0,0,0,207,144,0,207,144,0,207,144,0,207,144,0,207,144,0,207,144,0,207,144,0,207,144,0,223,144,2,255,80,127,216,0,12,249,0,0,0,12,249,0,0,0,12,249,0,0,0,12,249,2,239,177,12,249,45,251,0,12,251,223,176,0,12,255,252,0,0,12,254,255,80,0,12,249,159,244,0,12,249,11,254,48,12,249,1,207,227,12,249,0,207,144,12,249,0,207,144,12,249,0,207,144,12,249,0,207,144,12,249,0,207,144,12,249,0,13,250,158,233,59,253,112,0,223,244,63,254,52,255,64,13,251,0,239,160,15,247,0,223,160,14,248,0,255,112,13,249,0,239,128,15,247,0,223,144,14,248,0,255,112,13,249,0,239,128,15,247,0,223,144,14,248,0,255,112,12,250,157,235,32,12,255,81,223,160,12,252,0,159,208,12,250,0,159,208,12,249,0,159,208,12,249,0,159,208,12,249,0,159,208,12,249,0,159,208,0,125,254,180,0,9,252,36,255,64,47,245,0,191,192,95,243,0,159,240,95,243,0,159,240,47,245,0,191,192,9,252,20,255,64,0,125,254,180,0,12,250,157,234,16,12,255,81,207,160,12,252,0,95,242,12,250,0,63,245,12,250,0,63,245,12,252,0,95,242,12,255,81,207,160,12,250,157,234,16,12,249,0,0,0,12,249,0,0,0,12,249,0,0,0,1,174,217,175,208,10,252,21,255,208,47,245,0,207,208,95,243,0,175,208,95,243,0,175,208,47,245,0,207,208,10,252,21,255,208,1,174,217,175,208,0,0,0,159,208,0,0,0,159,208,0,0,0,159,208,12,250,157,208,207,246,0,12,252,0,0,207,160,0,12,249,0,0,207,144,0,12,249,0,0,207,144,0,4,206,235,80,31,242,20,162,79,245,16,0,30,255,253,128,4,190,255,247,0,0,45,249,42,65,29,246,5,190,237,112,9,253,0,0,159,208,0,207,255,255,96,159,208,0,9,253,0,0,159,208,0,9,253,0,0,159,208,0,7,254,16,0,27,239,242,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,13,249,0,207,192,10,253,21,255,192,2,190,217,191,192,159,192,0,191,179,255,48,31,245,13,248,6,254,16,127,208,191,144,2,255,79,243,0,11,254,253,0,0,95,255,112,0,0,239,242,0,111,240,10,249,1,255,82,255,48,239,208,79,241,13,247,47,239,24,252,0,159,166,248,245,191,144,6,254,159,31,159,245,0,47,254,192,206,255,16,0,223,248,9,255,192,0,9,255,80,95,248,0,95,244,4,255,80,159,209,223,144,1,223,239,209,0,3,255,243,0,0,95,255,80,0,30,252,254,32,11,252,12,251,6,255,32,47,247,159,192,0,191,163,255,48,31,245,12,249,6,254,16,111,224,191,160,1,239,111,245,0,9,254,254,0,0,63,255,144,0,0,207,244,0,0,8,254,0,0,1,207,112,0,9,254,128,0,0,63,255,255,247,0,0,191,247,0,8,255,226,0,111,255,64,3,255,247,0,29,255,160,0,111,252,16,0,111,255,255,247,0,0,93,255,48,0,0,255,128,0,0,2,255,48,0,0,2,255,48,0,0,3,255,48,0,0,25,254,16,0,4,255,246,0,0,0,25,254,16,0,0,3,255,48,0,0,2,255,48,0,0,2,255,48,0,0,0,255,128,0,0,0,93,255,48,3,245,0,63,80,3,245,0,63,80,3,245,0,63,80,3,245,0,63,80,3,245,0,63,80,3,245,0,63,80,3,245,0,63,80,4,255,213,0,0,0,8,254,0,0,0,4,255,16,0,0,4,255,16,0,0,3,255,32,0,0,1,239,129,0,0,0,111,255,48,0,1,255,129,0,0,3,255,32,0,0,4,255,16,0,0,4,255,16,0,0,8,254,0,0,4,255,213,0,0,0,0,0,0,0,0,1,142,234,65,41,64,8,255,255,255,255,64,7,97,22,206,198,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,174,161,0,138,26,128,11,80,91,0,138,26,128,1,190,161,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,9,253,0,159,208,9,253,0,0,0,0,0,0,0,0,2,174,233,16,12,243,79,160,47,192,14,240,47,192,14,240,12,243,79,160,2,174,234,16,0,0,0,0,13,255,255,192,0,0,0,0,0,0,0,0,0,0,11,226,0,0,0,6,226,0,0,0,0,0,0,0,0,0,0,0,0,0,0,5,255,243,0,0,0,191,255,128,0,0,47,252,254,0,0,7,254,47,245,0,0,223,144,191,160,0,79,243,6,255,16,9,255,255,255,247,1,239,64,0,111,192,111,241,0,3,255,59,252,0,0,14,249,0,12,193,248,0,0,0,204,31,128,0,0,0,0,0,0,0,0,0,0,0,0,0,5,255,243,0,0,0,191,255,128,0,0,47,252,254,0,0,7,254,47,245,0,0,223,144,191,160,0,79,243,6,255,16,9,255,255,255,247,1,239,64,0,111,192,111,241,0,3,255,59,252,0,0,14,249,0,0,79,128,0,0,1,216,0,0,0,0,0,0,0,0,0,0,0,0,11,255,255,255,96,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,255,255,255,32,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,254,0,0,0,11,255,255,255,128,0,143,64,79,64,0,0,0,0,0,11,254,0,191,224,11,254,0,191,224,11,254,0,191,224,11,254,0,191,224,11,254,0,191,224,0,5,233,46,0,0,0,11,87,232,0,0,0,0,0,0,0,0,0,0,0,0,0,0,11,255,112,1,255,112,11,255,225,1,255,112,11,255,249,1,255,112,11,253,239,33,255,112,11,252,127,161,255,112,11,252,30,244,255,112,11,252,6,252,255,112,11,252,0,223,255,112,11,252,0,95,255,112,11,252,0,12,255,112,0,0,3,249,0,0,0,0,29,144,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,24,222,236,129,0,2,223,145,26,253,16,11,253,0,1,239,160,47,249,0,0,175,241,79,247,0,0,159,242,79,247,0,0,159,242,47,249,0,0,175,241,11,253,0,1,239,160,2,223,145,26,253,16,0,24,223,252,129,0,0,3,246,143,32,0,0,3,246,143,32,0,0,0,0,0,0,0,0,0,0,0,0,0,0,24,222,236,129,0,2,223,145,26,253,16,11,253,0,1,239,160,47,249,0,0,175,241,79,247,0,0,159,242,79,247,0,0,159,242,47,249,0,0,175,241,11,253,0,1,239,160,2,223,145,26,253,16,0,24,223,252,129,0,0,0,7,245,0,0,0,3,229,0,0,0,0,0,0,0,0,0,0,0,0,0,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,159,240,0,175,224,3,255,129,62,248,0,3,174,254,198,0,0,7,242,205,0,0,0,127,44,208,0,0,0,0,0,0,0,0,0,0,0,0,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,191,224,0,143,241,11,254,0,8,255,16,159,240,0,175,224,3,255,129,62,248,0,3,174,254,198,0,0,125,238,196,0,7,254,34,239,48,12,250,2,223,112,12,249,46,249,48,12,249,143,144,0,12,249,159,193,0,12,249,95,254,80,12,249,8,255,243,12,249,0,79,246,12,249,0,63,244,12,249,159,253,112,0,175,48,0,0,1,204,0,0,0,2,215,0,0,191,255,234,32,0,0,24,253,0,0,0,47,243,4,206,255,255,82,255,112,47,245,95,241,4,255,83,255,97,207,245,6,223,199,255,80,0,0,10,243,0,0,4,246,0,0,1,216,0,0,191,255,234,32,0,0,24,253,0,0,0,47,243,4,206,255,255,82,255,112,47,245,95,241,4,255,83,255,97,207,245,6,223,199,255,80,0,111,58,224,0,6,243,174,0,0,0,0,0,0,191,255,234,32,0,0,24,253,0,0,0,47,243,4,206,255,255,82,255,112,47,245,95,241,4,255,83,255,97,207,245,6,223,199,255,80,0,108,237,129,9,254,49,85,47,246,0,0,95,243,0,0,95,243,0,0,47,246,0,0,9,253,49,101,0,108,253,129,0,0,75,0,0,0,47,16,0,14,251,0,0,127,96,0,0,0,9,225,0,0,0,0,186,0,0,0,124,254,163,0,9,251,20,255,48,47,244,0,207,160,95,255,255,255,192,95,243,0,0,0,47,246,0,0,0,9,253,49,57,96,0,124,254,198,16,0,0,6,246,0,0,0,46,144,0,0,0,171,0,0,0,124,254,163,0,9,251,20,255,48,47,244,0,207,160,95,255,255,255,192,95,243,0,0,0,47,246,0,0,0,9,253,49,57,96,0,124,254,198,16,0,12,210,0,126,48,2,229,0,12,249,0,12,249,0,12,249,0,12,249,0,12,249,0,12,249,0,12,249,0,12,249,0,0,9,176,103,0,0,29,72,150,0,0,59,8,209,0,12,250,157,235,32,12,255,81,223,160,12,252,0,159,208,12,250,0,159,208,12,249,0,159,208,12,249,0,159,208,12,249,0,159,208,12,249,0,159,208,0,127,80,0,0,0,9,225,0,0,0,0,202,0,0,0,125,254,180,0,9,252,36,255,64,47,245,0,191,192,95,243,0,159,240,95,243,0,159,240,47,245,0,191,192,9,252,20,255,64,0,125,254,180,0,0,0,7,246,0,0,0,46,144,0,0,0,187,0,0,0,125,254,180,0,9,252,36,255,64,47,245,0,191,192,95,243,0,159,240,95,243,0,159,240,47,245,0,191,192,9,252,20,255,64,0,125,254,180,0,0,95,74,224,0,0,95,74,224,0,0,0,0,0,0,0,125,254,180,0,9,252,36,255,64,47,245,0,191,192,95,243,0,159,240,95,243,0,159,240,47,245,0,191,192,9,252,20,255,64,0,125,254,180,0,0,0,4,249,0,0,0,13,176,0,0,0,141,16,0,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,13,249,0,207,192,10,253,21,255,192,2,190,217,191,192,0,63,104,242,0,0,63,104,242,0,0,0,0,0,0,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,14,248,0,175,192,13,249,0,207,192,10,253,21,255,192,2,190,217,191,192]},"F_SMALL":{"g":[[32,4,0,0,0,4,0],[33,4,8,0,-8,4,0],[34,5,8,0,-8,5,16],[35,9,8,0,-8,9,36],[36,7,10,0,-8,7,72],[37,10,8,0,-8,10,107],[38,9,8,0,-8,9,147],[39,3,8,0,-8,3,183],[40,4,10,0,-9,4,195],[41,4,10,0,-9,4,215],[42,6,8,0,-8,6,235],[43,9,7,0,-7,9,259],[44,4,2,0,-1,4,291],[45,4,4,0,-4,4,295],[46,4,1,0,-1,4,303],[47,4,9,0,-8,4,305],[48,7,8,0,-8,7,323],[49,7,8,0,-8,7,351],[50,7,8,0,-8,7,379],[51,7,8,0,-8,7,407],[52,7,8,0,-8,7,435],[53,7,8,0,-8,7,463],[54,7,8,0,-8,7,491],[55,7,8,0,-8,7,519],[56,7,8,0,-8,7,547],[57,7,8,0,-8,7,575],[58,4,6,0,-6,4,603],[59,4,7,0,-6,4,615],[60,9,6,0,-6,9,629],[61,9,5,0,-5,9,656],[62,9,6,0,-6,9,679],[63,6,8,0,-8,6,706],[64,11,10,0,-8,11,730],[65,8,8,0,-8,8,785],[66,8,8,0,-8,8,817],[67,8,8,0,-8,8,849],[68,8,8,0,-8,8,881],[69,7,8,0,-8,7,913],[70,6,8,0,-8,6,941],[71,9,8,0,-8,9,965],[72,8,8,0,-8,8,1001],[73,3,8,0,-8,3,1033],[74,4,10,-1,-8,3,1045],[75,8,8,0,-8,7,1065],[76,7,8,0,-8,6,1097],[77,9,8,0,-8,9,1125],[78,8,8,0,-8,8,1161],[79,9,8,0,-8,9,1193],[80,7,8,0,-8,7,1229],[81,9,9,0,-8,9,1257],[82,8,8,0,-8,8,1298],[83,7,8,0,-8,7,1330],[84,8,8,-1,-8,7,1358],[85,8,8,0,-8,8,1390],[86,8,8,0,-8,8,1422],[87,11,8,0,-8,11,1454],[88,8,8,0,-8,8,1498],[89,8,8,-1,-8,7,1530],[90,8,8,0,-8,8,1562],[91,4,10,0,-8,4,1594],[92,4,9,0,-8,4,1614],[93,4,10,0,-8,4,1632],[94,9,8,0,-8,9,1652],[95,7,3,-1,0,6,1688],[96,6,9,0,-9,6,1699],[97,7,6,0,-6,7,1726],[98,7,9,0,-9,7,1747],[99,6,6,0,-6,6,1779],[100,7,9,0,-9,7,1797],[101,7,6,0,-6,7,1829],[102,5,9,0,-9,4,1850],[103,7,8,0,-6,7,1873],[104,7,9,0,-9,7,1901],[105,3,8,0,-8,3,1933],[106,4,10,-1,-8,3,1945],[107,7,9,0,-9,6,1965],[108,3,9,0,-9,3,1997],[109,11,6,0,-6,11,2011],[110,7,6,0,-6,7,2044],[111,7,6,0,-6,7,2065],[112,7,8,0,-6,7,2086],[113,7,8,0,-6,7,2114],[114,5,6,0,-6,5,2142],[115,6,6,0,-6,6,2157],[116,5,8,0,-8,4,2175],[117,7,6,0,-6,7,2195],[118,7,6,0,-6,7,2216],[119,9,6,0,-6,9,2237],[120,7,6,0,-6,7,2264],[121,7,8,0,-6,7,2285],[122,6,6,0,-6,6,2313],[123,7,10,0,-8,7,2331],[124,4,11,0,-8,4,2366],[125,7,10,0,-8,7,2388],[126,9,5,0,-5,9,2423],[176,6,8,0,-8,6,2446],[183,4,5,0,-5,4,2470],[186,5,8,0,-8,5,2480],[193,8,10,0,-10,8,2500],[196,8,10,0,-10,8,2540],[201,7,10,0,-10,7,2580],[205,3,10,0,-10,3,2615],[209,8,11,0,-11,8,2630],[211,9,10,0,-10,9,2674],[214,9,10,0,-10,9,2719],[218,8,10,0,-10,8,2764],[220,8,10,0,-10,8,2804],[223,7,9,0,-9,7,2844],[224,7,9,0,-9,7,2876],[225,7,9,0,-9,7,2908],[228,7,8,0,-8,7,2940],[231,6,8,0,-6,6,2968],[232,7,9,0,-9,7,2992],[233,7,9,0,-9,7,3024],[237,4,9,0,-9,3,3056],[241,7,9,0,-9,7,3074],[242,7,9,0,-9,7,3106],[243,7,9,0,-9,7,3138],[246,7,8,0,-8,7,3170],[250,7,9,0,-9,7,3198],[252,7,8,0,-8,7,3230]],"b":[5,176,5,176,5,176,5,176,5,176,4,160,0,0,5,176,14,14,0,224,224,14,14,0,0,0,0,0,0,0,0,0,0,0,0,0,0,3,160,178,0,0,118,28,0,8,255,255,255,96,0,208,118,0,0,43,11,32,2,255,255,255,176,0,148,59,0,0,13,7,96,0,0,8,0,0,93,255,144,14,56,0,0,228,128,0,5,207,180,0,0,149,224,0,8,62,1,254,253,80,0,8,0,0,0,128,0,10,233,0,27,0,90,27,48,147,0,90,27,52,144,0,11,234,27,16,0,0,0,133,77,212,0,3,160,195,59,0,11,32,195,59,0,103,0,78,212,0,158,194,0,0,77,19,112,0,5,176,0,0,0,47,112,0,0,12,74,144,75,4,192,8,186,64,46,81,44,225,0,76,253,103,176,14,0,224,14,0,0,0,0,0,0,0,0,0,178,4,176,9,80,13,32,15,0,15,0,13,32,9,80,4,176,0,178,12,16,6,128,1,208,0,211,0,181,0,180,0,211,1,208,6,128,12,16,0,129,0,117,130,161,7,219,48,7,219,32,117,130,161,0,129,0,0,0,0,0,0,0,0,0,209,0,0,0,13,16,0,0,0,209,0,0,207,255,255,241,0,0,209,0,0,0,13,16,0,0,0,209,0,0,11,96,12,16,127,247,0,0,0,0,0,0,12,80,0,88,0,164,0,208,4,144,9,80,13,16,58,0,134,0,193,0,2,207,194,0,200,24,176,46,0,14,19,208,0,211,61,0,13,50,224,0,225,12,129,139,0,60,252,32,12,255,48,0,0,211,0,0,13,48,0,0,211,0,0,13,48,0,0,211,0,0,13,48,0,175,255,240,6,222,178,2,146,26,160,0,0,92,0,0,11,112,0,9,144,0,9,144,0,10,144,0,3,255,255,208,4,206,195,0,147,23,208,0,0,123,0,12,253,32,0,1,125,0,0,0,241,40,17,125,0,125,235,48,0,7,244,0,2,189,64,0,178,212,0,118,13,64,59,0,212,7,255,255,246,0,0,212,0,0,13,64,12,255,247,0,195,0,0,12,48,0,0,206,235,32,0,1,156,0,0,2,240,0,1,156,2,255,234,32,0,158,252,0,139,32,0,31,32,0,3,232,237,80,63,113,78,33,241,0,196,11,113,78,32,43,253,80,31,255,255,0,0,7,160,0,0,212,0,0,61,0,0,9,112,0,1,226,0,0,107,0,0,12,80,0,5,223,213,0,229,5,224,13,80,93,0,62,254,48,30,81,94,19,208,0,211,30,81,94,16,93,253,80,5,223,178,2,228,23,160,76,0,31,18,228,23,242,5,222,142,32,0,3,224,0,2,200,0,207,233,0,11,96,0,0,0,0,0,0,0,0,11,96,11,96,0,0,0,0,0,0,0,0,11,96,12,16,0,0,1,108,16,0,90,216,48,8,218,64,0,0,141,164,0,0,0,5,173,131,0,0,0,22,193,12,255,255,255,16,0,0,0,0,12,255,255,255,16,0,0,0,0,0,0,0,0,0,11,130,0,0,0,39,219,97,0,0,0,57,218,0,0,3,141,160,2,125,198,16,0,184,32,0,0,63,254,96,0,4,240,0,8,176,0,92,16,0,196,0,0,210,0,0,0,0,0,227,0,0,23,223,233,32,0,28,147,17,109,48,9,96,0,0,44,1,192,44,232,192,162,72,10,96,92,9,36,128,165,5,196,192,28,2,206,142,162,0,166,0,0,0,0,1,201,32,57,96,0,1,141,253,146,0,0,31,128,0,0,122,224,0,0,195,181,0,3,208,91,0,9,112,14,32,14,255,255,112,92,0,4,208,182,0,0,212,14,255,233,0,14,48,45,80,14,48,45,64,14,255,250,0,14,48,27,112,14,48,6,176,14,48,27,144,14,255,234,16,0,141,252,80,10,195,19,161,47,32,0,0,93,0,0,0,93,0,0,0,47,32,0,0,10,195,19,145,0,141,252,80,14,255,234,48,14,48,40,227,14,48,0,169,14,48,0,108,14,48,0,108,14,48,0,169,14,48,40,227,14,255,234,48,14,255,255,32,227,0,0,14,48,0,0,239,255,240,14,48,0,0,227,0,0,14,48,0,0,239,255,244,14,255,250,14,48,0,14,48,0,14,255,245,14,48,0,14,48,0,14,48,0,14,48,0,0,141,253,129,0,172,49,39,80,47,32,0,0,5,208,0,0,0,93,0,63,249,2,242,0,7,144,10,195,17,153,0,8,223,217,16,14,48,0,211,14,48,0,211,14,48,0,211,14,255,255,243,14,48,0,211,14,48,0,211,14,48,0,211,14,48,0,211,14,48,227,14,48,227,14,48,227,14,48,227,0,227,0,227,0,227,0,227,0,227,0,227,0,227,0,226,4,224,141,80,14,48,27,144,14,49,200,0,14,76,128,0,14,232,0,0,14,173,16,0,14,55,209,0,14,48,125,16,14,48,7,209,14,48,0,0,227,0,0,14,48,0,0,227,0,0,14,48,0,0,227,0,0,14,48,0,0,239,255,241,14,208,0,111,96,235,80,12,198,14,91,3,186,96,226,194,149,166,14,38,157,10,96,226,30,112,166,14,32,0,10,96,226,0,0,166,14,192,0,226,14,213,0,226,14,109,0,226,14,43,96,226,14,35,209,226,14,32,167,226,14,32,46,226,14,32,9,242,0,141,252,80,0,172,49,94,80,47,32,0,124,5,208,0,3,240,93,0,0,63,2,242,0,6,192,10,195,21,229,0,8,239,197,0,14,255,213,0,227,5,241,14,48,14,48,227,5,241,14,255,213,0,227,0,0,14,48,0,0,227,0,0,0,141,252,80,0,172,49,94,80,47,32,0,124,5,208,0,3,240,93,0,0,63,2,242,0,6,192,9,195,21,228,0,8,239,244,0,0,0,9,177,0,14,255,213,0,14,48,95,16,14,48,14,48,14,48,94,16,14,255,245,0,14,48,109,0,14,48,10,112,14,48,2,225,5,222,196,2,228,19,160,61,0,0,0,187,116,0,0,38,172,16,0,0,213,56,32,78,48,109,253,96,15,255,255,251,0,3,224,0,0,3,224,0,0,3,224,0,0,3,224,0,0,3,224,0,0,3,224,0,0,3,224,0,31,16,0,241,31,16,0,241,31,16,0,241,31,16,0,241,31,16,0,241,14,32,2,240,10,161,25,176,1,174,234,16,182,0,0,212,92,0,4,208,14,32,10,112,9,128,30,32,3,224,107,0,0,196,197,0,0,124,224,0,0,31,128,0,137,0,79,48,11,100,208,8,198,0,226,31,16,196,160,61,0,181,29,13,7,144,8,148,144,179,182,0,76,133,7,110,32,0,253,32,61,208,0,11,208,0,233,0,29,48,8,160,5,192,61,16,0,168,212,0,0,30,144,0,0,78,192,0,1,212,168,0,10,128,29,48,92,0,5,192,10,112,0,182,1,211,6,176,0,92,45,32,0,10,230,0,0,3,224,0,0,3,224,0,0,3,224,0,0,3,224,0,111,255,255,224,0,0,28,80,0,0,168,0,0,7,176,0,0,92,16,0,3,210,0,0,29,64,0,0,127,255,255,241,31,243,30,0,30,0,30,0,30,0,30,0,30,0,30,0,30,0,31,243,193,0,134,0,58,0,13,16,9,80,4,144,0,208,0,164,0,88,14,245,0,165,0,165,0,165,0,165,0,165,0,165,0,165,0,165,14,245,0,6,249,0,0,6,194,169,0,5,177,0,152,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,47,255,255,144,10,80,0,0,178,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,14,254,177,0,0,24,128,8,223,251,3,210,5,176,76,18,187,0,174,216,176,15,0,0,0,240,0,0,15,0,0,0,247,237,80,15,129,78,16,241,0,181,15,16,11,80,248,20,225,15,126,213,0,3,207,245,30,81,0,91,0,0,91,0,0,30,80,0,4,207,245,0,0,15,0,0,0,240,0,0,15,0,93,231,240,30,65,159,5,160,1,240,90,0,31,1,228,25,240,5,222,127,0,3,206,212,1,195,3,209,95,255,255,53,176,0,0,30,97,0,0,59,239,224,3,223,16,166,0,12,48,11,255,192,12,48,0,195,0,12,48,0,195,0,12,48,0,5,222,127,1,228,25,240,90,0,31,5,160,1,240,30,65,143,0,93,231,224,0,1,139,0,175,235,32,15,0,0,0,240,0,0,15,0,0,0,247,237,80,15,129,77,0,240,0,224,15,0,14,0,240,0,224,15,0,14,0,15,0,0,15,0,240,15,0,240,15,0,240,0,240,0,0,0,240,0,240,0,240,0,240,0,240,0,240,2,224,62,112,15,0,0,0,240,0,0,15,0,0,0,240,9,144,15,27,128,0,252,96,0,15,139,0,0,240,139,16,15,0,123,16,15,0,240,15,0,240,15,0,240,15,0,240,15,0,15,126,212,158,194,0,247,22,246,24,160,15,0,46,0,60,0,240,2,208,3,192,15,0,45,0,60,0,240,2,208,3,192,15,126,213,0,248,20,208,15,0,14,0,240,0,224,15,0,14,0,240,0,224,4,222,178,1,228,24,192,91,0,15,21,176,0,241,30,65,140,0,77,235,32,15,126,213,0,248,20,225,15,16,11,80,241,0,181,15,129,78,16,247,237,80,15,0,0,0,240,0,0,5,222,127,1,228,25,240,90,0,31,5,160,1,240,30,65,159,0,93,231,240,0,0,15,0,0,0,240,15,125,128,248,16,15,16,0,240,0,15,0,0,240,0,10,239,208,75,16,0,46,149,16,2,122,209,0,2,226,111,254,112,15,0,0,240,0,191,255,16,240,0,15,0,0,240,0,14,32,0,126,241,30,0,15,1,224,0,240,30,0,15,1,224,1,240,14,65,143,0,94,215,240,120,0,30,18,224,6,144,11,80,196,0,90,61,0,1,233,112,0,9,242,0,105,3,243,9,98,208,124,112,210,13,43,75,45,0,150,192,198,144,5,217,9,213,0,47,80,95,16,45,32,138,0,92,77,16,0,158,48,0,12,228,0,9,147,210,5,192,6,176,121,0,30,17,225,7,128,9,96,210,0,60,74,0,0,204,64,0,6,192,0,0,150,0,2,251,0,0,111,255,244,0,4,160,0,59,16,2,177,0,27,32,0,143,255,244,0,7,233,0,0,227,0,0,14,16,0,3,224,0,9,247,0,0,4,224,0,0,15,16,0,0,225,0,0,13,64,0,0,110,144,9,80,9,80,9,80,9,80,9,80,9,80,9,80,9,80,9,80,9,80,9,80,9,231,0,0,3,224,0,0,30,0,0,0,227,0,0,7,249,0,0,228,0,0,30,0,0,1,224,0,0,77,0,0,158,96,0,0,0,0,0,0,76,233,33,145,7,33,125,214,0,0,0,0,0,0,0,0,0,0,7,236,16,12,39,96,7,236,16,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,12,80,0,0,0,0,0,0,0,0,27,236,38,145,104,105,22,129,190,194,0,0,4,255,247,0,0,0,0,0,0,7,112,0,0,0,0,0,0,31,128,0,0,122,224,0,0,195,181,0,3,208,91,0,9,112,14,32,14,255,255,112,92,0,4,208,182,0,0,212,0,196,181,0,0,0,0,0,0,31,128,0,0,122,224,0,0,195,181,0,3,208,91,0,9,112,14,32,14,255,255,112,92,0,4,208,182,0,0,212,0,9,80,0,0,0,0,14,255,255,32,227,0,0,14,48,0,0,239,255,240,14,48,0,0,227,0,0,14,48,0,0,239,255,244,8,80,0,14,48,227,14,48,227,14,48,227,14,48,227,0,94,75,0,0,148,199,0,0,0,0,0,14,192,0,226,14,213,0,226,14,109,0,226,14,43,96,226,14,35,209,226,14,32,167,226,14,32,46,226,14,32,9,242,0,1,162,0,0,0,0,0,0,0,141,252,80,0,172,49,94,80,47,32,0,124,5,208,0,3,240,93,0,0,63,2,242,0,6,192,10,195,21,229,0,8,239,197,0,0,76,62,0,0,0,0,0,0,0,141,252,80,0,172,49,94,80,47,32,0,124,5,208,0,3,240,93,0,0,63,2,242,0,6,192,10,195,21,229,0,8,239,197,0,0,4,145,0,0,0,0,0,31,16,0,241,31,16,0,241,31,16,0,241,31,16,0,241,31,16,0,241,14,32,2,240,10,161,25,176,1,174,234,16,0,136,121,0,0,0,0,0,31,16,0,241,31,16,0,241,31,16,0,241,31,16,0,241,31,16,0,241,14,32,2,240,10,161,25,176,1,174,234,16,3,206,177,0,198,24,128,15,2,169,0,240,196,0,15,12,64,0,240,78,112,15,0,28,64,240,1,181,15,111,234,0,4,176,0,0,5,128,0,0,0,0,0,239,235,16,0,1,136,0,141,255,176,61,32,91,4,193,43,176,10,237,139,0,0,7,128,0,3,160,0,0,0,0,0,239,235,16,0,1,136,0,141,255,176,61,32,91,4,193,43,176,10,237,139,0,6,165,192,0,0,0,0,14,254,177,0,0,24,128,8,223,251,3,210,5,176,76,18,187,0,174,216,176,3,207,245,30,81,0,91,0,0,91,0,0,30,80,0,4,207,245,0,6,80,0,159,80,1,194,0,0,2,177,0,0,0,0,0,60,237,64,28,48,61,21,255,255,243,91,0,0,1,230,16,0,3,190,254,0,0,2,193,0,1,178,0,0,0,0,0,60,237,64,28,48,61,21,255,255,243,91,0,0,1,230,16,0,3,190,254,0,2,193,11,32,0,0,15,0,15,0,15,0,15,0,15,0,15,0,0,200,116,0,40,125,16,0,0,0,0,247,237,80,15,129,77,0,240,0,224,15,0,14,0,240,0,224,15,0,14,0,2,193,0,0,3,160,0,0,0,0,0,77,235,32,30,65,140,5,176,0,241,91,0,15,17,228,24,192,4,222,178,0,0,4,176,0,1,177,0,0,0,0,0,77,235,32,30,65,140,5,176,0,241,91,0,15,17,228,24,192,4,222,178,0,3,210,240,0,0,0,0,4,222,178,1,228,24,192,91,0,15,21,176,0,241,30,65,140,0,77,235,32,0,4,176,0,1,177,0,0,0,0,1,224,0,240,30,0,15,1,224,0,240,30,0,31,0,228,24,240,5,237,127,0,3,225,240,0,0,0,0,30,0,15,1,224,0,240,30,0,15,1,224,1,240,14,65,143,0,94,215,240]}};   // letras suavizadas (herramientas/generar_fuentes.py)
const gb = new Uint8Array(128 * 128);   // un byte por píxel (0–15); el firmware los guarda de dos en dos
const gPix = (x, y, v) => { if (x >= 0 && x < 128 && y >= 0 && y < 128) gb[y * 128 + x] = v; };
const gGet = (x, y) => gb[y * 128 + x];
function gRect(x, y, w, h, v) { for (let j = y; j < y + h; j++) for (let i = x; i < x + w; i++) gPix(i, j, v); }
function gFind(f, cp) { let lo = 0, hi = f.g.length - 1; while (lo <= hi) { const m = (lo + hi) >> 1; if (f.g[m][0] === cp) return f.g[m]; if (f.g[m][0] < cp) lo = m + 1; else hi = m - 1; } return cp === 63 ? null : gFind(f, 63); }
// Escribe con la línea base en y, mezclando con el fondo (0–15). Devuelve dónde acaba
function gText(x, y, t, f, col) {
  for (const ch of String(t)) {
    const g = gFind(f, ch.codePointAt(0)); if (!g) continue;
    const [, w, h, xo, yo, adv, off] = g;
    for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) {
      const k = j * w + i, a = (f.b[off + (k >> 1)] >> (k & 1 ? 0 : 4)) & 15;
      if (!a) continue;
      const px = x + xo + i, py = y + yo + j;
      if (px < 0 || px > 127 || py < 0 || py > 127) continue;
      const bg = gGet(px, py); gPix(px, py, bg + Math.round((col - bg) * a / 15));
    }
    x += adv;
  }
  return x;
}
const gWidth = (t, f) => { let w = 0; for (const ch of String(t)) { const g = gFind(f, ch.codePointAt(0)); if (g) w += g[5]; } return w; };
function gDraw() {
  const B = GF.F_BIG, M = GF.F_MED, Sm = GF.F_SMALL;
  gb.fill(0);
  // Cabecera: nombre y hora, y una raya
  gText(0, 10, prefs.name.substring(0, 14), Sm, 9);
  if (timeValid()) { const h = hhmm().trim(); gText(128 - gWidth(h, Sm), 10, h, Sm, 9); }
  gRect(0, 14, 128, 1, 3);
  // Temperatura grande (la de dentro o, sin termómetro, la del agua) y debajo qué es, con la humedad
  let big = '--', lab = '';
  if (!isNaN(E.cabT)) { big = numf(E.cabT, 1); lab = tf('T_D_IN') + (!isNaN(E.cabH) ? ' · ' + Math.round(E.cabH) + ' %' : ''); }
  else if (E.tempC > -100) { big = String(E.tempC); lab = tf('T_D_WATER').split(' ')[0]; }
  const x = gText(1, 50, big, B, 15);
  if (big !== '--') gText(x + 1, 50, '°', B, 15);
  gText(1, 63, lab, Sm, 8);
  // Estado y, calentando, una barra con el tiempo que queda
  let st;
  if (E.heaterOn) {
    st = tf(E.phase === PH_FLAME ? 'T_D_HEAT' : E.phase === PH_PAUSE ? 'T_D_PAUSE' : E.phase === PH_LOST ? 'T_D_LOST' : 'T_D_START');
    const rem = Math.max(0, Math.trunc((E.onUntil - simMs) / 1000)), rt = Math.ceil(rem / 60) + ' min', w = 128 - gWidth(rt, Sm) - 5;
    gRect(0, 83, w, 4, 2); gRect(0, 83, Math.round(w * rem / Math.max(1, E.onTotal)), 4, 12);
    gText(128 - gWidth(rt, Sm), 88, rt, Sm, 10);
  } else st = tf(E.thActive ? 'T_D_WAIT' : 'T_D_OFF');
  gText(0, 79, st, M, E.heaterOn ? 15 : 11);
  // Agua (si arriba va la de dentro) y batería
  if (E.tempC > -100 && !isNaN(E.cabT)) gText(0, 100, trf('T_D_WATER', E.tempC), Sm, 9);
  if (E.volt > 0) { const v = numf(E.volt, 1) + ' V'; gText(128 - gWidth(v, Sm), 100, v, Sm, 9); }
  // Lo siguiente: objetivo del termostato, salida o próximo programa
  let nx = '';
  if (E.thActive) nx = trf('T_D_UNTIL', E.thTarget + ' °C');
  else if (prefs.dep) { const d = new Date(prefs.dep * 60000); nx = trf('T_D_DEP', pad(d.getHours()) + ':' + pad(d.getMinutes())); }
  else { const n = nextSched(); if (n) nx = trf(n.dep ? 'T_D_DEP' : 'T_D_NEXT', n.t); }
  gText(0, 113, nx, Sm, 12);
  // Aviso: franja clara con letra oscura (se apagó sola o por batería; «sin respuesta» ya sale en el estado)
  if (!E.heaterOn && E.stopNote) { const t = tf('T_D_NOTE'); gRect(0, 117, 128, 11, 12); gText((128 - gWidth(t, Sm)) >> 1, 126, t, Sm, 0); }
}
function dispTick() {
  if (E.fast) return;   // prueba automática: no se dibuja la pantalla en cada segundo simulado
  if (!env.scr) E.oledOk = false;   // quitada: el firmware lo nota al fallar el envío de la imagen
  if (!E.oledOk) { E.dispOn = false; return; }
  // «Alguien mirando la web» en tiempo real: la web del simulador pregunta cada 3 s de verdad, que a ×60 son 3 min simulados
  E.dispOn = prefs.disp === 2 || E.btnUntil > simMs || (prefs.disp === 1 && (E.heaterOn || E.dispUntil > simMs || Date.now() - (E.uiReal || 0) < 15000));
  if (E.dispOn && simMs - E.lastDraw >= 1000) { E.lastDraw = simMs; if (prefs.oled === 2) gDraw(); else dispDraw(); }
}
// Color del LED (en el simulador no hay Bluetooth: el azul de «app conectada» no sale)
function ledColor() {
  const blink = Math.floor(simMs / 500) & 1; let r = 0, g = 0, b = 0;
  if (E.heaterOn && (E.phase === PH_LOST || E.busState === 0)) { if (blink) r = 255; }
  else if (E.heaterOn && E.warmSent) g = 255;
  else if (E.heaterOn && E.phase === PH_START) { if (blink) { r = 255; g = 70; } }
  else if (E.heaterOn) { r = 255; g = 70; }
  else if (E.stopNote) { if (Math.floor(simMs / 250) % 12 === 0) r = 255; }
  else if (E.thActive) { g = 120; b = 255; }
  return prefs.led ? [r, g, b] : [0, 0, 0];
}
function checkSchedule() {
  if (!timeValid()) return;
  const d = new Date(espTime() * 1000);
  if (d.getMinutes() === E.lastMinute) return;
  E.lastMinute = d.getMinutes();
  const nowMin = Math.floor(espTime() / 60);
  if (prefs.dep) {
    const done = { v: E.depOnceDone }; depCheck(prefs.dep, prefs.dept, done, 'app'); E.depOnceDone = done.v;
    if (nowMin >= prefs.dep) depSet(0, 0);
  }
  if (!prefs.auto) return;
  const wd = (d.getDay() + 6) % 7, m = d.getHours() * 60 + d.getMinutes();
  for (let i = 0; i < prefs.sch.length; i++) {
    const s = prefs.sch[i], tgt = s.x & SX_TGT;
    if (!s.en) continue;
    if (s.x & SX_DEP) {
      for (let k = 0; k < 2; k++) {
        const diff = k * 1440 + s.start - m;
        if ((s.days >> ((wd + k) % 7) & 1) && diff > 0 && diff <= 60) {
          const done = { v: E.depDone[i] || 0 }; depCheck(nowMin + diff, tgt, done, 'programa'); E.depDone[i] = done.v;
        }
      }
      continue;
    }
    if (E.heaterOn || E.thActive) continue;
    if ((s.days & (1 << wd)) && s.start === m) {
      if (!battOk()) return;
      if (tgt && !isNaN(E.cabT)) startSession(s.dur, tgt, 'programa');
      else startHeater(Math.min(s.dur, MAX_MIN), 'programa');
      return;
    }
  }
}
function errorsJson() {
  const r = wbusCmd(0x56, [0x01]);
  const codes = [];
  if (r && r.length >= 2) for (let i = 0; i < r[1] && 3 + 2 * i < r.length; i++) codes.push({ c: hx(r[2 + 2 * i]), n: r[3 + 2 * i] });
  return JSON.stringify({ ok: !!r, raw: E.lastRx, codes });
}
function espLoop() {
  if (E.down) return;
  if (E.heaterOn) {
    if (simMs - E.onUntil >= 0) stopHeater(tf('T_WHY_END'), !E.thActive);
    else if (simMs - E.lastKA >= KEEPALIVE_MS) {
      E.lastKA = simMs;
      const r = wbusCmd(0x44, [0x21, 0x00], true);
      if (r) {
        if (E.kaFails >= 5) addLog(tf('T_LOG_BUS_BACK'));
        E.kaFails = 0;
        if (r.length >= 1 && r[0] === 0x01) { if (++E.kaOff >= 2) heaterQuit(tf('T_WHY_NO_ORDER')); }
        else E.kaOff = 0;
      } else if (++E.kaFails === 5) {
        E.phase = PH_LOST;
        addLog(tf('T_LOG_BUS_LOST'));
        notify(tf('T_TG_BUS_LOST'));
      } else if (E.kaFails >= 24) {
        endSession(true);
        stopHeater(tf('T_WHY_NO_COMM'), false);
        E.stopNote = hhmm() + tf('T_NOTE_LOST');
        notify(tf('T_TG_LOST'));
      }
    }
  }
  if ((E.heaterOn || simMs - E.lastUi < 15000) && simMs - E.lastSensor >= SENSOR_MS) {
    if (readSensors() && E.heaterOn) { gasTick(); evalHeater(); battRunCheck(); warmCheck(); }
  }
  snTick();
  thermoTick();
  if ((!E.oledOk || !E.snOk) && simMs - E.hwProbeAt > 30000) hwProbe();
  dispTick();
  checkSchedule();
  tgPump();
}
function serialCli(c) {
  serialPrint('> ' + c, 'in');
  c = c.trim().toLowerCase();
  if (c.startsWith('on')) { const p = c.substring(2).trim().split(/\s+/); const e = heatOn(parseInt(p[0]) || 0, parseInt(p[1]) || 0, 'consola'); if (e) serialPrint(e); }
  else if (c === 'off') { endSession(true); stopHeater(tf('T_SRC_CONSOLE'), true); }
  else if (c === 'status') {
    readSensors();
    const PH = ['apagada', 'arrancando', 'con llama', 'pausa de regulación', 'sin respuesta'];
    serialPrint(`Estado: ${PH[E.phase]} | Temp ${E.tempC} C | ${E.volt.toFixed(2)} V | llama ${E.flame} | ${E.power} W\nTX: ${E.lastTx}\nRX: ${E.lastRx}`);
    serialPrint(`Gasoil estimado: encendido ${litros(E.gasCur)} | último ${litros(prefs.gasLast)} | mes ${litros(prefs.gasMonth)} | total ${litros(prefs.gasTotal)}`);
    serialPrint(`Dentro (SHT31): ${E.cabT.toFixed(1)} C, ${E.cabH} % | termostato ${E.thActive ? E.thTarget + ' C' : 'no'}\nPantalla: no`);
    if (E.stopNote) serialPrint(E.stopNote);
  }
  else if (c === 'errores') serialPrint(errorsJson());
  else if (c === 'cfg') serialPrint(route('/api/cfg', 'GET', new URLSearchParams())[1]);
  else if (c.length) serialPrint('Comandos: on [min] [°C] | off | status | errores | cfg | set clave=valor | wifi | forget | reboot');
}

// ---------- Servidor web del ESP32 ----------
function handleState() {
  E.lastUi = simMs; E.uiReal = Date.now();
  const rem = E.heaterOn ? Math.max(0, Math.trunc((E.onUntil - simMs) / 1000)) : 0;
  return {
    on: E.heaterOn, remain: rem, total: E.onTotal, src: E.onSrc, temp: E.tempC, volt: +E.volt.toFixed(2),
    flame: E.flame, pw: E.power, bus: E.busState, time: espTime(), tv: timeValid(), auto: prefs.auto,
    ph: E.phase, note: E.stopNote, tg: !!(prefs.tgtok && prefs.tgchat), tgchat: prefs.tgchat, tgl: E.tgLast,
    wm: prefs.wifimode, ble: 0, name: prefs.name, lang: LANGS[prefs.lang],
    gas: [+E.gasCur.toFixed(2), +prefs.gasLast.toFixed(2), +prefs.gasMonth.toFixed(2), +prefs.gasTotal.toFixed(2)],
    sta: E.sta, ssid: E.sta ? prefs.ssid : '', ip: E.sta ? '192.168.1.57' : '', rssi: E.sta ? -63 : 0,
    tx: E.lastTx, rx: E.lastRx, sch: prefs.sch.map(s => [s.en, s.days, s.start, s.dur, s.x]), log: E.log.slice().reverse(),
    ct: isNaN(E.cabT) ? null : +E.cabT.toFixed(1), ch: isNaN(E.cabH) ? null : E.cabH, tgt: E.thActive ? E.thTarget : 0,
    tun: E.thActive ? Math.trunc((E.thUntil - simMs) / 1000) : 0, dep: prefs.dep * 60, dept: prefs.dept, wa: E.heaterOn && E.warmSent,
  };
}
function route(path, method, args) {
  const a = k => args.get(k) ?? '';
  if (path === '/api/state' && method === 'GET') return [200, JSON.stringify(handleState())];
  if (path === '/api/on' && method === 'POST') {
    const e = heatOn(parseInt(a('min')) || 0, parseInt(a('tgt')) || 0, 'manual');
    return [e ? 400 : 200, e || 'ok'];
  }
  if (path === '/api/dep' && method === 'POST') {
    const t = a('t'), tg = parseInt(a('tgt')) || 0;
    if (t === 'off' || t === '0') { if (prefs.dep) addLog(tf('T_LOG_DEP_OFF')); depSet(0, 0); return [200, 'ok']; }
    const mm = /^(\d{1,2}):(\d{2})$/.exec(t);
    if (!timeValid() || !mm || +mm[1] > 23 || +mm[2] > 59 || (tg && (tg < 5 || tg > 25))) return [400, tf('T_E_DEP')];
    depSet(depNext(+mm[1], +mm[2]), tg);
    return [200, 'ok'];
  }
  if (path === '/api/off' && method === 'POST') { endSession(true); const ok = stopHeater(tf('T_SRC_MANUAL'), true); return [200, ok ? 'ok' : tf('T_W_OFF_NOCONF')]; }
  if (path === '/api/sched' && method === 'POST') {
    prefs.auto = a('auto') === '1';
    prefs.sch = [];
    for (const it of a('list').split(';')) {
      if (prefs.sch.length >= MAX_SCHED) break;
      const p = it.split(',').map(x => parseInt(x));
      if ((p.length === 4 || p.length === 5) && p.every(Number.isFinite) && p[2] >= 0 && p[2] < 1440 && p[3] > 0) {
        let x = (p[4] || 0) & (SX_DEP | SX_TGT); const tg = x & SX_TGT;
        if (tg && (tg < 5 || tg > 25)) x &= SX_DEP;
        prefs.sch.push({ en: p[0] ? 1 : 0, days: p[1] & 0x7F, start: p[2], dur: Math.min(p[3], x & SX_TGT ? MAX_SESSION : MAX_MIN), x });
        E.depDone[prefs.sch.length - 1] = 0;
      }
    }
    addLog(trf('T_LOG_SCHED', prefs.sch.length));
    return [200, 'ok'];
  }
  if (path === '/api/time' && method === 'POST') {
    const e = parseInt(a('epoch')) || 0;
    if (e < 1700000000) return [400, tf('T_E_TIME')];
    // El móvil manda la hora real; en el simulador manda la del mundo simulado
    setEpoch(Math.floor(worldMs / 1000));
    addLog(tf('T_LOG_TIME_WEB'));
    return [200, 'ok'];
  }
  // Redes cercanas (inventadas): como en la placa, la primera vez «buscando» y a los 2 s simulados la lista
  if (path === '/api/scan' && method === 'GET') {
    if (E.scanAt == null || simMs - E.scanAt > 60000) { E.scanAt = simMs; return [200, JSON.stringify({ run: true })]; }
    if (simMs - E.scanAt < 2000) return [200, JSON.stringify({ run: true })];
    E.scanAt = null;
    return [200, JSON.stringify({ nets: [{ s: 'Furgo 4G', r: -48, e: 1 }, { s: 'Casa', r: -61, e: 1 }, { s: 'Camping La Playa', r: -72, e: 0 }, { s: 'MOVISTAR_8F21', r: -83, e: 1 }] })];
  }
  if (path === '/api/errors' && method === 'GET') return [200, errorsJson()];
  if (path === '/api/cfg' && method === 'GET') {
    return [200, JSON.stringify({ name: prefs.name, pin: prefs.pin, wifimode: prefs.wifimode, ssid: prefs.ssid, tg: !!prefs.tgtok,
      tgchat: prefs.tgchat, minvolt: prefs.minvolt.toFixed(1), bonds: 0, lang: LANGS[prefs.lang], ver: FW + S.simulated,
      ota: 1, th: 1, oled: prefs.oled, disp: prefs.disp, led: prefs.led, toff: prefs.toff.toFixed(1), warm: prefs.warm,
      sens: E.snOk ? 'SHT31' : '', scr: E.oledOk })];
  }
  if (path === '/api/cfg' && method === 'POST') {
    // Mismas reglas que cfgSet() del firmware; nombre, claves, PIN y red externa piden reiniciar
    let restart = false;
    const chk = {
      lang: v => LANGS.includes(v) || tf('T_E_LANG'),
      name: v => v.length >= 1 && v.length <= 29 || tf('T_E_NAME'),
      appass: v => !v || (v.length >= 8 && v.length <= 63) || tf('T_E_APPASS'),
      pin: v => /^[1-9][0-9]{5}$/.test(v) || tf('T_E_PIN'),
      wifimode: v => /^[012]$/.test(v) || tf('T_E_WIFIMODE'),
      ssid: v => v.length <= 32 || tf('T_E_SSID'),
      pass: v => v.length <= 63 || tf('T_E_PASS'),
      tgtok: v => v.length <= 63 || tf('T_E_TOKEN'),
      tgchat: v => v.length <= 23 || tf('T_E_CHAT'),
      minvolt: v => (+v >= 10.5 && +v <= 13) || tf('T_E_MINVOLT'),
      oled: v => /^[012]$/.test(v) || trf('T_E_VALUE', 'oled'),
      disp: v => /^[012]$/.test(v) || trf('T_E_VALUE', 'disp'),
      led: v => /^[0-3]$/.test(v) || trf('T_E_VALUE', 'led'),
      toff: v => (v !== '' && +v >= -5 && +v <= 5) || tf('T_E_TOFF'),
      warm: v => (/^\d+$/.test(v) && (+v === 0 || (+v >= 30 && +v <= 80))) || tf('T_E_WARM'),
    };
    for (const k of Object.keys(chk)) {
      if (!args.has(k)) continue;
      const v = a(k).trim(), r = chk[k](v);
      if (r !== true) return [400, r];
      if (k === 'lang') prefs.lang = LANGS.indexOf(v);   // como en el firmware: se aplica al momento
      else if (k === 'name' && v !== prefs.name) { prefs.name = v; restart = true; }
      else if (k === 'appass' && v) { prefs.appass = v; restart = true; }
      else if (k === 'pin' && +v !== prefs.pin) { prefs.pin = +v; restart = true; }
      else if (k === 'wifimode') prefs.wifimode = +v;
      else if (k === 'ssid' && v !== prefs.ssid) { prefs.ssid = v; restart = true; }
      else if (k === 'pass' && v) { prefs.pass = v; restart = true; }
      else if (k === 'tgtok' && v) prefs.tgtok = v;
      else if (k === 'tgchat') prefs.tgchat = v;
      else if (k === 'minvolt') prefs.minvolt = +v;
      else if (k === 'toff') { if (!isNaN(E.cabT)) E.cabT += +v - prefs.toff; prefs.toff = +v; }
      else if (['oled', 'disp', 'led', 'warm'].includes(k)) { prefs[k] = +v; if (k === 'disp' || k === 'oled') E.dispUntil = simMs + 60000; }
    }
    addLog(tf('T_LOG_CFG'));
    if (!restart) return [200, tf('T_W_SAVED')];
    if (E.heaterOn) return [200, tf('T_W_SAVED_LATER')];
    setTimeout(() => reboot(), 800);
    return [200, tf('T_W_SAVED_REBOOT')];
  }
  if (path === '/api/tgtest' && method === 'POST') {
    if (!prefs.tgtok || !prefs.tgchat) return [400, tf('T_E_TG_CFG')];
    if (!prefs.ssid) return [400, tf('T_E_TG_NET')];
    notify(tf('T_TG_TEST'));
    return [200, tf('T_W_TG_SENDING')];
  }
  if (path === '/api/gasreset' && method === 'POST') { E.gasCur = prefs.gasLast = prefs.gasMonth = prefs.gasTotal = 0; addLog(tf('T_LOG_GASRESET')); return [200, tf('T_W_GASRESET')]; }
  if (path === '/api/forget' && method === 'POST') { addLog(trf('T_LOG_FORGET', 0)); return [200, trf('T_W_FORGOT', 0)]; }
  return [302, ''];
}
// fetch() que usa la web del móvil (movil.php) en lugar de la red
window.WB = {
  fetch(url, opt) {
    return new Promise((res, rej) => setTimeout(() => {
      if (E.down) return rej(new TypeError('Load failed'));
      const method = opt && opt.method || 'GET';
      const args = opt && opt.body ? new URLSearchParams(opt.body) : new URLSearchParams();
      const [status, body] = route(url, method, args);
      render();
      res({ ok: status >= 200 && status < 300, status, text: () => Promise.resolve(body) });
    }, 40 + Math.random() * 60));
  },
};

function reboot() {
  REC.on = false; tRecUi();
  E.down = true;
  E.heaterOn = false;
  serialPrint('');
  serialPrint('ets Jun  8 2016 00:22:57  rst:0x1 (POWERON_RESET)', 'in');
  setTimeout(espBoot, 2500);
  render();
}

// ================= Bucle de simulación =================
function advance(ms) {
  while (ms > 0) {
    const s = Math.min(ms, 200);
    simMs += s; worldMs += s; ms -= s;
    hTick(s);
    espLoop();
    if (REC.on && simMs >= REC.next) { recSample(); REC.next += 30000; }
  }
}
let lastReal = performance.now();
setInterval(() => {
  const now = performance.now(), dt = Math.min(1000, now - lastReal); lastReal = now;
  if (speed) advance(dt * speed);
}, 100);

// ================= Pintado =================
const COMPS = [[S.comps[0], s => !['OFF', 'LOCK', 'PAUSE'].includes(s)], [S.comps[1], s => s === 'GLOW' || s === 'IGN'],
  [S.comps[2], s => ['IGN', 'STAB', 'FULL', 'PART'].includes(s)], [S.comps[3], s => ['STAB', 'FULL', 'PART'].includes(s)],
  [S.comps[4], s => !['OFF', 'LOCK'].includes(s)]];
function hm(ms) { const m = Math.ceil(ms / 60000); return m < 60 ? m + ' min' : Math.floor(m / 60) + ' h ' + pad(m % 60); }
function render() {
  const w = new Date(worldMs);
  const f = w.toLocaleDateString(S.loc, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  $('simclock').textContent = `${f[0].toUpperCase() + f.slice(1)} · ${pad(w.getHours())}:${pad(w.getMinutes())}:${pad(w.getSeconds())}`;
  $('simsub').textContent = E.down ? S.booting : (speed ? (speed === 1 ? S.realTime : F(S.timeX, speed)) : S.paused) + F(S.upFor, hm(simMs || 1));
  $('hTemp').textContent = Math.round(H.temp);
  const s = ST[H.st];
  $('hState').innerHTML = `${s.t}<span class="code">0x${hx(s.c)}</span>`;
  $('hState').className = 'state' + (hFlame() ? ' burn' : H.st === 'LOCK' ? ' lock' : '');
  $('hComps').innerHTML = env.f.power ? `<span>${S.noPower}</span>` : COMPS.map(([n, f]) => `<span class="${f(H.st) ? 'on' : ''}">${n}</span>`).join('');
  $('hPow').textContent = H.pw + ' W';
  $('hVolt').textContent = fmt1(env.f.power ? env.batt : hVolt()) + ' V';
  $('hAmp').textContent = env.f.power ? '0 A' : fmt1(AMPS[H.st]) + ' A';
  $('hRun').textContent = H.cmd && ACTIVE.includes(H.st) ? hm(H.runLeft) : '—';
  const errs = [...H.errs].map(([c, n]) => `<b>0x${hx(c)}</b> ${ERRN[c] || ''} (×${n})`);
  $('hErrs').innerHTML = (errs.length ? S.errMem + errs.join(' · ') : S.errMemEmpty) + (H.lastStop ? F(S.lastStop, H.lastStop) : '');
  drawChart();
  drawBoard();
  if (trDirty) drawTrace();
  if (conDirty) drawCon();
  if (tgDirty) drawTg();
}
setInterval(render, 250);

// Pantalla (desde fb, como la manda el ESP32), LED y temperatura real de dentro
function drawBoard() {
  const c = $('oled'), g = c.getContext('2d'), gray = prefs.oled === 2, Hh = gray ? 128 : 64;
  if (c.height !== Hh) c.height = Hh;
  g.fillStyle = '#000'; g.fillRect(0, 0, 128, Hh);
  const on = !E.down && E.oledOk && E.dispOn;
  $('oledMsg').textContent = E.down ? '' : !E.oledOk ? (env.scr ? S.oledProbe : S.oledNone) : on ? '' : prefs.disp === 0 ? S.oledDisabled : S.oledOff;
  if (on && gray) { for (let y = 0; y < 128; y++) for (let x = 0; x < 128; x++) { const v = gb[y * 128 + x]; if (v) { g.fillStyle = `rgb(${v * 17},${v * 17},${v * 16})`; g.fillRect(x, y, 1, 1); } } }
  else if (on) { g.fillStyle = '#bfe6ff'; for (let p = 0; p < 8; p++) for (let x = 0; x < 128; x++) { const v = fb[p * 128 + x]; if (v) for (let b = 0; b < 8; b++) if (v >> b & 1) g.fillRect(x, p * 8 + b, 1, 1); } }
  const [r, gg, b] = E.down ? [0, 0, 0] : ledColor(), lv = [0, 0.4, 0.7, 1][prefs.led] || 0, lit = r || gg || b;
  $('led').style.background = lit ? `rgba(${r},${gg},${b},${0.35 + 0.65 * lv})` : '';
  $('led').style.boxShadow = lit ? `0 0 ${4 + 10 * lv}px rgba(${r},${gg},${b},${0.3 + 0.6 * lv})` : '';
  $('cabv').textContent = fmt1(H.cab) + ' °C';
}
function drawChart() {
  const c = $('chart'), r = c.getBoundingClientRect(), dpr = window.devicePixelRatio || 1;
  if (c.width !== Math.round(r.width * dpr)) { c.width = Math.round(r.width * dpr); c.height = Math.round(120 * dpr); }
  const g = c.getContext('2d'), W = c.width, Hh = c.height, P = 6 * dpr;
  g.clearRect(0, 0, W, Hh);
  const t1 = worldMs, t0 = t1 - 3600000, y = v => Hh - P - (v + 15) / 105 * (Hh - 2 * P);
  g.font = `${10 * dpr}px system-ui`; g.fillStyle = '#7a84a8'; g.strokeStyle = 'rgba(122,132,168,.18)'; g.lineWidth = dpr;
  for (const v of [0, 40, 70, 85]) { g.beginPath(); g.moveTo(0, y(v)); g.lineTo(W, y(v)); g.stroke(); g.fillText(v + '°', 4 * dpr, y(v) - 3 * dpr); }
  g.fillText(S.lastHour, W - 70 * dpr, Hh - 4 * dpr);
  const pts = H.hist.filter(p => p[0] >= t0).concat([[t1, H.temp]]);
  g.beginPath(); g.strokeStyle = '#ff9f1c'; g.lineWidth = 2 * dpr;
  pts.forEach((p, i) => { const x = (p[0] - t0) / 3600000 * W; i ? g.lineTo(x, y(p[1])) : g.moveTo(x, y(p[1])); });
  g.stroke();
}

const DIR = { tx: 'TX', echo: S.echoTag, rx: 'RX', brk: 'BRK' };
function drawTrace() {
  trDirty = false;
  const el = $('trace'), stick = el.scrollTop + el.clientHeight >= el.scrollHeight - 30, showRep = $('trRep').checked;
  let html = '', prev = null;
  for (const e of trace) {
    if (e.rep && !showRep) continue;
    const t = new Date(e.ts);
    // Separador al empezar cada transacción (un BRK va pegado a su TX)
    const gap = prev && (e.k === 'brk' || (e.k === 'tx' && prev !== 'brk')) ? ' gap' : '';
    prev = e.k;
    html += `<div class="tr ${e.k}${e.bad ? ' bad' : ''}${gap}"><span class="ts">${pad(t.getHours())}:${pad(t.getMinutes())}:${pad(t.getSeconds())}</span>` +
      `<span class="d">${DIR[e.k]}</span><span class="by">${e.bytes.length ? hexs(e.bytes) : '—'}</span><span class="x">${e.txt}</span></div>`;
  }
  el.innerHTML = html || `<div class="tr"><span class="x" style="grid-column:1/-1">${S.busEmpty}</span></div>`;
  if (stick) el.scrollTop = el.scrollHeight;
}
let tgDirty = true;
function drawTg() {
  tgDirty = false;
  const el = $('tgfeed');
  el.innerHTML = tgSent.length ? tgSent.slice().reverse().map(m => { const t = new Date(m.ts);
    return `<div class="msg"><span class="ts">${pad(t.getHours())}:${pad(t.getMinutes())}</span>${m.t.replace(/&/g, '&amp;').replace(/</g, '&lt;')}</div>`; }).join('')
    : `<div class="muted">${S.tgEmpty}</div>`;
}
function drawCon() {
  conDirty = false;
  const el = $('con'), stick = el.scrollTop + el.clientHeight >= el.scrollHeight - 30;
  el.innerHTML = con.map(c => `<div class="${c.cls || ''}">${c.l.replace(/&/g, '&amp;').replace(/</g, '&lt;') || '&nbsp;'}</div>`).join('');
  if (stick) el.scrollTop = el.scrollHeight;
}

// ================= Controles =================
document.querySelectorAll('[data-speed]').forEach(b => b.onclick = () => {
  speed = +b.dataset.speed;
  document.querySelectorAll('[data-speed]').forEach(x => x.classList.toggle('on', x === b));
  render();
});
$('jump10').onclick = () => { advance(600000); render(); };
$('reboot').onclick = reboot;
document.querySelectorAll('[data-f]').forEach(i => i.onchange = () => {
  env.f[i.dataset.f] = i.checked;
  $('xWbus').classList.toggle('hide', !env.f.wbus);
  $('xPower').classList.toggle('hide', !env.f.power);
  $('sigOk').classList.toggle('hide', env.f.cross);
  $('sigX').classList.toggle('hide', !env.f.cross);
  $('slpW').classList.toggle('hide', env.f.slp);
  $('slpX').classList.toggle('hide', !env.f.slp);
  render();
});
$('amb').oninput = () => { env.amb = +$('amb').value; $('ambv').textContent = env.amb + ' °C'; };
$('cab').oninput = () => { H.cab = +$('cab').value; E.snLast = -1e9; render(); };   // dentro, a mano (se lee al momento)
$('hwScr').onchange = () => { env.scr = $('hwScr').checked; render(); };
$('hwSens').onchange = () => { env.sens = $('hwSens').checked; render(); };
$('tRun').onclick = async () => {
  const p = { amb: +$('tAmb').value, cab0: +$('tCab').value, tgt: +$('tTgt').value, min: +$('tDur').value, batt: +$('tBatt').value };
  if (p.tgt && !E.snOk) { $('tMsg').textContent = S.tNoSens; return; }
  if (REC.on) { $('tMsg').textContent = S.tBusy; return; }
  $('tMsg').textContent = S.tRunning;
  await new Promise(r => setTimeout(r, 30));
  const r = runTest(p); render();
  if (!r) return;
  const a = WTTCInforme_a(r);
  $('tMsg').textContent = F(S.tDone, a.starts, a.gas);
  try { await savePdf([r]); } catch (e) { $('tMsg').textContent = S.tPdfErr; }
};
function tRecUi() { $('tRec').textContent = REC.on && REC.p && REC.p.manual ? S.tRecStop : S.tRecStart; $('tRec').classList.toggle('warn', REC.on); }
$('tRec').onclick = async () => {
  if (!REC.on) { recStart({ manual: true, amb: env.amb, cab0: H.cab, tgt: 0, min: 0, batt: env.batt }); $('tMsg').textContent = S.tRecOn; tRecUi(); return; }
  const r = recStop(); tRecUi();
  $('tMsg').textContent = F(S.tDone, WTTCInforme_a(r).starts, WTTCInforme_a(r).gas);
  try { await savePdf([r]); } catch (e) { $('tMsg').textContent = S.tPdfErr; }
};
// Resumen rápido para el mensaje (encendidos y gasoil), sin cargar informe.js
function WTTCInforme_a(r) { let n = 0, was = false; for (const x of r.s) { if (x.on && !was) n++; was = x.on; } return { starts: n, gas: fmt1(r.s.length ? r.s[r.s.length - 1].gas : 0) }; }
$('bootBtn').onclick = () => { if (!E.down) { E.dispUntil = E.btnUntil = simMs + 60000; E.lastDraw = -1e9; dispTick(); } render(); };
$('bat').oninput = () => { env.batt = +$('bat').value; $('batv').textContent = fmt1(env.batt) + ' V'; };
$('unlock').onclick = () => { if (H.st === 'LOCK') hSet('OFF'); H.fails = 0; H.cmd = 0; H.lastStop = S.stopF2; render(); };
$('clrerr').onclick = () => { H.errs.clear(); render(); };
$('trRep').onchange = () => { trDirty = true; render(); };
$('trClr').onclick = () => { trace.length = 0; trDirty = true; render(); };
$('conf').onsubmit = e => {
  e.preventDefault();
  const v = $('conx').value; $('conx').value = '';
  if (E.down) return;
  serialCli(v); render();
};

// Tabla de tramas + calculadora
const FR = [[S.fr[0], [0x21, 0x1E]], ['Keep-alive', [0x44, 0x21, 0x00]], [S.fr[1], [0x10]], [S.fr[2], [0x50, 0x05]], [S.fr[3], [0x56, 0x01]]];
const RESP = { 0x21: [0x1E], 0x44: [0x00], 0x10: [], 0x50: null, 0x56: null };
$('frames').innerHTML = FR.map(([n, c]) => {
  const f = [0xF4, c.length + 1, ...c]; f.push(xor(f));
  const rd = RESP[c[0]];
  let r = `<span class="muted">4F … ${S.data}</span>`;
  if (rd) { const b = [0x4F, rd.length + 2, c[0] | 0x80, ...rd]; b.push(xor(b)); r = `<code>${hexs(b)}</code>`; }
  return `<tr><td>${n}</td><td><code>${hexs(f)}</code></td><td>${r}</td></tr>`;
}).join('');
function calc() {
  const cmd = parseInt($('cCmd').value, 16);
  const dat = ($('cDat').value.match(/[0-9a-f]{1,2}/gi) || []).map(x => parseInt(x, 16));
  if (!Number.isFinite(cmd)) { $('cOut').textContent = '—'; return; }
  const f = [0xF4, dat.length + 2, cmd & 255, ...dat]; f.push(xor(f));
  $('cOut').textContent = hexs(f);
}
$('cCmd').oninput = $('cDat').oninput = calc; $('calc').onsubmit = e => e.preventDefault(); calc();

// Arranque: Webasto a temperatura ambiente, ESP32 recién alimentado y la web del móvil
H.temp = env.amb;
espBoot();
render();
$('movil').src = '/movil.php';
})();
</script>
</body>
</html>
