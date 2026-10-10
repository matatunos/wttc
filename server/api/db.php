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
//   referrers — de dónde llegan esas entradas: solo el dominio (google.es, furgovw.org…), por día y página de llegada
// Vive fuera de la carpeta web: /var/wttc-data/stats.sqlite (appdata/wttc-data en vigia, montaje propio con escritura).
// No se guarda la IP ni nada que identifique a la persona: solo un identificador aleatorio de instalación,
// que la app genera al aceptar y borra (pidiendo aquí su borrado) si se retira el permiso.

if (!defined('WTTC_DB')) define('WTTC_DB', '/var/wttc-data/stats.sqlite');   // las pruebas pueden definir otra
const WTTC_KINDS = ['movil', 'tablet', 'radio'];
const WTTC_HOST = 'wttc.favala.es';
// Código de instalación de una placa: 16 caracteres sin 0/O ni 1/I, en grupos de 4 (ver iidParse en WTTC.ino)
const WTTC_IID_RE = '/^[2-9A-HJ-NP-Z]{4}(-[2-9A-HJ-NP-Z]{4}){3}$/';

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
        -- Estadísticas de cada placa (opcionales, con su código de instalación): ver api/placa.php y mi.php
        CREATE TABLE IF NOT EXISTS boards (iid TEXT PRIMARY KEY, first_seen TEXT NOT NULL, last_seen TEXT NOT NULL,
            fw TEXT, lang TEXT, gas REAL, hsec INTEGER, nruns INTEGER, oled INTEGER, sens INTEGER);
        CREATE TABLE IF NOT EXISTS board_runs (iid TEXT NOT NULL, seq INTEGER NOT NULL, t0 INTEGER, dur INTEGER, ml INTEGER,
            cab0 INTEGER, cab1 INTEGER, cmax INTEGER, vmin INTEGER, src INTEGER, endr INTEGER, err INTEGER, got TEXT,
            PRIMARY KEY (iid, seq));
        CREATE TABLE IF NOT EXISTS board_days (iid TEXT NOT NULL, day TEXT NOT NULL, gas REAL, hsec INTEGER, nruns INTEGER,
            PRIMARY KEY (iid, day));
        -- Registros que envía una placa a petición del usuario (Diagnóstico → «Enviar el registro»): ver api/registro.php
        CREATE TABLE IF NOT EXISTS board_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, iid TEXT NOT NULL, at TEXT NOT NULL,
            fw TEXT, body TEXT NOT NULL);
        CREATE INDEX IF NOT EXISTS board_logs_iid ON board_logs (iid, id);
        -- Órdenes remotas firmadas para una placa (server/scripts/wttc-orden.sh las deja; api/orden.php las entrega)
        CREATE TABLE IF NOT EXISTS board_cmds (id INTEGER PRIMARY KEY AUTOINCREMENT, iid TEXT NOT NULL, msg TEXT NOT NULL,
            sig TEXT NOT NULL, created TEXT NOT NULL, expires INTEGER NOT NULL, delivered TEXT);
        CREATE INDEX IF NOT EXISTS board_cmds_iid ON board_cmds (iid, delivered);
        CREATE TABLE IF NOT EXISTS referrers (day TEXT NOT NULL, host TEXT NOT NULL, page TEXT NOT NULL,
            n INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, host, page));
    ');
    // Columnas añadidas después (firmware 0.2.17+): humedad, objetivo y minutos hasta llegar, agua y batería al empezar,
    // potencia media. CREATE TABLE IF NOT EXISTS no las añade a una tabla que ya existía
    $have = array_column($db->query('PRAGMA table_info(board_runs)')->fetchAll(), 'name');
    foreach (['hum0', 'hum1', 'tgt', 'treach', 'c0', 'v0', 'pw'] as $c)
        if (!in_array($c, $have, true)) $db->exec("ALTER TABLE board_runs ADD COLUMN $c INTEGER");
    return $db;
}

