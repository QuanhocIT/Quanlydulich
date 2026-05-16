<?php
class Payment {
    public const STATUS_TAO_MOI = 'TaoMoi';
    public const STATUS_DANG_XU_LY = 'DangXuLy';
    public const STATUS_THANH_CONG = 'ThanhCong';
    public const STATUS_THAT_BAI = 'ThatBai';
    public const STATUS_HET_HAN = 'HetHan';
    public const STATUS_DA_DOI_SOAT = 'DaDoiSoat';

    public $payment_id;
    public $booking_id;
    public $amount;
    public $payment_method;
    public $payment_date;
    public $status;
    public $note;

    public static function all($conn) {
        $stmt = $conn->prepare("SELECT * FROM payments WHERE deleted_at IS NULL ORDER BY payment_id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getStateList() {
        return [
            self::STATUS_TAO_MOI,
            self::STATUS_DANG_XU_LY,
            self::STATUS_THANH_CONG,
            self::STATUS_THAT_BAI,
            self::STATUS_HET_HAN,
            self::STATUS_DA_DOI_SOAT,
        ];
    }

    public static function getTransitionMap() {
        return [
            self::STATUS_TAO_MOI => [self::STATUS_DANG_XU_LY, self::STATUS_HET_HAN, self::STATUS_THAT_BAI],
            self::STATUS_DANG_XU_LY => [self::STATUS_THANH_CONG, self::STATUS_THAT_BAI, self::STATUS_HET_HAN],
            self::STATUS_THANH_CONG => [self::STATUS_DA_DOI_SOAT],
            self::STATUS_THAT_BAI => [self::STATUS_DANG_XU_LY],
            self::STATUS_HET_HAN => [self::STATUS_DANG_XU_LY],
            self::STATUS_DA_DOI_SOAT => [],
        ];
    }

    public static function canTransition($fromStatus, $toStatus) {
        if ($fromStatus === $toStatus) {
            return true;
        }

        $map = self::getTransitionMap();
        $allowed = $map[(string)$fromStatus] ?? [];
        return in_array((string)$toStatus, $allowed, true);
    }

    public static function ensureStateMachineSchema($conn) {
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

    public static function transitionStatus($conn, $paymentId, $toStatus, $reason, $meta = []) {
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

    private static function logTransition($conn, $paymentId, $action, array $payload) {
        $note = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([(int)$paymentId, (string)$action, date('Y-m-d H:i:s'), (string)$note]);
    }

    public static function find($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findForUpdate($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ? AND deleted_at IS NULL FOR UPDATE");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($conn, $data) {
        $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_date, status, note) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['booking_id'], $data['amount'], $data['payment_method'], $data['payment_date'], $data['status'], $data['note']
        ]);
    }
    public static function update($conn, $id, $data) {
        $stmt = $conn->prepare("UPDATE payments SET booking_id=?, amount=?, payment_method=?, payment_date=?, status=?, note=? WHERE payment_id=? AND deleted_at IS NULL");
        return $stmt->execute([
            $data['booking_id'], $data['amount'], $data['payment_method'], $data['payment_date'], $data['status'], $data['note'], $id
        ]);
    }
    public static function delete($conn, $id) {
        $stmt = $conn->prepare("UPDATE payments SET deleted_at = NOW() WHERE payment_id = ? AND deleted_at IS NULL");
        return $stmt->execute([$id]);
    }
}
