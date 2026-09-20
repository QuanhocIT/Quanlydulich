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

$currentAdminName = htmlspecialchars((string)($_SESSION['user_name'] ?? 'Quản trị viên'));
$adminInitial = mb_strtoupper(mb_substr($currentAdminName, 0, 1, 'UTF-8'), 'UTF-8');
$userRoleLabel = $currentRole ? htmlspecialchars((string)$currentRole) : 'Quản trị viên';
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/aventura.css?v=<?php echo rawurlencode(ASSET_VERSION) . '_' . (file_exists(__DIR__ . '/../../public/css/aventura.css') ? filemtime(__DIR__ . '/../../public/css/aventura.css') : time()); ?>">
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

        /* User Header Dropdown */
        .header-user-dropdown-wrapper {
            position: relative;
            display: inline-block;
        }
        .header-user-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 4px 12px 4px 6px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 30px;
            color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .header-user-btn:hover, .header-user-btn.active {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(223, 169, 116, 0.5);
            box-shadow: 0 0 14px rgba(223, 169, 116, 0.22);
        }
        .header-user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dfa974, #b27a3c);
            color: #10141d;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .header-user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
            text-align: left;
        }
        .header-user-name {
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .header-user-role {
            font-size: 9px;
            color: #dfa974;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-user-chevron {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.6);
            transition: transform 0.2s ease;
            margin-left: 2px;
        }
        .header-user-btn.active .header-user-chevron {
            transform: rotate(180deg);
        }
        .header-user-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            width: 235px;
            background: #191e2b;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.45);
            padding: 8px 0;
            z-index: 1050;
            backdrop-filter: blur(16px);
            animation: headerMenuFade 0.2s ease forwards;
        }
        .header-user-menu.show {
            display: block;
        }
        @keyframes headerMenuFade {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header-user-menu-header {
            padding: 8px 16px 10px;
        }
        .h-u-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .h-u-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 2px;
        }
        .header-user-menu-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 6px 0;
        }
        .header-user-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 13px;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .header-user-menu-item:hover {
            background: rgba(223, 169, 116, 0.12);
            color: #dfa974;
        }
        .header-user-menu-item i {
            font-size: 15px;
            width: 18px;
            text-align: center;
        }
        .header-user-menu-item.text-danger:hover {
            background: rgba(231, 76, 60, 0.15);
            color: #ff7675 !important;
        }

        /* ==========================================================================
           UPGRADED MODERN ADMIN SIDEBAR STYLES (AVENTURA PRO)
           ========================================================================== */
        aside.sidebar {
            width: 280px;
            background: #0d1322 !important;
            background: linear-gradient(180deg, #111728 0%, #090e1a 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 6px 0 28px rgba(0, 0, 0, 0.45);
            padding: 22px 0 35px !important;
            display: flex;
            flex-direction: column;
            scrollbar-width: thin;
            scrollbar-color: rgba(223, 169, 116, 0.25) transparent;
        }

        /* Slim sleek scrollbar */
        aside.sidebar::-webkit-scrollbar {
            width: 5px;
        }
        aside.sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        aside.sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 10px;
        }
        aside.sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(223, 169, 116, 0.4);
        }

        /* Brand Container */
        .sidebar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 10px 16px;
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .sidebar-brand-wrapper:hover {
            opacity: 0.95;
        }

        .brand-emblem {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 11px;
            background: linear-gradient(135deg, rgba(223, 169, 116, 0.28) 0%, rgba(212, 175, 55, 0.08) 100%);
            border: 1px solid rgba(223, 169, 116, 0.45);
            box-shadow: 0 0 20px rgba(223, 169, 116, 0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dfa974;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .sidebar-brand-wrapper:hover .brand-emblem {
            transform: rotate(12deg) scale(1.05);
            box-shadow: 0 0 25px rgba(223, 169, 116, 0.45);
            border-color: #dfa974;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-title {
            font-size: 19px;
            font-weight: 800;
            letter-spacing: 1.8px;
            line-height: 1.15;
            background: linear-gradient(135deg, #ffffff 30%, #dfa974 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-tagline {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.45);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .brand-version-badge {
            font-size: 8.5px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 4px;
            background: rgba(223, 169, 116, 0.15);
            border: 1px solid rgba(223, 169, 116, 0.3);
            color: #dfa974;
            letter-spacing: 0.5px;
        }

        /* Realtime status pill */
        .realtime-status-pill {
            margin: 4px 6px 12px;
            padding: 6px 10px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.09);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: #e2e8f0;
            transition: all 0.25s ease;
        }

        .realtime-status-pill:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .status-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #eab308;
            box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2);
            position: relative;
            flex-shrink: 0;
        }

        .realtime-status.is-connected .status-pulse-dot,
        .realtime-status-pill.is-connected .status-pulse-dot {
            background: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
        }

        .realtime-status.is-reconnecting .status-pulse-dot,
        .realtime-status-pill.is-reconnecting .status-pulse-dot {
            background: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
        }

        .status-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }

        .status-chip {
            font-size: 8.5px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.07);
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 0.5px;
        }

        /* Nav List */
        .sidebar .nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .sidebar .nav-group-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8 !important;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 14px 10px 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar .nav-group-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.12) 0%, transparent 100%);
        }

        /* Nav Items / Links */
        .sidebar .nav li {
            margin: 0;
            position: relative;
        }

        .sidebar .nav a,
        .sidebar .nav .nav-toggle {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            margin: 2px 6px;
            border-radius: 8px;
            color: #f1f5f9 !important;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 550;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            background: transparent;
            border-left: 3.5px solid transparent;
            min-height: 40px;
            cursor: pointer;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .sidebar .nav a .nav-text,
        .sidebar .nav .nav-toggle .nav-text {
            color: #f1f5f9 !important;
            font-weight: 550;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.25;
        }

        .sidebar .nav a:hover,
        .sidebar .nav .nav-toggle:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(2px);
        }

        .sidebar .nav a:hover .nav-text,
        .sidebar .nav .nav-toggle:hover .nav-text {
            color: #ffffff !important;
        }

        .sidebar .nav a.active,
        .sidebar .nav .nav-toggle.active {
            color: #ffffff !important;
            font-weight: 650 !important;
            background: linear-gradient(90deg, rgba(223, 169, 116, 0.25) 0%, rgba(223, 169, 116, 0.06) 100%) !important;
            border-left: 3.5px solid #dfa974 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
        }

        .sidebar .nav a.active .nav-text,
        .sidebar .nav .nav-toggle.active .nav-text {
            color: #ffffff !important;
            font-weight: 650;
        }

        /* Nav Icon Container */
        .sidebar .nav-icon-bg {
            width: 26px;
            min-width: 26px;
            height: 26px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.06);
            color: #93c5fd;
            font-size: 14.5px;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .sidebar .nav a:hover .nav-icon-bg,
        .sidebar .nav .nav-toggle:hover .nav-icon-bg {
            background: rgba(223, 169, 116, 0.22);
            color: #dfa974;
            transform: scale(1.05);
        }

        .sidebar .nav a.active .nav-icon-bg,
        .sidebar .nav .nav-toggle.active .nav-icon-bg {
            background: linear-gradient(135deg, #dfa974 0%, #b8860b 100%);
            color: #0b0f19;
            box-shadow: 0 0 10px rgba(223, 169, 116, 0.45);
        }

        /* Nav Badges */
        .sidebar .nav-badge {
            margin-left: auto;
            min-width: 20px;
            height: 18px;
            border-radius: 9px;
            padding: 0 6px;
            font-size: 10.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.4);
        }

        #paymentNavBadge {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
        }

        #reviewNavBadge {
            background: linear-gradient(135deg, #8b5cf6, #6d28d9);
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.4);
        }

        /* Expand Chevron */
        .sidebar .expand-icon {
            margin-left: auto;
            font-size: 12px;
            color: #cbd5e1;
            transition: transform 0.25s ease;
        }

        .nav-parent.expanded > .nav-toggle .expand-icon {
            transform: rotate(180deg);
            color: #dfa974;
        }

        /* Submenus (Nested Tree) */
        .nav-child-menu {
            margin: 2px 6px 6px 14px;
            padding: 2px 0 2px 8px;
            border-left: 1.5px solid rgba(223, 169, 116, 0.35);
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-child-menu[hidden] {
            display: none !important;
        }

        .nav-child-menu a {
            padding: 6px 8px !important;
            margin: 0 !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            border-radius: 6px !important;
            color: #cbd5e1 !important;
            border-left: none !important;
            min-height: 32px !important;
            display: flex !important;
            align-items: center !important;
            gap: 7px !important;
            background: transparent !important;
            -webkit-font-smoothing: antialiased;
        }

        .nav-child-menu a:hover {
            color: #ffffff !important;
            background: rgba(223, 169, 116, 0.12) !important;
            transform: translateX(3px) !important;
        }

        .child-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            display: inline-block;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .nav-child-menu a:hover .child-dot {
            background: #dfa974;
            box-shadow: 0 0 6px #dfa974;
            transform: scale(1.3);
        }

        /* Sidebar Bottom Profile Card */
        .sidebar-user-card {
            margin: 16px 6px 10px;
            padding: 10px;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* Global Theme Override: Ensure Nav is ALWAYS crisp and high-contrast in all themes */
        body.theme-light .sidebar .nav a,
        body.theme-light .sidebar .nav .nav-toggle,
        body.theme-light .sidebar .nav .nav-text,
        body.theme-light .sidebar .nav li a,
        body.theme-business-dark .sidebar .nav a,
        body.theme-business-dark .sidebar .nav .nav-toggle,
        body.theme-business-dark .sidebar .nav .nav-text,
        body.theme-business-dark .sidebar .nav li a {
            color: #f1f5f9 !important;
        }
        body.theme-light .sidebar .nav a:hover,
        body.theme-light .sidebar .nav .nav-toggle:hover,
        body.theme-business-dark .sidebar .nav a:hover,
        body.theme-business-dark .sidebar .nav .nav-toggle:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }
        body.theme-light .sidebar .nav a.active,
        body.theme-light .sidebar .nav .nav-toggle.active,
        body.theme-business-dark .sidebar .nav a.active,
        body.theme-business-dark .sidebar .nav .nav-toggle.active {
            color: #ffffff !important;
            background: linear-gradient(90deg, rgba(223, 169, 116, 0.25) 0%, rgba(223, 169, 116, 0.06) 100%) !important;
            border-left: 3.5px solid #dfa974 !important;
        }
        body.theme-light .sidebar .nav-icon-bg,
        body.theme-business-dark .sidebar .nav-icon-bg {
            background: rgba(255, 255, 255, 0.06) !important;
            color: #93c5fd !important;
        }
        body.theme-light .sidebar .nav a.active .nav-icon-bg,
        body.theme-business-dark .sidebar .nav a.active .nav-icon-bg {
            background: linear-gradient(135deg, #dfa974 0%, #b8860b 100%) !important;
            color: #0b0f19 !important;
        }
        body.theme-light .sidebar .nav-group-label,
        body.theme-business-dark .sidebar .nav-group-label {
            color: #94a3b8 !important;
        }
        body.theme-light .sidebar .nav-child-menu a,
        body.theme-business-dark .sidebar .nav-child-menu a {
            color: #cbd5e1 !important;
        }
        body.theme-light .sidebar .nav-child-menu a:hover,
        body.theme-business-dark .sidebar .nav-child-menu a:hover {
            color: #ffffff !important;
            background: rgba(223, 169, 116, 0.12) !important;
        }

        .sidebar-user-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .sidebar-user-avatar-text {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dfa974, #b8860b);
            color: #0b0f19;
            font-weight: 800;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 10px rgba(223, 169, 116, 0.3);
        }

        .sidebar-user-online {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #10b981;
            border: 2px solid #0d1322;
        }

        .sidebar-user-meta {
            flex: 1;
            overflow: hidden;
        }

        .sidebar-user-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .sidebar-user-role {
            font-size: 9.5px;
            font-weight: 600;
            color: #dfa974;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .sidebar-user-actions {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sidebar-action-btn {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .sidebar-action-btn:hover {
            background: rgba(223, 169, 116, 0.18);
            color: #dfa974;
            border-color: #dfa974;
        }

        .sidebar-action-btn.logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-color: #ef4444;
        }

        /* Sidebar Footer Note */
        .sidebar-footer-note {
            padding: 4px 16px 0;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.35);
            text-align: center;
            line-height: 1.4;
        }

        /* ==========================================================================
           TĂNG KÍCH THƯỚC NỘI DUNG TẤT CẢ CÁC TRANG TÀI KHOẢN ADMIN
           ========================================================================== */
        body.is-admin .content-area,
        body.role-admin .content-area {
            zoom: 1.07;
            padding: 26px 36px 60px;
            max-width: 100%;
            box-sizing: border-box;
        }

        body.is-admin .content-area table,
        body.role-admin .content-area table {
            font-size: 14.5px;
        }

        @media (max-width: 1366px) {
            body.is-admin .content-area,
            body.role-admin .content-area {
                zoom: 1.05;
                padding: 22px 28px 50px;
            }
        }

        @media (max-width: 992px) {
            body.is-admin .content-area,
            body.role-admin .content-area {
                zoom: 1.0;
                padding: 16px 16px 40px;
            }
        }
    </style>
