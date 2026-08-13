<?php
/** Registration form — 0–8 Years Parenting & Education Webinar */
require_once __DIR__ . '/../config/functions.php';

$ok  = flash('reg_success');
$err = flash('reg_error');
$old = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_old']);

$describes_options = [
    'Parent of a child aged 0–8 years',
    'Expecting Parent',
    'Primary School Teacher',
    'Parent & Teacher',
    'Other',
];

$child_age_options = [
    'Expecting',
    '0–1 year',
    '2–3 years',
    '4–5 years',
    '6–8 years',
    'More than one child in the 0–8 age group',
    'Not Applicable – Teacher',
];

$heard_options = [
    'Instagram',
    'Facebook',
    'WhatsApp',
    'YouTube',
    'Friend / Family',
    'School / Teacher',
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

    <!-- ── Registration Details ── -->
    <h5 class="form-section-heading">Registration Details</h5>

    <div class="mb-3">
      <label class="form-label">1. Full Name <span class="text-danger">*</span></label>
      <input type="text" name="full_name" class="form-control" required maxlength="150"
             placeholder="Enter your full name"
             value="<?= e($old['full_name'] ?? '') ?>">
      <div class="invalid-feedback">Please enter your full name.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">2. Email ID <span class="text-danger">*</span></label>
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

    <!-- ── About You ── -->
    <h5 class="form-section-heading">About You</h5>

    <div class="mb-4">
      <label class="form-label">4. Which best describes you? <span class="text-danger">*</span></label>
      <?php foreach ($describes_options as $opt): ?>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="profession"
                 id="desc_<?= e(preg_replace('/\W+/', '_', $opt)) ?>"
                 value="<?= e($opt) ?>" required
                 <?= (($old['profession'] ?? '') === $opt) ? 'checked' : '' ?>>
          <label class="form-check-label" for="desc_<?= e(preg_replace('/\W+/', '_', $opt)) ?>">
            <?= e($opt) ?>
          </label>
        </div>
      <?php endforeach; ?>
      <div class="invalid-feedback d-block" id="desc-error" style="display:none!important"></div>
    </div>

    <div class="mb-4">
      <label class="form-label">5. If you are a parent, what is your child's age?</label>
      <?php foreach ($child_age_options as $opt): ?>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="grade"
                 id="age_<?= e(preg_replace('/\W+/', '_', $opt)) ?>"
                 value="<?= e($opt) ?>"
                 <?= (($old['grade'] ?? '') === $opt) ? 'checked' : '' ?>>
          <label class="form-check-label" for="age_<?= e(preg_replace('/\W+/', '_', $opt)) ?>">
            <?= e($opt) ?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-4">
      <label class="form-label">6. How did you hear about this webinar?</label>
      <?php foreach ($heard_options as $opt): ?>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="heard_about"
                 id="heard_<?= e(preg_replace('/\W+/', '_', $opt)) ?>"
                 value="<?= e($opt) ?>"
                 <?= (($old['heard_about'] ?? '') === $opt) ? 'checked' : '' ?>>
          <label class="form-check-label" for="heard_<?= e(preg_replace('/\W+/', '_', $opt)) ?>">
            <?= e($opt) ?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mb-4">
      <label class="form-label">
        7. Would you like to receive information about future parenting, child development
        and career guidance sessions?
      </label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="message" id="future_yes"
               value="Yes" <?= (($old['message'] ?? '') === 'Yes') ? 'checked' : '' ?>>
        <label class="form-check-label" for="future_yes">Yes</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="message" id="future_no"
               value="No" <?= (($old['message'] ?? '') === 'No') ? 'checked' : '' ?>>
        <label class="form-check-label" for="future_no">No</label>
      </div>
    </div>

    <!-- ── Confirmation ── -->
    <h5 class="form-section-heading">Confirmation</h5>

    <div class="mb-4">
      <label class="form-label">
        8. I confirm that the information provided above is correct.
        <span class="text-danger">*</span>
      </label>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="confirmed" id="confirm_yes"
               value="yes" required
               <?= (($old['confirmed'] ?? '') === 'yes') ? 'checked' : '' ?>>
        <label class="form-check-label fw-semibold" for="confirm_yes">
          ✓ Yes, I confirm
        </label>
        <div class="invalid-feedback">Please confirm your details before submitting.</div>
      </div>
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
