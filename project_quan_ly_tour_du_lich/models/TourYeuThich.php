<?php

class TourYeuThich
{
    private PDO $conn;
    private static bool $tableEnsured = false;

    public function __construct()
    {
        $this->conn = connectDB();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void
    {
        if (self::$tableEnsured) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS khach_hang_tour_yeu_thich (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    khach_hang_id INT NOT NULL,
                    tour_id INT NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_kh_tour_yeu_thich (khach_hang_id, tour_id),
                    KEY idx_tour_yeu_thich_tour (tour_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        try {
            $this->conn->exec($sql);
            self::$tableEnsured = true;
        } catch (Throwable $e) {
            // Keep silent here to avoid breaking page render if DB user lacks DDL privilege.
        }
    }

    public function getFavoriteTourIdsByKhachHangId(int $khachHangId): array
    {
        if ($khachHangId <= 0) {
            return [];
        }

        $sql = 'SELECT tour_id FROM khach_hang_tour_yeu_thich WHERE khach_hang_id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $tourId = (int)($row['tour_id'] ?? 0);
            if ($tourId > 0) {
                $result[$tourId] = true;
            }
        }

        return $result;
    }

    public function toggleByKhachHangId(int $khachHangId, int $tourId): bool
    {
        if ($khachHangId <= 0 || $tourId <= 0) {
            return false;
        }

        if ($this->isFavorite($khachHangId, $tourId)) {
            $stmt = $this->conn->prepare('DELETE FROM khach_hang_tour_yeu_thich WHERE khach_hang_id = ? AND tour_id = ?');
            $stmt->execute([$khachHangId, $tourId]);
            return false;
        }

        $stmt = $this->conn->prepare('INSERT INTO khach_hang_tour_yeu_thich (khach_hang_id, tour_id) VALUES (?, ?)');
        $stmt->execute([$khachHangId, $tourId]);
        return true;
    }

    public function isFavorite(int $khachHangId, int $tourId): bool
    {
        if ($khachHangId <= 0 || $tourId <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare('SELECT 1 FROM khach_hang_tour_yeu_thich WHERE khach_hang_id = ? AND tour_id = ? LIMIT 1');
        $stmt->execute([$khachHangId, $tourId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getFavoriteToursByKhachHangId(int $khachHangId, int $limit = 20): array
    {
        if ($khachHangId <= 0) {
            return [];
        }

        $limit = max(1, min($limit, 200));
        $sql = "SELECT t.tour_id, t.ten_tour, t.loai_tour, t.mo_ta, t.gia_co_ban, t.trang_thai, f.created_at,
                       lk.ngay_khoi_hanh AS ngay_khoi_hanh_gan_nhat,
                       lk.diem_tap_trung,
                       lk.so_cho
                FROM khach_hang_tour_yeu_thich f
                INNER JOIN tour t ON t.tour_id = f.tour_id
                LEFT JOIN lich_khoi_hanh lk ON lk.tour_id = t.tour_id
                    AND lk.ngay_khoi_hanh >= CURDATE()
                    AND lk.trang_thai = 'SapKhoiHanh'
                    AND lk.id = (
                        SELECT lk2.id FROM lich_khoi_hanh lk2
                        WHERE lk2.tour_id = t.tour_id
                          AND lk2.ngay_khoi_hanh >= CURDATE()
                          AND lk2.trang_thai = 'SapKhoiHanh'
                        ORDER BY lk2.ngay_khoi_hanh ASC LIMIT 1
                    )
                WHERE f.khach_hang_id = ?
                  AND (t.trang_thai = 'HoatDong' OR t.trang_thai IS NULL)
                ORDER BY f.created_at DESC, f.id DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $khachHangId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
