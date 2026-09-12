<?php
$pageTitle = 'Báo Cáo Tài Chính - Vue 3';
$currentPage = 'baoCaoTaiChinh';

$additionalCSS = [
    BASE_URL . 'public/dist/admin/finance-dashboard.css?v=' . rawurlencode(ASSET_VERSION),
];

ob_start();
?>

<div class="admin-finance-dashboard-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-finance-dashboard">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-warning mb-3" role="status">
                <span class="visually-hidden">Đang tải báo cáo tài chính...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo trung tâm tài chính...</h5>
            <p class="small text-secondary">Tổng hợp doanh thu thực tế, chi phí vận hành và bảng xếp hạng tour</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_FINANCE_INIT__ = <?= json_encode($vueFinanceData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/finance-dashboard.js?v=<?= rawurlencode(ASSET_VERSION) ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
