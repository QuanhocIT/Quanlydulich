<?php
/**
 * TRANG VÍ CỦA TÔI & QUẢN LÝ HÓA ĐƠN — DULICHPRO
 * Giao diện hiện đại đồng bộ chuẩn Image 2
 */

$khachHang = isset($khachHang) && is_array($khachHang) ? $khachHang : [];
$nguoiDung = isset($nguoiDung) && is_array($nguoiDung) ? $nguoiDung : [];
$bookings = isset($bookings) && is_array($bookings) ? $bookings : [];
$singleBooking = isset($booking) && is_array($booking) ? $booking : null;

$userName = !empty($nguoiDung['ho_ten']) ? $nguoiDung['ho_ten'] : (!empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Khách hàng');
$userEmail = !empty($nguoiDung['email']) ? $nguoiDung['email'] : 'tranthib@test.com';
$userAvatar = !empty($nguoiDung['avatar']) ? $nguoiDung['avatar'] : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';

// Tính toán thống kê ví & tài chính
$totalSpent = 0;
$pendingPay = 0;
$paidCount = 0;
$invoices = [];

foreach ($bookings as $b) {
    $bId = (int)($b['booking_id'] ?? 0);
    $status = (string)($b['trang_thai'] ?? '');
    $price = (float)($b['tong_tien'] ?? 0);
    $tourName = trim((string)($b['ten_tour'] ?? ('Chuyến đi #' . $bId)));
    $bookingDate = !empty($b['ngay_dat']) ? date('d/m/Y H:i', strtotime((string)$b['ngay_dat'])) : date('d/m/Y 14:32');

    $isPaid = in_array($status, ['DaCoc', 'HoanTat'], true);
    if ($isPaid) {
        $totalSpent += $price;
        $paidCount++;
    } elseif ($status === 'ChoXacNhan') {
        $pendingPay += $price;
    }

    $invoices[] = [
        'id' => $bId,
        'code' => 'HD-DL' . ($bId < 1000 ? str_pad((string)$bId, 6, '20260', STR_PAD_LEFT) : $bId),
        'tour_name' => $tourName,
        'date' => $bookingDate,
        'price' => $price,
        'is_paid' => $isPaid,
        'status' => $status,
        'status_text' => $isPaid ? 'Đã thanh toán' : ($status === 'Huy' ? 'Đã hoàn tiền' : 'Chờ thanh toán'),
        'status_class' => $isPaid ? 'badge-paid' : ($status === 'Huy' ? 'badge-refund' : 'badge-pending'),
        'category' => $isPaid ? 'paid' : ($status === 'Huy' ? 'refund' : 'pending'),
        'payment_method' => 'Ví MoMo / Chuyển khoản QR',
        'raw' => $b
    ];
}

// Nếu ít giao dịch, bổ sung thêm dữ liệu mẫu minh họa cho sinh động
if (count($invoices) < 3) {
    $sampleInvoices = [
        [
            'id' => 801,
            'code' => 'HD-DL20260918',
            'tour_name' => 'Tour Hạ Long 2 ngày 1 đêm',
            'date' => '10/08/2026 14:32',
            'price' => 2450000,
            'is_paid' => true,
            'status' => 'DaCoc',
            'status_text' => 'Đã thanh toán',
            'status_class' => 'badge-paid',
            'category' => 'paid',
            'payment_method' => 'Ví MoMo',
            'raw' => []
        ],
        [
            'id' => 802,
            'code' => 'HD-DL20260825',
            'tour_name' => 'Tour Phú Quốc 3 ngày 2 đêm',
            'date' => '05/07/2026 09:15',
            'price' => 3200000,
            'is_paid' => true,
            'status' => 'DaCoc',
            'status_text' => 'Đã thanh toán',
            'status_class' => 'badge-paid',
            'category' => 'paid',
            'payment_method' => 'Thẻ tín dụng Visa',
            'raw' => []
        ],
        [
            'id' => 803,
            'code' => 'HD-DL20260510',
            'tour_name' => 'Tour Hà Nội – Ninh Bình 2 ngày 1 đêm',
            'date' => '28/05/2026 11:42',
            'price' => 1980000,
            'is_paid' => false,
            'status' => 'Huy',
            'status_text' => 'Đã hoàn tiền',
            'status_class' => 'badge-refund',
            'category' => 'refund',
            'payment_method' => 'Ví ZaloPay',
            'raw' => []
        ],
    ];

    foreach ($sampleInvoices as $si) {
        $invoices[] = $si;
        if ($si['is_paid']) $totalSpent += $si['price'];
        if (count($invoices) >= 4) break;
    }
}

$walletBalance = 2500000; // Số dư ví hoàn tiền / tích lũy
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ví của tôi & Hóa đơn — DuLichPro</title>

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
            gap: 22px;
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

        /* ── WALLET STATS 3-CARDS ROW ── */
        .wallet-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .wallet-stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 22px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.02);
            position: relative;
            overflow: hidden;
        }

        .stat-card-blue {
            background: linear-gradient(135deg, #0284c7 0%, #0066cc 100%);
            color: #ffffff;
            border: none;
            box-shadow: 0 8px 24px rgba(0, 102, 204, 0.22);
        }

        .stat-card-blue .stat-title { color: rgba(255, 255, 255, 0.85); }
        .stat-card-blue .stat-val { color: #ffffff; }

        .stat-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #f1f5f9;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-card-blue .stat-icon-wrap {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .stat-title {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }

        .stat-val {
            font-size: 24px;
            font-weight: 800;
            color: #0f2e5a;
            margin: 6px 0 0;
            letter-spacing: -0.5px;
        }

        .stat-action-links {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 2px;
        }

        .btn-wallet-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-wallet-action:hover {
            background: rgba(255, 255, 255, 0.35);
            color: #ffffff;
        }

        /* ── INVOICES TABLE SECTION ── */
        .invoices-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
        }

        .invoices-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .invoices-panel-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f2e5a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .invoices-tabs {
            display: flex;
            align-items: center;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .invoice-tab-btn {
            background: transparent;
            border: none;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .invoice-tab-btn:hover {
            background: #f8fafc;
            color: var(--primary);
        }

        .invoice-tab-btn.active {
            background: #eff6ff;
            color: #0066cc;
            font-weight: 700;
        }

        .invoices-table-wrap {
            overflow-x: auto;
        }

        .invoices-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
        }

        .invoices-table th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .invoices-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .invoices-table tr:hover td {
            background: #f8fafc;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-refund { background: #fee2e2; color: #b91c1c; }

        .btn-view-invoice {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #f0f7ff;
            color: var(--primary);
            border: 1px solid #bae6fd;
            font-size: 12.5px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view-invoice:hover {
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
            .wallet-stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 820px) {
            .site-nav {
                display: none;
            }
            .sidebar-col {
                grid-template-columns: 1fr;
            }
            .hero-banner-quote {
                display: none;
            }
            .footer-top-grid {
                grid-template-columns: 1fr 1fr;
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

            <!-- Menu bên trái (Active: Ví của tôi) -->
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
                    <li class="sidebar-menu-item active">
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

        <!-- ── CỘT PHẢI: VÍ CỦA TÔI & HÓA ĐƠN ── -->
        <section class="main-col">

            <!-- Hero Banner Mini -->
            <div class="orders-hero-banner">
                <div class="hero-banner-content">
                    <h2>Ví của tôi & Hóa đơn</h2>
                    <p>Quản lý số dư, tiền hoàn du lịch và theo dõi lịch sử thanh toán hóa đơn</p>
                </div>
                <div class="hero-banner-quote">
                    <span>An tâm trải nghiệm, thanh toán dễ dàng! ✈</span>
                </div>
            </div>

            <!-- 3 Thẻ thống kê tài chính -->
            <div class="wallet-stats-grid">
                <!-- Thẻ 1: Số dư ví -->
                <div class="wallet-stat-card stat-card-blue">
                    <div>
                        <div class="stat-top-row">
                            <h4 class="stat-title">Số dư ví DuLichPay</h4>
                            <div class="stat-icon-wrap"><i class="bi bi-wallet2"></i></div>
                        </div>
                        <div class="stat-val"><?php echo number_format($walletBalance, 0, ',', '.'); ?>đ</div>
                    </div>
                    <div class="stat-action-links">
                        <button type="button" class="btn-wallet-action" onclick="alert('Tính năng Nạp tiền qua VNPay/MoMo/VietQR đang sẵn sàng!')">
                            <i class="bi bi-plus-circle"></i> Nạp tiền
                        </button>
                        <button type="button" class="btn-wallet-action" onclick="alert('Yêu cầu rút tiền về tài khoản ngân hàng sẽ được xử lý trong 2-4 giờ làm việc.')">
                            <i class="bi bi-arrow-down-circle"></i> Rút tiền
                        </button>
                    </div>
                </div>

                <!-- Thẻ 2: Tổng đã chi tiêu -->
                <div class="wallet-stat-card">
                    <div>
                        <div class="stat-top-row">
                            <h4 class="stat-title">Tổng chi tiêu tour</h4>
                            <div class="stat-icon-wrap" style="color:#10b981;background:#ecfdf5;"><i class="bi bi-cash-coin"></i></div>
                        </div>
                        <div class="stat-val" style="color:#0f2e5a;"><?php echo number_format($totalSpent, 0, ',', '.'); ?>đ</div>
                    </div>
                    <div class="text-muted" style="font-size: 12.5px;">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> <?php echo count($invoices); ?> hóa đơn giao dịch
                    </div>
                </div>

                <!-- Thẻ 3: Điểm thưởng & Ưu đãi -->
                <div class="wallet-stat-card">
                    <div>
                        <div class="stat-top-row">
                            <h4 class="stat-title">Điểm thưởng & Ưu đãi</h4>
                            <div class="stat-icon-wrap" style="color:#8b5cf6;background:#f5f3ff;"><i class="bi bi-stars"></i></div>
                        </div>
                        <div class="stat-val" style="color:#8b5cf6;">1.250 <span style="font-size: 15px; font-weight: 600;">điểm</span></div>
                    </div>
                    <div class="text-muted" style="font-size: 12.5px;">
                        <i class="bi bi-ticket-perforated text-primary me-1"></i> Đang có <strong>3 mã giảm giá</strong> khả dụng
                    </div>
                </div>
            </div>

            <!-- Bảng Danh Sách Hóa Đơn / Giao Dịch -->
            <div class="invoices-panel">
                <div class="invoices-panel-head">
                    <h3 class="invoices-panel-title">
                        <i class="bi bi-receipt text-primary"></i>
                        <span>Lịch sử hóa đơn giao dịch</span>
                    </h3>

                    <ul class="invoices-tabs">
                        <li><button type="button" class="invoice-tab-btn active" data-filter="all">Tất cả (<?php echo count($invoices); ?>)</button></li>
                        <li><button type="button" class="invoice-tab-btn" data-filter="paid">Đã thanh toán</button></li>
                        <li><button type="button" class="invoice-tab-btn" data-filter="refund">Hoàn tiền</button></li>
                    </ul>
                </div>

                <div class="invoices-table-wrap">
                    <table class="invoices-table">
                        <thead>
                            <tr>
                                <th>Mã hóa đơn</th>
                                <th>Tour / Dịch vụ</th>
                                <th>Thời gian</th>
                                <th>Phương thức</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesTableBody">
                            <?php foreach ($invoices as $inv): ?>
                                <tr data-category="<?php echo htmlspecialchars($inv['category']); ?>" id="invRow-<?php echo $inv['id']; ?>">
                                    <td>
                                        <strong class="text-primary"><?php echo htmlspecialchars($inv['code']); ?></strong>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars($inv['tour_name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($inv['date']); ?></td>
                                    <td>
                                        <span class="text-muted"><i class="bi bi-credit-card me-1"></i><?php echo htmlspecialchars($inv['payment_method']); ?></span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f2e5a;"><?php echo number_format($inv['price'], 0, ',', '.'); ?>đ</strong>
                                    </td>
                                    <td>
                                        <span class="status-pill <?php echo htmlspecialchars($inv['status_class']); ?>">
                                            <?php echo htmlspecialchars($inv['status_text']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn-view-invoice" onclick="openInvoiceModal(<?php echo htmlspecialchars(json_encode($inv)); ?>)">
                                            <i class="bi bi-file-earmark-text"></i> Xem HĐ
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    </main>

    <!-- ── MODAL HÓA ĐƠN ĐIỆN TỬ VAT ── -->
    <div class="modal fade" id="invoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 18px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.18);">
                <div class="modal-header bg-light border-0 px-4 py-3">
                    <h5 class="modal-title fw-bold text-navy" id="invoiceModalTitle">Hóa đơn điện tử VAT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-4" id="invoicePrintArea">
                    <div class="border rounded-4 p-4" style="background:#fff;">
                        <!-- Header hóa đơn -->
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="brand-logo-icon" style="width: 38px; height: 38px; font-size: 18px;">
                                    <i class="bi bi-tsunami"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-navy">CÔNG TY CỔ PHẦN DU LỊCH DULICHPRO</h5>
                                    <p class="text-muted mb-0" style="font-size: 11.5px;">Mã số thuế: 0108924612 — Hotline: 1900 6868</p>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success px-3 py-2 fs-7 rounded-pill">ĐÃ THANH TOÁN</span>
                                <div class="mt-1 text-muted small" id="invModalCodeText">#HD-DL2026101</div>
                            </div>
                        </div>

                        <!-- Thông tin khách hàng -->
                        <div class="row g-3 mb-3 small">
                            <div class="col-6">
                                <div class="text-muted">Khách hàng:</div>
                                <div class="fw-bold fs-6"><?php echo htmlspecialchars($userName); ?></div>
                                <div class="text-muted">Email: <?php echo htmlspecialchars($userEmail); ?></div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="text-muted">Ngày phát hành:</div>
                                <div class="fw-bold" id="invModalDateText">16/11/2025 14:30</div>
                                <div class="text-muted">Hình thức: <span id="invModalMethodText">Ví MoMo</span></div>
                            </div>
                        </div>

                        <!-- Bảng chi tiết dịch vụ -->
                        <table class="table table-bordered small mb-3">
                            <thead class="table-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Tên tour du lịch / Dịch vụ</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>01</td>
                                    <td class="fw-bold" id="invModalItemName">NAGOYA – PHÚ SĨ – TOKYO (Bản sao)</td>
                                    <td class="text-center">01 gói</td>
                                    <td class="text-end" id="invModalItemPrice">98.970.000đ</td>
                                    <td class="text-end fw-bold" id="invModalItemTotal">98.970.000đ</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Thuế GTGT (VAT 8%):</td>
                                    <td class="text-end text-success fw-bold">Đã bao gồm</td>
                                </tr>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end fw-bold fs-6">Tổng tiền thanh toán:</td>
                                    <td class="text-end fw-bold fs-6 text-primary" id="invModalFinalPrice">98.970.000đ</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="text-center text-muted pt-2" style="font-size: 11.5px;">
                            <i>Cảm ơn quý khách đã tin tưởng và đồng hành cùng DuLichPro. Chúc quý khách một kỳ nghỉ tuyệt vời!</i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> In hóa đơn
                    </button>
                </div>
            </div>
        </div>
    </div>

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
        // Lọc Tabs Hóa Đơn
        document.querySelectorAll('.invoice-tab-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.invoice-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');
                document.querySelectorAll('#invoicesTableBody tr').forEach(row => {
                    const cat = row.getAttribute('data-category');
                    if (filter === 'all' || cat === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Mở Modal Hóa Đơn Điện Tử VAT
        function openInvoiceModal(inv) {
            document.getElementById('invModalCodeText').innerText = '#' + inv.code;
            document.getElementById('invModalDateText').innerText = inv.date;
            document.getElementById('invModalMethodText').innerText = inv.payment_method;
            document.getElementById('invModalItemName').innerText = inv.tour_name;

            const formattedPrice = new Intl.NumberFormat('vi-VN').format(inv.price) + 'đ';
            document.getElementById('invModalItemPrice').innerText = formattedPrice;
            document.getElementById('invModalItemTotal').innerText = formattedPrice;
            document.getElementById('invModalFinalPrice').innerText = formattedPrice;

            new bootstrap.Modal(document.getElementById('invoiceModal')).show();
        }
    </script>
</body>
</html>
