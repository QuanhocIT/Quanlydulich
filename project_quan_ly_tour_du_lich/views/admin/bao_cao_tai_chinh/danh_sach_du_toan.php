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
    </style>

<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
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
