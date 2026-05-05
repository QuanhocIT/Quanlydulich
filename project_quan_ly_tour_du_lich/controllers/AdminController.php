<?php
class AdminController {
    private function resolveDashboardAnchorDate(): DateTimeImmutable {
        $requestedDate = trim((string)($_GET['date_to'] ?? ''));
        $normalizedDate = validateDateYmd($requestedDate);
        $safeDate = $normalizedDate ?: date('Y-m-d');

        try {
            return new DateTimeImmutable($safeDate);
        } catch (Throwable $e) {
            return new DateTimeImmutable(date('Y-m-d'));
        }
    }

    private function getMonthToDateRevenue(DateTimeImmutable $anchorDate): array {
        $fromDate = $anchorDate->modify('first day of this month')->format('Y-m-d');
        $toDate = $anchorDate->format('Y-m-d');
        $total = 0.0;

        try {
            $conn = connectDB();
            $stmt = $conn->prepare(
                "SELECT COALESCE(SUM(so_tien), 0)
                 FROM giao_dich_tai_chinh
                 WHERE loai = 'Thu'
                   AND ngay_giao_dich >= ?
                   AND ngay_giao_dich <= ?"
            );
            $stmt->execute([$fromDate, $toDate]);
            $total = (float)($stmt->fetchColumn() ?? 0);
        } catch (Throwable $e) {
            error_log('[AdminController::getMonthToDateRevenue] ' . $e->getMessage());
        }

        return [
            'amount' => $total,
            'from' => $fromDate,
            'to' => $toDate,
        ];
    }

