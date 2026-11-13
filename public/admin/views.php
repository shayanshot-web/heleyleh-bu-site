<?php
/* نمایش صفحات پنل (فقط توابع؛ اجرا از index.php) */

function flash_take(): string { start_session(); $f = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); return (string)$f; }

function head(string $title): void {
    echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>' . e($title) . ' — پنل مدیریت هلیله</title><link rel="stylesheet" href="/admin/admin.css"></head>';
}

function layout_login(string $err): void {
    head('ورود');
    echo '<body class="login"><form method="post" class="login-box" autocomplete="on"><h1>پنل مدیریت هلیله</h1>';
    if ($err) echo '<p class="alert err">' . e($err) . '</p>';
    echo '<input type="hidden" name="_csrf" value="' . e(csrf()) . '"><label>نام کاربری<input name="u" required autofocus autocomplete="username" dir="ltr"></label><label>رمز عبور<input name="p" type="password" required autocomplete="current-password" dir="ltr"></label><button class="btn primary wide">ورود</button></form></body></html>';
}

function layout_start(string $title, string $active, array $user): void {
    $unread = can('messages') ? (int)db()->query("SELECT COUNT(*) FROM messages WHERE status='new'")->fetchColumn() : 0;
    head($title);
    echo '<body><div class="app"><aside class="side"><a class="brand" href="/admin/">هلیله · مدیریت</a><nav>';
    $nav = [['dashboard', '/admin/', '⌂', 'پیشخوان', true]];
    foreach (TYPES as $k => $t) $nav[] = ['list-' . $k, '/admin/?a=list&type=' . $k, $t['icon'], $t['label'], can($k)];
    $nav[] = ['stats', '/admin/?a=stats', '◔', 'آمار بازدید', can('others')];
    $nav[] = ['books', '/admin/?a=books', '📚', 'جلد کتاب‌ها', can('page')];
    $nav[] = ['tv', '/admin/?a=tv', '▶', 'تلویزیون گالری', can('gallery')];
    $nav[] = ['news', '/admin/?a=news', '▦', 'اخبار هلیله', can('page')];
    $nav[] = ['content', '/admin/?a=content', '✎', 'متن‌های سایت', can('page')];
    $nav[] = ['media', '/admin/?a=media', '▤', 'کتابخانه رسانه', can('media')];
    $nav[] = ['messages', '/admin/?a=messages', '✉', 'پیام‌ها' . ($unread ? " ($unread)" : ''), can('messages')];
    $pc = can('messages') ? (int)db()->query("SELECT COUNT(*) FROM comments WHERE status='pending'")->fetchColumn() : 0;
    $nav[] = ['comments', '/admin/?a=comments', '❝', 'نظرها' . ($pc ? " ($pc)" : ''), can('messages')];
    $nav[] = ['users', '/admin/?a=users', '☺', 'کاربران', $user['role'] === 'admin'];
    $nav[] = ['settings', '/admin/?a=settings', '⚙', 'تنظیمات', $user['role'] === 'admin'];
    $nav[] = ['profile', '/admin/?a=profile', '☰', 'پروفایل من', true];
    foreach ($nav as [$id, $href, $ic, $label, $ok]) if ($ok) echo '<a href="' . $href . '" class="' . ($active === $id ? 'on' : '') . '"><span>' . $ic . '</span> ' . e($label) . '</a>';
    echo '</nav><div class="side-foot"><a href="/" target="_blank" rel="noopener">مشاهده سایت ↗</a><form method="post" action="/admin/?a=logout"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><button class="link">خروج (' . e($user['name']) . ')</button></form></div></aside><main class="main">';
    $f = flash_take(); if ($f) echo '<p class="alert ok">' . e($f) . '</p>';
    echo '<h1 class="ptitle">' . e($title) . '</h1>';
}
function layout_end(): void { echo '</main></div><script src="/admin/admin.js"></script></body></html>'; }

