<?php
// falsa.inc.php — Dibujo de cómo conectar la Webasto falsa (un segundo ESP32) con la placa de WTTC: directa o por el
// bus. Código generado íntegramente con Claude (Anthropic). Pieza común: lo incluyen la portada (index.php, «Probar
// sin furgoneta») e instalar.php (junto al botón de la Webasto falsa); textos con t() (i18n/t.php, claves wf*).
// Es herramientas/webasto_falsa/esquema.svg con los textos traducidos: si cambia uno, cambiar el otro.
?>
<style>
#wfsvg .wf-bg{fill:#14171f}#wfsvg .wf-t{fill:#e8ecf6;font-size:19px;font-weight:700}#wfsvg .wf-s{fill:#9aa3bd;font-size:13px}
#wfsvg .wf-p{fill:#e8ecf6;font-size:13px;font-weight:600}#wfsvg .wf-esp{fill:#2a2f45;stroke:#4a5478;stroke-width:2}
#wfsvg .wf-tja{fill:#1e5a3c;stroke:#2e8a5c;stroke-width:2}#wfsvg .wf-w{fill:none;stroke-width:4;stroke-linecap:round;stroke-linejoin:round}
#wfsvg .wf-pin{fill:#e0b84a}#wfsvg .wf-tag{font-size:12px;font-weight:700;text-anchor:middle;dominant-baseline:central}
</style>
<div class="wirebox"><svg id="wfsvg" class="wire" viewBox="0 0 1000 760" role="img" aria-label="<?= t('wfAria') ?>" font-family="Segoe UI, Roboto, Arial, sans-serif">
<rect class="wf-bg" width="1000" height="760" rx="14"/>

  <!-- ===================== A) Directa ===================== -->
  <text x="30" y="42" class="wf-t"><?= t('wfA') ?></text>
  <text x="30" y="64" class="wf-s"><?= t('wfAsub') ?></text>

  <!-- ESP32 falso -->
  <rect x="40" y="95" width="250" height="190" rx="10" class="wf-esp"/>
  <text x="165" y="125" text-anchor="middle" class="wf-p"><?= t('wfEsp') ?></text>
  <text x="165" y="145" text-anchor="middle" class="wf-s"><?= t('wfEspSub') ?></text>
  <circle cx="290" cy="185" r="6" class="wf-pin"/><text x="278" y="190" text-anchor="end" class="wf-p">TX · IO17</text>
  <circle cx="290" cy="220" r="6" class="wf-pin"/><text x="278" y="225" text-anchor="end" class="wf-p">RX · IO16</text>
  <circle cx="290" cy="255" r="6" class="wf-pin"/><text x="278" y="260" text-anchor="end" class="wf-p">GND</text>
  <rect x="40" y="170" width="26" height="40" rx="4" fill="#5d6680"/><text x="80" y="275" class="wf-s"><?= t('wfUsb') ?></text>

  <!-- WTTC -->
  <rect x="660" y="95" width="290" height="190" rx="10" class="wf-esp"/>
  <text x="805" y="125" text-anchor="middle" class="wf-p">WTTC · ESP32-S3</text>
  <text x="805" y="145" text-anchor="middle" class="wf-s"><?= t('wfNoTja') ?></text>
  <circle cx="660" cy="185" r="6" class="wf-pin"/><text x="672" y="190" class="wf-p"><?= t('wfListen') ?></text>
  <circle cx="660" cy="220" r="6" class="wf-pin"/><text x="672" y="225" class="wf-p"><?= t('wfSend') ?></text>
  <circle cx="660" cy="255" r="6" class="wf-pin"/><text x="672" y="260" class="wf-p">GND</text>

  <path class="wf-w" d="M296 185 H654" stroke="#3ecf8e"/>
  <path class="wf-w" d="M296 220 H654" stroke="#4aa8ff"/>
  <path class="wf-w" d="M296 255 H654" stroke="#9aa3b5"/>
  <circle cx="475" cy="185" r="12" fill="#14171f" stroke="#3ecf8e" stroke-width="2.5"/><text x="475" y="185" class="wf-tag" fill="#3ecf8e">1</text>
  <circle cx="475" cy="220" r="12" fill="#14171f" stroke="#4aa8ff" stroke-width="2.5"/><text x="475" y="220" class="wf-tag" fill="#4aa8ff">2</text>
  <circle cx="475" cy="255" r="12" fill="#14171f" stroke="#9aa3b5" stroke-width="2.5"/><text x="475" y="255" class="wf-tag" fill="#9aa3b5">3</text>
  <text x="475" y="305" text-anchor="middle" class="wf-s"><?= t('wfLeg1') ?></text>
  <text x="475" y="325" text-anchor="middle" class="wf-s"><?= t('wfLeg2') ?></text>

  <line x1="30" y1="360" x2="970" y2="360" stroke="#323a52" stroke-width="2" stroke-dasharray="8 6"/>

  <!-- ===================== B) Por el bus ===================== -->
  <text x="30" y="398" class="wf-t"><?= t('wfB') ?></text>
  <text x="30" y="420" class="wf-s"><?= t('wfBsub') ?></text>

  <rect x="40" y="450" width="190" height="170" rx="10" class="wf-esp"/>
  <text x="135" y="478" text-anchor="middle" class="wf-p"><?= t('wfEsp') ?></text>
  <circle cx="230" cy="505" r="6" class="wf-pin"/><text x="218" y="510" text-anchor="end" class="wf-p">IO16 (RX)</text>
  <circle cx="230" cy="540" r="6" class="wf-pin"/><text x="218" y="545" text-anchor="end" class="wf-p">IO17 (TX)</text>
  <circle cx="230" cy="575" r="6" class="wf-pin"/><text x="218" y="580" text-anchor="end" class="wf-p">3V3</text>
  <circle cx="230" cy="605" r="6" class="wf-pin"/><text x="218" y="610" text-anchor="end" class="wf-p">GND</text>

  <rect x="290" y="450" width="150" height="170" rx="10" class="wf-tja"/>
  <text x="365" y="478" text-anchor="middle" class="wf-p">TJA1020</text>
  <circle cx="290" cy="505" r="5" class="wf-pin"/><text x="300" y="510" class="wf-s" style="fill:#d6f5e6">TX</text>
  <circle cx="290" cy="540" r="5" class="wf-pin"/><text x="300" y="545" class="wf-s" style="fill:#d6f5e6">RX</text>
  <circle cx="290" cy="575" r="5" class="wf-pin"/><text x="300" y="580" class="wf-s" style="fill:#d6f5e6">SLP</text>
  <circle cx="290" cy="605" r="5" class="wf-pin"/><text x="300" y="610" class="wf-s" style="fill:#d6f5e6">GND</text>
  <circle cx="440" cy="505" r="5" class="wf-pin"/><text x="430" y="510" text-anchor="end" class="wf-s" style="fill:#d6f5e6">LIN</text>
  <circle cx="440" cy="560" r="5" class="wf-pin"/><text x="430" y="565" text-anchor="end" class="wf-s" style="fill:#d6f5e6">12V</text>
  <circle cx="440" cy="605" r="5" class="wf-pin"/><text x="430" y="610" text-anchor="end" class="wf-s" style="fill:#d6f5e6">GND</text>

  <path class="wf-w" d="M236 505 H285" stroke="#3ecf8e"/>
  <path class="wf-w" d="M236 540 H285" stroke="#4aa8ff"/>
  <path class="wf-w" d="M236 575 H285" stroke="#b57bff"/>
  <path class="wf-w" d="M236 605 H285" stroke="#9aa3b5"/>

  <rect x="660" y="450" width="150" height="170" rx="10" class="wf-tja"/>
  <text x="735" y="478" text-anchor="middle" class="wf-p"><?= t('wfTjaW') ?></text>
  <circle cx="660" cy="505" r="5" class="wf-pin"/><text x="670" y="510" class="wf-s" style="fill:#d6f5e6">LIN</text>
  <circle cx="660" cy="560" r="5" class="wf-pin"/><text x="670" y="565" class="wf-s" style="fill:#d6f5e6">12V</text>
  <circle cx="660" cy="605" r="5" class="wf-pin"/><text x="670" y="610" class="wf-s" style="fill:#d6f5e6">GND</text>
  <rect x="840" y="470" width="120" height="130" rx="10" class="wf-esp"/>
  <text x="900" y="530" text-anchor="middle" class="wf-p">WTTC</text>
  <text x="900" y="550" text-anchor="middle" class="wf-s"><?= t('wfAsVan') ?></text>
  <path class="wf-w" d="M810 520 H840 M810 560 H840" stroke="#5d6680"/>

  <!-- el W-Bus: los dos LIN unidos -->
  <path class="wf-w" d="M446 505 H654" stroke="#111318" style="stroke-width:9"/><path class="wf-w" d="M446 505 H654" stroke="#c9cfe0"/>
  <text x="550" y="492" text-anchor="middle" class="wf-p"><?= t('wfBus') ?></text>
  <!-- 12 V y masa comunes -->
  <path class="wf-w" d="M446 560 H654" stroke="#e04848"/>
  <path class="wf-w" d="M446 605 H654" stroke="#9aa3b5"/>
  <rect x="490" y="640" width="120" height="58" rx="8" fill="#3a2a2a" stroke="#e04848" stroke-width="2"/>
  <text x="550" y="665" text-anchor="middle" class="wf-p"><?= t('wfPsu') ?></text><text x="550" y="685" text-anchor="middle" class="wf-s"><?= t('wfPsuSub') ?></text>
  <path class="wf-w" d="M520 640 V560" stroke="#e04848"/><path class="wf-w" d="M580 640 V605" stroke="#9aa3b5"/>
  <circle cx="520" cy="560" r="5" fill="#e04848"/><circle cx="580" cy="605" r="5" fill="#9aa3b5"/>
  <text x="30" y="735" class="wf-s"><?= t('wfFoot') ?></text>
</svg></div>
