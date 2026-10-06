// probar_web.js — Prueba la web de la placa (firmware/WTTC/web.h) sin navegador ni placa.
// Código generado íntegramente con Claude (Anthropic).
// Saca el JavaScript de web.h, lo ejecuta en Node con un DOM mínimo y estados de ejemplo (calentando con termostato,
// apagada sin termómetro) y comprueba lo que pinta en español, inglés y alemán: estado, temperatura de dentro, salida,
// programas (con sus días y opciones) y configuración. Si algo falla o no cuadra, termina con error y la CI lo marca.
// Uso: node .github/scripts/probar_web.js firmware/WTTC/web.h
const fs = require('fs'), vm = require('vm'), assert = require('assert');
const h = fs.readFileSync(process.argv[2], 'utf8'), i0 = h.indexOf('<script>') + 8;
const code = h.slice(i0, h.indexOf('</script>', i0));
const els = {};
const el = id => els[id] || (els[id] = { id, textContent: '', innerHTML: '', value: '', checked: false, hidden: false, disabled: false,
  style: {}, dataset: {}, classList: { toggle() {}, add() {}, remove() {} }, setAttribute() {}, addEventListener() {}, files: [] });
const now = Math.floor(Date.now() / 1000);
const state = (o) => Object.assign({ on: true, remain: 1320, total: 1800, src: 'programa', temp: 31, volt: 12.4, flame: 1, pw: 5000, bus: 1, time: now, tv: true,
  auto: true, ph: 2, gas: [0.07, 0.3, 1.2, 4.5], note: '', tg: false, tgchat: '', tgl: '', wm: 1, ble: 0, name: 'WTTC', lang: 'es', apdef: false,
  op: -1, om: '', onew: false, sta: false, ssid: '', ip: '', rssi: 0, tx: 'F4 03 44 21 00', rx: '4F 03 C4 00 88',
  sch: [[1, 31, 420, 30, 0], [1, 96, 480, 120, 148], [0, 127, 600, 60, 20]], log: ['06/10 13:27  Encendida (programa, 30 min)'],
  ct: 6.9, ch: 63, tgt: 20, tun: 5400, dep: now + 3600 * 15, dept: 21, wa: true }, o);
let cur = state({});
const sb = { document: { getElementById: el, querySelectorAll: () => [], documentElement: {}, hidden: false, body: el('body') }, navigator: { language: 'es-ES' },
  setTimeout: () => {}, setInterval: () => {}, alert: m => { throw new Error('alert: ' + m) }, confirm: () => false, URLSearchParams, Date, Math, JSON,
  fetch: async (p) => ({ ok: true, text: async () => JSON.stringify(p === '/api/cfg' ? { name: 'WTTC', pin: 123456, wifimode: 1, ssid: '', tgchat: '', minvolt: '12.0',
    lang: cur.lang, ver: '0.2.1', bonds: 1, tg: false, th: 1, oled: 0, disp: 1, led: 1, toff: '0.0', warm: 50, sens: 'SHT31', scr: false } : cur) }),
  console, window: {} };
sb.window.parent = sb.window;
vm.createContext(sb);
vm.runInContext(code + '\n;globalThis.__w={render,list,next,loadCfg,setLang,get st(){return st},set st(v){st=v}};', sb);
const W = sb.__w;
for (const lang of ['es', 'en', 'de']) {
  cur = state({ lang }); W.st = cur; W.render();
  console.log(`[${lang}] estado: ${el('st').textContent} | ${el('rem').textContent}`);
  console.log(`[${lang}] dentro: ${el('cab').innerHTML.replace(/<[^>]+>/g, '')} | salida: ${el('depSt').textContent}`);
  const L = el('list').innerHTML;
  console.log(`[${lang}] programas: ${(L.match(/class="row"/g) || []).length} filas, días ${(L.match(/data-k="day"/g) || []).length}, selects ${(L.match(/data-k="(mode|tg)"/g) || []).length} | ${el('next').textContent}`);
  assert.ok(el('st').textContent.length > 2, 'estado de una letra: ¿T() con una lista?');
  assert.strictEqual((L.match(/data-k="day"/g) || []).length, 21, 'faltan los días de los programas');
  assert.strictEqual((L.match(/data-k="(mode|tg)"/g) || []).length, 6, 'faltan las opciones de los programas');
  assert.ok(/\d/.test(el('cab').innerHTML) && el('depSt').textContent.length > 10, 'falta la temperatura de dentro o la salida');
  cur = state({ lang, on: false, ph: 0, tgt: 0, ct: null, ch: null, dep: 0 }); W.st = cur; W.render();
  console.log(`[${lang}] apagada sin termómetro: ${el('st').textContent} | Hasta oculto=${el('tgtRow').hidden} dentro oculto=${el('cab').hidden} | botón: ${el('big').textContent}`);
  assert.ok(el('tgtRow').hidden && el('cab').hidden, 'sin termómetro no debe ofrecerse «Hasta X °C»');
}
(async () => {
  await W.loadCfg();
  console.log('config: hwBox oculto=' + el('hwBox').hidden, '| pantalla oculta=' + el('lb_oled').hidden, '| corrección oculta=' + el('lb_toff').hidden, '|', el('hw').textContent);
  assert.ok(!el('hwBox').hidden && el('lb_oled').hidden && !el('lb_toff').hidden, 'ajustes de piezas opcionales mal ocultados');
  console.log('Web de la placa: OK');
})().catch(e => { console.error(e); process.exit(1); });
