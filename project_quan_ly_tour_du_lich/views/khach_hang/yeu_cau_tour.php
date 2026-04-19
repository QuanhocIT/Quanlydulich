<?php
$bookings = isset($bookings) && is_array($bookings) ? $bookings : [];
$participantsByBooking = isset($participantsByBooking) && is_array($participantsByBooking) ? $participantsByBooking : [];
$upcomingReminders = isset($upcomingReminders) && is_array($upcomingReminders) ? $upcomingReminders : [];

$statusLabels = [
    'ChoXacNhan' => 'Chờ xác nhận',
    'DaCoc' => 'Đã cọc',
    'HoanTat' => 'Hoàn tất',
    'Huy' => 'Đã hủy',
    'DaHuy' => 'Đã hủy',
    'TuChoi' => 'Từ chối',
];

$statusClasses = [
    'ChoXacNhan' => 'is-pending',
    'DaCoc' => 'is-paid',
    'HoanTat' => 'is-done',
    'Huy' => 'is-cancelled',
    'DaHuy' => 'is-cancelled',
    'TuChoi' => 'is-cancelled',
];

$formatDate = static function ($value, $fallback = 'Chưa cập nhật') {
    return !empty($value) ? date('d/m/Y', strtotime((string)$value)) : $fallback;
};

