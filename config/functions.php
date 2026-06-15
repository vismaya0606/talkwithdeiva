<?php
/**
 * Core helper functions: tenant resolution, settings, security,
 * authentication, uploads and small view helpers.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* ------------------------------------------------------------------ *
 *  Session
 * ------------------------------------------------------------------ */
function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        ]);
        session_start();
    }
}

/* ------------------------------------------------------------------ *
 *  Output escaping
 * ------------------------------------------------------------------ */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/* ------------------------------------------------------------------ *
 *  Tenant resolution (multi-tenant)
 *  A tenant is matched by the request host. www. is stripped and the
 *  port is ignored. Falls back to tenant id 1 for local development.
 * ------------------------------------------------------------------ */
function current_tenant(): array
{
    static $tenant = null;
    if ($tenant !== null) {
        return $tenant;
    }

    $host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
    $host = preg_replace('/:\d+$/', '', $host);   // strip port
    $host = preg_replace('/^www\./', '', $host);  // strip www.

    $stmt = db()->prepare(
        "SELECT * FROM tenants WHERE domain = ? AND status = 'active' LIMIT 1"
    );
    $stmt->execute([$host]);
    $row = $stmt->fetch();

    if (!$row) {
        // Fallback for local/dev: first active tenant.
        $row = db()->query(
            "SELECT * FROM tenants WHERE status = 'active' ORDER BY id ASC LIMIT 1"
        )->fetch();
    }

    if (!$row) {
        http_response_code(503);
        exit('No active tenant configured for this domain.');
    }

    return $tenant = $row;
}

function tenant_id(): int
{
    return (int) current_tenant()['id'];
}

/* ------------------------------------------------------------------ *
 *  Settings (per tenant key/value store, cached per request)
 * ------------------------------------------------------------------ */
function all_settings(?int $tid = null): array
{
    static $cache = [];
    $tid = $tid ?? tenant_id();

    if (!isset($cache[$tid])) {
        $stmt = db()->prepare('SELECT setting_key, setting_value FROM settings WHERE tenant_id = ?');
        $stmt->execute([$tid]);
        $cache[$tid] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $cache[$tid];
}

function setting(string $key, string $default = '', ?int $tid = null): string
{
    $all = all_settings($tid);
    return isset($all[$key]) && $all[$key] !== null ? (string) $all[$key] : $default;
}

function save_setting(string $key, string $value, ?int $tid = null): void
{
    $tid  = $tid ?? tenant_id();
    $stmt = db()->prepare(
        'INSERT INTO settings (tenant_id, setting_key, setting_value)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->execute([$tid, $key, $value]);
}

/* ------------------------------------------------------------------ *
 *  CSRF protection
 * ------------------------------------------------------------------ */
function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    start_session();
    $token = $_POST['csrf'] ?? '';
    return is_string($token) && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $token);
}

function require_csrf(): void
{
    if (!csrf_verify()) {
        http_response_code(419);
        exit('Invalid or expired form token. Please go back and try again.');
    }
}

/* ------------------------------------------------------------------ *
 *  Authentication
 * ------------------------------------------------------------------ */
function admin_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        return false;
    }

    // Tenant admins can only log in from their own domain.
    if ($admin['role'] === 'admin' && (int) $admin['tenant_id'] !== tenant_id()) {
        return false;
    }

    start_session();
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id'        => (int) $admin['id'],
        'name'      => $admin['name'],
        'role'      => $admin['role'],
        'tenant_id' => $admin['tenant_id'] !== null ? (int) $admin['tenant_id'] : null,
    ];
    return true;
}

function current_admin(): ?array
{
    start_session();
    return $_SESSION['admin'] ?? null;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin || !in_array($admin['role'], ['admin', 'superadmin'], true)) {
        redirect('login.php');
    }
    return $admin;
}

function require_superadmin(): array
{
    $admin = current_admin();
    if (!$admin || $admin['role'] !== 'superadmin') {
        redirect('../admin/login.php');
    }
    return $admin;
}

/**
 * The tenant id the logged-in admin operates on. Super admins may act
 * on a tenant chosen via ?tenant= / session, normal admins are locked
 * to their own tenant.
 */
function admin_tenant_id(): int
{
    $admin = current_admin();
    if ($admin && $admin['role'] === 'admin') {
        return (int) $admin['tenant_id'];
    }
    // For a tenant-bound admin panel accessed by a superadmin, default
    // to the tenant resolved from the current domain.
    return $admin['tenant_id'] ?? tenant_id();
}

function logout(): void
{
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ------------------------------------------------------------------ *
 *  Secure file upload
 * ------------------------------------------------------------------ */
function upload_image(array $file, string $subdir): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > 4 * 1024 * 1024) { // 4 MB limit
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'image/x-icon'      => 'ico',
        'image/vnd.microsoft.icon' => 'ico',
        'image/svg+xml'     => 'svg',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }
    $ext = $allowed[$mime];

    $dir = __DIR__ . '/../assets/uploads/' . trim($subdir, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return null;
    }

    // Public web path (relative to web root).
    return 'assets/uploads/' . trim($subdir, '/') . '/' . $name;
}

/* ------------------------------------------------------------------ *
 *  Small helpers
 * ------------------------------------------------------------------ */
function asset(string $path): string
{
    return base_url() . ltrim($path, '/');
}

/** Absolute base URL of the site, e.g. https://example.com/ */
function base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/';
}

/** Renders a stored image path, supporting both uploads and full URLs. */
function img_src(string $path, string $fallback = ''): string
{
    if ($path === '') {
        return $fallback;
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return asset($path);
}

function flash(string $key, ?string $msg = null): ?string
{
    start_session();
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}
