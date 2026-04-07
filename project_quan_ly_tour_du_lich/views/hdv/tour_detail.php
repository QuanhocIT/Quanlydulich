<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết tour - HDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$status = $tour['trang_thai'] ?? '';
$statusLabel = match ($status) {
    'SapKhoiHanh' => 'Sắp khởi hành',
    'DangChay' => 'Đang chạy',
    'HoanThanh' => 'Hoàn thành',
    'DaHuy' => 'Đã hủy',
    default => ($status ?: 'Chưa cập nhật'),
};
$statusClass = match ($status) {
    'SapKhoiHanh' => 'primary',
    'DangChay' => 'warning',
    'HoanThanh' => 'neutral',
    'DaHuy' => 'danger',
    default => 'neutral',
};
?>
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-map"></i> <?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tour chưa xác định'); ?></h3>
                <p class="mb-0 opacity-75">Thông tin điều hành, lịch trình và các lối tắt thao tác nhanh cho hướng dẫn viên.</p>
            </div>
            <a href="index.php?act=hdv/tours" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-calendar3"></i></div>
                <div>
                    <div class="hdv-kpi-label">Khởi hành</div>
                    <div class="hdv-kpi-value" style="font-size:1.25rem"><?php echo !empty($tour['ngay_khoi_hanh']) ? date('d/m/Y H:i', strtotime($tour['ngay_khoi_hanh'])) : 'N/A'; ?></div>
                    <div class="hdv-kpi-meta">Mốc xuất phát dự kiến của đoàn</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="hdv-kpi-label">Kết thúc</div>
                    <div class="hdv-kpi-value" style="font-size:1.25rem"><?php echo !empty($tour['ngay_ket_thuc']) ? date('d/m/Y H:i', strtotime($tour['ngay_ket_thuc'])) : 'N/A'; ?></div>
                    <div class="hdv-kpi-meta">Thời gian dự kiến hoàn tất hành trình</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-flag"></i></div>
                <div>
                    <div class="hdv-kpi-label">Trạng thái tour</div>
                    <div class="mt-2">
                        <span class="hdv-soft-badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                    </div>
                    <div class="hdv-kpi-meta">Dùng để quyết định bước xử lý tiếp theo</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-body hdv-hero">
                <span class="hdv-hero-badge"><i class="bi bi-compass"></i> Tour briefing</span>
                <h1 class="hdv-hero-title">Không gian tóm tắt này giúp bạn nắm toàn bộ tour trước khi bước vào vận hành thực tế.</h1>
                <p class="hdv-hero-text">
                    Kiểm tra nhanh thời gian, điểm tập trung, ghi chú và lịch trình chi tiết để đảm bảo mọi điểm chạm với khách đều mạch lạc và chủ động.
                </p>
                <div class="hdv-pill-list">
                    <span class="hdv-pill"><i class="bi bi-pin-map"></i> Điểm tập trung: <?php echo htmlspecialchars($tour['diem_tap_trung'] ?? 'Chưa xác định'); ?></span>
                </div>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Hành động nhanh</h4>
                    <p class="hdv-card-subtitle">Đi tới đúng nghiệp vụ chỉ với một lần nhấp</p>
                </div>
            </div>
            <div class="hdv-card-body">
                <div class="hdv-list">
                    <div class="hdv-list-item">
                        <div class="hdv-list-head">
                            <div>
                                <h5 class="hdv-list-title">Danh sách khách</h5>
                                <p class="mb-0 hdv-muted">Theo dõi thông tin khách trong đoàn.</p>
                            </div>
                            <a href="index.php?act=hdv/khach&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary">Mở</a>
                        </div>
                    </div>
                    <div class="hdv-list-item">
                        <div class="hdv-list-head">
                            <div>
                                <h5 class="hdv-list-title">Check-in và điểm danh</h5>
                                <p class="mb-0 hdv-muted">Xử lý xác nhận khách và trạng thái lên đoàn.</p>
                            </div>
                            <a href="index.php?act=hdv/checkin&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary">Mở</a>
                        </div>
                    </div>
                    <div class="hdv-list-item">
                        <div class="hdv-list-head">
                            <div>
                                <h5 class="hdv-list-title">Nhật ký tour</h5>
                                <p class="mb-0 hdv-muted">Ghi nhận diễn biến, sự cố và lưu ý vận hành.</p>
                            </div>
                            <a href="index.php?act=hdv/nhat_ky&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary">Mở</a>
                        </div>
                    </div>
                    <div class="hdv-list-item">
                        <div class="hdv-list-head">
                            <div>
                                <h5 class="hdv-list-title">Yêu cầu đặc biệt</h5>
                                <p class="mb-0 hdv-muted">Kiểm tra các lưu ý riêng của khách trong đoàn.</p>
                            </div>
                            <a href="index.php?act=hdv/yeu_cau_dac_biet&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary">Mở</a>
                        </div>
                    </div>
                    <?php if (($tour['trang_thai'] ?? '') === 'HoanThanh'): ?>
                        <div class="hdv-list-item">
                            <div class="hdv-list-head">
                                <div>
                                    <h5 class="hdv-list-title">Đánh giá và phản hồi</h5>
                                    <p class="mb-0 hdv-muted">Gửi phản hồi tổng kết sau tour hoàn thành.</p>
                                </div>
                                <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-primary">Mở</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-section-spacer"></div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Thông tin tour</h4>
                    <p class="hdv-card-subtitle">Các mốc chính để HDV rà lại trước khi dẫn đoàn</p>
                </div>
            </div>
            <div class="hdv-card-body">
                <div class="hdv-list">
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Tên tour</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tour chưa xác định'); ?></p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Điểm tập trung</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($tour['diem_tap_trung'] ?? 'Chưa xác định'); ?></p>
                    </div>
                    <?php if (!empty($tour['ghi_chu'])): ?>
                        <div class="hdv-list-item">
                            <h5 class="hdv-list-title">Ghi chú vận hành</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($tour['ghi_chu'])); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($tour['mo_ta'])): ?>
                        <div class="hdv-list-item">
                            <h5 class="hdv-list-title">Mô tả tour</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($tour['mo_ta'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Lịch trình chi tiết</h4>
                    <p class="hdv-card-subtitle">Nhịp tour được sắp theo thứ tự từng ngày</p>
                </div>
                <span class="hdv-soft-badge primary"><i class="bi bi-list-ol"></i> <?php echo count($lichTrinhList ?? []); ?> chặng</span>
            </div>
            <div class="hdv-card-body">
                <?php if (!empty($lichTrinhList)): ?>
                    <div class="hdv-timeline">
                        <?php foreach ($lichTrinhList as $index => $lichTrinh): ?>
                            <div class="hdv-timeline-item">
                                <div class="hdv-timeline-dot"><?php echo $index + 1; ?></div>
                                <div class="hdv-timeline-card">
                                    <div class="hdv-timeline-day">Ngày <?php echo htmlspecialchars($lichTrinh['ngay_thu'] ?? ($index + 1)); ?></div>
                                    <h5 class="hdv-list-title mb-2"><?php echo htmlspecialchars($lichTrinh['dia_diem'] ?? 'Điểm đến'); ?></h5>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($lichTrinh['hoat_dong'] ?? '')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="hdv-empty">
                        <i class="bi bi-map"></i>
                        Chưa có lịch trình chi tiết cho tour này.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
