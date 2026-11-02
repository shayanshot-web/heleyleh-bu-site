import ExpIcon from '@/components/ExpIcon';
import { experiences, type ExpIconName } from '@/content/experiences';
import { toHtml } from '@/lib/jsx-html';

export const dynamic = 'force-static';

/**
 * خروجی استاتیک /xp-icons.json برای پنل مدیریت (PHP):
 * SVG دقیق هر آیکن متحرک تجربه‌ها + نام فارسی آن، تا کارت‌هایی که از پنل ساخته می‌شوند
 * با همان قالب و همان آیکن‌های سایت نمایش داده شوند.
 */
export function GET() {
  const out: Record<string, { svg: string; label: string }> = {};
  for (const e of experiences) {
    const id: ExpIconName = e.id;
    out[id] = { svg: toHtml(ExpIcon({ name: id })), label: e.name.fa };
  }
  return Response.json(out);
}
