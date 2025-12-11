<?php
$pageTitle = 'Quản lý Yêu cầu Tour';
$currentPage = 'yeuCauTour';
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
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-left: 4px solid;
        border-radius: 2px;
        padding: 25px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
    }

    .stat-card.border-primary { border-left-color: #0d6efd; }
    .stat-card.border-warning { border-left-color: #ffc107; }
    .stat-card.border-success { border-left-color: #198754; }

    .stat-label {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--text-light);
    }

    .stat-value.warning { color: #ffc107; }
    .stat-value.success { color: #198754; }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .table-wrapper {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
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

    .request-note {
        max-width: 300px;
        white-space: pre-line;
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-warning {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .badge-success {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .badge-secondary {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
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

    .form-group .input,
    .form-group .select {
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

    .form-group .input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group .input:focus,
    .form-group .select:focus {
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
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>⭐ Quản lý Yêu cầu Tour từ Khách hàng</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">Xem và phản hồi các yêu cầu tour theo mong muốn</p>
        </div>
        <div>
            <a href="index.php?act=admin/dashboard" class="btn btn-secondary">
                ← Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        ✓ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        ⚠ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card border-primary">
        <div class="stat-label">Tổng yêu cầu</div>
        <div class="stat-value"><?php echo $tongYeuCau ?? 0; ?></div>
    </div>
    <div class="stat-card border-warning">
        <div class="stat-label">Chưa xử lý</div>
        <div class="stat-value warning"><?php echo $chuaXuLy ?? 0; ?></div>
    </div>
    <div class="stat-card border-success">
        <div class="stat-label">Đã xử lý</div>
        <div class="stat-value success"><?php echo ($tongYeuCau ?? 0) - ($chuaXuLy ?? 0); ?></div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="GET" action="">
        <input type="hidden" name="act" value="admin/quanLyYeuCauTour">
        <div class="filter-row">
            <div class="form-group">
                <label>🔍 Tìm kiếm</label>
                <input type="text" name="search" class="input" placeholder="Tên khách hàng, địa điểm..." value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>📊 Trạng thái</label>
                <select name="trang_thai" class="select">
                    <option value="">Tất cả</option>
                    <option value="DaGui" <?php echo (($filters['trang_thai'] ?? '') === 'DaGui') ? 'selected' : ''; ?>>Đã gửi</option>
                    <option value="ChuaGui" <?php echo (($filters['trang_thai'] ?? '') === 'ChuaGui') ? 'selected' : ''; ?>>Chưa gửi</option>
                </select>
            </div>
            <div class="form-group">
                <label style="opacity: 0;">Tìm</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    🔍 Tìm kiếm
                </button>
            </div>
            <div class="form-group">
                <label style="opacity: 0;">Làm mới</label>
                <a href="index.php?act=admin/quanLyYeuCauTour" class="btn btn-secondary" style="width: 100%;">
                    🔄 Làm mới
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Table -->
<div class="table-wrapper">
    <?php if (!empty($yeuCauList)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Khách hàng</th>
                    <th>Thông tin yêu cầu</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yeuCauList as $index => $yc): ?>
                    <?php
                        // Parse thông tin từ nội dung
                        $thongTin = [];
                        foreach (explode("\n", $yc['noi_dung'] ?? '') as $row) {
                            $kv = explode(": ", $row, 2);
                            if (count($kv) == 2) {
                                $thongTin[$kv[0]] = $kv[1];
                            }
                        }
                        $thoiGian = !empty($yc['created_at']) ? date('d/m/Y H:i', strtotime($yc['created_at'])) : 'N/A';
                    ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <div style="font-weight: 600; margin-bottom: 5px; color: var(--text-light);">
                                <?php echo htmlspecialchars($yc['nguoi_gui_ten'] ?? 'N/A'); ?>
                            </div>
                            <small style="color: var(--text-muted); font-size: 11px; display: block; line-height: 1.6;">
                                <?php echo htmlspecialchars($yc['nguoi_gui_email'] ?? ''); ?><br>
                                <?php echo htmlspecialchars($yc['nguoi_gui_phone'] ?? ''); ?>
                            </small>
                        </td>
                        <td>
                            <div class="request-note">
                                <strong>Địa điểm:</strong> <?php echo htmlspecialchars($thongTin['Địa điểm'] ?? 'N/A'); ?><br>
                                <strong>Thời gian:</strong> <?php echo htmlspecialchars($thongTin['Thời gian'] ?? 'N/A'); ?><br>
                                <strong>Số người:</strong> <?php echo htmlspecialchars($thongTin['Số người'] ?? 'N/A'); ?><br>
                                <?php if (!empty($thongTin['Yêu cầu đặc biệt'])): ?>
                                    <strong>Yêu cầu:</strong> <?php echo htmlspecialchars($thongTin['Yêu cầu đặc biệt']); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <small style="color: var(--text-muted);"><?php echo $thoiGian; ?></small>
                        </td>
                        <td>
                            <?php if ($yc['trang_thai'] === 'DaGui'): ?>
                                <span class="badge badge-warning">Chờ xử lý</span>
                            <?php elseif (strpos($yc['noi_dung'] ?? '', 'Đã xử lý') !== false): ?>
                                <span class="badge badge-success">Đã xử lý</span>
                            <?php else: ?>
                                <span class="badge badge-secondary"><?php echo htmlspecialchars($yc['trang_thai'] ?? 'N/A'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="index.php?act=admin/chiTietYeuCauTour&id=<?php echo $yc['id']; ?>" class="btn btn-secondary btn-sm">
                                👁️ Xem & Phản hồi
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>Chưa có yêu cầu tour nào.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
