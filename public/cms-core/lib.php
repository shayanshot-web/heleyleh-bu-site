<?php
/* هسته پنل مدیریت سایت هلیله: دیتابیس، ورود، نقش‌ها، رسانه، پاک‌سازی HTML */
declare(strict_types=1);

define('CMS_ROOT', dirname(__DIR__));                 // public_html
define('CMS_UPLOADS', CMS_ROOT . '/uploads');
$__parent = dirname(CMS_ROOT);
define('CMS_DATA', (is_dir($__parent) && is_writable($__parent)) ? $__parent . '/cms_data' : CMS_ROOT . '/cms-data');
const SITE_HOST = 'websiteco.ir';   // دامنهٔ اصلی؛ به‌جای HTTP_HOST (که مهاجم می‌تواند جعل کند) در canonical، نقشه و ایمیل استفاده می‌شود
const LANGS = ['fa' => 'فارسی', 'en' => 'English', 'ru' => 'Русский'];
const TYPES = [
    'post'    => ['label' => 'نوشته‌ها',      'one' => 'نوشته',     'icon' => '✎'],
    'page'    => ['label' => 'صفحات',         'one' => 'صفحه',      'icon' => '❏'],
    'product' => ['label' => 'محصولات',       'one' => 'محصول',     'icon' => '◈'],
    'event'   => ['label' => 'رویدادها',      'one' => 'رویداد',    'icon' => '◷'],
    'stay'    => ['label' => 'اقامتگاه‌ها',   'one' => 'اقامتگاه',  'icon' => '⌂'],
    'gallery' => ['label' => 'گالری',         'one' => 'تصویر',     'icon' => '▣'],
    'experience' => ['label' => 'تجربه‌ها',   'one' => 'تجربه',     'icon' => '☼'],
    'person'  => ['label' => 'مجری‌های تجربه‌ها', 'one' => 'مجری',   'icon' => '☺'],
];
// آدرس بخش‌ها در هر زبان (همان اسلاگ‌های سایت)
const SECTIONS = [
    'fa' => ['about' => 'about', 'stay' => 'accommodations', 'experiences' => 'experiences', 'gallery' => 'gallery', 'blog' => 'blog', 'events' => 'events', 'shop' => 'handicraft-shop'],
    'en' => ['about' => 'about-us', 'stay' => 'stay', 'experiences' => 'things-to-do', 'gallery' => 'photo-gallery', 'blog' => 'travel-blog', 'events' => 'events-calendar', 'shop' => 'craft-shop'],
    'ru' => ['about' => 'o-nas', 'stay' => 'prozhivanie', 'experiences' => 'vpechatleniya', 'gallery' => 'galereya', 'blog' => 'blog-helile', 'events' => 'sobytiya', 'shop' => 'remesla'],
];
const TYPE_SECTION = ['post' => 'blog', 'product' => 'shop', 'event' => 'events', 'stay' => 'stay', 'gallery' => 'gallery'];
const PAGE_KEYS = ['about' => 'درباره روستا', 'stay' => 'اقامت (متن بالای صفحه)', 'experiences' => 'تجربه‌ها', 'gallery' => 'گالری (متن بالای صفحه)', 'blog' => 'بلاگ (متن بالای صفحه)', 'events' => 'رویدادها (متن بالای صفحه)', 'shop' => 'فروشگاه (متن بالای صفحه)'];
const ROLES = ['admin' => 'مدیر کل', 'editor' => 'ویراستار', 'manager' => 'مدیر فروشگاه و اقامت', 'author' => 'نویسنده'];

