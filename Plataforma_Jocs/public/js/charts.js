/**
 * Gràfics SVG sense dependències externes (barres, línia i donut).
 * Fan servir les variables CSS del tema (--accent, --text, --border...).
 * Ús: <div class="grafic" data-tipus="barres|linia|donut" data-dades='[{"etiqueta":"A","valor":3}]'></div>
 */
(function () {
  var NS = 'http://www.w3.org/2000/svg';
  var COLORS = ['#66c0f4', '#fa5c5c', '#f0b429', '#4cc38a', '#b388ff', '#ff8a65', '#26c6da', '#9ccc65'];

  function el(nom, attrs, text) {
    var e = document.createElementNS(NS, nom);
    Object.keys(attrs || {}).forEach(function (k) { e.setAttribute(k, attrs[k]); });
    if (text !== undefined) e.textContent = text;
    return e;
  }

  function svg(w, h) {
    var s = el('svg', { viewBox: '0 0 ' + w + ' ' + h, role: 'img', 'class': 'svg-grafic' });
    return s;
  }

  function buit(cont) {
    cont.innerHTML = '<p class="text-muted grafic-buit">Encara no hi ha dades per mostrar.</p>';
  }

  function retalla(t, n) { t = String(t); return t.length > n ? t.slice(0, n - 1) + '…' : t; }

  function barres(cont, dades) {
    var W = 520, H = 240, mL = 40, mB = 48, mT = 12, mR = 10;
    var s = svg(W, H);
    var max = Math.max.apply(null, dades.map(function (d) { return d.valor; })) || 1;
    var w = (W - mL - mR) / dades.length;
    for (var i = 0; i <= 4; i++) {
      var y = mT + (H - mT - mB) * (1 - i / 4);
      s.appendChild(el('line', { x1: mL, x2: W - mR, y1: y, y2: y, stroke: 'var(--border)', 'stroke-width': 1 }));
      s.appendChild(el('text', { x: mL - 6, y: y + 4, 'text-anchor': 'end', 'class': 'svg-eix' }, Math.round(max * i / 4)));
    }
    dades.forEach(function (d, i) {
      var h = (H - mT - mB) * (d.valor / max);
      var x = mL + i * w + w * 0.15;
      var r = el('rect', { x: x, y: H - mB - h, width: w * 0.7, height: Math.max(h, 1), rx: 3, fill: COLORS[i % COLORS.length] });
      r.appendChild(el('title', {}, d.etiqueta + ': ' + d.valor));
      s.appendChild(r);
      s.appendChild(el('text', { x: x + w * 0.35, y: H - mB - h - 5, 'text-anchor': 'middle', 'class': 'svg-valor' }, d.valor));
      s.appendChild(el('text', { x: x + w * 0.35, y: H - mB + 16, 'text-anchor': 'middle', 'class': 'svg-eix' }, retalla(d.etiqueta, 12)));
    });
    cont.appendChild(s);
  }

  function linia(cont, dades) {
    var W = 520, H = 240, mL = 44, mB = 36, mT = 14, mR = 14;
    var s = svg(W, H);
    var max = Math.max.apply(null, dades.map(function (d) { return d.valor; })) || 1;
    for (var i = 0; i <= 4; i++) {
      var y = mT + (H - mT - mB) * (1 - i / 4);
      s.appendChild(el('line', { x1: mL, x2: W - mR, y1: y, y2: y, stroke: 'var(--border)', 'stroke-width': 1 }));
      s.appendChild(el('text', { x: mL - 6, y: y + 4, 'text-anchor': 'end', 'class': 'svg-eix' }, Math.round(max * i / 4)));
    }
    var n = dades.length;
    var punts = dades.map(function (d, i) {
      return [mL + (n === 1 ? (W - mL - mR) / 2 : (W - mL - mR) * i / (n - 1)), mT + (H - mT - mB) * (1 - d.valor / max)];
    });
    var path = punts.map(function (p, i) { return (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join(' ');
    var area = path + ' L' + punts[n - 1][0] + ' ' + (H - mB) + ' L' + punts[0][0] + ' ' + (H - mB) + ' Z';
    s.appendChild(el('path', { d: area, fill: 'var(--accent)', opacity: 0.15 }));
    s.appendChild(el('path', { d: path, fill: 'none', stroke: 'var(--accent)', 'stroke-width': 2.5, 'stroke-linejoin': 'round' }));
    punts.forEach(function (p, i) {
      var c = el('circle', { cx: p[0], cy: p[1], r: 4, fill: 'var(--accent)' });
      c.appendChild(el('title', {}, dades[i].etiqueta + ': ' + dades[i].valor + ' punts'));
      s.appendChild(c);
    });
    [0, n - 1].forEach(function (i, k) {
      if (k === 1 && n === 1) return;
      s.appendChild(el('text', { x: punts[i][0], y: H - mB + 18, 'text-anchor': k ? 'end' : 'start', 'class': 'svg-eix' }, dades[i].etiqueta));
    });
    cont.appendChild(s);
  }

  function donut(cont, dades) {
    var total = dades.reduce(function (a, d) { return a + d.valor; }, 0);
    if (!total) { return buit(cont); }
    var S = 180, c = S / 2, r = 70, sw = 28, circ = 2 * Math.PI * r, off = 0;
    var s = svg(S, S);
    dades.forEach(function (d, i) {
      var len = circ * d.valor / total;
      var a = el('circle', {
        cx: c, cy: c, r: r, fill: 'none', stroke: d.color || COLORS[i % COLORS.length], 'stroke-width': sw,
        'stroke-dasharray': len + ' ' + (circ - len), 'stroke-dashoffset': -off, transform: 'rotate(-90 ' + c + ' ' + c + ')'
      });
      a.appendChild(el('title', {}, d.etiqueta + ': ' + d.valor));
      s.appendChild(a);
      off += len;
    });
    s.appendChild(el('text', { x: c, y: c + 6, 'text-anchor': 'middle', 'class': 'svg-total' }, total));
    var wrap = document.createElement('div');
    wrap.className = 'donut-wrap';
    wrap.appendChild(s);
    var leg = document.createElement('ul');
    leg.className = 'llegenda';
    dades.forEach(function (d, i) {
      var li = document.createElement('li');
      li.innerHTML = '<span class="punt" style="background:' + (d.color || COLORS[i % COLORS.length]) + '"></span>';
      li.appendChild(document.createTextNode(d.etiqueta + ': ' + d.valor));
      leg.appendChild(li);
    });
    wrap.appendChild(leg);
    cont.appendChild(wrap);
  }

  window.renderGrafics = function () {
    document.querySelectorAll('.grafic').forEach(function (cont) {
      var dades = [];
      try { dades = JSON.parse(cont.getAttribute('data-dades') || '[]'); } catch (e) { }
      cont.textContent = '';
      if (!dades.length) { return buit(cont); }
      var t = cont.getAttribute('data-tipus');
      (t === 'linia' ? linia : t === 'donut' ? donut : barres)(cont, dades);
    });
  };

  window.renderGrafics();
})();
