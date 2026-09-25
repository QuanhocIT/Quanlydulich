<?php
$pageTitle = 'Trung tâm Tự động hóa Admin - Vue 3';
$currentPage = 'automation';

$cssPath = __DIR__ . '/../../public/dist/admin/automation-dashboard.css';
$jsPath = __DIR__ . '/../../public/dist/admin/automation-dashboard.js';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
$jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();

$additionalCSS = [
    BASE_URL . 'public/dist/admin/automation-dashboard.css?v=' . $cssVersion,
];

ob_start();
?>

<div class="admin-automation-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-automation-dashboard">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-warning mb-3" role="status">
                <span class="visually-hidden">Đang kết nối trung tâm tự động hóa...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo Automation Command Center...</h5>
            <p class="small text-secondary">Tải lịch sử job, sự kiện cảnh báo và đề xuất trợ lý vận hành</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_AUTOMATION_INIT__ = <?= json_encode($vueAutomationData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/automation-dashboard.js?v=<?= $jsVersion ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