/* ---------- دیتابیس ---------- */
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    if (!is_dir(CMS_DATA)) {
        @mkdir(CMS_DATA, 0750, true);
    }
    $ht = CMS_DATA . '/.htaccess';
    if (!is_file($ht)) @file_put_contents($ht, "Require all denied\n");
    $pdo = new PDO('sqlite:' . CMS_DATA . '/cms.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys=ON; PRAGMA journal_mode=WAL; PRAGMA busy_timeout=5000;');
    migrate($pdo);
    return $pdo;
}
function migrate(PDO $p): void {
    $p->exec("
    CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY, username TEXT UNIQUE NOT NULL, name TEXT NOT NULL, email TEXT, role TEXT NOT NULL, pass TEXT NOT NULL, created INTEGER NOT NULL);
    CREATE TABLE IF NOT EXISTS items(id INTEGER PRIMARY KEY, type TEXT NOT NULL, page_key TEXT, status TEXT NOT NULL DEFAULT 'draft', image INTEGER, extra TEXT NOT NULL DEFAULT '{}', author_id INTEGER, created INTEGER NOT NULL, updated INTEGER NOT NULL, published_at INTEGER);
    CREATE TABLE IF NOT EXISTS item_tr(item_id INTEGER NOT NULL REFERENCES items(id) ON DELETE CASCADE, lang TEXT NOT NULL, title TEXT NOT NULL DEFAULT '', slug TEXT NOT NULL DEFAULT '', excerpt TEXT NOT NULL DEFAULT '', body TEXT NOT NULL DEFAULT '', PRIMARY KEY(item_id, lang));
    CREATE INDEX IF NOT EXISTS idx_tr_slug ON item_tr(lang, slug);
    CREATE TABLE IF NOT EXISTS media(id INTEGER PRIMARY KEY, file TEXT NOT NULL, thumb TEXT, mime TEXT, size INTEGER, w INTEGER, h INTEGER, alt_fa TEXT DEFAULT '', alt_en TEXT DEFAULT '', alt_ru TEXT DEFAULT '', uploaded_by INTEGER, created INTEGER NOT NULL);
    CREATE TABLE IF NOT EXISTS messages(id INTEGER PRIMARY KEY, name TEXT, phone TEXT, email TEXT, subject TEXT, message TEXT, lang TEXT, ref TEXT, status TEXT NOT NULL DEFAULT 'new', created INTEGER NOT NULL);
    CREATE TABLE IF NOT EXISTS comments(id INTEGER PRIMARY KEY, name TEXT NOT NULL, text TEXT NOT NULL, rating INTEGER NOT NULL DEFAULT 0, lang TEXT NOT NULL DEFAULT 'fa', status TEXT NOT NULL DEFAULT 'pending', ip TEXT, created INTEGER NOT NULL);
    CREATE TABLE IF NOT EXISTS settings(k TEXT PRIMARY KEY, v TEXT);
    CREATE TABLE IF NOT EXISTS sessions(token TEXT PRIMARY KEY, data TEXT NOT NULL DEFAULT '{}', expires INTEGER NOT NULL);
    CREATE TABLE IF NOT EXISTS attempts(ip TEXT NOT NULL, ts INTEGER NOT NULL);
    ");
}

