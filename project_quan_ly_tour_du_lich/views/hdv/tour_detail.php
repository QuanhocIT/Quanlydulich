<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Tour - HDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        
        .info-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
        }
        
        .info-row {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .action-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
            color: inherit;
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">
                        <i class="bi bi-map"></i> <?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tour không xác định'); ?>
                    </h3>
                    <p class="mb-0 opacity-75">Chi tiết lịch trình tour</p>
                </div>
                <a href="index.php?act=hdv/tours" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <!-- Thông tin tour -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="bi bi-info-circle"></i> Thông tin Tour</h5>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 text-muted">Tên tour:</div>
                            <div class="col-8"><strong><?php echo htmlspecialchars($tour['ten_tour'] ?? 'Tour không xác định'); ?></strong></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 text-muted">Ngày khởi hành:</div>
                            <div class="col-8"><?php echo date('d/m/Y H:i', strtotime($tour['ngay_khoi_hanh'])); ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 text-muted">Ngày kết thúc:</div>
                            <div class="col-8"><?php echo date('d/m/Y H:i', strtotime($tour['ngay_ket_thuc'])); ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 text-muted">Điểm tập trung:</div>
                            <div class="col-8"><?php echo htmlspecialchars($tour['diem_tap_trung'] ?? 'Chưa xác định'); ?></div>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 text-muted">Trạng thái:</div>
                            <div class="col-8">
                                <span class="badge bg-<?php 
                                    echo match($tour['trang_thai']) {
                                        'SapKhoiHanh' => 'primary',
                                        'DangChay' => 'warning',
                                        'HoanThanh' => 'success',
                                        'DaHuy' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php 
                                    echo match($tour['trang_thai']) {
                                        'SapKhoiHanh' => 'Sắp khởi hành',
                                        'DangChay' => 'Đang chạy',
                                        'HoanThanh' => 'Hoàn thành',
                                        'DaHuy' => 'Đã hủy',
                                        default => $tour['trang_thai']
                                    };
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($tour['ghi_chu'])): ?>
                    <div class="info-row">
                        <div class="row">
                            <div class="col-4 text-muted">Ghi chú:</div>
                            <div class="col-8"><?php echo nl2br(htmlspecialchars($tour['ghi_chu'] ?? '')); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Mô tả tour -->
                <?php if (!empty($tour['mo_ta'])): ?>
                <div class="info-card">
                    <h5 class="mb-3"><i class="bi bi-file-text"></i> Mô tả Tour</h5>
                    <div><?php echo nl2br(htmlspecialchars($tour['mo_ta'] ?? '')); ?></div>
                </div>
                <?php endif; ?>
                <!-- Lịch trình chi tiết -->
                <div class="info-card">
                    <h5 class="mb-3"><i class="bi bi-list-check"></i> Lịch trình chi tiết</h5>
                    <?php if (!empty($lichTrinhList)): ?>
                        <ol class="list-group list-group-numbered">
                            <?php foreach ($lichTrinhList as $lichTrinh): ?>
                                <li class="list-group-item mb-2">
                                    <div class="fw-bold text-primary">Ngày <?= htmlspecialchars($lichTrinh['ngay_thu']) ?>: <?= htmlspecialchars($lichTrinh['dia_diem']) ?></div>
                                    <div><?= nl2br(htmlspecialchars($lichTrinh['hoat_dong'])) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <div class="text-muted">Chưa có lịch trình chi tiết cho tour này.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Quick Actions -->
                <h5 class="mb-3">Hành động nhanh</h5>
                
                <a href="index.php?act=hdv/khach&tour_id=<?php echo $tour['id']; ?>" class="action-card mb-3">
                    <div class="action-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-people"></i>
                    </div>
                    <h6>Danh sách Khách</h6>
                    <small class="text-muted">Xem thông tin khách trong đoàn</small>
                </a>

                <a href="index.php?act=hdv/checkin&tour_id=<?php echo $tour['id']; ?>" class="action-card mb-3">
                    <div class="action-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-check2-square"></i>
                    </div>
                    <h6>Check-in & Điểm danh</h6>
                    <small class="text-muted">Xác nhận và điểm danh khách</small>
                </a>

                <a href="index.php?act=hdv/nhat_ky&tour_id=<?php echo $tour['id']; ?>" class="action-card mb-3">
                    <div class="action-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h6>Nhật ký Tour</h6>
                    <small class="text-muted">Ghi chú hành trình, sự cố</small>
                </a>

                <a href="index.php?act=hdv/yeu_cau_dac_biet&tour_id=<?php echo $tour['id']; ?>" class="action-card mb-3">
                    <div class="action-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h6>Yêu cầu đặc biệt</h6>
                    <small class="text-muted">Ăn chay, bệnh lý, v.v.</small>
                </a>

                <?php if ($tour['trang_thai'] === 'HoanThanh'): ?>
                <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo $tour['id']; ?>" class="action-card mb-3">
                    <div class="action-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-star"></i>
                    </div>
                    <h6>Đánh giá & Phản hồi</h6>
                    <small class="text-muted">Gửi đánh giá tour</small>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 