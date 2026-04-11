-- Add booking participant-status column if missing.

SET @db_name := DATABASE();

SET @booking_exists := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = @db_name
    AND table_name = 'booking'
);

SET @column_exists := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @db_name
    AND table_name = 'booking'
    AND column_name = 'trang_thai_hanh_khach'
);

SET @sql_stmt := IF(
  @booking_exists = 0,
  "SELECT 'SKIP: table booking does not exist' AS migration_message",
  IF(
    @column_exists = 0,
    "ALTER TABLE booking ADD COLUMN trang_thai_hanh_khach VARCHAR(50) NULL DEFAULT NULL",
    "SELECT 'SKIP: booking.trang_thai_hanh_khach already exists' AS migration_message"
  )
);

PREPARE stmt FROM @sql_stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'DONE: booking participant status column migration executed' AS migration_message;