/* ---------- کمکی‌ها ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function now(): int { return time(); }
function setting(string $k, string $d = ''): string {
    $s = db()->prepare('SELECT v FROM settings WHERE k=?'); $s->execute([$k]);
    $r = $s->fetchColumn(); return $r === false ? $d : (string)$r;
}
function set_setting(string $k, string $v): void {
    db()->prepare('INSERT INTO settings(k,v) VALUES(?,?) ON CONFLICT(k) DO UPDATE SET v=excluded.v')->execute([$k, $v]);
}
function slugify(string $s): string {
    $s = trim(mb_strtolower($s, 'UTF-8'));
    $s = preg_replace('/[\s_]+/u', '-', $s);
    $s = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $s);
    $s = preg_replace('/-+/', '-', $s);
    return trim($s, '-');
}
function format_date(?int $ts, string $lang = 'fa'): string {
    if (!$ts) return '';
    if ($lang === 'fa' && class_exists('IntlDateFormatter')) {
        $f = new IntlDateFormatter('fa_IR@calendar=persian', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Asia/Tehran', IntlDateFormatter::TRADITIONAL);
        $r = $f->format($ts); if ($r) return $r;
    }
    return date('Y-m-d', $ts);
}

/* ---------- نشست و ورود (نشست در دیتابیس؛ مستقل از تنظیمات session هاست) ---------- */
const AUTH_COOKIE = 'hcms_auth';
function is_https(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (($_SERVER['HTTP_CF_VISITOR'] ?? '') !== '' && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false) || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}
function app_secret(): string {
    $s = setting('app_secret');
    if (strlen($s) < 32) { $s = bin2hex(random_bytes(32)); set_setting('app_secret', $s); }
    return $s;
}
function set_auth_cookie(string $token, int $expires): void {
    setcookie(AUTH_COOKIE, $token, ['expires' => $expires, 'path' => '/', 'secure' => is_https(), 'httponly' => true, 'samesite' => 'Lax']);
}
function start_session(): void {
    static $started = false;
    if ($started) return;
    $started = true;
    $_SESSION = [];
    $GLOBALS['__sess_token'] = '';
    $GLOBALS['__sess_orig'] = '[]';
    db()->prepare('DELETE FROM sessions WHERE expires<?')->execute([now()]);
    $tok = $_COOKIE[AUTH_COOKIE] ?? '';
    if (is_string($tok) && preg_match('/^[a-f0-9]{48}$/', $tok)) {
        $r = db()->prepare('SELECT data FROM sessions WHERE token=? AND expires>=?');
        $r->execute([hash('sha256', $tok), now()]);
        $row = $r->fetch();
        if ($row) {
            $GLOBALS['__sess_token'] = $tok;
            $GLOBALS['__sess_orig'] = (string)$row['data'];
            $_SESSION = json_decode($row['data'], true) ?: [];
        }
    }
    register_shutdown_function(function () {
        $tok = $GLOBALS['__sess_token'] ?? '';
        if ($tok === '') return;
        $json = json_encode($_SESSION ?: [], JSON_UNESCAPED_UNICODE);
        if ($json !== $GLOBALS['__sess_orig']) {
            db()->prepare('UPDATE sessions SET data=? WHERE token=?')->execute([$json, hash('sha256', $tok)]);
        }
    });
}
function session_open(array $data): void {   // ساخت نشست جدید پس از ورود موفق
    start_session();
    $old = $GLOBALS['__sess_token'] ?? '';
    if ($old !== '') db()->prepare('DELETE FROM sessions WHERE token=?')->execute([hash('sha256', $old)]);
    $tok = bin2hex(random_bytes(24)); $exp = now() + 8 * 3600;
    db()->prepare('INSERT INTO sessions(token,data,expires) VALUES(?,?,?)')->execute([hash('sha256', $tok), json_encode($data), $exp]);
    $GLOBALS['__sess_token'] = $tok; $GLOBALS['__sess_orig'] = json_encode($data); $_SESSION = $data;
    set_auth_cookie($tok, 0);
}
function current_user(): ?array {
    start_session();
    if (empty($_SESSION['uid'])) return null;
    static $u = null;
    if ($u === null || (int)$u['id'] !== (int)$_SESSION['uid']) {
        $s = db()->prepare('SELECT id,username,name,email,role FROM users WHERE id=?'); $s->execute([$_SESSION['uid']]);
        $u = $s->fetch() ?: null;
        if (!$u) { unset($_SESSION['uid']); }
    }
    return $u;
}
/* CSRF بدون وابستگی به کوکی: بعد از ورود وابسته به نشست، قبل از ورود وابسته به بازه زمانی */
function csrf_for(string $scope, int $window = 0): string {
    return hash_hmac('sha256', $scope . '|' . $window, app_secret());
}
function csrf(): string {
    start_session();
    $scope = ($GLOBALS['__sess_token'] ?? '') !== '' && !empty($_SESSION['uid']) ? 'u|' . $GLOBALS['__sess_token'] : 'anon';
    return csrf_for($scope, $scope === 'anon' ? intdiv(now(), 7200) : 0);
}
function csrf_check(): void {
    start_session();
    $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    $ok = false;
    if (is_string($t) && $t !== '') {
        if (!empty($_SESSION['uid']) && ($GLOBALS['__sess_token'] ?? '') !== '') $ok = hash_equals(csrf_for('u|' . $GLOBALS['__sess_token']), $t);
        else { $w = intdiv(now(), 7200); $ok = hash_equals(csrf_for('anon', $w), $t) || hash_equals(csrf_for('anon', $w - 1), $t) || hash_equals(csrf_for('anon', $w - 2), $t); }
    }
    if (!$ok) {
        // A0: توکن فرم نیامد/نامعتبر | A1: وارد نیستید یا نشست تمام شده (کوکی نیامده) | A2: وارد هستید ولی توکن فرم مال نشست دیگری است
        $code = (!is_string($t) || $t === '') ? 'A0' : (empty($_COOKIE[AUTH_COOKIE]) ? 'A1' : 'A2');
        http_response_code(419);
        header('Content-Type: text/html; charset=utf-8');
        exit('نشست منقضی شده؛ صفحه را دوباره باز کنید. <small dir="ltr">[' . $code . ' · ' . (is_https() ? 'h1' : 'h0') . ']</small>');
    }
}
function client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0'; }
function too_many_attempts(): bool {
    db()->prepare('DELETE FROM attempts WHERE ts<?')->execute([now() - 900]);
    $s = db()->prepare('SELECT COUNT(*) FROM attempts WHERE ip=?'); $s->execute([client_ip()]);
    return (int)$s->fetchColumn() >= 8;
}
/** سقف درخواست برای هر آی‌پی در یک بازهٔ کوتاه (برای فرم تماس و …). true یعنی از سقف گذشته؛ وگرنه همین درخواست هم شمرده می‌شود.
    ردیف‌های attempts بعد از ۱۵ دقیقه پاک می‌شوند، پس بازه حداکثر ۸۴۰ ثانیه است. */
