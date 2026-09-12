<?php
$pageTitle = 'Quản lý Nhà cung cấp - Vue 3';
$currentPage = 'nhaCungCap';

$additionalCSS = [
    BASE_URL . 'public/dist/admin/supplier-manage.css?v=' . rawurlencode(ASSET_VERSION),
];

ob_start();
?>

<div class="admin-supplier-manage-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-supplier-manage">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-warning mb-3" role="status">
                <span class="visually-hidden">Đang tải danh sách nhà cung cấp...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo mạng lưới đối tác...</h5>
            <p class="small text-secondary">Tải hồ sơ nhà cung cấp khách sạn, nhà hàng, vận chuyển và dịch vụ du lịch</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_SUPPLIER_MANAGE_INIT__ = <?= json_encode($vueSupplierData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/supplier-manage.js?v=<?= rawurlencode(ASSET_VERSION) ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
