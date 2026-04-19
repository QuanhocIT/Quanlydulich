<?php
$pageTitle = 'Chi tiết Giao dịch';
$currentPage = 'baoCaoTaiChinh';

$giaoDich = isset($giao_dich) && is_array($giao_dich) ? $giao_dich : null;
$loaiRaw = strtoupper(trim((string)($giaoDich['loai'] ?? '')));
$isThu = ($loaiRaw === 'THU');

$amountClass = $isThu ? 'amount-thu' : 'amount-chi';
$typeBadgeClass = $isThu ? 'badge-thu' : 'badge-chi';
$typeLabel = $loaiRaw !== '' ? $loaiRaw : 'N/A';
$loaiChiTiet = trim((string)($giaoDich['loai_giao_dich'] ?? ''));

$ngayGiaoDichText = !empty($giaoDich['ngay_giao_dich'])
    ? date('d/m/Y', strtotime((string)$giaoDich['ngay_giao_dich']))
    : 'N/A';

$createdAtText = !empty($giaoDich['created_at'])
    ? date('d/m/Y H:i:s', strtotime((string)$giaoDich['created_at']))
    : 'N/A';

$updatedAtText = (!empty($giaoDich['updated_at']) && ($giaoDich['updated_at'] ?? '') !== ($giaoDich['created_at'] ?? ''))
    ? date('d/m/Y H:i:s', strtotime((string)$giaoDich['updated_at']))
    : null;

