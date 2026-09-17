-- V025: Strategic performance indexes for hot queries, lookups, and sorting
-- Idempotent script checking information_schema.statistics before creating each index.

SET @db_name := DATABASE();

-- 1) nguoi_dung: Fast login/lookup by email
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'nguoi_dung'
    AND index_name = 'idx_nd_email_del'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE nguoi_dung ADD INDEX idx_nd_email_del (email, is_deleted)',
  "SELECT 'SKIP idx_nd_email_del' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) nguoi_dung: Search by phone number
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'nguoi_dung'
    AND index_name = 'idx_nd_sdt'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE nguoi_dung ADD INDEX idx_nd_sdt (so_dien_thoai)',
  "SELECT 'SKIP idx_nd_sdt' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) nguoi_dung: Role and status filter (for staff, HDVs, admins)
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'nguoi_dung'
    AND index_name = 'idx_nd_vaitro_trangthai'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE nguoi_dung ADD INDEX idx_nd_vaitro_trangthai (vai_tro, trang_thai, is_deleted)',
  "SELECT 'SKIP idx_nd_vaitro_trangthai' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) booking: Fast admin pagination without filesort
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'booking'
    AND index_name = 'idx_booking_del_ngaydat'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_del_ngaydat (is_deleted, ngay_dat, booking_id)',
  "SELECT 'SKIP idx_booking_del_ngaydat' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 5) booking: Fast status counters
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'booking'
    AND index_name = 'idx_booking_del_status'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE booking ADD INDEX idx_booking_del_status (is_deleted, trang_thai)',
  "SELECT 'SKIP idx_booking_del_status' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 6) tour: Fast public listing by status and sort
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'tour'
    AND index_name = 'idx_tour_del_status'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE tour ADD INDEX idx_tour_del_status (is_deleted, trang_thai, tour_id)',
  "SELECT 'SKIP idx_tour_del_status' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 7) tour: Type and price range index
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'tour'
    AND index_name = 'idx_tour_loai_gia'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE tour ADD INDEX idx_tour_loai_gia (loai_tour, gia_co_ban)',
  "SELECT 'SKIP idx_tour_loai_gia' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 8) lich_khoi_hanh: Fast schedule list ordering without filesort
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'lich_khoi_hanh'
    AND index_name = 'idx_lkh_del_ngay'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE lich_khoi_hanh ADD INDEX idx_lkh_del_ngay (deleted_at, ngay_khoi_hanh, gio_xuat_phat)',
  "SELECT 'SKIP idx_lkh_del_ngay' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 9) lich_khoi_hanh: Status range lookups
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'lich_khoi_hanh'
    AND index_name = 'idx_lkh_status_ngay'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE lich_khoi_hanh ADD INDEX idx_lkh_status_ngay (trang_thai, ngay_khoi_hanh)',
  "SELECT 'SKIP idx_lkh_status_ngay' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 10) phan_bo_dich_vu: Eliminate hash join table scan
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'phan_bo_dich_vu'
    AND index_name = 'idx_pbdv_lich_del'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE phan_bo_dich_vu ADD INDEX idx_pbdv_lich_del (lich_khoi_hanh_id, deleted_at)',
  "SELECT 'SKIP idx_pbdv_lich_del' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 11) danh_gia: Covering index for rating aggregations
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'danh_gia'
    AND index_name = 'idx_dg_covering_tour'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE danh_gia ADD INDEX idx_dg_covering_tour (loai_danh_gia, tour_id, diem, deleted_at)',
  "SELECT 'SKIP idx_dg_covering_tour' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 12) thong_bao: Covering unread counter by recipient, role, status
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'thong_bao'
    AND index_name = 'idx_tb_nhan_vaitro_status'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE thong_bao ADD INDEX idx_tb_nhan_vaitro_status (nguoi_nhan_id, vai_tro_nhan, trang_thai, deleted_at)',
  "SELECT 'SKIP idx_tb_nhan_vaitro_status' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 13) automation_events: Severity lookup
SET @exists := (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = @db_name
    AND table_name = 'automation_events'
    AND index_name = 'idx_auto_events_severity'
);
SET @sql_stmt := IF(
  @exists = 0,
  'ALTER TABLE automation_events ADD INDEX idx_auto_events_severity (severity, created_at)',
  "SELECT 'SKIP idx_auto_events_severity' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;
