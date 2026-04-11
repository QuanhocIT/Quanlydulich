-- Add tour_checkin document image columns if missing.

SET @db_name := DATABASE();

SET @table_exists := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = @db_name
    AND table_name = 'tour_checkin'
);

SET @cccd_exists := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @db_name
    AND table_name = 'tour_checkin'
    AND column_name = 'anh_cccd'
);

SET @passport_exists := (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = @db_name
    AND table_name = 'tour_checkin'
    AND column_name = 'anh_passport'
);

SET @sql_stmt := IF(
  @table_exists = 0,
  "SELECT 'SKIP: table tour_checkin does not exist' AS migration_message",
  IF(
    @cccd_exists = 0,
    "ALTER TABLE tour_checkin ADD COLUMN anh_cccd VARCHAR(255) NULL DEFAULT NULL",
    "SELECT 'SKIP: tour_checkin.anh_cccd already exists' AS migration_message"
  )
);
PREPARE stmt FROM @sql_stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql_stmt2 := IF(
  @table_exists = 0,
  "SELECT 'SKIP: table tour_checkin does not exist' AS migration_message",
  IF(
    @passport_exists = 0,
    "ALTER TABLE tour_checkin ADD COLUMN anh_passport VARCHAR(255) NULL DEFAULT NULL",
    "SELECT 'SKIP: tour_checkin.anh_passport already exists' AS migration_message"
  )
);
PREPARE stmt2 FROM @sql_stmt2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SELECT 'DONE: tour_checkin document columns migration executed' AS migration_message;
