<?php

class PaymentFinanceService {
    public static function existsThuTransaction($conn, $bookingId) {
        $bookingId = (int)$bookingId;
        if ($bookingId <= 0) {
            return false;
        }

        $stmt = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu'");
        $stmt->execute([$bookingId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function createThuTransactionIfMissing($conn, array $data) {
        $bookingId = (int)($data['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            return false;
        }

        if (self::existsThuTransaction($conn, $bookingId)) {
            return false;
        }

        self::createThuTransaction($conn, $data);
        return true;
    }

    public static function createThuTransaction($conn, array $data) {
        $stmt = $conn->prepare('INSERT INTO giao_dich_tai_chinh (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int)($data['booking_id'] ?? 0),
            (int)($data['tour_id'] ?? 0),
            (int)($data['khach_hang_id'] ?? 0),
            'Thu',
            (float)($data['amount'] ?? 0),
            (string)($data['description'] ?? ''),
            (string)($data['payment_date'] ?? date('Y-m-d')),
        ]);

        self::invalidateFinanceReadCache();
    }

    public static function updateBookingPaymentStatusIfExists($conn, $bookingId, $status) {
        if (dbColumnExists('booking', 'trang_thai_thanh_toan', $conn)) {
            $stmt = $conn->prepare('UPDATE booking SET trang_thai_thanh_toan = ? WHERE booking_id = ?');
            $stmt->execute([(string)$status, (int)$bookingId]);
        }
    }

    private static function invalidateFinanceReadCache() {
        cacheForget('admin_dashboard_overview_v1');
        cacheForgetByPrefix('bao_cao_tai_chinh_');
    }
}
