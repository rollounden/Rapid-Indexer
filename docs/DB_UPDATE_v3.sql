-- Clean DB Update v3
-- Only creates tables that are likely missing and safe to run

-- 1. Create task_batches for Drip Feed (Safe to run if table doesn't exist)
CREATE TABLE IF NOT EXISTS task_batches (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    task_id BIGINT UNSIGNED NOT NULL,
    provider_batch_id VARCHAR(128) NULL,
    link_count INT NOT NULL DEFAULT 0,
    status ENUM('pending','submitted','completed') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_batches_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create admin_actions if missing
CREATE TABLE IF NOT EXISTS admin_actions (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	admin_id BIGINT UNSIGNED NOT NULL,
	action VARCHAR(191) NOT NULL,
	target_id BIGINT NULL,
	details JSON NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_admin_actions_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create announcements if missing
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

-- 4. Add columns for Drip Feed (Run these one by one if they fail, or ignore if they exist)
-- MySQL doesn't have "ADD COLUMN IF NOT EXISTS" easily, so these might fail if columns exist.
-- That is okay! If it says "Duplicate column name", just ignore it.

ALTER TABLE tasks ADD COLUMN is_drip_feed TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE tasks ADD COLUMN drip_percentage INT NULL DEFAULT 10;
ALTER TABLE tasks ADD COLUMN drip_interval_minutes INT NULL DEFAULT 1440;
ALTER TABLE tasks ADD COLUMN next_run_at DATETIME NULL;

-- 5. Add batch_id to task_links
ALTER TABLE task_links ADD COLUMN batch_id BIGINT UNSIGNED NULL;
ALTER TABLE task_links ADD INDEX idx_tasklinks_batch (batch_id);

