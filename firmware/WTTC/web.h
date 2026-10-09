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
.cab{text-align:center;font-size:17px;margin:-8px 0 16px}.cab b{font-weight:600}
.line select,.line input[type=time]{font:inherit;font-size:15px;background:var(--sf2);color:var(--ink);border:0;border-radius:8px;padding:6px 8px}
.dlg{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;padding:16px;z-index:9}
.dlg[hidden]{display:none}.dlg>div{background:var(--sf);border-radius:16px;padding:18px;max-width:420px;width:100%}
.dlg p{white-space:pre-line;margin:0 0 14px}.dlg .btn{text-transform:capitalize}
progress{width:100%;height:12px;margin-top:10px;accent-color:var(--fl)}progress[hidden]{display:none}
.nets{display:flex;flex-direction:column;gap:6px;margin-top:8px}.nets button{display:flex;justify-content:space-between;gap:10px;text-align:left;border:0;border-radius:10px;padding:10px 12px;font:inherit;font-size:15px;background:var(--sf2);color:var(--ink)}.nets small{color:var(--mut);white-space:nowrap}
.opts{display:flex;gap:8px;margin-top:10px}.opts select{flex:1;min-width:0;font:inherit;font-size:14px;background:var(--sf2);color:var(--ink);border:0;border-radius:8px;padding:6px 8px}
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
<!-- Primer uso: con la clave de fábrica de la Wi-Fi («calefaccion», pública) la placa no admite órdenes desde la web
     hasta que se elige una clave nueva. Solo se ve en ese caso (st.apdef) -->
<div class="warn" id="setup" hidden><b data-t="setupTitle">Primer uso: protege la Wi-Fi de la placa</b>
<p class="sub" style="margin:6px 0 0" data-t="setupText">La clave de fábrica es pública: cualquiera cerca podría manejar la calefacción. Elige una nueva (mínimo 8 caracteres); la placa se reinicia y tendrás que volver a conectarte a su Wi-Fi con la clave nueva.</p>
<label class="f"><span data-t="setupLabel">Clave nueva de la Wi-Fi</span><input id="s_ap" type="password" autocomplete="new-password" minlength="8" maxlength="63"></label>
<div class="acts" style="margin-top:10px"><button class="btn pri" id="s_save" data-t="save">Guardar</button></div></div>
<!-- Desde otra red con el usuario y la clave de la web de fábrica (wttc / wttc): obliga a cambiarlos (st.wdef) -->
<div class="warn" id="wsetup" hidden><b data-t="wsetupT">Protege el acceso desde esta red</b>
<p class="sub" style="margin:6px 0 0" data-t="wsetupH"></p>
<label class="f"><span data-t="fWuser">Usuario de la web</span><input id="ws_user" autocomplete="username" autocapitalize="off" autocorrect="off" maxlength="23"></label>
<label class="f"><span data-t="wsetupNew">Clave nueva de la web</span><input id="ws_pass" type="password" autocomplete="new-password" minlength="6" maxlength="63"></label>
<div class="acts" style="margin-top:10px"><button class="btn pri" id="ws_save" data-t="save">Guardar</button></div></div>
<!-- Esfera: temperatura del agua, estado real y tiempo restante (el arco naranja se vacía según pasa el tiempo) -->
<div class="dial"><svg viewBox="0 0 200 200"><circle class="trk" cx="100" cy="100" r="88"/><circle class="arc" id="arc" cx="100" cy="100" r="88" stroke-dasharray="553" stroke-dashoffset="553"/></svg>
<div class="ctr"><div class="tmp" id="tmp">--°</div><div class="lbl" data-t="water">agua del motor</div><div class="st" id="st" data-t="connecting">Conectando…</div><div class="lbl" id="rem"></div></div></div>
<!-- Datos que da la Webasto (batería, llama, potencia) y debajo el gasoil estimado -->
<div class="stats"><span><span data-t="battery">Batería</span> <b id="volt">--</b></span><span><span data-t="flame">Llama</span> <b id="flame">--</b></span><span><span data-t="power">Potencia</span> <b id="pw">--</b></span></div>
<div class="gas" id="gas"></div>
<!-- Temperatura y humedad de dentro: solo con el termómetro opcional (SHT31 o AHT20) -->
<div class="cab" id="cab" hidden></div>
<!-- Avisos: se apagó sola (con sus averías), el W-Bus no responde, la placa no está en hora -->
<div id="warn"></div>
<!-- Duración elegida (15–60 min) y botón grande de encender / apagar -->
<div class="seg" id="seg"></div>
<!-- Con termómetro: «calentar hasta X °C» (termostato). La duración pasa a ser el tiempo máximo -->
<div class="line" id="tgtRow" style="margin-top:12px" hidden><span data-t="tgtL">Hasta</span><select id="tgt" aria-label="Hasta" data-ta="tgtL"></select></div>
<button class="big" id="big" disabled data-t="turnOn">Encender</button>
<!-- Salida suelta: «salgo a las 8:00»; la placa decide cuánto antes encender según el frío que haga -->
<div class="row" style="margin-top:12px"><div class="rtop"><span data-t="depL">Salgo a las</span><input type="time" id="depT" value="08:00" aria-label="Salgo a las" data-ta="depL" style="font-size:20px"><button class="btn" id="depGo" style="margin-left:auto;background:var(--sf2)" data-t="depGo">Programar</button></div>
<p class="sub" id="depSt" style="margin:8px 0 0"></p></div>

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
<!-- Buscar redes cercanas: tocar una rellena el nombre (la placa tarda unos segundos en buscar) -->
<div class="acts" style="margin-top:8px"><button class="btn" id="scanBtn" style="background:var(--sf2)" data-t="scanBtn">Buscar redes</button></div>
<div class="nets" id="scanList"></div>
<label class="f"><span data-t="fPass">Contraseña (vacío: no cambiarla)</span><input id="c_pass" type="password" autocomplete="new-password"></label>
<h3 data-t="tg">Avisos por Telegram (opcional)</h3>
<label class="f"><span data-t="fTok">Token del bot (de @BotFather; vacío: no cambiarlo)</span><input id="c_tok" type="password" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f"><span data-t="fChat">Chat ID (vacío: avisos desactivados)</span><input id="c_chat" inputmode="numeric" autocomplete="off"></label>
<p class="sub" id="tgst" style="white-space:pre-line"></p>
<h3 data-t="webT">Acceso a la web desde otra red</h3>
<p class="sub" data-t="webH"></p>
<label class="f"><span data-t="fWuser">Usuario de la web</span><input id="c_wuser" maxlength="23" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f"><span data-t="fWpass2">Clave de la web</span><input id="c_wpass" type="password" autocomplete="new-password"></label>
<h3 data-t="safety">Seguridad</h3>
<label class="f"><span data-t="fMinV">Batería mínima para arrancar un programa (V)</span><input id="c_mv" type="number" step="0.1" min="10.5" max="13"></label>
<div id="hwBox" hidden>
<p class="sub" data-t="minvHelp">Calentando, se apaga sola si la batería baja medio voltio por debajo de esta.</p>
<label class="f"><span data-t="fWarm">Avisar cuando el agua llegue a (°C; 0 = no avisar)</span><input id="c_warm" type="number" step="1" min="0" max="80"></label>
<h3 data-t="hwT">Pantalla, LED y termómetro (opcionales)</h3>
<p class="sub" id="hw"></p>
<!-- Cada ajuste solo aparece si su pieza está conectada (la placa la detecta) -->
<label class="f" id="lb_oled"><span data-t="fOled">Tipo de pantalla</span><select id="c_oled"><option value="0" data-t="oled0">OLED 1,3" (SH1106)</option><option value="1" data-t="oled1">OLED 0,96" (SSD1306)</option><option value="2" data-t="oled2">OLED 1,5" grises (SSD1327)</option></select></label>
<label class="f" id="lb_disp"><span data-t="fDisp">Pantalla</span><select id="c_disp"><option value="0" data-t="disp0">Apagada (solo con el botón BOOT)</option><option value="1" data-t="disp1">Automática</option><option value="2" data-t="disp2">Siempre encendida</option></select></label>
<label class="f"><span data-t="fLed">LED de la placa</span><select id="c_led"><option value="0" data-t="led0">Apagado</option><option value="1" data-t="led1">Bajo</option><option value="2" data-t="led2">Medio</option><option value="3" data-t="led3">Alto</option></select></label>
<label class="f" id="lb_toff"><span data-t="fToff">Corrección del termómetro (°C)</span><input id="c_toff" type="number" step="0.1" min="-5" max="5"></label></div>
<div class="acts" style="margin-top:14px"><button class="btn pri" id="csave" data-t="save">Guardar</button><button class="btn" id="tgtest" data-t="tgTest">Probar Telegram</button></div>
<div class="acts" style="margin-top:10px"><button class="btn" id="forget" data-t="forget">Borrar emparejamientos</button><button class="btn" id="tsync" data-t="setClock">Poner en hora</button><button class="btn" id="logout" data-t="logout" hidden>Salir</button></div>
<!-- Mis estadísticas: código de instalación (para verlas en wttc.favala.es/mi.php) y si se envían -->
<div id="myBox" hidden><h3 data-t="myT">Mis estadísticas</h3>
<p class="sub" data-t="myH"></p>
<label class="f"><span data-t="fIid">Código de instalación</span><input id="c_iid" maxlength="19" autocomplete="off" autocapitalize="characters" autocorrect="off" spellcheck="false" style="font-family:monospace;letter-spacing:1px"></label>
<div class="line" style="margin-top:10px"><span data-t="fStats">Enviar las estadísticas de esta placa</span><label class="sw"><input type="checkbox" id="c_stats" aria-label="Enviar las estadísticas de esta placa" data-ta="fStats"><i></i></label></div>
<p class="sub" id="runsSt"></p>
<div class="acts" style="margin-top:8px"><button class="btn" id="iidTg" data-t="iidTg">Enviar el código por Telegram</button><a class="btn" id="iidSee" target="_blank" rel="noopener" data-t="iidSee" style="text-decoration:none;text-align:center">Ver mis estadísticas</a></div></div>
<h3 data-t="updTitle">Actualizar firmware</h3>
<p class="sub" data-t="updHelp2">Con la placa unida a una red con internet, «Buscar actualizaciones» lo hace todo: busca, te pregunta y la descarga e instala sola.</p>
<label class="f"><span data-t="fOtaAuto">Actualizaciones automáticas (al arrancar y una vez al día, con red con internet)</span><select id="c_otaa"><option value="0" data-t="otaa0">No buscar</option><option value="1" data-t="otaa1">Buscar y avisar</option><option value="2" data-t="otaa2">Buscar e instalar sola (nunca calentando)</option></select></label>
<p class="sub" data-t="updHelp">Descarga el fichero .ota de la última versión (github.com/matatunos/wttc/releases) y súbelo aquí. Solo se instalan actualizaciones oficiales (con firma) y nunca mientras calienta; si la nueva no arranca bien, la placa vuelve sola a la anterior.</p>
<label class="f"><span data-t="updFile">Fichero .ota</span><input id="u_file" type="file" accept=".ota"></label>
<div class="acts" style="margin-top:10px"><button class="btn" id="u_go" data-t="updBtn">Actualizar</button><button class="btn pri" id="u_check" data-t="updCheck">Buscar actualizaciones</button></div>
<progress id="u_bar" max="100" value="0" hidden></progress>
<p class="sub" id="u_st" style="margin-top:8px"></p>
<p class="sub" id="c_ver" style="margin-top:10px"></p></details>
<!-- Avisos y preguntas dentro de la página: en la ventanita del portal cautivo (Android, iPhone) alert() y confirm()
     del navegador no salen, y la página parecía no hacer nada -->
