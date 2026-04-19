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

        body.page-payments .content-area {
            padding: 34px 48px 56px;
            background:
                radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
                radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
                linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
        }

        body.page-payments .aventura-content {
            max-width: 100%;
            padding: 0;
        }

        body.page-payments .aventura-header {
            min-height: 154px;
            padding: 28px 34px;
            margin-bottom: 24px;
            background:
                linear-gradient(100deg, rgba(28, 31, 33, 0.96) 0%, rgba(30, 42, 45, 0.94) 52%, rgba(119, 102, 45, 0.84) 100%),
                url("<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg");
            background-size: cover;
            background-position: center;
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 8px;
            box-shadow: 0 22px 60px rgba(0,0,0,0.28);
            position: relative;
            overflow: hidden;
        }

        body.page-payments .aventura-header::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
            pointer-events: none;
        }

        body.page-payments .aventura-title {
            position: relative;
            z-index: 2;
            color: #ffe082;
            font-size: 2rem;
            letter-spacing: 0;
        }

        body.page-payments .payments-toolbar {
            padding: 0;
            margin-bottom: 20px;
        }

        body.page-payments .payments-count {
            color: rgba(255,255,255,0.86);
            font-size: 15px;
        }

        body.page-payments .aventura-btn,
        body.page-payments .aventura-btn-sm,
        body.page-payments .aventura-btn-outline {
            border-radius: 8px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        body.page-payments .summary-grid {
            grid-template-columns: repeat(6, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        body.page-payments .summary-card {
            min-height: 106px;
            padding: 20px;
            border-radius: 8px;
            background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.025));
            border-color: rgba(255,255,255,0.1);
            border-left: 3px solid var(--accent-gold);
            box-shadow: 0 14px 32px rgba(0,0,0,0.18);
        }

        body.page-payments .summary-card .value {
            font-size: 2rem;
            line-height: 1;
        }

        body.page-payments .aventura-table-wrapper {
            border-radius: 8px;
            background: rgba(28, 30, 31, 0.78);
            border: 1px solid rgba(212, 175, 55, 0.22);
            box-shadow: 0 14px 36px rgba(0,0,0,0.18);
        }

        body.page-payments .aventura-table {
            min-width: 980px;
        }

        body.page-payments .aventura-table th {
            padding: 16px 20px;
            background: linear-gradient(90deg, rgba(212, 175, 55, 0.14), rgba(13, 202, 240, 0.06));
            white-space: nowrap;
        }

        body.page-payments .aventura-table td {
            padding: 18px 20px;
            vertical-align: middle;
        }

        body.page-payments .aventura-badge {
            border-radius: 8px;
            min-height: 30px;
        }

        @media (max-width: 1300px) {
            body.page-payments .summary-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }
        }

        @media (max-width: 900px) {
            body.page-payments .content-area {
                padding: 24px 18px 42px;
            }

            body.page-payments .aventura-header {
                padding: 24px;
            }
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
