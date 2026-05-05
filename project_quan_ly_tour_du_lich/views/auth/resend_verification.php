<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi lại email xác nhận - Quản lý Tour Du lịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background-image: url('<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg');
            background-size:cover; background-position:center; background-attachment:fixed;
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
        }
        body::before { content:''; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:1; }
        .wrap { position:relative; z-index:2; width:100%; max-width:440px; padding:0 20px; }
        .glass-card {
            background:rgba(255,255,255,0.15); backdrop-filter:blur(20px);
            border-radius:20px; border:1px solid rgba(255,255,255,0.3);
            box-shadow:0 8px 32px rgba(0,0,0,0.37); padding:3rem 2.5rem;
        }
        .header { text-align:center; margin-bottom:2rem; color:#fff; }
        .logo-circle {
            width:70px; height:70px; background:rgba(255,255,255,0.2);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            margin:0 auto 1rem; font-size:2rem; color:#fff;
            border:2px solid rgba(255,255,255,0.4);
        }
        .header h2 { font-size:1.75rem; font-weight:700; margin-bottom:.25rem; }
        .header p { opacity:.8; font-size:.95rem; }
        .form-group { margin-bottom:1.25rem; }
        .form-group label { color:#fff; font-size:.9rem; font-weight:500; margin-bottom:.4rem; display:block; }
        .form-control {
            width:100%; padding:.75rem 1rem; background:rgba(255,255,255,0.15);
            border:1px solid rgba(255,255,255,0.3); border-radius:12px; color:#fff; font-size:1rem;
        }
        .form-control::placeholder { color:rgba(255,255,255,0.6); }
        .form-control:focus { outline:none; background:rgba(255,255,255,0.25); border-color:rgba(255,255,255,0.5); }
        .btn-submit {
            width:100%; padding:1rem; background:linear-gradient(135deg,rgba(255,255,255,0.3),rgba(255,255,255,0.2));
            border:1px solid rgba(255,255,255,0.4); border-radius:12px; color:#fff;
            font-size:1.05rem; font-weight:600; cursor:pointer; margin-top:.5rem; transition:all .3s;
        }
        .btn-submit:hover { background:rgba(255,255,255,0.35); transform:translateY(-2px); }
        .alert-box { padding:.85rem 1rem; border-radius:10px; margin-bottom:1.25rem; font-size:.9rem; }
        .alert-ok { background:rgba(25,135,84,0.25); border:1px solid rgba(40,167,69,0.5); color:#fff; }
        .alert-info { background:rgba(13,110,253,0.25); border:1px solid rgba(13,110,253,0.5); color:#fff; }
        .back-link { text-align:center; margin-top:1.25rem; }
        .back-link a { color:rgba(255,255,255,0.8); text-decoration:none; font-size:.9rem; }
        .back-link a:hover { color:#fff; }
        .alert-err { background:rgba(220,53,69,0.25); border:1px solid rgba(220,53,69,0.5); color:#fff; }
        .alert-warn { background:rgba(255,193,7,0.2); border:1px solid rgba(255,193,7,0.5); color:#fff; }
        .countdown-bar { height:4px; background:rgba(255,255,255,0.15); border-radius:2px; margin-top:1rem; overflow:hidden; }
        .countdown-bar-fill { height:100%; background:rgba(40,167,69,0.7); border-radius:2px; transition:width 1s linear; }
        .countdown-text { text-align:center; font-size:.82rem; opacity:.75; margin-top:.4rem; }
        .input-icon-wrap { position:relative; }
        .input-icon-wrap .bi { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.6); pointer-events:none; }
        .input-icon-wrap .form-control { padding-left:2.4rem; }
        .btn-submit:disabled { opacity:.6; cursor:not-allowed; transform:none; }
        .spinner { display:inline-block; width:1em; height:1em; border:2px solid currentColor; border-right-color:transparent; border-radius:50%; animation:spin .6s linear infinite; vertical-align:-.15em; }
        @keyframes spin { to { transform:rotate(360deg); } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="glass-card">
        <div class="header">
            <div class="logo-circle"><i class="bi bi-envelope-arrow-up"></i></div>
            <h2>Gửi lại xác nhận</h2>
            <p>Nhập email đã đăng ký để nhận lại liên kết</p>
        </div>

        <div class="alert-box alert-info">
            <i class="bi bi-info-circle me-1"></i><strong>Ghi chú:</strong> Do chức năng này cần cấu hình với bên thứ 3 mất phí nên tạm thời bị Dev vô hiệu.
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-err">
                <i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars((string)$error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($alreadyVerified)): ?>
            <div class="alert-box alert-warn">
                <i class="bi bi-info-circle me-1"></i>
                Email này đã được xác nhận trước đó. Bạn có thể <a href="index.php?act=auth/login" style="color:#fff;font-weight:600;">đăng nhập ngay</a>.
            </div>
        <?php elseif (!empty($info)): ?>
            <div class="alert-box alert-ok" id="successBox">
                <i class="bi bi-check-circle me-1"></i><?php echo htmlspecialchars((string)$info); ?>
            </div>
            <div class="countdown-bar"><div class="countdown-bar-fill" id="cntBar" style="width:100%"></div></div>
            <p class="countdown-text" id="cntText">Chuyển đến trang đăng nhập sau <strong id="cntNum">8</strong> giây...</p>
        <?php endif; ?>

        <?php if (empty($info) && empty($alreadyVerified)): ?>
        <form method="POST" action="index.php?act=auth/resendVerification" id="resendForm" novalidate>
            <?php echo csrfField('auth_resend_verify'); ?>
            <div class="form-group">
                <label for="emailInput"><i class="bi bi-envelope"></i> Địa chỉ Email</label>
                <div class="input-icon-wrap">
                    <i class="bi bi-envelope"></i>
                    <input type="email" name="email" id="emailInput" class="form-control"
                           placeholder="Nhập email đã đăng ký" autocomplete="email"
                           value="<?php echo htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           required>
                </div>
            </div>
            <button type="submit" class="btn-submit" id="submitBtn">
                <i class="bi bi-send" id="sendIcon"></i>
                <span id="btnLabel"> Gửi lại email xác nhận</span>
            </button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.php?act=auth/login"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a>
        </div>
    </div>
</div>
<script>
(function(){
    // Auto-redirect countdown after success
    const cntNum  = document.getElementById('cntNum');
    const cntBar  = document.getElementById('cntBar');
    const cntText = document.getElementById('cntText');
    if (cntNum) {
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

    // Loading state on submit
    const form      = document.getElementById('resendForm');
    const submitBtn = document.getElementById('submitBtn');
    const sendIcon  = document.getElementById('sendIcon');
    const btnLabel  = document.getElementById('btnLabel');
    if (form) {
        form.addEventListener('submit', function(e) {
            const emailVal = document.getElementById('emailInput').value.trim();
            // Basic email pattern check
            if (!emailVal || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                e.preventDefault();
                document.getElementById('emailInput').style.borderColor = 'rgba(220,53,69,0.8)';
                return;
            }
            submitBtn.disabled = true;
            sendIcon.outerHTML = '<span class="spinner"></span>';
            btnLabel.textContent = ' Đang gửi...';
        });
    }
})();
</script>
</body>
</html>
