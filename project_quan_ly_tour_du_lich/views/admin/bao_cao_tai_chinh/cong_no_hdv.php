<?php
$pageTitle = 'Tổng quan công nợ HDV';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
        .report-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 32px;
            backdrop-filter: blur(10px);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .badge {
            font-size: 1rem;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
        }
        .badge-pos {
            background: rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        .badge-du {
            background: rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        .badge-no {
            background: rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }
        .badge-zero {
            background: rgba(107, 114, 128, 0.3);
            color: #adb5bd;
        }
        .mb-4 {
            margin-bottom: 1.5rem;
        }
    </style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-user-tie" style="color: var(--accent-gold);"></i> Tổng quan công nợ HDV
        </h1>
    </div>
    
    <div class="report-card">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>HDV</th>
                    <th>Tour</th>
                    <th>Tổng thu</th>
                    <th>Tổng chi</th>
                    <th>Công nợ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($congNoHDV as $row): ?>
                    <tr>
                        <td><i class="fas fa-user"></i> <?= htmlspecialchars($row['ten_hdv']) ?></td>
                        <td><i class="fas fa-route"></i> <?= htmlspecialchars($row['ten_tour']) ?></td>
                        <td><span class="badge badge-pos"><?= number_format($row['tong_thu']) ?>đ</span></td>
                        <td><span class="badge badge-zero"><?= number_format($row['tong_chi']) ?>đ</span></td>
                        <td>
                            <?php if($row['cong_no'] > 0): ?>
                                <span class="badge badge-du"><i class="fas fa-arrow-up"></i> <?= number_format($row['cong_no']) ?>đ</span>
                            <?php elseif($row['cong_no'] < 0): ?>
                                <span class="badge badge-no"><i class="fas fa-arrow-down"></i> <?= number_format($row['cong_no']) ?>đ</span>
                            <?php else: ?>
                                <span class="badge badge-zero"><i class="fas fa-minus"></i> 0đ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
