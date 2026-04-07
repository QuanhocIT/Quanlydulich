-- Chạy 1 lần trong MySQL để hỗ trợ:
-- 1) Admin cấu hình % hoa hồng HDV theo lịch khởi hành trước khi tour bắt đầu
-- 2) Lương HDV theo tháng = lương cứng (nhân sự) + tổng hoa hồng các tour dẫn trong tháng

-- 1) % hoa hồng HDV theo lịch khởi hành
ALTER TABLE lich_khoi_hanh
  ADD COLUMN phan_tram_hoa_hong_hdv DECIMAL(5,2) NOT NULL DEFAULT 0;

-- 2) Lương cứng theo nhân sự
ALTER TABLE nhan_su
  ADD COLUMN luong_co_ban DECIMAL(15,2) NOT NULL DEFAULT 0;

