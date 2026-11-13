import type { ReactNode } from 'react';

/* =====================================================================
   گرافیک تم دریایی هلیله: آیکن‌ها، صحنه خلیج فارس و موج‌ها (همه SVG درون‌خطی)
   ===================================================================== */

export type IconName =
  | 'home'
  | 'about'
  | 'stay'
  | 'attractions'
  | 'restaurants'
  | 'experiences'
  | 'gallery'
  | 'blog'
  | 'library'
  | 'events'
  | 'news'
  | 'shop'
  | 'contact'
  | 'sea'
  | 'food'
  | 'craft'
  | 'culture'
  | 'phone'
  | 'user'
  | 'pin'
  | 'search';

/* هر آیکن روی شبکه ۴۸×۴۸ کشیده شده؛ کلاس f یعنی سطح پرشده با رنگ شنی */
const icons: Record<IconName, ReactNode> = {
  /* لنگر: خانه و بندر */
  home: (
    <>
      <g className="a-sail">
        <circle cx="24" cy="11" r="4" />
        <path d="M24 15v25M17 22h14" />
        <path className="f" d="M9 31c1 6 7 10 15 10s14-4 15-10l-5 3M9 31l5 3" />
      </g>
    </>
  ),
  /* بادگیر بوشهری */
  about: (
    <>
      <g className="a-tower">
        <path className="f" d="M17 41V19l7-10 7 10v22z" />
        <path d="M24 9v-3M21 25v10M27 25v10M21 19h6" />
      </g>
      <path d="M12 41h24" />
    </>
  ),
  /* خانه با سقف شیروانی و در قوسی */
  stay: (
    <>
      <path className="f" d="M10 24v16h28V24" />
      <path d="M5 25 24 9l19 16M20 40V31a4 4 0 0 1 8 0v9M8 40h32" />
    </>
  ),
  /* غروب خورشید روی دریا */
  attractions: (
    <>
      <g className="a-sun">
        <path className="f" d="M13 29a11 11 0 0 1 22 0z" />
        <path d="M24 10v4M10 16l3 3M38 16l-3 3" />
      </g>
      <path d="M5 29h38" />
      <path className="a-wave" d="M9 35c3-3 6-3 9 0s6 3 9 0 6-3 9 0M13 41c3-2 5-2 8 0s5 2 8 0 5-2 6 0" />
    </>
  ),
  /* ماهی روی ظرف */
  restaurants: (
    <>
      <g className="a-fish">
        <path className="f" d="M8 25c5-8 15-11 22-5l9-5v18l-9-5c-7 6-17 3-22-3z" />
        <circle cx="15" cy="23" r="1.4" fill="currentColor" stroke="none" />
        <path d="M22 21c1.5 2 1.5 5 0 8" />
      </g>
      <path d="M7 40h34" />
    </>
  ),
  /* قطب‌نما */
  experiences: (
    <>
      <circle className="f" cx="24" cy="24" r="16" />
      <path className="a-needle" d="M30 18l-4 9-9 3 4-9z" />
      <circle cx="24" cy="24" r="1.6" fill="currentColor" stroke="none" />
    </>
  ),
  /* قاب عکس با خورشید و موج */
  gallery: (
    <>
      <rect className="f" x="7" y="10" width="34" height="28" rx="4" />
      <circle className="a-sun" cx="31" cy="19" r="3.2" />
      <path className="a-wave" d="M7 32c4-4 7-4 11 0s7 4 11 0 8-3 12 0" />
    </>
  ),
  /* قلم و برگه */
  blog: (
    <>
      <path className="f" d="M12 8h18l8 8v24H12z" />
      <path d="M30 8v8h8M18 24h14M18 30h14M18 36h8" />
    </>
  ),
  /* قفسهٔ کتاب: کتابخانه */
  library: (
    <>
      <path d="M6 40h36" />
      <rect className="f" x="9" y="15" width="8" height="25" rx="1.5" />
      <path d="M11.5 21h3M11.5 34h3" />
      <rect className="f" x="19" y="9" width="9" height="31" rx="1.5" />
      <path d="M21.5 16h4M21.5 33h4" />
      <g className="a-lean">
        <rect className="f" x="30" y="13" width="8" height="27" rx="1.5" />
        <path d="M32.5 19h3" />
      </g>
    </>
  ),
  /* تقویم */
  events: (
    <>
      <rect className="f" x="8" y="12" width="32" height="28" rx="4" />
      <path d="M8 21h32M16 8v8M32 8v8M17 28h4M27 28h4M17 34h4" />
    </>
  ),
  /* روزنامه */
  news: (
    <>
      <path className="f" d="M10 10h24v28H12a4 4 0 0 1-4-4V12a2 2 0 0 1 2-2z" />
      <path d="M34 18h6v16a4 4 0 0 1-4 4M15 17h14M15 23h6M24 23h5M15 29h14M15 34h9" />
    </>
  ),
  /* کوزه سفالی */
  shop: (
    <>
      <path className="f" d="M18 10h12v4c8 2 11 9 9 15-2 7-8 11-15 11s-13-4-15-11c-2-6 1-13 9-15z" />
      <path d="M18 10h12M14 26h20M15 32h18" />
    </>
  ),
  contact: (
    <>
      <path className="f" d="M14 8h8l3 9-5 3a21 21 0 0 0 8 8l3-5 9 3v8a4 4 0 0 1-4 4C23 38 10 25 10 12a4 4 0 0 1 4-4z" />
    </>
  ),
  /* موج و بادبان */
  sea: (
    <>
      <g className="a-sail">
        <path className="f" d="M26 8v20h13z" />
        <path d="M24 12 12 28h12" />
      </g>
      <path className="a-wave" d="M8 34c4-3 7-3 10 0s6 3 10 0 7-3 12 0M12 41c3-2 5-2 8 0s5 2 8 0 5-2 8 0" />
    </>
  ),
  /* کاسه غذا با بخار */
  food: (
    <>
      <path className="f" d="M7 25h34a17 17 0 0 1-34 0z" />
      <path d="M14 41h20" />
      <path className="a-steam" d="M17 10c-2 3 2 5 0 8M24 8c-2 3 2 6 0 10M31 10c-2 3 2 5 0 8" />
    </>
  ),
  /* سفال */
  craft: (
    <>
      <path className="f" d="M18 10h12v4c8 2 11 9 9 15-2 7-8 11-15 11s-13-4-15-11c-2-6 1-13 9-15z" />
      <path d="M18 10h12M14 26h20M15 32h18" />
    </>
  ),
  /* تنبک */
  culture: (
    <>
      <path className="f" d="M10 10h28c0 5-5 7-5 12s7 7 7 16H8c0-9 7-11 7-16s-5-7-5-12z" />
      <path d="M10 10c0 3 28 3 28 0M12 38h24M20 22h8" />
    </>
  ),
  phone: (
    <>
      <path className="f" d="M14 8h8l3 9-5 3a21 21 0 0 0 8 8l3-5 9 3v8a4 4 0 0 1-4 4C23 38 10 25 10 12a4 4 0 0 1 4-4z" />
    </>
  ),
  user: (
    <>
      <circle className="f" cx="24" cy="16" r="8" />
      <path d="M8 40c1-9 8-13 16-13s15 4 16 13" />
    </>
  ),
  search: (
    <>
      <circle className="f" cx="21" cy="21" r="12" />
      <path d="M30 30l11 11" />
    </>
  ),
  pin: (
    <>
      <path className="f" d="M24 42S10 29 10 19a14 14 0 0 1 28 0c0 10-14 23-14 23z" />
      <circle cx="24" cy="19" r="4.5" />
    </>
  ),
};

