<?php
/** Admin layout header + sidebar. Requires an authenticated admin. */
require_once __DIR__ . '/../../config/functions.php';
$admin = require_admin();
$active = $active ?? '';
$tid = admin_tenant_id();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin | <?= e(setting('site_name', 'Dashboard', $tid)) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <aside class="col-lg-2 col-md-3 admin-sidebar p-3 text-white">
      <h5 class="mb-4"><i class="bi bi-speedometer2 me-1"></i> Admin Panel</h5>
      <nav class="nav flex-column gap-1">
        <a href="dashboard.php"     class="<?= $active==='dashboard'?'active':'' ?>"><i class="bi bi-grid me-2"></i>Dashboard</a>
        <a href="homepage.php"      class="<?= $active==='homepage'?'active':'' ?>"><i class="bi bi-house me-2"></i>Homepage</a>
        <a href="services.php"      class="<?= $active==='services'?'active':'' ?>"><i class="bi bi-stars me-2"></i>Services</a>
        <a href="gallery.php"       class="<?= $active==='gallery'?'active':'' ?>"><i class="bi bi-images me-2"></i>Gallery</a>
        <a href="testimonials.php"  class="<?= $active==='testimonials'?'active':'' ?>"><i class="bi bi-chat-quote me-2"></i>Testimonials</a>
        <a href="registrations.php" class="<?= $active==='registrations'?'active':'' ?>"><i class="bi bi-people me-2"></i>Registrations</a>
        <a href="settings.php"      class="<?= $active==='settings'?'active':'' ?>"><i class="bi bi-palette me-2"></i>Theme &amp; Settings</a>
        <?php if ($admin['role'] === 'superadmin'): ?>
          <a href="../superadmin/tenants.php"><i class="bi bi-building me-2"></i>Super Admin</a>
        <?php endif; ?>
        <hr class="text-secondary">
        <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
      </nav>
    </aside>

    <!-- Main -->
    <main class="col-lg-10 col-md-9 py-4 px-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><?= e($page_heading ?? 'Dashboard') ?></h3>
        <div class="text-end small text-muted">
          <i class="bi bi-person-circle"></i> <?= e($admin['name']) ?>
          &middot; <a href="../index.php" target="_blank">View Site</a>
        </div>
      </div>
      <?php if ($m = flash('admin_ok')): ?>
        <div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
      <?php if ($m = flash('admin_err')): ?>
        <div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>
