<?php
$pageTitle = 'Hồ sơ HDV: ' . htmlspecialchars($hdv['ho_ten'] ?? '');
$currentPage = 'hdv';
ob_start();
?>
<style>
    .profile-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
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
    .card-body {
        padding: 20px;
        color: var(--text-light);
    }
    .card-body.text-center {
        text-align: center;
    }
    .list-group {
        list-style: none;
        padding: 0;
    }
    .list-group-item {
        background: rgba(30, 30, 30, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 4px;
        color: var(--text-light);
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
    .btn-outline-secondary {
        background: rgba(108, 117, 125, 0.3);
        color: var(--text-light);
        border: 1px solid rgba(108, 117, 125, 0.5);
    }
    .btn-outline-secondary:hover {
        background: rgba(108, 117, 125, 0.5);
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.875rem;
    }
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
    .col-md-4 { width: 33.333333%; }
    .col-md-8 { width: 66.666667%; }
    .mb-2 { margin-bottom: 0.5rem; }
    .mb-3 { margin-bottom: 1rem; }
    .py-4 { padding-top: 1.5rem; padding-bottom: 1.5rem; }
    .d-flex { display: flex; }
    .justify-content-between { justify-content: space-between; }
    .text-center { text-align: center; }
    .img-fluid {
        max-width: 100%;
        height: auto;
    }
    .rounded {
        border-radius: 4px;
    }
    .bg-secondary {
        background: rgba(108, 117, 125, 0.3);
        color: var(--text-light);
    }
    .text-white {
        color: var(--text-light) !important;
    }
    @media (max-width: 768px) {
        .col-md-4, .col-md-8 {
            width: 100%;
        }
    }
</style>

<div style="padding: 20px;">
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-person-badge" style="color: var(--accent-gold);"></i> Hồ sơ: <?php echo htmlspecialchars($hdv['ho_ten'] ?? '') ?>
        </h1>
        <a href="index.php?act=admin/quanLyHDV" style="background: rgba(108, 117, 125, 0.3); color: var(--text-light); padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(108, 117, 125, 0.5); font-size: 0.875rem;">
            Quay lại
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body text-center">
                    <?php if (!empty($hdv['anh'])): ?>
                        <img src="<?php echo htmlspecialchars($hdv['anh']) ?>" class="img-fluid rounded mb-2" style="max-width: 200px;">
                    <?php else: ?>
                        <div class="bg-secondary text-white rounded" style="padding: 40px; margin-bottom: 10px;">No image</div>
                    <?php endif; ?>
                    <h5 style="color: var(--text-light);"><?php echo htmlspecialchars($hdv['ho_ten'] ?? '') ?></h5>
                    <p style="color: var(--text-muted);"><?php echo htmlspecialchars($hdv['so_dien_thoai'] ?? '') ?></p>
                    <p style="color: var(--text-muted);"><?php echo htmlspecialchars($hdv['email'] ?? '') ?></p>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h6 style="color: var(--text-light); margin-bottom: 15px;">Thông tin</h6>
                    <p style="color: var(--text-light);"><strong>Ngày sinh:</strong> <?php echo htmlspecialchars($hdv['ngay_sinh'] ?? '') ?></p>
                    <p style="color: var(--text-light);"><strong>Chứng chỉ:</strong><br><?php echo nl2br(htmlspecialchars($hdv['chung_chi'] ?? '')) ?></p>
                    <p style="color: var(--text-light);"><strong>Ngôn ngữ:</strong> <?php echo htmlspecialchars($hdv['ngon_ngu'] ?? '') ?></p>
                    <p style="color: var(--text-light);"><strong>Sức khoẻ:</strong> <?php echo htmlspecialchars($hdv['suc_khoe'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 style="color: var(--text-light); margin-bottom: 15px;">Kinh nghiệm</h6>
                    <p style="color: var(--text-light);"><?php echo nl2br(htmlspecialchars($hdv['kinh_nghiem'] ?? '')) ?></p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h6 style="color: var(--text-light); margin-bottom: 15px;">Lịch sử dẫn tour</h6>
                    <?php if (!empty($history)): ?>
                        <ul class="list-group">
                            <?php foreach($history as $h): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($h['start_time'] ?? '') ?></strong>
                                    - <?php echo htmlspecialchars($h['title'] ?? ($h['ten_tour'] ?? 'Tour')) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p style="color: var(--text-muted);">Chưa có lịch sử.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
