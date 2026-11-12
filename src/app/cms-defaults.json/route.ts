import { flattenRoots } from '@/lib/cmsRoots';

export const dynamic = 'force-static';

/** فهرست متن‌های پیش‌فرض سایت؛ پنل مدیریت از روی همین فایل فرم ویرایش می‌سازد */
export function GET() {
  return new Response(JSON.stringify(flattenRoots()), { headers: { 'Content-Type': 'application/json; charset=utf-8' } });
}
