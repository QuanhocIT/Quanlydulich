<?php
require_once __DIR__ . '/../models/GiaoDich.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/KhachHang.php';
require_once __DIR__ . '/../models/LichSuKhachHang.php';
require_once __DIR__ . '/../models/DuToanTour.php';
require_once __DIR__ . '/../models/ChiPhiThucTe.php';
require_once __DIR__ . '/../services/BaoCaoTaiChinhService.php';

class BaoCaoTaiChinhController
{
    private GiaoDich $giaoDichModel;
    private Tour $tourModel;
    private Booking $bookingModel;
    private KhachHang $khachHangModel;
    private LichSuKhachHang $lichSuModel;
    private DuToanTour $duToanModel;
    private ChiPhiThucTe $chiPhiModel;
    private BaoCaoTaiChinhService $service;

    public function __construct()
    {
        requireRole('Admin');
        $this->giaoDichModel  = new GiaoDich();
        $this->tourModel      = new Tour();
        $this->bookingModel   = new Booking();
        $this->khachHangModel = new KhachHang();
        $this->lichSuModel    = new LichSuKhachHang();
        $this->duToanModel    = new DuToanTour();
        $this->chiPhiModel    = new ChiPhiThucTe();
        $this->service        = new BaoCaoTaiChinhService(
            $this->giaoDichModel->conn,
            $this->giaoDichModel,
            $this->tourModel,
            $this->duToanModel,
            $this->chiPhiModel
        );
    }

    // ==================== DASHBOARD ====================

    public function dashboard(): void
    {
        $thangHienTai = date('Y-m');
        $tuNgay       = date('Y-m-01');
        $denNgay      = date('Y-m-t');
        $cacheKey     = 'bao_cao_tai_chinh_dashboard_' . $thangHienTai;

        $payload  = cacheRemember($cacheKey, 90, fn() => $this->service->getDashboardPayload($tuNgay, $denNgay));
        $tongThu  = (float)($payload['tongThu'] ?? 0);
        $tongChi  = (float)($payload['tongChi'] ?? 0);
        $loiNhuan = (float)($payload['loiNhuan'] ?? ($tongThu - $tongChi));
        $topTours = $payload['topTours'] ?? [];

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/dashboard.php';
    }

    // ==================== GIAO DICH ====================

    public function giaoDichTheoTour(): void
    {
        $tourId = (int)($_GET['tour_id'] ?? 0);
        if ($tourId <= 0) {
            $_SESSION['error'] = 'Không tìm thấy tour.';
            header('Location: index.php?act=admin/baoCaoTaiChinh');
            exit;
        }

        $data          = $this->service->getGiaoDichTheoTour($tourId);
        $giaoDichs     = $data['giaoDichs'];
        $tour          = $data['tour'];
        $tongThu       = $data['tongThu'];
        $tongChiGD     = $data['tongChiGD'];
        $tongChiThucTe = $data['tongChiThucTe'];
        $bookings      = $data['bookings'];
        $loiNhuan      = $data['loiNhuan'];

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/chi_tiet_thu_chi_tour.php';
    }

    public function lichSuGiaoDich(): void
    {
        $filters = array_filter([
            'loai'           => $_GET['loai'] ?? '',
            'loai_giao_dich' => $_GET['loai_giao_dich'] ?? '',
            'tour_id'        => $_GET['tour_id'] ?? '',
            'khach_hang_id'  => $_GET['khach_hang_id'] ?? '',
            'tu_ngay'        => $_GET['tu_ngay'] ?? '',
            'den_ngay'       => $_GET['den_ngay'] ?? '',
            'keyword'        => $_GET['keyword'] ?? '',
        ]);

        $perPage           = 30;
        $currentPageNumber = max(1, (int)($_GET['page'] ?? 1));
        $offset            = ($currentPageNumber - 1) * $perPage;

        $giaoDichs    = $this->giaoDichModel->getFiltered($filters, $perPage, $offset);
        $tongSoBanGhi = $this->giaoDichModel->countFiltered($filters);
        $tongHop      = $this->giaoDichModel->getTongThuChiFiltered($filters);
        $tongThu      = (float)$tongHop['tong_thu'];
        $tongChi      = (float)$tongHop['tong_chi'];
        $totalPages   = max(1, (int)ceil($tongSoBanGhi / $perPage));

        $pagination = [
            'currentPage' => $currentPageNumber,
            'perPage'     => $perPage,
            'totalItems'  => $tongSoBanGhi,
            'totalPages'  => $totalPages,
        ];

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/lich_su_giao_dich.php';
    }

