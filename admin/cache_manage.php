<?php
// /admin/cache_manage.php
declare(strict_types=1);

require_once __DIR__ . '/../core.php';
require_once __DIR__ . '/auth.php';

$admin_page_title = '缓存管理';

$cacheDir = realpath(__DIR__ . '/../cache'); // 指向网站根目录/cache
$message = '';
$messageType = 'success';

$deleted = 0;
$failed  = 0;
$skipped = 0;

function rrmdir_html_files(string $dir, array $extAllow, int &$deleted, int &$failed, int &$skipped): void
{
    if (!is_dir($dir)) return;

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($it as $file) {
        /** @var SplFileInfo $file */
        $path = $file->getPathname();

        // 只删文件，不删目录（目录最后按需清理空目录）
        if ($file->isFile()) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (!in_array($ext, $extAllow, true)) {
                $skipped++;
                continue;
            }

            if (@unlink($path)) {
                $deleted++;
            } else {
                $failed++;
            }
        } elseif ($file->isDir()) {
            // 清理空目录（可选）
            @rmdir($path);
        }
    }
}

$extAllow = ['html', 'htm']; // 只清理缓存静态页

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_cache') {
    $start = microtime(true);

    // 安全检查：必须存在且在站点目录下
    if ($cacheDir === false) {
        $message = 'cache 目录不存在（请先创建站点根目录下的 /cache 文件夹）。';
        $messageType = 'warning';
    } else {
        // 防止误删：确保路径确实以站点根目录开头
        $siteRoot = realpath(__DIR__ . '/..');
        if ($siteRoot === false || strncmp($cacheDir, $siteRoot, strlen($siteRoot)) !== 0) {
            $message = '安全校验失败：cache 目录路径异常，已阻止清理。';
            $messageType = 'danger';
        } else {
            rrmdir_html_files($cacheDir, $extAllow, $deleted, $failed, $skipped);

            $costMs = (int)round((microtime(true) - $start) * 1000);
            if ($failed > 0) {
                $message = "清理完成：已删除 {$deleted} 个缓存文件，失败 {$failed} 个，跳过 {$skipped} 个（非 html）。耗时 {$costMs}ms。请检查 cache 目录权限。";
                $messageType = 'warning';
            } else {
                $message = "清理完成：已删除 {$deleted} 个缓存文件，跳过 {$skipped} 个（非 html）。耗时 {$costMs}ms。";
                $messageType = 'success';
            }
        }
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title">缓存管理</h2>
      <div class="text-muted mt-1">
        一键清空站点 <code>/cache</code> 目录下的静态缓存文件（默认只删除 <code>.html/.htm</code>）。
      </div>
    </div>
  </div>
</div>

<?php if ($message): ?>
  <div class="alert alert-<?= htmlspecialchars($messageType) ?> mt-3">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<div class="row mt-3">
  <div class="col-lg-8">

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">清空缓存</h3>
      </div>

      <div class="card-body">
        <div class="mb-2">
          <div class="text-muted">
            当前缓存目录：
            <code><?= htmlspecialchars($cacheDir ?: (__DIR__ . '/../cache')) ?></code>
          </div>
        </div>

        <div class="alert alert-warning">
          <strong>注意：</strong>此操作会删除缓存目录中的静态页面缓存文件。删除后，前台第一次访问会重新走动态渲染并生成缓存。
        </div>

        <form method="post" onsubmit="return confirm('确定要清空缓存吗？此操作不可撤销。');">
          <input type="hidden" name="action" value="clear_cache">
          <button type="submit" class="btn btn-danger">
            🗑️ 一键清空缓存
          </button>
        </form>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
