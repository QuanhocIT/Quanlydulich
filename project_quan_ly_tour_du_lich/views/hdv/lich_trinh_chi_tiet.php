<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch trình chi tiết tour - HDV</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
require_once __DIR__ . '/../../models/Tour.php';
$tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
$tourModel = new Tour();
$tour = $tourModel->findById($tourId);
$lichTrinhList = $tourModel->getLichTrinhByTourId($tourId);
$soNgay = count($lichTrinhList);
?>
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-calendar2-week"></i> Lịch trình chi tiết tour</h3>
                <p class="mb-0 opacity-75">Theo dõi nhịp vận hành theo từng ngày để chuẩn bị đón đoàn, di chuyển và điều phối dịch vụ thật mượt.</p>
            </div>
            <a href="javascript:history.back()" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-calendar-range"></i></div>
                <div>
                    <div class="hdv-kpi-label">Số ngày hành trình</div>
                    <div class="hdv-kpi-value"><?php echo $soNgay; ?></div>
                    <div class="hdv-kpi-meta">Tổng số chặng hiện có trong lịch trình</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tên tour</div>
                    <div class="hdv-kpi-value" style="font-size:1.25rem"><?php echo htmlspecialchars($tour['ten_tour'] ?? 'Chưa xác định'); ?></div>
                    <div class="hdv-kpi-meta">Thông tin điều hành đang được mở</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-geo-alt"></i></div>
                <div>
                    <div class="hdv-kpi-label">Điểm tập trung</div>
                    <div class="hdv-kpi-value" style="font-size:1.15rem"><?php echo htmlspecialchars($tour['diem_tap_trung'] ?? 'Chưa cập nhật'); ?></div>
                    <div class="hdv-kpi-meta">Mốc xuất phát của đoàn</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-body hdv-hero">
                <span class="hdv-hero-badge"><i class="bi bi-map"></i> Executive itinerary</span>
                <h1 class="hdv-hero-title">Một timeline rõ ràng giúp bạn điều phối tour tự tin hơn trong mọi điểm chạm của hành trình.</h1>
                <p class="hdv-hero-text">
                    Từ giờ tập trung, điểm đến trong ngày cho tới hoạt động trọng tâm, mọi nội dung được trình bày theo nhịp thời gian để HDV dễ rà soát trước khi dẫn đoàn.
                </p>
                <div class="hdv-pill-list">
                    <span class="hdv-pill"><i class="bi bi-sunrise"></i> Mở đầu ngày rõ ràng</span>
                    <span class="hdv-pill"><i class="bi bi-bus-front"></i> Dễ theo dõi điểm di chuyển</span>
                    <span class="hdv-pill"><i class="bi bi-journal-check"></i> Thuận tiện đối chiếu vận hành</span>
                </div>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Thông tin tổng quan</h4>
                    <p class="hdv-card-subtitle">Tóm tắt nhanh trước khi đọc chi tiết từng ngày</p>
                </div>
            </div>
            <div class="hdv-card-body">
                <div class="hdv-list">
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Thời gian dự kiến</h5>
                        <div class="hdv-list-meta">
                            <span><i class="bi bi-calendar3"></i> Khởi hành: <?php echo !empty($tour['ngay_khoi_hanh']) ? date('d/m/Y H:i', strtotime($tour['ngay_khoi_hanh'])) : 'N/A'; ?></span>
                            <span><i class="bi bi-calendar-check"></i> Kết thúc: <?php echo !empty($tour['ngay_ket_thuc']) ? date('d/m/Y H:i', strtotime($tour['ngay_ket_thuc'])) : 'N/A'; ?></span>
                        </div>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Lưu ý điều hành</h5>
                        <p class="mb-0 hdv-muted">Nên rà lại toàn bộ lịch trình trước ngày đi để thống nhất giờ tập trung, nội dung briefing cho khách và các điểm có rủi ro trễ giờ.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-section-spacer"></div>

    <div class="hdv-card">
        <div class="hdv-card-header">
            <div>
                <h4 class="hdv-card-title">Timeline hành trình</h4>
                <p class="hdv-card-subtitle">Trình bày theo từng ngày để HDV theo dõi mạch di chuyển của đoàn</p>
            </div>
            <span class="hdv-soft-badge primary"><i class="bi bi-clock-history"></i> <?php echo $soNgay; ?> ngày</span>
        </div>
        <div class="hdv-card-body">
            <?php if (!empty($lichTrinhList)): ?>
                <div class="hdv-timeline">
                    <?php foreach ($lichTrinhList as $index => $lichTrinh): ?>
                        <div class="hdv-timeline-item">
                            <div class="hdv-timeline-dot"><?php echo $index + 1; ?></div>
                            <div class="hdv-timeline-card">
                                <div class="hdv-timeline-day">
                                    <i class="bi bi-calendar-day"></i>
                                    Ngày <?php echo htmlspecialchars($lichTrinh['ngay_thu'] ?? ($index + 1)); ?>
                                </div>
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
</body>
</html>
