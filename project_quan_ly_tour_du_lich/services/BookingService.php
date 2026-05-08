<?php
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/KhachHang.php';
require_once __DIR__ . '/../models/NguoiDung.php';
require_once __DIR__ . '/../models/BookingHistory.php';
require_once __DIR__ . '/../models/BookingDeletionHistory.php';
require_once __DIR__ . '/../models/LichKhoiHanh.php';
require_once __DIR__ . '/../services/EmailQueueService.php';

class BookingService
{
    private Booking $bookingModel;
    private Tour $tourModel;
    private KhachHang $khachHangModel;
    private NguoiDung $nguoiDungModel;
    private BookingHistory $historyModel;
    private BookingDeletionHistory $deletionHistoryModel;
    private LichKhoiHanh $lichKhoiHanhModel;

    public function __construct(
        Booking $bookingModel,
        Tour $tourModel,
        KhachHang $khachHangModel,
        NguoiDung $nguoiDungModel,
        BookingHistory $historyModel,
        BookingDeletionHistory $deletionHistoryModel,
        LichKhoiHanh $lichKhoiHanhModel
    ) {
        $this->bookingModel         = $bookingModel;
        $this->tourModel            = $tourModel;
        $this->khachHangModel       = $khachHangModel;
        $this->nguoiDungModel       = $nguoiDungModel;
        $this->historyModel         = $historyModel;
        $this->deletionHistoryModel = $deletionHistoryModel;
        $this->lichKhoiHanhModel    = $lichKhoiHanhModel;
    }

    // =====================================================================
    // BOOKING CREATION
    // =====================================================================

    /**
     * Tạo booking có race-condition protection (FOR UPDATE lock).
     * Trả về bookingId. Ném RuntimeException nếu thất bại.
     */
    public function createBooking(
        int $khachHangId,
        int $tourId,
        array $tour,
        string $ngayKhoiHanh,
        string $ngayKetThuc,
        int $soNguoi,
        float $tienCoc,
        string $ghiChu
    ): int {
        $tongTien = (float)($tour['gia_co_ban'] ?? 0) * $soNguoi;

        $data = [
            'tour_id'        => $tourId,
            'khach_hang_id'  => $khachHangId,
            'ngay_dat'       => date('Y-m-d'),
            'so_nguoi'       => $soNguoi,
            'ngay_khoi_hanh' => $ngayKhoiHanh,
            'ngay_ket_thuc'  => $ngayKetThuc,
            'tong_tien'      => $tongTien,
            'tien_coc'       => $tienCoc,
            'trang_thai'     => 'ChoXacNhan',
            'ghi_chu'        => $ghiChu,
        ];

        $conn = connectDB();
        $conn->beginTransaction();
        try {
            $stmtLock = $conn->prepare(
                "SELECT so_cho FROM lich_khoi_hanh WHERE tour_id = ? AND ngay_khoi_hanh = ? FOR UPDATE"
            );
            $stmtLock->execute([$tourId, $ngayKhoiHanh]);
            $lichRow  = $stmtLock->fetch();
            $soChoMax = $lichRow ? (int)$lichRow['so_cho'] : (int)($tour['so_cho_toi_da'] ?? 50);

            $stmtDaDat = $conn->prepare(
                "SELECT COALESCE(SUM(so_nguoi), 0) FROM booking
                 WHERE tour_id = ? AND ngay_khoi_hanh = ? AND trang_thai NOT IN ('Huy')"
            );
            $stmtDaDat->execute([$tourId, $ngayKhoiHanh]);
            $daDat = (int)$stmtDaDat->fetchColumn();

            if ($daDat + $soNguoi > $soChoMax) {
                $conn->rollBack();
                throw new RuntimeException(
                    'Không đủ chỗ. Chỉ còn ' . max(0, $soChoMax - $daDat) . ' chỗ trống cho ngày này.'
                );
            }

            $bookingId = $this->bookingModel->insert($data);
            if (!$bookingId) {
                $conn->rollBack();
                throw new RuntimeException('Không thể tạo booking.');
            }
            $conn->commit();
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            $conn->rollBack();
            error_log('Booking create transaction error: ' . $e->getMessage());
            throw new RuntimeException('Không thể tạo booking. Vui lòng thử lại.');
        }

        // Tự động tạo lịch khởi hành nếu chưa có
        if (!empty($ngayKhoiHanh) && $tourId > 0) {
            $lichKhoiHanh = $this->lichKhoiHanhModel->findByTourAndNgayKhoiHanh($tourId, $ngayKhoiHanh);
            if (!$lichKhoiHanh) {
                $lichData = [
                    'tour_id'        => $tourId,
                    'ngay_khoi_hanh' => $ngayKhoiHanh,
                    'ngay_ket_thuc'  => $ngayKetThuc,
                    'gio_xuat_phat'  => null,
                    'gio_ket_thuc'   => null,
                    'diem_tap_trung' => '',
                    'so_cho'         => (int)($tour['so_cho_toi_da'] ?? $tour['so_cho'] ?? 50),
                    'hdv_id'         => null,
                    'trang_thai'     => 'SapKhoiHanh',
                    'ghi_chu'        => 'Tạo tự động từ booking #' . $bookingId,
                ];
                $lichId = $this->lichKhoiHanhModel->insert($lichData);
                if ($lichId) {
                    $this->tuDongPhanBoNhanSu($lichId, $ngayKhoiHanh, $ngayKetThuc);
                }
            } else {
                $this->tuDongPhanBoNhanSu($lichKhoiHanh['id'], $ngayKhoiHanh, $ngayKetThuc);
            }
        }

        return (int)$bookingId;
    }

