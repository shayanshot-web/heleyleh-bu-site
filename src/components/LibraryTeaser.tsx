import Link from '@/components/A';
import type { Lang } from '@/content/types';
import { pagePath } from '@/lib/i18n';

/** بخش معرفی کوتاه نشر هلیله در صفحهٔ اصلی؛ کل کارت پیوند به صفحهٔ کتابخانه است */
const ui: Record<Lang, { title: string; quote: string; text: string; cta: string; label: string }> = {
  fa: {
    title: 'نشر هلیله',
    quote: 'بیا تو، یه چایی وردار و یه کتاب؛ دریا تو ورقاش نفس می‌کشه.',
    text: 'قصه‌هایی که بوی نمک و نخل می‌دهند. هر کتاب ما ساحلی است؛ فقط باید ورقش بزنی.',
    cta: 'ورق بزن، کتابخانه را ببین',
    label: 'رفتن به کتابخانهٔ نشر هلیله',
  },
  en: {
    title: 'Helileh Publishing',
    quote: 'Come in, take a cup of tea and a book. The sea breathes between the pages.',
    text: 'Stories that smell of salt and palm trees. Every book is a shoreline; you only have to turn the page.',
    cta: 'Turn a page, see the library',
    label: 'Go to the Helileh Publishing library',
  },
  ru: {
    title: 'Издательство «Хелиле»',
    quote: 'Заходи, возьми чаю и книгу. Море дышит между страницами.',
    text: 'Истории, пахнущие солью и пальмами. Каждая книга как берег: нужно только перевернуть страницу.',
    cta: 'Перелистни и загляни в библиотеку',
    label: 'Перейти в библиотеку издательства «Хелиле»',
  },
};

function BookIcon() {
  return (
    <span className="lt-art" aria-hidden="true">
      <span className="lt-book">
        <span className="lt-cover" />
        <span className="lt-half lt-hl" />
        <span className="lt-half lt-hr" />
        <span className="lt-page lt-p1" />
        <span className="lt-page lt-p2" />
        <span className="lt-page lt-p3" />
      </span>
      <svg className="lt-wave" viewBox="0 0 120 20" width="120" height="20">
        <path d="M0 10c10-8 20-8 30 0s20 8 30 0 20-8 30 0 20 8 30 0" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
      </svg>
      <svg className="lt-leaf lt-l1" viewBox="0 0 24 24" width="22" height="22">
        <path d="M4 20C4 9 11 4 20 4c0 9-5 16-16 16z" fill="currentColor" />
      </svg>
      <svg className="lt-leaf lt-l2" viewBox="0 0 24 24" width="16" height="16">
        <path d="M4 20C4 9 11 4 20 4c0 9-5 16-16 16z" fill="currentColor" />
      </svg>
    </span>
  );
}

export default function LibraryTeaser({ lang }: { lang: Lang }) {
  const t = ui[lang];
  return (
    <section className="section lt-section">
      <div className="container">
        <Link href={pagePath(lang, 'blog')} className="lt-card" data-sound="pop" data-click-sound="flip" aria-label={t.label}>
          <BookIcon />
          <span className="lt-body">
            <h2>{t.title}</h2>
            <p className="lt-quote">«{t.quote}»</p>
            <p className="lt-text">{t.text}</p>
            <span className="lt-cta">{t.cta}</span>
          </span>
        </Link>
      </div>
    </section>
  );
}
