<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Tour - Khách hàng</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Danh sách Tour</h1>
        <nav>
            <a href="index.php?act=auth/profile">Thông tin cá nhân</a>
            <a href="index.php?act=auth/logout">Đăng xuất</a>
        </nav>
        <div class="content">
            <?php if (isset($tours) && !empty($tours)): ?>
                <div class="tour-list">
                    <?php foreach ($tours as $tour): ?>
                        <div class="tour-item">
                            <h3><?php echo htmlspecialchars($tour['ten_tour']); ?></h3>
                            <p><?php echo htmlspecialchars($tour['mo_ta']); ?></p>
                            <p><strong>Giá cơ bản:</strong> <?php echo number_format((float)($tour['gia_co_ban'] ?? 0)); ?> VNĐ</p>
                            <a href="index.php?act=tour/show&id=<?php echo $tour['tour_id']; ?>">Xem chi tiết</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Chưa có tour nào.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


