<?php
$pageTitle = 'Chi tiết hóa đơn';
$currentPage = 'invoices';
ob_start();

$invoice = $invoice ?? null;
$items = $items ?? [];

if (!$invoice) {
    ?>
    <div style="padding:20px;">
        <div style="max-width:900px;margin:0 auto;background:rgba(45,45,45,.5);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:24px;color:var(--text-light);">
            <h2 style="margin-top:0;"><i class="bi bi-exclamation-circle" style="color:#ff8b9a;"></i> Không tìm thấy hóa đơn</h2>
            <p style="color:var(--text-muted);">Hóa đơn không tồn tại hoặc đã bị xóa.</p>
            <a href="index.php?act=admin/invoices" style="display:inline-flex;align-items:center;gap:6px;color:var(--accent-gold);text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách hóa đơn
            </a>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    require __DIR__ . '/../../layouts/aventura.php';
    return;
}

$isPaid = ($invoice['status'] ?? '') === 'DaThanhToan';
$isOverdue = !$isPaid && !empty($invoice['due_date']) && $invoice['due_date'] < date('Y-m-d');
$statusText = $isOverdue ? 'Quá hạn' : (($invoice['status'] ?? '') === 'DaThanhToan' ? 'Đã thanh toán' : 'Chưa thanh toán');
$statusClass = $isPaid ? 'status-paid' : ($isOverdue ? 'status-overdue' : 'status-due');

$calculatedTotal = 0;
foreach ($items as $it) {
    $calculatedTotal += (float)($it['amount'] ?? 0);
}
?>

<style>
    .invoice-detail-wrap {
        max-width: 1120px;
        margin: 0 auto;
        padding: 20px;
    }
    .invoice-block {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }
    .invoice-head {
        padding: 20px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .invoice-head h1 {
        margin: 0 0 5px;
        color: var(--text-light);
        font-size: 1.85rem;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.86rem;
        border: 1px solid transparent;
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
    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    .info-box {
        padding: 14px;
        border-radius: 9px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(35, 35, 35, 0.5);
    }
    .info-label {
        color: var(--text-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }
    .info-value {
        color: var(--text-light);
        font-size: 1rem;
        font-weight: 600;
    }
    .line-items {
        padding: 16px;
    }
    .line-items h3 {
        margin: 0 0 12px;
        color: var(--text-light);
        font-size: 1.1rem;
    }
    .item-table-wrap {
        overflow-x: auto;
    }
    .item-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 720px;
    }
    .item-table th,
    .item-table td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 11px;
        color: var(--text-light);
        font-size: 0.92rem;
    }
    .item-table th {
        color: var(--text-muted);
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .num-col {
        text-align: right;
        white-space: nowrap;
    }
    .summary-row {
        margin-top: 12px;
        display: flex;
        justify-content: flex-end;
    }
    .summary-box {
        min-width: 280px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 12px;
        background: rgba(38, 38, 38, 0.5);
    }
    .summary-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text-light);
        margin-bottom: 8px;
    }
    .summary-line:last-child {
        margin-bottom: 0;
        padding-top: 8px;
        border-top: 1px dashed rgba(255, 255, 255, 0.2);
        font-weight: 700;
    }
    .action-row {
        margin-top: 14px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
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
    @media (max-width: 900px) {
        .invoice-detail-wrap {
            padding: 10px;
        }
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="invoice-detail-wrap">
    <div class="invoice-block invoice-head">
        <div>
            <h1><i class="bi bi-file-earmark-text" style="color:var(--accent-gold);"></i> Hóa đơn #<?= (int)$invoice['invoice_id'] ?></h1>
            <div style="color:var(--text-muted);font-size:.92rem;">Booking #<?= htmlspecialchars((string)($invoice['booking_id'] ?? 'N/A')) ?> • Khách hàng #<?= htmlspecialchars((string)($invoice['customer_id'] ?? 'N/A')) ?></div>
        </div>
        <span class="status-pill <?= $statusClass ?>"><?= $statusText ?></span>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Ngày lập</div>
            <div class="info-value"><?= !empty($invoice['issue_date']) ? date('d/m/Y', strtotime($invoice['issue_date'])) : '-' ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Hạn thanh toán</div>
            <div class="info-value"><?= !empty($invoice['due_date']) ? date('d/m/Y', strtotime($invoice['due_date'])) : '-' ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Tổng tiền hóa đơn</div>
            <div class="info-value" style="color:#8fd0ff;"><?= number_format((float)($invoice['total_amount'] ?? 0)) ?>₫</div>
        </div>
    </div>

    <div class="invoice-block line-items">
        <h3>Chi tiết các khoản</h3>
        <div class="item-table-wrap">
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Mô tả</th>
                        <th class="num-col">Số lượng</th>
                        <th class="num-col">Đơn giá</th>
                        <th class="num-col">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;color:var(--text-muted);padding:20px;">Hóa đơn chưa có khoản mục chi tiết.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($item['description'] ?? '')) ?></td>
                                <td class="num-col"><?= number_format((float)($item['quantity'] ?? 0), 0) ?></td>
                                <td class="num-col"><?= number_format((float)($item['unit_price'] ?? 0)) ?>₫</td>
                                <td class="num-col"><?= number_format((float)($item['amount'] ?? 0)) ?>₫</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="summary-row">
            <div class="summary-box">
                <div class="summary-line">
                    <span>Tổng theo khoản mục</span>
                    <span><?= number_format($calculatedTotal) ?>₫</span>
                </div>
                <div class="summary-line">
                    <span>Tổng hóa đơn</span>
                    <span><?= number_format((float)($invoice['total_amount'] ?? 0)) ?>₫</span>
                </div>
            </div>
        </div>

        <?php if (!empty($invoice['note'])): ?>
            <div style="margin-top:10px;color:var(--text-muted);font-size:.92rem;">
                <strong style="color:var(--text-light);">Ghi chú:</strong> <?= htmlspecialchars((string)$invoice['note']) ?>
            </div>
        <?php endif; ?>

        <div class="action-row">
            <a href="index.php?act=admin/invoices" class="btn-ui"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
            <a href="index.php?act=invoice/exportPDF&id=<?= (int)$invoice['invoice_id'] ?>" class="btn-ui btn-ui-primary"><i class="bi bi-file-earmark-pdf"></i> Tải PDF</a>
            <a href="index.php?act=invoice/sendMail&id=<?= (int)$invoice['invoice_id'] ?>" class="btn-ui btn-ui-gold"><i class="bi bi-envelope"></i> Gửi email hóa đơn</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
