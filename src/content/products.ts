import type { Lang } from './types';
import { applyOverrides } from '@/lib/overrides';

/**
 * محصولات صنایع دستی.
 * وقتی عکس واقعی آماده شد: فایل را در public/products/ بگذارید و نامش را در photo بنویسید؛
 * تا آن زمان تصویر گرافیکی نمایش داده می‌شود.
 */
export type ProductArtName = 'lenj' | 'mat' | 'shell';

export interface Product {
  id: string;
  art: ProductArtName;
  name: Record<Lang, string>;
  photo?: string;
}

export const products: Product[] = [
  { id: 'lenj', art: 'lenj', name: { fa: 'ماکت لنج چوبی', en: 'Wooden lenj (dhow) model', ru: 'Деревянная модель лендж (дау)' } },
  { id: 'mat', art: 'mat', name: { fa: 'حصیر و کپو', en: 'Reed mats and kapoo', ru: 'Циновки и капу' } },
  { id: 'shell', art: 'shell', name: { fa: 'تابلوهای صدفی', en: 'Seashell pictures', ru: 'Картины из ракушек' } },
];

export const productUi: Record<Lang, { title: string; ask: string; note: string }> = {
  fa: { title: 'محصولات', ask: 'استعلام قیمت و سفارش', note: 'تصویر گرافیکی است؛ عکس واقعی محصول به‌زودی اضافه می‌شود.' },
  en: { title: 'Products', ask: 'Ask for price and order', note: 'Illustration only; real product photos are coming soon.' },
  ru: { title: 'Товары', ask: 'Узнать цену и заказать', note: 'Это иллюстрация; реальные фото скоро появятся.' },
};
applyOverrides('products', products);
applyOverrides('productUi', productUi);
