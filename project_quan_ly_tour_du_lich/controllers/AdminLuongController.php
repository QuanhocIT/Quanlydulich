<?php
/**
 * AdminLuongController — quản lý lương/thưởng nhân sự
 *
 * Tách từ AdminController (group 1/9).
 * Các route tương ứng trong index.php:
 *   admin/quanLyLuongThuong, admin/chiTietLuong, admin/ajaxChiTietLuong,
 *   admin/taoLuongThuong, admin/capNhatLuongThuong, admin/duyetLuongNhanSu,
 *   admin/thanhToanLuongNhanSu, admin/tinhLaiLuongNhanSu, admin/capNhatLuongCoBan
 */
class AdminLuongController
{
    public function __construct()
    {
        requireRole('Admin');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function requirePostCsrf(string $redirectAct = 'admin/quanLyLuongThuong'): void
    {
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

    private function tinhLuong(
        string $loaiLuong,
        float $soTienCoDinh,
        float $phanTramHoaHong,
        float $doanhThu
    ): array {
        $loaiLuong       = in_array($loaiLuong, ['CoDinh', 'PhanTram', 'KetHop'], true) ? $loaiLuong : 'CoDinh';
        $soTienCoDinh    = max(0, $soTienCoDinh);
        $phanTramHoaHong = max(0, min(100, $phanTramHoaHong));
        $doanhThu        = max(0, $doanhThu);

        $tienHoaHong = 0.0;
        $tongLuong   = 0.0;

        if ($loaiLuong === 'CoDinh') {
            $tongLuong = $soTienCoDinh;
        } elseif ($loaiLuong === 'PhanTram') {
            $tienHoaHong = round($doanhThu * $phanTramHoaHong / 100, 2);
            $tongLuong   = $tienHoaHong;
        } else { // KetHop
            $tienHoaHong = round($doanhThu * $phanTramHoaHong / 100, 2);
            $tongLuong   = $soTienCoDinh + $tienHoaHong;
        }

        return [
            'loai_luong'       => $loaiLuong,
            'so_tien_co_dinh'  => $soTienCoDinh,
            'phan_tram_hoa_hong' => $phanTramHoaHong,
            'tien_hoa_hong'    => $tienHoaHong,
            'tong_luong'       => $tongLuong,
        ];
    }

    // =========================================================================
    // PUBLIC ACTIONS
    // =========================================================================

    public function capNhatLuongCoBan(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/NhanSu.php';
        $nhanSuModel = new NhanSu();

        $schema = validateInputSchema([
            'nhan_su_id'  => ['type' => 'id',    'required' => true],
            'luong_co_ban'=> ['type' => 'money',  'required' => true, 'min' => 0],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu cap nhat luong co ban khong hop le.');
            $_SESSION['error'] = 'Du lieu cap nhat luong co ban khong hop le.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $nhanSuId   = (int)($schema['data']['nhan_su_id']   ?? 0);
        $luongCoBan = (float)($schema['data']['luong_co_ban'] ?? 0);
        if ($nhanSuId <= 0) {
            $_SESSION['error'] = 'Thiếu nhân_sự_id.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $ok = $nhanSuModel->updateLuongCoBan($nhanSuId, $luongCoBan);
        $_SESSION[$ok ? 'success' : 'error'] = $ok
            ? 'Đã cập nhật lương cơ bản.'
            : 'Không thể cập nhật lương cơ bản (hãy đảm bảo đã thêm cột luong_co_ban).';

        $redirect = requestString('redirect', '', 'POST');
        header('Location: ' . (!empty($redirect)
            ? $redirect
            : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId));
        exit;
    }

    public function chiTietLuong(): void
    {
        $nhanSuId = $_GET['nhan_su_id'] ?? null;
        if (empty($nhanSuId)) {
            $_SESSION['error'] = 'Thiếu nhân_sự_id.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $month   = $_GET['month'] ?? '';
        $year    = $_GET['year']  ?? '';
        $showAll = (($_GET['all'] ?? '') === '1');
        if (!$showAll) {
            if ($month === '') $month = (int)date('n');
            if ($year  === '') $year  = (int)date('Y');
        }

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        require_once __DIR__ . '/../models/NhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();
        $nhanSuModel       = new NhanSu();

        $nhanSu = $nhanSuModel->findById($nhanSuId);
        if (!$nhanSu) {
            $_SESSION['error'] = 'Nhân sự không tồn tại.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $filters = ['nhan_su_id' => $nhanSuId];
        if (!$showAll) {
            $filters['month'] = $month;
            $filters['year']  = $year;
        }
        $luongChiTiet = $phanBoNhanSuModel->getAllLuong($filters);

        $tongCoDinh  = 0.0;
        $tongHoaHong = 0.0;
        $tongLuong   = 0.0;
        foreach ($luongChiTiet as $row) {
            $tongCoDinh  += (float)($row['so_tien_co_dinh'] ?? 0);
            $tongHoaHong += (float)($row['tien_hoa_hong']   ?? 0);
            $tongLuong   += (float)($row['tong_luong']       ?? 0);
        }

        if (($nhanSu['vai_tro'] ?? '') === 'HDV' && !$showAll && $month !== '' && $year !== '') {
            $luongCoBan  = (float)($nhanSu['luong_co_ban'] ?? 0);
            $tongCoDinh  = $luongCoBan;
            $tongLuong   = $luongCoBan + $tongHoaHong;
        }

        $pageTitle   = 'Chi tiết lương nhân sự';
        $currentPage = 'luongThuong';
        require 'views/admin/chi_tiet_luong.php';
    }

    public function quanLyLuongThuong(): void
    {
        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        require_once __DIR__ . '/../models/NhanSu.php';
        require_once __DIR__ . '/../models/LichKhoiHanh.php';
        require_once __DIR__ . '/../models/Tour.php';

        $phanBoNhanSuModel = new PhanBoNhanSu();
        $nhanSuModel       = new NhanSu();
        $lichKhoiHanhModel = new LichKhoiHanh();
        $tourModel         = new Tour();

        $filterNhanSu        = $_GET['nhan_su_id']       ?? '';
        $filterTour          = $_GET['tour_id']          ?? '';
        $filterMonth         = $_GET['month']            ?? '';
        $filterYear          = $_GET['year']             ?? '';
        $filterTrangThaiLuong = $_GET['trang_thai_luong'] ?? '';
        $showAll             = (($_GET['all'] ?? '') === '1');

        $allLuongTongHop = $phanBoNhanSuModel->getLuongTongHop([
            'nhan_su_id'        => $filterNhanSu,
            'tour_id'           => $filterTour,
            'month'             => $filterMonth,
            'year'              => $filterYear,
            'trang_thai_luong'  => $filterTrangThaiLuong,
        ]);

        if (!$showAll && $filterMonth !== '' && $filterYear !== '') {
            foreach ($allLuongTongHop as &$row) {
                if (($row['vai_tro'] ?? '') === 'HDV') {
                    $luongCoBan  = (float)($row['luong_co_ban']  ?? 0);
                    $tongHoaHong = (float)($row['tong_hoa_hong'] ?? 0);
                    $row['tong_co_dinh'] = $luongCoBan;
                    $row['tong_luong']   = $luongCoBan + $tongHoaHong;
                }
            }
            unset($row);
        }

        $nhanSuList       = $nhanSuModel->getOptions();
        $tourList         = $tourModel->getOptions();
        $lichKhoiHanhList = $lichKhoiHanhModel->getUpcomingOptions(500, 365);

        $pageTitle   = 'Quản lý lương thưởng nhân sự';
        $currentPage = 'luongThuong';

        require 'views/admin/quan_ly_luong_thuong.php';
    }

    public function ajaxChiTietLuong(): void
    {
        $nhanSuId = $_GET['nhan_su_id'] ?? null;
        if (empty($nhanSuId)) {
            http_response_code(400);
            echo 'Thiếu nhân_sự_id.';
            exit;
        }

        $month   = $_GET['month'] ?? '';
        $year    = $_GET['year']  ?? '';
        $showAll = (($_GET['all'] ?? '') === '1');

        if (!$showAll) {
            if ($month === '') $month = (int)date('n');
            if ($year  === '') $year  = (int)date('Y');
        }

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        require_once __DIR__ . '/../models/NhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();
        $nhanSuModel       = new NhanSu();

        $nhanSu = $nhanSuModel->findById($nhanSuId);
        if (!$nhanSu) {
            http_response_code(404);
            echo 'Nhân sự không tồn tại.';
            exit;
        }

        $filters = ['nhan_su_id' => $nhanSuId];
        if (!$showAll) {
            $filters['month'] = $month;
            $filters['year']  = $year;
        }
        $luongChiTiet = $phanBoNhanSuModel->getAllLuong($filters);

        $tongLuong   = 0.0;
        $tongHoaHong = 0.0;
        foreach ($luongChiTiet as $row) {
            $tongLuong   += (float)($row['tong_luong']     ?? 0);
            $tongHoaHong += (float)($row['tien_hoa_hong']  ?? 0);
        }
        if (($nhanSu['vai_tro'] ?? '') === 'HDV' && !$showAll && $month !== '' && $year !== '') {
            $tongLuong = (float)($nhanSu['luong_co_ban'] ?? 0) + $tongHoaHong;
        }

        require 'views/admin/ajax_chi_tiet_luong.php';
        exit;
    }

    public function taoLuongThuong(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        require_once __DIR__ . '/../models/NhanSu.php';
        require_once __DIR__ . '/../models/LichKhoiHanh.php';

        $phanBoNhanSuModel = new PhanBoNhanSu();
        $nhanSuModel       = new NhanSu();
        $lichKhoiHanhModel = new LichKhoiHanh();

        $schema = validateInputSchema([
            'nhan_su_id'        => ['type' => 'id',    'required' => true],
            'lich_khoi_hanh_id' => ['type' => 'id',    'required' => true],
            'loai_luong'        => ['type' => 'string', 'required' => true, 'enum' => ['CoDinh', 'PhanTram', 'KetHop']],
            'so_tien_co_dinh'   => ['type' => 'money',  'required' => false, 'min' => 0],
            'phan_tram_hoa_hong'=> ['type' => 'money',  'required' => false, 'min' => 0, 'max' => 100],
            'ghi_chu'           => ['type' => 'string', 'required' => false, 'max' => 500],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu tao luong thuong khong hop le.');
            $_SESSION['error'] = 'Du lieu tao luong thuong khong hop le.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $nhanSuId        = (int)($schema['data']['nhan_su_id']         ?? 0);
        $lichKhoiHanhId  = (int)($schema['data']['lich_khoi_hanh_id']  ?? 0);
        $loaiLuong       = (string)($schema['data']['loai_luong']       ?? 'CoDinh');
        $soTienCoDinh    = (float)($schema['data']['so_tien_co_dinh']   ?? 0);
        $phanTramHoaHong = (float)($schema['data']['phan_tram_hoa_hong']?? 0);
        $ghiChu          = (string)($schema['data']['ghi_chu']          ?? '');

        if ($nhanSuId <= 0 || $lichKhoiHanhId <= 0) {
            $_SESSION['error'] = 'Vui lòng chọn nhân sự và lịch khởi hành.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $nhanSu = $nhanSuModel->findById($nhanSuId);
        if (!$nhanSu) {
            $_SESSION['error'] = 'Nhân sự không tồn tại.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $lich = $lichKhoiHanhModel->findById($lichKhoiHanhId);
        if (!$lich) {
            $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $doanhThu = $phanBoNhanSuModel->getDoanhThuByLichKhoiHanh($lichKhoiHanhId);
        $tinh     = $this->tinhLuong($loaiLuong, $soTienCoDinh, $phanTramHoaHong, $doanhThu);

        $existing = $phanBoNhanSuModel->findByLichKhoiHanhAndNhanSu($lichKhoiHanhId, $nhanSuId);
        if ($existing) {
            $phanBoNhanSuModel->updateLuongFull(
                $existing['id'],
                array_merge($tinh, ['trang_thai_luong' => 'ChoDuyet', 'ghi_chu' => $ghiChu])
            );
            $_SESSION['success'] = 'Đã cập nhật lương/thưởng.';
        } else {
            $id = $phanBoNhanSuModel->insert(array_merge([
                'lich_khoi_hanh_id' => $lichKhoiHanhId,
                'nhan_su_id'        => $nhanSuId,
                'vai_tro'           => $nhanSu['vai_tro'] ?? 'Khac',
                'ghi_chu'           => $ghiChu,
                'trang_thai'        => 'DaXacNhan',
            ], $tinh, [
                'trang_thai_luong'   => 'ChoDuyet',
                'ngay_tao_luong'     => date('Y-m-d H:i:s'),
                'ngay_cap_nhat_luong'=> date('Y-m-d H:i:s'),
            ]));
            if ($id) {
                $phanBoNhanSuModel->updateTrangThai($id, 'DaXacNhan');
                $_SESSION['success'] = 'Đã tạo lương/thưởng mới.';
            } else {
                $_SESSION['error'] = 'Không thể tạo lương/thưởng.';
            }
        }

        $m = !empty($lich['ngay_khoi_hanh']) ? (int)date('n', strtotime($lich['ngay_khoi_hanh'])) : '';
        $y = !empty($lich['ngay_khoi_hanh']) ? (int)date('Y', strtotime($lich['ngay_khoi_hanh'])) : '';
        header('Location: index.php?act=admin/quanLyLuongThuong&month=' . urlencode((string)$m) . '&year=' . urlencode((string)$y));
        exit;
    }

    public function capNhatLuongThuong(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Thiếu id phân bổ.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $row = $phanBoNhanSuModel->findById($id);
        if (!$row) {
            $_SESSION['error'] = 'Bản ghi không tồn tại.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        if (($row['trang_thai_luong'] ?? '') === 'DaThanhToan') {
            $_SESSION['error'] = 'Dòng lương đã thanh toán, không thể chỉnh sửa.';
            $redirect = $_POST['redirect'] ?? null;
            header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/quanLyLuongThuong'));
            exit;
        }

        $loaiLuong       = $_POST['loai_luong']        ?? ($row['loai_luong']         ?? 'CoDinh');
        $soTienCoDinh    = $_POST['so_tien_co_dinh']   ?? ($row['so_tien_co_dinh']    ?? 0);
        $phanTramHoaHong = $_POST['phan_tram_hoa_hong']?? ($row['phan_tram_hoa_hong'] ?? 0);
        $trangThaiLuong  = $_POST['trang_thai_luong']  ?? ($row['trang_thai_luong']   ?? 'ChoDuyet');
        $ghiChu          = trim($_POST['ghi_chu']      ?? ($row['ghi_chu']            ?? ''));

        if (!in_array($trangThaiLuong, ['ChoDuyet', 'DaDuyet', 'DaThanhToan'], true)) {
            $trangThaiLuong = 'ChoDuyet';
        }

        $doanhThu = $phanBoNhanSuModel->getDoanhThuByLichKhoiHanh((int)($row['lich_khoi_hanh_id'] ?? 0));
        $tinh     = $this->tinhLuong($loaiLuong, $soTienCoDinh, $phanTramHoaHong, $doanhThu);

        $ok = $phanBoNhanSuModel->updateLuongFull(
            $id,
            array_merge($tinh, ['trang_thai_luong' => $trangThaiLuong, 'ghi_chu' => $ghiChu])
        );

        $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Đã cập nhật lương/thưởng.' : 'Cập nhật thất bại.';

        $redirect = $_POST['redirect'] ?? null;
        header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/quanLyLuongThuong'));
        exit;
    }

    public function duyetLuongNhanSu(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();

        $nhanSuId = (int)($_POST['nhan_su_id'] ?? 0);
        $month    = (int)($_POST['month']      ?? 0);
        $year     = (int)($_POST['year']       ?? 0);
        if ($nhanSuId <= 0 || $month <= 0 || $year <= 0) {
            $_SESSION['error'] = 'Thiếu tham số duyệt lương.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $affected = $phanBoNhanSuModel->updateTrangThaiLuongByNhanSuThangNam($nhanSuId, $month, $year, 'DaDuyet');
        $_SESSION[$affected > 0 ? 'success' : 'error'] = $affected > 0
            ? 'Đã duyệt lương tháng này.'
            : 'Không có dòng nào để duyệt (có thể đã thanh toán hết).';

        $redirect = $_POST['redirect'] ?? null;
        header('Location: ' . (!empty($redirect)
            ? $redirect
            : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
        exit;
    }

    public function thanhToanLuongNhanSu(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();

        $nhanSuId = (int)($_POST['nhan_su_id'] ?? 0);
        $month    = (int)($_POST['month']      ?? 0);
        $year     = (int)($_POST['year']       ?? 0);
        if ($nhanSuId <= 0 || $month <= 0 || $year <= 0) {
            $_SESSION['error'] = 'Thiếu tham số thanh toán lương.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $rows = $phanBoNhanSuModel->getAllLuong([
            'nhan_su_id' => $nhanSuId,
            'month'      => $month,
            'year'       => $year,
        ]);
        $hasChoDuyet = false;
        foreach ($rows as $r) {
            if (($r['trang_thai_luong'] ?? '') === 'ChoDuyet') {
                $hasChoDuyet = true;
                break;
            }
        }
        if ($hasChoDuyet) {
            $_SESSION['error'] = 'Còn dòng lương Chờ duyệt. Hãy duyệt trước khi thanh toán.';
            $redirect = $_POST['redirect'] ?? null;
            header('Location: ' . (!empty($redirect)
                ? $redirect
                : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
            exit;
        }

        $affected = $phanBoNhanSuModel->updateTrangThaiLuongByNhanSuThangNam($nhanSuId, $month, $year, 'DaThanhToan');
        $_SESSION[$affected > 0 ? 'success' : 'error'] = $affected > 0
            ? 'Đã đánh dấu đã thanh toán.'
            : 'Không có dòng nào được thanh toán (cần ở trạng thái Đã duyệt).';

        $redirect = $_POST['redirect'] ?? null;
        header('Location: ' . (!empty($redirect)
            ? $redirect
            : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
        exit;
    }

    public function tinhLaiLuongNhanSu(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();

        $nhanSuId = (int)($_POST['nhan_su_id'] ?? 0);
        $month    = (int)($_POST['month']      ?? 0);
        $year     = (int)($_POST['year']       ?? 0);
        if ($nhanSuId <= 0 || $month <= 0 || $year <= 0) {
            $_SESSION['error'] = 'Thiếu tham số tính lại lương.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $updated = $phanBoNhanSuModel->recalcLuongByNhanSuThangNam($nhanSuId, $month, $year);
        $_SESSION[$updated > 0 ? 'success' : 'error'] = $updated > 0
            ? "Đã tính lại lương/hoa hồng ({$updated} dòng)."
            : 'Không có dòng nào được tính lại (có thể đã thanh toán hết).';

        $redirect = $_POST['redirect'] ?? null;
        header('Location: ' . (!empty($redirect)
            ? $redirect
            : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
        exit;
    }
}
