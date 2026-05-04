-- V013: Email verification columns for nguoi_dung
-- Chạy trên database: quan_ly_tour_du_lich
-- Tương thích MySQL 5.7+ / 8.x

USE quan_ly_tour_du_lich;

ALTER TABLE nguoi_dung
    ADD COLUMN email_verification_token VARCHAR(64)  NULL DEFAULT NULL
        COMMENT 'One-time token sent to the user email for verification',
    ADD COLUMN email_verified_at        DATETIME     NULL DEFAULT NULL
        COMMENT 'Timestamp of successful email verification; NULL = not yet verified';

CREATE INDEX idx_email_verification_token
    ON nguoi_dung (email_verification_token);
