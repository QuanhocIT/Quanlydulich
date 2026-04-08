<?php
$pageTitle = 'Khiếu nại thanh toán';
$currentPage = 'payments';

ob_start();
?>
<div class="aventura-content">
    <style>
        .complaint-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .complaint-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(160px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .complaint-form .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .complaint-form label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }
        .complaint-form input,
        .complaint-form select {
            border: 1px solid var(--border-color);
            background: rgba(255,255,255,.02);
            color: var(--text-color);
            border-radius: 10px;
            padding: 8px 10px;
        }
        .complaint-actions {
            display: flex;
            gap: 8px;
            align-items: end;
            flex-wrap: wrap;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .summary-card {
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 10px;
        }
        .summary-card .label {
            color: var(--text-muted);
            font-size: 12px;
            margin-bottom: 4px;
        }
        .summary-card .value {
            font-size: 20px;
            font-weight: 700;
        }
        .value.warn { color: #f59e0b; }

        .note-box {
            max-width: 520px;
            white-space: pre-wrap;
            word-break: break-word;
            color: var(--text-light);
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 900px) {
            .complaint-form { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="aventura-header">
        <h1 class="aventura-title"><i class="bi bi-exclamation-diamond"></i> Khiếu nại thanh toán</h1>
    </div>

    <div class="complaint-tools">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="index.php?act=admin/paymentReconcile" class="aventura-btn aventura-btn-outline"><i class="bi bi-arrow-left"></i> Quay lại đối soát</a>
            <a href="index.php?act=admin/payments" class="aventura-btn aventura-btn-outline"><i class="bi bi-list"></i> Danh sách thanh toán</a>
        </div>
    </div>

    <form method="GET" action="index.php" class="complaint-form">
        <input type="hidden" name="act" value="admin/paymentComplaints">
        <div class="field">
            <label>Trạng thái</label>
            <select name="trang_thai">
                <option value="">Tất cả</option>
                <option value="DaGui" <?php echo (($filters['trang_thai'] ?? '') === 'DaGui') ? 'selected' : ''; ?>>Đã gửi</option>
                <option value="ChuaGui" <?php echo (($filters['trang_thai'] ?? '') === 'ChuaGui') ? 'selected' : ''; ?>>Chưa gửi</option>
                <option value="Loi" <?php echo (($filters['trang_thai'] ?? '') === 'Loi') ? 'selected' : ''; ?>>Lỗi</option>
            </select>
        </div>
        <div class="field">
            <label>Tìm kiếm</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars((string)($filters['search'] ?? '')); ?>" placeholder="Tên KH / SĐT / booking / payment...">
        </div>
        <div class="complaint-actions">
            <button type="submit" class="aventura-btn aventura-btn-gold"><i class="bi bi-funnel"></i> Lọc</button>
            <a href="index.php?act=admin/paymentComplaints" class="aventura-btn aventura-btn-outline"><i class="bi bi-arrow-counterclockwise"></i> Xóa lọc</a>
        </div>
    </form>

    <div class="summary-grid">
        <div class="summary-card"><div class="label">Tổng khiếu nại</div><div class="value"><?php echo (int)$totalComplaints; ?></div></div>
        <div class="summary-card"><div class="label">Chờ xử lý</div><div class="value warn"><?php echo (int)$pendingComplaints; ?></div></div>
    </div>

    <div class="aventura-table-wrapper">
        <table class="aventura-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Tiêu đề</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Nội dung tóm tắt</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($complaints)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted)">Chưa có khiếu nại thanh toán.</td></tr>
                <?php else: ?>
                    <?php foreach ($complaints as $item): ?>
                        <tr>
                            <td>#<?php echo (int)($item['id'] ?? 0); ?></td>
                            <td>
                                <div style="font-weight:600;"><?php echo htmlspecialchars((string)($item['nguoi_gui_ten'] ?? 'N/A')); ?></div>
                                <div style="color:var(--text-muted);font-size:12px;"><?php echo htmlspecialchars((string)($item['nguoi_gui_phone'] ?? '')); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars((string)($item['tieu_de'] ?? '')); ?></td>
                            <td><?php echo !empty($item['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string)$item['created_at']))) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars((string)($item['trang_thai'] ?? 'N/A')); ?></td>
                            <td>
                                <div class="note-box"><?php echo htmlspecialchars(mb_substr((string)($item['noi_dung'] ?? ''), 0, 260)); ?><?php echo mb_strlen((string)($item['noi_dung'] ?? '')) > 260 ? '...' : ''; ?></div>
                            </td>
                            <td>
                                <a href="index.php?act=admin/chiTietYeuCauTour&id=<?php echo (int)($item['id'] ?? 0); ?>" class="aventura-btn-sm aventura-btn-outline"><i class="bi bi-eye"></i> Xem</a>
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