<?php
$pageTitle = 'Báo Cáo Lãi Lỗ Từng Tour';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
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
        .profit {
            color: #10b981;
            font-weight: 700;
        }
        .loss {
            color: #ef4444;
            font-weight: 700;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        .btn-primary {
            background: var(--accent-gold);
            color: #000;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            background: #ffd700;
        }
        body.page-baoCaoTaiChinh .content-area {
            padding: 34px 48px 56px;
            background:
                radial-gradient(circle at 12% 8%, rgba(32, 178, 170, 0.08), transparent 28%),
                radial-gradient(circle at 84% 16%, rgba(212, 175, 55, 0.10), transparent 30%),
                linear-gradient(135deg, #131616 0%, #181b1c 48%, #111313 100%);
        }
        body.page-baoCaoTaiChinh .finance-profit-page {
            max-width: 100% !important;
            padding: 0 !important;
        }
        body.page-baoCaoTaiChinh .finance-profit-page .page-header-section {
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
        }
        body.page-baoCaoTaiChinh .finance-profit-page .page-header-section h1 {
            color: #ffe082 !important;
            font-size: 2.15rem !important;
            letter-spacing: 0 !important;
        }
        body.page-baoCaoTaiChinh .finance-profit-page .report-card {
            background: rgba(28, 30, 31, .80);
            border: 1px solid rgba(212, 175, 55, .20);
            border-radius: 8px;
            box-shadow: 0 18px 38px rgba(0,0,0,.20);
            overflow-x: auto;
            padding: 30px;
        }
        body.page-baoCaoTaiChinh .finance-profit-page table {
            min-width: 980px;
            margin-top: 0;
        }
        body.page-baoCaoTaiChinh .finance-profit-page th {
            background: linear-gradient(90deg, rgba(212,175,55,.16), rgba(255,255,255,.04));
            color: #d4af37;
            padding: 18px 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: .84rem;
        }
        body.page-baoCaoTaiChinh .finance-profit-page td {
            padding: 20px 16px;
            vertical-align: middle;
        }
        body.page-baoCaoTaiChinh .finance-profit-page .btn,
        body.page-baoCaoTaiChinh .finance-profit-page a[style*="accent-gold"] {
            min-height: 46px;
            border-radius: 8px !important;
            font-weight: 700 !important;
            box-shadow: 0 10px 22px rgba(212,175,55,.14);
        }
        body.page-baoCaoTaiChinh .finance-profit-page .profit {
            color: #20c997;
        }
        @media (max-width: 992px) {
            body.page-baoCaoTaiChinh .content-area { padding: 24px 18px 44px; }
            body.page-baoCaoTaiChinh .finance-profit-page .page-header-section h1 { font-size: 1.65rem !important; }
        }
    </style>

<div class="finance-profit-page" style="padding: 20px; max-width: 1400px; margin: 0 auto;">
    <?php
    $exportBaseQuery = [
        'act' => 'admin/baoCaoTaiChinh/xuatBaoCao',
        'loai' => 'lai_lo_tour',
        'tu_ngay' => $tuNgay ?? '',
        'den_ngay' => $denNgay ?? '',
    ];
    $exportExcelUrl = 'index.php?' . http_build_query($exportBaseQuery + ['format' => 'excel']);
    $exportPdfUrl = 'index.php?' . http_build_query($exportBaseQuery + ['format' => 'pdf']);
    ?>
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-chart-line" style="color: var(--accent-gold);"></i> Báo Cáo Lãi Lỗ Từng Tour
        </h1>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:15px;">
            <a href="index.php?act=admin/baoCaoTaiChinh" style="background: var(--accent-gold); color: #000; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="<?= htmlspecialchars($exportExcelUrl) ?>" class="btn btn-primary">
                <i class="fas fa-file-excel"></i> Xuất Excel
            </a>
            <a href="<?= htmlspecialchars($exportPdfUrl) ?>" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Xuất PDF
            </a>
        </div>
    </div>

    <form method="get" action="index.php" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-bottom:20px;">
        <input type="hidden" name="act" value="admin/laiLoTour">
        <div>
            <label for="tu_ngay" style="display:block;color:var(--text-muted);margin-bottom:6px;">Từ ngày</label>
            <input id="tu_ngay" name="tu_ngay" type="date" value="<?= htmlspecialchars($tuNgay ?? '') ?>" style="padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(45,45,45,.5);color:var(--text-light);">
        </div>
        <div>
            <label for="den_ngay" style="display:block;color:var(--text-muted);margin-bottom:6px;">Đến ngày</label>
            <input id="den_ngay" name="den_ngay" type="date" value="<?= htmlspecialchars($denNgay ?? '') ?>" style="padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:rgba(45,45,45,.5);color:var(--text-light);">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
    </form>
    
    <div class="report-card">
            <table>
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Doanh Thu</th>
                        <th>Chi Phí</th>
                        <th>Lợi Nhuận</th>
                        <th>Tỷ Suất (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($baoCao)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #999;">Chưa có dữ liệu</td></tr>
                    <?php else: ?>
                        <?php foreach($baoCao as $item): ?>
                            <tr data-href="index.php?act=admin/giaoDichTheoTour&tour_id=<?= (int)($item['tour']['tour_id'] ?? 0) ?>">
                                <td><strong><?= htmlspecialchars($item['tour']['ten_tour']) ?></strong></td>
                                <td><?= number_format($item['doanh_thu']) ?>đ</td>
                                <td><?= number_format($item['chi_phi']) ?>đ</td>
                                <td class="<?= $item['loi_nhuan'] >= 0 ? 'profit' : 'loss' ?>">
                                    <?= number_format($item['loi_nhuan']) ?>đ
                                </td>
                                <td class="<?= $item['ty_suat'] >= 0 ? 'profit' : 'loss' ?>">
                                    <?= number_format($item['ty_suat'], 2) ?>%
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
    </div>
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
