<?php
$pageTitle = 'Dashboard Admin';
$currentPage = 'dashboard';
ob_start();
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .feature-card {
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 22px;
        padding: 30px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--text-light);
        display: block;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        box-shadow: 0 22px 40px rgba(0, 0, 0, 0.12);
    }

    .feature-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent, rgba(212, 175, 55, 0.08), transparent);
        transform: translateX(-100%);
        transition: transform 0.55s ease;
    }

    .feature-card:hover::before {
        transform: translateX(100%);
    }

    .feature-card:hover {
        border-color: rgba(212, 175, 55, 0.35);
        transform: translateY(-6px);
        box-shadow: 0 26px 48px rgba(212, 175, 55, 0.12);
    }

    .feature-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.16), rgba(212, 175, 55, 0.04));
        color: var(--accent-gold);
        transition: all 0.3s ease;
    }

    .feature-card:hover .feature-icon {
        transform: scale(1.08) rotate(4deg);
    }

    .feature-card h5 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
        letter-spacing: 0.02em;
    }

    .feature-card p {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.7;
    }

    .notification-badge {
        position: absolute;
        top: 16px;
        right: 16px;
        min-width: 26px;
        height: 26px;
        padding: 0 8px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ef4444, #f97316);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(239, 68, 68, 0.28);
    }

    .welcome-admin {
        position: relative;
        border-radius: 26px;
        padding: 28px 32px;
        margin-bottom: 34px;
        display: flex;
        align-items: center;
        gap: 24px;
        overflow: hidden;
        border: 1px solid rgba(212, 175, 55, 0.16);
        background:
            radial-gradient(circle at top left, rgba(255, 224, 130, 0.18), transparent 32%),
            linear-gradient(135deg, rgba(45, 45, 45, 0.92), rgba(58, 46, 19, 0.94));
        box-shadow: 0 24px 52px rgba(0, 0, 0, 0.16);
    }

    .welcome-glow {
        position: absolute;
        inset: auto -10% -30% auto;
        width: 280px;
        height: 280px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 236, 140, 0.18), transparent 70%);
        pointer-events: none;
    }

    .welcome-avatar {
        width: 68px;
        height: 68px;
        border-radius: 22px;
        background: linear-gradient(135deg, #d4af37, #f6e6a7);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #2d2206;
        box-shadow: 0 14px 28px rgba(212,175,55,0.22);
        z-index: 1;
    }

    .welcome-title {
        margin: 0;
        color: #ffe6a0;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .welcome-desc {
        color: #f7f0d0;
        font-size: 1rem;
        margin-top: 6px;
    }

    .report-card {
        margin: 18px 0 34px;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 22px;
    }

    .chart-box {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        padding: 22px;
        border: 1px solid rgba(212, 175, 55, 0.14);
        background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.1);
        min-height: 100%;
    }

    .chart-box::after {
        content: '';
        position: absolute;
        inset: auto -70px -70px auto;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(212, 175, 55, 0.08), transparent 70%);
        pointer-events: none;
    }

    .chart-box.wide {
        grid-column: span 8;
    }

    .chart-box.medium {
        grid-column: span 4;
    }

    .chart-box.small {
        grid-column: span 4;
    }

    .chart-kicker {
        color: var(--text-muted);
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .chart-title {
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--accent-gold);
        margin-bottom: 8px;
    }

    .chart-description {
        color: var(--text-muted);
        font-size: 0.96rem;
        line-height: 1.65;
        margin-bottom: 16px;
        max-width: 760px;
    }

    .chart-canvas-wrap {
        position: relative;
        width: 100%;
        height: 340px;
    }

    .chart-canvas-wrap.medium {
        height: 300px;
    }

    .chart-canvas-wrap.small {
        height: 240px;
    }

    .chart-footnote {
        margin-top: 14px;
        color: var(--text-muted);
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .insight-list {
        margin-top: 16px;
        display: grid;
        gap: 9px;
    }

    .insight-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-light);
        font-size: 0.92rem;
        line-height: 1.4;
    }

    .insight-rank {
        min-width: 24px;
        height: 24px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(212, 175, 55, 0.16);
        border: 1px solid rgba(212, 175, 55, 0.28);
        color: var(--accent-gold);
        font-size: 0.8rem;
        font-weight: 700;
    }

    .legend-grid {
        margin-top: 16px;
        display: grid;
        gap: 8px;
        max-height: 230px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: var(--text-light);
        font-size: 0.92rem;
        line-height: 1.45;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 999px;
        flex-shrink: 0;
    }

    @media (max-width: 1200px) {
        .chart-box.wide,
        .chart-box.medium,
        .chart-box.small {
            grid-column: span 12;
        }
    }

    @media (max-width: 1024px) {
        .dashboard-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 24px;
        }
    }

    @media (max-width: 700px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .welcome-admin {
            padding: 22px 24px;
            gap: 16px;
            flex-direction: column;
            align-items: flex-start;
        }

        .welcome-avatar {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }

        .welcome-title {
            font-size: 1.45rem;
        }

        .chart-box {
            padding: 18px;
            border-radius: 20px;
        }

        .chart-canvas-wrap {
            height: 280px;
        }

        .chart-canvas-wrap.small,
        .chart-canvas-wrap.medium {
            height: 220px;
        }
    }

    /* === KPI Alerts Styles === */
    .kpi-alerts-section {
        margin: 24px 0 34px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .kpi-alert-card {
        position: relative;
        border-radius: 20px;
        padding: 24px;
        border: 2px solid;
        background: linear-gradient(135deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 180px;
    }

    .kpi-alert-card::before {
        content: '';
        position: absolute;
        inset: auto -50px -50px auto;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        opacity: 0.08;
        pointer-events: none;
    }

    .kpi-alert-card.warning {
        border-color: rgba(239, 68, 68, 0.4);
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.08), rgba(239, 68, 68, 0.02));
    }

    .kpi-alert-card.warning::before {
        background: radial-gradient(circle, rgba(239, 68, 68, 0.2), transparent 70%);
    }

    .kpi-alert-card.critical {
        border-color: rgba(239, 68, 68, 0.6);
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(239, 68, 68, 0.04));
    }

    .kpi-alert-card.critical::before {
        background: radial-gradient(circle, rgba(239, 68, 68, 0.3), transparent 70%);
    }

    .kpi-alert-card.success {
        border-color: rgba(34, 197, 94, 0.3);
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), rgba(34, 197, 94, 0.02));
    }

    .kpi-alert-card.success::before {
        background: radial-gradient(circle, rgba(34, 197, 94, 0.15), transparent 70%);
    }

    .kpi-alert-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    .kpi-alert-label {
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        margin-bottom: 8px;
        z-index: 1;
        position: relative;
    }

    .kpi-alert-number {
        font-size: 2.8rem;
        font-weight: 900;
        margin-bottom: 6px;
        z-index: 1;
        position: relative;
    }

    .kpi-alert-card.warning .kpi-alert-number {
        color: #ef4444;
    }

    .kpi-alert-card.critical .kpi-alert-number {
        color: #f97316;
    }

    .kpi-alert-card.success .kpi-alert-number {
        color: #22c55e;
    }

    .kpi-alert-detail {
        font-size: 0.92rem;
        color: var(--text-muted);
        margin-top: 8px;
        line-height: 1.5;
        z-index: 1;
        position: relative;
    }

    .kpi-alert-action {
        margin-top: 14px;
        display: inline-block;
        z-index: 1;
        position: relative;
    }

    .kpi-alert-action a {
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-block;
    }

    .kpi-alert-card.warning .kpi-alert-action a {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.12);
    }

    .kpi-alert-card.warning .kpi-alert-action a:hover {
        background: rgba(239, 68, 68, 0.24);
    }

    .kpi-alert-card.critical .kpi-alert-action a {
        color: #f97316;
        background: rgba(249, 115, 22, 0.12);
    }

    .kpi-alert-card.critical .kpi-alert-action a:hover {
        background: rgba(249, 115, 22, 0.24);
    }

    .kpi-alert-card.success .kpi-alert-action a {
        color: #22c55e;
        background: rgba(34, 197, 94, 0.12);
    }

    .kpi-alert-card.success .kpi-alert-action a:hover {
        background: rgba(34, 197, 94, 0.24);
    }

    /* === Quick Filters Styles === */
    .quick-filters-section {
        margin: 30px 0 28px;
        padding: 20px 24px;
        border-radius: 20px;
        border: 1px solid rgba(212, 175, 55, 0.14);
        background: linear-gradient(135deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
        backdrop-filter: blur(10px);
    }

    .quick-filters-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--accent-gold);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quick-filters-form {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: center;
    }

    .quick-filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .quick-filter-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .quick-filter-input,
    .quick-filter-select {
        min-width: 160px;
        padding: 8px 12px;
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        background: rgba(0,0,0,0.2);
        color: var(--text-light);
        font-size: 0.92rem;
        transition: all 0.2s ease;
    }

    .quick-filter-input:focus,
    .quick-filter-select:focus {
        outline: none;
        border-color: rgba(212, 175, 55, 0.5);
        box-shadow: 0 0 8px rgba(212, 175, 55, 0.2);
    }

    .quick-filter-input::placeholder {
        color: var(--text-muted);
    }

    .quick-filter-btn {
        padding: 8px 20px;
        border-radius: 8px;
        border: 1px solid rgba(212, 175, 55, 0.3);
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.2), rgba(212, 175, 55, 0.1));
        color: var(--accent-gold);
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 22px;
    }

    .quick-filter-btn:hover {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.3), rgba(212, 175, 55, 0.2));
        border-color: rgba(212, 175, 55, 0.5);
    }

    @media (max-width: 768px) {
        .quick-filters-form {
            flex-direction: column;
            gap: 12px;
        }

        .quick-filter-input,
        .quick-filter-select {
            width: 100%;
        }

        .quick-filter-btn {
            width: 100%;
        }
    }