ob_start();
?>
<style>
    .txn-shell {
        margin: 0 auto;
        max-width: 1120px;
        padding: 24px;
    }

    .txn-header {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .txn-title-wrap h1 {
        align-items: center;
        color: var(--text-light);
        display: inline-flex;
        font-size: clamp(1.8rem, 2.4vw, 2.3rem);
        gap: 10px;
        margin: 0;
    }

    .txn-title-wrap p {
        color: var(--text-muted);
        margin: 8px 0 0;
    }

    .txn-back {
        align-items: center;
        background: var(--accent-gold);
        border-radius: 10px;
        color: #111;
        display: inline-flex;
        font-weight: 700;
        gap: 8px;
        padding: 11px 16px;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .txn-back:hover {
        box-shadow: 0 10px 22px rgba(0, 0, 0, .24);
        color: #000;
        transform: translateY(-2px);
    }

    .txn-card {
        background:
            radial-gradient(circle at top right, rgba(212, 175, 55, .09), transparent 26%),
            rgba(35, 35, 35, .55);
        border: 1px solid rgba(255, 255, 255, .11);
        border-radius: 14px;
        box-shadow: 0 24px 50px rgba(0, 0, 0, .22);
        overflow: hidden;
    }

    .txn-card-head {
        align-items: flex-start;
        border-bottom: 1px solid rgba(255, 255, 255, .09);
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: space-between;
        padding: 22px;
    }

    .txn-section-title {
        color: var(--accent-gold);
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .txn-subtitle {
        color: var(--text-muted);
        font-size: .95rem;
        margin: 8px 0 0;
    }

    .txn-main-amount {
        font-size: clamp(1.9rem, 2.2vw, 2.5rem);
        font-weight: 800;
        line-height: 1;
        margin: 0;
        text-align: right;
    }

    .amount-thu { color: #17d399; }
    .amount-chi { color: #fb7185; }

    .txn-card-body {
        padding: 6px 22px 12px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .info-item {
        background: rgba(255, 255, 255, .03);
        border: 1px solid rgba(255, 255, 255, .06);
        border-radius: 12px;
        padding: 14px;
    }

    .info-item.full {
        grid-column: span 2;
    }

    .info-label {
        color: var(--text-muted);
        display: block;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .04em;
        margin-bottom: 7px;
        text-transform: uppercase;
    }

    .info-value {
        color: var(--text-light);
        font-size: 1rem;
        line-height: 1.55;
    }

    .info-link {
        color: #8ab4ff;
        text-decoration: none;
    }

    .info-link:hover {
        text-decoration: underline;
    }

    .type-badge {
        border-radius: 999px;
        display: inline-flex;
        font-size: .85rem;
        font-weight: 700;
        padding: 7px 12px;
    }

    .badge-thu {
        background: rgba(16, 185, 129, .18);
        border: 1px solid rgba(16, 185, 129, .35);
        color: #17d399;
    }

    .badge-chi {
        background: rgba(244, 63, 94, .18);
        border: 1px solid rgba(244, 63, 94, .35);
        color: #fb7185;
    }

    .txn-empty {
        align-items: center;
        color: var(--text-muted);
        display: flex;
        flex-direction: column;
        gap: 12px;
        justify-content: center;
        min-height: 240px;
        padding: 24px;
        text-align: center;
    }

    .txn-empty i {
        color: #f4b740;
        font-size: 2.25rem;
    }

    @media (max-width: 860px) {
        .txn-shell {
            padding: 16px;
        }

        .txn-card-head,
        .txn-card-body {
            padding-left: 16px;
            padding-right: 16px;
        }

        .txn-main-amount {
            text-align: left;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .info-item.full {
            grid-column: span 1;
        }
    }
</style>

<div class="txn-shell">
    <div class="txn-header">
        <div class="txn-title-wrap">
            <h1>
                <i class="bi bi-receipt-cutoff" style="color: var(--accent-gold);"></i>
                Chi tiết giao dịch
            </h1>
            <p>Theo dõi đầy đủ thông tin nghiệp vụ, liên kết tham chiếu và thời gian xử lý của giao dịch.</p>
        </div>
        <a class="txn-back" href="index.php?act=admin/lichSuGiaoDich">
            <i class="bi bi-arrow-left"></i>
            Quay lại danh sách
        </a>
    </div>

    <?php if ($giaoDich): ?>
        <section class="txn-card">
            <div class="txn-card-head">
                <div>
                    <h2 class="txn-section-title">Thông tin giao dịch</h2>
                    <p class="txn-subtitle">Mã đối tượng và lịch sử cập nhật được hiển thị theo dạng đọc nhanh.</p>
                </div>
                <p class="txn-main-amount <?php echo $amountClass; ?>">
                    <?php echo number_format((float)($giaoDich['so_tien'] ?? 0)); ?>đ
                </p>
            </div>

            <div class="txn-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Loại giao dịch</span>
                        <div class="info-value">
                            <span class="type-badge <?php echo $typeBadgeClass; ?>"><?php echo htmlspecialchars($typeLabel); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Loại chi tiết</span>
                        <div class="info-value"><?php echo htmlspecialchars($loaiChiTiet !== '' ? $loaiChiTiet : 'N/A'); ?></div>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Ngày giao dịch</span>
                        <div class="info-value"><?php echo $ngayGiaoDichText; ?></div>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Loại đối tượng</span>
                        <div class="info-value"><?php echo htmlspecialchars((string)($giaoDich['loai_doi_tuong'] ?? 'N/A')); ?></div>
                    </div>

                    <?php if (!empty($giaoDich['tour_id'])): ?>
                        <div class="info-item">
                            <span class="info-label">Tour tham chiếu</span>
                            <div class="info-value">
                                <a class="info-link" href="index.php?act=admin/chiTietTour&id=<?php echo (int)$giaoDich['tour_id']; ?>">
                                    Tour ID: <?php echo (int)$giaoDich['tour_id']; ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($giaoDich['booking_id'])): ?>
                        <div class="info-item">
                            <span class="info-label">Booking tham chiếu</span>
                            <div class="info-value">
                                <a class="info-link" href="index.php?act=admin/chiTietBooking&id=<?php echo (int)$giaoDich['booking_id']; ?>">
                                    Booking #<?php echo (int)$giaoDich['booking_id']; ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($giaoDich['khach_hang_id'])): ?>
                        <div class="info-item">
                            <span class="info-label">Khách hàng</span>
                            <div class="info-value">Khách hàng ID: <?php echo (int)$giaoDich['khach_hang_id']; ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($giaoDich['doi_tuong_id'])): ?>
                        <div class="info-item">
                            <span class="info-label">Đối tượng ID</span>
                            <div class="info-value"><?php echo (int)$giaoDich['doi_tuong_id']; ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="info-item">
                        <span class="info-label">Người thực hiện</span>
                        <div class="info-value">
                            <?php echo htmlspecialchars((string)($giaoDich['nguoi_thuc_hien'] ?? 'N/A')); ?>
                            <?php if (!empty($giaoDich['nguoi_thuc_hien_id'])): ?>
                                (ID: <?php echo (int)$giaoDich['nguoi_thuc_hien_id']; ?>)
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Ngày tạo</span>
                        <div class="info-value"><?php echo $createdAtText; ?></div>
                    </div>

                    <?php if ($updatedAtText !== null): ?>
                        <div class="info-item">
                            <span class="info-label">Ngày cập nhật</span>
                            <div class="info-value"><?php echo $updatedAtText; ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="info-item full">
                        <span class="info-label">Mô tả</span>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars((string)($giaoDich['mo_ta'] ?? 'Không có mô tả'))); ?></div>
                    </div>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="txn-card">
            <div class="txn-empty">
                <i class="bi bi-exclamation-triangle"></i>
                <h3 style="margin: 0; color: var(--text-light);">Không tìm thấy giao dịch</h3>
                <p style="margin: 0; max-width: 540px;">Dữ liệu có thể đã bị xóa hoặc không tồn tại trong hệ thống. Bạn có thể quay lại danh sách để kiểm tra bộ lọc.</p>
            </div>
        </section>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>











