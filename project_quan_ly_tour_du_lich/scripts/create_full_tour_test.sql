-- ============================================================
-- SCRIPT TẠO TOUR HOÀN CHỈNH TỪ A-Z ĐỂ TEST
-- Chạy script này trong phpMyAdmin hoặc MySQL client
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. TẠO NGƯỜI DÙNG (Khách hàng)
-- ============================================================
INSERT INTO nguoi_dung (id, ten_dang_nhap, ho_ten, email, so_dien_thoai, vai_tro, mat_khau, ngay_tao)
VALUES 
    (200, 'khach1', 'Nguyễn Văn An', 'nguyenvanan@test.com', '0911111111', 'KhachHang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
    (201, 'khach2', 'Trần Thị Bình', 'tranthibinh@test.com', '0922222222', 'KhachHang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
    (202, 'khach3', 'Lê Văn Cường', 'levancuong@test.com', '0933333333', 'KhachHang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
    (203, 'khach4', 'Phạm Thị Dung', 'phamthidung@test.com', '0944444444', 'KhachHang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW())
ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten), email = VALUES(email);

-- ============================================================
-- 2. TẠO KHÁCH HÀNG
-- ============================================================
INSERT INTO khach_hang (khach_hang_id, nguoi_dung_id, dia_chi, gioi_tinh, ngay_sinh)
VALUES 
    (200, 200, '123 Đường ABC, Quận 1, Hà Nội', 'Nam', '1990-01-15'),
    (201, 201, '456 Đường XYZ, Quận 3, TP.HCM', 'Nu', '1988-05-20'),
    (202, 202, '789 Đường DEF, Quận Hải Châu, Đà Nẵng', 'Nam', '1992-08-10'),
    (203, 203, '321 Đường GHI, Quận Thanh Khê, Đà Nẵng', 'Nu', '1995-12-25')
ON DUPLICATE KEY UPDATE dia_chi = VALUES(dia_chi);

-- ============================================================
-- 3. TẠO HDV

-- ============================================================
-- 4. TẠO TOUR
-- ============================================================
INSERT INTO tour (tour_id, ten_tour, loai_tour, mo_ta, gia_co_ban, chinh_sach, trang_thai)
VALUES 
    (100, 'NAGOYA – PHÚ SĨ – TOKYO (5 NGÀY 4 ĐÊM)', 'QuocTe', 
     'Tour tham quan Nhật Bản với các điểm đến nổi tiếng: Nagoya, Núi Phú Sĩ, Tokyo. Trải nghiệm văn hóa, ẩm thực và cảnh đẹp Nhật Bản.', 
     32990000.00, 
     'Hủy trước 14 ngày: hoàn 80%. Hủy trước 7 ngày: hoàn 50%. Hủy trước 3 ngày: hoàn 30%.', 
     'HoatDong')
ON DUPLICATE KEY UPDATE ten_tour = VALUES(ten_tour), mo_ta = VALUES(mo_ta);

-- ============================================================
-- 5. TẠO LỊCH TRÌNH CHI TIẾT
-- ============================================================
DELETE FROM lich_trinh_tour WHERE tour_id = 100;

INSERT INTO lich_trinh_tour (tour_id, ngay_thu, dia_diem, hoat_dong) VALUES
(100, 0, 'Sân bay Nội Bài – Ga đi quốc tế', '🕘 Giờ tập trung: 21:00 (trước giờ bay 3 tiếng)\n👤 Hướng dẫn viên làm thủ tục & hỗ trợ đoàn.'),
(100, 1, 'HÀ NỘI → TOKYO (Narita)', '✈️ Sáng / Trưa / Chiều:\n🕘 09:00 – Tập trung tại sân bay Nội Bài, HDV hỗ trợ check-in.\n🕙 12:00 – Cất cánh đi Nhật Bản.\n\n🌆 Chiều / Tối:\n🕕 18:00 – Hạ cánh sân bay Narita.\n🚌 Di chuyển về khách sạn nhận phòng.\n🍱 Tối: Ăn tối tại nhà hàng địa phương.\n🏨 Nghỉ đêm tại Tokyo / Narita.'),
(100, 2, 'NAGOYA – THÀNH PHỐ CẢNG', '🍳 Sáng:\n🕗 08:00 – Ăn sáng tại khách sạn.\n🚌 Di chuyển đến Nagoya.\n🏯 Tham quan Lâu đài Nagoya – biểu tượng lịch sử nổi tiếng.\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa với món đặc sản Nagoya.\n\n🛍️ Chiều:\n🕒 14:00 – Tham quan & mua sắm tại khu vực Sakae sầm uất.\n\n🍱 Tối:\n🕕 18:00 – Thưởng thức món Tebasaki (gà rán kiểu Nagoya).\n🏨 Nghỉ đêm tại Nagoya.'),
(100, 3, 'NAGOYA – NÚI PHÚ SĨ – KAWAGUCHIKO', '🍳 Sáng:\n🕗 08:00 – Ăn sáng tại khách sạn.\n🚌 Di chuyển đến khu vực núi Phú Sĩ.\n🏔️ Tham quan trạm 5 Núi Phú Sĩ (nếu thời tiết cho phép).\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa tại Kawaguchiko.\n\n🌅 Chiều:\n🌸 Tham quan Hồ Kawaguchiko – check-in với background núi Phú Sĩ.\n🏞️ Tham quan làng cổ Oshino Hakkai.\n\n🍱 Tối:\n🕕 18:00 – Ăn tối với set kaiseki Nhật Bản.\n🛁 Tắm onsen truyền thống tại khách sạn.\n🏨 Nghỉ đêm tại Kawaguchiko.'),
(100, 4, 'KAWAGUCHIKO – TOKYO', '🍳 Sáng:\n🕗 07:30 – Ăn sáng và trả phòng.\n🚌 Khởi hành về Tokyo.\n\n🏙️ Trưa:\n🕛 12:00 – Ăn trưa tại Tokyo.\n\n🗼 Chiều – City Tour Tokyo:\n🏯 Viếng Chùa Asakusa – Đền Sensoji.\n🛍️ Tham quan mua sắm tại Nakamise.\n📷 Check-in tại Tokyo SkyTree (chụp ảnh bên ngoài).\n🚏 Ghé Shibuya Crossing & tượng Hachiko.\n\n🍱 Tối:\n🕕 18:00 – Ăn tối món Nhật.\n🏨 Nghỉ đêm tại Tokyo.'),
(100, 5, 'TOKYO – HÀ NỘI', '🍳 Sáng:\n🕗 07:00 – Ăn sáng tại khách sạn.\n👜 Tự do mua sắm tại Aeon Mall hoặc Akihabara.\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa.\n\n✈️ Chiều:\n🚌 Di chuyển ra sân bay Narita.\n🕒 Làm thủ tục check-in.\n\n🌙 Tối:\n🛫 Bay về Hà Nội.\n🏁 Kết thúc hành trình – HDV chia tay đoàn.');

-- ============================================================
-- 6. TẠO LỊCH KHỞI HÀNH
-- Sử dụng HDV có sẵn: nhan_su_id = 2, nguoi_dung_id = 6
-- ============================================================
INSERT INTO lich_khoi_hanh (id, tour_id, ngay_khoi_hanh, gio_xuat_phat, ngay_ket_thuc, gio_ket_thuc, diem_tap_trung, so_cho, hdv_id, trang_thai, ghi_chu)
VALUES 
    (200, 100, '2025-12-02', '21:00:00', '2025-12-06', '18:00:00', 'Sân bay Nội Bài – Ga đi quốc tế', 50, 2, 'SapKhoiHanh', 'Lịch khởi hành test tour hoàn chỉnh')
ON DUPLICATE KEY UPDATE ngay_khoi_hanh = VALUES(ngay_khoi_hanh), hdv_id = VALUES(hdv_id);

-- ============================================================
-- 7. PHÂN BỔ HDV
-- Sử dụng HDV có sẵn: nhan_su_id = 2
-- ============================================================
INSERT INTO phan_bo_nhan_su (lich_khoi_hanh_id, nhan_su_id, vai_tro, ghi_chu, trang_thai, thoi_gian_xac_nhan)
VALUES 
    (200, 2, 'HDV', 'Phân bổ HDV chính cho tour test', 'DaXacNhan', NOW())
ON DUPLICATE KEY UPDATE trang_thai = 'DaXacNhan';

-- ============================================================
-- 8. TẠO BOOKING
-- ============================================================
DELETE FROM booking WHERE booking_id IN (200, 201, 202, 203);

INSERT INTO booking (booking_id, khach_hang_id, tour_id, ngay_khoi_hanh, ngay_ket_thuc, so_nguoi, tong_tien, ngay_dat, trang_thai, ghi_chu)
VALUES 
    (200, 200, 100, '2025-12-02', '2025-12-06', 2, 65980000.00, NOW(), 'HoanTat', 'Booking test tour hoàn chỉnh - 2 người'),
    (201, 201, 100, '2025-12-02', '2025-12-06', 3, 98970000.00, NOW(), 'DaCoc', 'Booking test tour hoàn chỉnh - 3 người (2 lớn + 1 trẻ em)'),
    (202, 202, 100, '2025-12-02', '2025-12-06', 1, 32990000.00, NOW(), 'ChoXacNhan', 'Booking test tour hoàn chỉnh - 1 người'),
    (203, 203, 100, '2025-12-02', '2025-12-06', 2, 65980000.00, NOW(), 'DaCoc', 'Booking test tour hoàn chỉnh - 2 người');

-- ============================================================
-- 9. TẠO ĐIỂM CHECK-IN
-- ============================================================
DELETE FROM diem_checkin WHERE tour_id = 100 AND id IN (200, 201, 202, 203);

INSERT INTO diem_checkin (id, tour_id, ten_diem, loai_diem, thoi_gian_du_kien, ghi_chu, thu_tu)
VALUES 
    (200, 100, 'Sân bay Nội Bài - Điểm tập trung', 'tap_trung', '2025-12-02 21:00:00', 'Điểm check-in test', 1),
    (201, 100, 'Khách sạn Tokyo - Check-in', 'nghi_ngoi', '2025-12-02 20:00:00', 'Điểm check-in test', 2),
    (202, 100, 'Lâu đài Nagoya - Tham quan', 'tham_quan', '2025-12-03 10:00:00', 'Điểm check-in test', 3),
    (203, 100, 'Núi Phú Sĩ - Tham quan', 'tham_quan', '2025-12-04 09:00:00', 'Điểm check-in test', 4);

-- ============================================================
-- 10. TẠO TOUR_CHECKIN (Danh sách khách chi tiết)
-- Đảm bảo số lượng khách khớp với so_nguoi trong booking
-- ============================================================
DELETE FROM tour_checkin WHERE booking_id IN (200, 201, 202, 203) AND lich_khoi_hanh_id = 200;

-- Booking 200: 2 người (khach_hang_id = 200)
INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai)
SELECT 200, 200, 200, nd.ho_ten, 'CMND200-1', 'PASS200-1', kh.ngay_sinh, kh.gioi_tinh, 'Việt Nam', nd.so_dien_thoai, nd.email, kh.dia_chi, 'ChuaCheckIn'
FROM khach_hang kh
INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
WHERE kh.khach_hang_id = 200;

-- Người thứ 2 trong booking 200 (người đi kèm, không có khach_hang_id riêng)
INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai)
VALUES (200, 200, 200, 'Nguyễn Thị Lan', 'CMND200-2', 'PASS200-2', '1992-03-20', 'Nu', 'Việt Nam', '0911111111', 'nguyenvanan@test.com', '123 Đường ABC, Quận 1, Hà Nội', 'ChuaCheckIn');

-- Booking 201: 3 người (khach_hang_id = 201)
INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai)
SELECT 201, 201, 200, nd.ho_ten, 'CMND201-1', 'PASS201-1', kh.ngay_sinh, kh.gioi_tinh, 'Việt Nam', nd.so_dien_thoai, nd.email, kh.dia_chi, 'ChuaCheckIn'
FROM khach_hang kh
INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
WHERE kh.khach_hang_id = 201;

