<?php
// wttc-stats/ — Vista privada (con login) de las estadísticas de la app WTTC: detalle por día e instalaciones.
// Código generado íntegramente con Claude (Anthropic).
// La pública y agregada está en https://wttc.favala.es/estadisticas.php
require_once __DIR__ . '/../auth.php';
tools_auth_require();
require_once __DIR__ . '/../wttc/api/db.php';
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$db = wttc_db();
$days = $db->query("SELECT day, COUNT(*) n FROM pings WHERE day >= date('now','-60 days') GROUP BY day ORDER BY day DESC")->fetchAll();
$news = array_column($db->query("SELECT first_seen d, COUNT(*) n FROM installs WHERE first_seen >= date('now','-60 days') GROUP BY first_seen")->fetchAll(), 'n', 'd');
$inst = $db->query('SELECT * FROM installs ORDER BY last_seen DESC, first_seen DESC LIMIT 200')->fetchAll();
$tot = array_column($db->query('SELECT k, SUM(v) AS v FROM daily GROUP BY k')->fetchAll(), 'v', 'k');
$errs = $db->query('SELECT code, SUM(n) AS n FROM err_daily GROUP BY code ORDER BY n DESC')->fetchAll();
$kinds = ['movil' => 'Móvil', 'tablet' => 'Tablet', 'radio' => 'Radio'];
// Visitas a la web pública (wttc_visit en db.php): «visitas» = entradas desde fuera; «páginas» = cargas
$vis = function (int $days) use ($db): array {
    $q = $db->prepare("SELECT COALESCE(SUM(entries), 0) e, COALESCE(SUM(views), 0) v FROM visits WHERE day >= ? AND page NOT LIKE 'evento-%'");
    $q->execute([$days ? gmdate('Y-m-d', time() - ($days - 1) * 86400) : '0000-00-00']);
    return $q->fetch();
};
// Procedencia de las visitas (solo el dominio; ver wttc_visit en db.php), últimos 30 días, con su página de llegada
$refs = [];
foreach ($db->query("SELECT host, page, SUM(n) n FROM referrers WHERE day >= date('now','-30 days') GROUP BY host, page ORDER BY host") as $r) {
    $refs[$r['host']]['n'] = ($refs[$r['host']]['n'] ?? 0) + $r['n'];
    $refs[$r['host']]['p'][$r['page']] = (int)$r['n'];
}
uasort($refs, fn($a, $b) => $b['n'] <=> $a['n']);
$pageNames = ['portada' => 'portada', 'portada-en' => 'portada EN', 'portada-de' => 'portada DE', 'averias' => 'averías',
              'averias-en' => 'averías EN', 'averias-de' => 'averías DE', 'estadisticas' => 'estadísticas',
              'instalar' => 'instalar', 'instalar-en' => 'instalar EN', 'instalar-de' => 'instalar DE'];