    /**
     * Validate + tạo booking (admin đặt hộ khách). Ném Exception nếu lỗi.
     * Trả về bookingId.
     */
    public function createBookingForKhach(array $post): int
    {
        $tourId      = validateId($post['tour_id'] ?? null) ?? 0;
        $hoTen       = requestString('ho_ten', '', 'POST');
        $email       = validateEmail($post['email'] ?? '');
        $soDienThoai = validatePhone($post['so_dien_thoai'] ?? '');
        $diaChi      = requestString('dia_chi', '', 'POST');
        $gioiTinh    = requestString('gioi_tinh', '', 'POST');
        $ngaySinh    = validateDateYmd($post['ngay_sinh'] ?? '') ?? null;
        $soNguoi     = validateId($post['so_nguoi'] ?? 1) ?? 1;
        $ngayKhoiHanh     = validateDateYmd($post['ngay_khoi_hanh'] ?? '') ?? '';
        $ngayKetThucForm  = validateDateYmd($post['ngay_ket_thuc'] ?? '') ?? $ngayKhoiHanh;
        $loaiKhach   = requestString('loai_khach', 'le', 'POST');
        $tenCongTy   = requestString('ten_cong_ty', '', 'POST');
        $ghiChu      = requestString('ghi_chu', '', 'POST');
        $yeuCauDacBiet = requestString('yeu_cau_dac_biet', '', 'POST');

        if (!in_array($gioiTinh, ['', 'Nam', 'Nu', 'Khac'], true)) {
            $gioiTinh = null;
        }
        if (!in_array($loaiKhach, ['le', 'doan'], true)) {
            $loaiKhach = 'le';
        }

        if (empty($hoTen)) {
            throw new \Exception('Vui lòng nhập tên khách hàng.');
        }
        if (empty($email) && empty($soDienThoai)) {
            throw new \Exception('Vui lòng nhập email hoặc số điện thoại.');
        }
        if (!empty($post['email']) && $email === null) {
            throw new \Exception('Email không hợp lệ.');
        }
        if (!empty($post['so_dien_thoai']) && $soDienThoai === null) {
            throw new \Exception('Số điện thoại không hợp lệ.');
        }
        if (!empty($post['ngay_sinh'])) {
            if ($ngaySinh === null) {
                throw new \Exception('Ngày sinh không hợp lệ.');
            }
            $dob = \DateTime::createFromFormat('Y-m-d', $ngaySinh);
            if (!$dob) {
                throw new \Exception('Ngày sinh không hợp lệ.');
            }
            $age = $dob->diff(new \DateTime('today'))->y;
            if ($age < 18) {
                throw new \Exception('Người đặt tour phải từ 18 tuổi trở lên và có thể chịu trách nhiệm pháp lý.');
            }
        }
        if ($tourId <= 0) {
            throw new \Exception('Vui lòng chọn tour.');
        }
        if (empty($ngayKhoiHanh)) {
            throw new \Exception('Vui lòng chọn ngày khởi hành.');
        }
        if ($soNguoi <= 0) {
            throw new \Exception('Số lượng người phải lớn hơn 0.');
        }

        $tour = $this->tourModel->findById($tourId);
        if (!$tour) {
            throw new \Exception('Tour không tồn tại.');
        }

        $nguoiDung = $this->nguoiDungModel->findOrCreate($hoTen, $email, $soDienThoai, 'KhachHang');
        if (!$nguoiDung) {
            throw new \Exception('Không thể tạo tài khoản khách hàng.');
        }

        $khachHang = $this->khachHangModel->findOrCreateByNguoiDungInfo(
            $nguoiDung['id'], $diaChi, $gioiTinh, $ngaySinh
        );
        if (!$khachHang) {
            throw new \Exception('Không thể tạo thông tin khách hàng.');
        }

        $tongTien = (float)($tour['gia_co_ban'] ?? 0) * $soNguoi;

        $conn = connectDB();
        $conn->beginTransaction();
        try {
            $stmtLock = $conn->prepare(
                "SELECT so_cho FROM lich_khoi_hanh WHERE tour_id = ? AND ngay_khoi_hanh = ? FOR UPDATE"
            );
            $stmtLock->execute([$tourId, $ngayKhoiHanh]);
            $lichRow  = $stmtLock->fetch();
            $soChoMax = $lichRow ? (int)$lichRow['so_cho'] : (int)($tour['so_cho_toi_da'] ?? 50);

            $stmtDaDat = $conn->prepare(
                "SELECT COALESCE(SUM(so_nguoi), 0) FROM booking
                 WHERE tour_id = ? AND ngay_khoi_hanh = ? AND trang_thai NOT IN ('Huy')"
            );
            $stmtDaDat->execute([$tourId, $ngayKhoiHanh]);
            $daDat = (int)$stmtDaDat->fetchColumn();

            if ($daDat + $soNguoi > $soChoMax) {
                $conn->rollBack();
                throw new \Exception(
                    'Không đủ chỗ trống. Chỉ còn ' . max(0, $soChoMax - $daDat) . ' chỗ cho ngày này.'
                );
            }

            $ghiChuFull = $ghiChu . ($loaiKhach === 'doan' && !empty($tenCongTy) ? " | Công ty/Tổ chức: {$tenCongTy}" : '');
            $bookingData = [
                'tour_id'        => $tourId,
                'khach_hang_id'  => $khachHang['khach_hang_id'],
                'ngay_dat'       => date('Y-m-d'),
                'ngay_khoi_hanh' => $ngayKhoiHanh,
                'ngay_ket_thuc'  => $ngayKetThucForm,
                'so_nguoi'       => $soNguoi,
                'tong_tien'      => $tongTien,
                'trang_thai'     => 'ChoXacNhan',
                'ghi_chu'        => $ghiChuFull,
            ];

            $bookingId = $this->bookingModel->insert($bookingData);
            if (!$bookingId) {
                $conn->rollBack();
                throw new \Exception('Không thể tạo booking.');
            }
            $conn->commit();
        } catch (\Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }

        // Tự động tạo lịch khởi hành
        if (!empty($ngayKhoiHanh) && $tourId > 0) {
            $lkh = $this->lichKhoiHanhModel->findByTourAndNgayKhoiHanh($tourId, $ngayKhoiHanh);
            if (!$lkh) {
                $this->lichKhoiHanhModel->insert([
                    'tour_id'        => $tourId,
                    'ngay_khoi_hanh' => $ngayKhoiHanh,
                    'ngay_ket_thuc'  => $ngayKetThucForm,
                    'gio_xuat_phat'  => null,
                    'gio_ket_thuc'   => null,
                    'diem_tap_trung' => '',
                    'so_cho'         => (int)($tour['so_cho_toi_da'] ?? $tour['so_cho'] ?? 50),
                    'hdv_id'         => null,
                    'trang_thai'     => 'SapKhoiHanh',
                    'ghi_chu'        => 'Tạo tự động từ booking #' . $bookingId,
                ]);
            }
        }

        if (!empty($yeuCauDacBiet)) {
            $this->tourModel->insertYeuCauDacBiet($bookingId, $yeuCauDacBiet);
        }

        // Gửi email xác nhận
        if (!empty($email) && $tour) {
            $adminBookingData = [
                'ngay_khoi_hanh' => $ngayKhoiHanh,
                'ngay_ket_thuc'  => $ngayKetThucForm,
                'so_nguoi_lon'   => $soNguoi,
                'so_tre_em'      => 0,
                'tong_tien'      => $tongTien,
                'tien_coc'       => $tongTien * 0.3,
                'trang_thai'     => 'ChoXacNhan',
            ];
            $this->sendBookingConfirmationEmailDirect($email, $hoTen, $bookingId, $adminBookingData, $tour);
        }

        return (int)$bookingId;
    }

