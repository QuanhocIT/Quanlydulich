<?php
$act = $_GET['act'] ?? 'hdv/dashboard';
$currentHdvTab = 'dashboard';

$tabMap = [
    'hdv/dashboard' => 'dashboard',
    'hdv/tours' => 'tours',
    'hdv/tour_detail' => 'tours',
    'hdv/lichLamViec' => 'schedule',
    'hdv/lich_trinh_chi_tiet' => 'schedule',
    'hdv/danhSachKhach' => 'guests',
    'hdv/khach' => 'guests',
    'hdv/checkInKhach' => 'checkin',
    'hdv/checkin' => 'checkin',
    'hdv/nhatKyTour' => 'journal',
    'hdv/nhat_ky' => 'journal',
    'hdv/quanLyYeuCauDacBiet' => 'requests',
    'hdv/yeuCauDacBiet' => 'requests',
    'hdv/phanHoi' => 'feedback',
    'hdv/danhGia' => 'feedback',
    'hdv/notifications' => 'notifications',
    'hdv/profile' => 'profile',
    'hdv/thanhToanCongNo' => 'payments',
];

if (isset($tabMap[$act])) {
    $currentHdvTab = $tabMap[$act];
}

// Lấy thông tin HDV & người dùng đang đăng nhập
$userId = (int)($_SESSION['user_id'] ?? 0);
$guideProfile = null;
if ($userId > 0) {
    try {
        $db = connectDB();
        $stmt = $db->prepare(
            "SELECT ns.*, nd.ho_ten, nd.email, nd.so_dien_thoai, nd.avatar 
             FROM nhan_su ns 
             LEFT JOIN nguoi_dung nd ON ns.nguoi_dung_id = nd.id 
             WHERE ns.nguoi_dung_id = ? AND ns.vai_tro = 'HDV' LIMIT 1"
        );
        $stmt->execute([$userId]);
        $guideProfile = $stmt->fetch(PDO::FETCH_ASSOC);

        // Lấy số thông báo chưa đọc nếu chưa được truyền từ controller
        if (!isset($notifications_count) && !empty($guideProfile['nhan_su_id'])) {
            $stmtNotif = $db->prepare("SELECT COUNT(*) FROM thong_bao_hdv WHERE nhan_su_id = ? AND da_xem = 0");
            $stmtNotif->execute([(int)$guideProfile['nhan_su_id']]);
            $notifications_count = (int)$stmtNotif->fetchColumn();
        }
    } catch (Exception $e) {
        $guideProfile = null;
    }
}

$guideName = htmlspecialchars(
    $hdv_info['ho_ten']
    ?? $nhanSu['ho_ten']
    ?? $guideProfile['ho_ten']
    ?? $_SESSION['user_name']
    ?? 'Hướng dẫn viên'
);

$guideEmail = htmlspecialchars(
    $hdv_info['email']
    ?? $nhanSu['email']
    ?? $guideProfile['email']
    ?? $_SESSION['email']
    ?? 'guide@tour.com'
);

