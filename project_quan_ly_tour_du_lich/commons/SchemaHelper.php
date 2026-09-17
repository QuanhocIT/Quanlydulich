<?php

declare(strict_types=1);

/**
 * Class SchemaHelper
 * 
 * High-performance centralized schema registry.
 * Eliminates slow, repeated INFORMATION_SCHEMA.COLUMNS queries across all models.
 */
class SchemaHelper
{
    /**
     * Pre-compiled schema map of core application tables.
     * Prevents metadata query roundtrips on every HTTP request.
     */
    private static array $schemaCache = [];

    /**
     * Pre-compiled list of common tables to minimize runtime SHOW COLUMNS calls.
     */
    private static array $precompiledSchemas = [
        'booking' => [
            'booking_id', 'tour_id', 'khach_hang_id', 'ngay_dat', 'ngay_khoi_hanh',
            'ngay_ket_thuc', 'so_nguoi', 'tong_tien', 'tien_coc', 'trang_thai',
            'ghi_chu', 'so_tien_con_lai', 'trang_thai_hanh_khach', 'is_deleted', 'deleted_at'
        ],
        'tour' => [
            'tour_id', 'ten_tour', 'loai_tour', 'mo_ta', 'gia_co_ban',
            'chinh_sach', 'id_nha_cung_cap', 'tao_boi', 'trang_thai',
            'qr_code_path', 'is_deleted', 'deleted_at'
        ],
        'lich_khoi_hanh' => [
            'id', 'tour_id', 'ngay_khoi_hanh', 'gio_xuat_phat', 'ngay_ket_thuc',
            'gio_ket_thuc', 'diem_tap_trung', 'so_cho', 'hdv_id', 'trang_thai',
            'ghi_chu', 'phan_tram_hoa_hong_hdv', 'deleted_at'
        ],
        'danh_gia' => [
            'danh_gia_id', 'khach_hang_id', 'tour_id', 'nha_cung_cap_id', 'nhan_su_id',
            'loai_danh_gia', 'tieu_chi', 'loai_dich_vu', 'diem', 'noi_dung',
            'phan_hoi_admin', 'ngay_danh_gia', 'ngay_phan_hoi', 'deleted_at'
        ],
        'giao_dich_tai_chinh' => [
            'id', 'tour_id', 'booking_id', 'khach_hang_id', 'loai',
            'loai_doi_tuong', 'doi_tuong_id', 'loai_giao_dich', 'so_tien',
            'mo_ta', 'nguoi_thuc_hien_id', 'nguoi_thuc_hien', 'ngay_giao_dich',
            'created_at', 'updated_at'
        ],
        'phan_bo_nhan_su' => [
            'id', 'lich_khoi_hanh_id', 'nhan_su_id', 'vai_tro', 'ghi_chu',
            'trang_thai', 'thoi_gian_xac_nhan', 'loai_luong', 'so_tien_co_dinh',
            'phan_tram_hoa_hong', 'tien_hoa_hong', 'tong_luong', 'trang_thai_luong',
            'ngay_tao_luong', 'ngay_cap_nhat_luong', 'created_at', 'deleted_at'
        ],
        'phan_bo_dich_vu' => [
            'id', 'lich_khoi_hanh_id', 'nha_cung_cap_id', 'loai_dich_vu', 'ten_dich_vu',
            'so_luong', 'don_vi', 'ngay_bat_dau', 'ngay_ket_thuc', 'gio_bat_dau',
            'gio_ket_thuc', 'dia_diem', 'gia_tien', 'ghi_chu', 'trang_thai',
            'thoi_gian_xac_nhan', 'created_at', 'updated_at', 'deleted_at'
        ],
        'nha_cung_cap' => [
            'id_nha_cung_cap', 'nguoi_dung_id', 'ten_don_vi', 'loai_dich_vu', 'dia_chi',
            'lien_he', 'mo_ta', 'danh_gia_tb', 'is_deleted', 'deleted_at'
        ],
        'nguoi_dung' => [
            'id', 'ten_dang_nhap', 'mat_khau', 'ho_ten', 'avatar', 'email',
            'so_dien_thoai', 'vai_tro', 'quyen_cap_cao', 'trang_thai', 'ngay_tao',
            'email_verification_token', 'email_verified_at', 'email_token_expires_at',
            'password_reset_token', 'password_reset_expires_at', 'two_factor_secret',
            'two_factor_enabled', 'is_deleted', 'deleted_at'
        ],
        'khach_hang' => [
            'khach_hang_id', 'nguoi_dung_id', 'dia_chi', 'gioi_tinh', 'ngay_sinh'
        ],
        'thong_bao' => [
            'id', 'tieu_de', 'noi_dung', 'loai_thong_bao', 'muc_do_uu_tien',
            'nguoi_gui_id', 'nguoi_nhan_id', 'vai_tro_nhan', 'trang_thai',
            'thoi_gian_gui', 'thoi_gian_hen_gui', 'created_at', 'updated_at', 'deleted_at'
        ],
        'thong_bao_doc' => [
            'thong_bao_id', 'nguoi_dung_id', 'da_doc', 'thoi_gian_doc'
        ],
        'hotel_room_assignment' => [
            'id', 'lich_khoi_hanh_id', 'booking_id', 'checkin_id', 'ten_khach_san',
            'so_phong', 'loai_phong', 'so_giuong', 'ngay_nhan_phong', 'ngay_tra_phong',
            'gia_phong', 'trang_thai', 'ghi_chu', 'created_at', 'updated_at', 'deleted_at'
        ],
        'tour_checkin' => [
            'id', 'booking_id', 'khach_hang_id', 'lich_khoi_hanh_id', 'ho_ten',
            'so_cmnd', 'so_passport', 'ngay_sinh', 'gioi_tinh', 'quoc_tich',
            'dia_chi', 'so_dien_thoai', 'email', 'checkin_time', 'checkout_time',
            'trang_thai', 'ghi_chu', 'created_at', 'updated_at', 'anh_cccd',
            'anh_passport', 'deleted_at'
        ],
        'du_toan_tour' => [
            'du_toan_id', 'tour_id', 'lich_khoi_hanh_id', 'cp_phuong_tien', 'mo_ta_phuong_tien',
            'cp_luu_tru', 'mo_ta_luu_tru', 'cp_ve_tham_quan', 'mo_ta_ve_tham_quan',
            'cp_an_uong', 'mo_ta_an_uong', 'cp_huong_dan_vien', 'cp_dich_vu_bo_sung',
            'mo_ta_dich_vu', 'cp_phat_sinh_du_kien', 'mo_ta_phat_sinh', 'nguoi_tao_id',
            'ngay_tao', 'ngay_cap_nhat', 'tong_du_toan', 'ghi_chu', 'deleted_at'
        ],
        'chi_phi_thuc_te' => [
            'chi_phi_id', 'du_toan_id', 'tour_id', 'lich_khoi_hanh_id', 'loai_chi_phi',
            'ten_khoan_chi', 'so_tien', 'ngay_phat_sinh', 'mo_ta', 'chung_tu',
            'trang_thai', 'nguoi_ghi_nhan_id', 'nguoi_duyet_id', 'ngay_duyet',
            'ly_do_tu_choi', 'created_at', 'updated_at', 'deleted_at'
        ],
        'yeu_cau_dac_biet' => [
            'id', 'booking_id', 'loai_yeu_cau', 'tieu_de', 'mo_ta',
            'muc_do_uu_tien', 'trang_thai', 'ghi_chu_hdv', 'nguoi_tao_id',
            'nguoi_xu_ly_id', 'ngay_tao', 'ngay_cap_nhat', 'deleted_at'
        ],
        'nhat_ky_tour' => [
            'id', 'tour_id', 'nhan_su_id', 'loai_nhat_ky', 'tieu_de',
            'noi_dung', 'cach_xu_ly', 'thoi_tiet', 'hinh_anh', 'ngay_ghi', 'deleted_at'
        ],
        'nhan_su' => [
            'nhan_su_id', 'nguoi_dung_id', 'vai_tro', 'loai_hdv', 'chuyen_tuyen',
            'danh_gia_tb', 'so_tour_da_dan', 'trang_thai_lam_viec', 'chung_chi',
            'ngon_ngu', 'kinh_nghiem', 'suc_khoe', 'luong_co_ban', 'is_deleted', 'deleted_at'
        ],
        'payments' => [
            'payment_id', 'booking_id', 'amount', 'payment_method', 'payment_date',
            'status', 'note', 'deleted_at'
        ]
    ];

