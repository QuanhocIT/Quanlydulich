<?php
/**
 * TRANG TRUNG TÂM THÔNG BÁO — DULICHPRO
 * Giao diện hiện đại đồng bộ chuẩn Image 2
 */

$khachHang = isset($khachHang) && is_array($khachHang) ? $khachHang : [];
$nguoiDung = isset($nguoiDung) && is_array($nguoiDung) ? $nguoiDung : [];
$thongBaoList = isset($thongBaoList) && is_array($thongBaoList) ? $thongBaoList : [];
$thongBaoChuaDoc = (int)($thongBaoChuaDoc ?? 0);

$userName = !empty($nguoiDung['ho_ten']) ? $nguoiDung['ho_ten'] : (!empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Khách hàng');
$userEmail = !empty($nguoiDung['email']) ? $nguoiDung['email'] : 'tranthib@test.com';
$userAvatar = !empty($nguoiDung['avatar']) ? $nguoiDung['avatar'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';

// Chuẩn bị danh sách thông báo
$notifications = [];
foreach ($thongBaoList as $tb) {
    $tId = (int)($tb['id'] ?? 0);
    $title = (string)($tb['tieu_de'] ?? 'Thông báo từ hệ thống');
    $content = (string)($tb['noi_dung'] ?? '');
    $time = !empty($tb['thoi_gian_gui']) ? date('d/m/Y H:i', strtotime($tb['thoi_gian_gui'])) : (!empty($tb['created_at']) ? date('d/m/Y H:i', strtotime($tb['created_at'])) : date('d/m/Y 09:30'));
    $isUnread = empty($tb['da_doc']) || $tb['da_doc'] == 0;

    // Phân loại icon
    $iconClass = 'bi-bell-fill text-primary';
    $cat = 'tour';
    if (stripos($title, 'thanh toán') !== false || stripos($title, 'tiền') !== false) {
        $iconClass = 'bi-credit-card-fill text-success';
        $cat = 'payment';
    } elseif (stripos($title, 'khởi hành') !== false || stripos($title, 'nhắc') !== false) {
        $iconClass = 'bi-alarm-fill text-warning';
        $cat = 'tour';
    } elseif (stripos($title, 'ưu đãi') !== false || stripos($title, 'khuyến mãi') !== false) {
        $iconClass = 'bi-gift-fill text-danger';
        $cat = 'promo';
    }

    $notifications[] = [
        'id' => $tId,
        'title' => $title,
        'content' => $content,
        'time' => $time,
        'is_unread' => $isUnread,
        'icon' => $iconClass,
        'category' => $cat
    ];
}

// Bổ sung các thông báo mẫu trực quan nếu danh sách ít
if (count($notifications) < 3) {
    $sampleNotes = [
        [
            'id' => 991,
            'title' => 'Xác nhận đặt tour thành công — #DL20260101',
            'content' => 'Đơn đặt tour NAGOYA – PHÚ SĨ – TOKYO của bạn đã được xác nhận thanh toán tiền cọc thành công. Chúc bạn có một hành trình tuyệt vời!',
            'time' => '10 phút trước',
            'is_unread' => true,
            'icon' => 'bi-check-circle-fill text-success',
            'category' => 'tour'
        ],
        [
            'id' => 992,
            'title' => 'Nhắc nhở chuẩn bị hành lý trước chuyến đi',
            'content' => 'Chuyến tham quan Vịnh Hạ Long sắp diễn ra trong 2 ngày tới. Quý khách vui lòng kiểm tra CCCD và tập trung tại điểm hẹn đúng giờ.',
            'time' => 'Hôm qua lúc 14:30',
            'is_unread' => true,
            'icon' => 'bi-alarm-fill text-warning',
            'category' => 'tour'
        ],
        [
            'id' => 993,
            'title' => 'Ưu đãi đặc quyền: Tặng mã giảm 10% tour Quốc tế',
            'content' => 'Chào mừng bạn đến với mùa du lịch Thu - Đông! Nhập mã DULICHPRO10 khi đặt bất kỳ tour quốc tế nào trong tuần này.',
            'time' => '2 ngày trước',
            'is_unread' => true,
            'icon' => 'bi-gift-fill text-danger',
            'category' => 'promo'
        ],
        [
            'id' => 994,
            'title' => 'Cập nhật lịch trình: Thêm điểm check-in tại Kyoto',
            'content' => 'Lịch trình tour Nhật Bản của bạn đã được nâng cấp bổ sung trải nghiệm mặc Kimono check-in Cố đô Kyoto hoàn toàn miễn phí.',
            'time' => '16/11/2025 09:15',
            'is_unread' => false,
            'icon' => 'bi-ticket-perforated-fill text-primary',
            'category' => 'tour'
        ],
    ];

    foreach ($sampleNotes as $sn) {
        $notifications[] = $sn;
        if (count($notifications) >= 4) break;
    }
}

$unreadTotal = count(array_filter($notifications, fn($n) => $n['is_unread']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm thông báo — DuLichPro</title>

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

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* ── HEADER / TOPBAR (Đồng bộ theo Image 2) ── */
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

        /* Toolbar */
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

        .toolbar-actions-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-mark-all-read {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: var(--primary);
            font-size: 12.5px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-mark-all-read:hover {
            background: #eff6ff;
            border-color: #bae6fd;
        }

        /* Notifications Stream */
        .notification-stream {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .notification-card-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 22px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            transition: all 0.2s ease;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .notification-card-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .notification-card-item.is-unread {
            background: #f0f7ff;
            border-color: #bfdbfe;
        }

        .notification-card-item.is-unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 18px;
            bottom: 18px;
            width: 4px;
            background: var(--primary);
            border-radius: 0 4px 4px 0;
        }

        .notif-icon-circle {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }

        .notif-content-wrap {
            flex-grow: 1;
        }

        .notif-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .notif-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f2e5a;
            margin: 0;
        }

        .badge-new-pill {
            background: #ef4444;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-block;
        }

        .notif-time {
            font-size: 12px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .notif-desc {
            font-size: 13.5px;
            color: #475569;
            line-height: 1.5;
            margin: 0 0 8px;
        }

        .btn-read-toggle {
            background: transparent;
            border: none;
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .btn-read-toggle:hover {
            color: var(--primary-hover);
            text-decoration: underline;
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
            .dashboard-container { grid-template-columns: 1fr; }
            .sidebar-col { display: grid; grid-template-columns: repeat(2, 1fr); }
            .sidebar-promo-card { grid-column: 1 / -1; }
        }

        @media (max-width: 820px) {
            .site-nav { display: none; }
            .sidebar-col { grid-template-columns: 1fr; }
            .hero-banner-quote { display: none; }
            .footer-top-grid { grid-template-columns: 1fr 1fr; }
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
                    <span class="badge-count" id="headerNotifBadge"><?php echo $unreadTotal; ?></span>
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

            <!-- Menu bên trái (Active: Thông báo) -->
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
                    <li class="sidebar-menu-item active">
                        <a href="index.php?act=khachHang/thongBao">
                            <span class="item-left"><i class="bi bi-bell-fill text-primary"></i> Thông báo</span>
                            <span class="menu-counter-badge" id="sidebarNotifBadge"><?php echo $unreadTotal; ?></span>
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

        <!-- ── CỘT PHẢI: TRUNG TÂM THÔNG BÁO ── -->
        <section class="main-col">

            <!-- Hero Banner Mini -->
            <div class="orders-hero-banner">
                <div class="hero-banner-content">
                    <h2>Trung tâm thông báo</h2>
                    <p>Cập nhật mọi tin tức hành trình, xác nhận booking và ưu đãi hấp dẫn</p>
                </div>
                <div class="hero-banner-quote">
                    <span>Đồng hành cùng bạn trên mọi nẻo đường! ✈</span>
                </div>
            </div>

            <!-- Toolbar Lọc & Thao tác -->
            <div class="filters-toolbar">
                <ul class="status-tabs-list">
                    <li>
                        <button type="button" class="status-tab-btn active" data-filter="all">
                            <i class="bi bi-inbox-fill"></i>
                            <span>Tất cả (<?php echo count($notifications); ?>)</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="unread">
                            <i class="bi bi-envelope-badge-fill text-danger"></i>
                            <span>Chưa đọc (<?php echo $unreadTotal; ?>)</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="tour">
                            <i class="bi bi-ticket-perforated"></i>
                            <span>Chuyến đi</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="status-tab-btn" data-filter="promo">
                            <i class="bi bi-gift"></i>
                            <span>Ưu đãi</span>
                        </button>
                    </li>
                </ul>

                <div class="toolbar-actions-right">
                    <button type="button" class="btn-mark-all-read" onclick="markAllAsRead()">
                        <i class="bi bi-check2-all"></i> Đánh dấu đã đọc tất cả
                    </button>
                </div>
            </div>

            <!-- Dòng danh sách thông báo -->
            <div class="notification-stream" id="notificationStream">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="notification-card-item <?php echo $n['is_unread'] ? 'is-unread' : ''; ?>" data-category="<?php echo htmlspecialchars($n['category']); ?>" data-unread="<?php echo $n['is_unread'] ? '1' : '0'; ?>" id="notifItem-<?php echo $n['id']; ?>">
                            
                            <div class="notif-icon-circle">
                                <i class="bi <?php echo htmlspecialchars($n['icon']); ?>"></i>
                            </div>

                            <div class="notif-content-wrap">
                                <div class="notif-header-row">
                                    <div class="d-flex align-items-center gap-2">
                                        <h4 class="notif-title"><?php echo htmlspecialchars($n['title']); ?></h4>
                                        <?php if ($n['is_unread']): ?>
                                            <span class="badge-new-pill">Mới</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="notif-time">
                                        <i class="bi bi-clock"></i>
                                        <span><?php echo htmlspecialchars($n['time']); ?></span>
                                    </div>
                                </div>

                                <p class="notif-desc"><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>

                                <?php if ($n['is_unread']): ?>
                                    <button type="button" class="btn-read-toggle" onclick="markSingleRead(<?php echo $n['id']; ?>)">
                                        <i class="bi bi-check2"></i> Đánh dấu đã đọc
                                    </button>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="background:#fff;border-radius:16px;border:1px dashed #cbd5e1;padding:48px 24px;text-align:center;">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <h4 style="margin: 16px 0 8px; font-weight: 700;">Hộp thông báo đang trống</h4>
                        <p style="color: #64748b; font-size: 14px;">Khi có thông tin đặt tour hoặc chương trình ưu đãi mới, chúng tôi sẽ thông báo cho bạn tại đây.</p>
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
                document.querySelectorAll('.notification-card-item').forEach(item => {
                    const cat = item.getAttribute('data-category');
                    const unread = item.getAttribute('data-unread');

                    if (filter === 'all') {
                        item.style.display = 'flex';
                    } else if (filter === 'unread') {
                        item.style.display = (unread === '1') ? 'flex' : 'none';
                    } else {
                        item.style.display = (cat === filter) ? 'flex' : 'none';
                    }
                });
            });
        });

        // Đánh dấu đã đọc 1 thông báo
        async function markSingleRead(notifId) {
            try {
                const res = await fetch(`index.php?act=khachHang/thongBao&mark_read=${notifId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const card = document.getElementById(`notifItem-${notifId}`);
                if (card) {
                    card.classList.remove('is-unread');
                    card.setAttribute('data-unread', '0');
                    const badge = card.querySelector('.badge-new-pill');
                    if (badge) badge.remove();
                    const btn = card.querySelector('.btn-read-toggle');
                    if (btn) btn.remove();
                }
                updateUnreadCounters();
            } catch (e) {
                console.log(e);
            }
        }

        // Đánh dấu đã đọc tất cả
        async function markAllAsRead() {
            try {
                await fetch(`index.php?act=khachHang/markAllNotificationsRead`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                document.querySelectorAll('.notification-card-item').forEach(card => {
                    card.classList.remove('is-unread');
                    card.setAttribute('data-unread', '0');
                    const badge = card.querySelector('.badge-new-pill');
                    if (badge) badge.remove();
                    const btn = card.querySelector('.btn-read-toggle');
                    if (btn) btn.remove();
                });
                updateUnreadCounters();
            } catch (e) {
                console.log(e);
            }
        }

        function updateUnreadCounters() {
            const unreadCount = document.querySelectorAll('.notification-card-item[data-unread="1"]').length;
            const headerBadge = document.getElementById('headerNotifBadge');
            const sidebarBadge = document.getElementById('sidebarNotifBadge');
            if (headerBadge) {
                headerBadge.innerText = unreadCount;
                if (unreadCount === 0) headerBadge.style.display = 'none';
            }
            if (sidebarBadge) {
                sidebarBadge.innerText = unreadCount;
                if (unreadCount === 0) sidebarBadge.style.display = 'none';
            }
        }
    </script>
</body>
</html>
