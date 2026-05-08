<?php
/**
 * AdminYeuCauTourController
 * Manages customer tour requests (yêu cầu tour).
 */
class AdminYeuCauTourController {

    public function __construct() {
        requireRole('Admin');
    }

    // ========== QUẢN LÝ YÊU CẦU TOUR TỪ KHÁCH HÀNG ==========

    public function quanLyYeuCauTour() {
        require_once 'models/ThongBao.php';

        $thongBaoModel = new ThongBao();

        $filters = [
            'trang_thai' => $_GET['trang_thai'] ?? '',
            'search' => trim($_GET['search'] ?? ''),
            'limit' => 100,
        ];

        $yeuCauList = $thongBaoModel->getYeuCauTour($filters);
        $tongYeuCau = count($yeuCauList);
        $chuaXuLy = $thongBaoModel->countYeuCauTourChuaXuLy();

        require 'views/admin/quan_ly_yeu_cau_tour.php';
    }

    public function yeuCauTourSnapshot() {
        header('Content-Type: application/json; charset=utf-8');
        require_once 'models/ThongBao.php';

        try {
            $thongBaoModel = new ThongBao();
            $filters = [
                'trang_thai' => $_GET['trang_thai'] ?? '',
                'search' => trim((string)($_GET['search'] ?? '')),
                'limit' => 100,
            ];

            $yeuCauList = $thongBaoModel->getYeuCauTour($filters);
            $tongYeuCau = count($yeuCauList);
            $chuaXuLy = (int)$thongBaoModel->countYeuCauTourChuaXuLy();

            $items = array_map(static function ($yc) {
                return [
                    'id' => (int)($yc['id'] ?? 0),
                    'tieu_de' => (string)($yc['tieu_de'] ?? ''),
                    'noi_dung' => (string)($yc['noi_dung'] ?? ''),
                    'trang_thai' => (string)($yc['trang_thai'] ?? ''),
                    'created_at' => (string)($yc['created_at'] ?? ''),
                    'nguoi_gui_ten' => (string)($yc['nguoi_gui_ten'] ?? ''),
                    'nguoi_gui_email' => (string)($yc['nguoi_gui_email'] ?? ''),
                    'nguoi_gui_phone' => (string)($yc['nguoi_gui_phone'] ?? ''),
                ];
            }, $yeuCauList ?: []);

            echo json_encode([
                'success' => true,
                'tong_yeu_cau' => $tongYeuCau,
                'chua_xu_ly' => $chuaXuLy,
                'da_xu_ly' => max(0, $tongYeuCau - $chuaXuLy),
                'items' => $items,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'tong_yeu_cau' => 0,
                'chua_xu_ly' => 0,
                'da_xu_ly' => 0,
                'items' => [],
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    /**
     * Xem chi tiết yêu cầu tour và phản hồi
     */
    public function chiTietYeuCauTour() {
        require_once 'models/ThongBao.php';
        require_once 'models/Tour.php';

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'ID yêu cầu không hợp lệ.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit;
        }

        $thongBaoModel = new ThongBao();
        $yeuCau = $thongBaoModel->findById($id);

        $isTourRequest = !empty($yeuCau) && (($yeuCau['tieu_de'] ?? '') === 'Yêu cầu tour theo mong muốn');
        $complaintTitle = (string)($yeuCau['tieu_de'] ?? '');
        $complaintContent = (string)($yeuCau['noi_dung'] ?? '');
        $isTransferComplaint = !empty($yeuCau) && (
            strpos($complaintTitle, 'Khieu nai chuyen khoan sai noi dung') === 0
            || strpos($complaintTitle, 'Khiếu nại chuyển khoản sai nội dung') === 0
            || strpos($complaintContent, '[KHIEU NAI CHUYEN KHOAN SAI NOI DUNG]') === 0
        );

        if (!$yeuCau || (!$isTourRequest && !$isTransferComplaint)) {
            $_SESSION['error'] = 'Yêu cầu không tồn tại.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit;
        }

        // Parse thông tin từ nội dung
        $thongTin = [];
        foreach (explode("\n", $yeuCau['noi_dung'] ?? '') as $row) {
            $kv = explode(": ", $row, 2);
            if (count($kv) == 2) {
                $thongTin[$kv[0]] = $kv[1];
            }
        }

        $tourModel = new Tour();
        $tourList = $tourModel->getAll(200, 0);

        require 'views/admin/chi_tiet_yeu_cau_tour.php';
    }

    /**
     * Xử lý phản hồi yêu cầu tour
     */
    public function phanHoiYeuCauTour() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit;
        }

        require_once 'models/ThongBao.php';
        require_once 'models/Tour.php';

        $yeuCauId = isset($_POST['yeu_cau_id']) ? (int)$_POST['yeu_cau_id'] : 0;
        $phanHoi = trim($_POST['phan_hoi'] ?? '');
        $trangThai = $_POST['trang_thai'] ?? 'DaXuLy';

        if ($yeuCauId <= 0) {
            $_SESSION['error'] = 'ID yêu cầu không hợp lệ.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit;
        }

        if (empty($phanHoi)) {
            $_SESSION['error'] = 'Vui lòng nhập nội dung phản hồi.';
            header('Location: index.php?act=admin/chiTietYeuCauTour&id=' . $yeuCauId);
            exit;
        }

        $thongBaoModel = new ThongBao();
        $result = $thongBaoModel->updatePhanHoi(
            $yeuCauId,
            $phanHoi,
            $_SESSION['user_id'] ?? null,
            $trangThai
        );

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? 'Đã gửi phản hồi thành công!'
            : 'Có lỗi xảy ra khi gửi phản hồi.';

        header('Location: index.php?act=admin/chiTietYeuCauTour&id=' . $yeuCauId);
        exit;
    }

    /**
     * Tạo tour mới từ yêu cầu của khách hàng
     */
    public function taoTourTuYeuCau() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit;
        }