$visTot = ['Hoy' => $vis(1), '7 días' => $vis(7), '30 días' => $vis(30), 'Total' => $vis(0)];
$visDays = $db->query("SELECT day, SUM(CASE WHEN page NOT LIKE 'evento-%' THEN entries END) e, SUM(CASE WHEN page NOT LIKE 'evento-%' THEN views END) v, SUM(CASE WHEN page = 'portada' THEN views END) p,
                       SUM(CASE WHEN page = 'portada-en' THEN views END) pen, SUM(CASE WHEN page = 'portada-de' THEN views END) pde,
                       SUM(CASE WHEN page LIKE 'averias%' THEN views END) av, SUM(CASE WHEN page = 'estadisticas' THEN views END) s,
                       SUM(CASE WHEN page LIKE 'instalar%' THEN views END) ins, SUM(CASE WHEN page = 'evento-instalar' THEN views END) insc,
                       SUM(CASE WHEN page LIKE 'evento-instalado-%' THEN views END) inok, SUM(CASE WHEN page LIKE 'evento-fallo-%' THEN views END) inko
                       FROM visits WHERE day >= date('now','-30 days') GROUP BY day ORDER BY day DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>WTTC · estadísticas privadas</title>
<style>
  :root{--bg:#0f1117;--card:#1a1d27;--text:#e2e8f8;--muted:#7a84a8;--border:#2e3350;--acc:#3a8ee0}
  body{margin:0;background:var(--bg);color:var(--text);font:14px/1.45 -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
  .wrap{max-width:1100px;margin:0 auto;padding:18px 16px 40px}
  a{color:var(--acc)} h1{font-size:1.3rem;margin:6px 0}
  .card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px;margin-top:14px;overflow-x:auto}
  h2{font-size:.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;margin:0 0 10px}
  table{width:100%;border-collapse:collapse;font-size:.82rem} th,td{text-align:left;padding:4px 6px;border-top:1px solid var(--border);white-space:nowrap}
  th{color:var(--muted);font-weight:600} td.n{text-align:right;font-variant-numeric:tabular-nums}
  .kv{display:flex;gap:24px;flex-wrap:wrap} .kv b{font-size:1.4rem;display:block}
  .grid{display:grid;grid-template-columns:1fr 2fr;gap:14px} @media(max-width:800px){.grid{grid-template-columns:1fr}}
  .grid .card{margin-top:0}
  .muted{color:var(--muted)} .kv .muted{font-size:.75rem}
</style></head><body><div class="wrap">
<a href="/">← tools.favala.es</a> · <a href="https://wttc.favala.es/estadisticas.php">vista pública</a>
<h1>WTTC · estadísticas privadas</h1>
<div class="card kv">
  <div><span>Instalaciones</span><b><?= count($inst) ?></b></div>
  <div><span>Encendidos app</span><b><?= (int)($tot['starts_app'] ?? 0) ?></b></div>
  <div><span>Encendidos programa</span><b><?= (int)($tot['starts_prog'] ?? 0) ?></b></div>
  <div><span>Apagados solos</span><b><?= (int)($tot['self_stops'] ?? 0) ?></b></div>
  <div><span>Averías</span><b><?= $h(implode(' · ', array_map(fn($e) => '0x' . $e['code'] . '×' . $e['n'], $errs)) ?: '—') ?></b></div>
</div>
<div class="card"><h2>Visitas a wttc.favala.es</h2>
  <div class="kv">
  <?php foreach ($visTot as $k => $t): ?><div><span><?= $h($k) ?></span><b><?= (int)$t['e'] ?></b><span class="muted"><?= (int)$t['v'] ?> páginas</span></div><?php endforeach; ?>
  </div>
  <p class="muted" style="margin:10px 0 0;font-size:.78rem">Visita = llegada desde fuera de la web (buscador, enlace o dirección escrita). Páginas = cargas de la portada (en español, /en/ o /de/), de las averías, de las estadísticas y del instalador. «Clics Instalar» = veces que se pulsa el botón de instalar desde el navegador; «Instaladas» y «Fallidas» = cómo terminó la grabación (solo si la pestaña sigue abierta al acabar). Nada de esto cuenta como visita.<?php
  $byChip = $db->query("SELECT page, SUM(views) n FROM visits WHERE page LIKE 'evento-instalado-%' OR page LIKE 'evento-fallo-%' GROUP BY page")->fetchAll(PDO::FETCH_KEY_PAIR);
  $chipTxt = []; foreach (['s3' => 'ESP32-S3', 'esp32' => 'ESP32', 'otro' => 'otra'] as $k => $n) { $ok = (int)($byChip["evento-instalado-$k"] ?? 0); $ko = (int)($byChip["evento-fallo-$k"] ?? 0); if ($ok || $ko) $chipTxt[] = "$n: $ok bien, $ko con error"; }
  if ($chipTxt) echo ' Por placa (total): ' . $h(implode(' · ', $chipTxt)) . '.'; ?> Sin bots, sin cookies y sin IP.</p>
  <?php if ($visDays): ?>
  <table style="margin-top:10px"><thead><tr><th>Día</th><th>Visitas</th><th>Páginas</th><th>Portada ES</th><th>EN</th><th>DE</th><th>Averías</th><th>Estadísticas</th><th>Instalar</th><th>Clics «Instalar»</th><th>Instaladas</th><th>Fallidas</th></tr></thead><tbody>
  <?php foreach ($visDays as $d): ?><tr><td><?= $h($d['day']) ?></td><td class="n"><?= (int)$d['e'] ?></td><td class="n"><?= (int)$d['v'] ?></td><td class="n"><?= (int)$d['p'] ?></td><td class="n"><?= (int)$d['pen'] ?></td><td class="n"><?= (int)$d['pde'] ?></td><td class="n"><?= (int)$d['av'] ?></td><td class="n"><?= (int)$d['s'] ?></td><td class="n"><?= (int)$d['ins'] ?></td><td class="n"><?= (int)$d['insc'] ?></td><td class="n"><?= (int)$d['inok'] ?></td><td class="n"><?= (int)$d['inko'] ?></td></tr><?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</div>
<div class="card"><h2>Procedencia de las visitas (últimos 30 días)</h2>
  <?php if ($refs): ?>
  <table><thead><tr><th>Desde</th><th>Visitas</th><th>Página de llegada</th></tr></thead><tbody>
  <?php foreach ($refs as $host => $r): arsort($r['p']); ?><tr><td><?= $h($host) ?></td><td class="n"><?= (int)$r['n'] ?></td>
    <td style="white-space:normal"><?= $h(implode(' · ', array_map(fn($p, $n) => ($pageNames[$p] ?? $p) . ' ×' . $n, array_keys($r['p']), $r['p']))) ?></td></tr><?php endforeach; ?>
  </tbody></table>
  <?php else: ?><p class="muted">Aún sin datos (se apunta desde el 5 de octubre de 2026).</p><?php endif; ?>
  <p class="muted" style="margin:10px 0 0;font-size:.78rem">Solo el dominio del que vienen (google.es, furgovw.org…); «enlace:furgovw» = enlace etiquetado (https://wttc.favala.es/?desde=furgovw), cuenta aunque el sitio no mande la procedencia; «(directo)» = sin procedencia: dirección escrita, marcador o una app que no la manda. Lo que se buscó en Google no llega nunca: está en Search Console → Rendimiento.</p>
</div>
<div class="grid" style="margin-top:14px">
  <div class="card"><h2>Últimos 60 días</h2>
    <table><thead><tr><th>Día</th><th>Informes</th><th>Nuevas</th></tr></thead><tbody>
    <?php foreach ($days as $d): ?><tr><td><?= $h($d['day']) ?></td><td class="n"><?= (int)$d['n'] ?></td><td class="n"><?= (int)($news[$d['day']] ?? 0) ?></td></tr><?php endforeach; ?>
    <?php if (!$days): ?><tr><td colspan="3">Sin informes todavía.</td></tr><?php endif; ?>
    </tbody></table></div>
  <div class="card"><h2>Instalaciones (200 más recientes)</h2>
    <table><thead><tr><th>Id</th><th>Primera vez</th><th>Última</th><th>App</th><th>Firmware</th><th>Android</th><th>Tipo</th><th>País</th></tr></thead><tbody>
    <?php foreach ($inst as $i): ?><tr><td title="Solo se muestra el principio del identificador aleatorio"><?= $h(substr($i['id'], 0, 8)) ?>…</td>
      <td><?= $h($i['first_seen']) ?></td><td><?= $h($i['last_seen']) ?></td><td><?= $h($i['app']) ?></td><td><?= $h($i['fw']) ?></td>
      <td><?= $i['sdk'] ? $h(wttc_android((int)$i['sdk'])) : '' ?></td><td><?= $h($kinds[$i['kind']] ?? '') ?></td><td><?= $h(wttc_country((string)$i['country'])) ?></td></tr><?php endforeach; ?>
    <?php if (!$inst): ?><tr><td colspan="8">Sin instalaciones todavía.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
</div></body></html>
