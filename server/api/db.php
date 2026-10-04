<?php
// wttc/api/db.php — Base de datos de las estadísticas anónimas de la app WTTC (SQLite).
// Vive fuera de la carpeta web: /var/wttc-data/stats.sqlite (appdata/wttc-data en vigia, montaje propio con escritura).
// No se guarda la IP ni nada que identifique a la persona: solo un identificador aleatorio de instalación,
// que la app genera al aceptar y borra (pidiendo aquí su borrado) si se retira el permiso.

if (!defined('WTTC_DB')) define('WTTC_DB', '/var/wttc-data/stats.sqlite');   // las pruebas pueden definir otra
const WTTC_KINDS = ['movil', 'tablet', 'radio'];

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
        CREATE TABLE IF NOT EXISTS totals (k TEXT PRIMARY KEY, v INTEGER NOT NULL DEFAULT 0);
        CREATE TABLE IF NOT EXISTS errors (code TEXT PRIMARY KEY, n INTEGER NOT NULL DEFAULT 0);
    ');
    return $db;
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

