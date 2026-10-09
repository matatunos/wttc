/*
  informe.js — Informe en PDF de las pruebas del simulador de WTTC (https://wttc.favala.es).
  Código generado íntegramente con Claude (Anthropic).

  WTTCInforme.build(jsPDF, runs, lang, fw) devuelve un documento jsPDF con:
    - si hay varias pruebas, una tabla que las compara;
    - por cada prueba: parámetros, resumen, gráficas (temperaturas con los encendidos sombreados, potencia, gasoil
      acumulado y batería), tabla de encendidos, registro de la placa y avisos de Telegram.
  Cada prueba (run) es { p: {amb, cab0, tgt, min, batt, manual}, s: [muestras], ev: [{t, m}], tg: [{t, m}] }; cada
  muestra { t (min), amb, cab (real), cabR (lo que lee el termómetro, o null), water, on, ph, pw, volt, gas (l), tgt }.
  Las gráficas son vectoriales (líneas de jsPDF), sin imágenes. Funciona en el navegador y en Node.
*/
(function (root) {
  const L = {
    es: { title: 'WTTC · Informe de prueba', titleN: 'WTTC · Informe de pruebas', sim: 'Simulador de wttc.favala.es · firmware {0}',
      warn: 'Simulación aproximada: el modelo de calor del simulador no es una medida real. Una furgo real dependerá del aislamiento, el ventilador, el sol y el viento.',
      date: 'Generado el {0}', compare: 'Comparación de las pruebas', test: 'Prueba {0}', manual: 'Prueba grabada a mano',
      params: 'Parámetros', amb: 'Fuera', cab0: 'Dentro al empezar', tgt: 'Objetivo', noTgt: 'sin termostato', dur: 'Duración', batt: 'Batería en reposo',
      summary: 'Resumen', starts: 'Encendidos', heating: 'Calentando', reach: 'Llega al objetivo', never: 'no llega', notApply: '—',
      band: 'Después, dentro entre', gas: 'Gasoil (estimado)', gasH: 'Gasoil por hora', vmin: 'Batería mínima', wmax: 'Agua, máxima',
      cabMax: 'Dentro, máxima', after: 'a los {0} min', of: '{0} de {1} min ({2} %)',
      chT: 'Temperaturas (°C)', chP: 'Potencia de la Webasto (W)', chG: 'Gasoil acumulado (l) y batería (V)',
      lgAmb: 'fuera', lgCab: 'dentro', lgWater: 'agua del motor', lgTgt: 'objetivo', lgOn: 'calentando', lgHyst: 'banda de histéresis', lgGas: 'gasoil', lgVolt: 'batería',
      min: 'min', cycles: 'Encendidos', cN: 'Nº', cFrom: 'Desde', cTo: 'Hasta', cDur: 'Duración', cWater: 'Agua al empezar', cCab: 'Dentro', cGas: 'Gasoil',
      log: 'Registro de la placa', tg: 'Avisos de Telegram', none: 'Ninguno.', page: 'Página {0} de {1}' },
    en: { title: 'WTTC · Test report', titleN: 'WTTC · Test report', sim: 'Simulator at wttc.favala.es · firmware {0}',
      warn: 'Approximate simulation: the simulator\'s heat model is not a real measurement. A real van depends on insulation, fan, sun and wind.',
      date: 'Generated on {0}', compare: 'Comparison of the tests', test: 'Test {0}', manual: 'Manually recorded test',
      params: 'Parameters', amb: 'Outside', cab0: 'Inside at start', tgt: 'Target', noTgt: 'no thermostat', dur: 'Duration', batt: 'Resting battery',
      summary: 'Summary', starts: 'Starts', heating: 'Heating', reach: 'Reaches the target', never: 'never', notApply: '—',
      band: 'Afterwards, inside between', gas: 'Diesel (estimate)', gasH: 'Diesel per hour', vmin: 'Lowest battery', wmax: 'Coolant, highest',
      cabMax: 'Inside, highest', after: 'after {0} min', of: '{0} of {1} min ({2} %)',
      chT: 'Temperatures (°C)', chP: 'Webasto power (W)', chG: 'Accumulated diesel (l) and battery (V)',
      lgAmb: 'outside', lgCab: 'inside', lgWater: 'coolant', lgTgt: 'target', lgOn: 'heating', lgHyst: 'hysteresis band', lgGas: 'diesel', lgVolt: 'battery',
      min: 'min', cycles: 'Starts', cN: 'No.', cFrom: 'From', cTo: 'To', cDur: 'Duration', cWater: 'Coolant at start', cCab: 'Inside', cGas: 'Diesel',
      log: 'Board log', tg: 'Telegram notifications', none: 'None.', page: 'Page {0} of {1}' },
    de: { title: 'WTTC · Testbericht', titleN: 'WTTC · Testbericht', sim: 'Simulator auf wttc.favala.es · Firmware {0}',
      warn: 'Ungefähre Simulation: das Wärmemodell des Simulators ist keine echte Messung. Ein echter Bus hängt von Dämmung, Gebläse, Sonne und Wind ab.',
      date: 'Erstellt am {0}', compare: 'Vergleich der Tests', test: 'Test {0}', manual: 'Von Hand aufgezeichneter Test',
      params: 'Parameter', amb: 'Außen', cab0: 'Innen zu Beginn', tgt: 'Ziel', noTgt: 'ohne Thermostat', dur: 'Dauer', batt: 'Batterie in Ruhe',
      summary: 'Zusammenfassung', starts: 'Starts', heating: 'Heizt', reach: 'Erreicht das Ziel', never: 'nie', notApply: '—',
      band: 'Danach innen zwischen', gas: 'Diesel (geschätzt)', gasH: 'Diesel pro Stunde', vmin: 'Niedrigste Batterie', wmax: 'Kühlwasser, höchstens',
      cabMax: 'Innen, höchstens', after: 'nach {0} min', of: '{0} von {1} min ({2} %)',
      chT: 'Temperaturen (°C)', chP: 'Leistung der Webasto (W)', chG: 'Diesel kumuliert (l) und Batterie (V)',
      lgAmb: 'außen', lgCab: 'innen', lgWater: 'Kühlwasser', lgTgt: 'Ziel', lgOn: 'heizt', lgHyst: 'Hysterese-Band', lgGas: 'Diesel', lgVolt: 'Batterie',
      min: 'min', cycles: 'Starts', cN: 'Nr.', cFrom: 'Von', cTo: 'Bis', cDur: 'Dauer', cWater: 'Kühlwasser beim Start', cCab: 'Innen', cGas: 'Diesel',
      log: 'Protokoll der Platine', tg: 'Telegram-Benachrichtigungen', none: 'Keine.', page: 'Seite {0} von {1}' },
  };
  const F = (s, ...a) => String(s).replace(/\{(\d)\}/g, (m, i) => a[i]);
  // Solo caracteres de la codificación de las letras estándar del PDF (Latin-1): lo demás se cambia
  const safe = s => String(s).replace(/≈/g, '~').replace(/[−–—]/g, '-').replace(/→/g, '->').replace(/…/g, '...')
    .replace(/[“”„]/g, '"').replace(/[‘’]/g, "'").replace(/[^\x00-\xff]/g, '?');
  const C = { ink: [22, 35, 58], mut: [110, 120, 140], grid: [225, 229, 236], amb: [140, 148, 160], cab: [42, 139, 192],
    water: [224, 134, 0], tgt: [214, 69, 69], on: [255, 226, 186], hyst: [214, 236, 246], pw: [224, 134, 0], gas: [120, 80, 30], volt: [31, 169, 113] };

  // Resumen de una prueba a partir de sus muestras
  function analyze(r) {
    const s = r.s, p = r.p, cyc = [];
    let cur = null;
    for (let i = 0; i < s.length; i++) {
      const x = s[i];
      if (x.on && !cur) cur = { on: x.t, water0: x.water, cab0: x.cabR ?? x.cab, gas0: x.gas };
      if (!x.on && cur) { Object.assign(cur, { off: x.t, cab1: x.cabR ?? x.cab, gas: x.gas - cur.gas0 }); cyc.push(cur); cur = null; }
    }
    const last = s[s.length - 1] || { t: 0, gas: 0 };
    if (cur) { Object.assign(cur, { off: last.t, cab1: last.cabR ?? last.cab, gas: last.gas - cur.gas0, open: true }); cyc.push(cur); }
    const heat = cyc.reduce((a, c) => a + c.off - c.on, 0);
    let reach = null, lo = Infinity, hi = -Infinity;
    if (p.tgt) for (const x of s) {
      const c = x.cabR; if (c == null) continue;
      if (reach == null && c >= p.tgt - 0.1) reach = x.t;   // el termostato apaga justo al llegar: una décima de margen
      if (reach != null && x.tgt) { lo = Math.min(lo, c); hi = Math.max(hi, c); }
    }
    const val = k => s.map(x => x[k]).filter(v => v != null && isFinite(v));
    return { cyc, heat, reach, lo, hi, dur: last.t, gas: last.gas, vmin: Math.min(...val('volt').filter(v => v > 0)), wmax: Math.max(...val('water')),
      cmax: Math.max(...val('cab')) };
  }

  function build(jsPDF, runs, lang, fw) {
    const T = L[lang] || L.es, n = v => (+v).toLocaleString({ es: 'es-ES', en: 'en-GB', de: 'de-DE' }[lang] || 'es-ES', { maximumFractionDigits: 1 });
    const n2 = v => (+v).toLocaleString({ es: 'es-ES', en: 'en-GB', de: 'de-DE' }[lang] || 'es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const deg = v => n(v) + ' °C', hm = m => { m = Math.round(m); return m < 60 ? m + ' min' : Math.floor(m / 60) + ' h' + (m % 60 ? ' ' + String(m % 60).padStart(2, '0') : ''); };
    const doc = new jsPDF({ unit: 'mm', format: 'a4', compress: true }), W = 210, M = 16;
    let y = 0;
    const font = (sz, bold, col) => { doc.setFont('helvetica', bold ? 'bold' : 'normal'); doc.setFontSize(sz); doc.setTextColor(...(col || C.ink)); };
    const text = (t, x, yy, o) => doc.text(safe(t), x, yy, o);
    const need = h => { if (y + h > 282) { doc.addPage(); y = 18; } };
    const h2 = t => { need(14); font(13, true); text(t, M, y); y += 2; doc.setDrawColor(...C.grid); doc.setLineWidth(0.4); doc.line(M, y, W - M, y); y += 6; };
    // Tabla sencilla: cabeceras, filas y anchos (mm)
    function table(head, rows, widths, opt = {}) {
      const rh = opt.rh || 5.6;
      need(rh * 2);
      font(8.5, true, C.mut);
      // Cabeceras largas en varias líneas, para que no se pisen
      const hl = head.map((h, i) => doc.splitTextToSize(safe(h), widths[i] - 2)), nl = Math.max(...hl.map(l => l.length));
      let x = M; hl.forEach((l, i) => { doc.text(l, x + 1, y); x += widths[i]; }); y += 2 + (nl - 1) * 3.6;
      doc.setDrawColor(...C.grid); doc.line(M, y, M + widths.reduce((a, b) => a + b, 0), y); y += rh - 1.4;
      for (const r of rows) {
        need(rh); font(8.5, false);
        x = M; r.forEach((c, i) => { text(c, x + 1, y); x += widths[i]; }); y += rh;
      }
      y += 2;
    }
    // Pares etiqueta/valor en dos columnas
    function kv(pairs) {
      const colW = (W - 2 * M) / 2;
      for (let i = 0; i < pairs.length; i += 2) {
        need(6);
        for (let j = 0; j < 2 && i + j < pairs.length; j++) {
          const [k, v] = pairs[i + j], x = M + j * colW;
          font(9, false, C.mut); text(k, x, y); font(9, true); text(v, x + 46, y);
        }
        y += 5.5;
      }
      y += 2;
    }
    // Gráfica: eje x en minutos; series [{pts, col, dash, w, ax}] (ax 1 = eje derecho); bandas de fondo y líneas horizontales
    function chart(o) {
      const h = o.h || 58, x0 = M + 10, x1 = W - M - (o.right ? 12 : 2), y0 = y + 4, y1 = y0 + h;
      need(h + 18);
      font(10, true); text(o.title, M, y); y += 2;
      const X = t => x0 + (x1 - x0) * t / Math.max(1, o.xmax), Y = (v, ax) => { const a = ax ? o.right : o; return y1 - (y1 - y0) * (v - a.ymin) / (a.ymax - a.ymin); };
      for (const b of o.bands || []) { doc.setFillColor(...b.col); doc.rect(X(b.t0), y0, Math.max(0.2, X(b.t1) - X(b.t0)), y1 - y0, 'F'); }
      for (const b of o.ybands || []) { doc.setFillColor(...b.col); doc.rect(x0, Y(b.v1), x1 - x0, Y(b.v0) - Y(b.v1), 'F'); }
      doc.setLineWidth(0.15); doc.setDrawColor(...C.grid); font(7, false, C.mut);
      for (const v of o.yt) { doc.line(x0, Y(v), x1, Y(v)); text(String(v), x0 - 1.5, Y(v) + 1, { align: 'right' }); }
      if (o.right) for (const v of o.right.yt) text(String(o.right.fmt ? o.right.fmt(v) : v), x1 + 1.5, Y(v, 1) + 1);
      const step = o.xmax > 180 ? 30 : o.xmax > 90 ? 15 : 10;
      for (let t = 0; t <= o.xmax + 0.01; t += step) { doc.line(X(t), y0, X(t), y1); text(hm(t).replace(' min', '′').replace('0′', '0'), X(t), y1 + 3.5, { align: 'center' }); }
      for (const l of o.hl || []) { doc.setDrawColor(...l.col); doc.setLineWidth(0.4); doc.setLineDashPattern([1.5, 1], 0); doc.line(x0, Y(l.v), x1, Y(l.v)); doc.setLineDashPattern([], 0); }
      for (const s of o.series) {
        doc.setDrawColor(...s.col); doc.setLineWidth(s.w || 0.5); doc.setLineDashPattern(s.dash || [], 0);
        let prev = null;
        for (const [t, v] of s.pts) {
          if (v == null || !isFinite(v)) { prev = null; continue; }
          const p = [X(t), Math.min(y1, Math.max(y0, Y(v, s.ax)))];
          if (prev) doc.line(prev[0], prev[1], p[0], p[1]);
          prev = p;
        }
        doc.setLineDashPattern([], 0);
      }
      doc.setDrawColor(...C.mut); doc.setLineWidth(0.2); doc.rect(x0, y0, x1 - x0, y1 - y0);
      // Leyenda
      let lx = x0; const ly = y1 + 8; font(7.5, false, C.ink);
      for (const g of o.legend) {
        if (g.box) { doc.setFillColor(...g.col); doc.rect(lx, ly - 2.3, 5, 3, 'F'); }
        else { doc.setDrawColor(...g.col); doc.setLineWidth(0.6); doc.setLineDashPattern(g.dash || [], 0); doc.line(lx, ly - 0.9, lx + 5, ly - 0.9); doc.setLineDashPattern([], 0); }
        text(g.label, lx + 6.5, ly); lx += 8.5 + doc.getTextWidth(safe(g.label)) + 3;
      }
      y = ly + 6;
    }

    const A = runs.map(analyze);
    // Portada
    y = 20;
    font(18, true); text(runs.length > 1 ? T.titleN : T.title, M, y); y += 7;
    font(9.5, false, C.mut); text(F(T.sim, fw) + ' · ' + F(T.date, new Date().toLocaleString({ es: 'es-ES', en: 'en-GB', de: 'de-DE' }[lang] || 'es-ES')), M, y); y += 6;
    doc.setFillColor(255, 244, 222); const wl = doc.splitTextToSize(safe(T.warn), W - 2 * M - 6); doc.rect(M, y - 4, W - 2 * M, wl.length * 4.2 + 3.5, 'F');
    font(8.5, false, [120, 70, 0]); doc.text(wl, M + 3, y + 0.5); y += wl.length * 4.2 + 6;
    const title = (r, i) => r.p.manual ? T.manual : F(T.test, i + 1) + ': ' + T.amb.toLowerCase() + ' ' + deg(r.p.amb) + ' · ' + (r.p.tgt ? T.tgt.toLowerCase() + ' ' + deg(r.p.tgt) : T.noTgt);
    if (runs.length > 1) {
      h2(T.compare);
      table([T.cN, T.amb, T.cab0, T.tgt, T.starts, T.reach, T.band, T.heating, T.gas],
        runs.map((r, i) => { const a = A[i]; return [String(i + 1), deg(r.p.amb), deg(r.p.cab0), r.p.tgt ? deg(r.p.tgt) : T.noTgt, String(a.cyc.length),
          !r.p.tgt ? T.notApply : a.reach == null ? T.never : hm(a.reach), isFinite(a.lo) ? n(a.lo) + '–' + n(a.hi) + ' °C' : T.notApply, hm(a.heat), n2(a.gas) + ' l']; }),
        [8, 15, 19, 18, 21, 21, 30, 21, 21]);
    }
    runs.forEach((r, i) => {
      const a = A[i], p = r.p;
      if (runs.length > 1 || i) { doc.addPage(); y = 18; }
      if (runs.length > 1) { font(14, true); text(title(r, i), M, y); y += 8; }
      h2(T.params);
      kv([[T.amb, deg(p.amb)], [T.cab0, deg(p.cab0)], [T.tgt, p.tgt ? deg(p.tgt) : T.noTgt], [T.dur, hm(a.dur)], [T.batt, n(p.batt) + ' V']]);
      h2(T.summary);
      kv([[T.starts, String(a.cyc.length)], [T.heating, F(T.of, Math.round(a.heat), Math.round(a.dur), Math.round(100 * a.heat / Math.max(1, a.dur)))],
        [T.reach, !p.tgt ? T.notApply : a.reach == null ? T.never : F(T.after, Math.round(a.reach))], [T.band, isFinite(a.lo) ? n(a.lo) + ' – ' + n(a.hi) + ' °C' : T.notApply],
        [T.gas, n2(a.gas) + ' l'], [T.gasH, n2(a.gas / Math.max(1 / 60, a.dur / 60)) + ' l/h'], [T.vmin, isFinite(a.vmin) ? n(a.vmin) + ' V' : T.notApply],
        [T.wmax, deg(Math.round(a.wmax))], [T.cabMax, deg(a.cmax)]]);
      const s = r.s, bands = a.cyc.map(c => ({ t0: c.on, t1: c.off, col: C.on }));
      const temps = s.flatMap(x => [x.amb, x.cab, x.water]).filter(isFinite);
      const tmin = Math.floor(Math.min(0, ...temps) / 10) * 10, tmax = Math.ceil(Math.max(30, ...temps) / 10) * 10;
      const yt = []; for (let v = tmin; v <= tmax; v += 10) yt.push(v);
      chart({ title: T.chT, xmax: a.dur, ymin: tmin, ymax: tmax, yt, bands, h: 70,
        ybands: p.tgt ? [{ v0: p.tgt - 1.5, v1: p.tgt, col: C.hyst }] : [], hl: p.tgt ? [{ v: p.tgt, col: C.tgt }] : [],
        series: [{ pts: s.map(x => [x.t, x.amb]), col: C.amb, dash: [1.2, 1] }, { pts: s.map(x => [x.t, x.water]), col: C.water },
          { pts: s.map(x => [x.t, x.cabR ?? x.cab]), col: C.cab, w: 0.7 }],
        legend: [{ col: C.cab, label: T.lgCab }, { col: C.water, label: T.lgWater }, { col: C.amb, label: T.lgAmb, dash: [1.2, 1] }]
          .concat(p.tgt ? [{ col: C.tgt, label: T.lgTgt, dash: [1.5, 1] }, { col: C.hyst, label: T.lgHyst, box: true }] : []).concat([{ col: C.on, label: T.lgOn, box: true }]) });
      chart({ title: T.chP, xmax: a.dur, ymin: 0, ymax: 6000, yt: [0, 2000, 4000, 6000], bands, h: 32,
        series: [{ pts: s.map(x => [x.t, x.pw]), col: C.pw }], legend: [{ col: C.on, label: T.lgOn, box: true }] });
      const gmax = Math.max(0.1, Math.ceil(a.gas * 10 + 0.5) / 10), gyt = [0, gmax / 2, gmax].map(v => Math.round(v * 100) / 100);
      chart({ title: T.chG, xmax: a.dur, ymin: 0, ymax: gmax, yt: gyt, bands, h: 32, right: { ymin: 11, ymax: 13.5, yt: [11, 12, 13], fmt: v => v + ' V' },
        series: [{ pts: s.map(x => [x.t, x.gas]), col: C.gas, w: 0.7 }, { pts: s.map(x => [x.t, x.volt]), col: C.volt, ax: 1 }],
        legend: [{ col: C.gas, label: T.lgGas + ' (l)' }, { col: C.volt, label: T.lgVolt + ' (V)' }] });
      h2(T.cycles);
      if (!a.cyc.length) { font(9, false, C.mut); text(T.none, M, y); y += 7; }
      else table([T.cN, T.cFrom, T.cTo, T.cDur, T.cWater, T.cCab, T.cGas],
        a.cyc.map((c, j) => [String(j + 1), hm(c.on), hm(c.off) + (c.open ? ' …' : ''), hm(c.off - c.on), deg(Math.round(c.water0)), deg(c.cab0) + ' -> ' + deg(c.cab1), n2(c.gas) + ' l']),
        [10, 22, 22, 22, 32, 44, 24]);
      h2(T.log);
      font(8, false);
      if (!r.ev.length) { font(9, false, C.mut); text(T.none, M, y); y += 7; }
      for (const e of r.ev) { const ls = doc.splitTextToSize(safe(hm(e.t).padStart(7) + '   ' + e.m), W - 2 * M); need(ls.length * 3.8); doc.text(ls, M, y); y += ls.length * 3.8; }
      y += 3;
      h2(T.tg);
      font(8, false);
      if (!r.tg.length) { font(9, false, C.mut); text(T.none, M, y); y += 7; }
      for (const e of r.tg) { const ls = doc.splitTextToSize(safe(hm(e.t).padStart(7) + '   ' + e.m), W - 2 * M); need(ls.length * 3.8); doc.text(ls, M, y); y += ls.length * 3.8; }
    });
    // Pie con el número de página
    const np = doc.getNumberOfPages();
    for (let i = 1; i <= np; i++) { doc.setPage(i); font(7.5, false, C.mut); text('wttc.favala.es · ' + F(T.page, i, np), W / 2, 290, { align: 'center' }); }
    return doc;
  }
  root.WTTCInforme = { build, analyze };
  if (typeof module !== 'undefined') module.exports = root.WTTCInforme;
})(typeof window !== 'undefined' ? window : globalThis);
