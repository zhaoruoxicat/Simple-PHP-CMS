<?php
// ------------------------------------------------------
// core.php - 全站公共核心文件
// ------------------------------------------------------

// ========== 基础配置 ==========
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms');
define('DB_USER', 'cms');
define('DB_PASS', 'zh6fHC84RcYTBCMZ');
define('DB_CHARSET', 'utf8mb4');

// 统一启用示例占位信息，避免前后台继续展示真实企业资料。
define('CMS_USE_PLACEHOLDER_BRANDING', true);

// ========== 初始化 ==========
session_start();
date_default_timezone_set('Asia/Shanghai');

// ========== 数据库连接（PDO） ==========
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('数据库连接失败，请联系管理员。');
}

// ========== 通用函数 ==========

// 从 cms_settings 读取配置，带静态缓存。
function get_setting(string $key, string $default = ''): string
{
    global $pdo;
    static $cache = [];

    if (isset($cache[$key])) {
        return $cache[$key];
    }

    $stmt = $pdo->prepare("SELECT setting_value FROM cms_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();

    if ($value === false) {
        $value = $default;
    }

    return $cache[$key] = $value;
}

function cms_placeholder_setting(string $key): ?string
{
    $year = date('Y');

    $placeholders = [
        'company_name'             => '示例企业名称',
        'company_phone'            => '000-0000-0000',
        'company_email'            => 'demo@example.com',
        'company_address'          => "示例地址信息\n示例城市 / 示例地区",
        'company_whatsapp'         => 'WhatsApp 示例账号',
        'company_wechat'           => 'WeChat 示例账号',
        'footer_copyright'         => "© {$year} 示例企业名称. All Rights Reserved.",
        'contact_title'            => '联系我们',
        'contact_intro'            => '这里是联系页面的通用占位说明文本，可在后续替换为正式内容。',
        'contact_extra'            => '这里是附加说明占位文本，可用于营业时间、回复时效或服务说明。',
        'contact_map'              => '<div class="ratio ratio-16x9 rounded border bg-light d-flex align-items-center justify-content-center text-muted">地图示例占位区域</div>',
        'social_x'                 => 'https://example.com/x',
        'social_facebook'          => 'https://example.com/facebook',
        'social_instagram'         => 'https://example.com/instagram',
        'social_linkedin'          => 'https://example.com/linkedin',
        'homepage_youtube'         => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
        'seo_default_title'        => '示例企业网站',
        'seo_default_keywords'     => 'sample company, placeholder website, demo content',
        'seo_default_description'  => '这是一个用于演示的企业网站内容占位描述。',
    ];

    return $placeholders[$key] ?? null;
}

function display_setting(string $key, string $default = ''): string
{
    if (CMS_USE_PLACEHOLDER_BRANDING) {
        $placeholder = cms_placeholder_setting($key);
        if ($placeholder !== null) {
            return $placeholder;
        }
    }

    return get_setting($key, $default);
}

function cms_placeholder_about_page(): array
{
    return [
        'title'        => '关于我们',
        'subtitle'     => '示例内容占位副标题',
        'banner_image' => '/includes/assets/img/blog/blog-1.jpg',
        'content'      => '<p>这里是通用的页面介绍占位文本，用于展示当前模板的排版结构与内容区域。后续可以替换为正式的品牌介绍、服务概述或项目背景说明。</p><p>这里是第二段通用占位文本，可用于补充团队能力、服务流程、交付方式或其他希望展示的页面信息。</p>',
        'side_title'   => '页面说明',
        'side_content' => '<p>这里是侧边栏占位内容，可放置简短说明、服务摘要、常见提示或其他辅助信息。</p>',
    ];
}

// 后台登录判断
function is_logged_in(): bool
{
    return !empty($_SESSION['cms_user_id']);
}

// 当前登录用户名
function current_username(): string
{
    return $_SESSION['cms_username'] ?? '';
}

// 安全重定向
function redirect(string $url)
{
    header("Location: $url");
    exit;
}
