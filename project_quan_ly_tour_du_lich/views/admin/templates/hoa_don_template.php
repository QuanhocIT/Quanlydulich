<?php
// Template Hóa Đơn
$ngayHoaDon = date('d/m/Y');
$soHoaDon = 'INV' . str_pad($booking['booking_id'], 8, '0', STR_PAD_LEFT);
$kyHieu = 'AA/25E';
?>

<div class="company-header">
    <div class="row">
        <div class="col-6 text-start">
            <h3 class="text-warning mb-2">CÔNG TY DU LỊCH ABC</h3>
            <p class="mb-1 small">Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
            <p class="mb-1 small">MST: 0123456789</p>
            <p class="mb-1 small">ĐT: 1900 xxxx</p>
        </div>
        <div class="col-6 text-end">
            <p class="mb-1"><strong>Mẫu số:</strong> 01GTKT3/001</p>
            <p class="mb-1"><strong>Ký hiệu:</strong> <?php echo $kyHieu; ?></p>
            <p class="mb-1"><strong>Số:</strong> <?php echo $soHoaDon; ?></p>
        </div>
    </div>
</div>

<div class="document-title text-warning">
    HÓA ĐƠN GIÁ TRỊ GIA TĂNG
    <div class="fs-6 mt-2 fw-normal">Ngày <?php echo date('d'); ?> tháng <?php echo date('m'); ?> năm <?php echo date('Y'); ?></div>
</div>

<h5 class="text-warning mt-4 mb-3">THÔNG TIN KHÁCH HÀNG</h5>

