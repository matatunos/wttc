<?php
// wttc/api/evento.php — Cuenta acciones de la web pública que no son cargas de página. Código generado íntegramente con
// Claude (Anthropic). Mismo criterio que wttc_visit(): solo sumas por día, sin cookies ni IP (Caddy no registra /api/*)
// y sin bots. Eventos (los manda instalar.php):
//   e=instalar                      → se ha pulsado «Instalar WTTC»
//   e=instalado|fallo & c=s3|esp32  → la instalación desde el navegador ha terminado bien o con error, y en qué placa
//   p=falsa                         → lo anterior, pero de la Webasto falsa (segundo botón de instalar.php)
// Responde 204; cualquier otro evento o placa se ignora.
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store');
$e = $_GET['e'] ?? '';
$c = $_GET['c'] ?? '';
$p = ($_GET['p'] ?? '') === 'falsa' ? '-falsa' : '';   // p=falsa: la Webasto falsa (0.3.11+)
if ($e === 'instalar') wttc_visit("evento-instalar$p");
elseif (in_array($e, ['instalado', 'fallo'], true) && in_array($c, ['s3', 'esp32', 'otro'], true)) wttc_visit("evento-$e$p-$c");
http_response_code(204);
