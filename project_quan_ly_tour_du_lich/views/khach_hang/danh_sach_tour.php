<?php
/** @var array $tours */
/** @var array $filters */
/** @var array $favoriteTourIds */
$tours = isset($tours) && is_array($tours) ? array_values($tours) : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$favoriteTourIds = isset($favoriteTourIds) && is_array($favoriteTourIds) ? $favoriteTourIds : [];
$isLoggedIn = !empty($_SESSION['user_id']);

// Lấy thông tin người dùng đang đăng nhập (đồng bộ chuẩn Image 1)
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

// Helper chuẩn hóa URL hình ảnh (hỗ trợ cả tuyệt đối https:// và tương đối uploads/...)
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

$searchQuery = trim((string)($filters['q'] ?? $_GET['search'] ?? ''));
$selectedLoaiTour = trim((string)($filters['loai_tour'] ?? $_GET['loai_tour'] ?? ''));
$selectedPriceRange = trim((string)($filters['price_range'] ?? $_GET['price_range'] ?? ''));
$selectedSort = trim((string)($filters['sort'] ?? $_GET['sort'] ?? 'newest'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour du lịch — DuLichPro</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans & Caveat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Caveat:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0066cc;
            --primary-hover: #0052a3;
            --primary-light: #e0f2fe;
            --primary-soft: #f0f7ff;
            --navy-dark: #0f172a;
            --navy-footer: #0a1426;
            --slate-gray: #64748b;
            --slate-light: #f8fafc;
            --border-color: #e2e8f0;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
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

        /* ── TOP NAVBAR (Đồng bộ chuẩn Image 1) ── */
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
            gap: 20px;
        }

        .brand-block {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
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
            white-space: nowrap;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-wrap: nowrap;
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
            display: inline-block;
        }

        .site-nav a:hover {
            color: #0066cc;
            background: #f0f7ff;
        }

        .site-nav a.active {
            color: #0066cc;
            background: #e0f2fe;
            font-weight: 600;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
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
            color: #0066cc;
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

        .user-dropdown-container {
            position: relative;
        }

        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 4px 12px 4px 4px;
            border-radius: 30px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            text-decoration: none;
            color: #1e293b;
            transition: background 0.2s;
            white-space: nowrap;
        }

        .user-profile-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
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
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
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

        .hero-section {
            background-image: linear-gradient(to right, rgba(15, 23, 42, 0.72) 0%, rgba(15, 23, 42, 0.35) 55%, rgba(15, 23, 42, 0.18) 100%), 
                              url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1920&auto=format&fit=crop');
            background-position: center 40%;
            background-size: cover;
            min-height: 480px;
            display: flex;
            align-items: center;
            padding: 60px 0 100px;
            position: relative;
        }

        .hero-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .hero-vietnam-tag {
            font-family: 'Caveat', cursive;
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .hero-vietnam-tag i {
            font-size: 20px;
            transform: rotate(-25deg);
            color: #7dd3fc;
        }

        .hero-title {
            font-size: 44px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.18;
            margin: 0 0 14px 0;
            text-shadow: 0 2px 8px rgba(0,0,0,0.4);
            letter-spacing: -0.5px;
            max-width: 650px;
        }

        .hero-subtitle {
            font-size: 15px;
            line-height: 1.6;
            color: #f1f5f9;
            max-width: 520px;
            margin-bottom: 26px;
            text-shadow: 0 1px 4px rgba(0,0,0,0.4);
        }

        .trust-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .trust-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12.5px;
            font-weight: 500;
        }

        .trust-pill i {
            color: #38bdf8;
            font-size: 14px;
        }

        .hero-right-calligraphy {
            position: absolute;
            right: 40px;
            top: 45%;
            transform: translateY(-50%);
            text-align: right;
            pointer-events: none;
        }

        .hero-right-calligraphy .script-text {
            font-family: 'Caveat', cursive;
            font-size: 36px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            line-height: 1.2;
            display: block;
        }

        /* ── FLOATING SEARCH CARD ── */
        .search-card-wrapper {
            max-width: 1200px;
            margin: -65px auto 40px;
            padding: 0 20px;
            position: relative;
            z-index: 20;
        }

        .search-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.12);
            padding: 24px 28px;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .search-tabs {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 14px;
            overflow-x: auto;
        }

        .search-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 20px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .search-tab-btn:hover {
            color: var(--primary);
            background: #f8fafc;
        }

        .search-tab-btn.active {
            background: #e0f2fe;
            color: #0284c7;
        }

        .search-fields-row {
            display: grid;
            grid-template-columns: 1.8fr 1.3fr 1.3fr auto;
            gap: 16px;
            align-items: center;
        }

        .search-field-col {
            padding: 8px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            transition: border-color 0.2s;
        }

        .search-field-col:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .search-field-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }

        .search-field-label i {
            color: var(--primary);
            font-size: 13px;
        }

        .search-field-input {
            width: 100%;
            border: none;
            outline: none;
            font-size: 14.5px;
            font-weight: 600;
            color: #1e293b;
            background: transparent;
            padding: 0;
        }

        .search-field-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
            font-size: 13.5px;
        }

        .btn-submit-search {
            height: 54px;
            background: #0066cc;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 0 30px;
            font-size: 15px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(0, 102, 204, 0.25);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-submit-search:hover {
            background: #0052a3;
            transform: translateY(-1px);
        }

        /* ── CATEGORY PILLS (6 Loại Tour) ── */
        .category-pills-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
            margin-bottom: 50px;
        }

        .category-pill-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 12px;
            text-align: center;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .category-pill-card:hover,
        .category-pill-card.is-active {
            border-color: #93c5fd;
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -6px rgba(0, 102, 204, 0.12);
        }

        .category-pill-card.is-active {
            background: #f0f9ff;
            border-color: var(--primary);
        }

        .category-pill-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #f0f7ff;
            color: #0066cc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }

        .category-pill-card:hover .category-pill-icon,
        .category-pill-card.is-active .category-pill-icon {
            background: var(--primary);
            color: #ffffff;
        }

        .category-pill-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 3px 0;
        }

        .category-pill-desc {
            font-size: 11.5px;
            color: #94a3b8;
            margin: 0;
        }

        /* ── SECTION COMMONS ── */
        .page-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-header-flex {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px 0;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #f97316;
        }

        .section-subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .view-all-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            transition: transform 0.2s;
        }

        .view-all-link:hover {
            color: var(--primary-hover);
            transform: translateX(3px);
        }

        /* ── SLIDER NAV BUTTONS ── */
        .btn-slider-nav {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .btn-slider-nav:hover {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.25);
            transform: translateY(-1px);
        }

        /* ── TOUR CARDS HORIZONTAL ROW ── */
        .tours-slider-wrapper {
            position: relative;
            margin-bottom: 56px;
        }

        .tours-grid {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scroll-behavior: smooth;
            scroll-snap-type: x mandatory;
            padding: 4px 4px 18px;
            margin-bottom: 0;
            -webkit-overflow-scrolling: touch;
            cursor: grab;
        }

        .tours-grid.is-dragging {
            cursor: grabbing;
            scroll-snap-type: none;
            scroll-behavior: auto;
        }

        /* Modern Subtle Scrollbar */
        .tours-grid::-webkit-scrollbar {
            height: 6px;
        }

        .tours-grid::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .tours-grid::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            transition: background 0.2s;
        }

        .tours-grid::-webkit-scrollbar-thumb:hover {
            background: #0066cc;
        }

        .tour-card {
            flex: 0 0 285px;
            width: 285px;
            min-width: 285px;
            scroll-snap-align: start;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            position: relative;
        }

        .tour-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 30px -8px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .tour-thumbnail-wrap {
            position: relative;
            height: 195px;
            width: 100%;
            overflow: hidden;
            background: #e2e8f0;
        }

        .tour-thumbnail-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .tour-card:hover .tour-thumbnail-wrap img {
            transform: scale(1.05);
        }

        .tour-tag-badge {
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
            letter-spacing: 0.3px;
        }

        .badge-banchay { background: #f97316; }
        .badge-khuyenmai { background: #ef4444; }
        .badge-hot { background: #f43f5e; }
        .badge-tietkiem { background: #10b981; }

        .btn-favorite-heart {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(4px);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 15px;
            cursor: pointer;
            z-index: 2;
            transition: all 0.2s;
        }

        .btn-favorite-heart:hover,
        .btn-favorite-heart.is-favorited {
            color: #ef4444;
            background: #ffffff;
        }

        /* Duration Badge on bottom-left of image */
        .tour-duration-badge {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            z-index: 2;
        }

        /* Corner Arrow Button on bottom-right of image */
        .tour-thumb-arrow-btn {
            position: absolute;
            bottom: 12px;
            right: 12px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.85);
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
            z-index: 2;
        }

        .tour-card:hover .tour-thumb-arrow-btn {
            background: #0066cc;
            color: #ffffff;
            transform: translateX(2px);
        }

        .tour-card-body {
            padding: 16px 16px 18px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .tour-card-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px 0;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 44px;
        }

        .tour-card-title a {
            color: inherit;
            text-decoration: none;
        }

        .tour-card-title a:hover {
            color: var(--primary);
        }

        .tour-location-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .tour-location-meta i {
            color: #94a3b8;
        }

        .tour-rating-row {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12.5px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .tour-rating-row i {
            color: #eab308;
        }

        .tour-amenities-pills {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: #64748b;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .tour-amenities-pills span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .tour-amenities-pills i {
            font-size: 13px;
            color: #0284c7;
        }

        .tour-card-footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .tour-price-value {
            font-size: 17px;
            font-weight: 800;
            color: #0066cc;
            letter-spacing: -0.3px;
        }

        .tour-price-unit {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
        }

        .btn-book-now {
            background: #0066cc;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            padding: 7px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-book-now:hover {
            background: #0052a3;
            color: #ffffff;
        }

        /* ── PROMO SPLIT BANNER (Summer 40% Off + 3 Service Cards) ── */
        .promo-split-section {
            display: grid;
            grid-template-columns: 1.7fr 1.1fr;
            gap: 20px;
            margin-bottom: 56px;
        }

        .promo-beach-card {
            background-image: linear-gradient(to right, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.3) 65%, rgba(15, 23, 42, 0.1) 100%), 
                              url('https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=1200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            padding: 40px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 240px;
        }

        .promo-beach-tag {
            font-family: 'Caveat', cursive;
            font-size: 26px;
            color: #7dd3fc;
            margin-bottom: 4px;
        }

        .promo-beach-title {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.2;
            margin: 0 0 8px 0;
        }

        .promo-beach-subtitle {
            font-size: 15px;
            color: #e2e8f0;
            margin: 0 0 22px 0;
        }

        .btn-promo-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 700;
            padding: 10px 22px;
            border-radius: 24px;
            text-decoration: none;
            width: fit-content;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.2s;
        }

        .btn-promo-action:hover {
            background: #f1f5f9;
            color: var(--primary);
            transform: translateX(2px);
        }

        .promo-features-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .promo-feature-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
        }

        .promo-feature-box:hover {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 8px 20px -4px rgba(0, 102, 204, 0.1);
        }

        .feature-icon-bubble {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #e0f2fe;
            color: #0284c7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }

        .feature-text-content h4 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 3px 0;
        }

        .feature-text-content p {
            font-size: 12.5px;
            color: #64748b;
            margin: 0;
        }

        .feature-arrow-right {
            margin-left: auto;
            color: #94a3b8;
            font-size: 16px;
        }

        /* ── POPULAR DESTINATIONS (6 Điểm đến yêu thích) ── */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-bottom: 56px;
        }

        .destination-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            text-decoration: none;
            transition: all 0.25s ease;
            position: relative;
        }

        .destination-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .destination-img-wrap {
            height: 135px;
            width: 100%;
            overflow: hidden;
            position: relative;
        }

        .destination-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .destination-card:hover .destination-img-wrap img {
            transform: scale(1.08);
        }

        .destination-circle-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s;
        }

        .destination-card:hover .destination-circle-btn {
            background: #0066cc;
            color: #ffffff;
        }

        .destination-meta {
            padding: 12px 14px;
        }

        .destination-name {
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px 0;
        }

        .destination-province {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }

        /* ── TESTIMONIALS (With Photo Gallery) ── */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 60px;
        }

        .testimonial-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 22px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
        }

        .testimonial-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -5px rgba(0,0,0,0.06);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .testimonial-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e0f2fe;
        }

        .testimonial-author-name {
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px 0;
        }

        .testimonial-date {
            font-size: 11px;
            color: #94a3b8;
            margin-left: 8px;
            font-weight: normal;
        }

        .testimonial-stars {
            color: #f59e0b;
            font-size: 12px;
            display: flex;
            gap: 2px;
        }

        .testimonial-quote {
            font-size: 13px;
            line-height: 1.55;
            color: #475569;
            margin: 0 0 14px 0;
            flex-grow: 1;
        }

        .testimonial-gallery {
            display: flex;
            gap: 6px;
            margin-top: auto;
        }

        .testimonial-gallery img {
            width: 48px;
            height: 38px;
            border-radius: 6px;
            object-fit: cover;
            transition: transform 0.2s;
        }

        .testimonial-gallery img:hover {
            transform: scale(1.1);
        }

        /* ── FOOTER ── */
        .site-footer {
            background-color: var(--navy-footer);
            color: #cbd5e1;
            padding: 60px 0 24px;
        }

        .footer-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 1.2fr 1fr 1.4fr;
            gap: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 24px;
        }

        .footer-brand h2 {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
        }

        .footer-brand p {
            font-size: 12px;
            color: #94a3b8;
            margin: 4px 0 0 0;
        }

        .footer-heading {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 16px 0;
        }

        .footer-contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 13px;
        }

        .footer-contact-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #94a3b8;
        }

        .footer-contact-list i {
            color: #38bdf8;
            font-size: 15px;
            margin-top: 2px;
        }

        .footer-links-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 13.5px;
        }

        .footer-links-list a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links-list a:hover {
            color: #ffffff;
        }

        .footer-socials {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .footer-social-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.2s;
        }

        .footer-social-btn:hover {
            background: var(--primary);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .app-download-badges {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .app-store-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 6px 14px;
            border-radius: 8px;
            color: #ffffff;
            text-decoration: none;
            font-size: 11px;
            transition: all 0.2s;
        }

        .app-store-pill:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        .footer-bottom-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #64748b;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-newsletter-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 30px;
            padding: 4px 6px 4px 16px;
        }

        .footer-newsletter-input {
            background: transparent;
            border: none;
            outline: none;
            color: #ffffff;
            font-size: 13px;
            width: 240px;
        }

        .footer-newsletter-input::placeholder {
            color: #94a3b8;
        }

        .footer-newsletter-btn {
            background: #0066cc;
            color: #ffffff;
            border: none;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }

        .footer-newsletter-btn:hover {
            background: #0052a3;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .category-pills-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .destinations-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .promo-split-section {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .site-nav {
                display: none;
            }
            .hero-title {
                font-size: 30px;
            }
            .hero-right-calligraphy {
                display: none;
            }
            .search-fields-row {
                grid-template-columns: 1fr;
            }
            .category-pills-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .tour-card {
                flex: 0 0 265px;
                width: 265px;
                min-width: 265px;
            }
            .destinations-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .footer-bottom-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            .footer-newsletter-box {
                width: 100%;
            }
            .footer-newsletter-input {
                flex-grow: 1;
                width: auto;
            }
        }
    </style>