export function Icon({ name, size = 48, className = '' }: { name: IconName; size?: number; className?: string }) {
  return (
    <svg
      className={`icon ${className}`.trim()}
      viewBox="0 0 48 48"
      width={size}
      height={size}
      fill="none"
      stroke="currentColor"
      strokeWidth="2.2"
      strokeLinecap="round"
      strokeLinejoin="round"
      data-icon={name}
      aria-hidden="true"
      focusable="false"
    >
      {icons[name]}
    </svg>
  );
}

/* ---------- موج‌های تکرارشونده (دوره‌ای تا انیمیشن بی‌درز باشد) ---------- */
const PERIOD = 360;

function wavePath(y: number, amp: number, bottom: number, width = 1440 + PERIOD * 2): string {
  const half = PERIOD / 2;
  const n = Math.ceil(width / half);
  let d = `M0 ${y} q ${half / 2} ${-amp} ${half} 0`;
  for (let i = 1; i < n; i++) d += ` t ${half} 0`;
  return `${d} V${bottom} H0 Z`;
}

/* ---------- نخل خرما ---------- */
function Palm({ x, y, s = 1, flip = false }: { x: number; y: number; s?: number; flip?: boolean }) {
  const fronds = [
    'M0 0c-18-14-40-14-58 2',
    'M0 0c-10-22-30-30-52-22',
    'M0 0c8-24 28-32 52-24',
    'M0 0c18-14 40-12 58 4',
    'M0 0c-6 12-24 22-42 20',
    'M0 0c8 12 26 20 42 16',
  ];
  return (
    <g transform={`translate(${x} ${y}) scale(${flip ? -s : s} ${s})`} className="palm">
      <g className="sway">
        <path d="M0 0c5-30 3-62 12-96" fill="none" stroke="#06304f" strokeWidth="6" strokeLinecap="round" />
        <g transform="translate(12 -96)" fill="none" stroke="#06304f" strokeWidth="4" strokeLinecap="round">
          {fronds.map((d) => (
            <path key={d} d={d} />
          ))}
        </g>
      </g>
    </g>
  );
}

