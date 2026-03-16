<?php
// admin/create_admin.php
// 临时创建管理员账号的页面，用完后务必删除本文件

require_once __DIR__ . '/../core.php';

$error   = '';
$message = '';

// 检查是否已有用户
$count = (int)$pdo->query("SELECT COUNT(*) FROM cms_users")->fetchColumn();
if ($count > 0) {
    $error = '当前 cms_users 表中已经存在用户（数量：' . $count . '）。为了安全，本页面已禁用，请删除此文件。';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username   = trim($_POST['username'] ?? '');
        $password   = $_POST['password'] ?? '';
        $password2  = $_POST['password2'] ?? '';

        if ($username === '' || $password === '' || $password2 === '') {
            $error = '用户名和密码不能为空';
        } elseif ($password !== $password2) {
            $error = '两次输入的密码不一致';
        } elseif (strlen($password) < 6) {
            $error = '为了安全起见，密码长度请至少 6 位';
        } else {
            // 检查用户名是否已存在（虽然当前表是空的，顺手写完整）
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM cms_users WHERE username = ?");
            $stmt->execute([$username]);
            if ((int)$stmt->fetchColumn() > 0) {
                $error = '该用户名已存在，请更换一个';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO cms_users (username, password_hash, is_admin)
                    VALUES (:u, :p, 1)
                ");
                $stmt->execute([
                    ':u' => $username,
                    ':p' => $hash,
                ]);
                $message = '管理员账号创建成功！请立即使用该账号登录后台，并尽快删除本文件：admin/create_admin.php';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>临时创建管理员账号</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; background:#f5f5f5; margin:0; }
        .box { max-width:420px; margin:60px auto; background:#fff; padding:20px; box-shadow:0 0 6px rgba(0,0,0,0.15); }
        h1 { font-size:20px; margin-top:0; text-align:center; }
        .form-row { margin-bottom:12px; }
        .form-row label { display:block; font-size:14px; margin-bottom:4px; }
        .form-row input[type="text"],
        .form-row input[type="password"] { width:100%; padding:6px; box-sizing:border-box; font-size:14px; }
        .btn { display:block; width:100%; padding:8px; background:#007bff; color:#fff; border:none; border-radius:3px; font-size:14px; cursor:pointer; }
        .btn:hover { background:#0069d9; }
        .alert { padding:8px 10px; border-radius:3px; font-size:13px; margin-bottom:10px; }
        .alert-error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .alert-ok { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .tip { font-size:12px; color:#888; margin-top:10px; line-height:1.5; }
        .strong { font-weight:bold; }
        .disabled-box { opacity:0.7; }
    </style>
</head>
<body>
<div class="box <?= $count > 0 ? 'disabled-box' : '' ?>">
    <h1>临时创建管理员账号</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="alert alert-ok"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($count === 0): ?>
        <form method="post">
            <div class="form-row">
                <label>用户名</label>
                <input type="text" name="username" autocomplete="off" required>
            </div>
            <div class="form-row">
                <label>密码</label>
                <input type="password" name="password" autocomplete="new-password" required>
            </div>
            <div class="form-row">
                <label>确认密码</label>
                <input type="password" name="password2" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn">创建管理员账号</button>
        </form>
        <div class="tip">
            提示：此页面仅用于初次安装时创建后台登录账号。<br>
            创建成功后，请立刻删除文件：<span class="strong">admin/create_admin.php</span>。
        </div>
    <?php else: ?>
        <div class="tip">
            检测到 <span class="strong"><?= $count ?></span> 个已有用户。<br>
            出于安全考虑，本页面已锁定不再允许创建新账号。<br>
            如需管理用户，请在后台增加“用户管理”功能；<br>
            目前请手动删除：<span class="strong">admin/create_admin.php</span>。
        </div>
    <?php endif; ?>
</div>
</body>
</html>
