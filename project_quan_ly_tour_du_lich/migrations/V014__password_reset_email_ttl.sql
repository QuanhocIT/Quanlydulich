-- V014: Password reset tokens + email verification TTL
-- Chạy trên database: quan_ly_tour_du_lich

USE quan_ly_tour_du_lich;

ALTER TABLE nguoi_dung
    ADD COLUMN email_token_expires_at    DATETIME    NULL DEFAULT NULL
        COMMENT 'Expiry time for email_verification_token (24h from registration)',
    ADD COLUMN password_reset_token      VARCHAR(64) NULL DEFAULT NULL
        COMMENT 'One-time token for password reset flow',
    ADD COLUMN password_reset_expires_at DATETIME    NULL DEFAULT NULL
        COMMENT 'Expiry time of password_reset_token (1h from request)';

CREATE INDEX idx_password_reset_token
    ON nguoi_dung (password_reset_token);
