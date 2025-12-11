<?php

class AdminController {
    
    public function __construct() {
        requireRole('Admin');
        // khi vào gốc dự án sẽ gọi new AdminController(). Trong AdminController::__construct() có requireRole('Admin') → requireLogin() → nếu chưa đăng nhập thì chuyển hướng sang auth/login. Nên luôn thấy trang đăng nhập trước khi có session.
    }
    
    public function dashboard() {
        // Lấy dữ liệu tài chính các tour
        require_once __DIR__ . '/../models/GiaoDich.php';
        $tourModel = new Tour();
        $giaoDichModel = new GiaoDich();
        $toursRaw = $tourModel->getAll();
        $tours = [];
        foreach ($toursRaw as $tour) {
            $tongThu = $giaoDichModel->getTongThuByTourId($tour['tour_id']);
            $tongChi = $giaoDichModel->getTongChiByTourId($tour['tour_id']);
            $loiNhuan = $tongThu - $tongChi;
            $tours[] = [
                'ten_tour' => $tour['ten_tour'],
                'tong_thu' => $tongThu,
                'tong_chi_thuc_te' => $tongChi,
                'tong_du_toan' => $tour['gia_co_ban'], // hoặc trường dự toán phù hợp
                'loi_nhuan' => $loiNhuan
            ];
        }
        // Lấy doanh thu từng tháng (12 tháng gần nhất)
        $doanhThuTheoThang = $giaoDichModel->getTongThuTheoThang(12);
        require 'views/admin/dashboard.php';
    }
    
    public function quanLyTour() {
        $tourModel = new Tour();
        
        // Lọc theo loại tour
        $loaiTour = $_GET['loai_tour'] ?? '';
        $trangThai = $_GET['trang_thai'] ?? '';
        $search = trim($_GET['search'] ?? '');
        
        if (!empty($loaiTour) || !empty($trangThai) || !empty($search)) {
            $conditions = [];
            if (!empty($loaiTour)) {
                $conditions['loai_tour'] = $loaiTour;
            }
            if (!empty($trangThai)) {
                $conditions['trang_thai'] = $trangThai;
            }
            $tours = $tourModel->find($conditions);
            
            // Lọc theo tìm kiếm nếu có
            if (!empty($search)) {
                $tours = array_filter($tours, function($tour) use ($search) {
                    return stripos($tour['ten_tour'] ?? '', $search) !== false;
                });
            }
        } else {
        $tours = $tourModel->getAll();
        }
        
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
    $search = $_GET['search'] ?? '';
    $role = $_GET['role'] ?? '';
    error_log('DEBUG: role param = ' . $role);
    
    // 2. Load Model và gọi phương thức lọc
        require_once __DIR__ . '/../models/NguoiDung.php';
    $nguoiDungModel = new NguoiDung();
    
    // Phương thức này cần được bạn tạo trong NguoiDung.php
    $users = $nguoiDungModel->getFilteredUsers($search, $role);
    
    // 3. Truyền các biến cần thiết xuống View
    // View của bạn cần $users, $search, và $role để hiển thị dữ liệu và giữ trạng thái form.
    // Nếu bạn không dùng framework, cách đơn giản nhất là khai báo chúng:
    
    // $users đã có
    // $search đã có
    // $role đã có
    
    // 4. Load View
        require __DIR__ . '/../views/admin/quan_ly_nguoi_dung.php';
    }
// ... các code khác ...
    
