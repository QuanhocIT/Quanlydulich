<?php
// Model cho NguoiDung - tương tác với cơ sở dữ liệu
class NguoiDung 
{
    // Bỏ thuộc tính private $db; để tránh nhầm lẫn
    public $conn; // Thuộc tính kết nối duy nhất, được khởi tạo trong __construct()
    
    // Yêu cầu: Đảm bảo rằng hàm connectDB() trả về một đối tượng PDO đã kết nối thành công.
    public function __construct()
    {
        // Giả định hàm connectDB() đã được định nghĩa và có thể gọi được.
        $this->conn = connectDB();
    }

    // Lấy tất cả người dùng
    public function getAll() {
        $sql = "SELECT * FROM nguoi_dung ORDER BY ngay_tao DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy người dùng theo ID
    public function findById($id) {
        $sql = "SELECT * FROM nguoi_dung WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Tìm người dùng theo email
    public function findByEmail($email) {
        $sql = "SELECT * FROM nguoi_dung WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Tìm người dùng theo số điện thoại
    public function findByPhone($soDienThoai) {
        $sql = "SELECT * FROM nguoi_dung WHERE so_dien_thoai = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$soDienThoai]);
        return $stmt->fetch();
    }

    // Tìm hoặc tạo người dùng mới (cho nhân viên đặt tour)
    public function findOrCreate($hoTen, $email, $soDienThoai, $vaiTro = 'KhachHang') {
        // Tìm theo email trước (nếu có)
        if (!empty($email)) {
            $nguoiDung = $this->findByEmail($email);
            if ($nguoiDung) {
                return $nguoiDung;
            }
        }
        
        // Tìm theo số điện thoại (nếu có)
        if (!empty($soDienThoai)) {
            $nguoiDung = $this->findByPhone($soDienThoai);
            if ($nguoiDung) {
                return $nguoiDung;
            }
        }
        
        // Tạo mới nếu chưa có
        $tenDangNhap = !empty($email) ? $email : (!empty($soDienThoai) ? 'user_' . $soDienThoai : 'user_' . time());
        $matKhau = password_hash('123456', PASSWORD_DEFAULT); // Mật khẩu mặc định
        
        $nguoiDungId = $this->insert([
            'ten_dang_nhap' => $tenDangNhap,
            'ho_ten' => $hoTen,
            'email' => $email ?? '',
            'so_dien_thoai' => $soDienThoai ?? '',
            'mat_khau' => $matKhau,
            'vai_tro' => $vaiTro
        ]);
        
        return $this->findById($nguoiDungId);
    }

    /**
     * Lấy danh sách người dùng có kèm tìm kiếm và lọc.
     * Đây là hàm được sửa để sử dụng $this->conn thay vì $this->db
     */
    public function getFilteredUsers($search = '', $role = '', $status = '') {
        // Kiểm tra kết nối trước khi sử dụng
        if ($this->conn === null) {
             // Có thể ném ngoại lệ hoặc trả về mảng rỗng nếu kết nối thất bại
             return []; 
        }

        $sql = "SELECT * FROM nguoi_dung WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (ten_dang_nhap LIKE :search OR ho_ten LIKE :search OR email LIKE :search OR so_dien_thoai LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if (!empty($role)) {
            $sql .= " AND vai_tro = :role";
            $params[':role'] = $role;
        }
        if (!empty($status)) {
            $sql .= " AND trang_thai = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY id DESC";

        try {
            // SỬA LỖI: Sử dụng $this->conn thay vì $this->db
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Xử lý lỗi DB tại đây (nên dùng error_log)
            return []; 
        }
    }

    public function getUserStats($search = '', $role = '', $status = '') {
        if ($this->conn === null) {
            return [
                'total' => 0,
                'active' => 0,
                'locked' => 0,
                'roles' => [
                    'Admin' => 0,
                    'HDV' => 0,
                    'KhachHang' => 0,
                    'NhaCungCap' => 0,
                ],
            ];
        }

        $sql = "SELECT vai_tro, trang_thai, COUNT(*) AS total FROM nguoi_dung WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (ten_dang_nhap LIKE :search OR ho_ten LIKE :search OR email LIKE :search OR so_dien_thoai LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if (!empty($role)) {
            $sql .= " AND vai_tro = :role";
            $params[':role'] = $role;
        }
        if (!empty($status)) {
            $sql .= " AND trang_thai = :status";
            $params[':status'] = $status;
        }

        $sql .= " GROUP BY vai_tro, trang_thai";

        $stats = [
            'total' => 0,
            'active' => 0,
            'locked' => 0,
            'roles' => [
                'Admin' => 0,
                'HDV' => 0,
                'KhachHang' => 0,
                'NhaCungCap' => 0,
            ],
        ];

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $count = (int)($row['total'] ?? 0);
                $vaiTro = $row['vai_tro'] ?? '';
                $trangThai = $row['trang_thai'] ?? '';

                $stats['total'] += $count;
                if ($trangThai === 'HoatDong') {
                    $stats['active'] += $count;
                } elseif ($trangThai === 'BiKhoa') {
                    $stats['locked'] += $count;
                }

                if (isset($stats['roles'][$vaiTro])) {
                    $stats['roles'][$vaiTro] += $count;
                }
            }
        } catch (PDOException $e) {
            return $stats;
        }

        return $stats;
    }

    // Tìm người dùng theo điều kiện
    public function find($conditions = []) {
        $sql = "SELECT * FROM nguoi_dung";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Thêm người dùng mới
    public function insert($data) {
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, ho_ten, email, so_dien_thoai, mat_khau, vai_tro, ngay_tao) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        try {
            $result = $stmt->execute([
                $data['ten_dang_nhap'] ?? ($data['email'] ?? ''),
                $data['ho_ten'] ?? '',
                $data['email'] ?? '',
                $data['so_dien_thoai'] ?? '',
                $data['mat_khau'] ?? '',
                $data['vai_tro'] ?? 'KhachHang',
                $data['ngay_tao'] ?? date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            // Thêm logging hoặc xử lý lỗi chi tiết hơn nếu cần
            return false;
        }

        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Cập nhật người dùng
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['ho_ten'])) {
            $fields[] = "ho_ten = ?";
            $params[] = $data['ho_ten'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        if (isset($data['so_dien_thoai'])) {
            $fields[] = "so_dien_thoai = ?";
            $params[] = $data['so_dien_thoai'];
        }
        if (isset($data['vai_tro'])) {
            $fields[] = "vai_tro = ?";
            $params[] = $data['vai_tro'];
        }
        if (isset($data['mat_khau'])) {
            $fields[] = "mat_khau = ?";
            $params[] = $data['mat_khau'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE nguoi_dung SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // Cập nhật mật khẩu (hash)
    public function updatePassword($id, $hashedPassword) {
        $sql = "UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function updateStatus($id, $status) {
        $allowedStatus = ['HoatDong', 'BiKhoa'];
        if (!in_array($status, $allowedStatus, true)) {
            return false;
        }
        $sql = "UPDATE nguoi_dung SET trang_thai = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$status, $id]);
    }

    // Xóa người dùng
    public function delete($id) {
        $sql = "DELETE FROM nguoi_dung WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}