    private function requirePostCsrf(string $redirectAct = 'admin/dashboard') {
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

    private function optionalPostString(string $key) {
        $value = requestString($key, '', 'POST');
        return $value === '' ? null : $value;
    }

    private function optionalPostEmail(string $key) {
        if (!isset($_POST[$key])) {
            return null;
        }

        $rawValue = sanitizeText($_POST[$key]);
        if ($rawValue === '') {
            return null;
        }

        return validateEmail($rawValue);
    }

    private function optionalPostPhone(string $key) {
        if (!isset($_POST[$key])) {
            return null;
        }

        $rawValue = sanitizeText($_POST[$key]);
        if ($rawValue === '') {
            return null;
        }

        return validatePhone($rawValue);
    }

    private function optionalPostDate(string $key) {
        if (!isset($_POST[$key])) {
            return null;
        }

        $rawValue = sanitizeText($_POST[$key]);
        if ($rawValue === '') {
            return null;
        }

        return validateDateYmd($rawValue);
    }

    private function optionalPostId(string $key) {
        return validateId($_POST[$key] ?? null);
    }

    private function tinhLuong(string $loaiLuong, float $soTienCoDinh, float $phanTramHoaHong, float $doanhThu) {
        $loaiLuong = in_array($loaiLuong, ['CoDinh', 'PhanTram', 'KetHop'], true) ? $loaiLuong : 'CoDinh';
        $soTienCoDinh = max(0, (float)$soTienCoDinh);
        $phanTramHoaHong = max(0, min(100, (float)$phanTramHoaHong));
        $doanhThu = max(0, (float)$doanhThu);

        $tienHoaHong = 0;
        $tongLuong = 0;

        if ($loaiLuong === 'CoDinh') {
            $tongLuong = $soTienCoDinh;
        } elseif ($loaiLuong === 'PhanTram') {
            $tienHoaHong = round($doanhThu * $phanTramHoaHong / 100, 2);
            $tongLuong = $tienHoaHong;
        } else { // KetHop
            $tienHoaHong = round($doanhThu * $phanTramHoaHong / 100, 2);
            $tongLuong = $soTienCoDinh + $tienHoaHong;
        }

        return [
            'loai_luong' => $loaiLuong,
            'so_tien_co_dinh' => $soTienCoDinh,
            'phan_tram_hoa_hong' => $phanTramHoaHong,
            'tien_hoa_hong' => $tienHoaHong,
            'tong_luong' => $tongLuong,
        ];
    }
        // Hiển thị chi tiết lương cho nhân sự
        public function capNhatLuongCoBan() {
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                header('Location: index.php?act=admin/quanLyLuongThuong');
                exit;
            }
            $this->requirePostCsrf('admin/quanLyLuongThuong');

            require_once __DIR__ . '/../models/NhanSu.php';
            $nhanSuModel = new NhanSu();

            $schema = validateInputSchema([
                'nhan_su_id' => ['type' => 'id', 'required' => true],
                'luong_co_ban' => ['type' => 'money', 'required' => true, 'min' => 0],
            ], 'POST');
            if (!$schema['ok']) {
                setValidationErrors($schema['errors'], 'Du lieu cap nhat luong co ban khong hop le.');
                $_SESSION['error'] = 'Du lieu cap nhat luong co ban khong hop le.';
                header('Location: index.php?act=admin/quanLyLuongThuong');
                exit;
            }

            $nhanSuId = (int)($schema['data']['nhan_su_id'] ?? 0);
            $luongCoBan = (float)($schema['data']['luong_co_ban'] ?? 0);
            if ($nhanSuId <= 0) {
                $_SESSION['error'] = 'Thiếu nhân_sự_id.';
                header('Location: index.php?act=admin/quanLyLuongThuong');
                exit;
            }

            $ok = $nhanSuModel->updateLuongCoBan($nhanSuId, $luongCoBan);
            $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Đã cập nhật lương cơ bản.' : 'Không thể cập nhật lương cơ bản (hãy đảm bảo đã thêm cột luong_co_ban).';

            $redirect = requestString('redirect', '', 'POST');
            header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId));
            exit;
        }

        public function chiTietLuong() {
            $nhanSuId = $_GET['nhan_su_id'] ?? null;
            if (empty($nhanSuId)) {
                $_SESSION['error'] = 'Thiếu nhân_sự_id.';
                header('Location: index.php?act=admin/quanLyLuongThuong');
                exit;
            }
            $month = $_GET['month'] ?? '';
            $year = $_GET['year'] ?? '';
            $showAll = (($_GET['all'] ?? '') === '1');
            if (!$showAll) {
                if ($month === '') $month = (int)date('n');
                if ($year === '') $year = (int)date('Y');
            }
            require_once __DIR__ . '/../models/PhanBoNhanSu.php';
            require_once __DIR__ . '/../models/NhanSu.php';
            $phanBoNhanSuModel = new PhanBoNhanSu();
            $nhanSuModel = new NhanSu();
            // Lấy thông tin nhân sự
            $nhanSu = $nhanSuModel->findById($nhanSuId);
            if (!$nhanSu) {
                $_SESSION['error'] = 'Nhân sự không tồn tại.';
                header('Location: index.php?act=admin/quanLyLuongThuong');
                exit;
            }
            // Lấy thông tin lương/thưởng của nhân sự theo tháng/năm (giả sử có hàm getLuongByNhanSuThangNam)
            $filters = ['nhan_su_id' => $nhanSuId];
            if (!$showAll) {
                $filters['month'] = $month;
                $filters['year'] = $year;
            }
            $luongChiTiet = $phanBoNhanSuModel->getAllLuong($filters);

            $tongCoDinh = 0;
            $tongHoaHong = 0;
            $tongLuong = 0;
            foreach ($luongChiTiet as $row) {
                $tongCoDinh += (float)($row['so_tien_co_dinh'] ?? 0);
                $tongHoaHong += (float)($row['tien_hoa_hong'] ?? 0);
                $tongLuong += (float)($row['tong_luong'] ?? 0);
            }

            if (($nhanSu['vai_tro'] ?? '') === 'HDV' && !$showAll && $month !== '' && $year !== '') {
                $luongCoBan = (float)($nhanSu['luong_co_ban'] ?? 0);
                $tongCoDinh = $luongCoBan;
                $tongLuong = $luongCoBan + $tongHoaHong;
            }

            $pageTitle = 'Chi tiết lương nhân sự';
            $currentPage = 'luongThuong';
            require 'views/admin/chi_tiet_luong.php';
        }
    // Quản lý lương thưởng nhân sự
    public function quanLyLuongThuong() {
        // Chỉ render view, logic filter đã nằm trong view
        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        require_once __DIR__ . '/../models/NhanSu.php';
        require_once __DIR__ . '/../models/LichKhoiHanh.php';
        require_once __DIR__ . '/../models/Tour.php';

        $phanBoNhanSuModel = new PhanBoNhanSu();
        $nhanSuModel = new NhanSu();
        $lichKhoiHanhModel = new LichKhoiHanh();
        $tourModel = new Tour();

        $filterNhanSu = $_GET['nhan_su_id'] ?? '';
        $filterTour = $_GET['tour_id'] ?? '';
        $filterMonth = $_GET['month'] ?? '';
        $filterYear = $_GET['year'] ?? '';
        $filterTrangThaiLuong = $_GET['trang_thai_luong'] ?? '';
        $showAll = (($_GET['all'] ?? '') === '1');

        $allLuongTongHop = $phanBoNhanSuModel->getLuongTongHop([
            'nhan_su_id' => $filterNhanSu,
            'tour_id' => $filterTour,
            'month' => $filterMonth,
            'year' => $filterYear,
            'trang_thai_luong' => $filterTrangThaiLuong,
        ]);

        // Quy tắc lương HDV: lương cơ bản (theo tháng) + tổng hoa hồng các tour trong kỳ
        if (!$showAll && $filterMonth !== '' && $filterYear !== '') {
            foreach ($allLuongTongHop as &$row) {
                if (($row['vai_tro'] ?? '') === 'HDV') {
                    $luongCoBan = (float)($row['luong_co_ban'] ?? 0);
                    $tongHoaHong = (float)($row['tong_hoa_hong'] ?? 0);
                    $row['tong_co_dinh'] = $luongCoBan;
                    $row['tong_luong'] = $luongCoBan + $tongHoaHong;
                }
            }
            unset($row);
        }

        $nhanSuList = $nhanSuModel->getOptions();
        $tourList = $tourModel->getOptions();
        $lichKhoiHanhList = $lichKhoiHanhModel->getUpcomingOptions(500, 365);

        $pageTitle = 'Quản lý lương thưởng nhân sự';
        $currentPage = 'luongThuong';

        require 'views/admin/quan_ly_luong_thuong.php';
    }

    public function ajaxChiTietLuong() {
        $nhanSuId = $_GET['nhan_su_id'] ?? null;
        if (empty($nhanSuId)) {
            http_response_code(400);
            echo 'Thiếu nhân_sự_id.';
            exit;
        }

        $month = $_GET['month'] ?? '';
        $year = $_GET['year'] ?? '';
        $showAll = (($_GET['all'] ?? '') === '1');

        if (!$showAll) {
            if ($month === '') $month = (int)date('n');
            if ($year === '') $year = (int)date('Y');
        }

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        require_once __DIR__ . '/../models/NhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();
        $nhanSuModel = new NhanSu();

        $nhanSu = $nhanSuModel->findById($nhanSuId);
        if (!$nhanSu) {
            http_response_code(404);
            echo 'Nhân sự không tồn tại.';
            exit;
        }

        $filters = ['nhan_su_id' => $nhanSuId];
        if (!$showAll) {
            $filters['month'] = $month;
            $filters['year'] = $year;
        }
        $luongChiTiet = $phanBoNhanSuModel->getAllLuong($filters);

        $tongLuong = 0;
        $tongHoaHong = 0;
        foreach ($luongChiTiet as $row) {
            $tongLuong += (float)($row['tong_luong'] ?? 0);
            $tongHoaHong += (float)($row['tien_hoa_hong'] ?? 0);
        }
        if (($nhanSu['vai_tro'] ?? '') === 'HDV' && !$showAll && $month !== '' && $year !== '') {
            $tongLuong = (float)($nhanSu['luong_co_ban'] ?? 0) + $tongHoaHong;
        }

        require 'views/admin/ajax_chi_tiet_luong.php';
        exit;
    }

    public function taoLuongThuong() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        require_once __DIR__ . '/../models/NhanSu.php';
        require_once __DIR__ . '/../models/LichKhoiHanh.php';

        $phanBoNhanSuModel = new PhanBoNhanSu();
        $nhanSuModel = new NhanSu();
        $lichKhoiHanhModel = new LichKhoiHanh();

        $schema = validateInputSchema([
            'nhan_su_id' => ['type' => 'id', 'required' => true],
            'lich_khoi_hanh_id' => ['type' => 'id', 'required' => true],
            'loai_luong' => ['type' => 'string', 'required' => true, 'enum' => ['CoDinh', 'PhanTram', 'KetHop']],
            'so_tien_co_dinh' => ['type' => 'money', 'required' => false, 'min' => 0],
            'phan_tram_hoa_hong' => ['type' => 'money', 'required' => false, 'min' => 0, 'max' => 100],
            'ghi_chu' => ['type' => 'string', 'required' => false, 'max' => 500],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu tao luong thuong khong hop le.');
            $_SESSION['error'] = 'Du lieu tao luong thuong khong hop le.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $nhanSuId = (int)($schema['data']['nhan_su_id'] ?? 0);
        $lichKhoiHanhId = (int)($schema['data']['lich_khoi_hanh_id'] ?? 0);
        $loaiLuong = (string)($schema['data']['loai_luong'] ?? 'CoDinh');
        $soTienCoDinh = (float)($schema['data']['so_tien_co_dinh'] ?? 0);
        $phanTramHoaHong = (float)($schema['data']['phan_tram_hoa_hong'] ?? 0);
        $ghiChu = (string)($schema['data']['ghi_chu'] ?? '');

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
        $tinh = $this->tinhLuong($loaiLuong, $soTienCoDinh, $phanTramHoaHong, $doanhThu);

        $existing = $phanBoNhanSuModel->findByLichKhoiHanhAndNhanSu($lichKhoiHanhId, $nhanSuId);
        if ($existing) {
            $phanBoNhanSuModel->updateLuongFull(
                $existing['id'],
                array_merge($tinh, [
                    'trang_thai_luong' => 'ChoDuyet',
                    'ghi_chu' => $ghiChu,
                ])
            );
            $_SESSION['success'] = 'Đã cập nhật lương/thưởng.';
        } else {
            $id = $phanBoNhanSuModel->insert(array_merge([
                'lich_khoi_hanh_id' => $lichKhoiHanhId,
                'nhan_su_id' => $nhanSuId,
                'vai_tro' => $nhanSu['vai_tro'] ?? 'Khac',
                'ghi_chu' => $ghiChu,
                'trang_thai' => 'DaXacNhan',
            ], $tinh, [
                'trang_thai_luong' => 'ChoDuyet',
                'ngay_tao_luong' => date('Y-m-d H:i:s'),
                'ngay_cap_nhat_luong' => date('Y-m-d H:i:s'),
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

    public function capNhatLuongThuong() {
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

        $loaiLuong = $_POST['loai_luong'] ?? ($row['loai_luong'] ?? 'CoDinh');
        $soTienCoDinh = $_POST['so_tien_co_dinh'] ?? ($row['so_tien_co_dinh'] ?? 0);
        $phanTramHoaHong = $_POST['phan_tram_hoa_hong'] ?? ($row['phan_tram_hoa_hong'] ?? 0);
        $trangThaiLuong = $_POST['trang_thai_luong'] ?? ($row['trang_thai_luong'] ?? 'ChoDuyet');
        $ghiChu = trim($_POST['ghi_chu'] ?? ($row['ghi_chu'] ?? ''));

        if (!in_array($trangThaiLuong, ['ChoDuyet', 'DaDuyet', 'DaThanhToan'], true)) {
            $trangThaiLuong = 'ChoDuyet';
        }

        $doanhThu = $phanBoNhanSuModel->getDoanhThuByLichKhoiHanh((int)($row['lich_khoi_hanh_id'] ?? 0));
        $tinh = $this->tinhLuong($loaiLuong, $soTienCoDinh, $phanTramHoaHong, $doanhThu);

        $ok = $phanBoNhanSuModel->updateLuongFull(
            $id,
            array_merge($tinh, [
                'trang_thai_luong' => $trangThaiLuong,
                'ghi_chu' => $ghiChu,
            ])
        );

        $_SESSION[$ok ? 'success' : 'error'] = $ok ? 'Đã cập nhật lương/thưởng.' : 'Cập nhật thất bại.';

        $redirect = $_POST['redirect'] ?? null;
        header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/quanLyLuongThuong'));
        exit;
    }

    public function duyetLuongNhanSu() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();

        $nhanSuId = (int)($_POST['nhan_su_id'] ?? 0);
        $month = (int)($_POST['month'] ?? 0);
        $year = (int)($_POST['year'] ?? 0);
        if ($nhanSuId <= 0 || $month <= 0 || $year <= 0) {
            $_SESSION['error'] = 'Thiếu tham số duyệt lương.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        $affected = $phanBoNhanSuModel->updateTrangThaiLuongByNhanSuThangNam($nhanSuId, $month, $year, 'DaDuyet');
        $_SESSION[$affected > 0 ? 'success' : 'error'] = $affected > 0 ? 'Đã duyệt lương tháng này.' : 'Không có dòng nào để duyệt (có thể đã thanh toán hết).';

        $redirect = $_POST['redirect'] ?? null;
        header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
        exit;
    }

    public function thanhToanLuongNhanSu() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();

        $nhanSuId = (int)($_POST['nhan_su_id'] ?? 0);
        $month = (int)($_POST['month'] ?? 0);
        $year = (int)($_POST['year'] ?? 0);
        if ($nhanSuId <= 0 || $month <= 0 || $year <= 0) {
            $_SESSION['error'] = 'Thiếu tham số thanh toán lương.';
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }

        // Chỉ thanh toán khi tất cả dòng trong kỳ đã được duyệt (không còn ChoDuyet)
        $rows = $phanBoNhanSuModel->getAllLuong([
            'nhan_su_id' => $nhanSuId,
            'month' => $month,
            'year' => $year,
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
            header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
            exit;
        }

        $affected = $phanBoNhanSuModel->updateTrangThaiLuongByNhanSuThangNam($nhanSuId, $month, $year, 'DaThanhToan');
        $_SESSION[$affected > 0 ? 'success' : 'error'] = $affected > 0 ? 'Đã đánh dấu đã thanh toán.' : 'Không có dòng nào được thanh toán (cần ở trạng thái Đã duyệt).';

        $redirect = $_POST['redirect'] ?? null;
        header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
        exit;
    }

    public function tinhLaiLuongNhanSu() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyLuongThuong');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyLuongThuong');

        require_once __DIR__ . '/../models/PhanBoNhanSu.php';
        $phanBoNhanSuModel = new PhanBoNhanSu();

        $nhanSuId = (int)($_POST['nhan_su_id'] ?? 0);
        $month = (int)($_POST['month'] ?? 0);
        $year = (int)($_POST['year'] ?? 0);
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
        header('Location: ' . (!empty($redirect) ? $redirect : 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId . '&month=' . $month . '&year=' . $year));
        exit;
    }
    
    public function __construct() {
        requireRole('Admin');
        // khi vào gốc dự án sẽ gọi new AdminController(). Trong AdminController::__construct() có requireRole('Admin') → requireLogin() → nếu chưa đăng nhập thì chuyển hướng sang auth/login. Nên luôn thấy trang đăng nhập trước khi có session.
    }

    // Helper: Lấy KPI cảnh báo thực tế từ DB
    private function buildKpiAlerts(array $bookingStatusStats) {
        // 1. Booking chờ xác nhận - dùng lại data cache đã có, đúng status 'ChoXacNhan'
        $bookingPending = (int)($bookingStatusStats['ChoXacNhan'] ?? 0);

        // 2. Payment mismatch: payments ThanhCong/DaDoiSoat chưa có giao dịch Thu
        $paymentMismatch = 0;
        try {
            $conn = connectDB();
            $stmt = $conn->prepare(
                "SELECT COUNT(DISTINCT p.payment_id) AS cnt
                 FROM payments p
                 WHERE p.status IN ('ThanhCong', 'DaDoiSoat')
                   AND NOT EXISTS (
                       SELECT 1 FROM giao_dich_tai_chinh g
                       WHERE g.booking_id = p.booking_id
                         AND g.loai = 'Thu'
                   )"
            );
            $stmt->execute();
            $paymentMismatch = (int)($stmt->fetchColumn() ?? 0);
        } catch (Throwable $e) {
            error_log('[AdminController::buildKpiAlerts] paymentMismatch error: ' . $e->getMessage());
        }

        // 3. Công nợ HDV quá hạn > 7 ngày chưa duyệt
        $overdueDebt = 0;
        try {
            require_once __DIR__ . '/../models/CongNoHDV.php';
            $congNoHdvModel = new CongNoHDV();
            $overdueDebt = $congNoHdvModel->getQuaHanCount(7);
        } catch (Throwable $e) {
            error_log('[AdminController::buildKpiAlerts] overdueDebt error: ' . $e->getMessage());
        }

        $anchorDate = $this->resolveDashboardAnchorDate();
        $monthToDateRevenue = $this->getMonthToDateRevenue($anchorDate);

        return [
            'bookingPending' => $bookingPending,
            'paymentMismatch' => $paymentMismatch,
            'overdueDebt' => $overdueDebt,
            'monthToDateRevenue' => (float)($monthToDateRevenue['amount'] ?? 0),
            'monthToDateFrom' => (string)($monthToDateRevenue['from'] ?? ''),
            'monthToDateTo' => (string)($monthToDateRevenue['to'] ?? ''),
        ];
    }

    private function buildAutomationSnapshot() {
        $snapshot = [
            'recentEvents24h' => 0,
            'highSeverity24h' => 0,
            'openDecisionAssist' => 0,
            'criticalTours' => 0,
            'highPriorityBookings' => 0,
            'lastRun' => null,
            'recentEvents' => [],
        ];

        try {
            $conn = connectDB();

            $snapshot['recentEvents24h'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)")
                ->fetchColumn();

            $snapshot['highSeverity24h'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events WHERE severity = 'high' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)")
                ->fetchColumn();

            $snapshot['openDecisionAssist'] = (int)$conn
                ->query("SELECT COUNT(*) FROM admin_decision_assist WHERE status = 'open'")
                ->fetchColumn();

            $snapshot['criticalTours'] = (int)$conn
                ->query("SELECT COUNT(*) FROM tour_health_score WHERE health_level = 'Critical'")
                ->fetchColumn();

            $snapshot['highPriorityBookings'] = (int)$conn
                ->query("SELECT COUNT(*) FROM booking_priority WHERE priority_label = 'High'")
                ->fetchColumn();

            $lastRunStmt = $conn->query("SELECT job_name, is_success, created_at
                                         FROM automation_job_runs
                                         ORDER BY run_id DESC
                                         LIMIT 1");
            $snapshot['lastRun'] = $lastRunStmt ? $lastRunStmt->fetch(PDO::FETCH_ASSOC) : null;

            $eventsStmt = $conn->query("SELECT title, severity, created_at
                                        FROM automation_events
                                        ORDER BY event_id DESC
                                        LIMIT 5");
            $snapshot['recentEvents'] = $eventsStmt ? $eventsStmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            // Keep default snapshot values when automation tables are unavailable.
        }

        return $snapshot;
    }
    
    public function dashboard() {
        try {
            $this->markAdminDashboardNotificationsSeen();
        } catch (Throwable $e) {
            $this->initAdminNotificationState();
        }

        $dashboardData = cacheRemember('admin_dashboard_overview_v1', 120, function () {
            require_once __DIR__ . '/../models/GiaoDich.php';
            require_once __DIR__ . '/../models/Booking.php';
            require_once __DIR__ . '/../models/KhachHang.php';
            require_once __DIR__ . '/../models/DanhGia.php';
            require_once __DIR__ . '/../models/LichKhoiHanh.php';

            $tourModel = new Tour();
            $giaoDichModel = new GiaoDich();
            $bookingModel = new Booking();
            $khachHangModel = new KhachHang();
            $danhGiaModel = new DanhGia();
            $lichKhoiHanhModel = new LichKhoiHanh();

            $toursRaw = $tourModel->getDashboardTourStats();
            $tourIds = array_map(static function ($tour) {
                return (int)($tour['tour_id'] ?? 0);
            }, $toursRaw);
            $tongThuChiMap = $giaoDichModel->getTongThuChiByTourIds($tourIds);

            $tours = [];
            $tourStatusStats = [];
            foreach ($toursRaw as $tour) {
                $tourId = (int)($tour['tour_id'] ?? 0);
                $tongThu = (float)($tongThuChiMap[$tourId]['tong_thu'] ?? 0);
                $tongChi = (float)($tongThuChiMap[$tourId]['tong_chi'] ?? 0);
                $tours[] = [
                    'ten_tour' => $tour['ten_tour'],
                    'tong_thu' => $tongThu,
                    'tong_chi_thuc_te' => $tongChi,
                    'tong_du_toan' => $tour['gia_co_ban'],
                    'loi_nhuan' => $tongThu - $tongChi,
                ];

                $status = $tour['trang_thai'] ?? 'Khác';
                $tourStatusStats[$status] = ($tourStatusStats[$status] ?? 0) + 1;
            }

            $bookingStatusStats = $bookingModel->getStatusCounts();

            return [
                'tours' => $tours,
                'doanhThuTheoThang' => $giaoDichModel->getTongThuTheoThang(12),
                'bookingStatusStats' => $bookingStatusStats,
                'khachHangMoiTheoThang' => $khachHangModel->getNewCustomersByMonth(12),
                'tourStatusStats' => $tourStatusStats,
                'feedbackStats' => $danhGiaModel->getTourFeedbackBuckets(),
                'bookingManageStats' => $bookingStatusStats,
                'lichKhoiHanhStats' => $lichKhoiHanhModel->getScheduleCountByMonth(12),
            ];
        });

        $tours = $dashboardData['tours'] ?? [];
        $doanhThuTheoThang = $dashboardData['doanhThuTheoThang'] ?? [];
        $bookingStatusStats = $dashboardData['bookingStatusStats'] ?? [];
        $khachHangMoiTheoThang = $dashboardData['khachHangMoiTheoThang'] ?? [];
        $tourStatusStats = $dashboardData['tourStatusStats'] ?? [];
        $feedbackStats = $dashboardData['feedbackStats'] ?? [];
        $bookingManageStats = $dashboardData['bookingManageStats'] ?? [];
        $lichKhoiHanhStats = $dashboardData['lichKhoiHanhStats'] ?? [];

        // P5: Wrap buildKpiAlerts + buildAutomationSnapshot vào cache ngắn (60s)
        // để tránh 7 extra queries mỗi lần load dashboard khi nhiều admin đồng thời.
        $kpiAlerts = cacheRemember('admin_kpi_alerts_v1', 60, function () use ($bookingStatusStats) {
            return $this->buildKpiAlerts($bookingStatusStats);
        });
        $automationSnapshot = cacheRemember('admin_automation_snapshot_v1', 60, function () {
            return $this->buildAutomationSnapshot();
        });

        require 'views/admin/dashboard.php';
    }
    
    public function quanLyTour() {
        $tourModel = new Tour();

        $loaiTour = $_GET['loai_tour'] ?? '';
        $trangThai = $_GET['trang_thai'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $conditions = [];
        if (!empty($loaiTour)) $conditions['loai_tour'] = $loaiTour;
        if (!empty($trangThai)) $conditions['trang_thai'] = $trangThai;

        $perPage = 20;
        $pageNumber = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($pageNumber - 1) * $perPage;

        $totalTours = $tourModel->countFiltered($conditions, $search);
        $tours = $tourModel->getAllPaginated($conditions, $search, $perPage, $offset);
        $totalPages = (int)ceil($totalTours / $perPage);

        require 'views/admin/quan_ly_tour.php';
    }
    
    public function chiTietTour() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $tour = null;
        $lichTrinhList = [];
        $lichKhoiHanhList = [];
        $hinhAnhList = [];
        $error = null;

        if ($id <= 0) {
            $error = 'Thiếu mã tour cần xem chi tiết.';
        } else {
            $tourModel = new Tour();
            $lichKhoiHanhModel = new LichKhoiHanh();
            $tour = $tourModel->findById($id);
            if (!$tour) {
                $error = 'Tour không tồn tại hoặc đã bị xóa.';
            } else {
                $lichTrinhList = $tourModel->getLichTrinhByTourId($id);
                $lichKhoiHanhList = $lichKhoiHanhModel->getByTourId($id);
                $hinhAnhList = $tourModel->getHinhAnhByTourId($id);
            }
        }

        require 'views/admin/chi_tiet_tour_admin.php';
    }
    
    // File: controllers/AdminController.php

// ... các code khác ...

    public function quanLyNguoiDung() {
    // 1. Lấy tham số tìm kiếm và lọc từ URL (GET)
    // Các tên biến PHẢI khớp với tên trong form của View: name="search" và name="role"
    $search = trim($_GET['search'] ?? '');
    $role = $_GET['role'] ?? '';
    $status = $_GET['status'] ?? '';
    
    // 2. Load Model và gọi phương thức lọc
        require_once __DIR__ . '/../models/NguoiDung.php';
    $nguoiDungModel = new NguoiDung();
    
    // Phương thức này cần được bạn tạo trong NguoiDung.php
    $users = $nguoiDungModel->getFilteredUsers($search, $role, $status);
    $userStats = $nguoiDungModel->getUserStats($search, $role, $status);
    
    // 3. Truyền các biến cần thiết xuống View
    // View của bạn cần $users, $search, và $role để hiển thị dữ liệu và giữ trạng thái form.
    // Nếu bạn không dùng framework, cách đơn giản nhất là khai báo chúng:
    
    // $users đã có
    // $search đã có
    // $role đã có
    
    // 4. Load View
        require __DIR__ . '/../views/admin/quan_ly_nguoi_dung.php';
    }

    public function capNhatTrangThaiNguoiDung() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }
        $this->requirePostCsrf('admin/quanLyNguoiDung');

        require_once __DIR__ . '/../models/NguoiDung.php';
        $nguoiDungModel = new NguoiDung();

        $userId = requestInt('user_id', 0, 'POST');
        $status = requestString('status', '', 'POST');
        $adminId = (int)($_SESSION['user_id'] ?? 0);

        $allowedStatus = ['HoatDong', 'BiKhoa'];
        if ($userId <= 0 || !in_array($status, $allowedStatus, true)) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Dữ liệu cập nhật không hợp lệ.',
            ];
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }

        if ($userId === $adminId && $status === 'BiKhoa') {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Không thể tự khóa tài khoản đang đăng nhập.',
            ];
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }

        $targetUser = $nguoiDungModel->findById($userId);
        if (!$targetUser) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Người dùng không tồn tại.',
            ];
            header('Location: index.php?act=admin/quanLyNguoiDung');
            exit;
        }

        $isUpdated = $nguoiDungModel->updateStatus($userId, $status);
        $_SESSION['flash'] = [
            'type' => $isUpdated ? 'success' : 'error',
            'message' => $isUpdated
                ? 'Đã cập nhật trạng thái tài khoản.'
                : 'Không thể cập nhật trạng thái tài khoản.',
        ];

        $query = [
            'act=admin/quanLyNguoiDung',
            'search=' . urlencode(requestString('search', '', 'POST')),
            'role=' . urlencode(requestString('role', '', 'POST')),
            'status=' . urlencode(requestString('status_filter', '', 'POST')),
        ];

        header('Location: index.php?' . implode('&', $query));
        exit;
    }
