<?php
// Model cho Tour - tương tác với cơ sở dữ liệu
class Tour 
{
    public $conn;

    private function clearTourReadCache() {
        cacheForgetByPrefix('tour_options_');
        cacheForget('tour_dashboard_stats_v1');
        cacheForget('admin_dashboard_overview_v1');
    }
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Lấy tất cả tour
    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM tour ORDER BY tour_id DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = $this->conn->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(2, max(0, (int)$offset), PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll();
    }

    public function getLightweightList($limit = null, $offset = 0) {
        $sql = "SELECT tour_id, ten_tour, loai_tour, mo_ta, gia_co_ban, trang_thai
                FROM tour
                ORDER BY tour_id DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
        }

        $stmt = $this->conn->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(2, max(0, (int)$offset), PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOptions($limit = null) {
        $limitValue = $limit !== null ? max(1, (int)$limit) : 0;
        $cacheKey = 'tour_options_' . ($limitValue > 0 ? ('limit_' . $limitValue) : 'all');

        return cacheRemember($cacheKey, 300, function () use ($limitValue) {
            $sql = "SELECT tour_id, ten_tour FROM tour ORDER BY ten_tour ASC";
            if ($limitValue > 0) {
                $sql .= " LIMIT ?";
            }

            $stmt = $this->conn->prepare($sql);
            if ($limitValue > 0) {
                $stmt->bindValue(1, $limitValue, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll();
        });
    }

    public function getPublicTours(array $filters = [], $limit = null, $offset = 0) {
        $where = ["(trang_thai = 'HoatDong' OR trang_thai IS NULL)"];
        $params = [];

        if (!empty($filters['loai_tour'])) {
            $where[] = 'loai_tour = ?';
            $params[] = $filters['loai_tour'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(ten_tour LIKE ? OR mo_ta LIKE ?)';
            $keyword = '%' . trim((string)$filters['search']) . '%';
            array_push($params, $keyword, $keyword);
        }

        $sql = "SELECT tour_id, ten_tour, loai_tour, mo_ta, gia_co_ban, trang_thai
                FROM tour
                WHERE " . implode(' AND ', $where) . "
                ORDER BY tour_id DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelatedToursByType($loaiTour, $excludeTourId, $limit = 6) {
        $sql = "SELECT tour_id, ten_tour, loai_tour, mo_ta, gia_co_ban, trang_thai
                FROM tour
                WHERE loai_tour = ?
                  AND tour_id <> ?
                  AND (trang_thai = 'HoatDong' OR trang_thai IS NULL)
                ORDER BY tour_id DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $loaiTour);
        $stmt->bindValue(2, (int)$excludeTourId, PDO::PARAM_INT);
        $stmt->bindValue(3, max(1, (int)$limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return [];
        }

        $thumbnailMap = $this->getThumbnailMapByTourIds(array_column($rows, 'tour_id'));
        foreach ($rows as &$row) {
            $row['hinh_anh'] = $thumbnailMap[(int)($row['tour_id'] ?? 0)] ?? null;
        }
        unset($row);

        return $rows;
    }

    public function getDashboardTourStats() {
        return cacheRemember('tour_dashboard_stats_v1', 120, function () {
            $sql = "SELECT tour_id, ten_tour, gia_co_ban, trang_thai
                FROM tour
                ORDER BY tour_id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        });
    }

    // Lấy tour theo ID
    public function findById($id) {
        $sql = "SELECT * FROM tour WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Tìm tour theo điều kiện
    public function find($conditions = []) {
        $sql = "SELECT * FROM tour";
        $params = [];
        
        if (isset($conditions) && count($conditions) > 0) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Thêm tour mới
    public function insert($data) {
        $sql = "INSERT INTO tour (ten_tour, loai_tour, mo_ta, gia_co_ban, chinh_sach, id_nha_cung_cap, tao_boi, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['ten_tour'] ?? '',
            $data['loai_tour'] ?? 'TrongNuoc',
            $data['mo_ta'] ?? '',
            $data['gia_co_ban'] ?? 0,
            $data['chinh_sach'] ?? null,
            $data['id_nha_cung_cap'] ?? null,
            $data['tao_boi'] ?? null,
            $data['trang_thai'] ?? 'HoatDong'
        ]);

        if ($result) {
            $this->clearTourReadCache();
        }

        return $result;
    }

    // Cập nhật tour
    public function update($id, $data) {
        $sql = "UPDATE tour SET ten_tour = ?, loai_tour = ?, mo_ta = ?, gia_co_ban = ?, chinh_sach = ?, 
                id_nha_cung_cap = ?, trang_thai = ? WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['ten_tour'] ?? '',
            $data['loai_tour'] ?? 'TrongNuoc',
            $data['mo_ta'] ?? '',
            $data['gia_co_ban'] ?? 0,
            $data['chinh_sach'] ?? null,
            $data['id_nha_cung_cap'] ?? null,
            $data['trang_thai'] ?? 'HoatDong',
            $id
        ]);

        if ($result) {
            $this->clearTourReadCache();
        }

        return $result;
    }

    public function updateQrCodePath($tourId, $path) {
        $sql = "UPDATE tour SET qr_code_path = ? WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$path, (int)$tourId]);
        if ($result) {
            $this->clearTourReadCache();
        }
        return $result;
    }

    // Xóa tour
    public function delete($id) {
        // Xóa tất cả các bản ghi liên quan trước khi xóa tour
        // Thứ tự xóa: từ bảng con đến bảng cha để tránh vi phạm foreign key constraint
        
        // 1. Xóa hình ảnh tour
        $this->deleteHinhAnhByTourId($id);
        
        // 2. Xóa lịch trình tour
        $this->deleteLichTrinhByTourId($id);
        
        // 3. Xóa lịch khởi hành
        $this->deleteLichKhoiHanhByTourId($id);
        
        // 4. Xóa nhật ký tour
        $this->deleteNhatKyByTourId($id);
        
        // 5. Xóa phản hồi đánh giá
        $this->deletePhanHoiDanhGiaByTourId($id);
        
        // 6. Xóa giao dịch tài chính
        $this->deleteGiaoDichTaiChinhByTourId($id);
        
        // 7. Xóa yêu cầu đặc biệt
        $this->deleteYeuCauDacBietByTourId($id);
        
        // 8. Xóa booking (nếu muốn xóa cả booking khi xóa tour)
        $this->deleteBookingByTourId($id);
        
        // 9. Cuối cùng mới xóa tour
        $sql = "DELETE FROM tour WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$id]);

        if ($result) {
            $this->clearTourReadCache();
        }
        
        return $result;
    }

    // Lấy danh sách lịch trình theo tour_id
    public function getLichTrinhByTourId($tourId) {
        $sql = "SELECT ngay_thu, dia_diem, hoat_dong 
                FROM lich_trinh_tour 
                WHERE tour_id = ? 
                ORDER BY ngay_thu ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        return $stmt->fetchAll();
    }

    // Lấy danh sách lịch khởi hành theo tour_id
    public function getLichKhoiHanhByTourId($tourId) {
        $sql = "SELECT ngay_khoi_hanh, ngay_ket_thuc, diem_tap_trung, so_cho, trang_thai 
                FROM lich_khoi_hanh 
                WHERE tour_id = ? 
                ORDER BY ngay_khoi_hanh ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        return $stmt->fetchAll();
    }

    // Lấy thông tin hướng dẫn viên từ lịch khởi hành theo tour_id
    public function getHDVByTourId($tourId) {
        $sql = "SELECT 
                    lk.id as lich_khoi_hanh_id,
                    lk.ngay_khoi_hanh,
                    lk.ngay_ket_thuc,
                    lk.diem_tap_trung,
                    lk.trang_thai as lich_trang_thai,
                    ns.nhan_su_id,
                    ns.vai_tro as ns_vai_tro,
                    ns.chung_chi,
                    ns.ngon_ngu,
                    ns.kinh_nghiem,
                    ns.suc_khoe,
                    nd.id as nguoi_dung_id,
                    nd.ho_ten,
                    nd.email,
                    nd.so_dien_thoai
                FROM lich_khoi_hanh lk
                LEFT JOIN nhan_su ns ON lk.hdv_id = ns.nhan_su_id
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
                WHERE lk.tour_id = ? 
                ORDER BY lk.ngay_khoi_hanh ASC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        return $stmt->fetch();
    }

    // Lấy danh sách tour mà HDV hiện tại được phân công theo user_id.
    public function getToursByHDV($nguoiDungId) {
        $nguoiDungId = (int)$nguoiDungId;
        if ($nguoiDungId <= 0) {
            return [];
        }

        $sql = "SELECT DISTINCT
                    t.*, 
                    MAX(lk.ngay_khoi_hanh) AS ngay_khoi_hanh_gan_nhat
                FROM nhan_su ns
                INNER JOIN lich_khoi_hanh lk
                    ON lk.hdv_id = ns.nhan_su_id
                INNER JOIN tour t
                    ON t.tour_id = lk.tour_id
                WHERE ns.nguoi_dung_id = ?
                  AND ns.vai_tro = 'HDV'
                GROUP BY t.tour_id
                
                UNION
                
                SELECT DISTINCT
                    t.*, 
                    MAX(lk.ngay_khoi_hanh) AS ngay_khoi_hanh_gan_nhat
                FROM nhan_su ns
                INNER JOIN phan_bo_nhan_su pbn
                    ON pbn.nhan_su_id = ns.nhan_su_id
                   AND pbn.vai_tro = 'HDV'
                INNER JOIN lich_khoi_hanh lk
                    ON lk.id = pbn.lich_khoi_hanh_id
                INNER JOIN tour t
                    ON t.tour_id = lk.tour_id
                WHERE ns.nguoi_dung_id = ?
                  AND ns.vai_tro = 'HDV'
                GROUP BY t.tour_id
                
                ORDER BY ngay_khoi_hanh_gan_nhat DESC, tour_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nguoiDungId, $nguoiDungId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Lấy danh sách hình ảnh theo tour_id
    public function getHinhAnhByTourId($tourId) {
        $sql = "SELECT url_anh, mo_ta 
                FROM hinh_anh_tour 
                WHERE tour_id = ? 
                ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        return $stmt->fetchAll();
    }

    // Lấy ảnh đại diện (ảnh đầu tiên) cho nhiều tour cùng lúc để tránh N+1 query.
    public function getThumbnailMapByTourIds(array $tourIds) {
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

        $sql = "SELECT ha.tour_id, ha.url_anh
                FROM hinh_anh_tour ha
                INNER JOIN (
                    SELECT tour_id, MIN(id) AS first_id
                    FROM hinh_anh_tour
                    WHERE tour_id IN ($placeholders)
                    GROUP BY tour_id
                ) first_img
                    ON first_img.tour_id = ha.tour_id
                   AND first_img.first_id = ha.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($idList);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $tourId = (int)($row['tour_id'] ?? 0);
            if ($tourId > 0) {
                $result[$tourId] = $row['url_anh'] ?? null;
            }
        }

        return $result;
    }

    // Lấy lịch khởi hành tiếp theo (gần nhất >= hôm nay) cho nhiều tour cùng lúc — tránh N+1.
    // Trả về map [tour_id => ['ngay_khoi_hanh'=>..., 'ngay_ket_thuc'=>..., 'diem_tap_trung'=>..., 'so_cho'=>...]]
    public function getNextScheduleMapByTourIds(array $tourIds): array {
        $normalizedIds = [];
        foreach ($tourIds as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $normalizedIds[$id] = $id;
            }
        }
        if (empty($normalizedIds)) {
            return [];
        }

        $idList = array_values($normalizedIds);
        $placeholders = implode(',', array_fill(0, count($idList), '?'));
        $today = date('Y-m-d');

        // Ưu tiên lịch >= hôm nay (gần nhất). Dùng MIN(id) làm tie-breaker để lấy
        // đúng 1 hàng duy nhất mỗi tour mà không cần window function (MySQL 5.7+).
        $sql = "SELECT lk.tour_id,
                       lk.ngay_khoi_hanh,
                       lk.ngay_ket_thuc,
                       lk.diem_tap_trung,
                       lk.so_cho
                FROM lich_khoi_hanh lk
                INNER JOIN (
                    SELECT tour_id, MIN(id) AS first_id
                    FROM lich_khoi_hanh
                    WHERE tour_id IN ($placeholders)
                      AND ngay_khoi_hanh >= ?
                    GROUP BY tour_id
                ) nxt ON nxt.tour_id = lk.tour_id AND nxt.first_id = lk.id
                ORDER BY lk.ngay_khoi_hanh ASC";

        $params = array_merge($idList, [$today]);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $tid = (int)($row['tour_id'] ?? 0);
            if ($tid > 0) {
                $result[$tid] = $row;
            }
        }

        return $result;
    }

    // Đếm tour theo bộ lọc (dùng cho phân trang)
    public function countFiltered(array $conditions, string $search) {
        $where = ['1=1'];
        $params = [];
        if (!empty($conditions['loai_tour'])) {
            $where[] = 'loai_tour = ?';
            $params[] = $conditions['loai_tour'];
        }
        if (!empty($conditions['trang_thai'])) {
            $where[] = 'trang_thai = ?';
            $params[] = $conditions['trang_thai'];
        }
        if ($search !== '') {
            $where[] = 'ten_tour LIKE ?';
            $params[] = '%' . $search . '%';
        }
        $sql = 'SELECT COUNT(*) FROM tour WHERE ' . implode(' AND ', $where);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Lấy tour có phân trang và bộ lọc SQL-side
    public function getAllPaginated(array $conditions, string $search, int $limit, int $offset) {
        $where = ['1=1'];
        $params = [];
        if (!empty($conditions['loai_tour'])) {
            $where[] = 'loai_tour = ?';
            $params[] = $conditions['loai_tour'];
        }
        if (!empty($conditions['trang_thai'])) {
            $where[] = 'trang_thai = ?';
            $params[] = $conditions['trang_thai'];
        }
        if ($search !== '') {
            $where[] = 'ten_tour LIKE ?';
            $params[] = '%' . $search . '%';
        }
        $sql = 'SELECT * FROM tour WHERE ' . implode(' AND ', $where) . ' ORDER BY tour_id DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Lấy danh sách yêu cầu đặc biệt theo tour_id
    public function getYeuCauDacBietByTourId($tourId) {
        $sql = "SELECT yc.*, b.khach_hang_id 
                FROM yeu_cau_dac_biet yc
                INNER JOIN booking b ON yc.booking_id = b.booking_id
                WHERE b.tour_id = ? 
                ORDER BY yc.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$tourId]);
        return $stmt->fetchAll();
    }

    // Lấy nhật ký tour theo tour_id
    

public function getNhatKyTourByTourId($tourId) {
    $sql = "SELECT 
                nkt.id,
                nkt.tour_id,
                nkt.nhan_su_id,
                nkt.noi_dung,
                nkt.ngay_ghi AS thoi_gian_su_kien,
                nkt.loai_nhat_ky AS loai_su_kien,
                nkt.tieu_de,
                nkt.thoi_tiet,
                nkt.cach_xu_ly,
                nkt.hinh_anh,
                NULL AS dia_diem,
                nd.ho_ten AS nguoi_ghi_chep,
                nd.email AS hdv_email,
                nd.so_dien_thoai AS hdv_sdt
            FROM nhat_ky_tour nkt
            LEFT JOIN nhan_su ns ON nkt.nhan_su_id = ns.nhan_su_id
            LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id
            WHERE nkt.tour_id = ? 
            ORDER BY nkt.ngay_ghi DESC, nkt.id DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([(int)$tourId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Thêm lịch trình tour
    public function insertLichTrinh($tourId, $lichTrinh) {
        $sql = "INSERT INTO lich_trinh_tour (tour_id, ngay_thu, dia_diem, hoat_dong) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$tourId,
            (int)$lichTrinh['ngay_thu'],
            $lichTrinh['dia_diem'] ?? '',
            $lichTrinh['hoat_dong'] ?? ''
        ]);
    }

    // Thêm nhiều lịch trình cùng lúc
    public function insertMultipleLichTrinh($tourId, $lichTrinhList) {
        // Xóa lịch trình cũ trước
        $this->deleteLichTrinhByTourId($tourId);
        
        $sql = "INSERT INTO lich_trinh_tour (tour_id, ngay_thu, dia_diem, hoat_dong) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($lichTrinhList as $lichTrinh) {
            $stmt->execute([
                (int)$tourId,
                (int)$lichTrinh['ngay_thu'],
                $lichTrinh['dia_diem'] ?? '',
                $lichTrinh['hoat_dong'] ?? ''
            ]);
        }
        
        return true;
    }

    // Xóa lịch trình tour theo tour_id
    public function deleteLichTrinhByTourId($tourId) {
        $sql = "DELETE FROM lich_trinh_tour WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Thêm lịch khởi hành
    public function insertLichKhoiHanh($tourId, $lichKhoiHanh) {
        $sql = "INSERT INTO lich_khoi_hanh (tour_id, ngay_khoi_hanh, ngay_ket_thuc, diem_tap_trung, hdv_id, trang_thai) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$tourId,
            $lichKhoiHanh['ngay_khoi_hanh'] ?? null,
            $lichKhoiHanh['ngay_ket_thuc'] ?? null,
            $lichKhoiHanh['diem_tap_trung'] ?? '',
            isset($lichKhoiHanh['hdv_id']) && $lichKhoiHanh['hdv_id'] !== '' ? (int)$lichKhoiHanh['hdv_id'] : null,
            $lichKhoiHanh['trang_thai'] ?? 'SapKhoiHanh'
        ]);
    }

