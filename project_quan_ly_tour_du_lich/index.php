<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



session_start();

// Require toàn bộ các file khai báo môi trường, thực thi,...(không require view)

// Require file Common
require_once __DIR__ . '/commons/env.php';
require_once __DIR__ . '/commons/function.php';

// Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/TourController.php';
require_once __DIR__ . '/controllers/BookingController.php';
require_once __DIR__ . '/controllers/HDVController.php';
require_once __DIR__ . '/controllers/KhachHangController.php';
require_once __DIR__ . '/controllers/NhaCungCapController.php';
require_once __DIR__ . '/controllers/LichKhoiHanhController.php';
require_once __DIR__ . '/controllers/DanhGiaController.php';
require_once __DIR__ . '/controllers/BaoCaoTaiChinhController.php';

// Models
require_once __DIR__ . '/models/NguoiDung.php';
require_once __DIR__ . '/models/Tour.php';
require_once __DIR__ . '/models/Booking.php';
require_once __DIR__ . '/models/BookingHistory.php';
require_once __DIR__ . '/models/BookingDeletionHistory.php';
require_once __DIR__ . '/models/SupplierDeletionHistory.php';
require_once __DIR__ . '/models/KhachHang.php';
require_once __DIR__ . '/models/HDV.php';
require_once __DIR__ . '/models/NhaCungCap.php';
require_once __DIR__ . '/models/GiaoDich.php';
require_once __DIR__ . '/models/DanhGia.php';
require_once __DIR__ . '/models/NhanSu.php';
require_once __DIR__ . '/models/LichKhoiHanh.php';
require_once __DIR__ . '/models/PhanBoNhanSu.php';
require_once __DIR__ . '/models/PhanBoDichVu.php';
require_once __DIR__ . '/models/HDVManagement.php';
require_once __DIR__ . '/models/TourCheckin.php';
require_once __DIR__ . '/models/HotelRoomAssignment.php';
require_once __DIR__ . '/models/DichVuNhaCungCap.php';
require_once __DIR__ . '/models/YeuCauDacBiet.php';


// Route
$act = $_GET['act'] ?? 'auth/login';

// Để đảm bảo tính chất chỉ gọi 1 hàm Controller để xử lý request thì mình sử dụng match
match ($act) {
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
    'admin/lichSuXoaBooking' => (new AdminController())->lichSuXoaBooking(),
    'admin/lichSuXoaNhaCungCap' => (new AdminController())->lichSuXoaNhaCungCap(),
    'admin/chiTietLichSuXoaNhaCungCap' => (new AdminController())->chiTietLichSuXoaNhaCungCap(),
    'admin/chiTietTour' => (new AdminController())->chiTietTour(),
    'admin/yeuCauDacBiet' => (new AdminController())->yeuCauDacBiet(),
    'admin/capNhatYeuCauDacBiet' => (new AdminController())->capNhatYeuCauDacBiet(),
    'admin/themYeuCauDacBiet' => (new AdminController())->taoYeuCauDacBiet(),
    'admin/quanLyYeuCauTour' => (new AdminController())->quanLyYeuCauTour(),
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
    'hdv/lich_trinh_chi_tiet' => (new HDVController())->lichTrinhChiTiet(),
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
    'khachHang/lichTrinhTour' => (new KhachHangController())->lichTrinhTour(),
    'khachHang/thongBao' => (new KhachHangController())->thongBao(),
    'khachHang/capNhatThongTin' => (new KhachHangController())->capNhatThongTin(),
    'khachHang/guiYeuCauHoTro' => (new KhachHangController())->guiYeuCauHoTro(),
    'khachHang/guiYeuCauTour' => (new KhachHangController())->guiYeuCauTour(),
    'khachHang/yeuCauTour' => (new KhachHangController())->guiYeuCauTour(),
    'khachHang/thanhToan' => (new KhachHangController())->thanhToan(),
    'khachHang/thanhToanTour' => (new KhachHangController())->thanhToanTour(),

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
    
    // Default
    default => die("Route không tồn tại: $act")
};

