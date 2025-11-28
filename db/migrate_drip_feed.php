<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Db.php';

echo "Starting Drip Feed Migration...\n";

$pdo = Db::conn();

try {
    // 1. Create task_batches table
    echo "Creating task_batches table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS task_batches (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        task_id BIGINT UNSIGNED NOT NULL,
        provider_batch_id VARCHAR(128) NULL,
        link_count INT NOT NULL DEFAULT 0,
        status ENUM('pending','submitted','completed') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_batches_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo "task_batches table created/checked.\n";

    // 2. Add columns to tasks
    echo "Adding columns to tasks table...\n";
    
    $columns = [
        'is_drip_feed' => "TINYINT(1) NOT NULL DEFAULT 0",
        'drip_percentage' => "INT NULL DEFAULT 10",
        'drip_interval_minutes' => "INT NULL DEFAULT 1440",
        'next_run_at' => "DATETIME NULL"
    ];

    foreach ($columns as $col => $def) {
        try {
            $pdo->exec("ALTER TABLE tasks ADD COLUMN $col $def");
            echo "Added column $col\n";
        } catch (PDOException $e) {
            // 1060 = Duplicate column name
            if (strpos($e->getMessage(), '1060') !== false) {
                echo "Column $col already exists\n";
            } else {
                throw $e;
            }
        }
    }

    // 3. Add batch_id to task_links
    echo "Adding batch_id to task_links table...\n";
    try {
        $pdo->exec("ALTER TABLE task_links ADD COLUMN batch_id BIGINT UNSIGNED NULL");
        echo "Added column batch_id\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), '1060') !== false) {
            echo "Column batch_id already exists\n";
        } else {
            throw $e;
        }
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

