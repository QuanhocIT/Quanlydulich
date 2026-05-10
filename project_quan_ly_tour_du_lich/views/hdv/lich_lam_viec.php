<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch làm việc - HDV</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-calendar-week"></i> Lịch làm việc</h3>
                <p class="mb-0 opacity-75">Theo d?i l?ch kh?i h?nh, ph?n b? nhi?m v? v? y?u c?u d?c bi?t c?a kh?ch.</p>
            </div>
            <a href="index.php?act=hdv/dashboard" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Trang chủ
            </a>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="text-muted mb-2">L?ch kh?i h?nh du?c ph?n c?ng</div>
                    <h3 class="mb-0"><?php echo count($lichKhoiHanhList ?? []); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="text-muted mb-2">Ph?n b? nh?n s?</div>
                    <h3 class="mb-0"><?php echo count($phanBoNhanSuList ?? []); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="text-muted mb-2">Tour có yêu cầu đặc biệt</div>
                    <h3 class="mb-0"><?php echo count(array_filter($yeuCauDacBietTheoLich ?? [])); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($lichKhoiHanhList) && !empty($yeuCauDacBietTheoLich)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-stars"></i> Yêu cầu đặc biệt theo lịch khởi hành</h5>
            </div>
            <div class="card-body">
                <?php foreach ($lichKhoiHanhList as $lich): ?>
                    <?php
                        $lichId = (int)($lich['id'] ?? 0);
                        $danhSachKhach = $yeuCauDacBietTheoLich[$lichId] ?? [];
                    ?>
                    <?php if (!empty($danhSachKhach)): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($lich['ten_tour'] ?? 'Tour'); ?></h5>
                                        <div class="text-muted small">
                                            <?php echo !empty($lich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])) : 'N/A'; ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-warning-subtle text-dark"><?php echo count($danhSachKhach); ?> khách có ghi chú</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Khách hàng</th>
                                                <th>Liên hệ</th>
                                                <th>Yêu cầu đặc biệt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($danhSachKhach as $khach): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($khach['ho_ten'] ?? 'Khách'); ?></strong><br>
                                                        <small class="text-muted">Booking #<?php echo $khach['booking_id']; ?> • <?php echo (int)($khach['so_nguoi'] ?? 1); ?> khách</small>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($khach['email'] ?? ''); ?><br>
                                                        <?php echo htmlspecialchars($khach['so_dien_thoai'] ?? ''); ?>
                                                    </td>
                                                    <td><?php echo nl2br(htmlspecialchars($khach['yeu_cau_dac_biet'] ?? 'Chưa có yêu cầu đặc biệt')); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-briefcase"></i> L?ch kh?i h?nh du?c ph?n c?ng</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($lichKhoiHanhList)): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Khởi hành</th>
                                <th>Kết thúc</th>
                                <th>Điểm tập trung</th>
                                <th>Số chỗ</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lichKhoiHanhList as $lich): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($lich['ten_tour'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo !empty($lich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])) : 'N/A'; ?><br><small class="text-muted"><?php echo $lich['gio_xuat_phat'] ?? 'N/A'; ?></small></td>
                                <td><?php echo !empty($lich['ngay_ket_thuc']) ? date('d/m/Y', strtotime($lich['ngay_ket_thuc'])) : 'N/A'; ?><br><small class="text-muted"><?php echo $lich['gio_ket_thuc'] ?? 'N/A'; ?></small></td>
                                <td><?php echo htmlspecialchars($lich['diem_tap_trung'] ?? ''); ?></td>
                                <td><?php echo $lich['so_cho'] ?? 50; ?></td>
                                <td>
                                    <?php
                                    $statusLabels = [
                                        'SapKhoiHanh' => 'Sắp khởi hành',
                                        'DangChay' => 'Đang chạy',
                                        'HoanThanh' => 'Hoàn thành'
                                    ];
                                    $trangThai = $lich['trang_thai'] ?? null;
                                    ?>
                                    <span class="badge bg-primary-subtle text-dark"><?php echo $trangThai ? ($statusLabels[$trangThai] ?? $trangThai) : 'N/A'; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">Chua c? l?ch kh?i h?nh n?o du?c ph?n c?ng.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people"></i> Ph?n b? nh?n s?</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($phanBoNhanSuList)): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Thời gian</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phanBoNhanSuList as $pb): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($pb['ten_tour'] ?? 'N/A'); ?></strong></td>
                                <td><?php echo !empty($pb['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($pb['ngay_khoi_hanh'])) : 'N/A'; ?> → <?php echo !empty($pb['ngay_ket_thuc']) ? date('d/m/Y', strtotime($pb['ngay_ket_thuc'])) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($pb['vai_tro'] ?? ''); ?></td>
                                <td>
                                    <?php
                                    $statusLabels = [
                                        'ChoXacNhan' => 'Chờ xác nhận',
                                        'DaXacNhan' => '?? x?c nh?n',
                                        'TuChoi' => 'Từ chối',
                                        'Huy' => 'Hủy'
                                    ];
                                    $trangThaiPb = $pb['trang_thai'] ?? null;
                                    ?>
                                    <span class="badge bg-info-subtle text-dark"><?php echo $trangThaiPb ? ($statusLabels[$trangThaiPb] ?? $trangThaiPb) : 'N/A'; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($pb['ghi_chu'] ?? ''); ?></td>
                                <td>
                                    <form method="POST" action="index.php?act=lichKhoiHanh/updateTrangThaiNhanSu" class="d-flex gap-2">
                                        <input type="hidden" name="id" value="<?php echo $pb['id']; ?>">
                                        <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $pb['lich_khoi_hanh_id']; ?>">
                                        <select name="trang_thai" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="ChoXacNhan" <?php echo $pb['trang_thai'] == 'ChoXacNhan' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                            <option value="DaXacNhan" <?php echo $pb['trang_thai'] == 'DaXacNhan' ? 'selected' : ''; ?>>?? x?c nh?n</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-muted">Chua c? ph?n b? nh?n s? n?o.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($lichKhoiHanhList)): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-map"></i> Chi tiết tour & nhiệm vụ</h5>
            </div>
            <div class="card-body">
                <?php foreach ($lichKhoiHanhList as $lich): ?>
                    <?php
                        $tourId = $lich['tour_id'] ?? null;
                        $lichTrinh = ($tourId && isset($lichTrinhTheoTour[$tourId])) ? $lichTrinhTheoTour[$tourId] : [];
                        $nhiemVu = isset($nhiemVuTheoLich[$lich['id']]) ? $nhiemVuTheoLich[$lich['id']] : null;
                    ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($lich['ten_tour'] ?? 'Tour'); ?></h5>
                            <p><strong>Thời gian:</strong> <?php echo !empty($lich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])) : 'N/A'; ?> → <?php echo !empty($lich['ngay_ket_thuc']) ? date('d/m/Y', strtotime($lich['ngay_ket_thuc'])) : 'N/A'; ?></p>
                            <p><strong>Điểm tập trung:</strong> <?php echo htmlspecialchars($lich['diem_tap_trung'] ?? 'Chưa cập nhật'); ?></p>
                            <p>
                                <strong>Nhiệm vụ của tôi:</strong>
                                <?php
                                    if ($nhiemVu) {
                                        echo htmlspecialchars($nhiemVu['vai_tro'] ?? 'HDV');
                                        if (!empty($nhiemVu['ghi_chu'])) {
                                            echo ' - ' . htmlspecialchars($nhiemVu['ghi_chu']);
                                        }
                                    } else {
                                        echo 'HDV chính phụ trách xuyên suốt tour';
                                    }
                                ?>
                            </p>
                            <div>
                                <strong>Lịch trình từng ngày:</strong>
                                <?php if (!empty($lichTrinh)): ?>
                                    <ol class="mt-2">
                                        <?php foreach ($lichTrinh as $ngay): ?>
                                            <li class="mb-2">
                                                <strong>Ngày <?php echo (int)($ngay['ngay_thu'] ?? 0); ?>:</strong>
                                                <?php if (!empty($ngay['dia_diem'])): ?>
                                                    <em><?php echo htmlspecialchars($ngay['dia_diem']); ?></em><br>
                                                <?php endif; ?>
                                                <?php echo nl2br(htmlspecialchars($ngay['hoat_dong'] ?? '')); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ol>
                                <?php else: ?>
                                    <p class="text-muted mb-0 mt-2">Chưa có lịch trình chi tiết cho tour này.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
