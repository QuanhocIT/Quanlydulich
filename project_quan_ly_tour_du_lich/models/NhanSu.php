<?php 

class NhanSu 
{
    /** @var PDO */
    public PDO $conn;
    private static array $tableColumnsCache = [];
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function hasColumn(string $tableName, string $columnName): bool {
        if (!array_key_exists($tableName, self::$tableColumnsCache)) {
            $sql = "SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tableName]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columnMap = [];
            foreach ($rows as $row) {
                $name = (string)($row['COLUMN_NAME'] ?? '');
                if ($name !== '') {
                    $columnMap[$name] = true;
                }
            }
            self::$tableColumnsCache[$tableName] = $columnMap;
        }

        return isset(self::$tableColumnsCache[$tableName][$columnName]);
    }

    private function nhanSuNotDeletedClause(string $alias = ''): string {
        if (!$this->hasColumn('nhan_su', 'is_deleted')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'is_deleted = 0';
    }

    private function nguoiDungNotDeletedClause(string $alias = ''): string {
        if (!$this->hasColumn('nguoi_dung', 'is_deleted')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'is_deleted = 0';
    }

    private function tourNotDeletedClause(string $alias = ''): string {
        if (!$this->hasColumn('tour', 'is_deleted')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'is_deleted = 0';
    }

    // Lấy tất cả nhân sự (join với người dùng)
    public function getAll(?int $limit = null, int $offset = 0): array {
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.ten_dang_nhap, nd.avatar, nd.ngay_tao, nd.trang_thai as trang_thai_tai_khoan, nd.id as nguoi_dung_id_full
                FROM nhan_su AS ns
                LEFT JOIN nguoi_dung AS nd ON ns.nguoi_dung_id = nd.id
                                WHERE " . $this->nhanSuNotDeletedClause('ns') . "
                                    AND (nd.id IS NULL OR " . $this->nguoiDungNotDeletedClause('nd') . ")
                ORDER BY nd.ho_ten ASC";
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

    public function getOptions(?string $role = null, ?int $limit = null): array {
        $sql = "SELECT ns.nhan_su_id, ns.vai_tro, nd.ho_ten
                FROM nhan_su AS ns
            LEFT JOIN nguoi_dung AS nd ON ns.nguoi_dung_id = nd.id
            WHERE " . $this->nhanSuNotDeletedClause('ns') . "
              AND (nd.id IS NULL OR " . $this->nguoiDungNotDeletedClause('nd') . ")";
        $params = [];

        if ($role !== null && $role !== '') {
            $sql .= " AND ns.vai_tro = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY nd.ho_ten ASC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
        }

        $stmt = $this->conn->prepare($sql);
        $index = 1;
        foreach ($params as $param) {
            $stmt->bindValue($index++, $param);
        }
        if ($limit !== null) {
            $stmt->bindValue($index, (int)$limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy nhân sự theo vai trò
    public function getByRole(string $role): array {
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.ten_dang_nhap, nd.avatar, nd.ngay_tao, nd.trang_thai as trang_thai_tai_khoan, nd.id as nguoi_dung_id_full
                FROM nhan_su AS ns
                LEFT JOIN nguoi_dung AS nd ON ns.nguoi_dung_id = nd.id
                                WHERE ns.vai_tro = ?
                                    AND " . $this->nhanSuNotDeletedClause('ns') . "
                                    AND (nd.id IS NULL OR " . $this->nguoiDungNotDeletedClause('nd') . ")
                ORDER BY nd.ho_ten ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    // Lấy danh sách vai trò có trong hệ thống
    public function getRoles(): array {
        $roles = [];
        try {
            $sql = "SELECT DISTINCT vai_tro AS role FROM nhan_su WHERE vai_tro IS NOT NULL AND vai_tro != '' AND " . $this->nhanSuNotDeletedClause();
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) { $roles[] = $r['role']; }
        } catch (Exception $e) {
            // ignore
        }
        return $roles;
    }

    // Lấy nhân sự theo ID
    public function findById(int|string $id): array|false {
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.ten_dang_nhap, nd.avatar, nd.id as nguoi_dung_id_full
                FROM nhan_su AS ns
                LEFT JOIN nguoi_dung AS nd ON ns.nguoi_dung_id = nd.id
                                WHERE ns.nhan_su_id = ?
                                    AND " . $this->nhanSuNotDeletedClause('ns') . "
                                    AND (nd.id IS NULL OR " . $this->nguoiDungNotDeletedClause('nd') . ")";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Cập nhật lương cơ bản (cần có cột nhan_su.luong_co_ban)
    public function updateLuongCoBan(int|string $nhan_su_id, float|int|string $luongCoBan): bool {
        try {
            // Check column exists (để tránh lỗi khi DB chưa migrate)
            if (!$this->hasColumn('nhan_su', 'luong_co_ban')) {
                return false;
            }

            $sql = "UPDATE nhan_su SET luong_co_ban = ? WHERE nhan_su_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([(float)$luongCoBan, (int)$nhan_su_id]);
            return (int)$stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // Thêm nhân sự (gắn với tài khoản người dùng)
    public function insert(array $data): bool {
        $sql = "INSERT INTO nhan_su (nguoi_dung_id, vai_tro, chung_chi, ngon_ngu, kinh_nghiem, suc_khoe) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['nguoi_dung_id'] ?? null,
            $data['vai_tro'] ?? 'Khac',
            $data['chung_chi'] ?? null,
            $data['ngon_ngu'] ?? null,
            $data['kinh_nghiem'] ?? null,
            $data['suc_khoe'] ?? null,
        ]);
        
        // Cập nhật vai trò trong bảng người dùng (map sang ENUM hợp lệ)
        if ($result && isset($data['nguoi_dung_id'])) {
            $this->updateUserRoleFromStaff($data['nguoi_dung_id'], $data['vai_tro'] ?? 'Khac');
        }
        
        return $result;
    }

    // Cập nhật nhân sự
    public function update(int|string $id, array $data): bool {
        $nhanSu = $this->findById($id);
        if (!$nhanSu) return false;
        
        $sql = "UPDATE nhan_su SET vai_tro = ?, chung_chi = ?, ngon_ngu = ?, kinh_nghiem = ?, suc_khoe = ? WHERE nhan_su_id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['vai_tro'] ?? 'Khac',
            $data['chung_chi'] ?? null,
            $data['ngon_ngu'] ?? null,
            $data['kinh_nghiem'] ?? null,
            $data['suc_khoe'] ?? null,
            $id
        ]);
        
        // Cập nhật vai trò trong bảng người dùng (map sang ENUM hợp lệ)
        if ($result && $nhanSu['nguoi_dung_id']) {
            $this->updateUserRoleFromStaff($nhanSu['nguoi_dung_id'], $data['vai_tro'] ?? 'Khac');
        }
        
        return $result;
    }

    // Xóa nhân sự (chỉ xóa bản ghi, giữ lại tài khoản người dùng)
    public function delete(int|string $id): bool {
        if ($this->hasColumn('nhan_su', 'is_deleted')) {
            $hasDeletedAt = $this->hasColumn('nhan_su', 'deleted_at');
            $sql = "UPDATE nhan_su
                    SET is_deleted = 1";
            if ($hasDeletedAt) {
                $sql .= ", deleted_at = NOW()";
            }
            $sql .= " WHERE nhan_su_id = ? AND is_deleted = 0";
        } else {
            $sql = "DELETE FROM nhan_su WHERE nhan_su_id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Xóa nhân sự và tài khoản người dùng (cascade delete khach_hang và nha_cung_cap)
    public function deleteWithUser(int|string $nhan_su_id): bool {
        $nhanSu = $this->findById($nhan_su_id);
        if (!$nhanSu || !$nhanSu['nguoi_dung_id']) {
            return false;
        }
        
        $nguoi_dung_id = $nhanSu['nguoi_dung_id'];

        // Kiểm tra chỉ các ràng buộc quan trọng (tour.tao_boi)
        $criticalBlockers = $this->getCriticalDeleteBlockers($nguoi_dung_id);
        if (!empty($criticalBlockers)) {
            return false;
        }
        
        // Soft delete bản ghi nhan_su
        if ($this->hasColumn('nhan_su', 'is_deleted')) {
            $sql1 = "UPDATE nhan_su SET is_deleted = 1";
            if ($this->hasColumn('nhan_su', 'deleted_at')) {
                $sql1 .= ", deleted_at = NOW()";
            }
            $sql1 .= " WHERE nhan_su_id = ? AND is_deleted = 0";
        } else {
            $sql1 = "DELETE FROM nhan_su WHERE nhan_su_id = ?";
        }
        $stmt1 = $this->conn->prepare($sql1);
        $result1 = $stmt1->execute([$nhan_su_id]);

        // Soft delete tài khoản người dùng
        if ($this->hasColumn('nguoi_dung', 'is_deleted')) {
            $sql2 = "UPDATE nguoi_dung SET is_deleted = 1";
            if ($this->hasColumn('nguoi_dung', 'deleted_at')) {
                $sql2 .= ", deleted_at = NOW()";
            }
            $sql2 .= ", trang_thai = 'BiKhoa' WHERE id = ? AND is_deleted = 0";
        } else {
            $sql2 = "UPDATE nguoi_dung SET trang_thai = 'BiKhoa' WHERE id = ?";
        }
        $stmt2 = $this->conn->prepare($sql2);
        $result2 = $stmt2->execute([$nguoi_dung_id]);
        
        return $result1 && $result2;
    }

    // Trả về danh sách lý do quan trọng không thể xóa (chỉ tour.tao_boi)
    public function getCriticalDeleteBlockers(int|string $nguoi_dung_id): array {
        $reasons = [];
        // Bị tham chiếu bởi tour (trường tao_boi) - KHÔNG THỂ CASCADE
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS c FROM tour WHERE tao_boi = ? AND " . $this->tourNotDeletedClause());
            $stmt->execute([$nguoi_dung_id]);
            $row = $stmt->fetch();
            if (!empty($row['c']) && (int)$row['c'] > 0) {
                $reasons[] = 'Tài khoản đang là người tạo một hoặc nhiều Tour.';
            }
        } catch (Exception $e) {}
        return $reasons;
    }

    // Trả về danh sách lý do không thể xóa tài khoản người dùng (tất cả các ràng buộc)
    public function getDeleteBlockers(int|string $nguoi_dung_id): array {
        $reasons = [];
        // Bị tham chiếu bởi khach_hang
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS c FROM khach_hang WHERE nguoi_dung_id = ?");
            $stmt->execute([$nguoi_dung_id]);
            $row = $stmt->fetch();
            if (!empty($row['c']) && (int)$row['c'] > 0) {
                $reasons[] = 'Tài khoản đang gắn với bản ghi Khách hàng.';
            }
        } catch (Exception $e) {}

        // Bị tham chiếu bởi nha_cung_cap
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS c FROM nha_cung_cap WHERE nguoi_dung_id = ?");
            $stmt->execute([$nguoi_dung_id]);
            $row = $stmt->fetch();
            if (!empty($row['c']) && (int)$row['c'] > 0) {
                $reasons[] = 'Tài khoản đang gắn với bản ghi Nhà cung cấp.';
            }
        } catch (Exception $e) {}

        // Bị tham chiếu bởi tour (trường tao_boi)
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) AS c FROM tour WHERE tao_boi = ? AND " . $this->tourNotDeletedClause());
            $stmt->execute([$nguoi_dung_id]);
            $row = $stmt->fetch();
            if (!empty($row['c']) && (int)$row['c'] > 0) {
                $reasons[] = 'Tài khoản đang là người tạo một hoặc nhiều Tour.';
            }
        } catch (Exception $e) {}

        return $reasons;
    }

    // Lấy danh sách người dùng chưa có bản ghi nhân sự
    public function getAvailableUsers(): array {
        $sql = "SELECT id, ho_ten, email, ten_dang_nhap, vai_tro 
                FROM nguoi_dung 
                                WHERE id NOT IN (
                                         SELECT DISTINCT nguoi_dung_id
                                         FROM nhan_su
                                         WHERE nguoi_dung_id IS NOT NULL
                                             AND " . $this->nhanSuNotDeletedClause() . "
                                 )
                                     AND " . $this->nguoiDungNotDeletedClause() . "
                ORDER BY ho_ten ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Map vai_tro nhân sự sang vai_tro người dùng hợp lệ theo ENUM('Admin','HDV','KhachHang','NhaCungCap')
    private function mapUserRoleFromStaff(string $staffRole): string {
        $staffRole = (string)$staffRole;
        if (in_array($staffRole, ['HDV','DieuHanh','TaiXe','Khac'], true)) {
            return 'HDV';
        }
        return 'KhachHang';
    }

    // Cập nhật vai trò người dùng dựa trên vai_tro nhân sự (không ghi đè Admin/NhaCungCap)
    private function updateUserRoleFromStaff(int|string $nguoi_dung_id, string $staffRole): bool {
        // Lấy vai trò hiện tại
        $stmt = $this->conn->prepare("SELECT vai_tro FROM nguoi_dung WHERE id = ?");
        $stmt->execute([$nguoi_dung_id]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $current = $row['vai_tro'];
        if (in_array($current, ['Admin','NhaCungCap'], true)) {
            // Không ghi đè các vai trò này
            return true;
        }

        $mapped = $this->mapUserRoleFromStaff($staffRole);
        if ($mapped === $current) return true;

        $sql = "UPDATE nguoi_dung SET vai_tro = ? WHERE id = ?";
        $stmt2 = $this->conn->prepare($sql);
        return $stmt2->execute([$mapped, $nguoi_dung_id]);
    }

    // Tìm kiếm nhân sự
    public function search(string $q): array {
        $keyword = '%' . $q . '%';
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.ten_dang_nhap, nd.avatar, nd.ngay_tao, nd.trang_thai as trang_thai_tai_khoan, nd.id as nguoi_dung_id_full
                FROM nhan_su AS ns
                LEFT JOIN nguoi_dung AS nd ON ns.nguoi_dung_id = nd.id
                                WHERE (nd.ho_ten LIKE ? OR nd.email LIKE ? OR nd.so_dien_thoai LIKE ? OR ns.vai_tro LIKE ?)
                                     AND " . $this->nhanSuNotDeletedClause('ns') . "
                                     AND (nd.id IS NULL OR " . $this->nguoiDungNotDeletedClause('nd') . ")
                ORDER BY nd.ho_ten ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$keyword, $keyword, $keyword, $keyword]);
        return $stmt->fetchAll();
    }

    // Lấy danh sách Top HDV xuất sắc cho dashboard
    public function getTopHdv(int $limit = 5): array {
        $limit = max(1, (int)$limit);
        $sql = "SELECT ns.nhan_su_id, nd.ho_ten, nd.email, nd.so_dien_thoai,
                       COALESCE(ns.so_tour_da_dan, 0) AS so_tour_da_dan,
                       COALESCE(ns.danh_gia_tb, 0) AS danh_gia_tb,
                       ns.trang_thai_lam_viec
                FROM nhan_su AS ns
                JOIN nguoi_dung AS nd ON ns.nguoi_dung_id = nd.id
                WHERE ns.vai_tro = 'HDV'
                  AND " . $this->nhanSuNotDeletedClause('ns') . "
                  AND " . $this->nguoiDungNotDeletedClause('nd') . "
                ORDER BY ns.danh_gia_tb DESC, ns.so_tour_da_dan DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

?>
