<?php
/* «آمار بازدید»: داده از stats.sqlite (ساخته‌شدهٔ t.php). نمودارها SVG سمت سرورند (بدون کتابخانه، سازگار با CSP پنل). */
require_once __DIR__ . '/../cms-core/stats.php';

const ST_COUNTRIES = ['IR' => 'ایران', 'US' => 'آمریکا', 'DE' => 'آلمان', 'GB' => 'انگلیس', 'FR' => 'فرانسه', 'NL' => 'هلند', 'CA' => 'کانادا', 'TR' => 'ترکیه', 'AE' => 'امارات', 'RU' => 'روسیه', 'IQ' => 'عراق', 'AF' => 'افغانستان', 'SE' => 'سوئد', 'IT' => 'ایتالیا', 'ES' => 'اسپانیا', 'AU' => 'استرالیا', 'IN' => 'هند', 'CN' => 'چین', 'JP' => 'ژاپن', 'KR' => 'کره جنوبی', 'AZ' => 'آذربایجان', 'AM' => 'ارمنستان', 'GE' => 'گرجستان', 'KW' => 'کویت', 'QA' => 'قطر', 'OM' => 'عمان', 'BH' => 'بحرین', 'SA' => 'عربستان', 'PK' => 'پاکستان', 'UA' => 'اوکراین', 'BY' => 'بلاروس', 'KZ' => 'قزاقستان', 'UZ' => 'ازبکستان', 'TM' => 'ترکمنستان', 'CH' => 'سوئیس', 'AT' => 'اتریش', 'BE' => 'بلژیک', 'NO' => 'نروژ', 'DK' => 'دانمارک', 'FI' => 'فنلاند', 'PL' => 'لهستان', 'BR' => 'برزیل', 'MY' => 'مالزی', 'SG' => 'سنگاپور', 'EG' => 'مصر', 'LB' => 'لبنان', 'SY' => 'سوریه', 'IL' => 'اسرائیل', 'GR' => 'یونان', 'CY' => 'قبرس', 'MX' => 'مکزیک'];
const ST_PROVINCES = ['alborz' => 'البرز', 'ardabil' => 'اردبیل', 'bushehr' => 'بوشهر', 'chaharmahal and bakhtiari' => 'چهارمحال و بختیاری', 'east azerbaijan' => 'آذربایجان شرقی', 'fars' => 'فارس', 'gilan' => 'گیلان', 'golestan' => 'گلستان', 'hamadan' => 'همدان', 'hormozgan' => 'هرمزگان', 'ilam' => 'ایلام', 'isfahan' => 'اصفهان', 'kerman' => 'کرمان', 'kermanshah' => 'کرمانشاه', 'khuzestan' => 'خوزستان', 'kohgiluyeh and boyer-ahmad' => 'کهگیلویه و بویراحمد', 'kurdistan' => 'کردستان', 'lorestan' => 'لرستان', 'markazi' => 'مرکزی', 'mazandaran' => 'مازندران', 'north khorasan' => 'خراسان شمالی', 'qazvin' => 'قزوین', 'qom' => 'قم', 'razavi khorasan' => 'خراسان رضوی', 'semnan' => 'سمنان', 'sistan and baluchestan' => 'سیستان و بلوچستان', 'south khorasan' => 'خراسان جنوبی', 'tehran' => 'تهران', 'west azerbaijan' => 'آذربایجان غربی', 'yazd' => 'یزد', 'zanjan' => 'زنجان'];
const ST_CITIES = ['tehran' => 'تهران', 'mashhad' => 'مشهد', 'isfahan' => 'اصفهان', 'esfahan' => 'اصفهان', 'karaj' => 'کرج', 'shiraz' => 'شیراز', 'tabriz' => 'تبریز', 'qom' => 'قم', 'ahvaz' => 'اهواز', 'kermanshah' => 'کرمانشاه', 'urmia' => 'ارومیه', 'rasht' => 'رشت', 'zahedan' => 'زاهدان', 'hamadan' => 'همدان', 'kerman' => 'کرمان', 'yazd' => 'یزد', 'ardabil' => 'اردبیل', 'bandar abbas' => 'بندرعباس', 'arak' => 'اراک', 'zanjan' => 'زنجان', 'sanandaj' => 'سنندج', 'qazvin' => 'قزوین', 'khorramabad' => 'خرم‌آباد', 'gorgan' => 'گرگان', 'sari' => 'ساری', 'bushehr' => 'بوشهر', 'bandar-e bushehr' => 'بوشهر', 'dezful' => 'دزفول', 'abadan' => 'آبادان', 'kashan' => 'کاشان', 'sabzevar' => 'سبزوار', 'neyshabur' => 'نیشابور', 'nishapur' => 'نیشابور', 'birjand' => 'بیرجند', 'bojnord' => 'بجنورد', 'semnan' => 'سمنان', 'ilam' => 'ایلام', 'yasuj' => 'یاسوج', 'shahr-e kord' => 'شهرکرد', 'borujerd' => 'بروجرد', 'khomeyni shahr' => 'خمینی‌شهر', 'najafabad' => 'نجف‌آباد', 'kish' => 'کیش', 'qeshm' => 'قشم', 'bandar-e mahshahr' => 'ماهشهر', 'genaveh' => 'گناوه', 'borazjan' => 'برازجان', 'kangan' => 'کنگان', 'asaluyeh' => 'عسلویه', 'jam' => 'جم', 'dayyer' => 'دیر', 'dashtestan' => 'دشتستان', 'ahram' => 'اهرم', 'khormuj' => 'خورموج', 'rey' => 'ری', 'eslamshahr' => 'اسلامشهر', 'varamin' => 'ورامین', 'shahriar' => 'شهریار', 'pakdasht' => 'پاکدشت', 'fardis' => 'فردیس', 'karaj city' => 'کرج', 'mehrshahr' => 'مهرشهر', 'sirjan' => 'سیرجان', 'jahrom' => 'جهرم', 'marvdasht' => 'مرودشت', 'saveh' => 'ساوه', 'qaem shahr' => 'قائمشهر', 'babol' => 'بابل', 'amol' => 'آمل', 'mahabad' => 'مهاباد', 'khoy' => 'خوی', 'maragheh' => 'مراغه', 'bukan' => 'بوکان', 'saqqez' => 'سقز', 'bam' => 'بم', 'chabahar' => 'چابهار', 'iranshahr' => 'ایرانشهر'];

