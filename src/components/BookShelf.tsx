'use client';

import { useCallback, useEffect, useRef, useState, type CSSProperties } from 'react';
import { bookUi, books, pick, publisher, type Book, type BookUi } from '@/content/books';
import type { Lang } from '@/content/types';

/* ---------- ابزارهای کوچک ---------- */

// عطف‌ها از پالت دریایی خود سایت (سرمه‌ای، آبی، ماسه) و چند رنگ گرم چوب‌وار
const PALETTE = ['#0a3e66', '#0a6fb5', '#1b6b6f', '#7a2e2e', '#c9962b', '#5b6b3a', '#d9c7a6', '#3b2f5c'];
const HEIGHTS = [172, 156, 184, 164, 178, 150, 190, 160];
const WIDTHS = [46, 38, 52, 42, 48, 36, 54, 40];
const PER_SHELF = 6;
/** +989171731776 را به شکل خوانا و محلی 0917 173 1776 نشان می‌دهد */
const phoneShown = (ph: string) => {
  const d = ph.replace(/\D/g, '');
  return /^989\d{9}$/.test(d) ? `0${d.slice(2, 5)} ${d.slice(5, 8)} ${d.slice(8)}` : ph;
};
/** عرض عطف را از طول عنوان حساب می‌کنیم تا عنوان‌های بلند چند ستونه و کامل خوانا بمانند */
const spineW = (t: string) => {
  const long = t.length > 22;
  const perCol = long ? 13 : 10;
  const lineW = long ? 14 : 18;
  return Math.min(96, 30 + Math.ceil(t.length / perCol) * lineW);
};

