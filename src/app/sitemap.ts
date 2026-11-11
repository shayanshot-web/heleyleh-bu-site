import type { MetadataRoute } from 'next';
import { MAINTENANCE, allRoutes, routePath, SITE_URL } from '@/lib/i18n';

export const dynamic = 'force-static';

export default function sitemap(): MetadataRoute.Sitemap {
  const now = new Date();
  return allRoutes()
    .filter((r) => !(MAINTENANCE && r.kind === 'home' && r.lang === 'fa'))
    .map((r) => ({
    url: `${SITE_URL}${routePath(r)}`,
    lastModified: r.kind === 'post' ? new Date(`${r.post.date}T00:00:00Z`) : now,
    changeFrequency: r.kind === 'post' ? 'monthly' : 'weekly',
    priority: r.kind === 'home' ? 1 : r.kind === 'post' ? 0.5 : 0.7,
  }));
}
