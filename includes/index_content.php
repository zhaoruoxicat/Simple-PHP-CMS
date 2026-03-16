<?php
// /includes/index_content.php
// core.php 已由根 index.php 引入，这里只负责输出首页主体内容。
// 当前版本完全保留 Flexor 原始 index 模板的各个 section，只调整静态资源路径到 /includes/assets/。
?>

<!-- Hero Section -->
<section id="hero" class="hero section dark-background">

  <img src="/includes/assets/img/bg.jpg" alt="" data-aos="fade-in">

  <div class="container position-relative">

    <!-- Welcome Title -->
    <div class="welcome position-relative" data-aos="fade-down" data-aos-delay="100">
      <h2>大标题大标题大标题</h2>
      <p>
        副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题副标题
      </p>
    </div><!-- End Welcome -->

    <div class="content row gy-4">

      <!-- Why Choose Us -->
      <div class="col-lg-4 d-flex align-items-stretch">
        <div class="why-box" data-aos="zoom-out" data-aos-delay="200">
          <h3>Why Choose us?</h3>
          <p>
            介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
          </p>
          <div class="text-center">
            <a href="about.html" class="more-btn">
              <span>Learn More</span> <i class="bi bi-chevron-right"></i>
            </a>
          </div>
        </div>
      </div><!-- End Why Box -->

      <!-- Icon Boxes -->
      <div class="col-lg-8 d-flex align-items-stretch">
        <div class="d-flex flex-column justify-content-center">
          <div class="row gy-4">

            <!-- Icon 1 -->
            <div class="col-xl-4 d-flex align-items-stretch">
              <div class="icon-box" data-aos="zoom-out" data-aos-delay="300">
                <i class="bi bi-clipboard-data"></i>
                <h4>小标题</h4>
                <p>
                 介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
                </p>
              </div>
            </div>

            <!-- Icon 2 -->
            <div class="col-xl-4 d-flex align-items-stretch">
              <div class="icon-box" data-aos="zoom-out" data-aos-delay="400">
                <i class="bi bi-gem"></i>
                <h4>小标题</h4>
                <p>
                  介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
                </p>
              </div>
            </div>

            <!-- Icon 3 -->
            <div class="col-xl-4 d-flex align-items-stretch">
              <div class="icon-box" data-aos="zoom-out" data-aos-delay="500">
                <i class="bi bi-inboxes"></i>
                <h4>小标题</h4>
                <p>
                  介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
                </p>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div><!-- End  Content -->

  </div>

</section><!-- /Hero Section -->


<!-- About Section -->
<section id="about" class="about section light-background">

  <div class="container">

    <div class="row gy-4">

<?php
// 从配置表取出首页 YouTube 链接
$rawYoutube = trim(get_setting('homepage_youtube', ''));

// 默认备用嵌入地址（防止后台没填时整块空白）
$embedYoutube = 'https://www.youtube.com/embed/AUT5cjCOSQA';

if ($rawYoutube !== '') {
    $url = $rawYoutube;

    // 1) https://www.youtube.com/watch?v=XXXX
    if (preg_match('~v=([A-Za-z0-9_\-]{6,})~', $url, $m)) {
        $videoId = $m[1];
        $embedYoutube = 'https://www.youtube.com/embed/' . $videoId;
    }
    // 2) https://youtu.be/XXXX
    elseif (preg_match('~youtu\.be/([A-Za-z0-9_\-]{6,})~', $url, $m)) {
        $videoId = $m[1];
        $embedYoutube = 'https://www.youtube.com/embed/' . $videoId;
    }
    // 3) 已经是 embed 链接或其它形式，就直接用
    elseif (str_contains($url, 'youtube.com/embed') || str_contains($url, 'youtube-nocookie.com/embed')) {
        $embedYoutube = $url;
    }
}
?>

