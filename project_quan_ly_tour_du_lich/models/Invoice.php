<?php
class Invoice {
    public int|string|null $invoice_id = null;
    public int|string|null $booking_id = null;
    public int|string|null $customer_id = null;
    public ?string $issue_date = null;
    public ?string $due_date = null;
    public float|int|string|null $total_amount = null;
    public ?string $status = null;
    public ?string $note = null;

    private static $columnExistsCache = [];
    private static $tableColumnsCache = [];

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
            return $alias !== '' ? ($alias . '.invoice_id') : 'invoice_id';
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
        if (!self::hasColumn($conn, 'invoices', 'deleted_at')) {
            return '1=1';
        }

        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    public static function all(PDO $conn): array {
        $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'invoices') . " FROM invoices WHERE " . self::notDeletedClause($conn));
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function find(PDO $conn, int $id): mixed {
        $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'invoices') . " FROM invoices WHERE invoice_id = ? AND " . self::notDeletedClause($conn));
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function create(PDO $conn, array $data): bool {
        $stmt = $conn->prepare("INSERT INTO invoices (booking_id, customer_id, issue_date, due_date, total_amount, status, note) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['booking_id'], $data['customer_id'], $data['issue_date'], $data['due_date'], $data['total_amount'], $data['status'], $data['note']
        ]);
    }
    public static function update(PDO $conn, int $id, array $data): bool {
        $stmt = $conn->prepare("UPDATE invoices SET booking_id=?, customer_id=?, issue_date=?, due_date=?, total_amount=?, status=?, note=? WHERE invoice_id=?");
        return $stmt->execute([
            $data['booking_id'], $data['customer_id'], $data['issue_date'], $data['due_date'], $data['total_amount'], $data['status'], $data['note'], $id
        ]);
    }
    public static function delete(PDO $conn, int $id): bool {
        if (self::hasColumn($conn, 'invoices', 'deleted_at')) {
            $stmt = $conn->prepare("UPDATE invoices SET deleted_at = NOW() WHERE invoice_id = ? AND deleted_at IS NULL");
        } else {
            $stmt = $conn->prepare("DELETE FROM invoices WHERE invoice_id = ?");
        }
        return $stmt->execute([$id]);
    }
}
