<?php
require_once 'models/DanhGia.php';

class DanhGiaController {
    private $model;
    
    public function __construct() {
        $this->model = new DanhGia();
    }
    
    // Danh sách tất cả đánh giá (Admin)
    public function index() {
        requireRole('Admin');
        
        // Debug
        error_log("DanhGiaController::index() called");
        error_log("Session role: " . (currentUserRole() ?? 'not set'));
        
        // Lấy tham số lọc
        $filters = [
            'loai' => $_GET['loai'] ?? '',
            'diem_min' => $_GET['diem_min'] ?? '',
            'diem_max' => $_GET['diem_max'] ?? '',
            'tu_ngay' => $_GET['tu_ngay'] ?? '',
            'den_ngay' => $_GET['den_ngay'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $danhGiaList = $this->model->filter($filters);
        error_log("DanhGiaList count: " . count($danhGiaList));
        
        // Thống kê
        $stats = $this->model->getStatistics();
        error_log("Stats: " . print_r($stats, true));
        
        require 'views/admin/quan_ly_danh_gia.php';
    }
    
    // Báo cáo tổng hợp
    public function baoCao() {
        requireRole('Admin');
        
        $loai = $_GET['loai'] ?? 'tour';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if ($loai === 'tour' && $id) {
            $report = $this->model->getReportByTour($id);
        } elseif ($loai === 'ncc' && $id) {
            $report = $this->model->getReportByNhaCungCap($id);
        } elseif ($loai === 'nhan_su' && $id) {
            $report = $this->model->getReportByNhanSu($id);
        } else {
            $report = $this->model->getOverallReport();
        }
        
        require 'views/admin/bao_cao_danh_gia.php';
    }
    
    // Chi tiết đánh giá
    public function chiTiet() {
        requireRole('Admin');
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $danhGia = $this->model->findById($id);
        
        if (!$danhGia) {
            $_SESSION['error'] = 'Không tìm thấy đánh giá';
            header('Location: index.php?act=admin/danhGia');
            exit();
        }
        
        require 'views/admin/chi_tiet_danh_gia.php';
    }
    
    // Trả lời đánh giá
    public function traLoi() {
        requireRole('Admin');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $phan_hoi_admin = $_POST['phan_hoi_admin'] ?? '';
            
            $result = $this->model->updateAdminResponse($id, $phan_hoi_admin);
            
            if ($result) {
                $_SESSION['success'] = 'Đã trả lời đánh giá';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra';
            }
            
            header('Location: index.php?act=admin/danhGia/chiTiet&id=' . $id);
            exit();
        }
    }
    
    // Xóa đánh giá
    public function xoa() {
        requireRole('Admin');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/danhGia');
            exit();
        }

        if (!verifyCsrfToken($_POST['_csrf_global'] ?? '', 'global_form')
            && !verifyCsrfToken($_POST['_csrf_token'] ?? '', 'admin_form')) {
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF).';
            header('Location: index.php?act=admin/danhGia');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Đã xóa đánh giá';
        } else {
            $_SESSION['error'] = 'Không thể xóa đánh giá';
        }
        
        header('Location: index.php?act=admin/danhGia');
        exit();
    }
    
    // Export báo cáo
    public function export() {
        requireRole('Admin');
        
        $loai = $_GET['loai'] ?? 'tour';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $format = $_GET['format'] ?? 'pdf';
        
        // Get data
        if ($loai === 'tour' && $id) {
            $data = $this->model->getReportByTour($id);
        } elseif ($loai === 'ncc' && $id) {
            $data = $this->model->getReportByNhaCungCap($id);
        } else {
            $data = $this->model->getOverallReport();
        }
        
        if ($format === 'excel') {
            $this->exportExcel($data, $loai);
        } else {
            $this->exportPDF($data, $loai);
        }
    }
    
    private function exportExcel($data, $loai) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="bao-cao-danh-gia-' . $loai . '-' . date('Y-m-d') . '.xls"');
        
        echo "<html><body>";
        echo "<h2>Báo cáo đánh giá - " . ucfirst($loai) . "</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Ngày</th><th>Khách hàng</th><th>Loại</th><th>Điểm</th><th>Nội dung</th></tr>";

                if (!empty($data['danh_gia_list']) && is_array($data['danh_gia_list'])) {
                    foreach ($data['danh_gia_list'] as $dg) {
                        echo "<tr>";
                        echo "<td>" . date('d/m/Y', strtotime($dg['ngay_danh_gia'])) . "</td>";
                        echo "<td>" . htmlspecialchars($dg['ho_ten']) . "</td>";
                        echo "<td>" . htmlspecialchars($dg['loai']) . "</td>";
                        echo "<td>" . $dg['diem'] . "/5</td>";
                        echo "<td>" . htmlspecialchars($dg['noi_dung']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='color:#999;text-align:center'>Không có dữ liệu đánh giá</td></tr>";
                }
        
        echo "</table>";
        echo "</body></html>";
        exit();
    }
    
    private function exportPDF($data, $loai) {
        // Simple PDF export using HTML
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="bao-cao-danh-gia-' . $loai . '-' . date('Y-m-d') . '.pdf"');
        
        // Simplified - would use a proper PDF library in production
        $_SESSION['info'] = 'Tính năng xuất PDF đang được phát triển. Vui lòng sử dụng Excel.';
        header('Location: index.php?act=admin/danhGia/baoCao&loai=' . $loai);
        exit();
    }
}
