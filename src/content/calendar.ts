import type { Lang } from './types';
import { applyOverrides } from '@/lib/overrides';

/* تقویم هلیله: بادهای محلی و جشن‌های ایران باستان (تاریخ‌ها به تقویم خورشیدی/جلالی) */

export type CalKind = 'ancient';

interface Tx {
  title: string;
  desc: string;
}

export interface CalEntry {
  id: string;
  kind: CalKind;
  m: number; // ماه ۱ تا ۱۲
  d1: number;
  d2?: number;
  /** پایان بازه، وقتی بازه از ماه دیگری تمام می‌شود (ممکن است به سال بعد برسد) */

  /** تاریخ متحرک یا تقریبی که به‌صورت متن می‌آید */
  label?: Record<Lang, string>;
  /** کلید منبع‌ها در sources */

  t: Record<Lang, Tx>;
}

export const monthNames: Record<Lang, string[]> = {
  fa: ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'],
  en: ['Farvardin', 'Ordibehesht', 'Khordad', 'Tir', 'Mordad', 'Shahrivar', 'Mehr', 'Aban', 'Azar', 'Dey', 'Bahman', 'Esfand'],
  ru: ['Фарвардин', 'Ордибехешт', 'Хордад', 'Тир', 'Мордад', 'Шахривар', 'Мехр', 'Абан', 'Азар', 'Дей', 'Бахман', 'Эсфанд'],
};

export const gregMonths: Record<Lang, string[]> = {
  fa: ['ژانویه', 'فوریه', 'مارس', 'آوریل', 'مه', 'ژوئن', 'ژوئیه', 'اوت', 'سپتامبر', 'اکتبر', 'نوامبر', 'دسامبر'],
  en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  ru: ['янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'],
};

export const calUi: Record<
  Lang,
  {
    title: string;
    lead: string;
    tableTitle: string;
    colDate: string;
    colEvent: string;
    colAbout: string;
    kindAncient: string;
    seasonLabels: string[];
    note: string;
  }
> = {
  fa: {
    title: 'تقویم هلیله',
    lead: 'جشن‌های کهن ایرانی، ماه به ماه؛ برای برنامه‌ریزی سفر و دیدار از هلیله در روزهای خاص.',
    tableTitle: 'تقویم سال',
    colDate: 'تاریخ',
    colEvent: 'مناسبت',
    colAbout: 'دربارهٔ آن',
    kindAncient: 'ایران باستان',
    seasonLabels: ['بهار', 'تابستان', 'پاییز', 'زمستان'],
    note: 'تاریخ‌ها بر پایهٔ تقویم خورشیدی است و تاریخ میلادی تقریبی است.',
  },
  en: {
    title: 'Helileh calendar',
    lead: 'Ancient Iranian festivals, month by month: handy for planning a trip to Helileh on a special day.',
    tableTitle: 'The year at a glance',
    colDate: 'Date',
    colEvent: 'Occasion',
    colAbout: 'About',
    kindAncient: 'Ancient Iran',
    seasonLabels: ['Spring', 'Summer', 'Autumn', 'Winter'],
    note: 'Dates follow the Persian solar calendar; Gregorian dates are approximate.',
  },
  ru: {
    title: 'Календарь Хелилы',
    lead: 'Древние иранские праздники по месяцам: удобно для планирования поездки в Хелилу в особый день.',
    tableTitle: 'Год в таблице',
    colDate: 'Дата',
    colEvent: 'Событие',
    colAbout: 'О чём',
    kindAncient: 'Древний Иран',
    seasonLabels: ['Весна', 'Лето', 'Осень', 'Зима'],
    note: 'Даты даны по иранскому солнечному календарю; григорианские даты приблизительные.',
  },
};