    public function quanLyBooking() {
        $bookingModel = new Booking();
        
        // Luôn dùng getAllWithDetails để có đầy đủ thông tin khách hàng
        $bookings = $bookingModel->getAllWithDetails();
        
        // Lọc theo trạng thái nếu có
        if (isset($_GET['trang_thai']) && !empty($_GET['trang_thai'])) {
            $bookings = array_filter($bookings, function($booking) {
                return $booking['trang_thai'] == $_GET['trang_thai'];
            });
            $bookings = array_values($bookings);
        }
        
        // Lấy yêu cầu tour cho mỗi booking
        try {
            require_once 'models/ThongBao.php';
            require_once 'models/KhachHang.php';
            
            $thongBaoModel = new ThongBao();
            $khachHangModel = new KhachHang();
            
            // Lấy tất cả yêu cầu tour
            $yeuCauList = $thongBaoModel->getYeuCauTour(['limit' => 1000]);
            
            // Tạo map: nguoi_dung_id => yêu_cau_tour (lấy yêu cầu mới nhất)
            $yeuCauMap = [];
            if (!empty($yeuCauList)) {
                foreach ($yeuCauList as $yc) {
                    if (!empty($yc['nguoi_gui_id'])) {
                        if (!isset($yeuCauMap[$yc['nguoi_gui_id']])) {
                            $yeuCauMap[$yc['nguoi_gui_id']] = $yc;
                        }
                    }
                }
            }
            
            // Tạo map khach_hang_id => nguoi_dung_id để tối ưu
            $khachHangMap = [];
            $khachHangIds = array_filter(array_unique(array_column($bookings, 'khach_hang_id')));
            if (!empty($khachHangIds)) {
                foreach ($khachHangIds as $khId) {
                    if (!empty($khId)) {
                        $kh = $khachHangModel->findById($khId);
                        if ($kh && !empty($kh['nguoi_dung_id'])) {
                            $khachHangMap[$khId] = $kh['nguoi_dung_id'];
                        }
                    }
                }
            }
            
            // Gắn yêu cầu tour vào mỗi booking
            foreach ($bookings as &$booking) {
                $booking['yeu_cau_tour'] = null;
                if (!empty($booking['khach_hang_id']) && isset($khachHangMap[$booking['khach_hang_id']])) {
                    $nguoiDungId = $khachHangMap[$booking['khach_hang_id']];
                    $booking['yeu_cau_tour'] = $yeuCauMap[$nguoiDungId] ?? null;
                }
            }
            unset($booking); // Unset reference
        } catch (Exception $e) {
            // Nếu có lỗi, đặt yeu_cau_tour = null cho tất cả booking
            foreach ($bookings as &$booking) {
                $booking['yeu_cau_tour'] = null;
            }
            unset($booking);
        }
        
        // Lọc theo yêu cầu tour nếu có
        if (isset($_GET['co_yeu_cau_tour']) && $_GET['co_yeu_cau_tour'] !== '') {
            $coYeuCau = $_GET['co_yeu_cau_tour'] == '1';
            $bookings = array_filter($bookings, function($booking) use ($coYeuCau) {
                return $coYeuCau ? !empty($booking['yeu_cau_tour']) : empty($booking['yeu_cau_tour']);
            });
            $bookings = array_values($bookings); // Re-index array
        }
        
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
        $tourList = $tourModel->getAll();

        // Danh sách booking để admin có thể chọn khi tạo yêu cầu mới
        $bookingModel = new Booking();
        $bookingList = $bookingModel->getAllWithDetails();

        require 'views/admin/quan_ly_yeu_cau_dac_biet.php';
    }

