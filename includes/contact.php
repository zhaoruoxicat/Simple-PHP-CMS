<?php
// /includes/contact.php — 联系我们页面（内容部分）
// 由根目录 contact.php 负责引入 header/footer，这里只专注主体内容

require_once __DIR__ . '/../core.php';

// 页面 meta（一般在根 contact.php 里用，这里保留以防需要）
$company_name  = get_setting('company_name', 'Your Company');
$page_title    = 'Contact Us - ' . $company_name;
$body_class    = 'contact-page';

// 联系方式相关配置，全部从 cms_settings 表调取
$company_addr     = get_setting('company_address', '');
$company_phone    = get_setting('company_phone', '');
$company_email    = get_setting('company_email', '');
$contact_title    = get_setting('contact_title', 'Contact Us');
$contact_intro    = get_setting('contact_intro', 'Leave us a message or contact us directly via the following ways.');
$contact_extra    = get_setting('contact_extra', '');        // 额外说明（可选）

// ✅ 这里改为使用和后台设置一致的 key：company_whatsapp / company_wechat
$contact_whatsapp = get_setting('company_whatsapp', '');    // WhatsApp 号码（可选）
$contact_wechat   = get_setting('company_wechat', '');      // 微信号（可选）

// 地图：这里使用 cms_settings.setting_key = 'contact_map'
// 内容直接填 Google 提供的 <iframe ...></iframe> 代码，前端不做转义，原样输出
$contact_map      = get_setting('contact_map', '');

// 产品分类：左侧列表使用
// 表结构：product_categories(id, name, slug, sort_order, is_active, ...)
// slug 用于 URL，如 products_list.php?category=slug
$categories = [];
try {
    $stmt = $pdo->prepare("
        SELECT id, name, slug
        FROM product_categories
        WHERE is_active = 1
        ORDER BY sort_order ASC, name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $categories = [];
}

?>

<!-- Page Title（沿用 service-details 布局） -->
<div class="page-title">
  <div class="container d-lg-flex justify-content-between align-items-center">
    <h1 class="mb-2 mb-lg-0"><?= htmlspecialchars($contact_title) ?></h1>
    <nav class="breadcrumbs">
      <ol>
        <li><a href="index.php">Home</a></li>
        <li class="current">Contact</li>
      </ol>
    </nav>
  </div>
</div><!-- End Page Title -->

<!-- Contact Details Section（基于 service-details 两栏结构） -->
<section id="contact-details" class="service-details section">

  <div class="container">

    <div class="row gy-4">

      <!-- 左侧：产品分类列表 + 简短介绍 -->
      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">

        <div class="services-list">
          <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
              <a href="products_list.php?category=<?= urlencode($cat['slug']) ?>">
                <?= htmlspecialchars($cat['name']) ?>
              </a>
            <?php endforeach; ?>
          <?php else: ?>
            <a href="#" class="active">No product categories yet</a>
          <?php endif; ?>
        </div>

        <h4><?= htmlspecialchars($company_name) ?></h4>
        <?php if ($contact_intro): ?>
          <p><?= nl2br(htmlspecialchars($contact_intro)) ?></p>
        <?php endif; ?>

      </div>

      <!-- 右侧：联系方式全部来自数据库 -->
      <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">

        <div class="row gy-4">

          <?php if ($company_addr): ?>
            <div class="col-md-6">
              <div class="info-item d-flex align-items-start">
                <i class="icon bi bi-geo-alt flex-shrink-0"></i>
                <div>
                  <h5>Address</h5>
                  <p class="mb-0"><?= nl2br(htmlspecialchars($company_addr)) ?></p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($company_phone): ?>
            <div class="col-md-6">
              <div class="info-item d-flex align-items-start">
                <i class="icon bi bi-telephone flex-shrink-0"></i>
                <div>
                  <h5>Phone</h5>
                  <p class="mb-0"><?= htmlspecialchars($company_phone) ?></p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($company_email): ?>
            <div class="col-md-6">
              <div class="info-item d-flex align-items-start">
                <i class="icon bi bi-envelope flex-shrink-0"></i>
                <div>
                  <h5>Email</h5>
                  <p class="mb-0">
                    <a href="mailto:<?= htmlspecialchars($company_email) ?>">
                      <?= htmlspecialchars($company_email) ?>
                    </a>
                  </p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($contact_whatsapp): ?>
            <div class="col-md-6">
              <div class="info-item d-flex align-items-start">
                <i class="icon bi bi-whatsapp flex-shrink-0"></i>
                <div>
                  <h5>WhatsApp</h5>
                  <p class="mb-0"><?= htmlspecialchars($contact_whatsapp) ?></p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($contact_wechat): ?>
            <div class="col-md-6">
              <div class="info-item d-flex align-items-start">
                <i class="icon bi bi-chat-dots flex-shrink-0"></i>
                <div>
                  <h5>WeChat</h5>
                  <p class="mb-0"><?= htmlspecialchars($contact_wechat) ?></p>
                </div>
              </div>
            </div>
          <?php endif; ?>

        </div><!-- End row gy-4 -->

        <?php if ($contact_extra): ?>
          <p class="mt-4">
            <?= nl2br(htmlspecialchars($contact_extra)) ?>
          </p>
        <?php endif; ?>

        <?php if ($contact_map): ?>
          <div class="mt-4">
            <!-- 这里不做转义，直接输出 Google 提供的 <iframe> 嵌入代码 -->
            <?= $contact_map ?>
          </div>
        <?php endif; ?>

      </div><!-- End col-lg-8 -->

    </div><!-- End row -->

  </div>

</section><!-- /Contact Details Section -->
