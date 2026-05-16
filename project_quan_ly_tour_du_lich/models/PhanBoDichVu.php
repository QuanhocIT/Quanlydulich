<?php
// Model cho PhanBoDichVu - Phân bổ dịch vụ cho lịch khởi hành
class PhanBoDichVu 
{
    public PDO $conn;
    private static array $columnExistsCache = [];
    private static array $tableColumnsCache = [];
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    private function getTableColumns(string $tableName): array {
        if (!array_key_exists($tableName, self::$tableColumnsCache)) {
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
            self::$tableColumnsCache[$tableName] = $columns;
        }

        return self::$tableColumnsCache[$tableName];
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

    private function phanBoDichVuSelectColumns(string $alias = ''): string {
        return $this->selectColumnsFromTable('phan_bo_dich_vu', $alias);
    }

    private function hasColumn(string $tableName, string $columnName): bool {
        $key = $tableName . '.' . $columnName;
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
        }
        try {
            $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$tableName, $columnName]);
            self::$columnExistsCache[$key] = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            self::$columnExistsCache[$key] = false;
        }
        return self::$columnExistsCache[$key];
    }

    private function notDeletedClause(string $alias = 'pbd'): string {
        if (!$this->hasColumn('phan_bo_dich_vu', 'deleted_at')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }

    // Lấy phân bổ dịch vụ theo lịch khởi hành
    public function getByLichKhoiHanh(int $lichKhoiHanhId): array {
        $sql = "SELECT " . $this->phanBoDichVuSelectColumns('pbd') . ", 
                ncc.ten_don_vi, ncc.loai_dich_vu as ncc_loai_dich_vu
                FROM phan_bo_dich_vu pbd
                LEFT JOIN nha_cung_cap ncc ON pbd.nha_cung_cap_id = ncc.id_nha_cung_cap
                WHERE pbd.lich_khoi_hanh_id = ?
              AND " . $this->notDeletedClause('pbd') . "
                ORDER BY pbd.loai_dich_vu, pbd.ngay_bat_dau";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Lấy phân bổ dịch vụ theo loại
    public function getByLoai(int $lichKhoiHanhId, string $loaiDichVu): array {
        $sql = "SELECT " . $this->phanBoDichVuSelectColumns('pbd') . ", 
                ncc.ten_don_vi
                FROM phan_bo_dich_vu pbd
                LEFT JOIN nha_cung_cap ncc ON pbd.nha_cung_cap_id = ncc.id_nha_cung_cap
                                WHERE pbd.lich_khoi_hanh_id = ? AND pbd.loai_dich_vu = ?
                                    AND " . $this->notDeletedClause('pbd');
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId, $loaiDichVu]);
        return $stmt->fetchAll();
    }

    // Thêm phân bổ dịch vụ
    public function insert(array $data): string|false {
        $sql = "INSERT INTO phan_bo_dich_vu 
                (lich_khoi_hanh_id, nha_cung_cap_id, loai_dich_vu, ten_dich_vu, 
                 so_luong, don_vi, ngay_bat_dau, ngay_ket_thuc, gio_bat_dau, gio_ket_thuc,
                 dia_diem, gia_tien, ghi_chu, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['lich_khoi_hanh_id'] ?? 0,
            $data['nha_cung_cap_id'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['ten_dich_vu'] ?? '',
            $data['so_luong'] ?? 1,
            $data['don_vi'] ?? null,
            !empty($data['ngay_bat_dau']) ? $data['ngay_bat_dau'] : null,
            !empty($data['ngay_ket_thuc']) ? $data['ngay_ket_thuc'] : null,
            !empty($data['gio_bat_dau']) ? $data['gio_bat_dau'] : null,
            !empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] : null,
            $data['dia_diem'] ?? null,
            $data['gia_tien'] ?? null,
            $data['ghi_chu'] ?? null,
            $data['trang_thai'] ?? 'ChoXacNhan'
        ]);
        
        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật phân bổ dịch vụ
    public function update(int $id, array $data): bool {
        $sql = "UPDATE phan_bo_dich_vu SET 
                nha_cung_cap_id = ?, loai_dich_vu = ?, ten_dich_vu = ?,
                so_luong = ?, don_vi = ?, ngay_bat_dau = ?, ngay_ket_thuc = ?,
                gio_bat_dau = ?, gio_ket_thuc = ?, dia_diem = ?,
                gia_tien = ?, ghi_chu = ?, trang_thai = ?,
                thoi_gian_xac_nhan = ?
                WHERE id = ?";
        $thoiGianXacNhan = isset($data['trang_thai']) && $data['trang_thai'] == 'DaXacNhan' 
            ? date('Y-m-d H:i:s') 
            : null;
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nha_cung_cap_id'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['ten_dich_vu'] ?? '',
            $data['so_luong'] ?? 1,
            $data['don_vi'] ?? null,
            !empty($data['ngay_bat_dau']) ? $data['ngay_bat_dau'] : null,
            !empty($data['ngay_ket_thuc']) ? $data['ngay_ket_thuc'] : null,
            !empty($data['gio_bat_dau']) ? $data['gio_bat_dau'] : null,
            !empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] : null,
            $data['dia_diem'] ?? null,
            $data['gia_tien'] ?? null,
            $data['ghi_chu'] ?? null,
            $data['trang_thai'] ?? 'ChoXacNhan',
            $thoiGianXacNhan,
            $id
        ]);
    }

    // Xóa phân bổ dịch vụ
    public function delete(int $id): bool {
        if ($this->hasColumn('phan_bo_dich_vu', 'deleted_at')) {
            $sql = "UPDATE phan_bo_dich_vu SET deleted_at = NOW(), trang_thai = 'Huy' WHERE id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM phan_bo_dich_vu WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }

    // Cập nhật trạng thái xác nhận
    public function updateTrangThai(int $id, string $trangThai): bool {
        $sql = "UPDATE phan_bo_dich_vu SET 
                trang_thai = ?,
                thoi_gian_xac_nhan = ?
                WHERE id = ?";
        $thoiGian = ($trangThai == 'DaXacNhan' || $trangThai == 'TuChoi') 
            ? date('Y-m-d H:i:s') 
            : null;
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$trangThai, $thoiGian, (int)$id]);
    }

    // Lấy tổng chi phí dịch vụ cho lịch khởi hành
    public function getTongChiPhi(int $lichKhoiHanhId): float {
        $sql = "SELECT COALESCE(SUM(gia_tien * so_luong), 0) as tong_chi_phi
                FROM phan_bo_dich_vu
                WHERE lich_khoi_hanh_id = ? AND trang_thai != 'Huy' AND " . $this->notDeletedClause();
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        $result = $stmt->fetch();
        return (float)($result['tong_chi_phi'] ?? 0);
    }
}

