<?php

class TourCheckin {
    public PDO $conn;
    private static array $columnExistsCache = [];
    private static array $tableColumnsCache = [];
    
    public function __construct() {
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

    private function checkinSelectColumns(string $alias = ''): string {
        return $this->selectColumnsFromTable('tour_checkin', $alias);
    }

    private function hasColumn(string $tableName, string $columnName): bool {
        $key = $tableName . '.' . $columnName;
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
        }
        try {
            $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tableName, $columnName]);
            self::$columnExistsCache[$key] = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            self::$columnExistsCache[$key] = false;
        }
        return self::$columnExistsCache[$key];
    }

    private function notDeletedClause(string $alias = 'tc'): string {
        if (!$this->hasColumn('tour_checkin', 'deleted_at')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    // Lấy tất cả check-in
    public function getAll(): array {
        $sql = "SELECT " . $this->checkinSelectColumns('tc') . ", 
                       b.tour_id, b.so_nguoi, b.ngay_khoi_hanh,
                       kh.dia_chi as khach_hang_dia_chi,
                       nd.ho_ten as nguoi_dung_ho_ten, nd.email as nguoi_dung_email, nd.so_dien_thoai as nguoi_dung_sdt
                FROM tour_checkin tc
                LEFT JOIN booking b ON tc.booking_id = b.booking_id
                LEFT JOIN khach_hang kh ON tc.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE " . $this->notDeletedClause('tc') . "
                ORDER BY tc.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy check-in theo ID
    public function findById(int $id): mixed {
        $sql = "SELECT " . $this->checkinSelectColumns() . " FROM tour_checkin WHERE id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Lấy check-in theo booking
    public function getByBookingId(int $bookingId): array {
        $sql = "SELECT " . $this->checkinSelectColumns('tc') . ", 
                       nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM tour_checkin tc
                LEFT JOIN khach_hang kh ON tc.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                                WHERE tc.booking_id = ?
                                    AND " . $this->notDeletedClause('tc');
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    // Lấy check-in theo lịch khởi hành
    public function getByLichKhoiHanhId(int $lichKhoiHanhId): array {
        $sql = "SELECT " . $this->checkinSelectColumns('tc') . ", 
                       b.booking_id, b.so_nguoi, b.tong_tien,
                       nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM tour_checkin tc
                LEFT JOIN booking b ON tc.booking_id = b.booking_id
                LEFT JOIN khach_hang kh ON tc.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                                WHERE tc.lich_khoi_hanh_id = ?
                                    AND " . $this->notDeletedClause('tc') . "
                ORDER BY tc.checkin_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Thêm check-in mới
    public function insert(array $data): bool {
        $sql = "INSERT INTO tour_checkin (
                    booking_id, khach_hang_id, lich_khoi_hanh_id, ho_ten, 
                    so_cmnd, so_passport, ngay_sinh, gioi_tinh, quoc_tich,
                    dia_chi, so_dien_thoai, email, checkin_time, trang_thai, ghi_chu
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['booking_id'],
            $data['khach_hang_id'],
            $data['lich_khoi_hanh_id'] ?? null,
            $data['ho_ten'],
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['checkin_time'] ?? date('Y-m-d H:i:s'),
            $data['trang_thai'] ?? 'DaCheckIn',
            $data['ghi_chu'] ?? null
        ]);
    }

    // Cập nhật check-in
    public function update(int $id, array $data): bool {
        $sql = "UPDATE tour_checkin SET 
                ho_ten = ?, so_cmnd = ?, so_passport = ?, ngay_sinh = ?,
                gioi_tinh = ?, quoc_tich = ?, dia_chi = ?, so_dien_thoai = ?,
                email = ?, trang_thai = ?, ghi_chu = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ho_ten'],
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['trang_thai'] ?? 'DaCheckIn',
            $data['ghi_chu'] ?? null,
            $id
        ]);
    }

    // Checkout
    public function checkout(int $id): bool {
        $sql = "UPDATE tour_checkin SET 
                trang_thai = 'DaCheckOut', 
                checkout_time = NOW() 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Xóa check-in
    public function delete(int $id): bool {
        if ($this->hasColumn('tour_checkin', 'deleted_at')) {
            $sql = "UPDATE tour_checkin SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM tour_checkin WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Thống kê check-in theo lịch khởi hành
    public function getStatsByLichKhoiHanh(int $lichKhoiHanhId): mixed {
        $sql = "SELECT 
                COUNT(*) as total_checkin,
                SUM(CASE WHEN trang_thai = 'DaCheckIn' THEN 1 ELSE 0 END) as da_checkin,
                SUM(CASE WHEN trang_thai = 'ChuaCheckIn' THEN 1 ELSE 0 END) as chua_checkin,
                SUM(CASE WHEN trang_thai = 'DaCheckOut' THEN 1 ELSE 0 END) as da_checkout
                FROM tour_checkin 
                                WHERE lich_khoi_hanh_id = ?
                                    AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetch();
    }
}
