<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lich su thanh toan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f5f7fa; }
        .panel { background:#fff; border-radius:16px; box-shadow:0 8px 28px rgba(2,6,23,.08); border:1px solid #eef2f7; }
        .method-badge { font-size: .85rem; }
        .status-success { color: #198754; font-weight:700; }
        .status-fail { color: #dc3545; font-weight:700; }
        .status-pending { color: #fd7e14; font-weight:700; }
        .log-item { font-size:.9rem; color:#64748b; }
    </style>
</head>
<body>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
