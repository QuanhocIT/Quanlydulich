<?php
class GiaoDich {
    // Lấy tổng thu các tháng gần nhất (mặc định 12 tháng)
    public function getTongThuTheoThang($soThang = 12) {
        $sql = "SELECT DATE_FORMAT(ngay_giao_dich, '%m/%Y') as thang, COALESCE(SUM(so_tien),0) as tong_thu
                FROM giao_dich_tai_chinh
                WHERE loai = 'Thu'
                GROUP BY thang
                ORDER BY MIN(ngay_giao_dich) DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$soThang]);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach (array_reverse($rows) as $row) {
            $result[$row['thang']] = (float)$row['tong_thu'];
        }
        return $result;
    }
    // Lấy tổng thu của một tour
    public function getTongThuByTourId($tourId) {
        $sql = "SELECT SUM(so_tien) as tong_thu FROM giao_dich_tai_chinh WHERE tour_id = ? AND loai = 'Thu'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        $row = $stmt->fetch();
        return (float)($row['tong_thu'] ?? 0);
    }

    // Lấy tổng chi của một tour
    public function getTongChiByTourId($tourId) {
        $sql = "SELECT SUM(so_tien) as tong_chi FROM giao_dich_tai_chinh WHERE tour_id = ? AND loai = 'Chi'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        $row = $stmt->fetch();
        return (float)($row['tong_chi'] ?? 0);
    }

    // Lấy tổng thu/chi theo danh sách tour trong một query để tránh N+1.
    public function getTongThuChiByTourIds(array $tourIds) {
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

        $idList = array_values($normalizedIds);
        $placeholders = implode(',', array_fill(0, count($idList), '?'));
        $sql = "SELECT
                    tour_id,
                    COALESCE(SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END), 0) AS tong_thu,
                    COALESCE(SUM(CASE WHEN loai = 'Chi' THEN so_tien ELSE 0 END), 0) AS tong_chi
                FROM giao_dich_tai_chinh
                WHERE tour_id IN ($placeholders)
                GROUP BY tour_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($idList);

        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $id = (int)($row['tour_id'] ?? 0);
            if ($id > 0) {
                $result[$id] = [
                    'tong_thu' => (float)($row['tong_thu'] ?? 0),
                    'tong_chi' => (float)($row['tong_chi'] ?? 0),
                ];
            }
        }

        return $result;
    }
    public $conn;
    public function __construct() {
        $this->conn = connectDB();
    }

    private function buildFilterConditions($filters, &$params) {
        $conditions = [];

        if (!empty($filters['loai'])) {
            $conditions[] = 'loai = ?';
            $params[] = $filters['loai'];
        }
        if (!empty($filters['loai_giao_dich'])) {
            $conditions[] = 'loai_giao_dich = ?';
            $params[] = $filters['loai_giao_dich'];
        }
        if (!empty($filters['tour_id'])) {
            $conditions[] = 'tour_id = ?';
            $params[] = (int)$filters['tour_id'];
        }
        if (!empty($filters['khach_hang_id'])) {
            $conditions[] = 'khach_hang_id = ?';
            $params[] = (int)$filters['khach_hang_id'];
        }
        if (!empty($filters['tu_ngay'])) {
            $conditions[] = 'ngay_giao_dich >= ?';
            $params[] = $filters['tu_ngay'];
        }
        if (!empty($filters['den_ngay'])) {
            $conditions[] = 'ngay_giao_dich <= ?';
            $params[] = $filters['den_ngay'];
        }
        if (!empty($filters['keyword'])) {
            $conditions[] = '(mo_ta LIKE ? OR nguoi_thuc_hien LIKE ?)';
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        return $conditions;
    }

    public function getAll($limit = null, $offset = 0) {
        return $this->getFiltered([], $limit, $offset);
    }

    public function getFiltered($filters = [], $limit = null, $offset = 0) {
        $params = [];
        $sql = 'SELECT * FROM giao_dich_tai_chinh';
        $conditions = $this->buildFilterConditions($filters, $params);

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ngay_giao_dich DESC, id DESC';

        if ($limit !== null) {
            $sql .= ' LIMIT ? OFFSET ?';
        }

        $stmt = $this->conn->prepare($sql);
        $index = 1;
        foreach ($params as $param) {
            $stmt->bindValue($index++, $param);
        }
        if ($limit !== null) {
            $stmt->bindValue($index++, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue($index, max(0, (int)$offset), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countFiltered($filters = []) {
        $params = [];
        $sql = 'SELECT COUNT(*) FROM giao_dich_tai_chinh';
        $conditions = $this->buildFilterConditions($filters, $params);

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getTongThuChiFiltered($filters = []) {
        $params = [];
        $sql = "SELECT
                    COALESCE(SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END), 0) AS tong_thu,
                    COALESCE(SUM(CASE WHEN loai = 'Chi' THEN so_tien ELSE 0 END), 0) AS tong_chi
                FROM giao_dich_tai_chinh";
        $conditions = $this->buildFilterConditions($filters, $params);

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return [
            'tong_thu' => (float)($row['tong_thu'] ?? 0),
            'tong_chi' => (float)($row['tong_chi'] ?? 0),
        ];
    }

    public function findById($id) {
        $sql = "SELECT * FROM giao_dich_tai_chinh WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Lấy giao dịch theo tour
    public function getByTourId($tourId) {
        $sql = "SELECT * FROM giao_dich_tai_chinh WHERE tour_id = ? ORDER BY ngay_giao_dich DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        return $stmt->fetchAll();
    }

    // Thêm giao dịch
    public function insert($data) {
        $sql = "INSERT INTO giao_dich_tai_chinh (tour_id, loai, so_tien, mo_ta, ngay_giao_dich) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['tour_id'] ?? null,
            $data['loai'] ?? 'Chi',
            $data['so_tien'] ?? 0,
            $data['mo_ta'] ?? null,
            $data['ngay_giao_dich'] ?? date('Y-m-d')
        ]);
    }

    // Tính tổng thu theo tour
    public function getTongThuByTour($tourId) {
        $sql = "SELECT COALESCE(SUM(so_tien), 0) as tong_thu 
                FROM giao_dich_tai_chinh 
                WHERE tour_id = ? AND loai = 'Thu'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        $result = $stmt->fetch();
        return (float)($result['tong_thu'] ?? 0);
    }

    // Tính tổng chi theo tour
    public function getTongChiByTour($tourId) {
        $sql = "SELECT COALESCE(SUM(so_tien), 0) as tong_chi 
                FROM giao_dich_tai_chinh 
                WHERE tour_id = ? AND loai = 'Chi'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        $result = $stmt->fetch();
        return (float)($result['tong_chi'] ?? 0);
    }

    // Tính lãi/lỗ theo tour
    public function getLaiLoByTour($tourId) {
        $tongThu = $this->getTongThuByTour($tourId);
        $tongChi = $this->getTongChiByTour($tourId);
        return $tongThu - $tongChi;
    }

    // Thống kê tổng hợp theo tour
    public function getThongKeByTour($tourId) {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END), 0) as tong_thu,
                    COALESCE(SUM(CASE WHEN loai = 'Chi' THEN so_tien ELSE 0 END), 0) as tong_chi,
                    COUNT(*) as so_giao_dich
                FROM giao_dich_tai_chinh 
                WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        $result = $stmt->fetch();
        $tongThu = (float)($result['tong_thu'] ?? 0);
        $tongChi = (float)($result['tong_chi'] ?? 0);
        return [
            'tong_thu' => $tongThu,
            'tong_chi' => $tongChi,
            'lai_lo' => $tongThu - $tongChi,
            'so_giao_dich' => (int)($result['so_giao_dich'] ?? 0)
        ];
    }

    // Thống kê tổng hợp tất cả tour
    public function getThongKeTongHop($startDate = null, $endDate = null) {
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN loai = 'Thu' THEN so_tien ELSE 0 END), 0) as tong_thu,
                    COALESCE(SUM(CASE WHEN loai = 'Chi' THEN so_tien ELSE 0 END), 0) as tong_chi,
                    COUNT(*) as so_giao_dich
                FROM giao_dich_tai_chinh WHERE 1=1";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND ngay_giao_dich >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND ngay_giao_dich <= ?";
            $params[] = $endDate;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        $tongThu = (float)($result['tong_thu'] ?? 0);
        $tongChi = (float)($result['tong_chi'] ?? 0);
        return [
            'tong_thu' => $tongThu,
            'tong_chi' => $tongChi,
            'lai_lo' => $tongThu - $tongChi,
            'so_giao_dich' => (int)($result['so_giao_dich'] ?? 0)
        ];
    }

    // Thống kê theo từng tour
    public function getThongKeTheoTour($startDate = null, $endDate = null) {
        $sql = "SELECT 
                    t.tour_id,
                    t.ten_tour,
                    COALESCE(SUM(CASE WHEN gd.loai = 'Thu' THEN gd.so_tien ELSE 0 END), 0) as tong_thu,
                    COALESCE(SUM(CASE WHEN gd.loai = 'Chi' THEN gd.so_tien ELSE 0 END), 0) as tong_chi,
                    COUNT(gd.id) as so_giao_dich
                FROM tour t
                LEFT JOIN giao_dich_tai_chinh gd ON t.tour_id = gd.tour_id";
        $params = [];
        $where = [];
        
        if ($startDate) {
            $where[] = "(gd.ngay_giao_dich >= ? OR gd.ngay_giao_dich IS NULL)";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "(gd.ngay_giao_dich <= ? OR gd.ngay_giao_dich IS NULL)";
            $params[] = $endDate;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " GROUP BY t.tour_id, t.ten_tour 
                  ORDER BY (COALESCE(SUM(CASE WHEN gd.loai = 'Thu' THEN gd.so_tien ELSE 0 END), 0) - 
                            COALESCE(SUM(CASE WHEN gd.loai = 'Chi' THEN gd.so_tien ELSE 0 END), 0)) DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$row) {
            $row['tong_thu'] = (float)$row['tong_thu'];
            $row['tong_chi'] = (float)$row['tong_chi'];
            $row['lai_lo'] = $row['tong_thu'] - $row['tong_chi'];
            $row['so_giao_dich'] = (int)$row['so_giao_dich'];
        }
        
        return $results;
    }

    // Lấy tổng thu chi cho tất cả tour bằng 1 truy vấn gộp.
    public function getThuChiTatCaTour($startDate = null, $endDate = null) {
        $sql = "SELECT
                    t.tour_id,
                    t.ten_tour,
                    t.loai_tour,
                    COALESCE(SUM(CASE WHEN gd.loai = 'Thu' THEN gd.so_tien ELSE 0 END), 0) AS tong_thu,
                    COALESCE(SUM(CASE WHEN gd.loai = 'Chi' THEN gd.so_tien ELSE 0 END), 0) AS tong_chi
                FROM tour t
                LEFT JOIN giao_dich_tai_chinh gd ON t.tour_id = gd.tour_id";

        $params = [];
        $where = [];

        if (!empty($startDate)) {
            $where[] = "(gd.ngay_giao_dich >= ? OR gd.ngay_giao_dich IS NULL)";
            $params[] = $startDate;
        }

        if (!empty($endDate)) {
            $where[] = "(gd.ngay_giao_dich <= ? OR gd.ngay_giao_dich IS NULL)";
            $params[] = $endDate;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " GROUP BY t.tour_id, t.ten_tour, t.loai_tour
                  ORDER BY t.tour_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
