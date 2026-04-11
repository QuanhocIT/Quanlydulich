<?php
require_once __DIR__ . '/../models/GiaoDich.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/KhachHang.php';
require_once __DIR__ . '/../models/LichSuKhachHang.php';
require_once __DIR__ . '/../models/DuToanTour.php';
require_once __DIR__ . '/../models/ChiPhiThucTe.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

class BaoCaoTaiChinhController {
        // Hiển thị thu chi từng tour
        public function thuChiTour() {
            // Lấy tổng thu chi theo tour bằng truy vấn gộp để tránh N+1 query.
            $tourStats = $this->giaoDichModel->getThuChiTatCaTour();
            $thuChiTours = [];
            foreach ($tourStats as $stat) {
                $tongThu = (float)($stat['tong_thu'] ?? 0);
                $tongChi = (float)($stat['tong_chi'] ?? 0);

                $tour = [
                    'tour_id' => $stat['tour_id'] ?? null,
                    'ten_tour' => $stat['ten_tour'] ?? '',
                    'loai_tour' => $stat['loai_tour'] ?? '',
                ];

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
        requireRole('Admin');
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

        $cacheKey = 'bao_cao_tai_chinh_dashboard_' . $thangHienTai;
        $payload = cacheRemember($cacheKey, 90, function () use ($tuNgay, $denNgay) {
            // Thống kê giao dịch tháng này
            $thongKe = $this->giaoDichModel->getThongKeTongHop($tuNgay, $denNgay);

            $tongThu = (float)($thongKe['tong_thu'] ?? 0);
            $tongChi = (float)($thongKe['tong_chi'] ?? 0);
            return [
                'tongThu' => $tongThu,
                'tongChi' => $tongChi,
                'loiNhuan' => (float)($thongKe['lai_lo'] ?? ($tongThu - $tongChi)),
                'topTours' => $this->getTopToursByRevenue(5),
            ];
        });

        $tongThu = (float)($payload['tongThu'] ?? 0);
        $tongChi = (float)($payload['tongChi'] ?? 0);
        $loiNhuan = (float)($payload['loiNhuan'] ?? ($tongThu - $tongChi));
        $topTours = $payload['topTours'] ?? [];
        
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

        $perPage = 30;
        $currentPageNumber = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($currentPageNumber - 1) * $perPage;

        $giaoDichs = $this->giaoDichModel->getFiltered($filters, $perPage, $offset);
        $tongSoBanGhi = $this->giaoDichModel->countFiltered($filters);
        $tongHop = $this->giaoDichModel->getTongThuChiFiltered($filters);
        $tongThu = (float)$tongHop['tong_thu'];
        $tongChi = (float)$tongHop['tong_chi'];
        $totalPages = max(1, (int)ceil($tongSoBanGhi / $perPage));

        $pagination = [
            'currentPage' => $currentPageNumber,
            'perPage' => $perPage,
            'totalItems' => $tongSoBanGhi,
            'totalPages' => $totalPages,
        ];
        
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
        $conn = $this->giaoDichModel->conn;

        $filters = [
            'hdv_id' => isset($_GET['hdv_id']) ? (int)$_GET['hdv_id'] : 0,
            'tour_id' => isset($_GET['tour_id']) ? (int)$_GET['tour_id'] : 0,
            'status' => trim((string)($_GET['status'] ?? '')),
            'keyword' => trim((string)($_GET['keyword'] ?? '')),
        ];

        // Ghi nhận thanh toán cho 1 công nợ HDV.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cong_no_hdv_id'])) {
            $congNoId = (int)($_POST['cong_no_hdv_id'] ?? 0);
            $soTienThanhToan = (float)($_POST['so_tien_thanh_toan'] ?? 0);
            $ngayThanhToan = trim((string)($_POST['ngay_thanh_toan'] ?? date('Y-m-d')));
            $phuongThuc = trim((string)($_POST['phuong_thuc'] ?? 'ChuyenKhoan'));
            $ghiChu = trim((string)($_POST['ghi_chu'] ?? ''));

            if ($congNoId <= 0 || $soTienThanhToan <= 0) {
                $_SESSION['error'] = 'Thong tin thanh toan khong hop le.';
            } else {
                if (!in_array($phuongThuc, ['TienMat', 'ChuyenKhoan', 'Khac'], true)) {
                    $phuongThuc = 'ChuyenKhoan';
                }

                try {
                    $stmtDebt = $conn->prepare("SELECT id, so_tien, han_thanh_toan FROM cong_no_hdv WHERE id = ? LIMIT 1");
                    $stmtDebt->execute([$congNoId]);
                    $debtRow = $stmtDebt->fetch(PDO::FETCH_ASSOC);

                    if (!$debtRow) {
                        throw new RuntimeException('Khong tim thay cong no can thanh toan.');
                    }

                    $stmtPaid = $conn->prepare("SELECT COALESCE(SUM(so_tien),0) FROM lich_su_thanh_toan_hdv WHERE cong_no_hdv_id = ?");
                    $stmtPaid->execute([$congNoId]);
                    $daThanhToan = (float)$stmtPaid->fetchColumn();
                    $conLai = max(0.0, (float)$debtRow['so_tien'] - $daThanhToan);

                    if ($conLai <= 0) {
                        throw new RuntimeException('Cong no nay da duoc thanh toan du.');
                    }
                    if ($soTienThanhToan > $conLai) {
                        throw new RuntimeException('So tien thanh toan vuot qua cong no con lai (' . number_format($conLai) . ' VND).');
                    }

                    $conn->beginTransaction();

                    $stmtInsert = $conn->prepare("INSERT INTO lich_su_thanh_toan_hdv (cong_no_hdv_id, ngay_thanh_toan, so_tien, phuong_thuc, ghi_chu) VALUES (?, ?, ?, ?, ?)");
                    $stmtInsert->execute([$congNoId, $ngayThanhToan, $soTienThanhToan, $phuongThuc, $ghiChu]);

                    $conLaiSauThanhToan = max(0.0, $conLai - $soTienThanhToan);
                    $trangThaiMoi = 'ChoDuyet';
                    if ($conLaiSauThanhToan <= 0.0001) {
                        $trangThaiMoi = 'DaThanhToan';
                    } elseif (!empty($debtRow['han_thanh_toan']) && $debtRow['han_thanh_toan'] < date('Y-m-d')) {
                        $trangThaiMoi = 'QuaHan';
                    }

                    $stmtUpdate = $conn->prepare("UPDATE cong_no_hdv SET trang_thai = ? WHERE id = ?");
                    $stmtUpdate->execute([$trangThaiMoi, $congNoId]);

                    $conn->commit();
                    $_SESSION['success'] = 'Da ghi nhan thanh toan cong no HDV thanh cong.';
                } catch (Throwable $e) {
                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    $_SESSION['error'] = $e->getMessage();
                }
            }

            $redirectQuery = ['act' => 'admin/congNo'];
            if ($filters['hdv_id'] > 0) $redirectQuery['hdv_id'] = $filters['hdv_id'];
            if ($filters['tour_id'] > 0) $redirectQuery['tour_id'] = $filters['tour_id'];
            if ($filters['status'] !== '') $redirectQuery['status'] = $filters['status'];
            if ($filters['keyword'] !== '') $redirectQuery['keyword'] = $filters['keyword'];
            header('Location: index.php?' . http_build_query($redirectQuery));
            exit;
        }

        $where = [];
        $params = [];

        if ($filters['hdv_id'] > 0) {
            $where[] = 'c.hdv_id = ?';
            $params[] = $filters['hdv_id'];
        }
        if ($filters['tour_id'] > 0) {
            $where[] = 'c.tour_id = ?';
            $params[] = $filters['tour_id'];
        }
        if ($filters['keyword'] !== '') {
            $where[] = '(nd.ho_ten LIKE ? OR t.ten_tour LIKE ? OR c.ghi_chu LIKE ?)';
            $kw = '%' . $filters['keyword'] . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $sql = "SELECT c.id, c.tour_id, c.hdv_id, c.so_tien, c.loai_cong_no, c.han_thanh_toan, c.trang_thai, c.ghi_chu,
                       nd.ho_ten AS ten_hdv,
                       t.ten_tour,
                       COALESCE(ls.tong_da_thanh_toan, 0) AS tong_da_thanh_toan,
                       COALESCE(ls.so_lan_thanh_toan, 0) AS so_lan_thanh_toan,
                       ls.lan_thanh_toan_cuoi
                FROM cong_no_hdv c
                JOIN nhan_su ns ON c.hdv_id = ns.nhan_su_id
                JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                JOIN tour t ON c.tour_id = t.tour_id
                LEFT JOIN (
                    SELECT cong_no_hdv_id,
                           COALESCE(SUM(so_tien), 0) AS tong_da_thanh_toan,
                           COUNT(*) AS so_lan_thanh_toan,
                           MAX(ngay_thanh_toan) AS lan_thanh_toan_cuoi
                    FROM lich_su_thanh_toan_hdv
                    GROUP BY cong_no_hdv_id
                ) ls ON ls.cong_no_hdv_id = c.id";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY c.updated_at DESC, c.id DESC';

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $congNoHDV = [];
        $summary = [
            'tong_goc' => 0.0,
            'tong_da_thanh_toan' => 0.0,
            'tong_con_lai' => 0.0,
            'so_cong_no' => 0,
            'so_qua_han' => 0,
            'so_da_thanh_toan' => 0,
        ];

        foreach ($rows as $row) {
            $tongGoc = (float)($row['so_tien'] ?? 0);
            $tongDaThanhToan = (float)($row['tong_da_thanh_toan'] ?? 0);
            $conLai = max(0.0, $tongGoc - $tongDaThanhToan);
            $isQuaHan = ($conLai > 0) && !empty($row['han_thanh_toan']) && ($row['han_thanh_toan'] < date('Y-m-d'));
            $trangThaiHienThi = $conLai <= 0 ? 'DaThanhToan' : ($isQuaHan ? 'QuaHan' : 'ConNo');

            if ($filters['status'] !== '' && $filters['status'] !== $trangThaiHienThi) {
                continue;
            }

            $summary['tong_goc'] += $tongGoc;
            $summary['tong_da_thanh_toan'] += $tongDaThanhToan;
            $summary['tong_con_lai'] += $conLai;
            $summary['so_cong_no']++;
            if ($isQuaHan) $summary['so_qua_han']++;
            if ($conLai <= 0) $summary['so_da_thanh_toan']++;

            $congNoHDV[] = [
                'id' => (int)$row['id'],
                'hdv_id' => (int)$row['hdv_id'],
                'tour_id' => (int)$row['tour_id'],
                'ten_hdv' => $row['ten_hdv'],
                'ten_tour' => $row['ten_tour'],
                'loai_cong_no' => $row['loai_cong_no'],
                'han_thanh_toan' => $row['han_thanh_toan'],
                'ghi_chu' => $row['ghi_chu'],
                'tong_goc' => $tongGoc,
                'tong_da_thanh_toan' => $tongDaThanhToan,
                'con_lai' => $conLai,
                'so_lan_thanh_toan' => (int)($row['so_lan_thanh_toan'] ?? 0),
                'lan_thanh_toan_cuoi' => $row['lan_thanh_toan_cuoi'],
                'trang_thai_hien_thi' => $trangThaiHienThi,
            ];
        }

        // Lấy lịch sử thanh toán theo danh sách công nợ hiện tại.
        $historyMap = [];
        if (!empty($congNoHDV)) {
            $ids = array_column($congNoHDV, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmtLs = $conn->prepare("SELECT cong_no_hdv_id, ngay_thanh_toan, so_tien, phuong_thuc, ghi_chu
                                     FROM lich_su_thanh_toan_hdv
                                     WHERE cong_no_hdv_id IN ($placeholders)
                                     ORDER BY ngay_thanh_toan DESC, id DESC");
            $stmtLs->execute($ids);
            $allLs = $stmtLs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allLs as $ls) {
                $cid = (int)$ls['cong_no_hdv_id'];
                if (!isset($historyMap[$cid])) {
                    $historyMap[$cid] = [];
                }
                $historyMap[$cid][] = $ls;
            }
        }

        foreach ($congNoHDV as &$item) {
            $cid = (int)$item['id'];
            $item['lich_su_thanh_toan'] = $historyMap[$cid] ?? [];
        }
        unset($item);

        if ((($_GET['export'] ?? '') === 'csv')) {
            $this->exportCongNoHdvCsv($congNoHDV);
            return;
        }

        $hdvOptionsStmt = $conn->prepare("SELECT ns.nhan_su_id AS hdv_id, nd.ho_ten AS ten_hdv
                                          FROM nhan_su ns
                                          JOIN nguoi_dung nd ON nd.id = ns.nguoi_dung_id
                                          ORDER BY nd.ho_ten ASC");
        $hdvOptionsStmt->execute();
        $hdvOptions = $hdvOptionsStmt->fetchAll(PDO::FETCH_ASSOC);

        $tourOptionsStmt = $conn->prepare("SELECT tour_id, ten_tour FROM tour ORDER BY ten_tour ASC");
        $tourOptionsStmt->execute();
        $tourOptions = $tourOptionsStmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no_hdv.php';
    }

    private function exportCongNoHdvCsv(array $rows): void {
        $fileName = 'cong-no-hdv-' . date('Ymd-His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            exit;
        }

        // UTF-8 BOM for Excel compatibility.
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'ID',
            'HDV',
            'Tour',
            'Loai cong no',
            'Tong goc',
            'Da thanh toan',
            'Con lai',
            'Han thanh toan',
            'Trang thai',
            'So lan thanh toan',
            'Lan thanh toan cuoi',
            'Ghi chu'
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                (int)($row['id'] ?? 0),
                (string)($row['ten_hdv'] ?? ''),
                (string)($row['ten_tour'] ?? ''),
                (string)($row['loai_cong_no'] ?? ''),
                (float)($row['tong_goc'] ?? 0),
                (float)($row['tong_da_thanh_toan'] ?? 0),
                (float)($row['con_lai'] ?? 0),
                (string)($row['han_thanh_toan'] ?? ''),
                (string)($row['trang_thai_hien_thi'] ?? ''),
                (int)($row['so_lan_thanh_toan'] ?? 0),
                (string)($row['lan_thanh_toan_cuoi'] ?? ''),
                (string)($row['ghi_chu'] ?? ''),
            ]);
        }

        fclose($out);
        exit;
    }
    
