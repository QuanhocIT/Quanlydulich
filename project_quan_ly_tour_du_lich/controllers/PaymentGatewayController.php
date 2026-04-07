<?php

class PaymentGatewayController {
    private static function debugRedirect($stage, array $payload = []) {
        $logFile = __DIR__ . '/../storage/payment_redirect_debug.log';
        $payload['stage'] = $stage;
        $payload['ts'] = date('c');
        @file_put_contents($logFile, json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    }

    /**
     * Truy vấn trạng thái giao dịch VNPay để cập nhật payment bị kẹt ở DangXuLy.
     */
    public static function queryVnpayStatus($conn, $id) {
        require_once __DIR__ . '/../models/Payment.php';
        require_once __DIR__ . '/../models/PaymentLog.php';
        self::ensurePaymentTables($conn);

        $paymentId = validateId($id) ?? 0;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ.';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'payment_vnpay_query')) {
            $_SESSION['error'] = 'Yêu cầu không hợp lệ (CSRF).';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        if ($paymentId <= 0) {
            $_SESSION['error'] = 'Payment ID không hợp lệ.';
            header('Location: index.php?act=admin/payments');
            exit;
        }

        if (!self::isVnpayConfigured()) {
            $_SESSION['error'] = 'VNPay chưa được cấu hình (thiếu TMN Code hoặc Hash Secret).';
            header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT p.payment_id, p.booking_id, p.amount, p.status, p.payment_method, p.note, p.payment_date,
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

            if (in_array(($payment['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true)) {
                $_SESSION['success'] = 'Giao dịch đã ở trạng thái ThanhCong.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            // Trích xuất TXN_REF từ note
            $note = (string)($payment['note'] ?? '');
            $txnRef = '';
            if (preg_match('/TXN_REF=([A-Za-z0-9]+)/', $note, $m)) {
                $txnRef = $m[1];
            }

            if ($txnRef === '') {
                $_SESSION['error'] = 'Không tìm thấy mã giao dịch VNPay (TXN_REF) trong payment này.';
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            // Xây dựng payload truy vấn VNPay querydr
            $transDate = date('YmdHis', strtotime((string)($payment['payment_date'] ?? 'now')));
            $createDate = date('YmdHis');
            $requestId = date('YmdHis') . rand(1000, 9999);
            $ipAddr = $_SERVER['SERVER_ADDR'] ?? ($_SERVER['LOCAL_ADDR'] ?? '127.0.0.1');
            $orderInfo = 'Kiem tra trang thai booking #' . (int)$payment['booking_id'];

            $hashData = $requestId . '|2.1.0|querydr|' . VNPAY_TMN_CODE . '|' . $txnRef . '|' . $transDate . '|' . $createDate . '|' . $ipAddr . '|' . $orderInfo;
            $secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);

            $postData = [
                'vnp_RequestId'      => $requestId,
                'vnp_Version'        => '2.1.0',
                'vnp_Command'        => 'querydr',
                'vnp_TmnCode'        => VNPAY_TMN_CODE,
                'vnp_TxnRef'         => $txnRef,
                'vnp_OrderInfo'      => $orderInfo,
                'vnp_TransactionDate'=> $transDate,
                'vnp_CreateDate'     => $createDate,
                'vnp_IpAddr'         => $ipAddr,
                'vnp_SecureHash'     => $secureHash
            ];

            $queryUrl = 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction';
            $ch = curl_init($queryUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $curlError = curl_error($ch);

            if ($curlError !== '') {
                PaymentLog::create($conn, [
                    'payment_id' => $paymentId,
                    'action' => 'VNPAY_QUERY_ERROR',
                    'log_time' => date('Y-m-d H:i:s'),
                    'note' => 'curl_error=' . $curlError
                ]);
                $_SESSION['error'] = 'Lỗi kết nối đến VNPay: ' . $curlError;
                header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
                exit;
            }

            $result = json_decode((string)$response, true);
            $vnpResponseCode = (string)($result['vnp_ResponseCode'] ?? '');
            $vnpTransactionStatus = (string)($result['vnp_TransactionStatus'] ?? '');
            $vnpTransactionNo = (string)($result['vnp_TransactionNo'] ?? '');

            PaymentLog::create($conn, [
                'payment_id' => $paymentId,
                'action' => 'VNPAY_QUERY_RESULT',
                'log_time' => date('Y-m-d H:i:s'),
                'note' => 'txnRef=' . $txnRef . '; responseCode=' . $vnpResponseCode . '; transStatus=' . $vnpTransactionStatus . '; transNo=' . $vnpTransactionNo
            ]);

            // responseCode=00 và transactionStatus=00 → ThanhCong
            if ($vnpResponseCode === '00' && $vnpTransactionStatus === '00') {
                $conn->beginTransaction();

                $stmtUp = $conn->prepare("UPDATE payments SET note = CONCAT(COALESCE(note,''), ' | VNPAY_QUERY_CONFIRM=', ?) WHERE payment_id = ?");
                $stmtUp->execute([date('Y-m-d H:i:s') . '; transNo=' . $vnpTransactionNo, $paymentId]);

                $transition = Payment::transitionStatus($conn, $paymentId, Payment::STATUS_THANH_CONG, 'vnpay_query_confirm', [
                    'transaction_no' => $vnpTransactionNo,
                ]);
                if (!$transition['ok']) {
                    throw new RuntimeException((string)$transition['message']);
                }

                if (!in_array((string)($payment['booking_status'] ?? ''), ['DaCoc', 'HoanTat'], true)) {
                    $conn->prepare("UPDATE booking SET trang_thai = 'DaCoc' WHERE booking_id = ?")->execute([(int)$payment['booking_id']]);
                }

                $stmtExists = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu'");
                $stmtExists->execute([(int)$payment['booking_id']]);
                if ((int)$stmtExists->fetchColumn() === 0) {
                    $conn->prepare("INSERT INTO giao_dich_tai_chinh
                        (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich)
                        VALUES (?, ?, ?, ?, ?, ?, ?)")->execute([
                        (int)$payment['booking_id'],
                        (int)($payment['tour_id'] ?? 0),
                        (int)($payment['khach_hang_id'] ?? 0),
                        'Thu',
                        (float)($payment['amount'] ?? 0),
                        'Xác nhận qua VNPay Query cho payment #' . $paymentId,
                        date('Y-m-d')
                    ]);
                }

                $conn->commit();
                $_SESSION['success'] = 'VNPay xác nhận giao dịch thành công! Payment #' . $paymentId . ' đã được cập nhật.';
            } elseif ($vnpResponseCode === '00') {
                // Giao dịch tồn tại nhưng đang xử lý hoặc thất bại
                $newStatus = ($vnpTransactionStatus === '02') ? Payment::STATUS_THAT_BAI : Payment::STATUS_DANG_XU_LY;
                $conn->prepare("UPDATE payments SET note = CONCAT(COALESCE(note,''), ' | VNPAY_QUERY=', ?) WHERE payment_id = ?")
                    ->execute(['transStatus=' . $vnpTransactionStatus, $paymentId]);
                Payment::transitionStatus($conn, $paymentId, $newStatus, 'vnpay_query_non_success', [
                    'response_code' => $vnpResponseCode,
                    'transaction_status' => $vnpTransactionStatus,
                ]);
                $_SESSION['error'] = 'VNPay trả về giao dịch chưa thành công (transStatus=' . $vnpTransactionStatus . '). Trạng thái: ' . $newStatus . '.';
            } else {
                $_SESSION['error'] = 'VNPay không tìm thấy hoặc từ chối giao dịch (responseCode=' . $vnpResponseCode . '). Vui lòng kiểm tra lại hoặc xác nhận thủ công.';
            }
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error'] = 'Lỗi truy vấn VNPay: ' . $e->getMessage();
        }

        header('Location: index.php?act=admin/show_payment&id=' . $paymentId);
        exit;
    }

    /**
     * VNPay IPN (Instant Payment Notification) - server-to-server callback từ VNPay.
     * VNPay gọi URL này trực tiếp sau khi giao dịch hoàn tất, không phụ thuộc trình duyệt.
     * Phải trả về JSON; không được redirect hay echo HTML.
     */
    public static function vnpayIpn($conn) {
        require_once __DIR__ . '/../models/Payment.php';
        require_once __DIR__ . '/../models/PaymentIdempotency.php';
        require_once __DIR__ . '/../models/PaymentLog.php';
        self::ensurePaymentTables($conn);

        @ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        $logFile = __DIR__ . '/../storage/vnpay_ipn.log';

        // Đọc tham số thô từ query string để bypass whitelist của router (IPN server-to-server không cần whitelist)
        $rawParams = [];
        parse_str($_SERVER['QUERY_STRING'] ?? '', $rawParams);
        $params = $rawParams;

        @file_put_contents($logFile, date('c') . ' IPN_RECEIVED ' . json_encode($params, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);

        // 1. Xác thực chữ ký
        if (!self::isVnpayConfigured()) {
            echo json_encode(['RspCode' => '99', 'Message' => 'VNPay not configured']);
            exit;
        }

        $verified = self::verifyVnpaySignature($params);
        if (!$verified) {
            @file_put_contents($logFile, date('c') . ' IPN_INVALID_SIGNATURE' . PHP_EOL, FILE_APPEND);
            echo json_encode(['RspCode' => '97', 'Message' => 'Invalid signature']);
            exit;
        }

        $responseCode     = (string)($params['vnp_ResponseCode'] ?? '');
        $transactionStatus = (string)($params['vnp_TransactionStatus'] ?? '');
        $txnRef           = (string)($params['vnp_TxnRef'] ?? '');
        $vnpAmount        = (int)($params['vnp_Amount'] ?? 0); // VNPay gửi nhân 100
        $gatewayRef       = (string)($params['vnp_TransactionNo'] ?? $txnRef);

        // 2. Parse txnRef lấy booking_id và payment_id
        $txnData = self::parseTxnRef($txnRef);
        $bookingId = (int)$txnData['booking_id'];
        $paymentId = (int)$txnData['payment_id'];

        if ($bookingId <= 0 || $paymentId <= 0) {
            @file_put_contents($logFile, date('c') . ' IPN_BAD_TXNREF txnRef=' . $txnRef . PHP_EOL, FILE_APPEND);
            echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
            exit;
        }

        $idempotencyRawKey = implode('|', [
            'txn_ref=' . $txnRef,
            'gateway_ref=' . $gatewayRef,
            'amount=' . $vnpAmount,
            'resp=' . $responseCode,
            'txn_status=' . $transactionStatus,
        ]);
        $idempotencyClaim = PaymentIdempotency::claim($conn, 'vnpay_ipn', $idempotencyRawKey, json_encode($params, JSON_UNESCAPED_UNICODE));
        if (!$idempotencyClaim['owner']) {
            if ($idempotencyClaim['status'] === PaymentIdempotency::STATUS_COMPLETED) {
                echo json_encode(['RspCode' => '00', 'Message' => 'Duplicate completed']);
                exit;
            }
            echo json_encode(['RspCode' => '00', 'Message' => 'Duplicate processing']);
            exit;
        }

        // 3. Lấy payment từ DB
        try {
            $stmt = $conn->prepare("SELECT p.payment_id, p.booking_id, p.amount, p.status, p.payment_method,
                                           b.tour_id, b.khach_hang_id, b.trang_thai AS booking_status
                                    FROM payments p
                                    INNER JOIN booking b ON b.booking_id = p.booking_id
                                    WHERE p.payment_id = ?
                                    LIMIT 1");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            @file_put_contents($logFile, date('c') . ' IPN_DB_ERROR ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            echo json_encode(['RspCode' => '99', 'Message' => 'System error']);
            exit;
        }

        if (!$payment) {
            echo json_encode(['RspCode' => '01', 'Message' => 'Order not found']);
            exit;
        }

        // 4. Kiểm tra số tiền khớp (VNPay gửi * 100)
        $expectedAmount = (int)round((float)$payment['amount'] * 100);
        if ($vnpAmount !== $expectedAmount) {
            PaymentLog::create($conn, [
                'payment_id' => $paymentId,
                'action' => 'IPN_AMOUNT_MISMATCH',
                'log_time' => date('Y-m-d H:i:s'),
                'note' => 'expected=' . $expectedAmount . ' received=' . $vnpAmount
            ]);
            @file_put_contents($logFile, date('c') . ' IPN_AMOUNT_MISMATCH expected=' . $expectedAmount . ' received=' . $vnpAmount . PHP_EOL, FILE_APPEND);
            PaymentIdempotency::markCompleted($conn, 'vnpay_ipn', $idempotencyRawKey, 4, 'Invalid amount');
            echo json_encode(['RspCode' => '04', 'Message' => 'Invalid amount']);
            exit;
        }

        // 5. Idempotent: đã xử lý rồi thì báo OK ngay
        if (in_array(($payment['status'] ?? ''), [Payment::STATUS_THANH_CONG, Payment::STATUS_DA_DOI_SOAT], true)) {
            PaymentIdempotency::markCompleted($conn, 'vnpay_ipn', $idempotencyRawKey, 0, 'Already successful');
            echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
            exit;
        }

        // 6. Cập nhật theo kết quả từ VNPay
        $isSuccess = ($responseCode === '00') && ($transactionStatus === '00');

        try {
            $conn->beginTransaction();

            $lockedPayment = Payment::findForUpdate($conn, $paymentId);
            if (!$lockedPayment) {
                throw new RuntimeException('Payment not found while locking');
            }

            self::lockBookingRow($conn, (int)$lockedPayment['booking_id']);

            $newStatus = $isSuccess ? Payment::STATUS_THANH_CONG : Payment::STATUS_THAT_BAI;
            $conn->prepare("UPDATE payments SET note = CONCAT(COALESCE(note,''), ' | IPN=', ?, ' transStatus=', ?)
                            WHERE payment_id = ?")
                ->execute([$gatewayRef, $transactionStatus, $paymentId]);

            $transition = Payment::transitionStatus($conn, $paymentId, $newStatus, 'vnpay_ipn', [
                'response_code' => $responseCode,
                'transaction_status' => $transactionStatus,
                'gateway_ref' => $gatewayRef,
            ]);
            if (!$transition['ok']) {
                throw new RuntimeException((string)$transition['message']);
            }

            PaymentLog::create($conn, [
                'payment_id' => $paymentId,
                'action' => 'IPN_' . ($isSuccess ? 'SUCCESS' : 'FAILED'),
                'log_time' => date('Y-m-d H:i:s'),
                'note' => 'responseCode=' . $responseCode . '; transStatus=' . $transactionStatus . '; gatewayRef=' . $gatewayRef
            ]);

            if ($isSuccess) {
                if (!in_array((string)($payment['booking_status'] ?? ''), ['DaCoc', 'HoanTat'], true)) {
                    $conn->prepare("UPDATE booking SET trang_thai = 'DaCoc' WHERE booking_id = ?")
                        ->execute([(int)$payment['booking_id']]);
                }

                $stmtExists = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu'");
                $stmtExists->execute([(int)$payment['booking_id']]);
                if ((int)$stmtExists->fetchColumn() === 0) {
                    $conn->prepare("INSERT INTO giao_dich_tai_chinh
                        (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich)
                        VALUES (?, ?, ?, ?, ?, ?, ?)")
                        ->execute([
                            (int)$payment['booking_id'],
                            (int)($payment['tour_id'] ?? 0),
                            (int)($payment['khach_hang_id'] ?? 0),
                            'Thu',
                            (float)($payment['amount'] ?? 0),
                            'VNPay IPN - payment #' . $paymentId . ' - ref=' . $gatewayRef,
                            date('Y-m-d')
                        ]);
                }
            }

            $conn->commit();
            PaymentIdempotency::markCompleted($conn, 'vnpay_ipn', $idempotencyRawKey, 0, 'Processed');
            @file_put_contents($logFile, date('c') . ' IPN_PROCESSED paymentId=' . $paymentId . ' status=' . $newStatus . PHP_EOL, FILE_APPEND);
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            PaymentIdempotency::markFailed($conn, 'vnpay_ipn', $idempotencyRawKey, 99, $e->getMessage());
            @file_put_contents($logFile, date('c') . ' IPN_COMMIT_ERROR ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            echo json_encode(['RspCode' => '99', 'Message' => 'System error']);
            exit;
        }

        echo json_encode(['RspCode' => '00', 'Message' => 'Confirm Success']);
        exit;
    }

    // Hiển thị trang chọn phương thức thanh toán (admin)
    public static function pay($conn, $booking_id) {
        $booking_id = (int)$booking_id;
        include __DIR__ . '/../views/admin/payments/pay.php';
    }

    public static function redirect($conn, $booking_id, $method) {
        require_once __DIR__ . '/../models/Payment.php';
        self::ensurePaymentTables($conn);

        $bookingId = (int)$booking_id;
        $method = self::normalizeMethod($method);
        $dbMethod = self::resolveDbPaymentMethod($conn, $method);
        $returnAct = $_GET['return_act'] ?? 'admin/chiTietBooking';
        $returnTourId = isset($_GET['return_tour_id']) ? (int)$_GET['return_tour_id'] : 0;

        self::debugRedirect('redirect_entry', [
            'booking_id' => $bookingId,
            'method' => $method,
            'return_act' => $returnAct,
            'return_tour_id' => $returnTourId,
            'payment_mode' => PAYMENT_MODE,
            'raw_get' => $_GET,
        ]);

        if ($returnAct === 'admin/chiTietBooking' && $returnTourId > 0) {
            $returnAct = 'khachHang/thanhToanTour';
        }

        if ($bookingId <= 0) {
            $_SESSION['error'] = 'Booking không hợp lệ để thanh toán.';
            header('Location: index.php?act=khachHang/dashboard');
            exit;
        }

        $booking = self::findBooking($conn, $bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Không tìm thấy booking cần thanh toán.';
            header('Location: index.php?act=khachHang/dashboard');
            exit;
        }

        if ($returnAct === 'khachHang/thanhToanTour' && $returnTourId <= 0) {
            $returnTourId = (int)($booking['tour_id'] ?? 0);
        }

        $amount = (float)($booking['tong_tien'] ?? 0);
        if ($amount <= 0) {
            $_SESSION['error'] = 'Số tiền booking không hợp lệ.';
            $redirectUrl = 'index.php?act=' . $returnAct . '&booking_id=' . $bookingId;
            if ($returnTourId > 0) {
                $redirectUrl .= '&id=' . $returnTourId;
            }
            header('Location: ' . $redirectUrl);
            exit;
        }

        $paymentId = self::findReusableInFlightPaymentId($conn, $bookingId, $dbMethod);
        if ($paymentId > 0) {
            self::createPaymentLog($conn, $paymentId, 'REUSE_INFLIGHT', 'Tai su dung payment dang xu ly do request gui lai');
        } else {
            $paymentId = self::createPayment($conn, [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'payment_method' => $dbMethod,
                'status' => Payment::STATUS_TAO_MOI,
                'note' => 'Khoi tao thanh toan online | gateway=' . $method
            ]);
        }

        if (!$paymentId) {
            $_SESSION['error'] = 'Không thể khởi tạo giao dịch thanh toán.';
            $redirectUrl = 'index.php?act=' . $returnAct . '&booking_id=' . $bookingId;
            if ($returnTourId > 0) {
                $redirectUrl .= '&id=' . $returnTourId;
            }
            header('Location: ' . $redirectUrl);
            exit;
        }

        if ((int)self::countPaymentLogs($conn, $paymentId, 'CREATE') === 0) {
            self::createPaymentLog($conn, $paymentId, 'CREATE', 'Khoi tao giao dich online');

            $toProcessing = Payment::transitionStatus($conn, $paymentId, Payment::STATUS_DANG_XU_LY, 'redirect_gateway_start', [
                'gateway_method' => $method,
                'payment_mode' => PAYMENT_MODE,
            ]);
            if (!$toProcessing['ok']) {
                $_SESSION['error'] = 'Khong the chuyen trang thai thanh toan sang DangXuLy.';
                header('Location: index.php?act=' . $returnAct . '&booking_id=' . $bookingId);
                exit;
            }
        } else {
            $currentStatus = self::getPaymentStatus($conn, $paymentId);
            if ($currentStatus === Payment::STATUS_TAO_MOI) {
                $toProcessing = Payment::transitionStatus($conn, $paymentId, Payment::STATUS_DANG_XU_LY, 'redirect_gateway_retry_activate', [
                    'gateway_method' => $method,
                    'payment_mode' => PAYMENT_MODE,
                ]);
                if (!$toProcessing['ok']) {
                    $_SESSION['error'] = 'Khong the chuyen trang thai thanh toan sang DangXuLy.';
                    header('Location: index.php?act=' . $returnAct . '&booking_id=' . $bookingId);
                    exit;
                }
            }
        }

        $txnRef = self::buildTxnRef($bookingId, $paymentId);
        self::updatePaymentNote($conn, $paymentId, 'TXN_REF=' . $txnRef);

        if (PAYMENT_MODE === 'vnpay') {
            if (!self::isVnpayConfigured()) {
                self::updatePaymentStatus($conn, $paymentId, Payment::STATUS_THAT_BAI, 'Thieu cau hinh VNPay (TMN/HASH_SECRET)', '');
                self::createPaymentLog($conn, $paymentId, 'CONFIG_ERROR', 'VNPay mode dang bat nhung thieu VNPAY_TMN_CODE hoac VNPAY_HASH_SECRET');
                $_SESSION['error'] = 'He thong chua cau hinh day du VNPay. Vui long lien he admin.';
                $redirectUrl = 'index.php?act=' . $returnAct . '&booking_id=' . $bookingId;
                if ($returnTourId > 0) {
                    $redirectUrl .= '&id=' . $returnTourId;
                }
                header('Location: ' . $redirectUrl);
                exit;
            }

            $vnpUrl = self::buildVnpayUrl($bookingId, $amount, $txnRef, $method, $returnAct, $returnTourId);
            self::createPaymentLog($conn, $paymentId, 'REDIRECT', 'Chuyen huong sang VNPay');
            header('Location: ' . $vnpUrl);
            exit;
        }

        if (PAYMENT_MODE === 'manual_qr') {
            self::createPaymentLog($conn, $paymentId, 'WAIT_CONFIRM', 'Cho admin xac nhan da nhan tien chuyen khoan QR');

            $consumeResult = BankWebhookController::tryConsumeQueuedWebhookForBooking($conn, $bookingId);
            if (!empty($consumeResult['confirmed'])) {
                self::createPaymentLog($conn, $paymentId, 'AUTO_RETRY_MATCH', 'Tu dong doi soat thanh cong tu webhook queue (queue_id=' . (int)($consumeResult['queue_id'] ?? 0) . ')');
                $_SESSION['success'] = 'Da ghi nhan va doi soat thanh toan tu dong. Hoa don da duoc cap nhat thanh cong.';
            } else {
                $_SESSION['success'] = 'Da ghi nhan yeu cau thanh toan. Vui long cho admin xac nhan sau khi chuyen khoan.';
            }

            if ($returnTourId > 0) {
                $redirectUrl = 'index.php?act=khachHang/thanhToanTour&id=' . $returnTourId . '&booking_id=' . $bookingId;
            } else {
                $redirectUrl = 'index.php?act=' . $returnAct . '&booking_id=' . $bookingId;
            }
            self::debugRedirect('manual_qr_redirect', [
                'booking_id' => $bookingId,
                'return_act' => $returnAct,
                'return_tour_id' => $returnTourId,
                'redirect_url' => $redirectUrl,
            ]);
            header('Location: ' . $redirectUrl);
            exit;
        }

        // Fallback mock để test local nhanh.
        $mockUrl = 'index.php?act=payment/callback'
            . '&booking_id=' . $bookingId
            . '&payment_id=' . $paymentId
            . '&method=' . urlencode($method)
            . '&status=success'
            . '&gateway_ref=' . urlencode($txnRef)
            . '&return_act=' . urlencode($returnAct);
        if ($returnTourId > 0) {
            $mockUrl .= '&return_tour_id=' . (int)$returnTourId;
        }

        self::createPaymentLog($conn, $paymentId, 'REDIRECT', 'Mock redirect callback local');
        header('Location: ' . $mockUrl);
        exit;
    }

    public static function callback($conn, $booking_id, $method, $status) {
        require_once __DIR__ . '/../models/Payment.php';
        require_once __DIR__ . '/../models/PaymentIdempotency.php';
        self::ensurePaymentTables($conn);

        $returnAct = $_GET['return_act'] ?? 'admin/chiTietBooking';
        $returnTourId = isset($_GET['return_tour_id']) ? (int)$_GET['return_tour_id'] : 0;
        $method = self::normalizeMethod($method);

        $bookingId = (int)$booking_id;
        $paymentId = isset($_GET['payment_id']) ? (int)$_GET['payment_id'] : 0;
        $gatewayRef = $_GET['gateway_ref'] ?? '';
        $success = false;
        $statusMessage = '';

        if (isset($_GET['vnp_ResponseCode'])) {
            $verified = self::verifyVnpaySignature($_GET);
            if (!$verified) {
                $statusMessage = 'Chu ky VNPay khong hop le';
            } else {
                $txnData = self::parseTxnRef($_GET['vnp_TxnRef'] ?? '');
                if (!empty($txnData['booking_id'])) {
                    $bookingId = (int)$txnData['booking_id'];
                }
                if (!empty($txnData['payment_id'])) {
                    $paymentId = (int)$txnData['payment_id'];
                }
                $gatewayRef = $_GET['vnp_TransactionNo'] ?? ($_GET['vnp_TxnRef'] ?? '');
                $responseCode = (string)($_GET['vnp_ResponseCode'] ?? '');
                $txnStatus = (string)($_GET['vnp_TransactionStatus'] ?? '');
                $success = ($responseCode === '00') && ($txnStatus === '' || $txnStatus === '00');
                $statusMessage = 'VNPay code=' . $responseCode . ', transaction=' . ($txnStatus !== '' ? $txnStatus : 'N/A');
            }
        } else {
            $success = (strtolower((string)$status) === 'success');
            $statusMessage = 'Mock callback status=' . ($success ? 'success' : 'failed');
        }

        if ($bookingId <= 0) {
            $_SESSION['error'] = 'Callback khong hop le: thieu booking.';
            header('Location: index.php?act=khachHang/dashboard');
            exit;
        }

        $idempotencyRawKey = implode('|', [
            'booking=' . $bookingId,
            'payment=' . $paymentId,
            'gateway_ref=' . (string)$gatewayRef,
            'success=' . ($success ? '1' : '0'),
            'status=' . $statusMessage,
        ]);
        $idempotencyClaim = PaymentIdempotency::claim($conn, 'gateway_callback', $idempotencyRawKey, json_encode($_GET, JSON_UNESCAPED_UNICODE));
        if (!$idempotencyClaim['owner']) {
            if ($idempotencyClaim['status'] === PaymentIdempotency::STATUS_COMPLETED) {
                $_SESSION['success'] = 'Da bo qua callback trung lap.';
            } else {
                $_SESSION['error'] = 'Callback dang duoc xu ly, vui long thu lai sau.';
            }
            header('Location: index.php?act=khachHang/hoaDon&booking_id=' . $bookingId);
            exit;
        }

        $booking = self::findBooking($conn, $bookingId);
        if (!$booking) {
            PaymentIdempotency::markFailed($conn, 'gateway_callback', $idempotencyRawKey, 404, 'Booking not found');
            $_SESSION['error'] = 'Booking khong ton tai khi callback.';
            header('Location: index.php?act=khachHang/dashboard');
            exit;
        }

        if ($returnAct === 'khachHang/thanhToanTour' && $returnTourId <= 0) {
            $returnTourId = (int)($booking['tour_id'] ?? 0);
        }

        if ($paymentId <= 0) {
            $paymentId = self::findLatestPendingPaymentId($conn, $bookingId, self::resolveDbPaymentMethod($conn, $method));
        }

        try {
            $conn->beginTransaction();

            self::lockBookingRow($conn, $bookingId);

            if ($paymentId > 0) {
                $paymentStatus = $success ? Payment::STATUS_THANH_CONG : Payment::STATUS_THAT_BAI;
                Payment::findForUpdate($conn, $paymentId);
                self::updatePaymentStatus($conn, $paymentId, $paymentStatus, $statusMessage, $gatewayRef);
                self::createPaymentLog($conn, $paymentId, 'CALLBACK', $statusMessage . ($gatewayRef !== '' ? ' | gateway_ref=' . $gatewayRef : ''));
            }

            if ($success) {
                // Idempotent: nếu booking đã DaCoc/HoanTat thì không tạo giao dịch lặp.
                if (!in_array((string)($booking['trang_thai'] ?? ''), ['DaCoc', 'HoanTat'], true)) {
                    $stmtBooking = $conn->prepare('UPDATE booking SET trang_thai = ? WHERE booking_id = ?');
                    $stmtBooking->execute(['DaCoc', $bookingId]);
                }

                self::updateBookingPaymentStatusColumnIfExists($conn, $bookingId, 'DaThanhToan');

                if (!self::existsPaymentFinanceTransaction($conn, $bookingId)) {
                    $stmtFinance = $conn->prepare('INSERT INTO giao_dich_tai_chinh (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $stmtFinance->execute([
                        $bookingId,
                        (int)($booking['tour_id'] ?? 0),
                        (int)($booking['khach_hang_id'] ?? 0),
                        'Thu',
                        (float)($booking['tong_tien'] ?? 0),
                        'Thanh toan online booking #' . $bookingId,
                        date('Y-m-d')
                    ]);
                }
            } else {
                self::updateBookingPaymentStatusColumnIfExists($conn, $bookingId, 'ChuaThanhToan');
            }

            $conn->commit();
            PaymentIdempotency::markCompleted($conn, 'gateway_callback', $idempotencyRawKey, 0, 'Processed');
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            PaymentIdempotency::markFailed($conn, 'gateway_callback', $idempotencyRawKey, 500, $e->getMessage());
            $_SESSION['error'] = 'Loi callback thanh toan: ' . $e->getMessage();
            header('Location: index.php?act=khachHang/hoaDon&booking_id=' . $bookingId);
            exit;
        }

        if ($success) {
            self::sendPaymentSuccessEmail($booking, $paymentId, $gatewayRef);
        }

        $_SESSION[$success ? 'success' : 'error'] = $success
            ? 'Thanh toan online thanh cong cho booking #' . $bookingId
            : 'Thanh toan online that bai cho booking #' . $bookingId;

        // Sau khi thanh toan thanh cong (khach hang), dieu huong den form nhap thong tin nguoi tham gia.
        if ($success && in_array($returnAct, ['khachHang/hoaDon', 'khachHang/thanhToanTour'], true)) {
            header('Location: index.php?act=khachHang/nhapThongTinThamGia&booking_id=' . $bookingId);
            exit;
        }

        if ($returnAct === 'khachHang/hoaDon') {
            header('Location: index.php?act=khachHang/hoaDon&booking_id=' . $bookingId);
            exit;
        }

        if ($returnAct === 'khachHang/thanhToanTour' && $returnTourId > 0) {
            header('Location: index.php?act=khachHang/thanhToanTour&id=' . $returnTourId . '&booking_id=' . $bookingId);
            exit;
        }

        header('Location: index.php?act=admin/chiTietBooking&id=' . $bookingId);
        exit;
    }

    private static function normalizeMethod($method) {
        $method = trim((string)$method);
        $allowed = ['VNPay', 'Momo', 'Paypal'];
        return in_array($method, $allowed, true) ? $method : 'VNPay';
    }

    // Map method gateway về enum DB cũ khi cần tương thích.
    private static function mapGatewayToLegacyDbMethod($gatewayMethod) {
        $map = [
            'VNPay' => 'ChuyenKhoan',
            'Momo' => 'ViDienTu',
            'Paypal' => 'ViDienTu'
        ];
        return $map[$gatewayMethod] ?? 'ChuyenKhoan';
    }

    private static function resolveDbPaymentMethod($conn, $gatewayMethod) {
        $gatewayMethod = self::normalizeMethod($gatewayMethod);
        $enumValues = self::getPaymentMethodEnumValues($conn);

        if (in_array($gatewayMethod, $enumValues, true)) {
            return $gatewayMethod;
        }

        $legacy = self::mapGatewayToLegacyDbMethod($gatewayMethod);
        if (in_array($legacy, $enumValues, true)) {
            return $legacy;
        }

        if (!empty($enumValues)) {
            return $enumValues[0];
        }

        return $legacy;
    }

    private static function getPaymentMethodEnumValues($conn) {
        $stmt = $conn->query("SHOW COLUMNS FROM payments LIKE 'payment_method'");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        if (!$row || empty($row['Type'])) {
            return [];
        }

        if (!preg_match('/^enum\((.*)\)$/i', (string)$row['Type'], $m)) {
            return [];
        }

        $raw = $m[1];
        $parts = str_getcsv($raw, ',', "'", '\\');
        $values = [];
        foreach ($parts as $part) {
            $value = trim($part, "'");
            if ($value !== '') {
                $values[] = $value;
            }
        }
        return $values;
    }

    private static function findBooking($conn, $bookingId) {
        $stmt = $conn->prepare('SELECT b.booking_id, b.tour_id, b.khach_hang_id, b.tong_tien, b.trang_thai, t.ten_tour, nd.email, nd.ho_ten FROM booking b LEFT JOIN tour t ON b.tour_id = t.tour_id LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id WHERE b.booking_id = ? LIMIT 1');
        $stmt->execute([$bookingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private static function ensurePaymentTables($conn) {
        $conn->exec("CREATE TABLE IF NOT EXISTS payments (
            payment_id INT(11) NOT NULL AUTO_INCREMENT,
            booking_id INT(11) NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            payment_method ENUM('ChuyenKhoan','TienMat','TheTinDung','ViDienTu','VNPay','Momo','Paypal') NOT NULL DEFAULT 'VNPay',
            payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            status ENUM('TaoMoi','DangXuLy','ThanhCong','ThatBai','HetHan','DaDoiSoat') DEFAULT 'DangXuLy',
            note TEXT DEFAULT NULL,
            PRIMARY KEY (payment_id),
            KEY booking_id (booking_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        Payment::ensureStateMachineSchema($conn);

        $conn->exec("CREATE TABLE IF NOT EXISTS payment_logs (
            log_id INT(11) NOT NULL AUTO_INCREMENT,
            payment_id INT(11) NOT NULL,
            action VARCHAR(100) NOT NULL,
            log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            note TEXT DEFAULT NULL,
            PRIMARY KEY (log_id),
            KEY payment_id (payment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        require_once __DIR__ . '/../models/PaymentIdempotency.php';
        PaymentIdempotency::ensureTable($conn);
    }

    private static function lockBookingRow($conn, $bookingId) {
        $stmt = $conn->prepare('SELECT booking_id FROM booking WHERE booking_id = ? FOR UPDATE');
        $stmt->execute([(int)$bookingId]);
    }

    private static function createPayment($conn, $data) {
        $stmt = $conn->prepare('INSERT INTO payments (booking_id, amount, payment_method, payment_date, status, note) VALUES (?, ?, ?, ?, ?, ?)');
        $ok = $stmt->execute([
            (int)$data['booking_id'],
            (float)$data['amount'],
            (string)$data['payment_method'],
            date('Y-m-d H:i:s'),
            (string)$data['status'],
            (string)$data['note']
        ]);
        if (!$ok) {
            return 0;
        }
        return (int)$conn->lastInsertId();
    }

    private static function createPaymentLog($conn, $paymentId, $action, $note) {
        if ($paymentId <= 0) {
            return;
        }
        $stmt = $conn->prepare('INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, ?, ?, ?)');
        $stmt->execute([(int)$paymentId, (string)$action, date('Y-m-d H:i:s'), (string)$note]);
    }

    private static function updatePaymentNote($conn, $paymentId, $note) {
        $stmt = $conn->prepare('UPDATE payments SET note = ? WHERE payment_id = ?');
        $stmt->execute([(string)$note, (int)$paymentId]);
    }

    private static function updatePaymentStatus($conn, $paymentId, $status, $note, $gatewayRef) {
        require_once __DIR__ . '/../models/Payment.php';
        $fullNote = $note;
        if ($gatewayRef !== '') {
            $fullNote .= ' | gateway_ref=' . $gatewayRef;
        }
        $stmt = $conn->prepare('UPDATE payments SET note = ? WHERE payment_id = ?');
        $stmt->execute([$fullNote, (int)$paymentId]);

        $transition = Payment::transitionStatus($conn, (int)$paymentId, (string)$status, 'gateway_update_status', [
            'gateway_ref' => (string)$gatewayRef,
            'note' => (string)$note,
        ]);
        if (!$transition['ok']) {
            throw new RuntimeException((string)$transition['message']);
        }
    }

    private static function findLatestPendingPaymentId($conn, $bookingId, $method) {
        $stmt = $conn->prepare('SELECT payment_id FROM payments WHERE booking_id = ? AND payment_method = ? ORDER BY payment_id DESC LIMIT 1');
        $stmt->execute([(int)$bookingId, (string)$method]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['payment_id'] ?? 0);
    }

    private static function findReusableInFlightPaymentId($conn, $bookingId, $method) {
        $stmt = $conn->prepare('SELECT payment_id FROM payments WHERE booking_id = ? AND payment_method = ? AND status IN (?, ?) ORDER BY payment_id DESC LIMIT 1');
        $stmt->execute([(int)$bookingId, (string)$method, Payment::STATUS_TAO_MOI, Payment::STATUS_DANG_XU_LY]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['payment_id'] ?? 0);
    }

    private static function countPaymentLogs($conn, $paymentId, $action) {
        $stmt = $conn->prepare('SELECT COUNT(*) AS c FROM payment_logs WHERE payment_id = ? AND action = ?');
        $stmt->execute([(int)$paymentId, (string)$action]);
        return (int)$stmt->fetchColumn();
    }

    private static function getPaymentStatus($conn, $paymentId) {
        $stmt = $conn->prepare('SELECT status FROM payments WHERE payment_id = ? LIMIT 1');
        $stmt->execute([(int)$paymentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (string)($row['status'] ?? '');
    }

    private static function existsPaymentFinanceTransaction($conn, $bookingId) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM giao_dich_tai_chinh WHERE booking_id = ? AND mo_ta = ?");
        $stmt->execute([(int)$bookingId, 'Thanh toan online booking #' . (int)$bookingId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0) > 0;
    }

    private static function updateBookingPaymentStatusColumnIfExists($conn, $bookingId, $status) {
        if (dbColumnExists('booking', 'trang_thai_thanh_toan', $conn)) {
            $stmt = $conn->prepare('UPDATE booking SET trang_thai_thanh_toan = ? WHERE booking_id = ?');
            $stmt->execute([(string)$status, (int)$bookingId]);
        }
    }

    private static function buildTxnRef($bookingId, $paymentId) {
        return 'BK' . (int)$bookingId . 'P' . (int)$paymentId . 'T' . time();
    }

    private static function parseTxnRef($txnRef) {
        $result = ['booking_id' => 0, 'payment_id' => 0];
        if (preg_match('/BK(\d+)P(\d+)T\d+/', (string)$txnRef, $m)) {
            $result['booking_id'] = (int)$m[1];
            $result['payment_id'] = (int)$m[2];
        }
        return $result;
    }

    private static function isVnpayConfigured() {
        return VNPAY_TMN_CODE !== '' && VNPAY_HASH_SECRET !== '';
    }

    private static function buildVnpayUrl($bookingId, $amount, $txnRef, $method, $returnAct, $returnTourId = 0) {
        $vnpData = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => VNPAY_TMN_CODE,
            'vnp_Amount' => (int)round($amount * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toan booking #' . (int)$bookingId,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => self::buildReturnUrl($returnAct, $returnTourId, $bookingId),
            'vnp_IpnUrl' => VNPAY_IPN_URL,
            'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis')
        ];

        ksort($vnpData);
        $query = [];
        foreach ($vnpData as $key => $value) {
            $query[] = urlencode($key) . '=' . urlencode((string)$value);
        }
        $hashData = implode('&', $query);
        $secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);

        return VNPAY_URL . '?' . $hashData . '&vnp_SecureHash=' . $secureHash;
    }

    private static function verifyVnpaySignature($params) {
        if (!self::isVnpayConfigured()) {
            return false;
        }

        $vnpSecureHash = $params['vnp_SecureHash'] ?? '';
        if ($vnpSecureHash === '') {
            return false;
        }

        $inputData = [];
        foreach ($params as $key => $value) {
            if (strpos($key, 'vnp_') === 0 && $key !== 'vnp_SecureHash' && $key !== 'vnp_SecureHashType') {
                $inputData[$key] = $value;
            }
        }

        ksort($inputData);
        $query = [];
        foreach ($inputData as $key => $value) {
            $query[] = urlencode($key) . '=' . urlencode((string)$value);
        }
        $hashData = implode('&', $query);
        $secureHash = hash_hmac('sha512', $hashData, VNPAY_HASH_SECRET);

        return hash_equals($secureHash, (string)$vnpSecureHash);
    }

    private static function sendPaymentSuccessEmail($booking, $paymentId, $gatewayRef) {
        if (empty($booking['email'])) {
            return;
        }

        require_once __DIR__ . '/../commons/mail.php';
        $to = (string)$booking['email'];
        $subject = 'Xac nhan thanh toan booking #' . (int)($booking['booking_id'] ?? 0);
        $body = "Xin chao " . ($booking['ho_ten'] ?? 'Quy khach') . ",\n\n"
            . "He thong da ghi nhan thanh toan thanh cong cho booking #" . (int)($booking['booking_id'] ?? 0) . ".\n"
            . "Tour: " . ($booking['ten_tour'] ?? 'N/A') . "\n"
            . "So tien: " . number_format((float)($booking['tong_tien'] ?? 0)) . " VND\n"
            . "Ma giao dich: " . ($gatewayRef !== '' ? $gatewayRef : ('PAY-' . (int)$paymentId)) . "\n\n"
            . "Cam on ban da su dung dich vu.";

        if (function_exists('sendInvoiceEmail')) {
            sendInvoiceEmail($to, $subject, $body);
        }
    }

    private static function buildReturnUrl($returnAct, $returnTourId, $bookingId) {
        $url = VNPAY_RETURN_URL . '&booking_id=' . (int)$bookingId . '&method=VNPay&return_act=' . urlencode($returnAct);
        if ($returnTourId > 0) {
            $url .= '&return_tour_id=' . (int)$returnTourId;
        }
        return $url;
    }
}
