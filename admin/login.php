<?php
require_once __DIR__ . '/../core.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = '用户名和密码不能为空';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM cms_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['cms_user_id']  = $user['id'];
            $_SESSION['cms_username'] = $user['username'];
            redirect('index.php');
        } else {
            $error = '用户名或密码错误';
        }
    }
}

$site_name = get_setting('company_name', 'CMS 管理系统');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <title>登录 - <?= htmlspecialchars($site_name) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- 本地 Tabler 样式 -->
  <link href="tabler.min.css" rel="stylesheet" />

  <style>
    body {
      background: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    }

    .login-card {
      width: 360px;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.1);
      background: #fff;
    }

    .login-logo {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      background: linear-gradient(135deg, #ff7b00, #ff5e00);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: #fff;
      font-size: 20px;
      margin-right: 10px;
    }

    .btn-orange {
      background-color: #ff7b00;
      border: none;
      color: #fff;
    }
    .btn-orange:hover {
      background-color: #ff8f26;
    }
  </style>
</head>

<body>

<div class="card login-card p-4">

  <div class="d-flex align-items-center mb-3">
    <div class="login-logo">A</div>
    <div>
      <div class="small text-muted">Admin Panel</div>
      <h2 class="h4 m-0"><?= htmlspecialchars($site_name) ?></h2>
    </div>
  </div>

  <p class="text-muted mb-4">请输入您的后台账号和密码登录。</p>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">

    <div class="mb-3">
      <label class="form-label">用户名</label>
      <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" autofocus>
    </div>

    <div class="mb-3">
      <label class="form-label">密码</label>
      <input type="password" name="password" class="form-control">
    </div>

    <button type="submit" class="btn btn-orange w-100">
      登录
    </button>

  </form>

  <div class="text-center text-muted small mt-3">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($site_name) ?>
  </div>

</div>

</body>
</html>
