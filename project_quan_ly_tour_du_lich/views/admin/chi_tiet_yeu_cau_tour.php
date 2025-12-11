<?php
$pageTitle = 'Chi tiết Yêu cầu Tour';
$currentPage = 'yeuCauTour';
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

    .two-column-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    @media (max-width: 992px) {
        .two-column-layout {
            grid-template-columns: 1fr;
        }
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
        padding: 12px 0;
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
        min-height: 120px;
    }

    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

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

    .badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-warning {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .badge-success {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
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

    .tour-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .tour-item {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .tour-item:last-child {
        border-bottom: none;
    }

    .tour-item h6 {
        color: var(--text-light);
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .tour-item p {
        color: var(--text-muted);
        font-size: 12px;
        margin-bottom: 10px;
        line-height: 1.6;
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>⭐ Chi tiết Yêu cầu Tour</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">Xem và phản hồi yêu cầu từ khách hàng</p>
        </div>
        <div>
            <a href="index.php?act=admin/quanLyYeuCauTour" class="btn btn-secondary">
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

<div class="two-column-layout">
    <!-- Main Content -->
    <div>
        <!-- Thông tin yêu cầu -->
        <div class="info-card">
            <div class="info-card-header">
                ℹ️ Thông tin Yêu cầu
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <div class="info-label">Khách hàng:</div>
                    <div class="info-value">
                        <strong><?php echo htmlspecialchars($yeuCau['nguoi_gui_ten'] ?? 'N/A'); ?></strong>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?php echo htmlspecialchars($thongTin['Email'] ?? 'N/A'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Số điện thoại:</div>
                    <div class="info-value"><?php echo htmlspecialchars($thongTin['Số điện thoại'] ?? 'N/A'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Địa điểm:</div>
                    <div class="info-value">
                        <strong><?php echo htmlspecialchars($thongTin['Địa điểm'] ?? 'N/A'); ?></strong>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Thời gian:</div>
                    <div class="info-value"><?php echo htmlspecialchars($thongTin['Thời gian'] ?? 'N/A'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Số người:</div>
                    <div class="info-value"><?php echo htmlspecialchars($thongTin['Số người'] ?? 'N/A'); ?></div>
                </div>
                <?php if (!empty($thongTin['Ngân sách'])): ?>
                <div class="info-row">
                    <div class="info-label">Ngân sách:</div>
                    <div class="info-value"><?php echo htmlspecialchars($thongTin['Ngân sách']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($thongTin['Yêu cầu đặc biệt'])): ?>
                <div class="info-row">
                    <div class="info-label">Yêu cầu đặc biệt:</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($thongTin['Yêu cầu đặc biệt'])); ?></div>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <div class="info-label">Thời gian gửi:</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($yeuCau['created_at'])); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Trạng thái:</div>
                    <div class="info-value">
                        <?php if ($yeuCau['trang_thai'] === 'DaGui'): ?>
                            <span class="badge badge-warning">Chờ xử lý</span>
                        <?php elseif (strpos($yeuCau['noi_dung'] ?? '', 'Đã xử lý') !== false): ?>
                            <span class="badge badge-success">Đã xử lý</span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?php echo htmlspecialchars($yeuCau['trang_thai'] ?? 'N/A'); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form phản hồi -->
        <div class="info-card">
            <div class="info-card-header">
                💬 Phản hồi Yêu cầu
            </div>
            <div class="info-card-body">
                <form method="POST" action="index.php?act=admin/phanHoiYeuCauTour">
                    <input type="hidden" name="yeu_cau_id" value="<?php echo $yeuCau['id']; ?>">
                    
                    <div class="form-group">
                        <label>📊 Trạng thái xử lý</label>
                        <select name="trang_thai" class="select">
                            <option value="DaXuLy">Đã xử lý</option>
                            <option value="DaGui" selected>Chờ xử lý</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>💬 Nội dung phản hồi <span style="color: #dc3545;">*</span></label>
                        <textarea name="phan_hoi" class="textarea" rows="6" required placeholder="Nhập nội dung phản hồi cho khách hàng..."></textarea>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                            ℹ️ Thông báo này sẽ được gửi đến khách hàng qua hệ thống.
                        </small>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">
                            📤 Gửi phản hồi
                        </button>
                        <a href="index.php?act=admin/quanLyYeuCauTour" class="btn btn-secondary">
                            ✕ Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar: Gợi ý Tour -->
    <div>
        <div class="info-card">
            <div class="info-card-header">
                💡 Gợi ý Tour tương tự
            </div>
            <div class="info-card-body">
                <?php if (!empty($tourList)): ?>
                    <ul class="tour-list">
                        <?php 
                        $count = 0;
                        foreach ($tourList as $tour): 
                            if ($count >= 5) break;
                            $count++;
                        ?>
                            <li class="tour-item">
                                <h6><?php echo htmlspecialchars($tour['ten_tour'] ?? 'N/A'); ?></h6>
                                <p>
                                    <?php echo htmlspecialchars(mb_substr($tour['mo_ta'] ?? '', 0, 80)); ?>...
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                    <span style="color: var(--accent-gold); font-weight: 700; font-size: 14px;">
                                        <?php echo number_format((float)($tour['gia_co_ban'] ?? 0)); ?>đ
                                    </span>
                                    <a href="index.php?act=admin/chiTietTour&id=<?php echo $tour['tour_id']; ?>" class="btn btn-secondary btn-sm">
                                        👁️ Xem chi tiết
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--text-muted); margin: 0;">Chưa có tour nào trong hệ thống.</p>
                <?php endif; ?>
                
                <div style="margin-top: 20px;">
                    <a href="index.php?act=admin/taoTour" class="btn btn-primary" style="width: 100%;">
                        ➕ Tạo tour mới
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
