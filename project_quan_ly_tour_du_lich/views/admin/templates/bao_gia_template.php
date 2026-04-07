<?php
// Function convert number to Vietnamese words
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
        
        // Simplified version for larger numbers
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

// Template Báo Giá
$ngayBaoGia = date('d/m/Y');
$maBaoGia = 'BG' . str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT);
?>

<div class="company-header">
    <h2 class="text-primary mb-1">CÔNG TY DU LỊCH ABC</h2>
    <p class="mb-0"><i class="bi bi-geo-alt"></i> Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
    <p class="mb-0"><i class="bi bi-telephone"></i> Hotline: 1900 xxxx | <i class="bi bi-envelope"></i> Email: info@dulichabc.vn</p>
    <p class="mb-0">Website: www.dulichabc.vn</p>
</div>

<div class="document-title">
    BÁO GIÁ DỊCH VỤ DU LỊCH
</div>

<table class="info-table">
    <tr>
        <td><i class="bi bi-file-text"></i> Mã báo giá:</td>
        <td><strong><?php echo $maBaoGia; ?></strong></td>
    </tr>
    <tr>
        <td><i class="bi bi-calendar"></i> Ngày báo giá:</td>
        <td><?php echo $ngayBaoGia; ?></td>
    </tr>
    <tr>
        <td><i class="bi bi-person"></i> Kính gửi:</td>
        <td><strong><?php echo htmlspecialchars($booking['ho_ten']); ?></strong></td>
    </tr>
    <tr>
        <td><i class="bi bi-envelope"></i> Email:</td>
        <td><?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td><i class="bi bi-phone"></i> Điện thoại:</td>
        <td><?php echo htmlspecialchars($booking['so_dien_thoai'] ?? 'N/A'); ?></td>
    </tr>
</table>

<p>Cảm ơn Quý khách đã quan tâm đến dịch vụ của chúng tôi. Chúng tôi xin gửi đến Quý khách báo giá chi tiết như sau:</p>

<h5 class="mt-4 mb-3"><i class="bi bi-info-circle text-primary"></i> THÔNG TIN TOUR</h5>

<table class="info-table">
    <tr>
        <td><i class="bi bi-map"></i> Tên tour:</td>
        <td><strong><?php echo htmlspecialchars($booking['ten_tour'] ?? 'N/A'); ?></strong></td>
    </tr>
    <tr>
        <td><i class="bi bi-calendar-event"></i> Ngày khởi hành:</td>
        <td><?php echo $booking['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'N/A'; ?></td>
    </tr>
    <tr>
        <td><i class="bi bi-people"></i> Số lượng:</td>
        <td><?php echo $booking['so_nguoi']; ?> người</td>
    </tr>
    <tr>
        <td><i class="bi bi-tag"></i> Loại tour:</td>
        <td><?php 
            $loaiTour = $booking['loai_tour'] ?? 'N/A';
            $loaiTourText = [
                'TrongNuoc' => 'Tour Trong Nước',
                'QuocTe' => 'Tour Quốc Tế',
                'TheoYeuCau' => 'Tour Theo Yêu Cầu'
            ];
            echo $loaiTourText[$loaiTour] ?? $loaiTour;
        ?></td>
    </tr>
</table>

<h5 class="mt-4 mb-3"><i class="bi bi-cash-stack text-success"></i> CHI TIẾT GIÁ</h5>

<table class="detail-table">
    <thead>
        <tr>
            <th>STT</th>
            <th>Nội dung</th>
            <th style="text-align: center;">Số lượng</th>
            <th style="text-align: right;">Đơn giá (VNĐ)</th>
            <th style="text-align: right;">Thành tiền (VNĐ)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>
                <strong>Dịch vụ tour trọn gói</strong><br>
                <small class="text-muted">
                    <?php echo nl2br(htmlspecialchars($booking['mo_ta'] ?? 'Tour du lịch trọn gói bao gồm: xe, khách sạn, bữa ăn, vé tham quan, HDV, bảo hiểm')); ?>
                </small>
            </td>
            <td style="text-align: center;"><?php echo $booking['so_nguoi']; ?></td>
            <td style="text-align: right;"><?php echo number_format($booking['tong_tien'] / $booking['so_nguoi']); ?></td>
            <td style="text-align: right;"><strong><?php echo number_format($booking['tong_tien']); ?></strong></td>
        </tr>
    </tbody>
</table>

<div class="total-section">
    <div class="mb-2">
        <strong>Tạm tính:</strong> 
        <span class="ms-3"><?php echo number_format($booking['tong_tien']); ?> VNĐ</span>
    </div>
    <div class="mb-2">
        <strong>VAT (0%):</strong> 
        <span class="ms-3">0 VNĐ</span>
    </div>
    <div class="mb-2 text-danger fs-5">
        <strong>TỔNG CỘNG:</strong> 
        <span class="ms-3"><strong><?php echo number_format($booking['tong_tien']); ?> VNĐ</strong></span>
    </div>
    <div class="fst-italic text-muted">
        (Bằng chữ: <?php echo ucfirst(convertNumberToWords($booking['tong_tien'])); ?> đồng)
    </div>
</div>

<h5 class="mt-4 mb-3"><i class="bi bi-clipboard-check text-info"></i> DỊCH VỤ BAO GỒM</h5>
<ul>
    <li>Xe du lịch đời mới, máy lạnh đưa đón theo chương trình</li>
    <li>Khách sạn tiêu chuẩn theo yêu cầu (2-3 người/phòng)</li>
    <li>Bữa ăn theo chương trình (sáng/trưa/tối)</li>
    <li>Vé tham quan các điểm du lịch theo chương trình</li>
    <li>Hướng dẫn viên chuyên nghiệp, nhiệt tình</li>
    <li>Bảo hiểm du lịch trọn tour</li>
    <li>Nước uống, khăn lạnh trên xe</li>
</ul>

<h5 class="mt-4 mb-3"><i class="bi bi-x-circle text-danger"></i> DỊCH VỤ KHÔNG BAO GỒM</h5>
<ul>
    <li>Chi phí cá nhân, điện thoại, giặt ủi...</li>
    <li>Đồ uống có cồn trong bữa ăn</li>
    <li>Tiền típ cho HDV, tài xế (tùy ý khách hàng)</li>
</ul>

<h5 class="mt-4 mb-3"><i class="bi bi-exclamation-triangle text-warning"></i> ĐIỀU KIỆN HUỶ TOUR</h5>
<ul>
    <li>Hủy trước 15 ngày: Hoàn 70% tổng tiền</li>
    <li>Hủy trước 7 ngày: Hoàn 50% tổng tiền</li>
    <li>Hủy trước 3 ngày: Hoàn 20% tổng tiền</li>
    <li>Hủy trong vòng 3 ngày hoặc không đi: Không hoàn tiền</li>
</ul>

<div class="signature-section">
    <div class="signature-box">
        <p><strong>KHÁCH HÀNG</strong></p>
        <p class="text-muted fst-italic">(Ký và ghi rõ họ tên)</p>
        <div style="height: 80px;"></div>
    </div>
    <div class="signature-box">
        <p><strong>ĐẠI DIỆN CÔNG TY</strong></p>
        <p class="text-muted fst-italic">(Ký và đóng dấu)</p>
        <div style="height: 80px;"></div>
    </div>
</div>

<div class="text-center mt-4 text-muted fst-italic">
    <p>Báo giá có hiệu lực trong 7 ngày kể từ ngày phát hành</p>
    <p>Mọi thắc mắc xin vui lòng liên hệ: <strong>1900 xxxx</strong></p>
</div>