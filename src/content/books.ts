import type { Lang } from './types';

/**
 * کتابخانهٔ نشر هلیله (صفحهٔ «درباره هلیله»).
 *
 * چطور کتاب اضافه کنیم:
 *  1. یک آیتم به آرایهٔ `books` پایین اضافه کنید (نمونه در انتهای همین فایل، داخل کامنت است).
 *  2. عکس جلد (اختیاری) را در public/books/ بگذارید و فقط نام فایل را در `cover` بنویسید.
 *     اگر جلد نباشد، جلدی گرافیکی با رنگ `color` ساخته می‌شود.
 *  3. فقط فیلدهایی را پر کنید که دارید؛ هر چیزی که خالی باشد در صفحه نمایش داده نمی‌شود.
 *
 * تا وقتی `books` خالی است، کل بخش کتابخانه در سایت دیده نمی‌شود.
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
  // phone: '+98…',
  // instagram: '…',
  // website: 'https://…',
};

export const books: Book[] = [
  /* نمونه:
  {
    id: 'sample',
    title: { fa: 'عنوان کتاب' },
    author: { fa: 'نام نویسنده' },
    year: '۱۴۰۴',
    pages: '۲۴۰',
    size: 'رقعی',
    isbn: '978-…',
    price: '۲۵۰٬۰۰۰ تومان',
    genre: { fa: 'داستان' },
    summary: { fa: 'بند اول خلاصه.\n\nبند دوم خلاصه.' },
    cover: 'sample.webp',
    color: '#0a3e66',
    buy: [{ label: { fa: 'خرید از …' }, url: 'https://…' }],
  },
  */
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
  },
};
