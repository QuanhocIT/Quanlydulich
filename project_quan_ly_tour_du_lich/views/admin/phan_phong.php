<?php 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php?act=auth/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân Phòng Khách Sạn - Quản Lý Tour Du Lịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
        }
        .booking-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 2px solid #dee2e6;
        }
        .form-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .form-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem 1.5rem;
        }
        .room-card {
            border: none;
            border-left: 4px solid #667eea;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: all 0.3s;
            margin-bottom: 1rem;
        }
        .room-card:hover {
            transform: translateX(8px);
            box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.2);
        }
        .hotel-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }
        .info-item i {
            width: 1.25rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?act=admin/dashboard">
                <i class="bi bi-speedometer2"></i> Quản trị
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?act=admin/quanLyTour">
                            <i class="bi bi-compass"></i> Tour
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="bi bi-building"></i> Phân phòng
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-6 fw-bold mb-2">
                            <i class="bi bi-building"></i> Phân Phòng Khách Sạn
                        </h1>
                        <p class="lead mb-0 opacity-75">Quản lý phân phòng cho khách hàng</p>
                    </div>
                    <a href="index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                       class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($booking): ?>
            <div class="row">
                <!-- Left Column: Booking Info -->
                <div class="col-lg-4">
                    <!-- Booking Info Card -->
                    <div class="booking-info-card">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-file-text text-primary"></i> Thông tin booking
                        </h5>
                        <div class="mb-3">
                            <small class="text-muted d-block">Mã booking</small>
                            <span class="badge bg-primary fs-6">
                                #<?php echo htmlspecialchars($booking['booking_id'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Khách hàng</small>
                            <h6 class="mb-0"><?php echo htmlspecialchars($booking['ho_ten'] ?? 'N/A'); ?></h6>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Số người</small>
                            <span class="badge bg-info">
                                <i class="bi bi-people"></i> <?php echo ($booking['so_nguoi'] ?? 0); ?> người
                            </span>
                        </div>
                        <?php if ($checkin): ?>
                        <div>
                            <small class="text-muted d-block">Trạng thái</small>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Đã check-in
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Room Stats -->
                    <?php if (!empty($roomList)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-graph-up text-primary"></i> Thống kê phòng
                            </h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Tổng số phòng</span>
                                <span class="badge bg-primary fs-5"><?php echo count($roomList); ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Tổng chi phí</span>
                                <span class="text-success fw-bold">
                                    <?php 
                                    $totalCost = array_sum(array_column($roomList, 'gia_phong'));
                                    echo number_format($totalCost, 0, ',', '.'); 
                                    ?> VNĐ
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Form & List -->
                <div class="col-lg-8">
                    <!-- Add Room Form -->
                    <div class="card form-card">
                        <div class="card-header">
                            <i class="bi bi-plus-circle"></i> Thêm phân phòng mới
                        </div>
                        <div class="card-body">
                            <form method="POST" action="index.php?act=admin/phanPhongKhachSan">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanhId; ?>">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                <?php if ($checkin): ?>
                                    <input type="hidden" name="checkin_id" value="<?php echo $checkin['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-building"></i> Tên khách sạn 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="ten_khach_san" class="form-control" required 
                                               list="hotel-list" placeholder="VD: Khách sạn Hoàng Gia">
                                        <datalist id="hotel-list">
                                            <?php foreach ($hotelList as $hotel): ?>
                                                <option value="<?php echo htmlspecialchars($hotel); ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-door-open"></i> Số phòng 
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="so_phong" class="form-control" required placeholder="VD: 301">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-star"></i> Loại phòng
                                        </label>
                                        <select name="loai_phong" class="form-select">
                                            <option value="Standard">Standard</option>
                                            <option value="Superior">Superior</option>
                                            <option value="Deluxe">Deluxe</option>
                                            <option value="Suite">Suite</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-bed"></i> Số giường
                                        </label>
                                        <input type="number" name="so_giuong" class="form-control" value="1" min="1" max="4">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-check"></i> Ngày nhận phòng
                                        </label>
                                        <input type="date" name="ngay_nhan_phong" class="form-control">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-calendar-x"></i> Ngày trả phòng
                                        </label>
                                        <input type="date" name="ngay_tra_phong" class="form-control">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-currency-dollar"></i> Giá phòng (VNĐ)
                                        </label>
                                        <input type="number" name="gia_phong" class="form-control" value="0" min="0" step="1000">
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-info-circle"></i> Trạng thái
                                        </label>
                                        <select name="trang_thai" class="form-select">
                                            <option value="DaDatPhong">Đã đặt phòng</option>
                                            <option value="DaNhanPhong">Đã nhận phòng</option>
                                            <option value="DaTraPhong">Đã trả phòng</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-chat-left-text"></i> Ghi chú
                                        </label>
                                        <textarea name="ghi_chu" class="form-control" rows="2" placeholder="Nhập ghi chú nếu có..."></textarea>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="bi bi-plus-circle"></i> Thêm phòng
                                        </button>
                                        <a href="index.php?act=admin/danhSachKhachTheoTour&lich_khoi_hanh_id=<?php echo $lichKhoiHanhId; ?>" 
                                           class="btn btn-outline-secondary btn-lg ms-2">
                                            <i class="bi bi-x-circle"></i> Hủy
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Room List -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-list-ul"></i> Danh sách phòng đã phân
                                <?php if (!empty($roomList)): ?>
                                    <span class="badge bg-primary ms-2"><?php echo count($roomList); ?></span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($roomList)): ?>
                                <?php foreach ($roomList as $room): ?>
                                    <div class="room-card card">
                                        <div class="card-body">
                                            <div class="d-flex gap-3">
                                                <!-- Hotel Icon -->
                                                <div class="hotel-icon flex-shrink-0">
                                                    <i class="bi bi-building"></i>
                                                </div>
                                                
                                                <!-- Room Info -->
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="fw-bold mb-1">
                                                                <?php echo htmlspecialchars($room['ten_khach_san']); ?>
                                                            </h6>
                                                            <span class="badge bg-primary">
                                                                Phòng <?php echo htmlspecialchars($room['so_phong']); ?>
                                                            </span>
                                                        </div>
                                                        <span class="status-badge <?php 
                                                            echo match($room['trang_thai']) {
                                                                'DaDatPhong' => 'bg-warning text-dark',
                                                                'DaNhanPhong' => 'bg-success',
                                                                'DaTraPhong' => 'bg-info',
                                                                default => 'bg-secondary'
                                                            };
                                                        ?>">
                                                            <?php 
                                                            echo match($room['trang_thai']) {
                                                                'DaDatPhong' => '📌 Đã đặt',
                                                                'DaNhanPhong' => '✅ Đã nhận',
                                                                'DaTraPhong' => '🔄 Đã trả',
                                                                default => $room['trang_thai']
                                                            };
                                                            ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="row g-2 mb-2">
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <i class="bi bi-star text-warning"></i>
                                                                <span><?php echo htmlspecialchars($room['loai_phong']); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <i class="bi bi-bed text-primary"></i>
                                                                <span><?php echo $room['so_giuong']; ?> giường</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="info-item">
                                                                <i class="bi bi-currency-dollar text-success"></i>
                                                                <span class="fw-bold text-success">
                                                                    <?php echo number_format($room['gia_phong'], 0, ',', '.'); ?> VNĐ
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($room['ngay_nhan_phong']): ?>
                                                    <div class="info-item mb-2">
                                                        <i class="bi bi-calendar-range text-info"></i>
                                                        <span>
                                                            <?php echo date('d/m/Y', strtotime($room['ngay_nhan_phong'])); ?> 
                                                            <i class="bi bi-arrow-right mx-1"></i>
                                                            <?php echo date('d/m/Y', strtotime($room['ngay_tra_phong'])); ?>
                                                        </span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($room['ghi_chu']): ?>
                                                    <div class="info-item">
                                                        <i class="bi bi-chat-left-text text-muted"></i>
                                                        <span class="fst-italic"><?php echo htmlspecialchars($room['ghi_chu']); ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-3">
                                                        <form method="POST" action="index.php?act=admin/phanPhongKhachSan" style="display: inline;" 
                                                              onsubmit="return confirm('Bạn có chắc muốn xóa phân phòng này?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $room['id']; ?>">
                                                            <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanhId; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i> Xóa
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-building"></i>
                                    <p class="mb-0">Chưa có phân phòng nào</p>
                                    <small class="text-muted">Sử dụng form bên trên để thêm phòng</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-exclamation-triangle fs-1 text-danger opacity-25"></i>
                    <p class="mt-3 text-danger fw-bold">Không tìm thấy thông tin booking</p>
                    <a href="index.php?act=admin/quanLyBooking" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
