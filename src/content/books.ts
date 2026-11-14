import type { Lang } from './types';
import { applyOverrides } from '@/lib/overrides';

/**
 * کتابخانهٔ نشر هلیله (صفحهٔ «وبلاگ»).
 *
 * چطور کتاب اضافه کنیم:
 *  1. یک آیتم به آرایهٔ `books` پایین اضافه کنید (نمونه در انتهای همین فایل، داخل کامنت است).
 *  2. عکس جلد (اختیاری) را در public/books/ بگذارید و فقط نام فایل را در `cover` بنویسید.
 *     اگر جلد نباشد، جلدی گرافیکی با رنگ `color` ساخته می‌شود.
 *  3. فقط فیلدهایی را پر کنید که دارید؛ هر چیزی که خالی باشد در صفحه نمایش داده نمی‌شود.
 *
 * تا وقتی `books` خالی است، قفسهٔ خالی با پیام «به‌زودی» نمایش داده می‌شود.
 * کتاب‌های فعلی از صفحهٔ ناشر در طاقچه (taaghche.com) گرفته شده؛ جلد گرافیکی با نام کتاب ساخته می‌شود.
 */

/** متن چندزبانه؛ فارسی لازم است و اگر انگلیسی/روسی نبود، فارسی نمایش داده می‌شود */
export type Loc<T = string> = { fa: T } & Partial<Record<'en' | 'ru', T>>;

export interface BuyLink {
  label: Loc;
  /** فقط آدرس https:// پذیرفته می‌شود */
  url: string;
}

export interface Book {
  id: string;
  title: Loc;
  author: Loc;
  translator?: Loc;
  /** سال انتشار، مثلاً '۱۴۰۴' */
  year?: string;
  pages?: string;
  /** قطع کتاب، مثلاً 'رقعی' */
  size?: string;
  isbn?: string;
  /** قیمت به همان شکلی که باید نمایش داده شود، مثلاً '۲۵۰٬۰۰۰ تومان' */
  price?: string;
  genre?: Loc;
  /** خلاصه؛ بندها را با یک خط خالی جدا کنید */
  summary: Loc;
  /** نام فایل جلد داخل public/books/ (یا آدرس /uploads/… که از پنل بارگذاری می‌شود). مقدار «_نام» یعنی هنوز عکسی نیست و جلد گرافیکی ساخته می‌شود */
  cover?: string;
  /** رنگ عطف و جلد گرافیکی، مثلاً '#0a3e66' */
  color?: string;
  /** لینک‌های خرید آنلاین */
  buy?: BuyLink[];
}

export interface Publisher {
  name: Loc;
  manager: Loc;
  /** شمارهٔ تماس با کد کشور، مثلاً '+989123456789' */
  phone?: string;
  /** شمارهٔ واتساپ با کد کشور و بدون + */
  whatsapp?: string;
  /** نام کاربری تلگرام بدون @ */
  telegram?: string;
  /** نام کاربری اینستاگرام بدون @ */
  instagram?: string;
  /** آدرس کامل سایت، با https:// */
  website?: string;
  email?: string;
  address?: Loc;
}

export const publisher: Publisher = {
  name: { fa: 'نشر هلیله', en: 'Helileh Publishing', ru: 'Издательство «Хелиле»' },
  manager: { fa: 'مهدی انصاری', en: 'Mehdi Ansari', ru: 'Мехди Ансари' },
  phone: '+989171731776',
  address: { fa: 'بوشهر – روستای گردشگری هلیله', en: 'Bushehr – Helileh tourist village', ru: 'Бушер – туристическая деревня Хелиле' },
  // instagram: '…',
  // website: 'https://…',
};

