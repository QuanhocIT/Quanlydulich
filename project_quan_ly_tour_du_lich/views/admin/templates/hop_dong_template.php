<?php
// Template Hợp Đồng
$ngayHopDong = date('d/m/Y');
$soHopDong = 'HD' . str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT);
?>

<div class="company-header">
    <h2 class="text-success mb-1">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</h2>
    <p class="mb-0"><strong>Độc lập - Tự do - Hạnh phúc</strong></p>
    <p class="mb-0">────────────</p>
</div>

<div class="document-title text-success">
    HỢP ĐỒNG DỊCH VỤ DU LỊCH
    <div class="fs-6 mt-2">Số: <?php echo $soHopDong; ?></div>
</div>

<p class="text-center fst-italic mb-4">
    Hôm nay, ngày <?php echo $ngayHopDong; ?>, tại TP.HCM, chúng tôi gồm:
</p>

<h5 class="text-success mt-4 mb-3">BÊN A: CÔNG TY DU LỊCH ABC (BÊN CUNG CẤP DỊCH VỤ)</h5>

<table class="info-table">
    <tr>
        <td>Tên công ty:</td>
        <td><strong>CÔNG TY TNHH DU LỊCH ABC</strong></td>
    </tr>
    <tr>
        <td>Địa chỉ:</td>
        <td>123 Đường ABC, Quận 1, TP. Hồ Chí Minh</td>
    </tr>
    <tr>
        <td>Mã số thuế:</td>
        <td>0123456789</td>
    </tr>
    <tr>
        <td>Điện thoại:</td>
        <td>1900 xxxx</td>
    </tr>
    <tr>
        <td>Email:</td>
        <td>info@dulichabc.vn</td>
    </tr>
    <tr>
        <td>Đại diện:</td>
        <td><strong>Ông/Bà [Tên Giám Đốc]</strong> - Chức vụ: Giám đốc</td>
    </tr>
</table>

<h5 class="text-success mt-4 mb-3">BÊN B: KHÁCH HÀNG (BÊN SỬ DỤNG DỊCH VỤ)</h5>

