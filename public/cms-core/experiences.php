<?php
/* «تجربه‌ها و آدم‌هایش»: داده از پنل، نمایش با همان قالب و آیکن‌های سایت (کلاس‌های xp-*).
   دو نوع محتوا: person (مجری/هماهنگ‌کننده) و experience (تجربه + آیکن + فهرست مجری‌ها). */
declare(strict_types=1);

const XP_TYPES = ['experience', 'person'];

const XP_UI = [
    'fa' => ['coordinator' => 'هماهنگی با', 'photoSoon' => 'عکس به‌زودی', 'noPerson' => 'آزاد؛ بدون نیاز به هماهنگی'],
    'en' => ['coordinator' => 'Contact', 'photoSoon' => 'Photo soon', 'noPerson' => 'Open to everyone'],
    'ru' => ['coordinator' => 'Контакт', 'photoSoon' => 'Фото скоро', 'noPerson' => 'Свободный доступ'],
];

/** آیکن‌ها: فایل /xp-icons.json که هنگام ساخت سایت از همان کامپوننت React تولید می‌شود */
function xp_icons(): array {
    static $icons = null;
    if ($icons !== null) return $icons;
    $f = CMS_ROOT . '/xp-icons.json';
    $j = is_file($f) ? json_decode((string)file_get_contents($f), true) : null;
    return $icons = is_array($j) ? $j : [];
}