function rate_limited(string $bucket, int $max, int $window = 600): bool {
    $window = min($window, 840);
    $key = 'rl:' . $bucket . ':' . hash('sha256', client_ip() . app_secret());
    db()->prepare('DELETE FROM attempts WHERE ts<?')->execute([now() - 900]);
    $s = db()->prepare('SELECT COUNT(*) FROM attempts WHERE ip=? AND ts>?'); $s->execute([$key, now() - $window]);
    if ((int)$s->fetchColumn() >= $max) return true;
    db()->prepare('INSERT INTO attempts(ip,ts) VALUES(?,?)')->execute([$key, now()]);
    return false;
}
/** همهٔ نشست‌های یک کاربر را می‌بندد (بعد از تغییر رمز یا حذف)، به‌جز نشست فعلی اگر توکنش داده شود */
function kill_user_sessions(int $uid, string $exceptToken = ''): void {
    $keep = $exceptToken !== '' ? hash('sha256', $exceptToken) : '';
    db()->prepare('DELETE FROM sessions WHERE json_extract(data, \'$.uid\')=CAST(? AS INTEGER) AND token<>?')->execute([$uid, $keep]);
}
/** کد نصب/بازیابی: فایل متنی داخل پوشهٔ داده (خارج از وب). فایل نباشد یا کوتاه باشد، ابزار غیرفعال است. هیچ کدی در مخزن نگه داشته نمی‌شود. */
function setup_code(string $name): ?string {
    if (!preg_match('/^[a-z-]+$/', $name)) return null;
    $f = CMS_DATA . '/' . $name . '.txt';
    if (!is_file($f)) return null;
    $c = trim((string)@file_get_contents($f));
    return strlen($c) >= 16 ? $c : null;
}
function login(string $user, string $pass): bool {
    if (too_many_attempts()) return false;
    // سقف جداگانه برای هر نام کاربری (جلوگیری از حدس رمز از آی‌پی‌های مختلف)
    $ukey = 'u:' . hash('sha256', mb_strtolower(trim($user)));
    $c = db()->prepare('SELECT COUNT(*) FROM attempts WHERE ip=?'); $c->execute([$ukey]);
    if ((int)$c->fetchColumn() >= 30) return false;
    $s = db()->prepare('SELECT * FROM users WHERE username=?'); $s->execute([trim($user)]);
    $u = $s->fetch();
    $ok = $u && password_verify($pass, $u['pass']);
    if (!$u) password_verify($pass, '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ0123'); // زمان یکسان
    if (!$ok) {
        $ins = db()->prepare('INSERT INTO attempts(ip,ts) VALUES(?,?)');
        $ins->execute([client_ip(), now()]); $ins->execute([$ukey, now()]);
        return false;
    }
    session_open(['uid' => (int)$u['id']]);
    db()->prepare('DELETE FROM attempts WHERE ip=? OR ip=?')->execute([client_ip(), $ukey]);
    if (password_needs_rehash($u['pass'], PASSWORD_DEFAULT)) {
        db()->prepare('UPDATE users SET pass=? WHERE id=?')->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
    }
    return true;
}
function logout(): void {
    start_session();
    $t = $GLOBALS['__sess_token'] ?? '';
    if ($t !== '') db()->prepare('DELETE FROM sessions WHERE token=?')->execute([hash('sha256', $t)]);
    $GLOBALS['__sess_token'] = ''; $_SESSION = [];
    set_auth_cookie('', 1);
}

