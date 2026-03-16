<?php
// /includes/header.php
require_once __DIR__ . '/../core.php';

/* =========================================================
 * 1) 判定当前访问形态：HTML 模式 or PHP 模式
 *    现在你要求“全部默认输出 html 链接”，所以这里：
 *    - 访问 /xxx.html => html
 *    - 访问 php 并带 ?__static=1 => html（用于生成缓存时输出 html 链接）
 *    - 访问 php 不带参数：也默认 html（避免首页默认 index.php 导致菜单全是 php）
 * ========================================================= */
$reqUri  = $_SERVER['REQUEST_URI'] ?? '/';
$reqPath = parse_url($reqUri, PHP_URL_PATH) ?: '/';

$is_html_mode = true; // ✅ 默认全部走 html 链接输出（你当前的需求）

// 兼容：直接访问 /xxx.html 或生成缓存 ?__static=1，都属于 html 模式（这里仅做显式标记）
if (isset($_GET['__static']) && (string)$_GET['__static'] === '1') {
  $is_html_mode = true;
} elseif (substr($reqPath, -5) === '.html') {
  $is_html_mode = true;
}

/* =========================================================
 * 2) 链接生成器（关键修复）
 *    - 兼容老写法：link_page('index.php','/index.html')
 *    - 兼容新写法：link_page('/index.html')
 *    现在统一返回 html 链接
 * ========================================================= */
if (!function_exists('link_page')) {
  /**
   * 兼容两种写法：
   * 1) link_page('/index.html')
   * 2) link_page('index.php', '/index.html')  ← 老代码
   *
   * 统一返回 HTML 链接（静态优先策略）
   */
  function link_page(string $arg1, ?string $arg2 = null): string {
    return ($arg2 !== null) ? $arg2 : $arg1;
  }
}

if (!function_exists('link_products_list')) {
  // products_list 两种形式：
  // - 全部：/products_list/index.html
  // - 分类：/products_list/Barbed.html
  function link_products_list(?string $categorySlug): string {
    $categorySlug = trim((string)$categorySlug);
    if ($categorySlug === '') return '/products_list/index.html';
    return '/products_list/' . rawurlencode($categorySlug) . '.html';
  }
}

if (!function_exists('link_product_detail')) {
  // 产品详情：
  // - /products/<slug>.html
  function link_product_detail(string $slug): string {
    $slug = trim($slug);
    return '/products/' . rawurlencode($slug) . '.html';
  }
}

/* =========================================================
 * 3) 站点基础信息 + 默认 SEO
 * ========================================================= */
$site_name     = get_setting('company_name', 'Your Company');
$body_class    = $body_class ?? '';
$company_email = get_setting('company_email', 'info@example.com');
$company_phone = get_setting('company_phone', '+00 0000 0000');

// ===== 默认 SEO 设置（仅兜底用） =====
$default_seo_title       = get_setting('seo_default_title', $site_name);
$default_seo_description = get_setting('seo_default_description', '');
$default_seo_keywords    = get_setting('seo_default_keywords', '');

$page_title       = $page_title       ?? $default_seo_title;
$meta_description = $meta_description ?? $default_seo_description;
$meta_keywords    = $meta_keywords    ?? $default_seo_keywords;

/* =========================================================
 * 4) 产品分类 + 产品（导航下拉）
 * ========================================================= */
$product_categories = [];
$products_by_cat    = [];

