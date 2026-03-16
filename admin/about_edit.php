<?php
// /admin/about_edit.php
require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';

// 确保表里至少有一条 id=1 的记录
try {
    $pdo->exec("INSERT IGNORE INTO about_page (id, title, content)
                VALUES (1, '关于我们', '<p>请在后台填写公司介绍内容。</p>')");
} catch (Throwable $e) {
    // 如果表不存在，会报错，需要先建表
}

// 先读取一遍当前内容
$stmt = $pdo->prepare("SELECT * FROM about_page WHERE id = 1 LIMIT 1");
$stmt->execute();
$about = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$current_banner_image = $about['banner_image'] ?? '';

$message      = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title        = trim($_POST['title']        ?? '');
    $subtitle     = trim($_POST['subtitle']     ?? '');
    $banner_image = trim($_POST['banner_image'] ?? $current_banner_image);
    $content      = $_POST['content']           ?? '';
    $side_title   = trim($_POST['side_title']   ?? '');
    $side_content = $_POST['side_content']      ?? '';

    // 处理图片上传
    if (!empty($_FILES['banner_image_file']['name']) &&
        $_FILES['banner_image_file']['error'] === UPLOAD_ERR_OK) {

        $tmp_name  = $_FILES['banner_image_file']['tmp_name'];
        $orig_name = $_FILES['banner_image_file']['name'];

        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed_ext, true)) {
            $message      = '图片格式不支持，仅允许：jpg、jpeg、png、gif、webp。';
            $message_type = 'danger';
        } else {
            $upload_dir      = __DIR__ . '/../uploads/about';
            $upload_url_base = '/uploads/about';

            if (!is_dir($upload_dir)) {
                @mkdir($upload_dir, 0775, true);
            }

            $new_name  = 'about_banner_' . date('Ymd_His') . '_' . substr(sha1($orig_name . microtime(true)), 0, 6) . '.' . $ext;
            $dest_path = $upload_dir . '/' . $new_name;

            if (move_uploaded_file($tmp_name, $dest_path)) {
                $banner_image = $upload_url_base . '/' . $new_name;
            } else {
                $message      = '图片上传失败，请检查 uploads/about 目录权限。';
                $message_type = 'danger';
            }
        }
    }

    // 如果前面没有致命错误，更新数据库
    if ($message_type !== 'danger') {
        try {
            $stmt = $pdo->prepare("
                UPDATE about_page SET
                  title        = :title,
                  subtitle     = :subtitle,
                  banner_image = :banner_image,
                  content      = :content,
                  side_title   = :side_title,
                  side_content = :side_content
                WHERE id = 1
            ");
            $stmt->execute([
                ':title'        => $title ?: '关于我们',
                ':subtitle'     => $subtitle,
                ':banner_image' => $banner_image,
                ':content'      => $content,
                ':side_title'   => $side_title,
                ':side_content' => $side_content,
            ]);

            $message      = '保存成功！';
            $message_type = 'success';

        } catch (Throwable $e) {
            $message      = '保存失败：' . $e->getMessage();
            $message_type = 'danger';
        }
    }

    // 再读一次最新数据用于回显
    $stmt = $pdo->prepare("SELECT * FROM about_page WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $about = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

// 用读取到的数据填充表单
$title        = $about['title']        ?? '';
$subtitle     = $about['subtitle']     ?? '';
$banner_image = $about['banner_image'] ?? '';
$content      = $about['content']      ?? '';
$side_title   = $about['side_title']   ?? '';
$side_content = $about['side_content'] ?? '';

$admin_page_title = '关于我们编辑';
require __DIR__ . '/admin_header.php';
?>

<!-- 页面标题 -->
<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">关于我们页面内容</h2>
      <div class="text-muted mt-1">
        此处内容将显示在前台 <code>/about.php</code> 页面，可用于公司介绍、工厂介绍等。
      </div>
    </div>
    <div class="col-auto ms-auto">
      <a href="../about.php" target="_blank" class="btn btn-outline-primary">
        预览前台页面
      </a>
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

    <form method="post" enctype="multipart/form-data" id="about-form">
      <!-- 主要内容 -->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">主要内容</h3>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label">页面标题</label>
            <input type="text" name="title" class="form-control"
                   value="<?= htmlspecialchars($title) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">副标题（可选）</label>
            <input type="text" name="subtitle" class="form-control"
                   value="<?= htmlspecialchars($subtitle) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">顶部大图</label>
            <div class="row g-2">
              <div class="col-md-6">
                <input type="text" name="banner_image" class="form-control mb-2"
                       placeholder="可手动填写图片 URL，或使用下方上传"
                       value="<?= htmlspecialchars($banner_image) ?>">
                <input type="file" name="banner_image_file" class="form-control">
                <div class="form-hint">
                  支持 jpg / jpeg / png / gif / webp，推荐尺寸按前台模板比例准备。
                </div>
              </div>
              <div class="col-md-6">
                <?php if ($banner_image): ?>
                  <div class="border rounded p-1 bg-light-subtle">
                    <img src="<?= htmlspecialchars($banner_image) ?>" alt="预览图片" class="img-fluid rounded">
                  </div>
                <?php else: ?>
                  <div class="text-muted small">
                    目前尚未设置顶部大图。
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- 富文本编辑器：主体内容 -->
          <div class="mb-3">
            <label class="form-label d-flex justify-content-between align-items-center">
              <span>页面主体内容（富文本）</span>
              <span class="small text-muted">
                可使用工具栏按钮设置标题、加粗、列表等格式
              </span>
            </label>

            <!-- 简易工具栏（正文） -->
            <div class="btn-group mb-2" role="group" aria-label="编辑工具栏">
              <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                      data-cmd="bold" data-target="main">
                加粗
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                      data-cmd="insertUnorderedList" data-target="main">
                无序列表
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                      data-cmd="insertOrderedList" data-target="main">
                有序列表
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                      data-cmd="formatBlock" data-value="h3" data-target="main">
                标题 H3
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary"
                      data-cmd="formatBlock" data-value="p" data-target="main">
                正文
              </button>
            </div>

            <!-- 可编辑区域：正文 -->
            <div id="editor_content"
                 class="form-control"
                 contenteditable="true"
                 style="min-height: 260px;">
              <?= $content /* HTML 直接输出，由管理员维护 */ ?>
            </div>

            <!-- 隐藏 textarea：用于提交 HTML -->
            <textarea name="content" id="content_field" class="d-none"><?= htmlspecialchars($content) ?></textarea>
          </div>

        </div>
      </div>

      <!-- 右侧栏 -->
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">右侧栏内容（可选）</h3>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label">右侧栏标题</label>
            <input type="text" name="side_title" class="form-control"
                   value="<?= htmlspecialchars($side_title) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">右侧栏富文本内容</label>

            <!-- 工具栏：侧栏 -->
            <div class="btn-group mb-2" role="group" aria-label="侧栏编辑工具栏">
              <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                      data-cmd="bold" data-target="sidebar">
                加粗
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                      data-cmd="insertUnorderedList" data-target="sidebar">
                无序列表
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                      data-cmd="insertOrderedList" data-target="sidebar">
                有序列表
              </button>
            </div>

            <!-- 可编辑区域：侧栏 -->
            <div id="editor_side"
                 class="form-control"
                 contenteditable="true"
                 style="min-height: 160px;">
              <?= $side_content ?>
            </div>

            <textarea name="side_content" id="side_content_field" class="d-none"><?= htmlspecialchars($side_content) ?></textarea>
          </div>

        </div>
      </div>

      <!-- 底部按钮 -->
      <div class="card">
        <div class="card-body d-flex justify-content-between">
          <a href="index.php" class="btn btn-link">返回仪表盘</a>
          <button type="submit" class="btn btn-primary">
            保存修改
          </button>
        </div>
      </div>

    </form>

  </div>

  <!-- 右侧提示栏 -->
  <div class="col-lg-3">
    <div class="card">
      <div class="card-header"><h3 class="card-title">使用说明</h3></div>
      <div class="card-body small">
        <p>前台页面路径：<code>/about.php</code></p>
        <p>数据库表：<code>about_page</code></p>
        <p>图片上传目录：<code>/uploads/about/</code></p>
        <p>如保存长文本仍有问题，可检查：</p>
        <ul>
          <li>数据库字段类型是否为 <code>TEXT/MEDIUMTEXT/LONGTEXT</code></li>
          <li>PHP <code>post_max_size</code> / <code>upload_max_filesize</code></li>
        </ul>
      </div>
    </div>
  </div>

</div>

<!-- 富文本编辑器相关 JS -->
<script>
  (function() {
    const form        = document.getElementById('about-form');
    const mainEditor  = document.getElementById('editor_content');
    const mainField   = document.getElementById('content_field');
    const sideEditor  = document.getElementById('editor_side');
    const sideField   = document.getElementById('side_content_field');

    function execCommandOn(target, cmd, value) {
      const el = (target === 'sidebar') ? sideEditor : mainEditor;
      el.focus();
      document.execCommand(cmd, false, value || null);
    }

    // 正文按钮
    document.querySelectorAll('button[data-cmd][data-target="main"]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const cmd   = this.getAttribute('data-cmd');
        const value = this.getAttribute('data-value') || null;
        execCommandOn('main', cmd, value);
      });
    });

    // 侧栏按钮
    document.querySelectorAll('button[data-cmd][data-target="sidebar"]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const cmd   = this.getAttribute('data-cmd');
        const value = this.getAttribute('data-value') || null;
        execCommandOn('sidebar', cmd, value);
      });
    });

    // 提交前同步内容
    form.addEventListener('submit', function() {
      mainField.value = mainEditor.innerHTML;
      sideField.value = sideEditor.innerHTML;
    });
  })();
</script>

<?php
require __DIR__ . '/admin_footer.php';
