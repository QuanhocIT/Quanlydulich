<?php
// Model cho SupplierDeletionHistory - Lịch sử xóa nhà cung cấp
class SupplierDeletionHistory 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Lấy tất cả lịch sử xóa
    public function getAll() {
        $sql = "SELECT sdh.*, 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa,
                nd_supplier.ho_ten as ten_nha_cung_cap, nd_supplier.email as email_nha_cung_cap
                FROM lich_su_xoa_nha_cung_cap sdh
                LEFT JOIN nguoi_dung nd ON sdh.nguoi_xoa_id = nd.id
                LEFT JOIN nha_cung_cap ncc ON sdh.nha_cung_cap_id = ncc.id_nha_cung_cap
                LEFT JOIN nguoi_dung nd_supplier ON ncc.nguoi_dung_id = nd_supplier.id
                ORDER BY sdh.thoi_gian_xoa DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy lịch sử xóa theo nha_cung_cap_id (bản ghi mới nhất)
    public function getBySupplierId($supplierId) {
        $sql = "SELECT sdh.*, 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa
                FROM lich_su_xoa_nha_cung_cap sdh
                LEFT JOIN nguoi_dung nd ON sdh.nguoi_xoa_id = nd.id
                WHERE sdh.nha_cung_cap_id = ?
                ORDER BY sdh.thoi_gian_xoa DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$supplierId]);
        return $stmt->fetch();
    }

    // Lấy chi tiết lịch sử xóa theo id bản ghi
    public function getById($id) {
        $sql = "SELECT sdh.*, 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa,
                nd_supplier.ho_ten as ten_nha_cung_cap, nd_supplier.email as email_nha_cung_cap
                FROM lich_su_xoa_nha_cung_cap sdh
                LEFT JOIN nguoi_dung nd ON sdh.nguoi_xoa_id = nd.id
                LEFT JOIN nha_cung_cap ncc ON sdh.nha_cung_cap_id = ncc.id_nha_cung_cap
                LEFT JOIN nguoi_dung nd_supplier ON ncc.nguoi_dung_id = nd_supplier.id
                WHERE sdh.id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    // Thêm lịch sử xóa
    public function insert($data) {
        $sql = "INSERT INTO lich_su_xoa_nha_cung_cap (
                    nha_cung_cap_id, nguoi_dung_id,
                    nguoi_xoa_id, ly_do_xoa, thong_tin_nha_cung_cap, 
                    thoi_gian_xoa
                ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nha_cung_cap_id'] ?? null,
            $data['nguoi_dung_id'] ?? null,
            $data['nguoi_xoa_id'] ?? null,
            $data['ly_do_xoa'] ?? null,
            $data['thong_tin_nha_cung_cap'] ?? null,
            $data['thoi_gian_xoa'] ?? date('Y-m-d H:i:s')
        ]);
    }
}

