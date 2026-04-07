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

                // Nếu mật khẩu đã hash (bcrypt,...), dùng password_verify
                if (!empty($stored) && password_verify($password, $stored)) {
                    $authenticated = true;
                } elseif ($stored === $password) {
                    // Trường hợp dữ liệu mẫu cũ lưu mật khẩu plaintext
                    $authenticated = true;
                    // Cập nhật lại thành hash an toàn
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $this->model->updatePassword($user['id'], $newHash);
                }

                if ($authenticated) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['vai_tro'];
                    $_SESSION['user_name'] = $user['ho_ten'];

                    if ($user['vai_tro'] === 'KhachHang') {
                        $khachHang = $this->khachHangModel->findByNguoiDungId($user['id']);
                        if ($khachHang) {
                            $_SESSION['khach_hang_id'] = $khachHang['khach_hang_id'];
                        }
                    }

                    // Redirect theo vai trò
                    switch ($user['vai_tro']) {
                        case 'Admin':
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

            $email = validateEmail($_POST['email'] ?? '');
            if ($email === null) {
                setValidationErrors(['email' => 'invalid'], 'Email khong hop le.');
                $error = "Email không hợp lệ.";
                require 'views/auth/register.php';
                return;
            }

            $soDienThoai = validatePhone($_POST['so_dien_thoai'] ?? '');
            if (!empty($_POST['so_dien_thoai']) && $soDienThoai === null) {
                setValidationErrors(['so_dien_thoai' => 'invalid'], 'So dien thoai khong hop le.');
                $error = "Số điện thoại không hợp lệ.";
                require 'views/auth/register.php';
                return;
            }

            $hoTen = requestString('ho_ten', '', 'POST');
            $password = (string)($_POST['password'] ?? '');
            if ($hoTen === '' || mb_strlen($hoTen) > 120 || strlen($password) < 6) {
                setValidationErrors([
                    'ho_ten' => $hoTen === '' || mb_strlen($hoTen) > 120 ? 'invalid' : null,
                    'password' => strlen($password) < 6 ? 'min:6' : null,
                ], 'Thong tin dang ky khong hop le.');
                $error = "Thông tin đăng ký không hợp lệ.";
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
                $_SESSION['user_id'] = $userId;
                $_SESSION['role'] = 'KhachHang';
                $khachHangId = $this->khachHangModel->insert([
                    'nguoi_dung_id' => $userId
                ]);
                $_SESSION['khach_hang_id'] = $khachHangId;
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
        session_destroy();
        header('Location: index.php?act=auth/login');
        exit();
    }
    

    public function profile() {
        requireLogin();
        $user = $this->model->findById($_SESSION['user_id']);
        require 'views/auth/profile.php';
    }
}
