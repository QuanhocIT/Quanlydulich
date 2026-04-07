<?php
$pageTitle = 'Tổng quan công nợ HDV';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
    .report-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 24px;
        backdrop-filter: blur(10px);
        margin-bottom: 16px;
    }
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(160px, 1fr));
        gap: 10px;
        align-items: end;
    }
    .filters-grid .field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .filters-grid label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
    }
    .filters-grid input,
    .filters-grid select {
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,.15);
        background: rgba(255,255,255,.03);
        color: var(--text-light);
        padding: 8px 10px;
    }
    .filters-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn {
        padding: 9px 14px;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--accent-gold);
        color: #000;
        font-weight: 600;
        cursor: pointer;
    }
    .btn:hover { background: #ffd700; }
    .btn-outline {
        background: transparent;
        color: var(--text-light);
        border: 1px solid rgba(255,255,255,.2);
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(120px, 1fr));
        gap: 10px;
    }
    .summary-box {
        background: rgba(255,255,255,.03);
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 10px;
        padding: 12px;
    }
    .summary-box .label {
        font-size: 12px;
        color: var(--text-muted);
    }
    .summary-box .value {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-light);
    }
    .summary-box .value.green { color: #10b981; }
    .summary-box .value.red { color: #ef4444; }

    .table {
        width: 100%;
        border-collapse: collapse;
        color: var(--text-light);
    }
    .table th {
        background: rgba(45, 45, 45, 0.7);
        color: var(--text-light);
        padding: 12px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .table td {
        padding: 12px;
        text-align: center;
        vertical-align: middle;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-light);
    }
    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .badge {
        font-size: .9rem;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-green { background: rgba(16, 185, 129, 0.2); color: #10b981; }
    .badge-red { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .badge-gold { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .badge-gray { background: rgba(107, 114, 128, 0.25); color: #d1d5db; }
    details {
        margin-top: 6px;
        text-align: left;
    }
    .pay-form {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
        margin-top: 8px;
    }
    .pay-form input,
    .pay-form select {
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,.2);
        background: rgba(255,255,255,.03);
        color: var(--text-light);
        padding: 6px 8px;
    }
    .pay-form button {
        grid-column: 1 / -1;
        justify-self: start;
    }
    .flash {
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 12px;
        font-size: 14px;
    }
    .flash-success { background: rgba(16,185,129,.15); border: 1px solid rgba(16,185,129,.45); }
    .flash-error { background: rgba(239,68,68,.15); border: 1px solid rgba(239,68,68,.45); }
    @media (max-width: 1200px) {
        .filters-grid { grid-template-columns: repeat(3, minmax(140px, 1fr)); }
        .summary-grid { grid-template-columns: repeat(3, minmax(120px, 1fr)); }
    }
    @media (max-width: 768px) {
        .filters-grid { grid-template-columns: 1fr; }
        .summary-grid { grid-template-columns: repeat(2, minmax(110px, 1fr)); }
        .pay-form { grid-template-columns: 1fr; }
    }
    </style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-user-tie" style="color: var(--accent-gold);"></i> Tổng quan công nợ HDV
        </h1>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="flash flash-success"><?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="flash flash-error"><?php echo htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="report-card">
        <form method="GET" action="index.php" class="filters-grid">
            <input type="hidden" name="act" value="admin/congNo">
            <div class="field">
                <label>HDV</label>
                <select name="hdv_id">
                    <option value="0">Tất cả HDV</option>
                    <?php foreach (($hdvOptions ?? []) as $opt): ?>
                        <option value="<?php echo (int)$opt['hdv_id']; ?>" <?php echo ((int)($filters['hdv_id'] ?? 0) === (int)$opt['hdv_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string)$opt['ten_hdv']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Tour</label>
                <select name="tour_id">
                    <option value="0">Tất cả tour</option>
                    <?php foreach (($tourOptions ?? []) as $opt): ?>
                        <option value="<?php echo (int)$opt['tour_id']; ?>" <?php echo ((int)($filters['tour_id'] ?? 0) === (int)$opt['tour_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars((string)$opt['ten_tour']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Trạng thái</label>
                <select name="status">
                    <option value="">Tất cả</option>
                    <option value="ConNo" <?php echo (($filters['status'] ?? '') === 'ConNo') ? 'selected' : ''; ?>>Còn nợ</option>
                    <option value="QuaHan" <?php echo (($filters['status'] ?? '') === 'QuaHan') ? 'selected' : ''; ?>>Quá hạn</option>
                    <option value="DaThanhToan" <?php echo (($filters['status'] ?? '') === 'DaThanhToan') ? 'selected' : ''; ?>>Đã thanh toán</option>
                </select>
            </div>
            <div class="field">
                <label>Tìm kiếm</label>
                <input type="text" name="keyword" value="<?php echo htmlspecialchars((string)($filters['keyword'] ?? '')); ?>" placeholder="Tên HDV / tour / ghi chú...">
            </div>
            <div class="filters-actions">
                <button type="submit" class="btn"><i class="fas fa-filter"></i> Lọc</button>
                <a class="btn btn-outline" href="index.php?act=admin/congNo"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
                <a class="btn btn-outline" href="index.php?act=admin/congNo&export=csv&hdv_id=<?= (int)($filters['hdv_id'] ?? 0) ?>&tour_id=<?= (int)($filters['tour_id'] ?? 0) ?>&status=<?= urlencode((string)($filters['status'] ?? '')) ?>&keyword=<?= urlencode((string)($filters['keyword'] ?? '')) ?>"><i class="fas fa-file-csv"></i> Xuất CSV</a>
            </div>
        </form>
    </div>

    <div class="report-card">
        <div class="summary-grid">
            <div class="summary-box">
                <div class="label">Số hồ sơ công nợ</div>
                <div class="value"><?php echo (int)($summary['so_cong_no'] ?? 0); ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Tổng công nợ gốc</div>
                <div class="value"><?php echo number_format((float)($summary['tong_goc'] ?? 0)); ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Đã thanh toán</div>
                <div class="value green"><?php echo number_format((float)($summary['tong_da_thanh_toan'] ?? 0)); ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Còn lại</div>
                <div class="value red"><?php echo number_format((float)($summary['tong_con_lai'] ?? 0)); ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Số khoản quá hạn</div>
                <div class="value red"><?php echo (int)($summary['so_qua_han'] ?? 0); ?></div>
            </div>
            <div class="summary-box">
                <div class="label">Khoản đã tất toán</div>
                <div class="value green"><?php echo (int)($summary['so_da_thanh_toan'] ?? 0); ?></div>
            </div>
        </div>
    </div>
    
    <div class="report-card">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>HDV</th>
                    <th>Tour</th>
                    <th>Loại</th>
                    <th>Gốc</th>
                    <th>Đã thanh toán</th>
                    <th>Còn lại</th>
                    <th>Hạn thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Lịch sử / Thanh toán</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($congNoHDV)): ?>
                    <tr><td colspan="10" style="text-align:center;color:var(--text-muted)">Không có dữ liệu công nợ phù hợp bộ lọc.</td></tr>
                <?php else: ?>
                <?php foreach($congNoHDV as $row): ?>
                    <tr>
                        <td>#<?= (int)$row['id'] ?></td>
                        <td><i class="fas fa-user"></i> <?= htmlspecialchars((string)$row['ten_hdv']) ?></td>
                        <td><i class="fas fa-route"></i> <?= htmlspecialchars((string)$row['ten_tour']) ?></td>
                        <td><span class="badge badge-gray"><?= htmlspecialchars((string)$row['loai_cong_no']) ?></span></td>
                        <td><span class="badge badge-gray"><?= number_format((float)$row['tong_goc']) ?>đ</span></td>
                        <td><span class="badge badge-green"><?= number_format((float)$row['tong_da_thanh_toan']) ?>đ</span></td>
                        <td>
                            <?php if((float)$row['con_lai'] > 0): ?>
                                <span class="badge badge-red"><i class="fas fa-arrow-up"></i> <?= number_format((float)$row['con_lai']) ?>đ</span>
                            <?php else: ?>
                                <span class="badge badge-green"><i class="fas fa-check"></i> 0đ</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['han_thanh_toan'])): ?>
                                <?= htmlspecialchars(date('d/m/Y', strtotime((string)$row['han_thanh_toan']))) ?>
                            <?php else: ?>
                                <span style="color:var(--text-muted)">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($row['trang_thai_hien_thi'] ?? '') === 'DaThanhToan'): ?>
                                <span class="badge badge-green"><i class="fas fa-check-circle"></i> Đã thanh toán</span>
                            <?php elseif (($row['trang_thai_hien_thi'] ?? '') === 'QuaHan'): ?>
                                <span class="badge badge-red"><i class="fas fa-circle-exclamation"></i> Quá hạn</span>
                            <?php else: ?>
                                <span class="badge badge-gold"><i class="fas fa-hourglass-half"></i> Còn nợ</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:left; min-width: 320px;">
                            <details>
                                <summary style="cursor:pointer;">Xem lịch sử (<?= (int)($row['so_lan_thanh_toan'] ?? 0) ?> lần)</summary>
                                <?php if (empty($row['lich_su_thanh_toan'])): ?>
                                    <div style="margin-top:8px;color:var(--text-muted);">Chưa có lịch sử thanh toán.</div>
                                <?php else: ?>
                                    <ul style="margin:8px 0 0 16px;padding:0;">
                                        <?php foreach ($row['lich_su_thanh_toan'] as $ls): ?>
                                            <li style="margin-bottom:4px;">
                                                <?= htmlspecialchars(date('d/m/Y', strtotime((string)$ls['ngay_thanh_toan']))) ?> -
                                                <?= number_format((float)$ls['so_tien']) ?>đ -
                                                <?= htmlspecialchars((string)$ls['phuong_thuc']) ?>
                                                <?php if (!empty($ls['ghi_chu'])): ?>
                                                    (<?= htmlspecialchars((string)$ls['ghi_chu']) ?>)
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </details>

                            <?php if ((float)$row['con_lai'] > 0): ?>
                                <form method="POST" action="index.php?act=admin/congNo&hdv_id=<?= (int)($filters['hdv_id'] ?? 0) ?>&tour_id=<?= (int)($filters['tour_id'] ?? 0) ?>&status=<?= urlencode((string)($filters['status'] ?? '')) ?>&keyword=<?= urlencode((string)($filters['keyword'] ?? '')) ?>" class="pay-form">
                                    <input type="hidden" name="cong_no_hdv_id" value="<?= (int)$row['id'] ?>">
                                    <input type="date" name="ngay_thanh_toan" value="<?= date('Y-m-d') ?>" required>
                                    <input type="number" name="so_tien_thanh_toan" min="1" max="<?= (int)max(1, floor((float)$row['con_lai'])) ?>" step="1" placeholder="Số tiền" required>
                                    <select name="phuong_thuc">
                                        <option value="ChuyenKhoan">Chuyển khoản</option>
                                        <option value="TienMat">Tiền mặt</option>
                                        <option value="Khac">Khác</option>
                                    </select>
                                    <input type="text" name="ghi_chu" placeholder="Ghi chú (tuỳ chọn)">
                                    <button type="submit" class="btn" onclick="return confirm('Xác nhận ghi nhận thanh toán cho công nợ #<?= (int)$row['id'] ?>?');">
                                        <i class="fas fa-money-bill"></i> Ghi nhận thanh toán
                                    </button>
                                </form>
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
