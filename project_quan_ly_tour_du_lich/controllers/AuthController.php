<?php
require_once __DIR__ . '/../commons/function.php';
require_once 'models/NguoiDung.php';
require_once 'models/KhachHang.php';

class AuthController {
    private $model;
    private $khachHangModel;
    
    public function __construct() {
        $this->model = new NguoiDung();
        $this->khachHangModel = new KhachHang();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            enforceRateLimit('login:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 5, 300);
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'auth_login')) {
                setValidationErrors(['_csrf_token' => 'invalid'], 'Phien lam viec khong hop le. Vui long thu lai.');
                $error = "Phiên làm việc không hợp lệ. Vui lòng thử lại.";
                require 'views/auth/login.php';
                return;
            }

            $username = requestString('username', '', 'POST');  // Có thể là ten_dang_nhap hoặc email
            $password = (string)($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                setValidationErrors([
                    'username' => $username === '' ? 'required' : null,
                    'password' => $password === '' ? 'required' : null,
                ], 'Ten dang nhap/Email hoac mat khau khong dung.');
                $error = "Tên đăng nhập/Email hoặc mật khẩu không đúng";
                require 'views/auth/login.php';
                return;
            }

            // Tìm người dùng theo ten_dang_nhap hoặc email
            $user = $this->model->find(['ten_dang_nhap' => $username]);
            if (!$user) {
                $user = $this->model->findByEmail($username);
            }

            if ($user) {
                $stored = $user['mat_khau'] ?? '';
                $authenticated = false;

                if (!isSecurePasswordHash($stored)) {
                    // Temporary compatibility mode: allow legacy plaintext login once,
                    // then upgrade to secure hash immediately.
                    if ((string)$stored === $password) {
                        $authenticated = true;
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $this->model->updatePassword($user['id'], $newHash);
                        $stored = $newHash;
                        logSecurityEvent('legacy_plaintext_password_upgraded', ['user_id' => (int)$user['id']]);
                    } else {
                        recordFailedLoginAttempt($username, 'invalid_credentials');
                        setValidationErrors(['credentials' => 'invalid'], 'Ten dang nhap/Email hoac mat khau khong dung.');
                        $error = 'Tên đăng nhập/Email hoặc mật khẩu không đúng';
                        require 'views/auth/login.php';
                        return;
                    }
                } elseif (password_verify($password, $stored)) {
                    $authenticated = true;
                    if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $this->model->updatePassword($user['id'], $newHash);
                        $stored = $newHash;
                    }
                }

