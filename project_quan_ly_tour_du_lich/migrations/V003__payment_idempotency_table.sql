-- Create payment idempotency table for webhook/callback deduplication.

CREATE TABLE IF NOT EXISTS payment_idempotency (
    id INT(11) NOT NULL AUTO_INCREMENT,
    scope VARCHAR(64) NOT NULL,
    idem_key CHAR(64) NOT NULL,
    status ENUM('processing','completed','failed') NOT NULL DEFAULT 'processing',
    response_code INT(11) DEFAULT NULL,
    response_message VARCHAR(255) DEFAULT NULL,
    payload_hash CHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_scope_key (scope, idem_key),
    KEY idx_scope_status (scope, status),
    KEY idx_last_seen_at (last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'DONE: payment idempotency table migration executed' AS migration_message;