/* ---------- نقش‌ها ---------- */
function can(string $cap, ?array $u = null): bool {
    $u = $u ?? current_user(); if (!$u) return false;
    $r = $u['role'];
    if ($r === 'admin') return true;
    $map = [
        'editor'  => ['post', 'page', 'product', 'event', 'stay', 'gallery', 'experience', 'person', 'media', 'messages', 'publish', 'others'],
        'manager' => ['product', 'event', 'stay', 'gallery', 'media', 'messages', 'publish', 'others'],
        'author'  => ['post', 'media'],
    ];
    return in_array($cap, $map[$r] ?? [], true);
}

/* ---------- پاک‌سازی HTML ---------- */
function clean_html(string $html): string {
    $html = trim($html); if ($html === '') return '';
    $allowed = ['p' => [], 'br' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'ul' => [], 'ol' => [], 'li' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 'blockquote' => [], 'hr' => [],
        'a' => ['href', 'title'], 'img' => ['src', 'alt'], 'figure' => [], 'figcaption' => [], 'div' => [], 'span' => []];
    $d = new DOMDocument();
    libxml_use_internal_errors(true);
    $d->loadHTML('<?xml encoding="UTF-8"><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $body = $d->getElementsByTagName('body')->item(0);
    $walk = function (DOMNode $n) use (&$walk, $allowed, $d) {
        foreach (iterator_to_array($n->childNodes) as $c) {
            if ($c->nodeType === XML_ELEMENT_NODE) {
                $t = strtolower($c->nodeName);
                if (!array_key_exists($t, $allowed)) {
                    if (in_array($t, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'math', 'link', 'meta'], true)) { $n->removeChild($c); continue; }
                    $walk($c);
                    while ($c->firstChild) $n->insertBefore($c->firstChild, $c);
                    $n->removeChild($c); continue;
                }
                foreach (iterator_to_array($c->attributes) as $a) {
                    $name = strtolower($a->nodeName);
                    if (!in_array($name, $allowed[$t], true)) { $c->removeAttribute($a->nodeName); continue; }
                    $val = trim($a->nodeValue);
                    if ($name === 'href' && !preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $val)) $c->removeAttribute('href');
                    if ($name === 'src' && !preg_match('#^(https://|/uploads/)#i', $val)) { $n->removeChild($c); continue 2; }
                }
                if ($t === 'a' && $c->hasAttribute('href') && preg_match('#^https?://#i', $c->getAttribute('href'))) $c->setAttribute('rel', 'noopener nofollow');
                $walk($c);
            } elseif ($c->nodeType !== XML_TEXT_NODE) {
                $n->removeChild($c);
            }
        }
    };
    $walk($body);
    $out = '';
    foreach ($body->childNodes as $c) $out .= $d->saveHTML($c);
    return trim($out);
}

