-- Bảng thông báo cho hệ thống
CREATE TABLE IF NOT EXISTS thong_bao (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tieu_de VARCHAR(255) NOT NULL COMMENT 'Tiêu đề thông báo',
    noi_dung TEXT COMMENT 'Nội dung chi tiết',
    loai_thong_bao ENUM('ChungChung', 'HDV', 'KhachHang', 'NhanSu', 'NhaCungCap', 'QuanTri') DEFAULT 'ChungChung' COMMENT 'Loại thông báo',
    muc_do_uu_tien ENUM('Thap', 'TrungBinh', 'Cao', 'KhanCap') DEFAULT 'TrungBinh' COMMENT 'Mức độ ưu tiên',
    nguoi_gui_id INT COMMENT 'ID người gửi (từ nguoi_dung)',
    nguoi_nhan_id INT COMMENT 'ID người nhận cụ thể (NULL = gửi tất cả)',
    vai_tro_nhan VARCHAR(50) COMMENT 'Vai trò nhận (HDV, Admin, KhachHang...)',
    trang_thai ENUM('ChuaGui', 'DaGui', 'Loi') DEFAULT 'ChuaGui' COMMENT 'Trạng thái gửi',
    thoi_gian_gui DATETIME COMMENT 'Thời gian gửi thực tế',
    thoi_gian_hen_gui DATETIME COMMENT 'Thời gian hẹn gửi (NULL = gửi ngay)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (nguoi_gui_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL,
    FOREIGN KEY (nguoi_nhan_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    
    INDEX idx_loai (loai_thong_bao),
    INDEX idx_trang_thai (trang_thai),
    INDEX idx_nguoi_nhan (nguoi_nhan_id),
    INDEX idx_vai_tro (vai_tro_nhan),
    INDEX idx_thoi_gian_gui (thoi_gian_gui)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quản lý thông báo hệ thống';

-- Bảng theo dõi trạng thái đọc thông báo của từng người dùng
CREATE TABLE IF NOT EXISTS thong_bao_doc (
    id INT PRIMARY KEY AUTO_INCREMENT,
    thong_bao_id INT NOT NULL COMMENT 'ID thông báo',
    nguoi_dung_id INT NOT NULL COMMENT 'ID người dùng',
    da_doc TINYINT(1) DEFAULT 0 COMMENT '0: Chưa đọc, 1: Đã đọc',
    thoi_gian_doc DATETIME COMMENT 'Thời gian đọc',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (thong_bao_id) REFERENCES thong_bao(id) ON DELETE CASCADE,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_thong_bao_nguoi_dung (thong_bao_id, nguoi_dung_id),
    INDEX idx_nguoi_dung_chua_doc (nguoi_dung_id, da_doc)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Theo dõi trạng thái đọc thông báo';
