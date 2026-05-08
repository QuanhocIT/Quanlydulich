<?php 

require_once __DIR__ . '/commons/SessionSecurity.php';
SessionSecurity::initialize(__DIR__ . '/storage/sessions');
SessionSecurity::start();

// ── Force HTTPS in production ──────────────────────────────────────────────
// Must run before any output or session-dependent logic.
if (!defined('APP_ENV')) {
    // APP_ENV not yet loaded — load env first to check
    require_once __DIR__ . '/commons/env.php';
}
if (defined('APP_ENV') && APP_ENV === 'production') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    if (!$isHttps) {
        $safeHost = preg_replace('/[^a-zA-Z0-9._\-]/', '', (string)($_SERVER['HTTP_HOST'] ?? ''));
        $safeUri  = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: https://' . $safeHost . $safeUri, true, 301);
        exit();
    }
}

// Require toàn bộ các file khai báo môi trường, thực thi,...(không require view)

// Require file Common
require_once __DIR__ . '/commons/env.php';
require_once __DIR__ . '/commons/perf.php';
require_once __DIR__ . '/commons/function.php';

// Tự động khởi động WebSocket server nếu chưa chạy
ensureWebSocketServerRunning();

// Lazy load classes from controllers/models to avoid eager loading all files on every request.
spl_autoload_register(function ($className) {
    static $baseDirs = null;
    if ($baseDirs === null) {
        $baseDirs = [
            __DIR__ . '/controllers/',
            __DIR__ . '/models/',
        ];
    }

    $safeClass = preg_replace('/[^A-Za-z0-9_]/', '', (string)$className);
    if ($safeClass === '') {
        return;
    }

    foreach ($baseDirs as $dir) {
        $filePath = $dir . $safeClass . '.php';
        if (is_file($filePath)) {
            require_once $filePath;
            return;
        }
    }
});

if (APP_ENV === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    if (ini_get('error_log') === '') {
        ini_set('error_log', __DIR__ . '/storage/php_error.log');
    }
    error_reporting(E_ALL);
} else {
    // M7: Guard against forgetting to set APP_ENV=production on a live server.
    // If the current host is NOT localhost/127.0.0.1/::1, treat the request as
    // production-safe regardless of the APP_ENV value so errors are never
    // displayed to real users, and emit a single log warning.
    $requestHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    $hostWithoutPort = explode(':', $requestHost)[0];
    $isLocalHost = in_array($hostWithoutPort, ['localhost', '127.0.0.1', '::1', ''], true);

    if (!$isLocalHost) {
        // Public-facing server but APP_ENV is not 'production' — apply production-safe settings.
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        if (ini_get('error_log') === '') {
            ini_set('error_log', __DIR__ . '/storage/php_error.log');
        }
        error_reporting(E_ALL);
        error_log('[SECURITY] M7: APP_ENV is "' . APP_ENV . '" but request host is "' . $requestHost . '". Set APP_ENV=production in .env to suppress this warning.');
    } else {
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }
}

// ── CSP nonce (generated once per request) ───────────────────────────────
$cspNonce = base64_encode(random_bytes(16));
define('CSP_NONCE', $cspNonce);

// ── Security headers (sent before any output) ──────────────────────────────
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=()');
    header(
        "Content-Security-Policy: "
        . "default-src 'self'; "
        . "script-src 'self' 'nonce-{$cspNonce}' cdn.jsdelivr.net cdnjs.cloudflare.com code.jquery.com; "
        . "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com; "
        . "font-src 'self' cdn.jsdelivr.net cdnjs.cloudflare.com fonts.gstatic.com data:; "
        . "img-src 'self' data: blob: https:; "
        . "connect-src 'self' ws: wss:; "
        . "frame-ancestors 'self'; "
        . "object-src 'none'; "
        . "base-uri 'self';"
    );
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
    }
}

// Route
$conn = getPDOConnection();

RequestValidator::sanitizeSuperglobals();

