<?php
/* «تلویزیون هلیله» بالای صفحهٔ گالری: لینک ویدیو (آپارات، یوتیوب، ویمیو، فایل مستقیم mp4/webm یا هر صفحه‌ای که پخش‌کنندهٔ جاسازی دارد)
   از پنل افزوده می‌شود و هر کدام یک «کانال» تلویزیون است؛ با کلیک روی کانال در خود صفحه پخش می‌شود و تمام‌صفحه هم می‌شود. */
declare(strict_types=1);

function tv_ready(): void {
    static $done = false;
    if ($done) return;
    db()->exec("CREATE TABLE IF NOT EXISTS tv(
        id INTEGER PRIMARY KEY, url TEXT NOT NULL UNIQUE, title TEXT NOT NULL DEFAULT '', thumb TEXT NOT NULL DEFAULT '',
        kind TEXT NOT NULL DEFAULT 'link', provider TEXT NOT NULL DEFAULT '', src TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 100, status TEXT NOT NULL DEFAULT 'published', created INTEGER NOT NULL)");
    $done = true;
}

/** تشخیص نوع لینک → [kind(embed|file|link), provider, src, thumb, title] یا رشتهٔ خطا */
function tv_resolve(string $url): array|string {
    $url = trim($url);
    if (!preg_match('#^https?://#i', $url) || !filter_var($url, FILTER_VALIDATE_URL)) return 'لینک معتبر نیست (باید با http یا https شروع شود).';
    $kind = 'link'; $prov = (string)preg_replace('/^www\./i', '', (string)parse_url($url, PHP_URL_HOST)); $src = ''; $thumb = ''; $title = '';
    if (preg_match('#(?:youtube\.com/(?:watch\?(?:[^\#]*&)?v=|embed/|shorts/|live/)|youtu\.be/)([\w-]{11})#i', $url, $m)) {
        $kind = 'embed'; $prov = 'youtube'; $src = 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0&playsinline=1'; $thumb = 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg';
    } elseif (preg_match('#vimeo\.com/(?:video/|channels/[\w-]+/|groups/[\w-]+/videos/)?(\d{6,})#i', $url, $m)) {
        $kind = 'embed'; $prov = 'vimeo'; $src = 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1';
    } elseif (preg_match('#aparat\.com/(?:v/|video/video/embed/videohash/)([A-Za-z0-9]+)#i', $url, $m)) {
        $kind = 'embed'; $prov = 'aparat'; $src = 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame?autoplay=true';
    } elseif (preg_match('#\.(mp4|webm|ogv|m4v)(?:\?|$)#i', (string)parse_url($url, PHP_URL_PATH))) {
        $kind = 'file'; $prov = 'file'; $src = $url;
    }
    // عنوان و تصویر از صفحه (Open Graph)؛ اگر نشد مهم نیست
    $page = ($kind === 'file') ? null : news_http_get($url);
    if (is_array($page)) {
        $html = $page[0]; $final = $page[1]; $meta = [];
        if (preg_match_all('/<meta\b[^>]*>/i', $html, $mm)) foreach ($mm[0] as $tag) {
            if (!preg_match('/\b(?:property|name)\s*=\s*["\']([^"\']+)["\']/i', $tag, $k)) continue;
            if (!preg_match('/\bcontent\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/is', $tag, $v)) continue;
            $key = strtolower(trim($k[1])); $val = $v[1] !== '' ? $v[1] : ($v[2] ?? '');
            if (!isset($meta[$key]) && trim($val) !== '') $meta[$key] = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        $htmlTitle = preg_match('#<title[^>]*>(.*?)</title>#is', $html, $tm) ? $tm[1] : '';
        $title = news_clean((string)($meta['og:title'] ?? $meta['twitter:title'] ?? $htmlTitle), 160);
        if ($thumb === '') $thumb = news_abs((string)($meta['og:image:secure_url'] ?? $meta['og:image'] ?? $meta['twitter:image'] ?? ''), $final);
        if ($kind === 'link') {   // صفحهٔ دیگر: اگر خودش پخش‌کنندهٔ جاسازی یا فایل ویدیو معرفی کرده باشد
            foreach (['og:video:secure_url', 'og:video:url', 'og:video', 'twitter:player', 'twitter:player:stream'] as $k) {
                if (empty($meta[$k])) continue;
                $v = news_abs($meta[$k], $final);
                if (!preg_match('#^https://#i', $v)) continue;
                if (preg_match('#\.(mp4|webm|ogv|m4v)(?:\?|$)#i', (string)parse_url($v, PHP_URL_PATH))) { $kind = 'file'; $src = $v; break; }
                if (in_array($k, ['og:video:secure_url', 'og:video:url', 'og:video', 'twitter:player'], true) && !preg_match('#\.(swf|flv)(?:\?|$)#i', $v)) { $kind = 'embed'; $src = $v; break; }
            }
        }
    }
    if ($title === '' && $kind === 'file') $title = urldecode(pathinfo((string)parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME));
    if ($title === '') $title = $prov;
    return ['kind' => $kind, 'provider' => $prov, 'src' => $src, 'thumb' => $thumb, 'title' => $title];
}

function tv_add_url(string $raw): array {
    tv_ready(); $pdo = db();
    $url = news_norm_url($raw);
    $ex = $pdo->prepare('SELECT id FROM tv WHERE url=?'); $ex->execute([$url]);
    if ($ex->fetchColumn()) return [0, 'این ویدیو از قبل هست: ' . mb_substr($url, 0, 60)];
    $r = tv_resolve($url);
    if (is_string($r)) return [0, $r];
    $next = (int)$pdo->query('SELECT COALESCE(MAX(sort),0)+10 FROM tv')->fetchColumn();
    $pdo->prepare('INSERT INTO tv(url,title,thumb,kind,provider,src,sort,status,created) VALUES(?,?,?,?,?,?,?,?,?)')
        ->execute([$url, $r['title'], $r['thumb'], $r['kind'], $r['provider'], $r['src'], $next, 'published', time()]);
    $note = $r['kind'] === 'link' ? ' (پخش‌کنندهٔ جاسازی پیدا نشد؛ در تلویزیون دکمهٔ «پخش در سایت منبع» نمایش داده می‌شود)' : '';
    return [(int)$pdo->lastInsertId(), 'کانال تازه: ' . mb_substr($r['title'], 0, 60) . $note];
}

const TV_UI = [
    'fa' => ['name' => 'تلویزیون هلیله', 'idle' => 'یک کانال را انتخاب کنید', 'prev' => 'کانال قبلی', 'next' => 'کانال بعدی', 'fs' => 'تمام‌صفحه', 'off' => 'خاموش', 'exit' => 'خروج از تمام‌صفحه', 'open' => 'پخش در سایت منبع', 'ch' => 'کانال', 'hint' => 'روی هر کانال بزنید تا پخش شود'],
    'en' => ['name' => 'Helileh TV', 'idle' => 'Pick a channel', 'prev' => 'Previous channel', 'next' => 'Next channel', 'fs' => 'Full screen', 'off' => 'Power off', 'exit' => 'Exit full screen', 'open' => 'Watch on the original site', 'ch' => 'Channel', 'hint' => 'Tap a channel to play it'],
    'ru' => ['name' => 'Телевизор Хелиле', 'idle' => 'Выберите канал', 'prev' => 'Предыдущий канал', 'next' => 'Следующий канал', 'fs' => 'На весь экран', 'off' => 'Выключить', 'exit' => 'Выйти из полноэкранного режима', 'open' => 'Смотреть на сайте источника', 'ch' => 'Канал', 'hint' => 'Нажмите на канал, чтобы смотреть'],
];

/** بلوک تلویزیون؛ اگر ویدیویی ثبت نشده باشد رشتهٔ خالی (صفحه همان گالری قبلی می‌ماند) */
function tv_block(string $lang): string {
    try {
        tv_ready();
        $rows = db()->query("SELECT * FROM tv WHERE status='published' ORDER BY sort, id LIMIT 60")->fetchAll();
    } catch (Throwable $ex) { return ''; }
    if (!$rows) return '';
    $u = TV_UI[$lang] ?? TV_UI['fa'];
    $ch = '';
    foreach ($rows as $i => $r) {
        $n = news_num($i + 1, $lang);
        $th = $r['thumb'] !== '' ? '<img src="' . e(preg_replace('#^http://#i', 'https://', $r['thumb'])) . '" alt="" loading="lazy" referrerpolicy="no-referrer">' : '';
        $ch .= '<li><button type="button" class="tvx-ch" data-kind="' . e($r['kind']) . '" data-src="' . e($r['kind'] === 'link' ? $r['url'] : $r['src']) . '" data-title="' . e($r['title']) . '" data-sound="tick" aria-label="' . e($u['ch'] . ' ' . $n . ': ' . $r['title']) . '">'
            . '<span class="tvx-th">' . $th . '</span><b class="tvx-no">' . e($n) . '</b><i class="tvx-live" aria-hidden="true"></i><span class="tvx-tt">' . e($r['title']) . '</span></button></li>';
    }
    $ico = [
        'prev' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-8 7 8 7z"/></svg>',
        'next' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l8 7-8 7z"/></svg>',
        'fs' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'off' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v8M6.7 6.8a8 8 0 1 0 10.6 0" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>',
    ];
    $knob = fn(string $k) => '<button type="button" class="tvx-knob" data-act="' . $k . '" data-sound="tick" aria-label="' . e($u[$k]) . '" title="' . e($u[$k]) . '">' . $ico[$k] . '<span>' . e($u[$k]) . '</span></button>';
    $jug = '<svg class="tvx-jug" viewBox="0 0 80 120" aria-hidden="true"><path d="M30 10h20v8c0 4-2 6-4 8 14 6 24 20 24 40 0 26-16 44-30 44S10 92 10 66c0-20 10-34 24-40-2-2-4-4-4-8z" fill="#a4592b"/><path d="M30 10h20M16 56h48M14 70h52M16 84h48" stroke="#5a2b12" stroke-width="2.4" fill="none"/><path d="M26 40c-6 10-8 22-6 36" stroke="#d9925a" stroke-width="3" fill="none" stroke-linecap="round" opacity=".6"/><path d="M30 10c-8 4-14 14-12 28M50 10c8 4 14 14 12 28" stroke="#5a2b12" stroke-width="3" fill="none"/></svg>';
    $dhow = '<svg class="tvx-dhow" viewBox="0 0 140 110" aria-hidden="true"><path d="M8 80h124c-6 14-18 22-34 22H34C22 102 12 94 8 80z" fill="#6a3c1e"/><path d="M8 80h124" stroke="#c9a24a" stroke-width="3"/><path d="M70 78V8" stroke="#3b2414" stroke-width="3"/><path d="M72 12c26 10 42 34 46 62H72z" fill="#efe3c2"/><path d="M68 22C46 34 32 52 28 74h40z" fill="#e3d2a6"/><path d="M72 30c12 6 22 18 26 36M72 48c8 4 14 12 18 22" stroke="#b79b5c" stroke-width="1.6" fill="none"/></svg>';
    return '<section class="tvx" id="tvx" data-open="' . e($u['open']) . '" aria-label="' . e($u['name']) . '">'
        . '<div class="tvx-room"><div class="tvx-set">'
        . '<div class="tvx-ant" aria-hidden="true"><i></i><i></i><b></b></div>'
        . '<div class="tvx-cab"><div class="tvx-bezel"><div class="tvx-screen" id="tvx-screen"><div class="tvx-stage"></div>'
        . '<div class="tvx-idle"><span>' . e($u['idle']) . '</span></div><div class="tvx-static" aria-hidden="true"></div><div class="tvx-glass" aria-hidden="true"></div>'
        . '<button type="button" class="tvx-exit" aria-label="' . e($u['exit']) . '">✕</button></div></div>'
        . '<div class="tvx-side"><div class="tvx-grille" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i></div><div class="tvx-knobs">' . $knob('prev') . $knob('next') . $knob('fs') . $knob('off') . '</div><p class="tvx-brand">' . e($u['name']) . '</p></div></div>'
        . '<div class="tvx-plate"><span class="tvx-cap" aria-live="polite">' . e($u['hint']) . '</span></div></div>'
        . $jug . $dhow . '</div>'
        . '<div class="tvx-console"><div class="tvx-mosaic" aria-hidden="true"></div><ul class="tvx-chs">' . $ch . '</ul><div class="tvx-feet" aria-hidden="true"><i></i><i></i></div></div>'
        . '<script>' . tv_js() . '</script></section>';
}

function tv_js(): string {
    return <<<'JS'
(function(){var root=document.getElementById("tvx");if(!root)return;
var scr=root.querySelector(".tvx-screen"),stage=root.querySelector(".tvx-stage"),cap=root.querySelector(".tvx-cap"),chs=[].slice.call(root.querySelectorAll(".tvx-ch")),cur=-1,idle=cap.textContent,openTxt=root.getAttribute("data-open")||"";
chs.forEach(function(c){var im=c.querySelector("img");if(im)im.addEventListener("error",function(){im.remove()})});
function stop(){stage.innerHTML="";scr.classList.remove("on")}
function mark(){chs.forEach(function(c,i){c.classList.toggle("is-on",i===cur);c.setAttribute("aria-pressed",i===cur?"true":"false")})}
function play(i){if(i<0||i>=chs.length)return;cur=i;var c=chs[i],k=c.dataset.kind,s=c.dataset.src,t=c.dataset.title,el;
mark();stop();scr.classList.add("tune");setTimeout(function(){scr.classList.remove("tune")},520);
if(k==="embed"){el=document.createElement("iframe");el.src=s;el.title=t;el.setAttribute("allow","autoplay; fullscreen; picture-in-picture; encrypted-media");el.setAttribute("allowfullscreen","");el.setAttribute("referrerpolicy","strict-origin-when-cross-origin");el.setAttribute("sandbox","allow-scripts allow-same-origin allow-presentation allow-popups");}
else if(k==="file"){el=document.createElement("video");el.src=s;el.controls=true;el.autoplay=true;el.playsInline=true;el.setAttribute("playsinline","");}
else{el=document.createElement("div");el.className="tvx-link";var a=document.createElement("a");a.href=s;a.target="_blank";a.rel="noopener";a.textContent=root.dataset.open;el.appendChild(a);}
stage.appendChild(el);scr.classList.add("on");cap.textContent=t;
if(window.matchMedia&&matchMedia("(max-width:720px)").matches)scr.scrollIntoView({block:"center",behavior:"smooth"})}
function off(){cur=-1;mark();stop();cap.textContent=idle;scr.classList.remove("fake")}
function fs(){var d=document;if(d.fullscreenElement||d.webkitFullscreenElement){(d.exitFullscreen||d.webkitExitFullscreen).call(d);return}
if(scr.classList.contains("fake")){scr.classList.remove("fake");return}
var f=scr.requestFullscreen||scr.webkitRequestFullscreen;
if(f){try{var r=f.call(scr);if(r&&r.catch)r.catch(function(){scr.classList.add("fake")})}catch(e){scr.classList.add("fake")}}
else{var v=stage.querySelector("video");if(v&&v.webkitEnterFullscreen)v.webkitEnterFullscreen();else scr.classList.add("fake")}}
chs.forEach(function(c,i){c.addEventListener("click",function(){play(i)})});
root.querySelectorAll(".tvx-knob").forEach(function(b){b.addEventListener("click",function(){var a=b.dataset.act;
if(a==="next")play(cur<0?0:(cur+1)%chs.length);else if(a==="prev")play(cur<0?chs.length-1:(cur-1+chs.length)%chs.length);else if(a==="fs")fs();else off()})});
root.querySelector(".tvx-exit").addEventListener("click",fs);
document.addEventListener("keydown",function(e){if(e.key==="Escape")scr.classList.remove("fake")});
})();
JS;
}

function tv_css(): string {
    return <<<'CSS'
<style>
.tvx{--wd:#4b2c19;--wd2:#6e4427;--wd3:#2f1a0e;--br:#c9a24a;--br2:#f0d98a;--tl:#134e5a;--cr:#efe3c2;--gl:#0c1412;max-width:980px;margin:18px auto 40px;padding:0 16px;position:relative;font-family:inherit}
.tvx *{box-sizing:border-box}
.tvx-room{position:relative;padding:34px 70px 0}
.tvx-set{position:relative;max-width:820px;margin:0 auto}
.tvx-ant{position:absolute;left:50%;top:-38px;width:0;height:40px;z-index:0}
.tvx-ant i{position:absolute;bottom:0;width:3px;height:92px;background:linear-gradient(#e6e6e6,#8a8a8a);border-radius:2px;transform-origin:bottom center}
.tvx-ant i:nth-child(1){transform:rotate(-28deg)}.tvx-ant i:nth-child(2){transform:rotate(26deg)}
.tvx-ant i::after{content:"";position:absolute;top:-4px;left:-2px;width:7px;height:7px;border-radius:50%;background:var(--br2)}
.tvx-ant b{position:absolute;bottom:-6px;left:-18px;width:36px;height:14px;border-radius:50% 50% 0 0;background:var(--wd3)}
.tvx-cab{position:relative;z-index:1;display:grid;grid-template-columns:1fr 148px;gap:16px;padding:20px;border-radius:26px;background:linear-gradient(145deg,var(--wd2),var(--wd) 55%,var(--wd3));box-shadow:0 0 0 3px var(--wd3),0 0 0 5px var(--br),0 22px 38px rgba(30,14,4,.45),inset 0 2px 0 rgba(255,220,160,.25)}
.tvx-cab::before{content:"";position:absolute;inset:7px;border-radius:20px;border:1.5px solid rgba(201,162,74,.55);pointer-events:none}
.tvx-bezel{padding:14px;border-radius:30px / 26px;background:#17100b;box-shadow:inset 0 0 0 2px #000,inset 0 6px 14px rgba(0,0,0,.7),0 1px 0 rgba(255,220,160,.2)}
.tvx-screen{position:relative;aspect-ratio:4/3;border-radius:6% / 8%;overflow:hidden;background:var(--gl);isolation:isolate;box-shadow:0 0 22px rgba(120,255,230,.12)}
.tvx-stage,.tvx-stage iframe,.tvx-stage video{position:absolute;inset:0;width:100%;height:100%;border:0;background:#000;display:block}
.tvx-stage video{object-fit:contain}
.tvx-link{display:flex;align-items:center;justify-content:center;background:#000}
.tvx-link a{color:#111;background:var(--br2);padding:12px 20px;border-radius:999px;font-weight:700;text-decoration:none}
.tvx-idle{position:absolute;inset:0;display:flex;align-items:flex-end;justify-content:center;padding-bottom:9%;background:linear-gradient(90deg,#c4c4c4 0 14.28%,#c4c400 0 28.56%,#00c4c4 0 42.84%,#00c400 0 57.12%,#c400c4 0 71.4%,#c40000 0 85.68%,#0000c4 0) top/100% 72%,linear-gradient(90deg,#0000c4 0 14.28%,#111 0 28.56%,#c400c4 0 42.84%,#111 0 57.12%,#00c4c4 0 71.4%,#111 0 85.68%,#c4c4c4 0) 0 78%/100% 8% no-repeat,linear-gradient(90deg,#00214c 0 25%,#fff 0 50%,#32006a 0 75%,#111 0) bottom/100% 20% no-repeat,#111;background-repeat:no-repeat;filter:saturate(.8) brightness(.85)}
.tvx-idle span{background:rgba(0,0,0,.72);color:#f5e8c0;padding:8px 18px;border-radius:999px;font-weight:700;font-size:clamp(.85rem,2.2vw,1.1rem);letter-spacing:.02em}
.tvx-screen.on .tvx-idle{display:none}
.tvx-static{position:absolute;inset:0;pointer-events:none;opacity:.16;mix-blend-mode:screen;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");animation:tvx-n .5s steps(5) infinite}
.tvx-screen.on .tvx-static{opacity:0;animation:none}
.tvx-screen.tune .tvx-static{opacity:1;animation:tvx-n .12s steps(4) infinite;z-index:3}
@keyframes tvx-n{0%{background-position:0 0}25%{background-position:-60px 40px}50%{background-position:40px -70px}75%{background-position:-30px -20px}100%{background-position:70px 50px}}
.tvx-glass{position:absolute;inset:0;pointer-events:none;z-index:4;border-radius:inherit;background:repeating-linear-gradient(0deg,rgba(0,0,0,.14) 0 1px,transparent 1px 3px),radial-gradient(120% 90% at 30% 10%,rgba(255,255,255,.16),transparent 45%),radial-gradient(circle at 50% 50%,transparent 62%,rgba(0,0,0,.5));box-shadow:inset 0 0 40px rgba(0,0,0,.65)}
.tvx-screen.on .tvx-glass{background:radial-gradient(120% 90% at 30% 8%,rgba(255,255,255,.1),transparent 40%),radial-gradient(circle at 50% 50%,transparent 70%,rgba(0,0,0,.35))}
.tvx-exit{display:none;position:absolute;top:12px;inset-inline-end:12px;z-index:6;width:42px;height:42px;border-radius:50%;border:0;background:rgba(0,0,0,.6);color:#fff;font-size:20px;cursor:pointer}
.tvx-screen.fake{position:fixed;inset:0;z-index:2147483000;aspect-ratio:auto;border-radius:0;width:100vw;height:100vh}
.tvx-screen.fake .tvx-exit{display:block}
.tvx-screen:fullscreen{aspect-ratio:auto;border-radius:0;width:100vw;height:100vh}
.tvx-screen:fullscreen .tvx-exit{display:block;opacity:.55}
.tvx-screen:-webkit-full-screen{aspect-ratio:auto;border-radius:0;width:100vw;height:100vh}
.tvx-screen:-webkit-full-screen .tvx-exit{display:block;opacity:.55}
.tvx-screen:fullscreen .tvx-glass,.tvx-screen.fake .tvx-glass{display:none}
.tvx-side{display:flex;flex-direction:column;gap:14px;align-items:center;padding:12px 4px;border-radius:18px;background:linear-gradient(180deg,#3a2211,#26150a);box-shadow:inset 0 0 0 1.5px rgba(201,162,74,.5),inset 0 4px 10px rgba(0,0,0,.5)}
.tvx-grille{display:flex;flex-direction:column;gap:5px;width:72%;margin-top:4px}
.tvx-grille i{display:block;height:5px;border-radius:3px;background:#0e0804;box-shadow:0 1px 0 rgba(201,162,74,.45)}
.tvx-knobs{display:grid;grid-template-columns:1fr 1fr;gap:10px 8px;width:100%;padding:0 8px}
.tvx-knob{display:flex;flex-direction:column;align-items:center;gap:3px;background:none;border:0;padding:0;cursor:pointer;color:var(--cr);font:inherit}
.tvx-knob svg{width:44px;height:44px;padding:11px;border-radius:50%;fill:#3a2308;color:#3a2308;background:radial-gradient(circle at 35% 30%,var(--br2),var(--br) 55%,#8a6a22);box-shadow:0 3px 0 #5b4414,0 5px 8px rgba(0,0,0,.5),inset 0 -2px 3px rgba(0,0,0,.25);transition:transform .12s,box-shadow .12s}
.tvx-knob:hover svg{filter:brightness(1.1)}
.tvx-knob:active svg{transform:translateY(3px) rotate(14deg);box-shadow:0 0 0 #5b4414,0 2px 4px rgba(0,0,0,.5),inset 0 -2px 3px rgba(0,0,0,.25)}
.tvx-knob span{font-size:.66rem;line-height:1.2;text-align:center;opacity:.9}
.tvx-brand{margin:auto 0 4px;font-family:'Lalezar','Vazirmatn Variable',sans-serif;color:var(--br2);font-size:1.05rem;text-align:center;text-shadow:0 1px 0 #000}
.tvx-plate{position:relative;z-index:2;display:flex;justify-content:center;margin-top:-12px}
.tvx-plate span{max-width:86%;background:linear-gradient(180deg,var(--br2),var(--br));color:#2b1b06;padding:5px 22px;border-radius:0 0 12px 12px;font-weight:700;font-size:.92rem;text-align:center;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;box-shadow:0 4px 8px rgba(0,0,0,.35),inset 0 -2px 0 rgba(0,0,0,.18)}
.tvx-jug{position:absolute;inset-inline-start:0;bottom:-2px;width:74px;height:auto;filter:drop-shadow(0 6px 6px rgba(0,0,0,.35))}
.tvx-dhow{position:absolute;inset-inline-end:-6px;bottom:-4px;width:128px;height:auto;filter:drop-shadow(0 6px 6px rgba(0,0,0,.3))}
.tvx-console{position:relative;z-index:2;max-width:900px;margin:0 auto;border-radius:12px 12px 6px 6px;background:linear-gradient(180deg,var(--wd2),var(--wd) 40%,var(--wd3));box-shadow:0 0 0 2px var(--wd3),0 0 0 4px var(--br),0 18px 30px rgba(30,14,4,.4);padding:0 0 20px}
.tvx-mosaic{height:26px;border-radius:10px 10px 0 0;border-bottom:3px solid var(--br);background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='26' height='26'%3E%3Crect width='26' height='26' fill='%23134e5a'/%3E%3Cg fill='none' stroke='%23c9a24a' stroke-width='1.3'%3E%3Crect x='6' y='6' width='14' height='14'/%3E%3Crect x='6' y='6' width='14' height='14' transform='rotate(45 13 13)'/%3E%3C/g%3E%3Ccircle cx='13' cy='13' r='2.2' fill='%23e8c76a'/%3E%3C/svg%3E")}
.tvx-chs{list-style:none;margin:18px 0 0;padding:0 22px;display:flex;flex-wrap:wrap;justify-content:center;gap:16px 14px}
.tvx-chs li{width:calc((100% - 14px*5)/6);min-width:104px}
.tvx-ch{position:relative;display:block;width:100%;aspect-ratio:3/4;padding:0;cursor:pointer;border:0;color:var(--cr);font:inherit;border-radius:50% 50% 8px 8px / 34% 34% 8px 8px;background:#120a05;overflow:hidden;box-shadow:0 0 0 3px var(--br),0 0 0 5px var(--wd3),inset 0 6px 12px rgba(0,0,0,.7);transition:transform .15s,box-shadow .15s}
.tvx-ch:hover{transform:translateY(-3px)}
.tvx-ch:focus-visible{outline:3px solid #fff;outline-offset:4px}
.tvx-th{position:absolute;inset:0;background:radial-gradient(circle at 50% 30%,rgba(201,162,74,.25),transparent 60%),repeating-linear-gradient(45deg,#1d1209 0 6px,#26170b 6px 12px)}
.tvx-th img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;filter:sepia(.35) saturate(.9) brightness(.85);transition:filter .2s}
.tvx-ch:hover .tvx-th img,.tvx-ch.is-on .tvx-th img{filter:none}
.tvx-no{position:absolute;top:10%;left:50%;transform:translateX(-50%);min-width:30px;height:30px;padding:0 8px;display:flex;align-items:center;justify-content:center;border-radius:15px;background:linear-gradient(180deg,var(--br2),var(--br));color:#2b1b06;font-size:.95rem;box-shadow:0 2px 4px rgba(0,0,0,.5)}
.tvx-tt{position:absolute;inset-inline:0;bottom:0;padding:18px 8px 8px;font-size:.74rem;line-height:1.35;text-align:center;background:linear-gradient(transparent,rgba(0,0,0,.85));display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.tvx-live{position:absolute;top:12px;inset-inline-end:14px;width:10px;height:10px;border-radius:50%;background:#e23b2e;box-shadow:0 0 8px #ff6a58;opacity:0}
.tvx-ch.is-on{box-shadow:0 0 0 3px var(--br2),0 0 0 6px var(--wd3),0 0 22px rgba(240,217,138,.7),inset 0 6px 12px rgba(0,0,0,.5)}
.tvx-ch.is-on .tvx-live{opacity:1;animation:tvx-b 1.2s ease-in-out infinite}
@keyframes tvx-b{50%{opacity:.25}}
.tvx-feet{position:absolute;left:0;right:0;bottom:-30px;display:flex;justify-content:space-between;padding:0 6%}
.tvx-feet i{display:block;width:34px;height:30px;background:linear-gradient(90deg,var(--wd3),var(--wd2),var(--wd3));clip-path:polygon(0 0,100% 0,82% 100%,18% 100%);box-shadow:0 0 0 2px var(--br)}
.tvx{margin-bottom:62px}
@media(max-width:860px){.tvx-room{padding:34px 0 0}.tvx-jug,.tvx-dhow{display:none}}
@media(max-width:720px){.tvx-cab{grid-template-columns:1fr;padding:14px;gap:12px}.tvx-side{flex-direction:row;flex-wrap:wrap;justify-content:center;padding:10px}.tvx-grille,.tvx-brand{display:none}.tvx-knobs{grid-template-columns:repeat(4,1fr);max-width:420px}.tvx-chs li{width:calc((100% - 14px*2)/3);min-width:0}.tvx-chs{padding:0 14px}.tvx-bezel{padding:9px}}
@media(prefers-reduced-motion:reduce){.tvx-static,.tvx-ch.is-on .tvx-live{animation:none}.tvx-ch,.tvx-knob svg{transition:none}}
</style>
CSS;
}