const safeHttps = (u?: string) => !!u && /^https:\/\/[^\s"'<>]+$/i.test(u);
const safeFile = (f?: string) => !!f && /^[\w.-]+$/.test(f) && !f.startsWith('_');
const safeUpload = (f?: string) => !!f && /^\/uploads\/[\w./-]+$/.test(f) && !f.includes('..');
const coverSrc = (f?: string) => (safeUpload(f) ? f! : safeFile(f) ? `/books/${f}` : '');
const safeUser = (u?: string) => !!u && /^[\w.]+$/.test(u);
const digits = (s?: string) => (s || '').replace(/\D/g, '');
const reduced = () => typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function colorOf(b: Book, i: number): string {
  return b.color && /^#[0-9a-f]{6}$/i.test(b.color) ? b.color : PALETTE[i % PALETTE.length];
}
/** رنگ متن روی رنگ زمینه (تیره یا روشن) برای خوانایی عطف */
function inkOn(hex: string): string {
  const n = parseInt(hex.slice(1), 16);
  const r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
  return 0.299 * r + 0.587 * g + 0.114 * b > 150 ? '#1d1308' : '#fffaf0';
}

/* ---------- گل و گیاه (SVG) ---------- */

function PlantBig() {
  const leaves: [number, string][] = [[-58, '#3f7d4e'], [-30, '#4f9560'], [0, '#3a7048'], [30, '#58a068'], [58, '#438052']];
  return (
    <svg className="bs-plant bs-sway" viewBox="0 0 140 190" aria-hidden="true" focusable="false">
      {leaves.map(([r, c]) => (
        <g key={r} transform={`rotate(${r} 70 132)`}>
          <path d="M70 132 C36 110 30 62 70 16 C110 62 104 110 70 132Z" fill={c} />
          <path d="M70 128 L70 30" stroke="rgba(255,255,255,.28)" strokeWidth="2" fill="none" />
        </g>
      ))}
      <path d="M38 128 h64 l-8 56 h-48z" fill="#b5603a" />
      <rect x="33" y="120" width="74" height="14" rx="4" fill="#c8704a" />
      <ellipse cx="70" cy="122" rx="32" ry="4" fill="#3a2a1c" />
    </svg>
  );
}

function PlantSmall() {
  return (
    <svg className="bs-plant bs-plant-sm" viewBox="0 0 70 72" aria-hidden="true" focusable="false">
      {[-70, -42, -14, 14, 42, 70].map((r, i) => (
        <ellipse key={r} cx="35" cy="30" rx="7" ry="17" transform={`rotate(${r} 35 46)`} fill={i % 2 ? '#6aa66f' : '#4f9560'} />
      ))}
      <path d="M20 46 h30 l-4 24 h-22z" fill="#b5603a" />
      <rect x="17" y="42" width="36" height="8" rx="3" fill="#c8704a" />
    </svg>
  );
}

function Ivy() {
  const leaves: [number, number, number][] = [[30, 30, -150], [44, 60, 30], [27, 92, -160], [42, 124, 20], [30, 156, -150], [41, 188, 25], [34, 218, -140]];
  return (
    <svg className="bs-ivy bs-sway" viewBox="0 0 70 240" aria-hidden="true" focusable="false">
      <path d="M35 0 C20 40 52 72 34 112 S22 192 38 238" stroke="#35623f" strokeWidth="2.6" fill="none" strokeLinecap="round" />
      {leaves.map(([x, y, r], i) => (
        <path key={i} transform={`translate(${x} ${y}) rotate(${r})`} d="M0 0 C8 -9 22 -9 28 0 C22 9 8 9 0 0Z" fill={i % 2 ? '#4f9560' : '#3f7d4e'} />
      ))}
    </svg>
  );
}

function Lamp() {
  return (
    <svg className="bs-lamp" viewBox="0 0 120 150" aria-hidden="true" focusable="false">
      <path d="M60 140 L60 70 L30 38" stroke="#5b3a24" strokeWidth="5" fill="none" strokeLinecap="round" />
      <ellipse cx="60" cy="142" rx="26" ry="6" fill="#4a2f1c" />
      <path d="M8 44 L36 16 L62 46 Z" fill="#c9962b" />
      <path d="M8 44 L62 46" stroke="#8d6a1c" strokeWidth="3" />
    </svg>
  );
}

/* ---------- جلد گرافیکی یا عکس جلد ---------- */

function Cover({ book, color, lang }: { book: Book; color: string; lang: Lang }) {
  const src = coverSrc(book.cover);
  if (src) {
    // eslint-disable-next-line @next/next/no-img-element
    return <img className="cv-img" src={src} alt={pick(book.title, lang)} loading="lazy" />;
  }
  return (
    <span className="cv" style={{ '--c': color, '--bk-ink': inkOn(color) } as CSSProperties}>
      <svg className="cv-wave" viewBox="0 0 200 40" aria-hidden="true" focusable="false" preserveAspectRatio="none">
        <path d="M0 24 C25 8 50 8 75 24 S125 40 150 24 S190 8 200 18" fill="none" stroke="currentColor" strokeWidth="2" />
        <path d="M0 34 C25 18 50 18 75 34 S125 50 150 34 S190 18 200 28" fill="none" stroke="currentColor" strokeWidth="1.2" opacity=".6" />
      </svg>
      <span className="cv-title">{pick(book.title, lang)}</span>
      <span className="cv-author">{pick(book.author, lang)}</span>
      <span className="cv-pub">{pick(publisher.name, lang)}</span>
    </span>
  );
}

/* ---------- مشخصات ---------- */

function Specs({ book, ui, lang }: { book: Book; ui: BookUi; lang: Lang }) {
  const rows: [string, string][] = [
    [ui.by, pick(book.author, lang)],
    ...(book.translator ? ([[ui.translator, pick(book.translator, lang)]] as [string, string][]) : []),
    ...(book.genre ? ([[ui.genre, pick(book.genre, lang)]] as [string, string][]) : []),
    ...(book.year ? ([[ui.year, book.year]] as [string, string][]) : []),
    ...(book.pages ? ([[ui.pages, book.pages]] as [string, string][]) : []),
    ...(book.size ? ([[ui.size, book.size]] as [string, string][]) : []),
    ...(book.isbn ? ([[ui.isbn, book.isbn]] as [string, string][]) : []),
    ...(book.price ? ([[ui.price, book.price]] as [string, string][]) : []),
  ];
  return (
    <dl className="b3-specs">
      {rows.map(([k, v]) => (
        <div key={k}>
          <dt>{k}</dt>
          <dd>{k === ui.isbn ? <bdi dir="ltr">{v}</bdi> : v}</dd>
        </div>
      ))}
    </dl>
  );
}

function Contacts({ ui, lang }: { ui: BookUi; lang: Lang }) {
  const p = publisher;
  const items: { k: string; label: string; href?: string; ltr?: boolean; ext?: boolean }[] = [];
  if (p.phone && digits(p.phone)) items.push({ k: 'call', label: `${ui.call}: \u2066${phoneShown(p.phone)}\u2069`, href: `tel:+${digits(p.phone)}` });
  if (digits(p.whatsapp)) items.push({ k: 'wa', label: ui.whatsapp, href: `https://wa.me/${digits(p.whatsapp)}`, ext: true });
  if (safeUser(p.telegram)) items.push({ k: 'tg', label: ui.telegram, href: `https://t.me/${p.telegram}`, ext: true });
  if (safeUser(p.instagram)) items.push({ k: 'ig', label: ui.instagram, href: `https://instagram.com/${p.instagram}`, ext: true });
  if (safeHttps(p.website)) items.push({ k: 'web', label: ui.website, href: p.website, ext: true });
  if (p.email && /^[^\s@<>"']+@[^\s@<>"']+$/.test(p.email)) items.push({ k: 'mail', label: p.email, href: `mailto:${p.email}`, ltr: true });
  if (p.address) items.push({ k: 'addr', label: `${ui.address}: ${pick(p.address, lang)}` });
  if (!items.length) return null;
  return (
    <ul className="b3-contacts">
      {items.map((it) => (
        <li key={it.k}>
          {it.href ? (
            <a href={it.href} {...(it.ext ? { target: '_blank', rel: 'noopener noreferrer' } : {})} data-sound="tick">
              {it.ltr ? <bdi dir="ltr">{it.label}</bdi> : it.label}
            </a>
          ) : (
            <span>{it.label}</span>
          )}
        </li>
      ))}
    </ul>
  );
}

/* ---------- کتابخانه + میز تحریر ---------- */

/** فنجان چای با بخار متحرک روی میز تحریر */
function Teacup() {
  return (
    <svg className="bs-tea" viewBox="0 0 90 84" aria-hidden="true" focusable="false">
      <g className="bs-steam" fill="none" stroke="#fff" strokeWidth="3" strokeLinecap="round" opacity="0.7">
        <path className="s1" d="M34 30c-5-6 5-9 0-16" />
        <path className="s2" d="M46 30c-5-6 5-9 0-18" />
        <path className="s3" d="M58 30c-5-6 5-9 0-14" />
      </g>
      <ellipse cx="45" cy="74" rx="38" ry="8" fill="#1c0f06" opacity="0.28" />
      <ellipse cx="45" cy="68" rx="34" ry="8" fill="#f4ece0" stroke="#c9b48e" strokeWidth="1.5" />
      <path d="M20 36h50c0 18-8 32-25 32S20 54 20 36z" fill="#fffaf0" stroke="#c9b48e" strokeWidth="1.5" />
      <path d="M70 41c11-2 11 14-1 15" fill="none" stroke="#c9b48e" strokeWidth="4" strokeLinecap="round" />
      <ellipse cx="45" cy="36" rx="25" ry="5" fill="#9a4a1d" />
      <path d="M24 48h42" stroke="#0a6fb5" strokeWidth="3" opacity="0.55" />
    </svg>
  );
}

/** قلم روی میز */
function Pen() {
  return (
    <svg className="bs-pen" viewBox="0 0 160 24" aria-hidden="true" focusable="false">
      <path d="M6 12l22-6v12z" fill="#e9d3a3" />
      <path d="M2 12l6-2v4z" fill="#222" />
      <rect x="28" y="6" width="112" height="12" rx="6" fill="#0a3e66" />
      <rect x="28" y="6" width="112" height="4" rx="2" fill="#fff" opacity="0.2" />
      <rect x="100" y="6" width="5" height="12" fill="#d9a441" />
      <path d="M140 9l16 1v4l-16 1z" fill="#d9a441" />
    </svg>
  );
}

export default function BookShelf({ lang }: { lang: Lang }) {
  const ui = bookUi[lang];
  const [selId, setSelId] = useState<string | null>(null);
  const [open, setOpen] = useState(false);
  const deskRef = useRef<HTMLElement>(null);
  const headRef = useRef<HTMLHeadingElement>(null);
  const caseRef = useRef<HTMLDivElement>(null);
  const lastBtn = useRef<HTMLButtonElement | null>(null);
  const timer = useRef(0);

  const choose = useCallback((id: string, btn: HTMLButtonElement) => {
    lastBtn.current = btn;
    window.clearTimeout(timer.current);
    setSelId(id);
    setOpen(false);
    // کتاب با جلد بسته روی میز می‌نشیند؛ بازدیدکننده با کلیک روی جلد (یا دکمه) ورقش می‌زند
  }, []);

  const close = useCallback(() => {
    window.clearTimeout(timer.current);
    setSelId(null);
    setOpen(false);
    caseRef.current?.scrollIntoView({ behavior: reduced() ? 'auto' : 'smooth', block: 'center' });
    lastBtn.current?.focus({ preventScroll: true });
  }, []);

  // با انتخاب کتاب، صفحه به میز تحریر پایین می‌آید
  useEffect(() => {
    if (!selId) return;
    deskRef.current?.scrollIntoView({ behavior: reduced() ? 'auto' : 'smooth', block: 'start' });
    const t = window.setTimeout(() => headRef.current?.focus({ preventScroll: true }), reduced() ? 0 : 800);
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') close();
    };
    document.addEventListener('keydown', onKey);
    return () => {
      window.clearTimeout(t);
      document.removeEventListener('keydown', onKey);
    };
  }, [selId, close]);

  useEffect(() => () => window.clearTimeout(timer.current), []);

  const shelves: { book: Book; idx: number }[][] = [];
  books.forEach((book, idx) => {
    const r = Math.floor(idx / PER_SHELF);
    (shelves[r] ||= []).push({ book, idx });
  });
  if (!shelves.length) shelves.push([]);
  const selIdx = books.findIndex((b) => b.id === selId);
  const sel = selIdx >= 0 ? books[selIdx] : null;
  const selColor = sel ? colorOf(sel, selIdx) : '#0a3e66';
  const paragraphs = sel ? pick(sel.summary, lang).split(/\n\s*\n/).map((s) => s.trim()).filter(Boolean) : [];
  const buy = (sel?.buy || []).filter((b) => safeHttps(b.url));

  return (
    <section className="bs" aria-labelledby="bs-title">
      <h2 id="bs-title" className="accent">
        {ui.title}
      </h2>
      <p className="bs-lead">{ui.lead}</p>

      <div className="bs-case" ref={caseRef} role="group" aria-label={ui.shelfLabel}>
        <div className="bs-top-plant" aria-hidden="true">
          <PlantBig />
        </div>
        <div className="bs-ivy-wrap" aria-hidden="true">
          <Ivy />
        </div>
        <div className="bs-body">
          {shelves.map((row, r) => (
            <div className="bs-shelf" key={r}>
              <div className="bs-row">
                {r === 0 ? <PlantSmall /> : null}
                {row.map(({ book, idx }) => {
                  const color = colorOf(book, idx);
                  const active = book.id === selId;
                  return (
                    <button
                      key={book.id}
                      type="button"
                      className={`bk${idx % 7 === 3 ? ' lean' : ''}${active ? ' is-active' : ''}`}
                      style={
                        {
                          '--c': color,
                          '--bk-ink': inkOn(color),
                          '--w': `${Math.max(WIDTHS[idx % WIDTHS.length], spineW(pick(book.title, lang)))}px`,
                          '--tfs': pick(book.title, lang).length > 22 ? '0.72rem' : '0.86rem',
                          '--h': `${HEIGHTS[idx % HEIGHTS.length]}px`,
                        } as CSSProperties
                      }
                      aria-pressed={active}
                      aria-label={`${ui.openHint}: ${pick(book.title, lang)}`}
                      data-sound="pop"
                      onClick={(e) => choose(book.id, e.currentTarget)}
                    >
                      <span className="bk-spine">
                        <span className="bk-t">{pick(book.title, lang)}</span>
                        <span className="bk-a">{pick(book.author, lang)}</span>
                      </span>
                      <span className="bk-edge" aria-hidden="true" />
                      <span className="bk-top" aria-hidden="true" />
                    </button>
                  );
                })}
                {!books.length ? <p className="bs-empty">{ui.empty}</p> : null}
                {r === 1 ? <PlantSmall /> : null}
              </div>
              <div className="bs-board" aria-hidden="true" />
            </div>
          ))}
        </div>
        <div className="bs-plinth" aria-hidden="true">
          <span className="bs-plaque">{pick(publisher.name, lang)}</span>
        </div>
      </div>

      {sel ? (
        <section className="bs-desk" ref={deskRef} aria-label={ui.deskLabel}>
          <div className="bs-desk-bar">
            <div>
              <h3 ref={headRef} tabIndex={-1}>
                {pick(sel.title, lang)}
              </h3>
              <p className="bs-desk-by">{pick(sel.author, lang)}</p>
            </div>
            <div className="bs-desk-actions">
              <button type="button" className="btn btn-blue" aria-expanded={open} onClick={() => setOpen((o) => !o)} data-sound="pop">
                {open ? ui.closeBook : ui.openBook}
              </button>
              <button type="button" className="btn btn-outline" onClick={close} data-sound="pop">
                {ui.back}
              </button>
            </div>
          </div>

          <div className="bs-scene">
            <Lamp />
            <div className="bs-desk-plant" aria-hidden="true">
              <PlantSmall />
            </div>
            <Teacup />
            <Pen />

            <div className={`b3-stage${open ? ' is-open' : ''}`} key={sel.id} style={{ '--c': selColor, '--bk-ink': inkOn(selColor) } as CSSProperties}>
              <div className="b3-shadow" aria-hidden="true" />
              <article className="b3">
                <div className="b3-base">
                  <div className="b3-narrow">
                    <Specs book={sel} ui={ui} lang={lang} />
                  </div>
                  <h4>{ui.summary}</h4>
                  {paragraphs.map((t, i) => (
                    <p key={i}>{t}</p>
                  ))}

                  <div className="b3-order">
                    <h4>{ui.order}</h4>
                    <p>
                      {ui.manager}: <strong>{pick(publisher.manager, lang)}</strong>
                    </p>
                    <Contacts ui={ui} lang={lang} />
                    <p className="b3-note">{ui.orderNote}</p>
                  </div>

                  {buy.length ? (
                    <div className="b3-buy">
                      <h4>{ui.buy}</h4>
                      <ul>
                        {buy.map((b) => (
                          <li key={b.url}>
                            <a className="btn btn-blue" href={b.url} target="_blank" rel="noopener noreferrer" data-sound="pop">
                              {pick(b.label, lang)}
                            </a>
                          </li>
                        ))}
                      </ul>
                      <p className="b3-note">{ui.buyNote}</p>
                    </div>
                  ) : null}
                </div>

                <span className="b3-ribbon" aria-hidden="true" />
                <div className="b3-cover">
                  {/* کلیک روی جلد فقط میان‌بُر ماوس است؛ کنترل دسترسی‌پذیر همان دکمهٔ بالای میز است */}
                  <div className="b3-front" aria-hidden="true" onClick={() => setOpen(true)}>
                    <Cover book={sel} color={selColor} lang={lang} />
                    <span className="b3-hint">{ui.flipHint}</span>
                  </div>
                  <div className="b3-back">
                    <Specs book={sel} ui={ui} lang={lang} />
                  </div>
                </div>
              </article>
            </div>

            <div className="bs-desktop" aria-hidden="true">
              <span className="bs-desktop-face" />
              <span className="bs-desktop-edge" />
            </div>
          </div>
        </section>
      ) : null}
    </section>
  );
}