function badge(string $s): string { $m = ['draft' => ['پیش‌نویس', 'gray'], 'pending' => ['در انتظار بازبینی', 'amber'], 'published' => ['منتشرشده', 'green']][$s] ?? [$s, 'gray']; return '<span class="badge ' . $m[1] . '">' . $m[0] . '</span>'; }

function render_page(string $a, array $user, string $type): void {
    switch ($a) {
        case 'list': page_list($user, $type); break;
        case 'edit': page_edit($user, $type); break;
        case 'stats': page_stats($user); break;
        case 'content': page_content($user); break;
        case 'news': page_news($user); break;
        case 'tv': page_tv($user); break;
        case 'books': page_books($user); break;
        case 'media': page_media($user); break;
        case 'messages': page_messages($user); break;
        case 'comments': page_comments($user); break;
        case 'users': page_users($user); break;
        case 'profile': page_profile($user); break;
        case 'settings': page_settings($user); break;
        default: page_dashboard($user);
    }
}

function page_dashboard(array $user): void {
    layout_start('پیشخوان', 'dashboard', $user);
    echo '<div class="grid-cards">';
    foreach (TYPES as $k => $t) {
        if (!can($k)) continue;
        $c = (int)db()->query("SELECT COUNT(*) FROM items WHERE type='$k'")->fetchColumn();
        $p = (int)db()->query("SELECT COUNT(*) FROM items WHERE type='$k' AND status='pending'")->fetchColumn();
        echo '<a class="stat" href="/admin/?a=list&type=' . $k . '"><b>' . $c . '</b><span>' . e($t['label']) . ($p ? ' · ' . $p . ' در انتظار بازبینی' : '') . '</span></a>';
    }
    echo '</div><div class="quick"><h2>افزودن سریع</h2>';
    foreach (TYPES as $k => $t) if (can($k)) echo '<a class="btn" href="/admin/?a=edit&type=' . $k . '">+ ' . e($t['one']) . ' جدید</a> ';
    echo '</div>';
    if (can('messages')) {
        $rows = db()->query('SELECT * FROM messages ORDER BY id DESC LIMIT 5')->fetchAll();
        echo '<h2>آخرین پیام‌ها</h2>';
        if (!$rows) echo '<p class="muted">هنوز پیامی نرسیده است.</p>';
        else { echo '<table class="tbl"><tbody>'; foreach ($rows as $m) echo '<tr><td>' . ($m['status'] === 'new' ? '<b>●</b> ' : '') . e($m['name']) . '</td><td>' . e($m['subject']) . '</td><td class="muted">' . e(mb_substr($m['message'], 0, 80)) . '</td></tr>'; echo '</tbody></table>'; }
    }
    layout_end();
}

