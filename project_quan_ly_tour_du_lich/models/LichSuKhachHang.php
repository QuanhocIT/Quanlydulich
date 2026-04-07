<?php
// Model quản lý lịch sử tương tác, chăm sóc khách hàng
class LichSuKhachHang 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Lấy toàn bộ lịch sử của một khách hàng
    public function getByKhachHang($khachHangId) {
        $sql = "SELECT ls.*, 
                       nd.ho_ten as nguoi_tao
                FROM lich_su_khach_hang ls
                LEFT JOIN nguoi_dung nd ON ls.nguoi_tao_id = nd.id
                WHERE ls.khach_hang_id = ?
                ORDER BY ls.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        return $stmt->fetchAll();
    }

    // Lấy lịch sử booking của khách hàng
    public function getLichSuBooking($khachHangId) {
        $sql = "SELECT b.*, 
                       t.ten_tour,
                       t.hinh_anh,
                       lkh.ngay_khoi_hanh,
                       lkh.ngay_ket_thuc
                FROM booking b
                LEFT JOIN tour t ON b.tour_id = t.tour_id
                LEFT JOIN lich_khoi_hanh lkh ON b.lich_khoi_hanh_id = lkh.id
                WHERE b.khach_hang_id = ?
                ORDER BY b.ngay_dat DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        return $stmt->fetchAll();
    }

    // Lấy lịch sử thanh toán
    public function getLichSuThanhToan($khachHangId) {
        $sql = "SELECT gd.*, 
                       t.ten_tour,
                       b.booking_id
                FROM giao_dich_tai_chinh gd
                LEFT JOIN booking b ON gd.booking_id = b.booking_id
                LEFT JOIN tour t ON gd.tour_id = t.tour_id
                WHERE b.khach_hang_id = ? 
                  AND gd.loai = 'Thu'
                  AND gd.loai_giao_dich IN ('Booking', 'ThanhToan', 'HoanTien')
                ORDER BY gd.ngay_giao_dich DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        return $stmt->fetchAll();
    }

    // Thêm ghi chú tương tác/chăm sóc
    public function themTuongTac($data) {
        $sql = "INSERT INTO lich_su_khach_hang 
                (khach_hang_id, loai_hoat_dong, noi_dung, nguoi_tao_id) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['khach_hang_id'],
            $data['loai_hoat_dong'], // TuVan, GuiUuDai, NhacLich, GoiDien, Email, etc.
            $data['noi_dung'],
            $data['nguoi_tao_id']
        ]);
        return $this->conn->lastInsertId();
    }

    // Tự động ghi lịch sử khi có booking
    public function ghiLichSuBooking($khachHangId, $bookingId, $tourId, $nguoiTaoId = null) {
        $tour = (new Tour())->findById($tourId);
        $noidung = "Đặt tour: " . ($tour['ten_tour'] ?? 'N/A') . " (Booking #$bookingId)";
        
        return $this->themTuongTac([
            'khach_hang_id' => $khachHangId,
            'loai_hoat_dong' => 'Booking',
            'noi_dung' => $noidung,
            'nguoi_tao_id' => $nguoiTaoId
        ]);
    }

    // Tự động ghi lịch sử thanh toán
    public function ghiLichSuThanhToan($khachHangId, $bookingId, $soTien, $nguoiTaoId = null) {
        $noidung = "Thanh toán " . number_format($soTien) . "đ cho Booking #$bookingId";
        
        return $this->themTuongTac([
            'khach_hang_id' => $khachHangId,
            'loai_hoat_dong' => 'ThanhToan',
            'noi_dung' => $noidung,
            'nguoi_tao_id' => $nguoiTaoId
        ]);
    }

    // Lấy danh sách khách hàng cần tái kích hoạt (>6 tháng không đặt tour)
    public function getKhachCanTaiKichHoat($soThang = 6) {
        $sql = "SELECT kh.*, 
                       nd.email,
                       nd.so_dien_thoai,
                       MAX(b.ngay_dat) as lan_dat_cuoi
                FROM khach_hang kh
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                LEFT JOIN booking b ON kh.khach_hang_id = b.khach_hang_id
                GROUP BY kh.khach_hang_id
                HAVING lan_dat_cuoi IS NULL 
                   OR lan_dat_cuoi < DATE_SUB(NOW(), INTERVAL ? MONTH)
                ORDER BY lan_dat_cuoi ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$soThang]);
        return $stmt->fetchAll();
    }

    // Phân loại khách hàng (VIP, Thường xuyên, Mới)
    public function phanLoaiKhachHang($khachHangId) {
        // Đếm số lần đặt tour
        $sql = "SELECT COUNT(*) as so_booking, 
                       SUM(tong_tien) as tong_chi_tieu,
                       MAX(ngay_dat) as lan_dat_cuoi
                FROM booking 
                WHERE khach_hang_id = ? AND trang_thai != 'Huy'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        $stats = $stmt->fetch();
        
        if ($stats['so_booking'] == 0) {
            return 'Mới';
        } elseif ($stats['so_booking'] >= 5 || $stats['tong_chi_tieu'] >= 50000000) {
            return 'VIP';
        } elseif ($stats['so_booking'] >= 2) {
            return 'Thường xuyên';
        } else {
            return 'Khách lẻ';
        }
    }

    // Lấy thống kê khách hàng
    public function getThongKeKhachHang($khachHangId) {
        $sql = "SELECT 
                    COUNT(*) as tong_booking,
                    COUNT(CASE WHEN trang_thai = 'HoanTat' THEN 1 END) as booking_hoan_tat,
                    COUNT(CASE WHEN trang_thai = 'Huy' THEN 1 END) as booking_huy,
                    SUM(CASE WHEN trang_thai != 'Huy' THEN tong_tien ELSE 0 END) as tong_chi_tieu,
                    MIN(ngay_dat) as khach_tu_ngay,
                    MAX(ngay_dat) as lan_dat_cuoi
                FROM booking 
                WHERE khach_hang_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        return $stmt->fetch();
    }

    // Lấy tour yêu thích của khách (đã đặt nhiều lần)
    public function getTourYeuThich($khachHangId) {
        $sql = "SELECT t.*, COUNT(*) as so_lan_dat
                FROM booking b
                JOIN tour t ON b.tour_id = t.tour_id
                WHERE b.khach_hang_id = ? AND b.trang_thai != 'Huy'
                GROUP BY t.tour_id
                ORDER BY so_lan_dat DESC
                LIMIT 5";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        return $stmt->fetchAll();
    }

    // Gửi ưu đãi cho khách hàng
    public function guiUuDai($khachHangId, $noiDung, $nguoiGuiId) {
        return $this->themTuongTac([
            'khach_hang_id' => $khachHangId,
            'loai_hoat_dong' => 'GuiUuDai',
            'noi_dung' => $noiDung,
            'nguoi_tao_id' => $nguoiGuiId
        ]);
    }

    // Nhắc lịch cho khách hàng
    public function nhacLich($khachHangId, $noiDung, $nguoiTaoId) {
        return $this->themTuongTac([
            'khach_hang_id' => $khachHangId,
            'loai_hoat_dong' => 'NhacLich',
            'noi_dung' => $noiDung,
            'nguoi_tao_id' => $nguoiTaoId
        ]);
    }

    // Timeline hoạt động khách hàng (kết hợp booking + lịch sử + giao dịch)
    public function getTimeline($khachHangId) {
        $timeline = [];
        
        // Lấy booking
        $bookings = $this->getLichSuBooking($khachHangId);
        foreach ($bookings as $b) {
            $timeline[] = [
                'type' => 'booking',
                'date' => $b['ngay_dat'],
                'data' => $b
            ];
        }
        
        // Lấy lịch sử tương tác
        $lichSu = $this->getByKhachHang($khachHangId);
        foreach ($lichSu as $ls) {
            $timeline[] = [
                'type' => 'tuong_tac',
                'date' => $ls['created_at'],
                'data' => $ls
            ];
        }
        
        // Lấy giao dịch
        $giaoDich = $this->getLichSuThanhToan($khachHangId);
        foreach ($giaoDich as $gd) {
            $timeline[] = [
                'type' => 'thanh_toan',
                'date' => $gd['ngay_giao_dich'],
                'data' => $gd
            ];
        }
        
        // Sắp xếp theo thời gian
        usort($timeline, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $timeline;
    }
}
