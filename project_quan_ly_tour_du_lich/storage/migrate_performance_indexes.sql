-- Performance indexes for large datasets
-- Run this file separately on DB: quan_ly_tour_du_lich
-- Idempotent: safe to run multiple times.

SET @db_name := DATABASE();

-- 1) Financial queries by tour + type + date
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'giao_dich_tai_chinh' AND index_name = 'idx_gd_tour_loai_ngay'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE giao_dich_tai_chinh ADD INDEX idx_gd_tour_loai_ngay (tour_id, loai, ngay_giao_dich)',
  "SELECT 'SKIP idx_gd_tour_loai_ngay' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Booking availability and status queries
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'booking' AND index_name = 'idx_booking_tour_ngay_status'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_tour_ngay_status (tour_id, ngay_khoi_hanh, trang_thai)',
  "SELECT 'SKIP idx_booking_tour_ngay_status' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Booking list ordered by date
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'booking' AND index_name = 'idx_booking_ngaydat_id'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_ngaydat_id (ngay_dat, booking_id)',
  "SELECT 'SKIP idx_booking_ngaydat_id' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) Payment reconciliation (status/date)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'payments' AND index_name = 'idx_pay_status_date'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE payments ADD INDEX idx_pay_status_date (status, payment_date)',
  "SELECT 'SKIP idx_pay_status_date' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5) Payment lookup by booking + status
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'payments' AND index_name = 'idx_pay_booking_status'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE payments ADD INDEX idx_pay_booking_status (booking_id, status)',
  "SELECT 'SKIP idx_pay_booking_status' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6) Schedule list and filters
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'lich_khoi_hanh' AND index_name = 'idx_lkh_ngay_status'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE lich_khoi_hanh ADD INDEX idx_lkh_ngay_status (ngay_khoi_hanh, trang_thai)',
  "SELECT 'SKIP idx_lkh_ngay_status' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 7) Staff assignment conflicts
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'phan_bo_nhan_su' AND index_name = 'idx_pbn_lich_nhansu'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE phan_bo_nhan_su ADD INDEX idx_pbn_lich_nhansu (lich_khoi_hanh_id, nhan_su_id)',
  "SELECT 'SKIP idx_pbn_lich_nhansu' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 8) Service assignment by departure
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'phan_bo_dich_vu' AND index_name = 'idx_pbdv_lich'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE phan_bo_dich_vu ADD INDEX idx_pbdv_lich (lich_khoi_hanh_id)',
  "SELECT 'SKIP idx_pbdv_lich' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 9) Tour-request lookup for booking filters (EXISTS on thong_bao)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'thong_bao' AND index_name = 'idx_tb_gui_tieude_vaitro'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE thong_bao ADD INDEX idx_tb_gui_tieude_vaitro (nguoi_gui_id, tieu_de, vai_tro_nhan)',
  "SELECT 'SKIP idx_tb_gui_tieude_vaitro' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 10) Booking list by customer + order by date/id (reduce filesort on admin list)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'booking' AND index_name = 'idx_booking_kh_ngaydat_id'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_kh_ngaydat_id (khach_hang_id, ngay_dat, booking_id)',
  "SELECT 'SKIP idx_booking_kh_ngaydat_id' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 11) Payment reconcile/report date-range + sort
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'payments' AND index_name = 'idx_pay_date_id_status'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE payments ADD INDEX idx_pay_date_id_status (payment_date, payment_id, status)',
  "SELECT 'SKIP idx_pay_date_id_status' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 12) Finance summary by booking + type (Thu)
SET @exists := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = @db_name AND table_name = 'giao_dich_tai_chinh' AND index_name = 'idx_gd_booking_loai'
);
SET @sql_stmt := IF(@exists = 0,
  'ALTER TABLE giao_dich_tai_chinh ADD INDEX idx_gd_booking_loai (booking_id, loai)',
  "SELECT 'SKIP idx_gd_booking_loai' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT 'DONE: performance index migration executed' AS migration_message;
