<?php
require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../commons/function.php';

$db = connectDB();

function explainQuery(PDO $db, string $name, string $sql, array $params = []) {
    echo "======================================================\n";
    echo "EXPLAIN: $name\n";
    echo "SQL: $sql\n";
    try {
        $stmt = $db->prepare("EXPLAIN " . $sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            echo sprintf("  Table: %-15s | Type: %-8s | Key: %-20s | Rows: %-5s | Extra: %s\n",
                $r['table'] ?? 'NULL',
                $r['type'] ?? 'NULL',
                $r['key'] ?? 'NULL',
                $r['rows'] ?? 'NULL',
                $r['Extra'] ?? ''
            );
        }
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}

// 1. Tour list query
explainQuery($db, 'Public Tours List', 
    "SELECT tour_id, ten_tour, loai_tour, mo_ta, gia_co_ban, trang_thai
     FROM tour
     WHERE is_deleted = 0 AND (trang_thai = 'HoatDong' OR trang_thai IS NULL)
     ORDER BY tour_id DESC LIMIT 20 OFFSET 0");

// 2. Schedule list query
explainQuery($db, 'Schedule List with Staff & Services',
    "SELECT lk.id, lk.ngay_khoi_hanh, lk.trang_thai, t.ten_tour,
            COUNT(DISTINCT pbn.id) AS so_nhan_su,
            COUNT(DISTINCT pbdv.id) AS so_dich_vu
     FROM lich_khoi_hanh lk
     LEFT JOIN tour t ON lk.tour_id = t.tour_id
     LEFT JOIN phan_bo_nhan_su pbn ON pbn.lich_khoi_hanh_id = lk.id AND pbn.deleted_at IS NULL
     LEFT JOIN phan_bo_dich_vu pbdv ON pbdv.lich_khoi_hanh_id = lk.id AND pbdv.deleted_at IS NULL
     WHERE lk.deleted_at IS NULL
     GROUP BY lk.id
     ORDER BY lk.ngay_khoi_hanh DESC LIMIT 20");

// 3. Booking list query
explainQuery($db, 'Admin Booking List',
    "SELECT b.booking_id, b.tour_id, b.khach_hang_id, b.trang_thai, b.tong_tien, b.tien_coc,
            t.ten_tour, nd.ho_ten, nd.email, nd.so_dien_thoai
     FROM booking b
     LEFT JOIN tour t ON b.tour_id = t.tour_id
     LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
     LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
     WHERE b.is_deleted = 0
     ORDER BY b.ngay_dat DESC, b.booking_id DESC
     LIMIT 20 OFFSET 0");

// 4. Tour Rating batch
explainQuery($db, 'Tour Rating Batch Map',
    "SELECT tour_id, AVG(diem) AS diem_tb, COUNT(*) AS so_danh_gia
     FROM danh_gia
     WHERE loai_danh_gia = 'Tour' AND tour_id IN (1, 2, 3, 4, 5)
     GROUP BY tour_id");

// 5. User lookup by email / login
explainQuery($db, 'User Login Lookup',
    "SELECT * FROM nguoi_dung WHERE email = ? AND is_deleted = 0 LIMIT 1",
    ['admin@example.com']);

// 6. Notifications unread count
explainQuery($db, 'Notifications Unread Count',
    "SELECT COUNT(*) as total
     FROM thong_bao tb
     LEFT JOIN nguoi_dung nd_gui ON tb.nguoi_gui_id = nd_gui.id
     LEFT JOIN thong_bao_doc tbd ON tb.id = tbd.thong_bao_id AND tbd.nguoi_dung_id = 1
     WHERE tb.nguoi_nhan_id = 1
       AND tb.vai_tro_nhan = 'KhachHang'
       AND tb.deleted_at IS NULL
       AND tb.nguoi_gui_id IS NOT NULL
       AND nd_gui.vai_tro = 'Admin'
       AND (tbd.da_doc IS NULL OR tbd.da_doc = 0)
       AND tb.trang_thai = 'DaGui'");

// 7. Finance summary
explainQuery($db, 'Finance Transactions Summary',
    "SELECT t.tour_id, t.ten_tour,
            COALESCE(SUM(CASE WHEN gd.loai = 'Thu' THEN gd.so_tien ELSE 0 END), 0) AS tong_thu,
            COALESCE(SUM(CASE WHEN gd.loai = 'Chi' THEN gd.so_tien ELSE 0 END), 0) AS tong_chi
     FROM tour t
     LEFT JOIN giao_dich_tai_chinh gd ON t.tour_id = gd.tour_id
     WHERE t.is_deleted = 0
     GROUP BY t.tour_id, t.ten_tour");
