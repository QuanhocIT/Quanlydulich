<?php

class PaymentIdempotency {
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public static function ensureTable($conn) {
        try {
            $conn->query('SELECT id, scope, idem_key, status FROM payment_idempotency LIMIT 1');
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Schema payment_idempotency is missing. Please run `php scripts/migrate.php up`. Root cause: ' . $e->getMessage()
            );
        }
    }

    public static function claim($conn, $scope, $rawKey, $payload = '') {
        self::ensureTable($conn);

        $scope = trim((string)$scope);
        $idemKey = self::hashKey($scope, (string)$rawKey);
        $payloadHash = $payload !== '' ? hash('sha256', (string)$payload) : null;

        $stmtInsert = $conn->prepare('INSERT IGNORE INTO payment_idempotency (scope, idem_key, status, payload_hash, created_at, updated_at, last_seen_at) VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())');
        $stmtInsert->execute([$scope, $idemKey, self::STATUS_PROCESSING, $payloadHash]);
        $isOwner = ((int)$stmtInsert->rowCount() > 0);

        $stmtRow = $conn->prepare('SELECT id, status, response_code, response_message FROM payment_idempotency WHERE scope = ? AND idem_key = ? LIMIT 1');
        $stmtRow->execute([$scope, $idemKey]);
        $row = $stmtRow->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmtTouch = $conn->prepare('UPDATE payment_idempotency SET last_seen_at = NOW() WHERE scope = ? AND idem_key = ?');
        $stmtTouch->execute([$scope, $idemKey]);

        return [
            'owner' => $isOwner,
            'scope' => $scope,
            'raw_key' => (string)$rawKey,
            'idem_key' => $idemKey,
            'status' => (string)($row['status'] ?? self::STATUS_PROCESSING),
            'response_code' => isset($row['response_code']) ? (int)$row['response_code'] : null,
            'response_message' => (string)($row['response_message'] ?? ''),
        ];
    }

    public static function markCompleted($conn, $scope, $rawKey, $responseCode = null, $responseMessage = '') {
        self::updateStatus($conn, $scope, $rawKey, self::STATUS_COMPLETED, $responseCode, $responseMessage);
    }

    public static function markFailed($conn, $scope, $rawKey, $responseCode = null, $responseMessage = '') {
        self::updateStatus($conn, $scope, $rawKey, self::STATUS_FAILED, $responseCode, $responseMessage);
    }

    private static function updateStatus($conn, $scope, $rawKey, $status, $responseCode, $responseMessage) {
        self::ensureTable($conn);

        $scope = trim((string)$scope);
        $idemKey = self::hashKey($scope, (string)$rawKey);

        $stmt = $conn->prepare('UPDATE payment_idempotency SET status = ?, response_code = ?, response_message = ?, updated_at = NOW(), last_seen_at = NOW() WHERE scope = ? AND idem_key = ?');
        $stmt->execute([
            (string)$status,
            $responseCode !== null ? (int)$responseCode : null,
            substr((string)$responseMessage, 0, 255),
            $scope,
            $idemKey,
        ]);
    }

    private static function hashKey($scope, $rawKey) {
        return hash('sha256', (string)$scope . '|' . (string)$rawKey);
    }
}
