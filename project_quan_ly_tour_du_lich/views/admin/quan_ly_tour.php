<?php
$pageTitle = 'Quản lý Tour';
$currentPage = 'quanLyTour';
ob_start();
?>

<style>
    .page-header {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        display: flex;
        align-items: center;
        gap: 22px;
        overflow: hidden;
        flex-wrap: wrap;
    }

    .page-header-glow {
        position: absolute;
        top: 0;
        left: -60%;
        width: 60%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: page-header-glow-move 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }

    @keyframes page-header-glow-move {
        0% { left: -60%; }
        100% { left: 100%; }
    }

    .page-header-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        z-index: 2;
        flex-shrink: 0;
    }

    .page-header-body {
        flex: 1;
        z-index: 2;
    }

    .page-header h2 {
        font-size: 1.7rem;
        letter-spacing: 1px;
        margin-bottom: 6px;
        color: #ffe082;
        font-weight: 700;
        text-shadow: 0 2px 8px #2d2d2d;
    }

    .page-header p {
        color: #fffde7;
        font-size: 1rem;
        text-shadow: 0 1px 4px #2d2d2d;
    }

    .page-header-actions {
        z-index: 2;
    }

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
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        align-items: end;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group .input,
    .form-group .select {
        width: 100%;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text-light);
        border-radius: 4px;
        padding: 10px 12px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .form-group .input::placeholder {
        color: rgba(255, 255, 255, 0.45);
    }

    .form-group .input:focus,
    .form-group .select:focus {
        outline: none;
        border-color: var(--accent-gold);
        box-shadow: 0 0 0 2px rgba(212,175,55,0.15);
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

    .badge {
        padding: 4px 10px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    .badge-success {
        background: rgba(72, 187, 120, 0.2);
        color: #48bb78;
        border: 1px solid rgba(72, 187, 120, 0.3);
    }

    .badge-danger {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .badge-info {
        background: rgba(102, 126, 234, 0.2);
        color: #667eea;
        border: 1px solid rgba(102, 126, 234, 0.3);
    }

    .badge-warning {
        background: rgba(237, 137, 54, 0.2);
        color: #ed8936;
        border: 1px solid rgba(237, 137, 54, 0.3);
    }

    .badge-secondary {
        background: rgba(160, 174, 192, 0.2);
        color: #a0aec0;
        border: 1px solid rgba(160, 174, 192, 0.3);
    }

    .btn-group {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 11px;
        letter-spacing: 0.5px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(72, 187, 120, 0.1);
        border-color: rgba(72, 187, 120, 0.3);
        color: #48bb78;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    @media (max-width: 1024px) {
        .filter-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 680px) {
        .page-header {
            padding: 20px;
        }

        .page-header h2 {
            font-size: 1.4rem;
        }

        .filter-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div class="page-header-glow"></div>
    <div class="page-header-avatar">🗺️</div>
    <div class="page-header-body">
        <h2>Quản lý Tour</h2>
        <p>Quản lý thông tin các tour du lịch</p>
    </div>
    <div class="page-header-actions">
        <a href="<?php echo BASE_URL; ?>index.php?act=tour/create" class="btn btn-primary">
            + Thêm tour mới
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="get" action="index.php">
        <input type="hidden" name="act" value="admin/quanLyTour">
        <div class="filter-row">
            <div class="form-group">
                <label><i class="bi bi-globe2 me-1"></i>Loại tour</label>
                <select name="loai_tour" class="form-group select">
                    <option value="">Tất cả</option>
                    <option value="TrongNuoc" <?php echo (isset($_GET['loai_tour']) && $_GET['loai_tour'] === 'TrongNuoc') ? 'selected' : ''; ?>>Trong nước</option>
                    <option value="QuocTe" <?php echo (isset($_GET['loai_tour']) && $_GET['loai_tour'] === 'QuocTe') ? 'selected' : ''; ?>>Quốc tế</option>
                    <option value="TheoYeuCau" <?php echo (isset($_GET['loai_tour']) && $_GET['loai_tour'] === 'TheoYeuCau') ? 'selected' : ''; ?>>Theo yêu cầu</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="bi bi-info-circle me-1"></i>Trạng thái</label>
                <select name="trang_thai" class="form-group select">
                    <option value="">Tất cả</option>
                    <option value="HoatDong">Hoạt động</option>
                    <option value="TamDung">Ngừng hoạt động</option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="bi bi-search me-1"></i>Tìm kiếm</label>
                <input type="search" name="search" class="form-group input" placeholder="Tên tour, điểm đến..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Lọc
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Tours Table -->
<?php if (!empty($tours)): ?>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;"><i class="bi bi-hash me-1"></i>ID</th>
                    <th><i class="bi bi-card-text me-1"></i>Tên tour</th>
                    <th style="width: 120px;"><i class="bi bi-globe2 me-1"></i>Loại tour</th>
                    <th style="width: 150px; text-align: right;"><i class="bi bi-cash me-1"></i>Giá cơ bản</th>
                    <th style="width: 120px; text-align: center;"><i class="bi bi-info-circle me-1"></i>Trạng thái</th>
                    <th style="width: 400px; text-align: center;"><i class="bi bi-tools me-1"></i>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tours as $tour) : ?>
                <tr>
                    <td>
                        <span class="badge badge-secondary">#<?php echo htmlspecialchars($tour['tour_id']); ?></span>
                    </td>
                    <td>
                        <div style="font-weight: 600; margin-bottom: 5px;"><?php echo htmlspecialchars($tour['ten_tour']); ?></div>
                        <?php if (!empty($tour['diem_khoi_hanh'])): ?>
                            <small style="color: var(--text-muted);">📍 <?php echo htmlspecialchars($tour['diem_khoi_hanh']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        $loaiTourLabels = [
                            'TrongNuoc' => ['<i class="bi bi-geo-alt-fill me-1"></i>Trong nước', 'badge-info'],
                            'QuocTe' => ['<i class="bi bi-airplane-fill me-1"></i>Quốc tế', 'badge-warning'],
                            'TheoYeuCau' => ['<i class="bi bi-stars me-1"></i>Theo yêu cầu', 'badge-secondary']
                        ];
                        $loai = $tour['loai_tour'] ?? 'TrongNuoc';
                        $label = $loaiTourLabels[$loai] ?? $loaiTourLabels['TrongNuoc'];
                        ?>
                        <span class="badge <?php echo $label[1]; ?>"><?php echo $label[0]; ?></span>
                    </td>
                    <td style="text-align: right;">
                        <span style="font-weight: 600; color: var(--accent-gold);"><i class="bi bi-cash me-1"></i><?php echo number_format((float)$tour['gia_co_ban'], 0, ',', '.'); ?>đ</span>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($tour['trang_thai'] === 'HoatDong'): ?>
                            <span class="badge badge-success"><i class="bi bi-check-circle me-1"></i>Hoạt động</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="bi bi-x-circle me-1"></i>Ngừng</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <div class="btn-group">
                            <a href="<?php echo BASE_URL; ?>index.php?act=tour/update&id=<?php echo urlencode($tour['tour_id']); ?>" 
                               class="btn btn-secondary btn-sm" title="Sửa tour">
                                <i class="bi bi-pencil-square me-1"></i> Sửa
                            </a>
                            <a href="<?php echo BASE_URL; ?>index.php?act=admin/chiTietTour&id=<?php echo urlencode($tour['tour_id']); ?>" 
                               class="btn btn-secondary btn-sm" title="Chi tiết tour">
                                <i class="bi bi-eye me-1"></i> Chi tiết
                            </a>
                            <a href="<?php echo BASE_URL; ?>index.php?act=tour/clone&id=<?php echo urlencode($tour['tour_id']); ?>" 
                               class="btn btn-secondary btn-sm" title="Sao chép tour" 
                               onclick="return confirm('Bạn có muốn sao chép tour này?');">
                                <i class="bi bi-files me-1"></i> Clone
                            </a>
                            <?php 
                            $qrPath = !empty($tour['qr_code_path']) ? BASE_URL . $tour['qr_code_path'] : null;
                            if ($qrPath): ?>
                                <a href="<?php echo $qrPath; ?>" class="btn btn-secondary btn-sm" target="_blank" rel="noopener" title="Xem mã QR">
                                    <i class="bi bi-qr-code me-1"></i> QR
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>index.php?act=tour/generateQr&id=<?php echo urlencode($tour['tour_id']); ?>"
                               class="btn btn-secondary btn-sm" title="Tạo/Cập nhật mã QR">
                                <i class="bi bi-qr-code me-1"></i> Tạo QR
                            </a>
                            <form method="POST" action="<?php echo BASE_URL; ?>index.php?act=tour/delete" style="display:inline; margin:0;">
                                <?php echo csrfField('tour_delete'); ?>
                                <input type="hidden" name="id" value="<?php echo (int)$tour['tour_id']; ?>">
                                <button type="submit"
                                        onclick="return confirm('Bạn có chắc muốn xóa tour này?')"
                                        class="btn btn-secondary btn-sm" style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);" title="Xóa tour">
                                    <i class="bi bi-trash me-1"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding: 15px 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-muted); font-size: 12px;">
            Tổng số: <strong><?php echo $totalTours ?? count($tours); ?></strong> tour
        </div>
        <?php $pageNumber = isset($pageNumber) ? (int)$pageNumber : max(1, (int)($_GET['page'] ?? 1)); ?>
        <?php if (($totalPages ?? 1) > 1): ?>
        <nav aria-label="Phân trang tour" style="padding: 12px 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <ul class="pagination justify-content-center mb-0">
                <?php if ($pageNumber > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pageNumber - 1])); ?>">‹</a>
                </li>
                <?php endif; ?>
                <?php
                $pageStart = max(1, $pageNumber - 2);
                $pageEnd   = min($totalPages, $pageNumber + 2);
                for ($p = $pageStart; $p <= $pageEnd; $p++):
                ?>
                <li class="page-item <?php echo $p === $pageNumber ? 'active' : ''; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($pageNumber < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $pageNumber + 1])); ?>">›</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="table-wrapper">
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h5 style="margin-bottom: 10px;">Chưa có tour nào</h5>
            <p style="margin-bottom: 20px;">Bắt đầu bằng cách thêm tour mới</p>
            <a href="<?php echo BASE_URL; ?>index.php?act=tour/create" class="btn btn-primary">
                Thêm tour đầu tiên
            </a>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
