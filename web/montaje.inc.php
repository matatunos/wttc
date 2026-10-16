<?php
// montaje.inc.php — Esquema de conexiones (dibujo, lista de cables y pasos) de WTTC, en el idioma de t().
// Código y contenido generados íntegramente con Claude (Anthropic).
// Pieza común: lo incluyen la portada (index.php, sección «Montaje») y la versión para imprimir (esquema.php, de la que
// sale el PDF). Sus estilos están en montaje.css. Si cambia un cable, se cambia aquí y en i18n/*.php (cables, steps).
?>
<div class="wirebox"><svg class="wire" viewBox="0 120 1130 800" role="img" aria-label="<?= t('svgAria') ?>">
<text x="20" y="200" class="t"><?= t('svgConn') ?></text><text x="20" y="216" class="m"><?= t('svgConnSub') ?></text>
<rect x="20" y="230" width="110" height="220" rx="8" fill="#3a3f4f" stroke="#5d6680" stroke-width="2"/>
<rect x="112" y="252" width="18" height="16" rx="3" fill="#252936"/>
<rect x="112" y="292" width="18" height="16" rx="3" fill="#252936"/>
<rect x="112" y="372" width="18" height="16" rx="3" fill="#252936"/>
<rect x="112" y="412" width="18" height="16" rx="3" fill="#252936"/>
<?= t('svgSilk') ?>
<g id="xPower" class="hide"><text x="20" y="182" class="bad"><?= t('svgNoPower') ?></text></g>
<text x="350" y="192" class="t"><?= t('svgBoard') ?></text><text x="350" y="208" class="m"><?= t('svgBoardSub') ?></text>
<rect x="350" y="220" width="220" height="200" rx="6" fill="#1f6f3a" stroke="#2c8a4c" stroke-width="2"/>
<rect x="350" y="235" width="30" height="170" rx="3" fill="#2fb37a" stroke="#1b7a50"/>
<rect x="540" y="235" width="30" height="170" rx="3" fill="#2fb37a" stroke="#1b7a50"/>
<circle cx="365" cy="260" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="254" x2="371" y2="266" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="260" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="254" x2="561" y2="266" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="264" class="silk">GND</text><text x="532" y="264" class="silk" text-anchor="end">GND</text>
<circle cx="365" cy="300" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="294" x2="371" y2="306" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="300" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="294" x2="561" y2="306" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="304" class="silk">LIN</text><text x="532" y="304" class="silk" text-anchor="end">RX</text>
<circle cx="365" cy="340" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="334" x2="371" y2="346" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="340" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="334" x2="561" y2="346" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="344" class="silk">INH</text><text x="532" y="344" class="silk" text-anchor="end">SLP</text>
<circle cx="365" cy="380" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="359" y1="374" x2="371" y2="386" stroke="#5d6680" stroke-width="2"/>
<circle cx="555" cy="380" r="9" fill="#c9cfe0" stroke="#7a84a8"/><line x1="549" y1="374" x2="561" y2="386" stroke="#5d6680" stroke-width="2"/>
<text x="388" y="384" class="silk">12V</text><text x="532" y="384" class="silk" text-anchor="end">TX</text>
<rect x="435" y="260" width="50" height="30" rx="2" fill="#111"/><text x="460" y="279" class="pin" text-anchor="middle">TJA1020</text>
<circle cx="460" cy="360" r="18" fill="#2b2b2b"/><text x="460" y="364" class="pin" text-anchor="middle">47µF</text>
<text x="460" y="408" class="m" text-anchor="middle"><?= t('svgInh') ?></text>
<text x="350" y="488" class="t"><?= t('svgReg') ?></text>
<rect x="350" y="500" width="220" height="120" rx="6" fill="#1d4f91" stroke="#2f6cc0" stroke-width="2"/>
<rect x="357" y="507" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="365" cy="515" r="4" fill="#5d6680"/>
<text x="379" y="519" class="silk" text-anchor="start">IN−</text>
<rect x="357" y="597" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="365" cy="605" r="4" fill="#5d6680"/>
<text x="379" y="609" class="silk" text-anchor="start">IN+</text>
<rect x="547" y="507" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="555" cy="515" r="4" fill="#5d6680"/>
<text x="541" y="519" class="silk" text-anchor="end">OUT+</text>
<rect x="547" y="597" width="16" height="16" rx="2" fill="#e8ecf5"/><circle cx="555" cy="605" r="4" fill="#5d6680"/>
<text x="541" y="609" class="silk" text-anchor="end">OUT−</text>
<rect x="428" y="532" width="56" height="56" rx="10" fill="#1a1a1a"/><text x="456" y="564" class="pin" text-anchor="middle">470</text>
<circle cx="402" cy="560" r="15" fill="#b9bfcc"/><text x="402" y="563" class="pin" text-anchor="middle" style="fill:#333">100µF</text>
<rect x="500" y="540" width="34" height="26" rx="2" fill="#2f6cc0" stroke="#7fb0ff"/><circle cx="517" cy="553" r="6" fill="#e0b84a"/>
<text x="517" y="580" class="pin" text-anchor="middle"><?= t('svgScrew') ?></text><text x="517" y="591" class="pin" text-anchor="middle"><?= t('svg5v') ?></text>
<text x="650" y="150" class="t"><?= t('svgEsp') ?></text><text x="650" y="166" class="m"><?= t('svgEspSub') ?></text>
<rect x="650" y="285" width="460" height="170" rx="8" fill="#16181e" stroke="#3a3f4f" stroke-width="2"/>
<path d="M660 330 h26 v10 h-26 v10 h26 v10 h-26 v10 h26 v10 h-26 v10 h26" fill="none" stroke="#c9a54a" stroke-width="3"/>
<rect x="700" y="330" width="220" height="80" rx="3" fill="#9aa0ac"/><text x="810" y="364" text-anchor="middle" style="fill:#222;font-weight:700">ESP32-S3-WROOM-1</text><text x="810" y="382" text-anchor="middle" style="fill:#333;font-size:12px">N16R8 · 16 MB</text>
<rect x="955" y="345" width="16" height="12" rx="2" fill="#2b2b2b"/><text x="963" y="372" class="pin" text-anchor="middle">BOOT</text><rect x="1000" y="345" width="16" height="12" rx="2" fill="#2b2b2b"/><text x="1008" y="372" class="pin" text-anchor="middle">RST</text>
<rect x="1098" y="333" width="28" height="22" rx="4" fill="#9aa0ac"/><text x="1090" y="348" class="pin use" text-anchor="end">UART</text><rect x="1098" y="385" width="28" height="22" rx="4" fill="#9aa0ac"/><text x="1090" y="400" class="pin" text-anchor="end">USB</text>
<circle cx="665" cy="297" r="6" fill="#e0b84a" stroke="#9aa3b5" stroke-width="3"/>
<text class="pin use" transform="translate(668,308) rotate(90)">GND</text>
<circle cx="685" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(688,308) rotate(90)">TX</text>
<circle cx="705" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(708,308) rotate(90)">RX</text>
<circle cx="725" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(728,308) rotate(90)">IO1</text>
<circle cx="745" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(748,308) rotate(90)">IO2</text>
<circle cx="765" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(768,308) rotate(90)">IO42</text>
<circle cx="785" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(788,308) rotate(90)">IO41</text>
<circle cx="805" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(808,308) rotate(90)">IO40</text>
<circle cx="825" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(828,308) rotate(90)">IO39</text>
<circle cx="845" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(848,308) rotate(90)">IO38</text>
<circle cx="865" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(868,308) rotate(90)">IO37</text>
<circle cx="885" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(888,308) rotate(90)">IO36</text>
<circle cx="905" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(908,308) rotate(90)">IO35</text>
<circle cx="925" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(928,308) rotate(90)">IO0</text>
<circle cx="945" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(948,308) rotate(90)">IO45</text>
<circle cx="965" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(968,308) rotate(90)">IO48</text>
<circle cx="985" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(988,308) rotate(90)">IO47</text>
<circle cx="1005" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1008,308) rotate(90)">IO21</text>
<circle cx="1025" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1028,308) rotate(90)">IO20</text>
<circle cx="1045" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1048,308) rotate(90)">IO19</text>
<circle cx="1065" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1068,308) rotate(90)">GND</text>
<circle cx="1085" cy="297" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1088,308) rotate(90)">GND</text>
<circle cx="665" cy="443" r="6" fill="#e0b84a" stroke="#b57bff" stroke-width="3"/>
<text class="pin use" transform="translate(668,432) rotate(-90)">3V3</text>
<circle cx="685" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(688,432) rotate(-90)">3V3</text>
<circle cx="705" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(708,432) rotate(-90)">RST</text>
<circle cx="725" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(728,432) rotate(-90)">IO4</text>
<circle cx="745" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(748,432) rotate(-90)">IO5</text>
<circle cx="765" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(768,432) rotate(-90)">IO6</text>
<circle cx="785" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(788,432) rotate(-90)">IO7</text>
<circle cx="805" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(808,432) rotate(-90)">IO15</text>
<circle cx="825" cy="443" r="6" fill="#e0b84a" stroke="#3ecf8e" stroke-width="3"/>
<text class="pin use" transform="translate(828,432) rotate(-90)">IO16</text>
<circle cx="845" cy="443" r="6" fill="#e0b84a" stroke="#4aa8ff" stroke-width="3"/>
<text class="pin use" transform="translate(848,432) rotate(-90)">IO17</text>
<circle cx="865" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(868,432) rotate(-90)">IO18</text>
<circle cx="885" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(888,432) rotate(-90)">IO8</text>
<circle cx="905" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(908,432) rotate(-90)">IO3</text>
<circle cx="925" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(928,432) rotate(-90)">IO46</text>
<circle cx="945" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(948,432) rotate(-90)">IO9</text>
<circle cx="965" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(968,432) rotate(-90)">IO10</text>
<circle cx="985" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(988,432) rotate(-90)">IO11</text>
<circle cx="1005" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1008,432) rotate(-90)">IO12</text>
<circle cx="1025" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1028,432) rotate(-90)">IO13</text>
<circle cx="1045" cy="443" r="4.5" fill="#e0b84a" stroke="none" stroke-width="3"/>
<text class="pin" transform="translate(1048,432) rotate(-90)">IO14</text>
<circle cx="1065" cy="443" r="6" fill="#e0b84a" stroke="#ff9f1c" stroke-width="3"/>
<text class="pin use" transform="translate(1068,432) rotate(-90)">5V</text>
<circle cx="1085" cy="443" r="6" fill="#e0b84a" stroke="#9aa3b5" stroke-width="3"/>
<text class="pin use" transform="translate(1088,432) rotate(-90)">GND</text>
<g><path class="halo" d="M230 260 V515 H365"/><path class="cw" d="M230 260 V515 H365" stroke="#a0703c"/><circle cx="230" cy="455" r="11" fill="var(--bg-card)" stroke="#a0703c" stroke-width="2.5"/><text x="230" y="455.5" class="tag" style="fill:#a0703c">5</text></g>
<g><path class="halo" d="M200 380 V605 H365"/><path class="cw" d="M200 380 V605 H365" stroke="#e04848"/><circle cx="200" cy="560" r="11" fill="var(--bg-card)" stroke="#e04848" stroke-width="2.5"/><text x="200" y="560.5" class="tag" style="fill:#e04848">4</text></g>
<g><path class="halo" d="M130 260 H365"/><path class="cw" d="M130 260 H365" stroke="#a0703c"/><circle cx="300" cy="260" r="11" fill="var(--bg-card)" stroke="#a0703c" stroke-width="2.5"/><text x="300" y="260.5" class="tag" style="fill:#a0703c">1</text></g>
<g><path class="halo" d="M130 300 H365"/><path class="cw" d="M130 300 H365" stroke="#9aa3bd" style="stroke-width:8"/><path class="cw" d="M130 300 H365" stroke="#111318"/><circle cx="240" cy="300" r="11" fill="var(--bg-card)" stroke="#fff" stroke-width="2.5"/><text x="240" y="300.5" class="tag" style="fill:#fff">2</text></g>
<g><path class="halo" d="M130 380 H365"/><path class="cw" d="M130 380 H365" stroke="#e04848"/><circle cx="300" cy="380" r="11" fill="var(--bg-card)" stroke="#e04848" stroke-width="2.5"/><text x="300" y="380.5" class="tag" style="fill:#e04848">3</text></g>
<g><path class="halo" d="M130 420 H168"/><path class="cw" d="M130 420 H168" stroke="#e8d23a"/></g>
<rect x="166" y="411" width="22" height="18" rx="4" fill="#5d6680"/><text x="20" y="648" class="m"><?= t('svgYellow') ?></text>
<circle cx="230" cy="260" r="6" fill="#a0703c" stroke="var(--bg-card)" stroke-width="2"/><circle cx="200" cy="380" r="6" fill="#e04848" stroke="var(--bg-card)" stroke-width="2"/>
<g id="xWbus" class="hide"><line class="xx" x1="290" y1="288" x2="314" y2="312"/><line class="xx" x1="314" y1="288" x2="290" y2="312"/><text x="270" y="330" class="bad"><?= t('svgWbus') ?></text></g>
<g><path class="halo" d="M555 605 H1085 V443"/><path class="cw" d="M555 605 H1085 V443" stroke="#9aa3b5"/><circle cx="800" cy="605" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5"/><text x="800" y="605.5" class="tag" style="fill:#9aa3b5">7</text></g>
<g><path class="halo" d="M555 515 H1065 V443"/><path class="cw" d="M555 515 H1065 V443" stroke="#ff9f1c"/><circle cx="800" cy="515" r="11" fill="var(--bg-card)" stroke="#ff9f1c" stroke-width="2.5"/><text x="800" y="515.5" class="tag" style="fill:#ff9f1c">6</text></g>
<g id="slpW"><g><path class="halo" d="M555 340 H610 V468 H665 V443"/><path class="cw" d="M555 340 H610 V468 H665 V443" stroke="#b57bff"/><circle cx="610" cy="415" r="11" fill="var(--bg-card)" stroke="#b57bff" stroke-width="2.5"/><text x="610" y="415.5" class="tag" style="fill:#b57bff">10</text></g></g>
<g id="slpX" class="hide"><path d="M555 340 H595" stroke="var(--bad)" stroke-width="3" stroke-dasharray="5 4"/><text x="620" y="560" class="bad"><?= t('svgSlp') ?></text></g>
<g><path class="halo" d="M555 260 H665 V297"/><path class="cw" d="M555 260 H665 V297" stroke="#9aa3b5"/><circle cx="615" cy="260" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5"/><text x="615" y="260.5" class="tag" style="fill:#9aa3b5">11</text></g>
<g id="sigOk"><g><path class="halo" d="M555 300 H630 V484 H845 V443"/><path class="cw" d="M555 300 H630 V484 H845 V443" stroke="#4aa8ff"/><circle cx="760" cy="484" r="11" fill="var(--bg-card)" stroke="#4aa8ff" stroke-width="2.5"/><text x="760" y="484.5" class="tag" style="fill:#4aa8ff">9</text></g><g><path class="halo" d="M555 380 H590 V500 H825 V443"/><path class="cw" d="M555 380 H590 V500 H825 V443" stroke="#3ecf8e"/><circle cx="700" cy="500" r="11" fill="var(--bg-card)" stroke="#3ecf8e" stroke-width="2.5"/><text x="700" y="500.5" class="tag" style="fill:#3ecf8e">8</text></g></g>
<g id="sigX" class="hide"><g><path class="halo" d="M555 300 H630 V484 H825 V443"/><path class="cw" d="M555 300 H630 V484 H825 V443" stroke="var(--bad)" stroke-dasharray="10 6"/><circle cx="760" cy="484" r="11" fill="var(--bg-card)" stroke="var(--bad)" stroke-width="2.5"/><text x="760" y="484.5" class="tag" style="fill:var(--bad)">9</text></g><g><path class="halo" d="M555 380 H590 V500 H845 V443"/><path class="cw" d="M555 380 H590 V500 H845 V443" stroke="var(--bad)" stroke-dasharray="10 6"/><circle cx="700" cy="500" r="11" fill="var(--bg-card)" stroke="var(--bad)" stroke-width="2.5"/><text x="700" y="500.5" class="tag" style="fill:var(--bad)">8</text></g><text x="870" y="540" class="bad"><?= t('svgCross') ?></text></g>
<!-- Opcional: pantalla OLED y termómetro en el bus I2C (IO4/IO5) y botón «calentar» (IO7 a GND). Recuadro y cables
     discontinuos: el montaje funciona sin ellos -->
