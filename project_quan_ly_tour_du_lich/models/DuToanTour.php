<?php
class DuToanTour
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }
    
    // Lấy tất cả dự toán
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT dt.*, t.ten_tour, nd.ho_ten as nguoi_tao
                FROM du_toan_tour dt
                JOIN tour t ON dt.tour_id = t.tour_id
                LEFT JOIN nguoi_dung nd ON dt.nguoi_tao_id = nd.id
                ORDER BY dt.ngay_tao DESC";
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
    
    // Lấy dự toán theo ID
    public function findById($id) {
        $sql = "SELECT dt.*, t.ten_tour, nd.ho_ten as nguoi_tao
                FROM du_toan_tour dt
                JOIN tour t ON dt.tour_id = t.tour_id
                LEFT JOIN nguoi_dung nd ON dt.nguoi_tao_id = nd.id
                WHERE dt.du_toan_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Lấy dự toán theo tour
    public function getByTour($tourId) {
        $sql = "SELECT dt.*, nd.ho_ten as nguoi_tao
                FROM du_toan_tour dt
                LEFT JOIN nguoi_dung nd ON dt.nguoi_tao_id = nd.id
                WHERE dt.tour_id = ?
                ORDER BY dt.ngay_tao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tourId]);
        return $stmt->fetchAll();
    }
    
    // Lấy dự toán theo lịch khởi hành
    public function getByLichKhoiHanh($lichId) {
        $sql = "SELECT dt.*, t.ten_tour, nd.ho_ten as nguoi_tao
                FROM du_toan_tour dt
                JOIN tour t ON dt.tour_id = t.tour_id
                LEFT JOIN nguoi_dung nd ON dt.nguoi_tao_id = nd.id
                WHERE dt.lich_khoi_hanh_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichId]);
        return $stmt->fetch();
    }
    
    // Tạo dự toán mới
    public function create($data) {
        $sql = "INSERT INTO du_toan_tour (
                    tour_id, lich_khoi_hanh_id,
                    cp_phuong_tien, mo_ta_phuong_tien,
                    cp_luu_tru, mo_ta_luu_tru,
                    cp_ve_tham_quan, mo_ta_ve_tham_quan,
                    cp_an_uong, mo_ta_an_uong,
                    cp_huong_dan_vien,
                    cp_dich_vu_bo_sung, mo_ta_dich_vu,
                    cp_phat_sinh_du_kien, mo_ta_phat_sinh,
                    nguoi_tao_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tour_id'],
            $data['lich_khoi_hanh_id'] ?? null,
            $data['cp_phuong_tien'] ?? 0,
            $data['mo_ta_phuong_tien'] ?? '',
            $data['cp_luu_tru'] ?? 0,
            $data['mo_ta_luu_tru'] ?? '',
            $data['cp_ve_tham_quan'] ?? 0,
            $data['mo_ta_ve_tham_quan'] ?? '',
            $data['cp_an_uong'] ?? 0,
            $data['mo_ta_an_uong'] ?? '',
            $data['cp_huong_dan_vien'] ?? 0,
            $data['cp_dich_vu_bo_sung'] ?? 0,
            $data['mo_ta_dich_vu'] ?? '',
            $data['cp_phat_sinh_du_kien'] ?? 0,
            $data['mo_ta_phat_sinh'] ?? '',
            $data['nguoi_tao_id']
        ]);
    }
    
    // Cập nhật dự toán
    public function update($id, $data) {
        $sql = "UPDATE du_toan_tour SET
                    cp_phuong_tien = ?,
                    mo_ta_phuong_tien = ?,
                    cp_luu_tru = ?,
                    mo_ta_luu_tru = ?,
                    cp_ve_tham_quan = ?,
                    mo_ta_ve_tham_quan = ?,
                    cp_an_uong = ?,
                    mo_ta_an_uong = ?,
                    cp_huong_dan_vien = ?,
                    cp_dich_vu_bo_sung = ?,
                    mo_ta_dich_vu = ?,
                    cp_phat_sinh_du_kien = ?,
                    mo_ta_phat_sinh = ?
                WHERE du_toan_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['cp_phuong_tien'] ?? 0,
            $data['mo_ta_phuong_tien'] ?? '',
            $data['cp_luu_tru'] ?? 0,
            $data['mo_ta_luu_tru'] ?? '',
            $data['cp_ve_tham_quan'] ?? 0,
            $data['mo_ta_ve_tham_quan'] ?? '',
            $data['cp_an_uong'] ?? 0,
            $data['mo_ta_an_uong'] ?? '',
            $data['cp_huong_dan_vien'] ?? 0,
            $data['cp_dich_vu_bo_sung'] ?? 0,
            $data['mo_ta_dich_vu'] ?? '',
            $data['cp_phat_sinh_du_kien'] ?? 0,
            $data['mo_ta_phat_sinh'] ?? '',
            $id
        ]);
    }
    
    // Xóa dự toán
    public function delete($id) {
        $hasDeletedAt = false;
        try {
            $stmtCheck = $this->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'du_toan_tour' AND COLUMN_NAME = 'deleted_at'");
            $stmtCheck->execute();
            $hasDeletedAt = ((int)$stmtCheck->fetchColumn() > 0);
        } catch (Throwable $e) {
            $hasDeletedAt = false;
        }
        if ($hasDeletedAt) {
            $sql = "UPDATE du_toan_tour SET deleted_at = NOW() WHERE du_toan_id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM du_toan_tour WHERE du_toan_id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Sao chép dự toán từ tour khác
    public function copyFromTour($sourceTourId, $targetTourId, $lichKhoiHanhId, $nguoiTaoId) {
        // Lấy dự toán gần nhất của tour nguồn
        $source = $this->getByTour($sourceTourId);
        if (empty($source)) {
            return false;
        }
        $source = $source[0];
        
        // Tạo dự toán mới cho tour đích
        return $this->create([
            'tour_id' => $targetTourId,
            'lich_khoi_hanh_id' => $lichKhoiHanhId,
            'cp_phuong_tien' => $source['cp_phuong_tien'],
            'mo_ta_phuong_tien' => $source['mo_ta_phuong_tien'],
            'cp_luu_tru' => $source['cp_luu_tru'],
            'mo_ta_luu_tru' => $source['mo_ta_luu_tru'],
            'cp_ve_tham_quan' => $source['cp_ve_tham_quan'],
            'mo_ta_ve_tham_quan' => $source['mo_ta_ve_tham_quan'],
            'cp_an_uong' => $source['cp_an_uong'],
            'mo_ta_an_uong' => $source['mo_ta_an_uong'],
            'cp_huong_dan_vien' => $source['cp_huong_dan_vien'],
            'cp_dich_vu_bo_sung' => $source['cp_dich_vu_bo_sung'],
            'mo_ta_dich_vu' => $source['mo_ta_dich_vu'],
            'cp_phat_sinh_du_kien' => $source['cp_phat_sinh_du_kien'],
            'mo_ta_phat_sinh' => $source['mo_ta_phat_sinh'],
            'nguoi_tao_id' => $nguoiTaoId
        ]);
    }
    
    // Lấy danh sách dự toán có cảnh báo
    public function getDuToanCanhBao() {
        // Ép collation cho canh_bao để tránh lỗi mix of collations
        $sql = "SELECT * FROM v_so_sanh_du_toan_thuc_te 
                WHERE CONVERT(canh_bao USING utf8mb4) COLLATE utf8mb4_unicode_ci IN ('VuotDuToan', 'GanVuot')
                ORDER BY 
                    CASE CONVERT(canh_bao USING utf8mb4) COLLATE utf8mb4_unicode_ci
                        WHEN 'VuotDuToan' THEN 1
                        WHEN 'GanVuot' THEN 2
                    END";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
