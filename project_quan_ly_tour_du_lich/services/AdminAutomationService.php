<?php

require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../services/PaymentReconcileService.php';

class AdminAutomationService {
    private $conn;
    private const ENABLED_SETTING_KEY = 'automation_enabled';

    public function __construct($conn) {
        $this->conn = $conn;
        $this->ensureAutomationTables();
    }

    public function getAutomationControlState() {
        $enabled = $this->isAutomationEnabled();
        $updatedAt = null;

        try {
            $stmt = $this->conn->prepare(
                "SELECT updated_at
                 FROM automation_settings
                 WHERE setting_key = ?
                 LIMIT 1"
            );
            $stmt->execute([self::ENABLED_SETTING_KEY]);
            $updatedAt = $stmt->fetchColumn() ?: null;
        } catch (Throwable $e) {
            $updatedAt = null;
        }

        return [
            'enabled' => $enabled,
            'updated_at' => $updatedAt,
        ];
    }

    public function isAutomationEnabled() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT setting_value
                 FROM automation_settings
                 WHERE setting_key = ?
                 LIMIT 1"
            );
            $stmt->execute([self::ENABLED_SETTING_KEY]);
            $value = $stmt->fetchColumn();
            if ($value === false) {
                $this->setAutomationEnabled(true);
                return true;
            }

            return ((string)$value) !== '0';
        } catch (Throwable $e) {
            return true;
        }
    }

    public function setAutomationEnabled($enabled) {
        $normalized = $enabled ? '1' : '0';
        $stmt = $this->conn->prepare(
            "INSERT INTO automation_settings (setting_key, setting_value, updated_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                updated_at = VALUES(updated_at)"
        );
        $stmt->execute([self::ENABLED_SETTING_KEY, $normalized]);
    }

    public function runAll() {
        $jobs = [
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

        $results = [];
        foreach ($jobs as $job) {
            $results[] = $this->runJob($job);
        }

        return $results;
    }

    public function runJob($jobName) {
        $jobName = trim((string)$jobName);
        $startedAt = microtime(true);

        if (!$this->isAutomationEnabled()) {
            $durationMs = round((microtime(true) - $startedAt) * 1000, 1);
            $message = 'Automation is temporarily disabled by admin.';
            $this->logRun($jobName, true, 0, $message, $durationMs);

            return [
                'ok' => true,
                'skipped' => true,
                'job' => $jobName,
                'message' => $message,
                'affected' => 0,
                'duration_ms' => $durationMs,
            ];
        }

        try {
            $result = match ($jobName) {
                'sla_tour_requests' => $this->runSlaTourRequests(),
                'booking_priority' => $this->runBookingPriority(),
                'reconcile_digest' => $this->runReconcileDigest(),
                'self_heal_pending_payments' => $this->runSelfHealPendingPayments(),
                'webhook_anomaly' => $this->runWebhookAnomaly(),
                'debt_reminder' => $this->runDebtReminder(),
                'departure_readiness' => $this->runDepartureReadiness(),
                'tour_health_score' => $this->runTourHealthScore(),
                'admin_inbox_digest' => $this->runAdminInboxDigest(),
                'decision_assist' => $this->runDecisionAssist(),
                default => [
                    'ok' => false,
                    'job' => $jobName,
                    'message' => 'Unknown job',
                    'affected' => 0,
                ],
            };

            $durationMs = round((microtime(true) - $startedAt) * 1000, 1);
            $this->logRun($jobName, (bool)($result['ok'] ?? false), (int)($result['affected'] ?? 0), (string)($result['message'] ?? ''), $durationMs);
            $result['duration_ms'] = $durationMs;
            return $result;
        } catch (Throwable $e) {
            $durationMs = round((microtime(true) - $startedAt) * 1000, 1);
            $this->logRun($jobName, false, 0, $e->getMessage(), $durationMs);
            return [
                'ok' => false,
                'job' => $jobName,
                'message' => $e->getMessage(),
                'affected' => 0,
                'duration_ms' => $durationMs,
            ];
        }
    }

    private function runSlaTourRequests() {
        $sql = "SELECT id, created_at
                FROM thong_bao
                WHERE vai_tro_nhan = 'Admin'
                  AND tieu_de = 'Yêu cầu tour theo mong muốn'
                  AND trang_thai = 'DaGui'
                  AND created_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                ORDER BY created_at ASC
                LIMIT 500";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $alerts = 0;
        foreach ($rows as $row) {
            $requestId = (int)($row['id'] ?? 0);
            $createdAt = strtotime((string)($row['created_at'] ?? ''));
            if ($requestId <= 0 || $createdAt === false) {
                continue;
            }

            $ageHours = (time() - $createdAt) / 3600;
            $level = 0;
            if ($ageHours >= 24) {
                $level = 3;
            } elseif ($ageHours >= 6) {
                $level = 2;
            } elseif ($ageHours >= 2) {
                $level = 1;
            }

            if ($level <= 0) {
                continue;
            }

            $severity = $level >= 3 ? 'high' : ($level === 2 ? 'medium' : 'low');
            $title = 'SLA yêu cầu tour quá hạn';
            $message = 'Yêu cầu #' . $requestId . ' đã chờ ' . (int)floor($ageHours) . ' giờ, cần admin xử lý.';
            $eventKey = 'sla_request_' . $requestId . '_L' . $level;

            if ($this->recordEvent($eventKey, 'sla_tour_requests', $severity, $title, $message, [
                'request_id' => $requestId,
                'age_hours' => round($ageHours, 1),
                'level' => $level,
            ], true)) {
                $alerts++;
            }
        }

        return [
            'ok' => true,
            'job' => 'sla_tour_requests',
            'message' => 'SLA scan completed',
            'affected' => $alerts,
        ];
    }

    private function runBookingPriority() {
        $sql = "SELECT booking_id, tong_tien, trang_thai, ngay_khoi_hanh, khach_hang_id
                FROM booking
                WHERE trang_thai IN ('ChoXacNhan', 'DaCoc')
                  AND ngay_khoi_hanh IS NOT NULL
                  AND ngay_khoi_hanh >= CURDATE()
                ORDER BY ngay_khoi_hanh ASC
                LIMIT 2000";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updated = 0;
        foreach ($rows as $row) {
            $bookingId = (int)($row['booking_id'] ?? 0);
            if ($bookingId <= 0) {
                continue;
            }

            $score = 0;
            $reasons = [];
            $tongTien = (float)($row['tong_tien'] ?? 0);
            $status = (string)($row['trang_thai'] ?? '');
            $daysLeft = $this->daysFromToday((string)($row['ngay_khoi_hanh'] ?? ''));

            if ($daysLeft !== null) {
                if ($daysLeft <= 3) {
                    $score += 40;
                    $reasons[] = 'Khoi hanh <= 3 ngay';
                } elseif ($daysLeft <= 7) {
                    $score += 25;
                    $reasons[] = 'Khoi hanh <= 7 ngay';
                }
            }

            if ($tongTien >= 20000000) {
                $score += 30;
                $reasons[] = 'Gia tri booking cao >= 20tr';
            } elseif ($tongTien >= 10000000) {
                $score += 20;
                $reasons[] = 'Gia tri booking >= 10tr';
            } elseif ($tongTien >= 5000000) {
                $score += 10;
                $reasons[] = 'Gia tri booking >= 5tr';
            }

            if ($status === 'ChoXacNhan') {
                $score += 15;
                $reasons[] = 'Dang cho xac nhan';
            }

            $priority = 'Low';
            if ($score >= 70) {
                $priority = 'High';
            } elseif ($score >= 40) {
                $priority = 'Medium';
            }

            $upsert = $this->conn->prepare(
                "INSERT INTO booking_priority (booking_id, priority_label, score, reasons_json, computed_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    priority_label = VALUES(priority_label),
                    score = VALUES(score),
                    reasons_json = VALUES(reasons_json),
                    computed_at = VALUES(computed_at)"
            );
            $upsert->execute([
                $bookingId,
                $priority,
                $score,
                json_encode($reasons, JSON_UNESCAPED_UNICODE),
            ]);
            $updated++;

            if ($priority === 'High' && $daysLeft !== null && $daysLeft <= 3) {
                $this->recordEvent(
                    'booking_priority_high_' . $bookingId . '_' . date('Ymd'),
                    'booking_priority',
                    'medium',
                    'Booking ưu tiên cao',
                    'Booking #' . $bookingId . ' cần xử lý gấp (điểm ' . $score . ').',
                    [
                        'booking_id' => $bookingId,
                        'score' => $score,
                        'priority' => $priority,
                        'days_left' => $daysLeft,
                    ],
                    true
                );
            }
        }

        return [
            'ok' => true,
            'job' => 'booking_priority',
            'message' => 'Booking priority computed',
            'affected' => $updated,
        ];
    }

    private function runReconcileDigest() {
        PaymentReconcileService::runAutoReconcileTick($this->conn);
        $report = PaymentReconcileService::refreshDailyMismatchReportCache($this->conn);

        $warning = (int)($report['warning'] ?? 0);
        $total = (int)($report['total'] ?? 0);
        $message = 'Doi soat hom nay: tong=' . $total
            . ', canh bao=' . $warning
            . ', thieu_thu=' . (int)($report['thieu_thu'] ?? 0)
            . ', thua_thu=' . (int)($report['thua_thu'] ?? 0)
            . ', lech_tien=' . (int)($report['lech_tien'] ?? 0) . '.';

        $this->recordEvent(
            'reconcile_digest_' . date('Ymd'),
            'reconcile_digest',
            $warning > 0 ? 'medium' : 'low',
            'Daily reconcile digest',
            $message,
            $report,
            true
        );

        return [
            'ok' => true,
            'job' => 'reconcile_digest',
            'message' => 'Reconcile digest generated',
            'affected' => 1,
        ];
    }

    private function runSelfHealPendingPayments() {
        Payment::ensureStateMachineSchema($this->conn);

        $sql = "SELECT payment_id, payment_date
                FROM payments
                WHERE status = 'DangXuLy'
                  AND payment_date <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                ORDER BY payment_date ASC
                LIMIT 500";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $expired = 0;
        foreach ($rows as $row) {
            $paymentId = (int)($row['payment_id'] ?? 0);
            if ($paymentId <= 0) {
                continue;
            }

            $transition = Payment::transitionStatus(
                $this->conn,
                $paymentId,
                Payment::STATUS_HET_HAN,
                'auto_self_heal_pending_timeout',
                [
                    'payment_date' => (string)($row['payment_date'] ?? ''),
                    'threshold_minutes' => 30,
                ]
            );
            if (!empty($transition['ok'])) {
                $expired++;
            }
        }

        if ($expired > 0) {
            $this->recordEvent(
                'self_heal_timeout_' . date('YmdH'),
                'self_heal_pending_payments',
                'medium',
                'Auto timeout payment treo',
                'Da chuyen ' . $expired . ' payment DangXuLy sang HetHan do qua 30 phut.',
                ['expired' => $expired],
                true
            );
        }

        return [
            'ok' => true,
            'job' => 'self_heal_pending_payments',
            'message' => 'Pending payment self-heal completed',
            'affected' => $expired,
        ];
    }

    private function runWebhookAnomaly() {
        $unmatchedCount = 0;
        $failedIdemCount = 0;

        try {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM bank_webhook_unmatched WHERE processed = 0");
            $unmatchedCount = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $unmatchedCount = 0;
        }

        try {
            $stmt = $this->conn->query("SELECT COUNT(*) FROM payment_idempotency WHERE status = 'failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
            $failedIdemCount = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $failedIdemCount = 0;
        }

        $anomalyScore = 0;
        if ($unmatchedCount >= 5) {
            $anomalyScore += 1;
        }
        if ($failedIdemCount >= 3) {
            $anomalyScore += 1;
        }

        $affected = 0;
        if ($anomalyScore > 0) {
            $severity = $anomalyScore >= 2 ? 'high' : 'medium';
            if ($this->recordEvent(
                'webhook_anomaly_' . date('YmdH'),
                'webhook_anomaly',
                $severity,
                'Canh bao bat thuong webhook',
                'Unmatched=' . $unmatchedCount . ', IdempotencyFailed24h=' . $failedIdemCount . '. Vui long kiem tra.',
                [
                    'unmatched_queue' => $unmatchedCount,
                    'idempotency_failed_24h' => $failedIdemCount,
                ],
                true
            )) {
                $affected = 1;
            }
        }

        return [
            'ok' => true,
            'job' => 'webhook_anomaly',
            'message' => 'Webhook anomaly scan completed',
            'affected' => $affected,
        ];
    }

    private function runDebtReminder() {
        $overdueHDV = 0;
        $pendingSupplier = 0;

        try {
            $stmt = $this->conn->query(
                "SELECT COUNT(*)
                 FROM cong_no_hdv c
                 LEFT JOIN (
                    SELECT cong_no_hdv_id, COALESCE(SUM(so_tien),0) AS da_tra
                    FROM lich_su_thanh_toan_hdv
                    GROUP BY cong_no_hdv_id
                 ) p ON p.cong_no_hdv_id = c.id
                 WHERE c.han_thanh_toan IS NOT NULL
                   AND c.han_thanh_toan < CURDATE()
                   AND (COALESCE(c.so_tien,0) - COALESCE(p.da_tra,0)) > 0"
            );
            $overdueHDV = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $overdueHDV = 0;
        }

        try {
            $stmt = $this->conn->query(
                "SELECT COUNT(*)
                 FROM phan_bo_dich_vu
                 WHERE trang_thai = 'ChoXacNhan'
                   AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)"
            );
            $pendingSupplier = (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            $pendingSupplier = 0;
        }

        $affected = 0;
        if ($overdueHDV > 0 || $pendingSupplier > 0) {
            if ($this->recordEvent(
                'debt_reminder_' . date('Ymd'),
                'debt_reminder',
                ($overdueHDV > 0 ? 'medium' : 'low'),
                'Nhac han cong no va doi tac',
                'Cong no HDV qua han=' . $overdueHDV . ', dich vu NCC cho xac nhan>3 ngay=' . $pendingSupplier . '.',
                [
                    'overdue_hdv' => $overdueHDV,
                    'pending_supplier_confirmation' => $pendingSupplier,
                ],
                true
            )) {
                $affected = 1;
            }
        }

        return [
            'ok' => true,
            'job' => 'debt_reminder',
            'message' => 'Debt reminder scan completed',
            'affected' => $affected,
        ];
    }

    private function runDepartureReadiness() {
        $sql = "SELECT id, tour_id, ngay_khoi_hanh, hdv_id, trang_thai
                FROM lich_khoi_hanh
                WHERE ngay_khoi_hanh BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                  AND trang_thai IN ('SapKhoiHanh', 'DangChay')
                ORDER BY ngay_khoi_hanh ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $issues = 0;
        foreach ($schedules as $schedule) {
            $id = (int)($schedule['id'] ?? 0);
            $tourId = (int)($schedule['tour_id'] ?? 0);
            $ngayKhoiHanh = (string)($schedule['ngay_khoi_hanh'] ?? '');
            if ($id <= 0 || $tourId <= 0 || $ngayKhoiHanh === '') {
                continue;
            }

            $issueList = [];

            $hasHdv = ((int)($schedule['hdv_id'] ?? 0) > 0);
            if (!$hasHdv) {
                $stmtHdv = $this->conn->prepare("SELECT COUNT(*) FROM phan_bo_nhan_su WHERE lich_khoi_hanh_id = ? AND vai_tro = 'HDV' AND trang_thai = 'DaXacNhan'");
                $stmtHdv->execute([$id]);
                $hasHdv = ((int)$stmtHdv->fetchColumn() > 0);
            }
            if (!$hasHdv) {
                $issueList[] = 'thieu_hdv';
            }

            $stmtService = $this->conn->prepare("SELECT COUNT(*) FROM phan_bo_dich_vu WHERE lich_khoi_hanh_id = ? AND trang_thai = 'DaXacNhan'");
            $stmtService->execute([$id]);
            if ((int)$stmtService->fetchColumn() <= 0) {
                $issueList[] = 'thieu_dich_vu_xac_nhan';
            }

            $stmtBooking = $this->conn->prepare("SELECT COUNT(*) FROM booking WHERE tour_id = ? AND ngay_khoi_hanh = ? AND trang_thai IN ('ChoXacNhan','DaCoc','HoanTat')");
            $stmtBooking->execute([$tourId, $ngayKhoiHanh]);
            if ((int)$stmtBooking->fetchColumn() <= 0) {
                $issueList[] = 'khong_co_booking_hop_le';
            }

            foreach ($issueList as $issue) {
                if ($this->recordEvent(
                    'departure_readiness_' . $id . '_' . $issue . '_' . date('Ymd'),
                    'departure_readiness',
                    'high',
                    'Canh bao readiness lich khoi hanh',
                    'Lich #' . $id . ' (' . $ngayKhoiHanh . ') gap van de: ' . $issue . '.',
                    [
                        'lich_khoi_hanh_id' => $id,
                        'issue' => $issue,
                        'ngay_khoi_hanh' => $ngayKhoiHanh,
                    ],
                    true
                )) {
                    $issues++;
                }
            }
        }

        return [
            'ok' => true,
            'job' => 'departure_readiness',
            'message' => 'Departure readiness checked',
            'affected' => $issues,
        ];
    }

    private function runTourHealthScore() {
        $sql = "SELECT tour_id, ten_tour
                FROM tour
                WHERE trang_thai = 'HoatDong' OR trang_thai IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updated = 0;
        foreach ($tours as $tour) {
            $tourId = (int)($tour['tour_id'] ?? 0);
            if ($tourId <= 0) {
                continue;
            }

            $bookings60d = $this->countScalar("SELECT COUNT(*) FROM booking WHERE tour_id = ? AND ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)", [$tourId]);
            $cancelled60d = $this->countScalar("SELECT COUNT(*) FROM booking WHERE tour_id = ? AND ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND trang_thai = 'Huy'", [$tourId]);
            $avgRating = $this->floatScalar("SELECT COALESCE(AVG(diem), 0) FROM danh_gia WHERE loai_danh_gia = 'Tour' AND tour_id = ?", [$tourId]);

            $cancelRate = ($bookings60d > 0) ? ($cancelled60d / $bookings60d) : 0;

            $score = 50;
            $score += min(25, $bookings60d * 2);
            $score += min(25, max(0, ($avgRating - 3.0) * 12.5));
            $score -= min(35, $cancelRate * 100 * 0.7);
            $score = (int)max(0, min(100, round($score)));

            $level = 'Good';
            if ($score < 40) {
                $level = 'Critical';
            } elseif ($score < 60) {
                $level = 'Watch';
            }

            $upsert = $this->conn->prepare(
                "INSERT INTO tour_health_score (tour_id, score, health_level, metrics_json, computed_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    score = VALUES(score),
                    health_level = VALUES(health_level),
                    metrics_json = VALUES(metrics_json),
                    computed_at = VALUES(computed_at)"
            );
            $upsert->execute([
                $tourId,
                $score,
                $level,
                json_encode([
                    'bookings_60d' => $bookings60d,
                    'cancelled_60d' => $cancelled60d,
                    'cancel_rate' => round($cancelRate, 4),
                    'avg_rating' => round($avgRating, 2),
                ], JSON_UNESCAPED_UNICODE),
            ]);
            $updated++;

            if ($level === 'Critical') {
                $this->recordEvent(
                    'tour_health_critical_' . $tourId . '_' . date('Ymd'),
                    'tour_health_score',
                    'high',
                    'Tour health critical',
                    'Tour #' . $tourId . ' co health score thap: ' . $score . '.',
                    ['tour_id' => $tourId, 'score' => $score, 'level' => $level],
                    true
                );
            }
        }

        return [
            'ok' => true,
            'job' => 'tour_health_score',
            'message' => 'Tour health scores updated',
            'affected' => $updated,
        ];
    }

    private function runAdminInboxDigest() {
        $pendingTourRequests = $this->countScalar(
            "SELECT COUNT(*)
             FROM thong_bao
             WHERE vai_tro_nhan = 'Admin'
               AND tieu_de = 'Yêu cầu tour theo mong muốn'
               AND trang_thai = 'DaGui'"
        );

        $paymentWarnings24h = $this->countScalar(
            "SELECT COUNT(*)
             FROM payment_logs
             WHERE action IN ('AUTO_RECONCILE_WARN', 'STATE_TRANSITION_BLOCKED')
               AND log_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)"
        );

        $overdueDebt = $this->countScalar(
            "SELECT COUNT(*)
             FROM cong_no_hdv c
             LEFT JOIN (
                 SELECT cong_no_hdv_id, COALESCE(SUM(so_tien),0) AS da_tra
                 FROM lich_su_thanh_toan_hdv
                 GROUP BY cong_no_hdv_id
             ) p ON p.cong_no_hdv_id = c.id
             WHERE c.han_thanh_toan IS NOT NULL
               AND c.han_thanh_toan < CURDATE()
               AND (COALESCE(c.so_tien,0) - COALESCE(p.da_tra,0)) > 0"
        );

        $message = 'Inbox digest: yeu_cau_tour=' . $pendingTourRequests
            . ', payment_warnings_24h=' . $paymentWarnings24h
            . ', cong_no_qua_han=' . $overdueDebt . '.';

        $created = $this->recordEvent(
            'admin_inbox_digest_' . date('YmdH'),
            'admin_inbox_digest',
            ($pendingTourRequests + $paymentWarnings24h + $overdueDebt) > 0 ? 'medium' : 'low',
            'Admin inbox digest',
            $message,
            [
                'pending_tour_requests' => $pendingTourRequests,
                'payment_warnings_24h' => $paymentWarnings24h,
                'overdue_debt' => $overdueDebt,
            ],
            true
        );

        return [
            'ok' => true,
            'job' => 'admin_inbox_digest',
            'message' => 'Admin inbox digest generated',
            'affected' => $created ? 1 : 0,
        ];
    }

    private function runDecisionAssist() {
        $sql = "SELECT b.booking_id, b.trang_thai, b.ngay_khoi_hanh, b.tour_id, b.tong_tien
                FROM booking b
                WHERE b.trang_thai IN ('ChoXacNhan', 'DaCoc')
                  AND b.ngay_khoi_hanh IS NOT NULL
                  AND b.ngay_khoi_hanh >= CURDATE()
                ORDER BY b.ngay_khoi_hanh ASC
                LIMIT 1000";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $created = 0;
        foreach ($bookings as $booking) {
            $bookingId = (int)($booking['booking_id'] ?? 0);
            if ($bookingId <= 0) {
                continue;
            }

            $recommendations = [];
            $status = (string)($booking['trang_thai'] ?? '');
            $daysLeft = $this->daysFromToday((string)($booking['ngay_khoi_hanh'] ?? ''));

            $successfulPayments = $this->countScalar(
                "SELECT COUNT(*) FROM payments WHERE booking_id = ? AND status IN ('ThanhCong', 'DaDoiSoat')",
                [$bookingId]
            );

            if ($status === 'ChoXacNhan' && $successfulPayments <= 0) {
                $recommendations[] = 'Lien he khach de xac nhan coc va huong dan thanh toan.';
            }

            if ($status === 'DaCoc' && $daysLeft !== null && $daysLeft <= 3) {
                $recommendations[] = 'Chot danh sach hanh khach, HDV va dich vu nha cung cap truoc ngay khoi hanh.';
            }

            if ($daysLeft !== null && $daysLeft <= 1) {
                $recommendations[] = 'Gui nhac lich trinh va diem tap trung cho khach truoc 24h.';
            }

            foreach ($recommendations as $text) {
                $hash = sha1('booking|' . $bookingId . '|' . $text);
                $insert = $this->conn->prepare(
                    "INSERT IGNORE INTO admin_decision_assist
                        (entity_type, entity_id, recommendation_hash, recommendation_text, status, created_at, updated_at)
                     VALUES ('booking', ?, ?, ?, 'open', NOW(), NOW())"
                );
                $insert->execute([$bookingId, $hash, $text]);
                if ($insert->rowCount() > 0) {
                    $created++;
                }
            }
        }

        if ($created > 0) {
            $this->recordEvent(
                'decision_assist_' . date('YmdH'),
                'decision_assist',
                'low',
                'Decision assist updated',
                'Da tao/cap nhat ' . $created . ' goi y hanh dong cho admin.',
                ['created' => $created],
                true
            );
        }

        return [
            'ok' => true,
            'job' => 'decision_assist',
            'message' => 'Decision assist refreshed',
            'affected' => $created,
        ];
    }

    private function ensureAutomationTables() {
        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS automation_job_runs (
                run_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                job_name VARCHAR(64) NOT NULL,
                is_success TINYINT(1) NOT NULL DEFAULT 1,
                affected_count INT NOT NULL DEFAULT 0,
                message VARCHAR(255) DEFAULT NULL,
                duration_ms DECIMAL(10,1) DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (run_id),
                KEY idx_job_created (job_name, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS automation_events (
                event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                event_key VARCHAR(191) NOT NULL,
                job_name VARCHAR(64) NOT NULL,
                severity ENUM('low','medium','high') NOT NULL DEFAULT 'low',
                title VARCHAR(190) NOT NULL,
                message TEXT NOT NULL,
                payload_json LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (event_id),
                UNIQUE KEY uniq_event_key (event_key),
                KEY idx_job_created (job_name, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS booking_priority (
                booking_id INT(11) NOT NULL,
                priority_label ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low',
                score INT(11) NOT NULL DEFAULT 0,
                reasons_json LONGTEXT DEFAULT NULL,
                computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (booking_id),
                KEY idx_priority_label (priority_label),
                KEY idx_computed_at (computed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS tour_health_score (
                tour_id INT(11) NOT NULL,
                score INT(11) NOT NULL DEFAULT 0,
                health_level ENUM('Good','Watch','Critical') NOT NULL DEFAULT 'Good',
                metrics_json LONGTEXT DEFAULT NULL,
                computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (tour_id),
                KEY idx_health_level (health_level),
                KEY idx_computed_at (computed_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS admin_decision_assist (
                assist_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                entity_type VARCHAR(30) NOT NULL,
                entity_id INT(11) NOT NULL,
                recommendation_hash CHAR(40) NOT NULL,
                recommendation_text VARCHAR(500) NOT NULL,
                status ENUM('open','done','ignored') NOT NULL DEFAULT 'open',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (assist_id),
                UNIQUE KEY uniq_entity_reco (entity_type, entity_id, recommendation_hash),
                KEY idx_status (status),
                KEY idx_entity (entity_type, entity_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS automation_settings (
                setting_key VARCHAR(64) NOT NULL,
                setting_value VARCHAR(255) NOT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO automation_settings (setting_key, setting_value, updated_at)
             VALUES (?, '1', NOW())"
        );
        $stmt->execute([self::ENABLED_SETTING_KEY]);
    }

    private function logRun($jobName, $isSuccess, $affectedCount, $message, $durationMs) {
        $stmt = $this->conn->prepare(
            "INSERT INTO automation_job_runs (job_name, is_success, affected_count, message, duration_ms)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (string)$jobName,
            $isSuccess ? 1 : 0,
            (int)$affectedCount,
            substr((string)$message, 0, 255),
            $durationMs,
        ]);
    }

    private function recordEvent($eventKey, $jobName, $severity, $title, $message, array $payload = [], $notifyAdmin = true) {
        $stmt = $this->conn->prepare(
            "INSERT IGNORE INTO automation_events (event_key, job_name, severity, title, message, payload_json)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (string)$eventKey,
            (string)$jobName,
            (string)$severity,
            (string)$title,
            (string)$message,
            json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $inserted = ($stmt->rowCount() > 0);
        if ($inserted && $notifyAdmin) {
            $this->pushAdminNotification($jobName, $severity, $title, $message, $payload);
        }

        return $inserted;
    }

    private function pushAdminNotification($jobName, $severity, $title, $message, array $payload = []) {
        $priority = match ($severity) {
            'high' => 'Cao',
            'medium' => 'TrungBinh',
            default => 'Thap',
        };

        $fullTitle = '[AUTO][' . strtoupper($jobName) . '] ' . $title;
        $fullMessage = $message;
        if (!empty($payload)) {
            $fullMessage .= "\n\nPayload: " . json_encode($payload, JSON_UNESCAPED_UNICODE);
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO thong_bao (
                tieu_de, noi_dung, loai_thong_bao, muc_do_uu_tien,
                nguoi_gui_id, nguoi_nhan_id, vai_tro_nhan,
                trang_thai, thoi_gian_gui, thoi_gian_hen_gui
            ) VALUES (?, ?, 'ChungChung', ?, NULL, NULL, 'Admin', 'DaGui', NOW(), NULL)"
        );
        $stmt->execute([
            substr($fullTitle, 0, 255),
            $fullMessage,
            $priority,
        ]);
    }

    private function daysFromToday($dateYmd) {
        $ts = strtotime((string)$dateYmd . ' 00:00:00');
        if ($ts === false) {
            return null;
        }
        $delta = $ts - strtotime(date('Y-m-d') . ' 00:00:00');
        return (int)floor($delta / 86400);
    }

    private function countScalar($sql, array $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function floatScalar($sql, array $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return (float)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0.0;
        }
    }
}
