import type { Metadata } from 'next';
import { MAINTENANCE, altRoute, dicts, routeDescription, routePath, routeTitle, type Route } from './i18n';

const KEYWORDS: Record<string, string[]> = {
  fa: ['هلیله', 'هلیله بوشهر', 'هلیله باستانی', 'روستای گردشگری هلیله', 'گردشگری بوشهر', 'سفر به بوشهر', 'اقامتگاه بوشهر', 'ساحل بوشهر', 'صنایع دستی بوشهر', 'تور نجوم بوشهر', 'نشر هلیله', 'تقویم هلیله'],
  en: ['Helileh', 'Helileh Bushehr', 'ancient Helileh', 'Bushehr tourism', 'Bushehr travel', 'Persian Gulf village', 'Bushehr beach', 'Bushehr handicrafts', 'Helileh Publishing'],
  ru: ['Хелиле', 'Хелиле Бушер', 'Бушер туризм', 'Бушер путешествие', 'Персидский залив', 'пляж Бушер', 'ремёсла Бушера', 'издательство Хелиле'],
};
const keywordsFor = (l: string) => KEYWORDS[l] ?? KEYWORDS.fa;

export function metaFor(route: Route): Metadata {
  const d = dicts[route.lang];
  const title = routeTitle(route);
  const description = routeDescription(route);
  const path = routePath(route);
  return {
    title,
    description,
    // صفحه نتایج جستجو نباید در گوگل ایندکس شود
    ...(route.kind === 'search' || (MAINTENANCE && route.kind === 'home' && route.lang === 'fa')
      ? { robots: { index: false, follow: true } }
      : {}),
    keywords: keywordsFor(route.lang),
    robots: { index: true, follow: true, googleBot: { index: true, follow: true, 'max-image-preview': 'large', 'max-snippet': -1, 'max-video-preview': -1 } },
    alternates: {
      canonical: path,
      languages: {
        'fa-IR': routePath(altRoute(route, 'fa')),
        en: routePath(altRoute(route, 'en')),
        ru: routePath(altRoute(route, 'ru')),
        'x-default': routePath(altRoute(route, 'fa')),
      },
    },
    openGraph: {
      title,
      description,
      url: path,
      siteName: d.siteName,
      locale: d.ogLocale,
      type: route.kind === 'post' ? 'article' : 'website',
      images: [{ url: '/og-image.jpg', width: 1200, height: 630, alt: d.siteName }],
      ...(route.kind === 'post' ? { publishedTime: route.post.date, authors: [d.org] } : {}),
    },
    twitter: { card: 'summary_large_image', title, description, images: ['/og-image.jpg'] },
  };
}
