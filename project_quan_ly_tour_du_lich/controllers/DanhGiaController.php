<?php
require_once 'models/DanhGia.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

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
        if (!class_exists(Dompdf::class)) {
            $_SESSION['error'] = 'Khong tim thay thu vien PDF. Vui long kiem tra composer install.';
            header('Location: index.php?act=admin/danhGia/baoCao&loai=' . urlencode((string)$loai));
            exit();
        }

        $fileName = 'bao-cao-danh-gia-' . $loai . '-' . date('Y-m-d') . '.pdf';
        $html = $this->renderPdfHtml($data, $loai);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($fileName, ['Attachment' => true]);
        exit();
    }

    private function renderPdfHtml($data, $loai) {
        $title = 'Bao cao danh gia - ' . ucfirst((string)$loai);
        $rowsHtml = '';

        if (!empty($data['danh_gia_list']) && is_array($data['danh_gia_list'])) {
            foreach ($data['danh_gia_list'] as $dg) {
                $rowsHtml .= '<tr>'
                    . '<td>' . htmlspecialchars(date('d/m/Y', strtotime((string)($dg['ngay_danh_gia'] ?? date('Y-m-d')))), ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td>' . htmlspecialchars((string)($dg['ho_ten'] ?? $dg['ten_khach_hang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td>' . htmlspecialchars((string)($dg['ten_tour'] ?? $dg['loai_danh_gia'] ?? $loai), ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td style="text-align:center">' . (int)($dg['diem'] ?? 0) . '/5</td>'
                    . '<td>' . nl2br(htmlspecialchars((string)($dg['noi_dung'] ?? ''), ENT_QUOTES, 'UTF-8')) . '</td>'
                    . '</tr>';
            }
        } elseif ($loai === 'tour' && !empty($data['tour'])) {
            $rowsHtml .= '<tr><td colspan="5">Khong co danh gia chi tiet cho tour nay.</td></tr>';
        } elseif ($loai === 'ncc' && !empty($data['nha_cung_cap'])) {
            $rowsHtml .= '<tr><td colspan="5">Khong co danh gia chi tiet cho nha cung cap nay.</td></tr>';
        } elseif ($loai === 'nhan_su' && !empty($data['nhan_su'])) {
            $rowsHtml .= '<tr><td colspan="5">Khong co danh gia chi tiet cho nhan su nay.</td></tr>';
        } else {
            $title = 'Bao cao danh gia tong hop';
            $rowsHtml .= '<tr><td colspan="5">Bao cao tong hop khong co danh sach chi tiet de xuat PDF. Vui long dung Excel neu can du lieu tong hop day du.</td></tr>';
        }

        return '<!DOCTYPE html>'
            . '<html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>'
            . '<style>'
            . 'body{font-family:"DejaVu Sans",sans-serif;font-size:12px;color:#111;}'
            . 'h1{font-size:18px;margin:0 0 8px 0;}'
            . '.meta{margin:0 0 16px 0;color:#555;}'
            . 'table{width:100%;border-collapse:collapse;margin-top:12px;}'
            . 'th,td{border:1px solid #ccc;padding:8px;vertical-align:top;}'
            . 'th{background:#f3f3f3;text-align:left;}'
            . '</style></head><body>'
            . '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
            . '<div class="meta">Ngay xuat: ' . date('d/m/Y H:i') . '</div>'
            . '<table><thead><tr><th>Ngay</th><th>Khach hang</th><th>Doi tuong</th><th>Diem</th><th>Noi dung</th></tr></thead><tbody>'
            . $rowsHtml
            . '</tbody></table></body></html>';
    }
}
