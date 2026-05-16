<?php
// Model cho BookingDeletionHistory - Lịch sử xóa booking
class BookingDeletionHistory 
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

    private function bookingDeletionSelectColumns(string $alias = ''): string {
        $columns = $this->getTableColumns('booking_deletion_history');
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

    // Lấy tất cả lịch sử xóa
    public function getAll(): array {
        $sql = "SELECT " . $this->bookingDeletionSelectColumns('bdh') . ", 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa,
                t.ten_tour, t.tour_id,
                kh.khach_hang_id,
                nd_khach.ho_ten as ten_khach_hang, nd_khach.email as email_khach_hang
                FROM booking_deletion_history bdh
                LEFT JOIN nguoi_dung nd ON bdh.nguoi_xoa_id = nd.id
                LEFT JOIN tour t ON bdh.tour_id = t.tour_id
                LEFT JOIN khach_hang kh ON bdh.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd_khach ON kh.nguoi_dung_id = nd_khach.id
                ORDER BY bdh.thoi_gian_xoa DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy lịch sử xóa theo booking_id (nếu còn lưu)
    public function getByBookingId(int $bookingId): mixed {
        $sql = "SELECT " . $this->bookingDeletionSelectColumns('bdh') . ", 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa
                FROM booking_deletion_history bdh
                LEFT JOIN nguoi_dung nd ON bdh.nguoi_xoa_id = nd.id
                WHERE bdh.booking_id = ?
                ORDER BY bdh.thoi_gian_xoa DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$bookingId]);
        return $stmt->fetch();
    }

    // Thêm lịch sử xóa
    public function insert(array $data): bool {
        $sql = "INSERT INTO booking_deletion_history (
                    booking_id, tour_id, khach_hang_id, 
                    nguoi_xoa_id, ly_do_xoa, thong_tin_booking, 
                    thoi_gian_xoa
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['booking_id'] ?? null,
            $data['tour_id'] ?? null,
            $data['khach_hang_id'] ?? null,
            $data['nguoi_xoa_id'] ?? null,
            $data['ly_do_xoa'] ?? null,
            $data['thong_tin_booking'] ?? null,
            $data['thoi_gian_xoa'] ?? date('Y-m-d H:i:s')
        ]);
    }
}

