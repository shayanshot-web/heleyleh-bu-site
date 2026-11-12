<?php
/* دریافت‌کنندهٔ آمار بازدید (از t.js). همیشه ۲۰۴ برمی‌گرداند؛ هر خطایی بی‌صدا نادیده می‌شود تا سایت به آمار وابسته نشود.
   دلیل ناموفق‌بودن‌ها در cms_data/stats-debug.log ثبت می‌شود و در پنل (آمار بازدید ← عیب‌یابی) دیده می‌شود. */
declare(strict_types=1);
header('Cache-Control: no-store');
$method = $_SERVER['REQUEST_METHOD'] ?? '';
try {
    require __DIR__ . '/cms-core/lib.php';
    require __DIR__ . '/cms-core/stats.php';
    if ($method === 'GET' && isset($_GET['check'])) {   // آزمایش سلامت: https://heleyleh-bu.ir/t.php?check=1
        header('Content-Type: application/json');
        $n = (int)stats_db()->query('SELECT COUNT(*) FROM hits')->fetchColumn();
        echo json_encode(['ok' => true, 'php' => PHP_VERSION, 'hits' => $n]); exit;
    }
    http_response_code(204);
    if ($method !== 'POST') exit;
    $origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin !== '') {
        $oh = strtolower(preg_replace('/^www\./', '', (string)parse_url($origin, PHP_URL_HOST)));
        if ($oh !== SITE_HOST && !in_array($oh, ['localhost', '127.0.0.1'], true)) { stats_log('origin-rejected: ' . mb_substr($origin, 0, 60)); exit; }
    }
    $raw = file_get_contents('php://input', false, null, 0, 4096);
    $in = json_decode((string)$raw, true);
    if (!is_array($in)) { stats_log('bad-json'); exit; }
    $r = stats_collect($in);
    if ($r !== 'ok') stats_log($r);
} catch (Throwable $e) {
    if (function_exists('stats_log')) stats_log('ERROR ' . get_class($e) . ': ' . preg_replace('~/[^\s:]+~', '[path]', $e->getMessage()));
    if ($method === 'GET') { header('Content-Type: application/json'); echo json_encode(['ok' => false, 'error' => get_class($e)]); }
}
