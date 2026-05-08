<?php
/**
 * AdminAutomationController
 * Manages admin automation jobs, dashboard, and decision assist.
 */
class AdminAutomationController {

    public function __construct() {
        requireRole('Admin');
    }

    private function requirePostCsrf(string $redirectAct = 'admin/automationDashboard'): void {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF token không hợp lệ.';
            header('Location: index.php?act=' . $redirectAct);
            exit;
        }
    }

    public function buildAutomationSnapshot(): array {
        $snapshot = [
            'recentEvents24h' => 0,
            'highSeverity24h' => 0,
            'openDecisionAssist' => 0,
            'criticalTours' => 0,
            'highPriorityBookings' => 0,
            'lastRun' => null,
            'recentEvents' => [],
        ];

        try {
            $conn = connectDB();

            $snapshot['recentEvents24h'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)")
                ->fetchColumn();

            $snapshot['highSeverity24h'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events WHERE severity = 'high' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)")
                ->fetchColumn();

            $snapshot['openDecisionAssist'] = (int)$conn
                ->query("SELECT COUNT(*) FROM admin_decision_assist WHERE status = 'open'")
                ->fetchColumn();

            $snapshot['criticalTours'] = (int)$conn
                ->query("SELECT COUNT(*) FROM tour_health_score WHERE health_level = 'Critical'")
                ->fetchColumn();

            $snapshot['highPriorityBookings'] = (int)$conn
                ->query("SELECT COUNT(*) FROM booking_priority WHERE priority_label = 'High'")
                ->fetchColumn();

            $lastRunStmt = $conn->query("SELECT job_name, is_success, created_at
                                         FROM automation_job_runs
                                         ORDER BY run_id DESC
                                         LIMIT 1");
            $snapshot['lastRun'] = $lastRunStmt ? $lastRunStmt->fetch(PDO::FETCH_ASSOC) : null;

            $eventsStmt = $conn->query("SELECT title, severity, created_at
                                        FROM automation_events
                                        ORDER BY event_id DESC
                                        LIMIT 5");
            $snapshot['recentEvents'] = $eventsStmt ? $eventsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            // Keep default snapshot values when automation tables are unavailable.
        }

        return $snapshot;
    }

    // ========== PUBLIC ACTIONS ==========

    public function automationStatus() {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $data = [
            'eventsCount'        => 0,
            'highSeverityCount'  => 0,
            'decisionCount'      => 0,
            'tourHealthCount'    => 0,
            'priorityCount'      => 0,
            'latestRun'          => null,
            'automationEnabled'  => true,
            'automationUpdatedAt'=> null,
            'schedulerInterval'  => 15,
            'timestamp'          => date('Y-m-d H:i:s'),
        ];

        try {
            $conn = connectDB();
            require_once __DIR__ . '/../services/AdminAutomationService.php';
            $service = new AdminAutomationService($conn);
            $controlState = $service->getAutomationControlState();
            $data['automationEnabled'] = !empty($controlState['enabled']);
            $data['automationUpdatedAt'] = $controlState['updated_at'] ?? null;

            $data['eventsCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events")
                ->fetchColumn();

            $data['highSeverityCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events WHERE severity = 'high'")
                ->fetchColumn();

            $data['decisionCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM admin_decision_assist WHERE status = 'open'")
                ->fetchColumn();

            $data['tourHealthCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM tour_health_score WHERE health_level IN ('Watch','Critical')")
                ->fetchColumn();

            $data['priorityCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM booking_priority WHERE priority_label = 'High'")
                ->fetchColumn();

            $row = $conn->query("SELECT job_name, is_success, created_at
                                  FROM automation_job_runs
                                  ORDER BY run_id DESC
                                  LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $data['latestRun'] = $row ?: null;
        } catch (Throwable $e) {
            // return defaults
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function automationDashboard() {
        $conn = connectDB();
        require_once __DIR__ . '/../services/AdminAutomationService.php';
        $service = new AdminAutomationService($conn);

        $jobRuns = [];
        $events = [];
        $priorityBookings = [];
        $tourHealth = [];
        $decisionAssist = [];
        $automationControlState = $service->getAutomationControlState();

        try {
            $stmt = $conn->query("SELECT run_id, job_name, is_success, affected_count, message, duration_ms, created_at
                                  FROM automation_job_runs
                                  ORDER BY run_id DESC
                                  LIMIT 40");
            $jobRuns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $jobRuns = [];
        }

        try {
            $stmt = $conn->query("SELECT event_id, job_name, severity, title, message, created_at
                                  FROM automation_events
                                  ORDER BY event_id DESC
                                  LIMIT 40");
            $events = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $events = [];
        }

        try {
            $stmt = $conn->query("SELECT bp.booking_id, bp.priority_label, bp.score, bp.computed_at,
                                         b.ngay_khoi_hanh, b.tong_tien, b.trang_thai
                                  FROM booking_priority bp
                                  LEFT JOIN booking b ON b.booking_id = bp.booking_id
                                  WHERE bp.priority_label = 'High'
                                  ORDER BY bp.score DESC, bp.computed_at DESC
                                  LIMIT 30");
            $priorityBookings = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $priorityBookings = [];
        }

        try {
            $stmt = $conn->query("SELECT th.tour_id, th.score, th.health_level, th.computed_at, t.ten_tour
                                  FROM tour_health_score th
                                  LEFT JOIN tour t ON t.tour_id = th.tour_id
                                  WHERE th.health_level IN ('Watch', 'Critical')
                                  ORDER BY th.score ASC, th.computed_at DESC
                                  LIMIT 30");
            $tourHealth = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $tourHealth = [];
        }

        try {
            $stmt = $conn->query("SELECT assist_id, entity_type, entity_id, recommendation_text, status, updated_at
                                  FROM admin_decision_assist
                                  WHERE status = 'open'
                                  ORDER BY updated_at DESC
                                  LIMIT 40");
            $decisionAssist = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $decisionAssist = [];
        }

        $pageTitle = 'Trung tâm Tự động hóa Admin';
        $currentPage = 'automation';
        $availableJobs = [
            'all',
            'sla_tour_requests',
            'booking_priority',
            'reconcile_digest',
            'self_heal_pending_payments',
            'webhook_anomaly',
            'debt_reminder',
            'departure_readiness',
            'tour_health_score',
            'admin_inbox_digest',
            'decision_assist',
        ];

        require 'views/admin/automation_dashboard.php';
    }

    public function toggleAutomation() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }
        $this->requirePostCsrf('admin/automationDashboard');

        $enabled = requestString('enabled', '1', 'POST') === '1';

        require_once __DIR__ . '/../services/AdminAutomationService.php';
        $conn = connectDB();
        $service = new AdminAutomationService($conn);
        $service->setAutomationEnabled($enabled);

        $_SESSION['success'] = $enabled
            ? 'Đã bật lại toàn bộ tự động hóa.'
            : 'Đã tạm tắt toàn bộ tự động hóa. Job tay và job nền sẽ bị bỏ qua cho đến khi bật lại.';

        header('Location: index.php?act=admin/automationDashboard');
        exit;
    }

    public function runAutomationJob() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }
        $this->requirePostCsrf('admin/automationDashboard');

        $job = requestString('job', 'all', 'POST');
        $availableJobs = [
            'all',
            'sla_tour_requests',
            'booking_priority',
            'reconcile_digest',
            'self_heal_pending_payments',
            'webhook_anomaly',
            'debt_reminder',
            'departure_readiness',
            'tour_health_score',
            'admin_inbox_digest',
            'decision_assist',
        ];

        if (!in_array($job, $availableJobs, true)) {
            $_SESSION['error'] = 'Job tự động hóa không hợp lệ.';
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }

        require_once __DIR__ . '/../services/AdminAutomationService.php';
        $conn = connectDB();
        $service = new AdminAutomationService($conn);

        if (!$service->isAutomationEnabled()) {
            $_SESSION['error'] = 'Tự động hóa đang tạm tắt. Hãy bật lại trước khi chạy job.';
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }

        if ($job === 'all') {
            $results = $service->runAll();
            $failed = 0;
            $affected = 0;
            foreach ($results as $result) {
                if (empty($result['ok'])) {
                    $failed++;
                }
                $affected += (int)($result['affected'] ?? 0);
            }
            $_SESSION[$failed > 0 ? 'error' : 'success'] = $failed > 0
                ? 'Đã chạy all jobs, có ' . $failed . ' job lỗi.'
                : 'Đã chạy all jobs thành công. Tổng tác động: ' . $affected . '.';
        } else {
            $result = $service->runJob($job);
            if (!empty($result['ok'])) {
                $_SESSION['success'] = 'Đã chạy job ' . $job . '. affected=' . (int)($result['affected'] ?? 0) . '.';
            } else {
                $_SESSION['error'] = 'Chạy job ' . $job . ' thất bại: ' . (string)($result['message'] ?? 'unknown error');
            }
        }

        header('Location: index.php?act=admin/automationDashboard');
        exit;
    }

    public function updateDecisionAssistStatus() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }
        $this->requirePostCsrf('admin/automationDashboard');

        $assistId = requestId('assist_id', 0, 'POST') ?? 0;
        $status = requestString('status', 'open', 'POST');
        if (!in_array($status, ['open', 'done', 'ignored'], true) || $assistId <= 0) {
            $_SESSION['error'] = 'Thông tin cập nhật gợi ý không hợp lệ.';
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }

        $conn = connectDB();
        $stmt = $conn->prepare("UPDATE admin_decision_assist
                               SET status = ?, updated_at = NOW()
                               WHERE assist_id = ?");
        $stmt->execute([$status, $assistId]);

        $_SESSION['success'] = 'Đã cập nhật trạng thái gợi ý #' . $assistId . '.';
        header('Location: index.php?act=admin/automationDashboard');
        exit;
    }
}
