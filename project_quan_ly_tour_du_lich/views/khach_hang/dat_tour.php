<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Tour - Khách hàng</title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
</head>
<body>
    <div class="container">
        <h1>Đặt Tour</h1>
        <?php if (isset($tour)): ?>
            <form method="POST" action="index.php?act=booking/create" data-gia-co-ban="<?php echo $tour['gia_co_ban'] ?? 0; ?>">
                <?php echo csrfField('booking_create'); ?>
                <input type="hidden" name="tour_id" value="<?php echo $tour['tour_id']; ?>">
                <div class="form-group">
                    <p><strong><i class="bi bi-card-text text-primary me-1"></i> Tour:</strong> <?php echo htmlspecialchars($tour['ten_tour']); ?></p>
                    <p><strong><i class="bi bi-cash-coin text-success me-1"></i> Giá cơ bản:</strong> <?php echo number_format((float)($tour['gia_co_ban'] ?? 0)); ?> VNĐ</p>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-people-fill text-info me-1"></i> Số lượng người:</label>
                    <input type="number" name="so_nguoi" min="1" required>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-calendar-event text-warning me-1"></i> Ngày khởi hành:</label>
                    <input type="date" name="ngay_khoi_hanh" required>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-chat-left-text text-secondary me-1"></i> Ghi chú:</label>
                    <textarea name="ghi_chu" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-calculator text-danger me-1"></i> Tổng tiền:</label>
                    <input type="text" name="tong_tien" value="<?php echo (float)($tour['gia_co_ban'] ?? 0); ?>" readonly>
                </div>
                <button type="submit"><i class="bi bi-check2-circle me-1"></i> Xác nhận đặt tour</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>


