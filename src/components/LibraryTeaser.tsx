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
      <span className="lt-glow" />
      <span className="lt-shadow" />
      <span className="lt-book">
        <span className="lt-cover" />
        <span className="lt-block lt-bl" />
        <span className="lt-block lt-br" />
        <span className="lt-page lt-p1" />
        <span className="lt-page lt-p2" />
        <span className="lt-page lt-p3" />
        <span className="lt-ribbon" />
      </span>
    </span>
  );
}

/** پس‌زمینه: دریای شبانه با دو لایه موج و نخل بوشهری */
function Backdrop() {
  return (
    <>
      <svg className="lt-bg" viewBox="0 0 1200 330" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
        <g className="lt-palm" fill="none" stroke="#e9d9ad" strokeLinecap="round" opacity="0.2">
          <path d="M1090 330c4-70 14-120 36-170" strokeWidth="7" />
          <path d="M1126 160c-30-34-70-40-104-26M1126 160c-14-40-46-62-86-64M1126 160c10-38 36-62 76-70M1126 160c26-24 56-30 86-20M1126 160c-6 20-30 30-52 34" strokeWidth="5" />
        </g>
        <g className="w1" fill="#0a6fb5" opacity="0.28">
          <path d="M-100 262c80-26 160-26 240 0s160 26 240 0 160-26 240 0 160 26 240 0 160-26 240 0 160 26 240 0V330H-100z" />
        </g>
        <g className="w2" fill="#5fb4e6" opacity="0.2">
          <path d="M-100 288c80-22 160-22 240 0s160 22 240 0 160-22 240 0 160 22 240 0 160-22 240 0 160 22 240 0V330H-100z" />
        </g>
        <g fill="#031625" opacity="0.5"><path d="M-100 312c120-14 240-14 360 0s240 14 360 0 240-14 360 0 240 14 360 0V330H-100z" /></g>
      </svg>
      <span className="lt-dust">
        {[
          ['9%', '3px', '7s', '0s'],
          ['16%', '4px', '9s', '2s'],
          ['23%', '2px', '8s', '4s'],
          ['30%', '3px', '10s', '1s'],
          ['12%', '2px', '11s', '5s'],
        ].map(([x, sz, t, d]) => (
          <i key={x} style={{ '--x': x, '--s': sz, '--t': t, '--d': d } as React.CSSProperties} />
        ))}
      </span>
    </>
  );
}

export default function LibraryTeaser({ lang }: { lang: Lang }) {
  const t = ui[lang];
  return (
    <section className="section lt-section">
      <div className="container">
        <Link href={pagePath(lang, 'blog')} className="lt-card" data-sound="pop" data-click-sound="flip" aria-label={t.label}>
          <Backdrop />
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
