<?php
/** Reusable registration form (used on home page + register.php). */
require_once __DIR__ . '/../config/functions.php';

$syllabus_options = ['CBSE', 'ICSE', 'State Board', 'IB', 'IGCSE'];
$heard_options    = ['Instagram', 'Facebook', 'WhatsApp', 'Friends', 'Others'];

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
      <label class="form-label">Parent Name <span class="text-danger">*</span></label>
      <input type="text" name="full_name" class="form-control" required maxlength="150"
             value="<?= e($old['full_name'] ?? '') ?>">
      <div class="invalid-feedback">Please enter the parent's name.</div>
    </div>
    <div class="col-md-6">
      <label class="form-label">WhatsApp Number <span class="text-danger">*</span></label>
      <input type="tel" name="mobile" class="form-control" required pattern="[0-9+\s\-]{7,15}"
             maxlength="20" value="<?= e($old['mobile'] ?? '') ?>">
      <div class="invalid-feedback">Please enter a valid WhatsApp number.</div>
    </div>
    <div class="col-md-6">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" maxlength="190"
             value="<?= e($old['email'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Child's Name</label>
      <input type="text" name="child_name" class="form-control" maxlength="150"
             value="<?= e($old['child_name'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Grade / Class</label>
      <input type="text" name="grade" class="form-control" maxlength="60"
             value="<?= e($old['grade'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Syllabus</label>
      <select name="syllabus" class="form-select">
        <option value="">-- Select --</option>
        <?php foreach ($syllabus_options as $opt): ?>
          <option value="<?= e($opt) ?>" <?= (($old['syllabus'] ?? '') === $opt) ? 'selected' : '' ?>>
            <?= e($opt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">City</label>
      <input type="text" name="city" class="form-control" maxlength="120"
             value="<?= e($old['city'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">How did you hear about this webinar?</label>
      <select name="heard_about" class="form-select">
        <option value="">-- Select --</option>
        <?php foreach ($heard_options as $opt): ?>
          <option value="<?= e($opt) ?>" <?= (($old['heard_about'] ?? '') === $opt) ? 'selected' : '' ?>>
            <?= e($opt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">What is your primary question or expectation from this webinar?</label>
      <textarea name="message" class="form-control" rows="3" maxlength="1000"><?= e($old['message'] ?? '') ?></textarea>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-lg brand-btn w-100">Submit Registration</button>
    </div>
  </form>
</div>
