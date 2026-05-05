<?php

require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/PaymentLog.php';
require_once __DIR__ . '/PaymentFinanceService.php';

class PaymentReconcileService {
    public static function buildReconcileRowsSummary(PDO $conn, array $filters): array {
        $reconcileRows = [];
        $summary = [
            'total' => 0,
            'ok' => 0,
            'warning' => 0,
            'thieu_thu' => 0,
            'thua_thu' => 0,
            'lech_tien' => 0,
        ];

        $sql = "SELECT p.payment_id, p.booking_id, p.amount, p.payment_method, p.payment_date, p.status,
                       b.trang_thai AS booking_status
                FROM payments p
                LEFT JOIN booking b ON b.booking_id = p.booking_id
                WHERE 1=1";

        $params = [];
        if (($filters['from_date'] ?? '') !== '') {
            $sql .= " AND p.payment_date >= ?";
            $params[] = $filters['from_date'] . ' 00:00:00';
        }
        if (($filters['to_date'] ?? '') !== '') {
            $sql .= " AND p.payment_date <= ?";
            $params[] = $filters['to_date'] . ' 23:59:59';
        }
        if (in_array((string)($filters['payment_status'] ?? ''), Payment::getStateList(), true)) {
            $sql .= " AND p.status = ?";
            $params[] = (string)$filters['payment_status'];
        }

        $sql .= " ORDER BY p.payment_date DESC, p.payment_id DESC LIMIT 500";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $financeSummaryByBooking = self::getFinanceThuSummaryByBookingIds($conn, array_column($rows, 'booking_id'));

        foreach ($rows as $row) {
            $issues = [];
            $paymentAmount = (float)($row['amount'] ?? 0);
            $bookingId = (int)($row['booking_id'] ?? 0);
            $financeSummary = $financeSummaryByBooking[$bookingId] ?? ['total_thu' => 0.0, 'thu_count' => 0];
            $financeTotal = (float)($financeSummary['total_thu'] ?? 0);
            $financeThuCount = (int)($financeSummary['thu_count'] ?? 0);
            $isSuccess = in_array(($row['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true);
            $isFailed = (($row['status'] ?? '') === 'ThatBai');
            $hasThu = ($financeThuCount > 0) && ($financeTotal > 0);

            $row['finance_total'] = $financeTotal;
            $row['finance_thu_count'] = $financeThuCount;

            if ($isSuccess && !$hasThu) {
                $issues[] = 'ThanhCong nhung chua co giao dich thu tai chinh';
                $summary['thieu_thu']++;
            }

            if ($isFailed && $hasThu) {
                $issues[] = 'ThatBai nhung da ghi nhan giao dich thu';
                $summary['thua_thu']++;
            }

            if ($isSuccess && $hasThu && abs($financeTotal - $paymentAmount) > 1) {
                $issues[] = 'Lech so tien giua payments va giao_dich_tai_chinh';
                $summary['lech_tien']++;
            }

            $row['issues'] = $issues;
            $row['reconcile_state'] = empty($issues) ? 'OK' : 'WARNING';
            $row['can_repair_missing_finance'] = ($isSuccess && !$hasThu);

            if (($filters['reconcile_state'] ?? '') !== '' && in_array((string)$filters['reconcile_state'], ['OK', 'WARNING'], true)) {
                if ($row['reconcile_state'] !== $filters['reconcile_state']) {
                    continue;
                }
            }

            $reconcileRows[] = $row;
        }

        $summary['total'] = count($reconcileRows);
        foreach ($reconcileRows as $row) {
            if (($row['reconcile_state'] ?? 'OK') === 'OK') {
                $summary['ok']++;
            } else {
                $summary['warning']++;
            }
        }

        return [
            'rows' => $reconcileRows,
            'summary' => $summary,
        ];
    }

    public static function runAutoReconcileTick(PDO $conn): void {
        $now = time();
        $cacheDir = __DIR__ . '/../storage/cache';
        $cacheFile = $cacheDir . '/auto_reconcile_payments.json';
        $intervalSeconds = 600;

        try {
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0750, true);
            }
            if (is_file($cacheFile)) {
                $raw = @file_get_contents($cacheFile);
                $data = $raw ? json_decode($raw, true) : null;
                $lastRun = (int)($data['last_run'] ?? 0);
                if ($lastRun > 0 && ($now - $lastRun) < $intervalSeconds) {
                    return;
                }
            }

            @file_put_contents($cacheFile, json_encode(['last_run' => $now], JSON_UNESCAPED_UNICODE));

            $sql = "SELECT p.payment_id, p.booking_id, p.amount, p.status,
                           p.payment_date
                    FROM payments p
                    WHERE p.payment_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                    ORDER BY p.payment_id DESC
                    LIMIT 1000";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $financeSummaryByBooking = self::getFinanceThuSummaryByBookingIds($conn, array_column($rows, 'booking_id'));

            $warned = 0;
            $checked = 0;

            foreach ($rows as $row) {
                $checked++;
                $issues = [];
                $paymentId = (int)($row['payment_id'] ?? 0);
                $paymentAmount = (float)($row['amount'] ?? 0);
                $bookingId = (int)($row['booking_id'] ?? 0);
                $financeSummary = $financeSummaryByBooking[$bookingId] ?? ['total_thu' => 0.0, 'thu_count' => 0];
                $financeTotal = (float)($financeSummary['total_thu'] ?? 0);
                $financeThuCount = (int)($financeSummary['thu_count'] ?? 0);
                $isSuccess = in_array(($row['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true);
                $isFailed = (($row['status'] ?? '') === 'ThatBai');
                $hasThu = ($financeThuCount > 0) && ($financeTotal > 0);

                if ($isSuccess && !$hasThu) {
                    $issues[] = 'ThanhCong nhung chua co giao dich thu tai chinh';
                }
                if ($isFailed && $hasThu) {
                    $issues[] = 'ThatBai nhung da ghi nhan giao dich thu';
                }
                if ($isSuccess && $hasThu && abs($financeTotal - $paymentAmount) > 1) {
                    $issues[] = 'Lech so tien giua payments va giao_dich_tai_chinh';
                }

                if (empty($issues) || $paymentId <= 0) {
                    if ($isSuccess) {
                        Payment::transitionStatus($conn, $paymentId, Payment::STATUS_DA_DOI_SOAT, 'auto_reconcile_ok', [
                            'source' => 'runAutoReconcileTick',
                        ]);
                    }
                    continue;
                }

                if (($row['status'] ?? '') === Payment::STATUS_DANG_XU_LY) {
                    $paymentDateTs = !empty($row['payment_date']) ? strtotime((string)$row['payment_date']) : 0;
                    if ($paymentDateTs > 0 && (time() - $paymentDateTs) > 86400) {
                        Payment::transitionStatus($conn, $paymentId, Payment::STATUS_HET_HAN, 'auto_timeout_dang_xu_ly', [
                            'payment_date' => (string)$row['payment_date'],
                        ]);
                    }
                }

                $note = 'AUTO_RECONCILE: ' . implode(' | ', $issues);
                $stmtLast = $conn->prepare("SELECT note FROM payment_logs WHERE payment_id = ? AND action = 'AUTO_RECONCILE_WARN' ORDER BY log_id DESC LIMIT 1");
                $stmtLast->execute([$paymentId]);
                $lastNote = (string)$stmtLast->fetchColumn();

                if ($lastNote === $note) {
                    continue;
                }

                PaymentLog::create($conn, [
                    'payment_id' => $paymentId,
                    'action' => 'AUTO_RECONCILE_WARN',
                    'log_time' => date('Y-m-d H:i:s'),
                    'note' => $note,
                ]);
                $warned++;
            }

            @file_put_contents($cacheFile, json_encode([
                'last_run' => $now,
                'checked' => $checked,
                'warned' => $warned,
            ], JSON_UNESCAPED_UNICODE));

            self::refreshDailyMismatchReportCache($conn);
        } catch (Throwable $e) {
            // Never break payment pages because of background reconcile tick.
        }
    }

    public static function repairMissingFinanceTransaction(PDO $conn, int $paymentId, string $repairReason = ''): array {
        if ($paymentId <= 0) {
            return ['ok' => false, 'message' => 'Payment ID khong hop le.'];
        }

        $repairReason = trim((string)$repairReason);
        if (mb_strlen($repairReason) < 10) {
            return ['ok' => false, 'message' => 'Vui long nhap ly do sua loi toi thieu 10 ky tu.'];
        }

        try {
            self::ensureReconcileAuditTable($conn);
            $stmt = $conn->prepare("SELECT p.payment_id, p.booking_id, p.amount, p.payment_date, p.status,
                                           b.tour_id, b.khach_hang_id
                                    FROM payments p
                                    INNER JOIN booking b ON b.booking_id = p.booking_id
                                    WHERE p.payment_id = ?
                                    LIMIT 1");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                return ['ok' => false, 'message' => 'Khong tim thay thanh toan can sua.'];
            }

            if (($payment['status'] ?? '') !== 'ThanhCong') {
                return ['ok' => false, 'message' => 'Chi duoc sua cho giao dich ThanhCong.'];
            }

            $existsCount = PaymentFinanceService::existsThuTransaction($conn, (int)$payment['booking_id']) ? 1 : 0;
            if ($existsCount > 0) {
                return ['ok' => false, 'message' => 'Booking nay da co giao dich thu tai chinh.'];
            }

            $conn->beginTransaction();

            $adminId = (int)($_SESSION['user_id'] ?? 0);
            $beforeSnapshot = [
                'payment_id' => (int)$payment['payment_id'],
                'booking_id' => (int)$payment['booking_id'],
                'payment_status' => (string)$payment['status'],
                'payment_amount' => (float)$payment['amount'],
                'finance_thu_count' => $existsCount,
            ];

            PaymentFinanceService::createThuTransaction($conn, [
                'booking_id' => (int)$payment['booking_id'],
                'tour_id' => (int)($payment['tour_id'] ?? 0),
                'khach_hang_id' => (int)($payment['khach_hang_id'] ?? 0),
                'amount' => (float)($payment['amount'] ?? 0),
                'description' => 'Reconcile auto-fix tu payment #' . (int)$payment['payment_id'],
                'payment_date' => !empty($payment['payment_date']) ? date('Y-m-d', strtotime((string)$payment['payment_date'])) : date('Y-m-d'),
            ]);

            PaymentLog::create($conn, [
                'payment_id' => (int)$payment['payment_id'],
                'action' => 'REPAIR_FINANCE',
                'log_time' => date('Y-m-d H:i:s'),
                'note' => 'Tao bu toan thu bo sung tu trang doi soat admin | reason=' . $repairReason . ' | admin_id=' . $adminId,
            ]);

            $afterSnapshot = [
                'payment_id' => (int)$payment['payment_id'],
                'booking_id' => (int)$payment['booking_id'],
                'finance_thu_count' => 1,
                'finance_amount' => (float)$payment['amount'],
            ];

            self::createReconcileAudit($conn, [
                'payment_id' => (int)$payment['payment_id'],
                'booking_id' => (int)$payment['booking_id'],
                'action' => 'repair_missing_finance',
                'reason' => $repairReason,
                'performed_by' => $adminId,
                'before_json' => json_encode($beforeSnapshot, JSON_UNESCAPED_UNICODE),
                'after_json' => json_encode($afterSnapshot, JSON_UNESCAPED_UNICODE),
            ]);

            $conn->commit();
            return ['ok' => true, 'message' => 'Da tao bu toan thu bo sung cho payment #' . (int)$payment['payment_id'] . '.'];
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ['ok' => false, 'message' => 'Khong the sua doi soat: ' . $e->getMessage()];
        }
    }

    public static function ensureReconcileAuditTable(PDO $conn): void {
        try {
            $conn->query('SELECT audit_id, payment_id, booking_id FROM payment_reconcile_audit LIMIT 1');
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Schema payment_reconcile_audit is missing. Please run `php scripts/migrate.php up`. Root cause: ' . $e->getMessage()
            );
        }
    }

    public static function refreshDailyMismatchReportCache(PDO $conn): array {
        $cacheDir = __DIR__ . '/../storage/cache';
        $cacheFile = $cacheDir . '/payment_mismatch_daily_report.json';

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0750, true);
        }

        $today = date('Y-m-d');
        $cached = [];
        if (is_file($cacheFile)) {
            $raw = @file_get_contents($cacheFile);
            $cached = $raw ? (json_decode($raw, true) ?: []) : [];
        }

        if (($cached['date'] ?? '') === $today && !empty($cached['report'])) {
            return $cached['report'];
        }

        $report = self::buildDailyMismatchReport($conn, $today);
        @file_put_contents($cacheFile, json_encode([
            'date' => $today,
            'generated_at' => date('c'),
            'report' => $report,
        ], JSON_UNESCAPED_UNICODE));

        return $report;
    }

    private static function createReconcileAudit(PDO $conn, array $data): void {
        $stmt = $conn->prepare('INSERT INTO payment_reconcile_audit (payment_id, booking_id, action, reason, performed_by, before_json, after_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)($data['payment_id'] ?? 0),
            (int)($data['booking_id'] ?? 0),
            (string)($data['action'] ?? 'unknown'),
            substr((string)($data['reason'] ?? ''), 0, 500),
            isset($data['performed_by']) ? (int)$data['performed_by'] : null,
            (string)($data['before_json'] ?? ''),
            (string)($data['after_json'] ?? ''),
            date('Y-m-d H:i:s'),
        ]);
    }

