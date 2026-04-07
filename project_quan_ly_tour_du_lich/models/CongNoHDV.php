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
}
