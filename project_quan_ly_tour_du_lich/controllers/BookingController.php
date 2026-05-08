<?php
require_once 'models/Booking.php';
require_once 'models/Tour.php';
require_once 'models/KhachHang.php';
require_once 'models/NguoiDung.php';
require_once 'models/BookingHistory.php';
require_once 'models/BookingDeletionHistory.php';
require_once 'models/LichKhoiHanh.php';
require_once __DIR__ . '/../services/BookingService.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

class BookingController
{
    private Booking $bookingModel;
    private Tour $tourModel;
    private KhachHang $khachHangModel;
    private BookingService $service;

    public function __construct()
    {
        $this->bookingModel = new Booking();
        $this->tourModel    = new Tour();
        $this->khachHangModel = new KhachHang();

        $this->service = new BookingService(
            $this->bookingModel,
            $this->tourModel,
            $this->khachHangModel,
            new NguoiDung(),
            new BookingHistory(),
            new BookingDeletionHistory(),
            new LichKhoiHanh()
        );
    }

    // =====================================================================
    // CUSTOMER: ĐẶT TOUR
    // =====================================================================

    public function create(): void
    {
        requireRole('KhachHang');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_create')) {
                $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
                header('Location: index.php?act=tour/index');
                exit();
            }

            $schema = validateInputSchema([
                'tour_id'        => ['type' => 'id',   'required' => true],
                'ngay_khoi_hanh' => ['type' => 'date', 'required' => true],
                'ngay_ket_thuc'  => ['type' => 'date', 'required' => false],
                'so_nguoi'       => ['type' => 'id',   'required' => true],
                'tien_coc'       => ['type' => 'money','required' => false, 'min' => 0],
            ], 'POST');

            if (!$schema['ok']) {
                setValidationErrors($schema['errors'], 'Du lieu dat tour khong hop le.');
                $_SESSION['error'] = 'Du lieu dat tour khong hop le.';
                header('Location: index.php?act=tour/index');
                exit();
            }

            $khachHangId = $this->service->resolveKhachHangId();
            if (!$khachHangId) {
                header('Location: index.php?act=tour/index');
                exit();
            }

            $tourId = (int)($schema['data']['tour_id'] ?? 0);
            $tour   = $tourId > 0 ? $this->tourModel->findById($tourId) : null;
            if ($tourId <= 0 || !$tour) {
                header('Location: index.php?act=tour/index');
                exit();
            }

            $ngayKhoiHanh = (string)($schema['data']['ngay_khoi_hanh'] ?? '');
            $ngayKetThuc  = (string)($schema['data']['ngay_ket_thuc'] ?? $ngayKhoiHanh);
            $soNguoi      = (int)($schema['data']['so_nguoi'] ?? 1);
            $tienCoc      = (float)($schema['data']['tien_coc'] ?? 0);
            $ghiChu       = requestString('ghi_chu', '', 'POST');

