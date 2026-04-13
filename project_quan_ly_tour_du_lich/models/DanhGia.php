<?php
class DanhGia {
    private $conn;
    
    public function __construct() {
        $this->conn = connectDB();
    }
    
    // Lọc đánh giá
    public function filter($filters = []) {
        $sql = "SELECT dg.*, 
                       nd.ho_ten as ten_khach_hang,
                       nd.email as email_khach_hang,
                       t.ten_tour
                FROM danh_gia dg
                LEFT JOIN khach_hang k ON dg.khach_hang_id = k.khach_hang_id
                LEFT JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                LEFT JOIN tour t ON dg.tour_id = t.tour_id
                WHERE 1=1";
        
        $params = [];
        
        // Lọc theo loại
        if (!empty($filters['loai_danh_gia'])) {
            $sql .= " AND dg.loai_danh_gia = ?";
            $params[] = $filters['loai_danh_gia'];
        }
        
        // Lọc theo điểm
        if (!empty($filters['diem_min'])) {
            $sql .= " AND dg.diem >= ?";
            $params[] = (int)$filters['diem_min'];
        }
        
        if (!empty($filters['diem_max'])) {
            $sql .= " AND dg.diem <= ?";
            $params[] = (int)$filters['diem_max'];
        }
        
        // Lọc theo ngày
        if (!empty($filters['tu_ngay'])) {
            $sql .= " AND dg.ngay_danh_gia >= ?";
            $params[] = $filters['tu_ngay'];
        }
        
        if (!empty($filters['den_ngay'])) {
            $sql .= " AND dg.ngay_danh_gia <= ?";
            $params[] = $filters['den_ngay'];
        }
        
        // Tìm kiếm
        if (!empty($filters['search'])) {
            $sql .= " AND (nd.ho_ten LIKE ? OR dg.noi_dung LIKE ? OR t.ten_tour LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY dg.ngay_danh_gia DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopReviews($minScore = 4, $limit = 3) {
        $sql = "SELECT dg.*, 
                       nd.ho_ten as ten_khach_hang,
                       nd.email as email_khach_hang,
                       t.ten_tour
                FROM danh_gia dg
                LEFT JOIN khach_hang k ON dg.khach_hang_id = k.khach_hang_id
                LEFT JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                LEFT JOIN tour t ON dg.tour_id = t.tour_id
                WHERE dg.diem >= ?
                ORDER BY dg.diem DESC, dg.ngay_danh_gia DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, (int)$minScore, PDO::PARAM_INT);
        $stmt->bindValue(2, max(1, (int)$limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Thống kê tổng quan
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as tong_danh_gia,
                    AVG(diem) as diem_trung_binh,
                    SUM(CASE WHEN diem >= 4 THEN 1 ELSE 0 END) as hai_long,
                    SUM(CASE WHEN diem <= 2 THEN 1 ELSE 0 END) as khong_hai_long,
                    COUNT(CASE WHEN loai_danh_gia = 'Tour' THEN 1 END) as danh_gia_tour,
                    COUNT(CASE WHEN loai_danh_gia = 'NhaCungCap' THEN 1 END) as danh_gia_ncc,
                    COUNT(CASE WHEN loai_danh_gia = 'NhanSu' THEN 1 END) as danh_gia_nhan_su
                FROM danh_gia";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thống kê bucket điểm đánh giá tour cho dashboard.
    public function getTourFeedbackBuckets() {
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN diem >= 5 THEN 1 ELSE 0 END), 0) AS rat_hai_long,
                    COALESCE(SUM(CASE WHEN diem = 4 THEN 1 ELSE 0 END), 0) AS hai_long,
                    COALESCE(SUM(CASE WHEN diem = 3 THEN 1 ELSE 0 END), 0) AS trung_binh,
                    COALESCE(SUM(CASE WHEN diem = 2 THEN 1 ELSE 0 END), 0) AS khong_hai_long,
                    COALESCE(SUM(CASE WHEN diem <= 1 THEN 1 ELSE 0 END), 0) AS rat_khong_hai_long
                FROM danh_gia
                WHERE loai_danh_gia = 'Tour'";
        $stmt = $this->conn->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'Rất hài lòng' => (int)($row['rat_hai_long'] ?? 0),
            'Hài lòng' => (int)($row['hai_long'] ?? 0),
            'Trung bình' => (int)($row['trung_binh'] ?? 0),
            'Không hài lòng' => (int)($row['khong_hai_long'] ?? 0),
            'Rất không hài lòng' => (int)($row['rat_khong_hai_long'] ?? 0),
        ];
    }

