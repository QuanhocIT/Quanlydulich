-- V013: Email verification columns for nguoi_dung
-- Chạy trên database: quan_ly_tour_du_lich
-- Tương thích MySQL 5.7+ / 8.x

USE quan_ly_tour_du_lich;

SET @col_token_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND COLUMN_NAME = 'email_verification_token'
);

SET @col_verified_at_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND COLUMN_NAME = 'email_verified_at'
);

SET @alter_sql = IF(
    @col_token_exists = 0 AND @col_verified_at_exists = 0,
    "ALTER TABLE nguoi_dung
        ADD COLUMN email_verification_token VARCHAR(64) NULL DEFAULT NULL
            COMMENT 'One-time token sent to the user email for verification',
        ADD COLUMN email_verified_at DATETIME NULL DEFAULT NULL
            COMMENT 'Timestamp of successful email verification; NULL = not yet verified'",
    IF(
        @col_token_exists = 0,
        "ALTER TABLE nguoi_dung
            ADD COLUMN email_verification_token VARCHAR(64) NULL DEFAULT NULL
                COMMENT 'One-time token sent to the user email for verification'",
        IF(
            @col_verified_at_exists = 0,
            "ALTER TABLE nguoi_dung
                ADD COLUMN email_verified_at DATETIME NULL DEFAULT NULL
                    COMMENT 'Timestamp of successful email verification; NULL = not yet verified'",
            "SELECT 'SKIP: email verification columns already exist'"
        )
    )
);

PREPARE stmt_alter_email_verification FROM @alter_sql;
EXECUTE stmt_alter_email_verification;
DEALLOCATE PREPARE stmt_alter_email_verification;

SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND INDEX_NAME = 'idx_email_verification_token'
);

SET @idx_sql = IF(
    @idx_exists = 0,
    'CREATE INDEX idx_email_verification_token ON nguoi_dung (email_verification_token)',
    "SELECT 'SKIP: idx_email_verification_token already exists'"
);

PREPARE stmt_create_email_verification_idx FROM @idx_sql;
EXECUTE stmt_create_email_verification_idx;
DEALLOCATE PREPARE stmt_create_email_verification_idx;
