<?php
require __DIR__ . '/cms-core/lib.php';
require __DIR__ . '/cms-core/overrides.php';
require __DIR__ . '/cms-core/render.php';
if (isset($_GET['hc'])) { require __DIR__ . '/cms-core/stats.php'; stats_endpoint(); exit; }   // دریافت آمار بازدید
if (isset($_GET['ovp'])) { ov_serve((string)$_GET['ovp']); exit; }   // صفحه‌های استاتیک با متن‌های ویرایش‌شده
ov_start_buffer();   // متن‌های ویرایش‌شده در «متن‌های سایت» روی خروجی اعمال می‌شود
cms_render_request();
