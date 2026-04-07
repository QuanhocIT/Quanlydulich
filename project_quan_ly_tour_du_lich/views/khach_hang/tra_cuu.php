<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đặt tour</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Tra cứu đặt tour</h2>
        <?php if (!empty($error)) : ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo BASE_URL; ?>index.php?act=khachHang/traCuu">
            <div class="form-group">
                <label>Mã tour (ID):</label>
                <input type="number" name="ma_tour" min="1" required>
            </div>
            <div class="form-group">
                <label>Mã khách hàng (ID):</label>
                <input type="number" name="ma_khach_hang" min="1" required>
            </div>
            <button type="submit">Xem chi tiết</button>
        </form>
        <p><a href="<?php echo BASE_URL; ?>index.php?act=admin/dashboard">Quay lại trang chủ</a></p>
    </div>
</body>
</html>


