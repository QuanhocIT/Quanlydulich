<?php
$pageTitle = 'Dashboard Admin';
$currentPage = 'dashboard';
ob_start();

$totalTours = count($tours ?? []);
$totalRevenue = array_sum(array_map(static function ($value) {
    return (float)$value;
}, array_values($doanhThuTheoThang ?? [])));
$totalBookings = array_sum(array_map(static function ($value) {
    return (int)$value;
}, array_values($bookingStatusStats ?? [])));
$totalCustomers = array_sum(array_map(static function ($value) {
    return (int)$value;
}, array_values($khachHangMoiTheoThang ?? [])));
$monthlyRevenue = (float)($kpiAlerts['monthToDateRevenue'] ?? 0);
$pendingBookings = (int)($kpiAlerts['bookingPending'] ?? 0);
$overdueDebt = (int)($kpiAlerts['overdueDebt'] ?? 0);
$automationEvents = (int)($automationSnapshot['recentEvents24h'] ?? 0);
$totalDepartures = (int)array_sum(array_map('intval', array_values($lichKhoiHanhStats ?? [])));
$orderSummaryTotal = max(1, $pendingBookings + $totalDepartures + $automationEvents);
$pendingRatio = $totalBookings > 0 ? round(($pendingBookings / $totalBookings) * 100, 1) : 0;
$lastAutomationEventAt = (string)($automationSnapshot['recentEvents'][0]['created_at'] ?? '-');

// Enhanced KPI metrics for ORDER SUMMARY
$confirmedBookings = (int)($bookingStatusStats['Đã xác nhận'] ?? $bookingStatusStats['Confirmed'] ?? 0);
$cancelledBookings = (int)($bookingStatusStats['Đã hủy'] ?? $bookingStatusStats['Cancelled'] ?? 0);
$bookingConfirmationRate = $totalBookings > 0 ? round(($confirmedBookings / $totalBookings) * 100, 1) : 0;
$bookingCancellationRate = $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 1) : 0;

// Revenue metrics
$avgBookingValue = $confirmedBookings > 0 ? round($monthlyRevenue / $confirmedBookings, 0) : 0;
$revenueStatus = $monthlyRevenue > 0 ? ($monthlyRevenue / 1000000) : 0; // Quick indicator
$revenueStatusLabel = $revenueStatus > 2 ? 'Cao' : ($revenueStatus > 1 ? 'Trung bình' : 'Thấp');

// Payment health
$overdueDebtStatus = $overdueDebt > 0 ? 'Cảnh báo' : 'Bình thường';
$debtRatio = $monthlyRevenue > 0 ? round(($overdueDebt / $monthlyRevenue) * 100, 1) : 0;

// Operational metrics
$toursWithDepartures = count(array_filter($lichKhoiHanhStats ?? [], static function ($val) {
    return (int)$val > 0;
}));
$avgDeparturesPerTour = $totalTours > 0 ? round($totalDepartures / $totalTours, 1) : 0;
$systemHealthScore = min(100, max(0, 100 - ($debtRatio * 0.5) - ($bookingCancellationRate * 0.3)));

// Alert indicators
$hasOverduePayments = $overdueDebt > 0;
$hasHighCancellationRate = $bookingCancellationRate > 15;
$hasLowConfirmationRate = $bookingConfirmationRate < 50 && $totalBookings > 10;
$alertCount = (int)$hasOverduePayments + (int)$hasHighCancellationRate + (int)$hasLowConfirmationRate;

$orderSummaryItems = [
    [
        'label' => 'Booking chờ xử lý',
        'value' => $pendingBookings,
        'class' => 'pink',
    ],
    [
        'label' => 'Lịch khởi hành',
        'value' => $totalDepartures,
        'class' => 'teal',
    ],
    [
        'label' => 'Sự kiện tự động hóa 24h',
        'value' => $automationEvents,
        'class' => 'violet',
    ],
];

$topTours = $tours ?? [];
usort($topTours, static function ($left, $right) {
    return (float)($right['loi_nhuan'] ?? 0) <=> (float)($left['loi_nhuan'] ?? 0);
});
$topTours = array_slice($topTours, 0, 5);

$recentEvents = $automationSnapshot['recentEvents'] ?? [];
$recentEventCount = count($recentEvents);

$statusPalette = [
    '#5eead4',
    '#f472b6',
    '#818cf8',
    '#fbbf24',
    '#38bdf8',
    '#fb7185',
];

$metricCards = [
    [
        'label' => 'Tổng tour',
        'value' => number_format($totalTours, 0, ',', '.'),
        'meta' => 'Tour đang được theo dõi',
        'accent' => 'teal',
        'icon' => 'bi-map',
    ],
    [
        'label' => 'Doanh thu tháng',
        'value' => number_format($monthlyRevenue, 0, ',', '.') . 'đ',
        'meta' => 'Lũy kế đến hiện tại',
        'accent' => 'pink',
        'icon' => 'bi-cash-stack',
    ],
    [
        'label' => 'Tổng booking',
        'value' => number_format($totalBookings, 0, ',', '.'),
        'meta' => $pendingBookings . ' booking chờ xác nhận',
        'accent' => 'violet',
        'icon' => 'bi-journal-check',
    ],
    [
        'label' => 'Khách hàng mới',
        'value' => number_format($totalCustomers, 0, ',', '.'),
        'meta' => '12 tháng gần nhất',
        'accent' => 'sky',
        'icon' => 'bi-people',
    ],
];

$actionCards = [
    [
        'title' => 'Quản lý tour',
        'desc' => 'Theo dõi danh sách tour, lịch trình và hiệu quả doanh thu.',
        'href' => 'index.php?act=admin/quanLyTour',
        'icon' => 'bi-geo-alt',
    ],
    [
        'title' => 'Quản lý booking',
        'desc' => 'Xử lý đặt chỗ, xác nhận nhanh và theo dõi trạng thái booking.',
        'href' => 'index.php?act=admin/quanLyBooking',
        'icon' => 'bi-journal-bookmark',
    ],
    [
        'title' => 'Lịch khởi hành',
        'desc' => 'Kiểm soát lịch chạy tour và số đợt khởi hành theo tháng.',
        'href' => 'index.php?act=lichKhoiHanh/index',
        'icon' => 'bi-calendar3',
    ],
    [
        'title' => 'Báo cáo tài chính',
        'desc' => 'Xem thu chi, lãi lỗ và các chỉ số vận hành quan trọng.',
        'href' => 'index.php?act=admin/baoCaoTaiChinh',
        'icon' => 'bi-bar-chart',
    ],
];
?>

