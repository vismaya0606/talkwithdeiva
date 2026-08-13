<?php
require_once __DIR__ . '/config/functions.php';
start_session();

$tid = tenant_id();

// Fetch priced services once — used by the form dropdown and POST handling.
try {
    $svc_stmt = db()->prepare(
        'SELECT id, title, price FROM services
         WHERE tenant_id = ? AND price IS NOT NULL AND price > 0
         ORDER BY display_order ASC'
    );
    $svc_stmt->execute([$tid]);
    $priced_services = $svc_stmt->fetchAll();
} catch (Exception $e) {
    $priced_services = [];
}

$razorpay_order      = null;
$payment_row_id      = 0;
$payment_service     = null;
$buyer_for_modal     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $service_id = (int)($_POST['service_id'] ?? 0);

    $data = [
        'full_name'   => trim($_POST['full_name']   ?? ''),
        'mobile'      => trim($_POST['mobile']       ?? ''),
        'email'       => trim($_POST['email']        ?? ''),
        'child_name'  => trim($_POST['child_name']   ?? ''),
        'grade'       => trim($_POST['grade']        ?? ''),
        'syllabus'    => trim($_POST['syllabus']     ?? ''),
        'city'        => trim($_POST['city']         ?? ''),
        'heard_about' => trim($_POST['heard_about']  ?? ''),
        'message'     => trim($_POST['message']      ?? ''),
        'service_id'  => $service_id,
    ];

    // Resolve the selected service from our already-fetched list.
    $selected_service = null;
    foreach ($priced_services as $svc) {
        if ((int)$svc['id'] === $service_id) {
            $selected_service = $svc;
            break;
        }
    }

    if ($data['full_name'] === '' || $data['mobile'] === '') {
        flash('reg_error', 'Parent name and WhatsApp number are required.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        flash('reg_error', 'Please enter a valid email address.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } else {
        // Save registration.
        db()->prepare(
            'INSERT INTO registrations
             (tenant_id, full_name, mobile, email, child_name, grade, syllabus,
              city, heard_about, interested_service, message)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $tid,
            $data['full_name'], $data['mobile'], $data['email'] ?: null,
            $data['child_name'] ?: null, $data['grade'] ?: null,
            $data['syllabus'] ?: null, $data['city'] ?: null,
            $data['heard_about'] ?: null,
            $selected_service ? $selected_service['title'] : null,
            $data['message'] ?: null,
        ]);

        if ($selected_service && razorpay_enabled()) {
            // Record the payment attempt before opening the modal.
            db()->prepare(
                'INSERT INTO payments
                 (tenant_id, service_id, service_title, buyer_name, email, phone, amount)
                 VALUES (?,?,?,?,?,?,?)'
            )->execute([
                $tid, (int)$selected_service['id'], $selected_service['title'],
                $data['full_name'], $data['email'], $data['mobile'],
                $selected_service['price'],
            ]);
            $payment_row_id  = (int) db()->lastInsertId();
            $order           = razorpay_create_order(
                (float) $selected_service['price'],
                'receipt_' . $payment_row_id
            );

            if ($order) {
                db()->prepare(
                    'UPDATE payments SET payment_request_id = ? WHERE id = ? AND tenant_id = ?'
                )->execute([$order['id'], $payment_row_id, $tid]);

                $razorpay_order  = $order;
                $payment_service = $selected_service;
                $buyer_for_modal = $data;
                // Fall through — render page with Razorpay modal auto-opening.
            } else {
                db()->prepare('DELETE FROM payments WHERE id = ? AND tenant_id = ?')
                    ->execute([$payment_row_id, $tid]);
                flash('reg_error', 'Registration saved, but payment could not be started. Please contact us.');
                redirect(base_url() . 'register.php#register-form');
            }
        } else {
            flash('reg_success', 'Thank you! Your registration has been received successfully.');
            redirect(base_url() . 'register.php#register-form');
        }
    }
}

$page_title       = 'Register | ' . setting('site_name');
$page_description = 'Register with ' . setting('site_name') . ' today.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h1 class="section-title">Register</h1>
      <p class="text-muted">Fill in your details and proceed to pay securely.</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php include __DIR__ . '/includes/register-form.php'; ?>
      </div>
    </div>
  </div>
</section>

<?php if ($razorpay_order): ?>
<!-- Razorpay modal auto-opens after registration is saved -->
<div id="rzp-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center
     justify-content-center" style="background:rgba(0,0,0,.55);z-index:9999">
  <div class="bg-white rounded shadow p-4 text-center" style="max-width:400px">
    <div class="spinner-border text-danger mb-3" role="status"></div>
    <p class="mb-0 fw-semibold">Opening payment window…</p>
  </div>
</div>

<form id="rzp-form" action="<?= e(base_url()) ?>payment-status.php" method="post" class="d-none">
  <?= csrf_field() ?>
  <input type="hidden" name="ref"                 value="<?= $payment_row_id ?>">
  <input type="hidden" name="razorpay_order_id"   id="rzp_order_id">
  <input type="hidden" name="razorpay_payment_id" id="rzp_payment_id">
  <input type="hidden" name="razorpay_signature"  id="rzp_signature">
</form>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var rzpOptions = {
  key:         <?= json_encode(setting('razorpay_key_id')) ?>,
  amount:      <?= (int) round((float)$payment_service['price'] * 100) ?>,
  currency:    "INR",
  name:        <?= json_encode(setting('site_name')) ?>,
  description: <?= json_encode($payment_service['title']) ?>,
  order_id:    <?= json_encode($razorpay_order['id']) ?>,
  prefill: {
    name:    <?= json_encode($buyer_for_modal['full_name'] ?? '') ?>,
    email:   <?= json_encode($buyer_for_modal['email']    ?? '') ?>,
    contact: <?= json_encode($buyer_for_modal['mobile']   ?? '') ?>
  },
  theme: { color: <?= json_encode(setting('primary_color', '#E8112D')) ?> },
  handler: function(response) {
    document.getElementById('rzp_order_id').value   = response.razorpay_order_id;
    document.getElementById('rzp_payment_id').value = response.razorpay_payment_id;
    document.getElementById('rzp_signature').value  = response.razorpay_signature;
    document.getElementById('rzp-form').submit();
  },
  modal: {
    ondismiss: function() {
      document.getElementById('rzp-overlay').remove();
    }
  }
};
var rzp = new Razorpay(rzpOptions);
rzp.on('payment.failed', function(r) {
  document.getElementById('rzp_order_id').value   = r.error.metadata.order_id   || '';
  document.getElementById('rzp_payment_id').value = r.error.metadata.payment_id || '';
  document.getElementById('rzp_signature').value  = '';
  document.getElementById('rzp-form').submit();
});
rzp.open();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
