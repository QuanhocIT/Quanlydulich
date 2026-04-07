<?php
// File: controllers/AjaxChiTietLuongController.php
require_once __DIR__ . '/../models/PhanBoNhanSu.php';
require_once __DIR__ . '/../models/LichKhoiHanh.php';
require_once __DIR__ . '/../models/Tour.php';

$nhan_su_id = $_GET['nhan_su_id'] ?? '';
$month = $_GET['month'] ?? '';
$year = $_GET['year'] ?? '';

$phanBoNhanSuModel = new PhanBoNhanSu();
$lichKhoiHanhModel = new LichKhoiHanh();
$tourModel = new Tour();

// Lấy danh sách phân bổ nhân sự theo tháng/năm
$allLuong = $phanBoNhanSuModel->getAllLuong([
    'nhan_su_id' => $nhan_su_id,
    'month' => $month,
    'year' => $year
]);

if (empty($allLuong)) {
    echo '<div style="text-align:center; color:#ffd700;">Không có dữ liệu chi tiết!</div>';
    exit;
}

$totalHoaHong = 0;
echo '<h3 style="color:#ffd700; text-align:center; margin-bottom:18px;">Chi tiết lương các tour trong tháng</h3>';
echo '<div style="overflow-x:auto;"><table style="width:100%; background:#181818; border-radius:8px; color:#fff;">
<thead><tr>
<th>Tên tour</th>
<th>Ngày khởi hành</th>
<th>% Hoa hồng</th>
<th>Tiền hoa hồng</th>
</tr></thead><tbody>';
foreach ($allLuong as $row) {
    $ten_tour = $row['ten_tour'] ?? '';
    $ngay_khoi_hanh = isset($row['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($row['ngay_khoi_hanh'])) : '';
    $phan_tram_hoa_hong = $row['phan_tram_hoa_hong'] ?? 0;
    $tien_hoa_hong = $row['tien_hoa_hong'] ?? 0;
    $totalHoaHong += $tien_hoa_hong;
    echo '<tr>';
    echo '<td>' . htmlspecialchars($ten_tour) . '</td>';
    echo '<td>' . htmlspecialchars($ngay_khoi_hanh) . '</td>';
    echo '<td>' . htmlspecialchars($phan_tram_hoa_hong) . '%</td>';
    echo '<td>' . number_format($tien_hoa_hong) . '</td>';
    echo '</tr>';
}
echo '</tbody></table></div>';
echo '<div style="margin-top:18px; text-align:right; color:#ffd700; font-size:18px;">Tổng hoa hồng: <b>' . number_format($totalHoaHong) . '</b></div>';