<!-- Left: Embedded YouTube Video (Vertically Centered) -->
<div class="col-lg-5 d-flex align-items-center" style="min-height:300px;"
     data-aos="fade-up" data-aos-delay="200">
  <div class="ratio ratio-16x9 w-100">
    <iframe
      src="<?= htmlspecialchars($embedYoutube) ?>"
      title="Factory Video"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
      allowfullscreen>
    </iframe>
  </div>
</div>


      <!-- Right Content -->
      <div class="col-lg-7 content" data-aos="fade-up" data-aos-delay="100">
        <h3>About Us</h3>
        <p>
          介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
        </p>

        <ul>
          <li>
            <i class="bi bi-diagram-3"></i>
            <div>
              <h5>小标题</h5>
              <p>介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明</p>
            </div>
          </li>

          <li>
            <i class="bi bi-fullscreen-exit"></i>
            <div>
              <h5>小标题</h5>
              <p>介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明</p>
            </div>
          </li>

          <li>
            <i class="bi bi-broadcast"></i>
            <div>
              <h5>小标题</h5>
              <p>介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明.</p>
            </div>
          </li>

          <li>
            <i class="bi bi-buildings"></i>
            <div>
              <h5>小标题</h5>
              <p>介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明</p>
            </div>
          </li>
        </ul>
      </div>

    </div>

  </div>

</section>


<!-- Services Section -->
<section id="services" class="services section light-background">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Our Services</h2>
    <p>副标题副标题副标题副标题副标题副标题副标题副标题</p>
  </div><!-- End Section Title -->

  <div class="container">
    <div class="row gy-4">

      <!-- Service 1 -->
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="service-item position-relative">
          <div class="icon">
            <i class="bi bi-building-check"></i>
          </div>
          <a href="#" class="stretched-link">
            <h3>小标题</h3>
          </a>
          <p>
            介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
          </p>
        </div>
      </div><!-- End Service Item -->

      <!-- Service 2 -->
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="service-item position-relative">
          <div class="icon">
            <i class="bi bi-shield-lock"></i>
          </div>
          <a href="#" class="stretched-link">
            <h3>小标题</h3>
          </a>
          <p>
            介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
          </p>
        </div>
      </div><!-- End Service Item -->

      <!-- Service 3 -->
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="service-item position-relative">
          <div class="icon">
            <i class="bi bi-globe2"></i>
          </div>
          <a href="#" class="stretched-link">
            <h3>小标题</h3>
          </a>
          <p>
            介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
          </p>
        </div>
      </div><!-- End Service Item -->

      <!-- Service 4 -->
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
        <div class="service-item position-relative">
          <div class="icon">
            <i class="bi bi-bezier2"></i>
          </div>
          <a href="#" class="stretched-link">
            <h3>小标题</h3>
          </a>
          <p>
            介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
          </p>
        </div>
      </div><!-- End Service Item -->

      <!-- Service 5 -->
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
        <div class="service-item position-relative">
          <div class="icon">
            <i class="bi bi-truck"></i>
          </div>
          <a href="#" class="stretched-link">
            <h3>小标题</h3>
          </a>
          <p>
            介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
          </p>
        </div>
      </div><!-- End Service Item -->

      <!-- Service 6 -->
      <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
        <div class="service-item position-relative">
          <div class="icon">
            <i class="bi bi-emoji-smile"></i>
          </div>
          <a href="#" class="stretched-link">
            <h3>小标题</h3>
          </a>
          <p>
            介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
          </p>
        </div>
      </div><!-- End Service Item -->

    </div>
  </div>

