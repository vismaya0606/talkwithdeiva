<?php
require_once __DIR__ . '/config/functions.php';
start_session();

$tid = tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $allowed_professions = [
        'Parent of a child aged 0–8 years', 'Expecting Parent',
        'Primary School Teacher', 'Parent & Teacher', 'Other',
    ];
    $allowed_ages = [
        'Expecting', '0–1 year', '2–3 years', '4–5 years', '6–8 years',
        'More than one child in the 0–8 age group', 'Not Applicable – Teacher',
    ];
    $allowed_heard = [
        'Instagram', 'Facebook', 'WhatsApp', 'YouTube',
        'Friend / Family', 'School / Teacher', 'Other',
    ];

    $data = [
        'full_name'   => trim($_POST['full_name']   ?? ''),
        'mobile'      => trim($_POST['mobile']       ?? ''),
        'email'       => trim($_POST['email']        ?? ''),
        'profession'  => trim($_POST['profession']   ?? ''),
        'grade'       => trim($_POST['grade']        ?? ''),
        'heard_about' => trim($_POST['heard_about']  ?? ''),
        'message'     => trim($_POST['message']      ?? ''),
        'confirmed'   => trim($_POST['confirmed']    ?? ''),
    ];

    // Whitelist radio values to prevent arbitrary input.
    if (!in_array($data['profession'],  $allowed_professions, true)) { $data['profession']  = ''; }
    if (!in_array($data['grade'],       $allowed_ages,        true)) { $data['grade']       = ''; }
    if (!in_array($data['heard_about'], $allowed_heard,       true)) { $data['heard_about'] = ''; }
    if (!in_array($data['message'],     ['Yes', 'No'],        true)) { $data['message']     = ''; }

    if ($data['full_name'] === '' || $data['mobile'] === '') {
        flash('reg_error', 'Full name and WhatsApp number are required.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['email'] === '') {
        flash('reg_error', 'Email address is required.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        flash('reg_error', 'Please enter a valid email address.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['profession'] === '') {
        flash('reg_error', 'Please select what best describes you.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['confirmed'] !== 'yes') {
        flash('reg_error', 'Please confirm that the information provided is correct.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } else {
        db()->prepare(
            'INSERT INTO registrations
             (tenant_id, full_name, mobile, email, profession, grade, heard_about, message)
             VALUES (?,?,?,?,?,?,?,?)'
        )->execute([
            $tid,
            $data['full_name'], $data['mobile'], $data['email'],
            $data['profession'] ?: null, $data['grade']       ?: null,
            $data['heard_about'] ?: null, $data['message']    ?: null,
        ]);

        // Registration saved — go to payment page.
        redirect(base_url() . 'payment.php');
    }
}

$page_title       = 'Register | ' . setting('site_name');
$page_description = '0–8 Years Parenting & Education Webinar registration with ' . setting('site_name') . '.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <p class="text-uppercase fw-semibold brand-text small mb-1 ls-wide">Webinar Registration</p>
      <h1 class="section-title">0–8 Years Parenting &amp;<br>Education Webinar</h1>
      <p class="text-muted">Fill in your details below and proceed to pay securely.</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <?php include __DIR__ . '/includes/register-form.php'; ?>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
