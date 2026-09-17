<?php
class AdminNguoiDungController {
    public function __construct() {
        requireRole('Admin');
    }

    private function requirePostCsrf(string $redirectAct = 'admin/dashboard') {
        $scopedToken = $_POST['_csrf_token'] ?? '';
        $globalToken = $_POST['_csrf_global'] ?? '';

        $validScoped = verifyCsrfToken($scopedToken, 'admin_form');
        $validGlobal = verifyCsrfToken($globalToken, 'global_form');

        if (!$validScoped && !$validGlobal) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=' . urlencode($redirectAct));
            exit;
        }
    }

    public function apiUserList(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            require_once __DIR__ . '/../models/NguoiDung.php';
            $nguoiDungModel = new NguoiDung();

            $search = trim($_GET['search'] ?? '');
            $role = trim($_GET['role'] ?? '');
            $status = trim($_GET['status'] ?? '');

            $users = $nguoiDungModel->getFilteredUsers($search, $role, $status);
            $userStats = $nguoiDungModel->getUserStats($search, $role, $status);

            echo json_encode([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'userStats' => $userStats,
                    'currentUserId' => (int)($_SESSION['user_id'] ?? 0),
                    'csrfToken' => csrfToken('admin_form'),
                    'csrfGlobal' => csrfToken('global_form'),
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function apiToggleStatus(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $userId = (int)($input['user_id'] ?? 0);
            $status = trim((string)($input['status'] ?? ''));
            $adminId = (int)($_SESSION['user_id'] ?? 0);

            $allowedStatus = ['HoatDong', 'BiKhoa'];
            if ($userId <= 0 || !in_array($status, $allowedStatus, true)) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
                exit;
            }

            if ($userId === $adminId && $status === 'BiKhoa') {
                echo json_encode(['success' => false, 'message' => 'Không thể tự khóa tài khoản đang đăng nhập.']);
                exit;
            }

            require_once __DIR__ . '/../models/NguoiDung.php';
            $nguoiDungModel = new NguoiDung();
            $targetUser = $nguoiDungModel->findById($userId);
            if (!$targetUser) {
                echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại.']);
                exit;
            }

            $isUpdated = $nguoiDungModel->updateStatus($userId, $status);
            echo json_encode([
                'success' => (bool)$isUpdated,
                'message' => $isUpdated ? 'Đã cập nhật trạng thái tài khoản.' : 'Không thể cập nhật trạng thái.',
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function apiCreateUser(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $username = trim((string)($input['ten_dang_nhap'] ?? ''));
            $fullname = trim((string)($input['ho_ten'] ?? ''));
            $email = trim((string)($input['email'] ?? ''));
            $phone = trim((string)($input['so_dien_thoai'] ?? ''));
            $role = trim((string)($input['vai_tro'] ?? 'KhachHang'));
            $status = trim((string)($input['trang_thai'] ?? 'HoatDong'));
            $password = (string)($input['mat_khau'] ?? '');

            if ($username === '' || strlen($username) < 3) {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập phải có ít nhất 3 ký tự.']);
                exit;
            }

            if (!preg_match('/^[a-zA-Z0-9_\.\@\-]+$/', $username)) {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập chỉ được chứa chữ cái, số, dấu chấm, gạch ngang, gạch dưới.']);
                exit;
            }

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Địa chỉ email không đúng định dạng.']);
                exit;
            }

            if (strlen($password) < 6) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu khởi tạo phải có ít nhất 6 ký tự.']);
                exit;
            }

            $allowedRoles = ['Admin', 'HDV', 'KhachHang', 'NhaCungCap'];
            if (!in_array($role, $allowedRoles, true)) {
                $role = 'KhachHang';
            }

            $allowedStatus = ['HoatDong', 'BiKhoa'];
            if (!in_array($status, $allowedStatus, true)) {
                $status = 'HoatDong';
            }

            $conn = connectDB();

            // Kiểm tra trùng username
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM nguoi_dung WHERE ten_dang_nhap = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmtCheck->execute([$username]);
            if ((int)$stmtCheck->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại trong hệ thống.']);
                exit;
            }

            // Kiểm tra trùng email
            $stmtCheckEmail = $conn->prepare("SELECT COUNT(*) FROM nguoi_dung WHERE email = ? AND (is_deleted = 0 OR is_deleted IS NULL)");
            $stmtCheckEmail->execute([$email]);
            if ((int)$stmtCheckEmail->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng bởi tài khoản khác.']);
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmtInsert = $conn->prepare(
                "INSERT INTO nguoi_dung (ten_dang_nhap, ho_ten, email, so_dien_thoai, mat_khau, vai_tro, trang_thai, ngay_tao)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $ok = $stmtInsert->execute([$username, $fullname, $email, $phone, $hashedPassword, $role, $status]);
            if (!$ok) {
                echo json_encode(['success' => false, 'message' => 'Không thể tạo tài khoản người dùng trong cơ sở dữ liệu.']);
                exit;
            }

            $newUserId = (int)$conn->lastInsertId();

            // Đồng bộ bản ghi nghiệp vụ theo vai trò
            if ($role === 'HDV') {
                $trangThaiLamViec = ($status === 'HoatDong') ? 'SanSang' : 'TamNghi';
                $stmtNS = $conn->prepare("INSERT INTO nhan_su (nguoi_dung_id, vai_tro, loai_hdv, trang_thai_lam_viec) VALUES (?, 'HDV', 'TongHop', ?)");
                $stmtNS->execute([$newUserId, $trangThaiLamViec]);
            } elseif ($role === 'KhachHang') {
                $stmtKH = $conn->prepare("INSERT INTO khach_hang (nguoi_dung_id, ho_ten, email, so_dien_thoai, ngay_tao) VALUES (?, ?, ?, ?, NOW())");
                $stmtKH->execute([$newUserId, $fullname ?: $username, $email, $phone]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Đã tạo tài khoản ' . $username . ' (' . $role . ') thành công!',
                'user_id' => $newUserId
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    public function apiUpdateUser(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $userId = (int)($input['user_id'] ?? 0);
            $fullname = trim((string)($input['ho_ten'] ?? ''));
            $email = trim((string)($input['email'] ?? ''));
            $phone = trim((string)($input['so_dien_thoai'] ?? ''));
            $role = trim((string)($input['vai_tro'] ?? ''));
            $status = trim((string)($input['trang_thai'] ?? ''));
            $adminId = (int)($_SESSION['user_id'] ?? 0);

            if ($userId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Mã người dùng không hợp lệ.']);
                exit;
            }

            require_once __DIR__ . '/../models/NguoiDung.php';
            $nguoiDungModel = new NguoiDung();
            $targetUser = $nguoiDungModel->findById($userId);
            if (!$targetUser) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
                exit;
            }

            $conn = connectDB();

            // Kiểm tra trùng email nếu có thay đổi
            if ($email !== '' && $email !== ($targetUser['email'] ?? '')) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'Email không đúng định dạng.']);
                    exit;
                }
                $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM nguoi_dung WHERE email = ? AND id != ? AND (is_deleted = 0 OR is_deleted IS NULL)");
                $stmtCheck->execute([$email, $userId]);
                if ((int)$stmtCheck->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Email này đã được sử dụng bởi người dùng khác.']);
                    exit;
                }
            }

            // Quy tắc bảo vệ Admin
            if ($userId === $adminId && $role !== 'Admin' && $role !== '') {
                echo json_encode(['success' => false, 'message' => 'Bạn không thể tự giáng chức tài khoản Admin của chính mình.']);
                exit;
            }

            if (($targetUser['vai_tro'] ?? '') === 'Admin' && $role !== 'Admin' && $role !== '') {
                $stmtCountAdmin = $conn->query("SELECT COUNT(*) FROM nguoi_dung WHERE vai_tro = 'Admin' AND trang_thai = 'HoatDong' AND (is_deleted = 0 OR is_deleted IS NULL)");
                if ((int)$stmtCountAdmin->fetchColumn() <= 1) {
                    echo json_encode(['success' => false, 'message' => 'Không thể đổi vai trò của Admin duy nhất trong hệ thống.']);
                    exit;
                }
            }

            $updateFields = [];
            $params = [];
            if ($fullname !== '') {
                $updateFields[] = "ho_ten = ?";
                $params[] = $fullname;
            }
            if ($email !== '') {
                $updateFields[] = "email = ?";
                $params[] = $email;
            }
            $updateFields[] = "so_dien_thoai = ?";
            $params[] = $phone;

            if (in_array($role, ['Admin', 'HDV', 'KhachHang', 'NhaCungCap'], true)) {
                $updateFields[] = "vai_tro = ?";
                $params[] = $role;
            }

            if (in_array($status, ['HoatDong', 'BiKhoa'], true)) {
                if ($userId === $adminId && $status === 'BiKhoa') {
                    echo json_encode(['success' => false, 'message' => 'Không thể tự khóa tài khoản của chính mình.']);
                    exit;
                }
                $updateFields[] = "trang_thai = ?";
                $params[] = $status;
            }

            if (empty($updateFields)) {
                echo json_encode(['success' => false, 'message' => 'Không có dữ liệu nào được thay đổi.']);
                exit;
            }

            $params[] = $userId;
            $stmtUpdate = $conn->prepare("UPDATE nguoi_dung SET " . implode(", ", $updateFields) . " WHERE id = ?");
            $ok = $stmtUpdate->execute($params);

            // Đồng bộ hồ sơ liên kết
            if ($ok) {
                if (($targetUser['vai_tro'] ?? '') === 'HDV' || $role === 'HDV') {
                    $conn->prepare("UPDATE nhan_su SET ho_ten = ?, so_dien_thoai = ?, email = ? WHERE nguoi_dung_id = ?")
                        ->execute([$fullname ?: ($targetUser['ho_ten'] ?? ''), $phone, $email ?: ($targetUser['email'] ?? ''), $userId]);
                }
                if (($targetUser['vai_tro'] ?? '') === 'KhachHang' || $role === 'KhachHang') {
                    $conn->prepare("UPDATE khach_hang SET ho_ten = ?, so_dien_thoai = ?, email = ? WHERE nguoi_dung_id = ?")
                        ->execute([$fullname ?: ($targetUser['ho_ten'] ?? ''), $phone, $email ?: ($targetUser['email'] ?? ''), $userId]);
                }
            }

            echo json_encode([
                'success' => (bool)$ok,
                'message' => $ok ? 'Cập nhật thông tin tài khoản thành công.' : 'Không thể cập nhật thông tin.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    public function apiResetPassword(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $userId = (int)($input['user_id'] ?? 0);
            $newPassword = (string)($input['new_password'] ?? '');

            if ($userId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Mã người dùng không hợp lệ.']);
                exit;
            }

            if (strlen($newPassword) < 6) {
                echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.']);
                exit;
            }

            require_once __DIR__ . '/../models/NguoiDung.php';
            $nguoiDungModel = new NguoiDung();
            $targetUser = $nguoiDungModel->findById($userId);
            if (!$targetUser) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
                exit;
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $conn = connectDB();

            $stmt = $conn->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?");
            $ok = $stmt->execute([$hashedPassword, $userId]);

            echo json_encode([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã đặt lại mật khẩu mới cho tài khoản ' . ($targetUser['ten_dang_nhap'] ?? '') . '.' : 'Không thể đặt lại mật khẩu.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    public function apiDeleteUser(): void {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $userId = (int)($input['user_id'] ?? 0);
            $adminId = (int)($_SESSION['user_id'] ?? 0);

            if ($userId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Mã người dùng không hợp lệ.']);
                exit;
            }

            if ($userId === $adminId) {
                echo json_encode(['success' => false, 'message' => 'Bạn không thể tự xóa tài khoản của chính mình.']);
                exit;
            }

            require_once __DIR__ . '/../models/NguoiDung.php';
            $nguoiDungModel = new NguoiDung();
            $targetUser = $nguoiDungModel->findById($userId);
            if (!$targetUser) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
                exit;
            }

            $conn = connectDB();
            if (($targetUser['vai_tro'] ?? '') === 'Admin') {
                $stmtCountAdmin = $conn->query("SELECT COUNT(*) FROM nguoi_dung WHERE vai_tro = 'Admin' AND trang_thai = 'HoatDong' AND (is_deleted = 0 OR is_deleted IS NULL)");
                if ((int)$stmtCountAdmin->fetchColumn() <= 1) {
                    echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản Admin duy nhất của hệ thống.']);
                    exit;
                }
            }

            $ok = $nguoiDungModel->delete($userId);

            echo json_encode([
                'success' => (bool)$ok,
                'message' => $ok ? 'Đã xóa tài khoản người dùng thành công.' : 'Không thể xóa tài khoản.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    public function quanLyNguoiDung() {
        $search = trim($_GET['search'] ?? '');
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';

        require_once __DIR__ . '/../models/NguoiDung.php';
        $nguoiDungModel = new NguoiDung();

        $users = $nguoiDungModel->getFilteredUsers($search, $role, $status);
        $userStats = $nguoiDungModel->getUserStats($search, $role, $status);

        $vueUserManageData = [
            'users' => $users,
            'userStats' => $userStats,
            'currentUserId' => (int)($_SESSION['user_id'] ?? 0),
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'csrfToken' => csrfToken('admin_form'),
            'csrfGlobal' => csrfToken('global_form'),
        ];

        require __DIR__ . '/../views/admin/quan_ly_nguoi_dung.php';
    }

    public function capNhatTrangThaiNguoiDung() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyNguoiDung');

        require_once __DIR__ . '/../models/NguoiDung.php';
        $nguoiDungModel = new NguoiDung();

        $userId = requestInt('user_id', 0, 'POST');
        $status = requestString('status', '', 'POST');
        $adminId = (int)($_SESSION['user_id'] ?? 0);

        $allowedStatus = ['HoatDong', 'BiKhoa'];
        if ($userId <= 0 || !in_array($status, $allowedStatus, true)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Dữ liệu cập nhật không hợp lệ.',
            ];
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }

        if ($userId === $adminId && $status === 'BiKhoa') {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Không thể tự khóa tài khoản đang đăng nhập.',
            ];
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }

        $targetUser = $nguoiDungModel->findById($userId);
        if (!$targetUser) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Người dùng không tồn tại.',
            ];
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }

        $isUpdated = $nguoiDungModel->updateStatus($userId, $status);
        $_SESSION['flash'] = [
            'type' => $isUpdated ? 'success' : 'error',
            'message' => $isUpdated
                ? 'Đã cập nhật trạng thái tài khoản.'
                : 'Không thể cập nhật trạng thái tài khoản.',
        ];

        $query = [
            'act=admin/quanLyNguoiDung',
            'search=' . urlencode(requestString('search', '', 'POST')),
            'role=' . urlencode(requestString('role', '', 'POST')),
            'status=' . urlencode(requestString('status_filter', '', 'POST')),
        ];

        header('Location: index.php?' . implode('&', $query));
        exit;
    }
}
