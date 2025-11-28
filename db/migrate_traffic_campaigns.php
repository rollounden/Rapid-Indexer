<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Db.php';

try {
    $pdo = Db::conn();
    
    echo "Migrating database for traffic campaigns...\n";

    // 1. Update 'tasks' type enum to include 'traffic_campaign'
    // Current types: indexer, checker, traffic
    // We need to change the column definition
    $pdo->exec("ALTER TABLE tasks MODIFY COLUMN type ENUM('indexer', 'checker', 'traffic', 'traffic_campaign') NOT NULL");
    echo "Updated 'type' column in tasks table.\n";

    // 2. Create traffic_schedule table
    $sql = "CREATE TABLE IF NOT EXISTS traffic_schedule (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        task_id BIGINT UNSIGNED NOT NULL,
        scheduled_at DATETIME NOT NULL,
        quantity INT NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
        provider_order_id VARCHAR(64) NULL,
        execution_log TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_traffic_schedule_task (task_id),
        INDEX idx_traffic_schedule_pending (status, scheduled_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Created 'traffic_schedule' table.\n";

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}

