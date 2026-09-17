<?php

class PaymentFinanceService {
    public static function existsThuTransaction($conn, $bookingId, $paymentId = 0) {
        $bookingId = (int)$bookingId;
        if ($bookingId <= 0) {
            return false;
        }

        $paymentId = (int)$paymentId;
        if ($paymentId > 0) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu' AND (mo_ta LIKE ? OR mo_ta LIKE ?)");
            $stmt->execute([$bookingId, "%payment #{$paymentId}%", "%GD #{$paymentId}%"]);
            if ((int)$stmt->fetchColumn() > 0) {
                return true;
            }
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

        $paymentId = (int)($data['payment_id'] ?? 0);
        $description = trim((string)($data['description'] ?? ''));

        // Kiểm tra xem giao dịch cụ thể với mô tả này đã tồn tại chưa
        if ($description !== '') {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu' AND mo_ta = ?");
            $stmt->execute([$bookingId, $description]);
            if ((int)$stmt->fetchColumn() > 0) {
                return false;
            }
        }

        // Nếu có payment_id, kiểm tra theo payment_id trong mô tả
        if ($paymentId > 0) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Thu' AND (mo_ta LIKE ? OR mo_ta LIKE ?)");
            $stmt->execute([$bookingId, "%payment #{$paymentId}%", "%GD #{$paymentId}%"]);
            if ((int)$stmt->fetchColumn() > 0) {
                return false;
            }
        } elseif (empty($description)) {
            if (self::existsThuTransaction($conn, $bookingId)) {
                return false;
            }
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

    public static function createChiRefundTransaction($conn, array $data) {
        $bookingId = (int)($data['booking_id'] ?? 0);
        $amount = (float)($data['amount'] ?? 0);
        if ($bookingId <= 0 || $amount <= 0) {
            return false;
        }

        $description = (string)($data['description'] ?? ('Hoàn tiền hủy booking #' . $bookingId));

        // Kiểm tra tránh hoàn tiền lặp lại cùng mô tả
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM giao_dich_tai_chinh WHERE booking_id = ? AND loai = 'Chi' AND mo_ta = ?");
        $stmtCheck->execute([$bookingId, $description]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            return false;
        }

        $stmt = $conn->prepare('INSERT INTO giao_dich_tai_chinh (booking_id, tour_id, khach_hang_id, loai, so_tien, mo_ta, ngay_giao_dich) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $bookingId,
            (int)($data['tour_id'] ?? 0),
            (int)($data['khach_hang_id'] ?? 0),
            'Chi',
            $amount,
            $description,
            (string)($data['payment_date'] ?? date('Y-m-d')),
        ]);

        self::invalidateFinanceReadCache();
        return true;
    }

    public static function updateBookingPaymentStatusIfExists($conn, $bookingId, $status) {
        if (dbColumnExists('booking', 'trang_thai_thanh_toan', $conn)) {
            $stmt = $conn->prepare('UPDATE booking SET trang_thai_thanh_toan = ? WHERE booking_id = ?');
            $stmt->execute([(string)$status, (int)$bookingId]);
        }
    }

    public static function invalidateFinanceReadCache() {
        cacheForget('admin_dashboard_overview_v1');
        cacheForgetByPrefix('bao_cao_tai_chinh_');
    }
}
