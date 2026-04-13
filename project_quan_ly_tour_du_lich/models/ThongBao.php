<?php
// Model cho ThongBao - Quản lý thông báo hệ thống
class ThongBao 
{
    public $conn;

    /**
     * Điều kiện SQL nhận diện khiếu nại chuyển khoản sai/thiếu nội dung.
     */
    private function paymentComplaintWhereClause(): string
    {
        return "(
                    tb.tieu_de LIKE 'Khieu nai chuyen khoan sai noi dung%'
                    OR tb.tieu_de LIKE 'Khiếu nại chuyển khoản sai nội dung%'
                    OR tb.noi_dung LIKE '[KHIEU NAI CHUYEN KHOAN SAI NOI DUNG]%'
                )";
    }
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    /**
     * Tạo thông báo mới
     */
    public function insert($data) {
        $sql = "INSERT INTO thong_bao (
                    tieu_de, noi_dung, loai_thong_bao, muc_do_uu_tien,
                    nguoi_gui_id, nguoi_nhan_id, vai_tro_nhan,
                    trang_thai, thoi_gian_gui, thoi_gian_hen_gui
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['tieu_de'] ?? '',
            $data['noi_dung'] ?? '',
            $data['loai_thong_bao'] ?? 'ChungChung',
            $data['muc_do_uu_tien'] ?? 'TrungBinh',
            $data['nguoi_gui_id'] ?? null,
            $data['nguoi_nhan_id'] ?? null,
            $data['vai_tro_nhan'] ?? null,
            $data['trang_thai'] ?? 'ChuaGui',
            $data['thoi_gian_gui'] ?? null,
            $data['thoi_gian_hen_gui'] ?? null
        ]);
        
        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Cập nhật thông báo
     */
    public function update($id, $data) {
        $sql = "UPDATE thong_bao SET 
                tieu_de = ?, noi_dung = ?, loai_thong_bao = ?, muc_do_uu_tien = ?,
                nguoi_nhan_id = ?, vai_tro_nhan = ?, trang_thai = ?,
                thoi_gian_gui = ?, thoi_gian_hen_gui = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tieu_de'] ?? '',
            $data['noi_dung'] ?? '',
            $data['loai_thong_bao'] ?? 'ChungChung',
            $data['muc_do_uu_tien'] ?? 'TrungBinh',
            $data['nguoi_nhan_id'] ?? null,
            $data['vai_tro_nhan'] ?? null,
            $data['trang_thai'] ?? 'ChuaGui',
            $data['thoi_gian_gui'] ?? null,
            $data['thoi_gian_hen_gui'] ?? null,
            (int)$id
        ]);
    }

    /**
     * Lấy thông báo theo ID
     */
    public function findById($id) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten,
                nd_nhan.ho_ten as nguoi_nhan_ten
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                LEFT JOIN nguoi_dung nd_nhan ON tb.nguoi_nhan_id = nd_nhan.id
                WHERE tb.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    /**
     * Lấy tất cả thông báo
     */
    public function getAll($limit = 100) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                ORDER BY tb.created_at DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông báo theo loại
     */
    public function getByLoai($loaiThongBao) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                WHERE tb.loai_thong_bao = ?
                ORDER BY tb.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$loaiThongBao]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông báo theo vai trò nhận
     */
    public function getByVaiTro($vaiTro, $limit = 50) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                WHERE tb.vai_tro_nhan = ? OR tb.vai_tro_nhan IS NULL
                ORDER BY tb.created_at DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$vaiTro, (int)$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông báo của người dùng cụ thể
     */
    public function getByNguoiDung($nguoiDungId, $limit = 50) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten,
                tbd.da_doc, tbd.thoi_gian_doc
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                LEFT JOIN thong_bao_doc tbd ON tb.id = tbd.thong_bao_id AND tbd.nguoi_dung_id = ?
            WHERE tb.nguoi_nhan_id = ?
              AND tb.vai_tro_nhan = 'KhachHang'
              AND tb.trang_thai = 'DaGui'
              AND tb.nguoi_gui_id IS NOT NULL
              AND nd_gui.vai_tro = 'Admin'
                ORDER BY tb.created_at DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$nguoiDungId, (int)$nguoiDungId, (int)$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Đếm thông báo chưa đọc của người dùng
     */
    public function countChuaDoc($nguoiDungId) {
        $sql = "SELECT COUNT(*) as total
                FROM thong_bao tb
                                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                LEFT JOIN thong_bao_doc tbd ON tb.id = tbd.thong_bao_id AND tbd.nguoi_dung_id = ?
                                WHERE tb.nguoi_nhan_id = ?
                                    AND tb.vai_tro_nhan = 'KhachHang'
                                    AND tb.nguoi_gui_id IS NOT NULL
                                    AND nd_gui.vai_tro = 'Admin'
                  AND (tbd.da_doc IS NULL OR tbd.da_doc = 0)
                  AND tb.trang_thai = 'DaGui'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$nguoiDungId, (int)$nguoiDungId]);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Đánh dấu đã đọc
     */
    public function danhDauDaDoc($thongBaoId, $nguoiDungId) {
        $sql = "INSERT INTO thong_bao_doc (thong_bao_id, nguoi_dung_id, da_doc, thoi_gian_doc)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE da_doc = 1, thoi_gian_doc = NOW()";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$thongBaoId, (int)$nguoiDungId]);
    }

    /**
     * Cập nhật trạng thái thông báo
     */
    public function updateTrangThai($id, $trangThai) {
        $sql = "UPDATE thong_bao SET trang_thai = ?";
        
        if ($trangThai === 'DaGui') {
            $sql .= ", thoi_gian_gui = NOW()";
        }
        
        $sql .= " WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$trangThai, (int)$id]);
    }

    /**
     * Xóa thông báo
     */
    public function delete($id) {
        $sql = "DELETE FROM thong_bao WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }

    /**
     * Gửi thông báo đến nhiều người theo vai trò
     */
    public function guiTheoVaiTro($data) {
        // Tạo thông báo
        $thongBaoId = $this->insert($data);
        
        if (!$thongBaoId) {
            return false;
        }
        
        // Cập nhật trạng thái đã gửi
        $this->updateTrangThai($thongBaoId, 'DaGui');
        
        return $thongBaoId;
    }

    /**
     * Lấy thống kê thông báo
     */
    public function getThongKe() {
        $sql = "SELECT 
                COUNT(*) as tong_so,
                SUM(CASE WHEN trang_thai = 'DaGui' THEN 1 ELSE 0 END) as da_gui,
                SUM(CASE WHEN trang_thai = 'ChuaGui' THEN 1 ELSE 0 END) as chua_gui,
                SUM(CASE WHEN trang_thai = 'Loi' THEN 1 ELSE 0 END) as loi,
                SUM(CASE WHEN loai_thong_bao = 'HDV' THEN 1 ELSE 0 END) as hdv,
                SUM(CASE WHEN loai_thong_bao = 'KhachHang' THEN 1 ELSE 0 END) as khach_hang,
                SUM(CASE WHEN loai_thong_bao = 'NhanSu' THEN 1 ELSE 0 END) as nhan_su
                FROM thong_bao";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Lấy tất cả thông báo của khách hàng
     */
    public function getAllByKhachHang($nguoiDungId) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                WHERE tb.nguoi_gui_id = ?
                ORDER BY tb.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$nguoiDungId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy yêu cầu tour từ khách hàng (cho Admin)
     */
    public function getYeuCauTour($filters = []) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten,
                nd_gui.email as nguoi_gui_email,
                nd_gui.so_dien_thoai as nguoi_gui_phone
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
            WHERE tb.tieu_de = 'Yêu cầu tour theo mong muốn'
                  AND tb.vai_tro_nhan = 'Admin'";
        
        $params = [];
        
        // Lọc theo trạng thái
        if (!empty($filters['trang_thai'])) {
            $sql .= " AND tb.trang_thai = ?";
            $params[] = $filters['trang_thai'];
        }
        
        // Tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (nd_gui.ho_ten LIKE ? OR tb.noi_dung LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY tb.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getYeuCauTourByUserIds(array $userIds) {
        $normalizedIds = [];
        foreach ($userIds as $userId) {
            $id = (int)$userId;
            if ($id > 0) {
                $normalizedIds[$id] = $id;
            }
        }

        if (empty($normalizedIds)) {
            return [];
        }

        $idList = array_values($normalizedIds);
        $placeholders = implode(',', array_fill(0, count($idList), '?'));
        $sql = "SELECT tb.*, 
                       nd_gui.ho_ten as nguoi_gui_ten,
                       nd_gui.email as nguoi_gui_email,
                       nd_gui.so_dien_thoai as nguoi_gui_phone
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                INNER JOIN (
                    SELECT nguoi_gui_id, MAX(id) AS latest_id
                    FROM thong_bao
                    WHERE tieu_de = 'YÃªu cáº§u tour theo mong muá»‘n'
                      AND vai_tro_nhan = 'Admin'
                      AND nguoi_gui_id IN ($placeholders)
                    GROUP BY nguoi_gui_id
                ) latest_tb ON latest_tb.latest_id = tb.id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($idList);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $userId = (int)($row['nguoi_gui_id'] ?? 0);
            if ($userId > 0) {
                $result[$userId] = $row;
            }
        }

        return $result;
    }

    /**
     * Đếm yêu cầu tour chưa xử lý
     */
    public function countYeuCauTourChuaXuLy() {
        $sql = "SELECT COUNT(*) as total
                FROM thong_bao tb
                WHERE tb.tieu_de = 'Yêu cầu tour theo mong muốn'
                  AND tb.vai_tro_nhan = 'Admin'
                  AND tb.trang_thai = 'DaGui'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Lấy danh sách khiếu nại thanh toán từ khách hàng (cho Admin)
     */
    public function getPaymentComplaints($filters = []) {
        $sql = "SELECT tb.*, 
                nd_gui.ho_ten as nguoi_gui_ten,
                nd_gui.email as nguoi_gui_email,
                nd_gui.so_dien_thoai as nguoi_gui_phone
                FROM thong_bao tb
                LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
                                WHERE " . $this->paymentComplaintWhereClause() . "
                  AND tb.vai_tro_nhan = 'Admin'";

        $params = [];

        if (!empty($filters['trang_thai'])) {
            $sql .= " AND tb.trang_thai = ?";
            $params[] = $filters['trang_thai'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (nd_gui.ho_ten LIKE ? OR nd_gui.so_dien_thoai LIKE ? OR tb.noi_dung LIKE ? OR tb.tieu_de LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY tb.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Đếm khiếu nại thanh toán chưa xử lý
     */
    public function countPaymentComplaintsChuaXuLy() {
        $sql = "SELECT COUNT(*) as total
                FROM thong_bao tb
                                WHERE " . $this->paymentComplaintWhereClause() . "
                  AND tb.vai_tro_nhan = 'Admin'
                  AND tb.trang_thai = 'DaGui'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Cập nhật trạng thái yêu cầu tour và gửi phản hồi
     */
    public function updatePhanHoi($yeuCauId, $phanHoi, $adminId, $trangThaiMoi = null) {
        // Lấy thông tin yêu cầu gốc
        $yeuCau = $this->findById($yeuCauId);
        if (!$yeuCau) {
            return false;
        }
        
        // Cập nhật trạng thái yêu cầu (nếu có trạng thái mới)
        // Vì bảng không có cột trạng thái riêng cho "Đã xử lý", ta sẽ giữ nguyên trạng thái "DaGui"
        // hoặc có thể thêm ghi chú vào nội dung
        if ($trangThaiMoi) {
            // Cập nhật nội dung để đánh dấu đã xử lý
            $noiDungMoi = $yeuCau['noi_dung'] . "\n\n--- Đã xử lý bởi Admin ---";
            $this->update($yeuCauId, [
                'tieu_de' => $yeuCau['tieu_de'],
                'noi_dung' => $noiDungMoi,
                'loai_thong_bao' => $yeuCau['loai_thong_bao'],
                'muc_do_uu_tien' => $yeuCau['muc_do_uu_tien'],
                'nguoi_nhan_id' => $yeuCau['nguoi_nhan_id'],
                'vai_tro_nhan' => $yeuCau['vai_tro_nhan'],
                'trang_thai' => $yeuCau['trang_thai']
            ]);
        }
        
        // Gửi thông báo phản hồi cho khách hàng
        if (!empty($phanHoi) && $yeuCau['nguoi_gui_id']) {
            $phanHoiData = [
                'tieu_de' => 'Phản hồi yêu cầu tour của bạn',
                'noi_dung' => $phanHoi,
                'loai_thong_bao' => 'KhachHang',
                'muc_do_uu_tien' => 'TrungBinh',
                'nguoi_gui_id' => $adminId,
                'nguoi_nhan_id' => $yeuCau['nguoi_gui_id'],
                'vai_tro_nhan' => 'KhachHang',
                'trang_thai' => 'DaGui',
                'thoi_gian_gui' => date('Y-m-d H:i:s')
            ];
            return $this->insert($phanHoiData);
        }
        
        return true;
    }
}
