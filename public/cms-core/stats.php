<?php
/* آمار بازدید خودمیزبان: بدون سرویس بیرونی و بدون ذخیرهٔ آی‌پی.
   دیتابیس جدا (stats.sqlite) تا نوشتن آمار با دیتابیس محتوا قفل نشود. */
declare(strict_types=1);

const STATS_TZ_OFFSET = 12600;      // تهران UTC+3:30 (بدون ساعت تابستانی)
const STATS_KEEP_DAYS = 400;

function stats_db(): PDO {
    static $p = null;
    if ($p) return $p;
    if (!is_dir(CMS_DATA)) @mkdir(CMS_DATA, 0750, true);
    $ht = CMS_DATA . '/.htaccess';
    if (!is_file($ht)) @file_put_contents($ht, "Require all denied\n");
    $p = new PDO('sqlite:' . CMS_DATA . '/stats.sqlite');
    $p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $p->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $p->exec('PRAGMA journal_mode=WAL; PRAGMA busy_timeout=3000; PRAGMA synchronous=NORMAL;');
    $p->exec("
      CREATE TABLE IF NOT EXISTS hits(
        id INTEGER PRIMARY KEY, hid TEXT UNIQUE, ts INTEGER NOT NULL, last INTEGER NOT NULL,
        vid TEXT NOT NULL, sid TEXT NOT NULL, path TEXT NOT NULL, lang TEXT, blang TEXT,
        ref_host TEXT, ref_url TEXT, ref_type TEXT, utm_s TEXT, utm_m TEXT, utm_c TEXT,
        dev TEXT, os TEXT, browser TEXT, brand TEXT, country TEXT, region TEXT, city TEXT,
        scr INTEGER, tz TEXT, dur INTEGER NOT NULL DEFAULT 0, entry INTEGER NOT NULL DEFAULT 0, newv INTEGER NOT NULL DEFAULT 0);
      CREATE INDEX IF NOT EXISTS h_ts ON hits(ts);
      CREATE INDEX IF NOT EXISTS h_sid ON hits(sid);
      CREATE INDEX IF NOT EXISTS h_vid ON hits(vid);
      CREATE TABLE IF NOT EXISTS geo_cache(h TEXT PRIMARY KEY, country TEXT, region TEXT, city TEXT, ts INTEGER NOT NULL);
      CREATE TABLE IF NOT EXISTS rl(h TEXT NOT NULL, ts INTEGER NOT NULL);
      CREATE INDEX IF NOT EXISTS rl_h ON rl(h, ts);");
    return $p;
}

/* ---------- تشخیص دستگاه / سیستم‌عامل / مرورگر ---------- */
function stats_is_bot(string $ua): bool {
    return $ua === '' || (bool)preg_match('/bot|crawl|spider|slurp|headless|lighthouse|pagespeed|preview|fetch|monitor|uptime|curl|wget|python|java\/|go-http|httpclient|scrapy|facebookexternalhit|whatsapp\/|telegrambot|ahrefs|semrush|mj12|dataprovider/i', $ua);
}
function stats_parse_ua(string $ua): array {
    $os = 'سایر'; $brand = 'سایر'; $browser = 'سایر'; $dev = 'desktop';
    if (preg_match('/iPhone|iPod/i', $ua)) { $dev = 'mobile'; $brand = 'Apple'; $os = 'iOS'; if (preg_match('/OS (\d+)[_\d]*/', $ua, $m)) $os = 'iOS ' . $m[1]; }
    elseif (preg_match('/iPad/i', $ua) || (stripos($ua, 'Macintosh') !== false && stripos($ua, 'Mobile') !== false)) { $dev = 'tablet'; $brand = 'Apple'; $os = 'iPadOS'; }
    elseif (stripos($ua, 'Android') !== false) {
        $dev = stripos($ua, 'Mobile') !== false ? 'mobile' : 'tablet'; $os = 'Android';
        if (preg_match('/Android (\d+)/', $ua, $m)) $os = 'Android ' . $m[1];
        $brand = match (true) {
            (bool)preg_match('/SM-|Samsung|GT-I/i', $ua) => 'Samsung',
            (bool)preg_match('/Redmi|Xiaomi|POCO|\bMi \d|M\d{4}[A-Z]\d|2\d{6}[A-Z]{1,2}\b/i', $ua) => 'Xiaomi',
            (bool)preg_match('/HONOR/i', $ua) => 'Honor',
            (bool)preg_match('/Huawei|HUAWEI|\b[A-Z]{3}-L\d\d\b|\b[A-Z]{3}-AL\d\d\b/', $ua) => 'Huawei',
            (bool)preg_match('/OPPO|CPH\d{4}|Realme|RMX\d{4}/i', $ua) => 'Oppo/Realme',
            (bool)preg_match('/OnePlus|IN20|LE2\d/i', $ua) => 'OnePlus',
            (bool)preg_match('/Pixel/i', $ua) => 'Google Pixel',
            (bool)preg_match('/Nokia/i', $ua) => 'Nokia',
            (bool)preg_match('/vivo/i', $ua) => 'Vivo',
            (bool)preg_match('/TECNO|Infinix|itel/i', $ua) => 'Transsion',
            default => 'سایر اندرویدی',
        };
    }
    elseif (stripos($ua, 'Windows') !== false) { $os = 'Windows'; if (preg_match('/Windows NT (\d+\.\d+)/', $ua, $m)) $os = ['10.0' => 'Windows 10/11', '6.3' => 'Windows 8.1', '6.2' => 'Windows 8', '6.1' => 'Windows 7'][$m[1]] ?? 'Windows'; }
    elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'Macintosh') !== false) { $os = 'macOS'; $brand = 'Apple'; }
    elseif (stripos($ua, 'CrOS') !== false) { $os = 'ChromeOS'; }
    elseif (stripos($ua, 'Linux') !== false || stripos($ua, 'X11') !== false) { $os = 'Linux'; }
    $browser = match (true) {
        stripos($ua, 'Instagram') !== false => 'Instagram (درون‌برنامه)',
        (bool)preg_match('/FBAN|FBAV/', $ua) => 'Facebook (درون‌برنامه)',
        stripos($ua, 'SamsungBrowser') !== false => 'Samsung Internet',
        (bool)preg_match('/Edg(e|A|iOS)?\//', $ua) => 'Edge',
        (bool)preg_match('/OPR\/|Opera/', $ua) => 'Opera',
        (bool)preg_match('/UCBrowser|UCWEB/', $ua) => 'UC Browser',
        stripos($ua, 'YaBrowser') !== false => 'Yandex Browser',
        stripos($ua, 'Vivaldi') !== false => 'Vivaldi',
        (bool)preg_match('/Firefox|FxiOS/', $ua) => 'Firefox',
        (bool)preg_match('/Chrome|CriOS/', $ua) => 'Chrome',
        stripos($ua, 'Safari') !== false => 'Safari',
        default => 'سایر',
    };
    if ($os === 'macOS' && $brand === 'سایر') $brand = 'Apple';
    if ($brand === 'سایر' && $dev === 'desktop') $brand = 'رایانه';
    return ['dev' => $dev, 'os' => $os, 'browser' => $browser, 'brand' => $brand];
}

