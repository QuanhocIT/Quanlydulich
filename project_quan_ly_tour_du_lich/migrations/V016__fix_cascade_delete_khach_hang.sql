-- V016: Protect financial history when a customer is deleted
-- Problem (M6): booking_ibfk_2 and booking_khach_hang_ibfk_2 used ON DELETE CASCADE.
-- Deleting a khach_hang record cascaded through booking → payments, booking_history,
-- giao_dich_tai_chinh, etc., wiping the entire financial audit trail.
-- Fix: change those two FKs to ON DELETE RESTRICT so the DB blocks deletion of a
-- customer who still has bookings, forcing the operator to explicitly handle the data first.

-- Step 1: Drop the cascading FKs
SET @has_fk_booking_ibfk_2 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'booking'
      AND CONSTRAINT_NAME = 'booking_ibfk_2'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @drop_fk_booking = IF(
    @has_fk_booking_ibfk_2 > 0,
    'ALTER TABLE `booking` DROP FOREIGN KEY `booking_ibfk_2`',
    "SELECT 'SKIP: booking_ibfk_2 not found'"
);

PREPARE stmt_drop_fk_booking FROM @drop_fk_booking;
EXECUTE stmt_drop_fk_booking;
DEALLOCATE PREPARE stmt_drop_fk_booking;

SET @has_fk_booking_kh_ibfk_2 = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'booking_khach_hang'
      AND CONSTRAINT_NAME = 'booking_khach_hang_ibfk_2'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);

SET @drop_fk_booking_kh = IF(
    @has_fk_booking_kh_ibfk_2 > 0,
    'ALTER TABLE `booking_khach_hang` DROP FOREIGN KEY `booking_khach_hang_ibfk_2`',
    "SELECT 'SKIP: booking_khach_hang_ibfk_2 not found'"
);

PREPARE stmt_drop_fk_booking_kh FROM @drop_fk_booking_kh;
EXECUTE stmt_drop_fk_booking_kh;
DEALLOCATE PREPARE stmt_drop_fk_booking_kh;

-- Step 2: Re-add with ON DELETE RESTRICT
ALTER TABLE `booking`
    ADD CONSTRAINT `booking_ibfk_2`
        FOREIGN KEY (`khach_hang_id`)
        REFERENCES `khach_hang` (`khach_hang_id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE;

ALTER TABLE `booking_khach_hang`
    ADD CONSTRAINT `booking_khach_hang_ibfk_2`
        FOREIGN KEY (`khach_hang_id`)
        REFERENCES `khach_hang` (`khach_hang_id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE;
