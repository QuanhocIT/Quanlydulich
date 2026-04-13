<?php

class KhachHangController {
    
    public function __construct() {
        requireRole('KhachHang');
    }

    // Gửi yêu cầu tour theo mong muốn
    public function guiYeuCauTour() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjaxRequest = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
            require_once 'models/ThongBao.php';
            require_once 'models/NguoiDung.php';
            $thongBaoModel = new ThongBao();
            $nguoiDungModel = new NguoiDung();
            $user = $nguoiDungModel->findById($_SESSION['user_id']);

            // Ho tro nhieu mau form (tour request / booking template) nhung luu thong nhat theo field yeu cau tour.
            $diaDiem = trim((string)($_POST['dia_diem'] ?? $_POST['destination'] ?? $_POST['location'] ?? $_POST['room_type'] ?? ''));
            $thoiGian = trim((string)($_POST['thoi_gian'] ?? ''));
            if ($thoiGian === '') {
                $arrival = trim((string)($_POST['arrival_date'] ?? $_POST['checkin_date'] ?? ''));
                $departure = trim((string)($_POST['departure_date'] ?? $_POST['checkout_date'] ?? ''));
                if ($arrival !== '' || $departure !== '') {
                    $thoiGian = trim($arrival . ($arrival !== '' && $departure !== '' ? ' - ' : '') . $departure);
                }
            }
            $soNguoi = trim((string)($_POST['so_nguoi'] ?? $_POST['guests'] ?? $_POST['so_luong'] ?? ''));
            $yeuCauDacBiet = trim((string)($_POST['yeu_cau_dac_biet'] ?? $_POST['special_request'] ?? $_POST['note'] ?? ''));

            $noi_dung =
                'Tên: ' . ($user['ho_ten'] ?? '') . "\n" .
                'Email: ' . ($user['email'] ?? '') . "\n" .
                'Số điện thoại: ' . ($user['so_dien_thoai'] ?? '') . "\n" .
                'Địa điểm: ' . $diaDiem . "\n" .
                'Thời gian: ' . $thoiGian . "\n" .
                'Số người: ' . $soNguoi . "\n" .
                'Yêu cầu đặc biệt: ' . $yeuCauDacBiet;
            $data = [
                'tieu_de' => 'Yêu cầu tour theo mong muốn',
                'noi_dung' => $noi_dung,
                'loai_thong_bao' => 'KhachHang',
                'muc_do_uu_tien' => 'TrungBinh',
                'nguoi_gui_id' => $_SESSION['user_id'],
                'vai_tro_nhan' => 'Admin',
                'trang_thai' => 'DaGui'
            ];
            $createdId = (int)$thongBaoModel->insert($data);

