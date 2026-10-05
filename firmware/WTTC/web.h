// ============================================================================================================
// web.h — Página web que sirve el ESP32 de WTTC (http://192.168.4.1 o http://wttc.local)
// Código generado íntegramente con Claude (Anthropic).
//
// Va en un .h aparte porque el preprocesador de Arduino solo analiza los .ino y confundía las funciones
// del JavaScript con funciones de C++ (generaba prototipos imposibles y no compilaba).
//
// Habla español, inglés o alemán: el idioma de la placa (ajuste «lang»); hasta conocerlo, el del navegador.
// Los textos fijos del HTML llevan data-t con su clave en I18N (dentro del JavaScript).
//
// Es una sola página (HTML + CSS + JavaScript, sin librerías) guardada en la flash (PROGMEM) como cadena
// literal «raw» de C++ (delimitador HTML): todo su contenido se envía tal cual al navegador.
// La misma página se usa en el simulador de https://wttc.favala.es (con fetch() desviado al ESP32 simulado).
// Los comentarios HTML y JavaScript de dentro también viajan al navegador: ocupan poco y ayudan a entenderla.
// ============================================================================================================
#pragma once
#include <Arduino.h>

static const char INDEX_HTML[] PROGMEM = R"HTML(<!doctype html><html lang="es"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes"><meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="WTTC">
<title>WTTC</title>
<style>
/* Colores en variables: tema oscuro por defecto y claro si el móvil está en modo claro */
:root{--bg:#0F1A2A;--sf:#172437;--sf2:#22344D;--ink:#E8EEF6;--mut:#8C9BB0;--fl:#FF9F1C;--ice:#5BC0EB;--ok:#3ECF8E;--bad:#FF6B6B;color-scheme:dark}
@media (prefers-color-scheme:light){:root{--bg:#EEF2F7;--sf:#FFFFFF;--sf2:#E1E8F0;--ink:#16233A;--mut:#5B6B82;--fl:#E08600;--ice:#2A8BC0;--ok:#1FA971;--bad:#D64545;color-scheme:light}}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.45 -apple-system,BlinkMacSystemFont,system-ui,sans-serif}
body{padding:calc(env(safe-area-inset-top,0px) + 12px) 16px calc(env(safe-area-inset-bottom,0px) + 28px)}
main{max-width:460px;margin:0 auto}
header{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:2px 12px;color:var(--mut);font-size:14px}
#clock{text-align:right}
header b{color:var(--ink);font-size:17px}
#net{width:9px;height:9px;border-radius:50%;background:var(--ok);display:inline-block;margin-right:7px}
#net.bad{background:var(--bad)}
.dial{position:relative;width:260px;height:260px;margin:18px auto 6px}
.dial svg{width:100%;height:100%;transform:rotate(-90deg)}
.dial circle{fill:none;stroke-width:12;stroke-linecap:round}
.trk{stroke:var(--sf2)}.arc{stroke:var(--ice);transition:stroke-dashoffset .6s,stroke .3s}
.on .arc{stroke:var(--fl)}
.ctr{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.tmp{font-size:70px;font-weight:200;letter-spacing:-2px;font-variant-numeric:tabular-nums;line-height:1}
.lbl{color:var(--mut);font-size:13px;margin-top:4px}
.st{font-size:20px;font-weight:600;margin-top:12px}
.on .st{color:var(--fl)}
.stats{display:flex;justify-content:center;gap:22px;color:var(--mut);font-size:14px;font-variant-numeric:tabular-nums;margin-bottom:18px}
.stats b{color:var(--ink);font-weight:600}
.gas{text-align:center;color:var(--mut);font-size:13px;margin:-10px 0 18px}
.seg{display:flex;background:var(--sf);border-radius:12px;padding:4px;gap:4px}
.seg button{flex:1;border:0;background:none;color:var(--mut);padding:9px 0;border-radius:9px;font:inherit;font-size:15px}
.seg button.sel{background:var(--sf2);color:var(--ink);font-weight:600}
.big{width:100%;margin-top:12px;border:0;border-radius:16px;padding:17px;font:inherit;font-size:18px;font-weight:600;background:var(--fl);color:#1A1000}
.on .big{background:var(--sf2);color:var(--ink)}
.big:disabled{opacity:.5}
.warn{background:var(--sf);border-left:3px solid var(--bad);padding:10px 12px;border-radius:8px;font-size:14px;margin:12px 0}
h2{font-size:20px;margin:32px 0 2px}
.sub{color:var(--mut);font-size:14px;margin:0 0 12px}
.row{background:var(--sf);border-radius:14px;padding:12px;margin-bottom:10px}
.rtop{display:flex;align-items:center;gap:10px}
.rtop input[type=time]{font:inherit;font-size:24px;font-weight:600;background:none;border:0;color:var(--ink);font-variant-numeric:tabular-nums;padding:0;min-width:0}
.rtop select{font:inherit;font-size:15px;background:var(--sf2);color:var(--ink);border:0;border-radius:8px;padding:6px 8px}
.x{margin-left:auto;background:none;border:0;color:var(--mut);font-size:24px;line-height:1;padding:0 4px}
.days{display:flex;gap:6px;margin-top:10px}
.days button{flex:1;height:36px;border-radius:18px;border:0;background:var(--sf2);color:var(--mut);font:inherit;font-size:14px;font-weight:600}
.days button.sel{background:var(--ice);color:#04121C}
.sw{position:relative;width:46px;height:28px;flex:none;display:inline-block}
.sw input{opacity:0;width:0;height:0;position:absolute}
.sw i{position:absolute;inset:0;background:var(--sf2);border-radius:14px;transition:.2s}
.sw i:before{content:"";position:absolute;width:22px;height:22px;left:3px;top:3px;border-radius:50%;background:#fff;transition:.2s}
.sw input:checked+i{background:var(--ok)}
.sw input:checked+i:before{transform:translateX(18px)}
.sw input:focus-visible+i{outline:2px solid var(--ice);outline-offset:2px}
.line{display:flex;align-items:center;justify-content:space-between;background:var(--sf);border-radius:14px;padding:12px;margin-bottom:10px}
.btn{border:0;border-radius:12px;padding:12px 14px;font:inherit;font-weight:600;background:var(--sf);color:var(--ink)}
.btn.pri{background:var(--ice);color:#04121C}.btn:disabled{opacity:.45}
.acts{display:flex;gap:10px}.acts .btn{flex:1}
details{background:var(--sf);border-radius:14px;padding:12px;margin-top:10px}
details .btn{background:var(--sf2)}
summary{font-weight:600;cursor:pointer}
pre{white-space:pre-wrap;word-break:break-word;font-size:12px;color:var(--mut);margin:10px 0 0}
label.f{display:block;font-size:13px;color:var(--mut);margin-top:10px}
label.f input,label.f select{display:block;width:100%;margin-top:4px;font:inherit;padding:10px;border-radius:10px;border:0;background:var(--sf2);color:var(--ink)}
details h3{font-size:15px;margin:18px 0 0}
:focus-visible{outline:2px solid var(--ice);outline-offset:2px}
@media (prefers-reduced-motion:reduce){*{transition:none!important}}
</style></head><body><main>
<!-- Cabecera: punto de conexión (verde = la web llega a la placa, rojo = no), nombre de la placa y su fecha y hora -->
<header><span><span id="net"></span><b id="hname">WTTC</b></span><span id="clock">--:--</span></header>
<!-- Esfera: temperatura del agua, estado real y tiempo restante (el arco naranja se vacía según pasa el tiempo) -->
<div class="dial"><svg viewBox="0 0 200 200"><circle class="trk" cx="100" cy="100" r="88"/><circle class="arc" id="arc" cx="100" cy="100" r="88" stroke-dasharray="553" stroke-dashoffset="553"/></svg>
<div class="ctr"><div class="tmp" id="tmp">--°</div><div class="lbl" data-t="water">agua del motor</div><div class="st" id="st" data-t="connecting">Conectando…</div><div class="lbl" id="rem"></div></div></div>
<!-- Datos que da la Webasto (batería, llama, potencia) y debajo el gasoil estimado -->
<div class="stats"><span><span data-t="battery">Batería</span> <b id="volt">--</b></span><span><span data-t="flame">Llama</span> <b id="flame">--</b></span><span><span data-t="power">Potencia</span> <b id="pw">--</b></span></div>
<div class="gas" id="gas"></div>
<!-- Avisos: se apagó sola (con sus averías), el W-Bus no responde, la placa no está en hora -->
<div id="warn"></div>
<!-- Duración elegida (15–60 min) y botón grande de encender / apagar -->
<div class="seg" id="seg"></div>
<button class="big" id="big" disabled data-t="turnOn">Encender</button>

<!-- Programas semanales: hora, duración, días y activo; se guardan en la placa con «Guardar programas» -->
<h2 data-t="schedules">Programas</h2><p class="sub" id="next" data-t="noSched">Sin programas.</p>
<div class="line"><span data-t="schedOn">Programas activos</span><label class="sw"><input type="checkbox" id="auto" aria-label="Programas activos" data-ta="schedOn"><i></i></label></div>
<div id="list"></div>
<div class="acts"><button class="btn" id="add" data-t="addSched">Añadir programa</button><button class="btn pri" id="save" disabled data-t="saveSched">Guardar programas</button></div>

<!-- Diagnóstico: averías guardadas en la Webasto, gasoil a cero, últimas tramas del bus y registro de eventos -->
<details><summary data-t="diag">Diagnóstico</summary>
<div class="acts" style="margin-top:10px"><button class="btn" id="errs" data-t="readFaults">Leer averías</button><button class="btn" id="gasreset" data-t="gasReset">Gasoil a cero</button></div>
<pre id="errout"></pre><pre id="diag"></pre></details>
<!-- Configuración: todo lo que se guarda en la placa (se lee del ESP32 al abrir este apartado) -->
<details id="cfgd"><summary data-t="settings">Configuración</summary>
<p class="sub" id="wifi" style="margin-top:8px"></p>
<label class="f"><span data-t="language">Idioma</span><select id="c_lang"><option value="es">Español</option><option value="en">English</option><option value="de">Deutsch</option></select></label>
<h3 data-t="ownNet">Bluetooth y Wi-Fi propios</h3>
<label class="f"><span data-t="fName">Nombre (red Wi-Fi y Bluetooth)</span><input id="c_name" maxlength="29" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f"><span data-t="fPin">PIN de emparejamiento Bluetooth (6 cifras)</span><input id="c_pin" inputmode="numeric" maxlength="6" autocomplete="off"></label>
<p class="sub" id="c_bonds"></p>
<label class="f"><span data-t="fAp">Clave de la Wi-Fi propia (mínimo 8; vacío: no cambiarla)</span><input id="c_ap" type="password" autocomplete="new-password"></label>
<label class="f"><span data-t="fWm">Wi-Fi propia</span><select id="c_wm"><option value="0" data-t="wm0">Siempre encendida (gasta más)</option><option value="1" data-t="wm1">Solo mientras calienta</option><option value="2" data-t="wm2">Solo a petición (máximo ahorro)</option></select></label>
<p class="sub" data-t="wmHelp">Tras arrancar, la Wi-Fi siempre queda 10 minutos encendida, por si hay que entrar sin Bluetooth. En «solo a petición» se enciende desde la app, y unos minutos para enviar avisos.</p>
<h3 data-t="inet">Red con internet (opcional)</h3>
<label class="f"><span data-t="fSsid">Red Wi-Fi (casa o punto de acceso del móvil)</span><input id="c_ssid" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f"><span data-t="fPass">Contraseña (vacío: no cambiarla)</span><input id="c_pass" type="password" autocomplete="new-password"></label>
<h3 data-t="tg">Avisos por Telegram (opcional)</h3>
<label class="f"><span data-t="fTok">Token del bot (de @BotFather; vacío: no cambiarlo)</span><input id="c_tok" type="password" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f"><span data-t="fChat">Chat ID (vacío: avisos desactivados)</span><input id="c_chat" inputmode="numeric" autocomplete="off"></label>
<p class="sub" id="tgst" style="white-space:pre-line"></p>
<h3 data-t="safety">Seguridad</h3>
<label class="f"><span data-t="fMinV">Batería mínima para arrancar un programa (V)</span><input id="c_mv" type="number" step="0.1" min="10.5" max="13"></label>
<div class="acts" style="margin-top:14px"><button class="btn pri" id="csave" data-t="save">Guardar</button><button class="btn" id="tgtest" data-t="tgTest">Probar Telegram</button></div>
<div class="acts" style="margin-top:10px"><button class="btn" id="forget" data-t="forget">Borrar emparejamientos</button><button class="btn" id="tsync" data-t="setClock">Poner en hora</button></div>
<p class="sub" id="c_ver" style="margin-top:10px"></p></details>
</main>
<script>
// ===== JavaScript de la página. Habla con el ESP32 por su API (/api/...) y repinta cada 3 s. =====
// Atajo para buscar elementos por id
const $=id=>document.getElementById(id);
// ---- Idioma: el de la placa (ajuste «lang»); hasta saberlo, el del navegador. Textos en es, en y de ----
const I18N={
es:{loc:'es-ES',water:'agua del motor',connecting:'Conectando…',battery:'Batería',flame:'Llama',power:'Potencia',turnOn:'Encender',turnOff:'Apagar',
 turningOn:'Encendiendo…',turningOff:'Apagando…',schedules:'Programas',noSched:'Sin programas.',schedOn:'Programas activos',addSched:'Añadir programa',
 saveSched:'Guardar programas',saved:'Guardado',diag:'Diagnóstico',readFaults:'Leer averías',gasReset:'Gasoil a cero',settings:'Configuración',language:'Idioma',
 ownNet:'Bluetooth y Wi-Fi propios',fName:'Nombre (red Wi-Fi y Bluetooth)',fPin:'PIN de emparejamiento Bluetooth (6 cifras)',
 fAp:'Clave de la Wi-Fi propia (mínimo 8; vacío: no cambiarla)',fWm:'Wi-Fi propia',wm0:'Siempre encendida (gasta más)',wm1:'Solo mientras calienta',
 wm2:'Solo a petición (máximo ahorro)',wmHelp:'Tras arrancar, la Wi-Fi siempre queda 10 minutos encendida, por si hay que entrar sin Bluetooth. En «solo a petición» se enciende desde la app, y unos minutos para enviar avisos.',
 inet:'Red con internet (opcional)',fSsid:'Red Wi-Fi (casa o punto de acceso del móvil)',fPass:'Contraseña (vacío: no cambiarla)',tg:'Avisos por Telegram (opcional)',
 fTok:'Token del bot (de @BotFather; vacío: no cambiarlo)',fChat:'Chat ID (vacío: avisos desactivados)',safety:'Seguridad',fMinV:'Batería mínima para arrancar un programa (V)',
 save:'Guardar',tgTest:'Probar Telegram',forget:'Borrar emparejamientos',setClock:'Poner en hora',
 PH:['Apagada','Arrancando…','Calentando','En pausa','Sin respuesta'],heating:'Calentando',off:'Apagada',hotWater:'Agua caliente: vuelve a prender sola. ',
 left:'Quedan {0}',bySched:' (programa)',yes:'sí',no:'no',gas:'Gasoil (estimado): ',gasNow:'{0} en este encendido',gasLast:'último encendido {0}',gasMonth:' · este mes {0}',gasTotal:' · total {0}',
 noClock:'sin hora',busWarn:'La Webasto no responde por W-Bus. Revisa el cable negro, la masa común y el módulo TJA1020.',
 clockWarn:'El reloj no está en hora y los programas no se ejecutarán. Pulsa «Poner en hora».',lastTx:'Último envío: ',lastRx:'Última respuesta: ',
 bleOn:'App conectada por Bluetooth. ',staOn:'Conectado a «{0}» ({1}, {2} dBm). ',staOff:'Sin red con internet. ',ownAp:'Red propia «{0}»: http://192.168.4.1',
 tgOn:'Avisos activados.',tgOff:'Avisos desactivados.',tgNet:' Solo salen si el ESP32 llega a la red con internet.',tgLast:'\nÚltimo aviso: ',
 aTime:'Hora de encendido',aDur:'Duración',aEn:'Programa activo',aDel:'Borrar programa',addHelp:'Añade un programa para que se encienda sola a una hora y unos días concretos.',
 max8:'Máximo 8 programas.',saveFail:'No se pudo guardar: ',noneActive:'Ningún programa activo.',schedOff:'Programas desactivados.',
 next:'Próximo encendido: {0} a las {1}, durante {2}.',today:'hoy',tomorrow:'mañana',reading:'Leyendo…',noAnswer:'La Webasto no respondió.',
 code:'Código 0x{0} ({1} veces)',noFaults:'Sin averías guardadas.',raw:'\nRespuesta: ',error:'Error: ',cfgFail:'No se pudo leer la configuración: ',
 stored:'guardado',notSet:'sin configurar',bonds:'Dispositivos emparejados: {0}.',noBonds:'Ningún dispositivo emparejado todavía.',
 ver:'Firmware WTTC {0} · código generado íntegramente con Claude (Anthropic) · github.com/matatunos/wttc',
 askGas:'¿Poner a cero el gasoil estimado (último encendido, mes y total)?',askForget:'¿Borrar todos los dispositivos emparejados? Habrá que volver a emparejar la app con el PIN.',
 days:['L','M','X','J','V','S','D'],daysL:['el lunes','el martes','el miércoles','el jueves','el viernes','el sábado','el domingo']},
en:{loc:'en-GB',water:'engine coolant',connecting:'Connecting…',battery:'Battery',flame:'Flame',power:'Power',turnOn:'Switch on',turnOff:'Switch off',
 turningOn:'Switching on…',turningOff:'Switching off…',schedules:'Schedules',noSched:'No schedules.',schedOn:'Schedules enabled',addSched:'Add schedule',
 saveSched:'Save schedules',saved:'Saved',diag:'Diagnostics',readFaults:'Read faults',gasReset:'Reset diesel',settings:'Settings',language:'Language',
 ownNet:'Own Bluetooth and Wi-Fi',fName:'Name (Wi-Fi network and Bluetooth)',fPin:'Bluetooth pairing PIN (6 digits)',
 fAp:'Own Wi-Fi password (at least 8; empty: keep it)',fWm:'Own Wi-Fi',wm0:'Always on (uses more power)',wm1:'Only while heating',
 wm2:'Only on request (maximum saving)',wmHelp:'After booting, the Wi-Fi always stays on for 10 minutes, in case you need to get in without Bluetooth. In “only on request” it is switched on from the app, and for a few minutes to send notifications.',
 inet:'Network with internet (optional)',fSsid:'Wi-Fi network (home or phone hotspot)',fPass:'Password (empty: keep it)',tg:'Telegram notifications (optional)',
 fTok:'Bot token (from @BotFather; empty: keep it)',fChat:'Chat ID (empty: notifications off)',safety:'Safety',fMinV:'Minimum battery to start a schedule (V)',
 save:'Save',tgTest:'Test Telegram',forget:'Delete pairings',setClock:'Set clock',
 PH:['Off','Starting…','Heating','Paused','No answer'],heating:'Heating',off:'Off',hotWater:'Water is hot: it will relight by itself. ',
 left:'{0} left',bySched:' (schedule)',yes:'yes',no:'no',gas:'Diesel (estimate): ',gasNow:'{0} this run',gasLast:'last run {0}',gasMonth:' · this month {0}',gasTotal:' · total {0}',
 noClock:'no time',busWarn:'The Webasto does not answer on the W-Bus. Check the black wire, the common ground and the TJA1020 module.',
 clockWarn:'The clock is not set and schedules will not run. Press “Set clock”.',lastTx:'Last sent: ',lastRx:'Last answer: ',
 bleOn:'App connected over Bluetooth. ',staOn:'Connected to “{0}” ({1}, {2} dBm). ',staOff:'No internet network. ',ownAp:'Own network “{0}”: http://192.168.4.1',
 tgOn:'Notifications on.',tgOff:'Notifications off.',tgNet:' They are only sent if the ESP32 reaches the internet network.',tgLast:'\nLast notification: ',
 aTime:'Start time',aDur:'Duration',aEn:'Schedule enabled',aDel:'Delete schedule',addHelp:'Add a schedule to switch it on at a given time and on given days.',
 max8:'At most 8 schedules.',saveFail:'Could not save: ',noneActive:'No schedule enabled.',schedOff:'Schedules disabled.',
 next:'Next start: {0} at {1}, for {2}.',today:'today',tomorrow:'tomorrow',reading:'Reading…',noAnswer:'The Webasto did not answer.',
 code:'Code 0x{0} ({1} times)',noFaults:'No faults stored.',raw:'\nAnswer: ',error:'Error: ',cfgFail:'Could not read the settings: ',
 stored:'stored',notSet:'not set',bonds:'Paired devices: {0}.',noBonds:'No device paired yet.',
 ver:'WTTC firmware {0} · code generated entirely with Claude (Anthropic) · github.com/matatunos/wttc',
 askGas:'Reset the diesel estimate (last run, month and total)?',askForget:'Delete all paired devices? The app will have to be paired again with the PIN.',
 days:['M','T','W','T','F','S','S'],daysL:['on Monday','on Tuesday','on Wednesday','on Thursday','on Friday','on Saturday','on Sunday']},
de:{loc:'de-DE',water:'Kühlwasser',connecting:'Verbinde…',battery:'Batterie',flame:'Flamme',power:'Leistung',turnOn:'Einschalten',turnOff:'Ausschalten',
 turningOn:'Schalte ein…',turningOff:'Schalte aus…',schedules:'Zeitpläne',noSched:'Keine Zeitpläne.',schedOn:'Zeitpläne aktiv',addSched:'Zeitplan hinzufügen',
 saveSched:'Zeitpläne speichern',saved:'Gespeichert',diag:'Diagnose',readFaults:'Fehler auslesen',gasReset:'Diesel zurücksetzen',settings:'Einstellungen',language:'Sprache',
 ownNet:'Eigenes Bluetooth und WLAN',fName:'Name (WLAN und Bluetooth)',fPin:'Bluetooth-Kopplungs-PIN (6 Ziffern)',
 fAp:'Passwort des eigenen WLANs (mind. 8; leer: unverändert)',fWm:'Eigenes WLAN',wm0:'Immer an (verbraucht mehr)',wm1:'Nur während des Heizens',
 wm2:'Nur auf Anfrage (maximal sparsam)',wmHelp:'Nach dem Start bleibt das WLAN immer 10 Minuten an, falls man ohne Bluetooth hinein muss. Bei „nur auf Anfrage“ wird es von der App eingeschaltet, und für ein paar Minuten zum Senden von Benachrichtigungen.',
 inet:'Netzwerk mit Internet (optional)',fSsid:'WLAN (Zuhause oder Handy-Hotspot)',fPass:'Passwort (leer: unverändert)',tg:'Telegram-Benachrichtigungen (optional)',
 fTok:'Bot-Token (von @BotFather; leer: unverändert)',fChat:'Chat-ID (leer: Benachrichtigungen aus)',safety:'Sicherheit',fMinV:'Mindestspannung der Batterie für einen Zeitplan (V)',
 save:'Speichern',tgTest:'Telegram testen',forget:'Kopplungen löschen',setClock:'Uhr stellen',
 PH:['Aus','Startet…','Heizt','Pause','Keine Antwort'],heating:'Heizt',off:'Aus',hotWater:'Wasser ist heiß: zündet von selbst wieder. ',
 left:'noch {0}',bySched:' (Zeitplan)',yes:'ja',no:'nein',gas:'Diesel (geschätzt): ',gasNow:'{0} bei diesem Lauf',gasLast:'letzter Lauf {0}',gasMonth:' · dieser Monat {0}',gasTotal:' · gesamt {0}',
 noClock:'keine Uhrzeit',busWarn:'Webasto antwortet nicht über W-Bus. Schwarzes Kabel, gemeinsame Masse und TJA1020-Modul prüfen.',
 clockWarn:'Die Uhr ist nicht gestellt, Zeitpläne laufen nicht. „Uhr stellen“ drücken.',lastTx:'Zuletzt gesendet: ',lastRx:'Letzte Antwort: ',
 bleOn:'App über Bluetooth verbunden. ',staOn:'Verbunden mit „{0}“ ({1}, {2} dBm). ',staOff:'Kein Netzwerk mit Internet. ',ownAp:'Eigenes Netz „{0}“: http://192.168.4.1',
 tgOn:'Benachrichtigungen an.',tgOff:'Benachrichtigungen aus.',tgNet:' Sie werden nur gesendet, wenn der ESP32 das Internet-Netz erreicht.',tgLast:'\nLetzte Benachrichtigung: ',
 aTime:'Startzeit',aDur:'Dauer',aEn:'Zeitplan aktiv',aDel:'Zeitplan löschen',addHelp:'Einen Zeitplan hinzufügen, damit sie zu einer bestimmten Zeit an bestimmten Tagen startet.',
 max8:'Höchstens 8 Zeitpläne.',saveFail:'Speichern fehlgeschlagen: ',noneActive:'Kein Zeitplan aktiv.',schedOff:'Zeitpläne deaktiviert.',
 next:'Nächster Start: {0} um {1}, für {2}.',today:'heute',tomorrow:'morgen',reading:'Lese…',noAnswer:'Webasto hat nicht geantwortet.',
 code:'Code 0x{0} ({1}-mal)',noFaults:'Keine Fehler gespeichert.',raw:'\nAntwort: ',error:'Fehler: ',cfgFail:'Einstellungen konnten nicht gelesen werden: ',
 stored:'gespeichert',notSet:'nicht eingerichtet',bonds:'Gekoppelte Geräte: {0}.',noBonds:'Noch kein Gerät gekoppelt.',
 ver:'WTTC-Firmware {0} · Code vollständig mit Claude (Anthropic) erzeugt · github.com/matatunos/wttc',
 askGas:'Dieselschätzung (letzter Lauf, Monat und gesamt) zurücksetzen?',askForget:'Alle gekoppelten Geräte löschen? Die App muss dann erneut mit der PIN gekoppelt werden.',
 days:['M','D','M','D','F','S','S'],daysL:['am Montag','am Dienstag','am Mittwoch','am Donnerstag','am Freitag','am Samstag','am Sonntag']}};
let lang=(navigator.language||'').slice(0,2);if(!I18N[lang])lang='en';
// Texto traducido; {0}, {1}… se sustituyen por los datos
const T=(k,...a)=>{const v=I18N[lang][k];return String(v!==undefined?v:I18N.es[k]).replace(/\{(\d)\}/g,(m,i)=>a[i])};
// Cambia el idioma y repinta los textos fijos (data-t) y las etiquetas para lectores de pantalla (data-ta)
function setLang(l){if(!I18N[l])return;lang=l;document.documentElement.lang=l;
 document.querySelectorAll('[data-t]').forEach(e=>e.textContent=T(e.dataset.t));
 document.querySelectorAll('[data-ta]').forEach(e=>e.setAttribute('aria-label',T(e.dataset.ta)));seg()}
// Duraciones del botón principal y de los programas (el firmware limita a 60 min)
const DURS=[15,30,45,60],SDURS=[15,30,45,60];
// Estado: st = último /api/state; sched = programas en edición; dirty = cambios sin guardar;
// dur = duración elegida; synced = ya se puso en hora; busy = esperando la respuesta de encender/apagar
let st=null,sched=[],dirty=false,dur=30,synced=false,busy=false;
// Escapa texto para meterlo en HTML sin riesgo
const esc=s=>String(s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
// Dos cifras: 7 -> "07"
const pad=n=>String(n).padStart(2,'0');
// Fecha completa en el idioma elegido para la cabecera: «domingo, 4 de octubre de 2026 · 20:51»
const fecha=d=>{const f=d.toLocaleDateString(T('loc'),{weekday:'long',day:'numeric',month:'long',year:'numeric'});return f[0].toUpperCase()+f.slice(1)+' · '+pad(d.getHours())+':'+pad(d.getMinutes())};
// Minutos del día -> "07:30"
const hm=m=>pad(Math.floor(m/60))+':'+pad(m%60);
// Duración en minutos -> "30 min" / "1 h"
const fmtDur=d=>d<60?d+' min':Math.floor(d/60)+' h'+(d%60?' '+d%60:'');
// Segundos restantes -> "25 min" / "1 h 05 min"
const fmtRem=s=>{const m=Math.ceil(s/60);return m<60?m+' min':Math.floor(m/60)+' h '+pad(m%60)+' min'};
// Número con los decimales que se pidan y la coma o el punto del idioma
const num=(v,d)=>(+v).toLocaleString(T('loc'),{minimumFractionDigits:d,maximumFractionDigits:d});
// Llamada a la API del ESP32: GET sin cuerpo o POST con formulario; devuelve JSON si lo es, o el texto.
// Si la respuesta no es 2xx lanza un error con el texto que da el firmware (se muestra con alert).
async function api(p,b){const r=await fetch(p,b?{method:'POST',body:new URLSearchParams(b)}:undefined);const t=await r.text();if(!r.ok)throw new Error(t||r.status);try{return JSON.parse(t)}catch(e){return t}}
// Pinta los botones de duración
function seg(){$('seg').innerHTML=DURS.map(d=>`<button class="${d==dur?'sel':''}" data-d="${d}">${fmtDur(d)}</button>`).join('')}
// Elegir duración
$('seg').onclick=e=>{const d=e.target.dataset.d;if(d){dur=+d;seg();render()}};
// Botón grande: enciende con la duración elegida o apaga
$('big').onclick=async()=>{if(!st)return;busy=true;$('big').disabled=true;$('big').textContent=T(st.on?'turningOff':'turningOn');
 try{await api(st.on?'/api/off':'/api/on',st.on?{}:{min:dur})}catch(e){alert(e.message)}busy=false;poll()};
// Repinta toda la página con el último estado
function render(){if(!st)return;
 if(st.lang&&st.lang!=lang)setLang(st.lang);
 document.body.classList.toggle('on',st.on);$('hname').textContent=st.name;
 $('tmp').textContent=st.temp>-100?st.temp+'°':'--°';
 $('st').textContent=st.on?(T('PH')[st.ph]||T('heating')):T('off');
 $('rem').textContent=st.on?(st.ph==3?T('hotWater'):'')+T('left',fmtRem(st.remain))+(st.src=='programa'?T('bySched'):''):'';
 $('arc').style.strokeDashoffset=553*(1-(st.on&&st.total?st.remain/st.total:0));
 $('volt').textContent=st.volt>0?num(st.volt,1)+' V':'--';
 $('flame').textContent=st.flame<0?'--':T(st.flame?'yes':'no');
 $('pw').textContent=st.pw<0?'--':st.pw+' W';
 if(st.gas){const L=v=>num(v,v<10?2:1)+' l';
  $('gas').textContent=T('gas')+(st.on?T('gasNow',L(st.gas[0])):T('gasLast',L(st.gas[1])))+T('gasMonth',L(st.gas[2]))+T('gasTotal',L(st.gas[3]))}
 $('clock').textContent=st.tv?fecha(new Date(st.time*1000)):T('noClock');
 let w='';
 if(!st.on&&st.note)w+='<div class="warn">'+esc(st.note)+'</div>';
 if(st.bus===0)w+='<div class="warn">'+T('busWarn')+'</div>';
 if(!st.tv)w+='<div class="warn">'+T('clockWarn')+'</div>';
 $('warn').innerHTML=w;
 if(!busy){$('big').disabled=false;$('big').textContent=st.on?T('turnOff'):T('turnOn')+' '+fmtDur(dur)}
 if(!dirty){sched=st.sch.map(a=>({en:!!a[0],days:a[1],start:a[2],dur:a[3]}));$('auto').checked=st.auto;list()}
 $('diag').textContent=T('lastTx')+(st.tx||'-')+'\n'+T('lastRx')+(st.rx||'-')+'\n\n'+st.log.join('\n');
 $('wifi').textContent=(st.ble?T('bleOn'):'')+(st.sta?T('staOn',st.ssid,st.ip,st.rssi):T('staOff'))+T('ownAp',st.name);
 $('tgst').textContent=T(st.tg?'tgOn':'tgOff')+T('tgNet')+(st.tgl?T('tgLast')+st.tgl:'');
 next()}
// Pinta la lista de programas (hora, duración, interruptor, borrar y días)
function list(){$('list').innerHTML=sched.map((s,i)=>`<div class="row"><div class="rtop">
<input type="time" value="${hm(s.start)}" data-i="${i}" data-k="start" aria-label="${T('aTime')}">
<select data-i="${i}" data-k="dur" aria-label="${T('aDur')}">${SDURS.map(d=>`<option value="${d}"${d==s.dur?' selected':''}>${fmtDur(d)}</option>`).join('')}</select>
<label class="sw"><input type="checkbox"${s.en?' checked':''} data-i="${i}" data-k="en" aria-label="${T('aEn')}"><i></i></label>
<button class="x" data-i="${i}" data-k="del" aria-label="${T('aDel')}">×</button></div>
<div class="days">${T('days').map((d,j)=>`<button class="${s.days>>j&1?'sel':''}" data-i="${i}" data-k="day" data-j="${j}">${d}</button>`).join('')}</div></div>`).join('')
 ||'<p class="sub">'+T('addHelp')+'</p>';
 $('save').disabled=!dirty}
// Marca que hay cambios sin guardar en los programas
function touch(re=true){dirty=true;if(re)list();$('save').disabled=false;next()}
// Clics en la lista: borrar un programa o cambiar un día
$('list').addEventListener('click',e=>{const t=e.target.closest('button[data-k]');if(!t)return;const i=+t.dataset.i;
 if(t.dataset.k=='del'){sched.splice(i,1);touch()}else if(t.dataset.k=='day'){sched[i].days^=1<<+t.dataset.j;touch()}});
// Cambios en la lista: hora, duración o activo
$('list').addEventListener('change',e=>{const t=e.target,i=+t.dataset.i,k=t.dataset.k;
 if(k=='start'&&t.value){const p=t.value.split(':');sched[i].start=+p[0]*60+ +p[1]}else if(k=='dur')sched[i].dur=+t.value;else if(k=='en')sched[i].en=t.checked;touch(false)});
// Añadir un programa (por defecto: 07:00, 30 min, de lunes a viernes)
$('add').onclick=()=>{if(sched.length>=8)return alert(T('max8'));sched.push({en:true,days:31,start:420,dur:30});touch()};
// Interruptor general de los programas
$('auto').onchange=()=>touch(false);
// Guardar programas en la placa (formato "activo,días,inicio,duración;…")
$('save').onclick=async()=>{try{await api('/api/sched',{auto:$('auto').checked?1:0,list:sched.map(s=>[s.en?1:0,s.days,s.start,s.dur].join(',')).join(';')});
 dirty=false;$('save').disabled=true;$('save').textContent=T('saved');setTimeout(()=>$('save').textContent=T('saveSched'),1500);poll()}catch(e){alert(T('saveFail')+e.message)}};
// Calcula y muestra el próximo encendido programado
function next(){if(!st)return;const act=sched.filter(s=>s.en&&s.days);
 if(!act.length){$('next').textContent=T(sched.length?'noneActive':'noSched');return}
 if(!$('auto').checked){$('next').textContent=T('schedOff');return}
 const now=new Date(st.tv?st.time*1000:Date.now()),wd=(now.getDay()+6)%7,m=now.getHours()*60+now.getMinutes();let b=null;
 for(const s of act)for(let k=0;k<8;k++){const d=(wd+k)%7;if(!(s.days>>d&1)||(k==0&&s.start<=m))continue;const t=k*1440+s.start-m;if(!b||t<b.t)b={t,k,d,s};break}
 $('next').textContent=b?T('next',b.k==0?T('today'):b.k==1?T('tomorrow'):T('daysL')[b.d],hm(b.s.start),fmtDur(b.s.dur)):T('noSched')}
// Leer las averías guardadas en la Webasto
$('errs').onclick=async()=>{$('errout').textContent=T('reading');try{const r=await api('/api/errors');
 $('errout').textContent=(!r.ok?T('noAnswer'):r.codes.length?r.codes.map(c=>T('code',c.c,c.n)).join('\n'):T('noFaults'))+T('raw')+r.raw}catch(e){$('errout').textContent=T('error')+e.message}};
// Configuración: lee los ajustes de la placa (las claves no se devuelven nunca: los campos quedan vacíos)
async function loadCfg(){try{const c=await api('/api/cfg');
 $('c_name').value=c.name;$('c_pin').value=c.pin;$('c_wm').value=c.wifimode;$('c_ssid').value=c.ssid;$('c_chat').value=c.tgchat;$('c_mv').value=c.minvolt;$('c_lang').value=c.lang||lang;
 $('c_tok').value='';$('c_ap').value='';$('c_pass').value='';$('c_tok').placeholder=T(c.tg?'stored':'notSet');
 $('c_bonds').textContent=c.bonds?T('bonds',c.bonds):T('noBonds');
 $('c_ver').textContent=T('ver',c.ver)}catch(e){alert(T('cfgFail')+e.message)}}
// Al abrir el apartado Configuración se leen los ajustes
$('cfgd').ontoggle=()=>{if($('cfgd').open)loadCfg()};
// Guardar configuración: las claves vacías no se envían (la placa conserva las que tenía).
// El idioma va primero: así la respuesta de la placa ya sale en el nuevo
$('csave').onclick=async()=>{const b={lang:$('c_lang').value,name:$('c_name').value.trim(),pin:$('c_pin').value.trim(),wifimode:$('c_wm').value,ssid:$('c_ssid').value.trim(),tgchat:$('c_chat').value.trim(),minvolt:$('c_mv').value};
 if($('c_ap').value)b.appass=$('c_ap').value;if($('c_pass').value)b.pass=$('c_pass').value;if($('c_tok').value.trim())b.tgtok=$('c_tok').value.trim();
 try{setLang(b.lang);alert(await api('/api/cfg',b));loadCfg();poll()}catch(e){alert(e.message)}};
// Poner a cero el gasoil estimado
$('gasreset').onclick=async()=>{if(!confirm(T('askGas')))return;try{alert(await api('/api/gasreset',{}));poll()}catch(e){alert(e.message)}};
// Borrar los móviles emparejados por Bluetooth
$('forget').onclick=async()=>{if(!confirm(T('askForget')))return;try{alert(await api('/api/forget',{}));loadCfg()}catch(e){alert(e.message)}};
// Aviso de prueba por Telegram
$('tgtest').onclick=async()=>{try{alert(await api('/api/tgtest',{}))}catch(e){alert(e.message)}};
// Poner la placa en hora con la del móvil
$('tsync').onclick=async()=>{try{await api('/api/time',{epoch:Math.floor(Date.now()/1000)});poll()}catch(e){alert(e.message)}};
// Pide el estado. La primera vez, si la placa no está en hora (o va desfasada), se la pone en hora
async function poll(){try{st=await api('/api/state');$('net').classList.remove('bad');
 if(!synced){synced=true;if(!st.tv||Math.abs(st.time-Date.now()/1000)>30){await api('/api/time',{epoch:Math.floor(Date.now()/1000)});st=await api('/api/state')}}
 render()}catch(e){$('net').classList.add('bad')}}
// Arranque: idioma del navegador hasta conocer el de la placa, botones de duración, primer estado y repetir cada 3 s
setLang(lang);poll();setInterval(()=>{if(!document.hidden)poll()},3000);
</script></body></html>)HTML";
