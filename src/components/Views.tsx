import Link from '@/components/A';
import type { InnerPageId, Lang, Post } from '@/content/types';
import ContactForm from './ContactForm';
import { Rich, Sections } from './Rich';
import SearchView from './SearchView';
import CalendarView from './Calendar';
import GalleryView from './GalleryView';
import LocalEvents from './LocalEvents';
import Messengers from './Messengers';
import Comments from './Comments';
import ShopProducts from './ShopProducts';
import BookShelf from './BookShelf';
import { ExperiencesGrid, ExperiencesTeaser } from './ExperiencesView';
import { TileArt, type TileArtName } from './TileArt';
import { HeroScene, Icon, WaveEdge, type IconName } from './Art';
import { dicts, formatDate, pagePath, postPath, searchPath, type Route } from '@/lib/i18n';

const pageIcons: Record<InnerPageId, IconName> = {
  about: 'about',
  stay: 'stay',
  experiences: 'experiences',
  gallery: 'gallery',
  blog: 'library',
  events: 'events',
  shop: 'shop',
  attractions: 'attractions',
  restaurants: 'restaurants',
  contact: 'contact',
};

function PageHero({
  title,
  lead,
  meta,
  icon,
  lang,
}: {
  title: string;
  lead?: string;
  meta?: string;
  icon?: IconName;
  lang: Lang;
}) {
  return (
    <section className="page-hero">
      <div className="container page-hero-row">
        {icon ? (
          <span className="hero-badge" data-sound={icon}>
            <Icon name={icon} size={44} />
          </span>
        ) : null}
        <div>
          <h1>{title}</h1>
          {meta ? <p className="meta">{meta}</p> : null}
          {lead ? (
            <p className="lead">
              <Rich text={lead} />
            </p>
          ) : null}
        </div>
      </div>
      <WaveEdge position="bottom" />
    </section>
  );
}

function BlogList({ lang }: { lang: Lang }) {
  const d = dicts[lang];
  return (
    <section>
      <h2>{d.ui.latestPosts}</h2>
      <div className="cards">
        {d.posts.map((p: Post) => (
          <article key={p.id} className="card">
            <p className="meta">{formatDate(lang, p.date)}</p>
            <h3>
              <Link href={postPath(lang, p)}>{p.title}</Link>
            </h3>
            <p>{p.excerpt}</p>
            <Link className="more" href={postPath(lang, p)}>
              {d.ui.readMore}
            </Link>
          </article>
        ))}
      </div>
    </section>
  );
}

