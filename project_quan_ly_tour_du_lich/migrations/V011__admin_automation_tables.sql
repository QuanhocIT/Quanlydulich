-- Automation tables for admin support jobs.

CREATE TABLE IF NOT EXISTS automation_job_runs (
    run_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    job_name VARCHAR(64) NOT NULL,
    is_success TINYINT(1) NOT NULL DEFAULT 1,
    affected_count INT NOT NULL DEFAULT 0,
    message VARCHAR(255) DEFAULT NULL,
    duration_ms DECIMAL(10,1) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (run_id),
    KEY idx_job_created (job_name, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_events (
    event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    event_key VARCHAR(191) NOT NULL,
    job_name VARCHAR(64) NOT NULL,
    severity ENUM('low','medium','high') NOT NULL DEFAULT 'low',
    title VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    payload_json LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id),
    UNIQUE KEY uniq_event_key (event_key),
    KEY idx_job_created (job_name, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS booking_priority (
    booking_id INT(11) NOT NULL,
    priority_label ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low',
    score INT(11) NOT NULL DEFAULT 0,
    reasons_json LONGTEXT DEFAULT NULL,
    computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (booking_id),
    KEY idx_priority_label (priority_label),
    KEY idx_computed_at (computed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tour_health_score (
    tour_id INT(11) NOT NULL,
    score INT(11) NOT NULL DEFAULT 0,
    health_level ENUM('Good','Watch','Critical') NOT NULL DEFAULT 'Good',
    metrics_json LONGTEXT DEFAULT NULL,
    computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (tour_id),
    KEY idx_health_level (health_level),
    KEY idx_computed_at (computed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_decision_assist (
    assist_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    entity_type VARCHAR(30) NOT NULL,
    entity_id INT(11) NOT NULL,
    recommendation_hash CHAR(40) NOT NULL,
    recommendation_text VARCHAR(500) NOT NULL,
    status ENUM('open','done','ignored') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (assist_id),
    UNIQUE KEY uniq_entity_reco (entity_type, entity_id, recommendation_hash),
    KEY idx_status (status),
    KEY idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automation_settings (
    setting_key VARCHAR(64) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO automation_settings (setting_key, setting_value, updated_at)
VALUES ('automation_enabled', '1', NOW());

SELECT 'DONE: admin automation tables migration executed' AS migration_message;
