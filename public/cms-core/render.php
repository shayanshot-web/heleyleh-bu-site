<?php
/* نمایش عمومی محتوای پنل داخل قالب سایت Next.js (همان هدر، فوتر و استایل) */

const UI = [
    'fa' => ['more' => 'ادامه مطلب', 'back' => 'بازگشت', 'none' => 'هنوز موردی ثبت نشده است.', 'price' => 'قیمت', 'stock' => 'موجودی', 'sku' => 'کد', 'cap' => 'ظرفیت', 'persons' => 'نفر', 'night' => 'هر شب', 'amen' => 'امکانات', 'when' => 'زمان', 'place' => 'مکان', 'name' => 'نام', 'phone' => 'تلفن', 'email' => 'ایمیل', 'msg' => 'پیام', 'send' => 'ارسال درخواست', 'ok' => 'درخواست شما ثبت شد؛ به‌زودی تماس می‌گیریم.', 'err' => 'ارسال نشد؛ لطفاً دوباره تلاش کنید یا تماس بگیرید.', 'req' => ['product' => 'استعلام و سفارش این محصول', 'stay' => 'درخواست رزرو', 'event' => 'ثبت‌نام / پرسش درباره رویداد'], 'subj' => ['product' => 'فروشگاه', 'stay' => 'رزرو اقامت', 'event' => 'رویداد'], 'dir' => 'rtl', 'latest' => 'تازه‌ترین'],
    'en' => ['more' => 'Read more', 'back' => 'Back', 'none' => 'Nothing here yet.', 'price' => 'Price', 'stock' => 'In stock', 'sku' => 'SKU', 'cap' => 'Capacity', 'persons' => 'guests', 'night' => 'per night', 'amen' => 'Amenities', 'when' => 'When', 'place' => 'Place', 'name' => 'Name', 'phone' => 'Phone', 'email' => 'Email', 'msg' => 'Message', 'send' => 'Send request', 'ok' => 'Thank you — your request was received. We will contact you soon.', 'err' => 'Could not send. Please try again or call us.', 'req' => ['product' => 'Order enquiry for this product', 'stay' => 'Booking request', 'event' => 'Event enquiry'], 'subj' => ['product' => 'Shop', 'stay' => 'Accommodation', 'event' => 'Event'], 'dir' => 'ltr', 'latest' => 'Latest'],
    'ru' => ['more' => 'Читать далее', 'back' => 'Назад', 'none' => 'Пока ничего нет.', 'price' => 'Цена', 'stock' => 'В наличии', 'sku' => 'Артикул', 'cap' => 'Вместимость', 'persons' => 'чел.', 'night' => 'за ночь', 'amen' => 'Удобства', 'when' => 'Когда', 'place' => 'Место', 'name' => 'Имя', 'phone' => 'Телефон', 'email' => 'Email', 'msg' => 'Сообщение', 'send' => 'Отправить запрос', 'ok' => 'Спасибо! Запрос получен, мы скоро свяжемся с вами.', 'err' => 'Не удалось отправить. Повторите попытку или позвоните.', 'req' => ['product' => 'Запрос по этому изделию', 'stay' => 'Запрос на бронирование', 'event' => 'Вопрос о мероприятии'], 'subj' => ['product' => 'Магазин', 'stay' => 'Проживание', 'event' => 'Событие'], 'dir' => 'ltr', 'latest' => 'Последнее'],
];
const SECTION_TYPES = ['blog' => 'post', 'shop' => 'product', 'events' => 'event', 'stay' => 'stay', 'gallery' => 'gallery'];

