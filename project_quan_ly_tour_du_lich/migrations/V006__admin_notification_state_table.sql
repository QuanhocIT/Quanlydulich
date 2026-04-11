-- Create admin notification baseline state table.

CREATE TABLE IF NOT EXISTS admin_notification_state (
    user_id INT(11) NOT NULL,
    payments_last_seen_id INT(11) NOT NULL DEFAULT 0,
    reviews_last_seen_id INT(11) NOT NULL DEFAULT 0,
    sound_enabled TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'DONE: admin_notification_state migration executed' AS migration_message;
