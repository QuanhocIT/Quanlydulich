<?php
class KhachHang 
{
    public PDO $conn;
    private static array $tableColumnsCache = [];
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function getTableColumns(string $tableName): array {
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

    private function selectColumnsFromTable(string $tableName, string $alias = ''): string {
        $columns = $this->getTableColumns($tableName);
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

    private function khachHangSelectColumns(string $alias = ''): string {
        return $this->selectColumnsFromTable('khach_hang', $alias);
    }

    public function getAll(?int $limit = null, int $offset = 0): array {
        $sql = "SELECT " . $this->khachHangSelectColumns() . " FROM khach_hang ORDER BY khach_hang_id DESC";
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

    public function findById(int $id): mixed {
        $sql = "SELECT " . $this->khachHangSelectColumns() . " FROM khach_hang WHERE khach_hang_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByNguoiDungId(int $nguoiDungId): mixed {
        $sql = "SELECT " . $this->khachHangSelectColumns() . " FROM khach_hang WHERE nguoi_dung_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nguoiDungId]);
        return $stmt->fetch();
    }

    public function findByUserId(int $userId): mixed {
        return $this->findByNguoiDungId($userId);
    }

    // Thống kê khách hàng mới theo tháng (dựa trên nguoi_dung.ngay_tao).
    public function getNewCustomersByMonth(int $months = 12): array {
        $months = max(1, (int)$months);
        $sql = "SELECT DATE_FORMAT(nd.ngay_tao, '%Y-%m') AS thang, COUNT(*) AS total
                FROM khach_hang kh
                INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE nd.ngay_tao IS NOT NULL
                  AND nd.ngay_tao >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(nd.ngay_tao, '%Y-%m')
                ORDER BY DATE_FORMAT(nd.ngay_tao, '%Y-%m') ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$months]);

        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $month = (string)($row['thang'] ?? '');
            if ($month !== '') {
                $result[$month] = (int)($row['total'] ?? 0);
            }
        }

        return $result;
    }

    public function insert(array $data): string|false {
        $sql = "INSERT INTO khach_hang (nguoi_dung_id, dia_chi, gioi_tinh, ngay_sinh) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['nguoi_dung_id'],
            $data['dia_chi'] ?? null,
            $data['gioi_tinh'] ?? null,
            $data['ngay_sinh'] ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    // Tìm hoặc tạo khách hàng từ thông tin người dùng
    public function findOrCreateByNguoiDungInfo(int $nguoiDungId, ?string $diaChi = null, ?string $gioiTinh = null, ?string $ngaySinh = null): mixed {
        // Tìm khách hàng hiện có
        $khachHang = $this->findByNguoiDungId($nguoiDungId);
        if ($khachHang) {
            return $khachHang;
        }
        
        // Tạo mới nếu chưa có
        $khachHangId = $this->insert([
            'nguoi_dung_id' => $nguoiDungId,
            'dia_chi' => $diaChi,
            'gioi_tinh' => $gioiTinh,
            'ngay_sinh' => $ngaySinh
        ]);
        
        return $this->findById($khachHangId);
    }

    // Lấy thông tin khách hàng với thông tin người dùng
    public function getKhachHangWithNguoiDung(int $khachHangId): mixed {
        $sql = "SELECT " . $this->khachHangSelectColumns('kh') . ", nd.ho_ten, nd.email, nd.so_dien_thoai, nd.vai_tro
                FROM khach_hang kh
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE kh.khach_hang_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        return $stmt->fetch();
    }
}
