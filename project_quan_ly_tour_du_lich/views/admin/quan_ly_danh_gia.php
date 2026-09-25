<?php
$pageTitle = 'Quản lý Đánh giá & Phản hồi - Vue 3';
$currentPage = 'danhGia';

$cssPath = __DIR__ . '/../../public/dist/admin/review-manage.css';
$jsPath = __DIR__ . '/../../public/dist/admin/review-manage.js';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
$jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();

$additionalCSS = [
    BASE_URL . 'public/dist/admin/review-manage.css?v=' . $cssVersion,
];

ob_start();
?>

<div class="admin-review-manage-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-review-manage">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-warning mb-3" role="status">
                <span class="visually-hidden">Đang tải đánh giá & phản hồi...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo quản lý đánh giá...</h5>
            <p class="small text-secondary">Tải dữ liệu phản hồi trải nghiệm du khách, đối tác nhà cung cấp và nhân sự</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_DANH_GIA_INIT__ = <?= json_encode($vueDanhGiaData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/review-manage.js?v=<?= $jsVersion ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
