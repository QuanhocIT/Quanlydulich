-- V015: Admin Two-Factor Authentication (TOTP)
-- Chạy trên database: quan_ly_tour_du_lich

USE quan_ly_tour_du_lich;

SET @has_two_factor_secret = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND COLUMN_NAME = 'two_factor_secret'
);

SET @has_two_factor_enabled = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'nguoi_dung'
      AND COLUMN_NAME = 'two_factor_enabled'
);

SET @alter_v015_secret = IF(
    @has_two_factor_secret = 0,
    "ALTER TABLE nguoi_dung
        ADD COLUMN two_factor_secret VARCHAR(64) NULL DEFAULT NULL
            COMMENT 'Base32-encoded TOTP secret (RFC 6238)'",
    "SELECT 'SKIP: two_factor_secret already exists'"
);

PREPARE stmt_v015_1 FROM @alter_v015_secret;
EXECUTE stmt_v015_1;
DEALLOCATE PREPARE stmt_v015_1;

SET @alter_v015_enabled = IF(
    @has_two_factor_enabled = 0,
    "ALTER TABLE nguoi_dung
        ADD COLUMN two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0
            COMMENT '1 = 2FA enabled, 0 = disabled'",
    "SELECT 'SKIP: two_factor_enabled already exists'"
);

PREPARE stmt_v015_2 FROM @alter_v015_enabled;
EXECUTE stmt_v015_2;
DEALLOCATE PREPARE stmt_v015_2;
