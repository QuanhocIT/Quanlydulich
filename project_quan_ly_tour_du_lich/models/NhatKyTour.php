<?php

class NhatKyTour
{
    public PDO $conn;
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

    private function nhatKySelectColumns(string $alias = ''): string
    {
        return $this->selectColumnsFromTable('nhat_ky_tour', $alias);
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

    private function notDeletedClause(string $alias = 'nkt'): string
    {
        if (!$this->hasColumn('nhat_ky_tour', 'deleted_at')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    public function getByHDVAndTour(int $nhanSuId, ?int $tourId = null): array
    {
        $sql = "SELECT " . $this->nhatKySelectColumns('nkt') . ", t.ten_tour
                FROM nhat_ky_tour nkt
                INNER JOIN tour t ON nkt.tour_id = t.tour_id
                                WHERE nkt.nhan_su_id = ?
                                    AND " . $this->notDeletedClause('nkt');
        $params = [(int)$nhanSuId];

        if ($tourId) {
            $sql .= " AND nkt.tour_id = ?";
            $params[] = (int)$tourId;
        }

        $sql .= " ORDER BY nkt.ngay_ghi DESC, nkt.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id, int $nhanSuId): mixed
    {
        $sql = "SELECT " . $this->nhatKySelectColumns() . " FROM nhat_ky_tour WHERE id = ? AND nhan_su_id = ? AND " . $this->notDeletedClause() . " LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id, (int)$nhanSuId]);
        return $stmt->fetch();
    }

    public function insert(array $data): bool
    {
        $sql = "INSERT INTO nhat_ky_tour (tour_id, nhan_su_id, noi_dung, ngay_ghi)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$data['tour_id'],
            (int)$data['nhan_su_id'],
            $data['noi_dung'] ?? '',
            $data['ngay_ghi'] ?? date('Y-m-d')
        ]);
    }

    public function update(int $id, int $nhanSuId, array $data): bool
    {
        $sql = "UPDATE nhat_ky_tour
                SET tour_id = ?, noi_dung = ?, ngay_ghi = ?
            WHERE id = ? AND nhan_su_id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$data['tour_id'],
            $data['noi_dung'] ?? '',
            $data['ngay_ghi'] ?? date('Y-m-d'),
            (int)$id,
            (int)$nhanSuId
        ]);
    }

    public function deleteById(int $id): bool
    {
        if ($this->hasColumn('nhat_ky_tour', 'deleted_at')) {
            $sql = "UPDATE nhat_ky_tour SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM nhat_ky_tour WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }
}