/* ---------- لنج بادبانی ---------- */
function Dhow() {
  return (
    <g className="dhow">
      {/* ردّ کف پشت لنج */}
      <g className="wake" fill="none" stroke="#fff" strokeLinecap="round">
        <path d="M-100 14q-40 4-84 0" strokeWidth="4" opacity=".7" />
        <path d="M-96 22q-56 5-120 0" strokeWidth="3" opacity=".5" />
        <path d="M-92 30q-70 5-150 0" strokeWidth="2.5" opacity=".35" />
      </g>
      {/* بازتاب روی آب */}
      <ellipse cx="0" cy="10" rx="110" ry="5" fill="#06304f" opacity=".28" />
      <path d="M-60 16h120M-40 22h80" stroke="#ffe3b0" strokeWidth="2" strokeLinecap="round" opacity=".5" />
      {/* بدنه */}
      <path d="M-96 -14 -84 6Q0 18 80 6L100 -32 90 -32Q62 -8 -96 -14z" fill="#4a2e1c" />
      <path d="M-90 -6Q0 4 88 -14" fill="none" stroke="#d9c7a6" strokeWidth="2.2" />
      {/* دکل و بادبان‌ها */}
      <path d="M-4 -12V-150" stroke="#2b1a10" strokeWidth="3" strokeLinecap="round" />
      <g className="sail"><path d="M-78 -20 -2 -146 62 -20z" fill="#f6f0e4" /></g>
      <path d="M-58 -42H46M-44 -66H34" stroke="#d9c7a6" strokeWidth="3" strokeLinecap="round" />
      <path d="M68 -24 86 -84 98 -30z" fill="#d9c7a6" />
      <path d="M-2 -150 98 -34M-2 -150-96 -16" stroke="#2b1a10" strokeWidth="1.2" fill="none" opacity=".7" />
      <path className="flag" d="M-4 -150l16 4-16 6z" fill="#e0734a" />
    </g>
  );
}

