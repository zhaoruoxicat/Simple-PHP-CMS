<?php
// /admin/faqs.php

require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';

$message = '';
$error   = '';

$id      = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$current = null;

// 删除（优先处理，避免下面多做无用查询）
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$del_id]);
    redirect('faqs.php');
    exit;
}

// 如果是编辑，读出当前这条
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current) {
        $error = '要编辑的 FAQ 不存在';
        $id = 0;
    }
}

// 保存（新增 / 编辑）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id    = (int)($_POST['id'] ?? 0);
    $question   = trim($_POST['question'] ?? '');
    $answer     = trim($_POST['answer'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    if ($question === '' || $answer === '') {
        $error = '问题和答案不能为空';
    } else {
        if ($post_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE faqs
                SET question   = :question,
                    answer     = :answer,
                    sort_order = :sort_order,
                    is_active  = :is_active
                WHERE id = :id
            ");
            $stmt->execute([
                ':question'   => $question,
                ':answer'     => $answer,
                ':sort_order' => $sort_order,
                ':is_active'  => $is_active,
                ':id'         => $post_id,
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO faqs (question, answer, sort_order, is_active)
                VALUES (:question, :answer, :sort_order, :is_active)
            ");
            $stmt->execute([
                ':question'   => $question,
                ':answer'     => $answer,
                ':sort_order' => $sort_order,
                ':is_active'  => $is_active,
            ]);
        }
        redirect('faqs.php');
        exit;
    }
}

// 列表
$stmt = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC");
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 页面标题给 admin_header 用
$admin_page_title = 'FAQ 管理';
require_once __DIR__ . '/admin_header.php';
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">FAQ 管理</h2>
      <div class="text-muted mt-1">
        管理前台常见问题内容（FAQ），支持中英文问题与答案。
      </div>
    </div>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-success mt-3"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row mt-3">
  <div class="col-lg-5 mb-3">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?= $id > 0 ? '编辑 FAQ' : '新增 FAQ' ?></h3>
      </div>
      <form method="post">
        <div class="card-body">

          <input type="hidden" name="id"
                 value="<?= ($id > 0 && $current) ? (int)$current['id'] : 0 ?>">

          <div class="mb-3">
            <label class="form-label">问题（可写英文问题）</label>
            <input type="text"
                   name="question"
                   class="form-control"
                   value="<?= ($id > 0 && $current) ? htmlspecialchars($current['question']) : '' ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">答案（可写中英文说明）</label>
            <textarea name="answer"
                      rows="4"
                      class="form-control"><?= ($id > 0 && $current) ? htmlspecialchars($current['answer']) : '' ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">排序值（越小越靠前）</label>
            <input type="number"
                   name="sort_order"
                   class="form-control w-auto"
                   value="<?= ($id > 0 && $current) ? (int)$current['sort_order'] : 0 ?>">
          </div>

          <div class="mb-3">
            <label class="form-check">
              <input class="form-check-input"
                     type="checkbox"
                     name="is_active"
                     <?= ($id > 0 && $current) ? ($current['is_active'] ? 'checked' : '') : 'checked' ?>>
              <span class="form-check-label">启用（显示在前台）</span>
            </label>
          </div>

        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-primary">
            <?= $id > 0 ? '保存修改' : '添加 FAQ' ?>
          </button>
        </div>
      </form>
    </div>

  </div>

  <div class="col-lg-7 mb-3">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">FAQ 列表</h3>
      </div>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th>问题</th>
              <th style="width: 80px;">排序</th>
              <th style="width: 90px;">状态</th>
              <th style="width: 160px;">操作</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$list): ?>
            <tr>
              <td colspan="5" class="text-center text-muted">
                暂无 FAQ
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($list as $f): ?>
              <tr>
                <td><?= (int)$f['id'] ?></td>
                <td><?= htmlspecialchars($f['question']) ?></td>
                <td><?= (int)$f['sort_order'] ?></td>
                <td>
                  <?php if ($f['is_active']): ?>
                    <span class="badge">启用</span>
                  <?php else: ?>
                    <span class="badge">停用</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="faqs.php?edit=<?= (int)$f['id'] ?>" class="btn btn-sm btn-outline-secondary me-1">
                    编辑
                  </a>
                  <a href="faqs.php?delete=<?= (int)$f['id'] ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('确定要删除该 FAQ 吗？');">
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
