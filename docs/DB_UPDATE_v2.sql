-- Update schema with missing tables and columns

-- 1. Add 'cryptomus' to payments enum if not exists (This syntax is tricky in MySQL, usually we just alter)
ALTER TABLE payments MODIFY COLUMN method ENUM('paypal', 'cryptomus') NOT NULL;

-- 2. Add provider column to tasks if missing (was in earlier code analysis but not in schema file)
-- We'll check if it exists first or just try to add it. Safe approach:
-- (Since I cannot conditionally execute SQL easily here, I will provide the statement)
ALTER TABLE tasks ADD COLUMN provider VARCHAR(32) DEFAULT 'speedyindex' AFTER status;
ALTER TABLE tasks ADD COLUMN provider_task_id VARCHAR(128) NULL AFTER speedyindex_task_id;

-- 3. Create settings table (it was missing from the file but exists in DB, good to have schema for it)
CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(191) PRIMARY KEY,
    `value` TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create task_batches for Drip Feed
CREATE TABLE IF NOT EXISTS task_batches (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT UNSIGNED NOT NULL,
    provider_batch_id VARCHAR(128) NULL,
    link_count INT NOT NULL DEFAULT 0,
    status ENUM('pending','submitted','completed') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_batches_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Add Drip Feed columns to tasks
ALTER TABLE tasks ADD COLUMN is_drip_feed TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE tasks ADD COLUMN drip_percentage INT NULL DEFAULT 10;
ALTER TABLE tasks ADD COLUMN drip_interval_minutes INT NULL DEFAULT 1440; -- 24 hours
ALTER TABLE tasks ADD COLUMN next_run_at DATETIME NULL;

-- 6. Add batch_id to task_links
ALTER TABLE task_links ADD COLUMN batch_id BIGINT UNSIGNED NULL;
-- Add FK later if needed, for now just index
ALTER TABLE task_links ADD INDEX idx_tasklinks_batch (batch_id);

-- 7. Create admin_actions if missing
CREATE TABLE IF NOT EXISTS admin_actions (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	admin_id BIGINT UNSIGNED NOT NULL,
	action VARCHAR(191) NOT NULL,
	target_id BIGINT NULL,
	details JSON NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_admin_actions_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Create announcements if missing
CREATE TABLE IF NOT EXISTS announcements (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL,
	message TEXT NOT NULL,
	show_from DATETIME NULL,
	show_to DATETIME NULL,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

