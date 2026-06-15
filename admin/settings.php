<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    foreach (['site_name','primary_color','secondary_color','footer_text',
              'meta_title','meta_description','meta_keywords'] as $f) {
        save_setting($f, trim($_POST[$f] ?? ''), $tid);
    }
    foreach (['logo' => 'logos', 'favicon' => 'logos', 'og_image' => 'logos'] as $field => $dir) {
        if (!empty($_FILES[$field]['name'])) {
            if ($p = upload_image($_FILES[$field], $dir)) {
                save_setting($field, $p, $tid);
            } else {
                flash('admin_err', 'One of the uploaded files was invalid and skipped.');
            }
        }
    }
    flash('admin_ok', 'Settings saved. Changes are live on the website.');
    redirect('settings.php');
}

$active = 'settings';
$page_heading = 'Theme & Settings';
include __DIR__ . '/inc/header.php';
?>
<form method="post" enctype="multipart/form-data" class="card shadow-sm">
  <div class="card-body">
    <?= csrf_field() ?>
    <h5 class="mb-3">Branding</h5>
    <div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">Website Name</label>
        <input class="form-control" name="site_name" value="<?= e(setting('site_name','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">Footer Text</label>
        <input class="form-control" name="footer_text" value="<?= e(setting('footer_text','',$tid)) ?>"></div>
      <div class="col-md-3"><label class="form-label">Primary Color</label>
        <input type="color" class="form-control form-control-color w-100" name="primary_color"
               value="<?= e(setting('primary_color','#0d6efd',$tid)) ?>"></div>
      <div class="col-md-3"><label class="form-label">Secondary Color</label>
        <input type="color" class="form-control form-control-color w-100" name="secondary_color"
               value="<?= e(setting('secondary_color','#6610f2',$tid)) ?>"></div>
      <div class="col-md-3"><label class="form-label">Logo</label>
        <input type="file" class="form-control" name="logo" accept="image/*">
        <?php if (setting('logo','',$tid)): ?><img src="../<?= e(setting('logo','',$tid)) ?>" height="40" class="mt-2 bg-dark rounded p-1"><?php endif; ?>
      </div>
      <div class="col-md-3"><label class="form-label">Favicon</label>
        <input type="file" class="form-control" name="favicon" accept="image/*,.ico">
        <?php if (setting('favicon','',$tid)): ?><img src="../<?= e(setting('favicon','',$tid)) ?>" height="32" class="mt-2"><?php endif; ?>
      </div>
    </div>

    <h5 class="mb-3">SEO</h5>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Meta Title</label>
        <input class="form-control" name="meta_title" value="<?= e(setting('meta_title','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">Meta Keywords</label>
        <input class="form-control" name="meta_keywords" value="<?= e(setting('meta_keywords','',$tid)) ?>"></div>
      <div class="col-12"><label class="form-label">Meta Description</label>
        <textarea class="form-control" name="meta_description" rows="2"><?= e(setting('meta_description','',$tid)) ?></textarea></div>
      <div class="col-md-6"><label class="form-label">Open Graph / Social Share Image</label>
        <input type="file" class="form-control" name="og_image" accept="image/*">
        <?php if (setting('og_image','',$tid)): ?><img src="../<?= e(setting('og_image','',$tid)) ?>" height="48" class="mt-2 rounded"><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Settings</button></div>
</form>
<?php include __DIR__ . '/inc/footer.php'; ?>
