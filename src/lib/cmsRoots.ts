/** همهٔ داده‌های متنی داخل کد که از پنل قابل ویرایش‌اند. هر ریشه‌ای که اینجا باشد در پنل دیده می‌شود. */
import { dicts } from '@/lib/i18n';
import { books, publisher, bookUi } from '@/content/books';
import { calUi, entries, monthNames } from '@/content/calendar';
import { localEvents, localEventsUi } from '@/content/localEvents';
import { mapPoints, mapStrings } from '@/content/map';
import { products, productUi } from '@/content/products';
import { galleryItems, galleryUi } from '@/content/gallery';
import { people, experiences, expUi } from '@/content/experiences';
import { messengerUi } from '@/content/messengers';

export const cmsRoots: Record<string, unknown> = {
  dicts,
  books,
  publisher,
  bookUi,
  calUi,
  calEntries: entries,
  monthNames,
  localEvents,
  localEventsUi,
  mapPoints,
  mapStrings,
  products,
  productUi,
  galleryItems,
  galleryUi,
  people,
  experiences,
  expUi,
  messengerUi,
};

/** کلیدهایی که متن نیستند (آدرس، شناسه، رنگ، نام فایل، …)؛ ویرایششان سایت را خراب می‌کند */
const SKIP_KEYS = new Set([
  'id', 'slug', 'slugs', 'searchSlug', 'homeAliases', 'lang', 'dir', 'locale', 'ogLocale', 'url', 'href', 'kind', 'art', 'icon', 'color', 'cat', 'scope',
  'file', 'thumb', 'photo', 'cover', 'postId', 'expId', 'personId', 'phone', 'phoneDisplay', 'lat', 'lon',
]);

export function flattenRoots(): Record<string, string> {
  const out: Record<string, string> = {};
  const walk = (v: unknown, path: string) => {
    if (typeof v === 'string') {
      if (v.trim() !== '' && !/^https?:\/\//.test(v) && !/^\/[\w./-]*$/.test(v) && !/^\d{4}-\d{2}-\d{2}/.test(v) && !/^[+\d]{8,}$/.test(v)) out[path] = v;
      return;
    }
    if (Array.isArray(v)) return v.forEach((x, i) => walk(x, `${path}.${i}`));
    if (v && typeof v === 'object') {
      for (const [k, x] of Object.entries(v)) {
        // جلد کتاب‌ها از پنل (بخش «جلد کتاب‌ها») عوض می‌شود؛ کلید cover فقط برای کتاب‌ها وارد فهرست می‌شود
        if (k === 'cover' && path.startsWith('books.') && typeof x === 'string') { out[`${path}.${k}`] = x; continue; }
        if (!SKIP_KEYS.has(k)) walk(x, `${path}.${k}`);
      }
    }
  };
  for (const [name, obj] of Object.entries(cmsRoots)) walk(obj, name);
  return out;
}
