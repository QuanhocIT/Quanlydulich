<?php
require_once 'models/LichKhoiHanh.php';
require_once 'models/PhanBoNhanSu.php';
require_once 'models/PhanBoDichVu.php';
require_once 'models/Tour.php';
require_once 'models/NhanSu.php';
require_once 'models/NhaCungCap.php';
require_once 'models/DichVuNhaCungCap.php';
require_once 'models/Booking.php';
require_once 'models/NhatKyTour.php';

class LichKhoiHanhController {
    private $lichKhoiHanhModel;
    private $phanBoNhanSuModel;
    private $phanBoDichVuModel;
    private $tourModel;
    private $nhanSuModel;
    private $nhaCungCapModel;
    private $bookingModel;
    private $dichVuCatalogModel;

    private function requirePostCsrf($redirectAct = 'lichKhoiHanh/index') {
        $scopedToken = $_POST['_csrf_token'] ?? '';
        $globalToken = $_POST['_csrf_global'] ?? '';

        $validScoped = verifyCsrfToken($scopedToken, 'lich_khoi_hanh_form');
        $validGlobal = verifyCsrfToken($globalToken, 'global_form');

        if (!$validScoped && !$validGlobal) {
            setValidationErrors(['_csrf_token' => 'invalid'], 'Yeu cau khong hop le (CSRF).');
            $_SESSION['error'] = 'Yeu cau khong hop le (CSRF). Vui long thu lai.';
            header('Location: index.php?act=' . urlencode($redirectAct));
            exit();
        }
    }
    
    public function __construct() {
        $this->lichKhoiHanhModel = new LichKhoiHanh();
        $this->phanBoNhanSuModel = new PhanBoNhanSu();
        $this->phanBoDichVuModel = new PhanBoDichVu();
        $this->tourModel = new Tour();
        $this->nhanSuModel = new NhanSu();
        $this->nhaCungCapModel = new NhaCungCap();
        $this->bookingModel = new Booking();
        $this->dichVuCatalogModel = new DichVuNhaCungCap();
    }

    // Danh sách lịch khởi hành
    public function index() {
        // Tránh UPDATE trạng thái toàn bảng cho mỗi lần reload trang.
        $now = time();
        $lastAutoUpdateAt = (int)($_SESSION['lich_khoi_hanh_auto_update_at'] ?? 0);
        if ($lastAutoUpdateAt <= 0 || ($now - $lastAutoUpdateAt) >= 300) {
            $this->lichKhoiHanhModel->autoUpdateTrangThai();
            $_SESSION['lich_khoi_hanh_auto_update_at'] = $now;
        }

        $filters = [
            'search' => trim((string)($_GET['search'] ?? '')),
            'trang_thai' => trim((string)($_GET['trang_thai'] ?? '')),
            'tu_ngay' => trim((string)($_GET['tu_ngay'] ?? '')),
            'den_ngay' => trim((string)($_GET['den_ngay'] ?? '')),
        ];

        $lichKhoiHanhList = $this->lichKhoiHanhModel->getAllFiltered($filters);
        $conflictSummary = $this->phanBoNhanSuModel->getScheduleConflictSummary($lichKhoiHanhList);

        foreach ($lichKhoiHanhList as &$lich) {
            $soLichTrung = (int)($conflictSummary[(int)($lich['id'] ?? 0)] ?? 0);
            $lich['coTrungLichHDV'] = $soLichTrung > 0;
            $lich['soLichTrungHDV'] = $soLichTrung;
        }
        unset($lich);
        
        require 'views/admin/quan_ly_lich_khoi_hanh.php';
    }

    // Chi tiết lịch khởi hành
    public function chiTiet() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            $_SESSION['error'] = 'ID lịch khởi hành không hợp lệ.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }
        
        $lichKhoiHanh = $this->lichKhoiHanhModel->getWithDetails($id);
        if (!$lichKhoiHanh) {
            $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }
        
        // Lấy phân bổ nhân sự
        $phanBoNhanSu = $this->phanBoNhanSuModel->getByLichKhoiHanh($id);

        // Tự động phân bổ nếu chưa có (sử dụng method từ PhanBoNhanSu)
        if (
            (empty($lichKhoiHanh['hdv_id']) || (int)$lichKhoiHanh['hdv_id'] === 0)
            && (empty($phanBoNhanSu) || count($phanBoNhanSu) === 0)
            && !empty($lichKhoiHanh['ngay_khoi_hanh'])
        ) {
            $autoNhanSuId = $this->phanBoNhanSuModel->autoAssignHDVIfMissing($id);
            if ($autoNhanSuId) {
                // Reload lại dữ liệu sau khi tự động phân bổ
                $lichKhoiHanh   = $this->lichKhoiHanhModel->getWithDetails($id);
                $phanBoNhanSu   = $this->phanBoNhanSuModel->getByLichKhoiHanh($id);
            }
        }
        
        // Lấy phân bổ dịch vụ
        $phanBoDichVu = $this->phanBoDichVuModel->getByLichKhoiHanh($id);
        
        // Lấy danh sách nhân sự để chọn
        $nhanSuList = $this->nhanSuModel->getAll();
        
        // Lấy danh sách nhà cung cấp
        $nhaCungCapList = $this->nhaCungCapModel->getAll();

        $catalogServicesMap = [];
        if (!empty($nhaCungCapList)) {
            $supplierIds = array_column($nhaCungCapList, 'id_nha_cung_cap');
            $catalogServices = $this->dichVuCatalogModel->getBySupplierIds($supplierIds);
            foreach ($catalogServices as $service) {
                $catalogServicesMap[$service['nha_cung_cap_id']][] = $service;
            }
        }
        
        // Tính tổng chi phí
        $tongChiPhi = $this->phanBoDichVuModel->getTongChiPhi($id);

