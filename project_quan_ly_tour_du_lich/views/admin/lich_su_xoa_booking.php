<?php
$pageTitle = 'Lịch sử xóa booking';
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

    .badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-secondary {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
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

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .booking-info {
        font-size: 12px;
        line-height: 1.6;
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>🕐 Lịch sử xóa booking</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">Xem lại các booking đã bị xóa</p>
        </div>
        <div>
            <a href="index.php?act=admin/quanLyBooking" class="btn btn-secondary">
                ← Quay lại
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

<!-- Table -->
<div class="table-wrapper">
    <?php if (!empty($lichSuXoa)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th>Booking ID</th>
                    <th>Tour</th>
                    <th>Khách hàng</th>
                    <th>Thông tin booking</th>
                    <th>Người xóa</th>
                    <th>Lý do xóa</th>
                    <th>Thời gian xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lichSuXoa as $idx => $item): ?>
                    <tr>
                        <td><?php echo $idx + 1; ?></td>
                        <td>
                            <span class="badge badge-secondary">#<?php echo $item['booking_id'] ?? 'N/A'; ?></span>
                        </td>
                        <td>
                            <?php if ($item['ten_tour']): ?>
                                <strong style="color: var(--text-light);"><?php echo htmlspecialchars($item['ten_tour']); ?></strong>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Tour đã bị xóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['ten_khach_hang']): ?>
                                <div style="color: var(--text-light); font-weight: 500;">
                                    <?php echo htmlspecialchars($item['ten_khach_hang']); ?>
                                </div>
                                <?php if ($item['email_khach_hang']): ?>
                                    <small style="color: var(--text-muted); font-size: 11px;">
                                        <?php echo htmlspecialchars($item['email_khach_hang']); ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $thongTin = json_decode($item['thong_tin_booking'] ?? '{}', true);
                            if ($thongTin):
                            ?>
                                <div class="booking-info" style="color: var(--text-muted);">
                                    <strong style="color: var(--text-light);">Số người:</strong> <?php echo $thongTin['so_nguoi'] ?? 0; ?><br>
                                    <strong style="color: var(--text-light);">Tổng tiền:</strong> <?php echo number_format($thongTin['tong_tien'] ?? 0, 0, ',', '.'); ?> VNĐ<br>
                                    <?php if ($thongTin['ngay_khoi_hanh']): ?>
                                        <strong style="color: var(--text-light);">Ngày khởi hành:</strong> <?php echo date('d/m/Y', strtotime($thongTin['ngay_khoi_hanh'])); ?><br>
                                    <?php endif; ?>
                                    <strong style="color: var(--text-light);">Trạng thái:</strong> 
                                    <?php
                                    $statusLabels = [
                                        'ChoXacNhan' => 'Chờ xác nhận',
                                        'DaCoc' => 'Đã cọc',
                                        'HoanTat' => 'Hoàn tất',
                                        'Huy' => 'Hủy'
                                    ];
                                    echo $statusLabels[$thongTin['trang_thai']] ?? $thongTin['trang_thai'] ?? 'N/A';
                                    ?>
                                </div>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Không có thông tin</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['nguoi_xoa']): ?>
                                <div style="font-weight: 600; color: var(--text-light);">
                                    <?php echo htmlspecialchars($item['nguoi_xoa']); ?>
                                </div>
                                <?php if ($item['email_nguoi_xoa']): ?>
                                    <small style="color: var(--text-muted); font-size: 11px;">
                                        <?php echo htmlspecialchars($item['email_nguoi_xoa']); ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['ly_do_xoa']): ?>
                                <span style="color: #dc3545; font-size: 12px;">
                                    <?php echo nl2br(htmlspecialchars($item['ly_do_xoa'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Không có</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small style="color: var(--text-muted);">
                                <?php echo $item['thoi_gian_xoa'] ? date('d/m/Y H:i:s', strtotime($item['thoi_gian_xoa'])) : 'N/A'; ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>Chưa có lịch sử xóa booking nào</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
