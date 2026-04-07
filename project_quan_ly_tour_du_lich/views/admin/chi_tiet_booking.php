<?php
$pageTitle = 'Chi tiết Booking';
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

    .info-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .info-card-header {
        background: rgba(212, 175, 55, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        color: var(--accent-gold);
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .info-card-body {
        padding: 25px;
    }

    .info-row {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: var(--text-muted);
        width: 200px;
        flex-shrink: 0;
        font-size: 13px;
    }

    .info-value {
        flex: 1;
        color: var(--text-light);
        font-size: 13px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.5px;
        display: inline-block;
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

    .form-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .form-card-header {
        background: rgba(212, 175, 55, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        color: var(--accent-gold);
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .form-card-body {
        padding: 25px;
    }

    .table-history {
        width: 100%;
        border-collapse: collapse;
    }

    .table-history thead {
        background: rgba(212, 175, 55, 0.1);
    }

    .table-history th {
        padding: 15px;
        text-align: left;
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--accent-gold);
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table-history td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        font-size: 13px;
    }

    .table-history tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .booking-id {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        color: var(--accent-gold);
        font-size: 1.25rem;
    }

    .empty-history {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-history-icon {
        font-size: 64px;
        opacity: 0.3;
        margin-bottom: 20px;
    }

    .two-column-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .two-column-layout {
            grid-template-columns: 1fr;
        }
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

    .alert-info {
        background: rgba(13, 202, 240, 0.1);
        border-color: rgba(13, 202, 240, 0.3);
        color: #0dcaf0;
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
    .form-group .select,
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
    .form-group .select:focus,
    .form-group textarea:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-gold);
    }

    .form-group .select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23d4af37' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-primary {
        background: rgba(13, 110, 253, 0.2);
        color: #0d6efd;
        border: 1px solid rgba(13, 110, 253, 0.3);
    }

    .badge-success {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .badge-warning {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .badge-secondary {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>📋 Chi Tiết Booking</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">
                Mã booking: <span class="booking-id">#<?php echo $booking['booking_id']; ?></span>
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="index.php?act=booking/xuatTaiLieu&id=<?php echo $booking['booking_id']; ?>" 
                   class="btn btn-primary" target="_blank">
                    📄 Xuất tài liệu
                </a>
                <a href="index.php?act=admin/quanLyBooking" class="btn btn-secondary">
                    ← Quay lại danh sách
                </a>
                <a href="index.php?act=admin/thanhToanBooking&id=<?php echo $booking['booking_id']; ?>" class="aventura-btn aventura-btn-gold" style="font-size:1.1em;">
                    <i class="bi bi-credit-card"></i> Thanh toán
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

<?php $validationPayload = getValidationErrors(); ?>
<?php $validationErrors = $validationPayload['errors'] ?? []; ?>

<div class="two-column-layout">
    <!-- Left Column - Booking Information -->
    <div>
        <!-- Thông tin booking -->
        <div class="info-card">
            <div class="info-card-header">
                ℹ️ Thông tin Booking
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <div class="info-label"># Mã Booking</div>
                    <div class="info-value">
                        <span class="booking-id">#<?php echo $booking['booking_id']; ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">📍 Tour</div>
                    <div class="info-value" style="font-weight: 600;">
                        <?php echo htmlspecialchars($booking['ten_tour'] ?? 'N/A'); ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">👥 Số lượng người</div>
                    <div class="info-value">
                        <span class="badge badge-primary">
                            <?php echo $booking['so_nguoi']; ?> người
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">📅 Ngày đặt</div>
                    <div class="info-value">
                        <?php echo date('d/m/Y', strtotime($booking['ngay_dat'])); ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">📆 Ngày khởi hành</div>
                    <div class="info-value">
                        <?php echo $booking['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'N/A'; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">✅ Ngày kết thúc</div>
                    <div class="info-value">
                        <?php 
                            $endDate = $booking['ngay_ket_thuc'] ?? $booking['ngay_khoi_hanh'];
                            echo $endDate ? date('d/m/Y', strtotime($endDate)) : 'N/A'; 
                        ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">💰 Tổng tiền</div>
                    <div class="info-value" style="font-weight: 600; color: var(--accent-gold); font-size: 16px;">
                        <?php echo number_format($booking['tong_tien'] ?? 0); ?> ₫
                    </div>
                </div>
                <?php
                // Tính toán thông tin tiền cọc
                $tongTien = (float)($booking['tong_tien'] ?? 0);
                $tienCoc = (float)($booking['tien_coc'] ?? ($booking['so_tien_coc'] ?? 0));
                
                // Nếu trạng thái là "Hoàn tất", tiền cọc = tổng tiền (đã thanh toán đủ)
                if ($booking['trang_thai'] == 'HoanTat' && $tongTien > 0) {
                    $tienCoc = $tongTien;
                    $trangThaiCoc = 'HoanTat';
                } else {
                // Nếu chưa có tiền cọc trong DB, tính 30% tổng tiền làm mặc định
                if ($tienCoc == 0 && $tongTien > 0) {
                    $tienCoc = round($tongTien * 0.3);
                    }
                    $trangThaiCoc = $booking['trang_thai_coc'] ?? ($booking['trang_thai'] == 'DaCoc' ? 'DaCoc' : 'ChuaCoc');
                }
                $tienConLai = max(0, $tongTien - $tienCoc);
                ?>
                <div class="info-row">
                    <div class="info-label">💳 Số tiền cọc</div>
                    <div class="info-value" style="font-weight: 600; color: #0d6efd; font-size: 16px;">
                        <?php echo number_format($tienCoc); ?> ₫
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">✓ Trạng thái cọc</div>
                    <div class="info-value">
                        <?php
                        $trangThaiCocLabels = [
                            'DaCoc' => 'Đã cọc',
                            'ChuaCoc' => 'Chưa cọc',
                            'HoanTat' => 'Hoàn tất'
                        ];
                        $trangThaiCocBadge = $trangThaiCoc == 'DaCoc' ? 'success' : ($trangThaiCoc == 'HoanTat' ? 'success' : 'warning');
                        ?>
                        <span class="badge badge-<?php echo $trangThaiCocBadge; ?>">
                            <?php echo $trangThaiCocLabels[$trangThaiCoc] ?? $trangThaiCoc; ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">💵 Số tiền còn lại</div>
                    <div class="info-value" style="font-weight: 600; color: #ffc107; font-size: 16px;">
                        <?php echo number_format($tienConLai); ?> ₫
                        <?php if ($tienConLai > 0): ?>
                            <small style="display: block; margin-top: 5px; color: var(--text-muted); font-size: 11px;">
                                (<?php echo round(($tienConLai / $tongTien) * 100, 1); ?>% tổng tiền)
                            </small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">🚩 Trạng thái booking</div>
                    <div class="info-value">
                        <?php
                        $statusLabels = [
                            'ChoXacNhan' => 'Chờ xác nhận',
                            'DaCoc' => 'Đã cọc',
                            'HoanTat' => 'Hoàn tất',
                            'Huy' => 'Hủy'
                        ];
                        $currentStatus = $booking['trang_thai'];
                        ?>
                        <span class="status-badge status-<?php echo $currentStatus; ?>">
                            <?php echo $statusLabels[$currentStatus] ?? $currentStatus; ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">📝 Ghi chú</div>
                    <div class="info-value">
                        <?php echo nl2br(htmlspecialchars($booking['ghi_chu'] ?? 'Không có ghi chú')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="info-card">
            <div class="info-card-header">
                👤 Thông tin người đặt
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <div class="info-label">👤 Họ tên</div>
                    <div class="info-value" style="font-weight: 600;">
                        <?php echo htmlspecialchars($booking['ho_ten'] ?? 'N/A'); ?>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">✉️ Email</div>
                    <div class="info-value">
                        <a href="mailto:<?php echo htmlspecialchars($booking['email'] ?? ''); ?>" 
                           style="color: var(--accent-gold); text-decoration: none;">
                            <?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?>
                        </a>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">📞 Số điện thoại</div>
                    <div class="info-value">
                        <a href="tel:<?php echo htmlspecialchars($booking['so_dien_thoai'] ?? ''); ?>" 
                           style="color: var(--accent-gold); text-decoration: none;">
                            <?php echo htmlspecialchars($booking['so_dien_thoai'] ?? 'N/A'); ?>
                        </a>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">📍 Địa chỉ</div>
                    <div class="info-value">
                        <?php echo htmlspecialchars($booking['dia_chi'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yêu cầu Tour (nếu có) -->
        <?php if (!empty($yeuCauTour)): ?>
            <?php
                // Parse thông tin từ nội dung
                $thongTinYeuCau = [];
                foreach (explode("\n", $yeuCauTour['noi_dung'] ?? '') as $row) {
                    $kv = explode(": ", $row, 2);
                    if (count($kv) == 2) {
                        $thongTinYeuCau[$kv[0]] = $kv[1];
                    }
                }
            ?>
            <div class="info-card" style="margin-top: 30px;">
                <div class="info-card-header" style="background: rgba(245, 87, 108, 0.2);">
                    ⭐ Yêu cầu Tour từ Khách hàng
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <div class="info-label">📍 Địa điểm</div>
                        <div class="info-value" style="font-weight: 600;">
                            <?php echo htmlspecialchars($thongTinYeuCau['Địa điểm'] ?? 'N/A'); ?>
                        </div>
                    </div>
                    <?php if (!empty($thongTinYeuCau['Thời gian'])): ?>
                    <div class="info-row">
                        <div class="info-label">📅 Thời gian</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($thongTinYeuCau['Thời gian']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($thongTinYeuCau['Số người'])): ?>
                    <div class="info-row">
                        <div class="info-label">👥 Số người</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($thongTinYeuCau['Số người']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($thongTinYeuCau['Ngân sách'])): ?>
                    <div class="info-row">
                        <div class="info-label">💰 Ngân sách</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($thongTinYeuCau['Ngân sách']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($thongTinYeuCau['Yêu cầu đặc biệt'])): ?>
                    <div class="info-row">
                        <div class="info-label">📋 Yêu cầu đặc biệt</div>
                        <div class="info-value">
                            <?php echo nl2br(htmlspecialchars($thongTinYeuCau['Yêu cầu đặc biệt'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">🕐 Thời gian gửi</div>
                        <div class="info-value">
                            <small style="color: var(--text-muted);">
                                <?php echo date('d/m/Y H:i', strtotime($yeuCauTour['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <a href="index.php?act=admin/chiTietYeuCauTour&id=<?php echo $yeuCauTour['id']; ?>" 
                           class="btn btn-secondary" style="width: 100%;">
                            👁️ Xem chi tiết & Phản hồi
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Forms -->
    <div>
        <?php if (hasRole(['Admin', 'HDV'])): ?>
            <!-- Cập nhật thông tin Booking -->
            <div class="form-card">
                <div class="form-card-header">
                    ✏️ Cập nhật thông tin Booking
                </div>
                <div class="form-card-body">
                    <form method="POST" action="index.php?act=booking/update">
                        <?php echo csrfField('booking_update'); ?>
                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>👥 Số lượng người <span style="color: #dc3545;">*</span></label>
                                <input type="number" name="so_nguoi" class="input" 
                                       value="<?php echo $booking['so_nguoi']; ?>" min="1" required>
                                <?php if (!empty($validationErrors['so_nguoi'])): ?>
                                    <small style="color:#ffb4b4;display:block;margin-top:6px;">Số lượng người không hợp lệ.</small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label>💰 Tổng tiền <span style="color: #dc3545;">*</span></label>
                                <input type="number" name="tong_tien" id="tongTienInput" class="input" 
                                       value="<?php echo $booking['tong_tien']; ?>" step="1000" min="0" required>
                                <?php if (!empty($validationErrors['tong_tien'])): ?>
                                    <small style="color:#ffb4b4;display:block;margin-top:6px;">Tổng tiền không hợp lệ.</small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label>📆 Ngày khởi hành</label>
                                <input type="date" name="ngay_khoi_hanh" class="input" 
                                       value="<?php echo $booking['ngay_khoi_hanh'] ?? ''; ?>">
                            </div>

                            <div class="form-group">
                                <label>✅ Ngày kết thúc</label>
                                <input type="date" name="ngay_ket_thuc" class="input"
                                       value="<?php echo $booking['ngay_ket_thuc'] ?? $booking['ngay_khoi_hanh'] ?? ''; ?>">
                                <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                                    Để trống sẽ dùng ngày khởi hành
                                </small>
                            </div>

                            <div class="form-group">
                                <label>💳 Tiền cọc</label>
                                <input type="number" name="tien_coc" id="tienCocInput" class="input" 
                                       value="<?php echo $tienCoc; ?>" step="1000" min="0">
                                <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                                    Để trống sẽ tự động tính 30% tổng tiền
                                </small>
                                <?php if (!empty($validationErrors['tien_coc'])): ?>
                                    <small style="color:#ffb4b4;display:block;margin-top:6px;">Tiền cọc không hợp lệ hoặc lớn hơn tổng tiền.</small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label>🚩 Trạng thái Booking</label>
                                <select name="trang_thai" id="trangThaiSelect" class="select">
                                    <option value="ChoXacNhan" <?php echo $currentStatus == 'ChoXacNhan' ? 'selected' : ''; ?>>
                                        Chờ xác nhận
                                    </option>
                                    <option value="DaCoc" <?php echo $currentStatus == 'DaCoc' ? 'selected' : ''; ?>>
                                        Đã cọc
                                    </option>
                                    <option value="HoanTat" <?php echo $currentStatus == 'HoanTat' ? 'selected' : ''; ?>>
                                        Hoàn tất
                                    </option>
                                    <option value="Huy" <?php echo $currentStatus == 'Huy' ? 'selected' : ''; ?>>
                                        Hủy
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>✓ Trạng thái cọc</label>
                                <select name="trang_thai_coc" id="trangThaiCocSelect" class="select">
                                    <option value="ChuaCoc" <?php echo $trangThaiCoc == 'ChuaCoc' ? 'selected' : ''; ?>>
                                        Chưa cọc
                                    </option>
                                    <option value="DaCoc" <?php echo $trangThaiCoc == 'DaCoc' ? 'selected' : ''; ?>>
                                        Đã cọc
                                    </option>
                                    <option value="HoanTat" <?php echo $trangThaiCoc == 'HoanTat' ? 'selected' : ''; ?>>
                                        Hoàn tất thanh toán
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div id="tienCocWarning" class="alert alert-info" style="display: none;">
                                ℹ️ <strong>Lưu ý:</strong> Khi chọn "Hoàn tất", tiền cọc sẽ tự động bằng tổng tiền (đã thanh toán đủ).
                            </div>
                        </div>

                        <div class="form-group">
                            <label>📝 Ghi chú</label>
                            <textarea name="ghi_chu" class="textarea"><?php echo htmlspecialchars($booking['ghi_chu'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                💾 Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lịch sử thay đổi -->
<div class="info-card">
    <div class="info-card-header">
        🕐 Lịch sử thay đổi trạng thái
    </div>
    <div style="padding: 0;">
        <?php if (!empty($history)): ?>
            <div style="overflow-x: auto;">
                <table class="table-history">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Trạng thái cũ</th>
                            <th>Trạng thái mới</th>
                            <th>Người thay đổi</th>
                            <th>Vai trò</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $item): ?>
                            <tr>
                                <td>
                                    <small style="color: var(--text-muted);">
                                        🕐 <?php echo date('d/m/Y H:i:s', strtotime($item['thoi_gian'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if ($item['trang_thai_cu']): ?>
                                        <span class="status-badge status-<?php echo $item['trang_thai_cu']; ?>">
                                            <?php echo $statusLabels[$item['trang_thai_cu']] ?? $item['trang_thai_cu']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $item['trang_thai_moi']; ?>">
                                        <?php echo $statusLabels[$item['trang_thai_moi']] ?? $item['trang_thai_moi']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($item['nguoi_thay_doi'] ?? 'N/A'); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo htmlspecialchars($item['vai_tro'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo nl2br(htmlspecialchars($item['ghi_chu'] ?? '-')); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-history">
                <div class="empty-history-icon">📭</div>
                <p style="margin: 0;">Chưa có lịch sử thay đổi nào</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Tự động đồng bộ trạng thái khi thay đổi tiền cọc hoặc trạng thái
    (function() {
        const tienCocInput = document.getElementById('tienCocInput');
        const tongTienInput = document.getElementById('tongTienInput');
        const trangThaiSelect = document.getElementById('trangThaiSelect');
        const trangThaiCocSelect = document.getElementById('trangThaiCocSelect');
        const tienCocWarning = document.getElementById('tienCocWarning');
        
        function getTongTien() {
            return parseFloat(tongTienInput?.value) || 0;
        }
        
        function updateWarning() {
            if (!tienCocWarning) return;
            const tongTien = getTongTien();
            const trangThai = trangThaiSelect?.value || '';
            const tienCoc = parseFloat(tienCocInput?.value) || 0;
            
            if (trangThai === 'HoanTat' || (tongTien > 0 && Math.abs(tienCoc - tongTien) < 0.01)) {
                tienCocWarning.style.display = 'block';
            } else {
                tienCocWarning.style.display = 'none';
            }
        }
        
        // Khi thay đổi tiền cọc
        if (tienCocInput) {
            tienCocInput.addEventListener('input', function() {
                const tongTien = getTongTien();
                const tienCoc = parseFloat(this.value) || 0;
                
                if (tongTien > 0 && Math.abs(tienCoc - tongTien) < 0.01) {
                    // Tiền cọc = tổng tiền → Hoàn tất
                    if (trangThaiSelect) trangThaiSelect.value = 'HoanTat';
                    if (trangThaiCocSelect) trangThaiCocSelect.value = 'HoanTat';
                } else if (tienCoc > 0 && tienCoc < tongTien) {
                    // Đã cọc một phần
                    if (trangThaiSelect && trangThaiSelect.value === 'ChoXacNhan') {
                        trangThaiSelect.value = 'DaCoc';
                    }
                    if (trangThaiCocSelect && trangThaiCocSelect.value === 'ChuaCoc') {
                        trangThaiCocSelect.value = 'DaCoc';
                    }
                }
                updateWarning();
            });
        }
        
        // Khi thay đổi tổng tiền
        if (tongTienInput) {
            tongTienInput.addEventListener('input', function() {
                const tongTien = getTongTien();
                const tienCoc = parseFloat(tienCocInput?.value) || 0;
                
                // Nếu tiền cọc > tổng tiền mới, giảm tiền cọc xuống
                if (tienCoc > tongTien && tongTien > 0) {
                    if (tienCocInput) tienCocInput.value = tongTien;
                }
                updateWarning();
            });
        }
        
        // Khi thay đổi trạng thái booking
        if (trangThaiSelect) {
            trangThaiSelect.addEventListener('change', function() {
                const tongTien = getTongTien();
                if (this.value === 'HoanTat' && tongTien > 0) {
                    // Hoàn tất → set tiền cọc = tổng tiền
                    if (tienCocInput) tienCocInput.value = tongTien;
                    if (trangThaiCocSelect) trangThaiCocSelect.value = 'HoanTat';
                }
                updateWarning();
            });
        }
        
        // Khi thay đổi trạng thái cọc
        if (trangThaiCocSelect) {
            trangThaiCocSelect.addEventListener('change', function() {
                const tongTien = getTongTien();
                if (this.value === 'HoanTat' && tongTien > 0) {
                    if (tienCocInput) tienCocInput.value = tongTien;
                    if (trangThaiSelect) trangThaiSelect.value = 'HoanTat';
                }
                updateWarning();
            });
        }
        
        // Kiểm tra khi trang load
        updateWarning();
    })();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
