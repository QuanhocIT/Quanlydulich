<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Khách - HDV</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
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
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        .table-responsive {
            border-radius: 1rem;
            overflow: hidden;
        }
        .fs-5 {
            font-size: 1.15rem !important;
        }
        .fw-bold {
            font-weight: 600 !important;
        }
        .mb-1 {
            margin-bottom: 0.5rem !important;
        }
        .mb-2 {
            margin-bottom: 1rem !important;
        }
    </style>
</head>
<body class="bg-light hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="bi bi-people"></i> Danh sách Khách
                    </h3>
                    <?php if ($tour): ?>
                    <p class="mb-0 opacity-75"><?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?></p>
                    <?php endif; ?>
                </div>
                <a href="index.php?act=hdv/dashboard" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Trang chủ
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Tour Selector -->
        <?php if (empty($tour)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Chọn tour để xem danh sách khách</h5>
                <select class="form-select" onchange="if(this.value) window.location.href='index.php?act=hdv/khach&tour_id=' + this.value">
                    <option value="">-- Chọn tour --</option>
                    <?php foreach($tours_list as $t): ?>
                    <option value="<?php echo $t['id']; ?>">
                        <?php echo htmlspecialchars($t['ten_tour'] ?? ''); ?> 
                        (<?php echo date('d/m/Y', strtotime($t['ngay_khoi_hanh'] ?? 'now')); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Tour Info -->
        <div class="tour-info-card mb-2">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="fw-bold mb-1 text-primary">
                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?>
                    </h4>
                    <div class="text-muted mb-2">
                        <i class="bi bi-calendar3"></i> <?php echo date('d/m/Y', strtotime($tour['ngay_khoi_hanh'] ?? 'now')); ?>
                        &rarr; <?php echo date('d/m/Y', strtotime($tour['ngay_ket_thuc'] ?? 'now')); ?>
                    </div>
                    <div class="text-muted">
                        <i class="bi bi-pin-map"></i> Điểm tập trung: <span class="fw-bold"> <?php echo htmlspecialchars($tour['diem_tap_trung'] ?? 'Chưa xác định'); ?> </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-primary fs-5">
                        <i class="bi bi-people"></i> <?php echo count($khach_list); ?> khách
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer List -->
        <?php if (!empty($khach_list)): ?>
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Khách</th>
                            <th>CMND/Passport</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Quốc tịch</th>
                            <th>Liên hệ</th>
                            <th>Địa chỉ</th>
                            <th>Trạng thái</th>
                            <th>Booking ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($khach_list as $index => $khach): ?>
                        <tr>
                            <td class="fw-bold text-primary">#<?php echo $index + 1; ?></td>
                            <td class="d-flex align-items-center">
                                <span class="customer-avatar">
                                    <?php 
                                    $name = $khach['ho_ten'] ?? 'Khách';
                                    $initials = implode('', array_map(fn($w) => mb_substr($w,0,1), explode(' ', $name)));
                                    echo strtoupper($initials);
                                    ?>
                                </span>
                                <span class="fw-bold"> <?php echo htmlspecialchars($khach['ho_ten'] ?? 'Khách'); ?> </span>
                            </td>
                            <td>
                                <?php if (!empty($khach['so_cmnd'])): ?>
                                    <span class="badge bg-info-subtle text-dark mb-1">CMND: <?php echo htmlspecialchars($khach['so_cmnd'] ?? ''); ?></span><br>
                                <?php endif; ?>
                                <?php if (!empty($khach['so_passport'])): ?>
                                    <span class="badge bg-secondary-subtle text-dark mb-1">Passport: <?php echo htmlspecialchars($khach['so_passport'] ?? ''); ?></span>
                                <?php endif; ?>
                                <?php if (empty($khach['so_cmnd']) && empty($khach['so_passport'])): ?>
                                    <span class="text-muted">Chưa cập nhật</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo !empty($khach['ngay_sinh']) ? date('d/m/Y', strtotime($khach['ngay_sinh'])) : '<span class="text-muted">N/A</span>'; ?>
                            </td>
                            <td>
                                <?php 
                                $gioiTinhLabels = ['Nam' => 'Nam', 'Nu' => 'Nữ', 'Khac' => 'Khác'];
                                echo $gioiTinhLabels[$khach['gioi_tinh']] ?? $khach['gioi_tinh'] ?? '<span class="text-muted">N/A</span>';
                                ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($khach['quoc_tich'] ?? 'Việt Nam'); ?>
                            </td>
                            <td>
                                <?php if (!empty($khach['email'])): ?>
                                    <small><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($khach['email'] ?? ''); ?></small><br>
                                <?php endif; ?>
                                <?php if (!empty($khach['so_dien_thoai'])): ?>
                                    <small><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($khach['so_dien_thoai'] ?? ''); ?></small>
                                <?php endif; ?>
                                <?php if (empty($khach['email']) && empty($khach['so_dien_thoai'])): ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo !empty($khach['dia_chi']) ? htmlspecialchars($khach['dia_chi'] ?? '') : '<span class="text-muted">N/A</span>'; ?>
                            </td>
                            <td>
                                <?php
                                $trangThaiLabels = [
                                    'ChuaCheckIn' => 'Chưa check-in',
                                    'DaCheckIn' => 'Đã check-in',
                                    'DaCheckOut' => 'Đã check-out'
                                ];
                                $trangThaiClass = [
                                    'ChuaCheckIn' => 'warning',
                                    'DaCheckIn' => 'success',
                                    'DaCheckOut' => 'secondary'
                                ];
                                $trangThai = $khach['trang_thai'] ?? 'ChuaCheckIn';
                                ?>
                                <span class="badge badge-status bg-<?php echo $trangThaiClass[$trangThai] ?? 'warning'; ?>">
                                    <?php echo $trangThaiLabels[$trangThai] ?? $trangThai; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($khach['booking_id'])): ?>
                                    <span class="badge bg-primary-subtle text-dark">#<?php echo $khach['booking_id']; ?></span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Chưa có khách nào trong danh sách. Vui lòng thêm khách vào lịch khởi hành này.
            </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>

    <script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
