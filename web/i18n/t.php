<?php
// i18n/t.php — Textos de la web pública de WTTC en el idioma $lang y la función t(). Pieza común de index.php y
// esquema.php (la versión para imprimir del esquema). Código generado íntegramente con Claude (Anthropic).
// Necesita $lang ('es', 'en' o 'de') y $version (puede ser ''). Lo que falte en un idioma sale en español.
$lang = in_array($lang ?? 'es', ['es', 'en', 'de'], true) ? $lang : 'es';
$T_ES = require __DIR__ . '/es.php';
$T = $lang === 'es' ? $T_ES : array_replace_recursive($T_ES, require __DIR__ . "/$lang.php");

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
