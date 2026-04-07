<?php
$isCompletedView = !empty($isCompletedView);
$pageTitle = $isCompletedView ? 'Booking đã hoàn thành' : 'Quản lý Booking';
$currentPage = 'booking';
ob_start();
?>

<style>
    .booking-admin-shell {
        display: grid;
        gap: 24px;
    }

    .flash-stack {
        display: grid;
        gap: 14px;
    }

    .page-header-section {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .page-header-glow {
        position: absolute;
        top: 0;
        left: -60%;
        width: 60%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: booking-header-glow-move 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }

    @keyframes booking-header-glow-move {
        0% { left: -60%; }
        100% { left: 100%; }
    }

    .page-header-inner {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-header-main {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .header-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        text-decoration: none !important;
    }

    .page-header-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        flex-shrink: 0;
    }

    .page-header-title h1 {
        margin: 0;
        color: #ffe082;
        font-size: 1.7rem;
        font-weight: 700;
        text-shadow: 0 2px 8px #2d2d2d;
    }

    .page-header-title p {
        color: #fffde7;
        font-size: 1rem;
        margin-top: 6px;
        text-shadow: 0 1px 4px #2d2d2d;
    }

    .booking-view-switch {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .view-switch-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 2px;
        border: 2px solid rgba(255, 255, 255, 0.1);
        background: rgba(45, 45, 45, 0.3);
        color: var(--text-light);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .view-switch-link:hover {
        background: rgba(45, 45, 45, 0.5);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
    }

    .view-switch-link.active {
        border-color: var(--accent-gold);
        background: rgba(212, 175, 55, 0.16);
        color: var(--accent-gold);
    }

    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08);
    }

    .section-header .icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.2);
        color: var(--accent-gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-right: 14px;
        flex-shrink: 0;
    }

    .section-header h3 {
        margin: 0;
        color: var(--text-light);
        font-size: 18px;
        font-weight: 600;
    }

    .section-header small {
        display: block;
        margin-top: 4px;
        color: var(--text-muted);
        font-size: 12px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-left: 4px solid;
        border-radius: 8px;
        padding: 22px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(212,175,55,0.15);
    }

    .stat-card.border-primary { border-left-color: #0d6efd; }
    .stat-card.border-warning { border-left-color: #ffc107; }
    .stat-card.border-info { border-left-color: #0dcaf0; }
    .stat-card.border-success { border-left-color: #198754; }
    .stat-card.border-danger { border-left-color: #dc3545; }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        margin-bottom: 15px;
        transition: all 0.3s;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stat-icon.bg-primary { background: rgba(13, 110, 253, 0.2); color: #0d6efd; }
    .stat-icon.bg-warning { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .stat-icon.bg-info { background: rgba(13, 202, 240, 0.2); color: #0dcaf0; }
    .stat-icon.bg-success { background: rgba(25, 135, 84, 0.2); color: #198754; }
    .stat-icon.bg-danger { background: rgba(220, 53, 69, 0.2); color: #dc3545; }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        letter-spacing: 0.5px;
    }

    .stats-note {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: -8px;
    }

    .stats-note-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(45, 45, 45, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
        font-size: 12px;
    }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        padding: 22px 24px;
        margin-bottom: 24px;
        backdrop-filter: blur(10px);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .input,
    .form-group .select {
        width: 100%;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text-light);
        border-radius: 4px;
        padding: 10px 12px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .form-group .input::placeholder {
        color: rgba(255, 255, 255, 0.45);
    }

    .form-group .input:focus,
    .form-group .select:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
    }

    .table-wrapper {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 8px;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 18px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
        font-size: 12px;
    }

    .table-summary strong {
        color: var(--text-light);
    }

    .table-hint {
        color: var(--text-muted);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: rgba(212, 175, 55, 0.1);
    }

    .table th {
        padding: 15px;
        text-align: left;
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--accent-gold);
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        font-size: 13px;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .status-ChoXacNhan {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .status-DaCoc {
        background: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }

    .status-HoanTat {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .status-Huy {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .btn-group {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn-icon {
        min-width: 38px;
        min-height: 34px;
        padding: 6px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid transparent;
        text-decoration: none !important;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16);
    }

    .btn-icon.view {
        background: rgba(13, 202, 240, 0.16);
        color: #0dcaf0;
        border-color: rgba(13, 202, 240, 0.28);
    }

    .btn-icon.assign {
        background: rgba(255, 193, 7, 0.16);
        color: #ffc107;
        border-color: rgba(255, 193, 7, 0.28);
    }

    .btn-icon.edit {
        background: rgba(13, 110, 253, 0.16);
        color: #0d6efd;
        border-color: rgba(13, 110, 253, 0.28);
    }

    .btn-icon.delete {
        background: rgba(220, 53, 69, 0.16);
        color: #dc3545;
        border-color: rgba(220, 53, 69, 0.28);
    }

    .pagination-shell {
        padding: 18px 20px 22px;
        border-top: 1px solid rgba(255,255,255,0.08);
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .page-item {
        margin: 0;
    }

    .page-link {
        min-width: 40px;
        height: 40px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.04);
        color: var(--text-light);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .page-link:hover {
        border-color: rgba(212, 175, 55, 0.45);
        color: var(--accent-gold);
        background: rgba(212, 175, 55, 0.08);
    }

    .page-item.active .page-link {
        border-color: rgba(212, 175, 55, 0.65);
        background: rgba(212, 175, 55, 0.16);
        color: var(--accent-gold);
        box-shadow: 0 10px 24px rgba(212, 175, 55, 0.10);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border-color: rgba(25, 135, 84, 0.3);
        color: #198754;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    @media (max-width: 700px) {
        .page-header-section {
            padding: 20px;
        }

        .page-header-main {
            width: 100%;
        }

        .page-header-title h1 {
            font-size: 1.4rem;
        }

        .table-toolbar {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .booking-view-switch {
            display: grid;
            grid-template-columns: 1fr;
        }

        .view-switch-link {
            justify-content: center;
        }
    }
</style>

<?php 
$bookingsPage = $bookings ?? [];
$total = (int)($totalBookings ?? count($bookingsPage));
$currentPageBookings = count($bookingsPage);
$choXacNhan = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'ChoXacNhan'));
$daCoc = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'DaCoc'));
$hoanTat = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'HoanTat'));
$huy = count(array_filter($bookingsPage, fn($b) => $b['trang_thai'] === 'Huy'));
?>

<div class="booking-admin-shell">
    <div class="page-header-section">
        <div class="page-header-glow"></div>
        <div class="page-header-inner">
            <div class="page-header-main">
                <div class="page-header-avatar">📋</div>
                <div class="page-header-title">
                    <h1><?php echo $isCompletedView ? 'Booking Đã Hoàn Thành' : 'Quản Lý Booking'; ?></h1>
                    <p><?php echo $isCompletedView ? 'Xem lại các booking hoàn tất, bao gồm booking đã ẩn khỏi danh sách chính' : 'Quản lý đặt tour, theo dõi trạng thái và xử lý booking của khách hàng'; ?></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="index.php?act=admin/lichSuXoaBooking" class="btn btn-secondary">
                    🕐 Lịch sử xóa
                </a>
                <a href="index.php?act=admin/quanLyYeuCauTour" class="btn btn-secondary">
                    ⭐ Yêu cầu đặt tour
                </a>
                <a href="index.php?act=booking/datTourChoKhach" class="btn btn-primary">
                    ➕ Đặt tour cho khách
                </a>
            </div>
        </div>
    </div>

    <div class="flash-stack">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                ⚠ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="booking-view-switch">
        <a href="index.php?act=admin/quanLyBooking" class="view-switch-link <?php echo !$isCompletedView ? 'active' : ''; ?>">
            📋 Danh sách booking
        </a>
        <a href="index.php?act=admin/bookingDaHoanThanh" class="view-switch-link <?php echo $isCompletedView ? 'active' : ''; ?>">
            ✅ Booking đã hoàn thành
        </a>
        <a href="index.php?act=booking/datTourChoKhach" class="view-switch-link">
            ➕ Đặt tour cho khách
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card border-primary">
            <div class="stat-icon bg-primary">📅</div>
            <div class="stat-value"><?php echo $total; ?></div>
            <div class="stat-label">Tổng booking theo bộ lọc</div>
        </div>
        <div class="stat-card border-warning">
            <div class="stat-icon bg-warning">⏳</div>
            <div class="stat-value" style="color: #ffc107;"><?php echo $choXacNhan; ?></div>
            <div class="stat-label">Chờ xác nhận trên trang hiện tại</div>
        </div>
        <div class="stat-card border-info">
            <div class="stat-icon bg-info">💰</div>
            <div class="stat-value" style="color: #0dcaf0;"><?php echo $daCoc; ?></div>
            <div class="stat-label">Đã cọc trên trang hiện tại</div>
        </div>
        <div class="stat-card border-success">
            <div class="stat-icon bg-success">✓</div>
            <div class="stat-value" style="color: #198754;"><?php echo $hoanTat; ?></div>
            <div class="stat-label">Hoàn tất trên trang hiện tại</div>
        </div>
        <div class="stat-card border-danger">
            <div class="stat-icon bg-danger">✕</div>
            <div class="stat-value" style="color: #dc3545;"><?php echo $huy; ?></div>
            <div class="stat-label">Đã hủy trên trang hiện tại</div>
        </div>
    </div>

    <div class="stats-note">
        <span class="stats-note-chip">📄 Đang hiển thị <?php echo $currentPageBookings; ?> booking trên trang này</span>
        <span class="stats-note-chip">🔎 Bộ lọc hiện tại được áp dụng lên toàn bộ danh sách</span>
    </div>

    <div class="filter-section">
        <div class="section-header">
            <div class="icon">🔎</div>
            <div>
                <h3>Bộ lọc booking</h3>
                <small>Lọc theo trạng thái và từ khóa để tìm booking nhanh hơn</small>
            </div>
        </div>
        <form method="GET" action="index.php">
            <input type="hidden" name="act" value="<?php echo $isCompletedView ? 'admin/bookingDaHoanThanh' : 'admin/quanLyBooking'; ?>">
            <div class="filter-row">
                <div class="form-group">
                    <label>Lọc theo trạng thái</label>
                    <select name="trang_thai" class="select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="ChoXacNhan" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'ChoXacNhan') ? 'selected' : ''; ?>>Chờ xác nhận</option>
                        <option value="DaCoc" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'DaCoc') ? 'selected' : ''; ?>>Đã cọc</option>
                        <option value="HoanTat" <?php echo ((isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'HoanTat') || $isCompletedView) ? 'selected' : ''; ?>>Hoàn tất</option>
                        <option value="Huy" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'Huy') ? 'selected' : ''; ?>>Hủy</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tìm kiếm</label>
                    <input type="text" name="search" class="input"
                           placeholder="Mã booking, tên khách..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        🔍 Lọc dữ liệu
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($bookings)): ?>
        <div class="table-wrapper">
            <div class="section-header" style="padding: 20px 20px 16px; margin-bottom: 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div class="icon">📑</div>
                <div>
                    <h3>Danh sách booking</h3>
                    <small>Hiển thị các booking theo bộ lọc và phân trang hiện tại</small>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã Booking</th>
                        <th>Khách hàng</th>
                        <th>Tour</th>
                        <th>Ngày khởi hành</th>
                        <th>Số người</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: center;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>
                            <span style="font-family: monospace; font-weight: 600; color: var(--accent-gold);">
                                #<?php echo htmlspecialchars($booking['booking_id'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <div style="line-height: 1.6;">
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($booking['ho_ten'] ?? $booking['ten_khach_hang'] ?? 'N/A'); ?></div>
                                <small style="color: var(--text-muted); font-size: 11px;">
                                    <?php echo htmlspecialchars($booking['email'] ?? ''); ?>
                                </small>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($booking['ten_tour'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            if (!empty($booking['ngay_khoi_hanh'])) {
                                echo date('d/m/Y', strtotime($booking['ngay_khoi_hanh']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                        <td style="text-align: center;"><?php echo $booking['so_nguoi'] ?? 0; ?></td>
                        <td style="font-weight: 600; color: var(--accent-gold);">
                            <?php echo number_format((float)($booking['tong_tien'] ?? 0)); ?>đ
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($booking['trang_thai'] ?? ''); ?>">
                                <?php
                                $statusLabels = [
                                    'ChoXacNhan' => 'Chờ xác nhận',
                                    'DaCoc' => 'Đã cọc',
                                    'HoanTat' => 'Hoàn tất',
                                    'Huy' => 'Hủy'
                                ];
                                echo $statusLabels[$booking['trang_thai']] ?? $booking['trang_thai'];
                                ?>
                            </span>
                            <?php if (!empty($booking['is_hidden'])): ?>
                                <div style="margin-top: 6px; font-size: 11px; color: #f7d36d;">Đã ẩn khỏi danh sách chính</div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div class="btn-group">
                                <a href="index.php?act=booking/chiTiet&id=<?php echo $booking['booking_id']; ?>"
                                   class="btn-icon view"
                                   title="Xem chi tiết">
                                    👁️
                                </a>
                                <?php if (!empty($booking['tour_id'])): ?>
                                <a href="index.php?act=tour/phanBoNhanSuLichKhoiHanh&id=<?php echo $booking['tour_id']; ?>"
                                   class="btn-icon assign"
                                   title="Phân bổ nhân sự và dịch vụ">
                                    👥
                                </a>
                                <?php endif; ?>
                                <?php if (hasRole(['Admin', 'HDV'])): ?>
                                <a href="index.php?act=booking/chiTiet&id=<?php echo $booking['booking_id']; ?>"
                                   class="btn-icon edit"
                                   title="Chỉnh sửa booking">
                                    ✏️
                                </a>
                                <?php endif; ?>
                                <?php if (hasRole('Admin') && !$isCompletedView && ($booking['trang_thai'] ?? '') === 'HoanTat' && empty($booking['is_hidden'])): ?>
                                <form method="POST" action="index.php" style="display:inline; margin:0;">
                                    <input type="hidden" name="act" value="booking/hideCompleted">
                                    <input type="hidden" name="booking_id" value="<?php echo (int)$booking['booking_id']; ?>">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(csrfToken('booking_hide'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit"
                                            class="btn-icon delete"
                                            title="Ẩn khỏi danh sách booking"
                                            onclick="return confirm('Ẩn booking hoàn tất này khỏi danh sách chính?');"
                                            style="border:0; cursor:pointer;">
                                        🙈
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="table-toolbar">
                <div class="table-summary">
                    Tổng số theo bộ lọc: <strong><?php echo $totalBookings ?? count($bookings); ?></strong> booking
                </div>
                <div class="table-hint">
                    Trang hiện tại: <?php echo $currentPageBookings; ?> booking
                </div>
            </div>

            <?php $pageNumber = isset($pageNumber) ? (int)$pageNumber : max(1, (int)($_GET['page'] ?? 1)); ?>
            <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="pagination-shell" aria-label="Phân trang booking">
                <ul class="pagination">
                    <?php if ($pageNumber > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pageNumber - 1])); ?>">‹</a>
                    </li>
                    <?php endif; ?>
                    <?php
                    $pageStart = max(1, $pageNumber - 2);
                    $pageEnd   = min($totalPages, $pageNumber + 2);
                    for ($p = $pageStart; $p <= $pageEnd; $p++):
                    ?>
                    <li class="page-item <?php echo $p === $pageNumber ? 'active' : ''; ?>">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
                    </li>
                    <?php endfor; ?>
                    <?php if ($pageNumber < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pageNumber + 1])); ?>">›</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <div class="section-header" style="padding: 20px 20px 16px; margin-bottom: 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div class="icon">📭</div>
                <div>
                    <h3>Danh sách booking</h3>
                    <small>Chưa có dữ liệu phù hợp với bộ lọc hiện tại</small>
                </div>
            </div>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h4 style="margin-bottom: 15px;">Chưa có booking nào</h4>
                <p>Hiện tại chưa có booking nào trong hệ thống hoặc bộ lọc không trả về kết quả.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
