<?php
$pageTitle  = 'Thông báo của bạn';
$activePage = 'thongBao';
$pageHero   = [
    'eyebrow'  => 'TRUNG TÂM THÔNG BÁO',
    'icon'     => 'bi-bell',
    'title'    => 'Thông báo của bạn',
    'subtitle' => 'Cập nhật mọi tin tức, đặt tour và ưu đãi từ DuLichPro.',
];
ob_start(); ?>
        body {
            background: #f5f7fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .notification-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .notification-card:hover {
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
        }
        .notification-card.unread {
            background: #f8f9ff;
            border-left-color: #667eea;
        }
        .notification-card.read {
            background: #f8f9fa;
            border-left-color: #6c757d;
        }
<?php $extraCss = ob_get_clean();
$thongBaoChuaDoc = (int)($thongBaoChuaDoc ?? 0);
if (!isset($thongBaoList) || !is_array($thongBaoList)) { $thongBaoList = []; }
$khRealtimeWsEnabled = realtimeWebSocketEnabled() && isLoggedIn() && hasRole('KhachHang');
$khRealtimeWsUrl   = $khRealtimeWsEnabled ? realtimeWebSocketPublicUrl() : '';
$khRealtimeWsToken = $khRealtimeWsEnabled
    ? buildRealtimeAuthToken((int)($_SESSION['user_id'] ?? 0), 'KhachHang', 'notifications')
    : '';
