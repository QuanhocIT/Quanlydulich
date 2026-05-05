<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Quản lý Tour Du lịch</title>
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
        body::before {
            content:''; position:fixed; inset:0;
            background:rgba(0,0,0,0.35); z-index:1;
        }
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
            border:1px solid rgba(255,255,255,0.3); border-radius:12px;
            color:#fff; font-size:1rem;
        }
        .form-control::placeholder { color:rgba(255,255,255,0.6); }
        .form-control:focus { outline:none; background:rgba(255,255,255,0.25); border-color:rgba(255,255,255,0.5); }
        .btn-submit {
            width:100%; padding:1rem; background:linear-gradient(135deg,rgba(255,255,255,0.3),rgba(255,255,255,0.2));
            border:1px solid rgba(255,255,255,0.4); border-radius:12px; color:#fff;
            font-size:1.05rem; font-weight:600; cursor:pointer; margin-top:.5rem;
            transition:all .3s;
        }
        .btn-submit:hover { background:rgba(255,255,255,0.35); transform:translateY(-2px); }
        .alert-box {
            padding:.85rem 1rem; border-radius:10px; margin-bottom:1.25rem; font-size:.9rem;
        }
        .alert-err { background:rgba(220,53,69,0.25); border:1px solid rgba(220,53,69,0.5); color:#fff; }
        .alert-ok  { background:rgba(25,135,84,0.25); border:1px solid rgba(40,167,69,0.5); color:#fff; }
        .alert-info { background:rgba(13,110,253,0.25); border:1px solid rgba(13,110,253,0.5); color:#fff; }
        .back-link { text-align:center; margin-top:1.25rem; }
        .back-link a { color:rgba(255,255,255,0.8); text-decoration:none; font-size:.9rem; }
        .back-link a:hover { color:#fff; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="glass-card">
        <div class="header">
            <div class="logo-circle"><i class="bi bi-key"></i></div>
            <h2>Quên mật khẩu</h2>
            <p>Nhập email để nhận liên kết đặt lại mật khẩu</p>
        </div>

        <div class="alert-box alert-info">
            <i class="bi bi-info-circle me-1"></i><strong>Ghi chú:</strong> Do chức năng này cần cấu hình với bên thứ 3 mất phí nên tạm thời bị Dev vô hiệu.
        </div>

        <?php if (!empty($info)): ?>
            <div class="alert-box alert-ok">
                <i class="bi bi-check-circle me-1"></i><?php echo htmlspecialchars($info); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert-box alert-err">
                <i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($info)): ?>
        <form method="POST" action="index.php?act=auth/forgotPassword">
            <?php echo csrfField('auth_forgot_password'); ?>
            <div class="form-group">
                <label><i class="bi bi-envelope"></i> Địa chỉ Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="Nhập email đã đăng ký" required
                       value="<?php echo htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-send"></i> Gửi liên kết đặt lại
            </button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.php?act=auth/login"><i class="bi bi-arrow-left"></i> Quay lại đăng nhập</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
