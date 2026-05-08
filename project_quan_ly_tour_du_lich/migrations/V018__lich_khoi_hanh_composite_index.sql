-- V018: Thêm composite index cho lich_khoi_hanh để tối ưu batch query getLichKhoiHanhByTourIds
-- Index (tour_id, ngay_khoi_hanh) hỗ trợ WHERE tour_id IN (...) ORDER BY ngay_khoi_hanh ASC

ALTER TABLE lich_khoi_hanh
    DROP INDEX IF EXISTS idx_lich_tour_id,
    ADD INDEX idx_lich_tour_ngay (tour_id, ngay_khoi_hanh);
