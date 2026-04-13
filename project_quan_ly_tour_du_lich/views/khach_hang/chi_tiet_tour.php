<?php
$anhDaiDien = $tour['hinh_anh'] ?? '';
if (empty($anhDaiDien) && !empty($hinhAnhList) && !empty($hinhAnhList[0]['url_anh'])) {
    $anhDaiDien = $hinhAnhList[0]['url_anh'];
}
if (empty($anhDaiDien)) {
    $anhDaiDien = 'https://images.unsplash.com/photo-1465156799763-2c087c332922?auto=format&fit=crop&w=800&q=80';
}

// Chọn lịch khởi hành ưu tiên: lịch từ hôm nay trở đi, nếu không có thì lấy lịch mới nhất.
$lichKhoiHanhHienThi = null;
$today = date('Y-m-d');
if (!empty($lichKhoiHanhList)) {
    foreach ($lichKhoiHanhList as $lk) {
        if (!empty($lk['ngay_khoi_hanh']) && $lk['ngay_khoi_hanh'] >= $today) {
            $lichKhoiHanhHienThi = $lk;
            break;
        }
    }

    if ($lichKhoiHanhHienThi === null) {
        $lichKhoiHanhHienThi = end($lichKhoiHanhList);
        reset($lichKhoiHanhList);
    }
}

$maTourHienThi = trim((string)($tour['ma_tour'] ?? ''));
if ($maTourHienThi === '') {
    $tourId = (int)($tour['tour_id'] ?? $tour['id'] ?? 0);
    $maTourHienThi = $tourId > 0 ? 'TOUR-' . str_pad((string)$tourId, 4, '0', STR_PAD_LEFT) : 'N/A';
}

$khoiHanhHienThi = trim((string)($tour['noi_khoi_hanh'] ?? ''));
if ($khoiHanhHienThi === '' && !empty($lichKhoiHanhHienThi['diem_tap_trung'])) {
    $khoiHanhHienThi = $lichKhoiHanhHienThi['diem_tap_trung'];
}
if ($khoiHanhHienThi === '') {
    $khoiHanhHienThi = 'Chưa cập nhật';
}

$ngayKhoiHanhHienThi = '';
if (!empty($lichKhoiHanhHienThi['ngay_khoi_hanh'])) {
    $ngayKhoiHanhHienThi = date('d-m-Y', strtotime($lichKhoiHanhHienThi['ngay_khoi_hanh']));
}

$khoiHanhSauHienThi = 'Chưa cập nhật';
$isBookingLockedBy48h = false;
if (!empty($lichKhoiHanhHienThi['ngay_khoi_hanh'])) {
    $departureTs = strtotime($lichKhoiHanhHienThi['ngay_khoi_hanh'] . ' 00:00:00');
    if ($departureTs !== false) {
        $secondsLeft = $departureTs - time();
        if ($secondsLeft <= 0) {
            $khoiHanhSauHienThi = 'Đã khởi hành';
            $isBookingLockedBy48h = true;
        } else {
            $daysLeft = (int)ceil($secondsLeft / 86400);
            $khoiHanhSauHienThi = $daysLeft . ' ngày';
            $isBookingLockedBy48h = $secondsLeft <= (48 * 3600);
        }
    }
}

$thoiGianHienThi = trim((string)($tour['thoi_gian'] ?? ''));
if ($thoiGianHienThi === '' && !empty($lichKhoiHanhHienThi['ngay_khoi_hanh']) && !empty($lichKhoiHanhHienThi['ngay_ket_thuc'])) {
    $start = strtotime($lichKhoiHanhHienThi['ngay_khoi_hanh']);
    $end = strtotime($lichKhoiHanhHienThi['ngay_ket_thuc']);
    if ($start && $end && $end >= $start) {
        $soNgay = (int)floor(($end - $start) / 86400) + 1;
        $thoiGianHienThi = $soNgay . ' ngày';
    }
}
if ($thoiGianHienThi === '') {
    $thoiGianHienThi = 'Chưa cập nhật';
}

$soChoToiDa = null;
$soChoConLai = null;
if (!empty($lichKhoiHanhHienThi)) {
    $soChoToiDa = isset($lichKhoiHanhHienThi['so_cho_toi_da'])
        ? (int)$lichKhoiHanhHienThi['so_cho_toi_da']
        : (isset($lichKhoiHanhHienThi['so_cho']) ? (int)$lichKhoiHanhHienThi['so_cho'] : null);
    $soChoConLai = isset($lichKhoiHanhHienThi['so_cho_con_lai'])
        ? (int)$lichKhoiHanhHienThi['so_cho_con_lai']
        : null;
}

