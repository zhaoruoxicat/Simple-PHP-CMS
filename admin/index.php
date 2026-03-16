<?php
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';

$admin_page_title = "仪表盘";

// 统计数量（没有则显示 0）
$category_count = 0;
$product_count  = 0;
$faq_count      = 0;
$message_count  = 0;

try {
    $category_count = (int)$pdo->query("SELECT COUNT(*) FROM product_categories")->fetchColumn();
} catch (Throwable $e) {
    $category_count = 0;
}

try {
    $product_count = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
} catch (Throwable $e) {
    $product_count = 0;
}

try {
    $faq_count = (int)$pdo->query("SELECT COUNT(*) FROM faqs")->fetchColumn();
} catch (Throwable $e) {
    $faq_count = 0;
}

// 页脚留言表（不存在或出错时默认 0）
// 表名按之前约定：cms_footer_messages
try {
    $message_count = (int)$pdo->query("SELECT COUNT(*) FROM cms_footer_messages")->fetchColumn();
} catch (Throwable $e) {
    $message_count = 0;
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">仪表盘</h2>
      <div class="text-muted mt-1">
        欢迎使用企业外贸站 CMS 后台。你可以在这里快速进入产品分类、产品、FAQ、留言等管理页面。
      </div>
    </div>
  </div>
</div>

<div class="row mt-3">

  <!-- 产品分类卡片 -->
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <span class="avatar bg-primary-lt me-3">
            <i class="bi bi-list-ul"></i>
          </span>
          <div class="flex-fill">
            <div class="card-title">产品分类</div>
            <div class="text-lg fw-bold"><?= (int)$category_count ?></div>
            <div class="text-muted small">当前已创建的产品分类数量</div>
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <a href="categories.php" class="btn btn-outline-primary btn-sm">
          进入分类管理
        </a>
      </div>
    </div>
  </div>

  <!-- 产品卡片 -->
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <span class="avatar bg-green-lt me-3">
            <i class="bi bi-box-seam"></i>
          </span>
          <div class="flex-fill">
            <div class="card-title">产品列表</div>
            <div class="text-lg fw-bold"><?= (int)$product_count ?></div>
            <div class="text-muted small">所有已录入的产品数量</div>
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <a href="products.php" class="btn btn-outline-success btn-sm">
          进入产品管理
        </a>
      </div>
    </div>
  </div>

  <!-- FAQ 卡片 -->
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <span class="avatar bg-orange-lt me-3">
            <i class="bi bi-question-circle"></i>
          </span>
          <div class="flex-fill">
            <div class="card-title">FAQ 常见问题</div>
            <div class="text-lg fw-bold"><?= (int)$faq_count ?></div>
            <div class="text-muted small">当前展示在前台的 FAQ 条目</div>
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <a href="faqs.php" class="btn btn-outline-warning btn-sm">
          进入 FAQ 管理
        </a>
      </div>
    </div>
  </div>

  <!-- 页脚留言卡片 -->
  <div class="col-md-3 col-sm-6 mb-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <span class="avatar bg-purple-lt me-3">
            <i class="bi bi-chat-dots"></i>
          </span>
          <div class="flex-fill">
            <div class="card-title">访客留言</div>
            <div class="text-lg fw-bold"><?= (int)$message_count ?></div>
            <div class="text-muted small">通过网站底部表单提交的留言</div>
          </div>
        </div>
      </div>
      <div class="card-footer text-end">
        <a href="messages.php" class="btn btn-outline-purple btn-sm">
          查看留言
        </a>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
