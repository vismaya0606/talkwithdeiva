<?php
/**
 * Database connection (PDO + prepared statements).
 *
 * Edit the four constants below to match your cPanel MySQL database.
 * In cPanel: "MySQL Databases" -> create DB + user, then add the user
 * to the database with ALL PRIVILEGES.
 */

declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'talkwithdeiva');   // your database name
define('DB_USER', 'root');            // your database user
define('DB_PASS', '');                // your database password
define('DB_CHARSET', 'utf8mb4');

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
