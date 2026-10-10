<?php
// i18n/es.php — Textos de la web pública en español (página y simulador). Generado con Claude (Anthropic).
// Mismas claves en en.php y de.php; lo que falte allí sale de aquí. {apk} y {stats} los rellena t() en index.php.
return [
'title' => 'WTTC · Webasto Thermo Top C desde el móvil con ESP32',
'desc' => <<<'TXT'
Controlador libre para la calefacción Webasto Thermo Top C de furgonetas y campers: placa ESP32 por W-Bus, app Android por Bluetooth y web por Wi-Fi.
TXT,
'ogTitle' => 'WTTC · Webasto Thermo Top C desde el móvil',
'h1' => '🔥 WTTC · Webasto Thermo Top C por Bluetooth y Wi-Fi',
'intro' => <<<'TXT'
Sustituye el temporizador original de la calefacción auxiliar Thermo Top C por un ESP32 que habla su protocolo W-Bus,
      y la maneja desde una app Android por Bluetooth o desde el navegador por Wi-Fi. Código abierto:
      <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">github.com/matatunos/wttc</a>.
      <br>Debajo, el <b>simulador</b>: a la izquierda, la web del firmware tal cual; a la derecha, una Webasto virtual que responde a sus tramas.
      Puedes acelerar el tiempo y provocar averías.
      <br><b>Todo el código y el contenido de WTTC</b> (firmware, app, servidor, esta web y su documentación) <b>están generados íntegramente con Claude</b> (Anthropic). Más abajo están las <a href="#descargas">descargas</a>, el <a href="#montaje">montaje</a>
      y las <a href="#uso">instrucciones de uso</a>. ¿Cuánta gente lo usa? <a href="{stats}">Estadísticas públicas</a>. ¿Tienes una placa? <a href="/mi.php">Mis estadísticas</a>.
      ¿Tu Webasto da una avería? <a href="{faults}">Códigos de avería de la Thermo Top C</a>.
      ¿Lo vas a montar? <a href="{install}">Instala el firmware desde el navegador</a>, sin Arduino IDE.
TXT,
'aviso' => <<<'TXT'
    <b>⚠️ Antes de nada: esto es un proyecto personal, no un producto.</b> WTTC manda sobre una calefacción que quema gasoil
    dentro de tu vehículo, y se ofrece tal cual, sin garantía de ningún tipo y en versión de prueba. Si decides montarlo,
    lo haces bajo tu entera responsabilidad. <a href="#responsabilidad">Lee la descarga de responsabilidad</a> antes de tocar un solo cable.
TXT,
'phoneTitle' => 'Web del ESP32 (firmware real)',
'phoneCap' => <<<'TXT'
Web servida por el ESP32 (copia literal del firmware).<br>Sus llamadas a <code>/api/…</code> las contesta el ESP32 simulado.
TXT,
'simTime' => 'tiempo simulado',
'pause' => '⏸ Pausa',
'rebootTitle' => 'Corta y devuelve la alimentación del ESP32',
'rebootBtn' => '↻ Reiniciar ESP32',
'heaterVirtual' => 'Webasto Thermo Top C (virtual)',
'water' => 'agua del motor',
'off' => 'Apagada',
'kPower' => 'Potencia',
'kBatt' => 'Batería',
'kAmp' => 'Consumo',
'kRun' => 'Orden activa',
'chartAria' => 'Temperatura del agua en la última hora simulada',
'tgSim' => 'Telegram (simulado)',
'tgHint' => 'Lo que te llegaría al móvil. En el simulador no se envía nada de verdad.',
'faultsTitle' => 'Provocar averías',
'faults' => <<<'TXT'
          <label><input type="checkbox" data-f="wbus"><span>Cable W-Bus suelto<small>El TJA1020 sigue devolviendo el eco, pero la Webasto no contesta.</small></span></label>
          <label><input type="checkbox" data-f="slp"><span>SLP del TJA1020 al aire<small>El transceptor duerme: ni eco ni respuesta.</small></span></label>
          <label><input type="checkbox" data-f="cross"><span>TX/RX cruzados<small>Fallo típico con la serigrafía TX/RX de la placa.</small></span></label>
          <label><input type="checkbox" data-f="power"><span>Webasto sin alimentación<small>Fusible de la unidad fundido: la Webasto no contesta a nada.</small></span></label>
          <label><input type="checkbox" data-f="fuel"><span>Sin gasoil / bomba atascada<small>No prende: avería 0x02 y, tras 3 arranques fallidos, bloqueo.</small></span></label>
          <label><input type="checkbox" data-f="noise"><span>Ruido en el bus<small>1 de cada 4 respuestas llega con el checksum mal.</small></span></label>

TXT,
'env' => <<<'TXT'
          <span>Temperatura exterior <input type="range" id="amb" min="-15" max="40" step="1" value="5"> <b id="ambv">5 °C</b></span>
          <span>Batería en reposo <input type="range" id="bat" min="11.0" max="13.0" step="0.1" value="12.6"> <b id="batv">12,6 V</b></span>
          <button class="b" id="unlock" title="Procedimiento del manual: quitar el fusible F2 (20 A) 5 s">Desbloquear (fusible F2)</button>
          <button class="b" id="clrerr" title="Como haría VCDS en el módulo 18">Borrar averías (VCDS)</button>

TXT,
'traffic' => 'Tráfico W-Bus · 2400 baudios 8E1',
'trRep' => ' keep-alive y sensores',
'clear' => 'Limpiar',
'traceHint' => <<<'TXT'
Azul, lo que envía el ESP32 (<code>F4</code> = de diagnóstico a calefactor). Gris, el eco que el firmware descarta. Verde, la respuesta de la Webasto (<code>4F</code>, comando | 0x80). El último byte es el XOR de todos los anteriores.
TXT,
'serial' => 'Consola serie (115200)',
'conPh' => 'on 30 · off · status · info · errores',
'conAria' => 'Orden para la consola serie',
'send' => 'Enviar',
'conHint' => <<<'TXT'
Es la misma consola que usarás en la primera prueba, con el ESP32 conectado al portátil por USB.
TXT,
'wiring' => 'Esquema de conexiones (para montar)',
'svgAria' => <<<'TXT'
Esquema de montaje: conector del mando, placa TJA1020, regulador LM2596 y ESP32, con los cables numerados
TXT,
'svgConn' => 'Conector del mando',
'svgConnSub' => '(lado furgo, quitando el mando)',
'svgSilk' => <<<'TXT'
<text x="32" y="263" class="silk">marrón</text><text x="32" y="303" class="silk">negro</text><text x="32" y="383" class="silk">rojo</text><text x="32" y="423" class="silk">amarillo</text>
TXT,
'svgNoPower' => '⚠ La Webasto no tiene corriente (fusible)',
'svgBoard' => 'Placa TJA1020 (Gutol)',
'svgBoardSub' => 'bornas de tornillo a los dos lados',
'svgInh' => 'INH: no se conecta',
'svgReg' => 'Regulador LM2596',
'svgScrew' => 'tornillo:',
'svg5v' => '5,0 V',
'svgEsp' => 'ESP32-S3 DevKitC-1 N16R8 (44 pines)',
'svgEspSub' => 'dibujado tumbado: antena a la izquierda, USB a la derecha',
'svgYellow' => 'amarillo: aislar con cinta, no se usa',
'svgWbus' => 'W-Bus suelto',
'svgSlp' => 'SLP al aire: falta el ⑩',
'svgCross' => '⑧ y ⑨ cruzados',
'svgOpt' => 'Opcional: pantalla y termómetro (bus I2C)',
'svgOptSub' => 'cables discontinuos: el montaje funciona sin ellos',
'svgOled' => 'pantalla OLED I2C (SSD1327 1,5")',
'svgSens' => 'termómetro SHT31 o AHT20',
'wireHint' => <<<'TXT'
Las placas pueden venir con las bornas en otro sitio: <b>guíate por lo que pone impreso al lado de cada borna o pin</b>, no por su posición en el dibujo. El dibujo es del <b>ESP32-S3 DevKitC-1</b>: todos los pines que usa WTTC están en el mismo lado. En las bases con bornas de tornillo vienen rotulados (a veces el de 5 V pone «5Vin»). Desde la versión 0.2.0 solo vale el <b>ESP32-S3</b>. Lo que está en el <b>recuadro discontinuo</b> (pantalla y termómetro, cables ⑫–⑮) es <b>opcional</b>: el montaje funciona sin ello.
  Los colores de los cables ⑥–⑪ son una sugerencia (cables dupont). Los del conector de la furgo están <b>sin confirmar: mídelos con el polímetro</b>.
TXT,
'cables' => <<<'TXT'
<h2 class="sec">Lista de cables</h2>
<table class="cables"><thead><tr><th>Nº</th><th>Cable</th><th>Desde</th><th>Hasta</th></tr></thead><tbody>

<tr><td>1</td><td><span class="sw-c" style="background:#a0703c"></span>marrón</td><td>marrón de la furgo (masa)</td><td>Placa TJA1020, borna <b>GND</b> (lado 12V/LIN)</td></tr>
<tr><td>2</td><td><span class="sw-c" style="background:#111318"></span>negro</td><td>negro de la furgo (W-Bus)</td><td>Placa TJA1020, borna <b>LIN</b></td></tr>
<tr><td>3</td><td><span class="sw-c" style="background:#e04848"></span>rojo</td><td>rojo de la furgo (+12 V)</td><td>Placa TJA1020, borna <b>12V</b></td></tr>
<tr><td>4</td><td><span class="sw-c" style="background:#e04848"></span>rojo</td><td>rojo de la furgo (empalme del ③)</td><td>LM2596, <b>IN+</b></td></tr>
<tr><td>5</td><td><span class="sw-c" style="background:#a0703c"></span>marrón</td><td>marrón de la furgo (empalme del ①)</td><td>LM2596, <b>IN−</b></td></tr>
<tr><td>6</td><td><span class="sw-c" style="background:#ff9f1c"></span>naranja</td><td>LM2596 <b>OUT+</b> (5,0 V)</td><td>ESP32, pin <b>5V</b></td></tr>
<tr><td>7</td><td><span class="sw-c" style="background:#9aa3b5"></span>gris</td><td>LM2596 <b>OUT−</b></td><td>ESP32, pin <b>GND</b> (cualquiera)</td></tr>
<tr><td>8</td><td><span class="sw-c" style="background:#3ecf8e"></span>verde</td><td>Placa TJA1020, borna <b>TX</b></td><td>ESP32, pin <b>IO16</b> (RX2)</td></tr>
<tr><td>9</td><td><span class="sw-c" style="background:#4aa8ff"></span>azul</td><td>Placa TJA1020, borna <b>RX</b></td><td>ESP32, pin <b>IO17</b> (TX2)</td></tr>
<tr><td>10</td><td><span class="sw-c" style="background:#b57bff"></span>violeta</td><td>Placa TJA1020, borna <b>SLP</b></td><td>ESP32, pin <b>3V3</b></td></tr>
<tr><td>11</td><td><span class="sw-c" style="background:#9aa3b5"></span>gris</td><td>Placa TJA1020, borna <b>GND</b> (lado TX/RX)</td><td>ESP32, pin <b>GND</b> (otro, o el mismo)</td></tr>
<tr><td>—</td><td><span class="sw-c" style="background:#e8d23a"></span>amarillo</td><td>amarillo de la furgo</td><td>no se usa: aíslalo con cinta</td></tr>
<tr><td>—</td><td>—</td><td>Placa TJA1020, borna <b>INH</b></td><td>no se conecta</td></tr>
<tr class="opt"><td colspan="4"><b>Opcional</b>: pantalla y termómetro (recuadro discontinuo del esquema). Los cuatro van al mismo bus I2C: cada cable une el pin del ESP32 con el de la pantalla <b>y</b> el del termómetro.</td></tr>
<tr><td>12</td><td><span class="sw-c" style="background:#ff7aa8"></span>rosa</td><td>ESP32, pin <b>3V3</b> (el otro)</td><td>Pantalla <b>VCC</b> y termómetro <b>VIN</b></td></tr>
<tr><td>13</td><td><span class="sw-c" style="background:#9aa3b5"></span>gris</td><td>ESP32, pin <b>GND</b> (o empalme del ⑦)</td><td>Pantalla <b>GND</b> y termómetro <b>GND</b></td></tr>
<tr><td>14</td><td><span class="sw-c" style="background:#2ec4b6"></span>turquesa</td><td>ESP32, pin <b>IO4</b> (SDA)</td><td>Pantalla <b>SDA</b> y termómetro <b>SDA</b></td></tr>
<tr><td>15</td><td><span class="sw-c" style="background:#e9c46a"></span>dorado</td><td>ESP32, pin <b>IO5</b> (SCL)</td><td>Pantalla <b>SCL</b> y termómetro <b>SCL</b></td></tr>
</tbody></table>
TXT,
'steps' => <<<'TXT'
<h2 class="sec">Pasos, en orden</h2>
<ol class="pasos">
<li><b>Antes de nada, comprueba que tu calefacción habla W-Bus.</b> La de las VW T5 con temporizador de fábrica (ref. 7H0 010 398 J) lo hace, y hay quien ya la ha encendido con un ESP, pero también se dice que algunas Thermo Top C antiguas usan otro protocolo. La <b>primera prueba</b> de más abajo (solo <code>status</code> y <code>errores</code>) lo confirma sin encender nada: si la Webasto contesta con su temperatura y su tensión, adelante; si solo sale el eco y «sin respuesta» con el cableado ya revisado, puede que la tuya no sea de W-Bus.</li>
<li><b>Mide el conector del mando</b> con el polímetro, con la Webasto conectada: un cable da +12 V fijos (el «rojo»), otro tiene continuidad con el chasis (el «marrón») y el W-Bus en reposo marca casi lo mismo que la batería (el «negro»). Si los colores no coinciden, manda lo que midas, no el dibujo.</li>
<li><b>Ajusta el LM2596 antes de conectarle nada:</b> pon solo los cables ④ y ⑤, mide entre OUT+ y OUT− y gira el tornillo dorado hasta que marque <b>5,0 V</b>. Si te pasas, el ESP32 se quema.</li>
<li>Desconecta, y monta la placa TJA1020: ① ② ③ en la borna del lado 12V/LIN (INH vacía) y ⑧ ⑨ ⑩ ⑪ en la del otro lado.</li>
<li>Por último, ⑥ y ⑦ al ESP32. Para la <b>primera prueba</b> no los pongas: alimenta el ESP32 por USB desde el portátil (en el ESP32-S3, por el USB marcado UART o COM) y escribe <code>status</code> en la consola.</li>
<li>Si la consola no muestra ni el eco, <b>intercambia ⑧ y ⑨</b> (pasa a menudo con la serigrafía TX/RX de estas placas). Si sale eco pero «sin respuesta», revisa ② y la masa.</li>
<li><b>Opcional, cuando lo básico funcione:</b> la pantalla OLED y el termómetro (SHT31 o AHT20) con ⑫–⑮. La placa los detecta sola al arrancar (o a los 30 s de conectarlos). El termómetro, a un palmo de la placa y del regulador, que calientan. Sin termómetro todo funciona igual, salvo «calentar hasta X °C».</li>
</ol>
TXT,
'dlTitle' => 'Descargas',
'version' => 'versión',
'dlFw' => 'Firmware para el ESP32',
'dlFwSub' => 'Carpeta <code>WTTC/</code> con <code>WTTC.ino</code> y <code>web.h</code>',
'dlApp' => 'App Android (APK)',
'dlAppSub' => <<<'TXT'
Android 8 o posterior · móvil, tablet o radio de coche · sin placa, prueba el modo demostración
TXT,
'dlOta' => 'Actualización sin cable (.ota)',
'dlOtaSub' => 'Para la web de la placa (Configuración → Actualizar firmware), si no puede descargarla sola. Firmada: solo se instala la oficial',
'dlOtaSoon' => 'Se está publicando: vuelve en unos minutos',
'dlSrc' => 'Código fuente',
'dlSrcSub' => 'Firmware, app y esta documentación · software libre, licencia GPL v3',
'versions' => 'Versiones y cambios',
'noData' => 'Sin datos.',
'allVersions' => 'Todas las versiones publicadas: ',
'dlHint' => <<<'TXT'
Cada cambio del firmware se compila automáticamente en GitHub con el núcleo ESP32 3.3.12, y la app se publica en
      <a href="https://github.com/matatunos/wttc/releases" target="_blank" rel="noopener">Releases</a>.
      <b>Versión de prueba (0.x): aún no se ha probado contra una Webasto real.</b> La 1.0 llegará cuando alguien lo haya probado montado;
      si lo montas, cuéntanos cómo te ha ido abriendo una incidencia en GitHub.
TXT,
'hw' => <<<'TXT'
<h2 class="sec">Hardware usado</h2>
<table class="hw"><thead><tr><th>Pieza</th><th>Modelo usado</th><th>Para qué</th></tr></thead><tbody>
<tr><td>Vehículo y calefactor</td><td>VW T5 con calefacción auxiliar de agua <b>Webasto Thermo Top C</b> de fábrica (ref. VW <b>7H0 010 398 J</b>), mandada por W-Bus desde el temporizador original</td><td>Lo que se controla. El ESP32 sustituye al temporizador y se enchufa en su conector</td></tr>
<tr><td>Microcontrolador</td><td><b>ESP32-S3 DevKitC-1 N16R8</b> (16 MB de flash; mejor sobre una base con bornas de tornillo, que no se sueltan con las vibraciones) — desde la versión 0.2.0, el único soportado</td><td>Wi-Fi, web, programas, avisos y protocolo W-Bus por su UART2 (IO16/IO17)</td></tr>
<tr><td>Transceptor W-Bus</td><td>Módulo <b>UART ↔ LIN/K-Line con TJA1020</b> (el usado es de Gutol: bornas de tornillo 12V/GND/LIN/INH y TX/RX/SLP/GND, sin pin VCC)</td><td>Adapta el TTL de 3,3 V del ESP32 al bus de un hilo a 12 V. Vale cualquier módulo con TJA1020 o equivalente (TJA1021, MCP2003, L9637D)</td></tr>
<tr><td>Opcional: pantalla</td><td><b>OLED I2C SSD1327 de 1,5"</b> (128×128, 16 grises; la recomendada) o una de 128×64: 1,3" (SH1106) o 0,96" (SSD1306). Dirección 0x3C o 0x3D</td><td>Temperatura de dentro, estado, agua, batería y lo siguiente que va a pasar. Se elige el tipo en Configuración</td></tr>
<tr><td>Opcional: termómetro</td><td>Módulo I2C <b>SHT31</b> o <b>AHT20</b> (temperatura y humedad)</td><td>Temperatura de dentro: «calentar hasta X °C», hora de salida más afinada y aviso de condensación a la vista</td></tr>
<tr><td>Opcional: botón «calentar»</td><td>Cualquier <b>pulsador</b> (normalmente abierto), entre <b>IO7</b> y <b>GND</b></td><td>Encender (30 min, o lo que elijas en Configuración) o apagar sin el móvil, como la tecla de calentar del mando original. Hay que mantenerlo medio segundo</td></tr>
<tr><td>Alimentación</td><td>Regulador reductor <b>LM2596</b> (módulo con potenciómetro), ajustado a <b>5,0 V</b></td><td>Saca los 5 V del ESP32 del +12 V permanente del conector</td></tr>
<tr><td>Cableado</td><td>Cables dupont hembra para el ESP32, cable de 0,5 mm² para el lado de 12 V, empalmes o regletas y cinta o termorretráctil</td><td>Conexiones según el esquema y la lista de cables de arriba</td></tr>
<tr><td>Herramientas</td><td>Polímetro, destornillador pequeño, PC con USB (Linux o Windows)</td><td>Identificar los cables del conector, ajustar el LM2596, programar el ESP32 y la primera prueba por consola</td></tr>
</tbody></table>
TXT,
'hwHint' => <<<'TXT'
Opcional: un <b>interruptor</b> en el +12 V del ESP32 si el vehículo va a estar parado semanas (ver «Consumo» más abajo) y un <b>fusible de 1 A de acción lenta</b> en línea. El +12 V del conector del mando es permanente (con el contacto quitado), que es el que hace falta; el fusible que lleva de fábrica varía (en los kits Webasto suele ser pequeño, a menudo de 1 A; en la T5 de fábrica sale de la caja de fusibles del coche y depende del año), así que pon el de 1 A aunque ya haya otro mayor: protege los cables finos de la placa. La placa gasta unos 0,1–0,2 A a 12 V (algo más en picos breves al transmitir); por el W-Bus solo van datos. No hace falta VCDS para usarlo, pero sirve para leer y borrar averías del módulo 18 (calefacción auxiliar).
TXT,
'instNeed' => <<<'TXT'
<h2 class="sec">Instalar el firmware: qué necesitas</h2>
<ul class="pasos">
<li><b>Lo más fácil:</b> <a href="{install}">instalarlo desde el navegador</a> (Chrome o Edge, un clic, sin nada de lo que sigue). Lo de abajo es para hacerlo con Arduino IDE.</li>
<li><b>Arduino IDE 2</b> (lo más sencillo) o <b>arduino-cli</b> (solo terminal).</li>
<li>El <b>núcleo ESP32 de Espressif</b> («esp32 by Espressif Systems»), versión 2.x o 3.x. Lo descarga el propio IDE: son varios cientos de MB.</li>
<li>Placa <b>ESP32S3 Dev Module</b> con el esquema de partición <b>Huge APP (3MB No OTA/1MB SPIFFS)</b>: con Bluetooth y Wi-Fi el programa no cabe en la partición normal.</li>
<li>Un <b>cable USB de datos</b>: muchos cables de cargar no llevan datos y el PC no ve la placa.</li>
<li><b>Driver USB-serie</b>: el ESP32-S3 tiene dos USB; usa el marcado <b>UART</b> o <b>COM</b>, que lleva un CH343 o un CP2102 (el otro es el USB del propio chip). En Linux funciona sin instalar nada; en Windows 10/11 suele instalarse solo y, si no aparece puerto COM, instala el «CP210x VCP driver» de Silicon Labs.</li>
<li>Ninguna librería aparte: Bluetooth, Wi-Fi, servidor web y Preferences vienen con el núcleo ESP32.</li>
</ul>
TXT,
'instIde' => <<<'TXT'
<h2 class="sec">Con Arduino IDE 2 (Linux y Windows)</h2>
<ol class="pasos">
<li>Instala el IDE desde <a href="https://www.arduino.cc/en/software" target="_blank" rel="noopener">arduino.cc/en/software</a>. En Windows sirve el instalador .exe o <code>winget install ArduinoSA.IDE.stable</code>; en Linux, el AppImage (dale permiso de ejecución).</li>
<li><b>Archivo → Preferencias → «URLs adicionales del gestor de placas»</b>, añade:<br><code class="sel">https://espressif.github.io/arduino-esp32/package_esp32_index.json</code></li>
<li><b>Herramientas → Placa → Gestor de placas</b>, busca «esp32» e instala <b>esp32 de Espressif Systems</b>, versión <b>3.x</b> (la 2.x ya no vale: si la tienes, actualízala ahí mismo).</li>
<li>Descomprime el zip: queda la carpeta <code>WTTC/</code> con <code>WTTC.ino</code> y <code>web.h</code> (el IDE exige que la carpeta se llame como el .ino). Abre <code>WTTC.ino</code>.</li>
<li><b>Herramientas → Placa → esp32 → ESP32S3 Dev Module</b> y <b>Flash Size → 16MB</b>; <b>PSRAM → OPI PSRAM</b>; <b>Herramientas → Partition Scheme → Huge APP (3MB No OTA/1MB SPIFFS)</b> (solo para el límite de tamaño: la carpeta <code>WTTC/</code> trae su propia tabla de particiones, con dos huecos para las actualizaciones sin cable, y Arduino la usa sola); y en <b>Puerto</b> el del ESP32 (<code>COMx</code> en Windows, <code>/dev/ttyUSB0</code> en Linux).</li>
<li>Pulsa <b>Subir</b> (la flecha). Si se queda en «Connecting……», mantén pulsado el botón <b>BOOT</b> de la placa hasta que empiece a escribir.</li>
<li><b>Herramientas → Monitor serie</b> a <b>115200</b> baudios: sale un resumen con la versión, el <b>PIN Bluetooth</b>, la Wi-Fi de la placa y su clave, y el usuario y la clave de la web (apúntalos; con <code>info</code> sale otra vez). Escribe <code>status</code> para la primera prueba.</li>
</ol>
TXT,
'cliLinux' => <<<'TXT'
<h2 class="sec">Con arduino-cli en Linux</h2>
<pre class="cmd">curl -fsSL https://raw.githubusercontent.com/arduino/arduino-cli/master/install.sh | BINDIR=~/.local/bin sh
arduino-cli config init
arduino-cli config add board_manager.additional_urls \
  https://espressif.github.io/arduino-esp32/package_esp32_index.json
arduino-cli core update-index
arduino-cli core install esp32:esp32

# dentro de la carpeta que contiene WTTC/
# ESP32-S3
FQBN=esp32:esp32:esp32s3:FlashSize=16M,PSRAM=opi,PartitionScheme=huge_app
arduino-cli compile --fqbn $FQBN WTTC
arduino-cli upload  --fqbn $FQBN -p /dev/ttyUSB0 WTTC
arduino-cli monitor -p /dev/ttyUSB0 -c baudrate=115200</pre>
<div class="hint">Si da «Permission denied» en <code>/dev/ttyUSB0</code>: <code>sudo usermod -aG dialout $USER</code> y cierra sesión. En Ubuntu, si el puerto aparece y desaparece, desinstala <code>brltty</code>.</div>
TXT,
'cliWin' => <<<'TXT'
<h2 class="sec">Con arduino-cli en Windows (PowerShell)</h2>
<pre class="cmd">winget install ArduinoSA.CLI
arduino-cli config init
arduino-cli config add board_manager.additional_urls `
  https://espressif.github.io/arduino-esp32/package_esp32_index.json
arduino-cli core update-index
arduino-cli core install esp32:esp32

# dentro de la carpeta que contiene WTTC\
# ESP32-S3
$FQBN = "esp32:esp32:esp32s3:FlashSize=16M,PSRAM=opi,PartitionScheme=huge_app"
arduino-cli compile --fqbn $FQBN WTTC
arduino-cli upload  --fqbn $FQBN -p COM3 WTTC
arduino-cli monitor -p COM3 -c baudrate=115200</pre>
<div class="hint">Cambia <code>COM3</code> por el puerto que salga en <code>arduino-cli board list</code> (o en el Administrador de dispositivos → Puertos COM). Tras instalar con winget, abre una PowerShell nueva para que encuentre <code>arduino-cli</code>.</div>
TXT,
'howTo' => 'Cómo manejarlo',
'useAndroid' => <<<'TXT'
<h3 class="sub3">📱 Móvil o tablet Android (app, por Bluetooth)</h3>
        <ol class="pasos">
          <li>Descarga <a href="{apk}">WTTC.apk</a> en el móvil y ábrelo. Android pedirá permiso para «instalar apps de origen desconocido» desde el navegador: concédelo para esta instalación.</li>
          <li>Abre WTTC y acepta el permiso de <b>dispositivos cercanos</b> (Bluetooth) y el de notificaciones.</li>
          <li>Pulsa <b>Buscar placa</b>, elige tu WTTC y escribe su <b>PIN de 6 cifras</b> (sale en el monitor serie al arrancar y en la web de la placa, apartado Configuración).</li>
          <li>Listo: a partir de ahí la app se conecta sola cuando la placa está al alcance (10–20 m con la carrocería de por medio) y la pone en hora.</li>
          <li>Si la calefacción se apaga sola con la app abierta, Android muestra un aviso con el motivo y sus códigos de avería.</li>
          <li>¿Aún sin placa? <b>Probar sin placa</b> abre el modo demostración: una placa simulada para ver cómo funciona la app.</li>
          <li><b>Actualizar el firmware:</b> Configuración → <b>Buscar actualizaciones</b>. Si hay versión nueva, enseña sus novedades y, si aceptas, la placa la descarga, la instala y se reinicia sola (tiene que estar unida a una red con internet). Con Telegram configurado, además te avisa ella cuando sale una.</li>
        </ol>
TXT,
'useIphone' => <<<'TXT'
<h3 class="sub3">🍏 iPhone (web, por Wi-Fi)</h3>
        <ol class="pasos">
          <li>No hay app para iPhone: se usa la web de la placa, que es la misma que ves en el simulador.</li>
          <li>Ajustes → Wi-Fi → red <b>WTTC</b> (clave de fábrica <code>calefaccion</code>: la primera vez la web te pide cambiarla antes de dejarte manejarla) y abre <b>http://192.168.4.1</b> en Safari.</li>
          <li>Compartir → <b>Añadir a pantalla de inicio</b>: queda como una app más.</li>
          <li>Para que la red de la placa esté disponible cuando la necesites, deja la Wi-Fi en «Siempre encendida» o «Mientras calienta» (ver consumo). En «Solo a petición» solo está los 10 minutos tras arrancar.</li>
          <li>Si configuras la placa para unirse al punto de acceso del iPhone, también responde en <b>http://wttc.local</b> sin cambiar de red.</li>
          <li>Para actualizar: Configuración → <b>Buscar actualizaciones</b> (con la placa unida a una red con internet), o sube el fichero <code>.ota</code> de la última versión desde ese mismo apartado.</li>
        </ol>
TXT,
'useRadio' => <<<'TXT'
<h3 class="sub3">🚐 Radio Android del vehículo</h3>
        <ol class="pasos">
          <li><b>Primero comprueba</b> que su Bluetooth sirve para apps: instala <b>nRF Connect</b> (Play Store, gratis) en la radio y pulsa Scan. Si aparecen dispositivos BLE, la app WTTC funcionará.</li>
          <li>Instala el APK: descárgalo desde el navegador de la radio o cópialo en un pendrive y ábrelo con su gestor de archivos.</li>
          <li>Empareja igual que en el móvil. La pantalla se adapta a la radio (columna centrada, botones grandes).</li>
          <li>Si nRF Connect <b>no ve nada</b>, el Bluetooth de la radio es un módulo aparte, solo para llamadas y música. Usa la web: conecta la radio a la red Wi-Fi <b>WTTC</b> y abre http://192.168.4.1. Mientras tanto, la radio no tendrá internet por Wi-Fi.</li>
        </ol>
TXT,
'power' => <<<'TXT'
<h2 class="sec">Consumo y modos de la Wi-Fi</h2>
        <table class="hw"><thead><tr><th>Modo (Configuración)</th><th>Cuándo está la Wi-Fi</th><th>Consumo aprox. aparcado</th></tr></thead><tbody>
          <tr><td>Siempre encendida (por defecto)</td><td>Todo el tiempo</td><td>≈ 40–60 mA (1–1,5 Ah/día)</td></tr>
          <tr><td>Mientras calienta (lo mejor con la furgo parada días)</td><td>Calentando y 10 min después</td><td>≈ 15–25 mA solo con Bluetooth</td></tr>
          <tr><td>Solo a petición</td><td>Cuando la pides desde la app (15 min) y unos minutos para enviar avisos</td><td>≈ 15–25 mA</td></tr>
        </tbody></table>
        <div class="hint">Cifras estimadas a 12 V, LM2596 incluido; mide el tuyo con el polímetro en serie. En todos los modos la Wi-Fi está 10 minutos encendida al arrancar, como rescate si no tienes el móvil emparejado. El procesador va a 80 MHz para gastar menos.</div>
TXT,
'tgHow' => <<<'TXT'
<h2 class="sec">Avisos por Telegram (opcional)</h2>
        <ol class="pasos">
          <li>En Telegram, habla con <b>@BotFather</b>, envía <code>/newbot</code> y sigue los pasos: te da el <b>token</b> de tu bot.</li>
          <li>Abre el chat con tu bot nuevo y pulsa <b>Iniciar</b> (si no, el bot no puede escribirte).</li>
          <li>Tu <b>chat ID</b>: escribe algo a tu bot y abre <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code>; es el número de <code>"chat":{"id":…}</code>.</li>
          <li>En la app o la web de la placa → Configuración: token, chat ID y una <b>red con internet</b> (por ejemplo, el punto de acceso del móvil). Guarda y pulsa <b>Probar Telegram</b>.</li>
          <li>Avisa al encender, al apagar, si se apaga sola (con sus averías), si deja de responder y si un programa no arranca por batería baja.</li>
        </ol>
TXT,
'security' => <<<'TXT'
<h2 class="sec" style="margin-top:18px">Seguridad</h2>
    <ul class="pasos">
      <li>Bluetooth con <b>emparejamiento por PIN</b> y conexión cifrada: sin emparejar no se puede leer ni mandar nada. El PIN se genera al azar en cada placa la primera vez que arranca.</li>
      <li>Si pierdes el móvil: Configuración → <b>Borrar emparejamientos</b> (también por la consola serie: <code>forget</code>), y cambia el PIN.</li>
      <li>La Wi-Fi propia sale de fábrica con una clave pública (<code>calefaccion</code>): hasta que eliges otra, la web de la placa no deja manejarla ni enseña el PIN, y la app te la pide al conectar. El token de Telegram nunca se muestra después de guardarlo.</li>
      <li>La web de la placa solo acepta órdenes de sí misma (no de otras webs abiertas en el móvil), y los avisos de Telegram comprueban el certificado del servidor.</li>
      <li>Las actualizaciones sin cable van <b>firmadas</b>: la placa solo instala las oficiales, nunca una versión más antigua que la que tiene, ni mientras calienta. Si la nueva no aguanta un minuto funcionando, vuelve sola a la anterior.</li>
      <li>Estadísticas anónimas: la app pregunta al abrirla por primera vez (dos botones iguales, «Sí, enviar» o «No, gracias»). Qué se envía y los resultados, en <a href="{stats}">estadísticas públicas</a>.</li>
      <li>La Webasto mantiene sus propias protecciones: necesita el mensaje de mantenimiento cada pocos segundos (si la placa se cuelga o se desconecta, se apaga sola con su postbarrido), y cada encendido lleva su duración máxima (60 min).</li>
    </ul>
TXT,
'framesHead' => <<<'TXT'
<h2 class="sec">Tramas que usa el firmware</h2>
      <table>
        <thead><tr><th>Acción</th><th>Trama</th><th>Respuesta de la Webasto</th></tr></thead>
TXT,
'cCmd' => 'Comando (hex)',
'cDat' => 'Datos (hex)',
'cOut' => 'Trama con checksum',
'simNotes' => <<<'TXT'
<h2 class="sec">Qué se simula</h2>
      <ul class="notes">
        <li><b>Gasoil:</b> estimado con la potencia que informa la Webasto (≈ 0,62 l/h a 5 kW, según su ficha); la placa lleva el encendido actual, el último, el mes y el total. Es una estimación (±20 %), no una medida del depósito.</li>
        <li><b>No se simula:</b> el Bluetooth (la app Android) ni el apagado de la Wi-Fi por ahorro: aquí la web del móvil siempre llega a la placa.</li>
        <li><b>Fiel al código:</b> la web, la API y la consola son las del firmware: reintentos, eco, pulso de despertar tras 10 s de silencio, keep-alive cada 5 s, sensores cada 8 s, programas, corte por batería &lt; 12,0 V y reloj que se pierde al reiniciar.</li>
        <li><b>Fiel al protocolo:</b> formato de tramas y respuestas de sensores (0x50/05) y averías (0x56/01) según libwbus.</li>
        <li><b>Aproximado:</b> los tiempos de arranque y post-barrido, los umbrales de regulación (plena carga hasta 75 °C, parcial hasta 85 °C, reanuda a 70 °C), el calentamiento del circuito y que la Webasto se apague si pasan 20 s sin keep-alive (dato de libwbus, sin comprobar en la tuya).</li>
        <li><b>Estado real:</b> el firmware mira la respuesta del keep-alive (01 = la Webasto ya no tiene la orden) y la llama: «Arrancando», «Calentando», «En pausa» (sin llama con el agua a ≥ 65 °C) o «Sin respuesta». Si se apaga sola (prueba «sin gasoil»), lo dice en la web con sus averías y avisa por Telegram. Que la Thermo Top C conteste 01 al keep-alive viene de libwbus: si la tuya no lo hiciera, queda de respaldo la llama (5 min sin llama con el agua fría).</li>
      </ul>
TXT,
'legal' => <<<'TXT'
<h2 class="sec">Descarga de responsabilidad</h2>
    <div class="legal">
      <p><b>1. Objeto.</b> WTTC (en adelante, «el proyecto») comprende el firmware para ESP32 y ESP32-S3, la aplicación para
        Android, esta web y su simulador, los esquemas, la documentación y los servicios asociados (estadísticas anónimas y
        actualizaciones). Es un proyecto personal y sin ánimo de lucro de su autor (matatunos, titular según la licencia), que
        se pone a disposición del público de forma gratuita. No es un producto comercial: no se vende, no tiene servicio técnico
        ni de atención al usuario y no ha superado ninguna homologación, certificación ni ensayo de conformidad.</p>
      <p><b>2. Sin garantía.</b> El proyecto se ofrece «tal cual» y «según disponibilidad», bajo la licencia GPL v3 o posterior, sin garantía de
        ningún tipo, expresa ni implícita, incluidas, sin carácter limitativo, las de funcionamiento, comerciabilidad, idoneidad
        para un fin concreto, ausencia de errores, compatibilidad con un vehículo o una calefacción determinados y continuidad
        de los servicios. Mientras su número de versión empiece por 0 es una versión de prueba: compila y se ha comprobado en el
        simulador, pero no se ha probado instalada con una calefacción Webasto real.</p>
      <p><b>3. Exactitud de la información.</b> La documentación (colores y función de los cables, conexiones, consumos, códigos
        de avería, estimaciones de gasoil y cualquier otro dato técnico) se basa en fuentes públicas y en suposiciones que no se
        han comprobado en todos los vehículos. Puede contener errores u omisiones y no sustituye al manual del fabricante, al
        esquema eléctrico del vehículo ni al criterio de un profesional. Compruébala por tus propios medios antes de conectar
        nada.</p>
      <p><b>4. Exclusión de responsabilidad.</b> En la máxima medida permitida por la legislación aplicable, el autor no será
        responsable de ningún daño o perjuicio, directo o indirecto, incidental o consecuente, de cualquier naturaleza
        —incluidos, entre otros, lesiones personales, intoxicación por monóxido de carbono, incendio, daños en la calefacción,
        en el vehículo, en su batería o en su instalación eléctrica, pérdida de la garantía o de la cobertura del seguro,
        sanciones, inmovilización del vehículo, pérdida de datos, lucro cesante o gastos de reparación— que se derive de la
        descarga, la instalación, el uso, la imposibilidad de uso, la modificación o la distribución del proyecto, de errores
        de su software o de su documentación, o de la interrupción o el fallo de servicios de terceros de los que depende
        (como GitHub o Telegram), o que guarde relación con ellos.</p>
      <p><b>5. Responsabilidad del usuario.</b> Quien instala o usa el proyecto lo hace por decisión propia, bajo su exclusiva
        responsabilidad, y asume todos los riesgos. En particular, le corresponde:</p>
      <ul class="pasos">
        <li>Hacer o encargar la instalación con los conocimientos necesarios para trabajar con seguridad en instalaciones
          eléctricas de 12 V y cerca de sistemas de combustible, protegiendo la alimentación con un fusible.</li>
        <li><b>No programar ni encender la calefacción con el vehículo en un garaje ni en ningún recinto cerrado o mal
          ventilado.</b> Los gases de escape contienen monóxido de carbono, que no tiene olor y puede ser mortal.</li>
        <li>Vigilar el funcionamiento en los primeros usos y conservar siempre una forma de apagarla sin el proyecto, como
          quitar el fusible de la propia calefacción. (Según la documentación del protocolo W-Bus, sin el mensaje de
          mantenimiento la calefacción se apaga sola en unos segundos, pero no se ha comprobado en una instalación real.)</li>
        <li>Comprobar si la modificación afecta a la garantía del vehículo o de la calefacción, a su seguro o a la normativa
          que le sea aplicable, y dejar la instalación original en su estado si retira el proyecto.</li>
      </ul>
      <p><b>6. Software generado con inteligencia artificial.</b> Todo el código y el contenido del proyecto se han generado con
        Claude, un modelo de inteligencia artificial de Anthropic, a partir de las indicaciones del autor. Pueden contener errores
        que no se hayan detectado. Anthropic no forma parte del proyecto ni responde de él.</p>
      <p><b>7. Marcas.</b> Webasto, Thermo Top, Volkswagen, Climatronic, Espressif, ESP32, Arduino, Android, Telegram y GitHub son
        marcas o denominaciones de sus respectivos titulares. El proyecto no tiene relación con ellos ni cuenta con su
        aprobación o patrocinio; sus nombres se usan solo para indicar con qué equipos y servicios es compatible o funciona.</p>
      <p><b>8. Límites de esta exclusión.</b> Nada de lo anterior excluye ni limita la responsabilidad en los casos en que la ley no
        permite hacerlo, como la derivada de dolo o culpa grave. Si alguna parte de este texto se considerase nula o
        inaplicable, el resto seguirá siendo válido.</p>
      <p><b>9. Aceptación y ley aplicable.</b> Descargar, instalar o usar cualquier parte del proyecto implica haber leído y
        aceptado esta descarga de responsabilidad y la licencia GPL v3; si no estás de acuerdo, no lo uses. En lo no previsto
        aquí se aplica la legislación española. Si hubiera diferencias entre las versiones en distintos idiomas, prevalece
        la española.</p>
      <p class="muted">Este texto complementa, sin sustituirla, la exención de garantía y de responsabilidad de la licencia GPL v3 (cláusulas 15 y 16)
        (fichero LICENSE del repositorio).</p>
TXT,
'credit' => <<<'TXT'
WTTC · software libre (GPL v3) en <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">GitHub</a> ·
    código y contenido generados íntegramente con Claude (Anthropic) · proyecto personal sin relación con Webasto ni Volkswagen.
TXT,
'relGithub' => 'Release en GitHub ↗',
'verWord' => 'Versión',
'boardSim' => 'Placa: pantalla, LED y termómetro',
'oledAria' => 'Pantalla OLED de la placa simulada',
'ledLbl' => 'LED de la placa',
'bootTitle' => 'Botón BOOT de la placa: enciende la pantalla un minuto',
'cabNow' => 'Dentro (real)',
'hwScr' => 'Pantalla OLED conectada',
'hwSens' => 'Termómetro conectado',
'cabSet' => 'Poner dentro a',
'boardHint' => 'La pantalla se dibuja píxel a píxel como en la placa (la SSD1327 de 128×128 en grises, o la de 128×64 si la eliges en Configuración; mismas letras y mismos textos). En modo automático sigue encendida mientras la web de la izquierda está abierta, igual que en la placa real con su web abierta. Al quitar una pieza, la placa la da por desconectada a los pocos segundos y lo que depende de ella desaparece de la web; al volver a conectarla, la detecta en medio minuto.',
'testTitle' => 'Pruebas e informe en PDF',
'tAmb' => 'Fuera',
'tCab' => 'Dentro al empezar',
'tTgt' => 'Hasta',
'tNoTgt' => 'sin termostato',
'tDur' => 'Duración',
'tBatt' => 'Batería',
'tRun' => 'Hacer la prueba y descargar el PDF',
'tRecStart' => 'Grabar lo que hago',
'tRecStop' => 'Terminar y descargar el PDF',
'testHint' => '«Hacer la prueba» enciende con esos datos y adelanta el tiempo simulado hasta el final de golpe (unos segundos); luego descarga un PDF con gráficas de temperaturas, encendidos, potencia, gasoil y batería, la tabla de encendidos y el registro de la placa. «Grabar lo que hago» apunta lo que pase mientras usas el simulador (a cualquier velocidad) hasta que lo terminas. El modelo de calor es aproximado.',
'sim' => [
    'tRunning' => 'Haciendo la prueba…', 'tDone' => 'Prueba hecha: {0} encendidos, {1} l de gasoil. Descargando el PDF…', 'tNoSens' => 'Para «Hasta» hace falta el termómetro: márcalo como conectado en «Placa».', 'tBusy' => 'Ya se está grabando: termina antes la grabación.', 'tRecOn' => 'Grabando… usa el simulador y pulsa «Terminar» al acabar.', 'tPdfErr' => 'No se pudo crear el PDF.', 'tRecStart' => 'Grabar lo que hago', 'tRecStop' => 'Terminar y descargar el PDF',
    'oledProbe' => "detectando…\n(hasta 30 s)", 'oledDisabled' => "desactivada en Configuración\n(BOOT la enciende un minuto)", 'oledNone' => 'sin pantalla', 'oledOff' => "pantalla apagada\n(pulsa BOOT)",
    "st" => [
        "OFF" => 'Apagada',
        "FAN" => 'Arranque del ventilador',
        "GLOW" => 'Precalentando la bujía',
        "IGN" => 'Encendido: entra gasoil',
        "STAB" => 'Estabilizando la llama',
        "FULL" => 'Combustión a plena carga',
        "PART" => 'Combustión a carga parcial',
        "PAUSE" => 'Pausa de regulación',
        "AFTER" => 'Post-barrido (apagándose)',
        "FAIL" => 'Post-barrido por fallo',
        "LOCK" => 'Bloqueada (interlock)',
    ],
    "errn" => [
        "1" => 'unidad de control defectuosa',
        "2" => 'no arranca',
        "3" => 'fallo de llama',
        "4" => 'tensión demasiado alta',
        "6" => 'sobrecalentamiento',
        "7" => 'bloqueada (interlock)',
        "18" => 'fallo de comunicación W-Bus',
    ],
    "stopTime" => 'tiempo de la orden agotado',
    "stopKa" => '20 s sin keep-alive',
    "stopCmd" => 'orden de apagado',
    "stopF2" => 'fusible F2 quitado 5 s',
    "txOn" => 'Encender calefacción estacionaria {0} min',
    "txOff" => 'Apagar',
    "txKa" => 'Keep-alive: sigue con la orden 0x{0}',
    "txSens" => 'Leer sensores (registro 0x05)',
    "txReg" => 'Leer registro 0x{0}',
    "txErrList" => 'Leer lista de averías',
    "txErrClr" => 'Borrar averías',
    "txErr" => 'Averías',
    "txCmd" => 'Comando 0x{0}',
    "rxOn" => 'Aceptado: {0} min',
    "rxOff" => 'Apagado aceptado',
    "rxKaOff" => 'Orden NO activa (01): la Webasto ya no está calentando',
    "rxKaOn" => 'Orden activa (00)',
    "rxSens" => 'Agua {0} °C · {1} V · llama {2} · {3} W',
    "yes" => 'sí',
    "no" => 'no',
    "rxNoErr" => 'Sin averías guardadas',
    "rxErrs" => '{0} avería(s): {1}',
    "rxResp" => 'Respuesta',
    "noEchoSlp" => 'Sin eco: el TJA1020 está dormido (SLP sin conectar)',
    "noEchoCross" => 'Sin eco: TX/RX cruzados, la trama no llega al bus',
    "echo" => 'Eco del propio envío (descartado)',
    "noRxWbus" => 'Sin respuesta en 500 ms: el W-Bus no llega a la Webasto',
    "noRxPower" => 'Sin respuesta en 500 ms: la Webasto no tiene alimentación',
    "noRxCmd" => 'Sin respuesta: orden no soportada',
    "noise" => 'Llega con un bit cambiado: el checksum no cuadra y el firmware la descarta',
    "brk" => 'Pulso de despertar: TX a nivel bajo 50 ms (más de 10 s sin hablar)',
    "simulated" => ' (simulado)',
    "comps" => ['Ventilador', 'Bujía', 'Bomba de gasoil', 'Llama', 'Bomba de agua'],
    "booting" => 'ESP32 arrancando…',
    "realTime" => 'tiempo real',
    "timeX" => 'tiempo ×{0}',
    "paused" => 'en pausa',
    "upFor" => ' · ESP32 encendido hace {0}',
    "noPower" => 'Sin alimentación',
    "errMem" => 'Memoria de averías: ',
    "errMemEmpty" => 'Memoria de averías vacía.',
    "lastStop" => ' · Último apagado: {0}.',
    "lastHour" => 'última hora',
    "echoTag" => 'eco',
    "busEmpty" => <<<'TXT'
Aún no ha pasado nada por el bus. Enciende desde el móvil o escribe «status» en la consola.
TXT,
    "tgEmpty" => <<<'TXT'
Sin mensajes. Para verlos, en la web de la placa abre «Configuración» y pon una red con internet (cualquiera), un token y un chat inventados; guarda y espera a que reinicie.
TXT,
    "fr" => ['Encender 30 min', 'Apagar', 'Leer sensores', 'Leer averías'],
    "data" => '(datos)',
    "loc" => 'es-ES',
],
];
