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
