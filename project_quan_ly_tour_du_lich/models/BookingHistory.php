<?php
// Model cho BookingHistory - Lịch sử thay đổi booking
class BookingHistory 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Lấy lịch sử thay đổi của một booking
    public function getByBookingId($bookingId) {
        $sql = "SELECT bh.*, 
                nd.ho_ten as nguoi_thay_doi, nd.vai_tro
                FROM booking_history bh
                LEFT JOIN nguoi_dung nd ON bh.nguoi_thay_doi_id = nd.id
                WHERE bh.booking_id = ? 
                ORDER BY bh.thoi_gian DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$bookingId]);
        return $stmt->fetchAll();
    }

    // Thêm lịch sử thay đổi
    public function insert($data) {
        $sql = "INSERT INTO booking_history (booking_id, trang_thai_cu, trang_thai_moi, nguoi_thay_doi_id, ghi_chu, thoi_gian) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['booking_id'] ?? 0,
            $data['trang_thai_cu'] ?? null,
            $data['trang_thai_moi'] ?? null,
            $data['nguoi_thay_doi_id'] ?? null,
            $data['ghi_chu'] ?? null,
            $data['thoi_gian'] ?? date('Y-m-d H:i:s')
        ]);
    }

    // Lấy tất cả lịch sử
    public function getAll() {
        $sql = "SELECT bh.*, 
                b.booking_id, b.tour_id,
                nd.ho_ten as nguoi_thay_doi, nd.vai_tro
                FROM booking_history bh
                LEFT JOIN booking b ON bh.booking_id = b.booking_id
                LEFT JOIN nguoi_dung nd ON bh.nguoi_thay_doi_id = nd.id
                ORDER BY bh.thoi_gian DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

