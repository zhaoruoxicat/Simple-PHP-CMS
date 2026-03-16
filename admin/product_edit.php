<?php
require_once __DIR__ . '/admin_header.php';

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error   = '';

// 删除产品
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$del_id]);
    redirect('products.php');
}

// 读取分类列表用于下拉选择
$stmt = $pdo->query("SELECT id, name FROM product_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
$categories = $stmt->fetchAll();

// 读取当前产品（增加 SEO 默认字段）
$product = [
    'id'              => 0,
    'category_id'     => 0,
    'name'            => '',
    'slug'            => '',
    'short_desc'      => '',
    'description'     => '',
    'main_image'      => '',
    'price_label'     => '',
    'is_featured'     => 0,
    'is_active'       => 1,
    'seo_keywords'    => '',
    'seo_description' => '',
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $product = $row;
    } else {
        $error = '产品不存在';
        $id = 0;
    }
}

// 删除单张图库图片（基于当前产品）
if (isset($_GET['delete_image']) && !empty($product['id'])) {
    $img_id = (int)$_GET['delete_image'];

    // 先查出图片，确保属于当前产品
    $stmt = $pdo->prepare("SELECT id, image_url FROM product_images WHERE id = ? AND product_id = ?");
    $stmt->execute([$img_id, $product['id']]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($img) {
        // 尝试删除物理文件（不强制）
        $file_path = __DIR__ . '/..' . $img['image_url']; // /uploads/xxx
        if (is_file($file_path)) {
            @unlink($file_path);
        }

        // 删数据库记录
        $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
        $stmt->execute([$img_id]);

        $message = '图片已删除';
    } else {
        $error = '图片不存在或不属于该产品';
    }
}

// 处理提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product['id']              = (int)($_POST['id'] ?? 0);
    $product['category_id']     = (int)($_POST['category_id'] ?? 0);
    $product['name']            = trim($_POST['name'] ?? '');
    $product['slug']            = trim($_POST['slug'] ?? '');
    $product['short_desc']      = trim($_POST['short_desc'] ?? '');
    $product['description']     = trim($_POST['description'] ?? '');
    $product['price_label']     = trim($_POST['price_label'] ?? '');
    $product['is_featured']     = isset($_POST['is_featured']) ? 1 : 0;
    $product['is_active']       = isset($_POST['is_active']) ? 1 : 0;
    // SEO 字段
    $product['seo_keywords']    = trim($_POST['seo_keywords'] ?? '');
    $product['seo_description'] = trim($_POST['seo_description'] ?? '');

    if ($product['name'] === '' || $product['slug'] === '' || $product['category_id'] === 0) {
        $error = '名称、slug 和分类不能为空';
    } else {
        // 处理主图上传（可选）
        if (!empty($_FILES['main_image']['name']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $error = '图片格式不支持';
            } else {
                $upload_dir  = __DIR__ . '/../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_name    = 'p_' . date('YmdHis') . '_' . mt_rand(1000,9999) . '.' . $ext;
                $target_path = $upload_dir . $new_name;
                if (move_uploaded_file($_FILES['main_image']['tmp_name'], $target_path)) {
                    // 前台访问路径
                    $product['main_image'] = '/uploads/products/' . $new_name;
                } else {
                    $error = '图片上传失败';
                }
            }
        }

        if ($error === '') {
            if ($product['id'] > 0) {
                $sql = "
                    UPDATE products
                    SET category_id     = :category_id,
                        name            = :name,
                        slug            = :slug,
                        short_desc      = :short_desc,
                        description     = :description,
                        main_image      = :main_image,
                        price_label     = :price_label,
                        is_featured     = :is_featured,
                        is_active       = :is_active,
                        seo_keywords    = :seo_keywords,
                        seo_description = :seo_description
                    WHERE id           = :id
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':category_id'     => $product['category_id'],
                    ':name'            => $product['name'],
                    ':slug'            => $product['slug'],
                    ':short_desc'      => $product['short_desc'],
                    ':description'     => $product['description'],
                    ':main_image'      => $product['main_image'],
                    ':price_label'     => $product['price_label'],
                    ':is_featured'     => $product['is_featured'],
                    ':is_active'       => $product['is_active'],
                    ':seo_keywords'    => $product['seo_keywords'],
                    ':seo_description' => $product['seo_description'],
                    ':id'              => $product['id'],
                ]);
                $message = '产品已更新';
            } else {
                $sql = "
                    INSERT INTO products
                    (category_id, name, slug, short_desc, description, main_image, price_label, is_featured, is_active, seo_keywords, seo_description)
                    VALUES
                    (:category_id, :name, :slug, :short_desc, :description, :main_image, :price_label, :is_featured, :is_active, :seo_keywords, :seo_description)
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':category_id'     => $product['category_id'],
                    ':name'            => $product['name'],
                    ':slug'            => $product['slug'],
                    ':short_desc'      => $product['short_desc'],
                    ':description'     => $product['description'],
                    ':main_image'      => $product['main_image'],
                    ':price_label'     => $product['price_label'],
                    ':is_featured'     => $product['is_featured'],
                    ':is_active'       => $product['is_active'],
                    ':seo_keywords'    => $product['seo_keywords'],
                    ':seo_description' => $product['seo_description'],
                ]);
                $product['id'] = (int)$pdo->lastInsertId();
                $message = '产品已添加';
                // 可选：redirect('products.php');
            }

            // 多图上传逻辑
            if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
                $upload_dir = __DIR__ . '/../uploads/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $url_prefix = '/uploads/products/';

                $names  = $_FILES['gallery_images']['name'];
                $tmps   = $_FILES['gallery_images']['tmp_name'];
                $errors = $_FILES['gallery_images']['error'];

                foreach ($names as $idx => $fileName) {
                    if ($fileName === '') {
                        continue;
                    }
                    if ($errors[$idx] !== UPLOAD_ERR_OK) {
                        continue;
                    }
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                        continue;
                    }

                    $new_name    = 'g_' . date('YmdHis') . '_' . mt_rand(1000,9999) . '.' . $ext;
                    $target_path = $upload_dir . $new_name;

                    if (move_uploaded_file($tmps[$idx], $target_path)) {
                        $image_url = $url_prefix . $new_name;

                        $stmtImg = $pdo->prepare("
                            INSERT INTO product_images (product_id, image_url, sort_order)
                            VALUES (:product_id, :image_url, :sort_order)
                        ");
                        $stmtImg->execute([
                            ':product_id' => $product['id'],
                            ':image_url'  => $image_url,
                            ':sort_order' => 0,
                        ]);
                    }
                }
            }
        }
    }
}

