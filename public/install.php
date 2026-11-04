<?php
declare(strict_types=1);
/**
 * نصب اولیهٔ پنل مدیریت هلیله: ساخت اولین مدیر کل.
 * فقط وقتی هیچ کاربری وجود ندارد کار می‌کند و بعد از ساخت مدیر، خودش غیرفعال می‌شود.
 * برای امنیت، «کد نصب» لازم است: فایل install-code.txt (حداقل ۱۶ نویسهٔ تصادفی) را خودتان داخل پوشهٔ داده
 * (cms_data) بسازید. فایل نباشد، این صفحه ۴۰۴ می‌دهد. بعد از نصب موفق فایل خودکار پاک می‌شود. کدی در مخزن نیست.
 */
require __DIR__ . '/cms-core/lib.php';
header('X-Frame-Options: DENY'); header('Cache-Control: no-store'); header('X-Robots-Tag: noindex');
$INSTALL_CODE = setup_code('install-code');
if ($INSTALL_CODE === null) { http_response_code(404); exit; }

try { $n = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(); }
catch (Throwable $ex) { error_log('cms install db: ' . $ex->getMessage()); http_response_code(500); exit('خطای دیتابیس؛ قابل‌نوشتن‌بودن پوشهٔ داده و فعال‌بودن pdo_sqlite را بررسی کنید (جزئیات در error_log هاست).'); }
if ($n > 0) { header('Location: /admin/'); exit; }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $un = trim((string)($_POST['username'] ?? '')); $name = trim((string)($_POST['name'] ?? ''));
    $pw = (string)($_POST['password'] ?? ''); $pw2 = (string)($_POST['password2'] ?? '');
    if (too_many_attempts()) $err = 'تلاش‌های ناموفق زیاد بود؛ ۱۵ دقیقه بعد دوباره امتحان کنید.';
    elseif (!hash_equals($INSTALL_CODE, trim((string)($_POST['code'] ?? '')))) { db()->prepare('INSERT INTO attempts(ip,ts) VALUES(?,?)')->execute([client_ip(), now()]); $err = 'کد نصب درست نیست.'; }
    elseif (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $un) || $name === '') $err = 'نام کاربری (۳ تا ۳۰ حرف لاتین/عدد) و نام الزامی است.';
    elseif (mb_strlen($pw) < 10) $err = 'رمز عبور حداقل ۱۰ نویسه باشد.';
    elseif ($pw !== $pw2) $err = 'تکرار رمز با رمز یکی نیست.';
    else {
        db()->prepare('INSERT INTO users(username,name,email,role,pass,created) VALUES(?,?,?,?,?,?)')
            ->execute([$un, $name, trim((string)($_POST['email'] ?? '')), 'admin', password_hash($pw, PASSWORD_DEFAULT), now()]);
        @unlink(CMS_DATA . '/install-code.txt');   // کد یک‌بارمصرف است
        header('Location: /admin/?a=login'); exit;
    }
}
echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>نصب پنل مدیریت هلیله</title><link rel="stylesheet" href="/admin/admin.css"></head>';
echo '<body class="login"><form method="post" class="login-box" autocomplete="off"><h1>ساخت مدیر کل</h1>';
if ($err) echo '<p class="alert err">' . e($err) . '</p>';
echo '<input type="hidden" name="_csrf" value="' . e(csrf()) . '">'
   . '<label>کد نصب<input name="code" required dir="ltr" autofocus></label>'
   . '<label>نام نمایشی<input name="name" required value="' . e((string)($_POST['name'] ?? '')) . '"></label>'
   . '<label>نام کاربری (لاتین)<input name="username" required dir="ltr" autocomplete="username" value="' . e((string)($_POST['username'] ?? '')) . '"></label>'
   . '<label>ایمیل (اختیاری)<input name="email" dir="ltr" value="' . e((string)($_POST['email'] ?? '')) . '"></label>'
   . '<label>رمز عبور (حداقل ۱۰ نویسه)<input name="password" type="password" required dir="ltr" autocomplete="new-password"></label>'
   . '<label>تکرار رمز عبور<input name="password2" type="password" required dir="ltr" autocomplete="new-password"></label>'
   . '<button class="btn primary wide">ساخت مدیر و ورود به پنل</button></form></body></html>';
