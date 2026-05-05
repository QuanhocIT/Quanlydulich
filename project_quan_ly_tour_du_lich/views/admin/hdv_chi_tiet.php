<?php
$pageTitle = 'Chi tiết HDV - ' . htmlspecialchars($hdv['ho_ten'] ?? 'N/A');
$currentPage = 'nhanSu';
ob_start();

$totalTours = is_array($lich_lam_viec ?? null) ? count($lich_lam_viec) : 0;
$totalReviews = is_array($danh_gia_list ?? null) ? count($danh_gia_list) : 0;
$totalDiary = is_array($nhat_ky_list ?? null) ? count($nhat_ky_list) : 0;

$avgRating = 0;
if ($totalReviews > 0) {
    $ratingSum = 0;
    foreach ($danh_gia_list as $dg) {
        $ratingSum += (float)($dg['diem'] ?? 0);
    }
    $avgRating = $ratingSum / $totalReviews;
}

$loaiHdv = 'Noi dia';
$ngonNgu = strtolower((string)($hdv['ngon_ngu'] ?? ''));
$kinhNghiem = strtolower((string)($hdv['kinh_nghiem'] ?? ''));
if (strpos($ngonNgu, 'anh') !== false || strpos($ngonNgu, 'nhat') !== false || strpos($ngonNgu, 'han') !== false || strpos($ngonNgu, 'trung') !== false) {
    $loaiHdv = 'Quoc te';
} elseif (strpos($kinhNghiem, 'chuyen') !== false) {
    $loaiHdv = 'Chuyen tuyen';
} elseif (strpos($kinhNghiem, 'doan') !== false) {
    $loaiHdv = 'Khach doan';
}

$hieuSuatList = is_array($hieu_suat ?? null) ? array_slice($hieu_suat, 0, 6) : [];

$maxToursInPerf = 1;
foreach ($hieuSuatList as $hsTemp) {
    $maxToursInPerf = max($maxToursInPerf, (int)($hsTemp['so_tour'] ?? 0));
}

