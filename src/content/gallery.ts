import type { Lang } from './types';
import { applyOverrides } from '@/lib/overrides';

/**
 * گالری تصاویر.
 * برای افزودن عکس: فایل را در public/gallery/ بگذارید و یک ردیف به galleryItems اضافه کنید.
 */
export type GalleryCat = 'sea' | 'village' | 'people' | 'food' | 'craft' | 'culture';

export interface GalleryItem {
  /** نام فایل بزرگ داخل public/gallery (حدود ۱۸۰۰ پیکسل) */
  file: string;
  /** نسخهٔ کوچک برای شبکه (حدود ۷۲۰ پیکسل) */
  thumb: string;
  /** ابعاد نسخهٔ بزرگ */
  w: number;
  h: number;
  cat: GalleryCat;
  title: Record<Lang, string>;
  /** متن جایگزین؛ اگر نباشد عنوان استفاده می‌شود */
  alt?: Record<Lang, string>;
  place?: Record<Lang, string>;
  /** نام عکاس */
  credit?: string;
}

export const galleryItems: GalleryItem[] = [
  { file: 'sunset-gate.webp', thumb: 'sunset-gate-t.webp', w: 1536, h: 1024, cat: 'sea', title: { fa: 'دروازهٔ چوبی کنار ساحل در غروب', en: 'Wooden gate on the beach at sunset', ru: 'Деревянные ворота на пляже на закате' } },
  { file: 'night-path.webp', thumb: 'night-path-t.webp', w: 1280, h: 960, cat: 'sea', title: { fa: 'مسیر چراغ‌دار تا ساحل در شفق', en: 'A lamp-lit path to the beach at dusk', ru: 'Освещённая дорожка к пляжу в сумерках' } },
  { file: 'golden-boat.webp', thumb: 'golden-boat-t.webp', w: 499, h: 1080, cat: 'sea', title: { fa: 'قایق در غروب طلایی', en: 'A boat in the golden sunset', ru: 'Лодка в золотом закате' } },
  { file: 'dusk-boats.webp', thumb: 'dusk-boats-t.webp', w: 1800, h: 1200, cat: 'sea', title: { fa: 'قایق موتوری و کشتی باری در شفق', en: 'A motorboat and a cargo ship at dusk', ru: 'Моторная лодка и грузовое судно в сумерках' } },
  { file: 'boat-ashore.webp', thumb: 'boat-ashore-t.webp', w: 1200, h: 1800, cat: 'people', title: { fa: 'مردان و قایق کنار ساحل هنگام غروب', en: 'Men and a boat at the water\'s edge at sunset', ru: 'Мужчины и лодка у кромки воды на закате' } },
  { file: 'stone-sign.webp', thumb: 'stone-sign-t.webp', w: 1350, h: 1800, cat: 'village', title: { fa: 'سنگ‌نوشتهٔ «محله باستانی هلیله»', en: 'The "Ancient Helileh quarter" stone sign', ru: 'Каменная табличка «Древний квартал Хелиле»' } },
  { file: 'mural-sun.webp', thumb: 'mural-sun-t.webp', w: 721, h: 1280, cat: 'culture', title: { fa: 'نقاشی دیواری خورشید و موج روی در', en: 'A sun-and-wave mural painted on a door', ru: 'Роспись «солнце и волна» на двери' } },
  { file: 'mural-boat.webp', thumb: 'mural-boat-t.webp', w: 958, h: 1278, cat: 'culture', title: { fa: 'نقاشی قایق و موج با خوشنویسی روی در', en: 'A boat and waves with calligraphy painted on a door', ru: 'Лодка и волны с каллиграфией на двери' } },
  { file: 'painted-door.webp', thumb: 'painted-door-t.webp', w: 854, h: 1280, cat: 'culture', title: { fa: 'در نقاشی‌شده با خوشنویسی', en: 'A door painted with calligraphy', ru: 'Дверь, расписанная каллиграфией' } },
  { file: 'jetty.webp', thumb: 'jetty-t.webp', w: 1800, h: 1200, cat: 'sea', title: { fa: 'موج‌شکن سنگی زیر آسمان ابری', en: 'A stone jetty under a stormy sky', ru: 'Каменный мол под грозовым небом' } },
  { file: 'old-man.webp', thumb: 'old-man-t.webp', w: 1350, h: 1800, cat: 'people', title: { fa: 'مردی نشسته کنار دریا', en: 'A man sitting by the sea', ru: 'Мужчина сидит у моря' } },
  { file: 'statue-boys.webp', thumb: 'statue-boys-t.webp', w: 1350, h: 1800, cat: 'village', title: { fa: 'مجسمهٔ دو نوجوان کنار دریا', en: 'Statue of two boys by the sea', ru: 'Статуя двух мальчиков у моря' } },
  { file: 'statue-sunset.webp', thumb: 'statue-sunset-t.webp', w: 1350, h: 1800, cat: 'village', title: { fa: 'مجسمه‌ها در غروب', en: 'The statues at sunset', ru: 'Статуи на закате' } },
  { file: 'hoop.webp', thumb: 'hoop-t.webp', w: 1800, h: 1350, cat: 'sea', title: { fa: 'حلقهٔ بسکتبال رو به دریا', en: 'A basketball hoop facing the sea', ru: 'Баскетбольное кольцо у моря' } },
  { file: 'flowers-arch.webp', thumb: 'flowers-arch-t.webp', w: 1350, h: 1800, cat: 'village', title: { fa: 'گل‌ها و سازه‌ای قوسی در روستا', en: 'Flowers and an arched structure in the village', ru: 'Цветы и арочное сооружение в деревне' } },
  { file: 'flame-tree.webp', thumb: 'flame-tree-t.webp', w: 1350, h: 1800, cat: 'village', title: { fa: 'درخت گل‌دار و پلکان', en: 'A flowering tree and a staircase', ru: 'Цветущее дерево и лестница' } },
  { file: 'tree-sea.webp', thumb: 'tree-sea-t.webp', w: 1350, h: 1800, cat: 'sea', title: { fa: 'سایهٔ درخت و دریا', en: 'Tree shade and the sea', ru: 'Тень дерева и море' } },
  { file: 'park-bench.webp', thumb: 'park-bench-t.webp', w: 1350, h: 1800, cat: 'village', title: { fa: 'نیمکتی زیر درختان کنار ساحل', en: 'A bench under the trees by the shore', ru: 'Скамейка под деревьями у берега' } },
  { file: 'rail-view.webp', thumb: 'rail-view-t.webp', w: 1350, h: 1800, cat: 'sea', title: { fa: 'نرده‌های کنار دریا و کشتی دور', en: 'A seaside railing and a distant ship', ru: 'Ограждение у моря и далёкое судно' } },
];

