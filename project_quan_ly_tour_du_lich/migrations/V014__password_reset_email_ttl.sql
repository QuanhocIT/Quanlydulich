-- V014: Password reset tokens + email verification TTL
-- Chạy trên database: quan_ly_tour_du_lich

USE quan_ly_tour_du_lich;

SET @has_email_token_expires_at = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND COLUMN_NAME = 'email_token_expires_at'
);

SET @has_password_reset_token = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND COLUMN_NAME = 'password_reset_token'
);

SET @has_password_reset_expires_at = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND COLUMN_NAME = 'password_reset_expires_at'
);

SET @alter_v014 = IF(
    @has_email_token_expires_at = 0 AND @has_password_reset_token = 0 AND @has_password_reset_expires_at = 0,
    "ALTER TABLE nguoi_dung
        ADD COLUMN email_token_expires_at DATETIME NULL DEFAULT NULL
            COMMENT 'Expiry time for email_verification_token (24h from registration)',
        ADD COLUMN password_reset_token VARCHAR(64) NULL DEFAULT NULL
            COMMENT 'One-time token for password reset flow',
        ADD COLUMN password_reset_expires_at DATETIME NULL DEFAULT NULL
            COMMENT 'Expiry time of password_reset_token (1h from request)'",
    IF(
        @has_email_token_expires_at = 0,
        "ALTER TABLE nguoi_dung
            ADD COLUMN email_token_expires_at DATETIME NULL DEFAULT NULL
                COMMENT 'Expiry time for email_verification_token (24h from registration)'",
        "SELECT 'SKIP: email_token_expires_at already exists'"
    )
);

PREPARE stmt_v014_1 FROM @alter_v014;
EXECUTE stmt_v014_1;
DEALLOCATE PREPARE stmt_v014_1;

SET @alter_v014_token = IF(
    @has_password_reset_token = 0,
    "ALTER TABLE nguoi_dung
        ADD COLUMN password_reset_token VARCHAR(64) NULL DEFAULT NULL
            COMMENT 'One-time token for password reset flow'",
    "SELECT 'SKIP: password_reset_token already exists'"
);

PREPARE stmt_v014_2 FROM @alter_v014_token;
EXECUTE stmt_v014_2;
DEALLOCATE PREPARE stmt_v014_2;

SET @alter_v014_exp = IF(
    @has_password_reset_expires_at = 0,
    "ALTER TABLE nguoi_dung
        ADD COLUMN password_reset_expires_at DATETIME NULL DEFAULT NULL
            COMMENT 'Expiry time of password_reset_token (1h from request)'",
    "SELECT 'SKIP: password_reset_expires_at already exists'"
);

PREPARE stmt_v014_3 FROM @alter_v014_exp;
EXECUTE stmt_v014_3;
DEALLOCATE PREPARE stmt_v014_3;

SET @has_idx_password_reset_token = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND INDEX_NAME = 'idx_password_reset_token'
);

SET @idx_v014 = IF(
    @has_idx_password_reset_token = 0,
    'CREATE INDEX idx_password_reset_token ON nguoi_dung (password_reset_token)',
    "SELECT 'SKIP: idx_password_reset_token already exists'"
);

PREPARE stmt_v014_idx FROM @idx_v014;
EXECUTE stmt_v014_idx;
DEALLOCATE PREPARE stmt_v014_idx;
