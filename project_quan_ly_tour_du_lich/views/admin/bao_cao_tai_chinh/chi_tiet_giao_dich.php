<?php
$pageTitle = 'Chi tiết Giao dịch';
$currentPage = 'baoCaoTaiChinh';
ob_start();
?>
<style>
        .info-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 250px;
            flex-shrink: 0;
        }
        .info-value {
            flex: 1;
            color: var(--text-light);
        }
        .badge-thu {
            background: rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .badge-chi {
            background: rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .amount {
            font-size: 28px;
            font-weight: 700;
            margin: 20px 0;
        }
        .amount-thu { color: #10b981; }
        .amount-chi { color: #ef4444; }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-gold);
            color: #000;
            cursor: pointer;
            transition: transform 0.2s;
            font-weight: 500;
        }
        .btn:hover {
            transform: translateY(-2px);
            background: #ffd700;
        }
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--accent-gold);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-gold);
        }
        a {
            color: var(--accent-gold);
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>

<div style="padding: 20px; max-width: 1000px; margin: 0 auto;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="fas fa-receipt" style="color: var(--accent-gold);"></i> Chi tiết Giao dịch
        </h1>
        <a href="index.php?act=admin/lichSuGiaoDich" style="background: var(--accent-gold); color: #000; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-top: 15px; font-weight: 500;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if ($giao_dich): ?>
        <div class="info-card">
            <div class="section-title">Thông tin Giao dịch</div>
                
                <div class="amount amount-<?= strtolower($giao_dich['loai']) ?>">
                    <?= number_format($giao_dich['so_tien'] ?? 0) ?>đ
                </div>
                
                <div class="info-row">
                    <div class="info-label">Loại giao dịch:</div>
                    <div class="info-value">
                        <span class="badge-<?= strtolower($giao_dich['loai']) ?>">
                            <?= htmlspecialchars($giao_dich['loai'] ?? 'N/A') ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Loại giao dịch chi tiết:</div>
                    <div class="info-value"><?= htmlspecialchars($giao_dich['loai_giao_dich'] ?? 'N/A') ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">Ngày giao dịch:</div>
                    <div class="info-value">
                        <?= $giao_dich['ngay_giao_dich'] ? date('d/m/Y', strtotime($giao_dich['ngay_giao_dich'])) : 'N/A' ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Mô tả:</div>
                    <div class="info-value"><?= htmlspecialchars($giao_dich['mo_ta'] ?? 'Không có mô tả') ?></div>
                </div>

                <?php if (!empty($giao_dich['tour_id'])): ?>
                    <div class="info-row">
                        <div class="info-label">Tour:</div>
                        <div class="info-value">
                            <a href="index.php?act=admin/chiTietTour&id=<?= $giao_dich['tour_id'] ?>" style="color: #667eea;">
                                Tour ID: <?= $giao_dich['tour_id'] ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($giao_dich['booking_id'])): ?>
                    <div class="info-row">
                        <div class="info-label">Booking:</div>
                        <div class="info-value">
                            <a href="index.php?act=admin/chiTietBooking&id=<?= $giao_dich['booking_id'] ?>" style="color: #667eea;">
                                Booking #<?= $giao_dich['booking_id'] ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($giao_dich['khach_hang_id'])): ?>
                    <div class="info-row">
                        <div class="info-label">Khách hàng:</div>
                        <div class="info-value">Khách hàng ID: <?= $giao_dich['khach_hang_id'] ?></div>
                    </div>
                <?php endif; ?>

                <div class="info-row">
                    <div class="info-label">Loại đối tượng:</div>
                    <div class="info-value"><?= htmlspecialchars($giao_dich['loai_doi_tuong'] ?? 'N/A') ?></div>
                </div>

                <?php if (!empty($giao_dich['doi_tuong_id'])): ?>
                    <div class="info-row">
                        <div class="info-label">Đối tượng ID:</div>
                        <div class="info-value"><?= $giao_dich['doi_tuong_id'] ?></div>
                    </div>
                <?php endif; ?>

                <div class="info-row">
                    <div class="info-label">Người thực hiện:</div>
                    <div class="info-value">
                        <?= htmlspecialchars($giao_dich['nguoi_thuc_hien'] ?? 'N/A') ?>
                        <?php if (!empty($giao_dich['nguoi_thuc_hien_id'])): ?>
                            (ID: <?= $giao_dich['nguoi_thuc_hien_id'] ?>)
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Ngày tạo:</div>
                    <div class="info-value">
                        <?= $giao_dich['created_at'] ? date('d/m/Y H:i:s', strtotime($giao_dich['created_at'])) : 'N/A' ?>
                    </div>
                </div>

                <?php if (!empty($giao_dich['updated_at']) && $giao_dich['updated_at'] != $giao_dich['created_at']): ?>
                    <div class="info-row">
                        <div class="info-label">Ngày cập nhật:</div>
                        <div class="info-value">
                            <?= date('d/m/Y H:i:s', strtotime($giao_dich['updated_at'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="info-card">
            <p style="text-align: center; color: var(--text-muted); padding: 40px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px; display: block; color: var(--text-muted);"></i>
                Không tìm thấy giao dịch
            </p>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>











