<?php
// i18n/de.php — Textos de la web pública en alemán (página y simulador). Generado con Claude (Anthropic).
// Mismas claves que es.php; lo que falte aquí sale en español. {apk} y {stats} los rellena t() en index.php.
return [
'title' => 'WTTC · Webasto Thermo Top C per Handy steuern, mit ESP32',
'desc' => 'Freier Controller für die Standheizung Webasto Thermo Top C in Bussen und Campern: ESP32-Platine am W-Bus, Android-App über Bluetooth und Webseite über WLAN.',
'ogTitle' => 'WTTC · Webasto Thermo Top C per Handy',
'h1' => '🔥 WTTC · Webasto Thermo Top C über Bluetooth und WLAN',
'intro' => <<<'TXT'
Ersetzt die originale Zeitschaltuhr der Standheizung Thermo Top C durch einen ESP32, der ihr W-Bus-Protokoll spricht,
      und steuert sie per Android-App über Bluetooth oder im Browser über WLAN. Open Source:
      <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">github.com/matatunos/wttc</a>.
      <br>Unten der <b>Simulator</b>: links die Webseite der Firmware, unverändert; rechts eine virtuelle Webasto, die auf ihre Telegramme antwortet.
      Du kannst die Zeit beschleunigen und Fehler auslösen.
      <br><b>Der gesamte Code und Inhalt von WTTC</b> (Firmware, App, Server, diese Webseite und ihre Dokumentation) <b>ist vollständig mit Claude erzeugt</b> (Anthropic). Weiter unten findest du die <a href="#descargas">Downloads</a>, den <a href="#montaje">Einbau</a>
      und die <a href="#uso">Bedienungsanleitung</a>. Wie viele nutzen es? <a href="{stats}">Öffentliche Statistik</a> (auf Spanisch). Du hast eine Platine? <a href="/mi.php">Meine Statistiken</a> (auf Spanisch).
      Zeigt deine Webasto einen Fehler? <a href="{faults}">Fehlercodes der Thermo Top C</a>.
      Willst du es bauen? <a href="{install}">Die Firmware im Browser installieren</a>, ohne Arduino IDE.
TXT,
'aviso' => <<<'TXT'
    <b>⚠️ Vorab: Das ist ein privates Projekt, kein Produkt.</b> WTTC steuert eine Heizung, die in deinem Fahrzeug Diesel
    verbrennt, und wird ohne jede Gewährleistung und als Testversion bereitgestellt. Wenn du es einbaust,
    tust du das vollständig auf eigene Verantwortung. <a href="#responsabilidad">Lies den Haftungsausschluss</a>, bevor du ein einziges Kabel anfasst.
TXT,
'phoneTitle' => 'Webseite des ESP32 (echte Firmware)',
'phoneCap' => 'Webseite, die der ESP32 ausliefert (wörtliche Kopie aus der Firmware).<br>Ihre Aufrufe an <code>/api/…</code> beantwortet der simulierte ESP32.',
'simTime' => 'simulierte Zeit',
'pause' => '⏸ Pause',
'rebootTitle' => 'Trennt die Stromversorgung des ESP32 und stellt sie wieder her',
'rebootBtn' => '↻ ESP32 neu starten',
'heaterVirtual' => 'Webasto Thermo Top C (virtuell)',
'water' => 'Kühlwasser',
'off' => 'Aus',
'kPower' => 'Leistung',
'kBatt' => 'Batterie',
'kAmp' => 'Stromaufnahme',
'kRun' => 'Aktiver Befehl',
'chartAria' => 'Kühlwassertemperatur in der letzten simulierten Stunde',
'tgSim' => 'Telegram (simuliert)',
'tgHint' => 'Was auf deinem Handy ankäme. Der Simulator sendet nichts wirklich.',
'faultsTitle' => 'Fehler auslösen',
'faults' => <<<'TXT'
          <label><input type="checkbox" data-f="wbus"><span>W-Bus-Kabel lose<small>Der TJA1020 liefert weiter das Echo, aber die Webasto antwortet nicht.</small></span></label>
          <label><input type="checkbox" data-f="slp"><span>SLP des TJA1020 offen<small>Der Transceiver schläft: weder Echo noch Antwort.</small></span></label>
          <label><input type="checkbox" data-f="cross"><span>TX/RX vertauscht<small>Typischer Fehler wegen der TX/RX-Beschriftung der Platine.</small></span></label>
          <label><input type="checkbox" data-f="power"><span>Webasto ohne Strom<small>Sicherung des Geräts durchgebrannt: die Webasto antwortet auf nichts.</small></span></label>
          <label><input type="checkbox" data-f="fuel"><span>Kein Diesel / Pumpe klemmt<small>Sie zündet nicht: Fehler 0x02 und nach 3 Fehlstarts Verriegelung.</small></span></label>
          <label><input type="checkbox" data-f="noise"><span>Störungen auf dem Bus<small>Jede vierte Antwort kommt mit falscher Prüfsumme an.</small></span></label>

TXT,
'env' => <<<'TXT'
          <span>Außentemperatur <input type="range" id="amb" min="-15" max="40" step="1" value="5"> <b id="ambv">5 °C</b></span>
          <span>Batterie in Ruhe <input type="range" id="bat" min="11.0" max="13.0" step="0.1" value="12.6"> <b id="batv">12,6 V</b></span>
          <button class="b" id="unlock" title="Vorgehen laut Handbuch: Sicherung F2 (20 A) 5 s ziehen">Entriegeln (Sicherung F2)</button>
          <button class="b" id="clrerr" title="Wie VCDS es im Steuergerät 18 tun würde">Fehler löschen (VCDS)</button>

TXT,
'traffic' => 'W-Bus-Verkehr · 2400 Baud 8E1',
'trRep' => ' Keep-Alive und Sensoren',
'clear' => 'Leeren',
'traceHint' => 'Blau: was der ESP32 sendet (<code>F4</code> = von der Diagnose an die Heizung). Grau: das Echo, das die Firmware verwirft. Grün: die Antwort der Webasto (<code>4F</code>, Befehl | 0x80). Das letzte Byte ist das XOR aller vorherigen.',
'serial' => 'Serielle Konsole (115200)',
'conPh' => 'on 30 · off · status · errores',
'conAria' => 'Befehl für die serielle Konsole',
'send' => 'Senden',
'conHint' => 'Es ist dieselbe Konsole, die du beim ersten Test benutzt, mit dem ESP32 per USB am Laptop. Ihre Befehle und Meldungen sind auf Spanisch.',
'wiring' => 'Schaltplan (für den Einbau)',
'svgAria' => 'Schaltplan: Stecker der Bedieneinheit, TJA1020-Platine, LM2596-Regler und ESP32, mit nummerierten Kabeln',
'svgConn' => 'Stecker der Bedieneinheit',
'svgConnSub' => '(Fahrzeugseite, Bedieneinheit abgezogen)',
'svgSilk' => '<text x="32" y="263" class="silk">braun</text><text x="32" y="303" class="silk">schwarz</text><text x="32" y="383" class="silk">rot</text><text x="32" y="423" class="silk">gelb</text>',
'svgNoPower' => '⚠ Die Webasto hat keinen Strom (Sicherung)',
'svgBoard' => 'TJA1020-Platine (Gutol)',
'svgBoardSub' => 'Schraubklemmen auf beiden Seiten',
'svgInh' => 'INH: nicht anschließen',
'svgReg' => 'LM2596-Regler',
'svgScrew' => 'Schraube:',
'svg5v' => '5,0 V',
'svgEsp' => 'ESP32-S3 DevKitC-1 N16R8 (44 Pins)',
'svgEspSub' => 'liegend gezeichnet: Antenne links, USB rechts',
'svgYellow' => 'gelb: mit Band isolieren, wird nicht benutzt',
'svgWbus' => 'W-Bus lose',
'svgSlp' => 'SLP offen: ⑩ fehlt',
'svgCross' => '⑧ und ⑨ vertauscht',
'svgOpt' => 'Optional: Display und Thermometer (I2C-Bus)',
'svgOptSub' => 'gestrichelte Kabel: der Aufbau funktioniert ohne sie',
'svgOled' => 'I2C-OLED-Display (SSD1327 1,5")',
'svgSens' => 'Thermometer SHT31 oder AHT20',
'wireHint' => <<<'TXT'
Die Platinen können die Klemmen an anderen Stellen haben: <b>richte dich nach der Beschriftung neben jeder Klemme bzw. jedem Pin</b>, nicht nach der Position in der Zeichnung. Die Zeichnung zeigt den <b>ESP32-S3 DevKitC-1</b>: alle Pins, die WTTC nutzt, liegen auf derselben Seite. Auf Basisplatinen mit Schraubklemmen sind sie beschriftet (der 5-V-Pin heißt manchmal „5Vin“). Seit Version 0.2.0 wird nur der <b>ESP32-S3</b> unterstützt. Was im <b>gestrichelten Kasten</b> steht (Display und Thermometer, Kabel ⑫–⑮), ist <b>optional</b>: der Aufbau funktioniert auch ohne.
  Die Farben der Kabel ⑥–⑪ sind ein Vorschlag (Dupont-Kabel). Die des Fahrzeugsteckers sind <b>unbestätigt: mit dem Multimeter messen</b>.
TXT,
'cables' => <<<'TXT'
<h2 class="sec">Kabelliste</h2>
<table class="cables"><thead><tr><th>Nr.</th><th>Kabel</th><th>Von</th><th>Nach</th></tr></thead><tbody>

<tr><td>1</td><td><span class="sw-c" style="background:#a0703c"></span>braun</td><td>braun vom Fahrzeug (Masse)</td><td>TJA1020-Platine, Klemme <b>GND</b> (Seite 12V/LIN)</td></tr>
<tr><td>2</td><td><span class="sw-c" style="background:#111318"></span>schwarz</td><td>schwarz vom Fahrzeug (W-Bus)</td><td>TJA1020-Platine, Klemme <b>LIN</b></td></tr>
<tr><td>3</td><td><span class="sw-c" style="background:#e04848"></span>rot</td><td>rot vom Fahrzeug (+12 V)</td><td>TJA1020-Platine, Klemme <b>12V</b></td></tr>
<tr><td>4</td><td><span class="sw-c" style="background:#e04848"></span>rot</td><td>rot vom Fahrzeug (abgezweigt von ③)</td><td>LM2596, <b>IN+</b></td></tr>
<tr><td>5</td><td><span class="sw-c" style="background:#a0703c"></span>braun</td><td>braun vom Fahrzeug (abgezweigt von ①)</td><td>LM2596, <b>IN−</b></td></tr>
<tr><td>6</td><td><span class="sw-c" style="background:#ff9f1c"></span>orange</td><td>LM2596 <b>OUT+</b> (5,0 V)</td><td>ESP32, Pin <b>5V</b></td></tr>
<tr><td>7</td><td><span class="sw-c" style="background:#9aa3b5"></span>grau</td><td>LM2596 <b>OUT−</b></td><td>ESP32, Pin <b>GND</b> (beliebig)</td></tr>
<tr><td>8</td><td><span class="sw-c" style="background:#3ecf8e"></span>grün</td><td>TJA1020-Platine, Klemme <b>TX</b></td><td>ESP32, Pin <b>IO16</b> (RX2)</td></tr>
<tr><td>9</td><td><span class="sw-c" style="background:#4aa8ff"></span>blau</td><td>TJA1020-Platine, Klemme <b>RX</b></td><td>ESP32, Pin <b>IO17</b> (TX2)</td></tr>
<tr><td>10</td><td><span class="sw-c" style="background:#b57bff"></span>violett</td><td>TJA1020-Platine, Klemme <b>SLP</b></td><td>ESP32, Pin <b>3V3</b></td></tr>
<tr><td>11</td><td><span class="sw-c" style="background:#9aa3b5"></span>grau</td><td>TJA1020-Platine, Klemme <b>GND</b> (Seite TX/RX)</td><td>ESP32, Pin <b>GND</b> (ein anderer oder derselbe)</td></tr>
<tr><td>—</td><td><span class="sw-c" style="background:#e8d23a"></span>gelb</td><td>gelb vom Fahrzeug</td><td>nicht benutzt: mit Band isolieren</td></tr>
<tr><td>—</td><td>—</td><td>TJA1020-Platine, Klemme <b>INH</b></td><td>nicht anschließen</td></tr>
<tr class="opt"><td colspan="4"><b>Optional</b>: Display und Thermometer (gestrichelter Kasten im Schaltplan). Alle vier gehen an denselben I2C-Bus: jedes Kabel verbindet den Pin des ESP32 mit dem des Displays <b>und</b> dem des Thermometers.</td></tr>
<tr><td>12</td><td><span class="sw-c" style="background:#ff7aa8"></span>rosa</td><td>ESP32, Pin <b>3V3</b> (der andere)</td><td>Display <b>VCC</b> und Thermometer <b>VIN</b></td></tr>
<tr><td>13</td><td><span class="sw-c" style="background:#9aa3b5"></span>grau</td><td>ESP32, Pin <b>GND</b> (oder Abzweig von ⑦)</td><td>Display <b>GND</b> und Thermometer <b>GND</b></td></tr>
<tr><td>14</td><td><span class="sw-c" style="background:#2ec4b6"></span>türkis</td><td>ESP32, Pin <b>IO4</b> (SDA)</td><td>Display <b>SDA</b> und Thermometer <b>SDA</b></td></tr>
<tr><td>15</td><td><span class="sw-c" style="background:#e9c46a"></span>gold</td><td>ESP32, Pin <b>IO5</b> (SCL)</td><td>Display <b>SCL</b> und Thermometer <b>SCL</b></td></tr>
</tbody></table>
TXT,
'steps' => <<<'TXT'
<h2 class="sec">Schritte, der Reihe nach</h2>
<ol class="pasos">
<li><b>Zuallererst prüfen, ob deine Heizung W-Bus spricht.</b> Die der VW T5 mit ab Werk eingebauter Zeitschaltuhr (Teilenr. 7H0 010 398 J) tut es, und jemand hat sie bereits mit einem ESP eingeschaltet, aber es heißt auch, dass manche ältere Thermo Top C ein anderes Protokoll nutzen. Der <b>erste Test</b> weiter unten (nur <code>status</code> und <code>errores</code>) klärt das, ohne etwas einzuschalten: antwortet die Webasto mit Temperatur und Spannung, geht es weiter; kommt bei bereits geprüfter Verkabelung nur das Echo und „sin respuesta“ (keine Antwort), ist deine vielleicht keine W-Bus-Heizung.</li>
<li><b>Den Stecker der Bedieneinheit</b> mit dem Multimeter <b>messen</b>, bei angeschlossener Webasto: ein Kabel führt dauerhaft +12 V (das „rote“), eines hat Durchgang zur Karosserie (das „braune“) und der W-Bus in Ruhe zeigt fast dasselbe wie die Batterie (das „schwarze“). Stimmen die Farben nicht, zählt die Messung, nicht die Zeichnung.</li>
<li><b>Den LM2596 einstellen, bevor irgendetwas daran hängt:</b> nur die Kabel ④ und ⑤ anschließen, zwischen OUT+ und OUT− messen und an der goldenen Schraube drehen, bis <b>5,0 V</b> anliegen. Ist es zu viel, brennt der ESP32 durch.</li>
<li>Abklemmen und die TJA1020-Platine verdrahten: ① ② ③ an die Klemme auf der Seite 12V/LIN (INH frei) und ⑧ ⑨ ⑩ ⑪ an die der anderen Seite.</li>
<li>Zum Schluss ⑥ und ⑦ an den ESP32. Für den <b>ersten Test</b> weglassen: den ESP32 per USB vom Laptop versorgen (beim ESP32-S3 über den mit UART oder COM beschrifteten USB-Anschluss) und in der Konsole <code>status</code> eingeben.</li>
<li>Zeigt die Konsole nicht einmal das Echo, <b>⑧ und ⑨ tauschen</b> (passiert oft wegen der TX/RX-Beschriftung dieser Platinen). Kommt ein Echo, aber „sin respuesta“ (keine Antwort), ② und die Masse prüfen.</li>
<li><b>Optional, wenn das Grundlegende läuft:</b> das OLED-Display und das Thermometer (SHT31 oder AHT20) mit ⑫–⑮. Die Platine erkennt sie beim Start selbst (oder 30 s nach dem Anschließen). Das Thermometer eine Handbreit von Platine und Regler entfernt anbringen, die warm werden. Ohne Thermometer funktioniert alles gleich, außer „Heizen bis X °C“.</li>
</ol>
TXT,
'dlTitle' => 'Downloads',
'version' => 'Version',
'dlFw' => 'Firmware für den ESP32',
'dlFwSub' => 'Ordner <code>WTTC/</code> mit <code>WTTC.ino</code> und <code>web.h</code>',
'dlApp' => 'Android-App (APK)',
'dlAppSub' => 'Android 8 oder neuer · Handy, Tablet oder Autoradio · noch keine Platine? Demomodus ausprobieren',
'dlOta' => 'Kabelloses Update (.ota)',
'dlOtaSub' => 'Für die Webseite der Platine (Einstellungen → Firmware aktualisieren), falls sie es nicht selbst herunterladen kann. Signiert: nur das offizielle wird installiert',
'dlOtaSoon' => 'Wird gerade veröffentlicht: in ein paar Minuten wieder vorbeischauen',
'dlSrc' => 'Quellcode',
'dlSrcSub' => 'Firmware, App und diese Dokumentation · freie Software, Lizenz GPL v3',
'versions' => 'Versionen und Änderungen (auf Spanisch)',
'noData' => 'Keine Daten.',
'allVersions' => 'Alle veröffentlichten Versionen: ',
'dlHint' => <<<'TXT'
Jede Änderung der Firmware wird auf GitHub automatisch mit den ESP32-Cores 2.0.17 und 3.3.12 kompiliert, und die App erscheint unter
      <a href="https://github.com/matatunos/wttc/releases" target="_blank" rel="noopener">Releases</a>.
      <b>Testversion (0.x): noch nicht an einer echten Webasto erprobt.</b> Die 1.0 kommt, wenn jemand es eingebaut getestet hat;
      wenn du es baust, erzähl uns, wie es lief, indem du ein Issue auf GitHub eröffnest.
TXT,
'hw' => <<<'TXT'
<h2 class="sec">Verwendete Hardware</h2>
<table class="hw"><thead><tr><th>Teil</th><th>Verwendetes Modell</th><th>Wofür</th></tr></thead><tbody>
<tr><td>Fahrzeug und Heizung</td><td>VW T5 mit ab Werk eingebauter Wasser-Standheizung <b>Webasto Thermo Top C</b> (VW-Teilenr. <b>7H0 010 398 J</b>), über W-Bus von der originalen Zeitschaltuhr gesteuert</td><td>Das, was gesteuert wird. Der ESP32 ersetzt die Zeitschaltuhr und wird in ihren Stecker gesteckt</td></tr>
<tr><td>Mikrocontroller</td><td><b>ESP32-S3 DevKitC-1 N16R8</b> (16 MB Flash; am besten auf einer Basis mit Schraubklemmen, die sich bei Vibrationen nicht löst) — seit Version 0.2.0 der einzige unterstützte</td><td>WLAN, Webseite, Zeitpläne, Benachrichtigungen und das W-Bus-Protokoll über seinen UART2 (IO16/IO17)</td></tr>
<tr><td>W-Bus-Transceiver</td><td>Modul <b>UART ↔ LIN/K-Line mit TJA1020</b> (das verwendete ist von Gutol: Schraubklemmen 12V/GND/LIN/INH und TX/RX/SLP/GND, ohne VCC-Pin)</td><td>Passt den 3,3-V-TTL-Pegel des ESP32 an den Eindraht-Bus mit 12 V an. Jedes Modul mit TJA1020 oder gleichwertig geht (TJA1021, MCP2003, L9637D)</td></tr>
<tr><td>Optional: Display</td><td><b>I2C-OLED SSD1327 1,5"</b> (128×128, 16 Graustufen; empfohlen) oder eines mit 128×64: 1,3" (SH1106) oder 0,96" (SSD1306). Adresse 0x3C oder 0x3D</td><td>Innentemperatur, Zustand, Kühlwasser, Batterie und was als Nächstes passiert. Der Typ wird in den Einstellungen gewählt</td></tr>
<tr><td>Optional: Thermometer</td><td>I2C-Modul <b>SHT31</b> oder <b>AHT20</b> (Temperatur und Feuchte)</td><td>Innentemperatur: „Heizen bis X °C“, genauere Abfahrtszeit und Kondenswasser auf einen Blick</td></tr>
<tr><td>Stromversorgung</td><td>Abwärtsregler <b>LM2596</b> (Modul mit Potentiometer), eingestellt auf <b>5,0 V</b></td><td>Erzeugt die 5 V für den ESP32 aus dem Dauer-Plus +12 V des Steckers</td></tr>
<tr><td>Verkabelung</td><td>Dupont-Kabel (Buchse) für den ESP32, 0,5-mm²-Kabel für die 12-V-Seite, Verbinder oder Lüsterklemmen und Band oder Schrumpfschlauch</td><td>Verbindungen laut Schaltplan und Kabelliste oben</td></tr>
<tr><td>Werkzeug</td><td>Multimeter, kleiner Schraubendreher, PC mit USB (Linux oder Windows)</td><td>Kabel des Steckers bestimmen, LM2596 einstellen, ESP32 programmieren und erster Test über die Konsole</td></tr>
</tbody></table>
TXT,
'hwHint' => 'Optional: ein <b>Schalter</b> im +12 V des ESP32, wenn das Fahrzeug wochenlang steht (siehe „Stromverbrauch“ unten), und eine 1-A-Sicherung in der Leitung, falls das +12 V des Steckers keine hat. VCDS ist für den Betrieb nicht nötig, hilft aber beim Auslesen und Löschen von Fehlern im Steuergerät 18 (Zusatzheizung).',
'instNeed' => <<<'TXT'
<h2 class="sec">Firmware aufspielen: was du brauchst</h2>
<ul class="pasos">
<li><b>Am einfachsten:</b> <a href="{install}">im Browser installieren</a> (Chrome oder Edge, ein Klick, nichts von dem Folgenden). Das Folgende ist für die Arduino IDE.</li>
<li><b>Arduino IDE 2</b> (am einfachsten) oder <b>arduino-cli</b> (nur Terminal).</li>
<li>Den <b>ESP32-Core von Espressif</b> („esp32 by Espressif Systems“), Version 2.x oder 3.x. Die IDE lädt ihn selbst herunter: mehrere hundert MB.</li>
<li>Board <b>ESP32S3 Dev Module</b> mit dem Partitionsschema <b>Huge APP (3MB No OTA/1MB SPIFFS)</b>: mit Bluetooth und WLAN passt das Programm nicht in die normale Partition.</li>
<li>Ein <b>USB-Datenkabel</b>: viele Ladekabel übertragen keine Daten, und der PC sieht die Platine nicht.</li>
<li><b>USB-Seriell-Treiber</b>: der ESP32-S3 hat zwei USB-Anschlüsse; den mit <b>UART</b> oder <b>COM</b> beschrifteten nehmen, er hat einen CH343 oder CP2102 (der andere ist das USB des Chips selbst). Unter Linux geht es ohne Installation; unter Windows 10/11 installiert er sich meist selbst, und falls kein COM-Port erscheint, den „CP210x VCP driver“ von Silicon Labs installieren.</li>
<li>Keine zusätzlichen Bibliotheken: Bluetooth, WLAN, Webserver und Preferences kommen mit dem ESP32-Core.</li>
</ul>
TXT,
'instIde' => <<<'TXT'
<h2 class="sec">Mit Arduino IDE 2 (Linux und Windows)</h2>
<ol class="pasos">
<li>Die IDE von <a href="https://www.arduino.cc/en/software" target="_blank" rel="noopener">arduino.cc/en/software</a> installieren. Unter Windows mit dem .exe-Installer oder <code>winget install ArduinoSA.IDE.stable</code>; unter Linux das AppImage (ausführbar machen).</li>
<li><b>Datei → Einstellungen → „Zusätzliche Boardverwalter-URLs“</b>, hinzufügen:<br><code class="sel">https://espressif.github.io/arduino-esp32/package_esp32_index.json</code></li>
<li><b>Werkzeuge → Board → Boardverwalter</b>, nach „esp32“ suchen und <b>esp32 von Espressif Systems</b> installieren.</li>
<li>Das Zip entpacken: es entsteht der Ordner <code>WTTC/</code> mit <code>WTTC.ino</code> und <code>web.h</code> (die IDE verlangt, dass der Ordner wie die .ino heißt). <code>WTTC.ino</code> öffnen.</li>
<li><b>Werkzeuge → Board → esp32 → ESP32S3 Dev Module</b> und <b>Flash Size → 16MB</b>; <b>PSRAM → OPI PSRAM</b>; <b>Werkzeuge → Partition Scheme → Huge APP (3MB No OTA/1MB SPIFFS)</b> (nur wegen der Größengrenze: der Ordner <code>WTTC/</code> bringt seine eigene Partitionstabelle mit zwei Plätzen für kabellose Updates mit, die Arduino selbst verwendet); und unter <b>Port</b> den des ESP32 (<code>COMx</code> unter Windows, <code>/dev/ttyUSB0</code> unter Linux).</li>
<li><b>Hochladen</b> drücken (der Pfeil). Bleibt es bei „Connecting……“ hängen, die Taste <b>BOOT</b> der Platine gedrückt halten, bis das Schreiben beginnt.</li>
<li><b>Werkzeuge → Serieller Monitor</b> mit <b>115200</b> Baud: es erscheinen Version, Name und die <b>Bluetooth-PIN</b> deiner Platine (notieren). Für den ersten Test <code>status</code> eingeben.</li>
</ol>
TXT,
'cliLinux' => <<<'TXT'
<h2 class="sec">Mit arduino-cli unter Linux</h2>
<pre class="cmd">curl -fsSL https://raw.githubusercontent.com/arduino/arduino-cli/master/install.sh | BINDIR=~/.local/bin sh
arduino-cli config init
arduino-cli config add board_manager.additional_urls \
  https://espressif.github.io/arduino-esp32/package_esp32_index.json
arduino-cli core update-index
arduino-cli core install esp32:esp32

# im Ordner, der WTTC/ enthält
# ESP32-S3
FQBN=esp32:esp32:esp32s3:FlashSize=16M,PSRAM=opi,PartitionScheme=huge_app
arduino-cli compile --fqbn $FQBN WTTC
arduino-cli upload  --fqbn $FQBN -p /dev/ttyUSB0 WTTC
arduino-cli monitor -p /dev/ttyUSB0 -c baudrate=115200</pre>
<div class="hint">Bei „Permission denied“ auf <code>/dev/ttyUSB0</code>: <code>sudo usermod -aG dialout $USER</code> und neu anmelden. Unter Ubuntu, wenn der Port erscheint und verschwindet, <code>brltty</code> deinstallieren.</div>
TXT,
'cliWin' => <<<'TXT'
<h2 class="sec">Mit arduino-cli unter Windows (PowerShell)</h2>
<pre class="cmd">winget install ArduinoSA.CLI
arduino-cli config init
arduino-cli config add board_manager.additional_urls `
  https://espressif.github.io/arduino-esp32/package_esp32_index.json
arduino-cli core update-index
arduino-cli core install esp32:esp32

# im Ordner, der WTTC\ enthält
# ESP32-S3
$FQBN = "esp32:esp32:esp32s3:FlashSize=16M,PSRAM=opi,PartitionScheme=huge_app"
arduino-cli compile --fqbn $FQBN WTTC
arduino-cli upload  --fqbn $FQBN -p COM3 WTTC
arduino-cli monitor -p COM3 -c baudrate=115200</pre>
<div class="hint"><code>COM3</code> durch den Port ersetzen, den <code>arduino-cli board list</code> anzeigt (oder im Geräte-Manager → Anschlüsse (COM &amp; LPT)). Nach der Installation mit winget eine neue PowerShell öffnen, damit sie <code>arduino-cli</code> findet.</div>
TXT,
'howTo' => 'Bedienung',
'useAndroid' => <<<'TXT'
<h3 class="sub3">📱 Android-Handy oder -Tablet (App, über Bluetooth)</h3>
        <ol class="pasos">
          <li><a href="{apk}">WTTC.apk</a> auf dem Handy herunterladen und öffnen. Android fragt nach der Erlaubnis, „unbekannte Apps zu installieren“ aus dem Browser: für diese Installation erlauben.</li>
          <li>WTTC öffnen und die Berechtigungen für <b>Geräte in der Nähe</b> (Bluetooth) und Benachrichtigungen erteilen.</li>
          <li><b>Platine suchen</b> antippen, dein WTTC wählen und die <b>6-stellige PIN</b> eingeben (steht beim Start im seriellen Monitor und auf der Webseite der Platine, Abschnitt Einstellungen).</li>
          <li>Fertig: ab dann verbindet sich die App von selbst, wenn die Platine in Reichweite ist (10–20 m durch die Karosserie), und stellt ihre Uhr.</li>
          <li>Schaltet sich die Heizung bei geöffneter App selbst ab, zeigt Android eine Benachrichtigung mit dem Grund und ihren Fehlercodes.</li>
          <li>Noch keine Platine? <b>Ohne Platine testen</b> startet den Demomodus: eine simulierte Platine, um zu sehen, wie die App funktioniert.</li>
          <li><b>Firmware aktualisieren:</b> Einstellungen → <b>Nach Updates suchen</b>. Gibt es eine neue Version, zeigt sie die Neuerungen und, wenn du zustimmst, lädt die Platine sie herunter, installiert sie und startet selbst neu (sie muss mit einem Netz mit Internet verbunden sein). Mit Telegram meldet sich die Platine außerdem selbst, wenn eine erscheint.</li>
        </ol>
TXT,
'useIphone' => <<<'TXT'
<h3 class="sub3">🍏 iPhone (Webseite, über WLAN)</h3>
        <ol class="pasos">
          <li>Es gibt keine iPhone-App: man nutzt die Webseite der Platine, dieselbe wie im Simulator.</li>
          <li>Einstellungen → WLAN → Netz <b>WTTC</b> (Passwort ab Werk <code>calefaccion</code>: beim ersten Mal verlangt die Webseite, es zu ändern, bevor man sie bedienen kann) und in Safari <b>http://192.168.4.1</b> öffnen.</li>
          <li>Teilen → <b>Zum Home-Bildschirm</b>: dann verhält sie sich wie eine App.</li>
          <li>Damit das Netz der Platine verfügbar ist, wenn du es brauchst, das WLAN auf „Immer an“ oder „Nur während des Heizens“ stellen (siehe Stromverbrauch). Bei „Nur auf Anfrage“ ist es nur die 10 Minuten nach dem Start da.</li>
          <li>Wenn die Platine sich mit dem Hotspot des iPhones verbindet, antwortet sie auch unter <b>http://wttc.local</b>, ohne das Netz zu wechseln.</li>
          <li>Zum Aktualisieren: Einstellungen → <b>Nach Updates suchen</b> (Platine mit einem Netz mit Internet verbunden) oder die <code>.ota</code>-Datei der neuesten Version im selben Abschnitt hochladen.</li>
        </ol>
TXT,
'useRadio' => <<<'TXT'
<h3 class="sub3">🚐 Android-Autoradio im Fahrzeug</h3>
        <ol class="pasos">
          <li><b>Zuerst prüfen</b>, ob sein Bluetooth für Apps taugt: <b>nRF Connect</b> (Play Store, kostenlos) auf dem Radio installieren und Scan antippen. Erscheinen BLE-Geräte, funktioniert die WTTC-App.</li>
          <li>Die APK installieren: im Browser des Radios herunterladen oder auf einen USB-Stick kopieren und mit seinem Dateimanager öffnen.</li>
          <li>Koppeln wie am Handy. Die Oberfläche passt sich an das Radio an (zentrierte Spalte, große Tasten).</li>
          <li>Sieht nRF Connect <b>nichts</b>, ist das Bluetooth des Radios ein eigenes Modul nur für Telefonie und Musik. Dann die Webseite nutzen: das Radio mit dem WLAN <b>WTTC</b> verbinden und http://192.168.4.1 öffnen. Solange hat das Radio kein Internet über WLAN.</li>
        </ol>
TXT,
'power' => <<<'TXT'
<h2 class="sec">Stromverbrauch und WLAN-Modi</h2>
        <table class="hw"><thead><tr><th>Modus (Einstellungen)</th><th>Wann das WLAN an ist</th><th>Ca. Verbrauch im Stand</th></tr></thead><tbody>
          <tr><td>Immer an (Standard)</td><td>Die ganze Zeit</td><td>≈ 40–60 mA (1–1,5 Ah/Tag)</td></tr>
          <tr><td>Nur während des Heizens (am besten bei tagelangem Stillstand)</td><td>Beim Heizen und 10 min danach</td><td>≈ 15–25 mA nur mit Bluetooth</td></tr>
          <tr><td>Nur auf Anfrage</td><td>Wenn du es in der App anforderst (15 min) und ein paar Minuten zum Senden von Benachrichtigungen</td><td>≈ 15–25 mA</td></tr>
        </tbody></table>
        <div class="hint">Geschätzte Werte bei 12 V, LM2596 inklusive; miss deinen mit dem Multimeter in Reihe. In allen Modi ist das WLAN nach dem Start 10 Minuten an, als Rettungsweg, falls dein Handy nicht gekoppelt ist. Der Prozessor läuft mit 80 MHz, um weniger zu verbrauchen.</div>
TXT,
'tgHow' => <<<'TXT'
<h2 class="sec">Telegram-Benachrichtigungen (optional)</h2>
        <ol class="pasos">
          <li>In Telegram mit <b>@BotFather</b> schreiben, <code>/newbot</code> senden und den Schritten folgen: er gibt dir den <b>Token</b> deines Bots.</li>
          <li>Den Chat mit deinem neuen Bot öffnen und <b>Starten</b> antippen (sonst kann der Bot dir nicht schreiben).</li>
          <li>Deine <b>Chat-ID</b>: dem Bot etwas schreiben und <code>https://api.telegram.org/bot&lt;TOKEN&gt;/getUpdates</code> öffnen; es ist die Zahl in <code>"chat":{"id":…}</code>.</li>
          <li>In der App oder auf der Webseite der Platine → Einstellungen: Token, Chat-ID und ein <b>Netzwerk mit Internet</b> (zum Beispiel der Hotspot des Handys). Speichern und <b>Telegram testen</b> antippen.</li>
          <li>Sie meldet sich beim Einschalten, beim Ausschalten, wenn sie sich selbst abschaltet (mit ihren Fehlern), wenn sie nicht mehr antwortet und wenn ein Zeitplan wegen schwacher Batterie nicht startet.</li>
        </ol>
TXT,
'security' => <<<'TXT'
<h2 class="sec" style="margin-top:18px">Sicherheit</h2>
    <ul class="pasos">
      <li>Bluetooth mit <b>PIN-Kopplung</b> und verschlüsselter Verbindung: ohne Kopplung lässt sich nichts lesen oder senden. Die PIN wird auf jeder Platine beim ersten Start zufällig erzeugt.</li>
      <li>Wenn du dein Handy verlierst: Einstellungen → <b>Kopplungen löschen</b> (auch über die serielle Konsole: <code>forget</code>) und die PIN ändern.</li>
      <li>Das eigene WLAN hat ab Werk ein öffentliches Passwort (<code>calefaccion</code>): bis du ein anderes wählst, lässt sich die Webseite der Platine nicht bedienen und zeigt die PIN nicht, und die App fragt beim Verbinden danach. Der Telegram-Token wird nach dem Speichern nie mehr angezeigt.</li>
      <li>Die Webseite der Platine nimmt nur Befehle von sich selbst an (nicht von anderen Webseiten auf dem Handy), und Telegram-Benachrichtigungen prüfen das Zertifikat des Servers.</li>
      <li>Kabellose Updates sind <b>signiert</b>: die Platine installiert nur offizielle, nie eine ältere Version als die installierte und nie während des Heizens. Läuft die neue nicht eine Minute lang, kehrt sie von selbst zur vorherigen zurück.</li>
      <li>Anonyme Statistik: die App fragt beim ersten Öffnen (zwei gleichwertige Tasten, „Ja, senden“ oder „Nein, danke“). Was gesendet wird und die Ergebnisse stehen in der <a href="{stats}">öffentlichen Statistik</a> (auf Spanisch).</li>
      <li>Die Webasto behält ihre eigenen Schutzfunktionen: sie braucht alle paar Sekunden die Keep-Alive-Nachricht (hängt sich die Platine auf oder wird getrennt, schaltet sie sich mit ihrem Nachlauf selbst ab), und jeder Start hat seine Höchstdauer (60 min).</li>
    </ul>
TXT,
'framesHead' => <<<'TXT'
<h2 class="sec">Telegramme der Firmware</h2>
      <table>
        <thead><tr><th>Aktion</th><th>Telegramm</th><th>Antwort der Webasto</th></tr></thead>
TXT,
'cCmd' => 'Befehl (hex)',
'cDat' => 'Daten (hex)',
'cOut' => 'Telegramm mit Prüfsumme',
'simNotes' => <<<'TXT'
<h2 class="sec">Was simuliert wird</h2>
      <ul class="notes">
        <li><b>Diesel:</b> geschätzt aus der von der Webasto gemeldeten Leistung (≈ 0,62 l/h bei 5 kW laut Datenblatt); die Platine führt den aktuellen Lauf, den letzten, den Monat und die Summe. Es ist eine Schätzung (±20 %), keine Tankmessung.</li>
        <li><b>Nicht simuliert:</b> Bluetooth (die Android-App) und das Abschalten des WLANs zum Sparen: hier erreicht die Webseite des Handys die Platine immer.</li>
        <li><b>Wie im Code:</b> Webseite, API und Konsole sind die der Firmware: Wiederholungen, Echo, Weckimpuls nach 10 s Stille, Keep-Alive alle 5 s, Sensoren alle 8 s, Zeitpläne, Abschaltung bei Batterie &lt; 12,0 V und eine Uhr, die beim Neustart verloren geht.</li>
        <li><b>Wie im Protokoll:</b> Telegrammformat und Antworten für Sensoren (0x50/05) und Fehler (0x56/01) laut libwbus.</li>
        <li><b>Näherungsweise:</b> Start- und Nachlaufzeiten, Regelschwellen (Volllast bis 75 °C, Teillast bis 85 °C, Wiederanlauf bei 70 °C), die Erwärmung des Kreislaufs und dass sich die Webasto nach 20 s ohne Keep-Alive abschaltet (Wert aus libwbus, an deiner nicht geprüft).</li>
        <li><b>Echter Zustand:</b> die Firmware wertet die Keep-Alive-Antwort aus (01 = die Webasto hat den Befehl nicht mehr) sowie die Flamme: „Startet“, „Heizt“, „Pause“ (keine Flamme bei Kühlwasser ≥ 65 °C) oder „Keine Antwort“. Schaltet sie sich selbst ab (probier „Kein Diesel“), zeigt die Webseite das mit ihren Fehlern an und meldet es über Telegram. Dass die Thermo Top C mit 01 auf das Keep-Alive antwortet, stammt aus libwbus: tut deine das nicht, bleibt die Flamme als Rückfallebene (5 min ohne Flamme bei kaltem Kühlwasser).</li>
      </ul>
TXT,
'legal' => <<<'TXT'
<h2 class="sec">Haftungsausschluss</h2>
    <div class="legal">
      <p><b>1. Gegenstand.</b> WTTC (im Folgenden „das Projekt“) umfasst die Firmware für ESP32 und ESP32-S3, die Android-App,
        diese Webseite mit ihrem Simulator, die Schaltpläne, die Dokumentation und die zugehörigen Dienste (anonyme Statistik
        und Updates). Es ist ein privates, nicht gewinnorientiertes Projekt seines Autors (matatunos, Rechteinhaber laut
        Lizenz), das der Öffentlichkeit kostenlos zur Verfügung gestellt wird. Es ist kein kommerzielles Produkt: es wird nicht
        verkauft, hat keinen technischen Support und keinen Kundendienst und hat keine Typgenehmigung, Zertifizierung oder
        Konformitätsprüfung durchlaufen.</p>
      <p><b>2. Keine Gewährleistung.</b> Das Projekt wird „wie besehen“ und „wie verfügbar“ unter der Lizenz GPL v3 (oder später) bereitgestellt,
        ohne jede ausdrückliche oder stillschweigende Gewährleistung, insbesondere ohne Gewähr für Funktion, Marktgängigkeit,
        Eignung für einen bestimmten Zweck, Fehlerfreiheit, Kompatibilität mit einem bestimmten Fahrzeug oder einer bestimmten
        Heizung und Fortbestand der Dienste. Solange die Versionsnummer mit 0 beginnt, ist es eine Testversion: sie kompiliert
        und wurde im Simulator geprüft, aber noch nicht eingebaut an einer echten Webasto-Heizung erprobt.</p>
      <p><b>3. Richtigkeit der Angaben.</b> Die Dokumentation (Farben und Funktion der Kabel, Anschlüsse, Verbrauch, Fehlercodes,
        Dieselschätzungen und alle anderen technischen Angaben) beruht auf öffentlichen Quellen und auf Annahmen, die nicht an
        jedem Fahrzeug geprüft wurden. Sie kann Fehler oder Lücken enthalten und ersetzt weder das Handbuch des Herstellers noch
        den Schaltplan des Fahrzeugs noch das Urteil einer Fachkraft. Prüfe sie selbst, bevor du etwas anschließt.</p>
      <p><b>4. Haftungsausschluss.</b> Soweit gesetzlich zulässig, haftet der Autor nicht für Schäden oder Nachteile jeglicher Art,
        ob unmittelbar oder mittelbar, Neben- oder Folgeschäden —einschließlich unter anderem Personenschäden,
        Kohlenmonoxidvergiftung, Brand, Schäden an der Heizung, am Fahrzeug, an dessen Batterie oder Elektrik, Verlust der
        Garantie oder des Versicherungsschutzes, Bußgelder, Stilllegung des Fahrzeugs, Datenverlust, entgangener Gewinn oder
        Reparaturkosten—, die aus dem Herunterladen, dem Einbau, der Nutzung, der Unmöglichkeit der Nutzung, der Änderung oder
        der Weitergabe des Projekts, aus Fehlern seiner Software oder Dokumentation oder aus der Unterbrechung oder dem Ausfall
        von Diensten Dritter, von denen es abhängt (wie GitHub oder Telegram), entstehen oder damit zusammenhängen.</p>
      <p><b>5. Verantwortung des Nutzers.</b> Wer das Projekt einbaut oder nutzt, tut dies aus eigener Entscheidung, auf
        ausschließlich eigene Verantwortung, und trägt alle Risiken. Insbesondere obliegt es ihm:</p>
      <ul class="pasos">
        <li>Den Einbau mit den nötigen Kenntnissen für sicheres Arbeiten an 12-V-Elektrik und in der Nähe von
          Kraftstoffsystemen vorzunehmen oder vornehmen zu lassen und die Versorgung mit einer Sicherung abzusichern.</li>
        <li><b>Die Heizung niemals zu programmieren oder einzuschalten, wenn das Fahrzeug in einer Garage oder einem anderen
          geschlossenen oder schlecht belüfteten Raum steht.</b> Die Abgase enthalten Kohlenmonoxid, das geruchlos ist und
          tödlich sein kann.</li>
        <li>Den Betrieb bei den ersten Einsätzen zu beobachten und immer eine Möglichkeit zu behalten, die Heizung ohne das
          Projekt abzuschalten, etwa durch Ziehen der Sicherung der Heizung selbst. (Laut der Dokumentation des
          W-Bus-Protokolls schaltet sich die Heizung ohne Keep-Alive-Nachricht in Sekunden selbst ab; an einer echten
          Installation ist das nicht geprüft.)</li>
        <li>Zu prüfen, ob die Änderung die Garantie des Fahrzeugs oder der Heizung, den Versicherungsschutz oder die für sie
          geltenden Vorschriften berührt, und beim Ausbau den Originalzustand wiederherzustellen.</li>
      </ul>
      <p><b>6. Mit künstlicher Intelligenz erzeugte Software.</b> Der gesamte Code und Inhalt des Projekts wurde nach den
        Vorgaben des Autors mit Claude erzeugt, einem KI-Modell von Anthropic. Er kann unentdeckte Fehler enthalten. Anthropic
        ist nicht Teil des Projekts und haftet nicht dafür.</p>
      <p><b>7. Marken.</b> Webasto, Thermo Top, Volkswagen, Climatronic, Espressif, ESP32, Arduino, Android, Telegram und GitHub
        sind Marken oder Bezeichnungen ihrer jeweiligen Inhaber. Das Projekt steht in keiner Beziehung zu ihnen und wird von
        ihnen weder genehmigt noch unterstützt; die Namen dienen nur dazu, anzugeben, mit welchen Geräten und Diensten es
        kompatibel ist oder funktioniert.</p>
      <p><b>8. Grenzen dieses Ausschlusses.</b> Nichts davon schließt die Haftung aus oder beschränkt sie, wo das Gesetz dies
        nicht zulässt, etwa bei Vorsatz oder grober Fahrlässigkeit. Sollte ein Teil dieses Textes unwirksam oder nicht
        durchsetzbar sein, bleibt der Rest gültig.</p>
      <p><b>9. Annahme und anwendbares Recht.</b> Wer einen Teil des Projekts herunterlädt, einbaut oder nutzt, erklärt, diesen
        Haftungsausschluss und die Lizenz GPL v3 gelesen und angenommen zu haben; wer nicht einverstanden ist, nutzt es nicht. Für
        hier nicht Geregeltes gilt spanisches Recht. Bei Abweichungen zwischen den Sprachfassungen ist die spanische Fassung
        maßgeblich.</p>
      <p class="muted">Dieser Text ergänzt den Gewährleistungs- und Haftungsausschluss der Lizenz GPL v3 (Abschnitte 15 und 16; Datei LICENSE im
        Repository), ohne ihn zu ersetzen.</p>
TXT,
'credit' => <<<'TXT'
WTTC · freie Software (GPL v3) auf <a href="https://github.com/matatunos/wttc" target="_blank" rel="noopener">GitHub</a> ·
    Code und Inhalt vollständig mit Claude (Anthropic) erzeugt · privates Projekt ohne Verbindung zu Webasto oder Volkswagen.
TXT,
'relGithub' => 'Release auf GitHub ↗',
'verWord' => 'Version',
'boardSim' => 'Platine: Display, LED und Thermometer',
'oledAria' => 'OLED-Display der simulierten Platine',
'ledLbl' => 'LED der Platine',
'bootTitle' => 'BOOT-Taste der Platine: schaltet das Display für eine Minute ein',
'cabNow' => 'Innen (real)',
'hwScr' => 'OLED-Display angeschlossen',
'hwSens' => 'Thermometer angeschlossen',
'cabSet' => 'Innen setzen auf',
'boardHint' => 'Das Display wird Pixel für Pixel wie auf der Platine gezeichnet (das SSD1327 mit 128×128 in Graustufen oder das mit 128×64, wenn in den Einstellungen gewählt; gleiche Schrift und gleiche Texte). Im Automatikmodus bleibt es an, solange die Webseite links offen ist, genau wie bei der echten Platine mit offener Webseite. Wird ein Teil entfernt, gilt es nach wenigen Sekunden als getrennt, und was davon abhängt, verschwindet von der Webseite; wieder angeschlossen, wird es innerhalb einer halben Minute erkannt.',
'testTitle' => 'Tests und PDF-Bericht',
'tAmb' => 'Außen',
'tCab' => 'Innen zu Beginn',
'tTgt' => 'Bis',
'tNoTgt' => 'ohne Thermostat',
'tDur' => 'Dauer',
'tBatt' => 'Batterie',
'tRun' => 'Test ausführen und PDF herunterladen',
'tRecStart' => 'Aufzeichnen, was ich tue',
'tRecStop' => 'Beenden und PDF herunterladen',
'testHint' => '„Test ausführen“ schaltet mit diesen Werten ein und springt mit der simulierten Zeit sofort bis zum Ende (ein paar Sekunden); dann wird ein PDF mit Diagrammen zu Temperaturen, Starts, Leistung, Diesel und Batterie, der Tabelle der Starts und dem Protokoll der Platine heruntergeladen. „Aufzeichnen, was ich tue“ hält fest, was passiert, während du den Simulator benutzt (bei jeder Geschwindigkeit), bis du es beendest. Das Wärmemodell ist ungefähr.',
'sim' => [
    'tRunning' => 'Test läuft…', 'tDone' => 'Test fertig: {0} Starts, {1} l Diesel. PDF wird heruntergeladen…', 'tNoSens' => '„Bis“ braucht das Thermometer: unter „Platine“ als angeschlossen markieren.', 'tBusy' => 'Es läuft bereits eine Aufzeichnung: zuerst beenden.', 'tRecOn' => 'Aufzeichnung… Simulator benutzen und am Ende „Beenden“ drücken.', 'tPdfErr' => 'Das PDF konnte nicht erstellt werden.', 'tRecStart' => 'Aufzeichnen, was ich tue', 'tRecStop' => 'Beenden und PDF herunterladen',
    'oledProbe' => "wird erkannt…\n(bis zu 30 s)", 'oledDisabled' => "in den Einstellungen aus\n(BOOT schaltet es eine Minute ein)", 'oledNone' => 'kein Display', 'oledOff' => "Display aus\n(BOOT drücken)",
    'st' => ['OFF' => 'Aus', 'FAN' => 'Gebläseanlauf', 'GLOW' => 'Glühstift heizt vor', 'IGN' => 'Zündung: Diesel fließt',
             'STAB' => 'Flamme stabilisiert sich', 'FULL' => 'Verbrennung mit Volllast', 'PART' => 'Verbrennung mit Teillast',
             'PAUSE' => 'Regelpause', 'AFTER' => 'Nachlauf (schaltet ab)', 'FAIL' => 'Nachlauf wegen Fehler', 'LOCK' => 'Verriegelt (Interlock)'],
    'errn' => ['1' => 'Steuergerät defekt', '2' => 'kein Start', '3' => 'Flammabriss', '4' => 'Spannung zu hoch',
               '6' => 'Überhitzung', '7' => 'verriegelt (Interlock)', '18' => 'W-Bus-Kommunikationsfehler'],
    'stopTime' => 'Befehlszeit abgelaufen',
    'stopKa' => '20 s ohne Keep-Alive',
    'stopCmd' => 'Ausschaltbefehl',
    'stopF2' => 'Sicherung F2 5 s gezogen',
    'txOn' => 'Standheizung einschalten {0} min',
    'txOff' => 'Ausschalten',
    'txKa' => 'Keep-Alive: Befehl 0x{0} weiter ausführen',
    'txSens' => 'Sensoren lesen (Register 0x05)',
    'txReg' => 'Register 0x{0} lesen',
    'txErrList' => 'Fehlerliste lesen',
    'txErrClr' => 'Fehler löschen',
    'txErr' => 'Fehler',
    'txCmd' => 'Befehl 0x{0}',
    'rxOn' => 'Angenommen: {0} min',
    'rxOff' => 'Ausschalten angenommen',
    'rxKaOff' => 'Befehl NICHT aktiv (01): die Webasto heizt nicht mehr',
    'rxKaOn' => 'Befehl aktiv (00)',
    'rxSens' => 'Kühlwasser {0} °C · {1} V · Flamme {2} · {3} W',
    'yes' => 'ja',
    'no' => 'nein',
    'rxNoErr' => 'Keine Fehler gespeichert',
    'rxErrs' => '{0} Fehler: {1}',
    'rxResp' => 'Antwort',
    'noEchoSlp' => 'Kein Echo: der TJA1020 schläft (SLP nicht angeschlossen)',
    'noEchoCross' => 'Kein Echo: TX/RX vertauscht, das Telegramm erreicht den Bus nicht',
    'echo' => 'Echo des eigenen Telegramms (verworfen)',
    'noRxWbus' => 'Keine Antwort in 500 ms: der W-Bus erreicht die Webasto nicht',
    'noRxPower' => 'Keine Antwort in 500 ms: die Webasto hat keinen Strom',
    'noRxCmd' => 'Keine Antwort: Befehl nicht unterstützt',
    'noise' => 'Kommt mit einem gekippten Bit an: die Prüfsumme stimmt nicht und die Firmware verwirft es',
    'brk' => 'Weckimpuls: TX 50 ms auf Low (mehr als 10 s Stille)',
    'simulated' => ' (simuliert)',
    'comps' => ['Gebläse', 'Glühstift', 'Dosierpumpe', 'Flamme', 'Wasserpumpe'],
    'booting' => 'ESP32 startet…',
    'realTime' => 'Echtzeit',
    'timeX' => 'Zeit ×{0}',
    'paused' => 'pausiert',
    'upFor' => ' · ESP32 seit {0} an',
    'noPower' => 'Kein Strom',
    'errMem' => 'Fehlerspeicher: ',
    'errMemEmpty' => 'Fehlerspeicher leer.',
    'lastStop' => ' · Letzte Abschaltung: {0}.',
    'lastHour' => 'letzte Stunde',
    'echoTag' => 'Echo',
    'busEmpty' => 'Über den Bus ist noch nichts gelaufen. Am Handy einschalten oder in der Konsole „status“ eingeben.',
    'tgEmpty' => 'Keine Nachrichten. Um sie zu sehen, auf der Webseite der Platine „Einstellungen“ öffnen und ein Netzwerk mit Internet (irgendeins), einen erfundenen Token und Chat eintragen; speichern und den Neustart abwarten.',
    'fr' => ['Einschalten 30 min', 'Ausschalten', 'Sensoren lesen', 'Fehler lesen'],
    'data' => '(Daten)',
    'loc' => 'de-DE',
],
];