/* ---------- منبع ورود ---------- */
function stats_ref_class(string $host, string $url): array {
    $host = strtolower(preg_replace('/^www\./', '', $host));
    if ($host === '' ) return ['direct', ''];
    $h = $host;
    if (str_starts_with($h, 'android-app') || preg_match('/^com\./', $h)) {
        if (str_contains($h, 'googlequicksearchbox')) return ['search', 'google.com'];
        if (str_contains($h, 'instagram')) return ['social', 'instagram.com'];
        if (str_contains($h, 'telegram')) return ['social', 'telegram.org'];
        if (str_contains($h, 'whatsapp')) return ['social', 'whatsapp.com'];
        return ['other', $h];
    }
    $search = ['google.', 'bing.com', 'yandex.', 'duckduckgo.com', 'yahoo.', 'baidu.com', 'ecosia.org', 'ask.com', 'search.brave.com', 'startpage.com', 'neshan.org', 'parsijoo.ir', 'sokan.ir', 'seznam.cz', 'naver.com'];
    foreach ($search as $s) if (str_contains($h, $s)) return ['search', $h];
    $social = ['instagram.com', 'l.instagram.com', 'facebook.com', 'fb.com', 'm.facebook.com', 't.me', 'telegram.', 'web.telegram.org', 'whatsapp.com', 'wa.me', 'twitter.com', 't.co', 'x.com', 'linkedin.com', 'lnkd.in', 'pinterest.', 'youtube.com', 'youtu.be', 'aparat.com', 'eitaa.com', 'rubika.ir', 'bale.ai', 'ble.ir', 'soroush', 'reddit.com', 'vk.com', 'ok.ru', 'tiktok.com', 'threads.net'];
    foreach ($social as $s) if (str_contains($h, $s)) return ['social', $h];
    if (preg_match('/mail\.|outlook\.|webmail/', $h)) return ['email', $h];
    return ['site', $h];
}