export function HomeView({ lang }: { lang: Lang }) {
  const d = dicts[lang];
  const h = d.home;
  // هر کاشی به صفحه اختصاصی خودش می‌رود: معرفی، اقامتگاه‌ها، جاذبه‌ها، رستوران‌ها
  const tileLinks: InnerPageId[] = ['about', 'stay', 'attractions', 'restaurants'];
  const tileIcons = ['about', 'stay', 'attractions', 'restaurants'] as const;
  const doIcons: IconName[] = ['sea', 'food', 'craft', 'culture'];
  return (
    <>
      <section className="hero" data-sound="sea">
        <div className="container hero-copy">
          <p className="eyebrow">{h.eyebrow}</p>
          <h1>{h.h1}</h1>
          <p className="hero-lead">{h.lead}</p>
          <div className="btn-row">
            <Link data-sound="pop" className="btn btn-sand" href={pagePath(lang, 'about')}>
              {h.ctaAbout}
            </Link>
            <Link data-sound="pop" className="btn btn-outline" href={pagePath(lang, 'events')}>
              {h.ctaEvents}
            </Link>
          </div>
        </div>
        <div className="hero-art">
          <HeroScene />
        </div>
      </section>

      <section className="section">
        <div className="container narrow">
          <h2 className="accent">{h.introTitle}</h2>
          <p>{h.introText}</p>
          <Link data-sound="pop" className="btn btn-blue" href={pagePath(lang, 'about')}>
            {h.introCta}
          </Link>
        </div>
      </section>

      <section className="container tiles-wrap">
        <ul className="tiles">
          {h.tiles.map((t, i) => (
            <li key={t.h}>
              <Link href={pagePath(lang, tileLinks[i])} className="tile" data-sound={tileIcons[i]}>
                <span className="tile-art">
                  <TileArt name={tileIcons[i] as TileArtName} />
                </span>
                <span className="tile-body">
                  <h3>{t.h}</h3>
                  <p>{t.p}</p>
                </span>
              </Link>
            </li>
          ))}
        </ul>
      </section>

      <section className="band">
        <WaveEdge position="top" />
        <div className="container narrow">
          <h2>{h.stayTitle}</h2>
          <p>{h.stayText}</p>
          <Link data-sound="pop" className="btn btn-blue" href={pagePath(lang, 'contact')}>
            {h.stayCta}
          </Link>
        </div>
        <WaveEdge position="bottom" />
      </section>

      <ExperiencesTeaser lang={lang} />

      <section className="section">
        <div className="container">
          <h2 className="accent">{h.doTitle}</h2>
          <div className="cards four">
            {h.doItems.map((t, i) => (
              <div key={t.h} className="card do-card" data-sound={doIcons[i]}>
                <span className="do-icon">
                  <Icon name={doIcons[i]} size={40} />
                </span>
                <h3>{t.h}</h3>
                <p>{t.p}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="band">
        <WaveEdge position="top" />
        <div className="container narrow">
          <h2>{h.eventsTitle}</h2>
          <p>
            <Rich text={h.eventsText} />
          </p>
          <Link data-sound="pop" className="btn btn-blue" href={pagePath(lang, 'events')}>
            {h.eventsCta}
          </Link>
        </div>
        <WaveEdge position="bottom" />
      </section>

      <section className="section">
        <div className="container narrow">
          <h2 className="accent">{h.shopTitle}</h2>
          <p>{h.shopText}</p>
          <Link data-sound="pop" className="btn btn-navy" href={pagePath(lang, 'shop')}>
            {h.shopCta}
          </Link>
        </div>
      </section>
    </>
  );
}

export function PageView({ lang, id }: { lang: Lang; id: InnerPageId }) {
  const d = dicts[lang];
  const p = d.pages[id];
  return (
    <>
      <PageHero title={p.title} lead={p.lead} icon={pageIcons[id]} lang={lang} />
      <div className={id === 'events' ? 'container prose prose-wide' : 'container prose'}>
        {id === 'blog' ? (
          <BlogList lang={lang} />
        ) : (
          <>
            {id === 'contact' ? (
              <>
                <ul className="contact-list">
                  <li data-sound="drop">
                    <Icon name="user" size={30} />
                    <span>
                      {d.ui.managerLabel}: <strong>{d.manager}</strong>
                    </span>
                  </li>
                  <li data-sound="contact">
                    <Icon name="phone" size={30} />
                    <a href={`tel:${d.phone}`} dir="ltr">
                      {d.phoneDisplay}
                    </a>
                  </li>
                  <li data-sound="drop">
                    <Icon name="pin" size={30} />
                    <span>{d.address}</span>
                  </li>
                </ul>
                <Messengers lang={lang} />
              </>
            ) : null}
            {id === 'events' ? (
              <>
                <LocalEvents lang={lang} />
                <CalendarView lang={lang} />
              </>
            ) : null}
            {id === 'experiences' ? null : <Sections sections={p.sections} />}
            {id === 'events' ? <p className="notice">{d.ui.noEvents}</p> : null}
            {id === 'stay' || id === 'shop' ? (
              <p>
                <Link data-sound="pop" className="btn btn-blue" href={pagePath(lang, 'contact')}>
                  {d.nav.contact}
                </Link>
              </p>
            ) : null}
            {id === 'contact' ? (
              <>
                <ContactForm ui={d.ui.form} lang={lang} />
              </>
            ) : null}
          </>
        )}
      </div>
      {id === 'experiences' ? (
        <div className="container xp-page">
          <Comments lang={lang} />
          <ExperiencesGrid lang={lang} />
          <div className="prose" style={{ paddingBlock: '24px 0' }}>
            <Sections sections={p.sections} />
          </div>
        </div>
      ) : null}
      {id === 'blog' ? (
        <div className="container">
          <BookShelf lang={lang} />
        </div>
      ) : null}
      {id === 'gallery' ? (
        <div className="container">
          <GalleryView lang={lang} />
        </div>
      ) : null}
      {id === 'shop' ? (
        <div className="container">
          <ShopProducts lang={lang} phone={d.phone} />
        </div>
      ) : null}
    </>
  );
}

export function PostView({ lang, post }: { lang: Lang; post: Post }) {
  const d = dicts[lang];
  return (
    <>
      <PageHero lang={lang} title={post.title} icon="blog" meta={`${d.ui.publishedOn} ${formatDate(lang, post.date)}`} />
      <article className="container prose">
        <p className="lead">{post.lead}</p>
        <Sections sections={post.sections} />
        <p>
          <Link className="more" href={pagePath(lang, 'blog')}>
            {d.ui.backToBlog}
          </Link>
        </p>
      </article>
    </>
  );
}

export function SearchPageView({ lang }: { lang: Lang }) {
  const d = dicts[lang];
  return (
    <>
      <PageHero title={d.ui.search.title} lead={d.ui.search.lead} icon="experiences" lang={lang} />
      <div className="container prose">
        <SearchView lang={lang} ui={d.ui.search} action={searchPath(lang)} />
      </div>
    </>
  );
}

export function View({ route }: { route: Route }) {
  switch (route.kind) {
    case 'search':
      return <SearchPageView lang={route.lang} />;
    case 'home':
      return <HomeView lang={route.lang} />;
    case 'page':
      return <PageView lang={route.lang} id={route.id} />;
    case 'post':
      return <PostView lang={route.lang} post={route.post} />;
  }
}
