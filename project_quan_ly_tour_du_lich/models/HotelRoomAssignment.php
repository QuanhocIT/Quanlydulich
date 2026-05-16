<?php

class HotelRoomAssignment {
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

    private function roomAssignmentSelectColumns(string $alias = ''): string {
        return $this->selectColumnsFromTable('hotel_room_assignment', $alias);
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

    private function notDeletedClause(string $alias = 'hra'): string {
        if (!$this->hasColumn('hotel_room_assignment', 'deleted_at')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    // Lấy tất cả phân phòng
    public function getAll(): array {
        $sql = "SELECT " . $this->roomAssignmentSelectColumns('hra') . ", 
                       tc.ho_ten as khach_ho_ten,
                       b.tour_id
                FROM hotel_room_assignment hra
                LEFT JOIN tour_checkin tc ON hra.checkin_id = tc.id
                LEFT JOIN booking b ON hra.booking_id = b.booking_id
                WHERE " . $this->notDeletedClause('hra') . "
                ORDER BY hra.ngay_nhan_phong DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy phân phòng theo ID
    public function findById(int $id): mixed {
        $sql = "SELECT " . $this->roomAssignmentSelectColumns() . " FROM hotel_room_assignment WHERE id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Lấy phân phòng theo lịch khởi hành
    public function getByLichKhoiHanhId(int $lichKhoiHanhId): array {
        $sql = "SELECT " . $this->roomAssignmentSelectColumns('hra') . ", 
                       tc.ho_ten as khach_ho_ten, tc.so_dien_thoai,
                       b.booking_id
                FROM hotel_room_assignment hra
                LEFT JOIN tour_checkin tc ON hra.checkin_id = tc.id
                LEFT JOIN booking b ON hra.booking_id = b.booking_id
                                WHERE hra.lich_khoi_hanh_id = ?
                                    AND " . $this->notDeletedClause('hra') . "
                ORDER BY hra.ten_khach_san, hra.so_phong";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Lấy phân phòng theo booking
    public function getByBookingId(int $bookingId): array {
        $sql = "SELECT " . $this->roomAssignmentSelectColumns() . " FROM hotel_room_assignment WHERE booking_id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    // Thêm phân phòng mới
    public function insert(array $data): bool {
        $sql = "INSERT INTO hotel_room_assignment (
                    lich_khoi_hanh_id, booking_id, checkin_id, ten_khach_san,
                    so_phong, loai_phong, so_giuong, ngay_nhan_phong, ngay_tra_phong,
                    gia_phong, trang_thai, ghi_chu
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['lich_khoi_hanh_id'],
            $data['booking_id'],
            $data['checkin_id'] ?? null,
            $data['ten_khach_san'],
            $data['so_phong'],
            $data['loai_phong'] ?? 'Standard',
            $data['so_giuong'] ?? 1,
            $data['ngay_nhan_phong'],
            $data['ngay_tra_phong'],
            $data['gia_phong'] ?? 0,
            $data['trang_thai'] ?? 'DaDatPhong',
            $data['ghi_chu'] ?? null
        ]);
    }

    // Cập nhật phân phòng
    public function update(int $id, array $data): bool {
        $sql = "UPDATE hotel_room_assignment SET 
                ten_khach_san = ?, so_phong = ?, loai_phong = ?, so_giuong = ?,
                ngay_nhan_phong = ?, ngay_tra_phong = ?, gia_phong = ?,
                trang_thai = ?, ghi_chu = ?
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ten_khach_san'],
            $data['so_phong'],
            $data['loai_phong'] ?? 'Standard',
            $data['so_giuong'] ?? 1,
            $data['ngay_nhan_phong'],
            $data['ngay_tra_phong'],
            $data['gia_phong'] ?? 0,
            $data['trang_thai'] ?? 'DaDatPhong',
            $data['ghi_chu'] ?? null,
            $id
        ]);
    }

    // Cập nhật trạng thái
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE hotel_room_assignment SET trang_thai = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // Xóa phân phòng
    public function delete(int $id): bool {
        if ($this->hasColumn('hotel_room_assignment', 'deleted_at')) {
            $sql = "UPDATE hotel_room_assignment SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM hotel_room_assignment WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Lấy danh sách khách sạn đã sử dụng
    public function getHotelList(): array {
        $sql = "SELECT DISTINCT ten_khach_san FROM hotel_room_assignment ORDER BY ten_khach_san";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Thống kê phòng theo lịch khởi hành
    public function getStatsByLichKhoiHanh(int $lichKhoiHanhId): mixed {
        $sql = "SELECT 
                COUNT(*) as total_rooms,
                SUM(CASE WHEN trang_thai = 'DaDatPhong' THEN 1 ELSE 0 END) as da_dat,
                SUM(CASE WHEN trang_thai = 'DaNhanPhong' THEN 1 ELSE 0 END) as da_nhan,
                SUM(CASE WHEN trang_thai = 'DaTraPhong' THEN 1 ELSE 0 END) as da_tra,
                SUM(gia_phong) as tong_chi_phi
                FROM hotel_room_assignment 
                                WHERE lich_khoi_hanh_id = ?
                                    AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$lichKhoiHanhId]);
        return $stmt->fetch();
    }
}
