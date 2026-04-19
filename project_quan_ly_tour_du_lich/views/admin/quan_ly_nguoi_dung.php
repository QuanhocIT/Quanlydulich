<?php
$pageTitle = 'Quản lý Người dùng';
$currentPage = 'nguoiDung';
ob_start();
?>

<style>
    .page-header-section {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 32px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        display: flex;
        align-items: center;
        gap: 22px;
        overflow: hidden;
    }
    .page-header-glow {
        position: absolute;
        top: 0; left: -60%;
        width: 60%; height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: phglow 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }
    @keyframes phglow {
        0% { left: -60%; }
        100% { left: 100%; }
    }
    .page-header-avatar {
        width: 64px; height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        z-index: 2;
        flex-shrink: 0;
    }
    .page-header-text {
        z-index: 2;
    }
    .page-header-title {
        margin: 0; color: #ffe082; font-size: 1.7rem; font-weight: 700;
        text-shadow: 0 2px 8px #2d2d2d;
    }
    .page-header-desc {
        color: #fffde7; font-size: 1rem; margin-top: 6px;
        text-shadow: 0 1px 4px #2d2d2d;
    }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        padding: 22px 28px;
        margin-bottom: 28px;
        backdrop-filter: blur(10px);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-left: 4px solid var(--accent-gold);
        border-radius: 8px;
        padding: 18px 20px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.15);
    }

    .stat-card .label {
        color: var(--text-muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .stat-card .value {
        color: #ffd700;
        font-size: 2.2rem;
        font-weight: 700;
        line-height: 1;
    }

    .stat-card .stat-icon-box {
        width: 52px; height: 52px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem;
        background: rgba(212,175,55,0.13);
        color: var(--accent-gold);
        transition: all 0.3s;
        flex-shrink: 0;
    }

    .stat-card:hover .stat-icon-box {
        background: var(--accent-gold);
        color: var(--primary-dark);
        transform: scale(1.1) rotate(5deg);
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

    .badge-role {
        padding: 6px 12px;
        border-radius: 2px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .badge-Admin {
        background: rgba(255, 123, 123, 0.2);
        color: #ff7b7b;
        border: 1px solid rgba(255, 123, 123, 0.3);
    }

    .badge-HDV {
        background: rgba(102, 185, 255, 0.2);
        color: #66b9ff;
        border: 1px solid rgba(102, 185, 255, 0.3);
    }

    .badge-KhachHang {
        background: rgba(99, 230, 190, 0.2);
        color: #63e6be;
        border: 1px solid rgba(99, 230, 190, 0.3);
    }

    .badge-NhaCungCap {
        background: rgba(255, 216, 115, 0.2);
        color: #ffd873;
        border: 1px solid rgba(255, 216, 115, 0.3);
    }

    .badge-status {
        padding: 6px 12px;
        border-radius: 2px;
        font-weight: 600;
        font-size: 11px;
        letter-spacing: 0.5px;
        display: inline-block;
    }

    .badge-status-active {
        color: #63e6be;
        border: 1px solid rgba(99, 230, 190, 0.3);
        background: rgba(99, 230, 190, 0.2);
    }

    .badge-status-locked {
        color: #ff8787;
        border: 1px solid rgba(255, 135, 135, 0.3);
        background: rgba(255, 135, 135, 0.2);
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .select {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text-light);
        padding: 11px 10px;
        font-size: 13px;
        border-radius: 4px;
        transition: all 0.3s;
        width: 100%;
        font-family: inherit;
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23d4af37' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 30px;
    }

    .form-group .input {
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text-light);
        padding: 11px 10px;
        font-size: 13px;
        border-radius: 4px;
        transition: all 0.3s;
        width: 100%;
        font-family: inherit;
    }

    .form-group .input::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }

    .form-group .input:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
    }

    .form-group .select:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-info {
        background: rgba(13, 202, 240, 0.1);
        border-color: rgba(13, 202, 240, 0.3);
        color: #0dcaf0;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.15);
        border-color: rgba(25, 135, 84, 0.35);
        color: #8ce99a;
    }

    .alert-error {
        background: rgba(220, 53, 69, 0.15);
        border-color: rgba(220, 53, 69, 0.35);
        color: #ffa8a8;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .action-group {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn-sm {
        padding: 8px 10px;
        font-size: 12px;
        line-height: 1;
    }

    .text-end {
        text-align: right;
    }

    body.page-nguoiDung .content-area {
        padding: 34px 48px 56px;
        background:
            radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
            radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
    }

    body.page-nguoiDung .page-header-section {
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

    body.page-nguoiDung .page-header-section::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
        pointer-events: none;
    }

    body.page-nguoiDung .page-header-glow {
        display: none;
    }

    body.page-nguoiDung .page-header-avatar,
    body.page-nguoiDung .page-header-text {
        position: relative;
        z-index: 2;
    }

    body.page-nguoiDung .page-header-avatar {
        width: 74px;
        height: 74px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.18);
        border: 1px solid rgba(255, 224, 130, 0.32);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 16px 34px rgba(0,0,0,0.24);
    }

    body.page-nguoiDung .page-header-title {
        font-size: 2rem;
        line-height: 1.18;
        letter-spacing: 0;
        text-shadow: none;
    }

    body.page-nguoiDung .page-header-desc {
        color: rgba(255,255,255,0.86);
        text-shadow: none;
    }

    body.page-nguoiDung .stats-grid {
        grid-template-columns: repeat(5, minmax(170px, 1fr));
        gap: 16px;
    }

    body.page-nguoiDung .stat-card {
        min-height: 126px;
        padding: 22px;
        background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.025));
        border-color: rgba(255, 255, 255, 0.1);
        border-left-width: 3px;
        box-shadow: 0 14px 32px rgba(0,0,0,0.18);
    }

    body.page-nguoiDung .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 54px rgba(0,0,0,0.24);
    }

    body.page-nguoiDung .filter-section,
    body.page-nguoiDung .table-wrapper {
        background: rgba(28, 30, 31, 0.78);
        border-color: rgba(212, 175, 55, 0.22);
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    body.page-nguoiDung .filter-section {
        padding: 24px 32px 28px;
    }

    body.page-nguoiDung .filter-row {
        grid-template-columns: minmax(220px, 1fr) minmax(190px, .8fr) minmax(190px, .8fr) minmax(180px, .75fr) minmax(160px, .65fr);
        gap: 18px;
    }

    body.page-nguoiDung .form-group label {
        color: rgba(245,245,245,0.78);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-nguoiDung .form-group .input,
    body.page-nguoiDung .form-group .select {
        min-height: 52px;
        border-radius: 8px;
        border-color: rgba(255,255,255,0.14);
        background-color: rgba(255,255,255,0.08);
    }

    body.page-nguoiDung .filter-section .btn,
    body.page-nguoiDung .table-wrapper .btn {
        min-height: 52px;
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-nguoiDung .table-wrapper {
        border-radius: 8px;
        overflow: hidden;
    }

    body.page-nguoiDung .table-wrapper > div:last-child {
        overflow-x: auto;
    }

    body.page-nguoiDung .table {
        min-width: 1180px;
    }

    body.page-nguoiDung .table thead {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.14), rgba(13, 202, 240, 0.06));
    }

    body.page-nguoiDung .table th {
        padding: 16px 20px;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    body.page-nguoiDung .table td {
        padding: 18px 20px;
        vertical-align: middle;
    }

    body.page-nguoiDung .badge-role,
    body.page-nguoiDung .badge-status {
        border-radius: 8px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
    }

    @media (max-width: 1500px) {
        body.page-nguoiDung .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }

        body.page-nguoiDung .filter-row {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
    }

    @media (max-width: 900px) {
        body.page-nguoiDung .content-area {
            padding: 24px 18px 42px;
        }

        body.page-nguoiDung .page-header-section {
            padding: 24px;
            align-items: flex-start;
        }

        body.page-nguoiDung .page-header-avatar {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div class="page-header-glow"></div>
    <div class="page-header-avatar">👤</div>
    <div class="page-header-text">
        <h2 class="page-header-title">Quản lý Người dùng</h2>
        <div class="page-header-desc">Quản lý thông tin nhân viên, khách hàng và nhà cung cấp</div>
    </div>
</div>

<!-- Flash -->
<?php if (!empty($_SESSION['flash'])): ?>
    <?php $flashType = $_SESSION['flash']['type'] ?? 'info'; ?>
    <div class="alert alert-<?php echo htmlspecialchars($flashType); ?>">
        <?php echo htmlspecialchars($_SESSION['flash']['message'] ?? ''); ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div><div class="label"><i class="bi bi-people me-1"></i>Tổng người dùng</div><div class="value"><?php echo (int)($userStats['total'] ?? 0); ?></div></div>
        <div class="stat-icon-box">👥</div>
    </div>
    <div class="stat-card">
        <div><div class="label"><i class="bi bi-person-check me-1"></i>Đang hoạt động</div><div class="value"><?php echo (int)($userStats['active'] ?? 0); ?></div></div>
        <div class="stat-icon-box">✅</div>
    </div>
    <div class="stat-card" style="border-left-color:#ef4444">
        <div><div class="label"><i class="bi bi-person-lock me-1"></i>Bị khóa</div><div class="value" style="color:#ef4444"><?php echo (int)($userStats['locked'] ?? 0); ?></div></div>
        <div class="stat-icon-box" style="background:rgba(239,68,68,0.13);color:#ef4444">🔒</div>
    </div>
    <div class="stat-card">
        <div><div class="label"><i class="bi bi-shield-lock me-1"></i>Admin</div><div class="value"><?php echo (int)($userStats['roles']['Admin'] ?? 0); ?></div></div>
        <div class="stat-icon-box">🛡️</div>
    </div>
    <div class="stat-card" style="border-left-color:#10b981">
        <div><div class="label"><i class="bi bi-compass me-1"></i>HDV</div><div class="value" style="color:#10b981"><?php echo (int)($userStats['roles']['HDV'] ?? 0); ?></div></div>
        <div class="stat-icon-box" style="background:rgba(16,185,129,0.13);color:#10b981">🎯</div>
    </div>
    <div class="stat-card">
        <div><div class="label"><i class="bi bi-person-heart me-1"></i>Khách hàng</div><div class="value"><?php echo (int)($userStats['roles']['KhachHang'] ?? 0); ?></div></div>
        <div class="stat-icon-box">👤</div>
    </div>
    <div class="stat-card">
        <div><div class="label"><i class="bi bi-building me-1"></i>Nhà cung cấp</div><div class="value"><?php echo (int)($userStats['roles']['NhaCungCap'] ?? 0); ?></div></div>
        <div class="stat-icon-box">🏢</div>
    </div>
</div>

<!-- Search & Filter form -->
<div class="filter-section">
    <form method="get" action="">
        <input type="hidden" name="act" value="admin/quanLyNguoiDung">
        <div class="filter-row">
            <div class="form-group">
                <label><i class="bi bi-search me-1"></i>Từ khóa</label>
                <input type="text" name="search" class="input" placeholder="Tên, email, SĐT..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
            <div class="form-group">
                <label><i class="bi bi-person-lines-fill me-1"></i>Lọc theo vai trò</label>
                <select name="role" class="select">
                    <option value="">-- Tất cả --</option>
                    <option value="Admin" <?php echo (isset($role) && $role === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="HDV" <?php echo (isset($role) && $role === 'HDV') ? 'selected' : ''; ?>>HDV</option>
                    <option value="KhachHang" <?php echo (isset($role) && $role === 'KhachHang') ? 'selected' : ''; ?>>Khách hàng</option>
                    <option value="NhaCungCap" <?php echo (isset($role) && $role === 'NhaCungCap') ? 'selected' : ''; ?>>Nhà cung cấp</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="bi bi-toggle-on me-1"></i>Trạng thái</label>
                <select name="status" class="select">
                    <option value="">-- Tất cả --</option>
                    <option value="HoatDong" <?php echo (($status ?? '') === 'HoatDong') ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="BiKhoa" <?php echo (($status ?? '') === 'BiKhoa') ? 'selected' : ''; ?>>Bị khóa</option>
                </select>
            </div>
            <div class="form-group">
                <label style="opacity: 0;">Áp dụng</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="bi bi-funnel me-1"></i>Áp dụng bộ lọc
                </button>
            </div>
            <div class="form-group">
                <label style="opacity: 0;">Reset</label>
                <a href="index.php?act=admin/quanLyNguoiDung" class="btn btn-secondary" style="width: 100%;">
                    <i class="bi bi-arrow-clockwise me-1"></i>Đặt lại
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-wrapper">
    <div style="padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;">
        <h5 style="margin: 0; color: var(--text-light); font-size: 16px;">Danh sách Người dùng</h5>
        <small style="color: var(--text-muted); font-size: 12px;"><?php echo count($users ?? []); ?> kết quả</small>
    </div>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;"><i class="bi bi-hash me-1"></i>ID</th>
                    <th><i class="bi bi-person me-1"></i>Tên đăng nhập</th>
                    <th><i class="bi bi-person-badge me-1"></i>Họ tên</th>
                    <th><i class="bi bi-envelope me-1"></i>Email</th>
                    <th><i class="bi bi-telephone me-1"></i>Số điện thoại</th>
                    <th style="width: 150px;"><i class="bi bi-person-lines-fill me-1"></i>Vai trò</th>
                    <th style="width: 120px;"><i class="bi bi-toggle-on me-1"></i>Trạng thái</th>
                    <th style="width: 150px;"><i class="bi bi-calendar me-1"></i>Ngày tạo</th>
                    <th class="text-end" style="width: 180px;"><i class="bi bi-gear me-1"></i>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['id']); ?></td>
                            <td><?php echo htmlspecialchars($u['ten_dang_nhap'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($u['ho_ten'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($u['so_dien_thoai'] ?? '-'); ?></td>
                            <td>
                                <?php $v = $u['vai_tro'] ?? 'KhachHang'; ?>
                                <span class="badge-role badge-<?php echo htmlspecialchars($v); ?>">
                                    <?php echo htmlspecialchars($v); ?>
                                </span>
                            </td>
                            <td>
                                <?php $isLocked = (($u['trang_thai'] ?? 'HoatDong') === 'BiKhoa'); ?>
                                <span class="badge-status <?php echo $isLocked ? 'badge-status-locked' : 'badge-status-active'; ?>">
                                    <?php echo $isLocked ? 'Bị khóa' : 'Hoạt động'; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($u['ngay_tao'] ?? ''); ?></td>
                            <td class="text-end">
                                <div class="action-group">
                                    <?php if ((int)($u['id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0)): ?>
                                        <span class="badge-status badge-status-active">Tài khoản hiện tại</span>
                                    <?php else: ?>
                                        <form method="post" action="index.php?act=admin/capNhatTrangThaiNguoiDung" onsubmit="return confirm('Bạn có chắc muốn cập nhật trạng thái tài khoản này?');">
                                            <input type="hidden" name="user_id" value="<?php echo (int)($u['id'] ?? 0); ?>">
                                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role ?? ''); ?>">
                                            <input type="hidden" name="status_filter" value="<?php echo htmlspecialchars($status ?? ''); ?>">
                                            <input type="hidden" name="status" value="<?php echo $isLocked ? 'HoatDong' : 'BiKhoa'; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $isLocked ? 'btn-primary' : 'btn-secondary'; ?>">
                                                <i class="bi <?php echo $isLocked ? 'bi-unlock' : 'bi-lock'; ?> me-1"></i>
                                                <?php echo $isLocked ? 'Mở khóa' : 'Khóa'; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            Không có dữ liệu phù hợp.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
