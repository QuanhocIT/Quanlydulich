-- V019: Daily KPI summary table for admin dashboard acceleration.

CREATE TABLE IF NOT EXISTS daily_kpi_summary (
    summary_date DATE NOT NULL,
    booking_new_count INT NOT NULL DEFAULT 0,
    booking_cancel_count INT NOT NULL DEFAULT 0,
    payment_success_count INT NOT NULL DEFAULT 0,
    revenue_success_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    conversion_rate_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    notes_json LONGTEXT DEFAULT NULL,
    computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (summary_date),
    KEY idx_kpi_computed (computed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'DONE: daily_kpi_summary migration executed' AS migration_message;
