<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết dịch vụ - Nhà cung cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/supplier.css">
</head>
<body class="supplier-body">
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
?>
<div class="container-fluid supplier-shell">
    <section class="supplier-page-header">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="supplier-eyebrow"><i class="bi bi-info-circle"></i> Chi tiết dịch vụ</span>
                <h1 class="supplier-page-title">Thông tin dịch vụ</h1>
                <p class="supplier-page-subtitle">Chi tiết đầy đủ của hạng mục đang được gán cho tour, với bố cục rõ ràng để tra cứu nhanh hơn.</p>
            </div>
            <div class="col-lg-4">
                <div class="supplier-header-actions">
                    <a href="index.php?act=nhaCungCap/baoGia" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Quay lại báo giá
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars((string)($_SESSION['success'] ?? ''), ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars((string)($_SESSION['error'] ?? ''), ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
        $currentTab = 'baoGia';
        include __DIR__ . '/partials/main_nav.php';
    ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card supplier-section-card">
                <div class="card-header">
                    <h5 class="supplier-card-title"><i class="bi bi-file-text"></i> Thông tin dịch vụ</h5>
                </div>
                <div class="card-body">
                    <div class="supplier-info-grid">
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Tên dịch vụ</div>
                            <div><?php echo htmlspecialchars($dichVu['ten_dich_vu']); ?></div>
                        </div>
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Loại dịch vụ</div>
                            <div><span class="supplier-badge-soft info"><?php echo $loaiDichVuMap[$dichVu['loai_dich_vu']] ?? $dichVu['loai_dich_vu']; ?></span></div>
                        </div>
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Số lượng</div>
                            <div><?php echo $dichVu['so_luong']; ?> <?php if ($dichVu['don_vi']): ?><span class="text-muted"><?php echo htmlspecialchars($dichVu['don_vi']); ?></span><?php endif; ?></div>
                        </div>
                        <?php if ($dichVu['ngay_bat_dau'] || $dichVu['ngay_ket_thuc']): ?>
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Thời gian</div>
                            <div>
                                <?php if ($dichVu['ngay_bat_dau']): ?>
                                    <i class="bi bi-calendar-event"></i> Bắt đầu: <?php echo date('d/m/Y', strtotime($dichVu['ngay_bat_dau'])); ?>
                                    <?php if ($dichVu['gio_bat_dau']): ?> <?php echo date('H:i', strtotime($dichVu['gio_bat_dau'])); ?><?php endif; ?>
                                    <br>
                                <?php endif; ?>
                                <?php if ($dichVu['ngay_ket_thuc']): ?>
                                    <i class="bi bi-calendar-check"></i> Kết thúc: <?php echo date('d/m/Y', strtotime($dichVu['ngay_ket_thuc'])); ?>
                                    <?php if ($dichVu['gio_ket_thuc']): ?> <?php echo date('H:i', strtotime($dichVu['gio_ket_thuc'])); ?><?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($dichVu['dia_diem']): ?>
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Địa điểm</div>
                            <div><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($dichVu['dia_diem']); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Giá tiền</div>
                            <div>
                                <?php if ($dichVu['gia_tien']): ?>
                                    <strong class="text-success fs-5"><?php echo number_format($dichVu['gia_tien'], 0, ',', '.'); ?>đ</strong>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có giá</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Trạng thái</div>
                            <div>
                                <?php $status = $statusMap[$dichVu['trang_thai']] ?? ['text' => $dichVu['trang_thai'], 'class' => 'secondary']; ?>
                                <span class="supplier-badge-soft <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span>
                                <?php if (!empty($dichVu['thoi_gian_xac_nhan'])): ?>
                                    <br><small class="text-muted">Xác nhận lúc: <?php echo date('d/m/Y H:i', strtotime($dichVu['thoi_gian_xac_nhan'])); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($dichVu['ghi_chu']): ?>
                        <div class="supplier-info-row">
                            <div class="supplier-info-label">Ghi chú</div>
                            <div><div class="supplier-note-box"><?php echo nl2br(htmlspecialchars($dichVu['ghi_chu'])); ?></div></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card supplier-section-card mb-4">
                <div class="card-header">
                    <h5 class="supplier-card-title"><i class="bi bi-calendar3"></i> Thông tin tour</h5>
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
                    <div class="mb-0">
                        <strong>Mô tả tour:</strong><br>
                        <small class="text-muted"><?php echo nl2br(htmlspecialchars($dichVu['tour_mo_ta'])); ?></small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card supplier-section-card">
                <div class="card-header">
                    <h5 class="supplier-card-title"><i class="bi bi-clock-history"></i> Mốc thời gian</h5>
                </div>
                <div class="card-body">
                    <?php if ($dichVu['created_at']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Tạo lúc</small>
                        <i class="bi bi-calendar-plus"></i> <?php echo date('d/m/Y H:i', strtotime($dichVu['created_at'])); ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($dichVu['updated_at']): ?>
                    <div class="mb-0">
                        <small class="text-muted d-block">Cập nhật lúc</small>
                        <i class="bi bi-pencil"></i> <?php echo date('d/m/Y H:i', strtotime($dichVu['updated_at'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
