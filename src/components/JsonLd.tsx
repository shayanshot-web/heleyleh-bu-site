import { books, pick, publisher } from '@/content/books';
import { dicts, pagePath, routePath, SITE_URL, type Route } from '@/lib/i18n';

/** داده‌های ساختاریافتهٔ گوگل (Schema.org). به شکل JSON-LD در صفحه گذاشته می‌شود. */
const abs = (p: string) => `${SITE_URL}${p}`;

function graph(route: Route): Record<string, unknown>[] {
  const d = dicts[route.lang];
  const url = abs(routePath(route));
  const orgId = `${SITE_URL}/#org`;
  const siteId = `${SITE_URL}/#website`;
  const placeId = `${SITE_URL}/#destination`;
  const inLanguage = route.lang === 'fa' ? 'fa-IR' : route.lang;

  const organization = {
    '@type': 'Organization',
    '@id': orgId,
    name: d.org,
    url: SITE_URL,
    logo: abs('/icon.svg'),
    email: 'info@heleyleh-bu.ir',
    telephone: '+989176443247',
    address: { '@type': 'PostalAddress', addressLocality: 'Helileh, Bushehr', addressRegion: 'Bushehr', addressCountry: 'IR' },
  };
  const website = { '@type': 'WebSite', '@id': siteId, url: SITE_URL, name: d.siteName, inLanguage, publisher: { '@id': orgId } };
  const destination = {
    '@type': 'TouristDestination',
    '@id': placeId,
    name: d.siteName,
    description: d.home.lead,
    url: SITE_URL,
    address: { '@type': 'PostalAddress', addressLocality: 'Helileh, Bushehr', addressRegion: 'Bushehr', addressCountry: 'IR' },
    touristType: ['Beach holidays', 'Cultural tourism', 'Handicrafts', 'Astronomy'],
  };

  const out: Record<string, unknown>[] = [];
  if (route.kind === 'home') {
    out.push(organization, website, destination);
    return out;
  }

  out.push({ '@type': 'WebSite', '@id': siteId, url: SITE_URL, name: d.siteName, publisher: { '@id': orgId } });
  const crumbs = [{ name: d.nav.home, url: abs(pagePath(route.lang, 'home')) }];

  if (route.kind === 'page') {
    crumbs.push({ name: d.pages[route.id].title, url });
    out.push({ '@type': 'WebPage', '@id': `${url}#page`, url, name: d.pages[route.id].title, inLanguage, isPartOf: { '@id': siteId } });
    if (route.id === 'blog' && books.length) {
      out.push({
        '@type': 'ItemList',
        name: pick(publisher.name, route.lang),
        itemListElement: books.map((b, i) => ({
          '@type': 'ListItem',
          position: i + 1,
          item: {
            '@type': 'Book',
            name: pick(b.title, route.lang),
            author: { '@type': 'Person', name: pick(b.author, route.lang) },
            publisher: { '@type': 'Organization', name: pick(publisher.name, route.lang), telephone: publisher.phone },
            inLanguage: 'fa-IR',
            ...(b.isbn ? { isbn: b.isbn } : {}),
            ...(b.genre ? { genre: pick(b.genre, route.lang) } : {}),
          },
        })),
      });
    }
  } else if (route.kind === 'post') {
    const blogUrl = abs(pagePath(route.lang, 'blog'));
    crumbs.push({ name: d.pages.blog.title, url: blogUrl }, { name: route.post.title, url });
    out.push({
      '@type': 'Article',
      '@id': `${url}#article`,
      headline: route.post.title,
      description: route.post.excerpt,
      datePublished: route.post.date,
      dateModified: route.post.date,
      inLanguage,
      mainEntityOfPage: url,
      author: { '@id': orgId },
      publisher: { '@id': orgId },
      image: abs('/og-image.jpg'),
      about: { '@id': placeId },
    });
    out.push(organization);
  }

  out.push({
    '@type': 'BreadcrumbList',
    itemListElement: crumbs.map((c, i) => ({ '@type': 'ListItem', position: i + 1, name: c.name, item: c.url })),
  });
  return out;
}

export default function JsonLd({ route }: { route: Route }) {
  const json = JSON.stringify({ '@context': 'https://schema.org', '@graph': graph(route) }).replace(/</g, '\\u003c');
  return <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: json }} />;
}