</style>

<div class="welcome-admin">
    <div class="welcome-glow"></div>
    <div class="welcome-avatar">📊</div>
    <div class="welcome-text">
        <h2 class="welcome-title">Xin chào Quản trị viên</h2>
        <div class="welcome-desc">Bảng điều khiển được làm mới để bạn theo dõi doanh thu, lợi nhuận và vận hành trực quan hơn.</div>
    </div>
</div>

<!-- === KPI ALERTS SECTION === -->
<div class="kpi-alerts-section">
    <?php
        $monthToDateRevenue = (float)($kpiAlerts['monthToDateRevenue'] ?? 0);
        $monthToDateFrom = (string)($kpiAlerts['monthToDateFrom'] ?? date('Y-m-01'));
        $monthToDateTo = (string)($kpiAlerts['monthToDateTo'] ?? date('Y-m-d'));
        $monthToDateRangeText = date('d/m/Y', strtotime($monthToDateFrom)) . ' - ' . date('d/m/Y', strtotime($monthToDateTo));
        $monthToDateActionUrl = 'index.php?act=admin/lichSuGiaoDich&loai=Thu&tu_ngay=' . urlencode($monthToDateFrom) . '&den_ngay=' . urlencode($monthToDateTo);
    ?>
    <!-- Booking Pending -->
    <div class="kpi-alert-card <?php echo ($kpiAlerts['bookingPending'] > 10) ? 'critical' : (($kpiAlerts['bookingPending'] > 0) ? 'warning' : 'success'); ?>">
        <div>
            <div class="kpi-alert-label">📋 Booking Chờ Xử Lý</div>
            <div class="kpi-alert-number"><?php echo $kpiAlerts['bookingPending']; ?></div>
            <div class="kpi-alert-detail">
                <?php if ($kpiAlerts['bookingPending'] > 10): ?>
                    ⚠️ Có nhiều booking cần xử lý ngay
                <?php elseif ($kpiAlerts['bookingPending'] > 0): ?>
                    ℹ️ Cần xem xét những yêu cầu mới
                <?php else: ?>
                    ✓ Không có booking nào chờ xử lý
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-alert-action">
            <a href="index.php?act=admin/quanLyBooking" title="Xem chi tiết">Xem danh sách →</a>
        </div>
    </div>

    <!-- Month-To-Date Revenue -->
    <div class="kpi-alert-card <?php echo ($monthToDateRevenue > 0) ? 'success' : 'warning'; ?>">
        <div>
            <div class="kpi-alert-label">💰 Doanh Thu Lũy Kế Tháng</div>
            <div class="kpi-alert-number"><?php echo number_format($monthToDateRevenue, 0, ',', '.'); ?>đ</div>
            <div class="kpi-alert-detail">
                <?php if ($monthToDateRevenue > 0): ?>
                    ✓ Từ ngày <?php echo htmlspecialchars(date('d/m', strtotime($monthToDateFrom))); ?> đến <?php echo htmlspecialchars(date('d/m', strtotime($monthToDateTo))); ?>
                <?php else: ?>
                    ℹ️ Chưa có doanh thu trong khoảng <?php echo htmlspecialchars($monthToDateRangeText); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-alert-action">
            <a href="<?php echo htmlspecialchars($monthToDateActionUrl); ?>" title="Xem chi tiết">Xem thu →</a>
        </div>
    </div>

    <!-- Overdue Debt -->
    <div class="kpi-alert-card <?php echo ($kpiAlerts['overdueDebt'] > 3) ? 'critical' : (($kpiAlerts['overdueDebt'] > 0) ? 'warning' : 'success'); ?>">
        <div>
            <div class="kpi-alert-label">📌 Công Nợ Quá Hạn</div>
            <div class="kpi-alert-number"><?php echo $kpiAlerts['overdueDebt']; ?></div>
            <div class="kpi-alert-detail">
                <?php if ($kpiAlerts['overdueDebt'] > 3): ?>
                    ⚠️ Có công nợ sắp hết hạn
                <?php elseif ($kpiAlerts['overdueDebt'] > 0): ?>
                    ℹ️ Cần xử lý công nợ HDV
                <?php else: ?>
                    ✓ Không có công nợ quá hạn
                <?php endif; ?>
            </div>
        </div>
        <div class="kpi-alert-action">
            <a href="index.php?act=admin/congNo" title="Xem chi tiết">Quản lý →</a>
        </div>
    </div>
