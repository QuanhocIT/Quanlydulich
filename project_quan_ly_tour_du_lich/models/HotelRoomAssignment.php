<?php

class HotelRoomAssignment {
    public $conn;
    
    public function __construct() {
        $this->conn = connectDB();
    }

    // Lấy tất cả phân phòng
    public function getAll() {
        $sql = "SELECT hra.*, 
                       tc.ho_ten as khach_ho_ten,
                       b.tour_id
                FROM hotel_room_assignment hra
                LEFT JOIN tour_checkin tc ON hra.checkin_id = tc.id
                LEFT JOIN booking b ON hra.booking_id = b.booking_id
                ORDER BY hra.ngay_nhan_phong DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy phân phòng theo ID
    public function findById($id) {
        $sql = "SELECT * FROM hotel_room_assignment WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Lấy phân phòng theo lịch khởi hành
    public function getByLichKhoiHanhId($lichKhoiHanhId) {
        $sql = "SELECT hra.*, 
                       tc.ho_ten as khach_ho_ten, tc.so_dien_thoai,
                       b.booking_id
                FROM hotel_room_assignment hra
                LEFT JOIN tour_checkin tc ON hra.checkin_id = tc.id
                LEFT JOIN booking b ON hra.booking_id = b.booking_id
                WHERE hra.lich_khoi_hanh_id = ?
                ORDER BY hra.ten_khach_san, hra.so_phong";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Lấy phân phòng theo booking
    public function getByBookingId($bookingId) {
        $sql = "SELECT * FROM hotel_room_assignment WHERE booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    // Thêm phân phòng mới
    public function insert($data) {
        $sql = "INSERT INTO hotel_room_assignment (
                    lich_khoi_hanh_id, booking_id, checkin_id, ten_khach_san,
                    so_phong, loai_phong, so_giuong, ngay_nhan_phong, ngay_tra_phong,
                    gia_phong, trang_thai, ghi_chu
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['lich_khoi_hanh_id'],
            $data['booking_id'],
            $data['checkin_id'] ?? null,
            $data['ten_khach_san'],
            $data['so_phong'],
            $data['loai_phong'] ?? 'Standard',
            $data['so_giuong'] ?? 1,
            $data['ngay_nhan_phong'],
            $data['ngay_tra_phong'],
            $data['gia_phong'] ?? 0,
            $data['trang_thai'] ?? 'DaDatPhong',
            $data['ghi_chu'] ?? null
        ]);
    }

    // Cập nhật phân phòng
    public function update($id, $data) {
        $sql = "UPDATE hotel_room_assignment SET 
                ten_khach_san = ?, so_phong = ?, loai_phong = ?, so_giuong = ?,
                ngay_nhan_phong = ?, ngay_tra_phong = ?, gia_phong = ?,
                trang_thai = ?, ghi_chu = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ten_khach_san'],
            $data['so_phong'],
            $data['loai_phong'] ?? 'Standard',
            $data['so_giuong'] ?? 1,
            $data['ngay_nhan_phong'],
            $data['ngay_tra_phong'],
            $data['gia_phong'] ?? 0,
            $data['trang_thai'] ?? 'DaDatPhong',
            $data['ghi_chu'] ?? null,
            $id
        ]);
    }

    // Cập nhật trạng thái
    public function updateStatus($id, $status) {
        $sql = "UPDATE hotel_room_assignment SET trang_thai = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // Xóa phân phòng
    public function delete($id) {
        $sql = "DELETE FROM hotel_room_assignment WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Lấy danh sách khách sạn đã sử dụng
    public function getHotelList() {
        $sql = "SELECT DISTINCT ten_khach_san FROM hotel_room_assignment ORDER BY ten_khach_san";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Thống kê phòng theo lịch khởi hành
    public function getStatsByLichKhoiHanh($lichKhoiHanhId) {
        $sql = "SELECT 
                COUNT(*) as total_rooms,
                SUM(CASE WHEN trang_thai = 'DaDatPhong' THEN 1 ELSE 0 END) as da_dat,
                SUM(CASE WHEN trang_thai = 'DaNhanPhong' THEN 1 ELSE 0 END) as da_nhan,
                SUM(CASE WHEN trang_thai = 'DaTraPhong' THEN 1 ELSE 0 END) as da_tra,
                SUM(gia_phong) as tong_chi_phi
                FROM hotel_room_assignment 
                WHERE lich_khoi_hanh_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetch();
    }
}
