<?php 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php?act=auth/login');
    exit;
}
$pageTitle = 'Check-in Khách';
$currentPage = 'checkin';
ob_start();
?>
<style>
        .checkin-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .checkin-form {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 4px;
            backdrop-filter: blur(10px);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-light);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            font-size: 14px;
            background: rgba(30, 30, 30, 0.7);
            color: var(--text-light);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            background: rgba(30, 30, 30, 0.9);
            border-color: var(--accent-gold);
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
        }
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .booking-info {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        .booking-info h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: var(--text-light);
        }
        .booking-info p {
            margin: 5px 0;
            font-size: 14px;
            color: var(--text-light);
        }
        .btn-submit {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
            padding: 12px 30px;
            border: 1px solid rgba(40, 167, 69, 0.5);
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
            font-weight: 500;
        }
        .btn-submit:hover {
            background: rgba(40, 167, 69, 0.5);
        }
        .btn-cancel {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            padding: 12px 30px;
            border: 1px solid rgba(108, 117, 125, 0.5);
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
        }
        .btn-cancel:hover {
            background: rgba(108, 117, 125, 0.5);
        }
        .checkin-status {
            background: rgba(40, 167, 69, 0.2);
            color: #5cb85c;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #5cb85c;
        }
    </style>

<div style="padding: 20px;">
    <div class="checkin-container">
        <div style="margin-bottom: 20px;">
            <a href="<?php echo BASE_URL; ?>index.php?act=admin/dashboard" style="color: #007bff; text-decoration: none;">← Dashboard</a> | 
            <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyTour" style="color: #007bff; text-decoration: none;">Quản lý Tour</a>
        </div>
        <h1><?php echo $checkin ? 'Cập Nhật Check-in' : 'Check-in Khách'; ?></h1>
        
        <?php if ($booking): ?>
            <div class="booking-info">
                <h3>Thông tin booking</h3>
                <p><strong>Mã booking:</strong> <?php echo htmlspecialchars($booking['booking_id'] ?? 'N/A'); ?></p>
                <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($booking['ho_ten'] ?? 'N/A'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email'] ?? 'N/A'); ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($booking['so_dien_thoai'] ?? 'N/A'); ?></p>
                <p><strong>Số người:</strong> <?php echo ($booking['so_nguoi'] ?? 0); ?> người</p>
            </div>
            
            <?php if ($checkin): ?>
                <div class="checkin-status">
                    ✅ Khách đã check-in vào lúc: <?php echo date('d/m/Y H:i', strtotime($checkin['checkin_time'])); ?>
                </div>
            <?php endif; ?>
            
            <div class="checkin-form">
                <form method="POST" action="index.php?act=<?php echo $checkin ? 'admin/updateCheckIn' : 'admin/checkInKhach'; ?>">
                    <?php if ($checkin): ?>
                        <input type="hidden" name="id" value="<?php echo $checkin['id']; ?>">
                    <?php endif; ?>
                    
                    <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanhId; ?>">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                    
                    <div class="form-group">
                        <label for="ho_ten">Họ và tên <span style="color: red;">*</span></label>
                        <input type="text" id="ho_ten" name="ho_ten" 
                               value="<?php echo htmlspecialchars($checkin['ho_ten'] ?? $booking['ho_ten'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="so_cmnd">Số CMND/CCCD</label>
                            <input type="text" id="so_cmnd" name="so_cmnd" 
                                   value="<?php echo htmlspecialchars($checkin['so_cmnd'] ?? ''); ?>" 
                                   placeholder="VD: 001234567890">
                        </div>
                        
                        <div class="form-group">
                            <label for="so_passport">Số Passport</label>
                            <input type="text" id="so_passport" name="so_passport" 
                                   value="<?php echo htmlspecialchars($checkin['so_passport'] ?? ''); ?>" 
                                   placeholder="VD: A12345678">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="so_dien_thoai">Số điện thoại</label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" 
                                   value="<?php echo htmlspecialchars($checkin['so_dien_thoai'] ?? $booking['so_dien_thoai'] ?? ''); ?>" 
                                   placeholder="VD: 0901234567">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($checkin['email'] ?? $booking['email'] ?? ''); ?>" 
                                   placeholder="VD: email@example.com">
                        </div>
                    </div>
                    
                    <?php if ($checkin): ?>
                        <div class="form-group">
                            <label for="trang_thai">Trạng thái</label>
                            <select id="trang_thai" name="trang_thai">
                                <option value="DaCheckIn" <?php echo ($checkin['trang_thai'] ?? '') === 'DaCheckIn' ? 'selected' : ''; ?>>
                                    Đã check-in
                                </option>
                                <option value="DaCheckOut" <?php echo ($checkin['trang_thai'] ?? '') === 'DaCheckOut' ? 'selected' : ''; ?>>
                                    Đã check-out
                                </option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="ghi_chu">Ghi chú</label>
                        <textarea id="ghi_chu" name="ghi_chu" placeholder="Nhập ghi chú nếu có..."><?php echo htmlspecialchars($checkin['ghi_chu'] ?? ''); ?></textarea>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-submit">
                            <?php echo $checkin ? '✅ Cập nhật' : '✅ Check-in'; ?>
                        </button>
                        <a href="index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                           class="btn-cancel">
                            ← Quay lại
                        </a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="checkin-form">
                <p style="color: #dc3545;">Không tìm thấy thông tin booking.</p>
                <a href="index.php?act=admin/quanLyBooking" class="btn-cancel">← Quay lại</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
