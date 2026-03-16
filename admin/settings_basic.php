<?php
// /admin/settings_basic.php

require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';

/**
 * 需要在 cms_settings 中维护的键：
 *  - company_name
 *  - company_phone
 *  - company_email
 *  - company_address
 *  - company_whatsapp
 *  - company_wechat
 *  - footer_copyright
 *  - contact_map
 *  - seo_default_title          ← 新字段
 *  - seo_default_keywords       ← 新字段
 *  - seo_default_description    ← 新字段
 */

$fields = [
    'company_name'          => '公司名称（中英文）',
    'company_phone'         => '联系电话',
    'company_email'         => '邮箱',
    'company_address'       => '地址',
    'company_whatsapp'      => 'WhatsApp',
    'company_wechat'        => '微信 / WeChat',
    'footer_copyright'      => '页脚版权文字',

    // 地图
    'contact_map'           => 'Google Map / 地图嵌入代码（iframe）',

    // SEO 默认字段（你指定的名称）
    'seo_default_title'       => '默认 SEO 标题（可选）',
    'seo_default_keywords'    => '默认 SEO 关键词（英文逗号分隔）',
    'seo_default_description' => '默认 SEO 描述（description）',
];

$message      = '';
$message_type = 'success';

// 保存提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($fields as $key => $label) {
            $value = $_POST[$key] ?? '';
            $value = trim($value);

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

        $message      = '保存成功';
        $message_type = 'success';

    } catch (Throwable $e) {
        $message      = '保存失败：' . $e->getMessage();
        $message_type = 'danger';
    }
}

// 读取当前值
$values = [];
foreach ($fields as $key => $label) {
    $values[$key] = get_setting($key, '');
}

// 页面标题
$admin_page_title = '基础信息设置';

require_once __DIR__ . '/admin_header.php';
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">基础信息设置</h2>
      <div class="text-muted mt-1">
        设置公司信息、地图嵌入代码及默认 SEO 信息。
      </div>
    </div>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?= htmlspecialchars($message_type) ?> mt-3">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<div class="row mt-3">
  <div class="col-lg-9">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">公司信息 & SEO 设置</h3>
      </div>

      <form method="post">
        <div class="card-body">

          <?php foreach ($fields as $key => $label): ?>
            <div class="mb-3">
              <label class="form-label"><?= htmlspecialchars($label) ?></label>

              <?php
              // textarea 类型字段
              $textarea_fields = [
                  'company_address',
                  'footer_copyright',
                  'contact_map',
                  'seo_default_description',
              ];
              ?>

              <?php if (in_array($key, $textarea_fields, true)): ?>
                <textarea
                  name="<?= htmlspecialchars($key) ?>"
                  class="form-control"
                  rows="<?= ($key === 'contact_map' ? 5 : 3) ?>"
                ><?= htmlspecialchars($values[$key]) ?></textarea>

                <?php if ($key === 'contact_map'): ?>
                  <div class="form-hint">
                    粘贴 Google Maps 提供的嵌入 iframe 代码。
                  </div>
                <?php endif; ?>

                <?php if ($key === 'seo_default_description'): ?>
                  <div class="form-hint">
                    建议填写 1–2 句描述，用于没有单独 SEO 的页面。
                  </div>
                <?php endif; ?>

              <?php else: ?>
                <input
                  type="text"
                  name="<?= htmlspecialchars($key) ?>"
                  class="form-control"
                  value="<?= htmlspecialchars($values[$key]) ?>"
                >

                <?php if ($key === 'seo_default_keywords'): ?>
                  <div class="form-hint">
                    英文逗号分隔，例如：<code>keywords,keywords</code>
                  </div>
                <?php endif; ?>

              <?php endif; ?>
            </div>
          <?php endforeach; ?>

        </div>

        <div class="card-footer text-end">
          <button type="submit" class="btn btn-primary">保存</button>
        </div>
      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
