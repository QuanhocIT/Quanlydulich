<?php
class PaymentLog {
    public int|string|null $log_id = null;
    public int|string|null $payment_id = null;
    public ?string $action = null;
    public ?string $log_time = null;
    public ?string $note = null;

    private static array $columnExistsCache = [];
    private static array $tableColumnsCache = [];

    private static function getTableColumns(PDO $conn, string $tableName): array {
        if (!array_key_exists($tableName, self::$tableColumnsCache)) {
            $sql = "SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?
                    ORDER BY ORDINAL_POSITION";
            $stmt = $conn->prepare($sql);
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

    private static function selectColumnsClause(PDO $conn, string $tableName, string $alias = ''): string {
        $columns = self::getTableColumns($conn, $tableName);
        if (empty($columns)) {
            return $alias !== '' ? ($alias . '.log_id') : 'log_id';
        }

        if ($alias === '') {
            return implode(', ', $columns);
        }

        $prefixed = array_map(static function ($column) use ($alias) {
            return $alias . '.' . $column;
        }, $columns);

        return implode(', ', $prefixed);
    }

    private static function hasColumn(PDO $conn, string $tableName, string $columnName): bool {
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
            $stmt = $conn->prepare($sql);
            $stmt->execute([$tableName, $columnName]);
            self::$columnExistsCache[$key] = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            self::$columnExistsCache[$key] = false;
        }

        return self::$columnExistsCache[$key];
    }

    private static function notDeletedClause(PDO $conn, string $alias = ''): string {
        if (!self::hasColumn($conn, 'payment_logs', 'deleted_at')) {
            return '1=1';
        }

        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    public static function all(PDO $conn, ?int $payment_id = null): array {
        if ($payment_id) {
            $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'payment_logs') . " FROM payment_logs WHERE payment_id = ? AND " . self::notDeletedClause($conn));
            $stmt->execute([$payment_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'payment_logs') . " FROM payment_logs WHERE " . self::notDeletedClause($conn));
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    public static function find(PDO $conn, int $id): mixed {
        $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'payment_logs') . " FROM payment_logs WHERE log_id = ? AND " . self::notDeletedClause($conn));
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function create(PDO $conn, array $data): bool {
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['payment_id'], $data['action'], $data['log_time'], $data['note']
        ]);
    }
    public static function update(PDO $conn, int $id, array $data): bool {
        $stmt = $conn->prepare("UPDATE payment_logs SET payment_id=?, action=?, log_time=?, note=? WHERE log_id=? AND " . self::notDeletedClause($conn));
        return $stmt->execute([
            $data['payment_id'], $data['action'], $data['log_time'], $data['note'], $id
        ]);
    }
    public static function delete(PDO $conn, int $id): bool {
        if (self::hasColumn($conn, 'payment_logs', 'deleted_at')) {
            $stmt = $conn->prepare("UPDATE payment_logs SET deleted_at = NOW() WHERE log_id = ? AND deleted_at IS NULL");
        } else {
            $stmt = $conn->prepare("DELETE FROM payment_logs WHERE log_id = ?");
        }
        return $stmt->execute([$id]);
    }
}
