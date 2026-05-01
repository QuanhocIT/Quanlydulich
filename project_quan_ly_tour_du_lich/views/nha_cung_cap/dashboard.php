<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Nhà cung cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/supplier.css">
</head>
<body class="supplier-body">
    <?php
        $pendingCount = count($dichVuChoXacNhan ?? []);
        $confirmedCount = count($dichVuDaXacNhan ?? []);
        $serviceCount = (int)($congNo['so_dich_vu'] ?? 0);
        $debtValue = (float)($congNo['tong_cong_no'] ?? 0);

        $historyStatusCounts = [
            'Chờ xác nhận' => 0,
            'Đã xác nhận' => 0,
            'Từ chối / Hủy' => 0,
            'Hoàn tất' => 0,
        ];

        $recentActivityMap = [];

        foreach (($lichSu ?? []) as $item) {
            $status = $item['trang_thai'] ?? '';
            if ($status === 'ChoXacNhan') {
                $historyStatusCounts['Chờ xác nhận']++;
            } elseif ($status === 'DaXacNhan') {
                $historyStatusCounts['Đã xác nhận']++;
            } elseif ($status === 'HoanTat') {
                $historyStatusCounts['Hoàn tất']++;
            } else {
                $historyStatusCounts['Từ chối / Hủy']++;
            }

            $dateKey = !empty($item['created_at']) ? date('d/m', strtotime($item['created_at'])) : 'Khác';
            if (!isset($recentActivityMap[$dateKey])) {
                $recentActivityMap[$dateKey] = 0;
            }
            $recentActivityMap[$dateKey]++;
        }

        if (empty($recentActivityMap)) {
            $recentActivityMap[date('d/m')] = 0;
        }
    ?>

    <div class="container-fluid supplier-shell">
        <section class="supplier-page-header">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="supplier-eyebrow"><i class="bi bi-stars"></i> Không gian nhà cung cấp</span>
                    <h1 class="supplier-page-title">Tổng quan hợp tác</h1>
                    <p class="supplier-page-subtitle">
                        Theo dõi yêu cầu mới, tiến độ phản hồi và các đầu việc quan trọng trong một giao diện tinh gọn, đồng nhất giữa các trang.
                    </p>
                    <div class="supplier-quick-info">
                        <span class="supplier-chip"><i class="bi bi-buildings"></i> <?php echo htmlspecialchars($nhaCungCap['ten_don_vi'] ?? 'Nhà cung cấp'); ?></span>
                        <span class="supplier-chip"><i class="bi bi-activity"></i> Cập nhật theo dữ liệu hiện tại</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="supplier-header-actions">
                        <a href="index.php?act=nhaCungCap/baoGia&trang_thai=ChoXacNhan" class="btn btn-primary">
                            <i class="bi bi-send-check"></i> Xử lý báo giá
                        </a>
                        <a href="index.php?act=nhaCungCap/dichVu" class="btn btn-outline-secondary">
                            <i class="bi bi-grid"></i> Quản lý dịch vụ
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show supplier-alert" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php
            $currentTab = 'dashboard';
            include __DIR__ . '/partials/main_nav.php';
        ?>

        <div class="supplier-stats-grid">
            <div class="supplier-stat-card warning">
                <div class="supplier-stat-label">Chờ xác nhận</div>
                <p class="supplier-stat-value"><?php echo $pendingCount; ?></p>
                <div class="supplier-stat-meta">Các dịch vụ đang cần bạn phản hồi.</div>
            </div>
            <div class="supplier-stat-card success">
                <div class="supplier-stat-label">Đã xác nhận</div>
                <p class="supplier-stat-value"><?php echo $confirmedCount; ?></p>
                <div class="supplier-stat-meta">Những hạng mục đã chốt với điều hành.</div>
            </div>
            <div class="supplier-stat-card info">
                <div class="supplier-stat-label">Tổng công nợ</div>
                <p class="supplier-stat-value"><?php echo number_format($debtValue, 0, ',', '.'); ?>đ</p>
                <div class="supplier-stat-meta">Giá trị phát sinh từ dịch vụ đã xác nhận.</div>
            </div>
            <div class="supplier-stat-card primary">
                <div class="supplier-stat-label">Dịch vụ</div>
                <p class="supplier-stat-value"><?php echo $serviceCount; ?></p>
                <div class="supplier-stat-meta">Số đầu mục bạn đang tham gia phục vụ.</div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="card supplier-section-card supplier-chart-card">
                    <div class="card-header">
                        <h5 class="supplier-card-title"><i class="bi bi-pie-chart"></i> Cơ cấu trạng thái</h5>
                        <div class="supplier-card-subtitle">Phân bổ nhanh các đầu việc bạn đang theo dõi.</div>
                    </div>
                    <div class="card-body">
                        <div class="supplier-chart-wrap">
                            <canvas id="supplierStatusChart"></canvas>
                        </div>
                        <div class="supplier-chart-note">
                            Tập trung vào nhóm chờ xác nhận để giữ nhịp phản hồi ổn định với điều hành.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card supplier-section-card supplier-chart-card">
                    <div class="card-header">
                        <h5 class="supplier-card-title"><i class="bi bi-bar-chart"></i> Tỷ trọng tổng quan</h5>
                        <div class="supplier-card-subtitle">So sánh khối lượng công việc và giá trị hiện tại.</div>
                    </div>
                    <div class="card-body">
                        <div class="supplier-chart-wrap">
                            <canvas id="supplierOverviewChart"></canvas>
                        </div>
                        <div class="supplier-chart-note">
                            Giúp nhìn nhanh giữa số lượng dịch vụ đang chạy và giá trị công nợ hiện có.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card supplier-section-card supplier-chart-card">
                    <div class="card-header">
                        <h5 class="supplier-card-title"><i class="bi bi-activity"></i> Nhịp hoạt động gần đây</h5>
                        <div class="supplier-card-subtitle">Số bản ghi hợp tác phát sinh theo mốc thời gian.</div>
                    </div>
                    <div class="card-body">
                        <div class="supplier-chart-wrap">
                            <canvas id="supplierTimelineChart"></canvas>
                        </div>
                        <div class="supplier-chart-note">
                            Quan sát thời điểm phát sinh nhiều cập nhật để chủ động phân bổ thời gian xử lý.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card supplier-section-card">
                    <div class="card-header">
                        <h5 class="supplier-card-title"><i class="bi bi-bell"></i> Dịch vụ chờ xác nhận</h5>
                        <div class="supplier-card-subtitle">Ưu tiên những yêu cầu mới để không bỏ sót nhịp phối hợp.</div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dichVuChoXacNhan)): ?>
                            <div class="supplier-empty-state">
                                <div class="supplier-empty-icon"><i class="bi bi-inbox"></i></div>
                                <p class="mb-0">Hiện chưa có dịch vụ nào đang chờ xác nhận.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table supplier-table">
                                    <thead>
                                        <tr>
                                            <th>Tour</th>
                                            <th>Dịch vụ</th>
                                            <th>Ngày</th>
                                            <th>Giá</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($dichVuChoXacNhan, 0, 5) as $dv): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($dv['ten_tour'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="supplier-badge-soft info"><?php echo htmlspecialchars($dv['loai_dich_vu']); ?></span>
                                                <br><small><?php echo htmlspecialchars($dv['ten_dich_vu']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($dv['ngay_bat_dau']): ?>
                                                    <?php echo date('d/m/Y', strtotime($dv['ngay_bat_dau'])); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($dv['gia_tien']): ?>
                                                    <?php echo number_format($dv['gia_tien'], 0, ',', '.'); ?>đ
                                                <?php else: ?>
                                                    <span class="text-muted">Chưa có giá</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="index.php?act=nhaCungCap/baoGia&trang_thai=ChoXacNhan" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> Xem
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="index.php?act=nhaCungCap/baoGia&trang_thai=ChoXacNhan" class="btn btn-outline-primary">
                                    Xem tất cả <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card supplier-section-card h-100">
                    <div class="card-header">
                        <h5 class="supplier-card-title"><i class="bi bi-clock-history"></i> Lịch sử gần đây</h5>
                        <div class="supplier-card-subtitle">Những cập nhật mới nhất trong quá trình hợp tác.</div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lichSu)): ?>
                            <div class="supplier-empty-state py-4">
                                <div class="supplier-empty-icon"><i class="bi bi-inbox"></i></div>
                                <p class="mb-0 small">Chưa có lịch sử hoạt động.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($lichSu, 0, 5) as $ls): ?>
                            <div class="supplier-list-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($ls['ten_tour'] ?? 'N/A'); ?></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($ls['ten_dich_vu']); ?>
                                            <br>
                                            <?php
                                                $statusClass = 'warning';
                                                if (($ls['trang_thai'] ?? '') === 'DaXacNhan') {
                                                    $statusClass = 'success';
                                                } elseif (($ls['trang_thai'] ?? '') === 'HoanTat') {
                                                    $statusClass = 'info';
                                                } elseif (in_array(($ls['trang_thai'] ?? ''), ['TuChoi', 'Huy'], true)) {
                                                    $statusClass = 'danger';
                                                }

                                                $statusTextMap = [
                                                    'ChoXacNhan' => 'Chờ xác nhận',
                                                    'DaXacNhan' => 'Đã xác nhận',
                                                    'TuChoi' => 'Từ chối',
                                                    'Huy' => 'Hủy',
                                                    'HoanTat' => 'Hoàn tất'
                                                ];
                                            ?>
                                            <span class="supplier-badge-soft <?php echo $statusClass; ?>">
                                                <?php echo $statusTextMap[$ls['trang_thai']] ?? ($ls['trang_thai'] ?? 'Khác'); ?>
                                            </span>
                                        </small>
                                    </div>
                                    <?php if (!empty($ls['gia_tien'])): ?>
                                    <div class="text-end">
                                        <strong class="text-success"><?php echo number_format($ls['gia_tien'], 0, ',', '.'); ?>đ</strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <div class="text-center mt-3">
                                <a href="index.php?act=nhaCungCap/hopDong" class="btn btn-outline-secondary">
                                    Xem lịch sử <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        Chart.defaults.color = '#776759';
        Chart.defaults.borderColor = 'rgba(118, 96, 72, 0.12)';
        Chart.defaults.font.family = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';

        const supplierStatusCtx = document.getElementById('supplierStatusChart');
        if (supplierStatusCtx) {
            new Chart(supplierStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_keys($historyStatusCounts), JSON_UNESCAPED_UNICODE); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($historyStatusCounts), JSON_UNESCAPED_UNICODE); ?>,
                        backgroundColor: ['#d2a162', '#3d8775', '#b56a62', '#688eb0'],
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        const supplierOverviewCtx = document.getElementById('supplierOverviewChart');
        if (supplierOverviewCtx) {
            new Chart(supplierOverviewCtx, {
                type: 'bar',
                data: {
                    labels: ['Chờ xác nhận', 'Đã xác nhận', 'Dịch vụ', 'Công nợ / triệu'],
                    datasets: [{
                        label: 'Tổng quan',
                        data: [
                            <?php echo (int)$pendingCount; ?>,
                            <?php echo (int)$confirmedCount; ?>,
                            <?php echo (int)$serviceCount; ?>,
                            <?php echo round($debtValue / 1000000, 1); ?>
                        ],
                        backgroundColor: ['#d2a162', '#3d8775', '#b88357', '#688eb0'],
                        borderRadius: 12,
                        borderSkipped: false
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        const supplierTimelineCtx = document.getElementById('supplierTimelineChart');
        if (supplierTimelineCtx) {
            new Chart(supplierTimelineCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_keys($recentActivityMap), JSON_UNESCAPED_UNICODE); ?>,
                    datasets: [{
                        label: 'Bản ghi hợp tác',
                        data: <?php echo json_encode(array_values($recentActivityMap), JSON_UNESCAPED_UNICODE); ?>,
                        borderColor: '#1f6a62',
                        backgroundColor: 'rgba(31, 106, 98, 0.16)',
                        fill: true,
                        tension: 0.36,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#1f6a62'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    </script>
<?php if (function_exists('realtimeWebSocketEnabled') && realtimeWebSocketEnabled() && !empty($_SESSION['user_id'])): ?>
<?php
$_wsToken = buildRealtimeAuthToken((int)$_SESSION['user_id'], 'NhaCungCap');
$_wsUrl   = realtimeWebSocketPublicUrl() . '?token=' . rawurlencode($_wsToken);
?>
<script>
(function() {
    var wsUrl = <?php echo json_encode($_wsUrl, JSON_UNESCAPED_UNICODE); ?>;
    var prevPending = <?php echo (int)$pendingCount; ?>;
    var ws = null;
    var reconnectTimer = null;

    function showSupplierToast(msg) {
        var t = document.getElementById('supplierWsToast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'supplierWsToast';
            t.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1e293b;border:1px solid #fbbf24;color:#fbbf24;padding:12px 20px;border-radius:8px;z-index:9999;cursor:pointer;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,.4)';
            t.onclick = function() { window.location.reload(); };
            document.body.appendChild(t);
        }
        t.textContent = msg + ' — Nhấn để tải lại';
        t.style.display = 'block';
        window.setTimeout(function() { t.style.display = 'none'; }, 8000);
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
                var pending = Number(packet.payload.pending || 0);
                var statEl = document.querySelector('.supplier-stat-value');
                if (statEl) statEl.textContent = String(pending);
                if (pending > prevPending) {
                    showSupplierToast('Có ' + (pending - prevPending) + ' dịch vụ mới cần xác nhận');
                }
                prevPending = pending;
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
</body>
</html>
