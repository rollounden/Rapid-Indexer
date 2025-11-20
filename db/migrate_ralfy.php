<?php
require_once __DIR__ . '/../src/Db.php';

try {
    $pdo = Db::conn();
    
    // Create settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(50) PRIMARY KEY,
        `value` TEXT NULL,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Insert default settings if not exist
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (`key`, `value`) VALUES (?, ?)");
    $stmt->execute(['indexing_provider', 'speedyindex']);
    $stmt->execute(['ralfy_api_key', '']);

    // Update tasks table
    // Check if provider column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'provider'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN provider VARCHAR(20) NOT NULL DEFAULT 'speedyindex' AFTER search_engine");
    }

    // Check if external_id column exists (generic id)
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'provider_task_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN provider_task_id VARCHAR(100) NULL AFTER speedyindex_task_id");
    }
    
    echo "Database migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