/* ---------- موقعیت (کشور/شهر) ---------- */
function stats_http_json(string $url): ?array {
    $ctx = stream_context_create(['http' => ['timeout' => 2.0, 'ignore_errors' => true, 'header' => "User-Agent: heleyleh-stats\r\nAccept: application/json\r\n"], 'ssl' => ['verify_peer' => true]]);
    $r = @file_get_contents($url, false, $ctx);
    if (!is_string($r) || $r === '') return null;
    $j = json_decode($r, true);
    return is_array($j) ? $j : null;
}
/** اولویت: هدرهای Cloudflare (اگر سایت پشت آن باشد) ← سرویس‌های رایگان آی‌پی (ipwho.is، ipapi.co). نتیجه برای هر آی‌پی ۳۰ روز کش می‌شود. */
function stats_geo(string $ip): array {
    $none = ['country' => '', 'region' => '', 'city' => ''];
    $cf = strtoupper((string)($_SERVER['HTTP_CF_IPCOUNTRY'] ?? ''));
    if (preg_match('/^[A-Z]{2}$/', $cf) && $cf !== 'XX' && $cf !== 'T1') {
        return ['country' => $cf, 'region' => (string)($_SERVER['HTTP_CF_REGION'] ?? ''), 'city' => (string)($_SERVER['HTTP_CF_IPCITY'] ?? '')];
    }
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return $none;
    $db = stats_db();
    $h = hash('sha256', $ip . app_secret());
    $s = $db->prepare('SELECT country,region,city,ts FROM geo_cache WHERE h=?'); $s->execute([$h]);
    $c = $s->fetch();
    if ($c && ($c['country'] !== '' ? $c['ts'] > time() - 30 * 86400 : $c['ts'] > time() - 3600)) return ['country' => $c['country'], 'region' => $c['region'], 'city' => $c['city']];
    $g = $none;
    $j = stats_http_json('https://ipwho.is/' . rawurlencode($ip) . '?fields=success,country_code,region,city');
    if ($j && ($j['success'] ?? false) && !empty($j['country_code'])) $g = ['country' => strtoupper((string)$j['country_code']), 'region' => (string)($j['region'] ?? ''), 'city' => (string)($j['city'] ?? '')];
    else {
        $j = stats_http_json('https://ipapi.co/' . rawurlencode($ip) . '/json/');
        if ($j && !empty($j['country_code'])) $g = ['country' => strtoupper((string)$j['country_code']), 'region' => (string)($j['region'] ?? ''), 'city' => (string)($j['city'] ?? '')];
    }
    $db->prepare('INSERT OR REPLACE INTO geo_cache(h,country,region,city,ts) VALUES(?,?,?,?,?)')->execute([$h, $g['country'], mb_substr($g['region'], 0, 60), mb_substr($g['city'], 0, 60), time()]);
    return $g;
}

