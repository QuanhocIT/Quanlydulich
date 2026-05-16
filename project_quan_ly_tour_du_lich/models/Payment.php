<?php
class Payment {
    public const STATUS_TAO_MOI = 'TaoMoi';
    public const STATUS_DANG_XU_LY = 'DangXuLy';
    public const STATUS_THANH_CONG = 'ThanhCong';
    public const STATUS_THAT_BAI = 'ThatBai';
    public const STATUS_HET_HAN = 'HetHan';
    public const STATUS_DA_DOI_SOAT = 'DaDoiSoat';

    public int|string|null $payment_id = null;
    public int|string|null $booking_id = null;
    public int|float|string|null $amount = null;
    public ?string $payment_method = null;
    public ?string $payment_date = null;
    public ?string $status = null;
    public ?string $note = null;

    private static array $columnExistsCache = [];

    private static function selectColumnsClause(string $alias = ''): string {
        $columns = [
            'payment_id',
            'booking_id',
            'amount',
            'payment_method',
            'payment_date',
            'status',
            'note',
        ];

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
        if (!self::hasColumn($conn, 'payments', 'deleted_at')) {
            return '1=1';
        }

        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    public static function all(PDO $conn): array {
        $stmt = $conn->prepare("SELECT " . self::selectColumnsClause() . " FROM payments WHERE " . self::notDeletedClause($conn) . " ORDER BY payment_id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStateList(): array {
        return [
            self::STATUS_TAO_MOI,
            self::STATUS_DANG_XU_LY,
            self::STATUS_THANH_CONG,
            self::STATUS_THAT_BAI,
            self::STATUS_HET_HAN,
            self::STATUS_DA_DOI_SOAT,
        ];
    }

    public static function getTransitionMap(): array {
        return [
            self::STATUS_TAO_MOI => [self::STATUS_DANG_XU_LY, self::STATUS_HET_HAN, self::STATUS_THAT_BAI],
            self::STATUS_DANG_XU_LY => [self::STATUS_THANH_CONG, self::STATUS_THAT_BAI, self::STATUS_HET_HAN],
            self::STATUS_THANH_CONG => [self::STATUS_DA_DOI_SOAT],
            self::STATUS_THAT_BAI => [self::STATUS_DANG_XU_LY],
            self::STATUS_HET_HAN => [self::STATUS_DANG_XU_LY],
            self::STATUS_DA_DOI_SOAT => [],
        ];
    }

    public static function canTransition(string $fromStatus, string $toStatus): bool {
        if ($fromStatus === $toStatus) {
            return true;
        }

        $map = self::getTransitionMap();
        $allowed = $map[(string)$fromStatus] ?? [];
        return in_array((string)$toStatus, $allowed, true);
    }

    public static function ensureStateMachineSchema(PDO $conn): void {
        try {
            $stmt = $conn->query("SHOW COLUMNS FROM payments LIKE 'status'");
            $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            if (!$column || empty($column['Type'])) {
                throw new RuntimeException('Missing payments.status column');
            }

            if (stripos((string)$column['Type'], 'enum(') !== 0) {
                throw new RuntimeException('payments.status must be ENUM');
            }

            $rawEnum = (string)$column['Type'];
            preg_match('/^enum\((.*)\)$/i', $rawEnum, $matches);
            $parts = isset($matches[1]) ? str_getcsv($matches[1], ',', "'", '\\') : [];
            $enumValues = [];
            foreach ($parts as $part) {
                $value = trim((string)$part, "'");
                if ($value !== '') {
                    $enumValues[] = $value;
                }
            }

            foreach (self::getStateList() as $required) {
                if (!in_array($required, $enumValues, true)) {
                    throw new RuntimeException('payments.status enum is missing state: ' . $required);
                }
            }
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Schema payments is not ready for payment state machine. Please run `php scripts/migrate.php up`. Root cause: ' . $e->getMessage()
            );
        }
    }

    public static function transitionStatus(PDO $conn, int $paymentId, string $toStatus, string $reason, array $meta = []): array {
        $paymentId = (int)$paymentId;
        $toStatus = (string)$toStatus;

        if ($paymentId <= 0 || !in_array($toStatus, self::getStateList(), true)) {
            return ['ok' => false, 'message' => 'Invalid payment transition request'];
        }

        $payment = self::find($conn, $paymentId);
        if (!$payment) {
            return ['ok' => false, 'message' => 'Payment not found'];
        }

        $fromStatus = (string)($payment['status'] ?? self::STATUS_TAO_MOI);
        if (!self::canTransition($fromStatus, $toStatus)) {
            self::logTransition($conn, $paymentId, 'STATE_TRANSITION_BLOCKED', [
                'from' => $fromStatus,
                'to' => $toStatus,
                'reason' => $reason,
                'meta' => $meta,
            ]);
            return ['ok' => false, 'message' => 'Invalid transition: ' . $fromStatus . ' -> ' . $toStatus];
        }

        if ($fromStatus === $toStatus) {
            self::logTransition($conn, $paymentId, 'STATE_TRANSITION_NOOP', [
                'from' => $fromStatus,
                'to' => $toStatus,
                'reason' => $reason,
                'meta' => $meta,
            ]);
            return ['ok' => true, 'message' => 'No status change'];
        }

        $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE payment_id = ?");
        $stmt->execute([$toStatus, $paymentId]);

        self::logTransition($conn, $paymentId, 'STATE_TRANSITION', [
            'from' => $fromStatus,
            'to' => $toStatus,
            'reason' => $reason,
            'meta' => $meta,
        ]);

        return ['ok' => true, 'message' => 'Transition applied', 'from' => $fromStatus, 'to' => $toStatus];
    }

    private static function logTransition(PDO $conn, int $paymentId, string $action, array $payload): void {
        $note = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([(int)$paymentId, (string)$action, date('Y-m-d H:i:s'), (string)$note]);
    }

    public static function find(PDO $conn, int $id): mixed {
        $stmt = $conn->prepare("SELECT " . self::selectColumnsClause() . " FROM payments WHERE payment_id = ? AND " . self::notDeletedClause($conn));
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findForUpdate(PDO $conn, int $id): mixed {
        $stmt = $conn->prepare("SELECT " . self::selectColumnsClause() . " FROM payments WHERE payment_id = ? AND " . self::notDeletedClause($conn) . " FOR UPDATE");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $conn, array $data): bool {
        $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_date, status, note) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['booking_id'], $data['amount'], $data['payment_method'], $data['payment_date'], $data['status'], $data['note']
        ]);
    }
    public static function update(PDO $conn, int $id, array $data): bool {
        $stmt = $conn->prepare("UPDATE payments SET booking_id=?, amount=?, payment_method=?, payment_date=?, status=?, note=? WHERE payment_id=? AND " . self::notDeletedClause($conn));
        return $stmt->execute([
            $data['booking_id'], $data['amount'], $data['payment_method'], $data['payment_date'], $data['status'], $data['note'], $id
        ]);
    }
    public static function delete(PDO $conn, int $id): bool {
        if (self::hasColumn($conn, 'payments', 'deleted_at')) {
            $stmt = $conn->prepare("UPDATE payments SET deleted_at = NOW() WHERE payment_id = ? AND deleted_at IS NULL");
        } else {
            $stmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
        }
        return $stmt->execute([$id]);
    }
}
