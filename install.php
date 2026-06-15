<?php
/**
 * One-time web installer.
 *
 * Visit https://yourdomain.com/install.php after uploading the files.
 * It will:
 *   1. Test your MySQL credentials
 *   2. Create the database tables (from database/schema.sql)
 *   3. Set your site's domain for the default tenant
 *   4. Set your Admin and Super Admin passwords
 *   5. Write config/db.php
 *   6. Lock itself (install.lock) so it cannot run again
 *
 * Delete this file after a successful install.
 */
declare(strict_types=1);

$ROOT      = __DIR__;
$LOCK      = $ROOT . '/install.lock';
$DB_CONFIG = $ROOT . '/config/db.php';
$SCHEMA    = $ROOT . '/database/schema.sql';

$errors  = [];
$success = false;

/* Already installed? */
if (file_exists($LOCK)) {
    render_page('<div class="alert alert-success"><strong>Already installed.</strong> '
        . 'For security, please delete <code>install.php</code> from your server.</div>');
    exit;
}

function php_str(string $v): string
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $v) . "'";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host   = trim($_POST['db_host'] ?? 'localhost');
    $name   = trim($_POST['db_name'] ?? '');
    $user   = trim($_POST['db_user'] ?? '');
    $pass   = (string)($_POST['db_pass'] ?? '');
    $domain = strtolower(trim($_POST['domain'] ?? ''));
    $domain = preg_replace('/^https?:\/\//', '', $domain);
    $domain = preg_replace('/^www\./', '', rtrim($domain, '/'));

    $adminUser  = trim($_POST['admin_user'] ?? 'admin');
    $adminPass  = (string)($_POST['admin_pass'] ?? '');
    $superPass  = (string)($_POST['super_pass'] ?? '');

    if ($name === '' || $user === '') {
        $errors[] = 'Database name and user are required.';
    }
    if ($domain === '') {
        $errors[] = 'Website domain is required.';
    }
    if (strlen($adminPass) < 6 || strlen($superPass) < 6) {
        $errors[] = 'Both passwords must be at least 6 characters.';
    }
    if (!file_exists($SCHEMA)) {
        $errors[] = 'database/schema.sql is missing. Re-upload the project files.';
    }

    if (!$errors) {
        try {
            $pdo = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $errors[] = 'Could not connect to the database. Check your credentials and that the '
                . 'database exists (create it in cPanel → MySQL Databases first).';
        }
    }

    if (!$errors) {
        // Split schema into statements; run DDL/SET immediately, seed only if empty.
        $sql = file_get_contents($SCHEMA);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql); // strip comment lines
        $statements = array_filter(array_map('trim', explode(";\n", $sql)));

        $seeds = [];
        try {
            foreach ($statements as $stmt) {
                $stmt = trim($stmt, " \t\r\n;");
                if ($stmt === '') {
                    continue;
                }
                if (stripos($stmt, 'INSERT') === 0) {
                    $seeds[] = $stmt;
                } else {
                    $pdo->exec($stmt);
                }
            }

            $hasData = (int) $pdo->query('SELECT COUNT(*) FROM tenants')->fetchColumn() > 0;
            if (!$hasData) {
                foreach ($seeds as $stmt) {
                    $pdo->exec($stmt);
                }
            }

            // Apply user choices.
            $pdo->prepare('UPDATE tenants SET domain = ? WHERE id = 1')
                ->execute([$domain]);

            $pdo->prepare('UPDATE admins SET username = ?, password = ? WHERE role = "admin" AND tenant_id = 1')
                ->execute([$adminUser, password_hash($adminPass, PASSWORD_DEFAULT)]);

            $pdo->prepare('UPDATE admins SET password = ? WHERE role = "superadmin"')
                ->execute([password_hash($superPass, PASSWORD_DEFAULT)]);

            // Write config/db.php
            $config = "<?php\n"
                . "declare(strict_types=1);\n\n"
                . "define('DB_HOST', " . php_str($host) . ");\n"
                . "define('DB_NAME', " . php_str($name) . ");\n"
                . "define('DB_USER', " . php_str($user) . ");\n"
                . "define('DB_PASS', " . php_str($pass) . ");\n"
                . "define('DB_CHARSET', 'utf8mb4');\n\n"
                . "function db(): PDO\n{\n"
                . "    static \$pdo = null;\n"
                . "    if (\$pdo === null) {\n"
                . "        \$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);\n"
                . "        try {\n"
                . "            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [\n"
                . "                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n"
                . "                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
                . "                PDO::ATTR_EMULATE_PREPARES   => false,\n"
                . "            ]);\n"
                . "        } catch (PDOException \$e) {\n"
                . "            http_response_code(500);\n"
                . "            exit('Database connection failed. Please try again later.');\n"
                . "        }\n"
                . "    }\n"
                . "    return \$pdo;\n}\n";

            if (@file_put_contents($DB_CONFIG, $config) === false) {
                $errors[] = 'Tables created, but config/db.php is not writable. '
                    . 'Set its permissions to 644 (or paste the credentials manually) and retry.';
            } else {
                @file_put_contents($LOCK, 'installed ' . date('c'));
                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = 'Database setup failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
    }
}

