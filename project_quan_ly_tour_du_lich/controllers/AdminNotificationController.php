<?php
/**
 * AdminNotificationController
 * Manages admin notification state, SSE stream, and notification settings.
 */
class AdminNotificationController {

    public function __construct() {
        requireRole('Admin');
    }

    private function requirePostCsrf(string $redirectAct = 'admin/notificationSettings'): void {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF token không hợp lệ.';
            header('Location: index.php?act=' . $redirectAct);
            exit;
        }
    }

    // ========== NOTIFICATION STATE HELPERS ==========

    public function initAdminNotificationState(?PDO $conn = null) {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $state = getAdminNotificationState($userId, $conn);
        $_SESSION['admin_notifications'] = $state;
        return $state;
    }

    private function persistAdminNotificationState(array $updates, ?PDO $conn = null) {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $state = saveAdminNotificationState($userId, $updates, $conn);
        $_SESSION['admin_notifications'] = $state;
        return $state;
    }

    private function markAdminPaymentNotificationsSeen(?PDO $conn = null) {
        $pdo = $conn instanceof PDO ? $conn : connectDB();
        $maxPaymentId = (int)$pdo->query("SELECT COALESCE(MAX(payment_id), 0) FROM payments")->fetchColumn();
        return $this->persistAdminNotificationState([
            'payments_last_seen_id' => $maxPaymentId,
        ], $pdo);
    }

    public function markAdminReviewNotificationsSeen(?PDO $conn = null) {
        $pdo = $conn instanceof PDO ? $conn : connectDB();
        $maxReviewId = (int)$pdo->query("SELECT COALESCE(MAX(danh_gia_id), 0) FROM danh_gia")->fetchColumn();
        return $this->persistAdminNotificationState([
            'reviews_last_seen_id' => $maxReviewId,
        ], $pdo);
    }

    public function markAdminDashboardNotificationsSeen(?PDO $conn = null) {
        $pdo = $conn instanceof PDO ? $conn : connectDB();
        $baseline = getAdminNotificationBaseline($pdo);
        return $this->persistAdminNotificationState([
            'payments_last_seen_id' => $baseline['payments_last_seen_id'],
            'reviews_last_seen_id' => $baseline['reviews_last_seen_id'],
        ], $pdo);
    }

    private function getAdminNotificationPayload(PDO $conn) {
        require_once 'models/ThongBao.php';
        $state = $this->initAdminNotificationState($conn);

        $paymentsLastSeenId = (int)($state['payments_last_seen_id'] ?? 0);
        $reviewsLastSeenId = (int)($state['reviews_last_seen_id'] ?? 0);

        $paymentStmt = $conn->prepare("SELECT COUNT(*) FROM payments WHERE payment_id > ?");
        $paymentStmt->execute([$paymentsLastSeenId]);
        $paymentCount = (int)$paymentStmt->fetchColumn();

        $reviewStmt = $conn->prepare("SELECT COUNT(*) FROM danh_gia WHERE danh_gia_id > ?");
        $reviewStmt->execute([$reviewsLastSeenId]);
        $reviewCount = (int)$reviewStmt->fetchColumn();

        $thongBaoModel = new ThongBao();
        $requestCount = (int)$thongBaoModel->countYeuCauTourChuaXuLy();

        return [
            'success' => true,
            'payments' => $paymentCount,
            'reviews' => $reviewCount,
            'requests' => $requestCount,
            'dashboard' => $paymentCount + $reviewCount + $requestCount,
            'sound_enabled' => ((int)($state['sound_enabled'] ?? 1) === 1) ? 1 : 0,
        ];
    }

    // ========== PUBLIC ACTIONS ==========

    public function notificationSettings() {
        $state = $this->initAdminNotificationState();

        $pageTitle = 'Cài đặt thông báo';
        $currentPage = 'notificationSettings';
        $soundEnabled = ((int)($state['sound_enabled'] ?? 1) === 1);

        require 'views/admin/notification_settings.php';
    }

    public function saveNotificationSettings() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/notificationSettings');
            exit;
        }
        $this->requirePostCsrf('admin/notificationSettings');

        $soundEnabled = isset($_POST['sound_enabled']) ? 1 : 0;
        $this->persistAdminNotificationState(['sound_enabled' => $soundEnabled]);
        $_SESSION['success'] = 'Đã cập nhật cài đặt thông báo.';

        header('Location: index.php?act=admin/notificationSettings');
        exit;
    }

    public function markNotificationsReadAll() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/notificationSettings');
            exit;
        }
        $this->requirePostCsrf('admin/notificationSettings');

        try {
            $conn = connectDB();
            $this->markAdminDashboardNotificationsSeen($conn);
        } catch (Throwable $e) {
            $this->initAdminNotificationState();
        }

        if ((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            header('Content-Type: application/json; charset=utf-8');
            try {
                $conn = connectDB();
                echo json_encode($this->getAdminNotificationPayload($conn), JSON_UNESCAPED_UNICODE);
                exit;
            } catch (Throwable $e) {
                echo json_encode([
                    'success' => true,
                    'payments' => 0,
                    'reviews' => 0,
                    'requests' => 0,
                    'dashboard' => 0,
                    'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $_SESSION['success'] = 'Đã đánh dấu tất cả thông báo là đã xem.';
        header('Location: index.php?act=admin/notificationSettings');
        exit;
    }

    public function notificationCounts() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $conn = connectDB();
            echo json_encode($this->getAdminNotificationPayload($conn), JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'payments' => 0,
                'reviews' => 0,
                'requests' => 0,
                'dashboard' => 0,
                'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function notificationStream() {
        @set_time_limit(0);
        @ignore_user_abort(true);

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $this->initAdminNotificationState();

        // Giải phóng session file lock ngay sau khi đọc xong session data.
        session_write_close();

        try {
            $startedAt = time();
            $lastPayloadHash = '';
            $lastMetaHash = '';
            $cachedPayload = [
                'success' => true,
                'payments' => 0,
                'reviews' => 0,
                'requests' => 0,
                'dashboard' => 0,
                'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
            ];

            while (!connection_aborted()) {
                if ((time() - $startedAt) > 300) {
                    echo "event: close\n";
                    echo "data: {}\n\n";
                    @ob_flush();
                    @flush();
                    break;
                }

                $conn = connectDB();

                $metaStmt = $conn->query("SELECT
                    (SELECT COALESCE(MAX(payment_id), 0) FROM payments) AS payment_max,
                    (SELECT COALESCE(MAX(danh_gia_id), 0) FROM danh_gia) AS review_max,
                    (SELECT COALESCE(MAX(id), 0) FROM thong_bao WHERE tieu_de = 'Yêu cầu tour theo mong muốn' AND vai_tro_nhan = 'Admin') AS request_max,
                    (SELECT COUNT(*) FROM thong_bao WHERE tieu_de = 'Yêu cầu tour theo mong muốn' AND vai_tro_nhan = 'Admin' AND trang_thai = 'DaGui') AS request_pending
                ");
                $meta = $metaStmt->fetch(PDO::FETCH_ASSOC) ?: [];
                $metaHash = md5(
                    (string)($meta['payment_max'] ?? '') . '|' .
                    (string)($meta['review_max'] ?? '') . '|' .
                    (string)($meta['request_max'] ?? '') . '|' .
                    (string)($meta['request_pending'] ?? '') . '|' .
                    (string)($_SESSION['admin_notifications']['sound_enabled'] ?? 1)
                );

                if ($metaHash !== $lastMetaHash) {
                    $cachedPayload = $this->getAdminNotificationPayload($conn);
                    $lastMetaHash = $metaHash;
                }

                $conn = null;
                releasePDOConnection();

                $payloadHash = md5(json_encode($cachedPayload, JSON_UNESCAPED_UNICODE));
                if ($payloadHash !== $lastPayloadHash) {
                    echo "event: notification\n";
                    echo 'data: ' . json_encode($cachedPayload, JSON_UNESCAPED_UNICODE) . "\n\n";
                    $lastPayloadHash = $payloadHash;
                } else {
                    echo ": ping\n\n";
                }

                @ob_flush();
                @flush();
                sleep(2);
            }
            exit;
        } catch (Throwable $e) {
            echo "event: notification\n";
            echo 'data: ' . json_encode([
                'success' => false,
                'payments' => 0,
                'reviews' => 0,
                'requests' => 0,
                'dashboard' => 0,
                'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
            ], JSON_UNESCAPED_UNICODE) . "\n\n";
            @ob_flush();
            @flush();
            exit;
        }
    }
}
