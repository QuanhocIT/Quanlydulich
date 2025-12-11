<?php
$pageTitle = 'Quản lý hóa đơn công nợ HDV';
$currentPage = 'cong_no_hdv';
ob_start();
?>
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
        color: var(--text-light);
    }
    .table thead {
        background: rgba(45, 45, 45, 0.7);
    }
    .table thead th {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-light);
    }
    .table tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    .table tbody td {
        padding: 15px;
        color: var(--text-light);
    }
    .table-bordered {
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .form-control {
        background: rgba(30, 30, 30, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 8px 12px;
        border-radius: 4px;
    }
    .form-control:focus {
        background: rgba(30, 30, 30, 0.9);
        border-color: var(--accent-gold);
        outline: none;
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
    }
    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s;
    }
    .btn-success {
        background: rgba(40, 167, 69, 0.3);
        color: #5cb85c;
        border: 1px solid rgba(40, 167, 69, 0.5);
    }
    .btn-success:hover {
        background: rgba(40, 167, 69, 0.5);
    }
    .btn-danger {
        background: rgba(220, 53, 69, 0.3);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.5);
    }
    .btn-danger:hover {
        background: rgba(220, 53, 69, 0.5);
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.875rem;
    }
    .mb-1 {
        margin-bottom: 0.25rem;
    }
    .mt-4 {
        margin-top: 1.5rem;
    }
</style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-receipt" style="color: var(--accent-gold);"></i> Quản lý hóa đơn công nợ HDV
        </h1>
    </div>
    
    <div style="background: rgba(45, 45, 45, 0.5); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 4px; padding: 25px; backdrop-filter: blur(10px);">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>HDV</th>
                    <th>Tour</th>
                    <th>Số tiền</th>
                    <th>Loại</th>
                    <th>Ảnh hóa đơn</th>
                    <th>Ghi chú</th>
                    <th>Trạng thái</th>
                    <th>Ngày gửi</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($hoaDons as $hd): ?>
                    <tr>
                        <td><?= htmlspecialchars($hd['ten_hdv']) ?></td>
                        <td><?= htmlspecialchars($hd['ten_tour']) ?></td>
                        <td><?= number_format($hd['so_tien']) ?>đ</td>
                        <td><?= $hd['loai_cong_no'] ?></td>
                        <td><?php if($hd['anh_hoa_don']): ?><a href="<?= $hd['anh_hoa_don'] ?>" target="_blank" style="color: var(--accent-gold);">Xem ảnh</a><?php endif; ?></td>
                        <td><?= htmlspecialchars($hd['ghi_chu']) ?></td>
                        <td><?= $hd['trang_thai'] ?></td>
                        <td><?= $hd['ngay_gui'] ?></td>
                        <td>
                            <a href="index.php?act=admin/duyetHoaDon&id=<?= $hd['id'] ?>" class="btn btn-success btn-sm">Duyệt</a>
                            <form method="POST" action="index.php?act=admin/tuChoiHoaDon" style="display:inline">
                                <input type="hidden" name="id" value="<?= $hd['id'] ?>">
                                <input type="text" name="ly_do" placeholder="Lý do từ chối" class="form-control mb-1" required style="width: 150px; display: inline-block;">
                                <button type="submit" class="btn btn-danger btn-sm">Từ chối</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
