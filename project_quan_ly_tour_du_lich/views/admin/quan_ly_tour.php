<?php
$pageTitle = 'Quản lý Tour';
$currentPage = 'quanLyTour';
ob_start();
?>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header h2 {
        font-size: 24px;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }

    .page-header p {
        color: var(--text-muted);
        font-size: 13px;
    }

    .filter-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 20px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
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

    .badge {
        padding: 4px 10px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
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
</style>

<div class="page-header">
    <div>
        <h2>Quản lý Tour</h2>
        <p>Quản lý thông tin các tour du lịch</p>
    </div>
    <a href="<?php echo BASE_URL; ?>index.php?act=tour/create" class="btn btn-primary">
        + Thêm tour mới
    </a>
</div>

<!-- Filter Section -->
<div class="filter-section">
    <form method="get" action="index.php">
        <input type="hidden" name="act" value="admin/quanLyTour">
        <div class="filter-row">
            <div class="form-group">
                <label>Loại tour</label>
                <select name="loai_tour" class="form-group select">
                    <option value="">Tất cả</option>
                    <option value="TrongNuoc" <?php echo (isset($_GET['loai_tour']) && $_GET['loai_tour'] === 'TrongNuoc') ? 'selected' : ''; ?>>Trong nước</option>
                    <option value="QuocTe" <?php echo (isset($_GET['loai_tour']) && $_GET['loai_tour'] === 'QuocTe') ? 'selected' : ''; ?>>Quốc tế</option>
                    <option value="TheoYeuCau" <?php echo (isset($_GET['loai_tour']) && $_GET['loai_tour'] === 'TheoYeuCau') ? 'selected' : ''; ?>>Theo yêu cầu</option>
                </select>
            </div>
            <div class="form-group">
                <label>Trạng thái</label>
                <select name="trang_thai" class="form-group select">
                    <option value="">Tất cả</option>
                    <option value="HoatDong">Hoạt động</option>
                    <option value="TamDung">Ngừng hoạt động</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tìm kiếm</label>
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
                    <th style="width: 80px;">ID</th>
                    <th>Tên tour</th>
                    <th style="width: 120px;">Loại tour</th>
                    <th style="width: 150px; text-align: right;">Giá cơ bản</th>
                    <th style="width: 120px; text-align: center;">Trạng thái</th>
                    <th style="width: 400px; text-align: center;">Hành động</th>
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
                            'TrongNuoc' => ['Trong nước', 'badge-info'],
                            'QuocTe' => ['Quốc tế', 'badge-warning'],
                            'TheoYeuCau' => ['Theo yêu cầu', 'badge-secondary']
                        ];
                        $loai = $tour['loai_tour'] ?? 'TrongNuoc';
                        $label = $loaiTourLabels[$loai] ?? $loaiTourLabels['TrongNuoc'];
                        ?>
                        <span class="badge <?php echo $label[1]; ?>"><?php echo $label[0]; ?></span>
                    </td>
                    <td style="text-align: right;">
                        <span style="font-weight: 600; color: var(--accent-gold);"><?php echo number_format((float)$tour['gia_co_ban'], 0, ',', '.'); ?>đ</span>
                    </td>
                    <td style="text-align: center;">
                        <?php if ($tour['trang_thai'] === 'HoatDong'): ?>
                            <span class="badge badge-success">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Ngừng</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <div class="btn-group">
                            <a href="<?php echo BASE_URL; ?>index.php?act=tour/update&id=<?php echo urlencode($tour['tour_id']); ?>" 
                               class="btn btn-secondary btn-sm" title="Sửa tour">
                                Sửa
                            </a>
                            <a href="<?php echo BASE_URL; ?>index.php?act=admin/chiTietTour&id=<?php echo urlencode($tour['tour_id']); ?>" 
                               class="btn btn-secondary btn-sm" title="Chi tiết tour">
                                Chi tiết
                            </a>
                            <a href="<?php echo BASE_URL; ?>index.php?act=tour/clone&id=<?php echo urlencode($tour['tour_id']); ?>" 
                               class="btn btn-secondary btn-sm" title="Sao chép tour" 
                               onclick="return confirm('Bạn có muốn sao chép tour này?');">
                                Clone
                            </a>
                            <?php 
                            $qrPath = !empty($tour['qr_code_path']) ? BASE_URL . $tour['qr_code_path'] : null;
                            if ($qrPath): ?>
                                <a href="<?php echo $qrPath; ?>" class="btn btn-secondary btn-sm" target="_blank" rel="noopener" title="Xem mã QR">
                                    QR
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo BASE_URL; ?>index.php?act=tour/generateQr&id=<?php echo urlencode($tour['tour_id']); ?>"
                               class="btn btn-secondary btn-sm" title="Tạo/Cập nhật mã QR">
                                Tạo QR
                            </a>
                            <button onclick="if(confirm('Bạn có chắc muốn xóa tour này?')) window.location.href='<?php echo BASE_URL; ?>index.php?act=tour/delete&id=<?php echo urlencode($tour['tour_id']); ?>'" 
                                    class="btn btn-secondary btn-sm" style="background: rgba(220, 53, 69, 0.2); color: #dc3545; border-color: rgba(220, 53, 69, 0.3);" title="Xóa tour">
                                Xóa
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="padding: 15px 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); color: var(--text-muted); font-size: 12px;">
            Tổng số: <strong><?php echo count($tours); ?></strong> tour
        </div>
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
