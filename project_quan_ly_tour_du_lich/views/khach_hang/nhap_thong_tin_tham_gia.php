<?php
/** @var int    $bookingId */
/** @var array  $booking */
/** @var int    $requiredCount */
/** @var array  $draft */
/** @var array  $existingRows */
/** @var array  $participantErrors */

$pageTitle = 'Thông tin người tham gia';
$activePage = 'yeuCauTour';
$pageHero = [
    'eyebrow' => 'HOÀN THIỆN HỒ SƠ',
    'icon' => 'bi-person-vcard',
    'title' => 'Khai báo người tham gia đầy đủ, gọn và dễ kiểm tra.',
    'subtitle' => 'Dữ liệu này được dùng cho check-in, đối soát giấy tờ và chuẩn bị dịch vụ trước ngày khởi hành, nên cần chính xác cho từng hành khách.',
    'actions' => [
        ['label' => 'Xem hóa đơn', 'url' => 'index.php?act=khachHang/hoaDon&booking_id=' . (int)$bookingId, 'icon' => 'bi-receipt'],
        ['label' => 'Quay lại booking', 'url' => 'index.php?act=khachHang/yeuCauTour', 'icon' => 'bi-arrow-left', 'style' => 'ghost'],
    ],
];

$sourceRows = [];
if (!empty($draft) && is_array($draft)) {
    foreach ($draft as $row) {
        if (is_array($row)) {
            $sourceRows[] = $row;
        }
    }
} elseif (!empty($existingRows) && is_array($existingRows)) {
    $sourceRows = $existingRows;
}

while (count($sourceRows) < (int)$requiredCount) {
    $sourceRows[] = [];
}

if (!isset($sourceRows[0]) || !is_array($sourceRows[0])) {
    $sourceRows[0] = [];
}
$sourceRows[0] = array_merge([
    'ho_ten' => (string)($booking['ho_ten'] ?? ''),
    'so_dien_thoai' => (string)($booking['so_dien_thoai'] ?? ''),
    'email' => (string)($booking['email'] ?? ''),
    'dia_chi' => (string)($booking['dia_chi'] ?? ''),
    'quoc_tich' => 'Việt Nam',
    'gioi_tinh' => 'Khac',
], $sourceRows[0]);

