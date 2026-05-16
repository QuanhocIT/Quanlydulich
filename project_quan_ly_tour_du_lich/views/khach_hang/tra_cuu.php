<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu booking</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(1200px 640px at -10% -10%, rgba(14,165,233,.22), transparent 60%),
                radial-gradient(1000px 520px at 110% 0%, rgba(59,130,246,.14), transparent 55%),
                linear-gradient(180deg, #f5f9ff 0%, #edf3fb 100%);
            color: #0f172a;
        }

        .lookup-wrap {
            max-width: 960px;
            margin: 28px auto;
            padding: 0 14px;
        }

        .lookup-card {
            background: rgba(255,255,255,.9);
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 20px;
            box-shadow: 0 24px 64px rgba(2,6,23,.1);
            backdrop-filter: blur(8px);
        }

        .lookup-head {
            border-bottom: 1px solid rgba(15,23,42,.08);
            padding: 22px;
        }

        .lookup-body {
            padding: 22px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .info-box {
            border: 1px solid rgba(15,23,42,.1);
            border-radius: 14px;
            padding: 12px;
            background: rgba(255,255,255,.78);
        }

        .info-label {
            font-size: .82rem;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
            letter-spacing: .04em;
        }

        .info-value {
            font-weight: 700;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <main class="lookup-wrap">
        <div class="lookup-card">
            <div class="lookup-head">
                <h1 class="h4 mb-1">Tra cứu booking</h1>
                <p class="text-secondary mb-0">Nhập mã booking và email hoặc số điện thoại đã dùng khi đặt tour.</p>
            </div>
            <div class="lookup-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" action="<?php echo BASE_URL; ?>index.php?act=khachHang/traCuu" class="row g-3">
                    <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mã booking</label>
                        <input type="text" name="booking_ref" class="form-control" placeholder="VD: BK-000231 hoặc 231" value="<?php echo htmlspecialchars((string)($bookingRef ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email hoặc số điện thoại</label>
                        <input type="text" name="verifier" class="form-control" placeholder="VD: ten@email.com hoặc 09xxxxxxxx" value="<?php echo htmlspecialchars((string)($verifier ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Tra cứu
                        </button>
                        <a href="index.php?act=auth/login" class="btn btn-outline-secondary">Đăng nhập</a>
                    </div>
                </form>

                <?php if (!empty($lookupData) && is_array($lookupData)): ?>
                    <?php $booking = (array)($lookupData['booking'] ?? []); ?>
                    <?php $payment = (array)($lookupData['latest_payment'] ?? []); ?>

                    <hr class="my-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h5 mb-0">Kết quả tra cứu</h2>
                        <?php if (!empty($lookupData['export_token'])): ?>
                            <a class="btn btn-sm btn-outline-primary" href="index.php?act=khachHang/traCuuPdf&token=<?php echo urlencode((string)$lookupData['export_token']); ?>">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Xuất PDF xác nhận
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="info-grid">
                        <article class="info-box">
                            <div class="info-label">Mã tra cứu</div>
                            <div class="info-value"><?php echo htmlspecialchars((string)($lookupData['booking_code'] ?? 'N/A')); ?></div>
                        </article>
                        <article class="info-box">
                            <div class="info-label">Khách hàng</div>
                            <div class="info-value"><?php echo htmlspecialchars((string)($booking['ho_ten'] ?? 'N/A')); ?></div>
                        </article>
                        <article class="info-box">
                            <div class="info-label">Tour</div>
                            <div class="info-value"><?php echo htmlspecialchars((string)($booking['ten_tour'] ?? 'N/A')); ?></div>
                        </article>
                        <article class="info-box">
                            <div class="info-label">Ngày khởi hành</div>
                            <div class="info-value">
                                <?php if (!empty($booking['ngay_khoi_hanh'])): ?>
                                    <?php echo date('d/m/Y', strtotime((string)$booking['ngay_khoi_hanh'])); ?>
                                <?php else: ?>
                                    Chưa cập nhật
                                <?php endif; ?>
                            </div>
                        </article>
                        <article class="info-box">
                            <div class="info-label">Trạng thái booking</div>
                            <div class="info-value"><?php echo htmlspecialchars((string)($booking['trang_thai'] ?? 'N/A')); ?></div>
                        </article>
                        <article class="info-box">
                            <div class="info-label">Trạng thái thanh toán</div>
                            <div class="info-value"><?php echo htmlspecialchars((string)($payment['status'] ?? 'Chưa có giao dịch')); ?></div>
                        </article>
                        <article class="info-box">
                            <div class="info-label">Tổng tiền</div>
                            <div class="info-value"><?php echo number_format((float)($booking['tong_tien'] ?? 0), 0, ',', '.'); ?> VND</div>
                        </article>
                        <article class="info-box">
                            <div class="info-label">Hồ sơ người tham gia</div>
                            <div class="info-value">
                                <?php echo (int)($lookupData['provided_participants'] ?? 0); ?>/<?php echo (int)($lookupData['required_participants'] ?? 0); ?>
                                <?php if (!empty($lookupData['participant_complete'])): ?>
                                    <span class="badge text-bg-success ms-1">Đủ</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning ms-1">Thiếu</span>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>


