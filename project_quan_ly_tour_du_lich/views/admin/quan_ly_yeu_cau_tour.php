<?php
/** @var array $yeuCauList */
$pageTitle = 'Quản lý Yêu cầu Tour';
$currentPage = 'yeuCauTour';
$metaRefreshSeconds = 20;
ob_start();
?>

<style>
    .page-header-section {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .page-header-glow {
        position: absolute;
        top: 0;
        left: -60%;
        width: 60%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: yeu-cau-header-glow-move 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }

    @keyframes yeu-cau-header-glow-move {
        0% { left: -60%; }
        100% { left: 100%; }
    }

    .page-header-inner {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-header-main {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .page-header-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        flex-shrink: 0;
    }

    .page-header-title h1 {
        margin: 0;
        color: #ffe082;
        font-size: 1.7rem;
        font-weight: 700;
        text-shadow: 0 2px 8px #2d2d2d;
    }

    .page-header-title p {
        color: #fffde7;
        font-size: 1rem;
        margin-top: 6px;
        text-shadow: 0 1px 4px #2d2d2d;
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
        padding: 22px;
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(212,175,55,0.15);
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
        font-size: 2.2rem;
        font-weight: 700;
        color: #ffd700;
    }

    .stat-value.warning { color: #ffc107; }
    .stat-value.success { color: #10b981; }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 8px;
        padding: 22px 24px;
        margin-bottom: 24px;
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
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text-light);
        padding: 10px 12px;
        font-size: 13px;
        border-radius: 4px;
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
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
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

    .realtime-note {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 8px;
    }

    .admin-live-toast {
        position: fixed;
        right: 18px;
        top: 86px;
        z-index: 1200;
        min-width: 260px;
        max-width: min(92vw, 380px);
        border: 1px solid rgba(16, 185, 129, 0.28);
        border-radius: 12px;
        background: rgba(8, 20, 16, 0.94);
        color: #d1fae5;
        padding: 12px 14px;
        box-shadow: 0 16px 40px rgba(2, 6, 23, 0.35);
        font-weight: 600;
        opacity: 0;
        transform: translateY(-8px);
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease;
    }

    .admin-live-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    @media (max-width: 700px) {
        .page-header-section {
            padding: 20px;
        }

        .page-header-title h1 {
            font-size: 1.4rem;
        }
    }

    body.page-yeuCauTour .content-area {
        padding: 34px 48px 56px;
        background:
            radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
            radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
    }

    body.page-yeuCauTour .page-header-section {
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

    body.page-yeuCauTour .page-header-section::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
        pointer-events: none;
    }

    body.page-yeuCauTour .page-header-glow {
        display: none;
    }

    body.page-yeuCauTour .page-header-inner {
        position: relative;
        z-index: 2;
        align-items: center;
    }

    body.page-yeuCauTour .page-header-main {
        align-items: center;
        gap: 18px;
    }

    body.page-yeuCauTour .page-header-avatar {
        width: 74px;
        height: 74px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.18);
        border: 1px solid rgba(255, 224, 130, 0.32);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 16px 34px rgba(0,0,0,0.24);
    }

    body.page-yeuCauTour .page-header-title h1 {
        font-size: 2rem;
        line-height: 1.18;
        letter-spacing: 0;
        text-shadow: none;
    }

    body.page-yeuCauTour .page-header-title p {
        max-width: 680px;
        color: rgba(255,255,255,0.86);
        text-shadow: none;
    }

    body.page-yeuCauTour .page-header-section .btn,
    body.page-yeuCauTour .filter-section .btn,
    body.page-yeuCauTour .table-wrapper .btn {
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-yeuCauTour .page-header-section .btn {
        min-height: 46px;
        padding-inline: 28px;
    }

    body.page-yeuCauTour .stats-grid {
        grid-template-columns: repeat(3, minmax(220px, 1fr));
        gap: 24px;
    }

    body.page-yeuCauTour .stat-card {
        min-height: 160px;
        padding: 28px 32px;
        background: linear-gradient(180deg, rgba(255,255,255,0.07), rgba(255,255,255,0.025));
        border-color: rgba(255, 255, 255, 0.1);
        border-left-width: 3px;
        box-shadow: 0 14px 32px rgba(0,0,0,0.18);
    }

    body.page-yeuCauTour .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 54px rgba(0,0,0,0.24);
    }

    body.page-yeuCauTour .stat-label {
        margin-bottom: 24px;
        letter-spacing: 0.06em;
        line-height: 1.45;
    }

    body.page-yeuCauTour .stat-value {
        font-size: 2.65rem;
        line-height: 1;
        letter-spacing: 0;
    }

    body.page-yeuCauTour .filter-section,
    body.page-yeuCauTour .table-wrapper {
        background: rgba(28, 30, 31, 0.78);
        border-color: rgba(212, 175, 55, 0.22);
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    body.page-yeuCauTour .filter-section {
        padding: 24px 32px 28px;
    }

    body.page-yeuCauTour .filter-row {
        grid-template-columns: minmax(260px, 1.25fr) minmax(220px, 0.9fr) minmax(180px, 0.7fr) minmax(180px, 0.7fr);
        gap: 18px;
    }

    body.page-yeuCauTour .form-group label {
        color: rgba(245,245,245,0.78);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-yeuCauTour .form-group .input,
    body.page-yeuCauTour .form-group .select {
        min-height: 52px;
        border-radius: 8px;
        border-color: rgba(255,255,255,0.14);
        background-color: rgba(255,255,255,0.08);
    }

    body.page-yeuCauTour .form-group .input:focus,
    body.page-yeuCauTour .form-group .select:focus {
        border-color: rgba(13, 202, 240, 0.58);
        box-shadow: 0 0 0 3px rgba(13, 202, 240, 0.12);
    }

    body.page-yeuCauTour .filter-section .btn {
        min-height: 52px;
    }

    body.page-yeuCauTour .table-wrapper {
        border-radius: 8px;
        overflow-x: auto;
    }

    body.page-yeuCauTour .realtime-note {
        padding: 16px 20px !important;
        margin: 0;
        background: rgba(255,255,255,0.025);
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    body.page-yeuCauTour .table {
        min-width: 980px;
    }

    body.page-yeuCauTour .table thead {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.14), rgba(13, 202, 240, 0.06));
    }

    body.page-yeuCauTour .table th {
        padding: 16px 20px;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    body.page-yeuCauTour .table td {
        padding: 18px 20px;
        vertical-align: middle;
    }

    body.page-yeuCauTour .table tbody tr {
        transition: background 0.2s ease;
    }

    body.page-yeuCauTour .table tbody tr:hover {
        background: rgba(255,255,255,0.065);
    }

    body.page-yeuCauTour .request-note {
        max-width: 420px;
        padding: 10px 12px;
        border-radius: 8px;
        background: rgba(255,255,255,0.035);
        border: 1px solid rgba(255,255,255,0.07);
    }

    body.page-yeuCauTour .badge {
        border-radius: 8px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
    }

    body.theme-light.page-yeuCauTour .stat-card,
    body.theme-light.page-yeuCauTour .filter-section,
    body.theme-light.page-yeuCauTour .table-wrapper {
        background: rgba(255,255,255,0.9) !important;
    }

    @media (max-width: 1200px) {
        body.page-yeuCauTour .stats-grid,
        body.page-yeuCauTour .filter-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 900px) {
        body.page-yeuCauTour .content-area {
            padding: 24px 18px 42px;
        }

        body.page-yeuCauTour .page-header-section {
            padding: 24px;
        }

        body.page-yeuCauTour .page-header-inner,
        body.page-yeuCauTour .page-header-main {
            align-items: flex-start;
        }

        body.page-yeuCauTour .page-header-avatar {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div class="page-header-glow"></div>
    <div class="page-header-inner">
        <div class="page-header-main">
            <div class="page-header-avatar">⭐</div>
            <div class="page-header-title">
                <h1>Quản lý Yêu cầu Tour từ Khách hàng</h1>
                <p>Xem và phản hồi các yêu cầu tour theo mong muốn</p>
            </div>
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
        <div class="stat-value" id="statTongYeuCau"><?php echo $tongYeuCau ?? 0; ?></div>
    </div>
    <div class="stat-card border-warning">
        <div class="stat-label">Chưa xử lý</div>
        <div class="stat-value warning" id="statChuaXuLy"><?php echo $chuaXuLy ?? 0; ?></div>
    </div>
    <div class="stat-card border-success">
        <div class="stat-label">Đã xử lý</div>
        <div class="stat-value success" id="statDaXuLy"><?php echo ($tongYeuCau ?? 0) - ($chuaXuLy ?? 0); ?></div>
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
    <div class="realtime-note px-3 pt-2">Realtime: snapshot mỗi 5 giây, fallback tự tải lại trang mỗi 20 giây. <span id="snapshotLastSync" style="opacity:.8">(chưa đồng bộ)</span></div>
    <table class="table" id="yeuCauTable" <?php if (empty($yeuCauList)): ?>style="display:none"<?php endif; ?>>
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
        <tbody id="yeuCauTableBody">
            <?php foreach ($yeuCauList as $index => $yc): ?>
                <?php
                    $thongTin = [];
                    foreach (explode("\n", $yc['noi_dung'] ?? '') as $row) {
                        $kv = explode(": ", $row, 2);
                        if (count($kv) == 2) {
                            $thongTin[$kv[0]] = $kv[1];
                        }
                    }
                    $thoiGian = !empty($yc['created_at']) ? date('d/m/Y H:i', strtotime($yc['created_at'])) : 'N/A';
                    $isTransferComplaint = (strpos((string)($yc['tieu_de'] ?? ''), 'Khieu nai chuyen khoan sai noi dung') === 0);
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
                            <?php if ($isTransferComplaint): ?>
                                <div><strong>Loại:</strong> Khieu nai chuyen khoan</div>
                                <div><strong>Booking:</strong> <?php echo htmlspecialchars($thongTin['Booking ID'] ?? 'N/A'); ?></div>
                                <div><strong>Payment:</strong> <?php echo htmlspecialchars($thongTin['Payment ID'] ?? 'N/A'); ?></div>
                                <div><strong>Tham chieu:</strong> <?php echo htmlspecialchars($thongTin['Ma giao dich/tham chieu'] ?? 'N/A'); ?></div>
                                <?php if (!empty($thongTin['Noi dung khiu nai'])): ?>
                                    <div><strong>Noi dung:</strong> <?php echo htmlspecialchars($thongTin['Noi dung khiu nai']); ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <strong>Địa điểm:</strong> <?php echo htmlspecialchars($thongTin['Địa điểm'] ?? 'N/A'); ?><br>
                                <strong>Thời gian:</strong> <?php echo htmlspecialchars($thongTin['Thời gian'] ?? 'N/A'); ?><br>
                                <strong>Số người:</strong> <?php echo htmlspecialchars($thongTin['Số người'] ?? 'N/A'); ?><br>
                                <?php if (!empty($thongTin['Yêu cầu đặc biệt'])): ?>
                                    <strong>Yêu cầu:</strong> <?php echo htmlspecialchars($thongTin['Yêu cầu đặc biệt']); ?>
                                <?php endif; ?>
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

    <div class="empty-state" id="yeuCauEmptyState" <?php if (!empty($yeuCauList)): ?>style="display:none"<?php endif; ?>>
        <div class="empty-state-icon">📭</div>
        <p>Chưa có yêu cầu tour nào.</p>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
(function () {
    function initTourRequestRealtime() {
        var statTongYeuCau = document.getElementById('statTongYeuCau');
        var statChuaXuLy = document.getElementById('statChuaXuLy');
        var statDaXuLy = document.getElementById('statDaXuLy');
        var yeuCauTableBody = document.getElementById('yeuCauTableBody');
        var yeuCauEmptyState = document.getElementById('yeuCauEmptyState');
        var snapshotLastSync = document.getElementById('snapshotLastSync');
        var tableWrapper = document.querySelector('.table-wrapper');
        var snapshotTimerId = null;
        var visibleIntervalMs = 5000;
        var hiddenIntervalMs = 20000;
        var firstSnapshotLoaded = false;
        var latestKnownIds = [];
        var adminToastHideTimerId = null;
        var consecutivePollErrors = 0;

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '\'': '&#39;', '"': '&quot;' })[char];
        });
    }

    function parseThongTin(noiDung) {
        var info = {};
        String(noiDung || '').split('\n').forEach(function (line) {
            var index = line.indexOf(': ');
            if (index <= 0) return;
            var key = line.slice(0, index).trim();
            var value = line.slice(index + 2).trim();
            info[key] = value;
        });
        return info;
    }

    function formatDateTime(value) {
        if (!value) return 'N/A';
        var date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return escapeHtml(value);
        return date.toLocaleString('vi-VN', { hour12: false });
    }

    function buildStatusCell(item) {
        var trangThai = String(item.trang_thai || '');
        if (trangThai === 'DaGui') {
            return '<span class="badge badge-warning">Chờ xử lý</span>';
        }
        if (String(item.noi_dung || '').indexOf('Đã xử lý') !== -1) {
            return '<span class="badge badge-success">Đã xử lý</span>';
        }
        return '<span class="badge badge-secondary">' + escapeHtml(trangThai || 'N/A') + '</span>';
    }

    function buildInfoCell(item) {
        var info = parseThongTin(item.noi_dung || '');
        var isTransferComplaint = String(item.tieu_de || '').indexOf('Khieu nai chuyen khoan sai noi dung') === 0;
        if (isTransferComplaint) {
            return '<div class="request-note">' +
                '<div><strong>Loại:</strong> Khieu nai chuyen khoan</div>' +
                '<div><strong>Booking:</strong> ' + escapeHtml(info['Booking ID'] || 'N/A') + '</div>' +
                '<div><strong>Payment:</strong> ' + escapeHtml(info['Payment ID'] || 'N/A') + '</div>' +
                '<div><strong>Tham chieu:</strong> ' + escapeHtml(info['Ma giao dich/tham chieu'] || 'N/A') + '</div>' +
                (info['Noi dung khiu nai'] ? '<div><strong>Noi dung:</strong> ' + escapeHtml(info['Noi dung khiu nai']) + '</div>' : '') +
            '</div>';
        }

        return '<div class="request-note">' +
            '<strong>Địa điểm:</strong> ' + escapeHtml(info['Địa điểm'] || 'N/A') + '<br>' +
            '<strong>Thời gian:</strong> ' + escapeHtml(info['Thời gian'] || 'N/A') + '<br>' +
            '<strong>Số người:</strong> ' + escapeHtml(info['Số người'] || 'N/A') + '<br>' +
            (info['Yêu cầu đặc biệt'] ? ('<strong>Yêu cầu:</strong> ' + escapeHtml(info['Yêu cầu đặc biệt'])) : '') +
        '</div>';
    }

    function renderTable(items) {
        if (!tableWrapper) return;

        if (!Array.isArray(items) || items.length === 0) {
            if (yeuCauTableBody) {
                var table = yeuCauTableBody.closest('table');
                if (table) table.style.display = 'none';
            }
            if (yeuCauEmptyState) {
                yeuCauEmptyState.style.display = 'block';
            }
            return;
        }

        if (!yeuCauTableBody) return;
        var table = yeuCauTableBody.closest('table');
        if (table) table.style.display = 'table';
        if (yeuCauEmptyState) yeuCauEmptyState.style.display = 'none';

        var html = '';
        items.forEach(function (item, index) {
            html += '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' +
                    '<div style="font-weight: 600; margin-bottom: 5px; color: var(--text-light);">' + escapeHtml(item.nguoi_gui_ten || 'N/A') + '</div>' +
                    '<small style="color: var(--text-muted); font-size: 11px; display: block; line-height: 1.6;">' +
                        escapeHtml(item.nguoi_gui_email || '') + '<br>' +
                        escapeHtml(item.nguoi_gui_phone || '') +
                    '</small>' +
                '</td>' +
                '<td>' + buildInfoCell(item) + '</td>' +
                '<td><small style="color: var(--text-muted);">' + escapeHtml(formatDateTime(item.created_at || '')) + '</small></td>' +
                '<td>' + buildStatusCell(item) + '</td>' +
                '<td><a href="index.php?act=admin/chiTietYeuCauTour&id=' + Number(item.id || 0) + '" class="btn btn-secondary btn-sm">👁️ Xem & Phản hồi</a></td>' +
            '</tr>';
        });

        yeuCauTableBody.innerHTML = html;
    }

    function showAdminToast(message) {
        var existing = document.getElementById('adminLiveToast');
        if (!existing) {
            existing = document.createElement('div');
            existing.id = 'adminLiveToast';
            existing.className = 'admin-live-toast';
            document.body.appendChild(existing);
        }

        existing.textContent = message;
        existing.classList.add('is-visible');
        if (adminToastHideTimerId) {
            window.clearTimeout(adminToastHideTimerId);
        }
        adminToastHideTimerId = window.setTimeout(function () {
            existing.classList.remove('is-visible');
        }, 3200);
    }

    function getNewItemsCount(items) {
        if (!Array.isArray(items)) return 0;
        var currentIds = items.map(function (item) {
            return Number(item.id || 0);
        }).filter(function (id) { return id > 0; });

        if (!firstSnapshotLoaded) {
            latestKnownIds = currentIds;
            return 0;
        }

        var knownSet = new Set(latestKnownIds);
        var newCount = currentIds.filter(function (id) {
            return !knownSet.has(id);
        }).length;
        latestKnownIds = currentIds;
        return newCount;
    }

    async function refreshYeuCauSnapshot() {
        try {
            var params = new URLSearchParams(window.location.search);
            params.set('act', 'admin/yeuCauTourSnapshot');
            params.set('_ts', Date.now().toString());

            var response = await fetch('index.php?' + params.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            });
            if (!response.ok) return;

            var data = await response.json();
            if (!data || data.success !== true) return;

            consecutivePollErrors = 0;

            if (statTongYeuCau) statTongYeuCau.textContent = String(data.tong_yeu_cau || 0);
            if (statChuaXuLy) statChuaXuLy.textContent = String(data.chua_xu_ly || 0);
            if (statDaXuLy) statDaXuLy.textContent = String(data.da_xu_ly || 0);
            renderTable(data.items || []);

            if (snapshotLastSync) {
                var now = new Date();
                snapshotLastSync.textContent = '(đồng bộ: ' + now.toLocaleTimeString('vi-VN', { hour12: false }) + ')';
            }

            var newItemsCount = getNewItemsCount(data.items || []);
            if (firstSnapshotLoaded && !document.hidden && newItemsCount > 0) {
                showAdminToast('Có ' + newItemsCount + ' yêu cầu tour mới vừa gửi.');
            }
            firstSnapshotLoaded = true;
        } catch (error) {
            consecutivePollErrors++;
            if (consecutivePollErrors >= 3) {
                restartSnapshotTimer();
                consecutivePollErrors = 0;
            }
        }
    }

        function restartSnapshotTimer() {
            if (snapshotTimerId) {
                window.clearTimeout(snapshotTimerId);
                snapshotTimerId = null;
            }

            var intervalMs = document.hidden ? hiddenIntervalMs : visibleIntervalMs;
            snapshotTimerId = window.setTimeout(function pollLoop() {
                refreshYeuCauSnapshot().finally(function () {
                    restartSnapshotTimer();
                });
            }, intervalMs);
        }

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                refreshYeuCauSnapshot();
            }
            restartSnapshotTimer();
        });

        window.addEventListener('focus', function () {
            refreshYeuCauSnapshot();
            restartSnapshotTimer();
        });

        // When Admin WS fires, immediately refresh without waiting for polling interval
        document.addEventListener('adminNotification', function(e) {
            var payload = e && e.detail;
            if (!payload || payload.success !== true) return;
            refreshYeuCauSnapshot();
        });

        refreshYeuCauSnapshot();
        restartSnapshotTimer();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTourRequestRealtime);
    } else {
        initTourRequestRealtime();
    }
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
