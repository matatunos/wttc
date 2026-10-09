// Service worker mínimo de «Mis estadísticas» (mi.php), como el de musica/ en el portal.
// Solo habilita la instalación como app (PWA); pasa todo a la red, sin caché: los datos tienen que ser los del servidor.
// Se registra con alcance /mi.php: no toca el resto de la web (simulador, descargas…).
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(self.clients.claim()));
self.addEventListener('fetch', (e) => { e.respondWith(fetch(e.request)); });