function page_list(array $user, string $type): void {
    if (!can($type)) { http_response_code(403); exit('دسترسی ندارید'); }
    $t = TYPES[$type];
    layout_start($t['label'], 'list-' . $type, $user);
    echo '<p><a class="btn primary" href="/admin/?a=edit&type=' . $type . '">+ ' . e($t['one']) . ' جدید</a></p>';
    if (in_array($type, XP_TYPES, true)) xp_seed_box();
    $q = 'SELECT i.*, u.name AS author FROM items i LEFT JOIN users u ON u.id=i.author_id WHERE i.type=?';
    $args = [$type];
    if (!can('others')) { $q .= ' AND i.author_id=?'; $args[] = $user['id']; }
    $st = $_GET['status'] ?? '';
    if (in_array($st, ['draft', 'pending', 'published'], true)) { $q .= ' AND i.status=?'; $args[] = $st; }
    $q .= ' ORDER BY i.updated DESC LIMIT 300';
    $s = db()->prepare($q); $s->execute($args); $rows = $s->fetchAll();
    echo '<p class="filters"><a href="/admin/?a=list&type=' . $type . '">همه</a> · <a href="/admin/?a=list&type=' . $type . '&status=published">منتشرشده</a> · <a href="/admin/?a=list&type=' . $type . '&status=pending">در انتظار بازبینی</a> · <a href="/admin/?a=list&type=' . $type . '&status=draft">پیش‌نویس</a></p>';
    if (!$rows) { echo '<p class="muted">موردی وجود ندارد.</p>'; layout_end(); return; }
    echo '<table class="tbl"><thead><tr><th></th><th>عنوان</th><th>زبان‌ها</th><th>وضعیت</th><th>نویسنده</th><th>آخرین ویرایش</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $tr = db()->prepare('SELECT lang,title FROM item_tr WHERE item_id=? AND title<>""'); $tr->execute([$r['id']]); $trs = $tr->fetchAll(PDO::FETCH_KEY_PAIR);
        $title = $trs['fa'] ?? ($trs['en'] ?? ($trs['ru'] ?? '(بدون عنوان)'));
        $img = $r['image'] ? media_get((int)$r['image']) : null;
        $ic = '';
        if ($type === 'experience') { $ex = json_decode($r['extra'] ?: '{}', true) ?: []; $svg = xp_icons()[$ex['icon'] ?? '']['svg'] ?? ''; $ic = $svg ? '<span class="xpi-mini">' . $svg . '</span>' : ''; }
        echo '<tr><td>' . ($img ? '<img class="thumb" src="' . e(media_url($img, true)) . '" alt="">' : $ic) . '</td><td><a href="/admin/?a=edit&type=' . $type . '&id=' . $r['id'] . '"><b>' . e($title) . '</b></a>' . ($type === 'page' ? ' <span class="muted">(' . e(PAGE_KEYS[$r['page_key']] ?? '') . ')</span>' : '') . '</td><td>';
        foreach (LANGS as $l => $n) echo '<span class="lg ' . (isset($trs[$l]) ? 'on' : '') . '">' . strtoupper($l) . '</span>';
        echo '</td><td>' . badge($r['status']) . '</td><td>' . e($r['author']) . '</td><td class="muted">' . e(format_date((int)$r['updated'])) . '</td><td class="acts">';
        if (can('publish') && $r['status'] !== 'published') echo '<form method="post" action="/admin/?a=status"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="status" value="published"><button class="link">انتشار</button></form>';
        echo '<form method="post" action="/admin/?a=delete" data-confirm="این مورد برای همیشه حذف شود؟"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="link danger">حذف</button></form></td></tr>';
    }
    echo '</tbody></table>';
    layout_end();
}

function field_x(string $k, string $label, array $extra, string $type = 'text', string $dir = ''): string {
    return '<label>' . e($label) . '<input type="' . $type . '" name="x_' . $k . '" value="' . e($extra[$k] ?? '') . '"' . ($dir ? ' dir="' . $dir . '"' : '') . '></label>';
}

