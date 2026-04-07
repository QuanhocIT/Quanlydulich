<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch trình Tour - Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #667eea;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 2rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #667eea;
        }
        .timeline-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-calendar3 me-2"></i>Lịch trình Tour</h2>
            <a href="index.php?act=khachHang/dashboard" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Quay lại
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($tour) && $tour): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Loại tour:</strong> <?php echo htmlspecialchars($tour['loai_tour'] ?? ''); ?></p>
                            <p><strong>Giá cơ bản:</strong> <?php echo number_format((float)($tour['gia_co_ban'] ?? 0)); ?> VNĐ/người</p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($lichKhoiHanh): ?>
                                <p><strong>Ngày khởi hành:</strong> <?php echo date('d/m/Y', strtotime($lichKhoiHanh['ngay_khoi_hanh'] ?? '')); ?></p>
                                <p><strong>Giờ khởi hành:</strong> <?php echo !empty($lichKhoiHanh['gio_khoi_hanh']) ? date('H:i', strtotime($lichKhoiHanh['gio_khoi_hanh'])) : 'Chưa xác định'; ?></p>
                                <p><strong>Điểm tập trung:</strong> <?php echo htmlspecialchars($lichKhoiHanh['dia_diem_tap_trung'] ?? 'Chưa xác định'); ?></p>
                            <?php elseif ($booking): ?>
                                <p><strong>Ngày khởi hành:</strong> <?php echo !empty($booking['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($booking['ngay_khoi_hanh'])) : 'Chưa xác định'; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($tour['mo_ta'])): ?>
                        <div class="mt-3">
                            <h5>Mô tả tour</h5>
                            <p><?php echo nl2br(htmlspecialchars($tour['mo_ta'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($lichTrinhList)): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Lịch trình chi tiết</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php foreach ($lichTrinhList as $index => $lichTrinh): ?>
                                <div class="timeline-item">
                                    <div class="timeline-card">
                                        <h5 class="text-primary">Ngày <?php echo htmlspecialchars($lichTrinh['ngay_thu'] ?? ($index + 1)); ?></h5>
                                        <p class="mb-2"><strong><i class="bi bi-geo-alt me-2"></i>Địa điểm:</strong> <?php echo htmlspecialchars($lichTrinh['dia_diem'] ?? ''); ?></p>
                                        <p class="mb-2"><strong><i class="bi bi-clock me-2"></i>Thời gian:</strong> 
                                            <?php if (!empty($lichTrinh['thoi_gian'])): ?>
                                                <?php echo htmlspecialchars($lichTrinh['thoi_gian']); ?>
                                            <?php else: ?>
                                                Cả ngày
                                            <?php endif; ?>
                                        </p>
                                        <p class="mb-0"><strong><i class="bi bi-activity me-2"></i>Hoạt động:</strong> <?php echo nl2br(htmlspecialchars($lichTrinh['hoat_dong'] ?? '')); ?></p>
                                        <?php if (!empty($lichTrinh['ghi_chu'])): ?>
                                            <div class="mt-2 p-2 bg-light rounded">
                                                <small><i class="bi bi-info-circle me-1"></i><?php echo nl2br(htmlspecialchars($lichTrinh['ghi_chu'])); ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>Chưa có lịch trình chi tiết cho tour này.
                </div>
            <?php endif; ?>

            <?php if (!empty($tour['chinh_sach'])): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>Chính sách</h5>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($tour['chinh_sach'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Không tìm thấy thông tin tour.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


