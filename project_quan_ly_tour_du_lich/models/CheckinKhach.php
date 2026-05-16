<?php

class CheckinKhach
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

    private function checkinSelectColumns(string $alias = ''): string
    {
        return $this->selectColumnsFromTable('tour_checkin', $alias);
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
        if (!$this->hasColumn('tour_checkin', 'deleted_at')) {
            return '1=1';
        }

        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    public function getByLichKhoiHanh(int $lichKhoiHanhId): array
    {
        $sql = "SELECT " . $this->checkinSelectColumns() . "
                FROM tour_checkin
                WHERE lich_khoi_hanh_id = ?
                                    AND " . $this->notDeletedClause() . "
                ORDER BY updated_at DESC, checkin_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    public function findOne(int $lichKhoiHanhId, int $bookingId, int $khachHangId): mixed
    {
                $sql = "SELECT " . $this->checkinSelectColumns() . "
                FROM tour_checkin
                WHERE lich_khoi_hanh_id = ?
                  AND booking_id = ?
                  AND khach_hang_id = ?
                                    AND " . $this->notDeletedClause() . "
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId, (int)$bookingId, (int)$khachHangId]);
        return $stmt->fetch();
    }

    public function getByBookingId(int $bookingId): array
    {
        $sql = "SELECT " . $this->checkinSelectColumns() . "
                FROM tour_checkin
                WHERE booking_id = ?
                                    AND " . $this->notDeletedClause() . "
                ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$bookingId]);
        return $stmt->fetchAll();
    }

    // Lấy checkin theo nhiều booking_id trong một query, trả về map booking_id => rows.
    public function getByBookingIdsGrouped(array $bookingIds): array
    {
        $normalized = [];
        foreach ($bookingIds as $bookingId) {
            $id = (int)$bookingId;
            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }

        if (empty($normalized)) {
            return [];
        }

        $idList = array_values($normalized);
        $placeholders = implode(',', array_fill(0, count($idList), '?'));
        $sql = "SELECT " . $this->checkinSelectColumns() . "
                FROM tour_checkin
                WHERE booking_id IN ($placeholders)
                                    AND " . $this->notDeletedClause() . "
                ORDER BY booking_id ASC, id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($idList);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $bookingId = (int)($row['booking_id'] ?? 0);
            if ($bookingId <= 0) {
                continue;
            }
            if (!isset($grouped[$bookingId])) {
                $grouped[$bookingId] = [];
            }
            $grouped[$bookingId][] = $row;
        }

        return $grouped;
    }

    public function findById(int $id): mixed
    {
        $sql = "SELECT " . $this->checkinSelectColumns() . "
                FROM tour_checkin
                WHERE id = ?
                                    AND " . $this->notDeletedClause() . "
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function delete(int $id): bool
    {
        if ($this->hasColumn('tour_checkin', 'deleted_at')) {
            $sql = "UPDATE tour_checkin SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM tour_checkin WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }

    public function ensureExtendedSchema(): void
    {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        if (!dbColumnExists('tour_checkin', 'anh_cccd', $this->conn) || !dbColumnExists('tour_checkin', 'anh_passport', $this->conn)) {
            throw new RuntimeException(
                'Schema tour_checkin is missing extended document columns. Please run `php scripts/migrate.php up`.'
            );
        }

        $initialized = true;
    }

    public function insert(array $data): bool
    {
        $this->ensureExtendedSchema();

        $columns = [
            'booking_id', 'khach_hang_id', 'lich_khoi_hanh_id',
            'ho_ten', 'so_cmnd', 'so_passport', 'ngay_sinh', 'gioi_tinh',
            'quoc_tich', 'dia_chi', 'so_dien_thoai', 'email',
            'checkin_time', 'checkout_time', 'trang_thai', 'ghi_chu'
        ];
        $values = [
            (int)$data['booking_id'],
            (int)$data['khach_hang_id'],
            (int)$data['lich_khoi_hanh_id'],
            $data['ho_ten'] ?? '',
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['checkin_time'] ?? null,
            $data['checkout_time'] ?? null,
            $data['trang_thai'] ?? 'ChuaCheckIn',
            $data['ghi_chu'] ?? null,
        ];

        if (dbColumnExists('tour_checkin', 'anh_cccd', $this->conn)) {
            $columns[] = 'anh_cccd';
            $values[] = $data['anh_cccd'] ?? null;
        }

        if (dbColumnExists('tour_checkin', 'anh_passport', $this->conn)) {
            $columns[] = 'anh_passport';
            $values[] = $data['anh_passport'] ?? null;
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO tour_checkin (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE tour_checkin
                SET trang_thai = ?,
                    ghi_chu = ?,
                    checkin_time = ?,
                    checkout_time = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['trang_thai'] ?? 'ChuaCheckIn',
            $data['ghi_chu'] ?? null,
            $data['checkin_time'] ?? null,
            $data['checkout_time'] ?? null,
            (int)$id
        ]);
    }

    public function updateFull(int $id, array $data): bool
    {
        $this->ensureExtendedSchema();

        $sql = "UPDATE tour_checkin
                SET ho_ten = ?,
                    so_cmnd = ?,
                    so_passport = ?,
                    ngay_sinh = ?,
                    gioi_tinh = ?,
                    quoc_tich = ?,
                    dia_chi = ?,
                    so_dien_thoai = ?,
                    email = ?,
                    ghi_chu = ?";

        $values = [
            $data['ho_ten'] ?? '',
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['ghi_chu'] ?? null,
        ];

        if (dbColumnExists('tour_checkin', 'anh_cccd', $this->conn) && array_key_exists('anh_cccd', $data)) {
            $sql .= ", anh_cccd = ?";
            $values[] = $data['anh_cccd'] ?? null;
        }

        if (dbColumnExists('tour_checkin', 'anh_passport', $this->conn) && array_key_exists('anh_passport', $data)) {
            $sql .= ", anh_passport = ?";
            $values[] = $data['anh_passport'] ?? null;
        }

        $sql .= "
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $values[] = (int)$id;
        return $stmt->execute($values);
    }
}

