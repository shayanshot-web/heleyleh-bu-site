<?php
/* پنل «تجربه‌ها» و «مجری‌ها»: فرم ویرایش، ذخیره با ترجمهٔ خودکار، وارد کردن داده‌های فعلی، تنظیمات ترجمه */

/** ذخیرهٔ تجربه یا مجری (از index.php صدا زده می‌شود) */
function xp_save(array $user): never {
    $type = (string)($_POST['type'] ?? '');
    if (!in_array($type, XP_TYPES, true) || !can($type)) { http_response_code(403); exit('دسترسی ندارید'); }
    $id = (int)($_POST['id'] ?? 0);
    $old = $id ? item_load($id) : null;
    if ($id && (!$old || $old['type'] !== $type)) { http_response_code(404); exit('یافت نشد'); }
    $back = '/admin/?a=edit&type=' . $type . ($id ? '&id=' . $id : '');

    $titles = [];
    foreach (array_keys(LANGS) as $l) $titles[$l] = mb_substr(trim((string)($_POST["title_$l"] ?? '')), 0, 250);
    if ($titles['fa'] === '') { $_SESSION['flash'] = 'نام فارسی الزامی است.'; redirect($back); }

    $status = ($_POST['status'] ?? '') === 'published' && can('publish') ? 'published' : 'draft';
    $notes = [];
    $extra = $old['extra'] ?? [];
    $img = null;

    if ($type === 'person') {
        $ph = trim((string)($_POST['x_phone'] ?? ''));
        if ($ph === '') unset($extra['phone']);
        elseif (($n = xp_norm_phone($ph)) !== null) $extra['phone'] = $n;
        else { unset($extra['phone']); $notes[] = 'شمارهٔ تماس معتبر نبود و ذخیره نشد (مثال درست: ۰۹۱۷۱۲۳۴۵۶۷).'; }
        $img = (int)($_POST['image'] ?? 0) ?: null;
        if ($img && !media_get($img)) $img = null;
    } else {
        $icon = (string)($_POST['icon'] ?? '');
        $icons = xp_icons();
        $extra['icon'] = isset($icons[$icon]) ? $icon : (array_key_first($icons) ?? 'park');
        $extra['sort'] = max(0, min(9999, (int)($_POST['x_sort'] ?? 100)));
        $valid = array_keys(xp_load('person', false));
        $who = [];
        foreach ((array)($_POST['who'] ?? []) as $pid) { $pid = (int)$pid; if (in_array($pid, $valid, true) && !in_array($pid, $who, true)) $who[] = $pid; }
        $extra['who'] = $who;
    }

    // ترجمهٔ خودکار: زبانی که خالی است (یا با گزینهٔ «ترجمهٔ مجدد») از روی فارسی ترجمه می‌شود
    $retr = !empty($_POST['retranslate']); $done = []; $failed = [];
    foreach (['en', 'ru'] as $l) {
        if ($retr || $titles[$l] === '') {
            $r = cms_translate($titles['fa'], $l);
            if ($r['ok']) { $titles[$l] = $r['text']; $done[] = strtoupper($l); }
            else $failed[strtoupper($l)] = $r['err'];
        }
    }

    $pdo = db(); $pdo->beginTransaction();
    try {
        if ($id) {
            $pub = $old['published_at'] ?: ($status === 'published' ? now() : null);
            $pdo->prepare('UPDATE items SET status=?, image=?, extra=?, updated=?, published_at=? WHERE id=?')
                ->execute([$status, $type === 'person' ? $img : null, json_encode($extra, JSON_UNESCAPED_UNICODE), now(), $pub, $id]);
        } else {
            $pdo->prepare('INSERT INTO items(type,status,image,extra,author_id,created,updated,published_at) VALUES(?,?,?,?,?,?,?,?)')
                ->execute([$type, $status, $img, json_encode($extra, JSON_UNESCAPED_UNICODE), $user['id'], now(), now(), $status === 'published' ? now() : null]);
            $id = (int)$pdo->lastInsertId();
        }
        $st = $pdo->prepare("INSERT INTO item_tr(item_id,lang,title,slug,excerpt,body) VALUES(?,?,?,'','','') ON CONFLICT(item_id,lang) DO UPDATE SET title=excluded.title");
        foreach ($titles as $l => $t) $st->execute([$id, $l, $t]);
        $pdo->commit();
    } catch (Throwable $ex) { $pdo->rollBack(); error_log('cms xp save: ' . $ex->getMessage()); http_response_code(500); exit('خطا در ذخیره؛ دوباره تلاش کنید.'); }

    $msg = 'ذخیره شد.';
    if ($done) $msg .= ' ترجمهٔ خودکار انجام شد (' . implode('، ', $done) . ')؛ لطفاً نتیجه را بازبینی کنید.';
    if ($failed) {
        $msg .= ' ترجمهٔ خودکار انجام نشد (' . implode(' | ', array_map(fn($k, $v) => "$k: $v", array_keys($failed), $failed)) . ')؛ ' . 'تا وارد کردن ترجمهٔ دستی، متن فارسی نمایش داده می‌شود.';
    }
    if ($notes) $msg .= ' ' . implode(' ', $notes);
    $_SESSION['flash'] = $msg;
    redirect('/admin/?a=edit&type=' . $type . '&id=' . $id);
}