                if ($authenticated) {
                    // Block login for new accounts whose email hasn't been verified yet.
                    // Accounts created before email verification was introduced
                    // (email_verification_token IS NULL) are treated as grandfathered.
                    $pendingVerification = isset($user['email_verified_at'])
                        && $user['email_verified_at'] === null
                        && isset($user['email_verification_token'])
                        && $user['email_verification_token'] !== null;

                    if ($pendingVerification) {
                        $error = 'Tài khoản chưa được xác nhận email. Vui lòng kiểm tra hộp thư <strong>'
                            . htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8')
                            . '</strong> và nhấn vào liên kết xác nhận.';
                        require 'views/auth/login.php';
                        return;
                    }

                    $sessionData = [];

                    if ($user['vai_tro'] === 'KhachHang') {
                        $khachHang = $this->khachHangModel->findByNguoiDungId($user['id']);
                        if ($khachHang) {
                            $sessionData['khach_hang_id'] = $khachHang['khach_hang_id'];
                        }
                    }

                    if (requiresPasswordChange($stored)) {
                        $sessionData['force_password_change'] = 1;
                    }

                    completeUserLoginSession($user['id'], $user['vai_tro'], $user['ho_ten'], $sessionData);

                    if (!empty($_SESSION['force_password_change'])) {
                        $_SESSION['error'] = 'Tai khoan dang dung mat khau mac dinh. Vui long doi mat khau de tiep tuc.';
                        header('Location: index.php?act=auth/forcePasswordChange');
                        exit();
                    }

                    // Redirect theo vai trò
                    switch ($user['vai_tro']) {
                        case 'Admin':
                        $_SESSION['admin_sidebar_start_hidden_once'] = 1;
                        header('Location: index.php?act=admin/dashboard');
                        exit();
                        case 'HDV':
                            header('Location: index.php?act=hdv/dashboard');
                            exit();
                        case 'KhachHang':
                            header('Location: index.php?act=khachHang/dashboard');
                            exit();
                        case 'NhaCungCap':
                            header('Location: index.php?act=nhaCungCap/dichVu');
                            exit();
                        default:
                            header('Location: index.php?act=tour/index');
                        exit();
                    }
                }
            }

            recordFailedLoginAttempt($username, 'invalid_credentials');
            $error = "Tên đăng nhập/Email hoặc mật khẩu không đúng";
            setValidationErrors(['credentials' => 'invalid'], 'Ten dang nhap/Email hoac mat khau khong dung.');
            require 'views/auth/login.php';
        } else {
            require 'views/auth/login.php';
        }
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            enforceRateLimit('register:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 3, 600);
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'auth_register')) {
                setValidationErrors(['_csrf_token' => 'invalid'], 'Phien lam viec khong hop le. Vui long thu lai.');
                $error = "Phiên làm việc không hợp lệ. Vui lòng thử lại.";
                require 'views/auth/register.php';
                return;
            }

            $schema = validateInputSchema([
                'email' => ['type' => 'email', 'required' => true],
                'so_dien_thoai' => ['type' => 'phone', 'required' => false],
                'ho_ten' => ['type' => 'string', 'required' => true, 'max' => 120],
                'password' => ['type' => 'string', 'required' => true, 'min' => 8],
            ], 'POST');

            if (!$schema['ok']) {
                setValidationErrors($schema['errors'], 'Thong tin dang ky khong hop le.');
                $error = "Thông tin đăng ký không hợp lệ.";
                require 'views/auth/register.php';
                return;
            }

            $email = $schema['data']['email'];
            if ($email === null) {
                setValidationErrors(['email' => 'invalid'], 'Email khong hop le.');
                $error = "Email không hợp lệ.";
                require 'views/auth/register.php';
                return;
            }

            $soDienThoai = $schema['data']['so_dien_thoai'];
            if (!empty($_POST['so_dien_thoai']) && $soDienThoai === null) {
                setValidationErrors(['so_dien_thoai' => 'invalid'], 'So dien thoai khong hop le.');
                $error = "Số điện thoại không hợp lệ.";
                require 'views/auth/register.php';
                return;
            }

            $hoTen = (string)$schema['data']['ho_ten'];
            $password = (string)$schema['data']['password'];
            $passwordPolicy = validatePasswordPolicy($password);
            if (!$passwordPolicy['ok']) {
                setValidationErrors(['password' => implode(',', $passwordPolicy['errors'])], 'Mat khau chua dat chinh sach an toan.');
                $error = "Mật khẩu phải có ít nhất 8 ký tự gồm chữ hoa, chữ thường, số và ký tự đặc biệt.";
                require 'views/auth/register.php';
                return;
            }

            // Kiểm tra email đã tồn tại chưa
            $existing = $this->model->findByEmail($email);
            if ($existing) {
                $error = "Email đã được sử dụng. Vui lòng dùng email khác.";
                require 'views/auth/register.php';
                return;
            }

            // Nếu người dùng có gửi ten_dang_nhap riêng, kiểm tra trùng tên đăng nhập
            $ten_dang_nhap = requestString('ten_dang_nhap', $email, 'POST');
            $existingUserName = $this->model->find(['ten_dang_nhap' => $ten_dang_nhap]);
            if ($existingUserName) {
                $error = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
                require 'views/auth/register.php';
                return;
            }

            $data = [
                'so_dien_thoai' => $soDienThoai ?? '',
                'ho_ten' => $hoTen,
                'email' => $email,
                'ten_dang_nhap' => $ten_dang_nhap,
                'mat_khau' => password_hash($password, PASSWORD_DEFAULT),
                'vai_tro' => 'KhachHang',
                'ngay_tao' => date('Y-m-d H:i:s')
            ];

            $userId = $this->model->insert($data);

            if ($userId) {
                $this->khachHangModel->insert(['nguoi_dung_id' => $userId]);

                // --- Email verification ---
                // Generate a cryptographically secure one-time token and store it.
                $verifyToken = bin2hex(random_bytes(32));
                $conn = connectDB();
                $stmt = $conn->prepare("UPDATE nguoi_dung SET email_verification_token = ? WHERE id = ?");
                $stmt->execute([$verifyToken, $userId]);

                // Send verification email (non-blocking: failure just skips sending)
                require_once __DIR__ . '/../commons/mail.php';
                $verifyUrl = rtrim((string)BASE_URL, '/') . '/index.php?act=auth/verifyEmail&token=' . urlencode($verifyToken);
                $htmlBody = '<p>Xin chào <strong>' . htmlspecialchars($hoTen, ENT_QUOTES, 'UTF-8') . '</strong>,</p>'
                    . '<p>Cảm ơn bạn đã đăng ký tài khoản. Vui lòng nhấn vào liên kết bên dưới để xác nhận địa chỉ email:</p>'
                    . '<p><a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '</a></p>'
                    . '<p>Liên kết có hiệu lực trong <strong>24 giờ</strong>. Nếu bạn không thực hiện đăng ký này, hãy bỏ qua email.</p>';
                sendHtmlEmail($email, 'Xác nhận email - Quản lý Tour Du lịch', $htmlBody);

                $info = 'Đăng ký thành công! Vui lòng kiểm tra hộp thư <strong>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</strong> để xác nhận địa chỉ email trước khi đăng nhập.';
                require 'views/auth/register.php';
                return;
            } else {
                $error = "Đăng ký thất bại. Vui lòng thử lại sau.";
                require 'views/auth/register.php';
                return;
            }
        } else {
            require 'views/auth/register.php';
        }
    }

    /**
     * Verify a user's email address via the one-time token sent during registration.
     * On success, marks the account as verified and logs the user in.
     */
    public function verifyEmail() {
        $token = trim((string)($_GET['token'] ?? ''));
        if ($token === '' || strlen($token) > 64) {
            $error = 'Liên kết xác nhận không hợp lệ.';
            require 'views/auth/login.php';
            return;
        }

        $conn = connectDB();
        $stmt = $conn->prepare(
            "SELECT id, ho_ten, email, vai_tro
               FROM nguoi_dung
              WHERE email_verification_token = ?
                AND email_verified_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'Liên kết xác nhận không hợp lệ hoặc đã được sử dụng.';
            require 'views/auth/login.php';
            return;
        }

        // Mark verified and clear token (one-time use)
        $upd = $conn->prepare(
            "UPDATE nguoi_dung SET email_verified_at = NOW(), email_verification_token = NULL WHERE id = ?"
        );
        $upd->execute([(int)$user['id']]);
        logSecurityEvent('email_verified', ['user_id' => (int)$user['id']]);

        // Auto-login after verification
        $khachHangId = null;
        if ($user['vai_tro'] === 'KhachHang') {
            require_once 'models/KhachHang.php';
            $kh     = new KhachHang();
            $khInfo = $kh->findByUserId((int)$user['id']);
            $khachHangId = $khInfo['khach_hang_id'] ?? null;
        }
        completeUserLoginSession((int)$user['id'], (string)$user['vai_tro'], (string)$user['ho_ten'], [
            'khach_hang_id' => $khachHangId,
        ]);
        $_SESSION['success'] = 'Email đã được xác nhận thành công! Chào mừng bạn đến với hệ thống.';
        redirectToRoleHome('tour/index');
    }

    public function logout() {
        logoutCurrentUser('logout');
        header('Location: index.php?act=auth/login');
        exit();
    }

    public function forcePasswordChange() {
        requireLogin();

        if (empty($_SESSION['force_password_change'])) {
            redirectToRoleHome('tour/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'auth_force_password_change')) {
                setValidationErrors(['_csrf_token' => 'invalid'], 'Phien lam viec khong hop le. Vui long thu lai.');
                $error = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
                require 'views/auth/force_change_password.php';
                return;
            }

            $currentPassword = (string)($_POST['current_password'] ?? '');
            $newPassword = (string)($_POST['new_password'] ?? '');
            $confirmPassword = (string)($_POST['confirm_password'] ?? '');

            if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                $error = 'Vui long nhap day du thong tin mat khau.';
                require 'views/auth/force_change_password.php';
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $error = 'Mat khau moi va xac nhan khong khop.';
                require 'views/auth/force_change_password.php';
                return;
            }

            $policy = validatePasswordPolicy($newPassword);
            if (!$policy['ok']) {
                $error = 'Mat khau moi phai co it nhat 8 ky tu, gom chu hoa, chu thuong, so va ky tu dac biet.';
                require 'views/auth/force_change_password.php';
                return;
            }

            $user = $this->model->findById((int)($_SESSION['user_id'] ?? 0));
            if (!$user || !password_verify($currentPassword, (string)($user['mat_khau'] ?? ''))) {
                $error = 'Mat khau hien tai khong dung.';
                require 'views/auth/force_change_password.php';
                return;
            }

            if (password_verify($newPassword, (string)$user['mat_khau'])) {
                $error = 'Mat khau moi phai khac mat khau hien tai.';
                require 'views/auth/force_change_password.php';
                return;
            }

            $this->model->updatePassword((int)$user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
            unset($_SESSION['force_password_change']);
            $_SESSION['success'] = 'Da doi mat khau thanh cong.';
            logSecurityEvent('password_changed_after_forced_policy', ['user_id' => (int)$user['id']]);

            redirectToRoleHome('tour/index');
        }

        require 'views/auth/force_change_password.php';
    }
    

    public function profile() {
        requireLogin();
        $user = $this->model->findById($_SESSION['user_id']);
        require 'views/auth/profile.php';
    }
}
