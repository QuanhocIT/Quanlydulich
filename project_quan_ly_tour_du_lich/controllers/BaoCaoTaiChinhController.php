<?php
require_once __DIR__ . '/../models/GiaoDich.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/KhachHang.php';
require_once __DIR__ . '/../models/LichSuKhachHang.php';
require_once __DIR__ . '/../models/DuToanTour.php';
require_once __DIR__ . '/../models/ChiPhiThucTe.php';

class BaoCaoTaiChinhController {
        // Hiển thị thu chi từng tour
        public function thuChiTour() {
            // Lấy danh sách tour
            $tours = $this->tourModel->getAll();
            // Lấy tổng thu chi từng tour
            $thuChiTours = [];
            foreach ($tours as $tour) {
                $tongThu = $this->giaoDichModel->getTongThuByTourId($tour['tour_id']);
                $tongChi = $this->giaoDichModel->getTongChiByTourId($tour['tour_id']);
                $thuChiTours[] = [
                    'tour' => $tour,
                    'tong_thu' => $tongThu,
                    'tong_chi' => $tongChi,
                    'loi_nhuan' => $tongThu - $tongChi
                ];
            }
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/thu_chi_tour.php';
        }
    private $giaoDichModel;
    private $tourModel;
    private $bookingModel;
    private $khachHangModel;
    private $lichSuModel;
    private $duToanModel;
    private $chiPhiModel;

    public function __construct() {
        $this->giaoDichModel = new GiaoDich();
        $this->tourModel = new Tour();
        $this->bookingModel = new Booking();
        $this->khachHangModel = new KhachHang();
        $this->lichSuModel = new LichSuKhachHang();
        $this->duToanModel = new DuToanTour();
        $this->chiPhiModel = new ChiPhiThucTe();
    }

    // Dashboard tổng quan tài chính
    public function dashboard() {
        // Lấy tháng hiện tại
        $thangHienTai = date('Y-m');
        $tuNgay = date('Y-m-01');
        $denNgay = date('Y-m-t');
        
        // Thống kê giao dịch tháng này
        $thongKe = $this->giaoDichModel->getThongKeTongHop($tuNgay, $denNgay);
        
        // Tính tổng thu, tổng chi, lợi nhuận (kết quả trả về dạng 1 bản ghi tổng hợp)
        $tongThu = (float)($thongKe['tong_thu'] ?? 0);
        $tongChi = (float)($thongKe['tong_chi'] ?? 0);
        $loiNhuan = (float)($thongKe['lai_lo'] ?? ($tongThu - $tongChi));
        
        // Lấy top 5 tour có doanh thu cao nhất
        $topTours = $this->getTopToursByRevenue(5);
        
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/dashboard.php';
    }

    // Hiển thị toàn bộ giao dịch của một tour
    public function giaoDichTheoTour() {
        $tourId = $_GET['tour_id'] ?? null;
        if ($tourId) {
            $giaoDichs = $this->giaoDichModel->getByTourId($tourId);
            $tour = $this->tourModel->findById($tourId);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/chi_tiet_thu_chi_tour.php';
        } else {
            $_SESSION['error'] = 'Không tìm thấy tour.';
            header('Location: index.php?act=admin/baoCaoTaiChinh');
            exit;
        }
    }
    
    // Lịch sử giao dịch nội bộ
    public function lichSuGiaoDich() {
        // Xử lý bộ lọc
        $filters = [
            'loai' => $_GET['loai'] ?? '',
            'loai_giao_dich' => $_GET['loai_giao_dich'] ?? '',
            'tour_id' => $_GET['tour_id'] ?? '',
            'khach_hang_id' => $_GET['khach_hang_id'] ?? '',
            'tu_ngay' => $_GET['tu_ngay'] ?? '',
            'den_ngay' => $_GET['den_ngay'] ?? '',
            'keyword' => $_GET['keyword'] ?? ''
        ];
        
        // Xóa các filter rỗng
        $filters = array_filter($filters);
        
        // Lấy danh sách giao dịch
        // Chưa có hàm search, chỉ dùng getAll
        $giaoDichs = $this->giaoDichModel->getAll();
        
        // Tính tổng thu, tổng chi
        $tongThu = 0;
        $tongChi = 0;
        foreach ($giaoDichs as $gd) {
            if ($gd['loai'] == 'Thu') {
                $tongThu += $gd['so_tien'];
            } else {
                $tongChi += $gd['so_tien'];
            }
        }
        
        // Lấy danh sách tours và khách hàng cho filter
        $tours = $this->tourModel->getAll();
        $khachHangs = $this->khachHangModel->getAll();
        
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/lich_su_giao_dich.php';
    }

