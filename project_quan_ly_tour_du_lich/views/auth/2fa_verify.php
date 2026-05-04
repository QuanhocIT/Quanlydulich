<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực 2 bước - Quản lý Tour Du lịch</title>
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
        body::before { content:''; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1; }
        .wrap { position:relative; z-index:2; width:100%; max-width:420px; padding:0 20px; }
        .glass-card {
            background:rgba(255,255,255,0.15); backdrop-filter:blur(20px);
            border-radius:20px; border:1px solid rgba(255,255,255,0.3);
            box-shadow:0 8px 32px rgba(0,0,0,0.4); padding:3rem 2.5rem;
        }
        .header { text-align:center; margin-bottom:2rem; color:#fff; }
        .logo-circle {
            width:72px; height:72px; background:rgba(255,255,255,0.2);
            border-radius:50%; display:flex; align-items:center; justify-content:center;
            margin:0 auto 1rem; font-size:2.2rem; color:#fff;
            border:2px solid rgba(255,255,255,0.4);
        }
        .header h2 { font-size:1.75rem; font-weight:700; margin-bottom:.25rem; }
        .header p  { opacity:.8; font-size:.95rem; }
        .form-group { margin-bottom:1.5rem; }
        .form-group label { color:#fff; font-size:.9rem; font-weight:500; margin-bottom:.4rem; display:block; }
        .code-input {
            width:100%; padding:.9rem 1rem; background:rgba(255,255,255,0.15);
            border:1px solid rgba(255,255,255,0.3); border-radius:12px;
            color:#fff; font-size:1.6rem; font-weight:700; letter-spacing:.5rem;
            text-align:center;
        }
        .code-input::placeholder { color:rgba(255,255,255,0.45); font-size:1rem; letter-spacing:normal; font-weight:400; }
        .code-input:focus { outline:none; background:rgba(255,255,255,0.25); border-color:rgba(255,255,255,0.5); }
        .btn-submit {
            width:100%; padding:1rem;
            background:linear-gradient(135deg,rgba(255,255,255,0.3),rgba(255,255,255,0.2));
            border:1px solid rgba(255,255,255,0.4); border-radius:12px; color:#fff;
            font-size:1.05rem; font-weight:600; cursor:pointer; margin-top:.25rem; transition:all .3s;
        }
        .btn-submit:hover { background:rgba(255,255,255,0.35); transform:translateY(-2px); }
        .alert-err { background:rgba(220,53,69,0.25); border:1px solid rgba(220,53,69,0.5); color:#fff;
            padding:.85rem 1rem; border-radius:10px; margin-bottom:1.25rem; font-size:.9rem; }
        .hint { color:rgba(255,255,255,0.65); font-size:.83rem; margin-top:.5rem; text-align:center; }
        .back-link { text-align:center; margin-top:1.25rem; }
        .back-link a { color:rgba(255,255,255,0.75); text-decoration:none; font-size:.9rem; }
        .back-link a:hover { color:#fff; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="glass-card">
        <div class="header">
            <div class="logo-circle"><i class="bi bi-shield-lock-fill"></i></div>
            <h2>Xác thực 2 bước</h2>
            <p>Nhập mã 6 chữ số từ ứng dụng Authenticator</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-err"><i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?act=auth/verify2fa" autocomplete="off">
            <?php echo csrfField('auth_2fa_verify'); ?>
            <div class="form-group">
                <label><i class="bi bi-phone"></i> Mã xác thực (TOTP)</label>
                <input type="text" name="totp_code" class="code-input"
                       placeholder="000000" maxlength="6" inputmode="numeric"
                       pattern="[0-9]{6}" autofocus autocomplete="one-time-code" required>
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle"></i> Xác nhận
            </button>
        </form>

        <p class="hint">Mã thay đổi mỗi 30 giây. Mở Google Authenticator / Authy để lấy mã.</p>

        <div class="back-link">
            <a href="index.php?act=auth/login"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-submit khi nhập đủ 6 chữ số
document.querySelector('.code-input').addEventListener('input', function() {
    if (this.value.replace(/\D/g,'').length === 6) {
        this.value = this.value.replace(/\D/g,'');
        this.closest('form').submit();
    }
});
</script>
</body>
</html>
