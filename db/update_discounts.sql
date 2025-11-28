CREATE TABLE IF NOT EXISTS discount_codes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS discount_usage (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    discount_code_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usage_code (discount_code_id),
    INDEX idx_usage_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add columns to payments table if they don't exist
-- Note: Running this block multiple times might cause errors if columns exist, 
-- so checks are recommended, but basic ALTER is shown here.

ALTER TABLE payments ADD COLUMN discount_code_id BIGINT UNSIGNED NULL;
ALTER TABLE payments ADD COLUMN original_amount DECIMAL(10,2) NULL;
ALTER TABLE payments ADD COLUMN discount_amount DECIMAL(10,2) NULL DEFAULT 0;

