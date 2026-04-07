  <?php
$pageTitle = 'Chi tiết thanh toán';
$currentPage = 'payments';

ob_start();
?>
<div class="aventura-content">
    <style>
        .payment-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 14px;
            margin-bottom: 16px;
        }
        .payment-card {
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 14px;
        }
        .payment-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: var(--accent-gold);
        }
        .kv {
            display: grid;
            grid-template-columns: 130px 1fr;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .kv .k { color: var(--text-muted); }
        .kv .v { font-weight: 600; }
        .log-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .log-list li {
            padding: 10px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            margin-bottom: 8px;
            background: rgba(255,255,255,.02);
        }
        .confirm-form .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 10px;
        }
        .confirm-form label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
        }
        .confirm-form input {
            border: 1px solid var(--border-color);
            background: rgba(255,255,255,.02);
            color: var(--text-color);
            border-radius: 10px;
            padding: 8px 10px;
        }
        .detail-actions {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
        }
        @media (max-width: 900px) {
            .payment-detail-grid { grid-template-columns: 1fr; }
            .kv { grid-template-columns: 1fr; gap: 4px; }
        }
    </style>

    <div class="aventura-header">
        <h1 class="aventura-title"><i class="bi bi-credit-card"></i> Chi tiết thanh toán #<?php echo (int)($payment['payment_id'] ?? 0); ?></h1>
    </div>

    <div class="detail-actions">
        <a href="index.php?act=admin/payments" class="aventura-btn aventura-btn-outline"><i class="bi bi-arrow-left"></i> Danh sách thanh toán</a>
        <a href="index.php?act=admin/paymentReconcile" class="aventura-btn aventura-btn-gold"><i class="bi bi-clipboard-check"></i> Đối soát</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="payment-card" style="border-color: rgba(25,135,84,.35); margin-bottom:14px;">
            <div style="color:#78e08f; font-weight:600;">✓ <?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="payment-card" style="border-color: rgba(220,53,69,.35); margin-bottom:14px;">
            <div style="color:#ffb4b4; font-weight:600;">⚠ <?php echo htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?></div>
        </div>
    <?php endif; ?>

    <?php $validationPayload = getValidationErrors(); ?>
    <?php $validationErrors = $validationPayload['errors'] ?? []; ?>

    <div class="payment-detail-grid">
        <div class="payment-card">
            <h3>Thông tin giao dịch</h3>
            <div class="kv"><div class="k">Booking</div><div class="v">#<?php echo (int)($payment['booking_id'] ?? 0); ?></div></div>
            <div class="kv"><div class="k">Số tiền</div><div class="v"><?php echo number_format((float)($payment['amount'] ?? 0)); ?>₫</div></div>
            <div class="kv"><div class="k">Phương thức</div><div class="v"><?php echo htmlspecialchars((string)($payment['payment_method'] ?? 'N/A')); ?></div></div>
            <div class="kv"><div class="k">Ngày thanh toán</div><div class="v"><?php echo htmlspecialchars((string)($payment['payment_date'] ?? '')); ?></div></div>
            <div class="kv"><div class="k">Trạng thái</div><div class="v"><?php echo htmlspecialchars((string)($payment['status'] ?? '')); ?></div></div>
            <div class="kv"><div class="k">Ghi chú</div><div class="v"><?php echo htmlspecialchars((string)($payment['note'] ?? '')); ?></div></div>
        </div>

        <?php if (!empty($paymentDetail)): ?>
            <div class="payment-card">
                <h3>Thông tin đối chiếu</h3>
                <div class="kv"><div class="k">Khách hàng</div><div class="v"><?php echo htmlspecialchars((string)($paymentDetail['ho_ten'] ?? 'N/A')); ?></div></div>
                <div class="kv"><div class="k">SĐT</div><div class="v"><?php echo htmlspecialchars((string)($paymentDetail['so_dien_thoai'] ?? 'N/A')); ?></div></div>
                <div class="kv"><div class="k">Email</div><div class="v"><?php echo htmlspecialchars((string)($paymentDetail['email'] ?? 'N/A')); ?></div></div>
                <div class="kv"><div class="k">Tour</div><div class="v"><?php echo htmlspecialchars((string)($paymentDetail['ten_tour'] ?? 'N/A')); ?></div></div>
                <div class="kv"><div class="k">Booking</div><div class="v">#<?php echo (int)($paymentDetail['booking_id'] ?? 0); ?></div></div>
                <div class="kv"><div class="k">Số tiền cần thu</div><div class="v"><?php echo number_format((float)($paymentDetail['amount'] ?? 0)); ?>₫</div></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="payment-card" style="margin-bottom:14px;">
        <h3>Lịch sử thao tác</h3>
        <ul class="log-list">
            <?php if (empty($logs)): ?>
                <li>Chưa có lịch sử thao tác.</li>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <li>
                        <b><?php echo htmlspecialchars((string)($log['log_time'] ?? '')); ?></b>
                        - <?php echo htmlspecialchars((string)($log['action'] ?? '')); ?>:
                        <?php echo htmlspecialchars((string)($log['note'] ?? '')); ?>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <?php if (($payment['status'] ?? '') === 'DangXuLy'): ?>
        <?php $isGatewayMethod = in_array(($payment['payment_method'] ?? ''), ['VNPay', 'Momo', 'Paypal'], true); ?>

        <?php if ($isGatewayMethod && defined('PAYMENT_MODE') && PAYMENT_MODE === 'vnpay' && defined('VNPAY_TMN_CODE') && VNPAY_TMN_CODE !== ''): ?>
        <div class="payment-card" style="margin-bottom:14px; border-color: rgba(255,193,7,.3);">
            <h3><i class="bi bi-search"></i> Kiểm tra trạng thái với VNPay</h3>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:10px;">
                Giao dịch chưa được cập nhật do callback không về. Nhấn để truy vấn trực tiếp VNPay và tự động cập nhật nếu thành công.
            </p>
            <form method="POST" action="index.php?act=admin/query_vnpay_status&id=<?php echo (int)$payment['payment_id']; ?>">
                <?php echo csrfField('payment_vnpay_query'); ?>
                <button type="submit" class="aventura-btn aventura-btn-gold" onclick="return confirm('Truy vấn VNPay để kiểm tra trạng thái giao dịch?');">
                    <i class="bi bi-arrow-clockwise"></i> Truy vấn VNPay
                </button>
            </form>
        </div>
        <?php endif; ?>

        <?php if ($isGatewayMethod): ?>
        <div class="payment-card" style="margin-bottom:14px; border-color: rgba(13,202,240,.3);">
            <h3><i class="bi bi-shield-check"></i> Xác nhận thanh toán gateway (Admin override)</h3>
            <p style="font-size:13px; color:var(--text-muted); margin-bottom:10px;">
                Dùng khi admin đã xác minh giao dịch thành công trực tiếp trên cổng <b><?php echo htmlspecialchars((string)($payment['payment_method'] ?? '')); ?></b>
                nhưng hệ thống chưa nhận được callback. Thao tác này sẽ cập nhật trạng thái, booking và giao dịch tài chính.
            </p>
            <form method="POST" class="confirm-form" action="index.php?act=admin/confirm_gateway_payment&id=<?php echo (int)$payment['payment_id']; ?>">
                <?php echo csrfField('payment_gateway_confirm'); ?>
                <div class="field">
                    <label>Ghi chú xác nhận (tuỳ chọn)</label>
                    <input type="text" name="admin_note" maxlength="200" placeholder="VD: Đã kiểm tra trên portal VNPay, mã GD 12345678...">
                </div>
                <button type="submit" class="aventura-btn" style="background:rgba(13,202,240,.15); border:1px solid rgba(13,202,240,.4); color:#0dcaf0;"
                    onclick="return confirm('Xác nhận đã nhận thanh toán qua cổng <?php echo htmlspecialchars((string)($payment['payment_method'] ?? '')); ?> cho payment #<?php echo (int)$payment['payment_id']; ?>?\n\nChỉ thực hiện khi đã xác minh trên portal của cổng thanh toán.');">
                    <i class="bi bi-check2-circle"></i> Xác nhận đã thanh toán qua cổng
                </button>
            </form>
        </div>
        <?php endif; ?>

        <div class="payment-card">
            <h3>Xác nhận đã nhận tiền (chuyển khoản thủ công)</h3>
            <form method="POST" class="confirm-form" action="index.php?act=admin/confirm_payment_received&id=<?php echo (int)$payment['payment_id']; ?>">
                <?php echo csrfField('payment_confirm_received'); ?>
                <div class="field">
                    <label>Số tiền thực nhận (VND)</label>
                    <input type="number" name="received_amount" min="1" step="1" value="<?php echo (int)round((float)($payment['amount'] ?? 0)); ?>" required>
                    <?php if (!empty($validationErrors['received_amount'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:4px;">Số tiền thực nhận không hợp lệ hoặc chưa đủ.</small>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label>Nội dung chuyển khoản từ sao kê</label>
                    <input type="text" name="transfer_note" placeholder="Ví dụ: BOOKING_<?php echo (int)($payment['booking_id'] ?? 0); ?>_<?php echo htmlspecialchars((string)($paymentDetail['so_dien_thoai'] ?? 'SDT')); ?>" required>
                    <?php if (!empty($validationErrors['transfer_note'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:4px;">Nội dung chuyển khoản chưa hợp lệ hoặc chưa khớp.</small>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px;">
                    Hệ thống sẽ đối chiếu số tiền, booking_id và SĐT trong nội dung chuyển khoản trước khi xác nhận.
                    Nội dung cần chứa booking ID (<b><?php echo (int)($payment['booking_id'] ?? 0); ?></b>) hoặc SĐT khách hàng.
                </div>
                <button type="submit" class="aventura-btn aventura-btn-gold" onclick="return confirm('Xác nhận đã nhận tiền cho payment #<?php echo (int)$payment['payment_id']; ?>?');">Xác nhận đã nhận tiền</button>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