// ... các code khác ...
    
    public function quanLyBooking() {
        $bookingModel = new Booking();

        $perPage = 20;
        $pageNumber = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($pageNumber - 1) * $perPage;

        $filters = [
            'trang_thai' => $_GET['trang_thai'] ?? '',
            'search'     => trim($_GET['search'] ?? ''),
            'co_yeu_cau_tour' => isset($_GET['co_yeu_cau_tour']) ? (string)$_GET['co_yeu_cau_tour'] : '',
            'exclude_hidden' => true,
            'only_paid' => true,
        ];

        $totalBookings = $bookingModel->countAllWithDetailsFiltered($filters);
        $bookings      = $bookingModel->getAllWithDetailsFiltered($filters, $perPage, $offset);
        $totalPages    = (int)ceil($totalBookings / $perPage);

        // Gắn yêu cầu tour vào mỗi booking (dùng nguoi_dung_id đã có trong SELECT)
        try {
            $thongBaoModel = new ThongBao();
            $yeuCauMap = $thongBaoModel->getYeuCauTourByUserIds(array_column($bookings, 'nguoi_dung_id'));

            foreach ($bookings as &$booking) {
                $ndId = (int)($booking['nguoi_dung_id'] ?? 0);
                $booking['yeu_cau_tour'] = $ndId > 0 ? ($yeuCauMap[$ndId] ?? null) : null;
            }
            unset($booking);
        } catch (Exception $e) {
            foreach ($bookings as &$booking) {
                $booking['yeu_cau_tour'] = null;
            }
            unset($booking);
        }

        require 'views/admin/quan_ly_booking.php';
    }

    public function bookingDaHoanThanh() {
        $bookingModel = new Booking();

        $perPage = 20;
        $pageNumber = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($pageNumber - 1) * $perPage;

        $filters = [
            'trang_thai' => 'HoanTat',
            'search' => trim($_GET['search'] ?? ''),
            'co_yeu_cau_tour' => isset($_GET['co_yeu_cau_tour']) ? (string)$_GET['co_yeu_cau_tour'] : '',
            'exclude_hidden' => false,
        ];

        $totalBookings = $bookingModel->countAllWithDetailsFiltered($filters);
        $bookings = $bookingModel->getAllWithDetailsFiltered($filters, $perPage, $offset);
        $totalPages = (int)ceil($totalBookings / $perPage);

        try {
            $thongBaoModel = new ThongBao();
            $yeuCauMap = $thongBaoModel->getYeuCauTourByUserIds(array_column($bookings, 'nguoi_dung_id'));

            foreach ($bookings as &$booking) {
                $ndId = (int)($booking['nguoi_dung_id'] ?? 0);
                $booking['yeu_cau_tour'] = $ndId > 0 ? ($yeuCauMap[$ndId] ?? null) : null;
            }
            unset($booking);
        } catch (Exception $e) {
            foreach ($bookings as &$booking) {
                $booking['yeu_cau_tour'] = null;
            }
            unset($booking);
        }

        $isCompletedView = true;
        require 'views/admin/quan_ly_booking.php';
    }

    public function yeuCauDacBiet() {
        require_once 'models/YeuCauDacBiet.php';
        require_once 'models/Tour.php';
        require_once 'models/Booking.php';

        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'tour_id' => isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0,
            'muc_do_uu_tien' => $_GET['muc_do_uu_tien'] ?? '',
            'trang_thai' => $_GET['trang_thai'] ?? '',
            'loai_yeu_cau' => $_GET['loai_yeu_cau'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $yeuCauModel = new YeuCauDacBiet();
        $requests = $yeuCauModel->getAllForAdmin($filters);
        $stats = $yeuCauModel->getSummaryStats();
        $histories = $yeuCauModel->getHistoriesByRequestIds(array_column($requests, 'id'));

        $tourModel = new Tour();
        $tourList = $tourModel->getOptions(500);

        // Danh sách booking để admin có thể chọn khi tạo yêu cầu mới
        $bookingModel = new Booking();
        $bookingList = $bookingModel->getRecentOptionsForSpecialRequests(500);

        require 'views/admin/quan_ly_yeu_cau_dac_biet.php';
    }

    public function capNhatYeuCauDacBiet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        $this->requirePostCsrf('admin/yeuCauDacBiet');

        $schema = validateInputSchema([
            'yeu_cau_id' => ['type' => 'id', 'required' => true],
            'trang_thai' => ['type' => 'string', 'required' => false, 'max' => 50],
            'muc_do_uu_tien' => ['type' => 'string', 'required' => false, 'max' => 50],
            'ghi_chu_hdv' => ['type' => 'string', 'required' => false, 'max' => 1000],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu cap nhat yeu cau dac biet khong hop le.');
            $_SESSION['error'] = 'Du lieu cap nhat yeu cau dac biet khong hop le.';
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        $yeuCauId = (int)($schema['data']['yeu_cau_id'] ?? 0);
        if ($yeuCauId <= 0) {
            $_SESSION['error'] = 'Thiếu mã yêu cầu cần cập nhật.';
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        require_once 'models/YeuCauDacBiet.php';
        $yeuCauModel = new YeuCauDacBiet();

        $data = [
            'trang_thai' => $schema['data']['trang_thai'] ?? null,
            'muc_do_uu_tien' => $schema['data']['muc_do_uu_tien'] ?? null,
            'ghi_chu_hdv' => $schema['data']['ghi_chu_hdv'] ?? null
        ];

        $nguoiDungId = $_SESSION['user_id'] ?? null;
        // Admin không phải nhân sự nên không gán vào nguoi_xu_ly_id (FK sang nhan_su),
        // chỉ dùng user_id để lưu lịch sử thao tác.
        $result = $yeuCauModel->updateByAdmin($yeuCauId, $data, null, $nguoiDungId);

        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Cập nhật yêu cầu thành công.' : 'Không thể cập nhật yêu cầu.';

        header('Location: index.php?act=admin/yeuCauDacBiet');
        exit();
    }

    /**
     * Admin tạo mới yêu cầu đặc biệt cho một booking cụ thể
     */
    public function taoYeuCauDacBiet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        $this->requirePostCsrf('admin/yeuCauDacBiet');

        $schema = validateInputSchema([
            'booking_id' => ['type' => 'id', 'required' => true],
            'loai_yeu_cau' => ['type' => 'string', 'required' => false, 'max' => 50],
            'tieu_de' => ['type' => 'string', 'required' => false, 'max' => 255],
            'mo_ta' => ['type' => 'string', 'required' => false, 'max' => 5000],
            'muc_do_uu_tien' => ['type' => 'string', 'required' => false, 'max' => 50],
            'trang_thai' => ['type' => 'string', 'required' => false, 'max' => 50],
            'ghi_chu_hdv' => ['type' => 'string', 'required' => false, 'max' => 1000],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu tao yeu cau dac biet khong hop le.');
            $_SESSION['error'] = 'Du lieu tao yeu cau dac biet khong hop le.';
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        $bookingId = (int)($schema['data']['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            $_SESSION['error'] = 'Vui lòng chọn booking/khách hàng cần tạo yêu cầu.';
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        require_once 'models/YeuCauDacBiet.php';
        $yeuCauModel = new YeuCauDacBiet();

        $data = [
            'loai_yeu_cau' => $schema['data']['loai_yeu_cau'] ?? 'khac',
            'tieu_de' => (string)($schema['data']['tieu_de'] ?? ''),
            'mo_ta' => $schema['data']['mo_ta'] ?? null,
            'muc_do_uu_tien' => $schema['data']['muc_do_uu_tien'] ?? 'trung_binh',
            'trang_thai' => $schema['data']['trang_thai'] ?? 'moi',
            'ghi_chu_hdv' => $schema['data']['ghi_chu_hdv'] ?? null,
        ];

        if ($data['tieu_de'] === '') {
            $data['tieu_de'] = 'Yêu cầu đặc biệt';
        }

        $nguoiTaoId = $_SESSION['user_id'] ?? null;
        if (!$nguoiTaoId) {
            $_SESSION['error'] = 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.';
            header('Location: index.php?act=auth/login');
            exit();
        }

        $newId = $yeuCauModel->createFromAdmin($bookingId, $data, $nguoiTaoId);

        if ($newId) {
            $_SESSION['success'] = 'Đã tạo yêu cầu đặc biệt mới cho khách.';
        } else {
            $_SESSION['error'] = 'Không thể tạo yêu cầu đặc biệt. Vui lòng thử lại.';
        }

        header('Location: index.php?act=admin/yeuCauDacBiet');
        exit();
    }
    
    public function addNhacungcap() {
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
                        'ten_don_vi'   => $tenDonVi,
                        'loai_dich_vu' => $loaiDichVu !== '' ? $loaiDichVu : null,
                        'nguoi_dung_id'=> $nguoiDungId,
                        'dia_chi'      => $diaChi,
                        'lien_he'      => $lienHe,
                        'mo_ta'        => $moTa
                    ];
                    $nhaCungCapModel->create($data);

                    // Nếu có gắn với tài khoản người dùng, cập nhật vai trò thành NhaCungCap
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
        $nhaCungCapModel = new NhaCungCap();
        $nhaCungCapList = $nhaCungCapModel->getAll();
        
        // Danh sách tài khoản để admin gán nhanh thành nhà cung cấp
        $nguoiDungModel = new NguoiDung();
        $supplierUsers = [];
        try {
            // Lấy TẤT CẢ tài khoản CHƯA gắn với bất kỳ nhà cung cấp nào (không giới hạn vai trò)
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
                        'ten_don_vi' => $tenDonVi,
                        'loai_dich_vu' => $loaiDichVu !== '' ? $loaiDichVu : null,
                        'dia_chi' => $diaChi,
                        'lien_he' => $lienHe,
                        'mo_ta' => $moTa
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
        require_once 'models/SupplierDeletionHistory.php';
        $nhaCungCapModel = new NhaCungCap();
        $nguoiDungModel = new NguoiDung();
        $deletionHistoryModel = new SupplierDeletionHistory();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhaCungCap');
            $schema = validateInputSchema([
                'id_nha_cung_cap' => ['type' => 'id', 'required' => true],
                'mat_khau' => ['type' => 'string', 'required' => true, 'min' => 1],
                'ly_do_xoa' => ['type' => 'string', 'required' => false, 'max' => 1000],
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
            
            // Kiểm tra mật khẩu admin
            $adminId = $_SESSION['user_id'] ?? 0;
            $admin = $nguoiDungModel->findById($adminId);
            
            if (!$admin || !password_verify($matKhau, $admin['mat_khau'])) {
                $_SESSION['error'] = 'Mật khẩu không đúng.';
                header('Location: index.php?act=admin/nhaCungCap&id=' . $id);
                exit();
            }
            
            try {
                // Lấy thông tin nhà cung cấp trước khi xóa
                $nhaCungCap = $nhaCungCapModel->findById($id);
                if (!$nhaCungCap) {
                    $_SESSION['error'] = 'Không tìm thấy nhà cung cấp';
                } else {
                    // Lưu thông tin nhà cung cấp vào JSON trước khi xóa
                    $thongTinNCC = json_encode([
                        'id_nha_cung_cap' => $nhaCungCap['id_nha_cung_cap'],
                        'ten_don_vi' => $nhaCungCap['ten_don_vi'] ?? 'N/A',
                        'loai_dich_vu' => $nhaCungCap['loai_dich_vu'] ?? null,
                        'dia_chi' => $nhaCungCap['dia_chi'] ?? null,
                        'lien_he' => $nhaCungCap['lien_he'] ?? null,
                        'mo_ta' => $nhaCungCap['mo_ta'] ?? null,
                        'nguoi_dung_id' => $nhaCungCap['nguoi_dung_id'] ?? null
                    ], JSON_UNESCAPED_UNICODE);
                    
                    // Xóa các bản ghi liên quan trước (cascade delete)
                    // 1. Xóa phân bổ dịch vụ
                    $sql1 = "DELETE FROM phan_bo_dich_vu WHERE nha_cung_cap_id = ?";
                    $stmt1 = $nhaCungCapModel->conn->prepare($sql1);
                    $stmt1->execute([$id]);
                    
                    // 2. Xóa danh mục dịch vụ của nhà cung cấp
                    $sql2 = "DELETE FROM dich_vu_nha_cung_cap WHERE nha_cung_cap_id = ?";
                    $stmt2 = $nhaCungCapModel->conn->prepare($sql2);
                    $stmt2->execute([$id]);
                    
                    // 3. Xóa nhà cung cấp
                    $result = $nhaCungCapModel->delete($id);
                    
                    if ($result) {
                        // Lưu vào lịch sử xóa
                        $deletionHistoryModel->insert([
                            'nha_cung_cap_id' => $id,
                            'nguoi_dung_id' => $nhaCungCap['nguoi_dung_id'] ?? null,
                            'nguoi_xoa_id' => $adminId,
                            'ly_do_xoa' => $lyDoXoa,
                            'thong_tin_nha_cung_cap' => $thongTinNCC
                        ]);
                        
                        // Nếu có gắn với user, đổi lại vai trò về KhachHang
                        if (!empty($nhaCungCap['nguoi_dung_id'])) {
                            $nguoiDungModel->update($nhaCungCap['nguoi_dung_id'], ['vai_tro' => 'KhachHang']);
                        }
                        
                        $_SESSION['success'] = 'Xóa nhà cung cấp thành công';
                    } else {
                        $_SESSION['error'] = 'Không thể xóa nhà cung cấp';
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Lỗi khi xóa: ' . $e->getMessage();
            }
        }
        
        header('Location: index.php?act=admin/nhaCungCap');
        exit();
    }
    
    // Xem chi tiết dịch vụ
    public function chiTietDichVu() {
        $nhaCungCapModel = new NhaCungCap();
        $dichVuId = $_GET['id'] ?? 0;
        $nccId = $_GET['ncc_id'] ?? null;
        
        if ($dichVuId <= 0) {
            $_SESSION['error'] = 'Không tìm thấy dịch vụ';
            header('Location: index.php?act=admin/nhaCungCap' . ($nccId ? '&id=' . $nccId : ''));
            exit();
        }
        
        // Admin có thể xem tất cả dịch vụ, không cần kiểm tra nhaCungCapId
        $dichVu = $nhaCungCapModel->getDichVuById($dichVuId);
        
        if (!$dichVu) {
            $_SESSION['error'] = 'Không tìm thấy dịch vụ';
            header('Location: index.php?act=admin/nhaCungCap' . ($nccId ? '&id=' . $nccId : ''));
            exit();
        }
        
        // Lấy thông tin nhà cung cấp nếu chưa có
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
            'action' => ['type' => 'string', 'required' => true, 'enum' => ['approve', 'reject', 'update_price']],
            'ncc_id' => ['type' => 'id', 'required' => false],
            'gia_tien' => ['type' => 'money', 'required' => false, 'min' => 0],
            'ghi_chu' => ['type' => 'string', 'required' => false, 'max' => 500],
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
        $redirect = 'index.php?act=admin/nhaCungCap';
        if ($nccId) {
            $redirect .= '&id=' . $nccId;
        }

        if ($serviceId <= 0 || $action === '') {
            $_SESSION['error'] = 'Dịch vụ hoặc hành động không hợp lệ';
            header('Location: ' . $redirect);
            exit();
        }

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
    public function danhGia() {
        try {
            $this->markAdminReviewNotificationsSeen();
        } catch (Throwable $e) {
            $this->initAdminNotificationState();
        }

        require 'views/admin/danh_gia.php';
    }
    public function nhanSu() {
        $nhanSuModel = new NhanSu();
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';
        $allNhanSu = $nhanSuModel->getAll();
        $data_by_role = [];
        foreach ($allNhanSu as $item) {
            $itemRole = trim((string)($item['vai_tro'] ?? ''));
            if ($itemRole === '') {
                $itemRole = 'Khac';
            }
            if (!isset($data_by_role[$itemRole])) {
                $data_by_role[$itemRole] = [];
            }
            $data_by_role[$itemRole][] = $item;
        }
        $roles = array_keys($data_by_role);
        sort($roles);
        
        // apply filters: if search query, search across all; if role filter, use that role's data
        if ($q !== '') {
            $nhan_su_list = $nhanSuModel->search($q);
            $active_role = null;
        } elseif ($role !== '' && isset($data_by_role[$role])) {
            $nhan_su_list = $data_by_role[$role];
            $active_role = $role;
        } else {
            $nhan_su_list = $allNhanSu;
            $active_role = null;
        }
        
        require 'views/admin/quan_ly_nhan_su.php';
    }

    // Admin: quản lý HDV (danh sách + CRUD cơ bản)
    public function quanLyHDV() {
        $hdvModel = new HDV();
        $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        if ($q !== '') {
            // sử dụng search trên nhan_su (tạm gọi chung)
            $ns = new NhanSu();
            $hdv_list = $ns->search($q);
        } else {
            $hdv_list = $hdvModel->getAll($groupId);
        }
        // load groups
        $groups = [];
        try {
            $stmt = $hdvModel->conn->prepare('SELECT * FROM hdv_groups ORDER BY name ASC');
            $stmt->execute();
            $groups = $stmt->fetchAll();
        } catch (Exception $e) {
            // ignore if table not exists
        }
        require 'views/admin/quan_ly_hdv.php';
    }

    public function quanLyHDVCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/quanLyHDV');
            $model = new HDV();
            $hoTen = requestString('ho_ten', '', 'POST');
            $ngaySinh = $this->optionalPostDate('ngay_sinh');
            $soDienThoai = $this->optionalPostPhone('so_dien_thoai');
            $email = $this->optionalPostEmail('email');
            $groupId = $this->optionalPostId('group_id');

            if ($hoTen === '') {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Họ tên HDV không được để trống.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            if (isset($_POST['ngay_sinh']) && sanitizeText($_POST['ngay_sinh']) !== '' && $ngaySinh === null) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ngày sinh không hợp lệ.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            if (isset($_POST['so_dien_thoai']) && sanitizeText($_POST['so_dien_thoai']) !== '' && $soDienThoai === null) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Số điện thoại không hợp lệ.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            if (isset($_POST['email']) && sanitizeText($_POST['email']) !== '' && $email === null) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email không hợp lệ.'];
                header('Location: index.php?act=admin/quanLyHDV');
                exit;
            }

            $data = [
                'ho_ten' => $hoTen,
                'ngay_sinh' => $ngaySinh,
                'anh' => $this->optionalPostString('anh'),
                'so_dien_thoai' => $soDienThoai,
                'email' => $email,
                'dia_chi' => $this->optionalPostString('dia_chi'),
                'chung_chi' => $this->optionalPostString('chung_chi'),
                'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                'suc_khoe' => $this->optionalPostString('suc_khoe'),
                'group_id' => $groupId,
                'note' => $this->optionalPostString('note'),
            ];
            $ok = $model->insert($data);
            $_SESSION['flash'] = $ok ? ['type'=>'success','message'=>'Thêm HDV thành công'] : ['type'=>'danger','message'=>'Thêm HDV thất bại'];
        }
        header('Location: index.php?act=admin/quanLyHDV'); exit;
    }

    public function quanLyHDVUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/quanLyHDV');
            $model = new HDV();
            $id = requestInt('nhan_su_id', 0, 'POST');
            if ($id > 0) {
                $hoTen = requestString('ho_ten', '', 'POST');
                $ngaySinh = $this->optionalPostDate('ngay_sinh');
                $soDienThoai = $this->optionalPostPhone('so_dien_thoai');
                $email = $this->optionalPostEmail('email');
                $groupId = $this->optionalPostId('group_id');

                if ($hoTen === '') {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Họ tên HDV không được để trống.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                if (isset($_POST['ngay_sinh']) && sanitizeText($_POST['ngay_sinh']) !== '' && $ngaySinh === null) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ngày sinh không hợp lệ.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                if (isset($_POST['so_dien_thoai']) && sanitizeText($_POST['so_dien_thoai']) !== '' && $soDienThoai === null) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Số điện thoại không hợp lệ.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                if (isset($_POST['email']) && sanitizeText($_POST['email']) !== '' && $email === null) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Email không hợp lệ.'];
                    header('Location: index.php?act=admin/quanLyHDV');
                    exit;
                }

                $data = [
                    'ho_ten' => $hoTen,
                    'ngay_sinh' => $ngaySinh,
                    'anh' => $this->optionalPostString('anh'),
                    'so_dien_thoai' => $soDienThoai,
                    'email' => $email,
                    'dia_chi' => $this->optionalPostString('dia_chi'),
                    'chung_chi' => $this->optionalPostString('chung_chi'),
                    'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                    'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                    'suc_khoe' => $this->optionalPostString('suc_khoe'),
                    'group_id' => $groupId,
                    'is_available' => isset($_POST['is_available']) ? 1 : 0,
                    'note' => $this->optionalPostString('note'),
                ];
                $ok = $model->update($id, $data);
                $_SESSION['flash'] = $ok ? ['type'=>'success','message'=>'Cập nhật HDV thành công'] : ['type'=>'danger','message'=>'Cập nhật HDV thất bại'];
            }
        }
        header('Location: index.php?act=admin/quanLyHDV'); exit;
    }

    public function quanLyHDVDelete() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $model = new HDV();
            $ok = $model->delete($id);
            $_SESSION['flash'] = $ok ? ['type'=>'success','message'=>'Xóa HDV thành công'] : ['type'=>'danger','message'=>'Xóa HDV thất bại'];
        }
        header('Location: index.php?act=admin/quanLyHDV'); exit;
    }

    // Hiển thị lịch phân công HDV (calendar)
    public function hdvSchedule() {
        $hdvModel = new HDV();
        // load hdv list
        $hdv_list = $hdvModel->getAll();
        require 'views/admin/hdv_schedule.php';
    }

    // Trang hồ sơ HDV
    public function hdvProfile() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $hdvModel = new HDV();
        $hdv = $hdvModel->findById($id);
        $history = [];
        if ($hdv) {
            $history = $hdvModel->getTourHistory($id, 100);
        }
        require 'views/admin/hdv_profile.php';
    }

    // API: trả về lịch của HDV (JSON)
    public function hdvApiGetSchedule() {
        header('Content-Type: application/json');
        $hdvId = isset($_GET['hdv_id']) ? (int)$_GET['hdv_id'] : 0;
        $hdvModel = new HDV();
        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;
        $events = [];
        if ($hdvId > 0) {
            $rows = $hdvModel->getSchedule($hdvId, $from, $to);
            foreach ($rows as $r) {
                $events[] = [
                    'id' => $r['id'],
                    'title' => 'Tour ' . ($r['tour_id'] ?? ''),
                    'start' => $r['start_time'],
                    'end' => $r['end_time'],
                    'extendedProps' => ['note' => $r['note'] ?? '']
                ];
            }
        }
        echo json_encode($events);
        exit;
    }

    // API: kiểm tra khả dụng
    public function hdvApiCheck() {
        header('Content-Type: application/json');
        $hdvId = isset($_GET['hdv_id']) ? (int)$_GET['hdv_id'] : 0;
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $hdvModel = new HDV();
        $ok = false;
        if ($hdvId && $start && $end) {
            $ok = $hdvModel->isAvailable($hdvId, $start, $end);
        }
        echo json_encode(['available' => $ok]);
        exit;
    }

    // API: phân công (thêm schedule) — POST JSON {hdv_id, tour_id, start, end, note}
    public function hdvApiAssign() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false,'msg'=>'Method not allowed']); exit; }
        $payload = $_POST;
        $hdvId = isset($payload['hdv_id']) ? (int)$payload['hdv_id'] : 0;
        $tourId = isset($payload['tour_id']) ? (int)$payload['tour_id'] : null;
        $start = $payload['start'] ?? null;
        $end = $payload['end'] ?? null;
        $note = $payload['note'] ?? null;
        $hdvModel = new HDV();
        if (!$hdvId || !$start || !$end) { echo json_encode(['ok'=>false,'msg'=>'Thiếu dữ liệu']); exit; }
        if (!$hdvModel->isAvailable($hdvId, $start, $end)) {
            echo json_encode(['ok'=>false,'msg'=>'HDV không rảnh trong khung thời gian này']); exit;
        }
        $ok = $hdvModel->addSchedule($hdvId, $tourId, $start, $end, $note);
        echo json_encode(['ok'=>$ok]); exit;
    }

    // API: đề xuất HDV rảnh cho khoảng thời gian (trả danh sách hdv_id)
    public function hdvApiSuggest() {
        header('Content-Type: application/json');
        $start   = $_GET['start'] ?? null;
        $end     = $_GET['end']   ?? null;
        $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;

        $hdvModel   = new HDV();
        $candidates = $hdvModel->getAll($groupId, true);

        if (empty($candidates) || $start === null || $end === null) {
            echo json_encode(['available' => []]); exit;
        }

        // P6: Một batch query thay vì N query trong vòng lặp
        $allIds       = array_map(fn($c) => (int)$c['nhan_su_id'], $candidates);
        $availableIds = array_flip($hdvModel->getAvailableIdsBatch($allIds, $start, $end));

        $available = [];
        foreach ($candidates as $c) {
            if (isset($availableIds[(int)$c['nhan_su_id']])) {
                $available[] = ['id' => (int)$c['nhan_su_id'], 'ho_ten' => $c['ho_ten']];
            }
        }

        echo json_encode(['available' => $available]); exit;
    }

    public function nhanSuCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhanSu');
            $model = new NhanSu();

            $allowedRoles = ['HDV', 'DieuHanh', 'NhaCungCap', 'Khac'];
            $nguoiDungId = requestInt('nguoi_dung_id', 0, 'POST');
            $vaiTro = requestString('vai_tro', 'Khac', 'POST');

            if (!in_array($vaiTro, $allowedRoles, true)) {
                $vaiTro = 'Khac';
            }

            $data = [
                    'nguoi_dung_id' => $nguoiDungId > 0 ? $nguoiDungId : null,
                    'vai_tro' => $vaiTro,
                'chung_chi' => $this->optionalPostString('chung_chi'),
                'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                'suc_khoe' => $this->optionalPostString('suc_khoe'),
            ];
            
                // Validate: người dùng phải được chọn
                if (empty($data['nguoi_dung_id'])) {
                    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Vui lòng chọn người dùng.'];
                } else {
                    $ok = $model->insert($data);
                    if ($ok) {
                        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Thêm nhân sự thành công. Vai trò người dùng đã được cập nhật.'];
                    } else {
                        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Thêm nhân sự thất bại.'];
                    }
                }
        }
        header('Location: index.php?act=admin/nhanSu');
        exit;
    }

    // API: trả về danh sách người dùng chưa có nhân sự (JSON)
    public function nhanSu_get_users() {
        $model = new NhanSu();
        $users = $model->getAvailableUsers();
        header('Content-Type: application/json');
        echo json_encode(['users' => $users]);
        exit;
    }

    public function nhanSuUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/nhanSu');
            $model = new NhanSu();
            $allowedRoles = ['HDV', 'DieuHanh', 'NhaCungCap', 'Khac'];

            $id = requestInt('nhan_su_id', 0, 'POST');
            if ($id <= 0) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'ID nhân sự không hợp lệ.'];
                header('Location: index.php?act=admin/nhanSu');
                exit;
            }

            $vaiTro = requestString('vai_tro', 'Khac', 'POST');
            if (!in_array($vaiTro, $allowedRoles, true)) {
                $vaiTro = 'Khac';
            }

            $data = [
                'vai_tro' => $vaiTro,
                'chung_chi' => $this->optionalPostString('chung_chi'),
                'ngon_ngu' => $this->optionalPostString('ngon_ngu'),
                'kinh_nghiem' => $this->optionalPostString('kinh_nghiem'),
                'suc_khoe' => $this->optionalPostString('suc_khoe'),
            ];
            $ok = $model->update($id, $data);
            if ($ok) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Cập nhật nhân sự thành công.'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Cập nhật nhân sự thất bại.'];
            }
        }
        header('Location: index.php?act=admin/nhanSu');
        exit;
    }

    public function nhanSuDelete() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/nhanSu');
            exit;
        }

        $this->requirePostCsrf('admin/nhanSu');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $delete_user = isset($_POST['delete_user']) && $_POST['delete_user'] === '1' ? true : false;
        
        if ($id > 0) {
            $model = new NhanSu();
            if ($delete_user) {
                // kiểm tra blocker quan trọng trước khi xóa (chỉ tour.tao_boi)
                $nhanSu = $model->findById($id);
                if ($nhanSu && !empty($nhanSu['nguoi_dung_id'])) {
                    $blockers = $model->getCriticalDeleteBlockers($nhanSu['nguoi_dung_id']);
                    if (!empty($blockers)) {
                        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Không thể xóa tài khoản do: ' . implode(' ', $blockers)];
                        header('Location: index.php?act=admin/nhanSu');
                        exit;
                    }
                }
                $ok = $model->deleteWithUser($id);
                $msg = $ok ? 'Xóa nhân sự, tài khoản và dữ liệu liên quan thành công.' : 'Xóa nhân sú và tài khoản thất bại.';
            } else {
                $ok = $model->delete($id);
                $msg = $ok ? 'Xóa nhân sự thành công. Tài khoản vẫn được giữ.' : 'Xóa nhân sự thất bại.';
            }
            
            if ($ok) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => $msg];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Xóa thất bại.'];
            }
        }
        header('Location: index.php?act=admin/nhanSu');
        exit;
    }

    // Xem chi tiết sơ yếu lý lịch nhân sự
    public function nhanSuChiTiet() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $nhanSu = null;
        $error = null;

        if ($id <= 0) {
            $error = 'Thiếu mã nhân sự cần xem.';
        } else {
            $model = new NhanSu();
            $nhanSu = $model->findById($id);
            
            if (!$nhanSu) {
                $error = 'Nhân sự không tồn tại hoặc đã bị xóa.';
            } else {
                // Lấy thêm thông tin vai trò người dùng
                if (!empty($nhanSu['nguoi_dung_id'])) {
                    $nguoiDungModel = new NguoiDung();
                    $nguoiDung = $nguoiDungModel->findById($nhanSu['nguoi_dung_id']);
                    if ($nguoiDung) {
                        $nhanSu['vai_tro_nguoi_dung'] = $nguoiDung['vai_tro'];
                        $nhanSu['quyen_cap_cao'] = $nguoiDung['quyen_cap_cao'];
                        $nhanSu['trang_thai'] = $nguoiDung['trang_thai'];
                        $nhanSu['ngay_tao'] = $nguoiDung['ngay_tao'];
                        $nhanSu['avatar'] = $nguoiDung['avatar'];
                    }
                }
            }
        }

        require 'views/admin/nhan_su_chi_tiet.php';
    }

    // ==================== QUẢN LÝ HDV NÂNG CAO (SỬ DỤNG DATABASE HIỆN CÓ) ====================
    
    public function hdvAdvanced() {
        $hdvMgmt = new HDVManagement();
        
        $hdv_list = $hdvMgmt->getAllHDV();
        $stats = $hdvMgmt->getThongKeTongQuan();
        $hieu_suat_list = $hdvMgmt->getBaoCaoHieuSuat();
        $thong_bao_list = $hdvMgmt->getThongBao(null, 20);
        $lich_lam_viec = $hdvMgmt->getAllLichLamViec(); // Lấy tất cả lịch làm việc
        $tourOptions = (new Tour())->getOptions(300);
        
        require 'views/admin/hdv_quan_ly_nang_cao.php';
    }

    public function hdvAddSchedule() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hdvMgmt = new HDVManagement();
            
            $data = [
                'tour_id' => $_POST['tour_id'],
                'hdv_id' => $_POST['hdv_id'],
                'ngay_khoi_hanh' => $_POST['ngay_khoi_hanh'],
                'ngay_ket_thuc' => $_POST['ngay_ket_thuc'],
                'diem_tap_trung' => $_POST['diem_tap_trung'] ?? '',
                'trang_thai' => $_POST['trang_thai'] ?? 'DaXacNhan'
            ];
            
            $result = $hdvMgmt->phanCongHDV($data);
            $_SESSION['flash'] = [
                'type' => $result['success'] ? 'success' : 'danger',
                'message' => $result['message']
            ];
        }
        
        header('Location: index.php?act=admin/hdv_advanced');
        exit;
    }

    public function hdvGetSchedule() {
        $hdvMgmt = new HDVManagement();
        
        $hdv_id = $_GET['hdv_id'] ?? null;
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        
        $events = $hdvMgmt->getLichLamViec($hdv_id, $start, $end);
        
        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }

    public function hdvSendNotification() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $hdvMgmt = new HDVManagement();
            
            $data = [
                'nhan_su_id' => !empty($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : null,
                'loai_thong_bao' => $_POST['loai_thong_bao'] ?? 'ThongBao',
                'tieu_de' => $_POST['tieu_de'] ?? '',
                'noi_dung' => $_POST['noi_dung'] ?? '',
                'uu_tien' => $_POST['uu_tien'] ?? 'TrungBinh'
            ];
            
            $result = $hdvMgmt->guiThongBao($data);
            
            $_SESSION['flash'] = [
                'type' => $result ? 'success' : 'danger',
                'message' => $result ? 'Gửi thông báo thành công!' : 'Lỗi khi gửi thông báo!'
            ];
        }
        
        header('Location: index.php?act=admin/hdv_advanced');
        exit;
    }

    public function hdvLichTable() {
        $hdvMgmt = new HDVManagement();
        
        $hdv_list = $hdvMgmt->getAllHDV();
        $lich_lam_viec = $hdvMgmt->getLichLamViec(); // Lấy tất cả lịch
        
        require 'views/admin/hdv_lich_lam_viec_table.php';
    }

    public function hdvDetail() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $hdvMgmt = new HDVManagement();
        $nhanSuModel = new NhanSu();
        
        $hdv = $nhanSuModel->findById($id);
        $hieu_suat = $hdvMgmt->getHieuSuatTheoThang($id);
        $danh_gia_list = $hdvMgmt->getDanhGiaByHDV($id);
        $lich_lam_viec = $hdvMgmt->getLichLamViec($id);
        $nhat_ky_list = $hdvMgmt->getNhatKyByHDV($id);
        
        require 'views/admin/hdv_chi_tiet.php';
    }
    
    private function chonAnhChinh(array $hinhAnhList) {
        foreach ($hinhAnhList as $anh) {
            if (!empty($anh['url_anh'])) {
                return $anh;
            }
        }
        return null;
    }

    // ========== QUẢN LÝ KHÁCH THEO TOUR ==========
    
    // Danh sách khách theo tour
    public function danhSachKhachTheoTour() {
        $lichKhoiHanhId = isset($_GET['lich_khoi_hanh_id']) ? (int)$_GET['lich_khoi_hanh_id'] : 0;
        $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
        
        $tourModel = new Tour();
        $lichKhoiHanhModel = new LichKhoiHanh();
        $bookingModel = new Booking();
        $checkinModel = new TourCheckin();
        $roomModel = new HotelRoomAssignment();
        
        $tour = null;
        $lichKhoiHanh = null;
        $bookingList = [];
        $lichKhoiHanhList = [];
        $checkinStats = null;
        $roomStats = null;
        
        if ($lichKhoiHanhId > 0) {
            $lichKhoiHanh = $lichKhoiHanhModel->findById($lichKhoiHanhId);
            if ($lichKhoiHanh) {
                $tourId = $lichKhoiHanh['tour_id'];
                $tour = $tourModel->findById($tourId);
                
                // Lấy danh sách booking theo lịch khởi hành
                $sql = "SELECT b.*, 
                               nd.ho_ten as khach_ho_ten, 
                               nd.email, 
                               nd.so_dien_thoai,
                               tc.id as checkin_id, 
                               tc.trang_thai as checkin_status
                        FROM booking b
                        LEFT JOIN khach_hang k ON b.khach_hang_id = k.khach_hang_id
                        LEFT JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                        LEFT JOIN tour_checkin tc ON b.booking_id = tc.booking_id
                        WHERE b.tour_id = ? 
                        AND b.ngay_khoi_hanh = (SELECT ngay_khoi_hanh FROM lich_khoi_hanh WHERE id = ?)
                        ORDER BY b.ngay_dat DESC";
                $stmt = $bookingModel->conn->prepare($sql);
                $stmt->execute([$tourId, $lichKhoiHanhId]);
                $bookingList = $stmt->fetchAll();
                
                // Lấy thống kê
                $checkinStats = $checkinModel->getStatsByLichKhoiHanh($lichKhoiHanhId);
                $roomStats = $roomModel->getStatsByLichKhoiHanh($lichKhoiHanhId);
            }
        } else if ($tourId > 0) {
            $tour = $tourModel->findById($tourId);
            $lichKhoiHanhList = $lichKhoiHanhModel->getByTourId($tourId);
        }
        
        require 'views/admin/danh_sach_khach.php';
    }
    
    // Check-in khách
    public function checkInKhach() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $checkinModel = new TourCheckin();
            
            $data = [
                'lich_khoi_hanh_id' => $_POST['lich_khoi_hanh_id'] ?? 0,
                'booking_id' => $_POST['booking_id'] ?? 0,
                'ho_ten' => $_POST['ho_ten'] ?? '',
                'so_cmnd' => $_POST['so_cmnd'] ?? null,
                'so_passport' => $_POST['so_passport'] ?? null,
                'so_dien_thoai' => $_POST['so_dien_thoai'] ?? null,
                'email' => $_POST['email'] ?? null,
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            if ($checkinModel->insert($data)) {
                $_SESSION['success'] = 'Check-in khách thành công!';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi check-in!';
            }
            
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $data['lich_khoi_hanh_id']);
            exit;
        }
        
        // GET: hiển thị form check-in
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        $lichKhoiHanhId = isset($_GET['lich_khoi_hanh_id']) ? (int)$_GET['lich_khoi_hanh_id'] : 0;
        
        $bookingModel = new Booking();
        $checkinModel = new TourCheckin();
        
        $booking = $bookingModel->findById($bookingId);
        $checkin = $checkinModel->getByBookingId($bookingId);
        
        require 'views/admin/check_in.php';
    }
    
    // Cập nhật check-in
    public function updateCheckIn() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $checkinModel = new TourCheckin();
            
            $id = $_POST['id'] ?? 0;
            $data = [
                'ho_ten' => $_POST['ho_ten'] ?? '',
                'so_cmnd' => $_POST['so_cmnd'] ?? null,
                'so_passport' => $_POST['so_passport'] ?? null,
                'so_dien_thoai' => $_POST['so_dien_thoai'] ?? null,
                'email' => $_POST['email'] ?? null,
                'trang_thai' => $_POST['trang_thai'] ?? 'DaCheckIn',
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            if ($checkinModel->update($id, $data)) {
                $_SESSION['success'] = 'Cập nhật check-in thành công!';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
            }
            
            $lichKhoiHanhId = $_POST['lich_khoi_hanh_id'] ?? 0;
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
            exit;
        }
    }
    
    // Phân phòng khách sạn
    public function phanPhongKhachSan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roomModel = new HotelRoomAssignment();
            
            $action = $_POST['action'] ?? 'add';
            $lichKhoiHanhId = $_POST['lich_khoi_hanh_id'] ?? 0;
            
            if ($action === 'add') {
                $data = [
                    'lich_khoi_hanh_id' => $lichKhoiHanhId,
                    'booking_id' => $_POST['booking_id'] ?? 0,
                    'checkin_id' => $_POST['checkin_id'] ?? null,
                    'ten_khach_san' => $_POST['ten_khach_san'] ?? '',
                    'so_phong' => $_POST['so_phong'] ?? '',
                    'loai_phong' => $_POST['loai_phong'] ?? 'Standard',
                    'so_giuong' => $_POST['so_giuong'] ?? 1,
                    'ngay_nhan_phong' => $_POST['ngay_nhan_phong'] ?? null,
                    'ngay_tra_phong' => $_POST['ngay_tra_phong'] ?? null,
                    'gia_phong' => $_POST['gia_phong'] ?? 0,
                    'trang_thai' => $_POST['trang_thai'] ?? 'DaDatPhong',
                    'ghi_chu' => $_POST['ghi_chu'] ?? null
                ];
                
                if ($roomModel->insert($data)) {
                    $_SESSION['success'] = 'Phân phòng thành công!';
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi phân phòng!';
                }
            } else if ($action === 'update') {
                $id = $_POST['id'] ?? 0;
                $data = [
                    'ten_khach_san' => $_POST['ten_khach_san'] ?? '',
                    'so_phong' => $_POST['so_phong'] ?? '',
                    'loai_phong' => $_POST['loai_phong'] ?? 'Standard',
                    'so_giuong' => $_POST['so_giuong'] ?? 1,
                    'ngay_nhan_phong' => $_POST['ngay_nhan_phong'] ?? null,
                    'ngay_tra_phong' => $_POST['ngay_tra_phong'] ?? null,
                    'gia_phong' => $_POST['gia_phong'] ?? 0,
                    'trang_thai' => $_POST['trang_thai'] ?? 'DaDatPhong',
                    'ghi_chu' => $_POST['ghi_chu'] ?? null
                ];
                
                if ($roomModel->update($id, $data)) {
                    $_SESSION['success'] = 'Cập nhật phòng thành công!';
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật!';
                }
            } else if ($action === 'delete') {
                $id = $_POST['id'] ?? 0;
                if ($roomModel->delete($id)) {
                    $_SESSION['success'] = 'Xóa phân phòng thành công!';
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi xóa!';
                }
            }
            
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
            exit;
        }
        
        // GET: hiển thị form phân phòng
        $lichKhoiHanhId = isset($_GET['lich_khoi_hanh_id']) ? (int)$_GET['lich_khoi_hanh_id'] : 0;
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        
        $bookingModel = new Booking();
        $roomModel = new HotelRoomAssignment();
        $checkinModel = new TourCheckin();
        
        $booking = null;
        $roomList = [];
        $hotelList = [];
        $checkin = null;
        
        if ($bookingId > 0) {
            // Lấy thông tin booking với thông tin khách hàng
            $sql = "SELECT b.*, 
                           nd.ho_ten, 
                           nd.email, 
                           nd.so_dien_thoai
                    FROM booking b
                    LEFT JOIN khach_hang k ON b.khach_hang_id = k.khach_hang_id
                    LEFT JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                    WHERE b.booking_id = ?";
            $stmt = $bookingModel->conn->prepare($sql);
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch();
            
            $roomList = $roomModel->getByBookingId($bookingId);
            $checkin = $checkinModel->getByBookingId($bookingId);
        }
        
        if ($lichKhoiHanhId > 0) {
            $hotelList = $roomModel->getHotelList();
        }
        
        require 'views/admin/phan_phong.php';
    }
    
    /**
     * Quản lý nhật ký tour - Admin
     */
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
        $tourModel = new Tour();
        $tours = $tourModel->getOptions(500);
        
        // Lấy danh sách HDV cho filter
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
        $tourModel = new Tour();
        $tours = $tourModel->getOptions(500);
        
        // Lấy danh sách HDV
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
                    // C4: dùng uploadFile() helper — kiểm tra MIME thật + tạo tên file ngẫu nhiên
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
            'tour_id' => $_POST['tour_id'] ?? 0,
            'nhan_su_id' => $_POST['nhan_su_id'] ?? 0,
            'loai_nhat_ky' => $_POST['loai_nhat_ky'] ?? 'hanh_trinh',
            'tieu_de' => $_POST['tieu_de'] ?? '',
            'noi_dung' => $_POST['noi_dung'] ?? '',
            'ngay_ghi' => $_POST['ngay_ghi'] ?? date('Y-m-d H:i:s'),
            'cach_xu_ly' => $_POST['cach_xu_ly'] ?? null,
            'hinh_anh' => !empty($imageUrls) ? json_encode($imageUrls) : null
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
                        $data['cach_xu_ly'], $data['hinh_anh'], $id
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
                        $data['cach_xu_ly'], $id
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
                    $data['cach_xu_ly'], $data['hinh_anh']
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
                
                if ($result) {
                    $_SESSION['success'] = 'Xóa nhật ký thành công';
                } else {
                    $_SESSION['error'] = 'Lỗi khi xóa nhật ký';
                }
            } else {
                $_SESSION['error'] = 'Không tìm thấy nhật ký';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: index.php?act=admin/quanLyNhatKyTour');
        exit;
    }
    
    // Thêm khách vào lịch khởi hành
    public function themKhachLichKhoiHanh() {
        $lichKhoiHanhId = isset($_GET['lich_khoi_hanh_id']) ? (int)$_GET['lich_khoi_hanh_id'] : 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/themKhachLichKhoiHanh');

            $lichKhoiHanhId = requestInt('lich_khoi_hanh_id', $lichKhoiHanhId, 'POST');
            $lichKhoiHanhModel = new LichKhoiHanh();
            $bookingModel = new Booking();
            $khachHangModel = new KhachHang();
            $nguoiDungModel = new NguoiDung();

            $allowedBookingStatus = ['ChoXacNhan', 'DaXacNhan', 'TuChoi', 'Huy', 'HoanTat'];
            
            $lichKhoiHanh = $lichKhoiHanhModel->findById($lichKhoiHanhId);
            if (!$lichKhoiHanh) {
                $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
                header('Location: index.php?act=admin/danhSachKhachTheoTour');
                exit();
            }
            
            // Tìm hoặc tạo người dùng
            $emailRaw = requestString('email', '', 'POST');
            $email = validateEmail($emailRaw);
            $hoTen = requestString('ho_ten', '', 'POST');
            $soDienThoai = $this->optionalPostPhone('so_dien_thoai');
            $diaChi = $this->optionalPostString('dia_chi');
            $gioiTinh = $this->optionalPostString('gioi_tinh');
            $ngaySinh = $this->optionalPostDate('ngay_sinh');
            $soNguoi = requestInt('so_nguoi', 1, 'POST');
            $tongTien = requestFloat('tong_tien', 0, 'POST');
            $trangThai = requestString('trang_thai', 'ChoXacNhan', 'POST');
            $ghiChu = $this->optionalPostString('ghi_chu');
            
            if ($email === null || $hoTen === '') {
                $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin khách hàng.';
                header('Location: index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit();
            }

            if (isset($_POST['so_dien_thoai']) && sanitizeText($_POST['so_dien_thoai']) !== '' && $soDienThoai === null) {
                $_SESSION['error'] = 'Số điện thoại không hợp lệ.';
                header('Location: index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit();
            }

            if (isset($_POST['ngay_sinh']) && sanitizeText($_POST['ngay_sinh']) !== '' && $ngaySinh === null) {
                $_SESSION['error'] = 'Ngày sinh không hợp lệ.';
                header('Location: index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit();
            }

            if ($soNguoi <= 0) {
                $_SESSION['error'] = 'Số người phải lớn hơn 0.';
                header('Location: index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit();
            }

            if ($tongTien < 0) {
                $_SESSION['error'] = 'Tổng tiền không hợp lệ.';
                header('Location: index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit();
            }

            if (!in_array($trangThai, $allowedBookingStatus, true)) {
                $trangThai = 'ChoXacNhan';
            }
            
            // Tìm người dùng theo email
            $nguoiDung = $nguoiDungModel->findByEmail($email);
            $matKhauTam = null;
            if (!$nguoiDung) {
                // Tạo người dùng mới
                $matKhauTam = generateTemporaryPassword();
                $nguoiDungId = $nguoiDungModel->insert([
                    'ho_ten' => $hoTen,
                    'email' => $email,
                    'so_dien_thoai' => $soDienThoai,
                    'vai_tro' => 'KhachHang',
                    'mat_khau' => password_hash($matKhauTam, PASSWORD_DEFAULT)
                ]);
                $nguoiDung = $nguoiDungModel->findById($nguoiDungId);
            }
            
            // Tìm hoặc tạo khách hàng
            $khachHang = $khachHangModel->findOrCreateByNguoiDungInfo(
                $nguoiDung['id'],
                $diaChi,
                $gioiTinh,
                $ngaySinh
            );
            
            // Tạo booking
            $bookingData = [
                'tour_id' => $lichKhoiHanh['tour_id'],
                'khach_hang_id' => $khachHang['khach_hang_id'],
                'ngay_dat' => date('Y-m-d'),
                'ngay_khoi_hanh' => $lichKhoiHanh['ngay_khoi_hanh'],
                'ngay_ket_thuc' => $lichKhoiHanh['ngay_ket_thuc'],
                'so_nguoi' => $soNguoi,
                'tong_tien' => $tongTien,
                'trang_thai' => $trangThai,
                'ghi_chu' => $ghiChu
            ];
            
            $bookingId = $bookingModel->insert($bookingData);
            if ($bookingId) {
                $_SESSION['success'] = 'Thêm khách vào lịch khởi hành thành công.';
                if ($matKhauTam !== null) {
                    $_SESSION['success'] .= ' Tai khoan moi da duoc tao voi mat khau tam thoi: ' . $matKhauTam . '. Vui long thong bao cho khach doi mat khau ngay sau lan dang nhap dau.';
                }
            } else {
                $_SESSION['error'] = 'Không thể thêm booking.';
            }
            
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
            exit();
        }
        
        // GET: hiển thị form
        $lichKhoiHanhModel = new LichKhoiHanh();
        $tourModel = new Tour();
        $nguoiDungModel = new NguoiDung();
        
        $lichKhoiHanh = $lichKhoiHanhModel->findById($lichKhoiHanhId);
        if (!$lichKhoiHanh) {
            $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
            header('Location: index.php?act=admin/danhSachKhachTheoTour');
            exit;
        }
        
        $tour = $tourModel->findById($lichKhoiHanh['tour_id']);
        $khachHangList = $nguoiDungModel->getAll(); // Lấy danh sách khách hàng để chọn
        
        require 'views/admin/them_khach_lich_khoi_hanh.php';
    }
    
    // Sửa khách trong lịch khởi hành
    public function suaKhachLichKhoiHanh() {
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        $lichKhoiHanhId = isset($_GET['lich_khoi_hanh_id']) ? (int)$_GET['lich_khoi_hanh_id'] : 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('admin/suaKhachLichKhoiHanh');

            $bookingId = requestInt('booking_id', $bookingId, 'POST');
            $lichKhoiHanhId = requestInt('lich_khoi_hanh_id', $lichKhoiHanhId, 'POST');
            $bookingModel = new Booking();
            $allowedBookingStatus = ['ChoXacNhan', 'DaXacNhan', 'TuChoi', 'Huy', 'HoanTat'];
            
            $booking = $bookingModel->findById($bookingId);
            if (!$booking) {
                $_SESSION['error'] = 'Booking không tồn tại.';
                header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit;
            }
            
            $soNguoi = requestInt('so_nguoi', 1, 'POST');
            $tongTien = requestFloat('tong_tien', 0, 'POST');
            $trangThai = requestString('trang_thai', 'ChoXacNhan', 'POST');
            $ghiChu = $this->optionalPostString('ghi_chu');

            if ($soNguoi <= 0) {
                $_SESSION['error'] = 'Số người phải lớn hơn 0.';
                header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit;
            }

            if ($tongTien < 0) {
                $_SESSION['error'] = 'Tổng tiền không hợp lệ.';
                header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit;
            }

            if (!in_array($trangThai, $allowedBookingStatus, true)) {
                $trangThai = 'ChoXacNhan';
            }

            $data = [
                'so_nguoi' => $soNguoi,
                'tong_tien' => $tongTien,
                'trang_thai' => $trangThai,
                'ghi_chu' => $ghiChu
            ];
            
            $result = $bookingModel->update($bookingId, $data);
            if ($result) {
                $_SESSION['success'] = 'Cập nhật thông tin booking thành công.';
            } else {
                $_SESSION['error'] = 'Không thể cập nhật booking.';
            }
            
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
            exit;
        }
        
        // GET: hiển thị form
        $bookingModel = new Booking();
        $lichKhoiHanhModel = new LichKhoiHanh();
        $tourModel = new Tour();
        
        $booking = $bookingModel->getBookingWithDetails($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại.';
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
            exit;
        }
        
        $lichKhoiHanh = $lichKhoiHanhModel->findById($lichKhoiHanhId);
        $tour = $tourModel->findById($booking['tour_id']);
        
        require 'views/admin/sua_khach_lich_khoi_hanh.php';
    }
    
    // Xóa khách khỏi lịch khởi hành
    public function xoaKhachLichKhoiHanh() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyBooking');
            exit;
        }

        $this->requirePostCsrf('admin/quanLyBooking');

        $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        
        $bookingModel = new Booking();
        $booking = $bookingModel->findById($bookingId);
        
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại.';
        } else {
            // Chỉ xóa nếu chưa check-in
            $checkinModel = new TourCheckin();
            $checkin = $checkinModel->getByBookingId($bookingId);
            
            if ($checkin) {
                $_SESSION['error'] = 'Không thể xóa booking đã check-in. Vui lòng hủy booking thay vì xóa.';
            } else {
                $result = $bookingModel->delete($bookingId);
                if ($result) {
                    $_SESSION['success'] = 'Xóa booking thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể xóa booking.';
                }
            }
        }
        
        header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
        exit;
    }

    // Hiển thị lịch sử xóa booking
    public function lichSuXoaBooking() {
        require_once 'models/BookingDeletionHistory.php';
        $deletionHistoryModel = new BookingDeletionHistory();
        
        $lichSuXoa = $deletionHistoryModel->getAll();
        
        require 'views/admin/lich_su_xoa_booking.php';
    }

    // Hiển thị lịch sử xóa nhà cung cấp
    public function lichSuXoaNhaCungCap() {
        require_once 'models/SupplierDeletionHistory.php';
        $deletionHistoryModel = new SupplierDeletionHistory();
        
        $lichSuXoa = $deletionHistoryModel->getAll();
        
        require 'views/admin/lich_su_xoa_nha_cung_cap.php';
    }

    // Xem chi tiết một bản ghi lịch sử xóa nhà cung cấp
    public function chiTietLichSuXoaNhaCungCap() {
        require_once 'models/SupplierDeletionHistory.php';
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

    // ========== QUẢN LÝ YÊU CẦU TOUR TỪ KHÁCH HÀNG ==========
    
    /**
     * Quản lý yêu cầu tour từ khách hàng
     */
    public function quanLyYeuCauTour() {
        require_once 'models/ThongBao.php';
        
        $thongBaoModel = new ThongBao();
        
        // Lọc yêu cầu
        $filters = [
            'trang_thai' => $_GET['trang_thai'] ?? '',
            'search' => trim($_GET['search'] ?? ''),
            'limit' => 100
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
        
        // Lấy danh sách tour để admin có thể gợi ý
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
        
        if ($result) {
            $_SESSION['success'] = 'Đã gửi phản hồi thành công!';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi gửi phản hồi.';
        }
        
        header('Location: index.php?act=admin/chiTietYeuCauTour&id=' . $yeuCauId);
        exit;
    }
    
    private function initAdminNotificationState(?PDO $conn = null) {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $state = getAdminNotificationState($userId, $conn);
        $_SESSION['admin_notifications'] = $state;
        return $state;
    }

    private function persistAdminNotificationState(array $updates, ?PDO $conn = null) {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $state = saveAdminNotificationState($userId, $updates, $conn);
        $_SESSION['admin_notifications'] = $state;
        return $state;
    }

    private function markAdminPaymentNotificationsSeen(?PDO $conn = null) {
        $pdo = $conn instanceof PDO ? $conn : connectDB();
        $maxPaymentId = (int)$pdo->query("SELECT COALESCE(MAX(payment_id), 0) FROM payments")->fetchColumn();
        return $this->persistAdminNotificationState([
            'payments_last_seen_id' => $maxPaymentId,
        ], $pdo);
    }

    private function markAdminReviewNotificationsSeen(?PDO $conn = null) {
        $pdo = $conn instanceof PDO ? $conn : connectDB();
        $maxReviewId = (int)$pdo->query("SELECT COALESCE(MAX(danh_gia_id), 0) FROM danh_gia")->fetchColumn();
        return $this->persistAdminNotificationState([
            'reviews_last_seen_id' => $maxReviewId,
        ], $pdo);
    }

    private function markAdminDashboardNotificationsSeen(?PDO $conn = null) {
        $pdo = $conn instanceof PDO ? $conn : connectDB();
        $baseline = getAdminNotificationBaseline($pdo);
        return $this->persistAdminNotificationState([
            'payments_last_seen_id' => $baseline['payments_last_seen_id'],
            'reviews_last_seen_id' => $baseline['reviews_last_seen_id'],
        ], $pdo);
    }

    private function getAdminNotificationPayload(PDO $conn) {
        require_once 'models/ThongBao.php';
        $state = $this->initAdminNotificationState($conn);

        $paymentsLastSeenId = (int)($state['payments_last_seen_id'] ?? 0);
        $reviewsLastSeenId = (int)($state['reviews_last_seen_id'] ?? 0);

        $paymentStmt = $conn->prepare("SELECT COUNT(*) FROM payments WHERE payment_id > ?");
        $paymentStmt->execute([$paymentsLastSeenId]);
        $paymentCount = (int)$paymentStmt->fetchColumn();

        $reviewStmt = $conn->prepare("SELECT COUNT(*) FROM danh_gia WHERE danh_gia_id > ?");
        $reviewStmt->execute([$reviewsLastSeenId]);
        $reviewCount = (int)$reviewStmt->fetchColumn();

        $thongBaoModel = new ThongBao();
        $requestCount = (int)$thongBaoModel->countYeuCauTourChuaXuLy();

        return [
            'success' => true,
            'payments' => $paymentCount,
            'reviews' => $reviewCount,
            'requests' => $requestCount,
            'dashboard' => $paymentCount + $reviewCount + $requestCount,
            'sound_enabled' => ((int)($state['sound_enabled'] ?? 1) === 1) ? 1 : 0,
        ];
    }

    public function notificationSettings() {
        requireRole('Admin');
        $state = $this->initAdminNotificationState();

        $pageTitle = 'Cài đặt thông báo';
        $currentPage = 'notificationSettings';
        $soundEnabled = ((int)($state['sound_enabled'] ?? 1) === 1);

        require 'views/admin/notification_settings.php';
    }

    public function saveNotificationSettings() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/notificationSettings');
            exit;
        }
        $this->requirePostCsrf('admin/notificationSettings');

        $soundEnabled = isset($_POST['sound_enabled']) ? 1 : 0;
        $this->persistAdminNotificationState(['sound_enabled' => $soundEnabled]);
        $_SESSION['success'] = 'Đã cập nhật cài đặt thông báo.';

        header('Location: index.php?act=admin/notificationSettings');
        exit;
    }

    public function markNotificationsReadAll() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/notificationSettings');
            exit;
        }
        $this->requirePostCsrf('admin/notificationSettings');

        try {
            $conn = connectDB();
            $this->markAdminDashboardNotificationsSeen($conn);
        } catch (Throwable $e) {
            $this->initAdminNotificationState();
        }

        if ((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            header('Content-Type: application/json; charset=utf-8');
            try {
                $conn = connectDB();
                echo json_encode($this->getAdminNotificationPayload($conn), JSON_UNESCAPED_UNICODE);
                exit;
            } catch (Throwable $e) {
                echo json_encode([
                    'success' => true,
                    'payments' => 0,
                    'reviews' => 0,
                    'requests' => 0,
                    'dashboard' => 0,
                    'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        $_SESSION['success'] = 'Đã đánh dấu tất cả thông báo là đã xem.';
        header('Location: index.php?act=admin/notificationSettings');
        exit;
    }

    public function notificationCounts() {
        requireRole('Admin');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $conn = connectDB();
            echo json_encode($this->getAdminNotificationPayload($conn), JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'payments' => 0,
                'reviews' => 0,
                'requests' => 0,
                'dashboard' => 0,
                'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function notificationStream() {
        requireRole('Admin');
        @set_time_limit(0);
        @ignore_user_abort(true);

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $this->initAdminNotificationState();

        // Giải phóng session file lock ngay sau khi đọc xong session data.
        // Nếu không, vòng lặp SSE kéo dài 5 phút sẽ giữ lock và chặn mọi
        // request khác của cùng user (tab, AJAX) phải chờ toàn bộ thời gian đó.
        session_write_close();

        try {
            $startedAt = time();
            $lastPayloadHash = '';
            $lastMetaHash = '';
            $cachedPayload = [
                'success' => true,
                'payments' => 0,
                'reviews' => 0,
                'requests' => 0,
                'dashboard' => 0,
                'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
            ];

            while (!connection_aborted()) {
                if ((time() - $startedAt) > 300) {
                    echo "event: close\n";
                    echo "data: {}\n\n";
                    @ob_flush();
                    @flush();
                    break;
                }

                // Lấy kết nối ở đầu mỗi chu kỳ, giải phóng trước sleep()
                // để không giữ MySQL connection trong khoảng thời gian idle.
                $conn = connectDB();

                $metaStmt = $conn->query("SELECT
                    (SELECT COALESCE(MAX(payment_id), 0) FROM payments) AS payment_max,
                    (SELECT COALESCE(MAX(danh_gia_id), 0) FROM danh_gia) AS review_max,
                    (SELECT COALESCE(MAX(id), 0) FROM thong_bao WHERE tieu_de = 'Yêu cầu tour theo mong muốn' AND vai_tro_nhan = 'Admin') AS request_max,
                    (SELECT COUNT(*) FROM thong_bao WHERE tieu_de = 'Yêu cầu tour theo mong muốn' AND vai_tro_nhan = 'Admin' AND trang_thai = 'DaGui') AS request_pending
                ");
                $meta = $metaStmt->fetch(PDO::FETCH_ASSOC) ?: [];
                $metaHash = md5(
                    (string)($meta['payment_max'] ?? '') . '|' .
                    (string)($meta['review_max'] ?? '') . '|' .
                    (string)($meta['request_max'] ?? '') . '|' .
                    (string)($meta['request_pending'] ?? '') . '|' .
                    (string)($_SESSION['admin_notifications']['sound_enabled'] ?? 1)
                );

                if ($metaHash !== $lastMetaHash) {
                    $cachedPayload = $this->getAdminNotificationPayload($conn);
                    $lastMetaHash = $metaHash;
                }

                // Giải phóng kết nối trước khi sleep để không chiếm MySQL connection.
                $conn = null;
                releasePDOConnection();

                $payloadHash = md5(json_encode($cachedPayload, JSON_UNESCAPED_UNICODE));
                if ($payloadHash !== $lastPayloadHash) {
                    echo "event: notification\n";
                    echo 'data: ' . json_encode($cachedPayload, JSON_UNESCAPED_UNICODE) . "\n\n";
                    $lastPayloadHash = $payloadHash;
                } else {
                    echo ": ping\n\n";
                }

                @ob_flush();
                @flush();
                sleep(2);
            }
            exit;
        } catch (Throwable $e) {
            echo "event: notification\n";
            echo 'data: ' . json_encode([
                'success' => false,
                'payments' => 0,
                'reviews' => 0,
                'requests' => 0,
                'dashboard' => 0,
                'sound_enabled' => ((int)($_SESSION['admin_notifications']['sound_enabled'] ?? 1) === 1) ? 1 : 0,
            ], JSON_UNESCAPED_UNICODE) . "\n\n";
            @ob_flush();
            @flush();
            exit;
        }
    }

    public function automationStatus() {
        requireRole('Admin');
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $data = [
            'eventsCount'      => 0,
            'highSeverityCount'=> 0,
            'decisionCount'    => 0,
            'tourHealthCount'  => 0,
            'priorityCount'    => 0,
            'latestRun'        => null,
            'automationEnabled'=> true,
            'automationUpdatedAt' => null,
            'schedulerInterval'=> 15,
            'timestamp'        => date('Y-m-d H:i:s'),
        ];

        try {
            $conn = connectDB();
            require_once __DIR__ . '/../services/AdminAutomationService.php';
            $service = new AdminAutomationService($conn);
            $controlState = $service->getAutomationControlState();
            $data['automationEnabled'] = !empty($controlState['enabled']);
            $data['automationUpdatedAt'] = $controlState['updated_at'] ?? null;

            $data['eventsCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events")
                ->fetchColumn();

            $data['highSeverityCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM automation_events WHERE severity = 'high'")
                ->fetchColumn();

            $data['decisionCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM admin_decision_assist WHERE status = 'open'")
                ->fetchColumn();

            $data['tourHealthCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM tour_health_score WHERE health_level IN ('Watch','Critical')")
                ->fetchColumn();

            $data['priorityCount'] = (int)$conn
                ->query("SELECT COUNT(*) FROM booking_priority WHERE priority_label = 'High'")
                ->fetchColumn();

            $row = $conn->query("SELECT job_name, is_success, created_at
                                  FROM automation_job_runs
                                  ORDER BY run_id DESC
                                  LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $data['latestRun'] = $row ?: null;
        } catch (Throwable $e) {
            // return defaults
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function automationDashboard() {
        requireRole('Admin');

        $conn = connectDB();
        require_once __DIR__ . '/../services/AdminAutomationService.php';
        $service = new AdminAutomationService($conn);

        $jobRuns = [];
        $events = [];
        $priorityBookings = [];
        $tourHealth = [];
        $decisionAssist = [];
        $automationControlState = $service->getAutomationControlState();

        try {
            $stmt = $conn->query("SELECT run_id, job_name, is_success, affected_count, message, duration_ms, created_at
                                  FROM automation_job_runs
                                  ORDER BY run_id DESC
                                  LIMIT 40");
            $jobRuns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $jobRuns = [];
        }

        try {
            $stmt = $conn->query("SELECT event_id, job_name, severity, title, message, created_at
                                  FROM automation_events
                                  ORDER BY event_id DESC
                                  LIMIT 40");
            $events = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $events = [];
        }

        try {
            $stmt = $conn->query("SELECT bp.booking_id, bp.priority_label, bp.score, bp.computed_at,
                                         b.ngay_khoi_hanh, b.tong_tien, b.trang_thai
                                  FROM booking_priority bp
                                  LEFT JOIN booking b ON b.booking_id = bp.booking_id
                                  WHERE bp.priority_label = 'High'
                                  ORDER BY bp.score DESC, bp.computed_at DESC
                                  LIMIT 30");
            $priorityBookings = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $priorityBookings = [];
        }

        try {
            $stmt = $conn->query("SELECT th.tour_id, th.score, th.health_level, th.computed_at, t.ten_tour
                                  FROM tour_health_score th
                                  LEFT JOIN tour t ON t.tour_id = th.tour_id
                                  WHERE th.health_level IN ('Watch', 'Critical')
                                  ORDER BY th.score ASC, th.computed_at DESC
                                  LIMIT 30");
            $tourHealth = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $tourHealth = [];
        }

        try {
            $stmt = $conn->query("SELECT assist_id, entity_type, entity_id, recommendation_text, status, updated_at
                                  FROM admin_decision_assist
                                  WHERE status = 'open'
                                  ORDER BY updated_at DESC
                                  LIMIT 40");
            $decisionAssist = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $decisionAssist = [];
        }

        $pageTitle = 'Trung tâm Tự động hóa Admin';
        $currentPage = 'automation';
        $availableJobs = [
            'all',
            'sla_tour_requests',
            'booking_priority',
            'reconcile_digest',
            'self_heal_pending_payments',
            'webhook_anomaly',
            'debt_reminder',
            'departure_readiness',
            'tour_health_score',
            'admin_inbox_digest',
            'decision_assist',
        ];

        require 'views/admin/automation_dashboard.php';
    }

    public function toggleAutomation() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }
        $this->requirePostCsrf('admin/automationDashboard');

        $enabled = requestString('enabled', '1', 'POST') === '1';

        require_once __DIR__ . '/../services/AdminAutomationService.php';
        $conn = connectDB();
        $service = new AdminAutomationService($conn);
        $service->setAutomationEnabled($enabled);

        $_SESSION['success'] = $enabled
            ? 'Đã bật lại toàn bộ tự động hóa.'
            : 'Đã tạm tắt toàn bộ tự động hóa. Job tay và job nền sẽ bị bỏ qua cho đến khi bật lại.';

        header('Location: index.php?act=admin/automationDashboard');
        exit;
    }

    public function runAutomationJob() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }
        $this->requirePostCsrf('admin/automationDashboard');

        $job = requestString('job', 'all', 'POST');
        $availableJobs = [
            'all',
            'sla_tour_requests',
            'booking_priority',
            'reconcile_digest',
            'self_heal_pending_payments',
            'webhook_anomaly',
            'debt_reminder',
            'departure_readiness',
            'tour_health_score',
            'admin_inbox_digest',
            'decision_assist',
        ];

        if (!in_array($job, $availableJobs, true)) {
            $_SESSION['error'] = 'Job tự động hóa không hợp lệ.';
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }

        require_once __DIR__ . '/../services/AdminAutomationService.php';
        $conn = connectDB();
        $service = new AdminAutomationService($conn);

        if (!$service->isAutomationEnabled()) {
            $_SESSION['error'] = 'Tự động hóa đang tạm tắt. Hãy bật lại trước khi chạy job.';
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }

        if ($job === 'all') {
            $results = $service->runAll();
            $failed = 0;
            $affected = 0;
            foreach ($results as $result) {
                if (empty($result['ok'])) {
                    $failed++;
                }
                $affected += (int)($result['affected'] ?? 0);
            }
            $_SESSION[$failed > 0 ? 'error' : 'success'] = $failed > 0
                ? 'Đã chạy all jobs, có ' . $failed . ' job lỗi.'
                : 'Đã chạy all jobs thành công. Tổng tác động: ' . $affected . '.';
        } else {
            $result = $service->runJob($job);
            if (!empty($result['ok'])) {
                $_SESSION['success'] = 'Đã chạy job ' . $job . '. affected=' . (int)($result['affected'] ?? 0) . '.';
            } else {
                $_SESSION['error'] = 'Chạy job ' . $job . ' thất bại: ' . (string)($result['message'] ?? 'unknown error');
            }
        }

        header('Location: index.php?act=admin/automationDashboard');
        exit;
    }

    public function updateDecisionAssistStatus() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }
        $this->requirePostCsrf('admin/automationDashboard');

        $assistId = requestId('assist_id', 0, 'POST') ?? 0;
        $status = requestString('status', 'open', 'POST');
        if (!in_array($status, ['open', 'done', 'ignored'], true) || $assistId <= 0) {
            $_SESSION['error'] = 'Thông tin cập nhật gợi ý không hợp lệ.';
            header('Location: index.php?act=admin/automationDashboard');
            exit;
        }

        $conn = connectDB();
        $stmt = $conn->prepare("UPDATE admin_decision_assist
                               SET status = ?, updated_at = NOW()
                               WHERE assist_id = ?");
        $stmt->execute([$status, $assistId]);

        $_SESSION['success'] = 'Đã cập nhật trạng thái gợi ý #' . $assistId . '.';
        header('Location: index.php?act=admin/automationDashboard');
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
        
        // Tạo tour mới
        $tourModel = new Tour();
        $tourData = [
            'ten_tour' => $_POST['ten_tour'] ?? ($thongTin['Địa điểm'] ?? 'Tour mới'),
            'loai_tour' => $_POST['loai_tour'] ?? 'TrongNuoc',
            'mo_ta' => $_POST['mo_ta'] ?? 'Tour được tạo từ yêu cầu của khách hàng',
            'gia_co_ban' => isset($_POST['gia_co_ban']) ? (float)$_POST['gia_co_ban'] : 0,
            'trang_thai' => 'HoatDong',
            'tao_boi' => $_SESSION['user_id'] ?? null
        ];
        
        $tourId = $tourModel->insert($tourData);
        
        if ($tourId) {
            // Gửi thông báo cho khách hàng
            $thongBao = new ThongBao();
            $phanHoi = "Chúng tôi đã tạo tour mới dựa trên yêu cầu của bạn. Vui lòng xem chi tiết: " . 
                       "index.php?act=khachHang/chiTietTour&id=" . $tourId;
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

    // AJAX: KPI snapshot cho admin/dashboard realtime refresh
    public function dashboardKpiSnapshot() {
        requireRole('Admin');
        header('Content-Type: application/json; charset=utf-8');

        try {
            $result = cacheRemember('admin_dashboard_kpi_snapshot', 30, function () {
                $conn = connectDB();

                $totalRevenue = (float)$conn
                    ->query("SELECT COALESCE(SUM(so_tien), 0) FROM giao_dich_tai_chinh WHERE loai = 'Thu' AND ngay_giao_dich >= DATE_SUB(NOW(), INTERVAL 12 MONTH)")
                    ->fetchColumn();

                $totalBookings = (int)$conn
                    ->query("SELECT COUNT(*) FROM booking WHERE trang_thai NOT IN ('Huy')")
                    ->fetchColumn();

                $pendingBookings = (int)$conn
                    ->query("SELECT COUNT(*) FROM booking WHERE trang_thai = 'ChoXacNhan'")
                    ->fetchColumn();

                return [
                    'success' => true,
                    'total_revenue' => $totalRevenue,
                    'total_bookings' => $totalBookings,
                    'pending_bookings' => $pendingBookings,
                ];
            });

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode(['success' => false], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // AJAX: Booking/seat stats cho chi_tiet_lich_khoi_hanh realtime refresh
    public function lichKhoiHanhStats() {
        requireRole('Admin');
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $conn = connectDB();

            $stmt = $conn->prepare(
                "SELECT
                    (SELECT COUNT(*) FROM booking WHERE lich_khoi_hanh_id = ? AND trang_thai NOT IN ('Huy')) AS so_booking,
                    (SELECT COALESCE(SUM(so_nguoi), 0) FROM booking WHERE lich_khoi_hanh_id = ? AND trang_thai NOT IN ('Huy')) AS tong_nguoi_dat"
            );
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'so_booking' => (int)($row['so_booking'] ?? 0),
                'tong_nguoi_dat' => (int)($row['tong_nguoi_dat'] ?? 0),
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode(['success' => false], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

