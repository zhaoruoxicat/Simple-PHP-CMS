<?php
require_once __DIR__ . '/admin_header.php';

// 提示信息初始化
$message = '';
$error   = '';

/**
 * 从 Session / 全局变量中尽量获取当前登录用户信息
 *
 * 尝试顺序：
 *  1) $_SESSION['cms_user_id'] / $_SESSION['cms_username']
 *  2) $_SESSION['admin_id'] / $_SESSION['admin_username']
 *  3) $_SESSION['user_id'] / $_SESSION['username']
 *  4) 如果 admin_header.php 里有 $current_user，也尝试使用
 */

$login_id       = null;
$login_username = null;

// 1) 优先使用 cms_xxx
if (!empty($_SESSION['cms_user_id'])) {
    $login_id = (int)$_SESSION['cms_user_id'];
}
if (!empty($_SESSION['cms_username'])) {
    $login_username = trim((string)$_SESSION['cms_username']);
}

// 2) 兼容 admin_xxx
if ($login_id === null && !empty($_SESSION['admin_id'])) {
    $login_id = (int)$_SESSION['admin_id'];
}
if ($login_username === null && !empty($_SESSION['admin_username'])) {
    $login_username = trim((string)$_SESSION['admin_username']);
}

// 3) 再兼容 user_xxx / username
if ($login_id === null && !empty($_SESSION['user_id'])) {
    $login_id = (int)$_SESSION['user_id'];
}
if ($login_username === null && !empty($_SESSION['username'])) {
    $login_username = trim((string)$_SESSION['username']);
}

// 4) 如果 admin_header.php 里有定义 $current_user，也用上
if (isset($current_user) && is_array($current_user)) {
    if ($login_id === null && !empty($current_user['id'])) {
        $login_id = (int)$current_user['id'];
    }
    if ($login_username === null && !empty($current_user['username'])) {
        $login_username = trim((string)$current_user['username']);
    }
}

// 如果既没有 id，也没有用户名，就认为未登录（理论上应被 admin_header 拦截，但这里再兜底一次）
if ($login_id === null && $login_username === null) {
    $error = '未检测到登录信息，请先通过后台登录页面登录后再访问本页。';
} else {
    // 处理表单提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password     = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if ($current_password === '' || $new_password === '' || $confirm_password === '') {
            $error = '请完整填写当前密码、新密码和确认新密码。';
        } elseif ($new_password !== $confirm_password) {
            $error = '两次输入的新密码不一致。';
        } elseif (strlen($new_password) < 6) {
            $error = '新密码长度至少为 6 位。';
        } else {
            /**
             * 使用 cms_users 表：
             *   id, username, password_hash, is_admin, created_at
             */

            try {
                // 根据已有信息构造查询
                if ($login_id !== null) {
                    $stmt = $pdo->prepare("
                        SELECT id, username, password_hash, is_admin
                        FROM cms_users
                        WHERE id = :id
                        LIMIT 1
                    ");
                    $stmt->execute([':id' => $login_id]);
                } else {
                    // 退而用用户名查询
                    $stmt = $pdo->prepare("
                        SELECT id, username, password_hash, is_admin
                        FROM cms_users
                        WHERE username = :username
                        LIMIT 1
                    ");
                    $stmt->execute([':username' => $login_username]);
                }

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = '账号不存在或已被删除。';
                } else {
                    // 校验当前密码（password_hash 字段）
                    if (!password_verify($current_password, $user['password_hash'])) {
                        $error = '当前密码不正确。';
                    } else {
                        // 生成新哈希并更新
                        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                        $stmt2 = $pdo->prepare("
                            UPDATE cms_users
                            SET password_hash = :pwd
                            WHERE id = :id
                        ");
                        $stmt2->execute([
                            ':pwd' => $new_hash,
                            ':id'  => $user['id'],
                        ]);

                        $message = '密码已成功修改。';
                    }
                }
            } catch (Throwable $e) {
                // 为避免暴露细节，正式环境可以只给“系统错误”，开发时可临时打印 $e->getMessage()
                $error = '修改密码时发生错误，请稍后重试。';
                // 调试时可打开下一行：
                // $error .= ' 调试信息：' . $e->getMessage();
            }
        }
    }
}
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">修改密码</h2>
      <div class="text-muted mt-1">
        在这里修改当前登录账号的登录密码。
      </div>
    </div>
  </div>
</div>

<div class="row mt-3">
  <div class="col-lg-6">

    <?php if ($message !== ''): ?>
      <div class="alert alert-success alert-dismissible" role="alert">
        <div class="d-flex">
          <div><?= htmlspecialchars($message) ?></div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
      </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger alert-dismissible" role="alert">
        <div class="d-flex">
          <div><?= htmlspecialchars($error) ?></div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
      </div>
    <?php endif; ?>

    <?php if ($login_id === null && $login_username === null): ?>
      <div class="card">
        <div class="card-body">
          <p class="mb-0 text-danger">
            未检测到登录信息，请先通过后台登录页面登录后再访问本页。
          </p>
        </div>
      </div>
    <?php else: ?>

      <form method="post" class="card">
        <div class="card-header">
          <h3 class="card-title">修改登录密码</h3>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label">当前密码</label>
            <input type="password"
                   name="current_password"
                   class="form-control"
                   autocomplete="current-password"
                   required>
          </div>

          <div class="mb-3">
            <label class="form-label">新密码</label>
            <input type="password"
                   name="new_password"
                   class="form-control"
                   autocomplete="new-password"
                   required>
            <div class="form-hint">
              建议至少 6 位，包含字母与数字，避免使用过于简单的密码。
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">确认新密码</label>
            <input type="password"
                   name="confirm_password"
                   class="form-control"
                   autocomplete="new-password"
                   required>
          </div>

        </div>
        <div class="card-footer text-end">
          <button type="submit" class="btn btn-primary">
            保存新密码
          </button>
        </div>
      </form>

    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
