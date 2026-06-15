<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        if (!empty($_FILES['image']['name'])) {
            if ($p = upload_image($_FILES['image'], 'gallery')) {
                $stmt = db()->prepare('INSERT INTO gallery (tenant_id,image,caption,display_order) VALUES (?,?,?,?)');
                $stmt->execute([$tid, $p, trim($_POST['caption'] ?? ''), (int)($_POST['display_order'] ?? 0)]);
                flash('admin_ok', 'Image uploaded.');
            } else {
                flash('admin_err', 'Invalid image file (allowed: jpg, png, gif, webp; max 4MB).');
            }
        } else {
            flash('admin_err', 'Please choose an image to upload.');
        }
    } elseif ($action === 'order') {
        $stmt = db()->prepare('UPDATE gallery SET caption=?,display_order=? WHERE id=? AND tenant_id=?');
        $stmt->execute([trim($_POST['caption'] ?? ''),(int)$_POST['display_order'],(int)$_POST['id'],$tid]);
        flash('admin_ok', 'Image updated.');
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('SELECT image FROM gallery WHERE id=? AND tenant_id=?');
        $stmt->execute([(int)$_POST['id'], $tid]);
        if ($img = $stmt->fetchColumn()) {
            $file = __DIR__ . '/../' . $img;
            if (!preg_match('#^https?://#i', $img) && is_file($file)) { @unlink($file); }
        }
        $stmt = db()->prepare('DELETE FROM gallery WHERE id=? AND tenant_id=?');
        $stmt->execute([(int)$_POST['id'], $tid]);
        flash('admin_ok', 'Image deleted.');
    }
    redirect('gallery.php');
}

$rows = db()->prepare('SELECT * FROM gallery WHERE tenant_id=? ORDER BY display_order,id');
$rows->execute([$tid]);
$rows = $rows->fetchAll();

$active = 'gallery';
$page_heading = 'Gallery Management';
include __DIR__ . '/inc/header.php';
?>
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white"><strong>Upload Image</strong></div>
  <div class="card-body">
    <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="upload">
      <div class="col-md-5"><label class="form-label">Image</label>
        <input type="file" class="form-control" name="image" accept="image/*" required></div>
      <div class="col-md-4"><label class="form-label">Caption</label>
        <input class="form-control" name="caption"></div>
      <div class="col-md-2"><label class="form-label">Order</label>
        <input type="number" class="form-control" name="display_order" value="0"></div>
      <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bi bi-upload"></i></button></div>
    </form>
  </div>
</div>

<div class="row g-3">
  <?php foreach ($rows as $r): ?>
    <div class="col-sm-6 col-md-4 col-lg-3">
      <div class="card shadow-sm h-100">
        <img src="../<?= e($r['image']) ?>" class="card-img-top" style="height:160px;object-fit:cover"
             onerror="this.src='<?= e($r['image']) ?>'">
        <div class="card-body p-2">
          <form method="post" class="d-flex gap-1 mb-1">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="order">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input class="form-control form-control-sm" name="caption" value="<?= e($r['caption']) ?>" placeholder="Caption">
            <input type="number" class="form-control form-control-sm" style="max-width:70px" name="display_order" value="<?= (int)$r['display_order'] ?>">
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-save"></i></button>
          </form>
          <form method="post" onsubmit="return confirm('Delete image?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i> Delete</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?><p class="text-muted">No gallery images yet.</p><?php endif; ?>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
