<?php
/**
 * CampusConnect Security Lab - Database Configuration
 * Adjust these values to match your local MariaDB/MySQL setup.
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'campusconnect_lab');
define('DB_USER',    'root');
define('DB_PASS',    '');          // Leave empty if your local MySQL has no root password
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a PDO connection. Throws a PDOException on failure.
 *
 * @return PDO
 */
function get_db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
