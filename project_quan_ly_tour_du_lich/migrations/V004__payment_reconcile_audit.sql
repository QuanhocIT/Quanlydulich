-- Create audit trail table for controlled payment reconcile repairs.

CREATE TABLE IF NOT EXISTS payment_reconcile_audit (
    audit_id INT(11) NOT NULL AUTO_INCREMENT,
    payment_id INT(11) NOT NULL,
    booking_id INT(11) NOT NULL,
    action VARCHAR(64) NOT NULL,
    reason VARCHAR(500) DEFAULT NULL,
    performed_by INT(11) DEFAULT NULL,
    before_json LONGTEXT DEFAULT NULL,
    after_json LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (audit_id),
    KEY idx_payment_id (payment_id),
    KEY idx_booking_id (booking_id),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'DONE: payment_reconcile_audit migration executed' AS migration_message;