-- Người thứ 2 và 3 trong booking 201
INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai)
VALUES 
    (201, 201, 200, 'Trần Văn Hùng', 'CMND201-2', 'PASS201-2', '1990-07-15', 'Nam', 'Việt Nam', '0922222222', 'tranthibinh@test.com', '456 Đường XYZ, Quận 3, TP.HCM', 'ChuaCheckIn'),
    (201, 201, 200, 'Trần Thị Mai', 'CMND201-3', 'PASS201-3', '2015-10-20', 'Nu', 'Việt Nam', NULL, NULL, '456 Đường XYZ, Quận 3, TP.HCM', 'ChuaCheckIn');

-- Booking 202: 1 người (khach_hang_id = 202)
INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai)
SELECT 202, 202, 200, nd.ho_ten, 'CMND202-1', 'PASS202-1', kh.ngay_sinh, kh.gioi_tinh, 'Việt Nam', nd.so_dien_thoai, nd.email, kh.dia_chi, 'ChuaCheckIn'
FROM khach_hang kh
INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
WHERE kh.khach_hang_id = 202;

-- Booking 203: 2 người (khach_hang_id = 203)
INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai)
SELECT 203, 203, 200, nd.ho_ten, 'CMND203-1', 'PASS203-1', kh.ngay_sinh, kh.gioi_tinh, 'Việt Nam', nd.so_dien_thoai, nd.email, kh.dia_chi, 'ChuaCheckIn'
FROM khach_hang kh
INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
WHERE kh.khach_hang_id = 203;

