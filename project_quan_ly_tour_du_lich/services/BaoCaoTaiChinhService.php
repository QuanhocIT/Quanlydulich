<?php

/**
 * BaoCaoTaiChinhService
 *
 * Chứa toàn bộ logic nghiệp vụ tài chính: tính toán, truy vấn, export.
 * Controller chỉ đọc request → gọi service → render view.
 */

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

class BaoCaoTaiChinhService
{
    private PDO $conn;
    private GiaoDich $giaoDichModel;
    private Tour $tourModel;
    private DuToanTour $duToanModel;
    private ChiPhiThucTe $chiPhiModel;

    public function __construct(
        PDO $conn,
        GiaoDich $giaoDichModel,
        Tour $tourModel,
        DuToanTour $duToanModel,
        ChiPhiThucTe $chiPhiModel
    ) {
        $this->conn = $conn;
        $this->giaoDichModel = $giaoDichModel;
        $this->tourModel = $tourModel;
        $this->duToanModel = $duToanModel;
        $this->chiPhiModel = $chiPhiModel;
    }

    // ==================== UTILITIES ====================

    public function normalizeDate(string $value): string
    {
        $raw = trim($value);
        if ($raw === '') {
            return '';
        }
        $date = DateTime::createFromFormat('Y-m-d', $raw);
        return $date === false ? '' : $date->format('Y-m-d');
    }

    public function formatCurrency(float $value): string
    {
        return number_format($value) . ' VND';
    }

    // ==================== DASHBOARD ====================

    /**
     * Xây payload cho dashboard tài chính (dùng bên trong cacheRemember).
     */
    public function getDashboardPayload(string $tuNgay, string $denNgay): array
    {
        $thongKe = $this->giaoDichModel->getThongKeTongHop($tuNgay, $denNgay);
        $tongThu = (float)($thongKe['tong_thu'] ?? 0);
        $tongChi = (float)($thongKe['tong_chi'] ?? 0);
        return [
            'tongThu'   => $tongThu,
            'tongChi'   => $tongChi,
            'loiNhuan'  => (float)($thongKe['lai_lo'] ?? ($tongThu - $tongChi)),
            'topTours'  => $this->getTopToursByRevenue(5),
        ];
    }

    // ==================== TOUR FINANCIAL ROWS ====================

    /**
     * Trả về mảng row tài chính (thu/chi/lợi nhuận) cho từng tour trong khoảng ngày.
     */
    public function buildTourFinancialRows(string $startDate = '', string $endDate = ''): array
    {
        $stats = $this->giaoDichModel->getThuChiTatCaTour($startDate ?: null, $endDate ?: null);

        $chiPhiSql = "SELECT tour_id, COALESCE(SUM(so_tien), 0) AS tong_chi_thuc_te
                      FROM chi_phi_thuc_te
                      WHERE trang_thai = 'DaDuyet'";
        $chiPhiParams = [];
        if ($startDate !== '') {
            $chiPhiSql .= ' AND ngay_phat_sinh >= ?';
            $chiPhiParams[] = $startDate;
        }
        if ($endDate !== '') {
            $chiPhiSql .= ' AND ngay_phat_sinh <= ?';
            $chiPhiParams[] = $endDate;
        }
        $chiPhiSql .= ' GROUP BY tour_id';
        $stmtChiPhi = $this->conn->prepare($chiPhiSql);
        $stmtChiPhi->execute($chiPhiParams);

        $chiPhiByTour = [];
        foreach ($stmtChiPhi->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $chiPhiByTour[(int)($row['tour_id'] ?? 0)] = (float)($row['tong_chi_thuc_te'] ?? 0);
        }

        $stmtDuToan = $this->conn->prepare(
            "SELECT tour_id, COALESCE(SUM(tong_du_toan), 0) AS tong_du_toan
             FROM du_toan_tour GROUP BY tour_id"
        );
        $stmtDuToan->execute();

        $duToanByTour = [];
        foreach ($stmtDuToan->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $duToanByTour[(int)($row['tour_id'] ?? 0)] = (float)($row['tong_du_toan'] ?? 0);
        }

        $rows = [];
        foreach ($stats as $stat) {
            $tourId = (int)($stat['tour_id'] ?? 0);
            if ($tourId <= 0) {
                continue;
            }

            $tongThu        = (float)($stat['tong_thu'] ?? 0);
            $tongChiGD      = (float)($stat['tong_chi'] ?? 0);
            $tongChiThucTe  = (float)($chiPhiByTour[$tourId] ?? 0);
            $tongDuToan     = (float)($duToanByTour[$tourId] ?? 0);
            $status         = 'AnToan';

            if ($tongDuToan > 0) {
                if ($tongChiThucTe > $tongDuToan) {
                    $status = 'VuotDuToan';
                } elseif ($tongChiThucTe >= ($tongDuToan * 0.9)) {
                    $status = 'GanVuot';
                }
            }

            $rows[] = [
                'tour_id'             => $tourId,
                'ten_tour'            => (string)($stat['ten_tour'] ?? ''),
                'loai_tour'           => (string)($stat['loai_tour'] ?? ''),
                'tong_thu'            => $tongThu,
                'tong_chi_giao_dich'  => $tongChiGD,
                'tong_chi_thuc_te'    => $tongChiThucTe,
                'tong_du_toan'        => $tongDuToan,
                'loi_nhuan'           => $tongThu - $tongChiThucTe,
                'status'              => $status,
            ];
        }

        usort($rows, static function ($a, $b) {
            return (float)$b['loi_nhuan'] <=> (float)$a['loi_nhuan'];
        });

        return $rows;
    }