function fa_num($v): string { return strtr((string)$v, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']); }
function fa_n(int|float $n, int $dec = 0): string { return fa_num(number_format($n, $dec, '٫', '٬')); }
function g2j(int $gy, int $gm, int $gd): array {
    $gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    $gy2 = $gm > 2 ? $gy + 1 : $gy;
    $days = 355666 + 365 * $gy + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100) + intdiv($gy2 + 399, 400) + $gd + $gdm[$gm - 1];
    $jy = -1595 + 33 * intdiv($days, 12053); $days %= 12053;
    $jy += 4 * intdiv($days, 1461); $days %= 1461;
    if ($days > 365) { $jy += intdiv($days - 1, 365); $days = ($days - 1) % 365; }
    if ($days < 186) { $jm = 1 + intdiv($days, 31); $jd = 1 + $days % 31; } else { $jm = 7 + intdiv($days - 186, 30); $jd = 1 + ($days - 186) % 30; }
    return [$jy, $jm, $jd];
}
const ST_JM = ['', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
function st_jdate(int $ts, bool $full = false): string {   // ts ثانیهٔ یونیکس؛ خروجی به وقت تهران
    $t = $ts + STATS_TZ_OFFSET;
    [$jy, $jm, $jd] = g2j((int)gmdate('Y', $t), (int)gmdate('n', $t), (int)gmdate('j', $t));
    return fa_num($jd) . ' ' . ST_JM[$jm] . ($full ? ' ' . fa_num($jy) : '');
}
function st_hm(int $ts): string { return fa_num(gmdate('H:i', $ts + STATS_TZ_OFFSET)); }
function st_dur(int $s): string { if ($s < 60) return fa_n($s) . ' ثانیه'; $m = intdiv($s, 60); return fa_n($m) . ' دقیقه' . ($s % 60 ? ' و ' . fa_n($s % 60) . ' ثانیه' : ''); }
function st_flag(string $cc): string { return preg_match('/^[A-Z]{2}$/', $cc) ? mb_chr(0x1F1E6 + ord($cc[0]) - 65) . mb_chr(0x1F1E6 + ord($cc[1]) - 65) : ''; }
function st_norm(string $s): string { return trim(preg_replace(['/\s+province$/i', '/^ostan-e\s+/i', '/\s+county$/i'], '', strtolower(trim($s)))); }

/* ---------- نمودارها (SVG) ---------- */
const ST_PAL = ['#0a6fb5', '#1b7f4b', '#c78a1b', '#7a4fb5', '#b3261e', '#2c8fa3', '#8a7a5c', '#d9c7a6'];
function svg_area(array $series, array $labels, int $w = 760, int $h = 240): string {
    // series: [name => [values...]]؛ زمان از راست (قدیمی) به چپ (جدید)
    $n = count($labels); if ($n < 1) return '';
    $max = 1; foreach ($series as $v) $max = max($max, max($v));
    $pl = 8; $pr = 44; $pt = 14; $pb = 26; $iw = $w - $pl - $pr; $ih = $h - $pt - $pb;
    $x = fn($i) => $w - $pr - ($n > 1 ? $i * $iw / ($n - 1) : $iw / 2);
    $y = fn($v) => $pt + $ih - ($v / $max) * $ih;
    $o = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" class="st-svg" role="img" aria-label="نمودار بازدید روزانه">';
    for ($g = 0; $g <= 4; $g++) {
        $gv = $max * $g / 4; $gy = $y($gv);
        $o .= '<line x1="' . $pl . '" x2="' . ($w - $pr) . '" y1="' . $gy . '" y2="' . $gy . '" class="grid"/><text x="' . ($w - $pr + 6) . '" y="' . ($gy + 4) . '" class="ax">' . fa_n((int)round($gv)) . '</text>';
    }
    $step = max(1, (int)ceil($n / 8));
    for ($i = 0; $i < $n; $i += $step) $o .= '<text x="' . $x($i) . '" y="' . ($h - 6) . '" class="ax" text-anchor="middle">' . e($labels[$i]) . '</text>';
    $ci = 0;
    foreach ($series as $name => $vals) {
        $pts = []; for ($i = 0; $i < $n; $i++) $pts[] = round($x($i), 1) . ',' . round($y($vals[$i]), 1);
        $c = ST_PAL[$ci % count(ST_PAL)];
        $area = 'M' . round($x(0), 1) . ',' . ($pt + $ih) . ' L' . implode(' L', $pts) . ' L' . round($x($n - 1), 1) . ',' . ($pt + $ih) . ' Z';
        if ($ci === 0) $o .= '<path d="' . $area . '" fill="' . $c . '" opacity=".12"/>';
        $o .= '<polyline points="' . implode(' ', $pts) . '" fill="none" stroke="' . $c . '" stroke-width="2" stroke-linejoin="round"/>';
        if ($n <= 40) foreach ($pts as $i => $pt2) { [$px, $py] = explode(',', $pt2); $o .= '<circle cx="' . $px . '" cy="' . $py . '" r="2.6" fill="' . $c . '"><title>' . e($labels[$i]) . ': ' . fa_n($vals[$i]) . ' (' . e($name) . ')</title></circle>'; }
        $ci++;
    }
    return $o . '</svg>';
}
function svg_cols(array $vals, array $labels, int $w = 520, int $h = 200): string {
    $n = count($vals); $max = max(1, max($vals)); $pb = 22; $pt = 8; $bw = ($w - 10) / $n;
    $o = '<svg viewBox="0 0 ' . $w . ' ' . $h . '" class="st-svg" role="img" aria-label="نمودار ستونی">';
    foreach ($vals as $i => $v) {
        $bh = ($h - $pb - $pt) * $v / $max; $x = $w - 5 - ($i + 1) * $bw + 2;
        $o .= '<rect x="' . round($x, 1) . '" y="' . round($h - $pb - $bh, 1) . '" width="' . round($bw - 4, 1) . '" height="' . round($bh, 1) . '" rx="3" fill="#0a6fb5" opacity="' . ($v ? .85 : .15) . '"><title>' . e($labels[$i]) . ': ' . fa_n($v) . '</title></rect>';
        if ($i % 3 === 0) $o .= '<text x="' . round($x + ($bw - 4) / 2, 1) . '" y="' . ($h - 6) . '" class="ax" text-anchor="middle">' . e($labels[$i]) . '</text>';
    }
    return $o . '</svg>';
}
function svg_donut(array $data, int $size = 150): string {   // [برچسب => عدد]
    $tot = array_sum($data); if ($tot <= 0) return '<p class="muted">داده‌ای نیست</p>';
    $r = 54; $c = 2 * M_PI * $r; $off = 0; $o = '<div class="st-donut"><svg viewBox="0 0 140 140" width="' . $size . '" height="' . $size . '" role="img" aria-label="نمودار دایره‌ای"><g transform="rotate(-90 70 70)"><circle cx="70" cy="70" r="' . $r . '" fill="none" stroke="#eee8d8" stroke-width="22"/>';
    $i = 0; $leg = '';
    foreach ($data as $k => $v) {
        if ($v <= 0) continue; $len = $c * $v / $tot; $col = ST_PAL[$i % count(ST_PAL)];
        $o .= '<circle cx="70" cy="70" r="' . $r . '" fill="none" stroke="' . $col . '" stroke-width="22" stroke-dasharray="' . round($len, 2) . ' ' . round($c - $len, 2) . '" stroke-dashoffset="' . round(-$off, 2) . '"><title>' . e($k) . ': ' . fa_n($v) . '</title></circle>';
        $leg .= '<li><i style="background:' . $col . '"></i>' . e($k) . ' <b>' . fa_n(round($v * 100 / $tot, 1), 1) . '٪</b> <span class="muted">(' . fa_n($v) . ')</span></li>';
        $off += $len; $i++;
    }
    return $o . '</g></svg><ul>' . $leg . '</ul></div>';
}
function bars(array $rows, int $limit = 12, string $unit = ''): string {   // rows: [[label, count, extraHtml?]]
    if (!$rows) return '<p class="muted">داده‌ای نیست</p>';
    $rows = array_slice($rows, 0, $limit); $max = max(1, max(array_column($rows, 1))); $tot = max(1, array_sum(array_column($rows, 1)));
    $o = '<ul class="st-bars">';
    foreach ($rows as $r) $o .= '<li><div class="lb">' . $r[0] . '</div><div class="bar"><span style="width:' . round($r[1] * 100 / $max, 1) . '%"></span></div><div class="nm">' . fa_n($r[1]) . $unit . ' <small>' . fa_n(round($r[1] * 100 / $tot), 0) . '٪</small></div></li>';
    return $o . '</ul>';
}
function heatmap(array $grid, array $wd, array $hl): string {   // grid[wd][h]
    $max = 1; foreach ($grid as $r) $max = max($max, max($r));
    $o = '<div class="st-heat" dir="ltr"><div class="hr"><span></span>';
    for ($h = 0; $h < 24; $h++) $o .= '<span class="hh">' . ($h % 3 === 0 ? fa_num($h) : '') . '</span>';
    $o .= '</div>';
    foreach ($wd as $d => $name) {
        $o .= '<div class="hr"><span class="hd">' . e($name) . '</span>';
        for ($h = 0; $h < 24; $h++) { $v = $grid[$d][$h]; $o .= '<span class="hc" style="opacity:' . ($v ? round(.18 + .82 * $v / $max, 2) : .06) . '" title="' . e($name) . ' ساعت ' . fa_num($h) . ': ' . fa_n($v) . ' بازدید"></span>'; }
        $o .= '</div>';
    }
    return $o . '</div>';
}

function page_stats(array $user): void {
    if (!can('others')) { http_response_code(403); exit('دسترسی ندارید'); }
    layout_start('آمار بازدید', 'stats', $user);
    $db = stats_db();
    $rng = (string)($_GET['r'] ?? '30'); $opts = ['1' => 'امروز', '7' => '۷ روز', '30' => '۳۰ روز', '90' => '۹۰ روز', '365' => '۱ سال'];
    if (!isset($opts[$rng])) $rng = '30'; $N = (int)$rng;
    $off = STATS_TZ_OFFSET; $now = time();
    $today = intdiv($now + $off, 86400) * 86400 - $off;
    $from = $today - ($N - 1) * 86400; $pfrom = $from - $N * 86400;
    $q = function (string $sql, array $a = []) use ($db) { $s = $db->prepare($sql); $s->execute($a); return $s->fetchAll(); };
    $one = fn(string $sql, array $a = []) => $q($sql, $a)[0] ?? [];


    $diag = function () { $L = stats_log_tail(15); return '<details class="st-card wide"' . (!$L ? '' : ' open') . '><summary><b>عیب‌یابی ردیاب</b></summary><p class="muted">آزمایش سلامت: <a href="/dynamic.php?hc=1&check=1" target="_blank" rel="noopener" dir="ltr">/dynamic.php?hc=1&amp;check=1</a> (باید <span dir="ltr">{"ok":true,…}</span> نشان دهد). دلیل بازدیدهای ثبت‌نشده اینجا نوشته می‌شود:</p>' . ($L ? '<pre dir="ltr" class="st-log">' . e(implode("\n", $L)) . '</pre>' : '<p class="muted">خطا یا ردی از درخواست رد‌شده نیست.</p>') . '</details>'; };
    echo '<div class="st-top"><p class="ct-tabs">';
    foreach ($opts as $k => $nm) echo '<a class="btn' . ($rng === (string)$k ? ' primary' : '') . '" href="/admin/?a=stats&r=' . $k . '">' . e($nm) . '</a> ';
    echo '</p><label class="st-me"><input type="checkbox" data-notrack> این مرورگر را از آمار حذف کن (بازدیدهای خودم شمرده نشود)</label></div>';

    $tot = $one('SELECT COUNT(*) v, COUNT(DISTINCT vid) u, COUNT(DISTINCT sid) s FROM hits WHERE ts>=?', [$from]);
    if ((int)($tot['v'] ?? 0) === 0) {
        $any = (int)$one('SELECT COUNT(*) c FROM hits')['c'];
        echo '<div class="alert ok">' . ($any ? 'در این بازه بازدیدی ثبت نشده است.' : 'هنوز بازدیدی ثبت نشده. آمار از لحظهٔ فعال‌شدن این بخش شروع می‌شود و ربات‌ها شمرده نمی‌شوند. یک بار سایت را (در پنجرهٔ ناشناس یا مرورگری که حذف‌شده نیست) باز کنید و اینجا را دوباره ببینید.') . '</div>';
        echo $diag(); layout_end(); return;
    }
    $prev = $one('SELECT COUNT(*) v, COUNT(DISTINCT vid) u FROM hits WHERE ts>=? AND ts<?', [$pfrom, $from]);
    $sess = $q('SELECT COUNT(*) c, SUM(dur) d FROM hits WHERE ts>=? GROUP BY sid', [$from]);
    $nS = count($sess); $bounce = 0; $dsum = 0; foreach ($sess as $r) { if ((int)$r['c'] === 1) $bounce++; $dsum += (int)$r['d']; }
    $newU = (int)$one('SELECT COUNT(DISTINCT vid) c FROM hits WHERE ts>=? AND newv=1', [$from])['c']; $retU = max(0, (int)$tot['u'] - $newU);
    $online = (int)$one('SELECT COUNT(DISTINCT sid) c FROM hits WHERE last>=?', [$now - 180])['c'];
    $delta = function ($cur, $pre) { if ((int)$pre <= 0) return ''; $p = round(($cur - $pre) * 100 / $pre); return '<small class="' . ($p >= 0 ? 'up' : 'down') . '">' . ($p >= 0 ? '▲' : '▼') . ' ' . fa_n(abs($p)) . '٪ نسبت به دورهٔ قبل</small>'; };
    $kpi = [
        ['بازدیدکنندهٔ یکتا', fa_n((int)$tot['u']), $delta((int)$tot['u'], (int)$prev['u'])],
        ['بازدید صفحه', fa_n((int)$tot['v']), $delta((int)$tot['v'], (int)$prev['v'])],
        ['نشست (ورود به سایت)', fa_n($nS), ''],
        ['میانگین مدت حضور در هر نشست', st_dur($nS ? (int)round($dsum / $nS) : 0), ''],
        ['صفحه در هر نشست', fa_n($nS ? $tot['v'] / $nS : 0, 1), ''],
        ['نرخ پرش (فقط یک صفحه)', fa_n($nS ? round($bounce * 100 / $nS) : 0) . '٪', ''],
        ['جدید / برگشتی', fa_n($newU) . ' / ' . fa_n($retU), ''],
        ['آنلاین در ۳ دقیقهٔ اخیر', fa_n($online), ''],
    ];
    echo '<div class="st-kpis">'; foreach ($kpi as [$l, $v, $d]) echo '<div class="st-kpi"><span>' . e($l) . '</span><b>' . $v . '</b>' . $d . '</div>'; echo '</div>';

    // روند روزانه
    $days = []; foreach ($q("SELECT strftime('%Y-%m-%d', ts+$off,'unixepoch') d, COUNT(*) v, COUNT(DISTINCT vid) u FROM hits WHERE ts>=? GROUP BY d", [$from]) as $r) $days[$r['d']] = $r;
    $lab = []; $sv = []; $su = [];
    for ($i = 0; $i < $N; $i++) { $t = $from + $i * 86400; $k = gmdate('Y-m-d', $t + $off); $lab[] = st_jdate($t); $sv[] = (int)($days[$k]['v'] ?? 0); $su[] = (int)($days[$k]['u'] ?? 0); }
    if ($N === 1) { $lab = ['امروز']; }
    echo '<section class="st-card wide"><h2>بازدید در هر روز</h2><p class="st-legend"><i style="background:#0a6fb5"></i> بازدید صفحه <i style="background:#1b7f4b"></i> بازدیدکنندهٔ یکتا</p>' . ($N > 1 ? svg_area(['بازدید صفحه' => $sv, 'بازدیدکنندهٔ یکتا' => $su], $lab) : '<p class="muted">برای نمودار روزانه بازهٔ ۷ روز یا بیشتر را انتخاب کنید.</p>') . '</section>';

    // ساعت و روز هفته
    $hrs = array_fill(0, 24, 0); $grid = array_fill(0, 7, array_fill(0, 24, 0));
    foreach ($q("SELECT CAST(strftime('%w', ts+$off,'unixepoch') AS INT) w, CAST(strftime('%H', ts+$off,'unixepoch') AS INT) h, COUNT(*) c FROM hits WHERE ts>=? GROUP BY w,h", [$from]) as $r) { $wd = ((int)$r['w'] + 1) % 7; $grid[$wd][(int)$r['h']] += (int)$r['c']; $hrs[(int)$r['h']] += (int)$r['c']; }
    $wnames = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه']; $hl = array_map('fa_num', range(0, 23));
    echo '<div class="st-grid2"><section class="st-card"><h2>ساعت‌های پربازدید (به وقت تهران)</h2>' . svg_cols($hrs, $hl) . '</section><section class="st-card"><h2>روز هفته و ساعت</h2>' . heatmap($grid, $wnames, $hl) . '</section></div>';

    // دایره‌ها
    $devNames = ['mobile' => 'موبایل', 'tablet' => 'تبلت', 'desktop' => 'رایانه'];
    $dev = []; foreach ($q('SELECT dev k, COUNT(DISTINCT sid) c FROM hits WHERE ts>=? GROUP BY dev ORDER BY c DESC', [$from]) as $r) $dev[$devNames[$r['k']] ?? $r['k']] = (int)$r['c'];
    $refNames = ['direct' => 'مستقیم (آدرس / بوکمارک)', 'search' => 'موتور جست‌وجو', 'social' => 'شبکه‌های اجتماعی', 'site' => 'سایت‌های دیگر', 'email' => 'ایمیل', 'campaign' => 'کمپین (UTM)', 'other' => 'سایر'];
    $src = []; foreach ($q('SELECT ref_type k, COUNT(DISTINCT sid) c FROM hits WHERE ts>=? AND entry=1 GROUP BY ref_type ORDER BY c DESC', [$from]) as $r) $src[$refNames[$r['k']] ?? $r['k']] = (int)$r['c'];
    $lg = []; foreach ($q('SELECT lang k, COUNT(*) c FROM hits WHERE ts>=? GROUP BY lang ORDER BY c DESC', [$from]) as $r) $lg[LANGS[$r['k']] ?? $r['k']] = (int)$r['c'];
    echo '<div class="st-grid3"><section class="st-card"><h2>نوع دستگاه</h2>' . svg_donut($dev) . '</section><section class="st-card"><h2>از کجا آمده‌اند؟</h2>' . svg_donut($src) . '</section><section class="st-card"><h2>زبان سایت (بازدید صفحه)</h2>' . svg_donut($lg) . '</section></div>';

    // صفحه‌ها
    $pg = fn($r) => '<a href="' . e($r['k']) . '" target="_blank" rel="noopener" dir="ltr">' . e($r['k']) . '</a>';
    $pages = array_map(fn($r) => [$pg($r), (int)$r['c']], $q('SELECT path k, COUNT(*) c FROM hits WHERE ts>=? GROUP BY path ORDER BY c DESC LIMIT 15', [$from]));
    $entries = array_map(fn($r) => [$pg($r), (int)$r['c']], $q('SELECT path k, COUNT(*) c FROM hits WHERE ts>=? AND entry=1 GROUP BY path ORDER BY c DESC LIMIT 10', [$from]));
    echo '<div class="st-grid2"><section class="st-card"><h2>صفحه‌های پربازدید</h2>' . bars($pages, 15) . '</section><section class="st-card"><h2>صفحه‌های ورودی (اولین صفحهٔ نشست)</h2>' . bars($entries, 10) . '</section></div>';

    // ارجاع‌ها
    $refs = array_map(fn($r) => ['<span dir="ltr">' . e($r['k']) . '</span> <small class="muted">' . e($refNames[$r['t']] ?? '') . '</small>', (int)$r['c']], $q("SELECT ref_host k, ref_type t, COUNT(DISTINCT sid) c FROM hits WHERE ts>=? AND entry=1 AND ref_host<>'' GROUP BY ref_host ORDER BY c DESC LIMIT 15", [$from]));
    $urls = array_map(fn($r) => ['<a href="' . e($r['k']) . '" target="_blank" rel="noopener nofollow" dir="ltr">' . e(mb_substr(preg_replace('#^https?://#', '', $r['k']), 0, 70)) . '</a>', (int)$r['c']], $q("SELECT ref_url k, COUNT(DISTINCT sid) c FROM hits WHERE ts>=? AND entry=1 AND ref_url LIKE 'http%' GROUP BY ref_url ORDER BY c DESC LIMIT 15", [$from]));
    echo '<div class="st-grid2"><section class="st-card"><h2>سایت‌هایی که بازدیدکننده فرستاده‌اند</h2>' . bars($refs, 15) . '</section><section class="st-card"><h2>لینک‌های دقیق ارجاع</h2>' . bars($urls, 15) . '</section></div>';
    $utm = array_map(fn($r) => [e(trim($r['s'] . ' / ' . $r['m'] . ' / ' . $r['c'], ' /')), (int)$r['n']], $q("SELECT utm_s s, utm_m m, utm_c c, COUNT(DISTINCT sid) n FROM hits WHERE ts>=? AND utm_s<>'' GROUP BY utm_s,utm_m,utm_c ORDER BY n DESC LIMIT 10", [$from]));
    if ($utm) echo '<section class="st-card wide"><h2>کمپین‌ها (منبع / رسانه / نام)</h2>' . bars($utm, 10) . '</section>';

    // جغرافیا
    $cn = array_map(fn($r) => [st_flag($r['k']) . ' ' . e(ST_COUNTRIES[$r['k']] ?? ($r['k'] ?: 'نامشخص')), (int)$r['c']], $q("SELECT country k, COUNT(DISTINCT vid) c FROM hits WHERE ts>=? GROUP BY country ORDER BY c DESC LIMIT 15", [$from]));
    $prov = []; $city = [];
    foreach ($q("SELECT region, city, COUNT(DISTINCT vid) c FROM hits WHERE ts>=? AND country='IR' GROUP BY region, city", [$from]) as $r) {
        $p = st_norm($r['region']); $c = st_norm($r['city']);
        $pn = $r['region'] === '' ? 'نامشخص' : (ST_PROVINCES[$p] ?? $r['region']); $cnm = $r['city'] === '' ? 'نامشخص' : (ST_CITIES[$c] ?? $r['city']);
        $prov[$pn] = ($prov[$pn] ?? 0) + (int)$r['c']; $city[$cnm] = ($city[$cnm] ?? 0) + (int)$r['c'];
    }
    arsort($prov); arsort($city);
    $asRows = fn($a) => array_map(fn($k, $v) => [e((string)$k), $v], array_keys($a), $a);
    echo '<div class="st-grid3"><section class="st-card"><h2>کشورها (بازدیدکنندهٔ یکتا)</h2>' . bars($cn, 15) . '</section><section class="st-card"><h2>استان‌های ایران</h2>' . bars($asRows($prov), 15) . '</section><section class="st-card"><h2>شهرهای ایران</h2>' . bars($asRows($city), 15) . '</section></div>';
    echo '<p class="muted st-note">موقعیت از آی‌پی تخمین زده می‌شود و آی‌پی ذخیره نمی‌شود. برای اینترنت همراه و VPN دقت شهر کم است و گاهی شهر مرکز اپراتور دیده می‌شود.</p>';

    // دستگاه و سیستم‌عامل
    $simple = function (string $col, int $lim = 10) use ($q, $from) { return array_map(fn($r) => [e($r['k'] !== '' ? $r['k'] : 'نامشخص'), (int)$r['c']], $q("SELECT $col k, COUNT(DISTINCT sid) c FROM hits WHERE ts>=? GROUP BY $col ORDER BY c DESC LIMIT $lim", [$from])); };
    $osF = [];   // خانوادهٔ سیستم‌عامل برای نمودار کلی
    foreach ($simple('os', 40) as [$l, $c]) { $fam = preg_replace('/ [\d\/.]+$/', '', html_entity_decode($l)); $fam = str_starts_with($fam, 'Windows') ? 'Windows' : $fam; $osF[$fam] = ($osF[$fam] ?? 0) + $c; } arsort($osF);
    echo '<div class="st-grid3"><section class="st-card"><h2>سیستم‌عامل (خانواده)</h2>' . svg_donut($osF) . '</section><section class="st-card"><h2>نسخهٔ سیستم‌عامل</h2>' . bars($simple('os', 12), 12) . '</section><section class="st-card"><h2>مرورگر</h2>' . bars($simple('browser'), 10) . '</section></div>';
    $scr = function () use ($q, $from) { $o = ['تا ۳۶۰' => 0, '۳۶۱ تا ۷۶۷' => 0, '۷۶۸ تا ۱۲۷۹' => 0, '۱۲۸۰ تا ۱۹۱۹' => 0, '۱۹۲۰ و بیشتر' => 0]; foreach ($q('SELECT scr, COUNT(DISTINCT sid) c FROM hits WHERE ts>=? AND scr>0 GROUP BY scr', [$from]) as $r) { $w = (int)$r['scr']; $k = $w <= 360 ? 'تا ۳۶۰' : ($w < 768 ? '۳۶۱ تا ۷۶۷' : ($w < 1280 ? '۷۶۸ تا ۱۲۷۹' : ($w < 1920 ? '۱۲۸۰ تا ۱۹۱۹' : '۱۹۲۰ و بیشتر'))); $o[$k] += (int)$r['c']; } return array_map(fn($k, $v) => [$k, $v], array_keys($o), $o); };
    $bl = array_map(fn($r) => ['<span dir="ltr">' . e($r['k'] ?: '؟') . '</span>', (int)$r['c']], $q("SELECT blang k, COUNT(DISTINCT vid) c FROM hits WHERE ts>=? GROUP BY blang ORDER BY c DESC LIMIT 8", [$from]));
    echo '<div class="st-grid3"><section class="st-card"><h2>برند دستگاه</h2>' . bars($simple('brand'), 10) . '</section><section class="st-card"><h2>عرض صفحه (پیکسل)</h2>' . bars($scr(), 5) . '</section><section class="st-card"><h2>زبان مرورگر بازدیدکننده</h2>' . bars($bl, 8) . '</section></div>';

    // زنده
    $rows = $q('SELECT * FROM hits ORDER BY id DESC LIMIT 25');
    echo '<section class="st-card wide"><h2>آخرین بازدیدها</h2><div class="tbl-wrap"><table class="tbl"><thead><tr><th>زمان</th><th>صفحه</th><th>مکان</th><th>دستگاه</th><th>منبع</th><th>مدت</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $loc = ($r['country'] ? st_flag($r['country']) . ' ' : '') . ($r['country'] === 'IR' ? (ST_CITIES[st_norm($r['city'])] ?? $r['city']) : (ST_COUNTRIES[$r['country']] ?? $r['country']));
        echo '<tr><td>' . st_jdate((int)$r['ts']) . ' ' . st_hm((int)$r['ts']) . '</td><td dir="ltr">' . e($r['path']) . '</td><td>' . e(trim($loc) ?: '—') . '</td><td>' . e(($devNames[$r['dev']] ?? '') . ' · ' . $r['os'] . ' · ' . $r['browser']) . '</td><td>' . e($r['ref_host'] ?: ($refNames[$r['ref_type']] ?? '—')) . '</td><td>' . ($r['dur'] ? st_dur((int)$r['dur']) : '—') . '</td></tr>';
    }
    echo '</tbody></table></div></section><p class="muted st-note">ربات‌ها، مرورگرهای خودکار، درخواست «Do Not Track» و بازدیدهای مرورگرهای حذف‌شده شمرده نمی‌شوند. بازدید داخل پنل ثبت نمی‌شود. داده‌ها ۱۳ ماه نگه داشته می‌شود.</p>';
    echo $diag();
    layout_end();
}