/** فرم ویرایش تجربه / مجری (با همان ظاهر و تب‌های زبان ویرایشگر) */
function page_edit_xp(array $user, string $type, ?array $it): void {
    $t = TYPES[$type]; $id = (int)($it['id'] ?? 0); $x = $it['extra'] ?? [];
    layout_start(($it ? 'ویرایش ' : 'افزودن ') . $t['one'], 'list-' . $type, $user);
    echo '<form method="post" action="/admin/?a=save" class="editor" data-editor><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><input type="hidden" name="type" value="' . e($type) . '"><input type="hidden" name="id" value="' . $id . '"><div class="cols"><div class="col-main">';
    echo '<div class="tabs" role="tablist">'; $first = true;
    foreach (LANGS as $l => $n) { echo '<button type="button" class="tab' . ($first ? ' on' : '') . '" data-tab="' . $l . '">' . e($n) . '</button>'; $first = false; }
    echo '</div>'; $first = true;
    $label = $type === 'person' ? 'نام مجری' : 'نام تجربه';
    foreach (LANGS as $l => $n) {
        $val = $it['tr'][$l]['title'] ?? ''; $dir = $l === 'fa' ? 'rtl' : 'ltr';
        echo '<section class="pane' . ($first ? ' on' : '') . '" data-pane="' . $l . '"><label>' . e($label) . ' (' . e($n) . ')<input name="title_' . $l . '" value="' . e($val) . '" dir="' . $dir . '" class="big"' . ($l === 'fa' ? ' required' : '') . '></label>';
        echo $l === 'fa'
            ? '<p class="muted small">فقط نام فارسی را وارد کنید؛ هنگام ذخیره، نام انگلیسی و روسی خودکار ترجمه می‌شود (اگر خالی باشند).</p>'
            : '<p class="muted small">' . ($val === '' ? 'خالی است؛ هنگام ذخیره خودکار از فارسی ترجمه می‌شود. می‌توانید دستی هم بنویسید.' : 'می‌توانید متن را دستی اصلاح کنید.') . '</p>';
        echo '</section>'; $first = false;
    }
    echo '</div><div class="col-side"><div class="box"><h3>انتشار</h3><label>وضعیت<select name="status">';
    $cur = $it['status'] ?? 'published';
    foreach (['published' => 'منتشرشده (نمایش در سایت)', 'draft' => 'پیش‌نویس (پنهان)'] as $k => $v) echo '<option value="' . $k . '"' . ($cur === $k ? ' selected' : '') . '>' . $v . '</option>';
    echo '</select></label><label class="chk"><input type="checkbox" name="retranslate" value="1"> ترجمهٔ مجدد از فارسی (جایگزین متن‌های انگلیسی و روسی)</label><button class="btn primary wide">ذخیره</button>';
    if ($it) echo '<p class="muted small">ایجاد: ' . e(format_date((int)$it['created'])) . '</p>';
    echo '</div>';

    if ($type === 'person') {
        $img = !empty($it['image']) ? media_get((int)$it['image']) : null;
        echo '<div class="box"><h3>شمارهٔ تماس</h3><label><input name="x_phone" value="' . e($x['phone'] ?? '') . '" dir="ltr" placeholder="09171234567" inputmode="tel"></label><p class="muted small">در سایت به‌صورت پیوند تماس نمایش داده می‌شود. خالی بگذارید تا شماره نمایش داده نشود.</p></div>';
        if ($id) { $u = xp_person_usage($id); if ($u) echo '<div class="box"><h3>تجربه‌های این مجری</h3><p class="small">' . e(implode('، ', $u)) . '</p><p class="muted small">با پیش‌نویس‌کردن این مجری، او از همهٔ این تجربه‌ها پنهان می‌شود.</p></div>'; }
        echo '<div class="box"><h3>عکس مجری</h3><input type="hidden" name="image" value="' . (int)($it['image'] ?? 0) . '" data-image-input><div class="imgprev" data-image-prev>' . ($img ? '<img src="' . e(media_url($img, true)) . '" alt="">' : '<span class="muted">انتخاب نشده</span>') . '</div><button type="button" class="btn" data-media-pick>انتخاب / آپلود عکس</button> <button type="button" class="link danger" data-image-clear>حذف</button><p class="muted small">بهترین نتیجه: عکس مربعی از چهره؛ در سایت دایره‌ای بریده می‌شود.</p></div>';
    } else {
        $icons = xp_icons(); $curIcon = $x['icon'] ?? array_key_first($icons);
        echo '<div class="box"><h3>آیکن</h3>';
        if (!$icons) echo '<p class="alert err">فهرست آیکن‌ها (xp-icons.json) روی هاست نیست؛ سایت را دوباره دیپلوی کنید.</p>';
        echo '<div class="iconpick">';
        foreach ($icons as $k => $ic) echo '<label class="ip" title="' . e($ic['label']) . '"><input type="radio" name="icon" value="' . e($k) . '"' . ($curIcon === $k ? ' checked' : '') . '><span class="ipi">' . $ic['svg'] . '</span><small>' . e($ic['label']) . '</small></label>';
        echo '</div></div>';
        echo '<div class="box"><h3>مجری‌ها (هماهنگی با)</h3>';
        $people = xp_load('person', false); $sel = array_map('intval', (array)($x['who'] ?? []));
        uasort($people, fn($a, $b) => strcmp($a['names']['fa'] ?? '', $b['names']['fa'] ?? ''));
        if (!$people) echo '<p class="muted small">هنوز مجری‌ای ثبت نشده است.</p>';
        foreach ($people as $p) echo '<label class="chk"><input type="checkbox" name="who[]" value="' . $p['id'] . '"' . (in_array($p['id'], $sel, true) ? ' checked' : '') . '> ' . e($p['names']['fa'] ?? '') . ($p['x']['phone'] ?? '' ? ' <small class="muted" dir="ltr">' . e($p['x']['phone']) . '</small>' : '') . ($p['status'] !== 'published' ? ' <small class="muted">(پیش‌نویس)</small>' : '') . '</label>';
        echo '<p class="muted small">هیچ‌کس را تیک نزنید تا «آزاد؛ بدون نیاز به هماهنگی» نمایش داده شود. مجری جدید: <a href="/admin/?a=edit&type=person" target="_blank" rel="noopener">افزودن مجری ↗</a> (بعد صفحه را تازه کنید).</p></div>';
        echo '<div class="box"><h3>ترتیب نمایش</h3><label><input type="number" name="x_sort" value="' . (int)($x['sort'] ?? 100) . '" min="0" max="9999" dir="ltr"></label><p class="muted small">عدد کوچک‌تر، بالاتر. (فاصلهٔ ۱۰ بین موردها بگذارید تا بعداً جا برای افزودن باشد.)</p></div>';
    }
    echo '</div></div></form>';
    layout_end();
}