/* ---------- رسانه ---------- */
function save_upload(array $f, int $uid): array {
    if (($f['error'] ?? 1) !== UPLOAD_ERR_OK) throw new RuntimeException('آپلود ناموفق بود (کد ' . ($f['error'] ?? '?') . ').');
    if ($f['size'] > 8 * 1024 * 1024) throw new RuntimeException('حجم فایل بیش از ۸ مگابایت است.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime] ?? null;
    if (!$ext) throw new RuntimeException('فقط تصویر JPG، PNG، WebP یا GIF مجاز است.');
    $info = @getimagesize($f['tmp_name']); if (!$info) throw new RuntimeException('فایل تصویر معتبر نیست.');
    $dir = CMS_UPLOADS . '/' . date('Y/m');
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) throw new RuntimeException('ساخت پوشه آپلود ممکن نشد.');
    $base = bin2hex(random_bytes(8));
    $rel = date('Y/m') . "/$base.$ext";
    $dest = CMS_UPLOADS . '/' . $rel;
    $w = $info[0]; $h = $info[1]; $thumbRel = null;
    $src = null;
    if (function_exists('imagecreatefromstring') && $ext !== 'gif') {
        $src = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
    }
    if ($src) {
        if ($ext === 'jpg' && function_exists('exif_read_data')) {
            $ex = @exif_read_data($f['tmp_name']); $o = $ex['Orientation'] ?? 1;
            $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0; if ($rot) { $src = imagerotate($src, $rot, 0); }
        }
        $w = imagesx($src); $h = imagesy($src);
        $resize = function ($max) use ($src, $w, $h) {
            if (max($w, $h) <= $max) return null;
            $r = $max / max($w, $h); $nw = (int)round($w * $r); $nh = (int)round($h * $r);
            $im = imagecreatetruecolor($nw, $nh); imagealphablending($im, false); imagesavealpha($im, true);
            imagecopyresampled($im, $src, 0, 0, 0, 0, $nw, $nh, $w, $h); return $im;
        };
        $save = function ($im, $path) use ($ext) {
            if ($ext === 'jpg') imagejpeg($im, $path, 84); elseif ($ext === 'png') imagepng($im, $path, 6); else imagewebp($im, $path, 82);
        };
        $big = $resize(1800) ?: $src; $save($big, $dest);
        $w = imagesx($big); $h = imagesy($big);
        $th = $resize(480) ?: $big; $thumbRel = date('Y/m') . "/$base-t.$ext"; $save($th, CMS_UPLOADS . '/' . $thumbRel);
    } else {
        if (!move_uploaded_file($f['tmp_name'], $dest)) throw new RuntimeException('ذخیره فایل ممکن نشد.');
    }
    db()->prepare('INSERT INTO media(file,thumb,mime,size,w,h,uploaded_by,created) VALUES(?,?,?,?,?,?,?,?)')
        ->execute([$rel, $thumbRel, $mime, (int)filesize($dest), $w, $h, $uid, now()]);
    return media_get((int)db()->lastInsertId());
}
function media_get(int $id): ?array {
    $s = db()->prepare('SELECT * FROM media WHERE id=?'); $s->execute([$id]); return $s->fetch() ?: null;
}
function media_url(?array $m, bool $thumb = false): string {
    if (!$m) return '';
    return '/uploads/' . ($thumb && $m['thumb'] ? $m['thumb'] : $m['file']);
}

/* ---------- محتوا ---------- */
function item_load(int $id): ?array {
    $s = db()->prepare('SELECT * FROM items WHERE id=?'); $s->execute([$id]); $i = $s->fetch(); if (!$i) return null;
    $i['extra'] = json_decode($i['extra'] ?: '{}', true) ?: [];
    $i['tr'] = [];
    $t = db()->prepare('SELECT * FROM item_tr WHERE item_id=?'); $t->execute([$id]);
    foreach ($t->fetchAll() as $r) $i['tr'][$r['lang']] = $r;
    return $i;
}
function unique_slug(string $type, string $lang, string $slug, int $exceptId): string {
    $base = $slug !== '' ? $slug : 'item'; $n = 1; $cand = $base;
    while (true) {
        $s = db()->prepare('SELECT COUNT(*) FROM item_tr t JOIN items i ON i.id=t.item_id WHERE t.lang=? AND t.slug=? AND i.type=? AND t.item_id<>?');
        $s->execute([$lang, $cand, $type, $exceptId]);
        if (!(int)$s->fetchColumn()) return $cand;
        $cand = $base . '-' . (++$n);
    }
}

/* ---------- ترجمهٔ خودکار و «تجربه‌ها و آدم‌هایش» ---------- */
require_once __DIR__ . '/translate.php';
require_once __DIR__ . '/experiences.php';
