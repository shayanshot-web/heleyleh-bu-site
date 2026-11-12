import type { Lang } from './types';
import { applyOverrides } from '@/lib/overrides';

/**
 * شمارهٔ پیام‌رسان‌ها. اگر شناسهٔ (username) ایتا یا بله را بدهید، لینک با شناسه ساخته می‌شود
 * که مطمئن‌تر از لینک با شماره است. مثال: eitaaUser: 'helileh'
 */
export const MESSENGER_NUMBER = '09176443247';
export const MESSENGER_INTL = '989176443247';
export const eitaaUser = '';
export const baleUser = '';

export type MessengerId = 'whatsapp' | 'telegram' | 'eitaa' | 'bale';

export const messengers: { id: MessengerId; href: string; label: Record<Lang, string> }[] = [
  { id: 'whatsapp', href: `https://wa.me/${MESSENGER_INTL}`, label: { fa: 'واتساپ', en: 'WhatsApp', ru: 'WhatsApp' } },
  { id: 'telegram', href: `https://t.me/+${MESSENGER_INTL}`, label: { fa: 'تلگرام', en: 'Telegram', ru: 'Telegram' } },
  {
    id: 'eitaa',
    href: eitaaUser ? `https://eitaa.com/${eitaaUser}` : `https://eitaa.com/+${MESSENGER_INTL}`,
    label: { fa: 'ایتا', en: 'Eitaa', ru: 'Eitaa' },
  },
  {
    id: 'bale',
    href: baleUser ? `https://ble.ir/${baleUser}` : `https://ble.ir/+${MESSENGER_INTL}`,
    label: { fa: 'بله', en: 'Bale', ru: 'Bale' },
  },
];

export const messengerUi: Record<Lang, { title: string; hint: string; credit: string }> = {
  fa: { title: 'پیام در پیام‌رسان‌ها', hint: 'با یک کلیک در برنامه پیام بدهید', credit: 'طراحی و توسعه سایت: تیم وبسایتکو' },
  en: { title: 'Message us', hint: 'One tap opens the chat in the app', credit: 'Website design and development: WebSiteCo team' },
  ru: { title: 'Напишите нам', hint: 'Один клик — чат откроется в приложении', credit: 'Дизайн и разработка сайта: команда WebSiteCo' },
};
applyOverrides('messengerUi', messengerUi);