function page_edit(array $user, string $type): void {
    if (!can($type)) { http_response_code(403); exit('دسترسی ندارید'); }
    $id = (int)($_GET['id'] ?? 0); $it = $id ? item_load($id) : null;
    if ($id && !$it) { http_response_code(404); exit('یافت نشد'); }
    if ($it && ($it['author_id'] != $user['id'] && !can('others'))) { http_response_code(403); exit('این مورد متعلق به شما نیست'); }
    $type = $it['type'] ?? $type; $t = TYPES[$type];
    if (in_array($type, XP_TYPES, true)) { page_edit_xp($user, $type, $it); return; }
    layout_start(($it ? 'ویرایش ' : 'افزودن ') . $t['one'], 'list-' . $type, $user);
    $x = $it['extra'] ?? []; $img = !empty($it['image']) ? media_get((int)$it['image']) : null;
    echo '<form method="post" action="/admin/?a=save" class="editor" data-editor><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="type" value="' . e($type) . '"><input type="hidden" name="id" value="' . $id . '"><div class="cols"><div class="col-main">';
    echo '<div class="tabs" role="tablist">'; $first = true;
    foreach (LANGS as $l => $n) { echo '<button type="button" class="tab' . ($first ? ' on' : '') . '" data-tab="' . $l . '">' . e($n) . '</button>'; $first = false; }
    echo '</div>'; $first = true;
    foreach (LANGS as $l => $n) {
        $tr = $it['tr'][$l] ?? ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => ''];
        $dir = $l === 'fa' ? 'rtl' : 'ltr';
        echo '<section class="pane' . ($first ? ' on' : '') . '" data-pane="' . $l . '"><label>عنوان (' . e($n) . ')<input name="title_' . $l . '" value="' . e($tr['title']) . '" dir="' . $dir . '" class="big"></label>';
        if ($type !== 'page') echo '<label>نشانی (اسلاگ) <small class="muted">— خالی بگذارید، خودکار از عنوان ساخته می‌شود</small><input name="slug_' . $l . '" value="' . e($tr['slug']) . '" dir="ltr"></label>';
        if ($type !== 'gallery') echo '<label>چکیده کوتاه<textarea name="excerpt_' . $l . '" rows="2" dir="' . $dir . '">' . e($tr['excerpt']) . '</textarea></label>';
        echo '<div class="lbl">متن</div><div class="toolbar" data-for="' . $l . '"><button type="button" data-cmd="bold"><b>B</b></button><button type="button" data-cmd="italic"><i>I</i></button><button type="button" data-cmd="underline"><u>U</u></button><button type="button" data-block="h2">H2</button><button type="button" data-block="h3">H3</button><button type="button" data-block="p">¶</button><button type="button" data-cmd="insertUnorderedList">• فهرست</button><button type="button" data-cmd="insertOrderedList">۱. فهرست</button><button type="button" data-block="blockquote">❝</button><button type="button" data-link>پیوند</button><button type="button" data-cmd="unlink">حذف پیوند</button><button type="button" data-media-insert>🖼 تصویر</button><button type="button" data-cmd="removeFormat">پاک‌کردن قالب</button></div>';
        echo '<div class="rte" contenteditable="true" data-rte="' . $l . '" dir="' . $dir . '">' . $tr['body'] . '</div><textarea hidden name="body_' . $l . '" data-body="' . $l . '"></textarea></section>';
        $first = false;
    }
    echo '</div><div class="col-side"><div class="box"><h3>انتشار</h3><label>وضعیت<select name="status">';
    $cur = $it['status'] ?? 'draft';
    foreach (['draft' => 'پیش‌نویس', 'pending' => 'در انتظار بازبینی', 'published' => 'منتشرشده'] as $k => $v) { if ($k === 'published' && !can('publish')) continue; echo '<option value="' . $k . '"' . ($cur === $k ? ' selected' : '') . '>' . $v . '</option>'; }
    echo '</select></label>';
    if (!can('publish')) echo '<p class="muted small">نویسنده نمی‌تواند مستقیم منتشر کند؛ «در انتظار بازبینی» را بزنید تا ویراستار منتشر کند.</p>';
    echo '<button class="btn primary wide">ذخیره</button>';
    if ($it) echo '<p class="muted small">ایجاد: ' . e(format_date((int)$it['created'])) . '</p>';
    echo '</div>';
    if ($type === 'page') {
        echo '<div class="box"><h3>کدام صفحه؟</h3><select name="page_key">';
        foreach (PAGE_KEYS as $k => $v) echo '<option value="' . $k . '"' . (($it['page_key'] ?? '') === $k ? ' selected' : '') . '>' . e($v) . '</option>';
        echo '</select><p class="muted small">متنِ وارد‌شده جای متن فعلی همان صفحه در سایت می‌نشیند. اگر زبانی را خالی بگذارید، متن قبلی آن زبان می‌ماند.</p></div>';
    }
    if ($type !== 'page') {
        echo '<div class="box"><h3>تصویر شاخص</h3><input type="hidden" name="image" value="' . (int)($it['image'] ?? 0) . '" data-image-input><div class="imgprev" data-image-prev>' . ($img ? '<img src="' . e(media_url($img, true)) . '" alt="">' : '<span class="muted">انتخاب نشده</span>') . '</div><button type="button" class="btn" data-media-pick>انتخاب / آپلود تصویر</button> <button type="button" class="link danger" data-image-clear>حذف</button></div>';
    }
    if ($type === 'product') echo '<div class="box"><h3>مشخصات محصول</h3>' . field_x('price', 'قیمت (مثلاً ۲۵۰٬۰۰۰ تومان)', $x) . field_x('stock', 'موجودی', $x) . field_x('sku', 'کد محصول', $x, 'text', 'ltr') . '</div>';
    if ($type === 'event') echo '<div class="box"><h3>زمان و مکان</h3>' . field_x('date_start', 'تاریخ شروع', $x, 'date', 'ltr') . field_x('date_end', 'تاریخ پایان', $x, 'date', 'ltr') . field_x('place', 'مکان', $x) . '</div>';
    if ($type === 'stay') echo '<div class="box"><h3>مشخصات اقامتگاه</h3>' . field_x('capacity', 'ظرفیت (نفر)', $x) . field_x('price', 'قیمت هر شب', $x) . field_x('amenities', 'امکانات (با ویرگول جدا کنید)', $x) . '</div>';
    echo '</div></div></form>';
    layout_end();
}

