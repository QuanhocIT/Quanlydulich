<?php
// Model cho NguoiDung - tương tác với cơ sở dữ liệu
class NguoiDung 
{
    // Bỏ thuộc tính private $db; để tránh nhầm lẫn
    public PDO $conn; // Thuộc tính kết nối duy nhất, được khởi tạo trong __construct()
    private static array $tableColumnsCache = [];
    private ?bool $hasIsDeletedColumn = null;
    private ?bool $hasDeletedAtColumn = null;
    
    // Yêu cầu: Đảm bảo rằng hàm connectDB() trả về một đối tượng PDO đã kết nối thành công.
    public function __construct()
    {
        // Giả định hàm connectDB() đã được định nghĩa và có thể gọi được.
        $this->conn = connectDB();
    }

    private function getTableColumns(string $tableName): array
    {
        if (!array_key_exists($tableName, self::$tableColumnsCache)) {
            $sql = "SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?
                    ORDER BY ORDINAL_POSITION";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tableName]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = [];
            foreach ($rows as $row) {
                $name = (string)($row['COLUMN_NAME'] ?? '');
                if ($name !== '') {
                    $columns[] = $name;
                }
            }

            self::$tableColumnsCache[$tableName] = $columns;
        }

        return self::$tableColumnsCache[$tableName];
    }

    private function userSelectColumns(string $alias = ''): string
    {
        $columns = $this->getTableColumns('nguoi_dung');
        if (empty($columns)) {
            return $alias !== '' ? ($alias . '.id') : 'id';
        }

        if ($alias === '') {
            return implode(', ', $columns);
        }

        $prefixed = array_map(static function ($column) use ($alias) {
            return $alias . '.' . $column;
        }, $columns);

        return implode(', ', $prefixed);
    }

    private function supportsIsDeleted(): bool
    {
        if ($this->hasIsDeletedColumn !== null) {
            return $this->hasIsDeletedColumn;
        }

        try {
            $sql = "SELECT COUNT(*)
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'nguoi_dung'
                      AND COLUMN_NAME = 'is_deleted'";
            $stmt = $this->conn->query($sql);
            $this->hasIsDeletedColumn = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            $this->hasIsDeletedColumn = false;
        }

        return $this->hasIsDeletedColumn;
    }

    private function notDeletedClause(): string
    {
        return $this->supportsIsDeleted() ? 'is_deleted = 0' : '1=1';
    }

    private function supportsDeletedAt(): bool
    {
        if ($this->hasDeletedAtColumn !== null) {
            return $this->hasDeletedAtColumn;
        }

        try {
            $sql = "SELECT COUNT(*)
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = 'nguoi_dung'
                      AND COLUMN_NAME = 'deleted_at'";
            $stmt = $this->conn->query($sql);
            $this->hasDeletedAtColumn = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            $this->hasDeletedAtColumn = false;
        }

        return $this->hasDeletedAtColumn;
    }

