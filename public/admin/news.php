<?php
/* پنل «اخبار»: لینک خبر را بچسبانید؛ تیتر، چکیده و عکس خودکار گرفته می‌شود */

function news_admin_post(array $user, string $a): never {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    news_ready(); $pdo = db();
    $_SESSION['flash'] = '';
    if ($a === 'news_add') {
        $lines = preg_split('/[\s,]+/u', (string)($_POST['urls'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $lines = array_slice(array_unique($lines), 0, 20);
        $msgs = [];
        foreach ($lines as $u) { [$id, $m] = news_add_url($u); $msgs[] = $m; }
        $_SESSION['flash'] = $msgs ? implode(' | ', $msgs) : 'لینکی وارد نشده بود.';
        redirect('/admin/?a=news');
    }
    $id = (int)($_POST['id'] ?? 0);
    $row = $pdo->prepare('SELECT * FROM news WHERE id=?'); $row->execute([$id]); $r = $row->fetch();
    if (!$r) { $_SESSION['flash'] = 'خبر پیدا نشد.'; redirect('/admin/?a=news'); }
    if ($a === 'news_del') { $pdo->prepare('DELETE FROM news WHERE id=?')->execute([$id]); $_SESSION['flash'] = 'خبر حذف شد.'; redirect('/admin/?a=news'); }
    if ($a === 'news_toggle') {
        $pdo->prepare('UPDATE news SET status=? WHERE id=?')->execute([$r['status'] === 'published' ? 'draft' : 'published', $id]);
        $_SESSION['flash'] = $r['status'] === 'published' ? 'خبر از سایت پنهان شد.' : 'خبر منتشر شد.';
        redirect('/admin/?a=news');
    }
    if ($a === 'news_refetch') {
        $m = news_fetch_meta($r['url']);
        if (is_string($m)) $_SESSION['flash'] = 'دریافت خودکار ناموفق بود: ' . $m;
        else {
            $pdo->prepare('UPDATE news SET title=?,summary=?,image=?,source=?,published=?,fetched=1,tries=0 WHERE id=?')->execute([$m['title'], $m['summary'], $m['image'], $m['source'], $m['published'], $id]);
            $_SESSION['flash'] = 'اطلاعات خبر دوباره از سایت منبع گرفته شد.';
        }
        redirect('/admin/?a=news&id=' . $id);
    }
    if ($a === 'news_save') {
        $img = trim((string)($_POST['image'] ?? ''));
        if ($img !== '' && !preg_match('#^https?://#i', $img)) $img = '';
        $lang = in_array($_POST['lang'] ?? '', ['fa', 'en', 'ru'], true) ? $_POST['lang'] : 'fa';
        $ts = trim((string)($_POST['published'] ?? '')); $ts = $ts !== '' ? (strtotime($ts) ?: 0) : 0;
        $st = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';
        $title = mb_substr(trim((string)($_POST['title'] ?? '')), 0, 300);
        $pdo->prepare('UPDATE news SET title=?,summary=?,image=?,source=?,lang=?,published=?,status=?,pinned=?,fetched=1 WHERE id=?')->execute([
            $title, mb_substr(trim((string)($_POST['summary'] ?? '')), 0, 600), mb_substr($img, 0, 1000), mb_substr(trim((string)($_POST['source'] ?? '')), 0, 100), $lang, $ts, $st, empty($_POST['pinned']) ? 0 : 1, $id]);
        $_SESSION['flash'] = 'خبر ذخیره شد.';
        redirect('/admin/?a=news');
    }
    redirect('/admin/?a=news');
}

function news_admin_form(string $act, array $f = []): string {
    return '<form method="post" action="/admin/?a=' . $act . '" style="display:inline"><input type="hidden" name="_csrf" value="' . e(csrf()) . '">' . implode('', array_map(fn($k, $v) => '<input type="hidden" name="' . e($k) . '" value="' . e((string)$v) . '">', array_keys($f), $f));
}

function page_news(array $user): void {
    if (!can('page')) { http_response_code(403); exit('دسترسی ندارید'); }
    news_ready(); $pdo = db();
    layout_start('اخبار هلیله', 'news', $user);
    $id = (int)($_GET['id'] ?? 0);
    if ($id) {
        $s = $pdo->prepare('SELECT * FROM news WHERE id=?'); $s->execute([$id]); $r = $s->fetch();
        if (!$r) { echo '<p class="alert err">خبر پیدا نشد.</p>'; layout_end(); return; }
        echo '<p><a href="/admin/?a=news">← فهرست اخبار</a></p>';
        echo '<form method="post" action="/admin/?a=news_save" class="card"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $id . '">';
        echo '<p class="muted" dir="ltr" style="text-align:left">' . e($r['url']) . '</p>';
        echo '<label>تیتر خبر<input name="title" value="' . e($r['title']) . '" maxlength="300" required></label>';
        echo '<label>چکیده<textarea name="summary" rows="4" maxlength="600">' . e($r['summary']) . '</textarea></label>';
        echo '<label>آدرس عکس خبر (لینک مستقیم)<input name="image" value="' . e($r['image']) . '" dir="ltr" placeholder="https://…"></label>';
        if ($r['image'] !== '') echo '<p><img src="' . e($r['image']) . '" alt="" style="max-width:260px;border-radius:8px" referrerpolicy="no-referrer"></p>';
        echo '<label>نام سایت منبع<input name="source" value="' . e($r['source']) . '" maxlength="100"></label>';
        echo '<label>زبان خبر<select name="lang">'; foreach (LANGS as $c => $nm) echo '<option value="' . $c . '"' . ($r['lang'] === $c ? ' selected' : '') . '>' . e($nm) . '</option>'; echo '</select></label>';
        echo '<label>تاریخ انتشار (مثل 2026-11-13)<input name="published" value="' . ($r['published'] ? date('Y-m-d', (int)$r['published']) : '') . '" dir="ltr"></label>';
        echo '<label><input type="checkbox" name="status" value="published"' . ($r['status'] === 'published' ? ' checked' : '') . '> منتشر شود</label> ';
        echo '<label><input type="checkbox" name="pinned" value="1"' . ($r['pinned'] ? ' checked' : '') . '> سنجاق (خبر اصلی صفحهٔ اول)</label>';
        echo '<p><button class="btn primary">ذخیره</button></p></form>';
        echo news_admin_form('news_refetch', ['id' => $id]) . '<button class="btn">گرفتن دوبارهٔ اطلاعات از سایت منبع</button></form>';
        layout_end(); return;
    }
    echo '<form method="post" action="/admin/?a=news_add" class="card"><input type="hidden" name="_csrf" value="' . e(csrf()) . '">';
    echo '<label>لینک خبرها (هر لینک در یک خط؛ تا ۲۰ لینک)<textarea name="urls" rows="4" dir="ltr" placeholder="https://example.ir/news/123" required></textarea></label>';
    echo '<p class="muted">تیتر، چکیده و عکس خبر خودکار از خود صفحهٔ خبر گرفته می‌شود. اگر نشد، خبر پیش‌نویس می‌ماند تا دستی کامل کنید. در سایت، هر خبر به صفحهٔ اصلی خبر در سایت منبع لینک است و با زیاد شدن خبرها صفحه‌های بعدی خودکار ساخته می‌شود.</p>';
    echo '<button class="btn primary">افزودن خبر</button></form>';
    $rows = $pdo->query('SELECT * FROM news ORDER BY pinned DESC, (published=0), published DESC, id DESC')->fetchAll();
    echo '<h2>' . count($rows) . ' خبر</h2>';
    if (!$rows) echo '<p class="muted">هنوز خبری ثبت نشده است.</p>';
    else {
        echo '<table class="tbl"><thead><tr><th>تیتر</th><th>منبع</th><th>تاریخ</th><th>وضعیت</th><th></th></tr></thead><tbody>';
        foreach ($rows as $r) {
            echo '<tr><td><a href="/admin/?a=news&id=' . $r['id'] . '">' . e($r['title'] !== '' ? $r['title'] : $r['url']) . '</a>' . ($r['pinned'] ? ' 📌' : '') . ($r['summary'] === '' || $r['image'] === '' ? ' <span class="muted">(ناقص)</span>' : '') . '</td><td>' . e($r['source']) . '</td><td>' . ($r['published'] ? e(date('Y-m-d', (int)$r['published'])) : '—') . '</td><td>' . badge($r['status']) . '</td><td>';
            echo news_admin_form('news_toggle', ['id' => $r['id']]) . '<button class="btn">' . ($r['status'] === 'published' ? 'پنهان' : 'انتشار') . '</button></form> ';
            echo news_admin_form('news_del', ['id' => $r['id']]) . '<button class="btn" onclick="return confirm(\'این خبر حذف شود؟\')">حذف</button></form></td></tr>';
        }
        echo '</tbody></table>';
    }
    layout_end();
}
