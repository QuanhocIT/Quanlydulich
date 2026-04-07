<?php
require_once 'models/Booking.php';
require_once 'models/Tour.php';
require_once 'models/KhachHang.php';
require_once 'models/NguoiDung.php';
require_once 'models/BookingHistory.php';
require_once 'models/BookingDeletionHistory.php';
require_once 'models/LichKhoiHanh.php';
require_once __DIR__ . '/../commons/mail.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

class BookingController {
    private $bookingModel;
    private $tourModel;
    private $khachHangModel;
    private $nguoiDungModel;
    private $historyModel;
    private $deletionHistoryModel;
    private $lichKhoiHanhModel;
    
    public function __construct() {
        $this->bookingModel = new Booking();
        $this->tourModel = new Tour();
        $this->khachHangModel = new KhachHang();
        $this->nguoiDungModel = new NguoiDung();
        $this->historyModel = new BookingHistory();
        $this->deletionHistoryModel = new BookingDeletionHistory();
        $this->lichKhoiHanhModel = new LichKhoiHanh();
    }
    
    public function create() {
        requireRole('KhachHang');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_create')) {
                $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
                header('Location: index.php?act=tour/index');
                exit();
            }

            $schema = validateInputSchema([
                'tour_id' => ['type' => 'id', 'required' => true],
                'ngay_khoi_hanh' => ['type' => 'date', 'required' => true],
                'ngay_ket_thuc' => ['type' => 'date', 'required' => false],
                'so_nguoi' => ['type' => 'id', 'required' => true],
                'tien_coc' => ['type' => 'money', 'required' => false, 'min' => 0],
                'tong_tien' => ['type' => 'money', 'required' => false, 'min' => 0],
            ], 'POST');
            if (!$schema['ok']) {
                setValidationErrors($schema['errors'], 'Du lieu dat tour khong hop le.');
                $_SESSION['error'] = 'Du lieu dat tour khong hop le.';
                header('Location: index.php?act=tour/index');
                exit();
            }

            $khachHangId = $_SESSION['khach_hang_id'] ?? null;
            if (!$khachHangId && isset($_SESSION['user_id'])) {
                $khachHang = $this->khachHangModel->findByNguoiDungId($_SESSION['user_id']);
                if ($khachHang) {
                    $khachHangId = $khachHang['khach_hang_id'];
                    $_SESSION['khach_hang_id'] = $khachHangId;
                }
            }

            if (!$khachHangId) {
                header('Location: index.php?act=tour/index');
                exit();
            }

            $tourId = (int)($schema['data']['tour_id'] ?? 0);
            $tour = $tourId > 0 ? $this->tourModel->findById($tourId) : null;
            if ($tourId <= 0 || !$tour) {
                header('Location: index.php?act=tour/index');
                exit();
            }

            $ngayKhoiHanh = (string)($schema['data']['ngay_khoi_hanh'] ?? '');
            $ngayKetThuc = (string)($schema['data']['ngay_ket_thuc'] ?? $ngayKhoiHanh);
            $soNguoi = (int)($schema['data']['so_nguoi'] ?? 1);

            $tienCoc = (float)($schema['data']['tien_coc'] ?? 0);
            $tongTienInput = $schema['data']['tong_tien'];
            $tongTien = $tongTienInput ?? ((float)($tour['gia_co_ban'] ?? 0) * $soNguoi);

            $data = [
                'tour_id' => $tourId,
                'khach_hang_id' => $khachHangId,
                'ngay_dat' => date('Y-m-d'),
                'so_nguoi' => $soNguoi,
                'ngay_khoi_hanh' => $ngayKhoiHanh,
                'ngay_ket_thuc' => $ngayKetThuc,
                'tong_tien' => $tongTien,
                'tien_coc' => $tienCoc,
                'trang_thai' => 'ChoXacNhan',
                'ghi_chu' => requestString('ghi_chu', '', 'POST')
            ];
            
