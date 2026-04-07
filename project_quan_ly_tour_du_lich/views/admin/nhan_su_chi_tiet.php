<?php
$pageTitle = 'Sơ yếu lý lịch - ' . htmlspecialchars($nhanSu['ho_ten'] ?? 'Nhân sự');
$currentPage = 'nhanSu';
ob_start();
?>
<style>
        .profile-header {
            background: rgba(102, 126, 234, 0.3);
            border: 1px solid rgba(102, 126, 234, 0.5);
            color: var(--text-light);
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            object-fit: cover;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .info-section {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }
        .info-section h5 {
            color: var(--accent-gold);
            border-bottom: 2px solid var(--accent-gold);
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .info-row {
            display: flex;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 200px;
            flex-shrink: 0;
        }
        .info-value {
            color: var(--text-light);
            flex: 1;
        }
        .info-value a {
            color: #4da3ff;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        .status-active {
            background-color: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
        }
        .status-locked {
            background-color: rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        .print-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-primary {
            background: rgba(13, 110, 253, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-warning {
            background: rgba(255, 193, 7, 0.3) !important;
            color: #ffc107 !important;
        }
        .bg-secondary {
            background: rgba(108, 117, 125, 0.3) !important;
            color: #adb5bd !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        .btn-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(108, 117, 125, 0.5);
        }
        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.5);
        }
        .btn-primary {
            background: rgba(13, 110, 253, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(13, 110, 253, 0.5);
        }
        .btn-primary:hover {
            background: rgba(13, 110, 253, 0.5);
        }
        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        @media print {
            .no-print { display: none; }
            .profile-header { background: rgba(102, 126, 234, 0.5) !important; }
        }
    </style>

<div style="padding: 20px;">

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <a href="index.php?act=admin/nhanSu" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách
        </a>
    <?php else: ?>
        <!-- Profile Header -->
        <div class="profile-header">
            <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
                <div style="display: grid; grid-template-columns: auto 1fr; gap: 30px; align-items: center;">
                    <div style="text-align: center;">
                        <?php if (!empty($nhanSu['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($nhanSu['avatar']); ?>" alt="Avatar" class="profile-avatar">
                        <?php else: ?>
                            <div class="profile-avatar" style="display: flex; align-items: center; justify-content: center; background: rgba(45, 45, 45, 0.5);">
                                <i class="bi bi-person-fill" style="font-size: 4rem; color: var(--accent-gold);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 class="mb-2"><?php echo htmlspecialchars($nhanSu['ho_ten'] ?? 'N/A'); ?></h2>
                        <p class="mb-1 fs-5">
                            <i class="bi bi-briefcase"></i> 
                            <strong>Vai trò:</strong> <?php echo htmlspecialchars($nhanSu['vai_tro'] ?? 'N/A'); ?>
                        </p>
                        <p class="mb-0">
                            <span class="status-badge <?php echo ($nhanSu['trang_thai'] ?? '') === 'HoatDong' ? 'status-active' : 'status-locked'; ?>">
                                <?php echo ($nhanSu['trang_thai'] ?? '') === 'HoatDong' ? 'Đang hoạt động' : 'Bị khóa'; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Thông tin cá nhân -->
                <div>
                    <div class="info-section">
                        <h5><i class="bi bi-person-badge"></i> Thông tin cá nhân</h5>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-hash"></i> Mã nhân sự:</div>
                            <div class="info-value"><?php echo htmlspecialchars($nhanSu['nhan_su_id'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-person"></i> Họ và tên:</div>
                            <div class="info-value"><?php echo htmlspecialchars($nhanSu['ho_ten'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-envelope"></i> Email:</div>
                            <div class="info-value">
                                <?php if (!empty($nhanSu['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($nhanSu['email']); ?>">
                                        <?php echo htmlspecialchars($nhanSu['email']); ?>
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-telephone"></i> Điện thoại:</div>
                            <div class="info-value">
                                <?php if (!empty($nhanSu['so_dien_thoai'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($nhanSu['so_dien_thoai']); ?>">
                                        <?php echo htmlspecialchars($nhanSu['so_dien_thoai']); ?>
                                    </a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-person-circle"></i> Tên đăng nhập:</div>
                            <div class="info-value"><?php echo htmlspecialchars($nhanSu['ten_dang_nhap'] ?? 'N/A'); ?></div>
                        </div>
                    </div>

                    <!-- Sức khỏe -->
                    <div class="info-section">
                        <h5><i class="bi bi-heart-pulse"></i> Tình trạng sức khỏe</h5>
                        <div class="info-row">
                            <div class="info-value">
                                <?php echo !empty($nhanSu['suc_khoe']) ? nl2br(htmlspecialchars($nhanSu['suc_khoe'])) : '<em class="text-muted">Chưa cập nhật</em>'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin nghiệp vụ -->
                <div>
                    <div class="info-section">
                        <h5><i class="bi bi-award"></i> Trình độ & Chứng chỉ</h5>
                        <div class="info-row">
                            <div class="info-value">
                                <?php echo !empty($nhanSu['chung_chi']) ? nl2br(htmlspecialchars($nhanSu['chung_chi'])) : '<em class="text-muted">Chưa cập nhật</em>'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h5><i class="bi bi-translate"></i> Ngoại ngữ</h5>
                        <div class="info-row">
                            <div class="info-value">
                                <?php echo !empty($nhanSu['ngon_ngu']) ? nl2br(htmlspecialchars($nhanSu['ngon_ngu'])) : '<em class="text-muted">Chưa cập nhật</em>'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h5><i class="bi bi-graph-up"></i> Kinh nghiệm làm việc</h5>
                        <div class="info-row">
                            <div class="info-value">
                                <?php echo !empty($nhanSu['kinh_nghiem']) ? nl2br(htmlspecialchars($nhanSu['kinh_nghiem'])) : '<em class="text-muted">Chưa cập nhật</em>'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin tài khoản -->
            <div style="margin-top: 30px;">
                <div class="info-section">
                    <h5><i class="bi bi-shield-lock"></i> Thông tin tài khoản</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-key"></i> Vai trò hệ thống:</div>
                                    <div class="info-value">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($nhanSu['vai_tro_nguoi_dung'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-calendar-check"></i> Ngày tạo tài khoản:</div>
                                    <div class="info-value">
                                        <?php 
                                        if (!empty($nhanSu['ngay_tao'])) {
                                            $date = new DateTime($nhanSu['ngay_tao']);
                                            echo $date->format('d/m/Y H:i');
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </div>
                                </div>
                        </div>
                        <div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-toggle-on"></i> Trạng thái:</div>
                                    <div class="info-value">
                                        <span class="status-badge <?php echo ($nhanSu['trang_thai'] ?? '') === 'HoatDong' ? 'status-active' : 'status-locked'; ?>">
                                            <i class="bi bi-<?php echo ($nhanSu['trang_thai'] ?? '') === 'HoatDong' ? 'check-circle' : 'x-circle'; ?>"></i>
                                            <?php echo ($nhanSu['trang_thai'] ?? '') === 'HoatDong' ? 'Hoạt động' : 'Bị khóa'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label"><i class="bi bi-star"></i> Quyền cao cấp:</div>
                                    <div class="info-value">
                                        <?php echo !empty($nhanSu['quyen_cap_cao']) ? '<span class="badge bg-warning text-dark">Có</span>' : '<span class="badge bg-secondary">Không</span>'; ?>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="no-print" style="text-align: center; margin-top: 30px;">
                <a href="index.php?act=admin/nhanSu" class="btn btn-secondary btn-lg">
                    <i class="bi bi-arrow-left"></i> Quay lại danh sách
                </a>
                <button onclick="window.print()" class="btn btn-primary btn-lg">
                    <i class="bi bi-printer"></i> In sơ yếu lý lịch
                </button>
            </div>
        </div>

        <!-- Floating Print Button -->
        <button onclick="window.print()" class="btn btn-primary print-btn no-print" title="In sơ yếu lý lịch">
            <i class="bi bi-printer-fill" style="font-size: 1.5rem;"></i>
        </button>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
