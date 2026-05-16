<?php
class ChiPhiThucTe
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }
    
    // Lấy tất cả chi phí
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT cp.*, 
                    t.ten_tour,
                    nd_ghi.ho_ten as nguoi_ghi_nhan,
                    nd_duyet.ho_ten as nguoi_duyet
                FROM chi_phi_thuc_te cp
                JOIN tour t ON cp.tour_id = t.tour_id
                JOIN nguoi_dung nd_ghi ON cp.nguoi_ghi_nhan_id = nd_ghi.id
                LEFT JOIN nguoi_dung nd_duyet ON cp.nguoi_duyet_id = nd_duyet.id
                ORDER BY cp.ngay_phat_sinh DESC, cp.created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
        }
        $stmt = $this->conn->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(2, max(0, (int)$offset), PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }
    
    // Lấy chi phí theo ID
    public function findById($id) {
        $sql = "SELECT cp.*, 
                    t.ten_tour,
                    nd_ghi.ho_ten as nguoi_ghi_nhan,
                    nd_duyet.ho_ten as nguoi_duyet
                FROM chi_phi_thuc_te cp
                JOIN tour t ON cp.tour_id = t.tour_id
                JOIN nguoi_dung nd_ghi ON cp.nguoi_ghi_nhan_id = nd_ghi.id
                LEFT JOIN nguoi_dung nd_duyet ON cp.nguoi_duyet_id = nd_duyet.id
                WHERE cp.chi_phi_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Lấy chi phí theo dự toán
    public function getByDuToan($duToanId) {
        $sql = "SELECT cp.*, 
                    nd_ghi.ho_ten as nguoi_ghi_nhan,
                    nd_duyet.ho_ten as nguoi_duyet
                FROM chi_phi_thuc_te cp
                JOIN nguoi_dung nd_ghi ON cp.nguoi_ghi_nhan_id = nd_ghi.id
                LEFT JOIN nguoi_dung nd_duyet ON cp.nguoi_duyet_id = nd_duyet.id
                WHERE cp.du_toan_id = ?
                ORDER BY cp.ngay_phat_sinh DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$duToanId]);
        return $stmt->fetchAll();
    }
    
    // Lấy chi phí theo tour
    public function getByTour($tourId) {
        $sql = "SELECT cp.*, 
                    nd_ghi.ho_ten as nguoi_ghi_nhan,
                    nd_duyet.ho_ten as nguoi_duyet
                FROM chi_phi_thuc_te cp
                JOIN nguoi_dung nd_ghi ON cp.nguoi_ghi_nhan_id = nd_ghi.id
                LEFT JOIN nguoi_dung nd_duyet ON cp.nguoi_duyet_id = nd_duyet.id
                WHERE cp.tour_id = ?
                ORDER BY cp.ngay_phat_sinh DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tourId]);
        return $stmt->fetchAll();
    }
    
    // Lấy chi phí chờ duyệt
    public function getChoDuyet() {
        $sql = "SELECT cp.*, 
                    t.ten_tour,
                    lkh.ngay_khoi_hanh,
                    nd_ghi.ho_ten as nguoi_ghi_nhan
                FROM chi_phi_thuc_te cp
                JOIN tour t ON cp.tour_id = t.tour_id
                LEFT JOIN lich_khoi_hanh lkh ON cp.lich_khoi_hanh_id = lkh.lich_id
                JOIN nguoi_dung nd_ghi ON cp.nguoi_ghi_nhan_id = nd_ghi.id
                WHERE cp.trang_thai = 'ChoXacNhan'
                ORDER BY cp.created_at ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Thêm chi phí mới
    public function create($data) {
        $sql = "INSERT INTO chi_phi_thuc_te (
                    du_toan_id, tour_id, lich_khoi_hanh_id,
                    loai_chi_phi, ten_khoan_chi, so_tien,
                    ngay_phat_sinh, mo_ta, chung_tu,
                    nguoi_ghi_nhan_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['du_toan_id'],
            $data['tour_id'],
            $data['lich_khoi_hanh_id'] ?? null,
            $data['loai_chi_phi'],
            $data['ten_khoan_chi'],
            $data['so_tien'],
            $data['ngay_phat_sinh'],
            $data['mo_ta'] ?? '',
            $data['chung_tu'] ?? null,
            $data['nguoi_ghi_nhan_id']
        ]);
    }
    
    // Cập nhật chi phí
    public function update($id, $data) {
        $sql = "UPDATE chi_phi_thuc_te SET
                    loai_chi_phi = ?,
                    ten_khoan_chi = ?,
                    so_tien = ?,
                    ngay_phat_sinh = ?,
                    mo_ta = ?,
                    chung_tu = ?
                WHERE chi_phi_id = ? AND trang_thai = 'ChoXacNhan'";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['loai_chi_phi'],
            $data['ten_khoan_chi'],
            $data['so_tien'],
            $data['ngay_phat_sinh'],
            $data['mo_ta'] ?? '',
            $data['chung_tu'] ?? null,
            $id
        ]);
    }
    
    // Duyệt chi phí
    public function approve($id, $nguoiDuyetId) {
        $sql = "UPDATE chi_phi_thuc_te SET
                    trang_thai = 'DaDuyet',
                    nguoi_duyet_id = ?,
                    ngay_duyet = NOW()
                WHERE chi_phi_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nguoiDuyetId, $id]);
    }
    
    // Từ chối chi phí
    public function reject($id, $nguoiDuyetId, $lyDo) {
        $sql = "UPDATE chi_phi_thuc_te SET
                    trang_thai = 'TuChoi',
                    nguoi_duyet_id = ?,
                    ngay_duyet = NOW(),
                    ly_do_tu_choi = ?
                WHERE chi_phi_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nguoiDuyetId, $lyDo, $id]);
    }
    
    // Xóa chi phí (chỉ được xóa nếu chưa duyệt)
    public function delete($id) {
        $hasDeletedAt = false;
        try {
            $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'chi_phi_thuc_te' AND COLUMN_NAME = 'deleted_at'");
            $stmtCheck->execute();
            $hasDeletedAt = ((int)$stmtCheck->fetchColumn() > 0);
        } catch (Throwable $e) {
            $hasDeletedAt = false;
        }
        if ($hasDeletedAt) {
            $sql = "UPDATE chi_phi_thuc_te
                SET deleted_at = NOW(), trang_thai = 'DaXoa'
                WHERE chi_phi_id = ? AND trang_thai = 'ChoXacNhan' AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM chi_phi_thuc_te WHERE chi_phi_id = ? AND trang_thai = 'ChoXacNhan'";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Tính tổng chi phí thực tế đã duyệt theo dự toán
    public function getTongThucTeByDuToan($duToanId) {
        $sql = "SELECT COALESCE(SUM(so_tien), 0) as tong
                FROM chi_phi_thuc_te
                WHERE du_toan_id = ? AND trang_thai = 'DaDuyet'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$duToanId]);
        $result = $stmt->fetch();
        return $result['tong'];
    }
    
    // Tính tổng chi phí theo loại
    public function getTongTheoLoai($duToanId, $loaiChiPhi) {
        $sql = "SELECT COALESCE(SUM(so_tien), 0) as tong
                FROM chi_phi_thuc_te
                WHERE du_toan_id = ? AND loai_chi_phi = ? AND trang_thai = 'DaDuyet'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$duToanId, $loaiChiPhi]);
        $result = $stmt->fetch();
        return $result['tong'];
    }
    
    // Kiểm tra cảnh báo vượt ngưỡng
    public function kiemTraCanhBao($duToanId) {
        $sql = "SELECT 
                    dt.tong_du_toan,
                    COALESCE(SUM(cp.so_tien), 0) as tong_thuc_te,
                    CASE 
                        WHEN COALESCE(SUM(cp.so_tien), 0) > dt.tong_du_toan THEN 'VuotDuToan'
                        WHEN COALESCE(SUM(cp.so_tien), 0) > (dt.tong_du_toan * 0.9) THEN 'GanVuot'
                        ELSE 'AnToan'
                    END as canh_bao
                FROM du_toan_tour dt
                LEFT JOIN chi_phi_thuc_te cp ON dt.du_toan_id = cp.du_toan_id 
                    AND cp.trang_thai = 'DaDuyet'
                WHERE dt.du_toan_id = ?
                GROUP BY dt.du_toan_id, dt.tong_du_toan";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$duToanId]);
        return $stmt->fetch();
    }
    
    // Tìm kiếm chi phí
    public function search($filters) {
        $sql = "SELECT cp.*, 
                    t.ten_tour,
                    nd_ghi.ho_ten as nguoi_ghi_nhan,
                    nd_duyet.ho_ten as nguoi_duyet
                FROM chi_phi_thuc_te cp
                JOIN tour t ON cp.tour_id = t.tour_id
                JOIN nguoi_dung nd_ghi ON cp.nguoi_ghi_nhan_id = nd_ghi.id
                LEFT JOIN nguoi_dung nd_duyet ON cp.nguoi_duyet_id = nd_duyet.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['tour_id'])) {
            $sql .= " AND cp.tour_id = ?";
            $params[] = $filters['tour_id'];
        }
        
        if (!empty($filters['loai_chi_phi'])) {
            $sql .= " AND cp.loai_chi_phi = ?";
            $params[] = $filters['loai_chi_phi'];
        }
        
        if (!empty($filters['trang_thai'])) {
            $sql .= " AND cp.trang_thai = ?";
            $params[] = $filters['trang_thai'];
        }
        
        if (!empty($filters['tu_ngay'])) {
            $sql .= " AND cp.ngay_phat_sinh >= ?";
            $params[] = $filters['tu_ngay'];
        }
        
        if (!empty($filters['den_ngay'])) {
            $sql .= " AND cp.ngay_phat_sinh <= ?";
            $params[] = $filters['den_ngay'];
        }
        
        if (!empty($filters['keyword'])) {
            $sql .= " AND (cp.ten_khoan_chi LIKE ? OR cp.mo_ta LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        $sql .= " ORDER BY cp.ngay_phat_sinh DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
