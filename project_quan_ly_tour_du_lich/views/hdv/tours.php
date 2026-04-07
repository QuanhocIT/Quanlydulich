<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch trình tour - HDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$currentFilter = $_GET['status'] ?? 'all';
$statusText = [
    'SapKhoiHanh' => 'Sắp khởi hành',
    'DangChay' => 'Đang chạy',
    'HoanThanh' => 'Hoàn thành',
    'DaHuy' => 'Đã hủy',
];
$filterLabel = [
    'all' => 'Tất cả',
    'SapKhoiHanh' => 'Sắp khởi hành',
    'DangChay' => 'Đang chạy',
    'HoanThanh' => 'Hoàn thành',
];
$tourStats = [
    'all' => count($tours ?? []),
    'SapKhoiHanh' => 0,
    'DangChay' => 0,
    'HoanThanh' => 0,
];
foreach (($tours ?? []) as $tour) {
    $status = $tour['trang_thai'] ?? '';
    if (isset($tourStats[$status])) {
        $tourStats[$status]++;
    }
}
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-map"></i> Lịch trình tour</h3>
                <p class="mb-0 opacity-75">Quản lý các tour bạn đang phụ trách, xác nhận phân bổ mới và đi nhanh tới từng nghiệp vụ vận hành.</p>
            </div>
            <a href="index.php?act=hdv/dashboard" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Trang chủ
            </a>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-list-task"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tổng tour hiển thị</div>
                    <div class="hdv-kpi-value"><?php echo (int)$tourStats['all']; ?></div>
                    <div class="hdv-kpi-meta">Theo bộ lọc hiện tại: <?php echo htmlspecialchars($filterLabel[$currentFilter] ?? 'Tất cả'); ?></div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="hdv-kpi-label">Sắp khởi hành</div>
                    <div class="hdv-kpi-value"><?php echo (int)$tourStats['SapKhoiHanh']; ?></div>
                    <div class="hdv-kpi-meta">Những tour cần chuẩn bị trước ngày đi</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-bell"></i></div>
                <div>
                    <div class="hdv-kpi-label">Chờ xác nhận</div>
                    <div class="hdv-kpi-value"><?php echo count($phanBoChoXacNhan ?? []); ?></div>
                    <div class="hdv-kpi-meta">Phân bổ mới đang chờ bạn phản hồi</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-card mb-4">
        <div class="hdv-card-header">
            <div>
                <h4 class="hdv-card-title">Bộ lọc trạng thái</h4>
                <p class="hdv-card-subtitle">Thu hẹp nhanh danh sách tour theo giai đoạn vận hành</p>
            </div>
        </div>
        <div class="hdv-card-body">
            <div class="filter-tabs mb-0">
                <ul class="nav nav-pills gap-2">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentFilter === 'all' ? 'active' : ''; ?>" href="index.php?act=hdv/tours&status=all">
                            <i class="bi bi-list-ul"></i> Tất cả
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentFilter === 'SapKhoiHanh' ? 'active' : ''; ?>" href="index.php?act=hdv/tours&status=SapKhoiHanh">
                            <i class="bi bi-calendar-event"></i> Sắp khởi hành
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentFilter === 'DangChay' ? 'active' : ''; ?>" href="index.php?act=hdv/tours&status=DangChay">
                            <i class="bi bi-play-circle"></i> Đang chạy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentFilter === 'HoanThanh' ? 'active' : ''; ?>" href="index.php?act=hdv/tours&status=HoanThanh">
                            <i class="bi bi-check-circle"></i> Hoàn thành
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?php if (!empty($phanBoChoXacNhan)): ?>
        <div class="hdv-card mb-4">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Phân bổ nhân sự chờ xác nhận</h4>
                    <p class="hdv-card-subtitle">Kiểm tra vai trò, thời gian và xác nhận sớm để đội điều hành khóa lịch</p>
                </div>
                <span class="hdv-soft-badge warning"><i class="bi bi-bell-fill"></i> <?php echo count($phanBoChoXacNhan); ?> mục</span>
            </div>
            <div class="hdv-card-body">
                <div class="hdv-list">
                    <?php foreach ($phanBoChoXacNhan as $pb): ?>
                        <div class="hdv-list-item">
                            <div class="hdv-list-head">
                                <div>
                                    <h5 class="hdv-list-title"><?php echo htmlspecialchars($pb['ten_tour'] ?? 'Tour chưa xác định'); ?></h5>
                                    <div class="hdv-list-meta mt-2">
                                        <span><i class="bi bi-calendar3"></i> <?php echo !empty($pb['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($pb['ngay_khoi_hanh'])) : 'N/A'; ?><?php if (!empty($pb['ngay_ket_thuc'])): ?> → <?php echo date('d/m/Y', strtotime($pb['ngay_ket_thuc'])); ?><?php endif; ?></span>
                                        <span><i class="bi bi-person-workspace"></i> Vai trò: <?php echo htmlspecialchars($pb['phan_bo_vai_tro'] ?? 'HDV'); ?></span>
                                    </div>
                                </div>
                                <span class="hdv-soft-badge warning"><i class="bi bi-hourglass-split"></i> Chờ xác nhận</span>
                            </div>

                            <?php if (!empty($pb['ghi_chu'])): ?>
                                <p class="mb-0 hdv-muted"><?php echo htmlspecialchars($pb['ghi_chu']); ?></p>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap gap-2">
                                <form method="POST" action="index.php?act=hdv/xacNhanPhanBo" class="d-inline">
                                    <input type="hidden" name="phan_bo_id" value="<?php echo (int)$pb['id']; ?>">
                                    <input type="hidden" name="action" value="xac_nhan">
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc muốn xác nhận phân bổ này?');">
                                        <i class="bi bi-check-circle"></i> Xác nhận
                                    </button>
                                </form>
                                <form method="POST" action="index.php?act=hdv/xacNhanPhanBo" class="d-inline">
                                    <input type="hidden" name="phan_bo_id" value="<?php echo (int)$pb['id']; ?>">
                                    <input type="hidden" name="action" value="tu_choi">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn từ chối phân bổ này?');">
                                        <i class="bi bi-x-circle"></i> Từ chối
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="hdv-card">
        <div class="hdv-card-header">
            <div>
                <h4 class="hdv-card-title">Danh sách tour của bạn</h4>
                <p class="hdv-card-subtitle">Đi vào từng tour để xem chi tiết lịch trình, khách, check-in và nhật ký</p>
            </div>
        </div>
        <div class="hdv-card-body">
            <?php if (!empty($tours)): ?>
                <div class="row g-4">
                    <?php foreach ($tours as $tour): ?>
                        <?php
                        $status = $tour['trang_thai'] ?? '';
                        $badgeClass = match ($status) {
                            'SapKhoiHanh' => 'primary',
                            'DangChay' => 'warning',
                            'HoanThanh' => 'neutral',
                            'DaHuy' => 'danger',
                            default => 'neutral',
                        };
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="hdv-list-item h-100">
                                <div class="hdv-list-head">
                                    <div>
                                        <h5 class="hdv-list-title"><?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tour'); ?></h5>
                                        <div class="hdv-list-meta mt-2">
                                            <span><i class="bi bi-calendar3"></i> <?php echo !empty($tour['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($tour['ngay_khoi_hanh'])) : 'N/A'; ?></span>
                                            <span><i class="bi bi-calendar-check"></i> <?php echo !empty($tour['ngay_ket_thuc']) ? date('d/m/Y', strtotime($tour['ngay_ket_thuc'])) : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <span class="hdv-soft-badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($statusText[$status] ?? ($status ?: 'N/A')); ?>
                                    </span>
                                </div>

                                <div class="hdv-list">
                                    <?php if (!empty($tour['diem_tap_trung'])): ?>
                                        <div class="hdv-list-meta">
                                            <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($tour['diem_tap_trung']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($tour['so_nguoi'])): ?>
                                        <div class="hdv-list-meta">
                                            <span><i class="bi bi-people"></i> <?php echo (int)$tour['so_nguoi']; ?> khách</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-grid gap-2 mt-2">
                                    <a href="index.php?act=hdv/tour_detail&id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-eye"></i> Xem chi tiết
                                    </a>
                                    <?php if (in_array($status, ['DangChay', 'SapKhoiHanh'], true)): ?>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="index.php?act=hdv/khach&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-people"></i> Khách
                                            </a>
                                            <a href="index.php?act=hdv/checkin&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-check2-square"></i> Check-in
                                            </a>
                                            <a href="index.php?act=hdv/nhat_ky&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-journal-text"></i> Nhật ký
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="hdv-empty">
                    <i class="bi bi-info-circle"></i>
                    <?php if ($currentFilter === 'all'): ?>
                        Hiện tại bạn chưa có tour nào.
                    <?php else: ?>
                        Không có tour nào trong trạng thái này.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
