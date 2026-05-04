<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Quản lý Tour Du lịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 450px;
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
        
        .login-header {
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
        
        .login-header h2 {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .form-group label {
            display: block;
            color: white;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
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
        
        .btn-login {
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
        
        .btn-login::before {
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
        
        .btn-login:hover::before {
            width: 400px;
            height: 400px;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.3));
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn-login:active {
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
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .register-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
        }
        
        .register-link a:hover {
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
        
        /* Responsive */
        @media (max-width: 576px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }
            
            .login-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="glass-card">
            <div class="login-header">
                <div class="logo-circle">
                    <i class="bi bi-globe-asia-australia"></i>
                </div>
                <h2>Đăng nhập</h2>
                <p>Hệ thống quản lý tour du lịch</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php $validationPayload = getValidationErrors(); ?>
            <?php if (!isset($error) && !empty($validationPayload['message'])): ?>
                <div class="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo htmlspecialchars((string)$validationPayload['message']); ?>
                </div>
            <?php endif; ?>
            <?php $validationErrors = $validationPayload['errors'] ?? []; ?>
            
            <form method="POST" action="index.php?act=auth/login">
                <?php echo csrfField('auth_login'); ?>
                <div class="form-group">
                    <label><i class="bi bi-person"></i> Tên đăng nhập / Email</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-circle input-icon"></i>
                        <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập hoặc email" required>
                    </div>
                    <?php if (!empty($validationErrors['username']) || !empty($validationErrors['credentials'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:6px;">Vui lòng nhập đúng tên đăng nhập/email.</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label><i class="bi bi-lock"></i> Mật khẩu</label>
                    <div class="input-wrapper">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                    </div>
                    <?php if (!empty($validationErrors['password']) || !empty($validationErrors['credentials'])): ?>
                        <small style="color:#ffb4b4;display:block;margin-top:6px;">Mật khẩu không hợp lệ.</small>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-login">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </span>
                </button>
            </form>
            
            <div class="divider">
                <span>hoặc</span>
            </div>
            
            <div class="register-link">
                <a href="index.php?act=auth/forgotPassword" style="opacity:0.75;font-size:0.9rem;">
                    <i class="bi bi-key"></i> Quên mật khẩu?
                </a>
            </div>
            <div class="register-link" style="margin-top:0.5rem;">
                <a href="index.php?act=auth/register">
                    <i class="bi bi-person-plus"></i> Chưa có tài khoản? Đăng ký ngay
                </a>
            </div>
            <div class="register-link" style="margin-top:0.5rem;opacity:0.7;font-size:0.85rem;">
                <a href="index.php?act=auth/resendVerification">
                    <i class="bi bi-envelope-arrow-up"></i> Gửi lại email xác nhận
                </a>
            </div>
            <div class="text-center mt-3">
                    <a href="google_login.php" class="btn btn-danger w-100"><i class="bi bi-google me-1"></i> Đăng nhập bằng Google</a>
                </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


