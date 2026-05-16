-- V022: Soft delete columns for remaining business entities.
-- Idempotent migration.

SET @db_name := DATABASE();

-- Helper pattern repeated per table

-- lich_khoi_hanh
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'lich_khoi_hanh' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE lich_khoi_hanh ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP lich_khoi_hanh.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tour_checkin
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'tour_checkin' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE tour_checkin ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP tour_checkin.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- thong_bao
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'thong_bao' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE thong_bao ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP thong_bao.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- chi_phi_thuc_te
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'chi_phi_thuc_te' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE chi_phi_thuc_te ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP chi_phi_thuc_te.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- danh_gia
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'danh_gia' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE danh_gia ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP danh_gia.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- du_toan_tour
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'du_toan_tour' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE du_toan_tour ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP du_toan_tour.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- hotel_room_assignment
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'hotel_room_assignment' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE hotel_room_assignment ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP hotel_room_assignment.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- invoices
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'invoices' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE invoices ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP invoices.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- invoice_items
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'invoice_items' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE invoice_items ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP invoice_items.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- payments
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'payments' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE payments ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP payments.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- payment_logs
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'payment_logs' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE payment_logs ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP payment_logs.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- khach_hang_tour_yeu_thich
SET @has_col := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = @db_name AND table_name = 'khach_hang_tour_yeu_thich' AND column_name = 'deleted_at'
);
SET @sql_stmt := IF(@has_col = 0,
  'ALTER TABLE khach_hang_tour_yeu_thich ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL',
  "SELECT 'SKIP khach_hang_tour_yeu_thich.deleted_at' AS migration_message"
);
PREPARE stmt FROM @sql_stmt; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT 'DONE: V022 soft delete columns applied' AS migration_message;
