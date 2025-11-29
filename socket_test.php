<?php
// socket_test.php
// Testing connection with explicit socket from CLI
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Db.php';

try {
    $dsn = 'mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "SUCCESS: Connected via /var/lib/mysql/mysql.sock\n";
} catch (PDOException $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
?>
