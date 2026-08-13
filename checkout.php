<?php
/**
 * Checkout: buyer picks a priced service, enters their details, then pays
 * via Razorpay's in-page checkout modal. After payment Razorpay calls our
 * handler which posts the signature to payment-status.php for verification.
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

$razorpay_order = null;
$payment_row_id = 0;

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
    } elseif (!razorpay_enabled()) {
        flash('pay_error', 'Online payment is temporarily unavailable. Please contact us to complete your booking.');
    } else {
        // Record the attempt first so it is traceable even if the buyer
        // closes the payment modal without paying.
        db()->prepare(
            'INSERT INTO payments (tenant_id, service_id, service_title, buyer_name, email, phone, amount)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([
            $tid, (int)$service['id'], $service['title'],
            $buyer['name'], $buyer['email'], $buyer['phone'], $service['price'],
        ]);
        $payment_row_id = (int) db()->lastInsertId();

        $order = razorpay_create_order(
            (float) $service['price'],
            'receipt_' . $payment_row_id
        );

        if ($order) {
            db()->prepare('UPDATE payments SET payment_request_id = ? WHERE id = ? AND tenant_id = ?')
                ->execute([$order['id'], $payment_row_id, $tid]);
            $razorpay_order = $order;
        } else {
            // Roll back the pending row so it does not pollute the admin view.
            db()->prepare('DELETE FROM payments WHERE id = ? AND tenant_id = ?')
                ->execute([$payment_row_id, $tid]);
            flash('pay_error', 'We could not start the payment. Please try again or contact us.');
            $_SESSION['pay_old'] = $buyer;
        }
    }

    if (!$razorpay_order) {
        redirect(base_url() . 'checkout.php?service=' . (int)$service['id']);
    }
}

$err = flash('pay_error');
// pay_old holds values from a failed/validation-failed POST;
// checkout_prefill carries name/email/phone forwarded from the registration form.
$old = $_SESSION['pay_old'] ?? $_SESSION['checkout_prefill'] ?? [];
unset($_SESSION['pay_old'], $_SESSION['checkout_prefill']);

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

    <?php if ($razorpay_order): ?>
      <!-- ── Razorpay payment modal is triggered automatically ── -->
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="bg-white rounded shadow-sm p-4 text-center">
            <div class="service-icon mx-auto mb-3"><i class="bi <?= e($service['icon']) ?>"></i></div>
            <h5 class="fw-bold"><?= e($service['title']) ?></h5>
            <p class="text-muted small"><?= e($service['description']) ?></p>
            <hr>
            <div class="fs-4 fw-bold brand-text mb-3"><?= e(format_price($service['price'])) ?></div>
            <p class="text-muted small mb-3">
              <i class="bi bi-shield-lock me-1"></i>
              Payments are processed securely by Razorpay. We never see or store your card / UPI details.
            </p>
            <button id="rzp-button" class="btn brand-btn btn-lg w-100">
              <i class="bi bi-lock-fill me-1"></i> Pay Now
            </button>
            <p class="text-muted small mt-3 mb-0">
              By paying you agree to our
              <a href="<?= e(base_url()) ?>terms.php">Terms &amp; Conditions</a>,
              <a href="<?= e(base_url()) ?>privacy.php">Privacy Policy</a> and
              <a href="<?= e(base_url()) ?>refund.php">Refund &amp; Cancellation Policy</a>.
            </p>
          </div>
        </div>
      </div>

      <!-- Hidden form posted to payment-status.php after Razorpay succeeds -->
      <form id="rzp-form" action="<?= e(base_url()) ?>payment-status.php" method="post" class="d-none">
        <?= csrf_field() ?>
        <input type="hidden" name="ref"                    value="<?= $payment_row_id ?>">
        <input type="hidden" name="razorpay_order_id"      id="rzp_order_id">
        <input type="hidden" name="razorpay_payment_id"    id="rzp_payment_id">
        <input type="hidden" name="razorpay_signature"     id="rzp_signature">
      </form>

      <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
      <script>
        var options = {
          key:         <?= json_encode(setting('razorpay_key_id')) ?>,
          amount:      <?= (int) round((float)$service['price'] * 100) ?>,
          currency:    "INR",
          name:        <?= json_encode(setting('site_name')) ?>,
          description: <?= json_encode($service['title']) ?>,
          order_id:    <?= json_encode($razorpay_order['id']) ?>,
          prefill: {
            name:    <?= json_encode($buyer['name'] ?? '') ?>,
            email:   <?= json_encode($buyer['email'] ?? '') ?>,
            contact: <?= json_encode($buyer['phone'] ?? '') ?>
          },
          theme: { color: <?= json_encode(setting('primary_color', '#0d6efd')) ?> },
          handler: function(response) {
            document.getElementById('rzp_order_id').value   = response.razorpay_order_id;
            document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
            document.getElementById('rzp_signature').value  = response.razorpay_signature;
            document.getElementById('rzp-form').submit();
          },
          modal: {
            ondismiss: function() {
              document.getElementById('rzp-button').disabled = false;
            }
          }
        };
        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function(response) {
          document.getElementById('rzp_order_id').value   = response.error.metadata.order_id   || '';
          document.getElementById('rzp_payment_id').value = response.error.metadata.payment_id || '';
          document.getElementById('rzp_signature').value  = '';
          document.getElementById('rzp-form').submit();
        });

        // Auto-open the modal; "Pay Now" button re-opens if dismissed.
        rzp.open();
        document.getElementById('rzp-button').addEventListener('click', function(e) {
          e.preventDefault();
          this.disabled = true;
          rzp.open();
        });
      </script>

    <?php else: ?>
      <!-- ── Buyer details form ── -->
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
              Payments are processed securely by Razorpay. We never see or store your card / UPI details.
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
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
