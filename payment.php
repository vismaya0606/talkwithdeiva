<?php
/**
 * Payment page: shown after registration is saved.
 * Displays the Razorpay Payment Button so the user can complete their fee payment.
 */
require_once __DIR__ . '/config/functions.php';
start_session();

$page_title       = 'Complete Payment | ' . setting('site_name');
$page_description = 'Plan Your Child\'s Future Before 10th! — Securely pay your course fees with ' . setting('site_name') . '.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container d-flex justify-content-center">
    <div class="bg-white rounded shadow-sm p-5 text-center" style="max-width:520px;width:100%">

      <i class="bi bi-check-circle-fill text-success" style="font-size:3.5rem"></i>
      <h2 class="section-title mt-3">Registration Saved!</h2>
      <p class="text-muted mb-1">Your details have been received.</p>
      <p class="text-muted mb-4">
        Please complete your <strong>course fee payment of ₹299</strong> below to confirm your enrollment.
      </p>

      <div class="d-flex justify-content-center mb-3">
        <form>
          <script src="https://checkout.razorpay.com/v1/payment-button.js"
                  data-payment_button_id="pl_TP5tjzYsvoaAjs" async></script>
        </form>
      </div>

      <p class="text-muted small mt-3 mb-0">
        <i class="bi bi-shield-lock me-1"></i>
        Payments are processed securely by Razorpay.
      </p>
      <p class="text-muted small mt-2">
        Questions? Contact us at
        <a href="mailto:<?= e(setting('contact_email')) ?>"><?= e(setting('contact_email')) ?></a>
      </p>

      <hr class="my-3">
      <a href="<?= e(base_url()) ?>index.php" class="text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Back to Home
      </a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
