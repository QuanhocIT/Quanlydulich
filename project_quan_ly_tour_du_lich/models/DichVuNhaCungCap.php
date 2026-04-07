<?php

class DichVuNhaCungCap
{
    protected $conn;

    public function __construct()
    {
        $this->conn = connectDB();
    }

    public function getAllBySupplier($nhaCungCapId)
    {
        $sql = "SELECT * FROM dich_vu_nha_cung_cap WHERE nha_cung_cap_id = ? ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$nhaCungCapId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id, $nhaCungCapId)
    {
        $sql = "SELECT * FROM dich_vu_nha_cung_cap WHERE id = ? AND nha_cung_cap_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id, (int)$nhaCungCapId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nhaCungCapId, $data)
    {
        $sql = "INSERT INTO dich_vu_nha_cung_cap 
                (nha_cung_cap_id, ten_dich_vu, mo_ta, loai_dich_vu, gia_tham_khao, don_vi_tinh, cong_suat_toi_da, thoi_gian_xu_ly, tai_lieu_dinh_kem, trang_thai)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            (int)$nhaCungCapId,
            $data['ten_dich_vu'],
            $data['mo_ta'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['gia_tham_khao'] !== '' ? $data['gia_tham_khao'] : null,
            $data['don_vi_tinh'] ?? null,
            $data['cong_suat_toi_da'] !== '' ? (int)$data['cong_suat_toi_da'] : null,
            $data['thoi_gian_xu_ly'] ?? null,
            $data['tai_lieu_dinh_kem'] ?? null,
            $data['trang_thai'] ?? 'HoatDong'
        ]);
        return $this->conn->lastInsertId();
    }

    public function getBySupplierIds(array $supplierIds)
    {
        $supplierIds = array_filter(array_map('intval', $supplierIds));
        if (empty($supplierIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($supplierIds), '?'));
        $sql = "SELECT * FROM dich_vu_nha_cung_cap 
                WHERE nha_cung_cap_id IN ($placeholders)
                ORDER BY nha_cung_cap_id, ten_dich_vu";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($supplierIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $nhaCungCapId, $data)
    {
        $sql = "UPDATE dich_vu_nha_cung_cap
                SET ten_dich_vu = ?, mo_ta = ?, loai_dich_vu = ?, gia_tham_khao = ?, 
                    don_vi_tinh = ?, cong_suat_toi_da = ?, thoi_gian_xu_ly = ?, 
                    tai_lieu_dinh_kem = ?, trang_thai = ?
                WHERE id = ? AND nha_cung_cap_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['ten_dich_vu'],
            $data['mo_ta'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['gia_tham_khao'] !== '' ? $data['gia_tham_khao'] : null,
            $data['don_vi_tinh'] ?? null,
            $data['cong_suat_toi_da'] !== '' ? (int)$data['cong_suat_toi_da'] : null,
            $data['thoi_gian_xu_ly'] ?? null,
            $data['tai_lieu_dinh_kem'] ?? null,
            $data['trang_thai'] ?? 'HoatDong',
            (int)$id,
            (int)$nhaCungCapId
        ]);
    }

    public function delete($id, $nhaCungCapId)
    {
        $sql = "DELETE FROM dich_vu_nha_cung_cap WHERE id = ? AND nha_cung_cap_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id, (int)$nhaCungCapId]);
    }
}


