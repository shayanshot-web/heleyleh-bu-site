/**
 * متن‌های ویرایش‌شده در پنل مدیریت (/admin ← «متن‌های سایت»).
 * سرور (ov.php) فهرست تغییرها را داخل هر صفحه به‌صورت window.__HC_OV می‌گذارد؛
 * این تابع همان متن‌ها را روی داده‌های داخل کد می‌نشاند تا هیدریشن با HTML تغییریافته یکی باشد.
 * کلیدها: «نام‌ریشه.مسیر.با.نقطه» مثل dicts.fa.home.h1 یا books.0.title.fa
 */
declare global {
  interface Window {
    __HC_OV?: Record<string, string>;
  }
}

export function applyOverrides(root: string, obj: unknown): void {
  if (typeof window === 'undefined') return;
  const ov = window.__HC_OV;
  if (!ov) return;
  const pre = root + '.';
  for (const key of Object.keys(ov)) {
    if (!key.startsWith(pre) || typeof ov[key] !== 'string') continue;
    const parts = key.slice(pre.length).split('.');
    let cur: any = obj;
    for (let i = 0; i < parts.length - 1 && cur != null; i++) cur = cur[parts[i]];
    const last = parts[parts.length - 1];
    if (cur != null && typeof cur === 'object' && typeof cur[last] === 'string') cur[last] = ov[key];
  }
}
