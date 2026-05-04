<?php
/** @var array $tour */
$tour             = $tour ?? [];
$danhGiaTourAvg   = $danhGiaTourAvg ?? 0.0;
$danhGiaTourCount = $danhGiaTourCount ?? 0;
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

$seatPercentRemain = null;
if ($soChoConLai !== null && $soChoToiDa !== null && $soChoToiDa > 0) {
    $seatPercentRemain = (int)round(($soChoConLai / $soChoToiDa) * 100);
    if ($seatPercentRemain < 0) {
        $seatPercentRemain = 0;
    }
    if ($seatPercentRemain > 100) {
        $seatPercentRemain = 100;
    }
}

$soKhachDaDatHienThi = null;
$tiLeLapDayHienThi = null;
if ($soChoConLai !== null && $soChoToiDa !== null && $soChoToiDa > 0) {
    $soKhachDaDatHienThi = max(0, $soChoToiDa - $soChoConLai);
    $tiLeLapDayHienThi = (int)round(($soKhachDaDatHienThi / $soChoToiDa) * 100);
}

$danhGiaAvgHero = isset($danhGiaTourAvg) ? (float)$danhGiaTourAvg : 0;
$danhGiaCountHero = isset($danhGiaTourCount) ? (int)$danhGiaTourCount : 0;
if ($danhGiaCountHero <= 0 && !empty($danhGiaTourList) && is_array($danhGiaTourList)) {
    $danhGiaCountHero = count($danhGiaTourList);
}
$hdvHienThi = !empty($hdvInfo) && is_array($hdvInfo) ? $hdvInfo : (!empty($tour['hdv_info']) && is_array($tour['hdv_info']) ? $tour['hdv_info'] : null);
$nhatKyHienThi = !empty($nhatKyList) && is_array($nhatKyList) ? $nhatKyList : (!empty($tour['nhat_ky']) && is_array($tour['nhat_ky']) ? $tour['nhat_ky'] : []);
$yeuCauHienThi = !empty($yeuCauList) && is_array($yeuCauList) ? $yeuCauList : (!empty($tour['yeu_cau_dac_biet']) && is_array($tour['yeu_cau_dac_biet']) ? $tour['yeu_cau_dac_biet'] : []);
$giaTourHienThi = (float)($tour['gia_tour'] ?? $tour['gia_co_ban'] ?? 0);
$trangThaiCho = 'Con nhieu cho';
$trangThaiChoClass = 'seat-good';
if ($seatPercentRemain !== null) {
    if ($seatPercentRemain <= 20) {
        $trangThaiCho = 'Sap het cho';
        $trangThaiChoClass = 'seat-low';
    } elseif ($seatPercentRemain <= 45) {
        $trangThaiCho = 'Dat nhanh';
        $trangThaiChoClass = 'seat-mid';
    }
}
$camKetList = [
    ['icon' => 'bi bi-shield-check', 'title' => 'Thong tin ro rang', 'desc' => 'Lich khoi hanh, gia va so cho duoc hien thi minh bach.'],
    ['icon' => 'bi bi-headset', 'title' => 'Ho tro nhanh', 'desc' => 'Co the dat nhanh hoac chuyen sang trang thanh toan ngay.'],
    ['icon' => 'bi bi-map', 'title' => 'Lich trinh cu the', 'desc' => 'Noi dung tour va cac moc hanh trinh duoc trinh bay tach bach.'],
];
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
        .tourlux-back.is-active{
            background:linear-gradient(135deg, var(--lx-gold), #e2bf78);
            border-color:rgba(214,178,109,.72);
            color:#132033;
            box-shadow:0 10px 24px rgba(185,137,61,.22);
        }
        .tourlux-hero-topbar{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            flex-wrap:wrap;
        }
        .tourlux-brand{
            display:inline-flex;
            align-items:center;
            gap:.52rem;
            color:#fff;
            text-decoration:none;
            font-family:"Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
            font-size:1.95rem;
            font-weight:700;
            letter-spacing:.3px;
        }
        .tourlux-brand i{ color: var(--lx-gold); }
        .tourlux-top-actions{
            display:flex;
            align-items:center;
            gap:.55rem;
            flex-wrap:wrap;
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
        .data-grid-table{
            border-radius:16px;
            overflow:hidden;
        }
        .data-grid-table thead th{
            background:linear-gradient(180deg, rgba(11,18,32,.06), rgba(11,18,32,.03));
            border-bottom:1px solid rgba(15,23,42,.14);
            white-space:nowrap;
        }
        .data-grid-table tbody tr:nth-child(even){
            background:rgba(214,178,109,.05);
        }
        .data-grid-table tbody tr:hover{
            background:rgba(214,178,109,.10);
        }

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
        .tour-seat-meter{
            margin-top:.68rem;
            border-top:1px dashed rgba(15,23,42,.14);
            padding-top:.68rem;
        }
        .tour-seat-meter-head{
            display:flex;
            align-items:center;
            justify-content:space-between;
            font-size:.82rem;
            color:#475569;
            font-weight:700;
            margin-bottom:6px;
        }
        .tour-seat-bar{
            height:9px;
            width:100%;
            border-radius:999px;
            background:rgba(15,23,42,.08);
            overflow:hidden;
        }
        .tour-seat-progress{
            height:100%;
            border-radius:999px;
            background:linear-gradient(90deg, #17413b, #2f7d74);
            transition: width .35s ease;
        }

        .tour-copy{
            font-size:1.02rem;
            line-height:1.8;
            color:#334155;
        }
        .tourlux-stats{
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:10px;
            margin-top:14px;
            max-width:760px;
        }
        .tourlux-overview-grid{
            display:grid;
            grid-template-columns:repeat(4, minmax(0, 1fr));
            gap:14px;
            margin-bottom:20px;
        }
        .tourlux-overview-card{
            position:relative;
            overflow:hidden;
            border-radius:22px;
            padding:18px;
            min-height:142px;
            background:
                radial-gradient(220px 120px at 100% 0%, rgba(214,178,109,.18), transparent 60%),
                linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,255,255,.82));
            border:1px solid rgba(255,255,255,.7);
            box-shadow:0 18px 54px rgba(2,6,23,.10);
        }
        .tourlux-overview-card i{
            font-size:1.15rem;
            color:var(--lx-gold2);
        }
        .tourlux-overview-label{
            font-size:.8rem;
            text-transform:uppercase;
            letter-spacing:.08em;
            font-weight:800;
            color:#64748b;
            margin-top:12px;
        }
        .tourlux-overview-value{
            font-size:1.15rem;
            font-weight:900;
            color:#0f172a;
            margin-top:6px;
            line-height:1.25;
        }
        .tourlux-overview-note{
            color:#64748b;
            font-size:.92rem;
            margin-top:6px;
        }
        .seat-pill{
            display:inline-flex;
            align-items:center;
            gap:.4rem;
            border-radius:999px;
            padding:.4rem .8rem;
            font-size:.78rem;
            font-weight:800;
            margin-top:10px;
            border:1px solid transparent;
        }
        .seat-pill.seat-good{
            color:#166534;
            background:rgba(34,197,94,.10);
            border-color:rgba(34,197,94,.18);
        }
        .seat-pill.seat-mid{
            color:#9a6700;
            background:rgba(245,158,11,.12);
            border-color:rgba(245,158,11,.22);
        }
        .seat-pill.seat-low{
            color:#b42318;
            background:rgba(239,68,68,.10);
            border-color:rgba(239,68,68,.18);
        }
        .tourlux-stat{
            border:1px solid rgba(255,255,255,.18);
            background:rgba(255,255,255,.08);
            backdrop-filter: blur(10px);
            border-radius:14px;
            padding:10px 12px;
            display:flex;
            align-items:flex-start;
            gap:8px;
        }
        .tourlux-stat i{
            color:rgba(214,178,109,.95);
            font-size:1.02rem;
            transform:translateY(1px);
        }
        .tourlux-stat-k{
            font-size:.72rem;
            color:rgba(255,255,255,.64);
            text-transform:uppercase;
            letter-spacing:.08em;
            font-weight:800;
        }
        .tourlux-stat-v{
            color:#fff;
            font-size:.98rem;
            font-weight:900;
            line-height:1.2;
            margin-top:2px;
        }

        .timeline-list{
            display:grid;
            gap:12px;
        }
        .timeline-item{
            display:grid;
            grid-template-columns: 76px 1fr;
            gap:12px;
            align-items:start;
            background:rgba(255,255,255,.82);
            border:1px solid rgba(15,23,42,.10);
            border-radius:14px;
            padding:12px;
            box-shadow:0 8px 24px rgba(2,6,23,.06);
        }
        .timeline-day{
            border-radius:12px;
            background:linear-gradient(135deg, #15233b, #20365f);
            color:#f7e5b6;
            text-align:center;
            padding:10px 8px;
            font-weight:900;
            font-size:.86rem;
            letter-spacing:.03em;
        }
        .timeline-place{
            font-weight:800;
            color:#13213a;
            margin-bottom:3px;
        }
        .timeline-activity{
            color:#475569;
            line-height:1.55;
            font-size:.95rem;
        }
        .feature-grid{
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:14px;
        }
        .feature-card{
            border-radius:18px;
            border:1px solid rgba(15,23,42,.10);
            background:linear-gradient(180deg, rgba(255,255,255,.90), rgba(255,255,255,.78));
            padding:16px;
            box-shadow:0 14px 36px rgba(2,6,23,.08);
        }
        .feature-card i{
            font-size:1.1rem;
            color:var(--lx-gold2);
        }
        .feature-title{
            font-weight:900;
            color:#13213a;
            margin:10px 0 6px 0;
        }
        .feature-copy{
            color:#64748b;
            line-height:1.55;
            font-size:.94rem;
            margin:0;
        }
        .schedule-card-list{
            display:grid;
            gap:12px;
        }
        .schedule-card{
            border-radius:18px;
            border:1px solid rgba(15,23,42,.10);
            background:rgba(255,255,255,.88);
            padding:16px;
            box-shadow:0 12px 30px rgba(2,6,23,.06);
        }
        .schedule-card-top{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:12px;
            margin-bottom:10px;
        }
        .schedule-date{
            font-weight:900;
            color:#13213a;
            font-size:1.02rem;
        }
        .schedule-range{
            color:#64748b;
            font-size:.92rem;
            margin-top:2px;
        }
        .status-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius:999px;
            padding:.42rem .8rem;
            font-size:.78rem;
            font-weight:800;
            white-space:nowrap;
        }
        .status-badge.status-open{
            background:rgba(34,197,94,.12);
            color:#166534;
        }
        .status-badge.status-closed{
            background:rgba(148,163,184,.18);
            color:#334155;
        }
        .status-badge.status-busy{
            background:rgba(245,158,11,.16);
            color:#9a6700;
        }
        .schedule-meta{
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:10px;
        }
        .schedule-meta-item{
            border-radius:14px;
            background:rgba(15,23,42,.04);
            padding:10px 12px;
        }
        .schedule-meta-item span{
            display:block;
            color:#64748b;
            font-size:.8rem;
            font-weight:700;
            margin-bottom:4px;
        }
        .schedule-meta-item strong{
            color:#0f172a;
            font-size:.94rem;
        }
        .list-clean{
            list-style:none;
            padding:0;
            margin:0;
            display:grid;
            gap:10px;
        }
        .list-clean li{
            border-radius:16px;
            border:1px solid rgba(15,23,42,.08);
            background:rgba(255,255,255,.78);
            padding:12px 14px;
            color:#334155;
            line-height:1.55;
        }
        .list-clean li strong{
            color:#0f172a;
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
            flex:0 0 296px;
            scroll-snap-align:start;
            border-radius:22px;
            overflow:hidden;
            border:1px solid rgba(15,23,42,.10);
            background:linear-gradient(180deg,#ffffff,#fbfdfa);
            box-shadow:0 18px 54px rgba(2,6,23,.10);
            transition: transform .22s, box-shadow .22s;
            display:flex;
            flex-direction:column;
        }
        .related-card:hover{
            transform: translateY(-5px);
            box-shadow:0 26px 74px rgba(2,6,23,.16);
        }
        .related-card img{
            width:100%;
            height:176px;
            object-fit:cover;
            display:block;
            filter:saturate(1.05) contrast(1.02);
            transition:transform .35s ease;
        }
        .related-card:hover img{
            transform:scale(1.04);
        }
        .related-body{ padding: 14px 14px 16px 14px; display:flex; flex-direction:column; flex:1; }
        .related-name{
            font-weight:900;
            letter-spacing:.1px;
            margin:0 0 6px 0;
            line-height:1.2;
            font-size:1.05rem;
        }
        .related-meta{
            color:var(--lx-muted);
            font-size:.92rem;
            min-height: 42px;
            margin-bottom:12px;
        }
        .related-price{
            font-weight:900;
            color:#b42318;
            font-size:1.05rem;
        }
        .related-actions{
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-top:auto;
            gap:8px;
        }
        .related-badge{
            display:inline-flex;
            align-items:center;
            border-radius:999px;
            background:rgba(214,178,109,.18);
            color:#7a561f;
            border:1px solid rgba(214,178,109,.34);
            padding:4px 10px;
            font-size:.74rem;
            font-weight:800;
        }
        @media (min-width: 992px){
            .related-scroller{ flex-wrap:wrap; overflow:visible; }
            .related-card{ flex:1 1 calc(33.333% - 14px); max-width:calc(33.333% - 14px); }
        }

        .js-reveal{
            opacity:0;
            transform:translateY(20px);
            transition:opacity .55s ease, transform .55s ease;
        }
        .js-reveal.is-visible{
            opacity:1;
            transform:translateY(0);
        }
        @media (prefers-reduced-motion: reduce){
            .js-reveal{ opacity:1; transform:none; transition:none; }
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

        .review-form-shell{
            margin-top:14px;
            border:1px solid rgba(15,23,42,.10);
            background:linear-gradient(180deg, rgba(255,255,255,.86), rgba(255,255,255,.70));
            border-radius:18px;
            padding:16px;
            box-shadow:0 10px 28px rgba(2,6,23,.06);
        }
        .review-form-shell .form-label{
            font-weight:800;
            color:#1e293b;
            margin-bottom:.48rem;
        }
        .review-form-shell .form-select,
        .review-form-shell .form-control{
            border-radius:14px;
            border:1px solid rgba(15,23,42,.16);
            background:rgba(255,255,255,.95);
            min-height:48px;
            padding:10px 12px;
        }
        .review-form-shell .form-control{ min-height:134px; }
        .review-form-shell .form-select:focus,
        .review-form-shell .form-control:focus{
            border-color:rgba(214,178,109,.72);
            box-shadow:0 0 0 .2rem rgba(214,178,109,.18);
        }
        .review-score-hints{
            margin-top:.55rem;
            display:flex;
            flex-wrap:wrap;
            gap:6px;
        }
        .review-score-chip{
            border:1px solid rgba(214,178,109,.34);
            background:rgba(214,178,109,.12);
            color:#7a561f;
            border-radius:999px;
            padding:3px 9px;
            font-size:.74rem;
            font-weight:700;
        }
        .review-form-footnote{
            color:#64748b;
            font-size:.86rem;
            font-weight:600;
        }

        @media (max-width: 991.98px){
            .tourlux-overview-grid,
            .feature-grid{
                grid-template-columns:1fr 1fr;
            }
            .tourlux-stats{ grid-template-columns:1fr; max-width:none; }
            #dat-tour{ margin-top: 2px; }
            .tourlux-sticky{ position:static !important; top:auto !important; }
            .tour-action-stack .btn{ min-height:50px; font-size:.98rem; }
        }
        @media (max-width: 767.98px){
            .tourlux-overview-grid,
            .feature-grid,
            .schedule-meta{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body class="tourlux">
    <header class="tourlux-hero" style="--hero-img: url('<?php echo htmlspecialchars($anhDaiDien); ?>');">
        <div class="container">
            <div class="tourlux-hero-topbar">
                <a class="tourlux-brand" href="index.php?act=khachHang/dashboard" aria-label="Trang chủ DuLichPro">
                    <i class="bi bi-star-fill"></i> DuLichPro
                </a>
                <div class="tourlux-top-actions">
                    <a class="tourlux-back" href="index.php?act=khachHang/dashboard">
                        <i class="bi bi-house-door"></i> Trang chủ
                    </a>
                    <a class="tourlux-back is-active" href="index.php?act=khachHang/danhSachTour">
                        <i class="bi bi-stars"></i> Tour nổi bật
                    </a>
                    <a class="tourlux-back" href="javascript:history.back()">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>

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

                    <div class="tourlux-stats">
                        <div class="tourlux-stat">
                            <i class="bi bi-star-fill"></i>
                            <div>
                                <div class="tourlux-stat-k">Đánh giá trung bình</div>
                                <div class="tourlux-stat-v"><?php echo number_format($danhGiaAvgHero, 1); ?>/5 (<?php echo (int)$danhGiaCountHero; ?>)</div>
                            </div>
                        </div>
                        <div class="tourlux-stat">
                            <i class="bi bi-person-check-fill"></i>
                            <div>
                                <div class="tourlux-stat-k">Khách đã đặt</div>
                                <div class="tourlux-stat-v"><?php echo $soKhachDaDatHienThi !== null ? (int)$soKhachDaDatHienThi . ' khách' : 'Đang cập nhật'; ?></div>
                            </div>
                        </div>
                        <div class="tourlux-stat">
                            <i class="bi bi-bar-chart-fill"></i>
                            <div>
                                <div class="tourlux-stat-k">Tỉ lệ lấp chỗ</div>
                                <div class="tourlux-stat-v"><?php echo $tiLeLapDayHienThi !== null ? (int)$tiLeLapDayHienThi . '%' : 'Đang cập nhật'; ?></div>
                            </div>
                        </div>
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

        <section class="tourlux-overview-grid js-reveal">
            <article class="tourlux-overview-card">
                <i class="bi bi-calendar2-week"></i>
                <div class="tourlux-overview-label">Khoi hanh gan nhat</div>
                <div class="tourlux-overview-value"><?php echo htmlspecialchars($ngayKhoiHanhHienThi !== '' ? $ngayKhoiHanhHienThi : 'Dang cap nhat'); ?></div>
                <div class="tourlux-overview-note"><?php echo htmlspecialchars($khoiHanhHienThi); ?></div>
            </article>
            <article class="tourlux-overview-card">
                <i class="bi bi-hourglass-split"></i>
                <div class="tourlux-overview-label">Thoi luong</div>
                <div class="tourlux-overview-value"><?php echo htmlspecialchars($thoiGianHienThi); ?></div>
                <div class="tourlux-overview-note">Phu hop cho ke hoach di chuyen linh hoat.</div>
            </article>
            <article class="tourlux-overview-card">
                <i class="bi bi-people-fill"></i>
                <div class="tourlux-overview-label">Tinh trang cho</div>
                <div class="tourlux-overview-value"><?php echo htmlspecialchars((string)$soChoHienThi); ?></div>
                <div class="seat-pill <?php echo htmlspecialchars($trangThaiChoClass); ?>">
                    <i class="bi bi-lightning-charge-fill"></i> <?php echo htmlspecialchars($trangThaiCho); ?>
                </div>
            </article>
            <article class="tourlux-overview-card">
                <i class="bi bi-cash-coin"></i>
                <div class="tourlux-overview-label">Gia hien tai</div>
                <div class="tourlux-overview-value"><?php echo number_format($giaTourHienThi); ?>d</div>
                <div class="tourlux-overview-note">Gia tinh tren moi khach cho lich khoi hanh hien tai.</div>
            </article>
        </section>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="panel mb-4 js-reveal">
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

	                <div class="panel mb-4 js-reveal">
	                    <div class="panel-body">
	                        <h3 class="panel-title"><i class="bi bi-gem"></i> Diem nhan hanh trinh</h3>
	                        <div class="feature-grid mt-3">
	                            <?php foreach ($camKetList as $camKet): ?>
	                                <article class="feature-card">
	                                    <i class="<?php echo htmlspecialchars($camKet['icon']); ?>"></i>
	                                    <div class="feature-title"><?php echo htmlspecialchars($camKet['title']); ?></div>
	                                    <p class="feature-copy"><?php echo htmlspecialchars($camKet['desc']); ?></p>
	                                </article>
	                            <?php endforeach; ?>
	                        </div>
	                    </div>
	                </div>

	                <div class="panel mb-4 js-reveal">
	                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-info-circle"></i> Mô tả tour</h3>
                        <div class="mt-3 tour-copy">
                            <?php echo nl2br(htmlspecialchars($tour['mo_ta'] ?? 'Chưa có mô tả.')); ?>
                        </div>
                    </div>
                </div>

                <div class="panel mb-4 js-reveal">
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-calendar-event"></i> Thông tin khởi hành</h3>
                        <div class="mt-3">
                            <?php if (!empty($lichKhoiHanhList)): ?>
                                <div class="schedule-card-list mb-3">
                                    <?php foreach ($lichKhoiHanhList as $lk): ?>
                                        <?php
                                            $statusRaw = trim((string)($lk['trang_thai'] ?? 'Dang mo'));
                                            $statusClass = 'status-open';
                                            if (stripos($statusRaw, 'dong') !== false || stripos($statusRaw, 'huy') !== false) {
                                                $statusClass = 'status-closed';
                                            } elseif ((int)($lk['so_cho_con_lai'] ?? 999) <= 5) {
                                                $statusClass = 'status-busy';
                                            }
                                        ?>
                                        <article class="schedule-card">
                                            <div class="schedule-card-top">
                                                <div>
                                                    <div class="schedule-date"><?php echo date('d/m/Y', strtotime($lk['ngay_khoi_hanh'])); ?></div>
                                                    <div class="schedule-range">Den <?php echo date('d/m/Y', strtotime($lk['ngay_ket_thuc'])); ?></div>
                                                </div>
                                                <span class="status-badge <?php echo htmlspecialchars($statusClass); ?>">
                                                    <?php echo htmlspecialchars($statusRaw); ?>
                                                </span>
                                            </div>
                                            <div class="schedule-meta">
                                                <div class="schedule-meta-item">
                                                    <span>Diem tap trung</span>
                                                    <strong><?php echo htmlspecialchars($lk['diem_tap_trung'] ?? 'Dang cap nhat'); ?></strong>
                                                </div>
                                                <div class="schedule-meta-item">
                                                    <span>So cho con lai</span>
                                                    <strong><?php echo htmlspecialchars(isset($lk['so_cho_con_lai']) ? ((string)$lk['so_cho_con_lai'] . '/' . (string)($lk['so_cho_toi_da'] ?? $lk['so_cho'] ?? '')) : 'Dang cap nhat'); ?></strong>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                                <table class="table table-bordered table-sm mb-0 data-grid-table d-none">
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

                <div class="panel mb-4 js-reveal">
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-person-badge-fill"></i> Hướng dẫn viên</h3>
                        <div class="mt-3">
                            <?php if (!empty($hdvHienThi)): ?>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-muted-2">
                                    <span class="chip" style="border-color: rgba(15,23,42,.12); background: rgba(255,255,255,.6); color: var(--lx-ink);">
                                        <i class="bi bi-person-circle"></i>
                                        <b><?php echo htmlspecialchars($hdvHienThi['ho_ten'] ?? ''); ?></b>
                                    </span>
                                    <span class="chip" style="border-color: rgba(15,23,42,.12); background: rgba(255,255,255,.6); color: var(--lx-ink);">
                                        <i class="bi bi-envelope-at"></i>
                                        <?php echo htmlspecialchars($hdvHienThi['email'] ?? ''); ?>
                                    </span>
                                    <span class="chip" style="border-color: rgba(15,23,42,.12); background: rgba(255,255,255,.6); color: var(--lx-ink);">
                                        <i class="bi bi-telephone"></i>
                                        <?php echo htmlspecialchars($hdvHienThi['so_dien_thoai'] ?? ''); ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="text-muted-2"><i class="bi bi-person-x-fill me-1"></i> Chưa có thông tin hướng dẫn viên.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="panel mb-4 js-reveal">
                    <div class="panel-body">
                        <h3 class="panel-title"><i class="bi bi-list-task"></i> Lịch trình chi tiết</h3>
                        <div class="mt-3">
                            <?php if (!empty($lichTrinhList)): ?>
                                <div class="timeline-list">
                                    <?php foreach ($lichTrinhList as $lt): ?>
                                        <article class="timeline-item">
                                            <div class="timeline-day">Ngày <?php echo (int)($lt['ngay_thu'] ?? 0); ?></div>
                                            <div>
                                                <div class="timeline-place"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($lt['dia_diem'] ?? 'Đang cập nhật'); ?></div>
                                                <div class="timeline-activity"><?php echo htmlspecialchars($lt['hoat_dong'] ?? 'Đang cập nhật hoạt động'); ?></div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-muted-2"><i class="bi bi-x-circle me-1"></i> Chưa cập nhật lịch trình.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($nhatKyHienThi)): ?>
                    <div class="panel mb-4 js-reveal">
                        <div class="panel-body">
                            <h3 class="panel-title"><i class="bi bi-journal-text"></i> Nhật ký tour</h3>
                            <ul class="list-clean mt-3">
                                <?php foreach ($nhatKyHienThi as $nk): ?>
                                    <li><strong><?php echo !empty($nk['ngay_ghi']) ? date('d/m/Y', strtotime($nk['ngay_ghi'])) : 'Nhat ky'; ?></strong><br><?php echo htmlspecialchars($nk['noi_dung'] ?? 'Dang cap nhat.'); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($yeuCauHienThi)): ?>
                    <div class="panel mb-4 js-reveal">
                        <div class="panel-body">
                            <h3 class="panel-title"><i class="bi bi-star-fill"></i> Yêu cầu đặc biệt</h3>
                            <ul class="list-clean mt-3">
                                <?php foreach ($yeuCauHienThi as $yc): ?>
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
                                <?php if ($seatPercentRemain !== null): ?>
                                    <div class="tour-seat-meter">
                                        <div class="tour-seat-meter-head">
                                            <span>Tỉ lệ chỗ còn</span>
                                            <span><?php echo (int)$seatPercentRemain; ?>%</span>
                                        </div>
                                        <div class="tour-seat-bar" aria-hidden="true">
                                            <div class="tour-seat-progress" style="width: <?php echo (int)$seatPercentRemain; ?>%;"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="tour-info-row price"><span class="k">Giá/khách:</span><span class="v"><?php echo number_format($tour['gia_tour'] ?? $tour['gia_co_ban'] ?? 0); ?>đ</span></div>
                            </div>

                            <?php if ($isBookingLockedBy48h): ?>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Tour này chỉ nhận đặt trước ít nhất 48 giờ so với ngày khởi hành.
                                </div>
                            <?php endif; ?>

                            <div class="promo mb-3">
                                <i class="bi bi-shield-check"></i>
                                <div class="text-muted-2">
                                    Đặt ngay để nhận ưu đãi giờ chót, tiết kiệm thêm <b>1,000K</b>.
                                </div>
                            </div>

                            <div class="d-grid gap-2 tour-action-stack">
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
            <div class="panel mb-4 js-reveal">
                <div class="panel-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h3 class="panel-title"><i class="bi bi-chat-quote"></i> Phản hồi khách hàng</h3>
                        <div class="rating-badge">
                            <span><?php echo number_format((float)($danhGiaTourAvg ?? 0), 1); ?>/5</span>
                            <span class="text-muted-2" style="font-weight:800;">(<?php echo (int)($danhGiaTourCount ?? 0); ?> đánh giá)</span>
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

        <div class="panel mb-4 js-reveal">
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
                    <form class="review-form-shell" method="post" action="index.php?act=khachHang/guiDanhGia">
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
                                <div class="review-score-hints">
                                    <span class="review-score-chip">5: Xuất sắc</span>
                                    <span class="review-score-chip">4: Tốt</span>
                                    <span class="review-score-chip">3: Ổn</span>
                                    <span class="review-score-chip">2-1: Cần cải thiện</span>
                                </div>
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
                            <span class="review-form-footnote align-self-center">Chỉ hiển thị khi bạn đã đặt và hoàn thành tour.</span>
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
            <div class="panel mb-4 js-reveal">
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
                                            <span class="related-badge">
                                                <?php echo htmlspecialchars($t['loai_tour']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="related-meta"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($moTa); ?></div>
                                    <div class="related-actions">
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
            var revealEls = document.querySelectorAll('.js-reveal');
            if (!revealEls || revealEls.length === 0) return;

            var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reduced || !('IntersectionObserver' in window)) {
                revealEls.forEach(function (el) { el.classList.add('is-visible'); });
                return;
            }

            var io = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

            revealEls.forEach(function (el) { io.observe(el); });
        })();

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
