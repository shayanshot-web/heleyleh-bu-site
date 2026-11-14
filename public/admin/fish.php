<?php
/* پنل «ماهی‌فروشان»: نام و شمارهٔ تماس؛ زیر محصولات صنایع دستی و بومی نمایش داده می‌شود */

function fish_admin_post(array $user, string $a): never {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    fish_ready(); $pdo = db(); $_SESSION['flash'] = '';
    if ($a === 'fish_add') {
        $n = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 100); $p = fish_phone_clean((string)($_POST['phone'] ?? ''));
        if ($n === '' || strlen(preg_replace('/\D/', '', $p)) < 5) $_SESSION['flash'] = 'نام و شمارهٔ معتبر وارد کنید.';
        else {
            $next = (int)$pdo->query('SELECT COALESCE(MAX(sort),0)+10 FROM fish_sellers')->fetchColumn();
            $pdo->prepare('INSERT INTO fish_sellers(name,phone,note,sort) VALUES(?,?,?,?)')->execute([$n, $p, mb_substr(trim((string)($_POST['note'] ?? '')), 0, 120), $next]);
            $_SESSION['flash'] = 'ماهی‌فروش اضافه شد و زیر محصولات صنایع دستی نمایش داده می‌شود.';
        }
        redirect('/admin/?a=fish');
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($a === 'fish_del') { $pdo->prepare('DELETE FROM fish_sellers WHERE id=?')->execute([$id]); $_SESSION['flash'] = 'حذف شد.'; }
    elseif ($a === 'fish_save') {
        $p = fish_phone_clean((string)($_POST['phone'] ?? '')); $n = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 100);
        if ($n === '' || strlen(preg_replace('/\D/', '', $p)) < 5) $_SESSION['flash'] = 'نام و شمارهٔ معتبر وارد کنید.';
        else { $pdo->prepare('UPDATE fish_sellers SET name=?,phone=?,note=? WHERE id=?')->execute([$n, $p, mb_substr(trim((string)($_POST['note'] ?? '')), 0, 120), $id]); $_SESSION['flash'] = 'ذخیره شد.'; }
    } elseif ($a === 'fish_move') {
        $dir = ($_POST['dir'] ?? '') === 'up' ? -1 : 1;
        $all = array_map('intval', $pdo->query('SELECT id FROM fish_sellers ORDER BY sort,id')->fetchAll(PDO::FETCH_COLUMN));
        $i = array_search($id, $all, true);
        if ($i !== false && isset($all[$i + $dir])) { $t = $all[$i]; $all[$i] = $all[$i + $dir]; $all[$i + $dir] = $t; }
        foreach ($all as $n => $vid) $pdo->prepare('UPDATE fish_sellers SET sort=? WHERE id=?')->execute([($n + 1) * 10, $vid]);
    }
    redirect('/admin/?a=fish');
}

function page_fish(array $user): void {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    fish_ready(); $pdo = db();
    layout_start('ماهی‌فروشان', 'fish', $user);
    echo '<form method="post" action="/admin/?a=fish_add" class="card"><input type="hidden" name="_csrf" value="' . e(csrf()) . '">';
    echo '<label>نام ماهی‌فروش<input name="name" maxlength="100" required></label><label>شمارهٔ تماس<input name="phone" dir="ltr" placeholder="0917 000 0000" required></label><label>توضیح (اختیاری، مثلاً «صید روزانه»)<input name="note" maxlength="120"></label>';
    echo '<p class="muted">این فهرست زیر محصولات صنایع دستی و بومی (فارسی، انگلیسی و روسی) نمایش داده می‌شود و روی موبایل با لمس شماره تماس گرفته می‌شود. تا وقتی کسی اضافه نشده، این بلوک نمایش داده نمی‌شود.</p><button class="btn primary">افزودن</button></form>';
    $rows = $pdo->query('SELECT * FROM fish_sellers ORDER BY sort,id')->fetchAll();
    echo '<h2>' . count($rows) . ' ماهی‌فروش</h2>';
    foreach ($rows as $i => $r) {
        echo '<div class="card" style="margin-bottom:10px"><form method="post" action="/admin/?a=fish_save"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $r['id'] . '">';
        echo '<label>نام<input name="name" value="' . e($r['name']) . '" maxlength="100" required></label><label>شمارهٔ تماس<input name="phone" value="' . e($r['phone']) . '" dir="ltr" required></label><label>توضیح<input name="note" value="' . e($r['note']) . '" maxlength="120"></label><button class="btn primary">ذخیره</button></form> ';
        echo tv_admin_form('fish_move', ['id' => $r['id'], 'dir' => 'up']) . '<button class="btn"' . ($i === 0 ? ' disabled' : '') . '>↑</button></form> ';
        echo tv_admin_form('fish_move', ['id' => $r['id'], 'dir' => 'down']) . '<button class="btn"' . ($i === count($rows) - 1 ? ' disabled' : '') . '>↓</button></form> ';
        echo tv_admin_form('fish_del', ['id' => $r['id']]) . '<button class="btn" onclick="return confirm(\'حذف شود؟\')">حذف</button></form></div>';
    }
    layout_end();
}