$lastTourDate = '';
if (!empty($lich_lam_viec)) {
    $last = end($lich_lam_viec);
    if (!empty($last['ngay_ket_thuc'])) {
        $lastTourDate = date('d/m/Y', strtotime((string)$last['ngay_ket_thuc']));
    }
    reset($lich_lam_viec);
}
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css' rel='stylesheet' />
<style>
    .hdv-page {
        padding: 20px;
    }

    .hdv-shell {
        background: rgba(25, 25, 25, 0.56);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        padding: 24px;
    }

    .panel {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }

    .profile-header {
        background: linear-gradient(135deg, rgba(77, 105, 186, 0.45), rgba(35, 52, 112, 0.4));
        border: 1px solid rgba(130, 159, 255, 0.35);
        border-radius: 10px;
        padding: 20px;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 18px;
        align-items: center;
    }

    .profile-avatar {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, 0.28);
        background: rgba(16, 19, 35, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent-gold);
        font-size: 44px;
    }

    .profile-name {
        margin: 0 0 10px 0;
        font-size: 38px;
        font-weight: 800;
        line-height: 1.12;
    }

    .meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 10px;
    }

    .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(8, 10, 20, 0.26);
        border-radius: 8px;
        padding: 4px 10px;
        font-weight: 600;
    }

    .contact-row {
        color: var(--text-light);
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .contact-row span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .btn-control {
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: var(--text-light);
        background: rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 9px 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-control:hover {
        background: rgba(255, 255, 255, 0.17);
        color: var(--text-light);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(170px, 1fr));
        gap: 14px;
        margin-top: 14px;
    }

    .stat-card {
        padding: 15px;
        border-left: 4px solid;
        display: flex;
        justify-content: space-between;
        align-items: center;
        min-height: 102px;
    }

    .stat-card.primary { border-left-color: #0d6efd; }
    .stat-card.success { border-left-color: #198754; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.info { border-left-color: #0dcaf0; }

    .stat-label {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 7px;
    }

    .stat-value {
        font-size: 30px;
        font-weight: 800;
        line-height: 1;
    }

    .stat-icon {
        font-size: 33px;
        opacity: 0.35;
    }

    .tabs-wrap {
        margin-top: 16px;
    }

    .tab-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .tab-btn {
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.06);
        color: var(--text-muted);
        border-radius: 8px;
        padding: 9px 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .tab-btn.active {
        color: var(--text-light);
        border-color: rgba(212, 175, 55, 0.55);
        background: rgba(212, 175, 55, 0.18);
    }

    .tab-panel {
        display: none;
    }

    .tab-panel.active {
        display: block;
    }

    .section-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, minmax(260px, 1fr));
        gap: 14px;
    }

    .sub-card {
        padding: 16px;
    }

    .sub-title {
        margin: 0 0 12px 0;
        font-size: 16px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .kv-list {
        display: grid;
        gap: 10px;
    }

    .kv-item {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 12px;
        border-bottom: 1px dashed rgba(255, 255, 255, 0.12);
        padding-bottom: 9px;
    }

    .kv-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .kv-key { color: var(--text-muted); }
    .kv-value { color: var(--text-light); font-weight: 600; }

    .calendar-box {
        padding: 16px;
    }

    #calendar {
        max-width: 100%;
    }

    .review-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(260px, 1fr));
        gap: 12px;
    }

    .review-card {
        padding: 14px;
        border: 1px solid rgba(255, 193, 7, 0.35);
        border-left: 3px solid #ffc107;
        border-radius: 8px;
        background: rgba(255, 193, 7, 0.08);
    }

    .review-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 8px;
    }

    .rating-stars {
        color: #ffc107;
        white-space: nowrap;
        font-size: 18px;
    }

    .timeline {
        border-left: 2px solid rgba(255, 255, 255, 0.2);
        margin-left: 8px;
        padding-left: 16px;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 14px;
    }

    .timeline-item::before {
        content: '';
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #4da3ff;
        position: absolute;
        left: -23px;
        top: 5px;
    }

    .perf-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .perf-table th,
    .perf-table td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 10px;
        text-align: left;
    }

    .perf-table th {
        color: var(--accent-gold);
        font-size: 13px;
        font-weight: 700;
        background: rgba(0, 0, 0, 0.2);
    }

    .perf-table tr:last-child td {
        border-bottom: 0;
    }

    .mini-chart {
        display: grid;
        gap: 8px;
    }

    .mini-row {
        display: grid;
        grid-template-columns: 90px 1fr 56px;
        gap: 10px;
        align-items: center;
    }

    .mini-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .mini-track {
        height: 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        overflow: hidden;
        position: relative;
    }

    .mini-fill-tour {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #4da3ff, #76c7ff);
    }

    .mini-fill-rating {
        height: 6px;
        border-radius: inherit;
        margin-top: 3px;
        background: linear-gradient(90deg, #ffd166, #ffb703);
    }

    .mini-value {
        font-size: 12px;
        color: var(--text-light);
        text-align: right;
        font-weight: 600;
    }

    .muted { color: var(--text-muted); }

    .empty-state {
        text-align: center;
        padding: 24px 10px;
        color: var(--text-muted);
    }

    .alert-warning {
        background: rgba(255, 193, 7, 0.16);
        border: 1px solid rgba(255, 193, 7, 0.45);
        color: #ffd76a;
        border-radius: 10px;
        padding: 12px 14px;
    }

    @media (max-width: 1200px) {
        .profile-header {
            grid-template-columns: auto 1fr;
        }

        .action-group {
            grid-column: 1 / -1;
            justify-content: flex-start;
        }
    }

    @media (max-width: 992px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }

        .section-grid-2,
        .review-grid {
            grid-template-columns: 1fr;
        }

        .profile-name {
            font-size: 30px;
        }
    }

    @media (max-width: 640px) {
        .hdv-page {
            padding: 8px;
        }

        .hdv-shell {
            padding: 12px;
        }

        .profile-header {
            grid-template-columns: 1fr;
            gap: 12px;
            padding: 14px;
        }

        .profile-name {
            font-size: 24px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .kv-item {
            grid-template-columns: 1fr;
            gap: 4px;
        }
    }
</style>

<div class="hdv-page">
    <?php if (!empty($hdv)): ?>
        <div class="hdv-shell">
            <section class="profile-header panel">
                <div class="profile-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div>
                    <h1 class="profile-name"><?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?></h1>
                    <div class="meta-row">
                        <span class="meta-item"><i class="bi bi-briefcase"></i><?php echo htmlspecialchars($hdv['vai_tro'] ?? 'N/A'); ?></span>
                        <span class="meta-item"><i class="bi bi-star"></i><?php echo htmlspecialchars($loaiHdv); ?></span>
                    </div>
                    <div class="contact-row">
                        <span><i class="bi bi-envelope"></i><?php echo htmlspecialchars($hdv['email'] ?? 'N/A'); ?></span>
                        <span><i class="bi bi-telephone"></i><?php echo htmlspecialchars($hdv['so_dien_thoai'] ?? 'N/A'); ?></span>
                        <span><i class="bi bi-calendar-check"></i>Tour gan nhat: <?php echo htmlspecialchars($lastTourDate !== '' ? $lastTourDate : 'Chua co'); ?></span>
                    </div>
                </div>
                <div class="action-group">
                    <a href="index.php?act=admin/nhanSu" class="btn-control"><i class="bi bi-arrow-left"></i>Quay lai</a>
                    <a href="index.php?act=admin/hdv_advanced" class="btn-control"><i class="bi bi-calendar2-week"></i>Quan ly lich</a>
                </div>
            </section>

            <section class="stats-grid">
                <article class="panel stat-card primary">
                    <div>
                        <div class="stat-label">Tong tour da dan</div>
                        <div class="stat-value"><?php echo (int)$totalTours; ?></div>
                    </div>
                    <i class="bi bi-geo-alt-fill stat-icon"></i>
                </article>
                <article class="panel stat-card success">
                    <div>
                        <div class="stat-label">Danh gia trung binh</div>
                        <div class="stat-value"><?php echo number_format((float)$avgRating, 1); ?></div>
                    </div>
                    <i class="bi bi-star-fill stat-icon"></i>
                </article>
                <article class="panel stat-card warning">
                    <div>
                        <div class="stat-label">So danh gia</div>
                        <div class="stat-value"><?php echo (int)$totalReviews; ?></div>
                    </div>
                    <i class="bi bi-chat-dots-fill stat-icon"></i>
                </article>
                <article class="panel stat-card info">
                    <div>
                        <div class="stat-label">Nhat ky tour</div>
                        <div class="stat-value"><?php echo (int)$totalDiary; ?></div>
                    </div>
                    <i class="bi bi-journal-text stat-icon"></i>
                </article>
            </section>

            <section class="tabs-wrap">
                <div class="tab-nav" role="tablist" aria-label="Chi tiet HDV">
                    <button class="tab-btn active" data-tab="tab-info"><i class="bi bi-info-circle"></i>Thong tin</button>
                    <button class="tab-btn" data-tab="tab-calendar"><i class="bi bi-calendar3"></i>Lich lam viec</button>
                    <button class="tab-btn" data-tab="tab-reviews"><i class="bi bi-star"></i>Danh gia</button>
                    <button class="tab-btn" data-tab="tab-diary"><i class="bi bi-journal"></i>Nhat ky</button>
                    <button class="tab-btn" data-tab="tab-performance"><i class="bi bi-bar-chart"></i>Hieu suat</button>
                </div>

                <div id="tab-info" class="tab-panel active">
                    <div class="section-grid-2">
                        <div class="panel sub-card">
                            <h3 class="sub-title"><i class="bi bi-person-vcard"></i>Thong tin ca nhan</h3>
                            <div class="kv-list">
                                <div class="kv-item"><div class="kv-key">Ho ten</div><div class="kv-value"><?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?></div></div>
                                <div class="kv-item"><div class="kv-key">Email</div><div class="kv-value"><?php echo htmlspecialchars($hdv['email'] ?? 'N/A'); ?></div></div>
                                <div class="kv-item"><div class="kv-key">Dien thoai</div><div class="kv-value"><?php echo htmlspecialchars($hdv['so_dien_thoai'] ?? 'N/A'); ?></div></div>
                                <div class="kv-item"><div class="kv-key">Vai tro</div><div class="kv-value"><?php echo htmlspecialchars($hdv['vai_tro'] ?? 'N/A'); ?></div></div>
                                <div class="kv-item"><div class="kv-key">Loai HDV</div><div class="kv-value"><?php echo htmlspecialchars($loaiHdv); ?></div></div>
                            </div>
                        </div>
                        <div class="panel sub-card">
                            <h3 class="sub-title"><i class="bi bi-mortarboard"></i>Ky nang va chung chi</h3>
                            <div class="kv-list">
                                <div class="kv-item"><div class="kv-key">Ngon ngu</div><div class="kv-value"><?php echo htmlspecialchars($hdv['ngon_ngu'] ?? 'N/A'); ?></div></div>
                                <div class="kv-item"><div class="kv-key">Kinh nghiem</div><div class="kv-value"><?php echo nl2br(htmlspecialchars($hdv['kinh_nghiem'] ?? 'N/A')); ?></div></div>
                                <div class="kv-item"><div class="kv-key">Chung chi</div><div class="kv-value"><?php echo nl2br(htmlspecialchars($hdv['chung_chi'] ?? 'N/A')); ?></div></div>
                                <div class="kv-item"><div class="kv-key">Suc khoe</div><div class="kv-value"><?php echo htmlspecialchars($hdv['suc_khoe'] ?? 'N/A'); ?></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-calendar" class="tab-panel">
                    <div class="panel calendar-box">
                        <h3 class="sub-title"><i class="bi bi-calendar3"></i>Lich lam viec HDV</h3>
                        <div id="calendar"></div>
                    </div>
                </div>

                <div id="tab-reviews" class="tab-panel">
                    <div class="panel sub-card">
                        <h3 class="sub-title"><i class="bi bi-star-fill"></i>Danh gia tu khach hang</h3>
                        <?php if (!empty($danh_gia_list)): ?>
                            <div class="review-grid">
                                <?php foreach ($danh_gia_list as $dg): ?>
                                    <?php
                                    $stars = (int)($dg['diem'] ?? 0);
                                    $reviewDate = '';
                                    if (!empty($dg['ngay_danh_gia'])) {
                                        $reviewDate = date('d/m/Y H:i', strtotime((string)$dg['ngay_danh_gia']));
                                    }
                                    ?>
                                    <article class="review-card">
                                        <div class="review-head">
                                            <div>
                                                <div><strong><?php echo htmlspecialchars($dg['ten_tour'] ?? 'Tour'); ?></strong></div>
                                                <div class="muted" style="font-size:12px;"><?php echo htmlspecialchars($reviewDate); ?></div>
                                            </div>
                                            <div class="rating-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php echo $i <= $stars ? '★' : '☆'; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div><?php echo nl2br(htmlspecialchars($dg['noi_dung'] ?? '')); ?></div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">Chua co danh gia nao.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="tab-diary" class="tab-panel">
                    <div class="panel sub-card">
                        <h3 class="sub-title"><i class="bi bi-journal-text"></i>Nhat ky hoat dong</h3>
                        <?php if (!empty($nhat_ky_list)): ?>
                            <div class="timeline">
                                <?php foreach ($nhat_ky_list as $nk): ?>
                                    <?php
                                    $diaryDate = '';
                                    if (!empty($nk['ngay_ghi'])) {
                                        $diaryDate = date('d/m/Y H:i', strtotime((string)$nk['ngay_ghi']));
                                    }
                                    ?>
                                    <div class="timeline-item">
                                        <div><strong><?php echo htmlspecialchars($nk['ten_tour'] ?? 'Tour'); ?></strong></div>
                                        <div class="muted" style="font-size:12px;"><i class="bi bi-clock-history"></i> <?php echo htmlspecialchars($diaryDate); ?></div>
                                        <div style="margin-top:6px;"><?php echo nl2br(htmlspecialchars($nk['noi_dung'] ?? '')); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">Chua co nhat ky nao.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="tab-performance" class="tab-panel">
                    <div class="panel sub-card">
                        <h3 class="sub-title"><i class="bi bi-bar-chart-line"></i>Hieu suat 6 thang gan nhat</h3>
                        <?php if (!empty($hieuSuatList)): ?>
                            <div class="mini-chart">
                                <?php foreach ($hieuSuatList as $hs): ?>
                                    <?php
                                    $monthLabel = (string)($hs['thang'] ?? '');
                                    $tourCount = (int)($hs['so_tour'] ?? 0);
                                    $ratingVal = (float)($hs['diem_tb'] ?? 0);
                                    $tourPct = (int)round(($tourCount / $maxToursInPerf) * 100);
                                    $ratingPct = (int)round((max(0, min(5, $ratingVal)) / 5) * 100);
                                    ?>
                                    <div class="mini-row">
                                        <div class="mini-label"><?php echo htmlspecialchars($monthLabel); ?></div>
                                        <div>
                                            <div class="mini-track" title="So tour: <?php echo $tourCount; ?>">
                                                <div class="mini-fill-tour" style="width: <?php echo $tourPct; ?>%;"></div>
                                            </div>
                                            <div class="mini-track" title="Diem trung binh: <?php echo number_format($ratingVal, 2); ?>" style="height: 8px; margin-top: 4px;">
                                                <div class="mini-fill-rating" style="width: <?php echo $ratingPct; ?>%;"></div>
                                            </div>
                                        </div>
                                        <div class="mini-value"><?php echo $tourCount; ?> tour</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <table class="perf-table">
                                <thead>
                                    <tr>
                                        <th>Thang</th>
                                        <th>So tour</th>
                                        <th>Diem trung binh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hieuSuatList as $hs): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars((string)($hs['thang'] ?? '')); ?></td>
                                            <td><?php echo (int)($hs['so_tour'] ?? 0); ?></td>
                                            <td><?php echo number_format((float)($hs['diem_tb'] ?? 0), 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">Chua co du lieu hieu suat theo thang.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    <?php else: ?>
        <div class="alert-warning">
            <i class="bi bi-exclamation-triangle"></i> Khong tim thay thong tin HDV.
        </div>
    <?php endif; ?>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    document.addEventListener('DOMContentLoaded', function() {
        var tabButtons = document.querySelectorAll('.tab-btn');
        var tabPanels = document.querySelectorAll('.tab-panel');
        var calendar = null;

        function initCalendarOnce() {
            var calendarEl = document.getElementById('calendar');
            if (!calendarEl || calendar) {
                return;
            }

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'vi',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                buttonText: {
                    today: 'Hom nay',
                    month: 'Thang',
                    week: 'Tuan',
                    list: 'Danh sach'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch('index.php?act=admin/hdv_get_schedule&hdv_id=<?php echo (int)($hdv['nhan_su_id'] ?? 0); ?>')
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            successCallback(data);
                        })
                        .catch(function(error) {
                            failureCallback(error);
                        });
                },
                eventDidMount: function(info) {
                    var statusMap = {
                        SapKhoiHanh: 'Sap khoi hanh',
                        DangChay: 'Dang chay',
                        HoanThanh: 'Hoan thanh'
                    };
                    var st = (info.event.extendedProps && info.event.extendedProps.trang_thai) || '';
                    var displayStatus = statusMap[st] || st || 'Khong ro';
                    var meeting = (info.event.extendedProps && info.event.extendedProps.diem_tap_trung) || 'Chua cap nhat';
                    info.el.title = info.event.title + '\nTrang thai: ' + displayStatus + '\nDiem tap trung: ' + meeting;
                },
                eventClick: function(info) {
                    var statusMap = {
                        SapKhoiHanh: 'Sap khoi hanh',
                        DangChay: 'Dang chay',
                        HoanThanh: 'Hoan thanh'
                    };
                    var st = (info.event.extendedProps && info.event.extendedProps.trang_thai) || '';
                    var displayStatus = statusMap[st] || st || 'Khong ro';
                    var meeting = (info.event.extendedProps && info.event.extendedProps.diem_tap_trung) || 'Chua cap nhat';
                    alert(
                        'Tour: ' + info.event.title + '\n' +
                        'Tu: ' + info.event.start.toLocaleDateString('vi-VN') + '\n' +
                        'Den: ' + (info.event.end ? info.event.end.toLocaleDateString('vi-VN') : 'N/A') + '\n' +
                        'Trang thai: ' + displayStatus + '\n' +
                        'Diem tap trung: ' + meeting
                    );
                }
            });

            calendar.render();
        }

        tabButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = btn.getAttribute('data-tab');

                tabButtons.forEach(function(otherBtn) {
                    otherBtn.classList.remove('active');
                });
                btn.classList.add('active');

                tabPanels.forEach(function(panel) {
                    panel.classList.toggle('active', panel.id === targetId);
                });

                if (targetId === 'tab-calendar') {
                    initCalendarOnce();
                    if (calendar) {
                        setTimeout(function() { calendar.updateSize(); }, 30);
                    }
                }
            });
        });
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
