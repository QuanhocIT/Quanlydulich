<?php

class BookingChangeRequest
{
    public PDO $conn;

    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function ensureTableExists(): void
    {
        try {
            $this->conn->query('SELECT id FROM booking_change_requests LIMIT 1');
        } catch (Throwable $e) {
            throw new RuntimeException('Thieu bang booking_change_requests. Vui long chay `php scripts/migrate.php up`.');
        }
    }

    public function create(array $data): int
    {
        $this->ensureTableExists();

        $sql = 'INSERT INTO booking_change_requests
                (booking_id, khach_hang_id, loai_yeu_cau, lich_khoi_hanh_moi_id, ngay_khoi_hanh_moi, phi_huy, ly_do, trang_thai, ghi_chu_xu_ly)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            (int)($data['booking_id'] ?? 0),
            (int)($data['khach_hang_id'] ?? 0),
            (string)($data['loai_yeu_cau'] ?? 'Huy'),
            isset($data['lich_khoi_hanh_moi_id']) ? (int)$data['lich_khoi_hanh_moi_id'] : null,
            $data['ngay_khoi_hanh_moi'] ?? null,
            (float)($data['phi_huy'] ?? 0),
            trim((string)($data['ly_do'] ?? '')),
            (string)($data['trang_thai'] ?? 'MoiTao'),
            $data['ghi_chu_xu_ly'] ?? null,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function hasOpenRequestByBookingId(int $bookingId): bool
    {
        $this->ensureTableExists();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM booking_change_requests WHERE booking_id = ? AND trang_thai IN ('MoiTao', 'DaDuyet')");
        $stmt->execute([(int)$bookingId]);

        return ((int)$stmt->fetchColumn()) > 0;
    }

    public function getByBookingId(int $bookingId): array
    {
        $this->ensureTableExists();

        $sql = "SELECT bcr.*, lkh.diem_tap_trung
                FROM booking_change_requests bcr
                LEFT JOIN lich_khoi_hanh lkh ON bcr.lich_khoi_hanh_moi_id = lkh.id
                WHERE bcr.booking_id = ?
                ORDER BY bcr.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$bookingId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(int $id): mixed
    {
        $this->ensureTableExists();

        $stmt = $this->conn->prepare('SELECT * FROM booking_change_requests WHERE id = ? LIMIT 1');
        $stmt->execute([(int)$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAllWithDetails(string $status = '', int $limit = 300): array
    {
        $this->ensureTableExists();

        $limit = max(1, min(1000, (int)$limit));
        $sql = "SELECT bcr.*,
                       b.trang_thai AS booking_trang_thai,
                       b.ngay_khoi_hanh AS booking_ngay_khoi_hanh,
                       b.so_nguoi,
                       b.tong_tien,
                       t.ten_tour,
                       nd.ho_ten,
                       nd.email,
                       nd.so_dien_thoai,
                       lkh.diem_tap_trung,
                       lkh.ngay_khoi_hanh AS lich_moi_ngay_khoi_hanh
                FROM booking_change_requests bcr
                INNER JOIN booking b ON b.booking_id = bcr.booking_id
                INNER JOIN khach_hang kh ON kh.khach_hang_id = bcr.khach_hang_id
                INNER JOIN nguoi_dung nd ON nd.id = kh.nguoi_dung_id
                LEFT JOIN tour t ON t.tour_id = b.tour_id
                LEFT JOIN lich_khoi_hanh lkh ON lkh.id = bcr.lich_khoi_hanh_moi_id";

        $params = [];
        if ($status !== '') {
            $sql .= ' WHERE bcr.trang_thai = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY bcr.id DESC LIMIT ' . $limit;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function updateStatus(int $id, string $status, ?string $note = null): bool
    {
        $this->ensureTableExists();

        $allowed = ['MoiTao', 'TuDongDuyet', 'DaDuyet', 'TuChoi'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->conn->prepare('UPDATE booking_change_requests SET trang_thai = ?, ghi_chu_xu_ly = ? WHERE id = ?');
        return $stmt->execute([$status, $note, (int)$id]);
    }
}
