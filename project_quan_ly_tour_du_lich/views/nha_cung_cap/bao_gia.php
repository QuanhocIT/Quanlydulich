<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo giá - Nhà cung cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/supplier.css">
</head>
<body class="supplier-body">
<?php
    $baoGiaStats = $baoGiaStats ?? ['cho_xac_nhan' => 0, 'da_xac_nhan' => 0, 'tu_choi' => 0, 'hoan_tat' => 0, 'tong' => 0];
    $filterLoai = $filterLoai ?? null;
    $keyword = $keyword ?? '';
    $lichKhoiHanhOptions = $lichKhoiHanhOptions ?? [];
    $catalogServices = $catalogServices ?? [];
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
                <span class="supplier-eyebrow"><i class="bi bi-file-earmark-text"></i> Báo giá</span>
                <h1 class="supplier-page-title">Quản lý báo giá</h1>
                <p class="supplier-page-subtitle">Kiểm soát yêu cầu báo giá, tra cứu theo trạng thái và gửi đề xuất thủ công trong cùng một giao diện đồng bộ.</p>
            </div>
            <div class="col-lg-4">
                <div class="supplier-header-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBaoGiaThuCong">
                        <i class="bi bi-plus-circle"></i> Gửi báo giá thủ công
                    </button>
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

    <div class="supplier-stats-grid">
        <div class="supplier-stat-card warning">
            <div class="supplier-stat-label">Chờ xác nhận</div>
            <p class="supplier-stat-value"><?php echo $baoGiaStats['cho_xac_nhan'] ?? 0; ?></p>
            <div class="supplier-stat-meta">Các báo giá đang cần bạn phản hồi.</div>
        </div>
        <div class="supplier-stat-card success">
            <div class="supplier-stat-label">Đã xác nhận</div>
            <p class="supplier-stat-value"><?php echo $baoGiaStats['da_xac_nhan'] ?? 0; ?></p>
            <div class="supplier-stat-meta">Những mục đã được điều hành chốt.</div>
        </div>
        <div class="supplier-stat-card primary">
            <div class="supplier-stat-label">Tổng báo giá</div>
            <p class="supplier-stat-value"><?php echo $baoGiaStats['tong'] ?? 0; ?></p>
            <div class="supplier-stat-meta">Tất cả yêu cầu bạn đang theo dõi.</div>
        </div>
        <div class="supplier-stat-card info">
            <div class="supplier-stat-label">Hoàn tất</div>
            <p class="supplier-stat-value"><?php echo $baoGiaStats['hoan_tat'] ?? 0; ?></p>
            <div class="supplier-stat-meta">Những dịch vụ đã đi hết vòng đời xử lý.</div>
        </div>
    </div>

    <div class="card supplier-section-card supplier-filter-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="supplier-card-title mb-0"><i class="bi bi-funnel"></i> Bộ lọc trạng thái</h5>
                <div class="supplier-filter-pills">
                    <a href="index.php?act=nhaCungCap/baoGia" class="btn btn-sm <?php echo !$trangThai ? 'btn-primary' : 'btn-outline-primary'; ?>">Tất cả (<?php echo $baoGiaStats['tong'] ?? 0; ?>)</a>
                    <a href="index.php?act=nhaCungCap/baoGia&trang_thai=ChoXacNhan" class="btn btn-sm <?php echo $trangThai === 'ChoXacNhan' ? 'btn-warning text-dark' : 'btn-outline-warning'; ?>">Chờ xác nhận</a>
                    <a href="index.php?act=nhaCungCap/baoGia&trang_thai=DaXacNhan" class="btn btn-sm <?php echo $trangThai === 'DaXacNhan' ? 'btn-success' : 'btn-outline-success'; ?>">Đã xác nhận</a>
                    <a href="index.php?act=nhaCungCap/baoGia&trang_thai=TuChoi" class="btn btn-sm <?php echo $trangThai === 'TuChoi' ? 'btn-danger' : 'btn-outline-danger'; ?>">Từ chối</a>
                    <a href="index.php?act=nhaCungCap/baoGia&trang_thai=HoanTat" class="btn btn-sm <?php echo $trangThai === 'HoanTat' ? 'btn-info text-white' : 'btn-outline-info'; ?>">Hoàn tất</a>
                </div>
            </div>

            <form class="row g-3 align-items-end mt-2" method="GET" action="index.php">
                <input type="hidden" name="act" value="nhaCungCap/baoGia">
                <?php if ($trangThai): ?><input type="hidden" name="trang_thai" value="<?php echo htmlspecialchars($trangThai); ?>"><?php endif; ?>
                <div class="col-md-4">
                    <label class="form-label">Loại dịch vụ</label>
                    <select name="loai" class="form-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($loaiDichVuMap as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($filterLoai === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" name="keyword" class="form-control" placeholder="Tìm tour hoặc dịch vụ" value="<?php echo htmlspecialchars($keyword); ?>">
                </div>
                <div class="col-md-4 d-flex flex-wrap gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Lọc</button>
                    <a href="index.php?act=nhaCungCap/baoGia" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card supplier-section-card">
        <div class="card-header">
            <h5 class="supplier-card-title"><i class="bi bi-list-ul"></i> Danh sách dịch vụ</h5>
            <div class="supplier-card-subtitle">Dữ liệu được chuẩn hóa theo một cấu trúc thống nhất để tiện rà soát và xử lý.</div>
        </div>
        <div class="card-body">
            <?php if (empty($dichVu)): ?>
                <div class="supplier-empty-state">
                    <div class="supplier-empty-icon"><i class="bi bi-inbox"></i></div>
                    <p class="mb-0">Không có dịch vụ nào phù hợp với bộ lọc hiện tại.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table supplier-table align-middle">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Loại dịch vụ</th>
                                <th>Tên dịch vụ</th>
                                <th>Số lượng</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Giá tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dichVu as $dv): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($dv['ten_tour'] ?? 'N/A'); ?></strong>
                                        <?php if ($dv['ngay_khoi_hanh']): ?><br><small class="text-muted"><i class="bi bi-calendar"></i> <?php echo date('d/m/Y', strtotime($dv['ngay_khoi_hanh'])); ?></small><?php endif; ?>
                                    </td>
                                    <td><span class="supplier-badge-soft info"><?php echo $loaiDichVuMap[$dv['loai_dich_vu']] ?? $dv['loai_dich_vu']; ?></span></td>
                                    <td><?php echo htmlspecialchars($dv['ten_dich_vu']); ?></td>
                                    <td>
                                        <?php echo $dv['so_luong']; ?>
                                        <?php if ($dv['don_vi']): ?><small class="text-muted"><?php echo $dv['don_vi']; ?></small><?php endif; ?>
                                    </td>
                                    <td><?php echo $dv['ngay_bat_dau'] ? date('d/m/Y', strtotime($dv['ngay_bat_dau'])) : '<span class="text-muted">-</span>'; ?></td>
                                    <td><?php echo $dv['ngay_ket_thuc'] ? date('d/m/Y', strtotime($dv['ngay_ket_thuc'])) : '<span class="text-muted">-</span>'; ?></td>
                                    <td>
                                        <?php if ($dv['gia_tien']): ?>
                                            <strong class="text-success"><?php echo number_format($dv['gia_tien'], 0, ',', '.'); ?>đ</strong>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa có giá</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $status = $statusMap[$dv['trang_thai']] ?? ['text' => $dv['trang_thai'], 'class' => 'secondary']; ?>
                                        <span class="supplier-badge-soft <?php echo $status['class']; ?>">
                                            <?php echo $status['text']; ?>
                                        </span>
                                        <?php if (!empty($dv['thoi_gian_xac_nhan'])): ?>
                                            <br><small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($dv['thoi_gian_xac_nhan'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="index.php?act=nhaCungCap/chiTietDichVu&id=<?php echo $dv['id']; ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/bao_gia_manual_modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    const templateSelect = document.getElementById('catalogTemplateSelect');
    if (templateSelect) {
        templateSelect.addEventListener('change', function () {
            const option = this.selectedOptions[0];
            if (!option || !option.dataset.name) return;

            document.getElementById('formBaoGiaTenDichVu').value = option.dataset.name || '';
            document.getElementById('formBaoGiaLoaiDichVu').value = option.dataset.loai || 'Khac';
            document.getElementById('formBaoGiaGiaTien').value = option.dataset.gia || '';
            document.getElementById('formBaoGiaDonVi').value = option.dataset.donvi || '';
            document.getElementById('formBaoGiaMoTa').value = option.dataset.mota || '';
        });
    }
</script>
</body>
</html>