</section><!-- /Services Section -->
<!-- Alt Services Section -->
<section id="alt-services" class="alt-services section">

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="row gy-4">

      <!-- Item 1：生产设备 -->
      <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="200">
        <div class="service-item position-relative">
          <div class="img">
            <img src="/includes/assets/img/services-1.jpg" class="img-fluid" alt="">
          </div>
          <div class="details">
            <h3>小标题</h3>
            <p>
              介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
            </p>
          </div>
        </div>
      </div><!-- End Service Item -->

      <!-- Item 2：品质控制 -->
      <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="300">
        <div class="service-item position-relative">
          <div class="img">
            <img src="/includes/assets/img/services-2.jpg" class="img-fluid" alt="">
          </div>
          <div class="details">
            <h3>小标题</h3>
            <p>
              介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
            </p>
          </div>
        </div>
      </div><!-- End Service Item -->

      <!-- Item 3：生产能力 -->
      <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="400">
        <div class="service-item position-relative">
          <div class="img">
            <img src="/includes/assets/img/services-3.jpg" class="img-fluid" alt="">
          </div>
          <div class="details">
            <h3>小标题</h3>
            <p>
              介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
            </p>
          </div>
        </div>
      </div><!-- End Service Item -->

      <!-- Item 4：支持定制 -->
      <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="500">
        <div class="service-item position-relative">
          <div class="img">
            <img src="/includes/assets/img/services-4.jpg" class="img-fluid" alt="">
          </div>
          <div class="details">
            <h3>小标题</h3>
            <p>
              介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
            </p>
          </div>
        </div>
      </div><!-- End Service Item -->

    </div>

  </div>

</section><!-- /Alt Services Section -->


<!-- Testimonials Section -->
<section id="testimonials" class="testimonials section dark-background">

  <img src="/includes/assets/img/testimonials-bg.jpg" class="testimonials-bg" alt="">

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="swiper init-swiper">
      <script type="application/json" class="swiper-config">
        {
          "loop": true,
          "speed": 600,
          "autoplay": { "delay": 5000 },
          "slidesPerView": "auto",
          "pagination": { "el": ".swiper-pagination", "type": "bullets", "clickable": true }
        }
      </script>

      <div class="swiper-wrapper">

        <!-- Testimonial 1 -->
        <div class="swiper-slide">
          <div class="testimonial-item text-center">

            <!-- Line Icon Avatar -->
            <div class="testimonial-icon mb-3">
              <i class="bi bi-person-circle" style="font-size:72px;color:#fff;"></i>
            </div>

            <h3>名字</h3>
            <h4>职务, 国家</h4>

            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
            </div>

            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>
                介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
              </span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <!-- Testimonial 2 -->
        <div class="swiper-slide">
          <div class="testimonial-item text-center">

            <div class="testimonial-icon mb-3">
              <i class="bi bi-person-circle" style="font-size:72px;color:#fff;"></i>
            </div>

            <h3>名字</h3>
            <h4>职务, 国家</h4>

            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
            </div>

            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>
                介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
              </span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <!-- Testimonial 3 -->
        <div class="swiper-slide">
          <div class="testimonial-item text-center">

            <div class="testimonial-icon mb-3">
              <i class="bi bi-person-circle" style="font-size:72px;color:#fff;"></i>
            </div>

            <h3>名字</h3>
            <h4>职务, 国家</h4>

            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
            </div>

            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>
                介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
              </span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <!-- Testimonial 4 -->
        <div class="swiper-slide">
          <div class="testimonial-item text-center">

            <div class="testimonial-icon mb-3">
              <i class="bi bi-person-circle" style="font-size:72px;color:#fff;"></i>
            </div>

            <h3>名字</h3>
            <h4>职务, 国家</h4>

            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
            </div>

            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>
                介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
              </span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

        <!-- Testimonial 5 -->
        <div class="swiper-slide">
          <div class="testimonial-item text-center">

            <div class="testimonial-icon mb-3">
              <i class="bi bi-person-circle" style="font-size:72px;color:#fff;"></i>
            </div>

            <h3>名字</h3>
            <h4>职务, 国家</h4>

            <div class="stars">
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
            </div>

            <p>
              <i class="bi bi-quote quote-icon-left"></i>
              <span>
                介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明介绍说明
              </span>
              <i class="bi bi-quote quote-icon-right"></i>
            </p>
          </div>
        </div>

      </div><!-- /.swiper-wrapper -->

      <div class="swiper-pagination"></div>
    </div>

  </div>

