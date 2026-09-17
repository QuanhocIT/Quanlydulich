<?php
class HDV 
{
    public PDO $conn;
    private static array $columnExistsCache = [];
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function hasColumn(string $tableName, string $columnName): bool {
        $key = $tableName . '.' . $columnName;
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
        }

        try {
            $sql = "SELECT COUNT(*)
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?
                      AND COLUMN_NAME = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tableName, $columnName]);
            self::$columnExistsCache[$key] = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            self::$columnExistsCache[$key] = false;
        }

        return self::$columnExistsCache[$key];
    }

    private function nhanSuNotDeletedClause(string $alias = ''): string {
        if (!$this->hasColumn('nhan_su', 'is_deleted')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'is_deleted = 0';
    }

    private function nguoiDungNotDeletedClause(string $alias = ''): string {
        if (!$this->hasColumn('nguoi_dung', 'is_deleted')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'is_deleted = 0';
    }
    // Lấy tất cả HDV (có thể lọc theo nhóm hoặc trạng thái)
    public function getAll(?int $groupId = null, bool $availableOnly = false): array {
        $conds = ["ns.vai_tro = 'HDV'", $this->nhanSuNotDeletedClause('ns'), "(nd.id IS NULL OR " . $this->nguoiDungNotDeletedClause('nd') . ")"];
        $params = [];
        if ($groupId) {
            $conds[] = 'ns.group_id = ?';
            $params[] = $groupId;
        }
        if ($availableOnly) {
            $conds[] = 'ns.trang_thai_lam_viec = "SanSang"';
        }
        $where = implode(' AND ', $conds);
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.avatar 
                FROM nhan_su ns 
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id 
                WHERE $where ORDER BY nd.ho_ten ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): mixed {
        $sql = "SELECT * FROM nhan_su WHERE nhan_su_id = ? AND " . $this->nhanSuNotDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Thêm HDV (hoặc cập nhật thông tin chi tiết)
    public function insert(array $data): bool {
        $sql = "INSERT INTO nhan_su (ho_ten, vai_tro, ngay_sinh, anh, so_dien_thoai, email, dia_chi, chung_chi, ngon_ngu, kinh_nghiem, suc_khoe, group_id, is_available, note)
                VALUES (?, 'HDV', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ho_ten'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['anh'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['dia_chi'] ?? null,
            $data['chung_chi'] ?? null,
            $data['ngon_ngu'] ?? null,
            $data['kinh_nghiem'] ?? null,
            $data['suc_khoe'] ?? null,
            $data['group_id'] ?? null,
            $data['is_available'] ?? 1,
            $data['note'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE nhan_su SET ho_ten = ?, ngay_sinh = ?, anh = ?, so_dien_thoai = ?, email = ?, dia_chi = ?, chung_chi = ?, ngon_ngu = ?, kinh_nghiem = ?, suc_khoe = ?, group_id = ?, is_available = ?, note = ? WHERE nhan_su_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ho_ten'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['anh'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['dia_chi'] ?? null,
            $data['chung_chi'] ?? null,
            $data['ngon_ngu'] ?? null,
            $data['kinh_nghiem'] ?? null,
            $data['suc_khoe'] ?? null,
            $data['group_id'] ?? null,
            $data['is_available'] ?? 1,
            $data['note'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool {
        if ($this->hasColumn('nhan_su', 'is_deleted')) {
            $sql = "UPDATE nhan_su SET is_deleted = 1";
            if ($this->hasColumn('nhan_su', 'deleted_at')) {
                $sql .= ", deleted_at = NOW()";
            }
            $sql .= " WHERE nhan_su_id = ? AND is_deleted = 0";
        } else {
            $sql = "DELETE FROM nhan_su WHERE nhan_su_id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Lấy lịch phân công của HDV (kết hợp lich_khoi_hanh và lich_lam_viec_hdv)
    public function getSchedule(int $hdvId, ?string $from = null, ?string $to = null): array {
        // 1. Lấy từ lich_khoi_hanh và phan_bo_nhan_su
        $sqlTour = "SELECT lkh.id, lkh.tour_id, t.ten_tour as note,
                           CONCAT(lkh.ngay_khoi_hanh, ' 00:00:00') as start_time,
                           CONCAT(COALESCE(lkh.ngay_ket_thuc, lkh.ngay_khoi_hanh), ' 23:59:59') as end_time,
                           lkh.trang_thai, 'Tour' as loai
                    FROM lich_khoi_hanh lkh
                    LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                    WHERE (lkh.hdv_id = ? OR EXISTS (
                        SELECT 1 FROM phan_bo_nhan_su pbn 
                        WHERE pbn.lich_khoi_hanh_id = lkh.id AND pbn.nhan_su_id = ? AND pbn.vai_tro = 'HDV' AND pbn.deleted_at IS NULL
                    ))
                    AND lkh.deleted_at IS NULL
                    AND lkh.trang_thai != 'Huy'";
        $paramsTour = [$hdvId, $hdvId];
        if ($from) {
            $sqlTour .= " AND COALESCE(lkh.ngay_ket_thuc, lkh.ngay_khoi_hanh) >= ?";
            $paramsTour[] = date('Y-m-d', strtotime($from));
        }
        if ($to) {
            $sqlTour .= " AND lkh.ngay_khoi_hanh <= ?";
            $paramsTour[] = date('Y-m-d', strtotime($to));
        }
        $stmtTour = $this->conn->prepare($sqlTour);
        $stmtTour->execute($paramsTour);
        $tourRows = $stmtTour->fetchAll(PDO::FETCH_ASSOC);

        // 2. Lấy từ lich_lam_viec_hdv
        $sqlLlv = "SELECT id, tour_id, COALESCE(ghi_chu, loai_lich) as note,
                          CONCAT(ngay_bat_dau, ' 00:00:00') as start_time,
                          CONCAT(ngay_ket_thuc, ' 23:59:59') as end_time,
                          trang_thai, loai_lich as loai
                   FROM lich_lam_viec_hdv
                   WHERE nhan_su_id = ? AND trang_thai != 'Huy'";
        $paramsLlv = [$hdvId];
        if ($from) {
            $sqlLlv .= " AND ngay_ket_thuc >= ?";
            $paramsLlv[] = date('Y-m-d', strtotime($from));
        }
        if ($to) {
            $sqlLlv .= " AND ngay_bat_dau <= ?";
            $paramsLlv[] = date('Y-m-d', strtotime($to));
        }
        $stmtLlv = $this->conn->prepare($sqlLlv);
        $stmtLlv->execute($paramsLlv);
        $llvRows = $stmtLlv->fetchAll(PDO::FETCH_ASSOC);

        $merged = array_merge($tourRows ?: [], $llvRows ?: []);
        usort($merged, function($a, $b) {
            return strcmp($a['start_time'] ?? '', $b['start_time'] ?? '');
        });
        return $merged;
    }

    // Thêm phân công lịch
    public function addSchedule(int $hdvId, ?int $tourId, string $startTime, string $endTime, ?string $note = null): bool {
        $startDate = date('Y-m-d', strtotime($startTime));
        $endDate = date('Y-m-d', strtotime($endTime));
        $sql = "INSERT INTO lich_lam_viec_hdv (nhan_su_id, tour_id, loai_lich, ngay_bat_dau, ngay_ket_thuc, ghi_chu, trang_thai)
                VALUES (?, ?, 'Tour', ?, ?, ?, 'XacNhan')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hdvId, $tourId, $startDate, $endDate, $note]);
    }

    // Ghi nhận nghỉ phép / vắng mặt
    public function addAbsence(int $hdvId, string $fromDate, string $toDate, ?string $type = null, ?string $reason = null): bool {
        $startDate = date('Y-m-d', strtotime($fromDate));
        $endDate = date('Y-m-d', strtotime($toDate));
        $loaiLich = 'NghiPhep';
        if ($type === 'Ban') {
            $loaiLich = 'Ban';
        }
        $sql = "INSERT INTO lich_lam_viec_hdv (nhan_su_id, loai_lich, ngay_bat_dau, ngay_ket_thuc, ghi_chu, trang_thai)
                VALUES (?, ?, ?, ?, ?, 'XacNhan')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hdvId, $loaiLich, $startDate, $endDate, $reason]);
    }

    // Kiểm tra HDV có rảnh trong khoảng thời gian nhất định
    public function isAvailable(int $hdvId, string $startTime, string $endTime): bool {
        $startDate = date('Y-m-d', strtotime($startTime));
        $endDate = date('Y-m-d', strtotime($endTime));

        // Kiểm tra lịch tour đang chạy hoặc sắp khởi hành
        $sqlTour = "SELECT COUNT(*) as c 
                    FROM lich_khoi_hanh lkh
                    WHERE (lkh.hdv_id = ? OR EXISTS (
                        SELECT 1 FROM phan_bo_nhan_su pbn 
                        WHERE pbn.lich_khoi_hanh_id = lkh.id AND pbn.nhan_su_id = ? AND pbn.vai_tro = 'HDV' AND pbn.deleted_at IS NULL
                    ))
                    AND lkh.deleted_at IS NULL
                    AND lkh.trang_thai IN ('SapKhoiHanh', 'DangChay', 'DaXacNhan')
                    AND NOT (COALESCE(lkh.ngay_ket_thuc, lkh.ngay_khoi_hanh) < ? OR lkh.ngay_khoi_hanh > ?)";
        $stmtTour = $this->conn->prepare($sqlTour);
        $stmtTour->execute([$hdvId, $hdvId, $startDate, $endDate]);
        $rTour = $stmtTour->fetch(PDO::FETCH_ASSOC);
        if ($rTour && (int)$rTour['c'] > 0) {
            return false;
        }

        // Kiểm tra lịch làm việc / nghỉ phép / bận
        $sqlLlv = "SELECT COUNT(*) as c 
                   FROM lich_lam_viec_hdv
                   WHERE nhan_su_id = ?
                     AND trang_thai != 'Huy'
                     AND NOT (ngay_ket_thuc < ? OR ngay_bat_dau > ?)";
        $stmtLlv = $this->conn->prepare($sqlLlv);
        $stmtLlv->execute([$hdvId, $startDate, $endDate]);
        $rLlv = $stmtLlv->fetch(PDO::FETCH_ASSOC);
        if ($rLlv && (int)$rLlv['c'] > 0) {
            return false;
        }

        return true;
    }

    // Lấy lịch sử dẫn tour (liên kết với bảng tour và lich_khoi_hanh)
    public function getTourHistory(int $hdvId, int $limit = 50): array {
        $limit = max(1, min(200, (int)$limit));
        $sql = "SELECT lkh.id, lkh.tour_id, t.ten_tour, t.ten_tour as title,
                       lkh.ngay_khoi_hanh as start_time,
                       COALESCE(lkh.ngay_ket_thuc, lkh.ngay_khoi_hanh) as end_time,
                       lkh.trang_thai
                FROM lich_khoi_hanh lkh
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                WHERE (lkh.hdv_id = ? OR EXISTS (
                    SELECT 1 FROM phan_bo_nhan_su pbn 
                    WHERE pbn.lich_khoi_hanh_id = lkh.id AND pbn.nhan_su_id = ? AND pbn.vai_tro = 'HDV' AND pbn.deleted_at IS NULL
                ))
                AND lkh.deleted_at IS NULL
                ORDER BY lkh.ngay_khoi_hanh DESC
                LIMIT $limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdvId, $hdvId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * P6: Kiểm tra khả dụng của nhiều HDV trong một batch query — tránh N+1.
     * Thay vì gọi isAvailable() N lần, gọi 1 lần để lấy danh sách ID khả dụng.
     *
     * @param  int[]  $hdvIds    Danh sách nhan_su_id cần kiểm tra
     * @param  string $startTime Thời gian bắt đầu (Y-m-d H:i:s hoặc Y-m-d)
     * @param  string $endTime   Thời gian kết thúc
     * @return int[]             Danh sách nhan_su_id còn rảnh
     */
    public function getAvailableIdsBatch(array $hdvIds, $startTime, $endTime): array
    {
        if (empty($hdvIds)) {
            return [];
        }

        $hdvIds = array_values(array_unique(array_map('intval', $hdvIds)));
        if (empty($hdvIds)) {
            return [];
        }

        $startDate = date('Y-m-d', strtotime((string)$startTime));
        $endDate   = date('Y-m-d', strtotime((string)$endTime));
        $ph        = implode(',', array_fill(0, count($hdvIds), '?'));

        // 1. HDV bận do lịch khởi hành / phân bổ nhân sự
        $sqlTour = "SELECT DISTINCT lkh.hdv_id as busy_id
                    FROM lich_khoi_hanh lkh
                    WHERE lkh.hdv_id IN ($ph)
                      AND lkh.deleted_at IS NULL
                      AND lkh.trang_thai IN ('SapKhoiHanh', 'DangChay', 'DaXacNhan')
                      AND NOT (COALESCE(lkh.ngay_ket_thuc, lkh.ngay_khoi_hanh) < ? OR lkh.ngay_khoi_hanh > ?)
                    UNION
                    SELECT DISTINCT pbn.nhan_su_id as busy_id
                    FROM phan_bo_nhan_su pbn
                    JOIN lich_khoi_hanh lkh2 ON pbn.lich_khoi_hanh_id = lkh2.id
                    WHERE pbn.nhan_su_id IN ($ph)
                      AND pbn.vai_tro = 'HDV'
                      AND pbn.deleted_at IS NULL
                      AND lkh2.deleted_at IS NULL
                      AND lkh2.trang_thai IN ('SapKhoiHanh', 'DangChay', 'DaXacNhan')
                      AND NOT (COALESCE(lkh2.ngay_ket_thuc, lkh2.ngay_khoi_hanh) < ? OR lkh2.ngay_khoi_hanh > ?)";
        
        $paramsTour = array_merge($hdvIds, [$startDate, $endDate], $hdvIds, [$startDate, $endDate]);
        $stmtTour = $this->conn->prepare($sqlTour);
        $stmtTour->execute($paramsTour);
        $busyByTour = array_column($stmtTour->fetchAll(PDO::FETCH_ASSOC), 'busy_id');

        // 2. HDV bận do lich_lam_viec_hdv
        $sqlLlv = "SELECT DISTINCT nhan_su_id as busy_id
                   FROM lich_lam_viec_hdv
                   WHERE nhan_su_id IN ($ph)
                     AND trang_thai != 'Huy'
                     AND NOT (ngay_ket_thuc < ? OR ngay_bat_dau > ?)";
        $paramsLlv = array_merge($hdvIds, [$startDate, $endDate]);
        $stmtLlv = $this->conn->prepare($sqlLlv);
        $stmtLlv->execute($paramsLlv);
        $busyByLlv = array_column($stmtLlv->fetchAll(PDO::FETCH_ASSOC), 'busy_id');

        $unavailable = array_unique(
            array_merge(
                array_map('intval', $busyByTour),
                array_map('intval', $busyByLlv)
            )
        );

        return array_values(array_diff($hdvIds, $unavailable));
    }
}
