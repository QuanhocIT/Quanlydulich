<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điểm danh khách - HDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .tour-info-card {
            background: white;
            border-radius: 1.2rem;
            box-shadow: 0 0.25rem 0.5rem rgba(102,126,234,0.08);
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
        }
        .table-custom {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 0.25rem 0.5rem rgba(102,126,234,0.08);
        }
        .table-custom th {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff;
            font-weight: 600;
            border: none;
        }
        .table-custom td {
            background: #fff;
            vertical-align: middle;
            border: none;
        }
        .table-custom tbody tr {
            transition: box-shadow 0.2s;
        }
        .table-custom tbody tr:hover {
            box-shadow: 0 0.5rem 1rem rgba(102,126,234,0.12);
            background: #f7f8fa;
        }
        .badge-status {
            font-size: 1rem;
            padding: 0.5em 1em;
            border-radius: 1em;
            font-weight: 500;
            box-shadow: 0 0.15rem 0.3rem rgba(102,126,234,0.08);
        }
        .checkin-form-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .checkin-form-inline textarea {
            min-width: 180px;
            border-radius: 0.5em;
            border: 1px solid #e0e0e0;
            padding: 0.5em;
        }
        .checkin-form-inline select {
            border-radius: 0.5em;
            border: 1px solid #e0e0e0;
            padding: 0.5em;
        }
        .checkin-form-inline button {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff;
            border: none;
            padding: 0.6em 1.5em;
            border-radius: 0.7em;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 0.15rem 0.3rem rgba(102,126,234,0.08);
            transition: background 0.2s;
        }
        .checkin-form-inline button:hover {
            background: linear-gradient(90deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        .fw-bold { font-weight: 600 !important; }
        .text-primary { color: var(--primary-color) !important; }
    </style>
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-person-check"></i> Điểm danh khách theo tour
                    </h2>
                </div>
                <a href="index.php?act=hdv/lichLamViec" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Quay lại lịch làm việc
                </a>
            </div>
        </div>
    </div>
    <div class="container">

        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; padding: 12px; margin: 15px 0; border-radius: 4px; color: #155724;">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; padding: 12px; margin: 15px 0; border-radius: 4px; color: #721c24;">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($lichKhoiHanhList)): ?>
            <form method="GET" action="index.php" style="margin: 20px 0;">
                <input type="hidden" name="act" value="hdv/checkInKhach">
                <label for="lich_id"><strong>Chọn lịch khởi hành:</strong></label>
                <select name="lich_id" id="lich_id" onchange="this.form.submit()">
                    <?php foreach ($lichKhoiHanhList as $lich): ?>
                        <option value="<?php echo $lich['id']; ?>" <?php echo (isset($selectedLich) && $selectedLich && $selectedLich['id'] == $lich['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lich['ten_tour'] ?? 'Tour'); ?> 
                            (<?php echo !empty($lich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])) : 'N/A'; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($selectedLich): ?>
                <div class="tour-info-card mb-2">
                    <h4 class="fw-bold mb-1 text-primary">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($selectedLich['ten_tour'] ?? 'Tour'); ?>
                    </h4>
                    <div class="text-muted mb-2">
                        <i class="bi bi-calendar3"></i> <?php echo !empty($selectedLich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($selectedLich['ngay_khoi_hanh'])) : 'N/A'; ?>
                        &rarr; <?php echo !empty($selectedLich['ngay_ket_thuc']) ? date('d/m/Y', strtotime($selectedLich['ngay_ket_thuc'])) : 'N/A'; ?>
                    </div>
                    <div class="text-muted">
                        <i class="bi bi-pin-map"></i> Điểm tập trung: <span class="fw-bold"> <?php echo htmlspecialchars($selectedLich['diem_tap_trung'] ?? 'Chưa cập nhật'); ?> </span>
                    </div>
                </div>

                <?php if (!empty($danhSachKhach)): ?>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Khách/Booking</th>
                                    <th>Liên hệ</th>
                                    <th>Nhóm</th>
                                    <th>Trạng thái điểm danh</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; foreach ($danhSachKhach as $khach): ?>
                                    <?php
                                        $khachHangId = (int)($khach['khach_hang_id'] ?? 0);
                                        $soNguoi = (int)($khach['so_nguoi'] ?? 1);
                                        $nhomLabel = $soNguoi >= 10 ? 'Đoàn lớn' : ($soNguoi >= 5 ? 'Nhóm' : ($soNguoi >= 3 ? 'Nhóm nhỏ' : ($soNguoi == 2 ? 'Cặp' : 'Khách lẻ')));
                                        $checkin = $checkinMap[$khachHangId] ?? null;
                                        $trangThai = $checkin['trang_thai'] ?? 'ChuaCheckIn';
                                        $badgeClass = match ($trangThai) {
                                            'DaCheckIn' => 'badge-status bg-success',
                                            'DaCheckOut' => 'badge-status bg-secondary',
                                            default => 'badge-status bg-warning text-dark'
                                        };
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary">#<?php echo $stt++; ?></td>
                                        <td>
                                            <span class="fw-bold"> <?php echo htmlspecialchars($khach['ho_ten'] ?? 'Khách'); ?> </span><br>
                                            <span class="badge bg-primary-subtle text-dark">Booking #<?php echo $khach['booking_id']; ?></span><br>
                                            <?php if (!empty($khach['dia_chi'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($khach['dia_chi']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($khach['email'] ?? ''); ?><br>
                                            <?php echo htmlspecialchars($khach['so_dien_thoai'] ?? ''); ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?php echo $soNguoi; ?> khách</span><br>
                                            <small class="text-muted"><?php echo $nhomLabel; ?></small>
                                        </td>
                                        <td>
                                            <span class="<?php echo $badgeClass; ?>">
                                                <?php
                                                    $labels = [
                                                        'ChuaCheckIn' => 'Chưa check-in',
                                                        'DaCheckIn' => 'Đã check-in',
                                                        'DaCheckOut' => 'Đã check-out'
                                                    ];
                                                    echo $labels[$trangThai] ?? $trangThai;
                                                ?>
                                            </span>
                                            <?php if (!empty($checkin['checkin_time'])): ?>
                                                <br><small class="text-success">Check-in: <?php echo date('d/m H:i', strtotime($checkin['checkin_time'])); ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($checkin['checkout_time'])): ?>
                                                <br><small class="text-secondary">Check-out: <?php echo date('d/m H:i', strtotime($checkin['checkout_time'])); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="index.php?act=hdv/updateCheckInKhach" class="checkin-form-inline">
                                                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $selectedLich['id']; ?>">
                                                <input type="hidden" name="booking_id" value="<?php echo $khach['booking_id']; ?>">
                                                <input type="hidden" name="khach_hang_id" value="<?php echo $khachHangId; ?>">
                                                <select name="trang_thai">
                                                    <option value="ChuaCheckIn" <?php echo $trangThai === 'ChuaCheckIn' ? 'selected' : ''; ?>>Chưa check-in</option>
                                                    <option value="DaCheckIn" <?php echo $trangThai === 'DaCheckIn' ? 'selected' : ''; ?>>Đã check-in</option>
                                                    <option value="DaCheckOut" <?php echo $trangThai === 'DaCheckOut' ? 'selected' : ''; ?>>Đã check-out</option>
                                                </select>
                                                <textarea name="ghi_chu" rows="2" placeholder="Ghi chú ngắn..."><?php echo htmlspecialchars($checkin['ghi_chu'] ?? ''); ?></textarea>
                                                <button type="submit">Cập nhật</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>Chưa có khách nào được đặt cho lịch khởi hành này.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Không tìm thấy lịch khởi hành bạn chọn.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>B?n chua du?c ph?n c?ng l?ch kh?i h?nh n?o.</p>
        <?php endif; ?>
    </div>
</body>
</html>


