<?php
$pageTitle = 'So sánh chi tiết dự toán & chi phí thực tế';
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
        .table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            padding: 15px;
            text-align: left;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .table tfoot th {
            background: rgba(45, 45, 45, 0.8);
            font-weight: 700;
        }
        .badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .bg-danger {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
        }
        .bg-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
        }
        .bg-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: #adb5bd;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        .btn-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(108, 117, 125, 0.5);
        }
        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.5);
        }
        .mt-4 {
            margin-top: 1.5rem;
        }
    </style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-bar-chart" style="color: var(--accent-gold);"></i> So sánh chi tiết dự toán & chi phí thực tế
        </h1>
    </div>
    
    <div class="report-card">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Hạng mục</th>
                    <th>Dự toán (VNĐ)</th>
                    <th>Thực tế (VNĐ)</th>
                    <th>Chênh lệch</th>
                    <th>Cảnh báo</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_du_toan = 0;
                $total_thuc_te = 0;
                if (!isset($soSanh) || !is_array($soSanh)) {
                    $soSanh = [];
                }
                foreach ($soSanh as $key => $item) {
                    $total_du_toan += $item['du_toan'];
                    $total_thuc_te += $item['thuc_te'];
                    $chenh_lech = $item['thuc_te'] - $item['du_toan'];
                    $canh_bao = '';
                    if ($item['du_toan'] > 0 && $chenh_lech > 0) {
                        $canh_bao = '<span class="badge bg-danger">Vượt dự toán</span>';
                    } elseif ($item['du_toan'] > 0 && $chenh_lech < 0) {
                        $canh_bao = '<span class="badge bg-success">Tiết kiệm</span>';
                    }
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($key) . '</td>';
                    echo '<td>' . number_format($item['du_toan']) . '</td>';
                    echo '<td>' . number_format($item['thuc_te']) . '</td>';
                    echo '<td>' . number_format($chenh_lech) . '</td>';
                    echo '<td>' . $canh_bao . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Tổng cộng</th>
                    <th><?= number_format($total_du_toan) ?></th>
                    <th><?= number_format($total_thuc_te) ?></th>
                    <th><?= number_format($total_thuc_te - $total_du_toan) ?></th>
                    <th>
                        <?php
                        if ($total_du_toan > 0 && $total_thuc_te > $total_du_toan) {
                            echo '<span class="badge bg-danger">Lỗ</span>';
                        } elseif ($total_du_toan > 0 && $total_thuc_te < $total_du_toan) {
                            echo '<span class="badge bg-success">Lãi</span>';
                        } else {
                            echo '<span class="badge bg-secondary">Đạt dự toán</span>';
                        }
                        ?>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="mt-4">
        <a href="?action=bao_cao_tai_chinh" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Quay lại</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