<!-- Login: desde otra red (casa, camping…) la placa pide usuario y clave (401); por su propia Wi-Fi no -->
<div class="dlg" id="login" hidden role="dialog" aria-modal="true"><div><b data-t="loginT">Entrar en la placa</b>
<p class="sub" style="margin:6px 0 0;white-space:normal" data-t="loginH"></p>
<form id="lgForm"><label class="f"><span data-t="fUser">Usuario</span><input id="lg_user" autocomplete="username" autocapitalize="off" autocorrect="off" value=""></label>
<label class="f"><span data-t="fWpass">Clave</span><input id="lg_pass" type="password" autocomplete="current-password"></label>
<p class="sub" id="lg_err" style="color:var(--bad,#e5484d)"></p>
<div class="acts"><button class="btn pri" id="lg_go" data-t="loginGo">Entrar</button></div></form></div></div>
<div class="dlg" id="dlg" hidden role="dialog" aria-modal="true"><div><p id="dlgT"></p>
<div class="acts"><button class="btn" id="dlgNo">no</button><button class="btn pri" id="dlgOk">OK</button></div></div></div>
</main>
<script>
// ===== JavaScript de la página. Habla con el ESP32 por su API (/api/...) y repinta cada 3 s. =====
// Atajo para buscar elementos por id
const $=id=>document.getElementById(id);
// Si algo falla en la página, se dice (antes se quedaba callada y los botones no hacían nada)
window.onerror=(m,src,l)=>{const w=$('warn');if(w)w.insertAdjacentHTML('afterbegin','<div class="warn">⚠️ '+String(m).replace(/[&<>]/g,'')+' ('+l+')</div>')};
// Avisos (say) y preguntas sí/no (ask, devuelve una promesa) en un recuadro de la propia página
function ask(msg,yesNo){return new Promise(res=>{$('dlgT').textContent=String(msg);$('dlgNo').hidden=!yesNo;
 $('dlgNo').textContent=T('no');$('dlgOk').textContent=yesNo?T('yes'):'OK';$('dlg').hidden=false;$('dlgOk').focus();
 $('dlgOk').onclick=()=>{$('dlg').hidden=true;res(true)};$('dlgNo').onclick=()=>{$('dlg').hidden=true;res(false)}})}
