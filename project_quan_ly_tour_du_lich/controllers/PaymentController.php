<?php
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/PaymentLog.php';
require_once __DIR__ . '/../services/PaymentFinanceService.php';
require_once __DIR__ . '/../services/PaymentReconcileService.php';
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

        PaymentReconcileService::runAutoReconcileTick($conn);
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
        if ((currentUserRole() ?? '') === 'Admin'
            && isset($_SESSION['error'])
            && trim((string)$_SESSION['error']) === 'Ban khong co quyen truy cap chuc nang nay.') {
            unset($_SESSION['error']);
        }

        Payment::ensureStateMachineSchema($conn);
        PaymentReconcileService::ensureReconcileAuditTable($conn);
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

        PaymentReconcileService::runAutoReconcileTick($conn);
        $filters = [
            'from_date' => validateDateYmd(requestString('from_date', '', 'GET')) ?? '',
            'to_date' => validateDateYmd(requestString('to_date', '', 'GET')) ?? '',
            'payment_status' => requestString('payment_status', '', 'GET'),
            'reconcile_state' => requestString('reconcile_state', '', 'GET')
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repair_payment_id'])) {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'payment_reconcile_repair')) {
                $_SESSION['payment_reconcile_error'] = 'Yeu cau khong hop le (CSRF).';
                header('Location: index.php?act=admin/paymentReconcile');
                exit;
            }

            $paymentId = requestId('repair_payment_id', 0, 'POST') ?? 0;
            $repairReason = requestString('repair_reason', '', 'POST');
            $repairResult = PaymentReconcileService::repairMissingFinanceTransaction($conn, $paymentId, $repairReason);
            $_SESSION[$repairResult['ok'] ? 'payment_reconcile_success' : 'payment_reconcile_error'] = $repairResult['message'];

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

        $dailyMismatchReport = PaymentReconcileService::refreshDailyMismatchReportCache($conn);

        try {
            $reconcileData = PaymentReconcileService::buildReconcileRowsSummary($conn, $filters);
            $reconcileRows = $reconcileData['rows'] ?? [];
            $summary = $reconcileData['summary'] ?? [];
        } catch (Throwable $e) {
            $_SESSION['payment_reconcile_error'] = 'Khong the doi soat thanh toan: ' . $e->getMessage();
            $reconcileRows = [];
            $summary = [
                'total' => 0,
                'ok' => 0,
                'warning' => 0,
                'thieu_thu' => 0,
                'thua_thu' => 0,
                'lech_tien' => 0,
            ];
        }

        include __DIR__ . '/../views/admin/payments/reconcile.php';
    }

    public static function complaints($conn) {
        require_once __DIR__ . '/../models/ThongBao.php';

        $filters = [
            'trang_thai' => requestString('trang_thai', '', 'GET'),
            'search' => trim(requestString('search', '', 'GET')),
            'limit' => 200,
        ];

        $thongBaoModel = new ThongBao();
        $complaints = $thongBaoModel->getPaymentComplaints($filters);
        $totalComplaints = count($complaints);
        $pendingComplaints = $thongBaoModel->countPaymentComplaintsChuaXuLy();

        include __DIR__ . '/../views/admin/payments/complaints.php';
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

            $existsFinance = PaymentFinanceService::existsThuTransaction($conn, (int)$payment['booking_id']);

            if (!$existsFinance) {
                PaymentFinanceService::createThuTransaction($conn, [
                    'booking_id' => (int)$payment['booking_id'],
                    'tour_id' => (int)($payment['tour_id'] ?? 0),
                    'khach_hang_id' => (int)($payment['khach_hang_id'] ?? 0),
                    'amount' => (float)($payment['amount'] ?? 0),
                    'description' => 'Admin xac nhan da nhan chuyen khoan QR cho payment #' . $paymentId,
                    'payment_date' => date('Y-m-d'),
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

            PaymentFinanceService::createThuTransactionIfMissing($conn, [
                'booking_id' => (int)$payment['booking_id'],
                'tour_id' => (int)($payment['tour_id'] ?? 0),
                'khach_hang_id' => (int)($payment['khach_hang_id'] ?? 0),
                'amount' => (float)($payment['amount'] ?? 0),
                'description' => 'Xác nhận thanh toán gateway #' . $paymentId . ' (admin override)',
                'payment_date' => date('Y-m-d'),
            ]);

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