        require_once 'models/ThongBao.php';
        require_once 'models/Tour.php';

        $yeuCauId = isset($_POST['yeu_cau_id']) ? (int)$_POST['yeu_cau_id'] : 0;

        if ($yeuCauId <= 0) {
            $_SESSION['error'] = 'ID yêu cầu không hợp lệ.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit;
        }

        $thongBaoModel = new ThongBao();
        $yeuCau = $thongBaoModel->findById($yeuCauId);

        if (!$yeuCau) {
            $_SESSION['error'] = 'Yêu cầu không tồn tại.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit;
        }

        // Parse thông tin từ yêu cầu
        $thongTin = [];
        foreach (explode("\n", $yeuCau['noi_dung'] ?? '') as $row) {
            $kv = explode(": ", $row, 2);
            if (count($kv) == 2) {
                $thongTin[$kv[0]] = $kv[1];
            }
        }

        $tourModel = new Tour();
        $tourData = [
            'ten_tour' => $_POST['ten_tour'] ?? ($thongTin['Địa điểm'] ?? 'Tour mới'),
            'loai_tour' => $_POST['loai_tour'] ?? 'TrongNuoc',
            'mo_ta' => $_POST['mo_ta'] ?? 'Tour được tạo từ yêu cầu của khách hàng',
            'gia_co_ban' => isset($_POST['gia_co_ban']) ? (float)$_POST['gia_co_ban'] : 0,
            'trang_thai' => 'HoatDong',
            'tao_boi' => $_SESSION['user_id'] ?? null,
        ];

        $tourId = $tourModel->insert($tourData);

        if ($tourId) {
            $thongBao = new ThongBao();
            $phanHoi = "Chúng tôi đã tạo tour mới dựa trên yêu cầu của bạn. Vui lòng xem chi tiết: "
                     . "index.php?act=khachHang/chiTietTour&id=" . $tourId;
            $thongBao->updatePhanHoi(
                $yeuCauId,
                $phanHoi,
                $_SESSION['user_id'] ?? null,
                'DaXuLy'
            );
            $_SESSION['success'] = 'Đã tạo tour mới và thông báo cho khách hàng!';
            header('Location: index.php?act=admin/chiTietTour&id=' . $tourId);
            exit;
        } else {
            $_SESSION['error'] = 'Không thể tạo tour mới.';
            header('Location: index.php?act=admin/chiTietYeuCauTour&id=' . $yeuCauId);
            exit;
        }
    }
}
