<?php
$statusMap = [
    'ChoXacNhan' => ['text' => 'Chờ xác nhận', 'class' => 'warning'],
    'DaXacNhan' => ['text' => 'Đã xác nhận', 'class' => 'success'],
    'TuChoi' => ['text' => 'Từ chối', 'class' => 'danger'],
    'HoanTat' => ['text' => 'Hoàn tất', 'class' => 'info'],
    'Huy' => ['text' => 'Hủy', 'class' => 'secondary'],
];
$loaiDichVuMap = [
    'Xe' => 'Xe',
    'KhachSan' => 'Khách sạn',
    'Ve' => 'Vé',
    'VeMayBay' => 'Vé máy bay',
    'NhaHang' => 'Nhà hàng',
    'DiemThamQuan' => 'Điểm tham quan',
    'Visa' => 'Visa',
    'BaoHiem' => 'Bảo hiểm',
    'Khac' => 'Khác'
];
$nccId = $_GET['ncc_id'] ?? $dichVu['nha_cung_cap_id'] ?? null;
$pageTitle = 'Chi tiết dịch vụ';
$currentPage = 'nha_cung_cap';
ob_start();
?>
<style>
    .info-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }
    .info-card .card-header {
        background: rgba(0, 123, 255, 0.3);
        color: var(--text-light);
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .info-card .card-body {
        padding: 20px;
        color: var(--text-light);
    }
    .badge {
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .bg-info { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
    .bg-success { background: rgba(40, 167, 69, 0.3); color: #5cb85c; }
    .bg-warning { background: rgba(255, 193, 7, 0.3); color: #ffc107; }
    .bg-danger { background: rgba(220, 53, 69, 0.3); color: #dc3545; }
    .bg-secondary { background: rgba(108, 117, 125, 0.3); color: #adb5bd; }
    .text-success { color: #5cb85c !important; }
    .text-muted { color: var(--text-muted) !important; }
    .alert {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    .btn-outline-secondary {
        background: rgba(108, 117, 125, 0.3);
        color: var(--text-light);
        border: 1px solid rgba(108, 117, 125, 0.5);
    }
    .btn-outline-secondary:hover {
        background: rgba(108, 117, 125, 0.5);
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
    .col-lg-4 { width: 33.333333%; }
    .col-lg-8 { width: 66.666667%; }
    .mb-0 { margin-bottom: 0; }
    .mb-1 { margin-bottom: 0.25rem; }
    .mb-2 { margin-bottom: 0.5rem; }
    .mb-3 { margin-bottom: 1rem; }
    .mb-4 { margin-bottom: 1.5rem; }
    .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
    .d-flex { display: flex; }
    .justify-content-between { justify-content: space-between; }
    .align-items-center { align-items: center; }
    .fw-bold { font-weight: 700; }
    .fs-5 { font-size: 1.25rem; }
    .fs-6 { font-size: 1rem; }
    .card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        backdrop-filter: blur(10px);
    }
    .card-header {
        background: rgba(0, 123, 255, 0.3);
        color: var(--text-light);
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .card-body {
        padding: 20px;
        color: var(--text-light);
    }
    @media (max-width: 992px) {
        .col-lg-4, .col-lg-8 {
            width: 100%;
        }
    }
</style>

<div style="padding: 20px;">
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-info-circle" style="color: var(--accent-gold);"></i> Chi tiết dịch vụ
        </h1>
        <a href="index.php?act=admin/nhaCungCap<?php echo $nccId ? '&id=' . $nccId : ''; ?>" style="background: rgba(108, 117, 125, 0.3); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(108, 117, 125, 0.5);">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <div><i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <div><i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Thông tin dịch vụ</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Tên dịch vụ:</strong></div>
                        <div class="col-md-8"><?php echo htmlspecialchars($dichVu['ten_dich_vu']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Loại dịch vụ:</strong></div>
                        <div class="col-md-8">
                            <span class="badge bg-info text-dark">
                                <?php echo $loaiDichVuMap[$dichVu['loai_dich_vu']] ?? $dichVu['loai_dich_vu']; ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Số lượng:</strong></div>
                        <div class="col-md-8">
                            <?php echo $dichVu['so_luong']; ?>
                            <?php if ($dichVu['don_vi']): ?>
                                <span class="text-muted"><?php echo htmlspecialchars($dichVu['don_vi']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($dichVu['ngay_bat_dau'] || $dichVu['ngay_ket_thuc']): ?>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Thời gian:</strong></div>
                        <div class="col-md-8">
                            <?php if ($dichVu['ngay_bat_dau']): ?>
                                <i class="bi bi-calendar-event"></i> Bắt đầu: <?php echo date('d/m/Y', strtotime($dichVu['ngay_bat_dau'])); ?>
                                <?php if ($dichVu['gio_bat_dau']): ?>
                                    <?php echo date('H:i', strtotime($dichVu['gio_bat_dau'])); ?>
                                <?php endif; ?>
                                <br>
                            <?php endif; ?>
                            <?php if ($dichVu['ngay_ket_thuc']): ?>
                                <i class="bi bi-calendar-check"></i> Kết thúc: <?php echo date('d/m/Y', strtotime($dichVu['ngay_ket_thuc'])); ?>
                                <?php if ($dichVu['gio_ket_thuc']): ?>
                                    <?php echo date('H:i', strtotime($dichVu['gio_ket_thuc'])); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($dichVu['dia_diem']): ?>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Địa điểm:</strong></div>
                        <div class="col-md-8">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($dichVu['dia_diem']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Giá tiền:</strong></div>
                        <div class="col-md-8">
                            <?php if ($dichVu['gia_tien']): ?>
                                <strong class="text-success fs-5"><?php echo number_format($dichVu['gia_tien'], 0, ',', '.'); ?>đ</strong>
                            <?php else: ?>
                                <span class="text-muted">Chưa có giá</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Trạng thái:</strong></div>
                        <div class="col-md-8">
                            <?php $status = $statusMap[$dichVu['trang_thai']] ?? ['text' => $dichVu['trang_thai'], 'class' => 'secondary']; ?>
                            <span class="badge bg-<?php echo $status['class']; ?> fs-6">
                                <?php echo $status['text']; ?>
                            </span>
                            <?php if (!empty($dichVu['thoi_gian_xac_nhan'])): ?>
                                <br><small class="text-muted">
                                    Xác nhận lúc: <?php echo date('d/m/Y H:i', strtotime($dichVu['thoi_gian_xac_nhan'])); ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($dichVu['ghi_chu']): ?>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Ghi chú:</strong></div>
                        <div class="col-md-8">
                            <div class="border rounded p-3 bg-light">
                                <?php echo nl2br(htmlspecialchars($dichVu['ghi_chu'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Nhà cung cấp</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Tên đơn vị:</strong><br>
                        <span class="fs-5"><?php echo htmlspecialchars($dichVu['nha_cung_cap_ten'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Thông tin tour</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Tên tour:</strong><br>
                        <span class="fs-5"><?php echo htmlspecialchars($dichVu['ten_tour'] ?? 'N/A'); ?></span>
                    </div>
                    <?php if ($dichVu['ngay_khoi_hanh']): ?>
                    <div class="mb-3">
                        <strong>Ngày khởi hành:</strong><br>
                        <i class="bi bi-calendar-event"></i> <?php echo date('d/m/Y', strtotime($dichVu['ngay_khoi_hanh'])); ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($dichVu['ngay_ket_thuc']): ?>
                    <div class="mb-3">
                        <strong>Ngày kết thúc:</strong><br>
                        <i class="bi bi-calendar-check"></i> <?php echo date('d/m/Y', strtotime($dichVu['ngay_ket_thuc'])); ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($dichVu['tour_mo_ta']): ?>
                    <div class="mb-3">
                        <strong>Mô tả tour:</strong><br>
                        <small class="text-muted"><?php echo nl2br(htmlspecialchars($dichVu['tour_mo_ta'])); ?></small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Lịch sử</h5>
                </div>
                <div class="card-body">
                    <?php if ($dichVu['created_at']): ?>
                    <div class="mb-2">
                        <small class="text-muted">Tạo lúc:</small><br>
                        <i class="bi bi-calendar-plus"></i> <?php echo date('d/m/Y H:i', strtotime($dichVu['created_at'])); ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($dichVu['updated_at']): ?>
                    <div class="mb-2">
                        <small class="text-muted">Cập nhật lúc:</small><br>
                        <i class="bi bi-pencil"></i> <?php echo date('d/m/Y H:i', strtotime($dichVu['updated_at'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>

