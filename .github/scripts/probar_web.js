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
  style: {}, dataset: {}, classList: { toggle() {}, add() {}, remove() {} }, setAttribute() {}, removeAttribute() {}, addEventListener() {}, files: [] });
const now = Math.floor(Date.now() / 1000);
const state = (o) => Object.assign({ on: true, remain: 1320, total: 1800, src: 'programa', temp: 31, volt: 12.4, flame: 1, pw: 5000, bus: 1, time: now, tv: true,
  auto: true, ph: 2, gas: [0.07, 0.3, 1.2, 4.5], note: '', tg: false, tgchat: '', tgl: '', wm: 1, ble: 0, name: 'WTTC', lang: 'es', apdef: false,
  op: -1, om: '', onew: false, sta: false, ssid: '', ip: '', rssi: 0, tx: 'F4 03 44 21 00', rx: '4F 03 C4 00 88',
  sch: [[1, 31, 420, 30, 0], [1, 96, 480, 120, 148], [0, 127, 600, 60, 20]], log: ['06/10 13:27  Encendida (programa, 30 min)'],
  ct: 6.9, ch: 63, tgt: 20, tun: 5400, dep: now + 3600 * 15, dept: 21, wa: true }, o);
let cur = state({}), noAuth = false;
const sb = { document: { getElementById: el, querySelectorAll: () => [], documentElement: {}, hidden: false, body: el('body') }, navigator: { language: 'es-ES' },
  setTimeout: () => {}, setInterval: () => {}, alert: m => { throw new Error('alert: ' + m) }, confirm: () => false, URLSearchParams, Date, Math, JSON,
  fetch: async (p, o) => (o && (sb.lastPost = { p, b: String(o.body) }), 0) || noAuth ? { ok: false, status: 401, text: async () => 'login' } : ({ ok: true, status: 200, text: async () => JSON.stringify(p === '/api/cfg' ? { name: 'WTTC', pin: 123456, wifimode: 1, ssid: '', tgchat: '', minvolt: '12.0',
    lang: cur.lang, ver: '0.2.1', bonds: 1, tg: false, th: 1, oled: 0, disp: 1, led: 1, toff: '0.0', warm: 50, sens: 'SHT31', scr: false,
    webuser: 'yo', webdef: false, iid: 'ABCD-2345-EFGH-6789', stats: 1, nruns: 12, rack: 10, stok: 300 } : cur) }),
  console, window: {} };
sb.window.parent = sb.window;
vm.createContext(sb);
vm.runInContext(code + '\n;globalThis.__w={render,list,next,loadCfg,setLang,poll,get st(){return st},set st(v){st=v}};', sb);
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
  console.log('mis estadísticas: oculto=' + el('myBox').hidden, '| enlace ' + el('iidSee').href, '|', el('runsSt').textContent);
  assert.ok(!el('myBox').hidden && el('iidSee').href.endsWith('#ABCD-2345-EFGH-6789') && /12/.test(el('runsSt').textContent), 'falta «Mis estadísticas»');
  // Desde otra red sin sesión, la placa contesta 401: tiene que salir el login
  el('login').hidden = true; noAuth = true;
  await W.poll();
  console.log('401 → login visible=' + !el('login').hidden);
  assert.ok(!el('login').hidden, 'con 401 no sale el login');
  noAuth = false;
  // Con usuario y clave de fábrica desde otra red: aviso para cambiarlos y botón de encender desactivado
  cur = state({ lang: 'es', on: false, wdef: true, lan: true }); W.st = cur; W.render();
  assert.ok(!el('wsetup').hidden && el('big').disabled && !el('logout').hidden, 'falta el aviso de usuario y clave de fábrica');
  // Actualizando (descarga por internet o instalando): una capa tapa toda la página
  el('updLock').hidden = true; cur = state({ lang: 'es', on: false, op: 40 }); W.st = cur; W.render();
  assert.ok(!el('updLock').hidden && /40/.test(el('updLockMsg').textContent), 'descargando: falta el bloqueo de la página');
  el('updLock').hidden = true; cur = state({ lang: 'es', on: false, op: -1, upd: 1 }); W.st = cur; W.render();
  assert.ok(!el('updLock').hidden, 'instalando: falta el bloqueo de la página');
  console.log('bloqueo al actualizar: OK (' + el('updLockMsg').textContent + ')');
  // Modo diagnóstico: apagado, el registro no se ve; encendido, sí
  cur = state({ lang: 'es', on: false, dg: 0 }); W.st = cur; W.render();
  assert.ok(el('diagAdv').hidden && !el('diagOff').hidden, 'sin modo diagnóstico se ve el registro');
  cur = state({ lang: 'es', on: false, dg: 1 }); W.st = cur; W.render();
  assert.ok(!el('diagAdv').hidden && el('diagOff').hidden, 'con modo diagnóstico no se ve el registro');
  console.log('modo diagnóstico: OK');
  // Programador (0.3.6+): «solo si hace frío», saltar la próxima, pausa, solapes, antelación aprendida y lo que se guarda
  cur = state({ lang: 'es', on: false, pause: now + 86400 * 3, dlr: 48, dln: 5,
    sch: [[1, 31, 420, 30, 0, 10, 1], [1, 1, 435, 30, 0, null, 0], [1, 96, 480, 120, 148]] }); W.st = cur; W.render();
  const P = el('list').innerHTML;
  assert.strictEqual((P.match(/data-k="cold"/g) || []).length, 3, 'falta «solo si hace frío»');
  assert.ok(/value="10" selected/.test(P) && /Se saltará la próxima vez/.test(P) && (P.match(/data-k="dup"/g) || []).length == 3, 'frío, saltar o duplicar mal pintados');
  assert.ok(!el('schWarn').hidden && /07:00.*07:15/.test(el('schWarn').textContent), 'no avisa de que 07:00 y 07:15 se pisan el lunes');
  assert.ok(el('pauseD').value.length == 10 && /pausa/.test(el('next').textContent), 'no se ve la pausa');
  assert.ok(/5 encendidos.*0,48/.test(el('depLearn').textContent), 'no se ve la antelación aprendida: ' + el('depLearn').textContent);
  console.log('programador: ' + el('schWarn').textContent + ' | ' + el('next').textContent + ' | ' + el('depLearn').textContent);
  await el('save').onclick();
  const q = new URLSearchParams(sb.lastPost.b);
  assert.ok(sb.lastPost.p == '/api/sched' && q.get('list') == '1,31,420,30,0,10;1,1,435,30,0;1,96,480,120,148' && q.get('skip') == '1' && +q.get('pause') > now,
    'guardar programas manda mal: ' + sb.lastPost.b);
  console.log('programador: guardar OK (' + decodeURIComponent(sb.lastPost.b) + ')');
  console.log('Web de la placa: OK');
})().catch(e => { console.error(e); process.exit(1); });
