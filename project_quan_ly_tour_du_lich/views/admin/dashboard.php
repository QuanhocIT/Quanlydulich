<?php
$pageTitle = 'Dashboard Quản Trị - Vue 3';
$currentPage = 'dashboard';

$additionalCSS = [
    BASE_URL . 'public/dist/admin/dashboard.css?v=' . rawurlencode(ASSET_VERSION),
];

ob_start();
?>

<div class="admin-dashboard-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-dashboard">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Đang tải Vue 3...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo giao diện Vue 3...</h5>
            <p class="small text-secondary">Tải số liệu thống kê, biểu đồ và tự động hóa hệ thống</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__ADMIN_DASHBOARD_INIT__ = <?= json_encode($vueDashboardData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/dashboard.js?v=<?= rawurlencode(ASSET_VERSION) ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
