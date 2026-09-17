<?php
/** @var array $tour */
$tour             = $tour ?? [];
$danhGiaTourAvg   = $danhGiaTourAvg ?? 4.8;
$danhGiaTourCount = $danhGiaTourCount ?? 320;
$hinhAnhList      = $hinhAnhList ?? [];
$lichTrinhList    = $lichTrinhList ?? [];
$lichKhoiHanhList = $lichKhoiHanhList ?? [];
$tourCungLoai     = $tourCungLoai ?? [];
$yeuCauList       = $yeuCauList ?? [];
$hdvInfo          = $hdvInfo ?? null;
$danhGiaTourList  = $danhGiaTourList ?? [];

// Lấy thông tin người dùng đang đăng nhập
$userId = (int)($_SESSION['user_id'] ?? 0);
$currentUser = null;
if ($userId > 0) {
    try {
        $db = connectDB();
        $stmt = $db->prepare("SELECT * FROM nguoi_dung WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $currentUser = null;
    }
}
$userName = !empty($currentUser['ho_ten']) ? $currentUser['ho_ten'] : (!empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Trần Thị Bình');
$userEmail = !empty($currentUser['email']) ? $currentUser['email'] : 'tranthibinh@test.com';
$userAvatar = !empty($currentUser['avatar']) ? $currentUser['avatar'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';

// Default fallback images matching Image 2
$fallbackGallery = [
    'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1400&q=80', // Ảnh lớn
    'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?auto=format&fit=crop&w=800&q=80',  // Thuyền
    'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80',  // Khách sạn
    'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80',  // Hoạt động
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',  // Cảnh hoàng hôn
];

$anhDaiDien = $tour['hinh_anh'] ?? '';
if (empty($anhDaiDien) && !empty($hinhAnhList) && !empty($hinhAnhList[0]['url_anh'])) {
    $anhDaiDien = $hinhAnhList[0]['url_anh'];
}
if (empty($anhDaiDien)) {
    $anhDaiDien = $fallbackGallery[0];
}

// Xây dựng danh sách 5 ảnh
$galleryImages = [];
if (!empty($anhDaiDien)) {
    $galleryImages[] = $anhDaiDien;
}
if (!empty($hinhAnhList)) {
    foreach ($hinhAnhList as $ha) {
        if (!empty($ha['url_anh']) && !in_array($ha['url_anh'], $galleryImages, true)) {
            $galleryImages[] = $ha['url_anh'];
        }
    }
}
if (count($galleryImages) < 5) {
    for ($i = count($galleryImages); $i < 5; $i++) {
        $galleryImages[] = $fallbackGallery[$i % count($fallbackGallery)];
    }
}

// Chọn lịch khởi hành ưu tiên
$lichKhoiHanhHienThi = null;
$today = date('Y-m-d');
if (!empty($lichKhoiHanhList)) {
    foreach ($lichKhoiHanhList as $lk) {
        if (!empty($lk['ngay_khoi_hanh']) && $lk['ngay_khoi_hanh'] >= $today) {
            $lichKhoiHanhHienThi = $lk;
            break;
        }
    }
    if ($lichKhoiHanhHienThi === null) {
        $lichKhoiHanhHienThi = end($lichKhoiHanhList);
        reset($lichKhoiHanhList);
    }
}

$maTourHienThi = trim((string)($tour['ma_tour'] ?? ''));
if ($maTourHienThi === '') {
    $tourId = (int)($tour['tour_id'] ?? $tour['id'] ?? 0);
    $maTourHienThi = $tourId > 0 ? 'TOUR-' . str_pad((string)$tourId, 4, '0', STR_PAD_LEFT) : 'TOUR-0103';
}

$khoiHanhHienThi = trim((string)($tour['noi_khoi_hanh'] ?? ''));
if ($khoiHanhHienThi === '' && !empty($lichKhoiHanhHienThi['diem_tap_trung'])) {
    $khoiHanhHienThi = $lichKhoiHanhHienThi['diem_tap_trung'];
}
if ($khoiHanhHienThi === '') {
    $khoiHanhHienThi = 'Hà Nội';
}

$ngayKhoiHanhHienThi = '';
if (!empty($lichKhoiHanhHienThi['ngay_khoi_hanh'])) {
    $ngayKhoiHanhHienThi = date('d/m/Y', strtotime($lichKhoiHanhHienThi['ngay_khoi_hanh']));
} else {
    $ngayKhoiHanhHienThi = date('d/m/Y', strtotime('+3 days'));
}

$thoiGianHienThi = trim((string)($tour['thoi_gian'] ?? ''));
if ($thoiGianHienThi === '' && !empty($lichKhoiHanhHienThi['ngay_khoi_hanh']) && !empty($lichKhoiHanhHienThi['ngay_ket_thuc'])) {
    $start = strtotime($lichKhoiHanhHienThi['ngay_khoi_hanh']);
    $end = strtotime($lichKhoiHanhHienThi['ngay_ket_thuc']);
    if ($start && $end && $end >= $start) {
        $soNgay = (int)floor(($end - $start) / 86400) + 1;
        $thoiGianHienThi = $soNgay . ' ngày ' . max(1, $soNgay - 1) . ' đêm';
    }
}
if ($thoiGianHienThi === '') {
    $thoiGianHienThi = '2 ngày 1 đêm';
}

$giaTourHienThi = (float)($tour['gia_tour'] ?? $tour['gia_co_ban'] ?? 2450000);
if ($giaTourHienThi <= 0) {
    $giaTourHienThi = 2450000;
}
$giaGoc = round($giaTourHienThi * 1.25 / 10000) * 10000;
if ($giaGoc <= $giaTourHienThi) {
    $giaGoc = $giaTourHienThi + 650000;
}
$tietKiem = $giaGoc - $giaTourHienThi;
$giaTreEm = round($giaTourHienThi * 0.75 / 1000) * 1000;

$danhGiaAvgHero = isset($danhGiaTourAvg) && (float)$danhGiaTourAvg > 0 ? (float)$danhGiaTourAvg : 4.8;
$danhGiaCountHero = isset($danhGiaTourCount) && (int)$danhGiaTourCount > 0 ? (int)$danhGiaTourCount : 320;

$isBookingLockedBy48h = false;
if (!empty($lichKhoiHanhHienThi['ngay_khoi_hanh'])) {
    $departureTs = strtotime($lichKhoiHanhHienThi['ngay_khoi_hanh'] . ' 00:00:00');
    if ($departureTs !== false) {
        $secondsLeft = $departureTs - time();
        if ($secondsLeft <= (48 * 3600) && $secondsLeft > -86400) {
            $isBookingLockedBy48h = true;
        }
    }
}

// Danh sách đánh giá mẫu phong phú nếu chưa có
if (empty($danhGiaTourList)) {
    $danhGiaTourList = [
        [
            'ho_ten' => 'Nguyễn Thị Hằng',
            'dia_diem' => 'Hà Nội',
            'ngay_danh_gia' => '2026-08-12',
            'diem' => 5,
            'noi_dung' => 'Tour rất chất lượng, hướng dẫn viên nhiệt tình, phục vụ chu đáo. Cảnh đẹp như tranh, chắc chắn sẽ quay lại!'
        ],
        [
            'ho_ten' => 'Trần Minh Đức',
            'dia_diem' => 'Đà Nẵng',
            'ngay_danh_gia' => '2026-08-05',
            'diem' => 5,
            'noi_dung' => 'Du thuyền hiện đại, đồ ăn ngon, lịch trình hợp lý. Gia đình tôi rất hài lòng!'
        ],
        [
            'ho_ten' => 'Lê Thị Mai',
            'dia_diem' => 'TP.HCM',
            'ngay_danh_gia' => '2026-07-28',
            'diem' => 5,
            'noi_dung' => 'Giá cả hợp lý, dịch vụ chuyên nghiệp. Rất đáng để trải nghiệm!'
        ],
    ];
}

// Danh sách tour liên quan mẫu nếu chưa có
if (empty($tourCungLoai)) {
    $tourCungLoai = [
        [
            'id' => 1,
            'ten_tour' => 'Tour Hạ Long 3 ngày 2 đêm',
            'badge' => 'Tour nổi bật',
            'hinh_anh' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=600&q=80',
            'rating' => '4.9 (450 đánh giá)',
            'noi_khoi_hanh' => 'Hà Nội',
            'thoi_gian' => '3 ngày 2 đêm',
            'gia_tour' => 3450000,
        ],
        [
            'id' => 2,
            'ten_tour' => 'Tour Cát Bà 2 ngày 1 đêm',
            'badge' => 'Khuyến mãi',
            'hinh_anh' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80',
            'rating' => '4.7 (320 đánh giá)',
            'noi_khoi_hanh' => 'Hải Phòng',
            'thoi_gian' => '2 ngày 1 đêm',
            'gia_tour' => 2150000,
        ],
        [
            'id' => 3,
            'ten_tour' => 'Tour Ninh Bình 1 ngày',
            'badge' => '',
            'hinh_anh' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80',
            'rating' => '4.8 (290 đánh giá)',
            'noi_khoi_hanh' => 'Hà Nội',
            'thoi_gian' => '1 ngày',
            'gia_tour' => 990000,
        ],
        [
            'id' => 4,
            'ten_tour' => 'Tour Sapa 3 ngày 2 đêm',
            'badge' => '',
            'hinh_anh' => 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?auto=format&fit=crop&w=600&q=80',
            'rating' => '4.9 (510 đánh giá)',
            'noi_khoi_hanh' => 'Hà Nội',
            'thoi_gian' => '3 ngày 2 đêm',
            'gia_tour' => 2890000,
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tour['ten_tour'] ?? 'Chi tiết tour') ?> - DuLichPro</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="<?= BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@1,500;1,600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0066FF;
            --primary-hover: #0052cc;
            --primary-light: #EBF3FF;
            --primary-soft: rgba(0, 102, 255, 0.08);
            --dark: #0F172A;
            --slate-800: #1E293B;
            --slate-600: #475569;
            --slate-500: #64748B;
            --slate-400: #94A3B8;
            --slate-200: #E2E8F0;
            --slate-100: #F1F5F9;
            --slate-50: #F8FAFC;
            --amber: #F59E0B;
            --red: #EF4444;
            --red-light: #FEE2E2;
            --green: #10B981;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 12px 32px -4px rgba(15, 23, 42, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            color: var(--slate-800);
            background-color: #FFFFFF;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── HEADER / TOPBAR (Chuẩn Image 1) ── */
        .site-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1360px;
            margin: 0 auto;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #0284c7 0%, #0066cc 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 22px;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
        }

        .brand-text h1 {
            font-size: 19px;
            font-weight: 800;
            color: #0f2e5a;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.4px;
        }

        .brand-text p {
            font-size: 11px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
            white-space: nowrap;
        }

        .site-nav a {
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 500;
            color: #475569;
            padding: 8px 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .site-nav a:hover {
            color: var(--primary);
            background: var(--primary-soft);
        }

        .site-nav a.active {
            color: var(--primary);
            background: var(--primary-light);
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }

        .btn-icon-round {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            background: #f1f5f9;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            position: relative;
            cursor: pointer;
        }

        .btn-icon-round:hover {
            background: #e2e8f0;
            color: var(--primary);
        }

        .badge-count {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            border-radius: 50%;
            width: 17px;
            height: 17px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 4px 10px 4px 4px;
            border-radius: 30px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            text-decoration: none;
            color: #1e293b;
            transition: background 0.2s;
        }

        .user-profile-btn:hover {
            background: #f1f5f9;
        }

        .user-profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-profile-name {
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
        }

        .user-dropdown-container {
            position: relative;
        }

        .btn-logout-shortcut {
            color: #ef4444 !important;
            background: #fef2f2 !important;
            border: 1px solid #fee2e2 !important;
            transition: all 0.2s ease;
        }

        .btn-logout-shortcut:hover {
            background: #fee2e2 !important;
            color: #dc2626 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.18);
        }

        .user-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
            min-width: 220px;
            padding: 8px;
            display: none;
            z-index: 1100;
        }

        .user-dropdown-container:hover .user-dropdown-menu,
        .user-dropdown-container:focus-within .user-dropdown-menu {
            display: block;
        }

        .user-dropdown-header {
            padding: 10px 12px 8px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 6px;
        }

        .user-dropdown-header strong {
            display: block;
            font-size: 13.5px;
            color: #0f172a;
        }

        .user-dropdown-header small {
            display: block;
            font-size: 11.5px;
            color: #64748b;
            word-break: break-all;
        }

        .user-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            text-decoration: none;
            transition: all 0.15s;
        }

        .user-dropdown-menu a:hover {
            background: #f8fafc;
            color: #0066cc;
        }

        .user-dropdown-menu a.text-danger:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        .user-dropdown-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 6px 0;
        }

        .btn-auth-login {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #1e293b;
            font-size: 14px;
            font-weight: 600;
            padding: 6px 12px;
            transition: color 0.2s;
            white-space: nowrap;
        }

        .btn-auth-login:hover {
            color: #0066cc;
        }

        .btn-auth-register {
            display: inline-flex;
            align-items: center;
            background: #0066cc;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 102, 204, 0.2);
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-auth-register:hover {
            background: #0052a3;
            transform: translateY(-1px);
        }

        /* HERO BANNER (Image 2) */
        .tour-hero {
            position: relative;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.72) 0%, rgba(15, 23, 42, 0.85) 100%),
                        url('<?= htmlspecialchars($anhDaiDien) ?>') center/cover no-repeat;
            color: #FFFFFF;
            padding: 34px 0 38px;
            overflow: hidden;
        }
        .breadcrumb-nav {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }
        .breadcrumb-nav a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
        }
        .breadcrumb-nav a:hover {
            color: #FFFFFF;
        }
        .hero-badge-wrap {
            margin-bottom: 10px;
        }
        .hero-badge {
            background: #0084FF;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .hero-title {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .hero-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
            max-width: 820px;
            margin-bottom: 18px;
            font-weight: 400;
        }
        .hero-meta-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.95);
        }
        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .hero-meta-item i {
            color: rgba(255, 255, 255, 0.8);
        }
        .hero-meta-item .star-gold {
            color: #FFB800;
        }
        .hero-signature {
            position: absolute;
            right: 36px;
            bottom: 26px;
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 22px;
            color: rgba(255, 255, 255, 0.45);
            pointer-events: none;
            user-select: none;
        }

        /* 5-IMAGE GALLERY (ĐƯỢC ĐẶT Ở CỘT TRÁI - NGANG HÀNG VỚI BẢNG GIÁ Ở CỘT PHẢI) */
        .gallery-grid {
            display: grid;
            grid-template-columns: 1.15fr 1fr;
            gap: 10px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            height: 380px;
            margin-bottom: 28px;
        }
        .gallery-left {
            position: relative;
            height: 100%;
        }
        .gallery-left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: var(--radius-lg) 0 0 var(--radius-lg);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .gallery-left:hover img {
            transform: scale(1.02);
        }
        .btn-view-video {
            position: absolute;
            bottom: 14px;
            left: 14px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-view-video:hover {
            background: rgba(0, 102, 255, 0.9);
            color: #FFFFFF;
        }
        .gallery-right {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 10px;
            height: 100%;
        }
        .gallery-thumb-item {
            position: relative;
            height: 100%;
            overflow: hidden;
            cursor: pointer;
        }
        .gallery-thumb-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .gallery-thumb-item:hover img {
            transform: scale(1.04);
        }
        .gallery-thumb-item.top-right img {
            border-radius: 0 var(--radius-lg) 0 0;
        }
        .gallery-thumb-item.bottom-right img {
            border-radius: 0 0 var(--radius-lg) 0;
        }
        .gallery-overlay-more {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 20px;
            font-weight: 800;
            border-radius: 0 0 var(--radius-lg) 0;
            transition: background 0.2s;
        }
        .gallery-thumb-item:hover .gallery-overlay-more {
            background: rgba(0, 102, 255, 0.75);
        }

        /* TABS NAVIGATION */
        .tour-tabs {
            display: flex;
            align-items: center;
            gap: 32px;
            border-bottom: 2px solid var(--slate-100);
            margin-bottom: 28px;
            padding-bottom: 0;
            position: sticky;
            top: 65px;
            background: #FFFFFF;
            z-index: 100;
        }
        .tab-btn {
            background: none;
            border: none;
            padding: 12px 0;
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-500);
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
        }
        .tab-btn:hover {
            color: var(--primary);
        }
        .tab-btn.active {
            color: var(--primary);
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
            border-radius: 3px 3px 0 0;
        }

        /* CONTENT SECTIONS */
        .section-block {
            margin-bottom: 36px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--slate-800);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title i {
            color: var(--primary);
            font-size: 17px;
        }
        .section-desc {
            font-size: 14px;
            color: var(--slate-600);
            line-height: 1.7;
            margin-bottom: 22px;
        }

        /* HIGHLIGHT CARDS (Grid 4 cột) */
        .highlights-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 28px;
        }
        .highlight-card {
            background: #FFFFFF;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-md);
            padding: 16px 12px;
            text-align: center;
            transition: all 0.2s;
        }
        .highlight-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 16px rgba(0, 102, 255, 0.08);
            transform: translateY(-2px);
        }
        .highlight-card .h-icon {
            font-size: 26px;
            color: var(--primary);
            margin-bottom: 10px;
            display: inline-block;
        }
        .highlight-card .h-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-800);
            line-height: 1.4;
        }

        /* DETAILED TIMELINE */
        .timeline-day-card {
            background: #FFFFFF;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 18px;
        }
        .day-header {
            background: var(--slate-50);
            padding: 12px 18px;
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .day-badge {
            background: var(--primary);
            color: #FFFFFF;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 16px;
        }
        .day-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-800);
        }
        .day-content {
            padding: 20px;
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 20px;
        }
        .day-thumb {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: var(--radius-md);
        }
        .timeline-steps {
            position: relative;
            padding-left: 20px;
        }
        .timeline-steps::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: var(--slate-200);
        }
        .timeline-step {
            position: relative;
            margin-bottom: 14px;
            font-size: 13px;
        }
        .timeline-step:last-child {
            margin-bottom: 0;
        }
        .timeline-step::before {
            content: '';
            position: absolute;
            left: -19px;
            top: 5px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            border: 2px solid #FFFFFF;
            box-shadow: 0 0 0 2px rgba(0, 102, 255, 0.2);
        }
        .step-time {
            font-weight: 800;
            color: var(--slate-800);
            margin-right: 6px;
            display: inline-block;
            min-width: 44px;
        }
        .step-text {
            color: var(--slate-600);
        }

        /* AMENITIES */
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }
        .amenity-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-md);
            padding: 14px 10px;
            text-align: center;
        }
        .amenity-card i {
            font-size: 22px;
            color: var(--primary);
            margin-bottom: 6px;
            display: inline-block;
        }
        .amenity-card span {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-800);
        }

        /* REVIEWS */
        .reviews-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .review-nav-btns {
            display: flex;
            gap: 8px;
        }
        .btn-rev-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid var(--slate-200);
            background: #FFFFFF;
            color: var(--slate-600);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-rev-circle:hover {
            background: var(--primary);
            color: #FFFFFF;
            border-color: var(--primary);
        }
        .reviews-layout {
            display: grid;
            grid-template-columns: 180px 1fr;
            gap: 20px;
            align-items: stretch;
        }
        .score-box {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            padding: 24px 16px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .score-num {
            font-size: 38px;
            font-weight: 800;
            color: var(--slate-800);
            line-height: 1;
            margin-bottom: 6px;
        }
        .score-num span {
            font-size: 18px;
            color: var(--slate-500);
            font-weight: 600;
        }
        .stars-row {
            color: var(--amber);
            font-size: 15px;
            margin-bottom: 6px;
            display: flex;
            gap: 2px;
        }
        .score-total {
            font-size: 11px;
            color: var(--slate-500);
            font-weight: 600;
        }
        .reviews-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }
        .review-card-item {
            background: #FFFFFF;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-md);
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .rev-user-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .rev-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .rev-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-800);
            line-height: 1.2;
        }
        .rev-loc {
            font-size: 10px;
            color: var(--slate-400);
        }
        .rev-stars {
            color: var(--amber);
            font-size: 11px;
            margin-bottom: 6px;
        }
        .rev-content {
            font-size: 11px;
            color: var(--slate-600);
            line-height: 1.5;
            font-style: italic;
        }

        /* RIGHT COLUMN: STICKY BOOKING CARD (BẢNG GIÁ NGANG HÀNG VỚI ẢNH) */
        .sticky-sidebar {
            position: sticky;
            top: 75px;
        }
        .booking-card {
            background: #FFFFFF;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            padding: 22px;
            box-shadow: var(--shadow-md);
            margin-bottom: 22px;
        }
        .price-badge-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .badge-discount {
            background: var(--red-light);
            color: var(--red);
            font-size: 11px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 4px;
        }
        .price-original {
            font-size: 13px;
            color: var(--slate-400);
            text-decoration: line-through;
            font-weight: 600;
        }
        .price-main {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.1;
            margin-bottom: 4px;
        }
        .price-main span {
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-500);
        }
        .price-save-note {
            font-size: 12px;
            color: var(--green);
            font-weight: 600;
            margin-bottom: 18px;
        }
        .form-group-custom {
            margin-bottom: 14px;
        }
        .form-label-custom {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .form-label-custom i {
            color: var(--slate-500);
        }
        .custom-select {
            width: 100%;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-sm);
            padding: 9px 12px;
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-800);
            background: #FFFFFF;
            cursor: pointer;
            outline: none;
        }
        .counter-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--slate-100);
        }
        .counter-box:last-child {
            border-bottom: none;
        }
        .counter-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-700);
        }
        .counter-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 2px 6px;
        }
        .btn-counter {
            background: none;
            border: none;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: var(--primary);
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-counter:hover {
            background: var(--slate-100);
        }
        .counter-value {
            font-size: 13px;
            font-weight: 700;
            min-width: 16px;
            text-align: center;
        }
        .btn-book-now {
            width: 100%;
            background: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: var(--radius-md);
            padding: 13px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 16px;
            margin-bottom: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-book-now:hover {
            background: var(--primary-hover);
            color: #FFFFFF;
            box-shadow: 0 4px 16px rgba(0, 102, 255, 0.3);
        }
        .btn-book-now:disabled {
            background: var(--slate-400);
            cursor: not-allowed;
            box-shadow: none;
        }
        .deposit-note {
            text-align: center;
            font-size: 11px;
            color: var(--slate-500);
            margin-bottom: 18px;
        }
        .trust-items {
            border-top: 1px solid var(--slate-100);
            padding-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .trust-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .trust-item i {
            color: var(--primary);
            font-size: 15px;
            margin-top: 2px;
        }
        .trust-item-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-800);
            line-height: 1.2;
        }
        .trust-item-desc {
            font-size: 11px;
            color: var(--slate-500);
        }

        /* INFO SPEC CARD */
        .info-spec-card {
            background: #FFFFFF;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            padding: 22px;
            margin-bottom: 22px;
        }
        .spec-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--slate-800);
            margin-bottom: 14px;
        }
        .spec-table {
            width: 100%;
            border-collapse: collapse;
        }
        .spec-table tr td {
            padding: 8px 0;
            font-size: 13px;
            border-bottom: 1px solid var(--slate-100);
        }
        .spec-table tr:last-child td {
            border-bottom: none;
        }
        .spec-label {
            color: var(--slate-500);
            width: 45%;
        }
        .spec-value {
            color: var(--slate-800);
            font-weight: 700;
            text-align: right;
        }

        /* PROMO BANNER */
        .promo-card {
            position: relative;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.4) 0%, rgba(15, 23, 42, 0.8) 100%),
                        url('https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?auto=format&fit=crop&w=600&q=80') center/cover no-repeat;
            border-radius: var(--radius-lg);
            padding: 26px 20px;
            color: #FFFFFF;
            overflow: hidden;
        }
        .promo-badge {
            background: #FF6600;
            color: #FFFFFF;
            font-size: 10px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .promo-title {
            font-size: 17px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 6px;
        }
        .promo-desc {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 14px;
            line-height: 1.5;
        }
        .btn-promo {
            background: var(--primary);
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 700;
            padding: 7px 15px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }
        .btn-promo:hover {
            background: var(--primary-hover);
            color: #FFFFFF;
        }

        /* RELATED TOURS SECTION */
        .related-section {
            border-top: 1px solid var(--slate-200);
            padding-top: 40px;
            margin-top: 40px;
            margin-bottom: 60px;
        }
        .related-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .related-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--slate-800);
        }
        .link-view-all {
            color: var(--primary);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }
        .rel-card {
            background: #FFFFFF;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            overflow: hidden;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }
        .rel-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }
        .rel-img-wrap {
            position: relative;
            height: 160px;
        }
        .rel-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .rel-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 4px;
            color: #FFFFFF;
        }
        .rel-badge.blue {
            background: var(--primary);
        }
        .rel-badge.orange {
            background: #FF6600;
        }
        .rel-body {
            padding: 14px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }
        .rel-tour-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .rel-rating {
            font-size: 11px;
            color: var(--slate-600);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .rel-rating i {
            color: var(--amber);
        }
        .rel-meta-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 11px;
            color: var(--slate-500);
            margin-bottom: 12px;
        }
        .rel-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--slate-100);
            padding-top: 8px;
        }
        .rel-price {
            font-size: 14px;
            font-weight: 800;
            color: var(--primary);
        }
        .rel-price span {
            font-size: 11px;
            color: var(--slate-400);
            font-weight: 500;
        }
        .rel-arrow {
            color: var(--primary);
            font-size: 15px;
        }

        /* LIGHTBOX MODAL */
        .modal-gallery-img {
            max-height: 80vh;
            object-fit: contain;
        }

        @media (max-width: 991px) {
            .nav-links { display: none; }
            .gallery-grid { height: 260px; grid-template-columns: 1fr 1fr; }
            .highlights-grid { grid-template-columns: repeat(2, 1fr); }
            .amenities-grid { grid-template-columns: repeat(2, 1fr); }
            .reviews-layout { grid-template-columns: 1fr; }
            .reviews-cards-grid { grid-template-columns: 1fr; }
            .related-grid { grid-template-columns: repeat(2, 1fr); }
            .day-content { grid-template-columns: 1fr; }
            .hero-signature { display: none; }
            .sticky-sidebar { position: static; }
        }
        @media (max-width: 576px) {
            .gallery-grid { height: auto; grid-template-columns: 1fr; }
            .gallery-left img { border-radius: var(--radius-lg); height: 220px; }
            .gallery-right { height: 220px; }
            .related-grid { grid-template-columns: 1fr; }
            .highlights-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- 1. SITE HEADER (Chuẩn Image 1) -->
    <header class="site-header">
        <div class="header-container">
            <a href="index.php?act=khachHang/dashboard" class="brand-block">
                <div class="brand-logo-icon">
                    <i class="bi bi-tsunami"></i>
                </div>
                <div class="brand-text">
                    <h1>DuLichPro</h1>
                    <p>Khám phá thế giới - Trải nghiệm tuyệt vời</p>
                </div>
            </a>

            <ul class="site-nav">
                <li><a href="index.php?act=khachHang/dashboard">Trang chủ</a></li>
                <li><a href="index.php?act=khachHang/danhSachTour" class="active">Tour du lịch</a></li>
                <li><a href="index.php?act=khachHang/dashboard#hotels">Khách sạn</a></li>
                <li><a href="index.php?act=khachHang/dashboard#flights">Vé máy bay</a></li>
                <li><a href="index.php?act=khachHang/dashboard#combos">Combo</a></li>
                <li><a href="index.php?act=khachHang/dashboard#visa">Visa</a></li>
                <li><a href="index.php?act=khachHang/dashboard#about">Về chúng tôi</a></li>
            </ul>

            <div class="header-actions">
                <a href="index.php?act=khachHang/danhSachTour" class="btn-icon-round" title="Tìm kiếm">
                    <i class="bi bi-search"></i>
                </a>

                <a href="index.php?act=khachHang/thongBao" class="btn-icon-round" title="Thông báo">
                    <i class="bi bi-bell"></i>
                    <span class="badge-count">3</span>
                </a>

                <?php if ($userId > 0 || !empty($_SESSION['user_id'])): ?>
                    <div class="user-dropdown-container">
                        <a href="index.php?act=khachHang/capNhatThongTin" class="user-profile-btn" title="Trang cá nhân">
                            <img src="<?= htmlspecialchars($userAvatar); ?>" alt="Avatar" class="user-profile-avatar" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';">
                            <span class="user-profile-name"><?= htmlspecialchars($userName); ?></span>
                            <i class="bi bi-chevron-down" style="font-size: 11px; color: #64748b;"></i>
                        </a>
                        <div class="user-dropdown-menu">
                            <div class="user-dropdown-header">
                                <strong><?= htmlspecialchars($userName); ?></strong>
                                <small><?= htmlspecialchars($userEmail); ?></small>
                            </div>
                            <a href="index.php?act=khachHang/capNhatThongTin"><i class="bi bi-person"></i> Trang cá nhân</a>
                            <a href="index.php?act=khachHang/guiYeuCauTour"><i class="bi bi-ticket-perforated"></i> Đơn đặt tour</a>
                            <a href="index.php?act=khachHang/tourYeuThich"><i class="bi bi-heart"></i> Tour yêu thích</a>
                            <a href="index.php?act=khachHang/viCuaToi"><i class="bi bi-wallet2"></i> Ví của tôi</a>
                            <a href="index.php?act=khachHang/guiYeuCauHoTro"><i class="bi bi-question-circle"></i> Hỗ trợ</a>
                            <div class="user-dropdown-divider"></div>
                            <a href="index.php?act=auth/logout" class="text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                        </div>
                    </div>

                    <!-- Nút tắt đăng xuất nhanh -->
                    <a href="index.php?act=auth/logout" class="btn-icon-round btn-logout-shortcut" title="Đăng xuất" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="index.php?act=login" class="btn-auth-login">
                        <i class="bi bi-person"></i>
                        <span>Đăng nhập</span>
                    </a>
                    <a href="index.php?act=register" class="btn-auth-register">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- 2. HERO BANNER (Image 2 style) -->
    <section class="tour-hero">
        <div class="container position-relative">
            <div class="breadcrumb-nav">
                <a href="index.php?act=khachHang/dashboard">Trang chủ</a>
                <span>&gt;</span>
                <a href="index.php?act=khachHang/danhSachTour">Tour du lịch</a>
                <span>&gt;</span>
                <span><?= htmlspecialchars($tour['ten_tour'] ?? 'Chi tiết tour') ?></span>
            </div>

            <div class="hero-badge-wrap">
                <span class="hero-badge"><?= htmlspecialchars($tour['loai_tour'] ?? 'Tour trong nước') ?></span>
            </div>

            <h1 class="hero-title"><?= htmlspecialchars($tour['ten_tour'] ?? 'Tour Hạ Long 2 ngày 1 đêm') ?></h1>

            <p class="hero-subtitle">
                <?= htmlspecialchars($tour['mo_ta_ngan'] ?? 'Khám phá kỳ quan thiên nhiên thế giới - Vịnh Hạ Long, trải nghiệm du thuyền sang trọng và nhiều hoạt động thú vị.') ?>
            </p>

            <div class="hero-meta-row">
                <div class="hero-meta-item">
                    <i class="bi bi-star-fill star-gold"></i>
                    <span><strong><?= number_format($danhGiaAvgHero, 1) ?></strong> (<?= (int)$danhGiaCountHero ?> đánh giá)</span>
                </div>
                <div class="hero-meta-item">
                    <i class="bi bi-geo-alt"></i>
                    <span><?= htmlspecialchars($khoiHanhHienThi) ?></span>
                </div>
                <div class="hero-meta-item">
                    <i class="bi bi-calendar-event"></i>
                    <span><?= htmlspecialchars($thoiGianHienThi) ?></span>
                </div>
                <div class="hero-meta-item">
                    <i class="bi bi-truck-front"></i>
                    <span>Xe ô tô + Du thuyền</span>
                </div>
            </div>

            <div class="hero-signature">
                Vịnh Hạ Long - Kỳ quan giữa lòng Việt Nam ~
            </div>
        </div>
    </section>

    <!-- MAIN BODY -->
    <main class="container my-4">

        <!-- Flash alerts -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- 3. TWO-COLUMN LAYOUT: BẢNG GIÁ NGANG HÀNG CHÍNH XÁC VỚI 5 ẢNH GALLERY -->
        <div class="row g-4 align-items-start">
            
            <!-- LEFT COLUMN (col-lg-8): GALLERY + TABS + NỘI DUNG TOUR -->
            <div class="col-lg-8">
                
                <!-- 5-PHOTO GALLERY (Nằm trên cùng của cột trái) -->
                <div class="gallery-grid">
                    <!-- Large Left Photo -->
                    <div class="gallery-left" onclick="openLightbox(0)">
                        <img id="mainGalleryImg" src="<?= htmlspecialchars($galleryImages[0]) ?>" alt="Ảnh chính tour">
                        <button class="btn-view-video" type="button" onclick="event.stopPropagation(); alert('Video giới thiệu tour đang được cập nhật.');">
                            <i class="bi bi-play-fill"></i> Xem video
                        </button>
                    </div>

                    <!-- 4 Right Thumbnails -->
                    <div class="gallery-right">
                        <div class="gallery-thumb-item" onclick="openLightbox(1)">
                            <img src="<?= htmlspecialchars($galleryImages[1]) ?>" alt="Ảnh tour 2">
                        </div>
                        <div class="gallery-thumb-item top-right" onclick="openLightbox(2)">
                            <img src="<?= htmlspecialchars($galleryImages[2]) ?>" alt="Ảnh tour 3">
                        </div>
                        <div class="gallery-thumb-item" onclick="openLightbox(3)">
                            <img src="<?= htmlspecialchars($galleryImages[3]) ?>" alt="Ảnh tour 4">
                        </div>
                        <div class="gallery-thumb-item bottom-right" onclick="openLightbox(4)">
                            <img src="<?= htmlspecialchars($galleryImages[4]) ?>" alt="Ảnh tour 5">
                            <div class="gallery-overlay-more">
                                +12
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS (Nằm ngay bên dưới 5 ảnh) -->
                <nav class="tour-tabs" id="tourTabs">
                    <button class="tab-btn active" onclick="scrollToTab('tong-quan', this)">Tổng quan</button>
                    <button class="tab-btn" onclick="scrollToTab('lich-trinh', this)">Lịch trình</button>
                    <button class="tab-btn" onclick="scrollToTab('dich-vu', this)">Dịch vụ bao gồm</button>
                    <button class="tab-btn" onclick="scrollToTab('dieu-khoan', this)">Điều khoản</button>
                    <button class="tab-btn" onclick="scrollToTab('danh-gia', this)">Đánh giá</button>
                </nav>

                <!-- SECTION 1: GIỚI THIỆU TOUR -->
                <section class="section-block" id="tong-quan">
                    <h2 class="section-title">
                        <i class="bi bi-stars"></i> Giới thiệu tour
                    </h2>
                    <div class="section-desc">
                        <?= !empty($tour['mo_ta']) ? nl2br(htmlspecialchars($tour['mo_ta'])) : 'Tour Hạ Long 2 ngày 1 đêm là hành trình lý tưởng để bạn khám phá vẻ đẹp hùng vĩ của Vịnh Hạ Long – di sản thiên nhiên thế giới được UNESCO công nhận. Bạn sẽ được trải nghiệm du thuyền sang trọng, thưởng thức ẩm thực đặc sản, tham quan các hang động nổi tiếng và tham gia nhiều hoạt động thú vị trên biển.' ?>
                    </div>

                    <!-- 4 HIGHLIGHTS (Như Hình 2) -->
                    <div class="highlights-grid">
                        <div class="highlight-card">
                            <i class="bi bi-compass h-icon"></i>
                            <div class="h-title">Tham quan<br>Vịnh Hạ Long</div>
                        </div>
                        <div class="highlight-card">
                            <i class="bi bi-water h-icon"></i>
                            <div class="h-title">Du thuyền<br>cao cấp</div>
                        </div>
                        <div class="highlight-card">
                            <i class="bi bi-tsunami h-icon"></i>
                            <div class="h-title">Tắm biển<br>&amp; chèo kayak</div>
                        </div>
                        <div class="highlight-card">
                            <i class="bi bi-egg-fried h-icon"></i>
                            <div class="h-title">Thưởng thức<br>hải sản tươi ngon</div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2: LỊCH TRÌNH CHI TIẾT -->
                <section class="section-block" id="lich-trinh">
                    <h2 class="section-title">
                        <i class="bi bi-chevron-right"></i> Lịch trình chi tiết
                    </h2>

                    <?php if (!empty($lichTrinhList)): ?>
                        <?php foreach ($lichTrinhList as $lt): ?>
                            <div class="timeline-day-card">
                                <div class="day-header">
                                    <span class="day-badge">Ngày <?= (int)($lt['ngay_thu'] ?? 1); ?></span>
                                    <span class="day-title"><?= htmlspecialchars($lt['dia_diem'] ?? 'Hành trình trong ngày'); ?></span>
                                </div>
                                <div class="day-content">
                                    <img class="day-thumb" src="<?= htmlspecialchars($galleryImages[((int)($lt['ngay_thu'] ?? 1)) % count($galleryImages)]) ?>" alt="Ảnh ngày">
                                    <div class="timeline-steps">
                                        <div class="timeline-step">
                                            <span class="step-time">Chi tiết:</span>
                                            <span class="step-text"><?= nl2br(htmlspecialchars($lt['hoat_dong'] ?? 'Khám phá và tham quan theo lịch trình.')); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Lịch trình mẫu chuẩn mực như Ảnh 2 -->
                        <div class="timeline-day-card">
                            <div class="day-header">
                                <span class="day-badge">Ngày 1</span>
                                <span class="day-title">Hà Nội – Hạ Long (Ăn trưa, tối)</span>
                            </div>
                            <div class="day-content">
                                <img class="day-thumb" src="<?= htmlspecialchars($galleryImages[0]) ?>" alt="Ngày 1">
                                <div class="timeline-steps">
                                    <div class="timeline-step">
                                        <span class="step-time">07:30</span>
                                        <span class="step-text">Xe đón quý khách tại điểm hẹn, khởi hành đi Hạ Long.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">11:30</span>
                                        <span class="step-text">Đến cảng Tuần Châu, làm thủ tục lên du thuyền.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">12:00</span>
                                        <span class="step-text">Thưởng thức bữa trưa với các món hải sản tươi ngon.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">14:00</span>
                                        <span class="step-text">Tham quan hang Sửng Sốt, chèo kayak hoặc tắm biển.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">18:00</span>
                                        <span class="step-text">Quay về du thuyền, tham gia tiệc tối và các hoạt động tự do. Nghỉ đêm trên du thuyền 4 sao.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="timeline-day-card">
                            <div class="day-header">
                                <span class="day-badge">Ngày 2</span>
                                <span class="day-title">Hạ Long – Hà Nội (Ăn sáng, trưa)</span>
                            </div>
                            <div class="day-content">
                                <img class="day-thumb" src="<?= htmlspecialchars($galleryImages[2]) ?>" alt="Ngày 2">
                                <div class="timeline-steps">
                                    <div class="timeline-step">
                                        <span class="step-time">06:30</span>
                                        <span class="step-text">Đón bình minh trên vịnh (tự do chụp ảnh, tập thể dục).</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">07:30</span>
                                        <span class="step-text">Ăn sáng tại du thuyền.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">08:30</span>
                                        <span class="step-text">Tham quan đảo Titop, tắm biển và leo núi ngắm toàn cảnh vịnh.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">11:00</span>
                                        <span class="step-text">Trả phòng, dùng bữa trưa.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">12:30</span>
                                        <span class="step-text">Di chuyển về bến, lên xe về Hà Nội.</span>
                                    </div>
                                    <div class="timeline-step">
                                        <span class="step-time">17:30</span>
                                        <span class="step-text">Về đến Hà Nội, kết thúc hành trình.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- SECTION 3: DỊCH VỤ & TIỆN ÍCH -->
                <section class="section-block" id="dich-vu">
                    <h2 class="section-title">
                        <i class="bi bi-shield-check"></i> Dịch vụ &amp; tiện ích
                    </h2>
                    <div class="amenities-grid">
                        <div class="amenity-card">
                            <i class="bi bi-water"></i>
                            <span>Du thuyền 4 sao</span>
                        </div>
                        <div class="amenity-card">
                            <i class="bi bi-cup-hot"></i>
                            <span>Nhà hàng sang trọng</span>
                        </div>
                        <div class="amenity-card">
                            <i class="bi bi-person-check"></i>
                            <span>Hướng dẫn viên chuyên nghiệp</span>
                        </div>
                        <div class="amenity-card">
                            <i class="bi bi-shield-lock"></i>
                            <span>Bảo hiểm du lịch</span>
                        </div>
                        <div class="amenity-card">
                            <i class="bi bi-wifi"></i>
                            <span>Wifi miễn phí</span>
                        </div>
                    </div>
                </section>

                <!-- SECTION 4: ĐIỀU KHOẢN -->
                <section class="section-block" id="dieu-khoan">
                    <h2 class="section-title">
                        <i class="bi bi-file-earmark-text"></i> Điều khoản &amp; chính sách
                    </h2>
                    <div class="p-3 bg-light rounded-3 text-muted small">
                        <p class="mb-2"><strong>Chính sách hủy vé:</strong> Miễn phí hủy tour trước 3 ngày khởi hành. Hủy trong vòng 48h trước giờ đi thu phí 50%. Sau 24h tính 100% chi phí.</p>
                        <p class="mb-0"><strong>Lưu ý:</strong> Quý khách vui lòng mang theo Căn cước công dân hoặc Hộ chiếu khi tham gia tour. Giá vé trẻ em áp dụng cho các bé từ 2 - 11 tuổi.</p>
                    </div>
                </section>

                <!-- SECTION 5: ĐÁNH GIÁ TỪ KHÁCH HÀNG -->
                <section class="section-block" id="danh-gia">
                    <div class="reviews-header">
                        <h2 class="section-title mb-0">
                            <i class="bi bi-chat-quote"></i> Đánh giá từ khách hàng
                        </h2>
                        <div class="review-nav-btns">
                            <button class="btn-rev-circle" type="button" title="Trước"><i class="bi bi-chevron-left"></i></button>
                            <button class="btn-rev-circle" type="button" title="Sau"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>

                    <div class="reviews-layout">
                        <!-- Score Box Left -->
                        <div class="score-box">
                            <div class="score-num"><?= number_format($danhGiaAvgHero, 1) ?><span>/5</span></div>
                            <div class="stars-row">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div class="score-total">Dựa trên <?= (int)$danhGiaCountHero ?> đánh giá</div>
                        </div>

                        <!-- 3 Review Cards Right -->
                        <div class="reviews-cards-grid">
                            <?php foreach (array_slice($danhGiaTourList, 0, 3) as $dg): ?>
                                <?php
                                    $ten = trim((string)($dg['ho_ten'] ?? 'Khách hàng'));
                                    $init = mb_strtoupper(mb_substr($ten, 0, 1));
                                    $loc = $dg['dia_diem'] ?? 'Hà Nội';
                                    $dateStr = !empty($dg['ngay_danh_gia']) ? date('d/m/Y', strtotime($dg['ngay_danh_gia'])) : '12/08/2026';
                                    $diem = (int)($dg['diem'] ?? 5);
                                ?>
                                <div class="review-card-item">
                                    <div>
                                        <div class="rev-user-row">
                                            <div class="rev-avatar"><?= htmlspecialchars($init) ?></div>
                                            <div>
                                                <div class="rev-name"><?= htmlspecialchars($ten) ?></div>
                                                <div class="rev-loc"><?= htmlspecialchars($loc) ?> • <?= htmlspecialchars($dateStr) ?></div>
                                            </div>
                                        </div>
                                        <div class="rev-stars">
                                            <?php for($s=1; $s<=5; $s++): ?>
                                                <i class="bi bi-star<?= $s <= $diem ? '-fill' : '' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="rev-content">
                                            "<?= htmlspecialchars($dg['noi_dung'] ?? 'Tour tuyệt vời, phục vụ rất chu đáo.') ?>"
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

            </div>

            <!-- RIGHT COLUMN (col-lg-4): BẢNG GIÁ & ĐẶT TOUR NẰM NGANG HÀNG TRÊN CÙNG VỚI ẢNH -->
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    
                    <!-- BOOKING / PRICE CARD (NGANG HÀNG VỚI TOP GALLERY) -->
                    <div class="booking-card">
                        <div class="price-badge-row">
                            <span class="badge-discount">Giảm 20%</span>
                            <span class="price-original"><?= number_format($giaGoc) ?>đ</span>
                        </div>
                        <div class="price-main">
                            <span id="displayPrice"><?= number_format($giaTourHienThi) ?>đ</span> <span>/khách</span>
                        </div>
                        <div class="price-save-note">
                            Tiết kiệm <?= number_format($tietKiem) ?>đ so với giá gốc
                        </div>

                        <!-- Form chọn ngày & khách -->
                        <form id="bookingForm" method="get" action="index.php">
                            <input type="hidden" name="act" value="khachHang/thanhToanTour">
                            <input type="hidden" name="id" value="<?= (int)($tour['tour_id'] ?? $tour['id'] ?? 0) ?>">
                            <input type="hidden" name="so_nguoi_lon" id="inputNguoiLon" value="2">
                            <input type="hidden" name="so_tre_em" id="inputTreEm" value="0">
                            <input type="hidden" name="so_luong" id="inputTongSoLuong" value="2">

                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="bi bi-calendar3"></i> Ngày khởi hành
                                </label>
                                <select class="custom-select" name="lich_khoi_hanh_id" id="selectLichKhoiHanh">
                                    <?php if (!empty($lichKhoiHanhList)): ?>
                                        <?php foreach ($lichKhoiHanhList as $lk): ?>
                                            <option value="<?= $lk['id'] ?? '' ?>">
                                                <?= date('d/m/Y', strtotime($lk['ngay_khoi_hanh'])) ?> (<?= date('l', strtotime($lk['ngay_khoi_hanh'])) ?>) - Còn <?= $lk['so_cho_con_lai'] ?? 'nhiều' ?> chỗ
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="1"><?= $ngayKhoiHanhHienThi ?> (Thứ 7)</option>
                                        <option value="2"><?= date('d/m/Y', strtotime('+7 days')) ?> (Thứ 7)</option>
                                        <option value="3"><?= date('d/m/Y', strtotime('+14 days')) ?> (Thứ 7)</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group-custom">
                                <label class="form-label-custom">
                                    <i class="bi bi-people"></i> Số lượng khách
                                </label>
                                <div class="counter-box">
                                    <div>
                                        <div class="counter-label">Người lớn (≥ 12 tuổi)</div>
                                    </div>
                                    <div class="counter-controls">
                                        <button class="btn-counter" type="button" onclick="updateCount('adult', -1)">–</button>
                                        <span class="counter-value" id="valAdult">2</span>
                                        <button class="btn-counter" type="button" onclick="updateCount('adult', 1)">+</button>
                                    </div>
                                </div>
                                <div class="counter-box">
                                    <div>
                                        <div class="counter-label">Trẻ em (2 – 11 tuổi)</div>
                                    </div>
                                    <div class="counter-controls">
                                        <button class="btn-counter" type="button" onclick="updateCount('child', -1)">–</button>
                                        <span class="counter-value" id="valChild">0</span>
                                        <button class="btn-counter" type="button" onclick="updateCount('child', 1)">+</button>
                                    </div>
                                </div>
                            </div>

                            <?php if ($isBookingLockedBy48h): ?>
                                <button type="button" class="btn-book-now" disabled>
                                    <i class="bi bi-lock"></i> Đã khóa đặt vé (&lt; 48h)
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn-book-now">
                                    <i class="bi bi-send-fill"></i> Đặt ngay
                                </button>
                            <?php endif; ?>
                            <div class="deposit-note">(Chỉ cần thanh toán trước 30%)</div>
                        </form>

                        <div class="trust-items">
                            <div class="trust-item">
                                <i class="bi bi-clock-history"></i>
                                <div>
                                    <div class="trust-item-title">Miễn phí hủy tour</div>
                                    <div class="trust-item-desc">Hủy trước 3 ngày khởi hành</div>
                                </div>
                            </div>
                            <div class="trust-item">
                                <i class="bi bi-headset"></i>
                                <div>
                                    <div class="trust-item-title">Hỗ trợ 24/7</div>
                                    <div class="trust-item-desc">Tư vấn nhanh chóng, tận tâm</div>
                                </div>
                            </div>
                            <div class="trust-item">
                                <i class="bi bi-shield-check"></i>
                                <div>
                                    <div class="trust-item-title">Thanh toán an toàn</div>
                                    <div class="trust-item-desc">Nhiều phương thức thanh toán</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INFO SPEC SHEET CARD -->
                    <div class="info-spec-card">
                        <div class="spec-title">Thông tin tour</div>
                        <table class="spec-table">
                            <tr>
                                <td class="spec-label">Thời gian</td>
                                <td class="spec-value"><?= htmlspecialchars($thoiGianHienThi) ?></td>
                            </tr>
                            <tr>
                                <td class="spec-label">Phương tiện</td>
                                <td class="spec-value">Xe ô tô + Du thuyền</td>
                            </tr>
                            <tr>
                                <td class="spec-label">Khởi hành từ</td>
                                <td class="spec-value"><?= htmlspecialchars($khoiHanhHienThi) ?></td>
                            </tr>
                            <tr>
                                <td class="spec-label">Khách sạn</td>
                                <td class="spec-value">Du thuyền 4 sao</td>
                            </tr>
                            <tr>
                                <td class="spec-label">Giá người lớn</td>
                                <td class="spec-value"><?= number_format($giaTourHienThi) ?>đ</td>
                            </tr>
                            <tr>
                                <td class="spec-label">Giá trẻ em (2 – 11 tuổi)</td>
                                <td class="spec-value"><?= number_format($giaTreEm) ?>đ</td>
                            </tr>
                            <tr>
                                <td class="spec-label">Giá em bé (&lt; 2 tuổi)</td>
                                <td class="spec-value text-success">Miễn phí</td>
                            </tr>
                        </table>
                    </div>

                    <!-- PROMO BANNER -->
                    <div class="promo-card">
                        <span class="promo-badge">Ưu đãi đặc biệt</span>
                        <div class="promo-title">Đặt tour sớm – Nhận ngay quà tặng</div>
                        <div class="promo-desc">Giảm ngay 20% cho nhóm từ 4 khách trở lên và nhiều ưu đãi hấp dẫn khác.</div>
                        <a href="#bookingForm" class="btn-promo">Tìm hiểu thêm &rarr;</a>
                    </div>

                </div>
            </div>

        </div>

        <!-- 4. RELATED TOURS (Như Ảnh 2) -->
        <section class="related-section">
            <div class="related-header">
                <div class="related-title">Tour du lịch liên quan</div>
                <a href="index.php?act=khachHang/danhSachTour" class="link-view-all">Xem tất cả &rarr;</a>
            </div>

            <div class="related-grid">
                <?php foreach (array_slice($tourCungLoai, 0, 4) as $rel): ?>
                    <?php
                        $relId = $rel['tour_id'] ?? $rel['id'] ?? '';
                        $relName = $rel['ten_tour'] ?? 'Tour du lịch';
                        $relImg = !empty($rel['hinh_anh']) ? $rel['hinh_anh'] : $galleryImages[0];
                        $relPrice = (float)($rel['gia_tour'] ?? $rel['gia_co_ban'] ?? 2000000);
                        $relBadge = $rel['badge'] ?? '';
                    ?>
                    <a href="index.php?act=khachHang/chiTietTour&id=<?= urlencode((string)$relId) ?>" class="rel-card">
                        <div class="rel-img-wrap">
                            <img src="<?= htmlspecialchars($relImg) ?>" alt="<?= htmlspecialchars($relName) ?>" loading="lazy">
                            <?php if ($relBadge === 'Tour nổi bật'): ?>
                                <span class="rel-badge blue"><?= htmlspecialchars($relBadge) ?></span>
                            <?php elseif ($relBadge === 'Khuyến mãi'): ?>
                                <span class="rel-badge orange"><?= htmlspecialchars($relBadge) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="rel-body">
                            <div>
                                <div class="rel-tour-name"><?= htmlspecialchars($relName) ?></div>
                                <div class="rel-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span><?= htmlspecialchars($rel['rating'] ?? '4.8 (320 đánh giá)') ?></span>
                                </div>
                                <div class="rel-meta-row">
                                    <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($rel['noi_khoi_hanh'] ?? 'Hà Nội') ?></span>
                                    <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($rel['thoi_gian'] ?? '2 ngày 1 đêm') ?></span>
                                </div>
                            </div>
                            <div class="rel-footer">
                                <div class="rel-price"><?= number_format($relPrice) ?>đ<span>/khách</span></div>
                                <div class="rel-arrow"><i class="bi bi-chevron-right"></i></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- LIGHTBOX MODAL -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="lightboxImg" class="img-fluid rounded-3 modal-gallery-img" src="" alt="Xem ảnh lớn">
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="<?= BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- INTERACTIVE JAVASCRIPT -->
    <script>
        var adultPrice = <?= (float)$giaTourHienThi ?>;
        var childPrice = <?= (float)$giaTreEm ?>;
        var adultCount = 2;
        var childCount = 0;

        function updateCount(type, delta) {
            if (type === 'adult') {
                adultCount = Math.max(1, adultCount + delta);
                document.getElementById('valAdult').innerText = adultCount;
                document.getElementById('inputNguoiLon').value = adultCount;
            } else if (type === 'child') {
                childCount = Math.max(0, childCount + delta);
                document.getElementById('valChild').innerText = childCount;
                document.getElementById('inputTreEm').value = childCount;
            }
            
            var totalCount = adultCount + childCount;
            document.getElementById('inputTongSoLuong').value = totalCount;

            var totalPrice = (adultCount * adultPrice) + (childCount * childPrice);
            document.getElementById('displayPrice').innerText = new Intl.NumberFormat('vi-VN').format(totalPrice) + 'đ';
        }

        function scrollToTab(id, el) {
            document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
            el.classList.add('active');
            var target = document.getElementById(id);
            if (target) {
                var yOffset = -140;
                var y = target.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({top: y, behavior: 'smooth'});
            }
        }

        var galleryPhotos = <?= json_encode($galleryImages) ?>;
        function openLightbox(index) {
            var modalEl = document.getElementById('galleryModal');
            var imgEl = document.getElementById('lightboxImg');
            if (modalEl && imgEl) {
                imgEl.src = galleryPhotos[index] || galleryPhotos[0];
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    </script>
</body>
</html>
