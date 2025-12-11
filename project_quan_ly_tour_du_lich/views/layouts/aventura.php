<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>AVENTURA - Life's A Journey</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/aventura.css">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">AVENTURA</div>
            <div class="logo-subtitle">LIFE'S A JOURNEY</div>

            <ul class="nav">
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'Admin'): ?>
                        <li><a href="index.php?act=admin/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>">Trang chủ</a></li>
                        <li>
                            <a href="#" class="nav-toggle">Quản lý tour <span class="expand-icon">+</span></a>
                            <ul class="dropdown-menu" id="toursmenu">
                                <li><a href="index.php?act=admin/quanLyTour">Danh sách tour</a></li>
                                <li><a href="index.php?act=tour/create">Tạo tour mới</a></li>
                                <li><a href="index.php?act=lichKhoiHanh/index">Lịch khởi hành</a></li>
                            </ul>
                        </li>
                        <li><a href="index.php?act=admin/quanLyBooking" class="<?php echo (isset($currentPage) && $currentPage === 'booking') ? 'active' : ''; ?>">Booking</a></li>
                        <li><a href="index.php?act=lichKhoiHanh/index" class="<?php echo (isset($currentPage) && $currentPage === 'lichKhoiHanh') ? 'active' : ''; ?>">Quản lý lịch khởi hành</a></li>
                        <li><a href="index.php?act=admin/nhanSu" class="<?php echo (isset($currentPage) && $currentPage === 'nhanSu') ? 'active' : ''; ?>">Quản lý nhân sự</a></li>
                        <li><a href="index.php?act=admin/quanLyNguoiDung" class="<?php echo (isset($currentPage) && $currentPage === 'nguoiDung') ? 'active' : ''; ?>">Quản lý người dùng</a></li>
                        <li><a href="index.php?act=admin/nhaCungCap" class="<?php echo (isset($currentPage) && $currentPage === 'nhaCungCap') ? 'active' : ''; ?>">Nhà cung cấp</a></li>
                        <li><a href="index.php?act=admin/baoCaoTaiChinh" class="<?php echo (isset($currentPage) && $currentPage === 'baoCaoTaiChinh') ? 'active' : ''; ?>">Báo cáo tài chính</a></li>
                        <li><a href="index.php?act=admin/danhGia" class="<?php echo (isset($currentPage) && $currentPage === 'danhGia') ? 'active' : ''; ?>">Đánh giá & Phản hồi</a></li>
                    <?php elseif ($_SESSION['role'] === 'HDV'): ?>
                        <li><a href="index.php?act=hdv/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>">Trang chủ</a></li>
                        <li><a href="index.php?act=hdv/lichLamViec" class="<?php echo (isset($currentPage) && $currentPage === 'lichLamViec') ? 'active' : ''; ?>">Lịch làm việc</a></li>
                        <li><a href="index.php?act=hdv/tours" class="<?php echo (isset($currentPage) && $currentPage === 'tours') ? 'active' : ''; ?>">Tour của tôi</a></li>
                        <li><a href="index.php?act=hdv/nhatKy" class="<?php echo (isset($currentPage) && $currentPage === 'nhatKy') ? 'active' : ''; ?>">Nhật ký tour</a></li>
                        <li><a href="index.php?act=hdv/yeuCauDacBiet" class="<?php echo (isset($currentPage) && $currentPage === 'yeuCauDacBiet') ? 'active' : ''; ?>">Yêu cầu đặc biệt</a></li>
                    <?php elseif ($_SESSION['role'] === 'KhachHang'): ?>
                        <li><a href="index.php?act=khachHang/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>">Trang chủ</a></li>
                        <li><a href="index.php?act=khachHang/danhSachTour" class="<?php echo (isset($currentPage) && $currentPage === 'tours') ? 'active' : ''; ?>">Danh sách tour</a></li>
                        <li><a href="index.php?act=khachHang/traCuu" class="<?php echo (isset($currentPage) && $currentPage === 'traCuu') ? 'active' : ''; ?>">Tra cứu booking</a></li>
                        <li><a href="index.php?act=khachHang/yeuCauTour" class="<?php echo (isset($currentPage) && $currentPage === 'yeuCauTour') ? 'active' : ''; ?>">Yêu cầu tour</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?act=auth/logout">Đăng xuất</a></li>
                <?php else: ?>
                    <li><a href="index.php?act=tour/index">Trang chủ</a></li>
                    <li><a href="index.php?act=auth/login">Đăng nhập</a></li>
                    <li><a href="index.php?act=auth/register">Đăng ký</a></li>
                <?php endif; ?>
            </ul>

            <div class="text-widget">
                <strong>TEXT WIDGET</strong>
                <p>The Text Widget allows you to add text and HTML to your sidebar. It's the most popular widget because of its power and flexibility.</p>
            </div>

            <div class="social-icons">
                <a href="#facebook">f</a>
                <a href="#twitter">𝕏</a>
                <a href="#instagram">📷</a>
                <a href="#pinterest">📌</a>
                <a href="#youtube">▶</a>
                <a href="#linkedin">in</a>
                <a href="#vimeo">▶</a>
                <a href="#tumblr">⚫</a>
                <a href="#telegram">✈</a>
            </div>

            <div class="copyright">© <?php echo date('Y'); ?> Aventura. All Rights Reserved</div>
        </aside>

        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <div class="header-item">
                        <span>☎</span>
                        <a href="tel:+1-888-665-5553">Call Center: +1-888-665-5553</a>
                    </div>
                    <div class="header-item">
                        <span>✉</span>
                        <a href="mailto:info@aventura.com">info@aventura.com</a>
                    </div>
                </div>
                <div class="header-right">
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <div class="header-item">
                            <span>👤</span>
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="header-item">
                        <span>📍</span>
                        <span>8 Boulevard...</span>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="content-area">
                <?php echo $content; ?>
            </div>
        </div>
    </div>

    <script>
        // Toggle dropdown menus
        document.querySelectorAll('.nav-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const menu = this.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('show');
                    const icon = this.querySelector('.expand-icon');
                    if (icon) {
                        icon.textContent = menu.classList.contains('show') ? '−' : '+';
                    }
                }
            });
        });

        // Close menus when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
                document.querySelectorAll('.expand-icon').forEach(icon => {
                    icon.textContent = '+';
                });
            }
        });
    </script>
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

