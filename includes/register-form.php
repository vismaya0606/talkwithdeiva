<?php
/** Reusable registration form (used on home page + register.php). */
require_once __DIR__ . '/../config/functions.php';

$svc = db()->prepare('SELECT title FROM services WHERE tenant_id = ? ORDER BY display_order, id');
$svc->execute([tenant_id()]);
$svc = $svc->fetchAll(PDO::FETCH_COLUMN);

$ok    = flash('reg_success');
$err   = flash('reg_error');
$old   = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_old']);
?>
<div id="register-form" class="register-card p-4 p-md-5 bg-white rounded shadow-sm">
  <?php if ($ok): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i><?= e($ok) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endif; ?>

  <form action="<?= e(base_url()) ?>register.php" method="post" novalidate class="needs-validation row g-3">
    <?= csrf_field() ?>
    <div class="col-md-6">
      <label class="form-label">Full Name <span class="text-danger">*</span></label>
      <input type="text" name="full_name" class="form-control" required maxlength="150"
             value="<?= e($old['full_name'] ?? '') ?>">
      <div class="invalid-feedback">Please enter your name.</div>
    </div>
    <div class="col-md-6">
      <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
      <input type="tel" name="mobile" class="form-control" required pattern="[0-9+\s\-]{7,15}"
             maxlength="20" value="<?= e($old['mobile'] ?? '') ?>">
      <div class="invalid-feedback">Please enter a valid mobile number.</div>
    </div>
    <div class="col-md-6">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" maxlength="190"
             value="<?= e($old['email'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">City</label>
      <input type="text" name="city" class="form-control" maxlength="120"
             value="<?= e($old['city'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">State</label>
      <input type="text" name="state" class="form-control" maxlength="120"
             value="<?= e($old['state'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Profession</label>
      <input type="text" name="profession" class="form-control" maxlength="150"
             value="<?= e($old['profession'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Interested Service</label>
      <select name="interested_service" class="form-select">
        <option value="">-- Select --</option>
        <?php foreach ($svc as $title): ?>
          <option value="<?= e($title) ?>" <?= (($old['interested_service'] ?? '') === $title) ? 'selected' : '' ?>>
            <?= e($title) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Message</label>
      <input type="text" name="message" class="form-control" maxlength="500"
             value="<?= e($old['message'] ?? '') ?>">
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-lg brand-btn w-100">Submit Registration</button>
    </div>
  </form>
</div>
