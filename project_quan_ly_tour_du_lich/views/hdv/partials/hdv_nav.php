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

$guideName = htmlspecialchars(
    $hdv_info['ho_ten']
    ?? $nhanSu['ho_ten']
    ?? $_SESSION['user_name']
    ?? 'Hướng dẫn viên'
);
?>
<div class="hdv-topbar">
    <div class="hdv-topbar-inner">
        <div class="hdv-topbar-brand">
            <div class="hdv-topbar-icon">
                <i class="bi bi-compass"></i>
            </div>
            <div>
                <span class="hdv-topbar-label">Guide Workspace</span>
                <span class="hdv-topbar-title"><?php echo $guideName; ?></span>
            </div>
        </div>

        <ul class="hdv-nav-list">
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'dashboard' ? 'active' : ''; ?>" href="index.php?act=hdv/dashboard"><i class="bi bi-house-door"></i> Dashboard</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'tours' ? 'active' : ''; ?>" href="index.php?act=hdv/tours"><i class="bi bi-map"></i> Tour</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'schedule' ? 'active' : ''; ?>" href="index.php?act=hdv/lichLamViec"><i class="bi bi-calendar-week"></i> Lịch</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'guests' ? 'active' : ''; ?>" href="index.php?act=hdv/danhSachKhach"><i class="bi bi-people"></i> Khách</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'checkin' ? 'active' : ''; ?>" href="index.php?act=hdv/checkInKhach"><i class="bi bi-check2-square"></i> Check-in</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'journal' ? 'active' : ''; ?>" href="index.php?act=hdv/nhatKyTour"><i class="bi bi-journal-text"></i> Nhật ký</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'requests' ? 'active' : ''; ?>" href="index.php?act=hdv/quanLyYeuCauDacBiet"><i class="bi bi-stars"></i> Yêu cầu</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'feedback' ? 'active' : ''; ?>" href="index.php?act=hdv/phanHoi"><i class="bi bi-chat-left-dots"></i> Phản hồi</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'notifications' ? 'active' : ''; ?>" href="index.php?act=hdv/notifications"><i class="bi bi-bell"></i> Thông báo</a></li>
            <li><a class="hdv-nav-link <?php echo $currentHdvTab === 'profile' ? 'active' : ''; ?>" href="index.php?act=hdv/profile"><i class="bi bi-person-circle"></i> Hồ sơ</a></li>
            <li><a class="hdv-nav-link logout" href="index.php?act=auth/logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
        </ul>
    </div>
</div>