function page_media(array $user): void {
    if (!can('media')) { http_response_code(403); exit('دسترسی ندارید'); }
    layout_start('کتابخانه رسانه', 'media', $user);
    echo '<form method="post" action="/admin/?a=media_upload" enctype="multipart/form-data" class="upload"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="file" name="files[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required> <button class="btn primary">آپلود تصاویر</button> <span class="muted small">JPG / PNG / WebP / GIF، تا ۸ مگابایت؛ خودکار بهینه می‌شود.</span></form>';
    $rows = db()->query('SELECT * FROM media ORDER BY id DESC LIMIT 300')->fetchAll();
    if (!$rows) echo '<p class="muted">هنوز تصویری آپلود نشده است.</p>';
    echo '<div class="mgrid">';
    foreach ($rows as $m) {
        echo '<div class="mcard"><a href="' . e(media_url($m)) . '" target="_blank" rel="noopener"><img src="' . e(media_url($m, true)) . '" alt="" loading="lazy"></a><form method="post" action="/admin/?a=media_save"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $m['id'] . '">';
        foreach (['fa' => 'متن جایگزین فارسی', 'en' => 'Alt (EN)', 'ru' => 'Alt (RU)'] as $l => $lab) echo '<input name="alt_' . $l . '" value="' . e($m['alt_' . $l]) . '" placeholder="' . e($lab) . '" dir="' . ($l === 'fa' ? 'rtl' : 'ltr') . '">';
        echo '<button class="btn small">ذخیره</button></form><p class="muted small" dir="ltr">' . e('/uploads/' . $m['file']) . ' · ' . (int)$m['w'] . '×' . (int)$m['h'] . '</p>';
        if (can('others')) echo '<form method="post" action="/admin/?a=media_delete" data-confirm="این تصویر حذف شود؟"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $m['id'] . '"><button class="link danger">حذف</button></form>';
        echo '</div>';
    }
    echo '</div>'; layout_end();
}

function page_messages(array $user): void {
    if (!can('messages')) { http_response_code(403); exit('دسترسی ندارید'); }
    layout_start('پیام‌ها و درخواست‌ها', 'messages', $user);
    $rows = db()->query('SELECT * FROM messages ORDER BY id DESC LIMIT 300')->fetchAll();
    if (!$rows) echo '<p class="muted">هنوز پیامی نرسیده است.</p>';
    foreach ($rows as $m) {
        echo '<article class="msg ' . ($m['status'] === 'new' ? 'new' : '') . '"><header><b>' . e($m['name']) . '</b> <span class="muted" dir="ltr">' . e($m['phone']) . ($m['email'] ? ' · ' . e($m['email']) : '') . '</span><span class="muted"> · ' . e($m['subject']) . ($m['ref'] ? ' · مرجع: ' . e($m['ref']) : '') . ' · ' . e(format_date((int)$m['created'])) . ' · ' . strtoupper(e($m['lang'])) . '</span></header><p>' . nl2br(e($m['message'])) . '</p><form method="post" action="/admin/?a=msg_action"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $m['id'] . '"><button class="link" name="do" value="' . ($m['status'] === 'new' ? 'read' : 'unread') . '">' . ($m['status'] === 'new' ? 'علامت‌گذاری به‌عنوان خوانده‌شده' : 'علامت‌گذاری به‌عنوان جدید') . '</button> <button class="link danger" name="do" value="delete" data-confirm="پیام حذف شود؟">حذف</button></form></article>';
    }
    layout_end();
}

