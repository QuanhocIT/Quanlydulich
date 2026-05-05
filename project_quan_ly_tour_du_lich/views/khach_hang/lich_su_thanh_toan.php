<?php
$pageTitle  = 'Lịch sử thanh toán';
$activePage = 'hoaDon';
$pageHero   = [
    'icon'     => 'bi-clock-history',
    'title'    => 'Lịch sử thanh toán',
    'subtitle' => 'Xem lại toàn bộ giao dịch thanh toán online của bạn.',
];
ob_start(); ?>
        body { background: #f5f7fa; }
        .panel { background:#fff; border-radius:16px; box-shadow:0 8px 28px rgba(2,6,23,.08); border:1px solid #eef2f7; }
        .method-badge { font-size: .85rem; }
        .status-success { color: #198754; font-weight:700; }
        .status-fail { color: #dc3545; font-weight:700; }
        .status-pending { color: #fd7e14; font-weight:700; }
        .log-item { font-size:.9rem; color:#64748b; }
<?php $extraCss = ob_get_clean();
include __DIR__ . '/_layout/header.php'; ?>
<div class="container py-4 py-lg-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="mb-0"><i class="bi bi-clock-history me-2"></i>Lich su thanh toan online</h2>
        <div class="d-flex gap-2">
            <a href="index.php?act=khachHang/hoaDon" class="btn btn-outline-primary"><i class="bi bi-receipt me-1"></i>Hoa don</a>
            <a href="index.php?act=khachHang/dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Trang chu</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="panel p-3 p-lg-4">
        <?php if (empty($paymentRows)): ?>
            <div class="text-muted py-3"><i class="bi bi-info-circle me-1"></i>Chua co giao dich thanh toan online nao.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Ma GD</th>
                            <th>Booking</th>
                            <th>Tour</th>
                            <th>Phuong thuc</th>
                            <th>So tien</th>
                            <th>Trang thai</th>
                            <th>Thoi gian</th>
                            <th>Log gan nhat</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($paymentRows as $row): ?>
                        <?php $pid = (int)($row['payment_id'] ?? 0); ?>
                        <tr>
                            <td><b>#<?php echo $pid; ?></b></td>
                            <td>#<?php echo (int)($row['booking_id'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($row['ten_tour'] ?? 'N/A'); ?></td>
                            <td><span class="badge text-bg-light method-badge"><?php echo htmlspecialchars($row['payment_method'] ?? ''); ?></span></td>
                            <td><b><?php echo number_format((float)($row['amount'] ?? 0)); ?> VND</b></td>
                            <td>
                                <?php $st = (string)($row['status'] ?? ''); ?>
                                <?php if ($st === 'ThanhCong'): ?>
                                    <span class="status-success">Thanh cong</span>
                                <?php elseif ($st === 'ThatBai'): ?>
                                    <span class="status-fail">That bai</span>
                                <?php else: ?>
                                    <span class="status-pending">Dang xu ly</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo !empty($row['payment_date']) ? date('d/m/Y H:i', strtotime($row['payment_date'])) : ''; ?></td>
                            <td>
                                <?php if (!empty($paymentLogsMap[$pid])): ?>
                                    <?php foreach ($paymentLogsMap[$pid] as $log): ?>
                                        <div class="log-item">- <?php echo htmlspecialchars($log['action'] ?? ''); ?> (<?php echo !empty($log['log_time']) ? date('d/m H:i', strtotime($log['log_time'])) : ''; ?>)</div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if (function_exists('realtimeWebSocketEnabled') && realtimeWebSocketEnabled() && !empty($_SESSION['user_id'])): ?>
<?php
$_lstWsToken = buildRealtimeAuthToken((int)$_SESSION['user_id'], 'KhachHang');
$_lstWsUrl   = realtimeWebSocketPublicUrl() . '?token=' . rawurlencode($_lstWsToken);
?>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
(function() {
    var wsUrl = <?php echo json_encode($_lstWsUrl, JSON_UNESCAPED_UNICODE); ?>;
    var ws = null;
    var reconnectTimer = null;
    var toastEl = null;

    function showToast() {
        if (toastEl) return;
        toastEl = document.createElement('div');
        toastEl.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1e293b;border:1px solid #38bdf8;color:#38bdf8;padding:12px 20px;border-radius:8px;z-index:9999;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,.4);display:flex;gap:12px;align-items:center';

        var msgSpan = document.createElement('span');
        msgSpan.textContent = '🔔 Có thông báo mới';
        toastEl.appendChild(msgSpan);

        var reloadBtn = document.createElement('button');
        reloadBtn.textContent = 'Tải lại';
        reloadBtn.style.cssText = 'background:#38bdf8;color:#000;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:13px;font-weight:600';
        reloadBtn.onclick = function() { window.location.reload(); };
        toastEl.appendChild(reloadBtn);

        var markBtn = document.createElement('button');
        markBtn.textContent = 'Đã đọc';
        markBtn.style.cssText = 'background:transparent;color:#94a3b8;border:1px solid #94a3b8;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:13px';
        markBtn.onclick = function() {
            fetch('index.php?act=khachHang/markAllNotificationsRead', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).catch(function() {});
            if (toastEl && toastEl.parentNode) { toastEl.parentNode.removeChild(toastEl); }
            toastEl = null;
        };
        toastEl.appendChild(markBtn);

        document.body.appendChild(toastEl);
    }

    function connect() {
        if (ws) return;
        ws = new WebSocket(wsUrl);
        ws.onmessage = function(e) {
            try {
                var packet = JSON.parse(e.data);
                if (packet.type === 'ping') {
                    ws.send(JSON.stringify({ type: 'pong', payload: { ts: packet.payload && packet.payload.ts } }));
                    return;
                }
                if (packet.type !== 'notification' || !packet.payload || packet.payload.success !== true) return;
                if (Number(packet.payload.unread || 0) > 0) {
                    showToast();
                }
            } catch (err) {}
        };
        ws.onclose = function() {
            ws = null;
            reconnectTimer = window.setTimeout(connect, 5000);
        };
        ws.onerror = function() { ws.close(); };
    }

    connect();
})();
</script>
<?php endif; ?>
<?php include __DIR__ . '/_layout/footer.php'; ?>