function cms_404(): never {
    http_response_code(404);
    $f = CMS_ROOT . '/404.html';
    header('Content-Type: text/html; charset=utf-8');
    if (is_file($f)) readfile($f); else echo 'Not found';
    exit;
}
function cms_prefix(string $lang): string { return $lang === 'fa' ? '/' : "/$lang/"; }
function cms_url(string $lang, string $section, string $slug = ''): string {
    return cms_prefix($lang) . SECTIONS[$lang][$section] . '/' . ($slug !== '' ? rawurlencode($slug) . '/' : '');
}
function cms_items(string $type, string $lang, int $limit = 100): array {
    $s = db()->prepare("SELECT i.*, t.title, t.slug, t.excerpt, t.body FROM items i JOIN item_tr t ON t.item_id=i.id AND t.lang=? WHERE i.type=? AND i.status='published' AND t.title<>'' ORDER BY " . ($type === 'event' ? "json_extract(i.extra,'$.date_start') DESC, " : '') . "COALESCE(i.published_at,i.created) DESC LIMIT " . (int)$limit);
    $s->execute([$lang, $type]);
    $rows = $s->fetchAll();
    foreach ($rows as &$r) $r['extra'] = json_decode($r['extra'] ?: '{}', true) ?: [];
    return $rows;
}
function cms_img(?int $id, string $lang, bool $thumb = false, string $cls = ''): string {
    if (!$id) return '';
    $m = media_get($id); if (!$m) return '';
    $alt = $m['alt_' . $lang] ?? '';
    return '<img' . ($cls ? ' class="' . $cls . '"' : '') . ' src="' . e(media_url($m, $thumb)) . '" alt="' . e($alt) . '" loading="lazy" width="' . (int)$m['w'] . '" height="' . (int)$m['h'] . '">';
}
function cms_meta_line(array $it, string $type, string $lang): string {
    $u = UI[$lang]; $x = $it['extra']; $o = [];
    if ($type === 'post') $o[] = format_date((int)($it['published_at'] ?: $it['created']), $lang);
    if ($type === 'product') { if (!empty($x['price'])) $o[] = $u['price'] . ': ' . $x['price']; }
    if ($type === 'event') { $d = ($x['date_start'] ?? '') . (!empty($x['date_end']) && $x['date_end'] !== ($x['date_start'] ?? '') ? ' → ' . $x['date_end'] : ''); if (trim($d)) $o[] = $d; if (!empty($x['place'])) $o[] = $x['place']; }
    if ($type === 'stay') { if (!empty($x['capacity'])) $o[] = $u['cap'] . ': ' . $x['capacity'] . ' ' . $u['persons']; if (!empty($x['price'])) $o[] = $x['price'] . ' ' . $u['night']; }
    return implode(' · ', array_map('e', array_filter($o)));
}
function cms_cards(array $items, string $type, string $lang, string $section): string {
    $u = UI[$lang]; $h = '<div class="cards cms-cards">';
    foreach ($items as $it) {
        $url = cms_url($lang, $section, $it['slug']); $meta = cms_meta_line($it, $type, $lang);
        $h .= '<article class="card">' . ($it['image'] ? '<a href="' . e($url) . '" tabindex="-1" aria-hidden="true">' . cms_img((int)$it['image'], $lang, true, 'cms-thumb') . '</a>' : '') . ($meta ? '<p class="meta">' . $meta . '</p>' : '') . '<h3><a href="' . e($url) . '">' . e($it['title']) . '</a></h3>' . ($it['excerpt'] !== '' ? '<p>' . e($it['excerpt']) . '</p>' : '') . '<a class="more" href="' . e($url) . '">' . e($u['more']) . '</a></article>';
    }
    return $h . '</div>';
}
function cms_gallery(array $items, string $lang): string {
    $h = '<div class="cms-gallery">';
    foreach ($items as $it) {
        $m = $it['image'] ? media_get((int)$it['image']) : null; if (!$m) continue;
        $h .= '<figure><a href="' . e(media_url($m)) . '" target="_blank" rel="noopener">' . cms_img((int)$it['image'], $lang, true) . '</a><figcaption>' . e($it['title']) . '</figcaption></figure>';
    }
    return $h . '</div>';
}
function cms_form(string $type, string $lang, string $ref): string {
    $u = UI[$lang];
    return '<form class="contact-form cms-req" data-lang="' . e($lang) . '" data-ok="' . e($u['ok']) . '" data-err="' . e($u['err']) . '"><h2>' . e($u['req'][$type]) . '</h2><input type="hidden" name="subject" value="' . e($u['subj'][$type]) . '"><input type="hidden" name="ref" value="' . e($ref) . '"><input type="hidden" name="lang" value="' . e($lang) . '"><div class="hp" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div><label>' . e($u['name']) . '<input name="name" required maxlength="200"></label><label>' . e($u['phone']) . '<input name="phone" required maxlength="40" dir="ltr"></label><label>' . e($u['email']) . '<input name="email" type="email" maxlength="200" dir="ltr"></label><label>' . e($u['msg']) . '<textarea name="message" rows="4" required maxlength="2000">' . e($ref) . '</textarea></label><button class="btn btn-blue" type="submit">' . e($u['send']) . '</button><p class="form-status" role="status"></p></form>';
}
function cms_hero(string $title, string $meta = '', string $lead = ''): string {
    return '<section class="page-hero"><div class="container"><h1>' . e($title) . '</h1>' . ($meta ? '<p class="meta">' . $meta . '</p>' : '') . ($lead !== '' ? '<p class="lead">' . e($lead) . '</p>' : '') . '</div></section>';
}