// 读取已上传的附加图片（用于缩略图显示）
$images = [];
if (!empty($product['id'])) {
    $stmt = $pdo->prepare("
        SELECT id, image_url, sort_order
        FROM product_images
        WHERE product_id = ?
        ORDER BY sort_order ASC, id ASC
    ");
    $stmt->execute([$product['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="page-header d-print-none">
  <div class="row align-items-center">
    <div class="col">
      <h2 class="page-title"><?= $product['id'] ? '编辑产品' : '新增产品' ?></h2>
      <div class="text-muted mt-1">
        设置产品的基本信息、图片以及 SEO 内容。
      </div>
    </div>
    <div class="col-auto ms-auto">
      <a href="products.php" class="btn btn-outline-secondary">
        返回产品列表
      </a>
    </div>
  </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success mt-3"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row mt-3">
  <div class="col-lg-8">

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">产品基本信息</h3>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label">分类</label>
            <select name="category_id" class="form-select">
              <option value="0">请选择分类</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">产品名称</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($product['name']) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">slug（URL 别名）</label>
            <input type="text" name="slug" class="form-control"
                   value="<?= htmlspecialchars($product['slug']) ?>">
            <div class="form-hint">
              建议使用小写英文、数字和短横线，便于 SEO 和客户记忆。
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">简短描述（列表使用，可中英文）</label>
            <textarea name="short_desc" rows="3" class="form-control"><?= htmlspecialchars($product['short_desc']) ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">详细描述（支持 HTML，可写参数表、图文等）</label>
            <textarea name="description" rows="8" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">价格说明（如 FOB Tianjin / As your request）</label>
            <input type="text" name="price_label" class="form-control"
                   value="<?= htmlspecialchars($product['price_label']) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">主图</label>
            <div class="row g-2">
              <div class="col-md-6">
                <?php if (!empty($product['main_image'])): ?>
                  <div class="border rounded p-1 bg-light-subtle mb-2">
                    <img src="<?= htmlspecialchars($product['main_image']) ?>" alt="" class="img-fluid rounded">
                  </div>
                  <div class="text-muted small mb-1">如不上传新图片，将保留现有图片。</div>
                <?php else: ?>
                  <div class="text-muted small mb-1">当前暂无主图。</div>
                <?php endif; ?>
                <input type="file" name="main_image" accept="image/*" class="form-control">
              </div>
            </div>
          </div>

          <!-- 多图上传 -->
          <div class="mb-3">
            <label class="form-label">产品图库（可多图上传）</label>
            <input type="file" name="gallery_images[]" accept="image/*" class="form-control" multiple>
            <div class="form-hint">
              可一次选择多张图片上传，作为产品详情中的附加图片。
            </div>
          </div>

          <?php if (!empty($images)): ?>
            <div class="mb-3">
              <label class="form-label">已添加图片</label>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach ($images as $img): ?>
                  <div class="border rounded p-1 bg-light-subtle me-2 mb-2" style="width:110px;">
                    <div style="width:100%;height:80px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                      <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="" class="img-fluid rounded" style="max-width:100%;max-height:100%;">
                    </div>
                    <div class="mt-1">
                      <a href="product_edit.php?id=<?= (int)$product['id'] ?>&delete_image=<?= (int)$img['id'] ?>"
                         class="btn btn-outline-danger btn-sm w-100"
                         onclick="return confirm('确定要删除这张图片吗？');">
                        删除
                      </a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-check mb-2">
              <input type="checkbox" name="is_featured" class="form-check-input"
                     <?= $product['is_featured'] ? 'checked' : '' ?>>
              <span class="form-check-label">首页推荐（Featured）</span>
            </label>

            <label class="form-check">
              <input type="checkbox" name="is_active" class="form-check-input"
                     <?= $product['is_active'] ? 'checked' : '' ?>>
              <span class="form-check-label">启用</span>
            </label>
          </div>

        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title">SEO 信息</h3>
        </div>
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label">SEO 关键词（Keywords）</label>
            <textarea name="seo_keywords" rows="2" class="form-control"
                      placeholder=""><?= htmlspecialchars($product['seo_keywords']) ?></textarea>
            <div class="form-hint">
              建议用英文逗号分隔多个关键词，供搜索引擎参考。
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">SEO 描述（Meta Description）</label>
            <textarea name="seo_description" rows="3" class="form-control"
                      placeholder="简单用英文描述该产品的主要用途和优势"><?= htmlspecialchars($product['seo_description']) ?></textarea>
            <div class="form-hint">
              通常 120–160 字符，简要概括产品卖点，用于搜索结果摘要。
            </div>
          </div>

        </div>
      </div>

      <div class="card">
        <div class="card-footer d-flex justify-content-between">
          <a href="products.php" class="btn btn-link">返回列表</a>
          <button type="submit" class="btn btn-primary">保存</button>
        </div>
      </div>

    </form>

  </div>

  <div class="col-lg-4 mt-3 mt-lg-0">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">提示</h3>
      </div>
      <div class="card-body small">
        <p>· 主图和图库上传目录：<code>/uploads/products/</code></p>
        <p>· 前台访问路径前缀：<code>/uploads/products/</code></p>
        <p>· 删除图库图片时会尝试删除物理文件，但不会影响主图。</p>
        <p>· SEO 字段请尽量使用英文，方便海外搜索引擎收录。</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
