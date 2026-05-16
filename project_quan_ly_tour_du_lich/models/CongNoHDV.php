<?php
class CongNoHDV {
    public PDO $conn;
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

    private function congNoSelectColumns(string $alias = ''): string {
        return $this->selectColumnsFromTable('cong_no_hdv', $alias);
    }
    // Tạo mới hóa đơn công nợ HDV
    public function create(array $data): bool {
        $sql = "INSERT INTO cong_no_hdv (tour_id, hdv_id, so_tien, loai_cong_no, anh_hoa_don, trang_thai, ngay_gui, ghi_chu) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tour_id'],
            $data['hdv_id'],
            $data['so_tien'],
            $data['loai_cong_no'],
            $data['anh_hoa_don'],
            $data['trang_thai'],
            $data['ghi_chu'] ?? null
        ]);
    }
    // Lấy danh sách hóa đơn công nợ theo HDV
    public function getByHDV(int $hdv_id): array {
        $sql = "SELECT " . $this->congNoSelectColumns() . " FROM cong_no_hdv WHERE hdv_id = ? ORDER BY ngay_gui DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdv_id]);
        return $stmt->fetchAll();
    }
    // Lấy danh sách hóa đơn chờ duyệt cho admin
    public function getChoDuyet(): array {
        $sql = "SELECT " . $this->congNoSelectColumns('cnh') . ", t.ten_tour, nd.ho_ten as ten_hdv FROM cong_no_hdv cnh JOIN tour t ON cnh.tour_id = t.tour_id JOIN nguoi_dung nd ON cnh.hdv_id = nd.id WHERE cnh.trang_thai = 'ChoDuyet' ORDER BY cnh.ngay_gui DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    // Duyệt hóa đơn
    public function approve(int $id): bool {
        $sql = "UPDATE cong_no_hdv SET trang_thai = 'DaDuyet' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    // Từ chối hóa đơn
    public function reject(int $id, string $ly_do): bool {
        $sql = "UPDATE cong_no_hdv SET trang_thai = 'TuChoi', ghi_chu = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$ly_do, $id]);
    }

    // Lấy số công nợ quá hạn theo đúng nghiệp vụ: còn dư nợ và đã quá hạn thanh toán.
    public function getQuaHanCount(int $days = 7): int {
        $sql = "SELECT COUNT(*) AS total
                FROM cong_no_hdv c
                LEFT JOIN (
                    SELECT cong_no_hdv_id, COALESCE(SUM(so_tien), 0) AS tong_da_thanh_toan
                    FROM lich_su_thanh_toan_hdv
                    GROUP BY cong_no_hdv_id
                ) ls ON ls.cong_no_hdv_id = c.id
                WHERE c.han_thanh_toan IS NOT NULL
                  AND c.han_thanh_toan < CURDATE()
                  AND (COALESCE(c.so_tien, 0) - COALESCE(ls.tong_da_thanh_toan, 0)) > 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    // Lấy danh sách công nợ quá hạn
    public function getQuaHanList(int $days = 7): array {
        $sql = "SELECT " . $this->congNoSelectColumns('cnh') . ", t.ten_tour, nd.ho_ten as ten_hdv FROM cong_no_hdv cnh 
                JOIN tour t ON cnh.tour_id = t.tour_id 
                JOIN nguoi_dung nd ON cnh.hdv_id = nd.id 
                WHERE cnh.trang_thai NOT IN ('DaDuyet', 'TuChoi') 
                AND cnh.ngay_gui < DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY cnh.ngay_gui ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
