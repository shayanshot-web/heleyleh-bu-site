import type { Metadata } from 'next';

/** آدرس آزمایشی قدیمی heleyleh-bu.ir/test به صفحهٔ اصلی می‌رود */
export const metadata: Metadata = {
  title: 'هلیله باستانی، بوشهر',
  robots: { index: false, follow: true },
  alternates: { canonical: '/' },
};

export default function TestHome() {
  return (
    <>
      <meta httpEquiv="refresh" content="0;url=/" />
      <p style={{ textAlign: 'center', padding: '3rem 1rem', fontFamily: 'sans-serif' }}>
        <a href="/">رفتن به صفحهٔ اصلی هلیله باستانی</a>
      </p>
    </>
  );
}
