<?php
// Model cho LichKhoiHanh - Quản lý lịch khởi hành chi tiết
class LichKhoiHanh 
{
    public PDO $conn;
    private static array $columnExistsCache = [];
    private static array $tableColumnsCache = [];

    public function clearScheduleReadCache(): void {
        cacheForgetByPrefix('lich_khoi_hanh_options_');
        cacheForgetByPrefix('lich_khoi_hanh_upcoming_');
        cacheForget('admin_dashboard_overview_v1');
    }
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function getTableColumns(string $tableName): array {
        return SchemaHelper::getTableColumns($this->conn, $tableName);
    }

    private function selectColumnsFromTable(string $tableName, string $alias = ''): string {
        return SchemaHelper::selectColumns($this->conn, $tableName, $alias);
    }

    private function lichKhoiHanhSelectColumns(string $alias = ''): string {
        return $this->selectColumnsFromTable('lich_khoi_hanh', $alias);
    }

    private function hasColumn(string $tableName, string $columnName): bool {
        return SchemaHelper::hasColumn($this->conn, $tableName, $columnName);
    }

    private function scheduleNotDeletedClause(string $alias = ''): string {
        return SchemaHelper::notDeletedClause($this->conn, 'lich_khoi_hanh', $alias);
    }

    private function assignmentNotDeletedClause(string $tableName, string $alias): string {
        return SchemaHelper::notDeletedClause($this->conn, $tableName, $alias);
    }

    private function bookingNotDeletedClause(string $alias = 'b'): string {
        return SchemaHelper::notDeletedClause($this->conn, 'booking', $alias);
    }

    // Tự động cập nhật trạng thái lịch khởi hành theo thời gian hiện tại
    public function autoUpdateTrangThai(): void {
        // Hoàn thành: đã kết thúc (ngày_ket_thuc < hôm nay hoặc = hôm nay và giờ_ket_thuc <= hiện tại)
        $sqlHoanThanh = "UPDATE lich_khoi_hanh
                         SET trang_thai = 'HoanThanh'
                         WHERE trang_thai IN ('SapKhoiHanh','DangChay')
                           AND " . $this->scheduleNotDeletedClause() . "
                           AND (
                               ngay_ket_thuc < CURDATE()
                               OR (ngay_ket_thuc = CURDATE() AND gio_ket_thuc IS NOT NULL AND gio_ket_thuc <= CURTIME())
                           )";
        $stmt1 = $this->conn->prepare($sqlHoanThanh);
        $stmt1->execute();

        // Đang chạy: đã bắt đầu nhưng chưa kết thúc
        $sqlDangChay = "UPDATE lich_khoi_hanh
                        SET trang_thai = 'DangChay'
                        WHERE trang_thai = 'SapKhoiHanh'
                          AND " . $this->scheduleNotDeletedClause() . "
                          AND ngay_khoi_hanh <= CURDATE()
                          AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= CURDATE())";
        $stmt2 = $this->conn->prepare($sqlDangChay);
        $stmt2->execute();

