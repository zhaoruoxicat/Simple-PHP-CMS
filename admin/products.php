<?php
// /admin/products.php

require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';

// 当前筛选的分类 ID（默认 0 = 不筛选）
$filter_category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// 读取全部启用的分类，用于筛选下拉框
$category_stmt = $pdo->query("
    SELECT id, name
    FROM product_categories
    WHERE is_active = 1
    ORDER BY sort_order ASC, id ASC
");
$categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

// 按条件查询产品及分类名称
$sql = "
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN product_categories c ON p.category_id = c.id
";
$params = [];

if ($filter_category_id > 0) {
    // 启用分类筛选
    $sql .= " WHERE p.category_id = :cid ";
    $params[':cid'] = $filter_category_id;
}

$sql .= " ORDER BY p.created_at DESC, p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 设置后台页面标题
$admin_page_title = '产品管理';

require_once __DIR__ . '/admin_header.php';
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">产品管理</h2>
      <div class="text-muted mt-1">
        管理前台展示的产品列表，包括名称、分类、主图、推荐状态以及启用状态。
      </div>
    </div>
    <div class="col-auto ms-auto">
      <a href="product_edit.php" class="btn btn-primary">
        新增产品
      </a>
    </div>
  </div>
</div>

<div class="row mt-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">产品列表</h3>
        <!-- 分类筛选（默认不过滤，只有选择具体分类才生效） -->
        <div class="ms-auto">
          <form method="get" class="row g-2 align-items-center">
            <div class="col-auto">
              <select name="category_id" class="form-select form-select-sm">
                <option value="0">全部分类</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>"
                    <?= $filter_category_id === (int)$cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-sm btn-outline-primary">
                筛选
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th>名称</th>
              <th>分类</th>
              <th style="width: 120px;">主图</th>
              <th style="width: 80px;">推荐</th>
              <th style="width: 80px;">状态</th>
              <th style="width: 200px;">操作</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$products): ?>
            <tr>
              <td colspan="7" class="text-center text-muted">
                暂无产品
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($products as $p): ?>
              <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= htmlspecialchars($p['category_name'] ?? '未分类') ?></td>
                <td>
                  <?php if (!empty($p['main_image'])): ?>
                    <img src="<?= htmlspecialchars($p['main_image']) ?>"
                         alt=""
                         style="height:40px;width:auto;"
                         class="border rounded">
                  <?php else: ?>
                    <span class="text-muted">无</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($p['is_featured'])): ?>
                    <span class="badge">是</span>
                  <?php else: ?>
                    <span class="badge">否</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($p['is_active'])): ?>
                    <span class="badge">启用</span>
                  <?php else: ?>
                    <span class="badge">停用</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="product_edit.php?id=<?= (int)$p['id'] ?>"
                     class="btn btn-sm btn-outline-secondary me-1">
                    编辑
                  </a>
                  <a href="product_edit.php?delete=<?= (int)$p['id'] ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('确定要删除该产品吗？');">
                    删除
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
