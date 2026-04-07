<?php
require_once 'models/CongNoHDV.php';
require_once 'models/Tour.php';
require_once 'models/KhachHang.php';
require_once 'models/HDV.php';
class CongNoHDVController {
    private $congNoModel;
    private $tourModel;
    private $hdvModel;

    private function requirePostCsrf($redirectAct) {
        $scopedToken = $_POST['_csrf_token'] ?? '';
        $globalToken = $_POST['_csrf_global'] ?? '';

        if (!verifyCsrfToken($scopedToken, 'cong_no_hdv_form') && !verifyCsrfToken($globalToken, 'global_form')) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=' . $redirectAct);
            exit;
        }
    }

    public function __construct() {
        requireLogin();
        $this->congNoModel = new CongNoHDV();
        $this->tourModel = new Tour();
        $this->hdvModel = new HDV();
    }
    // HDV xem và gửi hóa đơn công nợ
    public function thanhToanHDV() {
        requireRole('HDV');
        $hdv_id = $_SESSION['user_id'];
        $tours = $this->tourModel->getToursByHDV($hdv_id);
        $congNoHDVs = $this->congNoModel->getByHDV($hdv_id);
        require 'views/hdv/thanh_toan_cong_no.php';
    }
    // HDV gửi hóa đơn
    public function guiHoaDon() {
        requireRole('HDV');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/thanhToanHDV');
            exit;
        }

        $this->requirePostCsrf('hdv/thanhToanHDV');

        $hdv_id = $_SESSION['user_id'];
        $data = [
            'tour_id' => requestId('tour_id', 0, 'POST') ?? 0,
            'hdv_id' => $hdv_id,
            'so_tien' => requestMoney('so_tien', 0, 'POST', 0.01),
            'loai_cong_no' => requestString('loai_cong_no', '', 'POST'),
            'anh_hoa_don' => requestString('anh_hoa_don', '', 'POST'),
            'trang_thai' => 'ChoDuyet',
            'ghi_chu' => requestString('ghi_chu', '', 'POST') ?: null
        ];

        if ($data['tour_id'] <= 0 || $data['so_tien'] === null || $data['loai_cong_no'] === '') {
            $_SESSION['error'] = 'Thong tin hoa don cong no khong hop le.';
            header('Location: index.php?act=hdv/thanhToanHDV');
            exit;
        }

        $this->congNoModel->create($data);
        $_SESSION['success'] = 'Gửi hóa đơn thành công, chờ admin duyệt!';
        header('Location: index.php?act=hdv/thanhToanHDV');
        exit;
    }
    // Admin duyệt hóa đơn
    public function duyetHoaDon() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyCongNoHDV');
            exit;
        }

        $this->requirePostCsrf('admin/quanLyCongNoHDV');

        $id = requestId('id', 0, 'POST') ?? 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'Hoa don khong hop le.';
            header('Location: index.php?act=admin/quanLyCongNoHDV');
            exit;
        }

        $this->congNoModel->approve($id);
        $_SESSION['success'] = 'Đã duyệt hóa đơn!';
        header('Location: index.php?act=admin/quanLyCongNoHDV');
        exit;
    }
    // Admin từ chối hóa đơn
    public function tuChoiHoaDon() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyCongNoHDV');
            exit;
        }

        $this->requirePostCsrf('admin/quanLyCongNoHDV');

        $id = requestId('id', 0, 'POST') ?? 0;
        $ly_do = requestString('ly_do', '', 'POST');
        if ($id <= 0 || $ly_do === '') {
            $_SESSION['error'] = 'Thong tin tu choi hoa don khong hop le.';
            header('Location: index.php?act=admin/quanLyCongNoHDV');
            exit;
        }

        $this->congNoModel->reject($id, $ly_do);
        $_SESSION['success'] = 'Đã từ chối hóa đơn!';
        header('Location: index.php?act=admin/quanLyCongNoHDV');
        exit;
    }
    // Admin xem danh sách hóa đơn chờ duyệt
    public function quanLyCongNoHDV() {
        requireRole('Admin');
        $hoaDons = $this->congNoModel->getChoDuyet();
        require 'views/admin/quan_ly_cong_no_hdv.php';
    }
}
