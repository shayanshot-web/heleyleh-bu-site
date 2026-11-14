import type { Lang } from '@/content/types';
import Link from '@/components/A';
import { dicts, postPath } from '@/lib/i18n';
import { HELILEH, mapPoints, mapStrings } from '@/content/map';
import LeafletMap, { type PinData } from './LeafletMap';

const CORE_KM = 1.5;

function distKm(aLat: number, aLon: number, bLat: number, bLon: number) {
  const R = 6371;
  const rad = (d: number) => (d * Math.PI) / 180;
  const dLat = rad(bLat - aLat);
  const dLon = rad(bLon - aLon);
  const h = Math.sin(dLat / 2) ** 2 + Math.cos(rad(aLat)) * Math.cos(rad(bLat)) * Math.sin(dLon / 2) ** 2;
  return 2 * R * Math.asin(Math.sqrt(h));
}

export default function HelilehMap({ lang, locale, dir }: { lang: Lang; locale: string; dir: 'rtl' | 'ltr' }) {
  const s = mapStrings[lang];
  const nf = new Intl.NumberFormat(locale, { maximumFractionDigits: 0 });
  const nf1 = new Intl.NumberFormat(locale, { maximumFractionDigits: 1 });
  const km = (v: number) => (v < 10 ? nf1.format(Math.round(v * 10) / 10) : nf.format(Math.round(v)));

  // از نزدیک به دور؛ نقاط تا ۱٫۵ کیلومتر از مرکز «هستهٔ روستا» حساب می‌شوند و نمای روستا فقط آن‌ها را نشان می‌دهد
  const items = mapPoints
    .map((p) => ({ ...p, d: p.km ?? distKm(HELILEH.lat, HELILEH.lon, p.lat ?? HELILEH.lat, p.lon ?? HELILEH.lon) }))
    .sort((a, b) => a.d - b.d)
    .map((p, i) => ({ ...p, n: i + 1 }))
    .map((p) => {
      const post = p.postId ? dicts[lang].posts.find((x) => x.id === p.postId) : undefined;
      return { ...p, href: post ? postPath(lang, post) : undefined };
    });

  const pins: PinData[] = [
    {
      id: 'village',
      kind: 'village',
      lat: HELILEH.lat,
      lon: HELILEH.lon,
      label: s.center,
      mark: '★',
      popup: `<strong>${s.center}</strong>`,
      scope: 'local',
    },
    ...items.filter((p) => p.lat !== undefined && p.lon !== undefined).map((p) => ({
      id: p.id,
      kind: p.kind,
      lat: p.lat as number,
      lon: p.lon as number,
      label: p.name[lang],
      mark: nf.format(p.n),
      popup: `<strong>${p.name[lang]}</strong><br>${km(p.d)} ${s.km} ${s.distanceFrom}${p.href ? `<br><a href="${p.href}">${s.readMore}</a>` : ''}`,
      scope: (p.d <= CORE_KM ? 'local' : 'region') as 'local' | 'region',
    })),
  ];

  const kindsShown = ['village', 'fun', 'culture', 'ancient', 'hub'] as const;
  const gmaps = `https://www.google.com/maps/search/?api=1&query=${HELILEH.lat},${HELILEH.lon}`;

  return (
    <section className="helileh-map" aria-labelledby="helileh-map-title" lang={lang}>
      <div className="container">
        <h2 id="helileh-map-title">{s.title}</h2>
        <p className="hm-lead">{s.lead}</p>

        <div className="hm-card">
          <LeafletMap
            center={HELILEH}
            pins={pins}
            dir={dir}
            lang={lang}
            ariaLabel={s.aria}
            centerLabel={s.title}
            viewVillage={s.viewVillage}
            viewRegion={s.viewRegion}
            attribution={s.tilesNote}
          />

          <ul className="hm-legend">
            {kindsShown.map((k) => (
              <li key={k} className={`hm-${k}`}>
                <span className="hm-swatch" aria-hidden="true" />
                {s.kinds[k]}
              </li>
            ))}
          </ul>

          <ol className="hm-list" aria-label={s.listTitle}>
            <li className="hm-village">
              <span className="hm-badge-html" aria-hidden="true">
                ★
              </span>
              <span className="hm-list-name">{s.center}</span>
              <span className="hm-list-km" />
            </li>
            {items.map((p) => (
              <li key={p.id} className={`hm-${p.kind}`}>
                <span className="hm-badge-html" aria-hidden="true">
                  {nf.format(p.n)}
                </span>
                <span className="hm-list-name">
                  {p.href ? <Link href={p.href}>{p.name[lang]}</Link> : p.name[lang]}
                </span>
                <span className="hm-list-km">
                  {km(p.d)} {s.km}
                </span>
              </li>
            ))}
          </ol>
        </div>

        <p className="hm-note">{s.note}</p>
        <a className="hm-link" href={gmaps} target="_blank" rel="noopener noreferrer">
          {s.openGoogle}
        </a>
      </div>
    </section>
  );
}
