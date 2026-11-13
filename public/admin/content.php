<?php
/* «متن‌های سایت»: ویرایش همهٔ متن‌های داخل کد (صفحه اصلی، منو، صفحه‌ها، نوشته‌ها، کتابخانه، تقویم، نقشه و …) از پنل.
   فهرست متن‌ها از cms-defaults.json می‌آید که هر بیلد خودکار از روی کد ساخته می‌شود؛ پس هر متنی که به سایت اضافه شود همین‌جا هم دیده می‌شود. */
require_once __DIR__ . '/../cms-core/overrides.php';

const CT_ROOTS = [
    'books' => 'کتابخانه نشر هلیله (کتاب‌ها)', 'publisher' => 'نشر هلیله (ناشر)', 'bookUi' => 'کتابخانه (برچسب‌ها)',
    'calUi' => 'تقویم (برچسب‌ها)', 'calEntries' => 'تقویم هلیله', 'monthNames' => 'نام ماه‌ها',
    'localEvents' => 'رویدادهای محلی', 'localEventsUi' => 'رویدادهای محلی (برچسب‌ها)',
    'mapPoints' => 'نقشه (نقطه‌ها)', 'mapStrings' => 'نقشه (برچسب‌ها)',
    'products' => 'صنایع دستی (محصولات)', 'productUi' => 'صنایع دستی (برچسب‌ها)',
    'galleryItems' => 'گالری (عنوان عکس‌ها)', 'galleryUi' => 'گالری (برچسب‌ها)',
    'people' => 'مجری‌های تجربه‌ها', 'experiences' => 'تجربه‌ها', 'expUi' => 'تجربه‌ها (برچسب‌ها)', 'messengerUi' => 'پیام‌رسان‌ها',
];
const CT_PAGES = ['about' => 'درباره', 'stay' => 'اقامتگاه‌ها', 'experiences' => 'تجربه‌ها', 'gallery' => 'گالری', 'blog' => 'کتابخانه (بلاگ)', 'events' => 'رویدادها', 'shop' => 'صنایع دستی', 'attractions' => 'جاذبه‌ها', 'restaurants' => 'خوراک', 'contact' => 'تماس'];

/** کلید → [شناسهٔ گروه، زبان یا ''] */
function ct_parse(string $k): array {
    $p = explode('.', $k);
    $lang = '';
    foreach ($p as $seg) if (in_array($seg, ['fa', 'en', 'ru'], true)) { $lang = $seg; break; }
    if ($p[0] === 'dicts') {
        $sub = $p[2] ?? 'general';
        if (in_array($sub, ['pages', 'posts'], true)) return [$sub . '.' . ($p[3] ?? ''), $lang];
        if (in_array($sub, ['nav', 'home', 'ui'], true)) return [$sub, $lang];
        return ['general', $lang];
    }
    if (in_array($p[0], ['books', 'calEntries', 'localEvents', 'mapPoints', 'products', 'galleryItems', 'experiences'], true) && isset($p[1]) && ctype_digit($p[1])) return [$p[0] . '.' . $p[1], $lang];
    return [$p[0], $lang];
}
function ct_group_title(string $g, array $def): string {
    $p = explode('.', $g);
    $map = ['nav' => 'منوی سایت', 'home' => 'صفحهٔ اصلی', 'ui' => 'متن‌های رابط (دکمه‌ها، فرم‌ها، جست‌وجو، فوتر)', 'general' => 'عمومی (نام سایت، شعار، نشانی، مدیر)'];
    if (isset($map[$g])) return $map[$g];
    if ($p[0] === 'pages') return 'صفحهٔ ' . (CT_PAGES[$p[1]] ?? $p[1]);
    if ($p[0] === 'posts') { $t = $def['dicts.fa.posts.' . $p[1] . '.title'] ?? ''; return 'نوشته: ' . ($t !== '' ? mb_substr($t, 0, 60) : '#' . $p[1]); }
    if (isset($p[1]) && ctype_digit($p[1])) {
        $t = $def[$p[0] . '.' . $p[1] . '.title.fa'] ?? $def[$p[0] . '.' . $p[1] . '.name.fa'] ?? $def[$p[0] . '.' . $p[1] . '.t.fa.title'] ?? '';
        return (CT_ROOTS[$p[0]] ?? $p[0]) . ' — ' . ($t !== '' ? mb_substr($t, 0, 50) : '#' . $p[1]);
    }
    return CT_ROOTS[$g] ?? $g;
}
function ct_groups(array $def): array {
    $out = [];
    foreach ($def as $k => $v) { [$g, $l] = ct_parse($k); $out[$g][] = $k; }
    return $out;
}
function ct_label(string $k): string {
    $p = explode('.', $k);
    if ($p[0] === 'dicts') $p = array_slice($p, 3);
    return implode(' › ', array_filter($p, fn($s) => !in_array($s, ['fa', 'en', 'ru'], true)));
}

