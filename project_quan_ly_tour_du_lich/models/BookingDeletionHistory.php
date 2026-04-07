<?php
// Model cho BookingDeletionHistory - Lịch sử xóa booking
class BookingDeletionHistory 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Lấy tất cả lịch sử xóa
    public function getAll() {
        $sql = "SELECT bdh.*, 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa,
                t.ten_tour, t.tour_id,
                kh.khach_hang_id,
                nd_khach.ho_ten as ten_khach_hang, nd_khach.email as email_khach_hang
                FROM booking_deletion_history bdh
                LEFT JOIN nguoi_dung nd ON bdh.nguoi_xoa_id = nd.id
                LEFT JOIN tour t ON bdh.tour_id = t.tour_id
                LEFT JOIN khach_hang kh ON bdh.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd_khach ON kh.nguoi_dung_id = nd_khach.id
                ORDER BY bdh.thoi_gian_xoa DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy lịch sử xóa theo booking_id (nếu còn lưu)
    public function getByBookingId($bookingId) {
        $sql = "SELECT bdh.*, 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa
                FROM booking_deletion_history bdh
                LEFT JOIN nguoi_dung nd ON bdh.nguoi_xoa_id = nd.id
                WHERE bdh.booking_id = ?
                ORDER BY bdh.thoi_gian_xoa DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$bookingId]);
        return $stmt->fetch();
    }

    // Thêm lịch sử xóa
    public function insert($data) {
        $sql = "INSERT INTO booking_deletion_history (
                    booking_id, tour_id, khach_hang_id, 
                    nguoi_xoa_id, ly_do_xoa, thong_tin_booking, 
                    thoi_gian_xoa
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['booking_id'] ?? null,
            $data['tour_id'] ?? null,
            $data['khach_hang_id'] ?? null,
            $data['nguoi_xoa_id'] ?? null,
            $data['ly_do_xoa'] ?? null,
            $data['thong_tin_booking'] ?? null,
            $data['thoi_gian_xoa'] ?? date('Y-m-d H:i:s')
        ]);
    }
}

