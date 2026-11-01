import Link from '@/components/A';
import ExpIcon from '@/components/ExpIcon';
import { experiences, expUi, people, type Person } from '@/content/experiences';
import { pagePath } from '@/lib/i18n';
import type { Lang } from '@/content/types';
import { toHtml } from '@/lib/jsx-html';

const tel = (n: string) => `tel:+98${n.slice(1)}`;

function Avatar({ p, lang }: { p: Person; lang: Lang }) {
  const u = expUi[lang];
  return (
    <span className={p.photo ? 'xp-photo has' : 'xp-photo'}>
      {p.photo ? (
        // eslint-disable-next-line @next/next/no-img-element
        <img src={`/people/${p.photo}`} alt={p.name[lang]} width={84} height={84} loading="lazy" />
      ) : (
        <>
          <svg viewBox="0 0 48 48" aria-hidden="true">
            <circle cx="24" cy="18" r="8" />
            <path d="M8 42c2-10 9-14 16-14s14 4 16 14z" />
          </svg>
          <small>{u.photoSoon}</small>
        </>
      )}
    </span>
  );
}

/** نمایش تجربه‌ها به‌همراه آدم‌هایشان (کارت با آیکن متحرک و جای عکس) */
function GridCards({ lang }: { lang: Lang }) {
  const u = expUi[lang];
  return (
    <>
      {experiences.map((e) => (
        <li key={e.id} className="xp-card" data-sound="sea">
          <span className="xp-icon">
            <ExpIcon name={e.id} />
          </span>
          <h3>{e.name[lang]}</h3>
          {e.who.length === 0 ? (
            <p className="xp-free">{u.noPerson}</p>
          ) : (
            <ul className="xp-people">
              {e.who.map((id) => {
                const p = people[id];
                return (
                  <li key={id}>
                    <Avatar p={p} lang={lang} />
                    <span className="xp-who">
                      <small>{u.coordinator}</small>
                      <strong>{p.name[lang]}</strong>
                      {p.phone ? (
                        <a href={tel(p.phone)} dir="ltr" className="xp-call">
                          {p.phone}
                        </a>
                      ) : null}
                    </span>
                  </li>
                );
              })}
            </ul>
          )}
        </li>
      ))}
    </>
  );
}

/**
 * کارت‌ها به‌صورت HTML آماده داخل <ul> گذاشته می‌شوند (dangerouslySetInnerHTML).
 * دلیل: پنل مدیریت (PHP) همین <ul> را با داده‌های پنل جایگزین می‌کند و React موقع هیدریشن
 * محتوای داخلی این عنصر را مقایسه و بازنویسی نمی‌کند؛ پس داده‌های پنل می‌مانند و بقیهٔ صفحه (نظرها، صدا، منو) کار می‌کند.
 */
export function ExperiencesGrid({ lang }: { lang: Lang }) {
  return <ul className="xp-grid" suppressHydrationWarning dangerouslySetInnerHTML={{ __html: toHtml(<GridCards lang={lang} />) }} />;
}

function StripItems({ lang }: { lang: Lang }) {
  return (
    <>
      {experiences.slice(0, 12).map((e) => (
        <li key={e.id}>
          <Link href={pagePath(lang, 'experiences')} data-sound="sea">
            <span className="xp-icon sm">
              <ExpIcon name={e.id} />
            </span>
            <span>{e.name[lang]}</span>
          </Link>
        </li>
      ))}
    </>
  );
}

/** نوار معرفی در صفحهٔ اصلی */
export function ExperiencesTeaser({ lang }: { lang: Lang }) {
  const u = expUi[lang];
  return (
    <section className="section xp-teaser">
      <div className="container">
        <h2 className="accent">{u.teaserTitle}</h2>
        <ul className="xp-strip" suppressHydrationWarning dangerouslySetInnerHTML={{ __html: toHtml(<StripItems lang={lang} />) }} />
        <p>
          <Link className="btn btn-blue" data-sound="pop" href={pagePath(lang, 'experiences')}>
            {u.teaserCta}
          </Link>
        </p>
      </div>
    </section>
  );
}
