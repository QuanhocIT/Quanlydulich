<?php

/**
 * Class HDVManagement
 * Quản lý HDV nâng cao - SỬ DỤNG DATABASE HIỆN CÓ
 * KHÔNG TẠO BẢNG MỚI - TẬN DỤNG: lich_khoi_hanh, phan_hoi_danh_gia, nhat_ky_tour
 */
class HDVManagement
{
    public $conn;

    public function __construct()
    {
        $this->conn = connectDB();
    }

    // ==================== QUẢN LÝ HDV ====================
    
    /**
     * Lấy tất cả HDV với thông tin đầy đủ
     * Tự động phân loại dựa trên kinh nghiệm và ngôn ngữ
     */
    public function getAllHDV()
    {
        $sql = "SELECT 
                    ns.*, 
                    nd.ho_ten, 
                    nd.email, 
                    nd.so_dien_thoai, 
                    nd.ten_dang_nhap,
                    nd.trang_thai,
                    CASE 
                        WHEN ns.ngon_ngu LIKE '%Anh%' OR ns.ngon_ngu LIKE '%Nhật%' OR ns.ngon_ngu LIKE '%Hàn%' THEN 'QuocTe'
                        WHEN ns.kinh_nghiem LIKE '%chuyên%' OR ns.kinh_nghiem LIKE '%tuyến%' THEN 'ChuyenTuyen'
                        WHEN ns.kinh_nghiem LIKE '%đoàn%' THEN 'ChuyenDoan'
                        ELSE 'NoiDia'
                    END as loai_hdv,
                    COALESCE(
                        (SELECT AVG(diem) FROM phan_hoi_danh_gia phd 
                         INNER JOIN lich_khoi_hanh lkh ON phd.tour_id = lkh.tour_id 
                         WHERE lkh.hdv_id = ns.nhan_su_id AND phd.loai = 'Tour'), 
                        0
                    ) as danh_gia_tb,
                    COALESCE(
                        (SELECT COUNT(DISTINCT tour_id) FROM lich_khoi_hanh WHERE hdv_id = ns.nhan_su_id), 
                        0
                    ) as so_tour_da_dan,
                    CASE
                        WHEN EXISTS (
                            SELECT 1 FROM lich_khoi_hanh 
                            WHERE hdv_id = ns.nhan_su_id 
                            AND trang_thai = 'DangChay'
                            AND CURDATE() BETWEEN ngay_khoi_hanh AND ngay_ket_thuc
                        ) THEN 'DangBan'
                        WHEN nd.trang_thai = 'BiKhoa' THEN 'TamNghi'
                        ELSE 'SanSang'
                    END as trang_thai_lam_viec,
                    CASE 
                        WHEN ns.kinh_nghiem LIKE '%Miền Bắc%' THEN 'Miền Bắc'
                        WHEN ns.kinh_nghiem LIKE '%Miền Trung%' THEN 'Miền Trung'
                        WHEN ns.kinh_nghiem LIKE '%Miền Nam%' THEN 'Miền Nam'
                        WHEN ns.kinh_nghiem LIKE '%Đông Nam Á%' THEN 'Đông Nam Á'
                        ELSE NULL
                    END as chuyen_tuyen
                FROM nhan_su ns
                INNER JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE ns.vai_tro = 'HDV'
                ORDER BY nd.ho_ten ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy HDV đang sẵn sàng (không có lịch trùng)
     */
    public function getHDVSanSang($ngay_bat_dau = null, $ngay_ket_thuc = null)
    {
        if (!$ngay_bat_dau) $ngay_bat_dau = date('Y-m-d');
        if (!$ngay_ket_thuc) $ngay_ket_thuc = $ngay_bat_dau;

        $sql = "SELECT 
                    ns.*, 
                    nd.ho_ten, 
                    nd.email, 
                    nd.so_dien_thoai,
                    COALESCE((SELECT AVG(diem) FROM phan_hoi_danh_gia phd 
                              INNER JOIN lich_khoi_hanh lkh ON phd.tour_id = lkh.tour_id 
                              WHERE lkh.hdv_id = ns.nhan_su_id), 0) as danh_gia_tb,
                    COALESCE((SELECT COUNT(DISTINCT tour_id) FROM lich_khoi_hanh 
                              WHERE hdv_id = ns.nhan_su_id), 0) as so_tour_da_dan
                FROM nhan_su ns
                INNER JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE ns.vai_tro = 'HDV' 
                  AND nd.trang_thai = 'HoatDong'
                  AND ns.nhan_su_id NOT IN (
                      SELECT hdv_id 
                      FROM lich_khoi_hanh 
                      WHERE hdv_id IS NOT NULL
                        AND (
                            (ngay_khoi_hanh <= ? AND ngay_ket_thuc >= ?)
                            OR (ngay_khoi_hanh <= ? AND ngay_ket_thuc >= ?)
                            OR (ngay_khoi_hanh >= ? AND ngay_ket_thuc <= ?)
                        )
                  )
                ORDER BY danh_gia_tb DESC, so_tour_da_dan DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $ngay_bat_dau, $ngay_bat_dau,
            $ngay_ket_thuc, $ngay_ket_thuc,
            $ngay_bat_dau, $ngay_ket_thuc
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Thống kê tổng quan
     */
    public function getThongKeTongQuan()
    {
        $stats = [
            'san_sang' => 0,
            'dang_ban' => 0,
            'nghi_phep' => 0,
            'tour_thang' => 0
        ];

        // HDV sẵn sàng
        $sql = "SELECT COUNT(*) as count 
                FROM nhan_su ns
                INNER JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE ns.vai_tro = 'HDV' 
                  AND nd.trang_thai = 'HoatDong'
                  AND ns.nhan_su_id NOT IN (
                      SELECT hdv_id FROM lich_khoi_hanh 
                      WHERE hdv_id IS NOT NULL
                        AND trang_thai = 'DangChay'
                        AND CURDATE() BETWEEN ngay_khoi_hanh AND ngay_ket_thuc
                  )";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['san_sang'] = $row['count'] ?? 0;

        // HDV đang bận
        $sql = "SELECT COUNT(DISTINCT hdv_id) as count 
                FROM lich_khoi_hanh 
                WHERE hdv_id IS NOT NULL
                  AND trang_thai = 'DangChay'
                  AND CURDATE() BETWEEN ngay_khoi_hanh AND ngay_ket_thuc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['dang_ban'] = $row['count'] ?? 0;

        // HDV nghỉ/tạm ngưng
        $sql = "SELECT COUNT(*) as count 
                FROM nhan_su ns
                INNER JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE ns.vai_tro = 'HDV' AND nd.trang_thai = 'BiKhoa'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['nghi_phep'] = $row['count'] ?? 0;

        // Tour tháng này
        $sql = "SELECT COUNT(*) as count 
                FROM lich_khoi_hanh 
                WHERE MONTH(ngay_khoi_hanh) = MONTH(CURDATE())
                  AND YEAR(ngay_khoi_hanh) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $stats['tour_thang'] = $row['count'] ?? 0;

        return $stats;
    }

    // ==================== LỊCH LÀM VIỆC ====================

    /**
     * Phân công HDV cho tour - GHI VÀO lich_khoi_hanh
     */
    public function phanCongHDV($data)
    {
        // Kiểm tra trùng lịch
        if ($this->kiemTraTrungLich($data['hdv_id'], $data['ngay_khoi_hanh'], $data['ngay_ket_thuc'])) {
            return ['success' => false, 'message' => 'HDV đã có lịch trong khoảng thời gian này!'];
        }

        $sql = "INSERT INTO lich_khoi_hanh (tour_id, ngay_khoi_hanh, ngay_ket_thuc, diem_tap_trung, hdv_id, trang_thai)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['tour_id'],
            $data['ngay_khoi_hanh'],
            $data['ngay_ket_thuc'],
            $data['diem_tap_trung'] ?? '',
            $data['hdv_id'],
            $data['trang_thai'] ?? 'SapKhoiHanh'
        ]);

        return [
            'success' => $result, 
            'message' => $result ? 'Phân công HDV thành công!' : 'Phân công thất bại!'
        ];
    }

