<?php
$pageTitle = 'Quản lý hóa đơn';
$currentPage = 'invoices';
ob_start();

$statusLabels = [
    'DaThanhToan' => 'Đã thanh toán',
    'ChuaThanhToan' => 'Chưa thanh toán',
    'QuaHan' => 'Quá hạn',
];
?>

<style>
    .invoice-page-wrap {
        max-width: 1240px;
        margin: 0 auto;
        padding: 20px;
    }
    .invoice-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }
    .invoice-header {
        padding: 22px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .invoice-header h1 {
        margin: 0 0 6px;
        color: var(--text-light);
        font-size: 1.95rem;
    }
    .invoice-sub {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.95rem;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .stat-box {
        padding: 14px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(38, 38, 38, 0.52);
    }
    .stat-label {
        color: var(--text-muted);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 7px;
    }
    .stat-value {
        color: var(--text-light);
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0;
    }
    .invoice-filter {
        padding: 16px;
        margin-bottom: 16px;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 10px;
    }
    .filter-grid input,
    .filter-grid select {
        width: 100%;
        min-height: 42px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: rgba(28, 28, 28, 0.72);
        color: var(--text-light);
        padding: 8px 12px;
    }
    .filter-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    .btn-ui {
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 12px;
        color: var(--text-light);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.04);
        cursor: pointer;
    }
    .btn-ui-primary {
        border-color: rgba(77, 163, 255, 0.5);
        color: #4da3ff;
        background: rgba(13, 110, 253, 0.2);
    }
    .btn-ui-gold {
        border-color: rgba(212, 175, 55, 0.55);
        color: var(--accent-gold);
        background: rgba(212, 175, 55, 0.18);
    }
    .table-wrap {
        overflow-x: auto;
    }
    .invoice-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }
    .invoice-table th,
    .invoice-table td {
        padding: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--text-light);
        font-size: 0.92rem;
        vertical-align: top;
    }
    .invoice-table th {
        color: var(--text-muted);
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .amount-col {
        font-weight: 700;
        color: #8fd0ff;
        white-space: nowrap;
    }
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        padding: 5px 10px;
        font-size: 0.8rem;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .status-paid {
        color: #63c684;
        border-color: rgba(25, 135, 84, 0.45);
        background: rgba(25, 135, 84, 0.22);
    }
    .status-due {
        color: #ffc760;
        border-color: rgba(255, 193, 7, 0.45);
        background: rgba(255, 193, 7, 0.2);
    }
    .status-overdue {
        color: #ff8b9a;
        border-color: rgba(220, 53, 69, 0.45);
        background: rgba(220, 53, 69, 0.2);
    }
    .empty-state {
        text-align: center;
        padding: 40px 14px;
        color: var(--text-muted);
    }
    @media (max-width: 1000px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 700px) {
        .invoice-page-wrap {
            padding: 10px;
        }
        .stats-grid,
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="invoice-page-wrap">
    <div class="invoice-card invoice-header">
        <div>
            <h1><i class="bi bi-receipt" style="color: var(--accent-gold);"></i> Danh sách hóa đơn</h1>
            <p class="invoice-sub">Đồng bộ theo giao diện admin, hỗ trợ lọc nhanh theo trạng thái, ngày lập và từ khóa.</p>
        </div>
        <a href="#" class="btn-ui btn-ui-gold" title="Tính năng tạo hóa đơn sẽ mở rộng ở bước tiếp theo">
            <i class="bi bi-plus-circle"></i> Thêm hóa đơn
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">Tổng hóa đơn</div>
            <p class="stat-value"><?= number_format($stats['total'] ?? 0) ?></p>
        </div>
        <div class="stat-box">
            <div class="stat-label">Đã thanh toán</div>
            <p class="stat-value" style="color:#63c684;"><?= number_format($stats['paid'] ?? 0) ?></p>
        </div>
        <div class="stat-box">
            <div class="stat-label">Quá hạn</div>
            <p class="stat-value" style="color:#ff8b9a;"><?= number_format($stats['overdue'] ?? 0) ?></p>
        </div>
        <div class="stat-box">
            <div class="stat-label">Tổng giá trị</div>
            <p class="stat-value" style="font-size:1.12rem;"><?= number_format($stats['total_amount'] ?? 0) ?>₫</p>
        </div>
    </div>

    <div class="invoice-card invoice-filter">
        <form method="get" action="index.php">
            <input type="hidden" name="act" value="admin/invoices">
            <div class="filter-grid">
                <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Tìm theo mã hóa đơn, booking, khách hàng, ghi chú...">
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="DaThanhToan" <?= (($_GET['status'] ?? '') === 'DaThanhToan') ? 'selected' : '' ?>>Đã thanh toán</option>
                    <option value="ChuaThanhToan" <?= (($_GET['status'] ?? '') === 'ChuaThanhToan') ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option value="QuaHan" <?= (($_GET['status'] ?? '') === 'QuaHan') ? 'selected' : '' ?>>Quá hạn</option>
                </select>
                <input type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                <input type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-ui btn-ui-primary"><i class="bi bi-search"></i> Lọc dữ liệu</button>
                <a href="index.php?act=admin/invoices" class="btn-ui"><i class="bi bi-arrow-counterclockwise"></i> Đặt lại</a>
            </div>
        </form>
    </div>

    <div class="invoice-card">
        <div class="table-wrap">
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Booking</th>
                        <th>Khách hàng</th>
                        <th>Ngày lập</th>
                        <th>Hạn thanh toán</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="bi bi-inbox" style="font-size:1.4rem;display:block;margin-bottom:8px;"></i>
                            Chưa có hóa đơn phù hợp bộ lọc hiện tại.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                    <?php
                    $isPaid = ($inv['status'] ?? '') === 'DaThanhToan';
                    $isOverdue = !$isPaid && !empty($inv['due_date']) && $inv['due_date'] < date('Y-m-d');
                    $statusClass = $isPaid ? 'status-paid' : ($isOverdue ? 'status-overdue' : 'status-due');
                    $statusText = $isOverdue ? 'Quá hạn' : ($statusLabels[$inv['status'] ?? ''] ?? ($inv['status'] ?? 'Không rõ'));
                    ?>
                    <tr>
                        <td>#<?= (int)$inv['invoice_id'] ?></td>
                        <td><?= htmlspecialchars((string)($inv['booking_id'] ?? 'N/A')) ?></td>
                        <td><?= htmlspecialchars((string)($inv['customer_id'] ?? 'N/A')) ?></td>
                        <td><?= !empty($inv['issue_date']) ? date('d/m/Y', strtotime($inv['issue_date'])) : '-' ?></td>
                        <td><?= !empty($inv['due_date']) ? date('d/m/Y', strtotime($inv['due_date'])) : '-' ?></td>
                        <td class="amount-col"><?= number_format((float)($inv['total_amount'] ?? 0)) ?>₫</td>
                        <td><span class="badge-status <?= $statusClass ?>"><?= $statusText ?></span></td>
                        <td>
                            <a href="index.php?act=admin/show_invoice&id=<?= (int)$inv['invoice_id'] ?>" class="btn-ui" style="min-height:30px;padding:6px 10px;">
                                <i class="bi bi-eye"></i> Xem
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
