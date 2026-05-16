<?php
class InvoiceItem {
    public int|string|null $item_id = null;
    public int|string|null $invoice_id = null;
    public ?string $description = null;
    public int|float|string|null $quantity = null;
    public int|float|string|null $unit_price = null;
    public int|float|string|null $amount = null;

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
            return $alias !== '' ? ($alias . '.item_id') : 'item_id';
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
        if (!self::hasColumn($conn, 'invoice_items', 'deleted_at')) {
            return '1=1';
        }

        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    public static function all(PDO $conn, ?int $invoice_id = null): array {
        if ($invoice_id) {
            $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'invoice_items') . " FROM invoice_items WHERE invoice_id = ? AND " . self::notDeletedClause($conn));
            $stmt->execute([$invoice_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'invoice_items') . " FROM invoice_items WHERE " . self::notDeletedClause($conn));
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    public static function find(PDO $conn, int $id): mixed {
        $stmt = $conn->prepare("SELECT " . self::selectColumnsClause($conn, 'invoice_items') . " FROM invoice_items WHERE item_id = ? AND " . self::notDeletedClause($conn));
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function create(PDO $conn, array $data): bool {
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['invoice_id'], $data['description'], $data['quantity'], $data['unit_price'], $data['amount']
        ]);
    }
    public static function update(PDO $conn, int $id, array $data): bool {
        $stmt = $conn->prepare("UPDATE invoice_items SET invoice_id=?, description=?, quantity=?, unit_price=?, amount=? WHERE item_id=?");
        return $stmt->execute([
            $data['invoice_id'], $data['description'], $data['quantity'], $data['unit_price'], $data['amount'], $id
        ]);
    }
    public static function delete(PDO $conn, int $id): bool {
        if (self::hasColumn($conn, 'invoice_items', 'deleted_at')) {
            $stmt = $conn->prepare("UPDATE invoice_items SET deleted_at = NOW() WHERE item_id = ? AND deleted_at IS NULL");
        } else {
            $stmt = $conn->prepare("DELETE FROM invoice_items WHERE item_id = ?");
        }
        return $stmt->execute([$id]);
    }
}
