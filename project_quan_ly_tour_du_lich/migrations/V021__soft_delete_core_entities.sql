-- V021: Soft delete columns for core entities.
-- Idempotent: add columns/indexes only when missing.

SET @db_name := DATABASE();

-- booking
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'booking' AND column_name = 'is_deleted'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE booking ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0',
  "SELECT 'SKIP booking.is_deleted' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'booking' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE booking ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP booking.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_idx := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'booking' AND index_name = 'idx_booking_soft_delete'
);
SET @sql_stmt := IF(@has_idx = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_soft_delete (is_deleted, deleted_at)',
  "SELECT 'SKIP idx_booking_soft_delete' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tour
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'tour' AND column_name = 'is_deleted'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE tour ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0',
  "SELECT 'SKIP tour.is_deleted' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'tour' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE tour ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP tour.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_idx := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'tour' AND index_name = 'idx_tour_soft_delete'
);
SET @sql_stmt := IF(@has_idx = 0,
  'ALTER TABLE tour ADD INDEX idx_tour_soft_delete (is_deleted, deleted_at)',
  "SELECT 'SKIP idx_tour_soft_delete' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- nguoi_dung
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'nguoi_dung' AND column_name = 'is_deleted'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE nguoi_dung ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0',
  "SELECT 'SKIP nguoi_dung.is_deleted' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'nguoi_dung' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE nguoi_dung ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP nguoi_dung.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- nhan_su
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'nhan_su' AND column_name = 'is_deleted'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE nhan_su ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0',
  "SELECT 'SKIP nhan_su.is_deleted' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'nhan_su' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE nhan_su ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP nhan_su.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- nha_cung_cap
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'nha_cung_cap' AND column_name = 'is_deleted'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE nha_cung_cap ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0',
  "SELECT 'SKIP nha_cung_cap.is_deleted' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'nha_cung_cap' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE nha_cung_cap ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP nha_cung_cap.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- nhat_ky_tour
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'nhat_ky_tour' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE nhat_ky_tour ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP nhat_ky_tour.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- diem_checkin
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'diem_checkin' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE diem_checkin ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP diem_checkin.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- yeu_cau_dac_biet
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'yeu_cau_dac_biet' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE yeu_cau_dac_biet ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP yeu_cau_dac_biet.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- phan_hoi_hdv
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'phan_hoi_hdv' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE phan_hoi_hdv ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP phan_hoi_hdv.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- phan_bo_nhan_su
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'phan_bo_nhan_su' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE phan_bo_nhan_su ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP phan_bo_nhan_su.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- phan_bo_dich_vu
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'phan_bo_dich_vu' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE phan_bo_dich_vu ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP phan_bo_dich_vu.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- dich_vu_nha_cung_cap
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'dich_vu_nha_cung_cap' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE dich_vu_nha_cung_cap ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP dich_vu_nha_cung_cap.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT 'DONE: V021 soft delete columns applied' AS migration_message;
