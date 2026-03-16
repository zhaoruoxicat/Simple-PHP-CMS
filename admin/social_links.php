<?php
// /admin/social_links.php
require_once __DIR__ . '/admin_header.php';

$fields = [
    'homepage_youtube'  => '首页 YouTube 视频链接',
    'social_x'          => 'X（原 Twitter）主页链接',
    'social_facebook'   => 'Facebook 页面链接',
    'social_instagram'  => 'Instagram 页面链接',
    'social_linkedin'   => 'LinkedIn 页面链接',
];

$message = '';
$error   = '';

// 保存提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($fields as $key => $label) {
            $value = trim($_POST[$key] ?? '');

            $stmt = $pdo->prepare("
                INSERT INTO cms_settings (setting_key, setting_value)
                VALUES (:k, :v)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([
                ':k' => $key,
                ':v' => $value,
            ]);
        }
        $message = '保存成功';
    } catch (Throwable $e) {
        $error = '保存失败：' . $e->getMessage();
    }
}
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">网络平台信息</h2>
      <div class="text-muted mt-1">
        在这里统一管理首页 YouTube 视频链接，以及 X / Facebook / Instagram / LinkedIn 等社交媒体链接。
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
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">链接设置</h3>
      </div>
      <div class="card-body">
        <form method="post">

          <!-- 首页 YouTube 视频 -->
          <div class="mb-3">
            <label class="form-label">首页 YouTube 视频链接</label>
            <input type="text"
                   name="homepage_youtube"
                   class="form-control"
                   placeholder="例如：https://www.youtube.com/watch?v=xxxxxx"
                   value="<?= htmlspecialchars(get_setting('homepage_youtube', '')) ?>">
            <div class="form-hint">
              建议填写完整视频链接，后续首页 About 模块会从这里读取并嵌入播放器。
            </div>
          </div>

          <!-- X -->
          <div class="mb-3">
            <label class="form-label">X（原 Twitter）主页链接</label>
            <input type="text"
                   name="social_x"
                   class="form-control"
                   placeholder="例如：https://x.com/youraccount"
                   value="<?= htmlspecialchars(get_setting('social_x', '')) ?>">
          </div>

          <!-- Facebook -->
          <div class="mb-3">
            <label class="form-label">Facebook 页面链接</label>
            <input type="text"
                   name="social_facebook"
                   class="form-control"
                   placeholder="例如：https://www.facebook.com/yourpage"
                   value="<?= htmlspecialchars(get_setting('social_facebook', '')) ?>">
          </div>

          <!-- Instagram -->
          <div class="mb-3">
            <label class="form-label">Instagram 页面链接</label>
            <input type="text"
                   name="social_instagram"
                   class="form-control"
                   placeholder="例如：https://www.instagram.com/youraccount"
                   value="<?= htmlspecialchars(get_setting('social_instagram', '')) ?>">
          </div>

          <!-- LinkedIn -->
          <div class="mb-3">
            <label class="form-label">LinkedIn 公司主页链接</label>
            <input type="text"
                   name="social_linkedin"
                   class="form-control"
                   placeholder="例如：https://www.linkedin.com/company/yourcompany"
                   value="<?= htmlspecialchars(get_setting('social_linkedin', '')) ?>">
          </div>

          <div class="mt-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
              保存
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mt-3 mt-lg-0">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">使用说明</h3>
      </div>
      <div class="card-body small">
        <p>· 本页的数据会存入 <code>cms_settings</code> 表中。</p>
        <p>· 前台模板可通过 <code>get_setting('homepage_youtube')</code> 等函数读取。</p>
        <p>· 暂时不填的链接可以留空，前端可做「判断为空则不显示图标」。</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
