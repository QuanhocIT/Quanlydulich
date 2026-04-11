-- Create payments and payment_logs base tables if missing (legacy safety migration).

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT(11) NOT NULL AUTO_INCREMENT,
    booking_id INT(11) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_method ENUM('VNPay','Momo','Paypal','ChuyenKhoan','TienMat','TheTinDung','ViDienTu') NOT NULL DEFAULT 'VNPay',
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('TaoMoi','DangXuLy','ThanhCong','ThatBai','HetHan','DaDoiSoat') NOT NULL DEFAULT 'DangXuLy',
    note TEXT DEFAULT NULL,
    PRIMARY KEY (payment_id),
    KEY booking_id (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_logs (
    log_id INT(11) NOT NULL AUTO_INCREMENT,
    payment_id INT(11) NOT NULL,
    action VARCHAR(100) NOT NULL,
    log_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note TEXT DEFAULT NULL,
    PRIMARY KEY (log_id),
    KEY payment_id (payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'DONE: payments/payment_logs base tables migration executed' AS migration_message;
