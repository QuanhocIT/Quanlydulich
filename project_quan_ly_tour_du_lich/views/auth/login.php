<?php
$validationPayload = getValidationErrors();
$validationErrors = $validationPayload['errors'] ?? [];
$errorMessage = !empty($error) ? $error : ($validationPayload['message'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - AVENTURA | Life's A Journey</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/images/momo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/auth-modern.css?v=<?php echo rawurlencode(defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'); ?>">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Left Panel: Travel Showcase -->
            <div class="auth-showcase-panel">
                <div class="brand-badge">
                    <div class="brand-logo-icon">A</div>
                    <div>
                        <div class="brand-name">AVENTURA</div>
                        <div class="brand-tagline">LIFE'S A JOURNEY</div>
                    </div>
                </div>

                <div class="showcase-content">
                    <h1 class="showcase-headline">
                        Khám Phá Kỳ Quan <span>Việt Nam</span> Bất Tận
                    </h1>
                    <p class="showcase-desc">
                        Hệ thống đặt tour du lịch và quản lý lữ hành thông minh. Đồng hành cùng hàng ngàn chuyến phiêu lưu đáng nhớ trên khắp mọi miền đất nước.
                    </p>

                    <div class="showcase-features">
                        <div class="feature-pill">
                            <i class="bi bi-compass"></i>
                            <span>Hàng trăm lịch khởi hành đa dạng khắp cả nước</span>
                        </div>
                        <div class="feature-pill">
                            <i class="bi bi-shield-check"></i>
                            <span>Thanh toán bảo mật đa kênh & xác thực tài khoản 2 lớp</span>
                        </div>
                        <div class="feature-pill">
                            <i class="bi bi-headset"></i>
                            <span>Đội ngũ hướng dẫn viên & CSKH tận tâm 24/7</span>
                        </div>
                    </div>
                </div>

                <div class="showcase-footer">
                    <div>© <?php echo date('Y'); ?> Aventura Travel Platform</div>
                    <div class="rating-pill">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                        <span style="color:#fff;margin-left:4px;">4.9 / 5.0</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Auth Login Form -->
            <div class="auth-form-panel">
                <div class="form-header">
                    <h2 class="form-title">Chào Mừng Trở Lại</h2>
                    <p class="form-subtitle">Đăng nhập tài khoản để tiếp tục trải nghiệm cùng Aventura</p>
                </div>

                <!-- Error Flash Alert -->
                <?php if (!empty($errorMessage)): ?>
                    <div class="auth-alert auth-alert-error" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?php echo $errorMessage; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="auth-alert auth-alert-success" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div><?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="index.php?act=auth/login" id="loginForm">
                    <?php echo csrfField('auth_login'); ?>

                    <div class="form-group-modern">
                        <label class="form-label-modern" for="username">
                            <span>Tên đăng nhập hoặc Email</span>
                        </label>
                        <div class="input-container-modern">
                            <i class="bi bi-person input-icon-prefix"></i>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="input-modern" 
                                placeholder="Nhập username hoặc email của bạn..." 
                                required
                                autocomplete="username"
                            >
                        </div>
                        <?php if (!empty($validationErrors['username']) || !empty($validationErrors['credentials'])): ?>
                            <div class="input-error-text">
                                <i class="bi bi-info-circle"></i> Vui lòng kiểm tra lại thông tin đăng nhập.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern" for="password">
                            <span>Mật khẩu</span>
                        </label>
                        <div class="input-container-modern">
                            <i class="bi bi-shield-lock input-icon-prefix"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="input-modern has-action-btn" 
                                placeholder="Nhập mật khẩu..." 
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="input-action-btn" id="togglePasswordBtn" title="Hiện/ẩn mật khẩu">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        <?php if (!empty($validationErrors['password'])): ?>
                            <div class="input-error-text">
                                <i class="bi bi-info-circle"></i> Mật khẩu không hợp lệ.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-options-row">
                        <label class="custom-checkbox-wrap">
                            <input type="checkbox" id="rememberMe">
                            <span>Ghi nhớ tài khoản</span>
                        </label>
                        <a href="index.php?act=auth/forgotPassword" class="link-gold">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn-auth-primary" id="submitBtn">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Đăng Nhập</span>
                    </button>
                </form>

                <div class="auth-divider">
                    <span>hoặc tiếp tục với</span>
                </div>

                <a href="google_login.php" class="btn-google-auth">
                    <svg class="google-icon-svg" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Đăng nhập bằng tài khoản Google</span>
                </a>

                <div class="auth-footer-links">
                    <div>
                        Chưa có tài khoản? 
                        <a href="index.php?act=auth/register" class="link-gold">Đăng ký thành viên mới</a>
                    </div>
                    <div>
                        <a href="index.php?act=auth/resendVerification" style="color:#94a3b8;font-size:0.82rem;text-decoration:none;">
                            <i class="bi bi-envelope-check me-1"></i>Chưa nhận được email kích hoạt?
                        </a>
                    </div>
                    <a href="index.php?act=tour/index" class="home-return-link">
                        <i class="bi bi-arrow-left"></i> Quay lại trang chủ khám phá tour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script nonce="<?php echo defined('CSP_NONCE') ? CSP_NONCE : ''; ?>">
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const pwdInput = document.getElementById('password');
        const pwdIcon = document.getElementById('togglePasswordIcon');
        if (toggleBtn && pwdInput && pwdIcon) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = pwdInput.type === 'password';
                pwdInput.type = isPassword ? 'text' : 'password';
                pwdIcon.classList.toggle('bi-eye', !isPassword);
                pwdIcon.classList.toggle('bi-eye-slash', isPassword);
            });
        }

        // Remember username via localStorage
        const rememberCheckbox = document.getElementById('rememberMe');
        const usernameInput = document.getElementById('username');
        const savedUsername = localStorage.getItem('aventura_saved_username');
        if (savedUsername && usernameInput) {
            usernameInput.value = savedUsername;
            if (rememberCheckbox) rememberCheckbox.checked = true;
        }

        const loginForm = document.getElementById('loginForm');
        if (loginForm && usernameInput && rememberCheckbox) {
            loginForm.addEventListener('submit', function() {
                if (rememberCheckbox.checked) {
                    localStorage.setItem('aventura_saved_username', usernameInput.value.trim());
                } else {
                    localStorage.removeItem('aventura_saved_username');
                }
            });
        }
    });
    </script>
</body>
</html>