    // =====================================================================
    // STATUS / PAYMENT UPDATES
    // =====================================================================

    /**
     * Cập nhật trạng thái booking. Nếu sang HoanTat tự động fill tienCoc = tongTien.
     * Trả về true/false.
     */
    public function updateTrangThaiForBooking(
        int $bookingId,
        string $trangThaiMoi,
        string $ghiChu,
        ?int $nguoiThayDoiId
    ): bool {
        if ($trangThaiMoi === 'HoanTat') {
            $booking = $this->bookingModel->findById($bookingId);
            if ($booking) {
                $tongTien = (float)($booking['tong_tien'] ?? 0);
                if ($tongTien > 0) {
                    $conn = connectDB();
                    $conn->prepare(
                        "UPDATE booking SET tien_coc = ?, trang_thai_coc = 'HoanTat' WHERE booking_id = ?"
                    )->execute([$tongTien, $bookingId]);
                    if (empty($ghiChu)) {
                        $ghiChu = 'Đã thanh toán đủ (tự động cập nhật tiền cọc = tổng tiền)';
                    }
                }
            }
        }
        return (bool)$this->bookingModel->updateTrangThai($bookingId, $trangThaiMoi, $nguoiThayDoiId, $ghiChu);
    }

    /**
     * Xử lý cập nhật tiền cọc. Ném RuntimeException nếu lỗi.
     * Trả về thông báo thành công.
     */
    public function processTienCocUpdate(
        int $bookingId,
        float $tienCoc,
        string $trangThaiCoc,
        string $ghiChuCoc
    ): string {
        $booking = $this->bookingModel->findById($bookingId);
        if (!$booking) {
            throw new \RuntimeException('Booking không tồn tại.');
        }

        $tongTien = (float)($booking['tong_tien'] ?? 0);

        if ($tienCoc > $tongTien) {
            throw new \RuntimeException(
                'Số tiền cọc không được vượt quá tổng tiền (' . number_format($tongTien) . ' ₫).'
            );
        }

        if ($tienCoc == 0 && $tongTien > 0) {
            $tienCoc = round($tongTien * 0.3);
        }

        $trangThaiBookingMoi = $booking['trang_thai'];
        $trangThaiCocMoi     = $trangThaiCoc;

        if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
            $trangThaiBookingMoi = 'HoanTat';
            $trangThaiCocMoi     = 'HoanTat';
        } elseif ($tienCoc > 0 && $trangThaiCoc === 'DaCoc'
            && $booking['trang_thai'] !== 'DaCoc' && $booking['trang_thai'] !== 'HoanTat'
        ) {
            $trangThaiBookingMoi = 'DaCoc';
        }

        $conn = connectDB();
        try {
            foreach (['tien_coc', 'trang_thai_coc'] as $col) {
                if (!dbColumnExists('booking', $col, $conn)) {
                    throw new \RuntimeException('Bang booking chua du schema. Thieu cot: ' . $col);
                }
            }

            $soTienConLai = max(0, $tongTien - $tienCoc);

            if (dbColumnExists('booking', 'so_tien_con_lai', $conn)) {
                $sql = "UPDATE booking SET tien_coc = ?, trang_thai_coc = ?, so_tien_con_lai = ? WHERE booking_id = ?";
                $result = $conn->prepare($sql)->execute([$tienCoc, $trangThaiCocMoi, $soTienConLai, $bookingId]);
            } else {
                $sql = "UPDATE booking SET tien_coc = ?, trang_thai_coc = ? WHERE booking_id = ?";
                $result = $conn->prepare($sql)->execute([$tienCoc, $trangThaiCocMoi, $bookingId]);
            }

            if ($result && $trangThaiBookingMoi !== $booking['trang_thai']) {
                $ghiChuThayDoi = 'Cập nhật tiền cọc: ' . number_format($tienCoc) . ' ₫';
                if ($tienCoc >= $tongTien) {
                    $ghiChuThayDoi .= ' (Đã thanh toán đủ)';
                }
                if ($ghiChuCoc) {
                    $ghiChuThayDoi .= ' - ' . $ghiChuCoc;
                }
                $this->bookingModel->updateTrangThai(
                    $bookingId, $trangThaiBookingMoi, $_SESSION['user_id'] ?? null, $ghiChuThayDoi
                );
            }

            if (!$result) {
                throw new \RuntimeException('Không thể cập nhật tiền cọc.');
            }
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Lỗi khi cập nhật tiền cọc: ' . $e->getMessage());
        }

