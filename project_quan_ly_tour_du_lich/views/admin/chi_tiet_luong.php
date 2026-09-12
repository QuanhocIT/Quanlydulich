<?php
$pageTitle = 'Chi tiết lương nhân sự - Vue 3';
$currentPage = 'luongThuong';

$additionalCSS = [
    BASE_URL . 'public/dist/admin/salary-detail.css?v=' . rawurlencode(ASSET_VERSION),
];

ob_start();
?>

<div class="admin-salary-detail-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-salary-detail">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Đang tải bảng lương...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo chi tiết lương nhân sự...</h5>
            <p class="small text-secondary">Tải bảng tính hoa hồng, định mức chuyến và trạng thái duyệt lương</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_SALARY_DETAIL_INIT__ = <?= json_encode($vueSalaryData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/salary-detail.js?v=<?= rawurlencode(ASSET_VERSION) ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