export const entries: CalEntry[] = [
  {
    id: 'nowruz',
    kind: 'ancient',
    m: 1,
    d1: 1,
    t: {
      fa: { title: 'نوروز', desc: 'آغاز سال نو و نخستین روز بهار؛ کهن‌ترین جشن ایرانی، با سفرهٔ هفت‌سین و دید و بازدید.' },
      en: { title: 'Nowruz', desc: 'The new year and first day of spring: Iran’s oldest festival, with the haft-sin table and family visits.' },
      ru: { title: 'Новруз', desc: 'Новый год и первый день весны: древнейший иранский праздник со столом хафт-син и семейными визитами.' },
    },
  },
  {
    id: 'sizdah',
    kind: 'ancient',
    m: 1,
    d1: 13,
    t: {
      fa: { title: 'سیزده‌به‌در', desc: 'روز بیرون‌رفتن به دامن طبیعت؛ ساحل هلیله جای خوبی برای این روز است.' },
      en: { title: 'Sizdah Bedar', desc: 'The day to head out into nature at the end of Nowruz; Helileh’s shore is a lovely place for it.' },
      ru: { title: 'Сиздах-бедар', desc: 'День выезда на природу в конце Новруза; побережье Хелилы для этого прекрасно подходит.' },
    },
  },
  {
    id: 'farvardingan',
    kind: 'ancient',
    m: 1,
    d1: 19,
    t: {
      fa: { title: 'فروردینگان', desc: 'روز یادبود درگذشتگان و فروهرها؛ خاندان‌ها به آرامگاه‌ها می‌روند و یاد نیاکان را گرامی می‌دارند.' },
      en: { title: 'Farvardingan', desc: 'A day to remember the departed and ancestral spirits; families visit graves and honour their forebears.' },
      ru: { title: 'Фарвардинган', desc: 'День памяти ушедших и духов предков; семьи посещают могилы и чтят память предков.' },
    },
  },
  {
    id: 'ordibeheshtgan',
    kind: 'ancient',
    m: 2,
    d1: 3,
    t: {
      fa: { title: 'اردیبهشتگان', desc: 'جشن گل و روشنایی؛ ستایش اردیبهشت، فرشتهٔ راستی و نور، در اوج بهار.' },
      en: { title: 'Ordibeheshtgan', desc: 'A festival of flowers and light, honouring Ordibehesht, the spirit of truth, at the height of spring.' },
      ru: { title: 'Ордибехештган', desc: 'Праздник цветов и света в честь Ордибехешта, духа истины, в разгар весны.' },
    },
  },
  {
    id: 'khordadgan',
    kind: 'ancient',
    m: 3,
    d1: 6,
    t: {
      fa: { title: 'خردادگان', desc: 'جشن آب و رسایی؛ روزی برای قدردانی از آب و بارور شدن زمین.' },
      en: { title: 'Khordadgan', desc: 'A festival of water and perfection, giving thanks for water and the fertile earth.' },
      ru: { title: 'Хордадган', desc: 'Праздник воды и совершенства: благодарность воде и плодородной земле.' },
    },
  },
  {
    id: 'tirgan',
    kind: 'ancient',
    m: 4,
    d1: 13,
    t: {
      fa: { title: 'تیرگان', desc: 'جشن باران و آب‌پاشی در گرمای تابستان؛ به یاد تیر آرش. نزدیک‌ترین جشن باستانی به حال‌وهوای دریا.' },
      en: { title: 'Tirgan', desc: 'A summer festival of rain and splashing water, remembering Arash’s arrow: the ancient feast closest to the spirit of the sea.' },
      ru: { title: 'Тирган', desc: 'Летний праздник дождя и брызг воды в память о стреле Араша; древний праздник, ближе всего к духу моря.' },
    },
  },
  {
    id: 'mordadgan',
    kind: 'ancient',
    m: 5,
    d1: 7,
    t: {
      fa: { title: 'مردادگان (امردادگان)', desc: 'جشن گیاهان و بی‌مرگی؛ ستایش درخت و رویش و آبادانی.' },
      en: { title: 'Amordadgan', desc: 'A festival of plants and immortality, honouring trees, growth and a flourishing land.' },
      ru: { title: 'Амордадган', desc: 'Праздник растений и бессмертия: почитание деревьев, роста и процветания земли.' },
    },
  },
  {
    id: 'shahrivargan',
    kind: 'ancient',
    m: 6,
    d1: 4,
    t: {
      fa: { title: 'شهریورگان', desc: 'جشن شهریور، نمادِ شهریاری آرمانی و مهربانی؛ در برخی روایت‌ها به زادروز کوروش هم نسبت داده می‌شود.' },
      en: { title: 'Shahrivargan', desc: 'A festival of Shahrivar, symbol of just rule and kindness; some accounts link it to Cyrus’s birthday.' },
      ru: { title: 'Шахриварган', desc: 'Праздник Шахривара, символа справедливой власти и доброты; по некоторым рассказам связан с днём рождения Кира.' },
    },
  },
  {
    id: 'mehregan',
    kind: 'ancient',
    m: 7,
    d1: 16,
    t: {
      fa: { title: 'مهرگان', desc: 'جشن پاییزی دوستی و مهر و برداشت محصول؛ دومین جشن بزرگ ایرانیان پس از نوروز.' },
      en: { title: 'Mehregan', desc: 'The autumn festival of friendship, affection and harvest, Iran’s great feast after Nowruz.' },
      ru: { title: 'Мехреган', desc: 'Осенний праздник дружбы, любви и урожая: величайший иранский праздник после Новруза.' },
    },
  },
  {
    id: 'abangan',
    kind: 'ancient',
    m: 8,
    d1: 10,
    t: {
      fa: { title: 'آبان‌گان', desc: 'جشن آب و ستایش ناهید، بانوی آب‌ها؛ برای شهری کنار دریا، روزی ویژه است.' },
      en: { title: 'Abangan', desc: 'A festival of water, honouring Anahita, lady of the waters: a special day for a town on the sea.' },
      ru: { title: 'Абанган', desc: 'Праздник воды в честь Анахиты, владычицы вод: особый день для приморского города.' },
    },
  },
  {
    id: 'azargan',
    kind: 'ancient',
    m: 9,
    d1: 9,
    t: {
      fa: { title: 'آذرگان', desc: 'جشن آتش و گرما در آستانهٔ سرما؛ در این روز دور آتش جمع می‌شدند.' },
      en: { title: 'Azargan', desc: 'A festival of fire and warmth as the cold sets in; people gathered around the flames.' },
      ru: { title: 'Азарган', desc: 'Праздник огня и тепла с приходом холодов; люди собирались у костра.' },
    },
  },
  {
    id: 'yalda',
    kind: 'ancient',
    m: 9,
    d1: 30,
    t: {
      fa: { title: 'شب یلدا', desc: 'بلندترین شب سال و میلاد مهر؛ شبی با انار و هندوانه و حافظ‌خوانی تا بازگشت خورشید.' },
      en: { title: 'Yalda night', desc: 'The longest night of the year and the birth of light: pomegranates, watermelon and Hafez until the sun returns.' },
      ru: { title: 'Ночь Ялда', desc: 'Самая длинная ночь года и рождение света: гранаты, арбуз и стихи Хафиза до возвращения солнца.' },
    },
  },
  {
    id: 'deygan',
    kind: 'ancient',
    m: 10,
    d1: 1,
    t: {
      fa: { title: 'دی‌گان (خرم‌روز)', desc: 'نخستین روز دی و جشن آفریدگار؛ روزی برای بخشش و آشتی در دل زمستان.' },
      en: { title: 'Deygan (Khorram Ruz)', desc: 'The first day of Dey and a feast of the Creator: a day of forgiveness and reconciliation in midwinter.' },
      ru: { title: 'Дейган (Хоррам-руз)', desc: 'Первый день месяца Дей и праздник Творца: день прощения и примирения посреди зимы.' },
    },
  },
  {
    id: 'bahmangan',
    kind: 'ancient',
    m: 11,
    d1: 2,
    t: {
      fa: { title: 'بهمنگان', desc: 'جشن اندیشهٔ نیک و مهربانی با چهارپایان؛ در گذشته خوراکی بدون گوشت می‌پختند.' },
      en: { title: 'Bahmangan', desc: 'A feast of good thought and kindness to animals; traditionally a meat-free meal was prepared.' },
      ru: { title: 'Бахманган', desc: 'Праздник благой мысли и доброты к животным; по традиции готовили блюда без мяса.' },
    },
  },
  {
    id: 'sadeh',
    kind: 'ancient',
    m: 11,
    d1: 10,
    t: {
      fa: { title: 'سده', desc: 'جشن آتش، پنجاه روز پیش از نوروز؛ شکست سرمای زمستان با افروختن آتش بزرگ.' },
      en: { title: 'Sadeh', desc: 'The fire festival fifty days before Nowruz, defying midwinter cold with a great bonfire.' },
      ru: { title: 'Сада', desc: 'Праздник огня за пятьдесят дней до Новруза: большой костёр побеждает зимний холод.' },
    },
  },
  {
    id: 'badireh',
    kind: 'ancient',
    m: 11,
    d1: 22,
    t: {
      fa: { title: 'بادیره (روز باد)', desc: 'روز ستایش باد در تقویم کهن؛ پیوندی زیبا با بادهای دریایی هلیله.' },
      en: { title: 'Badireh (wind day)', desc: 'A day honouring the wind in the old calendar, a fitting link to the sea winds of Helileh.' },
      ru: { title: 'Бадире (день ветра)', desc: 'День почитания ветра в древнем календаре: прекрасная связь с морскими ветрами Хелилы.' },
    },
  },
  {
    id: 'sepandarmazgan',
    kind: 'ancient',
    m: 12,
    d1: 5,
    t: {
      fa: { title: 'سپندارمذگان (اسفندگان)', desc: 'جشن زمین و مهر مادران؛ روز گرامی‌داشت زنان و پیشکش هدیه به مادران.' },
      en: { title: 'Sepandarmazgan', desc: 'A feast of the earth and of mothers, honouring women with gifts for mothers.' },
      ru: { title: 'Сепандармазган', desc: 'Праздник земли и матерей: день почитания женщин и подарков матерям.' },
    },
  },
  {
    id: 'chaharshanbe',
    kind: 'ancient',
    m: 12,
    d1: 24,
    label: {
      fa: 'آخرین سه‌شنبه سال',
      en: 'Last Tuesday of the year',
      ru: 'Последний вторник года',
    },
    t: {
      fa: { title: 'چهارشنبه‌سوری', desc: 'شب پریدن از روی آتش و آغاز استقبال از نوروز؛ زردی من از تو، سرخی تو از من.' },
      en: { title: 'Chaharshanbe Suri', desc: 'The night of leaping over bonfires that welcomes Nowruz: “my yellow to you, your red to me.”' },
      ru: { title: 'Чахаршанбе-сури', desc: 'Ночь прыжков через костры, встречающая Новруз: «моя желтизна тебе, твой румянец мне».' },
    },
  },
];
applyOverrides('calUi', calUi);
applyOverrides('calEntries', entries);
applyOverrides('monthNames', monthNames);
