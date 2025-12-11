<?php
$pageTitle = 'Thu Chi Từng Tour';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
        .report-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 25px;
            margin-bottom: 30px;
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
    </style>

<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-money-bill-wave" style="color: var(--accent-gold);"></i> Thu Chi Từng Tour
        </h1>
        <a href="index.php?act=admin/baoCaoTaiChinh" style="background: var(--accent-gold); color: #000; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-top: 15px; font-weight: 500;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <div class="report-card">
            <table>
                <thead>
                    <tr>
                        <th>Tên Tour</th>
                        <th>Tổng Thu</th>
                        <th>Chi phí thực tế</th>
                        <th>Dự toán</th>
                        <th>Lợi Nhuận</th>
                        <th>Trạng thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($tours)): ?>
                        <tr><td colspan="5" style="text-align: center; color: #999;">Chưa có tour nào</td></tr>
                    <?php else: ?>
                        <?php foreach($tours as $tour): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($tour['ten_tour']) ?></strong></td>
                                <td><?= number_format($tour['tong_thu']) ?>đ</td>
                                <td><?= number_format($tour['tong_chi_thuc_te']) ?>đ</td>
                                <td><?= number_format($tour['tong_du_toan']) ?>đ</td>
                                <td style="color: <?= $tour['loi_nhuan'] >= 0 ? '#10b981' : '#ef4444' ?>; font-weight: 700;">
                                    <?= number_format($tour['loi_nhuan']) ?>đ
                                </td>
                                <td>
                                    <?php if ($tour['status'] === 'VuotDuToan'): ?>
                                        <span title="Chi phí thực tế đã vượt dự toán!" style="color:#d9534f;font-weight:bold">
                                            <i class="fas fa-exclamation-triangle"></i> Vượt dự toán
                                        </span>
                                    <?php elseif ($tour['status'] === 'GanVuot'): ?>
                                        <span title="Chi phí thực tế đã đạt 90% dự toán!" style="color:#f0ad4e;font-weight:bold">
                                            <i class="fas fa-exclamation-circle"></i> Gần vượt
                                        </span>
                                    <?php else: ?>
                                        <span title="Chi phí trong mức an toàn" style="color:#10b981;font-weight:bold">
                                            <i class="fas fa-check-circle"></i> An toàn
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?act=admin/thuChiTour&tour_id=<?= $tour['tour_id'] ?>" class="btn" style="padding: 6px 12px; font-size: 13px;">
                                        <i class="fas fa-eye"></i> Chi tiết
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