const say=m=>{ask(m,false)};
// ---- Idioma: el de la placa (ajuste «lang»); hasta saberlo, el del navegador. Textos en es, en y de ----
const I18N={
es:{loc:'es-ES',loginT:'Entrar en la placa',loginH:'Estás conectado desde otra red (casa, camping…): hace falta el usuario y la clave de la web. De fábrica son wttc y wttc.',fUser:'Usuario',fWpass:'Clave',loginGo:'Entrar',wsetupT:'Protege el acceso desde esta red',wsetupH:'Entras con el usuario y la clave de fábrica (wttc / wttc), que son públicos: cualquiera en esta red podría manejar la calefacción. Elige otros.',wsetupNew:'Clave nueva de la web (mínimo 6)',webT:'Acceso a la web desde otra red',webH:'Al entrar a la web de la placa desde otra red (casa, camping…) se piden usuario y clave. Por su propia Wi-Fi no hace falta: ya se puso su clave.',fWuser:'Usuario de la web',fWpass2:'Clave de la web (mínimo 6; vacío: no cambiarla)',logout:'Salir',myT:'Mis estadísticas',myH:'Cada encendido queda apuntado en la placa: duración, gasoil, temperaturas y por qué se apagó. Si lo activas, la placa (o la app, si la placa no tiene internet) lo envía, y lo ves con gráficas en wttc.favala.es con tu código. Sin el código nadie puede verlas.',fIid:'Código de instalación (al cambiar de placa, escribe aquí el de la anterior para seguir con sus estadísticas)',fStats:'Enviar las estadísticas de esta placa',iidTg:'Enviar el código por Telegram',iidSee:'Ver mis estadísticas',runsSt:'Encendidos apuntados: {0} · enviados: {1}',runsOk:' · último envío hace {0}',runsNever:' · aún no se ha enviado nada',fOtaAuto:'Actualizaciones automáticas (al arrancar y una vez al día, con red con internet)',otaa0:'No buscar',otaa1:'Buscar y avisar',otaa2:'Buscar e instalar sola (nunca calentando)',nvAvail:'Hay una versión nueva del firmware: {0}.',nvGo:'Actualizar',scanBtn:'Buscar redes',scanning:'Buscando redes… (unos segundos)',scanNone:'No se ve ninguna red.',scanFail:'La placa no ha terminado de buscar. Prueba otra vez.',scanPick:'Toca una para usarla:',cabin:'Dentro: {0}',hum:' · humedad {0} %',tgtL:'Hasta',tgtNone:'Sin límite de temperatura',waiting:'En espera',tgtOn:'Hasta {0} · margen {1}',
 warmOk:' · agua caliente',turnOnTgt:'Encender hasta {0} (máx. {1})',depL:'Salgo a las',depGo:'Programar',depCancel:'Cancelar',
 depSet:'Salida: {0} a las {1}{2}. Enciende antes, según el frío que haga.',depTgt:' (hasta {0})',mStart:'Encender a esta hora',mDep:'Salgo a esta hora',
 sTgt:'Sin termostato',nextDep:'Próxima salida: {0} a las {1}.',minvHelp:'Calentando, se apaga sola si la batería baja medio voltio por debajo de esta.',
 fWarm:'Avisar cuando el agua llegue a (°C; 0 = no avisar)',hwT:'Pantalla, LED y termómetro (opcionales)',fOled:'Tipo de pantalla',oled0:'OLED 1,3" (SH1106)',
 oled1:'OLED 0,96" (SSD1306)',oled2:'OLED 1,5" grises (SSD1327)',fDisp:'Pantalla',disp0:'Apagada (solo con el botón BOOT)',disp1:'Automática (se apaga al minuto)',disp2:'Siempre encendida',fLed:'LED de la placa',
 led0:'Apagado',led1:'Bajo',led2:'Medio',led3:'Alto',fToff:'Corrección del termómetro (°C)',hwDet:'Detectado: {0}.',scr:'pantalla',
 hwNone:'No se detecta pantalla ni termómetro (bus I2C: SDA a IO4, SCL a IO5, más 3V3 y GND).',updCheck:'Buscar actualizaciones',updHelp2:'Con la placa unida a una red con internet, «Buscar actualizaciones» lo hace todo: busca, te pregunta y la descarga e instala sola.',updSearching:'Buscando… (hasta un minuto)',updAsk:'¿Actualizar ahora? La placa la descarga, la instala y se reinicia sola.',updDownloading:'Descargando e instalando… {0} %',updTimeout:'La placa no ha contestado a tiempo.',updTitle:'Actualizar firmware',updHelp:'Descarga el fichero .ota de la última versión (github.com/matatunos/wttc/releases) y súbelo aquí. Solo se instalan actualizaciones oficiales (con firma) y nunca mientras calienta; si la nueva no arranca bien, la placa vuelve sola a la anterior.',updFile:'Fichero .ota',updBtn:'Actualizar',updNoFile:'Elige primero el fichero .ota.',updSending:'Subiendo… {0} %',updChecking:'Comprobando la firma e instalando…',updSim:'En el simulador no se puede actualizar: es la web de una placa de verdad.',updNet:'Se cortó la conexión con la placa.',setupTitle:'Primer uso: protege la Wi-Fi de la placa',setupText:'La clave de fábrica es pública: cualquiera cerca podría manejar la calefacción. Elige una nueva (mínimo 8 caracteres); la placa se reinicia y tendrás que volver a conectarte a su Wi-Fi con la clave nueva.',setupLabel:'Clave nueva de la Wi-Fi',setupShort:'La clave debe tener al menos 8 caracteres.',water:'agua del motor',connecting:'Conectando…',battery:'Batería',flame:'Llama',power:'Potencia',turnOn:'Encender',turnOff:'Apagar',
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
en:{loc:'en-GB',loginT:'Log in to the board',loginH:'You are connected from another network (home, campsite…): the web user and password are needed. The factory ones are wttc and wttc.',fUser:'User',fWpass:'Password',loginGo:'Log in',wsetupT:'Protect access from this network',wsetupH:'You are logged in with the factory user and password (wttc / wttc), which are public: anyone on this network could control the heater. Choose others.',wsetupNew:'New web password (at least 6)',webT:'Web access from another network',webH:"When the board's web page is opened from another network (home, campsite…), a user and password are asked for. Not through its own Wi-Fi: its password was already entered.",fWuser:'Web user',fWpass2:'Web password (at least 6; empty: keep it)',logout:'Log out',myT:'My statistics',myH:'Every run is recorded on the board: duration, fuel, temperatures and why it stopped. If you turn this on, the board (or the app, if the board has no internet) sends it, and you see it with charts on wttc.favala.es with your code. Without the code nobody can see them.',fIid:'Installation code (when changing boards, enter the old one here to keep its statistics)',fStats:"Send this board's statistics",iidTg:'Send the code by Telegram',iidSee:'See my statistics',runsSt:'Runs recorded: {0} · sent: {1}',runsOk:' · last sent {0} ago',runsNever:' · nothing sent yet',fOtaAuto:'Automatic updates (at boot and once a day, with a network with internet)',otaa0:'Do not check',otaa1:'Check and notify',otaa2:'Check and install by itself (never while heating)',nvAvail:'There is a new firmware version: {0}.',nvGo:'Update',scanBtn:'Find networks',scanning:'Looking for networks… (a few seconds)',scanNone:'No network in sight.',scanFail:'The board did not finish searching. Try again.',scanPick:'Tap one to use it:',cabin:'Inside: {0}',hum:' · humidity {0} %',tgtL:'Up to',tgtNone:'No temperature limit',waiting:'Waiting',tgtOn:'Up to {0} · window {1}',
 warmOk:' · water is warm',turnOnTgt:'Heat up to {0} (max {1})',depL:'I leave at',depGo:'Set',depCancel:'Cancel',
 depSet:'Departure: {0} at {1}{2}. It switches on earlier, depending on how cold it is.',depTgt:' (up to {0})',mStart:'Switch on at this time',mDep:'I leave at this time',
 sTgt:'No thermostat',nextDep:'Next departure: {0} at {1}.',minvHelp:'While heating, it switches itself off if the battery drops half a volt below this.',
 fWarm:'Notify when the water reaches (°C; 0 = no notice)',hwT:'Display, LED and thermometer (optional)',fOled:'Display type',oled0:'OLED 1.3" (SH1106)',
 oled1:'OLED 0.96" (SSD1306)',oled2:'OLED 1.5" greyscale (SSD1327)',fDisp:'Display',disp0:'Off (only with the BOOT button)',disp1:'Automatic (off after a minute)',disp2:'Always on',fLed:'Board LED',
 led0:'Off',led1:'Low',led2:'Medium',led3:'High',fToff:'Thermometer correction (°C)',hwDet:'Detected: {0}.',scr:'display',
 hwNone:'No display or thermometer detected (I2C bus: SDA to IO4, SCL to IO5, plus 3V3 and GND).',updCheck:'Check for updates',updHelp2:'With the board joined to a network with internet, “Check for updates” does it all: it checks, asks you, and downloads and installs it by itself.',updSearching:'Checking… (up to a minute)',updAsk:'Update now? The board downloads it, installs it and restarts by itself.',updDownloading:'Downloading and installing… {0} %',updTimeout:'The board did not answer in time.',updTitle:'Update firmware',updHelp:'Download the .ota file of the latest version (github.com/matatunos/wttc/releases) and upload it here. Only official (signed) updates are installed, and never while heating; if the new one does not start properly, the board goes back to the previous one by itself.',updFile:'.ota file',updBtn:'Update',updNoFile:'Choose the .ota file first.',updSending:'Uploading… {0} %',updChecking:'Checking the signature and installing…',updSim:'Updating is not possible in the simulator: this is the web page of a real board.',updNet:'The connection to the board was lost.',setupTitle:'First use: protect the board\'s Wi-Fi',setupText:'The factory password is public: anyone nearby could control the heater. Choose a new one (at least 8 characters); the board restarts and you will have to reconnect to its Wi-Fi with the new password.',setupLabel:'New Wi-Fi password',setupShort:'The password must be at least 8 characters long.',water:'engine coolant',connecting:'Connecting…',battery:'Battery',flame:'Flame',power:'Power',turnOn:'Switch on',turnOff:'Switch off',
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
de:{loc:'de-DE',loginT:'Bei der Platine anmelden',loginH:'Du bist aus einem anderen Netz verbunden (Zuhause, Campingplatz…): Web-Benutzer und -Passwort sind nötig. Ab Werk sind es wttc und wttc.',fUser:'Benutzer',fWpass:'Passwort',loginGo:'Anmelden',wsetupT:'Zugang aus diesem Netz schützen',wsetupH:'Du bist mit Benutzer und Passwort ab Werk (wttc / wttc) angemeldet, die öffentlich sind: jeder in diesem Netz könnte die Heizung steuern. Wähle andere.',wsetupNew:'Neues Web-Passwort (mindestens 6)',webT:'Web-Zugang aus einem anderen Netz',webH:'Wird die Webseite der Platine aus einem anderen Netz geöffnet (Zuhause, Campingplatz…), werden Benutzer und Passwort verlangt. Über ihr eigenes WLAN nicht: dessen Passwort wurde schon eingegeben.',fWuser:'Web-Benutzer',fWpass2:'Web-Passwort (mindestens 6; leer: behalten)',logout:'Abmelden',myT:'Meine Statistiken',myH:'Jeder Heizlauf wird auf der Platine notiert: Dauer, Diesel, Temperaturen und warum er endete. Wenn du das einschaltest, sendet es die Platine (oder die App, wenn die Platine kein Internet hat), und du siehst es mit Diagrammen auf wttc.favala.es mit deinem Code. Ohne den Code kann sie niemand sehen.',fIid:'Installationscode (beim Platinenwechsel hier den der alten eingeben, um ihre Statistiken weiterzuführen)',fStats:'Statistiken dieser Platine senden',iidTg:'Code per Telegram senden',iidSee:'Meine Statistiken ansehen',runsSt:'Heizläufe notiert: {0} · gesendet: {1}',runsOk:' · zuletzt gesendet vor {0}',runsNever:' · noch nichts gesendet',fOtaAuto:'Automatische Updates (beim Start und einmal am Tag, mit Netz mit Internet)',otaa0:'Nicht suchen',otaa1:'Suchen und melden',otaa2:'Suchen und selbst installieren (nie beim Heizen)',nvAvail:'Es gibt eine neue Firmware-Version: {0}.',nvGo:'Aktualisieren',scanBtn:'Netze suchen',scanning:'Suche Netze… (ein paar Sekunden)',scanNone:'Kein Netz in Reichweite.',scanFail:'Die Platine ist mit der Suche nicht fertig geworden. Nochmal versuchen.',scanPick:'Eines antippen, um es zu nutzen:',cabin:'Innen: {0}',hum:' · Feuchte {0} %',tgtL:'Bis',tgtNone:'Ohne Temperaturgrenze',waiting:'Wartet',tgtOn:'Bis {0} · Zeitfenster {1}',
 warmOk:' · Wasser ist warm',turnOnTgt:'Heizen bis {0} (max. {1})',depL:'Abfahrt um',depGo:'Einstellen',depCancel:'Löschen',
 depSet:'Abfahrt: {0} um {1}{2}. Schaltet je nach Kälte früher ein.',depTgt:' (bis {0})',mStart:'Zu dieser Zeit einschalten',mDep:'Abfahrt zu dieser Zeit',
 sTgt:'Ohne Thermostat',nextDep:'Nächste Abfahrt: {0} um {1}.',minvHelp:'Während des Heizens schaltet sie sich ab, wenn die Batterie ein halbes Volt darunter fällt.',
 fWarm:'Melden, wenn das Wasser erreicht (°C; 0 = keine Meldung)',hwT:'Display, LED und Thermometer (optional)',fOled:'Displaytyp',oled0:'OLED 1,3" (SH1106)',
 oled1:'OLED 0,96" (SSD1306)',oled2:'OLED 1,5" Graustufen (SSD1327)',fDisp:'Display',disp0:'Aus (nur mit der BOOT-Taste)',disp1:'Automatisch (nach einer Minute aus)',disp2:'Immer an',fLed:'LED der Platine',
 led0:'Aus',led1:'Niedrig',led2:'Mittel',led3:'Hoch',fToff:'Thermometerkorrektur (°C)',hwDet:'Erkannt: {0}.',scr:'Display',
 hwNone:'Kein Display und kein Thermometer erkannt (I2C-Bus: SDA an IO4, SCL an IO5, dazu 3V3 und GND).',updCheck:'Nach Updates suchen',updHelp2:'Ist die Platine mit einem Netz mit Internet verbunden, erledigt „Nach Updates suchen“ alles: sucht, fragt dich und lädt und installiert es selbst.',updSearching:'Suche… (bis zu einer Minute)',updAsk:'Jetzt aktualisieren? Die Platine lädt es herunter, installiert es und startet selbst neu.',updDownloading:'Lade herunter und installiere… {0} %',updTimeout:'Die Platine hat nicht rechtzeitig geantwortet.',updTitle:'Firmware aktualisieren',updHelp:'Die .ota-Datei der neuesten Version herunterladen (github.com/matatunos/wttc/releases) und hier hochladen. Es werden nur offizielle (signierte) Updates installiert und nie während des Heizens; startet die neue nicht richtig, kehrt die Platine von selbst zur vorherigen zurück.',updFile:'.ota-Datei',updBtn:'Aktualisieren',updNoFile:'Zuerst die .ota-Datei wählen.',updSending:'Lade hoch… {0} %',updChecking:'Prüfe die Signatur und installiere…',updSim:'Im Simulator kann nicht aktualisiert werden: das ist die Webseite einer echten Platine.',updNet:'Die Verbindung zur Platine ist abgebrochen.',setupTitle:'Erste Nutzung: WLAN der Platine schützen',setupText:'Das Passwort ab Werk ist öffentlich: jeder in der Nähe könnte die Heizung steuern. Wähle ein neues (mindestens 8 Zeichen); die Platine startet neu und du musst dich mit dem neuen Passwort wieder mit ihrem WLAN verbinden.',setupLabel:'Neues WLAN-Passwort',setupShort:'Das Passwort muss mindestens 8 Zeichen lang sein.',water:'Kühlwasser',connecting:'Verbinde…',battery:'Batterie',flame:'Flamme',power:'Leistung',turnOn:'Einschalten',turnOff:'Ausschalten',
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
// Texto traducido; {0}, {1}… se sustituyen por los datos. Las listas (PH, days, daysL) se devuelven tal cual
const T=(k,...a)=>{let v=I18N[lang][k];if(v===undefined)v=I18N.es[k];return Array.isArray(v)?v:String(v).replace(/\{(\d)\}/g,(m,i)=>a[i])};
// Cambia el idioma y repinta los textos fijos (data-t) y las etiquetas para lectores de pantalla (data-ta)
function setLang(l){if(!I18N[l])return;lang=l;document.documentElement.lang=l;
 document.querySelectorAll('[data-t]').forEach(e=>e.textContent=T(e.dataset.t));
 document.querySelectorAll('[data-ta]').forEach(e=>e.setAttribute('aria-label',T(e.dataset.ta)));seg()}
// Duraciones del botón principal y de los programas (el firmware limita a 60 min). Con objetivo de temperatura
// (termostato) la duración es la ventana máxima, hasta 4 h. Objetivos que se ofrecen (el firmware admite 5–25 °C)
const DURS=[15,30,45,60],SDURS=[15,30,45,60],TDURS=[60,120,180,240],TGTS=[10,12,14,16,17,18,19,20,21,22,23,24,25];
// Estado: st = último /api/state; sched = programas en edición; dirty = cambios sin guardar;
// dur = duración elegida; synced = ya se puso en hora; busy = esperando la respuesta de encender/apagar
let st=null,sched=[],dirty=false,dur=30,synced=false,busy=false,tgt=0;
// ¿Tiene la placa termómetro? (sin él, «Hasta X °C» no se ofrece)
const hasSens=()=>!!st&&st.ct!==null&&st.ct!==undefined;
// Temperatura con su unidad: 19,5 °C
const deg=(v,d)=>num(v,d)+' °C';
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
// 401 = entra desde otra red sin sesión: se enseña el login
async function api(p,b){const r=await fetch(p,b?{method:'POST',body:new URLSearchParams(b)}:undefined);const t=await r.text();if(r.status===401)showLogin();if(!r.ok)throw new Error(t||r.status);try{return JSON.parse(t)}catch(e){return t}}
function showLogin(){if(!$('login').hidden)return;$('login').hidden=false;$('lg_err').textContent='';setTimeout(()=>$('lg_user').focus(),50)}
$('lgForm').onsubmit=async e=>{e.preventDefault();$('lg_go').disabled=true;$('lg_err').textContent='';
 try{await api('/api/login',{user:$('lg_user').value.trim(),pass:$('lg_pass').value});$('login').hidden=true;$('lg_pass').value='';synced=false;await poll()}
 catch(err){$('lg_err').textContent=err.message}$('lg_go').disabled=false};
// Primer uso desde otra red: usuario y clave de la web nuevos
$('ws_save').onclick=async()=>{const u=$('ws_user').value.trim(),p=$('ws_pass').value;if(p.length<6)return say(T('fWpass2'));
 try{say(await api('/api/cfg',{webuser:u||'wttc',webpass:p}));$('ws_pass').value='';poll()}catch(e){say(e.message)}};
$('logout').onclick=async()=>{try{await api('/api/logout',{})}catch(e){}location.reload()};
$('iidTg').onclick=async()=>{try{say(await api('/api/iidtg',{}))}catch(e){say(e.message)}};
// Pinta los botones de duración
function seg(){const L=tgt?TDURS:DURS;if(!L.includes(dur))dur=tgt?120:30;
 $('seg').innerHTML=L.map(d=>`<button class="${d==dur?'sel':''}" data-d="${d}">${fmtDur(d)}</button>`).join('');
 $('tgt').innerHTML='<option value="0">'+T('tgtNone')+'</option>'+TGTS.map(t=>`<option value="${t}"${t==tgt?' selected':''}>${t} °C</option>`).join('')}
// Elegir objetivo de temperatura (0 = encendido normal)
$('tgt').onchange=()=>{tgt=+$('tgt').value;seg();render()};
// Elegir duración
$('seg').onclick=e=>{const d=e.target.dataset.d;if(d){dur=+d;seg();render()}};
// Botón grande: enciende con la duración elegida o apaga
// Con termostato en marcha (aunque esté en espera) el botón lo termina
$('big').onclick=async()=>{if(!st)return;const off=st.on||st.tgt>0;busy=true;$('big').disabled=true;$('big').textContent=T(off?'turningOff':'turningOn');
 try{await api(off?'/api/off':'/api/on',off?{}:{min:dur,tgt:tgt})}catch(e){say(e.message)}busy=false;poll()};
// Salida suelta: programar o cancelar
$('depGo').onclick=async()=>{try{await api('/api/dep',st&&st.dep?{t:'off'}:{t:$('depT').value,tgt:tgt});poll()}catch(e){say(e.message)}};
// Repinta toda la página con el último estado
function render(){if(!st)return;
 if(st.lang&&st.lang!=lang)setLang(st.lang);
 document.body.classList.toggle('on',st.on);$('hname').textContent=st.name;
 $('tmp').textContent=st.temp>-100?st.temp+'°':'--°';
 const th=st.tgt>0;
 $('st').textContent=st.on?(T('PH')[st.ph]||T('heating')):th?T('waiting'):T('off');
 $('rem').textContent=th?T('tgtOn',deg(st.tgt,0),fmtRem(st.tun))+(st.wa?T('warmOk'):''):
  st.on?(st.ph==3?T('hotWater'):'')+T('left',fmtRem(st.remain))+(st.src=='programa'?T('bySched'):'')+(st.wa?T('warmOk'):''):'';
 const hasT=hasSens();
 $('cab').hidden=!hasT;if(hasT)$('cab').innerHTML=T('cabin','<b>'+deg(st.ct,1)+'</b>')+(st.ch!==null?T('hum',st.ch):'');
 $('tgtRow').hidden=!hasT;if(!hasT&&tgt){tgt=0;seg()}
 if(st.dep){const d=new Date(st.dep*1000),k=Math.round((new Date(d).setHours(0,0,0,0)-new Date(st.time*1000).setHours(0,0,0,0))/864e5);
  $('depSt').textContent=T('depSet',k<=0?T('today'):k==1?T('tomorrow'):T('daysL')[(d.getDay()+6)%7],pad(d.getHours())+':'+pad(d.getMinutes()),st.dept?T('depTgt',deg(st.dept,0)):'')}
 else $('depSt').textContent='';
 if(!busy)$('depGo').textContent=T(st.dep?'depCancel':'depGo');
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
 // Versión nueva vista por la placa (búsqueda automática o a mano), con botón; y el progreso si está instalando
 if(st.op>=0)w+='<div class="warn">'+T('updDownloading',st.op)+'</div>';
 else if(st.nv)w+='<div class="warn">'+T('nvAvail',esc(st.nv))+' <button class="btn pri" id="nvGo" style="margin-left:6px;padding:6px 12px">'+T('nvGo')+'</button></div>';
 $('warn').innerHTML=w;
 $('setup').hidden=st.apdef!==true;
 $('wsetup').hidden=st.wdef!==true||st.apdef===true;$('logout').hidden=!st.lan;
 if(!busy){$('big').disabled=st.apdef===true||st.wdef===true;$('big').textContent=st.on||th?T('turnOff'):tgt?T('turnOnTgt',deg(tgt,0),fmtDur(dur)):T('turnOn')+' '+fmtDur(dur)}
 if(!dirty){sched=st.sch.map(a=>({en:!!a[0],days:a[1],start:a[2],dur:a[3],x:a[4]||0}));$('auto').checked=st.auto;list()}
 $('diag').textContent=T('lastTx')+(st.tx||'-')+'\n'+T('lastRx')+(st.rx||'-')+'\n\n'+st.log.join('\n');
 $('wifi').textContent=(st.ble?T('bleOn'):'')+(st.sta?T('staOn',st.ssid,st.ip,st.rssi):T('staOff'))+T('ownAp',st.name);
 $('tgst').textContent=T(st.tg?'tgOn':'tgOff')+T('tgNet')+(st.tgl?T('tgLast')+st.tgl:'');
 next()}
// Pinta la lista de programas (hora, duración, interruptor, borrar y días)
function list(){$('list').innerHTML=sched.map((s,i)=>`<div class="row"><div class="rtop">
<input type="time" value="${hm(s.start)}" data-i="${i}" data-k="start" aria-label="${T('aTime')}">
<select data-i="${i}" data-k="dur" aria-label="${T('aDur')}"${s.x&128?' hidden':''}>${(s.x&63?TDURS:SDURS).map(d=>`<option value="${d}"${d==s.dur?' selected':''}>${fmtDur(d)}</option>`).join('')}</select>
<label class="sw"><input type="checkbox"${s.en?' checked':''} data-i="${i}" data-k="en" aria-label="${T('aEn')}"><i></i></label>
<button class="x" data-i="${i}" data-k="del" aria-label="${T('aDel')}">×</button></div>
<div class="days">${T('days').map((d,j)=>`<button class="${s.days>>j&1?'sel':''}" data-i="${i}" data-k="day" data-j="${j}">${d}</button>`).join('')}</div>
<div class="opts"><select data-i="${i}" data-k="mode"><option value="0">${T('mStart')}</option><option value="128"${s.x&128?' selected':''}>${T('mDep')}</option></select>
${hasSens()||s.x&63?`<select data-i="${i}" data-k="tg"><option value="0">${T('sTgt')}</option>${TGTS.map(t=>`<option value="${t}"${(s.x&63)==t?' selected':''}>${T('tgtL')} ${t} °C</option>`).join('')}</select>`:''}</div></div>`).join('')
 ||'<p class="sub">'+T('addHelp')+'</p>';
 $('save').disabled=!dirty}
// Marca que hay cambios sin guardar en los programas
function touch(re=true){dirty=true;if(re)list();$('save').disabled=false;next()}
// Clics en la lista: borrar un programa o cambiar un día
$('list').addEventListener('click',e=>{const t=e.target.closest('button[data-k]');if(!t)return;const i=+t.dataset.i;
 if(t.dataset.k=='del'){sched.splice(i,1);touch()}else if(t.dataset.k=='day'){sched[i].days^=1<<+t.dataset.j;touch()}});
// Cambios en la lista: hora, duración o activo
$('list').addEventListener('change',e=>{const t=e.target,i=+t.dataset.i,k=t.dataset.k;
 if(k=='start'&&t.value){const p=t.value.split(':');sched[i].start=+p[0]*60+ +p[1]}else if(k=='dur')sched[i].dur=+t.value;else if(k=='en')sched[i].en=t.checked;
 // Modo (encender a la hora / hora de salida) y objetivo: x = 128 si es salida + °C del objetivo. Cambian la lista de duraciones
 else if(k=='mode'||k=='tg'){const s=sched[i];s.x=k=='mode'?(+t.value|(s.x&63)):((s.x&128)|+t.value);
  const L=s.x&63?TDURS:SDURS;if(!L.includes(s.dur))s.dur=s.x&63?120:30;touch();return}
 touch(false)});
// Añadir un programa (por defecto: 07:00, 30 min, de lunes a viernes)
$('add').onclick=()=>{if(sched.length>=8)return say(T('max8'));sched.push({en:true,days:31,start:420,dur:30,x:0});touch()};
// Interruptor general de los programas
$('auto').onchange=()=>touch(false);
// Guardar programas en la placa (formato "activo,días,inicio,duración;…")
$('save').onclick=async()=>{try{await api('/api/sched',{auto:$('auto').checked?1:0,list:sched.map(s=>[s.en?1:0,s.days,s.start,s.dur,s.x||0].join(',')).join(';')});
 dirty=false;$('save').disabled=true;$('save').textContent=T('saved');setTimeout(()=>$('save').textContent=T('saveSched'),1500);poll()}catch(e){say(T('saveFail')+e.message)}};
// Calcula y muestra el próximo encendido programado
function next(){if(!st)return;const act=sched.filter(s=>s.en&&s.days);
 if(!act.length){$('next').textContent=T(sched.length?'noneActive':'noSched');return}
 if(!$('auto').checked){$('next').textContent=T('schedOff');return}
 const now=new Date(st.tv?st.time*1000:Date.now()),wd=(now.getDay()+6)%7,m=now.getHours()*60+now.getMinutes();let b=null;
 for(const s of act)for(let k=0;k<8;k++){const d=(wd+k)%7;if(!(s.days>>d&1)||(k==0&&s.start<=m))continue;const t=k*1440+s.start-m;if(!b||t<b.t)b={t,k,d,s};break}
 const dn=b&&(b.k==0?T('today'):b.k==1?T('tomorrow'):T('daysL')[b.d]);
 $('next').textContent=!b?T('noSched'):b.s.x&128?T('nextDep',dn,hm(b.s.start)):T('next',dn,hm(b.s.start),fmtDur(b.s.dur))}
// Leer las averías guardadas en la Webasto
$('errs').onclick=async()=>{$('errout').textContent=T('reading');try{const r=await api('/api/errors');
 $('errout').textContent=(!r.ok?T('noAnswer'):r.codes.length?r.codes.map(c=>T('code',c.c,c.n)).join('\n'):T('noFaults'))+T('raw')+r.raw}catch(e){$('errout').textContent=T('error')+e.message}};
// Configuración: lee los ajustes de la placa (las claves no se devuelven nunca: los campos quedan vacíos)
async function loadCfg(){try{const c=await api('/api/cfg');
 $('c_name').value=c.name;$('c_pin').value=c.pin||'';$('c_wm').value=c.wifimode;$('c_ssid').value=c.ssid;$('c_chat').value=c.tgchat;$('c_mv').value=c.minvolt;$('c_lang').value=c.lang||lang;
 $('hwBox').hidden=!c.th;$('lb_oled').hidden=$('lb_disp').hidden=!c.scr;$('lb_toff').hidden=!c.sens;
 $('c_otaa').value=c.otaauto!=null?c.otaauto:1;$('c_otaa').disabled=c.otaauto==null;
 // Acceso desde otra red y mis estadísticas (firmware 0.2.16+)
 $('c_wuser').value=c.webuser||'';$('c_wpass').value='';$('c_wuser').disabled=$('c_wpass').disabled=c.webuser==null;
 $('myBox').hidden=!c.iid;if(c.iid){$('c_iid').value=c.iid;$('c_stats').checked=!!c.stats;$('iidSee').href='https://wttc.favala.es/mi.php#'+c.iid;
  $('runsSt').textContent=T('runsSt',c.nruns,c.rack)+(c.stok>=0?T('runsOk',fmtRem(c.stok)):c.stats?T('runsNever'):'');}
 if(c.th){$('c_oled').value=c.oled;$('c_disp').value=c.disp;$('c_led').value=c.led;$('c_toff').value=c.toff;$('c_warm').value=c.warm;
  const h=[c.sens,c.scr?T('scr'):''].filter(x=>x).join(', ');$('hw').textContent=h?T('hwDet',h):T('hwNone')}
 $('c_tok').value='';$('c_ap').value='';$('c_pass').value='';$('c_tok').placeholder=T(c.tg?'stored':'notSet');
 $('c_bonds').textContent=c.bonds?T('bonds',c.bonds):T('noBonds');
 $('c_ver').textContent=T('ver',c.ver)}catch(e){say(T('cfgFail')+e.message)}}
// Al abrir el apartado Configuración se leen los ajustes
$('cfgd').ontoggle=()=>{if($('cfgd').open)loadCfg()};
// Guardar configuración: las claves vacías no se envían (la placa conserva las que tenía).
// El idioma va primero: así la respuesta de la placa ya sale en el nuevo
$('csave').onclick=async()=>{const b={lang:$('c_lang').value,name:$('c_name').value.trim(),wifimode:$('c_wm').value,ssid:$('c_ssid').value.trim(),tgchat:$('c_chat').value.trim(),minvolt:$('c_mv').value,
  oled:$('c_oled').value,disp:$('c_disp').value,led:$('c_led').value,toff:$('c_toff').value||0,warm:$('c_warm').value||0};
 if(!$('c_otaa').disabled)b.otaauto=$('c_otaa').value;
 if(!$('c_wuser').disabled){b.webuser=$('c_wuser').value.trim();if($('c_wpass').value)b.webpass=$('c_wpass').value}
 if(!$('myBox').hidden){b.iid=$('c_iid').value.trim();b.stats=$('c_stats').checked?1:0}
 if($('c_pin').value.trim())b.pin=$('c_pin').value.trim();if($('c_ap').value)b.appass=$('c_ap').value;if($('c_pass').value)b.pass=$('c_pass').value;if($('c_tok').value.trim())b.tgtok=$('c_tok').value.trim();
 try{setLang(b.lang);say(await api('/api/cfg',b));loadCfg();poll()}catch(e){say(e.message)}};
// Primer uso: guardar la clave nueva de la Wi-Fi (la placa se reinicia para aplicarla)
$('s_save').onclick=async()=>{const v=$('s_ap').value;if(v.length<8)return say(T('setupShort'));
 try{say(await api('/api/cfg',{appass:v}));$('s_ap').value='';poll()}catch(e){say(e.message)}};
// Actualizar firmware: sube el .ota (multipart) con XMLHttpRequest para ver el progreso. La placa comprueba la firma
// y la versión, lo graba en el hueco libre y se reinicia. En el simulador (esta web dentro de un iframe) no se puede.
const inSim=()=>{try{return window.parent!==window&&!!parent.WB}catch(e){return false}};
$('u_go').onclick=()=>{if(inSim())return say(T('updSim'));const f=$('u_file').files[0];if(!f)return say(T('updNoFile'));
 const x=new XMLHttpRequest(),fd=new FormData();fd.append('ota',f,f.name);$('u_go').disabled=true;
 x.upload.onprogress=e=>{if(e.lengthComputable){const p=Math.floor(e.loaded*100/e.total);$('u_bar').hidden=p>=100;$('u_bar').value=p;$('u_st').textContent=p<100?T('updSending',p):T('updChecking')}};
 x.onload=()=>{$('u_go').disabled=false;$('u_st').textContent=x.responseText;say(x.responseText)};
 x.onerror=()=>{$('u_go').disabled=false;$('u_st').textContent=T('updNet')};
 x.open('POST','/api/update');x.send(fd)};
// Botón «Actualizar» del aviso de versión nueva: pregunta y la placa la descarga e instala (como «Buscar actualizaciones»)
$('warn').addEventListener('click',async e=>{if(e.target.id!=='nvGo')return;if(inSim())return say(T('updSim'));
 if(!await ask(T('nvAvail',st.nv)+'\n\n'+T('updAsk'),true))return;
 try{await api('/api/otaupdate',{});const r=await otaWait();if(r)say(r)}catch(err){say(err.message)}});
// Buscar actualizaciones por internet: la placa mira la última versión; si hay una nueva, pregunta y, si se acepta,
// la placa la descarga, comprueba la firma, la instala y se reinicia. El progreso llega en el estado (op, om, onew)
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
// Barra de progreso: con el porcentaje de la descarga (op) mientras dura; se oculta al acabar
async function otaWait(){try{for(let i=0;i<80;i++){await sleep(1500);await poll();
 if(st&&st.op>=0){$('u_st').textContent=T('updDownloading',st.op);$('u_bar').hidden=false;$('u_bar').value=st.op}
 if(st&&st.om)return st.om}return ''}finally{$('u_bar').hidden=true}}
$('u_check').onclick=async()=>{if(inSim())return say(T('updSim'));$('u_check').disabled=true;$('u_st').textContent=T('updSearching');
 try{await api('/api/otacheck',{});const m=await otaWait();$('u_st').textContent=m||T('updTimeout');
  if(m&&st.onew&&await ask(m+'\n\n'+T('updAsk'),true)){await api('/api/otaupdate',{});const r=await otaWait();$('u_st').textContent=r||T('updTimeout');if(r)say(r)}}
 catch(e){$('u_st').textContent=e.message}$('u_check').disabled=false};
// Buscar redes Wi-Fi: la placa busca en segundo plano; se le pregunta cada 1,5 s hasta tener la lista (como mucho 25 s)
const bars=r=>r>=-55?'▂▄▆█':r>=-67?'▂▄▆':r>=-78?'▂▄':'▂';
$('scanBtn').onclick=async()=>{const L=$('scanList');$('scanBtn').disabled=true;L.innerHTML='<p class="sub">'+T('scanning')+'</p>';
 try{let r=null;for(let i=0;i<16;i++){r=await api('/api/scan');if(r.nets)break;await sleep(1500)}
  L.innerHTML=!r||!r.nets?'<p class="sub">'+T('scanFail')+'</p>':!r.nets.length?'<p class="sub">'+T('scanNone')+'</p>':
   '<p class="sub" style="margin:0">'+T('scanPick')+'</p>'+r.nets.map(n=>`<button data-s="${esc(n.s)}"><span>${esc(n.s)}${n.e?' 🔒':''}</span><small>${bars(n.r)} ${n.r} dBm</small></button>`).join('')}
 catch(e){L.innerHTML='<p class="sub">'+esc(e.message)+'</p>'}
 $('scanBtn').disabled=false};
// Tocar una red: se pone su nombre y se pasa a la contraseña
$('scanList').onclick=e=>{const b=e.target.closest('button[data-s]');if(!b)return;$('c_ssid').value=b.dataset.s;$('scanList').innerHTML='';$('c_pass').focus()};
// Poner a cero el gasoil estimado
$('gasreset').onclick=async()=>{if(!await ask(T('askGas'),true))return;try{say(await api('/api/gasreset',{}));poll()}catch(e){say(e.message)}};
// Borrar los móviles emparejados por Bluetooth
$('forget').onclick=async()=>{if(!await ask(T('askForget'),true))return;try{say(await api('/api/forget',{}));loadCfg()}catch(e){say(e.message)}};
// Aviso de prueba por Telegram
$('tgtest').onclick=async()=>{try{say(await api('/api/tgtest',{}))}catch(e){say(e.message)}};
// Poner la placa en hora con la del móvil
$('tsync').onclick=async()=>{try{await api('/api/time',{epoch:Math.floor(Date.now()/1000)});poll()}catch(e){say(e.message)}};
// Pide el estado. La primera vez, si la placa no está en hora (o va desfasada), se la pone en hora
async function poll(){try{st=await api('/api/state');$('net').classList.remove('bad');
 if(!synced){synced=true;if(!st.tv||Math.abs(st.time-Date.now()/1000)>30){await api('/api/time',{epoch:Math.floor(Date.now()/1000)});st=await api('/api/state')}}
 render()}catch(e){$('net').classList.add('bad')}}
// Arranque: idioma del navegador hasta conocer el de la placa, botones de duración, primer estado y repetir cada 3 s
setLang(lang);poll();setInterval(()=>{if(!document.hidden)poll()},3000);
</script></body></html>)HTML";
