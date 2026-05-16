CREATE TABLE IF NOT EXISTS support_tickets (
    id INT NOT NULL AUTO_INCREMENT,
    ticket_code VARCHAR(24) NOT NULL,
    khach_hang_id INT NOT NULL,
    booking_id INT NULL,
    subject VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'General',
    priority ENUM('Thap','TrungBinh','Cao','KhanCap') NOT NULL DEFAULT 'TrungBinh',
    status ENUM('Open','InProgress','WaitingCustomer','Resolved','Closed') NOT NULL DEFAULT 'Open',
    sla_due_at DATETIME NULL,
    last_reply_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_support_ticket_code (ticket_code),
    KEY idx_support_tickets_khach (khach_hang_id),
    KEY idx_support_tickets_status (status),
    KEY idx_support_tickets_sla_due (sla_due_at),
    KEY idx_support_tickets_booking (booking_id),
    CONSTRAINT fk_support_tickets_khach_hang FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(khach_hang_id) ON DELETE CASCADE,
    CONSTRAINT fk_support_tickets_booking FOREIGN KEY (booking_id) REFERENCES booking(booking_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_ticket_messages (
    id INT NOT NULL AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    sender_id INT NULL,
    sender_role ENUM('KhachHang','Admin','System') NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_support_ticket_messages_ticket (ticket_id),
    KEY idx_support_ticket_messages_created (created_at),
    CONSTRAINT fk_support_ticket_messages_ticket FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;