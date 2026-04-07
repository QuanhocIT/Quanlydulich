<?php
// Model cho PhanBoDichVu - Phân bổ dịch vụ cho lịch khởi hành
class PhanBoDichVu 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    // Lấy phân bổ dịch vụ theo lịch khởi hành
    public function getByLichKhoiHanh($lichKhoiHanhId) {
        $sql = "SELECT pbd.*, 
                ncc.ten_don_vi, ncc.loai_dich_vu as ncc_loai_dich_vu
                FROM phan_bo_dich_vu pbd
                LEFT JOIN nha_cung_cap ncc ON pbd.nha_cung_cap_id = ncc.id_nha_cung_cap
                WHERE pbd.lich_khoi_hanh_id = ?
                ORDER BY pbd.loai_dich_vu, pbd.ngay_bat_dau";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    // Lấy phân bổ dịch vụ theo loại
    public function getByLoai($lichKhoiHanhId, $loaiDichVu) {
        $sql = "SELECT pbd.*, 
                ncc.ten_don_vi
                FROM phan_bo_dich_vu pbd
                LEFT JOIN nha_cung_cap ncc ON pbd.nha_cung_cap_id = ncc.id_nha_cung_cap
                WHERE pbd.lich_khoi_hanh_id = ? AND pbd.loai_dich_vu = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId, $loaiDichVu]);
        return $stmt->fetchAll();
    }

    // Thêm phân bổ dịch vụ
    public function insert($data) {
        $sql = "INSERT INTO phan_bo_dich_vu 
                (lich_khoi_hanh_id, nha_cung_cap_id, loai_dich_vu, ten_dich_vu, 
                 so_luong, don_vi, ngay_bat_dau, ngay_ket_thuc, gio_bat_dau, gio_ket_thuc,
                 dia_diem, gia_tien, ghi_chu, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $data['lich_khoi_hanh_id'] ?? 0,
            $data['nha_cung_cap_id'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['ten_dich_vu'] ?? '',
            $data['so_luong'] ?? 1,
            $data['don_vi'] ?? null,
            !empty($data['ngay_bat_dau']) ? $data['ngay_bat_dau'] : null,
            !empty($data['ngay_ket_thuc']) ? $data['ngay_ket_thuc'] : null,
            !empty($data['gio_bat_dau']) ? $data['gio_bat_dau'] : null,
            !empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] : null,
            $data['dia_diem'] ?? null,
            $data['gia_tien'] ?? null,
            $data['ghi_chu'] ?? null,
            $data['trang_thai'] ?? 'ChoXacNhan'
        ]);
        
        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật phân bổ dịch vụ
    public function update($id, $data) {
        $sql = "UPDATE phan_bo_dich_vu SET 
                nha_cung_cap_id = ?, loai_dich_vu = ?, ten_dich_vu = ?,
                so_luong = ?, don_vi = ?, ngay_bat_dau = ?, ngay_ket_thuc = ?,
                gio_bat_dau = ?, gio_ket_thuc = ?, dia_diem = ?,
                gia_tien = ?, ghi_chu = ?, trang_thai = ?,
                thoi_gian_xac_nhan = ?
                WHERE id = ?";
        $thoiGianXacNhan = isset($data['trang_thai']) && $data['trang_thai'] == 'DaXacNhan' 
            ? date('Y-m-d H:i:s') 
            : null;
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nha_cung_cap_id'] ?? null,
            $data['loai_dich_vu'] ?? 'Khac',
            $data['ten_dich_vu'] ?? '',
            $data['so_luong'] ?? 1,
            $data['don_vi'] ?? null,
            !empty($data['ngay_bat_dau']) ? $data['ngay_bat_dau'] : null,
            !empty($data['ngay_ket_thuc']) ? $data['ngay_ket_thuc'] : null,
            !empty($data['gio_bat_dau']) ? $data['gio_bat_dau'] : null,
            !empty($data['gio_ket_thuc']) ? $data['gio_ket_thuc'] : null,
            $data['dia_diem'] ?? null,
            $data['gia_tien'] ?? null,
            $data['ghi_chu'] ?? null,
            $data['trang_thai'] ?? 'ChoXacNhan',
            $thoiGianXacNhan,
            $id
        ]);
    }

    // Xóa phân bổ dịch vụ
    public function delete($id) {
        $sql = "DELETE FROM phan_bo_dich_vu WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }

    // Cập nhật trạng thái xác nhận
    public function updateTrangThai($id, $trangThai) {
        $sql = "UPDATE phan_bo_dich_vu SET 
                trang_thai = ?,
                thoi_gian_xac_nhan = ?
                WHERE id = ?";
        $thoiGian = ($trangThai == 'DaXacNhan' || $trangThai == 'TuChoi') 
            ? date('Y-m-d H:i:s') 
            : null;
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$trangThai, $thoiGian, (int)$id]);
    }

    // Lấy tổng chi phí dịch vụ cho lịch khởi hành
    public function getTongChiPhi($lichKhoiHanhId) {
        $sql = "SELECT COALESCE(SUM(gia_tien * so_luong), 0) as tong_chi_phi
                FROM phan_bo_dich_vu
                WHERE lich_khoi_hanh_id = ? AND trang_thai != 'Huy'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        $result = $stmt->fetch();
        return (float)($result['tong_chi_phi'] ?? 0);
    }
}

