<?php
// 根目录 contact.php —— Contact 页面入口（带缓存）

require_once __DIR__ . '/core.php';

$cacheDir  = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/contact.html';

/**
 * 1. POST 请求（页脚留言）必须走动态
 *    不读缓存、不写缓存
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    // 2. 如果已有缓存，直接输出缓存
    if (is_file($cacheFile)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($cacheFile);
        exit;
    }

    // 3. 无缓存：开始缓冲，稍后生成缓存
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    ob_start();
}

/* ====== 页面正常动态渲染 ====== */

$page_title = 'Contact Us';
$body_class = 'contact-page';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/contact.php';  // 内容模板
require __DIR__ . '/includes/footer.php';

/* ====== 写入缓存（仅 GET）====== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $html = ob_get_clean();

    // 写缓存失败不影响页面展示
    @file_put_contents($cacheFile, $html, LOCK_EX);

    echo $html;
}
