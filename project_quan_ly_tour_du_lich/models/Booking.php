<?php
// Model cho Booking - tương tác với cơ sở dữ liệu
class Booking 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Tìm booking theo tour_id và khach_hang_id (mã tour và mã khách hàng)
    public function findByTourAndCustomer($tourId, $khachHangId) {
        $sql = "SELECT * FROM booking WHERE tour_id = ? AND khach_hang_id = ? ORDER BY ngay_dat DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId, (int)$khachHangId]);
        return $stmt->fetch();
    }

    // Lấy tất cả booking
    public function getAll() {
        $sql = "SELECT * FROM booking ORDER BY ngay_dat DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy booking theo ID
    public function findById($id) {
        $sql = "SELECT * FROM booking WHERE booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Tìm booking theo điều kiện
    public function find($conditions = []) {
        $sql = "SELECT * FROM booking";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY ngay_dat DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Thêm booking mới
    public function insert($data) {
        // Kiểm tra cột so_tien_con_lai
        $checkSoTienConLai = $this->conn->query("SHOW COLUMNS FROM booking LIKE 'so_tien_con_lai'");
        $hasSoTienConLai = $checkSoTienConLai->rowCount() > 0;
            $soTienConLai = 0; // Khởi tạo biến số tiền còn lại
        if ($hasSoTienConLai) {
            // Nếu có cột, tính số tiền còn lại = tổng tiền - tiền cọc (nếu có), mặc định là tổng tiền nếu chưa cọc
                $tienCoc = $data['tien_coc'] ?? 0;
                if (isset($data['tong_tien'])) {
                    $soTienConLai = $data['tong_tien'] - $tienCoc;
                } else {
                    $soTienConLai = 0;
                }
            if ($soTienConLai < 0) $soTienConLai = 0;
            $sql = "INSERT INTO booking (tour_id, khach_hang_id, ngay_dat, ngay_khoi_hanh, ngay_ket_thuc, so_nguoi, tong_tien, so_tien_con_lai, trang_thai, ghi_chu) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $data['tour_id'] ?? 0,
                $data['khach_hang_id'] ?? 0,
                $data['ngay_dat'] ?? date('Y-m-d'),
                $data['ngay_khoi_hanh'] ?? null,
                $data['ngay_ket_thuc'] ?? null,
                $data['so_nguoi'] ?? 1,
                $data['tong_tien'] ?? 0,
                $soTienConLai,
                $data['trang_thai'] ?? 'ChoXacNhan',
                $data['ghi_chu'] ?? null
            ]);
        } else {
            $sql = "INSERT INTO booking (tour_id, khach_hang_id, ngay_dat, ngay_khoi_hanh, ngay_ket_thuc, so_nguoi, tong_tien, trang_thai, ghi_chu) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $data['tour_id'] ?? 0,
                $data['khach_hang_id'] ?? 0,
                $data['ngay_dat'] ?? date('Y-m-d'),
                $data['ngay_khoi_hanh'] ?? null,
                $data['ngay_ket_thuc'] ?? null,
                $data['so_nguoi'] ?? 1,
                $data['tong_tien'] ?? 0,
                $data['trang_thai'] ?? 'ChoXacNhan',
                $data['ghi_chu'] ?? null
            ]);
        }
        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật booking
    public function update($id, $data) {
        // Kiểm tra xem cột tien_coc và trang_thai_coc có tồn tại không
        $checkTienCoc = $this->conn->query("SHOW COLUMNS FROM booking LIKE 'tien_coc'");
        $hasTienCoc = $checkTienCoc->rowCount() > 0;
        
        $checkTrangThaiCoc = $this->conn->query("SHOW COLUMNS FROM booking LIKE 'trang_thai_coc'");
        $hasTrangThaiCoc = $checkTrangThaiCoc->rowCount() > 0;
        
            // Kiểm tra cột so_tien_con_lai
            $checkSoTienConLai = $this->conn->query("SHOW COLUMNS FROM booking LIKE 'so_tien_con_lai'");
            $hasSoTienConLai = $checkSoTienConLai->rowCount() > 0;
            if ($hasTienCoc && $hasSoTienConLai && isset($data['tien_coc'])) {
                // Tính số tiền còn lại nếu chưa truyền vào
                    if (isset($data['tong_tien']) && isset($data['so_tien_coc'])) {
                        $soTienConLai = $data['tong_tien'] - $data['so_tien_coc'];
                    } else {
                        $soTienConLai = $data['so_tien_con_lai'] ?? (($data['tong_tien'] ?? 0) - ($data['tien_coc'] ?? 0));
                    }
                if ($soTienConLai < 0) $soTienConLai = 0;
                $sql = "UPDATE booking SET so_nguoi = ?, ngay_khoi_hanh = ?, ngay_ket_thuc = ?, tong_tien = ?, tien_coc = ?, so_tien_con_lai = ?, trang_thai = ?, ghi_chu = ? WHERE booking_id = ?";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([
                    $data['so_nguoi'] ?? 1,
                    $data['ngay_khoi_hanh'] ?? null,
                    $data['ngay_ket_thuc'] ?? null,
                    $data['tong_tien'] ?? 0,
                    $data['tien_coc'] ?? 0,
                    $soTienConLai,
                    $data['trang_thai'] ?? 'ChoXacNhan',
                    $data['ghi_chu'] ?? null,
                    $id
                ]);
        } elseif ($hasTienCoc && isset($data['tien_coc'])) {
            $sql = "UPDATE booking SET so_nguoi = ?, ngay_khoi_hanh = ?, ngay_ket_thuc = ?, tong_tien = ?, tien_coc = ?, trang_thai = ?, ghi_chu = ? WHERE booking_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['so_nguoi'] ?? 1,
                $data['ngay_khoi_hanh'] ?? null,
                $data['ngay_ket_thuc'] ?? null,
                $data['tong_tien'] ?? 0,
                $data['tien_coc'] ?? 0,
                $data['trang_thai'] ?? 'ChoXacNhan',
                $data['ghi_chu'] ?? null,
                $id
            ]);
        } else {
            $sql = "UPDATE booking SET so_nguoi = ?, ngay_khoi_hanh = ?, ngay_ket_thuc = ?, tong_tien = ?, trang_thai = ?, ghi_chu = ? WHERE booking_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $data['so_nguoi'] ?? 1,
                $data['ngay_khoi_hanh'] ?? null,
                $data['ngay_ket_thuc'] ?? null,
                $data['tong_tien'] ?? 0,
                $data['trang_thai'] ?? 'ChoXacNhan',
                $data['ghi_chu'] ?? null,
                $id
            ]);
        }
    }

    // Cập nhật trạng thái booking và lưu lịch sử
    public function updateTrangThai($id, $trangThaiMoi, $nguoiThayDoiId, $ghiChu = null) {
        // Lấy trạng thái cũ
        $booking = $this->findById($id);
        if (!$booking) {
            return false;
        }
        
        $trangThaiCu = $booking['trang_thai'];
        
        // Nếu trạng thái không thay đổi, không cần cập nhật
        if ($trangThaiCu === $trangThaiMoi) {
            return true;
        }
        
        // Cập nhật trạng thái
        $sql = "UPDATE booking SET trang_thai = ? WHERE booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$trangThaiMoi, $id]);
        
        if ($result) {
            // Lưu lịch sử thay đổi
            require_once 'BookingHistory.php';
            $historyModel = new BookingHistory();
            $historyModel->insert([
                'booking_id' => $id,
                'trang_thai_cu' => $trangThaiCu,
                'trang_thai_moi' => $trangThaiMoi,
                'nguoi_thay_doi_id' => $nguoiThayDoiId,
                'ghi_chu' => $ghiChu
            ]);
        }
        
        return $result;
    }

    // Lấy booking với đầy đủ thông tin để hiển thị
    public function getAllWithDetails() {
        $sql = "SELECT b.*, 
                t.ten_tour, t.gia_co_ban, t.loai_tour,
                kh.khach_hang_id, kh.dia_chi,
                nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM booking b
                LEFT JOIN tour t ON b.tour_id = t.tour_id
                LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                ORDER BY b.ngay_dat DESC, b.booking_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Xóa booking
    public function delete($id) {
        $sql = "DELETE FROM booking WHERE booking_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Lấy tổng số người đã đặt cho một tour và ngày khởi hành cụ thể
    public function getSoNguoiDaDat($tourId, $ngayKhoiHanh) {
        $sql = "SELECT COALESCE(SUM(so_nguoi), 0) as tong_nguoi 
                FROM booking 
                WHERE tour_id = ? 
                AND ngay_khoi_hanh = ? 
                AND trang_thai IN ('ChoXacNhan', 'DaCoc', 'HoanTat')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId, $ngayKhoiHanh]);
        $result = $stmt->fetch();
        return (int)($result['tong_nguoi'] ?? 0);
    }

    // Kiểm tra chỗ trống cho tour và ngày khởi hành
    public function kiemTraChoTrong($tourId, $ngayKhoiHanh, $soNguoiCanDat, $soChoToiDa = 50) {
        $soNguoiDaDat = $this->getSoNguoiDaDat($tourId, $ngayKhoiHanh);
        $choTrong = $soChoToiDa - $soNguoiDaDat;
        return [
            'co_cho' => $choTrong >= $soNguoiCanDat,
            'cho_trong' => $choTrong,
            'da_dat' => $soNguoiDaDat,
            'toi_da' => $soChoToiDa
        ];
    }

    // Lấy booking với thông tin tour và khách hàng
    public function getBookingWithDetails($bookingId) {
        $sql = "SELECT b.*, 
                t.ten_tour, t.gia_co_ban, t.mo_ta, t.loai_tour, t.chinh_sach,
                kh.khach_hang_id, kh.dia_chi,
                nd.ho_ten, nd.email, nd.so_dien_thoai
                FROM booking b
                LEFT JOIN tour t ON b.tour_id = t.tour_id
                LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE b.booking_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$bookingId]);
        $result = $stmt->fetchAll();
        // Đảm bảo luôn trả về mảng đơn, không phải danh sách
        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    // Lấy danh sách yêu cầu đặc biệt dành cho một lịch khởi hành cụ thể
    public function getSpecialRequestsByLichKhoiHanh($tourId, $ngayKhoiHanh) {
        $sql = "SELECT 
                    y.id as yeu_cau_id,
                    y.tieu_de,
                    y.mo_ta,
                    y.loai_yeu_cau,
                    y.muc_do_uu_tien,
                    y.trang_thai,
                    y.ngay_tao,
                    b.booking_id,
                    b.so_nguoi,
                    b.ngay_dat,
                    nd.ho_ten as khach_ten,
                    nd.email,
                    nd.so_dien_thoai
                FROM yeu_cau_dac_biet y
                INNER JOIN booking b ON y.booking_id = b.booking_id
                INNER JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE b.tour_id = ?
                    AND b.ngay_khoi_hanh = ?
                    AND b.trang_thai IN ('ChoXacNhan','DaCoc','HoanTat')
                ORDER BY b.ngay_dat DESC, y.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId, $ngayKhoiHanh]);
        return $stmt->fetchAll();
    }

    // Lấy danh sách khách/nhóm tham gia tour cho một lịch cụ thể
    public function getKhachByTourAndNgayKhoiHanh($tourId, $ngayKhoiHanh) {
        // Lưu ý: KHÔNG giới hạn quá chặt theo trạng_thai để tránh mất khách ở màn HDV
        // Chỉ loại các booking đã hủy (DaHuy) nếu có, còn lại hiển thị cho HDV/Admin xử lý.
        $sql = "SELECT 
                    b.booking_id,
                    b.khach_hang_id,
                    b.so_nguoi,
                    b.ngay_dat,
                    b.ghi_chu as ghi_chu_booking,
                    nd.ho_ten,
                    nd.email,
                    nd.so_dien_thoai,
                    kh.dia_chi,
                    (
                        SELECT id 
                        FROM yeu_cau_dac_biet y 
                        WHERE y.booking_id = b.booking_id
                        ORDER BY y.id DESC 
                        LIMIT 1
                    ) as yeu_cau_id,
                    (
                        SELECT mo_ta 
                        FROM yeu_cau_dac_biet y 
                        WHERE y.booking_id = b.booking_id
                        ORDER BY y.id DESC 
                        LIMIT 1
                    ) as yeu_cau_dac_biet
                FROM booking b
                LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE b.tour_id = ?
                    AND DATE(b.ngay_khoi_hanh) = DATE(?)
                    AND (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')
                ORDER BY b.ngay_dat ASC, b.booking_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId, $ngayKhoiHanh]);
        return $stmt->fetchAll();
    }
    
    // Lấy danh sách khách/nhóm tham gia tour theo lich_khoi_hanh.id
    // Phương thức này đảm bảo lấy đúng booking theo lịch khởi hành cụ thể
    public function getKhachByLichKhoiHanhId($lichKhoiHanhId) {
        $sql = "SELECT 
                    b.booking_id,
                    b.khach_hang_id,
                    b.so_nguoi,
                    b.ngay_dat,
                    b.ghi_chu as ghi_chu_booking,
                    nd.ho_ten,
                    nd.email,
                    nd.so_dien_thoai,
                    kh.dia_chi,
                    (
                        SELECT id 
                        FROM yeu_cau_dac_biet y 
                        WHERE y.booking_id = b.booking_id
                        ORDER BY y.id DESC 
                        LIMIT 1
                    ) as yeu_cau_id,
                    (
                        SELECT mo_ta 
                        FROM yeu_cau_dac_biet y 
                        WHERE y.booking_id = b.booking_id
                        ORDER BY y.id DESC 
                        LIMIT 1
                    ) as yeu_cau_dac_biet
                FROM booking b
                LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                INNER JOIN lich_khoi_hanh lkh ON b.tour_id = lkh.tour_id 
                    AND DATE(b.ngay_khoi_hanh) = DATE(lkh.ngay_khoi_hanh)
                WHERE lkh.id = ?
                    AND (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')
                ORDER BY b.ngay_dat ASC, b.booking_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Lấy booking theo khách hàng ID
    public function getByKhachHangId($khachHangId) {
        $sql = "SELECT b.*, 
                t.ten_tour, t.gia_co_ban, t.mo_ta, t.loai_tour, t.chinh_sach,
                t.trang_thai as tour_trang_thai,
                lkh.ngay_khoi_hanh as lich_ngay_khoi_hanh, lkh.gio_xuat_phat as gio_khoi_hanh, lkh.diem_tap_trung
                FROM booking b
                LEFT JOIN tour t ON b.tour_id = t.tour_id
                LEFT JOIN lich_khoi_hanh lkh ON b.tour_id = lkh.tour_id AND b.ngay_khoi_hanh = lkh.ngay_khoi_hanh
                WHERE b.khach_hang_id = ?
                ORDER BY b.ngay_dat DESC, b.booking_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$khachHangId]);
        return $stmt->fetchAll();
    }

    // Lấy booking theo user ID (nguoi_dung_id)
    public function getByUserId($userId) {
        $sql = "SELECT b.*, 
                t.ten_tour, t.gia_co_ban, t.mo_ta, t.loai_tour, t.chinh_sach,
                t.trang_thai as tour_trang_thai,
                lkh.ngay_khoi_hanh as lich_ngay_khoi_hanh, lkh.gio_xuat_phat as gio_khoi_hanh, lkh.diem_tap_trung
                FROM booking b
                LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                LEFT JOIN tour t ON b.tour_id = t.tour_id
                LEFT JOIN lich_khoi_hanh lkh ON b.tour_id = lkh.tour_id AND b.ngay_khoi_hanh = lkh.ngay_khoi_hanh
                WHERE kh.nguoi_dung_id = ?
                ORDER BY b.ngay_dat DESC, b.booking_id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$userId]);
        return $stmt->fetchAll();
    }
}
