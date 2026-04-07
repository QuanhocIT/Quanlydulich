<?php
class NhaCungCap 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    public function getAll() {
        $sql = "SELECT * FROM nha_cung_cap";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT * FROM nha_cung_cap WHERE id_nha_cung_cap = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByUserId($userId) {
        $sql = "SELECT * FROM nha_cung_cap WHERE nguoi_dung_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    // Lấy danh sách dịch vụ được phân bổ kèm bộ lọc
    public function getDichVu($nhaCungCapId, $filters = null) {
        $sql = "SELECT pbdv.*, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc, 
                t.ten_tour, t.tour_id
                FROM phan_bo_dich_vu pbdv
                LEFT JOIN lich_khoi_hanh lkh ON pbdv.lich_khoi_hanh_id = lkh.id
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                WHERE pbdv.nha_cung_cap_id = ?";
        $params = [$nhaCungCapId];

        if (is_array($filters)) {
            if (!empty($filters['trang_thai'])) {
                $sql .= " AND pbdv.trang_thai = ?";
                $params[] = $filters['trang_thai'];
            }
            if (!empty($filters['loai_dich_vu'])) {
                $sql .= " AND pbdv.loai_dich_vu = ?";
                $params[] = $filters['loai_dich_vu'];
            }
            if (!empty($filters['keyword'])) {
                $sql .= " AND (t.ten_tour LIKE ? OR pbdv.ten_dich_vu LIKE ?)";
                $keyword = '%' . $filters['keyword'] . '%';
                $params[] = $keyword;
                $params[] = $keyword;
            }
        } elseif (!empty($filters)) {
            // Giữ tương thích cũ: truyền chuỗi trạng thái
            $sql .= " AND pbdv.trang_thai = ?";
            $params[] = $filters;
        }

        $sql .= " ORDER BY 
                    CASE pbdv.trang_thai 
                        WHEN 'ChoXacNhan' THEN 1
                        WHEN 'DaXacNhan' THEN 2
                        WHEN 'HoanTat' THEN 3
                        WHEN 'TuChoi' THEN 4
                        ELSE 5
                    END,
                    pbdv.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy chi tiết một dịch vụ theo ID
    public function getDichVuById($dichVuId, $nhaCungCapId = null) {
        $sql = "SELECT pbdv.*, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc, lkh.id as lich_khoi_hanh_id,
                t.ten_tour, t.tour_id, t.mo_ta as tour_mo_ta,
                ncc.ten_don_vi as nha_cung_cap_ten
                FROM phan_bo_dich_vu pbdv
                LEFT JOIN lich_khoi_hanh lkh ON pbdv.lich_khoi_hanh_id = lkh.id
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                LEFT JOIN nha_cung_cap ncc ON pbdv.nha_cung_cap_id = ncc.id_nha_cung_cap
                WHERE pbdv.id = ?";
        $params = [$dichVuId];
        if ($nhaCungCapId !== null) {
            $sql .= " AND pbdv.nha_cung_cap_id = ?";
            $params[] = $nhaCungCapId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật trạng thái dịch vụ (xác nhận booking)
    public function xacNhanDichVu($dichVuId, $giaTien = null) {
        if ($giaTien !== null) {
            $sql = "UPDATE phan_bo_dich_vu 
                    SET trang_thai = 'DaXacNhan', 
                        thoi_gian_xac_nhan = NOW(),
                        gia_tien = ?
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$giaTien, $dichVuId]);
        } else {
            $sql = "UPDATE phan_bo_dich_vu 
                    SET trang_thai = 'DaXacNhan', 
                        thoi_gian_xac_nhan = NOW()
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$dichVuId]);
        }
        
        return $stmt->rowCount() > 0;
    }

