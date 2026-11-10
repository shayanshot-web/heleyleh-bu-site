import Link from '@/components/A';
import type { ReactNode } from 'react';
import type { PageId } from '@/content/types';
import Header from './Header';
import { Icon, WaveEdge, type IconName } from './Art';
import LangSync from './LangSync';
import SoundFX from './SoundFX';
import Messengers from './Messengers';
import Aquarium from './Aquarium';
import { messengerUi } from '@/content/messengers';
import HelilehMap from './HelilehMap';
import {
  altRoute,
  dicts,
  formatYear,
  langs,
  pagePath,
  routePath,
  searchPath,
  type Route,
} from '@/lib/i18n';

const navOrder: PageId[] = [
  'home',
  'about',
  'experiences',
  'attractions',
  'stay',
  'restaurants',
  'gallery',
  'events',
  'shop',
  'blog',
  'contact',
];
const navIcons: Record<PageId, IconName> = {
  home: 'home',
  about: 'about',
  experiences: 'experiences',
  attractions: 'attractions',
  stay: 'stay',
  restaurants: 'restaurants',
  gallery: 'gallery',
  events: 'events',
  shop: 'shop',
  blog: 'library',
  contact: 'contact',
};

export default function Shell({ route, children }: { route: Route; children: ReactNode }) {
  const lang = route.lang;
  const d = dicts[lang];
  const items = navOrder.map((id) => ({ label: d.nav[id], href: pagePath(lang, id), icon: navIcons[id], clickSound: id === 'blog' ? 'flip' : undefined }));
  const switcher = langs.map((l) => ({
    code: l,
    name: dicts[l].langName,
    href: routePath(altRoute(route, l)),
    active: l === lang,
  }));

  return (
    <div lang={lang} dir={d.dir} className={`shell lang-${lang}`}>
      <LangSync lang={lang} dir={d.dir} />
      <a className="skip-link" href="#main">
        {d.ui.skip}
      </a>
      <Header
        siteName={d.siteName}
        homeHref={pagePath(lang, 'home')}
        items={items}
        langs={switcher}
        menuLabel={d.ui.menu}
        langLabel={d.ui.language}
        searchAction={searchPath(lang)}
        searchLabel={d.ui.search.label}
        searchPlaceholder={d.ui.search.placeholder}
      />
      <main id="main">{children}</main>
      <HelilehMap lang={lang} locale={d.locale} dir={d.dir} />
      <SoundFX onLabel={d.ui.sound.on} offLabel={d.ui.sound.off} hint={d.ui.sound.hint} />
      <footer className="site-footer">
        <WaveEdge position="top" />
        <Aquarium />
        <div className="container ft">
          <div className="ft-brand">
            <p className="ft-title">{d.siteName}</p>
            <p className="ft-tag">{d.tagline}</p>
            <ul className="ft-contact">
              <li>
                <Icon name="user" size={20} />
                <span>{d.manager}</span>
              </li>
              <li>
                <Icon name="phone" size={20} />
                <a href={`tel:${d.phone}`} dir="ltr">
                  {d.phoneDisplay}
                </a>
              </li>
              <li>
                <Icon name="pin" size={20} />
                <span>{d.address}</span>
              </li>
            </ul>
            <Messengers lang={lang} variant="compact" />
          </div>
          <nav aria-label={d.ui.menu} className="ft-nav">
            <ul>
              {items.slice(1).map((i) => (
                <li key={i.href}>
                  <Link href={i.href} data-sound="tick">
                    <span className="ft-ico">
                      <Icon name={i.icon} size={18} />
                    </span>
                    {i.label}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
        </div>
        <div className="ft-bottom">
          <span>
            © {formatYear(lang)} {d.org}. {d.ui.rights}
          </span>
          <span className="ft-credit">
            {messengerUi[lang].credit}{' '}
            <a href="https://websiteco.ir" target="_blank" rel="noopener" dir="ltr">
              WebSiteCo.ir
            </a>
          </span>
        </div>
      </footer>
    </div>
  );
}
