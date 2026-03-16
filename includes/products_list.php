<?php
// /includes/products_list.php
require_once __DIR__ . '/../core.php';

/* =========================================================
 * 1) 判定当前访问形态：HTML 模式 or PHP 模式
 *    - 访问 /products_list/index.html 或 /products_list/Barbed.html => HTML 模式
 *    - 生成缓存时走 PHP 入口但带 ?__static=1 => 也视为 HTML 模式
 * ========================================================= */
$reqUri  = $_SERVER['REQUEST_URI'] ?? '/';
$reqPath = parse_url($reqUri, PHP_URL_PATH) ?: '/';

$is_html_mode = false;
if (isset($_GET['__static']) && (string)$_GET['__static'] === '1') {
    $is_html_mode = true;
} else {
    $is_html_mode = (substr($reqPath, -5) === '.html');
}

/* =========================================================
 * 2) 链接生成器：根据模式自动生成链接
 * ========================================================= */
if (!function_exists('link_page')) {
    function link_page(string $phpFile, string $htmlPath): string {
        global $is_html_mode;
        return $is_html_mode ? $htmlPath : $phpFile;
    }
}

if (!function_exists('link_products_list')) {
    // - 全部：/products_list/index.html  或 products_list.php
    // - 分类：/products_list/Barbed.html 或 products_list.php?category=Barbed
    function link_products_list(?string $categorySlug): string {
        global $is_html_mode;
        $categorySlug = trim((string)$categorySlug);

        if ($is_html_mode) {
            if ($categorySlug === '') return '/products_list/index.html';
            return '/products_list/' . rawurlencode($categorySlug) . '.html';
        }

        if ($categorySlug === '') return 'products_list.php';
        return 'products_list.php?category=' . urlencode($categorySlug);
    }
}

if (!function_exists('link_product_detail')) {
    // 产品详情：
    // - HTML 模式：/products/<slug>.html
    // - PHP 模式： products.php?slug=<slug>
    function link_product_detail(string $slug): string {
        global $is_html_mode;
        $slug = trim($slug);

        if ($is_html_mode) {
            return '/products/' . rawurlencode($slug) . '.html';
        }
        return 'products.php?slug=' . urlencode($slug);
    }
}

/* =========================================================
 * 3) 解析分类参数：PHP 模式用 ?category=xxx
 *    HTML 模式通常由重写规则注入到 $_GET['category']
 * ========================================================= */
$categoryParam = trim($_GET['category'] ?? '');
$category      = null;

// 先查 slug，再兼容纯数字 id
if ($categoryParam !== '') {
    // 按 slug 查
    $stmt = $pdo->prepare("
        SELECT id, name, slug
        FROM product_categories
        WHERE slug = :slug AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([':slug' => $categoryParam]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    // 如果没查到且是纯数字，则按 id 查一次
    if (!$category && ctype_digit($categoryParam)) {
        $stmt = $pdo->prepare("
            SELECT id, name, slug
            FROM product_categories
            WHERE id = :id AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':id' => (int)$categoryParam]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/* =========================================================
 * 4) 查询产品列表
 * ========================================================= */
$where  = 'p.is_active = 1';
$params = [];

if ($category) {
    $where .= ' AND p.category_id = :category_id';
    $params[':category_id'] = (int)$category['id'];
}

$sql = "
    SELECT 
        p.id,
        p.name,
        p.slug,
        p.short_desc,
        p.main_image,
        p.price_label,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    LEFT JOIN product_categories c ON p.category_id = c.id
    WHERE {$where}
    ORDER BY p.is_featured DESC, p.created_at DESC, p.id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
 * 5) 页面标题 / body class
 * ========================================================= */
$page_title = $category ? ('Products - ' . $category['name']) : 'Products';
$body_class = 'products-page';

require_once __DIR__ . '/header.php';

// 常用链接
$home_link      = link_page('index.php', '/index.html');
$products_root  = link_products_list(''); // All products
$current_cat    = $category ? (string)$category['slug'] : '';
?>

<main class="main">

  <!-- Page Title -->
  <div class="page-title">
    <div class="container d-lg-flex justify-content-between align-items-center">
      <h1 class="mb-2 mb-lg-0">
        <?= htmlspecialchars($category['name'] ?? 'Products') ?>
      </h1>
      <nav class="breadcrumbs">
        <ol>
          <li><a href="<?= htmlspecialchars($home_link) ?>">Home</a></li>

          <?php if ($category): ?>
            <li><a href="<?= htmlspecialchars($products_root) ?>">Products</a></li>
            <li class="current"><?= htmlspecialchars($category['name']) ?></li>
          <?php else: ?>
            <li class="current">Products</li>
          <?php endif; ?>

        </ol>
      </nav>
    </div>
  </div><!-- End Page Title -->

  <!-- Product List Section -->
  <section id="product-list" class="blog-posts section">
    <div class="container">

      <?php if ($category): ?>
        <div class="mb-4">
          <span class="badge bg-warning text-dark">
            Category: <?= htmlspecialchars($category['name']) ?>
          </span>
        </div>
      <?php endif; ?>

      <div class="row gy-4">

        <?php if (empty($products)): ?>

          <div class="col-12">
            <div class="alert alert-info mb-0">
              <?= $category ? 'No products in this category yet.' : 'No products available yet.' ?>
            </div>
          </div>

        <?php else: ?>

          <?php foreach ($products as $p): ?>
            <?php $detail_link = link_product_detail((string)$p['slug']); ?>
            <div class="col-lg-4 col-md-6">
              <article>

                <div class="post-img">
                  <?php if (!empty($p['main_image'])): ?>
                    <img src="<?= htmlspecialchars($p['main_image']) ?>" alt="" class="img-fluid">
                  <?php else: ?>
                    <img src="/includes/assets/img/placeholder-product.jpg" alt="" class="img-fluid">
                  <?php endif; ?>
                </div>

                <?php if (!empty($p['category_name'])): ?>
                  <p class="post-category">
                    <?= htmlspecialchars($p['category_name']) ?>
                  </p>
                <?php endif; ?>

                <h2 class="title">
                  <a href="<?= htmlspecialchars($detail_link) ?>">
                    <?= htmlspecialchars($p['name']) ?>
                  </a>
                </h2>

                <?php if (!empty($p['short_desc'])): ?>
                  <p class="mb-2" style="font-size: 0.95rem;">
                    <?= nl2br(htmlspecialchars($p['short_desc'])) ?>
                  </p>
                <?php endif; ?>

                <div class="d-flex align-items-center justify-content-between mt-2">
                  <?php if (!empty($p['price_label'])): ?>
                    <span class="text-muted" style="font-size: 0.9rem;">
                      <?= htmlspecialchars($p['price_label']) ?>
                    </span>
                  <?php else: ?>
                    <span></span>
                  <?php endif; ?>

                  <a href="<?= htmlspecialchars($detail_link) ?>"
                     class="btn btn-sm btn-outline-primary">
                    View Details
                  </a>
                </div>

              </article>
            </div>
          <?php endforeach; ?>

        <?php endif; ?>

      </div>

    </div>
  </section>

</main>

<?php require_once __DIR__ . '/footer.php'; ?>
