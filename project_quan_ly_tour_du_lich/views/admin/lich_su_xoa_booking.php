<?php
$pageTitle = 'Lịch sử xóa booking';
$currentPage = 'booking';
ob_start();
?>

<style>
    .page-header-section {
        position: relative;
        background: linear-gradient(90deg, #2d2d2d 0%, #3a2e13 100%);
        border-radius: 8px;
        padding: 24px 32px;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(212,175,55,0.10);
        backdrop-filter: blur(10px);
        overflow: hidden;
    }

    .page-header-glow {
        position: absolute;
        top: 0;
        left: -60%;
        width: 60%;
        height: 100%;
        background: linear-gradient(120deg, rgba(255,236,140,0.18) 0%, rgba(255,236,140,0.45) 50%, rgba(255,236,140,0.18) 100%);
        filter: blur(2px);
        animation: lich-su-header-glow-move 2.8s linear infinite;
        z-index: 1;
        pointer-events: none;
    }

    @keyframes lich-su-header-glow-move {
        0% { left: -60%; }
        100% { left: 100%; }
    }

    .page-header-inner {
        position: relative;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-header-main {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .page-header-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4af37 60%, #fffde7 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.1rem;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
        flex-shrink: 0;
    }

    .page-header-title h1 {
        margin: 0;
        color: #ffe082;
        font-size: 1.7rem;
        font-weight: 700;
        text-shadow: 0 2px 8px #2d2d2d;
    }

    .page-header-title p {
        color: #fffde7;
        font-size: 1rem;
        margin-top: 6px;
        text-shadow: 0 1px 4px #2d2d2d;
    }

    .table-wrapper {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(212, 175, 55, 0.18);
        border-radius: 8px;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead {
        background: rgba(212, 175, 55, 0.1);
    }

    .table th {
        padding: 15px;
        text-align: left;
        font-size: 12px;
        letter-spacing: 1px;
        color: var(--accent-gold);
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-light);
        font-size: 13px;
    }

    .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-secondary {
        background: rgba(108, 117, 125, 0.2);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.3);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border-color: rgba(25, 135, 84, 0.3);
        color: #198754;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border-color: rgba(220, 53, 69, 0.3);
        color: #dc3545;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    @media (max-width: 700px) {
        .page-header-section {
            padding: 20px;
        }

        .page-header-title h1 {
            font-size: 1.4rem;
        }
    }

    .booking-info {
        font-size: 12px;
        line-height: 1.6;
    }

    body.page-booking .content-area:has(.deleted-booking-history-page) {
        padding: 34px 48px 56px;
        background:
            radial-gradient(circle at 10% 0%, rgba(13, 202, 240, 0.08), transparent 28%),
            radial-gradient(circle at 100% 10%, rgba(212, 175, 55, 0.11), transparent 30%),
            linear-gradient(180deg, rgba(255,255,255,0.018), transparent 260px);
    }

    body.page-booking .deleted-booking-history-page .page-header-section {
        min-height: 154px;
        padding: 28px 34px;
        background:
            linear-gradient(100deg, rgba(28, 31, 33, 0.96) 0%, rgba(30, 42, 45, 0.94) 52%, rgba(119, 102, 45, 0.84) 100%),
            url("<?php echo BASE_URL; ?>public/images/logos/hinh-nen-viet-nam-4k10.jpg");
        background-size: cover;
        background-position: center;
        border: 1px solid rgba(255, 255, 255, 0.09);
        box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
    }

    body.page-booking .deleted-booking-history-page .page-header-section::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.18), rgba(0,0,0,0.04));
        pointer-events: none;
    }

    body.page-booking .deleted-booking-history-page .page-header-glow {
        display: none;
    }

    body.page-booking .deleted-booking-history-page .page-header-inner {
        position: relative;
        z-index: 2;
        align-items: center;
    }

    body.page-booking .deleted-booking-history-page .page-header-main {
        align-items: center;
        gap: 18px;
    }

    body.page-booking .deleted-booking-history-page .page-header-avatar {
        width: 74px;
        height: 74px;
        border-radius: 8px;
        background: rgba(212, 175, 55, 0.18);
        border: 1px solid rgba(255, 224, 130, 0.32);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 16px 34px rgba(0,0,0,0.24);
    }

    body.page-booking .deleted-booking-history-page .page-header-title h1 {
        font-size: 2rem;
        line-height: 1.18;
        letter-spacing: 0;
        text-shadow: none;
    }

    body.page-booking .deleted-booking-history-page .page-header-title p {
        max-width: 680px;
        color: rgba(255,255,255,0.86);
        text-shadow: none;
    }

    body.page-booking .deleted-booking-history-page .page-header-section .btn {
        min-height: 46px;
        padding-inline: 28px;
        border-radius: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.page-booking .deleted-booking-history-page .table-wrapper {
        background: rgba(28, 30, 31, 0.78);
        border-color: rgba(212, 175, 55, 0.22);
        border-radius: 8px;
        overflow-x: auto;
        box-shadow: 0 14px 36px rgba(0,0,0,0.18);
    }

    body.page-booking .deleted-booking-history-page .table {
        min-width: 1180px;
    }

    body.page-booking .deleted-booking-history-page .table thead {
        background: linear-gradient(90deg, rgba(212, 175, 55, 0.14), rgba(13, 202, 240, 0.06));
    }

    body.page-booking .deleted-booking-history-page .table th {
        padding: 16px 20px;
        letter-spacing: 0.06em;
        white-space: nowrap;
    }

    body.page-booking .deleted-booking-history-page .table td {
        padding: 20px;
        vertical-align: middle;
    }

    body.page-booking .deleted-booking-history-page .table tbody tr {
        transition: background 0.2s ease;
    }

    body.page-booking .deleted-booking-history-page .table tbody tr:hover {
        background: rgba(255,255,255,0.065);
    }

    body.page-booking .deleted-booking-history-page .badge {
        border-radius: 8px;
        min-height: 30px;
        display: inline-flex;
        align-items: center;
    }

    body.page-booking .deleted-booking-history-page .booking-info {
        min-width: 210px;
        padding: 10px 12px;
        border-radius: 8px;
        background: rgba(255,255,255,0.035);
        border: 1px solid rgba(255,255,255,0.07);
    }

    body.theme-light.page-booking .deleted-booking-history-page .table-wrapper {
        background: rgba(255,255,255,0.9) !important;
    }

    @media (max-width: 900px) {
        body.page-booking .content-area:has(.deleted-booking-history-page) {
            padding: 24px 18px 42px;
        }

        body.page-booking .deleted-booking-history-page .page-header-section {
            padding: 24px;
        }

        body.page-booking .deleted-booking-history-page .page-header-inner,
        body.page-booking .deleted-booking-history-page .page-header-main {
            align-items: flex-start;
        }

        body.page-booking .deleted-booking-history-page .page-header-avatar {
            width: 58px;
            height: 58px;
            font-size: 1.8rem;
        }
    }
</style>

<div class="deleted-booking-history-page">
<!-- Page Header -->
<div class="page-header-section">
    <div class="page-header-glow"></div>
    <div class="page-header-inner">
        <div class="page-header-main">
            <div class="page-header-avatar">🕐</div>
            <div class="page-header-title">
                <h1>Lịch sử xóa booking</h1>
                <p>Xem lại các booking đã bị xóa</p>
            </div>
        </div>
        <div>
            <a href="index.php?act=admin/quanLyBooking" class="btn btn-secondary">
                ← Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        ✓ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        ⚠ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!-- Table -->
<div class="table-wrapper">
    <?php if (!empty($lichSuXoa)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th>Booking ID</th>
                    <th>Tour</th>
                    <th>Khách hàng</th>
                    <th>Thông tin booking</th>
                    <th>Người xóa</th>
                    <th>Lý do xóa</th>
                    <th>Thời gian xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lichSuXoa as $idx => $item): ?>
                    <tr>
                        <td><?php echo $idx + 1; ?></td>
                        <td>
                            <span class="badge badge-secondary">#<?php echo $item['booking_id'] ?? 'N/A'; ?></span>
                        </td>
                        <td>
                            <?php if ($item['ten_tour']): ?>
                                <strong style="color: var(--text-light);"><?php echo htmlspecialchars($item['ten_tour']); ?></strong>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Tour đã bị xóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['ten_khach_hang']): ?>
                                <div style="color: var(--text-light); font-weight: 500;">
                                    <?php echo htmlspecialchars($item['ten_khach_hang']); ?>
                                </div>
                                <?php if ($item['email_khach_hang']): ?>
                                    <small style="color: var(--text-muted); font-size: 11px;">
                                        <?php echo htmlspecialchars($item['email_khach_hang']); ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $thongTin = json_decode($item['thong_tin_booking'] ?? '{}', true);
                            if ($thongTin):
                            ?>
                                <div class="booking-info" style="color: var(--text-muted);">
                                    <strong style="color: var(--text-light);">Số người:</strong> <?php echo $thongTin['so_nguoi'] ?? 0; ?><br>
                                    <strong style="color: var(--text-light);">Tổng tiền:</strong> <?php echo number_format($thongTin['tong_tien'] ?? 0, 0, ',', '.'); ?> VNĐ<br>
                                    <?php if ($thongTin['ngay_khoi_hanh']): ?>
                                        <strong style="color: var(--text-light);">Ngày khởi hành:</strong> <?php echo date('d/m/Y', strtotime($thongTin['ngay_khoi_hanh'])); ?><br>
                                    <?php endif; ?>
                                    <strong style="color: var(--text-light);">Trạng thái:</strong> 
                                    <?php
                                    $statusLabels = [
                                        'ChoXacNhan' => 'Chờ xác nhận',
                                        'DaCoc' => 'Đã cọc',
                                        'HoanTat' => 'Hoàn tất',
                                        'Huy' => 'Hủy'
                                    ];
                                    echo $statusLabels[$thongTin['trang_thai']] ?? $thongTin['trang_thai'] ?? 'N/A';
                                    ?>
                                </div>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Không có thông tin</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['nguoi_xoa']): ?>
                                <div style="font-weight: 600; color: var(--text-light);">
                                    <?php echo htmlspecialchars($item['nguoi_xoa']); ?>
                                </div>
                                <?php if ($item['email_nguoi_xoa']): ?>
                                    <small style="color: var(--text-muted); font-size: 11px;">
                                        <?php echo htmlspecialchars($item['email_nguoi_xoa']); ?>
                                    </small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($item['ly_do_xoa']): ?>
                                <span style="color: #dc3545; font-size: 12px;">
                                    <?php echo nl2br(htmlspecialchars($item['ly_do_xoa'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">Không có</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small style="color: var(--text-muted);">
                                <?php echo $item['thoi_gian_xoa'] ? date('d/m/Y H:i:s', strtotime($item['thoi_gian_xoa'])) : 'N/A'; ?>
                            </small>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>Chưa có lịch sử xóa booking nào</p>
        </div>
    <?php endif; ?>
</div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
