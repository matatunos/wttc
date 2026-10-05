<?php
// wttc/api/db.php — Base de datos de las estadísticas anónimas de la app WTTC (SQLite).
// Código generado íntegramente con Claude (Anthropic).
//
// Tablas:
//   installs  — una fila por instalación que ha aceptado: identificador aleatorio, primera y última vez,
//               versiones (app, firmware, Android), tipo de dispositivo y país (según el idioma del sistema)
//   pings     — qué instalaciones informaron cada día (para contar activas por día o semana)
//   daily     — contadores sumados por día: starts_app, starts_prog, self_stops (sin instalación)
//   err_daily — códigos de avería sumados por día (sin instalación)
//   visits    — visitas a la web pública por día y página: páginas vistas y entradas desde fuera de la web
// Vive fuera de la carpeta web: /var/wttc-data/stats.sqlite (appdata/wttc-data en vigia, montaje propio con escritura).
// No se guarda la IP ni nada que identifique a la persona: solo un identificador aleatorio de instalación,
// que la app genera al aceptar y borra (pidiendo aquí su borrado) si se retira el permiso.

if (!defined('WTTC_DB')) define('WTTC_DB', '/var/wttc-data/stats.sqlite');   // las pruebas pueden definir otra
const WTTC_KINDS = ['movil', 'tablet', 'radio'];
const WTTC_HOST = 'wttc.favala.es';

// La web también se abre en tools.favala.es/wttc/ (misma carpeta): esas visitas van al dominio público,
// para que los buscadores solo vean una copia. Caddy pone el dominio original en X-Forwarded-Host;
// si no viene, no se redirige (así nunca hay bucle).
function wttc_public_only(): void {
    if (($_SERVER['HTTP_X_FORWARDED_HOST'] ?? '') !== 'tools.favala.es') return;
    $uri = preg_replace('#^/wttc(?=/|$)#', '', $_SERVER['REQUEST_URI'] ?? '/');
    header('Location: https://' . WTTC_HOST . ($uri === '' ? '/' : $uri), true, 301);
    exit;
}

function wttc_db(): PDO {
    static $db = null;
    if ($db) return $db;
    $db = new PDO('sqlite:' . WTTC_DB, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('PRAGMA journal_mode=WAL; PRAGMA busy_timeout=3000;');
    $db->exec('
        CREATE TABLE IF NOT EXISTS installs (
            id TEXT PRIMARY KEY, first_seen TEXT NOT NULL, last_seen TEXT NOT NULL,
            app TEXT, fw TEXT, sdk INTEGER, kind TEXT, country TEXT);
        CREATE TABLE IF NOT EXISTS pings (day TEXT NOT NULL, id TEXT NOT NULL, PRIMARY KEY (day, id));
        -- Contadores y averías por día (sin instalación: solo sumas), para poder filtrar por periodo
        CREATE TABLE IF NOT EXISTS daily (day TEXT NOT NULL, k TEXT NOT NULL, v INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, k));
        CREATE TABLE IF NOT EXISTS err_daily (day TEXT NOT NULL, code TEXT NOT NULL, n INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, code));
        CREATE TABLE IF NOT EXISTS visits (day TEXT NOT NULL, page TEXT NOT NULL, views INTEGER NOT NULL DEFAULT 0,
            entries INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, page));
    ');
    return $db;
}

// Cuenta una carga de una página de la web pública. Sin cookies ni IP: solo sumas por día y página.
// «entries» = la carga viene de fuera de la web (buscador, enlace, URL escrita), es decir, una visita nueva.
// Los bots no cuentan. Nunca rompe la página: si algo falla, no se cuenta y ya está.
function wttc_visit(string $page): void {
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') return;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|monitor|uptime|headless|curl|wget|python|java\/|go-http|httpclient|okhttp|lighthouse/i', $ua)) return;
        if (($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? '') !== '') return;   // precargas del navegador
        // Host no sirve: Caddy reenvía con «Host: tools.favala.es»; el dominio público es fijo
        $entry = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST) !== WTTC_HOST ? 1 : 0;
        wttc_db()->prepare('INSERT INTO visits (day, page, views, entries) VALUES (?, ?, 1, ?)
                            ON CONFLICT(day, page) DO UPDATE SET views = views + 1, entries = entries + excluded.entries')
                 ->execute([gmdate('Y-m-d'), $page, $entry]);
    } catch (Throwable $e) {
        error_log('wttc visit: ' . $e->getMessage());
    }
}

// Periodo pedido por la página (?period=7d|30d|90d|1y|all o ?from=AAAA-MM-DD&to=AAAA-MM-DD) → [desde, hasta] en UTC
function wttc_range(array $q): array {
    $to = gmdate('Y-m-d');
    $re = '/^\d{4}-\d{2}-\d{2}$/';
    if (isset($q['from'], $q['to']) && preg_match($re, $q['from']) && preg_match($re, $q['to'])) {
        return $q['from'] <= $q['to'] ? [$q['from'], $q['to']] : [$q['to'], $q['from']];
    }
    $days = ['7d' => 7, '30d' => 30, '90d' => 90, '1y' => 365][$q['period'] ?? '30d'] ?? null;
    if ($days === null) return ['2026-01-01', $to];                  // «Todo»
    return [gmdate('Y-m-d', time() - ($days - 1) * 86400), $to];
}

// Versión de Android legible a partir del nivel de API
function wttc_android(int $sdk): string {
    $m = [26 => '8.0', 27 => '8.1', 28 => '9', 29 => '10', 30 => '11', 31 => '12', 32 => '12L', 33 => '13', 34 => '14', 35 => '15', 36 => '16'];
    return isset($m[$sdk]) ? 'Android ' . $m[$sdk] : "API $sdk";
}

// Significado de los códigos de avería más comunes de la Thermo Top (según libwbus)
function wttc_error_name(string $code): string {
    $m = ['01' => 'unidad de control defectuosa', '02' => 'no arranca', '03' => 'fallo de llama', '04' => 'tensión demasiado alta',
          '05' => 'llama antes de encender', '06' => 'sobrecalentamiento', '07' => 'bloqueada (interlock)', '08' => 'bomba dosificadora',
          '09' => 'ventilador', '0A' => 'bujía', '0B' => 'bomba de agua', '12' => 'fallo de comunicación W-Bus', '14' => 'tensión demasiado baja'];
    return $m[strtoupper($code)] ?? '';
}

// Nombre en español de los países más probables (el contenedor no tiene la extensión intl)
function wttc_country(string $c): string {
    if ($c === '') return 'Sin indicar';
    $m = ['ES' => 'España', 'PT' => 'Portugal', 'FR' => 'Francia', 'DE' => 'Alemania', 'IT' => 'Italia', 'GB' => 'Reino Unido',
          'IE' => 'Irlanda', 'NL' => 'Países Bajos', 'BE' => 'Bélgica', 'LU' => 'Luxemburgo', 'AT' => 'Austria', 'CH' => 'Suiza',
          'PL' => 'Polonia', 'CZ' => 'Chequia', 'SE' => 'Suecia', 'NO' => 'Noruega', 'DK' => 'Dinamarca', 'FI' => 'Finlandia',
          'US' => 'Estados Unidos', 'MX' => 'México', 'AR' => 'Argentina', 'CL' => 'Chile', 'CO' => 'Colombia', 'AD' => 'Andorra'];
    return $m[$c] ?? $c;
}

