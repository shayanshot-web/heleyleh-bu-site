<?php
/* همهٔ صفحه‌های استاتیک سایت (HTML و فایل‌های .txt مسیریابی) از اینجا رد می‌شوند تا متن‌های ویرایش‌شدهٔ پنل اعمال شود.
   اگر چیزی ویرایش نشده باشد یا هر مشکلی در پنل/دیتابیس پیش بیاید، فایل همان‌طور که هست برمی‌گردد (سایت هیچ‌وقت به پنل وابسته نمی‌شود). */
declare(strict_types=1);

$root = realpath(__DIR__);
function ov_404(string $root): never {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    $f = $root . '/404.html';
    if (is_file($f)) readfile($f); else echo 'Not found';
    exit;
}
$u = rawurldecode((string)(strtok((string)($_GET['u'] ?? ''), '?') ?: ''));
if ($u === '' || $u[0] !== '/' || strpos($u, "\0") !== false || strpos($u, '..') !== false) ov_404($root);
$path = $root . $u;
if (is_dir($path)) $path = rtrim($path, '/') . '/index.html';
$real = realpath($path);
if ($real === false || strpos($real, $root . DIRECTORY_SEPARATOR) !== 0 || !is_file($real)) ov_404($root);
$ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
if (!in_array($ext, ['html', 'txt'], true)) ov_404($root);
header('Content-Type: ' . ($ext === 'html' ? 'text/html' : 'text/plain') . '; charset=utf-8');
header('Cache-Control: no-cache');
$body = (string)file_get_contents($real);
if (!in_array(basename($real), ['robots.txt', 'build.txt'], true) && is_file($root . '/cms-defaults.json')) {
    try {
        require __DIR__ . '/cms-core/lib.php';
        require __DIR__ . '/cms-core/overrides.php';
        $body = ov_filter($body);
    } catch (Throwable $e) { /* فایل اصلی */ }
}
echo $body;
