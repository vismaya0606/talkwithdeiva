<?php
require_once __DIR__ . '/config/functions.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $data = [
        'full_name'          => trim($_POST['full_name'] ?? ''),
        'mobile'             => trim($_POST['mobile'] ?? ''),
        'email'              => trim($_POST['email'] ?? ''),
        'city'               => trim($_POST['city'] ?? ''),
        'state'              => trim($_POST['state'] ?? ''),
        'profession'         => trim($_POST['profession'] ?? ''),
        'interested_service' => trim($_POST['interested_service'] ?? ''),
        'message'            => trim($_POST['message'] ?? ''),
    ];

    // Validation: name + mobile required, email optional but validated if given.
    if ($data['full_name'] === '' || $data['mobile'] === '') {
        flash('reg_error', 'Name and mobile number are required.');
        $_SESSION['reg_old'] = $data;
    } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        flash('reg_error', 'Please enter a valid email address.');
        $_SESSION['reg_old'] = $data;
    } else {
        $stmt = db()->prepare(
            'INSERT INTO registrations
             (tenant_id, full_name, mobile, email, city, state, profession, interested_service, message)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            tenant_id(),
            $data['full_name'], $data['mobile'], $data['email'] ?: null,
            $data['city'], $data['state'], $data['profession'],
            $data['interested_service'], $data['message'],
        ]);
        flash('reg_success', 'Thank you! Your registration has been received successfully.');
    }

    redirect(base_url() . 'register.php#register-form');
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
