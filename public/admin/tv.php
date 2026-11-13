<?php
/* پنل «تلویزیون گالری»: لینک ویدیو (آپارات، یوتیوب، ویمیو، فایل mp4 یا هر صفحهٔ دارای پخش‌کنندهٔ جاسازی) را بچسبانید */

function tv_admin_post(array $user, string $a): never {
    if (!can('gallery')) { http_response_code(403); exit('دسترسی ندارید'); }
    tv_ready(); $pdo = db();
    $_SESSION['flash'] = '';
    if ($a === 'tv_add') {
        $urls = array_slice(array_unique(preg_split('/\s+/u', (string)($_POST['urls'] ?? ''), -1, PREG_SPLIT_NO_EMPTY)), 0, 15);
        $msgs = []; foreach ($urls as $u) $msgs[] = tv_add_url($u)[1];
        $_SESSION['flash'] = $msgs ? implode(' | ', $msgs) : 'لینکی وارد نشده بود.';
        redirect('/admin/?a=tv');
    }
    $id = (int)($_POST['id'] ?? 0);
    $q = $pdo->prepare('SELECT * FROM tv WHERE id=?'); $q->execute([$id]); $r = $q->fetch();
    if (!$r) { $_SESSION['flash'] = 'ویدیو پیدا نشد.'; redirect('/admin/?a=tv'); }
    if ($a === 'tv_del') { $pdo->prepare('DELETE FROM tv WHERE id=?')->execute([$id]); $_SESSION['flash'] = 'کانال حذف شد.'; }
    elseif ($a === 'tv_toggle') { $pdo->prepare('UPDATE tv SET status=? WHERE id=?')->execute([$r['status'] === 'published' ? 'draft' : 'published', $id]); $_SESSION['flash'] = 'وضعیت کانال تغییر کرد.'; }
    elseif ($a === 'tv_move') {
        $dir = ($_POST['dir'] ?? '') === 'up' ? -1 : 1;
        $all = $pdo->query('SELECT id FROM tv ORDER BY sort,id')->fetchAll(PDO::FETCH_COLUMN);
        $i = array_search($id, array_map('intval', $all), true);
        if ($i !== false && isset($all[$i + $dir])) { $t = $all[$i]; $all[$i] = $all[$i + $dir]; $all[$i + $dir] = $t; }
        foreach ($all as $n => $vid) $pdo->prepare('UPDATE tv SET sort=? WHERE id=?')->execute([($n + 1) * 10, $vid]);
    } elseif ($a === 'tv_save') {
        $th = trim((string)($_POST['thumb'] ?? '')); if ($th !== '' && !preg_match('#^https?://#i', $th)) $th = '';
        $pdo->prepare('UPDATE tv SET title=?, thumb=? WHERE id=?')->execute([mb_substr(trim((string)($_POST['title'] ?? '')), 0, 200), mb_substr($th, 0, 1000), $id]);
        $_SESSION['flash'] = 'ذخیره شد.';
    } elseif ($a === 'tv_refetch') {
        $m = tv_resolve($r['url']);
        if (is_string($m)) $_SESSION['flash'] = 'دریافت دوباره ناموفق بود: ' . $m;
        else { $pdo->prepare('UPDATE tv SET title=?,thumb=?,kind=?,provider=?,src=? WHERE id=?')->execute([$m['title'], $m['thumb'], $m['kind'], $m['provider'], $m['src'], $id]); $_SESSION['flash'] = 'اطلاعات دوباره از لینک گرفته شد.'; }
    }
    redirect('/admin/?a=tv');
}

function tv_admin_form(string $act, array $f = []): string {
    $h = '<form method="post" action="/admin/?a=' . $act . '" style="display:inline"><input type="hidden" name="_csrf" value="' . e(csrf()) . '">';
    foreach ($f as $k => $v) $h .= '<input type="hidden" name="' . e($k) . '" value="' . e((string)$v) . '">';
    return $h;
}

