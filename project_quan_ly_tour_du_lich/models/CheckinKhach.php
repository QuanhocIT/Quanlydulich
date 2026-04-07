<?php

class CheckinKhach
{
    public $conn;

    public function __construct()
    {
        $this->conn = connectDB();
    }

    public function getByLichKhoiHanh($lichKhoiHanhId)
    {
        $sql = "SELECT *
                FROM tour_checkin
                WHERE lich_khoi_hanh_id = ?
                ORDER BY updated_at DESC, checkin_time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId]);
        return $stmt->fetchAll();
    }

    public function findOne($lichKhoiHanhId, $bookingId, $khachHangId)
    {
        $sql = "SELECT *
                FROM tour_checkin
                WHERE lich_khoi_hanh_id = ?
                  AND booking_id = ?
                  AND khach_hang_id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$lichKhoiHanhId, (int)$bookingId, (int)$khachHangId]);
        return $stmt->fetch();
    }

    public function getByBookingId($bookingId)
    {
        $sql = "SELECT *
                FROM tour_checkin
                WHERE booking_id = ?
                ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$bookingId]);
        return $stmt->fetchAll();
    }

    // Lấy checkin theo nhiều booking_id trong một query, trả về map booking_id => rows.
    public function getByBookingIdsGrouped(array $bookingIds)
    {
        $normalized = [];
        foreach ($bookingIds as $bookingId) {
            $id = (int)$bookingId;
            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }

        if (empty($normalized)) {
            return [];
        }

        $idList = array_values($normalized);
        $placeholders = implode(',', array_fill(0, count($idList), '?'));
        $sql = "SELECT *
                FROM tour_checkin
                WHERE booking_id IN ($placeholders)
                ORDER BY booking_id ASC, id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($idList);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $bookingId = (int)($row['booking_id'] ?? 0);
            if ($bookingId <= 0) {
                continue;
            }
            if (!isset($grouped[$bookingId])) {
                $grouped[$bookingId] = [];
            }
            $grouped[$bookingId][] = $row;
        }

        return $grouped;
    }

    public function findById($id)
    {
        $sql = "SELECT *
                FROM tour_checkin
                WHERE id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM tour_checkin WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([(int)$id]);
    }

    public function ensureExtendedSchema()
    {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        if (!dbColumnExists('tour_checkin', 'anh_cccd', $this->conn)) {
            $this->conn->exec("ALTER TABLE tour_checkin ADD COLUMN anh_cccd VARCHAR(255) NULL DEFAULT NULL");
        }

        if (!dbColumnExists('tour_checkin', 'anh_passport', $this->conn)) {
            $this->conn->exec("ALTER TABLE tour_checkin ADD COLUMN anh_passport VARCHAR(255) NULL DEFAULT NULL");
        }

        $initialized = true;
    }

    public function insert($data)
    {
        $this->ensureExtendedSchema();

        $columns = [
            'booking_id', 'khach_hang_id', 'lich_khoi_hanh_id',
            'ho_ten', 'so_cmnd', 'so_passport', 'ngay_sinh', 'gioi_tinh',
            'quoc_tich', 'dia_chi', 'so_dien_thoai', 'email',
            'checkin_time', 'checkout_time', 'trang_thai', 'ghi_chu'
        ];
        $values = [
            (int)$data['booking_id'],
            (int)$data['khach_hang_id'],
            (int)$data['lich_khoi_hanh_id'],
            $data['ho_ten'] ?? '',
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['checkin_time'] ?? null,
            $data['checkout_time'] ?? null,
            $data['trang_thai'] ?? 'ChuaCheckIn',
            $data['ghi_chu'] ?? null,
        ];

        if (dbColumnExists('tour_checkin', 'anh_cccd', $this->conn)) {
            $columns[] = 'anh_cccd';
            $values[] = $data['anh_cccd'] ?? null;
        }

        if (dbColumnExists('tour_checkin', 'anh_passport', $this->conn)) {
            $columns[] = 'anh_passport';
            $values[] = $data['anh_passport'] ?? null;
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO tour_checkin (" . implode(', ', $columns) . ") VALUES (" . $placeholders . ")";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE tour_checkin
                SET trang_thai = ?,
                    ghi_chu = ?,
                    checkin_time = ?,
                    checkout_time = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['trang_thai'] ?? 'ChuaCheckIn',
            $data['ghi_chu'] ?? null,
            $data['checkin_time'] ?? null,
            $data['checkout_time'] ?? null,
            (int)$id
        ]);
    }

    public function updateFull($id, $data)
    {
        $this->ensureExtendedSchema();

        $sql = "UPDATE tour_checkin
                SET ho_ten = ?,
                    so_cmnd = ?,
                    so_passport = ?,
                    ngay_sinh = ?,
                    gioi_tinh = ?,
                    quoc_tich = ?,
                    dia_chi = ?,
                    so_dien_thoai = ?,
                    email = ?,
                    ghi_chu = ?";

        $values = [
            $data['ho_ten'] ?? '',
            $data['so_cmnd'] ?? null,
            $data['so_passport'] ?? null,
            $data['ngay_sinh'] ?? null,
            $data['gioi_tinh'] ?? 'Khac',
            $data['quoc_tich'] ?? 'Việt Nam',
            $data['dia_chi'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['ghi_chu'] ?? null,
        ];

        if (dbColumnExists('tour_checkin', 'anh_cccd', $this->conn) && array_key_exists('anh_cccd', $data)) {
            $sql .= ", anh_cccd = ?";
            $values[] = $data['anh_cccd'] ?? null;
        }

        if (dbColumnExists('tour_checkin', 'anh_passport', $this->conn) && array_key_exists('anh_passport', $data)) {
            $sql .= ", anh_passport = ?";
            $values[] = $data['anh_passport'] ?? null;
        }

        $sql .= "
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $values[] = (int)$id;
        return $stmt->execute($values);
    }
}

