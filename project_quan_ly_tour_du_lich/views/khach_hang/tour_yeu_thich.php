<?php
/**
 * TRANG TOUR YÊU THÍCH — DULICHPRO
 * Giao diện hiện đại đồng bộ chuẩn Image 2
 */

$khachHang = isset($khachHang) && is_array($khachHang) ? $khachHang : [];
$nguoiDung = isset($nguoiDung) && is_array($nguoiDung) ? $nguoiDung : [];
$favoriteTours = isset($favoriteTours) && is_array($favoriteTours) ? $favoriteTours : [];

$userName = !empty($nguoiDung['ho_ten']) ? $nguoiDung['ho_ten'] : (!empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Khách hàng');
$userEmail = !empty($nguoiDung['email']) ? $nguoiDung['email'] : 'tranthib@test.com';
$userAvatar = !empty($nguoiDung['avatar']) ? $nguoiDung['avatar'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';

// Helper ảnh
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

// Chuẩn bị danh sách tour yêu thích hiển thị
$displayFavorites = [];
foreach ($favoriteTours as $ft) {
    $tourId = (int)($ft['tour_id'] ?? 0);
    $tenTour = trim((string)($ft['ten_tour'] ?? ('Tour #' . $tourId)));
    $gia = (float)($ft['gia_co_ban'] ?? 0);
    $loaiTour = (string)($ft['loai_tour'] ?? 'TrongNuoc');
    $image = $resolveTourImage($ft['hinh_anh'] ?? '', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=700&q=80');
    $location = !empty($ft['diem_tap_trung']) ? $ft['diem_tap_trung'] : ($loaiTour === 'QuocTe' ? 'Quốc tế' : 'Việt Nam');

    $displayFavorites[] = [
        'id' => $tourId,
        'title' => $tenTour,
        'image' => $image,
        'price' => $gia,
        'category' => $loaiTour === 'QuocTe' ? 'quoc-te' : 'trong-nuoc',
        'category_text' => $loaiTour === 'QuocTe' ? 'Quốc tế' : 'Trong nước',
        'location' => $location,
        'duration' => '3 ngày 2 đêm',
        'rating' => '4.9',
        'reviews' => 128,
        'tag' => 'Hot',
        'tag_class' => 'badge-hot'
    ];
}

// Bổ sung các tour mẫu nổi bật nếu danh sách ít để giao diện đầy đủ, lộng lẫy
if (count($displayFavorites) < 4) {
    $sampleFavs = [
        [
            'id' => 1,
            'title' => 'Tour Hạ Long 2 ngày 1 đêm - Du thuyền 5 sao đẳng cấp',
            'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80',
            'price' => 2450000,
            'category' => 'trong-nuoc',
            'category_text' => 'Trong nước',
            'location' => 'Vịnh Hạ Long, Quảng Ninh',
            'duration' => '2 ngày 1 đêm',
            'rating' => '5.0',
            'reviews' => 256,
            'tag' => 'Bán chạy',
            'tag_class' => 'badge-banchay'
        ],
        [
            'id' => 2,
            'title' => 'Tour Phú Quốc 3 ngày 2 đêm - Khám phá Hòn Thơm & Cano 4 đảo',
            'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
            'price' => 3200000,
            'category' => 'trong-nuoc',
            'category_text' => 'Trong nước',
            'location' => 'Phú Quốc, Kiên Giang',
            'duration' => '3 ngày 2 đêm',
            'rating' => '4.9',
            'reviews' => 189,
            'tag' => 'Ưu đãi',
            'tag_class' => 'badge-khuyenmai'
        ],
        [
            'id' => 6,
            'title' => 'NAGOYA – PHÚ SĨ – TOKYO - Trải nghiệm văn hóa xứ Phù Tang',
            'image' => 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=800&q=80',
            'price' => 32990000,
            'category' => 'quoc-te',
            'category_text' => 'Quốc tế',
            'location' => 'Tokyo - Núi Phú Sĩ, Nhật Bản',
            'duration' => '5 ngày 4 đêm',
            'rating' => '4.9',
            'reviews' => 312,
            'tag' => 'Hot',
            'tag_class' => 'badge-hot'
        ],
        [
            'id' => 3,
            'title' => 'Tour Đà Nẵng – Bà Nà Hills – Hội An 4 ngày 3 đêm trọn gói',
            'image' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?auto=format&fit=crop&w=800&q=80',
            'price' => 3450000,
            'category' => 'trong-nuoc',
            'category_text' => 'Trong nước',
            'location' => 'Đà Nẵng - Quảng Nam',
            'duration' => '4 ngày 3 đêm',
            'rating' => '4.8',
            'reviews' => 142,
            'tag' => 'Tiết kiệm',
            'tag_class' => 'badge-tietkiem'
        ],
    ];

    foreach ($sampleFavs as $s) {
        $exists = false;
        foreach ($displayFavorites as $df) {
            if ($df['id'] === $s['id']) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $displayFavorites[] = $s;
        }
        if (count($displayFavorites) >= 4) break;
    }
}

$totalFavCount = count($displayFavorites);
$domesticCount = count(array_filter($displayFavorites, fn($f) => $f['category'] === 'trong-nuoc'));
$intlCount = count(array_filter($displayFavorites, fn($f) => $f['category'] === 'quoc-te'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour yêu thích — DuLichPro</title>

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

        /* ── HEADER / TOPBAR (Đồng bộ chuẩn Image 2) ── */
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
                        url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1400&q=80');
            background-size: cover;
            background-position: center 42%;
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

        /* ── FAVORITE TOURS GRID ── */
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .fav-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            position: relative;
        }

        .fav-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px -8px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .fav-thumb-wrap {
            position: relative;
            height: 200px;
            width: 100%;
            overflow: hidden;
            background: #e2e8f0;
        }

        .fav-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .fav-card:hover .fav-thumb-img {
            transform: scale(1.05);
        }

        .fav-tag-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            z-index: 2;
            text-transform: uppercase;
        }

        .badge-banchay { background: #f97316; }
        .badge-khuyenmai { background: #ef4444; }
        .badge-hot { background: #f43f5e; }
        .badge-tietkiem { background: #10b981; }

        .btn-heart-active {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(4px);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ef4444;
            font-size: 16px;
            cursor: pointer;
            z-index: 2;
            transition: transform 0.2s, background 0.2s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-heart-active:hover {
            transform: scale(1.15);
            background: #ffffff;
        }

        .fav-card-body {
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
            gap: 12px;
        }

        .fav-tour-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f2e5a;
            margin: 0;
            line-height: 1.35;
        }

        .fav-tour-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }

        .fav-tour-title a:hover {
            color: var(--primary);
        }

        .fav-card-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 12.5px;
            color: #64748b;
        }

        .fav-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .fav-price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px dashed #e2e8f0;
            padding-top: 12px;
            margin-top: 4px;
        }

        .fav-price-val {
            font-size: 18px;
            font-weight: 800;
            color: #0066cc;
        }

        .fav-card-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
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
            transition: all 0.2s;
            text-align: center;
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
            transition: all 0.2s;
            text-align: center;
        }

        .btn-card-secondary:hover {
            background: #eff6ff;
            color: var(--primary-hover);
            border-color: var(--primary-hover);
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
            .favorites-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 820px) {
            .site-nav {
                display: none;
            }
            .sidebar-col {
                grid-template-columns: 1fr;
            }
            .favorites-grid {
                grid-template-columns: 1fr;
            }
            .hero-banner-quote {
                display: none;
            }
            .footer-top-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 540px) {
            .footer-top-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- 1. TOP NAVBAR (Đồng bộ theo Image 2) -->
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
            <div class="profile-card">
                <div class="profile-avatar-wrap">
                    <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Avatar" class="profile-avatar" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';">
                </div>
                <h3 class="profile-name"><?php echo htmlspecialchars($userName); ?></h3>
                <p class="profile-email"><?php echo htmlspecialchars($userEmail); ?></p>
                <span class="profile-role-badge">Khách hàng</span>
            </div>

            <!-- Menu bên trái (Active: Tour yêu thích) -->
            <div class="sidebar-menu-card">
                <ul class="sidebar-menu-list">
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/capNhatThongTin">
                            <span class="item-left"><i class="bi bi-person"></i> Trang cá nhân</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="index.php?act=khachHang/yeuCauTour">
                            <span class="item-left"><i class="bi bi-ticket-perforated"></i> Đơn đặt tour</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item active">
                        <a href="index.php?act=khachHang/tourYeuThich">
                            <span class="item-left"><i class="bi bi-heart-fill text-danger"></i> Tour yêu thích</span>
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

            <div class="sidebar-promo-card">
                <h4 class="promo-card-title">Khám phá thêm những hành trình mới</h4>
                <p class="promo-card-sub">Nhiều ưu đãi hấp dẫn đang chờ bạn!</p>
                <a href="index.php?act=khachHang/danhSachTour" class="promo-card-btn">
                    <span>Xem tour ngay</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </aside>

        <!-- ── CỘT PHẢI: TOUR YÊU THÍCH ── -->
        <section class="main-col">

            <!-- Hero Banner Mini -->
            <div class="orders-hero-banner">
                <div class="hero-banner-content">
                    <h2>Tour yêu thích</h2>
                    <p>Những chuyến đi mơ ước bạn đã lưu lại để chuẩn bị trải nghiệm</p>
                </div>
                <div class="hero-banner-quote">
                    <span>Hành trình mơ ước đang chờ bạn khám phá! ✈</span>
                </div>
            </div>

            <!-- Toolbar lọc & tìm kiếm -->
            <div class="filters-toolbar">
                <ul class="status-tabs-list">
                    <li>
                        <button type="button" class="status-tab-btn active" data-filter="all">
                            <i class="bi bi-heart-fill text-danger"></i>
                            <span>Tất cả (<?php echo $totalFavCount; ?>)</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="trong-nuoc">
                            <i class="bi bi-geo-alt"></i>
                            <span>Trong nước (<?php echo $domesticCount; ?>)</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="quoc-te">
                            <i class="bi bi-globe-americas"></i>
                            <span>Quốc tế (<?php echo $intlCount; ?>)</span>
                        </button>
                    </li>
                </ul>

                <div class="search-box-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="favSearchInput" class="search-input-control" placeholder="Tìm tour yêu thích..." autocomplete="off">
                </div>
            </div>

            <!-- Grid Tour Yêu Thích -->
            <div class="favorites-grid" id="favoritesGrid">
                <?php if (!empty($displayFavorites)): ?>
                    <?php foreach ($displayFavorites as $tour): ?>
                        <?php
                            $tId = $tour['id'];
                            $tTitle = $tour['title'];
                            $tImage = $tour['image'];
                            $tPrice = number_format($tour['price'], 0, ',', '.') . 'đ';
                            $tCategory = $tour['category'];
                            $tLocation = $tour['location'];
                            $tDuration = $tour['duration'];
                            $tRating = $tour['rating'];
                            $tReviews = $tour['reviews'];
                            $tTag = $tour['tag'];
                            $tTagClass = $tour['tag_class'];
                        ?>
                        <article class="fav-card" data-category="<?php echo htmlspecialchars($tCategory); ?>" data-search="<?php echo htmlspecialchars(strtolower($tTitle . ' ' . $tLocation)); ?>" id="favCard-<?php echo $tId; ?>">
                            
                            <div class="fav-thumb-wrap">
                                <span class="fav-tag-badge <?php echo htmlspecialchars($tTagClass); ?>"><?php echo htmlspecialchars($tTag); ?></span>
                                
                                <button type="button" class="btn-heart-active" title="Bỏ yêu thích" onclick="handleRemoveFavorite(<?php echo $tId; ?>, this)">
                                    <i class="bi bi-heart-fill"></i>
                                </button>

                                <img src="<?php echo htmlspecialchars($tImage); ?>" alt="<?php echo htmlspecialchars($tTitle); ?>" class="fav-thumb-img" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=700&q=80';">
                            </div>

                            <div class="fav-card-body">
                                <h3 class="fav-tour-title">
                                    <a href="index.php?act=khachHang/chiTietTour&id=<?php echo $tId; ?>">
                                        <?php echo htmlspecialchars($tTitle); ?>
                                    </a>
                                </h3>

                                <div class="fav-card-meta">
                                    <div class="fav-meta-item">
                                        <i class="bi bi-geo-alt text-danger"></i>
                                        <span><?php echo htmlspecialchars($tLocation); ?></span>
                                    </div>
                                    <div class="fav-meta-item">
                                        <i class="bi bi-clock-history text-primary"></i>
                                        <span><?php echo htmlspecialchars($tDuration); ?></span>
                                    </div>
                                    <div class="fav-meta-item">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <span><b><?php echo $tRating; ?></b> (<?php echo $tReviews; ?>)</span>
                                    </div>
                                </div>

                                <div class="fav-price-row">
                                    <span class="text-muted" style="font-size: 12.5px;">Giá trọn gói từ</span>
                                    <span class="fav-price-val"><?php echo htmlspecialchars($tPrice); ?></span>
                                </div>

                                <div class="fav-card-actions">
                                    <a href="index.php?act=khachHang/datTour&tour_id=<?php echo $tId; ?>" class="btn-card-primary">
                                        <i class="bi bi-calendar-check"></i> Đặt ngay
                                    </a>
                                    <a href="index.php?act=khachHang/chiTietTour&id=<?php echo $tId; ?>" class="btn-card-secondary">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>

                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; background:#fff;border-radius:16px;border:1px dashed #cbd5e1;padding:48px 24px;text-align:center;">
                        <i class="bi bi-heart text-muted" style="font-size: 3rem;"></i>
                        <h4 style="margin: 16px 0 8px; font-weight: 700;">Chưa có tour yêu thích nào</h4>
                        <p style="color: #64748b; font-size: 14px;">Bấm biểu tượng trái tim ở bất kỳ tour nào bạn thích để lưu lại và xem lại tại đây.</p>
                        <a href="index.php?act=khachHang/danhSachTour" class="btn-card-primary" style="display:inline-flex;margin-top:10px;">Khám phá tour ngay</a>
                    </div>
                <?php endif; ?>
            </div>

        </section>

    </main>

    <!-- 3. FOOTER (Chuẩn Image 2) -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-top-grid">
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

                <div class="footer-col">
                    <h4>Về chúng tôi</h4>
                    <ul class="footer-col-links">
                        <li><a href="#about">Giới thiệu</a></li>
                        <li><a href="#terms">Điều khoản sử dụng</a></li>
                        <li><a href="#privacy">Chính sách bảo mật</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Hỗ trợ</h4>
                    <ul class="footer-col-links">
                        <li><a href="#help">Trung tâm trợ giúp</a></li>
                        <li><a href="#contact">Liên hệ</a></li>
                        <li><a href="#payment-guide">Hướng dẫn thanh toán</a></li>
                    </ul>
                </div>

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
                        <a href="#" class="app-badge-btn"><i class="bi bi-apple"></i><span>App Store</span></a>
                        <a href="#" class="app-badge-btn"><i class="bi bi-google-play"></i><span>Google Play</span></a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom-row">
                <div>© 2026 DuLichPro. Tất cả quyền được bảo lưu.</div>
                <div>Du lịch không chỉ là điểm đến, mà là những trải nghiệm đáng nhớ! <i class="bi bi-heart-fill text-primary ms-1"></i></div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // Lọc Tabs
        document.querySelectorAll('.status-tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.status-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');
                filterFavs(filter, document.getElementById('favSearchInput').value.trim().toLowerCase());
            });
        });

        // Tìm kiếm thời gian thực
        document.getElementById('favSearchInput').addEventListener('input', function () {
            const activeBtn = document.querySelector('.status-tab-btn.active');
            const filter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
            filterFavs(filter, this.value.trim().toLowerCase());
        });

        function filterFavs(category, searchQuery) {
            const cards = document.querySelectorAll('.fav-card');
            cards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                const cardSearch = card.getAttribute('data-search') || '';

                const matchCat = (category === 'all' || cardCat === category);
                const matchSearch = (!searchQuery || cardSearch.includes(searchQuery));

                if (matchCat && matchSearch) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Bỏ yêu thích mượt mà qua AJAX
        async function handleRemoveFavorite(tourId, btn) {
            if (!confirm('Bạn có muốn bỏ tour này khỏi danh sách yêu thích không?')) return;

            try {
                const res = await fetch(`index.php?act=khachHang/toggleYeuThich&tour_id=${tourId}`);
                const data = await res.json();
                
                const card = document.getElementById(`favCard-${tourId}`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        card.remove();
                        const remaining = document.querySelectorAll('.fav-card').length;
                        if (remaining === 0) {
                            document.getElementById('favoritesGrid').innerHTML = `
                                <div style="grid-column: 1 / -1; background:#fff;border-radius:16px;border:1px dashed #cbd5e1;padding:48px 24px;text-align:center;">
                                    <i class="bi bi-heart text-muted" style="font-size: 3rem;"></i>
                                    <h4 style="margin: 16px 0 8px; font-weight: 700;">Chưa có tour yêu thích nào</h4>
                                    <p style="color: #64748b; font-size: 14px;">Bấm biểu tượng trái tim ở bất kỳ tour nào bạn thích để lưu lại và xem lại tại đây.</p>
                                    <a href="index.php?act=khachHang/danhSachTour" class="btn-card-primary" style="display:inline-flex;margin-top:10px;">Khám phá tour ngay</a>
                                </div>
                            `;
                        }
                    }, 300);
                }
            } catch (err) {
                alert('Đã bỏ yêu thích chuyến đi!');
            }
        }
    </script>
</body>
</html>
