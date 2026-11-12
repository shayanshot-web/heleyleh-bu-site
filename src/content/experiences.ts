import type { Lang } from './types';
import { applyOverrides } from '@/lib/overrides';

/**
 * تجربه‌ها و آدم‌هایشان.
 * نکته: در سایت زنده، اگر در «پنل مدیریت ← تجربه‌ها» مورد منتشرشده‌ای باشد، همان نمایش داده می‌شود؛
 * این فایل نسخهٔ پیش‌فرض/پشتیبان است (وقتی پنل خالی است یا در دسترس نیست).
 * عکس هر نفر: فایل را در public/people/ بگذارید و نامش را در فیلد photo بنویسید (مثلاً 'sina-arash.jpg').
 */
export interface Person {
  name: Record<Lang, string>;
  phone?: string;
  photo?: string;
}

export const people: Record<string, Person> = {
  'abdolhossein-barkh': { name: { fa: 'عبدالحسین برخ', en: 'Abdolhossein Barkh', ru: 'Абдольхоссейн Барх' }, phone: '09179294164' },
  'ali-daryaeinejad': { name: { fa: 'علی دریایی‌نژاد', en: 'Ali Daryaeinejad', ru: 'Али Дарьяинеджад' }, phone: '09176695283' },
  'hossein-hasanebrahimi': { name: { fa: 'حسین حسن‌ابراهیمی', en: 'Hossein Hasanebrahimi', ru: 'Хосейн Хасан-Ибрахими' }, phone: '09177719411' },
  'heydar-bimaki': { name: { fa: 'حیدر بیمکی', en: 'Heydar Bimaki', ru: 'Хейдар Бимаки' }, phone: '09178737122' },
  'sina-arash': { name: { fa: 'سینا آرش', en: 'Sina Arash', ru: 'Сина Араш' }, phone: '09113814089', photo: 'sina-arash.webp' },
  'jafar-dorfeshan': { name: { fa: 'جعفر دورفشان', en: 'Jafar Dorfeshan', ru: 'Джафар Дурфешан' }, phone: '09171710056', photo: 'jafar-dorfeshan.webp' },
  'ali-khajeh': { name: { fa: 'علی خواجه', en: 'Ali Khajeh', ru: 'Али Хаджех' }, phone: '09171753730' },
  'amirreza-rahbar': { name: { fa: 'امیررضا رهبر', en: 'Amirreza Rahbar', ru: 'Амирреза Рахбар' }, phone: '09376037139', photo: 'amirreza-rahbar.webp' },
  'reza-mousavi': { name: { fa: 'سید رضا موسوی', en: 'Seyed Reza Mousavi', ru: 'Сейед Реза Мусави' }, phone: '09176008569', photo: 'reza-mousavi.webp' },
  'hossein-khezri': { name: { fa: 'حسین خضری', en: 'Hossein Khezri', ru: 'Хосейн Хезри' }, phone: '09357173166', photo: 'hossein-khezri.webp' },
  'mehdi-ansari': { name: { fa: 'مهدی انصاری', en: 'Mehdi Ansari', ru: 'Мехди Ансари' }, photo: 'mehdi-ansari.webp' },
};

export type ExpIconName =
  | 'boat' | 'fishing' | 'paddle' | 'beachtennis' | 'volleyball' | 'football'
  | 'claytennis' | 'museum' | 'oldtown' | 'stars' | 'sundial' | 'park';

export interface Experience {
  id: ExpIconName;
  name: Record<Lang, string>;
  /** شناسه‌ها در people */
  who: string[];
}

export const experiences: Experience[] = [
  { id: 'boat', name: { fa: 'قایق‌سواری ساحلی', en: 'Coastal boat ride', ru: 'Прогулка на лодке' }, who: ['abdolhossein-barkh', 'ali-daryaeinejad'] },
  { id: 'fishing', name: { fa: 'ماهی‌گیری', en: 'Fishing', ru: 'Рыбалка' }, who: ['hossein-hasanebrahimi', 'heydar-bimaki'] },
  { id: 'paddle', name: { fa: 'پدل‌بوردینگ', en: 'Paddle boarding', ru: 'Падл-бординг' }, who: ['sina-arash'] },
  { id: 'beachtennis', name: { fa: 'تنیس ساحلی', en: 'Beach tennis', ru: 'Пляжный теннис' }, who: ['sina-arash'] },
  { id: 'volleyball', name: { fa: 'والیبال ساحلی', en: 'Beach volleyball', ru: 'Пляжный волейбол' }, who: ['jafar-dorfeshan'] },
  { id: 'football', name: { fa: 'فوتبال ساحلی', en: 'Beach football', ru: 'Пляжный футбол' }, who: ['ali-khajeh'] },
  { id: 'claytennis', name: { fa: 'تنیس خاکی', en: 'Clay-court tennis', ru: 'Теннис на грунте' }, who: ['amirreza-rahbar'] },
  { id: 'museum', name: { fa: 'موزه نجوم و دریانوردی', en: 'Astronomy & Seafaring Museum', ru: 'Музей астрономии и мореходства' }, who: ['reza-mousavi'] },
  { id: 'oldtown', name: { fa: 'بافت‌گردی', en: 'Old-quarter walk', ru: 'Прогулка по старому кварталу' }, who: ['reza-mousavi'] },
  { id: 'stars', name: { fa: 'تور نجوم', en: 'Astronomy tour', ru: 'Астрономический тур' }, who: ['hossein-khezri'] },
  { id: 'sundial', name: { fa: 'ساعت‌های آفتابی', en: 'Sundials', ru: 'Солнечные часы' }, who: ['hossein-khezri'] },
  { id: 'park', name: { fa: 'پارک ساحلی', en: 'Beach park', ru: 'Пляжный парк' }, who: [] },
];

export const expUi: Record<Lang, { coordinator: string; photoSoon: string; call: string; noPerson: string; teaserTitle: string; teaserCta: string; peopleTitle: string }> = {
  fa: { coordinator: 'هماهنگی با', photoSoon: 'عکس به‌زودی', call: 'تماس', noPerson: 'آزاد؛ بدون نیاز به هماهنگی', teaserTitle: 'تجربه‌ها و آدم‌هایش', teaserCta: 'همهٔ تجربه‌ها', peopleTitle: 'آدم‌های هلیله' },
  en: { coordinator: 'Contact', photoSoon: 'Photo soon', call: 'Call', noPerson: 'Open to everyone', teaserTitle: 'Experiences and the people behind them', teaserCta: 'All experiences', peopleTitle: 'People of Helileh' },
  ru: { coordinator: 'Контакт', photoSoon: 'Фото скоро', call: 'Позвонить', noPerson: 'Свободный доступ', teaserTitle: 'Впечатления и люди', teaserCta: 'Все впечатления', peopleTitle: 'Люди Хелиле' },
};
applyOverrides('people', people);
applyOverrides('experiences', experiences);
applyOverrides('expUi', expUi);
