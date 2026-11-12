<?php
/* همان نقطهٔ دریافت آمار؛ نسخهٔ اصلی از dynamic.php?hc=1 استفاده می‌کند. */
declare(strict_types=1);
try { require __DIR__ . '/cms-core/lib.php'; require __DIR__ . '/cms-core/stats.php'; stats_endpoint(); } catch (Throwable $e) { http_response_code(204); }