            try {
                $bookingId = $this->service->createBooking(
                    $khachHangId, $tourId, $tour, $ngayKhoiHanh, $ngayKetThuc, $soNguoi, $tienCoc, $ghiChu
                );
                $data = ['ngay_khoi_hanh' => $ngayKhoiHanh, 'ngay_ket_thuc' => $ngayKetThuc,
                         'so_nguoi' => $soNguoi, 'tong_tien' => (float)($tour['gia_co_ban'] ?? 0) * $soNguoi,
                         'tien_coc' => $tienCoc, 'trang_thai' => 'ChoXacNhan', 'ghi_chu' => $ghiChu];
                $this->service->sendBookingConfirmationEmail($bookingId, $data, $tour);
                header("Location: index.php?act=booking/show&id={$bookingId}");
            } catch (\RuntimeException $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: index.php?act=tour/index');
            }
            exit();
        }

        // GET — hiển thị form
        $tourId = requestId('tour_id', 0, 'GET') ?? 0;
        $tour   = $this->tourModel->findById($tourId);
        if (!$tour) {
            header('Location: index.php?act=tour/index');
            exit();
        }
        require 'views/khach_hang/dat_tour.php';
    }

    public function show(): void
    {
        requireLogin();
        $id      = requestId('id', 0, 'GET') ?? 0;
        $booking = $this->bookingModel->findById($id);

        $khachHangId = $this->service->resolveKhachHangId();

        if (!$booking || (!hasRole(['Admin', 'HDV']) && $booking['khach_hang_id'] != $khachHangId)) {
            header('Location: index.php?act=tour/index');
            exit();
        }
        $tour = $this->tourModel->findById($booking['tour_id']);
        require 'views/khach_hang/hoa_don.php';
    }

    // =====================================================================
    // ADMIN: DANH SACH
    // =====================================================================

    public function index(): void
    {
        requireLogin();

        if (hasRole('KhachHang')) {
            $khachHangId = $this->service->resolveKhachHangId();
            if ($khachHangId) {
                $conditions = ['khach_hang_id' => $khachHangId];
                if (!empty($_GET['trang_thai'])) {
                    $conditions['trang_thai'] = $_GET['trang_thai'];
                }
                $bookings      = $this->bookingModel->find($conditions);
                $totalBookings = count($bookings);
                $pageNumber    = 1;
                $totalPages    = 1;
            } else {
                $bookings = []; $totalBookings = 0; $pageNumber = 1; $totalPages = 0;
            }
        } else {
            $filters = [];
            if (!empty($_GET['trang_thai']))        $filters['trang_thai']        = $_GET['trang_thai'];
            if (!empty($_GET['search']))             $filters['search']            = $_GET['search'];
            if (isset($_GET['co_yeu_cau_tour']) && $_GET['co_yeu_cau_tour'] !== '') {
                $filters['co_yeu_cau_tour'] = $_GET['co_yeu_cau_tour'];
            }

            $perPage       = 50;
            $pageNumber    = max(1, (int)($_GET['page'] ?? 1));
            $offset        = ($pageNumber - 1) * $perPage;

            $totalBookings = $this->bookingModel->countAllWithDetailsFiltered($filters);
            $bookings      = $this->bookingModel->getAllWithDetailsFiltered($filters, $perPage, $offset);
            $totalPages    = (int)ceil($totalBookings / $perPage);
        }

        require 'views/admin/quan_ly_booking.php';
    }

    // =====================================================================
    // ADMIN: CẬP NHẬT TRẠNG THÁI
    // =====================================================================

    public function updateTrangThai(): void
    {
        requireRole(['Admin', 'HDV']);
        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_status_update')) {
            $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $bookingId   = validateId($_POST['booking_id'] ?? null) ?? 0;
        $trangThaiMoi = requestString('trang_thai', '', 'POST');
        $ghiChu      = requestString('ghi_chu', '', 'POST');

        if (!$this->service->checkPermissionToUpdate($bookingId)) {
            $_SESSION['error'] = 'Bạn không có quyền cập nhật booking này.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        if ($bookingId <= 0 || empty($trangThaiMoi)) {
            $_SESSION['error'] = 'Thông tin không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        if (!in_array($trangThaiMoi, ['ChoXacNhan', 'DaCoc', 'HoanTat', 'Huy'], true)) {
            $_SESSION['error'] = 'Trạng thái không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $result = $this->service->updateTrangThaiForBooking(
            $bookingId, $trangThaiMoi, $ghiChu, $_SESSION['user_id'] ?? null
        );

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? 'Cập nhật trạng thái booking thành công.'
            : 'Không thể cập nhật trạng thái booking.';

        header('Location: index.php?act=booking/chiTiet&id=' . $bookingId);
        exit();
    }

    // =====================================================================
    // ADMIN: CẬP NHẬT TIỀN CỌC
    // =====================================================================

    public function updateTienCoc(): void
    {
        requireRole(['Admin', 'HDV']);
        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_payment_update')) {
            $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $bookingId = validateId($_POST['booking_id'] ?? null) ?? 0;
        if ($bookingId <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        if (!$this->service->checkPermissionToUpdate($bookingId)) {
            $_SESSION['error'] = 'Bạn không có quyền cập nhật booking này.';
            header('Location: index.php?act=booking/chiTiet&id=' . $bookingId);
            exit();
        }

        $tienCoc     = validateMoney($_POST['tien_coc'] ?? 0, 0) ?? 0;
        $trangThaiCoc = requestString('trang_thai_coc', 'ChuaCoc', 'POST');
        $ghiChuCoc   = requestString('ghi_chu_coc', '', 'POST');

        try {
            $msg = $this->service->processTienCocUpdate($bookingId, $tienCoc, $trangThaiCoc, $ghiChuCoc);
            $_SESSION['success'] = $msg;
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: index.php?act=booking/chiTiet&id=' . $bookingId);
        exit();
    }

    // =====================================================================
    // ADMIN: CHI TIẾT
    // =====================================================================

    public function chiTiet(): void
    {
        requireLogin();
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $booking = $this->bookingModel->getBookingWithDetails($id);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        if (!$this->service->checkPermissionToView($booking)) {
            $_SESSION['error'] = 'Bạn không có quyền xem booking này.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $history = (new BookingHistory())->getByBookingId($id);
        $tour    = $this->tourModel->findById($booking['tour_id']);

        // Lấy yêu cầu tour của khách hàng (nếu có)
        $yeuCauTour = null;
        if (!empty($booking['khach_hang_id'])) {
            require_once 'models/ThongBao.php';
            $khachHang = $this->khachHangModel->findById($booking['khach_hang_id']);
            if ($khachHang && !empty($khachHang['nguoi_dung_id'])) {
                $yeuCauList = (new ThongBao())->getYeuCauTour(['search' => '', 'limit' => 50]);
                foreach ($yeuCauList as $yc) {
                    if (isset($yc['nguoi_gui_id']) && $yc['nguoi_gui_id'] == $khachHang['nguoi_dung_id']) {
                        $yeuCauTour = $yc;
                        break;
                    }
                }
            }
        }

        require 'views/admin/chi_tiet_booking.php';
    }

    // =====================================================================
    // ADMIN: UPDATE
    // =====================================================================

    public function update(): void
    {
        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_update')) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Phien lam viec khong hop le.');
            $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $id = validateId($_POST['booking_id'] ?? null) ?? 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        if (!$this->service->checkPermissionToUpdate($id)) {
            $_SESSION['error'] = 'Bạn không có quyền cập nhật booking này.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        try {
            $msg = $this->service->updateBookingData($id, $_POST);
            $_SESSION['success'] = $msg;
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: index.php?act=booking/chiTiet&id=' . $id);
        exit();
    }

    // =====================================================================
    // ADMIN: XÓA
    // =====================================================================

    public function delete(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        requireRole('Admin', 'admin/quanLyBooking', 'Chi Admin moi co quyen xoa booking.');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $booking = $this->bookingModel->getBookingWithDetails($id);
            if (!$booking) {
                $_SESSION['error'] = 'Booking không tồn tại.';
                header('Location: index.php?act=admin/quanLyBooking');
                exit();
            }
            require 'views/admin/xac_nhan_xoa_booking.php';
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_delete')) {
                $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
                header('Location: index.php?act=booking/delete&id=' . $id);
                exit();
            }

            $matKhau = (string)($_POST['mat_khau'] ?? '');
            $lyDoXoa = requestString('ly_do_xoa', '', 'POST');
            $adminId = (int)($_SESSION['user_id'] ?? 0);

            try {
                $this->service->deleteBooking($id, $matKhau, $adminId, $lyDoXoa);
                $_SESSION['success'] = 'Xóa booking thành công.';
            } catch (\RuntimeException $e) {
                $_SESSION['error'] = $e->getMessage();
            }

            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
    }

    // =====================================================================
    // ADMIN: ẨN BOOKING
    // =====================================================================

    public function hideCompleted(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_hide')) {
            $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        requireRole('Admin', 'admin/quanLyBooking', 'Chi Admin moi co quyen an booking.');

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $lyDoAn = trim((string)($_POST['ly_do_an'] ?? ''));
        $userId = (int)($_SESSION['user_id'] ?? 0);

        try {
            $this->service->hideBooking($bookingId, $lyDoAn, $userId);
            $_SESSION['success'] = 'Đã ẩn booking hoàn tất. Bạn có thể xem lại trong mục "Booking đã hoàn thành".';
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === '__already_hidden__') {
                $_SESSION['success'] = 'Booking này đã được ẩn trước đó.';
            } else {
                $_SESSION['error'] = $e->getMessage();
            }
        }

        header('Location: index.php?act=admin/quanLyBooking');
        exit();
    }

    // =====================================================================
    // ADMIN: ĐẶT TOUR CHO KHÁCH
    // =====================================================================

    public function datTourChoKhach(): void
    {
        requireRole('Admin', 'admin/quanLyBooking');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_staff_create')) {
                $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
                header('Location: index.php?act=booking/datTourChoKhach');
                exit();
            }
            try {
                $bookingId = $this->service->createBookingForKhach($_POST);
                $_SESSION['success'] = "Đặt tour thành công! Mã booking: #{$bookingId}";
                header("Location: index.php?act=booking/datTourChoKhach&success=1&booking_id={$bookingId}");
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                setValidationErrors(['general' => $e->getMessage()], 'Thong tin dat tour khong hop le.');
                $_SESSION['form_data'] = $_POST;
                header('Location: index.php?act=booking/datTourChoKhach');
            }
            exit();
        }

        // GET — hiển thị form
        $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : null;
        $tours  = $this->tourModel->getAll();
        $tour   = $tourId ? $this->tourModel->findById($tourId) : null;

        $lichKhoiHanhList = [];
        if ($tour) {
            $lichKhoiHanhList = $this->tourModel->getLichKhoiHanhByTourId($tourId);
        }

        $formData = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        require 'views/admin/dat_tour_cho_khach.php';
    }

    // =====================================================================
    // API: KIỂM TRA CHỖ TRỐNG
    // =====================================================================

    public function kiemTraChoTrong(): void
    {
        header('Content-Type: application/json');
        $tourId       = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
        $ngayKhoiHanh = $_GET['ngay_khoi_hanh'] ?? '';
        $soNguoi      = isset($_GET['so_nguoi']) ? (int)$_GET['so_nguoi'] : 1;

        if ($tourId <= 0 || empty($ngayKhoiHanh)) {
            echo json_encode(['error' => 'Thiếu thông tin']);
            exit();
        }
        echo json_encode($this->bookingModel->kiemTraChoTrong($tourId, $ngayKhoiHanh, $soNguoi));
        exit();
    }

    // =====================================================================
    // TÀI LIỆU / PDF
    // =====================================================================

    public function xuatTaiLieu(): void
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        $booking = $this->bookingModel->getBookingWithDetails($id);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        if (isset($booking[0]) && is_array($booking[0])) {
            $booking = $booking[0];
        }
        require 'views/admin/xuat_tai_lieu_booking.php';
    }

    public function exportPDF(): void
    {
        $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $type = $_GET['type'] ?? 'bao-gia';

        if ($id <= 0) {
            die('ID không hợp lệ');
        }
        $booking = $this->bookingModel->getBookingWithDetails($id);
        if (!$booking) {
            die('Booking không tồn tại');
        }
        if (isset($booking[0]) && is_array($booking[0])) {
            $booking = $booking[0];
        }

        $filenames = ['hop-dong' => 'Hop_Dong_', 'hoa-don' => 'Hoa_Don_'];
        $filename  = ($filenames[$type] ?? 'Bao_Gia_') . $booking['booking_id'] . '.pdf';
        $bodyHtml  = $this->service->buildBookingPdfHtml($type, $booking);

        header('Content-Type: text/html; charset=UTF-8');
        echo $this->service->wrapPdfHtml($filename, $bodyHtml);
    }

    public function sendEmail(): void
    {
        header('Content-Type: application/json');
        $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $type = $_GET['type'] ?? 'bao-gia';

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit();
        }
        $booking = $this->bookingModel->getBookingWithDetails($id);
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking không tồn tại']);
            exit();
        }
        $email = $booking['email'];
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Không có email khách hàng']);
            exit();
        }

        $subjects = [
            'hop-dong' => 'Hợp đồng dịch vụ du lịch - Booking #' . $booking['booking_id'],
            'hoa-don'  => 'Hóa đơn thanh toán - Booking #' . $booking['booking_id'],
        ];
        $subject  = $subjects[$type] ?? 'Báo giá tour du lịch - Booking #' . $booking['booking_id'];
        $htmlBody = $this->service->buildBookingPdfHtml($type, $booking);
        $this->service->sendDocumentEmail($email, $subject, $htmlBody, $booking['ho_ten'] ?? '');

        echo json_encode(['success' => true, 'message' => 'Đã gửi email thành công']);
        exit();
    }
}
