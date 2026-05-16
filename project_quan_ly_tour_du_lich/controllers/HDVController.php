<?php

require_once 'models/NhanSu.php';
require_once 'models/HDVManagement.php';
require_once 'models/LichKhoiHanh.php';
require_once 'models/PhanBoNhanSu.php';
require_once 'models/Tour.php';
require_once 'models/Booking.php';
require_once 'models/NhatKyTour.php';
require_once 'models/KhachHang.php';
require_once 'models/CheckinKhach.php';
require_once 'models/YeuCauDacBiet.php';

class HDVController {
    private static array $columnExistsCache = [];
    private NhanSu $nhanSuModel;
    private HDVManagement $hdvMgmtModel;
    private LichKhoiHanh $lichKhoiHanhModel;
    private PhanBoNhanSu $phanBoNhanSuModel;
    private Tour $tourModel;
    private Booking $bookingModel;
    private NhatKyTour $nhatKyTourModel;
    private KhachHang $khachHangModel;
    private YeuCauDacBiet $yeuCauDacBietModel;
    private CheckinKhach $checkinKhachModel;

    private function getHdvUnreadNotificationCount(int $nhanSuId) {
        $sql = "SELECT COUNT(*) as count FROM thong_bao_hdv WHERE nhan_su_id = ? AND da_xem = 0";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([(int)$nhanSuId]);
        $count = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
        return max(0, $count);
    }

