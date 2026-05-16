<?php

class SupportTicket
{
    public PDO $conn;

    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function ensureTables(): void
    {
        try {
            $this->conn->query('SELECT id FROM support_tickets LIMIT 1');
            $this->conn->query('SELECT id FROM support_ticket_messages LIMIT 1');
        } catch (Throwable $e) {
            throw new RuntimeException('Thieu bang support_tickets/support_ticket_messages. Vui long apply migration V024.');
        }
    }

    public function calcSlaDueAt(string $priority, ?DateTimeImmutable $from = null): string
    {
        $from = $from ?? new DateTimeImmutable('now');
        $hours = match ($priority) {
            'KhanCap' => 4,
            'Cao' => 12,
            'Thap' => 48,
            default => 24,
        };

        return $from->modify('+' . $hours . ' hours')->format('Y-m-d H:i:s');
    }

    public function detectCategory(string $subject, string $message): string
    {
        $haystack = mb_strtolower($subject . ' ' . $message);

        if (preg_match('/(thanh toan|chuyen khoan|hoa don|refund|hoan tien)/u', $haystack)) {
            return 'Payment';
        }

        if (preg_match('/(booking|doi lich|huy|khoi hanh|lich trinh)/u', $haystack)) {
            return 'Booking';
        }

        if (preg_match('/(tai khoan|dang nhap|mat khau|otp|2fa)/u', $haystack)) {
            return 'Account';
        }

        return 'General';
    }

    private function generateTicketCode(): string
    {
        $prefix = 'TKT-' . date('ymd') . '-';
        $suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return $prefix . $suffix;
    }

    public function createTicket(array $data): int
    {
        $this->ensureTables();

        $subject = trim((string)($data['subject'] ?? 'Yeu cau ho tro'));
        $message = trim((string)($data['message'] ?? ''));
        $priority = (string)($data['priority'] ?? 'TrungBinh');
        $allowedPriority = ['Thap', 'TrungBinh', 'Cao', 'KhanCap'];
        if (!in_array($priority, $allowedPriority, true)) {
            $priority = 'TrungBinh';
        }

        $category = $this->detectCategory($subject, $message);
        $slaDueAt = $this->calcSlaDueAt($priority);

        $conn = $this->conn;
        $conn->beginTransaction();

        try {
            $ticketCode = $this->generateTicketCode();
            $stmt = $conn->prepare('INSERT INTO support_tickets (ticket_code, khach_hang_id, booking_id, subject, category, priority, status, sla_due_at, last_reply_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([
                $ticketCode,
                (int)($data['khach_hang_id'] ?? 0),
                isset($data['booking_id']) && (int)$data['booking_id'] > 0 ? (int)$data['booking_id'] : null,
                $subject,
                $category,
                $priority,
                'Open',
                $slaDueAt,
            ]);

            $ticketId = (int)$conn->lastInsertId();
            $this->addMessage($ticketId, (int)($data['sender_id'] ?? 0), 'KhachHang', $message);

            $conn->commit();
            return $ticketId;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    public function addMessage(int $ticketId, int $senderId, string $senderRole, string $message): bool
    {
        $this->ensureTables();

        $senderRole = in_array($senderRole, ['KhachHang', 'Admin', 'System'], true) ? $senderRole : 'System';
        $message = trim($message);
        if ($ticketId <= 0 || $message === '') {
            return false;
        }

        $stmt = $this->conn->prepare('INSERT INTO support_ticket_messages (ticket_id, sender_id, sender_role, message) VALUES (?, ?, ?, ?)');
        $ok = $stmt->execute([$ticketId, $senderId > 0 ? $senderId : null, $senderRole, $message]);

        if ($ok) {
            $statusAfterReply = match ($senderRole) {
                'KhachHang' => 'Open',
                'Admin' => 'WaitingCustomer',
                default => null,
            };

            if ($statusAfterReply !== null) {
                $stmtTicket = $this->conn->prepare('UPDATE support_tickets SET last_reply_at = NOW(), status = ?, updated_at = NOW() WHERE id = ?');
                $stmtTicket->execute([$statusAfterReply, $ticketId]);
            }
        }

        return $ok;
    }

    public function updateStatus(int $ticketId, string $status): bool
    {
        $this->ensureTables();

        $allowed = ['Open', 'InProgress', 'WaitingCustomer', 'Resolved', 'Closed'];
        if ($ticketId <= 0 || !in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->conn->prepare('UPDATE support_tickets SET status = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$status, $ticketId]);
    }

    public function getByKhachHangId(int $khachHangId): array
    {
        $this->ensureTables();

        $stmt = $this->conn->prepare('SELECT * FROM support_tickets WHERE khach_hang_id = ? ORDER BY id DESC');
        $stmt->execute([$khachHangId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getByIdForKhachHang(int $ticketId, int $khachHangId): mixed
    {
        $this->ensureTables();

        $stmt = $this->conn->prepare('SELECT * FROM support_tickets WHERE id = ? AND khach_hang_id = ? LIMIT 1');
        $stmt->execute([$ticketId, $khachHangId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function getMessagesByTicketId(int $ticketId): array
    {
        $this->ensureTables();

        $stmt = $this->conn->prepare('SELECT * FROM support_ticket_messages WHERE ticket_id = ? ORDER BY id ASC');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllForAdmin(string $status = '', int $limit = 400): array
    {
        $this->ensureTables();

        $limit = max(1, min(1000, $limit));
        $sql = 'SELECT st.*, nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM support_tickets st
                INNER JOIN khach_hang kh ON kh.khach_hang_id = st.khach_hang_id
                INNER JOIN nguoi_dung nd ON nd.id = kh.nguoi_dung_id';

        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE st.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY st.id DESC LIMIT ' . $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getByIdForAdmin(int $ticketId): mixed
    {
        $this->ensureTables();

        $sql = 'SELECT st.*, nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM support_tickets st
                INNER JOIN khach_hang kh ON kh.khach_hang_id = st.khach_hang_id
                INNER JOIN nguoi_dung nd ON nd.id = kh.nguoi_dung_id
                WHERE st.id = ?
                LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ticketId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
