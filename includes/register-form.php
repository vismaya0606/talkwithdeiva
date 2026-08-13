<?php
/**
 * Reusable registration form (used on register.php and home page).
 * $priced_services must be set by the including page; if not, no course
 * dropdown is shown and the form saves a plain enquiry.
 */
require_once __DIR__ . '/../config/functions.php';

$syllabus_options = ['CBSE', 'ICSE', 'State Board', 'IB', 'IGCSE'];
$heard_options    = ['Instagram', 'Facebook', 'WhatsApp', 'Friends', 'Others'];

$ok  = flash('reg_success');
$err = flash('reg_error');
$old = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_old']);

// Fetch priced services if the including page hasn't already done it.
if (!isset($priced_services)) {
    try {
        $ps = db()->prepare(
            'SELECT id, title, price FROM services
             WHERE tenant_id = ? AND price IS NOT NULL AND price > 0
             ORDER BY display_order ASC'
        );
        $ps->execute([tenant_id()]);
        $priced_services = $ps->fetchAll();
    } catch (Exception $e) {
        $priced_services = [];
    }
}

$has_courses = !empty($priced_services);
?>
<div id="register-form" class="register-card p-4 p-md-5 bg-white rounded shadow-sm">
  <?php if ($ok): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-1"></i><?= e($ok) ?></div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endif; ?>

  <form action="<?= e(base_url()) ?>register.php" method="post"
        novalidate class="needs-validation row g-3">
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
          <option value="<?= e($opt) ?>"
            <?= (($old['syllabus'] ?? '') === $opt) ? 'selected' : '' ?>>
            <?= e($opt) ?>
          </option>
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
          <option value="<?= e($opt) ?>"
            <?= (($old['heard_about'] ?? '') === $opt) ? 'selected' : '' ?>>
            <?= e($opt) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">What is your primary question or expectation from this webinar?</label>
      <textarea name="message" class="form-control" rows="3"
                maxlength="1000"><?= e($old['message'] ?? '') ?></textarea>
    </div>

    <?php if ($has_courses): ?>
    <div class="col-12">
      <label class="form-label fw-semibold">Select Course <span class="text-danger">*</span></label>
      <select name="service_id" class="form-select form-select-lg" required>
        <option value="">-- Choose a course --</option>
        <?php foreach ($priced_services as $svc): ?>
          <option value="<?= (int)$svc['id'] ?>"
            <?= ((int)($old['service_id'] ?? 0) === (int)$svc['id']) ? 'selected' : '' ?>>
            <?= e($svc['title']) ?> — <?= e(format_price($svc['price'])) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <div class="invalid-feedback">Please select a course.</div>
    </div>
    <?php endif; ?>

    <div class="col-12">
      <button type="submit" class="btn btn-lg brand-btn w-100">
        <i class="bi bi-lock-fill me-1"></i>
        <?= $has_courses ? 'Register &amp; Pay Now' : 'Submit Registration' ?>
      </button>
    </div>

    <?php if ($has_courses): ?>
    <p class="text-muted small mb-0 col-12 text-center">
      <i class="bi bi-shield-lock me-1"></i>
      Payments are processed securely by Razorpay.
      By registering you agree to our
      <a href="<?= e(base_url()) ?>terms.php">Terms</a> &amp;
      <a href="<?= e(base_url()) ?>refund.php">Refund Policy</a>.
    </p>
    <?php endif; ?>
  </form>
</div>