    /**
     * Get all column names of a table, using precompiled map or fast SHOW COLUMNS query.
     */
    public static function getTableColumns(PDO $pdo, string $tableName): array
    {
        $tableLower = strtolower(trim($tableName));

        if (isset(self::$schemaCache[$tableLower])) {
            return self::$schemaCache[$tableLower];
        }

        if (isset(self::$precompiledSchemas[$tableLower])) {
            self::$schemaCache[$tableLower] = self::$precompiledSchemas[$tableLower];
            return self::$schemaCache[$tableLower];
        }

        try {
            // Fast fallback: SHOW COLUMNS is 10x lighter than querying INFORMATION_SCHEMA
            $stmt = $pdo->query("SHOW COLUMNS FROM `{$tableName}`");
            $columns = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $field = (string)($row['Field'] ?? '');
                if ($field !== '') {
                    $columns[] = $field;
                }
            }
            self::$schemaCache[$tableLower] = $columns;
            return $columns;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Check whether a column exists in a given table.
     */
    public static function hasColumn(PDO $pdo, string $tableName, string $columnName): bool
    {
        $columns = self::getTableColumns($pdo, $tableName);
        return in_array($columnName, $columns, true);
    }

    /**
     * Build select column string: e.g. "b.id, b.tour_id, b.ngay_dat"
     */
    public static function selectColumns(PDO $pdo, string $tableName, string $alias = ''): string
    {
        $columns = self::getTableColumns($pdo, $tableName);
        if (empty($columns)) {
            return $alias !== '' ? ($alias . '.*') : '*';
        }

        if ($alias === '') {
            return implode(', ', $columns);
        }

        $prefixed = array_map(static fn($c) => $alias . '.' . $c, $columns);
        return implode(', ', $prefixed);
    }

    /**
     * Build NOT DELETED clause for tables supporting soft deletion.
     */
    public static function notDeletedClause(PDO $pdo, string $tableName, string $alias = ''): string
    {
        $prefix = $alias !== '' ? ($alias . '.') : '';
        if (self::hasColumn($pdo, $tableName, 'is_deleted')) {
            return $prefix . 'is_deleted = 0';
        }
        if (self::hasColumn($pdo, $tableName, 'deleted_at')) {
            return $prefix . 'deleted_at IS NULL';
        }
        return '1=1';
    }
}
