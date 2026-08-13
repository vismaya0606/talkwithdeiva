<?php
/**
 * Payment result page. After the buyer completes (or fails) the Razorpay
 * checkout modal, the JS handler posts the payment identifiers here.
 * The signature is verified server-side before recording the result.
 */
require_once __DIR__ . '/config/functions.php';
start_session();

$tid = tenant_id();

// Accept POST (from the Razorpay JS handler form) or GET (for page reloads
// by already-verified payments, e.g. after redirect from confirmation email).
$ref        = (int)(($_POST['ref'] ?? $_GET['ref']) ?? 0);
$order_id   = trim(($_POST['razorpay_order_id']   ?? $_GET['razorpay_order_id'])   ?? '');
$payment_id = trim(($_POST['razorpay_payment_id'] ?? $_GET['razorpay_payment_id']) ?? '');
$signature  = trim($_POST['razorpay_signature'] ?? '');

$payment = null;
if ($ref > 0) {
    $stmt = db()->prepare('SELECT * FROM payments WHERE id = ? AND tenant_id = ?');
    $stmt->execute([$ref, $tid]);
    $payment = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $payment) {
    require_csrf();
}

$state = 'unknown'; // success | failed | unknown

if ($payment && $payment['status'] === 'success') {
    // Already verified (e.g. page reloaded).
    $state      = 'success';
    $payment_id = $payment['payment_id'];
} elseif ($payment && $payment['status'] !== 'success') {
    if ($signature !== '' && $order_id !== '' && $payment_id !== ''
        && $payment['payment_request_id'] === $order_id
    ) {
        // Verify Razorpay signature: HMAC-SHA256(order_id|payment_id, key_secret)
        $verified = razorpay_verify_payment($order_id, $payment_id, $signature)
            ? 'success' : 'failed';
    } else {
        // No valid signature sent — treat as a failed / cancelled payment.
        $verified = ($order_id !== '' || $payment_id !== '') ? 'failed' : null;
    }

    if ($verified !== null) {
        $state = $verified;
        db()->prepare('UPDATE payments SET payment_id = ?, status = ? WHERE id = ? AND tenant_id = ?')
            ->execute([$payment_id, $verified, (int)$payment['id'], $tid]);
    }
}

$page_title       = 'Payment Status | ' . setting('site_name');
$page_description = 'Payment status for your order with ' . setting('site_name') . '.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container text-center" style="max-width:640px">
    <?php if ($state === 'success'): ?>
      <div class="bg-white rounded shadow-sm p-5">
        <i class="bi bi-check-circle-fill text-success" style="font-size:4rem"></i>
        <h1 class="section-title mt-3">Payment Successful</h1>
        <p class="text-muted">
          Thank you, <?= e($payment['buyer_name']) ?>! Your payment of
          <strong><?= e(format_price($payment['amount'])) ?></strong> for
          <strong><?= e($payment['service_title']) ?></strong> has been received.
        </p>
        <?php if ($payment_id): ?>
          <p class="small text-muted">Payment Reference: <code><?= e($payment_id) ?></code></p>
        <?php endif; ?>
        <p class="text-muted small">A confirmation will be sent to <?= e($payment['email']) ?>.
          For any questions, contact us at
          <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a>.</p>
        <a href="<?= e(base_url()) ?>index.php" class="btn brand-btn mt-2">Back to Home</a>
      </div>
    <?php elseif ($state === 'failed'): ?>
      <div class="bg-white rounded shadow-sm p-5">
        <i class="bi bi-x-circle-fill text-danger" style="font-size:4rem"></i>
        <h1 class="section-title mt-3">Payment Failed</h1>
        <p class="text-muted">Unfortunately your payment could not be completed. No amount has been
          captured by us. If money was deducted from your account, it will be automatically refunded
          by your bank / the payment gateway.</p>
        <?php if ($payment): ?>
          <a href="<?= e(base_url()) ?>checkout.php?service=<?= (int)$payment['service_id'] ?>"
             class="btn brand-btn mt-2">Try Again</a>
        <?php endif; ?>
        <a href="<?= e(base_url()) ?>contact.php" class="btn btn-brand-outline mt-2">Contact Us</a>
      </div>
    <?php else: ?>
      <div class="bg-white rounded shadow-sm p-5">
        <i class="bi bi-question-circle-fill text-warning" style="font-size:4rem"></i>
        <h1 class="section-title mt-3">Payment Status Unknown</h1>
        <p class="text-muted">We could not confirm your payment status right now. If you completed a
          payment, please contact us at
          <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a>
          with your payment reference and we will verify it for you.</p>
        <a href="<?= e(base_url()) ?>index.php" class="btn brand-btn mt-2">Back to Home</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
