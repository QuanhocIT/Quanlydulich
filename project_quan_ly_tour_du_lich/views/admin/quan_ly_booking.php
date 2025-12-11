<?php
$pageTitle = 'Quản lý Booking';
$currentPage = 'booking';
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-left: 4px solid;
        border-radius: 2px;
        padding: 25px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateX(4px);
        border-color: var(--accent-gold);
    }

    .stat-card.border-primary { border-left-color: #0d6efd; }
    .stat-card.border-warning { border-left-color: #ffc107; }
    .stat-card.border-info { border-left-color: #0dcaf0; }
    .stat-card.border-success { border-left-color: #198754; }
    .stat-card.border-danger { border-left-color: #dc3545; }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }

    .stat-icon.bg-primary { background: rgba(13, 110, 253, 0.2); color: #0d6efd; }
    .stat-icon.bg-warning { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
    .stat-icon.bg-info { background: rgba(13, 202, 240, 0.2); color: #0dcaf0; }
    .stat-icon.bg-success { background: rgba(25, 135, 84, 0.2); color: #198754; }

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

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .table-wrapper {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        overflow: hidden;
        backdrop-filter: blur(10px);
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
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>📋 Quản Lý Booking</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">Quản lý đặt tour và xử lý booking của khách hàng</p>
            <a href="index.php?act=admin/lichSuXoaBooking" class="btn btn-secondary btn-sm" style="margin-top: 15px;">
                🕐 Xem lịch sử xóa booking
            </a>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="index.php?act=admin/quanLyYeuCauTour" class="btn btn-primary">
                ⭐ Yêu cầu đặt tour
            </a>
            <a href="index.php?act=booking/datTourChoKhach" class="btn btn-primary" style="background: var(--accent-gold); color: var(--primary-dark);">
                + Đặt tour cho khách
            </a>
        </div>
    </div>
</div>

<!-- Alerts -->
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

<!-- Statistics -->
<?php 
$total = count($bookings ?? []);
$choXacNhan = count(array_filter($bookings ?? [], fn($b) => $b['trang_thai'] === 'ChoXacNhan'));
$daCoc = count(array_filter($bookings ?? [], fn($b) => $b['trang_thai'] === 'DaCoc'));
$hoanTat = count(array_filter($bookings ?? [], fn($b) => $b['trang_thai'] === 'HoanTat'));
$huy = count(array_filter($bookings ?? [], fn($b) => $b['trang_thai'] === 'Huy'));
?>
<div class="stats-grid">
    <div class="stat-card border-primary">
        <div class="stat-icon bg-primary">📅</div>
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="stat-label">Tổng booking</div>
    </div>
    <div class="stat-card border-warning">
        <div class="stat-icon bg-warning">⏳</div>
        <div class="stat-value" style="color: #ffc107;"><?php echo $choXacNhan; ?></div>
        <div class="stat-label">Chờ xác nhận</div>
    </div>
    <div class="stat-card border-info">
        <div class="stat-icon bg-info">💰</div>
        <div class="stat-value" style="color: #0dcaf0;"><?php echo $daCoc; ?></div>
        <div class="stat-label">Đã cọc</div>
    </div>
    <div class="stat-card border-success">
        <div class="stat-icon bg-success">✓</div>
        <div class="stat-value" style="color: #198754;"><?php echo $hoanTat; ?></div>
        <div class="stat-label">Hoàn tất</div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="GET" action="index.php">
        <input type="hidden" name="act" value="admin/quanLyBooking">
        <div class="filter-row">
            <div class="form-group">
                <label>Lọc theo trạng thái</label>
                <select name="trang_thai" class="form-group select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="ChoXacNhan" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'ChoXacNhan') ? 'selected' : ''; ?>>Chờ xác nhận</option>
                    <option value="DaCoc" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'DaCoc') ? 'selected' : ''; ?>>Đã cọc</option>
                    <option value="HoanTat" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'HoanTat') ? 'selected' : ''; ?>>Hoàn tất</option>
                    <option value="Huy" <?php echo (isset($_GET['trang_thai']) && $_GET['trang_thai'] == 'Huy') ? 'selected' : ''; ?>>Hủy</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tìm kiếm</label>
                <input type="text" name="search" class="form-group input" 
                       placeholder="Mã booking, tên khách..." 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    🔍 Lọc
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Booking Table -->
<?php if (!empty($bookings)): ?>
    <div class="table-wrapper">
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
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($booking['ten_khach_hang'] ?? 'N/A'); ?></div>
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
                    </td>
                    <td style="text-align: center;">
                        <div class="btn-group">
                            <a href="index.php?act=booking/chiTiet&id=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-secondary btn-sm" 
                               style="background: rgba(13, 202, 240, 0.2); color: #0dcaf0; border-color: rgba(13, 202, 240, 0.3);"
                               title="Xem chi tiết">
                                👁️
                            </a>
                            <?php if (!empty($booking['tour_id'])): ?>
                            <a href="index.php?act=tour/phanBoNhanSuLichKhoiHanh&id=<?php echo $booking['tour_id']; ?>" 
                               class="btn btn-secondary btn-sm" 
                               style="background: rgba(255, 193, 7, 0.2); color: #ffc107; border-color: rgba(255, 193, 7, 0.3);"
                               title="Phân bổ nhân sự & dịch vụ">
                                👥
                            </a>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'HDV')): ?>
                            <a href="index.php?act=booking/chiTiet&id=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-secondary btn-sm" 
                               style="background: rgba(13, 110, 253, 0.2); color: #0d6efd; border-color: rgba(13, 110, 253, 0.3);"
                               title="Sửa">
                                ✏️
                            </a>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                            <a href="index.php?act=booking/delete&id=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-secondary btn-sm" 
                               style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);"
                               title="Xóa"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa booking này?');">
                                🗑️
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding: 15px 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-muted); font-size: 12px;">
            Tổng số: <strong><?php echo count($bookings); ?></strong> booking
        </div>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h4 style="margin-bottom: 15px;">Chưa có booking nào</h4>
            <p>Hiện tại chưa có booking nào trong hệ thống.</p>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