    /**
     * Kiểm tra trùng lịch
     */
    private function kiemTraTrungLich($hdv_id, $ngay_khoi_hanh, $ngay_ket_thuc, $exclude_id = null)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM lich_khoi_hanh 
                WHERE hdv_id = ? 
                  AND (
                      (ngay_khoi_hanh <= ? AND ngay_ket_thuc >= ?)
                      OR (ngay_khoi_hanh <= ? AND ngay_ket_thuc >= ?)
                      OR (ngay_khoi_hanh >= ? AND ngay_ket_thuc <= ?)
                  )";
        
        $params = [
            $hdv_id,
            $ngay_khoi_hanh, $ngay_khoi_hanh,
            $ngay_ket_thuc, $ngay_ket_thuc,
            $ngay_khoi_hanh, $ngay_ket_thuc
        ];

        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        
        return $row['count'] > 0;
    }

    /**
     * Lấy lịch làm việc (cho FullCalendar)
     */
    public function getLichLamViec($hdv_id = null, $start = null, $end = null)
    {
        $sql = "SELECT lkh.*, t.ten_tour, ns.nhan_su_id, nd.ho_ten
                FROM lich_khoi_hanh lkh
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                LEFT JOIN nhan_su ns ON lkh.hdv_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE 1=1";
        
        $params = [];
        
        if ($hdv_id) {
            $sql .= " AND lkh.hdv_id = ?";
            $params[] = $hdv_id;
        }
        
        if ($start) {
            $sql .= " AND lkh.ngay_ket_thuc >= ?";
            $params[] = $start;
        }
        
        if ($end) {
            $sql .= " AND lkh.ngay_khoi_hanh <= ?";
            $params[] = $end;
        }
        
        $sql .= " ORDER BY lkh.ngay_khoi_hanh ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Format cho FullCalendar
        $events = [];
        foreach ($rows as $row) {
            $color = '#007bff';
            switch ($row['trang_thai']) {
                case 'SapKhoiHanh':
                    $color = '#17a2b8';
                    break;
                case 'DangChay':
                    $color = '#28a745';
                    break;
                case 'HoanThanh':
                    $color = '#6c757d';
                    break;
            }

            $title = ($row['ho_ten'] ?? 'HDV') . ': ' . ($row['ten_tour'] ?? 'Tour');

            $events[] = [
                'id' => $row['id'],
                'title' => $title,
                'start' => $row['ngay_khoi_hanh'],
                'end' => date('Y-m-d', strtotime($row['ngay_ket_thuc'] . ' +1 day')),
                'color' => $color,
                'extendedProps' => [
                    'hdv_id' => $row['hdv_id'],
                    'tour_id' => $row['tour_id'],
                    'diem_tap_trung' => $row['diem_tap_trung'],
                    'trang_thai' => $row['trang_thai']
                ]
            ];
        }

        return $events;
    }

    // ==================== BÁO CÁO HIỆU SUẤT ====================

    /**
     * Lấy báo cáo hiệu suất HDV (tất cả hoặc 1 HDV cụ thể)
     */
    public function getBaoCaoHieuSuat($hdv_id = null)
    {
        $where = $hdv_id ? "WHERE lkh.hdv_id = ?" : "";
        
        $sql = "SELECT 
                    ns.nhan_su_id,
                    nd.ho_ten,
                    CASE 
                        WHEN ns.ngon_ngu LIKE '%Anh%' OR ns.ngon_ngu LIKE '%Nhật%' THEN 'QuocTe'
                        WHEN ns.kinh_nghiem LIKE '%chuyên%' THEN 'ChuyenTuyen'
                        ELSE 'NoiDia'
                    END as loai_hdv,
                    COUNT(DISTINCT lkh.tour_id) as tong_tour,
                    COALESCE(AVG(phd.diem), 0) as diem_tb,
                    SUM(CASE WHEN lkh.trang_thai = 'HoanThanh' THEN 1 ELSE 0 END) as tour_hoan_thanh,
                    MAX(lkh.ngay_ket_thuc) as tour_gan_nhat
                FROM nhan_su ns
                INNER JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                LEFT JOIN lich_khoi_hanh lkh ON ns.nhan_su_id = lkh.hdv_id
                LEFT JOIN phan_hoi_danh_gia phd ON lkh.tour_id = phd.tour_id AND phd.loai = 'Tour'
                $where
                AND ns.vai_tro = 'HDV'
                GROUP BY ns.nhan_su_id, nd.ho_ten
                ORDER BY diem_tb DESC, tong_tour DESC";
        
        $stmt = $this->conn->prepare($sql);
        if ($hdv_id) {
            $stmt->execute([$hdv_id]);
            return $stmt->fetch();
        } else {
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    /**
     * Hiệu suất HDV theo tháng
     */

    // ==================== ĐÁNH GIÁ ====================

    /**
     * Lấy đánh giá của HDV (từ phan_hoi_danh_gia)
     */
    public function getDanhGiaByHDV($nhan_su_id, $limit = 10)
    {
        $sql = "SELECT phd.*, t.ten_tour, nd.ho_ten as ten_khach
                FROM phan_hoi_danh_gia phd
                INNER JOIN tour t ON phd.tour_id = t.tour_id
                INNER JOIN lich_khoi_hanh lkh ON t.tour_id = lkh.tour_id
                LEFT JOIN nguoi_dung nd ON phd.nguoi_dung_id = nd.id
                WHERE lkh.hdv_id = ? AND phd.loai = 'Tour'
                ORDER BY phd.ngay_danh_gia DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhan_su_id, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Thêm nhật ký
     */
    public function themNhatKy($data)
    {
        $sql = "INSERT INTO nhat_ky_tour (tour_id, nhan_su_id, noi_dung, ngay_ghi)
                VALUES (?, ?, ?, CURDATE())";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tour_id'],
            $data['nhan_su_id'],
            $data['noi_dung']
        ]);
    }

    // ==================== GỢI Ý HDV ====================

    /**
     * Gợi ý HDV phù hợp cho tour
     */
    public function goiYHDV($tour_id, $ngay_khoi_hanh, $ngay_ket_thuc)
    {
        $sql = "SELECT 
                    ns.*,
                    nd.ho_ten,
                    nd.email,
                    COALESCE(AVG(phd.diem), 0) as diem_tb,
                    COUNT(DISTINCT lkh.id) as so_tour_da_dan
                FROM nhan_su ns
                INNER JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                LEFT JOIN lich_khoi_hanh lkh ON ns.nhan_su_id = lkh.hdv_id
                LEFT JOIN phan_hoi_danh_gia phd ON lkh.tour_id = phd.tour_id
                WHERE ns.vai_tro = 'HDV'
                  AND nd.trang_thai = 'HoatDong'
                  AND ns.nhan_su_id NOT IN (
                      SELECT hdv_id FROM lich_khoi_hanh
                      WHERE hdv_id IS NOT NULL
                        AND (
                            (ngay_khoi_hanh <= ? AND ngay_ket_thuc >= ?)
                            OR (ngay_khoi_hanh <= ? AND ngay_ket_thuc >= ?)
                            OR (ngay_khoi_hanh >= ? AND ngay_ket_thuc <= ?)
                        )
                  )
                GROUP BY ns.nhan_su_id
                ORDER BY diem_tb DESC, so_tour_da_dan DESC
                LIMIT 5";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $ngay_khoi_hanh, $ngay_khoi_hanh,
            $ngay_ket_thuc, $ngay_ket_thuc,
            $ngay_khoi_hanh, $ngay_ket_thuc
        ]);
        return $stmt->fetchAll();
    }

    // ==================== THÔNG BÁO (TẠM LƯU TRONG SESSION) ====================

    /**
     * Lấy thông báo (tạm thời lưu trong session, không dùng database)
     */
    public function getThongBao($nhan_su_id = null, $limit = 50)
    {
        $sql = "SELECT tb.*, nd.ho_ten
                FROM thong_bao_hdv tb
                LEFT JOIN nhan_su ns ON tb.nhan_su_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE 1=1";
        
        $params = [];
        
        if ($nhan_su_id) {
            $sql .= " AND (tb.nhan_su_id = ? OR tb.nhan_su_id IS NULL)";
            $params[] = $nhan_su_id;
        }
        
        $sql .= " ORDER BY tb.ngay_gui DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Gửi thông báo (lưu vào database)
     */
    public function guiThongBao($data)
    {
        $sql = "INSERT INTO thong_bao_hdv (nhan_su_id, loai_thong_bao, tieu_de, noi_dung, uu_tien, ngay_gui)
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nhan_su_id'], // NULL nếu gửi cho tất cả
            $data['loai_thong_bao'],
            $data['tieu_de'],
            $data['noi_dung'],
            $data['uu_tien']
        ]);
    }

    // ==================== NHẬT KÝ TOUR ====================

    /**
     * Lấy nhật ký tour của HDV
     */
    public function getNhatKyByHDV($hdv_id, $limit = 50)
    {
        $sql = "SELECT 
                    nkt.*,
                    t.ten_tour,
                    lkh.ngay_khoi_hanh,
                    lkh.ngay_ket_thuc
                FROM nhat_ky_tour nkt
                INNER JOIN tour t ON nkt.tour_id = t.tour_id
                LEFT JOIN lich_khoi_hanh lkh ON lkh.tour_id = t.tour_id AND lkh.hdv_id = ?
                WHERE nkt.nhan_su_id = ?
                ORDER BY nkt.ngay_ghi DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdv_id, $hdv_id, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy hiệu suất theo tháng (dùng cho biểu đồ)
     */
    public function getHieuSuatTheoThang($hdv_id, $so_thang = 12)
    {
        $sql = "SELECT 
                    DATE_FORMAT(lkh.ngay_khoi_hanh, '%Y-%m') as thang,
                    COUNT(DISTINCT lkh.tour_id) as so_tour,
                    AVG(phd.diem) as diem_tb
                FROM lich_khoi_hanh lkh
                LEFT JOIN phan_hoi_danh_gia phd ON phd.tour_id = lkh.tour_id AND phd.loai = 'Tour'
                WHERE lkh.hdv_id = ?
                    AND lkh.ngay_khoi_hanh >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY thang
                ORDER BY thang DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdv_id, $so_thang]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy tất cả lịch làm việc dạng bảng
     */
    public function getAllLichLamViec()
    {
        $sql = "SELECT lkh.*, t.ten_tour, ns.nhan_su_id, nd.ho_ten
                FROM lich_khoi_hanh lkh
                LEFT JOIN tour t ON lkh.tour_id = t.tour_id
                LEFT JOIN nhan_su ns ON lkh.hdv_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE lkh.hdv_id IS NOT NULL
                ORDER BY lkh.ngay_khoi_hanh DESC
                LIMIT 100";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