-- Người thứ 2 trong booking 203
INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai)
VALUES (203, 203, 200, 'Phạm Văn Đức', 'CMND203-2', 'PASS203-2', '1993-04-12', 'Nam', 'Việt Nam', '0944444444', 'phamthidung@test.com', '321 Đường GHI, Quận Thanh Khê, Đà Nẵng', 'ChuaCheckIn');

-- ============================================================
-- 11. TẠO CHECK-IN KHÁCH (Trạng thái check-in tại điểm)
-- Lưu ý: checkin_khach lưu theo booking_id (không phải từng khách riêng)
-- Mỗi booking có 1 record check-in tại mỗi điểm check-in
-- ============================================================
DELETE FROM checkin_khach WHERE booking_id IN (200, 201, 202, 203) AND diem_checkin_id = 200;

-- Check-in tại điểm 200 (Sân bay Nội Bài) cho các booking
-- Booking 200: đã check-in
INSERT INTO checkin_khach (diem_checkin_id, booking_id, trang_thai, thoi_gian_checkin, ghi_chu, nguoi_checkin_id)
VALUES (200, 200, 'da_checkin', NOW(), 'Đã check-in tại sân bay - 2 người', 2);

-- Booking 201: đã check-in
INSERT INTO checkin_khach (diem_checkin_id, booking_id, trang_thai, thoi_gian_checkin, ghi_chu, nguoi_checkin_id)
VALUES (200, 201, 'da_checkin', NOW(), 'Đã check-in tại sân bay - 3 người', 2);

