<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Quản lý Tour Du lịch</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
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
        .header h2 { font-size:1.8rem; font-weight:700; margin-bottom:.25rem; }
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
        .alert-err { background:rgba(220,53,69,0.25); border:1px solid rgba(220,53,69,0.5); color:#fff; }
        .back-link { text-align:center; margin-top:1.25rem; }
        .back-link a { color:rgba(255,255,255,0.8); text-decoration:none; font-size:.9rem; }
        .back-link a:hover { color:#fff; }
        .hint { font-size:.8rem; color:rgba(255,255,255,0.65); margin-top:.3rem; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="glass-card">
        <div class="header">
            <div class="logo-circle"><i class="bi bi-shield-lock"></i></div>
            <h2>Đặt lại mật khẩu</h2>
            <p>Nhập mật khẩu mới cho tài khoản của bạn</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-box alert-err">
                <i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?act=auth/resetPassword&token=<?php echo urlencode($_GET['token'] ?? ''); ?>">
            <?php echo csrfField('auth_reset_password'); ?>
            <div class="form-group">
                <label><i class="bi bi-lock"></i> Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control"
                       placeholder="Tối thiểu 8 ký tự" required minlength="8">
                <div class="hint">Phải có chữ hoa, chữ thường, số và ký tự đặc biệt.</div>
            </div>
            <div class="form-group">
                <label><i class="bi bi-lock-fill"></i> Xác nhận mật khẩu mới</label>
                <input type="password" name="confirm_password" class="form-control"
                       placeholder="Nhập lại mật khẩu mới" required minlength="8">
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle"></i> Đặt lại mật khẩu
            </button>
        </form>

        <div class="back-link">
            <a href="index.php?act=auth/login"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a>
        </div>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