    // Từ chối dịch vụ
    public function tuChoiDichVu($dichVuId, $ghiChu = null) {
        if ($ghiChu) {
            $sql = "UPDATE phan_bo_dich_vu 
                    SET trang_thai = 'TuChoi', 
                        thoi_gian_xac_nhan = NOW(),
                        ghi_chu = ?
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$ghiChu, $dichVuId]);
        } else {
            $sql = "UPDATE phan_bo_dich_vu 
                    SET trang_thai = 'TuChoi', 
                        thoi_gian_xac_nhan = NOW()
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$dichVuId]);
        }
        
        return $stmt->rowCount() > 0;
    }

    // Cập nhật giá dịch vụ
    public function capNhatGiaDichVu($dichVuId, $giaTien) {
        $sql = "UPDATE phan_bo_dich_vu SET gia_tien = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$giaTien, $dichVuId]);
        return $stmt->rowCount() > 0;
    }

    // Tính tổng công nợ
    public function getTongCongNo($nhaCungCapId) {
        $sql = "SELECT 
                    SUM(CASE WHEN trang_thai = 'DaXacNhan' THEN gia_tien ELSE 0 END) as tong_cong_no,
                    COUNT(CASE WHEN trang_thai = 'DaXacNhan' THEN 1 END) as so_dich_vu
                FROM phan_bo_dich_vu
                WHERE nha_cung_cap_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhaCungCapId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lịch sử hợp tác
    public function getLichSuHopTac($nhaCungCapId, $limit = 50) {
        $sql = "SELECT pbdv.*, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc,
                t.ten_tour, t.tour_id,
                COUNT(DISTINCT b.booking_id) as so_booking
                FROM phan_bo_dich_vu pbdv
                LEFT JOIN lich_khoi_hanh lkh ON pbdv.lich_khoi_hanh_id = lkh.id
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                LEFT JOIN booking b ON b.tour_id = t.tour_id
                WHERE pbdv.nha_cung_cap_id = ?
                GROUP BY pbdv.id
                ORDER BY pbdv.created_at DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhaCungCapId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thêm nhà cung cấp mới
    public function create($data) {
        // Cho phép gắn với tài khoản người dùng (nguoi_dung_id) nếu có
        if (!empty($data['nguoi_dung_id'])) {
            $sql = "INSERT INTO nha_cung_cap (nguoi_dung_id, ten_don_vi, loai_dich_vu, dia_chi, lien_he, mo_ta) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $params = [
                (int)$data['nguoi_dung_id'],
                $data['ten_don_vi'],
                $data['loai_dich_vu'] ?? null,
                $data['dia_chi'] ?? null,
                $data['lien_he'] ?? null,
                $data['mo_ta'] ?? null
            ];
        } else {
            $sql = "INSERT INTO nha_cung_cap (ten_don_vi, loai_dich_vu, dia_chi, lien_he, mo_ta) 
                    VALUES (?, ?, ?, ?, ?)";
            $params = [
                $data['ten_don_vi'],
                $data['loai_dich_vu'] ?? null,
                $data['dia_chi'] ?? null,
                $data['lien_he'] ?? null,
                $data['mo_ta'] ?? null
            ];
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $this->conn->lastInsertId();
    }

    // Cập nhật nhà cung cấp
    public function update($id, $data) {
        $sql = "UPDATE nha_cung_cap 
                SET ten_don_vi = ?, loai_dich_vu = ?, dia_chi = ?, lien_he = ?, mo_ta = ?
                WHERE id_nha_cung_cap = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ten_don_vi'],
            $data['loai_dich_vu'] ?? null,
            $data['dia_chi'] ?? null,
            $data['lien_he'] ?? null,
            $data['mo_ta'] ?? null,
            $id
        ]);
    }

    // Xóa nhà cung cấp
    public function delete($id) {
        $sql = "DELETE FROM nha_cung_cap WHERE id_nha_cung_cap = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Tổng quan dịch vụ đã cung cấp theo loại
    public function getServiceTypeSummary($nhaCungCapId) {
        $sql = "SELECT 
                    loai_dich_vu,
                    COUNT(*) AS so_lan_cung_cap,
                    SUM(IFNULL(gia_tien, 0)) AS tong_doanh_thu,
                    SUM(CASE WHEN trang_thai = 'DaXacNhan' THEN 1 ELSE 0 END) AS so_da_xac_nhan,
                    MIN(ngay_bat_dau) AS lan_dau,
                    MAX(ngay_ket_thuc) AS lan_gan_nhat
                FROM phan_bo_dich_vu
                WHERE nha_cung_cap_id = ?
                GROUP BY loai_dich_vu
                ORDER BY so_lan_cung_cap DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhaCungCapId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Thống kê tổng hợp cho nhà cung cấp
    public function getSupplierStats($nhaCungCapId) {
        $sql = "SELECT 
                    COUNT(*) AS tong_dich_vu,
                    SUM(CASE WHEN trang_thai = 'DaXacNhan' THEN 1 ELSE 0 END) AS da_xac_nhan,
                    SUM(CASE WHEN trang_thai = 'ChoXacNhan' THEN 1 ELSE 0 END) AS cho_xac_nhan,
                    SUM(CASE WHEN trang_thai = 'TuChoi' THEN 1 ELSE 0 END) AS tu_choi,
                    SUM(IFNULL(gia_tien, 0)) AS tong_gia_tri,
                    MIN(created_at) AS hop_tac_tu,
                    MAX(updated_at) AS moi_nhat
                FROM phan_bo_dich_vu
                WHERE nha_cung_cap_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhaCungCapId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Danh sách dịch vụ chi tiết theo tour
    public function getSupplierServices($nhaCungCapId, $loaiDichVu = null, $limit = 50) {
        $sql = "SELECT 
                    pbdv.*,
                    t.ten_tour,
                    lkh.ngay_khoi_hanh,
                    lkh.ngay_ket_thuc,
                    lkh.id AS lich_khoi_hanh_id
                FROM phan_bo_dich_vu pbdv
                LEFT JOIN lich_khoi_hanh lkh ON pbdv.lich_khoi_hanh_id = lkh.id
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                WHERE pbdv.nha_cung_cap_id = ?";

        $params = [$nhaCungCapId];

        if ($loaiDichVu) {
            $sql .= " AND pbdv.loai_dich_vu = ?";
            $params[] = $loaiDichVu;
        }

        $sql .= " ORDER BY lkh.ngay_khoi_hanh DESC, pbdv.created_at DESC LIMIT ?";
        $params[] = (int)$limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách loại dịch vụ đã từng cung cấp
    public function getDistinctServiceTypes($nhaCungCapId) {
        $sql = "SELECT DISTINCT loai_dich_vu 
                FROM phan_bo_dich_vu 
                WHERE nha_cung_cap_id = ?
                ORDER BY loai_dich_vu";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhaCungCapId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Thống kê báo giá theo trạng thái
    public function getBaoGiaStats($nhaCungCapId) {
        $sql = "SELECT 
                    SUM(CASE WHEN trang_thai = 'ChoXacNhan' THEN 1 ELSE 0 END) AS cho_xac_nhan,
                    SUM(CASE WHEN trang_thai = 'DaXacNhan' THEN 1 ELSE 0 END) AS da_xac_nhan,
                    SUM(CASE WHEN trang_thai = 'TuChoi' THEN 1 ELSE 0 END) AS tu_choi,
                    SUM(CASE WHEN trang_thai = 'HoanTat' THEN 1 ELSE 0 END) AS hoan_tat,
                    COUNT(*) AS tong
                FROM phan_bo_dich_vu
                WHERE nha_cung_cap_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhaCungCapId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Danh sách lịch khởi hành sắp tới để gửi báo giá thủ công
    public function getUpcomingLichKhoiHanh($limit = 20) {
        $sql = "SELECT lkh.id, t.ten_tour, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc,
                       lkh.diem_tap_trung, lkh.so_cho, lkh.trang_thai
                FROM lich_khoi_hanh lkh
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                WHERE lkh.ngay_khoi_hanh >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)
                ORDER BY lkh.ngay_khoi_hanh ASC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
