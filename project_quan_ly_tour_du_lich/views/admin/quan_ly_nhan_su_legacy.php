<?php
$pageTitle = 'Quản lý Nhân Sự';
$currentPage = 'nhanSu';
ob_start();
?>

<style>
    .page-header-nhansu {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 32px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        overflow: hidden;
        flex-wrap: wrap;
    }
    .page-header-nhansu .header-glow {
        position: absolute;
        top: 0; left: -60%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: header-glow-move 2.8s linear infinite;
        z-index: 1;
    }
    @keyframes header-glow-move {
        0% { left: -60%; }
        100% { left: 100%; }
    }
    .page-header-nhansu .header-avatar {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        z-index: 2;
        flex-shrink: 0;
    }
    .page-header-nhansu .header-text {
        z-index: 2;
        flex: 1;
    }
    .page-header-nhansu .header-title {
        margin: 0; color: #ffe082; font-size: 1.7rem; font-weight: 700;
        text-shadow: 0 2px 8px #2d2d2d;
    }
    .page-header-nhansu .header-desc {
        color: #fffde7; font-size: 1rem; margin-top: 6px;
        text-shadow: 0 1px 4px #2d2d2d;
    }
    .page-header-nhansu .header-actions {
        z-index: 2;
        display: flex; gap: 10px; flex-wrap: wrap;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-left: 4px solid;
        border-radius: 8px;
        padding: 22px 24px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.15);
    }

    .stat-card.border-primary { border-left-color: var(--accent-gold); }
    .stat-card.border-success { border-left-color: #10b981; }
    .stat-card.border-info { border-left-color: var(--accent-gold); }
    .stat-card.border-warning { border-left-color: var(--accent-gold); }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        background: rgba(212, 175, 55, 0.13);
        color: var(--accent-gold);
        transition: all 0.3s;
    }

    .stat-card:hover .stat-icon {
        background: var(--accent-gold);
        transform: scale(1.1) rotate(5deg);
    }

    .stat-icon.bg-primary { background: rgba(212, 175, 55, 0.13); }
    .stat-icon.bg-success { background: rgba(16, 185, 129, 0.13); color: #10b981; }
    .stat-icon.bg-info { background: rgba(212, 175, 55, 0.13); }
    .stat-icon.bg-warning { background: rgba(212, 175, 55, 0.13); }

    .stat-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        color: #ffd700;
    }

    .stat-value.success { color: #10b981; }
    .stat-value.info { color: #ffd700; }
    .stat-value.warning { color: #ffd700; }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        padding: 22px 28px;
        margin-bottom: 28px;
        backdrop-filter: blur(10px);
    }

    .role-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 30px;
    }

    .role-tab {
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 12px 20px;
        transition: all 0.3s;
        background: rgba(45, 45, 45, 0.3);
        color: var(--text-light);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
    }

    .role-tab:hover {
        background: rgba(45, 45, 45, 0.5);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
    }

    .role-tab.active {
        background: rgba(212, 175, 55, 0.2);
        border-color: var(--accent-gold);
        color: var(--accent-gold);
    }

    .table-wrapper {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 8px;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: rgba(212, 175, 55, 0.1);
    }

    .table th {
        padding: 15px;
        text-align: left;
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--accent-gold);
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        font-size: 13px;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .employee-avatar {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        color: var(--primary-dark);
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        box-shadow: 0 2px 8px rgba(212,175,55,0.25);
    }

    .role-badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .role-badge.bg-success {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .role-badge.bg-info {
        background: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }

    .role-badge.bg-warning {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .role-badge.bg-secondary {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }

    .hdv-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 999px;
        background: rgba(77, 163, 255, 0.16);
        border: 1px solid rgba(77, 163, 255, 0.35);
        color: #86c2ff;
        font-size: 11px;
        font-weight: 600;
        margin-left: 8px;
    }

    .btn-action {
        min-width: 34px;
        justify-content: center;
        font-size: 12px;
    }

    .btn-action.hdv-view {
        background: rgba(77, 163, 255, 0.2);
        color: #86c2ff;
        border-color: rgba(77, 163, 255, 0.35);
    }

    .btn-action.hdv-view:hover {
        background: rgba(77, 163, 255, 0.3);
        color: #b5dbff;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .input,
    .form-group .select,
    .form-group textarea {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 12px 10px;
        font-size: 13px;
        border-radius: 2px;
        transition: all 0.3s;
        width: 100%;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-group .input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group .input:focus,
    .form-group .select:focus,
    .form-group textarea:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-gold);
    }

    .form-group .select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23d4af37' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border-color: rgba(25, 135, 84, 0.3);
        color: #198754;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    .input-group {
        display: flex;
        gap: 10px;
    }

    .input-group .input {
        flex: 1;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(5px);
    }

    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background: rgba(45, 45, 45, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow-y: auto;
        backdrop-filter: blur(10px);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(212, 175, 55, 0.2);
    }

    .modal-title {
        margin: 0;
        color: var(--accent-gold);
        font-size: 18px;
        font-weight: 600;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-close {
        background: none;
        border: none;
        color: var(--text-light);
        font-size: 24px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.3s;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .btn-close-white {
        color: white;
    }

    body.page-nhanSu .content-area {
        padding: 34px 48px 56px;
        background:
            radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
            radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
    }

    body.page-nhanSu .page-header-nhansu {
        min-height: 154px;
        padding: 28px 34px;
        background:
            linear-gradient(100deg, rgba(28, 31, 33, 0.96) 0%, rgba(30, 42, 45, 0.94) 52%, rgba(119, 102, 45, 0.84) 100%),
            url("<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg");
        background-size: cover;
        background-position: center;
        border: 1px solid rgba(255, 255, 255, 0.09);
        box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
    }

    body.page-nhanSu .page-header-nhansu::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
        pointer-events: none;
    }

    body.page-nhanSu .page-header-nhansu .header-glow {
        display: none;
    }

    body.page-nhanSu .page-header-nhansu .header-avatar {
        width: 74px;
        height: 74px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.18);
        border: 1px solid rgba(255, 224, 130, 0.32);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 16px 34px rgba(0,0,0,0.24);
    }

    body.page-nhanSu .page-header-nhansu .header-title {
        font-size: 2rem;
        line-height: 1.18;
        letter-spacing: 0;
        text-shadow: none;
    }

    body.page-nhanSu .page-header-nhansu .header-desc {
        color: rgba(255,255,255,0.86);
        text-shadow: none;
    }

    body.page-nhanSu .page-header-nhansu .btn,
    body.page-nhanSu .filter-section .btn,
    body.page-nhanSu .role-tab,
    body.page-nhanSu .table-wrapper .btn {
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-nhanSu .page-header-nhansu .btn {
        min-height: 46px;
        padding-inline: 22px;
    }

    body.page-nhanSu .stats-grid {
        grid-template-columns: repeat(4, minmax(190px, 1fr));
        gap: 24px;
    }

    body.page-nhanSu .stat-card {
        min-height: 158px;
        padding: 28px 32px;
        background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.025));
        border-color: rgba(255, 255, 255, 0.1);
        border-left-width: 3px;
        box-shadow: 0 14px 32px rgba(0,0,0,0.18);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }

    body.page-nhanSu .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 54px rgba(0,0,0,0.24);
    }

    body.page-nhanSu .stat-value {
        font-size: 2.65rem;
        line-height: 1;
    }

    body.page-nhanSu .filter-section,
    body.page-nhanSu .table-wrapper {
        background: rgba(28, 30, 31, 0.78);
        border-color: rgba(212, 175, 55, 0.22);
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    body.page-nhanSu .filter-section {
        padding: 24px 32px 28px;
    }

    body.page-nhanSu .form-group label {
        color: rgba(245,245,245,0.78);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-nhanSu .form-group .input,
    body.page-nhanSu .form-group .select,
    body.page-nhanSu .form-group textarea {
        min-height: 52px;
        border-radius: 8px;
        border-color: rgba(255,255,255,0.14);
        background-color: rgba(255,255,255,0.08);
    }

    body.page-nhanSu .form-group .input:focus,
    body.page-nhanSu .form-group .select:focus,
    body.page-nhanSu .form-group textarea:focus {
        border-color: rgba(13, 202, 240, 0.58);
        box-shadow: 0 0 0 3px rgba(13, 202, 240, 0.12);
    }

    body.page-nhanSu .role-tabs {
        gap: 12px;
    }

    body.page-nhanSu .role-tab {
        min-height: 56px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-width: 1px;
        background: rgba(255,255,255,0.045);
        box-shadow: 0 10px 24px rgba(0,0,0,0.12);
    }

    body.page-nhanSu .role-tab.active,
    body.page-nhanSu .role-tab:hover {
        background: rgba(212, 175, 55, 0.14);
        border-color: rgba(212, 175, 55, 0.6);
        box-shadow: 0 16px 34px rgba(0,0,0,0.18);
    }

    body.page-nhanSu .table-wrapper {
        border-radius: 8px;
        overflow-x: auto;
    }

    body.page-nhanSu .table {
        min-width: 1100px;
    }

    body.page-nhanSu .table thead {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.14), rgba(13, 202, 240, 0.06));
    }

    body.page-nhanSu .table th {
        padding: 16px 20px;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    body.page-nhanSu .table td {
        padding: 18px 20px;
        vertical-align: middle;
    }

    body.page-nhanSu .role-badge,
    body.page-nhanSu .hdv-chip {
        border-radius: 8px;
    }

    @media (max-width: 1300px) {
        body.page-nhanSu .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }
    }

    @media (max-width: 900px) {
        body.page-nhanSu .content-area {
            padding: 24px 18px 42px;
        }

        body.page-nhanSu .page-header-nhansu {
            padding: 24px;
            align-items: flex-start;
        }

        body.page-nhanSu .page-header-nhansu .header-avatar {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-nhansu">
    <div class="header-glow"></div>
    <div class="header-avatar">👥</div>
    <div class="header-text">
        <h2 class="header-title">Quản Lý Nhân Sự</h2>
        <div class="header-desc">Quản lý thông tin nhân viên và hồ sơ cá nhân</div>
    </div>
    <div class="header-actions">
        <a href="index.php?act=admin/hdv_advanced" class="btn btn-secondary">
            🎯 Quản lý HDV
        </a>
        <button id="btnAdd" class="btn btn-primary" onclick="document.getElementById('nhanSuModal').classList.add('show')">
            ➕ Thêm nhân sự
        </button>
    </div>
</div>

<!-- Alerts -->
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; ?>
    <div class="alert alert-<?php echo $f['type'] === 'success' ? 'success' : 'danger'; ?>">
        <?php echo $f['type'] === 'success' ? '✓' : '⚠'; ?> <?php echo htmlspecialchars($f['message']); ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- Statistics -->
<?php 
$total = count($nhan_su_list ?? []);
$hdvCount = isset($data_by_role['HDV']) ? count($data_by_role['HDV']) : 0;
$dieuHanhCount = isset($data_by_role['DieuHanh']) ? count($data_by_role['DieuHanh']) : 0;
$nhaCungCapCount = isset($data_by_role['NhaCungCap']) ? count($data_by_role['NhaCungCap']) : 0;
?>
<div class="stats-grid">
    <div class="stat-card border-primary">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="stat-label">Tổng nhân sự</div>
                <div class="stat-value"><?php echo $total; ?></div>
            </div>
            <div class="stat-icon bg-primary">👥</div>
        </div>
    </div>
    <div class="stat-card border-success">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="stat-label">Hướng dẫn viên</div>
                <div class="stat-value success"><?php echo $hdvCount; ?></div>
            </div>
            <div class="stat-icon bg-success">🎯</div>
        </div>
    </div>
    <div class="stat-card border-info">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="stat-label">Điều hành</div>
                <div class="stat-value info"><?php echo $dieuHanhCount; ?></div>
            </div>
            <div class="stat-icon bg-info">💼</div>
        </div>
    </div>
    <div class="stat-card border-warning">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="stat-label">Nhà cung cấp</div>
                <div class="stat-value warning"><?php echo $nhaCungCapCount; ?></div>
            </div>
            <div class="stat-icon bg-warning">🏢</div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="get" action="index.php">
        <input type="hidden" name="act" value="admin/nhanSu">
        <div style="display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label>🔍 Tìm kiếm</label>
                <div class="input-group">
                    <input class="input" type="search" placeholder="Tìm kiếm tên, email, số điện thoại..." 
                           name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    <button class="btn btn-primary" type="submit">🔍 Tìm kiếm</button>
                </div>
            </div>
            <div style="font-size: 11px; color: var(--text-muted); text-align: right;">
                <div>Lọc theo vai trò bên dưới</div>
            </div>
        </div>
    </form>
</div>

<!-- Role Tabs -->
<?php if (!empty($roles)): ?>
<div class="role-tabs">
    <button class="role-tab <?php echo ($active_role === null) ? 'active' : ''; ?>" 
            onclick="window.location.href='index.php?act=admin/nhanSu'">
        📋 Tất cả 
        <span class="badge badge-secondary" style="margin-left: 8px;">
            <?php echo count($nhan_su_list ?? []); ?>
        </span>
    </button>
    <?php 
    $roleIcons = [
        'HDV' => '🎯',
        'DieuHanh' => '💼',
        'NhaCungCap' => '🏢',
        'Khac' => '⚙️'
    ];
    foreach($roles as $r): 
        $count = isset($data_by_role[$r]) ? count($data_by_role[$r]) : 0; 
        $icon = $roleIcons[$r] ?? '👤';
    ?>
        <button class="role-tab <?php echo ($active_role === $r) ? 'active' : ''; ?>" 
                onclick="window.location.href='index.php?act=admin/nhanSu&role=<?php echo urlencode($r); ?>'">
            <?php echo $icon; ?> <?php echo htmlspecialchars($r); ?> 
            <span class="badge badge-secondary" style="margin-left: 8px;">
                <?php echo $count; ?>
            </span>
        </button>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Employee Table -->
<div class="table-wrapper">
    <?php if (!empty($nhan_su_list)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">Avatar</th>
                    <th>Họ tên</th>
                    <th>Vai trò</th>
                    <th>Liên hệ</th>
                    <th>Chứng chỉ</th>
                    <th>Ngôn ngữ</th>
                    <th>Kinh nghiệm</th>
                    <th style="width: 280px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nhan_su_list as $nhan_su): ?>
                    <tr>
                        <td>
                            <div class="employee-avatar">
                                <?php 
                                $name = $nhan_su['ho_ten'] ?? 'N';
                                echo strtoupper(mb_substr($name, 0, 1));
                                ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; margin-bottom: 5px; color: var(--text-light);">
                                <?php echo htmlspecialchars($nhan_su['ho_ten'] ?? ''); ?>
                                <?php if (($nhan_su['vai_tro'] ?? '') === 'HDV'): ?>
                                    <span class="hdv-chip"><i class="bi bi-compass"></i>Ho so HDV</span>
                                <?php endif; ?>
                            </div>
                            <small style="color: var(--text-muted); font-size: 11px;">
                                ID: #<?php echo htmlspecialchars($nhan_su['nhan_su_id']); ?>
                            </small>
                        </td>
                        <td>
                            <span class="role-badge <?php 
                                echo match($nhan_su['vai_tro'] ?? '') {
                                    'HDV' => 'bg-success',
                                    'DieuHanh' => 'bg-info',
                                    'NhaCungCap' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                            ?>">
                                <?php echo htmlspecialchars($nhan_su['vai_tro'] ?? ''); ?>
                            </span>
                        </td>
                        <td>
                            <small style="color: var(--text-muted); font-size: 12px; display: block; line-height: 1.6;">
                                <div>📞 <?php echo htmlspecialchars($nhan_su['so_dien_thoai'] ?? 'N/A'); ?></div>
                                <div>✉️ <?php echo htmlspecialchars($nhan_su['email'] ?? 'N/A'); ?></div>
                            </small>
                        </td>
                        <td><small style="color: var(--text-muted);"><?php echo htmlspecialchars($nhan_su['chung_chi'] ?? 'N/A'); ?></small></td>
                        <td><small style="color: var(--text-muted);"><?php echo htmlspecialchars($nhan_su['ngon_ngu'] ?? 'N/A'); ?></small></td>
                        <td><small style="color: var(--text-muted);"><?php echo htmlspecialchars($nhan_su['kinh_nghiem'] ?? 'N/A'); ?></small></td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <a href="index.php?act=admin/nhanSu_chi_tiet&id=<?php echo $nhan_su['nhan_su_id']; ?>" 
                                   class="btn btn-secondary btn-sm btn-action" 
                                   style="background: rgba(13, 202, 240, 0.2); color: #0dcaf0; border-color: rgba(13, 202, 240, 0.3);"
                                   title="Xem sơ yếu lý lịch">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($nhan_su['vai_tro'] === 'HDV'): ?>
                                <a href="index.php?act=admin/hdv_detail&id=<?php echo $nhan_su['nhan_su_id']; ?>" 
                                   class="btn btn-secondary btn-sm btn-action hdv-view" 
                                   title="Quản lý HDV">
                                    <i class="bi bi-compass"></i>
                                </a>
                                <?php endif; ?>
                                <button class="btn btn-secondary btn-sm btn-action btn-edit" 
                                    style="background: rgba(13, 110, 253, 0.2); color: #0d6efd; border-color: rgba(13, 110, 253, 0.3);"
                                    data-id="<?php echo $nhan_su['nhan_su_id']; ?>"
                                    data-vai_tro="<?php echo htmlspecialchars($nhan_su['vai_tro'] ?? ''); ?>"
                                    data-chung_chi="<?php echo htmlspecialchars($nhan_su['chung_chi'] ?? ''); ?>"
                                    data-ngon_ngu="<?php echo htmlspecialchars($nhan_su['ngon_ngu'] ?? ''); ?>"
                                    data-kinh_nghiem="<?php echo htmlspecialchars($nhan_su['kinh_nghiem'] ?? ''); ?>"
                                    data-suc_khoe="<?php echo htmlspecialchars($nhan_su['suc_khoe'] ?? ''); ?>"
                                    data-user_info="<?php echo htmlspecialchars(($nhan_su['ho_ten'] ?? '') . ' (' . ($nhan_su['ten_dang_nhap'] ?? '') . ')'); ?>"
                                    title="Sửa">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="index.php?act=admin/nhanSu_delete" style="display:inline; margin:0;">
                                    <?php echo csrfField('admin_form'); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$nhan_su['nhan_su_id']; ?>">
                                    <button type="submit"
                                        class="btn btn-secondary btn-sm btn-action"
                                        style="background: rgba(255, 193, 7, 0.2); color: #ffc107; border-color: rgba(255, 193, 7, 0.3);"
                                        onclick="return confirm('Xóa nhân sự này? (Tài khoản sẽ được giữ)');"
                                        title="Xóa NV">
                                        <i class="bi bi-person-dash"></i>
                                    </button>
                                </form>
                                <form method="POST" action="index.php?act=admin/nhanSu_delete" style="display:inline; margin:0;">
                                    <?php echo csrfField('admin_form'); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$nhan_su['nhan_su_id']; ?>">
                                    <input type="hidden" name="delete_user" value="1">
                                    <button type="submit"
                                        class="btn btn-secondary btn-sm btn-action"
                                        style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);"
                                        onclick="return confirm('XÓA VĨ VIỄN: Nhân sự này và tài khoản liên kết sẽ bị xóa. Bạn chắc chắn?');"
                                        title="Xóa All">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <h4 style="margin-bottom: 15px; color: var(--text-light);">Chưa có nhân sự nào</h4>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Hãy thêm nhân sự đầu tiên để bắt đầu quản lý</p>
            <button class="btn btn-primary" onclick="document.getElementById('nhanSuModal').classList.add('show')">
                ➕ Thêm nhân sự ngay
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="nhanSuModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                ➕ Thêm / Sửa nhân sự
            </h5>
            <button type="button" class="btn-close btn-close-white" onclick="this.closest('.modal').classList.remove('show')">&times;</button>
        </div>
        <form id="nhanSuForm" method="post" action="index.php?act=admin/nhanSu_create">
            <div class="modal-body">
                <input type="hidden" name="nhan_su_id" id="nhan_su_id" value="">
                
                <div class="form-row">
                    <div class="form-group" id="userSelectWrapper" style="grid-column: 1 / -1;">
                        <label>👤 Chọn người dùng <span style="color: #dc3545;">*</span></label>
                        <select name="nguoi_dung_id" id="nguoi_dung_id" class="select" required>
                            <option value="">-- Chọn người dùng --</option>
                        </select>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                            ℹ️ Chỉ hiển thị tài khoản chưa có hồ sơ nhân sự
                        </small>
                    </div>
                    
                    <div class="form-group" id="userInfoDisplay" style="display:none; grid-column: 1 / -1;">
                        <label>👤 Người dùng</label>
                        <input type="text" id="userInfoText" class="input" readonly>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 5px; display: block;">
                            🔒 Không thể thay đổi người dùng khi sửa nhân sự
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>💼 Vai trò</label>
                        <select name="vai_tro" id="vai_tro" class="select">
                            <option value="HDV">HDV</option>
                            <option value="DieuHanh">Điều hành</option>
                            <option value="NhaCungCap">Nhà cung cấp</option>
                            <option value="Khac">Khác</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>❤️ Sức khỏe</label>
                        <input name="suc_khoe" id="suc_khoe" class="input" placeholder="VD: Tốt, khỏe mạnh">
                    </div>
                    
                    <div class="form-group">
                        <label>🏆 Chứng chỉ</label>
                        <textarea name="chung_chi" id="chung_chi" class="textarea" rows="2" 
                                  placeholder="VD: Chứng chỉ HDV, Bằng lái xe B2..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>🌐 Ngôn ngữ</label>
                        <input name="ngon_ngu" id="ngon_ngu" class="input" placeholder="VD: Tiếng Anh, Tiếng Nhật">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>⏰ Kinh nghiệm</label>
                        <textarea name="kinh_nghiem" id="kinh_nghiem" class="textarea" rows="3" 
                                  placeholder="VD: 5 năm kinh nghiệm làm HDV quốc tế..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('nhanSuModal').classList.remove('show')">
                    ✕ Đóng
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    ✓ Lưu thông tin
                </button>
            </div>
        </form>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    (function(){
        const modal = document.getElementById('nhanSuModal');
        const form = document.getElementById('nhanSuForm');
        const submitBtn = document.getElementById('submitBtn');

        // Load danh sách người dùng có sẵn
        function loadAvailableUsers() {
            const select = document.getElementById('nguoi_dung_id');
            fetch('index.php?act=admin/nhanSu_get_users')
                .then(r => r.json())
                .then(data => {
                    if (data.users && data.users.length > 0) {
                        const html = '<option value="">-- Chọn người dùng --</option>' + 
                            data.users.map(u => `<option value="${u.id}">${u.ho_ten} (${u.ten_dang_nhap})</option>`).join('');
                        select.innerHTML = html;
                    }
                })
                .catch(e => console.error('Lỗi tải người dùng:', e));
        }

        document.getElementById('btnAdd').addEventListener('click', function(){
            form.action = 'index.php?act=admin/nhanSu_create';
            form.nhan_su_id.value = '';
            form.reset();
            
            // Show user select, hide user info
            document.getElementById('userSelectWrapper').style.display = 'block';
            document.getElementById('userInfoDisplay').style.display = 'none';
            document.getElementById('nguoi_dung_id').required = true;
            
            loadAvailableUsers();
            modal.querySelector('.modal-title').innerHTML = '➕ Thêm nhân sự';
        });

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.dataset.id;
                form.action = 'index.php?act=admin/nhanSu_update';
                form.nhan_su_id.value = id;
                
                // Hide user select, show user info
                document.getElementById('userSelectWrapper').style.display = 'none';
                document.getElementById('userInfoDisplay').style.display = 'block';
                document.getElementById('nguoi_dung_id').required = false;
                document.getElementById('userInfoText').value = this.dataset.user_info || '';
                
                document.getElementById('vai_tro').value = this.dataset.vai_tro || '';
                document.getElementById('chung_chi').value = this.dataset.chung_chi || '';
                document.getElementById('ngon_ngu').value = this.dataset.ngon_ngu || '';
                document.getElementById('kinh_nghiem').value = this.dataset.kinh_nghiem || '';
                document.getElementById('suc_khoe').value = this.dataset.suc_khoe || '';
                modal.querySelector('.modal-title').innerHTML = '✏️ Sửa nhân sự';
                modal.classList.add('show');
            });
        });

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    })();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
