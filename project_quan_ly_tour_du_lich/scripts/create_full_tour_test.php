<?php
/**
 * Script tạo tour hoàn chỉnh từ A-Z để test
 * Bao gồm: Tour, Lịch trình, Khách hàng, Booking, Check-in, Yêu cầu đặc biệt...
 */

require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../commons/function.php';

// Override connectDB cho script này
if (!function_exists('getPDOConnection')) {
    function getPDOConnection() {
        try {
            // Thử nhiều cách kết nối
            $hosts = [
                ['host' => '127.0.0.1', 'port' => 3306],
                ['host' => 'localhost', 'port' => 3306],
            ];
            
            // Thử socket của XAMPP
            $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
            if (file_exists($socket)) {
                $hosts[] = ['socket' => $socket];
            }
            
            $lastError = null;
            foreach ($hosts as $config) {
                try {
                    if (isset($config['socket'])) {
                        $dsn = "mysql:unix_socket={$config['socket']};dbname=" . DB_NAME . ";charset=utf8mb4";
                    } else {
                        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname=" . DB_NAME . ";charset=utf8mb4";
                    }
                    
                    $conn = new PDO(
                        $dsn,
                        DB_USERNAME,
                        DB_PASSWORD,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );
                    $conn->exec("SET time_zone = '+07:00'");
                    return $conn;
                } catch (PDOException $e) {
                    $lastError = $e->getMessage();
                    continue;
                }
            }
            
            throw new Exception("Không thể kết nối MySQL. Lỗi cuối: $lastError\nVui lòng đảm bảo MySQL/XAMPP đang chạy.");
        } catch (Exception $e) {
            die("Kết nối thất bại: " . $e->getMessage() . "\n");
        }
    }
}

require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/NguoiDung.php';
require_once __DIR__ . '/../models/KhachHang.php';
require_once __DIR__ . '/../models/NhanSu.php';
require_once __DIR__ . '/../models/LichKhoiHanh.php';

$conn = connectDB();

echo "🚀 Bắt đầu tạo tour hoàn chỉnh từ A-Z...\n\n";

