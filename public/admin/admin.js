(function () {
  'use strict';
  var csrf = (document.querySelector('input[name=_csrf]') || {}).value || '';

  // تأیید حذف
  document.addEventListener('submit', function (e) {
    var m = e.target.getAttribute && e.target.getAttribute('data-confirm');
    if (m && !window.confirm(m)) e.preventDefault();
  });

  // حذف این مرورگر از آمار بازدید (همان origin سایت است)
  var nt = document.querySelector('[data-notrack]');
  if (nt) {
    try { nt.checked = localStorage.getItem('hc_notrack') === '1'; } catch (e) {}
    nt.addEventListener('change', function () { try { if (nt.checked) localStorage.setItem('hc_notrack', '1'); else localStorage.removeItem('hc_notrack'); } catch (e) {} });
  }

  var form = document.querySelector('[data-editor]');
  if (!form) return;

  // زبان‌ها
  var tabs = form.querySelectorAll('.tab'), panes = form.querySelectorAll('.pane');
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      tabs.forEach(function (x) { x.classList.toggle('on', x === t); });
      panes.forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-pane') === t.getAttribute('data-tab')); });
    });
  });

  // ویرایشگر
  var lastRange = null, lastRte = null;
  function rteOf(node) { while (node && !(node.getAttribute && node.hasAttribute('data-rte'))) node = node.parentNode; return node; }
  document.addEventListener('selectionchange', function () {
    var s = window.getSelection(); if (!s.rangeCount) return;
    var r = rteOf(s.anchorNode); if (r) { lastRange = s.getRangeAt(0).cloneRange(); lastRte = r; }
  });
  function focusRte(l) {
    var r = form.querySelector('[data-rte="' + l + '"]'); r.focus();
    if (lastRange && lastRte === r) { var s = window.getSelection(); s.removeAllRanges(); s.addRange(lastRange); }
    return r;
  }
  form.querySelectorAll('.toolbar').forEach(function (tb) {
    var l = tb.getAttribute('data-for');
    tb.addEventListener('mousedown', function (e) { e.preventDefault(); });
    tb.addEventListener('click', function (e) {
      var b = e.target.closest('button'); if (!b) return;
      focusRte(l);
      if (b.hasAttribute('data-cmd')) document.execCommand(b.getAttribute('data-cmd'), false, null);
      else if (b.hasAttribute('data-block')) document.execCommand('formatBlock', false, b.getAttribute('data-block'));
      else if (b.hasAttribute('data-link')) { var u = window.prompt('آدرس پیوند (https://…)'); if (u) document.execCommand('createLink', false, u); }
      else if (b.hasAttribute('data-media-insert')) openMedia(function (m) { focusRte(l); document.execCommand('insertImage', false, m.url); });
    });
  });
  // چسباندن به‌صورت متن ساده + حفظ پاراگراف
  form.querySelectorAll('.rte').forEach(function (r) {
    r.addEventListener('paste', function (e) {
      e.preventDefault();
      var t = (e.clipboardData || window.clipboardData).getData('text/plain');
      var html = t.split(/\n{1,}/).map(function (p) { return p.trim() ? '<p>' + p.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</p>' : ''; }).join('');
      document.execCommand('insertHTML', false, html);
    });
  });
  form.addEventListener('submit', function () {
    form.querySelectorAll('[data-rte]').forEach(function (r) {
      form.querySelector('[data-body="' + r.getAttribute('data-rte') + '"]').value = r.innerHTML;
    });
  });

  // انتخاب تصویر شاخص
  var imgInput = form.querySelector('[data-image-input]'), imgPrev = form.querySelector('[data-image-prev]');
  var pick = form.querySelector('[data-media-pick]');
  if (pick) pick.addEventListener('click', function () {
    openMedia(function (m) { imgInput.value = m.id; imgPrev.innerHTML = ''; var i = document.createElement('img'); i.src = m.thumb; i.alt = ''; imgPrev.appendChild(i); });
  });
  var clr = form.querySelector('[data-image-clear]');
  if (clr) clr.addEventListener('click', function () { imgInput.value = ''; imgPrev.innerHTML = '<span class="muted">انتخاب نشده</span>'; });

  // پنجره رسانه
  function openMedia(cb) {
    var wrap = document.createElement('div'); wrap.className = 'modal';
    wrap.innerHTML = '<div class="modal-box"><div class="modal-head"><b>کتابخانه رسانه</b><span><input type="file" accept="image/*" multiple data-up> </span><button type="button" class="btn small" data-close>بستن</button></div><p class="muted small" data-msg></p><div class="modal-grid"></div></div>';
    document.body.appendChild(wrap);
    var grid = wrap.querySelector('.modal-grid'), msg = wrap.querySelector('[data-msg]');
    function close() { document.body.removeChild(wrap); }
    wrap.addEventListener('click', function (e) { if (e.target === wrap || e.target.hasAttribute('data-close')) close(); });
    function add(m, first) {
      var b = document.createElement('button'); b.type = 'button';
      var i = document.createElement('img'); i.src = m.thumb; i.alt = m.alt || ''; b.appendChild(i);
      b.addEventListener('click', function () { close(); cb(m); });
      first ? grid.insertBefore(b, grid.firstChild) : grid.appendChild(b);
    }
    fetch('/admin/?a=media_json', { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (list) {
      if (!list.length) msg.textContent = 'تصویری نیست؛ از بالا آپلود کنید.'; list.forEach(function (m) { add(m); });
    });
    wrap.querySelector('[data-up]').addEventListener('change', function (ev) {
      var fd = new FormData(); fd.append('_csrf', csrf);
      Array.prototype.forEach.call(ev.target.files, function (f) { fd.append('files[]', f); });
      msg.textContent = 'در حال آپلود…';
      fetch('/admin/?a=media_upload&ajax=1', { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (d) {
        msg.textContent = (d.errors || []).join(' | '); (d.files || []).reverse().forEach(function (m) { add(m, true); });
      }).catch(function () { msg.textContent = 'آپلود ناموفق بود.'; });
    });
  }
})();