$_GET = whitelistRequestParams($_GET, [
    'act',
    'id',
    'code',
    'error',
    'nhan_su_id',
    'tour_id',
    'tourId',
    'booking_id',
    'lich_khoi_hanh_id',
    'lich_id',
    'entry_id',
    'du_toan_id',
    'group_id',
    'ncc_id',
    'hdv_id',
    'phan_bo_id',
    'month',
    'year',
    'page',
    'all',
    'search',
    'q',
    'keyword',
    'method',
    'type',
    'action',
    'status',
    'role',
    'trang_thai_luong',
    'loai_tour',
    'loai_nhat_ky',
    'co_yeu_cau_tour',
    'loai',
    'muc_do_uu_tien',
    'loai_yeu_cau',
    'format',
    'from',
    'to',
    'start',
    'end',
    'date_from',
    'date_to',
    'tu_ngay',
    'den_ngay',
    'ngay_khoi_hanh',
    'so_nguoi',
    'from_date',
    'to_date',
    'payment_status',
    'reconcile_state',
    'trang_thai',
    'diem_min',
    'diem_max',
    'return_act',
    'return_tour_id',
    'token'
]);

$allowedControllers = [
    'invoice',
    'tour',
    'auth',
    'booking',
    'lichKhoiHanh',
    'admin',
    'hdv',
    'nhaCungCap',
    'khachHang',
    'payment'
];

$act = requestRouteAct('auth/login');
if (!isValidRouteFormat($act)) {
    die('Route khong hop le.');
}

$sessionState = enforceSessionSecurity();
if (!empty($sessionState['invalidated']) && !in_array($act, ['auth/login', 'auth/register', 'auth/verify2fa', 'auth/verifyEmail', 'auth/resendVerification', 'auth/forgotPassword', 'auth/resetPassword'], true)) {
    header('Location: index.php?act=auth/login');
    exit();
}

if (!empty($_SESSION['force_password_change'])
    && !in_array($act, ['auth/logout', 'auth/forcePasswordChange'], true)
    && !str_starts_with($act, 'payment/')) {
    header('Location: index.php?act=auth/forcePasswordChange');
    exit();
}

// Normalize common route variants to canonical action names.
$actAliases = [
    'admin/quanlybooking' => 'admin/quanLyBooking',
    'admin/bookingdahoanthanh' => 'admin/bookingDaHoanThanh',
    'admin/quanlytour' => 'admin/quanLyTour',
    'admin/lichsuxoabooking' => 'admin/lichSuXoaBooking',
    'booking/dattourchokhach' => 'booking/datTourChoKhach',
];
$actLookup = strtolower($act);
if (isset($actAliases[$actLookup]) && $actAliases[$actLookup] !== $act) {
    $normalizedAct = $actAliases[$actLookup];
    $queryParams = $_GET;
    $queryParams['act'] = $normalizedAct;
    header('Location: index.php?' . http_build_query($queryParams));
    exit();
}

[$controller] = explode('/', $act, 2);
if (!in_array($controller, $allowedControllers, true)) {
    die('Controller khong duoc phep truy cap.');
}

authorizeRouteAccess($act);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$routeValidation = validateRequestByRoute($act, $method);
if (!$routeValidation['ok']) {
    setValidationErrors($routeValidation['errors'], 'Du lieu dau vao khong hop le.');
    $_SESSION['error'] = 'Du lieu dau vao khong hop le.';

    $redirectParams = $_GET;
    $redirectParams['act'] = $act;
    header('Location: index.php?' . http_build_query($redirectParams));
    exit();
}

if ($method === 'POST') {
    $csrfExemptActs = [
        'payment/bankWebhook',
        // These actions already perform scoped CSRF verification in their controllers.
        'auth/login',
        'auth/register',
        'auth/forgotPassword',
        'auth/resetPassword',
        'auth/resendVerification',
        'auth/verify2fa',
        'auth/setup2fa',
        'booking/create',
        'booking/update',
        'booking/delete',
        'booking/hideCompleted',
        'booking/datTourChoKhach',
        'admin/confirm_payment_received',
        'admin/confirm_gateway_payment',
        'admin/query_vnpay_status',
        'admin/paymentReconcile',
    ];

    if (!in_array($act, $csrfExemptActs, true)) {
        $csrfGlobalToken = $_POST['_csrf_global'] ?? '';
        if (!verifyCsrfToken($csrfGlobalToken, 'global_form')) {
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            $backAct = requestString('act', 'tour/index', 'GET');
            header('Location: index.php?act=' . urlencode($backAct));
            exit();
        }
    }
}