function cms_render_request(): void {
    if (isset($_GET['sitemap'])) { cms_sitemap(); return; }
    if (isset($_GET['searchindex'])) { cms_search_index(); return; }
    if (isset($_GET['home'])) { cms_render_home(); return; }
    $p = trim((string)($_GET['p'] ?? ''), '/');
    $seg = $p === '' ? [] : explode('/', $p);
    $lang = 'fa';
    if ($seg && in_array($seg[0], ['en', 'ru'], true)) $lang = array_shift($seg);
    $secSlug = rawurldecode($seg[0] ?? ''); $itemSlug = isset($seg[1]) ? rawurldecode($seg[1]) : '';
    if (count($seg) < 1 || count($seg) > 2) cms_404();
    $section = array_search($secSlug, SECTIONS[$lang], true);
    if ($section === false) cms_404();
    $tplFile = CMS_ROOT . '/' . ($lang === 'fa' ? '' : $lang . '/') . SECTIONS[$lang][$section] . '/index.html';
    if (!is_file($tplFile)) cms_404();
    $tpl = (string)file_get_contents($tplFile);
    $host = SITE_HOST;   // نه HTTP_HOST: هدر Host قابل جعل است
    $u = UI[$lang];
    $type = SECTION_TYPES[$section] ?? null;
    $title = null; $desc = ''; $main = null; $canon = cms_url($lang, $section);
    $alts = []; $inject = ''; $items = [];
    $keep = false;   // true: اسکریپت‌های Next حفظ می‌شود (فقط کارت‌های تجربه‌ها جایگزین شده و هیدریشن آن‌ها را بازنویسی نمی‌کند)

    if ($itemSlug !== '') {
        if (!$type || $type === 'gallery') cms_404();
        $s = db()->prepare("SELECT i.* FROM items i JOIN item_tr t ON t.item_id=i.id WHERE i.type=? AND t.lang=? AND t.slug=? AND i.status='published' AND t.title<>''");
        $s->execute([$type, $lang, $itemSlug]); $row = $s->fetch();
        if (!$row) cms_404();
        $it = item_load((int)$row['id']); $tr = $it['tr'][$lang]; $it['extra'] = $it['extra'] ?? [];
        $row2 = $it + $tr; $row2['extra'] = $it['extra'];
        $title = $tr['title']; $desc = $tr['excerpt'] !== '' ? $tr['excerpt'] : mb_substr(trim(strip_tags($tr['body'])), 0, 160);
        $canon = cms_url($lang, $section, $tr['slug']);
        foreach (LANGS as $l => $_) if (!empty($it['tr'][$l]['title']) && !empty($it['tr'][$l]['slug'])) $alts[$l] = cms_url($l, $section, $it['tr'][$l]['slug']);
        $x = $it['extra'];
        $info = '';
        if ($type === 'product') {
            if (!empty($x['stock'])) $info .= '<li>' . e($u['stock']) . ': ' . e($x['stock']) . '</li>';
            if (!empty($x['sku'])) $info .= '<li>' . e($u['sku']) . ': <span dir="ltr">' . e($x['sku']) . '</span></li>';
        }
        if ($type === 'stay' && !empty($x['amenities'])) $info .= '<li>' . e($u['amen']) . ': ' . e($x['amenities']) . '</li>';
        $main = cms_hero($tr['title'], cms_meta_line($row2, $type, $lang), $tr['excerpt'])
            . '<article class="container prose cms-article">' . ($it['image'] ? '<div class="cms-cover">' . cms_img((int)$it['image'], $lang) . '</div>' : '')
            . ($info ? '<ul class="cms-info">' . $info . '</ul>' : '') . '<div class="cms-body">' . $tr['body'] . '</div>'
            . ($type === 'event' ? cms_form($type, $lang, $tr['title']) : '')   // اقامتگاه و صنایع دستی فقط معرفی می‌شوند؛ فرم رزرو/سفارش ندارند
            . '<p><a class="more" href="' . e(cms_url($lang, $section)) . '">← ' . e($u['back']) . '</a></p></article>';
    } else {
        // صفحه بخش: متن ویرایش‌شده + فهرست محتوا
        $pg = null;
        $s = db()->prepare("SELECT i.id FROM items i JOIN item_tr t ON t.item_id=i.id AND t.lang=? WHERE i.type='page' AND i.page_key=? AND i.status='published' AND t.title<>'' ORDER BY i.updated DESC LIMIT 1");
        $s->execute([$lang, $section]); $pid = $s->fetchColumn();
        if ($pid) { $pi = item_load((int)$pid); $pg = $pi['tr'][$lang]; $title = $pg['title']; $desc = $pg['excerpt']; }
        $items = $type ? cms_items($type, $lang) : [];
        $xpInner = $section === 'experiences' ? xp_cards_html($lang) : '';   // تجربه‌ها و مجری‌های پنل (اگر منتشر شده باشد)
        if (!$pg && !$items && $xpInner === '') { header('Content-Type: text/html; charset=utf-8'); echo $tpl; return; }   // بدون تغییر: همان صفحه استاتیک
        $list = '';
        if ($xpInner !== '') {
            if ($pg) $list .= '<div class="container xp-page"><ul class="xp-grid">' . $xpInner . '</ul></div>';   // متن ویرایش‌شدهٔ صفحه + کارت‌ها
            else {
                $swapped = xp_replace_ul($tpl, 'xp-grid', $xpInner);
                if ($swapped === null) { header('Content-Type: text/html; charset=utf-8'); echo $tpl; return; }
                $tpl = $swapped; $keep = true;
            }
        }
        if ($items) {
            $list = '<section class="container cms-list"><h2>' . e($pg ? $u['latest'] : '') . '</h2>' . ($type === 'gallery' ? cms_gallery($items, $lang) : cms_cards($items, $type, $lang, $section)) . ($type === 'product' && setting('shop_note_' . $lang) !== '' ? '<p class="notice">' . e(setting('shop_note_' . $lang)) . '</p>' : '') . '</section>';
            $list = str_replace('<h2></h2>', '', $list);
        }
        if ($pg) {
            $main = cms_hero($pg['title'], '', $pg['excerpt']) . '<div class="container prose"><div class="block cms-body">' . $pg['body'] . '</div></div>' . $list;
        } else {
            $main = null; $inject = $list;   // محتوای فعلی صفحه حفظ می‌شود و فهرست به آن اضافه می‌شود
        }
        foreach (LANGS as $l => $_) $alts[$l] = cms_url($l, $section);
    }

    // --- ساخت خروجی بر پایه قالب استاتیک ---
    $out = $tpl;
    if (!$keep) {
        $out = preg_replace('#<script\b[^>]*>.*?</script>#si', '', $out);
        $out = preg_replace('#<link\b[^>]*rel="preload"[^>]*as="script"[^>]*>#i', '', $out);
    }
    $out = preg_replace('#<html\b[^>]*>#i', '<html lang="' . $lang . '" dir="' . $u['dir'] . '">', $out, 1);
    if ($main !== null || !empty($inject)) {
        $out = preg_replace_callback('#(<main\b[^>]*>)(.*)(</main>)#si', function ($m) use ($main, $inject, $items) {
            if ($main !== null) return $m[1] . $main . $m[3];
            $inner = !empty($items) ? preg_replace('#<p class="notice">.*?</p>#si', '', $m[2]) : $m[2];
            return $m[1] . $inner . $inject . $m[3];
        }, $out, 1);
    }
    if ($title !== null) {
        $siteName = '';
        if (preg_match('#<title>(.*?)</title>#s', $out, $tm) && preg_match('#[\|\-–—]\s*([^\|\-–—]+)$#u', html_entity_decode($tm[1]), $sm)) $siteName = trim($sm[1]);
        $full = $title . ($siteName ? ' | ' . $siteName : '');
        $out = preg_replace_callback('#<title>.*?</title>#s', fn() => '<title>' . e($full) . '</title>', $out, 1);
        $out = preg_replace_callback('#<meta\s+name="description"[^>]*>#i', fn() => '<meta name="description" content="' . e($desc) . '">', $out, 1);
        $out = preg_replace_callback('#<meta\s+property="og:title"[^>]*>#i', fn() => '<meta property="og:title" content="' . e($full) . '">', $out, 1);
        $out = preg_replace_callback('#<meta\s+property="og:description"[^>]*>#i', fn() => '<meta property="og:description" content="' . e($desc) . '">', $out, 1);
    }
    $out = preg_replace_callback('#<link\s+rel="canonical"[^>]*>#i', fn() => '<link rel="canonical" href="https://' . e(preg_replace('/^www\./', '', $host)) . e($canon) . '">', $out, 1);
    // پیوند زبان‌ها
    $out = preg_replace_callback('#<a\b[^>]*hreflang="(fa|en|ru)"[^>]*>#i', function ($m) use ($alts, $section) {
        $l = strtolower($m[1]); $url = $alts[$l] ?? cms_url($l, $section);
        return preg_replace('#href="[^"]*"#', 'href="' . e($url) . '"', $m[0], 1);
    }, $out);
    $css = '<style>.cms-thumb{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;margin-bottom:10px}.cms-cover img{width:100%;height:auto;border-radius:var(--radius);margin-bottom:18px}.cms-body img{max-width:100%;height:auto;border-radius:10px}.cms-body{overflow-wrap:anywhere}.cms-info{list-style:none;padding:0;margin:0 0 16px;color:var(--muted)}.cms-gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}.cms-gallery figure{margin:0}.cms-gallery img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px}.cms-gallery figcaption{font-size:.9rem;color:var(--muted);padding-top:4px}.cms-list{padding-block:24px 56px}.cms-req{margin-top:32px}</style>';
    $js = '<script>(function(){var b=document.querySelector(".nav-toggle"),n=document.getElementById("site-nav");if(b&&n)b.addEventListener("click",function(){var o=n.classList.toggle("open");b.setAttribute("aria-expanded",o)});document.querySelectorAll(".cms-req").forEach(function(f){f.addEventListener("submit",function(e){e.preventDefault();var s=f.querySelector(".form-status"),btn=f.querySelector("button");btn.disabled=true;fetch("/contact.php",{method:"POST",body:new FormData(f),credentials:"same-origin"}).then(function(r){return r.json()}).then(function(d){s.className="form-status "+(d.ok?"ok":"error");s.textContent=d.ok?f.dataset.ok:f.dataset.err;if(d.ok)f.reset()}).catch(function(){s.className="form-status error";s.textContent=f.dataset.err}).finally(function(){btn.disabled=false})})})})();</script>';
    if (!$keep) {   // با حفظ اسکریپت‌های Next، منو و ظاهر را خود سایت اداره می‌کند
        $out = preg_replace_callback('#</head>#i', fn() => $css . '</head>', $out, 1);
        $out = preg_replace_callback('#</body>#i', fn() => $js . '</body>', $out, 1);
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache');
    echo $out;
}

/* صفحهٔ اصلی: نوار «تجربه‌ها» از پنل؛ هر مشکلی پیش بیاید همان صفحهٔ استاتیک نمایش داده می‌شود */
function cms_render_home(): void {
    $hl = (string)($_GET['hl'] ?? '');
    $lang = in_array($hl, ['en', 'ru'], true) ? $hl : 'fa';
    $file = CMS_ROOT . '/' . ($lang === 'fa' ? '' : $lang . '/') . 'index.html';
    if (!is_file($file)) cms_404();
    $html = (string)file_get_contents($file);
    try {
        $inner = xp_strip_html($lang);
        if ($inner !== '' && ($swapped = xp_replace_ul($html, 'xp-strip', $inner)) !== null) $html = $swapped;
    } catch (Throwable $ex) { /* همان صفحهٔ استاتیک */ }
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-cache');
    echo $html;
}

/* فهرست جستجوی محتوای پنل (نوشته‌ها، صنایع دستی، اقامتگاه‌ها، رویدادها) برای صفحه جستجوی سایت */
function cms_search_index(): void {
    $out = [];
    foreach (SECTION_TYPES as $section => $type) {
        if ($type === 'gallery') continue;
        foreach (array_keys(LANGS) as $lang) {
            foreach (cms_items($type, $lang, 1000) as $it) {
                $raw = ($it['excerpt'] !== '' ? $it['excerpt'] . ' ' : '') . $it['body'];
                $text = trim((string)preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                $out[] = ['l' => $lang, 'k' => $type, 't' => $it['title'], 'u' => cms_url($lang, $section, $it['slug']), 'x' => mb_substr($text, 0, 1500)];
            }
        }
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache');
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function cms_sitemap(): void {
    $host = SITE_HOST;
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (SECTION_TYPES as $section => $type) {
        if ($type === 'gallery') continue;
        foreach (array_keys(LANGS) as $lang) {
            foreach (cms_items($type, $lang, 1000) as $it) {
                echo '<url><loc>' . e('https://' . $host . cms_url($lang, $section, $it['slug'])) . '</loc><lastmod>' . date('Y-m-d', (int)$it['updated']) . '</lastmod></url>';
            }
        }
    }
    echo '</urlset>';
}
