<?php
// Model cho PhanBoNhanSu - Phân bổ nhân sự cho lịch khởi hành
class PhanBoNhanSu 
{
    // Các trường lương linh hoạt
    public ?string $loai_luong = null;
    public int|float|string|null $so_tien_co_dinh = null;
    public int|float|string|null $phan_tram_hoa_hong = null;
    public int|float|string|null $tien_hoa_hong = null;
    public int|float|string|null $tong_luong = null;
    public ?string $trang_thai_luong = null;
    public ?string $ngay_tao_luong = null;
    public ?string $ngay_cap_nhat_luong = null;
    public PDO $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function getTableColumns(string $tableName): array {
        static $tableColumns = [];
        if (!array_key_exists($tableName, $tableColumns)) {
            $sql = "SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?
                    ORDER BY ORDINAL_POSITION";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tableName]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = [];
            foreach ($rows as $row) {
                $name = (string)($row['COLUMN_NAME'] ?? '');
                if ($name !== '') {
                    $columns[] = $name;
                }
            }
            $tableColumns[$tableName] = $columns;
        }

        return $tableColumns[$tableName];
    }

    private function selectColumnsFromTable(string $tableName, string $alias = ''): string {
        $columns = $this->getTableColumns($tableName);
        if (empty($columns)) {
            return $alias !== '' ? ($alias . '.id') : 'id';
        }

        if ($alias === '') {
            return implode(', ', $columns);
        }

        $prefixed = array_map(static function ($column) use ($alias) {
            return $alias . '.' . $column;
        }, $columns);
        return implode(', ', $prefixed);
    }

    private function phanBoSelectColumns(string $alias = ''): string {
        return $this->selectColumnsFromTable('phan_bo_nhan_su', $alias);
    }

    private function columnExists(string $table, string $column): bool {
        static $cache = [];
        static $tableColumns = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        try {
            if (!array_key_exists($table, $tableColumns)) {
                $sql = "SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$table]);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $columnMap = [];
                foreach ($rows as $row) {
                    $name = (string)($row['COLUMN_NAME'] ?? '');
                    if ($name !== '') {
                        $columnMap[$name] = true;
                    }
                }
                $tableColumns[$table] = $columnMap;
            }

            $cache[$key] = isset($tableColumns[$table][$column]);
        } catch (Exception $e) {
            $cache[$key] = false;
        }
        return $cache[$key];
    }

