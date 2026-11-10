'use client';

import Link from '@/components/A';
import { usePathname } from 'next/navigation';
import { useState } from 'react';
import { Icon, Logo, type IconName } from '@/components/Art';

export interface NavItem {
  label: string;
  href: string;
  icon: IconName;
  /** صدایی که با کلیک پخش می‌شود (مثلاً ورق زدن کتاب) */
  clickSound?: string;
}
export interface LangItem {
  code: string;
  name: string;
  href: string;
  active: boolean;
}

interface Props {
  siteName: string;
  homeHref: string;
  items: NavItem[];
  langs: LangItem[];
  menuLabel: string;
  langLabel: string;
  searchAction: string;
  searchLabel: string;
  searchPlaceholder: string;
}

export default function Header(p: Props) {
  const [open, setOpen] = useState(false);
  const pathname = usePathname() || '';
  const active = (href: string) => (href === p.homeHref ? pathname === href : pathname.startsWith(href));

  return (
    <header className="site-header">
      <div className="container header-row">
        <Link href={p.homeHref} className="brand" data-sound="sea" onClick={() => setOpen(false)}>
          <Logo />
          <span>{p.siteName}</span>
        </Link>

        <a className="header-search-link" href={p.searchAction} aria-label={p.searchLabel} data-sound="pop">
          <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
            <circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" strokeWidth="2" />
            <path d="M16 16l4.5 4.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
          </svg>
        </a>

        <button
          type="button"
          className="nav-toggle"
          aria-expanded={open}
          aria-controls="site-nav"
          onClick={() => setOpen((v) => !v)}
        >
          <span className="sr-only">{p.menuLabel}</span>
          <span className="bars" aria-hidden="true" />
        </button>

        <nav id="site-nav" className={open ? 'site-nav open' : 'site-nav'} aria-label={p.menuLabel}>
          <ul>
            {p.items.map((i) => (
              <li key={i.href}>
                <Link
                  href={i.href}
                  data-sound="tick"
                  data-click-sound={i.clickSound}
                  aria-current={active(i.href) ? 'page' : undefined}
                  onClick={() => setOpen(false)}
                >
                  <span className="nav-ico">
                    <Icon name={i.icon} size={26} className="nav-icon" />
                  </span>
                  <span className="nav-label">{i.label}</span>
                </Link>
              </li>
            ))}
          </ul>
          {/* فرم ساده GET (بدون نیاز به جاوااسکریپت)، تا در صفحه‌های پویای PHP هم کار کند */}
          <form className="header-search" action={p.searchAction} method="get" role="search">
            <label className="sr-only" htmlFor="header-q">
              {p.searchLabel}
            </label>
            <input
              id="header-q"
              type="search"
              name="q"
              placeholder={p.searchPlaceholder}
              maxLength={100}
              autoComplete="off"
            />
            <button type="submit" aria-label={p.searchLabel}>
              <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                <circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" strokeWidth="2" />
                <path d="M16 16l4.5 4.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
              </svg>
            </button>
          </form>
          <div className="lang-switch" role="group" aria-label={p.langLabel}>
            {p.langs.map((l) => (
              <Link
                key={l.code}
                data-sound="tick"
                href={l.href}
                hrefLang={l.code}
                lang={l.code}
                aria-current={l.active ? 'true' : undefined}
                onClick={() => setOpen(false)}
              >
                {l.name}
              </Link>
            ))}
          </div>
        </nav>
      </div>
    </header>
  );
}