$unreadCount = $thongBaoChuaDoc;
include __DIR__ . '/_layout/header.php'; ?>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-bell me-2"></i>Thông báo</h2>
            <a href="index.php?act=khachHang/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Bạn có <span class="text-primary" id="notificationUnreadCount"><?php echo $thongBaoChuaDoc; ?></span> thông báo chưa đọc</h5>
                    </div>
                </div>
            </div>
        </div>

        <div id="notificationList">
            <?php if (!empty($thongBaoList)): ?>
                <?php foreach ($thongBaoList as $tb): ?>
                    <div class="notification-card <?php echo empty($tb['da_doc']) || $tb['da_doc'] == 0 ? 'unread' : 'read'; ?>" data-notification-id="<?php echo (int)($tb['id'] ?? 0); ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 me-2"><?php echo htmlspecialchars($tb['tieu_de'] ?? ''); ?></h5>
                                    <?php if (empty($tb['da_doc']) || $tb['da_doc'] == 0): ?>
                                        <span class="badge bg-primary">Mới</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted mb-2"><?php echo nl2br(htmlspecialchars($tb['noi_dung'] ?? '')); ?></p>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?php 
                                    if (!empty($tb['thoi_gian_gui'])) {
                                        echo date('d/m/Y H:i', strtotime($tb['thoi_gian_gui']));
                                    } elseif (!empty($tb['created_at'])) {
                                        echo date('d/m/Y H:i', strtotime($tb['created_at']));
                                    }
                                    ?>
                                </small>
                            </div>
                            <?php if (empty($tb['da_doc']) || $tb['da_doc'] == 0): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2 js-mark-read-btn" data-id="<?php echo (int)($tb['id'] ?? 0); ?>">
                                    <i class="bi bi-check"></i> Đánh dấu đã đọc
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="alert alert-info" id="notificationEmptyState" <?php if (!empty($thongBaoList)): ?>style="display:none"<?php endif; ?>>
            <i class="bi bi-info-circle me-2"></i>Bạn chưa có thông báo nào.
        </div>
    </div>

    <script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    document.addEventListener('DOMContentLoaded', function () {
        var notificationUnreadCount = document.getElementById('notificationUnreadCount');
        var notificationList = document.getElementById('notificationList');
        var notificationEmptyState = document.getElementById('notificationEmptyState');
        var notificationEventSource = null;
        var notificationWebSocket = null;
        var pollingTimerId = null;
        var visiblePollingMs = 5000;
        var hiddenPollingMs = 20000;
        var wsReconnectTimerId = null;
        var wsReconnectMs = 3500;
        var realtimeWsEnabled = <?php echo ($khRealtimeWsEnabled && $khRealtimeWsUrl !== '' && $khRealtimeWsToken !== '') ? 'true' : 'false'; ?>;
        var realtimeWsUrl = <?php echo json_encode($khRealtimeWsUrl, JSON_UNESCAPED_UNICODE); ?>;
        var realtimeWsToken = <?php echo json_encode($khRealtimeWsToken, JSON_UNESCAPED_UNICODE); ?>;

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>'"]/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '\'': '&#39;', '"': '&quot;' })[char];
            });
        }

        function formatDate(value) {
            if (!value) return '';
            var parsed = new Date(value.replace(' ', 'T'));
            if (Number.isNaN(parsed.getTime())) return escapeHtml(value);
            return parsed.toLocaleString('vi-VN', { hour12: false });
        }

        function setUnreadCount(count) {
            if (notificationUnreadCount) {
                notificationUnreadCount.textContent = String(Number(count || 0));
            }
        }

        function buildNotificationHtml(item) {
            var isUnread = Number(item.da_doc || 0) === 0;
            var timestamp = item.thoi_gian_gui || item.created_at || '';
            return '<div class="notification-card ' + (isUnread ? 'unread' : 'read') + '" data-notification-id="' + Number(item.id || 0) + '">' +
                '<div class="d-flex justify-content-between align-items-start">' +
                    '<div class="flex-grow-1">' +
                        '<div class="d-flex align-items-center mb-2">' +
                            '<h5 class="mb-0 me-2">' + escapeHtml(item.tieu_de || '') + '</h5>' +
                            (isUnread ? '<span class="badge bg-primary">Mới</span>' : '') +
                        '</div>' +
                        '<p class="text-muted mb-2">' + escapeHtml(item.noi_dung || '').replace(/\n/g, '<br>') + '</p>' +
                        '<small class="text-muted"><i class="bi bi-clock me-1"></i>' + formatDate(timestamp) + '</small>' +
                    '</div>' +
                    (isUnread ?
                        '<button type="button" class="btn btn-sm btn-outline-primary ms-2 js-mark-read-btn" data-id="' + Number(item.id || 0) + '"><i class="bi bi-check"></i> Đánh dấu đã đọc</button>'
                        : '') +
                '</div>' +
            '</div>';
        }

        function renderNotificationList(items) {
            if (!notificationList) return;

            if (!Array.isArray(items) || items.length === 0) {
                notificationList.innerHTML = '';
                if (notificationEmptyState) notificationEmptyState.style.display = 'block';
                return;
            }

            if (notificationEmptyState) notificationEmptyState.style.display = 'none';
            notificationList.innerHTML = items.map(buildNotificationHtml).join('');
        }

        async function fetchNotificationFeed() {
            try {
                var response = await fetch('index.php?act=khachHang/notificationFeed&_ts=' + Date.now(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                var data = await response.json();
                if (!data || data.success !== true) return;

                setUnreadCount(data.unread || 0);
                renderNotificationList(data.items || []);
            } catch (error) {
                // Bỏ qua lỗi mạng tạm thời.
            }
        }

        function openNotificationStream() {
            if (typeof EventSource === 'undefined') {
                startNotificationPolling();
                return;
            }

            if (notificationEventSource) {
                notificationEventSource.close();
                notificationEventSource = null;
            }

            notificationEventSource = new EventSource('index.php?act=khachHang/notificationStream');
            notificationEventSource.addEventListener('notification', function (event) {
                try {
                    var payload = JSON.parse(event.data || '{}');
                    if (!payload || payload.success !== true) return;
                    setUnreadCount(payload.unread || 0);
                    renderNotificationList(payload.items || []);
                } catch (error) {
                    // Bỏ qua payload không hợp lệ.
                }
            });

            notificationEventSource.onerror = function () {
                if (notificationEventSource) {
                    notificationEventSource.close();
                    notificationEventSource = null;
                }
                startNotificationPolling();
            };
        }

        function clearWsReconnectTimer() {
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
                openNotificationWebSocket();
            }, wsReconnectMs);
        }

        function openNotificationWebSocket() {
            if (!realtimeWsEnabled || typeof WebSocket === 'undefined') {
                return false;
            }

            clearWsReconnectTimer();

            if (notificationWebSocket) {
                notificationWebSocket.close();
                notificationWebSocket = null;
            }

            var joinChar = realtimeWsUrl.indexOf('?') >= 0 ? '&' : '?';
            var wsUrl = realtimeWsUrl + joinChar + 'token=' + encodeURIComponent(realtimeWsToken);
            notificationWebSocket = new WebSocket(wsUrl);

            notificationWebSocket.onopen = function () {
                stopNotificationPolling();
            };

            notificationWebSocket.onmessage = function (event) {
                try {
                    var packet = JSON.parse(event.data || '{}');
                    if (!packet || packet.type !== 'notification' || !packet.payload || packet.payload.success !== true) {
                        return;
                    }

                    setUnreadCount(packet.payload.unread || 0);
                    renderNotificationList(packet.payload.items || []);
                } catch (error) {
                    // Bỏ qua payload không hợp lệ.
                }
            };

            notificationWebSocket.onerror = function () {
                // onclose xử lý fallback.
            };

            notificationWebSocket.onclose = function () {
                notificationWebSocket = null;
                startNotificationPolling();
                scheduleWsReconnect();
            };

            return true;
        }

        function stopNotificationStream() {
            if (notificationEventSource) {
                notificationEventSource.close();
                notificationEventSource = null;
            }
        }

        function stopNotificationWebSocket() {
            clearWsReconnectTimer();
            if (notificationWebSocket) {
                notificationWebSocket.close();
                notificationWebSocket = null;
            }
        }

        function startNotificationPolling() {
            if (pollingTimerId) {
                window.clearInterval(pollingTimerId);
            }
            var intervalMs = document.hidden ? hiddenPollingMs : visiblePollingMs;
            pollingTimerId = window.setInterval(fetchNotificationFeed, intervalMs);
        }

        function stopNotificationPolling() {
            if (pollingTimerId) {
                window.clearInterval(pollingTimerId);
                pollingTimerId = null;
            }
        }

        function runRealtimeByVisibility() {
            stopNotificationPolling();
            stopNotificationStream();
            stopNotificationWebSocket();

            if (document.hidden) {
                startNotificationPolling();
                return;
            }

            if (realtimeWsEnabled && typeof WebSocket !== 'undefined') {
                openNotificationWebSocket();
                startNotificationPolling();
            } else if (typeof EventSource !== 'undefined') {
                openNotificationStream();
                startNotificationPolling();
            } else {
                startNotificationPolling();
            };
        }

        notificationList.addEventListener('click', async function (event) {
            var target = event.target.closest('.js-mark-read-btn');
            if (!target) return;

            var id = Number(target.getAttribute('data-id') || 0);
            if (id <= 0) return;

            target.disabled = true;
            try {
                var response = await fetch('index.php?act=khachHang/thongBao&mark_read=' + id, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;

                var data = await response.json();
                if (!data || data.success !== true) return;

                var card = target.closest('.notification-card');
                if (card) {
                    card.classList.remove('unread');
                    card.classList.add('read');
                    var badge = card.querySelector('.badge.bg-primary');
                    if (badge) badge.remove();
                    target.remove();
                }
                setUnreadCount(data.unread || 0);
            } catch (error) {
                // Bỏ qua lỗi mạng tạm thời.
            } finally {
                target.disabled = false;
            }
        });

        fetchNotificationFeed();
        runRealtimeByVisibility();
        document.addEventListener('visibilitychange', function () {
            runRealtimeByVisibility();
            if (!document.hidden) {
                fetchNotificationFeed();
            }
        });
        window.addEventListener('beforeunload', function () {
            stopNotificationWebSocket();
            stopNotificationStream();
            stopNotificationPolling();
        });
    });
    </script>
<?php include __DIR__ . '/_layout/footer.php'; ?>


