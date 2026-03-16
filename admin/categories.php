<?php
require_once __DIR__ . '/admin_header.php';

// 处理新增 / 编辑
$message = '';
$error   = '';

$id          = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing_row = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM product_categories WHERE id = ?");
    $stmt->execute([$id]);
    $editing_row = $stmt->fetch();
    if (!$editing_row) {
        $error = '要编辑的分类不存在';
        $id = 0;
    }
}

// 删除
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    // 简单处理：如果有产品关联，这里会因外键限制失败，你可以后续加更友好的提示
    $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
    try {
        $stmt->execute([$del_id]);
        $message = '删除成功';
    } catch (Exception $e) {
        $error = '删除失败：可能有产品正在使用该分类';
    }
}

// 保存（新增或编辑）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id     = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '' || $slug === '') {
        $error = '名称和别名(slug)不能为空';
    } else {
        if ($post_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE product_categories
                SET name = :name, slug = :slug, description = :description,
                    sort_order = :sort_order, is_active = :is_active
                WHERE id = :id
            ");
            $stmt->execute([
                ':name'        => $name,
                ':slug'        => $slug,
                ':description' => $description,
                ':sort_order'  => $sort_order,
                ':is_active'   => $is_active,
                ':id'          => $post_id,
            ]);
            $message = '分类已更新';
            redirect('categories.php'); // 防止重复提交
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO product_categories (name, slug, description, sort_order, is_active)
                VALUES (:name, :slug, :description, :sort_order, :is_active)
            ");
            $stmt->execute([
                ':name'        => $name,
                ':slug'        => $slug,
                ':description' => $description,
                ':sort_order'  => $sort_order,
                ':is_active'   => $is_active,
            ]);
            $message = '分类已添加';
            redirect('categories.php');
        }
    }
}

// 重新读取列表
$stmt = $pdo->query("SELECT * FROM product_categories ORDER BY sort_order ASC, id ASC");
$rows = $stmt->fetchAll();
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">产品分类管理</h2>
      <div class="text-muted mt-1">
        管理产品的上层分类信息，用于前台产品分类页和菜单展示。
      </div>
    </div>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-success mt-3">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger mt-3">
    <?= htmlspecialchars($error) ?>
  </div>
<?php endif; ?>

<div class="row mt-3">
  <!-- 左侧：新增 / 编辑表单 -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?= $id > 0 ? '编辑分类' : '新增分类' ?></h3>
      </div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="id" value="<?= $id > 0 ? (int)$editing_row['id'] : 0 ?>">

          <div class="mb-3">
            <label class="form-label">分类名称</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   value="<?= $id > 0 ? htmlspecialchars($editing_row['name']) : '' ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">分类别名（slug，用于 URL）</label>
            <input type="text"
                   name="slug"
                   class="form-control"
                   value="<?= $id > 0 ? htmlspecialchars($editing_row['slug']) : '' ?>">
            <div class="form-hint">
              建议使用小写英文、数字和短横线，方便 SEO 和访客记忆。
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">描述（可选）</label>
            <textarea name="description"
                      rows="3"
                      class="form-control"><?= $id > 0 ? htmlspecialchars($editing_row['description']) : '' ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">排序值（越小越靠前）</label>
            <input type="number"
                   name="sort_order"
                   class="form-control"
                   value="<?= $id > 0 ? (int)$editing_row['sort_order'] : 0 ?>">
          </div>

          <div class="mb-3">
            <label class="form-check">
              <input type="checkbox"
                     name="is_active"
                     class="form-check-input"
                <?= $id > 0 ? ($editing_row['is_active'] ? 'checked' : '') : 'checked' ?>>
              <span class="form-check-label">启用</span>
            </label>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
              <?= $id > 0 ? '保存修改' : '添加分类' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- 右侧：分类列表 -->
  <div class="col-lg-7 mt-3 mt-lg-0">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">分类列表</h3>
      </div>
      <div class="table-responsive">
        <table class="table card-table table-striped">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th>名称</th>
              <th>slug</th>
              <th style="width: 80px;">排序</th>
              <th style="width: 80px;">状态</th>
              <th style="width: 140px;">操作</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="6" class="text-center text-muted">暂无分类</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><code><?= htmlspecialchars($r['slug']) ?></code></td>
                <td><?= (int)$r['sort_order'] ?></td>
                <td>
                  <?php if ($r['is_active']): ?>
                    <span class="badge ">启用</span>
                  <?php else: ?>
                    <span class="badge ">停用</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="categories.php?edit=<?= (int)$r['id'] ?>"
                     class="btn btn-sm btn-outline-primary me-1">
                    编辑
                  </a>
                  <a href="categories.php?delete=<?= (int)$r['id'] ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('确定要删除该分类吗？');">
                    删除
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer small text-muted">
        提示：如分类下已有产品，删除时可能因外键关系失败，需要先调整产品分类。
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