    /**
     * Top N tour theo doanh thu (có cache).
     */
    public function getTopToursByRevenue(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $cacheKey = 'bao_cao_tai_chinh_top_tour_revenue_limit_' . $limit;

        return cacheRemember($cacheKey, 120, function () use ($limit) {
            $stats  = $this->giaoDichModel->getThuChiTatCaTour();
            $result = [];
            foreach ($stats as $stat) {
                $result[] = [
                    'tour'       => [
                        'tour_id'   => $stat['tour_id'] ?? null,
                        'ten_tour'  => $stat['ten_tour'] ?? '',
                        'loai_tour' => $stat['loai_tour'] ?? '',
                    ],
                    'doanh_thu' => (float)($stat['tong_thu'] ?? 0),
                ];
            }
            usort($result, static function ($a, $b) {
                return $b['doanh_thu'] <=> $a['doanh_thu'];
            });
            return array_slice($result, 0, $limit);
        });
    }

    // ==================== GIAO DICH THEO TOUR ====================

    /**
     * Lấy dữ liệu giao dịch + booking cho 1 tour cụ thể.
     * Trả về ['giaoDichs', 'tour', 'tongThu', 'tongChiGD', 'tongChiThucTe', 'bookings', 'loiNhuan']
     */
    public function getGiaoDichTheoTour(int $tourId): array
    {
        $giaoDichs = $this->giaoDichModel->getByTourId($tourId);
        $tour      = $this->tourModel->findById($tourId);

        $tongThu    = 0.0;
        $tongChiGD  = 0.0;
        foreach ($giaoDichs as $gd) {
            $soTien = (float)($gd['so_tien'] ?? 0);
            if (($gd['loai'] ?? '') === 'Thu') {
                $tongThu += $soTien;
            } elseif (($gd['loai'] ?? '') === 'Chi') {
                $tongChiGD += $soTien;
            }
        }

        $stmtChiPhi = $this->conn->prepare(
            "SELECT COALESCE(SUM(so_tien), 0)
             FROM chi_phi_thuc_te
             WHERE tour_id = ? AND trang_thai = 'DaDuyet'"
        );
        $stmtChiPhi->execute([$tourId]);
        $tongChiThucTe = (float)$stmtChiPhi->fetchColumn();

        $stmtBookings = $this->conn->prepare(
            "SELECT b.booking_id, b.ngay_dat, b.tong_tien, b.trang_thai, nd.ho_ten
             FROM booking b
             LEFT JOIN khach_hang kh ON kh.khach_hang_id = b.khach_hang_id
             LEFT JOIN nguoi_dung nd ON nd.id = kh.nguoi_dung_id
             WHERE b.tour_id = ?
               AND (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')
             ORDER BY b.ngay_dat DESC, b.booking_id DESC"
        );
        $stmtBookings->execute([$tourId]);
        $bookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

        return [
            'giaoDichs'    => $giaoDichs,
            'tour'         => $tour,
            'tongThu'      => $tongThu,
            'tongChiGD'    => $tongChiGD,
            'tongChiThucTe'=> $tongChiThucTe,
            'bookings'     => $bookings,
            'loiNhuan'     => $tongThu - $tongChiGD - $tongChiThucTe,
        ];
    }

    // ==================== CONG NO HDV ====================

    /**
     * Ghi nhận thanh toán công nợ HDV (transaction an toàn).
     * Ném RuntimeException nếu có lỗi nghiệp vụ.
     */
    public function payCongNoHdv(
        int    $congNoId,
        float  $soTienThanhToan,
        string $ngayThanhToan,
        string $phuongThuc,
        string $ghiChu
    ): void {
        $stmtDebt = $this->conn->prepare(
            "SELECT id, so_tien, han_thanh_toan FROM cong_no_hdv WHERE id = ? LIMIT 1"
        );
        $stmtDebt->execute([$congNoId]);
        $debtRow = $stmtDebt->fetch(PDO::FETCH_ASSOC);

        if (!$debtRow) {
            throw new RuntimeException('Khong tim thay cong no can thanh toan.');
        }

        $stmtPaid = $this->conn->prepare(
            "SELECT COALESCE(SUM(so_tien),0) FROM lich_su_thanh_toan_hdv WHERE cong_no_hdv_id = ?"
        );
        $stmtPaid->execute([$congNoId]);
        $daThanhToan = (float)$stmtPaid->fetchColumn();
        $conLai      = max(0.0, (float)$debtRow['so_tien'] - $daThanhToan);

        if ($conLai <= 0) {
            throw new RuntimeException('Cong no nay da duoc thanh toan du.');
        }
        if ($soTienThanhToan > $conLai) {
            throw new RuntimeException(
                'So tien thanh toan vuot qua cong no con lai (' . number_format($conLai) . ' VND).'
            );
        }

        $this->conn->beginTransaction();

        $stmtInsert = $this->conn->prepare(
            "INSERT INTO lich_su_thanh_toan_hdv
                 (cong_no_hdv_id, ngay_thanh_toan, so_tien, phuong_thuc, ghi_chu)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmtInsert->execute([$congNoId, $ngayThanhToan, $soTienThanhToan, $phuongThuc, $ghiChu]);

        $conLaiSauThanhToan = max(0.0, $conLai - $soTienThanhToan);
        $trangThaiMoi = 'ChoDuyet';
        if ($conLaiSauThanhToan <= 0.0001) {
            $trangThaiMoi = 'DaThanhToan';
        } elseif (!empty($debtRow['han_thanh_toan']) && $debtRow['han_thanh_toan'] < date('Y-m-d')) {
            $trangThaiMoi = 'QuaHan';
        }

        $stmtUpdate = $this->conn->prepare(
            "UPDATE cong_no_hdv SET trang_thai = ? WHERE id = ?"
        );
        $stmtUpdate->execute([$trangThaiMoi, $congNoId]);

        $this->conn->commit();
    }

    /**
     * Truy vấn + tính toán danh sách công nợ HDV với filter.
     * Trả về ['congNoHDV', 'summary', 'historyMap', 'hdvOptions', 'tourOptions']
     */
    public function buildCongNoHdvData(array $filters): array
    {
        $where  = [];
        $params = [];

        if (($filters['hdv_id'] ?? 0) > 0) {
            $where[]  = 'c.hdv_id = ?';
            $params[] = $filters['hdv_id'];
        }
        if (($filters['tour_id'] ?? 0) > 0) {
            $where[]  = 'c.tour_id = ?';
            $params[] = $filters['tour_id'];
        }
        if (($filters['keyword'] ?? '') !== '') {
            $where[]  = '(nd.ho_ten LIKE ? OR t.ten_tour LIKE ? OR c.ghi_chu LIKE ?)';
            $kw       = '%' . $filters['keyword'] . '%';
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

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $statusFilter = $filters['status'] ?? '';
        $today        = date('Y-m-d');
        $congNoHDV    = [];
        $summary      = [
            'tong_goc'          => 0.0,
            'tong_da_thanh_toan'=> 0.0,
            'tong_con_lai'      => 0.0,
            'so_cong_no'        => 0,
            'so_qua_han'        => 0,
            'so_da_thanh_toan'  => 0,
        ];

        foreach ($rows as $row) {
            $tongGoc         = (float)($row['so_tien'] ?? 0);
            $tongDaThanhToan = (float)($row['tong_da_thanh_toan'] ?? 0);
            $conLai          = max(0.0, $tongGoc - $tongDaThanhToan);
            $isQuaHan        = ($conLai > 0) && !empty($row['han_thanh_toan']) && ($row['han_thanh_toan'] < $today);
            $trangThaiHienThi = $conLai <= 0 ? 'DaThanhToan' : ($isQuaHan ? 'QuaHan' : 'ConNo');

            if ($statusFilter !== '' && $statusFilter !== $trangThaiHienThi) {
                continue;
            }

            $summary['tong_goc']           += $tongGoc;
            $summary['tong_da_thanh_toan'] += $tongDaThanhToan;
            $summary['tong_con_lai']        += $conLai;
            $summary['so_cong_no']++;
            if ($isQuaHan) {
                $summary['so_qua_han']++;
            }
            if ($conLai <= 0) {
                $summary['so_da_thanh_toan']++;
            }

            $congNoHDV[] = [
                'id'                  => (int)$row['id'],
                'hdv_id'              => (int)$row['hdv_id'],
                'tour_id'             => (int)$row['tour_id'],
                'ten_hdv'             => $row['ten_hdv'],
                'ten_tour'            => $row['ten_tour'],
                'loai_cong_no'        => $row['loai_cong_no'],
                'han_thanh_toan'      => $row['han_thanh_toan'],
                'ghi_chu'             => $row['ghi_chu'],
                'tong_goc'            => $tongGoc,
                'tong_da_thanh_toan'  => $tongDaThanhToan,
                'con_lai'             => $conLai,
                'so_lan_thanh_toan'   => (int)($row['so_lan_thanh_toan'] ?? 0),
                'lan_thanh_toan_cuoi' => $row['lan_thanh_toan_cuoi'],
                'trang_thai_hien_thi' => $trangThaiHienThi,
            ];
        }

        // Batch lịch sử thanh toán
        $historyMap = [];
        if (!empty($congNoHDV)) {
            $ids          = array_column($congNoHDV, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmtLs       = $this->conn->prepare(
                "SELECT cong_no_hdv_id, ngay_thanh_toan, so_tien, phuong_thuc, ghi_chu
                 FROM lich_su_thanh_toan_hdv
                 WHERE cong_no_hdv_id IN ($placeholders)
                 ORDER BY ngay_thanh_toan DESC, id DESC"
            );
            $stmtLs->execute($ids);
            foreach ($stmtLs->fetchAll(PDO::FETCH_ASSOC) as $ls) {
                $historyMap[(int)$ls['cong_no_hdv_id']][] = $ls;
            }
        }

        foreach ($congNoHDV as &$item) {
            $item['lich_su_thanh_toan'] = $historyMap[(int)$item['id']] ?? [];
        }
        unset($item);

        // Dropdown options
        $hdvStmt = $this->conn->prepare(
            "SELECT ns.nhan_su_id AS hdv_id, nd.ho_ten AS ten_hdv
             FROM nhan_su ns
             JOIN nguoi_dung nd ON nd.id = ns.nguoi_dung_id
             ORDER BY nd.ho_ten ASC"
        );
        $hdvStmt->execute();

        $tourStmt = $this->conn->prepare("SELECT tour_id, ten_tour FROM tour ORDER BY ten_tour ASC");
        $tourStmt->execute();

        return [
            'congNoHDV'   => $congNoHDV,
            'summary'     => $summary,
            'historyMap'  => $historyMap,
            'hdvOptions'  => $hdvStmt->fetchAll(PDO::FETCH_ASSOC),
            'tourOptions' => $tourStmt->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    // ==================== NHAC HAN CONG NO ====================

    /**
     * Nhắc hạn công nợ khách hàng + NCC + HDV trong 3 ngày tới.
     */
    public function buildNhacHanCongNo(): array
    {
        $today         = date('Y-m-d');
        $nhacHanCongNo = [];

        // Khách hàng
        $stmtBookings = $this->conn->prepare(
            "SELECT b.booking_id, b.khach_hang_id, b.tour_id, b.han_thanh_toan,
                    nd.ho_ten, t.ten_tour
             FROM booking b
             LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
             LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
             LEFT JOIN tour t ON b.tour_id = t.tour_id
             WHERE b.han_thanh_toan IS NOT NULL
               AND b.han_thanh_toan <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
               AND (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')"
        );
        $stmtBookings->execute();
        foreach ($stmtBookings->fetchAll(PDO::FETCH_ASSOC) as $booking) {
            $han = $booking['han_thanh_toan'] ?? null;
            if (empty($han)) {
                continue;
            }
            $isQuaHan  = $today > $han;
            $isSapHan  = !$isQuaHan && (strtotime($han) - strtotime($today) <= 3 * 86400);
            $nhacHanCongNo[] = [
                'doi_tuong' => 'Khách hàng ' . ($booking['ho_ten'] ?? $booking['khach_hang_id']),
                'noi_dung'  => 'Đến hạn thanh toán hợp đồng tour ' . ($booking['ten_tour'] ?? $booking['tour_id']),
                'han'        => $han,
                'is_qua_han' => $isQuaHan,
                'is_sap_han' => $isSapHan,
            ];
        }

        // Nhà cung cấp
        $stmtNCC = $this->conn->prepare(
            "SELECT c.*, ncc.ten_don_vi
             FROM cong_no_nha_cung_cap c
             JOIN nha_cung_cap ncc ON c.nha_cung_cap_id = ncc.id_nha_cung_cap
             WHERE c.han_thanh_toan IS NOT NULL
               AND c.han_thanh_toan <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)"
        );
        $stmtNCC->execute();
        foreach ($stmtNCC->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (empty($row['han_thanh_toan'])) {
                continue;
            }
            $isQuaHan = $today > $row['han_thanh_toan'];
            $isSapHan = !$isQuaHan && (strtotime($row['han_thanh_toan']) - strtotime($today) <= 3 * 86400);
            if ($isQuaHan || $isSapHan) {
                $nhacHanCongNo[] = [
                    'doi_tuong'  => 'Nhà cung cấp ' . $row['ten_don_vi'],
                    'noi_dung'   => 'Đến hạn thanh toán dịch vụ: ' . ($row['ghi_chu'] ?? ''),
                    'han'        => $row['han_thanh_toan'],
                    'is_qua_han' => $isQuaHan,
                    'is_sap_han' => $isSapHan,
                ];
            }
        }

        // HDV
        $stmtHDV = $this->conn->prepare(
            "SELECT c.*, nd.ho_ten
             FROM cong_no_hdv c
             JOIN nhan_su h ON c.hdv_id = h.nhan_su_id
             JOIN nguoi_dung nd ON h.nguoi_dung_id = nd.id
             WHERE c.han_thanh_toan IS NOT NULL
               AND c.han_thanh_toan <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)"
        );
        $stmtHDV->execute();
        foreach ($stmtHDV->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (empty($row['han_thanh_toan'])) {
                continue;
            }
            $isQuaHan = $today > $row['han_thanh_toan'];
            $isSapHan = !$isQuaHan && (strtotime($row['han_thanh_toan']) - strtotime($today) <= 3 * 86400);
            if ($isQuaHan || $isSapHan) {
                $nhacHanCongNo[] = [
                    'doi_tuong'  => 'HDV ' . $row['ho_ten'],
                    'noi_dung'   => 'Đến hạn thanh toán phí tour: ' . ($row['ghi_chu'] ?? ''),
                    'han'        => $row['han_thanh_toan'],
                    'is_qua_han' => $isQuaHan,
                    'is_sap_han' => $isSapHan,
                ];
            }
        }

        return $nhacHanCongNo;
    }

    // ==================== CONG NO KHACH HANG ====================

    /**
     * Trả về danh sách công nợ khách hàng với lịch sử thanh toán đã batch.
     */
    public function buildCongNoKhachHang(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT b.booking_id, b.khach_hang_id, b.tour_id, b.tong_tien,
                    nd.ho_ten AS ten_khach_hang, nd.email, nd.so_dien_thoai,
                    t.ten_tour
             FROM booking b
             LEFT JOIN khach_hang kh ON b.khach_hang_id = kh.khach_hang_id
             LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
             LEFT JOIN tour t ON b.tour_id = t.tour_id
             WHERE (b.trang_thai IS NULL OR b.trang_thai <> 'DaHuy')
             ORDER BY b.ngay_dat DESC, b.booking_id DESC"
        );
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $historyByBooking = [];
        $paidByBooking    = [];

        if (!empty($bookings)) {
            $bookingIds   = array_values(array_filter(array_map(
                static fn($r) => (int)($r['booking_id'] ?? 0), $bookings
            ), static fn($id) => $id > 0));

            if (!empty($bookingIds)) {
                $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));
                $stmtHistory  = $this->conn->prepare(
                    "SELECT gdtc.booking_id, gdtc.ngay_giao_dich AS ngay, gdtc.so_tien
                     FROM giao_dich_tai_chinh gdtc
                     WHERE gdtc.booking_id IN ($placeholders) AND gdtc.loai = 'Thu'
                     ORDER BY gdtc.booking_id ASC, gdtc.ngay_giao_dich ASC"
                );
                $stmtHistory->execute($bookingIds);
                foreach ($stmtHistory->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $bid = (int)($row['booking_id'] ?? 0);
                    if ($bid <= 0) {
                        continue;
                    }
                    $historyByBooking[$bid][]  = ['ngay' => $row['ngay'], 'so_tien' => (float)($row['so_tien'] ?? 0)];
                    $paidByBooking[$bid]        = (float)($paidByBooking[$bid] ?? 0) + (float)($row['so_tien'] ?? 0);
                }
            }
        }

        $result = [];
        foreach ($bookings as $booking) {
            $bookingId   = (int)($booking['booking_id'] ?? 0);
            $tongTien    = (float)($booking['tong_tien'] ?? 0);
            $daThanhToan = (float)($paidByBooking[$bookingId] ?? 0);
            $result[]    = [
                'khach_hang_id'      => (int)($booking['khach_hang_id'] ?? 0),
                'ten_khach_hang'     => $booking['ten_khach_hang'] ?? 'N/A',
                'email'              => $booking['email'] ?? '',
                'so_dien_thoai'      => $booking['so_dien_thoai'] ?? '',
                'ten_tour'           => $booking['ten_tour'] ?? 'N/A',
                'cong_no'            => max(0.0, $tongTien - $daThanhToan),
                'lich_su_thanh_toan' => $historyByBooking[$bookingId] ?? [],
            ];
        }

        return $result;
    }

    // ==================== CONG NO NHA CUNG CAP ====================

    /**
     * Trả về danh sách công nợ nhà cung cấp với lịch sử thanh toán đã batch.
     */
    public function buildCongNoNhaCungCap(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*, ncc.ten_don_vi
             FROM cong_no_nha_cung_cap c
             JOIN nha_cung_cap ncc ON c.nha_cung_cap_id = ncc.id_nha_cung_cap"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lichSuByCongNoId = [];
        if (!empty($rows)) {
            $congNoIds    = array_values(array_filter(array_map(
                static fn($r) => (int)($r['id'] ?? 0), $rows
            ), static fn($id) => $id > 0));

            if (!empty($congNoIds)) {
                $placeholders = implode(',', array_fill(0, count($congNoIds), '?'));
                $stmtLichSu   = $this->conn->prepare(
                    "SELECT cong_no_ncc_id,
                            ngay_thanh_toan AS ngay,
                            so_tien_thanh_toan AS so_tien
                     FROM lich_su_thanh_toan_ncc
                     WHERE cong_no_ncc_id IN ($placeholders)
                     ORDER BY cong_no_ncc_id ASC, ngay_thanh_toan ASC"
                );
                $stmtLichSu->execute($congNoIds);
                foreach ($stmtLichSu->fetchAll(PDO::FETCH_ASSOC) as $item) {
                    $cid = (int)($item['cong_no_ncc_id'] ?? 0);
                    if ($cid <= 0) {
                        continue;
                    }
                    $lichSuByCongNoId[$cid][] = ['ngay' => $item['ngay'], 'so_tien' => (float)($item['so_tien'] ?? 0)];
                }
            }
        }

        $result = [];
        foreach ($rows as $row) {
            $cid      = (int)($row['id'] ?? 0);
            $result[] = [
                'ten_nha_cung_cap'   => $row['ten_don_vi'],
                'ten_dich_vu'        => $row['ghi_chu'] ?? '',
                'cong_no'            => $row['so_tien'],
                'lich_su_thanh_toan' => $lichSuByCongNoId[$cid] ?? [],
            ];
        }

        return $result;
    }

    // ==================== EXPORT ====================

    /**
     * Xây payload export (headers + rows) cho 1 loại báo cáo.
     * $params là mảng key/value (thường truyền $_GET vào).
     */
    public function buildExportPayload(string $loaiBaoCao, array $params = []): array
    {
        $tuNgay = $this->normalizeDate($params['tu_ngay'] ?? date('Y-m-01'));
        $denNgay = $this->normalizeDate($params['den_ngay'] ?? date('Y-m-t'));

        switch ($loaiBaoCao) {
            case 'lai_lo_tour':
                $rows = [];
                foreach ($this->buildTourFinancialRows($tuNgay, $denNgay) as $tour) {
                    $doanhThu  = (float)($tour['tong_thu'] ?? 0);
                    $chiPhi    = (float)($tour['tong_chi_thuc_te'] ?? 0);
                    $loiNhuan  = $doanhThu - $chiPhi;
                    $tySuat    = $doanhThu > 0 ? ($loiNhuan / $doanhThu * 100) : 0;
                    $rows[]    = [
                        (string)($tour['ten_tour'] ?? ''),
                        $this->formatCurrency($doanhThu),
                        $this->formatCurrency($chiPhi),
                        $this->formatCurrency($loiNhuan),
                        number_format($tySuat, 2) . '%',
                    ];
                }
                usort($rows, static function ($l, $r) {
                    $lv = (float)str_replace([' VND', ',', ' '], '', (string)$l[3]);
                    $rv = (float)str_replace([' VND', ',', ' '], '', (string)$r[3]);
                    return $rv <=> $lv;
                });
                return [
                    'title'   => 'Bao cao lai lo tung tour',
                    'headers' => ['Tour', 'Doanh thu', 'Chi phi', 'Loi nhuan', 'Ty suat'],
                    'rows'    => $rows,
                ];

            case 'thu_chi_tour':
                $rows = [];
                foreach ($this->buildTourFinancialRows($tuNgay, $denNgay) as $stat) {
                    $tongThu = (float)($stat['tong_thu'] ?? 0);
                    $tongChi = (float)($stat['tong_chi_thuc_te'] ?? 0);
                    $rows[]  = [
                        (string)($stat['ten_tour'] ?? ''),
                        (string)($stat['loai_tour'] ?? ''),
                        $this->formatCurrency($tongThu),
                        $this->formatCurrency($tongChi),
                        $this->formatCurrency($tongThu - $tongChi),
                    ];
                }
                return [
                    'title'   => 'Bao cao thu chi tung tour',
                    'headers' => ['Ten tour', 'Loai tour', 'Tong thu', 'Tong chi', 'Loi nhuan'],
                    'rows'    => $rows,
                ];

            case 'giao_dich':
            default:
                $filters = array_filter([
                    'loai'           => $params['loai_giao_dich_chinh'] ?? ($params['loai'] ?? ''),
                    'loai_giao_dich' => $params['loai_giao_dich'] ?? '',
                    'tour_id'        => $params['tour_id'] ?? '',
                    'khach_hang_id'  => $params['khach_hang_id'] ?? '',
                    'tu_ngay'        => $params['tu_ngay'] ?? '',
                    'den_ngay'       => $params['den_ngay'] ?? '',
                    'keyword'        => $params['keyword'] ?? '',
                ], static fn($v) => $v !== '' && $v !== null);

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
                    'title'   => 'Lich su giao dich noi bo',
                    'headers' => ['Ngay giao dich', 'Loai', 'Loai giao dich', 'So tien', 'Mo ta', 'Nguoi thuc hien'],
                    'rows'    => $rows,
                ];
        }
    }

    public function exportFinancialReportExcel(
        string $title,
        array  $headers,
        array  $rows,
        string $fileName
    ): void {
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        echo "\xEF\xBB\xBF";
        echo '<html><head><meta charset="UTF-8"><title>'
            . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
            . '</title></head><body>';
        echo '<h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';
        echo '<table border="1" cellspacing="0" cellpadding="6"><thead><tr>';
        foreach ($headers as $h) {
            echo '<th>' . htmlspecialchars((string)$h, ENT_QUOTES, 'UTF-8') . '</th>';
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

    public function exportFinancialReportPdf(
        string $title,
        array  $headers,
        array  $rows,
        string $fileName,
        string $backUrl = ''
    ): void {
        if (!class_exists(Dompdf::class)) {
            $_SESSION['error'] = 'Khong tim thay thu vien PDF. Vui long kiem tra composer install.';
            header('Location: ' . ($backUrl ?: 'index.php?act=admin/baoCaoTaiChinh'));
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

    public function renderFinancialReportPdfHtml(string $title, array $headers, array $rows): string
    {
        $thead = '';
        foreach ($headers as $h) {
            $thead .= '<th>' . htmlspecialchars((string)$h, ENT_QUOTES, 'UTF-8') . '</th>';
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
            . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
            . '</title><style>'
            . 'body{font-family:"DejaVu Sans",sans-serif;font-size:11px;color:#111;}'
            . 'h1{font-size:18px;margin:0 0 8px 0;}'
            . '.meta{margin:0 0 12px 0;color:#555;}'
            . 'table{width:100%;border-collapse:collapse;}'
            . 'th,td{border:1px solid #ccc;padding:6px;vertical-align:top;}'
            . 'th{background:#f3f3f3;text-align:left;}'
            . '</style></head><body>'
            . '<h1>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
            . '<div class="meta">Ngay xuat: ' . date('d/m/Y H:i') . '</div>'
            . '<table><thead><tr>' . $thead . '</tr></thead><tbody>' . $tbody . '</tbody></table>'
            . '</body></html>';
    }

    public function exportCongNoHdvCsv(array $rows): void
    {
        $fileName = 'cong-no-hdv-' . date('Ymd-His') . '.csv';
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        if ($out === false) {
            exit;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'ID', 'HDV', 'Tour', 'Loai cong no',
            'Tong goc', 'Da thanh toan', 'Con lai',
            'Han thanh toan', 'Trang thai',
            'So lan thanh toan', 'Lan thanh toan cuoi', 'Ghi chu',
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
}
