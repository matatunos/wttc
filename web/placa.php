<?php
// wttc/placa.php — La placa portadora de WTTC (diseño abierto en KiCad para encargarla en JLCPCB), en español, inglés
// y alemán. Código y contenido generados íntegramente con Claude (Anthropic).
// URLs: /placa.php (es), /en/board.php (en) y /de/platine.php (de); los dos últimos solo fijan $lang.
// Las imágenes y los ficheros de fabricación vienen de hardware/placa/fabricacion/ del repo (los copia deploy-web.sh
// a placa/): esta página solo los enseña y enlaza.
require_once __DIR__ . '/api/db.php';
wttc_public_only();
$lang = in_array($lang ?? 'es', ['es', 'en', 'de'], true) ? ($lang ?? 'es') : 'es';
wttc_visit($lang === 'es' ? 'placa' : 'placa-' . $lang);
$URLS = ['es' => 'https://wttc.favala.es/placa.php', 'en' => 'https://wttc.favala.es/en/board.php', 'de' => 'https://wttc.favala.es/de/platine.php'];
$HOME = ['es' => '/', 'en' => '/en/', 'de' => '/de/'];
$INST = ['es' => '/instalar.php', 'en' => '/en/install.php', 'de' => '/de/installieren.php'];
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$GH = 'https://github.com/matatunos/wttc/tree/main/hardware/placa';
$F = '/placa/';   // ficheros de fabricación (deploy-web.sh)
$v = fn($f) => is_file(__DIR__ . $F . $f) ? '?v=' . filemtime(__DIR__ . $F . $f) : '';

