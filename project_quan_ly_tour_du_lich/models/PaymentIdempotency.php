<?php

class PaymentIdempotency {
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public static function ensureTable($conn) {
        $conn->exec("CREATE TABLE IF NOT EXISTS payment_idempotency (
            id INT(11) NOT NULL AUTO_INCREMENT,
            scope VARCHAR(64) NOT NULL,
            idem_key CHAR(64) NOT NULL,
            status ENUM('processing','completed','failed') NOT NULL DEFAULT 'processing',
            response_code INT(11) DEFAULT NULL,
            response_message VARCHAR(255) DEFAULT NULL,
            payload_hash CHAR(64) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_scope_key (scope, idem_key),
            KEY idx_scope_status (scope, status),
            KEY idx_last_seen_at (last_seen_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
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
