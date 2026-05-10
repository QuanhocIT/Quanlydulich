-- V020: Hot query indexes for dashboard and automation jobs
-- Idempotent by checking INFORMATION_SCHEMA.STATISTICS before adding each index.

SET @db_name := DATABASE();

-- 1) booking: fast counts/filters by status and created date
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'booking'
    AND index_name = 'idx_booking_status_ngaydat'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_status_ngaydat (trang_thai, ngay_dat)',
  "SELECT 'SKIP idx_booking_status_ngaydat' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) booking: fast departure stats by lich_khoi_hanh
SET @has_booking_lich_khoi_hanh_id := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @db_name
    AND table_name = 'booking'
    AND column_name = 'lich_khoi_hanh_id'
);
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'booking'
    AND index_name = 'idx_booking_lich_status'
);
SET @sql_stmt := IF(
  @has_booking_lich_khoi_hanh_id > 0 AND @exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_lich_status (lich_khoi_hanh_id, trang_thai)',
  "SELECT 'SKIP idx_booking_lich_status (column or index unavailable)' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) booking: fast list by tour + departure date ordered by booking date
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'booking'
    AND index_name = 'idx_booking_tour_ngay_ngaydat'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_tour_ngay_ngaydat (tour_id, ngay_khoi_hanh, ngay_dat)',
  "SELECT 'SKIP idx_booking_tour_ngay_ngaydat' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) giao_dich_tai_chinh: revenue scans by type and date range
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'giao_dich_tai_chinh'
    AND index_name = 'idx_gd_loai_ngay'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE giao_dich_tai_chinh ADD INDEX idx_gd_loai_ngay (loai, ngay_giao_dich)',
  "SELECT 'SKIP idx_gd_loai_ngay' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5) thong_bao: SLA/admin inbox filters by role/title/status/time
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'thong_bao'
    AND index_name = 'idx_tb_role_title_status_created'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE thong_bao ADD INDEX idx_tb_role_title_status_created (vai_tro_nhan, tieu_de, trang_thai, created_at)',
  "SELECT 'SKIP idx_tb_role_title_status_created' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6) payment_logs: anomaly/digest scans by action and time
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'payment_logs'
    AND index_name = 'idx_pl_action_logtime'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE payment_logs ADD INDEX idx_pl_action_logtime (action, log_time)',
  "SELECT 'SKIP idx_pl_action_logtime' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 7) payment_idempotency: failed-status 24h scans
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'payment_idempotency'
    AND index_name = 'idx_pidem_status_created'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE payment_idempotency ADD INDEX idx_pidem_status_created (status, created_at)',
  "SELECT 'SKIP idx_pidem_status_created' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT 'DONE: V020 hot query indexes applied' AS migration_message;
