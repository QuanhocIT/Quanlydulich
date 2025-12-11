<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php?act=auth/login');
    exit;
}

$typeLabels = [
    'hanh_trinh' => '📍 Hành trình',
    'su_co' => '⚠️ Sự cố',
    'phan_hoi' => '💬 Phản hồi',
    'hoat_dong' => '🎯 Hoạt động'
];

$images = [];
if (!empty($entry['hinh_anh'])) {
    $decoded = json_decode($entry['hinh_anh'], true);
    if (is_array($decoded)) {
        $images = $decoded;
    }
}
$pageTitle = 'Chi tiết nhật ký tour';
$currentPage = 'nhat_ky_tour';
ob_start();
?>
<style>
    .info-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }
    .card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        backdrop-filter: blur(10px);
    }
    .card-header {
        background: rgba(45, 45, 45, 0.7);
        color: var(--text-light);
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .card-body {
        padding: 20px;
        color: var(--text-light);
    }
    .badge {
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .bg-primary { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
    .alert {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .alert-warning {
        background: rgba(255, 193, 7, 0.2);
        border: 1px solid rgba(255, 193, 7, 0.5);
        color: #ffc107;
    }
    .btn {
        padding: 10px 20px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }
    .btn-outline-primary {
        background: rgba(0, 123, 255, 0.3);
        color: #4da3ff;
        border: 1px solid rgba(0, 123, 255, 0.5);
    }
    .btn-outline-primary:hover {
        background: rgba(0, 123, 255, 0.5);
    }
    .btn-secondary {
        background: rgba(108, 117, 125, 0.3);
        color: var(--text-light);
        border: 1px solid rgba(108, 117, 125, 0.5);
    }
    .btn-secondary:hover {
        background: rgba(108, 117, 125, 0.5);
    }
    .text-muted { color: var(--text-muted) !important; }
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
    }
    .row > * {
        padding-left: 15px;
        padding-right: 15px;
    }
    .col-lg-5 { width: 41.666667%; }
    .col-lg-7 { width: 58.333333%; }
    .col-md-4 { width: 33.333333%; }
    .g-3 { gap: 1rem; }
    .mb-0 { margin-bottom: 0; }
    .mb-1 { margin-bottom: 0.25rem; }
    .mb-3 { margin-bottom: 1rem; }
    .mb-4 { margin-bottom: 1.5rem; }
    .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
    .d-flex { display: flex; }
    .justify-content-between { justify-content: space-between; }
    .align-items-center { align-items: center; }
    .fw-bold { font-weight: 700; }
    .text-uppercase { text-transform: uppercase; }
    .ratio {
        position: relative;
        width: 100%;
    }
    .ratio::before {
        content: '';
        display: block;
        padding-top: 100%;
    }
    .ratio > * {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    @media (max-width: 992px) {
        .col-lg-5, .col-lg-7, .col-md-4 {
            width: 100%;
        }
    }
</style>

<div style="padding: 20px;">
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <div>
            <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-file-earmark-text" style="color: var(--accent-gold);"></i> Chi tiết nhật ký tour
            </h1>
            <p style="margin: 0; opacity: 0.8; color: var(--text-light);">Ghi nhận đầy đủ thông tin do hướng dẫn viên báo cáo</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="index.php?act=admin/formNhatKyTour&id=<?php echo $entry['id']; ?>" style="background: rgba(0, 123, 255, 0.3); color: #4da3ff; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(0, 123, 255, 0.5);">
                <i class="bi bi-pencil"></i> Chỉnh sửa
            </a>
            <a href="index.php?act=admin/quanLyNhatKyTour" style="background: rgba(108, 117, 125, 0.3); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(108, 117, 125, 0.5);">
                <i class="bi bi-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($entry['tieu_de'] ?? ''); ?></strong>
                            <div class="text-muted small">
                                <i class="bi bi-calendar-event"></i>
                                <?php echo date('d/m/Y H:i', strtotime($entry['ngay_ghi'])); ?>
                            </div>
                        </div>
                        <span class="badge bg-primary fs-6">
                            <?php echo $typeLabels[$entry['loai_nhat_ky']] ?? $entry['loai_nhat_ky']; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted">Nội dung</h6>
                        <p class="mb-4">
                            <?php echo nl2br(htmlspecialchars($entry['noi_dung'] ?? '')); ?>
                        </p>

                        <?php if (!empty($entry['cach_xu_ly'])): ?>
                            <div class="alert alert-warning">
                                <strong><i class="bi bi-tools"></i> Cách xử lý / ghi chú:</strong><br>
                                <?php echo nl2br(htmlspecialchars($entry['cach_xu_ly'])); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($images)): ?>
                            <h6 class="text-uppercase text-muted">Hình ảnh đính kèm</h6>
                            <div class="row g-3">
                                <?php foreach ($images as $img): ?>
                                    <div class="col-md-4">
                                        <div class="ratio ratio-1x1 border rounded overflow-hidden">
                                            <img src="<?php echo BASE_URL . $img; ?>" 
                                                 alt="Ảnh nhật ký"
                                                 style="object-fit: cover; cursor: pointer;"
                                                 onclick="window.open('<?php echo BASE_URL . $img; ?>', '_blank')">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">Không có hình ảnh đính kèm.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>Thông tin tour</strong>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Tour:</strong> <?php echo htmlspecialchars($entry['ten_tour'] ?? 'N/A'); ?></p>
                        <p class="mb-1"><strong>ID tour:</strong> #<?php echo htmlspecialchars($entry['tour_id'] ?? 'N/A'); ?></p>
                        <p class="mb-0"><strong>Ngày ghi nhận:</strong> <?php echo date('d/m/Y', strtotime($entry['ngay_ghi'])); ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Thông tin hướng dẫn viên</strong>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($entry['hdv_ten'] ?? 'N/A'); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($entry['hdv_email'] ?? 'N/A'); ?></p>
                        <p class="mb-0"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($entry['hdv_sdt'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>

