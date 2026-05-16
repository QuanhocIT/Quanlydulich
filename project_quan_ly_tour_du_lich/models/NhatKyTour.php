<?php

class NhatKyTour
{
    public $conn;

    public function __construct()
    {
        $this->conn = connectDB();
    }

    public function getByHDVAndTour($nhanSuId, $tourId = null)
    {
        $sql = "SELECT nkt.*, t.ten_tour
                FROM nhat_ky_tour nkt
                INNER JOIN tour t ON nkt.tour_id = t.tour_id
                                WHERE nkt.nhan_su_id = ?
                                    AND nkt.deleted_at IS NULL";
        $params = [(int)$nhanSuId];

        if ($tourId) {
            $sql .= " AND nkt.tour_id = ?";
            $params[] = (int)$tourId;
        }

        $sql .= " ORDER BY nkt.ngay_ghi DESC, nkt.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id, $nhanSuId)
    {
        $sql = "SELECT * FROM nhat_ky_tour WHERE id = ? AND nhan_su_id = ? AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id, (int)$nhanSuId]);
        return $stmt->fetch();
    }

    public function insert($data)
    {
        $sql = "INSERT INTO nhat_ky_tour (tour_id, nhan_su_id, noi_dung, ngay_ghi)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$data['tour_id'],
            (int)$data['nhan_su_id'],
            $data['noi_dung'] ?? '',
            $data['ngay_ghi'] ?? date('Y-m-d')
        ]);
    }

    public function update($id, $nhanSuId, $data)
    {
        $sql = "UPDATE nhat_ky_tour
                SET tour_id = ?, noi_dung = ?, ngay_ghi = ?
                WHERE id = ? AND nhan_su_id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            (int)$data['tour_id'],
            $data['noi_dung'] ?? '',
            $data['ngay_ghi'] ?? date('Y-m-d'),
            (int)$id,
            (int)$nhanSuId
        ]);
    }

    public function deleteById($id)
    {
        $sql = "UPDATE nhat_ky_tour SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }
}

