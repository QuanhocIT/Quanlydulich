<?php
class KhachHang 
{
    public $conn;
    
    public function __construct()
    {
        $this->conn = connectDB();
    }

    public function getAll($limit = null, $offset = 0) {
        $sql = "SELECT * FROM khach_hang ORDER BY khach_hang_id DESC";
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

    public function findById($id) {
        $sql = "SELECT * FROM khach_hang WHERE khach_hang_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByNguoiDungId($nguoiDungId) {
        $sql = "SELECT * FROM khach_hang WHERE nguoi_dung_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nguoiDungId]);
        return $stmt->fetch();
    }

    public function findByUserId($userId) {
        return $this->findByNguoiDungId($userId);
    }

    // Thống kê khách hàng mới theo tháng (dựa trên nguoi_dung.ngay_tao).
    public function getNewCustomersByMonth($months = 12) {
        $months = max(1, (int)$months);
        $sql = "SELECT DATE_FORMAT(nd.ngay_tao, '%Y-%m') AS thang, COUNT(*) AS total
                FROM khach_hang kh
                INNER JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE nd.ngay_tao IS NOT NULL
                  AND nd.ngay_tao >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(nd.ngay_tao, '%Y-%m')
                ORDER BY DATE_FORMAT(nd.ngay_tao, '%Y-%m') ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$months]);

        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $month = (string)($row['thang'] ?? '');
            if ($month !== '') {
                $result[$month] = (int)($row['total'] ?? 0);
            }
        }

        return $result;
    }

    public function insert($data) {
        $sql = "INSERT INTO khach_hang (nguoi_dung_id, dia_chi, gioi_tinh, ngay_sinh) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['nguoi_dung_id'],
            $data['dia_chi'] ?? null,
            $data['gioi_tinh'] ?? null,
            $data['ngay_sinh'] ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    // Tìm hoặc tạo khách hàng từ thông tin người dùng
    public function findOrCreateByNguoiDungInfo($nguoiDungId, $diaChi = null, $gioiTinh = null, $ngaySinh = null) {
        // Tìm khách hàng hiện có
        $khachHang = $this->findByNguoiDungId($nguoiDungId);
        if ($khachHang) {
            return $khachHang;
        }
        
        // Tạo mới nếu chưa có
        $khachHangId = $this->insert([
            'nguoi_dung_id' => $nguoiDungId,
            'dia_chi' => $diaChi,
            'gioi_tinh' => $gioiTinh,
            'ngay_sinh' => $ngaySinh
        ]);
        
        return $this->findById($khachHangId);
    }

    // Lấy thông tin khách hàng với thông tin người dùng
    public function getKhachHangWithNguoiDung($khachHangId) {
        $sql = "SELECT kh.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.vai_tro
                FROM khach_hang kh
                LEFT JOIN nguoi_dung nd ON kh.nguoi_dung_id = nd.id
                WHERE kh.khach_hang_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$khachHangId]);
        return $stmt->fetch();
    }
}
