<?php

class DichVuNhaCungCap
{
    protected PDO $conn;
    private static array $columnExistsCache = [];
    private static array $tableColumnsCache = [];

    public function __construct()
    {
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

    private function selectColumnsFromTable(string $tableName, string $alias = ''): string
    {
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

    private function dichVuSelectColumns(string $alias = ''): string
    {
        return $this->selectColumnsFromTable('dich_vu_nha_cung_cap', $alias);
    }

    private function hasColumn(string $tableName, string $columnName): bool
    {
        $key = $tableName . '.' . $columnName;
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
        }

        try {
            $sql = "SELECT COUNT(*)
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?
                      AND COLUMN_NAME = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tableName, $columnName]);
            self::$columnExistsCache[$key] = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            self::$columnExistsCache[$key] = false;
        }

        return self::$columnExistsCache[$key];
    }

    private function notDeletedClause(string $alias = ''): string
    {
        if (!$this->hasColumn('dich_vu_nha_cung_cap', 'deleted_at')) {
            return '1=1';
        }

        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    public function getAllBySupplier(int $nhaCungCapId): array
    {
        $sql = "SELECT " . $this->dichVuSelectColumns() . " FROM dich_vu_nha_cung_cap WHERE nha_cung_cap_id = ? AND " . $this->notDeletedClause() . " ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$nhaCungCapId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id, int $nhaCungCapId): mixed
    {
        $sql = "SELECT " . $this->dichVuSelectColumns() . " FROM dich_vu_nha_cung_cap WHERE id = ? AND nha_cung_cap_id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id, (int)$nhaCungCapId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(int $nhaCungCapId, array $data): int|string
    {
        $sql = "INSERT INTO dich_vu_nha_cung_cap 
                (nha_cung_cap_id, ten_dich_vu, mo_ta, loai_dich_vu, gia_tham_khao, don_vi_tinh, cong_suat_toi_da, thoi_gian_xu_ly, tai_lieu_dinh_kem, trang_thai)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            (int)$nhaCungCapId,
            $data['ten_dich_vu'],
            $data['mo_ta'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['gia_tham_khao'] !== '' ? $data['gia_tham_khao'] : null,
            $data['don_vi_tinh'] ?? null,
            $data['cong_suat_toi_da'] !== '' ? (int)$data['cong_suat_toi_da'] : null,
            $data['thoi_gian_xu_ly'] ?? null,
            $data['tai_lieu_dinh_kem'] ?? null,
            $data['trang_thai'] ?? 'HoatDong'
        ]);
        return $this->conn->lastInsertId();
    }

    public function getBySupplierIds(array $supplierIds): array
    {
        $supplierIds = array_filter(array_map('intval', $supplierIds));
        if (empty($supplierIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($supplierIds), '?'));
        $sql = "SELECT " . $this->dichVuSelectColumns() . " FROM dich_vu_nha_cung_cap 
                WHERE nha_cung_cap_id IN ($placeholders)
                                    AND " . $this->notDeletedClause() . "
                ORDER BY nha_cung_cap_id, ten_dich_vu";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($supplierIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $id, int $nhaCungCapId, array $data): bool
    {
        $sql = "UPDATE dich_vu_nha_cung_cap
                SET ten_dich_vu = ?, mo_ta = ?, loai_dich_vu = ?, gia_tham_khao = ?, 
                    don_vi_tinh = ?, cong_suat_toi_da = ?, thoi_gian_xu_ly = ?, 
                    tai_lieu_dinh_kem = ?, trang_thai = ?
                WHERE id = ? AND nha_cung_cap_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ten_dich_vu'],
            $data['mo_ta'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['gia_tham_khao'] !== '' ? $data['gia_tham_khao'] : null,
            $data['don_vi_tinh'] ?? null,
            $data['cong_suat_toi_da'] !== '' ? (int)$data['cong_suat_toi_da'] : null,
            $data['thoi_gian_xu_ly'] ?? null,
            $data['tai_lieu_dinh_kem'] ?? null,
            $data['trang_thai'] ?? 'HoatDong',
            (int)$id,
            (int)$nhaCungCapId
        ]);
    }

    public function delete(int $id, int $nhaCungCapId): bool
    {
        if ($this->hasColumn('dich_vu_nha_cung_cap', 'deleted_at')) {
            $sql = "UPDATE dich_vu_nha_cung_cap SET deleted_at = NOW() WHERE id = ? AND nha_cung_cap_id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM dich_vu_nha_cung_cap WHERE id = ? AND nha_cung_cap_id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id, (int)$nhaCungCapId]);
    }
}


