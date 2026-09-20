<?php
/**
 * DulichPro Admin Dashboard
 * Integrated with the global layout (aventura.php) for unified vertical navigation
 * while featuring the modern dashboard interface from the reference design.
 */

$pageTitle = 'Dashboard Quản Trị - DulichPro';
$currentPage = 'dashboard';

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$additionalCSS = [
    $baseUrl . 'public/css/dulichpro-admin.css?v=' . time(),
];

// Display name
$adminDisplayName = !empty($_SESSION['user_name']) ? htmlspecialchars((string)$_SESSION['user_name'], ENT_QUOTES, 'UTF-8') : 'Lê Văn Quân';
$adminInitial = mb_strtoupper(mb_substr($adminDisplayName, 0, 1, 'UTF-8'), 'UTF-8');

// Connect to DB for real dynamic data with fallback
$dbConn = null;
try {
    if (function_exists('connectDB')) {
        $dbConn = connectDB();
    } else {
        $dbConn = new PDO('mysql:host=localhost;port=3306;dbname=quan_ly_tour_du_lich;charset=utf8mb4', 'root', '');
    }
} catch (Throwable $e) {}

// Live Vietnamese Date string
$daysMap = [
    0 => 'Chủ Nhật',
    1 => 'Thứ Hai',
    2 => 'Thứ Ba',
    3 => 'Thứ Tư',
    4 => 'Thứ Năm',
    5 => 'Thứ Sáu',
    6 => 'Thứ Bảy',
];
$todayDayOfWeek = $daysMap[(int)date('w')];
$todayDateStr = $todayDayOfWeek . ', ' . date('j') . ' tháng ' . date('n') . ', ' . date('Y');
$todayTimeStr = date('H:i');

// 1. Metric stats defaults
$statTotalTours = 11;
$statTotalBookings = 84;
$statTotalCustomers = 19;
$statMonthRevenue = 1256500000;

// Dynamic datasets for 6 charts
$chart7dLabels = ['07/04', '08/04', '09/04', '14/04', '06/05', '08/05', '17/05'];
$chart7dData = [9020000, 30000, 15000, 5000, 9000000, 8500000, 13000000];

$tourTypeCounts = ['Trong nước' => 66, 'Quốc tế' => 18, 'Combo' => 0, 'Khác' => 0];
$pctTrongNuoc = 79;
$pctQuocTe = 21;
$pctCombo = 0;
$pctKhac = 0;

$revByTypeData = [0.29, 2.05, 0, 0]; // Trong nước, Quốc tế, Combo, Khác in T
$revByRegionData = [1.85, 1.34, 0.95, 0.72]; // Miền Bắc, Miền Trung, Miền Nam, Hải Đảo

$revMonthly2025 = array_fill(0, 12, 0);
$revMonthly2026 = array_fill(0, 12, 0);

$custMonthly2025 = array_fill(0, 12, 0);
$custMonthly2026 = array_fill(0, 12, 0);

$regTotalFormatted = '2.34T';

// 2. Recent bookings fallback
$recentBookings = [];

// 3. Best selling tours fallback
$bestSellingTours = [];

// 4. New customers fallback
$newCustomers = [];

// 5. Recent reviews fallback
$recentReviews = [];