<table class="info-table">
    <tr>
        <td>Họ và tên:</td>
        <td><strong><?php echo htmlspecialchars($booking['ho_ten']); ?></strong></td>
    </tr>
    <tr>
        <td>Địa chỉ:</td>
        <td><?php echo htmlspecialchars($booking['dia_chi'] ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td>CMND/CCCD:</td>
        <td>____________________</td>
    </tr>
    <tr>
        <td>Điện thoại:</td>
        <td><?php echo htmlspecialchars($booking['so_dien_thoai'] ?? 'N/A'); ?></td>
    </tr>
    <tr>
        <td>Email:</td>
        <td><?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?></td>
    </tr>
</table>

<p class="mt-4">
    Hai bên thống nhất ký kết hợp đồng dịch vụ du lịch với các điều khoản sau đây:
</p>

<h5 class="text-success mt-4 mb-3">ĐIỀU 1: ĐỐI TƯỢNG HỢP ĐỒNG</h5>

<p>Bên A đồng ý cung cấp và Bên B đồng ý sử dụng dịch vụ du lịch với thông tin chi tiết như sau:</p>

<table class="info-table">
    <tr>
        <td>Tên chương trình:</td>
        <td><strong><?php echo htmlspecialchars($booking['ten_tour'] ?? 'N/A'); ?></strong></td>
    </tr>
    <tr>
        <td>Ngày khởi hành:</td>
        <td><strong><?php echo $booking['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'N/A'; ?></strong></td>
    </tr>
    <tr>
        <td>Loại tour:</td>
        <td><?php 
            $loaiTour = $booking['loai_tour'] ?? 'N/A';
            $loaiTourText = ['TrongNuoc' => 'Tour Trong Nước', 'QuocTe' => 'Tour Quốc Tế', 'TheoYeuCau' => 'Tour Theo Yêu Cầu'];
            echo $loaiTourText[$loaiTour] ?? $loaiTour;
        ?></td>
    </tr>
    <tr>
        <td>Số lượng khách:</td>
        <td><?php echo $booking['so_nguoi']; ?> người</td>
    </tr>
    <tr>
        <td>Phương tiện:</td>
        <td><?php echo htmlspecialchars($tour['phuong_tien'] ?? 'Xe du lịch'); ?></td>
    </tr>
    <tr>
        <td>Khách sạn:</td>
        <td><?php echo htmlspecialchars($tour['khach_san'] ?? 'Tiêu chuẩn 3*'); ?></td>
    </tr>
</table>

<h5 class="text-success mt-4 mb-3">ĐIỀU 2: GIÁ TRỊ HỢP ĐỒNG</h5>

<table class="detail-table">
    <thead>
        <tr>
            <th>Nội dung</th>
            <th style="text-align: center;">Số lượng</th>
            <th style="text-align: right;">Đơn giá</th>
            <th style="text-align: right;">Thành tiền</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Dịch vụ tour trọn gói</td>
            <td style="text-align: center;"><?php echo $booking['so_nguoi']; ?> người</td>
            <td style="text-align: right;"><?php echo number_format($booking['tong_tien'] / $booking['so_nguoi']); ?> VNĐ</td>
            <td style="text-align: right;"><strong><?php echo number_format($booking['tong_tien']); ?> VNĐ</strong></td>
        </tr>
    </tbody>
</table>

<p class="mt-3">
    <strong>Tổng giá trị hợp đồng: <?php echo number_format($booking['tong_tien']); ?> VNĐ</strong><br>
    <em>(Bằng chữ: <?php echo ucfirst(convertNumberToWords($booking['tong_tien'])); ?> đồng)</em>
</p>

<h5 class="text-success mt-4 mb-3">ĐIỀU 3: PHƯƠNG THỨC THANH TOÁN</h5>

<ul>
    <li><strong>Đặt cọc:</strong> Bên B thanh toán 30% giá trị hợp đồng (<?php echo number_format($booking['tong_tien'] * 0.3); ?> VNĐ) trong vòng 3 ngày kể từ ngày ký hợp đồng</li>
    <li><strong>Thanh toán còn lại:</strong> 70% (<?php echo number_format($booking['tong_tien'] * 0.7); ?> VNĐ) trước ngày khởi hành 7 ngày</li>
    <li><strong>Hình thức thanh toán:</strong> Chuyển khoản hoặc tiền mặt tại văn phòng công ty</li>
</ul>

<p><strong>Thông tin tài khoản:</strong></p>
<ul>
    <li>Tên tài khoản: CÔNG TY TNHH DU LỊCH ABC</li>
    <li>Số tài khoản: 1234567890</li>
    <li>Ngân hàng: Vietcombank - Chi nhánh TP.HCM</li>
</ul>

<h5 class="text-success mt-4 mb-3">ĐIỀU 4: QUYỀN VÀ NGHĨA VỤ CỦA BÊN A</h5>

<p><strong>Quyền của Bên A:</strong></p>
<ul>
    <li>Nhận thanh toán đầy đủ theo hợp đồng</li>
    <li>Thay đổi hành trình khi có lý do bất khả kháng (thiên tai, dịch bệnh...)</li>
    <li>Hủy tour nếu Bên B không thanh toán đúng thời hạn</li>
</ul>

<p><strong>Nghĩa vụ của Bên A:</strong></p>
<ul>
    <li>Cung cấp dịch vụ đúng chất lượng, đúng chương trình đã cam kết</li>
    <li>Bảo đảm an toàn cho khách hàng trong suốt hành trình</li>
    <li>Mua bảo hiểm du lịch cho toàn đoàn</li>
    <li>Thông báo kịp thời cho Bên B khi có thay đổi</li>
</ul>

<h5 class="text-success mt-4 mb-3">ĐIỀU 5: QUYỀN VÀ NGHĨA VỤ CỦA BÊN B</h5>

<p><strong>Quyền của Bên B:</strong></p>
<ul>
    <li>Được cung cấp dịch vụ đúng như đã thỏa thuận</li>
    <li>Được bảo vệ quyền lợi khi dịch vụ không đúng cam kết</li>
    <li>Được hoàn tiền theo quy định khi hủy tour</li>
</ul>

<p><strong>Nghĩa vụ của Bên B:</strong></p>
<ul>
    <li>Thanh toán đầy đủ, đúng hạn theo hợp đồng</li>
    <li>Chấp hành nghiêm chỉnh nội quy, lịch trình của đoàn</li>
    <li>Cung cấp đầy đủ, chính xác thông tin cá nhân khi đăng ký</li>
    <li>Tự chịu trách nhiệm về tài sản, an toàn cá nhân</li>
</ul>

<h5 class="text-success mt-4 mb-3">ĐIỀU 6: ĐIỀU KIỆN HỦY TOUR</h5>

<table class="detail-table">
    <thead>
        <tr>
            <th>Thời gian hủy</th>
            <th>Phí hủy</th>
            <th>Hoàn lại</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Trước 15 ngày khởi hành</td>
            <td>30%</td>
            <td>70%</td>
        </tr>
        <tr>
            <td>Từ 7-14 ngày trước khởi hành</td>
            <td>50%</td>
            <td>50%</td>
        </tr>
        <tr>
            <td>Từ 3-6 ngày trước khởi hành</td>
            <td>80%</td>
            <td>20%</td>
        </tr>
        <tr>
            <td>Trong vòng 3 ngày hoặc không đi</td>
            <td>100%</td>
            <td>0%</td>
        </tr>
    </tbody>
</table>

<h5 class="text-success mt-4 mb-3">ĐIỀU 7: GIẢI QUYẾT TRANH CHẤP</h5>

<ul>
    <li>Mọi tranh chấp phát sinh từ hợp đồng này sẽ được hai bên giải quyết thông qua thương lượng, hòa giải</li>
    <li>Trường hợp không thương lượng được, tranh chấp sẽ được đưa ra Tòa án nhân dân có thẩm quyền</li>
</ul>

<h5 class="text-success mt-4 mb-3">ĐIỀU 8: ĐIỀU KHOẢN CHUNG</h5>

<ul>
    <li>Hợp đồng có hiệu lực kể từ ngày ký và kết thúc khi hai bên hoàn thành nghĩa vụ</li>
    <li>Hợp đồng được lập thành 02 bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản</li>
    <li>Mọi sửa đổi, bổ sung phải được lập thành văn bản và có chữ ký của hai bên</li>
</ul>

<div class="signature-section" style="margin-top: 4rem;">
    <div class="signature-box">
        <p><strong>ĐẠI DIỆN BÊN B</strong></p>
        <p class="text-muted fst-italic">(Ký và ghi rõ họ tên)</p>
        <div style="height: 100px;"></div>
        <p><?php echo htmlspecialchars($booking['ho_ten']); ?></p>
    </div>
    <div class="signature-box">
        <p><strong>ĐẠI DIỆN BÊN A</strong></p>
        <p class="text-muted fst-italic">(Ký, ghi rõ họ tên và đóng dấu)</p>
        <div style="height: 100px;"></div>
        <p>Giám đốc</p>
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