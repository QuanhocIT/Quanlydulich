<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Quản lý Tour Du lịch</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Thông tin cá nhân</h2>
        <?php if (isset($user)): ?>
            <div class="profile-info">
                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($user['ho_ten']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Vai trò:</strong> <?php echo htmlspecialchars($user['vai_tro']); ?></p>
            </div>
        <?php endif; ?>
        <a href="index.php?act=tour/index">Trang chủ</a>
    </div>
</body>
</html>


