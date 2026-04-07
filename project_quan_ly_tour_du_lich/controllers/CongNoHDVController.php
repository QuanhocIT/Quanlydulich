<?php
require_once 'models/CongNoHDV.php';
require_once 'models/Tour.php';
require_once 'models/KhachHang.php';
require_once 'models/HDV.php';
class CongNoHDVController {
    private $congNoModel;
    private $tourModel;
    private $hdvModel;
    public function __construct() {
        $this->congNoModel = new CongNoHDV();
        $this->tourModel = new Tour();
        $this->hdvModel = new HDV();
    }
    // HDV xem và gửi hóa đơn công nợ
    public function thanhToanHDV() {
        $hdv_id = $_SESSION['user_id'];
        $tours = $this->tourModel->getToursByHDV($hdv_id);
        $congNoHDVs = $this->congNoModel->getByHDV($hdv_id);
        require 'views/hdv/thanh_toan_cong_no.php';
    }
    // HDV gửi hóa đơn
    public function guiHoaDon() {
        $hdv_id = $_SESSION['user_id'];
        $data = [
            'tour_id' => $_POST['tour_id'],
            'hdv_id' => $hdv_id,
            'so_tien' => $_POST['so_tien'],
            'loai_cong_no' => $_POST['loai_cong_no'],
            'anh_hoa_don' => $_POST['anh_hoa_don'],
            'trang_thai' => 'ChoDuyet',
            'ghi_chu' => $_POST['ghi_chu'] ?? null
        ];
        $this->congNoModel->create($data);
        $_SESSION['success'] = 'Gửi hóa đơn thành công, chờ admin duyệt!';
        header('Location: index.php?act=hdv/thanhToanHDV');
        exit;
    }
    // Admin duyệt hóa đơn
    public function duyetHoaDon() {
        $id = $_GET['id'];
        $this->congNoModel->approve($id);
        $_SESSION['success'] = 'Đã duyệt hóa đơn!';
        header('Location: index.php?act=admin/quanLyCongNoHDV');
        exit;
    }
    // Admin từ chối hóa đơn
    public function tuChoiHoaDon() {
        $id = $_POST['id'];
        $ly_do = $_POST['ly_do'];
        $this->congNoModel->reject($id, $ly_do);
        $_SESSION['success'] = 'Đã từ chối hóa đơn!';
        header('Location: index.php?act=admin/quanLyCongNoHDV');
        exit;
    }
    // Admin xem danh sách hóa đơn chờ duyệt
    public function quanLyCongNoHDV() {
        $hoaDons = $this->congNoModel->getChoDuyet();
        require 'views/admin/quan_ly_cong_no_hdv.php';
    }
}
