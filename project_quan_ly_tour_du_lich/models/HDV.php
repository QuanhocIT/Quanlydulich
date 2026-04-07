<?php
class HDV 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }
    // Lấy tất cả HDV (có thể lọc theo nhóm hoặc trạng thái)
    public function getAll($groupId = null, $availableOnly = false) {
        $conds = ["ns.vai_tro = 'HDV'"];
        $params = [];
        if ($groupId) {
            $conds[] = 'ns.group_id = ?';
            $params[] = $groupId;
        }
        if ($availableOnly) {
            $conds[] = 'ns.trang_thai_lam_viec = "SanSang"';
        }
        $where = implode(' AND ', $conds);
        $sql = "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.avatar 
                FROM nhan_su ns 
                LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id 
                WHERE $where ORDER BY nd.ho_ten ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $sql = "SELECT * FROM nhan_su WHERE nhan_su_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Thêm HDV (hoặc cập nhật thông tin chi tiết)
    public function insert($data) {
        $sql = "INSERT INTO nhan_su (ho_ten, vai_tro, ngay_sinh, anh, so_dien_thoai, email, dia_chi, chung_chi, ngon_ngu, kinh_nghiem, suc_khoe, group_id, is_available, note)
                VALUES (?, 'HDV', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ho_ten'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['anh'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['dia_chi'] ?? null,
            $data['chung_chi'] ?? null,
            $data['ngon_ngu'] ?? null,
            $data['kinh_nghiem'] ?? null,
            $data['suc_khoe'] ?? null,
            $data['group_id'] ?? null,
            $data['is_available'] ?? 1,
            $data['note'] ?? null,
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE nhan_su SET ho_ten = ?, ngay_sinh = ?, anh = ?, so_dien_thoai = ?, email = ?, dia_chi = ?, chung_chi = ?, ngon_ngu = ?, kinh_nghiem = ?, suc_khoe = ?, group_id = ?, is_available = ?, note = ? WHERE nhan_su_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ho_ten'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['anh'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['dia_chi'] ?? null,
            $data['chung_chi'] ?? null,
            $data['ngon_ngu'] ?? null,
            $data['kinh_nghiem'] ?? null,
            $data['suc_khoe'] ?? null,
            $data['group_id'] ?? null,
            $data['is_available'] ?? 1,
            $data['note'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM nhan_su WHERE nhan_su_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Lấy lịch phân công của HDV
    public function getSchedule($hdvId, $from = null, $to = null) {
        $params = [$hdvId];
        $sql = "SELECT * FROM hdv_schedules WHERE hdv_id = ?";
        if ($from) {
            $sql .= " AND end_time >= ?"; $params[] = $from;
        }
        if ($to) {
            $sql .= " AND start_time <= ?"; $params[] = $to;
        }
        $sql .= " ORDER BY start_time ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Thêm phân công lịch
    public function addSchedule($hdvId, $tourId, $startTime, $endTime, $note = null) {
        $sql = "INSERT INTO hdv_schedules (hdv_id, tour_id, start_time, end_time, note) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hdvId, $tourId, $startTime, $endTime, $note]);
    }

    // Ghi nhận nghỉ phép / vắng mặt
    public function addAbsence($hdvId, $fromDate, $toDate, $type = null, $reason = null) {
        $sql = "INSERT INTO hdv_absences (hdv_id, date_from, date_to, type, reason) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hdvId, $fromDate, $toDate, $type, $reason]);
    }

    // Kiểm tra HDV có rảnh trong khoảng thời gian nhất định
    public function isAvailable($hdvId, $startTime, $endTime) {
        // Kiểm tra lịch phân công trùng
        $sql = "SELECT COUNT(*) as c FROM hdv_schedules WHERE hdv_id = ? AND NOT (end_time <= ? OR start_time >= ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdvId, $startTime, $endTime]);
        $r = $stmt->fetch();
        if ($r && $r['c'] > 0) return false;

        // Kiểm tra vắng mặt
        $dateFrom = date('Y-m-d', strtotime($startTime));
        $dateTo = date('Y-m-d', strtotime($endTime));
        $sql2 = "SELECT COUNT(*) as c FROM hdv_absences WHERE hdv_id = ? AND NOT (date_to < ? OR date_from > ?)";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([$hdvId, $dateFrom, $dateTo]);
        $r2 = $stmt2->fetch();
        if ($r2 && $r2['c'] > 0) return false;

        return true;
    }

    // Lấy lịch sử dẫn tour (liên kết với bảng tour hoặc booking nếu có)
    public function getTourHistory($hdvId, $limit = 50) {
        // Nếu có bảng liên kết giữa hdv và tour (ví dụ hdv_schedules), trả về các tour đã dẫn
        $sql = "SELECT hs.*, t.* FROM hdv_schedules hs LEFT JOIN tour t ON hs.tour_id = t.tour_id WHERE hs.hdv_id = ? ORDER BY hs.start_time DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$hdvId, (int)$limit]);
        return $stmt->fetchAll();
    }
}
