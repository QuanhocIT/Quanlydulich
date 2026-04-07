<?php
$pageTitle = 'Quản lý thanh toán';
$currentPage = 'payments';

ob_start();
?>
<div class="aventura-content">
    <style>
        .payments-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .payments-count {
            color: var(--text-muted);
            font-size: 14px;
        }
        .payments-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .summary-card {
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 10px;
        }
        .summary-card .label {
            color: var(--text-muted);
            font-size: 12px;
            margin-bottom: 4px;
        }
        .summary-card .value {
            font-size: 20px;
            font-weight: 700;
        }
        .aventura-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-success {
            color: #0f5132;
            background: #d1e7dd;
        }
        .badge-warning {
            color: #664d03;
            background: #fff3cd;
        }
        .badge-danger {
            color: #842029;
            background: #f8d7da;
        }
        @media (max-width: 1100px) {
            .summary-grid { grid-template-columns: repeat(3, minmax(120px, 1fr)); }
        }
        @media (max-width: 680px) {
            .summary-grid { grid-template-columns: repeat(2, minmax(110px, 1fr)); }
        }
    </style>

    <div class="aventura-header">
        <h1 class="aventura-title"><i class="bi bi-credit-card-2-front"></i> Danh sách thanh toán</h1>
    </div>

    <div class="payments-toolbar">
        <div class="payments-count">Tổng giao dịch: <b><?php echo count($payments ?? []); ?></b></div>
        <div class="payments-actions">
            <a href="index.php?act=admin/paymentReconcile" class="aventura-btn aventura-btn-gold"><i class="bi bi-clipboard-check"></i> Đối soát thanh toán</a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card"><div class="label">TaoMoi</div><div class="value"><?php echo (int)($statusSummary['TaoMoi'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">DangXuLy</div><div class="value"><?php echo (int)($statusSummary['DangXuLy'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">ThanhCong</div><div class="value"><?php echo (int)($statusSummary['ThanhCong'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">ThatBai</div><div class="value"><?php echo (int)($statusSummary['ThatBai'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">HetHan</div><div class="value"><?php echo (int)($statusSummary['HetHan'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">DaDoiSoat</div><div class="value"><?php echo (int)($statusSummary['DaDoiSoat'] ?? 0); ?></div></div>
    </div>

    <div class="aventura-table-wrapper">
        <table class="aventura-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Booking</th>
                    <th>Số tiền</th>
                    <th>Phương thức</th>
                    <th>Ngày thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--text-muted)">Chưa có dữ liệu thanh toán.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <?php
                            $status = (string)($p['status'] ?? '');
                            $paymentDateText = '';
                            if (!empty($p['payment_date'])) {
                                $timestamp = strtotime((string)$p['payment_date']);
                                $paymentDateText = $timestamp ? date('Y-m-d H:i:s', $timestamp) : (string)$p['payment_date'];
                            }
                            $badgeClass = 'badge-warning';
                            $badgeText = 'Đang xử lý';
                            $badgeIcon = 'bi-hourglass-split';
                            if ($status === 'TaoMoi') {
                                $badgeClass = 'badge-warning';
                                $badgeText = 'Tạo mới';
                                $badgeIcon = 'bi-plus-circle';
                            } elseif ($status === 'ThanhCong') {
                                $badgeClass = 'badge-success';
                                $badgeText = 'Thành công';
                                $badgeIcon = 'bi-check-circle';
                            } elseif ($status === 'DaDoiSoat') {
                                $badgeClass = 'badge-success';
                                $badgeText = 'Đã đối soát';
                                $badgeIcon = 'bi-patch-check';
                            } elseif ($status === 'ThatBai') {
                                $badgeClass = 'badge-danger';
                                $badgeText = 'Thất bại';
                                $badgeIcon = 'bi-x-circle';
                            } elseif ($status === 'HetHan') {
                                $badgeClass = 'badge-danger';
                                $badgeText = 'Hết hạn';
                                $badgeIcon = 'bi-clock-history';
                            }
                        ?>
                        <tr>
                            <td><?php echo (int)$p['payment_id']; ?></td>
                            <td>#<?php echo (int)$p['booking_id']; ?></td>
                            <td><?php echo number_format((float)($p['amount'] ?? 0)); ?>₫</td>
                            <td><?php echo htmlspecialchars((string)($p['payment_method'] ?? 'N/A')); ?></td>
                            <td><?php echo htmlspecialchars($paymentDateText); ?></td>
                            <td>
                                <span class="aventura-badge <?php echo $badgeClass; ?>">
                                    <i class="bi <?php echo $badgeIcon; ?>"></i>
                                    <?php echo $badgeText; ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?act=admin/show_payment&id=<?php echo (int)$p['payment_id']; ?>" class="aventura-btn-sm aventura-btn-outline">
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
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
