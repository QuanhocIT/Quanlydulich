<?php 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php?act=auth/login');
    exit;
}
$pageTitle = 'Danh Sách Khách Theo Tour';
$currentPage = 'danh_sach_khach';
ob_start();
?>
<style>
        .page-header-section {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            padding: 40px;
            margin-bottom: 40px;
            backdrop-filter: blur(10px);
        }
        .stats-card {
            border-left: 4px solid;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(45, 45, 45, 0.5);
            backdrop-filter: blur(10px);
        }
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        }
        .stats-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .tour-info-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        .customer-table-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(45, 45, 45, 0.5);
            backdrop-filter: blur(10px);
        }
        .table-custom {
            color: var(--text-light);
        }
        .table-custom thead {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
        }
        .table-custom thead th {
            color: var(--text-light);
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table-custom tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .table-custom tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .table-custom tbody td {
            padding: 15px;
            color: var(--text-light);
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .schedule-select-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            color: var(--text-light);
            backdrop-filter: blur(10px);
        }
        .schedule-select-card:hover {
            border-color: var(--accent-gold);
            box-shadow: 0 8px 16px rgba(255, 193, 7, 0.2);
            transform: translateX(8px);
        }
        .signature-section {
            margin-top: 3rem;
            display: none;
            page-break-inside: avoid;
        }
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-body {
            padding: 20px;
            color: var(--text-light);
        }
        .badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .bg-primary { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        .bg-success { background: rgba(40, 167, 69, 0.3); color: #5cb85c; }
        .bg-warning { background: rgba(255, 193, 7, 0.3); color: #ffc107; }
        .bg-info { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        .bg-light { background: rgba(255, 255, 255, 0.1); color: var(--text-light); }
        .text-dark { color: var(--text-light) !important; }
        .text-success { color: #5cb85c !important; }
        .text-warning { color: #ffc107 !important; }
        .text-muted { color: var(--text-muted) !important; }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid rgba(40, 167, 69, 0.5);
            color: #5cb85c;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid rgba(255, 193, 7, 0.5);
            color: #ffc107;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: var(--accent-gold);
            color: #000;
        }
        .btn-primary:hover {
            background: #ffd700;
        }
        .btn-light {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-light:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .btn-outline-light {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .btn-info {
            background: rgba(0, 123, 255, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(0, 123, 255, 0.5);
        }
        .btn-info:hover {
            background: rgba(0, 123, 255, 0.5);
        }
        .btn-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-success:hover {
            background: rgba(40, 167, 69, 0.5);
        }
        .btn-warning {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.5);
        }
        .btn-warning:hover {
            background: rgba(255, 193, 7, 0.5);
        }
        .btn-danger {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.5);
        }
        .btn-dark {
            background: rgba(33, 37, 41, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(33, 37, 41, 0.5);
        }
        .btn-dark:hover {
            background: rgba(33, 37, 41, 0.5);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .btn-lg {
            padding: 12px 24px;
            font-size: 1rem;
        }
        .btn-group {
            display: inline-flex;
            gap: 5px;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -15px;
            margin-right: -15px;
        }
        .row > * {
            padding-left: 15px;
            padding-right: 15px;
        }
        .col-md-3 { width: 25%; }
        .col-lg-4 { width: 33.333333%; }
        .col-lg-8 { width: 66.666667%; }
        .g-3, .g-4 { gap: 1rem; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .px-4 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
        .p-0 { padding: 0; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }
        .small { font-size: 0.875rem; }
        .lead { font-size: 1.1rem; }
        .opacity-75 { opacity: 0.75; }
        .opacity-25 { opacity: 0.25; }
        .h-100 { height: 100%; }
        .table-responsive {
            overflow-x: auto;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                font-size: 11pt;
            }
            .page-header-section {
                background: none !important;
                color: black !important;
                border: 2px solid black;
                padding: 1rem !important;
            }
            .stats-card {
                border: 1px solid black !important;
                box-shadow: none !important;
            }
            table {
                page-break-inside: auto;
                border: 1px solid black !important;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                background: #f0f0f0 !important;
                color: black !important;
            }
            .signature-section {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 3rem;
                margin-top: 3rem;
            }
            .signature-box {
                text-align: center;
            }
            .signature-line {
                margin-top: 4rem;
                border-top: 1px solid black;
                padding-top: 0.5rem;
            }
        }
        @media (max-width: 768px) {
            .col-md-3, .col-lg-4, .col-lg-8 {
                width: 100%;
            }
        }
    </style>

<div style="padding: 20px;">
    <!-- Page Header -->
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-people-fill" style="color: var(--accent-gold);"></i> Danh Sách Khách Theo Tour
            </h1>
            <p style="margin: 0; opacity: 0.8; color: var(--text-light);">Quản lý thông tin khách hàng, check-in và phân phòng</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;" class="no-print">
            <a href="<?php echo BASE_URL; ?>index.php?act=admin/dashboard" style="background: rgba(255, 255, 255, 0.1); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyTour" style="background: rgba(255, 255, 255, 0.1); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                <i class="bi bi-compass"></i> Quản lý Tour
            </a>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success no-print" style="display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger no-print" style="display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>
        
        <?php if ($lichKhoiHanh && $tour): ?>
            <!-- Tour Info -->
            <div class="tour-info-card">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="fw-bold mb-3">
                            <i class="bi bi-compass text-primary"></i>
                            <?php echo htmlspecialchars($tour['ten_tour'] ?? 'N/A'); ?>
                        </h2>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <small class="text-muted d-block"><i class="bi bi-hash"></i> Mã tour</small>
                                <span class="badge bg-primary">#<?php echo htmlspecialchars($tour['tour_id'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block"><i class="bi bi-calendar-event"></i> Khởi hành</small>
                                <strong><?php echo date('d/m/Y', strtotime($lichKhoiHanh['ngay_khoi_hanh'])); ?></strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block"><i class="bi bi-calendar-check"></i> Kết thúc</small>
                                <strong><?php echo date('d/m/Y', strtotime($lichKhoiHanh['ngay_ket_thuc'])); ?></strong>
                            </div>
                            <?php if (isset($tour['gia_co_ban']) && $tour['gia_co_ban']): ?>
                            <div class="col-md-3">
                                <small class="text-muted d-block"><i class="bi bi-currency-dollar"></i> Giá tour</small>
                                <strong class="text-success"><?php echo number_format($tour['gia_co_ban'], 0, ',', '.'); ?> VNĐ</strong>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-4 text-end no-print">
                        <button class="btn btn-lg btn-dark" onclick="window.print()">
                            <i class="bi bi-printer"></i> In Danh Sách Đoàn
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stats-card h-100" style="border-left-color: #0d6efd !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Tổng booking</p>
                                    <h3 class="mb-0 fw-bold"><?php echo count($bookingList); ?></h3>
                                </div>
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-file-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card h-100" style="border-left-color: #198754 !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Đã check-in</p>
                                    <h3 class="mb-0 fw-bold text-success"><?php echo $checkinStats['total_checkin'] ?? 0; ?></h3>
                                </div>
                                <div class="stats-icon bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card h-100" style="border-left-color: #ffc107 !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Chưa check-in</p>
                                    <h3 class="mb-0 fw-bold text-warning"><?php echo count($bookingList) - ($checkinStats['total_checkin'] ?? 0); ?></h3>
                                </div>
                                <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card h-100" style="border-left-color: #0dcaf0 !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Đã phân phòng</p>
                                    <h3 class="mb-0 fw-bold text-info"><?php echo $roomStats['total_rooms'] ?? 0; ?></h3>
                                </div>
                                <div class="stats-icon bg-info bg-opacity-10 text-info">
                                    <i class="bi bi-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customer Table -->
            <div class="card customer-table-card">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-table"></i> Danh sách khách hàng
                    </h5>
                    <?php if ($lichKhoiHanhId): ?>
                        <a href="index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                           class="btn btn-primary btn-sm no-print">
                            <i class="bi bi-plus-circle"></i> Thêm khách
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($bookingList)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted opacity-25"></i>
                            <p class="mt-3 text-muted">Chưa có booking nào cho lịch khởi hành này</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">STT</th>
                                        <th>Mã Booking</th>
                                        <th>Tên Khách Hàng</th>
                                        <th>Email</th>
                                        <th>Số điện thoại</th>
                                        <th style="width: 100px;">Số người</th>
                                        <th style="width: 150px;">Trạng thái</th>
                                        <th class="no-print" style="width: 200px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookingList as $index => $booking): ?>
                                        <tr>
                                            <td class="fw-bold text-center"><?php echo $index + 1; ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    #<?php echo htmlspecialchars($booking['booking_id'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($booking['khach_ho_ten'] ?? 'N/A'); ?></div>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="bi bi-envelope"></i>
                                                    <?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="bi bi-phone"></i>
                                                    <?php echo htmlspecialchars($booking['so_dien_thoai'] ?? 'N/A'); ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">
                                                    <i class="bi bi-people"></i> <?php echo ($booking['so_nguoi'] ?? 0); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($booking['checkin_id']): ?>
                                                    <span class="status-badge bg-success text-white">
                                                        <i class="bi bi-check-circle"></i>
                                                        <?php 
                                                            $status = $booking['checkin_status'];
                                                            echo $status === 'DaCheckIn' ? 'Đã check-in' : 
                                                                 ($status === 'DaCheckOut' ? 'Đã check-out' : 'Chưa check-in');
                                                        ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-badge bg-warning text-dark">
                                                        <i class="bi bi-clock"></i> Chưa check-in
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="no-print">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="index.php?act=admin/suaKhachLichKhoiHanh&booking_id=<?php echo $booking['booking_id']; ?>&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                                                       class="btn btn-info" title="Sửa">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if (!$booking['checkin_id']): ?>
                                                        <a href="index.php?act=admin/checkInKhach&booking_id=<?php echo $booking['booking_id']; ?>&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                                                           class="btn btn-success" title="Check-in">
                                                            <i class="bi bi-check-circle"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="index.php?act=admin/checkInKhach&booking_id=<?php echo $booking['booking_id']; ?>&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                                                           class="btn btn-primary" title="Chi tiết">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="index.php?act=admin/phanPhongKhachSan&booking_id=<?php echo $booking['booking_id']; ?>&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                                                       class="btn btn-warning" title="Phân phòng">
                                                        <i class="bi bi-building"></i>
                                                    </a>
                                                    <a href="index.php?act=admin/xoaKhachLichKhoiHanh&booking_id=<?php echo $booking['booking_id']; ?>&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                                                       class="btn btn-danger" 
                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa booking này?');"
                                                       title="Xóa">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Signature Section (for print) -->
            <div class="signature-section">
                <div class="signature-box">
                    <p class="fw-bold">NGƯỜI LẬP DANH SÁCH</p>
                    <p class="signature-line">(Ký và ghi rõ họ tên)</p>
                </div>
                <div class="signature-box">
                    <p class="fw-bold">TRƯỞNG ĐOÀN</p>
                    <p class="signature-line">(Ký và ghi rõ họ tên)</p>
                </div>
            </div>
            
        <?php elseif ($tour): ?>
            <!-- Select Schedule -->
            <div class="tour-info-card">
                <h2 class="fw-bold mb-3">
                    <i class="bi bi-compass text-primary"></i>
                    <?php echo htmlspecialchars($tour['ten_tour']); ?>
                </h2>
                <p class="lead">Vui lòng chọn lịch khởi hành để xem danh sách khách:</p>
                
                <?php if (!empty($lichKhoiHanhList)): ?>
                    <div class="mt-4">
                        <?php foreach ($lichKhoiHanhList as $lkh): ?>
                            <a href="index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=<?php echo $lkh['id']; ?>" 
                               class="schedule-select-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-2">
                                            <i class="bi bi-calendar-event text-primary"></i>
                                            <?php echo date('d/m/Y', strtotime($lkh['ngay_khoi_hanh'])); ?> 
                                            <i class="bi bi-arrow-right mx-2"></i>
                                            <?php echo date('d/m/Y', strtotime($lkh['ngay_ket_thuc'])); ?>
                                        </h5>
                                        <?php if (isset($lkh['gia_co_ban']) && $lkh['gia_co_ban']): ?>
                                            <p class="mb-0 text-success fw-bold">
                                                <i class="bi bi-currency-dollar"></i>
                                                <?php echo number_format($lkh['gia_co_ban'], 0, ',', '.'); ?> VNĐ
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <i class="bi bi-chevron-right fs-4 text-primary"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        Tour này chưa có lịch khởi hành nào.
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Select Tour -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-compass fs-1 text-muted opacity-25"></i>
                    <p class="mt-3 text-muted">
                        Vui lòng chọn tour từ trang 
                        <a href="index.php?act=admin/quanLyTour" class="fw-bold">Quản lý tour</a> 
                        để xem danh sách khách.
                    </p>
                </div>
            </div>
        <?php endif; ?>
</div>

<script>
    // Print functionality preserved
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