        // Lấy yêu cầu đặc biệt của khách cho lịch khởi hành này
        $yeuCauDacBietList = [];
        if (!empty($lichKhoiHanh['tour_id']) && !empty($lichKhoiHanh['ngay_khoi_hanh'])) {
            $yeuCauDacBietList = $this->bookingModel->getSpecialRequestsByLichKhoiHanh(
                $lichKhoiHanh['tour_id'],
                $lichKhoiHanh['ngay_khoi_hanh']
            );
        }
        
        // Lấy danh sách booking và khách chi tiết cho lịch khởi hành
        $bookingList = [];
        $danhSachKhachChiTiet = [];
        $allCheckinRows = [];
        if (!empty($lichKhoiHanh['tour_id']) && !empty($lichKhoiHanh['ngay_khoi_hanh'])) {
            // 1. Lấy danh sách nhóm booking theo tour + ngày khởi hành
            $bookingList = $this->bookingModel->getKhachByLichKhoiHanhId($id);
            
            // 2. Lấy danh sách khách chi tiết từ tour_checkin
            require_once 'models/CheckinKhach.php';
            $checkinModel = new CheckinKhach();

            if (!empty($bookingList)) {
                // Có booking: lấy checkin theo batch để tránh query theo từng booking.
                $bookingIds = array_map(static function ($booking) {
                    return (int)($booking['booking_id'] ?? 0);
                }, $bookingList);
                $checkinGrouped = $checkinModel->getByBookingIdsGrouped($bookingIds);

                foreach ($bookingList as $booking) {
                    $bookingId = (int)($booking['booking_id'] ?? 0);
                    if ($bookingId <= 0) {
                        continue;
                    }
                    $danhSachKhachChiTiet[$bookingId] = $checkinGrouped[$bookingId] ?? [];
                }

                // Build flattened list for admin table from grouped rows.
                foreach ($danhSachKhachChiTiet as $rows) {
                    if (is_array($rows) && !empty($rows)) {
                        foreach ($rows as $row) {
                            $allCheckinRows[] = $row;
                        }
                    }
                }
            } else {
                // Không có booking nhưng HDV đã có danh sách khách trong tour_checkin
                // => đọc trực tiếp theo lich_khoi_hanh_id để admin vẫn xem được giống HDV
                $checkinRows = $checkinModel->getByLichKhoiHanh($id);
                $allCheckinRows = $checkinRows;

                if (!empty($checkinRows)) {
                    $bookingGrouped = [];

                    foreach ($checkinRows as $row) {
                        $bId = (int)($row['booking_id'] ?? 0);
                        if ($bId <= 0) {
                            // Gán booking giả nếu thiếu ID
                            $bId = -1;
                        }

                        if (!isset($bookingGrouped[$bId])) {
                            $bookingGrouped[$bId] = [
                                'booking_id'   => $bId,
                                'khach_hang_id'=> $row['khach_hang_id'] ?? null,
                                'so_nguoi'     => 0,
                                'ngay_dat'     => null,
                                'ghi_chu_booking' => null,
                                // Dùng tên khách đầu tiên làm nhãn nhóm
                                'ho_ten'       => $row['ho_ten'] ?? 'Khách',
                                'email'        => $row['email'] ?? null,
                                'so_dien_thoai'=> $row['so_dien_thoai'] ?? null,
                                'dia_chi'      => $row['dia_chi'] ?? null,
                            ];
                            $danhSachKhachChiTiet[$bId] = [];
                        }

                        $bookingGrouped[$bId]['so_nguoi']++;
                        $danhSachKhachChiTiet[$bId][] = $row;
                    }

                    // Chuyển về dạng mảng tuần tự để view hiển thị
                    $bookingList = array_values($bookingGrouped);
                }
            }

            // 3. Đồng bộ lại thống kê số booking & tổng số khách cho view admin
            $lichKhoiHanh['so_booking'] = count($bookingList);

            $tongNguoi = 0;
            if (!empty($danhSachKhachChiTiet)) {
                foreach ($danhSachKhachChiTiet as $list) {
                    $tongNguoi += is_array($list) ? count($list) : 0;
                }
            } else {
                // fallback: dùng trường so_nguoi trong booking nếu chưa có chi tiết
                foreach ($bookingList as $booking) {
                    $tongNguoi += (int)($booking['so_nguoi'] ?? 0);
                }
            }
            $lichKhoiHanh['tong_nguoi_dat'] = $tongNguoi;
        }