/* ---------- دریافت رویداد از مرورگر ---------- */
function stats_clean_path(string $p): ?string {
    $p = strtok($p, '?#') ?: '';
    $p = rawurldecode($p);
    if ($p === '' || $p[0] !== '/' || mb_strlen($p) > 200 || preg_match('/[\x00-\x1f]/', $p)) return null;
    if (preg_match('#^/(admin|cms-core|uploads|_next)(/|$)#', $p)) return null;
    $p = preg_replace('#/+#', '/', $p);
    if ($p !== '/' && !str_contains(basename($p), '.')) $p = rtrim($p, '/') . '/';
    return $p;
}
function stats_collect(array $in): void {
    $db = stats_db(); $now = time();
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (stats_is_bot($ua)) return;
    $ip = client_ip();
    $ih = hash('sha256', $ip . app_secret());
    $db->prepare('DELETE FROM rl WHERE ts<?')->execute([$now - 120]);
    $q = $db->prepare('SELECT COUNT(*) FROM rl WHERE h=?'); $q->execute([$ih]);
    if ((int)$q->fetchColumn() >= 90) return;
    $db->prepare('INSERT INTO rl(h,ts) VALUES(?,?)')->execute([$ih, $now]);

    $hid = (string)($in['h'] ?? '');
    if (!preg_match('/^[a-f0-9]{8,32}$/', $hid)) return;
    $act = (string)($in['a'] ?? 'pv');
    if ($act === 'd') {
        $d = max(0, min(600, (int)($in['d'] ?? 0)));
        $db->prepare('UPDATE hits SET dur=MIN(dur+?,1800), last=? WHERE hid=?')->execute([$d, $now, $hid]);
        return;
    }
    $vid = (string)($in['v'] ?? ''); $sid = (string)($in['s'] ?? '');
    if (!preg_match('/^[a-f0-9]{8,32}$/', $vid) || !preg_match('/^[a-f0-9]{8,32}$/', $sid)) return;
    $path = stats_clean_path((string)($in['p'] ?? '')); if ($path === null) return;
    $lang = preg_match('#^/(en|ru)/#', $path, $m) ? $m[1] : 'fa';
    $ref = (string)($in['r'] ?? ''); $refHost = ''; $refUrl = '';
    if ($ref !== '' && preg_match('~^(https?|android-app)://([^/\s?#]+)~i', $ref, $m)) {
        $refHost = strtolower($m[2]);
        $refUrl = mb_substr(preg_replace('/[?#].*$/', '', $ref), 0, 250);
    }
    if ($refHost !== '' && preg_replace('/^www\./', '', $refHost) === SITE_HOST) { $refHost = ''; $refUrl = ''; }
    [$refType, $refHost2] = stats_ref_class($refHost, $refUrl);
    $utm = is_array($in['u'] ?? null) ? $in['u'] : [];
    $us = mb_substr((string)($utm['s'] ?? ''), 0, 60); $um = mb_substr((string)($utm['m'] ?? ''), 0, 60); $uc = mb_substr((string)($utm['c'] ?? ''), 0, 80);
    if ($us !== '' && $refType === 'direct') $refType = 'campaign';
    $entry = !empty($in['e']) ? 1 : 0;
    // موقعیت: فقط برای اولین بازدیدِ نشست محاسبه می‌شود؛ بقیه از همان نشست کپی می‌شود
    $g = null;
    $s = $db->prepare('SELECT country,region,city FROM hits WHERE sid=? LIMIT 1'); $s->execute([$sid]);
    $row = $s->fetch();
    if ($row) $g = $row;
    else { $g = stats_geo($ip); }
    $p = stats_parse_ua($ua);
    $bl = strtolower(substr((string)($in['l'] ?? ''), 0, 2)); if (!preg_match('/^[a-z]{2}$/', $bl)) $bl = '';
    $scr = max(0, min(10000, (int)($in['w'] ?? 0)));
    $tz = preg_match('#^[A-Za-z_/+-]{1,40}$#', (string)($in['z'] ?? '')) ? (string)$in['z'] : '';
    $db->prepare('INSERT OR IGNORE INTO hits(hid,ts,last,vid,sid,path,lang,blang,ref_host,ref_url,ref_type,utm_s,utm_m,utm_c,dev,os,browser,brand,country,region,city,scr,tz,entry,newv)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
       ->execute([$hid, $now, $now, $vid, $sid, $path, $lang, $bl, $refHost2 !== '' ? $refHost2 : $refHost, $refUrl, $refType, $us, $um, $uc, $p['dev'], $p['os'], $p['browser'], $p['brand'], $g['country'], $g['region'], $g['city'], $scr, $tz, $entry, !empty($in['n']) ? 1 : 0]);
    if (random_int(1, 200) === 1) {
        $db->prepare('DELETE FROM hits WHERE ts<?')->execute([$now - STATS_KEEP_DAYS * 86400]);
        $db->prepare('DELETE FROM geo_cache WHERE ts<?')->execute([$now - 40 * 86400]);
    }
}
