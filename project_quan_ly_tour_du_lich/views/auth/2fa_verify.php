<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực 2 Bước (2FA) - AVENTURA | Life's A Journey</title>
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
                <h2 class="form-title" style="font-size: 1.6rem;">Xác Thực 2 Bước (2FA)</h2>
                <p class="form-subtitle">Nhập mã 6 chữ số từ ứng dụng Google Authenticator hoặc Authy</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?act=auth/verify2fa" autocomplete="off">
                <?php echo csrfField('auth_2fa_verify'); ?>

                <div class="form-group-modern">
                    <label class="form-label-modern" for="totp_code" style="justify-content:center;">
                        <span>Mã xác thực TOTP (6 chữ số)</span>
                    </label>
                    <div class="input-container-modern">
                        <input 
                            type="text" 
                            id="totp_code"
                            name="totp_code" 
                            class="input-modern" 
                            style="text-align: center; font-size: 1.6rem; letter-spacing: 0.5rem; font-weight: 700; height: 54px; padding: 0 1rem;"
                            placeholder="••••••" 
                            maxlength="6" 
                            inputmode="numeric"
                            pattern="[0-9]{6}" 
                            autofocus 
                            autocomplete="one-time-code" 
                            required
                        >
                    </div>
                    <div style="font-size: 0.76rem; color: #94a3b8; text-align: center; margin-top: 0.4rem;">
                        Mã OTP thay đổi mỗi 30 giây trên điện thoại của bạn
                    </div>
                </div>

                <button type="submit" class="btn-auth-primary" style="margin-top: 1.25rem;">
                    <i class="bi bi-shield-check"></i>
                    <span>Xác Nhận Đăng Nhập</span>
                </button>
            </form>

            <div class="auth-footer-links" style="margin-top: 1.5rem;">
                <div>
                    <a href="index.php?act=auth/login" class="link-gold">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại màn hình đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    document.getElementById('totp_code')?.addEventListener('input', function() {
        if (this.value.replace(/\D/g,'').length === 6) {
            this.value = this.value.replace(/\D/g,'');
            this.closest('form').submit();
        }
    });
    </script>
</body>
</html>
