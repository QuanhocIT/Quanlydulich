<?php
$pageTitle = 'Danh sách khách của tour';
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
        .text-center {
            text-align: center;
        }
        .mt-3 {
            margin-top: 1rem;
        }
    </style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-people" style="color: var(--accent-gold);"></i> Danh sách khách của tour
        </h1>
    </div>
    
    <div class="report-card">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>
                    <th>Ngày đặt</th>
                    <th>Số người</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bookings)): foreach ($bookings as $i => $bk): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($bk['ho_ten'] ?? '') ?></td>
                        <td><?= htmlspecialchars($bk['ngay_dat'] ?? '') ?></td>
                        <td><?= htmlspecialchars($bk['so_nguoi'] ?? '') ?></td>
                        <td><?= number_format($bk['tong_tien'] ?? 0) ?> đ</td>
                        <td><?= htmlspecialchars($bk['trang_thai'] ?? '') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center">Không có khách nào!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="index.php?act=admin/thuChiTour&tour_id=<?= $_GET['tour_id'] ?>" class="btn btn-secondary mt-3">Quay lại chi tiết tour</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>