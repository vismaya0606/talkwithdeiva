<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$tid = admin_tenant_id();

$fields = [
  'hero_title','hero_subtitle','hero_tagline','about_title','about_content',
  'achievements','contact_phone','contact_whatsapp','contact_email','contact_address',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    foreach ($fields as $f) {
        save_setting($f, trim($_POST[$f] ?? ''), $tid);
    }
    if (!empty($_FILES['hero_image']['name'])) {
        if ($p = upload_image($_FILES['hero_image'], 'logos')) {
            save_setting('hero_image', $p, $tid);
        }
    }
    flash('admin_ok', 'Homepage content updated.');
    redirect('homepage.php');
}

$active = 'homepage';
$page_heading = 'Homepage Management';
include __DIR__ . '/inc/header.php';
?>
<form method="post" enctype="multipart/form-data" class="card shadow-sm">
  <div class="card-body">
    <?= csrf_field() ?>
    <h5 class="mb-3">Hero Banner</h5>
    <div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">Hero Title</label>
        <input class="form-control" name="hero_title" value="<?= e(setting('hero_title','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">Hero Subtitle / Designation</label>
        <input class="form-control" name="hero_subtitle" value="<?= e(setting('hero_subtitle','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">Tagline</label>
        <input class="form-control" name="hero_tagline" value="<?= e(setting('hero_tagline','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">Hero / Profile Image</label>
        <input type="file" class="form-control" name="hero_image" accept="image/*">
        <?php if (setting('hero_image','',$tid)): ?>
          <img src="../<?= e(setting('hero_image','',$tid)) ?>" height="60" class="mt-2 rounded">
        <?php endif; ?>
      </div>
    </div>

    <h5 class="mb-3">About Section</h5>
    <div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">About Title</label>
        <input class="form-control" name="about_title" value="<?= e(setting('about_title','',$tid)) ?>"></div>
      <div class="col-12"><label class="form-label">About Content</label>
        <textarea class="form-control" name="about_content" rows="4"><?= e(setting('about_content','',$tid)) ?></textarea></div>
      <div class="col-12"><label class="form-label">Achievements (one per line, e.g. "15+ Years Experience")</label>
        <textarea class="form-control" name="achievements" rows="3"><?= e(setting('achievements','',$tid)) ?></textarea></div>
    </div>

    <h5 class="mb-3">Contact Information</h5>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Phone</label>
        <input class="form-control" name="contact_phone" value="<?= e(setting('contact_phone','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">WhatsApp Number (digits incl. country code)</label>
        <input class="form-control" name="contact_whatsapp" value="<?= e(setting('contact_whatsapp','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">Email</label>
        <input class="form-control" name="contact_email" value="<?= e(setting('contact_email','',$tid)) ?>"></div>
      <div class="col-md-6"><label class="form-label">Address</label>
        <input class="form-control" name="contact_address" value="<?= e(setting('contact_address','',$tid)) ?>"></div>
    </div>
  </div>
  <div class="card-footer bg-white text-end"><button class="btn btn-primary">Save Changes</button></div>
</form>
<?php include __DIR__ . '/inc/footer.php'; ?>
