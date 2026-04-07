<?php
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/PaymentLog.php';
class PaymentController {
    public static function index($conn) {
        Payment::ensureStateMachineSchema($conn);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId > 0) {
            try {
                $maxPaymentId = (int)$conn->query("SELECT COALESCE(MAX(payment_id), 0) FROM payments")->fetchColumn();
                $_SESSION['admin_notifications'] = saveAdminNotificationState($userId, [
                    'payments_last_seen_id' => $maxPaymentId,
                ], $conn);
            } catch (Throwable $e) {
                // Bỏ qua nếu không thể cập nhật trạng thái đã xem.
            }
        }

        self::runAutoReconcileTick($conn);
        $payments = Payment::all($conn);
        $statusSummary = self::buildStatusSummary($conn, $payments);
        include __DIR__ . '/../views/admin/payments/index.php';
    }

    private static function buildStatusSummary($conn, array $payments): array {
        $summary = [
            Payment::STATUS_TAO_MOI => 0,
            Payment::STATUS_DANG_XU_LY => 0,
            Payment::STATUS_THANH_CONG => 0,
            Payment::STATUS_THAT_BAI => 0,
            Payment::STATUS_HET_HAN => 0,
            Payment::STATUS_DA_DOI_SOAT => 0,
        ];

        try {
            $stmt = $conn->query('SELECT status, COUNT(*) AS total FROM payments GROUP BY status');
            $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($rows as $row) {
                $status = (string)($row['status'] ?? '');
                if (array_key_exists($status, $summary)) {
                    $summary[$status] = (int)($row['total'] ?? 0);
                }
            }
        } catch (Throwable $e) {
            foreach ($payments as $payment) {
                $status = (string)($payment['status'] ?? '');
                if (array_key_exists($status, $summary)) {
                    $summary[$status]++;
                }
            }
        }

        return $summary;
    }

    public static function reconcile($conn) {
        Payment::ensureStateMachineSchema($conn);
        self::ensureReconcileAuditTable($conn);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId > 0) {
            try {
                $maxPaymentId = (int)$conn->query("SELECT COALESCE(MAX(payment_id), 0) FROM payments")->fetchColumn();
                $_SESSION['admin_notifications'] = saveAdminNotificationState($userId, [
                    'payments_last_seen_id' => $maxPaymentId,
                ], $conn);
            } catch (Throwable $e) {
                // Bỏ qua nếu không thể cập nhật trạng thái đã xem.
            }
        }

        self::runAutoReconcileTick($conn);
        $filters = [
            'from_date' => validateDateYmd(requestString('from_date', '', 'GET')) ?? '',
            'to_date' => validateDateYmd(requestString('to_date', '', 'GET')) ?? '',
            'payment_status' => requestString('payment_status', '', 'GET'),
            'reconcile_state' => requestString('reconcile_state', '', 'GET')
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repair_payment_id'])) {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'payment_reconcile_repair')) {
                $_SESSION['error'] = 'Yeu cau khong hop le (CSRF).';
                header('Location: index.php?act=admin/paymentReconcile');
                exit;
            }

            $paymentId = requestId('repair_payment_id', 0, 'POST') ?? 0;
            $repairReason = requestString('repair_reason', '', 'POST');
            $repairResult = self::repairMissingFinanceTransaction($conn, $paymentId, $repairReason);
            $_SESSION[$repairResult['ok'] ? 'success' : 'error'] = $repairResult['message'];

            $query = [
                'act' => 'admin/paymentReconcile',
                'from_date' => $filters['from_date'],
                'to_date' => $filters['to_date'],
                'payment_status' => $filters['payment_status'],
                'reconcile_state' => $filters['reconcile_state']
            ];
            header('Location: index.php?' . http_build_query(array_filter($query, function ($v) {
                return $v !== '';
            })));
            exit;
        }

        $dailyMismatchReport = self::refreshDailyMismatchReportCache($conn);

        $reconcileRows = [];
        $summary = [
            'total' => 0,
            'ok' => 0,
            'warning' => 0,
            'thieu_thu' => 0,
            'thua_thu' => 0,
            'lech_tien' => 0
        ];

        $sql = "SELECT p.payment_id, p.booking_id, p.amount, p.payment_method, p.payment_date, p.status,
                       b.trang_thai AS booking_status,
                       COALESCE(fin.total_thu, 0) AS finance_total,
                       COALESCE(fin.thu_count, 0) AS finance_thu_count
                FROM payments p
                LEFT JOIN booking b ON b.booking_id = p.booking_id
                LEFT JOIN (
                    SELECT booking_id,
                           SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END) AS total_thu,
                           SUM(CASE WHEN loai = 'Thu' THEN 1 ELSE 0 END) AS thu_count
                    FROM giao_dich_tai_chinh
                    GROUP BY booking_id
                ) fin ON fin.booking_id = p.booking_id
                WHERE 1=1";

        $params = [];
        if ($filters['from_date'] !== '') {
            $sql .= " AND DATE(p.payment_date) >= ?";
            $params[] = $filters['from_date'];
        }
        if ($filters['to_date'] !== '') {
            $sql .= " AND DATE(p.payment_date) <= ?";
            $params[] = $filters['to_date'];
        }
        if (in_array($filters['payment_status'], Payment::getStateList(), true)) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['payment_status'];
        }

        $sql .= " ORDER BY p.payment_date DESC, p.payment_id DESC LIMIT 500";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $issues = [];
                $paymentAmount = (float)($row['amount'] ?? 0);
                $financeTotal = (float)($row['finance_total'] ?? 0);
                $isSuccess = in_array(($row['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true);
                $isFailed = (($row['status'] ?? '') === 'ThatBai');
                $hasThu = ((int)($row['finance_thu_count'] ?? 0) > 0) && ($financeTotal > 0);

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

                if ($filters['reconcile_state'] !== '' && in_array($filters['reconcile_state'], ['OK', 'WARNING'], true)) {
                    if ($row['reconcile_state'] !== $filters['reconcile_state']) {
                        continue;
                    }
                }

                $reconcileRows[] = $row;
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = 'Khong the doi soat thanh toan: ' . $e->getMessage();
        }

        $summary['total'] = count($reconcileRows);
        foreach ($reconcileRows as $row) {
            if (($row['reconcile_state'] ?? 'OK') === 'OK') {
                $summary['ok']++;
            } else {
                $summary['warning']++;
            }
        }

        include __DIR__ . '/../views/admin/payments/reconcile.php';
    }

    private static function runAutoReconcileTick($conn) {
        $cacheDir = __DIR__ . '/../storage/cache';
        $cacheFile = $cacheDir . '/auto_reconcile_payments.json';
        $intervalSeconds = 600;

        try {
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }

            $now = time();
            if (is_file($cacheFile)) {
                $raw = @file_get_contents($cacheFile);
                $data = $raw ? json_decode($raw, true) : null;
                $lastRun = (int)($data['last_run'] ?? 0);
                if ($lastRun > 0 && ($now - $lastRun) < $intervalSeconds) {
                    return;
                }
            }

            // Mark first to avoid concurrent requests running the same job repeatedly.
            @file_put_contents($cacheFile, json_encode(['last_run' => $now], JSON_UNESCAPED_UNICODE));

            $sql = "SELECT p.payment_id, p.booking_id, p.amount, p.status,
                          p.payment_date,
                           COALESCE(fin.total_thu, 0) AS finance_total,
                           COALESCE(fin.thu_count, 0) AS finance_thu_count
                    FROM payments p
                    LEFT JOIN (
                        SELECT booking_id,
                               SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END) AS total_thu,
                               SUM(CASE WHEN loai = 'Thu' THEN 1 ELSE 0 END) AS thu_count
                        FROM giao_dich_tai_chinh
                        GROUP BY booking_id
                    ) fin ON fin.booking_id = p.booking_id
                    WHERE p.payment_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                    ORDER BY p.payment_id DESC
                    LIMIT 1000";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $warned = 0;
            $checked = 0;

            foreach ($rows as $row) {
                $checked++;
                $issues = [];
                $paymentId = (int)($row['payment_id'] ?? 0);
                $paymentAmount = (float)($row['amount'] ?? 0);
                $financeTotal = (float)($row['finance_total'] ?? 0);
                $isSuccess = in_array(($row['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true);
                $isFailed = (($row['status'] ?? '') === 'ThatBai');
                $hasThu = ((int)($row['finance_thu_count'] ?? 0) > 0) && ($financeTotal > 0);

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
                    'note' => $note
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

    private static function repairMissingFinanceTransaction($conn, $paymentId, $repairReason = '') {
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

            $stmtExists = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu'");
            $stmtExists->execute([(int)$payment['booking_id']]);
            $existsCount = (int)$stmtExists->fetchColumn();
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

            $stmtInsert = $conn->prepare("INSERT INTO giao_dich_tai_chinh
                (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                (int)$payment['booking_id'],
                (int)($payment['tour_id'] ?? 0),
                (int)($payment['khach_hang_id'] ?? 0),
                'Thu',
                (float)($payment['amount'] ?? 0),
                'Reconcile auto-fix tu payment #' . (int)$payment['payment_id'],
                !empty($payment['payment_date']) ? date('Y-m-d', strtotime((string)$payment['payment_date'])) : date('Y-m-d')
            ]);

            PaymentLog::create($conn, [
                'payment_id' => (int)$payment['payment_id'],
                'action' => 'REPAIR_FINANCE',
                'log_time' => date('Y-m-d H:i:s'),
                'note' => 'Tao bu toan thu bo sung tu trang doi soat admin | reason=' . $repairReason . ' | admin_id=' . $adminId
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

    private static function ensureReconcileAuditTable($conn) {
        $conn->exec("CREATE TABLE IF NOT EXISTS payment_reconcile_audit (
            audit_id INT(11) NOT NULL AUTO_INCREMENT,
            payment_id INT(11) NOT NULL,
            booking_id INT(11) NOT NULL,
            action VARCHAR(64) NOT NULL,
            reason VARCHAR(500) DEFAULT NULL,
            performed_by INT(11) DEFAULT NULL,
            before_json LONGTEXT DEFAULT NULL,
            after_json LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (audit_id),
            KEY idx_payment_id (payment_id),
            KEY idx_booking_id (booking_id),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private static function createReconcileAudit($conn, array $data) {
        $stmt = $conn->prepare("INSERT INTO payment_reconcile_audit (payment_id, booking_id, action, reason, performed_by, before_json, after_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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

    private static function refreshDailyMismatchReportCache($conn) {
        $cacheDir = __DIR__ . '/../storage/cache';
        $cacheFile = $cacheDir . '/payment_mismatch_daily_report.json';

        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
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

    private static function buildDailyMismatchReport($conn, $dateYmd) {
        $summary = [
            'date' => (string)$dateYmd,
            'total' => 0,
            'warning' => 0,
            'thieu_thu' => 0,
            'thua_thu' => 0,
            'lech_tien' => 0,
        ];

        $sql = "SELECT p.payment_id, p.amount, p.status,
                       COALESCE(fin.total_thu, 0) AS finance_total,
                       COALESCE(fin.thu_count, 0) AS finance_thu_count
                FROM payments p
                LEFT JOIN (
                    SELECT booking_id,
                           SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END) AS total_thu,
                           SUM(CASE WHEN loai = 'Thu' THEN 1 ELSE 0 END) AS thu_count
                    FROM giao_dich_tai_chinh
                    GROUP BY booking_id
                ) fin ON fin.booking_id = p.booking_id
                WHERE DATE(p.payment_date) = ?";

        $stmt = $conn->prepare($sql);
        $stmt->execute([(string)$dateYmd]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $summary['total']++;
            $paymentAmount = (float)($row['amount'] ?? 0);
            $financeTotal = (float)($row['finance_total'] ?? 0);
            $isSuccess = in_array(($row['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true);
            $isFailed = (($row['status'] ?? '') === Payment::STATUS_THAT_BAI);
            $hasThu = ((int)($row['finance_thu_count'] ?? 0) > 0) && ($financeTotal > 0);

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

    public static function show($conn, $id) {
        Payment::ensureStateMachineSchema($conn);
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId > 0) {
            try {
                $maxPaymentId = (int)$conn->query("SELECT COALESCE(MAX(payment_id), 0) FROM payments")->fetchColumn();
                $_SESSION['admin_notifications'] = saveAdminNotificationState($userId, [
                    'payments_last_seen_id' => $maxPaymentId,
                ], $conn);
            } catch (Throwable $e) {
                // Bỏ qua nếu không thể cập nhật trạng thái đã xem.
            }
        }

        $paymentId = validateId($id) ?? 0;
        $payment = Payment::find($conn, $paymentId);
        $logs = PaymentLog::all($conn, $paymentId);
        $paymentDetail = null;

        if ($payment) {
            try {
                $stmt = $conn->prepare("SELECT p.payment_id, p.booking_id, p.amount, p.payment_method, p.payment_date, p.status, p.note,
                                               b.tour_id, b.khach_hang_id, b.trang_thai AS booking_status,
                                               t.ten_tour,
                                               nd.ho_ten, nd.so_dien_thoai, nd.email
                                        FROM payments p
                                        INNER JOIN booking b ON b.booking_id = p.booking_id
                                        LEFT JOIN tour t ON t.tour_id = b.tour_id
                                        LEFT JOIN khach_hang kh ON kh.khach_hang_id = b.khach_hang_id
                                        LEFT JOIN nguoi_dung nd ON nd.id = kh.nguoi_dung_id
                                        WHERE p.payment_id = ?
                                        LIMIT 1");
                $stmt->execute([$paymentId]);
                $paymentDetail = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Throwable $e) {
                $paymentDetail = null;
            }
        }

        include __DIR__ . '/../views/admin/payments/show.php';
    }

    public static function confirmReceived($conn, $id) {
        $paymentId = validateId($id) ?? 0;
        if ($paymentId <= 0) {
            $_SESSION['error'] = 'Payment ID khong hop le.';
            header('Location: index.php?act=admin/payments');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yeu cau xac nhan khong hop le.';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'payment_confirm_received')) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF).';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        $schema = validateInputSchema([
            'received_amount' => ['type' => 'money', 'required' => true, 'min' => 1],
            'transfer_note' => ['type' => 'string', 'required' => true, 'min' => 3, 'max' => 255],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Thong tin xac nhan thanh toan khong hop le.');
            $_SESSION['error'] = 'Thong tin xac nhan thanh toan khong hop le.';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        $receivedAmount = (float)($schema['data']['received_amount'] ?? 0);
        $transferNote = (string)($schema['data']['transfer_note'] ?? '');

        try {
            $stmt = $conn->prepare("SELECT p.payment_id, p.booking_id, p.amount, p.status,
                                           b.tour_id, b.khach_hang_id, b.trang_thai AS booking_status,
                                           nd.ho_ten, nd.so_dien_thoai,
                                           t.ten_tour
                                    FROM payments p
                                    INNER JOIN booking b ON b.booking_id = p.booking_id
                                    LEFT JOIN khach_hang kh ON kh.khach_hang_id = b.khach_hang_id
                                    LEFT JOIN nguoi_dung nd ON nd.id = kh.nguoi_dung_id
                                    LEFT JOIN tour t ON t.tour_id = b.tour_id
                                    WHERE p.payment_id = ?
                                    LIMIT 1");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $_SESSION['error'] = 'Khong tim thay giao dich thanh toan.';
                header('Location: index.php?act=admin/payments');
                exit;
            }

            if (in_array(($payment['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true)) {
                $_SESSION['success'] = 'Giao dich da o trang thai ThanhCong truoc do.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            $expectedAmount = (float)($payment['amount'] ?? 0);
            if ($receivedAmount <= 0) {
                setValidationErrors(['received_amount' => 'required|numeric|min:1'], 'Vui long nhap so tien thuc nhan.');
                $_SESSION['error'] = 'Vui long nhap so tien thuc nhan de xac nhan giao dich.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            if ($receivedAmount + 1 < $expectedAmount) {
                $thieu = $expectedAmount - $receivedAmount;
                PaymentLog::create($conn, [
                    'payment_id' => $paymentId,
                    'action' => 'UNDERPAID_CHECK',
                    'log_time' => date('Y-m-d H:i:s'),
                    'note' => 'So tien nhan thuc te chua du. expected=' . $expectedAmount . ', received=' . $receivedAmount
                ]);
                setValidationErrors(['received_amount' => 'underpaid'], 'So tien thuc nhan chua du de xac nhan.');
                $_SESSION['error'] = 'Khach chuyen chua du tien. Con thieu ' . number_format($thieu) . ' VND.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            $noteCompact = preg_replace('/\s+/', '', strtoupper($transferNote));
            $bookingToken = (string)($payment['booking_id'] ?? '');
            $phoneDigits = preg_replace('/\D+/', '', (string)($payment['so_dien_thoai'] ?? ''));
            $matchedBooking = ($bookingToken !== '' && strpos($noteCompact, strtoupper($bookingToken)) !== false);
            $matchedPhone = false;
            if ($phoneDigits !== '') {
                if (strpos($noteCompact, $phoneDigits) !== false) {
                    $matchedPhone = true;
                } elseif (strlen($phoneDigits) >= 4) {
                    $matchedPhone = strpos($noteCompact, substr($phoneDigits, -4)) !== false;
                }
            }

            if ($transferNote === '') {
                setValidationErrors(['transfer_note' => 'required'], 'Vui long nhap noi dung chuyen khoan.');
                $_SESSION['error'] = 'Vui long nhap noi dung chuyen khoan de doi chieu booking/khach hang.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            if (!$matchedBooking && !$matchedPhone) {
                PaymentLog::create($conn, [
                    'payment_id' => $paymentId,
                    'action' => 'UNMATCHED_TRANSFER_NOTE',
                    'log_time' => date('Y-m-d H:i:s'),
                    'note' => 'Noi dung chuyen khoan khong khop booking_id/so_dien_thoai. note=' . $transferNote
                ]);
                setValidationErrors(['transfer_note' => 'unmatched'], 'Noi dung chuyen khoan chua khop booking hoac SDT.');
                $_SESSION['error'] = 'Noi dung chuyen khoan chua khop booking hoac SĐT. Chua the xac nhan tu dong.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            $conn->beginTransaction();

            $stmtUpdate = $conn->prepare("UPDATE payments
                                          SET amount = ?,
                                              note = CONCAT(COALESCE(note, ''), ' | ADMIN_CONFIRM=', ?, ' | TRANSFER_NOTE=', ?)
                                          WHERE payment_id = ?");
            $stmtUpdate->execute([
                $receivedAmount,
                date('Y-m-d H:i:s'),
                substr($transferNote, 0, 255),
                $paymentId
            ]);

            $transition = Payment::transitionStatus($conn, $paymentId, Payment::STATUS_THANH_CONG, 'admin_manual_confirm_received', [
                'received_amount' => $receivedAmount,
                'matched_booking' => $matchedBooking ? 1 : 0,
                'matched_phone' => $matchedPhone ? 1 : 0,
            ]);
            if (!$transition['ok']) {
                throw new RuntimeException((string)$transition['message']);
            }

            PaymentLog::create($conn, [
                'payment_id' => $paymentId,
                'action' => 'MANUAL_CONFIRM',
                'log_time' => date('Y-m-d H:i:s'),
                'note' => 'Admin xac nhan da nhan tien. received=' . $receivedAmount . '; matched_booking=' . ($matchedBooking ? 'yes' : 'no') . '; matched_phone=' . ($matchedPhone ? 'yes' : 'no')
            ]);

            if (!in_array((string)($payment['booking_status'] ?? ''), ['DaCoc', 'HoanTat'], true)) {
                $stmtBooking = $conn->prepare("UPDATE booking SET trang_thai = 'DaCoc' WHERE booking_id = ?");
                $stmtBooking->execute([(int)$payment['booking_id']]);
            }

            $stmtExists = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu'");
            $stmtExists->execute([(int)$payment['booking_id']]);
            $existsFinance = (int)$stmtExists->fetchColumn() > 0;

            if (!$existsFinance) {
                $stmtFinance = $conn->prepare("INSERT INTO giao_dich_tai_chinh (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich)
                                               VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtFinance->execute([
                    (int)$payment['booking_id'],
                    (int)($payment['tour_id'] ?? 0),
                    (int)($payment['khach_hang_id'] ?? 0),
                    'Thu',
                    (float)($payment['amount'] ?? 0),
                    'Admin xac nhan da nhan chuyen khoan QR cho payment #' . $paymentId,
                    date('Y-m-d')
                ]);
            }

            $conn->commit();
            $_SESSION['success'] = 'Da xac nhan nhan tien thanh cong cho payment #' . $paymentId . '.';
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error'] = 'Khong the xac nhan giao dich: ' . $e->getMessage();
        }

        header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
        exit;
    }
    /**
     * Xác nhận thanh toán gateway (VNPay/Momo/...) bị kẹt ở DangXuLy do callback không về.
     * Không yêu cầu đối chiếu nội dung chuyển khoản vì admin đã xác minh trực tiếp qua cổng.
     */
    public static function confirmGatewayPayment($conn, $id) {
        $paymentId = validateId($id) ?? 0;
        if ($paymentId <= 0) {
            $_SESSION['error'] = 'Payment ID không hợp lệ.';
            header('Location: index.php?act=admin/payments');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'payment_gateway_confirm')) {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ (CSRF).';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        $gatewayMethods = ['VNPay', 'Momo', 'Paypal'];

        try {
            $stmt = $conn->prepare("SELECT p.payment_id, p.booking_id, p.amount, p.status, p.payment_method,
                                           b.tour_id, b.khach_hang_id, b.trang_thai AS booking_status
                                    FROM payments p
                                    INNER JOIN booking b ON b.booking_id = p.booking_id
                                    WHERE p.payment_id = ?
                                    LIMIT 1");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $_SESSION['error'] = 'Không tìm thấy giao dịch thanh toán.';
                header('Location: index.php?act=admin/payments');
                exit;
            }

            if (!in_array((string)($payment['payment_method'] ?? ''), $gatewayMethods, true)) {
                $_SESSION['error'] = 'Chỉ áp dụng xác nhận gateway cho thanh toán VNPay/Momo/Paypal.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            if (in_array(($payment['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true)) {
                $_SESSION['success'] = 'Giao dịch đã ở trạng thái ThanhCong.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            $adminNote = requestString('admin_note', '', 'POST');
            $adminId = (int)($_SESSION['user_id'] ?? 0);

            $conn->beginTransaction();

            $stmtUpdate = $conn->prepare("UPDATE payments
                                          SET note = CONCAT(COALESCE(note, ''), ' | GATEWAY_CONFIRM=', ?)
                                          WHERE payment_id = ?");
            $stmtUpdate->execute([
                date('Y-m-d H:i:s') . ($adminNote !== '' ? '; note=' . substr($adminNote, 0, 200) : '') . '; admin_id=' . $adminId,
                $paymentId
            ]);

            $transition = Payment::transitionStatus($conn, $paymentId, Payment::STATUS_THANH_CONG, 'admin_manual_gateway_confirm', [
                'admin_id' => $adminId,
            ]);
            if (!$transition['ok']) {
                throw new RuntimeException((string)$transition['message']);
            }

            PaymentLog::create($conn, [
                'payment_id' => $paymentId,
                'action' => 'GATEWAY_MANUAL_CONFIRM',
                'log_time' => date('Y-m-d H:i:s'),
                'note' => 'Admin xác nhận thanh toán gateway thành công (callback bị thiếu). admin_id=' . $adminId
                    . ($adminNote !== '' ? '; ghi_chu=' . substr($adminNote, 0, 200) : '')
            ]);

            if (!in_array((string)($payment['booking_status'] ?? ''), ['DaCoc', 'HoanTat'], true)) {
                $stmtBooking = $conn->prepare("UPDATE booking SET trang_thai = 'DaCoc' WHERE booking_id = ?");
                $stmtBooking->execute([(int)$payment['booking_id']]);
            }

            $stmtExists = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu'");
            $stmtExists->execute([(int)$payment['booking_id']]);
            if ((int)$stmtExists->fetchColumn() === 0) {
                $stmtFinance = $conn->prepare("INSERT INTO giao_dich_tai_chinh
                    (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtFinance->execute([
                    (int)$payment['booking_id'],
                    (int)($payment['tour_id'] ?? 0),
                    (int)($payment['khach_hang_id'] ?? 0),
                    'Thu',
                    (float)($payment['amount'] ?? 0),
                    'Xác nhận thanh toán gateway #' . $paymentId . ' (admin override)',
                    date('Y-m-d')
                ]);
            }

            $conn->commit();
            $_SESSION['success'] = 'Đã xác nhận thanh toán gateway thành công cho payment #' . $paymentId . '.';
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error'] = 'Không thể xác nhận thanh toán gateway: ' . $e->getMessage();
        }

        header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
        exit;
    }

    public static function create($conn, $data) {
        Payment::create($conn, $data);
        // Redirect or show message
    }
    public static function update($conn, $id, $data) {
        Payment::update($conn, $id, $data);
        // Redirect or show message
    }
    public static function delete($conn, $id) {
        Payment::delete($conn, $id);
        // Redirect or show message
    }
}