</section><!-- /Testimonials Section -->


<?php
// 放在本 section 前面，保证 $pdo 已经可用（core.php 在 header 里已经 require 过的话，这里就不用再引了）

/* ===== 判定当前访问形态：HTML or PHP（兼容生成缓存 ?__static=1） ===== */
$reqUri  = $_SERVER['REQUEST_URI'] ?? '/';
$reqPath = parse_url($reqUri, PHP_URL_PATH) ?: '/';
$is_html_mode = false;
if (isset($_GET['__static']) && (string)$_GET['__static'] === '1') {
  $is_html_mode = true;
} else {
  $is_html_mode = (substr($reqPath, -5) === '.html');
}

/* ===== 产品详情链接：HTML => /products/xxx.html，PHP => products.php?slug=xxx ===== */
if (!function_exists('link_product_detail')) {
  function link_product_detail(string $slug): string {
    global $is_html_mode;
    $slug = trim($slug);
    if ($slug === '') return $is_html_mode ? '/products/index.html' : 'products_list.php';

    if ($is_html_mode) {
      return '/products/' . rawurlencode($slug) . '.html';
    }
    return 'products.php?slug=' . urlencode($slug);
  }
}

$portfolioProducts  = [];
$portfolioCategories = [];

try {
    // 读取所有有分类的启用产品
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.name,
            p.slug,
            p.short_desc,
            p.main_image,
            p.category_id,
            c.name AS category_name
        FROM products p
        INNER JOIN product_categories c ON p.category_id = c.id
        WHERE p.is_active = 1
          AND c.is_active = 1
        ORDER BY 
            c.sort_order ASC,
            c.id ASC,
            p.is_featured DESC,
            p.id DESC
    ");
    $portfolioProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 按 category_id 汇总分类，用于顶部筛选按钮
    foreach ($portfolioProducts as $p) {
        $cid = (int)$p['category_id'];
        if (!isset($portfolioCategories[$cid])) {
            $portfolioCategories[$cid] = [
                'id'   => $cid,
                'name' => $p['category_name'] ?? 'Category ' . $cid,
            ];
        }
    }
} catch (Throwable $e) {
    $portfolioProducts   = [];
    $portfolioCategories = [];
}
?>

<!-- Portfolio Section -->
<section id="portfolio" class="portfolio section">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Products</h2>
    <p>副标题副标题副标题副标题副标题</p>
  </div><!-- End Section Title -->

  <div class="container">

    <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">

      <!-- 筛选按钮：All + 按分类 -->
      <ul class="portfolio-filters isotope-filters" data-aos="fade-up" data-aos-delay="100">
        <li data-filter="*" class="filter-active">All</li>

        <?php if (!empty($portfolioCategories)): ?>
          <?php foreach ($portfolioCategories as $cat): ?>
            <li data-filter=".cat-<?= (int)$cat['id'] ?>">
              <?= htmlspecialchars($cat['name']) ?>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul><!-- End Portfolio Filters -->

      <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="200">

        <?php if (empty($portfolioProducts)): ?>

          <div class="col-12">
            <p class="text-center text-muted mb-0">
              No products found. Please add products in admin panel.
            </p>
          </div>

        <?php else: ?>

          <?php foreach ($portfolioProducts as $p): ?>
            <?php
              $cid        = (int)$p['category_id'];
              $catClass   = 'cat-' . $cid;
              $title      = $p['name'] ?: 'Product';
              $desc       = $p['short_desc'] ?: '';
              $img        = $p['main_image'] ?: '/includes/assets/img/masonry-portfolio/masonry-portfolio-1.jpg';

              // ✅ 这里改为适配静态 HTML
              $detailUrl  = link_product_detail((string)$p['slug']);

              $galleryKey = 'portfolio-gallery-cat-' . $cid;
            ?>
            <div class="col-lg-4 col-md-6 portfolio-item isotope-item <?= htmlspecialchars($catClass) ?>">
              <img src="<?= htmlspecialchars($img) ?>" class="img-fluid" alt="<?= htmlspecialchars($title) ?>">
              <div class="portfolio-info">
                <h4><?= htmlspecialchars($title) ?></h4>
                <?php if ($desc !== ''): ?>
                  <p><?= htmlspecialchars($desc) ?></p>
                <?php endif; ?>

                <!-- 放大预览 -->
                <a href="<?= htmlspecialchars($img) ?>"
                   title="<?= htmlspecialchars($title) ?>"
                   data-gallery="<?= htmlspecialchars($galleryKey) ?>"
                   class="glightbox preview-link">
                  <i class="bi bi-zoom-in"></i>
                </a>

                <!-- 产品详情链接（✅ 已适配 HTML/PHP） -->
                <a href="<?= htmlspecialchars($detailUrl) ?>"
                   title="More Details"
                   class="details-link">
                  <i class="bi bi-link-45deg"></i>
                </a>
              </div>
            </div><!-- End Portfolio Item -->
          <?php endforeach; ?>

        <?php endif; ?>

      </div><!-- End Portfolio Container -->

    </div>

  </div>