-- Booking 202: chưa check-in
INSERT INTO checkin_khach (diem_checkin_id, booking_id, trang_thai, thoi_gian_checkin, ghi_chu, nguoi_checkin_id)
VALUES (200, 202, 'chua_checkin', NULL, NULL, NULL);

-- Booking 203: đã check-in
INSERT INTO checkin_khach (diem_checkin_id, booking_id, trang_thai, thoi_gian_checkin, ghi_chu, nguoi_checkin_id)
VALUES (200, 203, 'da_checkin', NOW(), 'Đã check-in tại sân bay - 2 người', 2);

-- ============================================================
-- 12. TẠO YÊU CẦU ĐẶC BIỆT
-- ============================================================
DELETE FROM yeu_cau_dac_biet WHERE booking_id IN (200, 201, 202, 203);

INSERT INTO yeu_cau_dac_biet (booking_id, loai_yeu_cau, tieu_de, mo_ta, muc_do_uu_tien, trang_thai, nguoi_tao_id)
VALUES 
    (200, 'an_uong', 'Dị ứng hải sản', 'Khách bị dị ứng hải sản, cần tránh các món có hải sản trong suốt chuyến đi', 'cao', 'moi', 200),
    (201, 'suc_khoe', 'Cần hỗ trợ di chuyển', 'Có trẻ em 10 tuổi, cần hỗ trợ khi di chuyển và tham quan', 'trung_binh', 'moi', 201),
    (202, 'phong_o', 'Phòng đơn', 'Yêu cầu phòng đơn riêng, không ở chung', 'thap', 'moi', 202),
    (203, 'khac', 'Yêu cầu đặc biệt về visa', 'Cần hỗ trợ đặc biệt về thủ tục visa và giấy tờ', 'trung_binh', 'moi', 203);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- HOÀN THÀNH!
-- ============================================================
-- Tour ID: 100
-- Lịch khởi hành ID: 200
-- HDV ID: 2 (nhan_su_id = 2, nguoi_dung_id = 6)
-- Số booking: 4
-- Tổng số khách: 8
-- ============================================================