    public function getTourRatingMapByTourIds(array $tourIds) {
        $normalizedIds = [];
        foreach ($tourIds as $tourId) {
            $id = (int)$tourId;
            if ($id > 0) {
                $normalizedIds[$id] = $id;
            }
        }

        if (empty($normalizedIds)) {
            return [];
        }

        $ids = array_values($normalizedIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT tour_id, AVG(diem) AS diem_tb, COUNT(*) AS so_danh_gia
                FROM danh_gia
                WHERE loai_danh_gia = 'Tour'
                  AND tour_id IN ($placeholders)
                GROUP BY tour_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $ratingMap = [];
        foreach ($rows as $row) {
            $tourId = (int)($row['tour_id'] ?? 0);
            if ($tourId <= 0) {
                continue;
            }

            $ratingMap[$tourId] = [
                'diem_tb' => (float)($row['diem_tb'] ?? 0),
                'so_danh_gia' => (int)($row['so_danh_gia'] ?? 0),
            ];
        }

        return $ratingMap;
    }
    
    // Báo cáo theo tour
    public function getReportByTour($tour_id) {
        // Thông tin tour
        $sql = "SELECT t.*, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
                FROM tour t
                LEFT JOIN danh_gia dg ON t.tour_id = dg.tour_id
                WHERE t.tour_id = ?
                GROUP BY t.tour_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Danh sách đánh giá
        $sql = "SELECT dg.*, nd.ho_ten, nd.email
                FROM danh_gia dg
                JOIN khach_hang k ON dg.khach_hang_id = k.khach_hang_id
                JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                WHERE dg.tour_id = ?
                ORDER BY dg.ngay_danh_gia DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tour_id]);
        $danh_gia_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Phân tích theo tiêu chí
        $sql = "SELECT 
                    AVG(CASE WHEN tieu_chi = 'ChatLuongTour' THEN diem END) as chat_luong_tour,
                    AVG(CASE WHEN tieu_chi = 'DichVu' THEN diem END) as dich_vu,
                    AVG(CASE WHEN tieu_chi = 'HuongDanVien' THEN diem END) as huong_dan_vien,
                    AVG(CASE WHEN tieu_chi = 'GiaCa' THEN diem END) as gia_ca
                FROM danh_gia
                WHERE tour_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$tour_id]);
        $tieu_chi = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'tour' => $tour,
            'danh_gia_list' => $danh_gia_list,
            'tieu_chi' => $tieu_chi
        ];
    }
    
    // Báo cáo theo nhà cung cấp
    public function getReportByNhaCungCap($ncc_id) {
        // Thông tin nhà cung cấp
        $sql = "SELECT ncc.*, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
            FROM nha_cung_cap ncc
            LEFT JOIN danh_gia dg ON ncc.id_nha_cung_cap = dg.nha_cung_cap_id
            WHERE ncc.id_nha_cung_cap = ?
            GROUP BY ncc.id_nha_cung_cap";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ncc_id]);
        $nha_cung_cap = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Danh sách đánh giá
        $sql = "SELECT dg.*, nd.ho_ten, nd.email, t.ten_tour
                FROM danh_gia dg
                JOIN khach_hang k ON dg.khach_hang_id = k.khach_hang_id
                JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                LEFT JOIN tour t ON dg.tour_id = t.tour_id
                WHERE dg.nha_cung_cap_id = ?
                ORDER BY dg.ngay_danh_gia DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ncc_id]);
        $danh_gia_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Phân tích theo loại dịch vụ
        $sql = "SELECT loai_dich_vu, AVG(diem) as diem_tb, COUNT(*) as so_luong
                FROM danh_gia
                WHERE nha_cung_cap_id = ?
                GROUP BY loai_dich_vu";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$ncc_id]);
        $theo_dich_vu = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'nha_cung_cap' => $nha_cung_cap,
            'danh_gia_list' => $danh_gia_list,
            'theo_dich_vu' => $theo_dich_vu
        ];
    }
    
    // Báo cáo theo nhân sự
    public function getReportByNhanSu($nhan_su_id) {
        // Thông tin nhân sự
        $sql = "SELECT ns.*, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
                FROM nhan_su ns
                LEFT JOIN danh_gia dg ON ns.nhan_su_id = dg.nhan_su_id
                WHERE ns.nhan_su_id = ?
                GROUP BY ns.nhan_su_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhan_su_id]);
        $nhan_su = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Danh sách đánh giá
        $sql = "SELECT dg.*, nd.ho_ten, nd.email, t.ten_tour
                FROM danh_gia dg
                JOIN khach_hang k ON dg.khach_hang_id = k.khach_hang_id
                JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                LEFT JOIN tour t ON dg.tour_id = t.tour_id
                WHERE dg.nhan_su_id = ?
                ORDER BY dg.ngay_danh_gia DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nhan_su_id]);
        $danh_gia_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'nhan_su' => $nhan_su,
            'danh_gia_list' => $danh_gia_list
        ];
    }
    
    // Báo cáo tổng hợp
    public function getOverallReport() {
        $report = [];
        
        // Top tour tốt nhất
        $sql = "SELECT t.ten_tour, t.tour_id, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
                FROM tour t
                JOIN danh_gia dg ON t.tour_id = dg.tour_id
                WHERE dg.loai_danh_gia = 'Tour'
                GROUP BY t.tour_id
                HAVING COUNT(dg.danh_gia_id) >= 3
                ORDER BY diem_tb DESC
                LIMIT 10";
        
        $stmt = $this->conn->query($sql);
        $report['top_tour'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tour cần cải thiện
        $sql = "SELECT t.ten_tour, t.tour_id, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
                FROM tour t
                JOIN danh_gia dg ON t.tour_id = dg.tour_id
                WHERE dg.loai_danh_gia = 'Tour'
                GROUP BY t.tour_id
                HAVING COUNT(dg.danh_gia_id) >= 3
                ORDER BY diem_tb ASC
                LIMIT 10";
        
        $stmt = $this->conn->query($sql);
        $report['tour_can_cai_thien'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top nhà cung cấp
        $sql = "SELECT ncc.ten_don_vi, ncc.id_nha_cung_cap, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
            FROM danh_gia dg
            JOIN nha_cung_cap ncc ON dg.nha_cung_cap_id = ncc.id_nha_cung_cap
            WHERE dg.loai_danh_gia = 'NhaCungCap'
            GROUP BY ncc.id_nha_cung_cap
            HAVING COUNT(dg.danh_gia_id) >= 3
            ORDER BY diem_tb DESC
            LIMIT 10";
        
        $stmt = $this->conn->query($sql);
        $report['top_nha_cung_cap'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // NCC cần cải thiện
        $sql = "SELECT ncc.ten_don_vi, ncc.id_nha_cung_cap, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
            FROM danh_gia dg
            JOIN nha_cung_cap ncc ON dg.nha_cung_cap_id = ncc.id_nha_cung_cap
            WHERE dg.loai_danh_gia = 'NhaCungCap'
            GROUP BY ncc.id_nha_cung_cap
            HAVING COUNT(dg.danh_gia_id) >= 3 AND diem_tb < 3
            ORDER BY diem_tb ASC
            LIMIT 10";
        
        $stmt = $this->conn->query($sql);
        $report['ncc_can_cai_thien'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top nhân sự
        $sql = "SELECT nd.ho_ten, ns.nhan_su_id, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
            FROM nhan_su ns
            JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
            JOIN danh_gia dg ON ns.nhan_su_id = dg.nhan_su_id
            GROUP BY ns.nhan_su_id
            HAVING COUNT(dg.danh_gia_id) >= 3
            ORDER BY diem_tb DESC
            LIMIT 10";
        
        $stmt = $this->conn->query($sql);
        $report['top_nhan_su'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Nhân sự cần nhắc nhở
        $sql = "SELECT nd.ho_ten, ns.nhan_su_id, AVG(dg.diem) as diem_tb, COUNT(dg.danh_gia_id) as so_danh_gia
            FROM nhan_su ns
            JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
            JOIN danh_gia dg ON ns.nhan_su_id = dg.nhan_su_id
            GROUP BY ns.nhan_su_id
            HAVING COUNT(dg.danh_gia_id) >= 3 AND diem_tb < 3
            ORDER BY diem_tb ASC
            LIMIT 10";
        
        $stmt = $this->conn->query($sql);
        $report['nhan_su_can_nhac_nho'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Thống kê theo tháng
        $sql = "SELECT DATE_FORMAT(ngay_danh_gia, '%Y-%m') as thang, 
                       COUNT(*) as so_luong, 
                       AVG(diem) as diem_tb
                FROM danh_gia
                WHERE ngay_danh_gia >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY thang
                ORDER BY thang DESC";
        
        $stmt = $this->conn->query($sql);
        $report['theo_thang'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $report;
    }
    
    // Tìm theo ID
    public function findById($id) {
        $sql = "SELECT dg.*, 
                       nd.ho_ten as ten_khach_hang,
                       nd.email as email_khach_hang,
                       nd.so_dien_thoai as dien_thoai_khach_hang,
                       t.ten_tour
                FROM danh_gia dg
                LEFT JOIN khach_hang k ON dg.khach_hang_id = k.khach_hang_id
                LEFT JOIN nguoi_dung nd ON k.nguoi_dung_id = nd.id
                LEFT JOIN tour t ON dg.tour_id = t.tour_id
                WHERE dg.danh_gia_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Cập nhật phản hồi admin
    public function updateAdminResponse($id, $phan_hoi) {
        $sql = "UPDATE danh_gia 
                SET phan_hoi_admin = ?, ngay_phan_hoi = NOW()
                WHERE danh_gia_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$phan_hoi, $id]);
    }
    
    // Xóa đánh giá
    public function delete($id) {
        $sql = "DELETE FROM danh_gia WHERE danh_gia_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Thêm đánh giá mới (từ khách hàng)
    public function create($data) {
        $sql = "INSERT INTO danh_gia (
                    khach_hang_id, tour_id, nha_cung_cap_id, nhan_su_id,
                    loai_danh_gia, tieu_chi, loai_dich_vu,
                    diem, noi_dung, ngay_danh_gia
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['khach_hang_id'],
            $data['tour_id'] ?? null,
            $data['nha_cung_cap_id'] ?? null,
            $data['nhan_su_id'] ?? null,
            $data['loai_danh_gia'],
            $data['tieu_chi'] ?? null,
            $data['loai_dich_vu'] ?? null,
            $data['diem'],
            $data['noi_dung']
        ]);
    }

    public function existsTourReviewByKhachHang($khachHangId, $tourId) {
        $khachHangId = (int)$khachHangId;
        $tourId = (int)$tourId;
        if ($khachHangId <= 0 || $tourId <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "SELECT 1
             FROM danh_gia
             WHERE khach_hang_id = ?
               AND tour_id = ?
               AND loai_danh_gia = 'Tour'
             LIMIT 1"
        );
        $stmt->execute([$khachHangId, $tourId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getTourReviewByKhachHang($khachHangId, $tourId) {
        $khachHangId = (int)$khachHangId;
        $tourId = (int)$tourId;
        if ($khachHangId <= 0 || $tourId <= 0) {
            return null;
        }

        $stmt = $this->conn->prepare(
            "SELECT *
             FROM danh_gia
             WHERE khach_hang_id = ?
               AND tour_id = ?
               AND loai_danh_gia = 'Tour'
             ORDER BY danh_gia_id DESC
             LIMIT 1"
        );
        $stmt->execute([$khachHangId, $tourId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function updateTourReviewByKhachHang($khachHangId, $tourId, array $data) {
        $khachHangId = (int)$khachHangId;
        $tourId = (int)$tourId;
        if ($khachHangId <= 0 || $tourId <= 0) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "UPDATE danh_gia
             SET diem = ?,
                 noi_dung = ?,
                 tieu_chi = ?,
                 loai_dich_vu = ?,
                 ngay_danh_gia = NOW()
             WHERE khach_hang_id = ?
               AND tour_id = ?
               AND loai_danh_gia = 'Tour'"
        );

        return $stmt->execute([
            (int)($data['diem'] ?? 0),
            (string)($data['noi_dung'] ?? ''),
            $data['tieu_chi'] ?? null,
            $data['loai_dich_vu'] ?? null,
            $khachHangId,
            $tourId,
        ]);
    }
}
