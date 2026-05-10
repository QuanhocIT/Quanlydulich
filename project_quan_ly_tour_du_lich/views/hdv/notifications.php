<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - HDV</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$notifications = $notifications ?? [];
$totalNotifications = count($notifications);
$unreadCount = 0;
$latestNotificationDate = null;

foreach ($notifications as $item) {
    if (empty($item['da_xem'])) {
        $unreadCount++;
    }
    if ($latestNotificationDate === null && !empty($item['ngay_gui'])) {
        $latestNotificationDate = date('d/m/Y H:i', strtotime($item['ngay_gui']));
    }
}
?>
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-bell"></i> Thông báo</h3>
                <p class="mb-0 opacity-75">Toàn bộ cập nhật liên quan đến phân công, vận hành tour và nhắc việc dành cho bạn được gom tại đây.</p>
            </div>
            <a href="index.php?act=hdv/dashboard" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Trang chủ
            </a>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-inboxes"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tổng thông báo</div>
                    <div class="hdv-kpi-value"><?php echo $totalNotifications; ?></div>
                    <div class="hdv-kpi-meta">Lịch sử cập nhật gần đây của bạn</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-envelope-open"></i></div>
                <div>
                    <div class="hdv-kpi-label">Chưa đọc trước khi mở trang</div>
                    <div class="hdv-kpi-value"><?php echo $unreadCount; ?></div>
                    <div class="hdv-kpi-meta">Trang này sẽ đánh dấu chúng là đã xem</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-clock-history"></i></div>
                <div>
                    <div class="hdv-kpi-label">Cập nhật mới nhất</div>
                    <div class="hdv-kpi-value" style="font-size:1.2rem"><?php echo $latestNotificationDate ?: 'Chưa có'; ?></div>
                    <div class="hdv-kpi-meta">Mốc thời gian gần nhất hệ thống gửi tới bạn</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-body hdv-hero">
                <span class="hdv-hero-badge"><i class="bi bi-broadcast-pin"></i> Trung tâm cập nhật</span>
                <h1 class="hdv-hero-title">Thông báo được trình bày theo dòng thời gian để bạn nắm việc nhanh và không bỏ sót điểm quan trọng.</h1>
                <p class="hdv-hero-text">
                    Ưu tiên xử lý trước các cập nhật về phân công tour, thay đổi lịch khởi hành, yêu cầu đặc biệt của khách và các nhắc việc vận hành trong ngày.
                </p>
                <div class="hdv-pill-list">
                    <span class="hdv-pill"><i class="bi bi-check2-all"></i> Tự động đánh dấu đã xem</span>
                    <span class="hdv-pill"><i class="bi bi-lightning-charge"></i> Ưu tiên việc gấp</span>
                    <span class="hdv-pill"><i class="bi bi-journal-text"></i> Gắn với luồng làm việc HDV</span>
                </div>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Gợi ý xử lý</h4>
                    <p class="hdv-card-subtitle">Ba nhóm nội dung nên ưu tiên khi đọc</p>
                </div>
            </div>
            <div class="hdv-card-body">
                <div class="hdv-list">
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">1. Xác nhận thay đổi lịch và phân công</h5>
                        <p class="mb-0 hdv-muted">Kiểm tra các thông báo liên quan đến lịch khởi hành, tour mới được giao hoặc điều chỉnh nhân sự.</p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">2. Rà yêu cầu đặc biệt của khách</h5>
                        <p class="mb-0 hdv-muted">Ưu tiên các yêu cầu ăn uống, sức khỏe, di chuyển hoặc lưu trú cần chuẩn bị trước ngày đi.</p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">3. Chốt việc sau tour</h5>
                        <p class="mb-0 hdv-muted">Nếu có nhắc phản hồi, nhật ký tour hoặc công nợ, nên xử lý sớm để đội điều hành tổng hợp.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-section-spacer"></div>

    <div class="hdv-card">
        <div class="hdv-card-header">
            <div>
                <h4 class="hdv-card-title">Dòng thời gian thông báo</h4>
                <p class="hdv-card-subtitle">Sắp xếp theo thứ tự mới nhất để bạn theo dõi liên tục</p>
            </div>
            <span class="hdv-soft-badge primary"><i class="bi bi-bell-fill"></i> Live feed</span>
        </div>
        <div class="hdv-card-body">
            <?php if (!empty($notifications)): ?>
                <div class="hdv-timeline">
                    <?php foreach ($notifications as $notif): ?>
                        <?php $isUnread = empty($notif['da_xem']); ?>
                        <div class="hdv-timeline-item">
                            <div class="hdv-timeline-dot"><i class="bi bi-bell"></i></div>
                            <div class="hdv-timeline-card">
                                <div class="hdv-list-head">
                                    <div>
                                        <h5 class="hdv-list-title">
                                            <?php echo htmlspecialchars($notif['tieu_de'] ?? 'Thông báo'); ?>
                                        </h5>
                                        <div class="hdv-list-meta mt-2">
                                            <span><i class="bi bi-clock"></i> <?php echo !empty($notif['ngay_gui']) ? date('d/m/Y H:i', strtotime($notif['ngay_gui'])) : 'N/A'; ?></span>
                                            <?php if (!empty($notif['loai_thong_bao'])): ?>
                                                <span><i class="bi bi-tag"></i> <?php echo htmlspecialchars($notif['loai_thong_bao']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="hdv-soft-badge <?php echo $isUnread ? 'warning' : 'neutral'; ?>">
                                        <i class="bi bi-<?php echo $isUnread ? 'sparkles' : 'check2'; ?>"></i>
                                        <?php echo $isUnread ? 'Vừa mở' : 'Đã xem'; ?>
                                    </span>
                                </div>
                                <p class="mb-0 mt-3"><?php echo nl2br(htmlspecialchars($notif['noi_dung'] ?? '')); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="hdv-empty">
                    <i class="bi bi-bell-slash"></i>
                    Hiện chưa có thông báo nào dành cho bạn.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