            $bookingId = $this->bookingModel->insert($data);
            if ($bookingId) {
                // Tự động tạo lịch khởi hành nếu chưa có
                if (!empty($ngayKhoiHanh) && !empty($tourId)) {
                    $lichKhoiHanh = $this->lichKhoiHanhModel->findByTourAndNgayKhoiHanh($tourId, $ngayKhoiHanh);
                    if (!$lichKhoiHanh) {
                        // Tạo lịch khởi hành mới
                        $lichKhoiHanhData = [
                            'tour_id' => $tourId, // Đảm bảo tour_id luôn có giá trị
                            'ngay_khoi_hanh' => $ngayKhoiHanh,
                            'ngay_ket_thuc' => $ngayKetThuc,
                            'gio_xuat_phat' => null,
                            'gio_ket_thuc' => null,
                            'diem_tap_trung' => '',
                            'so_cho' => 50, // Mặc định
                            'hdv_id' => null,
                            'trang_thai' => 'SapKhoiHanh',
                            'ghi_chu' => 'Tạo tự động từ booking #' . $bookingId
                        ];
                        $lichKhoiHanhId = $this->lichKhoiHanhModel->insert($lichKhoiHanhData);
                        if ($lichKhoiHanhId) {
                            // Tự động phân bổ nhân sự nếu admin quên
                            $this->tuDongPhanBoNhanSu($lichKhoiHanhId, $ngayKhoiHanh, $ngayKetThuc);
                        }
                    } else {
                        // Lịch khởi hành đã có, kiểm tra và tự động phân bổ nếu chưa có
                        $this->tuDongPhanBoNhanSu($lichKhoiHanh['id'], $ngayKhoiHanh, $ngayKetThuc);
                    }
                }
                
                header("Location: index.php?act=booking/show&id=$bookingId");
                exit();
            } else {
                header('Location: index.php?act=tour/index');
                exit();
            }
        } else {
            $tourId = requestId('tour_id', 0, 'GET') ?? 0;
            $tour = $this->tourModel->findById($tourId);
            if (!$tour) {
                header('Location: index.php?act=tour/index');
                exit();
            }
            require 'views/khach_hang/dat_tour.php';
        }
    }
    
    public function show() {
        // requireLogin();
        $id = requestId('id', 0, 'GET') ?? 0;
        $booking = $this->bookingModel->findById($id);
        
        $khachHangId = $_SESSION['khach_hang_id'] ?? null;
        if (!$khachHangId && isset($_SESSION['user_id'])) {
            $khachHang = $this->khachHangModel->findByNguoiDungId($_SESSION['user_id']);
            if ($khachHang) {
                $khachHangId = $khachHang['khach_hang_id'];
                $_SESSION['khach_hang_id'] = $khachHangId;
            }
        }

        if (!$booking || ($khachHangId && $booking['khach_hang_id'] != $khachHangId)) {
            header('Location: index.php?act=tour/index');
            exit();
        }
        $tour = $this->tourModel->findById($booking['tour_id']);
        require 'views/khach_hang/hoa_don.php';
    }
    
    public function index() {
        // requireLogin();
        $filters = [];
        
        if (hasRole('KhachHang')) {
            $khachHangId = $_SESSION['khach_hang_id'] ?? null;
            if (!$khachHangId && isset($_SESSION['user_id'])) {
                $khachHang = $this->khachHangModel->findByNguoiDungId($_SESSION['user_id']);
                if ($khachHang) {
                    $khachHangId = $khachHang['khach_hang_id'];
                    $_SESSION['khach_hang_id'] = $khachHangId;
                }
            }
            // Customer role gets filtered by their own customer ID
            if ($khachHangId) {
                // For customer role, use find() with single customer ID
                $conditions = ['khach_hang_id' => $khachHangId];
                if (isset($_GET['trang_thai']) && !empty($_GET['trang_thai'])) {
                    $conditions['trang_thai'] = $_GET['trang_thai'];
                }
                $bookings = $this->bookingModel->find($conditions);
                $totalBookings = count($bookings);
                $pageNumber = 1;
                $totalPages = 1;
            } else {
                // No customer ID, show empty
                $bookings = [];
                $totalBookings = 0;
                $pageNumber = 1;
                $totalPages = 0;
            }
        } else {
            // Admin view with pagination
            if (isset($_GET['trang_thai']) && !empty($_GET['trang_thai'])) {
                $filters['trang_thai'] = $_GET['trang_thai'];
            }
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $filters['search'] = $_GET['search'];
            }
            if (isset($_GET['co_yeu_cau_tour']) && $_GET['co_yeu_cau_tour'] !== '') {
                $filters['co_yeu_cau_tour'] = $_GET['co_yeu_cau_tour'];
            }

            $perPage = 50;
            $pageNumber = max(1, (int)($_GET['page'] ?? 1));
            $offset = ($pageNumber - 1) * $perPage;

            $totalBookings = $this->bookingModel->countAllWithDetailsFiltered($filters);
            $bookings = $this->bookingModel->getAllWithDetailsFiltered($filters, $perPage, $offset);
            $totalPages = (int)ceil($totalBookings / $perPage);
        }
        
        require 'views/admin/quan_ly_booking.php';
    }

    // Cập nhật trạng thái booking
    public function updateTrangThai() {
        // requireLogin();
        $bookingId = validateId($_POST['booking_id'] ?? null) ?? 0;
        $trangThaiMoi = requestString('trang_thai', '', 'POST');
        $ghiChu = requestString('ghi_chu', '', 'POST');
        $nguoiThayDoiId = $_SESSION['user_id'] ?? null;
        
        // Kiểm tra quyền
        if (!$this->checkPermissionToUpdate($bookingId)) {
            $_SESSION['error'] = 'Bạn không có quyền cập nhật booking này.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        if ($bookingId <= 0 || empty($trangThaiMoi)) {
            $_SESSION['error'] = 'Thông tin không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        $validStatuses = ['ChoXacNhan', 'DaCoc', 'HoanTat', 'Huy'];
        if (!in_array($trangThaiMoi, $validStatuses)) {
            $_SESSION['error'] = 'Trạng thái không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        // Nếu đổi sang "Hoàn tất", cần cập nhật tiền cọc = tổng tiền
        if ($trangThaiMoi == 'HoanTat') {
            $booking = $this->bookingModel->findById($bookingId);
            if ($booking) {
                $tongTien = (float)($booking['tong_tien'] ?? 0);
                if ($tongTien > 0) {
                    // Cập nhật tiền cọc và trạng thái cọc
                    $conn = connectDB();
                    $sql = "UPDATE booking SET tien_coc = ?, trang_thai_coc = 'HoanTat' WHERE booking_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$tongTien, $bookingId]);
                    
                    $ghiChu = $ghiChu ?: 'Đã thanh toán đủ (tự động cập nhật tiền cọc = tổng tiền)';
                }
            }
        }
        
        $result = $this->bookingModel->updateTrangThai($bookingId, $trangThaiMoi, $nguoiThayDoiId, $ghiChu);
        
        if ($result) {
            $_SESSION['success'] = 'Cập nhật trạng thái booking thành công.';
        } else {
            $_SESSION['error'] = 'Không thể cập nhật trạng thái booking.';
        }
        
        header('Location: index.php?act=booking/chiTiet&id=' . $bookingId);
        exit();
    }

    // Cập nhật tiền cọc
    public function updateTienCoc() {
        // requireLogin();
        $bookingId = validateId($_POST['booking_id'] ?? null) ?? 0;
        
        if ($bookingId <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        // Kiểm tra quyền
        if (!$this->checkPermissionToUpdate($bookingId)) {
            $_SESSION['error'] = 'Bạn không có quyền cập nhật booking này.';
            header('Location: index.php?act=booking/chiTiet&id=' . $bookingId);
            exit();
        }
        
        $tienCoc = validateMoney($_POST['tien_coc'] ?? 0, 0) ?? 0;
        $trangThaiCoc = requestString('trang_thai_coc', 'ChuaCoc', 'POST');
        $ghiChuCoc = requestString('ghi_chu_coc', '', 'POST');
        // Tính số tiền còn lại
        $soTienConLai = 0;
        
        // Lấy thông tin booking hiện tại
        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        // Validate tiền cọc không được vượt quá tổng tiền
        $tongTien = (float)($booking['tong_tien'] ?? 0);
        if ($tienCoc > $tongTien) {
            $_SESSION['error'] = 'Số tiền cọc không được vượt quá tổng tiền (' . number_format($tongTien) . ' ₫).';
            header('Location: index.php?act=booking/chiTiet&id=' . $bookingId);
            exit();
        }
        
        // Nếu chưa nhập tiền cọc, tính 30% tổng tiền
        if ($tienCoc == 0 && $tongTien > 0) {
            $tienCoc = round($tongTien * 0.3);
        }
        
        // Kiểm tra nếu tiền cọc bằng tổng tiền thì tự động đổi trạng thái thành "Hoàn tất" (Đã thanh toán)
        $trangThaiBookingMoi = $booking['trang_thai'];
        $trangThaiCocMoi = $trangThaiCoc;
        
        if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
            // Tiền cọc = tổng tiền (dùng abs để tránh lỗi làm tròn)
            $trangThaiBookingMoi = 'HoanTat';
            $trangThaiCocMoi = 'HoanTat';
        } elseif ($tienCoc > 0 && $trangThaiCoc == 'DaCoc' && $booking['trang_thai'] != 'DaCoc' && $booking['trang_thai'] != 'HoanTat') {
            // Nếu đã cọc nhưng chưa bằng tổng tiền, đổi thành "Đã cọc"
            $trangThaiBookingMoi = 'DaCoc';
        }
        
        // Cập nhật vào database
        // Giả định có các trường tien_coc và trang_thai_coc trong bảng booking
        // Nếu chưa có, sẽ cần ALTER TABLE để thêm các cột này
        $conn = connectDB();
        try {
            // Kiểm tra xem các cột có tồn tại không
            $hasTienCoc = dbColumnExists('booking', 'tien_coc', $conn);
            
            if ($hasTienCoc) {
                // Cập nhật với các trường mới
                // Kiểm tra cột so_tien_con_lai
                $hasSoTienConLai = dbColumnExists('booking', 'so_tien_con_lai', $conn);
                $soTienConLai = $tongTien - $tienCoc;
                if ($soTienConLai < 0) $soTienConLai = 0;
                if ($hasSoTienConLai) {
                    $sql = "UPDATE booking SET tien_coc = ?, trang_thai_coc = ?, so_tien_con_lai = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([$tienCoc, $trangThaiCocMoi, $soTienConLai, $bookingId]);
                } else {
                    $sql = "UPDATE booking SET tien_coc = ?, trang_thai_coc = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($sql);
                    $result = $stmt->execute([$tienCoc, $trangThaiCocMoi, $bookingId]);
                }
            } else {
                // Nếu chưa có cột, chỉ cập nhật trạng thái booking nếu cần
                // Hoặc có thể tự động tạo cột (không khuyến khích trong production)
                $result = true; // Tạm thời return true, sẽ cần ALTER TABLE sau
            }
            
            if ($result) {
                // Cập nhật trạng thái booking nếu có thay đổi
                if ($trangThaiBookingMoi != $booking['trang_thai']) {
                    $ghiChuThayDoi = 'Cập nhật tiền cọc: ' . number_format($tienCoc) . ' ₫';
                    if ($tienCoc >= $tongTien) {
                        $ghiChuThayDoi .= ' (Đã thanh toán đủ)';
                    }
                    if ($ghiChuCoc) {
                        $ghiChuThayDoi .= ' - ' . $ghiChuCoc;
                    }
                    $this->bookingModel->updateTrangThai($bookingId, $trangThaiBookingMoi, $_SESSION['user_id'] ?? null, $ghiChuThayDoi);
                }
                
                $thongBao = 'Cập nhật tiền cọc thành công. Số tiền cọc: ' . number_format($tienCoc) . ' ₫';
                if ($tienCoc >= $tongTien && $tongTien > 0) {
                    $thongBao .= ' - Trạng thái đã tự động chuyển thành "Hoàn tất" (Đã thanh toán đủ)';
                }
                $_SESSION['success'] = $thongBao;
            } else {
                $_SESSION['error'] = 'Không thể cập nhật tiền cọc.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi khi cập nhật tiền cọc: ' . $e->getMessage();
        }
        
        header('Location: index.php?act=booking/chiTiet&id=' . $bookingId);
        exit();
    }

    // Xem chi tiết booking
    public function chiTiet() {
        // requireLogin();
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
        
        // Kiểm tra quyền xem
        if (!$this->checkPermissionToView($booking)) {
            $_SESSION['error'] = 'Bạn không có quyền xem booking này.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        // Lấy lịch sử thay đổi
        $history = $this->historyModel->getByBookingId($id);
        
        // Lấy thông tin tour
        $tour = $this->tourModel->findById($booking['tour_id']);
        
        // Lấy yêu cầu tour của khách hàng (nếu có)
        $yeuCauTour = null;
        if (!empty($booking['khach_hang_id'])) {
            require_once 'models/KhachHang.php';
            require_once 'models/ThongBao.php';
            
            $khachHangModel = new KhachHang();
            $khachHang = $khachHangModel->findById($booking['khach_hang_id']);
            
            if ($khachHang && !empty($khachHang['nguoi_dung_id'])) {
                $thongBaoModel = new ThongBao();
                
                // Lấy tất cả yêu cầu tour, sau đó filter theo khách hàng này
                // Lấy yêu cầu mới nhất của khách hàng
                $yeuCauList = $thongBaoModel->getYeuCauTour([
                    'search' => '',
                    'limit' => 50
                ]);
                
                // Tìm yêu cầu của khách hàng này (ưu tiên yêu cầu mới nhất)
                foreach ($yeuCauList as $yc) {
                    if (isset($yc['nguoi_gui_id']) && $yc['nguoi_gui_id'] == $khachHang['nguoi_dung_id']) {
                        $yeuCauTour = $yc;
                        break; // Lấy yêu cầu đầu tiên (mới nhất do đã sort DESC)
                    }
                }
            }
        }
        
        require 'views/admin/chi_tiet_booking.php';
    }

    // Cập nhật thông tin booking
    public function update() {
        // requireLogin();
        if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_update')) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Phien lam viec khong hop le.');
            $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $id = validateId($_POST['booking_id'] ?? null) ?? 0;
        
        if ($id <= 0) {
            setValidationErrors(['booking_id' => 'invalid'], 'ID booking khong hop le.');
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        // Kiểm tra quyền
        if (!$this->checkPermissionToUpdate($id)) {
            setValidationErrors(['permission' => 'denied'], 'Ban khong co quyen cap nhat booking nay.');
            $_SESSION['error'] = 'Bạn không có quyền cập nhật booking này.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        $ngayKhoiHanh = validateDateYmd($_POST['ngay_khoi_hanh'] ?? '') ?? null;
        $ngayKetThuc = validateDateYmd($_POST['ngay_ket_thuc'] ?? '') ?? $ngayKhoiHanh;
        
        $tongTien = validateMoney($_POST['tong_tien'] ?? null, 0);
        $tienCoc = validateMoney($_POST['tien_coc'] ?? 0, 0);
        if ($tongTien === null || $tienCoc === null) {
            setValidationErrors([
                'tong_tien' => $tongTien === null ? 'invalid' : null,
                'tien_coc' => $tienCoc === null ? 'invalid' : null,
            ], 'So tien khong hop le.');
            $_SESSION['error'] = 'Số tiền không hợp lệ.';
            header('Location: index.php?act=booking/chiTiet&id=' . $id);
            exit();
        }

        if ($tienCoc > $tongTien) {
            setValidationErrors([
                'tien_coc' => 'gt:tong_tien'
            ], 'Tien coc khong duoc lon hon tong tien.');
            $_SESSION['error'] = 'Tiền cọc không được lớn hơn tổng tiền.';
            header('Location: index.php?act=booking/chiTiet&id=' . $id);
            exit();
        }
        
        // Lấy thông tin booking hiện tại
        $booking = $this->bookingModel->findById($id);
        $trangThaiCu = $booking['trang_thai'] ?? '';
        
        // Xác định trạng thái booking mới từ form
        $trangThaiMoi = $_POST['trang_thai'] ?? $trangThaiCu;
        
        // Nếu đổi sang trạng thái "Hoàn tất", tự động set tiền cọc = tổng tiền
        if ($trangThaiMoi == 'HoanTat' && $tongTien > 0) {
            $tienCoc = $tongTien;
        } else {
            // Lấy tiền cọc hiện tại nếu chưa nhập
            if ($tienCoc == 0) {
                $tienCoc = (float)($booking['tien_coc'] ?? ($booking['so_tien_coc'] ?? 0));
            }
            
            // Nếu chưa có tiền cọc và tổng tiền > 0, tính 30% tổng tiền
            if ($tienCoc == 0 && $tongTien > 0) {
                $tienCoc = round($tongTien * 0.3);
            }
        }
        
        // Nếu tiền cọc bằng tổng tiền, tự động đổi thành "Hoàn tất" (Đã thanh toán)
        if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
            $trangThaiMoi = 'HoanTat';
        } elseif ($tienCoc > 0 && $tienCoc < $tongTien && $trangThaiCu != 'HoanTat' && $trangThaiMoi != 'HoanTat') {
            // Nếu đã cọc nhưng chưa đủ, đổi thành "Đã cọc" nếu chưa phải "Hoàn tất"
            if ($trangThaiCu == 'ChoXacNhan') {
                $trangThaiMoi = 'DaCoc';
            }
        }
        
        // Xác định trạng thái cọc - ưu tiên từ form, sau đó tự động xác định
        $trangThaiCoc = $_POST['trang_thai_coc'] ?? $booking['trang_thai_coc'] ?? 'ChuaCoc';
        
        // Tự động điều chỉnh trạng thái cọc dựa trên logic
        if ($trangThaiMoi == 'HoanTat' || ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01)) {
            $trangThaiCoc = 'HoanTat';
        } elseif ($tienCoc > 0 && $trangThaiCoc == 'ChuaCoc') {
            $trangThaiCoc = 'DaCoc';
        }
        
        $data = [
            'so_nguoi' => validateId($_POST['so_nguoi'] ?? 1) ?? 1,
            'ngay_khoi_hanh' => $ngayKhoiHanh,
            'ngay_ket_thuc' => $ngayKetThuc,
            'tong_tien' => $tongTien,
            'tien_coc' => $tienCoc,
            'trang_thai_coc' => $trangThaiCoc,
            'trang_thai' => $trangThaiMoi,
            'ghi_chu' => requestString('ghi_chu', '', 'POST')
        ];
        
        // Kiểm tra có thay đổi gì không
        $coThayDoi = false;
        $thayDoiChiTiet = [];
        
        if ($trangThaiMoi !== $trangThaiCu) {
            $coThayDoi = true;
            $thayDoiChiTiet[] = "Trạng thái: {$trangThaiCu} → {$trangThaiMoi}";
        }
        
        $tienCocCu = (float)($booking['tien_coc'] ?? ($booking['so_tien_coc'] ?? 0));
        if (abs($tienCoc - $tienCocCu) > 0.01) {
            $coThayDoi = true;
            $thayDoiChiTiet[] = "Tiền cọc: " . number_format($tienCocCu) . " ₫ → " . number_format($tienCoc) . " ₫";
        }
        
        $tongTienCu = (float)($booking['tong_tien'] ?? 0);
        if (abs($tongTien - $tongTienCu) > 0.01) {
            $coThayDoi = true;
            $thayDoiChiTiet[] = "Tổng tiền: " . number_format($tongTienCu) . " ₫ → " . number_format($tongTien) . " ₫";
        }
        
        $soNguoiCu = (int)($booking['so_nguoi'] ?? 1);
        $soNguoiMoi = (int)($data['so_nguoi']);
        if ($soNguoiMoi !== $soNguoiCu) {
            $coThayDoi = true;
            $thayDoiChiTiet[] = "Số người: {$soNguoiCu} → {$soNguoiMoi}";
        }
        
        // Kiểm tra thay đổi ghi chú
        $ghiChuCu = trim($booking['ghi_chu'] ?? '');
        $ghiChuMoi = trim($_POST['ghi_chu'] ?? '');
        if ($ghiChuMoi !== $ghiChuCu) {
            $coThayDoi = true;
            if (empty($ghiChuCu)) {
                $thayDoiChiTiet[] = "Ghi chú: (trống) → " . (mb_strlen($ghiChuMoi) > 50 ? mb_substr($ghiChuMoi, 0, 50) . '...' : $ghiChuMoi);
            } elseif (empty($ghiChuMoi)) {
                $thayDoiChiTiet[] = "Ghi chú: " . (mb_strlen($ghiChuCu) > 50 ? mb_substr($ghiChuCu, 0, 50) . '...' : $ghiChuCu) . " → (trống)";
            } else {
                $thayDoiChiTiet[] = "Ghi chú đã được cập nhật";
            }
        }
        
        $result = $this->bookingModel->update($id, $data);
        
        if ($result) {
            // Nếu có thay đổi, lưu lịch sử
            if ($coThayDoi) {
                // Nếu trạng thái thay đổi, dùng updateTrangThai (đã có logic lưu lịch sử)
                if ($trangThaiMoi !== $trangThaiCu) {
                    $ghiChu = 'Cập nhật thông tin booking';
                    if (!empty($thayDoiChiTiet)) {
                        $ghiChu .= ': ' . implode(', ', $thayDoiChiTiet);
                    }
                    if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
                        $ghiChu .= ' - Tiền cọc = tổng tiền, tự động chuyển thành "Hoàn tất" (Đã thanh toán đủ)';
                    }
                    $this->bookingModel->updateTrangThai($id, $trangThaiMoi, $_SESSION['user_id'] ?? null, $ghiChu);
                    
                    // Tự động phân bổ nhân sự khi booking được xác nhận (DaCoc)
                    if ($trangThaiMoi === 'DaCoc' && !empty($ngayKhoiHanh)) {
                        $lichKhoiHanh = $this->lichKhoiHanhModel->findByTourAndNgayKhoiHanh($booking['tour_id'], $ngayKhoiHanh);
                        if ($lichKhoiHanh) {
                            $this->tuDongPhanBoNhanSu($lichKhoiHanh['id'], $ngayKhoiHanh, $ngayKetThuc ?? $ngayKhoiHanh);
                        }
                    }
                } else {
                    // Trạng thái không đổi nhưng có thay đổi khác - lưu lịch sử với trạng thái giữ nguyên
                    $historyModel = new BookingHistory();
                    $ghiChu = 'Cập nhật thông tin booking: ' . implode(', ', $thayDoiChiTiet);
                    $historyModel->insert([
                        'booking_id' => $id,
                        'trang_thai_cu' => $trangThaiCu,
                        'trang_thai_moi' => $trangThaiMoi,
                        'nguoi_thay_doi_id' => $_SESSION['user_id'] ?? null,
                        'ghi_chu' => $ghiChu
                    ]);
                }
            }
            
            $thongBao = 'Cập nhật booking thành công.';
            if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
                $thongBao .= ' Trạng thái đã tự động chuyển thành "Hoàn tất" (Đã thanh toán đủ).';
            }
            $_SESSION['success'] = $thongBao;
        } else {
            $_SESSION['error'] = 'Không thể cập nhật booking.';
        }
        
        header('Location: index.php?act=booking/chiTiet&id=' . $id);
        exit();
    }

    // Xóa booking
    public function delete() {
        // requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            $_SESSION['error'] = 'ID booking không hợp lệ.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
        
        requireRole('Admin', 'admin/quanLyBooking', 'Chi Admin moi co quyen xoa booking.');
        
        // GET: Hiển thị form xác nhận mật khẩu
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
        
        // POST: Xác nhận mật khẩu và xóa
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_delete')) {
                $_SESSION['error'] = 'Phiên làm việc không hợp lệ.';
                header('Location: index.php?act=booking/delete&id=' . $id);
                exit();
            }

            $matKhau = (string)($_POST['mat_khau'] ?? '');
            $lyDoXoa = requestString('ly_do_xoa', '', 'POST');
            
            // Kiểm tra mật khẩu admin
            $adminId = $_SESSION['user_id'] ?? 0;
            $admin = $this->nguoiDungModel->findById($adminId);
            
            if (!$admin || !password_verify($matKhau, $admin['mat_khau'])) {
                $_SESSION['error'] = 'Mật khẩu không đúng.';
                header('Location: index.php?act=booking/delete&id=' . $id);
                exit();
            }
            
            // Lấy thông tin booking trước khi xóa
            $booking = $this->bookingModel->getBookingWithDetails($id);
            if (!$booking) {
                $_SESSION['error'] = 'Booking không tồn tại.';
                header('Location: index.php?act=admin/quanLyBooking');
                exit();
            }
            
            // Lưu thông tin booking vào JSON trước khi xóa
            $thongTinBooking = json_encode([
                'booking_id' => $booking['booking_id'],
                'tour_id' => $booking['tour_id'],
                'ten_tour' => $booking['ten_tour'] ?? 'N/A',
                'khach_hang_id' => $booking['khach_hang_id'],
                'ten_khach_hang' => $booking['ho_ten'] ?? 'N/A',
                'so_nguoi' => $booking['so_nguoi'] ?? 0,
                'tong_tien' => $booking['tong_tien'] ?? 0,
                'ngay_dat' => $booking['ngay_dat'] ?? null,
                'ngay_khoi_hanh' => $booking['ngay_khoi_hanh'] ?? null,
                'ngay_ket_thuc' => $booking['ngay_ket_thuc'] ?? null,
                'trang_thai' => $booking['trang_thai'] ?? 'N/A',
                'ghi_chu' => $booking['ghi_chu'] ?? null
            ], JSON_UNESCAPED_UNICODE);
            
            // Xóa booking
            $result = $this->bookingModel->delete($id);
            
            if ($result) {
                // Lưu vào lịch sử xóa
                $this->deletionHistoryModel->insert([
                    'booking_id' => $id,
                    'tour_id' => $booking['tour_id'] ?? null,
                    'khach_hang_id' => $booking['khach_hang_id'] ?? null,
                    'nguoi_xoa_id' => $adminId,
                    'ly_do_xoa' => $lyDoXoa,
                    'thong_tin_booking' => $thongTinBooking
                ]);
                
                $_SESSION['success'] = 'Xóa booking thành công.';
            } else {
                $_SESSION['error'] = 'Không thể xóa booking.';
            }
            
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }
    }

    // Ẩn booking đã hoàn tất khỏi danh sách chính (không xóa dữ liệu).
    public function hideCompleted() {
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

        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        if (($booking['trang_thai'] ?? '') !== 'HoanTat') {
            $_SESSION['error'] = 'Chỉ có thể ẩn booking đã hoàn tất.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        if ($this->bookingModel->isBookingHidden($bookingId)) {
            $_SESSION['success'] = 'Booking này đã được ẩn trước đó.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        $lyDoAn = trim((string)($_POST['ly_do_an'] ?? ''));
        $prefix = $this->bookingModel->getHideReasonPrefix();

        $thongTinBooking = json_encode([
            'booking_id' => $booking['booking_id'],
            'tour_id' => $booking['tour_id'],
            'ten_tour' => $booking['ten_tour'] ?? 'N/A',
            'khach_hang_id' => $booking['khach_hang_id'],
            'ten_khach_hang' => $booking['ho_ten'] ?? 'N/A',
            'so_nguoi' => $booking['so_nguoi'] ?? 0,
            'tong_tien' => $booking['tong_tien'] ?? 0,
            'ngay_dat' => $booking['ngay_dat'] ?? null,
            'ngay_khoi_hanh' => $booking['ngay_khoi_hanh'] ?? null,
            'ngay_ket_thuc' => $booking['ngay_ket_thuc'] ?? null,
            'trang_thai' => $booking['trang_thai'] ?? 'N/A',
            'ghi_chu' => $booking['ghi_chu'] ?? null,
        ], JSON_UNESCAPED_UNICODE);

        $historySaved = $this->deletionHistoryModel->insert([
            'booking_id' => $bookingId,
            'tour_id' => $booking['tour_id'] ?? null,
            'khach_hang_id' => $booking['khach_hang_id'] ?? null,
            'nguoi_xoa_id' => (int)($_SESSION['user_id'] ?? 0),
            'ly_do_xoa' => $prefix . ($lyDoAn !== '' ? (' ' . $lyDoAn) : ' Ẩn booking đã hoàn tất khỏi danh sách chính'),
            'thong_tin_booking' => $thongTinBooking,
        ]);

        if ($historySaved) {
            $_SESSION['success'] = 'Đã ẩn booking hoàn tất. Bạn có thể xem lại trong mục "Booking đã hoàn thành".';
        } else {
            $_SESSION['error'] = 'Không thể ẩn booking. Vui lòng thử lại.';
        }

        header('Location: index.php?act=admin/quanLyBooking');
        exit();
    }

    // Kiểm tra quyền cập nhật
    private function checkPermissionToUpdate($bookingId) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Admin có quyền cập nhật tất cả
        if (hasRole('Admin')) {
            return true;
        }
        
        // HDV có thể cập nhật booking của tour mình phụ trách
        if (hasRole('HDV')) {
            // Có thể thêm logic kiểm tra tour do HDV phụ trách
            return true; // Tạm thời cho phép
        }
        
        return false;
    }

    // Kiểm tra quyền xem
    private function checkPermissionToView($booking) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Admin và HDV có quyền xem tất cả
        if (hasRole(['Admin', 'HDV'])) {
            return true;
        }
        
        // Khách hàng chỉ xem được booking của mình
        if (hasRole('KhachHang')) {
            $khachHangId = $_SESSION['khach_hang_id'] ?? null;
            if (!$khachHangId && isset($_SESSION['user_id'])) {
                $khachHang = $this->khachHangModel->findByNguoiDungId($_SESSION['user_id']);
                if ($khachHang) {
                    $khachHangId = $khachHang['khach_hang_id'];
                }
            }
            return $khachHangId && $booking['khach_hang_id'] == $khachHangId;
        }
        
        return false;
    }

    // Nhân viên đặt tour cho khách hàng
    public function datTourChoKhach() {
        requireRole('Admin', 'admin/quanLyBooking');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!verifyCsrfToken($_POST['_csrf_token'] ?? '', 'booking_staff_create')) {
                    throw new Exception('Phiên làm việc không hợp lệ.');
                }

                // Lấy thông tin từ form
                $tourId = validateId($_POST['tour_id'] ?? null) ?? 0;
                $hoTen = requestString('ho_ten', '', 'POST');
                $email = validateEmail($_POST['email'] ?? '');
                $soDienThoai = validatePhone($_POST['so_dien_thoai'] ?? '');
                $diaChi = requestString('dia_chi', '', 'POST');
                $gioiTinh = requestString('gioi_tinh', '', 'POST');
                $ngaySinh = validateDateYmd($_POST['ngay_sinh'] ?? '') ?? null;
                $soNguoi = validateId($_POST['so_nguoi'] ?? 1) ?? 1;
                $ngayKhoiHanh = validateDateYmd($_POST['ngay_khoi_hanh'] ?? '') ?? '';
                $ngayKetThucForm = validateDateYmd($_POST['ngay_ket_thuc'] ?? '') ?? $ngayKhoiHanh;
                $loaiKhach = requestString('loai_khach', 'le', 'POST'); // le hoặc doan
                $tenCongTy = requestString('ten_cong_ty', '', 'POST');
                $ghiChu = requestString('ghi_chu', '', 'POST');
                $yeuCauDacBiet = requestString('yeu_cau_dac_biet', '', 'POST');

                if (!in_array($gioiTinh, ['', 'Nam', 'Nu', 'Khac'], true)) {
                    $gioiTinh = null;
                }

                if (!in_array($loaiKhach, ['le', 'doan'], true)) {
                    $loaiKhach = 'le';
                }

                // Validation
                if (empty($hoTen)) {
                    throw new Exception('Vui lòng nhập tên khách hàng.');
                }
                if (empty($email) && empty($soDienThoai)) {
                    throw new Exception('Vui lòng nhập email hoặc số điện thoại.');
                }
                if (!empty($_POST['email']) && $email === null) {
                    throw new Exception('Email không hợp lệ.');
                }
                if (!empty($_POST['so_dien_thoai']) && $soDienThoai === null) {
                    throw new Exception('Số điện thoại không hợp lệ.');
                }
                // Ngày sinh: nếu nhập thì phải đủ 18 tuổi trở lên
                if (!empty($_POST['ngay_sinh'])) {
                    if ($ngaySinh === null) {
                        throw new Exception('Ngày sinh không hợp lệ.');
                    }
                    $dob = DateTime::createFromFormat('Y-m-d', $ngaySinh);
                    if (!$dob) {
                        throw new Exception('Ngày sinh không hợp lệ.');
                    }
                    $today = new DateTime('today');
                    $age = $dob->diff($today)->y;
                    if ($age < 18) {
                        throw new Exception('Người đặt tour phải từ 18 tuổi trở lên và có thể chịu trách nhiệm pháp lý.');
                    }
                }
                if ($tourId <= 0) {
                    throw new Exception('Vui lòng chọn tour.');
                }
                if (empty($ngayKhoiHanh)) {
                    throw new Exception('Vui lòng chọn ngày khởi hành.');
                }
                if ($soNguoi <= 0) {
                    throw new Exception('Số lượng người phải lớn hơn 0.');
                }

                // Kiểm tra tour tồn tại
                $tour = $this->tourModel->findById($tourId);
                if (!$tour) {
                    throw new Exception('Tour không tồn tại.');
                }

                // Kiểm tra chỗ trống
                $kiemTraCho = $this->bookingModel->kiemTraChoTrong($tourId, $ngayKhoiHanh, $soNguoi);
                if (!$kiemTraCho['co_cho']) {
                    throw new Exception("Không đủ chỗ trống. Chỉ còn {$kiemTraCho['cho_trong']} chỗ trống.");
                }

                // Tìm hoặc tạo người dùng
                $nguoiDung = $this->nguoiDungModel->findOrCreate($hoTen, $email, $soDienThoai, 'KhachHang');
                if (!$nguoiDung) {
                    throw new Exception('Không thể tạo tài khoản khách hàng.');
                }

                // Tìm hoặc tạo khách hàng
                $khachHang = $this->khachHangModel->findOrCreateByNguoiDungInfo(
                    $nguoiDung['id'],
                    $diaChi,
                    $gioiTinh,
                    $ngaySinh
                );
                if (!$khachHang) {
                    throw new Exception('Không thể tạo thông tin khách hàng.');
                }

                // Tính tổng tiền
                $giaCoBan = (float)($tour['gia_co_ban'] ?? 0);
                $tongTien = $giaCoBan * $soNguoi;

                // Tạo booking
                $bookingData = [
                    'tour_id' => $tourId,
                    'khach_hang_id' => $khachHang['khach_hang_id'],
                    'ngay_dat' => date('Y-m-d'),
                    'ngay_khoi_hanh' => $ngayKhoiHanh,
                    'ngay_ket_thuc' => $ngayKetThucForm,
                    'so_nguoi' => $soNguoi,
                    'tong_tien' => $tongTien,
                    'trang_thai' => 'ChoXacNhan',
                    'ghi_chu' => $ghiChu . ($loaiKhach === 'doan' && !empty($tenCongTy) ? " | Công ty/Tổ chức: {$tenCongTy}" : '')
                ];

                $bookingId = $this->bookingModel->insert($bookingData);
                if (!$bookingId) {
                    throw new Exception('Không thể tạo booking.');
                }

                // Tự động tạo lịch khởi hành nếu chưa có
                if (!empty($ngayKhoiHanh) && !empty($tourId)) {
                    $ngayKetThuc = $ngayKetThucForm;
                    $lichKhoiHanh = $this->lichKhoiHanhModel->findByTourAndNgayKhoiHanh($tourId, $ngayKhoiHanh);
                    if (!$lichKhoiHanh) {
                        // Tạo lịch khởi hành mới
                        $lichKhoiHanhData = [
                            'tour_id' => $tourId, // Đảm bảo tour_id luôn có giá trị
                            'ngay_khoi_hanh' => $ngayKhoiHanh,
                            'ngay_ket_thuc' => $ngayKetThuc,
                            'gio_xuat_phat' => null,
                            'gio_ket_thuc' => null,
                            'diem_tap_trung' => '',
                            'so_cho' => 50, // Mặc định
                            'hdv_id' => null,
                            'trang_thai' => 'SapKhoiHanh',
                            'ghi_chu' => 'Tạo tự động từ booking #' . $bookingId
                        ];
                        $this->lichKhoiHanhModel->insert($lichKhoiHanhData);
                    }
                }

                // Lưu yêu cầu đặc biệt nếu có
                if (!empty($yeuCauDacBiet)) {
                    $this->tourModel->insertYeuCauDacBiet($bookingId, $yeuCauDacBiet);
                }

                $_SESSION['success'] = "Đặt tour thành công! Mã booking: #{$bookingId}";
                header("Location: index.php?act=booking/datTourChoKhach&success=1&booking_id={$bookingId}");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                setValidationErrors(['general' => $e->getMessage()], 'Thong tin dat tour khong hop le.');
                // Giữ lại dữ liệu form để hiển thị lại
                $_SESSION['form_data'] = $_POST;
                header('Location: index.php?act=booking/datTourChoKhach');
                exit();
            }
        } else {
            // Hiển thị form
            $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : null;
            $tours = $this->tourModel->getAll();
            $tour = $tourId ? $this->tourModel->findById($tourId) : null;
            
            // Lấy lịch khởi hành nếu có tour
            $lichKhoiHanhList = [];
            if ($tour) {
                $lichKhoiHanhList = $this->tourModel->getLichKhoiHanhByTourId($tourId);
            }
            
            // Lấy dữ liệu form từ session nếu có (khi có lỗi)
            $formData = $_SESSION['form_data'] ?? [];
            unset($_SESSION['form_data']);
            
            require 'views/admin/dat_tour_cho_khach.php';
        }
    }

    // API: Kiểm tra chỗ trống (AJAX)
    public function kiemTraChoTrong() {
        header('Content-Type: application/json');
        
        $tourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0;
        $ngayKhoiHanh = $_GET['ngay_khoi_hanh'] ?? '';
        $soNguoi = isset($_GET['so_nguoi']) ? (int)$_GET['so_nguoi'] : 1;
        
        if ($tourId <= 0 || empty($ngayKhoiHanh)) {
            echo json_encode(['error' => 'Thiếu thông tin']);
            exit();
        }
        
        $result = $this->bookingModel->kiemTraChoTrong($tourId, $ngayKhoiHanh, $soNguoi);
        echo json_encode($result);
        exit();
    }

    // Trang xuất tài liệu (báo giá, hợp đồng, hóa đơn)
    public function xuatTaiLieu() {
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
        
        // Đảm bảo chỉ truyền một booking duy nhất vào template
        if (isset($booking[0]) && is_array($booking[0])) {
            $booking = $booking[0];
        }
        
        require 'views/admin/xuat_tai_lieu_booking.php';
    }

    // Xuất file PDF
    public function exportPDF() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $type = $_GET['type'] ?? 'bao-gia'; // bao-gia, hop-dong, hoa-don
        
        if ($id <= 0) {
            die('ID không hợp lệ');
        }
        
        $booking = $this->bookingModel->getBookingWithDetails($id);
        if (!$booking) {
            die('Booking không tồn tại');
        }
        
        // Đảm bảo chỉ truyền một booking duy nhất vào template
        if (isset($booking[0]) && is_array($booking[0])) {
            $booking = $booking[0];
        }
        ob_start();
        switch ($type) {
            case 'hop-dong':
                include 'views/admin/templates/hop_dong_template.php';
                $filename = 'Hop_Dong_' . $booking['booking_id'] . '.pdf';
                break;
            case 'hoa-don':
                include 'views/admin/templates/hoa_don_template.php';
                $filename = 'Hoa_Don_' . $booking['booking_id'] . '.pdf';
                break;
            default:
                include 'views/admin/templates/bao_gia_template.php';
                $filename = 'Bao_Gia_' . $booking['booking_id'] . '.pdf';
        }
        $html = ob_get_clean();
        $this->generateSimplePDF($html, $filename);
    }

    // Generate PDF đơn giản (có thể thay bằng dompdf)
    private function generateSimplePDF($html, $filename) {
        // Cách 1: Sử dụng mPDF hoặc dompdf (cần cài qua composer)
        // require_once 'vendor/autoload.php';
        // $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        // $mpdf->WriteHTML($html);
        // $mpdf->Output($filename, 'D'); // D = download
        
        // Cách 2: Tạm thời xuất HTML để xem trước và in (fallback)
        header('Content-Type: text/html; charset=UTF-8');
        
        // Chuyển HTML sang PDF đơn giản
        echo $this->convertHTMLtoPDF($html, $filename);
    }

    // Convert HTML to PDF đơn giản
    private function convertHTMLtoPDF($html, $filename) {
        // Thêm CSS cho print và auto print
        $pdfHTML = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $filename . '</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12pt; margin: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .info-table td { border: 1px solid #dee2e6; padding: 8px; }
                .info-table td:first-child { font-weight: 600; background: #f8f9fa; width: 30%; }
                .detail-table { border: 1px solid #000; }
                .detail-table th, .detail-table td { border: 1px solid #000; padding: 8px; }
                .detail-table thead { background: #f8f9fa; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-muted { color: #6c757d; }
                .company-header { text-align: center; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px; }
                .document-title { text-align: center; font-size: 1.75rem; font-weight: bold; margin: 20px 0; text-transform: uppercase; }
                .total-section { border-top: 2px solid #000; padding-top: 15px; margin-top: 20px; }
                .signature-section { margin-top: 40px; }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>' . $html . '
            <div class="no-print text-center mt-4">
                <button onclick="window.print()" class="btn btn-primary btn-lg">
                    <i class="bi bi-printer"></i> In tài liệu
                </button>
                <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">
                    <i class="bi bi-x-circle"></i> Đóng
                </button>
            </div>
        </body>
        </html>';
        
        return $pdfHTML;
    }

    // Gửi email
    public function sendEmail() {
        header('Content-Type: application/json');
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
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
        
        // Tạo nội dung email
        ob_start();
        switch ($type) {
            case 'hop-dong':
                include 'views/admin/templates/hop_dong_template.php';
                $subject = 'Hợp đồng dịch vụ du lịch - Booking #' . $booking['booking_id'];
                break;
            case 'hoa-don':
                include 'views/admin/templates/hoa_don_template.php';
                $subject = 'Hóa đơn thanh toán - Booking #' . $booking['booking_id'];
                break;
            default:
                include 'views/admin/templates/bao_gia_template.php';
                $subject = 'Báo giá tour du lịch - Booking #' . $booking['booking_id'];
        }
        $htmlContent = ob_get_clean();
        
        // Gửi email
        $result = $this->sendHTMLEmail($email, $subject, $htmlContent, $booking['ho_ten']);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã gửi email thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể gửi email']);
        }
        exit();
    }

    // Function gửi HTML email
    private function sendHTMLEmail($to, $subject, $htmlContent, $toName = '') {
        $message = '
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 800px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { background: #fff; padding: 20px; }
                .footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Công ty Du lịch ABC</h2>
                    <p>Cảm ơn quý khách đã tin tưởng sử dụng dịch vụ</p>
                </div>
                <div class="content">
                    <p>Kính gửi: <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Chúng tôi xin gửi đến quý khách tài liệu đính kèm.</p>
                    <hr>
                    ' . $htmlContent . '
                    <hr>
                    <p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ:</p>
                    <ul>
                        <li>Hotline: 1900 xxxx</li>
                        <li>Email: info@dulichabc.vn</li>
                        <li>Website: www.dulichabc.vn</li>
                    </ul>
                </div>
                <div class="footer">
                    <p>© 2025 Công ty Du lịch ABC. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';

        return sendHtmlEmail($to, $subject, $message, '', [
            'from_name' => 'Công ty Du lịch ABC',
        ]);
    }

    /**
     * Tự động phân bổ nhân sự (HDV) cho lịch khởi hành nếu chưa có
     * @param int $lichKhoiHanhId ID lịch khởi hành
     * @param string $ngayKhoiHanh Ngày khởi hành (Y-m-d)
     * @param string $ngayKetThuc Ngày kết thúc (Y-m-d)
     * @return bool True nếu phân bổ thành công hoặc đã có HDV, False nếu thất bại
     */
    private function tuDongPhanBoNhanSu($lichKhoiHanhId, $ngayKhoiHanh, $ngayKetThuc) {
        try {
            require_once 'models/LichKhoiHanh.php';
            require_once 'models/PhanBoNhanSu.php';
            require_once 'models/NhanSu.php';
            require_once 'models/HDV.php';
            
            $lichKhoiHanhModel = new LichKhoiHanh();
            $phanBoNhanSuModel = new PhanBoNhanSu();
            $nhanSuModel = new NhanSu();
            $hdvModel = new HDV();
            
            // Lấy thông tin lịch khởi hành
            $lichKhoiHanh = $lichKhoiHanhModel->findById($lichKhoiHanhId);
            if (!$lichKhoiHanh) {
                return false;
            }
            
            // Kiểm tra xem đã có phân bổ nhân sự (HDV) chưa
            $phanBoHienTai = $phanBoNhanSuModel->getByVaiTro($lichKhoiHanhId, 'HDV');
            if (!empty($phanBoHienTai)) {
                // Đã có HDV được phân bổ, không cần làm gì
                return true;
            }
            
            // Kiểm tra xem lịch khởi hành đã có hdv_id chưa
            if (!empty($lichKhoiHanh['hdv_id'])) {
                // Đã có HDV chính, thêm vào bảng phan_bo_nhan_su
                $phanBoData = [
                    'lich_khoi_hanh_id' => $lichKhoiHanhId,
                    'nhan_su_id' => $lichKhoiHanh['hdv_id'],
                    'vai_tro' => 'HDV',
                    'ghi_chu' => 'Tự động phân bổ từ hdv_id',
                    'trang_thai' => 'ChoXacNhan'
                ];
                return $phanBoNhanSuModel->insert($phanBoData) !== false;
            }
            
            // Chưa có HDV, tìm HDV rảnh
            if (empty($ngayKhoiHanh) || empty($ngayKetThuc)) {
                return false;
            }
            
            // Lấy danh sách HDV
            $hdvList = $nhanSuModel->getByRole('HDV');
            if (empty($hdvList)) {
                return false; // Không có HDV nào
            }
            
            // Tạo startTime và endTime để kiểm tra rảnh
            $startTime = $ngayKhoiHanh . ' 00:00:00';
            $endTime = $ngayKetThuc . ' 23:59:59';
            
            // Tìm HDV rảnh (ưu tiên HDV ít tour nhất)
            $hdvRanh = null;
            $minTours = PHP_INT_MAX;
            
            foreach ($hdvList as $hdv) {
                if (empty($hdv['nhan_su_id'])) {
                    continue;
                }
                
                // Kiểm tra HDV có rảnh không
                if ($hdvModel->isAvailable($hdv['nhan_su_id'], $startTime, $endTime)) {
                    // Đếm số tour đang có (để ưu tiên HDV ít tour nhất)
                    $schedules = $hdvModel->getSchedule($hdv['nhan_su_id']);
                    $soTour = count($schedules);
                    
                    if ($soTour < $minTours) {
                        $minTours = $soTour;
                        $hdvRanh = $hdv;
                    }
                }
            }
            
            // Nếu tìm thấy HDV rảnh, phân bổ
            if ($hdvRanh && !empty($hdvRanh['nhan_su_id'])) {
                // Cập nhật hdv_id vào lịch khởi hành
                $lichKhoiHanhModel->assignHDV($lichKhoiHanhId, $hdvRanh['nhan_su_id']);
                
                // Thêm vào bảng phan_bo_nhan_su
                $phanBoData = [
                    'lich_khoi_hanh_id' => $lichKhoiHanhId,
                    'nhan_su_id' => $hdvRanh['nhan_su_id'],
                    'vai_tro' => 'HDV',
                    'ghi_chu' => 'Tự động phân bổ - HDV rảnh',
                    'trang_thai' => 'ChoXacNhan'
                ];
                
                $result = $phanBoNhanSuModel->insert($phanBoData);
                
                // Thêm vào lịch làm việc của HDV
                if ($result) {
                    $hdvModel->addSchedule(
                        $hdvRanh['nhan_su_id'],
                        $lichKhoiHanh['tour_id'] ?? null,
                        $startTime,
                        $endTime,
                        'Tự động phân bổ từ booking'
                    );
                }
                
                return $result !== false;
            }
            
            return false; // Không tìm thấy HDV rảnh
        } catch (Exception $e) {
            // Log error nếu cần
            error_log("Lỗi tự động phân bổ nhân sự: " . $e->getMessage());
            return false;
        }
    }

}