</div>

<!-- === QUICK FILTERS SECTION === -->
<div class="quick-filters-section">
    <div class="quick-filters-title">⚙️ Lọc nhanh dữ liệu</div>
    <form method="get" class="quick-filters-form" action="index.php">
        <input type="hidden" name="act" value="admin/dashboard">
        
        <div class="quick-filter-group">
            <label class="quick-filter-label">Từ ngày</label>
            <input 
                type="date" 
                name="date_from" 
                class="quick-filter-input"
                value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>"
            >
        </div>

        <div class="quick-filter-group">
            <label class="quick-filter-label">Đến ngày</label>
            <input 
                type="date" 
                name="date_to" 
                class="quick-filter-input"
                value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>"
            >
        </div>

        <div class="quick-filter-group">
            <label class="quick-filter-label">Tour</label>
            <select name="tour_id" class="quick-filter-select">
                <option value="">-- Tất cả tour --</option>
                <?php if (!empty($tours)): ?>
                    <?php foreach ($tours as $tour): ?>
                        <option value="<?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?>" 
                            <?php echo (($_GET['tour_id'] ?? '') === ($tour['ten_tour'] ?? '')) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tour['ten_tour'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="quick-filter-group">
            <label class="quick-filter-label">Trạng thái</label>
            <select name="status" class="quick-filter-select">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="DangXuLy" <?php echo (($_GET['status'] ?? '') === 'DangXuLy') ? 'selected' : ''; ?>>Đang xử lý</option>
                <option value="DaXuLy" <?php echo (($_GET['status'] ?? '') === 'DaXuLy') ? 'selected' : ''; ?>>Đã xử lý</option>
                <option value="HoanThanh" <?php echo (($_GET['status'] ?? '') === 'HoanThanh') ? 'selected' : ''; ?>>Hoàn thành</option>
                <option value="HuyBo" <?php echo (($_GET['status'] ?? '') === 'HuyBo') ? 'selected' : ''; ?>>Hủy bỏ</option>
            </select>
        </div>

        <button type="submit" class="quick-filter-btn">🔍 Lọc dữ liệu</button>
    </form>
</div>

<div class="report-card">
    <div class="chart-grid">
        <div class="chart-box wide">
            <div class="chart-kicker">Revenue Pulse</div>
            <div class="chart-title">Doanh thu theo tháng</div>
            <div class="chart-description">Nhìn nhanh nhịp tăng trưởng và xác định những tháng đóng góp mạnh nhất vào tổng doanh thu.</div>
            <div class="chart-canvas-wrap">
                <canvas id="barChart"></canvas>
            </div>
            <div id="topMonthDescriptions" class="insight-list"></div>
        </div>

        <div class="chart-box medium">
            <div class="chart-kicker">Profit Mix</div>
            <div class="chart-title">Lợi nhuận từng tour</div>
            <div class="chart-description">Tập trung vào nhóm tour mang lại biên lợi nhuận nổi bật.</div>
            <div class="chart-canvas-wrap medium">
                <canvas id="pieChart"></canvas>
            </div>
            <div id="pieLegend" class="legend-grid"></div>
        </div>

        <div class="chart-box small">
            <div class="chart-kicker">Booking Pulse</div>
            <div class="chart-title">Trạng thái booking</div>
            <div class="chart-description">Cơ cấu xử lý booking ở thời điểm hiện tại.</div>
            <div class="chart-canvas-wrap small">
                <canvas id="bookingStatusChart"></canvas>
            </div>
        </div>

        <div class="chart-box small">
            <div class="chart-kicker">Booking Ops</div>
            <div class="chart-title">Quản lý booking</div>
            <div class="chart-description">Số lượng booking theo từng nhóm vận hành.</div>
            <div class="chart-canvas-wrap small">
                <canvas id="bookingManageChart"></canvas>
            </div>
        </div>

        <div class="chart-box small">
            <div class="chart-kicker">Departure Flow</div>
            <div class="chart-title">Lịch khởi hành</div>
            <div class="chart-description">Nhịp mở lịch khởi hành theo từng tháng.</div>
            <div class="chart-canvas-wrap small">
                <canvas id="lichKhoiHanhChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const tours = <?php echo json_encode($tours ?? [], JSON_UNESCAPED_UNICODE); ?>;
const tourNames = tours.map(t => t.ten_tour);
const loiNhuan = tours.map(t => Number(t.loi_nhuan || 0));

const rawMonthLabels = <?php echo json_encode(array_keys($doanhThuTheoThang ?? []), JSON_UNESCAPED_UNICODE); ?>;
const rawMonthRevenue = <?php echo json_encode(array_values($doanhThuTheoThang ?? []), JSON_UNESCAPED_UNICODE); ?>.map(x => Number(x) || 0);
const monthSeries = rawMonthLabels.map((label, index) => ({
    label,
    value: rawMonthRevenue[index] || 0
}));

const bookingStatusData = <?php echo json_encode($bookingStatusStats ?? [], JSON_UNESCAPED_UNICODE); ?>;
const bookingStatusLabels = Object.keys(bookingStatusData);
const bookingStatusCounts = Object.values(bookingStatusData).map(x => Number(x) || 0);

const bookingManageStats = <?php echo json_encode($bookingManageStats ?? [], JSON_UNESCAPED_UNICODE); ?>;
const bookingManageLabels = Object.keys(bookingManageStats);
const bookingManageCounts = Object.values(bookingManageStats).map(x => Number(x) || 0);

const lichKhoiHanhStats = <?php echo json_encode($lichKhoiHanhStats ?? [], JSON_UNESCAPED_UNICODE); ?>;
const lichKhoiHanhLabels = Object.keys(lichKhoiHanhStats);
const lichKhoiHanhCounts = Object.values(lichKhoiHanhStats).map(x => Number(x) || 0);

const top3Months = [...monthSeries].sort((a, b) => b.value - a.value).slice(0, 3);
const topMonthDescriptions = document.getElementById('topMonthDescriptions');

if (topMonthDescriptions) {
    if (!top3Months.length) {
        topMonthDescriptions.innerHTML = '<div class="insight-item">Chưa có dữ liệu doanh thu theo tháng.</div>';
    } else {
        topMonthDescriptions.innerHTML = top3Months.map((item, index) => `
            <div class="insight-item">
                <span class="insight-rank">${index + 1}</span>
                <span>Tháng <strong>${item.label}</strong>: ${item.value.toLocaleString('vi-VN')} VNĐ</span>
            </div>
        `).join('');
    }
}

Chart.defaults.color = getComputedStyle(document.body).getPropertyValue('--text-muted').trim() || '#9ba8be';
Chart.defaults.borderColor = 'rgba(212, 175, 55, 0.08)';
Chart.defaults.font.family = 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';

const adminPalette = ['#d4af37', '#34d399', '#60a5fa', '#f97316', '#f87171', '#a78bfa', '#22c55e', '#06b6d4', '#eab308', '#fb7185'];

function buildCurrency(value) {
    return Number(value || 0).toLocaleString('vi-VN') + ' VNĐ';
}

function baseTooltipCallbacks(prefix) {
    return {
        label(context) {
            const raw = context.parsed.y ?? context.parsed ?? 0;
            return `${prefix}: ${Number(raw).toLocaleString('vi-VN')}`;
        }
    };
}

new Chart(document.getElementById('barChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: rawMonthLabels,
        datasets: [{
            label: 'Doanh thu',
            data: rawMonthRevenue,
            backgroundColor: ['rgba(212,175,55,0.85)', 'rgba(212,175,55,0.78)', 'rgba(212,175,55,0.72)', 'rgba(212,175,55,0.66)', 'rgba(212,175,55,0.6)', 'rgba(212,175,55,0.56)', 'rgba(212,175,55,0.52)', 'rgba(212,175,55,0.48)', 'rgba(212,175,55,0.44)', 'rgba(212,175,55,0.42)', 'rgba(212,175,55,0.38)', 'rgba(212,175,55,0.34)'],
            borderRadius: 14,
            borderSkipped: false,
            maxBarThickness: 42
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 22, 31, 0.96)',
                borderColor: 'rgba(212,175,55,0.2)',
                borderWidth: 1,
                padding: 12,
                callbacks: {
                    label(context) {
                        return 'Doanh thu: ' + buildCurrency(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    callback(value) {
                        return Number(value).toLocaleString('vi-VN');
                    }
                }
            }
        }
    }
});

const topProfitTours = tours
    .map(t => ({ name: t.ten_tour, profit: Number(t.loi_nhuan || 0) }))
    .sort((a, b) => b.profit - a.profit)
    .slice(0, 6);

new Chart(document.getElementById('pieChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: topProfitTours.map(item => item.name),
        datasets: [{
            label: 'Lợi nhuận',
            data: topProfitTours.map(item => item.profit),
            backgroundColor: adminPalette.slice(0, topProfitTours.length),
            borderWidth: 0,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '64%',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 22, 31, 0.96)',
                borderColor: 'rgba(212,175,55,0.2)',
                borderWidth: 1,
                padding: 12,
                callbacks: {
                    label(context) {
                        return 'Lợi nhuận: ' + buildCurrency(context.parsed);
                    }
                }
            }
        }
    }
});

