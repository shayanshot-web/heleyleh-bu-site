import type { Lang } from './types';

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
  /** نام فایل جلد داخل public/books/ */
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
  // instagram: '…',
  // website: 'https://…',
};

export const books: Book[] = [
  {
    id: 'avaz-daneha',
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
    title: { fa: 'نمکو دمبو' },
    author: { fa: 'حمید آتشچی' },
    genre: { fa: 'داستان' },
    summary: { fa: 'داستانی در فضای جنوب ایران، به‌ویژه بوشهر، با گویش و جزئیات زندگی مردم همان منطقه. خانواده‌ای فقیر را دنبال می‌کند که پدرشان بیمار است و ورود مردی غریبه به خانه زندگی‌شان را عوض می‌کند. مرگ پدر، مراسم عزا و روابط محله در ادامه می‌آید. نویسنده تلخی و طنز را کنار هم می‌گذارد و به فقر، نابرابری و آیین‌های اجتماعی می‌پردازد.' },
    color: '#0a3e66',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/243365/%D9%86%D9%85%DA%A9%D9%88-%D8%AF%D9%85%D8%A8%D9%88' }],
  },
  {
    id: 'modir-sahne',
    title: { fa: 'مدیر صحنه در فیلم‌سازی' },
    author: { fa: 'احمد حیاوی' },
    genre: { fa: 'سینما و تئاتر' },
    summary: { fa: 'دربارهٔ کار و اهمیت مدیر صحنه در ساخت فیلم و نمایش. از ریشه‌های این حرفه در تئاتر یونان باستان تا امروز، و جایگاه آن در سینما و تئاتر ایران و شکل‌گیری انجمن‌های صنفی. مهارت‌های لازم، وظایف اصلی، کار تیمی، مدیریت بحران و نکته‌های کاربردی برای ورود به حرفه هم در کتاب آمده است. مطالب بر پایهٔ تجربهٔ شخصی نویسنده نوشته شده و برای دانشجویان، علاقه‌مندان و فعالان حرفه‌ای مناسب است.' },
    color: '#5b2a6b',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/243362/%D9%85%D8%AF%DB%8C%D8%B1-%D8%B5%D8%AD%D9%86%D9%87-%D8%AF%D8%B1-%D9%81%DB%8C%D9%84%D9%85-%D8%B3%D8%A7%D8%B2%DB%8C' }],
  },
  {
    id: 'shili-yu',
    title: { fa: 'شیلی یو، شناخت ماهیان خلیج فارس' },
    author: { fa: 'اکبر شایان‌خواه' },
    genre: { fa: 'ماهیان خلیج فارس' },
    summary: { fa: 'راهنمای شناخت ماهیان خلیج فارس. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#0f6b6b',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/225659/%D8%B4%DB%8C%D9%84%DB%8C-%DB%8C%D9%88%D8%8C-%D8%B4%D9%86%D8%A7%D8%AE%D8%AA-%D9%85%D8%A7%D9%87%DB%8C%D8%A7%D9%86-%D8%AE%D9%84%DB%8C%D8%AC-%D9%81%D8%A7%D8%B1%D8%B3' }],
  },
  {
    id: 'captain-football',
    title: { fa: 'کاپیتان تیم فوتبال ما' },
    author: { fa: 'نصرالله احمدی' },
    summary: { fa: 'توضیح این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#2f6b2f',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/217727/%DA%A9%D8%A7%D9%BE%DB%8C%D8%AA%D8%A7%D9%86-%D8%AA%DB%8C%D9%85-%D9%81%D9%88%D8%AA%D8%A8%D8%A7%D9%84-%D9%85%D8%A7' }],
  },
  {
    id: 'chand-dastan',
    title: { fa: 'چند داستان ساده از روزگار کسانی که نمی‌خندند' },
    author: { fa: 'محسن فردین' },
    genre: { fa: 'داستان کوتاه' },
    summary: { fa: 'مجموعه‌ای از داستان‌های کوتاه از نویسنده‌ای ایرانی که در آلمان زندگی می‌کند. داستان‌ها نگاهی تازه به مسائل انسان امروز دارند و با نثری ساده و روان نوشته شده‌اند. پیشنهاد برای علاقه‌مندان به داستان ایرانی.' },
    color: '#8a6a12',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/217288/%DA%86%D9%86%D8%AF-%D8%AF%D8%A7%D8%B3%D8%AA%D8%A7%D9%86-%D8%B3%D8%A7%D8%AF%D9%87-%D8%A7%D8%B2-%D8%B1%D9%88%D8%B2%DA%AF%D8%A7%D8%B1-%DA%A9%D8%B3%D8%A7%D9%86%DB%8C-%DA%A9%D9%87-%D9%86%D9%85%DB%8C-%D8%AE%D9%86%D8%AF%D9%86%D8%AF' }],
  },
  {
    id: 'ghale',
    title: { fa: 'قلعه' },
    author: { fa: 'داریوش محمدزاده‌فرد' },
    genre: { fa: 'رمان' },
    summary: { fa: 'دربارهٔ مردمانی که در گذشته در قلعهٔ برازجان زندگی می‌کردند. روزگار پرفراز و نشیب آن‌ها، عشق‌ها و دشمنی‌هایشان و زندگی دشوار اما پرمایه‌شان روایت می‌شود.' },
    color: '#8c2f2f',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/210970/%D9%82%D9%84%D8%B9%D9%87' }],
  },
  {
    id: 'harche-didi',
    title: { fa: 'هرچه دیدی هیچی مگو' },
    author: { fa: 'سید عبدالله بلادی' },
    summary: { fa: 'توضیح این کتاب به‌زودی اضافه می‌شود. برای اطلاعات بیشتر یا تهیه از ناشر با نشر هلیله تماس بگیرید.' },
    color: '#3a4a5e',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/202506/%D9%87%D8%B1%DA%86%D9%87-%D8%AF%DB%8C%D8%AF%DB%8C-%D9%87%DB%8C%DA%86%DB%8C-%D9%85%DA%AF%D9%88' }],
  },
  {
    id: 'herat-ajmer-1',
    title: { fa: 'از هرات تا اجمیر و طوس، جلد اول' },
    author: { fa: 'احمدغنی خسروی' },
    genre: { fa: 'ادبیات فارسی' },
    summary: { fa: 'جلد نخست از دو جلد، گزیده‌ای از پژوهش‌های دانشگاهی احمدغنی خسروی دربارهٔ زبان و ادبیات فارسی، با همکاری زینب یوسفی. مطالب به سه حوزهٔ تمدنی فارسی‌زبان می‌پردازد: افغانستان، ایران و شبه‌قارهٔ هند. منبعی برای پژوهشگران، دانشجویان و علاقه‌مندان این حوزه.' },
    color: '#1f5a46',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/195022/%D8%A7%D8%B2-%D9%87%D8%B1%D8%A7%D8%AA-%D8%AA%D8%A7-%D8%A7%D8%AC%D9%85%DB%8C%D8%B1-%D9%88-%D8%B7%D9%88%D8%B3-%D8%AC%D9%84%D8%AF-%D8%A7%D9%88%D9%84' }],
  },
  {
    id: 'herat-ajmer-2',
    title: { fa: 'از هرات تا اجمیر و طوس، جلد دوم' },
    author: { fa: 'احمدغنی خسروی' },
    genre: { fa: 'ادبیات فارسی' },
    summary: { fa: 'جلد دوم از دو جلد پژوهش‌های احمدغنی خسروی دربارهٔ زبان و ادبیات فارسی در افغانستان، ایران و شبه‌قارهٔ هند.' },
    color: '#2c4f7a',
    buy: [{ label: { fa: 'خرید و مطالعه در طاقچه', en: 'Buy on Taaghche', ru: 'Купить на Taaghche' }, url: 'https://taaghche.com/book/191293/' }],
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
}

export const bookUi: Record<Lang, BookUi> = {
  fa: {
    title: 'کتابخانهٔ نشر هلیله',
    lead: 'کتاب‌هایی که نشر هلیله به همت آقای مهدی انصاری منتشر کرده است. روی هر کتاب بزنید تا روی میز تحریر باز شود.',
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
  },
  en: {
    title: 'Helileh Publishing library',
    lead: 'Books published by Helileh Publishing, managed by Mehdi Ansari. Select a book to open it on the writing desk.',
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
  },
  ru: {
    title: 'Библиотека издательства «Хелиле»',
    lead: 'Книги издательства «Хелиле» под руководством Мехди Ансари. Выберите книгу, и она откроется на письменном столе.',
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
  },
};