</head>
<body>

    <!-- 1. TOP NAVBAR (Đồng bộ chuẩn Image 1) -->
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

                <?php if ($isLoggedIn): ?>
                    <a href="index.php?act=khachHang/thongBao" class="btn-icon-round" title="Thông báo">
                        <i class="bi bi-bell"></i>
                        <span class="badge-count">3</span>
                    </a>

                    <div class="user-dropdown-container">
                        <a href="index.php?act=khachHang/capNhatThongTin" class="user-profile-btn" title="Trang cá nhân">
                            <img src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Avatar" class="user-profile-avatar" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';">
                            <span class="user-profile-name"><?php echo htmlspecialchars($userName); ?></span>
                            <i class="bi bi-chevron-down" style="font-size: 11px; color: #64748b;"></i>
                        </a>
                        <div class="user-dropdown-menu">
                            <div class="user-dropdown-header">
                                <strong><?php echo htmlspecialchars($userName); ?></strong>
                                <small><?php echo htmlspecialchars($userEmail); ?></small>
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
                    <a href="index.php?act=auth/login" class="btn-auth-login">
                        <i class="bi bi-person"></i> <span>Đăng nhập</span>
                    </a>
                    <a href="index.php?act=auth/register" class="btn-auth-register">
                        <span>Đăng ký</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- 2. HERO BANNER -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-vietnam-tag">
                <span>Vietnam</span>
                <i class="bi bi-send-fill"></i>
            </div>

            <h1 class="hero-title">Khám phá những hành trình tuyệt vời</h1>

            <p class="hero-subtitle">
                Từ những bãi biển xanh mát, những thành phố sôi động đến những vùng đất văn hóa đặc sắc, DuLichPro luôn đồng hành cùng bạn trên mọi hành trình.
            </p>

            <div class="trust-badges">
                <div class="trust-pill">
                    <i class="bi bi-compass-fill"></i>
                    <span>1000+ Tour du lịch</span>
                </div>
                <div class="trust-pill">
                    <i class="bi bi-shield-check"></i>
                    <span>An toàn - Uy tín hàng đầu</span>
                </div>
                <div class="trust-pill">
                    <i class="bi bi-headset"></i>
                    <span>Hỗ trợ 24/7 - Luôn bên bạn</span>
                </div>
            </div>
        </div>

        <div class="hero-right-calligraphy">
            <span class="script-text">Đi để khám phá<br>Thế giới! ✈</span>
        </div>
    </section>

    <!-- 3. FLOATING BOOKING & SEARCH CARD -->
    <div class="search-card-wrapper">
        <div class="search-card">
            <!-- Tabs -->
            <div class="search-tabs">
                <button type="button" class="search-tab-btn active">
                    <i class="bi bi-compass-fill"></i>
                    <span>Tour du lịch</span>
                </button>
                <button type="button" class="search-tab-btn" onclick="location.href='index.php?act=khachHang/dashboard';">
                    <i class="bi bi-building"></i>
                    <span>Khách sạn</span>
                </button>
                <button type="button" class="search-tab-btn" onclick="location.href='index.php?act=khachHang/dashboard';">
                    <i class="bi bi-airplane-engines"></i>
                    <span>Vé máy bay</span>
                </button>
                <button type="button" class="search-tab-btn" onclick="location.href='index.php?act=khachHang/dashboard';">
                    <i class="bi bi-gift"></i>
                    <span>Combo</span>
                </button>
                <button type="button" class="search-tab-btn" onclick="location.href='index.php?act=khachHang/dashboard';">
                    <i class="bi bi-person-vcard"></i>
                    <span>Visa</span>
                </button>
            </div>

            <!-- Search Form -->
            <form action="index.php" method="GET">
                <input type="hidden" name="act" value="khachHang/danhSachTour">
                
                <div class="search-fields-row">
                    <!-- Field 1: Destination -->
                    <div class="search-field-col">
                        <div class="search-field-label">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Điểm đến</span>
                        </div>
                        <input type="text" name="search" class="search-field-input" placeholder="Nhập điểm đến..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>

                    <!-- Field 2: Date -->
                    <div class="search-field-col">
                        <div class="search-field-label">
                            <i class="bi bi-calendar3"></i>
                            <span>Ngày khởi hành</span>
                        </div>
                        <input type="date" name="ngay_di" class="search-field-input">
                    </div>

                    <!-- Field 3: Number of Guests -->
                    <div class="search-field-col">
                        <div class="search-field-label">
                            <i class="bi bi-people-fill"></i>
                            <span>Số lượng khách</span>
                        </div>
                        <select name="so_nguoi" class="search-field-input" style="cursor: pointer;">
                            <option value="2">2 người lớn, 0 trẻ em</option>
                            <option value="1">1 người lớn</option>
                            <option value="3">3 người lớn</option>
                            <option value="4">Gia đình (4 người)</option>
                            <option value="5">Đoàn đông (&gt; 5 người)</option>
                        </select>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-submit-search">
                        <i class="bi bi-search"></i>
                        <span>Tìm kiếm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <main class="page-container">

        <!-- 4. CATEGORY PILLS (6 Loại Tour) -->
        <section class="mb-5">
            <div class="category-pills-grid">
                <!-- 1. Tour trong nước -->
                <a href="index.php?act=khachHang/danhSachTour&loai_tour=TrongNuoc" class="category-pill-card <?php echo ($selectedLoaiTour === 'TrongNuoc') ? 'is-active' : ''; ?>">
                    <div class="category-pill-icon">
                        <i class="bi bi-geo-fill"></i>
                    </div>
                    <h3 class="category-pill-title">Tour trong nước</h3>
                    <p class="category-pill-desc">Khám phá Việt Nam</p>
                </a>

                <!-- 2. Tour nước ngoài -->
                <a href="index.php?act=khachHang/danhSachTour&loai_tour=QuocTe" class="category-pill-card <?php echo ($selectedLoaiTour === 'QuocTe') ? 'is-active' : ''; ?>">
                    <div class="category-pill-icon">
                        <i class="bi bi-globe-asia-australia"></i>
                    </div>
                    <h3 class="category-pill-title">Tour nước ngoài</h3>
                    <p class="category-pill-desc">Khám phá thế giới</p>
                </a>

                <!-- 3. Tour biển đảo -->
                <a href="index.php?act=khachHang/danhSachTour&search=biển" class="category-pill-card">
                    <div class="category-pill-icon">
                        <i class="bi bi-water"></i>
                    </div>
                    <h3 class="category-pill-title">Tour biển đảo</h3>
                    <p class="category-pill-desc">Nghỉ dưỡng, thư giãn</p>
                </a>

                <!-- 4. Tour văn hóa -->
                <a href="index.php?act=khachHang/danhSachTour&search=cổ" class="category-pill-card">
                    <div class="category-pill-icon">
                        <i class="bi bi-bank"></i>
                    </div>
                    <h3 class="category-pill-title">Tour văn hóa</h3>
                    <p class="category-pill-desc">Di sản & lịch sử</p>
                </a>

                <!-- 5. Tour mạo hiểm -->
                <a href="index.php?act=khachHang/danhSachTour&search=khám+phá" class="category-pill-card">
                    <div class="category-pill-icon">
                        <i class="bi bi-signpost-2-fill"></i>
                    </div>
                    <h3 class="category-pill-title">Tour mạo hiểm</h3>
                    <p class="category-pill-desc">Trải nghiệm mới</p>
                </a>

                <!-- 6. Tour gia đình -->
                <a href="index.php?act=khachHang/danhSachTour" class="category-pill-card">
                    <div class="category-pill-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="category-pill-title">Tour gia đình</h3>
                    <p class="category-pill-desc">Gắn kết yêu thương</p>
                </a>
            </div>
        </section>

        <!-- 5. TOUR NỔI BẬT (FEATURED TOURS SLIDER) -->
        <section class="mb-5">
            <div class="section-header-flex">
                <div>
                    <h2 class="section-title">
                        <i class="bi bi-fire"></i>
                        <span>Tour nổi bật</span>
                    </h2>
                    <p class="section-subtitle">Những hành trình được yêu thích nhất hiện nay — kéo sang ngang để xem thêm</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn-slider-nav" id="prevToursBtn" aria-label="Cuộn sang trái" title="Cuộn sang trái">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn-slider-nav" id="nextToursBtn" aria-label="Cuộn sang phải" title="Cuộn sang phải">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <a href="index.php?act=khachHang/danhSachTour" class="view-all-link ms-2">
                        <span>Xem tất cả</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Tour Cards Horizontal Slider -->
            <div class="tours-slider-wrapper">
                <div class="tours-grid" id="toursSlider">
                <?php
                $sampleTours = [
                    [
                        'id' => 1,
                        'name' => 'Tour Hạ Long 2 ngày 1 đêm',
                        'location' => 'Quảng Ninh',
                        'duration' => '2N1Đ',
                        'rating' => '4.8',
                        'reviews' => '320',
                        'amenities' => ['Xe ô tô', 'Du thuyền', 'Ăn uống'],
                        'price' => 2450000,
                        'badge' => 'Bán chạy',
                        'badge_class' => 'badge-banchay',
                        'image' => 'https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=600&auto=format&fit=crop'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Tour Đà Nẵng – Hội An 4 ngày 3 đêm',
                        'location' => 'Đà Nẵng – Hội An',
                        'duration' => '4N3Đ',
                        'rating' => '4.7',
                        'reviews' => '520',
                        'amenities' => ['Xe ô tô', 'Khách sạn', 'Ăn uống'],
                        'price' => 3450000,
                        'badge' => 'Khuyến mãi',
                        'badge_class' => 'badge-khuyenmai',
                        'image' => 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?q=80&w=600&auto=format&fit=crop'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Tour Phú Quốc 3 ngày 2 đêm',
                        'location' => 'Phú Quốc',
                        'duration' => '3N2Đ',
                        'rating' => '4.9',
                        'reviews' => '410',
                        'amenities' => ['Xe ô tô', 'Khách sạn', 'Ăn uống'],
                        'price' => 3200000,
                        'badge' => 'Hot',
                        'badge_class' => 'badge-hot',
                        'image' => 'https://images.unsplash.com/photo-1589394815804-964ed0be2eb5?q=80&w=600&auto=format&fit=crop'
                    ],
                    [
                        'id' => 4,
                        'name' => 'Tour Ninh Bình 1 ngày',
                        'location' => 'Ninh Bình',
                        'duration' => '1 Ngày',
                        'rating' => '4.6',
                        'reviews' => '280',
                        'amenities' => ['Xe ô tô', 'Vé tham quan', 'Ăn uống'],
                        'price' => 1980000,
                        'badge' => 'Tiết kiệm',
                        'badge_class' => 'badge-tietkiem',
                        'image' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?q=80&w=600&auto=format&fit=crop'
                    ]
                ];

                // Nếu có danh sách tour thực tế từ database, gán và ánh xạ chuẩn xác
                $displayTours = $sampleTours;
                if (!empty($tours)) {
                    $badgeTypes = [
                        ['badge' => 'Bán chạy', 'class' => 'badge-banchay'],
                        ['badge' => 'Khuyến mãi', 'class' => 'badge-khuyenmai'],
                        ['badge' => 'Hot', 'class' => 'badge-hot'],
                        ['badge' => 'Tiết kiệm', 'class' => 'badge-tietkiem']
                    ];
                    $displayTours = [];
                    foreach ($tours as $idx => $dbTour) {
                        $tourId = (int)($dbTour['tour_id'] ?? 0);
                        $tourName = $dbTour['ten_tour'] ?? '';
                        $fallbackImg = $sampleTours[$idx % count($sampleTours)]['image'];
                        $tourImg = $resolveTourImage($dbTour['hinh_anh'] ?? '', $fallbackImg);

                        $rawPrice = !empty($dbTour['gia_co_ban']) ? (float)$dbTour['gia_co_ban'] : (float)($dbTour['gia_tour'] ?? 0);
                        $tourPrice = $rawPrice > 0 ? $rawPrice : ($sampleTours[$idx % 4]['price'] ?? 2000000);

                        $avgRating = !empty($dbTour['diem_tb']) ? number_format((float)$dbTour['diem_tb'], 1) : ($sampleTours[$idx % 4]['rating'] ?? '4.8');
                        $reviewCount = !empty($dbTour['so_danh_gia']) ? (int)$dbTour['so_danh_gia'] : ($sampleTours[$idx % 4]['reviews'] ?? '120');

                        // Duration
                        $duration = trim((string)($dbTour['thoi_gian'] ?? ''));
                        if ($duration === '') {
                            $duration = $sampleTours[$idx % 4]['duration'] ?? '3N2Đ';
                        }

                        // Location
                        $location = !empty($dbTour['dia_diem']) ? $dbTour['dia_diem'] : '';
                        if ($location === '') {
                            if (!empty($dbTour['diem_tap_trung'])) {
                                $location = $dbTour['diem_tap_trung'];
                            } elseif (mb_stripos($tourName, 'Sơn Tây') !== false || mb_stripos($tourName, 'Đường Lâm') !== false) {
                                $location = 'Hà Nội';
                            } elseif (mb_stripos($tourName, 'Đà Lạt') !== false) {
                                $location = 'Lâm Đồng';
                            } elseif (mb_stripos($tourName, 'Phú Sĩ') !== false || mb_stripos($tourName, 'Tokyo') !== false) {
                                $location = 'Nhật Bản';
                            } else {
                                $location = ($dbTour['loai_tour'] ?? '') === 'QuocTe' ? 'Quốc tế' : 'Việt Nam';
                            }
                        }

                        $bIndex = $idx % count($badgeTypes);
                        $displayTours[] = [
                            'id' => $tourId,
                            'name' => $tourName ?: ('Tour du lịch #' . $tourId),
                            'location' => $location,
                            'duration' => $duration,
                            'rating' => $avgRating,
                            'reviews' => $reviewCount,
                            'amenities' => $sampleTours[$idx % 4]['amenities'] ?? ['Xe ô tô', 'Khách sạn', 'Ăn uống'],
                            'price' => $tourPrice,
                            'badge' => $badgeTypes[$bIndex]['badge'],
                            'badge_class' => $badgeTypes[$bIndex]['class'],
                            'image' => $tourImg,
                            'fallback_image' => $fallbackImg
                        ];
                    }
                }

                foreach ($displayTours as $tour):
                    $isFav = in_array((int)$tour['id'], $favoriteTourIds, true);
                    $detailUrl = "index.php?act=khachHang/chiTietTour&id=" . (int)$tour['id'];
                    $fallbackImage = $tour['fallback_image'] ?? $tour['image'];
                ?>
                <div class="tour-card">
                    <div class="tour-thumbnail-wrap">
                        <span class="tour-tag-badge <?php echo htmlspecialchars($tour['badge_class']); ?>">
                            <?php echo htmlspecialchars($tour['badge']); ?>
                        </span>
                        
                        <button type="button" class="btn-favorite-heart js-toggle-fav <?php echo $isFav ? 'is-favorited' : ''; ?>" data-tour-id="<?php echo (int)$tour['id']; ?>" aria-label="Yêu thích">
                            <i class="bi <?php echo $isFav ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                        </button>

                        <span class="tour-duration-badge">
                            <i class="bi bi-clock"></i>
                            <span><?php echo htmlspecialchars($tour['duration']); ?></span>
                        </span>

                        <a href="<?php echo htmlspecialchars($detailUrl); ?>" class="tour-thumb-arrow-btn" title="Xem chi tiết" aria-label="Xem chi tiết">
                            <i class="bi bi-arrow-right"></i>
                        </a>

                        <a href="<?php echo htmlspecialchars($detailUrl); ?>">
                            <img src="<?php echo htmlspecialchars($tour['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($tour['name']); ?>" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($fallbackImage); ?>';">
                        </a>
                    </div>

                    <div class="tour-card-body">
                        <h3 class="tour-card-title">
                            <a href="<?php echo htmlspecialchars($detailUrl); ?>">
                                <?php echo htmlspecialchars($tour['name']); ?>
                            </a>
                        </h3>

                        <div class="tour-location-meta">
                            <i class="bi bi-geo-alt"></i>
                            <span><?php echo htmlspecialchars($tour['location']); ?></span>
                        </div>

                        <div class="tour-rating-row">
                            <i class="bi bi-star-fill"></i>
                            <strong><?php echo htmlspecialchars($tour['rating']); ?></strong>
                            <span>(<?php echo htmlspecialchars($tour['reviews']); ?> đánh giá)</span>
                        </div>

                        <div class="tour-amenities-pills">
                            <span><i class="bi bi-car-front-fill"></i> <?php echo htmlspecialchars($tour['amenities'][0] ?? 'Xe ô tô'); ?></span>
                            <span><i class="bi bi-buildings"></i> <?php echo htmlspecialchars($tour['amenities'][1] ?? 'Khách sạn'); ?></span>
                            <span><i class="bi bi-cup-hot-fill"></i> <?php echo htmlspecialchars($tour['amenities'][2] ?? 'Ăn uống'); ?></span>
                        </div>

                        <div class="tour-card-footer">
                            <div>
                                <span class="tour-price-value"><?php echo number_format($tour['price'], 0, ',', '.'); ?>đ</span>
                                <span class="tour-price-unit">/khách</span>
                            </div>
                            <a href="<?php echo htmlspecialchars($detailUrl); ?>" class="btn-book-now">
                                Đặt ngay
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 6. PROMO SPLIT BANNER (Summer 40% Off + 3 Service Cards) -->
        <section class="promo-split-section">
            <!-- Left Large Summer Card -->
            <div class="promo-beach-card">
                <div class="promo-beach-tag">Mùa hè rực rỡ</div>
                <h3 class="promo-beach-title">Giảm đến 40%</h3>
                <p class="promo-beach-subtitle">Cho các tour du lịch biển đảo hot nhất!</p>
                <a href="index.php?act=khachHang/danhSachTour&search=biển" class="btn-promo-action">
                    <span>Khám phá ngay</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Right 3 Stacked Cards -->
            <div class="promo-features-stack">
                <div class="promo-feature-box">
                    <div class="feature-icon-bubble">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div class="feature-text-content">
                        <h4>Đặt tour dễ dàng</h4>
                        <p>Chỉ vài bước đơn giản</p>
                    </div>
                </div>

                <div class="promo-feature-box">
                    <div class="feature-icon-bubble">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="feature-text-content">
                        <h4>Thanh toán an toàn</h4>
                        <p>Bảo mật tuyệt đối</p>
                    </div>
                </div>

                <div class="promo-feature-box">
                    <div class="feature-icon-bubble">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="feature-text-content">
                        <h4>Hỗ trợ 24/7</h4>
                        <p>Luôn sẵn sàng</p>
                    </div>
                    <i class="bi bi-chevron-right feature-arrow-right"></i>
                </div>
            </div>
        </section>

        <!-- 7. ĐIỂM ĐẾN ĐƯỢC YÊU THÍCH (6 Điểm đến) -->
        <section class="mb-5">
            <div class="section-header-block mb-3">
                <h2 class="section-title" style="font-size: 22px;">
                    <i class="bi bi-geo-alt-fill" style="color: #0284c7;"></i>
                    <span>Điểm đến được yêu thích</span>
                </h2>
                <p class="section-subtitle">Top những địa điểm du lịch hấp dẫn nhất</p>
            </div>

            <div class="destinations-grid">
                <!-- 1. Hạ Long -->
                <a href="index.php?act=khachHang/danhSachTour&search=Hạ+Long" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=400&auto=format&fit=crop" alt="Hạ Long">
                        <div class="destination-circle-btn"><i class="bi bi-arrow-right"></i></div>
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">Hạ Long</h4>
                        <p class="destination-province">Quảng Ninh</p>
                    </div>
                </a>

                <!-- 2. Đà Nẵng -->
                <a href="index.php?act=khachHang/danhSachTour&search=Đà+Nẵng" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?q=80&w=400&auto=format&fit=crop" alt="Đà Nẵng">
                        <div class="destination-circle-btn"><i class="bi bi-arrow-right"></i></div>
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">Đà Nẵng</h4>
                        <p class="destination-province">Đà Nẵng</p>
                    </div>
                </a>

                <!-- 3. Phú Quốc -->
                <a href="index.php?act=khachHang/danhSachTour&search=Phú+Quốc" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1589394815804-964ed0be2eb5?q=80&w=400&auto=format&fit=crop" alt="Phú Quốc">
                        <div class="destination-circle-btn"><i class="bi bi-arrow-right"></i></div>
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">Phú Quốc</h4>
                        <p class="destination-province">Kiên Giang</p>
                    </div>
                </a>

                <!-- 4. Nha Trang -->
                <a href="index.php?act=khachHang/danhSachTour&search=Nha+Trang" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=400&auto=format&fit=crop" alt="Nha Trang">
                        <div class="destination-circle-btn"><i class="bi bi-arrow-right"></i></div>
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">Nha Trang</h4>
                        <p class="destination-province">Khánh Hòa</p>
                    </div>
                </a>

                <!-- 5. Hội An -->
                <a href="index.php?act=khachHang/danhSachTour&search=Hội+An" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1578632767115-351597cf2477?q=80&w=400&auto=format&fit=crop" alt="Hội An">
                        <div class="destination-circle-btn"><i class="bi bi-arrow-right"></i></div>
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">Hội An</h4>
                        <p class="destination-province">Quảng Nam</p>
                    </div>
                </a>

                <!-- 6. Sapa -->
                <a href="index.php?act=khachHang/danhSachTour&search=Sa+Pa" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1544085311-11a028465b03?q=80&w=400&auto=format&fit=crop" alt="Sapa">
                        <div class="destination-circle-btn"><i class="bi bi-arrow-right"></i></div>
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">Sapa</h4>
                        <p class="destination-province">Lào Cai</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- 8. KHÁCH HÀNG NÓI GÌ VỀ CHÚNG TÔI? (With Photo Gallery) -->
        <section class="mb-5">
            <div class="section-header-flex">
                <div>
                    <h2 class="section-title" style="font-size: 22px;">
                        <i class="bi bi-star-fill" style="color: #0284c7;"></i>
                        <span>Khách hàng nói gì về chúng tôi?</span>
                    </h2>
                    <p class="section-subtitle">Trải nghiệm thực tế từ những khách hàng đã đồng hành cùng DuLichPro</p>
                </div>
                <a href="index.php?act=khachHang/dashboard#reviews" class="view-all-link">
                    <span>Xem thêm đánh giá</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="testimonials-grid">
                <!-- Review 1 -->
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=150&auto=format&fit=crop" alt="Nguyễn Thị Mai" class="testimonial-avatar">
                        <div>
                            <h4 class="testimonial-author-name">
                                Nguyễn Thị Mai
                                <span class="testimonial-date">12/06/2026</span>
                            </h4>
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="testimonial-quote">“Tour Hạ Long thật tuyệt vời! Dịch vụ chuyên nghiệp, hướng dẫn viên nhiệt tình. Chắc chắn sẽ quay lại!”</p>
                    <div class="testimonial-gallery">
                        <img src="https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1589394815804-964ed0be2eb5?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                    </div>
                </div>

                <!-- Review 2 -->
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150&auto=format&fit=crop" alt="Trần Văn Hùng" class="testimonial-avatar">
                        <div>
                            <h4 class="testimonial-author-name">
                                Trần Văn Hùng
                                <span class="testimonial-date">05/06/2026</span>
                            </h4>
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="testimonial-quote">“Đà Nẵng – Hội An là hành trình không thể bỏ lỡ. Cảnh đẹp, ẩm thực ngon, giá cả hợp lý.”</p>
                    <div class="testimonial-gallery">
                        <img src="https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1578632767115-351597cf2477?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1583417319070-4a69db38a482?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                    </div>
                </div>

                <!-- Review 3 -->
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=150&auto=format&fit=crop" alt="Lê Thị Minh" class="testimonial-avatar">
                        <div>
                            <h4 class="testimonial-author-name">
                                Lê Thị Minh
                                <span class="testimonial-date">28/05/2026</span>
                            </h4>
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="testimonial-quote">“Dịch vụ của DuLichPro rất tốt, hỗ trợ nhanh chóng. Tôi rất hài lòng và sẽ giới thiệu cho bạn bè!”</p>
                    <div class="testimonial-gallery">
                        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1568849676085-51415703900f?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                        <img src="https://images.unsplash.com/photo-1589394815804-964ed0be2eb5?q=80&w=120&auto=format&fit=crop" alt="Review photo">
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- 9. EXECUTIVE FOOTER -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Col 1: Brand -->
                <div class="footer-brand">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-logo-icon" style="width: 38px; height: 38px; font-size: 18px;">
                            <i class="bi bi-tsunami"></i>
                        </div>
                        <div>
                            <h2>DuLichPro</h2>
                            <p>Khám phá thế giới - Trải nghiệm tuyệt vời</p>
                        </div>
                    </div>
                    <p style="font-size: 13px; line-height: 1.6; color: #94a3b8; max-width: 320px;">
                        Đồng hành cùng bạn trên mọi nẻo đường với những tour du lịch đẳng cấp, an toàn và tràn ngập niềm vui.
                    </p>
                </div>

                <!-- Col 2: Liên hệ -->
                <div>
                    <h3 class="footer-heading">Liên hệ</h3>
                    <ul class="footer-contact-list">
                        <li>
                            <i class="bi bi-telephone-fill"></i>
                            <span>1900 1234</span>
                        </li>
                        <li>
                            <i class="bi bi-envelope-fill"></i>
                            <span>support@dulichpro.vn</span>
                        </li>
                        <li>
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>123 Nguyễn Văn Cừ, Quận 1, TP. HCM</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Hỗ trợ -->
                <div>
                    <h3 class="footer-heading">Hỗ trợ</h3>
                    <ul class="footer-links-list">
                        <li><a href="#faq">Câu hỏi thường gặp</a></li>
                        <li><a href="#privacy">Chính sách bảo mật</a></li>
                        <li><a href="#terms">Điều khoản sử dụng</a></li>
                        <li><a href="index.php?act=khachHang/guiYeuCauHoTro">Gửi yêu cầu hỗ trợ</a></li>
                    </ul>
                </div>

                <!-- Col 4: Mạng xã hội & Tải ứng dụng -->
                <div>
                    <h3 class="footer-heading">Kết nối với chúng tôi</h3>
                    <div class="footer-socials">
                        <a href="#" class="footer-social-btn" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-social-btn" aria-label="Youtube"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="footer-social-btn" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-social-btn" aria-label="Tiktok"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="footer-social-btn" aria-label="Pinterest"><i class="bi bi-pinterest"></i></a>
                    </div>

                    <h3 class="footer-heading" style="font-size: 13.5px; margin-bottom: 10px;">Tải ứng dụng ngay</h3>
                    <div class="app-download-badges">
                        <a href="#" class="app-store-pill">
                            <i class="bi bi-apple"></i>
                            <div>
                                <span style="font-size: 9px; display: block; opacity: 0.8;">Tải trên</span>
                                <strong>App Store</strong>
                            </div>
                        </a>
                        <a href="#" class="app-store-pill">
                            <i class="bi bi-google-play"></i>
                            <div>
                                <span style="font-size: 9px; display: block; opacity: 0.8;">Tải trên</span>
                                <strong>Google Play</strong>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar with Newsletter Form -->
            <div class="footer-bottom-bar">
                <span>© 2026 DuLichPro. All rights reserved.</span>

                <form class="footer-newsletter-box" onsubmit="alert('Cảm ơn bạn đã đăng ký nhận ưu đãi từ DuLichPro!'); return false;">
                    <input type="email" class="footer-newsletter-input" placeholder="Nhập email để nhận ưu đãi mới nhất" required>
                    <button type="submit" class="footer-newsletter-btn">Đăng ký</button>
                </form>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var csrfToken = <?php echo json_encode(csrfToken('global_form'), JSON_UNESCAPED_UNICODE); ?>;

        // Toggle Favorite Heart Button AJAX
        document.querySelectorAll('.js-toggle-fav').forEach(function (button) {
            button.addEventListener('click', async function (e) {
                e.preventDefault();
                e.stopPropagation();

                var tourId = this.getAttribute('data-tour-id');
                if (!tourId) return;

                this.disabled = true;
                try {
                    var formData = new URLSearchParams();
                    formData.append('tour_id', tourId);
                    formData.append('_csrf_global', csrfToken);

                    var response = await fetch('index.php?act=khachHang/toggleYeuThich', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData.toString()
                    });
                    var data = await response.json();
                    if (data && data.success) {
                        var icon = this.querySelector('i');
                        if (data.is_favorite) {
                            this.classList.add('is-favorited');
                            if (icon) icon.className = 'bi bi-heart-fill';
                        } else {
                            this.classList.remove('is-favorited');
                            if (icon) icon.className = 'bi bi-heart';
                        }
                    }
                } catch (err) {
                    console.error('Favorite error:', err);
                } finally {
                    this.disabled = false;
                }
            });
        });

        // ── Horizontal Tour Slider Navigation & Drag-to-Scroll ──
        var slider = document.getElementById('toursSlider');
        var prevBtn = document.getElementById('prevToursBtn');
        var nextBtn = document.getElementById('nextToursBtn');

        if (slider) {
            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    slider.scrollBy({ left: -305, behavior: 'smooth' });
                });
            }
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    slider.scrollBy({ left: 305, behavior: 'smooth' });
                });
            }

            // Drag to scroll via mouse
            var isDown = false;
            var startX;
            var scrollLeft;

            slider.addEventListener('mousedown', function (e) {
                isDown = true;
                slider.classList.add('is-dragging');
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            window.addEventListener('mouseup', function () {
                if (isDown) {
                    isDown = false;
                    slider.classList.remove('is-dragging');
                }
            });

            slider.addEventListener('mouseleave', function () {
                if (isDown) {
                    isDown = false;
                    slider.classList.remove('is-dragging');
                }
            });

            slider.addEventListener('mousemove', function (e) {
                if (!isDown) return;
                e.preventDefault();
                var x = e.pageX - slider.offsetLeft;
                var walk = (x - startX) * 1.5;
                slider.scrollLeft = scrollLeft - walk;
            });
        }
    });
    </script>
</body>
</html>
