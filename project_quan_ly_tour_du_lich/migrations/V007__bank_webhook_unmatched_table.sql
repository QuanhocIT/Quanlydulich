-- Create queue table for unmatched bank webhook events.

CREATE TABLE IF NOT EXISTS bank_webhook_unmatched (
    queue_id INT(11) NOT NULL AUTO_INCREMENT,
    provider VARCHAR(32) NOT NULL,
    gateway_ref VARCHAR(128) DEFAULT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    booking_candidates VARCHAR(255) DEFAULT NULL,
    payload_json LONGTEXT DEFAULT NULL,
    received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed TINYINT(1) NOT NULL DEFAULT 0,
    processed_at DATETIME DEFAULT NULL,
    process_note VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (queue_id),
    KEY idx_processed (processed),
    KEY idx_received_at (received_at),
    KEY idx_gateway_ref (gateway_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'DONE: bank_webhook_unmatched migration executed' AS migration_message;
