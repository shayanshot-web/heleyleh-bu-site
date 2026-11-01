import type { ReactNode } from 'react';

/**
 * سریالایزر کوچک JSX → رشتهٔ HTML برای کامپوننت‌های ساده (بدون state و hook).
 * react-dom/server در App Router مجاز نیست؛ این تابع برای دو کار استفاده می‌شود:
 *  ۱) /xp-icons.json (SVG آیکن‌ها برای پنل مدیریت)
 *  ۲) رندر کارت‌های «تجربه‌ها» به‌صورت HTML تا پنل (PHP) بتواند همان بخش را بدون خراب‌کردن هیدریشن React جایگزین کند.
 */
const esc = (s: string) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const kebab = (s: string) => s.replace(/[A-Z]/g, (m) => '-' + m.toLowerCase());
const KEEP = new Set(['viewBox', 'preserveAspectRatio']);
const VOID = new Set(['img', 'br', 'hr', 'input', 'meta', 'link']);

function attrName(k: string): string {
  if (k === 'className') return 'class';
  return KEEP.has(k) ? k : kebab(k);
}

export function toHtml(node: unknown): string {
  if (node === null || node === undefined || typeof node === 'boolean') return '';
  if (typeof node === 'string' || typeof node === 'number') return esc(String(node));
  if (Array.isArray(node)) return node.map(toHtml).join('');
  const el = node as { type: unknown; props?: Record<string, unknown> };
  const props = el.props ?? {};
  if (typeof el.type === 'function') return toHtml((el.type as (p: unknown) => ReactNode)(props));
  if (typeof el.type === 'symbol') return toHtml(props.children); // Fragment
  const tag = String(el.type);
  let attrs = '';
  for (const [k, v] of Object.entries(props)) {
    if (k === 'children' || v === undefined || v === null || v === false) continue;
    if (k === 'style' && typeof v === 'object') {
      const css = Object.entries(v as Record<string, string | number>).map(([a, b]) => `${kebab(a)}:${b}`).join(';');
      attrs += ` style="${esc(css)}"`;
    } else {
      attrs += ` ${attrName(k)}="${esc(String(v))}"`;
    }
  }
  return VOID.has(tag) ? `<${tag}${attrs}/>` : `<${tag}${attrs}>${toHtml(props.children)}</${tag}>`;
}
