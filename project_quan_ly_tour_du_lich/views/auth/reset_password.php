<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu - AVENTURA | Life's A Journey</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/images/momo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/auth-modern.css?v=<?php echo rawurlencode(defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'); ?>">
</head>
<body class="auth-page">
    <div class="auth-wrapper auth-wrapper-single">
        <div class="auth-card-single">
            <div class="auth-single-header">
                <div class="auth-single-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h2 class="form-title" style="font-size: 1.6rem;">Đặt Lại Mật Khẩu</h2>
                <p class="form-subtitle">Nhập mật khẩu mới an toàn cho tài khoản của bạn</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?act=auth/resetPassword&token=<?php echo urlencode($_GET['token'] ?? ''); ?>">
                <?php echo csrfField('auth_reset_password'); ?>

                <div class="form-group-modern">
                    <label class="form-label-modern" for="new_password">
                        <span>Mật khẩu mới (tối thiểu 8 ký tự)</span>
                    </label>
                    <div class="input-container-modern">
                        <i class="bi bi-lock input-icon-prefix"></i>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="input-modern" 
                            placeholder="Nhập mật khẩu mới..." 
                            required 
                            minlength="8"
                            autocomplete="new-password"
                        >
                    </div>
                    <div style="font-size: 0.74rem; color: #94a3b8; margin-top: 0.25rem;">
                        Phải có chữ hoa, chữ thường, số và ký tự đặc biệt.
                    </div>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern" for="confirm_password">
                        <span>Xác nhận mật khẩu mới</span>
                    </label>
                    <div class="input-container-modern">
                        <i class="bi bi-shield-check input-icon-prefix"></i>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="input-modern" 
                            placeholder="Nhập lại mật khẩu mới..." 
                            required 
                            minlength="8"
                            autocomplete="new-password"
                        >
                    </div>
                </div>

                <button type="submit" class="btn-auth-primary" style="margin-top: 1.25rem;">
                    <i class="bi bi-check2-circle"></i>
                    <span>Cập Nhật Mật Khẩu Mới</span>
                </button>
            </form>

            <div class="auth-footer-links" style="margin-top: 1.5rem;">
                <div>
                    <a href="index.php?act=auth/login" class="link-gold">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
