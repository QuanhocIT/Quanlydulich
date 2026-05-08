<?php
/**
 * AdminNhatKyTourController
 * Handles all admin routes for nhật ký tour management.
 */
class AdminNhatKyTourController {

    public function __construct() {
        requireRole('Admin');
    }

    private function requirePostCsrf(string $redirectAct = 'admin/quanLyNhatKyTour'): void {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF token không hợp lệ.';
            header('Location: index.php?act=' . $redirectAct);
            exit;
        }
    }

    // ========== QUẢN LÝ NHẬT KÝ TOUR ==========

    public function quanLyNhatKyTour() {
        $conn = connectDB();

        // Lấy filter
        $filter_tour = $_GET['tour_id'] ?? '';
        $filter_hdv = $_GET['hdv_id'] ?? '';
        $filter_loai = $_GET['loai_nhat_ky'] ?? '';
        $filter_tu_ngay = $_GET['tu_ngay'] ?? '';
        $filter_den_ngay = $_GET['den_ngay'] ?? '';

        // Đồng bộ biến filter cho view
        $tourId = $filter_tour;
        $hdvId = $filter_hdv;
        $loaiNhatKy = $filter_loai;
        $tuNgay = $filter_tu_ngay;
        $denNgay = $filter_den_ngay;

        // Build query
        $sql = "SELECT nkt.*, t.ten_tour, nd.ho_ten as hdv_ten
                FROM nhat_ky_tour nkt
                LEFT JOIN tour t ON nkt.tour_id = t.tour_id
                LEFT JOIN nhan_su ns ON nkt.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE 1=1";
        $params = [];

        if ($filter_tour) {
            $sql .= " AND nkt.tour_id = ?";
            $params[] = $filter_tour;
        }
        if ($filter_hdv) {
            $sql .= " AND nkt.nhan_su_id = ?";
            $params[] = $filter_hdv;
        }
        if ($filter_loai) {
            $sql .= " AND nkt.loai_nhat_ky = ?";
            $params[] = $filter_loai;
        }
        if ($filter_tu_ngay) {
            $sql .= " AND DATE(nkt.ngay_ghi) >= ?";
            $params[] = $filter_tu_ngay;
        }
        if ($filter_den_ngay) {
            $sql .= " AND DATE(nkt.ngay_ghi) <= ?";
            $params[] = $filter_den_ngay;
        }

        $sql .= " ORDER BY nkt.ngay_ghi DESC, nkt.id DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $nhatKyList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Thống kê
        $stats = [
            'tong' => 0,
            'hanh_trinh' => 0,
            'su_co' => 0,
            'phan_hoi' => 0,
            'hoat_dong' => 0
        ];

        $sqlStats = "SELECT
                        COUNT(*) as tong,
                        SUM(CASE WHEN loai_nhat_ky = 'hanh_trinh' THEN 1 ELSE 0 END) as hanh_trinh,
                        SUM(CASE WHEN loai_nhat_ky = 'su_co' THEN 1 ELSE 0 END) as su_co,
                        SUM(CASE WHEN loai_nhat_ky = 'phan_hoi' THEN 1 ELSE 0 END) as phan_hoi,
                        SUM(CASE WHEN loai_nhat_ky = 'hoat_dong' THEN 1 ELSE 0 END) as hoat_dong
                     FROM nhat_ky_tour";
        $stmtStats = $conn->prepare($sqlStats);
        $stmtStats->execute();
        $statsResult = $stmtStats->fetch(PDO::FETCH_ASSOC);
        if ($statsResult) {
            $stats = array_merge($stats, $statsResult);
        }

        // Lấy danh sách tour cho filter
        require_once __DIR__ . '/../models/Tour.php';
        $tourModel = new Tour();
        $tours = $tourModel->getOptions(500);

        // Lấy danh sách HDV cho filter
        require_once __DIR__ . '/../models/NhanSu.php';
        $nhanSuModel = new NhanSu();
        $hdvList = $nhanSuModel->getOptions('HDV', 500);

        require 'views/admin/quan_ly_nhat_ky_tour.php';
    }

    /**
     * Form thêm/sửa nhật ký tour - Admin
     */
    public function formNhatKyTour() {
        $conn = connectDB();
        $id = $_GET['id'] ?? 0;
        $entry = null;

        if ($id > 0) {
            $sql = "SELECT nkt.*, t.ten_tour, nd.ho_ten as hdv_ten
                    FROM nhat_ky_tour nkt
                    LEFT JOIN tour t ON nkt.tour_id = t.tour_id
                    LEFT JOIN nhan_su ns ON nkt.nhan_su_id = ns.nhan_su_id
                    LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                    WHERE nkt.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Lấy danh sách tour
        require_once __DIR__ . '/../models/Tour.php';
        $tourModel = new Tour();
        $tours = $tourModel->getOptions(500);

        // Lấy danh sách HDV
        require_once __DIR__ . '/../models/NhanSu.php';
        $nhanSuModel = new NhanSu();
        $hdvList = $nhanSuModel->getOptions('HDV', 500);

        require 'views/admin/form_nhat_ky_tour.php';
    }

    /**
     * Chi tiết nhật ký tour - Admin
     */
    public function chiTietNhatKyTour() {
        $conn = connectDB();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'Nhật ký không hợp lệ.';
            header('Location: index.php?act=admin/quanLyNhatKyTour');
            exit;
        }

        $sql = "SELECT nkt.*,
                       t.ten_tour, t.tour_id,
                       nd.ho_ten AS hdv_ten, nd.email AS hdv_email, nd.so_dien_thoai AS hdv_sdt
                FROM nhat_ky_tour nkt
                LEFT JOIN tour t ON nkt.tour_id = t.tour_id
                LEFT JOIN nhan_su ns ON nkt.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE nkt.id = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            $_SESSION['error'] = 'Không tìm thấy nhật ký.';
            header('Location: index.php?act=admin/quanLyNhatKyTour');
            exit;
        }

        require 'views/admin/chi_tiet_nhat_ky_tour.php';
    }

    /**
     * Lưu nhật ký tour - Admin
     */
    public function saveNhatKyTour() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyNhatKyTour');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyNhatKyTour');

        $conn = connectDB();
        $id = $_POST['id'] ?? 0;

        // Xử lý upload hình ảnh
        $imageUrls = [];
        if (isset($_FILES['hinh_anh']) && !empty($_FILES['hinh_anh']['name'][0])) {
            $uploadDir = 'uploads/nhat_ky/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $maxFiles = min(count($_FILES['hinh_anh']['name']), 5);
            for ($i = 0; $i < $maxFiles; $i++) {
                if ($_FILES['hinh_anh']['error'][$i] === UPLOAD_ERR_OK) {
                    $singleFile = [
                        'tmp_name' => $_FILES['hinh_anh']['tmp_name'][$i],
                        'name'     => $_FILES['hinh_anh']['name'][$i],
                        'size'     => $_FILES['hinh_anh']['size'][$i],
                        'error'    => $_FILES['hinh_anh']['error'][$i],
                        'type'     => $_FILES['hinh_anh']['type'][$i],
                    ];
                    $savedPath = uploadFile($singleFile, 'uploads/nhat_ky');
                    if ($savedPath !== null) {
                        $imageUrls[] = $savedPath;
                    }
                }
            }
        }

        $data = [
            'tour_id'      => $_POST['tour_id'] ?? 0,
            'nhan_su_id'   => $_POST['nhan_su_id'] ?? 0,
            'loai_nhat_ky' => $_POST['loai_nhat_ky'] ?? 'hanh_trinh',
            'tieu_de'      => $_POST['tieu_de'] ?? '',
            'noi_dung'     => $_POST['noi_dung'] ?? '',
            'ngay_ghi'     => $_POST['ngay_ghi'] ?? date('Y-m-d H:i:s'),
            'cach_xu_ly'   => $_POST['cach_xu_ly'] ?? null,
            'hinh_anh'     => !empty($imageUrls) ? json_encode($imageUrls) : null,
        ];

        try {
            if ($id > 0) {
                // Update
                if (!empty($imageUrls)) {
                    // Xóa hình ảnh cũ nếu có
                    $sqlOld = "SELECT hinh_anh FROM nhat_ky_tour WHERE id = ?";
                    $stmtOld = $conn->prepare($sqlOld);
                    $stmtOld->execute([$id]);
                    $oldEntry = $stmtOld->fetch(PDO::FETCH_ASSOC);
                    if ($oldEntry && !empty($oldEntry['hinh_anh'])) {
                        $oldImages = json_decode($oldEntry['hinh_anh'], true);
                        if ($oldImages && is_array($oldImages)) {
                            foreach ($oldImages as $img) {
                                if (file_exists($img)) {
                                    unlink($img);
                                }
                            }
                        }
                    }

                    $sql = "UPDATE nhat_ky_tour SET
                            tour_id = ?, nhan_su_id = ?, loai_nhat_ky = ?, tieu_de = ?,
                            noi_dung = ?, ngay_ghi = ?, cach_xu_ly = ?, hinh_anh = ?
                            WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([
                        $data['tour_id'], $data['nhan_su_id'], $data['loai_nhat_ky'],
                        $data['tieu_de'], $data['noi_dung'], $data['ngay_ghi'],
                        $data['cach_xu_ly'], $data['hinh_anh'], $id,
                    ]);
                } else {
                    $sql = "UPDATE nhat_ky_tour SET
                            tour_id = ?, nhan_su_id = ?, loai_nhat_ky = ?, tieu_de = ?,
                            noi_dung = ?, ngay_ghi = ?, cach_xu_ly = ?
                            WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([
                        $data['tour_id'], $data['nhan_su_id'], $data['loai_nhat_ky'],
                        $data['tieu_de'], $data['noi_dung'], $data['ngay_ghi'],
                        $data['cach_xu_ly'], $id,
                    ]);
                }
                $_SESSION['success'] = 'Cập nhật nhật ký thành công';
            } else {
                // Insert
                $sql = "INSERT INTO nhat_ky_tour
                        (tour_id, nhan_su_id, loai_nhat_ky, tieu_de, noi_dung, ngay_ghi, cach_xu_ly, hinh_anh)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $data['tour_id'], $data['nhan_su_id'], $data['loai_nhat_ky'],
                    $data['tieu_de'], $data['noi_dung'], $data['ngay_ghi'],
                    $data['cach_xu_ly'], $data['hinh_anh'],
                ]);
                $_SESSION['success'] = 'Thêm nhật ký thành công';
            }

            if (!$result) {
                $_SESSION['error'] = 'Lỗi khi lưu nhật ký';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: index.php?act=admin/quanLyNhatKyTour');
        exit;
    }

    /**
     * Xóa nhật ký tour - Admin
     */
    public function deleteNhatKyTour() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyNhatKyTour');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyNhatKyTour');

        $conn = connectDB();
        $id = $_POST['id'] ?? 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'Thiếu ID nhật ký';
            header('Location: index.php?act=admin/quanLyNhatKyTour');
            exit;
        }

        try {
            // Lấy thông tin nhật ký để xóa hình ảnh
            $sql = "SELECT hinh_anh FROM nhat_ky_tour WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($entry) {
                // Xóa hình ảnh
                if (!empty($entry['hinh_anh'])) {
                    $images = json_decode($entry['hinh_anh'], true);
                    if ($images && is_array($images)) {
                        foreach ($images as $img) {
                            if (file_exists($img)) {
                                unlink($img);
                            }
                        }
                    }
                }

                // Xóa nhật ký
                $sql = "DELETE FROM nhat_ky_tour WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([$id]);

                $_SESSION[$result ? 'success' : 'error'] = $result
                    ? 'Xóa nhật ký thành công'
                    : 'Lỗi khi xóa nhật ký';
            } else {
                $_SESSION['error'] = 'Không tìm thấy nhật ký';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }

        header('Location: index.php?act=admin/quanLyNhatKyTour');
        exit;
    }
}
