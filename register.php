<?php
require_once __DIR__ . '/config/functions.php';
start_session();

$tid = tenant_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();

    $allowed_grades = [
        'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5',
        'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10',
    ];
    $allowed_syllabus = [
        'CBSE', 'ICSE', 'State Board', 'IB', 'Cambridge / IGCSE', 'Other',
    ];

    $data = [
        'full_name'  => trim($_POST['full_name']  ?? ''),
        'mobile'     => trim($_POST['mobile']      ?? ''),
        'email'      => trim($_POST['email']       ?? ''),
        'child_name' => trim($_POST['child_name']  ?? ''),
        'grade'      => trim($_POST['grade']       ?? ''),
        'syllabus'   => trim($_POST['syllabus']    ?? ''),
    ];

    // Whitelist radio values to prevent arbitrary input.
    if (!in_array($data['grade'],    $allowed_grades,   true)) { $data['grade']    = ''; }
    if (!in_array($data['syllabus'], $allowed_syllabus, true)) { $data['syllabus'] = ''; }

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
    } elseif ($data['child_name'] === '') {
        flash('reg_error', 'Please enter your child\'s name.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['grade'] === '') {
        flash('reg_error', 'Please select your child\'s current grade.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } elseif ($data['syllabus'] === '') {
        flash('reg_error', 'Please select the syllabus / board your child follows.');
        $_SESSION['reg_old'] = $data;
        redirect(base_url() . 'register.php#register-form');
    } else {
        db()->prepare(
            'INSERT INTO registrations
             (tenant_id, full_name, mobile, email, child_name, grade, syllabus)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([
            $tid,
            $data['full_name'], $data['mobile'], $data['email'],
            $data['child_name'], $data['grade'], $data['syllabus'],
        ]);

        // Registration saved — go to payment page.
        redirect(base_url() . 'payment.php');
    }
}

$page_title       = 'Register | ' . setting('site_name');
$page_description = 'Plan Your Child\'s Future Before 10th! — Webinar registration with ' . setting('site_name') . '.';
include __DIR__ . '/includes/header.php';
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <p class="text-uppercase fw-semibold brand-text small mb-1 ls-wide">Webinar Registration</p>
      <h1 class="section-title">Plan Your Child's Future<br>Before 10th!</h1>
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