</head>
<body class="<?php echo htmlspecialchars(trim(implode(' ', array_filter($bodyClasses))), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="container">
        <!-- Sidebar (Nâng cấp hiện đại AVENTURA PRO) -->
        <aside class="sidebar" id="sidebar">
            <a href="index.php?act=admin/dashboard" class="sidebar-brand-wrapper" title="AVENTURA Admin Dashboard">
                <div class="brand-emblem">
                    <i class="bi bi-compass-fill"></i>
                </div>
                <div class="brand-info">
                    <div class="brand-title">AVENTURA</div>
                    <div class="brand-tagline">LUXURY TRAVEL <span class="brand-version-badge">v3.5 PRO</span></div>
                </div>
            </a>

            <button type="button" class="mobile-sidebar-close" id="mobileSidebarClose" aria-label="Đóng menu điều hướng">
                <i class="bi bi-x-lg"></i>
            </button>

            <?php if ($isAdminRole): ?>
                <div id="realtimeStatus" class="realtime-status realtime-status-pill is-connecting" title="Trạng thái kết nối thông báo realtime">
                    <span id="realtimeStatusDot" class="realtime-status-dot status-pulse-dot"></span>
                    <span id="realtimeStatusText" class="status-text">Đang kết nối realtime...</span>
                    <span class="status-chip">LIVE</span>
                </div>
            <?php endif; ?>

            <ul class="nav">
                <li class="nav-group-label"><i class="bi bi-grid-fill"></i> NAVIGATION</li>
                <?php if ($currentRole !== null): ?>
                    <?php if ($isAdminRole): ?>
                        <li>
                            <a href="index.php?act=admin/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>" title="Dashboard">
                                <span class="nav-icon-bg"><i class="bi bi-speedometer2"></i></span> <span class="nav-text">Dashboard</span>
                                <span id="dashboardNavBadge" class="nav-badge" title="Có <?php echo $dashboardNotificationCount; ?> thông báo mới"<?php if ($dashboardNotificationCount <= 0): ?> style="display:none"<?php endif; ?>><?php echo $dashboardNotificationCount; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="index.php?act=admin/quanLyLuongThuong" class="<?php echo (isset($currentPage) && $currentPage === 'luongThuong') ? 'active' : ''; ?>" title="Lương thưởng">
                                <span class="nav-icon-bg"><i class="bi bi-cash-stack"></i></span> <span class="nav-text">Lương thưởng</span>
                            </a>
                        </li>
                        <li class="nav-parent<?php echo (isset($currentPage) && in_array($currentPage, ['tour', 'tourCreate', 'lichKhoiHanh'], true)) ? ' expanded' : ''; ?>">
                            <a href="#" class="nav-toggle" title="Quản lý tour"><span class="nav-icon-bg"><i class="bi bi-geo-alt-fill"></i></span> <span class="nav-text">Quản lý tour</span> <i class="bi bi-chevron-down expand-icon"></i></a>
                            <div class="nav-child-menu"<?php echo (isset($currentPage) && in_array($currentPage, ['tour', 'tourCreate', 'lichKhoiHanh'], true)) ? '' : ' hidden'; ?>>
                                <a href="index.php?act=admin/quanLyTour" title="Danh sách tour"><span class="child-dot"></span>Danh sách tour</a>
                                <a href="index.php?act=tour/create" title="Tạo tour mới"><span class="child-dot"></span>Tạo tour mới</a>
                                <a href="index.php?act=lichKhoiHanh/index" title="Lịch khởi hành"><span class="child-dot"></span>Lịch khởi hành</a>
                            </div>
                        </li>
                        <li class="nav-parent<?php echo (isset($currentPage) && in_array($currentPage, ['booking', 'bookingHoanThanh', 'yeuCauTour', 'datTourChoKhach', 'lichSuXoaBooking'], true)) ? ' expanded' : ''; ?>">
                            <a href="#" class="nav-toggle <?php echo (isset($currentPage) && $currentPage === 'booking') ? 'active' : ''; ?>" title="Quản lý Booking"><span class="nav-icon-bg"><i class="bi bi-journal-bookmark-fill"></i></span> <span class="nav-text">Quản lý Booking</span> <i class="bi bi-chevron-down expand-icon"></i></a>
                            <div class="nav-child-menu"<?php echo (isset($currentPage) && in_array($currentPage, ['booking', 'bookingHoanThanh', 'yeuCauTour', 'datTourChoKhach', 'lichSuXoaBooking'], true)) ? '' : ' hidden'; ?>>
                                <a href="index.php?act=admin/quanLyBooking" title="Danh sách booking"><span class="child-dot"></span>Danh sách booking</a>
                                <a href="index.php?act=admin/bookingDaHoanThanh" title="Booking đã hoàn thành"><span class="child-dot"></span>Booking hoàn thành</a>
                                <a href="index.php?act=admin/quanLyYeuCauTour" title="Yêu cầu đặt tour"><span class="child-dot"></span>Yêu cầu đặt tour</a>
                                <a href="index.php?act=booking/datTourChoKhach" title="Đặt tour cho khách"><span class="child-dot"></span>Đặt tour cho khách</a>
                                <a href="index.php?act=admin/lichSuXoaBooking" title="Lịch sử xóa booking"><span class="child-dot"></span>Lịch sử xóa booking</a>
                            </div>
                        </li>
                        <li><a href="index.php?act=lichKhoiHanh/index" class="<?php echo (isset($currentPage) && $currentPage === 'lichKhoiHanh') ? 'active' : ''; ?>" title="Quản lý lịch khởi hành"><span class="nav-icon-bg"><i class="bi bi-calendar3"></i></span> <span class="nav-text">Quản lý lịch khởi hành</span></a></li>
                        <li><a href="index.php?act=admin/nhanSu" class="<?php echo (isset($currentPage) && $currentPage === 'nhanSu') ? 'active' : ''; ?>" title="Quản lý nhân sự"><span class="nav-icon-bg"><i class="bi bi-person-badge-fill"></i></span> <span class="nav-text">Quản lý nhân sự</span></a></li>
                        <li><a href="index.php?act=admin/quanLyNguoiDung" class="<?php echo (isset($currentPage) && $currentPage === 'nguoiDung') ? 'active' : ''; ?>" title="Quản lý người dùng"><span class="nav-icon-bg"><i class="bi bi-people-fill"></i></span> <span class="nav-text">Quản lý người dùng</span></a></li>
                        <li><a href="index.php?act=admin/nhaCungCap" class="<?php echo (isset($currentPage) && $currentPage === 'nhaCungCap') ? 'active' : ''; ?>" title="Nhà cung cấp"><span class="nav-icon-bg"><i class="bi bi-building"></i></span> <span class="nav-text">Nhà cung cấp</span></a></li>
                        
                        <li class="nav-group-label"><i class="bi bi-shield-check"></i> ADMIN & TÀI CHÍNH</li>
                        <li><a href="index.php?act=admin/invoices" class="<?php echo (isset($currentPage) && $currentPage === 'invoices') ? 'active' : ''; ?>" title="Quản lý hóa đơn"><span class="nav-icon-bg"><i class="bi bi-receipt-cutoff"></i></span> <span class="nav-text">Quản lý hóa đơn</span></a></li>
                        <li><a href="index.php?act=admin/payments" class="<?php echo (isset($currentPage) && $currentPage === 'payments') ? 'active' : ''; ?>" title="Quản lý thanh toán"><span class="nav-icon-bg"><i class="bi bi-credit-card-2-front-fill"></i></span> <span class="nav-text">Quản lý thanh toán</span><span id="paymentNavBadge" class="nav-badge" title="Có <?php echo $paymentNotificationCount; ?> thanh toán mới"<?php if ($paymentNotificationCount <= 0): ?> style="display:none"<?php endif; ?>><?php echo $paymentNotificationCount; ?></span></a></li>
                        <li class="nav-parent<?php echo (isset($currentPage) && in_array($currentPage, ['baoCaoTaiChinh', 'lichSuGiaoDich', 'thuChiTour', 'congNo', 'laiLoTour', 'duToanTour', 'soSanhDuToan'], true)) ? ' expanded' : ''; ?>">
                            <a href="#" class="nav-toggle <?php echo (isset($currentPage) && $currentPage === 'baoCaoTaiChinh') ? 'active' : ''; ?>" title="Báo cáo tài chính"><span class="nav-icon-bg"><i class="bi bi-bar-chart-line-fill"></i></span> <span class="nav-text">Báo cáo tài chính</span> <i class="bi bi-chevron-down expand-icon"></i></a>
                            <div class="nav-child-menu"<?php echo (isset($currentPage) && in_array($currentPage, ['baoCaoTaiChinh', 'lichSuGiaoDich', 'thuChiTour', 'congNo', 'laiLoTour', 'duToanTour', 'soSanhDuToan'], true)) ? '' : ' hidden'; ?>>
                                <a href="index.php?act=admin/lichSuGiaoDich" title="Lịch sử giao dịch"><span class="child-dot"></span>Lịch sử giao dịch</a>
                                <a href="index.php?act=admin/thuChiTour" title="Thu chi từng tour"><span class="child-dot"></span>Thu chi từng tour</a>
                                <a href="index.php?act=admin/congNo" title="Công nợ"><span class="child-dot"></span>Công nợ</a>
                                <a href="index.php?act=admin/laiLoTour" title="Lãi lỗ từng tour"><span class="child-dot"></span>Lãi lỗ từng tour</a>
                                <a href="index.php?act=admin/duToanTour" title="Dự toán tour"><span class="child-dot"></span>Dự toán tour</a>
                                <a href="index.php?act=admin/soSanhDuToan" title="So sánh dự toán"><span class="child-dot"></span>So sánh dự toán</a>
                            </div>
                        </li>
                        <li><a href="index.php?act=admin/danhGia" class="<?php echo (isset($currentPage) && ($currentPage === 'danhGia' || $currentPage === 'danh_gia')) ? 'active' : ''; ?>" title="Đánh giá & Phản hồi"><span class="nav-icon-bg"><i class="bi bi-chat-square-quote-fill"></i></span> <span class="nav-text">Đánh giá & Phản hồi</span><span id="reviewNavBadge" class="nav-badge" title="Có <?php echo $reviewNotificationCount; ?> đánh giá mới"<?php if ($reviewNotificationCount <= 0): ?> style="display:none"<?php endif; ?>><?php echo $reviewNotificationCount; ?></span></a></li>
                        <li><a href="index.php?act=admin/automationDashboard" class="<?php echo (isset($currentPage) && $currentPage === 'automation') ? 'active' : ''; ?>" title="Trung tâm tự động hóa"><span class="nav-icon-bg"><i class="bi bi-cpu-fill"></i></span> <span class="nav-text">Tự động hóa Admin</span></a></li>
                        <li><a href="index.php?act=admin/notificationSettings" class="<?php echo (isset($currentPage) && $currentPage === 'notificationSettings') ? 'active' : ''; ?>" title="Cài đặt thông báo"><span class="nav-icon-bg"><i class="bi bi-bell-fill"></i></span> <span class="nav-text">Cài đặt thông báo</span></a></li>
                    <?php elseif ($currentRole === 'HDV'): ?>
                        <li><a href="index.php?act=hdv/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-house"></i></span> Trang chủ</a></li>
                        <li><a href="index.php?act=hdv/lichLamViec" class="<?php echo (isset($currentPage) && $currentPage === 'lichLamViec') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-calendar-event"></i></span> Lịch làm việc</a></li>
                        <li><a href="index.php?act=hdv/tours" class="<?php echo (isset($currentPage) && $currentPage === 'tours') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-geo-alt"></i></span> Tour của tôi</a></li>
                        <li><a href="index.php?act=hdv/nhatKy" class="<?php echo (isset($currentPage) && $currentPage === 'nhatKy') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-journal-text"></i></span> Nhật ký tour</a></li>
                        <li><a href="index.php?act=hdv/yeuCauDacBiet" class="<?php echo (isset($currentPage) && $currentPage === 'yeuCauDacBiet') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-star"></i></span> Yêu cầu đặc biệt</a></li>
                    <?php elseif ($currentRole === 'KhachHang'): ?>
                        <li><a href="index.php?act=khachHang/dashboard" class="<?php echo (isset($currentPage) && $currentPage === 'dashboard') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-house"></i></span> Trang chủ</a></li>
                        <li><a href="index.php?act=khachHang/danhSachTour" class="<?php echo (isset($currentPage) && $currentPage === 'tours') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-map"></i></span> Danh sách tour</a></li>
                        <li><a href="index.php?act=khachHang/traCuu" class="<?php echo (isset($currentPage) && $currentPage === 'traCuu') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-search"></i></span> Tra cứu booking</a></li>
                        <li><a href="index.php?act=khachHang/yeuCauTour" class="<?php echo (isset($currentPage) && $currentPage === 'yeuCauTour') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-ticket"></i></span> Yêu cầu tour</a></li>
                    <?php endif; ?>
                    
                    <li class="nav-group-label"><i class="bi bi-gear-fill"></i> HỆ THỐNG</li>
                    <?php if ($isAdminRole): ?>
                        <li><a href="index.php?act=auth/setup2fa" class="<?php echo (isset($currentPage) && $currentPage === 'settings') ? 'active' : ''; ?>"><span class="nav-icon-bg"><i class="bi bi-shield-lock-fill"></i></span> <span class="nav-text">Bảo mật 2FA</span></a></li>
                    <?php endif; ?>
                    <li><a href="index.php?act=auth/logout" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');"><span class="nav-icon-bg"><i class="bi bi-box-arrow-right"></i></span> <span class="nav-text">Đăng xuất</span></a></li>
                <?php else: ?>
                    <li><a href="index.php?act=tour/index"><span class="nav-icon-bg"><i class="bi bi-house"></i></span> Trang chủ</a></li>
                    <li><a href="index.php?act=auth/login"><span class="nav-icon-bg"><i class="bi bi-box-arrow-in-right"></i></span> Đăng nhập</a></li>
                    <li><a href="index.php?act=auth/register"><span class="nav-icon-bg"><i class="bi bi-person-plus"></i></span> Đăng ký</a></li>
                <?php endif; ?>
            </ul>

            <!-- Sidebar Bottom Quick User Profile Card -->
            <?php if (isset($_SESSION['user_name'])): ?>
                <div class="sidebar-user-card">
                    <div class="sidebar-user-avatar-wrap">
                        <span class="sidebar-user-avatar-text"><?php echo $adminInitial; ?></span>
                        <span class="sidebar-user-online" title="Đang trực tuyến"></span>
                    </div>
                    <div class="sidebar-user-meta">
                        <div class="sidebar-user-name" title="<?php echo $currentAdminName; ?>"><?php echo $currentAdminName; ?></div>
                        <div class="sidebar-user-role"><?php echo $userRoleLabel; ?></div>
                    </div>
                    <div class="sidebar-user-actions">
                        <a href="<?php echo BASE_URL; ?>index.php?act=auth/setup2fa" class="sidebar-action-btn" title="Bảo mật 2FA">
                            <i class="bi bi-shield-lock"></i>
                        </a>
                        <a href="<?php echo BASE_URL; ?>index.php?act=auth/logout" class="sidebar-action-btn logout-btn" title="Đăng xuất" onclick="return confirm('Bạn có chắc chắn muốn đăng xuất không?');">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="sidebar-footer-note">
                <div>AVENTURA <strong>PRO SUITE v3.5</strong></div>
                <div style="opacity: 0.55; font-size: 9.5px; margin-top: 3px;">© <?php echo date('Y'); ?> All Rights Reserved</div>
            </div>
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
                        <?php 
                            $currentAdminName = htmlspecialchars((string)$_SESSION['user_name']);
                            $adminInitial = mb_strtoupper(mb_substr($currentAdminName, 0, 1, 'UTF-8'), 'UTF-8');
                            $userRoleLabel = $currentRole ? htmlspecialchars((string)$currentRole) : 'Quản trị viên';
                        ?>
                        <div class="header-user-dropdown-wrapper" id="headerUserDropdownWrapper">
                            <button type="button" class="header-user-btn" id="headerUserDropdownBtn" aria-expanded="false" aria-haspopup="true" title="Tài khoản: <?php echo $currentAdminName; ?>">
                                <span class="header-user-avatar"><?php echo $adminInitial; ?></span>
                                <span class="header-user-info">
                                    <span class="header-user-name"><?php echo $currentAdminName; ?></span>
                                    <span class="header-user-role"><?php echo $userRoleLabel; ?></span>
                                </span>
                                <i class="bi bi-chevron-down header-user-chevron"></i>
                            </button>
                            <div class="header-user-menu" id="headerUserDropdownMenu" role="menu">
                                <div class="header-user-menu-header">
                                    <div class="h-u-title"><?php echo $currentAdminName; ?></div>
                                    <div class="h-u-sub"><?php echo $userRoleLabel; ?> hệ thống</div>
                                </div>
                                <div class="header-user-menu-divider"></div>
                                <?php if ($isAdminRole): ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?act=admin/profile" class="header-user-menu-item" role="menuitem">
                                        <i class="bi bi-person-gear"></i>
                                        <span>Hồ sơ cá nhân & Bảo mật</span>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>index.php?act=admin/profile#password" class="header-user-menu-item" role="menuitem">
                                        <i class="bi bi-key"></i>
                                        <span>Đổi mật khẩu</span>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyNguoiDung" class="header-user-menu-item" role="menuitem">
                                        <i class="bi bi-people"></i>
                                        <span>Quản lý người dùng</span>
                                    </a>
                                <?php elseif ($currentRole === 'HDV'): ?>
                                    <a href="<?php echo BASE_URL; ?>index.php?act=hdv/profile" class="header-user-menu-item" role="menuitem">
                                        <i class="bi bi-person-badge"></i>
                                        <span>Hồ sơ hướng dẫn viên</span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>index.php?act=auth/setup2fa" class="header-user-menu-item" role="menuitem">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Xác thực 2 lớp (2FA)</span>
                                </a>
                                <div class="header-user-menu-divider"></div>
                                <a href="<?php echo BASE_URL; ?>index.php?act=auth/logout" class="header-user-menu-item text-danger" role="menuitem">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Đăng xuất</span>
                                </a>
                            </div>
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

        // Header User Menu Dropdown
        const userDropdownBtn = document.getElementById('headerUserDropdownBtn');
        const userDropdownMenu = document.getElementById('headerUserDropdownMenu');
        if (userDropdownBtn && userDropdownMenu) {
            userDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = userDropdownMenu.classList.contains('show');
                userDropdownMenu.classList.toggle('show', !isOpen);
                userDropdownBtn.classList.toggle('active', !isOpen);
                userDropdownBtn.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
            });
            document.addEventListener('click', function(e) {
                if (!userDropdownMenu.contains(e.target) && !userDropdownBtn.contains(e.target)) {
                    userDropdownMenu.classList.remove('show');
                    userDropdownBtn.classList.remove('active');
                    userDropdownBtn.setAttribute('aria-expanded', 'false');
                }
            });
        }
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
                realtimeStatusText.textContent = 'Realtime: Đang đồng bộ';
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

        let wsReconnectAttempts = 0;
        const maxWsReconnectAttempts = 3;

        function scheduleWsReconnect() {
            if (wsReconnectTimer) return;
            wsReconnectAttempts++;
            if (wsReconnectAttempts > maxWsReconnectAttempts) {
                setRealtimeConnectionState('polling');
                return;
            }
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
                wsReconnectAttempts = 0;
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

        // Nav parent-child: cho phép mở nhiều menu con cùng lúc và xoay mũi tên
        document.querySelectorAll('.nav-parent > .nav-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.closest('.nav-parent');
                const menu = parent.querySelector('.nav-child-menu');
                const isVisible = !menu.hasAttribute('hidden');
                if (isVisible) {
                    menu.setAttribute('hidden', '');
                    parent.classList.remove('expanded');
                } else {
                    menu.removeAttribute('hidden');
                    parent.classList.add('expanded');
                }
            });
        });

        // Tự động mở submenu nếu đang ở trang con hoặc menu cha đang active
        document.querySelectorAll('.nav-parent').forEach(parent => {
            const hasActiveChild = parent.querySelector('.nav-child-menu a.active, .nav-toggle.active');
            if (hasActiveChild) {
                const menu = parent.querySelector('.nav-child-menu');
                if (menu) menu.removeAttribute('hidden');
                parent.classList.add('expanded');
            }
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
