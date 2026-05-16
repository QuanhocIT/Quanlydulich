<?php
// Model cho SupplierDeletionHistory - Lịch sử xóa nhà cung cấp
class SupplierDeletionHistory 
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

    private function supplierDeletionSelectColumns(string $alias = ''): string {
        $columns = $this->getTableColumns('lich_su_xoa_nha_cung_cap');
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
        $sql = "SELECT " . $this->supplierDeletionSelectColumns('sdh') . ", 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa,
                nd_supplier.ho_ten as ten_nha_cung_cap, nd_supplier.email as email_nha_cung_cap
                FROM lich_su_xoa_nha_cung_cap sdh
                LEFT JOIN nguoi_dung nd ON sdh.nguoi_xoa_id = nd.id
                LEFT JOIN nha_cung_cap ncc ON sdh.nha_cung_cap_id = ncc.id_nha_cung_cap
                LEFT JOIN nguoi_dung nd_supplier ON ncc.nguoi_dung_id = nd_supplier.id
                ORDER BY sdh.thoi_gian_xoa DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy lịch sử xóa theo nha_cung_cap_id (bản ghi mới nhất)
    public function getBySupplierId(int $supplierId): mixed {
        $sql = "SELECT " . $this->supplierDeletionSelectColumns('sdh') . ", 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa
                FROM lich_su_xoa_nha_cung_cap sdh
                LEFT JOIN nguoi_dung nd ON sdh.nguoi_xoa_id = nd.id
                WHERE sdh.nha_cung_cap_id = ?
                ORDER BY sdh.thoi_gian_xoa DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$supplierId]);
        return $stmt->fetch();
    }

    // Lấy chi tiết lịch sử xóa theo id bản ghi
    public function getById(int $id): mixed {
        $sql = "SELECT " . $this->supplierDeletionSelectColumns('sdh') . ", 
                nd.ho_ten as nguoi_xoa, nd.email as email_nguoi_xoa,
                nd_supplier.ho_ten as ten_nha_cung_cap, nd_supplier.email as email_nha_cung_cap
                FROM lich_su_xoa_nha_cung_cap sdh
                LEFT JOIN nguoi_dung nd ON sdh.nguoi_xoa_id = nd.id
                LEFT JOIN nha_cung_cap ncc ON sdh.nha_cung_cap_id = ncc.id_nha_cung_cap
                LEFT JOIN nguoi_dung nd_supplier ON ncc.nguoi_dung_id = nd_supplier.id
                WHERE sdh.id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    // Thêm lịch sử xóa
    public function insert(array $data): bool {
        $sql = "INSERT INTO lich_su_xoa_nha_cung_cap (
                    nha_cung_cap_id, nguoi_dung_id,
                    nguoi_xoa_id, ly_do_xoa, thong_tin_nha_cung_cap, 
                    thoi_gian_xoa
                ) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nha_cung_cap_id'] ?? null,
            $data['nguoi_dung_id'] ?? null,
            $data['nguoi_xoa_id'] ?? null,
            $data['ly_do_xoa'] ?? null,
            $data['thong_tin_nha_cung_cap'] ?? null,
            $data['thoi_gian_xoa'] ?? date('Y-m-d H:i:s')
        ]);
    }
}

