<?php
// Database config
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'auth_demo');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default is empty password for root

// Policy
define('MAX_FAILED_ATTEMPTS', 3);          // attempts before lock
define('LOCK_DURATION_SECONDS', 15 * 60);  // 15 minutes lock

// Logging
define('LOG_DIR', __DIR__ . '/logs');
if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0750, true);

// Session
if (session_status() === PHP_SESSION_NONE) session_start();

function getPDO() {
    static $pdo;
    if ($pdo) return $pdo;
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    return $pdo;
}
