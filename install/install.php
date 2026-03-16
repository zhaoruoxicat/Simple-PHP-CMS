<?php
// 安装锁
if (file_exists(__DIR__ . '/../install.lock')) {
    die('系统已经安装，如需重新安装请删除 install.lock');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $db_host = trim($_POST['db_host']);
    $db_name = trim($_POST['db_name']);
    $db_user = trim($_POST['db_user']);
    $db_pass = trim($_POST['db_pass']);

    $admin_user = trim($_POST['admin_user']);
    $admin_pass = trim($_POST['admin_pass']);

    try {

        // 测试数据库连接
        $pdo = new PDO(
            "mysql:host=$db_host;charset=utf8mb4",
            $db_user,
            $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // 创建数据库
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $pdo->exec("USE `$db_name`");

        // 导入SQL
        $sql = file_get_contents(__DIR__ . '/install.sql');
        $pdo->exec($sql);

        // 创建管理员
        $password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO cms_users (username,password_hash,is_admin)
            VALUES (?,?,1)
        ");

        $stmt->execute([$admin_user, $password_hash]);

        // 写入 core.php
        $core_path = __DIR__ . '/../core.php';

        $core = file_get_contents($core_path);

        $core = preg_replace("/define\('DB_HOST'.*?\);/", "define('DB_HOST', '$db_host');", $core);
        $core = preg_replace("/define\('DB_NAME'.*?\);/", "define('DB_NAME', '$db_name');", $core);
        $core = preg_replace("/define\('DB_USER'.*?\);/", "define('DB_USER', '$db_user');", $core);
        $core = preg_replace("/define\('DB_PASS'.*?\);/", "define('DB_PASS', '$db_pass');", $core);

        file_put_contents($core_path, $core);

        // 创建安装锁
        file_put_contents(__DIR__ . '/../install.lock', 'installed');

        $success = "安装完成！请删除 install 目录。";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>CMS 安装</title>
<style>
body{font-family:Arial;background:#f5f5f5}
.container{width:500px;margin:60px auto;background:#fff;padding:30px;border-radius:6px}
input{width:100%;padding:10px;margin:8px 0}
button{padding:10px 20px}
.error{color:red}
.success{color:green}
</style>
</head>
<body>

<div class="container">

<h2>CMS 安装程序</h2>

<?php if($error): ?>
<p class="error"><?=htmlspecialchars($error)?></p>
<?php endif; ?>

<?php if($success): ?>
<p class="success"><?=$success?></p>
<?php endif; ?>

<form method="post">

<h3>数据库配置</h3>

<input name="db_host" placeholder="数据库地址 localhost" required>
<input name="db_name" placeholder="数据库名" required>
<input name="db_user" placeholder="数据库用户名" required>
<input name="db_pass" placeholder="数据库密码">

<h3>管理员账号</h3>

<input name="admin_user" placeholder="管理员用户名" required>
<input name="admin_pass" placeholder="管理员密码" required>

<br><br>

<button type="submit">开始安装</button>

</form>

</div>

</body>
</html>