// Cuenta una carga de una página de la web pública. Sin cookies ni IP: solo sumas por día y página.
// «entries» = la carga viene de fuera de la web (buscador, enlace, URL escrita), es decir, una visita nueva;
// de esas se apunta también de dónde vienen, solo el dominio (nunca la URL entera, que puede llevar datos).
// Un enlace etiquetado (?desde=furgovw o ?utm_source=…) manda sobre el referer: sirve para sitios que no lo envían.
// Los bots no cuentan. Nunca rompe la página: si algo falla, no se cuenta y ya está.
function wttc_visit(string $page): void {
    try {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') return;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($ua === '' || preg_match('/bot|crawl|spider|slurp|preview|monitor|uptime|headless|curl|wget|python|java\/|go-http|httpclient|okhttp|lighthouse/i', $ua)) return;
        if (($_SERVER['HTTP_SEC_PURPOSE'] ?? $_SERVER['HTTP_PURPOSE'] ?? '') !== '') return;   // precargas del navegador
        // Host no sirve: Caddy reenvía con «Host: tools.favala.es»; el dominio público es fijo
        $ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
        $refHost = strtolower((string)parse_url($ref, PHP_URL_HOST));
        $tag = (string)($_GET['desde'] ?? $_GET['utm_source'] ?? '');
        $tag = preg_match('/^[A-Za-z0-9._-]{1,40}$/', $tag) ? strtolower($tag) : '';
        $entry = ($refHost !== WTTC_HOST || $tag !== '') ? 1 : 0;
        $day = gmdate('Y-m-d');
        $db = wttc_db();
        $db->prepare('INSERT INTO visits (day, page, views, entries) VALUES (?, ?, 1, ?)
                      ON CONFLICT(day, page) DO UPDATE SET views = views + 1, entries = entries + excluded.entries')
           ->execute([$day, $page, $entry]);
        if ($entry) {
            // Procedencia: dominio sin «www.»; sin referer, «(directo)»; las apps Android mandan android-app://paquete
            if ($tag !== '') $src = 'enlace:' . $tag;
            elseif ($ref === '') $src = '(directo)';
            elseif (str_starts_with($ref, 'android-app://')) $src = 'app:' . substr(preg_replace('#^android-app://([^/]+).*#', '$1', $ref), 0, 70);
            else $src = preg_replace('/^www\./', '', $refHost);
            if (!preg_match('/^[a-z0-9:._()-]{1,80}$/', $src)) $src = '(otro)';
            // Tope de procedencias distintas por día: el referer y ?desde= los manda el visitante y podría
            // inventarse miles para llenar la base de datos. Pasado el tope, las nuevas cuentan como «(otro)»
            $known = $db->prepare('SELECT 1 FROM referrers WHERE day = ? AND host = ? LIMIT 1'); $known->execute([$day, $src]);
            if (!$known->fetchColumn()) {
                $n = $db->prepare('SELECT COUNT(DISTINCT host) FROM referrers WHERE day = ?'); $n->execute([$day]);
                if ($n->fetchColumn() >= 200) $src = '(otro)';
            }
            $db->prepare('INSERT INTO referrers (day, host, page, n) VALUES (?, ?, ?, 1) ON CONFLICT(day, host, page) DO UPDATE SET n = n + 1')
               ->execute([$day, $src, $page]);
        }
    } catch (Throwable $e) {
        error_log('wttc visit: ' . $e->getMessage());
    }
}

// Periodo pedido por la página (?period=7d|30d|90d|1y|all o ?from=AAAA-MM-DD&to=AAAA-MM-DD) → [desde, hasta] en UTC
function wttc_range(array $q): array {
    $to = gmdate('Y-m-d');
    $re = '/^\d{4}-\d{2}-\d{2}$/';
    if (isset($q['from'], $q['to']) && is_string($q['from']) && is_string($q['to'])
        && preg_match($re, $q['from']) && preg_match($re, $q['to'])
        && checkdate((int)substr($q['from'], 5, 2), (int)substr($q['from'], 8, 2), (int)substr($q['from'], 0, 4))
        && checkdate((int)substr($q['to'], 5, 2), (int)substr($q['to'], 8, 2), (int)substr($q['to'], 0, 4))) {
        [$a, $b] = $q['from'] <= $q['to'] ? [$q['from'], $q['to']] : [$q['to'], $q['from']];
        // Dentro de lo que hay datos: desde 2026 hasta hoy. Sin este límite, un rango de siglos hace que la
        // página monte millones de días en memoria (cualquiera puede pedirlo: la página es pública)
        return [max($a, '2026-01-01'), min(max($b, '2026-01-01'), $to)];
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

// Significado de los códigos de avería más comunes de la Thermo Top (tabla de errores de libwbus, wbus/wbus_const.h).
// 0x0_ = cortocircuito, 0x8_ = circuito abierto. Lista completa en https://wttc.favala.es/averias.php
function wttc_error_name(string $code): string {
    $m = ['01' => 'unidad de control defectuosa', '02' => 'no arranca', '03' => 'fallo de llama', '04' => 'tensión demasiado alta',
          '05' => 'llama antes de encender', '06' => 'sobrecalentamiento', '07' => 'bloqueada (interlock)', '08' => 'bomba dosificadora (cortocircuito)',
          '09' => 'ventilador (cortocircuito)', '0A' => 'bujía (cortocircuito)', '0B' => 'bomba de agua (cortocircuito)', '12' => 'fallo de comunicación W-Bus',
          '14' => 'sensor de temperatura (cortocircuito)', '15' => 'ventilador bloqueado', '83' => 'fallo de llama', '84' => 'tensión demasiado baja',
          '87' => 'bloqueo permanente', '88' => 'bomba dosificadora', '89' => 'ventilador (circuito abierto)', '8A' => 'bujía (circuito abierto)',
          '8B' => 'bomba de agua (circuito abierto)', '92' => 'fallo de refresco de la orden', '94' => 'sensor de temperatura (circuito abierto)'];
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

