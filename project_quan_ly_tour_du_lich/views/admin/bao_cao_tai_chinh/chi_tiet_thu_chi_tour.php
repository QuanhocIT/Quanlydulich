<?php
$pageTitle = 'Chi tiết thu – chi tour';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
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
            margin-bottom: 30px;
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
        .text-success {
            color: #5cb85c !important;
        }
        .text-danger {
            color: #dc3545 !important;
        }
        .fw-bold {
            font-weight: 700;
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
        h4 {
            color: var(--text-light);
            margin-bottom: 15px;
        }
    </style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-cash-coin" style="color: var(--accent-gold);"></i> Chi tiết thu – chi tour: <span style="color: var(--accent-gold);"><?= htmlspecialchars($tour['ten_tour'] ?? '') ?></span>
        </h1>
    </div>
    
    <div class="stats-row">
        <div class="stat-box">
            <strong style="color: var(--text-muted); display: block; margin-bottom: 8px;">Tổng thu:</strong>
            <span class="text-success fw-bold" style="font-size: 1.2rem;"><?= number_format($tongThu ?? 0) ?> đ</span>
        </div>
        <div class="stat-box">
            <strong style="color: var(--text-muted); display: block; margin-bottom: 8px;">Chi phí giao dịch:</strong>
            <span class="text-danger fw-bold" style="font-size: 1.2rem;"><?= number_format($tongChiGD ?? 0) ?> đ</span>
        </div>
        <div class="stat-box">
            <strong style="color: var(--text-muted); display: block; margin-bottom: 8px;">Chi phí thực tế:</strong>
            <span class="text-danger fw-bold" style="font-size: 1.2rem;"><?= number_format($tongChiThucTe ?? 0) ?> đ</span>
        </div>
        <div class="stat-box">
            <strong style="color: var(--text-muted); display: block; margin-bottom: 8px;">Lợi nhuận thực tế:</strong>
            <span class="fw-bold" style="font-size: 1.2rem; color: <?= ($loiNhuan ?? 0) >= 0 ? '#5cb85c' : '#dc3545' ?>;">
                <?= number_format($loiNhuan ?? 0) ?> đ
            </span>
        </div>
    </div>
    
    <div class="report-card">
        <h4>Danh sách giao dịch</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Ngày</th>
                    <th>Loại</th>
                    <th>Số tiền</th>
                    <th>Mô tả</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($giaoDichs)): foreach ($giaoDichs as $i => $gd): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($gd['ngay_giao_dich'] ?? '') ?></td>
                        <td><?= htmlspecialchars($gd['loai'] ?? '') ?></td>
                        <td><?= number_format($gd['so_tien'] ?? 0) ?> đ</td>
                        <td><?= htmlspecialchars($gd['mo_ta'] ?? '') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center">Không có giao dịch nào!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="report-card">
        <h4>Danh sách booking</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
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
                        <td><?= number_format($bk['tong_tien'] ?? 0) ?> đ</td>
                        <td><?= htmlspecialchars($bk['trang_thai'] ?? '') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center">Không có booking nào!</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <a href="index.php?act=admin/thuChiTour" class="btn btn-secondary mt-3">Quay lại danh sách tour</a>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>
