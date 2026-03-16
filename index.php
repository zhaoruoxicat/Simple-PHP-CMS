<?php
// 网站根目录：index.php
declare(strict_types=1);

require_once __DIR__ . '/core.php';

// ============================
//  静态缓存配置
// ============================
$cacheDir  = __DIR__ . '/cache';
$cacheFile = $cacheDir . '/index.html';

// 缓存有效期（秒）：按需调整。测试阶段可设 60~300
$cacheTtl = 300;

// 是否允许用缓存：只对 GET 生效，POST 一律动态（给页脚留言用）
$canUseCache = ($_SERVER['REQUEST_METHOD'] === 'GET');

// 允许手动强制刷新：/index.html?refresh=1
if ($canUseCache && isset($_GET['refresh']) && $_GET['refresh'] == '1') {
    $canUseCache = false;
}

// 命中缓存直接输出（不进 header/footer，不查库，最快）
if ($canUseCache && is_file($cacheFile)) {
    $age = time() - (int)@filemtime($cacheFile);
    if ($age >= 0 && $age <= $cacheTtl) {
        header('Content-Type: text/html; charset=utf-8');
        header('X-Cache: HIT');
        readfile($cacheFile);
        exit;
    }
}

// ============================
//  动态渲染（并在 GET 时生成缓存）
// ============================

// 从数据库取公司名，用作标题
$site_name  = get_setting('company_name', 'Your Company');
$page_title = $site_name . ' - Home';
$body_class = 'index-page';

// 动态输出开始
ob_start();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/index_content.php';
require_once __DIR__ . '/includes/footer.php';

$html = ob_get_clean();

// 只在 GET 且无错误输出时写缓存；POST 不写缓存（避免把提示缓存）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    // 写缓存用临时文件 + 原子替换，避免并发写坏文件
    $tmp = $cacheFile . '.tmp';
    if (@file_put_contents($tmp, $html, LOCK_EX) !== false) {
        @rename($tmp, $cacheFile);
    } else {
        @unlink($tmp);
    }

    header('X-Cache: MISS');
} else {
    // POST（页脚留言）建议明确不缓存
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Cache: BYPASS');
}

header('Content-Type: text/html; charset=utf-8');
echo $html;