if ($soChoToiDa === null || $soChoToiDa <= 0) {
    $soChoToiDa = isset($tour['so_cho']) ? (int)$tour['so_cho'] : null;
}

if ($soChoConLai === null && $soChoToiDa !== null) {
    $soChoConLai = $soChoToiDa;
}

$soChoHienThi = 'Chưa cập nhật';
if ($soChoConLai !== null && $soChoToiDa !== null && $soChoToiDa > 0) {
    $soChoConLai = max(0, $soChoConLai);
    $soChoHienThi = $soChoConLai . '/' . $soChoToiDa;
} elseif ($soChoToiDa !== null && $soChoToiDa > 0) {
    $soChoHienThi = (string)$soChoToiDa;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --lx-bg:#0b1220;
            --lx-bg2:#070d18;
            --lx-ink:#0f172a;
            --lx-muted:#64748b;
            --lx-gold:#d6b26d;
            --lx-gold2:#b9893d;
            --lx-card: rgba(255,255,255,.84);
        }
        body.tourlux{
            font-family:"Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            color:var(--lx-ink);
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(214,178,109,.14), transparent 60%),
                radial-gradient(900px 520px at 85% 0%, rgba(11,18,32,.08), transparent 55%),
                #f5f6f8;
            min-height:100vh;
        }
        body.tourlux h1, body.tourlux h2, body.tourlux h3, body.tourlux .tourlux-hero-title{
            font-family:"Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
        }

        .tourlux-hero{
            position:relative;
            background-image:
                linear-gradient(90deg, rgba(11,18,32,.92) 0%, rgba(11,18,32,.72) 46%, rgba(11,18,32,.40) 100%),
                radial-gradient(900px 520px at 15% 20%, rgba(214,178,109,.14), transparent 55%),
                var(--hero-img);
            background-size:cover;
            background-position:center;
            color:#fff;
            border-radius: 0 0 26px 26px;
            overflow:hidden;
        }
        .tourlux-hero::after{
            content:"";
            position:absolute;
            inset:0;
            background: radial-gradient(1400px 540px at 30% 0%, rgba(214,178,109,.10), transparent 60%);
            pointer-events:none;
        }
        .tourlux-hero .container{
            position:relative;
            z-index:2;
            padding-top: 24px;
            padding-bottom: 34px;
        }
        .tourlux-back{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            color:rgba(255,255,255,.86);
            text-decoration:none;
            padding:.45rem .7rem;
            border-radius:999px;
            border:1px solid rgba(255,255,255,.18);
            background:rgba(255,255,255,.06);
            backdrop-filter: blur(10px);
            transition:.18s;
        }
        .tourlux-back:hover{
            color:#fff;
            background:rgba(214,178,109,.14);
            border-color:rgba(214,178,109,.32);
        }
        .tourlux-kicker{
            letter-spacing:.18em;
            text-transform:uppercase;
            color:rgba(214,178,109,.95);
            font-weight:800;
            font-size:.8rem;
            margin-top: 16px;
        }
        .tourlux-hero-title{
            font-size: clamp(1.7rem, 4vw, 2.6rem);
            line-height:1.08;
            margin:.3rem 0 .6rem 0;
            letter-spacing:.2px;
        }
        .tourlux-meta{
            display:flex;
            flex-wrap:wrap;
            gap:.55rem;
            margin-top:.5rem;
        }
        .chip{
            display:inline-flex;
            align-items:center;
            gap:.45rem;
            padding:.45rem .7rem;
            border-radius:999px;
            border:1px solid rgba(255,255,255,.18);
            background:rgba(255,255,255,.06);
            backdrop-filter: blur(10px);
            color:rgba(255,255,255,.88);
            font-weight:600;
            font-size:.92rem;
        }
        .chip i{ color: rgba(214,178,109,.95); }

        .panel{
            background:var(--lx-card);
            border:1px solid rgba(255,255,255,.58);
            box-shadow:0 26px 80px rgba(2,6,23,.12);
            border-radius:20px;
            backdrop-filter: blur(14px);
        }
        .panel-body{ padding: 18px 18px; }
        @media (min-width: 992px){
            .panel-body{ padding: 22px 22px; }
        }
        .panel-title{
            font-weight:800;
            letter-spacing:.2px;
            margin:0;
            display:flex;
            align-items:center;
            gap:.55rem;
        }
        .panel-title i{ color: var(--lx-gold2); }
        .panel-title::after{
            content:"";
            display:block;
            height:2px;
            width:56px;
            margin-left:auto;
            background:linear-gradient(90deg,var(--lx-gold), transparent);
            border-radius:999px;
            opacity:.95;
        }
        .text-muted-2{ color: var(--lx-muted) !important; }

        .tourlux-mainimg{
            width:100%;
            height: min(520px, 55vw);
            object-fit:cover;
            border-radius:18px;
            border:1px solid rgba(255,255,255,.40);
            box-shadow:0 22px 70px rgba(2,6,23,.18);
        }
        @media (max-width: 576px){
            .tourlux-mainimg{ height: 260px; }
        }
        .tourlux-thumbs{
            display:flex;
            gap:.55rem;
            overflow:auto;
            padding:.6rem .15rem .15rem .15rem;
            scroll-snap-type:x mandatory;
        }
        .tourlux-thumbs::-webkit-scrollbar{ height:10px; }
        .tourlux-thumbs::-webkit-scrollbar-track{
            background:rgba(15,23,42,.08);
            border-radius:999px;
        }
        .tourlux-thumbs::-webkit-scrollbar-thumb{
            background:linear-gradient(90deg, rgba(11,18,32,.65), rgba(20,38,68,.72));
            border-radius:999px;
            border:2px solid rgba(245,246,248,.92);
        }
        .tourlux-thumb{
            width:96px;
            height:76px;
            flex:0 0 auto;
            object-fit:cover;
            border-radius:12px;
            cursor:pointer;
            border:1px solid rgba(15,23,42,.12);
            opacity:.86;
            transition:.18s;
            scroll-snap-align:start;
        }
        .tourlux-thumb:hover{ opacity:1; transform: translateY(-2px); }
        .tourlux-thumb.is-active{
            opacity:1;
            border-color: rgba(214,178,109,.55);
            box-shadow:0 10px 28px rgba(185,137,61,.18);
        }

        .table{
            overflow:hidden;
            border-radius:16px;
            border:1px solid rgba(15,23,42,.10);
            background:#fff;
        }
        .table thead th{
            background:rgba(11,18,32,.03);
            color:#0f172a;
            font-weight:800;
            vertical-align:middle;
        }
        .table td{ vertical-align:middle; }

        .price{
            font-size:2.1rem;
            font-weight:900;
            letter-spacing:.3px;
            color:#b42318;
            margin:0;
        }
        .price small{
            font-weight:700;
            color:#0f172a;
            font-size:1rem;
        }
        .promo{
            border-radius:16px;
            border:1px dashed rgba(180,35,24,.28);
            background:rgba(180,35,24,.06);
            padding:12px 12px;
            display:flex;
            gap:.65rem;
            align-items:flex-start;
        }
        .promo i{ color:#b42318; font-size:1.35rem; line-height:1; margin-top:2px; }
        .promo b{ color:#b42318; }

        .btn-lux{
            background:linear-gradient(135deg,var(--lx-bg), #142644);
            border:none;
            color:#fff;
            font-weight:800;
            border-radius:14px;
            box-shadow:0 16px 46px rgba(11,18,32,.22);
        }
        .btn-lux:hover{ filter:brightness(1.04); color:#fff; }
        .btn-lux-outline{
            border:1px solid rgba(214,178,109,.55);
            color:var(--lx-gold2);
            background:rgba(255,255,255,.55);
            font-weight:800;
            border-radius:14px;
        }
        .btn-lux-outline:hover{
            background:rgba(214,178,109,.12);
            color:#7a561f;
        }

        .tourlux-sticky{
            top: 18px;
            z-index: 20;
        }
        @media (min-width: 992px){
            .tourlux-sticky{ top: 28px; }
        }

        .tour-info-box{
            background:rgba(255,255,255,.90);
            border:1px solid rgba(15,23,42,.10);
            border-radius:18px;
            padding: 14px 14px;
        }
        .tour-info-head{
            display:flex;
            align-items:center;
            gap:.55rem;
            font-weight:900;
            color:rgba(11,18,32,.92);
            margin:0 0 .75rem 0;
            font-family:"Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
        }
        .tour-info-head i{
            color:#0d6efd;
            font-size:1.2rem;
        }
        .tour-info-row{
            display:grid;
            grid-template-columns: 140px 1fr;
            column-gap:.6rem;
            line-height:1.35;
            padding:.28rem 0;
            align-items:start;
        }
        .tour-info-row .k{
            color:rgba(100,116,139,.98);
            font-weight:800;
            white-space:normal;
        }
        .tour-info-row .v{
            color:rgba(15,23,42,.92);
            font-weight:700;
            min-width:0;
            word-break:break-word;
        }
        .tour-info-row.price{
            margin-top:.45rem;
            padding-top:.6rem;
            border-top:1px dashed rgba(15,23,42,.14);
            grid-template-columns: 1fr auto;
            align-items:baseline;
        }
        .tour-info-row.price .k{
            font-size:1.35rem;
            font-weight:900;
            color:rgba(100,116,139,.92);
        }
        .tour-info-row.price .v{
            color:#b42318;
            font-weight:900;
            font-size:1.35rem;
            justify-self:end;
        }
        @media (max-width: 576px){
            .tour-info-row{ grid-template-columns: 120px 1fr; }
            .tour-info-row.price .k,
            .tour-info-row.price .v{
                font-size:1.15rem;
            }
        }

        /* RELATED TOURS */
        .related-scroller{
            display:flex;
            gap:14px;
            overflow:auto;
            padding:.55rem .15rem .15rem .15rem;
            scroll-snap-type:x mandatory;
        }
        .related-scroller::-webkit-scrollbar{ height:10px; }
        .related-scroller::-webkit-scrollbar-track{
            background:rgba(15,23,42,.08);
            border-radius:999px;
        }
        .related-scroller::-webkit-scrollbar-thumb{
            background:linear-gradient(90deg, rgba(11,18,32,.65), rgba(20,38,68,.72));
            border-radius:999px;
            border:2px solid rgba(245,246,248,.92);
        }
        .related-card{
            flex:0 0 260px;
            scroll-snap-align:start;
            border-radius:18px;
            overflow:hidden;
            border:1px solid rgba(255,255,255,.58);
            background:rgba(255,255,255,.78);
            box-shadow:0 18px 54px rgba(2,6,23,.10);
            transition: transform .18s, box-shadow .18s;
        }
        .related-card:hover{
            transform: translateY(-3px);
            box-shadow:0 24px 70px rgba(2,6,23,.13);
        }
        .related-card img{
            width:100%;
            height:150px;
            object-fit:cover;
            display:block;
            filter:saturate(1.05) contrast(1.02);
        }
        .related-body{ padding: 12px 12px 14px 12px; }
        .related-name{
            font-weight:900;
            letter-spacing:.1px;
            margin:0 0 6px 0;
            line-height:1.2;
            font-size:1.02rem;
        }
        .related-meta{
            color:var(--lx-muted);
            font-size:.95rem;
            min-height: 38px;
            margin-bottom:10px;
        }
        .related-price{
            font-weight:900;
            color:#b42318;
        }
        @media (min-width: 992px){
            .related-scroller{ flex-wrap:wrap; overflow:visible; }
            .related-card{ flex:1 1 calc(33.333% - 14px); max-width:calc(33.333% - 14px); }
        }

        /* REVIEWS */
        .reviews-head{
            display:flex;
            flex-wrap:wrap;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            margin-top:2px;
        }
        .rating-badge{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            padding:.4rem .7rem;
            border-radius:999px;
            border:1px solid rgba(214,178,109,.45);
            background:rgba(214,178,109,.12);
            color:rgba(11,18,32,.92);
            font-weight:900;
        }
        .rating-stars i{ color: rgba(214,178,109,.95); }
        .review-grid{
            display:grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap:14px;
            margin-top: 14px;
        }
        .review-card{
            border-radius:18px;
            border:1px solid rgba(255,255,255,.58);
            background:rgba(255,255,255,.76);
            box-shadow:0 18px 54px rgba(2,6,23,.10);
            padding: 14px 14px;
        }
        .review-top{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
        }
        .avatar{
            width:40px;
            height:40px;
            border-radius:999px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:linear-gradient(135deg, rgba(11,18,32,.86), rgba(20,38,68,.72));
            color:#fff;
            font-weight:900;
            letter-spacing:.4px;
            flex:0 0 auto;
        }
        .review-name{
            font-weight:900;
            margin:0;
            line-height:1.1;
        }
        .review-date{
            color:var(--lx-muted);
            font-size:.9rem;
        }
        .review-body{
            color:rgba(15,23,42,.92);
            margin-top:10px;
            line-height:1.5;
        }
    </style>
</head>
<body class="tourlux">
    <header class="tourlux-hero" style="--hero-img: url('<?php echo htmlspecialchars($anhDaiDien); ?>');">
        <div class="container">
            <a class="tourlux-back" href="index.php?act=khachHang/dashboard">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>

            <div class="tourlux-kicker">Chi tiết tour</div>
            <div class="row align-items-end g-4 mt-0">
                <div class="col-lg-8">
                    <h1 class="tourlux-hero-title"><?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tên tour'); ?></h1>

                    <div class="tourlux-meta">
                        <?php if (!empty($tour['loai_tour'])): ?>
                            <span class="chip"><i class="bi bi-globe2"></i> <?php echo htmlspecialchars($tour['loai_tour']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($khoiHanhHienThi) && $khoiHanhHienThi !== 'Chưa cập nhật'): ?>
                            <span class="chip"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($khoiHanhHienThi); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($thoiGianHienThi) && $thoiGianHienThi !== 'Chưa cập nhật'): ?>
                            <span class="chip"><i class="bi bi-clock-history"></i> <?php echo htmlspecialchars($thoiGianHienThi); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($soChoHienThi) && $soChoHienThi !== 'Chưa cập nhật'): ?>
                            <span class="chip"><i class="bi bi-people"></i> Còn <?php echo htmlspecialchars((string)$soChoHienThi); ?> chỗ</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex align-items-center justify-content-lg-end gap-2">
                        <?php if ($isBookingLockedBy48h): ?>
                            <button type="button" class="btn btn-lux-outline px-3 py-2" disabled>
                                <i class="bi bi-lock me-1"></i> Đặt nhanh đã khóa
                            </button>
                            <button type="button" class="btn btn-lux px-3 py-2" disabled>
                                <i class="bi bi-calendar-x me-1"></i> Ngừng nhận đặt
                            </button>
                        <?php else: ?>
                            <a class="btn btn-lux-outline px-3 py-2" href="#dat-tour">
                                <i class="bi bi-lightning-charge me-1"></i> Đặt nhanh
                            </a>
                            <a class="btn btn-lux px-3 py-2" href="index.php?act=khachHang/thanhToanTour&id=<?php echo $tour['tour_id'] ?? $tour['id']; ?>">
                                <i class="bi bi-cart-plus me-1"></i> Thanh toán
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="container my-4 my-lg-5">
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="panel mb-4">
                    <div class="panel-body">
                        <img id="mainTourImage" src="<?php echo htmlspecialchars($anhDaiDien); ?>" class="tourlux-mainimg" alt="Ảnh tour">

                        <?php if (!empty($hinhAnhList)): ?>
                            <div class="tourlux-thumbs mt-2" aria-label="Thư viện ảnh tour">
                                <?php foreach ($hinhAnhList as $idx => $ha): ?>
                                    <?php
                                        $urlAnh = $ha['url_anh'] ?? '';
                                        if (empty($urlAnh)) continue;
                                        $active = ($idx === 0) ? ' is-active' : '';
                                    ?>
                                    <img
                                        class="tourlux-thumb<?php echo $active; ?>"
                                        src="<?php echo htmlspecialchars($urlAnh); ?>"
                                        data-full="<?php echo htmlspecialchars($urlAnh); ?>"
                                        alt="Hình ảnh tour"
                                        loading="lazy"
                                    >
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel mb-4">
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-info-circle"></i> Mô tả tour</h3>
                        <div class="mt-3 text-muted-2">
                            <?php echo nl2br(htmlspecialchars($tour['mo_ta'] ?? 'Chưa có mô tả.')); ?>
                        </div>
                    </div>
                </div>

                <div class="panel mb-4">
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-calendar-event"></i> Thông tin khởi hành</h3>
                        <div class="mt-3">
                            <?php if (!empty($lichKhoiHanhList)): ?>
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ngày khởi hành</th>
                                            <th>Ngày kết thúc</th>
                                            <th>Điểm tập trung</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($lichKhoiHanhList as $lk): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($lk['ngay_khoi_hanh'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($lk['ngay_ket_thuc'])); ?></td>
                                            <td><?php echo htmlspecialchars($lk['diem_tap_trung']); ?></td>
                                            <td><?php echo htmlspecialchars($lk['trang_thai']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-muted-2">Chưa có lịch khởi hành.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="panel mb-4">
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-person-badge-fill"></i> Hướng dẫn viên</h3>
                        <div class="mt-3">
                            <?php if (!empty($tour['hdv_info'])): ?>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-muted-2">
                                    <span class="chip" style="border-color: rgba(15,23,42,.12); background: rgba(255,255,255,.6); color: var(--lx-ink);">
                                        <i class="bi bi-person-circle"></i>
                                        <b><?php echo htmlspecialchars($tour['hdv_info']['ho_ten'] ?? ''); ?></b>
                                    </span>
                                    <span class="chip" style="border-color: rgba(15,23,42,.12); background: rgba(255,255,255,.6); color: var(--lx-ink);">
                                        <i class="bi bi-envelope-at"></i>
                                        <?php echo htmlspecialchars($tour['hdv_info']['email'] ?? ''); ?>
                                    </span>
                                    <span class="chip" style="border-color: rgba(15,23,42,.12); background: rgba(255,255,255,.6); color: var(--lx-ink);">
                                        <i class="bi bi-telephone"></i>
                                        <?php echo htmlspecialchars($tour['hdv_info']['so_dien_thoai'] ?? ''); ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="text-muted-2"><i class="bi bi-person-x-fill me-1"></i> Chưa có thông tin hướng dẫn viên.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="panel mb-4">
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-list-task"></i> Lịch trình chi tiết</h3>
                        <div class="mt-3">
                            <?php if (!empty($lichTrinhList)): ?>
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Địa điểm</th>
                                            <th>Hoạt động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($lichTrinhList as $lt): ?>
                                        <tr>
                                            <td><?php echo $lt['ngay_thu']; ?></td>
                                            <td><?php echo htmlspecialchars($lt['dia_diem']); ?></td>
                                            <td><?php echo htmlspecialchars($lt['hoat_dong']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-muted-2"><i class="bi bi-x-circle me-1"></i> Chưa cập nhật lịch trình.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($tour['nhat_ky'])): ?>
                    <div class="panel mb-4">
                        <div class="panel-body">
                            <h3 class="panel-title"><i class="bi bi-journal-text"></i> Nhật ký tour</h3>
                            <ul class="mt-3 mb-0 text-muted-2">
                                <?php foreach ($tour['nhat_ky'] as $nk): ?>
                                    <li><?php echo htmlspecialchars($nk['noi_dung']); ?> (<?php echo date('d/m/Y', strtotime($nk['ngay_ghi'])); ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tour['yeu_cau_dac_biet'])): ?>
                    <div class="panel mb-4">
                        <div class="panel-body">
                            <h3 class="panel-title"><i class="bi bi-star-fill"></i> Yêu cầu đặc biệt</h3>
                            <ul class="mt-3 mb-0 text-muted-2">
                                <?php foreach ($tour['yeu_cau_dac_biet'] as $yc): ?>
                                    <li><?php echo htmlspecialchars($yc['mo_ta']); ?> (Mức độ: <?php echo htmlspecialchars($yc['muc_do_uu_tien']); ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-2 mt-2 mb-4">
                    <?php if ($isBookingLockedBy48h): ?>
                        <button type="button" class="btn btn-lux px-4 py-3" disabled>
                            <i class="bi bi-calendar-x me-1"></i> Tour đã khóa đặt (trước 48h)
                        </button>
                    <?php else: ?>
                        <a href="index.php?act=khachHang/thanhToanTour&id=<?php echo $tour['tour_id'] ?? $tour['id']; ?>" class="btn btn-lux px-4 py-3">
                            <i class="bi bi-cart-plus me-1"></i> Đặt tour ngay
                        </a>
                    <?php endif; ?>
                    <a href="index.php?act=khachHang/dashboard" class="btn btn-lux-outline px-4 py-3">
                        <i class="bi bi-house-door me-1"></i> Về trang chủ
                    </a>
                </div>
            </div>

            <div class="col-lg-4" id="dat-tour">
                <div class="position-sticky tourlux-sticky">
                    <div class="panel">
                        <div class="panel-body">
                            <div class="tour-info-box mb-3">
                                <div class="tour-info-head"><i class="bi bi-info-circle"></i> Thông tin tour</div>
                                <div class="tour-info-row"><span class="k">Tên tour:</span><span class="v"><?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?></span></div>
                                <div class="tour-info-row"><span class="k">Mã tour:</span><span class="v"><?php echo htmlspecialchars($maTourHienThi); ?></span></div>
                                <div class="tour-info-row"><span class="k">Loại tour:</span><span class="v"><?php echo htmlspecialchars($tour['loai_tour'] ?? ''); ?></span></div>
                                <div class="tour-info-row"><span class="k">Khởi hành:</span><span class="v"><?php echo htmlspecialchars($khoiHanhHienThi); ?></span></div>
                                <div class="tour-info-row"><span class="k">Ngày khởi hành:</span><span class="v"><?php echo htmlspecialchars($ngayKhoiHanhHienThi !== '' ? $ngayKhoiHanhHienThi : 'Chưa cập nhật'); ?></span></div>
                                <div class="tour-info-row"><span class="k">Khởi hành sau:</span><span class="v"><?php echo htmlspecialchars($khoiHanhSauHienThi); ?></span></div>
                                <div class="tour-info-row"><span class="k">Thời gian:</span><span class="v"><?php echo htmlspecialchars($thoiGianHienThi); ?></span></div>
                                <div class="tour-info-row"><span class="k">Số chỗ còn:</span><span class="v"><?php echo htmlspecialchars((string)$soChoHienThi); ?></span></div>
                                <div class="tour-info-row price"><span class="k">Giá/khách:</span><span class="v"><?php echo number_format($tour['gia_tour'] ?? $tour['gia_co_ban'] ?? 0); ?>đ</span></div>
                            </div>

                            <?php if ($isBookingLockedBy48h): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Tour này chỉ nhận đặt trước ít nhất 48 giờ so với ngày khởi hành.
                                </div>
                            <?php endif; ?>

                            <div class="promo mb-3">
                                <i class="bi bi-gift"></i>
                                <div class="text-muted-2">
                                    Đặt ngay để nhận ưu đãi giờ chót, tiết kiệm thêm <b>1,000K</b>.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <?php if ($isBookingLockedBy48h): ?>
                                    <button type="button" class="btn btn-lux py-3" disabled>
                                        <i class="bi bi-lock me-1"></i> Tạm khóa đặt tour
                                    </button>
                                <?php else: ?>
                                    <a class="btn btn-lux py-3" href="index.php?act=khachHang/thanhToanTour&id=<?php echo $tour['tour_id'] ?? $tour['id']; ?>">
                                        <i class="bi bi-cart-check me-1"></i> Đặt ngay và thanh toán
                                    </a>
                                <?php endif; ?>
                                <a class="btn btn-lux-outline py-3" href="index.php?act=khachHang/dashboard">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Xem tour khác
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($danhGiaTourList)): ?>
            <div class="panel mb-4">
                <div class="panel-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h3 class="panel-title"><i class="bi bi-chat-quote"></i> Phản hồi khách hàng</h3>
                        <div class="rating-badge">
                            <span><?php echo number_format((float)$danhGiaTourAvg, 1); ?>/5</span>
                            <span class="text-muted-2" style="font-weight:800;">(<?php echo (int)$danhGiaTourCount; ?> đánh giá)</span>
                        </div>
                    </div>

                    <div class="review-grid">
                        <?php foreach (array_slice($danhGiaTourList, 0, 6) as $dg): ?>
                            <?php
                                $ten = trim((string)($dg['ho_ten'] ?? $dg['ten_khach_hang'] ?? 'Ẩn danh'));
                                $initial = 'U';
                                if ($ten !== '') {
                                    $initial = strtoupper(substr($ten, 0, 1));
                                }
                                $diem = (int)($dg['diem'] ?? 0);
                                if ($diem < 0) $diem = 0;
                                if ($diem > 5) $diem = 5;
                                $noiDung = trim((string)($dg['noi_dung'] ?? ''));
                                $ngay = !empty($dg['ngay_danh_gia']) ? date('d/m/Y', strtotime($dg['ngay_danh_gia'])) : '';
                            ?>
                            <article class="review-card">
                                <div class="review-top">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar" aria-hidden="true"><?php echo htmlspecialchars($initial); ?></div>
                                        <div>
                                            <p class="review-name mb-1"><?php echo htmlspecialchars($ten); ?></p>
                                            <?php if ($ngay !== ''): ?>
                                                <div class="review-date"><i class="bi bi-clock me-1"></i><?php echo htmlspecialchars($ngay); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="rating-stars" aria-label="Đánh giá">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $diem): ?>
                                                <i class="bi bi-star-fill"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <?php if ($noiDung !== ''): ?>
                                    <div class="review-body">“<?php echo htmlspecialchars($noiDung); ?>”</div>
                                <?php else: ?>
                                    <div class="review-body text-muted-2">Khách hàng chưa để lại nội dung.</div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="panel mb-4">
            <div class="panel-body">
                <h3 class="panel-title"><i class="bi bi-pencil-square"></i> Đánh giá tour này</h3>

                <?php if (!empty($coTheDanhGiaTour)): ?>
                    <?php
                        $existingDiem = !empty($tourReviewCurrentUser['diem']) ? (int)$tourReviewCurrentUser['diem'] : 0;
                        $existingNoiDung = trim((string)($tourReviewCurrentUser['noi_dung'] ?? ''));
                    ?>
                    <?php if (!empty($daDanhGiaTourNay)): ?>
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Bạn đã đánh giá tour này. Bạn có thể chỉnh sửa đánh giá bên dưới.
                        </div>
                    <?php endif; ?>
                    <form class="mt-3" method="post" action="index.php?act=khachHang/guiDanhGia">
                        <input type="hidden" name="loai_danh_gia" value="Tour">
                        <input type="hidden" name="tour_id" value="<?php echo (int)($tour['tour_id'] ?? 0); ?>">
                        <input type="hidden" name="tieu_chi" value="ChatLuongTour">
                        <input type="hidden" name="redirect_tour_id" value="<?php echo (int)($tour['tour_id'] ?? 0); ?>">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Điểm đánh giá</label>
                                <select class="form-select" name="diem" required>
                                    <option value="" <?php echo $existingDiem <= 0 ? 'selected' : ''; ?>>Chọn điểm</option>
                                    <option value="5" <?php echo $existingDiem === 5 ? 'selected' : ''; ?>>5 - Rất hài lòng</option>
                                    <option value="4" <?php echo $existingDiem === 4 ? 'selected' : ''; ?>>4 - Hài lòng</option>
                                    <option value="3" <?php echo $existingDiem === 3 ? 'selected' : ''; ?>>3 - Bình thường</option>
                                    <option value="2" <?php echo $existingDiem === 2 ? 'selected' : ''; ?>>2 - Chưa hài lòng</option>
                                    <option value="1" <?php echo $existingDiem === 1 ? 'selected' : ''; ?>>1 - Không hài lòng</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nội dung đánh giá</label>
                                <textarea class="form-control" name="noi_dung" rows="4" maxlength="1000" required placeholder="Chia sẻ trải nghiệm thực tế của bạn về tour..."><?php echo htmlspecialchars($existingNoiDung); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-lux px-4">
                                <i class="bi bi-send me-1"></i> <?php echo !empty($daDanhGiaTourNay) ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?>
                            </button>
                            <span class="text-muted-2 align-self-center">Chỉ hiển thị khi bạn đã đặt và hoàn thành tour.</span>
                        </div>
                    </form>
                <?php elseif (empty($daDatTourNay)): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Bạn cần đặt tour này trước khi có thể đánh giá.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-clock-history me-1"></i>
                        Bạn đã đặt tour, vui lòng đánh giá sau khi đã trải nghiệm xong chuyến đi.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($tourCungLoai)): ?>
            <div class="panel mb-4">
                <div class="panel-body">
                    <h3 class="panel-title"><i class="bi bi-stars"></i> Tour cùng loại</h3>
                    <div class="related-scroller mt-2" aria-label="Danh sách tour cùng loại">
                        <?php foreach ($tourCungLoai as $t): ?>
                            <?php
                                $tid = $t['tour_id'] ?? $t['id'] ?? '';
                                if ($tid === '') continue;
                                $img = $t['hinh_anh'] ?? 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=60';
                                $gia = $t['gia_tour'] ?? $t['gia_co_ban'] ?? 0;
                                $moTa = $t['mo_ta_ngan'] ?? $t['mo_ta'] ?? '';
                                $moTa = trim((string)$moTa);
                                if (function_exists('mb_strlen') && mb_strlen($moTa) > 80) {
                                    $moTa = (function_exists('mb_substr') ? mb_substr($moTa, 0, 80) : substr($moTa, 0, 80)) . '...';
                                }
                                if ($moTa === '') $moTa = 'Gợi ý tour phù hợp cùng loại, ưu đãi tốt.';
                            ?>
                            <article class="related-card">
                                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($t['ten_tour'] ?? 'Tour'); ?>" loading="lazy">
                                <div class="related-body">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <h4 class="related-name"><?php echo htmlspecialchars($t['ten_tour'] ?? 'Tour'); ?></h4>
                                        <?php if (!empty($t['loai_tour'])): ?>
                                            <span class="badge" style="background:rgba(214,178,109,.22); color:#7a561f; border:1px solid rgba(214,178,109,.35);">
                                                <?php echo htmlspecialchars($t['loai_tour']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="related-meta"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($moTa); ?></div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="related-price"><?php echo number_format((float)$gia); ?>đ</div>
                                        <a class="btn btn-lux-outline btn-sm px-3" href="index.php?act=khachHang/chiTietTour&id=<?php echo urlencode((string)$tid); ?>">
                                            <i class="bi bi-info-circle me-1"></i> Xem
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var mainImg = document.getElementById('mainTourImage');
            if (!mainImg) return;

            var thumbs = document.querySelectorAll('.tourlux-thumb');
            if (!thumbs || thumbs.length === 0) return;

            var setActive = function (el) {
                thumbs.forEach(function (t) { t.classList.remove('is-active'); });
                el.classList.add('is-active');
            };

            thumbs.forEach(function (thumb) {
                thumb.addEventListener('click', function () {
                    var full = thumb.getAttribute('data-full') || thumb.getAttribute('src');
                    if (!full) return;
                    mainImg.setAttribute('src', full);
                    setActive(thumb);
                });
            });
        })();
    </script>
</body>
</html>
