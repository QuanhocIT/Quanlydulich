<?php
$pageTitle = 'Quản Lý Nhân Sự - Vue 3';
$currentPage = 'nhanSu';

$additionalCSS = [
    BASE_URL . 'public/dist/admin/personnel-manage.css?v=' . rawurlencode(ASSET_VERSION),
];

ob_start();
?>

<div class="admin-personnel-manage-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-personnel-manage">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Đang tải danh sách nhân sự...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo quản lý nhân sự...</h5>
            <p class="small text-secondary">Tải hồ sơ hướng dẫn viên, điều hành tour và phân quyền chuyên môn</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_PERSONNEL_MANAGE_INIT__ = <?= json_encode($vueNhanSuData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/personnel-manage.js?v=<?= rawurlencode(ASSET_VERSION) ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
