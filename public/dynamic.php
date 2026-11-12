<?php
require __DIR__ . '/cms-core/lib.php';
require __DIR__ . '/cms-core/overrides.php';
require __DIR__ . '/cms-core/render.php';
ov_start_buffer();   // متن‌های ویرایش‌شده در «متن‌های سایت» روی خروجی اعمال می‌شود
cms_render_request();
