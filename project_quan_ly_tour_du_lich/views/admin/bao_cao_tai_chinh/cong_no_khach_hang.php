<?php
$pageTitle = 'Cong no khach hang';
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
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        color: var(--text-light);
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        vertical-align: top;
    }
    th {
        background: rgba(45, 45, 45, 0.7);
        color: var(--text-light);
        font-weight: 600;
    }
    tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .debt-value {
        color: #ef4444;
        font-weight: 700;
    }
    .history-item {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
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
</style>

<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-user-friends" style="color: var(--accent-gold);"></i> Cong no khach hang
        </h1>
        <a href="index.php?act=admin/congNo" class="btn">
            <i class="fas fa-arrow-left"></i> Quay lai cong no HDV
        </a>
    </div>

    <div class="report-card">
        <table>
            <thead>
                <tr>
                    <th>Khach hang</th>
                    <th>Email</th>
                    <th>So dien thoai</th>
                    <th>Tour</th>
                    <th>Cong no</th>
                    <th>Lich su thanh toan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($congNoKhachHang)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#999;">Khong co cong no nao</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($congNoKhachHang as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['ten_khach_hang'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['so_dien_thoai'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['ten_tour'] ?? 'N/A') ?></td>
                            <td class="debt-value"><?= number_format((float)($row['cong_no'] ?? 0)) ?>đ</td>
                            <td>
                                <?php if (!empty($row['lich_su_thanh_toan'])): ?>
                                    <?php foreach ($row['lich_su_thanh_toan'] as $ls): ?>
                                        <div class="history-item">
                                            <?= date('d/m/Y', strtotime((string)($ls['ngay'] ?? 'now'))) ?>: <?= number_format((float)($ls['so_tien'] ?? 0)) ?>đ
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">Chua thanh toan</span>
                                <?php endif; ?>
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