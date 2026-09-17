<?php
/** @var array $danhGiaTot */
/** @var array $allTours */
/** @var array $bookings */
/** @var array $khachHang */
$favoriteTourIds = isset($favoriteTourIds) && is_array($favoriteTourIds) ? $favoriteTourIds : [];
$allToursList = isset($allTours) && is_array($allTours) ? array_values($allTours) : [];
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DuLichPro - Khám phá thế giới, Trải nghiệm tuyệt vời</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
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
            --hover-shadow: 0 20px 30px -10px rgba(0, 102, 204, 0.15);
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

        /* HERO BANNER */
        .hero-section {
            position: relative;
            background-image: linear-gradient(to right, rgba(15, 23, 42, 0.65) 0%, rgba(15, 23, 42, 0.25) 55%, rgba(15, 23, 42, 0.15) 100%), 
                              url('https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=1920&auto=format&fit=crop');
            background-position: center 45%;
            background-size: cover;
            min-height: 480px;
            display: flex;
            align-items: center;
            position: relative;
            padding: 60px 0 100px;
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
            font-size: 22px;
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
        }

        .hero-title .highlight {
            color: #38bdf8;
        }

        .hero-subtitle {
            font-size: 15px;
            line-height: 1.6;
            color: #f1f5f9;
            max-width: 480px;
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

        /* Hero Right Calligraphy */
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
            font-size: 34px;
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            line-height: 1.2;
            display: block;
        }

        /* Hero Slider Arrow Buttons */
        .hero-slider-btn {
            position: absolute;
            top: 48%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 5;
        }

        .hero-slider-btn:hover {
            background: rgba(255, 255, 255, 0.5);
            color: #0f172a;
        }

        .hero-slider-prev { left: 24px; }
        .hero-slider-next { right: 24px; }

        /* FLOATING SEARCH CARD */
        .search-card-wrapper {
            max-width: 1200px;
            margin: -65px auto 50px;
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

        /* Search Tabs */
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

        /* Search Input Row */
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
            position: relative;
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
            box-shadow: 0 8px 22px rgba(0, 102, 204, 0.35);
        }

        /* SECTION STYLING */
        .page-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-header-block {
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px 0;
            letter-spacing: -0.3px;
        }

        .section-subtitle {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .section-header-flex {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
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

        /* CATEGORIES (Danh mục nổi bật) */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-bottom: 56px;
        }

        .category-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px 14px;
            text-align: center;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .category-card:hover {
            border-color: #93c5fd;
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -6px rgba(0, 102, 204, 0.12);
        }

        .category-icon-circle {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #f0f7ff;
            color: #0066cc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .category-card:hover .category-icon-circle {
            background: var(--primary);
            color: #ffffff;
        }

        .category-name {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 4px 0;
        }

        .category-desc {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
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

        /* FEATURED TOURS (Tour du lịch nổi bật - Horizontal Row) */
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
            height: 190px;
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
            background: rgba(255, 255, 255, 0.85);
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

        .btn-favorite-heart.is-favorited i {
            font-weight: 900;
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

        /* SPECIAL PROMOTIONS (Ưu đãi đặc biệt) */
        .promo-banners-grid {
            display: grid;
            grid-template-columns: 1.6fr 1.1fr;
            gap: 20px;
            margin-bottom: 56px;
        }

        /* Big Left Promo */
        .promo-banner-large {
            background-image: linear-gradient(to right, rgba(15, 23, 42, 0.8) 0%, rgba(15, 23, 42, 0.3) 60%, rgba(15, 23, 42, 0.1) 100%), 
                              url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1200&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            padding: 38px 40px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 240px;
            position: relative;
        }

        .promo-badge-tag {
            font-size: 12.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #bae6fd;
            margin-bottom: 8px;
        }

        .promo-large-title {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.2;
            margin: 0 0 8px 0;
        }

        .promo-large-subtitle {
            font-size: 15px;
            color: #e2e8f0;
            margin: 0 0 24px 0;
        }

        .btn-promo-white {
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

        .btn-promo-white:hover {
            background: #f1f5f9;
            color: var(--primary);
            transform: translateX(2px);
        }

        /* Right Stacked Promos */
        .promo-right-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .promo-mini-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            flex: 1;
            transition: all 0.2s ease;
        }

        .promo-mini-card:hover {
            transform: translateY(-2px);
            border-color: #93c5fd;
            box-shadow: 0 10px 20px -5px rgba(0, 102, 204, 0.1);
        }

        .promo-mini-content h4 {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }

        .promo-mini-content p {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 12px 0;
        }

        .promo-link-arrow {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .promo-mini-img {
            width: 120px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            margin-left: 16px;
        }

        /* POPULAR DESTINATIONS (Điểm đến được yêu thích) */
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
        }

        .destination-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .destination-img-wrap {
            height: 125px;
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

        .destination-meta {
            padding: 12px 14px;
        }

        .destination-name {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px 0;
        }

        .destination-name i {
            color: #0284c7;
            font-size: 13px;
        }

        .destination-country {
            font-size: 12px;
            color: #64748b;
            margin: 0;
            padding-left: 18px;
        }

        /* WHY CHOOSE US (Tại sao chọn DuLichPro) */
        .why-choose-us-wrap {
            background: linear-gradient(180deg, #f0f7ff 0%, #ffffff 100%);
            border: 1px solid #e0f2fe;
            border-radius: 24px;
            padding: 40px 36px;
            margin-bottom: 56px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 36px;
            padding-bottom: 32px;
            border-bottom: 1px dashed #cbd5e1;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px 18px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(0, 102, 204, 0.04);
            transition: all 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #93c5fd;
            box-shadow: 0 12px 24px -5px rgba(0, 102, 204, 0.12);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: #0066cc;
            line-height: 1.1;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .stat-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px 0;
        }

        .stat-desc {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .value-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .value-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #e0f2fe;
            color: #0066cc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .value-card:hover .value-icon-box {
            background: #0066cc;
            color: #ffffff;
            transform: scale(1.05);
        }

        .value-content h4 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }

        .value-content p {
            font-size: 12.5px;
            line-height: 1.55;
            color: #64748b;
            margin: 0;
        }

        /* TESTIMONIALS (Khách hàng nói gì về chúng tôi?) */
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
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }

        .testimonial-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -5px rgba(0,0,0,0.06);
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
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
            margin: 0 0 3px 0;
        }

        .testimonial-stars {
            color: #f59e0b;
            font-size: 12px;
            display: flex;
            gap: 2px;
        }

        .testimonial-quote {
            font-size: 13.5px;
            line-height: 1.6;
            color: #475569;
            margin: 0;
            font-style: normal;
        }

        /* FOOTER */
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

        .footer-brand .brand-logo-icon {
            box-shadow: none;
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
            letter-spacing: -0.2px;
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

        .app-store-pill i {
            font-size: 18px;
        }

        .footer-bottom-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #64748b;
        }

        /* SCROLL TO TOP FLOATING BUTTON */
        .btn-scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #0f172a;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            z-index: 990;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-scroll-top:hover {
            background: var(--primary);
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* QUICK TOUR REQUEST MODAL & CHATBOT TOGGLE */
        .floating-chat-trigger {
            position: fixed;
            bottom: 84px;
            right: 30px;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #0284c7;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.35);
            cursor: pointer;
            z-index: 990;
            border: none;
            transition: all 0.2s;
        }

        .floating-chat-trigger:hover {
            background: #0369a1;
            transform: scale(1.05);
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 1024px) {
            .category-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .destinations-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .values-grid {
                grid-template-columns: repeat(2, 1fr);
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
                font-size: 32px;
            }
            .hero-right-calligraphy {
                display: none;
            }
            .search-fields-row {
                grid-template-columns: 1fr;
            }
            .btn-submit-search {
                width: 100%;
            }
            .promo-banners-grid {
                grid-template-columns: 1fr;
            }
            .testimonials-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .values-grid {
                grid-template-columns: 1fr;
            }
            .tour-card {
                flex: 0 0 265px;
                width: 265px;
                min-width: 265px;
            }
            .why-choose-us-wrap {
                padding: 26px 20px;
            }
            .destinations-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .tours-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- 1. TOP NAVBAR (Đồng bộ chuẩn Image 1) -->
    <header class="site-header">
        <div class="header-container">
            <!-- Brand Logo -->
            <a href="index.php?act=khachHang/dashboard" class="brand-block">
                <div class="brand-logo-icon">
                    <i class="bi bi-tsunami"></i>
                </div>
                <div class="brand-text">
                    <h1>DuLichPro</h1>
                    <p>Khám phá thế giới - Trải nghiệm tuyệt vời</p>
                </div>
            </a>

            <!-- Navigation Links -->
            <ul class="site-nav">
                <li><a href="index.php?act=khachHang/dashboard" class="active">Trang chủ</a></li>
                <li><a href="index.php?act=khachHang/danhSachTour">Tour du lịch</a></li>
                <li><a href="index.php?act=khachHang/dashboard#hotels">Khách sạn</a></li>
                <li><a href="index.php?act=khachHang/dashboard#flights">Vé máy bay</a></li>
                <li><a href="index.php?act=khachHang/dashboard#combos">Combo</a></li>
                <li><a href="index.php?act=khachHang/dashboard#visa">Visa</a></li>
                <li><a href="index.php?act=khachHang/dashboard#about">Về chúng tôi</a></li>
            </ul>

            <!-- Header Right Actions -->
            <div class="header-actions">
                <a href="index.php?act=khachHang/danhSachTour" class="btn-icon-round" title="Tìm kiếm">
                    <i class="bi bi-search"></i>
                </a>

                <?php if ($isLoggedIn): ?>
                    <!-- Notification Bell -->
                    <a href="index.php?act=khachHang/thongBao" class="btn-icon-round" title="Thông báo">
                        <i class="bi bi-bell"></i>
                        <span class="badge-count">3</span>
                    </a>

                    <!-- User Profile Dropdown Pill (Chuẩn Image 1) -->
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

    <!-- FLASH MESSAGES -->
    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <div class="page-container mt-3">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- 2. HERO BANNER -->
    <section class="hero-section">
        <button class="hero-slider-btn hero-slider-prev" aria-label="Previous slide">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button class="hero-slider-btn hero-slider-next" aria-label="Next slide">
            <i class="bi bi-chevron-right"></i>
        </button>

        <div class="hero-container">
            <div class="hero-vietnam-tag">
                <span>Viet Nam</span>
                <i class="bi bi-send-fill"></i>
            </div>

            <h1 class="hero-title">
                Đi để khám phá<br>
                Đi để <span class="highlight">trưởng thành</span>
            </h1>

            <p class="hero-subtitle">
                Những hành trình tuyệt vời đang chờ bạn khám phá. Cùng DuLichPro trải nghiệm những điểm đến đẹp nhất trong và ngoài nước với dịch vụ chuyên nghiệp, uy tín.
            </p>

            <div class="trust-badges">
                <div class="trust-pill">
                    <i class="bi bi-tag-fill"></i>
                    <span>Giá tốt nhất thị trường</span>
                </div>
                <div class="trust-pill">
                    <i class="bi bi-clock-history"></i>
                    <span>Hỗ trợ 24/7 mọi hành trình</span>
                </div>
                <div class="trust-pill">
                    <i class="bi bi-shield-check"></i>
                    <span>Đảm bảo an toàn và chất lượng</span>
                </div>
                <div class="trust-pill">
                    <i class="bi bi-wallet2"></i>
                    <span>Thanh toán linh hoạt nhiều phương thức</span>
                </div>
            </div>
        </div>

        <!-- Right Calligraphy Accent -->
        <div class="hero-right-calligraphy">
            <span class="script-text">Khám phá<br>những vùng đất mới</span>
        </div>
    </section>

    <!-- 3. FLOATING BOOKING FILTER CARD -->
    <div class="search-card-wrapper">
        <div class="search-card">
            <!-- Filter Tabs -->
            <div class="search-tabs">
                <button type="button" class="search-tab-btn active" data-type="tour">
                    <i class="bi bi-compass-fill"></i>
                    <span>Tour du lịch</span>
                </button>
                <button type="button" class="search-tab-btn" data-type="hotel">
                    <i class="bi bi-building"></i>
                    <span>Khách sạn</span>
                </button>
                <button type="button" class="search-tab-btn" data-type="flight">
                    <i class="bi bi-airplane-engines"></i>
                    <span>Vé máy bay</span>
                </button>
                <button type="button" class="search-tab-btn" data-type="combo">
                    <i class="bi bi-gift"></i>
                    <span>Combo</span>
                </button>
                <button type="button" class="search-tab-btn" data-type="visa">
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
                            <span>Bạn muốn đi đâu?</span>
                        </div>
                        <input type="text" name="search" class="search-field-input" placeholder="VD: Đà Nẵng, Phú Quốc, Nhật Bản..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>

                    <!-- Field 2: Departure Date -->
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

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit-search">
                        <i class="bi bi-search"></i>
                        <span>Tìm kiếm</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN PAGE CONTENT -->
    <main class="page-container">

        <!-- 4. DANH MỤC NỔI BẬT -->
        <section class="mb-5">
            <div class="section-header-block">
                <h2 class="section-title">Danh mục nổi bật</h2>
                <p class="section-subtitle">Khám phá các dịch vụ du lịch đa dạng, đáp ứng mọi nhu cầu của bạn</p>
            </div>

            <div class="category-grid">
                <!-- 1. Tour du lịch -->
                <a href="index.php?act=khachHang/danhSachTour" class="category-card">
                    <div class="category-icon-circle">
                        <i class="bi bi-luggage"></i>
                    </div>
                    <h3 class="category-name">Tour du lịch</h3>
                    <p class="category-desc">Trong nước & quốc tế</p>
                </a>

                <!-- 2. Khách sạn -->
                <a href="#hotels" class="category-card" onclick="openQuickRequestModal('Khách sạn'); return false;">
                    <div class="category-icon-circle">
                        <i class="bi bi-building"></i>
                    </div>
                    <h3 class="category-name">Khách sạn</h3>
                    <p class="category-desc">Đặt phòng dễ dàng</p>
                </a>

                <!-- 3. Vé máy bay -->
                <a href="#flights" class="category-card" onclick="openQuickRequestModal('Vé máy bay'); return false;">
                    <div class="category-icon-circle">
                        <i class="bi bi-airplane"></i>
                    </div>
                    <h3 class="category-name">Vé máy bay</h3>
                    <p class="category-desc">Bay khắp thế giới</p>
                </a>

                <!-- 4. Combo -->
                <a href="#combo" class="category-card" onclick="openQuickRequestModal('Combo du lịch'); return false;">
                    <div class="category-icon-circle">
                        <i class="bi bi-gift"></i>
                    </div>
                    <h3 class="category-name">Combo</h3>
                    <p class="category-desc">Tiết kiệm hơn</p>
                </a>

                <!-- 5. Visa -->
                <a href="#visa" class="category-card" onclick="openQuickRequestModal('Tư vấn Visa'); return false;">
                    <div class="category-icon-circle">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <h3 class="category-name">Visa</h3>
                    <p class="category-desc">Thủ tục nhanh gọn</p>
                </a>

                <!-- 6. Tất cả dịch vụ -->
                <a href="index.php?act=khachHang/danhSachTour" class="category-card">
                    <div class="category-icon-circle">
                        <i class="bi bi-arrow-right-circle"></i>
                    </div>
                    <h3 class="category-name">Tất cả dịch vụ</h3>
                    <p class="category-desc">Khám phá ngay</p>
                </a>
            </div>
        </section>

        <!-- 5. TOUR DU LỊCH NỔI BẬT -->
        <section class="mb-5">
            <div class="section-header-flex">
                <div>
                    <h2 class="section-title">Tour du lịch nổi bật</h2>
                    <p class="section-subtitle">Những hành trình được yêu thích và đánh giá cao nhất — kéo sang ngang để xem thêm</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn-slider-nav" id="prevToursHomeBtn" aria-label="Cuộn sang trái" title="Cuộn sang trái">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn-slider-nav" id="nextToursHomeBtn" aria-label="Cuộn sang phải" title="Cuộn sang phải">
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
                <div class="tours-grid" id="toursSliderHome">
                <?php
                // Chuẩn bị 4 tour tiêu biểu từ dữ liệu động hoặc tour mẫu đẹp mắt
                $featuredTours = [
                    [
                        'id' => 1,
                        'name' => 'Tour Hạ Long 2 ngày 1 đêm',
                        'location' => 'Quảng Ninh',
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
                        'rating' => '4.6',
                        'reviews' => '280',
                        'amenities' => ['Xe ô tô', 'Vé tham quan', 'Ăn uống'],
                        'price' => 1980000,
                        'badge' => 'Tiết kiệm',
                        'badge_class' => 'badge-tietkiem',
                        'image' => 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?q=80&w=600&auto=format&fit=crop'
                    ]
                ];

                // Hàm hỗ trợ chuẩn hóa đường dẫn hình ảnh (tuyệt đối vs tương đối)
                $resolveTourImage = static function ($image, $fallback = '') {
                    $image = trim((string)$image);
                    if ($image === '') {
                        return $fallback;
                    }
                    if (preg_match('/^https?:\/\//i', $image)) {
                        return $image;
                    }
                    return rtrim(BASE_URL, '/') . '/' . ltrim($image, '/');
                };

                // Nếu trong cơ sở dữ liệu có các tour thật, ta hợp nhất hiển thị
                if (!empty($allToursList)) {
                    $badgeTypes = [
                        ['badge' => 'Bán chạy', 'class' => 'badge-banchay'],
                        ['badge' => 'Khuyến mãi', 'class' => 'badge-khuyenmai'],
                        ['badge' => 'Hot', 'class' => 'badge-hot'],
                        ['badge' => 'Tiết kiệm', 'class' => 'badge-tietkiem']
                    ];
                    $idx = 0;
                    foreach (array_slice($allToursList, 0, 4) as $dbTour) {
                        $tourId = (int)($dbTour['tour_id'] ?? 0);
                        $tourName = $dbTour['ten_tour'] ?? '';
                        $fallbackImg = $featuredTours[$idx]['image'] ?? 'https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=600&auto=format&fit=crop';
                        $tourImg = $resolveTourImage($dbTour['hinh_anh'] ?? '', $fallbackImg);

                        $rawPrice = !empty($dbTour['gia_co_ban']) ? (float)$dbTour['gia_co_ban'] : (float)($dbTour['gia_tour'] ?? 0);
                        $tourPrice = $rawPrice > 0 ? $rawPrice : ($featuredTours[$idx]['price'] ?? 2000000);

                        $ratingData = $dbTour['rating'] ?? [];
                        $avgRating = !empty($ratingData['diem_tb']) ? number_format((float)$ratingData['diem_tb'], 1) : ($featuredTours[$idx]['rating'] ?? '4.8');
                        $reviewCount = !empty($ratingData['so_danh_gia']) ? (int)$ratingData['so_danh_gia'] : ($featuredTours[$idx]['reviews'] ?? '150');

                        $location = !empty($dbTour['dia_diem']) ? $dbTour['dia_diem'] : '';
                        if ($location === '') {
                            if (!empty($dbTour['diem_tap_trung'])) {
                                $location = $dbTour['diem_tap_trung'];
                            } elseif (mb_stripos($tourName, 'Sơn Tây') !== false || mb_stripos($tourName, 'Đường Lâm') !== false) {
                                $location = 'Hà Nội';
                            } elseif (mb_stripos($tourName, 'Đà Lạt') !== false) {
                                $location = 'Lâm Đồng - Đà Lạt';
                            } elseif (mb_stripos($tourName, 'Phú Sĩ') !== false || mb_stripos($tourName, 'Tokyo') !== false || mb_stripos($tourName, 'Nagoya') !== false) {
                                $location = 'Nhật Bản';
                            } else {
                                $location = ($dbTour['loai_tour'] ?? '') === 'QuocTe' ? 'Tour Quốc Tế' : 'Việt Nam';
                            }
                        }

                        $featuredTours[$idx] = [
                            'id' => $tourId,
                            'name' => $tourName ?: $featuredTours[$idx]['name'],
                            'location' => $location,
                            'rating' => $avgRating,
                            'reviews' => $reviewCount,
                            'amenities' => $featuredTours[$idx]['amenities'] ?? ['Xe ô tô', 'Khách sạn', 'Ăn uống'],
                            'price' => $tourPrice,
                            'badge' => $badgeTypes[$idx]['badge'],
                            'badge_class' => $badgeTypes[$idx]['class'],
                            'image' => $tourImg,
                            'fallback_image' => $fallbackImg
                        ];
                        $idx++;
                    }
                }

                foreach ($featuredTours as $tour):
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

        <!-- 6. ƯU ĐÃI ĐẶC BIỆT -->
        <section class="promo-banners-grid">
            <!-- Left Large Promo Card -->
            <div class="promo-banner-large">
                <div class="promo-badge-tag">Ưu đãi đặc biệt</div>
                <h3 class="promo-large-title">Giảm đến 30%</h3>
                <p class="promo-large-subtitle">Cho các tour du lịch trong nước</p>
                <a href="index.php?act=khachHang/danhSachTour" class="btn-promo-white">
                    <span>Xem ngay</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Right Stacked Cards -->
            <div class="promo-right-stack">
                <!-- Flight Promo -->
                <div class="promo-mini-card">
                    <div class="promo-mini-content">
                        <h4>Vé máy bay giá tốt</h4>
                        <p>Bay khắp muôn nơi</p>
                        <a href="index.php?act=khachHang/guiYeuCauTour" class="promo-link-arrow">
                            <span>Đặt ngay</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <img src="https://images.unsplash.com/photo-1436491865332-7a61a109cc05?q=80&w=300&auto=format&fit=crop" alt="Vé máy bay" class="promo-mini-img">
                </div>

                <!-- Hotel Promo -->
                <div class="promo-mini-card">
                    <div class="promo-mini-content">
                        <h4>Khách sạn cao cấp</h4>
                        <p>Nghỉ dưỡng đẳng cấp</p>
                        <a href="index.php?act=khachHang/guiYeuCauTour" class="promo-link-arrow">
                            <span>Đặt ngay</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <img src="https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=300&auto=format&fit=crop" alt="Khách sạn cao cấp" class="promo-mini-img">
                </div>
            </div>
        </section>

        <!-- 7. ĐIỂM ĐẾN ĐƯỢC YÊU THÍCH -->
        <section class="mb-5">
            <div class="section-header-block">
                <h2 class="section-title">Điểm đến được yêu thích</h2>
                <p class="section-subtitle">Những địa điểm hot nhất hiện nay</p>
            </div>

            <div class="destinations-grid">
                <!-- 1. Đà Nẵng -->
                <a href="index.php?act=khachHang/danhSachTour&search=Đà+Nẵng" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?q=80&w=400&auto=format&fit=crop" alt="Đà Nẵng">
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">
                            <i class="bi bi-geo-alt-fill"></i> Đà Nẵng
                        </h4>
                        <p class="destination-country">Việt Nam</p>
                    </div>
                </a>

                <!-- 2. Phú Quốc -->
                <a href="index.php?act=khachHang/danhSachTour&search=Phú+Quốc" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1589394815804-964ed0be2eb5?q=80&w=400&auto=format&fit=crop" alt="Phú Quốc">
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">
                            <i class="bi bi-geo-alt-fill"></i> Phú Quốc
                        </h4>
                        <p class="destination-country">Việt Nam</p>
                    </div>
                </a>

                <!-- 3. Hạ Long -->
                <a href="index.php?act=khachHang/danhSachTour&search=Hạ+Long" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=400&auto=format&fit=crop" alt="Hạ Long">
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">
                            <i class="bi bi-geo-alt-fill"></i> Hạ Long
                        </h4>
                        <p class="destination-country">Việt Nam</p>
                    </div>
                </a>

                <!-- 4. Nha Trang -->
                <a href="index.php?act=khachHang/danhSachTour&search=Nha+Trang" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=400&auto=format&fit=crop" alt="Nha Trang">
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">
                            <i class="bi bi-geo-alt-fill"></i> Nha Trang
                        </h4>
                        <p class="destination-country">Việt Nam</p>
                    </div>
                </a>

                <!-- 5. Đà Lạt -->
                <a href="index.php?act=khachHang/danhSachTour&search=Đà+Lạt" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1568849676085-51415703900f?q=80&w=400&auto=format&fit=crop" alt="Đà Lạt">
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">
                            <i class="bi bi-geo-alt-fill"></i> Đà Lạt
                        </h4>
                        <p class="destination-country">Việt Nam</p>
                    </div>
                </a>

                <!-- 6. Hội An -->
                <a href="index.php?act=khachHang/danhSachTour&search=Hội+An" class="destination-card">
                    <div class="destination-img-wrap">
                        <img src="https://images.unsplash.com/photo-1578632767115-351597cf2477?q=80&w=400&auto=format&fit=crop" alt="Hội An">
                    </div>
                    <div class="destination-meta">
                        <h4 class="destination-name">
                            <i class="bi bi-geo-alt-fill"></i> Hội An
                        </h4>
                        <p class="destination-country">Việt Nam</p>
                    </div>
                </a>
            </div>
        </section>

        <!-- 8. TẠI SAO CHỌN DULICHPRO (STATISTICS & CORE VALUES) -->
        <section class="why-choose-us-wrap">
            <div class="section-header-block text-center mb-4">
                <h2 class="section-title">Tại sao chọn DuLichPro?</h2>
                <p class="section-subtitle">Đồng hành cùng hàng triệu du khách khám phá những vùng đất mới với chất lượng dịch vụ chuẩn mực</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">50.000+</div>
                    <h4 class="stat-title">Khách hàng tin tưởng</h4>
                    <p class="stat-desc">Đã đồng hành trên mọi nẻo đường</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">500+</div>
                    <h4 class="stat-title">Tour du lịch đa dạng</h4>
                    <p class="stat-desc">Điểm đến hấp dẫn trong & quốc tế</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">10+</div>
                    <h4 class="stat-title">Năm kinh nghiệm</h4>
                    <p class="stat-desc">Đội ngũ hướng dẫn viên chuyên nghiệp</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number">99.2%</div>
                    <h4 class="stat-title">Đánh giá hài lòng</h4>
                    <p class="stat-desc">Phản hồi 5 sao từ quý khách hàng</p>
                </div>
            </div>

            <!-- Values / Pillars Grid -->
            <div class="values-grid">
                <!-- Pillar 1 -->
                <div class="value-card">
                    <div class="value-icon-box">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <div class="value-content">
                        <h4>Giá tốt & Minh bạch</h4>
                        <p>Cam kết giá cạnh tranh nhất, không phí ẩn và chính sách hoàn hủy rõ ràng.</p>
                    </div>
                </div>

                <!-- Pillar 2 -->
                <div class="value-card">
                    <div class="value-icon-box">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="value-content">
                        <h4>An toàn & Chu đáo</h4>
                        <p>Bảo hiểm du lịch trọn gói cao cấp cùng hệ sinh thái đối tác hàng không, khách sạn 4-5 sao.</p>
                    </div>
                </div>

                <!-- Pillar 3 -->
                <div class="value-card">
                    <div class="value-icon-box">
                        <i class="bi bi-compass-fill"></i>
                    </div>
                    <div class="value-content">
                        <h4>Hành trình độc đáo</h4>
                        <p>Lịch trình được tối ưu kỹ lưỡng, kết hợp tham quan danh lam thắng cảnh và ẩm thực bản địa.</p>
                    </div>
                </div>

                <!-- Pillar 4 -->
                <div class="value-card">
                    <div class="value-icon-box">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div class="value-content">
                        <h4>Hỗ trợ 24/7 tức thời</h4>
                        <p>Chuyên viên chăm sóc khách hàng và hướng dẫn viên sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 9. KHÁCH HÀNG NÓI GÌ VỀ CHÚNG TÔI? -->
        <section class="mb-5">
            <div class="section-header-block">
                <h2 class="section-title">Khách hàng nói gì về chúng tôi?</h2>
                <p class="section-subtitle">Hàng nghìn khách hàng đã tin tưởng và lựa chọn DuLichPro</p>
            </div>

            <div class="testimonials-grid">
                <?php
                $testimonials = [
                    [
                        'name' => 'Nguyễn Thị Mai',
                        'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=150&auto=format&fit=crop',
                        'quote' => 'Dịch vụ rất chuyên nghiệp, hướng dẫn viên nhiệt tình. Chuyến đi thật tuyệt vời!'
                    ],
                    [
                        'name' => 'Trần Văn Hùng',
                        'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150&auto=format&fit=crop',
                        'quote' => 'Giá cả hợp lý, thủ tục nhanh gọn. Sẽ tiếp tục ủng hộ DuLichPro trong những chuyến đi tới!'
                    ],
                    [
                        'name' => 'Lê Thị Hương',
                        'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=150&auto=format&fit=crop',
                        'quote' => 'Tôi rất hài lòng với chất lượng dịch vụ. Một trải nghiệm đáng nhớ!'
                    ]
                ];

                // Nếu có đánh giá thật trong cơ sở dữ liệu thì ánh xạ hiển thị
                if (!empty($danhGiaTot) && is_array($danhGiaTot)) {
                    $tIndex = 0;
                    foreach (array_slice($danhGiaTot, 0, 3) as $dg) {
                        if (!empty($dg['binh_luan'])) {
                            $testimonials[$tIndex]['name'] = $dg['ho_ten'] ?? $testimonials[$tIndex]['name'];
                            $testimonials[$tIndex]['quote'] = $dg['binh_luan'];
                            $tIndex++;
                        }
                    }
                }

                foreach ($testimonials as $item):
                ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <img src="<?php echo htmlspecialchars($item['avatar']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="testimonial-avatar">
                        <div>
                            <h4 class="testimonial-author-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                        </div>
                    </div>
                    <p class="testimonial-quote">“<?php echo htmlspecialchars($item['quote']); ?>”</p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <!-- 9. EXECUTIVE NAVY FOOTER -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Col 1: Brand & Intro -->
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

                <!-- Col 4: Kết nối với chúng tôi & Tải ứng dụng -->
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

            <!-- Footer Bottom Bar -->
            <div class="footer-bottom-bar">
                <span>© 2026 DuLichPro. All rights reserved.</span>
                <span>Du lịch an toàn - Trải nghiệm trọn vẹn <i class="bi bi-heart-fill text-danger"></i></span>
            </div>
        </div>
    </footer>

    <!-- SCROLL TO TOP BUTTON -->
    <a href="#" class="btn-scroll-top" id="scrollTopBtn" title="Lên đầu trang" aria-label="Lên đầu trang">
        <i class="bi bi-arrow-up"></i>
    </a>

    <!-- FLOATING CHATBOT / SUPPORT TRIGGER -->
    <button type="button" class="floating-chat-trigger" onclick="openQuickRequestModal('Tư vấn nhanh');" title="Gửi yêu cầu tour" aria-label="Hỗ trợ">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <!-- QUICK TOUR REQUEST MODAL -->
    <div class="modal fade" id="quickTourModal" tabindex="-1" aria-labelledby="quickTourModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 18px; border: none; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: #0066cc; color: #fff; padding: 20px 24px;">
                    <h5 class="modal-title fw-bold fs-6" id="quickTourModalLabel">
                        <i class="bi bi-send-check me-2"></i>Gửi yêu cầu dịch vụ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickTourRequestForm" action="index.php?act=khachHang/guiYeuCauTour" method="POST">
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Điền thông tin — chuyên viên DuLichPro sẽ liên hệ tư vấn hành trình phù hợp nhất cho bạn trong 15 phút.</p>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Dịch vụ / Địa điểm mong muốn</label>
                            <input type="text" name="dia_diem" id="quickServiceField" class="form-control" placeholder="VD: Tour Đà Nẵng 3N2Đ, Khách sạn Phú Quốc..." required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark">Ngày đi</label>
                                <input type="date" name="arrival_date" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark">Ngày về</label>
                                <input type="date" name="departure_date" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Số lượng khách</label>
                            <input type="number" name="so_nguoi" class="form-control" placeholder="VD: 2" min="1" value="2">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Yêu cầu đặc biệt</label>
                            <textarea name="yeu_cau_dac_biet" class="form-control" rows="3" placeholder="Khách sạn 4 sao, ăn chay, hướng biển..."></textarea>
                        </div>

                        <div id="quickTourRequestFeedback" style="display:none;" class="small mt-2"></div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" style="background:#0066cc;">
                            GỬI YÊU CẦU
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Favorite Heart Button AJAX
        document.querySelectorAll('.js-toggle-fav').forEach(function (button) {
            button.addEventListener('click', async function (e) {
                e.preventDefault();
                e.stopPropagation();

                var tourId = this.getAttribute('data-tour-id');
                if (!tourId) return;

                this.disabled = true;
                try {
                    var response = await fetch('index.php?act=khachHang/toggleFavoriteTour', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ tour_id: tourId })
                    });
                    var data = await response.json();
                    if (data && data.success) {
                        var icon = this.querySelector('i');
                        if (data.is_favorite) {
                            this.classList.add('is-favorited');
                            if (icon) {
                                icon.className = 'bi bi-heart-fill';
                            }
                        } else {
                            this.classList.remove('is-favorited');
                            if (icon) {
                                icon.className = 'bi bi-heart';
                            }
                        }
                    }
                } catch (err) {
                    console.error('Favorite error:', err);
                } finally {
                    this.disabled = false;
                }
            });
        });

        // Search Tabs Selection
        document.querySelectorAll('.search-tab-btn').forEach(function(tab) {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.search-tab-btn').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Scroll to Top Smooth
        var scrollBtn = document.getElementById('scrollTopBtn');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                scrollBtn.style.opacity = '1';
                scrollBtn.style.pointerEvents = 'auto';
            } else {
                scrollBtn.style.opacity = '0';
                scrollBtn.style.pointerEvents = 'none';
            }
        });

        // Quick Request Modal Helper
        window.openQuickRequestModal = function(serviceTitle) {
            var input = document.getElementById('quickServiceField');
            if (input && serviceTitle) {
                input.value = serviceTitle;
            }
            var modalEl = document.getElementById('quickTourModal');
            if (modalEl && window.bootstrap) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        };

        // Quick Tour Request Form AJAX submission
        var quickForm = document.getElementById('quickTourRequestForm');
        var quickFeedback = document.getElementById('quickTourRequestFeedback');
        if (quickForm) {
            quickForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                var btn = quickForm.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'ĐANG GỬI...';
                }
                if (quickFeedback) {
                    quickFeedback.style.display = 'none';
                }

                try {
                    var response = await fetch(quickForm.action, {
                        method: 'POST',
                        body: new FormData(quickForm),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    var data = await response.json();
                    if (data && data.success) {
                        if (quickFeedback) {
                            quickFeedback.className = 'small mt-2 text-success fw-bold';
                            quickFeedback.textContent = data.message || 'Yêu cầu của bạn đã được gửi thành công!';
                            quickFeedback.style.display = 'block';
                        }
                        quickForm.reset();
                        setTimeout(function() {
                            var modalEl = document.getElementById('quickTourModal');
                            if (modalEl && window.bootstrap) {
                                bootstrap.Modal.getInstance(modalEl)?.hide();
                            }
                        }, 2000);
                    } else {
                        if (quickFeedback) {
                            quickFeedback.className = 'small mt-2 text-danger';
                            quickFeedback.textContent = (data && data.message) ? data.message : 'Không thể gửi yêu cầu.';
                            quickFeedback.style.display = 'block';
                        }
                    }
                } catch(err) {
                    if (quickFeedback) {
                        quickFeedback.className = 'small mt-2 text-danger';
                        quickFeedback.textContent = 'Lỗi kết nối. Vui lòng thử lại sau.';
                        quickFeedback.style.display = 'block';
                    }
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = 'GỬI YÊU CẦU';
                    }
                }
            });
        }
        // ── Horizontal Tour Slider Navigation & Drag-to-Scroll ──
        var sliderHome = document.getElementById('toursSliderHome');
        var prevHomeBtn = document.getElementById('prevToursHomeBtn');
        var nextHomeBtn = document.getElementById('nextToursHomeBtn');

        if (sliderHome) {
            if (prevHomeBtn) {
                prevHomeBtn.addEventListener('click', function () {
                    sliderHome.scrollBy({ left: -305, behavior: 'smooth' });
                });
            }
            if (nextHomeBtn) {
                nextHomeBtn.addEventListener('click', function () {
                    sliderHome.scrollBy({ left: 305, behavior: 'smooth' });
                });
            }

            var isDownH = false;
            var startXH;
            var scrollLeftH;

            sliderHome.addEventListener('mousedown', function (e) {
                isDownH = true;
                sliderHome.classList.add('is-dragging');
                startXH = e.pageX - sliderHome.offsetLeft;
                scrollLeftH = sliderHome.scrollLeft;
            });

            window.addEventListener('mouseup', function () {
                if (isDownH) {
                    isDownH = false;
                    sliderHome.classList.remove('is-dragging');
                }
            });

            sliderHome.addEventListener('mouseleave', function () {
                if (isDownH) {
                    isDownH = false;
                    sliderHome.classList.remove('is-dragging');
                }
            });

            sliderHome.addEventListener('mousemove', function (e) {
                if (!isDownH) return;
                e.preventDefault();
                var x = e.pageX - sliderHome.offsetLeft;
                var walk = (x - startXH) * 1.5;
                sliderHome.scrollLeft = scrollLeftH - walk;
            });
        }
    });
    </script>
</body>
</html>