/* ---------- صحنه اصلی: غروب خلیج فارس ---------- */
export function HeroScene() {
  return (
    <svg
      className="hero-scene"
      viewBox="0 0 1440 420"
      preserveAspectRatio="xMinYMax slice"
      aria-hidden="true"
      focusable="false"
    >
      <defs>
        <linearGradient id="hs-sea" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stopColor="#f0b777" />
          <stop offset=".08" stopColor="#3a8fc7" />
          <stop offset="1" stopColor="#0a4f86" />
        </linearGradient>
        <radialGradient id="hs-sun" cx=".5" cy=".5" r=".5">
          <stop offset="0" stopColor="#fff2cf" />
          <stop offset=".55" stopColor="#ffd58a" />
          <stop offset="1" stopColor="#ffb867" stopOpacity="0" />
        </radialGradient>
      </defs>

      {/* هاله و خورشید */}
      <circle className="sun-halo" cx="300" cy="196" r="190" fill="url(#hs-sun)" opacity=".55" />
      <circle className="sun-core" cx="300" cy="196" r="64" fill="#ffe2a3" />

      {/* ابرهای رونده */}
      <g className="cloud c1" fill="#fff" opacity=".55">
        <path d="M0 70c0-12 10-20 22-18 6-14 30-14 36 2 14-2 22 8 20 16z" />
      </g>
      <g className="cloud c2" fill="#fff" opacity=".4">
        <path d="M0 40c0-10 9-17 19-15 5-12 26-12 31 2 12-2 19 7 17 13z" />
      </g>
      <g className="cloud c3" fill="#ffe9c7" opacity=".5">
        <path d="M0 100c0-9 8-15 17-13 4-11 23-11 27 2 11-2 17 6 15 11z" />
      </g>

      {/* مرغ‌های دریایی */}
      <g className="bird b1" fill="none" stroke="#fff" strokeWidth="2.6" strokeLinecap="round" strokeLinejoin="round">
        <path className="wing" d="M0 0q9-9 18 0 9-9 18 0" />
      </g>
      <g className="bird b2" fill="none" stroke="#fff" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
        <path className="wing" d="M0 0q7-7 14 0 7-7 14 0" />
      </g>

      {/* ساحل دور: تپه‌های شنی و نخل‌ها */}
      <g>
        <path d="M0 196c60-26 130-30 210-8 60 16 110 14 160 4v32H0z" fill="#c9b48c" />
        <path className="scene-right" d="M1060 204c70-22 150-30 230-8 70 18 110 12 150-6v32H1060z" fill="#c9b48c" />
        <Palm x={64} y={190} s={1.15} />
        <Palm x={142} y={196} s={0.8} flip />
        <g className="scene-right">
          <Palm x={1330} y={196} s={1.1} />
          <Palm x={1398} y={200} s={0.8} flip />
        </g>
      </g>

      {/* دریا */}
      <rect x="0" y="196" width="1440" height="224" fill="url(#hs-sea)" />
      {/* نور خورشید روی آب */}
      <g stroke="#ffe3b0" strokeLinecap="round" opacity=".7">
        <path className="glint g1" d="M250 212h100" strokeWidth="3" />
        <path className="glint g2" d="M268 224h64" strokeWidth="3" />
        <path className="glint g3" d="M232 238h136" strokeWidth="2.5" />
        <path className="glint g4" d="M262 252h76" strokeWidth="2" />
      </g>

      {/* لنج */}
      <g transform="translate(520 232)">
        <g className="dhow-drift">
          <Dhow />
        </g>
      </g>

      {/* موج‌ها از دور به نزدیک */}
      <g className="wave w1">
        <path d={wavePath(262, 10, 420)} fill="#0b6aa8" opacity=".55" />
      </g>
      <g className="wave w2">
        <path d={wavePath(296, 14, 420)} fill="#0a5f9c" opacity=".8" />
      </g>
      <g className="wave w3">
        <path d={wavePath(334, 16, 420)} fill="#0a3e66" opacity=".9" />
      </g>
      {/* کف ساحل شنی و رسیدن به رنگ صفحه */}
      <g className="wave w4">
        <path d={wavePath(372, 14, 420)} fill="#d9c7a6" />
      </g>
      <g className="wave w5">
        <path d={wavePath(396, 12, 420)} fill="#fffdf8" />
      </g>
    </svg>
  );
}

/* ---------- لبه موجی بین بخش‌ها ---------- */
export function WaveEdge({ position = 'top' }: { position?: 'top' | 'bottom' }) {
  return (
    <svg
      className={`wave-edge ${position}`}
      viewBox="0 0 1200 40"
      preserveAspectRatio="none"
      aria-hidden="true"
      focusable="false"
    >
      <path d="M0 40V22C150 2 300 2 450 22s300 20 450 0 225-12 300-4v22z" />
    </svg>
  );
}

/* ---------- لوگوی سایت: بادبان روی موج ---------- */
export function Logo() {
  return (
    <svg className="logo" viewBox="0 0 40 40" width="40" height="40" aria-hidden="true" focusable="false">
      <circle cx="20" cy="20" r="20" fill="#0A3E66" />
      <circle cx="26" cy="12" r="4.2" fill="#D9C7A6" />
      <path d="M19 8 10 24h9z" fill="#F6F0E4" />
      <path d="M21 12v12h9z" fill="#D9C7A6" />
      <path d="M8 27c3.4-2.6 6.8-2.6 10.2 0s6.8 2.6 10.2 0 3.6-1.6 4.6-.8" fill="none" stroke="#fff" strokeWidth="2" strokeLinecap="round" />
      <path d="M8 33c3.4-2.6 6.8-2.6 10.2 0s6.8 2.6 10.2 0 3.6-1.6 4.6-.8" fill="none" stroke="#0A6FB5" strokeWidth="2" strokeLinecap="round" />
    </svg>
  );
}
