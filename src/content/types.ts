export type Lang = 'fa' | 'en' | 'ru';

export type PageId =
  | 'home'
  | 'about'
  | 'stay'
  | 'experiences'
  | 'gallery'
  | 'blog'
  | 'events'
  | 'news'
  | 'shop'
  | 'attractions'
  | 'restaurants'
  | 'contact';

export type InnerPageId = Exclude<PageId, 'home'>;

export interface Section {
  /** عنوان بخش (اختیاری) */
  h?: string;
  /** سطح عنوان؛ پیش‌فرض ۲ */
  level?: 2 | 3;
  p: string[];
}

export interface PageContent {
  title: string;
  lead: string;
  sections: Section[];
}

export type PostId =
  | 'one-day'
  | 'souvenirs'
  | 'ancient-cemetery'
  | 'travel-guide'
  | 'experiences-guide'
  | 'publisher-books'
  | 'calendar-guide';

export interface Post {
  id: PostId;
  slug: string;
  title: string;
  excerpt: string;
  /** تاریخ انتشار به صورت ISO */
  date: string;
  lead: string;
  sections: Section[];
}

export interface Tile {
  h: string;
  p: string;
}

export interface HomeContent {
  eyebrow: string;
  h1: string;
  lead: string;
  ctaStay: string;
  ctaEvents: string;
  ctaAbout: string;
  introTitle: string;
  introText: string;
  introCta: string;
  tiles: Tile[];
  stayTitle: string;
  stayText: string;
  stayCta: string;
  doTitle: string;
  doItems: Tile[];
  eventsTitle: string;
  eventsText: string;
  eventsCta: string;
  shopTitle: string;
  shopText: string;
  shopCta: string;
  contactTitle: string;
}

export interface UiStrings {
  skip: string;
  menu: string;
  language: string;
  readMore: string;
  latestPosts: string;
  publishedOn: string;
  backToBlog: string;
  noEvents: string;
  rights: string;
  phoneLabel: string;
  addressLabel: string;
  managerLabel: string;
  sound: { on: string; off: string; hint: string };
  search: {
    label: string;
    placeholder: string;
    button: string;
    title: string;
    lead: string;
    loading: string;
    tooShort: string;
    noResults: string;
    resultsFor: string;
    error: string;
    kinds: { page: string; post: string; product: string; stay: string; event: string };
  };
  form: {
    name: string;
    phone: string;
    email: string;
    subject: string;
    subjectOptions: string[];
    message: string;
    send: string;
    sending: string;
    success: string;
    error: string;
  };
  notFoundTitle: string;
  notFoundText: string;
  notFoundHome: string;
}

export interface Dict {
  lang: Lang;
  dir: 'rtl' | 'ltr';
  /** برای Intl و og:locale */
  locale: string;
  ogLocale: string;
  langName: string;
  siteName: string;
  tagline: string;
  nav: Record<PageId, string>;
  slugs: Record<InnerPageId, string>;
  /** اسلاگ صفحه جستجو */
  searchSlug: string;
  /** اسلاگ‌های دیگری که صفحه اصلی هم روی آن‌ها نمایش داده می‌شود (آدرس‌های قدیمی وردپرس) */
  homeAliases: string[];
  pages: Record<InnerPageId, PageContent>;
  posts: Post[];
  home: HomeContent;
  ui: UiStrings;
  phone: string;
  phoneDisplay: string;
  /** نام مدیر مجموعه */
  manager: string;
  address: string;
  org: string;
}
