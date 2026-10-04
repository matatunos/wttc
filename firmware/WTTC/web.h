// ============================================================================================================
// web.h — Página web que sirve el ESP32 de WTTC (http://192.168.4.1 o http://wttc.local)
// Código generado íntegramente con Claude (Anthropic).
//
// Va en un .h aparte porque el preprocesador de Arduino solo analiza los .ino y confundía las funciones
// del JavaScript con funciones de C++ (generaba prototipos imposibles y no compilaba).
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
<div class="ctr"><div class="tmp" id="tmp">--°</div><div class="lbl">agua del motor</div><div class="st" id="st">Conectando…</div><div class="lbl" id="rem"></div></div></div>
<!-- Datos que da la Webasto (batería, llama, potencia) y debajo el gasoil estimado -->
<div class="stats"><span>Batería <b id="volt">--</b></span><span>Llama <b id="flame">--</b></span><span>Potencia <b id="pw">--</b></span></div>
<div class="gas" id="gas"></div>
<!-- Avisos: se apagó sola (con sus averías), el W-Bus no responde, la placa no está en hora -->
<div id="warn"></div>
<!-- Duración elegida (15–60 min) y botón grande de encender / apagar -->
<div class="seg" id="seg"></div>
<button class="big" id="big" disabled>Encender</button>

<!-- Programas semanales: hora, duración, días y activo; se guardan en la placa con «Guardar programas» -->
<h2>Programas</h2><p class="sub" id="next">Sin programas.</p>
<div class="line"><span>Programas activos</span><label class="sw"><input type="checkbox" id="auto" aria-label="Programas activos"><i></i></label></div>
<div id="list"></div>
<div class="acts"><button class="btn" id="add">Añadir programa</button><button class="btn pri" id="save" disabled>Guardar programas</button></div>

