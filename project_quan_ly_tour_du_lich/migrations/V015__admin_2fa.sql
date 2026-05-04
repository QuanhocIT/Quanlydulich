-- V015: Admin Two-Factor Authentication (TOTP)
-- Chạy trên database: quan_ly_tour_du_lich

USE quan_ly_tour_du_lich;

ALTER TABLE nguoi_dung
    ADD COLUMN two_factor_secret  VARCHAR(64)    NULL DEFAULT NULL
        COMMENT 'Base32-encoded TOTP secret (RFC 6238)',
    ADD COLUMN two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 = 2FA enabled, 0 = disabled';
