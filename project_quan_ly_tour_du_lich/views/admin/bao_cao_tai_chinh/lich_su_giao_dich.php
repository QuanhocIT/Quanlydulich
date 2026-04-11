<?php
$pageTitle = 'Lịch Sử Giao Dịch';
$currentPage = 'baoCaoTaiChinh';
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
    </style>

<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
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
                            <tr>
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
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