    private function resolveCurrentHdvId() {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return 0;
        }

        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);

        return (int)$stmt->fetchColumn();
    }

    private function requirePostCsrf(string $redirectAct = 'hdv/dashboard') {
        $scopedToken = $_POST['_csrf_token'] ?? '';
        $globalToken = $_POST['_csrf_global'] ?? '';

        $validScoped = verifyCsrfToken($scopedToken, 'hdv_form');
        $validGlobal = verifyCsrfToken($globalToken, 'global_form');

        if (!$validScoped && !$validGlobal) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=' . urlencode($redirectAct));
            exit();
        }
    }

    private function hasColumn(string $tableName, string $columnName): bool {
        $key = $tableName . '.' . $columnName;
        if (array_key_exists($key, self::$columnExistsCache)) {
            return self::$columnExistsCache[$key];
        }

        try {
            $stmt = $this->nhanSuModel->conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
            $stmt->execute([$tableName, $columnName]);
            self::$columnExistsCache[$key] = ((int)$stmt->fetchColumn() > 0);
        } catch (Throwable $e) {
            self::$columnExistsCache[$key] = false;
        }

        return self::$columnExistsCache[$key];
    }

    private function notDeletedClause(string $tableName, string $alias = ''): string {
        if (!$this->hasColumn($tableName, 'deleted_at')) {
            return '1=1';
        }
        $prefix = $alias !== '' ? ($alias . '.') : '';
        return $prefix . 'deleted_at IS NULL';
    }
    
    public function __construct() {
        requireRole('HDV');
        $this->nhanSuModel = new NhanSu();
        $this->hdvMgmtModel = new HDVManagement();
        $this->lichKhoiHanhModel = new LichKhoiHanh();
        $this->phanBoNhanSuModel = new PhanBoNhanSu();
        $this->tourModel = new Tour();
        $this->bookingModel = new Booking();
        $this->nhatKyTourModel = new NhatKyTour();
        $this->khachHangModel = new KhachHang();
        $this->yeuCauDacBietModel = new YeuCauDacBiet();
        $this->checkinKhachModel = new CheckinKhach();
    }
    
    public function lichLamViec() {
        $nhanSu = $this->getCurrentHDV();
        $nhanSuId = $nhanSu['nhan_su_id'];
        
        $lichKhoiHanhList = $this->getLichKhoiHanhByHDV($nhanSuId);
        $lichTrinhTheoTour = [];
        foreach ($lichKhoiHanhList as $lich) {
            $tourId = $lich['tour_id'] ?? null;
            if ($tourId && !isset($lichTrinhTheoTour[$tourId])) {
                $lichTrinhTheoTour[$tourId] = $this->tourModel->getLichTrinhByTourId($tourId);
            }
        }
        
        // Lấy phân bổ nhân sự (nếu HDV được phân công qua phan_bo_nhan_su)
        $sql = "SELECT pbn.*, lkh.tour_id, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc, lkh.trang_thai as lkh_trang_thai,
                t.ten_tour
                FROM phan_bo_nhan_su pbn
                LEFT JOIN lich_khoi_hanh lkh ON pbn.lich_khoi_hanh_id = lkh.id
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                WHERE pbn.nhan_su_id = ?
                ORDER BY lkh.ngay_khoi_hanh DESC";
        $stmt = $this->phanBoNhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId]);
        $phanBoNhanSuList = $stmt->fetchAll();

        $nhiemVuTheoLich = [];
        foreach ($phanBoNhanSuList as $pb) {
            if (($pb['nhan_su_id'] ?? null) == $nhanSuId) {
                $nhiemVuTheoLich[$pb['lich_khoi_hanh_id']] = $pb;
            }
        }

        // Lấy danh sách khách và yêu cầu đặc biệt cho từng lịch khởi hành
        $yeuCauDacBietTheoLich = [];
        $lichIds = array_map(static function ($lich) {
            return (int)($lich['id'] ?? 0);
        }, $lichKhoiHanhList);
        $khachByLich = $this->bookingModel->getKhachByLichKhoiHanhIdsGrouped($lichIds);

        foreach ($lichKhoiHanhList as $lich) {
            $lichId = (int)($lich['id'] ?? 0);
            if ($lichId <= 0) {
                continue;
            }
            $yeuCauDacBietTheoLich[$lichId] = $khachByLich[$lichId] ?? [];
        }
        
        require 'views/hdv/lich_lam_viec.php';
    }
    
    public function nhatKyTour() {
        $nhanSu = $this->getCurrentHDV();
        $nhanSuId = $nhanSu['nhan_su_id'];

        $lichKhoiHanhList = $this->getLichKhoiHanhByHDV($nhanSuId);
        $allowedTourIds = array_map(fn($lich) => (int)$lich['tour_id'], $lichKhoiHanhList);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requirePostCsrf('hdv/nhatKyTour');
            $this->handleNhatKyPost($nhanSuId, $allowedTourIds);
            return;
        }

        $selectedTourId = isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : null;
        if (!$selectedTourId && !empty($allowedTourIds)) {
            $selectedTourId = $allowedTourIds[0];
        }

        $nhatKyList = $this->nhatKyTourModel->getByHDVAndTour($nhanSuId, $selectedTourId ?: null);

        $entryEditing = null;
        $entryId = isset($_GET['entry_id']) ? (int)$_GET['entry_id'] : null;
        if ($entryId) {
            $entryEditing = $this->nhatKyTourModel->getById($entryId, $nhanSuId);
            if ($entryEditing) {
                $parsed = $this->parseNhatKyContent($entryEditing['noi_dung'] ?? '');
                $entryEditing = array_merge($parsed, $entryEditing);
            }
        }

        require 'views/hdv/nhat_ky_tour.php';
    }
    
    public function danhSachKhach() {
        $nhanSu = $this->getCurrentHDV();
        $nhanSuId = $nhanSu['nhan_su_id'];

        $lichKhoiHanhList = $this->getLichKhoiHanhByHDV($nhanSuId);

        $selectedLichId = isset($_GET['lich_id']) ? (int)$_GET['lich_id'] : null;
        if (!$selectedLichId && !empty($lichKhoiHanhList)) {
            $selectedLichId = (int)$lichKhoiHanhList[0]['id'];
        }

        $selectedLich = null;
        foreach ($lichKhoiHanhList as $lich) {
            if ((int)$lich['id'] === $selectedLichId) {
                $selectedLich = $lich;
                break;
            }
        }

        $danhSachKhach = [];
        if ($selectedLich) {
            // Lấy danh sách từng khách từ tour_checkin theo lich_khoi_hanh_id
            $danhSachKhach = $this->checkinKhachModel->getByLichKhoiHanh($selectedLich['id']);
            
            // Nếu chưa có khách trong tour_checkin, lấy từ booking và tạo mặc định
            if (empty($danhSachKhach)) {
                $bookings = $this->bookingModel->getKhachByTourAndNgayKhoiHanh(
                    $selectedLich['tour_id'],
                    $selectedLich['ngay_khoi_hanh']
                );
                
                // Tạo danh sách khách từ booking (mỗi booking có thể có nhiều người)
                foreach ($bookings as $booking) {
                    $soNguoi = (int)($booking['so_nguoi'] ?? 1);
                    for ($i = 0; $i < $soNguoi; $i++) {
                        $danhSachKhach[] = [
                            'id' => null,
                            'booking_id' => $booking['booking_id'],
                            'khach_hang_id' => $booking['khach_hang_id'],
                            'ho_ten' => $booking['ho_ten'] . ($soNguoi > 1 ? ' #' . ($i + 1) : ''),
                            'so_cmnd' => null,
                            'so_passport' => null,
                            'ngay_sinh' => null,
                            'gioi_tinh' => null,
                            'quoc_tich' => 'Việt Nam',
                            'dia_chi' => $booking['dia_chi'] ?? null,
                            'so_dien_thoai' => $booking['so_dien_thoai'] ?? null,
                            'email' => $booking['email'] ?? null,
                            'trang_thai' => 'ChuaCheckIn',
                            'ghi_chu' => null
                        ];
                    }
                }
            }
        }

        require 'views/hdv/danh_sach_khach.php';
    }

    public function checkInKhach() {
        $nhanSu = $this->getCurrentHDV();
        $nhanSuId = $nhanSu['nhan_su_id'];

        $lichKhoiHanhList = $this->getLichKhoiHanhByHDV($nhanSuId);

        $selectedLichId = isset($_GET['lich_id']) ? (int)$_GET['lich_id'] : null;
        if (!$selectedLichId && !empty($lichKhoiHanhList)) {
            $selectedLichId = (int)$lichKhoiHanhList[0]['id'];
        }

        $selectedLich = null;
        foreach ($lichKhoiHanhList as $lich) {
            if ((int)$lich['id'] === $selectedLichId) {
                $selectedLich = $lich;
                break;
            }
        }

        $danhSachKhach = [];
        $checkinMap = [];

        if ($selectedLich) {
            $danhSachKhach = $this->bookingModel->getKhachByTourAndNgayKhoiHanh(
                $selectedLich['tour_id'],
                $selectedLich['ngay_khoi_hanh']
            );

            $checkinList = $this->checkinKhachModel->getByLichKhoiHanh($selectedLich['id']);
            foreach ($checkinList as $item) {
                $checkinMap[$item['khach_hang_id']] = $item;
            }
        }

        require 'views/hdv/checkin_khach.php';
    }

    public function updateCheckInKhach() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=hdv/checkInKhach');
            exit();
        }

        $this->requirePostCsrf('hdv/checkInKhach');

        $nhanSu = $this->getCurrentHDV();
        $nhanSuId = $nhanSu['nhan_su_id'];

        $schema = validateInputSchema([
            'lich_khoi_hanh_id' => ['type' => 'id', 'required' => true],
            'booking_id' => ['type' => 'id', 'required' => true],
            'khach_hang_id' => ['type' => 'id', 'required' => true],
            'trang_thai' => ['type' => 'string', 'required' => true, 'enum' => ['ChuaCheckIn', 'DaCheckIn', 'DaCheckOut']],
            'ghi_chu' => ['type' => 'string', 'required' => false, 'max' => 1000],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu check-in khach khong hop le.');
            $_SESSION['error'] = 'Du lieu check-in khach khong hop le.';
            header('Location: index.php?act=hdv/checkInKhach');
            exit();
        }

        $lichKhoiHanhId = (int)($schema['data']['lich_khoi_hanh_id'] ?? 0);
        $bookingId = (int)($schema['data']['booking_id'] ?? 0);
        $khachHangId = (int)($schema['data']['khach_hang_id'] ?? 0);
        $trangThai = (string)($schema['data']['trang_thai'] ?? 'ChuaCheckIn');
        $ghiChu = (string)($schema['data']['ghi_chu'] ?? '');

        if ($lichKhoiHanhId <= 0 || $bookingId <= 0 || $khachHangId <= 0) {
            $_SESSION['error'] = 'Thiếu thông tin cần thiết.';
            header('Location: index.php?act=hdv/checkInKhach');
            exit();
        }

        $assignedLichList = $this->getLichKhoiHanhByHDV($nhanSuId);
        $allowedLichIds = array_map(fn($item) => (int)$item['id'], $assignedLichList);
        if (!in_array($lichKhoiHanhId, $allowedLichIds, true)) {
            $_SESSION['error'] = 'Bạn không được phép cập nhật lịch này.';
            header('Location: index.php?act=hdv/checkInKhach');
            exit();
        }

        $khachInfo = $this->khachHangModel->getKhachHangWithNguoiDung($khachHangId);
        if (!$khachInfo) {
            $_SESSION['error'] = 'Không tìm thấy thông tin khách hàng.';
            header('Location: index.php?act=hdv/checkInKhach&lich_id=' . $lichKhoiHanhId);
            exit();
        }

        $gioiTinh = $khachInfo['gioi_tinh'] ?? 'Khac';
        if ($gioiTinh === 'Nữ') {
            $gioiTinh = 'Nu';
        } elseif ($gioiTinh === 'Khác') {
            $gioiTinh = 'Khac';
        }

        $existing = $this->checkinKhachModel->findOne($lichKhoiHanhId, $bookingId, $khachHangId);
        $checkinTime = null;
        $checkoutTime = null;

        if ($trangThai === 'DaCheckIn') {
            $checkinTime = $existing['checkin_time'] ?? date('Y-m-d H:i:s');
        } elseif ($trangThai === 'DaCheckOut') {
            $checkinTime = $existing['checkin_time'] ?? date('Y-m-d H:i:s');
            $checkoutTime = $existing['checkout_time'] ?? date('Y-m-d H:i:s');
        }

        $data = [
            'booking_id' => $bookingId,
            'khach_hang_id' => $khachHangId,
            'lich_khoi_hanh_id' => $lichKhoiHanhId,
            'ho_ten' => $khachInfo['ho_ten'] ?? '',
            'ngay_sinh' => $khachInfo['ngay_sinh'] ?? null,
            'gioi_tinh' => $gioiTinh ?: 'Khac',
            'quoc_tich' => 'Việt Nam',
            'dia_chi' => $khachInfo['dia_chi'] ?? null,
            'so_dien_thoai' => $khachInfo['so_dien_thoai'] ?? null,
            'email' => $khachInfo['email'] ?? null,
            'trang_thai' => $trangThai,
            'ghi_chu' => $ghiChu,
            'checkin_time' => $checkinTime,
            'checkout_time' => $checkoutTime
        ];

        $result = false;
        if ($existing) {
            $result = $this->checkinKhachModel->update($existing['id'], $data);
        } else {
            $result = $this->checkinKhachModel->insert($data);
        }

        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Cập nhật điểm danh thành công.' : 'Không thể cập nhật điểm danh.';

        header('Location: index.php?act=hdv/checkInKhach&lich_id=' . $lichKhoiHanhId);
        exit();
    }

    public function quanLyYeuCauDacBiet() {
        $nhanSu = $this->getCurrentHDV();
        $nhanSuId = $nhanSu['nhan_su_id'];
        
        // Sử dụng method mới từ model để lấy yêu cầu đặc biệt (đồng bộ với Admin)
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'tour_id' => isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0,
            'muc_do_uu_tien' => $_GET['muc_do_uu_tien'] ?? '',
            'trang_thai' => $_GET['trang_thai'] ?? '',
            'loai_yeu_cau' => $_GET['loai_yeu_cau'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $requests = $this->yeuCauDacBietModel->getAllForHDV($nhanSuId, $filters);
     
$stats = $this->yeuCauDacBietModel->getSummaryStatsForHDV($nhanSuId, $filters);
        $histories = $this->yeuCauDacBietModel->getHistoriesByRequestIds(array_column($requests, 'id'));

        // Lấy danh sách tour HDV phụ trách (unique)
        $lichKhoiHanhList = $this->getLichKhoiHanhByHDV($nhanSuId);
        $tourListMap = [];
        foreach ($lichKhoiHanhList as $lich) {
            if (!empty($lich['tour_id'])) {
                $tourId = (int)$lich['tour_id'];
                if (!isset($tourListMap[$tourId])) {
                    $tourListMap[$tourId] = $tourId;
                }
            }
        }
        $tourList = array_values($tourListMap);
        
        // Lấy danh sách booking để tạo yêu cầu mới
        $bookingList = [];
        $lichIds = array_column($lichKhoiHanhList, 'id');
        $lichMap = [];
        foreach ($lichKhoiHanhList as $lich) {
            $lichMap[(int)$lich['id']] = $lich;
        }
        $groupedBookings = $this->bookingModel->getKhachByLichKhoiHanhIdsGrouped($lichIds);
        foreach ($groupedBookings as $lichId => $bookings) {
            $lich = $lichMap[$lichId] ?? [];
            foreach ($bookings as $bk) {
                $bookingList[] = [
                    'booking_id' => $bk['booking_id'],
                    'ten_tour' => $lich['ten_tour'] ?? '',
                    'ngay_khoi_hanh' => $lich['ngay_khoi_hanh'] ?? '',
                    'ho_ten' => $bk['ho_ten'] ?? '',
                    'so_dien_thoai' => $bk['so_dien_thoai'] ?? ''
                ];
            }
        }
        
        require 'views/hdv/quan_ly_yeu_cau_dac_biet.php';
    }

    public function updateYeuCauDacBiet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=hdv/quanLyYeuCauDacBiet');
            exit();
        }

        $this->requirePostCsrf('hdv/quanLyYeuCauDacBiet');

        $nhanSu = $this->getCurrentHDV();
        $nhanSuId = $nhanSu['nhan_su_id'];

        $schema = validateInputSchema([
            'lich_khoi_hanh_id' => ['type' => 'id', 'required' => true],
            'tour_id' => ['type' => 'id', 'required' => true],
            'khach_hang_id' => ['type' => 'id', 'required' => true],
            'booking_id' => ['type' => 'id', 'required' => true],
            'noi_dung' => ['type' => 'string', 'required' => true, 'min' => 3, 'max' => 2000],
            'redirect_to' => ['type' => 'string', 'required' => false, 'max' => 100],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu yeu cau dac biet khong hop le.');
            $_SESSION['error'] = 'Du lieu yeu cau dac biet khong hop le.';
            header('Location: index.php?act=hdv/checkInKhach');
            exit();
        }

        $lichKhoiHanhId = (int)($schema['data']['lich_khoi_hanh_id'] ?? 0);
        $tourId = (int)($schema['data']['tour_id'] ?? 0);
        $khachHangId = (int)($schema['data']['khach_hang_id'] ?? 0);
        $bookingId = (int)($schema['data']['booking_id'] ?? 0);
        $noiDung = (string)($schema['data']['noi_dung'] ?? '');

        if ($lichKhoiHanhId <= 0 || $tourId <= 0 || $khachHangId <= 0 || $bookingId <= 0) {
            $_SESSION['error'] = 'Thiếu thông tin yêu cầu đặc biệt.';
            header('Location: index.php?act=hdv/checkInKhach');
            exit();
        }

        $assigned = $this->getLichKhoiHanhByHDV($nhanSuId);
        $allowed = [];
        foreach ($assigned as $lich) {
            $allowed[(int)$lich['id']] = (int)$lich['tour_id'];
        }

        if (!isset($allowed[$lichKhoiHanhId]) || $allowed[$lichKhoiHanhId] !== $tourId) {
            $_SESSION['error'] = 'Bạn không được phép cập nhật yêu cầu cho tour này.';
            header('Location: index.php?act=hdv/checkInKhach');
            exit();
        }

        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking || (int)$booking['tour_id'] !== $tourId) {
            $_SESSION['error'] = 'Booking không hợp lệ.';
            header('Location: index.php?act=hdv/checkInKhach&lich_id=' . $lichKhoiHanhId);
            exit();
        }

        if ($noiDung === '') {
            $_SESSION['error'] = 'Vui lòng nhập nội dung yêu cầu đặc biệt.';
            header('Location: index.php?act=hdv/checkInKhach&lich_id=' . $lichKhoiHanhId);
            exit();
        }

        $result = $this->yeuCauDacBietModel->upsert($khachHangId, $tourId, $noiDung);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Đã lưu yêu cầu đặc biệt.' : 'Không thể lưu yêu cầu.';

        $redirectTo = (string)($schema['data']['redirect_to'] ?? 'hdv/quanLyYeuCauDacBiet');
        if ($redirectTo === 'hdv/checkInKhach') {
            header('Location: index.php?act=hdv/checkInKhach&lich_id=' . $lichKhoiHanhId);
        } elseif ($redirectTo === 'hdv/lichLamViec') {
            header('Location: index.php?act=hdv/lichLamViec');
        } else {
            header('Location: index.php?act=hdv/quanLyYeuCauDacBiet');
        }
        exit();
    }
    
    /**
     * Trang chủ HDV Dashboard
     */
    public function dashboard() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        // Lấy thông tin nhân sự HDV
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai 
                FROM nhan_su ns 
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id 
                WHERE ns.nguoi_dung_id = ? AND ns.vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $hdv_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$hdv_info) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=tour/index');
            exit();
        }
        
        $nhanSuId = $hdv_info['nhan_su_id'];
        
        // Thống kê & danh sách tour dựa trên cả hai nguồn:
        // - HDV chính trong lich_khoi_hanh.hdv_id
        // - Được phân bổ trong phan_bo_nhan_su với trạng thái 'DaXacNhan'
        $stats = [
            'upcoming_tours'   => 0,
            'ongoing_tours'    => 0,
            'completed_tours'  => 0,
            'rating'           => 0, // TODO: tính từ bảng đánh giá nếu có
        ];

        $today = date('Y-m-d');
        $sevenDaysLater = date('Y-m-d', strtotime('+7 days'));

        $sql = "SELECT DISTINCT lkh.*, t.ten_tour,
                       pbn.id as phan_bo_id, 
                       pbn.trang_thai as phan_bo_trang_thai, 
                       pbn.vai_tro as phan_bo_vai_tro
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                LEFT JOIN phan_bo_nhan_su pbn 
                    ON lkh.id = pbn.lich_khoi_hanh_id 
                    AND pbn.nhan_su_id = ?
                WHERE (lkh.hdv_id = ? OR (pbn.nhan_su_id IS NOT NULL AND pbn.trang_thai = 'DaXacNhan'))";

        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId, $nhanSuId]);
        $allTours = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today_tours = [];
        $upcoming_tours = [];

        foreach ($allTours as $tour) {
            $status = $tour['trang_thai'] ?? '';
            switch ($status) {
                case 'SapKhoiHanh':
                    $stats['upcoming_tours']++;
                    break;
                case 'DangChay':
                    $stats['ongoing_tours']++;
                    break;
                case 'HoanThanh':
                    $stats['completed_tours']++;
                    break;
            }

            $ngayKhoiHanh = $tour['ngay_khoi_hanh'] ?? null;
            if ($ngayKhoiHanh) {
                if ($ngayKhoiHanh === $today) {
                    $today_tours[] = $tour;
                }
                if ($ngayKhoiHanh >= $today && $ngayKhoiHanh <= $sevenDaysLater) {
                    $upcoming_tours[] = $tour;
                }
            }
        }
        
        // Thông báo mới
        $recent_notifications = $this->hdvMgmtModel->getThongBao($nhanSuId, 5);
        
        // Đếm thông báo chưa đọc
        $notifications_count = $this->getHdvUnreadNotificationCount($nhanSuId);
        
        require 'views/hdv/dashboard.php';
    }

    public function notificationCounts() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $nhanSuId = $this->resolveCurrentHdvId();
            if ($nhanSuId <= 0) {
                http_response_code(401);
                echo json_encode(['success' => false, 'unread' => 0], JSON_UNESCAPED_UNICODE);
                exit();
            }

            echo json_encode([
                'success' => true,
                'unread' => $this->getHdvUnreadNotificationCount($nhanSuId),
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'unread' => 0], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
    
    /**
     * Xem lịch trình tour
     */
    public function tours() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=tour/index');
            exit();
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        $filter_status = $_GET['status'] ?? 'all';
        
        // Lấy danh sách tour đã xác nhận (HDV chính hoặc phân bổ đã xác nhận)
        $sql = "SELECT DISTINCT lkh.*, t.ten_tour, 
                pbn.id as phan_bo_id, pbn.trang_thai as phan_bo_trang_thai, pbn.vai_tro as phan_bo_vai_tro
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                WHERE (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))";
        
        if ($filter_status !== 'all') {
            $sql .= " AND lkh.trang_thai = ?";
        }
        
        $sql .= " ORDER BY lkh.ngay_khoi_hanh DESC";
        
        $params = [$nhanSuId, $nhanSuId, $nhanSuId];
        if ($filter_status !== 'all') {
            $params[] = $filter_status;
        }
        
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute($params);
        $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy danh sách phân bổ chờ xác nhận
        $sql = "SELECT pbn.*, lkh.id as lich_khoi_hanh_id, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc,
                t.ten_tour, t.tour_id
                FROM phan_bo_nhan_su pbn
                LEFT JOIN lich_khoi_hanh lkh ON pbn.lich_khoi_hanh_id = lkh.id
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                WHERE pbn.nhan_su_id = ? AND pbn.trang_thai = 'ChoXacNhan'
                ORDER BY lkh.ngay_khoi_hanh ASC";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId]);
        $phanBoChoXacNhan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/hdv/tours.php';
    }
    
    /**
     * Xác nhận hoặc từ chối phân bổ nhân sự
     */
    public function xacNhanPhanBo() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        $nhanSuId = (int)$nhanSu['nhan_su_id'];
        $phanBoId = requestId('phan_bo_id', 0, 'REQUEST') ?? 0;
        $action = requestString('action', '', 'REQUEST'); // 'xac_nhan' hoặc 'tu_choi'
        
        if ($phanBoId <= 0 || !in_array($action, ['xac_nhan', 'tu_choi'], true)) {
            $_SESSION['error'] = 'Thông tin không hợp lệ.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        // Kiểm tra phân bổ có thuộc về HDV này không
        $phanBo = $this->phanBoNhanSuModel->findById($phanBoId);
        if (!$phanBo || $phanBo['nhan_su_id'] != $nhanSuId) {
            $_SESSION['error'] = 'Bạn không có quyền thực hiện thao tác này.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        // Cập nhật trạng thái
        $trangThai = ($action === 'xac_nhan') ? 'DaXacNhan' : 'TuChoi';
        $result = $this->phanBoNhanSuModel->updateTrangThai($phanBoId, $trangThai);
        
        if ($result) {
            $_SESSION['success'] = $action === 'xac_nhan' 
                ? 'Đã xác nhận phân bổ nhân sự thành công!' 
                : 'Đã từ chối phân bổ nhân sự.';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật trạng thái.';
        }
        
        header('Location: index.php?act=hdv/tours');
        exit();
    }
    
    /**
     * Chi tiết tour
     */
    public function tourDetail() {
        $userId = $_SESSION['user_id'] ?? null;
        $tour_id = $_GET['id'] ?? 0;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        // Lấy chi tiết tour và kiểm tra quyền (HDV chính hoặc phân bổ đã xác nhận)
        $nhanSuId = $nhanSu['nhan_su_id'];
        $sql = "SELECT DISTINCT 
                    lkh.*,
                    t.ten_tour, t.loai_tour, t.mo_ta
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                WHERE lkh.id = ? 
                AND (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId, $tour_id, $nhanSuId, $nhanSuId]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tour) {
            $_SESSION['error'] = 'Không tìm thấy tour hoặc bạn không có quyền truy cập tour này. Tour phải được xác nhận trước khi xem.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        // Nếu không có ten_tour, thử lấy trực tiếp từ bảng tour bằng tour_id
        if (empty($tour['ten_tour']) && !empty($tour['tour_id'])) {
            $sql2 = "SELECT ten_tour, loai_tour, mo_ta FROM tour WHERE tour_id = ? LIMIT 1";
            $stmt2 = $this->nhanSuModel->conn->prepare($sql2);
            $stmt2->execute([$tour['tour_id']]);
            $tourInfo = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($tourInfo) {
                if (!empty($tourInfo['ten_tour'])) {
                    $tour['ten_tour'] = $tourInfo['ten_tour'];
                }
                if (empty($tour['loai_tour']) && !empty($tourInfo['loai_tour'])) {
                    $tour['loai_tour'] = $tourInfo['loai_tour'];
                }
                if (empty($tour['mo_ta']) && !empty($tourInfo['mo_ta'])) {
                    $tour['mo_ta'] = $tourInfo['mo_ta'];
                }
            }
        }
        
        // Lấy lịch trình chi tiết theo tour_id (nếu có)
        $lichTrinhList = [];
        if (!empty($tour['tour_id'])) {
            $lichTrinhList = $this->tourModel->getLichTrinhByTourId($tour['tour_id']);
        }
        require 'views/hdv/tour_detail.php';
    }
    
    /**
     * Xem danh sách khách
     */
    public function khach() {
        $userId = $_SESSION['user_id'] ?? null;
        $tour_id = $_GET['tour_id'] ?? 0;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        $khach_list = [];
        $tour = null;
        
        if ($tour_id > 0) {
            // Kiểm tra quyền (HDV chính hoặc phân bổ đã xác nhận)
            $sql = "SELECT DISTINCT lkh.*, t.ten_tour 
                    FROM lich_khoi_hanh lkh 
                    LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                    LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                    WHERE lkh.id = ? 
                    AND (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$nhanSuId, $tour_id, $nhanSuId, $nhanSuId]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tour) {
                // Lấy danh sách từng khách từ tour_checkin theo lich_khoi_hanh_id
                $khach_list = $this->checkinKhachModel->getByLichKhoiHanh($tour_id);
                
                // Nếu chưa có khách trong tour_checkin, lấy từ booking và tạo danh sách tạm
                if (empty($khach_list)) {
                    $bookings = $this->bookingModel->getKhachByTourAndNgayKhoiHanh(
                        $tour['tour_id'],
                        $tour['ngay_khoi_hanh']
                    );
                    
                    // Tạo danh sách khách từ booking (mỗi booking có thể có nhiều người)
                    foreach ($bookings as $booking) {
                        $soNguoi = (int)($booking['so_nguoi'] ?? 1);
                        for ($i = 0; $i < $soNguoi; $i++) {
                            $khach_list[] = [
                                'id' => null,
                                'booking_id' => $booking['booking_id'],
                                'khach_hang_id' => $booking['khach_hang_id'],
                                'ho_ten' => $booking['ho_ten'] . ($soNguoi > 1 ? ' #' . ($i + 1) : ''),
                                'so_cmnd' => null,
                                'so_passport' => null,
                                'ngay_sinh' => null,
                                'gioi_tinh' => null,
                                'quoc_tich' => 'Việt Nam',
                                'dia_chi' => $booking['dia_chi'] ?? null,
                                'so_dien_thoai' => $booking['so_dien_thoai'] ?? null,
                                'email' => $booking['email'] ?? null,
                                'trang_thai' => 'ChuaCheckIn',
                                'ghi_chu' => null,
                                'yeu_cau_dac_biet' => $booking['yeu_cau_dac_biet'] ?? null
                            ];
                        }
                    }
                }
            }
        }
        
        // Lấy danh sách tour đang chạy
        $sql = "SELECT lkh.id, lkh.ngay_khoi_hanh, t.ten_tour 
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                WHERE lkh.hdv_id = ? AND lkh.trang_thai IN ('DangChay', 'SapKhoiHanh')
                ORDER BY lkh.ngay_khoi_hanh ASC";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId]);
        $tours_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/hdv/khach.php';
    }
    
    /**
     * Xem/thêm/cập nhật nhật ký tour
     */
    public function nhatKy() {
        $userId = $_SESSION['user_id'] ?? null;
        $tour_id = $_GET['tour_id'] ?? 0;
        $edit_id = $_GET['edit_id'] ?? 0;
        
        // Xóa thông báo lỗi cũ về quyền
        if (isset($_SESSION['error']) && strpos($_SESSION['error'], 'quyền') !== false) {
            unset($_SESSION['error']);
        }
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        // Lấy nhan_su_id - không kiểm tra vai_tro
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin nhân sự.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        $nhat_ky_list = [];
        $tour = null;
        $edit_entry = null;
        
        if ($tour_id > 0) {
            // Lấy tour - không kiểm tra quyền
            $sql = "SELECT lkh.*, t.ten_tour, t.tour_id as tour_table_id
                    FROM lich_khoi_hanh lkh 
                    LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                    WHERE lkh.id = ?";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$tour_id]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tour) {
                $_SESSION['error'] = 'Tour không tồn tại (ID: ' . $tour_id . ')';
                $tour_id = 0;
            } else {
                // Lấy nhật ký tour - sử dụng tour_id từ bảng tour
                $sql = "SELECT * FROM nhat_ky_tour 
                        WHERE tour_id = ? AND nhan_su_id = ?
                          AND " . $this->notDeletedClause('nhat_ky_tour') . "
                        ORDER BY ngay_ghi DESC, id DESC";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([$tour['tour_table_id'], $nhanSuId]);
                $nhat_ky_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Nếu có edit_id, lấy dữ liệu để sửa
                if ($edit_id > 0) {
                    $sql = "SELECT * FROM nhat_ky_tour WHERE id = ? AND nhan_su_id = ? AND " . $this->notDeletedClause('nhat_ky_tour');
                    $stmt = $this->nhanSuModel->conn->prepare($sql);
                    $stmt->execute([$edit_id, $nhanSuId]);
                    $edit_entry = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        }
        
        // Lấy danh sách tour - không kiểm tra quyền
        $sql = "SELECT lkh.id, lkh.ngay_khoi_hanh, t.ten_tour 
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                ORDER BY lkh.ngay_khoi_hanh DESC";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute();
        $tours_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/hdv/nhat_ky.php';
    }
    
    /**
     * Check-in và điểm danh
     */
    public function checkin() {
        $userId = $_SESSION['user_id'] ?? null;
        $tour_id = $_GET['tour_id'] ?? 0;
        $diem_id = $_GET['diem_id'] ?? 0;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        $tour = null;
        $diem_checkin_list = [];
        $diem_hien_tai = null;
        $khach_list = [];
        $bookings = []; // For debugging
        
        if ($tour_id > 0) {
            // Kiểm tra xem HDV có được phân bổ vào tour/lịch khởi hành này và đã xác nhận không
            // Kiểm tra qua: lich_khoi_hanh.hdv_id HOẶC phan_bo_nhan_su đã xác nhận
            // tour_id trong URL có thể là lich_khoi_hanh.id hoặc tour.tour_id
            $sql = "SELECT DISTINCT 
                        lkh.id,
                        lkh.tour_id,
                        lkh.ngay_khoi_hanh,
                        lkh.ngay_ket_thuc,
                        lkh.gio_xuat_phat,
                        lkh.gio_ket_thuc,
                        lkh.diem_tap_trung,
                        lkh.so_cho,
                        lkh.hdv_id,
                        lkh.trang_thai,
                        lkh.ghi_chu,
                        t.ten_tour
                    FROM lich_khoi_hanh lkh 
                    LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                    LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                    WHERE (lkh.tour_id = ? OR lkh.id = ?)
                    AND (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))
                    LIMIT 1";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$nhanSuId, $tour_id, $tour_id, $nhanSuId, $nhanSuId]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tour) {
                $_SESSION['error'] = 'Bạn không có quyền truy cập tour này hoặc tour chưa được xác nhận.';
                header('Location: index.php?act=hdv/checkin');
                exit();
            }
            
            if ($tour && !empty($tour['tour_id']) && !empty($tour['ngay_khoi_hanh'])) {
                // Lấy danh sách điểm check-in - dùng tour_id từ lich_khoi_hanh
                $sql = "SELECT * FROM diem_checkin 
                        WHERE tour_id = ?
                          AND " . $this->notDeletedClause('diem_checkin') . "
                        ORDER BY thu_tu ASC, thoi_gian_du_kien ASC";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([$tour['tour_id']]);
                $diem_checkin_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Nếu có điểm check-in được chọn
                if ($diem_id > 0) {
                    $sql = "SELECT * FROM diem_checkin WHERE id = ? AND tour_id = ? AND " . $this->notDeletedClause('diem_checkin');
                    $stmt = $this->nhanSuModel->conn->prepare($sql);
                    $stmt->execute([$diem_id, $tour['tour_id']]);
                    $diem_hien_tai = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($diem_hien_tai) {
                        // Lấy danh sách khách giống logic admin (Booking model)
                        // Dùng tour_id và ngay_khoi_hanh từ lich_khoi_hanh (không phải từ bảng tour)
                        // Đảm bảo ngay_khoi_hanh là DATE format (YYYY-MM-DD)
                        
                        // Debug: Log giá trị để kiểm tra
                        error_log("HDV Checkin Debug - tour_id from URL: " . $tour_id);
                        error_log("HDV Checkin Debug - lich_khoi_hanh.id: " . ($tour['id'] ?? 'NULL'));
                        error_log("HDV Checkin Debug - lich_khoi_hanh.tour_id: " . ($tour['tour_id'] ?? 'NULL'));
                        error_log("HDV Checkin Debug - lich_khoi_hanh.ngay_khoi_hanh: " . ($tour['ngay_khoi_hanh'] ?? 'NULL'));
                        
                        // Debug: Kiểm tra tất cả booking có ngay_khoi_hanh này
                        $sqlDebug = "SELECT booking_id, tour_id, ngay_khoi_hanh, trang_thai, khach_hang_id 
                                     FROM booking 
                                     WHERE DATE(ngay_khoi_hanh) = DATE(?) 
                                     ORDER BY booking_id";
                        $stmtDebug = $this->nhanSuModel->conn->prepare($sqlDebug);
                        $stmtDebug->execute([$tour['ngay_khoi_hanh']]);
                        $allBookings = $stmtDebug->fetchAll(PDO::FETCH_ASSOC);
                        error_log("HDV Checkin Debug - All bookings with ngay_khoi_hanh " . $tour['ngay_khoi_hanh'] . ": " . json_encode($allBookings));
                        
                        // Debug: Kiểm tra booking với tour_id này
                        $sqlDebug2 = "SELECT booking_id, tour_id, ngay_khoi_hanh, trang_thai, khach_hang_id 
                                      FROM booking 
                                      WHERE tour_id = ? 
                                      ORDER BY booking_id";
                        $stmtDebug2 = $this->nhanSuModel->conn->prepare($sqlDebug2);
                        $stmtDebug2->execute([(int)$tour['tour_id']]);
                        $bookingsByTourId = $stmtDebug2->fetchAll(PDO::FETCH_ASSOC);
                        error_log("HDV Checkin Debug - All bookings with tour_id " . $tour['tour_id'] . ": " . json_encode($bookingsByTourId));
                        
                        // Thử lấy booking theo lich_khoi_hanh.id trước (chính xác hơn)
                        // Nếu không có, fallback về tour_id + ngay_khoi_hanh
                        $bookings = $this->bookingModel->getKhachByLichKhoiHanhId($tour['id']);
                        
                        // Nếu không tìm thấy, thử cách cũ
                        if (empty($bookings)) {
                            $bookings = $this->bookingModel->getKhachByTourAndNgayKhoiHanh(
                                (int)$tour['tour_id'],
                                $tour['ngay_khoi_hanh']
                            );
                        }
                        
                        error_log("HDV Checkin Debug - bookings count: " . count($bookings));

                        $khach_list = [];

                        // Đảm bảo danh sách khách hàng từ tour_checkin khớp với booking
                        // Lấy booking trước để đảm bảo chỉ hiển thị khách từ booking hợp lệ
                        if (!empty($bookings)) {
                            $bookingIds = array_column($bookings, 'booking_id');
                            
                            // Lấy khách chi tiết từ tour_checkin CHỈ từ các booking hợp lệ
                            require_once 'models/CheckinKhach.php';
                            $checkinModel = new CheckinKhach();
                            
                            // Lấy tất cả khách chi tiết theo lich_khoi_hanh_id và booking_id
                            $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
                            $sql = "SELECT * FROM tour_checkin 
                                    WHERE lich_khoi_hanh_id = ? 
                                      AND booking_id IN ($placeholders)
                                    ORDER BY booking_id ASC, id ASC";
                        $stmt = $this->nhanSuModel->conn->prepare($sql);
                            $params = array_merge([$tour['id']], $bookingIds);
                            $stmt->execute($params);
                            $khachChiTiet = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Lấy trạng thái check-in từ checkin_khach theo diem_checkin_id + booking_id
                            $checkinMap = [];
                            if (!empty($bookingIds) && $diem_id > 0) {
                                $placeholders2 = implode(',', array_fill(0, count($bookingIds), '?'));
                                $sql = "SELECT * FROM checkin_khach 
                                        WHERE diem_checkin_id = ? 
                                          AND booking_id IN ($placeholders2)";
                                $stmt = $this->nhanSuModel->conn->prepare($sql);
                                $params2 = array_merge([$diem_id], $bookingIds);
                                $stmt->execute($params2);
                                $checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Map theo booking_id + khach_hang_id để gán cho từng khách cụ thể
                                foreach ($checkins as $ck) {
                                    $key = $ck['booking_id'] . '_' . ($ck['khach_hang_id'] ?? '0');
                                    $checkinMap[$key] = $ck;
                                }
                            }

                            // Gán trạng thái check-in cho từng khách
                            // Ưu tiên: checkin_khach (theo điểm check-in) > tour_checkin.trang_thai (tổng quát)
                            foreach ($khachChiTiet as $khach) {
                                $key = $khach['booking_id'] . '_' . ($khach['khach_hang_id'] ?? '0');
                                $ck = $checkinMap[$key] ?? null;
                                
                                // Lấy trạng thái từ checkin_khach (theo điểm check-in) nếu có
                                $checkinStatus = null;
                                if ($ck) {
                                    // Map từ checkin_khach.trang_thai sang format hiển thị
                                    $statusMap = [
                                        'chua_checkin' => 'chua_checkin',
                                        'da_checkin' => 'da_checkin',
                                        'vang_mat' => 'vang_mat',
                                        're_gio' => 're_gio'
                                    ];
                                    $checkinStatus = $statusMap[$ck['trang_thai']] ?? null;
                                }
                                
                                // Nếu không có checkin_khach, dùng tour_checkin.trang_thai (tổng quát)
                                if (!$checkinStatus && !empty($khach['trang_thai'])) {
                                    // Map từ tour_checkin.trang_thai sang format hiển thị
                                    $tourCheckinStatusMap = [
                                        'ChuaCheckIn' => 'chua_checkin',
                                        'DaCheckIn' => 'da_checkin',
                                        'DaCheckOut' => 'da_checkin' // Check-out vẫn hiển thị là đã check-in
                                    ];
                                    $checkinStatus = $tourCheckinStatusMap[$khach['trang_thai']] ?? 'chua_checkin';
                                }
                                
                                // Nếu vẫn không có, mặc định là chưa check-in
                                if (!$checkinStatus) {
                                    $checkinStatus = 'chua_checkin';
                                }
                                
                                $khach_list[] = array_merge($khach, [
                                    'checkin_status' => $checkinStatus,
                                    'thoi_gian_checkin' => $ck['thoi_gian_checkin'] ?? null,
                                    'checkin_note' => $ck['ghi_chu'] ?? null,
                                ]);
                            }
                            
                            // Nếu không có tour_checkin nhưng có booking, fallback về booking (logic cũ)
                            if (empty($khachChiTiet) && !empty($bookings)) {
                                $checkinMap = [];
                                if (!empty($bookingIds) && $diem_id > 0) {
                                    $placeholders3 = implode(',', array_fill(0, count($bookingIds), '?'));
                                    $sql = "SELECT * FROM checkin_khach 
                                            WHERE diem_checkin_id = ? 
                                              AND booking_id IN ($placeholders3)";
                                    $stmt = $this->nhanSuModel->conn->prepare($sql);
                                    $params3 = array_merge([$diem_id], $bookingIds);
                                    $stmt->execute($params3);
                                    $checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($checkins as $ck) {
                                        $checkinMap[$ck['booking_id']] = $ck;
                                    }
                                }

                                foreach ($bookings as $bk) {
                                    $ck = $checkinMap[$bk['booking_id']] ?? null;
                                    $khach_list[] = array_merge($bk, [
                                        'checkin_status' => $ck['trang_thai'] ?? null,
                                        'thoi_gian_checkin' => $ck['thoi_gian_checkin'] ?? null,
                                        'checkin_note' => $ck['ghi_chu'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // Lấy danh sách tour đang chạy mà HDV được phân bổ và đã xác nhận
        // Kiểm tra qua: lich_khoi_hanh.hdv_id HOẶC phan_bo_nhan_su đã xác nhận
        $sql = "SELECT DISTINCT lkh.id, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc, t.ten_tour, t.tour_id
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                WHERE lkh.trang_thai IN ('DangChay', 'SapKhoiHanh')
                AND (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))
                ORDER BY lkh.ngay_khoi_hanh ASC";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId, $nhanSuId, $nhanSuId]);
        $tours_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/hdv/checkin.php';
    }
    
    /**
     * Lưu điểm check-in
     */
    public function saveDiemCheckin() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/checkin');
            exit;
        }

        $this->requirePostCsrf('hdv/checkin');

        $schema = validateInputSchema([
            'tour_id' => ['type' => 'id', 'required' => true],
            'diem_id' => ['type' => 'id', 'required' => false],
            'ten_diem' => ['type' => 'string', 'required' => true, 'min' => 2, 'max' => 255],
            'loai_diem' => ['type' => 'string', 'required' => false, 'max' => 50],
            'thoi_gian_du_kien' => ['type' => 'string', 'required' => false, 'max' => 30],
            'ghi_chu' => ['type' => 'string', 'required' => false, 'max' => 1000],
            'thu_tu' => ['type' => 'id', 'required' => false],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu diem check-in khong hop le.');
            $_SESSION['error'] = 'Du lieu diem check-in khong hop le.';
            header('Location: index.php?act=hdv/checkin');
            exit;
        }

        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        $diem_id = (int)($schema['data']['diem_id'] ?? 0);
        
        // Normalize thoi_gian_du_kien: chuyển chuỗi rỗng thành NULL
        $thoiGianDuKien = $schema['data']['thoi_gian_du_kien'] ?? null;
        if ($thoiGianDuKien === '' || $thoiGianDuKien === null) {
            $thoiGianDuKien = null;
        }
        
        $data = [
            'tour_id' => $tour_id,
            'ten_diem' => (string)($schema['data']['ten_diem'] ?? ''),
            'loai_diem' => (string)($schema['data']['loai_diem'] ?? 'tap_trung'),
            'thoi_gian_du_kien' => $thoiGianDuKien,
            'ghi_chu' => $schema['data']['ghi_chu'] ?? null,
            'thu_tu' => (int)($schema['data']['thu_tu'] ?? 1)
        ];
        
        try {
            if ($diem_id > 0) {
                // Update
                $sql = "UPDATE diem_checkin SET 
                        ten_diem = ?, loai_diem = ?, thoi_gian_du_kien = ?, 
                        ghi_chu = ?, thu_tu = ?
                        WHERE id = ?";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([
                    $data['ten_diem'], $data['loai_diem'], $data['thoi_gian_du_kien'],
                    $data['ghi_chu'], $data['thu_tu'], $diem_id
                ]);
                $_SESSION['success'] = 'Cập nhật điểm check-in thành công';
            } else {
                // Insert
                $sql = "INSERT INTO diem_checkin (tour_id, ten_diem, loai_diem, thoi_gian_du_kien, ghi_chu, thu_tu) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([
                    $data['tour_id'], $data['ten_diem'], $data['loai_diem'],
                    $data['thoi_gian_du_kien'], $data['ghi_chu'], $data['thu_tu']
                ]);
                $_SESSION['success'] = 'Thêm điểm check-in thành công';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: index.php?act=hdv/checkin&tour_id=' . $tour_id);
        exit;
    }
    
    /**
     * Xóa điểm check-in
     */
    public function deleteDiemCheckin() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/tours');
            exit;
        }

        $this->requirePostCsrf('hdv/checkin');

        $schema = validateInputSchema([
            'id' => ['type' => 'id', 'required' => true],
            'tour_id' => ['type' => 'id', 'required' => false],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu xoa diem check-in khong hop le.');
            $_SESSION['error'] = 'Du lieu xoa diem check-in khong hop le.';
            header('Location: index.php?act=hdv/checkin');
            exit;
        }

        $diem_id = (int)($schema['data']['id'] ?? 0);
        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        
        try {
            if ($this->hasColumn('diem_checkin', 'deleted_at')) {
                $sql = "UPDATE diem_checkin SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
            } else {
                $sql = "DELETE FROM diem_checkin WHERE id = ?";
            }
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$diem_id]);
            $_SESSION['success'] = 'Xóa điểm check-in thành công';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: index.php?act=hdv/checkin&tour_id=' . $tour_id);
        exit;
    }
    
    /**
     * Lưu trạng thái check-in của khách
     */
    public function saveCheckinKhach() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yeu cau khong hop le']);
            exit;
        }

        $this->requirePostCsrf('hdv/checkin');

        $userId = $_SESSION['user_id'] ?? null;
        
        // Lấy nhan_su_id
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin HDV']);
            exit;
        }
        
        $schema = validateInputSchema([
            'diem_checkin_id' => ['type' => 'id', 'required' => true],
            'booking_id' => ['type' => 'id', 'required' => true],
            'trang_thai' => ['type' => 'string', 'required' => true, 'enum' => ['da_checkin', 'vang_mat', 're_gio', 'chua_checkin']],
            'ghi_chu' => ['type' => 'string', 'required' => false, 'max' => 1000],
        ], 'POST');
        if (!$schema['ok']) {
            echo json_encode(['success' => false, 'message' => 'Du lieu khong hop le']);
            exit;
        }

        $diem_checkin_id = (int)($schema['data']['diem_checkin_id'] ?? 0);
        $booking_id = (int)($schema['data']['booking_id'] ?? 0);
        $trang_thai = (string)($schema['data']['trang_thai'] ?? 'da_checkin');
        $ghi_chu = $schema['data']['ghi_chu'] ?? null;
        
        try {
            // Lấy thông tin điểm check-in để biết lich_khoi_hanh_id
            $sql = "SELECT tour_id FROM diem_checkin WHERE id = ? AND " . $this->notDeletedClause('diem_checkin');
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$diem_checkin_id]);
            $diemCheckin = $stmt->fetch();
            
            if (!$diemCheckin) {
                throw new Exception('Không tìm thấy điểm check-in');
            }
            
            // Lấy lich_khoi_hanh_id từ booking
            $sql = "SELECT lkh.id as lich_khoi_hanh_id 
                    FROM booking b
                    INNER JOIN lich_khoi_hanh lkh ON b.tour_id = lkh.tour_id 
                        AND DATE(b.ngay_khoi_hanh) = DATE(lkh.ngay_khoi_hanh)
                    WHERE b.booking_id = ? AND b.tour_id = ?
                    LIMIT 1";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$booking_id, $diemCheckin['tour_id']]);
            $lichInfo = $stmt->fetch();
            
            // Kiểm tra xem đã có bản ghi chưa
            $sql = "SELECT id FROM checkin_khach WHERE diem_checkin_id = ? AND booking_id = ?";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$diem_checkin_id, $booking_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update
                $sql = "UPDATE checkin_khach SET 
                        trang_thai = ?, thoi_gian_checkin = NOW(), ghi_chu = ?, nguoi_checkin_id = ?
                        WHERE id = ?";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([$trang_thai, $ghi_chu, $nhanSu['nhan_su_id'], $existing['id']]);
            } else {
                // Insert
                $sql = "INSERT INTO checkin_khach (diem_checkin_id, booking_id, trang_thai, thoi_gian_checkin, ghi_chu, nguoi_checkin_id) 
                        VALUES (?, ?, ?, NOW(), ?, ?)";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([$diem_checkin_id, $booking_id, $trang_thai, $ghi_chu, $nhanSu['nhan_su_id']]);
            }
            
            // Đồng bộ trạng thái với tour_checkin (nếu có lich_khoi_hanh_id)
            if ($lichInfo && !empty($lichInfo['lich_khoi_hanh_id'])) {
                $lichKhoiHanhId = $lichInfo['lich_khoi_hanh_id'];
                
                // Map trạng thái từ checkin_khach sang tour_checkin
                $tourCheckinStatus = null;
                if ($trang_thai === 'da_checkin') {
                    $tourCheckinStatus = 'DaCheckIn';
                } elseif ($trang_thai === 'vang_mat') {
                    // Vắng mặt không cập nhật tour_checkin (giữ nguyên trạng thái)
                    $tourCheckinStatus = null;
                } else {
                    // re_gio, chua_checkin: không cập nhật
                    $tourCheckinStatus = null;
                }
                
                // Cập nhật tour_checkin cho tất cả khách trong booking này
                if ($tourCheckinStatus) {
                    $sql = "UPDATE tour_checkin 
                            SET trang_thai = ?, checkin_time = NOW()
                            WHERE booking_id = ? AND lich_khoi_hanh_id = ?";
                    $stmt = $this->nhanSuModel->conn->prepare($sql);
                    $stmt->execute([$tourCheckinStatus, $booking_id, $lichKhoiHanhId]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Yêu cầu đặc biệt
     */
    /**
     * Yêu cầu đặc biệt
     */
    public function yeuCauDacBiet() {
        $userId = $_SESSION['user_id'] ?? null;
        $tour_id = $_GET['tour_id'] ?? 0;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        
        // Sử dụng filter để lấy yêu cầu
        $filters = [
            'keyword' => trim($_GET['keyword'] ?? ''),
            'tour_id' => $tour_id > 0 ? (int)$tour_id : 0,
            'muc_do_uu_tien' => $_GET['muc_do_uu_tien'] ?? '',
            'trang_thai' => $_GET['trang_thai'] ?? '',
            'loai_yeu_cau' => $_GET['loai_yeu_cau'] ?? '',
        ];
        
        // Lấy tất cả yêu cầu từ các tour HDV phụ trách
        $yeu_cau_list = $this->yeuCauDacBietModel->getAllForHDV($nhanSuId, $filters);
     
$stats = $this->yeuCauDacBietModel->getSummaryStatsForHDV($nhanSuId, $filters);
        
        // Lấy thông tin tour nếu có tour_id
        $tour = null;
        if ($tour_id > 0) {
            $sql = "SELECT DISTINCT lkh.*, t.ten_tour, t.tour_id 
                    FROM lich_khoi_hanh lkh 
                    LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                    LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                    WHERE lkh.id = ? 
                    AND (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$nhanSuId, $tour_id, $nhanSuId, $nhanSuId]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Lấy danh sách tour HDV phụ trách
        $sql = "SELECT DISTINCT lkh.id, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc, t.ten_tour, t.tour_id
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                WHERE (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))
                AND lkh.trang_thai IN ('DangChay', 'SapKhoiHanh')
                ORDER BY lkh.ngay_khoi_hanh ASC";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId, $nhanSuId, $nhanSuId]);
        $tours_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy danh sách booking để thêm yêu cầu (từ tất cả tour HDV phụ trách)
        $bookings_list = [];
        foreach ($tours_list as $t) {
            $sql = "SELECT b.booking_id, nd.ho_ten, b.so_nguoi, t.ten_tour, lkh.ngay_khoi_hanh
                    FROM booking b
                    INNER JOIN lich_khoi_hanh lkh ON b.tour_id = lkh.tour_id AND DATE(b.ngay_khoi_hanh) = DATE(lkh.ngay_khoi_hanh)
                    LEFT JOIN tour t ON b.tour_id = t.tour_id
                    LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                    LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                    WHERE lkh.id = ? AND (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')
                    ORDER BY nd.ho_ten ASC";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$t['id']]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $bookings_list = array_merge($bookings_list, $bookings);
        }
    
// --- Chuẩn hoá filter tour_id (nếu $_GET['tour_id'] thực ra là lich_khoi_hanh.id) ---
// Nếu đang xem chi tiết lich_khoi_hanh (đã lấy $tour phía trên), lấy tour_id thực từ $tour
if (!empty($tour) && !empty($tour['tour_id'])) {
    $filters['tour_id'] = (int)$tour['tour_id'];
} else {
    // Nếu không có $tour (không ở trang chi tiết), giữ filter tour_id nếu có từ query (hoặc 0 để lấy tất cả)
    $filters['tour_id'] = $filters['tour_id'] ?? 0;
}

// Gọi model sau khi đã đảm bảo filters['tour_id'] đúng
$yeu_cau_list = $this->yeuCauDacBietModel->getAllForHDV($nhanSuId, $filters);

$stats = $this->yeuCauDacBietModel->getSummaryStatsForHDV($nhanSuId, $filters);
        
        require 'views/hdv/yeu_cau_dac_biet.php';
    }
    
    /**
     * Lưu yêu cầu đặc biệt
     */
    public function saveYeuCauDacBiet() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/yeu_cau_dac_biet');
            exit;
        }

        $this->requirePostCsrf('hdv/yeu_cau_dac_biet');

        $userId = $_SESSION['user_id'] ?? null;
        $schema = validateInputSchema([
            'tour_id' => ['type' => 'id', 'required' => true],
            'yeu_cau_id' => ['type' => 'id', 'required' => false],
            'booking_id' => ['type' => 'id', 'required' => true],
            'loai_yeu_cau' => ['type' => 'string', 'required' => false, 'max' => 50],
            'tieu_de' => ['type' => 'string', 'required' => true, 'min' => 2, 'max' => 255],
            'mo_ta' => ['type' => 'string', 'required' => false, 'max' => 5000],
            'muc_do_uu_tien' => ['type' => 'string', 'required' => false, 'max' => 50],
            'trang_thai' => ['type' => 'string', 'required' => false, 'max' => 50],
            'ghi_chu_hdv' => ['type' => 'string', 'required' => false, 'max' => 1000],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu yeu cau dac biet khong hop le.');
            $_SESSION['error'] = 'Du lieu yeu cau dac biet khong hop le.';
            header('Location: index.php?act=hdv/yeu_cau_dac_biet');
            exit;
        }

        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        $yeu_cau_id = (int)($schema['data']['yeu_cau_id'] ?? 0);
        
        // Lấy nhan_su_id
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV';
            header('Location: index.php?act=hdv/tours');
            exit;
        }
        
        $data = [
            'booking_id' => (int)($schema['data']['booking_id'] ?? 0),
            'loai_yeu_cau' => $schema['data']['loai_yeu_cau'] ?? 'khac',
            'tieu_de' => $schema['data']['tieu_de'] ?? '',
            'mo_ta' => $schema['data']['mo_ta'] ?? null,
            'muc_do_uu_tien' => $schema['data']['muc_do_uu_tien'] ?? 'trung_binh',
            'trang_thai' => $schema['data']['trang_thai'] ?? 'moi',
            'ghi_chu_hdv' => $schema['data']['ghi_chu_hdv'] ?? null
        ];
        
        try {
            if ($yeu_cau_id > 0) {
                // Update
                $sql = "UPDATE yeu_cau_dac_biet SET 
                        loai_yeu_cau = ?, tieu_de = ?, mo_ta = ?, muc_do_uu_tien = ?, 
                        trang_thai = ?, ghi_chu_hdv = ?, nguoi_xu_ly_id = ?
                        WHERE id = ?";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([
                    $data['loai_yeu_cau'], $data['tieu_de'], $data['mo_ta'], 
                    $data['muc_do_uu_tien'], $data['trang_thai'], $data['ghi_chu_hdv'],
                    $nhanSu['nhan_su_id'], $yeu_cau_id
                ]);
                
                // Lưu lịch sử
                $sql = "INSERT INTO lich_su_yeu_cau (yeu_cau_id, hanh_dong, noi_dung, nguoi_thuc_hien_id) 
                        VALUES (?, 'cap_nhat', ?, ?)";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([
                    $yeu_cau_id, 
                    'Cập nhật trạng thái: ' . $data['trang_thai'], 
                    $userId
                ]);
                
                $_SESSION['success'] = 'Cập nhật yêu cầu thành công';
            } else {
                // Insert
                $sql = "INSERT INTO yeu_cau_dac_biet 
                        (booking_id, loai_yeu_cau, tieu_de, mo_ta, muc_do_uu_tien, trang_thai, ghi_chu_hdv, nguoi_tao_id, nguoi_xu_ly_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([
                    $data['booking_id'], $data['loai_yeu_cau'], $data['tieu_de'], 
                    $data['mo_ta'], $data['muc_do_uu_tien'], $data['trang_thai'],
                    $data['ghi_chu_hdv'], $userId, $nhanSu['nhan_su_id']
                ]);
                
                $new_id = $this->nhanSuModel->conn->lastInsertId();
                
                // Lưu lịch sử
                $sql = "INSERT INTO lich_su_yeu_cau (yeu_cau_id, hanh_dong, noi_dung, nguoi_thuc_hien_id) 
                        VALUES (?, 'tao_moi', ?, ?)";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([
                    $new_id, 
                    'Tạo yêu cầu mới: ' . ($data['tieu_de'] ?: 'Yêu cầu đặc biệt'), 
                    $userId
                ]);
                
                $_SESSION['success'] = 'Thêm yêu cầu đặc biệt thành công';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        // Redirect về trang yêu cầu đặc biệt, giữ lại tour_id nếu có
        $redirectUrl = 'index.php?act=hdv/yeu_cau_dac_biet';
        if ($tour_id > 0) {
            $redirectUrl .= '&tour_id=' . $tour_id;
        }
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Xóa yêu cầu đặc biệt
     */
    public function deleteYeuCauDacBiet() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/yeu_cau_dac_biet');
            exit;
        }

        $this->requirePostCsrf('hdv/yeu_cau_dac_biet');

        $schema = validateInputSchema([
            'id' => ['type' => 'id', 'required' => true],
            'tour_id' => ['type' => 'id', 'required' => false],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu xoa yeu cau khong hop le.');
            $_SESSION['error'] = 'Du lieu xoa yeu cau khong hop le.';
            header('Location: index.php?act=hdv/yeu_cau_dac_biet');
            exit;
        }

        $yeu_cau_id = (int)($schema['data']['id'] ?? 0);
        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        
        try {
            if ($this->hasColumn('yeu_cau_dac_biet', 'deleted_at')) {
                $sql = "UPDATE yeu_cau_dac_biet SET deleted_at = NOW(), trang_thai = 'da_xoa' WHERE id = ? AND deleted_at IS NULL";
            } else {
                $sql = "DELETE FROM yeu_cau_dac_biet WHERE id = ?";
            }
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$yeu_cau_id]);
            $_SESSION['success'] = 'Xóa yêu cầu thành công';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: index.php?act=hdv/yeu_cau_dac_biet&tour_id=' . $tour_id);
        exit;
    }
    
    /**
     * Đánh giá và phản hồi
     */
    public function danhGia() {
        $userId = $_SESSION['user_id'] ?? null;
        $tour_id = $_GET['tour_id'] ?? 0;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/tours');
            exit();
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        $danh_gia_list = [];
        $tour = null;
        
        if ($tour_id > 0) {
            // Kiểm tra quyền (HDV chính hoặc phân bổ đã xác nhận)
            $sql = "SELECT DISTINCT lkh.*, t.ten_tour 
                    FROM lich_khoi_hanh lkh 
                    LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                    LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                    WHERE lkh.id = ? 
                    AND (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$nhanSuId, $tour_id, $nhanSuId, $nhanSuId]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Lấy danh sách tour đã hoàn thành
        $sql = "SELECT lkh.id, lkh.ngay_khoi_hanh, t.ten_tour 
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                WHERE lkh.hdv_id = ? AND lkh.trang_thai = 'HoanThanh'
                ORDER BY lkh.ngay_khoi_hanh DESC";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId]);
        $tours_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require 'views/hdv/danh_gia.php';
    }
    
    /**
     * Thông báo
     */
    public function notifications() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=tour/index');
            exit();
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        
        // Lấy tất cả thông báo
        $notifications = $this->hdvMgmtModel->getThongBao($nhanSuId, 50);
        
        // Đánh dấu tất cả là đã đọc
        $sql = "UPDATE thong_bao_hdv SET da_xem = 1 WHERE nhan_su_id = ?";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId]);
        
        require 'views/hdv/notifications.php';
    }
    
    /**
     * Lưu nhật ký tour
     */
    public function saveNhatKy() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/nhat_ky');
            exit;
        }

        $this->requirePostCsrf('hdv/nhat_ky');

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập';
            header('Location: index.php?act=auth/login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Lấy nhan_su_id - không kiểm tra vai_tro
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin nhân sự';
            header('Location: index.php?act=hdv/tours');
            exit;
        }
        
        $schema = validateInputSchema([
            'tour_id' => ['type' => 'id', 'required' => true],
            'entry_id' => ['type' => 'id', 'required' => false],
            'loai_nhat_ky' => ['type' => 'string', 'required' => false, 'max' => 50],
            'tieu_de' => ['type' => 'string', 'required' => true, 'min' => 2, 'max' => 255],
            'noi_dung' => ['type' => 'string', 'required' => true, 'min' => 2, 'max' => 10000],
            'ngay_ghi' => ['type' => 'string', 'required' => false, 'max' => 30],
            'cach_xu_ly' => ['type' => 'string', 'required' => false, 'max' => 2000],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu nhat ky khong hop le.');
            $_SESSION['error'] = 'Du lieu nhat ky khong hop le.';
            header('Location: index.php?act=hdv/nhat_ky');
            exit;
        }

        $nhanSuId = $nhanSu['nhan_su_id'];
        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        $entry_id = (int)($schema['data']['entry_id'] ?? 0);
        
        if ($tour_id <= 0) {
            $_SESSION['error'] = 'Thiếu thông tin tour';
            header('Location: index.php?act=hdv/nhat_ky');
            exit;
        }
        
        // Lấy tour - không kiểm tra quyền
        $sql = "SELECT lkh.*, t.tour_id as tour_table_id, t.ten_tour 
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                WHERE lkh.id = ?";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tour) {
            $_SESSION['error'] = 'Tour không tồn tại (ID: ' . $tour_id . ')';
            header('Location: index.php?act=hdv/nhat_ky&tour_id=' . $tour_id);
            exit;
        }
        
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
                    $fileName = time() . '_' . $i . '_' . basename($_FILES['hinh_anh']['name'][$i]);
                    $targetFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'][$i], $targetFile)) {
                        $imageUrls[] = $targetFile;
                    }
                }
            }
        }
        
        $data = [
            'tour_id' => $tour['tour_table_id'], // Sử dụng tour_id từ bảng tour
            'nhan_su_id' => $nhanSuId,
            'loai_nhat_ky' => $schema['data']['loai_nhat_ky'] ?? 'hanh_trinh',
            'tieu_de' => $schema['data']['tieu_de'] ?? '',
            'noi_dung' => $schema['data']['noi_dung'] ?? '',
            'ngay_ghi' => $schema['data']['ngay_ghi'] ?? date('Y-m-d H:i:s'),
            'cach_xu_ly' => $schema['data']['cach_xu_ly'] ?? null,
            'hinh_anh' => !empty($imageUrls) ? json_encode($imageUrls) : null
        ];
        
        try {
            if ($entry_id > 0) {
                // Update - chỉ update hinh_anh nếu có upload mới
                if (!empty($imageUrls)) {
                    $sql = "UPDATE nhat_ky_tour SET 
                            loai_nhat_ky = ?, tieu_de = ?, noi_dung = ?, ngay_ghi = ?, 
                            cach_xu_ly = ?, hinh_anh = ?
                            WHERE id = ? AND nhan_su_id = ?";
                    $stmt = $this->nhanSuModel->conn->prepare($sql);
                    $result = $stmt->execute([
                        $data['loai_nhat_ky'], $data['tieu_de'], $data['noi_dung'], 
                        $data['ngay_ghi'], $data['cach_xu_ly'], $data['hinh_anh'],
                        $entry_id, $nhanSuId
                    ]);
                } else {
                    $sql = "UPDATE nhat_ky_tour SET 
                            loai_nhat_ky = ?, tieu_de = ?, noi_dung = ?, ngay_ghi = ?, 
                            cach_xu_ly = ?
                            WHERE id = ? AND nhan_su_id = ?";
                    $stmt = $this->nhanSuModel->conn->prepare($sql);
                    $result = $stmt->execute([
                        $data['loai_nhat_ky'], $data['tieu_de'], $data['noi_dung'], 
                        $data['ngay_ghi'], $data['cach_xu_ly'],
                        $entry_id, $nhanSuId
                    ]);
                }
                $_SESSION['success'] = 'Cập nhật nhật ký thành công';
            } else {
                // Insert new entry
                $sql = "INSERT INTO nhat_ky_tour 
                        (tour_id, nhan_su_id, loai_nhat_ky, tieu_de, noi_dung, ngay_ghi, cach_xu_ly, hinh_anh) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->nhanSuModel->conn->prepare($sql);
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
        
        header('Location: index.php?act=hdv/nhat_ky&tour_id=' . $tour_id);
        exit;
    }
    
    /**
     * Xóa nhật ký tour
     */
    public function deleteNhatKy() {
        $userId = $_SESSION['user_id'] ?? null;
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/nhat_ky');
            exit;
        }

        $this->requirePostCsrf('hdv/nhat_ky');

        $schema = validateInputSchema([
            'id' => ['type' => 'id', 'required' => true],
            'tour_id' => ['type' => 'id', 'required' => false],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu xoa nhat ky khong hop le.');
            $_SESSION['error'] = 'Du lieu xoa nhat ky khong hop le.';
            header('Location: index.php?act=hdv/nhat_ky');
            exit;
        }

        $entry_id = (int)($schema['data']['id'] ?? 0);
        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        
        // Lấy nhan_su_id
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV';
            header('Location: index.php?act=hdv/tours');
            exit;
        }
        
        try {
            // Lấy thông tin nhật ký để xóa hình ảnh
            $sql = "SELECT hinh_anh FROM nhat_ky_tour WHERE id = ? AND nhan_su_id = ? AND " . $this->notDeletedClause('nhat_ky_tour');
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$entry_id, $nhanSu['nhan_su_id']]);
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
                if ($this->hasColumn('nhat_ky_tour', 'deleted_at')) {
                    $sql = "UPDATE nhat_ky_tour SET deleted_at = NOW() WHERE id = ? AND nhan_su_id = ? AND deleted_at IS NULL";
                } else {
                    $sql = "DELETE FROM nhat_ky_tour WHERE id = ? AND nhan_su_id = ?";
                }
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $result = $stmt->execute([$entry_id, $nhanSu['nhan_su_id']]);
                
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
        
        header('Location: index.php?act=hdv/nhat_ky&tour_id=' . $tour_id);
        exit;
    }

    // ==================== PHẢN HỒI ĐÁNH GIÁ ====================
    public function phanHoi() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit;
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/dashboard');
            exit;
        }
        
        $nhanSuId = $nhanSu['nhan_su_id'];
        
        // Lấy tour được chọn
        $tour_id = $_GET['tour_id'] ?? 0;
        $loai_filter = $_GET['loai'] ?? '';
        
        $tour = null;
        if ($tour_id) {
            // Kiểm tra quyền (HDV chính hoặc phân bổ đã xác nhận)
            $sql = "SELECT DISTINCT lkh.id, lkh.tour_id, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc, t.ten_tour, t.tour_id
                    FROM lich_khoi_hanh lkh
                    LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                    LEFT JOIN phan_bo_nhan_su pbn ON (lkh.id = pbn.lich_khoi_hanh_id AND pbn.nhan_su_id = ?)
                    WHERE lkh.id = ? 
                    AND (lkh.hdv_id = ? OR (pbn.nhan_su_id = ? AND pbn.trang_thai = 'DaXacNhan'))";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$nhanSuId, $tour_id, $nhanSuId, $nhanSuId]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Lấy danh sách phản hồi
        $phan_hoi_list = [];
        if ($tour) {
            $sql = "SELECT ph.*, t.ten_tour, ns.ho_ten as ten_nguoi_xu_ly
                    FROM phan_hoi_hdv ph
                    LEFT JOIN tour t ON ph.tour_id = t.tour_id
                    LEFT JOIN nhan_su ns ON ph.nguoi_xu_ly_id = ns.nhan_su_id
                    WHERE ph.tour_id = ? AND ph.hdv_id = ? AND " . $this->notDeletedClause('phan_hoi_hdv', 'ph');
            
            $params = [$tour['tour_id'], $nhanSuId];
            
            if ($loai_filter) {
                $sql .= " AND ph.loai_danh_gia = ?";
                $params[] = $loai_filter;
            }
            
            $sql .= " ORDER BY ph.ngay_tao DESC";
            
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute($params);
            $phan_hoi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Lấy danh sách tour đã hoàn thành
        $sql = "SELECT lkh.id, lkh.ngay_khoi_hanh, lkh.ngay_ket_thuc, t.ten_tour 
                FROM lich_khoi_hanh lkh 
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id 
                WHERE lkh.hdv_id = ? AND lkh.trang_thai = 'DaHoanThanh'
                ORDER BY lkh.ngay_khoi_hanh DESC";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$nhanSuId]);
        $tours_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Thống kê
        $stats = ['tong' => 0, 'moi' => 0, 'da_xem' => 0, 'da_xu_ly' => 0, 'diem_tb' => 0];
        if ($tour) {
            $sql = "SELECT 
                    COUNT(*) as tong,
                    SUM(CASE WHEN trang_thai = 'moi' THEN 1 ELSE 0 END) as moi,
                    SUM(CASE WHEN trang_thai = 'da_xem' THEN 1 ELSE 0 END) as da_xem,
                    SUM(CASE WHEN trang_thai = 'da_xu_ly' THEN 1 ELSE 0 END) as da_xu_ly,
                    AVG(diem_danh_gia) as diem_tb
                    FROM phan_hoi_hdv 
                    WHERE tour_id = ? AND hdv_id = ? AND " . $this->notDeletedClause('phan_hoi_hdv');
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([$tour['tour_id'], $nhanSuId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        require 'views/hdv/phan_hoi.php';
    }
    
    public function savePhanHoi() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/phan_hoi');
            exit;
        }

        $this->requirePostCsrf('hdv/phan_hoi');

        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit;
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/dashboard');
            exit;
        }
        
        $schema = validateInputSchema([
            'id' => ['type' => 'id', 'required' => false],
            'tour_id' => ['type' => 'id', 'required' => true],
            'loai_danh_gia' => ['type' => 'string', 'required' => true, 'max' => 50],
            'ten_doi_tuong' => ['type' => 'string', 'required' => true, 'max' => 255],
            'diem_danh_gia' => ['type' => 'money', 'required' => true, 'min' => 1, 'max' => 5],
            'tieu_de' => ['type' => 'string', 'required' => true, 'min' => 2, 'max' => 255],
            'noi_dung' => ['type' => 'string', 'required' => true, 'min' => 2, 'max' => 10000],
            'diem_manh' => ['type' => 'string', 'required' => false, 'max' => 5000],
            'diem_yeu' => ['type' => 'string', 'required' => false, 'max' => 5000],
            'de_xuat' => ['type' => 'string', 'required' => false, 'max' => 5000],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu phan hoi khong hop le.');
            $_SESSION['error'] = 'Du lieu phan hoi khong hop le.';
            header('Location: index.php?act=hdv/phan_hoi');
            exit;
        }

        $nhanSuId = (int)$nhanSu['nhan_su_id'];
        $id = (int)($schema['data']['id'] ?? 0);
        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        $loai_danh_gia = (string)($schema['data']['loai_danh_gia'] ?? '');
        $ten_doi_tuong = (string)($schema['data']['ten_doi_tuong'] ?? '');
        $diem_danh_gia = (float)($schema['data']['diem_danh_gia'] ?? 0);
        $tieu_de = (string)($schema['data']['tieu_de'] ?? '');
        $noi_dung = (string)($schema['data']['noi_dung'] ?? '');
        $diem_manh = (string)($schema['data']['diem_manh'] ?? '');
        $diem_yeu = (string)($schema['data']['diem_yeu'] ?? '');
        $de_xuat = (string)($schema['data']['de_xuat'] ?? '');
        
        // Xử lý upload ảnh
        $hinh_anh = [];
        if (isset($_FILES['hinh_anh']) && is_array($_FILES['hinh_anh']['name'])) {
            $upload_dir = 'uploads/phan_hoi/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            foreach ($_FILES['hinh_anh']['name'] as $key => $name) {
                if ($_FILES['hinh_anh']['error'][$key] == 0) {
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $filename = uniqid() . '_' . time() . '.' . $ext;
                    $target = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'][$key], $target)) {
                        $hinh_anh[] = $target;
                    }
                }
            }
        }
        
        // Lấy tour_id thực tế từ lich_khoi_hanh
        $sql = "SELECT tour_id FROM lich_khoi_hanh WHERE id = ?";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$tour_id]);
        $tour_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $actual_tour_id = $tour_data['tour_id'];
        
        try {
            if ($id > 0) {
                // Update
                $sql = "UPDATE phan_hoi_hdv SET 
                        loai_danh_gia = ?, ten_doi_tuong = ?, diem_danh_gia = ?,
                        tieu_de = ?, noi_dung = ?, diem_manh = ?, diem_yeu = ?, de_xuat = ?";
                
                $params = [$loai_danh_gia, $ten_doi_tuong, $diem_danh_gia, 
                          $tieu_de, $noi_dung, $diem_manh, $diem_yeu, $de_xuat];
                
                if (!empty($hinh_anh)) {
                    $sql .= ", hinh_anh = ?";
                    $params[] = json_encode($hinh_anh);
                }
                
                $sql .= " WHERE id = ? AND hdv_id = ?";
                $params[] = $id;
                $params[] = $nhanSuId;
                
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute($params);
                
                $_SESSION['success'] = 'Cập nhật phản hồi thành công!';
            } else {
                // Insert
                $sql = "INSERT INTO phan_hoi_hdv 
                        (tour_id, hdv_id, loai_danh_gia, ten_doi_tuong, diem_danh_gia, 
                         tieu_de, noi_dung, diem_manh, diem_yeu, de_xuat, hinh_anh) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $this->nhanSuModel->conn->prepare($sql);
                $stmt->execute([
                    $actual_tour_id, $nhanSuId, $loai_danh_gia, $ten_doi_tuong, $diem_danh_gia,
                    $tieu_de, $noi_dung, $diem_manh, $diem_yeu, $de_xuat, json_encode($hinh_anh)
                ]);
                
                $_SESSION['success'] = 'Gửi phản hồi thành công!';
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: index.php?act=hdv/phan_hoi&tour_id=' . $tour_id);
        exit;
    }
    
    public function deletePhanHoi() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit;
        }
        
        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();
        
        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/dashboard');
            exit;
        }
        
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/phan_hoi');
            exit;
        }

        $this->requirePostCsrf('hdv/phan_hoi');

        $schema = validateInputSchema([
            'id' => ['type' => 'id', 'required' => true],
            'tour_id' => ['type' => 'id', 'required' => false],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu xoa phan hoi khong hop le.');
            $_SESSION['error'] = 'Du lieu xoa phan hoi khong hop le.';
            header('Location: index.php?act=hdv/phan_hoi');
            exit;
        }

        $nhanSuId = (int)$nhanSu['nhan_su_id'];
        $id = (int)($schema['data']['id'] ?? 0);
        $tour_id = (int)($schema['data']['tour_id'] ?? 0);
        
        // Xóa ảnh trước
        $sql = "SELECT hinh_anh FROM phan_hoi_hdv WHERE id = ? AND hdv_id = ? AND " . $this->notDeletedClause('phan_hoi_hdv');
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$id, $nhanSuId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && $row['hinh_anh']) {
            $hinh_anh = json_decode($row['hinh_anh'], true);
            if (is_array($hinh_anh)) {
                foreach ($hinh_anh as $img) {
                    if (file_exists($img)) {
                        unlink($img);
                    }
                }
            }
        }
        
        // Xóa phản hồi
        if ($this->hasColumn('phan_hoi_hdv', 'deleted_at')) {
            $sql = "UPDATE phan_hoi_hdv SET deleted_at = NOW() WHERE id = ? AND hdv_id = ? AND deleted_at IS NULL";
        } else {
            $sql = "DELETE FROM phan_hoi_hdv WHERE id = ? AND hdv_id = ?";
        }
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$id, $nhanSuId]);
        
        $_SESSION['success'] = 'Xóa phản hồi thành công!';
        header('Location: index.php?act=hdv/phan_hoi&tour_id=' . $tour_id);
        exit;
    }
    
    // ==================== HỒ SƠ HDV ====================
    public function profile() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit;
        }
        
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.avatar
                FROM nhan_su ns
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE ns.nguoi_dung_id = ? AND ns.vai_tro = 'HDV' 
                LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $hdv_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$hdv_info) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=hdv/dashboard');
            exit;
        }
        
        require 'views/hdv/profile.php';
    }
    
    public function updateProfile() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit;
        }
        
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=hdv/profile');
            exit;
        }

        $this->requirePostCsrf('hdv/profile');

        $schema = validateInputSchema([
            'email' => ['type' => 'email', 'required' => true],
            'so_dien_thoai' => ['type' => 'phone', 'required' => true],
            'chung_chi' => ['type' => 'string', 'required' => false, 'max' => 1000],
            'ngon_ngu' => ['type' => 'string', 'required' => false, 'max' => 255],
            'kinh_nghiem' => ['type' => 'string', 'required' => false, 'max' => 2000],
            'suc_khoe' => ['type' => 'string', 'required' => false, 'max' => 500],
        ], 'POST');
        if (!$schema['ok']) {
            setValidationErrors($schema['errors'], 'Du lieu cap nhat ho so khong hop le.');
            $_SESSION['error'] = 'Du lieu cap nhat ho so khong hop le.';
            header('Location: index.php?act=hdv/profile');
            exit;
        }

        try {
            // Cập nhật bảng nguoi_dung
            $sql = "UPDATE nguoi_dung SET 
                    email = ?, 
                    so_dien_thoai = ?
                    WHERE id = ?";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([
                $schema['data']['email'] ?? '',
                $schema['data']['so_dien_thoai'] ?? '',
                $userId
            ]);
            
            // Cập nhật bảng nhan_su
            $sql = "UPDATE nhan_su SET 
                    chung_chi = ?, 
                    ngon_ngu = ?, 
                    kinh_nghiem = ?, 
                    suc_khoe = ?
                    WHERE nguoi_dung_id = ?";
            $stmt = $this->nhanSuModel->conn->prepare($sql);
            $stmt->execute([
                $schema['data']['chung_chi'] ?? null,
                $schema['data']['ngon_ngu'] ?? null,
                $schema['data']['kinh_nghiem'] ?? null,
                $schema['data']['suc_khoe'] ?? null,
                $userId
            ]);
            
            $_SESSION['success'] = 'Cập nhật hồ sơ thành công!';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
        }
        
        header('Location: index.php?act=hdv/profile');
        exit;
    }

    private function getCurrentHDV() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: index.php?act=auth/login');
            exit();
        }

        $sql = "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1";
        $stmt = $this->nhanSuModel->conn->prepare($sql);
        $stmt->execute([$userId]);
        $nhanSu = $stmt->fetch();

        if (!$nhanSu) {
            $_SESSION['error'] = 'Không tìm thấy thông tin HDV.';
            header('Location: index.php?act=tour/index');
            exit();
        }

        return $nhanSu;
    }

    private function getLichKhoiHanhByHDV(int $nhanSuId) {
        $sql = "SELECT lk.*, t.ten_tour, t.loai_tour, t.gia_co_ban
                FROM lich_khoi_hanh lk
                LEFT JOIN tour t ON lk.tour_id = t.tour_id
                WHERE lk.hdv_id = ?
                ORDER BY lk.ngay_khoi_hanh DESC, lk.gio_xuat_phat DESC";
        $stmt = $this->lichKhoiHanhModel->conn->prepare($sql);
        $stmt->execute([(int)$nhanSuId]);
        return $stmt->fetchAll();
    }

    private function handleNhatKyPost(int $nhanSuId, array $allowedTourIds) {
        $tourId = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
        if ($tourId <= 0 || (!empty($allowedTourIds) && !in_array($tourId, $allowedTourIds, true))) {
            $_SESSION['error'] = 'Tour không hợp lệ hoặc bạn không được phân công.';
            header('Location: index.php?act=hdv/nhatKyTour');
            exit();
        }

        $ngayGhi = !empty($_POST['ngay_ghi']) ? $_POST['ngay_ghi'] : date('Y-m-d');
        $noiDung = $this->buildNhatKyContent($_POST);

        if (empty(trim($noiDung))) {
            $_SESSION['error'] = 'Vui lòng nhập nội dung nhật ký.';
            header('Location: index.php?act=hdv/nhatKyTour&tour_id=' . $tourId);
            exit();
        }

        $action = $_POST['journal_action'] ?? 'create';
        if ($action === 'update') {
            $entryId = isset($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;
            if ($entryId <= 0) {
                $_SESSION['error'] = 'Nhật ký không hợp lệ.';
            } else {
                $result = $this->nhatKyTourModel->update($entryId, $nhanSuId, [
                    'tour_id' => $tourId,
                    'noi_dung' => $noiDung,
                    'ngay_ghi' => $ngayGhi
                ]);
                $_SESSION[$result ? 'success' : 'error'] = $result ? 'Đã cập nhật nhật ký.' : 'Không thể cập nhật nhật ký.';
            }
        } else {
            $result = $this->nhatKyTourModel->insert([
                'tour_id' => $tourId,
                'nhan_su_id' => $nhanSuId,
                'noi_dung' => $noiDung,
                'ngay_ghi' => $ngayGhi
            ]);
            $_SESSION[$result ? 'success' : 'error'] = $result ? 'Đã thêm nhật ký mới.' : 'Không thể thêm nhật ký.';
        }

        header('Location: index.php?act=hdv/nhatKyTour&tour_id=' . $tourId);
        exit();
    }

    private function buildNhatKyContent(array $input) {
        $sections = [
            'Tiêu đề' => $input['tieu_de'] ?? '',
            'Hoạt động nổi bật' => $input['hoat_dong'] ?? '',
            'Sự kiện / Sự cố' => $input['su_kien'] ?? '',
            'Cách xử lý' => $input['cach_xu_ly'] ?? '',
            'Phản hồi khách hàng' => $input['phan_hoi'] ?? '',
            'Ảnh minh họa' => $input['anh_minh_hoa'] ?? '',
            'Ghi chú thêm' => $input['ghi_chu_them'] ?? ''
        ];

        $lines = [];
        foreach ($sections as $label => $value) {
            $value = trim($value);
            if ($value !== '') {
                $lines[] = "{$label}: {$value}";
            }
        }

        return implode("\n", $lines);
    }

    private function parseNhatKyContent(string $text) {
        $result = [
            'tieu_de' => '',
            'hoat_dong' => '',
            'su_kien' => '',
            'cach_xu_ly' => '',
            'phan_hoi' => '',
            'anh_minh_hoa' => '',
            'ghi_chu_them' => ''
        ];

        if (empty($text)) {
            return $result;
        }

        $mapping = [
            'tiêu đề' => 'tieu_de',
            'hoạt động nổi bật' => 'hoat_dong',
            'sự kiện / sự cố' => 'su_kien',
            'sự cố / sự kiện' => 'su_kien',
            'cách xử lý' => 'cach_xu_ly',
            'phản hồi khách hàng' => 'phan_hoi',
            'ảnh minh họa' => 'anh_minh_hoa',
            'ghi chú thêm' => 'ghi_chu_them'
        ];

        $lines = preg_split("/\r\n|\n|\r/", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            foreach ($mapping as $label => $key) {
                $prefix = $label . ':';
                if (stripos($line, $prefix) === 0) {
                    $result[$key] = trim(substr($line, strlen($prefix)));
                    break;
                }
            }
        }

        return $result;
    }
}
