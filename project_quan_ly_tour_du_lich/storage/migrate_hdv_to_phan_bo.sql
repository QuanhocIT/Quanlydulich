-- Migration: Chuyển dữ liệu từ lich_khoi_hanh.hdv_id sang phan_bo_nhan_su
-- Ngày: 2025-11-17

-- Bước 1: Chuyển tất cả HDV từ cột hdv_id sang bảng phan_bo_nhan_su
INSERT INTO phan_bo_nhan_su (lich_khoi_hanh_id, nhan_su_id, vai_tro, trang_thai, ghi_chu)
SELECT 
    id as lich_khoi_hanh_id,
    hdv_id as nhan_su_id,
    'HDV' as vai_tro,
    CASE 
        WHEN trang_thai = 'HoanThanh' THEN 'DaXacNhan'
        WHEN trang_thai = 'DangChay' THEN 'DaXacNhan'
        WHEN trang_thai = 'SapKhoiHanh' THEN 'DaXacNhan'
        WHEN trang_thai = 'DaXacNhan' THEN 'DaXacNhan'
        ELSE 'ChoXacNhan'
    END as trang_thai,
    'Migrated from hdv_id column' as ghi_chu
FROM lich_khoi_hanh
WHERE hdv_id IS NOT NULL
  AND NOT EXISTS (
      -- Tránh duplicate nếu đã có record
      SELECT 1 FROM phan_bo_nhan_su 
      WHERE lich_khoi_hanh_id = lich_khoi_hanh.id 
        AND nhan_su_id = lich_khoi_hanh.hdv_id
        AND vai_tro = 'HDV'
  );

-- Bước 2: Kiểm tra kết quả
SELECT 
    COUNT(*) as tong_so_phan_bo,
    SUM(CASE WHEN vai_tro = 'HDV' THEN 1 ELSE 0 END) as so_hdv,
    SUM(CASE WHEN trang_thai = 'DaXacNhan' THEN 1 ELSE 0 END) as da_xac_nhan
FROM phan_bo_nhan_su;

-- Bước 3 (optional): Sau khi xác nhận migration thành công, có thể xóa cột hdv_id
-- ALTER TABLE lich_khoi_hanh DROP COLUMN hdv_id;
-- CẢNH BÁO: Chỉ chạy lệnh trên khi đã chắc chắn migration thành công!