try {
  // 读取分类
  $stmt = $pdo->prepare("
    SELECT id, name, slug
    FROM product_categories
    WHERE is_active = 1
    ORDER BY sort_order ASC, name ASC
  ");
  $stmt->execute();
  $product_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if ($product_categories) {
    // 读取所有启用产品
    $stmt2 = $pdo->prepare("
      SELECT id, name, slug, category_id
      FROM products
      WHERE is_active = 1
      ORDER BY category_id ASC, name ASC
    ");
    $stmt2->execute();
    $all_products = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // 按分类分组
    foreach ($all_products as $p) {
      $cid = (int)$p['category_id'];
      if (!isset($products_by_cat[$cid])) $products_by_cat[$cid] = [];
      $products_by_cat[$cid][] = $p;
    }
  }
} catch (Throwable $e) {
  $product_categories = [];
  $products_by_cat    = [];
}

/* =========================================================
 * 5) 社交链接
 * ========================================================= */
$linkX         = trim(get_setting('social_x', ''));
$linkFacebook  = trim(get_setting('social_facebook', ''));
$linkInstagram = trim(get_setting('social_instagram', ''));
$linkLinkedin  = trim(get_setting('social_linkedin', ''));

if (!function_exists('render_social_icon')) {
  function render_social_icon($url, $iconClass, $extraClass = '') {
    $url = trim((string)$url);
    if ($url === '') return '';
    return '<a href="' . htmlspecialchars($url) . '" class="' . htmlspecialchars($extraClass) . '" target="_blank" rel="noopener">'
         . '<i class="bi ' . htmlspecialchars($iconClass) . '"></i>'
         . '</a>';
  }
}

/* =========================================================
 * 6) 顶部菜单：全站默认输出 html 链接
 * ========================================================= */
$home_link    = link_page('index.php',   '/index.html');
$faq_link     = link_page('faq.php',     '/faq.html');
$about_link   = link_page('about.php',   '/about.html');
$contact_link = link_page('contact.php', '/contact.html');

// Products 主入口（全部产品）
$products_root_link = link_products_list('');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">

  <!-- Favicons -->
  <link href="/includes/assets/img/favicon.png" rel="icon">
  <link href="/includes/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="/includes/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/includes/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="/includes/assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="/includes/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="/includes/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main Template CSS -->
  <link href="/includes/assets/css/main.css" rel="stylesheet">
</head>

<body class="<?= htmlspecialchars(trim($body_class)) ?>">

  <!-- Header -->
  <header id="header" class="header sticky-top">

    <!-- Top bar -->
    <div class="topbar d-flex align-items-center dark-background">
      <div class="container d-flex justify-content-center justify-content-md-between">
        <div class="contact-info d-flex align-items-center">
          <i class="bi bi-envelope d-flex align-items-center">
            <a href="mailto:<?= htmlspecialchars($company_email) ?>"><?= htmlspecialchars($company_email) ?></a>
          </i>
          <i class="bi bi-phone d-flex align-items-center ms-4">
            <span><?= htmlspecialchars($company_phone) ?></span>
          </i>
        </div>

        <div class="social-links d-none d-md-flex align-items-center">
          <?= render_social_icon($linkX,         'bi-twitter-x',  'twitter') ?>
          <?= render_social_icon($linkFacebook,  'bi-facebook',   'facebook') ?>
          <?= render_social_icon($linkInstagram, 'bi-instagram',  'instagram') ?>
          <?= render_social_icon($linkLinkedin,  'bi-linkedin',   'linkedin') ?>
        </div>

      </div>
    </div><!-- End Top Bar -->

    <!-- Branding + Nav -->
    <div class="branding d-flex align-items-center">
      <div class="container position-relative d-flex align-items-center justify-content-between">

        <a href="<?= htmlspecialchars($home_link) ?>" class="logo d-flex align-items-center">
          <h1 class="sitename"><?= htmlspecialchars($site_name) ?></h1>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>

            <li>
              <a href="<?= htmlspecialchars($home_link) ?>" class="<?= ($body_class === 'index-page' ? 'active' : '') ?>">
                Home
              </a>
            </li>

            <!-- Products 下拉菜单：分类 + 分类下的产品 -->
            <li class="dropdown">
              <a href="<?= htmlspecialchars($products_root_link) ?>"
                 class="<?= (in_array($body_class, ['products-page','product-page']) ? 'active' : '') ?>">
                <span>Products</span>
                <i class="bi bi-chevron-down toggle-dropdown"></i>
              </a>

              <?php if (!empty($product_categories)): ?>
                <ul>
                  <li>
                    <a href="<?= htmlspecialchars(link_products_list('')) ?>">All Products</a>
                  </li>

                  <?php foreach ($product_categories as $cat): ?>
                    <?php
                      $cid       = (int)$cat['id'];
                      $cat_slug  = (string)($cat['slug'] ?? '');
                      $cat_name  = (string)($cat['name'] ?? '');
                      $cat_link  = link_products_list($cat_slug);
                      $cat_prods = $products_by_cat[$cid] ?? [];
                    ?>

                    <?php if (!empty($cat_prods)): ?>
                      <li class="dropdown">
                        <a href="<?= htmlspecialchars($cat_link) ?>">
                          <span><?= htmlspecialchars($cat_name) ?></span>
                          <i class="bi bi-chevron-right toggle-dropdown"></i>
                        </a>
                        <ul>
                          <?php foreach ($cat_prods as $p): ?>
                            <?php $p_link = link_product_detail((string)$p['slug']); ?>
                            <li>
                              <a href="<?= htmlspecialchars($p_link) ?>">
                                <?= htmlspecialchars($p['name']) ?>
                              </a>
                            </li>
                          <?php endforeach; ?>
                        </ul>
                      </li>
                    <?php else: ?>
                      <li>
                        <a href="<?= htmlspecialchars($cat_link) ?>">
                          <?= htmlspecialchars($cat_name) ?>
                        </a>
                      </li>
                    <?php endif; ?>

                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <ul>
                  <li><a href="<?= htmlspecialchars(link_products_list('')) ?>">All Products</a></li>
                </ul>
              <?php endif; ?>
            </li>

            <li>
              <a href="<?= htmlspecialchars($faq_link) ?>" class="<?= ($body_class === 'faq-page' ? 'active' : '') ?>">
                FAQ
              </a>
            </li>

            <li>
              <a href="<?= htmlspecialchars($about_link) ?>" class="<?= ($body_class === 'about-page' ? 'active' : '') ?>">
                About
              </a>
            </li>

            <li>
              <a href="<?= htmlspecialchars($contact_link) ?>" class="<?= ($body_class === 'contact-page' ? 'active' : '') ?>">
                Contact
              </a>
            </li>

          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

      </div>
    </div>

  </header><!-- End Header -->

  <main id="main">
