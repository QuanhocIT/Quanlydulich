<?php
$paymentNotificationCount = 0;
$reviewNotificationCount = 0;
$dashboardNotificationCount = 0;
$soundNotificationEnabled = true;
$currentRole = currentUserRole();
$isAdminRole = hasRole('Admin');
$forceSidebarHiddenOnLoad = false;
if ($isAdminRole && !empty($_SESSION['admin_sidebar_start_hidden_once'])) {
    $forceSidebarHiddenOnLoad = true;
    unset($_SESSION['admin_sidebar_start_hidden_once']);
}

$bodyClasses = [];
if (isset($currentPage)) {
    $bodyClasses[] = 'page-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$currentPage);
}
if ($currentRole !== null) {
    $roleClass = strtolower((string)$currentRole);
    $bodyClasses[] = 'role-' . preg_replace('/[^a-z0-9_-]/', '', $roleClass);
}
if ($isAdminRole) {
    $bodyClasses[] = 'is-admin';
}

if (!isset($content)) {
    $content = '';
}

$realtimeWsEnabled = realtimeWebSocketEnabled();
$realtimeWsUrl = '';
$realtimeWsToken = '';
if ($realtimeWsEnabled && isset($_SESSION['user_id']) && $currentRole !== null) {
    $realtimeWsUrl = realtimeWebSocketPublicUrl();
    $realtimeWsToken = buildRealtimeAuthToken((int)$_SESSION['user_id'], (string)$currentRole, 'notifications');
}


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-global-token" content="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php if (!empty($metaRefreshSeconds) && (int)$metaRefreshSeconds > 0): ?>
        <meta http-equiv="refresh" content="<?php echo (int)$metaRefreshSeconds; ?>">
    <?php endif; ?>
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>AVENTURA - Life's A Journey</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/aventura.css?v=<?php echo rawurlencode(ASSET_VERSION); ?>">
    <?php if (isset($currentPage) && $currentPage === 'baoCaoTaiChinh'): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/finance-report-unified.css?v=<?php echo rawurlencode(ASSET_VERSION); ?>">
    <?php endif; ?>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/images/momo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
        .table-auto-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 12px 0 0;
        }

        .table-auto-pagination-meta {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.72);
        }

        .table-auto-pagination-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .table-auto-pagination-btn {
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            border-radius: 999px;
            padding: 4px 11px;
            font-size: 12px;
            line-height: 1.25;
            cursor: pointer;
            transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
        }

        .table-auto-pagination-btn:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.34);
            transform: translateY(-1px);
        }

        .table-auto-pagination-btn.is-active {
            background: #32d4af;
            border-color: #32d4af;
            color: #082a24;
            font-weight: 700;
        }

        .table-auto-pagination-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body class="<?php echo htmlspecialchars(trim(implode(' ', array_filter($bodyClasses))), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="logo">AVENTURA</div>
            <div class="logo-subtitle">LIFE'S A JOURNEY</div>
            <button type="button" class="mobile-sidebar-close" id="mobileSidebarClose" aria-label="Đóng menu điều hướng">
                <i class="bi bi-x-lg"></i>
            </button>
            <?php if ($isAdminRole): ?>
                <div id="realtimeStatus" class="realtime-status is-connecting" title="Trạng thái kết nối thông báo realtime">
                    <span id="realtimeStatusDot" class="realtime-status-dot"></span>
                    <span id="realtimeStatusText">Đang kết nối realtime...</span>
                </div>
            <?php endif; ?>
            <ul class="nav">
                <li class="nav-group-label">NAVIGATION</li>
                <?php if ($currentRole !== null): ?>
                    <?php if ($isAdminRole): ?>
                        <li>
                            <a href="index.php?act=admin/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>" title="Trang chủ">
                                <span class="nav-icon-bg"><i class="bi bi-house-door"></i></span> <span class="nav-text">Dashboard</span>
                                <span id="dashboardNavBadge" class="nav-badge" title="Có <?php echo $dashboardNotificationCount; ?> thông báo mới"<?php if ($dashboardNotificationCount <= 0): ?> style="display:none"<?php endif; ?>><?php echo $dashboardNotificationCount; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php?act=admin/quanLyLuongThuong" class="<?php echo (isset($currentPage) && $currentPage === 'luongThuong') ? 'active' : ''; ?>" title="Lương thưởng">
                                <span class="nav-icon-bg"><i class="bi bi-cash-coin"></i></span> <span class="nav-text">Lương thưởng</span>
                            </a>
                        </li>
                        <li class="nav-parent">
                            <a href="#" class="nav-toggle" title="Quản lý tour"><span class="nav-icon-bg"><i class="bi bi-geo-alt"></i></span> <span class="nav-text">Quản lý tour</span> <span class="expand-icon">&#9662;</span></a>
                            <div class="nav-child-menu" hidden>
                                <a href="index.php?act=admin/quanLyTour" title="Danh sách tour"><span class="nav-child-bar"></span>- Danh sách tour</a>
                                <a href="index.php?act=tour/create" title="Tạo tour mới"><span class="nav-child-bar"></span>- Tạo tour mới</a>
                                <a href="index.php?act=lichKhoiHanh/index" title="Lịch khởi hành"><span class="nav-child-bar"></span>- Lịch khởi hành</a>
                            </div>
                        </li>
                        <li class="nav-parent">
                            <a href="#" class="nav-toggle <?php echo (isset($currentPage) && $currentPage === 'booking') ? 'active' : ''; ?>" title="Quản lý Booking"><span class="nav-icon-bg"><i class="bi bi-journal-bookmark"></i></span> <span class="nav-text">Quản lý Booking</span> <span class="expand-icon">&#9662;</span></a>
                            <div class="nav-child-menu" hidden>
                                <a href="index.php?act=admin/quanLyBooking" title="Danh sách booking"><span class="nav-child-bar"></span>- Danh sách booking</a>
                                <a href="index.php?act=admin/bookingDaHoanThanh" title="Booking đã hoàn thành"><span class="nav-child-bar"></span>- Booking đã hoàn thành</a>
                                <a href="index.php?act=admin/quanLyYeuCauTour" title="Yêu cầu đặt tour"><span class="nav-child-bar"></span>- Yêu cầu đặt tour</a>
                                <a href="index.php?act=booking/datTourChoKhach" title="Đặt tour cho khách"><span class="nav-child-bar"></span>- Đặt tour cho khách</a>
                                <a href="index.php?act=admin/lichSuXoaBooking" title="Lịch sử xóa booking"><span class="nav-child-bar"></span>- Lịch sử xóa booking</a>
                            </div>
                        </li>
                        <li><a href="index.php?act=lichKhoiHanh/index" class="<?php echo (isset($currentPage) && $currentPage === 'lichKhoiHanh') ? 'active' : ''; ?>" title="Quản lý lịch khởi hành"><span class="nav-icon-bg"><i class="bi bi-calendar3"></i></span> <span class="nav-text">Quản lý lịch khởi hành</span></a></li>
                        <li><a href="index.php?act=admin/nhanSu" class="<?php echo (isset($currentPage) && $currentPage === 'nhanSu') ? 'active' : ''; ?>" title="Quản lý nhân sự"><span class="nav-icon-bg"><i class="bi bi-people"></i></span> <span class="nav-text">Quản lý nhân sự</span></a></li>
                        <li><a href="index.php?act=admin/quanLyNguoiDung" class="<?php echo (isset($currentPage) && $currentPage === 'nguoiDung') ? 'active' : ''; ?>" title="Quản lý người dùng"><span class="nav-icon-bg"><i class="bi bi-person-lines-fill"></i></span> <span class="nav-text">Quản lý người dùng</span></a></li>
                        <li><a href="index.php?act=admin/nhaCungCap" class="<?php echo (isset($currentPage) && $currentPage === 'nhaCungCap') ? 'active' : ''; ?>" title="Nhà cung cấp"><span class="nav-icon-bg"><i class="bi bi-truck"></i></span> <span class="nav-text">Nhà cung cấp</span></a></li>
                        <li class="nav-group-label">ADMIN PANEL</li>
                           <li><a href="index.php?act=admin/invoices" class="<?php echo (isset($currentPage) && $currentPage === 'invoices') ? 'active' : ''; ?>" title="Quản lý hóa đơn"><span class="nav-icon-bg"><i class="bi bi-receipt"></i></span> <span class="nav-text">Quản lý hóa đơn</span></a></li>
                           <li><a href="index.php?act=admin/payments" class="<?php echo (isset($currentPage) && $currentPage === 'payments') ? 'active' : ''; ?>" title="Quản lý thanh toán"><span class="nav-icon-bg"><i class="bi bi-credit-card"></i></span> <span class="nav-text">Quản lý thanh toán</span><span id="paymentNavBadge" class="nav-badge" title="Có <?php echo $paymentNotificationCount; ?> thanh toán mới"<?php if ($paymentNotificationCount <= 0): ?> style="display:none"<?php endif; ?>><?php echo $paymentNotificationCount; ?></span></a></li>
                        <li class="nav-parent">
                            <a href="#" class="nav-toggle <?php echo (isset($currentPage) && $currentPage === 'baoCaoTaiChinh') ? 'active' : ''; ?>" title="Báo cáo tài chính"><span class="nav-icon-bg"><i class="bi bi-bar-chart"></i></span> <span class="nav-text">Báo cáo tài chính</span> <span class="expand-icon">&#9662;</span></a>
                            <div class="nav-child-menu" hidden>
                                <a href="index.php?act=admin/lichSuGiaoDich" title="Lịch sử giao dịch"><span class="nav-child-bar"></span>- Lịch sử giao dịch</a>
                                <a href="index.php?act=admin/thuChiTour" title="Thu chi từng tour"><span class="nav-child-bar"></span>- Thu chi từng tour</a>
                                <a href="index.php?act=admin/congNo" title="Công nợ"><span class="nav-child-bar"></span>- Công nợ</a>
                                <a href="index.php?act=admin/laiLoTour" title="Lãi lỗ từng tour"><span class="nav-child-bar"></span>- Lãi lỗ từng tour</a>
                                <a href="index.php?act=admin/duToanTour" title="Dự toán tour"><span class="nav-child-bar"></span>- Dự toán tour</a>
                                <a href="index.php?act=admin/soSanhDuToan" title="So sánh dự toán"><span class="nav-child-bar"></span>- So sánh dự toán</a>
                            </div>
                        </li>
                        <li><a href="index.php?act=admin/danhGia" class="<?php echo (isset($currentPage) && ($currentPage === 'danhGia' || $currentPage === 'danh_gia')) ? 'active' : ''; ?>" title="Đánh giá & Phản hồi"><span class="nav-icon-bg"><i class="bi bi-chat-dots"></i></span> <span class="nav-text">Đánh giá & Phản hồi</span><span id="reviewNavBadge" class="nav-badge" title="Có <?php echo $reviewNotificationCount; ?> đánh giá mới"<?php if ($reviewNotificationCount <= 0): ?> style="display:none"<?php endif; ?>><?php echo $reviewNotificationCount; ?></span></a></li>
                        <li><a href="index.php?act=admin/automationDashboard" class="<?php echo (isset($currentPage) && $currentPage === 'automation') ? 'active' : ''; ?>" title="Trung tâm tự động hóa"><span class="nav-icon-bg"><i class="bi bi-cpu"></i></span> <span class="nav-text">Tự động hóa Admin</span></a></li>
                        <li><a href="index.php?act=admin/notificationSettings" class="<?php echo (isset($currentPage) && $currentPage === 'notificationSettings') ? 'active' : ''; ?>" title="Cài đặt thông báo"><span class="nav-icon-bg"><i class="bi bi-bell"></i></span> <span class="nav-text">Cài đặt thông báo</span></a></li>
                    <?php elseif ($currentRole === 'HDV'): ?>
                        <li><a href="index.php?act=hdv/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>">Trang chủ</a></li>
                        <li><a href="index.php?act=hdv/lichLamViec" class="<?php echo (isset($currentPage) && $currentPage === 'lichLamViec') ? 'active' : ''; ?>">Lịch làm việc</a></li>
                        <li><a href="index.php?act=hdv/tours" class="<?php echo (isset($currentPage) && $currentPage === 'tours') ? 'active' : ''; ?>">Tour của tôi</a></li>
                        <li><a href="index.php?act=hdv/nhatKy" class="<?php echo (isset($currentPage) && $currentPage === 'nhatKy') ? 'active' : ''; ?>">Nhật ký tour</a></li>
                        <li><a href="index.php?act=hdv/yeuCauDacBiet" class="<?php echo (isset($currentPage) && $currentPage === 'yeuCauDacBiet') ? 'active' : ''; ?>">Yêu cầu đặc biệt</a></li>
                    <?php elseif ($currentRole === 'KhachHang'): ?>
                        <li><a href="index.php?act=khachHang/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>">Trang chủ</a></li>
                        <li><a href="index.php?act=khachHang/danhSachTour" class="<?php echo (isset($currentPage) && $currentPage === 'tours') ? 'active' : ''; ?>">Danh sách tour</a></li>
                        <li><a href="index.php?act=khachHang/traCuu" class="<?php echo (isset($currentPage) && $currentPage === 'traCuu') ? 'active' : ''; ?>">Tra cứu booking</a></li>
                        <li><a href="index.php?act=khachHang/yeuCauTour" class="<?php echo (isset($currentPage) && $currentPage === 'yeuCauTour') ? 'active' : ''; ?>">Yêu cầu tour</a></li>
                    <?php endif; ?>
                    
                    <?php if ($isAdminRole): ?>
                        <li><a href="index.php?act=auth/setup2fa" class="<?php echo (isset($currentPage) && $currentPage === 'settings') ? 'active' : ''; ?>"><i class="bi bi-shield-lock me-1"></i>Bảo mật 2FA</a></li>
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
        <button type="button" class="mobile-sidebar-backdrop" id="mobileSidebarBackdrop" aria-label="Đóng menu điều hướng"></button>

        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <div class="header-utility-controls" aria-label="Điều khiển giao diện nhanh">
                        <button type="button" class="sidebar-toggle" id="sidebarToggle" title="Thu gọn/mở rộng sidebar" aria-label="Thu gọn/mở rộng sidebar"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="sidebar-theme" id="sidebarTheme" title="Chuyển chế độ sáng/tối" aria-label="Chuyển chế độ sáng/tối"><i class="bi bi-moon-stars"></i></button>
                    </div>
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

    <script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    document.addEventListener('DOMContentLoaded', function() {
        const csrfMeta = document.querySelector('meta[name="csrf-global-token"]');
        const csrfGlobalToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        const forceSidebarHiddenOnLoad = <?php echo $forceSidebarHiddenOnLoad ? 'true' : 'false'; ?>;

        if (csrfGlobalToken) {
            document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function(form) {
                if (form.querySelector('input[name="_csrf_global"]')) {
                    return;
                }

                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = '_csrf_global';
                hidden.value = csrfGlobalToken;
                form.appendChild(hidden);
            });
        }

        const isAdminUser = <?php echo $isAdminRole ? 'true' : 'false'; ?>;
        const soundEnabledOnServer = <?php echo $soundNotificationEnabled ? 'true' : 'false'; ?>;
        const STORAGE_KEYS = {
            sidebarCollapsed: 'aventura_sidebar_collapsed',
            lightThemeLegacy: 'aventura_theme_light',
            themeMode: 'aventura_theme_mode',
        };

        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarTheme = document.getElementById('sidebarTheme');
        const mobileSidebarBackdrop = document.getElementById('mobileSidebarBackdrop');
        const mobileSidebarClose = document.getElementById('mobileSidebarClose');
        const realtimeStatus = document.getElementById('realtimeStatus');
        const realtimeStatusText = document.getElementById('realtimeStatusText');
        const dashboardNavBadge = document.getElementById('dashboardNavBadge');
        const paymentNavBadge = document.getElementById('paymentNavBadge');
        const reviewNavBadge = document.getElementById('reviewNavBadge');
        const THEME_MODES = ['dark', 'business-dark', 'soft-light'];
        const streamReconnectDelay = 5000;
        const wsReconnectDelay = 3500;
        const fallbackPollIntervalMs = 5000;
        let notificationEventSource = null;
        let notificationWebSocket = null;
        let streamReconnectTimer = null;
        let wsReconnectTimer = null;
        let notificationFallbackTimer = null;
        let previousPaymentCount = Number.parseInt(paymentNavBadge ? paymentNavBadge.textContent : '0', 10) || 0;
        let previousReviewCount = Number.parseInt(reviewNavBadge ? reviewNavBadge.textContent : '0', 10) || 0;
        let previousDashboardCount = Number.parseInt(dashboardNavBadge ? dashboardNavBadge.textContent : '0', 10) || 0;
        let soundNotificationEnabled = soundEnabledOnServer;
        let audioContext = null;
        const realtimeWsEnabled = <?php echo ($isAdminRole && $realtimeWsEnabled && $realtimeWsUrl !== '' && $realtimeWsToken !== '') ? 'true' : 'false'; ?>;
        const realtimeWsUrl = <?php echo json_encode($realtimeWsUrl, JSON_UNESCAPED_UNICODE); ?>;
        const realtimeWsToken = <?php echo json_encode($realtimeWsToken, JSON_UNESCAPED_UNICODE); ?>;

        function ensureAudioContext() {
            if (!audioContext) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    audioContext = new AudioContextClass();
                }
            }
            if (audioContext && audioContext.state === 'suspended') {
                audioContext.resume().catch(() => {});
            }
        }

        function playNotificationSound() {
            try {
                ensureAudioContext();
                if (!audioContext || audioContext.state !== 'running') return;

                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                const currentTime = audioContext.currentTime;

                oscillator.type = 'triangle';
                oscillator.frequency.setValueAtTime(920, currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(1240, currentTime + 0.11);
                gainNode.gain.setValueAtTime(0.0001, currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.032, currentTime + 0.02);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, currentTime + 0.17);

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                oscillator.start(currentTime);
                oscillator.stop(currentTime + 0.18);
            } catch (error) {
                // Bỏ qua nếu trình duyệt chặn âm thanh tự động.
            }
        }

        function pulseBadge(element) {
            if (!element) return;
            element.classList.remove('is-pulse');
            // Force reflow để animation chạy lại kể cả cùng class.
            void element.offsetWidth;
            element.classList.add('is-pulse');
            setTimeout(function() {
                element.classList.remove('is-pulse');
            }, 700);
        }

        function renderBadge(element, count, label) {
            if (!element) return;
            const safeCount = Number.isFinite(count) ? Math.max(0, Math.floor(count)) : 0;
            const tooltip = 'Có ' + safeCount + ' ' + label + ' mới';
            element.title = tooltip;
            element.setAttribute('aria-label', tooltip);
            if (safeCount > 0) {
                element.textContent = safeCount;
                element.style.display = 'inline-flex';
            } else {
                element.textContent = '0';
                element.style.display = 'none';
            }
        }

        function setRealtimeConnectionState(state) {
            if (!realtimeStatus || !realtimeStatusText) return;
            realtimeStatus.classList.remove('is-connecting', 'is-connected', 'is-reconnecting', 'is-polling');

            if (state === 'connected') {
                realtimeStatus.classList.add('is-connected');
                realtimeStatusText.textContent = 'Realtime: Đã kết nối';
                return;
            }
            if (state === 'reconnecting') {
                realtimeStatus.classList.add('is-reconnecting');
                realtimeStatusText.textContent = 'Realtime: Mất kết nối, đang thử lại...';
                return;
            }
            if (state === 'polling') {
                realtimeStatus.classList.add('is-polling');
                realtimeStatusText.textContent = 'Realtime: Fallback polling';
                return;
            }

            realtimeStatus.classList.add('is-connecting');
            realtimeStatusText.textContent = 'Realtime: Đang kết nối...';
        }

        function applyNotificationPayload(data) {
            if (!data || data.success !== true) return;

            const nextPaymentCount = Number(data.payments || 0);
            const nextReviewCount = Number(data.reviews || 0);
            const nextDashboardCount = Number(data.dashboard || 0);
            if (Object.prototype.hasOwnProperty.call(data, 'sound_enabled')) {
                soundNotificationEnabled = Number(data.sound_enabled) === 1;
            }

            const hasIncrease =
                (nextPaymentCount > previousPaymentCount) ||
                (nextReviewCount > previousReviewCount) ||
                (nextDashboardCount > previousDashboardCount);

            if (nextPaymentCount > previousPaymentCount) {
                pulseBadge(paymentNavBadge);
            }
            if (nextReviewCount > previousReviewCount) {
                pulseBadge(reviewNavBadge);
            }
            if (nextDashboardCount > previousDashboardCount) {
                pulseBadge(dashboardNavBadge);
            }
            if (hasIncrease && soundNotificationEnabled) {
                playNotificationSound();
            }

            renderBadge(dashboardNavBadge, nextDashboardCount, 'thông báo');
            renderBadge(paymentNavBadge, nextPaymentCount, 'thanh toán');
            renderBadge(reviewNavBadge, nextReviewCount, 'đánh giá');

            previousDashboardCount = nextDashboardCount;
            previousPaymentCount = nextPaymentCount;
            previousReviewCount = nextReviewCount;

            // Broadcast to child views listening for real-time updates
            document.dispatchEvent(new CustomEvent('adminNotification', { detail: data }));
        }

        async function fetchNotificationSnapshot() {
            if (!isAdminUser) return;
            try {
                const response = await fetch(
                    'index.php?act=admin/notificationCounts&_ts=' + Date.now(),
                    {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );
                if (!response.ok) return;
                const data = await response.json();
                applyNotificationPayload(data);
            } catch (error) {
                // Bỏ qua lỗi mạng tạm thời để không ảnh hưởng trải nghiệm.
            }
        }

        function startFallbackPolling() {
            if (notificationFallbackTimer) return;
            notificationFallbackTimer = setInterval(fetchNotificationSnapshot, fallbackPollIntervalMs);
        }

        function stopFallbackPolling() {
            if (!notificationFallbackTimer) return;
            clearInterval(notificationFallbackTimer);
            notificationFallbackTimer = null;
        }

        function clearStreamReconnectTimer() {
            if (!streamReconnectTimer) return;
            clearTimeout(streamReconnectTimer);
            streamReconnectTimer = null;
        }

        function clearWsReconnectTimer() {
            if (!wsReconnectTimer) return;
            clearTimeout(wsReconnectTimer);
            wsReconnectTimer = null;
        }

        function scheduleStreamReconnect() {
            if (streamReconnectTimer) return;
            setRealtimeConnectionState('reconnecting');
            streamReconnectTimer = setTimeout(function() {
                streamReconnectTimer = null;
                openNotificationStream();
            }, streamReconnectDelay);
        }

        function scheduleWsReconnect() {
            if (wsReconnectTimer) return;
            setRealtimeConnectionState('reconnecting');
            wsReconnectTimer = setTimeout(function() {
                wsReconnectTimer = null;
                openNotificationWebSocket();
            }, wsReconnectDelay);
        }

        function openNotificationWebSocket() {
            if (!isAdminUser || !realtimeWsEnabled || typeof WebSocket === 'undefined') return false;

            clearWsReconnectTimer();
            setRealtimeConnectionState('connecting');

            if (notificationWebSocket) {
                try {
                    notificationWebSocket.close();
                } catch (error) {
                    // no-op
                }
                notificationWebSocket = null;
            }

            const joinChar = realtimeWsUrl.indexOf('?') >= 0 ? '&' : '?';
            const wsUrl = realtimeWsUrl + joinChar + 'token=' + encodeURIComponent(realtimeWsToken);
            notificationWebSocket = new WebSocket(wsUrl);

            notificationWebSocket.onopen = function() {
                setRealtimeConnectionState('connected');
                stopFallbackPolling();
            };

            notificationWebSocket.onmessage = function(event) {
                try {
                    const packet = JSON.parse(event.data || '{}');
                    if (!packet) return;
                    if (packet.type === 'ping') {
                        notificationWebSocket.send(JSON.stringify({ type: 'pong', payload: { ts: packet.payload && packet.payload.ts } }));
                        return;
                    }
                    if (packet.type !== 'notification' || !packet.payload) return;
                    applyNotificationPayload(packet.payload);
                    setRealtimeConnectionState('connected');
                } catch (error) {
                    // Ignore invalid packet payload.
                }
            };

            notificationWebSocket.onerror = function() {
                // onclose handles reconnect and fallback.
            };

            notificationWebSocket.onclose = function() {
                notificationWebSocket = null;
                startFallbackPolling();
                fetchNotificationSnapshot();
                scheduleWsReconnect();
            };

            return true;
        }

        function openNotificationStream() {
            if (!isAdminUser || typeof EventSource === 'undefined') return;

            clearStreamReconnectTimer();
            setRealtimeConnectionState('connecting');
            if (notificationEventSource) {
                notificationEventSource.close();
                notificationEventSource = null;
            }

            notificationEventSource = new EventSource('index.php?act=admin/notificationStream');

            notificationEventSource.onopen = function() {
                setRealtimeConnectionState('connected');
                stopFallbackPolling();
            };

            notificationEventSource.addEventListener('notification', function(event) {
                try {
                    const payload = JSON.parse(event.data);
                    applyNotificationPayload(payload);
                    setRealtimeConnectionState('connected');
                } catch (error) {
                    // Bỏ qua payload không hợp lệ.
                }
            });

            notificationEventSource.addEventListener('close', function() {
                if (notificationEventSource) {
                    notificationEventSource.close();
                    notificationEventSource = null;
                }
                startFallbackPolling();
                fetchNotificationSnapshot();
                scheduleStreamReconnect();
            });

            notificationEventSource.onerror = function() {
                if (notificationEventSource) {
                    notificationEventSource.close();
                    notificationEventSource = null;
                }
                startFallbackPolling();
                fetchNotificationSnapshot();
                scheduleStreamReconnect();
            };
        }

        function setSidebarIcon() {
            if (!sidebar || !sidebarToggle) return;
            const icon = sidebarToggle.querySelector('i');
            if (!icon) return;
            const isMobile = window.innerWidth <= 768;
            icon.className = 'bi';
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.add(isMobile ? 'bi-list' : 'bi-chevron-right');
            } else {
                icon.classList.add(isMobile ? 'bi-x-lg' : 'bi-chevron-left');
            }
        }

        function enforceMobileSidebarNavVisibility(isMobileOpen) {
            if (!sidebar) return;

            const nav = sidebar.querySelector('.nav');
            if (nav) {
                if (isMobileOpen) {
                    nav.style.setProperty('display', 'block', 'important');
                    nav.style.setProperty('opacity', '1', 'important');
                    nav.style.setProperty('visibility', 'visible', 'important');
                    nav.style.setProperty('margin-top', '0', 'important');
                } else {
                    nav.style.removeProperty('display');
                    nav.style.removeProperty('opacity');
                    nav.style.removeProperty('visibility');
                    nav.style.removeProperty('margin-top');
                }
            }

            sidebar.querySelectorAll('.nav li, .nav a, .nav-toggle, .nav-text, .expand-icon, .nav-group-label, .nav-child-menu').forEach(function(node) {
                if (isMobileOpen) {
                    node.style.setProperty('opacity', '1', 'important');
                    node.style.setProperty('visibility', 'visible', 'important');
                } else {
                    node.style.removeProperty('opacity');
                    node.style.removeProperty('visibility');
                }
            });

            sidebar.querySelectorAll('.nav a, .nav-toggle').forEach(function(node) {
                if (isMobileOpen) {
                    node.style.setProperty('display', 'flex', 'important');
                    node.style.setProperty('align-items', 'center', 'important');
                } else {
                    node.style.removeProperty('display');
                    node.style.removeProperty('align-items');
                }
            });

            sidebar.querySelectorAll('.text-widget, .social-icons, .copyright, .logo-subtitle, .realtime-status').forEach(function(node) {
                if (isMobileOpen) {
                    node.style.setProperty('display', 'none', 'important');
                } else {
                    node.style.removeProperty('display');
                }
            });
        }

        function applySidebarState() {
            if (!sidebar) return;
            const isCollapsed = sidebar.classList.contains('collapsed');
            const isMobile = window.innerWidth <= 768;
            const isMobileOpen = isMobile && !isCollapsed;

            document.body.classList.toggle('sidebar-hidden', isCollapsed);
            document.body.classList.toggle('mobile-sidebar-open', isMobileOpen);
            document.body.classList.toggle('mobile-sidebar-scroll-lock', isMobileOpen);

            if (!isMobile) {
                document.body.classList.remove('mobile-sidebar-open', 'mobile-sidebar-scroll-lock');
            }

            if (isMobileOpen) {
                // Always show nav from the top when opening mobile sidebar.
                sidebar.scrollTop = 0;
            }

            enforceMobileSidebarNavVisibility(isMobileOpen);

            setSidebarIcon();
        }

        function applyThemeMode(mode) {
            const safeMode = THEME_MODES.includes(mode) ? mode : 'dark';
            document.body.classList.remove('theme-light', 'theme-business-dark');
            if (safeMode === 'soft-light') {
                document.body.classList.add('theme-light');
            }
            if (safeMode === 'business-dark') {
                document.body.classList.add('theme-business-dark');
            }
            return safeMode;
        }

        function setThemeIcon(mode) {
            if (!sidebarTheme) return;
            const icon = sidebarTheme.querySelector('i');
            if (!icon) return;
            sidebarTheme.classList.remove('is-business');
            icon.className = 'bi';

            if (mode === 'soft-light') {
                icon.classList.add('bi-brightness-high');
                sidebarTheme.title = 'Chế độ hiện tại: Soft Light';
            } else if (mode === 'business-dark') {
                icon.classList.add('bi-circle-half');
                sidebarTheme.classList.add('is-business');
                sidebarTheme.title = 'Chế độ hiện tại: Business Dark';
            } else {
                icon.classList.add('bi-moon-stars');
                sidebarTheme.title = 'Chế độ hiện tại: Dark';
            }
        }

        function getCurrentThemeMode() {
            if (document.body.classList.contains('theme-light')) return 'soft-light';
            if (document.body.classList.contains('theme-business-dark')) return 'business-dark';
            return 'dark';
        }

        function syncMobileUtilityTitles() {
            [sidebarToggle, sidebarTheme].forEach(function(button) {
                if (!button) return;
                if (!button.dataset.desktopTitle && button.getAttribute('title')) {
                    button.dataset.desktopTitle = button.getAttribute('title');
                }

                if (window.innerWidth <= 768) {
                    button.removeAttribute('title');
                } else if (button.dataset.desktopTitle) {
                    button.setAttribute('title', button.dataset.desktopTitle);
                }
            });
        }

        function updateMobileHeaderMetrics() {
            const header = document.querySelector('.header');
            if (!header) return;

            if (window.innerWidth > 768) {
                document.body.style.removeProperty('--mobile-header-height');
                return;
            }

            const rect = header.getBoundingClientRect();
            const headerHeight = Math.max(64, Math.ceil(rect.height || 0));
            document.body.style.setProperty('--mobile-header-height', headerHeight + 'px');
        }

        function initAdminMotion() {
            if (!isAdminUser) return;

            const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const revealSelectors = [
                '.content-area > *',
                '.content-area .page-header',
                '.content-area .page-header-section',
                '.content-area .welcome-admin',
                '.content-area .auto-hero',
                '.content-area .auto-panel',
                '.content-area .auto-table-panel',
                '.content-area .auto-card',
                '.content-area .chart-box',
                '.content-area .filter-section',
                '.content-area .table-wrapper',
                '.content-area .info-card',
                '.content-area .form-card',
                '.content-area .schedule-card',
                '.content-area .stat-card',
                '.content-area .report-section',
                '.content-area .card'
            ];
            const hoverSelector = '.auto-panel, .auto-table-panel, .auto-card, .chart-box, .filter-section, .table-wrapper, .info-card, .form-card, .schedule-card, .stat-card, .report-section, .card';
            const seen = new Set();
            const revealTargets = [];
            const nonVisualTags = new Set(['SCRIPT', 'STYLE', 'LINK', 'META']);

            revealSelectors.forEach(function(selector) {
                document.querySelectorAll(selector).forEach(function(element) {
                    if (seen.has(element)) return;
                    if (element.closest('thead, tbody, tr, td, th')) return;
                    if (nonVisualTags.has(element.tagName)) return;
                    if (element.getClientRects().length === 0) return;
                    seen.add(element);
                    revealTargets.push(element);
                });
            });

            if (revealTargets.length === 0) {
                return;
            }

            revealTargets.forEach(function(element, index) {
                element.classList.add('admin-reveal');
                element.style.setProperty('--admin-reveal-delay', Math.min(index, 10) * 55 + 'ms');

                if (element.matches(hoverSelector) && !element.matches('.feature-card')) {
                    element.classList.add('admin-hover-lift');
                }
            });

            if (reduceMotion || !('IntersectionObserver' in window)) {
                revealTargets.forEach(function(element) {
                    element.classList.add('is-visible');
                    element.style.removeProperty('--admin-reveal-delay');
                });
                return;
            }

            function revealIfInViewport(element) {
                const rect = element.getBoundingClientRect();
                if (rect.bottom <= 0 || rect.top >= window.innerHeight) {
                    return false;
                }

                element.classList.add('is-visible');
                return true;
            }

            const revealObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

            revealTargets.forEach(function(element) {
                revealIfInViewport(element);
                revealObserver.observe(element);
            });

            window.setTimeout(function() {
                revealTargets.forEach(function(element) {
                    if (!element.classList.contains('is-visible')) {
                        element.classList.add('is-visible');
                    }
                });
            }, 700);
        }

        function initTableAutoPagination() {
            const rowsPerPage = 10;
            const tables = document.querySelectorAll('.content-area table');

            tables.forEach(function(table, tableIndex) {
                if (!table || table.dataset.autoPagination === '1') return;
                if (table.classList.contains('auto-pagination-skip')) return;

                const tableWrapper = table.closest('.table-responsive') || table;
                let hasExistingPagination = false;
                let siblingCursor = tableWrapper.nextElementSibling;
                let steps = 0;
                while (siblingCursor && steps < 4) {
                    if (
                        siblingCursor.classList.contains('pagination-shell') ||
                        siblingCursor.classList.contains('table-auto-pagination') ||
                        siblingCursor.classList.contains('pagination') ||
                        siblingCursor.querySelector('.pagination, .pagination-shell')
                    ) {
                        hasExistingPagination = true;
                        break;
                    }
                    siblingCursor = siblingCursor.nextElementSibling;
                    steps += 1;
                }

                if (hasExistingPagination) return;

                const tbody = table.tBodies && table.tBodies.length > 0 ? table.tBodies[0] : null;
                if (!tbody) return;

                const rows = Array.from(tbody.rows).filter(function(row) {
                    return !row.classList.contains('auto-pagination-ignore');
                });

                if (rows.length <= rowsPerPage) return;

                table.dataset.autoPagination = '1';
                let currentPage = 1;
                const totalPages = Math.ceil(rows.length / rowsPerPage);

                const shell = document.createElement('div');
                shell.className = 'table-auto-pagination';
                shell.setAttribute('role', 'navigation');
                shell.setAttribute('aria-label', 'Phân trang bảng dữ liệu');

                const meta = document.createElement('div');
                meta.className = 'table-auto-pagination-meta';

                const controls = document.createElement('div');
                controls.className = 'table-auto-pagination-controls';

                shell.appendChild(meta);
                shell.appendChild(controls);

                const wrapper = table.closest('.table-responsive');
                if (wrapper && wrapper.parentNode) {
                    wrapper.parentNode.insertBefore(shell, wrapper.nextSibling);
                } else if (table.parentNode) {
                    table.parentNode.insertBefore(shell, table.nextSibling);
                }

                function createPageButton(label, targetPage, options) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'table-auto-pagination-btn';
                    button.textContent = label;

                    if (options && options.active) {
                        button.classList.add('is-active');
                        button.setAttribute('aria-current', 'page');
                    }
                    if (options && options.disabled) {
                        button.disabled = true;
                    }

                    button.addEventListener('click', function() {
                        if (targetPage === currentPage || targetPage < 1 || targetPage > totalPages) return;
                        currentPage = targetPage;
                        renderPagination();
                    });

                    return button;
                }

                function renderPagination() {
                    const startIndex = (currentPage - 1) * rowsPerPage;
                    const endIndex = startIndex + rowsPerPage;

                    rows.forEach(function(row, rowIndex) {
                        row.style.display = rowIndex >= startIndex && rowIndex < endIndex ? '' : 'none';
                    });

                    const showingFrom = startIndex + 1;
                    const showingTo = Math.min(endIndex, rows.length);
                    meta.textContent = 'Hiển thị ' + showingFrom + '-' + showingTo + ' / ' + rows.length + ' mục (10 mục/trang)';

                    controls.innerHTML = '';
                    controls.appendChild(createPageButton('Trước', currentPage - 1, { disabled: currentPage === 1 }));

                    const maxNumericButtons = 5;
                    let startPage = Math.max(1, currentPage - 2);
                    let endPage = Math.min(totalPages, startPage + maxNumericButtons - 1);
                    startPage = Math.max(1, endPage - maxNumericButtons + 1);

                    if (startPage > 1) {
                        controls.appendChild(createPageButton('1', 1, { active: currentPage === 1 }));
                        if (startPage > 2) {
                            const dots = document.createElement('span');
                            dots.className = 'table-auto-pagination-meta';
                            dots.textContent = '...';
                            controls.appendChild(dots);
                        }
                    }

                    for (let page = startPage; page <= endPage; page += 1) {
                        controls.appendChild(createPageButton(String(page), page, { active: page === currentPage }));
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            const dots = document.createElement('span');
                            dots.className = 'table-auto-pagination-meta';
                            dots.textContent = '...';
                            controls.appendChild(dots);
                        }
                        controls.appendChild(createPageButton(String(totalPages), totalPages, { active: currentPage === totalPages }));
                    }

                    controls.appendChild(createPageButton('Sau', currentPage + 1, { disabled: currentPage === totalPages }));
                }

                shell.dataset.tablePaginationId = 'table-pager-' + String(tableIndex + 1);
                renderPagination();
            });
        }

        if (sidebar) {
            const shouldStartCollapsed = forceSidebarHiddenOnLoad
                ? true
                : (window.innerWidth <= 768 ? true : localStorage.getItem(STORAGE_KEYS.sidebarCollapsed) === '1');

            sidebar.classList.toggle('collapsed', shouldStartCollapsed);
            localStorage.setItem(STORAGE_KEYS.sidebarCollapsed, shouldStartCollapsed ? '1' : '0');
        }
        let savedThemeMode = localStorage.getItem(STORAGE_KEYS.themeMode);
        if (!savedThemeMode) {
            savedThemeMode = localStorage.getItem(STORAGE_KEYS.lightThemeLegacy) === '1' ? 'soft-light' : 'dark';
        }
        const appliedTheme = applyThemeMode(savedThemeMode);
        localStorage.setItem(STORAGE_KEYS.themeMode, appliedTheme);
        applySidebarState();
        setThemeIcon(appliedTheme);
        syncMobileUtilityTitles();
        updateMobileHeaderMetrics();
        initAdminMotion();
        initTableAutoPagination();

        // Nav parent-child: cho phép mở nhiều menu con cùng lúc
        document.querySelectorAll('.nav-parent > .nav-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.nav-parent');
                const menu = parent.querySelector('.nav-child-menu');
                const isVisible = !menu.hasAttribute('hidden');
                // Toggle menu hiện tại, không ảnh hưởng menu khác
                if (isVisible) {
                    menu.setAttribute('hidden', '');
                } else {
                    menu.removeAttribute('hidden');
                }
            });
        });

        // Sidebar collapse/expand
        if (sidebar && sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                applySidebarState();
                localStorage.setItem(STORAGE_KEYS.sidebarCollapsed, sidebar.classList.contains('collapsed') ? '1' : '0');
                // Đóng tất cả menu cha khi thu gọn
                if (sidebar.classList.contains('collapsed')) {
                    document.querySelectorAll('.nav-parent').forEach(item => item.classList.remove('open'));
                }
            });
        }

        if (mobileSidebarBackdrop && sidebar) {
            mobileSidebarBackdrop.addEventListener('click', function() {
                if (window.innerWidth > 768) return;
                sidebar.classList.add('collapsed');
                applySidebarState();
                localStorage.setItem(STORAGE_KEYS.sidebarCollapsed, '1');
            });
        }

        if (mobileSidebarClose && sidebar) {
            mobileSidebarClose.addEventListener('click', function() {
                if (window.innerWidth > 768) return;
                sidebar.classList.add('collapsed');
                applySidebarState();
                localStorage.setItem(STORAGE_KEYS.sidebarCollapsed, '1');
            });
        }

        if (sidebar) {
            sidebar.querySelectorAll('.nav a').forEach(function(link) {
                if (link.classList.contains('nav-toggle')) return;
                link.addEventListener('click', function() {
                    if (window.innerWidth > 768) return;
                    sidebar.classList.add('collapsed');
                    applySidebarState();
                    localStorage.setItem(STORAGE_KEYS.sidebarCollapsed, '1');
                });
            });
        }

        // Dark/Light mode toggle
        if (sidebarTheme) {
            sidebarTheme.addEventListener('click', function() {
                const currentMode = getCurrentThemeMode();
                const nextIndex = (THEME_MODES.indexOf(currentMode) + 1) % THEME_MODES.length;
                const nextMode = applyThemeMode(THEME_MODES[nextIndex]);
                setThemeIcon(nextMode);
                localStorage.setItem(STORAGE_KEYS.themeMode, nextMode);
                syncMobileUtilityTitles();
            });
        }

        window.addEventListener('resize', function() {
            updateMobileHeaderMetrics();
            syncMobileUtilityTitles();
            applySidebarState();
        }, { passive: true });

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;
            if (window.innerWidth > 768) return;
            if (!sidebar || sidebar.classList.contains('collapsed')) return;

            sidebar.classList.add('collapsed');
            applySidebarState();
            localStorage.setItem(STORAGE_KEYS.sidebarCollapsed, '1');
        });

        if (isAdminUser) {
            ['pointerdown', 'keydown'].forEach(function(eventName) {
                document.addEventListener(eventName, function() {
                    // Defer AudioContext setup to keep input handler lightweight.
                    window.requestAnimationFrame(function() {
                        ensureAudioContext();
                    });
                }, { once: true, passive: true });
            });
            fetchNotificationSnapshot();
            if (realtimeWsEnabled && typeof WebSocket !== 'undefined') {
                openNotificationWebSocket();
                startFallbackPolling();
            } else if (typeof EventSource !== 'undefined') {
                openNotificationStream();
                startFallbackPolling();
            } else {
                setRealtimeConnectionState('polling');
                startFallbackPolling();
            }
            window.addEventListener('beforeunload', function() {
                clearWsReconnectTimer();
                clearStreamReconnectTimer();
                stopFallbackPolling();
                if (notificationWebSocket) {
                    notificationWebSocket.close();
                }
                if (notificationEventSource) {
                    notificationEventSource.close();
                }
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
