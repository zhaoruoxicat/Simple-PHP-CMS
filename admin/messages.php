<?php
// /admin/messages.php
require_once __DIR__ . '/admin_header.php';

// 简单权限控制如果你有的话，可以在 admin_header 里统一做，这里就不重复了

$message = '';
$error   = '';

// 删除单条
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM cms_messages WHERE id = ?");
        $stmt->execute([$del_id]);
        $message = '留言已删除';
    }
}

// 清空全部（可选功能）
if (isset($_GET['clear']) && $_GET['clear'] === 'all') {
    // 按需决定要不要真的全部删除
    $pdo->exec("TRUNCATE TABLE cms_messages");
    $message = '所有留言已清空';
}

// 分页参数（简单版）
$page     = max(1, (int)($_GET['page'] ?? 1));
$pageSize = 20;
$offset   = ($page - 1) * $pageSize;

// 统计总数
$totalStmt = $pdo->query("SELECT COUNT(*) FROM cms_messages");
$totalRows = (int)$totalStmt->fetchColumn();
$totalPages = ($totalRows > 0) ? (int)ceil($totalRows / $pageSize) : 1;

// 读取当前页数据
$stmt = $pdo->prepare("
    SELECT id, email, message, ip_address, user_agent, created_at, created_ts, timezone
    FROM cms_messages
    ORDER BY id DESC
    LIMIT :offset, :ps
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':ps', $pageSize, PDO::PARAM_INT);
$stmt->execute();
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 一个小工具函数：根据 created_ts + timezone 生成“用户当地时间 & 北京时间”字符串
function format_message_times(array $row): string
{
    $ts = (int)($row['created_ts'] ?? 0);
    if ($ts <= 0) {
        // 兼容旧数据：没有 created_ts 的，用 created_at 尝试转
        if (!empty($row['created_at'])) {
            $dt = new DateTime($row['created_at'], new DateTimeZone('UTC'));
            $ts = $dt->getTimestamp();
        } else {
            return '-';
        }
    }

    $tzStr = $row['timezone'] ?: 'UTC';

    // 用户当地时间
    try {
        $dtUser = new DateTime('@' . $ts);
        $dtUser->setTimezone(new DateTimeZone($tzStr));
        $userTimeStr = $dtUser->format('Y-m-d H:i:s');
    } catch (Throwable $e) {
        // 如果时区字符串非法，就退回 UTC
        $dtUser = new DateTime('@' . $ts);
        $dtUser->setTimezone(new DateTimeZone('UTC'));
        $userTimeStr = $dtUser->format('Y-m-d H:i:s') . ' (UTC)';
        $tzStr = 'UTC';
    }

    // 北京时间
    $dtBj = new DateTime('@' . $ts);
    $dtBj->setTimezone(new DateTimeZone('Asia/Shanghai'));
    $bjTimeStr = $dtBj->format('Y-m-d H:i:s');

    $html  = '用户当地时间：' . htmlspecialchars($userTimeStr) . ' (' . htmlspecialchars($tzStr) . ')<br>';
    $html .= '北京时间：' . htmlspecialchars($bjTimeStr);
    return $html;
}

?>
<h1>页脚留言管理</h1>

<?php if ($message): ?>
  <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="mb-3 d-flex justify-content-between align-items-center">
  <div class="text-muted">
    共 <?= (int)$totalRows ?> 条留言，当前第 <?= (int)$page ?>/<?= (int)$totalPages ?> 页
  </div>
  <div>
    <a href="?clear=all"
       class="btn btn-sm btn-danger"
       onclick="return confirm('确定要清空所有留言吗？此操作不可恢复。');">
      清空全部留言
    </a>
  </div>
</div>

<table class="table table-bordered table-striped table-sm">
  <thead>
    <tr>
      <th style="width:60px;">ID</th>
      <th style="width:200px;">邮箱</th>
      <th>留言内容</th>
      <th style="width:220px;">时间</th>
      <th style="width:180px;">IP / UA</th>
      <th style="width:80px;">操作</th>
    </tr>
  </thead>
  <tbody>
    <?php if (!$list): ?>
      <tr>
        <td colspan="6" class="text-center text-muted">暂无留言</td>
      </tr>
    <?php else: ?>
      <?php foreach ($list as $row): ?>
        <tr>
          <td><?= (int)$row['id'] ?></td>
          <td>
            <a href="mailto:<?= htmlspecialchars($row['email']) ?>">
              <?= htmlspecialchars($row['email']) ?>
            </a>
          </td>
          <td style="max-width:400px; white-space:pre-wrap; word-break:break-all;">
            <?= nl2br(htmlspecialchars($row['message'] ?? '')) ?>
          </td>
          <td class="small">
            <?= format_message_times($row) ?>
          </td>
          <td class="small" style="max-width:220px; word-break:break-all;">
            <?php if (!empty($row['ip_address'])): ?>
              IP：<?= htmlspecialchars($row['ip_address']) ?><br>
            <?php endif; ?>
            <?php if (!empty($row['user_agent'])): ?>
              UA：<?= htmlspecialchars($row['user_agent']) ?>
            <?php endif; ?>
          </td>
          <td>
            <a href="?delete=<?= (int)$row['id'] ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('确定要删除这条留言吗？');">
              删除
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<?php if ($totalPages > 1): ?>
  <nav aria-label="Page navigation">
    <ul class="pagination">
      <?php
      $baseUrl = strtok($_SERVER['REQUEST_URI'], '?');
      // 保持其它 GET 参数（如果有的话）
      $query   = $_GET;
      ?>
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <?php
        $query['page'] = max(1, $page - 1);
        ?>
        <a class="page-link" href="<?= htmlspecialchars($baseUrl . '?' . http_build_query($query)) ?>">«</a>
      </li>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php $query['page'] = $i; ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= htmlspecialchars($baseUrl . '?' . http_build_query($query)) ?>">
            <?= $i ?>
          </a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <?php
        $query['page'] = min($totalPages, $page + 1);
        ?>
        <a class="page-link" href="<?= htmlspecialchars($baseUrl . '?' . http_build_query($query)) ?>">»</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
