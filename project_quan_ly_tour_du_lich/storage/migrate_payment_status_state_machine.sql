-- Migrate payments.status enum to state-machine values.
-- Safe to run standalone on DB: quan_ly_tour_du_lich

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
   MODIFY COLUMN status ENUM(
     'TaoMoi',
     'DangXuLy',
     'ThanhCong',
     'ThatBai',
     'HetHan',
     'DaDoiSoat'
   ) NOT NULL DEFAULT 'DangXuLy'",
  "SELECT 'SKIP: table payments does not exist in current database' AS migration_message"
);

PREPARE stmt FROM @sql_stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'DONE: payment status state-machine migration executed' AS migration_message;
