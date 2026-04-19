<?php

?><!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn - Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: rgba(15, 23, 42, 0.12);
            --leaf: #15233b;
            --leaf-2: #20365f;
            --gold: #d6b26d;
        }
        body.invoice-page {
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(214,178,109,.16), transparent 60%),
                radial-gradient(900px 520px at 85% 0%, rgba(11,18,32,.08), transparent 55%),
                #f5f6f8;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            min-height: 100vh;
        }
        .invoice-shell { padding: 24px 0 56px; }
        .layout-shell {
            width: min(1500px, calc(100% - 48px));
            margin: 0 auto;
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
            margin-bottom: 22px;
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
            background: linear-gradient(135deg, var(--leaf), var(--leaf-2));
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
            background:
                linear-gradient(115deg, rgba(11,18,32,.93), rgba(21,35,59,.78)),
                url('https://images.unsplash.com/photo-1522202222206-b750505f51f0?auto=format&fit=crop&w=1800&q=80') center/cover;
            border: 1px solid rgba(214,178,109,.2);
            border-radius: 32px;
            box-shadow: 0 30px 80px rgba(2,6,23,.22);
            color: #fff;
            margin-bottom: 18px;
            overflow: hidden;
            padding: 34px 30px;
            position: relative;
        }
        .hero::after {
            background: rgba(214,178,109,.14);
            border-radius: 999px;
            content: "";
            height: 260px;
            position: absolute;
            right: -86px;
            top: -86px;
            width: 260px;
        }
        .hero-content {
            max-width: 760px;
            position: relative;
            z-index: 1;
        }
        .hero h1 {
            font-family: "Playfair Display", serif;
            font-size: clamp(2rem, 4vw, 3.25rem);
            line-height: 1.05;
            margin: 0 0 10px;
            letter-spacing: .2px;
        }
        .hero p {
            color: rgba(255,255,255,.84);
            font-size: 1rem;
            margin: 0;
            line-height: 1.75;
        }
        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }
        .hero-btn {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-weight: 800;
            gap: 8px;
            padding: 10px 16px;
            text-decoration: none;
        }
        .hero-btn.main {
            background: linear-gradient(135deg, var(--gold), #e2bf78);
            color: #132033;
            box-shadow: 0 10px 24px rgba(185,137,61,.24);
        }
        .hero-btn.ghost {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(214,178,109,.35);
            color: #f7e5b6;
        }
        .invoice-card {
            background: rgba(255,255,255,.9);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 14px 38px rgba(2,6,23,.1);
        }
        .invoice-header {
            border-bottom: 1px solid rgba(15,23,42,.12);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .card {
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 14px 38px rgba(2,6,23,.08);
            overflow: hidden;
        }
        .card-header {
            background: rgba(255,255,255,.85);
            border-bottom: 1px solid var(--line);
            font-weight: 800;
        }
        .alert {
            border-radius: 16px;
            box-shadow: 0 10px 26px rgba(15,23,42,.1);
        }
        @media (max-width: 992px) {
            .topbar { align-items: flex-start; flex-direction: column; }
            .nav-actions { justify-content: flex-start; }
        }
        @media (max-width: 768px) {
            .invoice-shell { padding-bottom: 34px; }
            .layout-shell { width: min(100% - 24px, 1500px); }
            .hero { border-radius: 24px; padding: 28px 20px; }
            .invoice-card { padding: 1.2rem; }
        }
    </style>
</head>
<body class="invoice-page">
    <main class="invoice-shell">
    <div class="layout-shell">
        <header class="topbar">
            <a class="brand" href="index.php?act=khachHang/dashboard">
                <span class="brand-mark"><i class="bi bi-compass"></i></span>
                <span>DuLichPro</span>
            </a>
            <nav class="nav-actions" aria-label="Điều hướng khách hàng">
                <a class="nav-pill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>
                <a class="nav-pill" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-stars"></i> Tour nổi bật</a>
                <a class="nav-pill" href="index.php?act=khachHang/yeuCauTour"><i class="bi bi-suitcase2"></i> Tour đã đặt</a>
                <a class="nav-pill" href="index.php?act=khachHang/capNhatThongTin"><i class="bi bi-person-gear"></i> Hồ sơ</a>
                <a class="nav-pill is-active" href="index.php?act=khachHang/hoaDon"><i class="bi bi-receipt"></i> Hóa đơn</a>
            </nav>
        </header>

        <section class="hero">
            <div class="hero-content">
                <h1>Hóa đơn và thanh toán</h1>
                <p>Theo dõi trạng thái giao dịch, xem chi tiết hóa đơn và hoàn tất thanh toán trực tuyến trong cùng một màn hình.</p>
                <div class="hero-actions">
                    <a href="index.php?act=khachHang/lichSuThanhToan" class="hero-btn main"><i class="bi bi-clock-history"></i> Lịch sử thanh toán</a>
                    <a href="index.php?act=khachHang/yeuCauTour" class="hero-btn ghost"><i class="bi bi-arrow-left"></i> Quay lại tour đã đặt</a>
                </div>
            </div>
        </section>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($booking) && $booking): ?>
            <div class="invoice-card">
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Hóa đơn #<?php echo $booking['booking_id']; ?></h3>
                            <p class="text-muted mb-0">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($booking['ngay_dat'] ?? '')); ?></p>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-<?php 
                                echo match($booking['trang_thai']) {
                                    'ChoXacNhan' => 'warning',
                                    'DaCoc' => 'info',
                                    'HoanTat' => 'success',
                                    'Huy' => 'danger',
                                    default => 'secondary'
                                };
                            ?> fs-6">
                                <?php echo htmlspecialchars($booking['trang_thai'] ?? ''); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Thông tin khách hàng</h5>
                        <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($booking['ho_ten'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($booking['email'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($booking['so_dien_thoai'] ?? ''); ?></p>
                        <?php if (!empty($booking['dia_chi'])): ?>
                            <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($booking['dia_chi']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h5>Thông tin tour</h5>
                        <p class="mb-1"><strong>Tour:</strong> <?php echo htmlspecialchars($booking['ten_tour'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Loại tour:</strong> <?php echo htmlspecialchars($booking['loai_tour'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Ngày khởi hành:</strong> <?php echo !empty($booking['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'Chưa xác định'; ?></p>
                        <p class="mb-0"><strong>Số người:</strong> <?php echo $booking['so_nguoi'] ?? 0; ?> người</p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Mô tả</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['ten_tour'] ?? ''); ?></td>
                                <td class="text-end"><?php echo $booking['so_nguoi'] ?? 0; ?> người</td>
                                <td class="text-end"><?php echo number_format((float)($booking['gia_co_ban'] ?? 0)); ?> VNĐ</td>
                                <td class="text-end"><strong><?php echo number_format((float)($booking['tong_tien'] ?? 0)); ?> VNĐ</strong></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Tổng cộng:</th>
                                <th class="text-end"><?php echo number_format((float)($booking['tong_tien'] ?? 0)); ?> VNĐ</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($latestPayment)): ?>
                    <?php
                        $paymentStatus = $latestPayment['status'] ?? '';
                        $paymentBadge = match($paymentStatus) {
                            'ThanhCong' => 'success',
                            'DangXuLy' => 'warning text-dark',
                            'ThatBai' => 'danger',
                            default => 'secondary'
                        };
                        $phoneDigits = preg_replace('/\D+/', '', (string)($booking['so_dien_thoai'] ?? ''));
                        $transferNoteExact = 'BOOKING_' . (int)($booking['booking_id'] ?? 0) . '_' . $phoneDigits;
                    ?>
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>Thanh toán online mới nhất:</strong>
                                #<?php echo (int)$latestPayment['payment_id']; ?>
                                - <?php echo htmlspecialchars($latestPayment['payment_method'] ?? 'N/A'); ?>
                                - <?php echo number_format((float)($latestPayment['amount'] ?? 0)); ?> VNĐ
                            </div>
                            <span class="badge bg-<?php echo $paymentBadge; ?> fs-6"><?php echo htmlspecialchars($paymentStatus); ?></span>
                        </div>
                        <div class="small text-muted mt-1">
                            <?php echo !empty($latestPayment['payment_date']) ? date('d/m/Y H:i', strtotime($latestPayment['payment_date'])) : 'N/A'; ?>
                        </div>
                        <?php if ($paymentStatus === 'DangXuLy'): ?>
                            <div class="small text-warning mt-1">
                                He thong dang cho webhook/admin xac nhan da nhan tien. Trang se tu dong cap nhat sau 10 giay.
                            </div>
                            <div class="mt-3 p-3 rounded" style="background:#fff8db;border:1px dashed #f0ad4e;">
                                <div class="fw-semibold mb-1"><i class="bi bi-info-circle me-1"></i>Noi dung chuyen khoan chinh xac cua ban:</div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span id="transferNoteExact" class="px-2 py-1 rounded" style="background:#fff;border:1px solid #e6e6e6;font-family:monospace;"><?php echo htmlspecialchars($transferNoteExact); ?></span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyTransferNoteExact()">
                                        <i class="bi bi-clipboard me-1"></i>Sao chep
                                    </button>
                                </div>
                                <div class="small text-muted mt-1">Khach chi can copy dung chuoi nay de he thong doi soat tu dong nhanh hon.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($giaoDichList)): ?>
                    <h5 class="mb-3">Lịch sử thanh toán</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Ngày</th>
                                    <th>Mô tả</th>
                                    <th class="text-end">Số tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $tongDaThanhToan = 0;
                                foreach ($giaoDichList as $gd): 
                                    if ($gd['loai'] === 'Thu') {
                                        $tongDaThanhToan += (float)$gd['so_tien'];
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($gd['ngay_giao_dich'] ?? '')); ?></td>
                                        <td><?php echo htmlspecialchars($gd['mo_ta'] ?? ''); ?></td>
                                        <td class="text-end <?php echo $gd['loai'] === 'Thu' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $gd['loai'] === 'Thu' ? '+' : '-'; ?>
                                            <?php echo number_format((float)($gd['so_tien'] ?? 0)); ?> VNĐ
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Tổng đã thanh toán:</th>
                                    <th class="text-end text-success"><?php echo number_format($tongDaThanhToan); ?> VNĐ</th>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-end">Còn nợ:</th>
                                    <th class="text-end <?php echo ($booking['tong_tien'] - $tongDaThanhToan) > 0 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo number_format(max(0, (float)$booking['tong_tien'] - $tongDaThanhToan)); ?> VNĐ
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (in_array($booking['trang_thai'], ['ChoXacNhan', 'DaCoc'])): ?>
                    <div class="text-end">
                        <?php $isPendingPayment = !empty($latestPayment) && (($latestPayment['status'] ?? '') === 'DangXuLy'); ?>
                        <?php if ($isPendingPayment): ?>
                            <button type="button" class="btn btn-secondary btn-lg" disabled>
                                <i class="bi bi-hourglass-split me-2"></i>Đang xử lý thanh toán
                            </button>
                        <?php else: ?>
                            <a href="index.php?act=khachHang/thanhToan&booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card me-2"></i>Thanh toán qua cổng online
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($booking['ghi_chu'])): ?>
                    <div class="mt-4">
                        <h5>Ghi chú</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($booking['ghi_chu'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif (isset($bookings) && !empty($bookings)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Chọn hóa đơn để xem</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mã booking</th>
                                    <th>Tour</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td>#<?php echo $b['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($b['ten_tour'] ?? ''); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($b['ngay_dat'] ?? '')); ?></td>
                                        <td><?php echo number_format((float)($b['tong_tien'] ?? 0)); ?> VNĐ</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($b['trang_thai']) {
                                                    'ChoXacNhan' => 'warning',
                                                    'DaCoc' => 'info',
                                                    'HoanTat' => 'success',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo htmlspecialchars($b['trang_thai'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?act=khachHang/hoaDon&booking_id=<?php echo $b['booking_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Xem
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>Bạn chưa có booking nào.
            </div>
        <?php endif; ?>
    </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($latestPayment) && (($latestPayment['status'] ?? '') === 'DangXuLy')): ?>
        <script>
            function copyTransferNoteExact() {
                var el = document.getElementById('transferNoteExact');
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

            setTimeout(function () {
                window.location.reload();
            }, 10000);
        </script>
    <?php endif; ?>
</body>
</html>


