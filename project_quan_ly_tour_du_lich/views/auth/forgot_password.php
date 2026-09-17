<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu - AVENTURA | Life's A Journey</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/images/momo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/auth-modern.css?v=<?php echo rawurlencode(defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'); ?>">
</head>
<body class="auth-page">
    <div class="auth-wrapper auth-wrapper-single">
        <div class="auth-card-single">
            <div class="auth-single-header">
                <div class="auth-single-icon">
                    <i class="bi bi-key-fill"></i>
                </div>
                <h2 class="form-title" style="font-size: 1.6rem;">Quên Mật Khẩu</h2>
                <p class="form-subtitle">Nhập email của bạn để nhận liên kết khôi phục mật khẩu an toàn</p>
            </div>

            <?php if (!empty($info)): ?>
                <div class="auth-alert auth-alert-success" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?php echo htmlspecialchars($info); ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php if (empty($info)): ?>
            <form method="POST" action="index.php?act=auth/forgotPassword">
                <?php echo csrfField('auth_forgot_password'); ?>
                
                <div class="form-group-modern">
                    <label class="form-label-modern" for="email">
                        <span>Địa chỉ Email tài khoản</span>
                    </label>
                    <div class="input-container-modern">
                        <i class="bi bi-envelope input-icon-prefix"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="input-modern" 
                            placeholder="your.email@domain.com" 
                            required
                            autocomplete="email"
                            value="<?php echo htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                </div>

                <button type="submit" class="btn-auth-primary" style="margin-top: 1.25rem;">
                    <i class="bi bi-send-fill"></i>
                    <span>Gửi Liên Kết Đặt Lại Mật Khẩu</span>
                </button>
            </form>
            <?php endif; ?>

            <div class="auth-footer-links" style="margin-top: 1.5rem;">
                <div>
                    <a href="index.php?act=auth/login" class="link-gold">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại màn hình đăng nhập
                    </a>
                </div>
                <a href="index.php?act=tour/index" class="home-return-link">
                    Về trang chủ khám phá tour
                </a>
            </div>
        </div>
    </div>
</body>
</html>