/* ---------------- View ---------------- */
function render_page(string $body): void
{
    ?><!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install — Website Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container" style="max-width:640px">
  <div class="text-center my-4"><h3>🚀 Website Installer</h3></div>
  <?= $body ?>
</div></body></html><?php
}

if ($success) {
    render_page(
        '<div class="alert alert-success">'
        . '<h5 class="mb-2">✅ Installation complete!</h5>'
        . 'Your database is set up and credentials saved.</div>'
        . '<div class="alert alert-danger"><strong>Important:</strong> delete <code>install.php</code> '
        . 'from your server now (cPanel → File Manager → right-click → Delete).</div>'
        . '<div class="d-grid gap-2">'
        . '<a class="btn btn-primary" href="index.php">Go to your website</a>'
        . '<a class="btn btn-outline-secondary" href="admin/login.php">Go to Admin Login</a>'
        . '</div>'
    );
    exit;
}

$errHtml = '';
foreach ($errors as $e) {
    $errHtml .= '<div class="alert alert-danger py-2 mb-2">' . htmlspecialchars($e, ENT_QUOTES) . '</div>';
}

$v = fn(string $k, string $d = '') => htmlspecialchars((string)($_POST[$k] ?? $d), ENT_QUOTES);
$guessDomain = htmlspecialchars(preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? ''), ENT_QUOTES);

ob_start();
?>
<?= $errHtml ?>
<form method="post" class="card shadow-sm">
  <div class="card-body">
    <h6 class="text-uppercase text-muted small">Database (from cPanel → MySQL Databases)</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label">DB Host</label>
        <input class="form-control" name="db_host" value="<?= $v('db_host', 'localhost') ?>"></div>
      <div class="col-md-6"><label class="form-label">Database Name</label>
        <input class="form-control" name="db_name" required value="<?= $v('db_name') ?>"></div>
      <div class="col-md-6"><label class="form-label">Database User</label>
        <input class="form-control" name="db_user" required value="<?= $v('db_user') ?>"></div>
      <div class="col-md-6"><label class="form-label">Database Password</label>
        <input type="text" class="form-control" name="db_pass" value="<?= $v('db_pass') ?>"></div>
    </div>

    <h6 class="text-uppercase text-muted small">Website</h6>
    <div class="mb-4">
      <label class="form-label">Your Domain (without www)</label>
      <input class="form-control" name="domain" required placeholder="example.com"
             value="<?= $v('domain', $guessDomain) ?>">
    </div>

    <h6 class="text-uppercase text-muted small">Admin Accounts</h6>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Admin Username</label>
        <input class="form-control" name="admin_user" value="<?= $v('admin_user', 'admin') ?>"></div>
      <div class="col-md-6"><label class="form-label">Admin Password (min 6)</label>
        <input type="text" class="form-control" name="admin_pass" required minlength="6"></div>
      <div class="col-md-6"><label class="form-label">Super Admin Password (min 6)</label>
        <input type="text" class="form-control" name="super_pass" required minlength="6">
        <small class="text-muted">Super Admin username stays <code>superadmin</code></small></div>
    </div>
  </div>
  <div class="card-footer bg-white text-end">
    <button class="btn btn-primary">Install Now</button>
  </div>
</form>
<p class="text-muted small mt-3 text-center">
  Create the database & user in cPanel first, then run this once.
</p>
<?php
render_page(ob_get_clean());
