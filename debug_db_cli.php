<?php
// debug_db_cli.php
require_once __DIR__ . '/config/config.php'; // Forgot this!
require_once __DIR__ . '/src/Db.php';
try {
    $pdo = Db::conn();
    echo "Connected successfully.\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>