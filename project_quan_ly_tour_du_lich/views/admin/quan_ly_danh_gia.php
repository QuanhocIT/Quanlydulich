<?php
$pageTitle = 'Quản lý Đánh giá & Phản hồi';
$currentPage = 'danhGia';
ob_start();
?>

<style>
    .review-page-wrap {
        max-width: 1240px;
        margin: 0 auto;
        padding: 18px;
    }

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
        transform: translateX(4px);
        border-color: var(--accent-gold);
    }

    .stat-card.border-primary { border-left-color: #0d6efd; }
    .stat-card.border-warning { border-left-color: #ffc107; }
    .stat-card.border-success { border-left-color: #198754; }
    .stat-card.border-danger { border-left-color: #dc3545; }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-left: auto;
    }

    .stat-label {
        font-weight: 600;
        color: var(--text-muted);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-light);
        margin: 0;
    }

    .stat-subvalue {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 5px;
    }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 30px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .filter-section h5 {
        color: var(--text-light);
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .section-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .section-header {
        background: rgba(255, 255, 255, 0.05);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px 30px;
        border-radius: 2px 2px 0 0;
    }

    .section-header h5 {
        color: var(--text-light);
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        min-width: 1120px;
    }

    .table-custom thead th {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table-custom tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: background 0.2s;
    }

    .table-custom tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .table-custom tbody td {
        padding: 15px;
        color: var(--text-light);
        font-size: 13px;
        vertical-align: top;
    }

    .table-custom thead th:nth-child(1) { width: 86px; }
    .table-custom thead th:nth-child(2) { width: 190px; }
    .table-custom thead th:nth-child(3) { width: 120px; }
    .table-custom thead th:nth-child(4) { width: 180px; }
    .table-custom thead th:nth-child(5) { width: 90px; text-align: center; }
    .table-custom thead th:nth-child(6) { width: 32%; }
    .table-custom thead th:nth-child(7) { width: 140px; }
    .table-custom thead th:nth-child(8) { width: 96px; text-align: center; }

    .customer-name {
        color: var(--text-light);
        font-size: 16px;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 4px;
        display: block;
    }

    .customer-mail {
        color: var(--text-muted);
        font-size: 12px;
        display: block;
    }

    .content-snippet {
        line-height: 1.55;
        color: rgba(255, 255, 255, 0.94);
        max-width: 100%;
        word-break: break-word;
    }

    .score-cell {
        text-align: center;
    }

    .score-pill {
        min-width: 58px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    .reply-cell {
        white-space: nowrap;
    }

    .action-group {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .review-card {
        border-left: 3px solid;
    }

    .rating-stars {
        color: #ffc107;
    }

    .btn-custom {
        background: rgba(212, 175, 55, 0.2);
        border: 1px solid var(--accent-gold);
        color: var(--accent-gold);
        padding: 10px 20px;
        border-radius: 2px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-custom:hover {
        background: rgba(212, 175, 55, 0.3);
        color: var(--accent-gold);
        transform: translateY(-2px);
    }

    .btn-secondary-custom {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 10px 20px;
        border-radius: 2px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary-custom:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-light);
    }

    .btn-action {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-light);
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 12px;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        margin: 0 2px;
    }

    .btn-action:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--accent-gold);
        border-color: var(--accent-gold);
    }

    .btn-action.btn-primary-action {
        border-color: rgba(13, 110, 253, 0.3);
        color: #0d6efd;
    }

    .btn-action.btn-primary-action:hover {
        background: rgba(13, 110, 253, 0.2);
        border-color: #0d6efd;
    }

    .btn-action.btn-danger-action {
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    .btn-action.btn-danger-action:hover {
        background: rgba(220, 53, 69, 0.2);
        border-color: #dc3545;
    }

    .badge-custom {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-primary-custom {
        background: rgba(13, 110, 253, 0.2);
        color: #0d6efd;
        border: 1px solid rgba(13, 110, 253, 0.3);
    }

    .badge-info-custom {
        background: rgba(13, 202, 240, 0.2);
        color: #0dcaf0;
        border: 1px solid rgba(13, 202, 240, 0.3);
    }

    .badge-success-custom {
        background: rgba(25, 135, 84, 0.2);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.3);
    }

    .badge-warning-custom {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .badge-danger-custom {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .alert-custom {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-left: 4px solid;
        border-radius: 2px;
        padding: 15px 20px;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
    }

    .alert-success-custom {
        border-left-color: #198754;
        color: #198754;
    }

    .alert-danger-custom {
        border-left-color: #dc3545;
        color: #dc3545;
    }

    .alert-info-custom {
        border-left-color: #0dcaf0;
        color: #0dcaf0;
    }

    input[type="text"],
    input[type="date"],
    select,
    textarea {
        background: rgba(30, 30, 30, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 10px 15px;
        color: var(--text-light);
        font-size: 13px;
        width: 100%;
        transition: all 0.3s;
    }

    input[type="text"]:focus,
    input[type="date"]:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: var(--accent-gold);
        background: rgba(30, 30, 30, 0.9);
    }

    input[type="text"]::placeholder,
    textarea::placeholder {
        color: var(--text-muted);
    }

    select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23d4af37' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 35px;
        appearance: none;
    }

    label {
        color: var(--text-light);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: block;
    }

    @media (max-width: 900px) {
        .review-page-wrap {
            padding: 10px;
        }
        .page-header-section,
        .filter-section {
            padding: 18px;
            margin-bottom: 18px;
        }
        .section-header {
            padding: 14px 18px;
        }
    }
</style>

<div class="review-page-wrap">
<!-- Header Section -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2 style="margin: 0; color: var(--text-light); font-size: 24px; font-weight: 600; margin-bottom: 10px;">
                <i class="bi bi-star-fill" style="color: var(--accent-gold); margin-right: 10px;"></i> Quản lý Đánh giá & Phản hồi
            </h2>
            <p style="margin: 0; color: var(--text-muted); font-size: 13px;">Theo dõi và quản lý phản hồi đánh giá từ khách hàng</p>
        </div>
        <div>
            <a href="index.php?act=admin/danhGia/baoCao" class="btn-custom">
                <i class="bi bi-file-earmark-bar-graph"></i> Báo cáo tổng hợp
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert-custom alert-success-custom">
        <i class="bi bi-check-circle-fill"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert-custom alert-danger-custom">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Thống kê -->
<div class="stats-grid">
    <div class="stat-card border-primary">
        <div style="display: flex; align-items: center;">
            <div style="flex: 1;">
                <div class="stat-label">Tổng đánh giá</div>
                <div class="stat-value" style="color: #0d6efd;"><?= number_format($stats['tong_danh_gia']) ?></div>
            </div>
            <div class="stat-icon" style="background: rgba(13, 110, 253, 0.2); color: #0d6efd;">
                <i class="bi bi-chat-quote"></i>
            </div>
        </div>
    </div>
    <div class="stat-card border-warning">
        <div style="display: flex; align-items: center;">
            <div style="flex: 1;">
                <div class="stat-label">Điểm trung bình</div>
                <div class="stat-value" style="color: #ffc107;">
                    <?= number_format($stats['diem_trung_binh'], 1) ?> <i class="bi bi-star-fill"></i>
                </div>
            </div>
            <div class="stat-icon" style="background: rgba(255, 193, 7, 0.2); color: #ffc107;">
                <i class="bi bi-star"></i>
            </div>
        </div>
    </div>
    <div class="stat-card border-success">
        <div style="display: flex; align-items: center;">
            <div style="flex: 1;">
                <div class="stat-label">Khách hài lòng (≥4★)</div>
                <div class="stat-value" style="color: #198754;"><?= number_format($stats['hai_long']) ?></div>
                <div class="stat-subvalue">
                    <?= $stats['tong_danh_gia'] > 0 ? round(($stats['hai_long']/$stats['tong_danh_gia'])*100, 1) : 0 ?>%
                </div>
            </div>
            <div class="stat-icon" style="background: rgba(25, 135, 84, 0.2); color: #198754;">
                <i class="bi bi-emoji-smile"></i>
            </div>
        </div>
    </div>
    <div class="stat-card border-danger">
        <div style="display: flex; align-items: center;">
            <div style="flex: 1;">
                <div class="stat-label">Không hài lòng (≤2★)</div>
                <div class="stat-value" style="color: #dc3545;"><?= number_format($stats['khong_hai_long']) ?></div>
                <div class="stat-subvalue">
                    <?= $stats['tong_danh_gia'] > 0 ? round(($stats['khong_hai_long']/$stats['tong_danh_gia'])*100, 1) : 0 ?>%
                </div>
            </div>
            <div class="stat-icon" style="background: rgba(220, 53, 69, 0.2); color: #dc3545;">
                <i class="bi bi-emoji-frown"></i>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc -->
<div class="filter-section">
    <h5><i class="bi bi-funnel"></i> Bộ lọc</h5>
    <form method="GET" action="index.php">
        <input type="hidden" name="act" value="admin/danhGia">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div>
                <label>Loại đánh giá</label>
                <select name="loai">
                    <option value="">Tất cả</option>
                    <option value="Tour" <?= ($_GET['loai'] ?? '') === 'Tour' ? 'selected' : '' ?>>Tour</option>
                    <option value="NhaCungCap" <?= ($_GET['loai'] ?? '') === 'NhaCungCap' ? 'selected' : '' ?>>Nhà cung cấp</option>
                    <option value="NhanSu" <?= ($_GET['loai'] ?? '') === 'NhanSu' ? 'selected' : '' ?>>Nhân sự</option>
                </select>
            </div>
            <div>
                <label>Điểm tối thiểu</label>
                <select name="diem_min">
                    <option value="">Tất cả</option>
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($_GET['diem_min'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?>★</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label>Điểm tối đa</label>
                <select name="diem_max">
                    <option value="">Tất cả</option>
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($_GET['diem_max'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?>★</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label>Từ ngày</label>
                <input type="date" name="tu_ngay" value="<?= $_GET['tu_ngay'] ?? '' ?>">
            </div>
            <div>
                <label>Đến ngày</label>
                <input type="date" name="den_ngay" value="<?= $_GET['den_ngay'] ?? '' ?>">
            </div>
            <div>
                <label>Tìm kiếm</label>
                <input type="text" name="search" placeholder="Tên, nội dung..." value="<?= $_GET['search'] ?? '' ?>">
            </div>
        </div>
        <div>
            <button type="submit" class="btn-custom">
                <i class="bi bi-search"></i> Tìm kiếm
            </button>
            <a href="index.php?act=admin/danhGia" class="btn-secondary-custom">
                <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
            </a>
        </div>
    </form>
</div>

<!-- Danh sách đánh giá -->
<div class="section-card">
    <div class="section-header">
        <h5><i class="bi bi-chat-left-quote"></i> Danh sách Đánh giá (<?= count($danhGiaList) ?>)</h5>
    </div>
    <div style="padding: 0;">
        <?php if (empty($danhGiaList)): ?>
            <div class="alert-custom alert-info-custom" style="margin: 30px; text-align: center;">
                <i class="bi bi-info-circle" style="font-size: 32px; display: block; margin-bottom: 10px;"></i>
                <p style="margin: 0;">Không có đánh giá nào</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Khách hàng</th>
                            <th>Loại</th>
                            <th>Đối tượng</th>
                            <th>Điểm</th>
                            <th>Nội dung</th>
                            <th>Phản hồi</th>
                            <th style="text-align: center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhGiaList as $dg): ?>
                            <tr class="review-card" style="border-left-color: <?= $dg['diem'] >= 4 ? '#198754' : ($dg['diem'] <= 2 ? '#dc3545' : '#ffc107') ?>;">
                                <td><?= date('d/m/Y', strtotime($dg['ngay_danh_gia'])) ?></td>
                                <td>
                                    <span class="customer-name"><?= htmlspecialchars($dg['ten_khach_hang'] ?? 'N/A') ?></span>
                                    <span class="customer-mail"><?= htmlspecialchars($dg['email_khach_hang'] ?? '') ?></span>
                                </td>
                                <td>
                                    <?php
                                    $loaiBadge = [
                                        'Tour' => 'primary-custom',
                                        'NhaCungCap' => 'info-custom',
                                        'NhanSu' => 'success-custom'
                                    ];
                                    $loaiText = [
                                        'Tour' => 'Tour',
                                        'NhaCungCap' => 'Nhà cung cấp',
                                        'NhanSu' => 'Nhân sự'
                                    ];
                                    ?>
                                    <span class="badge-custom badge-<?= $loaiBadge[$dg['loai_danh_gia']] ?? 'secondary' ?>">
                                        <?= $loaiText[$dg['loai_danh_gia']] ?? $dg['loai_danh_gia'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($dg['loai_danh_gia'] === 'Tour'): ?>
                                        <?= htmlspecialchars($dg['ten_tour'] ?? 'N/A') ?>
                                    <?php elseif ($dg['loai_danh_gia'] === 'NhaCungCap'): ?>
                                        Nhà cung cấp #<?= $dg['nha_cung_cap_id'] ?? 'N/A' ?>
                                    <?php elseif ($dg['loai_danh_gia'] === 'NhanSu'): ?>
                                        Nhân sự #<?= $dg['nhan_su_id'] ?? 'N/A' ?>
                                    <?php endif; ?>
                                </td>
                                <td class="score-cell">
                                    <span class="badge-custom score-pill badge-<?= $dg['diem'] >= 4 ? 'success-custom' : ($dg['diem'] <= 2 ? 'danger-custom' : 'warning-custom') ?>">
                                        <?= $dg['diem'] ?> <i class="bi bi-star-fill"></i>
                                    </span>
                                </td>
                                <td>
                                    <div class="content-snippet">
                                        <?= htmlspecialchars(mb_substr($dg['noi_dung'] ?? '', 0, 100)) ?>
                                        <?= mb_strlen($dg['noi_dung'] ?? '') > 100 ? '...' : '' ?>
                                    </div>
                                </td>
                                <td class="reply-cell">
                                    <?php if (!empty($dg['phan_hoi_admin'])): ?>
                                        <span class="badge-custom badge-success-custom"><i class="bi bi-check-circle"></i> Đã trả lời</span>
                                    <?php else: ?>
                                        <span class="badge-custom badge-warning-custom"><i class="bi bi-clock"></i> Chưa trả lời</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-group">
                                        <a href="index.php?act=admin/danhGia/chiTiet&id=<?= $dg['danh_gia_id'] ?>" 
                                           class="btn-action btn-primary-action" title="Chi tiết">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" action="index.php?act=admin/danhGia/xoa" style="display:inline; margin:0;">
                                            <?php echo csrfField('admin_form'); ?>
                                            <input type="hidden" name="id" value="<?= (int)$dg['danh_gia_id'] ?>">
                                            <button type="submit"
                                                class="btn-action btn-danger-action"
                                                onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')"
                                                title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

 </div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
