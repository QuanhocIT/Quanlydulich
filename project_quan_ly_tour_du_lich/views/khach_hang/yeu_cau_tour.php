<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo doi tour da dat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --trk-ink: #0f1d34;
            --trk-muted: #607089;
            --trk-gold: #d7ad5b;
            --trk-gold-dark: #b98a3a;
            --trk-card: rgba(255, 255, 255, 0.88);
            --trk-border: rgba(15, 30, 54, 0.12);
        }

        body.trk {
            min-height: 100vh;
            color: var(--trk-ink);
            font-family: "Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(1200px 620px at -8% -12%, rgba(215, 173, 91, 0.18), transparent 58%),
                radial-gradient(900px 520px at 110% 2%, rgba(59, 130, 246, 0.14), transparent 56%),
                linear-gradient(180deg, #f8fbff 0%, #f2f5fb 40%, #edf3fb 100%);
        }

        .trk-wrap {
            max-width: 1240px;
            margin: 0 auto;
            padding: 22px 14px 42px;
        }

        .trk-hero {
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,.5);
            overflow: hidden;
            background:
                radial-gradient(circle at 18% 28%, rgba(215, 173, 91, 0.22), transparent 44%),
                linear-gradient(115deg, rgba(8, 18, 34, 0.96), rgba(27, 48, 82, 0.88));
            box-shadow: 0 24px 70px rgba(2, 6, 23, 0.16);
            padding: 22px 24px;
            position: relative;
        }

        .trk-back {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            text-decoration: none;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.24);
            background: rgba(255,255,255,.08);
            color: rgba(255,255,255,.9);
            padding: .46rem .72rem;
            font-weight: 700;
            transition: .2s ease;
        }

        .trk-back:hover {
            color: #fff;
            background: rgba(215, 173, 91, 0.2);
            border-color: rgba(215, 173, 91, 0.44);
        }

        .trk-title {
            margin: 12px 0 6px;
            color: #fff;
            text-align: center;
            font-family: "Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
            font-size: clamp(1.7rem, 3vw, 2.5rem);
            letter-spacing: .2px;
        }

        .trk-sub {
            margin: 0;
            text-align: center;
            color: rgba(255,255,255,.82);
        }

        .trk-sub b {
            color: rgba(215, 173, 91, 0.95);
        }

        .trk-grid {
            margin-top: 18px;
            row-gap: 16px;
        }

        .trk-card {
            border-radius: 22px;
            border: 1px solid var(--trk-border);
            background: var(--trk-card);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 58px rgba(2, 6, 23, 0.12);
            padding: 18px;
            height: 100%;
        }

        .trk-card h2 {
            margin: 0 0 12px;
            display: flex;
            align-items: center;
            gap: .55rem;
            font-size: 1.24rem;
            font-weight: 900;
        }

        .trk-card h2::after {
            content: "";
            margin-left: auto;
            width: 56px;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--trk-gold), transparent);
        }

        .booking-grid {
            display: grid;
            gap: 12px;
        }

        .booking-item {
            border-radius: 16px;
            border: 1px solid rgba(15, 30, 54, 0.1);
            background: rgba(255,255,255,.78);
            padding: 10px;
            display: grid;
            grid-template-columns: 84px 1fr;
            gap: 10px;
        }

        .booking-thumb {
            width: 84px;
            height: 84px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid rgba(15,30,54,.12);
            background: #e5ebf4;
        }

        .booking-name {
            margin: 0;
            font-size: 1.04rem;
            font-weight: 900;
            color: #13213b;
        }

        .meta-row {
            font-size: .9rem;
            color: var(--trk-muted);
            margin-top: 2px;
        }

        .meta-row b {
            color: #1c2f4d;
            font-weight: 800;
        }

        .status-badge {
            border-radius: 999px;
            padding: .3rem .7rem;
            font-size: .78rem;
            font-weight: 900;
            border: 1px solid rgba(15,30,54,.14);
            background: rgba(15,30,54,.08);
            color: #223655;
            display: inline-block;
            margin-top: 8px;
        }

        .status-ChoXacNhan { background: rgba(249, 115, 22, 0.16); color: #9a3412; border-color: rgba(249, 115, 22, 0.32); }
        .status-DaCoc { background: rgba(59, 130, 246, 0.16); color: #1d4ed8; border-color: rgba(59, 130, 246, 0.32); }
        .status-HoanTat { background: rgba(16, 185, 129, 0.16); color: #047857; border-color: rgba(16, 185, 129, 0.32); }

        .participant-scroll,
        .reminder-scroll {
            max-height: 560px;
            overflow: auto;
            padding-right: 2px;
        }

        .participant-block {
            border: 1px solid rgba(15,30,54,.1);
            border-radius: 14px;
            background: rgba(255,255,255,.74);
            padding: 10px;
            margin-bottom: 10px;
        }

        .participant-head {
            font-weight: 800;
            color: #172b4b;
            margin-bottom: 6px;
            font-size: .95rem;
        }

        .participant-list {
            margin: 0;
            padding-left: 18px;
            color: #2d3e5d;
            font-size: .9rem;
            display: grid;
            gap: 4px;
        }

        .reminder-item {
            border: 1px solid rgba(15,30,54,.1);
            border-radius: 14px;
            background: rgba(255,255,255,.78);
            padding: 11px 12px;
            margin-bottom: 10px;
            position: relative;
        }

        .reminder-item.urgent {
            border-color: rgba(220, 53, 69, 0.28);
            background: rgba(220, 53, 69, 0.08);
        }

        .reminder-days {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 999px;
            padding: .2rem .6rem;
            font-size: .78rem;
            font-weight: 900;
            background: rgba(15, 30, 54, 0.1);
            color: #1d3354;
        }

        .reminder-item.urgent .reminder-days {
            background: rgba(220, 53, 69, 0.18);
            color: #9f1239;
        }

        .empty-box {
            border-radius: 16px;
            border: 1px dashed rgba(15,30,54,.22);
            background: rgba(255,255,255,.6);
            color: var(--trk-muted);
            text-align: center;
            padding: 30px 14px;
            font-weight: 600;
        }

        .helper-link {
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 999px;
            border: 1px solid rgba(185,138,58,.42);
            text-decoration: none;
            color: #865f22;
            background: rgba(215,173,91,.12);
            padding: .34rem .74rem;
            font-size: .82rem;
            font-weight: 800;
        }

        .helper-link:hover {
            color: #6e4e1d;
            background: rgba(215,173,91,.2);
        }

        @media (max-width: 991.98px) {
            .trk-card {
                padding: 14px;
            }

            .booking-item {
                grid-template-columns: 74px 1fr;
            }

            .booking-thumb {
                width: 74px;
                height: 74px;
            }
        }
    </style>
</head>
<body class="trk">
<?php
$bookings = isset($bookings) && is_array($bookings) ? $bookings : [];
$participantsByBooking = isset($participantsByBooking) && is_array($participantsByBooking) ? $participantsByBooking : [];
$upcomingReminders = isset($upcomingReminders) && is_array($upcomingReminders) ? $upcomingReminders : [];

$statusClassMap = [
    'ChoXacNhan' => 'status-ChoXacNhan',
    'DaCoc' => 'status-DaCoc',
    'HoanTat' => 'status-HoanTat',
];
?>

<div class="trk-wrap">
    <header class="trk-hero">
        <a class="trk-back" href="index.php?act=khachHang/dashboard">
            <i class="bi bi-arrow-left"></i> Trang chu
        </a>
        <h1 class="trk-title">Tour da dat cua ban</h1>
        <p class="trk-sub">Theo doi <b>booking</b>, danh sach nguoi tham gia va nhac nho tour sap khoi hanh.</p>
    </header>

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

    <div class="row trk-grid">
        <div class="col-lg-6">
            <section class="trk-card">
                <h2><i class="bi bi-suitcase2"></i> Tour da dat</h2>

                <?php if (!empty($bookings)): ?>
                    <div class="booking-grid">
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                                $bookingId = (int)($booking['booking_id'] ?? 0);
                                $status = (string)($booking['trang_thai'] ?? 'Khac');
                                $statusClass = $statusClassMap[$status] ?? '';
                                $img = trim((string)($booking['hinh_anh'] ?? ''));
                                if ($img === '') {
                                    $img = 'https://dummyimage.com/300x300/e5ebf4/8aa0be&text=Tour';
                                }
                                $ngayDat = !empty($booking['ngay_dat']) ? date('d/m/Y', strtotime($booking['ngay_dat'])) : 'N/A';
                                $ngayKhoiHanh = !empty($booking['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'Chua cap nhat';
                                $tongTien = (float)($booking['tong_tien'] ?? 0);
                            ?>
                            <article class="booking-item">
                                <img class="booking-thumb" src="<?php echo htmlspecialchars($img); ?>" alt="Anh tour" loading="lazy">
                                <div>
                                    <p class="booking-name"><?php echo htmlspecialchars((string)($booking['ten_tour'] ?? ('Booking #' . $bookingId))); ?></p>
                                    <div class="meta-row"><b>Booking:</b> #<?php echo $bookingId; ?> | <b>Ngay dat:</b> <?php echo htmlspecialchars($ngayDat); ?></div>
                                    <div class="meta-row"><b>Khoi hanh:</b> <?php echo htmlspecialchars($ngayKhoiHanh); ?> | <b>So nguoi:</b> <?php echo (int)($booking['so_nguoi'] ?? 0); ?></div>
                                    <div class="meta-row"><b>Tong tien:</b> <?php echo number_format($tongTien); ?>d</div>
                                    <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span>
                                    <div>
                                        <a class="helper-link" href="index.php?act=khachHang/hoaDon&booking_id=<?php echo $bookingId; ?>">
                                            <i class="bi bi-receipt"></i> Xem hoa don
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-box">
                        Ban chua co booking nao.
                        <div class="mt-2">
                            <a class="helper-link" href="index.php?act=khachHang/dashboard#tours"><i class="bi bi-compass"></i> Kham pha tour</a>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <div class="col-lg-3">
            <section class="trk-card">
                <h2><i class="bi bi-people"></i> Nguoi duoc dat</h2>
                <div class="participant-scroll">
                    <?php if (!empty($bookings)): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                                $bookingId = (int)($booking['booking_id'] ?? 0);
                                $rows = $participantsByBooking[$bookingId] ?? [];
                            ?>
                            <div class="participant-block">
                                <div class="participant-head">Booking #<?php echo $bookingId; ?> - <?php echo htmlspecialchars((string)($booking['ten_tour'] ?? 'Tour')); ?></div>
                                <?php if (!empty($rows)): ?>
                                    <ol class="participant-list">
                                        <?php foreach ($rows as $row): ?>
                                            <?php
                                                $name = trim((string)($row['ho_ten'] ?? 'Khach')); 
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
                                    <div class="meta-row">Chua khai bao thong tin nguoi tham gia.</div>
                                    <a class="helper-link" href="index.php?act=khachHang/nhapThongTinThamGia&booking_id=<?php echo $bookingId; ?>">
                                        <i class="bi bi-person-vcard"></i> Nhap thong tin
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-box">Chua co du lieu nguoi tham gia.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-lg-3">
            <section class="trk-card">
                <h2><i class="bi bi-alarm"></i> Nhac khoi hanh</h2>
                <div class="reminder-scroll">
                    <?php if (!empty($upcomingReminders)): ?>
                        <?php foreach ($upcomingReminders as $remind): ?>
                            <?php
                                $days = (int)($remind['days_left'] ?? 0);
                                $urgent = !empty($remind['is_urgent']);
                                $bookingId = (int)($remind['booking_id'] ?? 0);
                                $ngayKhoiHanh = !empty($remind['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($remind['ngay_khoi_hanh'])) : 'N/A';
                            ?>
                            <article class="reminder-item <?php echo $urgent ? 'urgent' : ''; ?>">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <strong><?php echo htmlspecialchars((string)($remind['ten_tour'] ?? ('Booking #' . $bookingId))); ?></strong>
                                    <span class="reminder-days"><i class="bi bi-clock-history"></i> <?php echo $days; ?> ngay</span>
                                </div>
                                <div class="meta-row mt-1"><b>Khoi hanh:</b> <?php echo htmlspecialchars($ngayKhoiHanh); ?></div>
                                <div class="meta-row"><b>So nguoi:</b> <?php echo (int)($remind['so_nguoi'] ?? 0); ?></div>
                                <?php if ($urgent): ?>
                                    <div class="meta-row" style="color:#9f1239;"><b>Luu y:</b> Tour sap khoi hanh, vui long kiem tra giay to va lich trinh.</div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-box">Khong co tour sap khoi hanh.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