    private static function buildDailyMismatchReport(PDO $conn, string $dateYmd): array {
        $summary = [
            'date' => (string)$dateYmd,
            'total' => 0,
            'warning' => 0,
            'thieu_thu' => 0,
            'thua_thu' => 0,
            'lech_tien' => 0,
        ];

        $sql = "SELECT p.payment_id, p.booking_id, p.amount, p.status
                FROM payments p
                WHERE p.payment_date >= ?
                  AND p.payment_date <= ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([(string)$dateYmd . ' 00:00:00', (string)$dateYmd . ' 23:59:59']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $financeSummaryByBooking = self::getFinanceThuSummaryByBookingIds($conn, array_column($rows, 'booking_id'));

        foreach ($rows as $row) {
            $summary['total']++;
            $paymentAmount = (float)($row['amount'] ?? 0);
            $bookingId = (int)($row['booking_id'] ?? 0);
            $financeSummary = $financeSummaryByBooking[$bookingId] ?? ['total_thu' => 0.0, 'thu_count' => 0];
            $financeTotal = (float)($financeSummary['total_thu'] ?? 0);
            $financeThuCount = (int)($financeSummary['thu_count'] ?? 0);
            $isSuccess = in_array(($row['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true);
            $isFailed = (($row['status'] ?? '') === Payment::STATUS_THAT_BAI);
            $hasThu = ($financeThuCount > 0) && ($financeTotal > 0);

            $hasIssue = false;
            if ($isSuccess && !$hasThu) {
                $summary['thieu_thu']++;
                $hasIssue = true;
            }
            if ($isFailed && $hasThu) {
                $summary['thua_thu']++;
                $hasIssue = true;
            }
            if ($isSuccess && $hasThu && abs($financeTotal - $paymentAmount) > 1) {
                $summary['lech_tien']++;
                $hasIssue = true;
            }

            if ($hasIssue) {
                $summary['warning']++;
            }
        }

        return $summary;
    }

    private static function getFinanceThuSummaryByBookingIds(PDO $conn, array $bookingIds): array {
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
        $sql = "SELECT booking_id,
                       COALESCE(SUM(so_tien), 0) AS total_thu,
                       COUNT(*) AS thu_count
                FROM giao_dich_tai_chinh
                WHERE loai = 'Thu'
                  AND booking_id IN ($placeholders)
                GROUP BY booking_id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($idList);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [];
        foreach ($rows as $row) {
            $bookingId = (int)($row['booking_id'] ?? 0);
            if ($bookingId <= 0) {
                continue;
            }
            $summary[$bookingId] = [
                'total_thu' => (float)($row['total_thu'] ?? 0),
                'thu_count' => (int)($row['thu_count'] ?? 0),
            ];
        }

        return $summary;
    }
}