            if ($isAjaxRequest) {
                header('Content-Type: application/json; charset=utf-8');
                if ($createdId > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Yêu cầu của bạn đã được gửi đến admin. Chúng tôi sẽ liên hệ lại sớm nhất!'
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể gửi yêu cầu lúc này. Vui lòng thử lại sau.'
                    ], JSON_UNESCAPED_UNICODE);
                }
                exit();
            }

            $_SESSION['success'] = $createdId > 0
                ? 'Yêu cầu của bạn đã được gửi đến admin. Chúng tôi sẽ liên hệ lại sớm nhất!'
                : 'Không thể gửi yêu cầu lúc này. Vui lòng thử lại sau.';
            $redirectTo = trim((string)($_POST['redirect_to'] ?? ''));
            if ($redirectTo === '' || strpos($redirectTo, 'index.php?act=') !== 0) {
                $redirectTo = 'index.php?act=khachHang/guiYeuCauTour';
            }

            header('Location: ' . $redirectTo);
            exit();
        }
        require_once 'models/KhachHang.php';
        require_once 'models/Booking.php';
        require_once 'models/CheckinKhach.php';
        require_once 'models/Tour.php';

        $khachHangModel = new KhachHang();
        $bookingModel = new Booking();
        $checkinModel = new CheckinKhach();
        $tourModel = new Tour();

        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        $bookings = [];
        $participantsByBooking = [];
        $upcomingReminders = [];

        if ($khachHang && !empty($khachHang['khach_hang_id'])) {
            $bookings = $bookingModel->getByKhachHangId((int)$khachHang['khach_hang_id']);

            $tourIds = [];
            $bookingIds = [];
            foreach ($bookings as $booking) {
                $tourId = (int)($booking['tour_id'] ?? 0);
                $bookingId = (int)($booking['booking_id'] ?? 0);
                if ($tourId > 0) {
                    $tourIds[$tourId] = $tourId;
                }
                if ($bookingId > 0) {
                    $bookingIds[$bookingId] = $bookingId;
                }
            }

            if (!empty($tourIds)) {
                $thumbnailMap = $tourModel->getThumbnailMapByTourIds(array_values($tourIds));
                foreach ($bookings as &$booking) {
                    $tourId = (int)($booking['tour_id'] ?? 0);
                    $booking['hinh_anh'] = $thumbnailMap[$tourId] ?? null;
                }
                unset($booking);
            }

            if (!empty($bookingIds)) {
                $participantsByBooking = $checkinModel->getByBookingIdsGrouped(array_values($bookingIds));
            }

            $nowTs = time();
            foreach ($bookings as $booking) {
                $ngayKhoiHanh = trim((string)($booking['ngay_khoi_hanh'] ?? ''));
                if ($ngayKhoiHanh === '') {
                    continue;
                }

                $departureTs = strtotime($ngayKhoiHanh . ' 00:00:00');
                if ($departureTs === false || $departureTs < $nowTs) {
                    continue;
                }

                $daysLeft = (int)ceil(($departureTs - $nowTs) / 86400);
                $status = strtoupper(trim((string)($booking['trang_thai'] ?? '')));
                if (in_array($status, ['HUY', 'DAHUY', 'TUCHOI'], true)) {
                    continue;
                }

                $booking['days_left'] = $daysLeft;
                $booking['is_urgent'] = ($daysLeft <= 3);
                $upcomingReminders[] = $booking;
            }

            usort($upcomingReminders, static function ($a, $b) {
                $aDate = (string)($a['ngay_khoi_hanh'] ?? '');
                $bDate = (string)($b['ngay_khoi_hanh'] ?? '');
                return strcmp($aDate, $bDate);
            });
        }

        require 'views/khach_hang/yeu_cau_tour.php';
    }

    public function notificationCounts() {
        header('Content-Type: application/json; charset=utf-8');

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'unread' => 0], JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            require_once 'models/ThongBao.php';
            $thongBaoModel = new ThongBao();
            $unread = (int)$thongBaoModel->countChuaDoc($userId);

            echo json_encode([
                'success' => true,
                'unread' => $unread,
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'unread' => 0,
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
    
    // Dashboard khách hàng
    public function dashboard() {
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';
        require_once 'models/ThongBao.php';
        require_once 'models/Tour.php';
        require_once 'models/DanhGia.php';

        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();
        $thongBaoModel = new ThongBao();
        $tourModel = new Tour();
        $danhGiaModel = new DanhGia();

        // Lấy thông tin khách hàng
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng';
            header('Location: index.php?act=auth/profile');
            exit();
        }

        // Lấy booking của khách hàng
        $bookings = $bookingModel->getByKhachHangId($khachHang['khach_hang_id']);

        // Lấy thông báo chưa đọc
        $thongBaoChuaDoc = $thongBaoModel->countChuaDoc($_SESSION['user_id']);
        $thongBaoList = $thongBaoModel->getByNguoiDung($_SESSION['user_id'], 5);

        // Lấy tour sắp tới (booking có ngày khởi hành >= hôm nay)
        $tourSapToi = [];
        $today = date('Y-m-d');
        foreach ($bookings as $booking) {
            if (!empty($booking['ngay_khoi_hanh']) && $booking['ngay_khoi_hanh'] >= $today && 
                in_array($booking['trang_thai'], ['ChoXacNhan', 'DaCoc', 'HoanTat'])) {
                $tourSapToi[] = $booking;
            }
        }

        // Thống kê (chỉ tính booking hợp lệ đã đi vào quy trình xử lý).
        $bookingsHopLe = array_filter($bookings, static function ($b) {
            return in_array((string)($b['trang_thai'] ?? ''), ['ChoXacNhan', 'DaCoc', 'HoanTat'], true);
        });
        $tongBooking = count($bookingsHopLe);
        $bookingChoXacNhan = count(array_filter($bookings, fn($b) => $b['trang_thai'] === 'ChoXacNhan'));
        $bookingDaCoc = count(array_filter($bookings, fn($b) => $b['trang_thai'] === 'DaCoc'));
        $bookingHoanTat = count(array_filter($bookings, fn($b) => $b['trang_thai'] === 'HoanTat'));

        // Lấy 3 đánh giá tốt nhất (điểm cao nhất, mới nhất)
        $danhGiaTot = $danhGiaModel->getTopReviews(4, 3);

        // Lấy danh sách tour và lọc theo tên, loại tour nếu có
        $searchKeyword = trim((string)($_GET['search'] ?? ''));
        $allTours = $tourModel->getPublicTours(['search' => $searchKeyword], 24, 0);
        // Lọc theo loại tour nếu có
        if (!empty($_GET['loai_tour'])) {
            $allTours = array_filter($allTours, function($tour) {
                return $tour['loai_tour'] === $_GET['loai_tour'];
            });
        }
        // Lọc theo tên tour nếu có
        if (!empty($_GET['search'])) {
            $search = mb_strtolower(trim($_GET['search']));
            $allTours = array_filter($allTours, function($tour) use ($search) {
                return mb_strpos(mb_strtolower($tour['ten_tour']), $search) !== false;
            });
        }
        // Gán hình ảnh đại diện cho mỗi tour bằng một truy vấn duy nhất.
        $tourIds = array_map(static function ($tour) {
            return (int)($tour['tour_id'] ?? 0);
        }, $allTours);
        $thumbnailMap = $tourModel->getThumbnailMapByTourIds($tourIds);
        $ratingMap = $danhGiaModel->getTourRatingMapByTourIds($tourIds);
        foreach ($allTours as &$tour) {
            $tourId = (int)($tour['tour_id'] ?? 0);
            $tour['hinh_anh'] = $thumbnailMap[$tourId] ?? null;
            $tour['rating'] = $ratingMap[$tourId] ?? ['diem_tb' => 0, 'so_danh_gia' => 0];

            $lichKhoiHanhList = $tourId > 0 ? $tourModel->getLichKhoiHanhByTourId($tourId) : [];
            $nextSchedule = null;
            foreach ($lichKhoiHanhList as $lichKhoiHanh) {
                if (empty($lichKhoiHanh['ngay_khoi_hanh'])) {
                    continue;
                }
                if (strtotime((string)$lichKhoiHanh['ngay_khoi_hanh']) >= strtotime(date('Y-m-d'))) {
                    $nextSchedule = $lichKhoiHanh;
                    break;
                }
            }
            if ($nextSchedule === null && !empty($lichKhoiHanhList)) {
                $nextSchedule = $lichKhoiHanhList[0];
            }

            $tour['ngay_khoi_hanh_gan_nhat'] = $nextSchedule['ngay_khoi_hanh'] ?? null;
            $tour['diem_tap_trung'] = $nextSchedule['diem_tap_trung'] ?? '';
            $tour['so_cho'] = $nextSchedule['so_cho'] ?? null;
        }
        unset($tour);
        $tourTrongNuoc = array_filter($allTours, fn($t) => $t['loai_tour'] === 'TrongNuoc' && $t['trang_thai'] === 'HoatDong');
        $tourQuocTe = array_filter($allTours, fn($t) => $t['loai_tour'] === 'QuocTe' && $t['trang_thai'] === 'HoatDong');

        // Tạo danh sách địa danh cho mục grid
        $danhSachDiaDanh = [];
        foreach ($allTours as $tour) {
            if (!empty($tour['hinh_anh']) && !empty($tour['ten_tour'])) {
                $danhSachDiaDanh[] = [
                    'ten' => $tour['ten_tour'],
                    'hinh_anh' => $tour['hinh_anh']
                ];
            }
        }

        require 'views/khach_hang/dashboard.php';
    }
    
    // Tra cứu bằng mã tour và mã khách hàng (ID)
    public function traCuu() {
        
        require 'views/khach_hang/tra_cuu.php';
    }
    
    public function danhSachTour() {
        require_once 'models/Tour.php';
        require_once 'models/DanhGia.php';

        $tourModel = new Tour();
        $danhGiaModel = new DanhGia();
        $search = trim((string)($_GET['q'] ?? ''));
        $loaiTour = trim((string)($_GET['loai_tour'] ?? ''));
        $priceRange = trim((string)($_GET['price_range'] ?? ''));
        $sort = trim((string)($_GET['sort'] ?? 'newest'));

        $allowedLoaiTour = ['TrongNuoc', 'QuocTe'];
        if (!in_array($loaiTour, $allowedLoaiTour, true)) {
            $loaiTour = '';
        }

        $tours = $tourModel->getPublicTours([
            'search' => $search,
            'loai_tour' => $loaiTour,
        ], 80, 0);

        $tourIds = array_column($tours, 'tour_id');
        $tourRatingMap = $danhGiaModel->getTourRatingMapByTourIds($tourIds);

        $thumbnailMap = $tourModel->getThumbnailMapByTourIds($tourIds);
        foreach ($tours as &$tour) {
            $tourId = (int)($tour['tour_id'] ?? 0);
            $tour['hinh_anh'] = $thumbnailMap[$tourId] ?? null;
            $tour['diem_tb'] = (float)($tourRatingMap[$tourId]['diem_tb'] ?? 0);
            $tour['so_danh_gia'] = (int)($tourRatingMap[$tourId]['so_danh_gia'] ?? 0);

            $lichKhoiHanhList = $tourId > 0 ? $tourModel->getLichKhoiHanhByTourId($tourId) : [];
            $nextSchedule = null;
            foreach ($lichKhoiHanhList as $lichKhoiHanh) {
                if (empty($lichKhoiHanh['ngay_khoi_hanh'])) {
                    continue;
                }
                if (strtotime((string)$lichKhoiHanh['ngay_khoi_hanh']) >= strtotime(date('Y-m-d'))) {
                    $nextSchedule = $lichKhoiHanh;
                    break;
                }
            }
            if ($nextSchedule === null && !empty($lichKhoiHanhList)) {
                $nextSchedule = $lichKhoiHanhList[0];
            }

            $tour['ngay_khoi_hanh_gan_nhat'] = $nextSchedule['ngay_khoi_hanh'] ?? null;
            $tour['ngay_ket_thuc_gan_nhat'] = $nextSchedule['ngay_ket_thuc'] ?? null;
            $tour['diem_tap_trung'] = $nextSchedule['diem_tap_trung'] ?? '';
            $tour['so_cho'] = $nextSchedule['so_cho'] ?? null;
        }
        unset($tour);

        $tours = array_values(array_filter($tours, static function ($tour) use ($priceRange) {
            $price = (float)($tour['gia_co_ban'] ?? 0);
            return match ($priceRange) {
                'under5' => $price < 5000000,
                '5to10' => $price >= 5000000 && $price < 10000000,
                '10to20' => $price >= 10000000 && $price < 20000000,
                'over20' => $price >= 20000000,
                default => true,
            };
        }));

        usort($tours, static function ($a, $b) use ($sort) {
            return match ($sort) {
                'price_asc' => ((float)($a['gia_co_ban'] ?? 0)) <=> ((float)($b['gia_co_ban'] ?? 0)),
                'price_desc' => ((float)($b['gia_co_ban'] ?? 0)) <=> ((float)($a['gia_co_ban'] ?? 0)),
                'upcoming' => strtotime((string)($a['ngay_khoi_hanh_gan_nhat'] ?? '2999-12-31')) <=> strtotime((string)($b['ngay_khoi_hanh_gan_nhat'] ?? '2999-12-31')),
                default => ((int)($b['tour_id'] ?? 0)) <=> ((int)($a['tour_id'] ?? 0)),
            };
        });

        $filters = [
            'q' => $search,
            'loai_tour' => $loaiTour,
            'price_range' => $priceRange,
            'sort' => $sort,
        ];

        require 'views/khach_hang/danh_sach_tour.php';
    }
    
    public function chiTietTour() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $tour = null;
        $lichTrinhList = [];
        $lichKhoiHanhList = [];
        $hinhAnhList = [];
        $yeuCauList = [];
        $nhatKyList = [];
        $hdvInfo = null;
        $anhChinh = null;
        $tourCungLoai = [];
        $danhGiaTourList = [];
        $danhGiaTourAvg = 0;
        $danhGiaTourCount = 0;
        $daDatTourNay = false;
        $daTraiNghiemTourNay = false;
        $daDanhGiaTourNay = false;
        $coTheDanhGiaTour = false;
        $tourReviewCurrentUser = null;
        $error = null;

        if ($id <= 0) {
            $error = 'Thiếu mã tour cần xem chi tiết.';
        } else {
            require_once 'models/Tour.php';
            $tourModel = new Tour();
            $tour = $tourModel->findById($id);
            if (!$tour) {
                $error = 'Tour không tồn tại hoặc đã bị xóa.';
            } else {
                $lichTrinhList = $tourModel->getLichTrinhByTourId($id);
                $lichKhoiHanhList = $tourModel->getLichKhoiHanhByTourId($id);
                require_once 'models/Booking.php';
                $bookingModelForSeats = new Booking();
                $singleSchedule = count($lichKhoiHanhList) === 1;
                foreach ($lichKhoiHanhList as &$lk) {
                    $ngayKhoiHanh = (string)($lk['ngay_khoi_hanh'] ?? '');
                    $tongDaDat = $bookingModelForSeats->getSoNguoiDaDatTheoLich(
                        $id,
                        $ngayKhoiHanh,
                        $singleSchedule
                    );
                    $soChoToiDa = (int)($lk['so_cho'] ?? 50);
                    if ($soChoToiDa <= 0) {
                        $soChoToiDa = 50;
                    }

                    $lk['tong_nguoi_dat'] = $tongDaDat;
                    $lk['so_cho_toi_da'] = $soChoToiDa;
                    $lk['so_cho_con_lai'] = max(0, $soChoToiDa - $tongDaDat);
                }
                unset($lk);
                $hinhAnhList = $tourModel->getHinhAnhByTourId($id);
                $anhChinh = $this->chonAnhChinh($hinhAnhList);
                $yeuCauList = $tourModel->getYeuCauDacBietByTourId($id);
                $nhatKyList = $tourModel->getNhatKyTourByTourId($id);
                $hdvInfo = $tourModel->getHDVByTourId($id);

                // Phản hồi khách hàng đã trải nghiệm tour
                require_once 'models/DanhGia.php';
                $danhGiaModel = new DanhGia();
                $report = $danhGiaModel->getReportByTour($id);
                if (!empty($report['danh_gia_list']) && is_array($report['danh_gia_list'])) {
                    $danhGiaTourList = $report['danh_gia_list'];
                }
                $danhGiaTourAvg = (float)($report['tour']['diem_tb'] ?? 0);
                $danhGiaTourCount = (int)($report['tour']['so_danh_gia'] ?? 0);

                // Gợi ý tour cùng loại (tối đa 6)
                $loaiTour = trim((string)($tour['loai_tour'] ?? ''));
                if ($loaiTour !== '') {
                    $tourCungLoai = $tourModel->getRelatedToursByType($loaiTour, $id, 6);
                }

                require_once 'models/KhachHang.php';
                $khachHangModel = new KhachHang();
                $khachHang = $khachHangModel->findByUserId((int)($_SESSION['user_id'] ?? 0));
                if ($khachHang && !empty($khachHang['khach_hang_id'])) {
                    $bookings = $bookingModelForSeats->getByKhachHangId((int)$khachHang['khach_hang_id']);
                    $eligibility = $this->evaluateTourReviewEligibility($bookings, $id);
                    $daDatTourNay = (bool)$eligibility['booked'];
                    $daTraiNghiemTourNay = (bool)$eligibility['experienced'];

                    if ($daTraiNghiemTourNay) {
                        $tourReviewCurrentUser = $danhGiaModel->getTourReviewByKhachHang((int)$khachHang['khach_hang_id'], $id);
                        $daDanhGiaTourNay = !empty($tourReviewCurrentUser);
                        $coTheDanhGiaTour = true;
                    }
                }
            }
        }

        require 'views/khach_hang/chi_tiet_tour.php';
    }
    
    public function datTour() {
        require 'views/khach_hang/dat_tour.php';
    }
    
    public function danhGia() {
        // Lấy danh sách để khách hàng chọn
        require_once 'models/Tour.php';
        require_once 'models/NhaCungCap.php';
        require_once 'models/NhanSu.php';
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';
        
        $tourModel = new Tour();
        $nccModel = new NhaCungCap();
        $nhanSuModel = new NhanSu();
        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();
        
        // Lấy booking đã hoàn thành của khách hàng để đánh giá
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        $bookingsHoanTat = [];
        if ($khachHang) {
            $allBookings = $bookingModel->getByKhachHangId($khachHang['khach_hang_id']);
            $bookingsHoanTat = array_filter($allBookings, fn($b) => $b['trang_thai'] === 'HoanTat');
        }
        
        $tourList = $tourModel->getOptions(200);
        $nccList = $nccModel->getAll();
        $nhanSuList = $nhanSuModel->getAll();
        
        require 'views/khach_hang/danh_gia.php';
    }
    
    public function guiDanhGia() {
        $redirectTourId = (int)($_POST['redirect_tour_id'] ?? 0);
        $redirectUrl = $redirectTourId > 0
            ? 'index.php?act=khachHang/chiTietTour&id=' . $redirectTourId
            : 'index.php?act=khachHang/danhGia';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . $redirectUrl);
            exit();
        }
        
        require_once 'models/DanhGia.php';
        require_once 'models/KhachHang.php';
        require_once 'models/Booking.php';
        
        // Lấy khach_hang_id từ session
        $khachHangModel = new KhachHang();
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng';
            header('Location: ' . $redirectUrl);
            exit();
        }

        $loaiDanhGia = trim((string)($_POST['loai_danh_gia'] ?? ''));
        $tourId = !empty($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;

        if ($loaiDanhGia === 'Tour') {
            if ($tourId <= 0) {
                $_SESSION['error'] = 'Thiếu thông tin tour cần đánh giá.';
                header('Location: ' . $redirectUrl);
                exit();
            }

            $bookingModel = new Booking();
            $bookings = $bookingModel->getByKhachHangId((int)$khachHang['khach_hang_id']);
            $eligibility = $this->evaluateTourReviewEligibility($bookings, $tourId);

            if (empty($eligibility['booked'])) {
                $_SESSION['error'] = 'Bạn cần đặt tour này trước khi gửi đánh giá.';
                header('Location: ' . $redirectUrl);
                exit();
            }

            if (empty($eligibility['experienced'])) {
                $_SESSION['error'] = 'Bạn chỉ có thể đánh giá sau khi đã trải nghiệm xong tour.';
                header('Location: ' . $redirectUrl);
                exit();
            }
        }
        
        $data = [
            'khach_hang_id' => $khachHang['khach_hang_id'],
            'tour_id' => $tourId > 0 ? $tourId : null,
            'nha_cung_cap_id' => !empty($_POST['nha_cung_cap_id']) ? (int)$_POST['nha_cung_cap_id'] : null,
            'nhan_su_id' => !empty($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : null,
            'loai_danh_gia' => $loaiDanhGia,
            'tieu_chi' => $_POST['tieu_chi'] ?? null,
            'loai_dich_vu' => $_POST['loai_dich_vu'] ?? null,
            'diem' => (int)$_POST['diem'],
            'noi_dung' => $_POST['noi_dung']
        ];
        
        $danhGiaModel = new DanhGia();

        if ($loaiDanhGia === 'Tour' && $tourId > 0) {
            $existingTourReview = $danhGiaModel->getTourReviewByKhachHang((int)$khachHang['khach_hang_id'], $tourId);
            if (!empty($existingTourReview)) {
                if ($danhGiaModel->updateTourReviewByKhachHang((int)$khachHang['khach_hang_id'], $tourId, $data)) {
                    $_SESSION['success'] = 'Đã cập nhật đánh giá tour của bạn.';
                } else {
                    $_SESSION['error'] = 'Không thể cập nhật đánh giá. Vui lòng thử lại sau.';
                }

                header('Location: ' . $redirectUrl);
                exit();
            }
        }

        if ($danhGiaModel->create($data)) {
            $_SESSION['success'] = 'Cảm ơn bạn đã đánh giá! Ý kiến của bạn rất quan trọng với chúng tôi.';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
        
        header('Location: ' . $redirectUrl);
        exit();
    }

    private function evaluateTourReviewEligibility(array $bookings, int $tourId): array {
        $booked = false;
        $experienced = false;
        $now = time();

        foreach ($bookings as $booking) {
            if ((int)($booking['tour_id'] ?? 0) !== $tourId) {
                continue;
            }

            $booked = true;
            $status = trim((string)($booking['trang_thai'] ?? ''));
            if ($status === 'HoanTat') {
                $experienced = true;
                break;
            }

            $ngayKetThuc = trim((string)($booking['ngay_ket_thuc'] ?? ''));
            $ngayKhoiHanh = trim((string)($booking['ngay_khoi_hanh'] ?? ''));
            $endTs = $ngayKetThuc !== '' ? strtotime($ngayKetThuc . ' 23:59:59') : false;
            $startTs = $ngayKhoiHanh !== '' ? strtotime($ngayKhoiHanh . ' 23:59:59') : false;

            if (($endTs !== false && $endTs < $now) || ($endTs === false && $startTs !== false && $startTs < $now)) {
                $experienced = true;
                break;
            }
        }

        return [
            'booked' => $booked,
            'experienced' => $experienced,
            'can_review' => $booked && $experienced,
        ];
    }

    // Xem hóa đơn và trạng thái thanh toán
    public function hoaDon() {
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';
        require_once 'models/GiaoDich.php';
        
        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();
        $giaoDichModel = new GiaoDich();

        // Tu dong dong giao dich treo qua lau truoc khi hien thi hoa don.
        $this->expireStalePendingPayments($bookingModel->conn);
        
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }
        
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        $booking = null;
        $tour = null;
        $giaoDichList = [];
        $latestPayment = null;
        
        if ($bookingId > 0) {
            $booking = $bookingModel->getBookingWithDetails($bookingId);
            if ($booking && $booking['khach_hang_id'] == $khachHang['khach_hang_id']) {
                $giaoDichList = $giaoDichModel->getByTourId($booking['tour_id']);
                // Tính tổng đã thanh toán
                $tongDaThanhToan = $giaoDichModel->getTongThuByTour($booking['tour_id']);
                $latestPayment = $this->getLatestPaymentByBookingId($bookingModel->conn, $bookingId);
            } else {
                $_SESSION['error'] = 'Không tìm thấy hóa đơn';
                header('Location: index.php?act=khachHang/dashboard');
                exit();
            }
        } else {
            // Lấy tất cả booking của khách hàng
            $bookings = $bookingModel->getByKhachHangId($khachHang['khach_hang_id']);
        }
        
        require 'views/khach_hang/hoa_don.php';
    }

    // Lịch sử thanh toán online của khách hàng
    public function lichSuThanhToan() {
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';

        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();

        // Tu dong dong giao dich treo qua lau truoc khi tra cuu lich su.
        $this->expireStalePendingPayments($bookingModel->conn);

        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        $paymentRows = [];
        $conn = $bookingModel->conn;

        // Chỉ lấy các khoản thanh toán gắn với booking của khách hiện tại.
        $sql = "SELECT p.payment_id, p.booking_id, p.amount, p.payment_method, p.payment_date, p.status, p.note,
                       b.tour_id, b.tong_tien, b.trang_thai,
                       t.ten_tour
                FROM payments p
                INNER JOIN booking b ON p.booking_id = b.booking_id
                LEFT JOIN tour t ON b.tour_id = t.tour_id
                WHERE b.khach_hang_id = ?
                ORDER BY p.payment_date DESC, p.payment_id DESC";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([(int)$khachHang['khach_hang_id']]);
            $paymentRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            // Nếu bảng payment chưa có thì hiển thị rỗng thay vì crash.
            $paymentRows = [];
        }

        $paymentLogsMap = [];
        if (!empty($paymentRows)) {
            $paymentIds = array_column($paymentRows, 'payment_id');
            $placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
            try {
                $stmtLog = $conn->prepare("SELECT payment_id, action, log_time, note FROM payment_logs WHERE payment_id IN ($placeholders) ORDER BY log_time DESC, log_id DESC");
                $stmtLog->execute($paymentIds);
                $allLogs = $stmtLog->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allLogs as $log) {
                    $pid = (int)$log['payment_id'];
                    if (!isset($paymentLogsMap[$pid])) {
                        $paymentLogsMap[$pid] = [];
                    }
                    if (count($paymentLogsMap[$pid]) < 3) {
                        $paymentLogsMap[$pid][] = $log;
                    }
                }
            } catch (Throwable $e) {
                $paymentLogsMap = [];
            }
        }

        require 'views/khach_hang/lich_su_thanh_toan.php';
    }
    
    // Xem lịch trình tour chi tiết
    public function lichTrinhTour() {
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';
        require_once 'models/Tour.php';
        
        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();
        $tourModel = new Tour();
        
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }
        
        $booking = null;
        $tour = null;
        $lichTrinhList = [];
        $lichKhoiHanh = null;
        
        if ($bookingId > 0) {
            $booking = $bookingModel->getBookingWithDetails($bookingId);
            if ($booking && $booking['khach_hang_id'] == $khachHang['khach_hang_id']) {
                $tour = $tourModel->findById($booking['tour_id']);
                if ($tour) {
                    $lichTrinhList = $tourModel->getLichTrinhByTourId($booking['tour_id']);
                    $lichKhoiHanhList = $tourModel->getLichKhoiHanhByTourId($booking['tour_id']);
                    // Tìm lịch khởi hành phù hợp với ngày khởi hành của booking
                    foreach ($lichKhoiHanhList as $lkh) {
                        if ($lkh['ngay_khoi_hanh'] == $booking['ngay_khoi_hanh']) {
                            $lichKhoiHanh = $lkh;
                            break;
                        }
                    }
                }
            } else {
                $_SESSION['error'] = 'Không tìm thấy booking';
                header('Location: index.php?act=khachHang/dashboard');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Thiếu mã booking';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }
        
        require 'views/khach_hang/lich_trinh_tour.php';
    }
    
    // Thông báo
    public function thongBao() {
        require_once 'models/ThongBao.php';
        $isAjaxRequest = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        
        $thongBaoModel = new ThongBao();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        
        // Đánh dấu đã đọc nếu có tham số
        if (isset($_GET['mark_read']) && $_GET['mark_read'] > 0) {
            $marked = $thongBaoModel->danhDauDaDoc((int)$_GET['mark_read'], $userId);
            if ($isAjaxRequest) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => (bool)$marked,
                    'unread' => (int)$thongBaoModel->countChuaDoc($userId),
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: index.php?act=khachHang/thongBao');
            exit();
        }

        $thongBaoList = $thongBaoModel->getByNguoiDung($userId, 50);
        $thongBaoChuaDoc = $thongBaoModel->countChuaDoc($userId);
        
        require 'views/khach_hang/thong_bao.php';
    }

    public function notificationFeed() {
        header('Content-Type: application/json; charset=utf-8');
        require_once 'models/ThongBao.php';

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'unread' => 0, 'items' => []], JSON_UNESCAPED_UNICODE);
            exit();
        }

        try {
            $thongBaoModel = new ThongBao();
            $items = $thongBaoModel->getByNguoiDung($userId, 50);
            $unread = (int)$thongBaoModel->countChuaDoc($userId);

            $normalizedItems = array_map(static function ($tb) {
                return [
                    'id' => (int)($tb['id'] ?? 0),
                    'tieu_de' => (string)($tb['tieu_de'] ?? ''),
                    'noi_dung' => (string)($tb['noi_dung'] ?? ''),
                    'da_doc' => (int)($tb['da_doc'] ?? 0),
                    'thoi_gian_gui' => (string)($tb['thoi_gian_gui'] ?? ''),
                    'created_at' => (string)($tb['created_at'] ?? ''),
                ];
            }, $items ?: []);

            echo json_encode([
                'success' => true,
                'unread' => $unread,
                'items' => $normalizedItems,
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'unread' => 0, 'items' => []], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function notificationStream() {
        @set_time_limit(0);
        @ignore_user_abort(true);

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        require_once 'models/ThongBao.php';

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            echo "event: close\n";
            echo "data: {}\n\n";
            @ob_flush();
            @flush();
            exit();
        }

        $thongBaoModel = new ThongBao();
        $startedAt = time();
        $lastHash = '';

        while (!connection_aborted()) {
            if ((time() - $startedAt) > 300) {
                echo "event: close\n";
                echo "data: {}\n\n";
                @ob_flush();
                @flush();
                break;
            }

            try {
                $items = $thongBaoModel->getByNguoiDung($userId, 50);
                $unread = (int)$thongBaoModel->countChuaDoc($userId);
                $payload = [
                    'success' => true,
                    'unread' => $unread,
                    'items' => array_map(static function ($tb) {
                        return [
                            'id' => (int)($tb['id'] ?? 0),
                            'tieu_de' => (string)($tb['tieu_de'] ?? ''),
                            'noi_dung' => (string)($tb['noi_dung'] ?? ''),
                            'da_doc' => (int)($tb['da_doc'] ?? 0),
                            'thoi_gian_gui' => (string)($tb['thoi_gian_gui'] ?? ''),
                            'created_at' => (string)($tb['created_at'] ?? ''),
                        ];
                    }, $items ?: []),
                ];

                $hash = md5(json_encode($payload, JSON_UNESCAPED_UNICODE));
                if ($hash !== $lastHash) {
                    echo "event: notification\n";
                    echo 'data: ' . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n";
                    $lastHash = $hash;
                } else {
                    echo ": ping\n\n";
                }
            } catch (Throwable $e) {
                echo "event: notification\n";
                echo 'data: ' . json_encode(['success' => false, 'unread' => 0, 'items' => []], JSON_UNESCAPED_UNICODE) . "\n\n";
            }

            @ob_flush();
            @flush();
            sleep(3);
        }

        exit();
    }
    
    // Cập nhật thông tin cá nhân
    public function capNhatThongTin() {
        require_once 'models/KhachHang.php';
        require_once 'models/NguoiDung.php';
        $isAjaxRequest = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
        
        $khachHangModel = new KhachHang();
        $nguoiDungModel = new NguoiDung();
        
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        $nguoiDung = $nguoiDungModel->findById($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Cập nhật thông tin người dùng
            $nguoiDungData = [
                'ho_ten' => $_POST['ho_ten'] ?? '',
                'so_dien_thoai' => $_POST['so_dien_thoai'] ?? '',
                'email' => $_POST['email'] ?? ''
            ];
            
            if (!empty($_POST['mat_khau_moi']) && $_POST['mat_khau_moi'] === $_POST['xac_nhan_mat_khau']) {
                $policy = validatePasswordPolicy($_POST['mat_khau_moi']);
                if (!$policy['ok']) {
                    if ($isAjaxRequest) {
                        header('Content-Type: application/json; charset=utf-8');
                        http_response_code(422);
                        echo json_encode([
                            'success' => false,
                            'message' => 'Mat khau moi phai co it nhat 8 ky tu, gom chu hoa, chu thuong, so va ky tu dac biet.'
                        ], JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                    $_SESSION['error'] = 'Mat khau moi phai co it nhat 8 ky tu, gom chu hoa, chu thuong, so va ky tu dac biet.';
                    header('Location: index.php?act=khachHang/capNhatThongTin');
                    exit();
                }

                $nguoiDungData['mat_khau'] = password_hash($_POST['mat_khau_moi'], PASSWORD_DEFAULT);
                unset($_SESSION['force_password_change']);
            }
            
            $nguoiDungModel->update($_SESSION['user_id'], $nguoiDungData);
            
            // Cập nhật thông tin khách hàng
            if ($khachHang) {
                $khachHangData = [
                    'dia_chi' => $_POST['dia_chi'] ?? null,
                    'gioi_tinh' => $_POST['gioi_tinh'] ?? null,
                    'ngay_sinh' => !empty($_POST['ngay_sinh']) ? $_POST['ngay_sinh'] : null
                ];
                
                $sql = "UPDATE khach_hang SET dia_chi = ?, gioi_tinh = ?, ngay_sinh = ? WHERE khach_hang_id = ?";
                $stmt = $khachHangModel->conn->prepare($sql);
                $stmt->execute([
                    $khachHangData['dia_chi'],
                    $khachHangData['gioi_tinh'],
                    $khachHangData['ngay_sinh'],
                    $khachHang['khach_hang_id']
                ]);
            }
            
            $_SESSION['success'] = 'Cập nhật thông tin thành công';
            if ($isAjaxRequest) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công',
                    'display_name' => (string)($nguoiDungData['ho_ten'] ?? ''),
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: index.php?act=khachHang/capNhatThongTin');
            exit();
        }
        
        require 'views/khach_hang/cap_nhat_thong_tin.php';
    }
    
    // Gửi yêu cầu hỗ trợ
    public function guiYeuCauHoTro() {
        require_once 'models/ThongBao.php';
        require_once 'models/KhachHang.php';
        
        $thongBaoModel = new ThongBao();
        $khachHangModel = new KhachHang();
        
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'tieu_de' => $_POST['tieu_de'] ?? 'Yêu cầu hỗ trợ',
                'noi_dung' => $_POST['noi_dung'] ?? '',
                'loai_thong_bao' => 'KhachHang',
                'muc_do_uu_tien' => $_POST['muc_do_uu_tien'] ?? 'TrungBinh',
                'nguoi_gui_id' => $_SESSION['user_id'],
                'vai_tro_nhan' => 'Admin',
                'trang_thai' => 'DaGui'
            ];
            
            if ($thongBaoModel->insert($data)) {
                $_SESSION['success'] = 'Yêu cầu hỗ trợ đã được gửi thành công. Chúng tôi sẽ phản hồi sớm nhất có thể.';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
            }
            
            header('Location: index.php?act=khachHang/guiYeuCauHoTro');
            exit();
        }
        
        require 'views/khach_hang/gui_yeu_cau_ho_tro.php';
    }
    
    // Đặt tour & thanh toán nhanh từ trang khách hàng
    public function thanhToanTour() {
        require_once 'models/Tour.php';
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';
        require_once 'models/GiaoDich.php';
        require_once 'models/NguoiDung.php';

        $tourModel = new Tour();
        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();
        $giaoDichModel = new GiaoDich();
        $nguoiDungModel = new NguoiDung();

        // Lấy thông tin user & khách hàng hiện tại
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để đặt tour.';
            header('Location: index.php?act=auth/login');
            exit();
        }

        $khachHang = $khachHangModel->findByUserId($userId);
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng. Vui lòng cập nhật hồ sơ trước khi đặt tour.';
            header('Location: index.php?act=khachHang/capNhatThongTin');
            exit();
        }

        $nguoiDung = $nguoiDungModel->findById($userId);

        // Lấy tour cần đặt
        $tourId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($tourId <= 0) {
            $_SESSION['error'] = 'Thiếu thông tin tour cần đặt.';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        $tour = $tourModel->findById($tourId);
        if (!$tour) {
            $_SESSION['error'] = 'Tour không tồn tại hoặc đã bị xóa.';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        // Dùng để hiển thị thêm thông tin tour trên trang thanh toán.
        $hinhAnhList = $tourModel->getHinhAnhByTourId($tourId);
        $lichTrinhList = $tourModel->getLichTrinhByTourId($tourId);

        // Dùng để hiển thị thêm thông tin lịch khởi hành trên trang thanh toán.
        $lichKhoiHanhList = $tourModel->getLichKhoiHanhByTourId($tourId);
        $lichKhoiHanhHienThi = $this->pickDisplayedLichKhoiHanh($lichKhoiHanhList);
        $khoiHanhCountdownInfo = $this->buildDepartureCountdownInfo($lichKhoiHanhHienThi['ngay_khoi_hanh'] ?? null);
        $khoiHanhSauHienThi = $khoiHanhCountdownInfo['label'] ?? 'Chưa cập nhật';
        $isBookingLockedBy48h = !empty($khoiHanhCountdownInfo['is_locked_48h']);
        $canCreateNewBooking = !empty($khoiHanhCountdownInfo['can_book']);
        $bookingLockMessage = trim((string)($khoiHanhCountdownInfo['message'] ?? ''));

        // Nếu có booking_id trên query thì hiển thị thông tin giao dịch đang chờ ngay tại trang thanh toán tour.
        $activeBooking = null;
        $activePayment = null;
        $activeTransferNote = '';
        $activeBookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        if ($activeBookingId > 0) {
            $activeBookingData = $bookingModel->getBookingWithDetails($activeBookingId);
            if ($activeBookingData
                && (int)($activeBookingData['khach_hang_id'] ?? 0) === (int)$khachHang['khach_hang_id']
                && (int)($activeBookingData['tour_id'] ?? 0) === (int)$tourId) {
                $activeBooking = $activeBookingData;
                $activePayment = $this->getLatestPaymentByBookingId($bookingModel->conn, $activeBookingId);
                $phoneDigits = preg_replace('/\D+/', '', (string)($nguoiDung['so_dien_thoai'] ?? ''));
                if ($phoneDigits === '' || strlen($phoneDigits) < 4) {
                    // Fallback de dam bao noi dung chuyen khoan luon dung format BOOKING_{id}_{token}
                    $phoneDigits = str_pad((string)$activeBookingId, 4, '0', STR_PAD_LEFT);
                }
                $activeTransferNote = 'BOOKING_' . $activeBookingId . '_' . $phoneDigits;
            }
        }

        $activePaymentStatus = strtoupper(trim((string)($activePayment['status'] ?? '')));
        $hasActivePending = !empty($activeBooking) && !empty($activePayment) && ($activePaymentStatus === 'DANGXULY');

        // Xử lý khi khách bấm "Xác nhận đã thanh toán & Đặt tour"
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postAction = trim((string)($_POST['action'] ?? ''));

            if ($postAction === 'submit_transfer_complaint') {
                if (!verifyCsrfToken($_POST['_csrf_global'] ?? '', 'global_form')) {
                    $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
                    header('Location: index.php?act=khachHang/thanhToanTour&id=' . (int)$tourId . '&booking_id=' . (int)$activeBookingId);
                    exit();
                }

                $postedBookingId = (int)($_POST['booking_id'] ?? 0);
                $postedPaymentId = (int)($_POST['payment_id'] ?? 0);
                if ($postedBookingId <= 0 || $postedPaymentId <= 0) {
                    $_SESSION['error'] = 'Khong tim thay thong tin giao dich de khiu nai.';
                    header('Location: index.php?act=khachHang/thanhToanTour&id=' . (int)$tourId . '&booking_id=' . (int)$activeBookingId);
                    exit();
                }

                if (!$hasActivePending || (int)($activeBooking['booking_id'] ?? 0) !== $postedBookingId || (int)($activePayment['payment_id'] ?? 0) !== $postedPaymentId) {
                    $_SESSION['error'] = 'Chi co the gui khiu nai voi giao dich dang cho doi soat.';
                    header('Location: index.php?act=khachHang/thanhToanTour&id=' . (int)$tourId . '&booking_id=' . (int)$activeBookingId);
                    exit();
                }

                $complaintNote = trim((string)($_POST['complaint_note'] ?? ''));
                $transferRef = trim((string)($_POST['transfer_ref'] ?? ''));
                $transferTime = trim((string)($_POST['transfer_time'] ?? ''));
                $transferAmount = trim((string)($_POST['transfer_amount'] ?? ''));

                if (mb_strlen($complaintNote) < 10) {
                    $_SESSION['error'] = 'Noi dung khiu nai can it nhat 10 ky tu.';
                    header('Location: index.php?act=khachHang/thanhToanTour&id=' . (int)$tourId . '&booking_id=' . (int)$activeBookingId);
                    exit();
                }

                if (mb_strlen($complaintNote) > 1000) {
                    $complaintNote = mb_substr($complaintNote, 0, 1000);
                }
                if (mb_strlen($transferRef) > 120) {
                    $transferRef = mb_substr($transferRef, 0, 120);
                }
                if (mb_strlen($transferTime) > 40) {
                    $transferTime = mb_substr($transferTime, 0, 40);
                }
                if (mb_strlen($transferAmount) > 40) {
                    $transferAmount = mb_substr($transferAmount, 0, 40);
                }

                $created = $this->createWrongTransferComplaint([
                    'user' => $nguoiDung,
                    'tour' => $tour,
                    'booking_id' => $postedBookingId,
                    'payment_id' => $postedPaymentId,
                    'expected_transfer_note' => $activeTransferNote,
                    'expected_amount' => (float)($activePayment['amount'] ?? 0),
                    'complaint_note' => $complaintNote,
                    'transfer_ref' => $transferRef,
                    'transfer_time' => $transferTime,
                    'transfer_amount' => $transferAmount,
                ]);

                if ($created) {
                    $this->createCustomerPaymentLog($bookingModel->conn, $postedPaymentId, 'CUSTOMER_WRONG_TRANSFER_CLAIM', 'Khach hang gui khieu nai chuyen khoan sai/thiieu noi dung.');
                    $_SESSION['success'] = 'Da gui khiu nai thanh cong. Admin se kiem tra giao dich va lien he voi ban som.';
                } else {
                    $_SESSION['error'] = 'Khong the gui khiu nai luc nay. Vui long thu lai sau it phut.';
                }

                header('Location: index.php?act=khachHang/thanhToanTour&id=' . (int)$tourId . '&booking_id=' . (int)$activeBookingId);
                exit();
            }

            if (!$canCreateNewBooking) {
                $_SESSION['error'] = $bookingLockMessage !== ''
                    ? $bookingLockMessage
                    : 'Tour này đã nằm trong khoảng 48 giờ trước khởi hành, hệ thống không cho phép đặt mới.';
                header('Location: index.php?act=khachHang/thanhToanTour&id=' . $tourId);
                exit();
            }

            if (!empty($activePayment) && (($activePayment['status'] ?? '') === 'DangXuLy')) {
                $_SESSION['error'] = 'Ban da co giao dich dang xu ly cho booking nay. Vui long dung noi dung chuyen khoan hien thi ben duoi va cho he thong doi soat.';
                header('Location: index.php?act=khachHang/thanhToanTour&id=' . $tourId . '&booking_id=' . (int)$activeBookingId);
                exit();
            }

            $soLuong = isset($_POST['so_luong']) ? (int)$_POST['so_luong'] : 1;
            if ($soLuong <= 0) {
                $soLuong = 1;
            }

            // Lấy giá tour (ưu tiên giá_tour nếu có, fallback về gia_co_ban)
            $giaCoBan = 0;
            if (isset($tour['gia_tour']) && $tour['gia_tour'] !== null) {
                $giaCoBan = (float)$tour['gia_tour'];
            } elseif (isset($tour['gia_co_ban']) && $tour['gia_co_ban'] !== null) {
                $giaCoBan = (float)$tour['gia_co_ban'];
            }

            $tongTien = $giaCoBan * $soLuong;
            $paymentMethod = $_POST['payment_method'] ?? 'VNPay';
            $allowedMethods = ['VNPay', 'Momo', 'Paypal'];
            if (!in_array($paymentMethod, $allowedMethods, true)) {
                $paymentMethod = 'VNPay';
            }
            if (in_array(PAYMENT_MODE, ['vnpay', 'manual_qr'], true)) {
                $paymentMethod = 'VNPay';
            }

            try {
                $pendingBooking = $this->findReusablePendingBooking(
                    $bookingModel->conn,
                    (int)$tourId,
                    (int)$khachHang['khach_hang_id']
                );

                if (!empty($pendingBooking) && strtoupper((string)($pendingBooking['payment_status'] ?? '')) === 'DANGXULY') {
                    $_SESSION['error'] = 'Ban da co giao dich dang xu ly. Vui long hoan tat giao dich hien tai truoc khi tao giao dich moi.';
                    header('Location: index.php?act=khachHang/thanhToanTour&id=' . (int)$tourId . '&booking_id=' . (int)($pendingBooking['booking_id'] ?? 0));
                    exit();
                }

                $pendingNote = '[PENDING_PAYMENT] Booking tam cho thanh toan online tu trang khach hang';
                $bookingId = (int)($pendingBooking['booking_id'] ?? 0);

                if ($bookingId > 0) {
                    $stmtUpdateDraft = $bookingModel->conn->prepare(
                        'UPDATE booking
                         SET ngay_dat = ?, ngay_khoi_hanh = NULL, ngay_ket_thuc = NULL,
                             so_nguoi = ?, tong_tien = ?, trang_thai = ?, ghi_chu = ?
                         WHERE booking_id = ?'
                    );
                    $stmtUpdateDraft->execute([
                        date('Y-m-d'),
                        (int)$soLuong,
                        (float)$tongTien,
                        'Huy',
                        $pendingNote,
                        $bookingId,
                    ]);
                } else {
                    $bookingData = [
                        'tour_id' => $tourId,
                        'khach_hang_id' => $khachHang['khach_hang_id'],
                        'ngay_dat' => date('Y-m-d'),
                        'ngay_khoi_hanh' => null,
                        'ngay_ket_thuc' => null,
                        'so_nguoi' => $soLuong,
                        'tong_tien' => $tongTien,
                        // Booking tam: chi duoc coi la booking hop le sau khi payment thanh cong.
                        'trang_thai' => 'Huy',
                        'ghi_chu' => $pendingNote,
                    ];

                    $bookingId = (int)$bookingModel->insert($bookingData);
                    if ($bookingId <= 0) {
                        throw new Exception('Không thể khởi tạo booking tạm. Vui lòng thử lại sau.');
                    }
                }

                if (dbColumnExists('booking', 'trang_thai_thanh_toan', $bookingModel->conn)) {
                    $stmtPaymentStatus = $bookingModel->conn->prepare('UPDATE booking SET trang_thai_thanh_toan = ? WHERE booking_id = ?');
                    $stmtPaymentStatus->execute(['ChuaThanhToan', $bookingId]);
                }

                // Chuyển sang cổng thanh toán online và quay lại chính trang này để khách copy nội dung chuyển khoản chính xác.
                if (PAYMENT_MODE === 'manual_qr') {
                    $paymentId = $this->createPendingManualQrPayment($bookingModel->conn, (int)$bookingId, (float)$tongTien, (string)$paymentMethod);
                    if ($paymentId <= 0) {
                        throw new Exception('KhÃ´ng thá»ƒ khá»Ÿi táº¡o giao dá»‹ch thanh toÃ¡n. Vui lÃ²ng thá»­ láº¡i sau.');
                    }

                    $consumeResult = BankWebhookController::tryConsumeQueuedWebhookForBooking($bookingModel->conn, (int)$bookingId);
                    if (!empty($consumeResult['confirmed'])) {
                        $this->createCustomerPaymentLog($bookingModel->conn, $paymentId, 'AUTO_RETRY_MATCH', 'Tu dong doi soat thanh cong tu webhook queue (queue_id=' . (int)($consumeResult['queue_id'] ?? 0) . ')');
                        $_SESSION['success'] = 'Da ghi nhan va doi soat thanh toan tu dong. Hoa don da duoc cap nhat thanh cong.';
                    } else {
                        $_SESSION['success'] = 'Da ghi nhan yeu cau thanh toan. Vui long quet QR va chuyen khoan dung noi dung hien thi.';
                    }

                    header('Location: index.php?act=khachHang/thanhToanTour&id=' . (int)$tourId . '&booking_id=' . (int)$bookingId);
                    exit();
                }

                header('Location: index.php?act=payment/redirect&booking_id=' . $bookingId . '&method=' . urlencode($paymentMethod) . '&return_act=' . urlencode('khachHang/thanhToanTour') . '&return_tour_id=' . (int)$tourId);
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: index.php?act=khachHang/thanhToanTour&id=' . $tourId);
                exit();
            }
        }

        // GET: hiển thị form thanh toán tour
        require 'views/khach_hang/thanh_toan_tour.php';
    }

    // API nho de trang thanh toan polling realtime trang thai giao dich.
    public function paymentStatus() {
        header('Content-Type: application/json; charset=utf-8');

        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'Unauthenticated']);
            exit();
        }

        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        if ($bookingId <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'Missing booking_id']);
            exit();
        }

        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();

        $khachHang = $khachHangModel->findByUserId($userId);
        if (!$khachHang) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'message' => 'Forbidden']);
            exit();
        }

        $booking = $bookingModel->getBookingWithDetails($bookingId);
        if (!$booking || (int)($booking['khach_hang_id'] ?? 0) !== (int)$khachHang['khach_hang_id']) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Booking not found']);
            exit();
        }

        $payment = $this->getLatestPaymentByBookingId($bookingModel->conn, $bookingId);
        $paymentStatus = strtoupper(trim((string)($payment['status'] ?? '')));
        $bookingStatus = strtoupper(trim((string)($booking['trang_thai'] ?? '')));

        $isSuccess = ($paymentStatus === 'THANHCONG') || in_array($bookingStatus, ['DACOC', 'HOANTAT', 'DATHANHTOAN'], true);
        $isPending = ($paymentStatus === 'DANGXULY');

        $soNguoi = max(1, (int)($booking['so_nguoi'] ?? 1));
        $checkinCount = $this->countParticipantInfoRows($bookingModel->conn, $bookingId);
        $needsParticipantInfo = $isSuccess && ($checkinCount < $soNguoi);

        echo json_encode([
            'ok' => true,
            'booking_id' => (int)$bookingId,
            'payment_id' => (int)($payment['payment_id'] ?? 0),
            'payment_status' => $payment['status'] ?? '',
            'booking_status' => $booking['trang_thai'] ?? '',
            'is_success' => $isSuccess,
            'is_pending' => $isPending,
            'payment_amount' => isset($payment['amount']) ? (float)$payment['amount'] : null,
            'payment_date' => $payment['payment_date'] ?? null,
            'needs_participant_info' => $needsParticipantInfo,
            'next_action_url' => $needsParticipantInfo
                ? ('index.php?act=khachHang/nhapThongTinThamGia&booking_id=' . (int)$bookingId)
                : ('index.php?act=khachHang/hoaDon&booking_id=' . (int)$bookingId),
            'server_time' => date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    public function nhapThongTinThamGia() {
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';
        require_once 'models/CheckinKhach.php';

        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        if ($bookingId <= 0) {
            $_SESSION['error'] = 'Thiếu mã booking để nhập thông tin người tham gia.';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();
        $checkinModel = new CheckinKhach();

        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        $booking = $bookingModel->getBookingWithDetails($bookingId);
        if (!$booking || (int)($booking['khach_hang_id'] ?? 0) !== (int)$khachHang['khach_hang_id']) {
            $_SESSION['error'] = 'Không tìm thấy booking hợp lệ';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        $latestPayment = $this->getLatestPaymentByBookingId($bookingModel->conn, $bookingId);
        $paymentStatus = strtoupper(trim((string)($latestPayment['status'] ?? '')));
        $bookingStatus = strtoupper(trim((string)($booking['trang_thai'] ?? '')));
        $isPaid = ($paymentStatus === 'THANHCONG') || in_array($bookingStatus, ['DACOC', 'HOANTAT', 'DATHANHTOAN'], true);

        if (!$isPaid) {
            $_SESSION['error'] = 'Booking chưa thanh toán thành công. Vui lòng hoàn tất thanh toán trước khi nhập thông tin người tham gia.';
            header('Location: index.php?act=khachHang/thanhToan&booking_id=' . $bookingId);
            exit();
        }

        $requiredCount = max(1, (int)($booking['so_nguoi'] ?? 1));
        $resolvedLichKhoiHanhId = $this->resolveLichKhoiHanhIdForBooking($bookingModel->conn, $booking);
        if ($resolvedLichKhoiHanhId > 0) {
            $this->syncTourCheckinLichKhoiHanh($bookingModel->conn, $bookingId, $resolvedLichKhoiHanhId);
        }
        $existingRows = $checkinModel->getByBookingId($bookingId);
        $this->syncBookingParticipantStatus($bookingModel->conn, $bookingId, count($existingRows), $requiredCount);
        $participantErrors = $_SESSION['participant_errors'] ?? [];
        if (isset($_SESSION['participant_errors'])) {
            unset($_SESSION['participant_errors']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $participants = $_POST['participants'] ?? [];
            if (!is_array($participants)) {
                $participants = [];
            }

            $normalized = [];
            foreach ($participants as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $hoTen = trim((string)($p['ho_ten'] ?? ''));
                if ($hoTen === '') {
                    continue;
                }

                $normalized[] = [
                    'ho_ten' => $hoTen,
                    'so_cmnd' => trim((string)($p['so_cmnd'] ?? '')),
                    'so_passport' => strtoupper(trim((string)($p['so_passport'] ?? ''))),
                    'ngay_sinh' => trim((string)($p['ngay_sinh'] ?? '')),
                    'gioi_tinh' => trim((string)($p['gioi_tinh'] ?? 'Khac')),
                    'quoc_tich' => trim((string)($p['quoc_tich'] ?? 'Việt Nam')),
                    'dia_chi' => trim((string)($p['dia_chi'] ?? '')),
                    'so_dien_thoai' => trim((string)($p['so_dien_thoai'] ?? '')),
                    'email' => trim((string)($p['email'] ?? '')),
                    'ghi_chu' => trim((string)($p['ghi_chu'] ?? '')),
                    'existing_anh_cccd' => trim((string)($p['existing_anh_cccd'] ?? '')),
                    'existing_anh_passport' => trim((string)($p['existing_anh_passport'] ?? '')),
                ];
            }

            if (count($normalized) < $requiredCount) {
                $_SESSION['error'] = 'Vui lòng nhập đủ thông tin cho ' . $requiredCount . ' người tham gia.';
                $_SESSION['participant_draft'] = $participants;
                header('Location: index.php?act=khachHang/nhapThongTinThamGia&booking_id=' . $bookingId);
                exit();
            }

            $validationErrors = [];
            for ($i = 0; $i < $requiredCount; $i++) {
                $rowErrors = $this->validateParticipantRow($normalized[$i] ?? [], $i + 1);
                if (!empty($rowErrors)) {
                    $validationErrors[$i] = $rowErrors;
                }
            }

            $duplicateErrors = $this->validateDuplicateParticipantDocuments($normalized, $requiredCount);
            foreach ($duplicateErrors as $index => $rowErrors) {
                if (!isset($validationErrors[$index])) {
                    $validationErrors[$index] = [];
                }
                $validationErrors[$index] = array_merge($validationErrors[$index], $rowErrors);
            }

            if (!empty($validationErrors)) {
                $firstIndex = (int)array_key_first($validationErrors);
                $firstFieldErrors = (array)($validationErrors[$firstIndex] ?? []);
                $firstMessage = (string)reset($firstFieldErrors);

                $_SESSION['error'] = $firstMessage !== ''
                    ? $firstMessage
                    : 'Thông tin người tham gia chưa hợp lệ. Vui lòng kiểm tra lại.';
                $_SESSION['participant_draft'] = $participants;
                $_SESSION['participant_errors'] = $validationErrors;
                header('Location: index.php?act=khachHang/nhapThongTinThamGia&booking_id=' . $bookingId);
                exit();
            }

            try {
                $conn = $checkinModel->conn;
                // Run schema-altering checks before opening transaction to avoid implicit commit side effects.
                $checkinModel->ensureExtendedSchema();
                $conn->beginTransaction();
                $uploadedFilesToKeep = [];

                $stmtDelete = $conn->prepare('DELETE FROM tour_checkin WHERE booking_id = ?');
                $stmtDelete->execute([$bookingId]);

                $lichKhoiHanhVal = $resolvedLichKhoiHanhId > 0 ? $resolvedLichKhoiHanhId : null;

                for ($i = 0; $i < $requiredCount; $i++) {
                    $row = $normalized[$i];
                    $anhCccd = $row['existing_anh_cccd'] !== '' ? $row['existing_anh_cccd'] : null;
                    $anhPassport = $row['existing_anh_passport'] !== '' ? $row['existing_anh_passport'] : null;

                    $uploadCccd = $this->extractParticipantUpload('anh_cccd', $i);
                    if ($uploadCccd !== null) {
                        $savedPath = uploadFile($uploadCccd, 'uploads/participant_docs/');
                        if ($savedPath === null) {
                            throw new Exception('Không thể tải ảnh CCCD/CMND cho người tham gia #' . ($i + 1) . '.');
                        }
                        $anhCccd = $savedPath;
                        $uploadedFilesToKeep[] = $savedPath;
                    }

                    $uploadPassport = $this->extractParticipantUpload('anh_passport', $i);
                    if ($uploadPassport !== null) {
                        $savedPath = uploadFile($uploadPassport, 'uploads/participant_docs/');
                        if ($savedPath === null) {
                            throw new Exception('Không thể tải ảnh passport cho người tham gia #' . ($i + 1) . '.');
                        }
                        $anhPassport = $savedPath;
                        $uploadedFilesToKeep[] = $savedPath;
                    }

                    $ok = $checkinModel->insert([
                        'booking_id' => $bookingId,
                        'khach_hang_id' => (int)$khachHang['khach_hang_id'],
                        'lich_khoi_hanh_id' => $lichKhoiHanhVal,
                        'ho_ten' => $row['ho_ten'],
                        'so_cmnd' => $row['so_cmnd'] !== '' ? $row['so_cmnd'] : null,
                        'so_passport' => $row['so_passport'] !== '' ? $row['so_passport'] : null,
                        'ngay_sinh' => $row['ngay_sinh'] !== '' ? $row['ngay_sinh'] : null,
                        'gioi_tinh' => in_array($row['gioi_tinh'], ['Nam', 'Nữ', 'Khac'], true) ? $row['gioi_tinh'] : 'Khac',
                        'quoc_tich' => $row['quoc_tich'] !== '' ? $row['quoc_tich'] : 'Việt Nam',
                        'dia_chi' => $row['dia_chi'] !== '' ? $row['dia_chi'] : null,
                        'so_dien_thoai' => $row['so_dien_thoai'] !== '' ? $row['so_dien_thoai'] : null,
                        'email' => $row['email'] !== '' ? $row['email'] : null,
                        'anh_cccd' => $anhCccd,
                        'anh_passport' => $anhPassport,
                        'trang_thai' => 'ChuaCheckIn',
                        'ghi_chu' => $row['ghi_chu'] !== '' ? $row['ghi_chu'] : null,
                    ]);

                    if (!$ok) {
                        throw new Exception('Không thể lưu thông tin người tham gia.');
                    }
                }

                $this->syncBookingParticipantStatus($conn, $bookingId, $requiredCount, $requiredCount);
                $conn->commit();
                unset($_SESSION['participant_draft']);
                $_SESSION['success'] = 'Đã lưu thông tin người tham gia thành công.';
                header('Location: index.php?act=khachHang/hoaDon&booking_id=' . $bookingId);
                exit();
            } catch (Throwable $e) {
                if (isset($conn) && $conn->inTransaction()) {
                    $conn->rollBack();
                }
                if (!empty($uploadedFilesToKeep) && is_array($uploadedFilesToKeep)) {
                    foreach ($uploadedFilesToKeep as $path) {
                        if (is_string($path) && $path !== '') {
                            deleteFile($path);
                        }
                    }
                }
                $_SESSION['error'] = 'Không thể lưu thông tin người tham gia: ' . $e->getMessage();
                $_SESSION['participant_draft'] = $participants;
                header('Location: index.php?act=khachHang/nhapThongTinThamGia&booking_id=' . $bookingId);
                exit();
            }
        }

        $draft = $_SESSION['participant_draft'] ?? null;
        if (isset($_SESSION['participant_draft'])) {
            unset($_SESSION['participant_draft']);
        }

        require 'views/khach_hang/nhap_thong_tin_tham_gia.php';
    }
    
    // Thanh toán online
    public function thanhToan() {
        require_once 'models/Booking.php';
        require_once 'models/KhachHang.php';
        
        $bookingModel = new Booking();
        $khachHangModel = new KhachHang();
        
        $khachHang = $khachHangModel->findByUserId($_SESSION['user_id']);
        if (!$khachHang) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }
        
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        if ($bookingId <= 0) {
            $_SESSION['error'] = 'Thiếu mã booking';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }
        
        $booking = $bookingModel->getBookingWithDetails($bookingId);
        if (!$booking || $booking['khach_hang_id'] != $khachHang['khach_hang_id']) {
            $_SESSION['error'] = 'Không tìm thấy booking';
            header('Location: index.php?act=khachHang/dashboard');
            exit();
        }

        if (in_array($booking['trang_thai'] ?? '', ['HoanTat', 'DaHuy', 'Huy'], true)) {
            $_SESSION['error'] = 'Booking này không khả dụng để thanh toán online.';
            header('Location: index.php?act=khachHang/hoaDon&booking_id=' . $bookingId);
            exit();
        }

        $latestPayment = $this->getLatestPaymentByBookingId($bookingModel->conn, $bookingId);
        $hasPendingPayment = !empty($latestPayment) && (($latestPayment['status'] ?? '') === 'DangXuLy');
        
        // Luồng mới: tạo giao dịch qua PaymentGatewayController để đồng nhất với thanhToanTour.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($hasPendingPayment) {
                $_SESSION['error'] = 'Booking đang có giao dịch chờ xử lý. Vui lòng đợi kết quả thanh toán hiện tại.';
                header('Location: index.php?act=khachHang/hoaDon&booking_id=' . $bookingId);
                exit();
            }

            $paymentMethod = $_POST['payment_method'] ?? 'VNPay';
            $allowedMethods = ['VNPay', 'Momo', 'Paypal'];
            if (!in_array($paymentMethod, $allowedMethods, true)) {
                $paymentMethod = 'VNPay';
            }
            if (in_array(PAYMENT_MODE, ['vnpay', 'manual_qr'], true)) {
                $paymentMethod = 'VNPay';
            }

            header('Location: index.php?act=payment/redirect&booking_id=' . $bookingId . '&method=' . urlencode($paymentMethod) . '&return_act=khachHang/hoaDon');
            exit();
        }

        $paymentMethod = 'VNPay';
        
        require 'views/khach_hang/thanh_toan.php';
    }

    private function chonAnhChinh(array $hinhAnhList) {
        foreach ($hinhAnhList as $anh) {
            if (!empty($anh['url_anh'])) {
                return $anh;
            }
        }
        return null;
    }

    private function getLatestPaymentByBookingId(PDO $conn, int $bookingId) {
        if ($bookingId <= 0) {
            return null;
        }

        try {
            $stmt = $conn->prepare("SELECT payment_id, amount, payment_method, payment_date, status, note
                                   FROM payments
                                   WHERE booking_id = ?
                                   ORDER BY payment_id DESC
                                   LIMIT 1");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function findReusablePendingBooking(PDO $conn, int $tourId, int $khachHangId): ?array {
        if ($tourId <= 0 || $khachHangId <= 0) {
            return null;
        }

        try {
            $sql = "SELECT b.booking_id,
                           (
                               SELECT p.status
                               FROM payments p
                               WHERE p.booking_id = b.booking_id
                               ORDER BY p.payment_id DESC
                               LIMIT 1
                           ) AS payment_status
                    FROM booking b
                    WHERE b.tour_id = ?
                      AND b.khach_hang_id = ?
                      AND b.trang_thai = 'Huy'
                      AND b.ghi_chu LIKE '[PENDING_PAYMENT]%'
                    ORDER BY b.booking_id DESC
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->execute([$tourId, $khachHangId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function expireStalePendingPayments(PDO $conn): void {
        require_once __DIR__ . '/../models/Payment.php';
        $timeoutMinutes = 45;
        $cutoff = date('Y-m-d H:i:s', time() - ($timeoutMinutes * 60));

        try {
            $stmt = $conn->prepare("SELECT payment_id FROM payments WHERE status = ? AND payment_date < ? ORDER BY payment_id ASC LIMIT 200");
            $stmt->execute([Payment::STATUS_DANG_XU_LY, $cutoff]);
            $ids = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'payment_id'));
            if (empty($ids)) {
                return;
            }

            $conn->beginTransaction();
            $update = $conn->prepare("UPDATE payments SET note = CONCAT(COALESCE(note,''), ' | AUTO_TIMEOUT=', ?) WHERE payment_id = ? AND status = ?");
            $insertLog = $conn->prepare("INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, 'AUTO_TIMEOUT', ?, ?)");
            $now = date('Y-m-d H:i:s');

            foreach ($ids as $pid) {
                $update->execute([$now, $pid, Payment::STATUS_DANG_XU_LY]);
                if ($update->rowCount() > 0) {
                    Payment::transitionStatus($conn, $pid, Payment::STATUS_HET_HAN, 'customer_auto_timeout', [
                        'timeout_minutes' => $timeoutMinutes,
                    ]);
                    $insertLog->execute([$pid, $now, 'He thong tu dong timeout giao dich cho xu ly qua ' . $timeoutMinutes . ' phut']);
                }
            }

            $conn->commit();
        } catch (Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
        }
    }

    private function createPendingManualQrPayment(PDO $conn, int $bookingId, float $amount, string $paymentMethod): int {
        require_once __DIR__ . '/../models/Payment.php';
        if ($bookingId <= 0 || $amount <= 0) {
            return 0;
        }

        $this->ensureCustomerPaymentTables($conn);

        $resolvedMethod = $this->resolveCustomerPaymentMethod($conn, $paymentMethod);
        $stmt = $conn->prepare('INSERT INTO payments (booking_id, amount, payment_method, payment_date, status, note) VALUES (?, ?, ?, ?, ?, ?)');
        $ok = $stmt->execute([
            $bookingId,
            $amount,
            $resolvedMethod,
            date('Y-m-d H:i:s'),
            Payment::STATUS_TAO_MOI,
            'Khoi tao thanh toan online | gateway=VNPay'
        ]);
        if (!$ok) {
            return 0;
        }

        $paymentId = (int)$conn->lastInsertId();
        $this->createCustomerPaymentLog($conn, $paymentId, 'CREATE', 'Khoi tao giao dich online');
        Payment::transitionStatus($conn, $paymentId, Payment::STATUS_DANG_XU_LY, 'customer_create_pending_manual_qr');
        $this->createCustomerPaymentLog($conn, $paymentId, 'WAIT_CONFIRM', 'Cho admin xac nhan da nhan tien chuyen khoan QR');

        $txnRef = 'BK' . $bookingId . 'P' . $paymentId . 'T' . time();
        $updateStmt = $conn->prepare('UPDATE payments SET note = ? WHERE payment_id = ?');
        $updateStmt->execute(['TXN_REF=' . $txnRef, $paymentId]);

        return $paymentId;
    }

    private function ensureCustomerPaymentTables(PDO $conn): void {
        require_once __DIR__ . '/../models/Payment.php';
        try {
            $conn->query('SELECT payment_id, booking_id, amount, payment_method, status FROM payments LIMIT 1');
            $conn->query('SELECT log_id, payment_id, action FROM payment_logs LIMIT 1');
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Schema payments/payment_logs is missing. Please run `php scripts/migrate.php up`. Root cause: ' . $e->getMessage()
            );
        }

        Payment::ensureStateMachineSchema($conn);
    }

    private function resolveCustomerPaymentMethod(PDO $conn, string $paymentMethod): string {
        $normalized = in_array($paymentMethod, ['VNPay', 'Momo', 'Paypal'], true) ? $paymentMethod : 'VNPay';

        try {
            $stmt = $conn->query("SHOW COLUMNS FROM payments LIKE 'payment_method'");
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            if (!$row || empty($row['Type']) || !preg_match('/^enum\((.*)\)$/i', (string)$row['Type'], $m)) {
                return $normalized;
            }

            $enumValues = [];
            foreach (str_getcsv($m[1], ',', "'", '\\') as $part) {
                $value = trim($part, "'");
                if ($value !== '') {
                    $enumValues[] = $value;
                }
            }

            if (in_array($normalized, $enumValues, true)) {
                return $normalized;
            }

            $legacyMap = [
                'VNPay' => 'ChuyenKhoan',
                'Momo' => 'ViDienTu',
                'Paypal' => 'ViDienTu',
            ];
            $legacy = $legacyMap[$normalized] ?? 'ChuyenKhoan';
            if (in_array($legacy, $enumValues, true)) {
                return $legacy;
            }
        } catch (Throwable $e) {
            return $normalized;
        }

        return $normalized;
    }

    private function createCustomerPaymentLog(PDO $conn, int $paymentId, string $action, string $note): void {
        if ($paymentId <= 0) {
            return;
        }

        try {
            $stmt = $conn->prepare('INSERT INTO payment_logs (payment_id, action, log_time, note) VALUES (?, ?, ?, ?)');
            $stmt->execute([$paymentId, $action, date('Y-m-d H:i:s'), $note]);
        } catch (Throwable $e) {
        }
    }

    private function createWrongTransferComplaint(array $payload): bool {
        try {
            require_once __DIR__ . '/../models/ThongBao.php';
            $thongBaoModel = new ThongBao();

            $user = (array)($payload['user'] ?? []);
            $tour = (array)($payload['tour'] ?? []);
            $bookingId = (int)($payload['booking_id'] ?? 0);
            $paymentId = (int)($payload['payment_id'] ?? 0);
            $expectedTransferNote = trim((string)($payload['expected_transfer_note'] ?? ''));
            $expectedAmount = (float)($payload['expected_amount'] ?? 0);
            $complaintNote = trim((string)($payload['complaint_note'] ?? ''));
            $transferRef = trim((string)($payload['transfer_ref'] ?? ''));
            $transferTime = trim((string)($payload['transfer_time'] ?? ''));
            $transferAmount = trim((string)($payload['transfer_amount'] ?? ''));

            if ($bookingId <= 0 || $paymentId <= 0 || $complaintNote === '') {
                return false;
            }

            $noiDung =
                "[KHIEU NAI CHUYEN KHOAN SAI NOI DUNG]\n" .
                'Khach hang: ' . (string)($user['ho_ten'] ?? '') . "\n" .
                'Email: ' . (string)($user['email'] ?? '') . "\n" .
                'So dien thoai: ' . (string)($user['so_dien_thoai'] ?? '') . "\n" .
                'Booking ID: #' . $bookingId . "\n" .
                'Payment ID: #' . $paymentId . "\n" .
                'Tour: ' . (string)($tour['ten_tour'] ?? '') . "\n" .
                'So tien du kien: ' . number_format($expectedAmount) . " VND\n" .
                'Noi dung chuyen khoan dung: ' . $expectedTransferNote . "\n" .
                'So tien khach khai bao: ' . ($transferAmount !== '' ? $transferAmount : '[Khong cung cap]') . "\n" .
                'Thoi gian chuyen khoan: ' . ($transferTime !== '' ? $transferTime : '[Khong cung cap]') . "\n" .
                'Ma giao dich/tham chieu: ' . ($transferRef !== '' ? $transferRef : '[Khong cung cap]') . "\n" .
                'Noi dung khiu nai: ' . $complaintNote;

            $insertId = $thongBaoModel->insert([
                'tieu_de' => 'Khieu nai chuyen khoan sai noi dung - Booking #' . $bookingId,
                'noi_dung' => $noiDung,
                'loai_thong_bao' => 'KhachHang',
                'muc_do_uu_tien' => 'Cao',
                'nguoi_gui_id' => (int)($_SESSION['user_id'] ?? 0),
                'vai_tro_nhan' => 'Admin',
                'trang_thai' => 'DaGui',
            ]);

            return !empty($insertId);
        } catch (Throwable $e) {
            error_log('[KhachHangController::createWrongTransferComplaint] ' . $e->getMessage());
            return false;
        }
    }

    private function validateParticipantRow(array $row, int $position): array {
        $errors = [];

        $hoTen = trim((string)($row['ho_ten'] ?? ''));
        if ($hoTen === '' || mb_strlen($hoTen) < 2) {
            $errors['ho_ten'] = 'Người tham gia #' . $position . ': họ tên phải có ít nhất 2 ký tự.';
        }

        $ngaySinh = trim((string)($row['ngay_sinh'] ?? ''));
        if ($ngaySinh !== '') {
            $birthTs = strtotime($ngaySinh);
            $minTs = strtotime('1900-01-01');
            $todayTs = strtotime(date('Y-m-d'));
            if (!$birthTs || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngaySinh) || $birthTs < $minTs || $birthTs > $todayTs) {
                $errors['ngay_sinh'] = 'Người tham gia #' . $position . ': ngày sinh không hợp lệ.';
            }
        }

        $email = trim((string)($row['email'] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Người tham gia #' . $position . ': email không đúng định dạng.';
        }

        $soCmnd = preg_replace('/\D+/', '', (string)($row['so_cmnd'] ?? ''));
        if ($soCmnd !== '' && !in_array(strlen($soCmnd), [9, 12], true)) {
            $errors['so_cmnd'] = 'Người tham gia #' . $position . ': CCCD/CMND phải có 9 hoặc 12 chữ số.';
        }

        $passport = strtoupper(trim((string)($row['so_passport'] ?? '')));
        if ($passport !== '' && preg_match('/^[A-Z0-9]{6,12}$/', $passport) !== 1) {
            $errors['so_passport'] = 'Người tham gia #' . $position . ': passport chỉ gồm chữ/số, dài 6-12 ký tự.';
        }

        return $errors;
    }

    private function validateDuplicateParticipantDocuments(array $rows, int $requiredCount): array {
        $errors = [];
        $seenCmnd = [];
        $seenPassport = [];

        for ($i = 0; $i < $requiredCount; $i++) {
            $row = (array)($rows[$i] ?? []);
            $position = $i + 1;

            $soCmnd = preg_replace('/\D+/', '', (string)($row['so_cmnd'] ?? ''));
            if ($soCmnd !== '') {
                if (isset($seenCmnd[$soCmnd])) {
                    $errors[$i]['so_cmnd'] = 'Người tham gia #' . $position . ': CCCD/CMND bị trùng với người tham gia #' . $seenCmnd[$soCmnd] . '.';
                } else {
                    $seenCmnd[$soCmnd] = $position;
                }
            }

            $passport = strtoupper(trim((string)($row['so_passport'] ?? '')));
            if ($passport !== '') {
                if (isset($seenPassport[$passport])) {
                    $errors[$i]['so_passport'] = 'Người tham gia #' . $position . ': passport bị trùng với người tham gia #' . $seenPassport[$passport] . '.';
                } else {
                    $seenPassport[$passport] = $position;
                }
            }
        }

        return $errors;
    }

    private function extractParticipantUpload(string $field, int $index): ?array {
        $names = $_FILES['participants']['name'][$index][$field] ?? null;
        $tmpNames = $_FILES['participants']['tmp_name'][$index][$field] ?? null;
        $errors = $_FILES['participants']['error'][$index][$field] ?? UPLOAD_ERR_NO_FILE;
        $types = $_FILES['participants']['type'][$index][$field] ?? null;
        $sizes = $_FILES['participants']['size'][$index][$field] ?? null;

        if ($names === null || $tmpNames === null || $errors === UPLOAD_ERR_NO_FILE || (string)$names === '') {
            return null;
        }

        return [
            'name' => $names,
            'type' => $types,
            'tmp_name' => $tmpNames,
            'error' => $errors,
            'size' => $sizes,
        ];
    }

    private function ensureBookingParticipantStatusColumn(PDO $conn): void {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        if (!dbColumnExists('booking', 'trang_thai_hanh_khach', $conn)) {
            throw new RuntimeException('Schema booking.trang_thai_hanh_khach is missing. Please run `php scripts/migrate.php up`.');
        }

        $initialized = true;
    }

    private function syncBookingParticipantStatus(PDO $conn, int $bookingId, int $currentCount, int $requiredCount): void {
        if ($bookingId <= 0) {
            return;
        }

        try {
            $this->ensureBookingParticipantStatusColumn($conn);
            $required = max(1, $requiredCount);
            $status = $currentCount >= $required ? 'DaKhaiBaoDu' : 'ChuaKhaiBaoDu';
            $stmt = $conn->prepare('UPDATE booking SET trang_thai_hanh_khach = ? WHERE booking_id = ?');
            $stmt->execute([$status, $bookingId]);
        } catch (Throwable $e) {
        }
    }

    private function countParticipantInfoRows(PDO $conn, int $bookingId): int {
        if ($bookingId <= 0) {
            return 0;
        }

        try {
            $stmt = $conn->prepare('SELECT COUNT(*) FROM tour_checkin WHERE booking_id = ?');
            $stmt->execute([$bookingId]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function resolveLichKhoiHanhIdForBooking(PDO $conn, array $booking): int {
        $directId = (int)($booking['lich_khoi_hanh_id'] ?? 0);
        if ($directId > 0) {
            return $directId;
        }

        $tourId = (int)($booking['tour_id'] ?? 0);
        $ngayKhoiHanh = trim((string)($booking['ngay_khoi_hanh'] ?? ''));
        try {
            if ($tourId <= 0) {
                return 0;
            }

            if ($ngayKhoiHanh !== '') {
                $stmt = $conn->prepare("SELECT id
                                       FROM lich_khoi_hanh
                                       WHERE tour_id = ?
                                         AND DATE(ngay_khoi_hanh) = DATE(?)
                                       ORDER BY id DESC
                                       LIMIT 1");
                $stmt->execute([$tourId, $ngayKhoiHanh]);
                $matchedByDate = (int)$stmt->fetchColumn();
                if ($matchedByDate > 0) {
                    return $matchedByDate;
                }
            }

            // Fallback for quick-booking flows that did not persist ngay_khoi_hanh.
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM lich_khoi_hanh WHERE tour_id = ?");
            $countStmt->execute([$tourId]);
            $scheduleCount = (int)$countStmt->fetchColumn();

            if ($scheduleCount === 1) {
                $singleStmt = $conn->prepare("SELECT id FROM lich_khoi_hanh WHERE tour_id = ? LIMIT 1");
                $singleStmt->execute([$tourId]);
                return (int)$singleStmt->fetchColumn();
            }

            $nextStmt = $conn->prepare("SELECT id
                                       FROM lich_khoi_hanh
                                       WHERE tour_id = ?
                                         AND DATE(ngay_khoi_hanh) >= CURDATE()
                                       ORDER BY ngay_khoi_hanh ASC, id ASC
                                       LIMIT 1");
            $nextStmt->execute([$tourId]);
            $nextId = (int)$nextStmt->fetchColumn();
            if ($nextId > 0) {
                return $nextId;
            }

            $latestStmt = $conn->prepare("SELECT id
                                         FROM lich_khoi_hanh
                                         WHERE tour_id = ?
                                         ORDER BY ngay_khoi_hanh DESC, id DESC
                                         LIMIT 1");
            $latestStmt->execute([$tourId]);
            return (int)$latestStmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function syncTourCheckinLichKhoiHanh(PDO $conn, int $bookingId, int $lichKhoiHanhId): void {
        if ($bookingId <= 0 || $lichKhoiHanhId <= 0) {
            return;
        }

        try {
            $stmt = $conn->prepare('UPDATE tour_checkin
                                    SET lich_khoi_hanh_id = ?
                                    WHERE booking_id = ?
                                      AND (lich_khoi_hanh_id IS NULL OR lich_khoi_hanh_id = 0)');
            $stmt->execute([$lichKhoiHanhId, $bookingId]);
        } catch (Throwable $e) {
        }
    }

    private function pickDisplayedLichKhoiHanh(array $lichKhoiHanhList): ?array {
        if (empty($lichKhoiHanhList)) {
            return null;
        }

        $today = date('Y-m-d');
        foreach ($lichKhoiHanhList as $lk) {
            if (!empty($lk['ngay_khoi_hanh']) && (string)$lk['ngay_khoi_hanh'] >= $today) {
                return $lk;
            }
        }

        $last = end($lichKhoiHanhList);
        reset($lichKhoiHanhList);
        return $last ?: null;
    }

    private function buildDepartureCountdownInfo($ngayKhoiHanh): array {
        $dateYmd = trim((string)$ngayKhoiHanh);
        if ($dateYmd === '') {
            return [
                'can_book' => true,
                'is_locked_48h' => false,
                'days_remaining' => null,
                'label' => 'Chưa cập nhật',
                'message' => '',
            ];
        }

        $departureTs = strtotime($dateYmd . ' 00:00:00');
        if ($departureTs === false) {
            return [
                'can_book' => true,
                'is_locked_48h' => false,
                'days_remaining' => null,
                'label' => 'Chưa cập nhật',
                'message' => '',
            ];
        }

        $secondsLeft = $departureTs - time();
        if ($secondsLeft <= 0) {
            return [
                'can_book' => false,
                'is_locked_48h' => true,
                'days_remaining' => 0,
                'label' => 'Đã khởi hành',
                'message' => 'Tour này đã hoặc đang khởi hành, không thể đặt thêm.',
            ];
        }

        $daysRemaining = (int)ceil($secondsLeft / 86400);
        $isLocked48h = $secondsLeft <= (48 * 3600);

        return [
            'can_book' => !$isLocked48h,
            'is_locked_48h' => $isLocked48h,
            'days_remaining' => $daysRemaining,
            'label' => $daysRemaining . ' ngày',
            'message' => $isLocked48h
                ? 'Tour này chỉ cho phép đặt trước ít nhất 48 giờ so với ngày khởi hành.'
                : '',
        ];
    }
}