        if (((int)$stmt1->rowCount() + (int)$stmt2->rowCount()) > 0) {
            $this->clearScheduleReadCache();
        }
    }

    // Lấy tất cả lịch khởi hành
    public function getAll(?int $limit = null, int $offset = 0): array {
        $sql = "SELECT 
                    " . $this->lichKhoiHanhSelectColumns('lk') . ", 
                    t.ten_tour, 
                    t.loai_tour,
                    COUNT(DISTINCT pbn.id) AS so_nhan_su
                FROM lich_khoi_hanh lk
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                LEFT JOIN phan_bo_nhan_su pbn ON pbn.lich_khoi_hanh_id = lk.id AND " . $this->assignmentNotDeletedClause('phan_bo_nhan_su', 'pbn') . "
                WHERE " . $this->scheduleNotDeletedClause('lk') . "
                GROUP BY lk.id
                ORDER BY lk.ngay_khoi_hanh DESC, lk.gio_xuat_phat DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = $this->conn->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(2, max(0, (int)$offset), PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    public function getOptions(?int $limit = null): array {
        $limitValue = $limit !== null ? max(1, (int)$limit) : 0;
        $cacheKey = 'lich_khoi_hanh_options_' . ($limitValue > 0 ? ('limit_' . $limitValue) : 'all');

        return cacheRemember($cacheKey, 120, function () use ($limitValue) {
            $sql = "SELECT lk.id, lk.ngay_khoi_hanh, t.ten_tour
                    FROM lich_khoi_hanh lk
                    LEFT JOIN tour t ON lk.tour_id = t.tour_id
                    WHERE " . $this->scheduleNotDeletedClause('lk') . "
                    ORDER BY lk.ngay_khoi_hanh DESC, lk.id DESC";
            if ($limitValue > 0) {
                $sql .= " LIMIT ?";
            }

            $stmt = $this->conn->prepare($sql);
            if ($limitValue > 0) {
                $stmt->bindValue(1, $limitValue, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }

    public function getUpcomingOptions(int $limit = 200, int $daysAhead = 365): array {
        $limit = max(1, (int)$limit);
        $daysAhead = max(7, (int)$daysAhead);
        $cacheKey = 'lich_khoi_hanh_upcoming_limit_' . $limit . '_days_' . $daysAhead;

        return cacheRemember($cacheKey, 120, function () use ($limit, $daysAhead) {
            $sql = "SELECT lk.id, lk.ngay_khoi_hanh, lk.trang_thai, t.ten_tour
                    FROM lich_khoi_hanh lk
                    LEFT JOIN tour t ON lk.tour_id = t.tour_id
                                        WHERE " . $this->scheduleNotDeletedClause('lk') . "
                                            AND lk.ngay_khoi_hanh >= CURDATE()
                      AND lk.ngay_khoi_hanh <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
                    ORDER BY lk.ngay_khoi_hanh ASC, lk.id ASC
                    LIMIT ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $daysAhead, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }

    // Lấy danh sách lịch khởi hành theo bộ lọc (thực hiện filter tại SQL).
    public function getAllFiltered(array $filters = []): array {
        $sql = "SELECT
                    " . $this->lichKhoiHanhSelectColumns('lk') . ",
                    t.ten_tour,
                    t.loai_tour,
                    (SELECT hat.url_anh FROM hinh_anh_tour hat WHERE hat.tour_id = t.tour_id ORDER BY hat.id ASC LIMIT 1) AS hinh_anh,
                    COUNT(DISTINCT pbn.id) AS so_nhan_su,
                    COUNT(DISTINCT pbdv.id) AS so_dich_vu,
                    GROUP_CONCAT(DISTINCT CASE WHEN pbn.vai_tro = 'HDV' THEN pbn.nhan_su_id END ORDER BY pbn.nhan_su_id SEPARATOR ',') AS hdv_ids
                FROM lich_khoi_hanh lk
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                LEFT JOIN phan_bo_nhan_su pbn ON pbn.lich_khoi_hanh_id = lk.id AND " . $this->assignmentNotDeletedClause('phan_bo_nhan_su', 'pbn') . "
                LEFT JOIN phan_bo_dich_vu pbdv ON pbdv.lich_khoi_hanh_id = lk.id AND " . $this->assignmentNotDeletedClause('phan_bo_dich_vu', 'pbdv') . "
                WHERE " . $this->scheduleNotDeletedClause('lk') . "";

        $where = [];
        $having = [];
        $params = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $where[] = "(t.ten_tour LIKE ? OR lk.diem_tap_trung LIKE ?)";
            $keyword = '%' . $search . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $tourId = !empty($filters['tour_id']) ? (int)$filters['tour_id'] : 0;
        if ($tourId > 0) {
            $where[] = "lk.tour_id = ?";
            $params[] = $tourId;
        }

        $tuNgay = trim((string)($filters['tu_ngay'] ?? ''));
        if ($tuNgay !== '') {
            $where[] = "lk.ngay_khoi_hanh >= ?";
            $params[] = $tuNgay;
        }

        $denNgay = trim((string)($filters['den_ngay'] ?? ''));
        if ($denNgay !== '') {
            $where[] = "lk.ngay_khoi_hanh <= ?";
            $params[] = $denNgay;
        }

        $trangThai = trim((string)($filters['trang_thai'] ?? ''));
        if ($trangThai !== '') {
            if ($trangThai === 'ChoPhanBo') {
                $having[] = 'COUNT(DISTINCT pbn.id) = 0';
            } else {
                $where[] = 'lk.trang_thai = ?';
                $params[] = $trangThai;
            }
        }

        if (!empty($where)) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY lk.id';

        if (!empty($having)) {
            $sql .= ' HAVING ' . implode(' AND ', $having);
        }

        $sql .= ' ORDER BY lk.ngay_khoi_hanh DESC, lk.gio_xuat_phat DESC';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Lấy lịch khởi hành theo ID
    public function findById(int $id): mixed {
        $sql = "SELECT " . $this->lichKhoiHanhSelectColumns('lk') . ", t.ten_tour, t.loai_tour, t.gia_co_ban
                FROM lich_khoi_hanh lk
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                WHERE lk.id = ? AND " . $this->scheduleNotDeletedClause('lk');
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    // Lấy lịch khởi hành theo tour_id
    public function getByTourId(int $tourId): array {
        $sql = "SELECT " . $this->lichKhoiHanhSelectColumns('lk') . ", t.ten_tour, t.gia_co_ban
                FROM lich_khoi_hanh lk
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                                WHERE lk.tour_id = ?
                                    AND " . $this->scheduleNotDeletedClause('lk') . "
                ORDER BY lk.ngay_khoi_hanh ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        return $stmt->fetchAll();
    }

    // Thống kê số lịch khởi hành theo tháng cho dashboard.
    public function getScheduleCountByMonth(int $months = 12): array {
        $months = max(1, (int)$months);
        $sql = "SELECT DATE_FORMAT(ngay_khoi_hanh, '%Y-%m') AS thang, COUNT(*) AS total
                FROM lich_khoi_hanh
                WHERE ngay_khoi_hanh IS NOT NULL
                                    AND " . $this->scheduleNotDeletedClause() . "
                  AND ngay_khoi_hanh >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(ngay_khoi_hanh, '%Y-%m')
                ORDER BY DATE_FORMAT(ngay_khoi_hanh, '%Y-%m') ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$months]);

        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $month = (string)($row['thang'] ?? '');
            if ($month !== '') {
                $result[$month] = (int)($row['total'] ?? 0);
            }
        }

        return $result;
    }

    // Tổng hợp chỉ số vận hành lịch khởi hành và tỷ lệ lấp đầy chỗ
    public function getDashboardOperations(): array {
        $notDeleted = $this->scheduleNotDeletedClause();
        $sqlStatus = "SELECT trang_thai, COUNT(*) AS count, COALESCE(SUM(so_cho), 0) AS tong_cho
                      FROM lich_khoi_hanh
                      WHERE $notDeleted
                      GROUP BY trang_thai";
        $stmtStatus = $this->conn->query($sqlStatus);
        $statusMap = [];
        $totalCapacity = 0;
        if ($stmtStatus) {
            while ($row = $stmtStatus->fetch(PDO::FETCH_ASSOC)) {
                $st = (string)($row['trang_thai'] ?? 'Khac');
                $statusMap[$st] = (int)($row['count'] ?? 0);
                $totalCapacity += (int)($row['tong_cho'] ?? 0);
            }
        }

        $activeCount = (int)($statusMap['DangChay'] ?? 0);

        $sqlUpcoming = "SELECT COUNT(*) FROM lich_khoi_hanh
                        WHERE $notDeleted
                          AND ngay_khoi_hanh BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $upcomingCount = (int)($this->conn->query($sqlUpcoming)?->fetchColumn() ?? 0);

        $sqlGuests = "SELECT COALESCE(SUM(b.so_nguoi), 0) AS total_guests
                      FROM booking b
                      WHERE b.trang_thai != 'Huy' AND b.is_deleted = 0";
        $totalGuests = (int)($this->conn->query($sqlGuests)?->fetchColumn() ?? 0);

        $occupancyRate = $totalCapacity > 0 ? round(($totalGuests / $totalCapacity) * 100, 1) : 0.0;

        return [
            'status_counts' => $statusMap,
            'active_running' => $activeCount,
            'upcoming_7_days' => $upcomingCount,
            'total_capacity' => $totalCapacity,
            'total_guests' => $totalGuests,
            'occupancy_rate' => $occupancyRate,
        ];
    }

    // Tìm lịch khởi hành theo tour và ngày khởi hành (dùng để map từ booking)
    public function findByTourAndNgayKhoiHanh(int $tourId, string $ngayKhoiHanh): mixed {
        $sql = "SELECT " . $this->lichKhoiHanhSelectColumns() . " FROM lich_khoi_hanh 
                WHERE tour_id = ? AND ngay_khoi_hanh = ?
                                    AND " . $this->scheduleNotDeletedClause() . "
                ORDER BY id ASC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId, $ngayKhoiHanh]);
        return $stmt->fetch();
    }

    // Thêm lịch khởi hành mới
    public function insert(array $data): string|false {
        $sql = "INSERT INTO lich_khoi_hanh (tour_id, ngay_khoi_hanh, gio_xuat_phat, ngay_ket_thuc, gio_ket_thuc, diem_tap_trung, so_cho, hdv_id, trang_thai, ghi_chu) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['tour_id'] ?? null,
            $data['ngay_khoi_hanh'] ?? null,
            $data['gio_xuat_phat'] ?? null,
            $data['ngay_ket_thuc'] ?? null,
            $data['gio_ket_thuc'] ?? null,
            $data['diem_tap_trung'] ?? '',
            $data['so_cho'] ?? 50,
            $data['hdv_id'] ?? null,
            $data['trang_thai'] ?? 'SapKhoiHanh',
            $data['ghi_chu'] ?? null
        ]);
        
        if ($result) {
            $this->clearScheduleReadCache();
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật lịch khởi hành
    public function update(int $id, array $data): bool {
        $sql = "UPDATE lich_khoi_hanh SET 
                tour_id = ?, ngay_khoi_hanh = ?, gio_xuat_phat = ?, 
                ngay_ket_thuc = ?, gio_ket_thuc = ?, diem_tap_trung = ?, 
                so_cho = ?, hdv_id = ?, trang_thai = ?, ghi_chu = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['tour_id'] ?? null,
            $data['ngay_khoi_hanh'] ?? null,
            $data['gio_xuat_phat'] ?? null,
            $data['ngay_ket_thuc'] ?? null,
            $data['gio_ket_thuc'] ?? null,
            $data['diem_tap_trung'] ?? '',
            $data['so_cho'] ?? 50,
            $data['hdv_id'] ?? null,
            $data['trang_thai'] ?? 'SapKhoiHanh',
            $data['ghi_chu'] ?? null,
            $id
        ]);

        if ($result) {
            $this->clearScheduleReadCache();
        }

        return $result;
    }

    // Cập nhật % hoa hồng HDV cho lịch khởi hành (cần cột lich_khoi_hanh.phan_tram_hoa_hong_hdv)
    public function updatePhanTramHoaHongHDV(int $id, int|float $phanTram): bool {
        try {
            $sql = "UPDATE lich_khoi_hanh SET phan_tram_hoa_hong_hdv = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([(float)$phanTram, (int)$id]);
            return (int)$stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // Gán HDV chính cho lịch khởi hành
    public function assignHDV(int $lichKhoiHanhId, ?int $nhanSuId): bool {
        $sql = "UPDATE lich_khoi_hanh SET hdv_id = ? WHERE id = ? AND " . $this->scheduleNotDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $nhanSuId !== null ? (int)$nhanSuId : null,
            (int)$lichKhoiHanhId
        ]);

        if ($result) {
            $this->clearScheduleReadCache();
        }

        return $result;
    }

    // Xóa lịch khởi hành
    public function delete(int $id): bool {
        if ($this->hasColumn('lich_khoi_hanh', 'deleted_at')) {
            $sql = "UPDATE lich_khoi_hanh SET deleted_at = NOW(), trang_thai = 'DaHuy' WHERE id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM lich_khoi_hanh WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([(int)$id]);
        if ($result) {
            $this->clearScheduleReadCache();
        }
        return $result;
    }

    // Lấy lịch khởi hành với đầy đủ thông tin
    public function getWithDetails(int $id): mixed {
        $sql = "SELECT " . $this->lichKhoiHanhSelectColumns('lk') . ", 
                t.ten_tour, t.loai_tour, t.gia_co_ban,
                COUNT(DISTINCT b.booking_id) as so_booking,
                COALESCE(SUM(b.so_nguoi), 0) as tong_nguoi_dat
                FROM lich_khoi_hanh lk
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                LEFT JOIN booking b ON lk.tour_id = b.tour_id 
                    AND b.ngay_khoi_hanh = lk.ngay_khoi_hanh
                    AND " . $this->bookingNotDeletedClause('b') . "
                    AND b.trang_thai IN ('ChoXacNhan', 'DaCoc', 'HoanTat')
                WHERE lk.id = ? AND " . $this->scheduleNotDeletedClause('lk') . "
                GROUP BY lk.id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    // Lấy các lịch khởi hành mà HDV đã được phân công chính
    public function getByHdvId(int $hdvId): array {
        $sql = "SELECT " . $this->lichKhoiHanhSelectColumns('lk') . ", 
                       t.ten_tour, 
                       t.loai_tour,
                       t.gia_co_ban
                FROM lich_khoi_hanh lk
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                                WHERE lk.hdv_id = ?
                                    AND " . $this->scheduleNotDeletedClause('lk') . "
                ORDER BY lk.ngay_khoi_hanh DESC, lk.gio_xuat_phat DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$hdvId]);
        return $stmt->fetchAll();
    }
}

