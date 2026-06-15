<?php
require_once __DIR__ . '/../config/functions.php';
start_session();

if (current_admin()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if (admin_login($u, $p)) {
        $admin = current_admin();
        redirect($admin['role'] === 'superadmin' ? '../superadmin/tenants.php' : 'dashboard.php');
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-dark">
<div class="container">
  <div class="row justify-content-center align-items-center" style="min-height:100vh">
    <div class="col-md-5 col-lg-4">
      <div class="card shadow border-0">
        <div class="card-body p-4 p-md-5">
          <h4 class="text-center mb-4"><i class="bi bi-shield-lock"></i> Admin Login</h4>
          <?php if ($error): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
          <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Sign In</button>
          </form>
        </div>
      </div>
      <p class="text-center text-secondary small mt-3">Authorised access only</p>
    </div>
  </div>
</div>
</body>
</html>