// Để đảm bảo tính chất chỉ gọi 1 hàm Controller để xử lý request thì mình sử dụng match
match ($act) {
    'invoice/sendMail' => SendInvoiceMailController::send($conn, $_GET['id'] ?? null),
    'invoice/exportPDF' => InvoicePDFController::export($conn, $_GET['id'] ?? null),
    // Trang chủ - Tour
    'tour/index' => (new TourController())->index(),
    'tour/show' => (new TourController())->show(),
    'tour/create' => (new TourController())->create(),
    'tour/update' => (new TourController())->update(),
    'tour/delete' => (new TourController())->delete(),
    'tour/clone' => (new TourController())->clone(),
    'tour/generateQr' => (new TourController())->generateQr(),
    // Lịch khởi hành trong tour
    'tour/taoLichKhoiHanh' => (new TourController())->taoLichKhoiHanh(),
    'tour/chiTietLichKhoiHanh' => (new TourController())->chiTietLichKhoiHanh(),
    'tour/phanBoNhanSuLichKhoiHanh' => (new TourController())->phanBoNhanSuLichKhoiHanh(),
    'tour/updateTrangThaiNhanSuLichKhoiHanh' => (new TourController())->updateTrangThaiNhanSuLichKhoiHanh(),
    'tour/phanBoDichVuLichKhoiHanh' => (new TourController())->phanBoDichVuLichKhoiHanh(),
    'tour/updateTrangThaiDichVuLichKhoiHanh' => (new TourController())->updateTrangThaiDichVuLichKhoiHanh(),
    'tour/deleteNhanSuLichKhoiHanh' => (new TourController())->deleteNhanSuLichKhoiHanh(),
    'tour/deleteDichVuLichKhoiHanh' => (new TourController())->deleteDichVuLichKhoiHanh(),
   
    // Auth
    'auth/login' => (new AuthController())->login(),
    'auth/register' => (new AuthController())->register(),
    'auth/verify2fa' => (new AuthController())->verify2fa(),
    'auth/setup2fa' => (new AuthController())->setup2fa(),
    'auth/verifyEmail' => (new AuthController())->verifyEmail(),
    'auth/resendVerification' => (new AuthController())->resendVerification(),
    'auth/forgotPassword' => (new AuthController())->forgotPassword(),
    'auth/resetPassword' => (new AuthController())->resetPassword(),
    'auth/logout' => (new AuthController())->logout(),
    'auth/forcePasswordChange' => (new AuthController())->forcePasswordChange(),

    'auth/profile' => (new AuthController())->profile(),

    
    // Booking
    'booking/index' => (new BookingController())->index(),
    'booking/create' => (new BookingController())->create(),
    'booking/show' => (new BookingController())->show(),
    'booking/chiTiet' => (new BookingController())->chiTiet(),
    'booking/update' => (new BookingController())->update(),
    'booking/updateTrangThai' => (new BookingController())->updateTrangThai(),
    'booking/updateTienCoc' => (new BookingController())->updateTienCoc(),
    'booking/delete' => (new BookingController())->delete(),
    'booking/hideCompleted' => (new BookingController())->hideCompleted(),
    'booking/datTourChoKhach' => (new BookingController())->datTourChoKhach(),
    'booking/kiemTraChoTrong' => (new BookingController())->kiemTraChoTrong(),
    'booking/xuatTaiLieu' => (new BookingController())->xuatTaiLieu(),
    'booking/exportPDF' => (new BookingController())->exportPDF(),
    'booking/sendEmail' => (new BookingController())->sendEmail(),

    
    // Lịch khởi hành
    'lichKhoiHanh/index' => (new LichKhoiHanhController())->index(),
    'lichKhoiHanh/create' => (new LichKhoiHanhController())->create(),
    'lichKhoiHanh/chiTiet' => (new LichKhoiHanhController())->chiTiet(),
    'lichKhoiHanh/chiTietTheoBooking' => (new LichKhoiHanhController())->chiTietTheoBooking(),
    'lichKhoiHanh/edit' => (new LichKhoiHanhController())->edit(),
    'lichKhoiHanh/update' => (new LichKhoiHanhController())->update(),
    'lichKhoiHanh/phanBoNhanSu' => (new LichKhoiHanhController())->phanBoNhanSu(),
    'lichKhoiHanh/capNhatHoaHongHDV' => (new LichKhoiHanhController())->capNhatHoaHongHDV(),
    'lichKhoiHanh/tuDongPhanBoNhanSu' => (new LichKhoiHanhController())->tuDongPhanBoNhanSu(),
    'lichKhoiHanh/checkConflict' => (new LichKhoiHanhController())->checkConflict(),
    'lichKhoiHanh/updateTrangThaiNhanSu' => (new LichKhoiHanhController())->updateTrangThaiNhanSu(),
    'lichKhoiHanh/phanBoDichVu' => (new LichKhoiHanhController())->phanBoDichVu(),
    'lichKhoiHanh/updateTrangThaiDichVu' => (new LichKhoiHanhController())->updateTrangThaiDichVu(),
    'lichKhoiHanh/deleteNhanSu' => (new LichKhoiHanhController())->deleteNhanSu(),
    'lichKhoiHanh/deleteDichVu' => (new LichKhoiHanhController())->deleteDichVu(),
    'lichKhoiHanh/themKhachChiTiet' => (new LichKhoiHanhController())->themKhachChiTiet(),
    'lichKhoiHanh/suaKhachChiTiet' => (new LichKhoiHanhController())->suaKhachChiTiet(),
    'lichKhoiHanh/xoaKhachChiTiet' => (new LichKhoiHanhController())->xoaKhachChiTiet(),
    'lichKhoiHanh/themYeuCauDacBiet' => (new LichKhoiHanhController())->themYeuCauDacBiet(),
    'lichKhoiHanh/suaYeuCauDacBiet' => (new LichKhoiHanhController())->suaYeuCauDacBiet(),
    'lichKhoiHanh/xoaYeuCauDacBiet' => (new LichKhoiHanhController())->xoaYeuCauDacBiet(),
    'lichKhoiHanh/themNhatKy' => (new LichKhoiHanhController())->themNhatKy(),
    'lichKhoiHanh/suaNhatKy' => (new LichKhoiHanhController())->suaNhatKy(),
    'lichKhoiHanh/xoaNhatKy' => (new LichKhoiHanhController())->xoaNhatKy(),
    // Admin
    'admin/dashboard' => (new AdminController())->dashboard(),
    'admin/quanLyTour' => (new AdminController())->quanLyTour(),
    'admin/quanLyBooking' => (new AdminController())->quanLyBooking(),
    'admin/bookingDaHoanThanh' => (new AdminController())->bookingDaHoanThanh(),
    'admin/lichSuXoaBooking' => (new AdminController())->lichSuXoaBooking(),
    // Nhà cung cấp (lịch sử xóa) → AdminNhaCungCapController
    'admin/lichSuXoaNhaCungCap' => (new AdminNhaCungCapController())->lichSuXoaNhaCungCap(),
    'admin/chiTietLichSuXoaNhaCungCap' => (new AdminNhaCungCapController())->chiTietLichSuXoaNhaCungCap(),
    'admin/chiTietTour' => (new AdminController())->chiTietTour(),
    'admin/chiTietBooking' => (new BookingController())->chiTiet(),
    'admin/chi_tiet_lich_khoi_hanh' => (new LichKhoiHanhController())->chiTiet(),
    'admin/taoTour' => (new TourController())->create(),
    'admin/yeuCauDacBiet' => (new AdminController())->yeuCauDacBiet(),
    'admin/capNhatYeuCauDacBiet' => (new AdminController())->capNhatYeuCauDacBiet(),
    'admin/themYeuCauDacBiet' => (new AdminController())->taoYeuCauDacBiet(),
    // Yêu cầu tour → AdminYeuCauTourController
    'admin/quanLyYeuCauTour' => (new AdminYeuCauTourController())->quanLyYeuCauTour(),
    'admin/yeuCauTourSnapshot' => (new AdminYeuCauTourController())->yeuCauTourSnapshot(),
    'admin/chiTietYeuCauTour' => (new AdminYeuCauTourController())->chiTietYeuCauTour(),
    'admin/phanHoiYeuCauTour' => (new AdminYeuCauTourController())->phanHoiYeuCauTour(),
    'admin/taoTourTuYeuCau' => (new AdminYeuCauTourController())->taoTourTuYeuCau(),
    // Nhật ký tour → AdminNhatKyTourController
    'admin/quanLyNhatKyTour' => (new AdminNhatKyTourController())->quanLyNhatKyTour(),
    'admin/chiTietNhatKyTour' => (new AdminNhatKyTourController())->chiTietNhatKyTour(),
    'admin/formNhatKyTour' => (new AdminNhatKyTourController())->formNhatKyTour(),
    'admin/saveNhatKyTour' => (new AdminNhatKyTourController())->saveNhatKyTour(),
    'admin/deleteNhatKyTour' => (new AdminNhatKyTourController())->deleteNhatKyTour(),
    // Nhà cung cấp → AdminNhaCungCapController
    'admin/addNhacungcap' => (new AdminNhaCungCapController())->addNhacungcap(),
    // Người dùng → AdminNguoiDungController
    'admin/quanLyNguoiDung' => (new AdminNguoiDungController())->quanLyNguoiDung(),
    'admin/capNhatTrangThaiNguoiDung' => (new AdminNguoiDungController())->capNhatTrangThaiNguoiDung(),
    // Lương/thưởng nhân sự → AdminLuongController
    'admin/quanLyLuongThuong' => (new AdminLuongController())->quanLyLuongThuong(),
    'admin/chiTietLuong' => (new AdminLuongController())->chiTietLuong(),
    'admin/ajaxChiTietLuong' => (new AdminLuongController())->ajaxChiTietLuong(),
    'admin/taoLuongThuong' => (new AdminLuongController())->taoLuongThuong(),
    'admin/capNhatLuongThuong' => (new AdminLuongController())->capNhatLuongThuong(),
    'admin/duyetLuongNhanSu' => (new AdminLuongController())->duyetLuongNhanSu(),
    'admin/thanhToanLuongNhanSu' => (new AdminLuongController())->thanhToanLuongNhanSu(),
    'admin/tinhLaiLuongNhanSu' => (new AdminLuongController())->tinhLaiLuongNhanSu(),
    'admin/capNhatLuongCoBan' => (new AdminLuongController())->capNhatLuongCoBan(),
    // Thông báo → AdminNotificationController
    'admin/notificationCounts' => (new AdminNotificationController())->notificationCounts(),
    'admin/notificationStream' => (new AdminNotificationController())->notificationStream(),
    'admin/notificationSettings' => (new AdminNotificationController())->notificationSettings(),
    'admin/saveNotificationSettings' => (new AdminNotificationController())->saveNotificationSettings(),
    'admin/markNotificationsReadAll' => (new AdminNotificationController())->markNotificationsReadAll(),
    'admin/dashboardKpiSnapshot' => (new AdminController())->dashboardKpiSnapshot(),
    'admin/lichKhoiHanhStats' => (new AdminController())->lichKhoiHanhStats(),
    // Automation → AdminAutomationController
    'admin/automationDashboard' => (new AdminAutomationController())->automationDashboard(),
    'admin/automationStatus' => (new AdminAutomationController())->automationStatus(),
    'admin/toggleAutomation' => (new AdminAutomationController())->toggleAutomation(),
    'admin/runAutomationJob' => (new AdminAutomationController())->runAutomationJob(),
    'admin/updateDecisionAssistStatus' => (new AdminAutomationController())->updateDecisionAssistStatus(),
    // Báo cáo tài chính
    'admin/baoCaoTaiChinh' => (new BaoCaoTaiChinhController())->dashboard(),
    'admin/lichSuGiaoDich' => (new BaoCaoTaiChinhController())->lichSuGiaoDich(),
    'admin/giaoDichTheoTour' => (new BaoCaoTaiChinhController())->giaoDichTheoTour(),
    'admin/chiTietGiaoDich' => (new BaoCaoTaiChinhController())->chiTietGiaoDich(),
    'admin/congNo' => (new BaoCaoTaiChinhController())->congNo(),
    'admin/congNoKhachHang' => (new BaoCaoTaiChinhController())->congNoKhachHang(),
    'admin/congNoNhaCungCap' => (new BaoCaoTaiChinhController())->congNoNhaCungCap(),
    'admin/laiLoTour' => (new BaoCaoTaiChinhController())->laiLoTour(),
    'admin/duToanTour' => (new BaoCaoTaiChinhController())->duToanTour(),
    'admin/formDuToan' => (new BaoCaoTaiChinhController())->formDuToan(),
    'admin/chiPhiThucTe' => (new BaoCaoTaiChinhController())->chiPhiThucTe(),
    'admin/formChiPhi' => (new BaoCaoTaiChinhController())->formChiPhi(),
    'admin/duyetChiPhi' => (new BaoCaoTaiChinhController())->duyetChiPhi(),
    'admin/tuChoiChiPhi' => (new BaoCaoTaiChinhController())->tuChoiChiPhi(),
    'admin/soSanhDuToan' => (new BaoCaoTaiChinhController())->soSanhDuToan(),
    'admin/nhacHanCongNo' => (new BaoCaoTaiChinhController())->nhacHanCongNo(),
    'admin/thuChiTour' => (new BaoCaoTaiChinhController())->thuChiTour(),
    'admin/baoCaoTaiChinh/xuatBaoCao' => (new BaoCaoTaiChinhController())->xuatBaoCao(),
    // HDV
    'hdv/dashboard' => (new HDVController())->dashboard(),
    'hdv/tours' => (new HDVController())->tours(),
    'hdv/xacNhanPhanBo' => (new HDVController())->xacNhanPhanBo(),
    'hdv/tour_detail' => (new HDVController())->tourDetail(),
    'hdv/khach' => (new HDVController())->danhSachKhach(),
    'hdv/nhat_ky' => (new HDVController())->nhatKy(),
    'hdv/nhatKy' => (new HDVController())->nhatKy(),
    'hdv/save_nhat_ky' => (new HDVController())->saveNhatKy(),
    'hdv/delete_nhat_ky' => (new HDVController())->deleteNhatKy(),
    'hdv/checkin' => (new HDVController())->checkin(),
    'hdv/save_diem_checkin' => (new HDVController())->saveDiemCheckin(),
    'hdv/delete_diem_checkin' => (new HDVController())->deleteDiemCheckin(),
    'hdv/save_checkin_khach' => (new HDVController())->saveCheckinKhach(),
    'hdv/yeu_cau_dac_biet' => (new HDVController())->yeuCauDacBiet(),
    'hdv/yeuCauDacBiet' => (new HDVController())->yeuCauDacBiet(),
    'hdv/save_yeu_cau' => (new HDVController())->saveYeuCauDacBiet(),
    'hdv/delete_yeu_cau' => (new HDVController())->deleteYeuCauDacBiet(),
    'hdv/phan_hoi' => (new HDVController())->phanHoi(),
    'hdv/save_phan_hoi' => (new HDVController())->savePhanHoi(),
    'hdv/delete_phan_hoi' => (new HDVController())->deletePhanHoi(),
    'hdv/profile' => (new HDVController())->profile(),
    'hdv/update_profile' => (new HDVController())->updateProfile(),
    'hdv/danh_gia' => (new HDVController())->danhGia(),
    'hdv/danhGia' => (new HDVController())->danhGia(),
    'hdv/notifications' => (new HDVController())->notifications(),
    'hdv/notificationCounts' => (new HDVController())->notificationCounts(),
    'hdv/lichLamViec' => (new HDVController())->lichLamViec(),
    'hdv/nhatKyTour' => (new HDVController())->nhatKyTour(),
    'hdv/danhSachKhach' => (new HDVController())->danhSachKhach(),
    'hdv/checkInKhach' => (new HDVController())->checkInKhach(),
    'hdv/updateCheckInKhach' => (new HDVController())->updateCheckInKhach(),
    'hdv/quanLyYeuCauDacBiet' => (new HDVController())->quanLyYeuCauDacBiet(),
    'hdv/updateYeuCauDacBiet' => (new HDVController())->updateYeuCauDacBiet(),
    'hdv/phanHoi' => (new HDVController())->phanHoi(),
    // Admin - quản lý HDV → AdminNhanSuController
    'admin/quanLyHDV' => (new AdminNhanSuController())->quanLyHDV(),
    'admin/quanLyHDV_create' => (new AdminNhanSuController())->quanLyHDVCreate(),
    'admin/quanLyHDV_update' => (new AdminNhanSuController())->quanLyHDVUpdate(),
    'admin/quanLyHDV_delete' => (new AdminNhanSuController())->quanLyHDVDelete(),
    // Admin - HDV schedule & profile → AdminNhanSuController
    'admin/hdv_schedule' => (new AdminNhanSuController())->hdvSchedule(),
    'admin/hdv_profile' => (new AdminNhanSuController())->hdvProfile(),
    // API endpoints for AJAX → AdminNhanSuController
    'admin/hdv_api_get_schedule' => (new AdminNhanSuController())->hdvApiGetSchedule(),
    'admin/hdv_api_check' => (new AdminNhanSuController())->hdvApiCheck(),
    'admin/hdv_api_assign' => (new AdminNhanSuController())->hdvApiAssign(),
    'admin/hdv_api_suggest' => (new AdminNhanSuController())->hdvApiSuggest(),
    'admin/nhanSu_get_users' => (new AdminNhanSuController())->nhanSu_get_users(),
    // Admin - Quản lý khách theo tour
    'admin/danhSachKhachTheoTour' => (new AdminController())->danhSachKhachTheoTour(),
    'admin/themKhachLichKhoiHanh' => (new AdminController())->themKhachLichKhoiHanh(),
    'admin/suaKhachLichKhoiHanh' => (new AdminController())->suaKhachLichKhoiHanh(),
    'admin/xoaKhachLichKhoiHanh' => (new AdminController())->xoaKhachLichKhoiHanh(),
    'admin/checkInKhach' => (new AdminController())->checkInKhach(),
    'admin/updateCheckIn' => (new AdminController())->updateCheckIn(),
    'admin/phanPhongKhachSan' => (new AdminController())->phanPhongKhachSan(),
    'admin/nhaCungCap' => (new AdminNhaCungCapController())->nhaCungCap(),
    'admin/updateNhaCungCap' => (new AdminNhaCungCapController())->updateNhaCungCap(),
    'admin/deleteNhaCungCap' => (new AdminNhaCungCapController())->deleteNhaCungCap(),
    'admin/chiTietDichVu' => (new AdminNhaCungCapController())->chiTietDichVu(),
    'admin/supplierServiceAction' => (new AdminNhaCungCapController())->supplierServiceAction(),
    
    // Nhà cung cấp
    'nhaCungCap/dashboard' => (new NhaCungCapController())->dashboard(),
    'nhaCungCap/baoGia' => (new NhaCungCapController())->baoGia(),
    'nhaCungCap/dichVu' => (new NhaCungCapController())->dichVu(),
    'nhaCungCap/congNo' => (new NhaCungCapController())->congNo(),
    'nhaCungCap/hopDong' => (new NhaCungCapController())->hopDong(),
    'nhaCungCap/xacNhanBooking' => (new NhaCungCapController())->xacNhanBooking(),
    'nhaCungCap/capNhatGia' => (new NhaCungCapController())->capNhatGia(),
    'nhaCungCap/storeDichVu' => (new NhaCungCapController())->storeDichVu(),
    'nhaCungCap/updateDichVu' => (new NhaCungCapController())->updateDichVu(),
    'nhaCungCap/deleteDichVu' => (new NhaCungCapController())->deleteDichVu(),
    'nhaCungCap/storeBaoGiaThuCong' => (new NhaCungCapController())->storeBaoGiaThuCong(),
    'nhaCungCap/chiTietDichVu' => (new NhaCungCapController())->chiTietDichVu(),
    
    // Khách hàng
    'khachHang/dashboard' => (new KhachHangController())->dashboard(),
    'khachHang/danhSachTour' => (new KhachHangController())->danhSachTour(),
    'khachHang/chiTietTour' => (new KhachHangController())->chiTietTour(),
    'khachHang/datTour' => (new KhachHangController())->datTour(),
    'khachHang/danhGia' => (new KhachHangController())->danhGia(),
    'khachHang/guiDanhGia' => (new KhachHangController())->guiDanhGia(),
    'khachHang/traCuu' => (new KhachHangController())->traCuu(),
    'khachHang/hoaDon' => (new KhachHangController())->hoaDon(),
    'khachHang/lichSuThanhToan' => (new KhachHangController())->lichSuThanhToan(),
    'khachHang/lichTrinhTour' => (new KhachHangController())->lichTrinhTour(),
    'khachHang/thongBao' => (new KhachHangController())->thongBao(),
    'khachHang/capNhatThongTin' => (new KhachHangController())->capNhatThongTin(),
    'khachHang/guiYeuCauHoTro' => (new KhachHangController())->guiYeuCauHoTro(),
    'khachHang/guiYeuCauTour' => (new KhachHangController())->guiYeuCauTour(),
    'khachHang/yeuCauTour' => (new KhachHangController())->guiYeuCauTour(),
    'khachHang/toggleYeuThich' => (new KhachHangController())->toggleYeuThich(),
    'khachHang/notificationCounts' => (new KhachHangController())->notificationCounts(),
    'khachHang/markAllNotificationsRead' => (new KhachHangController())->markAllNotificationsRead(),
    'khachHang/notificationFeed' => (new KhachHangController())->notificationFeed(),
    'khachHang/notificationStream' => (new KhachHangController())->notificationStream(),
    'khachHang/thanhToan' => (new KhachHangController())->thanhToan(),
    'khachHang/thanhToanTour' => (new KhachHangController())->thanhToanTour(),
    'khachHang/nhapThongTinThamGia' => (new KhachHangController())->nhapThongTinThamGia(),
    'khachHang/paymentStatus' => (new KhachHangController())->paymentStatus(),

    // Nhân sự → AdminNhanSuController
    'admin/nhanSu' => (new AdminNhanSuController())->nhanSu(),
    'admin/nhanSuController' => (new AdminNhanSuController())->nhanSu(),
    'admin/nhanSu_create' => (new AdminNhanSuController())->nhanSuCreate(),
    'admin/nhanSu_update' => (new AdminNhanSuController())->nhanSuUpdate(),
    'admin/nhanSu_delete' => (new AdminNhanSuController())->nhanSuDelete(),
    'admin/nhanSu_chi_tiet' => (new AdminNhanSuController())->nhanSuChiTiet(),

    // Quản lý HDV nâng cao → AdminNhanSuController
    'admin/hdv_advanced' => (new AdminNhanSuController())->hdvAdvanced(),
    'admin/hdv_lich_table' => (new AdminNhanSuController())->hdvLichTable(),
    'admin/hdv_add_schedule' => (new AdminNhanSuController())->hdvAddSchedule(),
    'admin/hdv_get_schedule' => (new AdminNhanSuController())->hdvGetSchedule(),
    'admin/hdv_send_notification' => (new AdminNhanSuController())->hdvSendNotification(),
    'admin/hdv_detail' => (new AdminNhanSuController())->hdvDetail(),

    // Công nợ HDV
    'hdv/thanhToanHDV' => (new CongNoHDVController())->thanhToanHDV(),
    'hdv/guiHoaDon' => (new CongNoHDVController())->guiHoaDon(),
    'admin/quanLyCongNoHDV' => (new CongNoHDVController())->quanLyCongNoHDV(),
    'admin/duyetHoaDon' => (new CongNoHDVController())->duyetHoaDon(),
    'admin/tuChoiHoaDon' => (new CongNoHDVController())->tuChoiHoaDon(),
    
    // Quản lý đánh giá & phản hồi
    'admin/danhGia' => (new DanhGiaController())->index(),
    'admin/danhGia/chiTiet' => (new DanhGiaController())->chiTiet(),
    'admin/danhGia/traLoi' => (new DanhGiaController())->traLoi(),
    'admin/danhGia/xoa' => (new DanhGiaController())->xoa(),
    'admin/danhGia/baoCao' => (new DanhGiaController())->baoCao(),
    'admin/danhGia/export' => (new DanhGiaController())->export(),

        // Quản lý hóa đơn & thanh toán
        'admin/invoices' => InvoiceController::index($conn),
        'admin/show_invoice' => InvoiceController::show($conn, $_GET['id'] ?? null),
        'admin/payments' => PaymentController::index($conn),
        'admin/paymentReconcile' => PaymentController::reconcile($conn),
        'admin/paymentComplaints' => PaymentController::complaints($conn),
        'admin/show_payment' => PaymentController::show($conn, $_GET['id'] ?? null),
        'admin/confirm_payment_received' => PaymentController::confirmReceived($conn, $_GET['id'] ?? null),
        'admin/confirm_gateway_payment' => PaymentController::confirmGatewayPayment($conn, $_GET['id'] ?? null),
        'admin/query_vnpay_status' => PaymentGatewayController::queryVnpayStatus($conn, $_GET['id'] ?? null),

            // Thanh toán online booking
            'admin/thanhToanBooking' => PaymentGatewayController::pay($conn, $_GET['id'] ?? null),
            'payment/redirect' => PaymentGatewayController::redirect($conn, $_GET['booking_id'] ?? null, $_GET['method'] ?? 'VNPay'),
            'payment/callback' => PaymentGatewayController::callback($conn, $_GET['booking_id'] ?? null, $_GET['method'] ?? '', $_GET['status'] ?? ''),
            'payment/vnpayIpn' => PaymentGatewayController::vnpayIpn($conn),
            'payment/bankWebhook' => BankWebhookController::receive($conn),
    
    // Default
    default => die("Route không tồn tại: $act")
};

