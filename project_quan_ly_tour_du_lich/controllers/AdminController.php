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

    public function dashboard() {
        $notifCtrl = new AdminNotificationController();
        try {
            $notifCtrl->markAdminDashboardNotificationsSeen();
        } catch (Throwable $e) {
            $notifCtrl->initAdminNotificationState();
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
            return (new AdminAutomationController())->buildAutomationSnapshot();
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
    
    public function danhGia() {
        $notifCtrl = new AdminNotificationController();
        try {
            $notifCtrl->markAdminReviewNotificationsSeen();
        } catch (Throwable $e) {
            $notifCtrl->initAdminNotificationState();
        }

        require 'views/admin/danh_gia.php';
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

