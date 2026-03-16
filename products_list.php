<?php
// /products.php （根目录）

$slug = trim($_GET['slug'] ?? '');

// 有 slug：显示单个产品详情（你之前已经写好的 includes/products.php）
if ($slug !== '') {
    require __DIR__ . '/includes/products.php';
    exit;
}

// 没有 slug：显示产品列表（支持 ?category=）
require __DIR__ . '/includes/products_list.php';