ob_start(); ?>
    .participant-layout {
        display: grid;
        gap: 22px;
        grid-template-columns: minmax(0, 1.55fr) minmax(300px, .75fr);
    }
    .booking-summary-list {
        display: grid;
        gap: 12px;
    }
    .booking-summary-item {
        background: rgba(255,255,255,.72);
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 18px;
        padding: 14px;
    }
    .participant-card + .participant-card {
        margin-top: 18px;
    }
    .participant-card-title {
        align-items: center;
        display: flex;
        font-size: 1.02rem;
        font-weight: 800;
        gap: 10px;
        margin-bottom: 8px;
    }
    .participant-card-title i {
        color: #d6b26d;
    }
    .participant-card-note {
        color: #64748b;
        font-size: .86rem;
        margin-bottom: 14px;
    }
    .participant-form-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(12, minmax(0, 1fr));
    }
    .span-12 { grid-column: span 12; }
    .span-6 { grid-column: span 6; }
    .span-4 { grid-column: span 4; }
    .span-3 { grid-column: span 3; }
    .file-link {
        color: #1d4ed8;
        font-size: .82rem;
        font-weight: 700;
        margin-top: 6px;
        text-decoration: none;
    }
    .file-link:hover {
        text-decoration: underline;
    }
    .error-text {
        color: #b42318;
        font-size: .82rem;
        margin-top: 6px;
    }
    @media (max-width: 1100px) {
        .participant-layout {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 767px) {
        .participant-form-grid {
            grid-template-columns: 1fr;
        }
        .span-12,
        .span-6,
        .span-4,
        .span-3 {
            grid-column: auto;
        }
    }
<?php
$extraCss = ob_get_clean();
include __DIR__ . '/_layout/header.php';
?>

<main class="kh-page">
    <section class="kh-grid-3 kh-section" aria-label="Tổng quan booking">
        <div class="kh-stat-card">
            <span>Mã booking</span>
            <strong>#<?php echo (int)$bookingId; ?></strong>
        </div>
        <div class="kh-stat-card">
            <span>Số khách cần khai báo</span>
            <strong><?php echo (int)$requiredCount; ?></strong>
        </div>
        <div class="kh-stat-card">
            <span>Trạng thái hồ sơ</span>
            <strong><?php echo !empty($existingRows) ? 'Đã lưu' : 'Chưa đủ'; ?></strong>
        </div>
    </section>

    <section class="participant-layout kh-section">
        <div class="kh-surface">
            <div class="kh-surface-body">
                <?php if (!empty($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="kh-section-head">
                    <div>
                        <h2 class="kh-section-title">Danh sách hành khách</h2>
                        <p class="kh-section-note">Khai báo đầy đủ cho từng người tham gia. Dòng đầu tiên đã được điền sẵn từ người đại diện đặt tour để tiết kiệm thời gian.</p>
                    </div>
                    <span class="kh-chip"><i class="bi bi-people"></i> Tổng <?php echo (int)$requiredCount; ?> người</span>
                </div>

                <form method="POST" enctype="multipart/form-data" action="index.php?act=khachHang/nhapThongTinThamGia&booking_id=<?php echo (int)$bookingId; ?>" class="kh-stack">
                    <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">

                    <?php for ($i = 0; $i < (int)$requiredCount; $i++):
                        $p = $sourceRows[$i] ?? [];
                        $rowErrors = $participantErrors[$i] ?? [];
                    ?>
                        <section class="kh-surface-soft participant-card">
                            <div class="kh-surface-body">
                                <div class="participant-card-title">
                                    <i class="bi bi-person-badge"></i>
                                    <?php echo $i === 0 ? 'Người tham gia 1 (đề xuất: người đại diện)' : ('Người tham gia ' . ($i + 1)); ?>
                                </div>
                                <?php if ($i === 0): ?>
                                    <div class="participant-card-note">Nếu người đặt tour cũng là hành khách, bạn có thể giữ nguyên thông tin đã được điền sẵn.</div>
                                <?php endif; ?>

                                <div class="participant-form-grid">
                                    <div class="span-6">
                                        <label class="kh-label">Họ tên</label>
                                        <input class="kh-form-control" type="text" name="participants[<?php echo $i; ?>][ho_ten]" required value="<?php echo htmlspecialchars((string)($p['ho_ten'] ?? '')); ?>">
                                        <?php if (!empty($rowErrors['ho_ten'])): ?><div class="error-text"><?php echo htmlspecialchars((string)$rowErrors['ho_ten']); ?></div><?php endif; ?>
                                    </div>
                                    <div class="span-3">
                                        <label class="kh-label">Ngày sinh</label>
                                        <input class="kh-form-control" type="date" name="participants[<?php echo $i; ?>][ngay_sinh]" value="<?php echo htmlspecialchars((string)($p['ngay_sinh'] ?? '')); ?>">
                                        <?php if (!empty($rowErrors['ngay_sinh'])): ?><div class="error-text"><?php echo htmlspecialchars((string)$rowErrors['ngay_sinh']); ?></div><?php endif; ?>
                                    </div>
                                    <div class="span-3">
                                        <label class="kh-label">Giới tính</label>
                                        <?php $gt = (string)($p['gioi_tinh'] ?? 'Khac'); ?>
                                        <select class="kh-form-select" name="participants[<?php echo $i; ?>][gioi_tinh]">
                                            <option value="Nam" <?php echo $gt === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                            <option value="Nữ" <?php echo $gt === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                            <option value="Khac" <?php echo $gt === 'Khac' ? 'selected' : ''; ?>>Khác</option>
                                        </select>
                                    </div>

                                    <div class="span-4">
                                        <label class="kh-label">Số điện thoại</label>
                                        <input class="kh-form-control" type="text" name="participants[<?php echo $i; ?>][so_dien_thoai]" value="<?php echo htmlspecialchars((string)($p['so_dien_thoai'] ?? '')); ?>">
                                    </div>
                                    <div class="span-4">
                                        <label class="kh-label">Email</label>
                                        <input class="kh-form-control" type="email" name="participants[<?php echo $i; ?>][email]" value="<?php echo htmlspecialchars((string)($p['email'] ?? '')); ?>">
                                        <?php if (!empty($rowErrors['email'])): ?><div class="error-text"><?php echo htmlspecialchars((string)$rowErrors['email']); ?></div><?php endif; ?>
                                    </div>
                                    <div class="span-4">
                                        <label class="kh-label">Quốc tịch</label>
                                        <input class="kh-form-control" type="text" name="participants[<?php echo $i; ?>][quoc_tich]" value="<?php echo htmlspecialchars((string)($p['quoc_tich'] ?? 'Việt Nam')); ?>">
                                    </div>

                                    <div class="span-6">
                                        <label class="kh-label">Số CMND/CCCD</label>
                                        <input class="kh-form-control" type="text" name="participants[<?php echo $i; ?>][so_cmnd]" value="<?php echo htmlspecialchars((string)($p['so_cmnd'] ?? '')); ?>">
                                        <input type="hidden" name="participants[<?php echo $i; ?>][existing_anh_cccd]" value="<?php echo htmlspecialchars((string)($p['anh_cccd'] ?? '')); ?>">
                                        <div class="kh-help">Nhập 9 hoặc 12 chữ số nếu có.</div>
                                        <?php if (!empty($rowErrors['so_cmnd'])): ?><div class="error-text"><?php echo htmlspecialchars((string)$rowErrors['so_cmnd']); ?></div><?php endif; ?>
                                    </div>
                                    <div class="span-6">
                                        <label class="kh-label">Số passport</label>
                                        <input class="kh-form-control" type="text" name="participants[<?php echo $i; ?>][so_passport]" value="<?php echo htmlspecialchars((string)($p['so_passport'] ?? '')); ?>">
                                        <input type="hidden" name="participants[<?php echo $i; ?>][existing_anh_passport]" value="<?php echo htmlspecialchars((string)($p['anh_passport'] ?? '')); ?>">
                                        <div class="kh-help">Dùng cho tour quốc tế hoặc khi hãng vận chuyển yêu cầu.</div>
                                        <?php if (!empty($rowErrors['so_passport'])): ?><div class="error-text"><?php echo htmlspecialchars((string)$rowErrors['so_passport']); ?></div><?php endif; ?>
                                    </div>

                                    <div class="span-6">
                                        <label class="kh-label">Ảnh CCCD/CMND</label>
                                        <input class="kh-form-control" type="file" name="participants[<?php echo $i; ?>][anh_cccd]" accept="image/*,.pdf">
                                        <?php if (!empty($p['anh_cccd'])): ?>
                                            <a class="file-link" href="<?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/' . ltrim((string)$p['anh_cccd'], '/')); ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text me-1"></i> Xem file đã tải lên</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="span-6">
                                        <label class="kh-label">Ảnh passport</label>
                                        <input class="kh-form-control" type="file" name="participants[<?php echo $i; ?>][anh_passport]" accept="image/*,.pdf">
                                        <?php if (!empty($p['anh_passport'])): ?>
                                            <a class="file-link" href="<?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/' . ltrim((string)$p['anh_passport'], '/')); ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark-text me-1"></i> Xem file đã tải lên</a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="span-12">
                                        <label class="kh-label">Địa chỉ</label>
                                        <input class="kh-form-control" type="text" name="participants[<?php echo $i; ?>][dia_chi]" value="<?php echo htmlspecialchars((string)($p['dia_chi'] ?? '')); ?>">
                                    </div>
                                    <div class="span-12">
                                        <label class="kh-label">Ghi chú</label>
                                        <input class="kh-form-control" type="text" name="participants[<?php echo $i; ?>][ghi_chu]" value="<?php echo htmlspecialchars((string)($p['ghi_chu'] ?? '')); ?>">
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php endfor; ?>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <a class="kh-btn-ghost" href="index.php?act=khachHang/hoaDon&booking_id=<?php echo (int)$bookingId; ?>"><i class="bi bi-clock-history"></i> Để sau</a>
                        <button type="submit" class="kh-btn-main border-0"><i class="bi bi-check-circle"></i> Lưu thông tin người tham gia</button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="kh-stack">
            <div class="kh-surface-soft">
                <div class="kh-surface-body">
                    <h2 class="kh-section-title" style="font-size:1.4rem;">Thông tin booking</h2>
                    <div class="booking-summary-list">
                        <div class="booking-summary-item">
                            <span class="kh-label">Tour</span>
                            <strong><?php echo htmlspecialchars((string)($booking['ten_tour'] ?? 'N/A')); ?></strong>
                        </div>
                        <div class="booking-summary-item">
                            <span class="kh-label">Người đại diện</span>
                            <strong><?php echo htmlspecialchars((string)($booking['ho_ten'] ?? 'Chưa cập nhật')); ?></strong>
                            <div class="kh-help"><?php echo htmlspecialchars((string)($booking['so_dien_thoai'] ?? 'Chưa cập nhật')); ?><?php if (!empty($booking['email'])): ?> · <?php echo htmlspecialchars((string)$booking['email']); ?><?php endif; ?></div>
                        </div>
                        <div class="booking-summary-item">
                            <span class="kh-label">Địa chỉ đối chiếu</span>
                            <strong><?php echo htmlspecialchars((string)($booking['dia_chi'] ?? 'Chưa cập nhật')); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kh-surface-soft">
                <div class="kh-surface-body">
                    <h2 class="kh-section-title" style="font-size:1.4rem;">Lưu ý nhập liệu</h2>
                    <div class="kh-stack">
                        <div class="kh-chip"><i class="bi bi-shield-check"></i> Điền đúng giấy tờ để check-in nhanh hơn</div>
                        <div class="kh-help">Nếu thiếu passport hoặc CCCD tại thời điểm hiện tại, bạn vẫn có thể lưu trước rồi quay lại cập nhật sau.</div>
                        <div class="kh-help">Với tour quốc tế, nên kiểm tra kỹ họ tên và số passport khớp hoàn toàn với giấy tờ gốc.</div>
                    </div>
                </div>
            </div>
        </aside>
    </section>
</main>

<?php include __DIR__ . '/_layout/footer.php'; ?>
