<?php
/* پنل «جلد کتاب‌ها»: برای هر کتاب نشر هلیله عکس جلد بارگذاری کنید؛ بلافاصله روی سایت اعمال می‌شود.
   فهرست کتاب‌ها از cms-defaults.json می‌آید (ساختهٔ خودکار از books.ts)، پس کتاب تازه‌ای که به کد اضافه شود همین‌جا هم دیده می‌شود. */

require_once __DIR__ . '/../cms-core/overrides.php';

function books_list(): array {
    $def = ov_defaults(); $out = [];
    foreach ($def as $k => $v) {
        if (preg_match('/^books\.(\d+)\.title\.fa$/', $k, $m)) $out[(int)$m[1]]['title'] = $v;
        elseif (preg_match('/^books\.(\d+)\.author\.fa$/', $k, $m)) $out[(int)$m[1]]['author'] = $v;
        elseif (preg_match('/^books\.(\d+)\.cover$/', $k, $m)) { $out[(int)$m[1]]['cover'] = $v; $out[(int)$m[1]]['key'] = $k; }
    }
    ksort($out);
    return array_filter($out, fn($b) => isset($b['key']));
}

function books_admin_post(array $user, string $a): never {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    $list = books_list(); $i = (int)($_POST['i'] ?? -1);
    if (!isset($list[$i])) { $_SESSION['flash'] = 'کتاب پیدا نشد.'; redirect('/admin/?a=books'); }
    $key = $list[$i]['key']; $raw = ov_raw();
    if ($a === 'book_cover_reset') {
        unset($raw[$key]); ov_save($raw); $_SESSION['flash'] = 'جلد به حالت اصلی برگشت.';
    } else {
        try {
            if (empty($_FILES['cover']['tmp_name'])) throw new RuntimeException('عکسی انتخاب نشده بود.');
            $m = save_upload($_FILES['cover'], (int)($user['id'] ?? 0));
            $raw[$key] = media_url($m);   // اندازهٔ اصلی (بیشینه ۱۸۰۰ پیکسل)
            ov_save($raw);
            $_SESSION['flash'] = 'جلد «' . $list[$i]['title'] . '» ذخیره شد و روی سایت اعمال می‌شود.';
        } catch (Throwable $ex) { $_SESSION['flash'] = $ex->getMessage(); }
    }
    redirect('/admin/?a=books#b' . $i);
}

function page_books(array $user): void {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    layout_start('جلد کتاب‌ها', 'books', $user);
    $list = books_list(); $raw = ov_raw();
    if (!$list) { echo '<p class="alert err">فهرست کتاب‌ها (cms-defaults.json) هنوز روی هاست نیست؛ بعد از deploy بعدی اینجا پر می‌شود.</p>'; layout_end(); return; }
    echo '<p class="muted">برای هر کتاب عکس جلد را بارگذاری کنید (JPG، PNG یا WebP، تا ۸ مگابایت). عکس عمودی و فقط رو‌ی‌جلد بهتر است. بازدیدکننده اول همین جلد را می‌بیند و با کلیک ورق می‌زند.</p>';
    echo '<div class="bk-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px">';
    foreach ($list as $i => $b) {
        $cur = $raw[$b['key']] ?? $b['cover'];
        $custom = isset($raw[$b['key']]) && $raw[$b['key']] !== $b['cover'];
        $img = str_starts_with($cur, '/uploads/') ? $cur : (str_starts_with($cur, '_') ? '' : '/books/' . $cur);
        echo '<div class="card" id="b' . $i . '"><strong>' . e($b['title']) . '</strong><p class="muted">' . e($b['author'] ?? '') . '</p>';
        echo $img ? '<p><img src="' . e($img) . '" alt="" style="width:120px;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.3)"></p>' : '<p class="muted">هنوز عکس جلد ندارد (جلد گرافیکی نمایش داده می‌شود).</p>';
        echo '<form method="post" action="/admin/?a=book_cover_save" enctype="multipart/form-data"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="i" value="' . $i . '"><input type="file" name="cover" accept="image/jpeg,image/png,image/webp" required> <button class="btn primary">بارگذاری جلد</button></form>';
        if ($custom) echo '<form method="post" action="/admin/?a=book_cover_reset" style="margin-top:6px"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="i" value="' . $i . '"><button class="btn">بازگشت به جلد اصلی</button></form>';
        echo '</div>';
    }
    echo '</div>';
    layout_end();
}
