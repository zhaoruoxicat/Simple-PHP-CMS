<?php
// /about.php —— 前台 About 入口（带 /cache 静态缓存优先）

require_once __DIR__ . '/core.php';

$cacheDir  = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/about.html';

// 1) 如果是 POST（例如页脚留言），一定走动态，不读写缓存
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // 2) 缓存存在则直接输出缓存（最快）
    if (is_file($cacheFile)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($cacheFile);
        exit;
    }

    // 3) 缓存不存在：本次走动态，同时生成缓存文件
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    ob_start();
}

// ====== 页面正常动态渲染 ======
$page_title = 'About Us';
$body_class = 'about-page';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/about.php';
require __DIR__ . '/includes/footer.php';

// ====== 写入缓存（仅 GET / 非 POST）======
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $html = ob_get_clean();

    // 尽量写入缓存（失败也不影响正常展示）
    @file_put_contents($cacheFile, $html, LOCK_EX);

    echo $html;
}
