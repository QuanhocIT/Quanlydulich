<?php
$month = $month ?? ($_GET['month'] ?? '');
$year = $year ?? ($_GET['year'] ?? '');
$showAll = $showAll ?? (($_GET['all'] ?? '') === '1');

$nhanSuId = (int)($nhanSu['nhan_su_id'] ?? ($_GET['nhan_su_id'] ?? 0));
$detailHref = 'index.php?act=admin/chiTietLuong&nhan_su_id=' . $nhanSuId;
if ($showAll) {
    $detailHref .= '&all=1';
} else {
    $detailHref .= '&month=' . urlencode((string)$month) . '&year=' . urlencode((string)$year);
}
?>

<div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
    <div>
        <div style="font-size:16px; font-weight:800; color:#ffd700; margin-bottom:4px;">
            <?php echo htmlspecialchars($nhanSu['ho_ten'] ?? ''); ?>
        </div>
        <div style="color:#bbb; font-size:13px;">
            <?php echo $showAll ? 'Tất cả kỳ' : ('Tháng ' . htmlspecialchars((string)$month) . '/' . htmlspecialchars((string)$year)); ?>
        </div>
    </div>
    <div style="text-align:right;">
        <div style="color:#bbb; font-size:13px;">Tổng lương</div>
        <div style="font-size:18px; font-weight:900; color:#00e676;"><?php echo number_format((float)($tongLuong ?? 0)); ?></div>
    </div>
</div>

<div style="margin-top:10px;">
    <a href="<?php echo htmlspecialchars($detailHref); ?>" style="display:inline-block; padding:6px 10px; border-radius:6px; background:#ffd700; color:#222; font-weight:800; text-decoration:none;">
        <i class="bi bi-box-arrow-up-right me-1"></i>Mở trang chi tiết
    </a>
</div>

<div style="margin-top:12px; max-height:55vh; overflow:auto; border:1px solid #333; border-radius:8px;">
    <table style="width:100%; border-collapse:collapse; color:#fff;">
        <thead>
            <tr style="background:#1d1d1d; position:sticky; top:0;">
                <th style="padding:10px 8px; text-align:left; color:#ffd700; border-bottom:1px solid #333;">Tour</th>
                <th style="padding:10px 8px; text-align:center; color:#ffd700; border-bottom:1px solid #333;">Ngày KH</th>
                <th style="padding:10px 8px; text-align:right; color:#ffd700; border-bottom:1px solid #333;">Tổng</th>
                <th style="padding:10px 8px; text-align:center; color:#ffd700; border-bottom:1px solid #333;">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($luongChiTiet)): ?>
            <?php foreach ($luongChiTiet as $row): ?>
                <tr style="border-bottom:1px solid #292929;">
                    <td style="padding:10px 8px; text-align:left;"><?php echo htmlspecialchars($row['ten_tour'] ?? ''); ?></td>
                    <td style="padding:10px 8px; text-align:center;"><?php echo !empty($row['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($row['ngay_khoi_hanh'])) : ''; ?></td>
                    <td style="padding:10px 8px; text-align:right; font-weight:800; color:#00e676;"><?php echo number_format((float)($row['tong_luong'] ?? 0)); ?></td>
                    <td style="padding:10px 8px; text-align:center;"><?php echo htmlspecialchars($row['trang_thai_luong'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4" style="padding:14px 8px; color:#aaa; text-align:center;">Không có dữ liệu</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