        $msg = 'Cập nhật tiền cọc thành công. Số tiền cọc: ' . number_format($tienCoc) . ' ₫';
        if ($tienCoc >= $tongTien && $tongTien > 0) {
            $msg .= ' - Trạng thái đã tự động chuyển thành "Hoàn tất" (Đã thanh toán đủ)';
        }
        return $msg;
    }

    /**
     * Cập nhật thông tin booking (update + history). Ném Exception nếu lỗi.
     * Trả về thông báo thành công.
     */
    public function updateBookingData(int $id, array $post): string
    {
        $booking      = $this->bookingModel->findById($id);
        $trangThaiCu  = $booking['trang_thai'] ?? '';

        $ngayKhoiHanh = validateDateYmd($post['ngay_khoi_hanh'] ?? '') ?? null;
        $ngayKetThuc  = validateDateYmd($post['ngay_ket_thuc'] ?? '') ?? $ngayKhoiHanh;
        $tongTien     = validateMoney($post['tong_tien'] ?? null, 0);
        $tienCoc      = validateMoney($post['tien_coc'] ?? 0, 0) ?? 0;

        if ($tongTien === null) {
            throw new \RuntimeException('Số tiền không hợp lệ.');
        }
        if ($tienCoc > $tongTien) {
            throw new \RuntimeException('Tiền cọc không được lớn hơn tổng tiền.');
        }

        $trangThaiMoi = $post['trang_thai'] ?? $trangThaiCu;

        if ($trangThaiMoi === 'HoanTat' && $tongTien > 0) {
            $tienCoc = $tongTien;
        } else {
            if ($tienCoc == 0) {
                $tienCoc = (float)($booking['tien_coc'] ?? ($booking['so_tien_coc'] ?? 0));
            }
            if ($tienCoc == 0 && $tongTien > 0) {
                $tienCoc = round($tongTien * 0.3);
            }
        }

        if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
            $trangThaiMoi = 'HoanTat';
        } elseif ($tienCoc > 0 && $tienCoc < $tongTien && $trangThaiCu !== 'HoanTat' && $trangThaiMoi !== 'HoanTat') {
            if ($trangThaiCu === 'ChoXacNhan') {
                $trangThaiMoi = 'DaCoc';
            }
        }

        $trangThaiCoc = $post['trang_thai_coc'] ?? $booking['trang_thai_coc'] ?? 'ChuaCoc';
        if ($trangThaiMoi === 'HoanTat' || ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01)) {
            $trangThaiCoc = 'HoanTat';
        } elseif ($tienCoc > 0 && $trangThaiCoc === 'ChuaCoc') {
            $trangThaiCoc = 'DaCoc';
        }

        $soNguoiMoi = (int)(validateId($post['so_nguoi'] ?? 1) ?? 1);
        $ghiChuMoi  = trim($post['ghi_chu'] ?? '');

        $data = [
            'so_nguoi'      => $soNguoiMoi,
            'ngay_khoi_hanh'=> $ngayKhoiHanh,
            'ngay_ket_thuc' => $ngayKetThuc,
            'tong_tien'     => $tongTien,
            'tien_coc'      => $tienCoc,
            'trang_thai_coc'=> $trangThaiCoc,
            'trang_thai'    => $trangThaiMoi,
            'ghi_chu'       => $ghiChuMoi,
        ];

        // Detect changes
        $thayDoiChiTiet = [];
        if ($trangThaiMoi !== $trangThaiCu) {
            $thayDoiChiTiet[] = "Trạng thái: {$trangThaiCu} → {$trangThaiMoi}";
        }
        if (abs($tienCoc - (float)($booking['tien_coc'] ?? 0)) > 0.01) {
            $thayDoiChiTiet[] = 'Tiền cọc: ' . number_format((float)($booking['tien_coc'] ?? 0)) . ' ₫ → ' . number_format($tienCoc) . ' ₫';
        }
        if (abs($tongTien - (float)($booking['tong_tien'] ?? 0)) > 0.01) {
            $thayDoiChiTiet[] = 'Tổng tiền: ' . number_format((float)($booking['tong_tien'] ?? 0)) . ' ₫ → ' . number_format($tongTien) . ' ₫';
        }
        if ($soNguoiMoi !== (int)($booking['so_nguoi'] ?? 1)) {
            $thayDoiChiTiet[] = 'Số người: ' . ($booking['so_nguoi'] ?? 1) . ' → ' . $soNguoiMoi;
        }
        $ghiChuCu = trim($booking['ghi_chu'] ?? '');
        if ($ghiChuMoi !== $ghiChuCu) {
            $thayDoiChiTiet[] = empty($ghiChuCu)
                ? 'Ghi chú: (trống) → ' . mb_substr($ghiChuMoi, 0, 50)
                : (empty($ghiChuMoi) ? 'Ghi chú đã xóa' : 'Ghi chú đã được cập nhật');
        }

        $result = $this->bookingModel->update($id, $data);
        if (!$result) {
            throw new \RuntimeException('Không thể cập nhật booking.');
        }

        $nguoiThayDoiId = $_SESSION['user_id'] ?? null;
        if (!empty($thayDoiChiTiet)) {
            if ($trangThaiMoi !== $trangThaiCu) {
                $ghiChu = 'Cập nhật thông tin booking: ' . implode(', ', $thayDoiChiTiet);
                if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
                    $ghiChu .= ' - Tiền cọc = tổng tiền, tự động chuyển thành "Hoàn tất"';
                }
                $this->bookingModel->updateTrangThai($id, $trangThaiMoi, $nguoiThayDoiId, $ghiChu);

                if ($trangThaiMoi === 'DaCoc' && !empty($ngayKhoiHanh)) {
                    $lkh = $this->lichKhoiHanhModel->findByTourAndNgayKhoiHanh($booking['tour_id'], $ngayKhoiHanh);
                    if ($lkh) {
                        $this->tuDongPhanBoNhanSu($lkh['id'], $ngayKhoiHanh, $ngayKetThuc ?? $ngayKhoiHanh);
                    }
                }
            } else {
                $this->historyModel->insert([
                    'booking_id'       => $id,
                    'trang_thai_cu'    => $trangThaiCu,
                    'trang_thai_moi'   => $trangThaiMoi,
                    'nguoi_thay_doi_id'=> $nguoiThayDoiId,
                    'ghi_chu'          => 'Cập nhật thông tin booking: ' . implode(', ', $thayDoiChiTiet),
                ]);
            }
        }

        $msg = 'Cập nhật booking thành công.';
        if ($tongTien > 0 && abs($tienCoc - $tongTien) < 0.01) {
            $msg .= ' Trạng thái đã tự động chuyển thành "Hoàn tất" (Đã thanh toán đủ).';
        }
        return $msg;
    }

    // =====================================================================
    // DELETE / HIDE
    // =====================================================================

    /**
     * Xóa booking sau khi xác minh mật khẩu admin. Ném RuntimeException nếu lỗi.
     */
    public function deleteBooking(int $id, string $matKhau, int $adminId, string $lyDoXoa): void
    {
        $admin = $this->nguoiDungModel->findById($adminId);
        if (!$admin || !password_verify($matKhau, $admin['mat_khau'])) {
            throw new \RuntimeException('Mật khẩu không đúng.');
        }

        $booking = $this->bookingModel->getBookingWithDetails($id);
        if (!$booking) {
            throw new \RuntimeException('Booking không tồn tại.');
        }

        $thongTinBooking = json_encode($this->buildBookingSnapshot($booking), JSON_UNESCAPED_UNICODE);

        $result = $this->bookingModel->delete($id);
        if (!$result) {
            throw new \RuntimeException('Không thể xóa booking.');
        }

        $this->deletionHistoryModel->insert([
            'booking_id'      => $id,
            'tour_id'         => $booking['tour_id'] ?? null,
            'khach_hang_id'   => $booking['khach_hang_id'] ?? null,
            'nguoi_xoa_id'    => $adminId,
            'ly_do_xoa'       => $lyDoXoa,
            'thong_tin_booking'=> $thongTinBooking,
        ]);
    }

    /**
     * Ẩn booking đã HoanTat. Ném RuntimeException nếu lỗi.
     */
    public function hideBooking(int $bookingId, string $lyDoAn, int $userId): void
    {
        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
        if (!$booking) {
            throw new \RuntimeException('Booking không tồn tại.');
        }
        if (($booking['trang_thai'] ?? '') !== 'HoanTat') {
            throw new \RuntimeException('Chỉ có thể ẩn booking đã hoàn tất.');
        }
        if ($this->bookingModel->isBookingHidden($bookingId)) {
            throw new \RuntimeException('__already_hidden__');
        }

        $prefix          = $this->bookingModel->getHideReasonPrefix();
        $thongTinBooking = json_encode($this->buildBookingSnapshot($booking), JSON_UNESCAPED_UNICODE);

        $saved = $this->deletionHistoryModel->insert([
            'booking_id'       => $bookingId,
            'tour_id'          => $booking['tour_id'] ?? null,
            'khach_hang_id'    => $booking['khach_hang_id'] ?? null,
            'nguoi_xoa_id'     => $userId,
            'ly_do_xoa'        => $prefix . ($lyDoAn !== '' ? (' ' . $lyDoAn) : ' Ẩn booking đã hoàn tất khỏi danh sách chính'),
            'thong_tin_booking'=> $thongTinBooking,
        ]);

        if (!$saved) {
            throw new \RuntimeException('Không thể ẩn booking. Vui lòng thử lại.');
        }
    }

    // =====================================================================
    // PERMISSION CHECKS
    // =====================================================================

    public function checkPermissionToUpdate(int $bookingId): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        if (hasRole('Admin')) {
            return true;
        }
        if (hasRole('HDV')) {
            return $this->canCurrentHdvManageBooking($bookingId);
        }
        return false;
    }

    public function checkPermissionToView(array $booking): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        if (hasRole(['Admin', 'HDV'])) {
            return true;
        }
        if (hasRole('KhachHang')) {
            $khachHangId = $_SESSION['khach_hang_id'] ?? null;
            if (!$khachHangId && isset($_SESSION['user_id'])) {
                $kh = $this->khachHangModel->findByNguoiDungId($_SESSION['user_id']);
                if ($kh) {
                    $khachHangId = $kh['khach_hang_id'];
                }
            }
            return $khachHangId && $booking['khach_hang_id'] == $khachHangId;
        }
        return false;
    }

    public function resolveKhachHangId(): ?int
    {
        $khachHangId = $_SESSION['khach_hang_id'] ?? null;
        if (!$khachHangId && isset($_SESSION['user_id'])) {
            $kh = $this->khachHangModel->findByNguoiDungId($_SESSION['user_id']);
            if ($kh) {
                $khachHangId = $kh['khach_hang_id'];
                $_SESSION['khach_hang_id'] = $khachHangId;
            }
        }
        return $khachHangId ? (int)$khachHangId : null;
    }

    private function canCurrentHdvManageBooking(int $bookingId): bool
    {
        $nhanSuId = $this->getCurrentHdvNhanSuId();
        if ($nhanSuId <= 0) {
            return false;
        }
        $lichKhoiHanhId = $this->resolveLichKhoiHanhIdForPermission($bookingId);
        if ($lichKhoiHanhId <= 0) {
            return false;
        }
        $conn = connectDB();
        $stmt = $conn->prepare(
            "SELECT 1
             FROM lich_khoi_hanh lkh
             LEFT JOIN phan_bo_nhan_su pbn
                    ON pbn.lich_khoi_hanh_id = lkh.id
                   AND pbn.nhan_su_id = ?
                   AND pbn.trang_thai = 'DaXacNhan'
             WHERE lkh.id = ?
               AND (lkh.hdv_id = ? OR pbn.nhan_su_id IS NOT NULL)
             LIMIT 1"
        );
        $stmt->execute([$nhanSuId, $lichKhoiHanhId, $nhanSuId]);
        return (bool)$stmt->fetchColumn();
    }

    private function getCurrentHdvNhanSuId(): int
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return 0;
        }
        $conn = connectDB();
        $stmt = $conn->prepare(
            "SELECT nhan_su_id FROM nhan_su WHERE nguoi_dung_id = ? AND vai_tro = 'HDV' LIMIT 1"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    private function resolveLichKhoiHanhIdForPermission(int $bookingId): int
    {
        $conn = connectDB();
        $stmt = $conn->prepare(
            'SELECT lich_khoi_hanh_id, tour_id, ngay_khoi_hanh FROM booking WHERE booking_id = ? LIMIT 1'
        );
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$booking) {
            return 0;
        }
        $directId = (int)($booking['lich_khoi_hanh_id'] ?? 0);
        if ($directId > 0) {
            return $directId;
        }
        $tourId       = (int)($booking['tour_id'] ?? 0);
        $ngayKhoiHanh = trim((string)($booking['ngay_khoi_hanh'] ?? ''));
        if ($tourId <= 0 || $ngayKhoiHanh === '') {
            return 0;
        }
        $stmt2 = $conn->prepare(
            'SELECT id FROM lich_khoi_hanh WHERE tour_id = ? AND ngay_khoi_hanh = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt2->execute([$tourId, $ngayKhoiHanh]);
        return (int)$stmt2->fetchColumn();
    }

    // =====================================================================
    // AUTO HDV ASSIGNMENT
    // =====================================================================

    public function tuDongPhanBoNhanSu(int $lichKhoiHanhId, string $ngayKhoiHanh, string $ngayKetThuc): bool
    {
        try {
            require_once __DIR__ . '/../models/PhanBoNhanSu.php';
            require_once __DIR__ . '/../models/NhanSu.php';
            require_once __DIR__ . '/../models/HDV.php';

            $phanBoNhanSuModel = new \PhanBoNhanSu();
            $nhanSuModel       = new \NhanSu();
            $hdvModel          = new \HDV();

            $lichKhoiHanh = $this->lichKhoiHanhModel->findById($lichKhoiHanhId);
            if (!$lichKhoiHanh) {
                return false;
            }

            $phanBoHienTai = $phanBoNhanSuModel->getByVaiTro($lichKhoiHanhId, 'HDV');
            if (!empty($phanBoHienTai)) {
                return true;
            }

            if (!empty($lichKhoiHanh['hdv_id'])) {
                return $phanBoNhanSuModel->insert([
                    'lich_khoi_hanh_id' => $lichKhoiHanhId,
                    'nhan_su_id'        => $lichKhoiHanh['hdv_id'],
                    'vai_tro'           => 'HDV',
                    'ghi_chu'           => 'Tự động phân bổ từ hdv_id',
                    'trang_thai'        => 'ChoXacNhan',
                ]) !== false;
            }

            if (empty($ngayKhoiHanh) || empty($ngayKetThuc)) {
                return false;
            }

            $hdvList = $nhanSuModel->getByRole('HDV');
            if (empty($hdvList)) {
                return false;
            }

            $startTime = $ngayKhoiHanh . ' 00:00:00';
            $endTime   = $ngayKetThuc . ' 23:59:59';

            $hdvRanh = null;
            $minTours = PHP_INT_MAX;

            foreach ($hdvList as $hdv) {
                if (empty($hdv['nhan_su_id'])) {
                    continue;
                }
                if ($hdvModel->isAvailable($hdv['nhan_su_id'], $startTime, $endTime)) {
                    $soTour = count($hdvModel->getSchedule($hdv['nhan_su_id']));
                    if ($soTour < $minTours) {
                        $minTours = $soTour;
                        $hdvRanh  = $hdv;
                    }
                }
            }

            if ($hdvRanh && !empty($hdvRanh['nhan_su_id'])) {
                $this->lichKhoiHanhModel->assignHDV($lichKhoiHanhId, $hdvRanh['nhan_su_id']);
                $result = $phanBoNhanSuModel->insert([
                    'lich_khoi_hanh_id' => $lichKhoiHanhId,
                    'nhan_su_id'        => $hdvRanh['nhan_su_id'],
                    'vai_tro'           => 'HDV',
                    'ghi_chu'           => 'Tự động phân bổ - HDV rảnh',
                    'trang_thai'        => 'ChoXacNhan',
                ]);
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

            return false;
        } catch (\Exception $e) {
            error_log('Lỗi tự động phân bổ nhân sự: ' . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // EMAIL
    // =====================================================================

    /**
     * Gửi email xác nhận cho khách tự đặt (lấy email từ session user).
     */
    public function sendBookingConfirmationEmail(int $bookingId, array $data, array $tour): void
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                return;
            }
            $nguoiDung = $this->nguoiDungModel->findById($userId);
            if (!$nguoiDung || empty($nguoiDung['email'])) {
                return;
            }
            $toEmail = $nguoiDung['email'];
            $hoTen   = $nguoiDung['ho_ten'] ?? $nguoiDung['ten_dang_nhap'] ?? 'Quý khách';

            $html = $this->buildConfirmationEmailHtml($bookingId, $hoTen, $data, $tour, false);

            EmailQueueService::enqueue(
                $toEmail,
                'Xác nhận đặt tour thành công – ' . ($tour['ten_tour'] ?? 'AVENTURA'),
                $html
            );
        } catch (\Exception $e) {
            error_log('sendBookingConfirmationEmail error: ' . $e->getMessage());
            $this->logFailedEmail($nguoiDung['email'] ?? '', $bookingId, $e->getMessage());
        }
    }

    /**
     * Gửi email xác nhận khi admin đặt hộ khách (email & tên đã có sẵn).
     */
    public function sendBookingConfirmationEmailDirect(
        string $toEmail,
        string $customerName,
        int $bookingId,
        array $data,
        array $tour
    ): void {
        try {
            $html = $this->buildConfirmationEmailHtml($bookingId, $customerName, $data, $tour, true);
            EmailQueueService::enqueue(
                $toEmail,
                'Xác nhận đặt tour thành công – ' . ($tour['ten_tour'] ?? 'AVENTURA'),
                $html
            );
        } catch (\Exception $e) {
            error_log('sendBookingConfirmationEmailDirect error: ' . $e->getMessage());
            $this->logFailedEmail($toEmail, $bookingId, $e->getMessage());
        }
    }

    private function buildConfirmationEmailHtml(
        int $bookingId,
        string $customerName,
        array $data,
        array $tour,
        bool $isAdminBooking
    ): string {
        $hoTen        = htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8');
        $tourName     = htmlspecialchars($tour['ten_tour'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $diaDiem      = htmlspecialchars($tour['dia_diem'] ?? '', ENT_QUOTES, 'UTF-8');
        $ngayKhoiHanh = isset($data['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($data['ngay_khoi_hanh'])) : 'N/A';
        $ngayKetThuc  = isset($data['ngay_ket_thuc'])  ? date('d/m/Y', strtotime($data['ngay_ket_thuc']))  : 'N/A';
        $soNguoiLon   = (int)($data['so_nguoi_lon']  ?? $data['so_nguoi'] ?? 0);
        $soTreEm      = (int)($data['so_tre_em']     ?? 0);
        $tongTienFmt  = isset($data['tong_tien']) ? number_format((float)$data['tong_tien'], 0, ',', '.') . ' đ' : 'N/A';
        $tienCocFmt   = isset($data['tien_coc'])  ? number_format((float)$data['tien_coc'],  0, ',', '.') . ' đ' : 'N/A';
        $trangThai    = htmlspecialchars($data['trang_thai'] ?? 'ChoXacNhan', ENT_QUOTES, 'UTF-8');
        $baseUrl      = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $bookingLink  = $baseUrl . '/index.php?act=booking/show&id=' . $bookingId;
        $intro        = $isAdminBooking
            ? "Booking tour của bạn đã được nhân viên <strong>AVENTURA</strong> xác nhận. Dưới đây là thông tin chi tiết."
            : "Cảm ơn bạn đã đặt tour tại <strong>AVENTURA</strong>! Dưới đây là thông tin chi tiết booking của bạn.";

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Xác nhận đặt tour</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:30px 0;">
  <tr><td align="center">
    <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.12);">
      <tr>
        <td style="background:linear-gradient(135deg,#1a73e8,#0d47a1);padding:36px 40px;text-align:center;">
          <h1 style="color:#fff;margin:0;font-size:26px;letter-spacing:1px;">AVENTURA</h1>
          <p style="color:#c8dcff;margin:6px 0 0;font-size:14px;">Hệ thống Quản lý Tour Du lịch</p>
        </td>
      </tr>
      <tr>
        <td style="padding:36px 40px;">
          <p style="font-size:16px;color:#333;margin:0 0 8px;">Xin chào <strong>{$hoTen}</strong>,</p>
          <p style="color:#555;margin:0 0 24px;line-height:1.6;">{$intro}</p>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f7ff;border-left:4px solid #1a73e8;border-radius:4px;margin-bottom:24px;">
            <tr><td style="padding:20px 24px;">
              <h2 style="color:#1a73e8;margin:0 0 16px;font-size:18px;">{$tourName}</h2>
              <table width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;color:#444;">
                <tr><td width="45%" style="font-weight:bold;">📍 Điểm đến:</td><td>{$diaDiem}</td></tr>
                <tr><td style="font-weight:bold;">📅 Ngày khởi hành:</td><td>{$ngayKhoiHanh}</td></tr>
                <tr><td style="font-weight:bold;">🏁 Ngày kết thúc:</td><td>{$ngayKetThuc}</td></tr>
                <tr><td style="font-weight:bold;">👥 Số người lớn:</td><td>{$soNguoiLon} người</td></tr>
                <tr><td style="font-weight:bold;">🧒 Số trẻ em:</td><td>{$soTreEm} người</td></tr>
              </table>
            </td></tr>
          </table>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border-left:4px solid #f9a825;border-radius:4px;margin-bottom:24px;">
            <tr><td style="padding:20px 24px;">
              <h3 style="color:#f57f17;margin:0 0 14px;font-size:16px;">💰 Thông tin hóa đơn</h3>
              <table width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;color:#444;">
                <tr><td width="45%" style="font-weight:bold;">Mã booking:</td>
                    <td><span style="background:#e3f2fd;color:#1565c0;padding:2px 8px;border-radius:12px;font-family:monospace;">#BK{$bookingId}</span></td></tr>
                <tr><td style="font-weight:bold;">Tổng tiền:</td>
                    <td style="font-size:16px;font-weight:bold;color:#c62828;">{$tongTienFmt}</td></tr>
                <tr><td style="font-weight:bold;">Tiền cọc:</td>
                    <td style="color:#2e7d32;font-weight:bold;">{$tienCocFmt}</td></tr>
                <tr><td style="font-weight:bold;">Trạng thái:</td>
                    <td><span style="background:#fff3e0;color:#e65100;padding:2px 10px;border-radius:12px;">{$trangThai}</span></td></tr>
              </table>
            </td></tr>
          </table>
          <p style="text-align:center;margin:0 0 28px;">
            <a href="{$bookingLink}" style="display:inline-block;background:#1a73e8;color:#fff;text-decoration:none;padding:14px 36px;border-radius:6px;font-size:15px;font-weight:bold;">
              Xem chi tiết booking
            </a>
          </p>
          <p style="color:#777;font-size:13px;line-height:1.6;margin:0;">
            Nếu bạn có bất kỳ thắc mắc nào, hãy liên hệ với chúng tôi.<br>
            Chúc bạn có chuyến du lịch thật vui vẻ và ý nghĩa! 🌟
          </p>
        </td>
      </tr>
      <tr>
        <td style="background:#f8f9fa;padding:20px 40px;text-align:center;border-top:1px solid #e0e0e0;">
          <p style="color:#9e9e9e;font-size:12px;margin:0;">
            © 2025 AVENTURA Tour Management. Email này được gửi tự động, vui lòng không reply.
          </p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    public function sendDocumentEmail(string $to, string $subject, string $htmlContent, string $toName = ''): void
    {
        $message = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
.container { max-width: 800px; margin: 0 auto; padding: 20px; }
.header { background: linear-gradient(135deg,#667eea,#764ba2); color: white; padding: 20px; text-align: center; }
.content { background: #fff; padding: 20px; }
.footer { background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
</style>
</head>
<body>
<div class="container">
  <div class="header"><h2>Công ty Du lịch ABC</h2><p>Cảm ơn quý khách đã tin tưởng sử dụng dịch vụ</p></div>
  <div class="content">
    <p>Kính gửi: <strong>{$toName}</strong>,</p>
    <p>Chúng tôi xin gửi đến quý khách tài liệu đính kèm.</p>
    <hr>{$htmlContent}<hr>
    <p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ:</p>
    <ul><li>Hotline: 1900 xxxx</li><li>Email: info@dulichabc.vn</li></ul>
  </div>
  <div class="footer"><p>© 2025 Công ty Du lịch ABC. All rights reserved.</p></div>
</div>
</body>
</html>
HTML;
        sendHtmlEmail($to, $subject, $message, '', ['from_name' => 'Công ty Du lịch ABC']);
    }

    private function logFailedEmail(string $toEmail, int $bookingId, string $error): void
    {
        try {
            $logDir  = defined('PATH_ROOT') ? PATH_ROOT . 'storage' : __DIR__ . '/../storage';
            $logFile = $logDir . '/failed_emails.log';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0750, true);
            }
            $entry = json_encode([
                'time'       => date('c'),
                'booking_id' => $bookingId,
                'to'         => $toEmail,
                'error'      => $error,
            ], JSON_UNESCAPED_UNICODE) . PHP_EOL;
            @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        } catch (\Exception $ex) {
            error_log('logFailedEmail write error: ' . $ex->getMessage());
        }
    }

    // =====================================================================
    // PDF / DOCUMENT GENERATION
    // =====================================================================

    public function buildBookingPdfHtml(string $type, array $booking): string
    {
        ob_start();
        switch ($type) {
            case 'hop-dong':
                include __DIR__ . '/../views/admin/templates/hop_dong_template.php';
                break;
            case 'hoa-don':
                include __DIR__ . '/../views/admin/templates/hoa_don_template.php';
                break;
            default:
                include __DIR__ . '/../views/admin/templates/bao_gia_template.php';
        }
        return (string)ob_get_clean();
    }

    public function wrapPdfHtml(string $filename, string $bodyHtml): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{$filename}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    body { font-family: Arial, sans-serif; font-size: 12pt; margin: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    .info-table td { border: 1px solid #dee2e6; padding: 8px; }
    .info-table td:first-child { font-weight: 600; background: #f8f9fa; width: 30%; }
    .detail-table th, .detail-table td { border: 1px solid #000; padding: 8px; }
    .company-header { text-align: center; border-bottom: 3px double #000; padding-bottom: 15px; margin-bottom: 20px; }
    .document-title { text-align: center; font-size: 1.75rem; font-weight: bold; margin: 20px 0; text-transform: uppercase; }
    @media print { body { margin: 0; } .no-print { display: none; } }
  </style>
</head>
<body>
{$bodyHtml}
<div class="no-print text-center mt-4">
  <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="bi bi-printer"></i> In tài liệu</button>
  <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2"><i class="bi bi-x-circle"></i> Đóng</button>
</div>
</body>
</html>
HTML;
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    private function buildBookingSnapshot(array $booking): array
    {
        return [
            'booking_id'     => $booking['booking_id'],
            'tour_id'        => $booking['tour_id'],
            'ten_tour'       => $booking['ten_tour'] ?? 'N/A',
            'khach_hang_id'  => $booking['khach_hang_id'],
            'ten_khach_hang' => $booking['ho_ten'] ?? 'N/A',
            'so_nguoi'       => $booking['so_nguoi'] ?? 0,
            'tong_tien'      => $booking['tong_tien'] ?? 0,
            'ngay_dat'       => $booking['ngay_dat'] ?? null,
            'ngay_khoi_hanh' => $booking['ngay_khoi_hanh'] ?? null,
            'ngay_ket_thuc'  => $booking['ngay_ket_thuc'] ?? null,
            'trang_thai'     => $booking['trang_thai'] ?? 'N/A',
            'ghi_chu'        => $booking['ghi_chu'] ?? null,
        ];
    }
}
