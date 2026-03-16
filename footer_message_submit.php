<?php
// /footer_message_submit.php
declare(strict_types=1);

require_once __DIR__ . '/core.php';

function safe_return_url(string $url): string {
    $url = trim($url);
    if ($url === '') return '/index.html#footer';

    // 只允许站内路径，防止开放跳转
    // 允许形如：/index.html 或 /products_list/Barbed.html?x=1#footer
    if ($url[0] !== '/') return '/index.html#footer';
    if (strpos($url, "\n") !== false || strpos($url, "\r") !== false) return '/index.html#footer';

    return $url;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['footer_message_form'])) {
    header('Location: /index.html#footer', true, 302);
    exit;
}

$email    = trim((string)($_POST['email'] ?? ''));
$message  = trim((string)($_POST['message'] ?? ''));
$timezone = trim((string)($_POST['timezone'] ?? ''));
$return   = safe_return_url((string)($_POST['return_url'] ?? ''));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // 回跳并带失败标记
    $sep = (strpos($return, '?') !== false) ? '&' : '?';
    header('Location: ' . $return . $sep . 'footer_msg=err#footer', true, 302);
    exit;
}

try {
    $created_ts = time();
    $stmt = $pdo->prepare("
        INSERT INTO cms_messages (email, message, ip_address, user_agent, created_at, created_ts, timezone)
        VALUES (:email, :message, :ip, :ua, NOW(), :ts, :tz)
    ");
    $stmt->execute([
        ':email'   => $email,
        ':message' => $message,
        ':ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        ':ua'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ':ts'      => $created_ts,
        ':tz'      => $timezone,
    ]);

    $sep = (strpos($return, '?') !== false) ? '&' : '?';
    header('Location: ' . $return . $sep . 'footer_msg=ok#footer', true, 302);
    exit;

} catch (Throwable $e) {
    $sep = (strpos($return, '?') !== false) ? '&' : '?';
    header('Location: ' . $return . $sep . 'footer_msg=err#footer', true, 302);
    exit;
}
