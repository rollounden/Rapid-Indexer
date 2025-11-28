<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Db.php';

try {
    $pdo = Db::conn();
    
    echo "Migrating tasks table for traffic support...\n";

    // 1. Modify 'type' enum to include 'traffic'
    // Note: We must list all existing values + new value
    $pdo->exec("ALTER TABLE tasks MODIFY COLUMN type ENUM('indexer', 'checker', 'traffic') NOT NULL");
    echo "Updated 'type' column.\n";

    // 2. Make 'search_engine' nullable
    $pdo->exec("ALTER TABLE tasks MODIFY COLUMN search_engine ENUM('google', 'yandex') NULL");
    echo "Updated 'search_engine' column to be nullable.\n";

    // 3. Add 'meta_data' JSON column if it doesn't exist
    // Check if column exists first
    $stmt = $pdo->prepare("SHOW COLUMNS FROM tasks LIKE 'meta_data'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN meta_data JSON NULL AFTER provider");
        echo "Added 'meta_data' column.\n";
    } else {
        echo "'meta_data' column already exists.\n";
    }

    // 4. Add 'jap_key' to settings if not exists (optional, can be done via admin UI)
    // We won't insert a key, but we'll ensure the settings table exists (it should)

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}

