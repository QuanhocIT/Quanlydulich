<?php
$pageTitle = 'Quản lý HDV Nâng cao';
$currentPage = 'nhanSu';
ob_start();
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css' rel='stylesheet' />
<style>
        .hdv-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 4px solid #007bff;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }
        .hdv-card:hover {
            transform: translateY(-2px);
            background: rgba(45, 45, 45, 0.6);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        .status-sansang { background: rgba(40, 167, 69, 0.3); color: #5cb85c; }
        .status-dangban { background: rgba(255, 193, 7, 0.3); color: #ffc107; }
        .status-nghiphep { background: rgba(220, 53, 69, 0.3); color: #dc3545; }
        .status-tamnhi { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .stat-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }
        .stat-card.bg-success {
            background: rgba(40, 167, 69, 0.3) !important;
        }
        .stat-card.bg-warning {
            background: rgba(255, 193, 7, 0.3) !important;
        }
        .stat-card.bg-info {
            background: rgba(0, 123, 255, 0.3) !important;
        }
        .stat-card.bg-primary {
            background: rgba(13, 110, 253, 0.3) !important;
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .fc-event {
            cursor: pointer;
        }
        
        .loai-hdv-badge {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }
        
        .nav-tabs {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-tabs .nav-link {
            color: var(--text-muted);
            border: 1px solid transparent;
        }
        .nav-tabs .nav-link.active {
            background: rgba(13, 110, 253, 0.3);
            border-color: rgba(255, 255, 255, 0.1) rgba(255, 255, 255, 0.1) transparent;
            color: var(--text-light);
        }
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: rgba(45, 45, 45, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .card-body {
            color: var(--text-light);
        }
        .table {
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .table-light {
            background: rgba(45, 45, 45, 0.7) !important;
        }
        .form-control, .form-select {
            background: rgba(45, 45, 45, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .form-control:focus, .form-select:focus {
            background: rgba(45, 45, 45, 0.8);
            border-color: var(--accent-gold);
            color: var(--text-light);
        }
        .form-label {
            color: var(--text-light);
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-primary {
            background: rgba(13, 110, 253, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-info {
            background: rgba(0, 123, 255, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-success {
            background: rgba(40, 167, 69, 0.3) !important;
            color: #5cb85c !important;
        }
        .bg-secondary {
            background: rgba(108, 117, 125, 0.3) !important;
            color: #adb5bd !important;
        }
        .bg-danger {
            background: rgba(220, 53, 69, 0.3) !important;
            color: #dc3545 !important;
        }
        .text-white {
            color: var(--text-light) !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: rgba(13, 110, 253, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(13, 110, 253, 0.5);
        }
        .btn-primary:hover {
            background: rgba(13, 110, 253, 0.5);
        }
        .btn-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-success:hover {
            background: rgba(40, 167, 69, 0.5);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .btn-outline-primary {
            background: transparent;
            color: #4da3ff;
            border: 1px solid rgba(13, 110, 253, 0.5);
        }
        .btn-outline-primary:hover {
            background: rgba(13, 110, 253, 0.3);
        }
        .btn-outline-success {
            background: transparent;
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-outline-success:hover {
            background: rgba(40, 167, 69, 0.3);
        }
        .btn-outline-info {
            background: transparent;
            color: #4da3ff;
            border: 1px solid rgba(0, 123, 255, 0.5);
        }
        .btn-outline-info:hover {
            background: rgba(0, 123, 255, 0.3);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .list-group-item {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .modal-content {
            background: rgba(45, 45, 45, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .modal-title {
            color: var(--text-light);
        }
        .btn-close {
            filter: invert(1);
        }
    </style>

<div style="padding: 20px;">
    <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; ?>
        <div class="alert alert-<?php echo htmlspecialchars($f['type']); ?>" style="display: flex; justify-content: space-between; align-items: center;">
            <span><?php echo htmlspecialchars($f['message']); ?></span>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">&times;</button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

    <div class="page-header-section" style="margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-person-badge" style="color: var(--accent-gold);"></i> Quản lý HDV Nâng cao
            </h1>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                    <i class="bi bi-calendar-check"></i> Phân công HDV
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
                    <i class="bi bi-bell"></i> Gửi thông báo
                </button>
            </div>
        </div>
    </div>

        <!-- Thống kê tổng quan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">HDV Sẵn sàng</h6>
                            <h2 class="mb-0"><?php echo $stats['san_sang'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-check-circle stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-warning text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Đang làm việc</h6>
                            <h2 class="mb-0"><?php echo $stats['dang_ban'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-briefcase stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Nghỉ phép</h6>
                            <h2 class="mb-0"><?php echo $stats['nghi_phep'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-calendar-x stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Tour tháng này</h6>
                            <h2 class="mb-0"><?php echo $stats['tour_thang'] ?? 0; ?></h2>
                        </div>
                        <i class="bi bi-graph-up stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-danh-sach">
                    <i class="bi bi-list-ul"></i> Danh sách HDV
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lich">
                    <i class="bi bi-calendar3"></i> Lịch làm việc
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-hieu-suat">
                    <i class="bi bi-bar-chart"></i> Hiệu suất
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-thong-bao">
                    <i class="bi bi-bell"></i> Thông báo
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab 1: Danh sách HDV -->
            <div class="tab-pane fade show active" id="tab-danh-sach">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="filterLoaiHDV">
                            <option value="">Tất cả loại HDV</option>
                            <option value="NoiDia">Nội địa</option>
                            <option value="QuocTe">Quốc tế</option>
                            <option value="ChuyenTuyen">Chuyên tuyến</option>
                            <option value="ChuyenDoan">Chuyên đoàn</option>
                            <option value="TongHop">Tổng hợp</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterTrangThai">
                            <option value="">Tất cả trạng thái</option>
                            <option value="SanSang">Sẵn sàng</option>
                            <option value="DangBan">Đang bận</option>
                            <option value="NghiPhep">Nghỉ phép</option>
                            <option value="TamNghi">Tạm nghỉ</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="searchHDV" placeholder="Tìm kiếm theo tên, chuyên tuyến...">
                    </div>
                </div>

                <div class="row" id="hdvList">
                    <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                    <div class="col-md-6 col-lg-4 mb-3 hdv-item" 
                         data-loai="<?php echo htmlspecialchars($hdv['loai_hdv'] ?? ''); ?>"
                         data-trangthai="<?php echo htmlspecialchars($hdv['trang_thai_lam_viec'] ?? ''); ?>">
                        <div class="card hdv-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">
                                        <?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?>
                                    </h5>
                                    <span class="status-badge status-<?php echo strtolower($hdv['trang_thai_lam_viec'] ?? 'sansang'); ?>">
                                        <?php 
                                        $status_map = [
                                            'SanSang' => 'Sẵn sàng',
                                            'DangBan' => 'Đang bận',
                                            'NghiPhep' => 'Nghỉ phép',
                                            'TamNghi' => 'Tạm nghỉ'
                                        ];
                                        echo $status_map[$hdv['trang_thai_lam_viec'] ?? 'SanSang'];
                                        ?>
                                    </span>
                                </div>
                                
                                <span class="badge bg-primary loai-hdv-badge mb-2">
                                    <?php 
                                    $loai_map = [
                                        'NoiDia' => 'Nội địa',
                                        'QuocTe' => 'Quốc tế',
                                        'ChuyenTuyen' => 'Chuyên tuyến',
                                        'ChuyenDoan' => 'Chuyên đoàn',
                                        'TongHop' => 'Tổng hợp'
                                    ];
                                    echo $loai_map[$hdv['loai_hdv'] ?? 'TongHop'];
                                    ?>
                                </span>
                                
                                <?php if (!empty($hdv['chuyen_tuyen'])): ?>
                                <p class="mb-1"><i class="bi bi-geo-alt"></i> <small><?php echo htmlspecialchars($hdv['chuyen_tuyen']); ?></small></p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="rating-stars">
                                        <?php 
                                        $rating = floatval($hdv['danh_gia_tb'] ?? 0);
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                        }
                                        ?>
                                        <small class="text-muted ms-1"><?php echo number_format($rating, 1); ?></small>
                                    </span>
                                    <span class="badge bg-secondary"><?php echo $hdv['so_tour_da_dan'] ?? 0; ?> tour</span>
                                </div>
                                
                                <p class="mb-2"><i class="bi bi-translate"></i> <?php echo htmlspecialchars($hdv['ngon_ngu'] ?? 'N/A'); ?></p>
                                
                                <div class="btn-group w-100" role="group">
                                    <a href="index.php?act=admin/hdv_detail&id=<?php echo $hdv['nhan_su_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> Chi tiết
                                    </a>
                                    <button class="btn btn-sm btn-outline-success" onclick="openScheduleModal(<?php echo $hdv['nhan_su_id']; ?>)">
                                        <i class="bi bi-calendar-plus"></i> Lịch
                                    </button>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewPerformance(<?php echo $hdv['nhan_su_id']; ?>)">
                                        <i class="bi bi-graph-up"></i> Hiệu suất
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">Chưa có HDV nào trong hệ thống.</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 2: Lịch làm việc -->
            <div class="tab-pane fade" id="tab-lich">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Lịch làm việc HDV</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                            <i class="bi bi-plus-circle"></i> Thêm lịch
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Bộ lọc -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="filterHDV" onchange="filterScheduleTable()">
                                    <option value="">Tất cả HDV</option>
                                    <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                                    <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                        <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                                    </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="filterTrangThai" onchange="filterScheduleTable()">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="SapKhoiHanh">Sắp khởi hành</option>
                                    <option value="DangChay">Đang chạy</option>
                                    <option value="HoanThanh">Hoàn thành</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bảng lịch làm việc -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="scheduleTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>HDV</th>
                                        <th>Tour</th>
                                        <th>Ngày khởi hành</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Điểm tập trung</th>
                                        <th>Trạng thái</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($lich_lam_viec)): foreach($lich_lam_viec as $lich): ?>
                                    <tr data-hdv="<?php echo $lich['nhan_su_id']; ?>" data-status="<?php echo $lich['trang_thai']; ?>">
                                        <td><?php echo htmlspecialchars($lich['ho_ten'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($lich['ten_tour'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($lich['ngay_ket_thuc'])); ?></td>
                                        <td><?php echo htmlspecialchars($lich['diem_tap_trung'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'SapKhoiHanh' => 'bg-info',
                                                'DangChay' => 'bg-warning',
                                                'HoanThanh' => 'bg-success'
                                            ];
                                            $statusLabel = [
                                                'SapKhoiHanh' => 'Sắp khởi hành',
                                                'DangChay' => 'Đang chạy',
                                                'HoanThanh' => 'Hoàn thành'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $statusClass[$lich['trang_thai']] ?? 'bg-secondary'; ?>">
                                                <?php echo $statusLabel[$lich['trang_thai']] ?? $lich['trang_thai']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($lich['ghi_chu'] ?? '-'); ?></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Chưa có lịch làm việc nào</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Hiệu suất -->
            <div class="tab-pane fade" id="tab-hieu-suat">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Bảng xếp hạng HDV</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Họ tên</th>
                                        <th>Loại HDV</th>
                                        <th>Số tour</th>
                                        <th>Đánh giá TB</th>
                                        <th>Tour hoàn thành</th>
                                        <th>Tour gần nhất</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($hieu_suat_list)): $rank = 1; foreach($hieu_suat_list as $hs): ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($hs['ho_ten'] ?? 'N/A'); ?></strong></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php 
                                                $loai_map = [
                                                    'NoiDia' => 'Nội địa',
                                                    'QuocTe' => 'Quốc tế',
                                                    'ChuyenTuyen' => 'Chuyên tuyến',
                                                    'ChuyenDoan' => 'Chuyên đoàn',
                                                    'TongHop' => 'Tổng hợp'
                                                ];
                                                echo $loai_map[$hs['loai_hdv'] ?? 'TongHop'];
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo $hs['tong_tour'] ?? 0; ?></td>
                                        <td>
                                            <span class="rating-stars">
                                                <?php 
                                                $rating = floatval($hs['diem_tb'] ?? 0);
                                                echo number_format($rating, 1);
                                                ?> <i class="bi bi-star-fill"></i>
                                            </span>
                                        </td>
                                        <td><?php echo $hs['tour_hoan_thanh'] ?? 0; ?></td>
                                        <td>
                                            <?php 
                                            if (!empty($hs['tour_gan_nhat'])) {
                                                $date = new DateTime($hs['tour_gan_nhat']);
                                                echo $date->format('d/m/Y');
                                            } else {
                                                echo '<em class="text-muted">Chưa có</em>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="index.php?act=admin/hdv_detail&id=<?php echo $hs['nhan_su_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Chưa có dữ liệu hiệu suất</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Thông báo -->
            <div class="tab-pane fade" id="tab-thong-bao">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Lịch sử thông báo</h5>
                        <div class="list-group">
                            <?php if (!empty($thong_bao_list)): foreach($thong_bao_list as $tb): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($tb['tieu_de']); ?>
                                            <?php if ($tb['uu_tien'] === 'Cao' || $tb['uu_tien'] === 'KhanCap'): ?>
                                                <span class="badge bg-danger">Quan trọng</span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="mb-1"><?php echo htmlspecialchars($tb['noi_dung']); ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> 
                                            <?php 
                                            $date = new DateTime($tb['ngay_gui']);
                                            echo $date->format('d/m/Y H:i');
                                            ?>
                                            <?php if (!empty($tb['ho_ten'])): ?>
                                                | <i class="bi bi-person"></i> <?php echo htmlspecialchars($tb['ho_ten']); ?>
                                            <?php else: ?>
                                                | <i class="bi bi-people"></i> Tất cả HDV
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <span class="badge <?php echo $tb['da_xem'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $tb['da_xem'] ? 'Đã xem' : 'Chưa xem'; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; else: ?>
                            <div class="list-group-item text-center text-muted">
                                Chưa có thông báo nào
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Phân công HDV cho tour -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Phân công HDV cho Tour</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="index.php?act=admin/hdv_add_schedule">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tour *</label>
                            <select name="tour_id" class="form-select" required>
                                <option value="">-- Chọn Tour --</option>
                                <?php 
                                // Get list of tours
                                require_once './models/Tour.php';
                                $tourModel = new Tour();
                                $tours = $tourModel->getAll();
                                foreach($tours as $tour): 
                                ?>
                                <option value="<?php echo $tour['tour_id'] ?? $tour['id']; ?>">
                                    <?php echo htmlspecialchars($tour['ten_tour']); ?> 
                                    (<?php echo $tour['diem_khoi_hanh']; ?> - <?php echo $tour['diem_den']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">HDV *</label>
                            <select name="hdv_id" class="form-select" required>
                                <option value="">-- Chọn HDV --</option>
                                <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                                <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                    <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                                    <?php if (!empty($hdv['loai_hdv'])): ?>
                                        <small>(<?php echo $hdv['loai_hdv']; ?>)</small>
                                    <?php endif; ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ngày khởi hành *</label>
                            <input type="date" name="ngay_khoi_hanh" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ngày kết thúc *</label>
                            <input type="date" name="ngay_ket_thuc" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Điểm tập trung</label>
                            <input type="text" name="diem_tap_trung" class="form-control" placeholder="Ví dụ: Bến xe Miền Đông">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái *</label>
                            <select name="trang_thai" class="form-select" required>
                                <option value="DaXacNhan">Đã xác nhận</option>
                                <option value="ChoXacNhan">Chờ xác nhận</option>
                                <option value="Huy">Hủy</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calendar-check"></i> Phân công
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Gửi thông báo -->
    <div class="modal fade" id="addNotificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gửi thông báo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="index.php?act=admin/hdv_send_notification">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Gửi đến</label>
                            <select name="nhan_su_id" class="form-select">
                                <option value="">Tất cả HDV</option>
                                <?php if (!empty($hdv_list)): foreach($hdv_list as $hdv): ?>
                                <option value="<?php echo $hdv['nhan_su_id']; ?>">
                                    <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Loại thông báo *</label>
                            <select name="loai_thong_bao" class="form-select" required>
                                <option value="ThongBao">Thông báo</option>
                                <option value="NhacNho">Nhắc nhở</option>
                                <option value="CanhBao">Cảnh báo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mức ưu tiên *</label>
                            <select name="uu_tien" class="form-select" required>
                                <option value="TrungBinh">Trung bình</option>
                                <option value="Cao">Cao</option>
                                <option value="KhanCap">Khẩn cấp</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề *</label>
                            <input type="text" name="tieu_de" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nội dung *</label>
                            <textarea name="noi_dung" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-success">Gửi thông báo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    <script>
        // Filter HDV
        document.getElementById('filterLoaiHDV').addEventListener('change', filterHDV);
        document.getElementById('filterTrangThai').addEventListener('change', filterHDV);
        document.getElementById('searchHDV').addEventListener('input', filterHDV);

        function filterHDV() {
            const loai = document.getElementById('filterLoaiHDV').value;
            const trangThai = document.getElementById('filterTrangThai').value;
            const search = document.getElementById('searchHDV').value.toLowerCase();

            document.querySelectorAll('.hdv-item').forEach(item => {
                const itemLoai = item.dataset.loai;
                const itemTrangThai = item.dataset.trangthai;
                const itemText = item.textContent.toLowerCase();

                const matchLoai = !loai || itemLoai === loai;
                const matchTrangThai = !trangThai || itemTrangThai === trangThai;
                const matchSearch = !search || itemText.includes(search);

                item.style.display = (matchLoai && matchTrangThai && matchSearch) ? '' : 'none';
            });
        }

        // Calendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'vi',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    buttonText: {
                        today: 'Hôm nay',
                        month: 'Tháng',
                        week: 'Tuần',
                        list: 'Danh sách'
                    },
                    events: function(info, successCallback, failureCallback) {
                        const hdvFilter = document.getElementById('calendarHDVFilter').value;
                        fetch(`index.php?act=admin/hdv_get_schedule${hdvFilter ? '&hdv_id=' + hdvFilter : ''}`)
                            .then(response => response.json())
                            .then(data => successCallback(data))
                            .catch(error => failureCallback(error));
                    },
                    eventClick: function(info) {
                        alert('Sự kiện: ' + info.event.title + '\n' + 
                              'Bắt đầu: ' + info.event.start + '\n' +
                              'Kết thúc: ' + info.event.end);
                    }
                });
                calendar.render();

                // Reload calendar when filter changes
                document.getElementById('calendarHDVFilter').addEventListener('change', function() {
                    calendar.refetchEvents();
                });
            }
        });

        function openScheduleModal(hdvId) {
            const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
            document.querySelector('#addScheduleModal select[name="nhan_su_id"]').value = hdvId;
            modal.show();
        }

        function viewPerformance(hdvId) {
            window.location.href = `index.php?act=admin/hdv_detail&id=${hdvId}#performance`;
        }

        // Filter schedule table
        function filterScheduleTable() {
            const hdvFilter = document.getElementById('filterHDV').value;
            const statusFilter = document.getElementById('filterTrangThai').value;
            
            document.querySelectorAll('#scheduleTable tbody tr').forEach(row => {
                const hdvId = row.dataset.hdv;
                const status = row.dataset.status;
                
                let show = true;
                if (hdvFilter && hdvId !== hdvFilter) show = false;
                if (statusFilter && status !== statusFilter) show = false;
                
                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
