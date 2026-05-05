-- V017: Bảng hàng đợi email async — tách việc gửi email ra khỏi HTTP request
-- Thay vì gửi email trực tiếp trong request (block worker 20s), ta ghi vào bảng này
-- rồi có cron job /scripts/process_email_queue.php chạy mỗi 1 phút để gửi.

CREATE TABLE IF NOT EXISTS email_queue (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    to_email      VARCHAR(320)    NOT NULL,
    subject       VARCHAR(500)    NOT NULL,
    body_html     LONGTEXT        NOT NULL,
    attempts      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    max_attempts  TINYINT UNSIGNED NOT NULL DEFAULT 3,
    status        ENUM('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
    scheduled_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at       DATETIME        NULL,
    last_error    TEXT            NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_sched (status, scheduled_at),
    INDEX idx_created_at   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
