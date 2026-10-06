<?php
// wttc/api/evento.php — Cuenta acciones de la web pública que no son cargas de página (por ahora, pulsar «Instalar WTTC»
// en instalar.php). Código generado íntegramente con Claude (Anthropic).
// Mismo criterio que wttc_visit(): solo sumas por día, sin cookies ni IP (Caddy no registra /api/*) y sin bots.
// GET /api/evento.php?e=instalar → 204. Cualquier otro evento se ignora.
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store');
$e = $_GET['e'] ?? '';
if (in_array($e, ['instalar'], true)) wttc_visit('evento-' . $e);
http_response_code(204);
