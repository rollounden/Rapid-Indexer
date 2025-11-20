<?php
// Web-based migration script
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['uid']) || ($_SESSION['role'] ?? '') !== 'admin') {
    die('Access denied. Please login as admin.');
}

require_once __DIR__ . '/src/Db.php';

echo "<h1>Database Migration</h1>";
echo "<pre>";

try {
    $pdo = Db::conn();
    
    // Create settings table
    echo "Checking 'settings' table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(50) PRIMARY KEY,
        `value` TEXT NULL,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Done.\n";

    // Insert default settings if not exist
    echo "Inserting default settings...\n";
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (`key`, `value`) VALUES (?, ?)");
    $stmt->execute(['indexing_provider', 'speedyindex']);
    $stmt->execute(['ralfy_api_key', '']);
    echo "Done.\n";

    // Update tasks table
    echo "Checking 'tasks' table columns...\n";
    // Check if provider column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'provider'");
    if (!$stmt->fetch()) {
        echo "Adding 'provider' column...\n";
        $pdo->exec("ALTER TABLE tasks ADD COLUMN provider VARCHAR(20) NOT NULL DEFAULT 'speedyindex' AFTER search_engine");
    } else {
        echo "'provider' column already exists.\n";
    }

    // Check if provider_task_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'provider_task_id'");
    if (!$stmt->fetch()) {
        echo "Adding 'provider_task_id' column...\n";
        $pdo->exec("ALTER TABLE tasks ADD COLUMN provider_task_id VARCHAR(100) NULL AFTER speedyindex_task_id");
    } else {
        echo "'provider_task_id' column already exists.\n";
    }
    
    echo "\nMigration completed successfully!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo '<p><a href="/admin.php">Back to Admin Dashboard</a></p>';

