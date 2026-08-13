<?php
require_once __DIR__ . '/config/functions.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $service_id = (int)($_POST['service_id'] ?? 0);

    $data = [
        'full_name'   => trim($_POST['full_name'] ?? ''),   // Parent Name
        'mobile'      => trim($_POST['mobile'] ?? ''),       // WhatsApp Number
        'email'       => trim($_POST['email'] ?? ''),
        'child_name'  => trim($_POST['child_name'] ?? ''),
        'grade'       => trim($_POST['grade'] ?? ''),
        'syllabus'    => trim($_POST['syllabus'] ?? ''),
        'city'        => trim($_POST['city'] ?? ''),
        'heard_about' => trim($_POST['heard_about'] ?? ''),
        'message'     => trim($_POST['message'] ?? ''),      // Primary question / expectation
        'service_id'  => $service_id,
    ];

    // Resolve selected service title for the interested_service column.
    $interested_service = null;
    if ($service_id > 0) {
        $svc_stmt = db()->prepare(
            'SELECT title FROM services WHERE id = ? AND tenant_id = ? AND price IS NOT NULL AND price > 0'
        );
        $svc_stmt->execute([$service_id, tenant_id()]);
        $svc_row = $svc_stmt->fetch();
        $interested_service = $svc_row ? $svc_row['title'] : null;
    }

    // Validation: parent name + WhatsApp required, email optional but validated if given.
    if ($data['full_name'] === '' || $data['mobile'] === '') {
        flash('reg_error', 'Parent name and WhatsApp number are required.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        flash('reg_error', 'Please enter a valid email address.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } else {
        $stmt = db()->prepare(
            'INSERT INTO registrations
             (tenant_id, full_name, mobile, email, child_name, grade, syllabus, city, heard_about, interested_service, message)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            tenant_id(),
            $data['full_name'], $data['mobile'], $data['email'] ?: null,
            $data['child_name'] ?: null, $data['grade'] ?: null, $data['syllabus'] ?: null,
            $data['city'] ?: null, $data['heard_about'] ?: null, $interested_service,
            $data['message'] ?: null,
        ]);

        // If a priced course was selected, go straight to checkout.
        // Pre-fill checkout with the details already entered on this form.
        if ($service_id > 0 && $interested_service !== null) {
            start_session();
            $_SESSION['checkout_prefill'] = [
                'name'  => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['mobile'],
            ];
            redirect(base_url() . 'checkout.php?service=' . $service_id);
        }

        flash('reg_success', 'Thank you! Your registration has been received successfully.');
        redirect(base_url() . 'register.php#register-form');
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
      <p class="text-muted">Fill in your details and we will get in touch with you.</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php include __DIR__ . '/includes/register-form.php'; ?>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
