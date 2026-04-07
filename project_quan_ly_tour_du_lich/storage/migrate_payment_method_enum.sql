-- Migrate payment_method enum to support online gateway labels directly.
-- Safe to run standalone on DB: quan_ly_tour_du_lich
-- IMPORTANT: Run this file only, not the full database dump.

SET @db_name := DATABASE();

SET @payments_exists := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = @db_name
    AND table_name = 'payments'
);

SET @sql_stmt := IF(
  @payments_exists > 0,
  "ALTER TABLE payments
   MODIFY COLUMN payment_method ENUM(
     'VNPay',
     'Momo',
     'Paypal',
     'ChuyenKhoan',
     'TienMat',
     'TheTinDung',
     'ViDienTu'
   ) NOT NULL DEFAULT 'VNPay'",
  "SELECT 'SKIP: table payments does not exist in current database' AS migration_message"
);

PREPARE stmt FROM @sql_stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'DONE: payment_method enum migration executed' AS migration_message;
