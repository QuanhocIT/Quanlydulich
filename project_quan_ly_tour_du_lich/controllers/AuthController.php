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
                $khachHangId = $this->khachHangModel->insert([
                    'nguoi_dung_id' => $userId
                ]);
                completeUserLoginSession($userId, 'KhachHang', $hoTen, [
                    'khach_hang_id' => $khachHangId,
                ]);
                header('Location: index.php?act=tour/index');
                exit();
            } else {
                $error = "Đăng ký thất bại. Vui lòng thử lại sau.";
                require 'views/auth/register.php';
                return;
            }
        } else {
            require 'views/auth/register.php';
        }
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
