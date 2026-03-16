<?php
// /includes/about.php
// core.php 已由 header.php 引入，这里直接用 $pdo / get_setting 即可

// 读取 about_page 主内容
try {
    $stmt = $pdo->prepare("SELECT * FROM about_page WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $about = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $about = [];
}

// 准备一些默认值，防止为空时页面难看
$about_title      = $about['title']        ?? 'About Our Company';
$about_subtitle   = $about['subtitle']     ?? '';
$about_banner_img = $about['banner_image'] ?? '/includes/assets/img/blog/blog-1.jpg';
$about_content    = $about['content']      ?? '<p>Company introduction content is not configured yet. Please edit it in admin panel.</p>';
$about_side_title = $about['side_title']   ?? 'Company Info';
$about_side_body  = $about['side_content'] ?? '';

// 右侧可顺便附带全站设置里的联系方式
$company_addr  = get_setting('company_address', '');
$company_phone = get_setting('company_phone', '');
$company_email = get_setting('company_email', '');
?>

<!-- Page Title（沿用 blog-details 的布局） -->
<div class="page-title">
  <div class="container d-lg-flex justify-content-between align-items-center">
    <h1 class="mb-2 mb-lg-0"><?= htmlspecialchars($about_title) ?></h1>
    <nav class="breadcrumbs">
      <ol>
        <li><a href="index.php">Home</a></li>
        <li class="current">About</li>
      </ol>
    </nav>
  </div>
</div><!-- End Page Title -->

<div class="container">
  <div class="row">

    <!-- 左侧主内容 -->
    <div class="col-lg-8">

      <section id="about-details" class="blog-details section">
        <div class="container">

          <article class="article">

            <?php if (!empty($about_banner_img)): ?>
              <div class="post-img">
                <img src="<?= htmlspecialchars($about_banner_img) ?>" alt="" class="img-fluid">
              </div>
            <?php endif; ?>

            <h2 class="title"><?= htmlspecialchars($about_title) ?></h2>

            <?php if ($about_subtitle): ?>
              <div class="meta-top">
                <ul>
                  <li class="d-flex align-items-center">
                    <i class="bi bi-building"></i>
                    <span><?= htmlspecialchars($about_subtitle) ?></span>
                  </li>
                </ul>
              </div>
            <?php endif; ?>

            <div class="content">
              <!-- about_content 支持 HTML，因此不做转义，内容由后台管理员维护 -->
              <?= $about_content ?>
            </div><!-- End content -->

          </article>

        </div>
      </section><!-- /About Details Section -->

    </div><!-- End col-lg-8 -->

    <!-- 右侧侧栏 -->
    <div class="col-lg-4 sidebar">

      <div class="widgets-container">

        <!-- 公司信息 Widget -->
        <div class="widget-item">
          <h3 class="widget-title"><?= htmlspecialchars($about_side_title) ?></h3>

          <?php if ($about_side_body): ?>
            <div class="mt-3">
              <?= $about_side_body /* 允许 HTML */ ?>
            </div>
          <?php endif; ?>

          <div class="mt-3">
            <?php if ($company_addr): ?>
              <p class="mb-1"><i class="bi bi-geo-alt"></i> <?= nl2br(htmlspecialchars($company_addr)) ?></p>
            <?php endif; ?>
            <?php if ($company_phone): ?>
              <p class="mb-1"><i class="bi bi-telephone"></i> <?= htmlspecialchars($company_phone) ?></p>
            <?php endif; ?>
            <?php if ($company_email): ?>
              <p class="mb-0"><i class="bi bi-envelope"></i>
                <a href="mailto:<?= htmlspecialchars($company_email) ?>">
                  <?= htmlspecialchars($company_email) ?>
                </a>
              </p>
            <?php endif; ?>
          </div>
        </div><!-- /Company widget -->

      </div><!-- /.widgets-container -->

    </div><!-- End sidebar -->

  </div><!-- End row -->
</div><!-- End container -->
