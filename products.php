<?php
declare(strict_types=1);

// /products.php（或 /product.php）— 产品详情入口 + 静态缓存
// 依赖：/includes/products.php 负责实际输出完整 HTML
// 缓存：/cache/products/<slug>.html

require_once __DIR__ . '/core.php';

// 只支持 GET 缓存（避免把表单/登录态等缓存进去）
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    require_once __DIR__ . '/includes/products.php';
    exit;
}

$slug = trim($_GET['slug'] ?? '');
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 没有 slug/id 就走动态（你原逻辑里会提示 not found）
if ($slug === '' && $id <= 0) {
    require_once __DIR__ . '/includes/products.php';
    exit;
}

// ----------------------------
// 1) 计算缓存文件路径
// ----------------------------
$cacheBase = __DIR__ . '/cache/products';
if (!is_dir($cacheBase)) {
    @mkdir($cacheBase, 0777, true);
}

// 优先用 slug 做缓存键（更稳定）
// 如果你确实允许 ?id= 访问，这里也给一个兜底
$cacheKey = $slug !== '' ? $slug : ('id-' . $id);

// 防目录穿越：只允许 [a-zA-Z0-9_-]
$cacheKeySafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $cacheKey);
$cacheFile    = $cacheBase . '/' . $cacheKeySafe . '.html';

// ----------------------------
// 2) 读取缓存（命中就直接输出）
// ----------------------------
// 可选：加一个 TTL（比如 1 小时）
// $ttl = 3600; // 秒
// $cacheFresh = is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttl);

$cacheFresh = is_file($cacheFile);

// 强制刷新：/products/xxx.html?refresh=1
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] !== '0';

if ($cacheFresh && !$forceRefresh) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($cacheFile);
    exit;
}

// ----------------------------
// 3) 走动态渲染并生成缓存
// ----------------------------
// 关键：如果你希望“生成的缓存里菜单是 .html 链接”，
// 需要让 header.php 能识别本次请求是静态模式。
// 最稳做法：统一加一个 __static=1 参数（你的 rewrite 已经这样做了）
// 这里再兜底补一下（避免直接访问 products.php 但想生成 html 版缓存）
if (!isset($_GET['__static'])) {
    // 仅当访问的是 .html 重写来的场景通常已经带了，这里是兜底
    // 你也可以改成：只有当 URL 以 .html 结尾时才置 1
    // $_GET['__static'] = '1';
}

// 输出缓冲捕获 HTML
ob_start();
require_once __DIR__ . '/includes/products.php';
$html = ob_get_clean();

// 只有输出像 HTML 才写缓存（避免写入报错信息/空内容）
if (is_string($html) && strlen(trim($html)) > 100 && stripos($html, '<html') !== false) {
    // 原子写入：避免并发下半截文件
    $tmp = $cacheFile . '.tmp';
    @file_put_contents($tmp, $html, LOCK_EX);
    @rename($tmp, $cacheFile);
}

header('Content-Type: text/html; charset=utf-8');
echo $html;