</section><!-- /Portfolio Section -->




<!-- Faq Section -->
<?php require __DIR__ . '/index_faq.php'; ?>

<?php
// 从 cms_settings 读取公司联系方式
$company_address = get_setting('company_address', 'A108 Adam Street, New York, NY 535022');
$company_phone   = get_setting('company_phone', '+1 5589 55488 55');
$company_email   = get_setting('company_email', 'info@example.com');
?>

<!-- Contact Section -->
<section id="contact" class="contact section">

  <!-- Section Title -->
  <div class="container section-title" data-aos="fade-up">
    <h2>Contact</h2>
    <p>Get in touch with us for more product details and quotation.</p>
  </div><!-- End Section Title -->

  <div class="container" data-aos="fade-up" data-aos-delay="100">

    <div class="row gy-4">

      <div class="col-md-6">
        <div class="info-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="200">
          <i class="icon bi bi-geo-alt flex-shrink-0"></i>
          <div>
            <h3>Address</h3>
            <p><?= nl2br(htmlspecialchars($company_address)) ?></p>
          </div>
        </div>
      </div><!-- End Info Item -->

      <div class="col-md-6">
        <div class="info-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="300">
          <i class="icon bi bi-telephone flex-shrink-0"></i>
          <div>
            <h3>Call Us</h3>
            <p><?= htmlspecialchars($company_phone) ?></p>
          </div>
        </div>
      </div><!-- End Info Item -->

      <div class="col-md-6">
        <div class="info-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="400">
          <i class="icon bi bi-envelope flex-shrink-0"></i>
          <div>
            <h3>Email Us</h3>
            <p>
              <a href="mailto:<?= htmlspecialchars($company_email) ?>">
                <?= htmlspecialchars($company_email) ?>
              </a>
            </p>
          </div>
        </div>
      </div><!-- End Info Item -->

      <div class="col-md-6">
        <div class="info-item d-flex align-items-center" data-aos="fade-up" data-aos-delay="500">
          <i class="icon bi bi-share flex-shrink-0"></i>
          <div>
            <h3>Social Profiles</h3>
<div class="social-links">
  <?= render_social_icon($linkX,         'bi-twitter-x') ?>
  <?= render_social_icon($linkFacebook,  'bi-facebook') ?>
  <?= render_social_icon($linkInstagram, 'bi-instagram') ?>
  <?= render_social_icon($linkLinkedin,  'bi-linkedin') ?>
</div>
          </div>
        </div>
      </div><!-- End Info Item -->

    </div>

  </div>

</section><!-- /Contact Section -->