function content_save(array $user): never {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    $def = ov_defaults(); $raw = ov_raw();
    $byDef = [];
    foreach ($def as $k => $v) $byDef[$v][] = $k;
    $changed = 0; $in = $_POST['t'] ?? [];
    if (is_array($in)) foreach ($in as $k => $val) {
        if (!is_string($k) || !isset($def[$k]) || !is_string($val)) continue;
        $val = trim(str_replace("\r\n", "\n", $val));
        $cur = $raw[$k] ?? $def[$k];
        if ($val === $cur) continue;
        $val = mb_substr($val, 0, 6000);
        foreach ($byDef[$def[$k]] as $kk) {   // متن یکسان در چند جا، با هم عوض می‌شود تا صفحه و مرورگر هم‌خوان بمانند
            if ($val === '' || $val === $def[$k]) unset($raw[$kk]); else $raw[$kk] = $val;
        }
        $changed++;
    }
    ov_save($raw);
    $_SESSION['flash'] = $changed ? $changed . ' متن ذخیره شد و همین حالا روی سایت اعمال می‌شود.' : 'تغییری نبود.';
    $to = '/admin/?a=content' . (!empty($_POST['g']) ? '&g=' . rawurlencode((string)$_POST['g']) : '') . (!empty($_POST['l']) ? '&l=' . rawurlencode((string)$_POST['l']) : '') . (!empty($_POST['q']) ? '&q=' . rawurlencode((string)$_POST['q']) : '');
    redirect($to);
}