try {
    $conn->beginTransaction();

    // ============================================================
    // 1. TẠO NGƯỜI DÙNG (Khách hàng)
    // ============================================================
    echo "1. Tạo người dùng khách hàng...\n";
    
    $khachHangData = [
        ['id' => 200, 'ten_dang_nhap' => 'khach1', 'ho_ten' => 'Nguyễn Văn An', 'email' => 'nguyenvanan@test.com', 'sdt' => '0911111111'],
        ['id' => 201, 'ten_dang_nhap' => 'khach2', 'ho_ten' => 'Trần Thị Bình', 'email' => 'tranthibinh@test.com', 'sdt' => '0922222222'],
        ['id' => 202, 'ten_dang_nhap' => 'khach3', 'ho_ten' => 'Lê Văn Cường', 'email' => 'levancuong@test.com', 'sdt' => '0933333333'],
        ['id' => 203, 'ten_dang_nhap' => 'khach4', 'ho_ten' => 'Phạm Thị Dung', 'email' => 'phamthidung@test.com', 'sdt' => '0944444444'],
    ];
    
    $khachHangIds = [];
    foreach ($khachHangData as $kh) {
        $sql = "INSERT INTO nguoi_dung (id, ten_dang_nhap, ho_ten, email, so_dien_thoai, vai_tro, mat_khau, ngay_tao) 
                VALUES (?, ?, ?, ?, ?, 'KhachHang', ?, NOW())
                ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten), email = VALUES(email)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$kh['id'], $kh['ten_dang_nhap'], $kh['ho_ten'], $kh['email'], $kh['sdt'], 
                       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi']);
        
        // Tạo khách hàng
        $sql = "INSERT INTO khach_hang (khach_hang_id, nguoi_dung_id, dia_chi, gioi_tinh, ngay_sinh) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE dia_chi = VALUES(dia_chi)";
        $stmt = $conn->prepare($sql);
        $genders = ['Nam', 'Nu', 'Nam', 'Nu'];
        $birthdays = ['1990-01-15', '1988-05-20', '1992-08-10', '1995-12-25'];
        $addresses = [
            '123 Đường ABC, Quận 1, Hà Nội',
            '456 Đường XYZ, Quận 3, TP.HCM',
            '789 Đường DEF, Quận Hải Châu, Đà Nẵng',
            '321 Đường GHI, Quận Thanh Khê, Đà Nẵng'
        ];
        $idx = array_search($kh['id'], array_column($khachHangData, 'id'));
        $stmt->execute([$kh['id'], $kh['id'], $addresses[$idx], $genders[$idx], $birthdays[$idx]]);
        $khachHangIds[] = $kh['id'];
        echo "   ✓ Tạo khách hàng: {$kh['ho_ten']}\n";
    }

    // ============================================================
    // 2. TẠO HDV
    // ============================================================
    echo "\n2. Tạo HDV...\n";
    
    $hdvUserId = 210;
    $hdvNhanSuId = 210;
    
    $sql = "INSERT INTO nguoi_dung (id, ten_dang_nhap, ho_ten, email, so_dien_thoai, vai_tro, mat_khau, ngay_tao) 
            VALUES (?, 'hdv_test', 'HDV Test Full', 'hdvtestfull@test.com', '0955555555', 'HDV', ?, NOW())
            ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hdvUserId, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi']);
    
    $sql = "INSERT INTO nhan_su (nhan_su_id, nguoi_dung_id, vai_tro, loai_hdv, chuyen_tuyen, danh_gia_tb, so_tour_da_dan, trang_thai_lam_viec, ngon_ngu, kinh_nghiem) 
            VALUES (?, ?, 'HDV', 'QuocTe', 'Đông Nam Á, Nhật Bản, Hàn Quốc', 4.80, 10, 'SanSang', 'Tiếng Việt, Tiếng Anh, Tiếng Nhật', '5 năm kinh nghiệm')
            ON DUPLICATE KEY UPDATE nguoi_dung_id = VALUES(nguoi_dung_id)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hdvNhanSuId, $hdvUserId]);
    echo "   ✓ Tạo HDV: HDV Test Full\n";

    // ============================================================
    // 3. TẠO TOUR
    // ============================================================
    echo "\n3. Tạo tour...\n";
    
    $tourId = 100; // Tour ID mới
    
    $sql = "INSERT INTO tour (tour_id, ten_tour, loai_tour, mo_ta, gia_co_ban, chinh_sach, trang_thai) 
            VALUES (?, 'NAGOYA – PHÚ SĨ – TOKYO (5 NGÀY 4 ĐÊM)', 'QuocTe', 
                    'Tour tham quan Nhật Bản với các điểm đến nổi tiếng: Nagoya, Núi Phú Sĩ, Tokyo. Trải nghiệm văn hóa, ẩm thực và cảnh đẹp Nhật Bản.', 
                    32990000.00, 
                    'Hủy trước 14 ngày: hoàn 80%. Hủy trước 7 ngày: hoàn 50%. Hủy trước 3 ngày: hoàn 30%.', 
                    'HoatDong')
            ON DUPLICATE KEY UPDATE ten_tour = VALUES(ten_tour), mo_ta = VALUES(mo_ta)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tourId]);
    echo "   ✓ Tạo tour ID: $tourId\n";

    // ============================================================
    // 4. TẠO LỊCH TRÌNH CHI TIẾT
    // ============================================================
    echo "\n4. Tạo lịch trình chi tiết...\n";
    
    $lichTrinhData = [
        [
            'ngay_thu' => 0,
            'dia_diem' => 'Sân bay Nội Bài – Ga đi quốc tế',
            'hoat_dong' => "🕘 Giờ tập trung: 21:00 (trước giờ bay 3 tiếng)\n👤 Hướng dẫn viên làm thủ tục & hỗ trợ đoàn."
        ],
        [
            'ngay_thu' => 1,
            'dia_diem' => 'HÀ NỘI → TOKYO (Narita)',
            'hoat_dong' => "✈️ Sáng / Trưa / Chiều:\n🕘 09:00 – Tập trung tại sân bay Nội Bài, HDV hỗ trợ check-in.\n🕙 12:00 – Cất cánh đi Nhật Bản.\n\n🌆 Chiều / Tối:\n🕕 18:00 – Hạ cánh sân bay Narita.\n🚌 Di chuyển về khách sạn nhận phòng.\n🍱 Tối: Ăn tối tại nhà hàng địa phương.\n🏨 Nghỉ đêm tại Tokyo / Narita."
        ],
        [
            'ngay_thu' => 2,
            'dia_diem' => 'NAGOYA – THÀNH PHỐ CẢNG',
            'hoat_dong' => "🍳 Sáng:\n🕗 08:00 – Ăn sáng tại khách sạn.\n🚌 Di chuyển đến Nagoya.\n🏯 Tham quan Lâu đài Nagoya – biểu tượng lịch sử nổi tiếng.\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa với món đặc sản Nagoya.\n\n🛍️ Chiều:\n🕒 14:00 – Tham quan & mua sắm tại khu vực Sakae sầm uất.\n\n🍱 Tối:\n🕕 18:00 – Thưởng thức món Tebasaki (gà rán kiểu Nagoya).\n🏨 Nghỉ đêm tại Nagoya."
        ],
        [
            'ngay_thu' => 3,
            'dia_diem' => 'NAGOYA – NÚI PHÚ SĨ – KAWAGUCHIKO',
            'hoat_dong' => "🍳 Sáng:\n🕗 08:00 – Ăn sáng tại khách sạn.\n🚌 Di chuyển đến khu vực núi Phú Sĩ.\n🏔️ Tham quan trạm 5 Núi Phú Sĩ (nếu thời tiết cho phép).\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa tại Kawaguchiko.\n\n🌅 Chiều:\n🌸 Tham quan Hồ Kawaguchiko – check-in với background núi Phú Sĩ.\n🏞️ Tham quan làng cổ Oshino Hakkai.\n\n🍱 Tối:\n🕕 18:00 – Ăn tối với set kaiseki Nhật Bản.\n🛁 Tắm onsen truyền thống tại khách sạn.\n🏨 Nghỉ đêm tại Kawaguchiko."
        ],
        [
            'ngay_thu' => 4,
            'dia_diem' => 'KAWAGUCHIKO – TOKYO',
            'hoat_dong' => "🍳 Sáng:\n🕗 07:30 – Ăn sáng và trả phòng.\n🚌 Khởi hành về Tokyo.\n\n🏙️ Trưa:\n🕛 12:00 – Ăn trưa tại Tokyo.\n\n🗼 Chiều – City Tour Tokyo:\n🏯 Viếng Chùa Asakusa – Đền Sensoji.\n🛍️ Tham quan mua sắm tại Nakamise.\n📷 Check-in tại Tokyo SkyTree (chụp ảnh bên ngoài).\n🚏 Ghé Shibuya Crossing & tượng Hachiko.\n\n🍱 Tối:\n🕕 18:00 – Ăn tối món Nhật.\n🏨 Nghỉ đêm tại Tokyo."
        ],
        [
            'ngay_thu' => 5,
            'dia_diem' => 'TOKYO – HÀ NỘI',
            'hoat_dong' => "🍳 Sáng:\n🕗 07:00 – Ăn sáng tại khách sạn.\n👜 Tự do mua sắm tại Aeon Mall hoặc Akihabara.\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa.\n\n✈️ Chiều:\n🚌 Di chuyển ra sân bay Narita.\n🕒 Làm thủ tục check-in.\n\n🌙 Tối:\n🛫 Bay về Hà Nội.\n🏁 Kết thúc hành trình – HDV chia tay đoàn."
        ]
    ];
    
    $sql = "DELETE FROM lich_trinh_tour WHERE tour_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tourId]);
    
    $sql = "INSERT INTO lich_trinh_tour (tour_id, ngay_thu, dia_diem, hoat_dong) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($lichTrinhData as $lt) {
        $stmt->execute([$tourId, $lt['ngay_thu'], $lt['dia_diem'], $lt['hoat_dong']]);
        echo "   ✓ Thêm lịch trình ngày {$lt['ngay_thu']}: {$lt['dia_diem']}\n";
    }

    // ============================================================
    // 5. TẠO LỊCH KHỞI HÀNH
    // ============================================================
    echo "\n5. Tạo lịch khởi hành...\n";
    
    $lichKhoiHanhId = 200;
    $ngayKhoiHanh = '2025-12-02';
    $ngayKetThuc = '2025-12-06';
    
    $sql = "INSERT INTO lich_khoi_hanh (id, tour_id, ngay_khoi_hanh, gio_xuat_phat, ngay_ket_thuc, gio_ket_thuc, diem_tap_trung, so_cho, hdv_id, trang_thai, ghi_chu) 
            VALUES (?, ?, ?, '21:00:00', ?, '18:00:00', 'Sân bay Nội Bài – Ga đi quốc tế', 50, ?, 'SapKhoiHanh', 'Lịch khởi hành test tour hoàn chỉnh')
            ON DUPLICATE KEY UPDATE ngay_khoi_hanh = VALUES(ngay_khoi_hanh), hdv_id = VALUES(hdv_id)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId, $tourId, $ngayKhoiHanh, $ngayKetThuc, $hdvNhanSuId]);
    echo "   ✓ Tạo lịch khởi hành ID: $lichKhoiHanhId (Ngày: $ngayKhoiHanh - $ngayKetThuc)\n";

    // ============================================================
    // 6. PHÂN BỔ HDV
    // ============================================================
    echo "\n6. Phân bổ HDV...\n";
    
    $sql = "INSERT INTO phan_bo_nhan_su (lich_khoi_hanh_id, nhan_su_id, vai_tro, ghi_chu, trang_thai, thoi_gian_xac_nhan) 
            VALUES (?, ?, 'HDV', 'Phân bổ HDV chính cho tour test', 'DaXacNhan', NOW())
            ON DUPLICATE KEY UPDATE trang_thai = 'DaXacNhan'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId, $hdvNhanSuId]);
    echo "   ✓ Phân bổ HDV cho lịch khởi hành\n";

    // ============================================================
    // 7. TẠO BOOKING
    // ============================================================
    echo "\n7. Tạo booking...\n";
    
    $bookingData = [
        ['id' => 200, 'khach_id' => 200, 'so_nguoi' => 2, 'tong_tien' => 65980000, 'trang_thai' => 'HoanTat'],
        ['id' => 201, 'khach_id' => 201, 'so_nguoi' => 3, 'tong_tien' => 98970000, 'trang_thai' => 'DaCoc'],
        ['id' => 202, 'khach_id' => 202, 'so_nguoi' => 1, 'tong_tien' => 32990000, 'trang_thai' => 'ChoXacNhan'],
        ['id' => 203, 'khach_id' => 203, 'so_nguoi' => 2, 'tong_tien' => 65980000, 'trang_thai' => 'DaCoc'],
    ];
    
    $sql = "DELETE FROM booking WHERE booking_id IN (200, 201, 202, 203)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $sql = "INSERT INTO booking (booking_id, khach_hang_id, tour_id, ngay_khoi_hanh, ngay_ket_thuc, so_nguoi, tong_tien, ngay_dat, trang_thai, ghi_chu) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($bookingData as $b) {
        $stmt->execute([
            $b['id'], 
            $b['khach_id'], 
            $tourId, 
            $ngayKhoiHanh, 
            $ngayKetThuc, 
            $b['so_nguoi'], 
            $b['tong_tien'], 
            $b['trang_thai'],
            "Booking test tour hoàn chỉnh - {$b['so_nguoi']} người"
        ]);
        echo "   ✓ Tạo booking ID: {$b['id']} ({$b['so_nguoi']} người, {$b['trang_thai']})\n";
    }

    // ============================================================
    // 8. TẠO ĐIỂM CHECK-IN
    // ============================================================
    echo "\n8. Tạo điểm check-in...\n";
    
    $diemCheckinData = [
        ['id' => 200, 'ten' => 'Sân bay Nội Bài - Điểm tập trung', 'loai' => 'tap_trung', 'thoi_gian' => '2025-12-02 21:00:00', 'thu_tu' => 1],
        ['id' => 201, 'ten' => 'Khách sạn Tokyo - Check-in', 'loai' => 'nghi_ngoi', 'thoi_gian' => '2025-12-02 20:00:00', 'thu_tu' => 2],
        ['id' => 202, 'ten' => 'Lâu đài Nagoya - Tham quan', 'loai' => 'tham_quan', 'thoi_gian' => '2025-12-03 10:00:00', 'thu_tu' => 3],
        ['id' => 203, 'ten' => 'Núi Phú Sĩ - Tham quan', 'loai' => 'tham_quan', 'thoi_gian' => '2025-12-04 09:00:00', 'thu_tu' => 4],
    ];
    
    $sql = "DELETE FROM diem_checkin WHERE tour_id = ? AND id IN (200, 201, 202, 203)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$tourId]);
    
    $sql = "INSERT INTO diem_checkin (id, tour_id, ten_diem, loai_diem, thoi_gian_du_kien, ghi_chu, thu_tu) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    foreach ($diemCheckinData as $dc) {
        $stmt->execute([$dc['id'], $tourId, $dc['ten'], $dc['loai'], $dc['thoi_gian'], "Điểm check-in test", $dc['thu_tu']]);
        echo "   ✓ Tạo điểm check-in: {$dc['ten']}\n";
    }

    // ============================================================
    // 9. TẠO TOUR_CHECKIN (Danh sách khách chi tiết)
    // ============================================================
    echo "\n9. Tạo danh sách khách chi tiết (tour_checkin)...\n";
    
    $sql = "DELETE FROM tour_checkin WHERE booking_id IN (200, 201, 202, 203)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Booking 200: 2 người
    $sql = "INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai) 
            SELECT 200, 200, ?, ho_ten, CONCAT('CMND200-1'), CONCAT('PASS200-1'), ngay_sinh, gioi_tinh, 'Việt Nam', so_dien_thoai, email, dia_chi, 'ChuaCheckIn'
            FROM khach_hang kh
            INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
            WHERE kh.khach_hang_id = 200";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId]);
    
    $sql = "INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai) 
            VALUES (200, 200, ?, 'Nguyễn Thị Lan - Người 2', 'CMND200-2', 'PASS200-2', '1992-03-20', 'Nu', 'Việt Nam', '0911111111', 'nguyenvanan@test.com', '123 Đường ABC, Quận 1, Hà Nội', 'ChuaCheckIn')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId]);
    
    // Booking 201: 3 người (2 lớn + 1 trẻ em)
    $sql = "INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai) 
            SELECT 201, 201, ?, ho_ten, CONCAT('CMND201-1'), CONCAT('PASS201-1'), ngay_sinh, gioi_tinh, 'Việt Nam', so_dien_thoai, email, dia_chi, 'ChuaCheckIn'
            FROM khach_hang kh
            INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
            WHERE kh.khach_hang_id = 201";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId]);
    
    $sql = "INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai) 
            VALUES 
            (201, 201, ?, 'Trần Văn Hùng - Người 2', 'CMND201-2', 'PASS201-2', '1990-07-15', 'Nam', 'Việt Nam', '0922222222', 'tranthibinh@test.com', '456 Đường XYZ, Quận 3, TP.HCM', 'ChuaCheckIn'),
            (201, 201, ?, 'Trần Thị Mai - Trẻ em', 'CMND201-3', 'PASS201-3', '2015-10-20', 'Nu', 'Việt Nam', NULL, NULL, '456 Đường XYZ, Quận 3, TP.HCM', 'ChuaCheckIn')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId, $lichKhoiHanhId]);
    
    // Booking 202: 1 người
    $sql = "INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai) 
            SELECT 202, 202, ?, ho_ten, CONCAT('CMND202-1'), CONCAT('PASS202-1'), ngay_sinh, gioi_tinh, 'Việt Nam', so_dien_thoai, email, dia_chi, 'ChuaCheckIn'
            FROM khach_hang kh
            INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
            WHERE kh.khach_hang_id = 202";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId]);
    
    // Booking 203: 2 người
    $sql = "INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai) 
            SELECT 203, 203, ?, ho_ten, CONCAT('CMND203-1'), CONCAT('PASS203-1'), ngay_sinh, gioi_tinh, 'Việt Nam', so_dien_thoai, email, dia_chi, 'ChuaCheckIn'
            FROM khach_hang kh
            INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
            WHERE kh.khach_hang_id = 203";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId]);
    
    $sql = "INSERT INTO tour_checkin (booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich, so_dien_thoai, email, dia_chi, trang_thai) 
            VALUES (203, 203, ?, 'Phạm Văn Đức - Người 2', 'CMND203-2', 'PASS203-2', '1993-04-12', 'Nam', 'Việt Nam', '0944444444', 'phamthidung@test.com', '321 Đường GHI, Quận Thanh Khê, Đà Nẵng', 'ChuaCheckIn')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$lichKhoiHanhId]);
    
    echo "   ✓ Đã tạo danh sách khách cho tất cả booking\n";

    // ============================================================
    // 10. TẠO CHECK-IN KHÁCH (Trạng thái check-in tại điểm)
    // ============================================================
    echo "\n10. Tạo trạng thái check-in tại điểm...\n";
    
    $sql = "DELETE FROM checkin_khach WHERE booking_id IN (200, 201, 202, 203)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Check-in tại điểm 200 (Sân bay) - Một số đã check-in
    $sql = "INSERT INTO checkin_khach (diem_checkin_id, booking_id, trang_thai, thoi_gian_checkin, ghi_chu, nguoi_checkin_id) 
            VALUES 
            (200, 200, 'da_checkin', NOW(), 'Đã check-in tại sân bay', ?),
            (200, 201, 'da_checkin', NOW(), 'Đã check-in tại sân bay', ?),
            (200, 202, 'chua_checkin', NULL, NULL, NULL),
            (200, 203, 'da_checkin', NOW(), 'Đã check-in tại sân bay', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hdvNhanSuId, $hdvNhanSuId, $hdvNhanSuId]);
    
    echo "   ✓ Đã tạo trạng thái check-in cho điểm sân bay\n";

    // ============================================================
    // 11. TẠO YÊU CẦU ĐẶC BIỆT
    // ============================================================
    echo "\n11. Tạo yêu cầu đặc biệt...\n";
    
    $sql = "DELETE FROM yeu_cau_dac_biet WHERE booking_id IN (200, 201, 202, 203)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $sql = "INSERT INTO yeu_cau_dac_biet (booking_id, loai_yeu_cau, tieu_de, mo_ta, muc_do_uu_tien, trang_thai, nguoi_tao_id) 
            VALUES 
            (200, 'an_uong', 'Dị ứng hải sản', 'Khách bị dị ứng hải sản, cần tránh các món có hải sản trong suốt chuyến đi', 'cao', 'moi', 200),
            (201, 'suc_khoe', 'Cần hỗ trợ di chuyển', 'Có trẻ em 10 tuổi, cần hỗ trợ khi di chuyển và tham quan', 'trung_binh', 'moi', 201),
            (202, 'phong_o', 'Phòng đơn', 'Yêu cầu phòng đơn riêng, không ở chung', 'thap', 'moi', 202),
            (203, 'khac', 'Yêu cầu đặc biệt về visa', 'Cần hỗ trợ đặc biệt về thủ tục visa và giấy tờ', 'trung_binh', 'moi', 203)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "   ✓ Đã tạo yêu cầu đặc biệt cho các booking\n";

    $conn->commit();
    
    echo "\n✅ ============================================================\n";
    echo "✅ HOÀN THÀNH! Đã tạo tour hoàn chỉnh từ A-Z\n";
    echo "✅ ============================================================\n\n";
    echo "📋 Thông tin tour:\n";
    echo "   - Tour ID: $tourId\n";
    echo "   - Tên tour: NAGOYA – PHÚ SĨ – TOKYO (5 NGÀY 4 ĐÊM)\n";
    echo "   - Lịch khởi hành ID: $lichKhoiHanhId\n";
    echo "   - Ngày khởi hành: $ngayKhoiHanh\n";
    echo "   - Ngày kết thúc: $ngayKetThuc\n";
    echo "   - HDV ID: $hdvNhanSuId\n";
    echo "   - Số booking: " . count($bookingData) . "\n";
    echo "   - Tổng số khách: " . array_sum(array_column($bookingData, 'so_nguoi')) . "\n";
    echo "   - Số điểm check-in: " . count($diemCheckinData) . "\n";
    echo "   - Số ngày lịch trình: " . count($lichTrinhData) . "\n\n";
    echo "🔗 Truy cập:\n";
    echo "   - Chi tiết tour: index.php?act=admin/chiTietTour&id=$tourId\n";
    echo "   - Check-in HDV: index.php?act=hdv/checkin&lich_id=$lichKhoiHanhId\n";
    echo "   - Yêu cầu đặc biệt: index.php?act=hdv/yeu_cau_dac_biet&tour_id=$lichKhoiHanhId\n\n";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

