<?php
require_once 'models/Booking.php';
require_once 'models/Tour.php';
require_once 'models/KhachHang.php';
require_once 'models/NguoiDung.php';
require_once 'models/BookingHistory.php';
require_once 'models/BookingDeletionHistory.php';
require_once 'models/LichKhoiHanh.php';
require_once 'models/BookingChangeRequest.php';
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

    public function changeRequests(): void
    {
        requireRole('Admin');

        $status = trim((string)($_GET['trang_thai'] ?? ''));
        $allowedStatus = ['MoiTao', 'TuDongDuyet', 'DaDuyet', 'TuChoi'];
        if ($status !== '' && !in_array($status, $allowedStatus, true)) {
            $status = '';
        }

        $model = new BookingChangeRequest();
        try {
            $requests = $model->getAllWithDetails($status, 500);
        } catch (Throwable $e) {
            $requests = [];
            $_SESSION['error'] = $e->getMessage();
        }

        require 'views/admin/booking_change_requests.php';
    }

    public function processChangeRequest(): void
    {
        requireRole('Admin');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: index.php?act=booking/changeRequests');
            exit();
        }

        $requestId = (int)($_POST['request_id'] ?? 0);
        $action = trim((string)($_POST['action'] ?? ''));
        $note = trim((string)($_POST['ghi_chu_xu_ly'] ?? ''));

        if ($requestId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
            $_SESSION['error'] = 'Thông tin xử lý yêu cầu không hợp lệ.';
            header('Location: index.php?act=booking/changeRequests');
            exit();
        }

        $changeModel = new BookingChangeRequest();
        $request = $changeModel->findById($requestId);
        if (!$request) {
            $_SESSION['error'] = 'Không tìm thấy yêu cầu cần xử lý.';
            header('Location: index.php?act=booking/changeRequests');
            exit();
        }

        $currentStatus = (string)($request['trang_thai'] ?? '');
        if (in_array($currentStatus, ['DaDuyet', 'TuChoi'], true)) {
            $_SESSION['error'] = 'Yêu cầu này đã được xử lý trước đó.';
            header('Location: index.php?act=booking/changeRequests');
            exit();
        }

        $bookingId = (int)($request['booking_id'] ?? 0);
        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Booking liên quan không còn tồn tại.';
            header('Location: index.php?act=booking/changeRequests');
            exit();
        }

        $conn = $this->bookingModel->conn;

        // Lấy nguoi_dung_id của khách hàng nếu có
        $nguoiDungId = 0;
        if (!empty($booking['khach_hang_id'])) {
            $stmtUser = $conn->prepare('SELECT nguoi_dung_id FROM khach_hang WHERE khach_hang_id = ? LIMIT 1');
            $stmtUser->execute([(int)$booking['khach_hang_id']]);
            $nguoiDungId = (int)$stmtUser->fetchColumn();
        }

        require_once 'models/ThongBao.php';
        $thongBaoModel = new ThongBao();

        if ($action === 'reject') {
            $rejectReason = $note !== '' ? $note : 'Admin từ chối yêu cầu do không đáp ứng điều kiện.';
            $ok = $changeModel->updateStatus($requestId, 'TuChoi', $rejectReason);

            if ($ok && $nguoiDungId > 0) {
                $thongBaoModel->insert([
                    'tieu_de' => 'Yêu cầu cho Booking #' . $bookingId . ' bị từ chối',
                    'noi_dung' => "Yêu cầu thay đổi/hủy cho đơn tour #" . $bookingId . " của bạn không được chấp thuận. Lý do: " . $rejectReason,
                    'loai_thong_bao' => 'Booking',
                    'muc_do_uu_tien' => 'Cao',
                    'nguoi_nhan_id' => $nguoiDungId,
                    'vai_tro_nhan' => 'KhachHang',
                    'trang_thai' => 'DaGui',
                    'thoi_gian_gui' => date('Y-m-d H:i:s'),
                ]);
            }

            $_SESSION[$ok ? 'success' : 'error'] = $ok
                ? 'Đã từ chối yêu cầu thay đổi booking.'
                : 'Không thể cập nhật trạng thái yêu cầu.';
            header('Location: index.php?act=booking/changeRequests');
            exit();
        }

        try {
            $conn->beginTransaction();

            $type = (string)($request['loai_yeu_cau'] ?? '');
            if ($type === 'Huy') {
                $phiHuy = max(0, (float)($request['phi_huy'] ?? 0));
                $append = '[ADMIN_APPROVE_CANCEL] phi_huy=' . number_format($phiHuy, 0, ',', '.') . ' VND';
                $oldNote = trim((string)($booking['ghi_chu'] ?? ''));
                $mergedNote = $oldNote !== '' ? ($oldNote . "\n" . $append) : $append;

                $updated = $this->bookingModel->update((int)$bookingId, [
                    'so_nguoi' => (int)($booking['so_nguoi'] ?? 1),
                    'ngay_khoi_hanh' => $booking['ngay_khoi_hanh'] ?? null,
                    'ngay_ket_thuc' => $booking['ngay_ket_thuc'] ?? null,
                    'tong_tien' => (float)($booking['tong_tien'] ?? 0),
                    'trang_thai' => 'Huy',
                    'ghi_chu' => $mergedNote,
                ]);

                if (!$updated) {
                    throw new RuntimeException('Không thể cập nhật booking khi duyệt yêu cầu hủy.');
                }

                // Tính toán tiền khách đã thanh toán
                $stmtPaid = $conn->prepare("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE booking_id = ? AND status IN ('ThanhCong', 'DaDoiSoat')");
                $stmtPaid->execute([$bookingId]);
                $daThanhToan = (float)$stmtPaid->fetchColumn();

                $tienHoan = max(0, $daThanhToan - $phiHuy);

                // Ghi nhận nghiệp vụ Chi tài chính hoàn tiền nếu số tiền hoàn > 0
                if ($tienHoan > 0) {
                    require_once __DIR__ . '/../services/PaymentFinanceService.php';
                    PaymentFinanceService::createChiRefundTransaction($conn, [
                        'booking_id' => $bookingId,
                        'tour_id' => (int)($booking['tour_id'] ?? 0),
                        'khach_hang_id' => (int)($booking['khach_hang_id'] ?? 0),
                        'amount' => $tienHoan,
                        'description' => 'Hoàn tiền hủy booking #' . $bookingId . ' (Đã thanh toán: ' . number_format($daThanhToan, 0, ',', '.') . 'đ, Phí hủy: ' . number_format($phiHuy, 0, ',', '.') . 'đ)',
                        'payment_date' => date('Y-m-d')
                    ]);
                }

                // Gửi thông báo cho khách hàng
                if ($nguoiDungId > 0) {
                    $msgRefund = ($tienHoan > 0) 
                        ? ("Số tiền hoàn lại cho bạn là: " . number_format($tienHoan, 0, ',', '.') . " VNĐ. Kế toán sẽ liên hệ để thực hiện hoàn tất.") 
                        : "Không có số tiền hoàn lại sau khi khấu trừ phí hủy.";

                    $thongBaoModel->insert([
                        'tieu_de' => 'Xác nhận hủy Booking #' . $bookingId,
                        'noi_dung' => "Yêu cầu hủy tour #" . $bookingId . " của bạn đã được duyệt. Phí hủy áp dụng: " . number_format($phiHuy, 0, ',', '.') . " VNĐ. " . $msgRefund,
                        'loai_thong_bao' => 'Booking',
                        'muc_do_uu_tien' => 'Cao',
                        'nguoi_nhan_id' => $nguoiDungId,
                        'vai_tro_nhan' => 'KhachHang',
                        'trang_thai' => 'DaGui',
                        'thoi_gian_gui' => date('Y-m-d H:i:s'),
                    ]);
                }
            } elseif ($type === 'DoiLich') {
                $scheduleId = (int)($request['lich_khoi_hanh_moi_id'] ?? 0);
                if ($scheduleId <= 0) {
                    throw new RuntimeException('Yêu cầu đổi lịch thiếu lịch khởi hành mới.');
                }

                $stmt = $conn->prepare('SELECT id, ngay_khoi_hanh, ngay_ket_thuc, so_cho FROM lich_khoi_hanh WHERE id = ? AND tour_id = ? LIMIT 1');
                $stmt->execute([$scheduleId, (int)($booking['tour_id'] ?? 0)]);
                $schedule = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                if (!$schedule) {
                    throw new RuntimeException('Lịch khởi hành mới không hợp lệ.');
                }

                $newDate = (string)($schedule['ngay_khoi_hanh'] ?? '');
                if ($newDate === '' || strtotime($newDate . ' 00:00:00') <= time()) {
                    throw new RuntimeException('Lịch khởi hành mới không còn hợp lệ theo thời gian.');
                }

                $soChoToiDa = max(1, (int)($schedule['so_cho'] ?? 0));
                $soDaDat = $this->bookingModel->getSoNguoiDaDatTheoLich((int)($booking['tour_id'] ?? 0), $newDate, false);
                $soNguoiBooking = max(1, (int)($booking['so_nguoi'] ?? 1));
                if (($soChoToiDa - $soDaDat) < $soNguoiBooking) {
                    throw new RuntimeException('Lịch mới không đủ chỗ để duyệt yêu cầu đổi lịch.');
                }

                $updated = $this->bookingModel->update((int)$bookingId, [
                    'so_nguoi' => (int)($booking['so_nguoi'] ?? 1),
                    'ngay_khoi_hanh' => $newDate,
                    'ngay_ket_thuc' => $schedule['ngay_ket_thuc'] ?? null,
                    'tong_tien' => (float)($booking['tong_tien'] ?? 0),
                    'trang_thai' => (string)($booking['trang_thai'] ?? 'ChoXacNhan'),
                    'ghi_chu' => trim((string)($booking['ghi_chu'] ?? '')),
                ]);

                if (!$updated) {
                    throw new RuntimeException('Không thể cập nhật booking khi duyệt yêu cầu đổi lịch.');
                }

                if ($nguoiDungId > 0) {
                    $thongBaoModel->insert([
                        'tieu_de' => 'Đổi lịch tour thành công cho Booking #' . $bookingId,
                        'noi_dung' => "Yêu cầu chuyển ngày khởi hành của đơn #" . $bookingId . " sang ngày " . date('d/m/Y', strtotime($newDate)) . " đã được admin phê duyệt thành công.",
                        'loai_thong_bao' => 'Booking',
                        'muc_do_uu_tien' => 'TrungBinh',
                        'nguoi_nhan_id' => $nguoiDungId,
                        'vai_tro_nhan' => 'KhachHang',
                        'trang_thai' => 'DaGui',
                        'thoi_gian_gui' => date('Y-m-d H:i:s'),
                    ]);
                }
            } else {
                throw new RuntimeException('Loại yêu cầu không hợp lệ.');
            }

            $statusNote = $note !== '' ? $note : 'Đã duyệt bởi admin.';
            $statusOk = $changeModel->updateStatus($requestId, 'DaDuyet', $statusNote);
            if (!$statusOk) {
                throw new RuntimeException('Không thể cập nhật trạng thái yêu cầu sau khi duyệt.');
            }

            $conn->commit();
            $_SESSION['success'] = 'Đã duyệt yêu cầu thay đổi booking thành công.';
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: index.php?act=booking/changeRequests');
        exit();
    }

    /**
     * API danh sách hành khách theo booking
     */
    public function apiBookingPassengers(): void
    {
        requireRole(['Admin', 'HDV']);
        header('Content-Type: application/json; charset=utf-8');

        $bookingId = (int)($_GET['id'] ?? ($_GET['booking_id'] ?? 0));
        if ($bookingId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã booking.'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            $booking = $this->bookingModel->getBookingWithDetails($bookingId);
            if (!$booking) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Booking không tồn tại.'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            require_once 'models/TourCheckin.php';
            $tourCheckinModel = new TourCheckin();
            $checkins = $tourCheckinModel->getByBookingId($bookingId);

            $passengers = [];
            if (!empty($checkins)) {
                foreach ($checkins as $idx => $c) {
                    $passengers[] = [
                        'id' => (int)($c['id'] ?? 0),
                        'stt' => $idx + 1,
                        'ho_ten' => $c['ho_ten'] ?? ($booking['customer_name'] ?? 'Khách ' . ($idx + 1)),
                        'so_dien_thoai' => $c['so_dien_thoai'] ?? '',
                        'email' => $c['email'] ?? '',
                        'so_cmnd' => $c['so_cmnd'] ?? ($c['so_passport'] ?? ''),
                        'gioi_tinh' => $c['gioi_tinh'] ?? 'Nam',
                        'ngay_sinh' => $c['ngay_sinh'] ?? '',
                        'quoc_tich' => $c['quoc_tich'] ?? 'Việt Nam',
                        'trang_thai' => $c['trang_thai'] ?? 'ChuaCheckIn',
                        'ghi_chu' => $c['ghi_chu'] ?? '',
                        'is_primary' => ($idx === 0),
                    ];
                }
            } else {
                $soNguoi = max(1, (int)($booking['so_nguoi'] ?? 1));
                for ($i = 1; $i <= $soNguoi; $i++) {
                    if ($i === 1) {
                        $passengers[] = [
                            'id' => 0,
                            'stt' => 1,
                            'ho_ten' => $booking['customer_name'] ?? ($booking['ho_ten'] ?? 'Người đặt tour'),
                            'so_dien_thoai' => $booking['customer_phone'] ?? ($booking['so_dien_thoai'] ?? ''),
                            'email' => $booking['customer_email'] ?? ($booking['email'] ?? ''),
                            'so_cmnd' => '',
                            'gioi_tinh' => 'Nam',
                            'ngay_sinh' => '',
                            'quoc_tich' => 'Việt Nam',
                            'trang_thai' => 'ChuaCheckIn',
                            'ghi_chu' => $booking['ghi_chu'] ?? '',
                            'is_primary' => true,
                        ];
                    } else {
                        $passengers[] = [
                            'id' => 0,
                            'stt' => $i,
                            'ho_ten' => 'Khách đoàn #' . $i,
                            'so_dien_thoai' => '',
                            'email' => '',
                            'so_cmnd' => '',
                            'gioi_tinh' => '',
                            'ngay_sinh' => '',
                            'quoc_tich' => 'Việt Nam',
                            'trang_thai' => 'ChuaCheckIn',
                            'ghi_chu' => '',
                            'is_primary' => false,
                        ];
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'booking' => [
                    'booking_id' => $bookingId,
                    'ma_booking' => 'BK-' . str_pad((string)$bookingId, 5, '0', STR_PAD_LEFT),
                    'tour_id' => (int)($booking['tour_id'] ?? 0),
                    'ten_tour' => $booking['ten_tour'] ?? '',
                    'ngay_khoi_hanh' => $booking['ngay_khoi_hanh'] ?? '',
                    'ngay_ket_thuc' => $booking['ngay_ket_thuc'] ?? '',
                    'so_nguoi' => (int)($booking['so_nguoi'] ?? 1),
                    'tong_tien' => (float)($booking['tong_tien'] ?? 0),
                    'tien_coc' => (float)($booking['tien_coc'] ?? 0),
                    'trang_thai' => $booking['trang_thai'] ?? '',
                    'customer_name' => $booking['customer_name'] ?? ($booking['ho_ten'] ?? ''),
                    'customer_phone' => $booking['customer_phone'] ?? ($booking['so_dien_thoai'] ?? ''),
                ],
                'passengers' => $passengers,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    /**
     * API cập nhật nhanh trạng thái booking từ modal
     */
    public function apiQuickUpdateStatus(): void
    {
        requireRole(['Admin', 'HDV']);
        header('Content-Type: application/json; charset=utf-8');

        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $input = $json;
            }
        }

        $bookingId = (int)($input['booking_id'] ?? 0);
        $trangThai = trim((string)($input['trang_thai'] ?? ''));
        $tienCoc = isset($input['tien_coc']) && $input['tien_coc'] !== '' ? (float)$input['tien_coc'] : null;
        $ghiChu = trim((string)($input['ghi_chu'] ?? ''));

        if ($bookingId <= 0 || empty($trangThai)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin booking hoặc trạng thái.'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $validStatuses = ['ChoXacNhan', 'DaCoc', 'HoanTat', 'Huy'];
        if (!in_array($trangThai, $validStatuses, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ.'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if (!$this->service->checkPermissionToUpdate($bookingId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật booking này.'], JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            $booking = $this->bookingModel->findById($bookingId);
            if (!$booking) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Booking không tồn tại.'], JSON_UNESCAPED_UNICODE);
                exit();
            }

            if ($tienCoc !== null) {
                $tongTien = (float)($booking['tong_tien'] ?? 0);
                if ($tienCoc > $tongTien) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Tiền cọc không được lớn hơn tổng tiền.'], JSON_UNESCAPED_UNICODE);
                    exit();
                }
                $trangThaiCoc = ($tienCoc >= $tongTien && $tongTien > 0) ? 'HoanTat' : ($tienCoc > 0 ? 'DaCoc' : 'ChuaCoc');
                $this->service->processTienCocUpdate($bookingId, $tienCoc, $trangThaiCoc, $ghiChu);
            }

            $currentBooking = $this->bookingModel->findById($bookingId);
            if ($currentBooking['trang_thai'] !== $trangThai) {
                $this->service->updateTrangThaiForBooking($bookingId, $trangThai, $ghiChu, $_SESSION['user_id'] ?? null);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật trạng thái booking thành công!',
                'booking_id' => $bookingId,
                'trang_thai' => $trangThai,
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
}
