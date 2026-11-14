import type { Lang, PostId } from './types';
import { applyOverrides } from '@/lib/overrides';

/** نوع نقطه روی نقشه */
export type MapKind = 'village' | 'ancient' | 'fun' | 'culture' | 'hub';

export interface MapPoint {
  id: string;
  kind: MapKind;
  /** مختصات؛ اگر ندارد نقطه روی نقشه نمی‌افتد و فقط در فهرست با فاصلهٔ km می‌آید */
  lat?: number;
  lon?: number;
  /** فاصلهٔ ثابت (کیلومتر) برای نقطه‌های بدون مختصات */
  km?: number;
  /** نقطهٔ محلی (داخل و کنار روستا) یا نقطهٔ منطقه‌ای (بوشهر، بندر، نیروگاه، دلوار، ...) */
  scope: 'local' | 'region';
  /** مطلب مرتبط سایت (در پنجرهٔ نشانگر و فهرست پیوند می‌شود) */
  postId?: PostId;
  name: Record<Lang, string>;
}

/**
 * مرکز روستای هلیله (نقطهٔ ثبت‌شده در OpenStreetMap/ویکی‌پدیا).
 * اگر مرکز روستا جای دیگری است، فقط همین مختصات را عوض کنید؛ فاصله‌ها خودکار حساب می‌شوند.
 */
export const HELILEH = { lat: 28.836227, lon: 50.882821 };

export const mapPoints: MapPoint[] = [
  /* نقاط جذاب و گردشگری در خود هلیله */
  { id: 'cemetery', kind: 'ancient', scope: 'local', lat: 28.839093, lon: 50.8795601, postId: 'ancient-cemetery', name: { fa: 'گورستان باستانی هلیله', en: 'Helileh ancient cemetery', ru: 'Древнее кладбище Хелиле' } },
  { id: 'theater', kind: 'culture', scope: 'local', lat: 28.8404247, lon: 50.8724992, name: { fa: 'تئاتر ساحلی ایرج صغیری', en: 'Iraj Soghayri beach theater', ru: 'Пляжный театр Ираджа Согейри' } },
  { id: 'park', kind: 'fun', scope: 'local', lat: 28.838083, lon: 50.8746528, name: { fa: 'پارک هلیله', en: 'Helileh park', ru: 'Парк Хелиле' } },
  { id: 'beach', kind: 'fun', scope: 'local', lat: 28.8367361, lon: 50.8745014, name: { fa: 'ساحل هلیله', en: 'Helileh beach', ru: 'Пляж Хелиле' } },
  { id: 'garden', kind: 'fun', scope: 'local', lat: 28.8412819, lon: 50.8752786, name: { fa: 'باغ شمالی هلیله', en: 'Helileh north garden', ru: 'Северный сад Хелиле' } },
  { id: 'aftab', kind: 'fun', scope: 'local', lat: 28.830852, lon: 50.8791282, name: { fa: 'مجموعه ساحلی آفتاب', en: 'Aftab beach complex', ru: 'Пляжный комплекс Афтаб' } },
  /* اطراف هلیله */
  { id: 'kalut', kind: 'culture', scope: 'local', lat: 28.8652375, lon: 50.8638906, name: { fa: 'کلوت بوستان لیان', en: 'Lian kalut formations', ru: 'Калуты Лиан' } },
  { id: 'masileh', kind: 'fun', scope: 'local', lat: 28.8757505, lon: 50.8915325, name: { fa: 'مسیله (شوره‌زار) هلیله', en: 'Helileh salt marsh', ru: 'Солончак Хелиле' } },
  { id: 'lian', kind: 'fun', scope: 'local', lat: 28.8607513, lon: 50.8534535, name: { fa: 'پارک ساحلی لیان', en: 'Lian beach park', ru: 'Парк Лиан' } },
  /* نقاط بدون مختصات: فقط فاصله (کیلومتر) در فهرست */
  { id: 'bandargah-beach', kind: 'fun', scope: 'region', km: 3, name: { fa: 'ساحل بندرگاه', en: 'Bandargah beach', ru: 'Пляж Бандаргах' } },
  { id: 'riding-stable', kind: 'fun', scope: 'region', km: 3, name: { fa: 'اسطبل اسب‌سواری', en: 'Horse-riding stable', ru: 'Конюшня для верховой езды' } },
  { id: 'bushehr-mall', kind: 'hub', scope: 'region', km: 24, name: { fa: 'مجتمع تجاری و تفریحی بوشهر مال', en: 'Bushehr Mall (shopping and leisure complex)', ru: 'Торгово-развлекательный центр «Бушер Мол»' } },
  { id: 'delvar-shops', kind: 'hub', scope: 'region', km: 20, name: { fa: 'مراکز خرید ته‌لنجی دلوار', en: 'Delvar tah-lenji shopping centers', ru: 'Торговые центры тах-ленджи в Дельваре' } },
  /* منطقه: شهر، بندر، نیروگاه، دلوار، گورستان باستانی */
  { id: 'npp', kind: 'hub', scope: 'region', lat: 28.8297793, lon: 50.8859682, name: { fa: 'نیروگاه اتمی بوشهر', en: 'Bushehr nuclear plant', ru: 'АЭС «Бушер»' } },
  { id: 'rishehr', kind: 'fun', scope: 'region', lat: 28.8975036, lon: 50.8303717, name: { fa: 'ساحل ریشهر', en: 'Rishehr beach', ru: 'Пляж Ришехр' } },
  { id: 'shoghab', kind: 'ancient', scope: 'region', lat: 28.9164457, lon: 50.8138356, name: { fa: 'گورستان باستانی شغاب', en: 'Shoghab ancient cemetery', ru: 'Древний некрополь Шогаб' } },
  { id: 'port', kind: 'hub', scope: 'region', lat: 28.9704447, lon: 50.8573387, name: { fa: 'بندر بوشهر', en: 'Bushehr port', ru: 'Порт Бушера' } },
  { id: 'bushehr', kind: 'hub', scope: 'region', lat: 28.9861673, lon: 50.8318456, name: { fa: 'شهر بوشهر (بازار)', en: 'Bushehr city (bazaar)', ru: 'Бушер (базар)' } },
  { id: 'delvar', kind: 'hub', scope: 'region', lat: 28.76139, lon: 51.07, name: { fa: 'دلوار', en: 'Delvar', ru: 'Дельвар' } },
];