/** نام تجربه‌هایی که این مجری در آن‌ها انتخاب شده (برای جلوگیری از حذف) */
function xp_person_usage(int $personId): array {
    $o = [];
    foreach (xp_load('experience', false) as $e) if (in_array($personId, array_map('intval', (array)($e['x']['who'] ?? [])), true)) $o[] = $e['names']['fa'] ?? '';
    return $o;
}

/** دکمهٔ «وارد کردن تجربه‌ها و مجری‌های فعلی سایت» بالای فهرست‌ها */
function xp_seed_box(): void {
    if (!xp_seed_possible()) return;
    echo '<form method="post" action="/admin/?a=xp_seed" class="box" data-confirm="تجربه‌ها و مجری‌های فعلی سایت (۱۲ تجربه و ۱۰ مجری) وارد پنل شوند؟"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><h3>شروع با داده‌های فعلی سایت</h3><p class="muted small">هنوز چیزی در پنل نیست و سایت همان فهرست ثابت قبلی را نشان می‌دهد. با این دکمه همان ۱۲ تجربه و ۱۰ مجری (با نام فارسی/انگلیسی/روسی و شماره‌ها) وارد پنل می‌شوند تا بتوانید ویرایش، حذف یا اضافه کنید. ظاهر سایت تغییری نمی‌کند.</p><button class="btn primary">وارد کردن داده‌های فعلی</button></form>';
}