    // Xóa lịch khởi hành theo tour_id
    public function deleteLichKhoiHanhByTourId($tourId) {
        $sql = "DELETE FROM lich_khoi_hanh WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Thêm hình ảnh tour
    public function insertHinhAnh($tourId, $hinhAnh) {
        $sql = "INSERT INTO hinh_anh_tour (tour_id, url_anh, mo_ta) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$tourId,
            $hinhAnh['url_anh'] ?? '',
            $hinhAnh['mo_ta'] ?? ''
        ]);
    }

    // Xóa hình ảnh tour theo tour_id
    public function deleteHinhAnhByTourId($tourId) {
        $sql = "DELETE FROM hinh_anh_tour WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Xóa nhật ký tour theo tour_id
    public function deleteNhatKyByTourId($tourId) {
        $sql = "DELETE FROM nhat_ky_tour WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Xóa phản hồi đánh giá theo tour_id
    public function deletePhanHoiDanhGiaByTourId($tourId) {
        $sql = "DELETE FROM phan_hoi_danh_gia WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Xóa giao dịch tài chính theo tour_id
    public function deleteGiaoDichTaiChinhByTourId($tourId) {
        $sql = "DELETE FROM giao_dich_tai_chinh WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Xóa yêu cầu đặc biệt theo tour_id
    public function deleteYeuCauDacBietByTourId($tourId) {
        $sql = "DELETE yc FROM yeu_cau_dac_biet yc
                INNER JOIN booking b ON yc.booking_id = b.booking_id
                WHERE b.tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Thêm yêu cầu đặc biệt
    public function insertYeuCauDacBiet($bookingId, $noiDung, $loaiYeuCau = 'khac', $mucDoUuTien = 'trung_binh') {
        $sql = "INSERT INTO yeu_cau_dac_biet (booking_id, loai_yeu_cau, tieu_de, mo_ta, muc_do_uu_tien) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$bookingId,
            $loaiYeuCau,
            'Yêu cầu đặc biệt',
            $noiDung,
            $mucDoUuTien
        ]);
    }

    // Xóa booking theo tour_id
    public function deleteBookingByTourId($tourId) {
        $sql = "DELETE FROM booking WHERE tour_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$tourId]);
    }

    // Lấy tour_id vừa insert
    public function getLastInsertId() {
        return $this->conn->lastInsertId();
    }
}
