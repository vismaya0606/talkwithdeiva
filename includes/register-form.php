<?php
/** Registration form — Plan Your Child's Future Before 10th! */
require_once __DIR__ . '/../config/functions.php';

$ok  = flash('reg_success');
$err = flash('reg_error');
$old = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_old']);

$grade_options = [
    'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
    'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
];

$syllabus_options = [
    'CBSE',
    'ICSE',
    'State Board',
    'IB',
    'Cambridge / IGCSE',
    'Other',
];
?>
<div id="register-form" class="register-card p-4 p-md-5 bg-white rounded shadow-sm">
  <?php if ($ok): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i><?= e($ok) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endif; ?>

  <form action="<?= e(base_url()) ?>register.php" method="post"
        novalidate class="needs-validation">
    <?= csrf_field() ?>

    <!-- ── Parent Details ── -->
    <h5 class="form-section-heading">Parent Details</h5>

    <div class="mb-3">
      <label class="form-label">1. Parent's Full Name <span class="text-danger">*</span></label>
      <input type="text" name="full_name" class="form-control" required maxlength="150"
             placeholder="Enter your full name"
             value="<?= e($old['full_name'] ?? '') ?>">
      <div class="invalid-feedback">Please enter your full name.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">2. Email Address <span class="text-danger">*</span></label>
      <input type="email" name="email" class="form-control" required maxlength="190"
             placeholder="Enter your email address"
             value="<?= e($old['email'] ?? '') ?>">
      <div class="invalid-feedback">Please enter a valid email address.</div>
    </div>

    <div class="mb-4">
      <label class="form-label">3. WhatsApp Number <span class="text-danger">*</span></label>
      <input type="tel" name="mobile" class="form-control" required pattern="[0-9+\s\-]{7,15}"
             maxlength="20" placeholder="Enter your WhatsApp number"
             value="<?= e($old['mobile'] ?? '') ?>">
      <div class="invalid-feedback">Please enter a valid WhatsApp number.</div>
    </div>

    <!-- ── Child's Details ── -->
    <h5 class="form-section-heading">Child's Details</h5>

    <div class="mb-3">
      <label class="form-label">4. Child's Name <span class="text-danger">*</span></label>
      <input type="text" name="child_name" class="form-control" required maxlength="150"
             placeholder="Enter your child's name"
             value="<?= e($old['child_name'] ?? '') ?>">
      <div class="invalid-feedback">Please enter your child's name.</div>
    </div>

    <div class="mb-4">
      <label class="form-label">5. Child's Current Grade / Standard <span class="text-danger">*</span></label>
      <?php foreach ($grade_options as $opt): ?>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="grade"
                 id="grade_<?= e(preg_replace('/\W+/', '_', $opt)) ?>"
                 value="<?= e($opt) ?>" required
                 <?= (($old['grade'] ?? '') === $opt) ? 'checked' : '' ?>>
          <label class="form-check-label" for="grade_<?= e(preg_replace('/\W+/', '_', $opt)) ?>">
            <?= e($opt) ?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-4">
      <label class="form-label">6. Which syllabus / board does your child follow? <span class="text-danger">*</span></label>
      <?php foreach ($syllabus_options as $opt): ?>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="syllabus"
                 id="syl_<?= e(preg_replace('/\W+/', '_', $opt)) ?>"
                 value="<?= e($opt) ?>" required
                 <?= (($old['syllabus'] ?? '') === $opt) ? 'checked' : '' ?>>
          <label class="form-check-label" for="syl_<?= e(preg_replace('/\W+/', '_', $opt)) ?>">
            <?= e($opt) ?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn btn-lg brand-btn w-100">
      <i class="bi bi-lock-fill me-1"></i> Register &amp; Pay Now
    </button>
    <p class="text-muted small mt-2 mb-0 text-center">
      <i class="bi bi-shield-lock me-1"></i>
      Payments are processed securely by Razorpay.
    </p>

  </form>
</div>