function page_comments(array $user): void {
    if (!can('messages')) { http_response_code(403); exit('دسترسی ندارید'); }
    layout_start('نظرهای بازدیدکنندگان', 'comments', $user);
    $rows = db()->query("SELECT * FROM comments ORDER BY (status='pending') DESC, id DESC LIMIT 300")->fetchAll();
    if (!$rows) echo '<p class="muted">هنوز نظری ثبت نشده است.</p>';
    foreach ($rows as $c) {
        $ok = $c['status'] === 'approved';
        echo '<article class="msg ' . ($ok ? '' : 'new') . '"><header><b>' . e($c['name']) . '</b> <span class="muted"> · ' . ($c['rating'] ? str_repeat('★', (int)$c['rating']) . ' · ' : '') . e(format_date((int)$c['created'])) . ' · ' . strtoupper(e($c['lang'])) . ' · ' . ($ok ? 'منتشرشده' : 'در انتظار تأیید') . '</span></header><p>' . nl2br(e($c['text'])) . '</p><form method="post" action="/admin/?a=cm_action"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $c['id'] . '"><button class="link" name="do" value="' . ($ok ? 'hide' : 'approve') . '">' . ($ok ? 'برداشتن از سایت' : 'تأیید و نمایش در سایت') . '</button> <button class="link danger" name="do" value="delete" data-confirm="نظر حذف شود؟">حذف</button></form></article>';
    }
    layout_end();
}

function page_users(array $user): void {
    if ($user['role'] !== 'admin') { http_response_code(403); exit('دسترسی ندارید'); }
    layout_start('کاربران', 'users', $user);
    $rows = db()->query('SELECT * FROM users ORDER BY id')->fetchAll();
    echo '<table class="tbl"><thead><tr><th>نام</th><th>نام کاربری</th><th>نقش</th><th></th></tr></thead><tbody>';
    foreach ($rows as $u) {
        echo '<tr><td colspan="4"><form method="post" action="/admin/?a=user_save" class="urow"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $u['id'] . '"><input name="name" value="' . e($u['name']) . '" required><input name="username" value="' . e($u['username']) . '" dir="ltr" required><input name="email" value="' . e($u['email']) . '" dir="ltr" placeholder="ایمیل"><select name="role">';
        foreach (ROLES as $k => $v) echo '<option value="' . $k . '"' . ($u['role'] === $k ? ' selected' : '') . '>' . e($v) . '</option>';
        echo '</select><input name="password" type="password" placeholder="رمز جدید (اختیاری)" dir="ltr" autocomplete="new-password"><button class="btn small">ذخیره</button></form>';
        if ((int)$u['id'] !== (int)$user['id']) echo '<form method="post" action="/admin/?a=user_delete" data-confirm="این کاربر حذف شود؟"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="' . $u['id'] . '"><button class="link danger">حذف</button></form>';
        echo '</td></tr>';
    }
    echo '</tbody></table><div class="box"><h3>کاربر جدید</h3><form method="post" action="/admin/?a=user_save" class="urow"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="id" value="0"><input name="name" placeholder="نام" required><input name="username" placeholder="نام کاربری" dir="ltr" required><input name="email" placeholder="ایمیل" dir="ltr"><select name="role">';
    foreach (ROLES as $k => $v) echo '<option value="' . $k . '"' . ($k === 'author' ? ' selected' : '') . '>' . e($v) . '</option>';
    echo '</select><input name="password" type="password" placeholder="رمز (حداقل ۱۰ نویسه)" dir="ltr" required autocomplete="new-password"><button class="btn primary small">افزودن</button></form>';
    $rows = [
        ['نوشته‌ها (بلاگ)', '✔', '✔', '', '✔'],
        ['صفحات (درباره، تجربه‌ها و …)', '✔', '✔', '', ''],
        ['محصولات / صنایع دستی', '✔', '✔', '✔', ''],
        ['رویدادها', '✔', '✔', '✔', ''],
        ['اقامتگاه‌ها', '✔', '✔', '✔', ''],
        ['گالری', '✔', '✔', '✔', ''],
        ['تجربه‌ها و مجری‌ها', '✔', '✔', '', ''],
        ['کتابخانهٔ رسانه (آپلود عکس)', '✔', '✔', '✔', '✔'],
        ['پیام‌ها و نظرهای بازدیدکنندگان', '✔', '✔', '✔', ''],
        ['انتشار مستقیم', '✔', '✔', '✔', '— (نیاز به تأیید)'],
        ['ویرایش و حذف مطالب دیگران', '✔', '✔', '✔', ''],
        ['کاربران و تنظیمات', '✔', '', '', ''],
    ];
    echo '<h3>سطح دسترسی هر نقش</h3><table class="tbl"><thead><tr><th>امکان</th><th>مدیر کل</th><th>ویراستار</th><th>مدیر فروشگاه و اقامت</th><th>نویسنده</th></tr></thead><tbody>';
    foreach ($rows as $r) { echo '<tr>'; foreach ($r as $i => $c) echo '<td' . ($i ? ' style="text-align:center"' : '') . '>' . e($c) . '</td>'; echo '</tr>'; }
    echo '</tbody></table><p class="muted small">مدیر کل می‌تواند کاربر بسازد، نقش بدهد، رمز را تغییر دهد یا کاربر را حذف کند. نقش خودش قابل کاهش نیست.</p></div>';
    layout_end();
}

