<?php
/* متن‌های سایت که از پنل ویرایش شده‌اند: ذخیره، اعمال روی HTML و فایل‌های RSC (.txt) هر صفحه.
   کلیدها همان کلیدهای cms-defaults.json هستند (ساختهٔ خودکار هر بیلد از روی داده‌های داخل کد). */
declare(strict_types=1);

const OV_SETTING = 'site_ov';

function ov_defaults(): array {
    static $d = null;
    if ($d === null) {
        $f = CMS_ROOT . '/cms-defaults.json';
        $d = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    }
    return $d;
}
/** فقط تغییرهای معتبر: کلید باید در پیش‌فرض‌ها باشد، مقدار خالی نباشد و با پیش‌فرض فرق کند */
function ov_map(): array {
    static $m = null;
    if ($m !== null) return $m;
    $m = [];
    try {
        $raw = json_decode(setting(OV_SETTING, '{}'), true) ?: [];
        $def = ov_defaults();
        foreach ($raw as $k => $v) {
            if (isset($def[$k]) && is_string($v) && $v !== '' && $v !== $def[$k]) $m[$k] = $v;
        }
    } catch (Throwable $e) { $m = []; }
    return $m;
}
function ov_raw(): array {
    $r = json_decode(setting(OV_SETTING, '{}'), true);
    return is_array($r) ? $r : [];
}
function ov_save(array $raw): void {
    set_setting(OV_SETTING, json_encode($raw, JSON_UNESCAPED_UNICODE));
}

function ov_esc_html(string $s): string {   // همان escape که React در HTML استفاده می‌کند
    return str_replace(['&', '<', '>', '"', "'"], ['&amp;', '&lt;', '&gt;', '&quot;', '&#x27;'], $s);
}
function ov_json_inner(string $s): string { return substr((string)json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 1, -1); }

/** جایگزینی متن‌های ویرایش‌شده در یک سند (HTML یا .txt)؛ هر خطایی بیاید همان ورودی برمی‌گردد */
function ov_filter(string $buf): string {
    try {
        $map = ov_map();
        if (!$map) return $buf;
        $def = ov_defaults();
        $isHtml = (bool)preg_match('/^\s*<(!doctype|html)/i', $buf);
        foreach ($map as $k => $new) {
            $old = $def[$k];
            $pairs = [
                [ov_esc_html($old), ov_esc_html($new), '(?<=[>"])', '(?=[<"])'],
                [ov_json_inner($old), ov_json_inner($new), '(?<=")', '(?=\\\\?")'],
                [str_replace('<', '\\u003c', ov_json_inner($old)), str_replace('<', '\\u003c', ov_json_inner($new)), '(?<=")', '(?=\\\\?")'],
            ];
            foreach ($pairs as [$o, $n, $pre, $post]) {
                if ($o === '' || strpos($buf, $o) === false) continue;
                $r = @preg_replace_callback('/' . $pre . preg_quote($o, '/') . $post . '/u', fn() => $n, $buf);
                if (is_string($r)) $buf = $r;
            }
        }
        if ($isHtml) {
            $js = json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            $r = preg_replace_callback('/<head[^>]*>/i', fn($m) => $m[0] . '<script>window.__HC_OV=' . $js . ';</script>', $buf, 1);
            if (is_string($r)) $buf = $r;
        }
        return $buf;
    } catch (Throwable $e) { return $buf; }
}

/** برای صفحه‌هایی که PHP می‌سازد (dynamic.php): خروجی را از فیلتر رد می‌کند */
function ov_start_buffer(): void {
    ob_start(fn(string $b) => ov_filter($b));
}

/** سرو صفحهٔ استاتیک (HTML/.txt) با اعمال متن‌های ویرایش‌شده؛ از dynamic.php?ovp=/مسیر صدا زده می‌شود.
    اگر پنل/دیتابیس مشکل داشته باشد همان فایل اصلی برمی‌گردد. */
function ov_serve(string $u): void {
    $root = realpath(CMS_ROOT);
    $notFound = function () use ($root): void {
        http_response_code(404); header('Content-Type: text/html; charset=utf-8');
        $f = $root . '/404.html'; if (is_file($f)) readfile($f); else echo 'Not found';
    };
    $u = rawurldecode((string)(strtok($u, '?') ?: ''));
    if ($root === false || $u === '' || $u[0] !== '/' || strpos($u, "\0") !== false || strpos($u, '..') !== false) { $notFound(); return; }
    $path = $root . $u;
    if (is_dir($path)) $path = rtrim($path, '/') . '/index.html';
    $real = realpath($path);
    if ($real === false || strpos($real, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($real)) { $notFound(); return; }
    $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    if (!in_array($ext, ['html', 'txt'], true)) { $notFound(); return; }
    header('Content-Type: ' . ($ext === 'html' ? 'text/html' : 'text/plain') . '; charset=utf-8');
    header('Cache-Control: no-cache');
    $body = (string)file_get_contents($real);
    if (!in_array(basename($real), ['robots.txt', 'build.txt'], true) && is_file($root . '/cms-defaults.json')) {
        try { $body = ov_filter($body); } catch (Throwable $e) { /* فایل اصلی */ }
    }
    echo $body;
}
