<?php
/** @var array $booking */
$booking = $booking ?? [];
$paymentMethod = $paymentMethod ?? 'VNPay';

$pageTitle = 'Thanh toán online';
$activePage = 'hoaDon';
$pageHero = [
    'icon'     => 'bi-credit-card-2-front',
    'title'    => 'Thanh toán online',
    'subtitle' => 'Hoàn tất thanh toán booking của bạn qua cổng thanh toán an toàn.',
    'actions'  => [
        [
            'label' => 'Quay lại hóa đơn',
            'href'  => 'index.php?act=khachHang/hoaDon' . (!empty($booking['booking_id']) ? '&booking_id=' . (int)$booking['booking_id'] : ''),
            'icon'  => 'bi-arrow-left',
            'ghost' => true,
        ],
    ],
];
ob_start(); ?>
.payment-card {
    background: #fff;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.payment-summary {
    background: #f8f9fa;
    border-radius: .75rem;
    padding: 1.5rem;
}
<?php $extraCss = ob_get_clean(); include __DIR__ . '/_layout/header.php'; ?>

    <div class="container py-4 py-lg-5">

        <?php if (isset($booking) && $booking): ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="payment-card">
                        <h4 class="mb-4">Thông tin thanh toán</h4>
                        <?php if (!empty($hasPendingPayment)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-hourglass-split me-2"></i>
                                Booking đang có giao dịch <strong>DangXuLy</strong>. Vui lòng chờ hệ thống xử lý xong trước khi tạo giao dịch mới.
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info">
                            <i class="bi bi-shield-lock me-2"></i>
                            Hệ thống sẽ chuyển bạn tới cổng thanh toán online để hoàn tất giao dịch an toàn.
                            <div class="small mt-1 text-muted">Chế độ hiện tại: <?php echo strtoupper((string)PAYMENT_MODE); ?></div>
                        </div>

                        <form method="POST" action="index.php?act=khachHang/thanhToan&booking_id=<?php echo $booking['booking_id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Số tiền thanh toán</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo number_format((float)$booking['tong_tien']); ?>" readonly>
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                                <small class="text-muted">Hệ thống thanh toán theo tổng giá trị booking.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cổng thanh toán</label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="VNPay" <?php echo ($paymentMethod === 'VNPay') ? 'selected' : ''; ?>>VNPay (Khuyến nghị)</option>
                                    <?php if (!in_array(PAYMENT_MODE, ['vnpay', 'manual_qr'], true)): ?>
                                        <option value="Momo" <?php echo ($paymentMethod === 'Momo') ? 'selected' : ''; ?>>Momo</option>
                                        <option value="Paypal" <?php echo ($paymentMethod === 'Paypal') ? 'selected' : ''; ?>>PayPal</option>
                                    <?php endif; ?>
                                </select>
                                <?php if (PAYMENT_MODE === 'vnpay'): ?>
                                    <small class="text-muted">VNPay callback se cap nhat trang thai thanh toan tu dong khi giao dich thanh cong.</small>
                                <?php elseif (PAYMENT_MODE === 'manual_qr'): ?>
                                    <small class="text-muted">Che do MB QR dang bat, he thong cho admin xac nhan da nhan tien chuyen khoan.</small>
                                <?php endif; ?>
                            </div>

                            <div class="text-end">
                                <?php if (!empty($hasPendingPayment)): ?>
                                    <button type="button" class="btn btn-secondary btn-lg" disabled>
                                        <i class="bi bi-hourglass-split me-2"></i>Đang xử lý giao dịch hiện tại
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-box-arrow-up-right me-2"></i>Tiếp tục đến cổng thanh toán
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="payment-summary">
                        <h5 class="mb-3">Tóm tắt thanh toán</h5>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tour:</span>
                                <strong><?php echo htmlspecialchars($booking['ten_tour'] ?? ''); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Mã booking:</span>
                                <strong>#<?php echo $booking['booking_id']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Số người:</span>
                                <strong><?php echo $booking['so_nguoi'] ?? 0; ?> người</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tổng tiền:</span>
                                <strong><?php echo number_format((float)$booking['tong_tien']); ?> VNĐ</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Không tìm thấy thông tin booking.
            </div>
        <?php endif; ?>
    </div>

<?php include __DIR__ . '/_layout/footer.php'; ?>


