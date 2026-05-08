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

    public function quanLyNguoiDung() {
        // 1. Lấy tham số tìm kiếm và lọc từ URL (GET)
        // Các tên biến PHẢI khớp với tên trong form của View: name="search" và name="role"
        $search = trim($_GET['search'] ?? '');
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';

        // 2. Load Model và gọi phương thức lọc
            require_once __DIR__ . '/../models/NguoiDung.php';
        $nguoiDungModel = new NguoiDung();

        // Phương thức này cần được bạn tạo trong NguoiDung.php
        $users = $nguoiDungModel->getFilteredUsers($search, $role, $status);
        $userStats = $nguoiDungModel->getUserStats($search, $role, $status);

        // 3. Truyền các biến cần thiết xuống View
        // View của bạn cần $users, $search, và $role để hiển thị dữ liệu và giữ trạng thái form.
        // Nếu bạn không dùng framework, cách đơn giản nhất là khai báo chúng:

        // $users đã có
        // $search đã có
        // $role đã có

        // 4. Load View
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
