-- MySQL 8.x schema (utf8mb4, InnoDB)

CREATE TABLE users (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	email VARCHAR(191) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	status ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
	role ENUM('user','admin') NOT NULL DEFAULT 'user',
	credits_balance BIGINT NOT NULL DEFAULT 0,
	api_key VARCHAR(255) NULL,
	last_login_at DATETIME NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tasks (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	user_id BIGINT UNSIGNED NOT NULL,
	type ENUM('indexer','checker') NOT NULL,
	search_engine ENUM('google','yandex') NOT NULL,
	title VARCHAR(255) NULL,
	status ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
	vip TINYINT(1) NOT NULL DEFAULT 0,
	speedyindex_task_id VARCHAR(64) NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	completed_at DATETIME NULL,
	CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	INDEX idx_tasks_user_created (user_id, created_at),
	INDEX idx_tasks_provider (speedyindex_task_id),
	INDEX idx_tasks_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_links (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	task_id BIGINT UNSIGNED NOT NULL,
	url TEXT NOT NULL,
	status ENUM('pending','indexed','unindexed','error') NOT NULL DEFAULT 'pending',
	result_data JSON NULL,
	checked_at DATETIME NULL,
	error_code INT NULL,
	CONSTRAINT fk_tasklinks_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
	INDEX idx_tasklinks_task (task_id),
	INDEX idx_tasklinks_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	user_id BIGINT UNSIGNED NOT NULL,
	amount DECIMAL(10,2) NOT NULL,
	currency VARCHAR(16) NOT NULL DEFAULT 'USD',
	method ENUM('paypal') NOT NULL,
	paypal_order_id VARCHAR(128) NULL,
	paypal_capture_id VARCHAR(128) NULL,
	credits_awarded BIGINT NOT NULL DEFAULT 0,
	status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	UNIQUE KEY uniq_paypal_capture (paypal_capture_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE credit_ledger (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	user_id BIGINT UNSIGNED NOT NULL,
	delta BIGINT NOT NULL,
	reason ENUM('payment','task_deduction','task_refund','admin_adjustment','resubmission') NOT NULL,
	reference_table VARCHAR(64) NULL,
	reference_id BIGINT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_credit_ledger_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	INDEX idx_credit_ledger_user_time (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_logs (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	user_id BIGINT UNSIGNED NULL,
	endpoint VARCHAR(255) NOT NULL,
	request_payload JSON NULL,
	response_payload JSON NULL,
	status_code INT NULL,
	duration_ms INT UNSIGNED NULL,
	error_message TEXT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_api_logs_user_time (user_id, created_at),
	INDEX idx_api_logs_endpoint_time (endpoint, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE webhook_events (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	provider ENUM('paypal') NOT NULL,
	external_event_id VARCHAR(191) NOT NULL,
	event_type VARCHAR(191) NULL,
	signature VARCHAR(255) NULL,
	payload JSON NULL,
	status ENUM('received','processed','ignored','error') NOT NULL DEFAULT 'received',
	delivery_attempts INT NOT NULL DEFAULT 0,
	last_error TEXT NULL,
	processed_at DATETIME NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_webhook_external_event (external_event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_actions (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	admin_id BIGINT UNSIGNED NOT NULL,
	action VARCHAR(191) NOT NULL,
	target_id BIGINT NULL,
	details JSON NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_admin_actions_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcements (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL,
	message TEXT NOT NULL,
	show_from DATETIME NULL,
	show_to DATETIME NULL,
	is_active TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
