<?php
/* بخش «اخبار هلیله»: خبرها از سایت‌های خبری با لینک اضافه می‌شوند؛ تیتر، چکیده، عکس، منبع و تاریخ خودکار از خود صفحهٔ خبر
   (Open Graph / JSON-LD) گرفته می‌شود. صفحهٔ عمومی شبیه روزنامه است و هر بخش آن سایت خبری اصلی را باز می‌کند. */
declare(strict_types=1);

const NEWS_PER_PAGE = 9;
const NEWS_FETCH_MAX_BYTES = 900000;

function news_ready(): void {
    static $done = false;
    if ($done) return;
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS news(
        id INTEGER PRIMARY KEY, url TEXT NOT NULL UNIQUE, title TEXT NOT NULL DEFAULT '', summary TEXT NOT NULL DEFAULT '', image TEXT NOT NULL DEFAULT '',
        source TEXT NOT NULL DEFAULT '', lang TEXT NOT NULL DEFAULT 'fa', published INTEGER NOT NULL DEFAULT 0, created INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT 'published', pinned INTEGER NOT NULL DEFAULT 0, fetched INTEGER NOT NULL DEFAULT 0, tries INTEGER NOT NULL DEFAULT 0, last_try INTEGER NOT NULL DEFAULT 0);
      CREATE INDEX IF NOT EXISTS news_status ON news(status, pinned, published)");
    $done = true;
    if (setting('news_seeded', '') !== '1') {   // یک بار: دو خبر اولیه (بقیه با لینک از پنل)؛ حذف‌شده‌ها دوباره برنمی‌گردند
        set_setting('news_seeded', '1');
        $f = __DIR__ . '/news-seed.json';
        $seed = is_file($f) ? json_decode((string)file_get_contents($f), true) : [];
        $ins = $pdo->prepare("INSERT OR IGNORE INTO news(url,title,source,lang,created,status,fetched) VALUES(?,?,?,?,?,'published',0)");
        foreach ((array)$seed as $i => $r) $ins->execute([$r['url'], $r['title'], $r['source'] ?? '', 'fa', time() - $i]);
    }
}