    // Hiển thị chi tiết một giao dịch
    public function chiTietGiaoDich() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $giao_dich = $this->giaoDichModel->findById($id);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/chi_tiet_giao_dich.php';
        } else {
            $_SESSION['error'] = 'Không tìm thấy giao dịch.';
            header('Location: index.php?act=admin/lichSuGiaoDich');
            exit;
        }
    }
    
    // Báo cáo công nợ HDV
    public function congNo() {
        // Công nợ HDV: lấy từ bảng công nợ HDV thực tế
        $sql = "SELECT c.*, nd.ho_ten as ten_hdv, t.ten_tour FROM cong_no_hdv c JOIN nhan_su ns ON c.hdv_id = ns.nhan_su_id JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id JOIN tour t ON c.tour_id = t.tour_id";
        $stmt = $this->giaoDichModel->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $congNoHDV = [];
        foreach ($rows as $row) {
            // Lịch sử thanh toán HDV
            $sql2 = "SELECT ngay_thanh_toan as ngay, so_tien as so_tien FROM lich_su_thanh_toan_hdv WHERE cong_no_hdv_id = ? ORDER BY ngay_thanh_toan ASC";
            $stmt2 = $this->giaoDichModel->conn->prepare($sql2);
            $stmt2->execute([$row['id']]);
            $lich_su = $stmt2->fetchAll();
            $congNoHDV[] = [
                'ten_hdv' => $row['ten_hdv'],
                'ten_tour' => $row['ten_tour'],
                'tong_thu' => $row['so_tien'],
                'tong_chi' => 0,
                'cong_no' => $row['so_tien'],
                'lich_su_thanh_toan' => $lich_su
            ];
        }
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no_hdv.php';
    }
    
    // Báo cáo lãi lỗ từng tour
    public function laiLoTour() {
        $tuNgay = $_GET['tu_ngay'] ?? date('Y-m-01');
        $denNgay = $_GET['den_ngay'] ?? date('Y-m-t');
        
        $tours = $this->tourModel->getAll();
        
        $baoCao = [];
        foreach ($tours as $tour) {
            $tongThu = $this->giaoDichModel->getTongThuByTour($tour['tour_id']);
            $tongChi = $this->giaoDichModel->getTongChiByTour($tour['tour_id']);
            $loiNhuan = $tongThu - $tongChi;
            $tyLe = $tongThu > 0 ? ($loiNhuan / $tongThu * 100) : 0;
            
            $baoCao[] = [
                'tour' => $tour,
                'doanh_thu' => $tongThu,
                'chi_phi' => $tongChi,
                'loi_nhuan' => $loiNhuan,
                'ty_suat' => $tyLe
            ];
        }
        
        // Sắp xếp theo lợi nhuận giảm dần
        usort($baoCao, function($a, $b) {
            return $b['loi_nhuan'] - $a['loi_nhuan'];
        });
        
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/lai_lo_tour.php';
    }
    
    // Xuất báo cáo Excel/PDF
    public function xuatBaoCao() {
        $loaiBaoCao = $_GET['loai'] ?? 'giao_dich';
        $format = $_GET['format'] ?? 'excel';
        
        // TODO: Implement export functionality
        $_SESSION['info'] = 'Chức năng xuất báo cáo đang được phát triển';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    
    // Helper: Lấy top tours theo doanh thu
    private function getTopToursByRevenue($limit = 5) {
        $tours = $this->tourModel->getAll();
        $result = [];
        
        foreach ($tours as $tour) {
            $doanhThu = $this->giaoDichModel->getTongThuByTour($tour['tour_id']);
            $result[] = [
                'tour' => $tour,
                'doanh_thu' => $doanhThu
            ];
        }
        
        usort($result, function($a, $b) {
            return $b['doanh_thu'] - $a['doanh_thu'];
        });
        
        return array_slice($result, 0, $limit);
    }
    
    // ==================== QUẢN LÝ DỰ TOÁN TOUR ====================
    
    // Danh sách dự toán
    public function duToanTour() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->saveDuToan();
        }
        
        $tourId = $_GET['tour_id'] ?? null;
        
        if ($tourId) {
            // Xem chi tiết dự toán của 1 tour
            $tour = $this->tourModel->findById($tourId);
            $duToans = $this->duToanModel->getByTour($tourId);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/du_toan_chi_tiet.php';
        } else {
            // Danh sách tất cả tour có dự toán
            $duToans = $this->duToanModel->getAll();
            $tours = $this->tourModel->getAll();
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/danh_sach_du_toan.php';
        }
    }
    
    // Form tạo/sửa dự toán
    public function formDuToan() {
        $duToanId = $_GET['id'] ?? null;
        $tourId = $_GET['tour_id'] ?? null;
        
        if ($duToanId) {
            $duToan = $this->duToanModel->findById($duToanId);
            $tour = $this->tourModel->findById($duToan['tour_id']);
        } else {
            $duToan = null;
            $tour = $tourId ? $this->tourModel->findById($tourId) : null;
        }
        
        $tours = $this->tourModel->getAll();
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/form_du_toan.php';
    }
    
    // Lưu dự toán
    private function saveDuToan() {
        $duToanId = $_POST['du_toan_id'] ?? null;
        
        $data = [
            'tour_id' => $_POST['tour_id'],
            'lich_khoi_hanh_id' => $_POST['lich_khoi_hanh_id'] ?? null,
            'cp_phuong_tien' => $_POST['cp_phuong_tien'] ?? 0,
            'mo_ta_phuong_tien' => $_POST['mo_ta_phuong_tien'] ?? '',
            'cp_luu_tru' => $_POST['cp_luu_tru'] ?? 0,
            'mo_ta_luu_tru' => $_POST['mo_ta_luu_tru'] ?? '',
            'cp_ve_tham_quan' => $_POST['cp_ve_tham_quan'] ?? 0,
            'mo_ta_ve_tham_quan' => $_POST['mo_ta_ve_tham_quan'] ?? '',
            'cp_an_uong' => $_POST['cp_an_uong'] ?? 0,
            'mo_ta_an_uong' => $_POST['mo_ta_an_uong'] ?? '',
            'cp_huong_dan_vien' => $_POST['cp_huong_dan_vien'] ?? 0,
            'cp_dich_vu_bo_sung' => $_POST['cp_dich_vu_bo_sung'] ?? 0,
            'mo_ta_dich_vu' => $_POST['mo_ta_dich_vu'] ?? '',
            'cp_phat_sinh_du_kien' => $_POST['cp_phat_sinh_du_kien'] ?? 0,
            'mo_ta_phat_sinh' => $_POST['mo_ta_phat_sinh'] ?? '',
            'nguoi_tao_id' => $_SESSION['user_id']
        ];
        
        if ($duToanId) {
            $result = $this->duToanModel->update($duToanId, $data);
            $message = 'Cập nhật dự toán thành công!';
        } else {
            $result = $this->duToanModel->create($data);
            $message = 'Tạo dự toán thành công!';
        }
        
        if ($result) {
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra!';
        }
        
        header('Location: index.php?act=admin/duToanTour&tour_id=' . $data['tour_id']);
        exit;
    }
    
    // ==================== QUẢN LÝ CHI PHÍ THỰC TẾ ====================
    
    // Danh sách chi phí thực tế
    public function chiPhiThucTe() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->saveChiPhi();
        }
        
        $duToanId = $_GET['du_toan_id'] ?? null;
        
        if ($duToanId) {
            // Xem chi phí của 1 dự toán
            $duToan = $this->duToanModel->findById($duToanId);
            $chiPhis = $this->chiPhiModel->getByDuToan($duToanId);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/chi_phi_chi_tiet.php';
        } else {
            // Danh sách tất cả chi phí
            $chiPhis = $this->chiPhiModel->getAll();
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/danh_sach_chi_phi.php';
        }
    }
    
    // Form ghi nhận chi phí thực tế
    public function formChiPhi() {
        $chiPhiId = $_GET['id'] ?? null;
        $duToanId = $_GET['du_toan_id'] ?? null;
        
        if ($chiPhiId) {
            $chiPhi = $this->chiPhiModel->findById($chiPhiId);
            $duToan = $this->duToanModel->findById($chiPhi['du_toan_id']);
        } else {
            $chiPhi = null;
            $duToan = $duToanId ? $this->duToanModel->findById($duToanId) : null;
        }
        
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/form_chi_phi.php';
    }
    
    // Lưu chi phí thực tế
    private function saveChiPhi() {
        $chiPhiId = $_POST['chi_phi_id'] ?? null;
        
        $data = [
            'du_toan_id' => $_POST['du_toan_id'],
            'tour_id' => $_POST['tour_id'],
            'lich_khoi_hanh_id' => $_POST['lich_khoi_hanh_id'] ?? null,
            'loai_chi_phi' => $_POST['loai_chi_phi'],
            'ten_khoan_chi' => $_POST['ten_khoan_chi'],
            'so_tien' => $_POST['so_tien'],
            'ngay_phat_sinh' => $_POST['ngay_phat_sinh'],
            'mo_ta' => $_POST['mo_ta'] ?? '',
            'nguoi_ghi_nhan_id' => $_SESSION['user_id']
        ];
        
        // Xử lý upload chứng từ
        if (isset($_FILES['chung_tu']) && $_FILES['chung_tu']['error'] === 0) {
            $uploadDir = __DIR__ . '/../uploads/chung_tu/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . $_FILES['chung_tu']['name'];
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['chung_tu']['tmp_name'], $uploadPath)) {
                $data['chung_tu'] = 'uploads/chung_tu/' . $fileName;
            }
        }
        
        if ($chiPhiId) {
            $result = $this->chiPhiModel->update($chiPhiId, $data);
            $message = 'Cập nhật chi phí thành công!';
        } else {
            $result = $this->chiPhiModel->create($data);
            $message = 'Ghi nhận chi phí thành công!';
            
            // Kiểm tra cảnh báo
            $canhBao = $this->chiPhiModel->kiemTraCanhBao($data['du_toan_id']);
            if ($canhBao['canh_bao'] === 'VuotDuToan') {
                $_SESSION['warning'] = 'CẢNH BÁO: Chi phí thực tế đã vượt dự toán!';
            } elseif ($canhBao['canh_bao'] === 'GanVuot') {
                $_SESSION['warning'] = 'Lưu ý: Chi phí thực tế đã đạt 90% dự toán!';
            }
        }
        
        if ($result) {
            $_SESSION['success'] = $message;
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra!';
        }
        
        header('Location: index.php?act=admin/chiPhiThucTe&du_toan_id=' . $data['du_toan_id']);
        exit;
    }
    
    // Duyệt chi phí
    public function duyetChiPhi() {
        $chiPhiId = $_GET['id'];
        $result = $this->chiPhiModel->approve($chiPhiId, $_SESSION['user_id']);
        
        // Kiểm tra cảnh báo sau khi duyệt
        $chiPhi = $this->chiPhiModel->findById($chiPhiId);
        $canhBao = $this->chiPhiModel->kiemTraCanhBao($chiPhi['du_toan_id']);
        
        if ($result) {
            $_SESSION['success'] = 'Đã duyệt chi phí!';
            
            if ($canhBao['canh_bao'] === 'VuotDuToan') {
                $_SESSION['warning'] = 'CẢNH BÁO: Chi phí thực tế đã vượt dự toán!';
            } elseif ($canhBao['canh_bao'] === 'GanVuot') {
                $_SESSION['warning'] = 'Lưu ý: Chi phí thực tế đã đạt 90% dự toán!';
            }
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra!';
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Từ chối chi phí
    public function tuChoiChiPhi() {
        $chiPhiId = $_POST['id'];
        $lyDo = $_POST['ly_do'];
        
        $result = $this->chiPhiModel->reject($chiPhiId, $_SESSION['user_id'], $lyDo);
        
        if ($result) {
            $_SESSION['success'] = 'Đã từ chối chi phí!';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra!';
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // ==================== SO SÁNH DỰ TOÁN VS THỰC TẾ ====================
    
    public function soSanhDuToan() {
        $duToanId = $_GET['du_toan_id'] ?? null;
        
        if ($duToanId) {
            // So sánh chi tiết 1 dự toán
            $duToan = $this->duToanModel->findById($duToanId);
            $chiPhis = $this->chiPhiModel->getByDuToan($duToanId);
            
            // Tính toán theo từng loại
            $soSanh = [
                'PhuongTien' => [
                    'du_toan' => $duToan['cp_phuong_tien'],
                    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'PhuongTien')
                ],
                'LuuTru' => [
                    'du_toan' => $duToan['cp_luu_tru'],
                    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'LuuTru')
                ],
                'VeThamQuan' => [
                    'du_toan' => $duToan['cp_ve_tham_quan'],
                    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'VeThamQuan')
                ],
                'AnUong' => [
                    'du_toan' => $duToan['cp_an_uong'],
                    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'AnUong')
                ],
                'HuongDanVien' => [
                    'du_toan' => $duToan['cp_huong_dan_vien'],
                    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'HuongDanVien')
                ],
                'DichVuBoSung' => [
                    'du_toan' => $duToan['cp_dich_vu_bo_sung'],
                    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'DichVuBoSung')
                ],
                'PhatSinh' => [
                    'du_toan' => $duToan['cp_phat_sinh_du_kien'],
                    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'PhatSinh')
                ]
            ];
            
            // Chuẩn hóa dữ liệu cho view
            $chiPhiSoSanh = [];
            foreach ($soSanh as $loai => $cp) {
                $chenhLech = $cp['thuc_te'] - $cp['du_toan'];
                $ghiChu = '';
                if ($chenhLech < 0) {
                    $ghiChu = 'Tiết kiệm';
                } elseif ($chenhLech > 0) {
                    $ghiChu = 'Vượt dự toán';
                }
                $chiPhiSoSanh[] = [
                    'loai_chi_phi' => $loai,
                    'du_toan' => $cp['du_toan'],
                    'thuc_te' => $cp['thuc_te'],
                    'chenh_lech' => $chenhLech,
                    'ghi_chu' => $ghiChu
                ];
            }
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/so_sanh_chi_tiet.php';
        } else {
            // Tổng quan các dự toán có cảnh báo
            $canhBaos = $this->duToanModel->getDuToanCanhBao();
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/tong_quan_canh_bao.php';
        }
    }
    
    // Hiển thị nhắc hạn thu nợ/công nợ phải trả
    public function nhacHanCongNo() {
        $today = date('Y-m-d');
        $nhacHanCongNo = [];

        // Nhắc hạn công nợ khách hàng
        $bookings = $this->bookingModel->getAll();
        foreach ($bookings as $booking) {
            if (!empty($booking['han_thanh_toan'])) {
                $is_qua_han = $today > $booking['han_thanh_toan'];
                $is_sap_han = !$is_qua_han && (strtotime($booking['han_thanh_toan']) - strtotime($today) <= 3*24*3600);
                if ($is_qua_han || $is_sap_han) {
                    $khach = $this->khachHangModel->findById($booking['khach_hang_id']);
                    $tour = $this->tourModel->findById($booking['tour_id']);
                    $nhacHanCongNo[] = [
                        'doi_tuong' => 'Khách hàng ' . ($khach['ho_ten'] ?? $booking['khach_hang_id']),
                        'noi_dung' => 'Đến hạn thanh toán hợp đồng tour ' . ($tour['ten_tour'] ?? $booking['tour_id']),
                        'han' => $booking['han_thanh_toan'],
                        'is_qua_han' => $is_qua_han,
                        'is_sap_han' => $is_sap_han
                    ];
                }
            }
        }

        // Nhắc hạn công nợ nhà cung cấp
        $sqlNCC = "SELECT c.*, ncc.ten_don_vi FROM cong_no_nha_cung_cap c JOIN nha_cung_cap ncc ON c.nha_cung_cap_id = ncc.id_nha_cung_cap";
        $stmtNCC = $this->giaoDichModel->conn->prepare($sqlNCC);
        $stmtNCC->execute();
        $rowsNCC = $stmtNCC->fetchAll();
        foreach ($rowsNCC as $row) {
            if (!empty($row['han_thanh_toan'])) {
                $is_qua_han = $today > $row['han_thanh_toan'];
                $is_sap_han = !$is_qua_han && (strtotime($row['han_thanh_toan']) - strtotime($today) <= 3*24*3600);
                if ($is_qua_han || $is_sap_han) {
                    $nhacHanCongNo[] = [
                        'doi_tuong' => 'Nhà cung cấp ' . $row['ten_don_vi'],
                        'noi_dung' => 'Đến hạn thanh toán dịch vụ: ' . ($row['ghi_chu'] ?? ''),
                        'han' => $row['han_thanh_toan'],
                        'is_qua_han' => $is_qua_han,
                        'is_sap_han' => $is_sap_han
                    ];
                }
            }
        }

        // Nhắc hạn công nợ HDV
        $sqlHDV = "SELECT c.*, nd.ho_ten FROM cong_no_hdv c JOIN nhan_su h ON c.hdv_id = h.nhan_su_id JOIN nguoi_dung nd ON h.nguoi_dung_id = nd.id";
        $stmtHDV = $this->giaoDichModel->conn->prepare($sqlHDV);
        $stmtHDV->execute();
        $rowsHDV = $stmtHDV->fetchAll();
        foreach ($rowsHDV as $row) {
            if (!empty($row['han_thanh_toan'])) {
                $is_qua_han = $today > $row['han_thanh_toan'];
                $is_sap_han = !$is_qua_han && (strtotime($row['han_thanh_toan']) - strtotime($today) <= 3*24*3600);
                if ($is_qua_han || $is_sap_han) {
                    $nhacHanCongNo[] = [
                        'doi_tuong' => 'HDV ' . $row['ho_ten'],
                        'noi_dung' => 'Đến hạn thanh toán phí tour: ' . ($row['ghi_chu'] ?? ''),
                        'han' => $row['han_thanh_toan'],
                        'is_qua_han' => $is_qua_han,
                        'is_sap_han' => $is_sap_han
                    ];
                }
            }
        }

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/tong_quan_canh_bao.php';
    }
    
    // Hiển thị công nợ khách hàng
    public function congNoKhachHang() {
        // Lấy danh sách booking
        $bookings = $this->bookingModel->getAll();
        $congNoKhachHang = [];
        foreach ($bookings as $booking) {
            $khach = $this->khachHangModel->findById($booking['khach_hang_id']);
            $tour = $this->tourModel->findById($booking['tour_id']);
            // Tổng số tiền đã thanh toán: lấy từ giao_dich_tai_chinh theo tour_id và loai = 'Thu', lọc theo khach_hang_id
            $sql = "SELECT SUM(gdtc.so_tien) as da_thanh_toan FROM giao_dich_tai_chinh gdtc 
                    INNER JOIN booking b ON gdtc.tour_id = b.tour_id 
                    WHERE b.booking_id = ? AND gdtc.loai = 'Thu' AND b.khach_hang_id = ?";
            $stmt = $this->giaoDichModel->conn->prepare($sql);
            $stmt->execute([$booking['booking_id'], $booking['khach_hang_id']]);
            $daThanhToan = (float)($stmt->fetch()['da_thanh_toan'] ?? 0);
            $cong_no = max(0, (float)$booking['tong_tien'] - $daThanhToan);
            // Lịch sử thanh toán: lấy từ giao_dich_tai_chinh theo tour_id, loai = 'Thu', lọc theo khach_hang_id
            $sql2 = "SELECT gdtc.ngay_giao_dich as ngay, gdtc.so_tien FROM giao_dich_tai_chinh gdtc 
                      INNER JOIN booking b ON gdtc.tour_id = b.tour_id 
                      WHERE b.booking_id = ? AND gdtc.loai = 'Thu' AND b.khach_hang_id = ? 
                      ORDER BY gdtc.ngay_giao_dich ASC";
            $stmt2 = $this->giaoDichModel->conn->prepare($sql2);
            $stmt2->execute([$booking['booking_id'], $booking['khach_hang_id']]);
            $lich_su = $stmt2->fetchAll();
            $congNoKhachHang[] = [
                'ten_khach_hang' => $khach['ho_ten'] ?? 'N/A',
                'ten_tour' => $tour['ten_tour'] ?? 'N/A',
                'cong_no' => $cong_no,
                'lich_su_thanh_toan' => $lich_su
            ];
        }
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no_khach_hang.php';
    }

    // Hiển thị công nợ nhà cung cấp
    public function congNoNhaCungCap() {
        // Lấy danh sách công nợ NCC
        $sql = "SELECT c.*, ncc.ten_don_vi FROM cong_no_nha_cung_cap c JOIN nha_cung_cap ncc ON c.nha_cung_cap_id = ncc.id_nha_cung_cap";
        $stmt = $this->giaoDichModel->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $congNoNhaCungCap = [];
        foreach ($rows as $row) {
            // Lịch sử thanh toán NCC
            $sql2 = "SELECT ngay_thanh_toan as ngay, so_tien_thanh_toan as so_tien FROM lich_su_thanh_toan_ncc WHERE cong_no_ncc_id = ? ORDER BY ngay_thanh_toan ASC";
            $stmt2 = $this->giaoDichModel->conn->prepare($sql2);
            $stmt2->execute([$row['id']]);
            $lich_su = $stmt2->fetchAll();
            $congNoNhaCungCap[] = [
                'ten_nha_cung_cap' => $row['ten_don_vi'],
                'ten_dich_vu' => $row['ghi_chu'] ?? '',
                'cong_no' => $row['so_tien'],
                'lich_su_thanh_toan' => $lich_su
            ];
        }
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no.php';
    }
}

