<?php
/* نام قدیمی؛ همین کار حالا از dynamic.php?ovp=/مسیر انجام می‌شود (روی هاست فقط نام‌های آشنا باز می‌شوند). */
declare(strict_types=1);
try { require __DIR__ . '/cms-core/lib.php'; require __DIR__ . '/cms-core/overrides.php'; ov_serve((string)($_GET['u'] ?? '')); } catch (Throwable $e) { http_response_code(500); echo 'error'; }
