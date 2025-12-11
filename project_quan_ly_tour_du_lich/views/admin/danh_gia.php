<?php
$pageTitle = 'Quản lý Đánh giá';
$currentPage = 'danh_gia';
ob_start();
?>
<style>
    .content-section {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 25px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }
</style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-star" style="color: var(--accent-gold);"></i> Quản lý Đánh giá
        </h1>
        <a href="index.php?act=admin/dashboard" style="color: var(--accent-gold); text-decoration: none;">← Quay lại Dashboard</a>
    </div>
    
    <div class="content-section">
        <h2 style="color: var(--text-light);">Danh sách Đánh giá</h2>
        <!-- Nội dung đánh giá sẽ được thêm vào đây -->
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>


