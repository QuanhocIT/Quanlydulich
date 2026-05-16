<?php
/**
 * AdminNhaCungCapController
 * Manages supplier (nhà cung cấp) CRUD, services, and deletion history.
 */
class AdminNhaCungCapController {

    public function __construct() {
        requireRole('Admin');
    }

    private function requirePostCsrf(string $redirectAct = 'admin/nhaCungCap'): void {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'CSRF token không hợp lệ.';
            header('Location: index.php?act=' . $redirectAct);
            exit;
        }
    }

    private function optionalPostString(string $key) {
        $val = requestString($key, '', 'POST');
        return $val !== '' ? $val : null;
    }

    private function optionalPostId(string $key) {
        return validateId($_POST[$key] ?? null);
    }

    // ========== QUẢN LÝ NHÀ CUNG CẤP ==========

    public function addNhacungcap() {
        require_once __DIR__ . '/../models/NhaCungCap.php';
        require_once __DIR__ . '/../models/NguoiDung.php';
        $nhaCungCapModel = new NhaCungCap();
        $nguoiDungModel = new NguoiDung();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhaCungCap');
            $allowedServiceTypes = ['KhachSan', 'NhaHang', 'Xe', 'Ve', 'Visa', 'BaoHiem', 'Khac'];
            $nguoiDungId = $this->optionalPostId('nguoi_dung_id');
            $tenDonVi = requestString('ten_don_vi', '', 'POST');
            $loaiDichVu = requestString('loai_dich_vu', '', 'POST');
            $diaChi = $this->optionalPostString('dia_chi');
            $lienHe = $this->optionalPostString('lien_he');
            $moTa = $this->optionalPostString('mo_ta');

            if ($tenDonVi === '') {
                $_SESSION['error'] = 'Tên đơn vị không được để trống';
            } elseif ($loaiDichVu !== '' && !in_array($loaiDichVu, $allowedServiceTypes, true)) {
                $_SESSION['error'] = 'Loại dịch vụ không hợp lệ';
            } else {
                try {
                    $data = [
                        'ten_don_vi'    => $tenDonVi,
                        'loai_dich_vu'  => $loaiDichVu !== '' ? $loaiDichVu : null,
                        'nguoi_dung_id' => $nguoiDungId,
                        'dia_chi'       => $diaChi,
                        'lien_he'       => $lienHe,
                        'mo_ta'         => $moTa,
                    ];
                    $nhaCungCapModel->create($data);

                    if ($nguoiDungId) {
                        $nguoiDungModel->update($nguoiDungId, ['vai_tro' => 'NhaCungCap']);
                    }

                    $_SESSION['success'] = 'Thêm nhà cung cấp thành công';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Không thể thêm nhà cung cấp: ' . $e->getMessage();
                }
            }
        }

        header('Location: index.php?act=admin/nhaCungCap');
        exit();
    }

    public function nhaCungCap() {
        require_once __DIR__ . '/../models/NhaCungCap.php';
        require_once __DIR__ . '/../models/NguoiDung.php';
        $nhaCungCapModel = new NhaCungCap();
        $nhaCungCapList = $nhaCungCapModel->getAll();

        $nguoiDungModel = new NguoiDung();
        $supplierUsers = [];
        try {
            $sql = "SELECT nd.id, nd.ho_ten, nd.email, nd.so_dien_thoai
                    FROM nguoi_dung nd
                    LEFT JOIN nha_cung_cap ncc ON nd.id = ncc.nguoi_dung_id
                    WHERE ncc.id_nha_cung_cap IS NULL
                    ORDER BY nd.ngay_tao DESC
                    LIMIT 500";
            $stmt = $nguoiDungModel->conn->prepare($sql);
            $stmt->execute();
            $supplierUsers = $stmt->fetchAll();
        } catch (Exception $e) {
            $supplierUsers = [];
        }

        $selectedId = $_GET['id'] ?? $_GET['ncc_id'] ?? ($nhaCungCapList[0]['id_nha_cung_cap'] ?? null);
        $selectedLoai = $_GET['loai'] ?? null;
        $selectedSupplier = null;
        $serviceTypeSummary = [];
        $supplierStats = [];
        $supplierServices = [];
        $serviceTypes = [];

        if ($selectedId) {
            $selectedSupplier = $nhaCungCapModel->findById($selectedId);
            if ($selectedSupplier) {
                $serviceTypeSummary = $nhaCungCapModel->getServiceTypeSummary($selectedId);
                $supplierStats = $nhaCungCapModel->getSupplierStats($selectedId);
                $serviceTypes = $nhaCungCapModel->getDistinctServiceTypes($selectedId);
                $supplierServices = $nhaCungCapModel->getSupplierServices($selectedId, $selectedLoai ?: null, 100);
            }
        }

        require 'views/admin/nha_cung_cap.php';
    }

    public function updateNhaCungCap() {
        require_once __DIR__ . '/../models/NhaCungCap.php';
        $nhaCungCapModel = new NhaCungCap();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhaCungCap');
            $allowedServiceTypes = ['KhachSan', 'NhaHang', 'Xe', 'Ve', 'Visa', 'BaoHiem', 'Khac'];
            $id = requestInt('id_nha_cung_cap', 0, 'POST');
            $tenDonVi = requestString('ten_don_vi', '', 'POST');
            $loaiDichVu = requestString('loai_dich_vu', '', 'POST');
            $diaChi = $this->optionalPostString('dia_chi');
            $lienHe = $this->optionalPostString('lien_he');
            $moTa = $this->optionalPostString('mo_ta');

            if ($id <= 0) {
                $_SESSION['error'] = 'ID nhà cung cấp không hợp lệ';
            } elseif ($tenDonVi === '') {
                $_SESSION['error'] = 'Tên đơn vị không được để trống';
            } elseif ($loaiDichVu !== '' && !in_array($loaiDichVu, $allowedServiceTypes, true)) {
                $_SESSION['error'] = 'Loại dịch vụ không hợp lệ';
            } else {
                try {
                    $data = [
                        'ten_don_vi'   => $tenDonVi,
                        'loai_dich_vu' => $loaiDichVu !== '' ? $loaiDichVu : null,
                        'dia_chi'      => $diaChi,
                        'lien_he'      => $lienHe,
                        'mo_ta'        => $moTa,
                    ];
                    $nhaCungCapModel->update($id, $data);
                    $_SESSION['success'] = 'Cập nhật nhà cung cấp thành công';
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
                }
            }
        }

        header('Location: index.php?act=admin/nhaCungCap');
        exit();
    }

    public function deleteNhaCungCap() {
        require_once __DIR__ . '/../models/NhaCungCap.php';
        require_once __DIR__ . '/../models/NguoiDung.php';
        require_once __DIR__ . '/../models/SupplierDeletionHistory.php';
        $nhaCungCapModel = new NhaCungCap();
        $nguoiDungModel = new NguoiDung();
        $deletionHistoryModel = new SupplierDeletionHistory();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhaCungCap');
            $schema = validateInputSchema([
                'id_nha_cung_cap' => ['type' => 'id', 'required' => true],
                'mat_khau'        => ['type' => 'string', 'required' => true, 'min' => 1],
                'ly_do_xoa'       => ['type' => 'string', 'required' => false, 'max' => 1000],
            ], 'POST');
            if (!$schema['ok']) {
                setValidationErrors($schema['errors'], 'Du lieu xoa nha cung cap khong hop le.');
                $_SESSION['error'] = 'Du lieu xoa nha cung cap khong hop le.';
                header('Location: index.php?act=admin/nhaCungCap');
                exit();
            }

            $id = (int)($schema['data']['id_nha_cung_cap'] ?? 0);
            $matKhau = (string)($schema['data']['mat_khau'] ?? '');
            $lyDoXoa = (string)($schema['data']['ly_do_xoa'] ?? '');

            if ($id <= 0) {
                $_SESSION['error'] = 'ID nhà cung cấp không hợp lệ';
                header('Location: index.php?act=admin/nhaCungCap');
                exit();
            }

            $adminId = $_SESSION['user_id'] ?? 0;
            $admin = $nguoiDungModel->findById($adminId);

            if (!$admin || !password_verify($matKhau, $admin['mat_khau'])) {
                $_SESSION['error'] = 'Mật khẩu không đúng.';
                header('Location: index.php?act=admin/nhaCungCap&id=' . $id);
                exit();
            }

            try {
                $nhaCungCap = $nhaCungCapModel->findById($id);
                if (!$nhaCungCap) {
                    $_SESSION['error'] = 'Không tìm thấy nhà cung cấp';
                } else {
                    $thongTinNCC = json_encode([
                        'id_nha_cung_cap' => $nhaCungCap['id_nha_cung_cap'],
                        'ten_don_vi'      => $nhaCungCap['ten_don_vi'] ?? 'N/A',
                        'loai_dich_vu'    => $nhaCungCap['loai_dich_vu'] ?? null,
                        'dia_chi'         => $nhaCungCap['dia_chi'] ?? null,
                        'lien_he'         => $nhaCungCap['lien_he'] ?? null,
                        'mo_ta'           => $nhaCungCap['mo_ta'] ?? null,
                        'nguoi_dung_id'   => $nhaCungCap['nguoi_dung_id'] ?? null,
                    ], JSON_UNESCAPED_UNICODE);

                    $result = $nhaCungCapModel->delete($id);

                    if ($result) {
                        $deletionHistoryModel->insert([
                            'nha_cung_cap_id'        => $id,
                            'nguoi_dung_id'          => $nhaCungCap['nguoi_dung_id'] ?? null,
                            'nguoi_xoa_id'           => $adminId,
                            'ly_do_xoa'              => $lyDoXoa,
                            'thong_tin_nha_cung_cap' => $thongTinNCC,
                        ]);

                        if (!empty($nhaCungCap['nguoi_dung_id'])) {
                            $nguoiDungModel->update($nhaCungCap['nguoi_dung_id'], ['vai_tro' => 'KhachHang']);
                        }

                        $_SESSION['success'] = 'Đã ẩn nhà cung cấp (xóa mềm) thành công';
                    } else {
                        $_SESSION['error'] = 'Không thể ẩn nhà cung cấp';
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Lỗi khi xóa: ' . $e->getMessage();
            }
        }

        header('Location: index.php?act=admin/nhaCungCap');
        exit();
    }

    public function chiTietDichVu() {
        require_once __DIR__ . '/../models/NhaCungCap.php';
        $nhaCungCapModel = new NhaCungCap();
        $dichVuId = $_GET['id'] ?? 0;
        $nccId = $_GET['ncc_id'] ?? null;

        if ($dichVuId <= 0) {
            $_SESSION['error'] = 'Không tìm thấy dịch vụ';
            header('Location: index.php?act=admin/nhaCungCap' . ($nccId ? '&id=' . $nccId : ''));
            exit();
        }

        $dichVu = $nhaCungCapModel->getDichVuById($dichVuId);

        if (!$dichVu) {
            $_SESSION['error'] = 'Không tìm thấy dịch vụ';
            header('Location: index.php?act=admin/nhaCungCap' . ($nccId ? '&id=' . $nccId : ''));
            exit();
        }

        if ($dichVu['nha_cung_cap_id'] && !$nccId) {
            $nccId = $dichVu['nha_cung_cap_id'];
        }

        require 'views/admin/chi_tiet_dich_vu.php';
    }

    public function supplierServiceAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/nhaCungCap');
            exit();
        }
        $this->requirePostCsrf('admin/nhaCungCap');

        $schema = validateInputSchema([
            'dich_vu_id' => ['type' => 'id', 'required' => true],
            'action'     => ['type' => 'string', 'required' => true, 'enum' => ['approve', 'reject', 'update_price']],
            'ncc_id'     => ['type' => 'id', 'required' => false],
            'gia_tien'   => ['type' => 'money', 'required' => false, 'min' => 0],
            'ghi_chu'    => ['type' => 'string', 'required' => false, 'max' => 500],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu xu ly dich vu nha cung cap khong hop le.');
            $_SESSION['error'] = 'Du lieu xu ly dich vu nha cung cap khong hop le.';
            header('Location: index.php?act=admin/nhaCungCap');
            exit();
        }

        $serviceId = (int)($schema['data']['dich_vu_id'] ?? 0);
        $action = (string)($schema['data']['action'] ?? '');
        $nccId = (int)($schema['data']['ncc_id'] ?? 0);
        $redirect = 'index.php?act=admin/nhaCungCap' . ($nccId ? '&id=' . $nccId : '');

        if ($serviceId <= 0 || $action === '') {
            $_SESSION['error'] = 'Dịch vụ hoặc hành động không hợp lệ';
            header('Location: ' . $redirect);
            exit();
        }

        require_once __DIR__ . '/../models/NhaCungCap.php';
        $nhaCungCapModel = new NhaCungCap();

        try {
            switch ($action) {
                case 'approve':
                    $giaTien = (int)($schema['data']['gia_tien'] ?? 0);
                    if ($giaTien <= 0) {
                        throw new Exception('Giá tiền phải lớn hơn 0');
                    }
                    $nhaCungCapModel->xacNhanDichVu($serviceId, $giaTien);
                    $_SESSION['success'] = 'Đã xác nhận dịch vụ';
                    break;
                case 'reject':
                    $ghiChu = (string)($schema['data']['ghi_chu'] ?? '');
                    $nhaCungCapModel->tuChoiDichVu($serviceId, $ghiChu ?: null);
                    $_SESSION['success'] = 'Đã từ chối dịch vụ';
                    break;
                case 'update_price':
                    $giaTien = (int)($schema['data']['gia_tien'] ?? 0);
                    if ($giaTien <= 0) {
                        throw new Exception('Giá tiền phải lớn hơn 0');
                    }
                    $nhaCungCapModel->capNhatGiaDichVu($serviceId, $giaTien);
                    $_SESSION['success'] = 'Đã cập nhật giá dịch vụ';
                    break;
                default:
                    $_SESSION['error'] = 'Hành động không được hỗ trợ';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Không thể xử lý: ' . $e->getMessage();
        }

        header('Location: ' . $redirect);
        exit();
    }

    // ========== LỊCH SỬ XÓA NHÀ CUNG CẤP ==========

    public function lichSuXoaNhaCungCap() {
        require_once __DIR__ . '/../models/SupplierDeletionHistory.php';
        $deletionHistoryModel = new SupplierDeletionHistory();

        $lichSuXoa = $deletionHistoryModel->getAll();

        require 'views/admin/lich_su_xoa_nha_cung_cap.php';
    }

    public function chiTietLichSuXoaNhaCungCap() {
        require_once __DIR__ . '/../models/SupplierDeletionHistory.php';
        $deletionHistoryModel = new SupplierDeletionHistory();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'Bản ghi không hợp lệ.';
            header('Location: index.php?act=admin/lichSuXoaNhaCungCap');
            exit;
        }

        $chiTiet = $deletionHistoryModel->getById($id);

        if (!$chiTiet) {
            $_SESSION['error'] = 'Không tìm thấy bản ghi lịch sử xóa.';
            header('Location: index.php?act=admin/lichSuXoaNhaCungCap');
            exit;
        }

        require 'views/admin/chi_tiet_lich_su_xoa_nha_cung_cap.php';
    }
}
