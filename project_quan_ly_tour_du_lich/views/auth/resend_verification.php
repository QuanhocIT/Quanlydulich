<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Lại Email Xác Nhận - AVENTURA | Life's A Journey</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/images/momo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/auth-modern.css?v=<?php echo rawurlencode(defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'); ?>">
</head>
<body class="auth-page">
    <div class="auth-wrapper auth-wrapper-single">
        <div class="auth-card-single">
            <div class="auth-single-header">
                <div class="auth-single-icon">
                    <i class="bi bi-envelope-arrow-up-fill"></i>
                </div>
                <h2 class="form-title" style="font-size: 1.6rem;">Gửi Lại Email Kích Hoạt</h2>
                <p class="form-subtitle">Nhập email bạn đã đăng ký để nhận lại liên kết xác nhận tài khoản</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-error" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?php echo htmlspecialchars((string)$error); ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($alreadyVerified)): ?>
                <div class="auth-alert auth-alert-success" role="alert">
                    <i class="bi bi-info-circle-fill"></i>
                    <div>Email này đã được xác nhận kích hoạt trước đó. Bạn có thể <a href="index.php?act=auth/login" class="link-gold">đăng nhập ngay</a>.</div>
                </div>
            <?php elseif (!empty($info)): ?>
                <div class="auth-alert auth-alert-success" id="successBox" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?php echo htmlspecialchars((string)$info); ?></div>
                </div>
                <div class="pwd-strength-track" style="margin-top: 1rem;"><div class="pwd-strength-fill" id="cntBar" style="width:100%; background:#10b981;"></div></div>
                <p style="text-align:center; font-size:0.8rem; color:#94a3b8; margin-top:0.4rem;">Chuyển về trang đăng nhập sau <strong id="cntNum" style="color:#fff;">8</strong> giây...</p>
            <?php endif; ?>

            <?php if (empty($info) && empty($alreadyVerified)): ?>
            <form method="POST" action="index.php?act=auth/resendVerification" id="resendForm" novalidate>
                <?php echo csrfField('auth_resend_verify'); ?>

                <div class="form-group-modern">
                    <label class="form-label-modern" for="emailInput">
                        <span>Địa chỉ Email tài khoản</span>
                    </label>
                    <div class="input-container-modern">
                        <i class="bi bi-envelope input-icon-prefix"></i>
                        <input 
                            type="email" 
                            name="email" 
                            id="emailInput" 
                            class="input-modern" 
                            placeholder="your.email@domain.com" 
                            autocomplete="email"
                            value="<?php echo htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-auth-primary" id="submitBtn" style="margin-top: 1.25rem;">
                    <i class="bi bi-send-fill" id="sendIcon"></i>
                    <span id="btnLabel">Gửi Lại Email Xác Nhận</span>
                </button>
            </form>
            <?php endif; ?>

            <div class="auth-footer-links" style="margin-top: 1.5rem;">
                <div>
                    <a href="index.php?act=auth/login" class="link-gold">
                        <i class="bi bi-arrow-left me-1"></i> Quay lại đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    (function(){
        const cntNum  = document.getElementById('cntNum');
        const cntBar  = document.getElementById('cntBar');
        if (cntNum && cntBar) {
            let secs = 8;
            const tick = setInterval(function() {
                secs--;
                cntNum.textContent = secs;
                cntBar.style.width = (secs / 8 * 100) + '%';
                if (secs <= 0) {
                    clearInterval(tick);
                    window.location.href = 'index.php?act=auth/login';
                }
            }, 1000);
        }
    })();
    </script>
</body>
</html>
