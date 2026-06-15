<?php
require_once __DIR__ . '/../config/functions.php';
require_superadmin();

/** Default settings seeded for every new tenant. */
function seed_tenant_settings(int $tid, string $siteName): void
{
    $defaults = [
        'site_name'        => $siteName,
        'primary_color'    => '#0d6efd',
        'secondary_color'  => '#6610f2',
        'footer_text'      => '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.',
        'hero_title'       => 'Welcome to ' . $siteName,
        'hero_subtitle'    => 'Your designation here',
        'hero_tagline'     => 'Your inspiring tagline goes here.',
        'about_title'      => 'About Me',
        'about_content'    => 'Tell your story here.',
        'achievements'     => "10+ Years Experience\n100+ Happy Clients",
        'contact_phone'    => '',
        'contact_whatsapp' => '',
        'contact_email'    => '',
        'contact_address'  => '',
        'meta_title'       => $siteName,
        'meta_description' => 'Official website of ' . $siteName,
        'meta_keywords'    => '',
    ];
    foreach ($defaults as $k => $v) {
        save_setting($k, $v, $tid);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name   = trim($_POST['name'] ?? '');
        $domain = strtolower(trim($_POST['domain'] ?? ''));
        $domain = preg_replace('/^www\./', '', $domain);
        $aUser  = trim($_POST['admin_user'] ?? '');
        $aPass  = $_POST['admin_pass'] ?? '';
        $aEmail = trim($_POST['admin_email'] ?? '');

        if ($name === '' || $domain === '' || $aUser === '' || strlen($aPass) < 6) {
            flash('admin_err', 'All fields required; password must be at least 6 characters.');
        } else {
            try {
                db()->beginTransaction();
                $stmt = db()->prepare("INSERT INTO tenants (name,domain,status) VALUES (?,?, 'active')");
                $stmt->execute([$name, $domain]);
                $newTid = (int) db()->lastInsertId();

                $stmt = db()->prepare('INSERT INTO admins (tenant_id,name,username,email,password,role) VALUES (?,?,?,?,?, "admin")');
                $stmt->execute([$newTid, $name . ' Admin', $aUser, $aEmail, password_hash($aPass, PASSWORD_DEFAULT)]);

                seed_tenant_settings($newTid, $name);
                db()->commit();
                flash('admin_ok', 'Tenant created successfully.');
            } catch (PDOException $e) {
                db()->rollBack();
                flash('admin_err', 'Could not create tenant (domain or username may already exist).');
            }
        }
    } elseif ($action === 'toggle') {
        $stmt = db()->prepare("UPDATE tenants SET status = IF(status='active','inactive','active') WHERE id=?");
        $stmt->execute([(int)$_POST['id']]);
        flash('admin_ok', 'Tenant status changed.');
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM tenants WHERE id=?');
        $stmt->execute([(int)$_POST['id']]);  // cascades to all tenant data
        flash('admin_ok', 'Tenant deleted.');
    }
    redirect('tenants.php');
}

$tenants = db()->query(
    'SELECT t.*,
        (SELECT COUNT(*) FROM registrations r WHERE r.tenant_id=t.id) AS regs,
        (SELECT COUNT(*) FROM admins a WHERE a.tenant_id=t.id) AS admins
     FROM tenants t ORDER BY t.id'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Super Admin | Tenants</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container">
    <span class="navbar-brand"><i class="bi bi-building"></i> Super Admin — Tenant Management</span>
    <a href="../admin/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>

<div class="container pb-5">
  <?php if ($m = flash('admin_ok')): ?><div class="alert alert-success"><?= e($m) ?></div><?php endif; ?>
  <?php if ($m = flash('admin_err')): ?><div class="alert alert-danger"><?= e($m) ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>Add New Tenant</strong></div>
        <div class="card-body">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">
            <div class="mb-2"><label class="form-label">Business / Site Name</label>
              <input class="form-control" name="name" required></div>
            <div class="mb-2"><label class="form-label">Domain (e.g. client.com)</label>
              <input class="form-control" name="domain" required placeholder="example.com"></div>
            <hr>
            <div class="mb-2"><label class="form-label">Admin Username</label>
              <input class="form-control" name="admin_user" required></div>
            <div class="mb-2"><label class="form-label">Admin Email</label>
              <input type="email" class="form-control" name="admin_email"></div>
            <div class="mb-3"><label class="form-label">Admin Password (min 6)</label>
              <input type="text" class="form-control" name="admin_pass" required minlength="6"></div>
            <button class="btn btn-primary w-100">Create Tenant</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>All Tenants (<?= count($tenants) ?>)</strong></div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr>
              <th>#</th><th>Name</th><th>Domain</th><th>Status</th><th>Admins</th><th>Regs</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($tenants as $t): ?>
              <tr>
                <td><?= (int)$t['id'] ?></td>
                <td><?= e($t['name']) ?></td>
                <td><a href="http://<?= e($t['domain']) ?>" target="_blank"><?= e($t['domain']) ?></a></td>
                <td><span class="badge bg-<?= $t['status']==='active'?'success':'secondary' ?>"><?= e($t['status']) ?></span></td>
                <td><?= (int)$t['admins'] ?></td>
                <td><?= (int)$t['regs'] ?></td>
                <td class="text-end text-nowrap">
                  <form method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                    <button class="btn btn-sm btn-outline-secondary" title="Toggle status"><i class="bi bi-power"></i></button>
                  </form>
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete tenant and ALL its data?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <p class="text-muted small mt-3">
        Point each tenant's domain to this same hosting account (Addon Domain in cPanel).
        The site auto-detects the tenant from the visited domain. Tenant admins log in at
        <code>https://theirdomain.com/admin/login.php</code>.
      </p>
    </div>
  </div>
</div>
</body>
</html>
