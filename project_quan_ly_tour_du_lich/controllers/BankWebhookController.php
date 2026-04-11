<?php

require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/PaymentIdempotency.php';
require_once __DIR__ . '/../services/PaymentFinanceService.php';

class BankWebhookController {
    public static function receive($conn) {
        self::ensureUnmatchedWebhookTable($conn);
        PaymentIdempotency::ensureTable($conn);

        $raw = (string)file_get_contents('php://input');

        // Log trước khi kiểm tra auth để dễ debug khi SePay gửi sai token
        $authHeader   = trim((string)($_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
        $apikeyHeader = trim((string)($_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? $_SERVER['HTTP_APIKEY'] ?? ''));
        self::logRaw('RECEIVED',
            'method=' . ($_SERVER['REQUEST_METHOD'] ?? '') .
            ' auth_provided=' . ($authHeader !== '' || $apikeyHeader !== '' ? 'yes' : 'no') .
            ' body_len=' . strlen($raw)
        );

        if (!BANK_WEBHOOK_ENABLED) {
            self::logRaw('DISABLED');
            self::jsonResponse(503, ['ok' => false, 'message' => 'Bank webhook is disabled']);
            return;
        }

        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            self::jsonResponse(405, ['ok' => false, 'message' => 'Method not allowed']);
            return;
        }

        if (!self::isWebhookAuthorized()) {
            self::logRaw('AUTH_FAIL',
                'auth=' . substr($authHeader, 0, 60) .
                ' apikey=' . substr($apikeyHeader, 0, 60)
            );
            self::jsonResponse(401, ['ok' => false, 'message' => 'Unauthorized webhook']);
            return;
        }

        self::logRaw('AUTH_OK', 'payload=' . substr($raw, 0, 300));

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            self::jsonResponse(400, ['ok' => false, 'message' => 'Invalid JSON payload']);
            return;
        }

        $amount = self::extractAmount($payload);
        $description = self::extractDescription($payload);
        $gatewayRef = self::extractGatewayRef($payload);
        $transferType = self::extractTransferType($payload);
        $idempotencyRawKey = implode('|', [
            'provider=' . (string)BANK_WEBHOOK_PROVIDER,
            'gateway_ref=' . (string)$gatewayRef,
            'amount=' . (string)$amount,
            'desc=' . substr((string)$description, 0, 120),
        ]);
        $idempotencyClaim = PaymentIdempotency::claim($conn, 'bank_webhook_receive', $idempotencyRawKey, json_encode($payload, JSON_UNESCAPED_UNICODE));
        if (!$idempotencyClaim['owner']) {
            $statusCode = ($idempotencyClaim['status'] === PaymentIdempotency::STATUS_COMPLETED) ? 200 : 202;
            self::jsonResponse($statusCode, [
                'ok' => true,
                'matched' => false,
                'duplicate' => true,
                'message' => $idempotencyClaim['status'] === PaymentIdempotency::STATUS_COMPLETED
                    ? 'Duplicate webhook ignored'
                    : 'Webhook is being processed'
            ]);
            return;
        }

        if ($transferType !== '' && strtolower($transferType) !== 'in') {
            self::recordUnmatchedEvent('non_inbound_transfer', $description);
            self::completeWebhookIdempotency($conn, $idempotencyRawKey, 202, 'Ignored non-inbound transfer');
            self::jsonResponse(202, [
                'ok' => true,
                'matched' => false,
                'message' => 'Ignored non-inbound transfer type'
            ]);
            return;
        }

        if ($amount <= 0) {
            self::completeWebhookIdempotency($conn, $idempotencyRawKey, 422, 'Missing transfer amount');
            self::jsonResponse(422, ['ok' => false, 'message' => 'Missing transfer amount']);
            return;
        }

        $bookingCandidates = self::extractBookingCandidatesFromDescription($description);
        if (empty($bookingCandidates)) {
            self::recordUnmatchedEvent('missing_booking_token', $description);
            self::completeWebhookIdempotency($conn, $idempotencyRawKey, 202, 'Missing booking token');
            self::jsonResponse(202, [
                'ok' => true,
                'matched' => false,
                'message' => 'No strict BOOKING_{id}_{token} found in transfer description'
            ]);
            return;
        }

        $payment = null;
        $bookingId = 0;
        $triedCandidates = [];
        foreach ($bookingCandidates as $candidateId) {
            $triedCandidates[] = (int)$candidateId;
            $tryPayment = self::findLatestPendingPaymentByBooking($conn, (int)$candidateId);
            if ($tryPayment) {
                $payment = $tryPayment;
                $bookingId = (int)$candidateId;
                break;
            }
        }

        if (!$payment) {
            $queued = self::queueUnmatchedWebhook($conn, [
                'provider' => (string)BANK_WEBHOOK_PROVIDER,
                'gateway_ref' => (string)$gatewayRef,
                'amount' => (float)$amount,
                'description' => (string)$description,
                'booking_candidates' => $bookingCandidates,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
            self::recordUnmatchedEvent('no_pending_payment', $description);
            self::completeWebhookIdempotency($conn, $idempotencyRawKey, 200, 'No pending payment');

            self::jsonResponse(200, [
                'ok' => true,
                'matched' => false,
                'message' => 'No pending payment for this booking',
                'booking_candidates' => $bookingCandidates,
                'tried_candidates' => $triedCandidates,
                'queued_unmatched' => $queued
            ]);
            return;
        }

        $expectedAmount = (float)($payment['amount'] ?? 0);
        if ($amount + 1 < $expectedAmount) {
            self::createPaymentLog($conn, (int)$payment['payment_id'], 'WEBHOOK_UNDERPAID', 'expected=' . $expectedAmount . '; received=' . $amount . '; desc=' . $description);
            self::completeWebhookIdempotency($conn, $idempotencyRawKey, 202, 'Underpaid transfer');
            self::jsonResponse(202, [
                'ok' => true,
                'matched' => true,
                'confirmed' => false,
                'message' => 'Underpaid transfer',
                'booking_id' => $bookingId,
                'booking_candidates' => $bookingCandidates,
                'tried_candidates' => $triedCandidates,
                'matched_candidate' => $bookingId
            ]);
            return;
        }

        if (!BANK_WEBHOOK_ALLOW_OVERPAY && abs($amount - $expectedAmount) > 1) {
            self::createPaymentLog($conn, (int)$payment['payment_id'], 'WEBHOOK_AMOUNT_MISMATCH', 'expected=' . $expectedAmount . '; received=' . $amount . '; desc=' . $description);
            self::completeWebhookIdempotency($conn, $idempotencyRawKey, 202, 'Amount mismatch');
            self::jsonResponse(202, [
                'ok' => true,
                'matched' => true,
                'confirmed' => false,
                'message' => 'Amount mismatch',
                'booking_id' => $bookingId,
                'booking_candidates' => $bookingCandidates,
                'tried_candidates' => $triedCandidates,
                'matched_candidate' => $bookingId
            ]);
            return;
        }

        try {
            $conn->beginTransaction();
            self::lockBookingRow($conn, $bookingId);
            Payment::findForUpdate($conn, (int)$payment['payment_id']);

            $note = 'Webhook auto confirm | provider=' . BANK_WEBHOOK_PROVIDER;
            if ($gatewayRef !== '') {
                $note .= ' | ref=' . $gatewayRef;
            }
            if ($description !== '') {
                $note .= ' | desc=' . substr($description, 0, 180);
            }

            $stmtPayment = $conn->prepare('UPDATE payments SET amount = ?, note = ? WHERE payment_id = ?');
            $stmtPayment->execute([$amount, $note, (int)$payment['payment_id']]);

            $transition = Payment::transitionStatus($conn, (int)$payment['payment_id'], Payment::STATUS_THANH_CONG, 'bank_webhook_auto_confirm', [
                'provider' => (string)BANK_WEBHOOK_PROVIDER,
                'gateway_ref' => (string)$gatewayRef,
            ]);
            if (!$transition['ok']) {
                throw new RuntimeException((string)$transition['message']);
            }

            self::createPaymentLog($conn, (int)$payment['payment_id'], 'WEBHOOK_CONFIRM', 'Auto confirmed by bank webhook. amount=' . $amount . ($gatewayRef !== '' ? '; ref=' . $gatewayRef : ''));

            $stmtBooking = $conn->prepare('UPDATE booking SET trang_thai = ? WHERE booking_id = ? AND trang_thai NOT IN (\'DaCoc\', \'HoanTat\')');
            $stmtBooking->execute(['DaCoc', $bookingId]);

            PaymentFinanceService::updateBookingPaymentStatusIfExists($conn, $bookingId, 'DaThanhToan');
            PaymentFinanceService::createThuTransactionIfMissing($conn, [
                'booking_id' => $bookingId,
                'tour_id' => (int)($payment['tour_id'] ?? 0),
                'khach_hang_id' => (int)($payment['khach_hang_id'] ?? 0),
                'amount' => (float)$amount,
                'description' => 'Bank webhook auto-confirm booking #' . $bookingId,
                'payment_date' => date('Y-m-d'),
            ]);

            $conn->commit();
            self::completeWebhookIdempotency($conn, $idempotencyRawKey, 200, 'Webhook processed');

            self::jsonResponse(200, [
                'ok' => true,
                'matched' => true,
                'confirmed' => true,
                'booking_id' => $bookingId,
                'payment_id' => (int)$payment['payment_id'],
                'booking_candidates' => $bookingCandidates,
                'tried_candidates' => $triedCandidates,
                'matched_candidate' => $bookingId
            ]);
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            PaymentIdempotency::markFailed($conn, 'bank_webhook_receive', $idempotencyRawKey, 500, $e->getMessage());
            self::jsonResponse(500, ['ok' => false, 'message' => 'Webhook processing failed', 'error' => $e->getMessage()]);
        }
    }

    private static function completeWebhookIdempotency($conn, $rawKey, $responseCode, $message) {
        PaymentIdempotency::markCompleted($conn, 'bank_webhook_receive', (string)$rawKey, (int)$responseCode, (string)$message);
    }

    private static function lockBookingRow($conn, $bookingId) {
        $stmt = $conn->prepare('SELECT booking_id FROM booking WHERE booking_id = ? FOR UPDATE');
        $stmt->execute([(int)$bookingId]);
    }

    private static function isWebhookAuthorized() {
        $secretRaw = trim((string)BANK_WEBHOOK_SECRET);
        if ($secretRaw === '') {
            return false;
        }

        $secrets = array_values(array_filter(array_map('trim', explode(',', $secretRaw)), function ($v) {
            return $v !== '';
        }));
        if (empty($secrets)) {
            return false;
        }

        $matchesSecret = static function ($candidate) use ($secrets) {
            $candidate = trim((string)$candidate);
            if ($candidate === '') {
                return false;
            }

            foreach ($secrets as $secret) {
                if (hash_equals((string)$secret, $candidate)) {
                    return true;
                }
            }

            return false;
        };

        $headers = function_exists('getallheaders') ? (array)getallheaders() : [];

        $headerSecret = trim((string)($_SERVER['HTTP_X_WEBHOOK_SECRET'] ?? ''));
        if ($headerSecret === '' && isset($headers['X-Webhook-Secret'])) {
            $headerSecret = trim((string)$headers['X-Webhook-Secret']);
        }
        if ($matchesSecret($headerSecret)) {
            return true;
        }

        $authCandidates = [
            (string)($_SERVER['HTTP_AUTHORIZATION'] ?? ''),
            (string)($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''),
            (string)($_SERVER['Authorization'] ?? ''),
            (string)($headers['Authorization'] ?? ''),
            (string)($_SERVER['HTTP_APIKEY'] ?? ''),
            (string)($headers['Apikey'] ?? ''),
            (string)($headers['X-Api-Key'] ?? '')
        ];

        foreach ($authCandidates as $authRaw) {
            $auth = trim((string)$authRaw);
            if ($auth === '') {
                continue;
            }

            if ($matchesSecret($auth)) {
                return true;
            }

            if (stripos($auth, 'Bearer ') === 0) {
                $token = trim(substr($auth, 7));
                if ($matchesSecret($token)) {
                    return true;
                }
            }

            if (stripos($auth, 'Apikey ') === 0) {
                $token = trim(substr($auth, 7));
                if ($matchesSecret($token)) {
                    return true;
                }
            }
        }

        // Fallback: cho phep truyen secret qua query khi he thong gui webhook khong set header Authorization.
        $querySecret = trim((string)($_GET['webhook_secret'] ?? ''));
        if ($matchesSecret($querySecret)) {
            return true;
        }

        return false;
    }

    private static function extractAmount(array $payload) {
        $candidates = [
            self::dig($payload, 'transferAmount'),      // SePay
            self::dig($payload, 'amount'),
            self::dig($payload, 'accumulated'),         // SePay fallback
            self::dig($payload, 'data.amount'),
            self::dig($payload, 'data.transferAmount'),
            self::dig($payload, 'transaction.amount'),
            self::dig($payload, 'transaction.transferAmount')
        ];

        foreach ($candidates as $val) {
            if ($val !== null && $val !== '') {
                return (float)str_replace(',', '', (string)$val);
            }
        }
        return 0.0;
    }

    private static function extractDescription(array $payload) {
        $candidates = [
            self::dig($payload, 'content'),              // SePay (ưu tiên)
            self::dig($payload, 'transaction_content'),  // SePay
            self::dig($payload, 'description'),
            self::dig($payload, 'data.content'),
            self::dig($payload, 'data.description'),
            self::dig($payload, 'transaction.description'),
            self::dig($payload, 'transaction.content')
        ];

        foreach ($candidates as $val) {
            if (is_string($val) && trim($val) !== '') {
                return trim($val);
            }
        }
        return '';
    }

    private static function extractGatewayRef(array $payload) {
        $candidates = [
            self::dig($payload, 'referenceCode'),        // SePay: mã tham chiếu ngân hàng
            self::dig($payload, 'id'),                   // SePay: transaction ID
            self::dig($payload, 'transactionId'),
            self::dig($payload, 'transactionID'),
            self::dig($payload, 'reference'),
            self::dig($payload, 'data.id'),
            self::dig($payload, 'data.transactionId')
        ];

        foreach ($candidates as $val) {
            if ($val !== null && $val !== '') {
                return trim((string)$val);
            }
        }
        return '';
    }

    private static function extractTransferType(array $payload) {
        $candidates = [
            self::dig($payload, 'transferType'),
            self::dig($payload, 'type'),
            self::dig($payload, 'data.transferType'),
            self::dig($payload, 'transaction.transferType')
        ];

        foreach ($candidates as $val) {
            if ($val !== null && trim((string)$val) !== '') {
                return trim((string)$val);
            }
        }

        // SePay luôn gắn transferType; nếu thiếu thì coi là inbound để không bị bỏ qua
        return '';
    }

    private static function extractBookingCandidatesFromDescription($description) {
        $text = strtoupper((string)$description);
        if ($text === '') {
            return [];
        }

        $candidates = [];

        $appendCandidate = static function ($value) use (&$candidates) {
            $id = (int)$value;
            if ($id <= 0) {
                return;
            }
            if (!in_array($id, $candidates, true)) {
                $candidates[] = $id;
            }
        };

        // Chi chap nhan dinh dang day du: BOOKING_{id}_{token}
        // Vi du: BOOKING_299_0934567890
        if (preg_match_all('/BOOKING[_\-\s#:]*([0-9]{1,10})[_\-\s]+([A-Z0-9]{4,32})/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $appendCandidate($m[1]);
            }
        }

        return $candidates;
    }

    private static function findLatestPendingPaymentByBooking($conn, $bookingId) {
        $stmt = $conn->prepare('SELECT p.payment_id, p.amount, p.booking_id, b.tour_id, b.khach_hang_id FROM payments p INNER JOIN booking b ON b.booking_id = p.booking_id WHERE p.booking_id = ? AND p.status IN (?, ?) ORDER BY p.payment_id DESC LIMIT 1');
        $stmt->execute([(int)$bookingId, Payment::STATUS_DANG_XU_LY, Payment::STATUS_TAO_MOI]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function tryConsumeQueuedWebhookForBooking($conn, $bookingId) {
        self::ensureUnmatchedWebhookTable($conn);

        $bookingId = (int)$bookingId;
        if ($bookingId <= 0) {
            return ['confirmed' => false, 'reason' => 'invalid_booking'];
        }

        $payment = self::findLatestPendingPaymentByBooking($conn, $bookingId);
        if (!$payment) {
            return ['confirmed' => false, 'reason' => 'no_pending_payment'];
        }

        $rows = [];
        try {
            $stmt = $conn->query("SELECT queue_id, gateway_ref, amount, description, booking_candidates, received_at
                                 FROM bank_webhook_unmatched
                                 WHERE processed = 0
                                 ORDER BY queue_id DESC
                                 LIMIT 100");
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            return ['confirmed' => false, 'reason' => 'queue_read_error'];
        }

        if (empty($rows)) {
            return ['confirmed' => false, 'reason' => 'queue_empty'];
        }

        $expectedAmount = (float)($payment['amount'] ?? 0);
        foreach ($rows as $row) {
            $candidates = [];
            if (!empty($row['booking_candidates'])) {
                $decoded = json_decode((string)$row['booking_candidates'], true);
                if (is_array($decoded)) {
                    foreach ($decoded as $val) {
                        $id = (int)$val;
                        if ($id > 0 && !in_array($id, $candidates, true)) {
                            $candidates[] = $id;
                        }
                    }
                }
            }
            if (empty($candidates)) {
                $candidates = self::extractBookingCandidatesFromDescription((string)($row['description'] ?? ''));
            }

            if (!in_array($bookingId, $candidates, true)) {
                continue;
            }

            $amount = (float)($row['amount'] ?? 0);
            if ($amount + 1 < $expectedAmount) {
                self::markQueuedWebhookProcessed($conn, (int)$row['queue_id'], 'skip_underpaid_for_booking_' . $bookingId);
                continue;
            }

            if (!BANK_WEBHOOK_ALLOW_OVERPAY && abs($amount - $expectedAmount) > 1) {
                self::markQueuedWebhookProcessed($conn, (int)$row['queue_id'], 'skip_amount_mismatch_for_booking_' . $bookingId);
                continue;
            }

            try {
                $conn->beginTransaction();

                $note = 'Webhook auto confirm | provider=' . BANK_WEBHOOK_PROVIDER;
                if (!empty($row['gateway_ref'])) {
                    $note .= ' | ref=' . (string)$row['gateway_ref'];
                }
                if (!empty($row['description'])) {
                    $note .= ' | desc=' . substr((string)$row['description'], 0, 180);
                }

                $stmtPayment = $conn->prepare('UPDATE payments SET amount = ?, note = ? WHERE payment_id = ?');
                $stmtPayment->execute([$amount, $note, (int)$payment['payment_id']]);

                $transition = Payment::transitionStatus($conn, (int)$payment['payment_id'], Payment::STATUS_THANH_CONG, 'bank_webhook_queue_consume', [
                    'provider' => (string)BANK_WEBHOOK_PROVIDER,
                    'queue_id' => (int)$row['queue_id'],
                ]);
                if (!$transition['ok']) {
                    throw new RuntimeException((string)$transition['message']);
                }

                self::createPaymentLog($conn, (int)$payment['payment_id'], 'WEBHOOK_CONFIRM', 'Auto confirmed from queued unmatched webhook. amount=' . $amount . (!empty($row['gateway_ref']) ? '; ref=' . (string)$row['gateway_ref'] : ''));

                $stmtBooking = $conn->prepare('UPDATE booking SET trang_thai = ? WHERE booking_id = ? AND trang_thai NOT IN (\'DaCoc\', \'HoanTat\')');
                $stmtBooking->execute(['DaCoc', $bookingId]);
                PaymentFinanceService::updateBookingPaymentStatusIfExists($conn, $bookingId, 'DaThanhToan');
                PaymentFinanceService::createThuTransactionIfMissing($conn, [
                    'booking_id' => $bookingId,
                    'tour_id' => (int)($payment['tour_id'] ?? 0),
                    'khach_hang_id' => (int)($payment['khach_hang_id'] ?? 0),
                    'amount' => (float)$amount,
                    'description' => 'Bank webhook auto-confirm booking #' . $bookingId,
                    'payment_date' => date('Y-m-d'),
                ]);

                self::markQueuedWebhookProcessed($conn, (int)$row['queue_id'], 'consumed_for_booking_' . $bookingId);

                $conn->commit();
                return [
                    'confirmed' => true,
                    'booking_id' => $bookingId,
                    'payment_id' => (int)$payment['payment_id'],
                    'queue_id' => (int)$row['queue_id']
                ];
            } catch (Throwable $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                return ['confirmed' => false, 'reason' => 'consume_error', 'error' => $e->getMessage()];
            }
        }

        return ['confirmed' => false, 'reason' => 'no_queued_match_for_booking'];
    }

    private static function ensureUnmatchedWebhookTable($conn) {
        try {
            $conn->query('SELECT queue_id, provider, amount, processed FROM bank_webhook_unmatched LIMIT 1');
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Schema bank_webhook_unmatched is missing. Please run `php scripts/migrate.php up`. Root cause: ' . $e->getMessage()
            );
        }
    }

    private static function queueUnmatchedWebhook($conn, array $data) {
        try {
            $stmt = $conn->prepare('INSERT INTO bank_webhook_unmatched (provider, gateway_ref, amount, description, booking_candidates, payload_json, received_at, processed) VALUES (?, ?, ?, ?, ?, ?, ?, 0)');
            $stmt->execute([
                (string)($data['provider'] ?? 'custom'),
                (string)($data['gateway_ref'] ?? ''),
                (float)($data['amount'] ?? 0),
                substr((string)($data['description'] ?? ''), 0, 255),
                json_encode(array_values(array_map('intval', (array)($data['booking_candidates'] ?? []))), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                (string)($data['payload_json'] ?? null),
                date('Y-m-d H:i:s')
            ]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function markQueuedWebhookProcessed($conn, $queueId, $note) {
        $queueId = (int)$queueId;
        if ($queueId <= 0) {
            return;
        }
        $stmt = $conn->prepare('UPDATE bank_webhook_unmatched SET processed = 1, processed_at = ?, process_note = ? WHERE queue_id = ?');
        $stmt->execute([date('Y-m-d H:i:s'), substr((string)$note, 0, 255), $queueId]);
    }

    private static function createPaymentLog($conn, $paymentId, $action, $note) {
        if ($paymentId <= 0) {
            return;
        }
        $stmt = $conn->prepare('INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, ?, ?, ?)');
        $stmt->execute([(int)$paymentId, (string)$action, date('Y-m-d H:i:s'), (string)$note]);
    }

    private static function dig(array $arr, $path) {
        $parts = explode('.', $path);
        $cur = $arr;
        foreach ($parts as $p) {
            if (!is_array($cur) || !array_key_exists($p, $cur)) {
                return null;
            }
            $cur = $cur[$p];
        }
        return $cur;
    }

    private static function logRaw($stage, $extra = '') {
        $logFile = __DIR__ . '/../storage/bank_webhook_raw.log';
        @file_put_contents($logFile,
            date('c') . ' [' . $stage . '] ' . $extra . PHP_EOL,
            FILE_APPEND
        );
    }

    private static function recordUnmatchedEvent($reason, $description = '') {
        $now = time();
        $windowSeconds = 600;
        $threshold = 5;

        $cacheDir = __DIR__ . '/../storage/cache';
        $cacheFile = $cacheDir . '/bank_webhook_unmatched_monitor.json';
        $alertFile = __DIR__ . '/../storage/bank_webhook_alert.log';

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        $state = [
            'events' => [],
            'last_alert' => 0,
        ];

        if (is_file($cacheFile)) {
            $raw = @file_get_contents($cacheFile);
            $decoded = $raw ? json_decode((string)$raw, true) : null;
            if (is_array($decoded)) {
                $state = array_merge($state, $decoded);
            }
        }

        $events = [];
        foreach ((array)($state['events'] ?? []) as $ts) {
            $eventTs = (int)$ts;
            if ($eventTs > 0 && ($now - $eventTs) <= $windowSeconds) {
                $events[] = $eventTs;
            }
        }
        $events[] = $now;

        $state['events'] = $events;
        $eventCount = count($events);

        $shortDesc = substr(trim((string)$description), 0, 160);
        self::logRaw('UNMATCHED', 'reason=' . $reason . '; count_10m=' . $eventCount . '; desc=' . $shortDesc);

        $lastAlert = (int)($state['last_alert'] ?? 0);
        if ($eventCount >= $threshold && ($now - $lastAlert) >= $windowSeconds) {
            $state['last_alert'] = $now;
            @file_put_contents(
                $alertFile,
                date('c') . ' ALERT unmatched_webhook_spike count_10m=' . $eventCount . '; reason=' . $reason . '; desc=' . $shortDesc . PHP_EOL,
                FILE_APPEND
            );
            self::logRaw('UNMATCHED_SPIKE', 'count_10m=' . $eventCount . '; reason=' . $reason);
        }

        @file_put_contents($cacheFile, json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function jsonResponse($statusCode, array $payload) {
        http_response_code((int)$statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