$TX = [
'es' => [
  'title' => 'Placa portadora de WTTC para encargar (KiCad, JLCPCB)',
  'desc' => 'Una placa en la que se pincha la ESP32-S3 y que lleva protecciones, 5 V, el transceptor del W-Bus y conectores para pantalla, termómetro, sensor de CO y botón. Diseño abierto en KiCad, listo para encargar montada en JLCPCB.',
  'h1' => 'La placa de WTTC',
  'intro' => 'Una placa en la que <b>se pincha la ESP32-S3-DevKitC-1</b> y que lleva todo lo demás: entrada del vehículo con protecciones, los 5 V, el transceptor del W-Bus y conectores para las piezas opcionales. Sustituye al montaje con módulos (placa TJA1020, regulador LM2596 y cables dupont): se puede encargar entera, lista para pinchar la ESP32, o con las piezas pequeñas y soldar tú la borna, los zócalos y las tiras.',
  'warn' => '<b>Diseño sin probar.</b> Nadie la ha encargado ni montado todavía. Las comprobaciones de KiCad (eléctrica y de reglas de diseño) salen limpias, pero eso no garantiza que funcione. Si la encargas, <a href="https://github.com/matatunos/wttc/issues">cuéntalo</a>: la primera que se monte dirá si hay que corregir algo.',
  'top' => 'Cara de arriba', 'bottom' => 'Cara de abajo', 'd3' => 'En 3D (sin la ESP32 pinchada)',
  'what' => 'Qué lleva',
  'cols' => ['Bloque', 'Piezas', 'Notas'],
  'rows' => [
    ['Entrada del vehículo', 'Borna de 3 tornillos: <b>12V · GND · WBUS</b>', 'Los tres cables del conector del temporizador. Mídelos con el polímetro: los colores cambian entre vehículos.'],
    ['Protecciones', 'Fusible rearmable de 0,5 A, diodo contra polaridad inversa (SS34) y supresor de picos (SMBJ24A)', 'El supresor recorta por debajo de los 40 V que aguantan el transceptor y el regulador.'],
    ['5 V', 'Regulador conmutado <b>LMR16006X</b> (4–60 V, 0,6 A, 28 µA en reposo)', 'El ejemplo de 5 V de la hoja de datos de TI. Sustituye al LM2596.'],
    ['W-Bus', 'Transceptor <b>TJA1021T/20</b>, con la resistencia de nodo maestro (1 kΩ y diodo)', 'Mismo patillaje que el TJA1020 de los módulos. El puente «MAESTRO» viene hecho; cortándolo se quita la resistencia.'],
    ['ESP32-S3', 'Dos zócalos hembra de 22 pines, <b>filas a 25,4 mm</b>', 'La antena queda fuera de la placa, sin cobre debajo.'],
    ['Pantalla', 'Tira de 4: <b>GND · 3V3 · SCL · SDA</b>', 'Orden de las OLED de 0,96" y 1,3". Si la tuya va en otro orden, cables dupont.'],
    ['Termómetro', 'Tira de 4 (<b>3V3 · GND · SCL · SDA</b>) y conector <b>Qwiic</b>', 'SHT31 o AHT20; el Qwiic, para módulos con conector JST-SH.'],
    ['Sensor de CO', 'Tira de 4: <b>5V · GND · TXD · RXD</b>', 'ZE07-CO: su TXD va al IO15 y su RXD al IO6.'],
    ['Botón «calentar»', 'Pulsador en la placa y tira de 2 para uno externo', 'IO7 a masa, como en el montaje con módulos.'],
  ],
  'pins' => 'Los pines son los del firmware: <b>no hay que cambiar nada en WTTC</b>. Placa de 95 × 62 mm, dos caras, con planos de masa en las dos y cuatro taladros M3.',
  'files' => 'Ficheros',
  'pkNote' => '<b>Los paquetes no se suben tal cual a JLCPCB</b> (sale «menor de 10 × 10 mm»): descomprímelos y sube dentro el <b>wttc-placa-gerber.zip</b> (sin descomprimir) como placa, y la BOM y el CPL en el montaje.',
  'f' => ['pkT' => 'Encargo «todo» (entera, .zip)', 'pkS' => 'Encargo «smd» (.zip)', 'gerber' => 'Solo Gerber (.zip)', 'bomT' => 'BOM «todo»', 'cplT' => 'CPL «todo»', 'bomS' => 'BOM «smd»', 'cplS' => 'CPL «smd»', 'lista' => 'Lista de materiales completa', 'sch' => 'Esquema (PDF)', 'src' => 'Proyecto KiCad y generador (GitHub)'],
  'order' => 'Encargarla en JLCPCB',
  'orderList' => '<li>En <a href="https://jlcpcb.com" target="_blank" rel="noopener">jlcpcb.com</a> → «Order now», sube el <b>.zip de Gerber</b>. Lo detecta solo: 2 capas, 95 × 62 mm. Lo demás, tal cual (1,6 mm de grosor).</li><li>Activa <b>«PCB Assembly»</b> en la cara de arriba (<b>Top side</b>) y sube la <b>BOM</b> y el <b>CPL</b> de uno de los dos juegos:<ul><li><b>«todo»</b> (montaje «Standard»): llega <b>entera</b>, solo hay que pinchar la ESP32-S3.</li><li><b>«smd»</b> (montaje «Economic», más barato): llega con las piezas pequeñas; la borna, los zócalos y las tiras los sueldas tú.</li></ul>Todas las piezas llevan su código LCSC. Las «extended» cobran unos 3 $ cada una por pedido. En ningún caso va la ESP32-S3 (se compra aparte y se pincha) ni los módulos opcionales.</li><li><b>Revisa el giro de las piezas en su vista previa</b>: KiCad y JLCPCB no siempre coinciden en el ángulo cero. Es normal tener que girar a mano el regulador, el transceptor o algún diodo hasta que el pin 1 y la raya del cátodo coincidan con la serigrafía.</li><li>Con el juego «smd», lo de agujero pasante lo sueldas tú: la borna, los dos zócalos de 22 y las tiras de pines.</li>',
  'price' => 'A ojo (octubre de 2026): unos 40–50 $ por cinco placas, dos de ellas montadas, más el envío. Lo que más pesa son las tarifas fijas del montaje, no las piezas.',
  'mount' => 'Montaje y primera prueba',
  'mountList' => '<li>Con el juego «smd», suelda la borna, los zócalos y las tiras. Los pines de masa van unidos al plano sin «radios»: piden un soldador con algo de potencia.</li><li><b>Sin la ESP32 pinchada</b>, conecta 12 V a la borna y mide unos <b>5,0 V</b> entre 5V y GND de la tira del sensor de CO. Si no, desconecta y revisa.</li><li><b>Mide la distancia entre las filas de pines de tu ESP32-S3</b>: tiene que ser 25,4 mm (la de Espressif y las copias habituales). Pínchala con la antena hacia el borde de la derecha.</li><li>A partir de ahí, como siempre: <a href="{inst}">instala WTTC</a> y mira en la consola que el W-Bus contesta.</li>',
  'usb' => '<b>Ojo con el USB:</b> igual que con el montaje con módulos, no conectes el USB del ordenador con la placa alimentada por la furgoneta hasta comprobar que tu ESP32-S3 separa las dos alimentaciones.',
  'limits' => 'Limitaciones conocidas (v0.1)',
  'limitsList' => '<li>La entrada SLP del transceptor va fija a 3V3, como en el montaje con módulo. Si la ESP32 se alimenta por USB antes de conectar los 12 V, el transceptor podría no despertar hasta el siguiente arranque.</li><li>Sin resistencias de I2C en la placa: las llevan los módulos de pantalla y termómetro.</li><li>Con la ESP32 en zócalos, todo mide unos 20 mm de alto: hace falta una caja de al menos 25 mm por dentro.</li>',
  'how' => 'Cómo está hecha',
  'howText' => 'Generada con Claude desde código: un fichero describe las piezas (con sus códigos LCSC) y sus conexiones, y un script saca el esquema, coloca las piezas, ruta las pistas con Freerouting, pasa las comprobaciones de KiCad 9 y prepara los ficheros. Se abre y se retoca en KiCad 9 normal.',
  'back' => '← WTTC',
],
'en' => [
  'title' => 'WTTC carrier board to order (KiCad, JLCPCB)',
  'desc' => 'A board the ESP32-S3 plugs into, with protection, 5 V, the W-Bus transceiver and connectors for display, thermometer, CO sensor and button. Open KiCad design, ready to order assembled from JLCPCB.',
  'h1' => 'The WTTC board',
  'intro' => 'A board that <b>the ESP32-S3-DevKitC-1 plugs into</b>, carrying everything else: vehicle input with protection, 5 V, the W-Bus transceiver and connectors for the optional parts. It replaces the module build (TJA1020 board, LM2596 regulator and dupont wires): it can be ordered complete, ready for the ESP32, or with the small parts only, soldering the terminal block, sockets and headers yourself.',
  'warn' => '<b>Untested design.</b> Nobody has ordered or built one yet. KiCad’s checks (electrical and design rules) are clean, but that does not guarantee it works. If you order it, <a href="https://github.com/matatunos/wttc/issues">tell us</a>: the first one built will show whether anything needs fixing.',
  'top' => 'Top side', 'bottom' => 'Bottom side', 'd3' => 'In 3D (without the ESP32)',
  'what' => 'What it carries',
  'cols' => ['Block', 'Parts', 'Notes'],
  'rows' => [
    ['Vehicle input', '3-way screw terminal: <b>12V · GND · WBUS</b>', 'The three wires of the timer connector. Check them with a multimeter: colours vary between vehicles.'],
    ['Protection', '0.5 A resettable fuse, reverse-polarity diode (SS34) and transient suppressor (SMBJ24A)', 'The suppressor clamps below the 40 V the transceiver and regulator can take.'],
    ['5 V', '<b>LMR16006X</b> switching regulator (4–60 V, 0.6 A, 28 µA quiescent)', 'TI’s 5 V datasheet example. Replaces the LM2596.'],
    ['W-Bus', '<b>TJA1021T/20</b> transceiver with the master-node resistor (1 kΩ and diode)', 'Same pinout as the modules’ TJA1020. The «MAESTRO» jumper comes closed; cutting it removes the resistor.'],
    ['ESP32-S3', 'Two 22-pin female sockets, <b>rows 25.4 mm apart</b>', 'The antenna sits off the board, with no copper under it.'],
    ['Display', '4-pin header: <b>GND · 3V3 · SCL · SDA</b>', 'The order of 0.96" and 1.3" OLEDs. If yours differs, use dupont wires.'],
    ['Thermometer', '4-pin header (<b>3V3 · GND · SCL · SDA</b>) and <b>Qwiic</b> connector', 'SHT31 or AHT20; Qwiic for modules with a JST-SH connector.'],
    ['CO sensor', '4-pin header: <b>5V · GND · TXD · RXD</b>', 'ZE07-CO: its TXD goes to IO15 and its RXD to IO6.'],
    ['«Heat» button', 'Push button on the board and a 2-pin header for an external one', 'IO7 to ground, as in the module build.'],
  ],
  'pins' => 'The pins are the firmware’s: <b>nothing changes in WTTC</b>. 95 × 62 mm, two layers, ground planes on both and four M3 holes.',
  'files' => 'Files',
  'pkNote' => '<b>Do not upload the packages to JLCPCB as they are</b> (it reports «smaller than 10 × 10 mm»): unzip them and upload the <b>wttc-placa-gerber.zip</b> inside (still zipped) as the board, and the BOM and CPL in the assembly step.',
  'f' => ['pkT' => 'Order «todo» (complete, .zip)', 'pkS' => 'Order «smd» (.zip)', 'gerber' => 'Gerbers only (.zip)', 'bomT' => 'BOM «todo» (all)', 'cplT' => 'CPL «todo» (all)', 'bomS' => 'BOM «smd»', 'cplS' => 'CPL «smd»', 'lista' => 'Full bill of materials', 'sch' => 'Schematic (PDF)', 'src' => 'KiCad project and generator (GitHub)'],
  'order' => 'Ordering from JLCPCB',
  'orderList' => '<li>At <a href="https://jlcpcb.com" target="_blank" rel="noopener">jlcpcb.com</a> → «Order now», upload the <b>Gerber .zip</b>. It is detected automatically: 2 layers, 95 × 62 mm. Leave the rest as is (1.6 mm thick).</li><li>Enable <b>«PCB Assembly»</b> on the <b>Top side</b> and upload the <b>BOM</b> and <b>CPL</b> of one of the two sets:<ul><li><b>«todo»</b> (all, «Standard» assembly): it arrives <b>complete</b>, you only plug in the ESP32-S3.</li><li><b>«smd»</b> («Economic» assembly, cheaper): it arrives with the small parts; you solder the terminal block, sockets and headers.</li></ul>Every part has its LCSC code. «Extended» parts cost about $3 each per order. The ESP32-S3 (bought separately and plugged in) and the optional modules are never included.</li><li><b>Check the part rotations in their preview</b>: KiCad and JLCPCB do not always agree on the zero angle. It is normal to rotate the regulator, the transceiver or a diode by hand until pin 1 and the cathode band match the silkscreen.</li><li>With the «smd» set, the through-hole parts are yours to solder: the terminal block, the two 22-pin sockets and the pin headers.</li>',
  'price' => 'Rough figure (October 2026): about $40–50 for five boards, two of them assembled, plus shipping. Most of it is the fixed assembly fees, not the parts.',
  'mount' => 'Assembly and first test',
  'mountList' => '<li>With the «smd» set, solder the terminal block, sockets and headers. Ground pins are tied solidly to the plane: they need a reasonably powerful iron.</li><li><b>Without the ESP32</b>, connect 12 V to the terminal block and measure about <b>5.0 V</b> between 5V and GND of the CO sensor header. If not, disconnect and check.</li><li><b>Measure the row spacing of your ESP32-S3</b>: it must be 25.4 mm (Espressif’s and the usual clones). Plug it in with the antenna towards the right edge.</li><li>From there, as usual: <a href="{inst}">install WTTC</a> and check in the console that the W-Bus answers.</li>',
  'usb' => '<b>Careful with USB:</b> as with the module build, do not connect the computer’s USB while the board is powered by the van until you have checked that your ESP32-S3 keeps the two supplies apart.',
  'limits' => 'Known limitations (v0.1)',
  'limitsList' => '<li>The transceiver’s SLP input is tied to 3V3, as in the module build. If the ESP32 is powered over USB before the 12 V is connected, the transceiver might not wake up until the next boot.</li><li>No I2C pull-ups on the board: the display and thermometer modules have them.</li><li>With the ESP32 in sockets everything is about 20 mm tall: you need a box at least 25 mm deep inside.</li>',
  'how' => 'How it is made',
  'howText' => 'Generated with Claude from code: one file describes the parts (with their LCSC codes) and their connections, and a script produces the schematic, places the parts, routes the tracks with Freerouting, runs KiCad 9’s checks and prepares the files. It opens and can be edited in a normal KiCad 9.',
  'back' => '← WTTC',
],
'de' => [
  'title' => 'WTTC-Trägerplatine zum Bestellen (KiCad, JLCPCB)',
  'desc' => 'Eine Platine, auf die der ESP32-S3 gesteckt wird, mit Schutzbeschaltung, 5 V, W-Bus-Transceiver und Anschlüssen für Display, Thermometer, CO-Sensor und Taster. Offenes KiCad-Design, fertig bestückt bei JLCPCB bestellbar.',
  'h1' => 'Die WTTC-Platine',
  'intro' => 'Eine Platine, auf die <b>der ESP32-S3-DevKitC-1 gesteckt wird</b> und die alles andere trägt: Fahrzeugeingang mit Schutz, 5 V, den W-Bus-Transceiver und Anschlüsse für die optionalen Teile. Sie ersetzt den Aufbau mit Modulen (TJA1020-Platine, LM2596-Regler und Dupont-Kabel): sie lässt sich komplett bestückt bestellen, fertig für den ESP32, oder nur mit den kleinen Teilen, und Schraubklemme, Buchsen- und Stiftleisten lötest du selbst.',
  'warn' => '<b>Ungetestetes Design.</b> Noch niemand hat sie bestellt oder aufgebaut. Die Prüfungen von KiCad (elektrisch und Designregeln) sind sauber, aber das garantiert nicht, dass sie funktioniert. Wenn du sie bestellst, <a href="https://github.com/matatunos/wttc/issues">erzähl davon</a>: die erste aufgebaute zeigt, ob etwas zu korrigieren ist.',
  'top' => 'Oberseite', 'bottom' => 'Unterseite', 'd3' => 'In 3D (ohne ESP32)',
  'what' => 'Was drauf ist',
  'cols' => ['Block', 'Teile', 'Hinweise'],
  'rows' => [
    ['Fahrzeugeingang', '3-polige Schraubklemme: <b>12V · GND · WBUS</b>', 'Die drei Adern des Timer-Steckers. Mit dem Multimeter prüfen: die Farben unterscheiden sich je nach Fahrzeug.'],
    ['Schutz', 'Rückstellende Sicherung 0,5 A, Verpolschutzdiode (SS34) und Überspannungsschutz (SMBJ24A)', 'Der Überspannungsschutz begrenzt unter die 40 V, die Transceiver und Regler vertragen.'],
    ['5 V', 'Schaltregler <b>LMR16006X</b> (4–60 V, 0,6 A, 28 µA Ruhestrom)', 'Das 5-V-Beispiel aus dem TI-Datenblatt. Ersetzt den LM2596.'],
    ['W-Bus', 'Transceiver <b>TJA1021T/20</b> mit Master-Widerstand (1 kΩ und Diode)', 'Gleiche Belegung wie der TJA1020 der Module. Die Brücke «MAESTRO» ist geschlossen; durchtrennt entfällt der Widerstand.'],
    ['ESP32-S3', 'Zwei 22-polige Buchsenleisten, <b>Reihenabstand 25,4 mm</b>', 'Die Antenne liegt außerhalb der Platine, ohne Kupfer darunter.'],
    ['Display', '4-polige Stiftleiste: <b>GND · 3V3 · SCL · SDA</b>', 'Reihenfolge der 0,96"- und 1,3"-OLEDs. Bei anderer Reihenfolge Dupont-Kabel verwenden.'],
    ['Thermometer', '4-polige Stiftleiste (<b>3V3 · GND · SCL · SDA</b>) und <b>Qwiic</b>-Buchse', 'SHT31 oder AHT20; Qwiic für Module mit JST-SH-Stecker.'],
    ['CO-Sensor', '4-polige Stiftleiste: <b>5V · GND · TXD · RXD</b>', 'ZE07-CO: sein TXD an IO15, sein RXD an IO6.'],
    ['Taster «Heizen»', 'Taster auf der Platine und 2-polige Stiftleiste für einen externen', 'IO7 gegen Masse, wie beim Modulaufbau.'],
  ],
  'pins' => 'Die Pins sind die der Firmware: <b>an WTTC ändert sich nichts</b>. 95 × 62 mm, zwei Lagen, Masseflächen auf beiden und vier M3-Bohrungen.',
  'files' => 'Dateien',
  'pkNote' => '<b>Die Pakete nicht direkt bei JLCPCB hochladen</b> (meldet «kleiner als 10 × 10 mm»): entpacken und die enthaltene <b>wttc-placa-gerber.zip</b> (gepackt lassen) als Platine hochladen, BOM und CPL bei der Bestückung.',
  'f' => ['pkT' => 'Bestellung «todo» (komplett, .zip)', 'pkS' => 'Bestellung «smd» (.zip)', 'gerber' => 'Nur Gerber (.zip)', 'bomT' => 'BOM «todo» (alles)', 'cplT' => 'CPL «todo» (alles)', 'bomS' => 'BOM «smd»', 'cplS' => 'CPL «smd»', 'lista' => 'Vollständige Stückliste', 'sch' => 'Schaltplan (PDF)', 'src' => 'KiCad-Projekt und Generator (GitHub)'],
  'order' => 'Bei JLCPCB bestellen',
  'orderList' => '<li>Auf <a href="https://jlcpcb.com" target="_blank" rel="noopener">jlcpcb.com</a> → «Order now» die <b>Gerber-.zip</b> hochladen. Wird automatisch erkannt: 2 Lagen, 95 × 62 mm. Den Rest so lassen (1,6 mm dick).</li><li><b>«PCB Assembly»</b> auf der Oberseite (<b>Top side</b>) aktivieren und <b>BOM</b> und <b>CPL</b> eines der beiden Sätze hochladen:<ul><li><b>«todo»</b> (alles, Bestückung «Standard»): kommt <b>komplett</b>, nur noch den ESP32-S3 aufstecken.</li><li><b>«smd»</b> (Bestückung «Economic», günstiger): kommt mit den kleinen Teilen; Schraubklemme, Buchsen- und Stiftleisten lötest du selbst.</li></ul>Alle Teile haben ihren LCSC-Code. «Extended»-Teile kosten etwa 3 $ pro Teil und Bestellung. Der ESP32-S3 (separat kaufen und aufstecken) und die optionalen Module sind nie dabei.</li><li><b>Die Drehung der Teile in der Vorschau prüfen</b>: KiCad und JLCPCB sind sich beim Nullwinkel nicht immer einig. Es ist normal, Regler, Transceiver oder eine Diode von Hand zu drehen, bis Pin 1 und der Kathodenring zum Bestückungsdruck passen.</li><li>Mit dem Satz «smd» lötest du die bedrahteten Teile selbst: Schraubklemme, die zwei 22-poligen Buchsenleisten und die Stiftleisten.</li>',
  'price' => 'Grob (Oktober 2026): etwa 40–50 $ für fünf Platinen, zwei davon bestückt, plus Versand. Das meiste sind die festen Bestückungsgebühren, nicht die Teile.',
  'mount' => 'Aufbau und erster Test',
  'mountList' => '<li>Mit dem Satz «smd»: Schraubklemme, Buchsen- und Stiftleisten einlöten. Massepins sind voll an die Fläche angebunden: das braucht einen kräftigeren Lötkolben.</li><li><b>Ohne gesteckten ESP32</b> 12 V an die Klemme legen und etwa <b>5,0 V</b> zwischen 5V und GND der CO-Sensor-Leiste messen. Wenn nicht: trennen und prüfen.</li><li><b>Den Reihenabstand deines ESP32-S3 messen</b>: er muss 25,4 mm betragen (Espressif und die üblichen Nachbauten). Mit der Antenne zum rechten Rand stecken.</li><li>Danach wie immer: <a href="{inst}">WTTC installieren</a> und in der Konsole prüfen, dass der W-Bus antwortet.</li>',
  'usb' => '<b>Vorsicht mit USB:</b> wie beim Modulaufbau den USB des Computers nicht anschließen, solange die Platine vom Fahrzeug versorgt wird, bis geprüft ist, dass dein ESP32-S3 die beiden Versorgungen trennt.',
  'limits' => 'Bekannte Einschränkungen (v0.1)',
  'limitsList' => '<li>Der SLP-Eingang des Transceivers liegt fest auf 3V3, wie beim Modulaufbau. Wird der ESP32 per USB versorgt, bevor die 12 V anliegen, wacht der Transceiver eventuell erst beim nächsten Start auf.</li><li>Keine I2C-Pull-ups auf der Platine: die haben die Display- und Thermometermodule.</li><li>Mit dem ESP32 in Buchsenleisten ist alles etwa 20 mm hoch: das Gehäuse braucht innen mindestens 25 mm.</li>',
  'how' => 'Wie sie entstanden ist',
  'howText' => 'Mit Claude aus Code erzeugt: eine Datei beschreibt die Teile (mit LCSC-Codes) und ihre Verbindungen, ein Skript erstellt den Schaltplan, platziert die Teile, routet die Leiterbahnen mit Freerouting, führt die Prüfungen von KiCad 9 aus und erzeugt die Dateien. Sie lässt sich in einem normalen KiCad 9 öffnen und bearbeiten.',
  'back' => '← WTTC',
],
];
$T = $TX[$lang];
$fich = [['pkT', 'wttc-placa-encargo-todo.zip'], ['pkS', 'wttc-placa-encargo-smd.zip'], ['gerber', 'wttc-placa-gerber.zip'], ['bomT', 'wttc-placa-bom-jlcpcb-todo.csv'], ['cplT', 'wttc-placa-cpl-todo.csv'],
         ['bomS', 'wttc-placa-bom-jlcpcb-smd.csv'], ['cplS', 'wttc-placa-cpl-smd.csv'],
         ['lista', 'lista-de-materiales.csv'], ['sch', 'wttc-placa-esquema.pdf']];
