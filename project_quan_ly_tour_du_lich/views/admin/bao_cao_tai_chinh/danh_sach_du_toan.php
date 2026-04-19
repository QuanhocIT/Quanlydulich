<?php
$pageTitle = 'Danh Sách Dự Toán Tour';
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
            transition: all 0.3s;
            font-weight: 500;
        }
        .btn:hover {
            transform: translateY(-2px);
            background: #ffd700;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        body.page-baoCaoTaiChinh .content-area {
            padding: 34px 48px 56px;
            background:
                radial-gradient(circle at 12% 8%, rgba(32, 178, 170, 0.08), transparent 28%),
                radial-gradient(circle at 84% 16%, rgba(212, 175, 55, 0.10), transparent 30%),
                linear-gradient(135deg, #131616 0%, #181b1c 48%, #111313 100%);
        }
        body.page-baoCaoTaiChinh .finance-estimate-page {
            max-width: 100% !important;
            padding: 0 !important;
        }
        body.page-baoCaoTaiChinh .finance-estimate-page .page-header-section {
            min-height: 150px;
            padding: 28px 34px !important;
            border: 1px solid rgba(212, 175, 55, 0.22);
            border-radius: 8px;
            background:
                linear-gradient(90deg, rgba(22,24,24,.94) 0%, rgba(34,34,25,.9) 54%, rgba(212,175,55,.48) 100%),
                url('<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg') center/cover;
            box-shadow: 0 22px 48px rgba(0,0,0,.26);
            overflow: hidden;
        }
        body.page-baoCaoTaiChinh .finance-estimate-page .page-header-section h1 {
            color: #ffe082 !important;
            font-size: 2.15rem !important;
            letter-spacing: 0 !important;
        }
        body.page-baoCaoTaiChinh .finance-estimate-page .report-card {
            background: rgba(28, 30, 31, .80);
            border: 1px solid rgba(212, 175, 55, .20);
            border-radius: 8px;
            box-shadow: 0 18px 38px rgba(0,0,0,.20);
            overflow-x: auto;
            padding: 30px;
        }
        body.page-baoCaoTaiChinh .finance-estimate-page table {
            min-width: 980px;
            margin-top: 0;
        }
        body.page-baoCaoTaiChinh .finance-estimate-page th {
            background: linear-gradient(90deg, rgba(212,175,55,.16), rgba(255,255,255,.04));
            color: #d4af37;
            padding: 18px 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: .84rem;
        }
        body.page-baoCaoTaiChinh .finance-estimate-page td {
            padding: 20px 16px;
            vertical-align: middle;
        }
        body.page-baoCaoTaiChinh .finance-estimate-page .btn,
        body.page-baoCaoTaiChinh .finance-estimate-page a[style*="accent-gold"] {
            min-height: 46px;
            border-radius: 8px !important;
            font-weight: 700 !important;
            box-shadow: 0 10px 22px rgba(212,175,55,.14);
        }
        body.page-baoCaoTaiChinh .finance-estimate-page .btn-sm {
            min-height: 40px;
            padding: 8px 14px;
        }
        @media (max-width: 992px) {
            body.page-baoCaoTaiChinh .content-area { padding: 24px 18px 44px; }
            body.page-baoCaoTaiChinh .finance-estimate-page .page-header-section h1 { font-size: 1.65rem !important; }
        }
    </style>

<div class="finance-estimate-page" style="padding: 20px; max-width: 1400px; margin: 0 auto;">
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-calculator" style="color: var(--accent-gold);"></i> Danh Sách Dự Toán Tour
        </h1>
        <a href="index.php?act=admin/formDuToan" style="background: var(--accent-gold); color: #000; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
            <i class="fas fa-plus"></i> Tạo Dự Toán Mới
        </a>
    </div>
    
    <div class="report-card">
            <table>
                <thead>
                    <tr>
                        <th>Tour</th>
                        <th>Tổng Dự Toán</th>
                        <th>Người Tạo</th>
                        <th>Ngày Tạo</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($duToans)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #999;">Chưa có dự toán nào</td></tr>
                    <?php else: ?>
                        <?php foreach($duToans as $dt): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($dt['ten_tour']) ?></strong></td>
                                <td style="font-weight: 700; color: #667eea;">
                                    <?= number_format($dt['tong_du_toan']) ?>đ
                                </td>
                                <td><?= htmlspecialchars($dt['nguoi_tao'] ?? 'N/A') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($dt['ngay_tao'])) ?></td>
                                <td>
                                    <a href="index.php?act=admin/formDuToan&id=<?= $dt['du_toan_id'] ?>" class="btn btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?act=admin/soSanhDuToan&du_toan_id=<?= $dt['du_toan_id'] ?>" class="btn btn-sm">
                                        <i class="fas fa-chart-bar"></i> So sánh
                                    </a>
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
