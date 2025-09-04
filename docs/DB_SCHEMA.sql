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
	INDEX idx_tasks_provider (speedyindex_task_id)
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
	INDEX idx_tasklinks_task (task_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payments (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	user_id BIGINT UNSIGNED NOT NULL,
	amount BIGINT NOT NULL,
	method ENUM('paypal','crypto','yookassa') NOT NULL,
	speedyindex_invoice_id VARCHAR(128) NULL,
	paypal_txn_id VARCHAR(128) NULL,
	status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	UNIQUE KEY uniq_paypal_txn (paypal_txn_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_logs (
	id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	user_id BIGINT UNSIGNED NULL,
	endpoint VARCHAR(255) NOT NULL,
	request_payload JSON NULL,
	response_payload JSON NULL,
	status_code INT NULL,
	error_message TEXT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_api_logs_user_time (user_id, created_at)
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
