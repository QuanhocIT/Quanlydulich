<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách khách - HDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$guestCount = count($danhSachKhach ?? []);
$checkedInCount = 0;
foreach (($danhSachKhach ?? []) as $khach) {
    if (($khach['trang_thai'] ?? 'ChuaCheckIn') === 'DaCheckIn') {
        $checkedInCount++;
    }
}
?>
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-people"></i> Danh sách khách trong đoàn</h3>
                <p class="mb-0 opacity-75">Xem nhanh hồ sơ khách, thông tin liên hệ và tình trạng check-in theo từng lịch khởi hành được giao.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="index.php?act=hdv/lichLamViec" class="btn btn-light"><i class="bi bi-arrow-left"></i> Lịch làm việc</a>
                <a href="index.php?act=hdv/checkInKhach" class="btn btn-primary"><i class="bi bi-check2-square"></i> Điểm danh khách</a>
            </div>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <?php if (!empty($lichKhoiHanhList)): ?>
        <div class="hdv-grid cols-3 mb-4">
            <div class="hdv-card">
                <div class="hdv-kpi">
                    <div class="hdv-kpi-icon"><i class="bi bi-person-lines-fill"></i></div>
                    <div>
                        <div class="hdv-kpi-label">Tổng số khách</div>
                        <div class="hdv-kpi-value"><?php echo $guestCount; ?></div>
                        <div class="hdv-kpi-meta">Tính trên lịch khởi hành đang chọn</div>
                    </div>
                </div>
            </div>
            <div class="hdv-card">
                <div class="hdv-kpi">
                    <div class="hdv-kpi-icon"><i class="bi bi-person-check"></i></div>
                    <div>
                        <div class="hdv-kpi-label">Đã check-in</div>
                        <div class="hdv-kpi-value"><?php echo $checkedInCount; ?></div>
                        <div class="hdv-kpi-meta">Số khách đã xác nhận tham gia đoàn</div>
                    </div>
                </div>
            </div>
            <div class="hdv-card">
                <div class="hdv-kpi">
                    <div class="hdv-kpi-icon"><i class="bi bi-bus-front"></i></div>
                    <div>
                        <div class="hdv-kpi-label">Lịch khởi hành</div>
                        <div class="hdv-kpi-value"><?php echo count($lichKhoiHanhList); ?></div>
                        <div class="hdv-kpi-meta">Các lịch hiện thuộc phạm vi phụ trách</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hdv-card mb-4">
            <div class="hdv-card-body">
                <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="act" value="hdv/danhSachKhach">
                    <div class="col-lg-8">
                        <label for="lich_id" class="form-label">Chọn lịch khởi hành</label>
                        <select name="lich_id" id="lich_id" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($lichKhoiHanhList as $lich): ?>
                                <option value="<?php echo (int)$lich['id']; ?>" <?php echo ($selectedLich && (int)$selectedLich['id'] === (int)$lich['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lich['ten_tour'] ?? 'Tour'); ?>
                                    (<?php echo !empty($lich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])) : 'N/A'; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="hdv-pill"><i class="bi bi-filter-circle"></i> Đổi lịch để tải danh sách tương ứng</div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($selectedLich): ?>
            <div class="hdv-grid cols-2 mb-4">
                <div class="hdv-card">
                    <div class="hdv-card-body hdv-hero">
                        <span class="hdv-hero-badge"><i class="bi bi-briefcase"></i> Manifest</span>
                        <h1 class="hdv-hero-title"><?php echo htmlspecialchars($selectedLich['ten_tour'] ?? 'Tour'); ?></h1>
                        <p class="hdv-hero-text">
                            Kiểm tra nhanh danh sách đoàn, chuẩn bị thông tin đón khách và dùng bảng bên dưới để rà liên hệ, giấy tờ cũng như trạng thái check-in.
                        </p>
                        <div class="hdv-pill-list">
                            <span class="hdv-pill"><i class="bi bi-calendar3"></i> <?php echo !empty($selectedLich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($selectedLich['ngay_khoi_hanh'])) : 'N/A'; ?> → <?php echo !empty($selectedLich['ngay_ket_thuc']) ? date('d/m/Y', strtotime($selectedLich['ngay_ket_thuc'])) : 'N/A'; ?></span>
                            <span class="hdv-pill"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($selectedLich['diem_tap_trung'] ?? 'Chưa cập nhật'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="hdv-card">
                    <div class="hdv-card-header">
                        <div>
                            <h4 class="hdv-card-title">Tình trạng đoàn</h4>
                            <p class="hdv-card-subtitle">Một vài chỉ số nhanh để chuẩn bị trước giờ tập trung</p>
                        </div>
                    </div>
                    <div class="hdv-card-body">
                        <div class="hdv-list">
                            <div class="hdv-list-item">
                                <h5 class="hdv-list-title">Tổng số khách</h5>
                                <p class="mb-0"><?php echo $guestCount; ?> người</p>
                            </div>
                            <div class="hdv-list-item">
                                <h5 class="hdv-list-title">Khách chưa check-in</h5>
                                <p class="mb-0"><?php echo max($guestCount - $checkedInCount, 0); ?> người</p>
                            </div>
                            <div class="hdv-list-item">
                                <h5 class="hdv-list-title">Điểm tập trung</h5>
                                <p class="mb-0"><?php echo htmlspecialchars($selectedLich['diem_tap_trung'] ?? 'Chưa cập nhật'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hdv-card">
                <div class="hdv-card-header">
                    <div>
                        <h4 class="hdv-card-title">Danh sách hành khách</h4>
                        <p class="hdv-card-subtitle">Bảng tổng hợp liên hệ, giấy tờ và tình trạng tham gia đoàn</p>
                    </div>
                    <span class="hdv-soft-badge primary"><i class="bi bi-people-fill"></i> <?php echo $guestCount; ?> khách</span>
                </div>
                <div class="hdv-card-body">
                    <?php if (!empty($danhSachKhach)): ?>
                        <div class="table-responsive">
                            <table class="table hdv-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Khách</th>
                                        <th>CMND / Passport</th>
                                        <th>Ngày sinh</th>
                                        <th>Giới tính</th>
                                        <th>Quốc tịch</th>
                                        <th>Liên hệ</th>
                                        <th>Địa chỉ</th>
                                        <th>Trạng thái</th>
                                        <th>Booking</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($danhSachKhach as $khach): ?>
                                        <?php
                                        $gioiTinhLabels = ['Nam' => 'Nam', 'Nu' => 'Nữ', 'Khac' => 'Khác'];
                                        $trangThai = $khach['trang_thai'] ?? 'ChuaCheckIn';
                                        $trangThaiLabels = [
                                            'ChuaCheckIn' => 'Chưa check-in',
                                            'DaCheckIn' => 'Đã check-in',
                                            'DaCheckOut' => 'Đã check-out',
                                        ];
                                        $badgeClass = $trangThai === 'DaCheckIn' ? 'primary' : ($trangThai === 'DaCheckOut' ? 'neutral' : 'warning');
                                        ?>
                                        <tr>
                                            <td><?php echo $stt++; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($khach['ho_ten'] ?? 'Khách'); ?></strong>
                                            </td>
                                            <td>
                                                <?php if (!empty($khach['so_cmnd'])): ?>
                                                    <div>CMND: <?php echo htmlspecialchars($khach['so_cmnd']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($khach['so_passport'])): ?>
                                                    <div>Passport: <?php echo htmlspecialchars($khach['so_passport']); ?></div>
                                                <?php endif; ?>
                                                <?php if (empty($khach['so_cmnd']) && empty($khach['so_passport'])): ?>
                                                    <span class="hdv-muted">Chưa cập nhật</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($khach['ngay_sinh']) ? date('d/m/Y', strtotime($khach['ngay_sinh'])) : 'N/A'; ?></td>
                                            <td><?php echo $gioiTinhLabels[$khach['gioi_tinh']] ?? ($khach['gioi_tinh'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($khach['quoc_tich'] ?? 'Việt Nam'); ?></td>
                                            <td>
                                                <?php if (!empty($khach['email'])): ?>
                                                    <div><?php echo htmlspecialchars($khach['email']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($khach['so_dien_thoai'])): ?>
                                                    <div><?php echo htmlspecialchars($khach['so_dien_thoai']); ?></div>
                                                <?php endif; ?>
                                                <?php if (empty($khach['email']) && empty($khach['so_dien_thoai'])): ?>
                                                    <span class="hdv-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($khach['dia_chi']) ? htmlspecialchars($khach['dia_chi']) : 'N/A'; ?></td>
                                            <td><span class="hdv-soft-badge <?php echo $badgeClass; ?>"><?php echo $trangThaiLabels[$trangThai] ?? $trangThai; ?></span></td>
                                            <td><?php echo !empty($khach['booking_id']) ? '#' . (int)$khach['booking_id'] : 'N/A'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="hdv-empty">
                            <i class="bi bi-person-x"></i>
                            Chưa có khách nào trong danh sách. Vui lòng thêm khách vào lịch khởi hành này.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="hdv-card">
                <div class="hdv-card-body">
                    <div class="hdv-empty">
                        <i class="bi bi-exclamation-circle"></i>
                        Không tìm thấy lịch khởi hành phù hợp.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="hdv-card">
            <div class="hdv-card-body">
                <div class="hdv-empty">
                    <i class="bi bi-map"></i>
                    Bạn chưa được phân công tour nào.
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