export interface MapStrings {
  title: string;
  lead: string;
  note: string;
  center: string;
  km: string;
  kinds: Record<MapKind, string>;
  openGoogle: string;
  aria: string;
  listTitle: string;
  viewVillage: string;
  viewRegion: string;
  distanceFrom: string;
  tilesNote: string;
  readMore: string;
}

export const mapStrings: Record<Lang, MapStrings> = {
  fa: {
    title: 'نقشه هلیله و جاذبه‌های آن',
    lead: 'مرکز روستای هلیله و نقاط جذاب و گردشگری آن، همراه با فاصله تا بوشهر، بندر، نیروگاه و دلوار.',
    note: 'فاصله‌ها به‌صورت خط مستقیم و تقریبی‌اند؛ مسیر جاده‌ای کمی بیشتر است.',
    center: 'روستای هلیله (مرکز)',
    km: 'کیلومتر',
    kinds: { village: 'مرکز روستا', ancient: 'گورستان باستانی', fun: 'ساحل و تفریحی', culture: 'فرهنگی و دیدنی', hub: 'شهر، بندر، نیروگاه، دلوار' },
    openGoogle: 'باز کردن در گوگل‌مپ',
    aria: 'نقشه هلیله با علامت مرکز روستا و نقاط گردشگری',
    listTitle: 'فاصله از مرکز هلیله',
    viewVillage: 'نمای روستا',
    viewRegion: 'نمای منطقه',
    distanceFrom: 'از مرکز هلیله',
    tilesNote: 'نقشه: © مشارکت‌کنندگان OpenStreetMap',
    readMore: 'مطالعهٔ مطلب',
  },
  en: {
    title: 'Map of Helileh and its attractions',
    lead: 'The center of Helileh village and its sights and leisure spots, with distances to Bushehr, the port, the power plant and Delvar.',
    note: 'Distances are approximate straight-line values; by road they are somewhat longer.',
    center: 'Helileh village (center)',
    km: 'km',
    kinds: { village: 'Village center', ancient: 'Ancient cemetery', fun: 'Beaches and leisure', culture: 'Culture and sights', hub: 'City, port, plant, Delvar' },
    openGoogle: 'Open in Google Maps',
    aria: 'Map of Helileh marking the village center and tourist spots',
    listTitle: 'Distance from Helileh center',
    viewVillage: 'Village view',
    viewRegion: 'Region view',
    distanceFrom: 'from Helileh center',
    tilesNote: 'Map: © OpenStreetMap contributors',
    readMore: 'Read the article',
  },
  ru: {
    title: 'Карта Хелиле и его достопримечательностей',
    lead: 'Центр деревни Хелиле, её достопримечательности и места отдыха, а также расстояния до Бушера, порта, АЭС и Дельвара.',
    note: 'Расстояния приблизительные, по прямой; по дороге путь немного длиннее.',
    center: 'Деревня Хелиле (центр)',
    km: 'км',
    kinds: { village: 'Центр деревни', ancient: 'Древний некрополь', fun: 'Пляжи и отдых', culture: 'Культура и места', hub: 'Город, порт, АЭС, Дельвар' },
    openGoogle: 'Открыть в Google Maps',
    aria: 'Карта Хелиле с центром деревни и туристическими местами',
    listTitle: 'Расстояние от центра Хелиле',
    viewVillage: 'Вид деревни',
    viewRegion: 'Вид района',
    distanceFrom: 'от центра Хелиле',
    tilesNote: 'Карта: © участники OpenStreetMap',
    readMore: 'Читать статью',
  },
};
applyOverrides('mapPoints', mapPoints);
applyOverrides('mapStrings', mapStrings);