<g id="optHw">
<rect x="15" y="668" width="1107" height="247" rx="12" fill="none" stroke="#5d6680" stroke-width="2" stroke-dasharray="10 7"/>
<text x="35" y="698" class="t"><?= t('svgOpt') ?></text><text x="35" y="716" class="m"><?= t('svgOptSub') ?></text>
<circle cx="685" cy="443" r="7" fill="none" stroke="#ff7aa8" stroke-width="2.5" stroke-dasharray="3 2"/>
<circle cx="725" cy="443" r="7" fill="none" stroke="#2ec4b6" stroke-width="2.5" stroke-dasharray="3 2"/>
<circle cx="745" cy="443" r="7" fill="none" stroke="#e9c46a" stroke-width="2.5" stroke-dasharray="3 2"/>
<g><path class="halo" d="M685 443 V725"/><path class="cw" d="M685 443 V725" stroke="#ff7aa8" stroke-dasharray="9 6"/><circle cx="685" cy="560" r="11" fill="var(--bg-card)" stroke="#ff7aa8" stroke-width="2.5" stroke-dasharray="3 2"/><text x="685" y="560.5" class="tag" style="fill:#ff7aa8">12</text></g>
<g><path class="halo" d="M1085 605 V740"/><path class="cw" d="M1085 605 V740" stroke="#9aa3b5" stroke-dasharray="9 6"/><circle cx="1085" cy="650" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5" stroke-dasharray="3 2"/><text x="1085" y="650.5" class="tag" style="fill:#9aa3b5">13</text></g>
<circle cx="1085" cy="605" r="4" fill="#9aa3b5"/>
<g><path class="halo" d="M725 443 V755"/><path class="cw" d="M725 443 V755" stroke="#2ec4b6" stroke-dasharray="9 6"/><circle cx="725" cy="640" r="11" fill="var(--bg-card)" stroke="#2ec4b6" stroke-width="2.5" stroke-dasharray="3 2"/><text x="725" y="640.5" class="tag" style="fill:#2ec4b6">14</text></g>
<g><path class="halo" d="M745 443 V770"/><path class="cw" d="M745 443 V770" stroke="#e9c46a" stroke-dasharray="9 6"/><circle cx="745" cy="560" r="11" fill="var(--bg-card)" stroke="#e9c46a" stroke-width="2.5" stroke-dasharray="3 2"/><text x="745" y="560.5" class="tag" style="fill:#e9c46a">15</text></g>
<path class="cw" d="M685 725 H920" stroke="#ff7aa8" stroke-dasharray="9 6" style="stroke-width:3"/>
<path class="cw" d="M680 740 H1085" stroke="#9aa3b5" stroke-dasharray="9 6" style="stroke-width:3"/>
<path class="cw" d="M725 755 H995" stroke="#2ec4b6" stroke-dasharray="9 6" style="stroke-width:3"/>
<path class="cw" d="M730 770 H970" stroke="#e9c46a" stroke-dasharray="9 6" style="stroke-width:3"/>
<rect x="640" y="790" width="180" height="78" rx="6" fill="#0b0d12" stroke="#3a3f4f" stroke-width="2"/>
<rect x="655" y="812" width="150" height="46" rx="3" fill="#061424" stroke="#2a4a6a"/><text x="730" y="842" text-anchor="middle" style="fill:#5BC0EB;font-size:18px;font-weight:700">21,4°</text>
<path class="cw" d="M680 790 V740" stroke="#9aa3b5" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="680" cy="740" r="4" fill="#9aa3b5"/>
<circle cx="680" cy="795" r="4" fill="#e0b84a"/><text x="680" y="808" class="pin" text-anchor="middle">GND</text>
<path class="cw" d="M705 790 V725" stroke="#ff7aa8" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="705" cy="725" r="4" fill="#ff7aa8"/>
<circle cx="705" cy="795" r="4" fill="#e0b84a"/><text x="705" y="808" class="pin" text-anchor="middle">VCC</text>
<path class="cw" d="M730 790 V770" stroke="#e9c46a" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="730" cy="770" r="4" fill="#e9c46a"/>
<circle cx="730" cy="795" r="4" fill="#e0b84a"/><text x="730" y="808" class="pin" text-anchor="middle">SCL</text>
<path class="cw" d="M755 790 V755" stroke="#2ec4b6" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="755" cy="755" r="4" fill="#2ec4b6"/>
<circle cx="755" cy="795" r="4" fill="#e0b84a"/><text x="755" y="808" class="pin" text-anchor="middle">SDA</text>
<text x="730" y="884" class="m" text-anchor="middle"><?= t('svgOled') ?></text>
<rect x="900" y="790" width="120" height="70" rx="6" fill="#1f4e8c" stroke="#2f6ab0" stroke-width="2"/><rect x="948" y="824" width="24" height="18" rx="2" fill="#c9cfe0"/>
<path class="cw" d="M920 790 V725" stroke="#ff7aa8" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="920" cy="725" r="4" fill="#ff7aa8"/>
<circle cx="920" cy="795" r="4" fill="#e0b84a"/><text x="920" y="808" class="pin" text-anchor="middle">VIN</text>
<path class="cw" d="M945 790 V740" stroke="#9aa3b5" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="945" cy="740" r="4" fill="#9aa3b5"/>
<circle cx="945" cy="795" r="4" fill="#e0b84a"/><text x="945" y="808" class="pin" text-anchor="middle">GND</text>
<path class="cw" d="M970 790 V770" stroke="#e9c46a" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="970" cy="770" r="4" fill="#e9c46a"/>
<circle cx="970" cy="795" r="4" fill="#e0b84a"/><text x="970" y="808" class="pin" text-anchor="middle">SCL</text>
<path class="cw" d="M995 790 V755" stroke="#2ec4b6" stroke-dasharray="9 6" style="stroke-width:3"/>
<circle cx="995" cy="755" r="4" fill="#2ec4b6"/>
<circle cx="995" cy="795" r="4" fill="#e0b84a"/><text x="995" y="808" class="pin" text-anchor="middle">SDA</text>
<text x="960" y="884" class="m" text-anchor="middle"><?= t('svgSens') ?></text>
<!-- Botón «calentar»: pulsador normalmente abierto entre IO7 (⑯) y masa (⑰, al cable de masa de las piezas opcionales) -->
<circle cx="785" cy="443" r="7" fill="none" stroke="#f15bb5" stroke-width="2.5" stroke-dasharray="3 2"/>
<g><path class="halo" d="M785 443 V700 H500 V792"/><path class="cw" d="M785 443 V700 H500 V792" stroke="#f15bb5" stroke-dasharray="9 6"/><circle cx="600" cy="700" r="11" fill="var(--bg-card)" stroke="#f15bb5" stroke-width="2.5" stroke-dasharray="3 2"/><text x="600" y="700.5" class="tag" style="fill:#f15bb5">16</text></g>
<g><path class="halo" d="M540 792 V740 H680"/><path class="cw" d="M540 792 V740 H680" stroke="#9aa3b5" stroke-dasharray="9 6"/><circle cx="610" cy="740" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5" stroke-dasharray="3 2"/><text x="610" y="740.5" class="tag" style="fill:#9aa3b5">17</text></g>
<rect x="480" y="792" width="80" height="56" rx="6" fill="#2a2f3d" stroke="#5d6680" stroke-width="2"/>
<circle cx="520" cy="826" r="15" fill="#e04848" stroke="#8c2b2b" stroke-width="2"/>
<circle cx="500" cy="797" r="4" fill="#e0b84a"/><circle cx="540" cy="797" r="4" fill="#e0b84a"/>
<text x="520" y="884" class="m" text-anchor="middle"><?= t('svgBtn') ?></text>
<!-- Sensor de monóxido (ZE07-CO, 0.3.9+): 5 V del cable ⑥ (⑱), masa del ⑬ (⑲) y su TXD a IO15 (⑳) -->
<circle cx="805" cy="443" r="7" fill="none" stroke="#a3e635" stroke-width="2.5" stroke-dasharray="3 2"/>
<g><path class="halo" d="M1105 790 V690 H805 V443"/><path class="cw" d="M1105 790 V690 H805 V443" stroke="#a3e635" stroke-dasharray="9 6"/><circle cx="950" cy="690" r="11" fill="var(--bg-card)" stroke="#a3e635" stroke-width="2.5" stroke-dasharray="3 2"/><text x="950" y="690.5" class="tag" style="fill:#a3e635">20</text></g>
<g><path class="halo" d="M1065 515 V790"/><path class="cw" d="M1065 515 V790" stroke="#ff9f1c" stroke-dasharray="9 6"/><circle cx="1065" cy="560" r="11" fill="var(--bg-card)" stroke="#ff9f1c" stroke-width="2.5" stroke-dasharray="3 2"/><text x="1065" y="560.5" class="tag" style="fill:#ff9f1c">18</text></g>
<g><path class="halo" d="M1085 740 V790"/><path class="cw" d="M1085 740 V790" stroke="#9aa3b5" stroke-dasharray="9 6"/><circle cx="1085" cy="765" r="11" fill="var(--bg-card)" stroke="#9aa3b5" stroke-width="2.5" stroke-dasharray="3 2"/><text x="1085" y="765.5" class="tag" style="fill:#9aa3b5">19</text></g>
<circle cx="1065" cy="515" r="4" fill="#ff9f1c"/><circle cx="1085" cy="740" r="4" fill="#9aa3b5"/>
<rect x="1046" y="790" width="74" height="84" rx="6" fill="#1d3b2c" stroke="#2f6a4c" stroke-width="2"/>
<circle cx="1083" cy="846" r="17" fill="#c9cfe0" stroke="#7a84a8" stroke-width="2"/><circle cx="1083" cy="846" r="9" fill="#5d6680"/>
<circle cx="1065" cy="795" r="4" fill="#e0b84a"/><text class="pin" transform="translate(1068,802) rotate(90)">VIN</text>
<circle cx="1085" cy="795" r="4" fill="#e0b84a"/><text class="pin" transform="translate(1088,802) rotate(90)">GND</text>
<circle cx="1105" cy="795" r="4" fill="#e0b84a"/><text class="pin" transform="translate(1108,802) rotate(90)">TXD</text>
<text x="1118" y="896" class="m" text-anchor="end"><?= t('svgCo') ?></text><text x="1118" y="910" class="m" text-anchor="end"><?= t('svgCoSub') ?></text>
</g>
</svg></div>
<div class="hint"><?= t('wireHint') ?></div>
<div class="row2" style="margin-top:14px">
<div>
<?= t('cables') ?></div>
<div>
<?= t('steps') ?></div></div>