function page_tv(array $user): void {
    if (!can('gallery')) { http_response_code(403); exit('دسترسی ندارید'); }
    tv_ready(); $pdo = db();
    layout_start('تلویزیون گالری', 'tv', $user);
    $kinds = ['embed' => 'پخش در تلویزیون', 'file' => 'فایل ویدیو', 'link' => 'فقط لینک'];
    echo '<form method="post" action="/admin/?a=tv_add" class="card"><input type="hidden" name="_csrf" value="' . e(csrf()) . '">';
    echo '<label>لینک ویدیوها (هر لینک در یک خط؛ آپارات، یوتیوب، ویمیو، فایل mp4/webm یا صفحهٔ سایت‌های دیگر)<textarea name="urls" rows="4" dir="ltr" placeholder="https://www.aparat.com/v/xxxxx" required></textarea></label>';
    echo '<p class="muted">هر لینک یک «کانال» تلویزیون بالای صفحهٔ گالری می‌شود؛ بازدیدکننده روی کانال می‌زند، ویدیو در خود تلویزیون پخش می‌شود و می‌تواند تمام‌صفحه کند. عنوان و تصویر کانال خودکار گرفته می‌شود و بعداً قابل ویرایش است. سایت‌هایی که پخش‌کنندهٔ جاسازی ندارند با دکمهٔ «پخش در سایت منبع» باز می‌شوند.</p>';
    echo '<button class="btn primary">افزودن کانال</button></form>';
    $rows = $pdo->query('SELECT * FROM tv ORDER BY sort,id')->fetchAll();
    echo '<h2>' . count($rows) . ' کانال</h2>';
    if (!$rows) echo '<p class="muted">هنوز ویدیویی ثبت نشده؛ تا زمانی که کانالی نباشد، تلویزیون در گالری نمایش داده نمی‌شود.</p>';
    foreach ($rows as $i => $r) {
        echo '<div class="card" style="margin-bottom:10px"><form method="post" action="/admin/?a=tv_save"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $r['id'] . '">';
        echo '<p><strong>کانال ' . ($i + 1) . '</strong> ' . badge($r['status']) . ' <span class="muted">' . e($kinds[$r['kind']] ?? $r['kind']) . ' · ' . e($r['provider']) . '</span></p>';
        echo '<p class="muted" dir="ltr" style="text-align:left;word-break:break-all">' . e($r['url']) . '</p>';
        echo '<label>عنوان<input name="title" value="' . e($r['title']) . '" maxlength="200"></label><label>آدرس تصویر کانال<input name="thumb" value="' . e($r['thumb']) . '" dir="ltr" placeholder="https://…"></label>';
        if ($r['thumb'] !== '') echo '<p><img src="' . e($r['thumb']) . '" alt="" style="max-width:160px;border-radius:8px" referrerpolicy="no-referrer"></p>';
        echo '<button class="btn primary">ذخیره</button></form> ';
        echo tv_admin_form('tv_move', ['id' => $r['id'], 'dir' => 'up']) . '<button class="btn"' . ($i === 0 ? ' disabled' : '') . '>↑ بالاتر</button></form> ';
        echo tv_admin_form('tv_move', ['id' => $r['id'], 'dir' => 'down']) . '<button class="btn"' . ($i === count($rows) - 1 ? ' disabled' : '') . '>↓ پایین‌تر</button></form> ';
        echo tv_admin_form('tv_toggle', ['id' => $r['id']]) . '<button class="btn">' . ($r['status'] === 'published' ? 'پنهان' : 'انتشار') . '</button></form> ';
        echo tv_admin_form('tv_refetch', ['id' => $r['id']]) . '<button class="btn">گرفتن دوبارهٔ اطلاعات</button></form> ';
        echo tv_admin_form('tv_del', ['id' => $r['id']]) . '<button class="btn" onclick="return confirm(\'این کانال حذف شود؟\')">حذف</button></form></div>';
    }
    layout_end();
}
