<?php

class NhaCungCapController {
    private $nhaCungCapModel;
    private $dichVuCatalogModel;
    private $loaiDichVuAllowed = [
        'KhachSan',
        'NhaHang',
        'Xe',
        'Ve',
        'VeMayBay',
        'DiemThamQuan',
        'Visa',
        'BaoHiem',
        'Khac'
    ];

    private function requirePostCsrf($redirectAct = 'nhaCungCap/dashboard') {
        $scopedToken = $_POST['_csrf_token'] ?? '';
        $globalToken = $_POST['_csrf_global'] ?? '';

        $validScoped = verifyCsrfToken($scopedToken, 'supplier_form');
        $validGlobal = verifyCsrfToken($globalToken, 'global_form');

        if (!$validScoped && !$validGlobal) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=' . urlencode($redirectAct));
            exit();
        }
    }
    
    public function __construct() {
        requireRole('NhaCungCap');
        $this->nhaCungCapModel = new NhaCungCap();
        $this->dichVuCatalogModel = new DichVuNhaCungCap();
    }

    private function currentSupplier() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }

        $supplier = $this->nhaCungCapModel->findByUserId($userId);
        if (!$supplier) {
            $_SESSION['error'] = 'Không tìm thấy thông tin nhà cung cấp';
            header('Location: index.php?act=auth/login');
            exit();
        }

        return $supplier;
    }

    private function normalizeLoaiDichVu($value, $allowNull = false) {
        if (!$value) {
            return $allowNull ? null : 'Khac';
        }
        if (in_array($value, $this->loaiDichVuAllowed, true)) {
            return $value;
        }
        return $allowNull ? null : 'Khac';
    }

    private function normalizeDate($value) {
        $value = trim($value ?? '');
        return $value === '' ? null : $value;
    }

    private function normalizeTime($value) {
        $value = trim($value ?? '');
        return $value === '' ? null : $value;
    }
    
    // Dashboard
    public function dashboard() {
        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        
        // Thống kê
        $dichVuChoXacNhan = $this->nhaCungCapModel->getDichVu($nhaCungCapId, 'ChoXacNhan');
        $dichVuDaXacNhan = $this->nhaCungCapModel->getDichVu($nhaCungCapId, 'DaXacNhan');
        $congNo = $this->nhaCungCapModel->getTongCongNo($nhaCungCapId);
        $lichSu = $this->nhaCungCapModel->getLichSuHopTac($nhaCungCapId, 10);
        
        require 'views/nha_cung_cap/dashboard.php';
    }
    
    // Danh sách dịch vụ (báo giá)
    public function baoGia() {
        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        $trangThai = $_GET['trang_thai'] ?? null;
        $filterLoai = $_GET['loai'] ?? null;
        $keyword = trim($_GET['keyword'] ?? '');
        
        $filters = [
            'trang_thai' => $trangThai ?: null,
            'loai_dich_vu' => $this->normalizeLoaiDichVu($filterLoai, true),
            'keyword' => $keyword !== '' ? $keyword : null
        ];
        
        $dichVu = $this->nhaCungCapModel->getDichVu($nhaCungCapId, $filters);
        $baoGiaStats = $this->nhaCungCapModel->getBaoGiaStats($nhaCungCapId);
        $lichKhoiHanhOptions = $this->nhaCungCapModel->getUpcomingLichKhoiHanh(30);
        $catalogServices = $this->dichVuCatalogModel->getAllBySupplier($nhaCungCapId);
        $filterLoai = $filters['loai_dich_vu'];
        
        require 'views/nha_cung_cap/bao_gia.php';
    }
    
    // Xác nhận booking/dịch vụ
    public function xacNhanBooking() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=nhaCungCap/baoGia');
            exit();
        }

        $this->requirePostCsrf('nhaCungCap/baoGia');
        
        $this->currentSupplier();
        
        $dichVuId = $_POST['dich_vu_id'] ?? 0;
        $giaTien = $_POST['gia_tien'] ?? null;
        $action = $_POST['action'] ?? 'xac_nhan';
        
        if ($action === 'tu_choi') {
            $ghiChu = $_POST['ghi_chu'] ?? null;
            $result = $this->nhaCungCapModel->tuChoiDichVu($dichVuId, $ghiChu);
            if ($result) {
                $_SESSION['success'] = 'Đã từ chối dịch vụ';
            } else {
                $_SESSION['error'] = 'Không thể từ chối dịch vụ';
            }
        } else {
            $result = $this->nhaCungCapModel->xacNhanDichVu($dichVuId, $giaTien);
            if ($result) {
                $_SESSION['success'] = 'Đã xác nhận dịch vụ';
            } else {
                $_SESSION['error'] = 'Không thể xác nhận dịch vụ';
            }
        }
        
        header('Location: index.php?act=nhaCungCap/baoGia');
        exit();
    }
    
    // Cập nhật giá dịch vụ
    public function capNhatGia() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=nhaCungCap/baoGia');
            exit();
        }

        $this->requirePostCsrf('nhaCungCap/baoGia');
        
        $this->currentSupplier();
        
        $dichVuId = $_POST['dich_vu_id'] ?? 0;
        $giaTien = $_POST['gia_tien'] ?? 0;
        
        if ($giaTien <= 0) {
            $_SESSION['error'] = 'Giá tiền không hợp lệ';
            header('Location: index.php?act=nhaCungCap/baoGia');
            exit();
        }
        
        $result = $this->nhaCungCapModel->capNhatGiaDichVu($dichVuId, $giaTien);
        if ($result) {
            $_SESSION['success'] = 'Đã cập nhật giá dịch vụ';
        } else {
            $_SESSION['error'] = 'Không thể cập nhật giá dịch vụ';
        }
        
        header('Location: index.php?act=nhaCungCap/baoGia');
        exit();
    }
    
    // Quản lý dịch vụ
    public function dichVu() {
        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        $dichVu = $this->nhaCungCapModel->getDichVu($nhaCungCapId);
        $catalogServices = $this->dichVuCatalogModel->getAllBySupplier($nhaCungCapId);
        
        require 'views/nha_cung_cap/dich_vu.php';
    }
    
    public function storeDichVu() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        $this->requirePostCsrf('nhaCungCap/dichVu');

        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];

        $tenDichVu = trim($_POST['ten_dich_vu'] ?? '');
        if ($tenDichVu === '') {
            $_SESSION['error'] = 'Tên dịch vụ không được để trống';
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        $data = [
            'ten_dich_vu' => $tenDichVu,
            'mo_ta' => $_POST['mo_ta'] ?? null,
            'loai_dich_vu' => $this->normalizeLoaiDichVu($_POST['loai_dich_vu'] ?? null),
            'gia_tham_khao' => $_POST['gia_tham_khao'] ?? null,
            'don_vi_tinh' => $_POST['don_vi_tinh'] ?? null,
            'cong_suat_toi_da' => $_POST['cong_suat_toi_da'] ?? null,
            'thoi_gian_xu_ly' => $_POST['thoi_gian_xu_ly'] ?? null,
            'tai_lieu_dinh_kem' => $_POST['tai_lieu_dinh_kem'] ?? null,
            'trang_thai' => $_POST['trang_thai'] ?? 'HoatDong'
        ];

        try {
            $this->dichVuCatalogModel->create($nhaCungCapId, $data);
            $_SESSION['success'] = 'Đã thêm dịch vụ mới';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể thêm dịch vụ: ' . $e->getMessage();
        }

        header('Location: index.php?act=nhaCungCap/dichVu');
        exit();
    }

    public function updateDichVu() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        $this->requirePostCsrf('nhaCungCap/dichVu');

        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        $id = (int)($_POST['dich_vu_id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'Dịch vụ không hợp lệ';
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        $tenDichVu = trim($_POST['ten_dich_vu'] ?? '');
        if ($tenDichVu === '') {
            $_SESSION['error'] = 'Tên dịch vụ không được để trống';
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        $data = [
            'ten_dich_vu' => $tenDichVu,
            'mo_ta' => $_POST['mo_ta'] ?? null,
            'loai_dich_vu' => $this->normalizeLoaiDichVu($_POST['loai_dich_vu'] ?? null),
            'gia_tham_khao' => $_POST['gia_tham_khao'] ?? null,
            'don_vi_tinh' => $_POST['don_vi_tinh'] ?? null,
            'cong_suat_toi_da' => $_POST['cong_suat_toi_da'] ?? null,
            'thoi_gian_xu_ly' => $_POST['thoi_gian_xu_ly'] ?? null,
            'tai_lieu_dinh_kem' => $_POST['tai_lieu_dinh_kem'] ?? null,
            'trang_thai' => $_POST['trang_thai'] ?? 'HoatDong'
        ];

        $service = $this->dichVuCatalogModel->findById($id, $nhaCungCapId);
        if (!$service) {
            $_SESSION['error'] = 'Không tìm thấy dịch vụ';
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        try {
            $this->dichVuCatalogModel->update($id, $nhaCungCapId, $data);
            $_SESSION['success'] = 'Đã cập nhật dịch vụ';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể cập nhật dịch vụ: ' . $e->getMessage();
        }

        header('Location: index.php?act=nhaCungCap/dichVu');
        exit();
    }

    public function deleteDichVu() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        $this->requirePostCsrf('nhaCungCap/dichVu');

        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        $id = (int)($_POST['dich_vu_id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'Dịch vụ không hợp lệ';
            header('Location: index.php?act=nhaCungCap/dichVu');
            exit();
        }

        try {
            $this->dichVuCatalogModel->delete($id, $nhaCungCapId);
            $_SESSION['success'] = 'Đã xóa dịch vụ';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể xóa dịch vụ: ' . $e->getMessage();
        }

        header('Location: index.php?act=nhaCungCap/dichVu');
        exit();
    }

    public function storeBaoGiaThuCong() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=nhaCungCap/baoGia');
            exit();
        }

        $this->requirePostCsrf('nhaCungCap/baoGia');

        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];

        $lichKhoiHanhId = (int)($_POST['lich_khoi_hanh_id'] ?? 0);
        $tenDichVu = trim($_POST['ten_dich_vu'] ?? '');

        if ($lichKhoiHanhId <= 0 || $tenDichVu === '') {
            $_SESSION['error'] = 'Vui lòng chọn lịch khởi hành và nhập tên dịch vụ';
            header('Location: index.php?act=nhaCungCap/baoGia');
            exit();
        }

        $phanBoModel = new PhanBoDichVu();
        $data = [
            'lich_khoi_hanh_id' => $lichKhoiHanhId,
            'nha_cung_cap_id' => $nhaCungCapId,
            'loai_dich_vu' => $this->normalizeLoaiDichVu($_POST['loai_dich_vu'] ?? null),
            'ten_dich_vu' => $tenDichVu,
            'so_luong' => (int)($_POST['so_luong'] ?? 1),
            'don_vi' => $_POST['don_vi'] ?? null,
            'ngay_bat_dau' => $this->normalizeDate($_POST['ngay_bat_dau'] ?? null),
            'ngay_ket_thuc' => $this->normalizeDate($_POST['ngay_ket_thuc'] ?? null),
            'gio_bat_dau' => $this->normalizeTime($_POST['gio_bat_dau'] ?? null),
            'gio_ket_thuc' => $this->normalizeTime($_POST['gio_ket_thuc'] ?? null),
            'dia_diem' => $_POST['dia_diem'] ?? null,
            'gia_tien' => $_POST['gia_tien'] ?? null,
            'ghi_chu' => $_POST['ghi_chu'] ?? null,
            'trang_thai' => 'ChoXacNhan'
        ];

        try {
            $phanBoModel->insert($data);
            $_SESSION['success'] = 'Đã gửi báo giá thủ công, chờ điều hành xác nhận.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể gửi báo giá: ' . $e->getMessage();
        }

        header('Location: index.php?act=nhaCungCap/baoGia');
        exit();
    }
    
    // Công nợ
    public function congNo() {
        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        $congNo = $this->nhaCungCapModel->getTongCongNo($nhaCungCapId);
        $dichVuDaXacNhan = $this->nhaCungCapModel->getDichVu($nhaCungCapId, 'DaXacNhan');
        
        require 'views/nha_cung_cap/cong_no.php';
    }
    
    // Lịch sử hợp tác
    public function hopDong() {
        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        $lichSu = $this->nhaCungCapModel->getLichSuHopTac($nhaCungCapId);
        
        require 'views/nha_cung_cap/hop_dong.php';
    }
    
    // Xem chi tiết dịch vụ
    public function chiTietDichVu() {
        $nhaCungCap = $this->currentSupplier();
        $nhaCungCapId = $nhaCungCap['id_nha_cung_cap'];
        $dichVuId = $_GET['id'] ?? 0;
        
        if ($dichVuId <= 0) {
            $_SESSION['error'] = 'Không tìm thấy dịch vụ';
            header('Location: index.php?act=nhaCungCap/baoGia');
            exit();
        }
        
        $dichVu = $this->nhaCungCapModel->getDichVuById($dichVuId, $nhaCungCapId);
        
        if (!$dichVu) {
            $_SESSION['error'] = 'Không tìm thấy dịch vụ hoặc bạn không có quyền xem';
            header('Location: index.php?act=nhaCungCap/baoGia');
            exit();
        }
        
        require 'views/nha_cung_cap/chi_tiet_dich_vu.php';
    }
}
