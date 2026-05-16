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

    // Lấy lịch phân công của HDV
    public function getSchedule(int $hdvId, ?string $from = null, ?string $to = null): array {
        $params = [$hdvId];
        $sql = "SELECT * FROM hdv_schedules WHERE hdv_id = ?";
        if ($from) {
            $sql .= " AND end_time >= ?"; $params[] = $from;
        }
        if ($to) {
            $sql .= " AND start_time <= ?"; $params[] = $to;
        }
        $sql .= " ORDER BY start_time ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Thêm phân công lịch
    public function addSchedule(int $hdvId, ?int $tourId, string $startTime, string $endTime, ?string $note = null): bool {
        $sql = "INSERT INTO hdv_schedules (hdv_id, tour_id, start_time, end_time, note) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hdvId, $tourId, $startTime, $endTime, $note]);
    }

    // Ghi nhận nghỉ phép / vắng mặt
    public function addAbsence(int $hdvId, string $fromDate, string $toDate, ?string $type = null, ?string $reason = null): bool {
        $sql = "INSERT INTO hdv_absences (hdv_id, date_from, date_to, type, reason) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hdvId, $fromDate, $toDate, $type, $reason]);
    }

    // Kiểm tra HDV có rảnh trong khoảng thời gian nhất định
    public function isAvailable(int $hdvId, string $startTime, string $endTime): bool {
        // Kiểm tra lịch phân công trùng
        $sql = "SELECT COUNT(*) as c FROM hdv_schedules WHERE hdv_id = ? AND NOT (end_time <= ? OR start_time >= ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdvId, $startTime, $endTime]);
        $r = $stmt->fetch();
        if ($r && $r['c'] > 0) return false;

        // Kiểm tra vắng mặt
        $dateFrom = date('Y-m-d', strtotime($startTime));
        $dateTo = date('Y-m-d', strtotime($endTime));
        $sql2 = "SELECT COUNT(*) as c FROM hdv_absences WHERE hdv_id = ? AND NOT (date_to < ? OR date_from > ?)";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([$hdvId, $dateFrom, $dateTo]);
        $r2 = $stmt2->fetch();
        if ($r2 && $r2['c'] > 0) return false;

        return true;
    }

    // Lấy lịch sử dẫn tour (liên kết với bảng tour hoặc booking nếu có)
    public function getTourHistory(int $hdvId, int $limit = 50): array {
        // Nếu có bảng liên kết giữa hdv và tour (ví dụ hdv_schedules), trả về các tour đã dẫn
        $sql = "SELECT hs.*, t.* FROM hdv_schedules hs LEFT JOIN tour t ON hs.tour_id = t.tour_id WHERE hs.hdv_id = ? ORDER BY hs.start_time DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdvId, (int)$limit]);
        return $stmt->fetchAll();
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

        $hdvIds = array_values(array_map('intval', $hdvIds));
        $ph     = implode(',', array_fill(0, count($hdvIds), '?'));

        // Tìm HDV bận do lịch phân công trùng
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT hdv_id
             FROM hdv_schedules
             WHERE hdv_id IN ($ph)
               AND NOT (end_time <= ? OR start_time >= ?)"
        );
        $stmt->execute(array_merge($hdvIds, [$startTime, $endTime]));
        $busyBySchedule = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'hdv_id');

        // Tìm HDV bận do vắng mặt
        $dateFrom = date('Y-m-d', strtotime((string)$startTime));
        $dateTo   = date('Y-m-d', strtotime((string)$endTime));
        $stmt2 = $this->conn->prepare(
            "SELECT DISTINCT hdv_id
             FROM hdv_absences
             WHERE hdv_id IN ($ph)
               AND NOT (date_to < ? OR date_from > ?)"
        );
        $stmt2->execute(array_merge($hdvIds, [$dateFrom, $dateTo]));
        $busyByAbsence = array_column($stmt2->fetchAll(PDO::FETCH_ASSOC), 'hdv_id');

        $unavailable = array_unique(
            array_merge(
                array_map('intval', $busyBySchedule),
                array_map('intval', $busyByAbsence)
            )
        );

        return array_values(array_diff($hdvIds, $unavailable));
    }
}