    public function capNhatYeuCauDacBiet() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        $yeuCauId = isset($_POST['yeu_cau_id']) ? (int)$_POST['yeu_cau_id'] : 0;
        if ($yeuCauId <= 0) {
            $_SESSION['error'] = 'Thiếu mã yêu cầu cần cập nhật.';
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        require_once 'models/YeuCauDacBiet.php';
        $yeuCauModel = new YeuCauDacBiet();

        $data = [
            'trang_thai' => $_POST['trang_thai'] ?? null,
            'muc_do_uu_tien' => $_POST['muc_do_uu_tien'] ?? null,
            'ghi_chu_hdv' => $_POST['ghi_chu_hdv'] ?? null
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

        $bookingId = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
        if ($bookingId <= 0) {
            $_SESSION['error'] = 'Vui lòng chọn booking/khách hàng cần tạo yêu cầu.';
            header('Location: index.php?act=admin/yeuCauDacBiet');
            exit();
        }

        require_once 'models/YeuCauDacBiet.php';
        $yeuCauModel = new YeuCauDacBiet();

        $data = [
            'loai_yeu_cau' => $_POST['loai_yeu_cau'] ?? 'khac',
            'tieu_de' => trim($_POST['tieu_de'] ?? ''),
            'mo_ta' => $_POST['mo_ta'] ?? null,
            'muc_do_uu_tien' => $_POST['muc_do_uu_tien'] ?? 'trung_binh',
            'trang_thai' => $_POST['trang_thai'] ?? 'moi',
            'ghi_chu_hdv' => $_POST['ghi_chu_hdv'] ?? null,
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
            $nguoiDungId = isset($_POST['nguoi_dung_id']) && $_POST['nguoi_dung_id'] !== '' 
                ? (int)$_POST['nguoi_dung_id'] 
                : null;
            $tenDonVi = trim($_POST['ten_don_vi'] ?? '');
            $loaiDichVu = $_POST['loai_dich_vu'] ?? null;
            $diaChi = $_POST['dia_chi'] ?? null;
            $lienHe = $_POST['lien_he'] ?? null;
            $moTa = $_POST['mo_ta'] ?? null;
            
            if ($tenDonVi === '') {
                $_SESSION['error'] = 'Tên đơn vị không được để trống';
            } else {
                try {
                    $data = [
                        'ten_don_vi'   => $tenDonVi,
                        'loai_dich_vu' => $loaiDichVu,
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
                    ORDER BY nd.ngay_tao DESC";
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
            $id = $_POST['id_nha_cung_cap'] ?? 0;
            $tenDonVi = $_POST['ten_don_vi'] ?? '';
            $loaiDichVu = $_POST['loai_dich_vu'] ?? null;
            $diaChi = $_POST['dia_chi'] ?? null;
            $lienHe = $_POST['lien_he'] ?? null;
            $moTa = $_POST['mo_ta'] ?? null;
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID nhà cung cấp không hợp lệ';
            } elseif (empty($tenDonVi)) {
                $_SESSION['error'] = 'Tên đơn vị không được để trống';
            } else {
                try {
                    $data = [
                        'ten_don_vi' => $tenDonVi,
                        'loai_dich_vu' => $loaiDichVu,
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
            $id = $_POST['id_nha_cung_cap'] ?? 0;
            $matKhau = $_POST['mat_khau'] ?? '';
            $lyDoXoa = $_POST['ly_do_xoa'] ?? '';
            
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

        $serviceId = (int)($_POST['dich_vu_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $nccId = (int)($_POST['ncc_id'] ?? 0);
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
                    $giaTien = (int)($_POST['gia_tien'] ?? 0);
                    if ($giaTien <= 0) {
                        throw new Exception('Giá tiền phải lớn hơn 0');
                    }
                    $nhaCungCapModel->xacNhanDichVu($serviceId, $giaTien);
                    $_SESSION['success'] = 'Đã xác nhận dịch vụ';
                    break;
                case 'reject':
                    $ghiChu = trim($_POST['ghi_chu'] ?? '');
                    $nhaCungCapModel->tuChoiDichVu($serviceId, $ghiChu ?: null);
                    $_SESSION['success'] = 'Đã từ chối dịch vụ';
                    break;
                case 'update_price':
                    $giaTien = (int)($_POST['gia_tien'] ?? 0);
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
        require 'views/admin/danh_gia.php';
    }
    public function nhanSu() {
        $nhanSuModel = new NhanSu();
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';
        // load available roles for tabs
        $roles = $nhanSuModel->getRoles();
        
        // build data grouped by role (for tabs)
        $data_by_role = [];
        if (!empty($roles)) {
            foreach ($roles as $r) {
                $data_by_role[$r] = $nhanSuModel->getByRole($r);
            }
        }
        
        // apply filters: if search query, search across all; if role filter, use that role's data
        if ($q !== '') {
            $nhan_su_list = $nhanSuModel->search($q);
            $active_role = null;
        } elseif ($role !== '' && isset($data_by_role[$role])) {
            $nhan_su_list = $data_by_role[$role];
            $active_role = $role;
        } else {
            $nhan_su_list = $nhanSuModel->getAll();
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
            $model = new HDV();
            $data = [
                'ho_ten' => $_POST['ho_ten'] ?? '',
                'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
                'anh' => $_POST['anh'] ?? null,
                'so_dien_thoai' => $_POST['so_dien_thoai'] ?? null,
                'email' => $_POST['email'] ?? null,
                'dia_chi' => $_POST['dia_chi'] ?? null,
                'chung_chi' => $_POST['chung_chi'] ?? null,
                'ngon_ngu' => $_POST['ngon_ngu'] ?? null,
                'kinh_nghiem' => $_POST['kinh_nghiem'] ?? null,
                'suc_khoe' => $_POST['suc_khoe'] ?? null,
                'group_id' => $_POST['group_id'] ?? null,
                'note' => $_POST['note'] ?? null,
            ];
            $ok = $model->insert($data);
            $_SESSION['flash'] = $ok ? ['type'=>'success','message'=>'Thêm HDV thành công'] : ['type'=>'danger','message'=>'Thêm HDV thất bại'];
        }
        header('Location: index.php?act=admin/quanLyHDV'); exit;
    }

    public function quanLyHDVUpdate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new HDV();
            $id = isset($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : 0;
            if ($id > 0) {
                $data = [
                    'ho_ten' => $_POST['ho_ten'] ?? '',
                    'ngay_sinh' => $_POST['ngay_sinh'] ?? null,
                    'anh' => $_POST['anh'] ?? null,
                    'so_dien_thoai' => $_POST['so_dien_thoai'] ?? null,
                    'email' => $_POST['email'] ?? null,
                    'dia_chi' => $_POST['dia_chi'] ?? null,
                    'chung_chi' => $_POST['chung_chi'] ?? null,
                    'ngon_ngu' => $_POST['ngon_ngu'] ?? null,
                    'kinh_nghiem' => $_POST['kinh_nghiem'] ?? null,
                    'suc_khoe' => $_POST['suc_khoe'] ?? null,
                    'group_id' => $_POST['group_id'] ?? null,
                    'is_available' => isset($_POST['is_available']) ? 1 : 0,
                    'note' => $_POST['note'] ?? null,
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
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $groupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : null;
        $hdvModel = new HDV();
        $candidates = $hdvModel->getAll($groupId, true);
        $available = [];
        foreach ($candidates as $c) {
            if ($hdvModel->isAvailable($c['nhan_su_id'], $start, $end)) {
                $available[] = ['id'=>$c['nhan_su_id'],'ho_ten'=>$c['ho_ten']];
            }
        }
        echo json_encode(['available'=>$available]); exit;
    }

    public function nhanSuCreate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new NhanSu();
            $data = [
                    'nguoi_dung_id' => $_POST['nguoi_dung_id'] ?? null,
                    'vai_tro' => $_POST['vai_tro'] ?? 'Khac',
                'chung_chi' => $_POST['chung_chi'] ?? '',
                'ngon_ngu' => $_POST['ngon_ngu'] ?? '',
                'kinh_nghiem' => $_POST['kinh_nghiem'] ?? '',
                'suc_khoe' => $_POST['suc_khoe'] ?? '',
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
            $model = new NhanSu();
            $id = isset($_POST['nhan_su_id']) ? (int)$_POST['nhan_su_id'] : 0;
            if ($id <= 0) {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'ID nhân sự không hợp lệ.'];
                header('Location: index.php?act=admin/nhanSu');
                exit;
            }
            $data = [
                'vai_tro' => $_POST['vai_tro'] ?? 'Khac',
                'chung_chi' => $_POST['chung_chi'] ?? '',
                'ngon_ngu' => $_POST['ngon_ngu'] ?? '',
                'kinh_nghiem' => $_POST['kinh_nghiem'] ?? '',
                'suc_khoe' => $_POST['suc_khoe'] ?? '',
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $delete_user = isset($_GET['delete_user']) && $_GET['delete_user'] === '1' ? true : false;
        
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
        $tours = $tourModel->getAll();
        
        // Lấy danh sách HDV cho filter
        $hdvModel = new HDV();
        $hdvList = $hdvModel->getAll();
        
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
        $tours = $tourModel->getAll();
        
        // Lấy danh sách HDV
        $hdvModel = new HDV();
        $hdvList = $hdvModel->getAll();
        
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
                    $fileName = time() . '_' . $i . '_' . basename($_FILES['hinh_anh']['name'][$i]);
                    $targetFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'][$i], $targetFile)) {
                        $imageUrls[] = $targetFile;
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
        $conn = connectDB();
        $id = $_GET['id'] ?? 0;
        
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
            $lichKhoiHanhModel = new LichKhoiHanh();
            $bookingModel = new Booking();
            $khachHangModel = new KhachHang();
            $nguoiDungModel = new NguoiDung();
            
            $lichKhoiHanh = $lichKhoiHanhModel->findById($lichKhoiHanhId);
            if (!$lichKhoiHanh) {
                $_SESSION['error'] = 'Lịch khởi hành không tồn tại.';
                header('Location: index.php?act=admin/danhSachKhachTheoTour');
                exit();
            }
            
            // Tìm hoặc tạo người dùng
            $email = trim($_POST['email'] ?? '');
            $hoTen = trim($_POST['ho_ten'] ?? '');
            $soDienThoai = trim($_POST['so_dien_thoai'] ?? '');
            
            if (empty($email) || empty($hoTen)) {
                $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin khách hàng.';
                header('Location: index.php?act=admin/themKhachLichKhoiHanh&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit();
            }
            
            // Tìm người dùng theo email
            $nguoiDung = $nguoiDungModel->findByEmail($email);
            if (!$nguoiDung) {
                // Tạo người dùng mới
                $nguoiDungId = $nguoiDungModel->insert([
                    'ho_ten' => $hoTen,
                    'email' => $email,
                    'so_dien_thoai' => $soDienThoai,
                    'vai_tro' => 'KhachHang',
                    'mat_khau' => password_hash('123456', PASSWORD_DEFAULT) // Mật khẩu mặc định
                ]);
                $nguoiDung = $nguoiDungModel->findById($nguoiDungId);
            }
            
            // Tìm hoặc tạo khách hàng
            $khachHang = $khachHangModel->findOrCreateByNguoiDungInfo(
                $nguoiDung['id'],
                $_POST['dia_chi'] ?? null,
                $_POST['gioi_tinh'] ?? null,
                $_POST['ngay_sinh'] ?? null
            );
            
            // Tạo booking
            $bookingData = [
                'tour_id' => $lichKhoiHanh['tour_id'],
                'khach_hang_id' => $khachHang['khach_hang_id'],
                'ngay_dat' => date('Y-m-d'),
                'ngay_khoi_hanh' => $lichKhoiHanh['ngay_khoi_hanh'],
                'ngay_ket_thuc' => $lichKhoiHanh['ngay_ket_thuc'],
                'so_nguoi' => (int)($_POST['so_nguoi'] ?? 1),
                'tong_tien' => (float)($_POST['tong_tien'] ?? 0),
                'trang_thai' => $_POST['trang_thai'] ?? 'ChoXacNhan',
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            $bookingId = $bookingModel->insert($bookingData);
            if ($bookingId) {
                $_SESSION['success'] = 'Thêm khách vào lịch khởi hành thành công.';
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
            exit();
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
            $bookingModel = new Booking();
            
            $booking = $bookingModel->findById($bookingId);
            if (!$booking) {
                $_SESSION['error'] = 'Booking không tồn tại.';
                header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
                exit();
            }
            
            $data = [
                'so_nguoi' => (int)($_POST['so_nguoi'] ?? 1),
                'tong_tien' => (float)($_POST['tong_tien'] ?? 0),
                'trang_thai' => $_POST['trang_thai'] ?? 'ChoXacNhan',
                'ghi_chu' => $_POST['ghi_chu'] ?? null
            ];
            
            $result = $bookingModel->update($bookingId, $data);
            if ($result) {
                $_SESSION['success'] = 'Cập nhật thông tin booking thành công.';
            } else {
                $_SESSION['error'] = 'Không thể cập nhật booking.';
            }
            
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
            exit();
        }
        
        // GET: hiển thị form
        $bookingModel = new Booking();
        $lichKhoiHanhModel = new LichKhoiHanh();
        $tourModel = new Tour();
        
        $booking = $bookingModel->getBookingWithDetails($bookingId);
        if (!$booking) {
            $_SESSION['error'] = 'Booking không tồn tại.';
            header('Location: index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=' . $lichKhoiHanhId);
            exit();
        }
        
        $lichKhoiHanh = $lichKhoiHanhModel->findById($lichKhoiHanhId);
        $tour = $tourModel->findById($booking['tour_id']);
        
        require 'views/admin/sua_khach_lich_khoi_hanh.php';
    }
    
    // Xóa khách khỏi lịch khởi hành
    public function xoaKhachLichKhoiHanh() {
        $bookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
        $lichKhoiHanhId = isset($_GET['lich_khoi_hanh_id']) ? (int)$_GET['lich_khoi_hanh_id'] : 0;
        
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
        exit();
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
            exit();
        }
        
        $thongBaoModel = new ThongBao();
        $yeuCau = $thongBaoModel->findById($id);
        
        if (!$yeuCau || $yeuCau['tieu_de'] !== 'Yêu cầu tour theo mong muốn') {
            $_SESSION['error'] = 'Yêu cầu không tồn tại.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit();
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
        $tourList = $tourModel->getAll();
        
        require 'views/admin/chi_tiet_yeu_cau_tour.php';
    }
    
    /**
     * Xử lý phản hồi yêu cầu tour
     */
    public function phanHoiYeuCauTour() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit();
        }
        
        require_once 'models/ThongBao.php';
        require_once 'models/Tour.php';
        
        $yeuCauId = isset($_POST['yeu_cau_id']) ? (int)$_POST['yeu_cau_id'] : 0;
        $phanHoi = trim($_POST['phan_hoi'] ?? '');
        $trangThai = $_POST['trang_thai'] ?? 'DaXuLy';
        
        if ($yeuCauId <= 0) {
            $_SESSION['error'] = 'ID yêu cầu không hợp lệ.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit();
        }
        
        if (empty($phanHoi)) {
            $_SESSION['error'] = 'Vui lòng nhập nội dung phản hồi.';
            header('Location: index.php?act=admin/chiTietYeuCauTour&id=' . $yeuCauId);
            exit();
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
        exit();
    }
    
    /**
     * Tạo tour mới từ yêu cầu của khách hàng
     */
    public function taoTourTuYeuCau() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit();
        }
        
        require_once 'models/ThongBao.php';
        require_once 'models/Tour.php';
        
        $yeuCauId = isset($_POST['yeu_cau_id']) ? (int)$_POST['yeu_cau_id'] : 0;
        
        if ($yeuCauId <= 0) {
            $_SESSION['error'] = 'ID yêu cầu không hợp lệ.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit();
        }
        
        $thongBaoModel = new ThongBao();
        $yeuCau = $thongBaoModel->findById($yeuCauId);
        
        if (!$yeuCau) {
            $_SESSION['error'] = 'Yêu cầu không tồn tại.';
            header('Location: index.php?act=admin/quanLyYeuCauTour');
            exit();
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
            exit();
        } else {
            $_SESSION['error'] = 'Không thể tạo tour mới.';
            header('Location: index.php?act=admin/chiTietYeuCauTour&id=' . $yeuCauId);
            exit();
        }
    }
}