        if (empty($allCheckinRows)) {
            require_once 'models/CheckinKhach.php';
            $checkinModel = new CheckinKhach();
            $allCheckinRows = $checkinModel->getByLichKhoiHanh($id);
        }
// --- Bắt đầu: Lấy nhật ký tour (theo HDV nếu có) ---
$nhatKyTourList = [];

if (!empty($lichKhoiHanh['tour_id'])) {
    require_once 'models/NhatKyTour.php';
    $nhatKyModel = new NhatKyTour();

    // 1) Xác định HDV chính của lịch (hdv_id trong lich_khoi_hanh)
    $hdvId = $lichKhoiHanh['hdv_id'] ?? null;

    // Nếu không có hdv_id trực tiếp, fallback lấy từ phân bổ nhân sự (nếu có)
    if (empty($hdvId) && !empty($phanBoNhanSu) && is_array($phanBoNhanSu)) {
        foreach ($phanBoNhanSu as $p) {
            if (!empty($p['nhan_su_id'])) {
                $hdvId = $p['nhan_su_id'];
                break;
            }
        }
    }

    // 2) Lấy nhật ký: ưu tiên nhật ký của HDV nếu có, nếu không lấy theo tour chung
    if (!empty($hdvId)) {
        $nhatKyTourList = $nhatKyModel->getByHDVAndTour($hdvId, $lichKhoiHanh['tour_id']);
    } else {
        // fallback: lấy theo tour (toàn bộ nhật ký liên quan đến tour)
        $nhatKyTourList = $this->tourModel->getNhatKyTourByTourId($lichKhoiHanh['tour_id']);
    }

    // 3) Nếu cần chỉ hiển thị nhật ký đúng ngày khởi hành => lọc theo ngày
    if (!empty($lichKhoiHanh['ngay_khoi_hanh'])) {
        $ngayKH = date('Y-m-d', strtotime($lichKhoiHanh['ngay_khoi_hanh']));
        $nhatKyTourList = array_values(array_filter($nhatKyTourList, function($n) use ($ngayKH) {
            // Các model có thể trả 'thoi_gian_su_kien' hoặc 'ngay_ghi' hoặc 'thoi_tiet'
            $dateCandidate = $n['thoi_gian_su_kien'] ?? $n['ngay_ghi'] ?? $n['thoi_tiet'] ?? null;
            if (!$dateCandidate) return false;
            return date('Y-m-d', strtotime($dateCandidate)) === $ngayKH;
        }));
    }

    // 4) Post-process: chuẩn hoá tên khóa để view dùng chung (loai_su_kien, thoi_gian_su_kien, nguoi_ghi_chep, dia_diem)
    foreach ($nhatKyTourList as &$log) {
        // loại sự kiện
        if (!isset($log['loai_su_kien']) && isset($log['loai_nhat_ky'])) {
            $log['loai_su_kien'] = $log['loai_nhat_ky'];
        }

        // thời gian sự kiện
        if (empty($log['thoi_gian_su_kien'])) {
            if (!empty($log['ngay_ghi'])) $log['thoi_gian_su_kien'] = $log['ngay_ghi'];
            elseif (!empty($log['thoi_tiet'])) $log['thoi_gian_su_kien'] = $log['thoi_tiet'];
            else $log['thoi_gian_su_kien'] = null;
        }

        // dia_diem (nếu DB không có cột dia_diem, dùng null)
        $log['dia_diem'] = $log['dia_diem'] ?? ($log['thoi_tiet'] ?? null);

        // người ghi chép: nếu model trả tên hoặc nếu biết hdvId, lấy tên từ model NhanSu
        if (empty($log['nguoi_ghi_chep'])) {
            if (!empty($hdvId)) {
                $hdvInfo = $this->nhanSuModel->findById($hdvId);
                $log['nguoi_ghi_chep'] = $hdvInfo['ho_ten'] ?? ($_SESSION['ho_ten'] ?? 'HDV');
            } else {
                // fallback: nếu model trả hdv_ho_ten do join ở Tour::getNhatKyTourByTourId
                $log['nguoi_ghi_chep'] = $log['hdv_ho_ten'] ?? ($_SESSION['ho_ten'] ?? 'HDV');
            }
        }
    }
    unset($log);
}
// --- Kết thúc khối nhật ký ---
        
        
        require 'views/admin/chi_tiet_lich_khoi_hanh.php';
    }

    /**
     * Đi từ booking sang màn chi tiết lịch khởi hành để phân bổ nhân sự & dịch vụ.
     * Nếu chưa có lịch khởi hành cho tour + ngày khởi hành của booking thì tự tạo mới.
     */
    public function chiTietTheoBooking() {
        $bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

        $tourId = (int)($booking['tour_id'] ?? 0);
        $ngayKhoiHanh = $booking['ngay_khoi_hanh'] ?? $booking['ngay_dat'];

        if ($tourId <= 0 || empty($ngayKhoiHanh)) {
            $_SESSION['error'] = 'Booking chưa có thông tin tour hoặc ngày khởi hành.';
            header('Location: index.php?act=admin/quanLyBooking');
            exit();
        }

        // Tìm lịch khởi hành tương ứng
        $lichKhoiHanh = $this->lichKhoiHanhModel->findByTourAndNgayKhoiHanh($tourId, $ngayKhoiHanh);

        if (!$lichKhoiHanh) {
            // Tự tạo lịch khởi hành mới dựa trên thông tin booking
            $data = [
                'tour_id' => $tourId,
                'ngay_khoi_hanh' => $ngayKhoiHanh,
                'gio_xuat_phat' => null,
                'ngay_ket_thuc' => $booking['ngay_ket_thuc'] ?? $ngayKhoiHanh,
                'gio_ket_thuc' => null,
                'diem_tap_trung' => '',
                'so_cho' => 50,
                'hdv_id' => null,
                'trang_thai' => 'SapKhoiHanh',
                'ghi_chu' => 'Tạo tự động từ booking #' . $bookingId
            ];

            $lichKhoiHanhId = $this->lichKhoiHanhModel->insert($data);
        } else {
            $lichKhoiHanhId = $lichKhoiHanh['id'];
        }

        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId . '&from_booking=' . $bookingId);
        exit();
    }


    // Tạo lịch khởi hành mới
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'tour_id' => isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : null,
                'ngay_khoi_hanh' => $_POST['ngay_khoi_hanh'] ?? null,
                'gio_xuat_phat' => $_POST['gio_xuat_phat'] ?? null,
                'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?? null,
                'gio_ket_thuc' => $_POST['gio_ket_thuc'] ?? null,
                'diem_tap_trung' => $_POST['diem_tap_trung'] ?? '',
                'so_cho' => isset($_POST['so_cho']) ? (int)$_POST['so_cho'] : 50,
                'hdv_id' => isset($_POST['hdv_id']) && $_POST['hdv_id'] !== '' ? (int)$_POST['hdv_id'] : null,
                'trang_thai' => $_POST['trang_thai'] ?? 'SapKhoiHanh',
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            $id = $this->lichKhoiHanhModel->insert($data);
            if ($id) {
                $_SESSION['success'] = 'Tạo lịch khởi hành thành công.';
                header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $id);
                exit();
            } else {
                $_SESSION['error'] = 'Không thể tạo lịch khởi hành.';
            }
        }
        
        $tours = $this->tourModel->getAll();
        require 'views/admin/tao_lich_khoi_hanh.php';
    }

    // Hiển thị form sửa lịch khởi hành
    public function edit() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'ID lịch khởi hành không hợp lệ.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $lichKhoiHanh = $this->lichKhoiHanhModel->findById($id);
        if (!$lichKhoiHanh) {
            $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $tours = $this->tourModel->getAll();
        $mode = 'edit';
        require 'views/admin/tao_lich_khoi_hanh.php';
    }

    // Cập nhật lịch khởi hành
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID lịch khởi hành không hợp lệ.';
                header('Location: index.php?act=lichKhoiHanh/index');
                exit();
            }
            
            $data = [
                'tour_id' => isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : null,
                'ngay_khoi_hanh' => $_POST['ngay_khoi_hanh'] ?? null,
                'gio_xuat_phat' => $_POST['gio_xuat_phat'] ?? null,
                'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?? null,
                'gio_ket_thuc' => $_POST['gio_ket_thuc'] ?? null,
                'diem_tap_trung' => $_POST['diem_tap_trung'] ?? '',
                'so_cho' => isset($_POST['so_cho']) ? (int)$_POST['so_cho'] : 50,
                'hdv_id' => isset($_POST['hdv_id']) && $_POST['hdv_id'] !== '' ? (int)$_POST['hdv_id'] : null,
                'trang_thai' => $_POST['trang_thai'] ?? 'SapKhoiHanh',
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            $result = $this->lichKhoiHanhModel->update($id, $data);
            if ($result) {
                $_SESSION['success'] = 'Cập nhật lịch khởi hành thành công.';
            } else {
                $_SESSION['error'] = 'Không thể cập nhật lịch khởi hành.';
            }
            
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $id);
            exit();
        }
    }

    // Tự động phân bổ nhân sự (method mới)
    public function tuDongPhanBoNhanSu() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($id <= 0) {
            $_SESSION['error'] = 'ID lịch khởi hành không hợp lệ.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }
        
        $lichKhoiHanh = $this->lichKhoiHanhModel->findById($id);
        if (!$lichKhoiHanh) {
            $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }
        
        // Kiểm tra xem đã có phân bổ chưa
        $phanBoNhanSu = $this->phanBoNhanSuModel->getByLichKhoiHanh($id);
        if (!empty($phanBoNhanSu)) {
            $_SESSION['info'] = 'Lịch khởi hành đã có nhân sự được phân bổ.';
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $id);
            exit();
        }
        
        // Gọi method tự động phân bổ từ model
        $result = $this->phanBoNhanSuModel->autoAssignHDVIfMissing($id);
        
        if ($result) {
            $_SESSION['success'] = 'Đã tự động phân bổ HDV thành công!';
        } else {
            $_SESSION['error'] = 'Không tìm thấy HDV phù hợp để phân bổ. Vui lòng kiểm tra lại lịch làm việc của HDV hoặc thêm HDV mới vào hệ thống.';
        }
        
        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $id);
        exit();
    }

    // Phân bổ nhân sự
    public function phanBoNhanSu() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            $nhanSuId = isset($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : 0;
            $vaiTro = $_POST['vai_tro'] ?? 'Khac';
            $ghiChu = $_POST['ghi_chu'] ?? null;
            // Lấy thông tin lương từ form
            $loaiLuong = $_POST['loai_luong'] ?? 'CoDinh';
            $soTienCoDinh = isset($_POST['so_tien_co_dinh']) ? (float)$_POST['so_tien_co_dinh'] : 0;
            $phanTramHoaHong = isset($_POST['phan_tram_hoa_hong']) ? (float)$_POST['phan_tram_hoa_hong'] : 0;
            $tienHoaHong = 0;
            $tongLuong = 0;
            $trangThaiLuong = 'ChoDuyet';
            // Nếu có doanh thu tour, tính lương tự động
            $lichKhoiHanh = $this->lichKhoiHanhModel->findById($lichKhoiHanhId);
            $doanhThuTour = $this->phanBoNhanSuModel->getDoanhThuByLichKhoiHanh($lichKhoiHanhId);

            // Quy tắc HDV: % hoa hồng lấy từ lịch khởi hành (admin cấu hình trước khi tour bắt đầu)
            if ($vaiTro === 'HDV') {
                $loaiLuong = 'PhanTram';
                $soTienCoDinh = 0;
                $phanTramHoaHong = (float)($lichKhoiHanh['phan_tram_hoa_hong_hdv'] ?? $phanTramHoaHong);
            }
            if ($loaiLuong === 'PhanTram' && $doanhThuTour > 0) {
                $tienHoaHong = round($doanhThuTour * $phanTramHoaHong / 100, 2);
                $tongLuong = $tienHoaHong;
            } elseif ($loaiLuong === 'CoDinh') {
                $tongLuong = $soTienCoDinh;
            } elseif ($loaiLuong === 'KetHop' && $doanhThuTour > 0) {
                $tienHoaHong = round($doanhThuTour * $phanTramHoaHong / 100, 2);
                $tongLuong = $soTienCoDinh + $tienHoaHong;
            } else {
                $tongLuong = $soTienCoDinh;
            }
            if ($lichKhoiHanhId > 0 && $nhanSuId > 0) {
                $data = [
                    'lich_khoi_hanh_id' => $lichKhoiHanhId,
                    'nhan_su_id' => $nhanSuId,
                    'vai_tro' => $vaiTro,
                    'ghi_chu' => $ghiChu,
                    'loai_luong' => $loaiLuong,
                    'so_tien_co_dinh' => $soTienCoDinh,
                    'phan_tram_hoa_hong' => $phanTramHoaHong,
                    'tien_hoa_hong' => $tienHoaHong,
                    'tong_luong' => $tongLuong,
                    'trang_thai_luong' => $trangThaiLuong
                ];
                // Nếu phân bổ HDV, kiểm tra xem nhân sự này có trùng lịch với tour khác không
                if ($vaiTro === 'HDV') {
                    $conflicts = $this->phanBoNhanSuModel->getScheduleConflictsForStaff($lichKhoiHanhId, $nhanSuId);
                    if (!empty($conflicts)) {
                        $list = [];
                        foreach ($conflicts as $c) {
                            $from = $c['ngay_khoi_hanh'] ?? '';
                            $to   = $c['ngay_ket_thuc'] ?? $from;
                            $label = '#' . $c['id'];
                            if (!empty($c['ten_tour'])) {
                                $label .= ' - ' . $c['ten_tour'];
                            }
                            if ($from) {
                                $label .= ' (' . $from;
                                if ($to && $to !== $from) {
                                    $label .= ' → ' . $to;
                                }
                                $label .= ')';
                            }
                            $list[] = $label;
                        }
                        $_SESSION['warning'] = '<strong>CẢNH BÁO:</strong> Nhân sự này đang được phân công vào các lịch trùng ngày:<br>' 
                            . '<ul><li>' . implode('</li><li>', $list) . '</li></ul>'
                            . '<strong>Vẫn cho phép phân bổ, nhưng HDV cần cân nhắc tránh quá tải.</strong>';
                    }
                }
                $result = $this->phanBoNhanSuModel->insert($data);
                if ($result) {
                    $_SESSION['success'] = 'Phân bổ nhân sự thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể phân bổ nhân sự.';
                }
            } else {
                $_SESSION['error'] = 'Thông tin không hợp lệ.';
            }
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
            exit();
        }
    }

    // Admin cập nhật % hoa hồng HDV trước khi tour bắt đầu
    public function capNhatHoaHongHDV() {
        requireRole('Admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $phanTram = isset($_POST['phan_tram_hoa_hong_hdv']) ? (float)$_POST['phan_tram_hoa_hong_hdv'] : 0;
        $phanTram = max(0, min(100, $phanTram));

        if ($id <= 0) {
            $_SESSION['error'] = 'ID lịch khởi hành không hợp lệ.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $lich = $this->lichKhoiHanhModel->findById($id);
        if (!$lich) {
            $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $trangThai = $lich['trang_thai'] ?? '';
        if ($trangThai !== '' && $trangThai !== 'SapKhoiHanh') {
            $_SESSION['error'] = 'Chỉ được chỉnh % hoa hồng khi tour chưa bắt đầu (trạng thái Sắp khởi hành).';
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $id);
            exit();
        }

        $ok = $this->lichKhoiHanhModel->updatePhanTramHoaHongHDV($id, $phanTram);
        $_SESSION[$ok ? 'success' : 'error'] = $ok
            ? 'Đã cập nhật % hoa hồng HDV.'
            : 'Không thể cập nhật (hãy đảm bảo đã thêm cột phan_tram_hoa_hong_hdv).';

        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $id);
        exit();
    }

    // API: Kiểm tra trùng lịch khi chọn nhân sự (AJAX)
    public function checkConflict() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Method not allowed']);
            exit();
        }
        
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        $nhanSuId = isset($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : 0;
        $vaiTro = $_POST['vai_tro'] ?? '';
        
        if ($lichKhoiHanhId <= 0 || $nhanSuId <= 0) {
            echo json_encode(['hasConflict' => false]);
            exit();
        }
        
        // Chỉ kiểm tra conflict cho HDV
        if ($vaiTro === 'HDV') {
            $conflicts = $this->phanBoNhanSuModel->getScheduleConflictsForStaff($lichKhoiHanhId, $nhanSuId);
            if (!empty($conflicts)) {
                $conflictList = [];
                foreach ($conflicts as $c) {
                    $from = $c['ngay_khoi_hanh'] ?? '';
                    $to   = $c['ngay_ket_thuc'] ?? $from;
                    $label = 'Lịch #' . $c['id'];
                    if (!empty($c['ten_tour'])) {
                        $label .= ' - ' . $c['ten_tour'];
                    }
                    if ($from) {
                        $label .= ' (' . date('d/m/Y', strtotime($from));
                        if ($to && $to !== $from) {
                            $label .= ' → ' . date('d/m/Y', strtotime($to));
                        }
                        $label .= ')';
                    }
                    $conflictList[] = $label;
                }
                echo json_encode([
                    'hasConflict' => true,
                    'conflicts' => $conflictList,
                    'message' => 'CẢNH BÁO: Nhân sự này đang được phân công vào các lịch trùng ngày: ' . implode(', ', $conflictList)
                ]);
                exit();
            }
        }
        
        echo json_encode(['hasConflict' => false]);
        exit();
    }

    // Cập nhật trạng thái phân bổ nhân sự (HDV)
    public function updateTrangThaiNhanSu() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $trangThai = $_POST['trang_thai'] ?? 'ChoXacNhan';
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            
            if ($id > 0) {
                $phanBo = $this->phanBoNhanSuModel->findById($id);
                $result = $this->phanBoNhanSuModel->updateTrangThai($id, $trangThai);
                if ($result) {
                    if (
                        $phanBo
                        && $trangThai === 'DaXacNhan'
                        && isset($phanBo['vai_tro'])
                        && $phanBo['vai_tro'] === 'HDV'
                        && isset($phanBo['lich_khoi_hanh_id'], $phanBo['nhan_su_id'])
                    ) {
                        $this->lichKhoiHanhModel->assignHDV($phanBo['lich_khoi_hanh_id'], $phanBo['nhan_su_id']);
                    }
                    $_SESSION['success'] = 'Cập nhật trạng thái nhân sự thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể cập nhật trạng thái nhân sự.';
                }
            } else {
                $_SESSION['error'] = 'Thông tin không hợp lệ.';
            }
            
            // Redirect về trang phù hợp với vai trò
            if (hasRole('HDV')) {
                // HDV quay về trang lịch làm việc của mình
                header('Location: index.php?act=hdv/lichLamViec');
            } else {
                // Admin quay về trang chi tiết lịch khởi hành
                header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
            }
            exit();
        }
    }

    // Phân bổ dịch vụ
    public function phanBoDichVu() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            
            $data = [
                'lich_khoi_hanh_id' => $lichKhoiHanhId,
                'nha_cung_cap_id' => isset($_POST['nha_cung_cap_id']) && $_POST['nha_cung_cap_id'] !== '' ? (int)$_POST['nha_cung_cap_id'] : null,
                'loai_dich_vu' => $_POST['loai_dich_vu'] ?? 'Khac',
                'ten_dich_vu' => $_POST['ten_dich_vu'] ?? '',
                'so_luong' => isset($_POST['so_luong']) ? (int)$_POST['so_luong'] : 1,
                'don_vi' => $_POST['don_vi'] ?? null,
                'ngay_bat_dau' => $_POST['ngay_bat_dau'] ?? null,
                'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?? null,
                'gio_bat_dau' => $_POST['gio_bat_dau'] ?? null,
                'gio_ket_thuc' => $_POST['gio_ket_thuc'] ?? null,
                'dia_diem' => $_POST['dia_diem'] ?? null,
                'gia_tien' => isset($_POST['gia_tien']) ? (float)$_POST['gia_tien'] : null,
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            if ($lichKhoiHanhId > 0) {
                $result = $this->phanBoDichVuModel->insert($data);
                if ($result) {
                    $_SESSION['success'] = 'Phân bổ dịch vụ thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể phân bổ dịch vụ.';
                }
            } else {
                $_SESSION['error'] = 'Thông tin không hợp lệ.';
            }
            
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
            exit();
        }
    }

    // Cập nhật trạng thái phân bổ dịch vụ
    public function updateTrangThaiDichVu() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $trangThai = $_POST['trang_thai'] ?? 'ChoXacNhan';
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            
            if ($id > 0) {
                $result = $this->phanBoDichVuModel->updateTrangThai($id, $trangThai);
                if ($result) {
                    $_SESSION['success'] = 'Cập nhật trạng thái thành công.';
                } else {
                    $_SESSION['error'] = 'Không thể cập nhật trạng thái.';
                }
            }
            
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
            exit();
        }
    }

    // Xóa phân bổ nhân sự
    public function deleteNhanSu() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $this->requirePostCsrf('lichKhoiHanh/index');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        
        if ($id > 0) {
            $result = $this->phanBoNhanSuModel->delete($id);
            if ($result) {
                $_SESSION['success'] = 'Xóa phân bổ nhân sự thành công.';
            } else {
                $_SESSION['error'] = 'Không thể xóa phân bổ nhân sự.';
            }
        }
        
        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
        exit();
    }

    // Xóa phân bổ dịch vụ
    public function deleteDichVu() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $this->requirePostCsrf('lichKhoiHanh/index');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        
        if ($id > 0) {
            $result = $this->phanBoDichVuModel->delete($id);
            if ($result) {
                $_SESSION['success'] = 'Xóa phân bổ dịch vụ thành công.';
            } else {
                $_SESSION['error'] = 'Không thể xóa phân bổ dịch vụ.';
            }
        }
        
        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
        exit();
    }

    // Thêm khách chi tiết vào lịch khởi hành
    public function themKhachChiTiet() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once 'models/CheckinKhach.php';
            require_once 'models/Booking.php';
            
            $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
            $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
            
            $bookingModel = new Booking();
            $booking = $bookingModel->findById($bookingId);
            
            if (!$booking) {
                $_SESSION['error'] = 'Booking không tồn tại.';
                header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
                exit();
            }
            
            $checkinModel = new CheckinKhach();
            $hoTenArr = $_POST['ho_ten'] ?? [];

            // Cho phép submit cả dạng đơn (string) lẫn mảng
            if (!is_array($hoTenArr)) {
                $hoTenArr = [$hoTenArr];
                $_POST['so_cmnd'] = [$_POST['so_cmnd'] ?? null];
                $_POST['so_passport'] = [$_POST['so_passport'] ?? null];
                $_POST['ngay_sinh'] = [$_POST['ngay_sinh'] ?? null];
                $_POST['gioi_tinh'] = [$_POST['gioi_tinh'] ?? 'Khac'];
                $_POST['quoc_tich'] = [$_POST['quoc_tich'] ?? 'Việt Nam'];
                $_POST['dia_chi'] = [$_POST['dia_chi'] ?? null];
                $_POST['so_dien_thoai'] = [$_POST['so_dien_thoai'] ?? null];
                $_POST['email'] = [$_POST['email'] ?? null];
                $_POST['ghi_chu'] = [$_POST['ghi_chu'] ?? null];
            }

            $soCmndArr = $_POST['so_cmnd'] ?? [];
            $soPassportArr = $_POST['so_passport'] ?? [];
            $ngaySinhArr = $_POST['ngay_sinh'] ?? [];
            $gioiTinhArr = $_POST['gioi_tinh'] ?? [];
            $quocTichArr = $_POST['quoc_tich'] ?? [];
            $diaChiArr = $_POST['dia_chi'] ?? [];
            $soDienThoaiArr = $_POST['so_dien_thoai'] ?? [];
            $emailArr = $_POST['email'] ?? [];
            $ghiChuArr = $_POST['ghi_chu'] ?? [];

            $successCount = 0;
            $errorCount = 0;

            foreach ($hoTenArr as $index => $hoTen) {
                $hoTen = trim($hoTen ?? '');
                if ($hoTen === '') {
                    continue; // bỏ qua dòng trống
                }

            $data = [
                'booking_id' => $bookingId,
                'khach_hang_id' => $booking['khach_hang_id'],
                'lich_khoi_hanh_id' => $lichKhoiHanhId,
                    'ho_ten' => $hoTen,
                    'so_cmnd' => $soCmndArr[$index] ?? null,
                    'so_passport' => $soPassportArr[$index] ?? null,
                    'ngay_sinh' => $ngaySinhArr[$index] ?? null,
                    'gioi_tinh' => $gioiTinhArr[$index] ?? 'Khac',
                    'quoc_tich' => $quocTichArr[$index] ?? 'Việt Nam',
                    'dia_chi' => $diaChiArr[$index] ?? null,
                    'so_dien_thoai' => $soDienThoaiArr[$index] ?? null,
                    'email' => $emailArr[$index] ?? null,
                'trang_thai' => 'ChuaCheckIn',
                    'ghi_chu' => $ghiChuArr[$index] ?? null
            ];
            
                if ($checkinModel->insert($data)) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }

            if ($successCount > 0 && $errorCount === 0) {
                $_SESSION['success'] = 'Thêm ' . $successCount . ' khách thành công.';
            } elseif ($successCount > 0 && $errorCount > 0) {
                $_SESSION['success'] = 'Thêm ' . $successCount . ' khách thành công, ' . $errorCount . ' khách lỗi.';
            } else {
                $_SESSION['error'] = 'Không thể thêm khách. Vui lòng kiểm tra lại thông tin.';
            }
            
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
            exit();
        }
    }

    // Sửa khách chi tiết
    public function suaKhachChiTiet() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $lichKhoiHanhId = isset($_GET['lich_khoi_hanh_id']) ? (int)$_GET['lich_khoi_hanh_id'] : 0;
        
        require_once 'models/CheckinKhach.php';
        $checkinModel = new CheckinKhach();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $khach = $checkinModel->findById($id);
            if (!$khach) {
                $_SESSION['error'] = 'Không tìm thấy khách.';
                header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
                exit();
            }
            
            // Cập nhật thông tin cơ bản (cần mở rộng model để update đầy đủ)
            $data = [
                'ho_ten' => $_POST['ho_ten'] ?? $khach['ho_ten'],
                'so_cmnd' => $_POST['so_cmnd'] ?? $khach['so_cmnd'],
                'so_passport' => $_POST['so_passport'] ?? $khach['so_passport'],
                'ngay_sinh' => $_POST['ngay_sinh'] ?? $khach['ngay_sinh'],
                'gioi_tinh' => $_POST['gioi_tinh'] ?? $khach['gioi_tinh'],
                'quoc_tich' => $_POST['quoc_tich'] ?? $khach['quoc_tich'],
                'dia_chi' => $_POST['dia_chi'] ?? $khach['dia_chi'],
                'so_dien_thoai' => $_POST['so_dien_thoai'] ?? $khach['so_dien_thoai'],
                'email' => $_POST['email'] ?? $khach['email'],
                'ghi_chu' => $_POST['ghi_chu'] ?? $khach['ghi_chu']
            ];
            
            // Cần mở rộng method update trong CheckinKhach model
            $result = $checkinModel->updateFull($id, $data);
            if ($result) {
                $_SESSION['success'] = 'Cập nhật thông tin khách thành công.';
            } else {
                $_SESSION['error'] = 'Không thể cập nhật thông tin khách.';
            }
            
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
            exit();
        }
        
        // GET: hiển thị form
        $khach = $checkinModel->findById($id);
        if (!$khach) {
            $_SESSION['error'] = 'Không tìm thấy khách.';
            header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
            exit();
        }
        
        require 'views/admin/sua_khach_chi_tiet.php';
    }
   
