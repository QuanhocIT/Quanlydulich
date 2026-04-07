<?php
require_once 'models/Tour.php';

class TourController {
    private $model;
    
    public function __construct() {
        $this->model = new Tour();
    }
    
    public function index() {
        if (isLoggedIn()) {
            redirectToRoleHome('auth/login');
        }

        header('Location: index.php?act=auth/login');
        exit();
    }
    
    public function show() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$id) {
            header('Location: index.php?act=tour/index');
            exit();
        }
        $tour = $this->model->findById($id);
        if (!$tour) {
            header('Location: index.php?act=tour/index');
            exit();
        }
        $lichTrinhList = $this->model->getLichTrinhByTourId($id);
        $lichKhoiHanhList = $this->model->getLichKhoiHanhByTourId($id);
        $hinhAnhList = $this->model->getHinhAnhByTourId($id);
        $anhChinh = $this->chonAnhChinh($hinhAnhList);
        $yeuCauList = $this->model->getYeuCauDacBietByTourId($id);
        $nhatKyList = $this->model->getNhatKyTourByTourId($id);
        $hdvInfo = $this->model->getHDVByTourId($id);
        require 'views/khach_hang/chi_tiet_tour.php';
    }
    
    public function create() {
        requireRole('Admin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hanhDong = $_POST['hanh_dong'] ?? 'create';
            $data = [
                'ten_tour' => $_POST['ten_tour'] ?? '',
                'loai_tour' => $_POST['loai_tour'] ?? 'TrongNuoc',
                'mo_ta' => $_POST['mo_ta'] ?? '',
                'gia_co_ban' => isset($_POST['gia_co_ban']) ? (float)$_POST['gia_co_ban'] : 0,
                'chinh_sach' => $_POST['chinh_sach'] ?? null,
                'id_nha_cung_cap' => isset($_POST['id_nha_cung_cap']) && $_POST['id_nha_cung_cap'] !== '' ? (int)$_POST['id_nha_cung_cap'] : null,
                'tao_boi' => $_SESSION['user_id'] ?? null,
                'trang_thai' => $_POST['trang_thai'] ?? 'HoatDong'
            ];

            $lichTrinhPost = isset($_POST['lich_trinh']) && is_array($_POST['lich_trinh']) ? array_values($_POST['lich_trinh']) : [];
            $lichKhoiHanhPost = isset($_POST['lich_khoi_hanh']) && is_array($_POST['lich_khoi_hanh']) ? $_POST['lich_khoi_hanh'] : [];
            $hinhAnhPost = isset($_POST['hinh_anh']) && is_array($_POST['hinh_anh']) ? array_values($_POST['hinh_anh']) : [];

            $this->xuLyUploadHinhAnh($hinhAnhPost, 'hinh_anh_file');
            $hasImageError = false;
            if (isset($_SESSION['image_upload_error'])) {
                $hasImageError = true;
                unset($_SESSION['image_upload_error']);
            }

            if ($hanhDong === 'preview') {
                $tour = $data;
                $lichTrinhList = $lichTrinhPost;
            $lichKhoiHanhList = !empty($lichKhoiHanhPost) ? [$lichKhoiHanhPost] : [];
            $hinhAnhList = $hinhAnhPost;
            $anhChinh = $this->chonAnhChinh($hinhAnhList);
                require 'views/admin/tao_tour.php';
                return;
            }
            if ($hasImageError) {
                $tour = $data;
                $lichTrinhList = $lichTrinhPost;
                $lichKhoiHanhList = !empty($lichKhoiHanhPost) ? [$lichKhoiHanhPost] : [];
                $hinhAnhList = $hinhAnhPost;
                $anhChinh = $this->chonAnhChinh($hinhAnhList);
                require 'views/admin/tao_tour.php';
                return;
            }

            try {
                if (method_exists($this->model->conn, 'beginTransaction')) {
                    $this->model->conn->beginTransaction();
                }

                $inserted = $this->model->insert($data);
                if (!$inserted) {
                    throw new Exception('Không thể tạo tour mới.');
                }

                $tourId = (int)$this->model->getLastInsertId();

                if (!empty($lichTrinhPost)) {
                    foreach ($lichTrinhPost as $index => $lichTrinh) {
                        $lichTrinh['ngay_thu'] = isset($lichTrinh['ngay_thu']) && $lichTrinh['ngay_thu'] !== '' ? (int)$lichTrinh['ngay_thu'] : ($index + 1);
                        if (!empty($lichTrinh['dia_diem'])) {
                            $this->model->insertLichTrinh($tourId, $lichTrinh);
                        }
                    }
                }

                if (!empty($lichKhoiHanhPost) && is_array($lichKhoiHanhPost)) {
                    if (!empty($lichKhoiHanhPost['ngay_khoi_hanh'])) {
                        $this->model->insertLichKhoiHanh($tourId, $lichKhoiHanhPost);
                    }
                }

                if (!empty($hinhAnhPost)) {
                    foreach ($hinhAnhPost as $hinhAnh) {
                        if (!empty($hinhAnh['url_anh'])) {
                            $this->model->insertHinhAnh($tourId, $hinhAnh);
                        }
                    }
                }

                if (method_exists($this->model->conn, 'commit')) {
                    $this->model->conn->commit();
                }

                $qrCreated = $this->generateTourQrFile($tourId);
                $successMessage = 'Tạo tour thành công!';
                if (!$qrCreated) {
                    $successMessage .= ' (Chưa tạo được mã QR tự động, vui lòng tạo lại trong trang chi tiết tour).';
                }

                $_SESSION['success'] = $successMessage;
                header('Location: index.php?act=admin/quanLyTour');
                exit();
            
            } catch (Exception $e) {
                if (method_exists($this->model->conn, 'rollBack') && $this->model->conn->inTransaction()) {
                    $this->model->conn->rollBack();
                }

                $_SESSION['error'] = 'Lỗi khi tạo tour: ' . $e->getMessage();
                header('Location: index.php?act=tour/create');
                exit();
            }
        } else {
            $tour = null;
            $lichTrinhList = [];
            $lichKhoiHanhList = [];
            $hinhAnhList = [];
            $anhChinh = null;
            require 'views/admin/tao_tour.php';
        }
    }
    
    public function update() {
        requireRole('Admin');
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        $id = $id !== null ? (int)$id : null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $hanhDong = $_POST['hanh_dong'] ?? 'update';
            $data = [
                'ten_tour' => $_POST['ten_tour'] ?? '',
                'loai_tour' => $_POST['loai_tour'] ?? 'TrongNuoc',
                'mo_ta' => $_POST['mo_ta'] ?? '',
                'gia_co_ban' => isset($_POST['gia_co_ban']) ? (float)$_POST['gia_co_ban'] : 0,
                'chinh_sach' => $_POST['chinh_sach'] ?? null,
                'id_nha_cung_cap' => isset($_POST['id_nha_cung_cap']) && $_POST['id_nha_cung_cap'] !== '' ? (int)$_POST['id_nha_cung_cap'] : null,
                'trang_thai' => $_POST['trang_thai'] ?? 'HoatDong'
            ];
            
            $lichTrinhPost = isset($_POST['lich_trinh']) && is_array($_POST['lich_trinh']) ? array_values($_POST['lich_trinh']) : [];
            $lichKhoiHanhPost = isset($_POST['lich_khoi_hanh']) && is_array($_POST['lich_khoi_hanh']) ? $_POST['lich_khoi_hanh'] : [];
            $hinhAnhPost = isset($_POST['hinh_anh']) && is_array($_POST['hinh_anh']) ? array_values($_POST['hinh_anh']) : [];

            $this->xuLyUploadHinhAnh($hinhAnhPost, 'hinh_anh_file');
            $hasImageError = false;
            if (isset($_SESSION['image_upload_error'])) {
                $hasImageError = true;
                unset($_SESSION['image_upload_error']);
            }

            if ($hanhDong === 'preview') {
                $tour = array_merge($this->model->findById($id) ?: [], $data);
                $lichTrinhList = $lichTrinhPost;
                $lichKhoiHanhList = !empty($lichKhoiHanhPost) ? [$lichKhoiHanhPost] : $this->model->getLichKhoiHanhByTourId($id);
                $hinhAnhList = $hinhAnhPost;
                $anhChinh = $this->chonAnhChinh($hinhAnhList);
                require 'views/admin/tao_tour.php';
                return;
            }
            if ($hasImageError) {
                $tour = array_merge($this->model->findById($id) ?: [], $data);
                $lichTrinhList = $lichTrinhPost;
                $lichKhoiHanhList = !empty($lichKhoiHanhPost) ? [$lichKhoiHanhPost] : $this->model->getLichKhoiHanhByTourId($id);
                $hinhAnhList = $hinhAnhPost;
                $anhChinh = $this->chonAnhChinh($hinhAnhList);
                require 'views/admin/tao_tour.php';
                return;
            }

            $this->model->update($id, $data);
            
            // Xóa và thêm lại lịch trình
            $this->model->deleteLichTrinhByTourId($id);
            if (!empty($lichTrinhPost)) {
                foreach ($lichTrinhPost as $index => $lichTrinh) {
                    $lichTrinh['ngay_thu'] = isset($lichTrinh['ngay_thu']) && $lichTrinh['ngay_thu'] !== '' ? (int)$lichTrinh['ngay_thu'] : ($index + 1);
                    if (!empty($lichTrinh['dia_diem'])) {
                        $this->model->insertLichTrinh($id, $lichTrinh);
                    }
                }
            }
            
            // Xóa và thêm lại lịch khởi hành
            $this->model->deleteLichKhoiHanhByTourId($id);
            if (!empty($lichKhoiHanhPost) && is_array($lichKhoiHanhPost)) {
                if (!empty($lichKhoiHanhPost['ngay_khoi_hanh'])) {
                    $this->model->insertLichKhoiHanh($id, $lichKhoiHanhPost);
                }
            }
            
            // Xóa và thêm lại hình ảnh
            $this->model->deleteHinhAnhByTourId($id);
            if (!empty($hinhAnhPost)) {
                foreach ($hinhAnhPost as $hinhAnh) {
                    if (!empty($hinhAnh['url_anh'])) {
                        $this->model->insertHinhAnh($id, $hinhAnh);
                    }
                }
            }
            
       
            // Redirect với thông báo thành công
            $_SESSION['success'] = 'Cập nhật tour thành công!';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $id) {
            $tour = $this->model->findById($id);
            if (!$tour) {
                header('Location: index.php?act=admin/quanLyTour');
                exit();
            }
            $lichTrinhList = $this->model->getLichTrinhByTourId($id);
            $lichKhoiHanhList = $this->model->getLichKhoiHanhByTourId($id);
            $hinhAnhList = $this->model->getHinhAnhByTourId($id);
            $anhChinh = $this->chonAnhChinh($hinhAnhList);
            require 'views/admin/tao_tour.php';
        }
    }
    
    public function delete() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'tour_delete')
            && !verifyCsrfToken($_POST['_csrf_global'] ?? '', 'global_form')) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        if ($id) {
            try {
                $tour = $this->model->findById($id);
                $result = $this->model->delete($id);
                if ($result) {
                    if ($tour && !empty($tour['qr_code_path'])) {
                        deleteFile($tour['qr_code_path']);
                    }
                    $_SESSION['success'] = 'Xóa tour thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể xóa tour.';
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Lỗi khi xóa tour: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'ID tour không hợp lệ.';
        }
        header('Location: index.php?act=admin/quanLyTour');
        exit();
    }

    public function generateQr() {
        requireRole('Admin');
        $tourId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($tourId <= 0) {
            $_SESSION['error'] = 'Tour không hợp lệ.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        $tour = $this->model->findById($tourId);
        if (!$tour) {
            $_SESSION['error'] = 'Không tìm thấy tour.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        $result = $this->generateTourQrFile($tourId);
        if ($result) {
            $_SESSION['success'] = 'Đã cập nhật mã QR cho tour.';
        } else {
            $_SESSION['error'] = 'Không thể tạo mã QR. Vui lòng thử lại sau.';
        }

        header('Location: index.php?act=admin/chiTietTour&id=' . $tourId);
        exit();
    }

    private function xuLyUploadHinhAnh(array &$hinhAnhPost, string $inputName): void {
        if (!isset($_FILES[$inputName])) {
            return;
        }
    
        $files = $_FILES[$inputName];
    
        // Chuẩn hóa về dạng mảng (dù upload 1 hay nhiều)
        $arrFiles = [];
        foreach ($files['name'] as $i => $name) {
            $arrFiles[] = [
                'name'     => $name,
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }
    
        $uploadFolder = dirname(__DIR__) . '/public/uploads/tour_images/';
        if (!is_dir($uploadFolder) && !mkdir($uploadFolder, 0755, true)) {
            $_SESSION['image_upload_error'] = 'Không thể tạo thư mục lưu ảnh.';
            return;
        }
    
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
        foreach ($arrFiles as $index => $file) {
    
            // Không up ảnh nào ở vị trí này → bỏ qua
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
    
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['image_upload_error'] = 'Tải ảnh thất bại.';
                continue;
            }
    
            // Giới hạn size 5MB
            if ($file['size'] > 5 * 1024 * 1024) {
                $_SESSION['image_upload_error'] = 'Ảnh vượt quá dung lượng 5MB.';
                continue;
            }
    
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                $_SESSION['image_upload_error'] = 'Định dạng ảnh không hợp lệ.';
                continue;
            }
    
            $newName = uniqid('tour_', true) . '.' . $ext;
            $fullPath = $uploadFolder . $newName;
    
            // Lưu file
            if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                // Gán vào mảng gửi vào
                // Ví dụ: $hinhAnhPost = [ ['url_anh'=>'...'], ['url_anh'=>'...'] ]
                // Hoặc chỉ là mảng rỗng để push vào
                $hinhAnhPost[$index]['url_anh'] = 'public/uploads/tour_images/' . $newName;
    
            } else {
                $_SESSION['image_upload_error'] = 'Không thể lưu ảnh lên máy chủ.';
            }
        }
    }
    

    private function chonAnhChinh(array $hinhAnhList) {
        foreach ($hinhAnhList as $anh) {
            if (!empty($anh['url_anh'])) {
                return $anh;
            }
        }
        return null;
    }

    private function getTourBookingLink($tourId) {
        return rtrim(BASE_URL, '/') . '/index.php?act=tour/show&id=' . $tourId;
    }

    private function generateTourQrFile($tourId) {
        $tour = $this->model->findById($tourId);
        if (!$tour) {
            return false;
        }

        $qrDir = PATH_ROOT . 'public/uploads/qr/';
        if (!is_dir($qrDir) && !mkdir($qrDir, 0777, true)) {
            return false;
        }

        $fileName = 'tour_' . $tourId . '_' . time() . '.png';
        $filePath = $qrDir . $fileName;
        $bookingUrl = $this->getTourBookingLink($tourId);
        $qrService = 'https://api.qrserver.com/v1/create-qr-code/?size=480x480&data=' . urlencode($bookingUrl);
        $context = stream_context_create(['http' => ['timeout' => 10]]);
        $qrImage = @file_get_contents($qrService, false, $context);

        if ($qrImage === false || file_put_contents($filePath, $qrImage) === false) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            return false;
        }

        if (!empty($tour['qr_code_path'])) {
            deleteFile($tour['qr_code_path']);
        }

        $relativePath = 'public/uploads/qr/' . $fileName;
        $this->model->updateQrCodePath($tourId, $relativePath);
        return $relativePath;
    }

    // Tạo lịch khởi hành cho tour
    public function taoLichKhoiHanh() {
        $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
        
        if ($tourId <= 0) {
            $_SESSION['error'] = 'ID tour không hợp lệ.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }
        
        $tour = $this->model->findById($tourId);
        if (!$tour) {
            $_SESSION['error'] = 'Tour không tồn tại.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/LichKhoiHanh.php';
            $lichKhoiHanhModel = new LichKhoiHanh();
            
            $data = [
                'tour_id' => $tourId,
                'ngay_khoi_hanh' => $_POST['ngay_khoi_hanh'] ?? null,
                'gio_xuat_phat' => $_POST['gio_xuat_phat'] ?? null,
                'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?? null,
                'gio_ket_thuc' => $_POST['gio_ket_thuc'] ?? null,
                'diem_tap_trung' => $_POST['diem_tap_trung'] ?? '',
                'so_cho' => isset($_POST['so_cho']) ? (int)$_POST['so_cho'] : 50,
                'hdv_id' => isset($_POST['hdv_id']) && $_POST['hdv_id'] !== '' ? (int)$_POST['hdv_id'] : null,
                'trang_thai' => $_POST['trang_thai'] ?? 'SapKhoiHanh',
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            $id = $lichKhoiHanhModel->insert($data);
            if ($id) {
                $_SESSION['success'] = 'Tạo lịch khởi hành thành công.';
                header('Location: index.php?act=tour/chiTietLichKhoiHanh&id=' . $id . '&tour_id=' . $tourId);
                exit();
            } else {
                $_SESSION['error'] = 'Không thể tạo lịch khởi hành.';
            }
        }
        
        require_once 'models/NhanSu.php';
        $nhanSuModel = new NhanSu();
        $hdvList = $nhanSuModel->getByRole('HDV');
        
        require 'views/admin/tao_lich_khoi_hanh_tour.php';
    }

    // Chi tiết lịch khởi hành với phân bổ
    public function chiTietLichKhoiHanh() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
        
        if ($id <= 0 || $tourId <= 0) {
            $_SESSION['error'] = 'Thông tin không hợp lệ.';
            header('Location: index.php?act=admin/chiTietTour&id=' . $tourId);
            exit();
        }
        
        require_once 'models/LichKhoiHanh.php';
        require_once 'models/PhanBoNhanSu.php';
        require_once 'models/PhanBoDichVu.php';
        require_once 'models/NhanSu.php';
        require_once 'models/NhaCungCap.php';
        
        $lichKhoiHanhModel = new LichKhoiHanh();
        $phanBoNhanSuModel = new PhanBoNhanSu();
        $phanBoDichVuModel = new PhanBoDichVu();
        $nhanSuModel = new NhanSu();
        $nhaCungCapModel = new NhaCungCap();
        
        $lichKhoiHanh = $lichKhoiHanhModel->getWithDetails($id);
        if (!$lichKhoiHanh || $lichKhoiHanh['tour_id'] != $tourId) {
            $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
            header('Location: index.php?act=admin/chiTietTour&id=' . $tourId);
            exit();
        }
        
        $tour = $this->model->findById($tourId);
        $phanBoNhanSu = $phanBoNhanSuModel->getByLichKhoiHanh($id);
        $phanBoDichVu = $phanBoDichVuModel->getByLichKhoiHanh($id);
        $nhanSuList = $nhanSuModel->getAll();
        $nhaCungCapList = $nhaCungCapModel->getAll();
        $tongChiPhi = $phanBoDichVuModel->getTongChiPhi($id);
        
        require 'views/admin/chi_tiet_lich_khoi_hanh.php';
    }

    // Phân bổ nhân sự cho lịch khởi hành
    public function phanBoNhanSuLichKhoiHanh() {
        // Nếu là GET (click từ màn booking) thì chuyển sang màn chi tiết tour để chọn lịch khởi hành
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $tourIdFromGet = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($tourIdFromGet > 0) {
                header('Location: index.php?act=admin/chiTietTour&id=' . $tourIdFromGet);
                exit();
            }

            // Không có tour_id hợp lệ -> về trang quản lý booking
            $_SESSION['error'] = 'Thiếu thông tin tour để phân bổ nhân sự.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        require_once 'models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();
        
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
        $nhanSuId = isset($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : 0;
        $vaiTro = $_POST['vai_tro'] ?? 'Khac';
        $ghiChu = $_POST['ghi_chu'] ?? null;
        
        if ($lichKhoiHanhId > 0 && $nhanSuId > 0) {
            $data = [
                'lich_khoi_hanh_id' => $lichKhoiHanhId,
                'nhan_su_id' => $nhanSuId,
                'vai_tro' => $vaiTro,
                'ghi_chu' => $ghiChu
            ];
            
            $result = $phanBoNhanSuModel->insert($data);
            if ($result) {
                $_SESSION['success'] = 'Phân bổ nhân sự thành công.';
            } else {
                $_SESSION['error'] = 'Không thể phân bổ nhân sự.';
            }
        } else {
            $_SESSION['error'] = 'Thông tin không hợp lệ.';
        }
        
        header('Location: index.php?act=tour/chiTietLichKhoiHanh&id=' . $lichKhoiHanhId . '&tour_id=' . $tourId);
        exit();
    }

    // Cập nhật trạng thái phân bổ nhân sự
    public function updateTrangThaiNhanSuLichKhoiHanh() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/PhanBoNhanSu.php';
            $phanBoNhanSuModel = new PhanBoNhanSu();
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $trangThai = $_POST['trang_thai'] ?? 'ChoXacNhan';
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
            
            if ($id > 0) {
                $result = $phanBoNhanSuModel->updateTrangThai($id, $trangThai);
                if ($result) {
                    $_SESSION['success'] = 'Cập nhật trạng thái thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể cập nhật trạng thái.';
                }
            }
            
            header('Location: index.php?act=tour/chiTietLichKhoiHanh&id=' . $lichKhoiHanhId . '&tour_id=' . $tourId);
            exit();
        }
    }

    // Phân bổ dịch vụ cho lịch khởi hành
    public function phanBoDichVuLichKhoiHanh() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/PhanBoDichVu.php';
            $phanBoDichVuModel = new PhanBoDichVu();
            
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
            
            $data = [
                'lich_khoi_hanh_id' => $lichKhoiHanhId,
                'nha_cung_cap_id' => isset($_POST['nha_cung_cap_id']) && $_POST['nha_cung_cap_id'] !== '' ? (int)$_POST['nha_cung_cap_id'] : null,
                'loai_dich_vu' => $_POST['loai_dich_vu'] ?? 'Khac',
                'ten_dich_vu' => $_POST['ten_dich_vu'] ?? '',
                'so_luong' => isset($_POST['so_luong']) ? (int)$_POST['so_luong'] : 1,
                'don_vi' => $_POST['don_vi'] ?? null,
                'ngay_bat_dau' => $_POST['ngay_bat_dau'] ?? null,
                'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?? null,
                'gio_bat_dau' => $_POST['gio_bat_dau'] ?? null,
                'gio_ket_thuc' => $_POST['gio_ket_thuc'] ?? null,
                'dia_diem' => $_POST['dia_diem'] ?? null,
                'gia_tien' => isset($_POST['gia_tien']) ? (float)$_POST['gia_tien'] : null,
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            if ($lichKhoiHanhId > 0) {
                $result = $phanBoDichVuModel->insert($data);
                if ($result) {
                    $_SESSION['success'] = 'Phân bổ dịch vụ thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể phân bổ dịch vụ.';
                }
            } else {
                $_SESSION['error'] = 'Thông tin không hợp lệ.';
            }
            
            header('Location: index.php?act=tour/chiTietLichKhoiHanh&id=' . $lichKhoiHanhId . '&tour_id=' . $tourId);
            exit();
        }
    }

    // Cập nhật trạng thái phân bổ dịch vụ
    public function updateTrangThaiDichVuLichKhoiHanh() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/PhanBoDichVu.php';
            $phanBoDichVuModel = new PhanBoDichVu();
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $trangThai = $_POST['trang_thai'] ?? 'ChoXacNhan';
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
            
            if ($id > 0) {
                $result = $phanBoDichVuModel->updateTrangThai($id, $trangThai);
                if ($result) {
                    $_SESSION['success'] = 'Cập nhật trạng thái thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể cập nhật trạng thái.';
                }
            }
            
            header('Location: index.php?act=tour/chiTietLichKhoiHanh&id=' . $lichKhoiHanhId . '&tour_id=' . $tourId);
            exit();
        }
    }

    // Xóa phân bổ nhân sự
    public function deleteNhanSuLichKhoiHanh() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'tour_delete')
            && !verifyCsrfToken($_POST['_csrf_global'] ?? '', 'global_form')) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
        
        if ($id > 0) {
            require_once 'models/PhanBoNhanSu.php';
            $phanBoNhanSuModel = new PhanBoNhanSu();
            $result = $phanBoNhanSuModel->delete($id);
            if ($result) {
                $_SESSION['success'] = 'Xóa phân bổ nhân sự thành công.';
            } else {
                $_SESSION['error'] = 'Không thể xóa phân bổ nhân sự.';
            }
        }
        
        header('Location: index.php?act=tour/chiTietLichKhoiHanh&id=' . $lichKhoiHanhId . '&tour_id=' . $tourId);
        exit();
    }

    // Xóa phân bổ dịch vụ
    public function deleteDichVuLichKhoiHanh() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'tour_delete')
            && !verifyCsrfToken($_POST['_csrf_global'] ?? '', 'global_form')) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
        
        if ($id > 0) {
            require_once 'models/PhanBoDichVu.php';
            $phanBoDichVuModel = new PhanBoDichVu();
            $result = $phanBoDichVuModel->delete($id);
            if ($result) {
                $_SESSION['success'] = 'Xóa phân bổ dịch vụ thành công.';
            } else {
                $_SESSION['error'] = 'Không thể xóa phân bổ dịch vụ.';
            }
        }
        
        header('Location: index.php?act=tour/chiTietLichKhoiHanh&id=' . $lichKhoiHanhId . '&tour_id=' . $tourId);
        exit();
    }

    // Trang đặt tour online qua QR Code
    public function bookOnline() {
        $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
        
        if ($tourId <= 0) {
            $_SESSION['error'] = 'Tour không tồn tại.';
            header('Location: index.php?act=tour/index');
            exit();
        }
        
        $tour = $this->model->findById($tourId);
        if (!$tour) {
            $_SESSION['error'] = 'Tour không tồn tại.';
            header('Location: index.php?act=tour/index');
            exit();
        }
        
        $lichTrinhList = $this->model->getLichTrinhByTourId($tourId);
        $lichKhoiHanhList = $this->model->getLichKhoiHanhByTourId($tourId);
        $hinhAnhList = $this->model->getHinhAnhByTourId($tourId);
        $anhChinh = $this->chonAnhChinh($hinhAnhList);
    }

    // Clone tour - Sao chép tour cũ để tạo tour mới
    public function clone() {
        $tourId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($tourId <= 0) {
            $_SESSION['error'] = 'ID tour không hợp lệ.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }
        
        $originalTour = $this->model->findById($tourId);
        if (!$originalTour) {
            $_SESSION['error'] = 'Tour không tồn tại.';
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }
        
        try {
            if (method_exists($this->model->conn, 'beginTransaction')) {
                $this->model->conn->beginTransaction();
            }
            
            // Tạo tour mới với thông tin từ tour gốc
            $newTourData = [
                'ten_tour' => $originalTour['ten_tour'] . ' (Bản sao)',
                'loai_tour' => $originalTour['loai_tour'] ?? 'TrongNuoc',
                'mo_ta' => $originalTour['mo_ta'] ?? '',
                'gia_co_ban' => $originalTour['gia_co_ban'] ?? 0,
                'chinh_sach' => $originalTour['chinh_sach'] ?? null,
                'id_nha_cung_cap' => $originalTour['id_nha_cung_cap'] ?? null,
                'tao_boi' => $_SESSION['user_id'] ?? null,
                'trang_thai' => 'HoatDong'
            ];
            
            $inserted = $this->model->insert($newTourData);
            if (!$inserted) {
                throw new Exception('Không thể tạo tour mới.');
            }
            
            $newTourId = (int)$this->model->getLastInsertId();
            
            // Clone lịch trình
            $lichTrinhList = $this->model->getLichTrinhByTourId($tourId);
            foreach ($lichTrinhList as $lichTrinh) {
                $this->model->insertLichTrinh($newTourId, [
                    'ngay_thu' => $lichTrinh['ngay_thu'],
                    'dia_diem' => $lichTrinh['dia_diem'],
                    'hoat_dong' => $lichTrinh['hoat_dong']
                ]);
            }
            
            // Clone hình ảnh (chỉ copy đường dẫn, không copy file)
            $hinhAnhList = $this->model->getHinhAnhByTourId($tourId);
            foreach ($hinhAnhList as $hinhAnh) {
                $this->model->insertHinhAnh($newTourId, [
                    'url_anh' => $hinhAnh['url_anh'],
                    'mo_ta' => $hinhAnh['mo_ta']
                ]);
            }
            
            if (method_exists($this->model->conn, 'commit')) {
                $this->model->conn->commit();
            }
            
            $_SESSION['success'] = 'Đã sao chép tour thành công. Bạn có thể chỉnh sửa tour mới.';
            header('Location: index.php?act=tour/update&id=' . $newTourId);
            exit();
            
        } catch (Exception $e) {
            if (method_exists($this->model->conn, 'rollBack') && $this->model->conn->inTransaction()) {
                $this->model->conn->rollBack();
            }
            
            $_SESSION['error'] = 'Lỗi khi sao chép tour: ' . $e->getMessage();
            header('Location: index.php?act=admin/quanLyTour');
            exit();
        }
    }

}