$guideAvatar = !empty($hdv_info['avatar'])
    ? $hdv_info['avatar']
    : (!empty($guideProfile['avatar'])
        ? $guideProfile['avatar']
        : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80');

$guideStaffId = (int)($hdv_info['nhan_su_id'] ?? $nhanSu['nhan_su_id'] ?? $guideProfile['nhan_su_id'] ?? 0);
$unreadCount = (int)($notifications_count ?? 0);
?>
<!-- 1. TOP NAVBAR (Đồng bộ chuẩn Image 1 cho HDV) -->
<header class="site-header hdv-header">
    <div class="header-container">
        <!-- Brand Logo -->
        <a href="index.php?act=hdv/dashboard" class="brand-block">
            <div class="brand-logo-icon hdv-brand-icon">
                <i class="bi bi-compass"></i>
            </div>
            <div class="brand-text">
                <h1>DuLichPro <span class="badge-role-tag">HDV</span></h1>
                <p>Cổng điều hành & Hướng dẫn viên</p>
            </div>
        </a>

        <!-- Navigation Links -->
        <ul class="site-nav">
            <li><a class="<?php echo $currentHdvTab === 'dashboard' ? 'active' : ''; ?>" href="index.php?act=hdv/dashboard"><i class="bi bi-house-door"></i> Tổng quan</a></li>
            <li><a class="<?php echo $currentHdvTab === 'tours' ? 'active' : ''; ?>" href="index.php?act=hdv/tours"><i class="bi bi-map"></i> Tour</a></li>
            <li><a class="<?php echo $currentHdvTab === 'schedule' ? 'active' : ''; ?>" href="index.php?act=hdv/lichLamViec"><i class="bi bi-calendar-week"></i> Lịch</a></li>
            <li><a class="<?php echo $currentHdvTab === 'guests' ? 'active' : ''; ?>" href="index.php?act=hdv/danhSachKhach"><i class="bi bi-people"></i> Khách đoàn</a></li>
            <li><a class="<?php echo $currentHdvTab === 'checkin' ? 'active' : ''; ?>" href="index.php?act=hdv/checkInKhach"><i class="bi bi-check2-square"></i> Check-in</a></li>
            <li><a class="<?php echo $currentHdvTab === 'journal' ? 'active' : ''; ?>" href="index.php?act=hdv/nhatKyTour"><i class="bi bi-journal-text"></i> Nhật ký</a></li>
            <li><a class="<?php echo $currentHdvTab === 'requests' ? 'active' : ''; ?>" href="index.php?act=hdv/quanLyYeuCauDacBiet"><i class="bi bi-stars"></i> Yêu cầu</a></li>
            <li><a class="<?php echo $currentHdvTab === 'feedback' ? 'active' : ''; ?>" href="index.php?act=hdv/phanHoi"><i class="bi bi-chat-left-dots"></i> Phản hồi</a></li>
        </ul>

        <!-- Header Right Actions -->
        <div class="header-actions">
            <!-- Thông báo bell -->
            <a href="index.php?act=hdv/notifications" class="btn-icon-round" title="Thông báo điều hành">
                <i class="bi bi-bell"></i>
                <span id="hdvNavBadge" class="badge-count" style="<?php echo $unreadCount > 0 ? '' : 'display:none;'; ?>">
                    <?php echo $unreadCount; ?>
                </span>
            </a>

            <!-- User Profile Dropdown Pill (Chuẩn Image 1) -->
            <div class="user-dropdown-container">
                <a href="index.php?act=hdv/profile" class="user-profile-btn" title="Hồ sơ HDV">
                    <img src="<?php echo htmlspecialchars($guideAvatar); ?>" alt="Avatar" class="user-profile-avatar" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=200&h=200&q=80';">
                    <span class="user-profile-name"><?php echo $guideName; ?></span>
                    <i class="bi bi-chevron-down" style="font-size: 11px; color: #64748b;"></i>
                </a>
                <div class="user-dropdown-menu">
                    <div class="user-dropdown-header">
                        <strong><?php echo $guideName; ?></strong>
                        <small><?php echo $guideEmail; ?></small>
                        <div class="user-role-badge mt-1"><i class="bi bi-person-badge"></i> HDV #<?php echo $guideStaffId > 0 ? $guideStaffId : 'N/A'; ?></div>
                    </div>
                    <a href="index.php?act=hdv/profile"><i class="bi bi-person-circle"></i> Hồ sơ cá nhân</a>
                    <a href="index.php?act=hdv/thanhToanCongNo"><i class="bi bi-wallet2"></i> Lương & Công nợ</a>
                    <a href="index.php?act=hdv/notifications"><i class="bi bi-bell"></i> Thông báo điều hành</a>
                    <div class="user-dropdown-divider"></div>
                    <a href="index.php?act=auth/logout" class="text-danger" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                </div>
            </div>

            <!-- Nút tắt đăng xuất nhanh (Có xác nhận) -->
            <a href="index.php?act=auth/logout" class="btn-icon-round btn-logout-shortcut" title="Đăng xuất nhanh" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</header>
