<?php
declare(strict_types=1);
require __DIR__ . '/../cms-core/lib.php';
require __DIR__ . '/views.php';
require __DIR__ . '/xp.php';
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'none'; form-action 'self'");

try { db(); } catch (Throwable $ex) { error_log('cms db: ' . $ex->getMessage()); http_response_code(500); exit('خطای دیتابیس؛ قابل‌نوشتن‌بودن پوشهٔ داده و فعال‌بودن pdo_sqlite را بررسی کنید (جزئیات در error_log هاست).'); }
if (!(int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn()) { header('Location: /install.php'); exit; }

$a = $_GET['a'] ?? 'dashboard';
$flash = '';
function redirect(string $to): never { header('Location: ' . $to); exit; }
function json_out($d, int $code = 200): never { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

/* ---- ورود / خروج ---- */
if ($a === 'login') {
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        if (too_many_attempts()) $err = 'تلاش‌های ناموفق زیاد بود؛ ۱۵ دقیقه بعد دوباره امتحان کنید.';
        elseif (login((string)($_POST['u'] ?? ''), (string)($_POST['p'] ?? ''))) redirect('/admin/');
        else $err = 'نام کاربری یا رمز عبور درست نیست.';
    }
    layout_login($err); exit;
}
$user = current_user();
if (!$user) redirect('/admin/?a=login');
if ($a === 'logout') { csrf_check(); logout(); redirect('/admin/?a=login'); }

$type = $_GET['type'] ?? 'post';
if (!isset(TYPES[$type])) $type = 'post';
$post = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($post) csrf_check();

/* ---- تجربه‌ها و مجری‌ها (ذخیره با ترجمهٔ خودکار؛ منطق در xp.php) ---- */
if ($a === 'save' && $post && in_array((string)($_POST['type'] ?? ''), XP_TYPES, true)) xp_save($user);
if ($a === 'xp_seed' && $post) {
    if (!can('experience')) { http_response_code(403); exit('دسترسی ندارید'); }
    try { $n = xp_seed((int)$user['id']); $_SESSION['flash'] = $n ? $n . ' مورد (تجربه و مجری) وارد پنل شد.' : 'پنل از قبل داده دارد؛ چیزی وارد نشد.'; }
    catch (Throwable $ex) { error_log('cms seed: ' . $ex->getMessage()); $_SESSION['flash'] = 'وارد کردن ناموفق بود (جزئیات در error_log هاست).'; }
    redirect('/admin/?a=list&type=experience');
}
if (($a === 'tr_settings' || $a === 'tr_test') && $post) {
    if ($user['role'] !== 'admin') { http_response_code(403); exit('دسترسی ندارید'); }
    $a === 'tr_settings' ? xp_save_tr_settings() : xp_test_translate();
}

/* ---- ذخیره محتوا ---- */
if ($a === 'save' && $post) {
    $type = $_POST['type'] ?? 'post'; if (!isset(TYPES[$type]) || !can($type)) { http_response_code(403); exit('دسترسی ندارید'); }
    $id = (int)($_POST['id'] ?? 0);
    $old = $id ? item_load($id) : null;
    if ($id && !$old) { http_response_code(404); exit('یافت نشد'); }
    if ($old && $old['author_id'] != $user['id'] && !can('others')) { http_response_code(403); exit('این مورد متعلق به شما نیست'); }
    $status = in_array($_POST['status'] ?? '', ['draft', 'pending', 'published'], true) ? $_POST['status'] : 'draft';
    if ($status === 'published' && !can('publish')) $status = 'pending';
    $extra = [];
    foreach (['price', 'stock', 'sku', 'date_start', 'date_end', 'place', 'capacity', 'amenities'] as $k) {
        if (isset($_POST['x_' . $k])) $extra[$k] = mb_substr(trim((string)$_POST['x_' . $k]), 0, 300);
    }
    $img = (int)($_POST['image'] ?? 0) ?: null;
    $pageKey = $type === 'page' ? ($_POST['page_key'] ?? '') : null;
    if ($type === 'page' && !isset(PAGE_KEYS[$pageKey])) { $_SESSION['flash'] = 'کلید صفحه معتبر نیست.'; redirect('/admin/?a=edit&type=page&id=' . $id); }
    $pdo = db(); $pdo->beginTransaction();
    try {
        if ($id) {
            $pub = $old['published_at'];
            if ($status === 'published' && !$pub) $pub = now();
            $pdo->prepare('UPDATE items SET status=?, image=?, extra=?, page_key=?, updated=?, published_at=? WHERE id=?')
                ->execute([$status, $img, json_encode($extra, JSON_UNESCAPED_UNICODE), $pageKey, now(), $pub, $id]);
        } else {
            $pdo->prepare('INSERT INTO items(type,page_key,status,image,extra,author_id,created,updated,published_at) VALUES(?,?,?,?,?,?,?,?,?)')
                ->execute([$type, $pageKey, $status, $img, json_encode($extra, JSON_UNESCAPED_UNICODE), $user['id'], now(), now(), $status === 'published' ? now() : null]);
            $id = (int)$pdo->lastInsertId();
        }
        foreach (array_keys(LANGS) as $l) {
            $title = mb_substr(trim((string)($_POST["title_$l"] ?? '')), 0, 250);
            $body = clean_html((string)($_POST["body_$l"] ?? ''));
            $exc = mb_substr(trim((string)($_POST["excerpt_$l"] ?? '')), 0, 600);
            if ($type === 'page') { $slug = SECTIONS[$l][$pageKey]; }
            else {
                $slug = slugify((string)($_POST["slug_$l"] ?? '')) ?: slugify($title);
                $slug = $title === '' ? '' : unique_slug($type, $l, $slug, $id);
            }
            $pdo->prepare('INSERT INTO item_tr(item_id,lang,title,slug,excerpt,body) VALUES(?,?,?,?,?,?) ON CONFLICT(item_id,lang) DO UPDATE SET title=excluded.title, slug=excluded.slug, excerpt=excluded.excerpt, body=excluded.body')
                ->execute([$id, $l, $title, $slug, $exc, $body]);
        }
        $pdo->commit();
    } catch (Throwable $ex) { $pdo->rollBack(); error_log('cms save: ' . $ex->getMessage()); http_response_code(500); exit('خطا در ذخیره؛ دوباره تلاش کنید.'); }
    $_SESSION['flash'] = 'ذخیره شد.';
    redirect('/admin/?a=edit&type=' . $type . '&id=' . $id);
}
if ($a === 'delete' && $post) {
    $id = (int)($_POST['id'] ?? 0); $it = item_load($id);
    if ($it && $it['type'] === 'person' && ($used = xp_person_usage($id))) {   // حذف مجری در حال استفاده، تجربه را به‌اشتباه «آزاد» نشان می‌داد
        $_SESSION['flash'] = 'این مجری در این تجربه‌ها انتخاب شده و حذف نشد: ' . implode('، ', $used) . ' — ابتدا او را از آن‌ها بردارید.';
        redirect('/admin/?a=list&type=person');
    }
    if ($it && can($it['type']) && ($it['author_id'] == $user['id'] || can('others'))) {
        db()->prepare('DELETE FROM items WHERE id=?')->execute([$id]); $_SESSION['flash'] = 'حذف شد.';
        redirect('/admin/?a=list&type=' . $it['type']);
    }
    http_response_code(403); exit('دسترسی ندارید');
}
if ($a === 'status' && $post) {
    $id = (int)($_POST['id'] ?? 0); $it = item_load($id); $st = $_POST['status'] ?? '';
    if ($it && can($it['type']) && can('publish') && in_array($st, ['draft', 'pending', 'published'], true)) {
        db()->prepare('UPDATE items SET status=?, published_at=COALESCE(published_at, ?), updated=? WHERE id=?')->execute([$st, $st === 'published' ? now() : null, now(), $id]);
    }
    redirect('/admin/?a=list&type=' . ($it['type'] ?? 'post'));
}

/* ---- رسانه ---- */
if ($a === 'media_json') {
    if (!can('media')) json_out([], 403);
    $rows = db()->query('SELECT id,file,thumb,alt_fa,w,h FROM media ORDER BY id DESC LIMIT 200')->fetchAll();
    json_out(array_map(fn($m) => ['id' => (int)$m['id'], 'url' => '/uploads/' . $m['file'], 'thumb' => '/uploads/' . ($m['thumb'] ?: $m['file']), 'alt' => $m['alt_fa']], $rows));
}
if ($a === 'media_upload' && $post) {
    if (!can('media')) json_out(['error' => 'دسترسی ندارید'], 403);
    $out = []; $errs = [];
    $files = $_FILES['files'] ?? null;
    if ($files) {
        $n = is_array($files['name']) ? count($files['name']) : 0;
        for ($i = 0; $i < $n; $i++) {
            $f = ['name' => $files['name'][$i], 'tmp_name' => $files['tmp_name'][$i], 'size' => $files['size'][$i], 'error' => $files['error'][$i]];
            try { $m = save_upload($f, (int)$user['id']); $out[] = ['id' => (int)$m['id'], 'url' => media_url($m), 'thumb' => media_url($m, true)]; }
            catch (Throwable $ex) { $errs[] = $f['name'] . ': ' . $ex->getMessage(); }
        }
    }
    if (isset($_GET['ajax'])) json_out(['files' => $out, 'errors' => $errs]);
    $_SESSION['flash'] = count($out) . ' تصویر آپلود شد.' . ($errs ? ' خطا: ' . implode(' | ', $errs) : '');
    redirect('/admin/?a=media');
}
if ($a === 'media_save' && $post) {
    if (!can('media')) { http_response_code(403); exit; }
    $id = (int)$_POST['id'];
    db()->prepare('UPDATE media SET alt_fa=?, alt_en=?, alt_ru=? WHERE id=?')->execute([mb_substr((string)$_POST['alt_fa'], 0, 250), mb_substr((string)$_POST['alt_en'], 0, 250), mb_substr((string)$_POST['alt_ru'], 0, 250), $id]);
    $_SESSION['flash'] = 'متن جایگزین ذخیره شد.'; redirect('/admin/?a=media');
}
if ($a === 'media_delete' && $post) {
    if (!can('others')) { http_response_code(403); exit('فقط ویراستار یا مدیر می‌تواند حذف کند'); }
    $m = media_get((int)$_POST['id']);
    if ($m) {
        @unlink(CMS_UPLOADS . '/' . $m['file']); if ($m['thumb']) @unlink(CMS_UPLOADS . '/' . $m['thumb']);
        db()->prepare('DELETE FROM media WHERE id=?')->execute([$m['id']]);
        db()->prepare('UPDATE items SET image=NULL WHERE image=?')->execute([$m['id']]);
    }
    $_SESSION['flash'] = 'تصویر حذف شد.'; redirect('/admin/?a=media');
}

/* ---- پیام‌ها ---- */
if ($a === 'msg_action' && $post) {
    if (!can('messages')) { http_response_code(403); exit; }
    $id = (int)$_POST['id'];
    if (($_POST['do'] ?? '') === 'delete') db()->prepare('DELETE FROM messages WHERE id=?')->execute([$id]);
    else db()->prepare('UPDATE messages SET status=? WHERE id=?')->execute([($_POST['do'] ?? '') === 'unread' ? 'new' : 'read', $id]);
    redirect('/admin/?a=messages');
}

/* ---- نظرها ---- */
if ($a === 'cm_action' && $post) {
    if (!can('messages')) { http_response_code(403); exit; }
    $id = (int)$_POST['id']; $do = $_POST['do'] ?? '';
    if ($do === 'delete') db()->prepare('DELETE FROM comments WHERE id=?')->execute([$id]);
    else db()->prepare('UPDATE comments SET status=? WHERE id=?')->execute([$do === 'approve' ? 'approved' : 'pending', $id]);
    redirect('/admin/?a=comments');
}

/* ---- کاربران / پروفایل / تنظیمات ---- */
if ($a === 'user_save' && $post) {
    if ($user['role'] !== 'admin') { http_response_code(403); exit; }
    $id = (int)($_POST['id'] ?? 0); $un = trim((string)($_POST['username'] ?? '')); $name = mb_substr(trim((string)($_POST['name'] ?? '')), 0, 100); $role = $_POST['role'] ?? 'author'; $pw = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $un) || $name === '' || !isset(ROLES[$role])) { $_SESSION['flash'] = 'نام کاربری (۳ تا ۳۰ حرف لاتین/عدد) و نام الزامی است.'; redirect('/admin/?a=users'); }
    if (($id === 0 || $pw !== '') && mb_strlen($pw) < 10) { $_SESSION['flash'] = 'رمز عبور حداقل ۱۰ نویسه باشد.'; redirect('/admin/?a=users'); }
    if ($id === (int)$user['id'] && $role !== 'admin') { $_SESSION['flash'] = 'نقش خودتان را نمی‌توانید کاهش دهید.'; redirect('/admin/?a=users'); }
    try {
        if ($id) {
            db()->prepare('UPDATE users SET username=?, name=?, email=?, role=? WHERE id=?')->execute([$un, $name, trim((string)$_POST['email']), $role, $id]);
            if ($pw !== '') {
                db()->prepare('UPDATE users SET pass=? WHERE id=?')->execute([password_hash($pw, PASSWORD_DEFAULT), $id]);
                kill_user_sessions($id, $id === (int)$user['id'] ? (string)($GLOBALS['__sess_token'] ?? '') : '');   // نشست‌های قدیمی با رمز تازه بسته می‌شوند
            }
        } else {
            db()->prepare('INSERT INTO users(username,name,email,role,pass,created) VALUES(?,?,?,?,?,?)')->execute([$un, $name, trim((string)$_POST['email']), $role, password_hash($pw, PASSWORD_DEFAULT), now()]);
        }
        $_SESSION['flash'] = 'کاربر ذخیره شد.';
    } catch (Throwable $ex) { $_SESSION['flash'] = 'این نام کاربری قبلاً وجود دارد.'; }
    redirect('/admin/?a=users');
}
if ($a === 'user_delete' && $post) {
    $id = (int)$_POST['id'];
    if ($user['role'] === 'admin' && $id !== (int)$user['id']) { kill_user_sessions($id); db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]); db()->prepare('UPDATE items SET author_id=? WHERE author_id=?')->execute([$user['id'], $id]); $_SESSION['flash'] = 'کاربر حذف شد.'; }
    redirect('/admin/?a=users');
}
if ($a === 'profile' && $post) {
    $name = trim((string)($_POST['name'] ?? '')); $email = trim((string)($_POST['email'] ?? '')); $cur = (string)($_POST['current'] ?? ''); $new = (string)($_POST['password'] ?? '');
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $_SESSION['flash'] = 'ایمیل معتبر نیست.'; redirect('/admin/?a=profile'); }
    db()->prepare('UPDATE users SET name=?, email=? WHERE id=?')->execute([mb_substr($name, 0, 100) ?: $user['name'], $email, $user['id']]);
    if ($new !== '') {
        $h = db()->query('SELECT pass FROM users WHERE id=' . (int)$user['id'])->fetchColumn();
        if (!password_verify($cur, $h)) $_SESSION['flash'] = 'رمز فعلی درست نیست.';
        elseif (mb_strlen($new) < 10) $_SESSION['flash'] = 'رمز جدید حداقل ۱۰ نویسه باشد.';
        else { db()->prepare('UPDATE users SET pass=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]); kill_user_sessions((int)$user['id'], (string)($GLOBALS['__sess_token'] ?? '')); $_SESSION['flash'] = 'رمز تغییر کرد؛ ورودهای دیگر بسته شدند.'; }
    } else $_SESSION['flash'] = 'ذخیره شد.';
    redirect('/admin/?a=profile');
}
if ($a === 'settings' && $post && $user['role'] === 'admin') {
    $em = trim((string)($_POST['notify_email'] ?? ''));
    if ($em !== '' && !filter_var($em, FILTER_VALIDATE_EMAIL)) $_SESSION['flash'] = 'ایمیل معتبر نیست.';
    else { set_setting('notify_email', $em); set_setting('shop_note_fa', mb_substr((string)$_POST['shop_note_fa'], 0, 500)); set_setting('shop_note_en', mb_substr((string)$_POST['shop_note_en'], 0, 500)); set_setting('shop_note_ru', mb_substr((string)$_POST['shop_note_ru'], 0, 500)); $_SESSION['flash'] = 'تنظیمات ذخیره شد.'; }
    redirect('/admin/?a=settings');
}

render_page($a, $user, $type);
