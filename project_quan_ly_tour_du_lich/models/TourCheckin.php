<?php

class TourCheckin {
    public $conn;
    
    public function __construct() {
        $this->conn = connectDB();
    }

    // Lấy tất cả check-in
    public function getAll() {
        $sql = "SELECT tc.*, 
                       b.tour_id, b.so_nguoi, b.ngay_khoi_hanh,
                       kh.dia_chi as khach_hang_dia_chi,
                       nd.ho_ten as nguoi_dung_ho_ten, nd.email as nguoi_dung_email, nd.so_dien_thoai as nguoi_dung_sdt
                FROM tour_checkin tc
                LEFT JOIN booking b ON tc.booking_id = b.booking_id
                LEFT JOIN khach_hang kh ON tc.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                ORDER BY tc.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy check-in theo ID
    public function findById($id) {
        $sql = "SELECT * FROM tour_checkin WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Lấy check-in theo booking
    public function getByBookingId($bookingId) {
        $sql = "SELECT tc.*, 
                       nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM tour_checkin tc
                LEFT JOIN khach_hang kh ON tc.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE tc.booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    // Lấy check-in theo lịch khởi hành
    public function getByLichKhoiHanhId($lichKhoiHanhId) {
        $sql = "SELECT tc.*, 
                       b.booking_id, b.so_nguoi, b.tong_tien,
                       nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM tour_checkin tc
                LEFT JOIN booking b ON tc.booking_id = b.booking_id
                LEFT JOIN khach_hang kh ON tc.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE tc.lich_khoi_hanh_id = ?
                ORDER BY tc.checkin_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Thêm check-in mới
    public function insert($data) {
        $sql = "INSERT INTO tour_checkin (
                    booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, 
                    so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich,
                    dia_chi, so_dien_thoai, email, checkin_time, trang_thai, ghi_chu
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['booking_id'],
            $data['khach_hang_id'],
            $data['lich_khoi_hanh_id'] ?? null,
            $data['ho_ten'],
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['checkin_time'] ?? date('Y-m-d H:i:s'),
            $data['trang_thai'] ?? 'DaCheckIn',
            $data['ghi_chu'] ?? null
        ]);
    }

    // Cập nhật check-in
    public function update($id, $data) {
        $sql = "UPDATE tour_checkin SET 
                ho_ten = ?, so_cmnd = ?, so_passport = ?, ngay_sinh = ?,
                gioi_tinh = ?, quoc_tich = ?, dia_chi = ?, so_dien_thoai = ?,
                email = ?, trang_thai = ?, ghi_chu = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ho_ten'],
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['trang_thai'] ?? 'DaCheckIn',
            $data['ghi_chu'] ?? null,
            $id
        ]);
    }

    // Checkout
    public function checkout($id) {
        $sql = "UPDATE tour_checkin SET 
                trang_thai = 'DaCheckOut', 
                checkout_time = NOW() 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Xóa check-in
    public function delete($id) {
        $sql = "DELETE FROM tour_checkin WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Thống kê check-in theo lịch khởi hành
    public function getStatsByLichKhoiHanh($lichKhoiHanhId) {
        $sql = "SELECT 
                COUNT(*) as total_checkin,
                SUM(CASE WHEN trang_thai = 'DaCheckIn' THEN 1 ELSE 0 END) as da_checkin,
                SUM(CASE WHEN trang_thai = 'ChuaCheckIn' THEN 1 ELSE 0 END) as chua_checkin,
                SUM(CASE WHEN trang_thai = 'DaCheckOut' THEN 1 ELSE 0 END) as da_checkout
                FROM tour_checkin 
                WHERE lich_khoi_hanh_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetch();
    }
}
