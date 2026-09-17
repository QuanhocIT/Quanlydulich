<?php
/**
 * TRUNG TÂM HỖ TRỢ KHÁCH HÀNG — DULICHPRO
 * Giao diện hiện đại, sạch sẽ đồng bộ 100% chuẩn OTA (theo chuẩn Image 2)
 */

$bookings = isset($bookings) && is_array($bookings) ? $bookings : [];
$recentTickets = isset($recentTickets) && is_array($recentTickets) ? $recentTickets : [];
$selectedTicket = isset($selectedTicket) && is_array($selectedTicket) ? $selectedTicket : null;
$selectedMessages = isset($selectedMessages) && is_array($selectedMessages) ? $selectedMessages : [];

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

// Đếm thống kê tickets
$totalTickets = count($recentTickets);
$openTickets = 0;
$resolvedTickets = 0;
foreach ($recentTickets as $t) {
    $st = (string)($t['status'] ?? 'Open');
    if (in_array($st, ['Open', 'InProgress', 'WaitingCustomer'], true)) {
        $openTickets++;
    } elseif (in_array($st, ['Resolved', 'Closed'], true)) {
        $resolvedTickets++;
    }
}

// Xác định tab đang active
$activeTab = trim((string)($_GET['tab'] ?? ''));
if (!in_array($activeTab, ['create', 'tickets', 'faq', 'contact'], true)) {
    $activeTab = ($totalTickets > 0 && isset($_GET['ticket_id'])) ? 'tickets' : 'create';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm hỗ trợ — DuLichPro</title>

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

        /* ── HEADER / TOPBAR (Đồng bộ Image 2) ── */
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
            font-size: 20px;
            font-weight: 800;
            color: #0066cc;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .brand-text p {
            font-size: 11.5px;
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }

        .site-nav {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .site-nav a {
            text-decoration: none;
            color: #334155;
            font-weight: 600;
            font-size: 14.5px;
            transition: color 0.15s ease;
        }

        .site-nav a:hover {
            color: #0066cc;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-icon-round {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            text-decoration: none;
            position: relative;
            transition: all 0.2s;
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
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            width: 17px;
            height: 17px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
        }

        .user-profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 12px 4px 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.2s;
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

        /* ── MAIN DASHBOARD CONTAINER ── */
        .dashboard-container {
            max-width: 1360px;
            margin: 28px auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* ── SIDEBAR (CỘT TRÁI) ── */
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
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .profile-avatar-wrap {
            position: relative;
            width: 84px;
            height: 84px;
            margin: 0 auto 14px;
        }

        .profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e0f2fe;
        }

        .profile-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px;
        }

        .profile-email {
            font-size: 12.5px;
            color: #64748b;
            margin: 0 0 12px;
            word-break: break-all;
        }

        .profile-role-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0066cc;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
        }

        .sidebar-menu-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .sidebar-menu-list {
            list-style: none;
            margin: 0;
            padding: 0;
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
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .sidebar-menu-item a:hover {
            background: #f8fafc;
            color: #0066cc;
        }

        .sidebar-menu-item.active a {
            background: #e0f2fe;
            color: #0066cc;
            font-weight: 700;
        }

        .sidebar-menu-item a .item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-menu-item a i {
            font-size: 17px;
        }

        .menu-counter-badge {
            background: #ef4444;
            color: #ffffff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 12px;
        }

        .sidebar-promo-card {
            background: linear-gradient(135deg, #0284c7 0%, #0066cc 100%);
            border-radius: 18px;
            padding: 22px 20px;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.25);
            position: relative;
            overflow: hidden;
        }

        .sidebar-promo-card::after {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .promo-card-title {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 6px;
            line-height: 1.35;
        }

        .promo-card-sub {
            font-size: 12px;
            color: #e0f2fe;
            margin: 0 0 16px;
        }

        .promo-card-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: #0066cc;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }

        .promo-card-btn:hover {
            background: #f8fafc;
            color: #0052a3;
            transform: translateX(3px);
        }

        /* ── MAIN CONTENT (CỘT PHẢI) ── */
        .main-col {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Hero Banner */
        .support-hero-banner {
            background: linear-gradient(135deg, #0b2545 0%, #134074 50%, #0066cc 100%);
            border-radius: 20px;
            padding: 32px 36px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(2, 44, 88, 0.18);
        }

        .support-hero-banner::before {
            content: '';
            position: absolute;
            right: 18%;
            top: -50px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-banner-content h2 {
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 6px;
            letter-spacing: -0.5px;
        }

        .hero-banner-content p {
            font-size: 14px;
            color: #e0f2fe;
            margin: 0;
            font-weight: 400;
            max-width: 580px;
            line-height: 1.5;
        }

        .hero-banner-quote {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 18px;
            border-radius: 30px;
            font-family: 'Caveat', cursive;
            font-size: 19px;
            color: #fef08a;
            white-space: nowrap;
        }

        /* ── 4 KPI / QUICK ACTION CARDS ── */
        .support-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .support-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
        }

        .support-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 102, 204, 0.08);
            border-color: #cbd5e1;
        }

        .kpi-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .kpi-icon-blue { background: #e0f2fe; color: #0284c7; }
        .kpi-icon-green { background: #dcfce7; color: #16a34a; }
        .kpi-icon-amber { background: #fef3c7; color: #d97706; }
        .kpi-icon-purple { background: #f3e8ff; color: #9333ea; }

        .kpi-info-title {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin: 0 0 2px;
        }

        .kpi-info-val {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        /* ── TABS BAR ── */
        .support-tabs-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
        }

        .support-tab-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 12px;
            border: none;
            background: transparent;
            color: #475569;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .support-tab-btn:hover {
            color: #0066cc;
            background: #f8fafc;
        }

        .support-tab-btn.active {
            background: #0066cc;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(0, 102, 204, 0.25);
        }

        .tab-counter-pill {
            background: rgba(255, 255, 255, 0.25);
            color: inherit;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
        }

        .support-tab-btn:not(.active) .tab-counter-pill {
            background: #e2e8f0;
            color: #475569;
        }

        /* ── TAB PANELS ── */
        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        /* Surface Card */
        .content-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        }

        .content-card-head {
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .content-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px;
        }

        .content-card-desc {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }

        /* ── FORM ELEMENTS ── */
        .form-group-custom {
            margin-bottom: 18px;
        }

        .form-label-custom {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .form-control-custom,
        .form-select-custom {
            width: 100%;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            font-size: 13.5px;
            color: #0f172a;
            transition: all 0.2s;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            outline: none;
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.12);
        }

        .category-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 18px;
        }

        .category-radio-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
        }

        .category-radio-card:hover {
            border-color: #93c5fd;
            background: #f0f7ff;
        }

        .category-radio-card.active {
            border-color: #0066cc;
            background: #e0f2fe;
            color: #0066cc;
            font-weight: 700;
        }

        .category-radio-card i {
            font-size: 18px;
        }

        .category-radio-card span {
            font-size: 12.5px;
            line-height: 1.3;
        }

        /* Priority Options */
        .priority-options-row {
            display: flex;
            gap: 12px;
        }

        .priority-opt-btn {
            flex: 1;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            text-align: center;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s;
        }

        .priority-opt-btn.active.opt-normal { border-color: #0066cc; background: #e0f2fe; color: #0066cc; font-weight: 700; }
        .priority-opt-btn.active.opt-high { border-color: #f59e0b; background: #fef3c7; color: #b45309; font-weight: 700; }
        .priority-opt-btn.active.opt-urgent { border-color: #ef4444; background: #fee2e2; color: #b91c1c; font-weight: 700; }

        .priority-opt-btn .opt-title { font-size: 13px; font-weight: 600; }
        .priority-opt-btn .opt-time { font-size: 11px; opacity: 0.8; margin-top: 2px; }

        /* Tips Card */
        .support-tips-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-top: 20px;
        }

        .support-tips-box i {
            font-size: 18px;
            color: #16a34a;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .support-tips-box p {
            margin: 0;
            font-size: 12.5px;
            color: #166534;
            line-height: 1.45;
        }

        /* ── TICKETS LIST STYLES ── */
        .ticket-item-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 14px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            transition: all 0.2s;
        }

        .ticket-item-card:hover {
            border-color: #94a3b8;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .ticket-code-badge {
            font-family: monospace;
            background: #0f172a;
            color: #ffffff;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
        }

        .ticket-title-link {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            text-decoration: none;
            margin-top: 4px;
            display: block;
        }

        .ticket-title-link:hover {
            color: #0066cc;
        }

        .ticket-meta-line {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 6px;
            font-size: 12px;
            color: #64748b;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge.st-open { background: #dbeafe; color: #1e40af; }
        .status-badge.st-progress { background: #fef3c7; color: #92400e; }
        .status-badge.st-waiting { background: #fed7aa; color: #9a3412; }
        .status-badge.st-resolved { background: #dcfce7; color: #166534; }
        .status-badge.st-closed { background: #f1f5f9; color: #64748b; }

        .priority-badge {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }

        .priority-badge.p-low { background: #f1f5f9; color: #475569; }
        .priority-badge.p-med { background: #e0f2fe; color: #0369a1; }
        .priority-badge.p-high { background: #ffedd5; color: #c2410c; }
        .priority-badge.p-urgent { background: #fee2e2; color: #b91c1c; }

        /* ── FAQ ACCORDION ── */
        .faq-search-box {
            position: relative;
            margin-bottom: 22px;
        }

        .faq-search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }

        .faq-search-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
        }

        .faq-search-input:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.12);
        }

        .faq-accordion-item {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            margin-bottom: 12px;
            background: #ffffff;
            overflow: hidden;
            transition: all 0.2s;
        }

        .faq-accordion-item.active {
            border-color: #93c5fd;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.06);
        }

        .faq-accordion-header {
            padding: 16px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: #ffffff;
            font-weight: 700;
            font-size: 14.5px;
            color: #0f172a;
            user-select: none;
        }

        .faq-accordion-header:hover {
            color: #0066cc;
            background: #f8fafc;
        }

        .faq-accordion-body {
            padding: 0 20px 18px 20px;
            font-size: 13.5px;
            color: #475569;
            line-height: 1.6;
            display: none;
            border-top: 1px solid #f1f5f9;
        }

        .faq-accordion-item.active .faq-accordion-body {
            display: block;
        }

        .faq-accordion-item.active .faq-icon-chevron {
            transform: rotate(180deg);
        }

        .faq-icon-chevron {
            transition: transform 0.2s ease;
            color: #94a3b8;
        }

        /* ── CONTACT GRID ── */
        .contact-channel-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .contact-channel-card {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            background: #ffffff;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.2s;
        }

        .contact-channel-card:hover {
            border-color: #0066cc;
            box-shadow: 0 6px 18px rgba(0, 102, 204, 0.08);
        }

        .contact-icon-wrap {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        /* ── MODAL CONVERSATION ── */
        .conversation-wrap {
            max-height: 380px;
            overflow-y: auto;
            padding: 12px 8px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .chat-bubble {
            max-width: 82%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 13.5px;
            line-height: 1.5;
            position: relative;
        }

        .chat-bubble-customer {
            align-self: flex-end;
            background: linear-gradient(135deg, #0066cc, #0284c7);
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }

        .chat-bubble-support {
            align-self: flex-start;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 4px;
        }

        .chat-bubble-meta {
            font-size: 11px;
            margin-top: 4px;
            opacity: 0.8;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .chat-bubble-customer .chat-bubble-meta {
            color: #e0f2fe;
            justify-content: flex-end;
        }

        /* ── FOOTER ── */
        .site-footer {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            margin-top: 60px;
            padding: 40px 0 24px;
        }

        .footer-container {
            max-width: 1360px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.8fr 1fr 1fr 1.4fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .footer-col h4 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px;
        }

        .footer-col-links {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-col-links a {
            text-decoration: none;
            color: #64748b;
            font-size: 13.5px;
            transition: color 0.2s;
        }

        .footer-col-links a:hover {
            color: #0066cc;
        }

        .footer-social-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 14px;
        }

        .social-circle-btn {
            width: 36px;
            height: 36px;
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

        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            .support-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .category-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
            .contact-channel-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .support-kpi-grid {
                grid-template-columns: 1fr;
            }
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .priority-options-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <!-- 1. TOP NAVBAR (Đồng bộ chuẩn Image 2) -->
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
                    <li class="sidebar-menu-item">
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
                    <li class="sidebar-menu-item active">
                        <a href="index.php?act=khachHang/guiYeuCauHoTro">
                            <span class="item-left"><i class="bi bi-question-circle"></i> Hỗ trợ</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Banner khuyến mãi chân sidebar -->
            <div class="sidebar-promo-card">
                <h4 class="promo-card-title">Cần tư vấn trực tiếp ngay?</h4>
                <p class="promo-card-sub">Tổng đài viên luôn sẵn sàng giải đáp 24/7</p>
                <a href="tel:19006868" class="promo-card-btn">
                    <i class="bi bi-telephone-fill"></i>
                    <span>Gọi 1900 6868</span>
                </a>
            </div>
        </aside>

        <!-- ── CỘT PHẢI: NỘI DUNG HỖ TRỢ ── -->
        <section class="main-col">

            <!-- Hero Banner -->
            <div class="support-hero-banner">
                <div class="hero-banner-content">
                    <h2>Trung tâm hỗ trợ khách hàng</h2>
                    <p>Đội ngũ chăm sóc khách hàng DuLichPro luôn đồng hành, lắng nghe và giải đáp mọi yêu cầu của bạn một cách nhanh chóng nhất.</p>
                </div>
                <div class="hero-banner-quote">
                    <span>Luôn đồng hành trên từng cây số! 🎧</span>
                </div>
            </div>

            <!-- Flash Alert Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 rounded-4 shadow-sm" role="alert" style="background:#ecfdf5; color:#065f46;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                        <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-4 shadow-sm" role="alert" style="background:#fef2f2; color:#991b1b;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- 4 KPI / QUICK CONTACT CARDS -->
            <div class="support-kpi-grid">
                <a href="tel:19006868" class="support-kpi-card">
                    <div class="kpi-icon-box kpi-icon-blue"><i class="bi bi-telephone-inbound-fill"></i></div>
                    <div>
                        <div class="kpi-info-title">Hotline 24/7</div>
                        <div class="kpi-info-val">1900 6868</div>
                    </div>
                </a>
                <a href="https://zalo.me" target="_blank" class="support-kpi-card">
                    <div class="kpi-icon-box kpi-icon-green"><i class="bi bi-chat-dots-fill"></i></div>
                    <div>
                        <div class="kpi-info-title">Zalo CSKH Official</div>
                        <div class="kpi-info-val">Chat trực tiếp</div>
                    </div>
                </a>
                <div class="support-kpi-card" onclick="switchTab('tickets')" style="cursor: pointer;">
                    <div class="kpi-icon-box kpi-icon-amber"><i class="bi bi-ticket-detailed-fill"></i></div>
                    <div>
                        <div class="kpi-info-title">Phiếu của tôi</div>
                        <div class="kpi-info-val"><?php echo $openTickets; ?> đang xử lý / <?php echo $totalTickets; ?></div>
                    </div>
                </div>
                <div class="support-kpi-card">
                    <div class="kpi-icon-box kpi-icon-purple"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <div class="kpi-info-title">Cam kết SLA</div>
                        <div class="kpi-info-val">Từ 4h – 24h</div>
                    </div>
                </div>
            </div>

            <!-- TABS NAVIGATION BAR -->
            <div class="support-tabs-bar">
                <button type="button" class="support-tab-btn <?php echo $activeTab === 'create' ? 'active' : ''; ?>" onclick="switchTab('create')">
                    <i class="bi bi-pencil-square"></i>
                    <span>Gửi yêu cầu mới</span>
                </button>
                <button type="button" class="support-tab-btn <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>" onclick="switchTab('tickets')">
                    <i class="bi bi-ticket-perforated"></i>
                    <span>Phiếu hỗ trợ của tôi</span>
                    <span class="tab-counter-pill"><?php echo $totalTickets; ?></span>
                </button>
                <button type="button" class="support-tab-btn <?php echo $activeTab === 'faq' ? 'active' : ''; ?>" onclick="switchTab('faq')">
                    <i class="bi bi-patch-question"></i>
                    <span>Câu hỏi thường gặp (FAQ)</span>
                </button>
                <button type="button" class="support-tab-btn <?php echo $activeTab === 'contact' ? 'active' : ''; ?>" onclick="switchTab('contact')">
                    <i class="bi bi-geo-alt"></i>
                    <span>Kênh liên hệ & Văn phòng</span>
                </button>
            </div>

            <!-- ── TAB 1: GỬI YÊU CẦU MỚI (TẠO TICKET) ── -->
            <div class="tab-panel <?php echo $activeTab === 'create' ? 'active' : ''; ?>" id="panel-create">
                <div class="content-card">
                    <div class="content-card-head">
                        <div>
                            <h3 class="content-card-title">Tạo phiếu yêu cầu hỗ trợ</h3>
                            <p class="content-card-desc">Vui lòng cung cấp đầy đủ thông tin để nhân viên phụ trách hỗ trợ bạn nhanh chóng và chính xác nhất.</p>
                        </div>
                        <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill">
                            <i class="bi bi-shield-check me-1"></i> Bảo mật thông tin
                        </span>
                    </div>

                    <form method="POST" action="index.php?act=khachHang/guiYeuCauHoTro">
                        <input type="hidden" name="action" value="create_ticket">

                        <!-- Chọn danh mục vấn đề -->
                        <div class="form-group-custom">
                            <label class="form-label-custom">Chủ đề cần hỗ trợ</label>
                            <input type="hidden" name="category" id="selectedCategoryInput" value="Booking">
                            <div class="category-cards-grid">
                                <div class="category-radio-card active" data-cat="Booking" onclick="selectCategory(this, 'Booking')">
                                    <i class="bi bi-compass text-primary"></i>
                                    <span>Hành trình & Tour</span>
                                </div>
                                <div class="category-radio-card" data-cat="Payment" onclick="selectCategory(this, 'Payment')">
                                    <i class="bi bi-credit-card text-success"></i>
                                    <span>Thanh toán / Cọc</span>
                                </div>
                                <div class="category-radio-card" data-cat="Change" onclick="selectCategory(this, 'Change')">
                                    <i class="bi bi-arrow-repeat text-warning"></i>
                                    <span>Đổi lịch / Hủy tour</span>
                                </div>
                                <div class="category-radio-card" data-cat="Account" onclick="selectCategory(this, 'Account')">
                                    <i class="bi bi-person-gear text-info"></i>
                                    <span>Tài khoản & Khác</span>
                                </div>
                            </div>
                        </div>

                        <!-- Liên kết booking -->
                        <div class="row g-3">
                            <div class="col-md-6 form-group-custom">
                                <label class="form-label-custom" for="booking_id">Đơn đặt tour liên quan (nếu có)</label>
                                <select class="form-select-custom" id="booking_id" name="booking_id">
                                    <option value="0">-- Không liên kết đơn tour cụ thể --</option>
                                    <?php foreach ($bookings as $b): ?>
                                        <?php 
                                            $bId = (int)($b['booking_id'] ?? 0);
                                            $bCode = '#DL' . ($bId < 1000 ? str_pad((string)$bId, 8, '20260000', STR_PAD_LEFT) : $bId);
                                            $bTour = $b['ten_tour'] ?? ('Đơn hàng #' . $bId);
                                        ?>
                                        <option value="<?php echo $bId; ?>">
                                            <?php echo htmlspecialchars($bCode . ' - ' . $bTour); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 form-group-custom">
                                <label class="form-label-custom">Mức độ ưu tiên & Cam kết SLA</label>
                                <input type="hidden" name="muc_do_uu_tien" id="selectedPriorityInput" value="TrungBinh">
                                <div class="priority-options-row">
                                    <div class="priority-opt-btn active opt-normal" onclick="selectPriority(this, 'TrungBinh')">
                                        <div class="opt-title">Trung bình</div>
                                        <div class="opt-time">SLA 24h</div>
                                    </div>
                                    <div class="priority-opt-btn opt-high" onclick="selectPriority(this, 'Cao')">
                                        <div class="opt-title">Cao</div>
                                        <div class="opt-time">SLA 12h</div>
                                    </div>
                                    <div class="priority-opt-btn opt-urgent" onclick="selectPriority(this, 'KhanCap')">
                                        <div class="opt-title">Khẩn cấp</div>
                                        <div class="opt-time">SLA 4h</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tiêu đề -->
                        <div class="form-group-custom">
                            <label class="form-label-custom" for="tieu_de">Tiêu đề yêu cầu <span class="text-danger">*</span></label>
                            <input class="form-control-custom" id="tieu_de" type="text" name="tieu_de" placeholder="Tóm tắt ngắn gọn vấn đề của bạn (vd: Hướng dẫn xuất hóa đơn VAT, Yêu cầu đổi điểm tập trung...)" required minlength="5" maxlength="255">
                        </div>

                        <!-- Nội dung chi tiết -->
                        <div class="form-group-custom">
                            <label class="form-label-custom" for="noi_dung">Nội dung chi tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control-custom" id="noi_dung" name="noi_dung" rows="6" placeholder="Mô tả cụ thể tình huống phát sinh, thời gian, tên hành khách liên quan và kết quả bạn mong muốn được giải quyết..." required minlength="10" maxlength="2000"></textarea>
                        </div>

                        <!-- Gợi ý gửi yêu cầu -->
                        <div class="support-tips-box">
                            <i class="bi bi-lightbulb-fill"></i>
                            <p><strong>Mẹo xử lý nhanh:</strong> Nếu gặp trục trặc về thanh toán chuyển khoản, bạn vui lòng ghi kèm thời gian giao dịch và 4 số cuối tài khoản ngân hàng để bộ phận tài chính tra soát ngay lập tức.</p>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <button type="reset" class="btn btn-light px-4 py-2 rounded-3 fw-semibold text-secondary">
                                Làm mới
                            </button>
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm">
                                <i class="bi bi-send-fill me-1"></i> Gửi yêu cầu hỗ trợ
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── TAB 2: DANH SÁCH PHIẾU HỖ TRỢ (TICKETS) ── -->
            <div class="tab-panel <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>" id="panel-tickets">
                <div class="content-card">
                    <div class="content-card-head">
                        <div>
                            <h3 class="content-card-title">Phiếu hỗ trợ của tôi</h3>
                            <p class="content-card-desc">Theo dõi tiến trình tiếp nhận, SLA cam kết và trao đổi trực tiếp với nhân viên chăm sóc.</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-semibold" onclick="switchTab('create')">
                            <i class="bi bi-plus-lg me-1"></i> Tạo phiếu mới
                        </button>
                    </div>

                    <?php if (empty($recentTickets)): ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-inbox text-secondary" style="font-size: 56px; opacity: 0.4;"></i>
                            </div>
                            <h5 class="fw-bold text-navy">Bạn chưa có phiếu hỗ trợ nào</h5>
                            <p class="text-muted small mx-auto" style="max-width: 420px;">Khi có bất kỳ câu hỏi hoặc thắc mắc nào cần giải quyết, hãy gửi yêu cầu để được hỗ trợ chuyên nghiệp nhất.</p>
                            <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold mt-2" onclick="switchTab('create')">
                                <i class="bi bi-send me-1"></i> Gửi yêu cầu ngay
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="tickets-list-wrap">
                            <?php foreach ($recentTickets as $t): ?>
                                <?php
                                    $tId = (int)($t['id'] ?? 0);
                                    $tCode = htmlspecialchars((string)($t['ticket_code'] ?? ('#' . $tId)));
                                    $tSubject = htmlspecialchars((string)($t['subject'] ?? 'Yêu cầu hỗ trợ'));
                                    $tStatus = (string)($t['status'] ?? 'Open');
                                    $tPriority = (string)($t['priority'] ?? 'TrungBinh');
                                    $tCreated = !empty($t['created_at']) ? date('d/m/Y H:i', strtotime($t['created_at'])) : '--';
                                    $tSla = !empty($t['sla_due_at']) ? date('d/m/Y H:i', strtotime($t['sla_due_at'])) : 'Trong ngày';
                                    $tTour = !empty($t['ten_tour']) ? htmlspecialchars($t['ten_tour']) : '';

                                    // Status Badge mapping
                                    $stClass = 'st-open';
                                    $stText = 'Chờ tiếp nhận';
                                    if ($tStatus === 'InProgress') {
                                        $stClass = 'st-progress';
                                        $stText = 'Đang xử lý';
                                    } elseif ($tStatus === 'WaitingCustomer') {
                                        $stClass = 'st-waiting';
                                        $stText = 'Chờ bạn phản hồi';
                                    } elseif ($tStatus === 'Resolved') {
                                        $stClass = 'st-resolved';
                                        $stText = 'Đã giải quyết';
                                    } elseif ($tStatus === 'Closed') {
                                        $stClass = 'st-closed';
                                        $stText = 'Đã đóng';
                                    }

                                    // Priority Badge mapping
                                    $pClass = 'p-med';
                                    $pText = 'Trung bình';
                                    if ($tPriority === 'Thap') { $pClass = 'p-low'; $pText = 'Thấp'; }
                                    elseif ($tPriority === 'Cao') { $pClass = 'p-high'; $pText = 'Ưu tiên cao'; }
                                    elseif ($tPriority === 'KhanCap') { $pClass = 'p-urgent'; $pText = 'Khẩn cấp'; }
                                ?>
                                <div class="ticket-item-card">
                                    <div style="flex: 1;">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="ticket-code-badge"><?php echo $tCode; ?></span>
                                            <span class="status-badge <?php echo $stClass; ?>">
                                                <i class="bi bi-circle-fill" style="font-size: 7px;"></i>
                                                <?php echo $stText; ?>
                                            </span>
                                            <span class="priority-badge <?php echo $pClass; ?>">
                                                <?php echo $pText; ?>
                                            </span>
                                            <?php if ($tTour !== ''): ?>
                                                <span class="badge bg-light text-secondary border">
                                                    <i class="bi bi-suit-heart me-1 text-primary"></i><?php echo $tTour; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <a href="javascript:void(0);" onclick="openTicketConversation(<?php echo $tId; ?>)" class="ticket-title-link">
                                            <?php echo $tSubject; ?>
                                        </a>

                                        <div class="ticket-meta-line">
                                            <span><i class="bi bi-calendar-event me-1"></i> Ngày gửi: <?php echo $tCreated; ?></span>
                                            <span><i class="bi bi-stopwatch me-1"></i> Hạn SLA: <strong><?php echo $tSla; ?></strong></span>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-1 fw-semibold btn-sm" onclick="openTicketConversation(<?php echo $tId; ?>)">
                                            <i class="bi bi-chat-text me-1"></i> Xem & Phản hồi
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── TAB 3: CÂU HỎI THƯỜNG GẶP (FAQ) ── -->
            <div class="tab-panel <?php echo $activeTab === 'faq' ? 'active' : ''; ?>" id="panel-faq">
                <div class="content-card">
                    <div class="content-card-head">
                        <div>
                            <h3 class="content-card-title">Câu hỏi thường gặp</h3>
                            <p class="content-card-desc">Tra cứu nhanh câu trả lời cho các thắc mắc phổ biến về dịch vụ, thanh toán và hành trình tour.</p>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="faq-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" class="faq-search-input" id="faqSearchInput" placeholder="Tìm câu hỏi theo từ khóa (vd: đặt cọc, hủy tour, hoàn tiền, xuất hóa đơn VAT, bảo hiểm...)" oninput="filterFaq(this.value)">
                    </div>

                    <!-- FAQ Items -->
                    <div id="faqListWrap">
                        <!-- Q1 -->
                        <div class="faq-accordion-item active" data-keywords="đặt cọc thanh toán trả tiền hạn nạp ví momo vnpay">
                            <div class="faq-accordion-header" onclick="toggleFaq(this)">
                                <span>1. Quy định đặt cọc và hạn thanh toán số tiền còn lại như thế nào?</span>
                                <i class="bi bi-chevron-down faq-icon-chevron"></i>
                            </div>
                            <div class="faq-accordion-body">
                                Thông thường với các tour trong nước, bạn chỉ cần đặt cọc trước từ <strong>30% – 50%</strong> giá trị đơn tour để giữ chỗ chắc chắn. Số tiền còn lại có thể thanh toán trước ngày khởi hành từ 3 – 5 ngày làm việc hoặc thanh toán trực tiếp cho Hướng dẫn viên khi lên xe. Đối với tour Quốc tế yêu cầu xin visa, quy định cọc và hoàn tất tiền sẽ theo thời hạn nộp hồ sơ của Đại sứ quán.
                            </div>
                        </div>

                        <!-- Q2 -->
                        <div class="faq-accordion-item" data-keywords="đổi lịch đổi ngày dời lịch thay đổi hành trình">
                            <div class="faq-accordion-header" onclick="toggleFaq(this)">
                                <span>2. Tôi có thể đổi ngày khởi hành hoặc dời lịch tour sang thời gian khác không?</span>
                                <i class="bi bi-chevron-down faq-icon-chevron"></i>
                            </div>
                            <div class="faq-accordion-body">
                                Bạn hoàn toàn có thể yêu cầu dời ngày khởi hành miễn phí nếu thông báo cho DuLichPro <strong>trước 07 ngày làm việc</strong> so với ngày khởi hành ban đầu (tùy thuộc vào tình trạng phòng khách sạn và vé máy bay còn chỗ). Vui lòng vào mục <em>"Đơn đặt tour" &rarr; "Gửi yêu cầu thay đổi"</em> hoặc tạo phiếu hỗ trợ để chuyên viên xử lý.
                            </div>
                        </div>

                        <!-- Q3 -->
                        <div class="faq-accordion-item" data-keywords="hủy tour hoàn tiền chính sách hoàn phí hủy">
                            <div class="faq-accordion-header" onclick="toggleFaq(this)">
                                <span>3. Chính sách hoàn tiền khi hủy tour được tính như thế nào?</span>
                                <i class="bi bi-chevron-down faq-icon-chevron"></i>
                            </div>
                            <div class="faq-accordion-body">
                                - Hủy trước 15 ngày khởi hành: Hoàn 100% số tiền đã cọc (trừ phí xuất vé máy bay/tàu nếu có).<br>
                                - Hủy từ 08 - 14 ngày: Phí phạt 30% tổng giá trị tour.<br>
                                - Hủy từ 04 - 07 ngày: Phí phạt 50% tổng giá trị tour.<br>
                                - Hủy trong vòng 72 giờ: Phí phạt 100% tổng giá trị tour.<br>
                                Số tiền được hoàn sẽ chuyển về <strong>Ví DuLichPay</strong> của bạn ngay sau khi duyệt yêu cầu.
                            </div>
                        </div>

                        <!-- Q4 -->
                        <div class="faq-accordion-item" data-keywords="hướng dẫn viên liên hệ xe đón điểm đón giờ tập trung">
                            <div class="faq-accordion-header" onclick="toggleFaq(this)">
                                <span>4. Khi nào tôi sẽ nhận được thông tin chi tiết về Hướng dẫn viên và xe đón?</span>
                                <i class="bi bi-chevron-down faq-icon-chevron"></i>
                            </div>
                            <div class="faq-accordion-body">
                                Trước ngày khởi hành từ <strong>24 – 48 giờ</strong>, hệ thống DuLichPro sẽ tự động gửi thông báo tin nhắn và email kèm họ tên, số điện thoại của Hướng dẫn viên phụ trách đoàn, biển số xe và định vị điểm tập trung đón khách.
                            </div>
                        </div>

                        <!-- Q5 -->
                        <div class="faq-accordion-item" data-keywords="hóa đơn đỏ vat điện tử chứng từ thuế công ty">
                            <div class="faq-accordion-header" onclick="toggleFaq(this)">
                                <span>5. Làm thế nào để nhận hóa đơn giá trị gia tăng (VAT) điện tử?</span>
                                <i class="bi bi-chevron-down faq-icon-chevron"></i>
                            </div>
                            <div class="faq-accordion-body">
                                Ngay khi tour kết thúc hoặc khi đơn hàng đã thanh toán 100%, bạn chỉ cần gửi yêu cầu hỗ trợ kèm: <em>Tên công ty, Mã số thuế, Địa chỉ và Email nhận hóa đơn</em>. Kế toán DuLichPro sẽ xuất hóa đơn điện tử hợp lệ và gửi qua email của bạn trong vòng 24 - 48 giờ làm việc.
                            </div>
                        </div>

                        <!-- Q6 -->
                        <div class="faq-accordion-item" data-keywords="trẻ em giá vé em bé chính sách giá phụ thu">
                            <div class="faq-accordion-header" onclick="toggleFaq(this)">
                                <span>6. Chính sách giá vé dành cho trẻ em và em bé như thế nào?</span>
                                <i class="bi bi-chevron-down faq-icon-chevron"></i>
                            </div>
                            <div class="faq-accordion-body">
                                - Trẻ em dưới 2 tuổi: Miễn phí giá tour, chỉ phụ thu phí bảo hiểm và vé máy bay theo quy định của hãng hàng không.<br>
                                - Trẻ em từ 2 – dưới 5 tuổi: Tính 50% giá tour người lớn (ngủ chung giường với bố mẹ).<br>
                                - Trẻ em từ 5 – dưới 11 tuổi: Tính 75% giá tour người lớn (có tiêu chuẩn suất ăn và ghế ngồi riêng).<br>
                                - Từ 11 tuổi trở lên: Áp dụng giá tour như người lớn.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── TAB 4: KÊNH LIÊN HỆ & VĂN PHÒNG ── -->
            <div class="tab-panel <?php echo $activeTab === 'contact' ? 'active' : ''; ?>" id="panel-contact">
                <div class="content-card">
                    <div class="content-card-head">
                        <div>
                            <h3 class="content-card-title">Kênh liên hệ trực tiếp 24/7</h3>
                            <p class="content-card-desc">Bất cứ lúc nào bạn cần, chúng tôi luôn có mặt để đồng hành và hỗ trợ bạn chu đáo nhất.</p>
                        </div>
                    </div>

                    <div class="contact-channel-grid">
                        <!-- Kênh 1 -->
                        <div class="contact-channel-card">
                            <div class="contact-icon-wrap kpi-icon-blue">
                                <i class="bi bi-telephone-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-navy mb-1" style="font-size: 15px;">Tổng đài hỗ trợ toàn quốc</h5>
                                <div class="text-primary fw-bold fs-5 mb-1">1900 6868</div>
                                <p class="text-muted small mb-0">Hỗ trợ 24/7 kể cả ngày Lễ, Tết. Phím 1: Đặt tour & tư vấn, Phím 2: Khẩn cấp trong hành trình.</p>
                            </div>
                        </div>

                        <!-- Kênh 2 -->
                        <div class="contact-channel-card">
                            <div class="contact-icon-wrap kpi-icon-green">
                                <i class="bi bi-chat-text-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-navy mb-1" style="font-size: 15px;">Zalo CSKH Doanh Nghiệp</h5>
                                <div class="text-success fw-bold fs-5 mb-1">0988 123 456</div>
                                <p class="text-muted small mb-0">Gửi hình ảnh chứng từ, thông tin hành khách và trò chuyện trực tiếp cùng chuyên viên điều hành.</p>
                            </div>
                        </div>

                        <!-- Kênh 3 -->
                        <div class="contact-channel-card">
                            <div class="contact-icon-wrap kpi-icon-amber">
                                <i class="bi bi-envelope-open-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-navy mb-1" style="font-size: 15px;">Hòm thư điện tử (Email)</h5>
                                <div class="text-dark fw-bold mb-1" style="font-size: 14.5px;">cskh@dulichpro.vn</div>
                                <p class="text-muted small mb-0">Tiếp nhận khiếu nại, hợp đồng doanh nghiệp và yêu cầu báo giá tour theo đoàn riêng.</p>
                            </div>
                        </div>

                        <!-- Kênh 4 -->
                        <div class="contact-channel-card">
                            <div class="contact-icon-wrap kpi-icon-purple">
                                <i class="bi bi-buildings-fill"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-navy mb-1" style="font-size: 15px;">Văn phòng giao dịch chính</h5>
                                <div class="text-dark fw-semibold small mb-1">Tòa nhà DuLichPro, Cầu Giấy, Hà Nội</div>
                                <p class="text-muted small mb-0">Chi nhánh TP.HCM: Tầng 5, Bitexco Nam, Quận 1, TP. Hồ Chí Minh. Giờ mở cửa: 08:00 – 18:00 hàng ngày.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </main>

    <!-- ── FOOTER (Đồng bộ Image 2) ── -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Col 1: Brand -->
                <div class="footer-col">
                    <div class="brand-block mb-3">
                        <div class="brand-logo-icon"><i class="bi bi-tsunami"></i></div>
                        <div class="brand-text">
                            <h1>DuLichPro</h1>
                            <p>Khám phá thế giới - Trải nghiệm tuyệt vời</p>
                        </div>
                    </div>
                    <p style="color: #64748b; font-size: 13.5px; line-height: 1.6; max-width: 320px;">
                        Hệ sinh thái du lịch lữ hành hàng đầu, mang đến những hành trình an toàn, trải nghiệm đáng nhớ và dịch vụ chăm sóc tận tâm.
                    </p>
                </div>

                <!-- Col 2: Về chúng tôi -->
                <div class="footer-col">
                    <h4>Về chúng tôi</h4>
                    <ul class="footer-col-links">
                        <li><a href="index.php?act=khachHang/dashboard#about">Giới thiệu</a></li>
                        <li><a href="#terms">Điều khoản sử dụng</a></li>
                        <li><a href="#privacy">Chính sách bảo mật</a></li>
                    </ul>
                </div>

                <!-- Col 3: Hỗ trợ -->
                <div class="footer-col">
                    <h4>Hỗ trợ</h4>
                    <ul class="footer-col-links">
                        <li><a href="index.php?act=khachHang/guiYeuCauHoTro">Trung tâm trợ giúp</a></li>
                        <li><a href="index.php?act=khachHang/guiYeuCauHoTro&tab=contact">Liên hệ</a></li>
                        <li><a href="index.php?act=khachHang/guiYeuCauHoTro&tab=faq">Hướng dẫn thanh toán</a></li>
                    </ul>
                </div>

                <!-- Col 4: Theo dõi & Kênh xã hội -->
                <div class="footer-col">
                    <h4>Theo dõi chúng tôi</h4>
                    <div class="footer-social-row">
                        <a href="#" class="social-circle-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-circle-btn"><i class="bi bi-youtube"></i></a>
                        <a href="#" class="social-circle-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-circle-btn"><i class="bi bi-tiktok"></i></a>
                    </div>
                    <div class="mt-3 text-muted small">
                        Hotline CSKH: <strong>1900 6868</strong> (24/7)
                    </div>
                </div>
            </div>

            <div class="footer-bottom-row">
                <div>© 2026 DuLichPro. Tất cả quyền được bảo lưu.</div>
                <div>Du lịch không chỉ là điểm đến, mà là những trải nghiệm đáng nhớ! <i class="bi bi-heart-fill text-primary ms-1"></i></div>
            </div>
        </div>
    </footer>

    <!-- ── MODAL TRAO ĐỔI VÀ PHẢN HỒI TICKET (CONVERSATION MODAL) ── -->
    <div class="modal fade" id="ticketConversationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.18);">
                <div class="modal-header bg-light px-4 py-3 border-bottom">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="ticket-code-badge" id="modalTicketCode">TKT-000000</span>
                            <span class="status-badge st-open" id="modalTicketStatus">Đang xử lý</span>
                            <span class="priority-badge p-med" id="modalTicketPriority">Trung bình</span>
                        </div>
                        <h5 class="fw-bold text-navy mb-0 mt-1" id="modalTicketSubject">Tiêu đề ticket hỗ trợ</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Hộp thoại trao đổi -->
                    <div class="conversation-wrap" id="modalConversationWrap">
                        <!-- Nạp động qua JS -->
                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div> Đang tải lịch sử trao đổi...
                        </div>
                    </div>

                    <!-- Khung gửi câu trả lời -->
                    <div class="mt-4 pt-3 border-top" id="replyBoxWrap">
                        <form method="POST" action="index.php?act=khachHang/guiYeuCauHoTro">
                            <input type="hidden" name="action" value="reply_ticket">
                            <input type="hidden" name="ticket_id" id="replyTicketId" value="0">
                            <label class="form-label-custom mb-1" for="replyMessageInput">Phản hồi của bạn:</label>
                            <textarea class="form-control-custom mb-2" id="replyMessageInput" name="message" rows="3" placeholder="Nhập thêm thông tin, giải đáp thắc mắc hoặc câu hỏi tiếp theo..." required minlength="2" maxlength="2000"></textarea>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><i class="bi bi-clock-history me-1"></i> Nhân viên CSKH sẽ nhận được thông báo tức thì</span>
                                <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold btn-sm">
                                    <i class="bi bi-send me-1"></i> Gửi phản hồi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>

    <script>
        // Chuyển tab mượt mà
        function switchTab(tabName) {
            document.querySelectorAll('.support-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));

            const targetBtn = Array.from(document.querySelectorAll('.support-tab-btn')).find(b => b.getAttribute('onclick')?.includes(tabName));
            if (targetBtn) targetBtn.classList.add('active');

            const targetPanel = document.getElementById('panel-' + tabName);
            if (targetPanel) targetPanel.classList.add('active');

            // Cập nhật hash trên URL mà không reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        }

        // Chọn danh mục
        function selectCategory(el, cat) {
            document.querySelectorAll('.category-radio-card').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('selectedCategoryInput').value = cat;
        }

        // Chọn mức độ ưu tiên
        function selectPriority(el, prio) {
            document.querySelectorAll('.priority-opt-btn').forEach(p => p.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('selectedPriorityInput').value = prio;
        }

        // Accordion FAQ
        function toggleFaq(headerEl) {
            const item = headerEl.parentElement;
            item.classList.toggle('active');
        }

        // Lọc FAQ theo từ khóa
        function filterFaq(query) {
            const q = query.trim().toLowerCase();
            const items = document.querySelectorAll('.faq-accordion-item');
            items.forEach(item => {
                const keywords = (item.getAttribute('data-keywords') || '').toLowerCase();
                const text = item.innerText.toLowerCase();
                if (q === '' || keywords.includes(q) || text.includes(q)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Mở hội thoại ticket qua AJAX
        async function openTicketConversation(ticketId) {
            const modalEl = document.getElementById('ticketConversationModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            document.getElementById('replyTicketId').value = ticketId;
            const wrap = document.getElementById('modalConversationWrap');
            wrap.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Đang tải lịch sử trao đổi...</div>';

            try {
                const res = await fetch(`index.php?act=khachHang/guiYeuCauHoTro&ajax=get_ticket_messages&ticket_id=${ticketId}`);
                const data = await res.json();

                if (!data.success || !data.ticket) {
                    wrap.innerHTML = `<div class="alert alert-warning mb-0">${data.message || 'Không thể tải hội thoại.'}</div>`;
                    return;
                }

                // Cập nhật thông tin Header modal
                const t = data.ticket;
                document.getElementById('modalTicketCode').textContent = t.ticket_code || ('#' + t.id);
                document.getElementById('modalTicketSubject').textContent = t.subject || 'Yêu cầu hỗ trợ';

                // Trạng thái badge
                const stBadge = document.getElementById('modalTicketStatus');
                stBadge.className = 'status-badge ' + (
                    t.status === 'InProgress' ? 'st-progress' :
                    t.status === 'WaitingCustomer' ? 'st-waiting' :
                    t.status === 'Resolved' ? 'st-resolved' :
                    t.status === 'Closed' ? 'st-closed' : 'st-open'
                );
                stBadge.textContent = (
                    t.status === 'InProgress' ? 'Đang xử lý' :
                    t.status === 'WaitingCustomer' ? 'Chờ bạn phản hồi' :
                    t.status === 'Resolved' ? 'Đã giải quyết' :
                    t.status === 'Closed' ? 'Đã đóng' : 'Chờ tiếp nhận'
                );

                // Ưu tiên badge
                const pBadge = document.getElementById('modalTicketPriority');
                pBadge.className = 'priority-badge ' + (
                    t.priority === 'Cao' ? 'p-high' :
                    t.priority === 'KhanCap' ? 'p-urgent' : 'p-med'
                );
                pBadge.textContent = (
                    t.priority === 'Cao' ? 'Ưu tiên cao' :
                    t.priority === 'KhanCap' ? 'Khẩn cấp' : 'Trung bình'
                );

                // Hiển thị tin nhắn
                const messages = data.messages || [];
                if (messages.length === 0) {
                    wrap.innerHTML = '<div class="text-center text-muted py-4">Chưa có tin nhắn nào trong phiếu này.</div>';
                } else {
                    let html = '';
                    messages.forEach(m => {
                        const isCustomer = m.sender_role === 'KhachHang';
                        const bubbleClass = isCustomer ? 'chat-bubble-customer' : 'chat-bubble-support';
                        const senderName = isCustomer ? 'Bạn' : (m.sender_role === 'Admin' ? '🎧 Chăm Sóc Khách Hàng' : 'Hệ Thống');
                        const timeStr = m.created_at || '';

                        html += `
                            <div class="chat-bubble ${bubbleClass}">
                                <div style="font-weight: 700; font-size: 12px; margin-bottom: 3px; ${isCustomer ? 'color:#dbeafe;' : 'color:#0066cc;'}">
                                    ${senderName}
                                </div>
                                <div>${escapeHtml(m.message).replace(/\\n/g, '<br>')}</div>
                                <div class="chat-bubble-meta">
                                    <i class="bi bi-clock"></i> ${timeStr}
                                </div>
                            </div>
                        `;
                    });
                    wrap.innerHTML = html;
                    wrap.scrollTop = wrap.scrollHeight;
                }

                // Nếu ticket đã đóng, ẩn khung reply
                const replyWrap = document.getElementById('replyBoxWrap');
                if (t.status === 'Closed') {
                    replyWrap.innerHTML = '<div class="alert alert-secondary text-center small mb-0">Phiếu hỗ trợ này đã được đóng. Nếu cần hỗ trợ thêm, vui lòng tạo phiếu mới.</div>';
                }
            } catch (err) {
                console.error(err);
                wrap.innerHTML = '<div class="alert alert-danger mb-0">Lỗi kết nối mạng khi tải chi tiết phiếu.</div>';
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Tự động mở modal nếu URL có ticket_id
        <?php if (!empty($selectedTicketId) && $selectedTicketId > 0): ?>
            document.addEventListener('DOMContentLoaded', function() {
                openTicketConversation(<?php echo (int)$selectedTicketId; ?>);
            });
        <?php endif; ?>
    </script>
</body>
</html>
