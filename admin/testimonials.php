<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $desig= trim($_POST['designation'] ?? '');
        $text = trim($_POST['testimonial'] ?? '');
        $order= (int)($_POST['display_order'] ?? 0);

        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo = upload_image($_FILES['photo'], 'testimonials');
            if ($photo === null) { flash('admin_err', 'Invalid photo skipped.'); }
        }

        if ($name === '' || $text === '') {
            flash('admin_err', 'Name and testimonial text are required.');
        } elseif ($id > 0) {
            if ($photo) {
                $stmt = db()->prepare('UPDATE testimonials SET name=?,designation=?,testimonial=?,display_order=?,photo=? WHERE id=? AND tenant_id=?');
                $stmt->execute([$name,$desig,$text,$order,$photo,$id,$tid]);
            } else {
                $stmt = db()->prepare('UPDATE testimonials SET name=?,designation=?,testimonial=?,display_order=? WHERE id=? AND tenant_id=?');
                $stmt->execute([$name,$desig,$text,$order,$id,$tid]);
            }
            flash('admin_ok', 'Testimonial updated.');
        } else {
            $stmt = db()->prepare('INSERT INTO testimonials (tenant_id,name,designation,testimonial,photo,display_order) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$tid,$name,$desig,$text,$photo,$order]);
            flash('admin_ok', 'Testimonial added.');
        }
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM testimonials WHERE id=? AND tenant_id=?');
        $stmt->execute([(int)$_POST['id'], $tid]);
        flash('admin_ok', 'Testimonial deleted.');
    }
    redirect('testimonials.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM testimonials WHERE id=? AND tenant_id=?');
    $stmt->execute([(int)$_GET['edit'], $tid]);
    $edit = $stmt->fetch() ?: null;
}

$rows = db()->prepare('SELECT * FROM testimonials WHERE tenant_id=? ORDER BY display_order,id');
$rows->execute([$tid]);
$rows = $rows->fetchAll();

$active = 'testimonials';
$page_heading = 'Testimonials Management';
include __DIR__ . '/inc/header.php';
?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong><?= $edit ? 'Edit' : 'Add' ?> Testimonial</strong></div>
      <div class="card-body">
        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
          <div class="mb-3"><label class="form-label">Name</label>
            <input class="form-control" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Designation</label>
            <input class="form-control" name="designation" value="<?= e($edit['designation'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Testimonial</label>
            <textarea class="form-control" name="testimonial" rows="3" required><?= e($edit['testimonial'] ?? '') ?></textarea></div>
          <div class="mb-3"><label class="form-label">Profile Photo</label>
            <input type="file" class="form-control" name="photo" accept="image/*">
            <?php if (!empty($edit['photo'])): ?><img src="../<?= e($edit['photo']) ?>" height="48" class="mt-2 rounded-circle"><?php endif; ?></div>
          <div class="mb-3"><label class="form-label">Display Order</label>
            <input type="number" class="form-control" name="display_order" value="<?= e((string)($edit['display_order'] ?? 0)) ?>"></div>
          <button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Add' ?></button>
          <?php if ($edit): ?><a href="testimonials.php" class="btn btn-link w-100">Cancel</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light"><tr><th>Photo</th><th>Name</th><th>Testimonial</th><th>Order</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><img src="../<?= e($r['photo'] ?: '') ?>" onerror="this.style.display='none'" height="40" class="rounded-circle"></td>
              <td><?= e($r['name']) ?><br><small class="text-muted"><?= e($r['designation']) ?></small></td>
              <td class="small"><?= e(str_excerpt($r['testimonial'], 70)) ?></td>
              <td><?= (int)$r['display_order'] ?></td>
              <td class="text-end">
                <a href="?edit=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-4">No testimonials yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