    private function notDeletedClause(string $alias = 'pbn'): string {
        if (!$this->columnExists('phan_bo_nhan_su', 'deleted_at')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    // Lấy phân bổ theo ID
    public function findById(int $id): mixed {
        $sql = "SELECT " . $this->phanBoSelectColumns() . " FROM phan_bo_nhan_su WHERE id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    // Lấy phân bổ nhân sự theo lịch khởi hành
    public function getByLichKhoiHanh(int $lichKhoiHanhId): array {
        $sql = "SELECT " . $this->phanBoSelectColumns('pbn') . ", 
                ns.nhan_su_id, ns.vai_tro as ns_vai_tro,
                nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM phan_bo_nhan_su pbn
                LEFT JOIN nhan_su ns ON pbn.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE pbn.lich_khoi_hanh_id = ?
                                    AND " . $this->notDeletedClause('pbn') . "
                ORDER BY pbn.vai_tro, nd.ho_ten";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Lấy phân bổ nhân sự theo vai trò
    public function getByVaiTro(int $lichKhoiHanhId, string $vaiTro): array {
        $sql = "SELECT " . $this->phanBoSelectColumns('pbn') . ", 
                ns.nhan_su_id,
                nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM phan_bo_nhan_su pbn
                LEFT JOIN nhan_su ns ON pbn.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                                WHERE pbn.lich_khoi_hanh_id = ? AND pbn.vai_tro = ?
                                    AND " . $this->notDeletedClause('pbn');
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId, $vaiTro]);
        return $stmt->fetchAll();
    }


    // Thêm phân bổ nhân sự (có lương)
    public function insert(array $data): string|false {
        $sql = "INSERT INTO phan_bo_nhan_su (lich_khoi_hanh_id, nhan_su_id, vai_tro, ghi_chu, trang_thai,
            loai_luong, so_tien_co_dinh, phan_tram_hoa_hong, tien_hoa_hong, tong_luong, trang_thai_luong, ngay_tao_luong, ngay_cap_nhat_luong)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['lich_khoi_hanh_id'] ?? 0,
            $data['nhan_su_id'] ?? 0,
            $data['vai_tro'] ?? 'Khac',
            $data['ghi_chu'] ?? null,
            $data['trang_thai'] ?? 'ChoXacNhan',
            $data['loai_luong'] ?? 'CoDinh',
            $data['so_tien_co_dinh'] ?? 0,
            $data['phan_tram_hoa_hong'] ?? 0,
            $data['tien_hoa_hong'] ?? 0,
            $data['tong_luong'] ?? 0,
            $data['trang_thai_luong'] ?? 'ChoDuyet',
            $data['ngay_tao_luong'] ?? date('Y-m-d H:i:s'),
            $data['ngay_cap_nhat_luong'] ?? date('Y-m-d H:i:s'),
        ]);
        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật lương cho nhân sự phân bổ
    public function updateLuong(int $id, array $data): bool {
        $sql = "UPDATE phan_bo_nhan_su SET 
            loai_luong = ?, so_tien_co_dinh = ?, phan_tram_hoa_hong = ?, tien_hoa_hong = ?, tong_luong = ?,
            trang_thai_luong = ?, ngay_cap_nhat_luong = NOW()
            WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['loai_luong'] ?? 'CoDinh',
            $data['so_tien_co_dinh'] ?? 0,
            $data['phan_tram_hoa_hong'] ?? 0,
            $data['tien_hoa_hong'] ?? 0,
            $data['tong_luong'] ?? 0,
            $data['trang_thai_luong'] ?? 'ChoDuyet',
            $id
        ]);
    }

    // Tính lương tự động cho nhân sự theo doanh thu tour (nếu là phần trăm hoặc kết hợp)
    public function tinhLuongTuDong(int $phanBoId, int|float $doanhThuTour): float|false {
        $row = $this->findById($phanBoId);
        if (!$row) return false;
        $tongLuong = 0;
        $tienHoaHong = 0;
        if ($row['loai_luong'] == 'CoDinh') {
            $tongLuong = $row['so_tien_co_dinh'];
        } elseif ($row['loai_luong'] == 'PhanTram') {
            $tienHoaHong = round($doanhThuTour * $row['phan_tram_hoa_hong'] / 100, 2);
            $tongLuong = $tienHoaHong;
        } elseif ($row['loai_luong'] == 'KetHop') {
            $tienHoaHong = round($doanhThuTour * $row['phan_tram_hoa_hong'] / 100, 2);
            $tongLuong = $row['so_tien_co_dinh'] + $tienHoaHong;
        }
        // Cập nhật lại lương
        $this->updateLuong($phanBoId, [
            'tien_hoa_hong' => $tienHoaHong,
            'tong_luong' => $tongLuong
        ]);
        return $tongLuong;
    }

    // Lấy thông tin lương của nhân sự theo lịch khởi hành
    public function getLuongByLichKhoiHanh(int $lichKhoiHanhId): array {
        $sql = "SELECT " . $this->phanBoSelectColumns() . " FROM phan_bo_nhan_su WHERE lich_khoi_hanh_id = ? AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Cập nhật phân bổ nhân sự
    public function update(int $id, array $data): bool {
        $sql = "UPDATE phan_bo_nhan_su SET 
                nhan_su_id = ?, vai_tro = ?, ghi_chu = ?, trang_thai = ?,
                thoi_gian_xac_nhan = ?
                WHERE id = ?";
        $thoiGianXacNhan = isset($data['trang_thai']) && $data['trang_thai'] == 'DaXacNhan' 
            ? date('Y-m-d H:i:s') 
            : null;
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nhan_su_id'] ?? 0,
            $data['vai_tro'] ?? 'Khac',
            $data['ghi_chu'] ?? null,
            $data['trang_thai'] ?? 'ChoXacNhan',
            $thoiGianXacNhan,
            $id
        ]);
    }

    // Xóa phân bổ nhân sự
    public function delete(int $id): bool {
        if ($this->columnExists('phan_bo_nhan_su', 'deleted_at')) {
            $sql = "UPDATE phan_bo_nhan_su SET deleted_at = NOW(), trang_thai = 'Huy' WHERE id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM phan_bo_nhan_su WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }

    // Cập nhật trạng thái xác nhận
    public function updateTrangThai(int $id, string $trangThai, ?int $nguoiThayDoiId = null): bool {
        $sql = "UPDATE phan_bo_nhan_su SET 
                trang_thai = ?,
                thoi_gian_xac_nhan = ?
                WHERE id = ?";
        $thoiGian = ($trangThai == 'DaXacNhan' || $trangThai == 'TuChoi') 
            ? date('Y-m-d H:i:s') 
            : null;
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$trangThai, $thoiGian, (int)$id]);
    }

    /**
     * Tìm các lịch khởi hành khác mà nhân sự (HDV) này đang được phân bổ
     * và bị trùng khoảng ngày với lịch hiện tại.
     * Dùng để cảnh báo trùng lịch, KHÔNG chặn phân bổ.
     *
     * @param int $lichKhoiHanhId
     * @param int $nhanSuId
     * @return array
     */
    public function getScheduleConflictsForStaff(int $lichKhoiHanhId, int $nhanSuId): array {
        $lichKhoiHanhId = (int)$lichKhoiHanhId;
        $nhanSuId = (int)$nhanSuId;
        if ($lichKhoiHanhId <= 0 || $nhanSuId <= 0) {
            return [];
        }

        // Lấy khoảng ngày của lịch hiện tại
        $sqlBase = "SELECT ngay_khoi_hanh, COALESCE(ngay_ket_thuc, ngay_khoi_hanh) AS ngay_ket_thuc
                    FROM lich_khoi_hanh
                    WHERE id = ?";
        $stmtBase = $this->conn->prepare($sqlBase);
        $stmtBase->execute([$lichKhoiHanhId]);
        $current = $stmtBase->fetch();
        if (!$current || empty($current['ngay_khoi_hanh'])) {
            return [];
        }

        $start = $current['ngay_khoi_hanh'];
        $end   = $current['ngay_ket_thuc'];

        // Tìm các lịch khác mà HDV này đang dẫn (hdv_id) hoặc đã được phân bổ vai trò HDV
        $sql = "SELECT 
                    lk2.id,
                    lk2.ngay_khoi_hanh,
                    lk2.ngay_ket_thuc,
                    t.ten_tour
                FROM lich_khoi_hanh lk2
                LEFT JOIN tour t ON lk2.tour_id = t.tour_id
                LEFT JOIN phan_bo_nhan_su p2 
                       ON p2.lich_khoi_hanh_id = lk2.id 
                      AND p2.vai_tro = 'HDV'
                WHERE lk2.id <> ?
                  AND lk2.trang_thai IN ('SapKhoiHanh','DangChay')
                  AND (
                        lk2.hdv_id = ?
                        OR p2.nhan_su_id = ?
                  )
                  -- Điều kiện trùng ngày: NOT (end2 < start1 OR end1 < start2)
                  AND NOT (
                        COALESCE(lk2.ngay_ket_thuc, lk2.ngay_khoi_hanh) < ?
                        OR ? < lk2.ngay_khoi_hanh
                  )
                ORDER BY lk2.ngay_khoi_hanh ASC, lk2.id ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $lichKhoiHanhId,
            $nhanSuId,
            $nhanSuId,
            $start,
            $end,
        ]);

        return $stmt->fetchAll();
    }

    private function extractHdvIdsFromSchedule(array $schedule): array {
        $hdvIds = [];

        if (!empty($schedule['hdv_id'])) {
            $hdvIds[] = (int)$schedule['hdv_id'];
        }

        if (!empty($schedule['hdv_ids'])) {
            foreach (explode(',', (string)$schedule['hdv_ids']) as $id) {
                $id = (int)trim($id);
                if ($id > 0) {
                    $hdvIds[] = $id;
                }
            }
        }

        return array_values(array_unique(array_filter($hdvIds)));
    }

    private function schedulesOverlap(array $left, array $right): bool {
        $leftStart = $left['ngay_khoi_hanh'] ?? null;
        $rightStart = $right['ngay_khoi_hanh'] ?? null;
        if (empty($leftStart) || empty($rightStart)) {
            return false;
        }

        $leftEnd = $left['ngay_ket_thuc'] ?? $leftStart;
        $rightEnd = $right['ngay_ket_thuc'] ?? $rightStart;

        return !($rightEnd < $leftStart || $leftEnd < $rightStart);
    }

    public function getScheduleConflictSummary(array $scheduleRows): array {
        if (empty($scheduleRows)) {
            return [];
        }

        $staffIds = [];
        foreach ($scheduleRows as $schedule) {
            foreach ($this->extractHdvIdsFromSchedule($schedule) as $staffId) {
                $staffIds[$staffId] = true;
            }
        }

        if (empty($staffIds)) {
            return [];
        }

        $staffIds = array_keys($staffIds);
        $placeholders = implode(',', array_fill(0, count($staffIds), '?'));
        $sql = "SELECT
                    lk.id,
                    lk.hdv_id,
                    lk.ngay_khoi_hanh,
                    COALESCE(lk.ngay_ket_thuc, lk.ngay_khoi_hanh) AS ngay_ket_thuc,
                    GROUP_CONCAT(DISTINCT pbn.nhan_su_id ORDER BY pbn.nhan_su_id SEPARATOR ',') AS hdv_ids
                FROM lich_khoi_hanh lk
                LEFT JOIN phan_bo_nhan_su pbn
                       ON pbn.lich_khoi_hanh_id = lk.id
                      AND pbn.vai_tro = 'HDV'
                WHERE lk.trang_thai IN ('SapKhoiHanh','DangChay')
                  AND (
                        lk.hdv_id IN ($placeholders)
                        OR pbn.nhan_su_id IN ($placeholders)
                  )
                GROUP BY lk.id, lk.hdv_id, lk.ngay_khoi_hanh, COALESCE(lk.ngay_ket_thuc, lk.ngay_khoi_hanh)";

        $params = array_merge($staffIds, $staffIds);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $activeSchedules = $stmt->fetchAll();

        if (empty($activeSchedules)) {
            return [];
        }

        $conflictSummary = [];
        foreach ($scheduleRows as $schedule) {
            $scheduleId = (int)($schedule['id'] ?? 0);
            if ($scheduleId <= 0) {
                continue;
            }

            $currentStaffIds = $this->extractHdvIdsFromSchedule($schedule);
            if (empty($currentStaffIds)) {
                $conflictSummary[$scheduleId] = 0;
                continue;
            }

            $currentStaffMap = array_fill_keys($currentStaffIds, true);
            $conflictingScheduleIds = [];

            foreach ($activeSchedules as $activeSchedule) {
                $activeScheduleId = (int)($activeSchedule['id'] ?? 0);
                if ($activeScheduleId <= 0 || $activeScheduleId === $scheduleId) {
                    continue;
                }
                if (!$this->schedulesOverlap($schedule, $activeSchedule)) {
                    continue;
                }

                $activeStaffIds = $this->extractHdvIdsFromSchedule($activeSchedule);
                foreach ($activeStaffIds as $activeStaffId) {
                    if (isset($currentStaffMap[$activeStaffId])) {
                        $conflictingScheduleIds[$activeScheduleId] = true;
                        break;
                    }
                }
            }

            $conflictSummary[$scheduleId] = count($conflictingScheduleIds);
        }

        return $conflictSummary;
    }

    /**
     * Tự động phân bổ 1 HDV cho lịch khởi hành nếu chưa có phân bổ
     * - Chỉ chạy khi:
     *   + lich_khoi_hanh chưa có hdv_id
     *   + bảng phan_bo_nhan_su chưa có bản ghi vai_tro = 'HDV' cho lịch này
     * - Ưu tiên HDV đang sẵn sàng, đã dẫn ít tour hơn
     *
     */
    public function autoAssignHDVIfMissing(int $lichKhoiHanhId): ?int {
        $lichKhoiHanhId = (int)$lichKhoiHanhId;
        if ($lichKhoiHanhId <= 0) {
            return null;
        }

        // 1. Kiểm tra lịch đã có HDV hoặc đã có phân bổ HDV chưa
        $sqlCheck = "SELECT 
                        lk.hdv_id,
                        lk.ngay_khoi_hanh,
                        lk.ngay_ket_thuc,
                        SUM(CASE WHEN pbn.vai_tro = 'HDV' THEN 1 ELSE 0 END) AS so_phan_bo_hdv
                     FROM lich_khoi_hanh lk
                     LEFT JOIN phan_bo_nhan_su pbn ON pbn.lich_khoi_hanh_id = lk.id
                     WHERE lk.id = ?
                     GROUP BY lk.id, lk.hdv_id";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([$lichKhoiHanhId]);
        $rowCheck = $stmtCheck->fetch();

        if (!$rowCheck) {
            // Lịch không tồn tại
            return null;
        }

        if (!empty($rowCheck['hdv_id']) || (int)$rowCheck['so_phan_bo_hdv'] > 0) {
            // Đã có HDV hoặc đã có phân bổ HDV => không tự động
            return null;
        }

        // 2. Xác định khoảng thời gian của lịch khởi hành hiện tại
        $ngayBatDau = $rowCheck['ngay_khoi_hanh'] ?? null;
        $ngayKetThuc = $rowCheck['ngay_ket_thuc'] ?? null;
        if (!$ngayBatDau) {
            // Thiếu thông tin ngày => không tự động để tránh gán sai
            return null;
        }
        if (!$ngayKetThuc) {
            $ngayKetThuc = $ngayBatDau;
        }

        // 3. Chọn 1 HDV đang sẵn sàng, ít tour nhất,
        //    không bị trùng lịch với tour khác (SapKhoiHanh/DangChay)
        $sqlPick = "SELECT ns.nhan_su_id
                    FROM nhan_su ns
                    WHERE ns.vai_tro = 'HDV'
                      AND (ns.trang_thai_lam_viec IS NULL OR ns.trang_thai_lam_viec = 'SanSang')
                      AND NOT EXISTS (
                          SELECT 1
                          FROM lich_khoi_hanh lk2
                          LEFT JOIN phan_bo_nhan_su p2 
                                 ON p2.lich_khoi_hanh_id = lk2.id 
                                AND p2.vai_tro = 'HDV'
                          WHERE lk2.trang_thai IN ('SapKhoiHanh','DangChay')
                            AND lk2.id <> ?
                            AND (
                                lk2.hdv_id = ns.nhan_su_id
                                OR p2.nhan_su_id = ns.nhan_su_id
                            )
                            -- Điều kiện trùng khoảng ngày: NOT (end2 < start1 OR end1 < start2)
                            AND NOT (
                                COALESCE(lk2.ngay_ket_thuc, lk2.ngay_khoi_hanh) < ?
                                OR ? < lk2.ngay_khoi_hanh
                            )
                      )
                    ORDER BY COALESCE(ns.so_tour_da_dan, 0) ASC, ns.nhan_su_id ASC
                    LIMIT 1";
        $stmtPick = $this->conn->prepare($sqlPick);
        $stmtPick->execute([
            $lichKhoiHanhId,
            $ngayBatDau,
            $ngayKetThuc,
        ]);
        $hdv = $stmtPick->fetch();

        if (empty($hdv) || empty($hdv['nhan_su_id'])) {
            // Không có HDV phù hợp
            return null;
        }

        $nhanSuId = (int)$hdv['nhan_su_id'];

        // 3. Tạo bản ghi phân bổ nhân sự cho lịch khởi hành
        $dataInsert = [
            'lich_khoi_hanh_id' => $lichKhoiHanhId,
            'nhan_su_id'        => $nhanSuId,
            'vai_tro'           => 'HDV',
            'ghi_chu'           => 'Tự động phân bổ do chưa có HDV',
            'trang_thai'        => 'DaXacNhan',
        ];

        $idPhanBo = $this->insert($dataInsert);
        if (!$idPhanBo) {
            return null;
        }

        // Cập nhật thời gian xác nhận ngay lập tức
        $sqlTime = "UPDATE phan_bo_nhan_su 
                    SET thoi_gian_xac_nhan = ? 
                    WHERE id = ?";
        $stmtTime = $this->conn->prepare($sqlTime);
        $stmtTime->execute([date('Y-m-d H:i:s'), (int)$idPhanBo]);

        // 4. Gán HDV chính cho lịch khởi hành
        require_once __DIR__ . '/LichKhoiHanh.php';
        $lichKhoiHanhModel = new LichKhoiHanh();
        $lichKhoiHanhModel->assignHDV($lichKhoiHanhId, $nhanSuId);

        return $nhanSuId;
    }

    // Lấy tất cả lương thưởng nhân sự (lọc theo nhân sự, tour, tháng, năm)
    public function getAllLuong(array $filters = []): array {
        $sql = "SELECT " . $this->phanBoSelectColumns('pbn') . ", nd.ho_ten, t.ten_tour, lk.ngay_khoi_hanh, lk.ngay_ket_thuc
                FROM phan_bo_nhan_su pbn
                LEFT JOIN nhan_su ns ON pbn.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                LEFT JOIN lich_khoi_hanh lk ON pbn.lich_khoi_hanh_id = lk.id
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['nhan_su_id'])) {
            $sql .= " AND pbn.nhan_su_id = ?";
            $params[] = $filters['nhan_su_id'];
        }
        if (!empty($filters['tour_id'])) {
            $sql .= " AND t.tour_id = ?";
            $params[] = $filters['tour_id'];
        }
        if (!empty($filters['month'])) {
            $sql .= " AND MONTH(lk.ngay_khoi_hanh) = ?";
            $params[] = $filters['month'];
        }
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(lk.ngay_khoi_hanh) = ?";
            $params[] = $filters['year'];
        }
        $sql .= " ORDER BY lk.ngay_khoi_hanh DESC, pbn.nhan_su_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Lấy chi tiết lương/thưởng của nhân sự theo tháng/năm
     */
    public function getLuongByNhanSuThangNam(int $nhanSuId, int $month, int $year): array {
        $sql = "SELECT " . $this->phanBoSelectColumns('pbn') . ", nd.ho_ten, t.ten_tour, lk.ngay_khoi_hanh, lk.ngay_ket_thuc
                FROM phan_bo_nhan_su pbn
                LEFT JOIN nhan_su ns ON pbn.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                LEFT JOIN lich_khoi_hanh lk ON pbn.lich_khoi_hanh_id = lk.id
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                WHERE pbn.nhan_su_id = ? AND MONTH(lk.ngay_khoi_hanh) = ? AND YEAR(lk.ngay_khoi_hanh) = ?
                ORDER BY lk.ngay_khoi_hanh DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhanSuId, $month, $year]);
        return $stmt->fetchAll();
    }

    // Tìm phân bổ theo lịch khởi hành + nhân sự (để tránh tạo trùng)
    public function findByLichKhoiHanhAndNhanSu(int $lichKhoiHanhId, int $nhanSuId): mixed {
        $sql = "SELECT " . $this->phanBoSelectColumns() . "
                FROM phan_bo_nhan_su
                WHERE lich_khoi_hanh_id = ? AND nhan_su_id = ?
                ORDER BY id ASC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId, (int)$nhanSuId]);
        return $stmt->fetch();
    }

    // Doanh thu ước tính theo lịch khởi hành (map từ booking theo tour_id + ngày khởi hành)
    public function getDoanhThuByLichKhoiHanh(int $lichKhoiHanhId): float {
        $sql = "SELECT COALESCE(SUM(b.tong_tien), 0) AS doanh_thu
                FROM lich_khoi_hanh lk
                LEFT JOIN booking b
                       ON b.tour_id = lk.tour_id
                      AND b.ngay_khoi_hanh = lk.ngay_khoi_hanh
                      AND b.trang_thai IN ('DaCoc','HoanTat')
                WHERE lk.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        $row = $stmt->fetch();
        return (float)($row['doanh_thu'] ?? 0);
    }

    // Tổng hợp lương theo nhân sự (lọc theo tour/tháng/năm/trạng thái)
    public function getLuongTongHop(array $filters = []): array {
        $selectLuongCoBan = $this->columnExists('nhan_su', 'luong_co_ban')
            ? "COALESCE(ns.luong_co_ban, 0) AS luong_co_ban,"
            : "0 AS luong_co_ban,";

        $sql = "SELECT
                    pbn.nhan_su_id,
                    nd.ho_ten,
                    ns.vai_tro,
                    {$selectLuongCoBan}
                    COUNT(*) AS so_dong,
                    COALESCE(SUM(pbn.so_tien_co_dinh), 0) AS tong_co_dinh,
                    COALESCE(SUM(pbn.tien_hoa_hong), 0) AS tong_hoa_hong,
                    COALESCE(SUM(pbn.tong_luong), 0) AS tong_luong,
                    MAX(pbn.ngay_cap_nhat_luong) AS ngay_cap_nhat_luong,
                    CASE
                        WHEN SUM(CASE WHEN pbn.trang_thai_luong = 'ChoDuyet' THEN 1 ELSE 0 END) > 0 THEN 'ChoDuyet'
                        WHEN SUM(CASE WHEN pbn.trang_thai_luong = 'DaDuyet' THEN 1 ELSE 0 END) > 0 THEN 'DaDuyet'
                        ELSE 'DaThanhToan'
                    END AS trang_thai_luong_tong_hop
                FROM phan_bo_nhan_su pbn
                LEFT JOIN nhan_su ns ON pbn.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                LEFT JOIN lich_khoi_hanh lk ON pbn.lich_khoi_hanh_id = lk.id
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['nhan_su_id'])) {
            $sql .= " AND pbn.nhan_su_id = ?";
            $params[] = (int)$filters['nhan_su_id'];
        }
        if (!empty($filters['tour_id'])) {
            $sql .= " AND t.tour_id = ?";
            $params[] = (int)$filters['tour_id'];
        }
        if (!empty($filters['month'])) {
            $sql .= " AND MONTH(lk.ngay_khoi_hanh) = ?";
            $params[] = (int)$filters['month'];
        }
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(lk.ngay_khoi_hanh) = ?";
            $params[] = (int)$filters['year'];
        }
        if (!empty($filters['trang_thai_luong'])) {
            $sql .= " AND pbn.trang_thai_luong = ?";
            $params[] = $filters['trang_thai_luong'];
        }

        $sql .= " GROUP BY pbn.nhan_su_id, nd.ho_ten, ns.vai_tro
                  ORDER BY tong_luong DESC, nd.ho_ten ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateLuongFull(int $id, array $data): bool {
        $sql = "UPDATE phan_bo_nhan_su SET
                    loai_luong = ?,
                    so_tien_co_dinh = ?,
                    phan_tram_hoa_hong = ?,
                    tien_hoa_hong = ?,
                    tong_luong = ?,
                    trang_thai_luong = ?,
                    ghi_chu = ?,
                    ngay_tao_luong = COALESCE(ngay_tao_luong, NOW()),
                    ngay_cap_nhat_luong = NOW()
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['loai_luong'] ?? 'CoDinh',
            $data['so_tien_co_dinh'] ?? 0,
            $data['phan_tram_hoa_hong'] ?? 0,
            $data['tien_hoa_hong'] ?? 0,
            $data['tong_luong'] ?? 0,
            $data['trang_thai_luong'] ?? 'ChoDuyet',
            $data['ghi_chu'] ?? null,
            (int)$id
        ]);
    }

    public function updateTrangThaiLuong(int $id, string $trangThaiLuong): bool {
        $sql = "UPDATE phan_bo_nhan_su
                SET trang_thai_luong = ?, ngay_cap_nhat_luong = NOW()
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$trangThaiLuong, (int)$id]);
    }

    public function updateTrangThaiLuongByNhanSuThangNam(int $nhanSuId, int $month, int $year, string $trangThaiLuong): int {
        $whereExtra = "";
        if ($trangThaiLuong === 'DaDuyet') {
            // Không duyệt lại các dòng đã thanh toán
            $whereExtra = " AND pbn.trang_thai_luong <> 'DaThanhToan'";
        } elseif ($trangThaiLuong === 'DaThanhToan') {
            // Chỉ cho phép thanh toán khi đã duyệt
            $whereExtra = " AND pbn.trang_thai_luong = 'DaDuyet'";
        }

        $sql = "UPDATE phan_bo_nhan_su pbn
                LEFT JOIN lich_khoi_hanh lk ON pbn.lich_khoi_hanh_id = lk.id
                SET pbn.trang_thai_luong = ?, pbn.ngay_cap_nhat_luong = NOW()
                WHERE pbn.nhan_su_id = ?
                  AND MONTH(lk.ngay_khoi_hanh) = ?
                  AND YEAR(lk.ngay_khoi_hanh) = ?"
                . $whereExtra;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $trangThaiLuong,
            (int)$nhanSuId,
            (int)$month,
            (int)$year
        ]);
        return (int)$stmt->rowCount();
    }

    // Tính lại lương/hoa hồng cho 1 nhân sự theo tháng/năm (bỏ qua các dòng đã thanh toán)
    public function recalcLuongByNhanSuThangNam(int $nhanSuId, int $month, int $year): int {
        $selectHoaHongLk = $this->columnExists('lich_khoi_hanh', 'phan_tram_hoa_hong_hdv')
            ? ", lk.phan_tram_hoa_hong_hdv"
            : "";

        $sql = "SELECT " . $this->phanBoSelectColumns('pbn') . ", lk.ngay_khoi_hanh{$selectHoaHongLk}
                FROM phan_bo_nhan_su pbn
                LEFT JOIN lich_khoi_hanh lk ON pbn.lich_khoi_hanh_id = lk.id
                WHERE pbn.nhan_su_id = ?
                  AND MONTH(lk.ngay_khoi_hanh) = ?
                  AND YEAR(lk.ngay_khoi_hanh) = ?
                ORDER BY pbn.id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$nhanSuId, (int)$month, (int)$year]);
        $rows = $stmt->fetchAll();
        if (empty($rows)) return 0;

        $updated = 0;
        foreach ($rows as $row) {
            if (($row['trang_thai_luong'] ?? '') === 'DaThanhToan') {
                continue;
            }

            $loaiLuong = $row['loai_luong'] ?? 'CoDinh';
            $soTienCoDinh = (float)($row['so_tien_co_dinh'] ?? 0);
            $phanTramHoaHong = (float)($row['phan_tram_hoa_hong'] ?? 0);
            $doanhThu = $this->getDoanhThuByLichKhoiHanh((int)($row['lich_khoi_hanh_id'] ?? 0));

            // Nếu là HDV: ưu tiên % hoa hồng theo lịch khởi hành (nếu có), và lương theo tour = % * doanh thu
            if (($row['vai_tro'] ?? '') === 'HDV') {
                if (array_key_exists('phan_tram_hoa_hong_hdv', $row)) {
                    $phanTramHoaHong = (float)($row['phan_tram_hoa_hong_hdv'] ?? $phanTramHoaHong);
                }
                $loaiLuong = 'PhanTram';
                $soTienCoDinh = 0;
            }

            $tienHoaHong = 0;
            $tongLuong = 0;
            if ($loaiLuong === 'CoDinh') {
                $tongLuong = $soTienCoDinh;
            } elseif ($loaiLuong === 'PhanTram') {
                $tienHoaHong = round($doanhThu * $phanTramHoaHong / 100, 2);
                $tongLuong = $tienHoaHong;
            } else { // KetHop
                $tienHoaHong = round($doanhThu * $phanTramHoaHong / 100, 2);
                $tongLuong = $soTienCoDinh + $tienHoaHong;
            }

            $ok = $this->updateLuongFull((int)$row['id'], [
                'loai_luong' => $loaiLuong,
                'so_tien_co_dinh' => $soTienCoDinh,
                'phan_tram_hoa_hong' => $phanTramHoaHong,
                'tien_hoa_hong' => $tienHoaHong,
                'tong_luong' => $tongLuong,
                'trang_thai_luong' => $row['trang_thai_luong'] ?? 'ChoDuyet',
                'ghi_chu' => $row['ghi_chu'] ?? null,
            ]);
            if ($ok) $updated++;
        }

        return $updated;
    }
}

