<?php
$pageTitle = 'Xác nhận xóa booking';
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

    .confirm-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(220, 53, 69, 0.3);
        border-radius: 2px;
        max-width: 800px;
        margin: 0 auto;
        backdrop-filter: blur(10px);
    }

    .confirm-header {
        background: rgba(220, 53, 69, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        color: #dc3545;
        font-weight: 600;
        font-size: 18px;
    }

    .confirm-body {
        padding: 30px;
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }

    .info-table th {
        width: 30%;
        padding: 12px;
        text-align: left;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .info-table td {
        padding: 12px;
        color: var(--text-light);
        font-size: 13px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-warning {
        background: rgba(255, 193, 7, 0.1);
        border-color: rgba(255, 193, 7, 0.3);
        color: #ffc107;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .input,
    .form-group textarea {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 12px 10px;
        font-size: 13px;
        border-radius: 2px;
        transition: all 0.3s;
        width: 100%;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-group .input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group .input:focus,
    .form-group textarea:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-gold);
    }

    .form-text {
        color: var(--text-muted);
        font-size: 11px;
        margin-top: 5px;
        display: block;
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="text-align: center;">
        <h1>⚠️ Xác nhận xóa booking</h1>
        <p style="color: var(--text-muted); margin-top: 10px;">Hành động này không thể hoàn tác</p>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        ⚠ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Confirm Card -->
<div class="confirm-card">
    <div class="confirm-header">
        ⚠️ Xác nhận xóa booking
    </div>
    <div class="confirm-body">
        <div class="alert alert-warning">
            ⚠️ <strong>Cảnh báo:</strong> Hành động này không thể hoàn tác. Booking sẽ bị xóa vĩnh viễn.
        </div>
        
        <div style="margin-bottom: 30px;">
            <h6 style="font-weight: 600; margin-bottom: 15px; color: var(--accent-gold); font-size: 14px;">Thông tin booking:</h6>
            <table class="info-table">
                <tr>
                    <th>Booking ID:</th>
                    <td>#<?php echo $booking['booking_id']; ?></td>
                </tr>
                <tr>
                    <th>Tour:</th>
                    <td><?php echo htmlspecialchars($booking['ten_tour'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Khách hàng:</th>
                    <td><?php echo htmlspecialchars($booking['ho_ten'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Số người:</th>
                    <td><?php echo $booking['so_nguoi'] ?? 0; ?></td>
                </tr>
                <tr>
                    <th>Tổng tiền:</th>
                    <td style="color: var(--accent-gold); font-weight: 600;">
                        <?php echo number_format($booking['tong_tien'] ?? 0, 0, ',', '.'); ?> VNĐ
                    </td>
                </tr>
                <tr>
                    <th>Ngày khởi hành:</th>
                    <td><?php echo $booking['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'N/A'; ?></td>
                </tr>
                <tr>
                    <th>Trạng thái:</th>
                    <td>
                        <?php
                        $statusLabels = [
                            'ChoXacNhan' => 'Chờ xác nhận',
                            'DaCoc' => 'Đã cọc',
                            'HoanTat' => 'Hoàn tất',
                            'Huy' => 'Hủy'
                        ];
                        echo $statusLabels[$booking['trang_thai']] ?? $booking['trang_thai'];
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <form method="POST" action="index.php?act=booking/delete&id=<?php echo $booking['booking_id']; ?>">
            <?php echo csrfField('booking_delete'); ?>
            <div class="form-group">
                <label>🔒 Nhập mật khẩu Admin để xác nhận <span style="color: #dc3545;">*</span></label>
                <input type="password" name="mat_khau" class="input" required autofocus>
                <span class="form-text">Vui lòng nhập mật khẩu tài khoản Admin của bạn.</span>
            </div>
            
            <div class="form-group">
                <label>📝 Lý do xóa (tùy chọn)</label>
                <textarea name="ly_do_xoa" class="textarea" rows="3" placeholder="Nhập lý do xóa booking này..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-secondary" style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);">
                    🗑️ Xác nhận xóa
                </button>
                <a href="index.php?act=admin/quanLyBooking" class="btn btn-secondary">
                    ✕ Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