<table class="info-table">
    <tr>
        <td style="width: 25%;"><i class="bi bi-person"></i> Họ tên người mua:</td>
        <td><strong><?php echo htmlspecialchars($booking['ho_ten']); ?></strong></td>
    </tr>
    <tr>
        <td><i class="bi bi-building"></i> Tên đơn vị:</td>
        <td><?php echo isset($booking['ten_cong_ty']) && $booking['ten_cong_ty'] ? htmlspecialchars($booking['ten_cong_ty']) : 'Cá nhân'; ?></td>
    </tr>
    <tr>
        <td><i class="bi bi-geo-alt"></i> Địa chỉ:</td>
        <td><?php echo htmlspecialchars($booking['dia_chi'] ?? ''); ?></td>
    </tr>
    <tr>
        <td><i class="bi bi-phone"></i> Số điện thoại:</td>
        <td><?php echo htmlspecialchars($booking['so_dien_thoai'] ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td><i class="bi bi-envelope"></i> Email:</td>
        <td><?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td><i class="bi bi-credit-card"></i> Mã số thuế:</td>
        <td>__________________</td>
    </tr>
    <tr>
        <td><i class="bi bi-cash"></i> Hình thức thanh toán:</td>
        <td>Chuyển khoản / Tiền mặt</td>
    </tr>
</table>

<h5 class="text-warning mt-4 mb-3">CHI TIẾT HÓA ĐƠN</h5>

<table class="detail-table">
    <thead>
        <tr>
            <th style="width: 5%;">STT</th>
            <th style="width: 40%;">Tên hàng hóa, dịch vụ</th>
            <th style="width: 10%;">ĐVT</th>
            <th style="width: 10%;">Số lượng</th>
            <th style="width: 15%;">Đơn giá</th>
            <th style="width: 10%;">VAT (%)</th>
            <th style="width: 10%;">Thành tiền</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center;">1</td>
            <td>
                <strong>Dịch vụ tour du lịch</strong><br>
                <small class="text-muted">
                    Tour: <?php echo htmlspecialchars($booking['ten_tour'] ?? 'N/A'); ?><br>
                    Ngày KH: <?php echo $booking['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'N/A'; ?><br>
                    Số khách: <?php echo $booking['so_nguoi']; ?> người
                </small>
            </td>
            <td style="text-align: center;">Khách</td>
            <td style="text-align: center;"><?php echo $booking['so_nguoi']; ?></td>
            <td style="text-align: right;"><?php echo number_format($booking['tong_tien'] / $booking['so_nguoi']); ?></td>
            <td style="text-align: center;">0%</td>
            <td style="text-align: right;"><strong><?php echo number_format($booking['tong_tien']); ?></strong></td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: right;"><strong>Cộng tiền hàng:</strong></td>
            <td style="text-align: right;"><strong><?php echo number_format($booking['tong_tien']); ?></strong></td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: right;"><strong>Tiền thuế VAT (0%):</strong></td>
            <td style="text-align: right;"><strong>0</strong></td>
        </tr>
        <tr style="background: #fff3cd;">
            <td colspan="6" style="text-align: right;">
                <strong class="fs-5 text-warning">TỔNG CỘNG TIỀN THANH TOÁN:</strong>
            </td>
            <td style="text-align: right;">
                <strong class="fs-5 text-warning"><?php echo number_format($booking['tong_tien']); ?></strong>
            </td>
        </tr>
    </tbody>
</table>

<div class="mt-3">
    <p><strong>Số tiền viết bằng chữ:</strong> <em><?php echo ucfirst(convertNumberToWords($booking['tong_tien'])); ?> đồng chẵn</em></p>
</div>

<div class="mt-4 p-3 border rounded">
    <h6 class="text-warning"><i class="bi bi-info-circle"></i> GHI CHÚ:</h6>
    <ul class="small mb-0">
        <li>Hóa đơn này chỉ có giá trị khi có đầy đủ chữ ký, đóng dấu của người bán</li>
        <li>Vui lòng kiểm tra kỹ thông tin trước khi rời khỏi quầy thanh toán</li>
        <li>Hóa đơn là căn cứ để khấu trừ thuế GTGT và quyết toán với cơ quan thuế</li>
        <li>Thời hạn sử dụng hóa đơn: đến ngày 31/12/<?php echo date('Y'); ?></li>
    </ul>
</div>

<div class="signature-section" style="margin-top: 3rem;">
    <div class="signature-box">
        <p><strong>NGƯỜI MUA HÀNG</strong></p>
        <p class="text-muted fst-italic small">(Ký, ghi rõ họ tên)</p>
        <div style="height: 80px;"></div>
    </div>
    <div class="signature-box">
        <p><strong>NGƯỜI BÁN HÀNG</strong></p>
        <p class="text-muted fst-italic small">(Ký, ghi rõ họ tên)</p>
        <div style="height: 80px;"></div>
    </div>
    <div class="signature-box">
        <p><strong>THỦ TRƯỞNG ĐƠN VỊ</strong></p>
        <p class="text-muted fst-italic small">(Ký, đóng dấu, ghi rõ họ tên)</p>
        <div style="height: 80px;"></div>
    </div>
</div>

<div class="text-center mt-4">
    <p class="small text-muted mb-1">
        <i class="bi bi-printer"></i> Cần bảo quản hóa đơn để làm căn cứ kiểm tra khi cần thiết
    </p>
    <p class="small text-muted fst-italic">
        Mọi thắc mắc xin liên hệ: <strong>1900 xxxx</strong> hoặc email: <strong>info@dulichabc.vn</strong>
    </p>
</div>

<div class="mt-3 p-2 bg-light border-top border-bottom">
    <div class="row small">
        <div class="col-4">
            <strong>Ngày in:</strong> <?php echo date('d/m/Y H:i'); ?>
        </div>
        <div class="col-4 text-center">
            <strong>Mã hóa đơn:</strong> <?php echo $soHoaDon; ?>
        </div>
        <div class="col-4 text-end">
            <strong>Website:</strong> www.dulichabc.vn
        </div>
    </div>
</div>

<?php
if (!function_exists('convertNumberToWords')) {
    function convertNumberToWords($number) {
        $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tens = ['', 'mười', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
        
        if ($number == 0) return 'không';
        if ($number < 10) return $ones[$number];
        if ($number < 100) {
            $ten = floor($number / 10);
            $one = $number % 10;
            return $tens[$ten] . ($one > 0 ? ' ' . $ones[$one] : '');
        }
        
        $billion = floor($number / 1000000000);
        $million = floor(($number % 1000000000) / 1000000);
        $thousand = floor(($number % 1000000) / 1000);
        $hundred = $number % 1000;
        
        $result = [];
        if ($billion > 0) $result[] = convertNumberToWords($billion) . ' tỷ';
        if ($million > 0) $result[] = convertNumberToWords($million) . ' triệu';
        if ($thousand > 0) $result[] = convertNumberToWords($thousand) . ' nghìn';
        if ($hundred > 0) $result[] = convertNumberToWords($hundred);
        
        return implode(' ', $result);
    }
}
?>