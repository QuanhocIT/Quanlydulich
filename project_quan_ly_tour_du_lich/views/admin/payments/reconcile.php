<?php
$pageTitle = 'Đối soát thanh toán';
$currentPage = 'payments';

ob_start();
?>
<div class="aventura-content">
    <style>
        .reconcile-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .reconcile-form {
            display: grid;
            grid-template-columns: repeat(5, minmax(140px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .reconcile-form .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .reconcile-form label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }
        .reconcile-form input,
        .reconcile-form select {
            border: 1px solid var(--border-color);
            background: rgba(255,255,255,.02);
            color: var(--text-color);
            border-radius: 10px;
            padding: 8px 10px;
        }
        .reconcile-actions {
            display: flex;
            gap: 8px;
            align-items: end;
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
        .value.ok { color: #22c55e; }
        .value.warn { color: #f59e0b; }
        .row-warning { background: rgba(245, 158, 11, .08); }
        .row-ok { background: rgba(34, 197, 94, .06); }
        .issues-note { font-size: 12px; color: #f59e0b; }
        .flash {
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .flash-success { background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.4); }
        .flash-error { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.4); }
        @media (max-width: 1100px) {
            .reconcile-form { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
            .summary-grid { grid-template-columns: repeat(3, minmax(120px, 1fr)); }
        }
        @media (max-width: 680px) {
            .reconcile-form { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: repeat(2, minmax(110px, 1fr)); }
        }
    </style>

    <div class="aventura-header">
        <h1 class="aventura-title"><i class="bi bi-clipboard-check"></i> Đối soát thanh toán online</h1>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="flash flash-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="flash flash-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="reconcile-tools">
        <a href="index.php?act=admin/payments" class="aventura-btn aventura-btn-outline"><i class="bi bi-arrow-left"></i> Danh sách thanh toán</a>
    </div>

    <form method="GET" action="index.php" class="reconcile-form">
        <input type="hidden" name="act" value="admin/paymentReconcile">
        <div class="field">
            <label>Từ ngày</label>
            <input type="date" name="from_date" value="<?php echo htmlspecialchars((string)($filters['from_date'] ?? '')); ?>">
        </div>
        <div class="field">
            <label>Đến ngày</label>
            <input type="date" name="to_date" value="<?php echo htmlspecialchars((string)($filters['to_date'] ?? '')); ?>">
        </div>
        <div class="field">
            <label>Trạng thái payment</label>
            <select name="payment_status">
                <option value="">Tất cả</option>
                <option value="TaoMoi" <?php echo (($filters['payment_status'] ?? '') === 'TaoMoi') ? 'selected' : ''; ?>>TaoMoi</option>
                <option value="DangXuLy" <?php echo (($filters['payment_status'] ?? '') === 'DangXuLy') ? 'selected' : ''; ?>>DangXuLy</option>
                <option value="ThanhCong" <?php echo (($filters['payment_status'] ?? '') === 'ThanhCong') ? 'selected' : ''; ?>>ThanhCong</option>
                <option value="ThatBai" <?php echo (($filters['payment_status'] ?? '') === 'ThatBai') ? 'selected' : ''; ?>>ThatBai</option>
                <option value="HetHan" <?php echo (($filters['payment_status'] ?? '') === 'HetHan') ? 'selected' : ''; ?>>HetHan</option>
                <option value="DaDoiSoat" <?php echo (($filters['payment_status'] ?? '') === 'DaDoiSoat') ? 'selected' : ''; ?>>DaDoiSoat</option>
            </select>
        </div>
        <div class="field">
            <label>Kết quả đối soát</label>
            <select name="reconcile_state">
                <option value="">Tất cả</option>
                <option value="OK" <?php echo (($filters['reconcile_state'] ?? '') === 'OK') ? 'selected' : ''; ?>>OK</option>
                <option value="WARNING" <?php echo (($filters['reconcile_state'] ?? '') === 'WARNING') ? 'selected' : ''; ?>>WARNING</option>
            </select>
        </div>
        <div class="reconcile-actions">
            <button type="submit" class="aventura-btn aventura-btn-gold"><i class="bi bi-funnel"></i> Lọc</button>
            <a href="index.php?act=admin/paymentReconcile" class="aventura-btn aventura-btn-outline"><i class="bi bi-arrow-counterclockwise"></i> Xóa lọc</a>
        </div>
    </form>

    <div class="summary-grid">
        <div class="summary-card"><div class="label">Tổng bản ghi</div><div class="value"><?php echo (int)($summary['total'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">OK</div><div class="value ok"><?php echo (int)($summary['ok'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Cần kiểm tra</div><div class="value warn"><?php echo (int)($summary['warning'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Thiếu thu</div><div class="value"><?php echo (int)($summary['thieu_thu'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Thừa thu</div><div class="value"><?php echo (int)($summary['thua_thu'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Lệch tiền</div><div class="value"><?php echo (int)($summary['lech_tien'] ?? 0); ?></div></div>
    </div>

    <div class="summary-grid" style="margin-top:-4px;">
        <div class="summary-card"><div class="label">Bao cao ngay</div><div class="value"><?php echo htmlspecialchars((string)($dailyMismatchReport['date'] ?? date('Y-m-d'))); ?></div></div>
        <div class="summary-card"><div class="label">Tong payment/ngay</div><div class="value"><?php echo (int)($dailyMismatchReport['total'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Canh bao/ngay</div><div class="value warn"><?php echo (int)($dailyMismatchReport['warning'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Thieu thu/ngay</div><div class="value"><?php echo (int)($dailyMismatchReport['thieu_thu'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Thua thu/ngay</div><div class="value"><?php echo (int)($dailyMismatchReport['thua_thu'] ?? 0); ?></div></div>
        <div class="summary-card"><div class="label">Lech tien/ngay</div><div class="value"><?php echo (int)($dailyMismatchReport['lech_tien'] ?? 0); ?></div></div>
    </div>

    <div class="aventura-table-wrapper">
        <table class="aventura-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Booking</th>
                    <th>Ngày</th>
                    <th>Status</th>
                    <th>Số tiền payment</th>
                    <th>Tổng thu tài chính</th>
                    <th>Kết quả đối soát</th>
                    <th>Chi tiết</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reconcileRows)): ?>
                    <tr><td colspan="9" style="text-align:center;color:var(--text-muted)">Không có dữ liệu đối soát.</td></tr>
                <?php else: ?>
                    <?php foreach ($reconcileRows as $row): ?>
                        <?php $isWarning = (($row['reconcile_state'] ?? 'OK') !== 'OK'); ?>
                        <tr class="<?php echo $isWarning ? 'row-warning' : 'row-ok'; ?>">
                            <td><?php echo (int)$row['payment_id']; ?></td>
                            <td>#<?php echo (int)$row['booking_id']; ?></td>
                            <td><?php echo htmlspecialchars((string)($row['payment_date'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($row['status'] ?? '')); ?></td>
                            <td><?php echo number_format((float)($row['amount'] ?? 0)); ?> VND</td>
                            <td><?php echo number_format((float)($row['finance_total'] ?? 0)); ?> VND</td>
                            <td>
                                <?php echo htmlspecialchars((string)($row['reconcile_state'] ?? 'OK')); ?>
                                <?php if (!empty($row['issues'])): ?>
                                    <div class="issues-note"><?php echo htmlspecialchars(implode(' | ', $row['issues'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?act=admin/show_payment&id=<?php echo (int)$row['payment_id']; ?>" class="aventura-btn-sm aventura-btn-outline"><i class="bi bi-eye"></i> Xem</a>
                            </td>
                            <td>
                                <?php if (!empty($row['can_repair_missing_finance'])): ?>
                                    <form method="POST" action="index.php?act=admin/paymentReconcile&from_date=<?php echo urlencode((string)($filters['from_date'] ?? '')); ?>&to_date=<?php echo urlencode((string)($filters['to_date'] ?? '')); ?>&payment_status=<?php echo urlencode((string)($filters['payment_status'] ?? '')); ?>&reconcile_state=<?php echo urlencode((string)($filters['reconcile_state'] ?? '')); ?>" style="margin:0;">
                                        <?php echo csrfField('payment_reconcile_repair'); ?>
                                        <input type="hidden" name="repair_payment_id" value="<?php echo (int)$row['payment_id']; ?>">
                                        <input type="text" name="repair_reason" maxlength="500" required placeholder="Ly do sua loi (toi thieu 10 ky tu)" style="width:230px;margin-right:6px;">
                                        <button type="submit" class="aventura-btn-sm aventura-btn-gold" onclick="return confirm('Tạo bút toán thu bổ sung cho payment #<?php echo (int)$row['payment_id']; ?>?');">Tạo bút toán thu</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color:var(--text-muted)">-</span>
                                <?php endif; ?>
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