public function themYeuCauDacBiet() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?act=lichKhoiHanh/index');
        exit();
    }

    $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $lichId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;

    if ($bookingId <= 0) {
        $_SESSION['error'] = 'Vui lòng chọn khách/booking để tạo yêu cầu.';
        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichId);
        exit();
    }

    require_once 'models/YeuCauDacBiet.php';
    $yeuCauModel = new YeuCauDacBiet();

    // Ánh xạ giá trị loai_yeu_cau từ form sang DB
    $loaiMap = [
        'ThucPham' => 'an_uong',
        'YTe' => 'suc_khoe',
        'DichVu' => 'phong_o',
        'NguNgu' => 'phong_o',
        'AnToan' => 'an_toan',
        'Khac' => 'khac'
    ];
    
    $loaiInput = $_POST['loai_yeu_cau'] ?? 'Khac';
    $loaiDb = $loaiMap[$loaiInput] ?? 'khac'; // giá trị mặc định

    // Ánh xạ muc_do_uu_tien
    $ucTienMap = [
        'RatCao' => 'khan_cap',
        'Cao' => 'cao',
        'Trung' => 'trung_binh',
        'Thap' => 'thap'
    ];
    
    $ucTienInput = $_POST['muc_do_uu_tien'] ?? 'Trung';
    $ucTienDb = $ucTienMap[$ucTienInput] ?? 'trung_binh';

    // Ánh xạ trang_thai
    $trangThaiMap = [
        'Moi' => 'moi',
        'DangXuLy' => 'dang_xu_ly',
        'HoanTat' => 'da_giai_quyet',
        'KhongTheXuLy' => 'khong_the_thuc_hien'
    ];
    
    $trangThaiInput = $_POST['trang_thai'] ?? 'Moi';
    $trangThaiDb = $trangThaiMap[$trangThaiInput] ?? 'moi';

    $data = [
        'loai_yeu_cau' => $loaiDb,
        'tieu_de' => trim($_POST['tieu_de'] ?? 'Yêu cầu đặc biệt'),
        'mo_ta' => $_POST['noi_dung'] ?? null,
        'muc_do_uu_tien' => $ucTienDb,
        'trang_thai' => $trangThaiDb,
        'ghi_chu_hdv' => $_POST['ghi_chu'] ?? null
    ];

    $nguoiTaoId = $_SESSION['user_id'] ?? null;

    $newId = $yeuCauModel->createFromAdmin($bookingId, $data, $nguoiTaoId);

    if ($newId) {
        $_SESSION['success'] = 'Đã tạo yêu cầu đặc biệt.';
    } else {
        $_SESSION['error'] = 'Không thể tạo yêu cầu. Vui lòng thử lại.';
    }

    // Quay về chi tiết lịch
    header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichId);
    exit();
}

    public function xoaYeuCauDacBiet() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;

        $redirectAct = $lichKhoiHanhId > 0
            ? 'lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId
            : 'lichKhoiHanh/index';

        $this->requirePostCsrf($redirectAct);

        require_once 'models/YeuCauDacBiet.php';
        $yeuCauModel = new YeuCauDacBiet();

        $yeuCau = $yeuCauModel->findByIdWithRelations($id);
        if (!$yeuCau) {
            $_SESSION['error'] = 'Không tìm thấy yêu cầu đặc biệt.';
        } else {
            $result = $yeuCauModel->deleteById($id);
            if ($result) {
                $_SESSION['success'] = 'Xóa yêu cầu đặc biệt thành công.';
            } else {
                $_SESSION['error'] = 'Không thể xóa yêu cầu đặc biệt.';
            }
        }

        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
        exit();
    }

    // Xóa khách chi tiết
    public function xoaKhachChiTiet() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $this->requirePostCsrf('lichKhoiHanh/index');

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        
        require_once 'models/CheckinKhach.php';
        $checkinModel = new CheckinKhach();
        
        $khach = $checkinModel->findById($id);
        if (!$khach) {
            $_SESSION['error'] = 'Không tìm thấy khách.';
        } else {
            $result = $checkinModel->delete($id);
            if ($result) {
                $_SESSION['success'] = 'Xóa khách thành công.';
            } else {
                $_SESSION['error'] = 'Không thể xóa khách.';
            }
        }
        
        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
        exit();
    }

    public function themNhatKy() {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=lichKhoiHanh/index');
            exit;
        }

        // Lấy dữ liệu form
        $lichId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;
        $loaiSuKien = $_POST['loai_su_kien'] ?? null;
        $thoiGian = $_POST['thoi_gian_su_kien'] ?? null; // có thể là datetime-local
        $nguoiGhiChep = trim($_POST['nguoi_ghi_chep'] ?? ($_SESSION['ho_ten'] ?? 'Admin'));
        $diaDiem = $_POST['dia_diem'] ?? null;
        $noiDung = trim($_POST['noi_dung'] ?? '');

        if ($lichId <= 0) {
            $_SESSION['error'] = 'Lịch khởi hành không hợp lệ.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit;
        }

        // Lấy thông tin lich_khoi_hanh để có tour_id và hdv_id
        require_once 'models/LichKhoiHanh.php';
        $lkModel = new LichKhoiHanh();
        $lich = $lkModel->findById($lichId);
        if (!$lich) {
            $_SESSION['error'] = 'Không tìm thấy lịch khởi hành.';
            header('Location: index.php?act=lichKhoiHanh/index');
            exit;
        }

        $tourId = $lich['tour_id'] ?? null;
        $hdvId = $lich['hdv_id'] ?? null;

        // Nếu không có hdv_id, thử lấy từ phân bổ nhân sự (nếu có)
        if (empty($hdvId)) {
            if (!empty($this->phanBoNhanSuModel)) {
                $pbn = $this->phanBoNhanSuModel->getByLichKhoiHanh($lichId);
                if (!empty($pbn) && isset($pbn[0]['nhan_su_id'])) {
                    $hdvId = $pbn[0]['nhan_su_id'];
                }
            }
        }

        // Chuẩn bị dữ liệu chèn
        // Model NhatKyTour::insert(tour_id, nhan_su_id, noi_dung, ngay_ghi) tồn tại và sử dụng CURDATE nếu muốn
        require_once 'models/NhatKyTour.php';
        $nktModel = new NhatKyTour();

        // Dựng nội dung lưu: có thể lưu loại sự kiện + nội dung
        $contentToSave = $noiDung;
        if ($loaiSuKien) {
            $contentToSave = ($loaiSuKien . ($contentToSave ? ' - ' . $contentToSave : ''));
        }

        // Map thời gian: nếu client gửi datetime-local như "2025-12-05T14:30", chuyển về 'Y-m-d H:i:s'
        $ngayGhi = null;
        if (!empty($thoiGian)) {
            // một số browser gửi "YYYY-MM-DDTHH:MM"
            $thoiGian = str_replace('T', ' ', $thoiGian);
            $ngayGhi = date('Y-m-d H:i:s', strtotime($thoiGian));
        } else {
            $ngayGhi = date('Y-m-d H:i:s');
        }

        // Gọi model insert. NhatKyTour::insert chỉ chấp nhận (tour_id, nhan_su_id, noi_dung, ngay_ghi)
        $insertData = [
            'tour_id' => $tourId ?? 0,
            'nhan_su_id' => $hdvId ?? 0,
            'noi_dung' => $contentToSave,
            'ngay_ghi' => $ngayGhi
        ];

        $ok = $nktModel->insert($insertData);

        if ($ok) {
            $_SESSION['success'] = 'Đã thêm ghi chép nhật ký.';
        } else {
            $_SESSION['error'] = 'Lỗi khi thêm nhật ký.';
        }

        // Quay về chi tiết lịch khởi hành
        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichId);
        exit;
    }

    public function xoaNhatKy() {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            header('Location: index.php?act=lichKhoiHanh/index');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $lichKhoiHanhId = isset($_POST['lich_khoi_hanh_id']) ? (int)$_POST['lich_khoi_hanh_id'] : 0;

        $redirectAct = $lichKhoiHanhId > 0
            ? 'lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId
            : 'lichKhoiHanh/index';

        $this->requirePostCsrf($redirectAct);

        require_once 'models/NhatKyTour.php';
        $nhatKyModel = new NhatKyTour();

        $result = $id > 0 ? $nhatKyModel->deleteById($id) : false;
        if ($result) {
            $_SESSION['success'] = 'Xóa nhật ký thành công.';
        } else {
            $_SESSION['error'] = 'Không thể xóa nhật ký.';
        }

        header('Location: index.php?act=lichKhoiHanh/chiTiet&id=' . $lichKhoiHanhId);
        exit();
    }

    
}

