<?php
/* دریافت‌کنندهٔ آمار بازدید (از t.js). همیشه ۲۰۴ برمی‌گرداند؛ هر خطایی بی‌صدا نادیده گرفته می‌شود تا سایت هرگز به آمار وابسته نشود. */
declare(strict_types=1);
header('Cache-Control: no-store');
http_response_code(204);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') exit;
try {
    require __DIR__ . '/cms-core/lib.php';
    require __DIR__ . '/cms-core/stats.php';
    $origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin !== '') {
        $oh = strtolower(preg_replace('/^www\./', '', (string)parse_url($origin, PHP_URL_HOST)));
        if ($oh !== SITE_HOST && !in_array($oh, ['localhost', '127.0.0.1'], true)) exit;
    }
    $raw = file_get_contents('php://input', false, null, 0, 4096);
    $in = json_decode((string)$raw, true);
    if (is_array($in)) stats_collect($in);
} catch (Throwable $e) { /* نادیده */ }
