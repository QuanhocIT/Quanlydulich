<?php
/**
 * EmailQueueService — gửi email bất đồng bộ qua bảng email_queue.
 *
 * LUỒNG:
 *   1. Request HTTP gọi EmailQueueService::enqueue() → chỉ INSERT vào DB, ~1ms, không block.
 *   2. Cron job (scripts/process_email_queue.php) chạy mỗi 1 phút → gọi processQueue().
 *   3. processQueue() lấy các bản ghi pending, gửi SMTP, cập nhật status.
 *
 * Không cần Redis/RabbitMQ — chỉ cần MySQL đang có sẵn.
 */
class EmailQueueService
{
    /**
     * Đưa một email vào hàng đợi.
     * Nhanh (~1ms), không block request.
     *
     * @return int  ID bản ghi vừa insert (0 nếu lỗi)
     */
    public static function enqueue(
        string $toEmail,
        string $subject,
        string $bodyHtml,
        int    $maxAttempts = 3
    ): int {
        $toEmail = trim($toEmail);
        if ($toEmail === '' || filter_var($toEmail, FILTER_VALIDATE_EMAIL) === false) {
            error_log('[EmailQueueService::enqueue] Invalid email: ' . $toEmail);
            return 0;
        }

        try {
            $conn = getPDOConnection();
            $stmt = $conn->prepare(
                'INSERT INTO email_queue (to_email, subject, body_html, max_attempts, status, scheduled_at, created_at)
                 VALUES (?, ?, ?, ?, \'pending\', NOW(), NOW())'
            );
            $stmt->execute([$toEmail, $subject, $bodyHtml, max(1, $maxAttempts)]);
            return (int)$conn->lastInsertId();
        } catch (Throwable $e) {
            error_log('[EmailQueueService::enqueue] DB error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Xử lý hàng đợi — gọi từ cron job, không gọi trong HTTP request.
     *
     * @param  int   $batchSize  Số email xử lý mỗi lần chạy
     * @return array             ['sent'=>int, 'failed'=>int]
     */
    public static function processQueue(int $batchSize = 20): array
    {
        require_once __DIR__ . '/../commons/mail.php';

        $sent   = 0;
        $failed = 0;

        try {
            $conn = getPDOConnection();

            // Lấy batch pending — dùng SELECT ... FOR UPDATE SKIP LOCKED để tránh race
            // condition khi nhiều worker cron chạy song song (MySQL 8+).
            // Fallback: không có SKIP LOCKED thì dùng UPDATE trước.
            $conn->beginTransaction();

            $stmt = $conn->prepare(
                'SELECT id, to_email, subject, body_html, attempts, max_attempts
                 FROM email_queue
                 WHERE status = \'pending\'
                   AND attempts < max_attempts
                   AND scheduled_at <= NOW()
                 ORDER BY id ASC
                 LIMIT ?
                 FOR UPDATE SKIP LOCKED'
            );
            $stmt->bindValue(1, $batchSize, PDO::PARAM_INT);

            try {
                $stmt->execute();
            } catch (Throwable $e) {
                // MySQL < 8 không hỗ trợ SKIP LOCKED — fallback không có
                $conn->rollBack();
                $conn->beginTransaction();
                $stmt = $conn->prepare(
                    'SELECT id, to_email, subject, body_html, attempts, max_attempts
                     FROM email_queue
                     WHERE status = \'pending\'
                       AND attempts < max_attempts
                       AND scheduled_at <= NOW()
                     ORDER BY id ASC
                     LIMIT ?
                     FOR UPDATE'
                );
                $stmt->bindValue(1, $batchSize, PDO::PARAM_INT);
                $stmt->execute();
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                $conn->rollBack();
                return ['sent' => 0, 'failed' => 0];
            }

            // Đánh dấu 'processing' ngay trong transaction để các worker khác không lấy lại
            $ids = array_column($rows, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $conn->prepare("UPDATE email_queue SET status='processing' WHERE id IN ($placeholders)")
                 ->execute($ids);

            $conn->commit();

            // Gửi từng email ngoài transaction
            foreach ($rows as $row) {
                $id          = (int)$row['id'];
                $attempts    = (int)$row['attempts'] + 1;
                $maxAttempts = (int)$row['max_attempts'];

                try {
                    sendHtmlEmail($row['to_email'], $row['subject'], $row['body_html']);

                    $conn->prepare(
                        "UPDATE email_queue
                         SET status='sent', attempts=?, sent_at=NOW(), last_error=NULL
                         WHERE id=?"
                    )->execute([$attempts, $id]);

                    $sent++;
                } catch (Throwable $e) {
                    $errMsg = substr($e->getMessage(), 0, 500);
                    $newStatus = ($attempts >= $maxAttempts) ? 'failed' : 'pending';

                    $conn->prepare(
                        "UPDATE email_queue
                         SET status=?, attempts=?, last_error=?, scheduled_at=DATE_ADD(NOW(), INTERVAL ? MINUTE)
                         WHERE id=?"
                    )->execute([
                        $newStatus,
                        $attempts,
                        $errMsg,
                        self::backoffMinutes($attempts), // exponential backoff
                        $id,
                    ]);

                    error_log("[EmailQueueService] Failed id=$id attempt=$attempts: $errMsg");
                    $failed++;
                }
            }
        } catch (Throwable $e) {
            error_log('[EmailQueueService::processQueue] Fatal: ' . $e->getMessage());
            try { $conn->rollBack(); } catch (Throwable $_) {}
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /** Exponential backoff: lần 1=2 phút, lần 2=10 phút, lần 3+=30 phút */
    private static function backoffMinutes(int $attempt): int
    {
        if ($attempt <= 1) return 2;
        if ($attempt === 2) return 10;
        return 30;
    }
}
