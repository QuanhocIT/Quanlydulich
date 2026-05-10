<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá và phản hồi - HDV</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$completedTours = $tours_list ?? [];
$selectedTour = $tour ?? null;
$completedCount = count($completedTours);
$recentCompletion = null;
if (!empty($completedTours[0]['ngay_khoi_hanh'])) {
    $recentCompletion = date('d/m/Y', strtotime($completedTours[0]['ngay_khoi_hanh']));
}
?>
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-stars"></i> Đánh giá và phản hồi</h3>
                <p class="mb-0 opacity-75">Tổng hợp các tour đã hoàn thành để bạn gửi nhận xét vận hành, chất lượng dịch vụ và trải nghiệm đoàn.</p>
            </div>
            <a href="index.php?act=hdv/dashboard" class="btn btn-light"><i class="bi bi-arrow-left"></i> Trang chủ</a>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tour đã hoàn thành</div>
                    <div class="hdv-kpi-value"><?php echo $completedCount; ?></div>
                    <div class="hdv-kpi-meta">Những chuyến đi đã đủ điều kiện đánh giá</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-calendar-event"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tour gần nhất</div>
                    <div class="hdv-kpi-value" style="font-size:1.35rem"><?php echo $recentCompletion ?: 'Chưa có'; ?></div>
                    <div class="hdv-kpi-meta">Mốc hoàn thành mới nhất trong danh sách</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-chat-heart"></i></div>
                <div>
                    <div class="hdv-kpi-label">Trạng thái khu vực</div>
                    <div class="hdv-kpi-value" style="font-size:1.35rem">Sẵn sàng</div>
                    <div class="hdv-kpi-meta">Ưu tiên gửi phản hồi sau khi tour kết thúc</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-body hdv-hero">
                <span class="hdv-hero-badge"><i class="bi bi-pencil-square"></i> Không gian đánh giá</span>
                <h1 class="hdv-hero-title">Ghi lại điều hành thực tế một cách ngắn gọn, chuyên nghiệp và có ích cho đội vận hành.</h1>
                <p class="hdv-hero-text">
                    Khu vực này dùng để chọn tour đã hoàn thành và chuyển sang trang phản hồi chi tiết.
                    Bạn có thể dùng phản hồi để nêu chất lượng nhà cung cấp, mức độ hài lòng của khách, sự cố phát sinh và đề xuất cải thiện.
                </p>
                <div class="hdv-pill-list">
                    <span class="hdv-pill"><i class="bi bi-award"></i> Ưu tiên nội dung rõ ràng</span>
                    <span class="hdv-pill"><i class="bi bi-shield-check"></i> Theo dõi chất lượng vận hành</span>
                    <span class="hdv-pill"><i class="bi bi-people"></i> Tập trung trải nghiệm đoàn</span>
                </div>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Tour đang chọn</h4>
                    <p class="hdv-card-subtitle">Tóm tắt nhanh để bạn tiếp tục thao tác</p>
                </div>
                <span class="hdv-soft-badge primary"><i class="bi bi-pin-map"></i> Đang xem</span>
            </div>
            <div class="hdv-card-body">
                <?php if ($selectedTour): ?>
                    <div class="hdv-list-item">
                        <div class="hdv-list-head">
                            <div>
                                <h5 class="hdv-list-title"><?php echo htmlspecialchars($selectedTour['ten_tour'] ?? 'Tour đã chọn'); ?></h5>
                                <div class="hdv-list-meta mt-2">
                                    <span><i class="bi bi-calendar3"></i> Khởi hành <?php echo !empty($selectedTour['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($selectedTour['ngay_khoi_hanh'])) : 'N/A'; ?></span>
                                    <span><i class="bi bi-check2-circle"></i> Trạng thái <?php echo htmlspecialchars($selectedTour['trang_thai'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0 hdv-muted">
                            Bạn có thể chuyển ngay sang biểu mẫu phản hồi chi tiết để ghi nhận chất lượng tour, dịch vụ và các điểm cần rút kinh nghiệm.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="hdv-empty">
                        <i class="bi bi-journal-richtext"></i>
                        Chọn một tour đã hoàn thành bên dưới để bắt đầu tạo phản hồi chi tiết.
                    </div>
                <?php endif; ?>
            </div>
            <div class="hdv-card-footer">
                <?php if ($selectedTour): ?>
                    <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo (int)($selectedTour['id'] ?? 0); ?>" class="btn btn-primary">
                        <i class="bi bi-arrow-right-circle"></i> Mở biểu mẫu phản hồi
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="hdv-section-spacer"></div>

    <div class="hdv-card">
        <div class="hdv-card-header">
            <div>
                <h4 class="hdv-card-title">Danh sách tour đủ điều kiện đánh giá</h4>
                <p class="hdv-card-subtitle">Chọn đúng tour để mở khu vực phản hồi chi tiết</p>
            </div>
            <span class="hdv-soft-badge neutral"><i class="bi bi-list-task"></i> <?php echo $completedCount; ?> tour</span>
        </div>
        <div class="hdv-card-body">
            <?php if (!empty($completedTours)): ?>
                <div class="hdv-list">
                    <?php foreach ($completedTours as $item): ?>
                        <?php $isActive = $selectedTour && (int)($selectedTour['id'] ?? 0) === (int)($item['id'] ?? 0); ?>
                        <div class="hdv-list-item">
                            <div class="hdv-list-head">
                                <div>
                                    <h5 class="hdv-list-title"><?php echo htmlspecialchars($item['ten_tour'] ?? 'Tour'); ?></h5>
                                    <div class="hdv-list-meta mt-2">
                                        <span><i class="bi bi-calendar3"></i> <?php echo !empty($item['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($item['ngay_khoi_hanh'])) : 'N/A'; ?></span>
                                        <span><i class="bi bi-flag"></i> Mã lịch #<?php echo (int)($item['id'] ?? 0); ?></span>
                                    </div>
                                </div>
                                <span class="hdv-soft-badge <?php echo $isActive ? 'primary' : 'warning'; ?>">
                                    <i class="bi bi-<?php echo $isActive ? 'check-circle' : 'clock'; ?>"></i>
                                    <?php echo $isActive ? 'Đang chọn' : 'Chưa mở'; ?>
                                </span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="index.php?act=hdv/danhGia&tour_id=<?php echo (int)($item['id'] ?? 0); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Xem tóm tắt
                                </a>
                                <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo (int)($item['id'] ?? 0); ?>" class="btn btn-primary">
                                    <i class="bi bi-chat-left-text"></i> Viết phản hồi
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="hdv-empty">
                    <i class="bi bi-hourglass-split"></i>
                    Chưa có tour hoàn thành nào để đánh giá. Khi một tour kết thúc, mục phản hồi sẽ xuất hiện tại đây.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
