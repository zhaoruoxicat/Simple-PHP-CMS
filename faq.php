<?php
// /faq.php  — FAQ 页面入口（支持 /cache/faq.html 静态缓存优先）

declare(strict_types=1);

$cacheDir  = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/faq.html';

// 仅 GET/HEAD 才允许走缓存
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (in_array($method, ['GET', 'HEAD'], true)) {
    if (is_file($cacheFile)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($cacheFile);
        exit;
    }
}

// ---- 走动态渲染并生成缓存 ----
require_once __DIR__ . '/core.php';

$page_title = "FAQ - Frequently Asked Questions";
$body_class = "faq-page";

// 动态输出缓冲：用于生成缓存文件
ob_start();

// 你的站点结构：根文件负责 header/footer，includes/faq.php 只输出主体内容
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/faq.php';
require_once __DIR__ . '/includes/footer.php';

$html = (string)ob_get_clean();

// 尝试写入缓存（失败也不影响本次输出）
if (in_array($method, ['GET', 'HEAD'], true)) {
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
    // 原子写入：先写临时文件再 rename
    $tmp = $cacheFile . '.tmp';
    if (@file_put_contents($tmp, $html) !== false) {
        @rename($tmp, $cacheFile);
    } else {
        @unlink($tmp);
    }
}

header('Content-Type: text/html; charset=utf-8');
echo $html;
