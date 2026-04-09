<?php
class CongNoHDV {
    public $conn;
    public function __construct() {
        $this->conn = connectDB();
    }
    // Tạo mới hóa đơn công nợ HDV
    public function create($data) {
        $sql = "INSERT INTO cong_no_hdv (tour_id, hdv_id, so_tien, loai_cong_no, anh_hoa_don, trang_thai, ngay_gui, ghi_chu) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tour_id'],
            $data['hdv_id'],
            $data['so_tien'],
            $data['loai_cong_no'],
            $data['anh_hoa_don'],
            $data['trang_thai'],
            $data['ghi_chu'] ?? null
        ]);
    }
    // Lấy danh sách hóa đơn công nợ theo HDV
    public function getByHDV($hdv_id) {
        $sql = "SELECT * FROM cong_no_hdv WHERE hdv_id = ? ORDER BY ngay_gui DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdv_id]);
        return $stmt->fetchAll();
    }
    // Lấy danh sách hóa đơn chờ duyệt cho admin
    public function getChoDuyet() {
        $sql = "SELECT cnh.*, t.ten_tour, nd.ho_ten as ten_hdv FROM cong_no_hdv cnh JOIN tour t ON cnh.tour_id = t.tour_id JOIN nguoi_dung nd ON cnh.hdv_id = nd.id WHERE cnh.trang_thai = 'ChoDuyet' ORDER BY cnh.ngay_gui DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    // Duyệt hóa đơn
    public function approve($id) {
        $sql = "UPDATE cong_no_hdv SET trang_thai = 'DaDuyet' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    // Từ chối hóa đơn
    public function reject($id, $ly_do) {
        $sql = "UPDATE cong_no_hdv SET trang_thai = 'TuChoi', ghi_chu = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$ly_do, $id]);
    }

    // Lấy số công nợ quá hạn theo đúng nghiệp vụ: còn dư nợ và đã quá hạn thanh toán.
    public function getQuaHanCount($days = 7) {
        $sql = "SELECT COUNT(*) AS total
                FROM cong_no_hdv c
                LEFT JOIN (
                    SELECT cong_no_hdv_id, COALESCE(SUM(so_tien), 0) AS tong_da_thanh_toan
                    FROM lich_su_thanh_toan_hdv
                    GROUP BY cong_no_hdv_id
                ) ls ON ls.cong_no_hdv_id = c.id
                WHERE c.han_thanh_toan IS NOT NULL
                  AND c.han_thanh_toan < CURDATE()
                  AND (COALESCE(c.so_tien, 0) - COALESCE(ls.tong_da_thanh_toan, 0)) > 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    // Lấy danh sách công nợ quá hạn
    public function getQuaHanList($days = 7) {
        $sql = "SELECT cnh.*, t.ten_tour, nd.ho_ten as ten_hdv FROM cong_no_hdv cnh 
                JOIN tour t ON cnh.tour_id = t.tour_id 
                JOIN nguoi_dung nd ON cnh.hdv_id = nd.id 
                WHERE cnh.trang_thai NOT IN ('DaDuyet', 'TuChoi') 
                AND cnh.ngay_gui < DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY cnh.ngay_gui ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
