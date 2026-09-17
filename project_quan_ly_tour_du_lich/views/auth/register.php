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
    <title>Đăng Ký Tài Khoản - AVENTURA | Life's A Journey</title>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/images/momo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/auth-modern.css?v=<?php echo rawurlencode(defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'); ?>">
</head>
<body class="auth-page">
    <div class="auth-wrapper auth-wrapper-wide">
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
                        Bắt Đầu <span>Hành Trình</span> Kỳ Thú Của Bạn
                    </h1>
                    <p class="showcase-desc">
                        Trở thành thành viên Aventura để mở khóa hàng trăm tour du lịch trọn gói độc quyền, quản lý lịch khởi hành và tận hưởng dịch vụ lữ hành chuẩn 5 sao.
                    </p>

                    <div class="showcase-features">
                        <div class="feature-pill">
                            <i class="bi bi-gift"></i>
                            <span>Ưu đãi voucher chào mừng & tích điểm du lịch</span>
                        </div>
                        <div class="feature-pill">
                            <i class="bi bi-calendar-check"></i>
                            <span>Tra cứu & quản lý lịch trình tour theo thời gian thực</span>
                        </div>
                        <div class="feature-pill">
                            <i class="bi bi-chat-heart"></i>
                            <span>Được lắng nghe và hỗ trợ chăm sóc cá nhân hóa</span>
                        </div>
                    </div>
                </div>

                <div class="showcase-footer">
                    <div>© <?php echo date('Y'); ?> Aventura Travel Platform</div>
                    <div class="rating-pill">
                        <i class="bi bi-shield-lock-fill text-info"></i>
                        <span style="color:#cbd5e1;margin-left:4px;">Bảo mật thông tin 100%</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Auth Register Form -->
            <div class="auth-form-panel">
                <div class="form-header">
                    <h2 class="form-title">Đăng Ký Tài Khoản</h2>
                    <p class="form-subtitle">Điền thông tin bên dưới để tạo tài khoản khám phá Việt Nam</p>
                </div>

                <!-- Info / Success Flash Alert -->
                <?php if (!empty($info)): ?>
                    <div class="auth-alert auth-alert-success" role="alert">
                        <i class="bi bi-envelope-check-fill"></i>
                        <div><?php echo $info; ?></div>
                    </div>
                <?php endif; ?>

                <!-- Error Flash Alert -->
                <?php if (!empty($errorMessage)): ?>
                    <div class="auth-alert auth-alert-error" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?php echo htmlspecialchars((string)$errorMessage); ?></div>
                    </div>
                <?php endif; ?>

                <!-- Register Form -->
                <form method="POST" action="index.php?act=auth/register" id="registerForm">
                    <?php echo csrfField('auth_register'); ?>

                    <div class="form-group-modern">
                        <label class="form-label-modern" for="ho_ten">
                            <span>Họ và tên <span style="color:#f87171;">*</span></span>
                        </label>
                        <div class="input-container-modern">
                            <i class="bi bi-person input-icon-prefix"></i>
                            <input 
                                type="text" 
                                id="ho_ten" 
                                name="ho_ten" 
                                class="input-modern" 
                                placeholder="VD: Nguyễn Văn A" 
                                required
                                maxlength="120"
                                autocomplete="name"
                            >
                        </div>
                        <?php if (!empty($validationErrors['ho_ten'])): ?>
                            <div class="input-error-text">
                                <i class="bi bi-info-circle"></i> Họ tên không hợp lệ hoặc quá dài.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="grid-two-cols">
                        <div class="form-group-modern">
                            <label class="form-label-modern" for="email">
                                <span>Email <span style="color:#f87171;">*</span></span>
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
                                >
                            </div>
                            <?php if (!empty($validationErrors['email'])): ?>
                                <div class="input-error-text">
                                    <i class="bi bi-info-circle"></i> Email không hợp lệ.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group-modern">
                            <label class="form-label-modern" for="so_dien_thoai">
                                <span>Số điện thoại <span style="color:#f87171;">*</span></span>
                            </label>
                            <div class="input-container-modern">
                                <i class="bi bi-telephone input-icon-prefix"></i>
                                <input 
                                    type="tel" 
                                    id="so_dien_thoai" 
                                    name="so_dien_thoai" 
                                    class="input-modern" 
                                    placeholder="0912345678" 
                                    required
                                    autocomplete="tel"
                                >
                            </div>
                            <?php if (!empty($validationErrors['so_dien_thoai'])): ?>
                                <div class="input-error-text">
                                    <i class="bi bi-info-circle"></i> Số điện thoại không hợp lệ.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern" for="ten_dang_nhap">
                            <span>Tên đăng nhập <small style="color:#94a3b8;font-weight:400;">(Tùy chọn - mặc định là email)</small></span>
                        </label>
                        <div class="input-container-modern">
                            <i class="bi bi-at input-icon-prefix"></i>
                            <input 
                                type="text" 
                                id="ten_dang_nhap" 
                                name="ten_dang_nhap" 
                                class="input-modern" 
                                placeholder="VD: nguyenvana (để trống sẽ dùng email)" 
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern" for="password">
                            <span>Mật khẩu bảo vệ <span style="color:#f87171;">*</span></span>
                        </label>
                        <div class="input-container-modern">
                            <i class="bi bi-shield-lock input-icon-prefix"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="input-modern has-action-btn" 
                                placeholder="Nhập tối thiểu 8 ký tự..." 
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="input-action-btn" id="togglePasswordBtn" title="Hiện/ẩn mật khẩu">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>

                        <!-- Live Password Strength Indicator -->
                        <div class="pwd-strength-container">
                            <div class="pwd-strength-track">
                                <div class="pwd-strength-fill" id="pwdStrengthFill"></div>
                            </div>
                            <div class="pwd-strength-meta">
                                <span>Độ mạnh: <strong id="pwdStrengthLabel" style="color:#94a3b8;">Chưa nhập</strong></span>
                                <span id="pwdStrengthScore">0/4 tiêu chuẩn</span>
                            </div>
                            <div class="pwd-rules-checklist">
                                <div class="rule-item" id="ruleLen"><i class="bi bi-circle"></i> Tối thiểu 8 ký tự</div>
                                <div class="rule-item" id="ruleCase"><i class="bi bi-circle"></i> Chữ hoa & chữ thường</div>
                                <div class="rule-item" id="ruleNum"><i class="bi bi-circle"></i> Chứa ít nhất 1 số</div>
                                <div class="rule-item" id="ruleSpecial"><i class="bi bi-circle"></i> Ký tự đặc biệt (!@#$...)</div>
                            </div>
                        </div>

                        <?php if (!empty($validationErrors['password'])): ?>
                            <div class="input-error-text">
                                <i class="bi bi-info-circle"></i> <?php echo htmlspecialchars((string)$validationErrors['password']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-options-row" style="margin-top: 0.5rem; margin-bottom: 1.25rem;">
                        <label class="custom-checkbox-wrap" style="font-size:0.8rem; align-items:flex-start;">
                            <input type="checkbox" required checked id="agreeTerms" style="margin-top:3px;">
                            <span>Tôi đồng ý với <a href="#" class="link-gold">Điều khoản dịch vụ</a> và <a href="#" class="link-gold">Chính sách bảo mật</a> của Aventura.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-auth-primary" id="registerSubmitBtn">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Đăng Ký Tài Khoản</span>
                    </button>
                </form>

                <div class="auth-divider">
                    <span>hoặc</span>
                </div>

                <a href="google_login.php" class="btn-google-auth">
                    <svg class="google-icon-svg" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <span>Đăng ký nhanh bằng Google</span>
                </a>

                <div class="auth-footer-links">
                    <div>
                        Đã có tài khoản Aventura? 
                        <a href="index.php?act=auth/login" class="link-gold">Đăng nhập ngay</a>
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

        // Live Password Strength Check
        const fill = document.getElementById('pwdStrengthFill');
        const label = document.getElementById('pwdStrengthLabel');
        const scoreMeta = document.getElementById('pwdStrengthScore');
        const ruleLen = document.getElementById('ruleLen');
        const ruleCase = document.getElementById('ruleCase');
        const ruleNum = document.getElementById('ruleNum');
        const ruleSpecial = document.getElementById('ruleSpecial');

        function updateRuleItem(el, isValid) {
            if (!el) return;
            el.classList.toggle('valid', isValid);
            const icon = el.querySelector('i');
            if (icon) {
                icon.className = isValid ? 'bi bi-check-circle-fill' : 'bi bi-circle';
            }
        }

        if (pwdInput && fill && label) {
            pwdInput.addEventListener('input', function() {
                const val = pwdInput.value || '';
                if (!val) {
                    fill.style.width = '0%';
                    fill.style.backgroundColor = 'transparent';
                    label.innerText = 'Chưa nhập';
                    label.style.color = '#94a3b8';
                    if (scoreMeta) scoreMeta.innerText = '0/4 tiêu chuẩn';
                    updateRuleItem(ruleLen, false);
                    updateRuleItem(ruleCase, false);
                    updateRuleItem(ruleNum, false);
                    updateRuleItem(ruleSpecial, false);
                    return;
                }

                const hasLen = val.length >= 8;
                const hasCase = /[A-Z]/.test(val) && /[a-z]/.test(val);
                const hasNum = /[0-9]/.test(val);
                const hasSpecial = /[^A-Za-z0-9]/.test(val);

                updateRuleItem(ruleLen, hasLen);
                updateRuleItem(ruleCase, hasCase);
                updateRuleItem(ruleNum, hasNum);
                updateRuleItem(ruleSpecial, hasSpecial);

                let score = 0;
                if (hasLen) score++;
                if (hasCase) score++;
                if (hasNum) score++;
                if (hasSpecial) score++;

                if (scoreMeta) scoreMeta.innerText = score + '/4 tiêu chuẩn';

                if (score <= 1) {
                    fill.style.width = '25%';
                    fill.style.backgroundColor = '#ef4444';
                    label.innerText = 'Yếu';
                    label.style.color = '#ef4444';
                } else if (score === 2) {
                    fill.style.width = '50%';
                    fill.style.backgroundColor = '#f59e0b';
                    label.innerText = 'Trung bình';
                    label.style.color = '#f59e0b';
                } else if (score === 3) {
                    fill.style.width = '75%';
                    fill.style.backgroundColor = '#38bdf8';
                    label.innerText = 'Khá';
                    label.style.color = '#38bdf8';
                } else {
                    fill.style.width = '100%';
                    fill.style.backgroundColor = '#10b981';
                    label.innerText = 'Rất mạnh';
                    label.style.color = '#10b981';
                }
            });
        }
    });
    </script>
    <style>
    @media (max-width: 600px) {
        .grid-two-cols {
            grid-template-columns: 1fr !important;
        }
    }
    </style>
</body>
</html>
