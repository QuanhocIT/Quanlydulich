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
    </style>

<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
    <?php
    $exportExcelUrl = 'index.php?' . http_build_query(['act' => 'admin/baoCaoTaiChinh/xuatBaoCao', 'loai' => 'lai_lo_tour', 'format' => 'excel']);
    $exportPdfUrl = 'index.php?' . http_build_query(['act' => 'admin/baoCaoTaiChinh/xuatBaoCao', 'loai' => 'lai_lo_tour', 'format' => 'pdf']);
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
                            <tr>
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
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