export const books: Book[] = [
  {
    id: 'avaz-daneha',
    cover: '_avaz-daneha',
    title: { fa: 'آواز دانه‌ها' },
    author: { fa: 'داریوش محمدزاده‌فرد' },
    isbn: '978-622-7358-97-1',
    genre: { fa: 'رمان' },
    summary: { fa: 'رمانی دربارهٔ زندگی غلام‌شاه، پیرمردی روستایی، و نازنین در روستایی خشک و فرسوده. غلام‌شاه روزهایش را با یک خر و گاوی نحیف در زمینی کم‌جان می‌گذراند و رمان از همان صفحه‌های نخست او را در این زندگی سخت دنبال می‌کند.' },
    color: '#7a3b1d',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/336166/%D8%A2%D9%88%D8%A7%D8%B2-%D8%AF%D8%A7%D9%86%D9%87-%D9%87%D8%A7' }],
  },
  {
    id: 'namko-dambo',
    cover: 'namko.webp',
    title: { fa: 'نمکو دمبو' },
    author: { fa: 'حمید آتشچی' },
    genre: { fa: 'رمان' },
    year: '۱۴۰۴',
    pages: '۵۸۴',
    isbn: '978-622-7358-72-8',
    summary: { fa: 'داستانی در فضای جنوب ایران، به‌ویژه بوشهر، با گویش و جزئیات زندگی مردم همان منطقه. خانواده‌ای فقیر را دنبال می‌کند که پدرشان بیمار است و ورود مردی غریبه به خانه زندگی‌شان را عوض می‌کند. مرگ پدر، مراسم عزا و روابط محله در ادامه می‌آید. نویسنده تلخی و طنز را کنار هم می‌گذارد و به فقر، نابرابری و آیین‌های اجتماعی می‌پردازد.' },
    color: '#0a3e66',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/243365/%D9%86%D9%85%DA%A9%D9%88-%D8%AF%D9%85%D8%A8%D9%88' }],
  },
  {
    id: 'modir-sahne',
    cover: '_modir-sahne',
    title: { fa: 'مدیر صحنه در فیلم‌سازی' },
    author: { fa: 'احمد حیاوی' },
    genre: { fa: 'سینما و تئاتر' },
    summary: { fa: 'دربارهٔ کار و اهمیت مدیر صحنه در ساخت فیلم و نمایش. از ریشه‌های این حرفه در تئاتر یونان باستان تا امروز، و جایگاه آن در سینما و تئاتر ایران و شکل‌گیری انجمن‌های صنفی. مهارت‌های لازم، وظایف اصلی، کار تیمی، مدیریت بحران و نکته‌های کاربردی برای ورود به حرفه هم در کتاب آمده است. مطالب بر پایهٔ تجربهٔ شخصی نویسنده نوشته شده و برای دانشجویان، علاقه‌مندان و فعالان حرفه‌ای مناسب است.' },
    color: '#5b2a6b',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/243362/%D9%85%D8%AF%DB%8C%D8%B1-%D8%B5%D8%AD%D9%86%D9%87-%D8%AF%D8%B1-%D9%81%DB%8C%D9%84%D9%85-%D8%B3%D8%A7%D8%B2%DB%8C' }],
  },
  {
    id: 'shili-yu',
    cover: '_shili-yu',
    title: { fa: 'شیلی یو، شناخت ماهیان خلیج فارس' },
    author: { fa: 'اکبر شایان‌خواه' },
    genre: { fa: 'ماهیان خلیج فارس' },
    summary: { fa: 'راهنمای شناخت ماهیان خلیج فارس. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#0f6b6b',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/225659/%D8%B4%DB%8C%D9%84%DB%8C-%DB%8C%D9%88%D8%8C-%D8%B4%D9%86%D8%A7%D8%AE%D8%AA-%D9%85%D8%A7%D9%87%DB%8C%D8%A7%D9%86-%D8%AE%D9%84%DB%8C%D8%AC-%D9%81%D8%A7%D8%B1%D8%B3' }],
  },
  {
    id: 'captain-football',
    cover: 'captain.webp',
    title: { fa: 'کاپیتان تیم فوتبال ما' },
    author: { fa: 'نصرالله احمدی' },
    genre: { fa: 'مجموعه داستان کوتاه' },
    summary: { fa: 'توضیح این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#2f6b2f',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/217727/%DA%A9%D8%A7%D9%BE%DB%8C%D8%AA%D8%A7%D9%86-%D8%AA%DB%8C%D9%85-%D9%81%D9%88%D8%AA%D8%A8%D8%A7%D9%84-%D9%85%D8%A7' }],
  },
  {
    id: 'chand-dastan',
    cover: '_chand-dastan',
    title: { fa: 'چند داستان ساده از روزگار کسانی که نمی‌خندند' },
    author: { fa: 'محسن فردین' },
    genre: { fa: 'داستان کوتاه' },
    summary: { fa: 'مجموعه‌ای از داستان‌های کوتاه از نویسنده‌ای ایرانی که در آلمان زندگی می‌کند. داستان‌ها نگاهی تازه به مسائل انسان امروز دارند و با نثری ساده و روان نوشته شده‌اند. پیشنهاد برای علاقه‌مندان به داستان ایرانی.' },
    color: '#8a6a12',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/217288/%DA%86%D9%86%D8%AF-%D8%AF%D8%A7%D8%B3%D8%AA%D8%A7%D9%86-%D8%B3%D8%A7%D8%AF%D9%87-%D8%A7%D8%B2-%D8%B1%D9%88%D8%B2%DA%AF%D8%A7%D8%B1-%DA%A9%D8%B3%D8%A7%D9%86%DB%8C-%DA%A9%D9%87-%D9%86%D9%85%DB%8C-%D8%AE%D9%86%D8%AF%D9%86%D8%AF' }],
  },
  {
    id: 'ghale',
    cover: '_ghale',
    title: { fa: 'قلعه' },
    author: { fa: 'داریوش محمدزاده‌فرد' },
    genre: { fa: 'رمان' },
    summary: { fa: 'دربارهٔ مردمانی که در گذشته در قلعهٔ برازجان زندگی می‌کردند. روزگار پرفراز و نشیب آن‌ها، عشق‌ها و دشمنی‌هایشان و زندگی دشوار اما پرمایه‌شان روایت می‌شود.' },
    color: '#8c2f2f',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/210970/%D9%82%D9%84%D8%B9%D9%87' }],
  },
  {
    id: 'harche-didi',
    cover: '_harche-didi',
    title: { fa: 'هرچه دیدی هیچی مگو' },
    author: { fa: 'سید عبدالله بلادی' },
    summary: { fa: 'توضیح این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#3a4a5e',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/202506/%D9%87%D8%B1%DA%86%D9%87-%D8%AF%DB%8C%D8%AF%DB%8C-%D9%87%DB%8C%DA%86%DB%8C-%D9%85%DA%AF%D9%88' }],
  },
  {
    id: 'herat-ajmer-1',
    cover: '_herat-ajmer-1',
    title: { fa: 'از هرات تا اجمیر و طوس، جلد اول' },
    author: { fa: 'احمدغنی خسروی' },
    genre: { fa: 'ادبیات فارسی' },
    summary: { fa: 'جلد نخست از دو جلد، گزیده‌ای از پژوهش‌های دانشگاهی احمدغنی خسروی دربارهٔ زبان و ادبیات فارسی، با همکاری زینب یوسفی. مطالب به سه حوزهٔ تمدنی فارسی‌زبان می‌پردازد: افغانستان، ایران و شبه‌قارهٔ هند. منبعی برای پژوهشگران، دانشجویان و علاقه‌مندان این حوزه.' },
    color: '#1f5a46',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/195022/%D8%A7%D8%B2-%D9%87%D8%B1%D8%A7%D8%AA-%D8%AA%D8%A7-%D8%A7%D8%AC%D9%85%DB%8C%D8%B1-%D9%88-%D8%B7%D9%88%D8%B3-%D8%AC%D9%84%D8%AF-%D8%A7%D9%88%D9%84' }],
  },
  {
    id: 'herat-ajmer-2',
    cover: '_herat-ajmer-2',
    title: { fa: 'از هرات تا اجمیر و طوس، جلد دوم' },
    author: { fa: 'احمدغنی خسروی' },
    genre: { fa: 'ادبیات فارسی' },
    summary: { fa: 'جلد دوم از دو جلد پژوهش‌های احمدغنی خسروی دربارهٔ زبان و ادبیات فارسی در افغانستان، ایران و شبه‌قارهٔ هند.' },
    color: '#2c4f7a',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/191293/' }],
  },
  {
    id: 'molitor',
    cover: 'molitor.webp',
    title: { fa: 'خاطرات لامبرت مولیتور در بوشهر (۱۹۰۲–۱۹۰۴)' },
    author: { fa: 'به کوشش یاسین محمدی' },
    genre: { fa: 'خاطرات' },
    summary: { fa: 'خاطرات لامبرت مولیتور از سال‌های ۱۹۰۲ تا ۱۹۰۴ میلادی در بوشهر؛ به کوشش یاسین محمدی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#1d9bdc',
  },
  {
    id: 'lanj-chashm',
    cover: 'lanj-chashm.webp',
    title: { fa: 'لنج در چشم‌هایت غرق می‌شود' },
    author: { fa: 'عبدالله علی‌پور' },
    genre: { fa: 'مجموعه شعر' },
    summary: { fa: 'مجموعه شعر عبدالله علی‌پور.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#b4531f',
  },
  {
    id: 'abadan-radio-naft',
    cover: 'abadan-radio-naft.webp',
    title: { fa: 'اینجا آبادان است، رادیو نفت…' },
    author: { fa: 'حسن مختارزاده' },
    genre: { fa: 'مجموعه داستان' },
    summary: { fa: 'مجموعه داستان حسن مختارزاده (چاپ دوم).\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#c0392b',
  },
  {
    id: 'bar-bal-khis',
    cover: 'bar-bal-khis.webp',
    title: { fa: 'بر بال خیس بادها' },
    author: { fa: 'اسماعیل بختیاری‌فرد' },
    genre: { fa: 'تاریخ محلی' },
    summary: { fa: 'گشت‌وگذاری در کوچه‌پس‌کوچه‌های دیروزِ تنگک زنگنه؛ پژوهش و نگارش اسماعیل بختیاری‌فرد.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#c98f3a',
  },
  {
    id: 'soraya',
    cover: 'soraya.webp',
    title: { fa: 'ثریّا' },
    author: { fa: 'حسین پورصفر' },
    summary: { fa: 'کتابی از حسین پورصفر.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#29a9e0',
  },
  {
    id: 'jan-gomshode',
    cover: 'jan-gomshode.webp',
    title: { fa: 'جانِ گم‌شده' },
    author: { fa: 'منصور قائدی' },
    genre: { fa: 'روانشناسی و روانکاوی' },
    summary: { fa: 'گزارش به روانشناسی و روانکاوی، نوشتهٔ منصور قائدی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#8f98c8',
  },
  {
    id: 'ahmadkhan-daryabeygi',
    cover: 'ahmadkhan-daryabeygi.webp',
    title: { fa: 'زندگی و زمانه: احمدخان دریابیگی' },
    author: { fa: 'علیرضا مظفری‌زاده' },
    genre: { fa: 'تاریخ' },
    summary: { fa: 'دربارهٔ زندگی و روزگار احمدخان دریابیگی، حکمران بوشهر و بنادر و جزایر خلیج فارس در اواخر دورهٔ قاجار؛ نوشتهٔ علیرضا مظفری‌زاده.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#7a1f2b',
  },
  {
    id: 'ai-accounting',
    cover: 'ai-accounting.webp',
    title: { fa: 'رقابت‌های هوش مصنوعی در حسابداری و تحلیل پیامدهای آن' },
    author: { fa: 'حبیب محمدپور، حجت قدری جوشاتی' },
    genre: { fa: 'حسابداری و هوش مصنوعی' },
    summary: { fa: 'کتابی دربارهٔ رقابت‌های هوش مصنوعی در حسابداری و پیامدهای آن، نوشتهٔ حبیب محمدپور و حجت قدری جوشاتی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#12396a',
  },
  {
    id: 'dilam',
    cover: 'dilam.webp',
    title: { fa: 'شناختنامهٔ شهرستان دیلم' },
    author: { fa: 'اله‌کرم لیراوی' },
    genre: { fa: 'شناخت‌نامه' },
    summary: { fa: 'شناختنامهٔ شهرستان دیلم، نوشتهٔ اله‌کرم لیراوی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#7fb0dc',
  },
  {
    id: 'bafeh',
    cover: 'bafeh.webp',
    title: { fa: 'بافِه: فرهنگ واژگان گویشی بَنَک' },
    author: { fa: 'عبدالله علی‌پور، جمیله محمدزاده' },
    genre: { fa: 'فرهنگ گویش' },
    isbn: '978-622-8999-91-3',
    summary: { fa: 'فرهنگ واژگان گویشی بنک تنها شرح لغت‌نامه‌ای چند واژه در یک محدودهٔ جغرافیایی نیست؛ گویش در دل روشن خود دنیایی از تاریخ و خاطرهٔ قومی و فضایی نوستالژیک دارد و ما را با آداب و رسوم، بازی‌های محلی، شیوه‌های درمانی قدیم و وسایل و ابزار زندگی در گذشته آشنا می‌کند.\n\nگویش بنیاد فرهنگی و یادگار اجداد و پیشینیان نیک‌اندیش ماست؛ باشد که در حفظ آن بکوشیم.' },
    color: '#0aa3a8',
  },
  {
    id: 'kafsh',
    cover: 'kafsh.webp',
    title: { fa: 'با کفش‌های شما راه نرفته‌ام' },
    author: { fa: 'ستاره بارگاهی' },
    genre: { fa: 'مجموعه داستان' },
    summary: { fa: 'این کتاب، تکه‌هایی از زندگی‌ست؛ از آدم‌هایی که جنگیدند، شکستند، برخاستند و ادامه دادند.\n\nشاید میان این قصه‌ها نگاهی تازه به زندگی پیدا کنید، یا خودتان را جایی میان سطرها دوباره بشناسید؛ چراکه هیچ‌کس با کفش‌های دیگری راه نرفته است.' },
    color: '#e9a94a',
  },
  {
    id: 'nirvana',
    cover: 'nirvana.webp',
    title: { fa: 'نیروانا' },
    author: { fa: 'حکیمه میرشکاری' },
    genre: { fa: 'داستان' },
    summary: { fa: 'شاید رسیدن، همیشه پایان یک عشق نباشد… شاید بعضی آدم‌ها وارد زندگی‌مان می‌شوند تا چیزی را به ما یاد بدهند، نه اینکه برای همیشه بمانند.\n\n«نیروانا» داستان دختری است که می‌آموزد بعضی عشق‌ها برای ماندن نیستند؛ برای فهمیدن‌اند.' },
    color: '#8fa83a',
  },
  {
    id: 'armaghan',
    cover: 'armaghan.webp',
    title: { fa: 'ارمغان آن سال‌ها' },
    author: { fa: 'جعفر درائی' },
    genre: { fa: 'خاطرات' },
    summary: { fa: 'خاطرات دوران معلمی جعفر درائی در مدرسهٔ راهنمایی عسلویه.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#b7482f',
  },
  {
    id: 'ziafat',
    cover: 'ziafat.webp',
    title: { fa: 'ضیافت رُمان' },
    author: { fa: 'حسین پورصفر' },
    genre: { fa: 'رمان' },
    isbn: '978-622-7358-29-2',
    summary: { fa: 'رهی، بعد از مریم فیروز، دیگر با هیچ زنی نه عهد عشق بست و نه عهد زناشویی. سرنوشت رهی و مریم فیروز شبیه سرنوشت مهرتاج رخشان و میرزادهٔ عشقی بود.\n\nرهی، پس از آگاهی از مرگی که ناگزیر به او نزدیک می‌شد، ارتباطش را با بیرون از خانه قطع کرد و سرانجام خودخواسته در خانهٔ جان سپرد. مهرتاج رخشان هم عاقبت به باغی در محلهٔ فرامهٔ دماوند پناه برد و در همان باغ چشم از جهان فروبست. شریف با خودش گفت: «این تراژدی تمامی ندارد؛ انگار تا همه را به زیر نمی‌کشید، قرار نبود به پایان برسد…»' },
    color: '#3f7d57',
  },
  {
    id: 'hayrani',
    cover: 'hayrani.webp',
    title: { fa: 'حیرانی ارتجالی: در مبادی و مبانی عرفان و تصوف' },
    author: { fa: 'مجاهد غلامی' },
    genre: { fa: 'عرفان و تصوف' },
    summary: { fa: 'کتابی در مبادی و مبانی عرفان و تصوف، نوشتهٔ مجاهد غلامی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#2a9bb5',
  },
  {
    id: 'labelay',
    cover: 'labelay.webp',
    title: { fa: 'لابه‌لای سنگ‌های خاکستان' },
    author: { fa: 'حسن مختارزاده' },
    genre: { fa: 'مجموعه داستان' },
    summary: { fa: 'مجموعه داستان حسن مختارزاده.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#c79a5c',
  },
  {
    id: 'kharg',
    cover: 'kharg.webp',
    title: { fa: 'تاریخ جزیرهٔ خارک' },
    author: { fa: 'سید حسن رضوی' },
    genre: { fa: 'تاریخ' },
    summary: { fa: 'تاریخ جزیرهٔ خارک؛ فراز و فرود خاندان زعابی، آل مذکور و حیات‌داودی. نوشتهٔ سید حسن رضوی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#2b9db8',
  },
  {
    id: 'akharin-moj',
    cover: 'akharin-moj.webp',
    title: { fa: 'آخرین موج' },
    author: { fa: 'ناخدا عبدالرسول غریبی' },
    summary: { fa: 'کتابی از ناخدا عبدالرسول غریبی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#2f8a8a',
  },
  {
    id: 'akharin-hakem',
    cover: 'akharin-hakem.webp',
    title: { fa: 'آخرین حاکم بوشهر و پایان یک عصر' },
    author: { fa: 'میثم رضائی' },
    genre: { fa: 'تاریخ' },
    summary: { fa: 'کتابی دربارهٔ آخرین حاکم بوشهر و پایان یک عصر، نوشتهٔ میثم رضائی.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#b8864a',
  },
  {
    id: 'manifest-omid',
    cover: 'manifest-omid.webp',
    title: { fa: 'مانیفست امید' },
    author: { fa: 'امید غضنفر' },
    genre: { fa: 'مجموعه شعر' },
    summary: { fa: 'مجموعه شعر امید غضنفر.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#5c7a4a',
  },
  {
    id: 'tayefe-roosa',
    cover: 'tayefe-roosa.webp',
    title: { fa: 'طایفهٔ رؤسا و کلانتران بلوک درازی دشتی' },
    author: { fa: 'دکتر حسن قلندری' },
    genre: { fa: 'تاریخ محلی' },
    summary: { fa: 'به روایت اسناد و تصاویر، با اسنادی از دوره‌های صفویه، زندیه، قاجاریه، پهلوی اول و دوم و جمهوری اسلامی؛ نوشتهٔ دکتر حسن قلندری.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#8c2f3a',
  },
  {
    id: 'fanoos-dovom',
    cover: 'fanoos-dovom.webp',
    title: { fa: 'فانوس دوم: از جنوب، داستان (دفتر دوم)' },
    author: { fa: 'به کوشش مهدی انصاری و مرضیه ترکی‌نژاد' },
    genre: { fa: 'مجموعه داستان' },
    summary: { fa: 'دفتر دوم از مجموعهٔ «فانوس» با داستان‌هایی از جنوب، به کوشش مهدی انصاری و مرضیه ترکی‌نژاد.\n\nتوضیح کامل‌تر این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#e8a21c',
  },
  {
    id: 'talakut',
    cover: 'talakut.webp',
    title: { fa: 'تالاکوت' },
    author: { fa: 'احمد دریانورد' },
    genre: { fa: 'داستان' },
    isbn: '978-622-7358-32-2',
    summary: { fa: '«فریاد زدم: کمک! کمک! صدایم در باد و باران و خروش رود گم شد. سرعت آب بیشتر شده بود. یهو دیدم جلوتر، چند تخته‌سنگ وسط رودخانه قرار دارد؛ همان سنگ‌هایی که تالاکوتی‌ها از روی آن‌ها به سمت دیگر رود می‌رفتند. باید خودم را به سنگ‌ها می‌آویختم.»\n\n(از پشت‌جلد کتاب)' },
    color: '#333333',
  },
];

export const pick = <T,>(v: Loc<T>, lang: Lang): T => (v[lang as 'en' | 'ru'] ?? v.fa) as T;

export interface BookUi {
  title: string;
  lead: string;
  shelfLabel: string;
  openHint: string;
  deskLabel: string;
  back: string;
  openBook: string;
  closeBook: string;
  by: string;
  translator: string;
  year: string;
  pages: string;
  size: string;
  isbn: string;
  price: string;
  genre: string;
  summary: string;
  order: string;
  orderNote: string;
  manager: string;
  call: string;
  whatsapp: string;
  telegram: string;
  instagram: string;
  website: string;
  email: string;
  address: string;
  buy: string;
  buyNote: string;
  empty: string;
  flipHint: string;
}

export const bookUi: Record<Lang, BookUi> = {
  fa: {
    title: 'کتابخانهٔ نشر هلیله',
    lead: 'کتاب‌هایی که نشر هلیله به همت آقای مهدی انصاری منتشر کرده است. روی هر کتاب بزنید تا روی میز تحریر بیاید؛ اول جلد را می‌بینید و با کلیک روی آن، کتاب ورق می‌خورد.',
    shelfLabel: 'قفسهٔ کتاب‌های نشر هلیله',
    openHint: 'باز کردن کتاب',
    deskLabel: 'میز تحریر',
    back: 'بازگشت به کتابخانه',
    openBook: 'باز کردن جلد',
    closeBook: 'بستن جلد',
    by: 'نویسنده',
    translator: 'مترجم',
    year: 'سال انتشار',
    pages: 'تعداد صفحه',
    size: 'قطع',
    isbn: 'شابک',
    price: 'قیمت',
    genre: 'موضوع',
    summary: 'خلاصه',
    order: 'سفارش از نشر هلیله',
    orderNote: 'برای سفارش مستقیم از ناشر با ما تماس بگیرید.',
    manager: 'مدیر نشر',
    call: 'تماس',
    whatsapp: 'واتساپ',
    telegram: 'تلگرام',
    instagram: 'اینستاگرام',
    website: 'وب‌سایت',
    email: 'ایمیل',
    address: 'نشانی',
    buy: 'خرید آنلاین',
    buyNote: 'پیوند فروشگاه‌ها در پنجرهٔ تازه باز می‌شود.',
    empty: 'کتاب‌های نشر هلیله به‌زودی روی این قفسه قرار می‌گیرند.',
    flipHint: 'برای ورق زدن روی جلد کلیک کنید',
  },
  en: {
    title: 'Helileh Publishing library',
    lead: 'Books published by Helileh Publishing, managed by Mehdi Ansari. Select a book to bring it to the writing desk; you see the cover first, and a click turns it open.',
    shelfLabel: 'Helileh Publishing bookshelf',
    openHint: 'Open book',
    deskLabel: 'Writing desk',
    back: 'Back to the library',
    openBook: 'Open the cover',
    closeBook: 'Close the cover',
    by: 'Author',
    translator: 'Translator',
    year: 'Year',
    pages: 'Pages',
    size: 'Format',
    isbn: 'ISBN',
    price: 'Price',
    genre: 'Subject',
    summary: 'Summary',
    order: 'Order from Helileh Publishing',
    orderNote: 'Contact the publisher to order directly.',
    manager: 'Publisher',
    call: 'Call',
    whatsapp: 'WhatsApp',
    telegram: 'Telegram',
    instagram: 'Instagram',
    website: 'Website',
    email: 'Email',
    address: 'Address',
    buy: 'Buy online',
    buyNote: 'Store links open in a new tab.',
    empty: 'Helileh Publishing books will be placed on this shelf soon.',
    flipHint: 'Click the cover to turn the page',
  },
  ru: {
    title: 'Библиотека издательства «Хелиле»',
    lead: 'Книги издательства «Хелиле» под руководством Мехди Ансари. Выберите книгу, и она ляжет на письменный стол: сначала вы увидите обложку, а щелчок перевернёт её.',
    shelfLabel: 'Книжная полка издательства «Хелиле»',
    openHint: 'Открыть книгу',
    deskLabel: 'Письменный стол',
    back: 'Вернуться к библиотеке',
    openBook: 'Открыть обложку',
    closeBook: 'Закрыть обложку',
    by: 'Автор',
    translator: 'Переводчик',
    year: 'Год',
    pages: 'Страниц',
    size: 'Формат',
    isbn: 'ISBN',
    price: 'Цена',
    genre: 'Тема',
    summary: 'Краткое содержание',
    order: 'Заказ в издательстве «Хелиле»',
    orderNote: 'Свяжитесь с издательством, чтобы заказать напрямую.',
    manager: 'Руководитель',
    call: 'Позвонить',
    whatsapp: 'WhatsApp',
    telegram: 'Telegram',
    instagram: 'Instagram',
    website: 'Сайт',
    email: 'Email',
    address: 'Адрес',
    buy: 'Купить онлайн',
    buyNote: 'Ссылки на магазины откроются в новой вкладке.',
    empty: 'Книги издательства «Хелиле» скоро появятся на этой полке.',
    flipHint: 'Нажмите на обложку, чтобы открыть книгу',
  },
};
applyOverrides('books', books);
applyOverrides('publisher', publisher);
applyOverrides('bookUi', bookUi);