    // Báo cáo lãi lỗ từng tour
    public function laiLoTour() {
        $tuNgay = $_GET['tu_ngay'] ?? date('Y-m-01');
        $denNgay = $_GET['den_ngay'] ?? date('Y-m-t');
        
        $tours = $this->tourModel->getOptions(500);
        
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
        $loaiBaoCao = strtolower(trim((string)($_GET['loai'] ?? 'giao_dich')));
        $format = strtolower(trim((string)($_GET['format'] ?? 'excel')));

        if (!in_array($loaiBaoCao, ['giao_dich', 'lai_lo_tour', 'thu_chi_tour'], true)) {
            $_SESSION['error'] = 'Loai bao cao khong hop le.';
            header('Location: ' . $this->buildFinancialReportBackUrl());
            exit;
        }

        if (!in_array($format, ['excel', 'pdf'], true)) {
            $format = 'excel';
        }

        $payload = $this->buildExportPayload($loaiBaoCao);
        $fileName = 'bao-cao-' . $loaiBaoCao . '-' . date('Ymd-His');

        if ($format === 'pdf') {
            $this->exportFinancialReportPdf($payload['title'], $payload['headers'], $payload['rows'], $fileName . '.pdf');
            return;
        }

        $this->exportFinancialReportExcel($payload['title'], $payload['headers'], $payload['rows'], $fileName . '.xls');
    }

