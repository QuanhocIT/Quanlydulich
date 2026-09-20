<?php
$pageTitle = 'Quản lý Tour - Vue 3';
$currentPage = 'quanLyTour';

$cssPath = __DIR__ . '/../../public/dist/admin/tour-list.css';
$jsPath = __DIR__ . '/../../public/dist/admin/tour-list.js';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
$jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();

$additionalCSS = [
    BASE_URL . 'public/dist/admin/tour-list.css?v=' . $cssVersion,
];

ob_start();
?>

<div class="admin-tour-list-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-tour-list">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Đang tải danh sách Tour...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo danh mục Tour...</h5>
            <p class="small text-secondary">Tải bộ lọc, danh sách tour và các tùy chọn điều hành</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_TOUR_LIST_INIT__ = <?= json_encode($vueTourListData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/tour-list.js?v=<?= $jsVersion ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
