<?php
/** @var array|null $booking */
$booking = $booking ?? null;

$pageTitle  = 'Hóa đơn và thanh toán';
$activePage = 'hoaDon';
$pageHero   = [
    'icon'     => 'bi-receipt',
    'title'    => 'Hóa đơn và thanh toán',
    'subtitle' => 'Theo dõi trạng thái giao dịch, xem chi tiết hóa đơn và hoàn tất thanh toán trực tuyến trong cùng một màn hình.',
    'actions'  => [
        ['label' => 'Lịch sử thanh toán', 'href' => 'index.php?act=khachHang/lichSuThanhToan', 'icon' => 'bi-clock-history'],
        ['label' => 'Quay lại tour đã đặt', 'href' => 'index.php?act=khachHang/yeuCauTour', 'icon' => 'bi-arrow-left', 'ghost' => true],
    ],
];
ob_start(); ?>
.invoice-card {
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(15,23,42,.1);
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 14px 38px rgba(2,6,23,.08);
}
.invoice-header {
    border-bottom: 1px solid rgba(15,23,42,.12);
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .payment-history-wrap {
        max-height: 320px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .payment-history-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: var(--bs-table-bg, #f8f9fa);
    }

    .invoice-list-wrap {
        max-height: 400px;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .invoice-list-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fa;
    }
}
<?php $extraCss = ob_get_clean(); include __DIR__ . '/_layout/header.php'; ?>

    <div class="container py-4 py-lg-5">
        <?php if (isset($booking) && $booking): ?>
            <div class="invoice-card">
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Hóa đơn #<?php echo $booking['booking_id']; ?></h3>
                            <p class="text-muted mb-0">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($booking['ngay_dat'] ?? '')); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-<?php 
                                echo match($booking['trang_thai']) {
                                    'ChoXacNhan' => 'warning',
                                    'DaCoc' => 'info',
                                    'HoanTat' => 'success',
                                    'Huy' => 'danger',
                                    default => 'secondary'
                                };
                            ?> fs-6">
                                <?php echo htmlspecialchars($booking['trang_thai'] ?? ''); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Thông tin khách hàng</h5>
                        <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($booking['ho_ten'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($booking['email'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($booking['so_dien_thoai'] ?? ''); ?></p>
                        <?php if (!empty($booking['dia_chi'])): ?>
                            <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($booking['dia_chi']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5>Thông tin tour</h5>
                        <p class="mb-1"><strong>Tour:</strong> <?php echo htmlspecialchars($booking['ten_tour'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Loại tour:</strong> <?php echo htmlspecialchars($booking['loai_tour'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Ngày khởi hành:</strong> <?php echo !empty($booking['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'Chưa xác định'; ?></p>
                        <p class="mb-0"><strong>Số người:</strong> <?php echo $booking['so_nguoi'] ?? 0; ?> người</p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Mô tả</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['ten_tour'] ?? ''); ?></td>
                                <td class="text-end"><?php echo $booking['so_nguoi'] ?? 0; ?> người</td>
                                <td class="text-end"><?php echo number_format((float)($booking['gia_co_ban'] ?? 0)); ?> VNĐ</td>
                                <td class="text-end"><strong><?php echo number_format((float)($booking['tong_tien'] ?? 0)); ?> VNĐ</strong></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Tổng cộng:</th>
                                <th class="text-end"><?php echo number_format((float)($booking['tong_tien'] ?? 0)); ?> VNĐ</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($latestPayment)): ?>
                    <?php
                        $paymentStatus = $latestPayment['status'] ?? '';
                        $paymentBadge = match($paymentStatus) {
                            'ThanhCong' => 'success',
                            'DangXuLy' => 'warning text-dark',
                            'ThatBai' => 'danger',
                            default => 'secondary'
                        };
                        $phoneDigits = preg_replace('/\D+/', '', (string)($booking['so_dien_thoai'] ?? ''));
                        $transferNoteExact = 'BOOKING_' . (int)($booking['booking_id'] ?? 0) . '_' . $phoneDigits;
                    ?>
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>Thanh toán online mới nhất:</strong>
                                #<?php echo (int)$latestPayment['payment_id']; ?>
                                - <?php echo htmlspecialchars($latestPayment['payment_method'] ?? 'N/A'); ?>
                                - <?php echo number_format((float)($latestPayment['amount'] ?? 0)); ?> VNĐ
                            </div>
                            <span class="badge bg-<?php echo $paymentBadge; ?> fs-6"><?php echo htmlspecialchars($paymentStatus); ?></span>
                        </div>
                        <div class="small text-muted mt-1">
                            <?php echo !empty($latestPayment['payment_date']) ? date('d/m/Y H:i', strtotime($latestPayment['payment_date'])) : 'N/A'; ?>
                        </div>
                        <?php if ($paymentStatus === 'DangXuLy'): ?>
                            <div class="small text-warning mt-1">
                                He thong dang cho webhook/admin xac nhan da nhan tien. Trang se tu dong cap nhat sau 10 giay.
                            </div>
                            <div class="mt-3 p-3 rounded" style="background:#fff8db;border:1px dashed #f0ad4e;">
                                <div class="fw-semibold mb-1"><i class="bi bi-info-circle me-1"></i>Noi dung chuyen khoan chinh xac cua ban:</div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span id="transferNoteExact" class="px-2 py-1 rounded" style="background:#fff;border:1px solid #e6e6e6;font-family:monospace;"><?php echo htmlspecialchars($transferNoteExact); ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyTransferNoteExact()">
                                        <i class="bi bi-clipboard me-1"></i>Sao chep
                                    </button>
                                </div>
                                <div class="small text-muted mt-1">Khach chi can copy dung chuoi nay de he thong doi soat tu dong nhanh hon.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($giaoDichList)): ?>
                    <h5 class="mb-3">Lịch sử thanh toán</h5>
                    <div class="table-responsive mb-4 payment-history-wrap">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày</th>
                                    <th>Mô tả</th>
                                    <th class="text-end">Số tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $tongDaThanhToan = 0;
                                foreach ($giaoDichList as $gd): 
                                    if ($gd['loai'] === 'Thu') {
                                        $tongDaThanhToan += (float)$gd['so_tien'];
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($gd['ngay_giao_dich'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($gd['mo_ta'] ?? ''); ?></td>
                                        <td class="text-end <?php echo $gd['loai'] === 'Thu' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $gd['loai'] === 'Thu' ? '+' : '-'; ?>
                                            <?php echo number_format((float)($gd['so_tien'] ?? 0)); ?> VNĐ
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Tổng đã thanh toán:</th>
                                    <th class="text-end text-success"><?php echo number_format($tongDaThanhToan); ?> VNĐ</th>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-end">Còn nợ:</th>
                                    <th class="text-end <?php echo ($booking['tong_tien'] - $tongDaThanhToan) > 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format(max(0, (float)$booking['tong_tien'] - $tongDaThanhToan)); ?> VNĐ
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (in_array($booking['trang_thai'], ['ChoXacNhan', 'DaCoc'])): ?>
                    <div class="text-end">
                        <?php $isPendingPayment = !empty($latestPayment) && (($latestPayment['status'] ?? '') === 'DangXuLy'); ?>
                        <?php if ($isPendingPayment): ?>
                            <button type="button" class="btn btn-secondary btn-lg" disabled>
                                <i class="bi bi-hourglass-split me-2"></i>Đang xử lý thanh toán
                            </button>
                        <?php else: ?>
                            <a href="index.php?act=khachHang/thanhToan&booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card me-2"></i>Thanh toán qua cổng online
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($booking['ghi_chu'])): ?>
                    <div class="mt-4">
                        <h5>Ghi chú</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($booking['ghi_chu'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (isset($bookings) && !empty($bookings)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Danh sách hóa đơn</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive invoice-list-wrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã booking</th>
                                    <th>Tour</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td>#<?php echo $b['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($b['ten_tour'] ?? ''); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($b['ngay_dat'] ?? '')); ?></td>
                                        <td><?php echo number_format((float)($b['tong_tien'] ?? 0)); ?> VNĐ</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($b['trang_thai']) {
                                                    'ChoXacNhan' => 'warning',
                                                    'DaCoc' => 'info',
                                                    'HoanTat' => 'success',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo htmlspecialchars($b['trang_thai'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?act=khachHang/hoaDon&booking_id=<?php echo $b['booking_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Xem
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Bạn chưa có booking nào.
            </div>
        <?php endif; ?>

    </div>

<?php if (!empty($latestPayment) && (($latestPayment['status'] ?? '') === 'DangXuLy')): ?>
<script>
function copyTransferNoteExact() {
    var el = document.getElementById('transferNoteExact');
    if (!el) return;
    var text = (el.textContent || '').trim();
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text);
    } else {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }
}
setTimeout(function () { window.location.reload(); }, 10000);
</script>
<?php endif; ?>
<?php include __DIR__ . '/_layout/footer.php'; ?>
