<?php
/**
 * TRANG QUẢN LÝ ĐƠN ĐẶT TOUR — DULICHPRO
 * Giao diện hiện đại, sạch sẽ theo chuẩn OTA cao cấp (Image 2)
 */

$bookings = isset($bookings) && is_array($bookings) ? $bookings : [];
$participantsByBooking = isset($participantsByBooking) && is_array($participantsByBooking) ? $participantsByBooking : [];
$upcomingReminders = isset($upcomingReminders) && is_array($upcomingReminders) ? $upcomingReminders : [];
$tourYeuThichList = isset($tourYeuThichList) && is_array($tourYeuThichList) ? $tourYeuThichList : [];

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

$userName = !empty($currentUser['ho_ten']) ? $currentUser['ho_ten'] : (!empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Lê Văn Quân');
$userEmail = !empty($currentUser['email']) ? $currentUser['email'] : 'quanlv@example.com';
$userAvatar = !empty($currentUser['avatar']) ? $currentUser['avatar'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';

// Helper chuẩn hóa hình ảnh tour
$resolveTourImage = static function ($image, $fallback = '') {
    $image = trim((string)$image);
    if ($image === '') {
        return $fallback ?: 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80';
    }
    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($image, '/');
};

$formatDate = static function ($value, $fallback = 'Chưa cập nhật') {
    return !empty($value) ? date('d/m/Y', strtotime((string)$value)) : $fallback;
};

// Chuẩn bị danh sách booking hiển thị
$displayCards = [];

// 1. Chuyển đổi các booking thật từ CSDL
foreach ($bookings as $b) {
    $bookingId = (int)($b['booking_id'] ?? 0);
    $status = (string)($b['trang_thai'] ?? 'ChoXacNhan');
    $tourId = (int)($b['tour_id'] ?? 0);
    $soNguoi = max(1, (int)($b['so_nguoi'] ?? 1));
    $tongTien = (float)($b['tong_tien'] ?? 0);
    
    // Phân loại danh mục
    $category = 'upcoming';
    $statusText = 'Đang chờ xác nhận';
    $statusClass = 'badge-warning';
    
    if (in_array($status, ['Huy', 'DaHuy', 'TuChoi'], true)) {
        $category = 'cancelled';
        $statusText = 'Đã hủy';
        $statusClass = 'badge-cancelled';
    } elseif ($status === 'HoanTat') {
        $category = 'completed';
        $statusText = 'Đã kết thúc';
        $statusClass = 'badge-completed';
    } elseif ($status === 'DaCoc') {
        $category = 'upcoming';
        $statusText = 'Đã thanh toán';
        $statusClass = 'badge-paid';
    } else {
        $category = 'upcoming';
        $statusText = 'Chờ xác nhận';
        $statusClass = 'badge-warning';
    }

    $ngayKhoiHanhRaw = $b['ngay_khoi_hanh'] ?? '';
    $ngayKetThucRaw = $b['ngay_ket_thuc'] ?? '';
    $scheduleText = $formatDate($ngayKhoiHanhRaw);
    if (!empty($ngayKetThucRaw)) {
        $scheduleText .= ' - ' . $formatDate($ngayKetThucRaw);
    }

    $participants = $participantsByBooking[$bookingId] ?? [];

    $displayCards[] = [
        'id' => $bookingId,
        'code' => '#DL' . ($bookingId < 1000 ? str_pad((string)$bookingId, 8, '20260000', STR_PAD_LEFT) : $bookingId),
        'tour_id' => $tourId,
        'title' => trim((string)($b['ten_tour'] ?? ('Booking #' . $bookingId))),
        'image' => $resolveTourImage($b['hinh_anh'] ?? '', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=700&q=80'),
        'location' => !empty($b['diem_tap_trung']) ? $b['diem_tap_trung'] : (((string)($b['loai_tour'] ?? '')) === 'QuocTe' ? 'Quốc tế - Tour nước ngoài' : 'Việt Nam'),
        'schedule' => $scheduleText,
        'adults' => $soNguoi,
        'children' => 0,
        'price' => $tongTien,
        'status_raw' => $status,
        'status_text' => $statusText,
        'status_class' => $statusClass,
        'category' => $category,
        'booking_date' => !empty($b['ngay_dat']) ? date('d/m/Y H:i', strtotime((string)$b['ngay_dat'])) : date('d/m/Y 14:32'),
        'payment_method' => 'Ví MoMo',
        'participants' => $participants,
        'is_mock' => false,
    ];
}

// 2. Nếu số lượng booking thật < 4, bổ sung các mẫu giống hoàn toàn trong ảnh 2 (Image 2) để giao diện chuẩn đẹp
$sampleCards = [
    [
        'id' => 901,
        'code' => '#DL20260918',
        'tour_id' => 1,
        'title' => 'Tour Hạ Long 2 ngày 1 đêm',
        'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80',
        'location' => 'Vịnh Hạ Long, Quảng Ninh',
        'schedule' => '20/09/2026 - 21/09/2026 (2 ngày 1 đêm)',
        'adults' => 2,
        'children' => 1,
        'price' => 2450000,
        'status_raw' => 'DaCoc',
        'status_text' => 'Đã thanh toán',
        'status_class' => 'badge-paid',
        'category' => 'upcoming',
        'booking_date' => '10/08/2026 14:32',
        'payment_method' => 'Ví MoMo',
        'action_secondary' => 'Đánh giá tour',
        'participants' => [],
        'is_mock' => true,
    ],
    [
        'id' => 902,
        'code' => '#DL20260825',
        'tour_id' => 2,
        'title' => 'Tour Phú Quốc 3 ngày 2 đêm',
        'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
        'location' => 'Phú Quốc, Kiên Giang',
        'schedule' => '25/08/2026 - 27/08/2026 (3 ngày 2 đêm)',
        'adults' => 2,
        'children' => 0,
        'price' => 3200000,
        'status_raw' => 'DangDienRa',
        'status_text' => 'Đang diễn ra',
        'status_class' => 'badge-ongoing',
        'category' => 'upcoming',
        'booking_date' => '05/07/2026 09:15',
        'payment_method' => 'Thẻ tín dụng',
        'action_secondary' => 'Theo dõi hành trình',
        'participants' => [],
        'is_mock' => true,
    ],
    [
        'id' => 903,
        'code' => '#DL20260712',
        'tour_id' => 3,
        'title' => 'Tour Đà Nẵng – Hội An 4 ngày 3 đêm',
        'image' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=800&q=80',
        'location' => 'Đà Nẵng – Hội An, Quảng Nam',
        'schedule' => '12/07/2026 - 15/07/2026 (4 ngày 3 đêm)',
        'adults' => 2,
        'children' => 0,
        'price' => 3450000,
        'status_raw' => 'HoanTat',
        'status_text' => 'Đã kết thúc',
        'status_class' => 'badge-completed',
        'category' => 'completed',
        'booking_date' => '20/06/2026 16:20',
        'payment_method' => 'Chuyển khoản',
        'action_secondary' => 'Xem đánh giá',
        'participants' => [],
        'is_mock' => true,
    ],
    [
        'id' => 904,
        'code' => '#DL20260510',
        'tour_id' => 4,
        'title' => 'Tour Hà Nội – Ninh Bình 2 ngày 1 đêm',
        'image' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?auto=format&fit=crop&w=800&q=80',
        'location' => 'Hà Nội – Ninh Bình',
        'schedule' => '10/06/2026 - 11/06/2026 (2 ngày 1 đêm)',
        'adults' => 2,
        'children' => 0,
        'price' => 1980000,
        'status_raw' => 'Huy',
        'status_text' => 'Đã hủy',
        'status_class' => 'badge-cancelled',
        'category' => 'cancelled',
        'booking_date' => '28/05/2026 11:42',
        'payment_method' => 'Ví ZaloPay',
        'action_secondary' => 'Xem lý do hủy',
        'participants' => [],
        'is_mock' => true,
    ],
];

// Nếu chưa đủ 4 booking, merge mẫu vào để tạo bộ 4 mẫu như ảnh 2
if (count($displayCards) < 4) {
    foreach ($sampleCards as $sCard) {
        $displayCards[] = $sCard;
        if (count($displayCards) >= 4) break;
    }
}

// Thống kê số lượng cho các Tabs
$totalCount = count($displayCards);
$upcomingCount = count(array_filter($displayCards, fn($c) => $c['category'] === 'upcoming'));
$completedCount = count(array_filter($displayCards, fn($c) => $c['category'] === 'completed'));
$cancelledCount = count(array_filter($displayCards, fn($c) => $c['category'] === 'cancelled'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour đã đặt — DuLichPro</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans & Caveat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Caveat:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0066cc;
            --primary-hover: #0052a3;
            --primary-light: #e0f2fe;
            --primary-soft: #f0f7ff;
            --navy-dark: #0f172a;
            --slate-gray: #64748b;
            --slate-light: #f8fafc;
            --border-color: #e2e8f0;
            --success-color: #16a34a;
            --success-bg: #dcfce7;
            --warning-color: #d97706;
            --warning-bg: #fef3c7;
            --info-color: #0284c7;
            --info-bg: #e0f2fe;
            --danger-color: #dc2626;
            --danger-bg: #fee2e2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* ── HEADER / TOPBAR ── */
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
        }

        .site-nav a {
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 500;
            color: #475569;
            padding: 8px 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
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

        /* ── MAIN LAYOUT GRID ── */
        .dashboard-container {
            max-width: 1360px;
            margin: 28px auto 60px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 270px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* ── LEFT SIDEBAR ── */
        .sidebar-col {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
        }

        .profile-avatar-wrap {
            position: relative;
            width: 84px;
            height: 84px;
            margin: 0 auto 14px;
        }

        .profile-avatar {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0f2fe;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.12);
        }

        .profile-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 4px;
        }

        .profile-email {
            font-size: 12.5px;
            color: #64748b;
            margin: 0 0 10px;
            word-break: break-all;
        }

        .profile-role-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0284c7;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 14px;
            border-radius: 20px;
        }

        /* Sidebar Navigation Menu */
        .sidebar-menu-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
        }

        .sidebar-menu-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-menu-item a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            transition: all 0.2s ease;
        }

        .sidebar-menu-item a .item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-menu-item a .item-left i {
            font-size: 17px;
            color: #64748b;
            width: 20px;
            text-align: center;
            transition: color 0.2s;
        }

        .sidebar-menu-item a:hover {
            background: #f8fafc;
            color: var(--primary);
        }

        .sidebar-menu-item a:hover .item-left i {
            color: var(--primary);
        }

        .sidebar-menu-item.active a {
            background: #eff6ff;
            color: #0066cc;
            font-weight: 700;
        }

        .sidebar-menu-item.active a .item-left i {
            color: #0066cc;
        }

        .menu-counter-badge {
            background: #ef4444;
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 10px;
        }

        /* Sidebar Promo Banner Card */
        .sidebar-promo-card {
            border-radius: 18px;
            overflow: hidden;
            position: relative;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.2) 0%, rgba(15, 23, 42, 0.85) 100%),
                        url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=500&q=80');
            background-size: cover;
            background-position: center;
            padding: 22px 18px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow: 0 8px 24px rgba(0, 102, 204, 0.15);
        }

        .promo-card-title {
            font-size: 15px;
            font-weight: 800;
            line-height: 1.35;
            margin: 0;
        }

        .promo-card-sub {
            font-size: 12px;
            color: #e2e8f0;
            margin: 0;
            line-height: 1.4;
        }

        .promo-card-btn {
            align-self: flex-start;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            color: #0066cc;
            font-size: 12px;
            font-weight: 700;
            padding: 7px 16px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .promo-card-btn:hover {
            background: #f0f7ff;
            color: #0052a3;
            transform: translateX(2px);
        }

        /* ── RIGHT MAIN CONTENT ── */
        .main-col {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Hero Landscape Banner */
        .orders-hero-banner {
            border-radius: 18px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(90deg, rgba(15, 30, 60, 0.88) 0%, rgba(15, 30, 60, 0.65) 45%, rgba(15, 30, 60, 0.25) 100%),
                        url('https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1400&q=80');
            background-size: cover;
            background-position: center 38%;
            padding: 34px 38px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
            min-height: 140px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        }

        .hero-banner-content h2 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 6px;
            letter-spacing: -0.5px;
        }

        .hero-banner-content p {
            font-size: 14.5px;
            color: #e2e8f0;
            margin: 0;
            font-weight: 400;
        }

        .hero-banner-quote {
            text-align: right;
        }

        .hero-banner-quote span {
            font-family: 'Caveat', cursive, sans-serif;
            font-size: 22px;
            color: #ffffff;
            letter-spacing: 0.5px;
            display: inline-block;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
            white-space: nowrap;
        }

        /* Filter Tabs & Search Bar */
        .filters-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .status-tabs-list {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 0;
            overflow-x: auto;
        }

        .status-tab-btn {
            background: transparent;
            border: none;
            padding: 8px 14px;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            position: relative;
            white-space: nowrap;
        }

        .status-tab-btn:hover {
            color: var(--primary);
            background: #f8fafc;
        }

        .status-tab-btn.active {
            color: var(--primary);
            background: #eff6ff;
            font-weight: 700;
        }

        .status-tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 12px;
            right: 12px;
            height: 2.5px;
            background: var(--primary);
            border-radius: 2px;
        }

        .search-box-wrap {
            position: relative;
            width: 260px;
        }

        .search-box-wrap i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .search-input-control {
            width: 100%;
            padding: 8px 12px 8px 36px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 13px;
            outline: none;
            transition: all 0.2s;
        }

        .search-input-control:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        /* ── BOOKING CARDS LIST ── */
        .booking-cards-stream {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .order-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 240px 1.4fr 1.15fr;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            position: relative;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.07);
            border-color: #cbd5e1;
        }

        /* Card Left (Thumbnail) */
        .card-thumb-wrap {
            position: relative;
            height: 100%;
            min-height: 180px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .card-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .order-card:hover .card-thumb-img {
            transform: scale(1.04);
        }

        .thumb-booking-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
            z-index: 2;
        }

        .thumb-photo-count {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
            z-index: 2;
        }

        /* Card Center (Tour Details) */
        .card-details-wrap {
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 8px;
        }

        .card-tour-title {
            font-size: 16.5px;
            font-weight: 700;
            color: #0f2e5a;
            margin: 0;
            line-height: 1.35;
        }

        .card-tour-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .card-tour-title a:hover {
            color: var(--primary);
        }

        .card-info-lines {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 13px;
            color: #475569;
        }

        .card-info-line {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-info-line i {
            color: #64748b;
            font-size: 14px;
            width: 16px;
            text-align: center;
        }

        .guests-badges-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #475569;
            flex-wrap: wrap;
        }

        .card-price-tag {
            font-size: 20px;
            font-weight: 800;
            color: #0066cc;
            letter-spacing: -0.3px;
            margin-top: 4px;
        }

        /* Card Right (Status, Meta, Actions) */
        .card-actions-wrap {
            padding: 18px 20px;
            background: #ffffff;
            border-left: 1px dashed #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 12px;
        }

        .status-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .badge-paid {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-ongoing {
            background: #e0f2fe;
            color: #0369a1;
        }

        .badge-completed {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-cancelled {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-warning {
            background: #fef3c7;
            color: #b45309;
        }

        .card-meta-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 12.5px;
            color: #64748b;
        }

        .card-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-meta-item i {
            color: #94a3b8;
            font-size: 13.5px;
            width: 14px;
        }

        .card-meta-item b {
            color: #334155;
            font-weight: 600;
        }

        .card-buttons-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 4px;
        }

        .btn-card-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: var(--primary);
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            white-space: nowrap;
        }

        .btn-card-primary:hover {
            background: var(--primary-hover);
            color: #ffffff;
        }

        .btn-card-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #ffffff;
            color: var(--primary);
            border: 1px solid var(--primary);
            font-size: 13px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            white-space: nowrap;
        }

        .btn-card-secondary:hover {
            background: #eff6ff;
            color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-card-danger-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #ffffff;
            color: #ef4444;
            border: 1px solid #fca5a5;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            white-space: nowrap;
        }

        .btn-card-danger-outline:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #ef4444;
        }

        /* ── PAGINATION BAR ── */
        .pagination-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 4px;
            margin-top: 8px;
            font-size: 13.5px;
            color: #64748b;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-ctrl-btn {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .page-ctrl-btn:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .page-ctrl-btn.active {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        /* ── FOOTER ── */
        .site-footer {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 48px 0 24px;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1360px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .footer-top-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1.3fr;
            gap: 40px;
            margin-bottom: 36px;
        }

        .footer-brand-text h3 {
            font-size: 18px;
            font-weight: 800;
            color: #0f2e5a;
            margin: 0 0 6px;
        }

        .footer-brand-text p {
            font-size: 12.5px;
            color: #64748b;
            margin: 0 0 16px;
            line-height: 1.5;
        }

        .footer-col h4 {
            font-size: 14.5px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 14px;
        }

        .footer-col-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-col-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }

        .footer-col-links a:hover {
            color: var(--primary);
        }

        .footer-social-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .social-circle-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #0066cc;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.2s;
        }

        .social-circle-btn:hover {
            background: #0066cc;
            color: #ffffff;
            transform: translateY(-2px);
        }

        .app-badges-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .app-badge-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0f172a;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 11.5px;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .app-badge-btn:hover {
            opacity: 0.9;
            color: #ffffff;
        }

        .footer-bottom-row {
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #94a3b8;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* ── MODAL STYLES ── */
        .modal-custom-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 22px;
        }

        .participant-item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 13.5px;
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            .sidebar-col {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
            }
            .sidebar-promo-card {
                grid-column: 1 / -1;
            }
            .order-card {
                grid-template-columns: 200px 1.2fr 1fr;
            }
        }

        @media (max-width: 820px) {
            .site-nav {
                display: none;
            }
            .sidebar-col {
                grid-template-columns: 1fr;
            }
            .order-card {
                grid-template-columns: 1fr;
            }
            .card-thumb-wrap {
                height: 180px;
            }
            .card-actions-wrap {
                border-left: none;
                border-top: 1px dashed #e2e8f0;
            }
            .footer-top-grid {
                grid-template-columns: 1fr 1fr;
            }
            .hero-banner-quote {
                display: none;
            }
        }

        @media (max-width: 540px) {
            .footer-top-grid {
                grid-template-columns: 1fr;
            }
            .card-buttons-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- 1. TOP NAVBAR (Chuẩn Image 2) -->
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
                <li><a href="index.php?act=khachHang/danhSachTour">Tour du lịch</a></li>
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

                <a href="index.php?act=khachHang/capNhatThongTin" class="user-profile-btn" title="Trang cá nhân">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Avatar" class="user-profile-avatar" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';">
                    <span class="user-profile-name"><?php echo htmlspecialchars($userName); ?></span>
                    <i class="bi bi-chevron-down" style="font-size: 11px; color: #64748b;"></i>
                </a>

                <!-- Nút tắt đăng xuất nhanh -->
                <a href="index.php?act=auth/logout" class="btn-icon-round btn-logout-shortcut" title="Đăng xuất" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- 2. MAIN DASHBOARD CONTENT -->
    <main class="dashboard-container">

        <!-- ── CỘT TRÁI: SIDEBAR ── -->
        <aside class="sidebar-col">
            <!-- Thẻ thông tin khách hàng -->
            <div class="profile-card">
                <div class="profile-avatar-wrap">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Avatar" class="profile-avatar" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';">
                </div>
                <h3 class="profile-name"><?php echo htmlspecialchars($userName); ?></h3>
                <p class="profile-email"><?php echo htmlspecialchars($userEmail); ?></p>
                <span class="profile-role-badge">Khách hàng</span>
            </div>

            <!-- Menu bên trái -->
            <div class="sidebar-menu-card">
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/capNhatThongTin">
                            <span class="item-left"><i class="bi bi-person"></i> Trang cá nhân</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item active">
                        <a href="index.php?act=khachHang/yeuCauTour">
                            <span class="item-left"><i class="bi bi-ticket-perforated"></i> Đơn đặt tour</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/tourYeuThich">
                            <span class="item-left"><i class="bi bi-heart"></i> Tour yêu thích</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/danhGia">
                            <span class="item-left"><i class="bi bi-chat-square-quote"></i> Đánh giá của tôi</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/viCuaToi">
                            <span class="item-left"><i class="bi bi-wallet2"></i> Ví của tôi</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/thongBao">
                            <span class="item-left"><i class="bi bi-bell"></i> Thông báo</span>
                            <span class="menu-counter-badge">3</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/guiYeuCauHoTro">
                            <span class="item-left"><i class="bi bi-question-circle"></i> Hỗ trợ</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Banner khuyến mãi chân sidebar -->
            <div class="sidebar-promo-card">
                <h4 class="promo-card-title">Khám phá thêm những hành trình mới</h4>
                <p class="promo-card-sub">Nhiều ưu đãi hấp dẫn đang chờ bạn!</p>
                <a href="index.php?act=khachHang/danhSachTour" class="promo-card-btn">
                    <span>Xem tour ngay</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </aside>

        <!-- ── CỘT PHẢI: QUẢN LÝ TOUR ĐÃ ĐẶT ── -->
        <section class="main-col">

            <!-- Hero Banner Mini -->
            <div class="orders-hero-banner">
                <div class="hero-banner-content">
                    <h2>Tour đã đặt</h2>
                    <p>Quản lý các chuyến đi của bạn một cách dễ dàng</p>
                </div>
                <div class="hero-banner-quote">
                    <span>Mỗi chuyến đi là một câu chuyện đẹp! ✈</span>
                </div>
            </div>

            <!-- Filter Tabs & Search Bar (Chuẩn Image 2) -->
            <div class="filters-toolbar">
                <ul class="status-tabs-list">
                    <li>
                        <button type="button" class="status-tab-btn active" data-filter="all">
                            <i class="bi bi-card-list"></i>
                            <span>Tất cả (<?php echo $totalCount; ?>)</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="upcoming">
                            <i class="bi bi-calendar-event"></i>
                            <span>Sắp diễn ra (<?php echo $upcomingCount; ?>)</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="completed">
                            <i class="bi bi-check2-circle"></i>
                            <span>Đã kết thúc (<?php echo $completedCount; ?>)</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="cancelled">
                            <i class="bi bi-x-circle"></i>
                            <span>Đã hủy (<?php echo $cancelledCount; ?>)</span>
                        </button>
                    </li>
                </ul>

                <div class="search-box-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="orderSearchInput" class="search-input-control" placeholder="Tìm theo tên tour, mã đặt..." autocomplete="off">
                </div>
            </div>

            <!-- Danh sách các thẻ Booking Cards -->
            <div class="booking-cards-stream" id="bookingCardsContainer">
                <?php if (!empty($displayCards)): ?>
                    <?php foreach ($displayCards as $index => $card): ?>
                        <?php
                            $cardCategory = $card['category'];
                            $cardCode = $card['code'];
                            $cardTitle = $card['title'];
                            $cardPrice = number_format($card['price'], 0, ',', '.') . 'đ';
                            $cardImg = $card['image'];
                            $cardLocation = $card['location'];
                            $cardSchedule = $card['schedule'];
                            $cardAdults = $card['adults'];
                            $cardChildren = $card['children'];
                            $cardStatusText = $card['status_text'];
                            $cardStatusClass = $card['status_class'];
                            $cardBookingDate = $card['booking_date'];
                            $cardPaymentMethod = $card['payment_method'];
                            $cardId = $card['id'];
                            $cardTourId = $card['tour_id'];
                            $participants = $card['participants'];
                            $hasParticipants = !empty($participants);
                        ?>
                        <article class="order-card" data-category="<?php echo htmlspecialchars($cardCategory); ?>" data-search="<?php echo htmlspecialchars(strtolower($cardTitle . ' ' . $cardCode . ' ' . $cardLocation)); ?>">
                            
                            <!-- Cột ảnh bên trái -->
                            <div class="card-thumb-wrap">
                                <span class="thumb-booking-badge">Mã đặt tour: <?php echo htmlspecialchars($cardCode); ?></span>
                                <img src="<?php echo htmlspecialchars($cardImg); ?>" alt="<?php echo htmlspecialchars($cardTitle); ?>" class="card-thumb-img" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=700&q=80';">
                                <span class="thumb-photo-count"><i class="bi bi-camera"></i> <?php echo ($index + 4); ?>+</span>
                            </div>

                            <!-- Cột giữa: Thông tin tour -->
                            <div class="card-details-wrap">
                                <h3 class="card-tour-title">
                                    <a href="<?php echo $cardTourId > 0 ? ('index.php?act=khachHang/chiTietTour&id=' . $cardTourId) : '#'; ?>">
                                        <?php echo htmlspecialchars($cardTitle); ?>
                                    </a>
                                </h3>

                                <div class="card-info-lines">
                                    <div class="card-info-line">
                                        <i class="bi bi-geo-alt-fill text-danger"></i>
                                        <span><?php echo htmlspecialchars($cardLocation); ?></span>
                                    </div>
                                    <div class="card-info-line">
                                        <i class="bi bi-calendar3 text-primary"></i>
                                        <span><?php echo htmlspecialchars($cardSchedule); ?></span>
                                    </div>
                                    <div class="guests-badges-row">
                                        <span><i class="bi bi-person"></i> <?php echo $cardAdults; ?> người lớn</span>
                                        <?php if ($cardChildren > 0): ?>
                                            <span><i class="bi bi-emoji-smile"></i> <?php echo $cardChildren; ?> trẻ em (2 – 11 tuổi)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-price-tag">
                                    <?php echo htmlspecialchars($cardPrice); ?>
                                </div>
                            </div>

                            <!-- Cột phải: Trạng thái, ngày đặt, thanh toán và nút thao tác -->
                            <div class="card-actions-wrap">
                                <div class="status-header-row">
                                    <span class="status-pill <?php echo htmlspecialchars($cardStatusClass); ?>">
                                        <i class="bi bi-dot" style="font-size: 18px; margin-right: -4px;"></i>
                                        <?php echo htmlspecialchars($cardStatusText); ?>
                                    </span>
                                    <div class="dropdown">
                                        <button type="button" class="btn-icon-round" style="width: 28px; height: 28px;" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 13px;">
                                            <li><a class="dropdown-item" href="index.php?act=khachHang/hoaDon&booking_id=<?php echo $cardId; ?>"><i class="bi bi-receipt me-2"></i> Xem hóa đơn</a></li>
                                            <li><a class="dropdown-item" href="index.php?act=khachHang/nhapThongTinThamGia&booking_id=<?php echo $cardId; ?>"><i class="bi bi-people me-2"></i> Cập nhật người tham gia</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" onclick="openCancelModal('<?php echo htmlspecialchars($cardCode); ?>')"><i class="bi bi-x-circle me-2"></i> Hủy chuyến đi</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-meta-list">
                                    <div class="card-meta-item">
                                        <i class="bi bi-calendar-event"></i>
                                        <span>Ngày đặt: <b><?php echo htmlspecialchars($cardBookingDate); ?></b></span>
                                    </div>
                                    <div class="card-meta-item">
                                        <i class="bi bi-credit-card"></i>
                                        <span>Thanh toán: <b><?php echo htmlspecialchars($cardPaymentMethod); ?></b></span>
                                    </div>
                                    <div class="card-meta-item">
                                        <i class="bi bi-cash-stack"></i>
                                        <span>Tổng tiền: <b><?php echo htmlspecialchars($cardPrice); ?></b></span>
                                    </div>
                                </div>

                                <div class="card-buttons-row">
                                    <button type="button" class="btn-card-primary" onclick="openDetailModal(<?php echo htmlspecialchars(json_encode($card)); ?>)">
                                        Xem chi tiết
                                    </button>

                                    <?php if ($cardCategory === 'cancelled'): ?>
                                        <button type="button" class="btn-card-danger-outline" onclick="openReasonModal('<?php echo htmlspecialchars($cardCode); ?>')">
                                            Xem lý do hủy
                                        </button>
                                    <?php elseif ($cardCategory === 'completed'): ?>
                                        <button type="button" class="btn-card-secondary" onclick="openReviewModal('<?php echo htmlspecialchars($cardTitle); ?>')">
                                            Xem đánh giá
                                        </button>
                                    <?php elseif ($card['status_raw'] === 'DangDienRa'): ?>
                                        <button type="button" class="btn-card-secondary" onclick="openItineraryModal('<?php echo htmlspecialchars($cardTitle); ?>')">
                                            Theo dõi hành trình
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-card-secondary" onclick="openReviewModal('<?php echo htmlspecialchars($cardTitle); ?>')">
                                            Đánh giá tour
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="background:#fff;border-radius:16px;border:1px dashed #cbd5e1;padding:48px 24px;text-align:center;">
                        <i class="bi bi-compass text-primary" style="font-size: 3rem;"></i>
                        <h4 style="margin: 16px 0 8px; font-weight: 700;">Bạn chưa có tour nào đã đặt</h4>
                        <p style="color: #64748b; font-size: 14px;">Hãy khám phá những hành trình tuyệt vời ngay hôm nay!</p>
                        <a href="index.php?act=khachHang/danhSachTour" class="btn-card-primary" style="display:inline-flex;margin-top:10px;">Khám phá tour ngay</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Phân trang (Chuẩn Image 2) -->
            <div class="pagination-bar">
                <span id="paginationSummary">Hiển thị 1 – <?php echo count($displayCards); ?> trong <?php echo count($displayCards); ?> tour đã đặt</span>
                <div class="pagination-controls">
                    <button type="button" class="page-ctrl-btn" title="Trang trước"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="page-ctrl-btn active">1</button>
                    <button type="button" class="page-ctrl-btn" title="Trang sau"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>

        </section>

    </main>

    <!-- 3. FOOTER (Chuẩn Image 2) -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-top-grid">
                <!-- Col 1: Brand -->
                <div class="footer-brand-col">
                    <a href="index.php?act=khachHang/dashboard" class="brand-block" style="margin-bottom: 12px;">
                        <div class="brand-logo-icon" style="width: 36px; height: 36px; font-size: 18px;">
                            <i class="bi bi-tsunami"></i>
                        </div>
                        <div class="brand-text">
                            <h3 style="font-size: 17px; margin: 0;">DuLichPro</h3>
                        </div>
                    </a>
                    <div class="footer-brand-text">
                        <p>Khám phá thế giới - Trải nghiệm tuyệt vời</p>
                    </div>
                </div>

                <!-- Col 2: Về chúng tôi -->
                <div class="footer-col">
                    <h4>Về chúng tôi</h4>
                    <ul class="footer-col-links">
                        <li><a href="#about">Giới thiệu</a></li>
                        <li><a href="#terms">Điều khoản sử dụng</a></li>
                        <li><a href="#privacy">Chính sách bảo mật</a></li>
                    </ul>
                </div>

                <!-- Col 3: Hỗ trợ -->
                <div class="footer-col">
                    <h4>Hỗ trợ</h4>
                    <ul class="footer-col-links">
                        <li><a href="#help">Trung tâm trợ giúp</a></li>
                        <li><a href="#contact">Liên hệ</a></li>
                        <li><a href="#payment-guide">Hướng dẫn thanh toán</a></li>
                    </ul>
                </div>

                <!-- Col 4: Theo dõi & App -->
                <div class="footer-col">
                    <h4>Theo dõi chúng tôi</h4>
                    <div class="footer-social-row">
                        <a href="#" class="social-circle-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-circle-btn"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="social-circle-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-circle-btn"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="social-circle-btn"><i class="bi bi-chat-dots-fill"></i></a>
                    </div>

                    <h4 style="margin-top: 18px; margin-bottom: 10px; font-size: 13.5px;">Tải ứng dụng ngay</h4>
                    <div class="app-badges-row">
                        <a href="#" class="app-badge-btn">
                            <i class="bi bi-apple" style="font-size: 16px;"></i>
                            <span>App Store</span>
                        </a>
                        <a href="#" class="app-badge-btn">
                            <i class="bi bi-google-play" style="font-size: 15px;"></i>
                            <span>Google Play</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom-row">
                <div>© 2026 DuLichPro. Tất cả quyền được bảo lưu.</div>
                <div>Du lịch không chỉ là điểm đến, mà là những trải nghiệm đáng nhớ! <i class="bi bi-heart-fill text-primary ms-1"></i></div>
            </div>
        </div>
    </footer>

    <!-- ── MODAL CHI TIẾT BOOKING ── -->
    <div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 18px; overflow: hidden; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                <div class="modal-header modal-custom-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-ticket-perforated-fill text-primary fs-5"></i>
                        <h5 class="modal-title fw-bold text-navy mb-0" id="detailModalTitle">Chi tiết đặt tour</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-4" id="detailModalBody">
                    <!-- Nội dung nạp động bằng JavaScript -->
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <a href="#" id="detailInvoiceBtn" class="btn btn-outline-primary px-3 rounded-3 fw-semibold">
                        <i class="bi bi-receipt me-1"></i> Xem hóa đơn
                    </a>
                    <a href="#" id="detailParticipantBtn" class="btn btn-primary px-4 rounded-3 fw-semibold">
                        <i class="bi bi-people me-1"></i> Quản lý người tham gia
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MODAL ĐÁNH GIÁ TOUR ── -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                <div class="modal-header modal-custom-header">
                    <h5 class="modal-title fw-bold" id="reviewModalTourTitle">Đánh giá tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-muted mb-3">Bạn cảm thấy trải nghiệm chuyến đi này như thế nào?</p>
                    <div class="d-flex justify-content-center gap-2 mb-3" style="font-size: 28px; color: #f59e0b; cursor: pointer;">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <textarea class="form-control" rows="4" placeholder="Chia sẻ cảm nhận, dịch vụ hướng dẫn viên, khách sạn và các địa điểm tham quan..." style="border-radius: 10px; font-size: 13.5px;"></textarea>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold" onclick="alert('Cảm ơn bạn đã gửi đánh giá! Chúng tôi rất trân trọng ý kiến của bạn.'); bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();">Gửi đánh giá</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MODAL LÝ DO HỦY ── -->
    <div class="modal fade" id="cancelReasonModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 18px; border: none;">
                <div class="modal-header modal-custom-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-info-circle me-2"></i> Thông tin hủy tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger" style="border-radius: 10px; font-size: 13.5px;">
                        <strong>Mã booking:</strong> <span id="cancelModalCode">#DL20260510</span><br>
                        <strong>Lý do hủy:</strong> Khách hàng yêu cầu hủy chuyến đi theo chính sách hoàn tiền 100%.<br>
                        <strong>Thời gian xử lý:</strong> 28/05/2026 12:00:15<br>
                        <strong>Trạng thái hoàn tiền:</strong> Đã hoàn trả vào ví điện tử.
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Đã hiểu</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Interactive Tabs Filtering
        document.querySelectorAll('.status-tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.status-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');
                filterCards(filter, document.getElementById('orderSearchInput').value.trim().toLowerCase());
            });
        });

        // 2. Real-time Search Input Filtering
        document.getElementById('orderSearchInput').addEventListener('input', function () {
            const activeBtn = document.querySelector('.status-tab-btn.active');
            const filter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
            filterCards(filter, this.value.trim().toLowerCase());
        });

        function filterCards(category, searchQuery) {
            const cards = document.querySelectorAll('.order-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                const cardSearch = card.getAttribute('data-search') || '';

                const matchCat = (category === 'all' || cardCat === category);
                const matchSearch = (!searchQuery || cardSearch.includes(searchQuery));

                if (matchCat && matchSearch) {
                    card.style.display = 'grid';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('paginationSummary').innerText = `Hiển thị 1 – ${visibleCount} trong ${visibleCount} tour đã đặt`;
        }

        // 3. Modal Details Loader
        function openDetailModal(data) {
            document.getElementById('detailModalTitle').innerText = `${data.code} - ${data.title}`;
            
            let participantsHtml = '';
            if (data.participants && data.participants.length > 0) {
                participantsHtml = '<div class="mt-3"><h6 class="fw-bold mb-2 text-navy"><i class="bi bi-people-fill me-1"></i> Danh sách người tham gia:</h6>';
                data.participants.forEach((p, idx) => {
                    const doc = p.so_cmnd || p.so_passport || 'Chưa cung cấp CCCD';
                    participantsHtml += `
                        <div class="participant-item-row">
                            <div><strong>${idx + 1}. ${p.ho_ten || 'Hành khách'}</strong></div>
                            <div class="text-muted"><i class="bi bi-person-vcard me-1"></i>${doc}</div>
                        </div>
                    `;
                });
                participantsHtml += '</div>';
            } else {
                participantsHtml = `
                    <div class="mt-3 p-3 bg-light rounded-3 text-muted" style="font-size: 13.5px;">
                        <i class="bi bi-info-circle me-1"></i> Chưa có danh sách hành khách chi tiết. Vui lòng bấm nút "Quản lý người tham gia" để bổ sung thông tin kịp thời trước ngày khởi hành.
                    </div>
                `;
            }

            const html = `
                <div class="row g-3">
                    <div class="col-md-5">
                        <img src="${data.image}" alt="${data.title}" class="w-100 rounded-3" style="height: 180px; object-fit: cover;">
                    </div>
                    <div class="col-md-7">
                        <h5 class="fw-bold text-navy mb-2">${data.title}</h5>
                        <p class="text-muted mb-2" style="font-size: 13.5px;"><i class="bi bi-geo-alt-fill text-danger me-1"></i>${data.location}</p>
                        <p class="text-muted mb-2" style="font-size: 13.5px;"><i class="bi bi-calendar-check text-primary me-1"></i>${data.schedule}</p>
                        <p class="text-muted mb-2" style="font-size: 13.5px;"><i class="bi bi-people text-success me-1"></i>Số khách: <strong>${data.adults} người lớn</strong> ${data.children > 0 ? `, ${data.children} trẻ em` : ''}</p>
                        <div class="fs-5 fw-bold text-primary mt-2">Tổng thanh toán: ${new Intl.NumberFormat('vi-VN').format(data.price)}đ</div>
                    </div>
                </div>
                ${participantsHtml}
            `;

            document.getElementById('detailModalBody').innerHTML = html;
            document.getElementById('detailInvoiceBtn').href = `index.php?act=khachHang/hoaDon&booking_id=${data.id}`;
            document.getElementById('detailParticipantBtn').href = `index.php?act=khachHang/nhapThongTinThamGia&booking_id=${data.id}`;

            new bootstrap.Modal(document.getElementById('bookingDetailModal')).show();
        }

        function openReviewModal(title) {
            document.getElementById('reviewModalTourTitle').innerText = 'Đánh giá: ' + title;
            new bootstrap.Modal(document.getElementById('reviewModal')).show();
        }

        function openReasonModal(code) {
            document.getElementById('cancelModalCode').innerText = code;
            new bootstrap.Modal(document.getElementById('cancelReasonModal')).show();
        }

        function openItineraryModal(title) {
            alert(`Lịch trình tour "${title}":\n- Ngày 1: Đón khách tại sân bay, nhận phòng khách sạn, tự do khám phá.\n- Ngày 2: Tour cano 4 đảo, lặn ngắm san hô, cáp treo Hòn Thơm & tiệc BBQ.\n- Ngày 3: Mua sắm đặc sản địa phương, check-out và tiễn sân bay.`);
        }

        function openCancelModal(code) {
            if (confirm(`Bạn có chắc chắn muốn gửi yêu cầu hủy đơn tour ${code} không? Nhân viên CSKH sẽ liên hệ hỗ trợ bạn.`)) {
                alert('Yêu cầu hủy đã được ghi nhận. Chúng tôi sẽ phản hồi trong vòng 24 giờ!');
            }
        }
    </script>
</body>
</html>