function page_content(array $user): void {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    $def = ov_defaults(); $raw = ov_raw();
    layout_start('متن‌های سایت', 'content', $user);
    if (!$def) { echo '<p class="alert err">فهرست متن‌ها (cms-defaults.json) هنوز روی هاست نیست؛ بعد از deploy بعدی اینجا پر می‌شود.</p>'; layout_end(); return; }
    $g = (string)($_GET['g'] ?? ''); $l = (string)($_GET['l'] ?? ''); $q = trim((string)($_GET['q'] ?? ''));
    $groups = ct_groups($def);
    $count = array_count_values(array_values(array_map(fn($v) => $v, $def)));
    echo '<form method="get" class="ct-search"><input type="hidden" name="a" value="content"><input name="q" value="' . e($q) . '" placeholder="جست‌وجو در همهٔ متن‌های سایت…" aria-label="جست‌وجو"> <button class="btn">جست‌وجو</button></form>';
    $keys = [];
    if ($q !== '') {
        foreach ($def as $k => $v) if (mb_stripos($v, $q) !== false || mb_stripos($raw[$k] ?? '', $q) !== false) { $keys[] = $k; if (count($keys) >= 80) break; }
        echo '<h2>نتیجهٔ جست‌وجو: ' . e($q) . '</h2>';
        if (!$keys) echo '<p class="muted">موردی پیدا نشد.</p>';
    } elseif ($g !== '' && isset($groups[$g])) {
        $keys = array_values(array_filter($groups[$g], fn($k) => $l === '' || ct_parse($k)[1] === $l || ct_parse($k)[1] === ''));
        echo '<p><a href="/admin/?a=content">← همهٔ بخش‌ها</a></p><h2>' . e(ct_group_title($g, $def)) . '</h2><p class="muted">اگر متنی را پاک کنید یا به متن اصلی برگردانید، متن پیش‌فرض سایت دوباره نمایش داده می‌شود.</p>';
        $langs = []; foreach ($groups[$g] as $k) { $x = ct_parse($k)[1]; if ($x) $langs[$x] = true; }
        if (count($langs) > 1) { echo '<p class="ct-tabs">'; foreach (['' => 'همه زبان‌ها'] + LANGS as $code => $nm) if ($code === '' || isset($langs[$code])) echo '<a class="btn' . ($l === $code ? ' primary' : '') . '" href="/admin/?a=content&g=' . rawurlencode($g) . ($code ? '&l=' . $code : '') . '">' . e($nm) . '</a> '; echo '</p>'; }
    } else {
        echo '<p class="muted">هر بخش سایت را انتخاب کنید؛ همهٔ متن‌های آن به سه زبان همین‌جا قابل ویرایش است. نقطهٔ ● یعنی در آن بخش متنی ویرایش شده است.</p><table class="tbl"><thead><tr><th>بخش</th><th>تعداد متن</th><th>ویرایش‌شده</th></tr></thead><tbody>';
        $order = array_keys($groups);
        usort($order, function ($a, $b) {
            $rank = fn($g) => in_array($g, ['home', 'nav', 'general', 'ui'], true) ? 0 : (str_starts_with($g, 'pages.') ? 1 : (str_starts_with($g, 'posts.') ? 2 : 3));
            return [$rank($a), $a] <=> [$rank($b), $b];
        });
        foreach ($order as $gg) {
            $n = count($groups[$gg]); $m = count(array_filter($groups[$gg], fn($k) => isset($raw[$k])));
            echo '<tr><td><a href="/admin/?a=content&g=' . rawurlencode($gg) . '">' . e(ct_group_title($gg, $def)) . '</a></td><td>' . $n . '</td><td>' . ($m ? '<b>● ' . $m . '</b>' : '<span class="muted">—</span>') . '</td></tr>';
        }
        echo '</tbody></table>'; layout_end(); return;
    }
    $keys = array_values(array_filter($keys, fn($k) => !preg_match('/^books\.\d+\.cover$/', $k)));   // جلدها در بخش «جلد کتاب‌ها» است
    if ($keys) {
        echo '<form method="post" action="/admin/?a=content_save" class="ct-form"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="g" value="' . e($g) . '"><input type="hidden" name="l" value="' . e($l) . '"><input type="hidden" name="q" value="' . e($q) . '">';
        foreach ($keys as $k) {
            $d = $def[$k]; $cur = $raw[$k] ?? $d; $lg = ct_parse($k)[1]; $long = mb_strlen($d) > 70 || str_contains($d, "\n");
            echo '<div class="ct-row' . (isset($raw[$k]) ? ' mod' : '') . '"><label><span class="ct-key">' . e(ct_label($k)) . ($lg ? ' <em>' . e(LANGS[$lg]) . '</em>' : '') . (isset($raw[$k]) ? ' <b>● ویرایش‌شده</b>' : '') . (($count[$d] ?? 1) > 1 ? ' <small class="muted">(همین متن در ' . (($count[$d] ?? 1) - 1) . ' جای دیگر هم هست و با هم عوض می‌شود)</small>' : '') . '</span>';
            $dir = $lg === 'fa' || $lg === '' ? 'rtl' : 'ltr';
            echo $long ? '<textarea name="t[' . e($k) . ']" rows="' . min(10, max(3, (int)(mb_strlen($d) / 70) + 1)) . '" dir="' . $dir . '">' . e($cur) . '</textarea>' : '<input name="t[' . e($k) . ']" value="' . e($cur) . '" dir="' . $dir . '">';
            if (isset($raw[$k])) echo '<small class="muted">متن اصلی: ' . e(mb_substr($d, 0, 200)) . '</small>';
            echo '</label></div>';
        }
        echo '<p class="ct-save"><button class="btn primary">ذخیره و اعمال روی سایت</button></p></form>';
    }
    layout_end();
}