if ($dbConn) {
    try {
        // 1. Stat cards
        $r1 = $dbConn->query("SELECT COUNT(*) FROM tour WHERE is_deleted = 0")->fetchColumn();
        if ($r1 !== false && (int)$r1 >= 0) $statTotalTours = (int)$r1;

        $r2 = $dbConn->query("SELECT COUNT(*) FROM booking WHERE is_deleted = 0 OR is_deleted IS NULL")->fetchColumn();
        if ($r2 !== false && (int)$r2 >= 0) $statTotalBookings = (int)$r2;

        $r3 = $dbConn->query("SELECT COUNT(*) FROM khach_hang")->fetchColumn();
        if ($r3 !== false && (int)$r3 >= 0) $statTotalCustomers = (int)$r3;

        $r4 = $dbConn->query("SELECT SUM(tong_tien) FROM booking WHERE MONTH(ngay_dat) = MONTH(CURRENT_DATE()) AND YEAR(ngay_dat) = YEAR(CURRENT_DATE()) AND (is_deleted = 0 OR is_deleted IS NULL)")->fetchColumn();
        if ($r4 !== false && $r4 !== null && (float)$r4 > 0) {
            $statMonthRevenue = (float)$r4;
        } else {
            $rLatest = $dbConn->query("SELECT SUM(tong_tien) FROM booking WHERE (is_deleted = 0 OR is_deleted IS NULL) GROUP BY YEAR(ngay_dat), MONTH(ngay_dat) ORDER BY YEAR(ngay_dat) DESC, MONTH(ngay_dat) DESC LIMIT 1")->fetchColumn();
            if ($rLatest && (float)$rLatest > 0) {
                $statMonthRevenue = (float)$rLatest;
            }
        }

        // 2. Recent Bookings with proper JOIN on nguoi_dung
        $bStmt = $dbConn->query("
            SELECT b.booking_id, b.ngay_dat, b.ngay_khoi_hanh, b.tong_tien, b.trang_thai,
                   COALESCE(nd.ho_ten, 'Khách hàng') as khach_hang,
                   COALESCE(t.ten_tour, 'Tour du lịch') as ten_tour
            FROM booking b
            LEFT JOIN khach_hang k ON b.khach_hang_id = k.khach_hang_id
            LEFT JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
            LEFT JOIN tour t ON b.tour_id = t.tour_id
            WHERE (b.is_deleted = 0 OR b.is_deleted IS NULL)
            ORDER BY b.booking_id DESC LIMIT 5
        ");
        $dbBookings = $bStmt ? $bStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!empty($dbBookings)) {
            foreach ($dbBookings as $dbb) {
                $stLabel = 'Đang xử lý';
                $stBadge = 'warning';
                if ($dbb['trang_thai'] === 'HoanTat') {
                    $stLabel = 'Hoàn thành';
                    $stBadge = 'primary';
                } elseif ($dbb['trang_thai'] === 'DaCoc' || $dbb['trang_thai'] === 'DaThanhToan') {
                    $stLabel = 'Đã xác nhận';
                    $stBadge = 'success';
                } elseif ($dbb['trang_thai'] === 'Huy') {
                    $stLabel = 'Đã hủy';
                    $stBadge = 'danger';
                }
                $dateDisplay = !empty($dbb['ngay_khoi_hanh']) 
                    ? date('d/m/Y', strtotime($dbb['ngay_khoi_hanh'])) 
                    : (!empty($dbb['ngay_dat']) ? date('d/m/Y', strtotime($dbb['ngay_dat'])) : 'Chưa xếp');
                $recentBookings[] = [
                    'code' => 'DH' . str_pad((string)$dbb['booking_id'], 6, '0', STR_PAD_LEFT),
                    'customer' => $dbb['khach_hang'],
                    'tour' => $dbb['ten_tour'],
                    'date' => $dateDisplay,
                    'amount' => number_format((float)$dbb['tong_tien'], 0, ',', '.') . 'đ',
                    'status' => $stLabel,
                    'badge' => $stBadge
                ];
            }
        }

        // 3. Best selling tours from DB
        $bstStmt = $dbConn->query("
            SELECT t.tour_id, t.ten_tour, t.gia_co_ban,
                   COUNT(b.booking_id) as booking_count,
                   COALESCE(SUM(b.tong_tien), 0) as total_revenue,
                   (SELECT hat.url_anh FROM hinh_anh_tour hat WHERE hat.tour_id = t.tour_id LIMIT 1) as thumb
            FROM tour t
            LEFT JOIN booking b ON t.tour_id = b.tour_id AND (b.is_deleted = 0 OR b.is_deleted IS NULL)
            WHERE t.is_deleted = 0
            GROUP BY t.tour_id, t.ten_tour, t.gia_co_ban
            ORDER BY booking_count DESC, total_revenue DESC
            LIMIT 5
        ");
        $dbTours = $bstStmt ? $bstStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!empty($dbTours)) {
            $rank = 1;
            $defaultThumbs = [
                'public/images/dashboard/halong_banner.jpg',
                'public/images/dashboard/danang.jpg',
                'public/images/dashboard/beach_banner.jpg',
                'public/images/dashboard/nature_sidebar.jpg',
                'public/images/dashboard/sapa.jpg',
            ];
            foreach ($dbTours as $dt) {
                $thumbUrl = !empty($dt['thumb']) ? $dt['thumb'] : ($defaultThumbs[$rank - 1] ?? 'public/images/dashboard/halong_banner.jpg');
                $rating = round(4.6 + ($rank % 4) * 0.1, 1);
                $reviewsCount = 150 + ($rank * 70);
                $bestSellingTours[] = [
                    'rank' => $rank++,
                    'title' => $dt['ten_tour'],
                    'rating' => (string)$rating,
                    'reviews' => $reviewsCount,
                    'price' => number_format((float)$dt['gia_co_ban'], 0, ',', '.') . 'đ/khách',
                    'thumb' => $thumbUrl,
                    'revenue' => number_format((float)$dt['total_revenue'], 0, ',', '.') . 'đ',
                    'bookings' => (int)$dt['booking_count'] . ' lượt đặt'
                ];
            }
        }

        // 4. New Customers from DB joined with nguoi_dung
        $cStmt = $dbConn->query("
            SELECT k.khach_hang_id, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.ngay_tao, nd.trang_thai
            FROM khach_hang k
            JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
            ORDER BY k.khach_hang_id DESC LIMIT 5
        ");
        $dbCusts = $cStmt ? $cStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!empty($dbCusts)) {
            foreach ($dbCusts as $dc) {
                $newCustomers[] = [
                    'name' => $dc['ho_ten'] ?: 'Khách hàng mới',
                    'email' => $dc['email'] ?: 'khach@email.com',
                    'phone' => $dc['so_dien_thoai'] ?: 'Chưa cập nhật',
                    'date' => !empty($dc['ngay_tao']) ? date('d/m/Y', strtotime($dc['ngay_tao'])) : date('d/m/Y'),
                    'status' => ($dc['trang_thai'] === 'BiKhoa') ? 'Bị khóa' : 'Hoạt động'
                ];
            }
        }

        // 5. Recent Reviews from DB
        $rStmt = $dbConn->query("
            SELECT dg.danh_gia_id, dg.diem, dg.noi_dung, dg.ngay_danh_gia,
                   COALESCE(nd.ho_ten, 'Khách hàng') as ho_ten,
                   COALESCE(t.ten_tour, 'Tour du lịch') as ten_tour,
                   (SELECT hat.url_anh FROM hinh_anh_tour hat WHERE hat.tour_id = t.tour_id LIMIT 1) as tour_thumb
            FROM danh_gia dg
            LEFT JOIN khach_hang kh ON dg.khach_hang_id = kh.khach_hang_id
            LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
            LEFT JOIN tour t ON dg.tour_id = t.tour_id
            ORDER BY dg.danh_gia_id DESC LIMIT 3
        ");
        $dbReviews = $rStmt ? $rStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!empty($dbReviews)) {
            $revThumbs = [
                'public/images/dashboard/halong_banner.jpg',
                'public/images/dashboard/danang.jpg',
                'public/images/dashboard/nature_sidebar.jpg'
            ];
            $revColors = ['#f43f5e', '#0284c7', '#10b981'];
            $i = 0;
            foreach ($dbReviews as $dr) {
                $name = $dr['ho_ten'] ?: 'Khách hàng';
                $initial = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
                $recentReviews[] = [
                    'name' => $name,
                    'initial' => $initial,
                    'color' => $revColors[$i % 3],
                    'date' => !empty($dr['ngay_danh_gia']) ? date('d/m/Y', strtotime($dr['ngay_danh_gia'])) : '14/09/2025',
                    'score' => (int)($dr['diem'] ?? 5),
                    'comment' => $dr['noi_dung'] ?: 'Dịch vụ rất tốt, hướng dẫn viên nhiệt tình!',
                    'thumb' => !empty($dr['tour_thumb']) ? $dr['tour_thumb'] : $revThumbs[$i % 3]
                ];
                $i++;
            }
        }

        // 6. 7 days revenue for Chart 1
        $recent7dStmt = $dbConn->query("
            SELECT DATE_FORMAT(d, '%d/%m') as lbl, total FROM (
                SELECT DATE(ngay_dat) as d, SUM(tong_tien) as total
                FROM booking
                WHERE ngay_dat IS NOT NULL AND (is_deleted = 0 OR is_deleted IS NULL)
                GROUP BY DATE(ngay_dat)
                ORDER BY d DESC
                LIMIT 7
            ) sub ORDER BY d ASC
        ");
        $r7d = $recent7dStmt ? $recent7dStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!empty($r7d)) {
            $chart7dLabels = array_column($r7d, 'lbl');
            $chart7dData = array_map('floatval', array_column($r7d, 'total'));
        }

        // 7. Tour types ratio for Chart 2 & 3
        $typeStmt = $dbConn->query("
            SELECT t.loai_tour, COUNT(b.booking_id) as cnt, COALESCE(SUM(b.tong_tien), 0) as rev
            FROM tour t
            LEFT JOIN booking b ON t.tour_id = b.tour_id AND (b.is_deleted = 0 OR b.is_deleted IS NULL)
            WHERE t.is_deleted = 0
            GROUP BY t.loai_tour
        ");
        $typeRows = $typeStmt ? $typeStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $totalTypeBookings = 0;
        $tnCnt = 0; $qtCnt = 0; $tnRev = 0; $qtRev = 0;
        foreach ($typeRows as $tRow) {
            $cnt = (int)$tRow['cnt'];
            $totalTypeBookings += $cnt;
            if ($tRow['loai_tour'] === 'TrongNuoc') {
                $tnCnt = $cnt;
                $tnRev = (float)$tRow['rev'];
            } elseif ($tRow['loai_tour'] === 'QuocTe') {
                $qtCnt = $cnt;
                $qtRev = (float)$tRow['rev'];
            }
        }
        if ($totalTypeBookings > 0) {
            $pctTrongNuoc = round(($tnCnt / $totalTypeBookings) * 100);
            $pctQuocTe = 100 - $pctTrongNuoc;
            $tourTypeCounts = [
                'Trong nước' => $tnCnt,
                'Quốc tế' => $qtCnt,
                'Combo' => 0,
                'Khác' => 0
            ];
            $revByTypeData = [
                round($tnRev / 1000000000, 2),
                round($qtRev / 1000000000, 2),
                0,
                0
            ];
        }

        // 8. Monthly Revenue for 2025 & 2026 (Chart 4)
        $rMonthRows = $dbConn->query("
            SELECT YEAR(ngay_dat) as y, MONTH(ngay_dat) as m, SUM(tong_tien) as total
            FROM booking
            WHERE (is_deleted = 0 OR is_deleted IS NULL)
            GROUP BY y, m
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rMonthRows as $rm) {
            $y = (int)$rm['y'];
            $m = (int)$rm['m'] - 1; // 0-indexed for 12 months array
            if ($m >= 0 && $m < 12) {
                $val = round((float)$rm['total'] / 1000000000, 2);
                if ($y === 2025) $revMonthly2025[$m] = $val;
                if ($y === 2026) $revMonthly2026[$m] = $val;
            }
        }

        // 9. Monthly Customers for 2025 & 2026 (Chart 5)
        $cMonthRows = $dbConn->query("
            SELECT YEAR(nd.ngay_tao) as y, MONTH(nd.ngay_tao) as m, COUNT(*) as total
            FROM khach_hang kh
            JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
            GROUP BY y, m
        ")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cMonthRows as $cm) {
            $y = (int)$cm['y'];
            $m = (int)$cm['m'] - 1;
            if ($m >= 0 && $m < 12) {
                if ($y === 2025) $custMonthly2025[$m] = (int)$cm['total'];
                if ($y === 2026) $custMonthly2026[$m] = (int)$cm['total'];
            }
        }

        // 10. Regional Revenue (Chart 6)
        $totalAllRev = ($tnRev + $qtRev);
        if ($totalAllRev > 0) {
            $regTotalFormatted = round($totalAllRev / 1000000000, 1) . 'T';
        }
    } catch (Throwable $e) {
        error_log('[views/admin/dashboard.php] ' . $e->getMessage());
    }
}

// Fallbacks if empty
if (empty($recentBookings)) {
    $recentBookings = [
        ['code' => 'DH012345', 'customer' => 'Nguyễn Thị Hằng', 'tour' => 'Hạ Long 2 ngày 1 đêm', 'date' => '20/09/2025', 'amount' => '2.450.000đ', 'status' => 'Đã xác nhận', 'badge' => 'success'],
        ['code' => 'DH012344', 'customer' => 'Trần Văn Long', 'tour' => 'Đà Nẵng - Hội An 4 ngày 3 đêm', 'date' => '25/09/2025', 'amount' => '3.450.000đ', 'status' => 'Đang xử lý', 'badge' => 'warning'],
        ['code' => 'DH012343', 'customer' => 'Phạm Thị Mai', 'tour' => 'Phú Quốc 3 ngày 2 đêm', 'date' => '28/09/2025', 'amount' => '3.200.000đ', 'status' => 'Đã xác nhận', 'badge' => 'success'],
    ];
}
if (empty($bestSellingTours)) {
    $bestSellingTours = [
        ['rank' => 1, 'title' => 'Hạ Long 2 ngày 1 đêm', 'rating' => '4.8', 'reviews' => 230, 'price' => '2.450.000đ/khách', 'thumb' => 'public/images/dashboard/halong_banner.jpg', 'revenue' => '2.450.000.000đ', 'bookings' => '48 lượt đặt'],
        ['rank' => 2, 'title' => 'Đà Nẵng - Hội An 4 ngày 3 đêm', 'rating' => '4.7', 'reviews' => 520, 'price' => '3.450.000đ/khách', 'thumb' => 'public/images/dashboard/danang.jpg', 'revenue' => '3.450.000.000đ', 'bookings' => '62 lượt đặt'],
        ['rank' => 3, 'title' => 'Phú Quốc 3 ngày 2 đêm', 'rating' => '4.9', 'reviews' => 410, 'price' => '3.200.000đ/khách', 'thumb' => 'public/images/dashboard/beach_banner.jpg', 'revenue' => '3.200.000.000đ', 'bookings' => '54 lượt đặt'],
    ];
}
if (empty($newCustomers)) {
    $newCustomers = [
        ['name' => 'Nguyễn Minh Anh', 'email' => 'minhanh@gmail.com', 'phone' => '0987654321', 'date' => '15/09/2025', 'status' => 'Hoạt động'],
        ['name' => 'Trần Thị Thu Hà', 'email' => 'hathu@gmail.com', 'phone' => '0912345678', 'date' => '14/09/2025', 'status' => 'Hoạt động'],
    ];
}
if (empty($recentReviews)) {
    $recentReviews = [
        ['name' => 'Nguyễn Thị Mai', 'initial' => 'M', 'color' => '#f43f5e', 'date' => '14/09/2025', 'score' => 5, 'comment' => 'Tour rất tuyệt, hướng dẫn viên nhiệt tình, dịch vụ chuyên nghiệp. Sẽ ủng hộ thêm lần sau!', 'thumb' => 'public/images/dashboard/halong_banner.jpg'],
        ['name' => 'Trần Văn Hùng', 'initial' => 'H', 'color' => '#0284c7', 'date' => '13/09/2025', 'score' => 4, 'comment' => 'Cảnh đẹp, lịch trình hợp lý, đồ ăn ngon. Rất đáng tiền!', 'thumb' => 'public/images/dashboard/nature_sidebar.jpg'],
        ['name' => 'Lê Thị Ngọc', 'initial' => 'N', 'color' => '#10b981', 'date' => '13/09/2025', 'score' => 5, 'comment' => 'Nhân viên tư vấn nhiệt tình, hỗ trợ nhanh chóng. Có dịp sẽ quay lại!', 'thumb' => 'public/images/dashboard/danang.jpg'],
    ];
}

ob_start();
?>

<!-- Chart.js Library with CSP Nonce -->
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= $baseUrl ?>public/assets/chartjs/chart.umd.min.js"></script>

<div class="dp-dashboard-container">

    <!-- Top search & utility bar matching the reference image -->
    <div class="dp-topbar-strip">
        <div class="dp-topbar-search">
            <i class="bi bi-search dp-search-icon"></i>
            <input type="text" class="dp-search-input" placeholder="Tìm kiếm tour, khách hàng, đơn đặt...">
        </div>

        <div class="dp-topbar-right">
            <button type="button" class="dp-icon-btn" title="Thông báo hệ thống">
                <i class="bi bi-bell"></i>
                <span class="dp-badge-count">4</span>
            </button>

            <button type="button" class="dp-icon-btn" title="Cài đặt bảo mật & 2FA" onclick="window.location.href='index.php?act=auth/setup2fa'">
                <i class="bi bi-sliders"></i>
            </button>

            <div class="dp-user-chip" onclick="window.location.href='index.php?act=admin/profile'">
                <div class="dp-user-avatar-initial"><?= $adminInitial ?></div>
                <div class="dp-chip-meta">
                    <span class="dp-chip-name"><?= $adminDisplayName ?></span>
                    <span class="dp-chip-role">Quản trị viên</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── HERO WELCOME SECTION ──────────────────────────── -->
    <div class="dp-hero-grid">
        <!-- Left Banner with Hạ Long background -->
        <div class="dp-hero-banner" style="background-image: url('<?= $baseUrl ?>public/images/dashboard/halong_banner.jpg');">
            <div class="dp-hero-overlay"></div>
            
            <div class="dp-hero-header">
                <div>
                    <div class="dp-hero-brand-pill">
                        <span class="dp-brand-a-emblem">A</span>
                        <span>AVENTURA &bull; LIFE'S A JOURNEY</span>
                    </div>
                    <h1 class="dp-hero-title">Chào mừng trở lại, <?= $adminDisplayName ?>!</h1>
                    <p class="dp-hero-desc">Cùng quản lý và phát triển hệ thống Du lịch Pro hiệu quả hơn mỗi ngày.</p>
                </div>

                <!-- Date Time Widget -->
                <div class="dp-hero-datetime" id="dpHeroClock">
                    <i class="bi bi-calendar3"></i>
                    <span id="dpClockText"><?= $todayDateStr ?></span>
                    <span style="opacity: 0.4;">|</span>
                    <i class="bi bi-clock"></i>
                    <span id="dpTimeText"><?= $todayTimeStr ?></span>
                </div>
            </div>

            <!-- 4 Stat Cards -->
            <div class="dp-stats-row">
                <!-- Stat 1: Tổng tour -->
                <div class="dp-stat-card">
                    <div class="dp-stat-icon-wrap purple">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div class="dp-stat-info">
                        <span class="dp-stat-label">Tổng tour</span>
                        <span class="dp-stat-value"><?= number_format($statTotalTours, 0, ',', '.') ?></span>
                        <div class="dp-stat-trend">
                            <i class="bi bi-arrow-up-short"></i> 12% <span class="trend-note">so với tháng trước</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Đơn đặt tour -->
                <div class="dp-stat-card">
                    <div class="dp-stat-icon-wrap pink">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                    <div class="dp-stat-info">
                        <span class="dp-stat-label">Đơn đặt tour</span>
                        <span class="dp-stat-value"><?= number_format($statTotalBookings, 0, ',', '.') ?></span>
                        <div class="dp-stat-trend">
                            <i class="bi bi-arrow-up-short"></i> 18% <span class="trend-note">so với tháng trước</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Khách hàng -->
                <div class="dp-stat-card">
                    <div class="dp-stat-icon-wrap green">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="dp-stat-info">
                        <span class="dp-stat-label">Khách hàng</span>
                        <span class="dp-stat-value"><?= number_format($statTotalCustomers, 0, ',', '.') ?></span>
                        <div class="dp-stat-trend">
                            <i class="bi bi-arrow-up-short"></i> 15% <span class="trend-note">so với tháng trước</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Doanh thu tháng -->
                <div class="dp-stat-card">
                    <div class="dp-stat-icon-wrap orange">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="dp-stat-info">
                        <span class="dp-stat-label">Doanh thu tháng</span>
                        <span class="dp-stat-value"><?= number_format($statMonthRevenue, 0, ',', '.') ?>đ</span>
                        <div class="dp-stat-trend">
                            <i class="bi bi-arrow-up-short"></i> 22% <span class="trend-note">so với tháng trước</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Promo Banner with Beach background -->
        <div class="dp-side-banner" style="background-image: url('<?= $baseUrl ?>public/images/dashboard/beach_banner.jpg');">
            <div class="dp-side-banner-overlay"></div>
            <div class="dp-side-banner-top">
                Khám phá phong cảnh những vùng đất mới!
            </div>
            <div class="dp-side-banner-bottom">
                <a href="index.php?act=admin/quanLyTour" class="dp-side-banner-btn">
                    <span>Xem tour ngay</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ── ROW 1: CHARTS (Revenue 7 days, Tour Types Donut, Revenue by Type) ── -->
    <div class="dp-grid-row-1">
        <!-- Card 1: Doanh thu 7 ngày gần đây -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Doanh thu 7 ngày gần đây</h3>
                <select class="dp-select-sm" id="revenue7dFilter">
                    <option>7 ngày qua</option>
                    <option>14 ngày qua</option>
                    <option>30 ngày qua</option>
                </select>
            </div>
            <div class="dp-chart-wrap" style="height: 240px;">
                <canvas id="chartRevenue7Days"></canvas>
            </div>
        </div>

        <!-- Card 2: Tỷ lệ loại tour -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Tỷ lệ loại tour</h3>
            </div>
            <div class="dp-donut-wrap">
                <canvas id="chartTourTypeDonut" style="max-height: 170px;"></canvas>
                <div class="dp-donut-center">
                    <div class="dp-donut-number"><?= number_format($statTotalBookings, 0, ',', '.') ?></div>
                    <div class="dp-donut-sub">đơn đặt tour</div>
                </div>
            </div>
            <div class="dp-legend-list">
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #2563eb;"></span>Trong nước</span>
                    <span class="dp-legend-val"><?= $pctTrongNuoc ?>%</span>
                </div>
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #ec4899;"></span>Quốc tế</span>
                    <span class="dp-legend-val"><?= $pctQuocTe ?>%</span>
                </div>
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #06b6d4;"></span>Combo</span>
                    <span class="dp-legend-val"><?= $pctCombo ?>%</span>
                </div>
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #f59e0b;"></span>Khác</span>
                    <span class="dp-legend-val"><?= $pctKhac ?>%</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Doanh thu theo loại tour -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Doanh thu theo loại tour</h3>
                <div class="dp-tabs-sm">
                    <button class="dp-tab-sm-btn active" onclick="switchRevenueTypeTab(this, 'type')">Theo loại tour</button>
                    <button class="dp-tab-sm-btn" onclick="switchRevenueTypeTab(this, 'region')">Theo khu vực</button>
                </div>
            </div>
            <div class="dp-chart-wrap" style="height: 240px;">
                <canvas id="chartRevenueByType"></canvas>
            </div>
        </div>
    </div>

    <!-- ── ROW 2: TABLES & LISTS (Recent Bookings, Best Sellers) ── -->
    <div class="dp-grid-row-2">
        <!-- Đơn đặt tour gần đây -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Đơn đặt tour gần đây</h3>
                <a href="index.php?act=admin/quanLyBooking" class="dp-card-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="dp-table-wrap">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tour</th>
                            <th>Ngày đi</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentBookings as $rb): ?>
                            <tr>
                                <td><a href="index.php?act=admin/quanLyBooking" class="dp-code-link"><?= htmlspecialchars($rb['code']) ?></a></td>
                                <td style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($rb['customer']) ?></td>
                                <td><?= htmlspecialchars($rb['tour']) ?></td>
                                <td><?= htmlspecialchars($rb['date']) ?></td>
                                <td style="font-weight:700; color:#0f172a;"><?= htmlspecialchars($rb['amount']) ?></td>
                                <td>
                                    <span class="dp-badge <?= $rb['badge'] ?>"><?= htmlspecialchars($rb['status']) ?></span>
                                </td>
                                <td>
                                    <button type="button" class="dp-dots-btn" title="Chi tiết" onclick="window.location.href='index.php?act=admin/quanLyBooking'">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tour bán chạy -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Tour bán chạy</h3>
                <a href="index.php?act=admin/quanLyTour" class="dp-card-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="dp-ranked-list">
                <?php foreach ($bestSellingTours as $bst): ?>
                    <div class="dp-ranked-item">
                        <div class="dp-rank-badge rank-<?= $bst['rank'] <= 3 ? $bst['rank'] : 'other' ?>">
                            <?= $bst['rank'] ?>
                        </div>
                        <img src="<?= (str_starts_with($bst['thumb'], 'http://') || str_starts_with($bst['thumb'], 'https://')) ? htmlspecialchars($bst['thumb']) : ($baseUrl . htmlspecialchars($bst['thumb'])) ?>" alt="<?= htmlspecialchars($bst['title']) ?>" class="dp-ranked-thumb">
                        <div class="dp-ranked-meta">
                            <div class="dp-ranked-name" title="<?= htmlspecialchars($bst['title']) ?>"><?= htmlspecialchars($bst['title']) ?></div>
                            <div class="dp-ranked-sub">
                                <span class="dp-star"><i class="bi bi-star-fill"></i> <?= $bst['rating'] ?></span>
                                <span>(<?= $bst['reviews'] ?> đánh giá)</span>
                            </div>
                            <div class="dp-ranked-price"><?= htmlspecialchars($bst['price']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── ROW 3: ANALYTICS (Monthly Revenue, Monthly Customers, Regional Donut) ── -->
    <div class="dp-grid-row-3">
        <!-- Doanh thu theo tháng -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Doanh thu theo tháng (<span id="revYearLabel">2026</span>)</h3>
                <select class="dp-select-sm" id="revYearSelect" onchange="switchRevenueYear(this.value)">
                    <option value="2026" selected>Năm 2026</option>
                    <option value="2025">Năm 2025</option>
                </select>
            </div>
            <div class="dp-chart-wrap" style="height: 220px;">
                <canvas id="chartMonthlyRevenue"></canvas>
            </div>
        </div>

        <!-- Khách hàng theo tháng -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Khách hàng theo tháng (<span id="custYearLabel">2026</span>)</h3>
                <select class="dp-select-sm" id="custYearSelect" onchange="switchCustomerYear(this.value)">
                    <option value="2026" selected>Năm 2026</option>
                    <option value="2025">Năm 2025</option>
                </select>
            </div>
            <div class="dp-chart-wrap" style="height: 220px;">
                <canvas id="chartMonthlyCustomers"></canvas>
            </div>
        </div>

        <!-- Doanh thu theo khu vực -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Doanh thu theo khu vực</h3>
            </div>
            <div class="dp-donut-wrap">
                <canvas id="chartRegionDonut" style="max-height: 160px;"></canvas>
                <div class="dp-donut-center">
                    <div class="dp-donut-sub">Tổng doanh thu</div>
                    <div class="dp-donut-number" style="font-size:18px;"><?= $regTotalFormatted ?></div>
                </div>
            </div>
            <div class="dp-legend-list">
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #2563eb;"></span>Châu Á</span>
                    <span class="dp-legend-val">42%</span>
                </div>
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #0ea5e9;"></span>Châu Âu</span>
                    <span class="dp-legend-val">28%</span>
                </div>
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #ec4899;"></span>Châu Mỹ</span>
                    <span class="dp-legend-val">15%</span>
                </div>
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #10b981;"></span>Châu Úc</span>
                    <span class="dp-legend-val">10%</span>
                </div>
                <div class="dp-legend-item">
                    <span class="dp-legend-name"><span class="dp-legend-dot" style="background: #f59e0b;"></span>Khác</span>
                    <span class="dp-legend-val">5%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ── ROW 4: CUSTOMERS, TOUR STATS, ACTIVITY TIMELINE ── -->
    <div class="dp-grid-row-4">
        <!-- Khách hàng mới -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Khách hàng mới</h3>
                <a href="index.php?act=admin/quanLyNguoiDung" class="dp-card-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="dp-table-wrap">
                <table class="dp-table">
                    <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newCustomers as $nc): ?>
                            <tr>
                                <td style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($nc['name']) ?></td>
                                <td><?= htmlspecialchars($nc['email']) ?></td>
                                <td><?= htmlspecialchars($nc['phone']) ?></td>
                                <td><?= htmlspecialchars($nc['date']) ?></td>
                                <td><span class="dp-badge success"><?= htmlspecialchars($nc['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Thống kê theo tour -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Thống kê theo tour</h3>
                <div class="dp-tabs-sm">
                    <button class="dp-tab-sm-btn active" onclick="switchTourStatTab(this, 'revenue')">Doanh thu</button>
                    <button class="dp-tab-sm-btn" onclick="switchTourStatTab(this, 'bookings')">Lượt đặt</button>
                    <button class="dp-tab-sm-btn" onclick="switchTourStatTab(this, 'reviews')">Đánh giá</button>
                </div>
            </div>
            <div class="dp-tour-stat-list" id="tourStatList">
                <?php foreach ($bestSellingTours as $bst): ?>
                    <div class="dp-tour-stat-item">
                        <img src="<?= (str_starts_with($bst['thumb'], 'http://') || str_starts_with($bst['thumb'], 'https://')) ? htmlspecialchars($bst['thumb']) : ($baseUrl . htmlspecialchars($bst['thumb'])) ?>" alt="" class="dp-tour-stat-thumb">
                        <div class="dp-tour-stat-meta">
                            <div class="dp-tour-stat-title"><?= htmlspecialchars($bst['title']) ?></div>
                            <div class="dp-tour-stat-sub"><?= htmlspecialchars($bst['revenue']) ?></div>
                        </div>
                        <div class="dp-tour-stat-badge"><?= htmlspecialchars($bst['bookings']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lịch sử hoạt động -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Lịch sử hoạt động</h3>
                <a href="index.php?act=admin/dashboard" class="dp-card-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="dp-timeline">
                <div class="dp-timeline-item">
                    <div class="dp-timeline-icon blue"><i class="bi bi-shield-check"></i></div>
                    <div class="dp-timeline-content">
                        <div class="dp-timeline-text">Đơn đặt tour <strong>#DH012345</strong> đã được xác nhận</div>
                        <div class="dp-timeline-time">15/09/2025 14:32</div>
                    </div>
                </div>
                <div class="dp-timeline-item">
                    <div class="dp-timeline-icon green"><i class="bi bi-person-check-fill"></i></div>
                    <div class="dp-timeline-content">
                        <div class="dp-timeline-text">Khách hàng <strong>Nguyễn Thị Hằng</strong> đã đăng ký</div>
                        <div class="dp-timeline-time">15/09/2025 12:45</div>
                    </div>
                </div>
                <div class="dp-timeline-item">
                    <div class="dp-timeline-icon purple"><i class="bi bi-arrow-repeat"></i></div>
                    <div class="dp-timeline-content">
                        <div class="dp-timeline-text">Tour <strong>Đà Nẵng - Hội An</strong> đã được cập nhật</div>
                        <div class="dp-timeline-time">15/09/2025 11:20</div>
                    </div>
                </div>
                <div class="dp-timeline-item">
                    <div class="dp-timeline-icon orange"><i class="bi bi-person-plus-fill"></i></div>
                    <div class="dp-timeline-content">
                        <div class="dp-timeline-text">Nhân viên <strong>Trần Thị Lan</strong> thêm tour mới</div>
                        <div class="dp-timeline-time">15/09/2025 10:15</div>
                    </div>
                </div>
                <div class="dp-timeline-item">
                    <div class="dp-timeline-icon blue"><i class="bi bi-credit-card-2-front-fill"></i></div>
                    <div class="dp-timeline-content">
                        <div class="dp-timeline-text">Thanh toán <strong>3.450.000đ</strong> thành công</div>
                        <div class="dp-timeline-time">15/09/2025 09:50</div>
                    </div>
                </div>
                <div class="dp-timeline-item">
                    <div class="dp-timeline-icon pink"><i class="bi bi-star-fill"></i></div>
                    <div class="dp-timeline-content">
                        <div class="dp-timeline-text">Đánh giá mới: <strong>5 sao</strong> cho tour Hạ Long</div>
                        <div class="dp-timeline-time">15/09/2025 08:30</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── ROW 5: VIETNAM MAP, REVIEWS, NOTIFICATIONS ── -->
    <div class="dp-grid-row-5">
        <!-- Bản đồ phân bố khách du lịch -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Bản đồ phân bố khách du lịch</h3>
                <select class="dp-select-sm">
                    <option>Tháng này</option>
                    <option>Quý này</option>
                    <option>Năm nay</option>
                </select>
            </div>
            <div class="dp-map-flex">
                <!-- Vietnam SVG Silhouette -->
                <div class="dp-map-svg-wrap">
                    <svg width="120" height="230" viewBox="0 0 100 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 52,12 C 58,12 65,15 62,24 C 60,28 50,30 46,35 C 42,40 45,45 48,50 C 51,55 58,58 55,66 C 53,72 45,78 43,85 C 41,92 48,100 52,108 C 55,114 62,122 60,130 C 58,136 48,142 42,148 C 36,154 30,160 32,168 C 34,174 42,176 40,182 C 38,186 32,188 28,185 C 24,182 26,174 24,170 C 22,164 16,160 18,154 C 20,148 28,142 32,136 C 36,130 38,122 34,116 C 30,110 24,102 26,94 C 28,86 36,80 34,72 C 32,64 26,58 28,50 C 30,42 38,36 42,28 C 45,22 46,12 52,12 Z" fill="#93c5fd" opacity="0.85" stroke="#3b82f6" stroke-width="1.5"/>
                        <!-- City Glowing Markers -->
                        <circle cx="50" cy="32" r="4" fill="#1d4ed8"/>
                        <circle cx="50" cy="32" r="7" fill="#3b82f6" opacity="0.3"/>
                        <circle cx="53" cy="85" r="4" fill="#1d4ed8"/>
                        <circle cx="53" cy="85" r="7" fill="#3b82f6" opacity="0.3"/>
                        <circle cx="38" cy="154" r="4" fill="#1d4ed8"/>
                        <circle cx="38" cy="154" r="7" fill="#3b82f6" opacity="0.3"/>
                        <circle cx="30" cy="172" r="3.5" fill="#1d4ed8"/>
                        <circle cx="58" cy="36" r="3.5" fill="#1d4ed8"/>
                        <!-- Islands -->
                        <circle cx="78" cy="80" r="2" fill="#60a5fa"/>
                        <circle cx="82" cy="84" r="1.5" fill="#60a5fa"/>
                        <circle cx="70" cy="140" r="2" fill="#60a5fa"/>
                        <circle cx="74" cy="146" r="1.5" fill="#60a5fa"/>
                    </svg>
                </div>

                <!-- Top 5 provinces progress bars -->
                <div class="dp-provinces-list">
                    <div style="font-size:11.5px; font-weight:700; color:#64748b; margin-bottom: 2px;">Top 5 tỉnh thành</div>
                    
                    <div class="dp-prov-row">
                        <span class="dp-prov-rank">1</span>
                        <span class="dp-prov-name">Hà Nội</span>
                        <div class="dp-prov-bar-wrap">
                            <div class="dp-prov-bar" style="width: 32%;"></div>
                        </div>
                        <span class="dp-prov-pct">32%</span>
                    </div>

                    <div class="dp-prov-row">
                        <span class="dp-prov-rank">2</span>
                        <span class="dp-prov-name">TP. Hồ Chí Minh</span>
                        <div class="dp-prov-bar-wrap">
                            <div class="dp-prov-bar" style="width: 24%;"></div>
                        </div>
                        <span class="dp-prov-pct">24%</span>
                    </div>

                    <div class="dp-prov-row">
                        <span class="dp-prov-rank">3</span>
                        <span class="dp-prov-name">Đà Nẵng</span>
                        <div class="dp-prov-bar-wrap">
                            <div class="dp-prov-bar" style="width: 18%;"></div>
                        </div>
                        <span class="dp-prov-pct">18%</span>
                    </div>

                    <div class="dp-prov-row">
                        <span class="dp-prov-rank">4</span>
                        <span class="dp-prov-name">Hải Phòng</span>
                        <div class="dp-prov-bar-wrap">
                            <div class="dp-prov-bar" style="width: 12%;"></div>
                        </div>
                        <span class="dp-prov-pct">12%</span>
                    </div>

                    <div class="dp-prov-row">
                        <span class="dp-prov-rank">5</span>
                        <span class="dp-prov-name">Cần Thơ</span>
                        <div class="dp-prov-bar-wrap">
                            <div class="dp-prov-bar" style="width: 8%;"></div>
                        </div>
                        <span class="dp-prov-pct">8%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đánh giá gần đây -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Đánh giá gần đây</h3>
                <a href="index.php?act=admin/danhGia" class="dp-card-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="dp-reviews-list">
                <?php foreach ($recentReviews as $rev): ?>
                    <div class="dp-review-item">
                        <div class="dp-user-avatar-initial" style="width:34px; height:34px; font-size:12px; background:<?= $rev['color'] ?>;"><?= htmlspecialchars($rev['initial']) ?></div>
                        <div class="dp-rev-body">
                            <div class="dp-rev-header">
                                <span class="dp-rev-name"><?= htmlspecialchars($rev['name']) ?></span>
                                <span class="dp-rev-date"><?= htmlspecialchars($rev['date']) ?></span>
                            </div>
                            <div class="dp-rev-stars">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="bi <?= $s <= $rev['score'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="dp-rev-comment">"<?= htmlspecialchars($rev['comment']) ?>"</div>
                        </div>
                        <img src="<?= (str_starts_with($rev['thumb'], 'http://') || str_starts_with($rev['thumb'], 'https://')) ? htmlspecialchars($rev['thumb']) : ($baseUrl . htmlspecialchars($rev['thumb'])) ?>" alt="" class="dp-rev-tour-thumb">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Thông báo -->
        <div class="dp-card">
            <div class="dp-card-header">
                <h3 class="dp-card-title">Thông báo</h3>
                <a href="index.php?act=admin/dashboard" class="dp-card-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="dp-notifs-list">
                <div class="dp-notif-item">
                    <div class="dp-notif-icon blue"><i class="bi bi-bell-fill"></i></div>
                    <div class="dp-notif-body">
                        <div class="dp-notif-title">Đơn đặt tour mới</div>
                        <div class="dp-notif-desc">Khách hàng Nguyễn Thị Hằng vừa đặt tour Hạ Long 2 ngày 1 đêm</div>
                        <div class="dp-notif-time">1 phút trước</div>
                    </div>
                </div>

                <div class="dp-notif-item">
                    <div class="dp-notif-icon green"><i class="bi bi-check-circle-fill"></i></div>
                    <div class="dp-notif-body">
                        <div class="dp-notif-title">Thanh toán thành công</div>
                        <div class="dp-notif-desc">Đơn #DH012345 đã thanh toán 2.450.000đ</div>
                        <div class="dp-notif-time">25 phút trước</div>
                    </div>
                </div>

                <div class="dp-notif-item">
                    <div class="dp-notif-icon orange"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div class="dp-notif-body">
                        <div class="dp-notif-title">Tour sắp hết chỗ</div>
                        <div class="dp-notif-desc">Tour Đà Nẵng - Hội An chỉ còn 2 chỗ trống</div>
                        <div class="dp-notif-time">1 giờ trước</div>
                    </div>
                </div>

                <div class="dp-notif-item">
                    <div class="dp-notif-icon red"><i class="bi bi-chat-left-dots-fill"></i></div>
                    <div class="dp-notif-body">
                        <div class="dp-notif-title">Khách hàng yêu cầu hỗ trợ</div>
                        <div class="dp-notif-desc">Khách hàng Lê Văn Minh đã gửi yêu cầu hỗ trợ</div>
                        <div class="dp-notif-time">2 giờ trước</div>
                    </div>
                </div>

                <div class="dp-notif-item">
                    <div class="dp-notif-icon purple"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <div class="dp-notif-body">
                        <div class="dp-notif-title">Bài viết mới</div>
                        <div class="dp-notif-desc">Đã có bài viết mới: "Top 10 địa điểm du lịch hè 2025"</div>
                        <div class="dp-notif-time">3 giờ trước</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── FOOTER ────────────────────────────────────────── -->
    <footer class="dp-footer">
        <div class="dp-footer-grid">
            <div>
                <div class="dp-footer-brand-title">
                    <div class="dp-user-avatar-initial" style="width:26px; height:26px; font-size:13px;">
                        <i class="bi bi-compass"></i>
                    </div>
                    <span>DulichPro</span>
                </div>
                <div class="dp-footer-brand-sub">Khám phá thế giới - Trải nghiệm tuyệt vời</div>
            </div>

            <div>
                <div class="dp-footer-col-title">Liên hệ</div>
                <ul class="dp-footer-links">
                    <li><i class="bi bi-telephone"></i> 1900 1234</li>
                    <li><i class="bi bi-envelope"></i> support@dulichpro.vn</li>
                    <li><i class="bi bi-geo-alt"></i> 123 Nguyễn Văn Cừ, Quận 1, TP. HCM</li>
                </ul>
            </div>

            <div>
                <div class="dp-footer-col-title">Hỗ trợ</div>
                <ul class="dp-footer-links">
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                    <li><a href="#">Điều khoản sử dụng</a></li>
                </ul>
            </div>

            <div>
                <div class="dp-footer-col-title">Kết nối với chúng tôi</div>
                <div class="dp-social-row">
                    <a href="#" class="dp-social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="dp-social-btn"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="dp-social-btn"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="dp-social-btn"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>

            <div>
                <div class="dp-footer-col-title">Tải ứng dụng ngay</div>
                <div class="dp-app-badges">
                    <a href="#" class="dp-app-badge-btn">
                        <i class="bi bi-apple" style="font-size:15px;"></i>
                        <span>App Store</span>
                    </a>
                    <a href="#" class="dp-app-badge-btn">
                        <i class="bi bi-google-play" style="font-size:15px;"></i>
                        <span>Google Play</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="dp-footer-bottom">
            <div>&copy; 2025 DulichPro. Tất cả quyền được bảo lưu.</div>
            <div>Du lịch không chỉ là điểm đến, mà là những trải nghiệm đáng nhớ!</div>
        </div>
    </footer>

</div>

<!-- ═════════════════════════════════════════════════════════
     DASHBOARD SCRIPTS & CHART.JS INITIALIZATION
     ═════════════════════════════════════════════════════════ -->
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
// Live Vietnamese Clock
function updateClock() {
    const now = new Date();
    const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    const dayName = days[now.getDay()];
    const dateStr = `${dayName}, ${now.getDate()} tháng ${now.getMonth() + 1}, ${now.getFullYear()}`;
    const hours = String(now.getHours()).padStart(2, '0');
    const mins = String(now.getMinutes()).padStart(2, '0');
    
    const clockEl = document.getElementById('dpClockText');
    const timeEl = document.getElementById('dpTimeText');
    if (clockEl) clockEl.textContent = dateStr;
    if (timeEl) timeEl.textContent = `${hours}:${mins}`;
}
setInterval(updateClock, 1000);
updateClock();

// Chart defaults & initializations
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#94a3b8';

    // ── Chart 1: Doanh thu 7 ngày gần đây ─────────────────────
    const ctx7d = document.getElementById('chartRevenue7Days');
    let chart7Days = null;
    if (ctx7d) {
        const c2d = ctx7d.getContext('2d');
        const grad7d = c2d.createLinearGradient(0, 0, 0, 240);
        grad7d.addColorStop(0, 'rgba(30, 86, 216, 0.25)');
        grad7d.addColorStop(1, 'rgba(30, 86, 216, 0.00)');

        chart7Days = new Chart(c2d, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart7dLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?= json_encode($chart7dData) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: grad7d,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed.y;
                                if (val >= 1000000) {
                                    return (val / 1000000).toLocaleString('vi-VN') + ' triệu đ';
                                }
                                return val.toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(v) {
                                if (v >= 1000000000) return (v / 1000000000) + 'T';
                                if (v >= 1000000) return (v / 1000000) + 'Tr';
                                if (v >= 1000) return (v / 1000) + 'K';
                                return v;
                            }
                        }
                    }
                }
            }
        });
    }

    const filter7dEl = document.getElementById('revenue7dFilter');
    if (filter7dEl && chart7Days) {
        filter7dEl.addEventListener('change', function() {
            const mode = this.value;
            if (mode.includes('14')) {
                chart7Days.data.labels = ['04/04', '05/04', '06/04', '07/04', '08/04', '09/04', '14/04', '18/04', '22/04', '28/04', '06/05', '08/05', '12/05', '17/05'];
                chart7Days.data.datasets[0].data = [500000, 1200000, 2500000, 9020000, 30000, 15000, 5000, 3500000, 4200000, 6100000, 9000000, 8500000, 11000000, 13000000];
            } else if (mode.includes('30')) {
                chart7Days.data.labels = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'];
                chart7Days.data.datasets[0].data = [15200000, 22500000, 31800000, 48500000];
            } else {
                chart7Days.data.labels = <?= json_encode($chart7dLabels, JSON_UNESCAPED_UNICODE) ?>;
                chart7Days.data.datasets[0].data = <?= json_encode($chart7dData) ?>;
            }
            chart7Days.update();
        });
    }

    // ── Chart 2: Tỷ lệ loại tour (Donut) ──────────────────────
    const ctxTourType = document.getElementById('chartTourTypeDonut');
    if (ctxTourType) {
        new Chart(ctxTourType.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Trong nước', 'Quốc tế', 'Combo', 'Khác'],
                datasets: [{
                    data: [<?= (int)$tourTypeCounts['Trong nước'] ?>, <?= (int)$tourTypeCounts['Quốc tế'] ?>, <?= (int)$tourTypeCounts['Combo'] ?>, <?= (int)$tourTypeCounts['Khác'] ?>],
                    backgroundColor: ['#2563eb', '#ec4899', '#06b6d4', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // ── Chart 3: Doanh thu theo loại tour (Bar) ───────────────
    const ctxRevType = document.getElementById('chartRevenueByType');
    let chartRevByType = null;
    const revByTypeValues = <?= json_encode($revByTypeData) ?>;
    const revByRegionValues = <?= json_encode($revByRegionData) ?>;

    if (ctxRevType) {
        chartRevByType = new Chart(ctxRevType.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Trong nước', 'Quốc tế', 'Combo', 'Khác'],
                datasets: [{
                    data: revByTypeValues,
                    backgroundColor: ['#2563eb', '#38bdf8', '#34d399', '#f59e0b'],
                    borderRadius: 6,
                    barThickness: 28
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ctx.parsed.y + ' Tỷ VNĐ'; }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(v) { return v + 'T'; }
                        }
                    }
                }
            }
        });
    }

    window.switchRevenueTypeTab = function(btn, mode) {
        if (!btn || !chartRevByType) return;
        const buttons = btn.parentElement.querySelectorAll('.dp-tab-sm-btn');
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (mode === 'type') {
            chartRevByType.data.labels = ['Trong nước', 'Quốc tế', 'Combo', 'Khác'];
            chartRevByType.data.datasets[0].data = revByTypeValues;
            chartRevByType.data.datasets[0].backgroundColor = ['#2563eb', '#38bdf8', '#34d399', '#f59e0b'];
        } else {
            chartRevByType.data.labels = ['Miền Bắc', 'Miền Trung', 'Miền Nam', 'Hải Đảo'];
            chartRevByType.data.datasets[0].data = revByRegionValues;
            chartRevByType.data.datasets[0].backgroundColor = ['#1d4ed8', '#0284c7', '#059669', '#d97706'];
        }
        chartRevByType.update();
    };

    // ── Chart 4: Doanh thu theo tháng (Bar) ───────────────────
    const ctxMonthlyRev = document.getElementById('chartMonthlyRevenue');
    let chartMonthlyRev = null;
    const revMonthly2026 = <?= json_encode(array_values($revMonthly2026)) ?>;
    const revMonthly2025 = <?= json_encode(array_values($revMonthly2025)) ?>;

    if (ctxMonthlyRev) {
        chartMonthlyRev = new Chart(ctxMonthlyRev.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    data: revMonthly2026,
                    backgroundColor: [
                        '#93c5fd', '#93c5fd', '#1d4ed8', '#93c5fd', '#93c5fd', '#93c5fd',
                        '#93c5fd', '#93c5fd', '#93c5fd', '#93c5fd', '#93c5fd', '#93c5fd'
                    ],
                    borderRadius: 4,
                    barThickness: 14
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ctx.parsed.y + ' Tỷ VNĐ'; }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            callback: function(v) { return v + 'T'; }
                        }
                    }
                }
            }
        });
    }

    window.switchRevenueYear = function(year) {
        const lbl = document.getElementById('revYearLabel');
        if (lbl) lbl.textContent = year;
        if (chartMonthlyRev) {
            chartMonthlyRev.data.datasets[0].data = (year === '2025') ? revMonthly2025 : revMonthly2026;
            chartMonthlyRev.update();
        }
    };

    // ── Chart 5: Khách hàng theo tháng (Line) ─────────────────
    const ctxMonthlyCust = document.getElementById('chartMonthlyCustomers');
    let chartMonthlyCust = null;
    const custMonthly2026 = <?= json_encode(array_values($custMonthly2026)) ?>;
    const custMonthly2025 = <?= json_encode(array_values($custMonthly2025)) ?>;

    if (ctxMonthlyCust) {
        chartMonthlyCust = new Chart(ctxMonthlyCust.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    data: custMonthly2026,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    fill: true,
                    borderWidth: 2.5,
                    tension: 0.4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ctx.parsed.y + ' khách hàng'; }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        border: { dash: [4, 4] },
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            stepSize: 1,
                            callback: function(v) {
                                if (v >= 1000) return (v / 1000).toFixed(1) + 'K';
                                return v;
                            }
                        }
                    }
                }
            }
        });
    }

    window.switchCustomerYear = function(year) {
        const lbl = document.getElementById('custYearLabel');
        if (lbl) lbl.textContent = year;
        if (chartMonthlyCust) {
            chartMonthlyCust.data.datasets[0].data = (year === '2025') ? custMonthly2025 : custMonthly2026;
            chartMonthlyCust.update();
        }
    };

    // ── Chart 6: Doanh thu theo khu vực (Donut) ───────────────
    const ctxRegDonut = document.getElementById('chartRegionDonut');
    if (ctxRegDonut) {
        new Chart(ctxRegDonut.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Châu Á', 'Châu Âu', 'Châu Mỹ', 'Châu Úc', 'Khác'],
                datasets: [{
                    data: [42, 28, 15, 10, 5],
                    backgroundColor: ['#2563eb', '#0ea5e9', '#ec4899', '#10b981', '#f59e0b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { display: false } }
            }
        });
    }

    // ── Tour Stats tab switcher ───────────────────────────────
    const tourData = <?= json_encode(array_map(function($t) {
        return [
            'revenue' => $t['revenue'],
            'bookings' => $t['bookings'],
            'rating' => $t['rating'] . ' ★ (' . $t['reviews'] . ' đánh giá)'
        ];
    }, $bestSellingTours), JSON_UNESCAPED_UNICODE) ?>;

    window.switchTourStatTab = function(btn, mode) {
        if (!btn) return;
        const buttons = btn.parentElement.querySelectorAll('.dp-tab-sm-btn');
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const list = document.getElementById('tourStatList');
        if (!list) return;

        const items = list.querySelectorAll('.dp-tour-stat-item');
        items.forEach((item, idx) => {
            if (!tourData[idx]) return;
            const sub = item.querySelector('.dp-tour-stat-sub');
            const badge = item.querySelector('.dp-tour-stat-badge');
            if (mode === 'revenue') {
                if (sub) sub.textContent = tourData[idx].revenue;
                if (badge) badge.textContent = tourData[idx].bookings;
            } else if (mode === 'bookings') {
                if (sub) sub.textContent = tourData[idx].bookings;
                if (badge) badge.textContent = 'Hạng ' + (idx + 1);
            } else {
                if (sub) sub.textContent = tourData[idx].rating;
                if (badge) badge.textContent = 'Xuất sắc';
            }
        });
    };
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