    private function buildExportPayload($loaiBaoCao) {
        switch ($loaiBaoCao) {
            case 'lai_lo_tour':
                $rows = [];
                foreach ($this->tourModel->getOptions(500) as $tour) {
                    $doanhThu = (float)$this->giaoDichModel->getTongThuByTour($tour['tour_id']);
                    $chiPhi = (float)$this->giaoDichModel->getTongChiByTour($tour['tour_id']);
                    $loiNhuan = $doanhThu - $chiPhi;
                    $tySuat = $doanhThu > 0 ? ($loiNhuan / $doanhThu * 100) : 0;
                    $rows[] = [
                        (string)($tour['ten_tour'] ?? ''),
                        $this->formatCurrency($doanhThu),
                        $this->formatCurrency($chiPhi),
                        $this->formatCurrency($loiNhuan),
                        number_format($tySuat, 2) . '%',
                    ];
                }

                usort($rows, function ($left, $right) {
                    return strcmp((string)$left[0], (string)$right[0]);
                });

                return [
                    'title' => 'Bao cao lai lo tung tour',
                    'headers' => ['Tour', 'Doanh thu', 'Chi phi', 'Loi nhuan', 'Ty suat'],
                    'rows' => $rows,
                ];

            case 'thu_chi_tour':
                $stats = $this->giaoDichModel->getThuChiTatCaTour();
                $rows = [];
                foreach ($stats as $stat) {
                    $tongThu = (float)($stat['tong_thu'] ?? 0);
                    $tongChi = (float)($stat['tong_chi'] ?? 0);
                    $rows[] = [
                        (string)($stat['ten_tour'] ?? ''),
                        (string)($stat['loai_tour'] ?? ''),
                        $this->formatCurrency($tongThu),
                        $this->formatCurrency($tongChi),
                        $this->formatCurrency($tongThu - $tongChi),
                    ];
                }

                return [
                    'title' => 'Bao cao thu chi tung tour',
                    'headers' => ['Ten tour', 'Loai tour', 'Tong thu', 'Tong chi', 'Loi nhuan'],
                    'rows' => $rows,
                ];

            case 'giao_dich':
            default:
                $filters = [
                    'loai' => $_GET['loai_giao_dich_chinh'] ?? ($_GET['loai'] ?? ''),
                    'loai_giao_dich' => $_GET['loai_giao_dich'] ?? '',
                    'tour_id' => $_GET['tour_id'] ?? '',
                    'khach_hang_id' => $_GET['khach_hang_id'] ?? '',
                    'tu_ngay' => $_GET['tu_ngay'] ?? '',
                    'den_ngay' => $_GET['den_ngay'] ?? '',
                    'keyword' => $_GET['keyword'] ?? '',
                ];
                $filters = array_filter($filters, function ($value) {
                    return $value !== '' && $value !== null;
                });

                $rows = [];
                foreach ($this->giaoDichModel->getFiltered($filters) as $gd) {
                    $rows[] = [
                        (string)($gd['ngay_giao_dich'] ?? ''),
                        (string)($gd['loai'] ?? ''),
                        (string)($gd['loai_giao_dich'] ?? ''),
                        $this->formatCurrency((float)($gd['so_tien'] ?? 0)),
                        (string)($gd['mo_ta'] ?? ''),
                        (string)($gd['nguoi_thuc_hien'] ?? ''),
                    ];
                }

                return [
                    'title' => 'Lich su giao dich noi bo',
                    'headers' => ['Ngay giao dich', 'Loai', 'Loai giao dich', 'So tien', 'Mo ta', 'Nguoi thuc hien'],
                    'rows' => $rows,
                ];
        }
    }

