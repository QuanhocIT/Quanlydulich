-- V016: Protect financial history when a customer is deleted
-- Problem (M6): booking_ibfk_2 and booking_khach_hang_ibfk_2 used ON DELETE CASCADE.
-- Deleting a khach_hang record cascaded through booking → payments, booking_history,
-- giao_dich_tai_chinh, etc., wiping the entire financial audit trail.
-- Fix: change those two FKs to ON DELETE RESTRICT so the DB blocks deletion of a
-- customer who still has bookings, forcing the operator to explicitly handle the data first.

-- Step 1: Drop the cascading FKs
ALTER TABLE `booking`
    DROP FOREIGN KEY IF EXISTS `booking_ibfk_2`;

ALTER TABLE `booking_khach_hang`
    DROP FOREIGN KEY IF EXISTS `booking_khach_hang_ibfk_2`;

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
