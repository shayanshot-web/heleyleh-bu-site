/* آمار بازدید هلیله: بدون کوکی و بدون آی‌پی؛ فقط شناسهٔ تصادفی در مرورگر. DNT و مرورگرهای خودکار شمرده نمی‌شوند. */
(function () {
  'use strict';
  try {
    var ls = window.localStorage;
    if (navigator.doNotTrack === '1' || navigator.webdriver || ls.getItem('hc_notrack') === '1') return;
    var rnd = function () { var a = new Uint8Array(8); (window.crypto || window.msCrypto).getRandomValues(a); return Array.prototype.map.call(a, function (b) { return ('0' + b.toString(16)).slice(-2); }).join(''); };
    var now = Date.now(), vid = ls.getItem('hc_v'), isNew = 0, sid = ls.getItem('hc_s'), seen = +ls.getItem('hc_t') || 0, entry = 0;
    if (!vid) { vid = rnd(); ls.setItem('hc_v', vid); isNew = 1; }
    if (!sid || now - seen > 30 * 60 * 1000) { sid = rnd(); ls.setItem('hc_s', sid); entry = 1; }
    var cur = null, shownAt = 0, last = '', url = '/t.php';
    function send(o) {
      var s = JSON.stringify(o);
      try { if (navigator.sendBeacon && navigator.sendBeacon(url, new Blob([s], { type: 'text/plain' }))) return; } catch (e) {}
      try { fetch(url, { method: 'POST', body: s, keepalive: true, headers: { 'Content-Type': 'text/plain' } }); } catch (e) {}
    }
    function flush() {
      if (!cur || !shownAt) return;
      var d = Math.round((Date.now() - shownAt) / 1000); shownAt = Date.now();
      if (d > 0) send({ a: 'd', h: cur, d: d });
      try { ls.setItem('hc_t', String(Date.now())); } catch (e) {}
    }
    function pv() {
      var p = location.pathname;
      if (p === last) return;
      flush(); last = p; cur = rnd(); shownAt = Date.now();
      var q = new URLSearchParams(location.search), o = { a: 'pv', h: cur, v: vid, s: sid, p: p, w: window.innerWidth || 0, l: navigator.language || '', z: '' };
      try { o.z = Intl.DateTimeFormat().resolvedOptions().timeZone || ''; } catch (e) {}
      if (entry) { o.e = 1; o.r = document.referrer || ''; entry = 0; if (isNew) o.n = 1; }
      var us = q.get('utm_source'); if (us) o.u = { s: us, m: q.get('utm_medium') || '', c: q.get('utm_campaign') || '' };
      send(o);
      try { ls.setItem('hc_t', String(Date.now())); } catch (e) {}
    }
    ['pushState', 'replaceState'].forEach(function (m) {
      var f = history[m]; history[m] = function () { var r = f.apply(this, arguments); setTimeout(pv, 50); return r; };
    });
    window.addEventListener('popstate', function () { setTimeout(pv, 50); });
    document.addEventListener('visibilitychange', function () { if (document.visibilityState === 'hidden') { flush(); shownAt = 0; } else { shownAt = Date.now(); } });
    window.addEventListener('pagehide', function () { flush(); shownAt = 0; });
    setInterval(function () { if (document.visibilityState === 'visible') flush(); }, 45000);
    pv();
  } catch (e) { /* آمار نباید سایت را خراب کند */ }
})();
