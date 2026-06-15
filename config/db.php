<?php
/**
 * Database connection (PDO + prepared statements).
 *
 * Credentials are loaded in this order so that deploying via Git never
 * overwrites your real settings:
 *   1. config/credentials.php  (untracked — written by install.php)
 *   2. environment variables   (DB_HOST / DB_NAME / DB_USER / DB_PASS)
 *   3. the defaults below       (edit these only for local testing)
 */

declare(strict_types=1);

$__cred = __DIR__ . '/credentials.php';
if (is_file($__cred)) {
    require $__cred;   // defines DB_* constants
}

if (!defined('DB_HOST'))    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME'))    define('DB_NAME', getenv('DB_NAME') ?: 'talkwithdeiva');
if (!defined('DB_USER'))    define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS'))    define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO connection.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            // Do not leak credentials/SQL details to the visitor.
            exit('Database connection failed. Please try again later.');
        }
    }

    return $pdo;
}
