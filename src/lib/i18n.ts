import { fa } from '@/content/fa';
import { en } from '@/content/en';
import { ru } from '@/content/ru';
import { extraPosts } from '@/content/morePosts';
import type { Dict, InnerPageId, Lang, PageId, Post } from '@/content/types';
import { applyOverrides } from '@/lib/overrides';

export const SITE_URL = 'https://heleyleh-bu.ir';
/**
 * حالت «در حال به‌روزرسانی»: اگر true شود آدرس heleyleh-bu.ir صفحهٔ به‌روزرسانی را نشان می‌دهد
 * و خانهٔ سایت روی /test می‌رود. سایت منتشر شده است، پس false بماند.
 */
export const MAINTENANCE = false;
/**
 * کد تأیید مالکیت سایت در موتورهای جستجو (روش «تگ HTML»).
 * هر کدام را از Search Console / Bing Webmaster / Yandex Webmaster بگیرید و بین گیومه بگذارید؛ خالی باشد تگ ساخته نمی‌شود.
 */
export const SEARCH_VERIFY = { google: '0zNUkBoKxHS0aTESNbBNc2N5oQjdRcGE8HDmDIO6Ubc', bing: '4648680022200BDDF812628003F942DC', yandex: '' };
export const langs: Lang[] = ['fa', 'en', 'ru'];
const withPosts = (d: Dict, l: Lang): Dict => ({ ...d, posts: [...extraPosts[l], ...d.posts] });
export const dicts: Record<Lang, Dict> = { fa: withPosts(fa, 'fa'), en: withPosts(en, 'en'), ru: withPosts(ru, 'ru') };
export const innerIds: InnerPageId[] = [
  'about',
  'stay',
  'experiences',
  'gallery',
  'blog',
  'events',
  'shop',
  'attractions',
  'restaurants',
  'contact',
];

export type Route =
  | { kind: 'home'; lang: Lang }
  | { kind: 'page'; lang: Lang; id: InnerPageId }
  | { kind: 'post'; lang: Lang; post: Post }
  | { kind: 'search'; lang: Lang };

const prefix = (l: Lang) => (l === 'fa' ? '' : `/${l}`);

export function pagePath(l: Lang, id: PageId): string {
  if (id === 'home') return l === 'fa' ? (MAINTENANCE ? '/test/' : '/') : `/${l}/`;
  return `${prefix(l)}/${dicts[l].slugs[id]}/`;
}

export function searchPath(l: Lang): string {
  return `${prefix(l)}/${dicts[l].searchSlug}/`;
}

export function postPath(l: Lang, post: Post): string {
  return `${prefix(l)}/${post.slug}/`;
}

export function routePath(r: Route): string {
  switch (r.kind) {
    case 'home':
      return pagePath(r.lang, 'home');
    case 'page':
      return pagePath(r.lang, r.id);
    case 'post':
      return postPath(r.lang, r.post);
    case 'search':
      return searchPath(r.lang);
  }
}

/** همان صفحه در زبان دیگر */
export function altRoute(r: Route, l: Lang): Route {
  switch (r.kind) {
    case 'home':
      return { kind: 'home', lang: l };
    case 'page':
      return { kind: 'page', lang: l, id: r.id };
    case 'search':
      return { kind: 'search', lang: l };
    case 'post': {
      const post = dicts[l].posts.find((p) => p.id === r.post.id) ?? dicts[l].posts[0];
      return { kind: 'post', lang: l, post };
    }
  }
}

export function allRoutes(): Route[] {
  const out: Route[] = [];
  for (const lang of langs) {
    out.push({ kind: 'home', lang });
    for (const id of innerIds) out.push({ kind: 'page', lang, id });
    for (const post of dicts[lang].posts) out.push({ kind: 'post', lang, post });
  }
  return out;
}

/** تبدیل بخش‌های آدرس به مسیر منطقی؛ null یعنی ۴۰۴ */
export function resolve(slug: string[]): Route | null {
  let lang: Lang = 'fa';
  let rest = slug;
  if (slug[0] === 'en' || slug[0] === 'ru') {
    lang = slug[0];
    rest = slug.slice(1);
  }
  const d = dicts[lang];
  if (rest.length === 0) return { kind: 'home', lang };
  if (rest.length > 1) return null;
  const s = rest[0];
  if (d.homeAliases.includes(s)) return { kind: 'home', lang };
  if (s === d.searchSlug) return { kind: 'search', lang };
  const id = innerIds.find((i) => d.slugs[i] === s);
  if (id) return { kind: 'page', lang, id };
  const post = d.posts.find((p) => p.slug === s);
  if (post) return { kind: 'post', lang, post };
  return null;
}

/** همه آدرس‌هایی که باید در خروجی استاتیک ساخته شوند، به‌جز صفحه اصلی فارسی (app/page.tsx) */
export function staticSlugs(): string[][] {
  const out: string[][] = [];
  for (const l of langs) {
    const d = dicts[l];
    const base = l === 'fa' ? [] : [l];
    if (l !== 'fa') out.push(base);
    for (const a of d.homeAliases) out.push([...base, a]);
    out.push([...base, d.searchSlug]);
    for (const id of innerIds) out.push([...base, d.slugs[id]]);
    for (const p of d.posts) out.push([...base, p.slug]);
  }
  return out;
}

/** متن بدون عبارت‌های [تکمیل شود] برای توضیحات متا */
export function stripTodo(text: string): string {
  return text
    .replace(/<<([^|>]+)\|(?:https?:\/\/|tel:|mailto:)[^>]+>>/g, '$1')
    .replace(/\s*\[[^\]]+\]/g, '')
    .trim();
}

export function routeTitle(r: Route): string {
  const d = dicts[r.lang];
  switch (r.kind) {
    case 'home':
      return `${d.siteName} | ${d.tagline}`;
    case 'page':
      return `${d.pages[r.id].title} | ${d.siteName}`;
    case 'post':
      return `${r.post.title} | ${d.siteName}`;
    case 'search':
      return `${d.ui.search.title} | ${d.siteName}`;
  }
}

export function routeDescription(r: Route): string {
  const d = dicts[r.lang];
  switch (r.kind) {
    case 'home':
      return d.home.lead;
    case 'page':
      return stripTodo(d.pages[r.id].lead);
    case 'post':
      return stripTodo(r.post.excerpt);
    case 'search':
      return d.ui.search.lead;
  }
}

export function formatDate(lang: Lang, iso: string): string {
  const locale = lang === 'fa' ? 'fa-IR-u-ca-persian' : dicts[lang].locale;
  return new Intl.DateTimeFormat(locale, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    timeZone: 'UTC',
  }).format(new Date(`${iso}T00:00:00Z`));
}

export function formatYear(lang: Lang): string {
  const locale = lang === 'fa' ? 'fa-IR-u-ca-persian' : dicts[lang].locale;
  return new Intl.DateTimeFormat(locale, { year: 'numeric' }).format(new Date());
}
applyOverrides('dicts', dicts);
