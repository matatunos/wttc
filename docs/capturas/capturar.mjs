// capturar.mjs — Hace las capturas del README con un navegador sin pantalla (Playwright + Chromium).
// Código generado íntegramente con Claude (Anthropic). Lo ejecuta .github/workflows/capturas.yml
// (al cambiar este fichero o el workflow, o a mano desde la pestaña Actions de GitHub).
//
// Abre la web pública (https://wttc.favala.es), acelera el simulador ×60, pulsa «Encender» en la web de la
// placa (que va dentro de un iframe) y espera a que la Webasto virtual tenga llama. Después captura:
//   simulador.png    — la web de la placa y la Webasto virtual, lado a lado
//   web-placa.png    — solo la web de la placa, como se ve en el móvil
//   esquema.png      — el esquema de conexiones para montar
//   estadisticas.png — la página pública de estadísticas
import { chromium } from 'playwright';

const URL = 'https://wttc.favala.es';
const OUT = 'docs/capturas';

const browser = await chromium.launch();
// Pantalla de portátil con densidad 2 (capturas nítidas), tema oscuro, español y hora de Madrid
const ctx = await browser.newContext({
  viewport: { width: 1280, height: 900 }, deviceScaleFactor: 2,
  colorScheme: 'dark', locale: 'es-ES', timezoneId: 'Europe/Madrid',
});
const page = await ctx.newPage();

await page.goto(URL + '/', { waitUntil: 'networkidle' });
await page.click('button[data-speed="60"]');                     // tiempo simulado ×60
const movil = page.frameLocator('#movil');                       // la web de la placa
await movil.locator('#big:not([disabled])').click();              // «Encender 30 min»
await page.waitForTimeout(9000);                                  // 9 s reales = 9 min simulados: ya hay llama
await page.locator('.grid').first().screenshot({ path: `${OUT}/simulador.png` });
await page.locator('.phone').first().screenshot({ path: `${OUT}/web-placa.png` });
await page.locator('.wirebox').first().screenshot({ path: `${OUT}/esquema.png` });

await page.goto(URL + '/estadisticas.php', { waitUntil: 'networkidle' });
await page.waitForTimeout(1500);                                  // a que Chart.js pinte
await page.screenshot({ path: `${OUT}/estadisticas.png` });

await browser.close();
console.log('Capturas hechas en', OUT);