/* ---------- گرفتن اطلاعات خبر از لینک ---------- */
function news_host_is_public(string $host): bool {
    if (getenv('HC_NEWS_ALLOW_PRIVATE') === '1') return true;   // فقط برای آزمایش محلی
    $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
    if (!$ips) return false;
    foreach ($ips as $ip) if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
    return true;
}
function news_abs(string $href, string $base): string {
    $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($href === '') return '';
    if (preg_match('#^https?://#i', $href)) return $href;
    $b = parse_url($base); if (!$b || empty($b['host'])) return '';
    $origin = ($b['scheme'] ?? 'https') . '://' . $b['host'] . (!empty($b['port']) ? ':' . $b['port'] : '');
    if (str_starts_with($href, '//')) return ($b['scheme'] ?? 'https') . ':' . $href;
    if ($href[0] === '/') return $origin . $href;
    $dir = preg_replace('#/[^/]*$#', '/', $b['path'] ?? '/');
    return $origin . $dir . $href;
}
/** دریافت HTML با کنترل مقصد (SSRF)، حداکثر ۴ تغییر مسیر و سقف حجم؛ خروجی [html, finalUrl] یا رشتهٔ خطا */
function news_http_get(string $url): array|string {
    for ($hop = 0; $hop < 5; $hop++) {
        $p = parse_url($url);
        if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['http', 'https'], true) || empty($p['host'])) return 'لینک معتبر نیست.';
        $port = $p['port'] ?? (strtolower($p['scheme']) === 'https' ? 443 : 80);
        if (!in_array($port, [80, 443], true) && getenv('HC_NEWS_ALLOW_PRIVATE') !== '1') return 'فقط پورت‌های استاندارد وب مجازند.';
        if (!news_host_is_public($p['host'])) return 'آدرس این سایت از اینجا قابل دسترسی نیست.';
        if (!function_exists('curl_init')) return 'افزونهٔ cURL روی هاست فعال نیست.';
        $body = ''; $loc = ''; $ctype = '';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 9, CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36 HeleylehNews/1.0',
            CURLOPT_HTTPHEADER => ['Accept: text/html,application/xhtml+xml;q=0.9,*/*;q=0.5', 'Accept-Language: fa,en;q=0.8'],
            CURLOPT_ENCODING => '', CURLOPT_SSL_VERIFYPEER => true, CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => function ($c, $h) use (&$loc, &$ctype) {
                if (stripos($h, 'location:') === 0) $loc = trim(substr($h, 9));
                if (stripos($h, 'content-type:') === 0) $ctype = trim(substr($h, 13));
                return strlen($h);
            },
            CURLOPT_WRITEFUNCTION => function ($c, $d) use (&$body) {
                $body .= $d;
                if (strlen($body) > NEWS_FETCH_MAX_BYTES || stripos($body, '</head>') !== false) return 0;   // اطلاعات در <head> است؛ بیشتر لازم نیست
                return strlen($d);
            },
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_errno($ch);
        curl_close($ch);
        if ($code >= 300 && $code < 400 && $loc !== '') { $url = news_abs($loc, $url); continue; }
        if ($code >= 400) return 'سایت خبر خطای ' . $code . ' داد.';
        if ($body === '') return $err ? 'اتصال به سایت خبر برقرار نشد.' : 'پاسخی از سایت خبر نیامد.';
        if (preg_match('/charset=([\w-]+)/i', $ctype, $m)) $body = "<meta charset=\"{$m[1]}\">" . $body;
        return [$body, $url];
    }
    return 'تغییر مسیرهای بیش از حد.';
}
function news_clean(string $s, int $max): string {
    $s = html_entity_decode(strip_tags($s), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = trim((string)preg_replace('/\s+/u', ' ', $s));
    return mb_strlen($s) > $max ? rtrim(mb_substr($s, 0, $max - 1)) . '…' : $s;
}
/** [title, summary, image, source, published(ts), url] یا رشتهٔ خطا */
function news_fetch_meta(string $url): array|string {
    $r = news_http_get($url);
    if (is_string($r)) return $r;
    [$html, $final] = $r;
    if (!preg_match('/<meta[^>]+charset=["\']?utf-?8/i', $html) && preg_match('/<meta[^>]+charset=["\']?([\w-]+)/i', $html, $cm) && strtolower($cm[1]) !== 'utf-8') {
        $conv = @mb_convert_encoding($html, 'UTF-8', $cm[1]); if (is_string($conv)) $html = $conv;
    }
    $meta = [];
    if (preg_match_all('/<meta\b[^>]*>/i', $html, $mm)) foreach ($mm[0] as $tag) {
        if (!preg_match('/\b(?:property|name|itemprop)\s*=\s*["\']([^"\']+)["\']/i', $tag, $k)) continue;
        if (!preg_match('/\bcontent\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/is', $tag, $v)) continue;
        $key = strtolower(trim($k[1])); $val = $v[1] !== '' ? $v[1] : ($v[2] ?? '');
        if (!isset($meta[$key]) && trim($val) !== '') $meta[$key] = $val;
    }
    $ld = [];
    if (preg_match_all('#<script[^>]+application/ld\+json[^>]*>(.*?)</script>#is', $html, $lm)) foreach ($lm[1] as $j) {
        $d = json_decode(trim($j), true); if (!$d) continue;
        $stack = isset($d['@graph']) ? $d['@graph'] : (array_is_list($d) ? $d : [$d]);
        foreach ($stack as $n) if (is_array($n) && preg_match('/Article|NewsArticle|BlogPosting|Report/i', is_array($n['@type'] ?? '') ? implode(',', $n['@type']) : (string)($n['@type'] ?? ''))) { $ld = $n; break 2; }
    }
    $ldImg = '';
    if (!empty($ld['image'])) { $im = $ld['image']; $ldImg = is_string($im) ? $im : (is_array($im) ? (string)($im['url'] ?? ($im[0]['url'] ?? ($im[0] ?? ''))) : ''); }
    $htmlTitle = preg_match('#<title[^>]*>(.*?)</title>#is', $html, $tm) ? $tm[1] : '';
    $title = news_clean((string)($meta['og:title'] ?? $meta['twitter:title'] ?? $ld['headline'] ?? $htmlTitle), 220);
    $sum = news_clean((string)($meta['og:description'] ?? $meta['twitter:description'] ?? $meta['description'] ?? $ld['description'] ?? ''), 420);
    $img = news_abs((string)($meta['og:image:secure_url'] ?? $meta['og:image'] ?? $meta['twitter:image'] ?? $meta['twitter:image:src'] ?? $ldImg), $final);
    $src = news_clean((string)($meta['og:site_name'] ?? $meta['application-name'] ?? ''), 60);
    if ($src === '') $src = preg_replace('/^www\./i', '', (string)parse_url($final, PHP_URL_HOST));
    $dateRaw = (string)($meta['article:published_time'] ?? $meta['og:article:published_time'] ?? $meta['datepublished'] ?? $meta['date'] ?? $ld['datePublished'] ?? $meta['article:modified_time'] ?? '');
    $ts = $dateRaw !== '' ? (int)strtotime($dateRaw) : 0;
    if ($ts > time() + 86400 || $ts < 0) $ts = 0;
    if ($title === '') return 'تیتر خبر پیدا نشد (سایت اطلاعات استاندارد ندارد).';
    return ['title' => $title, 'summary' => $sum, 'image' => $img, 'source' => $src, 'published' => $ts, 'url' => $final];
}
function news_norm_url(string $u): string {
    $u = trim($u);
    if (!preg_match('#^https?://#i', $u)) return '';
    $p = parse_url($u); if (!$p || empty($p['host'])) return '';
    $q = '';
    if (!empty($p['query'])) { parse_str($p['query'], $a); foreach (array_keys($a) as $k) if (preg_match('/^(utm_|fbclid|gclid|igshid)/i', (string)$k)) unset($a[$k]); $q = $a ? '?' . http_build_query($a) : ''; }
    return strtolower($p['scheme']) . '://' . $p['host'] . (!empty($p['port']) ? ':' . $p['port'] : '') . ($p['path'] ?? '') . $q;
}
/** افزودن خبر با لینک: [id, پیام] */
function news_add_url(string $raw): array {
    news_ready();
    $url = news_norm_url($raw);
    if ($url === '') return [0, 'لینک معتبر نیست: ' . mb_substr($raw, 0, 60)];
    $pdo = db();
    $ex = $pdo->prepare('SELECT id FROM news WHERE url=?'); $ex->execute([$url]);
    if ($ex->fetchColumn()) return [0, 'این خبر از قبل هست: ' . mb_substr($url, 0, 70)];
    $m = news_fetch_meta($url);
    $ins = $pdo->prepare('INSERT INTO news(url,title,summary,image,source,lang,published,created,status,fetched,tries,last_try) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)');
    if (is_string($m)) {   // خودکار نشد: به‌صورت پیش‌نویس ثبت می‌شود تا دستی کامل شود
        $host = preg_replace('/^www\./i', '', (string)parse_url($url, PHP_URL_HOST));
        $ins->execute([$url, $host, '', '', $host, 'fa', 0, time(), 'draft', 0, 1, time()]);
        return [(int)$pdo->lastInsertId(), 'اطلاعات خودکار گرفته نشد (' . $m . ') و به‌صورت پیش‌نویس ثبت شد؛ تیتر و چکیده را دستی بنویسید.'];
    }
    $lang = preg_match('/[\x{0600}-\x{06FF}]/u', $m['title']) ? 'fa' : (preg_match('/[\x{0400}-\x{04FF}]/u', $m['title']) ? 'ru' : 'en');
    $ins->execute([$url, $m['title'], $m['summary'], $m['image'], $m['source'], $lang, $m['published'], time(), 'published', 1, 0, time()]);
    return [(int)$pdo->lastInsertId(), 'ثبت شد: ' . mb_substr($m['title'], 0, 80)];
}
/** تکمیل خودکار خبرهایی که فقط لینک/تیتر دارند (حداکثر n مورد در هر درخواست؛ بعد از ۳ شکست رها می‌شود) */
function news_autofill(int $n = 2): void {
    try {
        news_ready();
        $pdo = db();
        $rows = $pdo->query('SELECT * FROM news WHERE fetched=0 AND tries<3 AND last_try<' . (time() - 600) . " AND status='published' ORDER BY id LIMIT " . $n)->fetchAll();
        foreach ($rows as $r) {
            $pdo->prepare('UPDATE news SET tries=tries+1,last_try=? WHERE id=?')->execute([time(), $r['id']]);
            $m = news_fetch_meta($r['url']);
            if (is_string($m)) continue;
            $pdo->prepare('UPDATE news SET title=?,summary=?,image=?,source=?,published=?,fetched=1 WHERE id=?')->execute([
                $r['title'] !== '' ? $r['title'] : $m['title'], $r['summary'] !== '' ? $r['summary'] : $m['summary'], $r['image'] !== '' ? $r['image'] : $m['image'],
                $r['source'] !== '' ? $r['source'] : $m['source'], $r['published'] ?: $m['published'], $r['id'],
            ]);
        }
    } catch (Throwable $e) { /* صفحه بدون تکمیل نمایش داده می‌شود */ }
}

/* ---------- صفحهٔ روزنامه ---------- */
const NEWS_UI = [
    'fa' => ['name' => 'اخبار هلیله', 'tag' => 'روزنامهٔ تصویری روستای هلیله، بوشهر', 'edition' => 'ویژه‌نامهٔ دیجیتال', 'page' => 'صفحهٔ', 'of' => 'از', 'lead' => 'خبر اصلی', 'latest' => 'خبرهای بیشتر', 'read' => 'خبر کامل در سایت خبری ↗', 'prev' => '‹ صفحهٔ قبل', 'next' => 'صفحهٔ بعد ›', 'empty' => 'هنوز خبری ثبت نشده است.', 'note' => 'خبرها از سایت‌های خبری گردآوری می‌شود. با کلیک روی هر خبر، متن کامل را در سایت منبع می‌خوانید.', 'count' => 'خبر', 'src' => 'منبع', 'desc' => 'آخرین خبرها و گزارش‌های رسانه‌ها دربارهٔ روستای گردشگری هلیله بوشهر؛ هر خبر به سایت خبری اصلی لینک است.'],
    'en' => ['name' => 'Helileh News', 'tag' => 'The illustrated gazette of Helileh village, Bushehr', 'edition' => 'Digital edition', 'page' => 'Page', 'of' => 'of', 'lead' => 'Top story', 'latest' => 'More news', 'read' => 'Read the full story at the source ↗', 'prev' => '‹ Previous', 'next' => 'Next ›', 'empty' => 'No news yet.', 'note' => 'News is gathered from news sites. Click any story to read it in full at its source.', 'count' => 'stories', 'src' => 'Source', 'desc' => 'Latest news and media reports about Helileh, the coastal tourism village in Bushehr. Every story links to the original news site.'],
    'ru' => ['name' => 'Новости Хелиле', 'tag' => 'Иллюстрированная газета деревни Хелиле, Бушер', 'edition' => 'Цифровой выпуск', 'page' => 'Страница', 'of' => 'из', 'lead' => 'Главная новость', 'latest' => 'Другие новости', 'read' => 'Читать полностью на сайте источника ↗', 'prev' => '‹ Назад', 'next' => 'Вперёд ›', 'empty' => 'Новостей пока нет.', 'note' => 'Новости собраны с новостных сайтов. Нажмите на любую, чтобы прочитать полностью у источника.', 'count' => 'новостей', 'src' => 'Источник', 'desc' => 'Свежие новости и репортажи СМИ о Хелиле, прибрежной туристической деревне в Бушере. Каждая новость ведёт на исходный сайт.'],
];
function news_num(int|string $n, string $lang): string { return $lang === 'fa' ? strtr((string)$n, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']) : (string)$n; }
function news_page_url(string $lang, int $page): string {
    return cms_prefix($lang) . SECTIONS[$lang]['news'] . '/' . ($page > 1 ? 'page/' . $page . '/' : '');
}
function news_card(array $n, string $lang, string $cls, bool $big = false): string {
    $u = NEWS_UI[$lang];
    $img = '';
    if ($n['image'] !== '') {
        $src = getenv('HC_NEWS_ALLOW_PRIVATE') ? $n['image'] : preg_replace('#^http://#i', 'https://', $n['image']);
        $img = '<img src="' . e($src) . '" alt="" loading="lazy" referrerpolicy="no-referrer" decoding="async">';
    }
    $date = $n['published'] ? format_date((int)$n['published'], $lang) : '';
    $host = $n['source'] !== '' ? $n['source'] : (string)parse_url($n['url'], PHP_URL_HOST);
    return '<a class="np-item ' . $cls . '" href="' . e($n['url']) . '" target="_blank" rel="noopener" data-sound="tick">'
        . '<span class="np-img' . ($img === '' ? ' noimg' : '') . '">' . $img . '<i aria-hidden="true">' . e(NEWS_UI[$lang]['name']) . '</i></span>'
        . '<span class="np-body"><span class="np-kick"><b>' . e($host) . '</b>' . ($date !== '' ? ' · ' . e($date) : '') . '</span>'
        . '<span class="np-hl">' . e($n['title']) . '</span>'
        . ($n['summary'] !== '' ? '<span class="np-sum">' . e($n['summary']) . '</span>' : '')
        . '<span class="np-go">' . e($u['read']) . '</span></span></a>';
}
/** [html, totalPages, page] */
function news_main(string $lang, int $page): array {
    news_ready(); news_autofill(2);
    $u = NEWS_UI[$lang];
    $total = (int)db()->query("SELECT COUNT(*) FROM news WHERE status='published' AND title<>''")->fetchColumn();
    $pages = max(1, (int)ceil($total / NEWS_PER_PAGE));
    $page = max(1, min($page, $pages));
    $s = db()->prepare("SELECT * FROM news WHERE status='published' AND title<>'' ORDER BY pinned DESC, CASE WHEN published>0 THEN published ELSE created END DESC, id DESC LIMIT ? OFFSET ?");
    $s->execute([NEWS_PER_PAGE, ($page - 1) * NEWS_PER_PAGE]);
    $rows = $s->fetchAll();
    $h = '<section class="np"><div class="np-wrap"><header class="np-mast"><h1>' . e($u['name']) . '</h1><p class="np-tag">' . e($u['tag']) . '</p></header>'
        . '<div class="np-bar"><span>' . e(format_date(time(), $lang)) . '</span><span>' . e($u['edition']) . '</span><span>' . e($u['page']) . ' ' . news_num($page, $lang) . ' ' . e($u['of']) . ' ' . news_num($pages, $lang) . '</span><span>' . news_num($total, $lang) . ' ' . e($u['count']) . '</span></div>';
    if (!$rows) {
        $h .= '<p class="np-empty">' . e($u['empty']) . '</p>';
    } elseif ($page === 1) {
        $lead = array_shift($rows); $side = array_splice($rows, 0, 2);
        $h .= '<div class="np-top"><div class="np-lead"><span class="np-flag">' . e($u['lead']) . '</span>' . news_card($lead, $lang, 'np-xl', true) . '</div>';
        if ($side) { $h .= '<div class="np-side">'; foreach ($side as $n) $h .= news_card($n, $lang, 'np-md'); $h .= '</div>'; }
        $h .= '</div>';
        if ($rows) { $h .= '<h2 class="np-sec"><span>' . e($u['latest']) . '</span></h2><div class="np-grid">'; foreach ($rows as $n) $h .= news_card($n, $lang, 'np-sm'); $h .= '</div>'; }
    } else {
        $h .= '<div class="np-grid">'; foreach ($rows as $n) $h .= news_card($n, $lang, 'np-sm'); $h .= '</div>';
    }
    if ($pages > 1) {
        $h .= '<nav class="np-pager" aria-label="' . e($u['page']) . '">';
        if ($page > 1) $h .= '<a rel="prev" href="' . e(news_page_url($lang, $page - 1)) . '">' . e($u['prev']) . '</a>';
        for ($i = 1; $i <= $pages; $i++) $h .= $i === $page ? '<b aria-current="page">' . news_num($i, $lang) . '</b>' : '<a href="' . e(news_page_url($lang, $i)) . '">' . news_num($i, $lang) . '</a>';
        if ($page < $pages) $h .= '<a rel="next" href="' . e(news_page_url($lang, $page + 1)) . '">' . e($u['next']) . '</a>';
        $h .= '</nav>';
    }
    $h .= '<p class="np-note">' . e($u['note']) . '</p></div></section>'
        . '<script>document.querySelectorAll(".np-img img").forEach(function(i){function f(){i.parentNode.classList.add("noimg");i.remove()}i.addEventListener("error",f);if(i.complete&&i.naturalWidth===0)f()})</script>';
    return [$h, $pages, $page];
}
function news_css(): string {
    return <<<'CSS'
<style>
.np{--paper:#f3ebd6;--ink:#1c1915;--red:#9b2d20;background:var(--paper) radial-gradient(rgba(60,40,10,.06) 1px,transparent 1.2px) 0 0/5px 5px;color:var(--ink);padding:26px 0 56px;border-block:1px solid #d8cba8}
.np-wrap{max-width:1140px;margin:0 auto;padding:0 18px}
.np-mast{text-align:center;border-top:7px solid var(--ink);border-bottom:3px double var(--ink);padding:10px 0 12px}
.np-mast h1{margin:0;font:400 clamp(2.8rem,10vw,6rem)/1.1 'Lalezar','Vazirmatn Variable',system-ui,sans-serif;letter-spacing:.01em;text-shadow:2px 2px 0 rgba(155,45,32,.18)}
.np-tag{margin:2px 0 0;font-weight:600;font-size:.98rem}
.np-bar{display:flex;flex-wrap:wrap;justify-content:space-between;gap:6px 14px;padding:7px 2px;border-bottom:1px solid var(--ink);font-size:.82rem;font-weight:700}
.np-top{display:grid;grid-template-columns:1.7fr 1fr;border-bottom:3px double var(--ink);margin-top:18px}
.np-lead{position:relative;padding:0 0 18px;padding-inline-end:20px}
.np-flag{position:absolute;z-index:2;top:12px;inset-inline-start:12px;background:var(--red);color:#fff;font:400 .95rem 'Lalezar','Vazirmatn Variable',sans-serif;padding:2px 12px;border-radius:3px;box-shadow:2px 2px 0 var(--ink)}
.np-side{border-inline-start:1px solid var(--ink);padding-inline-start:20px;display:flex;flex-direction:column}
.np-item{display:flex;flex-direction:column;gap:10px;color:inherit;text-decoration:none;padding:14px 0}
.np-item:hover .np-hl,.np-item:focus-visible .np-hl{color:var(--red);text-decoration:underline;text-decoration-thickness:2px;text-underline-offset:4px}
.np-item:focus-visible{outline:3px solid var(--red);outline-offset:3px}
.np-img{position:relative;display:block;overflow:hidden;background:#d9ccaa;border:1px solid var(--ink);aspect-ratio:16/10}
.np-img img{width:100%;height:100%;object-fit:cover;display:block;filter:grayscale(.9) sepia(.3) contrast(1.08);transition:filter .5s,transform .6s}
.np-item:hover .np-img img{filter:none;transform:scale(1.04)}
.np-img i{display:none}
.np-img.noimg{background:radial-gradient(circle,rgba(28,25,21,.28) 1.4px,transparent 1.8px) 0 0/8px 8px,linear-gradient(135deg,#e4d6b0,#c9b88c)}
.np-img.noimg i{display:flex;position:absolute;inset:0;align-items:center;justify-content:center;font:400 clamp(1.6rem,4vw,2.6rem) 'Lalezar','Vazirmatn Variable',sans-serif;color:rgba(28,25,21,.55);font-style:normal}
.np-body{display:flex;flex-direction:column;gap:6px}
.np-kick{font-size:.76rem;font-weight:700;color:#5b5143}.np-kick b{color:var(--red)}
.np-hl{font-weight:900;line-height:1.5;font-size:1.08rem;transition:color .2s}
.np-xl .np-hl{font-size:clamp(1.55rem,3.4vw,2.45rem);line-height:1.4}
.np-sum{font-size:.93rem;line-height:1.95;display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:3;overflow:hidden}
.np-xl .np-sum{-webkit-line-clamp:5;font-size:1.02rem}
.np-xl .np-sum::first-letter{float:inline-start;font:400 3.1em/.9 'Lalezar','Vazirmatn Variable',sans-serif;color:var(--red);padding-inline-end:8px}
.np-go{font-size:.78rem;font-weight:800;color:var(--red);border-top:1px solid rgba(28,25,21,.25);padding-top:6px;margin-top:2px}
.np-md{border-bottom:1px solid var(--ink)}.np-md:last-child{border-bottom:0}.np-md .np-img{aspect-ratio:16/9}
.np-sec{margin:22px 0 4px;text-align:center;border-bottom:1px solid var(--ink);line-height:0}
.np-sec span{display:inline-block;background:var(--paper);padding:0 16px;line-height:1.2;font:400 1.5rem 'Lalezar','Vazirmatn Variable',sans-serif;transform:translateY(.62em)}
.np-grid{display:grid;grid-template-columns:repeat(3,1fr);margin-top:6px}
.np-sm{padding:16px 0;padding-inline:18px;border-bottom:1px solid var(--ink);border-inline-end:1px solid var(--ink)}
.np-sm:nth-child(3n){border-inline-end:0}.np-sm:nth-child(3n+1){padding-inline-start:0}.np-sm:nth-child(3n){padding-inline-end:0}
.np-sm .np-img{aspect-ratio:4/3}
.np-pager{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin:30px 0 6px}
.np-pager a,.np-pager b{min-width:42px;padding:7px 14px;border:2px solid var(--ink);text-align:center;color:var(--ink);text-decoration:none;font-weight:800;background:transparent}
.np-pager a:hover{background:var(--ink);color:var(--paper)}.np-pager b{background:var(--ink);color:var(--paper)}
.np-note{text-align:center;font-size:.82rem;color:#5b5143;margin:20px 0 0}.np-empty{text-align:center;padding:60px 0;font-weight:700}
@media(max-width:860px){.np-top{grid-template-columns:1fr}.np-lead{padding-inline-end:0}.np-side{border-inline-start:0;padding-inline-start:0;border-top:1px solid var(--ink)}.np-grid{grid-template-columns:1fr 1fr}.np-sm:nth-child(3n){border-inline-end:1px solid var(--ink);padding-inline-end:18px}.np-sm:nth-child(3n+1){padding-inline-start:18px}.np-sm:nth-child(2n){border-inline-end:0;padding-inline-end:0}.np-sm:nth-child(2n+1){padding-inline-start:0}}
@media(max-width:560px){.np-grid{grid-template-columns:1fr}.np-sm,.np-sm:nth-child(n){border-inline-end:0;padding-inline:0}}
@media(prefers-reduced-motion:reduce){.np-img img,.np-hl{transition:none}.np-item:hover .np-img img{transform:none}}
</style>
CSS;
}