const pieLegend = document.getElementById('pieLegend');
if (pieLegend) {
    pieLegend.innerHTML = topProfitTours.map((item, i) => `
        <div class="legend-item">
            <span class="legend-color" style="background:${adminPalette[i]}"></span>
            <span>${item.name} • ${item.profit.toLocaleString('vi-VN')} VNĐ</span>
        </div>
    `).join('');
}

new Chart(document.getElementById('bookingStatusChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: bookingStatusLabels,
        datasets: [{
            label: 'Trạng thái Booking',
            data: bookingStatusCounts,
            backgroundColor: adminPalette.slice(0, bookingStatusLabels.length),
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    usePointStyle: true
                }
            }
        }
    }
});

new Chart(document.getElementById('bookingManageChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: bookingManageLabels,
        datasets: [{
            label: 'Số lượng booking',
            data: bookingManageCounts,
            backgroundColor: 'rgba(96, 165, 250, 0.82)',
            borderRadius: 12,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 22, 31, 0.96)',
                borderColor: 'rgba(212,175,55,0.2)',
                borderWidth: 1,
                padding: 12,
                callbacks: baseTooltipCallbacks('Booking')
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

new Chart(document.getElementById('lichKhoiHanhChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: lichKhoiHanhLabels,
        datasets: [{
            label: 'Số lịch khởi hành',
            data: lichKhoiHanhCounts,
            borderColor: '#34d399',
            backgroundColor: 'rgba(52, 211, 153, 0.15)',
            fill: true,
            tension: 0.36,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#34d399'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 22, 31, 0.96)',
                borderColor: 'rgba(212,175,55,0.2)',
                borderWidth: 1,
                padding: 12,
                callbacks: baseTooltipCallbacks('Lịch khởi hành')
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
</script>

<div class="dashboard-grid">
    <a href="index.php?act=admin/quanLyTour" class="feature-card">
        <span class="notification-badge">12</span>
        <div class="feature-icon">📍</div>
        <h5>Quản lý Tour</h5>
        <p>Quản lý danh sách tour, lịch trình và thông tin chi tiết các chuyến đi.</p>
    </a>

    <a href="index.php?act=admin/quanLyBooking" class="feature-card">
        <span class="notification-badge">8</span>
        <div class="feature-icon">📋</div>
        <h5>Quản lý Booking</h5>
        <p>Xem và quản lý đặt chỗ, xác nhận booking của khách hàng.</p>
    </a>

    <a href="index.php?act=lichKhoiHanh/index" class="feature-card">
        <span class="notification-badge">5</span>
        <div class="feature-icon">🗓️</div>
        <h5>Lịch Khởi Hành</h5>
        <p>Theo dõi và quản lý lịch khởi hành, phân công nhân sự cho tour.</p>
    </a>

    <a href="index.php?act=admin/nhanSu" class="feature-card">
        <div class="feature-icon">👥</div>
        <h5>Quản lý Nhân sự</h5>
        <p>Quản lý hướng dẫn viên, điều hành và toàn bộ nhân viên.</p>
    </a>

    <a href="index.php?act=admin/quanLyNguoiDung" class="feature-card">
        <div class="feature-icon">👤</div>
        <h5>Quản lý Người dùng</h5>
        <p>Quản lý tài khoản, phân quyền và cấp quyền truy cập.</p>
    </a>

    <a href="index.php?act=admin/nhaCungCap" class="feature-card">
        <div class="feature-icon">🏢</div>
        <h5>Quản lý Nhà cung cấp</h5>
        <p>Theo dõi báo giá, dịch vụ và duyệt yêu cầu từ nhà cung cấp.</p>
    </a>

    <a href="index.php?act=admin/baoCaoTaiChinh" class="feature-card">
        <div class="feature-icon">📊</div>
        <h5>Báo cáo Tài chính</h5>
        <p>Thống kê doanh thu, chi phí và báo cáo tài chính tổng quan.</p>
    </a>

    <a href="index.php?act=admin/danhGia" class="feature-card">
        <span class="notification-badge">3</span>
        <div class="feature-icon">⭐</div>
        <h5>Đánh giá & Phản hồi</h5>
        <p>Quản lý đánh giá và phản hồi từ khách hàng về dịch vụ.</p>
    </a>

    <a href="index.php?act=admin/quanLyLuongThuong" class="feature-card">
        <div class="feature-icon">💰</div>
        <h5>Lương thưởng</h5>
        <p>Xem và quản lý lương, thưởng, hoa hồng cho nhân sự.</p>
    </a>

    <a href="index.php?act=auth/logout" class="feature-card" style="background: linear-gradient(180deg, rgba(220, 53, 69, 0.18), rgba(220, 53, 69, 0.08)); border-color: rgba(220, 53, 69, 0.26);">
        <div class="feature-icon" style="background: rgba(220, 53, 69, 0.18); color: #ff8d97;">🚪</div>
        <h5>Đăng xuất</h5>
        <p>Thoát khỏi hệ thống quản trị một cách an toàn.</p>
    </a>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
