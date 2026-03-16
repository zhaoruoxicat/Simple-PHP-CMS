<?php
// /admin/admin_header.php
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php'; // 登录校验

$admin_page_title = $admin_page_title ?? '后台管理中心';

// 当前登录用户名
$current_admin = $_SESSION['username'] ?? '管理员';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($admin_page_title) ?> - 后台管理</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Tabler 本地 CSS -->
  <link href="/admin/tabler.min.css" rel="stylesheet">
</head>
<body class="layout-fluid">

<div class="page">

  <!-- 顶部导航栏 -->
  <header class="navbar navbar-expand-md navbar-light bg-white sticky-top border-bottom">
    <div class="container-xl">

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <a href="index.php" class="navbar-brand">
        <span class="navbar-brand-text">管理后台</span>
      </a>

      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar-nav flex-row flex-wrap ms-auto">

          <a href="index.php" class="nav-link px-3">
            <span class="nav-link-title">仪表盘</span>
          </a>
          
          <a href="cache_manage.php" class="nav-link px-3">
            <span class="nav-link-title">清理缓存</span>
          </a>          
          
          
          <a href="messages.php" class="nav-link px-3">
            <span class="nav-link-title">留言管理</span>
          </a>

          <a href="about_edit.php" class="nav-link px-3">
            <span class="nav-link-title">关于我们</span>
          </a>

          <a href="settings_basic.php" class="nav-link px-3">
            <span class="nav-link-title">基础设置</span>
          </a>

          <a href="social_links.php" class="nav-link px-3">
            <span class="nav-link-title">网络平台链接设置</span>
          </a>

          <a href="categories.php" class="nav-link px-3">
            <span class="nav-link-title">分类管理</span>
          </a>

          <a href="products.php" class="nav-link px-3">
            <span class="nav-link-title">产品管理</span>
          </a>

          <a href="faqs.php" class="nav-link px-3">
            <span class="nav-link-title">FAQ</span>
          </a>


          <!-- 用户菜单 -->
          <div class="nav-item dropdown px-3">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">

              <span class="nav-link-title"><?= htmlspecialchars($current_admin) ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item " href="change_password.php">修改密码</a>
              <a class="dropdown-item text-danger" href="logout.php">退出登录</a>
            </div>
          </div>

        </div>
      </div>

    </div>
  </header>

  <!-- 页面主体内容区 -->
  <div class="page-body">
    <div class="container-xl py-4">
