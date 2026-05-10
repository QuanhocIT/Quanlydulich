<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dịch vụ - Nhà cung cấp</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/supplier.css">
</head>
<body class="supplier-body">
<?php
$loaiDichVuMap = [
    'Xe' => 'Xe',
    'KhachSan' => 'Khách sạn',
    'VeMayBay' => 'Vé máy bay',
    'NhaHang' => 'Nhà hàng',
    'DiemThamQuan' => 'Điểm tham quan',
    'Visa' => 'Visa',
    'BaoHiem' => 'Bảo hiểm',
    'Ve' => 'Vé',
    'Khac' => 'Khác'
];
$catalog = $catalogServices ?? [];
$assigned = $dichVu ?? [];
$catalogStatusMap = [
    'HoatDong' => ['text' => 'Hoạt động', 'class' => 'success'],
    'TamDung' => ['text' => 'Tạm dừng', 'class' => 'warning'],
    'NgungHopTac' => ['text' => 'Ngừng hợp tác', 'class' => 'secondary'],
];
$statusMap = [
    'ChoXacNhan' => ['text' => 'Chờ xác nhận', 'class' => 'warning'],
    'DaXacNhan' => ['text' => 'Đã xác nhận', 'class' => 'success'],
    'TuChoi' => ['text' => 'Từ chối', 'class' => 'danger'],
    'Huy' => ['text' => 'Hủy', 'class' => 'secondary'],
    'HoanTat' => ['text' => 'Hoàn tất', 'class' => 'info']
];
?>
    <div class="container-fluid supplier-shell">
        <section class="supplier-page-header">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="supplier-eyebrow"><i class="bi bi-briefcase"></i> Dịch vụ</span>
                    <h1 class="supplier-page-title">Quản lý dịch vụ</h1>
                    <p class="supplier-page-subtitle">Giữ danh mục dịch vụ của bạn gọn gàng, rõ ràng và đồng nhất với danh sách các hạng mục đang phục vụ tour.</p>
                </div>
                <div class="col-lg-4">
                    <div class="supplier-header-actions">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddCatalog">
                            <i class="bi bi-plus-circle"></i> Thêm dịch vụ
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
            $currentTab = 'dichVu';
            include __DIR__ . '/partials/main_nav.php';
        ?>

        <div class="card supplier-section-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="supplier-card-title"><i class="bi bi-collection"></i> Danh mục dịch vụ của bạn</h5>
                    <div class="supplier-card-subtitle">Sử dụng danh mục này để gửi báo giá nhanh và giữ dữ liệu luôn nhất quán.</div>
                </div>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAddCatalog">
                    <i class="bi bi-plus-lg"></i> Nhập dịch vụ mới
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($catalog)): ?>
                    <div class="supplier-empty-state">
                        <div class="supplier-empty-icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Bạn chưa thêm dịch vụ nào. Hãy tạo mục đầu tiên để bắt đầu.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table supplier-table align-middle">
                            <thead>
                                <tr>
                                    <th>Tên dịch vụ</th>
                                    <th>Loại</th>
                                    <th>Giá tham khảo</th>
                                    <th>Công suất</th>
                                    <th>Thời gian xử lý</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($catalog as $service): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($service['ten_dich_vu']); ?></strong>
                                        <?php if (!empty($service['mo_ta'])): ?>
                                            <div class="text-muted small"><?php echo nl2br(htmlspecialchars($service['mo_ta'])); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="supplier-badge-soft info"><?php echo $loaiDichVuMap[$service['loai_dich_vu']] ?? $service['loai_dich_vu']; ?></span></td>
                                    <td>
                                        <?php if ($service['gia_tham_khao']): ?>
                                            <strong class="text-primary"><?php echo number_format($service['gia_tham_khao'], 0, ',', '.'); ?>đ</strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars($service['don_vi_tinh'] ?? ''); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">Chưa nhập</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $service['cong_suat_toi_da'] ? $service['cong_suat_toi_da'] : '-'; ?></td>
                                    <td><?php echo $service['thoi_gian_xu_ly'] ? htmlspecialchars($service['thoi_gian_xu_ly']) : '-'; ?></td>
                                    <td>
                                        <?php $st = $catalogStatusMap[$service['trang_thai']] ?? ['text' => $service['trang_thai'], 'class' => 'secondary']; ?>
                                        <span class="supplier-badge-soft <?php echo $st['class']; ?>"><?php echo $st['text']; ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditCatalog<?php echo $service['id']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="index.php?act=nhaCungCap/deleteDichVu" onsubmit="return confirm('Bạn chắc chắn muốn xóa dịch vụ này?');">
                                                <?php echo csrfField('supplier_form'); ?>
                                                <input type="hidden" name="dich_vu_id" value="<?php echo $service['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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

        <div class="card supplier-section-card">
            <div class="card-header">
                <h5 class="supplier-card-title"><i class="bi bi-list-ul"></i> Dịch vụ đang phục vụ tour</h5>
                <div class="supplier-card-subtitle">Phân tách rõ danh mục nội bộ và các dịch vụ đang được giao thực tế.</div>
            </div>
            <div class="card-body">
                <?php if (empty($assigned)): ?>
                    <div class="supplier-empty-state">
                        <div class="supplier-empty-icon"><i class="bi bi-inbox"></i></div>
                        <p class="mb-0">Hiện chưa có dịch vụ nào được phân bổ cho tour.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table supplier-table">
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
                                <?php foreach ($assigned as $dv): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dv['ten_tour'] ?? 'N/A'); ?></strong></td>
                                    <td><span class="supplier-badge-soft info"><?php echo $loaiDichVuMap[$dv['loai_dich_vu']] ?? $dv['loai_dich_vu']; ?></span></td>
                                    <td><?php echo htmlspecialchars($dv['ten_dich_vu']); ?></td>
                                    <td><?php echo $dv['so_luong']; ?> <?php if ($dv['don_vi']): ?><small class="text-muted"><?php echo $dv['don_vi']; ?></small><?php endif; ?></td>
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
                                        <span class="supplier-badge-soft <?php echo $status['class']; ?>"><?php echo $status['text']; ?></span>
                                    </td>
                                    <td>
                                        <a href="index.php?act=nhaCungCap/baoGia&trang_thai=<?php echo $dv['trang_thai']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Theo dõi
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

    <div class="modal fade supplier-modal" id="modalAddCatalog" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm dịch vụ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?act=nhaCungCap/storeDichVu">
                    <?php echo csrfField('supplier_form'); ?>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên dịch vụ *</label>
                                <input type="text" name="ten_dich_vu" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loại dịch vụ</label>
                                <select name="loai_dich_vu" class="form-select">
                                    <?php foreach ($loaiDichVuMap as $key => $label): ?>
                                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Giá tham khảo</label>
                                <input type="number" name="gia_tham_khao" class="form-control" min="0" step="1000" placeholder="VD: 1500000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Đơn vị tính</label>
                                <input type="text" name="don_vi_tinh" class="form-control" placeholder="phòng/đêm, suất, chuyến...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Công suất tối đa</label>
                                <input type="number" name="cong_suat_toi_da" class="form-control" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thời gian xử lý</label>
                                <input type="text" name="thoi_gian_xu_ly" class="form-control" placeholder="VD: 2 giờ">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Mô tả</label>
                                <textarea name="mo_ta" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tài liệu / đường dẫn tham khảo</label>
                                <textarea name="tai_lieu_dinh_kem" class="form-control" rows="2" placeholder="URL hoặc ghi chú thêm"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" class="form-select">
                                    <option value="HoatDong">Hoạt động</option>
                                    <option value="TamDung">Tạm dừng</option>
                                    <option value="NgungHopTac">Ngừng hợp tác</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (!empty($catalog)): ?>
        <?php foreach ($catalog as $service): ?>
        <div class="modal fade supplier-modal" id="modalEditCatalog<?php echo $service['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cập nhật dịch vụ: <?php echo htmlspecialchars($service['ten_dich_vu']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="index.php?act=nhaCungCap/updateDichVu">
                        <?php echo csrfField('supplier_form'); ?>
                        <input type="hidden" name="dich_vu_id" value="<?php echo $service['id']; ?>">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tên dịch vụ *</label>
                                    <input type="text" name="ten_dich_vu" class="form-control" value="<?php echo htmlspecialchars($service['ten_dich_vu']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Loại dịch vụ</label>
                                    <select name="loai_dich_vu" class="form-select">
                                        <?php foreach ($loaiDichVuMap as $key => $label): ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($service['loai_dich_vu'] === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Giá tham khảo</label>
                                    <input type="number" name="gia_tham_khao" class="form-control" min="0" step="1000" value="<?php echo $service['gia_tham_khao']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Đơn vị tính</label>
                                    <input type="text" name="don_vi_tinh" class="form-control" value="<?php echo htmlspecialchars($service['don_vi_tinh'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Công suất tối đa</label>
                                    <input type="number" name="cong_suat_toi_da" class="form-control" min="0" value="<?php echo $service['cong_suat_toi_da']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Thời gian xử lý</label>
                                    <input type="text" name="thoi_gian_xu_ly" class="form-control" value="<?php echo htmlspecialchars($service['thoi_gian_xu_ly'] ?? ''); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="mo_ta" class="form-control" rows="3"><?php echo htmlspecialchars($service['mo_ta'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Tài liệu / đường dẫn tham khảo</label>
                                    <textarea name="tai_lieu_dinh_kem" class="form-control" rows="2"><?php echo htmlspecialchars($service['tai_lieu_dinh_kem'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="trang_thai" class="form-select">
                                        <option value="HoatDong" <?php echo ($service['trang_thai'] === 'HoatDong') ? 'selected' : ''; ?>>Hoạt động</option>
                                        <option value="TamDung" <?php echo ($service['trang_thai'] === 'TamDung') ? 'selected' : ''; ?>>Tạm dừng</option>
                                        <option value="NgungHopTac" <?php echo ($service['trang_thai'] === 'NgungHopTac') ? 'selected' : ''; ?>>Ngừng hợp tác</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