function page_profile(array $user): void {
    layout_start('پروفایل من', 'profile', $user);
    echo '<form method="post" action="/admin/?a=profile" class="box narrow"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><label>نام<input name="name" value="' . e($user['name']) . '" required></label><label>ایمیل<input name="email" value="' . e($user['email']) . '" dir="ltr"></label><hr><label>رمز فعلی (فقط برای تغییر رمز)<input type="password" name="current" dir="ltr" autocomplete="current-password"></label><label>رمز جدید (حداقل ۱۰ نویسه)<input type="password" name="password" dir="ltr" autocomplete="new-password"></label><button class="btn primary">ذخیره</button></form>';
    layout_end();
}

function page_settings(array $user): void {
    if ($user['role'] !== 'admin') { http_response_code(403); exit('دسترسی ندارید'); }
    layout_start('تنظیمات', 'settings', $user);
    echo '<form method="post" action="/admin/?a=settings" class="box narrow"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><label>ایمیل دریافت پیام‌های تماس و درخواست‌ها<input name="notify_email" value="' . e(setting('notify_email')) . '" dir="ltr" placeholder="info@heleyleh-bu.ir"></label>';
    foreach (['fa' => 'فارسی', 'en' => 'English', 'ru' => 'Русский'] as $l => $n) echo '<label>یادداشت زیر لیست محصولات (' . $n . ')<textarea name="shop_note_' . $l . '" rows="2" dir="' . ($l === 'fa' ? 'rtl' : 'ltr') . '">' . e(setting('shop_note_' . $l)) . '</textarea></label>';
    echo '<button class="btn primary">ذخیره</button></form>';
    xp_translate_settings_box();
    echo '<div class="box narrow"><h3>اطلاعات فنی</h3><p class="muted small" dir="ltr">PHP ' . e(PHP_VERSION) . ' · data: ' . e(CMS_DATA) . ' · uploads: ' . (is_writable(CMS_UPLOADS) ? 'writable' : 'NOT writable') . ' · GD: ' . (function_exists('imagecreatefromstring') ? 'yes' : 'no') . '</p></div>';
    layout_end();
}