    // Lấy tất cả người dùng
    public function getAll(): array {
        $sql = "SELECT " . $this->userSelectColumns() . " FROM nguoi_dung WHERE " . $this->notDeletedClause() . " ORDER BY ngay_tao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy người dùng theo ID
    public function findById(int $id): mixed {
        $sql = "SELECT " . $this->userSelectColumns() . " FROM nguoi_dung WHERE id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Tìm người dùng theo email
    public function findByEmail(string $email): mixed {
        $sql = "SELECT " . $this->userSelectColumns() . " FROM nguoi_dung WHERE email = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Tìm người dùng theo số điện thoại
    public function findByPhone(string $soDienThoai): mixed {
        $sql = "SELECT " . $this->userSelectColumns() . " FROM nguoi_dung WHERE so_dien_thoai = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$soDienThoai]);
        return $stmt->fetch();
    }

    // Tìm hoặc tạo người dùng mới (cho nhân viên đặt tour)
    public function findOrCreate(string $hoTen, ?string $email, ?string $soDienThoai, string $vaiTro = 'KhachHang'): mixed {
        // Tìm theo email trước (nếu có)
        if (!empty($email)) {
            $nguoiDung = $this->findByEmail($email);
            if ($nguoiDung) {
                return $nguoiDung;
            }
        }
        
        // Tìm theo số điện thoại (nếu có)
        if (!empty($soDienThoai)) {
            $nguoiDung = $this->findByPhone($soDienThoai);
            if ($nguoiDung) {
                return $nguoiDung;
            }
        }
        
        // Tạo mới nếu chưa có
        $tenDangNhap = !empty($email) ? $email : (!empty($soDienThoai) ? 'user_' . $soDienThoai : 'user_' . time());
        $matKhauTam = generateTemporaryPassword();
        $matKhau = password_hash($matKhauTam, PASSWORD_DEFAULT);
        
        $nguoiDungId = $this->insert([
            'ten_dang_nhap' => $tenDangNhap,
            'ho_ten' => $hoTen,
            'email' => $email ?? '',
            'so_dien_thoai' => $soDienThoai ?? '',
            'mat_khau' => $matKhau,
            'vai_tro' => $vaiTro
        ]);
        
        return $this->findById($nguoiDungId);
    }

    /**
     * Lấy danh sách người dùng có kèm tìm kiếm và lọc.
     * Đây là hàm được sửa để sử dụng $this->conn thay vì $this->db
     */
    public function getFilteredUsers(string $search = '', string $role = '', string $status = ''): array {
        // Kiểm tra kết nối trước khi sử dụng
        if ($this->conn === null) {
             // Có thể ném ngoại lệ hoặc trả về mảng rỗng nếu kết nối thất bại
             return []; 
        }

        $sql = "SELECT " . $this->userSelectColumns() . " FROM nguoi_dung WHERE " . $this->notDeletedClause();
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (ten_dang_nhap LIKE :search OR ho_ten LIKE :search OR email LIKE :search OR so_dien_thoai LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if (!empty($role)) {
            $sql .= " AND vai_tro = :role";
            $params[':role'] = $role;
        }
        if (!empty($status)) {
            $sql .= " AND trang_thai = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY id DESC";

        try {
            // SỬA LỖI: Sử dụng $this->conn thay vì $this->db
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Xử lý lỗi DB tại đây (nên dùng error_log)
            return []; 
        }
    }

    public function getUserStats(string $search = '', string $role = '', string $status = ''): array {
        if ($this->conn === null) {
            return [
                'total' => 0,
                'active' => 0,
                'locked' => 0,
                'roles' => [
                    'Admin' => 0,
                    'HDV' => 0,
                    'KhachHang' => 0,
                    'NhaCungCap' => 0,
                ],
            ];
        }

        $sql = "SELECT vai_tro, trang_thai, COUNT(*) AS total FROM nguoi_dung WHERE " . $this->notDeletedClause();
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (ten_dang_nhap LIKE :search OR ho_ten LIKE :search OR email LIKE :search OR so_dien_thoai LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if (!empty($role)) {
            $sql .= " AND vai_tro = :role";
            $params[':role'] = $role;
        }
        if (!empty($status)) {
            $sql .= " AND trang_thai = :status";
            $params[':status'] = $status;
        }

        $sql .= " GROUP BY vai_tro, trang_thai";

        $stats = [
            'total' => 0,
            'active' => 0,
            'locked' => 0,
            'roles' => [
                'Admin' => 0,
                'HDV' => 0,
                'KhachHang' => 0,
                'NhaCungCap' => 0,
            ],
        ];

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $count = (int)($row['total'] ?? 0);
                $vaiTro = $row['vai_tro'] ?? '';
                $trangThai = $row['trang_thai'] ?? '';

                $stats['total'] += $count;
                if ($trangThai === 'HoatDong') {
                    $stats['active'] += $count;
                } elseif ($trangThai === 'BiKhoa') {
                    $stats['locked'] += $count;
                }

                if (isset($stats['roles'][$vaiTro])) {
                    $stats['roles'][$vaiTro] += $count;
                }
            }
        } catch (PDOException $e) {
            return $stats;
        }

        return $stats;
    }

    // Tìm người dùng theo điều kiện
    public function find(array $conditions = []): mixed {
        $sql = "SELECT " . $this->userSelectColumns() . " FROM nguoi_dung WHERE " . $this->notDeletedClause();
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " AND " . implode(" AND ", $where);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Thêm người dùng mới
    public function insert(array $data): string|false {
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, ho_ten, email, so_dien_thoai, mat_khau, vai_tro, ngay_tao) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        try {
            $result = $stmt->execute([
                $data['ten_dang_nhap'] ?? ($data['email'] ?? ''),
                $data['ho_ten'] ?? '',
                $data['email'] ?? '',
                $data['so_dien_thoai'] ?? '',
                $data['mat_khau'] ?? '',
                $data['vai_tro'] ?? 'KhachHang',
                $data['ngay_tao'] ?? date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            // Thêm logging hoặc xử lý lỗi chi tiết hơn nếu cần
            return false;
        }

        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật người dùng
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];
        
        if (isset($data['ho_ten'])) {
            $fields[] = "ho_ten = ?";
            $params[] = $data['ho_ten'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        if (isset($data['so_dien_thoai'])) {
            $fields[] = "so_dien_thoai = ?";
            $params[] = $data['so_dien_thoai'];
        }
        if (isset($data['vai_tro'])) {
            $fields[] = "vai_tro = ?";
            $params[] = $data['vai_tro'];
        }
        if (isset($data['mat_khau'])) {
            $fields[] = "mat_khau = ?";
            $params[] = $data['mat_khau'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE nguoi_dung SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // Cập nhật mật khẩu (hash)
    public function updatePassword(int $id, string $hashedPassword): bool {
        $sql = "UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function updateStatus(int $id, string $status): bool {
        $allowedStatus = ['HoatDong', 'BiKhoa'];
        if (!in_array($status, $allowedStatus, true)) {
            return false;
        }
        $sql = "UPDATE nguoi_dung SET trang_thai = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // Xóa người dùng
    public function delete(int $id): bool {
        if ($this->supportsIsDeleted()) {
            $sql = "UPDATE nguoi_dung
                    SET is_deleted = 1";
            if ($this->supportsDeletedAt()) {
                $sql .= ",
                        deleted_at = NOW()";
            }
            $sql .= ",
                        trang_thai = 'BiKhoa'
                    WHERE id = ? AND is_deleted = 0";
        } else {
            $sql = "UPDATE nguoi_dung SET trang_thai = 'BiKhoa' WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}