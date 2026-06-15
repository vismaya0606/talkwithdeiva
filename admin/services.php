<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $icon  = trim($_POST['icon'] ?? 'bi-star');
        $order = (int)($_POST['display_order'] ?? 0);

        if ($title === '') {
            flash('admin_err', 'Title is required.');
        } elseif ($id > 0) {
            $stmt = db()->prepare('UPDATE services SET title=?,description=?,icon=?,display_order=? WHERE id=? AND tenant_id=?');
            $stmt->execute([$title,$desc,$icon,$order,$id,$tid]);
            flash('admin_ok', 'Service updated.');
        } else {
            $stmt = db()->prepare('INSERT INTO services (tenant_id,title,description,icon,display_order) VALUES (?,?,?,?,?)');
            $stmt->execute([$tid,$title,$desc,$icon,$order]);
            flash('admin_ok', 'Service added.');
        }
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM services WHERE id=? AND tenant_id=?');
        $stmt->execute([(int)$_POST['id'], $tid]);
        flash('admin_ok', 'Service deleted.');
    }
    redirect('services.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM services WHERE id=? AND tenant_id=?');
    $stmt->execute([(int)$_GET['edit'], $tid]);
    $edit = $stmt->fetch() ?: null;
}

$rows = db()->prepare('SELECT * FROM services WHERE tenant_id=? ORDER BY display_order,id');
$rows->execute([$tid]);
$rows = $rows->fetchAll();

$active = 'services';
$page_heading = 'Services Management';
include __DIR__ . '/inc/header.php';
?>
<div class="row g-4">
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><strong><?= $edit ? 'Edit' : 'Add' ?> Service</strong></div>
      <div class="card-body">
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
          <div class="mb-3"><label class="form-label">Title</label>
            <input class="form-control" name="title" required value="<?= e($edit['title'] ?? '') ?>"></div>
          <div class="mb-3"><label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea></div>
          <div class="mb-3"><label class="form-label">Icon
              (<a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icon</a> class)</label>
            <input class="form-control" name="icon" value="<?= e($edit['icon'] ?? 'bi-star') ?>" placeholder="bi-star"></div>
          <div class="mb-3"><label class="form-label">Display Order</label>
            <input type="number" class="form-control" name="display_order" value="<?= e((string)($edit['display_order'] ?? 0)) ?>"></div>
          <button class="btn btn-primary w-100"><?= $edit ? 'Update' : 'Add' ?></button>
          <?php if ($edit): ?><a href="services.php" class="btn btn-link w-100">Cancel</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light"><tr><th>#</th><th>Icon</th><th>Title</th><th>Order</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><i class="bi <?= e($r['icon']) ?> fs-5"></i></td>
              <td><?= e($r['title']) ?></td>
              <td><?= (int)$r['display_order'] ?></td>
              <td class="text-end">
                <a href="?edit=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="post" class="d-inline" onsubmit="return confirm('Delete this service?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-4">No services yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>
