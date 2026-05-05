<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ HDV - <?php echo htmlspecialchars($hdv_info['ho_ten'] ?? 'HDV'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$todayToursCount = count($today_tours ?? []);
$upcomingPreview = array_slice($upcoming_tours ?? [], 0, 3);
$recentNotifications = $recent_notifications ?? [];
$adminNotifications = array_slice($recentNotifications, 0, 4);

$notificationTypeLabels = [
    'LichTour' => ['label' => 'Lịch tour', 'class' => 'primary', 'icon' => 'calendar2-week'],
    'NhacNho' => ['label' => 'Nhắc nhở', 'class' => 'warning', 'icon' => 'alarm'],
    'CanhBao' => ['label' => 'Cảnh báo', 'class' => 'danger', 'icon' => 'shield-exclamation'],
    'ThongBao' => ['label' => 'Thông báo', 'class' => 'neutral', 'icon' => 'megaphone'],
];

$hdvRealtimeWsEnabled = realtimeWebSocketEnabled() && isLoggedIn() && hasRole('HDV');
$hdvRealtimeWsUrl = $hdvRealtimeWsEnabled ? realtimeWebSocketPublicUrl() : '';
$hdvRealtimeWsToken = $hdvRealtimeWsEnabled
    ? buildRealtimeAuthToken((int)($_SESSION['user_id'] ?? 0), 'HDV', 'notifications')
    : '';
?>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-house-door"></i> Trang chủ HDV</h3>
                <p class="mb-0 opacity-75">Bảng điều khiển tổng hợp tour, nhắc việc và cập nhật điều hành dành riêng cho bạn.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="index.php?act=hdv/notifications" class="btn btn-light position-relative">
                    <i class="bi bi-bell"></i> Thông báo
                    <span
                        id="hdvNotificationBadge"
                        class="notification-badge"
                        data-initial="<?php echo (int)($notifications_count ?? 0); ?>"
                        <?php if (empty($notifications_count)): ?>style="display:none"<?php endif; ?>
                    ><?php echo (int)($notifications_count ?? 0); ?></span>
                </a>
                <a href="index.php?act=hdv/profile" class="btn btn-light"><i class="bi bi-person-circle"></i> Hồ sơ</a>
                <a href="index.php?act=auth/logout" class="btn btn-outline-light"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
            </div>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <div class="hdv-card mb-4">
        <div class="hdv-card-body hdv-hero">
            <span class="hdv-hero-badge"><i class="bi bi-compass"></i> Daily command</span>
            <h1 class="hdv-hero-title">Xin chào, <?php echo htmlspecialchars($hdv_info['ho_ten'] ?? 'Hướng dẫn viên'); ?>!</h1>
            <p class="hdv-hero-text">
                Hôm nay là <?php echo date('d/m/Y'); ?>.
                <?php if ($todayToursCount > 0): ?>
                    Bạn có <?php echo $todayToursCount; ?> tour diễn ra trong ngày, hãy kiểm tra lịch trình và thông báo điều hành trước giờ tập trung.
                <?php else: ?>
                    Đây là thời điểm phù hợp để rà soát lịch sắp tới, nhật ký tour và các thông báo mới từ Admin.
                <?php endif; ?>
            </p>
            <div class="hdv-pill-list">
                <span class="hdv-pill"><i class="bi bi-calendar-check"></i> Tour hôm nay: <?php echo $todayToursCount; ?></span>
                <span class="hdv-pill"><i class="bi bi-bell"></i> Chưa đọc: <?php echo (int)($notifications_count ?? 0); ?></span>
                <span class="hdv-pill"><i class="bi bi-star-fill"></i> Đánh giá TB: <?php echo number_format((float)($stats['rating'] ?? 0), 1); ?></span>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-calendar-week"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tour sắp tới</div>
                    <div class="hdv-kpi-value"><?php echo (int)($stats['upcoming_tours'] ?? 0); ?></div>
                    <div class="hdv-kpi-meta">Các lịch khởi hành chuẩn bị diễn ra</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-play-circle"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tour đang chạy</div>
                    <div class="hdv-kpi-value"><?php echo (int)($stats['ongoing_tours'] ?? 0); ?></div>
                    <div class="hdv-kpi-meta">Những tour đang trong giai đoạn vận hành</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="hdv-kpi-label">Tour hoàn thành</div>
                    <div class="hdv-kpi-value"><?php echo (int)($stats['completed_tours'] ?? 0); ?></div>
                    <div class="hdv-kpi-meta">Tổng số tour đã khép lại</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Tour sắp tới</h4>
                    <p class="hdv-card-subtitle">Ưu tiên kiểm tra các tour diễn ra trong 7 ngày tới để chuẩn bị tài liệu, điểm danh và briefing đoàn</p>
                </div>
                <a href="index.php?act=hdv/tours" class="btn btn-outline-secondary btn-sm">Xem tất cả tour</a>
            </div>
            <div class="hdv-card-body">
                <?php if (!empty($upcomingPreview)): ?>
                    <div class="hdv-list">
                        <?php foreach ($upcomingPreview as $tour): ?>
                            <div class="hdv-list-item">
                                <div class="hdv-list-head">
                                    <div>
                                        <h5 class="hdv-list-title"><?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tour'); ?></h5>
                                        <div class="hdv-list-meta mt-2">
                                            <span><i class="bi bi-calendar3"></i> <?php echo !empty($tour['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($tour['ngay_khoi_hanh'])) : 'N/A'; ?></span>
                                            <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($tour['diem_tap_trung'] ?? 'Chưa xác định'); ?></span>
                                            <span><i class="bi bi-people"></i> <?php echo htmlspecialchars((string)($tour['so_nguoi'] ?? 'N/A')); ?> khách</span>
                                        </div>
                                    </div>
                                    <span class="hdv-soft-badge primary"><i class="bi bi-flag"></i> Sắp tới</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="index.php?act=hdv/tour_detail&id=<?php echo (int)($tour['tour_id'] ?? 0); ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-eye"></i> Xem chi tiết
                                    </a>
                                    <a href="index.php?act=hdv/danhSachKhach&lich_id=<?php echo (int)($tour['id'] ?? 0); ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-people"></i> Danh sách khách
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="hdv-empty">
                        <i class="bi bi-calendar-x"></i>
                        Hiện tại bạn chưa có tour nào sắp tới.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Thông báo từ Admin</h4>
                    <p class="hdv-card-subtitle">Các cập nhật điều hành và nhắc việc mới nhất được đưa thẳng lên trang chủ</p>
                </div>
                <a href="index.php?act=hdv/notifications" class="btn btn-outline-secondary btn-sm">Xem tất cả</a>
            </div>
            <div class="hdv-card-body">
                <?php if (!empty($adminNotifications)): ?>
                    <div class="hdv-list">
                        <?php foreach ($adminNotifications as $notif): ?>
                            <?php
                            $typeInfo = $notificationTypeLabels[$notif['loai_thong_bao'] ?? 'ThongBao'] ?? $notificationTypeLabels['ThongBao'];
                            $isUnread = empty($notif['da_xem']);
                            ?>
                            <div class="hdv-list-item">
                                <div class="hdv-list-head">
                                    <div>
                                        <h5 class="hdv-list-title"><?php echo htmlspecialchars($notif['tieu_de'] ?? 'Thông báo'); ?></h5>
                                        <div class="hdv-list-meta mt-2">
                                            <span><i class="bi bi-clock"></i> <?php echo !empty($notif['ngay_gui']) ? date('d/m/Y H:i', strtotime($notif['ngay_gui'])) : 'N/A'; ?></span>
                                            <span><i class="bi bi-person-workspace"></i> Admin / Điều hành</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                                        <span class="hdv-soft-badge <?php echo $typeInfo['class']; ?>">
                                            <i class="bi bi-<?php echo $typeInfo['icon']; ?>"></i> <?php echo $typeInfo['label']; ?>
                                        </span>
                                        <?php if ($isUnread): ?>
                                            <span class="hdv-soft-badge warning"><i class="bi bi-sparkles"></i> Mới</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($notif['noi_dung'] ?? '')); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="hdv-empty">
                        <i class="bi bi-megaphone"></i>
                        Hiện chưa có thông báo mới từ Admin.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
document.addEventListener('DOMContentLoaded', function () {
    var badge = document.getElementById('hdvNotificationBadge');
    var pollingTimerId = null;
    var wsReconnectTimerId = null;
    var webSocketClient = null;
    var visiblePollingMs = 5000;
    var hiddenPollingMs = 20000;
    var wsReconnectMs = 3500;
    var realtimeWsEnabled = <?php echo ($hdvRealtimeWsEnabled && $hdvRealtimeWsUrl !== '' && $hdvRealtimeWsToken !== '') ? 'true' : 'false'; ?>;
    var realtimeWsUrl = <?php echo json_encode($hdvRealtimeWsUrl, JSON_UNESCAPED_UNICODE); ?>;
    var realtimeWsToken = <?php echo json_encode($hdvRealtimeWsToken, JSON_UNESCAPED_UNICODE); ?>;

    function renderBadge(count) {
        if (!badge) return;
        var safeCount = Math.max(0, Number(count || 0));
        badge.textContent = String(safeCount);
        if (safeCount > 0) {
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    }

    async function fetchUnreadCount() {
        try {
            var response = await fetch('index.php?act=hdv/notificationCounts&_ts=' + Date.now(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!response.ok) return;

            var data = await response.json();
            if (!data || data.success !== true) return;
            renderBadge(data.unread || 0);
        } catch (error) {
            // Bỏ qua lỗi mạng tạm thời.
        }
    }

    function startPolling() {
        if (pollingTimerId) {
            window.clearInterval(pollingTimerId);
        }

        var intervalMs = document.hidden ? hiddenPollingMs : visiblePollingMs;
        pollingTimerId = window.setInterval(fetchUnreadCount, intervalMs);
    }

    function stopPolling() {
        if (pollingTimerId) {
            window.clearInterval(pollingTimerId);
            pollingTimerId = null;
        }
    }

    function clearWsReconnect() {
        if (wsReconnectTimerId) {
            window.clearTimeout(wsReconnectTimerId);
            wsReconnectTimerId = null;
        }
    }

    function scheduleWsReconnect() {
        if (wsReconnectTimerId || document.hidden) {
            return;
        }

        wsReconnectTimerId = window.setTimeout(function () {
            wsReconnectTimerId = null;
            openWebSocket();
        }, wsReconnectMs);
    }

    function stopWebSocket() {
        clearWsReconnect();
        if (webSocketClient) {
            webSocketClient.close();
            webSocketClient = null;
        }
    }

    function openWebSocket() {
        if (!realtimeWsEnabled || typeof WebSocket === 'undefined') {
            return false;
        }

        clearWsReconnect();
        stopWebSocket();

        var joinChar = realtimeWsUrl.indexOf('?') >= 0 ? '&' : '?';
        var wsUrl = realtimeWsUrl + joinChar + 'token=' + encodeURIComponent(realtimeWsToken);
        webSocketClient = new WebSocket(wsUrl);

        webSocketClient.onopen = function () {
            stopPolling();
        };

        webSocketClient.onmessage = function (event) {
            try {
                var packet = JSON.parse(event.data || '{}');
                if (!packet) return;
                if (packet.type === 'ping') {
                    webSocketClient.send(JSON.stringify({ type: 'pong', payload: { ts: packet.payload && packet.payload.ts } }));
                    return;
                }
                if (packet.type !== 'notification' || !packet.payload || packet.payload.success !== true) {
                    return;
                }

                renderBadge(packet.payload.unread || 0);
            } catch (error) {
                // Bỏ qua payload không hợp lệ.
            }
        };

        webSocketClient.onerror = function () {
            // onclose xử lý fallback.
        };

        webSocketClient.onclose = function () {
            webSocketClient = null;
            startPolling();
            scheduleWsReconnect();
        };

        return true;
    }

    function runRealtimeByVisibility() {
        stopPolling();
        stopWebSocket();

        if (document.hidden) {
            startPolling();
            return;
        }

        if (realtimeWsEnabled && typeof WebSocket !== 'undefined') {
            openWebSocket();
            startPolling();
        } else {
            startPolling();
        }
    }

    renderBadge(Number(badge ? badge.getAttribute('data-initial') : 0));
    fetchUnreadCount();
    runRealtimeByVisibility();

    document.addEventListener('visibilitychange', function () {
        runRealtimeByVisibility();
        if (!document.hidden) {
            fetchUnreadCount();
        }
    });

    window.addEventListener('beforeunload', function () {
        stopWebSocket();
        stopPolling();
    });
});
</script>
</body>
</html>