export const galleryUi: Record<
  Lang,
  { all: string; cats: Record<GalleryCat, string>; slot: string; soon: string; photoBy: string; close: string; prev: string; next: string; open: string }
> = {
  fa: {
    all: 'همه',
    cats: { sea: 'دریا و ساحل', village: 'روستا', people: 'مردم', food: 'خوراک', craft: 'صنایع دستی', culture: 'فرهنگ و آیین' },
    slot: 'جای عکس',
    soon: 'عکس‌های هلیله به‌زودی در این قاب‌ها قرار می‌گیرند.',
    photoBy: 'عکس',
    close: 'بستن',
    prev: 'قبلی',
    next: 'بعدی',
    open: 'بزرگ‌نمایی',
  },
  en: {
    all: 'All',
    cats: { sea: 'Sea and beach', village: 'Village', people: 'People', food: 'Food', craft: 'Handicrafts', culture: 'Culture and rituals' },
    slot: 'Photo slot',
    soon: 'Photos of Helileh will appear in these frames soon.',
    photoBy: 'Photo',
    close: 'Close',
    prev: 'Previous',
    next: 'Next',
    open: 'Enlarge',
  },
  ru: {
    all: 'Все',
    cats: { sea: 'Море и пляж', village: 'Деревня', people: 'Люди', food: 'Еда', craft: 'Ремёсла', culture: 'Культура и обряды' },
    slot: 'Место для фото',
    soon: 'Фотографии Хелиле скоро появятся в этих рамках.',
    photoBy: 'Фото',
    close: 'Закрыть',
    prev: 'Назад',
    next: 'Вперёд',
    open: 'Увеличить',
  },
};
applyOverrides('galleryItems', galleryItems);
applyOverrides('galleryUi', galleryUi);
