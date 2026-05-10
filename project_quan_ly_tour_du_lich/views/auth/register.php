<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Quản lý Tour Du lịch</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-image: url('<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
            padding: 2rem 0;
        }
        
        /* Dark overlay for better contrast */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        
        .register-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            padding: 0 20px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Glass Morphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated gradient border */
        .glass-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                rgba(255, 255, 255, 0.5),
                rgba(255, 255, 255, 0.1),
                rgba(255, 255, 255, 0.5)
            );
            border-radius: 20px;
            z-index: -1;
            animation: borderGlow 3s linear infinite;
        }
        
        @keyframes borderGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        
        .glass-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            border: 2px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            animation: logoPulse 3s ease-in-out infinite;
        }
        
        @keyframes logoPulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
            }
        }
        
        .logo-circle i {
            font-size: 2.5rem;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .register-header h2 {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .register-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
        }
        
        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
            z-index: 1;
        }
        
        .form-group label {
            display: block;
            color: white;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            z-index: 1;
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .form-control:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        }
        
        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.2));
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn-register::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-register:hover::before {
            width: 400px;
            height: 400px;
        }
        
        .btn-register:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.3));
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .btn-text {
            position: relative;
            z-index: 1;
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            z-index: 1;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
        }
        
        .divider::before { left: 0; }
        .divider::after { right: 0; }
        
        .divider span {
            background: transparent;
            padding: 0 1rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .login-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
        }
        
        .login-link a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .alert {
            background: rgba(220, 53, 69, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: shake 0.5s ease;
            position: relative;
            z-index: 1;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .alert i {
            margin-right: 0.5rem;
        }
        
        /* Chrome autofill fix */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: white;
            -webkit-box-shadow: 0 0 0px 1000px rgba(255, 255, 255, 0.1) inset;
            transition: background-color 5000s ease-in-out 0s;
        }
        
        /* Responsive */
        @media (max-width: 576px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }
            
            .register-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="glass-card">
            <div class="register-header">
                <div class="logo-circle">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <h2>Đăng ký tài khoản</h2>
                <p>Tạo tài khoản để khám phá du lịch Việt Nam</p>
            </div>
            
            <?php if (!empty($info)): ?>
                <div class="alert" style="background:rgba(25,135,84,0.15);border-color:#198754;color:#0f5132;">
                    <i class="bi bi-envelope-check"></i>
                    <?php echo $info; // Already HTML-escaped at controller level ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php $validationPayload = getValidationErrors(); ?>
            <?php if (empty($error) && !empty($validationPayload['message'])): ?>
                <div class="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo htmlspecialchars((string)$validationPayload['message']); ?>
                </div>
            <?php endif; ?>
            <?php $validationErrors = $validationPayload['errors'] ?? []; ?>
            
            <form method="POST" action="index.php?act=auth/register">
                <?php echo csrfField('auth_register'); ?>
                <div class="form-group">
                    <label><i class="bi bi-person"></i> Họ và tên</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-circle input-icon"></i>
                        <input type="text" name="ho_ten" class="form-control" placeholder="Nhập họ và tên đầy đủ" required>
                    </div>
                    <?php if (!empty($validationErrors['ho_ten'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:6px;">Họ tên không hợp lệ hoặc quá dài.</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label><i class="bi bi-envelope"></i> Email</label>
                    <div class="input-wrapper">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="your.email@example.com" required>
                    </div>
                    <?php if (!empty($validationErrors['email'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:6px;">Email không hợp lệ.</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label><i class="bi bi-phone"></i> Số điện thoại</label>
                    <div class="input-wrapper">
                        <i class="bi bi-telephone-fill input-icon"></i>
                        <input type="tel" name="so_dien_thoai" class="form-control" placeholder="0912345678" required>
                    </div>
                    <?php if (!empty($validationErrors['so_dien_thoai'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:6px;">Số điện thoại không hợp lệ.</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label><i class="bi bi-lock"></i> Mật khẩu</label>
                    <div class="input-wrapper">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Tạo mật khẩu mạnh" required>
                    </div>
                    <?php if (!empty($validationErrors['password'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:6px;">Mật khẩu phải có ít nhất 6 ký tự.</small>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-register">
                    <span class="btn-text">
                        <i class="bi bi-check-circle"></i> Đăng ký ngay
                    </span>
                </button>
            </form>
            
            <div class="divider">
                <span>hoặc</span>
            </div>
            
            <div class="login-link">
                <a href="index.php?act=auth/login">
                    <i class="bi bi-box-arrow-in-right"></i> Đã có tài khoản? Đăng nhập
                </a>
            </div>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>


