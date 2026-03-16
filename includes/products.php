<?php
// /includes/products.php
require_once __DIR__ . '/../core.php';

/* =========================================================
 * 0) 判定当前访问形态：HTML 模式 or PHP 模式
 *    - 访问 /products/xxx.html 或 /products_list/xxx.html => HTML 模式
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
 * 1) 链接生成器：根据模式生成对应链接
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
    // - HTML：/products/<slug>.html
    // - PHP： products.php?slug=<slug>
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
 * 2) 支持通过 ?id=1 或 ?slug=xxx 访问
 * ========================================================= */
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = trim($_GET['slug'] ?? '');

$product = null;

// 读取产品基本信息
if ($slug !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM products
        WHERE slug = :slug AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM products
        WHERE id = :id AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * 如果产品不存在：输出带 header/footer 的 404 提示
 */
if (!$product) {
    $page_title = 'Product Not Found';
    $body_class = 'product-page';

    require_once __DIR__ . '/header.php';

    $home_link     = link_page('index.php', '/index.html');
    $products_link = link_products_list('');
    ?>
    <div class="page-title">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Product Not Found</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="<?= htmlspecialchars($home_link) ?>">Home</a></li>
            <li><a href="<?= htmlspecialchars($products_link) ?>">Products</a></li>
            <li class="current">Product</li>
          </ol>
        </nav>
      </div>
    </div>

    <section class="section">
      <div class="container">
        <div class="alert alert-warning">
          当前产品不存在或已下架。
        </div>
      </div>
    </section>
    <?php
    require_once __DIR__ . '/footer.php';
    exit;
}

// 读取图片：主图 + 附加图库
$gallery_images = [];

try {
    $stmt = $pdo->prepare("
        SELECT image_url
        FROM product_images
        WHERE product_id = :pid
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([':pid' => $product['id']]);
    $gallery_images = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $gallery_images = [];
}

if (empty($gallery_images) && !empty($product['main_image'])) {
    $gallery_images[] = $product['main_image'];
}

// SEO 关键词拆成数组，用于底部标签展示
$seo_keywords_raw = $product['seo_keywords'] ?? '';
$seo_keywords = array_filter(
    array_map('trim', explode(',', (string)$seo_keywords_raw)),
    fn($v) => $v !== ''
);

// 右侧栏：产品分类列表
$categories = [];
try {
    $stmt = $pdo->query("
        SELECT id, name, slug
        FROM product_categories
        WHERE is_active = 1
        ORDER BY sort_order ASC, id ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $categories = [];
}

// 右侧栏：推荐产品（同分类优先）
$recommended = [];
try {
    if (!empty($product['category_id'])) {
        $stmt = $pdo->prepare("
            SELECT id, name, slug, main_image
            FROM products
            WHERE is_active = 1
              AND id <> :id
              AND category_id = :cat_id
            ORDER BY is_featured DESC, id DESC
            LIMIT 4
        ");
        $stmt->execute([
            ':id'     => $product['id'],
            ':cat_id' => $product['category_id'],
        ]);
        $recommended = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if (count($recommended) < 2) {
        $limit_left = 4 - count($recommended);
        $stmt = $pdo->prepare("
            SELECT id, name, slug, main_image
            FROM products
            WHERE is_active = 1
              AND id <> :id
            ORDER BY is_featured DESC, id DESC
            LIMIT :limit_left
        ");
        $stmt->bindValue(':id', $product['id'], PDO::PARAM_INT);
        $stmt->bindValue(':limit_left', $limit_left, PDO::PARAM_INT);
        $stmt->execute();
        $more = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $recommended = array_merge($recommended, $more);
    }
} catch (Throwable $e) {
    $recommended = [];
}

/* ========= SEO / header.php 变量 ========= */
$page_title = $product['name'] ?? 'Product Detail';

$meta_keywords    = isset($product['seo_keywords'])    ? trim($product['seo_keywords'])    : null;
$meta_description = isset($product['seo_description']) ? trim($product['seo_description']) : null;

if ($meta_keywords === '') $meta_keywords = null;
if ($meta_description === '') $meta_description = null;

$body_class = 'product-page';
require_once __DIR__ . '/header.php';

/* =========================================================
 * 3) 常用链接（供面包屑/侧边栏）
 * ========================================================= */
$home_link        = link_page('index.php', '/index.html');
$products_root    = link_products_list('');
$contact_form_url = link_page('/contact.php', '/contact.html');

// 当前产品分类（用于面包屑 Products -> Category）
$current_cat_slug = '';
$current_cat_name = '';
if (!empty($product['category_id'])) {
    foreach ($categories as $c) {
        if ((int)$c['id'] === (int)$product['category_id']) {
            $current_cat_slug = (string)($c['slug'] ?? '');
            $current_cat_name = (string)($c['name'] ?? '');
            break;
        }
    }
}
$category_link = $current_cat_slug !== '' ? link_products_list($current_cat_slug) : $products_root;
?>

<!-- Page Title -->
<div class="page-title">
  <div class="container d-lg-flex justify-content-between align-items-center">
    <h1 class="mb-2 mb-lg-0">
      <?= htmlspecialchars($product['name']) ?>
    </h1>
    <nav class="breadcrumbs">
      <ol>
        <li><a href="<?= htmlspecialchars($home_link) ?>">Home</a></li>
        <li><a href="<?= htmlspecialchars($products_root) ?>">Products</a></li>

        <?php if ($current_cat_slug !== '' && $current_cat_name !== ''): ?>
          <li><a href="<?= htmlspecialchars($category_link) ?>"><?= htmlspecialchars($current_cat_name) ?></a></li>
        <?php endif; ?>

        <li class="current"><?= htmlspecialchars($product['name']) ?></li>
      </ol>
    </nav>
  </div>
</div><!-- End Page Title -->

<div class="container">
  <div class="row">

    <div class="col-lg-8">

      <section id="product-details" class="blog-details section">
        <div class="container">

          <article class="article">

            <div class="portfolio-details-slider swiper init-swiper" data-aos="fade-up" data-aos-delay="100">

              <script type="application/json" class="swiper-config">
                {
                  "loop": true,
                  "speed": 600,
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": "auto",
                  "pagination": {
                    "el": ".swiper-pagination",
                    "type": "bullets",
                    "clickable": true
                  }
                }
              </script>

              <div class="swiper-wrapper align-items-center">
                <?php if (!empty($gallery_images)): ?>
                  <?php foreach ($gallery_images as $img_url): ?>
                    <div class="swiper-slide text-center">
                      <img src="<?= htmlspecialchars($img_url) ?>"
                           alt=""
                           class="img-fluid d-block mx-auto">
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="swiper-slide text-center">
                    <img src="/includes/assets/img/placeholder.png"
                         alt=""
                         class="img-fluid d-block mx-auto">
                  </div>
                <?php endif; ?>
              </div>

              <div class="swiper-pagination"></div>
            </div>

            <h2 class="title mt-4">
              <?= htmlspecialchars($product['name']) ?>
            </h2>

            <div class="meta-top">
              <ul>
                <?php if (!empty($product['price_label'])): ?>
                  <li class="d-flex align-items-center">
                    <i class="bi bi-cash-coin"></i>
                    <span><?= htmlspecialchars($product['price_label']) ?></span>
                  </li>
                <?php endif; ?>
                <?php if (!empty($product['is_featured'])): ?>
                  <li class="d-flex align-items-center">
                    <i class="bi bi-star-fill"></i>
                    <span>Featured Product</span>
                  </li>
                <?php endif; ?>
              </ul>
            </div>

            <div class="content">
              <?php if (!empty($product['short_desc'])): ?>
                <p><strong><?= nl2br(htmlspecialchars($product['short_desc'])) ?></strong></p>
              <?php endif; ?>

              <?php if (!empty($product['description'])): ?>
                <div class="product-description">
                  <?= $product['description'] ?>
                </div>
              <?php else: ?>
                <p class="text-muted">
                  Product description will be updated soon.
                </p>
              <?php endif; ?>
            </div>

            <div class="meta-bottom">
              <?php if (!empty($seo_keywords)): ?>
                <i class="bi bi-tags"></i>
                <ul class="tags">
                  <?php foreach ($seo_keywords as $kw): ?>
                    <li><?= htmlspecialchars($kw) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>

          </article>

        </div>
      </section>

    </div>

    <!-- 右侧 Sidebar -->
    <div class="col-lg-4 sidebar">

      <div class="widgets-container">

<?php
$company_name    = get_setting('company_name', 'Your Company');
$company_phone   = get_setting('company_phone', '');
$company_email   = get_setting('company_email', '');
$company_address = get_setting('company_address', '');
?>

<!-- Company Contact Widget -->
<div class="widget-item company-contact-widget mb-4" style="border-left:4px solid #ff5821; padding-left:12px;">
  <h3 class="widget-title" style="color:#ff5821;">Contact Us</h3>

  <div class="mt-3">

    <?php if (!empty($company_name)): ?>
      <p class="mb-2">
        <i class="bi bi-building me-2" style="color:#ff5821;"></i>
        <strong><?= htmlspecialchars($company_name) ?></strong>
      </p>
    <?php endif; ?>

    <?php if (!empty($company_phone)): ?>
      <p class="mb-2">
        <i class="bi bi-telephone-fill me-2" style="color:#ff5821;"></i>
        <strong>Phone:</strong>
        <span><?= htmlspecialchars($company_phone) ?></span>
      </p>
    <?php endif; ?>

    <?php if (!empty($company_email)): ?>
      <p class="mb-2">
        <i class="bi bi-envelope-fill me-2" style="color:#ff5821;"></i>
        <strong>Email:</strong>
        <a href="mailto:<?= htmlspecialchars($company_email) ?>">
          <?= htmlspecialchars($company_email) ?>
        </a>
      </p>
    <?php endif; ?>

    <?php if (!empty($company_address)): ?>
      <p class="mb-2">
        <i class="bi bi-geo-alt-fill me-2" style="color:#ff5821;"></i>
        <strong>Address:</strong><br>
        <?= nl2br(htmlspecialchars($company_address)) ?>
      </p>
    <?php endif; ?>

    <a href="<?= htmlspecialchars($contact_form_url) ?>"
       class="btn btn-sm mt-2"
       style="background-color:#ff5821;border:none;color:#fff;">
      Contact Form
    </a>

  </div>
</div>
<!-- /Company Contact Widget -->

        <!-- Categories Widget -->
        <div class="categories-widget widget-item">
          <h3 class="widget-title">Product Categories</h3>
          <ul class="mt-3">
            <?php if (empty($categories)): ?>
              <li><span class="text-muted">No categories yet.</span></li>
            <?php else: ?>
              <?php foreach ($categories as $cat): ?>
                <?php
                  $cat_slug = (string)($cat['slug'] ?? '');
                  if ($cat_slug === '') $cat_slug = (string)($cat['id'] ?? '');
                  $cat_link = link_products_list($cat_slug);
                ?>
                <li>
                  <a href="<?= htmlspecialchars($cat_link) ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div><!--/Categories Widget -->

        <!-- Recommended Products Widget -->
        <div class="recent-posts-widget widget-item">
          <h3 class="widget-title">Recommended Products</h3>

          <?php if (empty($recommended)): ?>
            <p class="text-muted mb-0">No recommended products yet.</p>
          <?php else: ?>
            <?php foreach ($recommended as $rp): ?>
              <?php $rp_link = link_product_detail((string)$rp['slug']); ?>
              <div class="post-item d-flex align-items-center mb-3">
                <?php if (!empty($rp['main_image'])): ?>
                  <div class="flex-shrink-0 me-3 text-center" style="width:80px;">
                    <img src="<?= htmlspecialchars($rp['main_image']) ?>"
                         alt=""
                         class="img-fluid d-block mx-auto rounded">
                  </div>
                <?php endif; ?>
                <div>
                  <h4 class="mb-1" style="font-size: 0.95rem;">
                    <a href="<?= htmlspecialchars($rp_link) ?>">
                      <?= htmlspecialchars($rp['name']) ?>
                    </a>
                  </h4>
                  <span class="text-muted" style="font-size: 0.8rem;">View details</span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div><!--/Recommended Products Widget -->

      </div>

    </div>

  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