?><!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $h($T['title']) ?></title>
<meta name="description" content="<?= $h($T['desc']) ?>">
<link rel="canonical" href="<?= $URLS[$lang] ?>">
<?php foreach ($URLS as $l => $u): ?><link rel="alternate" hreflang="<?= $l ?>" href="<?= $u ?>">
<?php endforeach; ?><link rel="alternate" hreflang="x-default" href="<?= $URLS['en'] ?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="WTTC">
<meta property="og:url" content="<?= $URLS[$lang] ?>">
<meta property="og:title" content="<?= $h($T['h1']) ?>">
<meta property="og:description" content="<?= $h($T['desc']) ?>">
<meta property="og:image" content="https://wttc.favala.es<?= $F ?>placa-3d.png">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<style>
  :root{--bg-page:#0f1117;--bg-card:#1a1d27;--text:#e2e8f8;--muted:#7a84a8;--border:#2e3350;--warn:#e0a93a;--warn-bg:rgba(224,169,58,.12);
    --fl:#ff9f1c;--ice:#5bc0eb;--acc:#3a8ee0}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:var(--bg-page);color:var(--text);font:15px/1.55 -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:20px 16px}
  .wrap{max-width:900px;margin:0 auto}
  a{color:var(--ice)} a.back{color:var(--muted);font-size:.85rem;text-decoration:none}
  .langs{float:right;display:flex;gap:4px;font-size:.82rem}
  .langs a{padding:3px 9px;border-radius:999px;border:1px solid var(--border);color:var(--muted);text-decoration:none}
  .langs a[aria-current]{background:var(--bg-card);color:var(--text);border-color:var(--acc)}
  h1{font-size:1.5rem;font-weight:800;margin:14px 0 8px;background:linear-gradient(135deg,#ff9f1c,#5bc0eb);
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  h2{font-size:1.05rem;margin:24px 0 8px}
  p,li,td{color:#c9d1e8} ol,ul{padding-left:22px} li{margin:6px 0}
  .aviso{background:var(--warn-bg);border:1px solid var(--warn);border-radius:12px;padding:10px 14px;font-size:.9rem;margin:14px 0}
  .fotos{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:14px 0}
  .fotos figure{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:8px;margin:0}
  .fotos .ancha{grid-column:1/-1}
  .fotos img{width:100%;height:auto;display:block;border-radius:8px}
  .fotos figcaption{color:var(--muted);font-size:.8rem;text-align:center;margin-top:4px}
  .tabla{overflow-x:auto}
  table{width:100%;border-collapse:collapse;font-size:.88rem;margin:8px 0}
  th,td{border-bottom:1px solid var(--border);padding:7px 8px;text-align:left;vertical-align:top}
  th{color:var(--muted);font-weight:600}
  .bajar{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0}
  .bajar a{background:var(--bg-card);border:1px solid var(--acc);border-radius:10px;padding:8px 14px;text-decoration:none;color:var(--text);font-weight:600;font-size:.9rem}
  .bajar a.prim{background:var(--fl);color:#1a1000;border-color:var(--fl)}
  .muted{color:var(--muted);font-size:.85rem}
  @media (max-width:640px){.fotos{grid-template-columns:1fr}}
</style>
</head>
<body><div class="wrap">
<nav class="langs" aria-label="Idioma"><?php foreach ($URLS as $l => $u): ?><a href="<?= $h(parse_url($u, PHP_URL_PATH)) ?>" hreflang="<?= $l ?>"<?= $l === $lang ? ' aria-current="page"' : '' ?>><?= strtoupper($l) ?></a><?php endforeach; ?></nav>
<a class="back" href="<?= $HOME[$lang] ?>"><?= $h($T['back']) ?></a>
<h1><?= $h($T['h1']) ?></h1>
<p><?= $T['intro'] ?></p>
<div class="aviso"><?= $T['warn'] ?></div>

<div class="fotos">
  <figure class="ancha"><img src="<?= $F ?>placa-3d.png<?= $v('placa-3d.png') ?>" alt="<?= $h($T['d3']) ?>" loading="lazy"><figcaption><?= $h($T['d3']) ?></figcaption></figure>
  <figure><img src="<?= $F ?>placa-top.png<?= $v('placa-top.png') ?>" alt="<?= $h($T['top']) ?>" loading="lazy"><figcaption><?= $h($T['top']) ?></figcaption></figure>
  <figure><img src="<?= $F ?>placa-bottom.png<?= $v('placa-bottom.png') ?>" alt="<?= $h($T['bottom']) ?>" loading="lazy"><figcaption><?= $h($T['bottom']) ?></figcaption></figure>
</div>

<h2><?= $h($T['files']) ?></h2>
<div class="bajar">
<?php foreach ($fich as $i => [$k, $f]): ?>  <a<?= $i < 2 ? ' class="prim"' : '' ?> href="<?= $F . $f . $v($f) ?>" download><?= $h($T['f'][$k]) ?></a>
<?php endforeach; ?>  <a href="<?= $GH ?>" target="_blank" rel="noopener"><?= $h($T['f']['src']) ?></a>
</div>
<div class="aviso"><?= $T['pkNote'] ?></div>

<h2><?= $h($T['what']) ?></h2>
<div class="tabla"><table>
<tr><?php foreach ($T['cols'] as $c): ?><th><?= $h($c) ?></th><?php endforeach; ?></tr>
<?php foreach ($T['rows'] as $r): ?><tr><td><b><?= $h($r[0]) ?></b></td><td><?= $r[1] ?></td><td><?= $r[2] ?></td></tr>
<?php endforeach; ?>
</table></div>
<p class="muted"><?= $T['pins'] ?></p>

<h2><?= $h($T['order']) ?></h2>
<ol><?= $T['orderList'] ?></ol>
<p class="muted"><?= $h($T['price']) ?></p>

<h2><?= $h($T['mount']) ?></h2>
<ol><?= str_replace('{inst}', $INST[$lang], $T['mountList']) ?></ol>
<div class="aviso"><?= $T['usb'] ?></div>

<h2><?= $h($T['limits']) ?></h2>
<ul><?= $T['limitsList'] ?></ul>

<h2><?= $h($T['how']) ?></h2>
<p><?= $h($T['howText']) ?></p>
</div></body></html>
