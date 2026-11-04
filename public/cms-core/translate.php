<?php
/* ترجمهٔ خودکار محتوای فارسی به انگلیسی و روسی.
   سرویس ترجمه از «تنظیمات» پنل انتخاب می‌شود؛ هاست باید به آن سرویس دسترسی خروجی (HTTPS) داشته باشد.
   اگر ترجمه ممکن نشود، ذخیره‌سازی ادامه می‌یابد و در سایت متن فارسی نمایش داده می‌شود. */

const TR_PROVIDERS = [
    'mymemory' => 'MyMemory (رایگان، بدون کلید؛ سقف روزانه دارد)',
    'libre'    => 'LibreTranslate (سرور خودتان یا عمومی؛ کلید اختیاری)',
    'google'   => 'Google Cloud Translation (نیازمند کلید API)',
    'off'      => 'خاموش (فقط ترجمهٔ دستی)',
];

function tr_provider(): string {
    $p = setting('tr_provider', 'mymemory');
    return isset(TR_PROVIDERS[$p]) ? $p : 'mymemory';
}

/** آدرس باید به یک سرور عمومی برسد؛ آی‌پی‌های داخلی/رزرو (localhost، شبکهٔ خصوصی، metadata ابری) رد می‌شوند (جلوگیری از SSRF) */
function tr_host_public(string $url): bool {
    $h = parse_url($url, PHP_URL_HOST); if (!is_string($h) || $h === '') return false;
    $ips = filter_var($h, FILTER_VALIDATE_IP) ? [$h] : (gethostbynamel($h) ?: []);
    if (!$ips) return false;
    foreach ($ips as $ip) if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return false;
    return true;
}

/** درخواست HTTPS ساده با timeout کوتاه؛ خروجی: [کد وضعیت, بدنه] یا [0, پیام خطا] */
function tr_http(string $url, ?array $post = null, bool $json = false): array {
    if (!preg_match('#^https?://#i', $url)) return [0, 'آدرس سرویس معتبر نیست.'];
    if (!function_exists('curl_init')) return [0, 'افزونهٔ curl روی هاست فعال نیست.'];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12, CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_FOLLOWLOCATION => false, CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_USERAGENT => 'HelilehCMS/1.0',
    ]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($json) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } else curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($ch);
    if ($body === false) { $err = curl_error($ch); curl_close($ch); return [0, 'اتصال به سرویس ترجمه ممکن نشد: ' . $err]; }
    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE); curl_close($ch);
    return [$code, (string)$body];
}

/** ترجمهٔ یک متن کوتاه. خروجی: ['ok' => bool, 'text' => string, 'err' => string] */
function cms_translate(string $text, string $to, string $from = 'fa'): array {
    $text = trim($text);
    if ($text === '') return ['ok' => false, 'text' => '', 'err' => 'متن خالی است.'];
    $prov = tr_provider();
    if ($prov === 'off') return ['ok' => false, 'text' => '', 'err' => 'ترجمهٔ خودکار خاموش است.'];
    $out = '';
    if ($prov === 'mymemory') {
        $q = ['q' => $text, 'langpair' => "$from|$to"];
        $mail = setting('tr_email'); if ($mail !== '') $q['de'] = $mail;
        [$code, $body] = tr_http('https://api.mymemory.translated.net/get?' . http_build_query($q));
        if ($code !== 200) return ['ok' => false, 'text' => '', 'err' => $code ? "پاسخ نامعتبر ($code)" : $body];
        $j = json_decode($body, true); $out = (string)($j['responseData']['translatedText'] ?? '');
        if (stripos($out, 'MYMEMORY WARNING') !== false || (int)($j['responseStatus'] ?? 200) === 429) return ['ok' => false, 'text' => '', 'err' => 'سقف روزانهٔ سرویس رایگان پر شده است.'];
    } elseif ($prov === 'libre') {
        $base = rtrim(setting('tr_url'), '/'); if ($base === '') return ['ok' => false, 'text' => '', 'err' => 'آدرس LibreTranslate در تنظیمات وارد نشده است.'];
        if (!tr_host_public($base)) return ['ok' => false, 'text' => '', 'err' => 'آدرس LibreTranslate باید یک سرور عمومی باشد.'];
        $payload = ['q' => $text, 'source' => $from, 'target' => $to, 'format' => 'text'];
        $key = setting('tr_key'); if ($key !== '') $payload['api_key'] = $key;
        [$code, $body] = tr_http($base . '/translate', $payload, true);
        if ($code !== 200) return ['ok' => false, 'text' => '', 'err' => $code ? "پاسخ نامعتبر ($code)" : $body];
        $out = (string)(json_decode($body, true)['translatedText'] ?? '');
    } else { // google
        $key = setting('tr_key'); if ($key === '') return ['ok' => false, 'text' => '', 'err' => 'کلید API گوگل در تنظیمات وارد نشده است.'];
        [$code, $body] = tr_http('https://translation.googleapis.com/language/translate/v2?key=' . rawurlencode($key), ['q' => $text, 'source' => $from, 'target' => $to, 'format' => 'text']);
        if ($code !== 200) return ['ok' => false, 'text' => '', 'err' => $code ? "پاسخ نامعتبر ($code)" : $body];
        $out = html_entity_decode((string)(json_decode($body, true)['data']['translations'][0]['translatedText'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    $out = trim($out);
    // خروجی خالی یا بدون تغییر (هنوز فارسی/عربی) یعنی ترجمه انجام نشده است
    if ($out === '' || preg_match('/\p{Arabic}/u', $out)) return ['ok' => false, 'text' => '', 'err' => 'سرویس ترجمه‌ای برنگرداند.'];
    return ['ok' => true, 'text' => mb_substr($out, 0, 250), 'err' => ''];
}
