<?php
/**
 * Checkout: buyer picks a priced service, enters their details and is
 * redirected to Instamojo's secure payment page. Instamojo sends them
 * back to payment-status.php where the payment is verified.
 */
require_once __DIR__ . '/config/functions.php';
start_session();

$tid = tenant_id();

// The service being purchased (?service=ID). Only priced services are payable.
$service_id = (int)($_GET['service'] ?? $_POST['service_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM services WHERE id = ? AND tenant_id = ? AND price IS NOT NULL AND price > 0');
$stmt->execute([$service_id, $tid]);
$service = $stmt->fetch();

if (!$service) {
    redirect(base_url() . 'index.php#services');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $buyer = [
        'name'  => trim($_POST['buyer_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
    ];

    if ($buyer['name'] === '' || $buyer['email'] === '' || $buyer['phone'] === '') {
        flash('pay_error', 'Please fill in your name, email and phone number.');
        $_SESSION['pay_old'] = $buyer;
    } elseif (!filter_var($buyer['email'], FILTER_VALIDATE_EMAIL)) {
        flash('pay_error', 'Please enter a valid email address.');
        $_SESSION['pay_old'] = $buyer;
    } elseif (!preg_match('/^[0-9+\s\-]{7,15}$/', $buyer['phone'])) {
        flash('pay_error', 'Please enter a valid phone number.');
        $_SESSION['pay_old'] = $buyer;
    } elseif (!instamojo_enabled()) {
        flash('pay_error', 'Online payment is temporarily unavailable. Please contact us to complete your booking.');
    } else {
        // Record the attempt first so it is traceable even if the buyer
        // never returns from the gateway.
        db()->prepare(
            'INSERT INTO payments (tenant_id, service_id, service_title, buyer_name, email, phone, amount)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([
            $tid, (int)$service['id'], $service['title'],
            $buyer['name'], $buyer['email'], $buyer['phone'], $service['price'],
        ]);
        $payment_row_id = (int) db()->lastInsertId();

        $pr = instamojo_create_payment_request(
            $service['title'],
            (float) $service['price'],
            $buyer['name'],
            $buyer['email'],
            $buyer['phone'],
            base_url() . 'payment-status.php?ref=' . $payment_row_id
        );

        if ($pr) {
            db()->prepare('UPDATE payments SET payment_request_id = ? WHERE id = ? AND tenant_id = ?')
                ->execute([$pr['id'], $payment_row_id, $tid]);
            redirect($pr['longurl']);
        }

        flash('pay_error', 'We could not start the payment. Please try again or contact us.');
        $_SESSION['pay_old'] = $buyer;
    }

    redirect(base_url() . 'checkout.php?service=' . (int)$service['id']);
}

$err = flash('pay_error');
$old = $_SESSION['pay_old'] ?? [];
unset($_SESSION['pay_old']);

$page_title       = 'Checkout | ' . setting('site_name');
$page_description = 'Complete your booking with ' . setting('site_name') . ' securely online.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container" style="max-width:960px">
    <div class="text-center mb-4">
      <h1 class="section-title">Checkout</h1>
      <p class="text-muted">Review your order and pay securely online.</p>
    </div>

    <?php if ($err): ?>
      <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Order summary -->
      <div class="col-lg-5">
        <div class="bg-white rounded shadow-sm p-4 h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-bag-check me-1 brand-text"></i> Order Summary</h5>
          <div class="d-flex align-items-start gap-3 mb-3">
            <div class="service-icon"><i class="bi <?= e($service['icon']) ?>"></i></div>
            <div>
              <h6 class="fw-bold mb-1"><?= e($service['title']) ?></h6>
              <p class="text-muted small mb-0"><?= e($service['description']) ?></p>
            </div>
          </div>
          <hr>
          <div class="d-flex justify-content-between fs-5">
            <span>Total</span>
            <strong class="brand-text"><?= e(format_price($service['price'])) ?></strong>
          </div>
          <p class="text-muted small mt-3 mb-0">
            <i class="bi bi-shield-lock me-1"></i>
            Payments are processed securely by Instamojo. We never see or store your card / UPI details.
          </p>
        </div>
      </div>

      <!-- Buyer details -->
      <div class="col-lg-7">
        <div class="bg-white rounded shadow-sm p-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-person me-1 brand-text"></i> Your Details</h5>
          <form action="<?= e(base_url()) ?>checkout.php" method="post" novalidate class="needs-validation row g-3">
            <?= csrf_field() ?>
            <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">
            <div class="col-12">
              <label class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="buyer_name" class="form-control" required maxlength="150"
                     value="<?= e($old['name'] ?? '') ?>">
              <div class="invalid-feedback">Please enter your name.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" required maxlength="190"
                     value="<?= e($old['email'] ?? '') ?>">
              <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number <span class="text-danger">*</span></label>
              <input type="tel" name="phone" class="form-control" required pattern="[0-9+\s\-]{7,15}"
                     maxlength="20" value="<?= e($old['phone'] ?? '') ?>">
              <div class="invalid-feedback">Please enter a valid phone number.</div>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-lg brand-btn w-100">
                <i class="bi bi-lock-fill me-1"></i>
                Pay <?= e(format_price($service['price'])) ?> Securely
              </button>
            </div>
            <p class="text-muted small mb-0 col-12">
              By paying you agree to our
              <a href="<?= e(base_url()) ?>terms.php">Terms &amp; Conditions</a>,
              <a href="<?= e(base_url()) ?>privacy.php">Privacy Policy</a> and
              <a href="<?= e(base_url()) ?>refund.php">Refund &amp; Cancellation Policy</a>.
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
