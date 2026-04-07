<?php
$pageTitle = 'Báo Cáo Tài Chính';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>

<style>
    .page-header-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 40px;
        margin-bottom: 40px;
        backdrop-filter: blur(10px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        backdrop-filter: blur(10px);
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card .icon {
        width: 60px;
        height: 60px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 15px;
    }

    .stat-card.revenue .icon {
        background: rgba(212, 175, 55, 0.2);
        color: var(--accent-gold);
    }

    .stat-card.expense .icon {
        background: rgba(245, 87, 108, 0.2);
        color: #f5576c;
    }

    .stat-card.profit .icon {
        background: rgba(79, 172, 254, 0.2);
        color: #4facfe;
    }

    .stat-card h3 {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-card .value {
        color: var(--text-light);
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-card .value.positive {
        color: #10b981;
    }

    .stat-card .value.negative {
        color: #ef4444;
    }

    .stat-card .change {
        font-size: 12px;
        color: var(--text-muted);
    }

    .stat-card .change.positive {
        color: #10b981;
    }

    .stat-card .change.negative {
        color: #ef4444;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    .info-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        backdrop-filter: blur(10px);
    }

    .info-card h2 {
        color: var(--accent-gold);
        font-size: 18px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        font-weight: 600;
    }

    .quick-links {
        display: grid;
        gap: 15px;
    }

    .quick-link {
        display: flex;
        align-items: center;
        padding: 15px;
        background: rgba(45, 45, 45, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        text-decoration: none;
        color: var(--text-light);
        transition: all 0.3s;
    }

    .quick-link:hover {
        background: rgba(212, 175, 55, 0.1);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
        transform: translateX(5px);
    }

    .quick-link-icon {
        width: 40px;
        height: 40px;
        background: rgba(212, 175, 55, 0.2);
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 18px;
        color: var(--accent-gold);
    }

    .quick-link:hover .quick-link-icon {
        background: rgba(212, 175, 55, 0.3);
    }

    .quick-link-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 3px;
    }

    .quick-link-desc {
        font-size: 11px;
        color: var(--text-muted);
    }

    .top-tours {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .tour-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .tour-item:last-child {
        border-bottom: none;
    }

    .tour-name {
        color: var(--text-light);
        font-weight: 500;
        font-size: 13px;
    }

    .tour-revenue {
        color: var(--accent-gold);
        font-weight: 700;
        font-size: 14px;
    }

    .tour-rank {
        color: var(--accent-gold);
        font-weight: 700;
        margin-right: 10px;
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>📊 Báo Cáo Tài Chính</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">
                <a href="index.php?act=admin/dashboard" style="color: var(--accent-gold); text-decoration: none;">Dashboard</a> / 
                <span>Báo cáo tài chính</span>
            </p>
        </div>
        <div>
            <a href="index.php?act=admin/dashboard" class="btn btn-secondary">
                ← Quay lại Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card revenue">
        <div class="icon">💰</div>
        <h3>TỔNG THU THÁNG NÀY</h3>
        <div class="value"><?php echo number_format($tongThu ?? 0); ?>đ</div>
        <div class="change positive">
            ↑ Tháng <?php echo date('m/Y'); ?>
        </div>
    </div>
    
    <div class="stat-card expense">
        <div class="icon">💸</div>
        <h3>TỔNG CHI THÁNG NÀY</h3>
        <div class="value"><?php echo number_format($tongChi ?? 0); ?>đ</div>
        <div class="change">
            📅 Tháng <?php echo date('m/Y'); ?>
        </div>
    </div>
    
    <div class="stat-card profit">
        <div class="icon">📈</div>
        <h3>LỢI NHUẬN</h3>
        <div class="value <?php echo ($loiNhuan ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
            <?php echo number_format($loiNhuan ?? 0); ?>đ
        </div>
        <div class="change <?php echo ($loiNhuan ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
            <?php if(($loiNhuan ?? 0) >= 0): ?>
                ↑ Khả quan
            <?php else: ?>
                ↓ Cần cải thiện
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <div class="info-card">
        <h2>⭐ Top 5 Tour Doanh Thu Cao Nhất</h2>
        <ul class="top-tours">
            <?php if(empty($topTours)): ?>
                <li class="tour-item">
                    <span style="color: var(--text-muted);">Chưa có dữ liệu</span>
                </li>
            <?php else: ?>
                <?php foreach($topTours as $index => $item): ?>
                    <li class="tour-item">
                        <div>
                            <span class="tour-rank">#<?php echo $index + 1; ?></span>
                            <span class="tour-name">
                                <?php echo htmlspecialchars($item['tour']['ten_tour']); ?>
                            </span>
                        </div>
                        <span class="tour-revenue">
                            <?php echo number_format($item['doanh_thu']); ?>đ
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="info-card">
        <h2>🔗 Truy Cập Nhanh</h2>
        <div class="quick-links">
            <a href="index.php?act=admin/lichSuGiaoDich" class="quick-link">
                <div class="quick-link-icon">🕐</div>
                <div>
                    <div class="quick-link-title">Lịch sử giao dịch</div>
                    <div class="quick-link-desc">Xem chi tiết các giao dịch</div>
                </div>
            </a>
            
            <a href="index.php?act=admin/thuChiTour" class="quick-link">
                <div class="quick-link-icon">🗺️</div>
                <div>
                    <div class="quick-link-title">Thu chi từng tour</div>
                    <div class="quick-link-desc">Báo cáo theo tour</div>
                </div>
            </a>
            
            <a href="index.php?act=admin/congNo" class="quick-link">
                <div class="quick-link-icon">💳</div>
                <div>
                    <div class="quick-link-title">Công nợ</div>
                    <div class="quick-link-desc">Quản lý công nợ KH/NCC</div>
                </div>
            </a>
            
            <a href="index.php?act=admin/laiLoTour" class="quick-link">
                <div class="quick-link-icon">📊</div>
                <div>
                    <div class="quick-link-title">Lãi lỗ từng tour</div>
                    <div class="quick-link-desc">Phân tích lãi lỗ</div>
                </div>
            </a>
            
            <a href="index.php?act=admin/duToanTour" class="quick-link">
                <div class="quick-link-icon">🧮</div>
                <div>
                    <div class="quick-link-title">Dự toán tour</div>
                    <div class="quick-link-desc">Quản lý dự toán chi phí</div>
                </div>
            </a>
            
            <a href="index.php?act=admin/soSanhDuToan" class="quick-link">
                <div class="quick-link-icon">⚖️</div>
                <div>
                    <div class="quick-link-title">So sánh dự toán</div>
                    <div class="quick-link-desc">Dự toán vs Thực tế</div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