<!-- Diagnóstico: averías guardadas en la Webasto, gasoil a cero, últimas tramas del bus y registro de eventos -->
<details><summary>Diagnóstico</summary>
<div class="acts" style="margin-top:10px"><button class="btn" id="errs">Leer averías</button><button class="btn" id="gasreset">Gasoil a cero</button></div>
<pre id="errout"></pre><pre id="diag"></pre></details>
<!-- Configuración: todo lo que se guarda en la placa (se lee del ESP32 al abrir este apartado) -->
<details id="cfgd"><summary>Configuración</summary>
<p class="sub" id="wifi" style="margin-top:8px"></p>
<h3>Bluetooth y Wi-Fi propios</h3>
<label class="f">Nombre (red Wi-Fi y Bluetooth)<input id="c_name" maxlength="29" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f">PIN de emparejamiento Bluetooth (6 cifras)<input id="c_pin" inputmode="numeric" maxlength="6" autocomplete="off"></label>
<p class="sub" id="c_bonds"></p>
<label class="f">Clave de la Wi-Fi propia (mínimo 8; vacío: no cambiarla)<input id="c_ap" type="password" autocomplete="new-password"></label>
<label class="f">Wi-Fi propia<select id="c_wm"><option value="0">Siempre encendida (gasta más)</option><option value="1">Solo mientras calienta</option><option value="2">Solo a petición (máximo ahorro)</option></select></label>
<p class="sub">Tras arrancar, la Wi-Fi siempre queda 10 minutos encendida, por si hay que entrar sin Bluetooth. En «solo a petición» se enciende desde la app, y unos minutos para enviar avisos.</p>
<h3>Red con internet (opcional)</h3>
<label class="f">Red Wi-Fi (casa o punto de acceso del móvil)<input id="c_ssid" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f">Contraseña (vacío: no cambiarla)<input id="c_pass" type="password" autocomplete="new-password"></label>
<h3>Avisos por Telegram (opcional)</h3>
<label class="f">Token del bot (de @BotFather; vacío: no cambiarlo)<input id="c_tok" type="password" autocomplete="off" autocapitalize="off" autocorrect="off"></label>
<label class="f">Chat ID (vacío: avisos desactivados)<input id="c_chat" inputmode="numeric" autocomplete="off"></label>
<p class="sub" id="tgst" style="white-space:pre-line"></p>
<h3>Seguridad</h3>
<label class="f">Batería mínima para arrancar un programa (V)<input id="c_mv" type="number" step="0.1" min="10.5" max="13"></label>
<div class="acts" style="margin-top:14px"><button class="btn pri" id="csave">Guardar</button><button class="btn" id="tgtest">Probar Telegram</button></div>
<div class="acts" style="margin-top:10px"><button class="btn" id="forget">Borrar emparejamientos</button><button class="btn" id="tsync">Poner en hora</button></div>
<p class="sub" id="c_ver" style="margin-top:10px"></p></details>
</main>
<script>
// ===== JavaScript de la página. Habla con el ESP32 por su API (/api/...) y repinta cada 3 s. =====
// Atajo para buscar elementos por id
const $=id=>document.getElementById(id);
// Días en letra (lunes primero, como en los programas) y en texto para «Próximo encendido»
const DAYS=['L','M','X','J','V','S','D'],DAYSL=['el lunes','el martes','el miércoles','el jueves','el viernes','el sábado','el domingo'];
// Duraciones del botón principal y de los programas (el firmware limita a 60 min)
const DURS=[15,30,45,60],SDURS=[15,30,45,60];
// Estado: st = último /api/state; sched = programas en edición; dirty = cambios sin guardar;
// dur = duración elegida; synced = ya se puso en hora; busy = esperando la respuesta de encender/apagar
let st=null,sched=[],dirty=false,dur=30,synced=false,busy=false;
// Nombres del estado real que manda el firmware (ph: 0 apagada … 4 sin respuesta)
const PH=['Apagada','Arrancando…','Calentando','En pausa','Sin respuesta'];
// Escapa texto para meterlo en HTML sin riesgo
const esc=s=>String(s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
// Dos cifras: 7 -> "07"
const pad=n=>String(n).padStart(2,'0');
// Fecha completa en español para la cabecera: «Domingo, 4 de octubre de 2026 · 20:51»
const DIAS=['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],MESES=['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const fecha=d=>DIAS[d.getDay()]+', '+d.getDate()+' de '+MESES[d.getMonth()]+' de '+d.getFullYear()+' · '+pad(d.getHours())+':'+pad(d.getMinutes());
// Minutos del día -> "07:30"
const hm=m=>pad(Math.floor(m/60))+':'+pad(m%60);
// Duración en minutos -> "30 min" / "1 h"
const fmtDur=d=>d<60?d+' min':Math.floor(d/60)+' h'+(d%60?' '+d%60:'');
// Segundos restantes -> "25 min" / "1 h 05 min"
const fmtRem=s=>{const m=Math.ceil(s/60);return m<60?m+' min':Math.floor(m/60)+' h '+pad(m%60)+' min'};
// Llamada a la API del ESP32: GET sin cuerpo o POST con formulario; devuelve JSON si lo es, o el texto.
// Si la respuesta no es 2xx lanza un error con el texto que da el firmware (se muestra con alert).
async function api(p,b){const r=await fetch(p,b?{method:'POST',body:new URLSearchParams(b)}:undefined);const t=await r.text();if(!r.ok)throw new Error(t||r.status);try{return JSON.parse(t)}catch(e){return t}}
// Pinta los botones de duración
function seg(){$('seg').innerHTML=DURS.map(d=>`<button class="${d==dur?'sel':''}" data-d="${d}">${fmtDur(d)}</button>`).join('')}
// Elegir duración
$('seg').onclick=e=>{const d=e.target.dataset.d;if(d){dur=+d;seg();render()}};
// Botón grande: enciende con la duración elegida o apaga
$('big').onclick=async()=>{if(!st)return;busy=true;$('big').disabled=true;$('big').textContent=st.on?'Apagando…':'Encendiendo…';
 try{await api(st.on?'/api/off':'/api/on',st.on?{}:{min:dur})}catch(e){alert(e.message)}busy=false;poll()};
// Repinta toda la página con el último estado
function render(){if(!st)return;
 document.body.classList.toggle('on',st.on);$('hname').textContent=st.name;
 $('tmp').textContent=st.temp>-100?st.temp+'°':'--°';
 $('st').textContent=st.on?(PH[st.ph]||'Calentando'):'Apagada';
 $('rem').textContent=st.on?(st.ph==3?'Agua caliente: vuelve a prender sola. ':'')+'Quedan '+fmtRem(st.remain)+(st.src=='programa'?' (programa)':''):'';
 $('arc').style.strokeDashoffset=553*(1-(st.on&&st.total?st.remain/st.total:0));
 $('volt').textContent=st.volt>0?st.volt.toFixed(1)+' V':'--';
 $('flame').textContent=st.flame<0?'--':(st.flame?'sí':'no');
 $('pw').textContent=st.pw<0?'--':st.pw+' W';
 if(st.gas){const L=v=>(+v).toLocaleString('es-ES',{maximumFractionDigits:v<10?2:1})+' l';
  $('gas').textContent='Gasoil (estimado): '+(st.on?L(st.gas[0])+' en este encendido':'último encendido '+L(st.gas[1]))+' · este mes '+L(st.gas[2])+' · total '+L(st.gas[3])}
 $('clock').textContent=st.tv?fecha(new Date(st.time*1000)):'sin hora';
 let w='';
 if(!st.on&&st.note)w+='<div class="warn">'+esc(st.note)+'</div>';
 if(st.bus===0)w+='<div class="warn">La Webasto no responde por W-Bus. Revisa el cable negro, la masa común y el módulo TJA1020.</div>';
 if(!st.tv)w+='<div class="warn">El reloj no está en hora y los programas no se ejecutarán. Pulsa «Poner en hora».</div>';
 $('warn').innerHTML=w;
 if(!busy){$('big').disabled=false;$('big').textContent=st.on?'Apagar':'Encender '+fmtDur(dur)}
 if(!dirty){sched=st.sch.map(a=>({en:!!a[0],days:a[1],start:a[2],dur:a[3]}));$('auto').checked=st.auto;list()}
 $('diag').textContent='Último envío: '+(st.tx||'-')+'\nÚltima respuesta: '+(st.rx||'-')+'\n\n'+st.log.join('\n');
 $('wifi').textContent=(st.ble?'App conectada por Bluetooth. ':'')+(st.sta?'Conectado a «'+st.ssid+'» ('+st.ip+', '+st.rssi+' dBm). ':'Sin red con internet. ')+'Red propia «'+st.name+'»: http://192.168.4.1';
 $('tgst').textContent=(st.tg?'Avisos activados.':'Avisos desactivados.')+' Solo salen si el ESP32 llega a la red con internet.'+(st.tgl?'\nÚltimo aviso: '+st.tgl:'');
 next()}
// Pinta la lista de programas (hora, duración, interruptor, borrar y días)
function list(){$('list').innerHTML=sched.map((s,i)=>`<div class="row"><div class="rtop">
<input type="time" value="${hm(s.start)}" data-i="${i}" data-k="start" aria-label="Hora de encendido">
<select data-i="${i}" data-k="dur" aria-label="Duración">${SDURS.map(d=>`<option value="${d}"${d==s.dur?' selected':''}>${fmtDur(d)}</option>`).join('')}</select>
<label class="sw"><input type="checkbox"${s.en?' checked':''} data-i="${i}" data-k="en" aria-label="Programa activo"><i></i></label>
<button class="x" data-i="${i}" data-k="del" aria-label="Borrar programa">×</button></div>
<div class="days">${DAYS.map((d,j)=>`<button class="${s.days>>j&1?'sel':''}" data-i="${i}" data-k="day" data-j="${j}">${d}</button>`).join('')}</div></div>`).join('')
 ||'<p class="sub">Añade un programa para que se encienda sola a una hora y unos días concretos.</p>';
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
$('add').onclick=()=>{if(sched.length>=8)return alert('Máximo 8 programas.');sched.push({en:true,days:31,start:420,dur:30});touch()};
// Interruptor general de los programas
$('auto').onchange=()=>touch(false);
// Guardar programas en la placa (formato "activo,días,inicio,duración;…")
$('save').onclick=async()=>{try{await api('/api/sched',{auto:$('auto').checked?1:0,list:sched.map(s=>[s.en?1:0,s.days,s.start,s.dur].join(',')).join(';')});
 dirty=false;$('save').disabled=true;$('save').textContent='Guardado';setTimeout(()=>$('save').textContent='Guardar programas',1500);poll()}catch(e){alert('No se pudo guardar: '+e.message)}};
// Calcula y muestra el próximo encendido programado
function next(){if(!st)return;const act=sched.filter(s=>s.en&&s.days);
 if(!act.length){$('next').textContent=sched.length?'Ningún programa activo.':'Sin programas.';return}
 if(!$('auto').checked){$('next').textContent='Programas desactivados.';return}
 const now=new Date(st.tv?st.time*1000:Date.now()),wd=(now.getDay()+6)%7,m=now.getHours()*60+now.getMinutes();let b=null;
 for(const s of act)for(let k=0;k<8;k++){const d=(wd+k)%7;if(!(s.days>>d&1)||(k==0&&s.start<=m))continue;const t=k*1440+s.start-m;if(!b||t<b.t)b={t,k,d,s};break}
 $('next').textContent=b?'Próximo encendido: '+(b.k==0?'hoy':b.k==1?'mañana':DAYSL[b.d])+' a las '+hm(b.s.start)+', durante '+fmtDur(b.s.dur)+'.':'Sin programas.'}
// Leer las averías guardadas en la Webasto
$('errs').onclick=async()=>{$('errout').textContent='Leyendo…';try{const r=await api('/api/errors');
 $('errout').textContent=(!r.ok?'La Webasto no respondió.':r.codes.length?r.codes.map(c=>'Código 0x'+c.c+' ('+c.n+' veces)').join('\n'):'Sin averías guardadas.')+'\nRespuesta: '+r.raw}catch(e){$('errout').textContent='Error: '+e.message}};
// Configuración: lee los ajustes de la placa (las claves no se devuelven nunca: los campos quedan vacíos)
async function loadCfg(){try{const c=await api('/api/cfg');
 $('c_name').value=c.name;$('c_pin').value=c.pin;$('c_wm').value=c.wifimode;$('c_ssid').value=c.ssid;$('c_chat').value=c.tgchat;$('c_mv').value=c.minvolt;
 $('c_tok').value='';$('c_ap').value='';$('c_pass').value='';$('c_tok').placeholder=c.tg?'guardado':'sin configurar';
 $('c_bonds').textContent=c.bonds?'Dispositivos emparejados: '+c.bonds+'.':'Ningún dispositivo emparejado todavía.';
 $('c_ver').textContent='Firmware WTTC '+c.ver+' · código generado íntegramente con Claude (Anthropic) · github.com/matatunos/wttc'}catch(e){alert('No se pudo leer la configuración: '+e.message)}}
// Al abrir el apartado Configuración se leen los ajustes
$('cfgd').ontoggle=()=>{if($('cfgd').open)loadCfg()};
// Guardar configuración: las claves vacías no se envían (la placa conserva las que tenía)
$('csave').onclick=async()=>{const b={name:$('c_name').value.trim(),pin:$('c_pin').value.trim(),wifimode:$('c_wm').value,ssid:$('c_ssid').value.trim(),tgchat:$('c_chat').value.trim(),minvolt:$('c_mv').value};
 if($('c_ap').value)b.appass=$('c_ap').value;if($('c_pass').value)b.pass=$('c_pass').value;if($('c_tok').value.trim())b.tgtok=$('c_tok').value.trim();
 try{alert(await api('/api/cfg',b));loadCfg()}catch(e){alert(e.message)}};
// Poner a cero el gasoil estimado
$('gasreset').onclick=async()=>{if(!confirm('¿Poner a cero el gasoil estimado (último encendido, mes y total)?'))return;try{alert(await api('/api/gasreset',{}));poll()}catch(e){alert(e.message)}};
// Borrar los móviles emparejados por Bluetooth
$('forget').onclick=async()=>{if(!confirm('¿Borrar todos los dispositivos emparejados? Habrá que volver a emparejar la app con el PIN.'))return;try{alert(await api('/api/forget',{}));loadCfg()}catch(e){alert(e.message)}};
// Aviso de prueba por Telegram
$('tgtest').onclick=async()=>{try{alert(await api('/api/tgtest',{}))}catch(e){alert(e.message)}};
// Poner la placa en hora con la del móvil
$('tsync').onclick=async()=>{try{await api('/api/time',{epoch:Math.floor(Date.now()/1000)});poll()}catch(e){alert(e.message)}};
// Pide el estado. La primera vez, si la placa no está en hora (o va desfasada), se la pone en hora
async function poll(){try{st=await api('/api/state');$('net').classList.remove('bad');
 if(!synced){synced=true;if(!st.tv||Math.abs(st.time-Date.now()/1000)>30){await api('/api/time',{epoch:Math.floor(Date.now()/1000)});st=await api('/api/state')}}
 render()}catch(e){$('net').classList.add('bad')}}
// Arranque: botones de duración, primer estado y repetir cada 3 s mientras la página esté visible
seg();poll();setInterval(()=>{if(!document.hidden)poll()},3000);
</script></body></html>)HTML";
