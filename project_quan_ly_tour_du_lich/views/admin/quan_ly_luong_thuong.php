<?php
$pageTitle = 'Quản lý lương thưởng nhân sự - Vue 3';
$currentPage = 'luongThuong';

$cssPath = __DIR__ . '/../../public/dist/admin/salary-manage.css';
$jsPath = __DIR__ . '/../../public/dist/admin/salary-manage.js';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
$jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();

$additionalCSS = [
    BASE_URL . 'public/dist/admin/salary-manage.css?v=' . $cssVersion,
];

ob_start();
?>

<div class="admin-salary-manage-wrapper">
    <!-- Vue 3 App Mount Target -->
    <div id="vue-admin-salary-manage">
        <!-- Initial skeleton fallback while Vue mounts -->
        <div class="p-4 text-center text-muted" style="min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="spinner-border text-warning mb-3" role="status">
                <span class="visually-hidden">Đang tải bảng lương thưởng...</span>
            </div>
            <h5 class="fw-bold text-light">Đang khởi tạo danh sách lương thưởng nhân sự...</h5>
            <p class="small text-secondary">Tải dữ liệu tổng hợp lương, phân bổ hoa hồng và bảng duyệt lương</p>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    window.__BASE_URL__ = '<?= BASE_URL ?>';
    window.__ADMIN_SALARY_MANAGE_INIT__ = <?= json_encode($vueSalaryManageData ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script type="module" nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>" src="<?= BASE_URL ?>public/dist/admin/salary-manage.js?v=<?= $jsVersion ?>"></script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