<style>
    :root {
        --dash-bg-1: #131616;
        --dash-bg-2: #181b1c;
        --dash-bg-3: #111313;
        --dash-panel: linear-gradient(180deg, rgba(28, 30, 31, 0.92), rgba(20, 22, 23, 0.96));
        --dash-panel-border: rgba(212, 175, 55, 0.2);
        --dash-text-main: #eef2ff;
        --dash-text-muted: #c8c9cf;
        --dash-accent: #d4af37;
    }

    body.page-dashboard .content-area {
        padding: 22px;
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, 0.12), transparent 24%),
            radial-gradient(circle at bottom left, rgba(32, 178, 170, 0.07), transparent 26%),
            linear-gradient(135deg, var(--dash-bg-1) 0%, var(--dash-bg-2) 48%, var(--dash-bg-3) 100%);
    }

    .admin-dashboard-shell {
        color: var(--dash-text-main);
    }

    .dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.95fr);
        gap: 22px;
        margin-bottom: 22px;
    }

    .hero-panel,
    .hero-side-panel,
    .metric-card,
    .dashboard-panel,
    .tour-card,
    .action-card {
        background: var(--dash-panel);
        border: 1px solid var(--dash-panel-border);
        border-radius: 22px;
        box-shadow: 0 22px 44px rgba(11, 15, 35, 0.28);
    }

    .dashboard-hero {
        align-items: stretch;
    }

    .dashboard-hero .hero-panel {
        position: relative;
        overflow: hidden;
        padding: 25px !important;
        display: flex;
        flex-direction: column;
    }

    .hero-panel::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top left, rgba(212, 175, 55, 0.15), transparent 35%),
            radial-gradient(circle at bottom right, rgba(45, 212, 191, 0.08), transparent 35%);
        pointer-events: none;
    }

    /* Decorative circle in hero */
    .hero-panel::after {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        border: 1px solid rgba(212, 175, 55, 0.12);
        pointer-events: none;
    }

    .dashboard-hero .hero-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        flex: 1;
        gap: 8px;
        margin: 0 !important;
        padding: 0 !important;
        justify-content: flex-start;
    }

    .dashboard-hero .hero-content > * {
        margin-top: 0 !important;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(212, 175, 55, 0.12);
        color: #f7e4a9;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .hero-title {
        margin: 0;
        font-size: 1.8rem;
        line-height: 1.3;
        font-weight: 700;
        color: #ffffff;
    }

    .hero-description {
        max-width: 680px;
        margin: 0;
        color: var(--dash-text-muted);
        font-size: 0.95rem;
        line-height: 1.65;
    }

    /* KPI Cards Grid */
    .dashboard-hero .hero-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-top: 6px;
    }

    .hero-kpi-card {
        position: relative;
        padding: 22px 16px;
        border-radius: 18px;
        border: 1px solid rgba(212, 175, 55, 0.15);
        background: rgba(10, 14, 22, 0.45);
        overflow: hidden;
        transition: border-color 0.2s, transform 0.2s;
    }

    .hero-kpi-card:hover {
        border-color: rgba(212, 175, 55, 0.35);
        transform: translateY(-2px);
    }

    .hero-kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        border-radius: 16px 16px 0 0;
    }

    .hero-kpi-card.gold::before { background: linear-gradient(90deg, #d4af37, #f7e4a9); }
    .hero-kpi-card.teal::before { background: linear-gradient(90deg, #2dd4bf, #5eead4); }
    .hero-kpi-card.red::before  { background: linear-gradient(90deg, #ef4444, #f87171); }

    .hero-kpi-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        font-size: 1.18rem;
        margin-bottom: 14px;
    }

    .hero-kpi-icon.gold { background: rgba(212, 175, 55, 0.18); color: #d4af37; }
    .hero-kpi-icon.teal { background: rgba(45, 212, 191, 0.18); color: #2dd4bf; }
    .hero-kpi-icon.red  { background: rgba(239, 68, 68, 0.18); color: #ef4444; }

    .hero-kpi-value {
        display: block;
        font-size: 1.72rem;
        font-weight: 700;
        color: #fff;
        line-height: 1;
        margin-bottom: 7px;
    }

    .hero-kpi-label {
        display: block;
        font-size: 0.78rem;
        color: var(--dash-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Top Tours Ranked */
    .dashboard-hero .hero-top-tours {
        margin-top: 8px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .hero-tours-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .hero-tours-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 0.75rem;
        color: var(--dash-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        font-weight: 600;
    }

    .hero-tours-label i {
        color: #d4af37;
    }

    .hero-tours-badge {
        font-size: 0.68rem;
        color: #d4af37;
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid rgba(212, 175, 55, 0.2);
        padding: 2px 8px;
        border-radius: 99px;
    }

    .hero-tour-rows {
        display: flex;
        flex-direction: column;
        flex: 1;
        gap: 0;
    }

    .hero-tour-row {
        display: grid;
        grid-template-columns: 28px 1fr auto;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid rgba(212, 175, 55, 0.08);
        background: rgba(10, 14, 22, 0.35);
        margin-bottom: 8px;
        flex: 1;
        transition: background 0.2s, border-color 0.2s;
    }

    .hero-tour-row:hover {
        background: rgba(212, 175, 55, 0.07);
        border-color: rgba(212, 175, 55, 0.2);
    }

    .hero-tour-rank {
        width: 24px;
        height: 24px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .hero-tour-rank.r1 { background: rgba(212, 175, 55, 0.25); color: #d4af37; }
    .hero-tour-rank.r2 { background: rgba(148, 163, 184, 0.2); color: #94a3b8; }
    .hero-tour-rank.r3 { background: rgba(180, 120, 60, 0.2); color: #cd7f32; }

    .hero-tour-info {
        min-width: 0;
    }

    .hero-tour-name {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        color: #f0f9ff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .hero-tour-sub {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 4px;
    }

    .hero-tour-depart {
        font-size: 0.72rem;
        color: #38bdf8;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .hero-tour-profit {
        text-align: right;
        flex-shrink: 0;
    }

    .hero-tour-profit strong {
        display: block;
        font-size: 1.08rem;
        color: #d4af37;
        font-weight: 700;
    }

    .hero-tour-profit small {
        font-size: 0.68rem;
        color: var(--dash-text-muted);
    }

    .hero-side-panel {
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 20px;
    }

    .side-chart-wrap {
        position: relative;
        height: 220px;
        padding: 8px 0 0;
    }

    .side-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .side-kpi {
        border: 1px solid rgba(212, 175, 55, 0.18);
        background: rgba(10, 14, 22, 0.42);
        border-radius: 12px;
        padding: 10px;
    }

    .side-kpi-label {
        display: block;
        color: var(--dash-text-muted);
        font-size: 0.74rem;
        margin-bottom: 6px;
    }

    .side-kpi-value {
        color: #f8fafc;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1;
    }

    .side-kpi-note {
        color: #9fb0be;
        font-size: 0.72rem;
        margin-top: 6px;
    }

    /* Enhanced KPI sections */
    .side-kpi-extended {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-top: 6px;
    }

    .side-kpi-metric {
        background: rgba(212, 175, 55, 0.08);
        border: 1px solid rgba(212, 175, 55, 0.12);
        border-radius: 10px;
        padding: 10px;
        text-align: center;
    }

    .side-kpi-metric-label {
        display: block;
        color: var(--dash-text-muted);
        font-size: 0.7rem;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .side-kpi-metric-value {
        display: block;
        color: #d4af37;
        font-size: 0.95rem;
        font-weight: 700;
    }

    /* Health indicator */
    .health-status-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background: rgba(20, 22, 23, 0.6);
        border-radius: 10px;
        margin-bottom: 8px;
    }

    .health-score {
        flex: 1;
        height: 6px;
        background: rgba(212, 175, 55, 0.1);
        border-radius: 3px;
        overflow: hidden;
    }

    .health-score-fill {
        height: 100%;
        background: linear-gradient(90deg, #ef4444, #f59e0b, #10b981);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .health-label {
        font-size: 0.75rem;
        color: var(--dash-text-muted);
        white-space: nowrap;
    }

    /* Alert badges */
    .side-alerts-section {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .alert-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 0.8rem;
        background: rgba(239, 68, 68, 0.1);
        border-left: 3px solid #ef4444;
    }

    .alert-badge.warning {
        background: rgba(245, 158, 11, 0.1);
        border-left-color: #f59e0b;
    }

    .alert-badge.success {
        background: rgba(16, 185, 129, 0.1);
        border-left-color: #10b981;
    }

    .alert-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        font-size: 0.65rem;
    }

    .side-title {
        margin: 0 0 10px;
        color: #fff;
        font-size: 1.1rem;
        font-weight: 700;
    }

    .side-copy {
        margin: 0;
        color: var(--dash-text-muted);
        line-height: 1.7;
        font-size: 0.94rem;
    }

    .progress-group {
        display: grid;
        gap: 14px;
    }

    .progress-item-head small {
        color: #97a6b2;
        font-size: 0.72rem;
        margin-left: 8px;
    }

    .progress-item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
        color: #eef2ff;
        font-size: 0.92rem;
    }

    .progress-track {
        height: 10px;
        border-radius: 999px;
        background: rgba(212, 175, 55, 0.1);
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: inherit;
    }

    .progress-bar.teal { background: linear-gradient(90deg, #2dd4bf, #14b8a6); }
    .progress-bar.pink { background: linear-gradient(90deg, #eab308, #d4af37); }
    .progress-bar.violet { background: linear-gradient(90deg, #60a5fa, #38bdf8); }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }

    .metric-card {
        position: relative;
        overflow: hidden;
        padding: 22px;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        right: -34px;
        bottom: -34px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.16), transparent 68%);
        pointer-events: none;
    }

    .metric-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .metric-label {
        display: block;
        color: var(--dash-text-muted);
        font-size: 0.9rem;
        margin-bottom: 6px;
    }

    .metric-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.1;
        color: #ffffff;
    }

    .metric-icon {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        color: #fff;
    }

    .metric-icon.teal { background: linear-gradient(135deg, #14b8a6, #5eead4); }
    .metric-icon.pink { background: linear-gradient(135deg, #ec4899, #f472b6); }
    .metric-icon.violet { background: linear-gradient(135deg, #7c3aed, #818cf8); }
    .metric-icon.sky { background: linear-gradient(135deg, #0284c7, #38bdf8); }

    .metric-meta {
        color: var(--dash-text-muted);
        font-size: 0.88rem;
        line-height: 1.6;
    }

    .panel-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr);
        gap: 18px;
        margin-bottom: 18px;
    }

    .dashboard-panel {
        padding: 22px;
    }

    .dashboard-panel.large-panel {
        min-height: 390px;
    }

    .panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .panel-kicker {
        margin-bottom: 8px;
        color: #d4af37;
        font-size: 0.77rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .panel-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: #fff;
    }

    .panel-description {
        margin: 8px 0 0;
        color: var(--dash-text-muted);
        font-size: 0.92rem;
        line-height: 1.65;
    }

    .panel-tag {
        padding: 8px 12px;
        border-radius: 12px;
        background: rgba(212, 175, 55, 0.12);
        color: #f7e4a9;
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .chart-empty {
        margin-top: 10px;
        color: var(--dash-text-muted);
        font-size: 0.86rem;
    }

    .chart-wrap {
        position: relative;
        height: 290px;
    }

    .chart-wrap.small {
        height: 250px;
    }

    .summary-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 220px;
        gap: 18px;
        align-items: center;
    }

    .summary-list {
        display: grid;
        gap: 12px;
    }

    .summary-item {
        display: grid;
        gap: 8px;
    }

    .summary-item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #e8ecff;
        font-size: 0.92rem;
    }

    .summary-item .progress-track {
        height: 8px;
    }

    .mini-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .activity-table {
        width: 100%;
        border-collapse: collapse;
    }

    .activity-table th,
    .activity-table td {
        padding: 14px 0;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .activity-table th {
        color: #c9b06a;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    .activity-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-weight: 600;
    }

    .activity-badge {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(212, 175, 55, 0.12);
        color: #fff;
    }

    .severity-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 88px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .severity-pill.high {
        background: rgba(244, 114, 182, 0.18);
        color: #f9a8d4;
    }

    .severity-pill.medium {
        background: rgba(251, 191, 36, 0.18);
        color: #fde68a;
    }

    .severity-pill.low {
        background: rgba(94, 234, 212, 0.16);
        color: #99f6e4;
    }

    .table-empty {
        padding: 20px 0 6px;
        color: var(--dash-text-muted);
    }

    .tour-card-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 18px;
    }

    .tour-card {
        overflow: hidden;
    }

    .tour-card-cover {
        aspect-ratio: 1 / 0.82;
        display: flex;
        align-items: flex-end;
        padding: 18px;
        background:
            radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 28%),
            linear-gradient(135deg, #52d1c8, #7c3aed);
    }

    .tour-card:nth-child(2) .tour-card-cover {
        background:
            radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 28%),
            linear-gradient(135deg, #f59e0b, #fb7185);
    }

    .tour-card:nth-child(3) .tour-card-cover {
        background:
            radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 28%),
            linear-gradient(135deg, #22c55e, #06b6d4);
    }

    .tour-card:nth-child(4) .tour-card-cover {
        background:
            radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 28%),
            linear-gradient(135deg, #818cf8, #ec4899);
    }

    .tour-card:nth-child(5) .tour-card-cover {
        background:
            radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 28%),
            linear-gradient(135deg, #38bdf8, #14b8a6);
    }

    .tour-card-cover i {
        font-size: 2rem;
        color: rgba(255, 255, 255, 0.96);
    }

    .tour-card-body {
        padding: 16px 18px 18px;
    }

    .tour-card-title {
        margin: 0 0 8px;
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
    }

    .tour-card-meta {
        margin: 0 0 12px;
        color: var(--dash-text-muted);
        font-size: 0.88rem;
        line-height: 1.6;
        min-height: 42px;
    }

    .tour-card-profit {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #eef2ff;
        font-size: 0.88rem;
    }

    .tour-profit-value {
        color: #f5d47b;
        font-weight: 700;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-top: 18px;
    }

    .action-card {
        display: block;
        padding: 20px;
        text-decoration: none;
        color: inherit;
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }

    .action-card:hover {
        transform: translateY(-4px);
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 26px 44px rgba(11, 15, 35, 0.34);
    }

    .action-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 14px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.28), rgba(32, 178, 170, 0.22));
        color: #fff;
        font-size: 1.25rem;
    }

    .action-title {
        margin: 0 0 8px;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
    }

    .action-desc {
        margin: 0;
        color: var(--dash-text-muted);
        line-height: 1.65;
        font-size: 0.9rem;
    }

    @media (max-width: 1320px) {
        .metrics-grid,
        .tour-card-grid,
        .action-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 1100px) {
        .dashboard-hero,
        .panel-grid,
        .summary-layout {
            grid-template-columns: 1fr;
        }

        .mini-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        body.page-dashboard .content-area {
            padding: 16px;
        }

        .hero-panel,
        .hero-side-panel,
        .metric-card,
        .dashboard-panel,
        .tour-card,
        .action-card {
            border-radius: 18px;
        }

        .hero-panel,
        .hero-side-panel,
        .metric-card,
        .dashboard-panel,
        .action-card {
            padding: 18px;
        }

        .hero-title {
            font-size: 1.55rem;
        }

        .dashboard-hero .hero-panel {
            padding: 12px !important;
        }

        .dashboard-hero .hero-content {
            gap: 6px;
        }

        .hero-kicker {
            padding: 6px 10px;
            font-size: 0.76rem;
        }

        .hero-description {
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .dashboard-hero .hero-kpi-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .dashboard-hero .hero-kpi-card {
            padding: 12px !important;
        }

        .dashboard-hero .hero-tour-row {
            padding: 10px !important;
        }

        .side-chart-wrap {
            height: 250px;
        }

        .side-kpi-grid {
            grid-template-columns: 1fr;
        }

        .side-kpi-extended {
            grid-template-columns: 1fr;
        }

        .metrics-grid,
        .tour-card-grid,
        .action-grid {
            grid-template-columns: 1fr;
        }

        .mini-grid {
            gap: 14px;
        }

        .activity-table,
        .activity-table thead,
        .activity-table tbody,
        .activity-table tr,
        .activity-table th,
        .activity-table td {
            display: block;
            width: 100%;
        }

        .activity-table thead {
            display: none;
        }

        .activity-table tr {
            padding: 0 0 12px;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .activity-table td {
            padding: 8px 0;
            border: 0;
        }
    }
</style>

<div class="admin-dashboard-shell">

    <section class="metrics-grid">
        <?php foreach ($metricCards as $card): ?>
            <article class="metric-card">
                <div class="metric-head">
                    <div>
                        <span class="metric-label"><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <div class="metric-value"><?php echo htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <span class="metric-icon <?php echo htmlspecialchars($card['accent'], ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="bi <?php echo htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    </span>
                </div>
                <div class="metric-meta"><?php echo htmlspecialchars($card['meta'], ENT_QUOTES, 'UTF-8'); ?></div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="panel-grid">
        <article class="dashboard-panel large-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-kicker">Revenue</div>
                    <h3 class="panel-title">Doanh thu theo tháng</h3>
                    <p class="panel-description">Biểu đồ chính hiển thị nhịp doanh thu 12 tháng gần nhất, giữ vai trò trung tâm giống dashboard tham chiếu.</p>
                </div>
                <span class="panel-tag">Tổng <?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</span>
            </div>
            <div class="chart-wrap">
                <canvas id="revenueChart"></canvas>
            </div>
            <?php if (empty($doanhThuTheoThang ?? [])): ?>
                <div class="chart-empty">Chưa có dữ liệu doanh thu theo tháng để vẽ biểu đồ.</div>
            <?php endif; ?>
        </article>

        <article class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-kicker">Order Summary</div>
                    <h3 class="panel-title">Cơ cấu booking</h3>
                    <p class="panel-description">Hiển thị phân bổ trạng thái booking hiện tại để admin biết nhóm nào cần ưu tiên xử lý.</p>
                </div>
                <span class="panel-tag">Live snapshot</span>
            </div>

            <div class="summary-layout">
                <div class="summary-list">
                    <?php
                    $statusEntries = array_slice(array_keys($bookingStatusStats ?? []), 0, 4);
                    foreach ($statusEntries as $index => $statusLabel):
                        $count = (int)($bookingStatusStats[$statusLabel] ?? 0);
                        $ratio = $totalBookings > 0 ? ($count / $totalBookings) * 100 : 0;
                        $barClass = $index % 3 === 0 ? 'teal' : ($index % 3 === 1 ? 'pink' : 'violet');
                    ?>
                        <div class="summary-item">
                            <div class="summary-item-head">
                                <span><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong><?php echo number_format($count, 0, ',', '.'); ?></strong>
                            </div>
                            <div class="progress-track">
                                <div class="progress-bar <?php echo $barClass; ?>" style="width: <?php echo min(100, max(4, $ratio)); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="chart-wrap small">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
            </div>
            <?php if (empty($bookingStatusStats ?? [])): ?>
                <div class="chart-empty">Chưa có dữ liệu trạng thái booking.</div>
            <?php endif; ?>
        </article>
    </section>

    <section class="mini-grid">
        <article class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-kicker">Customer Map</div>
                    <h3 class="panel-title">Khách hàng mới theo tháng</h3>
                    <p class="panel-description">Dùng dữ liệu khách mới để tạo nhịp cột màu theo phong cách tương tự ảnh mẫu.</p>
                </div>
                <span class="panel-tag">12 tháng</span>
            </div>
            <div class="chart-wrap small">
                <canvas id="customerChart"></canvas>
            </div>
            <?php if (empty($khachHangMoiTheoThang ?? [])): ?>
                <div class="chart-empty">Chưa có dữ liệu khách hàng mới theo tháng.</div>
            <?php endif; ?>
        </article>

        <article class="dashboard-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-kicker">Departure Flow</div>
                    <h3 class="panel-title">Lịch khởi hành theo tháng</h3>
                    <p class="panel-description">Theo dõi cường độ mở lịch khởi hành và phát hiện giai đoạn vận hành cao điểm.</p>
                </div>
                <span class="panel-tag">Xu hướng</span>
            </div>
            <div class="chart-wrap small">
                <canvas id="departureChart"></canvas>
            </div>
            <?php if (empty($lichKhoiHanhStats ?? [])): ?>
                <div class="chart-empty">Chưa có dữ liệu lịch khởi hành theo tháng.</div>
            <?php endif; ?>
        </article>
    </section>

    <section class="dashboard-panel">
        <div class="panel-header">
            <div>
                <div class="panel-kicker">Recent Activity</div>
                <h3 class="panel-title">Sự kiện vận hành gần đây</h3>
                <p class="panel-description">Bảng này thay vai trò của phần recent customer trong ảnh, nhưng tận dụng dữ liệu sự kiện admin đang có sẵn.</p>
            </div>
            <span class="panel-tag"><?php echo number_format($recentEventCount, 0, ',', '.'); ?> mục gần nhất</span>
        </div>

        <?php if (!empty($recentEvents)): ?>
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Sự kiện</th>
                        <th>Mức độ</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentEvents as $event): ?>
                        <?php
                        $severity = strtolower((string)($event['severity'] ?? 'low'));
                        if (!in_array($severity, ['high', 'medium', 'low'], true)) {
                            $severity = 'low';
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="activity-title">
                                    <span class="activity-badge"><i class="bi bi-activity"></i></span>
                                    <span><?php echo htmlspecialchars((string)($event['title'] ?? 'Sự kiện hệ thống'), ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </td>
                            <td><span class="severity-pill <?php echo $severity; ?>"><?php echo strtoupper($severity); ?></span></td>
                            <td><?php echo htmlspecialchars((string)($event['created_at'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="table-empty">Hiện chưa có dữ liệu sự kiện gần đây để hiển thị.</div>
        <?php endif; ?>
    </section>

    <section class="dashboard-panel" style="margin-top: 18px;">
        <div class="panel-header">
            <div>
                <div class="panel-kicker">Top Tours</div>
                <h3 class="panel-title">Tour nổi bật theo lợi nhuận</h3>
                <p class="panel-description">Dải card cuối trang được thiết kế như phần thumbnail trong ảnh tham chiếu, nhưng dùng dữ liệu tour thực tế từ hệ thống.</p>
            </div>
            <span class="panel-tag">Top <?php echo number_format(count($topTours), 0, ',', '.'); ?></span>
        </div>

        <div class="tour-card-grid">
            <?php if (!empty($topTours)): ?>
                <?php foreach ($topTours as $tour): ?>
                    <article class="tour-card">
                        <div class="tour-card-cover">
                            <i class="bi bi-globe-asia-australia"></i>
                        </div>
                        <div class="tour-card-body">
                            <h4 class="tour-card-title"><?php echo htmlspecialchars((string)($tour['ten_tour'] ?? 'Tour du lịch'), ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p class="tour-card-meta">
                                Tổng thu: <?php echo number_format((float)($tour['tong_thu'] ?? 0), 0, ',', '.'); ?>đ
                            </p>
                            <div class="tour-card-profit">
                                <span>Lợi nhuận</span>
                                <strong class="tour-profit-value"><?php echo number_format((float)($tour['loi_nhuan'] ?? 0), 0, ',', '.'); ?>đ</strong>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="tour-card">
                    <div class="tour-card-cover"><i class="bi bi-info-circle"></i></div>
                    <div class="tour-card-body">
                        <h4 class="tour-card-title">Chưa có dữ liệu tour</h4>
                        <p class="tour-card-meta">Khi hệ thống có dữ liệu doanh thu và chi phí theo tour, khu vực này sẽ tự cập nhật.</p>
                        <div class="tour-card-profit">
                            <span>Trạng thái</span>
                            <strong class="tour-profit-value">Đang chờ</strong>
                        </div>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </section>

    <section class="action-grid">
        <?php foreach ($actionCards as $action): ?>
            <a class="action-card" href="<?php echo htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8'); ?>">
                <span class="action-icon"><i class="bi <?php echo htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                <h4 class="action-title"><?php echo htmlspecialchars($action['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                <p class="action-desc"><?php echo htmlspecialchars($action['desc'], ENT_QUOTES, 'UTF-8'); ?></p>
            </a>
        <?php endforeach; ?>
    </section>
    <br>

    <section class="dashboard-hero">
        <div class="hero-panel">
            <div class="hero-content">
                <span class="hero-kicker"><i class="bi bi-grid-1x2-fill"></i> Trung tâm điều hành</span>
                <h2 class="hero-title">Toàn cảnh kinh doanh hôm nay: doanh thu, xử lý booking và cảnh báo công nợ.</h2>
                <p class="hero-description">
                    Khu vực này ưu tiên các tín hiệu quan trọng để admin ra quyết định nhanh trong ca trực mà không cần mở thêm báo cáo chi tiết.
                </p>

                <div class="hero-kpi-grid">
                    <div class="hero-kpi-card gold">
                        <div class="hero-kpi-icon gold"><i class="bi bi-graph-up-arrow"></i></div>
                        <span class="hero-kpi-value" id="kpiTotalRevenue"><?php echo number_format($totalRevenue / 1000000, 0, ',', '.'); ?>M</span>
                        <span class="hero-kpi-label">Doanh thu lũy kế 12 tháng (triệu)</span>
                    </div>
                    <div class="hero-kpi-card teal">
                        <div class="hero-kpi-icon teal"><i class="bi bi-journal-check"></i></div>
                        <span class="hero-kpi-value" id="kpiTotalBookings"><?php echo number_format($totalBookings, 0, ',', '.'); ?></span>
                        <span class="hero-kpi-label"><span id="kpiPendingBookings"><?php echo $pendingBookings; ?></span> booking cần ưu tiên xử lý</span>
                    </div>
                    <div class="hero-kpi-card red">
                        <div class="hero-kpi-icon red"><i class="bi bi-exclamation-triangle"></i></div>
                        <span class="hero-kpi-value"><?php echo number_format($overdueDebt, 0, ',', '.'); ?></span>
                        <span class="hero-kpi-label">Công nợ quá hạn cần thu hồi</span>
                    </div>
                </div>

                <?php if (!empty($topTours)): ?>
                <?php $topProfitMax = max(1, (float)($topTours[0]['loi_nhuan'] ?? 1)); ?>
                <div class="hero-top-tours">
                    <div class="hero-tours-header">
                        <span class="hero-tours-label"><i class="bi bi-trophy"></i> Bảng xếp hạng tour theo lợi nhuận</span>
                        <span class="hero-tours-badge">Cập nhật theo tháng</span>
                    </div>
                    <?php foreach (array_slice($topTours, 0, 3) as $idx => $tour): ?>
                        <?php
                        $tourName = htmlspecialchars((string)($tour['ten_tour'] ?? 'N/A'), ENT_QUOTES, 'UTF-8');
                        $tourProfit = (float)($tour['loi_nhuan'] ?? 0);
                        $departures = (int)($lichKhoiHanhStats[$tour['id_tour'] ?? ''] ?? 0);
                        $profitM = round($tourProfit / 1000000, 1);
                        $rankClass = ['r1', 'r2', 'r3'][$idx];
                        ?>
                        <div class="hero-tour-row">
                            <div class="hero-tour-rank <?php echo $rankClass; ?>"><?php echo $idx + 1; ?></div>
                            <div class="hero-tour-info">
                                <span class="hero-tour-name"><?php echo $tourName; ?></span>
                                <div class="hero-tour-sub">
                                    <span class="hero-tour-depart"><i class="bi bi-calendar3"></i> <?php echo $departures; ?> đợt khởi hành</span>
                                </div>
                            </div>
                            <div class="hero-tour-profit">
                                <strong><?php echo $profitM; ?>M</strong>
                                <small>lợi nhuận ước tính</small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="hero-side-panel">
            <div>
                <div class="panel-kicker">Order Summary</div>
                <h3 class="side-title">Tình hình vận hành nổi bật</h3>
                <p class="side-copy">Cập nhật thực thời về booking, lịch khởi hành, doanh thu và các cảnh báo vận hành quan trọng.</p>
            </div>

            <!-- Main Chart -->
            <div class="side-chart-wrap">
                <canvas id="orderSummaryChart"></canvas>
            </div>

            <!-- Core KPIs -->
            <div class="side-kpi-grid">
                <div class="side-kpi">
                    <span class="side-kpi-label">Tổng tín hiệu</span>
                    <div class="side-kpi-value"><?php echo number_format($orderSummaryTotal, 0, ',', '.'); ?></div>
                    <div class="side-kpi-note">3 nguồn dữ liệu</div>
                </div>
                <div class="side-kpi">
                    <span class="side-kpi-label">Doanh thu tháng</span>
                    <div class="side-kpi-value" style="color: #d4af37; font-size: 0.95rem;"><?php echo number_format($monthlyRevenue / 1000000, 1, ',', '.'); ?>M</div>
                    <div class="side-kpi-note">Lũy kế <?php echo $revenueStatusLabel; ?></div>
                </div>
                <div class="side-kpi">
                    <span class="side-kpi-label">Tỷ lệ xác nhận</span>
                    <div class="side-kpi-value" style="color: #38bdf8;"><?php echo number_format($bookingConfirmationRate, 1, ',', '.'); ?>%</div>
                    <div class="side-kpi-note">Trên tổng booking</div>
                </div>
            </div>

            <!-- Extended metrics grid -->
            <div class="side-kpi-extended">
                <div class="side-kpi-metric">
                    <span class="side-kpi-metric-label">Trung bình/booking</span>
                    <span class="side-kpi-metric-value"><?php echo number_format($avgBookingValue, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="side-kpi-metric">
                    <span class="side-kpi-metric-label">Hủy bỏ</span>
                    <span class="side-kpi-metric-value" style="color: #ef4444;"><?php echo number_format($bookingCancellationRate, 1, ',', '.'); ?>%</span>
                </div>
                <div class="side-kpi-metric">
                    <span class="side-kpi-metric-label">Tours hoạt động</span>
                    <span class="side-kpi-metric-value"><?php echo $toursWithDepartures . '/' . $totalTours; ?></span>
                </div>
                <div class="side-kpi-metric">
                    <span class="side-kpi-metric-label">Nợ quá hạn</span>
                    <span class="side-kpi-metric-value" style="color: <?php echo $hasOverduePayments ? '#ef4444' : '#10b981'; ?>;"><?php echo number_format($overdueDebt, 0, ',', '.'); ?></span>
                </div>
            </div>

            <!-- System Health -->
            <div class="health-status-bar">
                <span class="health-label">Sức khỏe hệ thống</span>
                <div class="health-score">
                    <div class="health-score-fill" style="width: <?php echo $systemHealthScore; ?>%"></div>
                </div>
                <span class="health-label"><?php echo (int)$systemHealthScore; ?>%</span>
            </div>

            <!-- Alerts Section -->
            <div class="side-alerts-section">
                <?php if ($hasOverduePayments): ?>
                    <div class="alert-badge">
                        <span class="alert-icon">⚠</span>
                        <span><?php echo number_format($overdueDebt, 0, ',', '.'); ?>đ nợ quá hạn - Tỷ lệ <?php echo number_format($debtRatio, 1, ',', '.'); ?>%</span>
                    </div>
                <?php endif; ?>
                <?php if ($hasHighCancellationRate): ?>
                    <div class="alert-badge warning">
                        <span class="alert-icon">!</span>
                        <span>Tỷ lệ hủy cao <?php echo number_format($bookingCancellationRate, 1, ',', '.'); ?>% - Cần theo sát</span>
                    </div>
                <?php endif; ?>
                <?php if ($hasLowConfirmationRate): ?>
                    <div class="alert-badge warning">
                        <span class="alert-icon">!</span>
                        <span>Xác nhận booking chậm <?php echo number_format($bookingConfirmationRate, 1, ',', '.'); ?>%</span>
                    </div>
                <?php endif; ?>
                <?php if ($alertCount === 0): ?>
                    <div class="alert-badge success">
                        <span class="alert-icon">✓</span>
                        <span>Tất cả chỉ số hoạt động bình thường</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Progress breakdown -->
            <div class="progress-group">
                <div style="font-size: 0.8rem; color: var(--dash-text-muted); margin-bottom: 4px;">Phân bổ tín hiệu:</div>
                <?php foreach ($orderSummaryItems as $item): ?>
                    <?php
                    $itemValue = (int)($item['value'] ?? 0);
                    $itemPercent = $orderSummaryTotal > 0 ? ($itemValue / $orderSummaryTotal) * 100 : 0;
                    ?>
                    <div class="progress-item">
                        <div class="progress-item-head">
                            <span><?php echo htmlspecialchars((string)($item['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            <strong>
                                <?php echo number_format($itemValue, 0, ',', '.'); ?>
                                <small><?php echo number_format($itemPercent, 1, ',', '.'); ?>%</small>
                            </strong>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar <?php echo htmlspecialchars((string)($item['class'] ?? 'teal'), ENT_QUOTES, 'UTF-8'); ?>" style="width: <?php echo min(100, max(8, $itemPercent)); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const revenueLabels = <?php echo json_encode(array_keys($doanhThuTheoThang ?? []), JSON_UNESCAPED_UNICODE); ?>;
    const revenueValues = <?php echo json_encode(array_map('floatval', array_values($doanhThuTheoThang ?? [])), JSON_UNESCAPED_UNICODE); ?>;
    const bookingStatusLabels = <?php echo json_encode(array_keys($bookingStatusStats ?? []), JSON_UNESCAPED_UNICODE); ?>;
    const bookingStatusValues = <?php echo json_encode(array_map('intval', array_values($bookingStatusStats ?? [])), JSON_UNESCAPED_UNICODE); ?>;
    const customerLabels = <?php echo json_encode(array_keys($khachHangMoiTheoThang ?? []), JSON_UNESCAPED_UNICODE); ?>;
    const customerValues = <?php echo json_encode(array_map('intval', array_values($khachHangMoiTheoThang ?? [])), JSON_UNESCAPED_UNICODE); ?>;
    const departureLabels = <?php echo json_encode(array_keys($lichKhoiHanhStats ?? []), JSON_UNESCAPED_UNICODE); ?>;
    const departureValues = <?php echo json_encode(array_map('intval', array_values($lichKhoiHanhStats ?? [])), JSON_UNESCAPED_UNICODE); ?>;
    const orderSummaryLabels = <?php echo json_encode(array_map(static fn($item) => (string)($item['label'] ?? ''), $orderSummaryItems), JSON_UNESCAPED_UNICODE); ?>;
    const orderSummaryValues = <?php echo json_encode(array_map(static fn($item) => (int)($item['value'] ?? 0), $orderSummaryItems), JSON_UNESCAPED_UNICODE); ?>;

    if (typeof Chart === 'undefined') {
        document.querySelectorAll('.chart-wrap').forEach(function (container) {
            const fallback = document.createElement('div');
            fallback.className = 'chart-empty';
            fallback.textContent = 'Không tải được thư viện biểu đồ (Chart.js). Dữ liệu tổng quan vẫn hiển thị ở các thẻ số liệu phía trên.';
            container.parentNode.appendChild(fallback);
        });
    } else {
    Chart.defaults.color = '#b8c0e4';
    Chart.defaults.font.family = 'Segoe UI, Tahoma, sans-serif';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';

    const dashboardColors = {
        teal: '#5eead4',
        pink: '#d4af37',
        violet: '#38bdf8',
        amber: '#fbbf24',
        sky: '#38bdf8',
        rose: '#fb7185'
    };

    function currency(value) {
        return Number(value || 0).toLocaleString('vi-VN') + 'đ';
    }

    const revenueCanvas = document.getElementById('revenueChart');
    if (revenueCanvas) {
        new Chart(revenueCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Doanh thu',
                    data: revenueValues,
                    borderColor: dashboardColors.pink,
                    backgroundColor: 'rgba(244, 114, 182, 0.18)',
                    fill: true,
                    tension: 0.42,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: dashboardColors.teal,
                    pointBorderWidth: 2,
                    pointBorderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(20, 24, 44, 0.96)',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1,
                        callbacks: {
                            label(context) {
                                return 'Doanh thu: ' + currency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        ticks: {
                            callback(value) {
                                return Number(value).toLocaleString('vi-VN');
                            }
                        }
                    }
                }
            }
        });
    }

    const bookingCanvas = document.getElementById('bookingStatusChart');
    if (bookingCanvas) {
        new Chart(bookingCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: bookingStatusLabels,
                datasets: [{
                    data: bookingStatusValues,
                    backgroundColor: [
                        dashboardColors.teal,
                        dashboardColors.pink,
                        dashboardColors.violet,
                        dashboardColors.amber,
                        dashboardColors.sky,
                        dashboardColors.rose
                    ],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#d6ddff',
                            boxWidth: 12,
                            usePointStyle: true,
                            padding: 14
                        }
                    }
                }
            }
        });
    }

    const orderSummaryCanvas = document.getElementById('orderSummaryChart');
    if (orderSummaryCanvas) {
        const totalOrderSummary = orderSummaryValues.reduce(function (sum, value) {
            return sum + Number(value || 0);
        }, 0);

        if (totalOrderSummary > 0) {
            const centerTextPlugin = {
                id: 'orderSummaryCenterText',
                afterDraw(chart) {
                    const meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || !meta.data.length) {
                        return;
                    }

                    const x = meta.data[0].x;
                    const y = meta.data[0].y;
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.fillStyle = '#f8fafc';
                    ctx.font = '700 22px Segoe UI';
                    ctx.fillText(String(totalOrderSummary), x, y - 2);
                    ctx.fillStyle = '#9fb0be';
                    ctx.font = '600 11px Segoe UI';
                    ctx.fillText('Tổng tín hiệu', x, y + 16);
                    ctx.restore();
                }
            };

            new Chart(orderSummaryCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: orderSummaryLabels,
                    datasets: [{
                        data: orderSummaryValues,
                        backgroundColor: [
                            'rgba(212, 175, 55, 0.95)',
                            'rgba(45, 212, 191, 0.9)',
                            'rgba(56, 189, 248, 0.9)'
                        ],
                        borderColor: [
                            'rgba(247, 228, 169, 0.95)',
                            'rgba(153, 246, 228, 0.95)',
                            'rgba(186, 230, 253, 0.95)'
                        ],
                        borderWidth: 1.2,
                        hoverOffset: 10,
                        spacing: 2,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    animation: {
                        animateRotate: true,
                        animateScale: true
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(20, 24, 44, 0.96)',
                            borderColor: 'rgba(255,255,255,0.08)',
                            borderWidth: 1,
                            callbacks: {
                                label(context) {
                                    const raw = Number(context.raw || 0);
                                    const percent = totalOrderSummary > 0
                                        ? Math.round((raw / totalOrderSummary) * 100)
                                        : 0;
                                    return `${context.label}: ${raw.toLocaleString('vi-VN')} (${percent}%)`;
                                }
                            }
                        }
                    }
                },
                plugins: [centerTextPlugin]
            });
        } else if (orderSummaryCanvas.parentNode) {
            orderSummaryCanvas.parentNode.innerHTML = '<div class="chart-empty">Chưa đủ dữ liệu để hiển thị biểu đồ tóm tắt.</div>';
        }
    }

    const customerCanvas = document.getElementById('customerChart');
    if (customerCanvas) {
        new Chart(customerCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: customerLabels,
                datasets: [{
                    label: 'Khách hàng mới',
                    data: customerValues,
                    backgroundColor: [
                        dashboardColors.teal,
                        dashboardColors.pink,
                        dashboardColors.violet,
                        dashboardColors.amber,
                        dashboardColors.sky,
                        dashboardColors.rose,
                        dashboardColors.teal,
                        dashboardColors.pink,
                        dashboardColors.violet,
                        dashboardColors.amber,
                        dashboardColors.sky,
                        dashboardColors.rose
                    ],
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }

    const departureCanvas = document.getElementById('departureChart');
    if (departureCanvas) {
        new Chart(departureCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: departureLabels,
                datasets: [{
                    label: 'Lịch khởi hành',
                    data: departureValues,
                    backgroundColor: 'rgba(94, 234, 212, 0.78)',
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(20, 24, 44, 0.96)',
                        borderColor: 'rgba(255,255,255,0.08)',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
        }
</script>
<script>
(function() {
    // Listen for AdminWS notifications broadcast by aventura.php layout
    // When new payments/bookings arrive, fetch updated KPI snapshot
    var kpiRefreshTimer = null;
    function scheduleKpiRefresh() {
        if (kpiRefreshTimer) return;
        kpiRefreshTimer = window.setTimeout(function() {
            kpiRefreshTimer = null;
            fetch('index.php?act=admin/dashboardKpiSnapshot&_ts=' + Date.now(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) {
                return r.ok ? r.json() : null;
            }).then(function(data) {
                if (!data || data.success !== true) return;
                var elRevenue = document.getElementById('kpiTotalRevenue');
                var elBookings = document.getElementById('kpiTotalBookings');
                var elPending = document.getElementById('kpiPendingBookings');
                if (elRevenue && data.total_revenue != null) {
                    var rev = Math.round(Number(data.total_revenue) / 1000000);
                    elRevenue.textContent = rev.toLocaleString('vi-VN') + 'M';
                }
                if (elBookings && data.total_bookings != null) {
                    elBookings.textContent = Number(data.total_bookings).toLocaleString('vi-VN');
                }
                if (elPending && data.pending_bookings != null) {
                    elPending.textContent = String(data.pending_bookings);
                }
            }).catch(function() {});
        }, 800);
    }

    document.addEventListener('adminNotification', function(e) {
        var payload = e && e.detail;
        if (!payload || payload.success !== true) return;
        scheduleKpiRefresh();
    });
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
