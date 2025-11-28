<?php
require_once __DIR__ . '/../src/Db.php';

try {
    $pdo = Db::conn();
    
    echo "Migrating database for discount codes...\n";
    
    // 1. Create discount_codes table
    $sql = "CREATE TABLE IF NOT EXISTS discount_codes (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
        value DECIMAL(10,2) NOT NULL,
        min_spend DECIMAL(10,2) NULL,
        max_uses INT UNSIGNED NULL,
        used_count INT UNSIGNED NOT NULL DEFAULT 0,
        expires_at DATETIME NULL,
        affiliate_user_id BIGINT UNSIGNED NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_discount_code (code),
        INDEX idx_discount_affiliate (affiliate_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Created discount_codes table.\n";
    
    // 2. Create discount_usage table
    $sql = "CREATE TABLE IF NOT EXISTS discount_usage (
        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
        discount_code_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        payment_id BIGINT UNSIGNED NOT NULL,
        discount_amount DECIMAL(10,2) NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usage_code (discount_code_id),
        INDEX idx_usage_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Created discount_usage table.\n";
    
    // 3. Update payments table
    // Check if columns exist first to avoid errors on re-run
    $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'discount_code_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN discount_code_id BIGINT UNSIGNED NULL");
        echo "Added discount_code_id to payments.\n";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'original_amount'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN original_amount DECIMAL(10,2) NULL");
        echo "Added original_amount to payments.\n";
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM payments LIKE 'discount_amount'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN discount_amount DECIMAL(10,2) NULL DEFAULT 0");
        echo "Added discount_amount to payments.\n";
    }
    
    echo "Migration completed successfully.\n";
    
} catch (Exception $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}

