<?php
/**
 * Script để thêm lịch trình chi tiết cho tour NAGOYA – PHÚ SĨ – TOKYO
 * Chạy script này một lần để thêm lịch trình vào database
 */

require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../models/Tour.php';

// Tour ID cho tour NAGOYA (có thể cần điều chỉnh)
$tourId = 6; // Thay đổi nếu tour ID khác

$tourModel = new Tour();

// Kiểm tra tour có tồn tại không
$tour = $tourModel->findById($tourId);
if (!$tour) {
    die("Tour ID $tourId không tồn tại!\n");
}

echo "Đang thêm lịch trình cho tour: " . $tour['ten_tour'] . "\n\n";

// Lịch trình chi tiết
$lichTrinhData = [
    [
        'ngay_thu' => 0, // Ngày tập trung
        'dia_diem' => 'Sân bay Nội Bài – Ga đi quốc tế',
        'hoat_dong' => "🕘 Giờ tập trung: 21:00 (trước giờ bay 3 tiếng)\n👤 Hướng dẫn viên làm thủ tục & hỗ trợ đoàn."
    ],
    [
        'ngay_thu' => 1,
        'dia_diem' => 'HÀ NỘI → TOKYO (Narita)',
        'hoat_dong' => "✈️ Sáng / Trưa / Chiều:\n🕘 09:00 – Tập trung tại sân bay Nội Bài, HDV hỗ trợ check-in.\n🕙 12:00 – Cất cánh đi Nhật Bản.\n\n🌆 Chiều / Tối:\n🕕 18:00 – Hạ cánh sân bay Narita.\n🚌 Di chuyển về khách sạn nhận phòng.\n🍱 Tối: Ăn tối tại nhà hàng địa phương.\n🏨 Nghỉ đêm tại Tokyo / Narita."
    ],
    [
        'ngay_thu' => 2,
        'dia_diem' => 'NAGOYA – THÀNH PHỐ CẢNG',
        'hoat_dong' => "🍳 Sáng:\n🕗 08:00 – Ăn sáng tại khách sạn.\n🚌 Di chuyển đến Nagoya.\n🏯 Tham quan Lâu đài Nagoya – biểu tượng lịch sử nổi tiếng.\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa với món đặc sản Nagoya.\n\n🛍️ Chiều:\n🕒 14:00 – Tham quan & mua sắm tại khu vực Sakae sầm uất.\n\n🍱 Tối:\n🕕 18:00 – Thưởng thức món Tebasaki (gà rán kiểu Nagoya).\n🏨 Nghỉ đêm tại Nagoya."
    ],
    [
        'ngay_thu' => 3,
        'dia_diem' => 'NAGOYA – NÚI PHÚ SĨ – KAWAGUCHIKO',
        'hoat_dong' => "🍳 Sáng:\n🕗 08:00 – Ăn sáng tại khách sạn.\n🚌 Di chuyển đến khu vực núi Phú Sĩ.\n🏔️ Tham quan trạm 5 Núi Phú Sĩ (nếu thời tiết cho phép).\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa tại Kawaguchiko.\n\n🌅 Chiều:\n🌸 Tham quan Hồ Kawaguchiko – check-in với background núi Phú Sĩ.\n🏞️ Tham quan làng cổ Oshino Hakkai.\n\n🍱 Tối:\n🕕 18:00 – Ăn tối với set kaiseki Nhật Bản.\n🛁 Tắm onsen truyền thống tại khách sạn.\n🏨 Nghỉ đêm tại Kawaguchiko."
    ],
    [
        'ngay_thu' => 4,
        'dia_diem' => 'KAWAGUCHIKO – TOKYO',
        'hoat_dong' => "🍳 Sáng:\n🕗 07:30 – Ăn sáng và trả phòng.\n🚌 Khởi hành về Tokyo.\n\n🏙️ Trưa:\n🕛 12:00 – Ăn trưa tại Tokyo.\n\n🗼 Chiều – City Tour Tokyo:\n🏯 Viếng Chùa Asakusa – Đền Sensoji.\n🛍️ Tham quan mua sắm tại Nakamise.\n📷 Check-in tại Tokyo SkyTree (chụp ảnh bên ngoài).\n🚏 Ghé Shibuya Crossing & tượng Hachiko.\n\n🍱 Tối:\n🕕 18:00 – Ăn tối món Nhật.\n🏨 Nghỉ đêm tại Tokyo."
    ],
    [
        'ngay_thu' => 5,
        'dia_diem' => 'TOKYO – HÀ NỘI',
        'hoat_dong' => "🍳 Sáng:\n🕗 07:00 – Ăn sáng tại khách sạn.\n👜 Tự do mua sắm tại Aeon Mall hoặc Akihabara.\n\n🍜 Trưa:\n🕛 12:00 – Ăn trưa.\n\n✈️ Chiều:\n🚌 Di chuyển ra sân bay Narita.\n🕒 Làm thủ tục check-in.\n\n🌙 Tối:\n🛫 Bay về Hà Nội.\n🏁 Kết thúc hành trình – HDV chia tay đoàn."
    ]
];

// Xóa lịch trình cũ và thêm mới
try {
    $tourModel->deleteLichTrinhByTourId($tourId);
    
    foreach ($lichTrinhData as $lt) {
        $result = $tourModel->insertLichTrinh($tourId, $lt);
        if ($result) {
            echo "✓ Đã thêm lịch trình ngày " . $lt['ngay_thu'] . ": " . $lt['dia_diem'] . "\n";
        } else {
            echo "✗ Lỗi khi thêm lịch trình ngày " . $lt['ngay_thu'] . "\n";
        }
    }
    
    echo "\n✅ Hoàn thành! Đã thêm " . count($lichTrinhData) . " ngày lịch trình.\n";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

