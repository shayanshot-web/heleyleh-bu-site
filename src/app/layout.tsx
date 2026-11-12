import type { Metadata, Viewport } from 'next';
import type { ReactNode } from 'react';
import '@fontsource-variable/vazirmatn';
import '@fontsource/lalezar';
import './globals.css';
import { SEARCH_VERIFY, SITE_URL } from '@/lib/i18n';

export const metadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  verification: {
    ...(SEARCH_VERIFY.google ? { google: SEARCH_VERIFY.google } : {}),
    ...(SEARCH_VERIFY.yandex ? { yandex: SEARCH_VERIFY.yandex } : {}),
    ...(SEARCH_VERIFY.bing ? { other: { 'msvalidate.01': SEARCH_VERIFY.bing } } : {}),
  },
};

export const viewport: Viewport = {
  themeColor: '#0A3E66',
  width: 'device-width',
  initialScale: 1,
};

export default function RootLayout({ children }: { children: ReactNode }) {
  // lang و dir دقیق هر صفحه را کامپوننت LangSync و wrapper داخل Shell تنظیم می‌کنند
  return (
    <html lang="fa" dir="rtl" suppressHydrationWarning>
      <body>
        {children}
        <script src="/t.js" defer />
      </body>
    </html>
  );
}
