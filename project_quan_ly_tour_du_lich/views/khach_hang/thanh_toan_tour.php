<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --lx-bg:#0b1220;
            --lx-ink:#0f172a;
            --lx-muted:#64748b;
            --lx-gold:#d6b26d;
            --lx-gold2:#b9893d;
            --lx-card: rgba(255,255,255,.84);
            --pay-bg-img: url('https://images.unsplash.com/photo-1496307653780-42ee777d4833?auto=format&fit=crop&w=2400&q=60');
        }
        body.paylux{
            font-family:"Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            color:var(--lx-ink);
            min-height:100vh;
            background:#f5f6f8;
            position:relative;
        }
        body.paylux::before{
            content:"";
            position:fixed;
            inset:-60px;
            z-index:-2;
            background-image:var(--pay-bg-img);
            background-size:cover;
            background-position:center;
            filter: blur(20px) saturate(1.05);
            transform: scale(1.06);
            opacity:.22;
        }
        body.paylux::after{
            content:"";
            position:fixed;
            inset:0;
            z-index:-1;
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(214,178,109,.14), transparent 60%),
                radial-gradient(900px 520px at 85% 0%, rgba(11,18,32,.08), transparent 55%),
                rgba(245,246,248,.92);
        }
        .pay-wrap{
            padding: 34px 12px 54px 12px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .pay-card{
            width: min(980px, 100%);
            background:var(--lx-card);
            border:1px solid rgba(255,255,255,.58);
            border-radius:24px;
            box-shadow:0 26px 80px rgba(2,6,23,.12);
            backdrop-filter: blur(14px);
            overflow:hidden;
        }
        .pay-head{
            padding: 22px 22px;
            background: linear-gradient(135deg, rgba(11,18,32,.95), rgba(20,38,68,.92));
            color:#fff;
        }
        .pay-back{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            color:rgba(255,255,255,.86);
            text-decoration:none;
            padding:.45rem .7rem;
            border-radius:999px;
            border:1px solid rgba(255,255,255,.22);
            background:rgba(255,255,255,.06);
            backdrop-filter: blur(10px);
            transition:.18s;
        }
        .pay-back:hover{
            color:#fff;
            background:rgba(214,178,109,.14);
            border-color:rgba(214,178,109,.32);
        }
        .pay-title{
            font-family:"Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
            margin:0;
            letter-spacing:.2px;
        }
        .pay-sub{
            margin:.35rem 0 0 0;
            color:rgba(255,255,255,.78);
        }
        .pay-body{ padding: 18px 18px; }
        @media (min-width: 992px){
            .pay-head{ padding: 26px 28px; }
            .pay-body{ padding: 22px 22px; }
        }
        .panel{
            background: rgba(255,255,255,.86);
            border:1px solid rgba(15,23,42,.10);
            border-radius:18px;
            padding: 16px 16px;
        }
        .panel h5{
            font-weight:900;
            margin:0 0 .65rem 0;
            display:flex;
            align-items:center;
            gap:.5rem;
        }
        .total-box{
            background: linear-gradient(135deg, rgba(11,18,32,.92), rgba(20,38,68,.92));
            color:#fff;
            border-radius:16px;
            padding: 14px 14px;
            border:1px solid rgba(214,178,109,.20);
        }
        .total-box .label{ color: rgba(255,255,255,.70); font-weight:700; }
        .total-box .value{ font-weight:900; font-size:1.25rem; letter-spacing:.2px; }
        .qr-img{
            max-width: 260px;
            width:100%;
            border-radius:16px;
            border:1px solid rgba(15,23,42,.10);
            box-shadow:0 18px 60px rgba(2,6,23,.12);
        }
        .btn-pay{
            border-radius:14px;
            font-weight:900;
            padding: 12px 18px;
            font-size: 1.05rem;
        }
        .btn-pay-primary{
            background: linear-gradient(135deg, var(--lx-gold), var(--lx-gold2));
            border:none;
            color:#132033;
            box-shadow:0 16px 46px rgba(185,137,61,.24);
        }
        .btn-pay-primary:hover{ filter:brightness(1.03); color:#132033; }
        .btn-pay-outline{
            border:1px solid rgba(214,178,109,.55);
            background: rgba(255,255,255,.65);
            color: var(--lx-gold2);
        }
        .btn-pay-outline:hover{ background: rgba(214,178,109,.12); color:#7a561f; }
        .form-control, .form-select{
            border-radius: 14px;
            padding: 10px 12px;
        }
        .flow-step{
            display:flex;
            align-items:center;
            gap:.55rem;
            padding:.55rem .7rem;
            border-radius:999px;
            background:rgba(15,23,42,.06);
            border:1px solid rgba(15,23,42,.10);
            font-size:.86rem;
            font-weight:700;
        }
        .flow-step i{ color:#1d4ed8; }
        .mode-badge{
            border-radius:999px;
            font-size:.8rem;
            font-weight:800;
            padding:.35rem .65rem;
            border:1px solid rgba(15,23,42,.10);
            background:rgba(255,255,255,.76);
        }
        .mode-badge.mock{ color:#b45309; border-color:rgba(180,83,9,.25); background:rgba(251,191,36,.15); }
        .mode-badge.vnpay{ color:#0f766e; border-color:rgba(15,118,110,.25); background:rgba(20,184,166,.14); }

        .complaint-box{
            margin-top: 10px;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(220, 53, 69, .28);
            background: rgba(220, 53, 69, .06);
        }
        .complaint-box .form-control,
        .complaint-box .form-label{
            font-size: .9rem;
        }

        .complaint-box.is-hidden {
            display: none;
        }
    </style>
</head>
<body class="paylux">
    <?php
    $giaTour = (float)($tour['gia_tour'] ?? $tour['gia_co_ban'] ?? 0);

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
        $tourIdHienThi = (int)($tour['tour_id'] ?? $tour['id'] ?? 0);
        $maTourHienThi = $tourIdHienThi > 0 ? 'TOUR-' . str_pad((string)$tourIdHienThi, 4, '0', STR_PAD_LEFT) : 'N/A';
    }

    $khoiHanhHienThi = trim((string)($tour['noi_khoi_hanh'] ?? ''));
    if ($khoiHanhHienThi === '' && !empty($lichKhoiHanhHienThi['diem_tap_trung'])) {
        $khoiHanhHienThi = $lichKhoiHanhHienThi['diem_tap_trung'];
    }
    if ($khoiHanhHienThi === '') {
        $khoiHanhHienThi = 'Chưa cập nhật';
    }

    $ngayKhoiHanhHienThi = 'Chưa cập nhật';
    if (!empty($lichKhoiHanhHienThi['ngay_khoi_hanh'])) {
        $ngayKhoiHanhHienThi = date('d-m-Y', strtotime($lichKhoiHanhHienThi['ngay_khoi_hanh']));
    }

    $khoiHanhSauHienThi = isset($khoiHanhSauHienThi) ? (string)$khoiHanhSauHienThi : 'Chưa cập nhật';
    $isBookingLockedBy48h = !empty($isBookingLockedBy48h);
    $bookingLockMessage = trim((string)($bookingLockMessage ?? ''));

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

    $soChoHienThi = $tour['so_cho'] ?? ($lichKhoiHanhHienThi['so_cho'] ?? null);
    if ($soChoHienThi === null || $soChoHienThi === '') {
        $soChoHienThi = 'Chưa cập nhật';
    }

    $accountNumberRaw = trim((string)QR_PAYMENT_ACCOUNT_NUMBER);
    $accountNumberDisplay = $accountNumberRaw;
    if ($accountNumberRaw !== '') {
        $compact = preg_replace('/\s+/', '', $accountNumberRaw);
        $len = strlen($compact);
        if ($len > 4) {
            $accountNumberDisplay = str_repeat('*', $len - 4) . substr($compact, -4);
        }
    }

    $qrImageEnv = trim((string)QR_PAYMENT_IMAGE_URL);
    $qrImageCandidates = [];
    if ($qrImageEnv !== '') {
        $qrImageCandidates[] = $qrImageEnv;
    }
    $qrImageCandidates[] = '/uploads/qr/image.png';
    $qrImageCandidates[] = '/public/uploads/qr/image.png';

    $resolvedQrImageUrl = '';
    foreach ($qrImageCandidates as $candidateUrl) {
        $candidateUrl = trim((string)$candidateUrl);
        if ($candidateUrl === '') {
            continue;
        }

        if (preg_match('/^https?:\/\//i', $candidateUrl) === 1) {
            $resolvedQrImageUrl = $candidateUrl;
            break;
        }

        $relativePath = ltrim($candidateUrl, '/');
        if (is_file(PATH_ROOT . $relativePath)) {
            $resolvedQrImageUrl = rtrim(BASE_URL, '/') . '/' . $relativePath;
            break;
        }

        if (strpos($relativePath, 'public/') === 0) {
            $withoutPublic = substr($relativePath, 7);
            if ($withoutPublic !== false && is_file(PATH_ROOT . $withoutPublic)) {
                $resolvedQrImageUrl = rtrim(BASE_URL, '/') . '/' . ltrim($withoutPublic, '/');
                break;
            }
        }
    }

    $defaultQrImageUrl = rtrim(BASE_URL, '/') . '/uploads/qr/image.png';
    $qrImageUrl = $resolvedQrImageUrl !== '' ? $resolvedQrImageUrl : $defaultQrImageUrl;

    $anhTourHienThi = $tour['hinh_anh'] ?? '';
    if (empty($anhTourHienThi) && !empty($hinhAnhList) && !empty($hinhAnhList[0]['url_anh'])) {
        $anhTourHienThi = $hinhAnhList[0]['url_anh'];
    }
    if (empty($anhTourHienThi)) {
        $anhTourHienThi = 'https://images.unsplash.com/photo-1465156799763-2c087c332922?auto=format&fit=crop&w=900&q=80';
    }

    $lichTrinhHienThi = [];
    if (!empty($lichTrinhList) && is_array($lichTrinhList)) {
        $lichTrinhHienThi = array_slice($lichTrinhList, 0, 4);
    }

    $isManualQrMode = (PAYMENT_MODE === 'manual_qr');
    $isVnpayMode = (PAYMENT_MODE === 'vnpay');
    $isVnpayConfigured = (trim((string)VNPAY_TMN_CODE) !== '' && trim((string)VNPAY_HASH_SECRET) !== '');

    $activePaymentStatus = strtoupper(trim((string)($activePayment['status'] ?? '')));
    $activeBookingStatus = strtoupper(trim((string)($activeBooking['trang_thai'] ?? '')));

    $activeTransferNote = '';
    if (!empty($activePayment['transfer_note'])) {
        $activeTransferNote = trim((string)$activePayment['transfer_note']);
    }

    $hasActivePending = !empty($activeBooking) && !empty($activePayment) && ($activePaymentStatus === 'DANGXULY');
    $hasPaymentSuccess = !empty($activeBooking) && (
        $activePaymentStatus === 'THANHCONG'
        || in_array($activeBookingStatus, ['DACOC', 'HOANTAT', 'DATHANHTOAN'], true)
    );
    $isPaymentStage = $hasActivePending || $hasPaymentSuccess;
    ?>
    <main class="pay-wrap">
        <div class="pay-card">
            <div class="pay-head">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h2 class="pay-title"><?php echo $isPaymentStage ? 'Trang thanh toán' : 'Xác nhận thông tin tour'; ?></h2>
                        <p class="pay-sub mb-0">
                            <?php echo $isPaymentStage
                                ? 'Vui lòng quét QR và chuyển khoản đúng nội dung để hệ thống tự động đối soát.'
                                : 'Xác nhận thông tin và xem nhanh hình ảnh, lịch trình tour mà bạn sắp tham gia.'; ?>
                        </p>
                    </div>
                    <span class="mode-badge <?php echo $isVnpayMode ? 'vnpay' : 'mock'; ?>">
                        Mode: <?php echo $isVnpayMode ? 'VNPay' : ($isManualQrMode ? 'Manual QR' : 'Mock Local'); ?>
                    </span>
                    <a href="index.php?act=khachHang/dashboard" class="pay-back">
                        <i class="bi bi-arrow-left"></i> Trang chủ
                    </a>
                </div>
            </div>

            <div class="pay-body">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <div class="flow-step"><i class="bi bi-1-circle-fill"></i> Xác nhận thông tin</div>
                    <div class="flow-step"><i class="bi bi-2-circle-fill"></i> Chuyển sang cổng thanh toán</div>
                    <div class="flow-step"><i class="bi bi-3-circle-fill"></i> <?php echo $isPaymentStage ? 'Trang thanh toán' : ('Nhận kết quả ' . ($isManualQrMode ? 'sau khi admin xac nhan' : 'tu dong')); ?></div>
                </div>
                <?php if ($isVnpayMode && !$isVnpayConfigured): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Che do VNPay dang bat nhung thieu cau hinh merchant. Vui long cap nhat `VNPAY_TMN_CODE` va `VNPAY_HASH_SECRET`.
                    </div>
                <?php endif; ?>
                <?php if ($hasPaymentSuccess): ?>
                    <div class="alert alert-success border mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <strong><i class="bi bi-check-circle-fill me-1"></i>Thanh toan thanh cong</strong>
                                <div class="small text-muted">Booking #<?php echo (int)($activeBooking['booking_id'] ?? 0); ?> da duoc he thong xac nhan giao dich.</div>
                            </div>
                            <a href="index.php?act=khachHang/nhapThongTinThamGia&booking_id=<?php echo (int)($activeBooking['booking_id'] ?? 0); ?>" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-person-vcard me-1"></i>Nhap thong tin nguoi tham gia
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($hasActivePending): ?>
                    <div id="pendingPaymentAlert" class="alert alert-warning border mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <strong id="pendingPaymentTitle"><i class="bi bi-hourglass-split me-1"></i>Booking #<?php echo (int)($activeBooking['booking_id'] ?? 0); ?> dang cho doi soat</strong>
                                <div id="pendingPaymentDesc" class="small text-muted">He thong da tao giao dich, ban chi can chuyen khoan dung noi dung ben duoi va cho webhook cap nhat.</div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="index.php?act=khachHang/hoaDon&booking_id=<?php echo (int)($activeBooking['booking_id'] ?? 0); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-receipt me-1"></i>Xem hoa don
                                </a>
                                <button type="button" id="complaintToggleBtn" class="btn btn-sm btn-outline-danger" onclick="toggleComplaintForm()">
                                    <i class="bi bi-exclamation-diamond me-1"></i>Khieu nai
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 p-2 rounded" style="background:#fff;border:1px dashed #f0ad4e;">
                            <div class="small fw-semibold mb-1">Noi dung chuyen khoan chinh xac:</div>
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span id="activeTransferNote" class="px-2 py-1 rounded" style="background:#fff;border:1px solid #e6e6e6;font-family:monospace;"><?php echo htmlspecialchars($activeTransferNote ?? ''); ?></span>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyActiveTransferNote()">
                                    <i class="bi bi-clipboard me-1"></i>Sao chep
                                </button>
                            </div>
                        </div>

                        <div id="complaintBox" class="complaint-box is-hidden">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <div class="fw-semibold text-danger"><i class="bi bi-exclamation-diamond me-1"></i>Da chuyen khoan sai/thiếu noi dung?</div>
                                    <div class="small text-muted">Gui khiu nai de admin doi soat thu cong. Ban nen dien ma giao dich va thoi gian chuyen khoan neu co.</div>
                                </div>
                            </div>

                            <form method="post" action="index.php?act=khachHang/thanhToanTour&id=<?php echo (int)($tour['tour_id'] ?? $tour['id'] ?? 0); ?>&booking_id=<?php echo (int)($activeBooking['booking_id'] ?? 0); ?>" class="mt-2">
                                <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="submit_transfer_complaint">
                                <input type="hidden" name="booking_id" value="<?php echo (int)($activeBooking['booking_id'] ?? 0); ?>">
                                <input type="hidden" name="payment_id" value="<?php echo (int)($activePayment['payment_id'] ?? 0); ?>">

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">So tien da chuyen (neu co)</label>
                                        <input type="text" name="transfer_amount" maxlength="40" class="form-control" placeholder="Vi du: 5,000">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Thoi gian chuyen khoan (neu co)</label>
                                        <input type="datetime-local" name="transfer_time" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Ma giao dich/tham chieu</label>
                                        <input type="text" name="transfer_ref" maxlength="120" class="form-control" placeholder="Ref tu app/ngan hang">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label mb-1">Mo ta van de</label>
                                        <textarea name="complaint_note" class="form-control" rows="3" minlength="10" maxlength="1000" required placeholder="Vi du: em da chuyen luc 10:42, nguoi nhan MB Bank, nhung viet sai noi dung..." ></textarea>
                                    </div>
                                    <div class="col-12 d-grid d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-send me-1"></i>Gui khieu nai
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="panel mb-3">
                            <h5><i class="bi bi-info-circle text-primary"></i> Thông tin tour</h5>
                            <div class="text-muted">
                                <div><b>Tên tour:</b> <?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?></div>
                                <div><b>Mã tour:</b> <?php echo htmlspecialchars($maTourHienThi); ?></div>
                                <div><b>Loại tour:</b> <?php echo htmlspecialchars($tour['loai_tour'] ?? ''); ?></div>
                                <div><b>Khởi hành:</b> <?php echo htmlspecialchars($khoiHanhHienThi); ?></div>
                                <div><b>Ngày khởi hành:</b> <?php echo htmlspecialchars($ngayKhoiHanhHienThi); ?></div>
                                <div><b>Khởi hành sau:</b> <?php echo htmlspecialchars($khoiHanhSauHienThi); ?></div>
                                <div><b>Thời gian:</b> <?php echo htmlspecialchars($thoiGianHienThi); ?></div>
                                <div><b>Số chỗ còn:</b> <?php echo htmlspecialchars((string)$soChoHienThi); ?></div>
                                <div><b>Giá/khách:</b> <span class="fw-bold" style="color:#b42318;"><?php echo number_format($giaTour); ?>đ</span></div>
                            </div>
                        </div>

                        <?php if ($hasActivePending): ?>
                            <div class="panel mb-3">
                                <h5><i class="bi bi-info-circle text-primary"></i> Trang thai giao dich</h5>
                                <div class="text-muted small">
                                    Da tao giao dich #<?php echo (int)($activePayment['payment_id'] ?? 0); ?> - <?php echo number_format((float)($activePayment['amount'] ?? 0)); ?> VND.
                                    Vui long khong tao them yeu cau moi de tranh trung lap.
                                </div>
                            </div>
                        <?php elseif ($isBookingLockedBy48h): ?>
                            <div class="panel mb-3">
                                <h5><i class="bi bi-lock-fill text-danger"></i> Tam khoa dat tour</h5>
                                <div class="text-muted small">
                                    <?php echo htmlspecialchars($bookingLockMessage !== '' ? $bookingLockMessage : 'Tour này chỉ cho phép đặt trước ít nhất 48 giờ so với ngày khởi hành.'); ?>
                                </div>
                                <div class="mt-3 d-grid">
                                    <a href="index.php?act=khachHang/chiTietTour&id=<?php echo (int)($tour['tour_id'] ?? $tour['id'] ?? 0); ?>" class="btn btn-pay btn-pay-outline w-100">
                                        <i class="bi bi-arrow-left-circle me-1"></i> Quay lại chi tiết tour
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                        <form method="post" action="index.php?act=khachHang/thanhToanTour&id=<?php echo $tour['tour_id'] ?? $tour['id']; ?>">
                            <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="panel mb-3">
                                <h5><i class="bi bi-person-lines-fill text-primary"></i> Thông tin khách</h5>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="so_luong" class="form-label fw-semibold">Số lượng người</label>
                                        <input type="number" class="form-control" id="so_luong" name="so_luong" min="1" value="1" required data-unit-price="<?php echo (int)$giaTour; ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="payment_method" class="form-label fw-semibold">Phương thức thanh toán online</label>
                                        <select id="payment_method" name="payment_method" class="form-select" required>
                                            <option value="VNPay">VNPay</option>
                                            <?php if (!$isVnpayMode && !$isManualQrMode): ?>
                                                <option value="Momo">MoMo</option>
                                                <option value="Paypal">PayPal</option>
                                            <?php endif; ?>
                                        </select>
                                        <div class="small text-muted mt-1">
                                            <?php if ($isManualQrMode): ?>
                                                Sau khi bấm xác nhận, hệ thống sẽ tạo mã thanh toán và hiển thị QR để bạn chuyển khoản.
                                            <?php elseif ($isVnpayMode): ?>
                                                Sau khi bấm xác nhận, hệ thống sẽ chuyển bạn sang cổng VNPay để thanh toán tự động.
                                            <?php else: ?>
                                                Hệ thống sẽ chuyển sang cổng thanh toán sau khi bạn bấm xác nhận.
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tên khách hàng</label>
                                        <input type="text" class="form-control" name="ten_khach_hang" value="<?php echo htmlspecialchars($nguoiDung['ho_ten'] ?? ''); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Số điện thoại</label>
                                        <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($nguoiDung['so_dien_thoai'] ?? ''); ?>" readonly>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($nguoiDung['email'] ?? ''); ?>" readonly>
                                    </div>
                                </div>

                                <div class="total-box mt-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="label">Tổng thanh toán</div>
                                        <div class="value" id="totalPrice"><?php echo number_format($giaTour); ?>đ</div>
                                    </div>
                                    <div class="small mt-1" style="color: rgba(255,255,255,.70);">
                                        (Tự động tính theo số lượng người)
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" id="createPaymentBtn" class="btn btn-pay btn-pay-primary w-100">
                                    <i class="bi bi-credit-card-2-front me-1"></i>
                                    <?php echo $isManualQrMode ?  'Chuyển sang cổng thanh toán' : 'Tạo mã thanh toán'; ?>
                                </button>
                                <a href="index.php?act=khachHang/dashboard" class="btn btn-pay btn-pay-outline w-100">
                                    <i class="bi bi-arrow-left-circle me-1"></i> Quay lại trang chủ
                                </a>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-5">
                        <div class="panel h-100">
                            <?php if ($isPaymentStage): ?>
                                <h5><i class="bi bi-qr-code-scan text-primary"></i> Thanh toan QR</h5>
                                <div class="text-muted mb-2">Vui long quet QR ben duoi va chuyen khoan dung noi dung de he thong doi soat tu dong.</div>
                                <div class="text-center mt-3">
                                    <img class="qr-img" src="<?php echo htmlspecialchars($qrImageUrl); ?>" alt="QR Thanh toan" loading="lazy" onerror="if(!this.dataset.fallback){this.dataset.fallback='1';this.src='<?php echo htmlspecialchars($defaultQrImageUrl); ?>';return;}this.style.display='none';this.nextElementSibling.style.display='block';">
                                    <div class="small text-danger" style="display:none;">Khong tai duoc anh QR. Vui long kiem tra cau hinh QR_PAYMENT_IMAGE_URL.</div>
                                    <div class="mt-3 fw-bold">
                                        <i class="bi bi-person-badge-fill text-success me-1"></i> <?php echo htmlspecialchars(QR_PAYMENT_ACCOUNT_NAME); ?> - <?php echo htmlspecialchars($accountNumberDisplay); ?>
                                    </div>
                                    <?php if (QR_PAYMENT_BANK_NAME !== ''): ?>
                                        <div class="small text-muted mt-1">Ngan hang: <?php echo htmlspecialchars(QR_PAYMENT_BANK_NAME); ?></div>
                                    <?php endif; ?>
                                    <div class="small text-muted mt-1">Noi dung chuyen khoan: <?php echo htmlspecialchars(($activeTransferNote ?? '') ?: QR_PAYMENT_TRANSFER_NOTE_HINT); ?></div>
                                </div>
                            <?php else: ?>
                                <h5><i class="bi bi-image text-primary"></i> Anh tour & lich trinh</h5>
                                <div class="text-muted mb-2">Thong tin tham khao nhanh de ban doi chieu truoc khi xac nhan dat tour.</div>
                                <div class="text-center mt-3">
                                    <img class="qr-img" src="<?php echo htmlspecialchars($anhTourHienThi); ?>" alt="Anh tour" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1465156799763-2c087c332922?auto=format&fit=crop&w=900&q=80';">
                                </div>
                                <div class="mt-3">
                                    <div class="fw-semibold mb-2"><i class="bi bi-map me-1"></i>Lich trinh noi bat</div>
                                    <?php if (!empty($lichTrinhHienThi)): ?>
                                        <ul class="list-group list-group-flush rounded" style="border:1px solid rgba(15,23,42,.10); overflow:hidden;">
                                            <?php foreach ($lichTrinhHienThi as $lt): ?>
                                                <li class="list-group-item px-3 py-2" style="background:rgba(255,255,255,.75);">
                                                    <div class="fw-semibold">Ngay <?php echo (int)($lt['ngay_thu'] ?? 0); ?> - <?php echo htmlspecialchars((string)($lt['dia_diem'] ?? 'Chua cap nhat dia diem')); ?></div>
                                                    <div class="small text-muted"><?php echo htmlspecialchars((string)($lt['hoat_dong'] ?? 'Dang cap nhat hoat dong')); ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php if (!empty($lichTrinhList) && count($lichTrinhList) > count($lichTrinhHienThi)): ?>
                                            <div class="small text-muted mt-2">Dang hien thi <?php echo count($lichTrinhHienThi); ?> / <?php echo count($lichTrinhList); ?> muc.</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="small text-muted">Tour nay chua co du lieu lich trinh chi tiet.</div>
                                    <?php endif; ?>
                                </div>
                                <a href="index.php?act=khachHang/chiTietTour&id=<?php echo (int)($tour['tour_id'] ?? $tour['id'] ?? 0); ?>" class="btn btn-pay btn-pay-outline w-100 mt-3">
                                    <i class="bi bi-info-circle me-1"></i>Xem chi tiet tour
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php if ($hasPaymentSuccess): ?>
        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1085;">
            <div id="paymentSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4500">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i> Da thanh toan thanh cong
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyActiveTransferNote() {
            var el = document.getElementById('activeTransferNote');
            if (!el) return;
            var text = (el.textContent || '').trim();
            if (!text) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                var ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
        }

        function toggleComplaintForm() {
            var box = document.getElementById('complaintBox');
            var btn = document.getElementById('complaintToggleBtn');
            if (!box) return;

            var isHidden = box.classList.contains('is-hidden');
            if (isHidden) {
                box.classList.remove('is-hidden');
                if (btn) {
                    btn.innerHTML = '<i class="bi bi-chevron-up me-1"></i>An form khieu nai';
                }
                var textarea = box.querySelector('textarea[name="complaint_note"]');
                if (textarea) {
                    textarea.focus();
                }
            } else {
                box.classList.add('is-hidden');
                if (btn) {
                    btn.innerHTML = '<i class="bi bi-exclamation-diamond me-1"></i>Khieu nai';
                }
            }
        }

        function showSuccessToast(message) {
            if (!window.bootstrap || !bootstrap.Toast) return;

            var existing = document.getElementById('runtimeSuccessToast');
            if (existing) {
                existing.remove();
            }

            var container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 start-50 translate-middle-x p-3';
            container.style.zIndex = '1085';

            container.innerHTML = ''
                + '<div id="runtimeSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">'
                + '  <div class="d-flex">'
                + '    <div class="toast-body fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>' + message + '</div>'
                + '    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>'
                + '  </div>'
                + '</div>';

            document.body.appendChild(container);
            var toastEl = container.querySelector('#runtimeSuccessToast');
            var toast = new bootstrap.Toast(toastEl);
            toast.show();

            toastEl.addEventListener('hidden.bs.toast', function () {
                if (container && container.parentNode) {
                    container.parentNode.removeChild(container);
                }
            });
        }

        (function () {
            var qty = document.getElementById('so_luong');
            var totalEl = document.getElementById('totalPrice');
            if (!qty || !totalEl) return;

            var unit = parseFloat(qty.getAttribute('data-unit-price') || '0') || 0;
            var formatVND = function (n) { return Math.round(n).toLocaleString('vi-VN') + 'đ'; };

            var update = function () {
                var count = parseInt(qty.value || '1', 10);
                if (!Number.isFinite(count) || count < 1) count = 1;
                totalEl.textContent = formatVND(unit * count);
            };

            qty.addEventListener('input', update);
            update();
        })();

        (function () {
            var toastEl = document.getElementById('paymentSuccessToast');
            if (!toastEl || !window.bootstrap || !bootstrap.Toast) return;
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        })();

        (function () {
            var submitBtn = document.getElementById('createPaymentBtn');
            if (!submitBtn) return;

            var form = submitBtn.closest('form');
            if (!form) return;

            form.addEventListener('submit', function () {
                if (submitBtn.disabled) return;
                submitBtn.disabled = true;
                submitBtn.classList.add('disabled');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Dang chuyen sang cong thanh toan...';
            });
        })();

        (function () {
            var bookingId = <?php echo (int)($activeBooking['booking_id'] ?? 0); ?>;
            var shouldPoll = <?php echo $hasActivePending ? 'true' : 'false'; ?>;
            if (!shouldPoll || bookingId <= 0) return;

            var firedSuccessToast = false;
            var pendingAlert = document.getElementById('pendingPaymentAlert');
            var pendingTitle = document.getElementById('pendingPaymentTitle');
            var pendingDesc = document.getElementById('pendingPaymentDesc');

            var poll = function () {
                fetch('index.php?act=khachHang/paymentStatus&booking_id=' + encodeURIComponent(String(bookingId)) + '&_t=' + Date.now(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data || !data.ok) return;

                    if (data.is_success) {
                        if (!firedSuccessToast) {
                            firedSuccessToast = true;
                            showSuccessToast('Da thanh toan thanh cong');
                        }

                        if (pendingAlert) {
                            pendingAlert.classList.remove('alert-warning');
                            pendingAlert.classList.add('alert-success');
                        }
                        if (pendingTitle) {
                            pendingTitle.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>Booking #' + bookingId + ' da thanh toan thanh cong';
                        }
                        if (pendingDesc) {
                            pendingDesc.textContent = 'He thong da xac nhan giao dich. Dang chuyen den buoc nhap thong tin nguoi tham gia...';
                        }

                        setTimeout(function () {
                            var nextUrl = (typeof data.next_action_url === 'string' && data.next_action_url) ? data.next_action_url : ('index.php?act=khachHang/nhapThongTinThamGia&booking_id=' + bookingId);
                            window.location.href = nextUrl;
                        }, 1200);
                    }
                })
                .catch(function () {
                    // Khong lam gi; se thu lai o chu ky sau.
                });
            };

            poll();
            setInterval(poll, 6000);
        })();
    </script>
</body>
</html>
