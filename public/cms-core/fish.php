<?php
/* «شمارهٔ تماس ماهی‌فروشان» زیر محصولات صنایع دستی و بومی؛ از پنل ← «ماهی‌فروشان» اضافه/ویرایش/حذف می‌شود */
declare(strict_types=1);

function fish_ready(): void {
    static $done = false;
    if ($done) return;
    db()->exec("CREATE TABLE IF NOT EXISTS fish_sellers(id INTEGER PRIMARY KEY, name TEXT NOT NULL, phone TEXT NOT NULL, note TEXT NOT NULL DEFAULT '', sort INTEGER NOT NULL DEFAULT 100)");
    $done = true;
}

/** ارقام فارسی/عربی → لاتین؛ فقط ارقام، + و فاصله/خط‌تیره */
function fish_phone_clean(string $p): string {
    $p = strtr($p, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
    return trim((string)preg_replace('/[^0-9+\-\s]/', '', $p));
}

const FISH_UI = [
    'fa' => ['h' => 'شمارهٔ تماس ماهی‌فروشان', 'p' => 'برای خرید ماهی تازهٔ هلیله مستقیم با ماهی‌فروشان تماس بگیرید.', 'call' => 'تماس'],
    'en' => ['h' => 'Fish sellers: phone numbers', 'p' => 'Call the fish sellers directly to buy fresh Helileh fish.', 'call' => 'Call'],
    'ru' => ['h' => 'Телефоны продавцов рыбы', 'p' => 'Чтобы купить свежую рыбу Хелиле, звоните продавцам напрямую.', 'call' => 'Позвонить'],
];

function fish_block(string $lang): string {
    try { fish_ready(); $rows = db()->query('SELECT * FROM fish_sellers ORDER BY sort,id')->fetchAll(); }
    catch (Throwable $ex) { return ''; }
    if (!$rows) return '';
    $u = FISH_UI[$lang] ?? FISH_UI['fa'];
    $li = '';
    foreach ($rows as $r) {
        $ph = fish_phone_clean($r['phone']); $tel = preg_replace('/[^0-9+]/', '', $ph);
        $li .= '<li><span class="fs-ic" aria-hidden="true"><svg viewBox="0 0 48 32"><path d="M4 16c8-12 22-12 30 0-8 12-22 12-30 0z" fill="#2b7a8c"/><path d="M34 16l11-9v18z" fill="#1f5f70"/><circle cx="14" cy="14" r="2" fill="#fff"/></svg></span>'
            . '<span class="fs-n">' . e($r['name']) . ($r['note'] !== '' ? '<small>' . e($r['note']) . '</small>' : '') . '</span>'
            . '<a class="fs-tel" href="tel:' . e($tel) . '" dir="ltr" aria-label="' . e($u['call'] . ' ' . $r['name']) . '">' . e($ph) . '</a></li>';
    }
    return '<section class="container cms-fish" aria-labelledby="fish-h"><h2 id="fish-h">' . e($u['h']) . '</h2><p class="fs-p">' . e($u['p']) . '</p><ul class="fs-list">' . $li . '</ul></section>';
}

function fish_css(): string {
    return '<style>.cms-fish{margin:34px auto 20px}.cms-fish h2{margin:0 0 6px}.fs-p{margin:0 0 14px;color:#5b6a70}.fs-list{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:12px}'
        . '.fs-list li{display:flex;align-items:center;gap:12px;padding:12px 14px;background:#fffdf8;border:1.5px solid #d9c9a3;border-radius:14px;box-shadow:0 2px 0 #e8dcc0}'
        . '.fs-ic svg{width:44px;height:30px;display:block}.fs-n{flex:1;font-weight:700;color:#14323a;display:flex;flex-direction:column}.fs-n small{font-weight:400;color:#6b7a80}'
        . '.fs-tel{padding:8px 14px;border-radius:999px;background:#134e5a;color:#fff;text-decoration:none;font-weight:700;letter-spacing:.02em;white-space:nowrap}.fs-tel:hover{background:#0e3b44}.fs-tel:focus-visible{outline:3px solid #c9a24a;outline-offset:2px}</style>';
}
