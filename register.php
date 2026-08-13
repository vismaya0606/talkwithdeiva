<?php
require_once __DIR__ . '/config/functions.php';
start_session();

$tid = tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

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
    ];

    if ($data['full_name'] === '' || $data['mobile'] === '') {
        flash('reg_error', 'Parent name and WhatsApp number are required.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        flash('reg_error', 'Please enter a valid email address.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } else {
        db()->prepare(
            'INSERT INTO registrations
             (tenant_id, full_name, mobile, email, child_name, grade, syllabus,
              city, heard_about, message)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $tid,
            $data['full_name'], $data['mobile'], $data['email'] ?: null,
            $data['child_name'] ?: null, $data['grade'] ?: null,
            $data['syllabus']   ?: null, $data['city']   ?: null,
            $data['heard_about'] ?: null, $data['message'] ?: null,
        ]);

        // Registration saved — go to payment page.
        redirect(base_url() . 'payment.php');
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
<?php include __DIR__ . '/includes/footer.php'; ?>
