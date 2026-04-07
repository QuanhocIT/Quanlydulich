<?php
$pageTitle = $pageTitle ?? 'Chi tiết lương nhân sự';
$currentPage = $currentPage ?? 'luongThuong';

$month = $month ?? ($_GET['month'] ?? '');
$year = $year ?? ($_GET['year'] ?? '');
$showAll = $showAll ?? (($_GET['all'] ?? '') === '1');

$redirectHere = $_SERVER['REQUEST_URI'] ?? 'index.php?act=admin/quanLyLuongThuong';

ob_start();
?>
<style>
    .ctluong-wrap {
        max-width: 1200px;
        margin: 40px auto 0 auto;
        background: rgba(30,30,30,0.98);
        border-radius: 10px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        padding: 24px 26px;
        color: #fff;
    }
    .ctluong-title {
        text-align: center;
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 14px;
        color: var(--accent-gold, #ffd700);
    }
    .ctluong-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 18px;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
        color: #ddd;
        font-size: 14px;
    }
    .btn-gold {
        background: var(--accent-gold, #ffd700);
        color: #222;
        font-weight: bold;
        border: none;
        border-radius: 4px;
        padding: 8px 14px;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
    }
    .btn-gold:hover { background: #e6c200; }
    .btn-outline {
        background: #333;
        color: #fff;
        border: 1px solid #444;
        border-radius: 4px;
        padding: 8px 14px;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
    }
    .ctluong-table {
        background: #181818;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.12);
    }
    .ctluong-table th, .ctluong-table td {
        padding: 10px 8px;
        text-align: center;
        vertical-align: middle;
        border-bottom: 1px solid #292929;
    }
    .ctluong-table th {
        background: #222;
        color: var(--accent-gold, #ffd700);
        font-weight: 700;
        font-size: 14px;
    }
    .inline-form {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        align-items: center;
    }
    .inline-form input, .inline-form select {
        border-radius: 4px;
        border: 1px solid #444;
        background: #222;
        color: #fff;
        padding: 6px 8px;
        font-size: 13px;
        min-width: 100px;
    }
    .inline-form input[type="number"] { width: 120px; }
    .inline-form input[name="ghi_chu"] { min-width: 160px; }
    @media (max-width: 900px) {
        .ctluong-wrap { padding: 16px 2vw; }
        .ctluong-title { font-size: 1.2rem; }
        .ctluong-table th, .ctluong-table td { padding: 6px 3px; font-size: 12px; }
        .inline-form input, .inline-form select { min-width: 120px; }
    }
</style>

<div class="ctluong-wrap">
    <div style="margin-bottom:10px;">
        <a class="btn-outline" href="index.php?act=admin/quanLyLuongThuong"><i class="bi bi-arrow-left me-1"></i>Quay lại</a>
    </div>

    <div class="ctluong-title">Chi tiết lương nhân sự</div>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="background:rgba(0,230,118,0.14); border:1px solid rgba(0,230,118,0.35); color:#b7ffda; padding:10px 12px; border-radius:6px; margin-bottom:12px;">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background:rgba(255,82,82,0.14); border:1px solid rgba(255,82,82,0.35); color:#ffd0d0; padding:10px 12px; border-radius:6px; margin-bottom:12px;">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="ctluong-meta">
        <div>
            <div><strong>Họ tên:</strong> <?php echo htmlspecialchars($nhanSu['ho_ten'] ?? ''); ?></div>
            <div><strong>Vai trò:</strong> <?php echo htmlspecialchars($nhanSu['vai_tro'] ?? ''); ?></div>
        </div>
        <div style="text-align:right;">
            <div><strong>Kỳ:</strong> <?php echo $showAll ? 'Tất cả' : ('Tháng ' . htmlspecialchars((string)$month) . '/' . htmlspecialchars((string)$year)); ?></div>
            <div><strong>Tổng:</strong> <?php echo number_format((float)($tongLuong ?? 0)); ?></div>
        </div>
    </div>

    <?php if (($nhanSu['vai_tro'] ?? '') === 'HDV' && !$showAll && !empty($month) && !empty($year)): ?>
        <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
            <form method="post" action="index.php?act=admin/capNhatLuongCoBan" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="nhan_su_id" value="<?php echo (int)($nhanSu['nhan_su_id'] ?? 0); ?>">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectHere); ?>">
                <span style="color:#bbb; font-size:13px;"><i class="bi bi-cash-stack me-1"></i>Lương cứng (tháng)</span>
                <input type="number" name="luong_co_ban" min="0" step="1000" value="<?php echo htmlspecialchars((string)($nhanSu['luong_co_ban'] ?? 0)); ?>" style="border-radius:4px; border:1px solid #444; background:#222; color:#fff; padding:6px 8px; width:160px;">
                <button type="submit" class="btn-outline">Lưu lương cứng</button>
            </form>
        </div>
        <div style="color:#aaa; font-size:12px; margin-top:-6px; margin-bottom:10px;">
            Tổng lương HDV trong tháng = lương cứng + tổng hoa hồng các tour dẫn trong tháng.
        </div>
    <?php endif; ?>

    <?php if (!$showAll && !empty($month) && !empty($year)): ?>
        <div style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap; margin-bottom:12px;">
            <form method="post" action="index.php?act=admin/tinhLaiLuongNhanSu" style="margin:0;">
                <input type="hidden" name="nhan_su_id" value="<?php echo (int)($nhanSu['nhan_su_id'] ?? 0); ?>">
                <input type="hidden" name="month" value="<?php echo (int)$month; ?>">
                <input type="hidden" name="year" value="<?php echo (int)$year; ?>">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectHere); ?>">
                <button class="btn-outline" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Tính lại hoa hồng</button>
            </form>
            <form method="post" action="index.php?act=admin/duyetLuongNhanSu" style="margin:0;">
                <input type="hidden" name="nhan_su_id" value="<?php echo (int)($nhanSu['nhan_su_id'] ?? 0); ?>">
                <input type="hidden" name="month" value="<?php echo (int)$month; ?>">
                <input type="hidden" name="year" value="<?php echo (int)$year; ?>">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectHere); ?>">
                <button class="btn-gold" type="submit"><i class="bi bi-check2-circle me-1"></i>Duyệt tháng này</button>
            </form>
            <form method="post" action="index.php?act=admin/thanhToanLuongNhanSu" style="margin:0;">
                <input type="hidden" name="nhan_su_id" value="<?php echo (int)($nhanSu['nhan_su_id'] ?? 0); ?>">
                <input type="hidden" name="month" value="<?php echo (int)$month; ?>">
                <input type="hidden" name="year" value="<?php echo (int)$year; ?>">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectHere); ?>">
                <button class="btn-gold" type="submit" style="background:#00e676;"><i class="bi bi-cash-coin me-1"></i>Đánh dấu đã thanh toán</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table ctluong-table">
            <thead>
                <tr>
                    <th>#Lịch</th>
                    <th>Tour</th>
                    <th>Ngày KH</th>
                    <th>Loại</th>
                    <th>Cố định</th>
                    <th>%</th>
                    <th>Hoa hồng</th>
                    <th>Tổng</th>
                    <th>Trạng thái</th>
                    <th>Cập nhật</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($luongChiTiet)): ?>
                    <?php foreach ($luongChiTiet as $row): ?>
                        <tr>
                            <td><?php echo (int)($row['lich_khoi_hanh_id'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($row['ten_tour'] ?? ''); ?></td>
                            <td><?php echo !empty($row['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($row['ngay_khoi_hanh'])) : ''; ?></td>
                            <td><?php echo htmlspecialchars($row['loai_luong'] ?? ''); ?></td>
                            <td><?php echo number_format((float)($row['so_tien_co_dinh'] ?? 0)); ?></td>
                            <td><?php echo htmlspecialchars((string)($row['phan_tram_hoa_hong'] ?? 0)); ?></td>
                            <td><?php echo number_format((float)($row['tien_hoa_hong'] ?? 0)); ?></td>
                            <td style="font-weight:800; color:#00e676;"><?php echo number_format((float)($row['tong_luong'] ?? 0)); ?></td>
                            <td><?php echo htmlspecialchars($row['trang_thai_luong'] ?? ''); ?></td>
                            <td>
                                <form class="inline-form" method="post" action="index.php?act=admin/capNhatLuongThuong">
                                    <input type="hidden" name="id" value="<?php echo (int)($row['id'] ?? 0); ?>">
                                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectHere); ?>">

                                    <?php $isPaid = (($row['trang_thai_luong'] ?? '') === 'DaThanhToan'); ?>
                                    <select name="loai_luong" <?php echo $isPaid ? 'disabled' : ''; ?>>
                                        <option value="CoDinh" <?php echo (($row['loai_luong'] ?? '') === 'CoDinh') ? 'selected' : ''; ?>>Cố định</option>
                                        <option value="PhanTram" <?php echo (($row['loai_luong'] ?? '') === 'PhanTram') ? 'selected' : ''; ?>>%</option>
                                        <option value="KetHop" <?php echo (($row['loai_luong'] ?? '') === 'KetHop') ? 'selected' : ''; ?>>Kết hợp</option>
                                    </select>
                                    <input type="number" name="so_tien_co_dinh" min="0" value="<?php echo htmlspecialchars((string)($row['so_tien_co_dinh'] ?? 0)); ?>" title="Lương cố định" <?php echo $isPaid ? 'disabled' : ''; ?>>
                                    <input type="number" name="phan_tram_hoa_hong" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars((string)($row['phan_tram_hoa_hong'] ?? 0)); ?>" title="% hoa hồng" <?php echo $isPaid ? 'disabled' : ''; ?>>
                                    <select name="trang_thai_luong" <?php echo $isPaid ? 'disabled' : ''; ?>>
                                        <option value="ChoDuyet" <?php echo (($row['trang_thai_luong'] ?? '') === 'ChoDuyet') ? 'selected' : ''; ?>>Chờ duyệt</option>
                                        <option value="DaDuyet" <?php echo (($row['trang_thai_luong'] ?? '') === 'DaDuyet') ? 'selected' : ''; ?>>Đã duyệt</option>
                                        <option value="DaThanhToan" <?php echo (($row['trang_thai_luong'] ?? '') === 'DaThanhToan') ? 'selected' : ''; ?>>Đã TT</option>
                                    </select>
                                    <input type="text" name="ghi_chu" maxlength="255" value="<?php echo htmlspecialchars((string)($row['ghi_chu'] ?? '')); ?>" placeholder="Ghi chú" <?php echo $isPaid ? 'disabled' : ''; ?>>
                                    <button type="submit" class="btn-gold" style="padding:7px 10px;" <?php echo $isPaid ? 'disabled' : ''; ?>>
                                        <?php echo $isPaid ? 'Đã TT' : 'Lưu'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
