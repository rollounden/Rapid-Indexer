<?php
// debug_sockets.php
$sockets = [
    '/var/lib/mysql/mysql.sock',
    '/var/run/mysqld/mysqld.sock',
    '/tmp/mysql.sock'
];

echo "Testing file_exists from CLI:\n";
foreach ($sockets as $sock) {
    if (file_exists($sock)) {
        echo "[OK] $sock exists\n";
    } else {
        echo "[FAIL] $sock not found\n";
    }
}

echo "\nTesting PDO connections:\n";
require_once __DIR__ . '/config/config.php'; // to get DB_NAME, DB_USER, DB_PASS
foreach ($sockets as $sock) {
    echo "Trying $sock ... ";
    try {
        $dsn = 'mysql:unix_socket=' . $sock . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        echo "SUCCESS\n";
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
?>