    private function exportFinancialReportExcel($title, array $headers, array $rows, $fileName) {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"><title>' . htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') . '</title></head><body>';
        echo '<h2>' . htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') . '</h2>';
        echo '<table border="1" cellspacing="0" cellpadding="6">';
        echo '<thead><tr>';
        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars((string)$header, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr></thead><tbody>';

        if (empty($rows)) {
            echo '<tr><td colspan="' . count($headers) . '">Khong co du lieu</td></tr>';
        } else {
            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($row as $cell) {
                    echo '<td>' . htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                echo '</tr>';
            }
        }

        echo '</tbody></table></body></html>';
        exit;
    }

    private function exportFinancialReportPdf($title, array $headers, array $rows, $fileName) {
        if (!class_exists(Dompdf::class)) {
            $_SESSION['error'] = 'Khong tim thay thu vien PDF. Vui long kiem tra composer install.';
            header('Location: ' . $this->buildFinancialReportBackUrl());
            exit;
        }

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->renderFinancialReportPdfHtml($title, $headers, $rows), 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream($fileName, ['Attachment' => true]);
        exit;
    }

    private function renderFinancialReportPdfHtml($title, array $headers, array $rows) {
        $thead = '';
        foreach ($headers as $header) {
            $thead .= '<th>' . htmlspecialchars((string)$header, ENT_QUOTES, 'UTF-8') . '</th>';
        }

        $tbody = '';
        if (empty($rows)) {
            $tbody = '<tr><td colspan="' . count($headers) . '">Khong co du lieu</td></tr>';
        } else {
            foreach ($rows as $row) {
                $tbody .= '<tr>';
                foreach ($row as $cell) {
                    $tbody .= '<td>' . htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $tbody .= '</tr>';
            }
        }

        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>'
            . htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8')
            . '</title><style>'
            . 'body{font-family:"DejaVu Sans",sans-serif;font-size:11px;color:#111;}'
            . 'h1{font-size:18px;margin:0 0 8px 0;}'
            . '.meta{margin:0 0 12px 0;color:#555;}'
            . 'table{width:100%;border-collapse:collapse;}'
            . 'th,td{border:1px solid #ccc;padding:6px;vertical-align:top;}'
            . 'th{background:#f3f3f3;text-align:left;}'
            . '</style></head><body>'
            . '<h1>' . htmlspecialchars((string)$title, ENT_QUOTES, 'UTF-8') . '</h1>'
            . '<div class="meta">Ngay xuat: ' . date('d/m/Y H:i') . '</div>'
            . '<table><thead><tr>' . $thead . '</tr></thead><tbody>' . $tbody . '</tbody></table>'
            . '</body></html>';
    }

    private function formatCurrency($value) {
        return number_format((float)$value) . ' VND';
    }

    private function buildFinancialReportBackUrl() {
        $referer = trim((string)($_SERVER['HTTP_REFERER'] ?? ''));
        if ($referer !== '') {
            return $referer;
        }

        return 'index.php?act=admin/baoCaoTaiChinh';
    }
    
    // Helper: Lấy top tours theo doanh thu
    private function getTopToursByRevenue($limit = 5) {
        $limit = max(1, (int)$limit);
        $cacheKey = 'bao_cao_tai_chinh_top_tour_revenue_limit_' . $limit;

        return cacheRemember($cacheKey, 120, function () use ($limit) {
            $stats = $this->giaoDichModel->getThuChiTatCaTour();
            $result = [];

            foreach ($stats as $stat) {
                $result[] = [
                    'tour' => [
                        'tour_id' => $stat['tour_id'] ?? null,
                        'ten_tour' => $stat['ten_tour'] ?? '',
                        'loai_tour' => $stat['loai_tour'] ?? '',
                    ],
                    'doanh_thu' => (float)($stat['tong_thu'] ?? 0)
                ];
            }

            usort($result, function($a, $b) {
                return $b['doanh_thu'] <=> $a['doanh_thu'];
            });

            return array_slice($result, 0, $limit);
        });
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
            $duToans = $this->duToanModel->getAll(400, 0);
            $tours = $this->tourModel->getOptions(500);
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
        
        $tours = $this->tourModel->getOptions(500);
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
            $chiPhis = $this->chiPhiModel->getAll(500, 0);
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
        $conn = $this->giaoDichModel->conn;

        // Nhắc hạn công nợ khách hàng
        $sqlBookings = "SELECT
                            b.booking_id,
                            b.khach_hang_id,
                            b.tour_id,
                            b.han_thanh_toan,
                            nd.ho_ten,
                            t.ten_tour
                        FROM booking b
                        LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                        LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                        LEFT JOIN tour t ON b.tour_id = t.tour_id
                        WHERE b.han_thanh_toan IS NOT NULL
                          AND b.han_thanh_toan <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                          AND (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')";
        $stmtBookings = $conn->prepare($sqlBookings);
        $stmtBookings->execute();
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        foreach ($bookings as $booking) {
            $hanThanhToan = $booking['han_thanh_toan'] ?? null;
            if (empty($hanThanhToan)) {
                continue;
            }
            $is_qua_han = $today > $hanThanhToan;
            $is_sap_han = !$is_qua_han && (strtotime($hanThanhToan) - strtotime($today) <= 3 * 24 * 3600);

            $nhacHanCongNo[] = [
                'doi_tuong' => 'Khách hàng ' . ($booking['ho_ten'] ?? $booking['khach_hang_id']),
                'noi_dung' => 'Đến hạn thanh toán hợp đồng tour ' . ($booking['ten_tour'] ?? $booking['tour_id']),
                'han' => $hanThanhToan,
                'is_qua_han' => $is_qua_han,
                'is_sap_han' => $is_sap_han
            ];
        }

        // Nhắc hạn công nợ nhà cung cấp
        $sqlNCC = "SELECT
                        c.*,
                        ncc.ten_don_vi
                    FROM cong_no_nha_cung_cap c
                    JOIN nha_cung_cap ncc ON c.nha_cung_cap_id = ncc.id_nha_cung_cap
                    WHERE c.han_thanh_toan IS NOT NULL
                      AND c.han_thanh_toan <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
        $stmtNCC = $conn->prepare($sqlNCC);
        $stmtNCC->execute();
        $rowsNCC = $stmtNCC->fetchAll(PDO::FETCH_ASSOC);
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
        $sqlHDV = "SELECT
                        c.*,
                        nd.ho_ten
                    FROM cong_no_hdv c
                    JOIN nhan_su h ON c.hdv_id = h.nhan_su_id
                    JOIN nguoi_dung nd ON h.nguoi_dung_id = nd.id
                    WHERE c.han_thanh_toan IS NOT NULL
                      AND c.han_thanh_toan <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
        $stmtHDV = $conn->prepare($sqlHDV);
        $stmtHDV->execute();
        $rowsHDV = $stmtHDV->fetchAll(PDO::FETCH_ASSOC);
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
        $conn = $this->giaoDichModel->conn;

        $sqlBookings = "SELECT
                            b.booking_id,
                            b.khach_hang_id,
                            b.tour_id,
                            b.tong_tien,
                            nd.ho_ten AS ten_khach_hang,
                            nd.email,
                            nd.so_dien_thoai,
                            t.ten_tour
                        FROM booking b
                        LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
                        LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                        LEFT JOIN tour t ON b.tour_id = t.tour_id
                        WHERE (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')
                        ORDER BY b.ngay_dat DESC, b.booking_id DESC";
        $stmtBookings = $conn->prepare($sqlBookings);
        $stmtBookings->execute();
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        $historyByBooking = [];
        $paidByBooking = [];

        if (!empty($bookings)) {
            $bookingIds = array_map(static function ($row) {
                return (int)($row['booking_id'] ?? 0);
            }, $bookings);
            $bookingIds = array_values(array_filter($bookingIds, static function ($id) {
                return $id > 0;
            }));

            if (!empty($bookingIds)) {
                $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
                $sqlHistory = "SELECT
                                    b.booking_id,
                                    gdtc.ngay_giao_dich AS ngay,
                                    gdtc.so_tien
                               FROM booking b
                               INNER JOIN giao_dich_tai_chinh gdtc ON gdtc.tour_id = b.tour_id
                               WHERE b.booking_id IN ($placeholders)
                                                                 AND gdtc.loai = 'Thu'
                               ORDER BY b.booking_id ASC, gdtc.ngay_giao_dich ASC";
                $stmtHistory = $conn->prepare($sqlHistory);
                $stmtHistory->execute($bookingIds);
                $historyRows = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

                foreach ($historyRows as $row) {
                    $bookingId = (int)($row['booking_id'] ?? 0);
                    if ($bookingId <= 0) {
                        continue;
                    }
                    if (!isset($historyByBooking[$bookingId])) {
                        $historyByBooking[$bookingId] = [];
                    }
                    $historyByBooking[$bookingId][] = [
                        'ngay' => $row['ngay'] ?? null,
                        'so_tien' => (float)($row['so_tien'] ?? 0),
                    ];
                    $paidByBooking[$bookingId] = (float)($paidByBooking[$bookingId] ?? 0) + (float)($row['so_tien'] ?? 0);
                }
            }
        }

        $congNoKhachHang = [];
        foreach ($bookings as $booking) {
            $bookingId = (int)($booking['booking_id'] ?? 0);
            $tongTien = (float)($booking['tong_tien'] ?? 0);
            $daThanhToan = (float)($paidByBooking[$bookingId] ?? 0);
            $congNo = max(0.0, $tongTien - $daThanhToan);

            $congNoKhachHang[] = [
                'khach_hang_id' => (int)($booking['khach_hang_id'] ?? 0),
                'ten_khach_hang' => $booking['ten_khach_hang'] ?? 'N/A',
                'email' => $booking['email'] ?? '',
                'so_dien_thoai' => $booking['so_dien_thoai'] ?? '',
                'ten_tour' => $booking['ten_tour'] ?? 'N/A',
                'cong_no' => $congNo,
                'lich_su_thanh_toan' => $historyByBooking[$bookingId] ?? [],
            ];
        }

        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no_khach_hang.php';
    }

    // Hiển thị công nợ nhà cung cấp
    public function congNoNhaCungCap() {
        $conn = $this->giaoDichModel->conn;

        // Lấy danh sách công nợ NCC
        $sql = "SELECT c.*, ncc.ten_don_vi FROM cong_no_nha_cung_cap c JOIN nha_cung_cap ncc ON c.nha_cung_cap_id = ncc.id_nha_cung_cap";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lichSuByCongNoId = [];
        if (!empty($rows)) {
            $congNoIds = array_map(static function ($row) {
                return (int)($row['id'] ?? 0);
            }, $rows);
            $congNoIds = array_values(array_filter($congNoIds, static function ($id) {
                return $id > 0;
            }));

            if (!empty($congNoIds)) {
                $placeholders = implode(',', array_fill(0, count($congNoIds), '?'));
                $sqlLichSu = "SELECT
                                  cong_no_ncc_id,
                                  ngay_thanh_toan AS ngay,
                                  so_tien_thanh_toan AS so_tien
                              FROM lich_su_thanh_toan_ncc
                              WHERE cong_no_ncc_id IN ($placeholders)
                              ORDER BY cong_no_ncc_id ASC, ngay_thanh_toan ASC";
                $stmtLichSu = $conn->prepare($sqlLichSu);
                $stmtLichSu->execute($congNoIds);
                $lichSuRows = $stmtLichSu->fetchAll(PDO::FETCH_ASSOC);

                foreach ($lichSuRows as $item) {
                    $congNoId = (int)($item['cong_no_ncc_id'] ?? 0);
                    if ($congNoId <= 0) {
                        continue;
                    }
                    if (!isset($lichSuByCongNoId[$congNoId])) {
                        $lichSuByCongNoId[$congNoId] = [];
                    }
                    $lichSuByCongNoId[$congNoId][] = [
                        'ngay' => $item['ngay'] ?? null,
                        'so_tien' => (float)($item['so_tien'] ?? 0),
                    ];
                }
            }
        }

        $congNoNhaCungCap = [];
        foreach ($rows as $row) {
            $congNoId = (int)($row['id'] ?? 0);
            $congNoNhaCungCap[] = [
                'ten_nha_cung_cap' => $row['ten_don_vi'],
                'ten_dich_vu' => $row['ghi_chu'] ?? '',
                'cong_no' => $row['so_tien'],
                'lich_su_thanh_toan' => $lichSuByCongNoId[$congNoId] ?? []
            ];
        }
        require __DIR__ . '/../views/admin/bao_cao_tai_chinh/cong_no.php';
    }
}

