<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buoc Doi Mat Khau - Quan ly tour</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 520px; margin-top: 40px;">
        <h2>Doi Mat Khau Bat Buoc</h2>
        <p>Tai khoan cua ban dang dung mat khau mac dinh hoac yeu. Vui long doi mat khau de tiep tuc.</p>

        <?php if (!empty($error)): ?>
            <div style="color: #b30000; margin-bottom: 10px;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php $validation = getValidationErrors(); ?>
        <?php if (!empty($validation['message'])): ?>
            <div style="color: #b30000; margin-bottom: 10px;"><?php echo htmlspecialchars((string)$validation['message'], ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?act=auth/forcePasswordChange">
            <?php echo csrfField('auth_force_password_change'); ?>

            <div style="margin-bottom: 10px;">
                <label>Mật khẩu hiện tại</label>
                <input type="password" name="current_password" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 10px;">
                <label>Mật khẩu mới</label>
                <input type="password" name="new_password" required style="width: 100%; padding: 8px;">
            </div>

            <div style="margin-bottom: 10px;">
                <label>Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" required style="width: 100%; padding: 8px;">
            </div>

            <div style="font-size: 12px; margin-bottom: 12px; color: #444;">
                Mat khau toi thieu 8 ky tu, bao gom chu hoa, chu thuong, so va ky tu dac biet.
            </div>

            <button type="submit" style="padding: 10px 14px;">Cap nhat mat khau</button>
            <a href="index.php?act=auth/logout" style="margin-left: 8px;">Dang xuat</a>
        </form>
    </div>
</body>
</html>