    public function chiTietGiaoDich(): void
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = 'Không tìm thấy giao dịch.';
            header('Location: index.php?act=admin/lichSuGiaoDich');
            exit;
        }
        $giao_dich = $this->giaoDichModel->findById($id);
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/chi_tiet_giao_dich.php';
    }

    // ==================== THU CHI / LAI LO TOUR ====================

    public function thuChiTour(): void
    {
        $tuNgay  = $this->service->normalizeDate($_GET['tu_ngay'] ?? date('Y-m-01'));
        $denNgay = $this->service->normalizeDate($_GET['den_ngay'] ?? date('Y-m-t'));
        $tours   = $this->service->buildTourFinancialRows($tuNgay, $denNgay);
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/thu_chi_tour.php';
    }

    public function laiLoTour(): void
    {
        $tuNgay  = $this->service->normalizeDate($_GET['tu_ngay'] ?? date('Y-m-01'));
        $denNgay = $this->service->normalizeDate($_GET['den_ngay'] ?? date('Y-m-t'));

        $baoCao = [];
        foreach ($this->service->buildTourFinancialRows($tuNgay, $denNgay) as $tour) {
            $doanhThu = (float)($tour['tong_thu'] ?? 0);
            $chiPhi   = (float)($tour['tong_chi_thuc_te'] ?? 0);
            $loiNhuan = $doanhThu - $chiPhi;
            $baoCao[] = [
                'tour'      => [
                    'tour_id'   => $tour['tour_id'] ?? null,
                    'ten_tour'  => $tour['ten_tour'] ?? '',
                    'loai_tour' => $tour['loai_tour'] ?? '',
                ],
                'doanh_thu' => $doanhThu,
                'chi_phi'   => $chiPhi,
                'loi_nhuan' => $loiNhuan,
                'ty_suat'   => $doanhThu > 0 ? ($loiNhuan / $doanhThu * 100) : 0,
            ];
        }

        usort($baoCao, static function ($a, $b) {
            return (float)$b['loi_nhuan'] <=> (float)$a['loi_nhuan'];
        });

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/lai_lo_tour.php';
    }

    // ==================== EXPORT ====================

    public function xuatBaoCao(): void
    {
        $loaiBaoCao = strtolower(trim((string)($_GET['loai'] ?? 'giao_dich')));
        $format     = strtolower(trim((string)($_GET['format'] ?? 'excel')));

        if (!in_array($loaiBaoCao, ['giao_dich', 'lai_lo_tour', 'thu_chi_tour'], true)) {
            $_SESSION['error'] = 'Loai bao cao khong hop le.';
            header('Location: ' . $this->buildFinancialReportBackUrl());
            exit;
        }
        if (!in_array($format, ['excel', 'pdf'], true)) {
            $format = 'excel';
        }

        $payload  = $this->service->buildExportPayload($loaiBaoCao, $_GET);
        $fileName = 'bao-cao-' . $loaiBaoCao . '-' . date('Ymd-His');

        if ($format === 'pdf') {
            $this->service->exportFinancialReportPdf(
                $payload['title'], $payload['headers'], $payload['rows'],
                $fileName . '.pdf', $this->buildFinancialReportBackUrl()
            );
            return;
        }

        $this->service->exportFinancialReportExcel(
            $payload['title'], $payload['headers'], $payload['rows'], $fileName . '.xls'
        );
    }

    // ==================== CONG NO HDV ====================

    public function congNo(): void
    {
        $filters = [
            'hdv_id'  => isset($_GET['hdv_id']) ? (int)$_GET['hdv_id'] : 0,
            'tour_id' => isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0,
            'status'  => trim((string)($_GET['status'] ?? '')),
            'keyword' => trim((string)($_GET['keyword'] ?? '')),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cong_no_hdv_id'])) {
            $congNoId        = (int)($_POST['cong_no_hdv_id'] ?? 0);
            $soTienThanhToan = (float)($_POST['so_tien_thanh_toan'] ?? 0);
            $ngayThanhToan   = trim((string)($_POST['ngay_thanh_toan'] ?? date('Y-m-d')));
            $phuongThuc      = trim((string)($_POST['phuong_thuc'] ?? 'ChuyenKhoan'));
            $ghiChu          = trim((string)($_POST['ghi_chu'] ?? ''));

            if ($congNoId <= 0 || $soTienThanhToan <= 0) {
                $_SESSION['error'] = 'Thong tin thanh toan khong hop le.';
            } else {
                if (!in_array($phuongThuc, ['TienMat', 'ChuyenKhoan', 'Khac'], true)) {
                    $phuongThuc = 'ChuyenKhoan';
                }
                try {
                    $this->service->payCongNoHdv(
                        $congNoId, $soTienThanhToan, $ngayThanhToan, $phuongThuc, $ghiChu
                    );
                    $_SESSION['success'] = 'Da ghi nhan thanh toan cong no HDV thanh cong.';
                } catch (Throwable $e) {
                    $_SESSION['error'] = $e->getMessage();
                }
            }

            $redirectQuery = ['act' => 'admin/congNo'];
            if ($filters['hdv_id'] > 0)    $redirectQuery['hdv_id']  = $filters['hdv_id'];
            if ($filters['tour_id'] > 0)   $redirectQuery['tour_id'] = $filters['tour_id'];
            if ($filters['status'] !== '')  $redirectQuery['status']  = $filters['status'];
            if ($filters['keyword'] !== '') $redirectQuery['keyword'] = $filters['keyword'];
            header('Location: index.php?' . http_build_query($redirectQuery));
            exit;
        }

        $exportType  = strtolower(trim((string)($_REQUEST['export'] ?? '')));
        $data        = $this->service->buildCongNoHdvData($filters);
        $congNoHDV   = $data['congNoHDV'];
        $summary     = $data['summary'];
        $hdvOptions  = $data['hdvOptions'];
        $tourOptions = $data['tourOptions'];

        if ($exportType === 'csv') {
            $this->service->exportCongNoHdvCsv($congNoHDV);
            return;
        }

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no_hdv.php';
    }

    // ==================== NHAC HAN / CONG NO KHAC ====================

    public function nhacHanCongNo(): void
    {
        $nhacHanCongNo = $this->service->buildNhacHanCongNo();
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/tong_quan_canh_bao.php';
    }

    public function congNoKhachHang(): void
    {
        $congNoKhachHang = $this->service->buildCongNoKhachHang();
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no_khach_hang.php';
    }

    public function congNoNhaCungCap(): void
    {
        $congNoNhaCungCap = $this->service->buildCongNoNhaCungCap();
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no.php';
    }

    // ==================== DU TOAN TOUR ====================

    public function duToanTour(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveDuToan();
            return;
        }

        $tourId = $_GET['tour_id'] ?? null;
        if ($tourId) {
            $tour    = $this->tourModel->findById($tourId);
            $duToans = $this->duToanModel->getByTour($tourId);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/du_toan_chi_tiet.php';
        } else {
            $duToans = $this->duToanModel->getAll(400, 0);
            $tours   = $this->tourModel->getOptions(500);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/danh_sach_du_toan.php';
        }
    }

    public function formDuToan(): void
    {
        $duToanId = $_GET['id'] ?? null;
        $tourId   = $_GET['tour_id'] ?? null;

        if ($duToanId) {
            $duToan = $this->duToanModel->findById($duToanId);
            $tour   = $this->tourModel->findById($duToan['tour_id']);
        } else {
            $duToan = null;
            $tour   = $tourId ? $this->tourModel->findById($tourId) : null;
        }

        $tours = $this->tourModel->getOptions(500);
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/form_du_toan.php';
    }

    private function saveDuToan(): void
    {
        $duToanId = $_POST['du_toan_id'] ?? null;
        $data     = [
            'tour_id'              => $_POST['tour_id'],
            'lich_khoi_hanh_id'    => $_POST['lich_khoi_hanh_id'] ?? null,
            'cp_phuong_tien'       => $_POST['cp_phuong_tien'] ?? 0,
            'mo_ta_phuong_tien'    => $_POST['mo_ta_phuong_tien'] ?? '',
            'cp_luu_tru'           => $_POST['cp_luu_tru'] ?? 0,
            'mo_ta_luu_tru'        => $_POST['mo_ta_luu_tru'] ?? '',
            'cp_ve_tham_quan'      => $_POST['cp_ve_tham_quan'] ?? 0,
            'mo_ta_ve_tham_quan'   => $_POST['mo_ta_ve_tham_quan'] ?? '',
            'cp_an_uong'           => $_POST['cp_an_uong'] ?? 0,
            'mo_ta_an_uong'        => $_POST['mo_ta_an_uong'] ?? '',
            'cp_huong_dan_vien'    => $_POST['cp_huong_dan_vien'] ?? 0,
            'cp_dich_vu_bo_sung'   => $_POST['cp_dich_vu_bo_sung'] ?? 0,
            'mo_ta_dich_vu'        => $_POST['mo_ta_dich_vu'] ?? '',
            'cp_phat_sinh_du_kien' => $_POST['cp_phat_sinh_du_kien'] ?? 0,
            'mo_ta_phat_sinh'      => $_POST['mo_ta_phat_sinh'] ?? '',
            'nguoi_tao_id'         => $_SESSION['user_id'],
        ];

        if ($duToanId) {
            $result  = $this->duToanModel->update($duToanId, $data);
            $message = 'Cập nhật dự toán thành công!';
        } else {
            $result  = $this->duToanModel->create($data);
            $message = 'Tạo dự toán thành công!';
        }

        $_SESSION[$result ? 'success' : 'error'] = $result ? $message : 'Có lỗi xảy ra!';
        header('Location: index.php?act=admin/duToanTour&tour_id=' . $data['tour_id']);
        exit;
    }

    // ==================== CHI PHI THUC TE ====================

    public function chiPhiThucTe(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveChiPhi();
            return;
        }

        $duToanId = $_GET['du_toan_id'] ?? null;
        if ($duToanId) {
            $duToan  = $this->duToanModel->findById($duToanId);
            $chiPhis = $this->chiPhiModel->getByDuToan($duToanId);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/chi_phi_chi_tiet.php';
        } else {
            $chiPhis = $this->chiPhiModel->getAll(500, 0);
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/danh_sach_chi_phi.php';
        }
    }

    public function formChiPhi(): void
    {
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

    private function saveChiPhi(): void
    {
        $chiPhiId = $_POST['chi_phi_id'] ?? null;
        $data     = [
            'du_toan_id'        => $_POST['du_toan_id'],
            'tour_id'           => $_POST['tour_id'],
            'lich_khoi_hanh_id' => $_POST['lich_khoi_hanh_id'] ?? null,
            'loai_chi_phi'      => $_POST['loai_chi_phi'],
            'ten_khoan_chi'     => $_POST['ten_khoan_chi'],
            'so_tien'           => $_POST['so_tien'],
            'ngay_phat_sinh'    => $_POST['ngay_phat_sinh'],
            'mo_ta'             => $_POST['mo_ta'] ?? '',
            'nguoi_ghi_nhan_id' => $_SESSION['user_id'],
        ];

        if (isset($_FILES['chung_tu']) && $_FILES['chung_tu']['error'] === 0) {
            $uploadDir = __DIR__ . '/../uploads/chung_tu/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $safeFileName = time() . '_' . basename($_FILES['chung_tu']['name']);
            if (move_uploaded_file($_FILES['chung_tu']['tmp_name'], $uploadDir . $safeFileName)) {
                $data['chung_tu'] = 'uploads/chung_tu/' . $safeFileName;
            }
        }

        if ($chiPhiId) {
            $result  = $this->chiPhiModel->update($chiPhiId, $data);
            $message = 'Cập nhật chi phí thành công!';
        } else {
            $result  = $this->chiPhiModel->create($data);
            $message = 'Ghi nhận chi phí thành công!';
            $canhBao = $this->chiPhiModel->kiemTraCanhBao($data['du_toan_id']);
            if (($canhBao['canh_bao'] ?? '') === 'VuotDuToan') {
                $_SESSION['warning'] = 'CẢNH BÁO: Chi phí thực tế đã vượt dự toán!';
            } elseif (($canhBao['canh_bao'] ?? '') === 'GanVuot') {
                $_SESSION['warning'] = 'Lưu ý: Chi phí thực tế đã đạt 90% dự toán!';
            }
        }

        $_SESSION[$result ? 'success' : 'error'] = $result ? $message : 'Có lỗi xảy ra!';
        header('Location: index.php?act=admin/chiPhiThucTe&du_toan_id=' . $data['du_toan_id']);
        exit;
    }

    public function duyetChiPhi(): void
    {
        $chiPhiId = $_GET['id'];
        $result   = $this->chiPhiModel->approve($chiPhiId, $_SESSION['user_id']);
        $chiPhi   = $this->chiPhiModel->findById($chiPhiId);
        $canhBao  = $this->chiPhiModel->kiemTraCanhBao($chiPhi['du_toan_id']);

        if ($result) {
            $_SESSION['success'] = 'Đã duyệt chi phí!';
            if (($canhBao['canh_bao'] ?? '') === 'VuotDuToan') {
                $_SESSION['warning'] = 'CẢNH BÁO: Chi phí thực tế đã vượt dự toán!';
            } elseif (($canhBao['canh_bao'] ?? '') === 'GanVuot') {
                $_SESSION['warning'] = 'Lưu ý: Chi phí thực tế đã đạt 90% dự toán!';
            }
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra!';
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function tuChoiChiPhi(): void
    {
        $chiPhiId = $_POST['id'];
        $lyDo     = $_POST['ly_do'];
        $result   = $this->chiPhiModel->reject($chiPhiId, $_SESSION['user_id'], $lyDo);
        $_SESSION[$result ? 'success' : 'error'] = $result ? 'Đã từ chối chi phí!' : 'Có lỗi xảy ra!';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // ==================== SO SANH DU TOAN ====================

    public function soSanhDuToan(): void
    {
        $duToanId = $_GET['du_toan_id'] ?? null;

        if ($duToanId) {
            $duToan  = $this->duToanModel->findById($duToanId);
            $chiPhis = $this->chiPhiModel->getByDuToan($duToanId);

            $soSanh = [
                'PhuongTien'   => ['du_toan' => $duToan['cp_phuong_tien'],       'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'PhuongTien')],
                'LuuTru'       => ['du_toan' => $duToan['cp_luu_tru'],           'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'LuuTru')],
                'VeThamQuan'   => ['du_toan' => $duToan['cp_ve_tham_quan'],      'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'VeThamQuan')],
                'AnUong'       => ['du_toan' => $duToan['cp_an_uong'],           'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'AnUong')],
                'HuongDanVien' => ['du_toan' => $duToan['cp_huong_dan_vien'],    'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'HuongDanVien')],
                'DichVuBoSung' => ['du_toan' => $duToan['cp_dich_vu_bo_sung'],   'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'DichVuBoSung')],
                'PhatSinh'     => ['du_toan' => $duToan['cp_phat_sinh_du_kien'], 'thuc_te' => $this->chiPhiModel->getTongTheoLoai($duToanId, 'PhatSinh')],
            ];

            $chiPhiSoSanh = [];
            foreach ($soSanh as $loai => $cp) {
                $chenhLech    = $cp['thuc_te'] - $cp['du_toan'];
                $chiPhiSoSanh[] = [
                    'loai_chi_phi' => $loai,
                    'du_toan'      => $cp['du_toan'],
                    'thuc_te'      => $cp['thuc_te'],
                    'chenh_lech'   => $chenhLech,
                    'ghi_chu'      => $chenhLech < 0 ? 'Tiết kiệm' : ($chenhLech > 0 ? 'Vượt dự toán' : ''),
                ];
            }
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/so_sanh_chi_tiet.php';
        } else {
            $canhBaos = $this->duToanModel->getDuToanCanhBao();
            require __DIR__ . '/../views/admin/bao_cao_tai_chinh/tong_quan_canh_bao.php';
        }
    }

    // ==================== HELPERS ====================

    private function buildFinancialReportBackUrl(): string
    {
        $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
        return $referer !== '' ? $referer : 'index.php?act=admin/baoCaoTaiChinh';
    }
}
