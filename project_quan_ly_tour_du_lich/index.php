<?php 

require_once __DIR__ . '/commons/SessionSecurity.php';
SessionSecurity::initialize(__DIR__ . '/storage/sessions');
SessionSecurity::start();

// Require toàn bộ các file khai báo môi trường, thực thi,...(không require view)

// Require file Common
require_once __DIR__ . '/commons/env.php';
require_once __DIR__ . '/commons/perf.php';
require_once __DIR__ . '/commons/function.php';

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
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
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
    'webhook_secret'
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
if (!empty($sessionState['invalidated']) && !in_array($act, ['auth/login', 'auth/register'], true)) {
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
    'lichKhoiHanh/themNhatKy' => (new LichKhoiHanhController())->themNhatKy(),
    // Admin
    'admin/dashboard' => (new AdminController())->dashboard(),
    'admin/quanLyTour' => (new AdminController())->quanLyTour(),
    'admin/quanLyBooking' => (new AdminController())->quanLyBooking(),
    'admin/bookingDaHoanThanh' => (new AdminController())->bookingDaHoanThanh(),
    'admin/lichSuXoaBooking' => (new AdminController())->lichSuXoaBooking(),
    'admin/lichSuXoaNhaCungCap' => (new AdminController())->lichSuXoaNhaCungCap(),
    'admin/chiTietLichSuXoaNhaCungCap' => (new AdminController())->chiTietLichSuXoaNhaCungCap(),
    'admin/chiTietTour' => (new AdminController())->chiTietTour(),
    'admin/yeuCauDacBiet' => (new AdminController())->yeuCauDacBiet(),
    'admin/capNhatYeuCauDacBiet' => (new AdminController())->capNhatYeuCauDacBiet(),
    'admin/themYeuCauDacBiet' => (new AdminController())->taoYeuCauDacBiet(),
    'admin/quanLyYeuCauTour' => (new AdminController())->quanLyYeuCauTour(),
    'admin/yeuCauTourSnapshot' => (new AdminController())->yeuCauTourSnapshot(),
    'admin/chiTietYeuCauTour' => (new AdminController())->chiTietYeuCauTour(),
    'admin/phanHoiYeuCauTour' => (new AdminController())->phanHoiYeuCauTour(),
    'admin/taoTourTuYeuCau' => (new AdminController())->taoTourTuYeuCau(),
    'admin/quanLyNhatKyTour' => (new AdminController())->quanLyNhatKyTour(),
    'admin/chiTietNhatKyTour' => (new AdminController())->chiTietNhatKyTour(),
    'admin/formNhatKyTour' => (new AdminController())->formNhatKyTour(),
    'admin/saveNhatKyTour' => (new AdminController())->saveNhatKyTour(),
    'admin/deleteNhatKyTour' => (new AdminController())->deleteNhatKyTour(),
    'admin/addNhacungcap' => (new AdminController())->addNhacungcap(),
    'admin/quanLyNguoiDung' => (new AdminController())->quanLyNguoiDung(),
    'admin/capNhatTrangThaiNguoiDung' => (new AdminController())->capNhatTrangThaiNguoiDung(),
    'admin/quanLyLuongThuong' => (new AdminController())->quanLyLuongThuong(),
    'admin/chiTietLuong' => (new AdminController())->chiTietLuong(),
    'admin/ajaxChiTietLuong' => (new AdminController())->ajaxChiTietLuong(),
    'admin/taoLuongThuong' => (new AdminController())->taoLuongThuong(),
    'admin/capNhatLuongThuong' => (new AdminController())->capNhatLuongThuong(),
    'admin/duyetLuongNhanSu' => (new AdminController())->duyetLuongNhanSu(),
    'admin/thanhToanLuongNhanSu' => (new AdminController())->thanhToanLuongNhanSu(),
    'admin/tinhLaiLuongNhanSu' => (new AdminController())->tinhLaiLuongNhanSu(),
    'admin/capNhatLuongCoBan' => (new AdminController())->capNhatLuongCoBan(),
    'admin/notificationCounts' => (new AdminController())->notificationCounts(),
    'admin/notificationStream' => (new AdminController())->notificationStream(),
    'admin/notificationSettings' => (new AdminController())->notificationSettings(),
    'admin/saveNotificationSettings' => (new AdminController())->saveNotificationSettings(),
    'admin/markNotificationsReadAll' => (new AdminController())->markNotificationsReadAll(),
    'admin/automationDashboard' => (new AdminController())->automationDashboard(),
    'admin/automationStatus' => (new AdminController())->automationStatus(),
    'admin/runAutomationJob' => (new AdminController())->runAutomationJob(),
    'admin/updateDecisionAssistStatus' => (new AdminController())->updateDecisionAssistStatus(),
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
    'hdv/save_nhat_ky' => (new HDVController())->saveNhatKy(),
    'hdv/delete_nhat_ky' => (new HDVController())->deleteNhatKy(),
    'hdv/checkin' => (new HDVController())->checkin(),
    'hdv/save_diem_checkin' => (new HDVController())->saveDiemCheckin(),
    'hdv/delete_diem_checkin' => (new HDVController())->deleteDiemCheckin(),
    'hdv/save_checkin_khach' => (new HDVController())->saveCheckinKhach(),
    'hdv/yeu_cau_dac_biet' => (new HDVController())->yeuCauDacBiet(),
    'hdv/save_yeu_cau' => (new HDVController())->saveYeuCauDacBiet(),
    'hdv/delete_yeu_cau' => (new HDVController())->deleteYeuCauDacBiet(),
    'hdv/phan_hoi' => (new HDVController())->phanHoi(),
    'hdv/save_phan_hoi' => (new HDVController())->savePhanHoi(),
    'hdv/delete_phan_hoi' => (new HDVController())->deletePhanHoi(),
    'hdv/profile' => (new HDVController())->profile(),
    'hdv/update_profile' => (new HDVController())->updateProfile(),
    'hdv/danh_gia' => (new HDVController())->danhGia(),
    'hdv/notifications' => (new HDVController())->notifications(),
    'hdv/lichLamViec' => (new HDVController())->lichLamViec(),
    'hdv/nhatKyTour' => (new HDVController())->nhatKyTour(),
    'hdv/danhSachKhach' => (new HDVController())->danhSachKhach(),
    'hdv/checkInKhach' => (new HDVController())->checkInKhach(),
    'hdv/updateCheckInKhach' => (new HDVController())->updateCheckInKhach(),
    'hdv/quanLyYeuCauDacBiet' => (new HDVController())->quanLyYeuCauDacBiet(),
    'hdv/updateYeuCauDacBiet' => (new HDVController())->updateYeuCauDacBiet(),
    'hdv/phanHoi' => (new HDVController())->phanHoi(),
    // Admin - quản lý HDV
    'admin/quanLyHDV' => (new AdminController())->quanLyHDV(),
    'admin/quanLyHDV_create' => (new AdminController())->quanLyHDVCreate(),
    'admin/quanLyHDV_update' => (new AdminController())->quanLyHDVUpdate(),
    'admin/quanLyHDV_delete' => (new AdminController())->quanLyHDVDelete(),
    // Admin - HDV schedule & profile
    'admin/hdv_schedule' => (new AdminController())->hdvSchedule(),
    'admin/hdv_profile' => (new AdminController())->hdvProfile(),
    // API endpoints for AJAX
    'admin/hdv_api_get_schedule' => (new AdminController())->hdvApiGetSchedule(),
    'admin/hdv_api_check' => (new AdminController())->hdvApiCheck(),
    'admin/hdv_api_assign' => (new AdminController())->hdvApiAssign(),
    'admin/hdv_api_suggest' => (new AdminController())->hdvApiSuggest(),
    'admin/nhanSu_get_users' => (new AdminController())->nhanSu_get_users(),
    // Admin - Quản lý khách theo tour
    'admin/danhSachKhachTheoTour' => (new AdminController())->danhSachKhachTheoTour(),
    'admin/themKhachLichKhoiHanh' => (new AdminController())->themKhachLichKhoiHanh(),
    'admin/suaKhachLichKhoiHanh' => (new AdminController())->suaKhachLichKhoiHanh(),
    'admin/xoaKhachLichKhoiHanh' => (new AdminController())->xoaKhachLichKhoiHanh(),
    'admin/checkInKhach' => (new AdminController())->checkInKhach(),
    'admin/updateCheckIn' => (new AdminController())->updateCheckIn(),
    'admin/phanPhongKhachSan' => (new AdminController())->phanPhongKhachSan(),
    'admin/nhaCungCap' => (new AdminController())->nhaCungCap(),
    'admin/addNhacungcap' => (new AdminController())->addNhacungcap(),
    'admin/updateNhaCungCap' => (new AdminController())->updateNhaCungCap(),
    'admin/deleteNhaCungCap' => (new AdminController())->deleteNhaCungCap(),
    'admin/chiTietDichVu' => (new AdminController())->chiTietDichVu(),
    'admin/supplierServiceAction' => (new AdminController())->supplierServiceAction(),
    
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
    'khachHang/notificationCounts' => (new KhachHangController())->notificationCounts(),
    'khachHang/notificationFeed' => (new KhachHangController())->notificationFeed(),
    'khachHang/notificationStream' => (new KhachHangController())->notificationStream(),
    'khachHang/thanhToan' => (new KhachHangController())->thanhToan(),
    'khachHang/thanhToanTour' => (new KhachHangController())->thanhToanTour(),
    'khachHang/nhapThongTinThamGia' => (new KhachHangController())->nhapThongTinThamGia(),
    'khachHang/paymentStatus' => (new KhachHangController())->paymentStatus(),

    // Nhân sự
    'admin/nhanSu' => (new AdminController())->nhanSu(),
    'admin/nhanSuController' => (new AdminController())->nhanSu(),
    'admin/nhanSu_create' => (new AdminController())->nhanSuCreate(),
    'admin/nhanSu_update' => (new AdminController())->nhanSuUpdate(),
    'admin/nhanSu_delete' => (new AdminController())->nhanSuDelete(),
    'admin/nhanSu_chi_tiet' => (new AdminController())->nhanSuChiTiet(),

    // Quản lý HDV nâng cao
    'admin/hdv_advanced' => (new AdminController())->hdvAdvanced(),
    'admin/hdv_lich_table' => (new AdminController())->hdvLichTable(),
    'admin/hdv_add_schedule' => (new AdminController())->hdvAddSchedule(),
    'admin/hdv_get_schedule' => (new AdminController())->hdvGetSchedule(),
    'admin/hdv_send_notification' => (new AdminController())->hdvSendNotification(),
    'admin/hdv_detail' => (new AdminController())->hdvDetail(),
    
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