$resolveImage = static function ($image) {
    $image = trim((string)$image);
    if ($image === '') {
        return 'https://images.unsplash.com/photo-1465156799763-2c087c332922?auto=format&fit=crop&w=700&q=80';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    return rtrim(BASE_URL, '/') . '/' . ltrim($image, '/');
};

$validBookings = array_filter($bookings, static function ($booking) {
    return !in_array((string)($booking['trang_thai'] ?? ''), ['Huy', 'DaHuy', 'TuChoi'], true);
});

$participantMissingCount = 0;
foreach ($bookings as $booking) {
    $bookingId = (int)($booking['booking_id'] ?? 0);
    if ($bookingId > 0 && empty($participantsByBooking[$bookingId])) {
        $participantMissingCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo dõi tour đã đặt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: rgba(15, 23, 42, .12);
            --card: rgba(255, 255, 255, .88);
            --leaf: #15233b;
            --teal: #20365f;
            --gold: #d6b26d;
            --cream: #f3ead8;
        }

        * { box-sizing: border-box; }

        body.trk {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(214,178,109,.16), transparent 60%),
                radial-gradient(900px 520px at 85% 0%, rgba(11,18,32,.08), transparent 55%),
                #f5f6f8;
        }

        .page-shell {
            width: min(1500px, calc(100% - 48px));
            margin: 0 auto;
            padding: 24px 0 56px;
        }

        .topbar {
            align-items: center;
            background: rgba(11,18,32,.92);
            border: 1px solid rgba(214,178,109,.22);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 18px;
            padding: 12px 14px;
            box-shadow: 0 12px 34px rgba(2,6,23,.18);
        }

        .brand {
            align-items: center;
            color: #fff;
            display: inline-flex;
            font-weight: 800;
            gap: 10px;
            letter-spacing: .08em;
            text-decoration: none;
            text-transform: uppercase;
        }

        .brand-mark {
            align-items: center;
            background: linear-gradient(135deg, var(--leaf), var(--teal));
            border-radius: 16px;
            color: var(--gold);
            display: inline-flex;
            height: 44px;
            justify-content: center;
            width: 44px;
        }

        .nav-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .nav-pill {
            align-items: center;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(214,178,109,.28);
            border-radius: 999px;
            color: rgba(255,255,255,.9);
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            gap: 8px;
            padding: 10px 15px;
            text-decoration: none;
            transition: background .2s, border-color .2s, color .2s;
        }

        .nav-pill:hover {
            background: rgba(214,178,109,.18);
            border-color: rgba(214,178,109,.46);
            color: #fff;
        }

        .nav-pill.is-active {
            background: linear-gradient(135deg, var(--gold), #e2bf78);
            border-color: rgba(214,178,109,.72);
            color: #132033;
            box-shadow: 0 10px 24px rgba(185,137,61,.24);
        }

        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            border: 1px solid rgba(214,178,109,.2);
            background:
                linear-gradient(115deg, rgba(11,18,32,.93), rgba(21,35,59,.78)),
                url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1800&q=80') center/cover;
            box-shadow: 0 30px 80px rgba(2,6,23,.22);
            color: #fff;
            padding: 28px;
        }

        .hero h1 {
            max-width: 820px;
            margin: 44px 0 12px;
            font-family: "Playfair Display", serif;
            font-size: clamp(2rem, 4vw, 3.35rem);
            line-height: 1.05;
            letter-spacing: .2px;
        }

        .hero p {
            max-width: 720px;
            margin: 0;
            color: rgba(255,255,255,.82);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin: 22px 0;
        }

        .stat-card,
        .panel,
        .booking-card {
            border: 1px solid var(--line);
            background: var(--card);
            box-shadow: 0 18px 50px rgba(18,33,29,.08);
            backdrop-filter: blur(12px);
        }

        .stat-card {
            border-radius: 22px;
            padding: 18px;
        }

        .stat-card span {
            display: block;
            color: var(--muted);
            font-size: .78rem;
            font-weight: 900;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .stat-card strong {
            display: block;
            margin-top: 8px;
            font-size: 2rem;
            line-height: 1;
        }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(340px, .85fr);
            gap: 22px;
            align-items: start;
        }

        .panel {
            border-radius: 28px;
            padding: 22px;
        }

        .panel + .panel {
            margin-top: 22px;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        .panel-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-family: "Playfair Display", serif;
            font-size: clamp(1.15rem, 2vw, 1.45rem);
            letter-spacing: .2px;
            font-weight: 900;
        }

        .panel-title i { color: var(--gold); }

        .booking-list {
            display: grid;
            gap: 16px;
        }

        .booking-card {
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            gap: 18px;
            border-radius: 24px;
            overflow: hidden;
            padding: 14px;
        }

        .booking-thumb {
            width: 100%;
            height: 170px;
            border-radius: 18px;
            object-fit: cover;
            background: #e5ebf4;
        }

        .booking-body {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .booking-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 8px;
        }

        .booking-title {
            margin: 0;
            font-size: 1.18rem;
            font-weight: 900;
            line-height: 1.35;
        }

        .status-badge {
            border-radius: 999px;
            padding: 7px 12px;
            font-size: .78rem;
            font-weight: 900;
            border: 1px solid rgba(20,35,31,.12);
            color: #233832;
            background: rgba(20,35,31,.08);
            white-space: nowrap;
        }

        .status-badge.is-pending { color: #9a3412; background: rgba(249,115,22,.15); border-color: rgba(249,115,22,.28); }
        .status-badge.is-paid { color: #1d4ed8; background: rgba(59,130,246,.15); border-color: rgba(59,130,246,.28); }
        .status-badge.is-done { color: #047857; background: rgba(16,185,129,.15); border-color: rgba(16,185,129,.28); }
        .status-badge.is-cancelled { color: #b42318; background: rgba(239,68,68,.12); border-color: rgba(239,68,68,.24); }

        .booking-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin: 8px 0 14px;
        }

        .meta-item {
            display: flex;
            gap: 9px;
            color: var(--muted);
            font-size: .92rem;
            font-weight: 700;
            min-width: 0;
        }

        .meta-item i {
            color: var(--teal);
            flex: 0 0 auto;
        }

        .meta-item b {
            color: var(--ink);
            font-weight: 900;
        }

        .booking-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: auto;
        }

        .action-primary,
        .action-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            border-radius: 999px;
            padding: 10px 15px;
            text-decoration: none;
            font-size: .88rem;
            font-weight: 900;
        }

        .action-primary {
            background: linear-gradient(135deg, var(--leaf), var(--teal));
            color: #fff;
        }

        .action-soft {
            background: var(--cream);
            color: #17413b;
            border: 1px solid rgba(214,178,109,.3);
        }

        .side-scroll {
            display: grid;
            gap: 12px;
            max-height: 520px;
            overflow: auto;
            padding-right: 4px;
        }

        .side-card {
            border: 1px solid rgba(20,35,31,.1);
            border-radius: 18px;
            background: rgba(255,255,255,.76);
            padding: 14px;
        }

        .side-card h3 {
            margin: 0 0 8px;
            font-size: .98rem;
            line-height: 1.45;
            font-weight: 900;
        }

        .side-card p {
            margin: 0 0 10px;
            color: var(--muted);
            font-size: .9rem;
            line-height: 1.6;
        }

        .participant-list {
            margin: 0;
            padding-left: 18px;
            color: #2d3e5d;
            font-size: .9rem;
            display: grid;
            gap: 4px;
        }

        .days-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            color: #1d3354;
            background: rgba(15,30,54,.09);
            font-size: .8rem;
            font-weight: 900;
        }

        .side-card.is-urgent {
            border-color: rgba(220,53,69,.3);
            background: rgba(220,53,69,.08);
        }

        .side-card.is-urgent .days-pill {
            color: #9f1239;
            background: rgba(220,53,69,.16);
        }

        .empty-state {
            border: 1px dashed rgba(20,35,31,.22);
            border-radius: 22px;
            background: rgba(255,255,255,.65);
            color: var(--muted);
            text-align: center;
            padding: 38px 18px;
            font-weight: 700;
        }

        .empty-state i {
            display: block;
            margin-bottom: 10px;
            color: var(--gold);
            font-size: 2rem;
        }

        @media (max-width: 1100px) {
            .content-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 768px) {
            .page-shell {
                width: min(100% - 24px, 1500px);
                padding-top: 18px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .nav-actions {
                justify-content: flex-start;
            }

            .hero {
                border-radius: 24px;
                padding: 20px;
            }

            .hero h1 { margin-top: 28px; }
            .stats-grid { grid-template-columns: 1fr; }
            .booking-card { grid-template-columns: 1fr; }
            .booking-thumb { height: 220px; }
            .booking-meta { grid-template-columns: 1fr; }
            .panel { padding: 18px; }
        }
    </style>
</head>
<body class="trk">
    <main class="page-shell">
        <header class="topbar">
            <a class="brand" href="index.php?act=khachHang/dashboard">
                <span class="brand-mark"><i class="bi bi-compass"></i></span>
                <span>DuLichPro</span>
            </a>
            <nav class="nav-actions" aria-label="Điều hướng khách hàng">
                <a class="nav-pill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>
                <a class="nav-pill" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-stars"></i> Tour nổi bật</a>
                <a class="nav-pill is-active" href="index.php?act=khachHang/yeuCauTour"><i class="bi bi-suitcase2"></i> Tour đã đặt</a>
                <a class="nav-pill" href="index.php?act=khachHang/capNhatThongTin"><i class="bi bi-person-gear"></i> Hồ sơ</a>
                <a class="nav-pill" href="index.php?act=khachHang/hoaDon"><i class="bi bi-receipt"></i> Hóa đơn</a>
            </nav>
        </header>

        <section class="hero">
            <h1>Theo dõi tour đã đặt</h1>
            <p>Quản lý booking, thông tin người tham gia, hóa đơn và nhắc nhở khởi hành trong một màn hình rõ ràng hơn.</p>
        </section>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success mt-3 mb-0">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger mt-3 mb-0">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <section class="stats-grid" aria-label="Tổng quan booking">
            <div class="stat-card">
                <span>Tổng booking</span>
                <strong><?php echo count($bookings); ?></strong>
            </div>
            <div class="stat-card">
                <span>Đang hiệu lực</span>
                <strong><?php echo count($validBookings); ?></strong>
            </div>
            <div class="stat-card">
                <span>Cần nhập người tham gia</span>
                <strong><?php echo $participantMissingCount; ?></strong>
            </div>
            <div class="stat-card">
                <span>Sắp khởi hành</span>
                <strong><?php echo count($upcomingReminders); ?></strong>
            </div>
        </section>

        <section class="content-grid">
            <div class="panel">
                <div class="panel-head">
                    <h2 class="panel-title"><i class="bi bi-suitcase2"></i> Tour đã đặt</h2>
                </div>

                <?php if (!empty($bookings)): ?>
                    <div class="booking-list">
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                                $bookingId = (int)($booking['booking_id'] ?? 0);
                                $status = (string)($booking['trang_thai'] ?? 'Khac');
                                $statusLabel = $statusLabels[$status] ?? $status;
                                $statusClass = $statusClasses[$status] ?? '';
                                $image = $resolveImage($booking['hinh_anh'] ?? '');
                                $title = (string)($booking['ten_tour'] ?? ('Booking #' . $bookingId));
                                $ngayDat = $formatDate($booking['ngay_dat'] ?? '', 'N/A');
                                $ngayKhoiHanh = $formatDate($booking['ngay_khoi_hanh'] ?? '');
                                $soNguoi = (int)($booking['so_nguoi'] ?? 0);
                                $tongTien = (float)($booking['tong_tien'] ?? 0);
                                $hasParticipants = !empty($participantsByBooking[$bookingId]);
                            ?>
                            <article class="booking-card">
                                <img class="booking-thumb" src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy">
                                <div class="booking-body">
                                    <div class="booking-title-row">
                                        <h3 class="booking-title"><?php echo htmlspecialchars($title); ?></h3>
                                        <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($statusLabel); ?></span>
                                    </div>

                                    <div class="booking-meta">
                                        <div class="meta-item"><i class="bi bi-hash"></i><span>Booking: <b>#<?php echo $bookingId; ?></b></span></div>
                                        <div class="meta-item"><i class="bi bi-calendar-plus"></i><span>Ngày đặt: <b><?php echo htmlspecialchars($ngayDat); ?></b></span></div>
                                        <div class="meta-item"><i class="bi bi-calendar-event"></i><span>Khởi hành: <b><?php echo htmlspecialchars($ngayKhoiHanh); ?></b></span></div>
                                        <div class="meta-item"><i class="bi bi-people"></i><span>Số người: <b><?php echo $soNguoi; ?></b></span></div>
                                        <div class="meta-item"><i class="bi bi-cash-coin"></i><span>Tổng tiền: <b><?php echo number_format($tongTien); ?>đ</b></span></div>
                                        <div class="meta-item"><i class="bi bi-person-vcard"></i><span>Người tham gia: <b><?php echo $hasParticipants ? 'Đã nhập' : 'Chưa nhập'; ?></b></span></div>
                                    </div>

                                    <div class="booking-actions">
                                        <a class="action-primary" href="index.php?act=khachHang/hoaDon&booking_id=<?php echo $bookingId; ?>">
                                            <i class="bi bi-receipt"></i> Xem hóa đơn
                                        </a>
                                        <a class="action-soft" href="index.php?act=khachHang/nhapThongTinThamGia&booking_id=<?php echo $bookingId; ?>">
                                            <i class="bi bi-person-vcard"></i> <?php echo $hasParticipants ? 'Cập nhật người tham gia' : 'Nhập người tham gia'; ?>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-map"></i>
                        Bạn chưa có booking nào.
                        <div class="mt-3">
                            <a class="action-primary" href="index.php?act=khachHang/danhSachTour">Khám phá tour</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <aside>
                <section class="panel">
                    <div class="panel-head">
                        <h2 class="panel-title"><i class="bi bi-people"></i> Người tham gia</h2>
                    </div>
                    <div class="side-scroll">
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $booking): ?>
                                <?php
                                    $bookingId = (int)($booking['booking_id'] ?? 0);
                                    $rows = $participantsByBooking[$bookingId] ?? [];
                                    $title = (string)($booking['ten_tour'] ?? ('Booking #' . $bookingId));
                                ?>
                                <article class="side-card">
                                    <h3>Booking #<?php echo $bookingId; ?> - <?php echo htmlspecialchars($title); ?></h3>
                                    <?php if (!empty($rows)): ?>
                                        <ol class="participant-list">
                                            <?php foreach ($rows as $row): ?>
                                                <?php
                                                    $name = trim((string)($row['ho_ten'] ?? 'Khách'));
                                                    $doc = trim((string)($row['so_cmnd'] ?? $row['so_passport'] ?? ''));
                                                ?>
                                                <li>
                                                    <?php echo htmlspecialchars($name); ?>
                                                    <?php if ($doc !== ''): ?>
                                                        - <?php echo htmlspecialchars($doc); ?>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php else: ?>
                                        <p>Chưa khai báo thông tin người tham gia.</p>
                                        <a class="action-soft" href="index.php?act=khachHang/nhapThongTinThamGia&booking_id=<?php echo $bookingId; ?>">
                                            <i class="bi bi-person-vcard"></i> Nhập thông tin
                                        </a>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state"><i class="bi bi-person-lines-fill"></i> Chưa có dữ liệu người tham gia.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <h2 class="panel-title"><i class="bi bi-alarm"></i> Nhắc khởi hành</h2>
                    </div>
                    <div class="side-scroll">
                        <?php if (!empty($upcomingReminders)): ?>
                            <?php foreach ($upcomingReminders as $remind): ?>
                                <?php
                                    $days = (int)($remind['days_left'] ?? 0);
                                    $urgent = !empty($remind['is_urgent']);
                                    $bookingId = (int)($remind['booking_id'] ?? 0);
                                    $title = (string)($remind['ten_tour'] ?? ('Booking #' . $bookingId));
                                    $ngayKhoiHanh = $formatDate($remind['ngay_khoi_hanh'] ?? '', 'N/A');
                                ?>
                                <article class="side-card <?php echo $urgent ? 'is-urgent' : ''; ?>">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <h3><?php echo htmlspecialchars($title); ?></h3>
                                        <span class="days-pill"><i class="bi bi-clock-history"></i> <?php echo $days; ?> ngày</span>
                                    </div>
                                    <p><b>Khởi hành:</b> <?php echo htmlspecialchars($ngayKhoiHanh); ?><br><b>Số người:</b> <?php echo (int)($remind['so_nguoi'] ?? 0); ?></p>
                                    <?php if ($urgent): ?>
                                        <p class="mb-0" style="color:#9f1239;"><b>Lưu ý:</b> Tour sắp khởi hành, vui lòng kiểm tra giấy tờ và lịch trình.</p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state"><i class="bi bi-calendar-check"></i> Không có tour sắp khởi hành.</div>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
