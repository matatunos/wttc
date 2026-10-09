<?php
// i18n/en.php — Textos de la web pública en inglés (página y simulador). Generado con Claude (Anthropic).
// Mismas claves que es.php; lo que falte aquí sale en español. {apk} y {stats} los rellena t() en index.php.
return [
'title' => 'WTTC · Webasto Thermo Top C from your phone with ESP32',
'desc' => 'Open-source controller for the Webasto Thermo Top C heater in vans and campers: ESP32 board on the W-Bus, Android app over Bluetooth and web page over Wi-Fi.',
'ogTitle' => 'WTTC · Webasto Thermo Top C from your phone',
'h1' => '🔥 WTTC · Webasto Thermo Top C over Bluetooth and Wi-Fi',
'intro' => <<<'TXT'
Replaces the original timer of the Thermo Top C auxiliary heater with an ESP32 that speaks its W-Bus protocol,
      and controls it from an Android app over Bluetooth or from the browser over Wi-Fi. Open source:
      <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">github.com/matatunos/wttc</a>.
      <br>Below is the <b>simulator</b>: on the left, the firmware's web page as is; on the right, a virtual Webasto that answers its frames.
      You can speed up time and cause faults.
      <br><b>All of WTTC's code and content</b> (firmware, app, server, this website and its documentation) <b>is generated entirely with Claude</b> (Anthropic). Further down are the <a href="#descargas">downloads</a>, the <a href="#montaje">wiring</a>
      and the <a href="#uso">user guide</a>. How many people use it? <a href="{stats}">Public statistics</a> (in Spanish). Have a board? <a href="/mi.php">My statistics</a> (in Spanish).
      Is your Webasto showing a fault? <a href="{faults}">Thermo Top C fault codes</a>.
      Going to build it? <a href="{install}">Install the firmware from the browser</a>, no Arduino IDE needed.
TXT,
'aviso' => <<<'TXT'
    <b>⚠️ First of all: this is a personal project, not a product.</b> WTTC controls a heater that burns diesel
    inside your vehicle, and it is provided as is, without any warranty and as a test version. If you decide to build it,
    you do so entirely at your own risk. <a href="#responsabilidad">Read the disclaimer of liability</a> before touching a single wire.
TXT,
'phoneTitle' => 'ESP32 web page (real firmware)',
'phoneCap' => 'Web page served by the ESP32 (verbatim copy from the firmware).<br>Its calls to <code>/api/…</code> are answered by the simulated ESP32.',
'simTime' => 'simulated time',
'pause' => '⏸ Pause',
'rebootTitle' => 'Cuts and restores the ESP32 power',
'rebootBtn' => '↻ Restart ESP32',
'heaterVirtual' => 'Webasto Thermo Top C (virtual)',
'water' => 'engine coolant',
'off' => 'Off',
'kPower' => 'Power',
'kBatt' => 'Battery',
'kAmp' => 'Current',
'kRun' => 'Active command',
'chartAria' => 'Coolant temperature over the last simulated hour',
'tgSim' => 'Telegram (simulated)',
'tgHint' => 'What would reach your phone. Nothing is really sent from the simulator.',
'faultsTitle' => 'Cause faults',
'faults' => <<<'TXT'
          <label><input type="checkbox" data-f="wbus"><span>W-Bus wire loose<small>The TJA1020 still returns the echo, but the Webasto does not answer.</small></span></label>
          <label><input type="checkbox" data-f="slp"><span>TJA1020 SLP not connected<small>The transceiver sleeps: no echo and no answer.</small></span></label>
          <label><input type="checkbox" data-f="cross"><span>TX/RX swapped<small>A common mistake with the TX/RX labels printed on the board.</small></span></label>
          <label><input type="checkbox" data-f="power"><span>Webasto without power<small>The unit's fuse is blown: the Webasto answers nothing.</small></span></label>
          <label><input type="checkbox" data-f="fuel"><span>No diesel / pump stuck<small>It does not ignite: fault 0x02 and, after 3 failed starts, lockout.</small></span></label>
          <label><input type="checkbox" data-f="noise"><span>Noise on the bus<small>1 in 4 answers arrives with a bad checksum.</small></span></label>

TXT,
'env' => <<<'TXT'
          <span>Outside temperature <input type="range" id="amb" min="-15" max="40" step="1" value="5"> <b id="ambv">5 °C</b></span>
          <span>Resting battery <input type="range" id="bat" min="11.0" max="13.0" step="0.1" value="12.6"> <b id="batv">12.6 V</b></span>
          <button class="b" id="unlock" title="Procedure from the manual: pull fuse F2 (20 A) for 5 s">Unlock (fuse F2)</button>
          <button class="b" id="clrerr" title="As VCDS would do on module 18">Clear faults (VCDS)</button>

TXT,
'traffic' => 'W-Bus traffic · 2400 baud 8E1',
'trRep' => ' keep-alive and sensors',
'clear' => 'Clear',
'traceHint' => 'Blue: what the ESP32 sends (<code>F4</code> = from diagnostics to heater). Grey: the echo the firmware discards. Green: the Webasto\'s answer (<code>4F</code>, command | 0x80). The last byte is the XOR of all the previous ones.',
'serial' => 'Serial console (115200)',
'conPh' => 'on 30 · off · status · info · errores',
'conAria' => 'Command for the serial console',
'send' => 'Send',
'conHint' => 'It is the same console you will use for the first test, with the ESP32 connected to your laptop over USB. Its commands and messages are in Spanish.',
'wiring' => 'Wiring diagram (for building it)',
'svgAria' => 'Wiring diagram: controller connector, TJA1020 board, LM2596 regulator and ESP32, with numbered wires',
'svgConn' => 'Controller connector',
'svgConnSub' => '(van side, with the controller removed)',
'svgSilk' => '<text x="32" y="263" class="silk">brown</text><text x="32" y="303" class="silk">black</text><text x="32" y="383" class="silk">red</text><text x="32" y="423" class="silk">yellow</text>',
'svgNoPower' => '⚠ The Webasto has no power (fuse)',
'svgBoard' => 'TJA1020 board (Gutol)',
'svgBoardSub' => 'screw terminals on both sides',
'svgInh' => 'INH: not connected',
'svgReg' => 'LM2596 regulator',
'svgScrew' => 'screw:',
'svg5v' => '5.0 V',
'svgEsp' => 'ESP32-S3 DevKitC-1 N16R8 (44 pins)',
'svgEspSub' => 'drawn lying down: antenna on the left, USB on the right',
'svgYellow' => 'yellow: insulate with tape, not used',
'svgWbus' => 'W-Bus loose',
'svgSlp' => 'SLP floating: ⑩ is missing',
'svgCross' => '⑧ and ⑨ swapped',
'svgOpt' => 'Optional: display and thermometer (I2C bus)',
'svgOptSub' => 'dashed wires: the build works without them',
'svgOled' => 'I2C OLED display (SSD1327 1.5")',
'svgSens' => 'SHT31 or AHT20 thermometer',
'wireHint' => <<<'TXT'
Boards may come with the terminals in other places: <b>go by what is printed next to each terminal or pin</b>, not by its position in the drawing. The drawing shows the <b>ESP32-S3 DevKitC-1</b>: all the pins WTTC uses are on the same side. Screw-terminal bases have them labelled (the 5 V one sometimes says “5Vin”). Since version 0.2.0 only the <b>ESP32-S3</b> is supported. What is inside the <b>dashed box</b> (display and thermometer, wires ⑫–⑮) is <b>optional</b>: the build works without it.
  The colours of wires ⑥–⑪ are a suggestion (dupont wires). Those of the van's connector are <b>unconfirmed: measure them with a multimeter</b>.
TXT,
'cables' => <<<'TXT'
<h2 class="sec">Wire list</h2>
<table class="cables"><thead><tr><th>No.</th><th>Wire</th><th>From</th><th>To</th></tr></thead><tbody>

<tr><td>1</td><td><span class="sw-c" style="background:#a0703c"></span>brown</td><td>van's brown (ground)</td><td>TJA1020 board, terminal <b>GND</b> (12V/LIN side)</td></tr>
<tr><td>2</td><td><span class="sw-c" style="background:#111318"></span>black</td><td>van's black (W-Bus)</td><td>TJA1020 board, terminal <b>LIN</b></td></tr>
<tr><td>3</td><td><span class="sw-c" style="background:#e04848"></span>red</td><td>van's red (+12 V)</td><td>TJA1020 board, terminal <b>12V</b></td></tr>
<tr><td>4</td><td><span class="sw-c" style="background:#e04848"></span>red</td><td>van's red (spliced from ③)</td><td>LM2596, <b>IN+</b></td></tr>
<tr><td>5</td><td><span class="sw-c" style="background:#a0703c"></span>brown</td><td>van's brown (spliced from ①)</td><td>LM2596, <b>IN−</b></td></tr>
<tr><td>6</td><td><span class="sw-c" style="background:#ff9f1c"></span>orange</td><td>LM2596 <b>OUT+</b> (5.0 V)</td><td>ESP32, pin <b>5V</b></td></tr>
<tr><td>7</td><td><span class="sw-c" style="background:#9aa3b5"></span>grey</td><td>LM2596 <b>OUT−</b></td><td>ESP32, pin <b>GND</b> (any)</td></tr>
<tr><td>8</td><td><span class="sw-c" style="background:#3ecf8e"></span>green</td><td>TJA1020 board, terminal <b>TX</b></td><td>ESP32, pin <b>IO16</b> (RX2)</td></tr>
<tr><td>9</td><td><span class="sw-c" style="background:#4aa8ff"></span>blue</td><td>TJA1020 board, terminal <b>RX</b></td><td>ESP32, pin <b>IO17</b> (TX2)</td></tr>
<tr><td>10</td><td><span class="sw-c" style="background:#b57bff"></span>violet</td><td>TJA1020 board, terminal <b>SLP</b></td><td>ESP32, pin <b>3V3</b></td></tr>
<tr><td>11</td><td><span class="sw-c" style="background:#9aa3b5"></span>grey</td><td>TJA1020 board, terminal <b>GND</b> (TX/RX side)</td><td>ESP32, pin <b>GND</b> (another one, or the same)</td></tr>
<tr><td>—</td><td><span class="sw-c" style="background:#e8d23a"></span>yellow</td><td>van's yellow</td><td>not used: insulate it with tape</td></tr>
<tr><td>—</td><td>—</td><td>TJA1020 board, terminal <b>INH</b></td><td>not connected</td></tr>
<tr class="opt"><td colspan="4"><b>Optional</b>: display and thermometer (dashed box in the diagram). All four go to the same I2C bus: each wire joins the ESP32 pin with the display's <b>and</b> the thermometer's.</td></tr>
<tr><td>12</td><td><span class="sw-c" style="background:#ff7aa8"></span>pink</td><td>ESP32, pin <b>3V3</b> (the other one)</td><td>Display <b>VCC</b> and thermometer <b>VIN</b></td></tr>
<tr><td>13</td><td><span class="sw-c" style="background:#9aa3b5"></span>grey</td><td>ESP32, pin <b>GND</b> (or splice from ⑦)</td><td>Display <b>GND</b> and thermometer <b>GND</b></td></tr>
<tr><td>14</td><td><span class="sw-c" style="background:#2ec4b6"></span>teal</td><td>ESP32, pin <b>IO4</b> (SDA)</td><td>Display <b>SDA</b> and thermometer <b>SDA</b></td></tr>
<tr><td>15</td><td><span class="sw-c" style="background:#e9c46a"></span>gold</td><td>ESP32, pin <b>IO5</b> (SCL)</td><td>Display <b>SCL</b> and thermometer <b>SCL</b></td></tr>
</tbody></table>
TXT,
'steps' => <<<'TXT'
<h2 class="sec">Steps, in order</h2>
<ol class="pasos">
<li><b>First of all, check that your heater speaks W-Bus.</b> The one in VW T5s with the factory timer (part no. 7H0 010 398 J) does, and someone has already switched it on with an ESP, but it is also said that some older Thermo Top C units use another protocol. The <b>first test</b> below (only <code>status</code> and <code>errores</code>) confirms it without switching anything on: if the Webasto answers with its temperature and voltage, go ahead; if you only get the echo and “sin respuesta” (no answer) with the wiring already checked, yours may not be a W-Bus unit.</li>
<li><b>Measure the controller connector</b> with a multimeter, with the Webasto connected: one wire gives a constant +12 V (the “red”), another has continuity to the chassis (the “brown”) and the idle W-Bus reads almost the same as the battery (the “black”). If the colours do not match, what you measure wins, not the drawing.</li>
<li><b>Adjust the LM2596 before connecting anything to it:</b> fit only wires ④ and ⑤, measure between OUT+ and OUT− and turn the gold screw until it reads <b>5.0 V</b>. If you go too high, the ESP32 burns out.</li>
<li>Disconnect, and wire the TJA1020 board: ① ② ③ on the 12V/LIN side terminal (INH empty) and ⑧ ⑨ ⑩ ⑪ on the other side.</li>
<li>Finally, ⑥ and ⑦ to the ESP32. For the <b>first test</b> leave them off: power the ESP32 over USB from your laptop (on the ESP32-S3, through the USB port marked UART or COM) and type <code>status</code> in the console.</li>
<li>If the console does not even show the echo, <b>swap ⑧ and ⑨</b> (it often happens with the TX/RX labels on these boards). If there is an echo but “sin respuesta” (no answer), check ② and the ground.</li>
<li><b>Optional, once the basics work:</b> the OLED display and the thermometer (SHT31 or AHT20) with ⑫–⑮. The board detects them by itself at boot (or 30 s after plugging them in). Keep the thermometer a hand's width away from the board and the regulator, which get warm. Without a thermometer everything works the same, except “heat up to X °C”.</li>
</ol>
TXT,
'dlTitle' => 'Downloads',
'version' => 'version',
'dlFw' => 'Firmware for the ESP32',
'dlFwSub' => 'Folder <code>WTTC/</code> with <code>WTTC.ino</code> and <code>web.h</code>',
'dlApp' => 'Android app (APK)',
'dlAppSub' => 'Android 8 or later · phone, tablet or car radio · no board yet? try demo mode',
'dlOta' => 'Wireless update (.ota)',
'dlOtaSub' => 'For the board\'s web page (Settings → Update firmware), if it cannot download it by itself. Signed: only the official one is installed',
'dlOtaSoon' => 'Being published: come back in a few minutes',
'dlSrc' => 'Source code',
'dlSrcSub' => 'Firmware, app and this documentation · free software, GPL v3 licence',
'versions' => 'Versions and changes (in Spanish)',
'noData' => 'No data.',
'allVersions' => 'All published versions: ',
'dlHint' => <<<'TXT'
Every firmware change is compiled automatically on GitHub with the ESP32 cores 2.0.17 and 3.3.12, and the app is published in
      <a href="https://github.com/matatunos/wttc/releases" target="_blank" rel="noopener">Releases</a>.
      <b>Test version (0.x): it has not been tried on a real Webasto yet.</b> 1.0 will come when someone has tried it installed;
      if you build it, tell us how it went by opening an issue on GitHub.
TXT,
'hw' => <<<'TXT'
<h2 class="sec">Hardware used</h2>
<table class="hw"><thead><tr><th>Part</th><th>Model used</th><th>What for</th></tr></thead><tbody>
<tr><td>Vehicle and heater</td><td>VW T5 with factory-fitted <b>Webasto Thermo Top C</b> coolant auxiliary heater (VW part no. <b>7H0 010 398 J</b>), driven over the W-Bus by the original timer</td><td>What is controlled. The ESP32 replaces the timer and plugs into its connector</td></tr>
<tr><td>Microcontroller</td><td><b>ESP32-S3 DevKitC-1 N16R8</b> (16 MB flash; best on a screw-terminal base, which does not come loose with vibration) — since version 0.2.0, the only one supported</td><td>Wi-Fi, web page, schedules, notifications and the W-Bus protocol on its UART2 (IO16/IO17)</td></tr>
<tr><td>W-Bus transceiver</td><td><b>UART ↔ LIN/K-Line module with TJA1020</b> (the one used is by Gutol: screw terminals 12V/GND/LIN/INH and TX/RX/SLP/GND, no VCC pin)</td><td>Adapts the ESP32's 3.3 V TTL to the single-wire 12 V bus. Any module with a TJA1020 or equivalent will do (TJA1021, MCP2003, L9637D)</td></tr>
<tr><td>Optional: display</td><td><b>1.5" SSD1327 I2C OLED</b> (128×128, 16 greys; the recommended one) or a 128×64 one: 1.3" (SH1106) or 0.96" (SSD1306). Address 0x3C or 0x3D</td><td>Inside temperature, status, coolant, battery and what happens next. The type is chosen in Settings</td></tr>
<tr><td>Optional: thermometer</td><td><b>SHT31</b> or <b>AHT20</b> I2C module (temperature and humidity)</td><td>Inside temperature: “heat up to X °C”, a better departure time and condensation at a glance</td></tr>
<tr><td>Power supply</td><td><b>LM2596</b> step-down regulator (module with potentiometer), set to <b>5.0 V</b></td><td>Takes the ESP32's 5 V from the connector's permanent +12 V</td></tr>
<tr><td>Wiring</td><td>Female dupont wires for the ESP32, 0.5 mm² wire for the 12 V side, splices or terminal blocks and tape or heat-shrink</td><td>Connections as per the diagram and the wire list above</td></tr>
<tr><td>Tools</td><td>Multimeter, small screwdriver, PC with USB (Linux or Windows)</td><td>Identifying the connector's wires, adjusting the LM2596, flashing the ESP32 and the first test over the console</td></tr>
</tbody></table>
TXT,
'hwHint' => 'Optional: a <b>switch</b> on the ESP32\'s +12 V if the vehicle will stand still for weeks (see “Power consumption” below) and a 1 A inline fuse if the connector\'s +12 V has none. You do not need VCDS to use it, but it is handy to read and clear faults on module 18 (auxiliary heater).',
'instNeed' => <<<'TXT'
<h2 class="sec">Flashing the firmware: what you need</h2>
<ul class="pasos">
<li><b>The easiest way:</b> <a href="{install}">install it from the browser</a> (Chrome or Edge, one click, none of what follows). What is below is for doing it with Arduino IDE.</li>
<li><b>Arduino IDE 2</b> (the easiest) or <b>arduino-cli</b> (terminal only).</li>
<li>The <b>Espressif ESP32 core</b> (“esp32 by Espressif Systems”), version 2.x or 3.x. The IDE downloads it itself: several hundred MB.</li>
<li>Board <b>ESP32S3 Dev Module</b> with the partition scheme <b>Huge APP (3MB No OTA/1MB SPIFFS)</b>: with Bluetooth and Wi-Fi the program does not fit in the default partition.</li>
<li>A <b>USB data cable</b>: many charging cables carry no data and the PC does not see the board.</li>
<li><b>USB-serial driver</b>: the ESP32-S3 has two USB ports; use the one marked <b>UART</b> or <b>COM</b>, which has a CH343 or CP2102 (the other is the chip's own USB). On Linux it works without installing anything; on Windows 10/11 it usually installs itself and, if no COM port shows up, install Silicon Labs' “CP210x VCP driver”.</li>
<li>No extra libraries: Bluetooth, Wi-Fi, web server and Preferences come with the ESP32 core.</li>
</ul>
TXT,
'instIde' => <<<'TXT'
<h2 class="sec">With Arduino IDE 2 (Linux and Windows)</h2>
<ol class="pasos">
<li>Install the IDE from <a href="https://www.arduino.cc/en/software" target="_blank" rel="noopener">arduino.cc/en/software</a>. On Windows use the .exe installer or <code>winget install ArduinoSA.IDE.stable</code>; on Linux, the AppImage (make it executable).</li>
<li><b>File → Preferences → “Additional boards manager URLs”</b>, add:<br><code class="sel">https://espressif.github.io/arduino-esp32/package_esp32_index.json</code></li>
<li><b>Tools → Board → Boards Manager</b>, search for “esp32” and install <b>esp32 by Espressif Systems</b>.</li>
<li>Unzip the archive: you get the folder <code>WTTC/</code> with <code>WTTC.ino</code> and <code>web.h</code> (the IDE requires the folder to be named like the .ino). Open <code>WTTC.ino</code>.</li>
<li><b>Tools → Board → esp32 → ESP32S3 Dev Module</b> and <b>Flash Size → 16MB</b>; <b>PSRAM → OPI PSRAM</b>; <b>Tools → Partition Scheme → Huge APP (3MB No OTA/1MB SPIFFS)</b> (only for the size limit: the <code>WTTC/</code> folder brings its own partition table, with two slots for wireless updates, and Arduino uses it by itself); and under <b>Port</b> the ESP32's one (<code>COMx</code> on Windows, <code>/dev/ttyUSB0</code> on Linux).</li>
<li>Press <b>Upload</b> (the arrow). If it hangs at “Connecting……”, hold the board's <b>BOOT</b> button until it starts writing.</li>
<li><b>Tools → Serial Monitor</b> at <b>115200</b> baud: it shows a summary with the version, the <b>Bluetooth PIN</b>, the board's Wi-Fi and its password, and the web user and password (write them down; <code>info</code> shows it again). Type <code>status</code> for the first test.</li>
</ol>
TXT,
'cliLinux' => <<<'TXT'
<h2 class="sec">With arduino-cli on Linux</h2>
<pre class="cmd">curl -fsSL https://raw.githubusercontent.com/arduino/arduino-cli/master/install.sh | BINDIR=~/.local/bin sh
arduino-cli config init
arduino-cli config add board_manager.additional_urls \
  https://espressif.github.io/arduino-esp32/package_esp32_index.json
arduino-cli core update-index
arduino-cli core install esp32:esp32

# inside the folder that contains WTTC/
# ESP32-S3
FQBN=esp32:esp32:esp32s3:FlashSize=16M,PSRAM=opi,PartitionScheme=huge_app
arduino-cli compile --fqbn $FQBN WTTC
arduino-cli upload  --fqbn $FQBN -p /dev/ttyUSB0 WTTC
arduino-cli monitor -p /dev/ttyUSB0 -c baudrate=115200</pre>
<div class="hint">If you get “Permission denied” on <code>/dev/ttyUSB0</code>: <code>sudo usermod -aG dialout $USER</code> and log out. On Ubuntu, if the port appears and disappears, uninstall <code>brltty</code>.</div>
TXT,
'cliWin' => <<<'TXT'
<h2 class="sec">With arduino-cli on Windows (PowerShell)</h2>
<pre class="cmd">winget install ArduinoSA.CLI
arduino-cli config init
arduino-cli config add board_manager.additional_urls `
  https://espressif.github.io/arduino-esp32/package_esp32_index.json
arduino-cli core update-index
arduino-cli core install esp32:esp32

# inside the folder that contains WTTC\
# ESP32-S3
$FQBN = "esp32:esp32:esp32s3:FlashSize=16M,PSRAM=opi,PartitionScheme=huge_app"
arduino-cli compile --fqbn $FQBN WTTC
arduino-cli upload  --fqbn $FQBN -p COM3 WTTC
arduino-cli monitor -p COM3 -c baudrate=115200</pre>
<div class="hint">Replace <code>COM3</code> with the port shown by <code>arduino-cli board list</code> (or in Device Manager → Ports (COM &amp; LPT)). After installing with winget, open a new PowerShell so it finds <code>arduino-cli</code>.</div>
TXT,
'howTo' => 'How to use it',
'useAndroid' => <<<'TXT'
<h3 class="sub3">📱 Android phone or tablet (app, over Bluetooth)</h3>
        <ol class="pasos">
          <li>Download <a href="{apk}">WTTC.apk</a> on the phone and open it. Android will ask for permission to “install unknown apps” from the browser: allow it for this installation.</li>
          <li>Open WTTC and accept the <b>nearby devices</b> (Bluetooth) and notifications permissions.</li>
          <li>Tap <b>Find board</b>, choose your WTTC and enter its <b>6-digit PIN</b> (shown on the serial monitor at boot and on the board's web page, Settings section).</li>
          <li>Done: from then on the app connects by itself when the board is in range (10–20 m through the bodywork) and sets its clock.</li>
          <li>If the heater switches itself off while the app is open, Android shows a notification with the reason and its fault codes.</li>
          <li>No board yet? <b>Try without a board</b> opens demo mode: a simulated board to see how the app works.</li>
          <li><b>Updating the firmware:</b> Settings → <b>Check for updates</b>. If there is a new version, it shows what's new and, if you accept, the board downloads it, installs it and restarts by itself (it must be joined to a network with internet). With Telegram set up, the board also tells you when one comes out.</li>
        </ol>
TXT,
'useIphone' => <<<'TXT'
<h3 class="sub3">🍏 iPhone (web page, over Wi-Fi)</h3>
        <ol class="pasos">
          <li>There is no iPhone app: you use the board's web page, the same one you see in the simulator.</li>
          <li>Settings → Wi-Fi → network <b>WTTC</b> (factory password <code>calefaccion</code>: the first time, the web page asks you to change it before letting you use it) and open <b>http://192.168.4.1</b> in Safari.</li>
          <li>Share → <b>Add to Home Screen</b>: it behaves like any other app.</li>
          <li>To have the board's network available when you need it, set the Wi-Fi to “Always on” or “Only while heating” (see power consumption). In “Only on request” it is only there for 10 minutes after booting.</li>
          <li>If you set the board to join the iPhone's hotspot, it also answers at <b>http://wttc.local</b> without changing networks.</li>
          <li>To update: Settings → <b>Check for updates</b> (with the board joined to a network with internet), or upload the <code>.ota</code> file of the latest version from that same section.</li>
        </ol>
TXT,
'useRadio' => <<<'TXT'
<h3 class="sub3">🚐 The vehicle's Android radio</h3>
        <ol class="pasos">
          <li><b>First check</b> that its Bluetooth works for apps: install <b>nRF Connect</b> (Play Store, free) on the radio and tap Scan. If BLE devices show up, the WTTC app will work.</li>
          <li>Install the APK: download it from the radio's browser or copy it to a USB stick and open it with its file manager.</li>
          <li>Pair as on the phone. The screen adapts to the radio (centred column, large buttons).</li>
          <li>If nRF Connect <b>sees nothing</b>, the radio's Bluetooth is a separate module, only for calls and music. Use the web page: connect the radio to the <b>WTTC</b> Wi-Fi network and open http://192.168.4.1. Meanwhile the radio will have no internet over Wi-Fi.</li>
        </ol>
TXT,
'power' => <<<'TXT'
<h2 class="sec">Power consumption and Wi-Fi modes</h2>
        <table class="hw"><thead><tr><th>Mode (Settings)</th><th>When the Wi-Fi is on</th><th>Approx. draw when parked</th></tr></thead><tbody>
          <tr><td>Always on (default)</td><td>All the time</td><td>≈ 40–60 mA (1–1.5 Ah/day)</td></tr>
          <tr><td>Only while heating (best when parked for days)</td><td>While heating and 10 min after</td><td>≈ 15–25 mA with Bluetooth only</td></tr>
          <tr><td>Only on request</td><td>When you ask for it from the app (15 min) and a few minutes to send notifications</td><td>≈ 15–25 mA</td></tr>
        </tbody></table>
        <div class="hint">Estimated figures at 12 V, LM2596 included; measure yours with a multimeter in series. In every mode the Wi-Fi is on for 10 minutes after booting, as a fallback if your phone is not paired. The processor runs at 80 MHz to use less power.</div>
TXT,
'tgHow' => <<<'TXT'
<h2 class="sec">Telegram notifications (optional)</h2>
        <ol class="pasos">
          <li>In Telegram, talk to <b>@BotFather</b>, send <code>/newbot</code> and follow the steps: it gives you your bot's <b>token</b>.</li>
          <li>Open the chat with your new bot and tap <b>Start</b> (otherwise the bot cannot write to you).</li>
          <li>Your <b>chat ID</b>: write something to your bot and open <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code>; it is the number in <code>"chat":{"id":…}</code>.</li>
          <li>In the app or the board's web page → Settings: token, chat ID and a <b>network with internet</b> (for example, the phone's hotspot). Save and tap <b>Test Telegram</b>.</li>
          <li>It notifies when switching on, when switching off, if it switches itself off (with its faults), if it stops answering and if a schedule does not start because of a low battery.</li>
        </ol>
TXT,
'security' => <<<'TXT'
<h2 class="sec" style="margin-top:18px">Security</h2>
    <ul class="pasos">
      <li>Bluetooth with <b>PIN pairing</b> and an encrypted connection: without pairing nothing can be read or sent. The PIN is generated at random on each board the first time it boots.</li>
      <li>If you lose your phone: Settings → <b>Delete pairings</b> (also from the serial console: <code>forget</code>), and change the PIN.</li>
      <li>The board's own Wi-Fi comes with a public factory password (<code>calefaccion</code>): until you choose another one, the board's web page will not let you use it or show the PIN, and the app asks you for it when connecting. The Telegram token is never shown after saving it.</li>
      <li>The board's web page only accepts commands from itself (not from other websites open on the phone), and Telegram notifications check the server's certificate.</li>
      <li>Wireless updates are <b>signed</b>: the board only installs official ones, never a version older than the installed one, and never while heating. If the new one does not keep running for a minute, it goes back to the previous one by itself.</li>
      <li>Anonymous statistics: the app asks the first time it is opened (two equal buttons, “Yes, send” or “No, thanks”). What is sent and the results, in the <a href="{stats}">public statistics</a> (in Spanish).</li>
      <li>The Webasto keeps its own protections: it needs the keep-alive message every few seconds (if the board hangs or is disconnected, it switches itself off with its after-run), and every start has its maximum duration (60 min).</li>
    </ul>
TXT,
'framesHead' => <<<'TXT'
<h2 class="sec">Frames used by the firmware</h2>
      <table>
        <thead><tr><th>Action</th><th>Frame</th><th>Webasto's answer</th></tr></thead>
TXT,
'cCmd' => 'Command (hex)',
'cDat' => 'Data (hex)',
'cOut' => 'Frame with checksum',
'simNotes' => <<<'TXT'
<h2 class="sec">What is simulated</h2>
      <ul class="notes">
        <li><b>Diesel:</b> estimated from the power reported by the Webasto (≈ 0.62 l/h at 5 kW, according to its data sheet); the board keeps the current run, the last one, the month and the total. It is an estimate (±20%), not a tank measurement.</li>
        <li><b>Not simulated:</b> Bluetooth (the Android app) and switching the Wi-Fi off to save power: here the phone's web page always reaches the board.</li>
        <li><b>True to the code:</b> the web page, the API and the console are the firmware's: retries, echo, wake-up pulse after 10 s of silence, keep-alive every 5 s, sensors every 8 s, schedules, battery cut-off &lt; 12.0 V and a clock that is lost on restart.</li>
        <li><b>True to the protocol:</b> frame format and the answers for sensors (0x50/05) and faults (0x56/01) according to libwbus.</li>
        <li><b>Approximate:</b> the start-up and after-run times, the regulation thresholds (full load up to 75 °C, part load up to 85 °C, resumes at 70 °C), how the circuit warms up, and that the Webasto switches off after 20 s without keep-alive (libwbus figure, not checked on yours).</li>
        <li><b>Real state:</b> the firmware looks at the keep-alive answer (01 = the Webasto no longer has the command) and at the flame: “Starting”, “Heating”, “Paused” (no flame with the coolant at ≥ 65 °C) or “No answer”. If it switches itself off (try “no diesel”), the web page says so with its faults and it notifies over Telegram. That the Thermo Top C answers 01 to the keep-alive comes from libwbus: if yours does not, the flame remains as a fallback (5 min without flame with cold coolant).</li>
      </ul>
TXT,
'legal' => <<<'TXT'
<h2 class="sec">Disclaimer of liability</h2>
    <div class="legal">
      <p><b>1. Scope.</b> WTTC (the “project”) comprises the firmware for ESP32 and ESP32-S3, the Android app, this website and
        its simulator, the diagrams, the documentation and the related services (anonymous statistics and updates). It is a
        personal, non-profit project of its author (matatunos, the copyright holder under the licence), made available to the
        public free of charge. It is not a commercial product: it is not sold, has no technical or customer support and has not
        passed any type approval, certification or conformity testing.</p>
      <p><b>2. No warranty.</b> The project is provided “as is” and “as available”, under the GPL v3 (or later) licence, without warranty of any
        kind, express or implied, including without limitation warranties of operation, merchantability, fitness for a
        particular purpose, freedom from errors, compatibility with a given vehicle or heater, and continuity of the services.
        While its version number starts with 0 it is a test version: it compiles and has been checked in the simulator, but it
        has not been tried installed with a real Webasto heater.</p>
      <p><b>3. Accuracy of information.</b> The documentation (wire colours and functions, connections, power consumption, fault
        codes, diesel estimates and any other technical data) is based on public sources and on assumptions that have not been
        checked on every vehicle. It may contain errors or omissions and does not replace the manufacturer's manual, the
        vehicle's wiring diagram or a professional's judgement. Check it yourself before connecting anything.</p>
      <p><b>4. Exclusion of liability.</b> To the maximum extent permitted by applicable law, the author shall not be liable for
        any damage or loss, direct or indirect, incidental or consequential, of any kind —including, among others, personal
        injury, carbon monoxide poisoning, fire, damage to the heater, the vehicle, its battery or its electrical system, loss of
        warranty or insurance cover, fines, immobilisation of the vehicle, loss of data, loss of profit or repair costs— arising
        out of or in connection with downloading, installing, using, being unable to use, modifying or distributing the project,
        errors in its software or documentation, or the interruption or failure of third-party services it depends on (such as
        GitHub or Telegram).</p>
      <p><b>5. User's responsibility.</b> Anyone who installs or uses the project does so of their own choice, under their sole
        responsibility, and assumes all risks. In particular, they are responsible for:</p>
      <ul class="pasos">
        <li>Carrying out the installation, or having it carried out, with the knowledge needed to work safely on 12 V electrical
          systems and near fuel systems, protecting the power supply with a fuse.</li>
        <li><b>Never scheduling or switching on the heater with the vehicle in a garage or any enclosed or poorly ventilated
          space.</b> The exhaust gases contain carbon monoxide, which has no smell and can be fatal.</li>
        <li>Watching how it works the first few times and always keeping a way to switch it off without the project, such as
          pulling the heater's own fuse. (According to the W-Bus protocol documentation, without the keep-alive message the
          heater switches itself off within seconds, but this has not been checked on a real installation.)</li>
        <li>Checking whether the modification affects the warranty of the vehicle or the heater, its insurance or the
          regulations that apply to them, and restoring the original installation if they remove the project.</li>
      </ul>
      <p><b>6. Software generated with artificial intelligence.</b> All of the project's code and content has been generated with
        Claude, an artificial intelligence model by Anthropic, following the author's instructions. It may contain errors that
        have not been detected. Anthropic is not part of the project and is not responsible for it.</p>
      <p><b>7. Trademarks.</b> Webasto, Thermo Top, Volkswagen, Climatronic, Espressif, ESP32, Arduino, Android, Telegram and GitHub
        are trademarks or names of their respective owners. The project has no relationship with them and is not approved or
        sponsored by them; their names are used only to indicate which equipment and services it is compatible or works
        with.</p>
      <p><b>8. Limits of this exclusion.</b> Nothing above excludes or limits liability where the law does not allow it, such as
        liability for wilful misconduct or gross negligence. If any part of this text is held void or unenforceable, the rest
        remains valid.</p>
      <p><b>9. Acceptance and governing law.</b> Downloading, installing or using any part of the project means you have read
        and accepted this disclaimer of liability and the GPL v3 licence; if you do not agree, do not use it. Matters not covered
        here are governed by Spanish law. In case of discrepancy between language versions, the Spanish version prevails.</p>
      <p class="muted">This text supplements, without replacing it, the warranty and liability disclaimer of the GPL v3 licence (sections 15 and 16)
        (LICENSE file in the repository).</p>
TXT,
'credit' => <<<'TXT'
WTTC · free software (GPL v3) on <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">GitHub</a> ·
    code and content generated entirely with Claude (Anthropic) · personal project unrelated to Webasto or Volkswagen.
TXT,
'relGithub' => 'Release on GitHub ↗',
'verWord' => 'Version',
'boardSim' => 'Board: display, LED and thermometer',
'oledAria' => 'OLED display of the simulated board',
'ledLbl' => 'Board LED',
'bootTitle' => 'Board\'s BOOT button: switches the display on for a minute',
'cabNow' => 'Inside (real)',
'hwScr' => 'OLED display connected',
'hwSens' => 'Thermometer connected',
'cabSet' => 'Set inside to',
'boardHint' => 'The display is drawn pixel by pixel as on the board (the 128×128 greyscale SSD1327, or the 128×64 one if you choose it in Settings; same fonts and same texts). In automatic mode it stays on while the web page on the left is open, just like the real board with its web page open. When you remove a part, the board treats it as disconnected after a few seconds and whatever depends on it disappears from the web page; when you plug it back in, it is detected within half a minute.',
'testTitle' => 'Tests and PDF report',
'tAmb' => 'Outside',
'tCab' => 'Inside at start',
'tTgt' => 'Up to',
'tNoTgt' => 'no thermostat',
'tDur' => 'Duration',
'tBatt' => 'Battery',
'tRun' => 'Run the test and download the PDF',
'tRecStart' => 'Record what I do',
'tRecStop' => 'Finish and download the PDF',
'testHint' => '“Run the test” switches on with those values and jumps the simulated time to the end at once (a few seconds); then it downloads a PDF with charts of temperatures, starts, power, diesel and battery, the table of starts and the board log. “Record what I do” logs what happens while you use the simulator (at any speed) until you finish it. The heat model is approximate.',
'sim' => [
    'tRunning' => 'Running the test…', 'tDone' => 'Test done: {0} starts, {1} l of diesel. Downloading the PDF…', 'tNoSens' => '“Up to” needs the thermometer: mark it as connected under “Board”.', 'tBusy' => 'A recording is already running: finish it first.', 'tRecOn' => 'Recording… use the simulator and press “Finish” when done.', 'tPdfErr' => 'The PDF could not be created.', 'tRecStart' => 'Record what I do', 'tRecStop' => 'Finish and download the PDF',
    'oledProbe' => "detecting…\n(up to 30 s)", 'oledDisabled' => "disabled in Settings\n(BOOT turns it on for a minute)", 'oledNone' => 'no display', 'oledOff' => "display off\n(press BOOT)",
    'st' => ['OFF' => 'Off', 'FAN' => 'Fan start-up', 'GLOW' => 'Glow plug preheating', 'IGN' => 'Ignition: fuel flowing',
             'STAB' => 'Flame stabilising', 'FULL' => 'Combustion at full load', 'PART' => 'Combustion at part load',
             'PAUSE' => 'Regulation pause', 'AFTER' => 'After-run (switching off)', 'FAIL' => 'After-run due to fault', 'LOCK' => 'Locked out (interlock)'],
    'errn' => ['1' => 'control unit faulty', '2' => 'no start', '3' => 'flame failure', '4' => 'voltage too high',
               '6' => 'overheating', '7' => 'locked out (interlock)', '18' => 'W-Bus communication fault'],
    'stopTime' => 'command time expired',
    'stopKa' => '20 s without keep-alive',
    'stopCmd' => 'switch-off command',
    'stopF2' => 'fuse F2 pulled for 5 s',
    'txOn' => 'Switch on parking heater {0} min',
    'txOff' => 'Switch off',
    'txKa' => 'Keep-alive: carry on with command 0x{0}',
    'txSens' => 'Read sensors (register 0x05)',
    'txReg' => 'Read register 0x{0}',
    'txErrList' => 'Read fault list',
    'txErrClr' => 'Clear faults',
    'txErr' => 'Faults',
    'txCmd' => 'Command 0x{0}',
    'rxOn' => 'Accepted: {0} min',
    'rxOff' => 'Switch-off accepted',
    'rxKaOff' => 'Command NOT active (01): the Webasto is no longer heating',
    'rxKaOn' => 'Command active (00)',
    'rxSens' => 'Coolant {0} °C · {1} V · flame {2} · {3} W',
    'yes' => 'yes',
    'no' => 'no',
    'rxNoErr' => 'No faults stored',
    'rxErrs' => '{0} fault(s): {1}',
    'rxResp' => 'Answer',
    'noEchoSlp' => 'No echo: the TJA1020 is asleep (SLP not connected)',
    'noEchoCross' => 'No echo: TX/RX swapped, the frame does not reach the bus',
    'echo' => 'Echo of our own frame (discarded)',
    'noRxWbus' => 'No answer within 500 ms: the W-Bus does not reach the Webasto',
    'noRxPower' => 'No answer within 500 ms: the Webasto has no power',
    'noRxCmd' => 'No answer: command not supported',
    'noise' => 'Arrives with a flipped bit: the checksum does not match and the firmware discards it',
    'brk' => 'Wake-up pulse: TX low for 50 ms (more than 10 s without talking)',
    'simulated' => ' (simulated)',
    'comps' => ['Fan', 'Glow plug', 'Fuel pump', 'Flame', 'Water pump'],
    'booting' => 'ESP32 booting…',
    'realTime' => 'real time',
    'timeX' => 'time ×{0}',
    'paused' => 'paused',
    'upFor' => ' · ESP32 on for {0}',
    'noPower' => 'No power',
    'errMem' => 'Fault memory: ',
    'errMemEmpty' => 'Fault memory empty.',
    'lastStop' => ' · Last switch-off: {0}.',
    'lastHour' => 'last hour',
    'echoTag' => 'echo',
    'busEmpty' => 'Nothing has gone over the bus yet. Switch it on from the phone or type “status” in the console.',
    'tgEmpty' => 'No messages. To see them, open “Settings” on the board\'s web page and enter a network with internet (any), a made-up token and chat; save and wait for it to restart.',
    'fr' => ['Switch on 30 min', 'Switch off', 'Read sensors', 'Read faults'],
    'data' => '(data)',
    'loc' => 'en-GB',
],
];
