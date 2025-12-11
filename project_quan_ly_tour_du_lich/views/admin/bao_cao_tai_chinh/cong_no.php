<?php
$pageTitle = 'Quản Lý Công Nợ';
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
            font-weight: 500;
        }
        .btn:hover {
            background: #ffd700;
        }
    </style>

<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-file-invoice-dollar" style="color: var(--accent-gold);"></i> Quản Lý Công Nợ
        </h1>
    </div>
            <a href="index.php?act=admin/baoCaoTaiChinh" class="btn" style="margin-top: 15px;">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
        
    <div class="report-card">
        <h2 style="color: var(--text-light); margin-bottom: 15px;">Công Nợ Khách Hàng</h2>
        <table>
            <thead>
                <tr>
                    <th>Khách Hàng</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Tổng Booking</th>
                    <th>Đã Thanh Toán</th>
                    <th>Còn Nợ</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($congNoKhachHang)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Không có công nợ</td></tr>
                <?php else: ?>
                    <?php foreach($congNoKhachHang as $cn): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cn['ho_ten'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($cn['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($cn['so_dien_thoai'] ?? '') ?></td>
                            <td><?= number_format($cn['tong_gia_tri_booking']) ?>đ</td>
                            <td><?= number_format($cn['da_thanh_toan']) ?>đ</td>
                            <td style="color: #ef4444; font-weight: 700;">
                                <?= number_format($cn['con_no']) ?>đ
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