/** شمارهٔ تلفن را به شکل 09xxxxxxxxx (ارقام لاتین) درمی‌آورد؛ نامعتبر → null */
function xp_norm_phone(string $s): ?string {
    $s = strtr($s, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    $d = preg_replace('/\D+/', '', $s) ?? '';
    if (str_starts_with($d, '0098')) $d = '0' . substr($d, 4);
    elseif (str_starts_with($d, '98') && strlen($d) >= 12) $d = '0' . substr($d, 2);
    elseif ($d !== '' && $d[0] !== '0') $d = '0' . $d;
    return preg_match('/^0\d{9,10}$/', $d) ? $d : null;
}
function xp_tel(string $phone): string { return 'tel:+98' . substr($phone, 1); }

/** همهٔ موردهای یک نوع، با نام‌ها در سه زبان. $onlyPublished=false برای پنل */
function xp_load(string $type, bool $onlyPublished = true): array {
    $q = 'SELECT id, status, image, extra FROM items WHERE type=?' . ($onlyPublished ? " AND status='published'" : '') . ' ORDER BY id';
    $s = db()->prepare($q); $s->execute([$type]);
    $rows = [];
    foreach ($s->fetchAll() as $r) {
        $x = json_decode($r['extra'] ?: '{}', true) ?: [];
        $rows[(int)$r['id']] = ['id' => (int)$r['id'], 'status' => $r['status'], 'image' => $r['image'] ? (int)$r['image'] : null, 'x' => $x, 'names' => []];
    }
    if (!$rows) return [];
    $t = db()->prepare('SELECT item_id, lang, title FROM item_tr WHERE item_id IN (SELECT id FROM items WHERE type=?)'); $t->execute([$type]);
    foreach ($t->fetchAll() as $r) if (isset($rows[(int)$r['item_id']])) $rows[(int)$r['item_id']]['names'][$r['lang']] = (string)$r['title'];
    return $rows;
}
/** نام در زبان خواسته‌شده؛ اگر ترجمه نشده بود، فارسی */
function xp_name(array $row, string $lang): string {
    return ($row['names'][$lang] ?? '') !== '' ? $row['names'][$lang] : ($row['names']['fa'] ?? '');
}

/** تجربه‌های منتشرشده، مرتب‌شده، با مجری‌های منتشرشده */
function xp_experiences(): array {
    $exps = xp_load('experience'); if (!$exps) return [];
    $people = xp_load('person');
    uasort($exps, fn($a, $b) => [(int)($a['x']['sort'] ?? 100), $a['id']] <=> [(int)($b['x']['sort'] ?? 100), $b['id']]);
    foreach ($exps as &$e) {
        $e['who'] = [];
        foreach ((array)($e['x']['who'] ?? []) as $pid) if (isset($people[(int)$pid])) $e['who'][] = $people[(int)$pid];
    }
    return array_values($exps);
}

function xp_section_url(string $lang): string {
    return ($lang === 'fa' ? '/' : "/$lang/") . SECTIONS[$lang]['experiences'] . '/';
}

/** عکس‌های ثابت سایت (public/people) برای مجری‌هایی که در پنل عکس ندارند؛ با نام فارسی تطبیق داده می‌شود. عکس پنل همیشه اولویت دارد. */
const XP_STATIC_PHOTOS = [
    'حیدر بیمکی' => 'heydar-bimaki.webp', 'سینا آرش' => 'sina-arash.webp', 'جعفر دورفشان' => 'jafar-dorfeshan.webp',
    'امیررضا رهبر' => 'amirreza-rahbar.webp', 'سید رضا موسوی' => 'reza-mousavi.webp', 'حسین خضری' => 'hossein-khezri.webp', 'مهدی انصاری' => 'mehdi-ansari.webp',
];
function xp_avatar(array $p, string $lang): string {
    $m = $p['image'] ? media_get($p['image']) : null;
    if (!$m) {
        $f = XP_STATIC_PHOTOS[trim(xp_name($p, 'fa'))] ?? '';
        if ($f !== '' && is_file(CMS_ROOT . '/people/' . $f)) {
            return '<span class="xp-photo has"><img src="/people/' . e($f) . '" alt="' . e(xp_name($p, $lang)) . '" width="84" height="84" loading="lazy"/></span>';
        }
    }
    if ($m) {
        $alt = ($m['alt_' . $lang] ?? '') !== '' ? $m['alt_' . $lang] : xp_name($p, $lang);
        return '<span class="xp-photo has"><img src="' . e(media_url($m, true)) . '" alt="' . e($alt) . '" width="84" height="84" loading="lazy"/></span>';
    }
    return '<span class="xp-photo"><svg viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="18" r="8"></circle><path d="M8 42c2-10 9-14 16-14s14 4 16 14z"></path></svg><small>' . e(XP_UI[$lang]['photoSoon']) . '</small></span>';
}

/** محتوای داخلی <ul class="xp-grid"> دقیقاً با همان مارکاپ کامپوننت ExperiencesGrid؛ اگر چیزی منتشر نشده باشد '' */
function xp_cards_html(string $lang): string {
    $exps = xp_experiences(); if (!$exps) return '';
    $icons = xp_icons(); $u = XP_UI[$lang]; $h = '';
    foreach ($exps as $e) {
        $h .= '<li class="xp-card" data-sound="hang"><span class="xp-icon">' . ($icons[$e['x']['icon'] ?? '']['svg'] ?? '') . '</span><h3>' . e(xp_name($e, $lang)) . '</h3>';
        if (!$e['who']) $h .= '<p class="xp-free">' . e($u['noPerson']) . '</p>';
        else {
            $h .= '<ul class="xp-people">';
            foreach ($e['who'] as $p) {
                $h .= '<li>' . xp_avatar($p, $lang) . '<span class="xp-who"><small>' . e($u['coordinator']) . '</small><strong>' . e(xp_name($p, $lang)) . '</strong>';
                if (!empty($p['x']['phone'])) $h .= '<a href="' . e(xp_tel((string)$p['x']['phone'])) . '" dir="ltr" class="xp-call">' . e($p['x']['phone']) . '</a>';
                $h .= '</span></li>';
            }
            $h .= '</ul>';
        }
        $h .= '</li>';
    }
    return $h;
}

/** محتوای داخلی <ul class="xp-strip"> (نوار صفحهٔ اصلی؛ ۱۲ مورد اول) */
function xp_strip_html(string $lang): string {
    $exps = xp_experiences(); if (!$exps) return '';
    $icons = xp_icons(); $h = '';
    foreach (array_slice($exps, 0, 12) as $e) {
        $h .= '<li><a href="' . e(xp_section_url($lang)) . '" data-sound="hang"><span class="xp-icon sm">' . ($icons[$e['x']['icon'] ?? '']['svg'] ?? '') . '</span><span>' . e(xp_name($e, $lang)) . '</span></a></li>';
    }
    return $h;
}

/** محتوای داخلی اولین <ul class="$class"> را عوض می‌کند (با شمارش <ul> تو‌در‌تو)؛ اگر پیدا نشد null */
function xp_replace_ul(string $html, string $class, string $inner): ?string {
    if (!preg_match('#<ul class="' . preg_quote($class, '#') . '"[^>]*>#', $html, $m, PREG_OFFSET_CAPTURE)) return null;
    $start = $m[0][1] + strlen($m[0][0]); $depth = 1; $pos = $start;
    while ($depth > 0 && preg_match('#<ul\b|</ul>#', $html, $t, PREG_OFFSET_CAPTURE, $pos)) {
        $depth += $t[0][0] === '</ul>' ? -1 : 1; $pos = $t[0][1] + strlen($t[0][0]);
        if ($depth === 0) return substr($html, 0, $start) . $inner . substr($html, $t[0][1]);
    }
    return null;
}

/* ---------- وارد کردن داده‌های فعلی سایت (یک‌بار) ---------- */
function xp_seed_data(): array {
    return [
        'people' => [
            'abdolhossein-barkh' => [['عبدالحسین برخ', 'Abdolhossein Barkh', 'Абдольхоссейн Барх'], '09179294164'],
            'ali-daryaeinejad' => [['علی دریایی‌نژاد', 'Ali Daryaeinejad', 'Али Дарьяинеджад'], '09176695283'],
            'hossein-hasanebrahimi' => [['حسین حسن‌ابراهیمی', 'Hossein Hasanebrahimi', 'Хосейн Хасан-Ибрахими'], '09177719411'],
            'heydar-bimaki' => [['حیدر بیمکی', 'Heydar Bimaki', 'Хейдар Бимаки'], '09178737122'],
            'sina-arash' => [['سینا آرش', 'Sina Arash', 'Сина Араш'], '09113814089'],
            'jafar-dorfeshan' => [['جعفر دورفشان', 'Jafar Dorfeshan', 'Джафар Дурфешан'], '09171710056'],
            'ali-khajeh' => [['علی خواجه', 'Ali Khajeh', 'Али Хаджех'], '09171753730'],
            'amirreza-rahbar' => [['امیررضا رهبر', 'Amirreza Rahbar', 'Амирреза Рахбар'], '09376037139'],
            'reza-mousavi' => [['سید رضا موسوی', 'Seyed Reza Mousavi', 'Сейед Реза Мусави'], '09176008569'],
            'hossein-khezri' => [['حسین خضری', 'Hossein Khezri', 'Хосейн Хезри'], '09357173166'],
        ],
        'experiences' => [
            ['boat', ['قایق‌سواری ساحلی', 'Coastal boat ride', 'Прогулка на лодке'], ['abdolhossein-barkh', 'ali-daryaeinejad']],
            ['fishing', ['ماهی‌گیری', 'Fishing', 'Рыбалка'], ['hossein-hasanebrahimi', 'heydar-bimaki']],
            ['paddle', ['پدل‌بوردینگ', 'Paddle boarding', 'Падл-бординг'], ['sina-arash']],
            ['beachtennis', ['تنیس ساحلی', 'Beach tennis', 'Пляжный теннис'], ['sina-arash']],
            ['volleyball', ['والیبال ساحلی', 'Beach volleyball', 'Пляжный волейбол'], ['jafar-dorfeshan']],
            ['football', ['فوتبال ساحلی', 'Beach football', 'Пляжный футбол'], ['ali-khajeh']],
            ['claytennis', ['تنیس خاکی', 'Clay-court tennis', 'Теннис на грунте'], ['amirreza-rahbar']],
            ['museum', ['موزه نجوم و دریانوردی', 'Astronomy & Seafaring Museum', 'Музей астрономии и мореходства'], ['reza-mousavi']],
            ['oldtown', ['بافت‌گردی', 'Old-quarter walk', 'Прогулка по старому кварталу'], ['reza-mousavi']],
            ['stars', ['تور نجوم', 'Astronomy tour', 'Астрономический тур'], ['hossein-khezri']],
            ['sundial', ['ساعت‌های آفتابی', 'Sundials', 'Солнечные часы'], ['hossein-khezri']],
            ['park', ['پارک ساحلی', 'Beach park', 'Пляжный парк'], []],
        ],
    ];
}
/** فقط وقتی هیچ تجربه و مجری‌ای در پنل نیست */
function xp_seed_possible(): bool {
    return (int)db()->query("SELECT COUNT(*) FROM items WHERE type IN ('experience','person')")->fetchColumn() === 0;
}
function xp_seed(int $uid): int {
    if (!xp_seed_possible()) return 0;
    $d = xp_seed_data(); $pdo = db(); $ids = []; $n = 0; $langs = array_keys(LANGS);
    $ins = $pdo->prepare('INSERT INTO items(type,status,extra,author_id,created,updated,published_at) VALUES(?,?,?,?,?,?,?)');
    $tr = $pdo->prepare('INSERT INTO item_tr(item_id,lang,title) VALUES(?,?,?)');
    $pdo->beginTransaction();
    try {
        foreach ($d['people'] as $key => [$names, $phone]) {
            $ins->execute(['person', 'published', json_encode(['phone' => $phone], JSON_UNESCAPED_UNICODE), $uid, now(), now(), now()]);
            $ids[$key] = (int)$pdo->lastInsertId(); $n++;
            foreach ($langs as $i => $l) $tr->execute([$ids[$key], $l, $names[$i]]);
        }
        foreach ($d['experiences'] as $i => [$icon, $names, $who]) {
            $x = ['icon' => $icon, 'sort' => ($i + 1) * 10, 'who' => array_map(fn($k) => $ids[$k], $who)];
            $ins->execute(['experience', 'published', json_encode($x, JSON_UNESCAPED_UNICODE), $uid, now(), now(), now()]);
            $id = (int)$pdo->lastInsertId(); $n++;
            foreach ($langs as $j => $l) $tr->execute([$id, $l, $names[$j]]);
        }
        $pdo->commit();
    } catch (Throwable $ex) { $pdo->rollBack(); throw $ex; }
    return $n;
}
