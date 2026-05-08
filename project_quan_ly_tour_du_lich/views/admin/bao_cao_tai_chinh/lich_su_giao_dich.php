<?php
$pageTitle = 'Lịch Sử Giao Dịch';
$currentPage = 'baoCaoTaiChinh';
/** @var float $tongThu */ $tongThu ??= 0.0;
/** @var float $tongChi */ $tongChi ??= 0.0;
ob_start();
?>
<style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }
        .report-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 25px;
            backdrop-filter: blur(10px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: var(--text-light);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            font-weight: 600;
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .badge-thu {
            background: rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .badge-chi {
            background: rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-gold);
            color: #000;
            font-weight: 500;
        }
        .btn:hover {
            background: #ffd700;
        }
        body.page-baoCaoTaiChinh .content-area {
            padding: 34px 48px 56px;
            background:
                radial-gradient(circle at 12% 8%, rgba(32, 178, 170, 0.08), transparent 28%),
                radial-gradient(circle at 84% 16%, rgba(212, 175, 55, 0.10), transparent 30%),
                linear-gradient(135deg, #131616 0%, #181b1c 48%, #111313 100%);
        }
        body.page-baoCaoTaiChinh .finance-transaction-page {
            max-width: 100% !important;
            padding: 0 !important;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .page-header-section {
            min-height: 150px;
            padding: 28px 34px !important;
            border: 1px solid rgba(212, 175, 55, 0.22);
            border-radius: 8px;
            background:
                linear-gradient(90deg, rgba(22,24,24,.94) 0%, rgba(34,34,25,.9) 54%, rgba(212,175,55,.48) 100%),
                url('<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg') center/cover;
            box-shadow: 0 22px 48px rgba(0,0,0,.26);
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .page-header-section::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0,0,0,.08), rgba(0,0,0,.35));
            pointer-events: none;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .page-header-section > * {
            position: relative;
            z-index: 1;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .page-header-section h1 {
            color: #ffe082 !important;
            font-size: 2.15rem !important;
            letter-spacing: 0 !important;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .stats-grid {
            gap: 24px;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .stat-card,
        body.page-baoCaoTaiChinh .finance-transaction-page .report-card {
            background: rgba(28, 30, 31, .80);
            border: 1px solid rgba(212, 175, 55, .20);
            border-radius: 8px;
            box-shadow: 0 18px 38px rgba(0,0,0,.20);
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .stat-card {
            min-height: 112px;
            border-left: 5px solid var(--accent-gold);
            padding: 26px;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .stat-card h3 {
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .report-card {
            overflow-x: auto;
            padding: 30px;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page table {
            min-width: 1060px;
            margin-top: 0;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page th {
            background: linear-gradient(90deg, rgba(212,175,55,.16), rgba(255,255,255,.04));
            color: #d4af37;
            padding: 18px 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: .84rem;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page td {
            padding: 20px 16px;
            vertical-align: middle;
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .btn,
        body.page-baoCaoTaiChinh .finance-transaction-page a[style*="accent-gold"] {
            min-height: 46px;
            border-radius: 8px !important;
            font-weight: 700 !important;
            box-shadow: 0 10px 22px rgba(212,175,55,.14);
        }
        body.page-baoCaoTaiChinh .finance-transaction-page .badge-thu,
        body.page-baoCaoTaiChinh .finance-transaction-page .badge-chi {
            border-radius: 8px;
            font-weight: 700;
            padding: 7px 12px;
        }
        @media (max-width: 992px) {
            body.page-baoCaoTaiChinh .content-area { padding: 24px 18px 44px; }
            body.page-baoCaoTaiChinh .finance-transaction-page .page-header-section h1 { font-size: 1.65rem !important; }
        }
    </style>

<div class="finance-transaction-page" style="padding: 20px; max-width: 1400px; margin: 0 auto;">
    <?php
    $exportQuery = $_GET;
    $exportQuery['act'] = 'admin/baoCaoTaiChinh/xuatBaoCao';
    $exportQuery['loai'] = 'giao_dich';
    $exportExcelUrl = 'index.php?' . http_build_query($exportQuery + ['format' => 'excel']);
    $exportPdfUrl = 'index.php?' . http_build_query($exportQuery + ['format' => 'pdf']);
    ?>
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-history" style="color: var(--accent-gold);"></i> Lịch Sử Giao Dịch Nội Bộ
        </h1>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:15px;">
            <a href="index.php?act=admin/baoCaoTaiChinh" style="background: var(--accent-gold); color: #000; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="<?= htmlspecialchars($exportExcelUrl) ?>" class="btn">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </a>
            <a href="<?= htmlspecialchars($exportPdfUrl) ?>" class="btn">
                <i class="fas fa-file-pdf"></i> Xuất PDF
            </a>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3 style="color: #10b981; font-size: 14px; margin-bottom: 10px;">TỔNG THU</h3>
            <div style="font-size: 28px; font-weight: 700; color: var(--text-light);">
                <?= number_format($tongThu) ?>đ
            </div>
        </div>
        <div class="stat-card">
            <h3 style="color: #ef4444; font-size: 14px; margin-bottom: 10px;">TỔNG CHI</h3>
            <div style="font-size: 28px; font-weight: 700; color: var(--text-light);">
                <?= number_format($tongChi) ?>đ
            </div>
        </div>
    </div>
    
    <div class="report-card">
            <table>
                <thead>
                    <tr>
                        <th>Ngày GD</th>
                        <th>Loại</th>
                        <th>Loại GD</th>
                        <th>Số Tiền</th>
                        <th>Mô Tả</th>
                        <th>Người Thực Hiện</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($giaoDichs)): ?>
                        <tr><td colspan="6" style="text-align: center; color: #999;">Chưa có giao dịch</td></tr>
                    <?php else: ?>
                        <?php foreach($giaoDichs as $gd): ?>
                            <tr data-href="index.php?act=admin/chiTietGiaoDich&id=<?= (int)$gd['id'] ?>">
                                <td><?= date('d/m/Y', strtotime($gd['ngay_giao_dich'])) ?></td>
                                <td>
                                    <span class="badge-<?= $gd['loai'] == 'Thu' ? 'thu' : 'chi' ?>">
                                        <?= $gd['loai'] ?>
                                    </span>
                                </td>
                                <td><?= $gd['loai_giao_dich'] ?></td>
                                <td style="font-weight: 700; color: <?= $gd['loai'] == 'Thu' ? '#10b981' : '#ef4444' ?>">
                                    <?= number_format($gd['so_tien']) ?>đ
                                </td>
                                <td><?= htmlspecialchars($gd['mo_ta'] ?? '') ?></td>
                                <td><?= htmlspecialchars($gd['nguoi_thuc_hien'] ?? 'N/A') ?></td>
                                <td>
                                    <a class="btn" href="index.php?act=admin/chiTietGiaoDich&id=<?= $gd['id'] ?>">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>

    <?php if (!empty($pagination) && ($pagination['totalPages'] ?? 1) > 1): ?>
        <?php
        $currentPageNumber = (int)($pagination['currentPage'] ?? 1);
        $totalPages = (int)($pagination['totalPages'] ?? 1);
        $query = $_GET;
        ?>
        <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin:20px 0 0; flex-wrap:wrap;">
            <div style="color:var(--text-muted); font-size:14px;">
                Trang <?= $currentPageNumber ?> / <?= $totalPages ?>
                (<?= number_format((int)($pagination['totalItems'] ?? 0)) ?> giao dịch)
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <?php if ($currentPageNumber > 1): ?>
                    <?php $query['page'] = $currentPageNumber - 1; ?>
                    <a class="btn" href="index.php?<?= htmlspecialchars(http_build_query($query)) ?>">Trang trước</a>
                <?php endif; ?>
                <?php if ($currentPageNumber < $totalPages): ?>
                    <?php $query['page'] = $currentPageNumber + 1; ?>
                    <a class="btn" href="index.php?<?= htmlspecialchars(http_build_query($query)) ?>">Trang sau</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>tr[data-href]{cursor:pointer;}tr[data-href]:hover td{background:rgba(255,255,255,0.04)!important;}</style>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
document.querySelectorAll('tr[data-href]').forEach(function(row){
    row.addEventListener('click',function(e){
        if(e.target.closest('a,button,form,input,select,textarea')) return;
        window.location.assign(row.dataset.href);
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