/** بخش تنظیمات ترجمه (داخل صفحهٔ تنظیمات) */
function xp_translate_settings_box(): void {
    echo '<form method="post" action="/admin/?a=tr_settings" class="box narrow"><input type="hidden" name="_csrf" value="' . e(csrf()) . '"><h3>ترجمهٔ خودکار (فارسی ← انگلیسی و روسی)</h3><label>سرویس ترجمه<select name="tr_provider">';
    foreach (TR_PROVIDERS as $k => $v) echo '<option value="' . $k . '"' . (tr_provider() === $k ? ' selected' : '') . '>' . e($v) . '</option>';
    echo '</select></label><label>ایمیل شما برای MyMemory (اختیاری؛ سقف روزانه را بالا می‌برد)<input name="tr_email" value="' . e(setting('tr_email')) . '" dir="ltr"></label><label>آدرس سرور LibreTranslate (فقط برای LibreTranslate)<input name="tr_url" value="' . e(setting('tr_url')) . '" dir="ltr" placeholder="https://translate.example.com"></label>';
    echo '<label>کلید API (Google یا LibreTranslate)<input name="tr_key" type="password" dir="ltr" autocomplete="new-password" placeholder="' . (setting('tr_key') !== '' ? 'ذخیره شده؛ برای تغییر، کلید جدید را وارد کنید' : '') . '"></label>';
    if (setting('tr_key') !== '') echo '<label class="chk"><input type="checkbox" name="tr_key_clear" value="1"> حذف کلید ذخیره‌شده</label>';
    echo '<button class="btn primary">ذخیرهٔ تنظیمات ترجمه</button> <button class="btn" formaction="/admin/?a=tr_test">آزمایش ترجمه</button><p class="muted small">هاست باید به اینترنت (HTTPS خروجی) دسترسی داشته باشد. اگر سرویس انتخابی از هاست شما قابل دسترس نباشد، «آزمایش ترجمه» پیغام خطا می‌دهد؛ در این حالت متن انگلیسی و روسی را دستی وارد کنید یا سرویس دیگری انتخاب کنید. نام آدم‌ها را بعد از ترجمهٔ خودکار حتماً بازبینی کنید.</p></form>';
}

/** ذخیرهٔ تنظیمات ترجمه */
function xp_save_tr_settings(): never {
    $prov = (string)($_POST['tr_provider'] ?? 'mymemory'); if (!isset(TR_PROVIDERS[$prov])) $prov = 'mymemory';
    $url = trim((string)($_POST['tr_url'] ?? ''));
    $mail = trim((string)($_POST['tr_email'] ?? ''));
    if ($url !== '' && (!preg_match('#^https?://[^\s]+$#i', $url) || !tr_host_public($url))) { $_SESSION['flash'] = 'آدرس سرور ترجمه معتبر نیست (با https:// شروع شود و باید یک سرور عمومی باشد، نه آدرس داخلی).'; redirect('/admin/?a=settings'); }
    if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) { $_SESSION['flash'] = 'ایمیل معتبر نیست.'; redirect('/admin/?a=settings'); }
    set_setting('tr_provider', $prov); set_setting('tr_url', mb_substr($url, 0, 300)); set_setting('tr_email', $mail);
    if (!empty($_POST['tr_key_clear'])) set_setting('tr_key', '');
    elseif (trim((string)($_POST['tr_key'] ?? '')) !== '') set_setting('tr_key', mb_substr(trim((string)$_POST['tr_key']), 0, 300));
    $_SESSION['flash'] = 'تنظیمات ترجمه ذخیره شد.';
    redirect('/admin/?a=settings');
}

/** آزمایش سرویس ترجمه با یک جملهٔ کوتاه */
function xp_test_translate(): never {
    $o = [];
    foreach (['en', 'ru'] as $l) { $r = cms_translate('قایق‌سواری ساحلی', $l); $o[] = strtoupper($l) . ': ' . ($r['ok'] ? '«' . $r['text'] . '»' : 'ناموفق — ' . $r['err']); }
    $_SESSION['flash'] = 'آزمایش ترجمه («قایق‌سواری ساحلی») — ' . implode(' | ', $o);
    redirect('/admin/?a=settings');
}
