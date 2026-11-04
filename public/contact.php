<?php
/**
 * دریافت فرم تماس سایت هلیله و ارسال آن به ایمیل مدیریت.
 * روی هر سی‌پنلی که PHP داشته باشد کار می‌کند؛ به Node نیازی نیست.
 *
 * مهم: قبل از انتشار، آدرس $TO را به ایمیل واقعی مدیریت تغییر دهید.
 */
$TO = 'info@websiteco.ir';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function done($ok, $code = 200) {
    http_response_code($code);
    echo json_encode(['ok' => $ok]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    done(false, 405);
}

// فقط از همین دامنه
$host = $_SERVER['HTTP_HOST'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== preg_replace('/:\d+$/', '', $host)) {
    done(false, 403);
}

// تله اسپم
if (!empty($_POST['website'])) {
    done(true);
}

function field($key, $max) {
    $v = isset($_POST[$key]) ? (string)$_POST[$key] : '';
    $v = trim(str_replace(["\r", "\0"], '', $v));
    return mb_substr($v, 0, $max);
}
function oneline($v) {
    return trim(preg_replace('/\s+/u', ' ', $v));
}

$name    = oneline(field('name', 200));
$phone   = oneline(field('phone', 40));
$email   = oneline(field('email', 200));
$subject = oneline(field('subject', 100));
$message = field('message', 2000);
$ref     = oneline(field('ref', 200));
$lang    = in_array($_POST['lang'] ?? '', ['fa', 'en', 'ru'], true) ? $_POST['lang'] : 'fa';

if ($name === '' || $phone === '' || $message === '') {
    done(false, 422);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    done(false, 422);
}

// ذخیره در صندوق پیام‌های پنل مدیریت (اگر پنل نصب باشد)
$saved = false;
$cms = __DIR__ . '/cms-core/lib.php';
if (is_file($cms)) {
    try {
        require_once $cms;
        if (rate_limited('contact', 5, 600)) done(false, 429);   // حداکثر ۵ پیام در ۱۰ دقیقه برای هر آی‌پی
        db()->prepare('INSERT INTO messages(name,phone,email,subject,message,lang,ref,created) VALUES(?,?,?,?,?,?,?,?)')
            ->execute([$name, $phone, $email, $subject, $message, $lang, $ref, time()]);
        $saved = true;
        $n = setting('notify_email');
        if ($n !== '' && filter_var($n, FILTER_VALIDATE_EMAIL)) $TO = $n;
    } catch (Throwable $e) { /* اگر پنل مشکل داشت، ایمیل همچنان ارسال می‌شود */ }
}

$domain = 'websiteco.ir';   // ثابت؛ هدر Host قابل جعل است و داخل هدر ایمیل نباید بیاید
$mailSubject = mb_encode_mimeheader('[' . $domain . '] ' . ($subject ?: 'Contact') . ' - ' . $name, 'UTF-8', 'B');

$body  = "Name: $name\n";
if ($ref !== '') $body .= "Ref: $ref\n";
$body .= "Phone: $phone\n";
$body .= "Email: " . ($email ?: '-') . "\n";
$body .= "Subject: $subject\n";
$body .= "Language: $lang\n";
$body .= "----------------------------\n";
$body .= $message . "\n";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: no-reply@" . $domain . "\r\n";
if ($email !== '') {
    $headers .= "Reply-To: " . $email . "\r\n";
}

$sent = @mail($TO, $mailSubject, $body, $headers);
$okk = $sent || $saved;
done($okk, $okk ? 200 : 500);
