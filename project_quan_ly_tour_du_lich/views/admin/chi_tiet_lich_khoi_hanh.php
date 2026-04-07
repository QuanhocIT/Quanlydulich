<?php
$pageTitle = 'Chi tiết Lịch Khởi Hành';
$currentPage = 'lich_khoi_hanh';
ob_start();
?>
<style>
        .page-header-section {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            padding: 40px;
            margin-bottom: 40px;
            backdrop-filter: blur(10px);
        }
        .info-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        .info-card .card-header {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            font-weight: 600;
            border: none;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .info-row {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        .info-value {
            color: var(--text-light);
            font-size: 1rem;
        }
        .section-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        .section-header {
            background: rgba(45, 45, 45, 0.7);
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 600;
            color: var(--text-light);
        }
        .add-form-card {
            background: rgba(45, 45, 45, 0.3);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .table-custom {
            margin-bottom: 0;
            width: 100%;
            color: var(--text-light);
        }
        .table-custom thead {
            background: rgba(45, 45, 45, 0.7);
        }
        .table-custom thead th {
            color: var(--text-light);
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table-custom tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .table-custom tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .table-custom tbody td {
            padding: 15px;
            color: var(--text-light);
        }
        .badge-role {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .stats-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 1.25rem;
        }
        .service-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .request-note {
            max-width: 320px;
            white-space: pre-line;
        }
        .diary-entry {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent-gold);
            backdrop-filter: blur(10px);
        }
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .entry-type-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .type-hanh_trinh { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        .type-su_co { background: rgba(220, 53, 69, 0.3); color: #dc3545; }
        .type-phan_hoi { background: rgba(108, 117, 125, 0.3); color: #adb5bd; }
        .type-hoat_dong { background: rgba(40, 167, 69, 0.3); color: #5cb85c; }
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .image-gallery img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .image-gallery img:hover {
            transform: scale(1.05);
        }
        
        /* Form elements */
        .form-control, .form-select {
            background: rgba(30, 30, 30, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            padding: 10px 15px;
            border-radius: 4px;
            width: 100%;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(30, 30, 30, 0.9);
            border-color: var(--accent-gold);
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
        }
        .form-label {
            color: var(--text-light);
            margin-bottom: 8px;
            display: block;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: var(--accent-gold);
            color: #000;
        }
        .btn-primary:hover {
            background: #ffd700;
            transform: translateY(-2px);
        }
        .btn-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-success:hover {
            background: rgba(40, 167, 69, 0.5);
        }
        .btn-danger, .btn-outline-danger {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        .btn-danger:hover, .btn-outline-danger:hover {
            background: rgba(220, 53, 69, 0.5);
        }
        .btn-info, .btn-outline-info {
            background: rgba(0, 123, 255, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(0, 123, 255, 0.5);
        }
        .btn-info:hover, .btn-outline-info:hover {
            background: rgba(0, 123, 255, 0.5);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .bg-primary { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        .bg-success { background: rgba(40, 167, 69, 0.3); color: #5cb85c; }
        .bg-danger { background: rgba(220, 53, 69, 0.3); color: #dc3545; }
        .bg-warning { background: rgba(255, 193, 7, 0.3); color: #ffc107; }
        .bg-info { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        .bg-secondary { background: rgba(108, 117, 125, 0.3); color: #adb5bd; }
        .bg-light { background: rgba(255, 255, 255, 0.1); color: var(--text-light); }
        .text-dark { color: var(--text-light) !important; }
        .text-danger { color: #dc3545 !important; }
        .text-warning { color: #ffc107 !important; }
        .text-muted { color: var(--text-muted) !important; }
        
        /* Progress bar */
        .progress {
            background: rgba(30, 30, 30, 0.7);
            border-radius: 4px;
            height: 25px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: 600;
        }
        
        /* Card body */
        .card-body {
            padding: 20px;
            color: var(--text-light);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            div[style*="grid-template-columns: 1fr 2fr"] {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
<?php
$serviceTypeOptions = [
    'Xe' => 'Xe vận chuyển',
    'KhachSan' => 'Khách sạn',
    'VeMayBay' => 'Vé máy bay',
    'Ve' => 'Vé tàu / xe khách',
    'NhaHang' => 'Nhà hàng',
    'DiemThamQuan' => 'Điểm tham quan',
    'Visa' => 'Visa',
    'BaoHiem' => 'Bảo hiểm',
    'Khac' => 'Khác'
];
$catalogServicesMap = $catalogServicesMap ?? [];
?>

<div style="padding: 20px;">
    <!-- Page Header -->
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <span style="background: rgba(255, 255, 255, 0.1); padding: 5px 12px; border-radius: 4px; font-size: 0.9rem;">#<?php echo $lichKhoiHanh['id']; ?></span>
                <span style="padding: 5px 12px; border-radius: 4px; font-size: 0.9rem; <?php 
                    echo match($lichKhoiHanh['trang_thai']) {
                        'SapKhoiHanh' => 'background: rgba(0, 123, 255, 0.3); color: #4da3ff;',
                        'DangChay' => 'background: rgba(40, 167, 69, 0.3); color: #5cb85c;',
                        'HoanThanh' => 'background: rgba(108, 117, 125, 0.3); color: #adb5bd;',
                        default => 'background: rgba(108, 117, 125, 0.3); color: #adb5bd;'
                    };
                ?>">
                <?php
                $statusLabels = [
                    'SapKhoiHanh' => 'Sắp khởi hành',
                    'DangChay' => 'Đang chạy',
                    'HoanThanh' => 'Hoàn thành'
                ];
                echo $statusLabels[$lichKhoiHanh['trang_thai']] ?? $lichKhoiHanh['trang_thai'];
                ?>
                </span>
            </div>
            <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-calendar-event"></i> Chi tiết Lịch Khởi Hành
            </h1>
            <p style="margin: 0; opacity: 0.8; color: var(--text-light);">
                <?php echo htmlspecialchars($lichKhoiHanh['ten_tour'] ?? 'N/A'); ?>
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="index.php?act=lichKhoiHanh/edit&id=<?php echo $lichKhoiHanh['id']; ?>" 
               style="background: var(--accent-gold); color: #000; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-pencil-square"></i> Sửa lịch
            </a>
            <?php if (!empty($fromTourDetail) && !empty($tour)): ?>
                <a href="index.php?act=admin/chiTietTour&id=<?php echo $tour['tour_id']; ?>" style="background: rgba(255, 255, 255, 0.1); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <i class="bi bi-arrow-left"></i> Quay lại chi tiết tour
                </a>
            <?php else: ?>
                <a href="index.php?act=lichKhoiHanh/index" style="background: rgba(255, 255, 255, 0.1); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
                    <i class="bi bi-arrow-left"></i> Quay lại danh sách
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div style="background: rgba(40, 167, 69, 0.2); border: 1px solid rgba(40, 167, 69, 0.5); color: #5cb85c; padding: 15px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['warning'])): ?>
        <div style="background: rgba(255, 193, 7, 0.2); border: 1px solid rgba(255, 193, 7, 0.5); color: #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['warning']; unset($_SESSION['warning']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['info'])): ?>
        <div style="background: rgba(0, 123, 255, 0.2); border: 1px solid rgba(0, 123, 255, 0.5); color: #4da3ff; padding: 15px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($_SESSION['info']); unset($_SESSION['info']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>
        
        <!-- Cảnh báo khi thiếu nhân sự hoặc dịch vụ -->
        <?php
        $coCanhBao = false;
        $danhSachCanhBao = [];
        
        // Không hiển thị cảnh báo nếu tour đã hoàn thành
        if (isset($lichKhoiHanh['trang_thai']) && $lichKhoiHanh['trang_thai'] === 'HoanThanh') {
            $coCanhBao = false;
        } else {
            // Kiểm tra nhân sự
            if (empty($phanBoNhanSu) || count($phanBoNhanSu) === 0) {
                $coCanhBao = true;
                $danhSachCanhBao[] = '<a href="#staff" class="alert-link">Chưa có nhân sự (HDV) được phân bổ</a>';
            }
            
            // Kiểm tra dịch vụ
            if (empty($phanBoDichVu)) {
                $coCanhBao = true;
                $danhSachCanhBao[] = '<a href="#service" class="alert-link">Chưa có dịch vụ nào được phân bổ</a>';
            } elseif (!empty($dichVuThieu)) {
                $coCanhBao = true;
                $danhSachCanhBao[] = '<a href="#service" class="alert-link">Thiếu các dịch vụ cơ bản: ' . implode(', ', array_map(function($loai) use ($serviceTypeOptions) {
                    return $serviceTypeOptions[$loai] ?? $loai;
                }, $dichVuThieu)) . '</a>';
            }
            
            // Kiểm tra gần ngày khởi hành (trong 7 ngày)
            if (!empty($lichKhoiHanh['ngay_khoi_hanh'])) {
                $ngayKhoiHanh = new DateTime($lichKhoiHanh['ngay_khoi_hanh']);
                $ngayHienTai = new DateTime();
                $soNgayConLai = $ngayHienTai->diff($ngayKhoiHanh)->days;
                
                if ($soNgayConLai <= 7 && $soNgayConLai >= 0) {
                    $coCanhBao = true;
                    if ($soNgayConLai == 0) {
                        $danhSachCanhBao[] = '<strong class="text-danger">Tour khởi hành HÔM NAY!</strong>';
                    } else {
                        $danhSachCanhBao[] = '<strong class="text-warning">Tour khởi hành sau ' . $soNgayConLai . ' ngày nữa!</strong>';
                    }
                }
            }
        }
        ?>
    <?php if ($coCanhBao): ?>
        <div style="background: rgba(255, 193, 7, 0.2); border: 2px solid rgba(255, 193, 7, 0.5); color: #ffc107; padding: 20px; border-radius: 4px; margin-bottom: 20px; position: relative;">
            <h5 style="margin: 0 0 15px 0; color: #ffc107;">
                <i class="bi bi-exclamation-triangle-fill"></i> Cảnh báo chuẩn bị tour
            </h5>
            <ul style="margin: 0 0 15px 0; padding-left: 20px;">
                <?php foreach ($danhSachCanhBao as $canhBao): ?>
                    <li><?php echo $canhBao; ?></li>
                <?php endforeach; ?>
            </ul>
            <hr style="border-color: rgba(255, 193, 7, 0.3); margin: 15px 0;">
            <p style="margin: 0; font-size: 0.9rem;">
                Vui lòng kiểm tra và hoàn thiện phân bổ nhân sự và dịch vụ trước khi tour khởi hành.
            </p>
            <button type="button" onclick="this.parentElement.remove()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.5); color: #dc3545; padding: 15px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>
        
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 30px;">
        <!-- Left Column: Info -->
        <div>
                <!-- Quick Stats -->
                <div class="info-card card">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Thông tin cơ bản
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-calendar-event text-primary"></i> Ngày khởi hành</div>
                            <div class="info-value fw-bold">
                                <?php echo $lichKhoiHanh['ngay_khoi_hanh'] ? date('d/m/Y', strtotime($lichKhoiHanh['ngay_khoi_hanh'])) : 'N/A'; ?>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-clock text-info"></i> Giờ xuất phát</div>
                            <div class="info-value"><?php echo $lichKhoiHanh['gio_xuat_phat'] ?? 'N/A'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-calendar-check text-success"></i> Ngày kết thúc</div>
                            <div class="info-value">
                                <?php echo $lichKhoiHanh['ngay_ket_thuc'] ? date('d/m/Y', strtotime($lichKhoiHanh['ngay_ket_thuc'])) : 'N/A'; ?>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-clock text-warning"></i> Giờ kết thúc</div>
                            <div class="info-value"><?php echo $lichKhoiHanh['gio_ket_thuc'] ?? 'N/A'; ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-geo-alt text-danger"></i> Điểm tập trung</div>
                            <div class="info-value"><?php echo htmlspecialchars($lichKhoiHanh['diem_tap_trung'] ?? 'Chưa xác định'); ?></div>
                        </div>
                    </div>
                </div>

                <?php $canEditHoaHong = (($lichKhoiHanh['trang_thai'] ?? '') === 'SapKhoiHanh'); ?>
                <div class="info-card card">
                    <div class="card-header">
                        <i class="bi bi-percent"></i> Hoa hồng HDV
                    </div>
                    <div class="card-body">
                        <div class="info-row" style="align-items:flex-start;">
                            <div class="info-label">%</div>
                            <div class="info-value">
                                <form method="post" action="index.php?act=lichKhoiHanh/capNhatHoaHongHDV" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:0;">
                                    <input type="hidden" name="id" value="<?php echo (int)($lichKhoiHanh['id'] ?? 0); ?>">
                                    <input type="number" name="phan_tram_hoa_hong_hdv" min="0" max="100" step="0.01"
                                           value="<?php echo htmlspecialchars((string)($lichKhoiHanh['phan_tram_hoa_hong_hdv'] ?? 0)); ?>"
                                           <?php echo $canEditHoaHong ? '' : 'disabled'; ?>
                                           style="width:120px; padding:6px 8px; border-radius:4px; border:1px solid #444; background:#222; color:#fff;">
                                    <button type="submit" class="btn btn-warning btn-sm" <?php echo $canEditHoaHong ? '' : 'disabled'; ?>>
                                        <i class="bi bi-save"></i> Lưu
                                    </button>
                                    <?php if (!$canEditHoaHong): ?>
                                        <div style="color:#aaa; font-size:12px;">Chỉ chỉnh khi trạng thái: Sắp khởi hành</div>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Stats -->
                <div class="info-card card">
                    <div class="card-header">
                        <i class="bi bi-graph-up"></i> Thống kê
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-people"></i> Số chỗ</div>
                            <div class="info-value">
                                <span class="stats-badge bg-primary text-white">
                                    <?php echo $lichKhoiHanh['so_cho'] ?? 50; ?>
                                </span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-file-text"></i> Số booking</div>
                            <div class="info-value">
                                <span class="stats-badge bg-info text-white">
                                    <?php echo $lichKhoiHanh['so_booking'] ?? 0; ?>
                                </span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-person-check"></i> Tổng người đã đặt</div>
                            <div class="info-value">
                                <span class="stats-badge bg-success text-white">
                                    <?php echo $lichKhoiHanh['tong_nguoi_dat'] ?? 0; ?>
                                </span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="bi bi-percent"></i> Tỷ lệ lấp đầy</div>
                            <div class="info-value">
                                <?php 
                                $soCho = $lichKhoiHanh['so_cho'] ?? 50;
                                $tongNguoi = $lichKhoiHanh['tong_nguoi_dat'] ?? 0;
                                $tyLe = $soCho > 0 ? round(($tongNguoi / $soCho) * 100, 1) : 0;
                                ?>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar <?php echo $tyLe >= 80 ? 'bg-success' : ($tyLe >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $tyLe; ?>%"
                                         aria-valuenow="<?php echo $tyLe; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo $tyLe; ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Right Column: Tabs -->
        <div>
            <!-- Nav Tabs -->
            <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); flex-wrap: wrap;">
                <button class="tab-btn active" onclick="switchTab('staff')" style="background: rgba(45, 45, 45, 0.5); border: none; color: var(--text-light); padding: 12px 24px; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500; border-bottom: 2px solid var(--accent-gold);">
                    <i class="bi bi-people"></i> Nhân sự
                </button>
                <button class="tab-btn" onclick="switchTab('customer')" style="background: rgba(45, 45, 45, 0.3); border: none; color: var(--text-muted); padding: 12px 24px; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500;">
                    <i class="bi bi-person-lines-fill"></i> Danh sách khách
                </button>
                <button class="tab-btn" onclick="switchTab('service')" style="background: rgba(45, 45, 45, 0.3); border: none; color: var(--text-muted); padding: 12px 24px; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500;">
                    <i class="bi bi-gear"></i> Dịch vụ
                </button>
                <button class="tab-btn" onclick="switchTab('special-request')" style="background: rgba(45, 45, 45, 0.3); border: none; color: var(--text-muted); padding: 12px 24px; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500;">
                    <i class="bi bi-heart-pulse"></i> ! Yêu cầu đặc biệt
                </button>
                <button class="tab-btn" onclick="switchTab('diary')" style="background: rgba(45, 45, 45, 0.3); border: none; color: var(--text-muted); padding: 12px 24px; border-radius: 4px 4px 0 0; cursor: pointer; font-weight: 500;">
                    <i class="bi bi-journal-text"></i> Nhật ký tour
                </button>
            </div>

            <!-- Tab Content -->
            <div id="detailTabsContent">
                <!-- Tab: Nhân sự -->
                <div class="tab-pane active" id="staff" style="display: block;">
                        <!-- Add Staff Form -->
                        <div class="add-form-card">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-person-plus"></i> Thêm nhân sự mới
                            </h6>
                            <form method="POST" action="index.php?act=lichKhoiHanh/phanBoNhanSu">
                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Nhân sự <span class="text-danger">*</span></label>
                                        <select name="nhan_su_id" id="nhanSuSelect" class="form-select" required>
                                <option value="">-- Chọn nhân sự --</option>
                                <?php foreach ($nhanSuList as $ns): ?>
                                    <option value="<?php echo $ns['nhan_su_id']; ?>">
                                        <?php echo htmlspecialchars($ns['ho_ten'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($ns['vai_tro'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                                </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Vai trò <span class="text-danger">*</span></label>
                                            <select name="vai_tro" id="vaiTroSelect" class="form-select" required>
                                                <option value="HDV">Hướng dẫn viên</option>
                                    <option value="TaiXe">Tài xế</option>
                                    <option value="HauCan">Hậu cần</option>
                                    <option value="DieuHanh">Điều hành</option>
                                    <option value="Khac">Khác</option>
                                </select>
                                    </div>
                                    <div class="col-12">
                                        <div id="conflictWarning" class="alert alert-warning d-none" role="alert">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>CẢNH BÁO:</strong>
                                            <div id="conflictMessage"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Loại lương</label>
                                        <select name="loai_luong" class="form-select">
                                            <option value="CoDinh">Cố định</option>
                                            <option value="PhanTram">% Hoa hồng</option>
                                            <option value="KetHop">Kết hợp</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Số tiền cố định (VNĐ)</label>
                                        <input type="number" name="so_tien_co_dinh" class="form-control" min="0" step="1000" placeholder="0">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">% Hoa hồng</label>
                                        <input type="number" name="phan_tram_hoa_hong" class="form-control" min="0" max="100" step="0.1" placeholder="0">
                                        <div class="form-text">Với vai trò HDV, hệ thống sẽ ưu tiên % hoa hồng theo lịch khởi hành (panel “Hoa hồng HDV”).</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Ghi chú</label>
                                        <textarea name="ghi_chu" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Thêm nhân sự
                                        </button>
                                        <?php if (empty($phanBoNhanSu) || count($phanBoNhanSu) === 0): ?>
                                            <a href="index.php?act=lichKhoiHanh/tuDongPhanBoNhanSu&id=<?php echo $lichKhoiHanh['id']; ?>" 
                                               class="btn btn-success" 
                                               onclick="return confirm('Bạn có muốn hệ thống tự động phân bổ HDV rảnh cho lịch khởi hành này?');">
                                                <i class="bi bi-magic"></i> Tự động phân bổ HDV
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
            </form>
                        </div>

                        <!-- Staff List -->
                        <div class="section-card card">
                            <div class="section-header">
                                <i class="bi bi-people"></i> Danh sách nhân sự đã phân bổ
                            </div>
                            <div class="card-body p-0">
            <?php if (!empty($phanBoNhanSu)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Nhân sự</th>
                            <th>Vai trò</th>
                            <th>Liên hệ</th>
                            <th>Trạng thái</th>
                            <th>Xác nhận lúc</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($phanBoNhanSu as $pb): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($pb['ho_ten'] ?? 'N/A'); ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-role bg-primary">
                                    <?php 
                                        $roles = [
                                                    'HDV' => 'Hướng dẫn viên',
                                                    'TaiXe' => 'Tài xế',
                                                    'HauCan' => 'Hậu cần',
                                                    'DieuHanh' => 'Điều hành',
                                                    'Khac' => 'Khác'
                                                ];
                                            echo $roles[$pb['vai_tro']] ?? $pb['vai_tro'];
                                    ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($pb['email'] ?? 'N/A'); ?></div>
                                        <div><i class="bi bi-phone"></i> <?php echo htmlspecialchars($pb['so_dien_thoai'] ?? 'N/A'); ?></div>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo match($pb['trang_thai']) {
                                            'ChoXacNhan' => 'bg-warning text-dark',
                                            'DaXacNhan' => 'bg-success',
                                            'TuChoi' => 'bg-danger',
                                            'Huy' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                    ?>">
                                    <?php
                                    $statusLabels = [
                                        'ChoXacNhan' => 'Chờ xác nhận',
                                        'DaXacNhan' => 'Đã xác nhận',
                                        'TuChoi' => 'Từ chối',
                                        'Huy' => 'Hủy'
                                    ];
                                    echo $statusLabels[$pb['trang_thai']] ?? $pb['trang_thai'];
                                    ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small><?php echo $pb['thoi_gian_xac_nhan'] ? date('d/m/Y H:i', strtotime($pb['thoi_gian_xac_nhan'])) : 'N/A'; ?></small>
                                </td>
                                <td>
                                    <form method="POST" action="index.php?act=lichKhoiHanh/deleteNhanSu" onsubmit="return confirm('Xóa phân bổ này?');" class="d-inline">
                                        <?php echo csrfField('lich_khoi_hanh_form'); ?>
                                        <input type="hidden" name="id" value="<?php echo $pb['id']; ?>">
                                        <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                                    </div>
            <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 opacity-25"></i>
                                        <p class="mt-3">Chưa có nhân sự nào được phân bổ</p>
                                    </div>
            <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <!-- Tab: Danh sách khách -->
                <div class="tab-pane" id="customer" style="display: none;">
                        <div class="add-form-card">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-info-circle"></i> Nguồn dữ liệu danh sách khách
                            </h6>
                            <div class="small text-muted">
                                Danh sách dưới đây được lấy tự động từ thông tin người tham gia do khách hàng khai báo sau khi đặt tour và thanh toán.
                                Admin không cần nhập thủ công tại màn này.
                            </div>
                        </div>

                        <!-- Customer List -->
                        <div class="section-card card">
                            <div class="section-header">
                                <i class="bi bi-person-lines-fill"></i> Danh sách khách chi tiết
                            </div>
                            <div class="card-body p-0">
                                <?php 
                                // Đảm bảo biến được khởi tạo (danh sách khách trong đoàn từ tour_checkin)
                                // Nếu biến không tồn tại, lấy lại từ model
                                // Dá»¯ liá»‡u checkin Ä‘Æ°á»£c controller chuáº©n bá»‹ sáºµn Ä‘á»ƒ trÃ¡nh query trong view.
                                
                                // Debug: Kiểm tra dữ liệu (đã tắt)
                                // echo "<!-- DEBUG VIEW: allCheckinRows count: " . count($allCheckinRows ?? []) . " -->";
                                // echo "<!-- DEBUG VIEW: lich_khoi_hanh_id: " . ($lichKhoiHanh['id'] ?? $id ?? 'N/A') . " -->";
                                
                                // Hiển thị toàn bộ khách từ tour_checkin trong một bảng
                                if (!empty($allCheckinRows)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-custom">
                                            <thead>
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Khách hàng</th>
                                                    <th>Giấy tờ</th>
                                                    <th>Liên hệ</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($allCheckinRows as $idx => $khach): ?>
                                                    <tr>
                                                        <td><?php echo $idx + 1; ?></td>
                                                        <td>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($khach['ho_ten'] ?? 'N/A'); ?></div>
                                                            <small class="text-muted">
                                                                Booking:
                                                                <?php if ($khach['booking_id']): ?>
                                                                    <span class="badge bg-primary">#<?php echo $khach['booking_id']; ?></span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Không có</span>
                                                                <?php endif; ?>
                                                                | <?php echo htmlspecialchars($khach['gioi_tinh'] ?? 'N/A'); ?>
                                                                | <?php echo htmlspecialchars($khach['quoc_tich'] ?? 'N/A'); ?>
                                                            </small>
                                                            <div><small class="text-muted">Ngày sinh: <?php echo $khach['ngay_sinh'] ? date('d/m/Y', strtotime($khach['ngay_sinh'])) : 'N/A'; ?></small></div>
                                                        </td>
                                                        <td>
                                                            <?php if ($khach['so_cmnd']): ?>
                                                                <small>CMND: <?php echo htmlspecialchars($khach['so_cmnd']); ?></small><br>
                                                            <?php endif; ?>
                                                            <?php if ($khach['so_passport']): ?>
                                                                <small>Passport: <?php echo htmlspecialchars($khach['so_passport']); ?></small>
                                                            <?php endif; ?>
                                                            <?php if (!$khach['so_cmnd'] && !$khach['so_passport']): ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($khach['so_dien_thoai'] ?? 'N/A'); ?></div>
                                                            <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($khach['email'] ?? 'N/A'); ?></div>
                                                            <div><small class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($khach['dia_chi'] ?? 'N/A'); ?></small></div>
                                                        </td>
                                                        <td>
                                                            <span class="badge <?php 
                                                                echo $khach['trang_thai'] === 'DaCheckIn' ? 'bg-success' : 
                                                                    ($khach['trang_thai'] === 'DaCheckOut' ? 'bg-secondary' : 'bg-warning');
                                                            ?>">
                                                                <?php 
                                                                echo $khach['trang_thai'] === 'DaCheckIn' ? 'Đã check-in' : 
                                                                    ($khach['trang_thai'] === 'DaCheckOut' ? 'Đã check-out' : 'Chưa check-in');
                                                                ?>
                                                            </span>
                                                            <div><small class="text-muted">In: <?php echo $khach['checkin_time'] ? date('d/m H:i', strtotime($khach['checkin_time'])) : '-'; ?></small></div>
                                                            <div><small class="text-muted">Out: <?php echo $khach['checkout_time'] ? date('d/m H:i', strtotime($khach['checkout_time'])) : '-'; ?></small></div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="p-3 text-center text-muted">
                                        <p class="mb-0">Chưa có khách nào trong danh sách check-in.</p>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    </div>

                                        <!-- Tab: Yêu cầu đặc biệt -->
                    <div class="tab-pane fade" id="request" role="tabpanel">
                        <!-- Add Request Form -->
                        <div class="add-form-card">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-plus-circle"></i> Thêm yêu cầu đặc biệt
                            </h6>
                            <form method="POST" action="index.php?act=lichKhoiHanh/themYeuCauDacBiet">
                                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Khách hàng <span class="text-danger">*</span></label>
                                        <select name="booking_id" class="form-select" required>
                                            <option value="">-- Chọn khách hàng --</option>
                                            <?php foreach ($bookingList as $b): ?>
                                                <option value="<?php echo $b['booking_id']; ?>">
                                                    <?php echo htmlspecialchars($b['ho_ten'] ?? 'N/A'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Loại yêu cầu <span class="text-danger">*</span></label>
                                        <select name="loai_yeu_cau" class="form-select" required>
                                            <option value="ThucPham">Thực phẩm / Dị ứng</option>
                                            <option value="YTe">Y tế / Sức khỏe</option>
                                            <option value="DichVu">Yêu cầu dịch vụ</option>
                                            <option value="NguNgu">Ngủ ngơi / Chỗ ở</option>
                                            <option value="AnToan">An toàn / Sự cố</option>
                                            <option value="Khac">Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Mức độ ưu tiên <span class="text-danger">*</span></label>
                                        <select name="muc_do_uu_tien" class="form-select" required>
                                            <option value="Thap">Thấp</option>
                                            <option value="Trung">Trung bình</option>
                                            <option value="Cao">Cao</option>
                                            <option value="RatCao">Rất cao</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Trạng thái</label>
                                        <select name="trang_thai" class="form-select">
                                            <option value="Moi">Mới</option>
                                            <option value="DangXuLy">Đang xử lý</option>
                                            <option value="HoanTat">Hoàn tất</option>
                                            <option value="KhongTheXuLy">Không thể xử lý</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Nội dung yêu cầu <span class="text-danger">*</span></label>
                                        <textarea name="noi_dung" class="form-control" rows="3" required></textarea>
                                    </div>
                                  
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Thêm yêu cầu
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Request List -->
                        <div class="section-card card">
                            <div class="section-header">
                                <i class="bi bi-exclamation-lg"></i> Danh sách yêu cầu đặc biệt
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($yeuCauDacBietList)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-custom">
                                            <thead>
                                                <tr>
                                                    <th>Khách hàng</th>
                                                    <th>Loại yêu cầu</th>
                                                    <th>Nội dung</th>
                                                    <th>Ưu tiên</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ngày tạo</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>

<tbody>
    <?php foreach ($yeuCauDacBietList as $yc): ?>
        <tr>
            <td><?php echo htmlspecialchars($yc['khach_ten'] ?? 'N/A'); ?></td>
            <td>
                <span class="badge bg-info">
                    <?php
                    $loaiMap = [
                        'an_uong' => 'Thực phẩm / Dị ứng',
                        'suc_khoe' => 'Y tế / Sức khỏe',
                        'phong_o' => 'Phòng ở',
                        'an_toan' => 'An toàn',
                        'khac' => 'Khác'
                    ];
                    echo $loaiMap[$yc['loai_yeu_cau']] ?? $yc['loai_yeu_cau'];
                    ?>
                </span>
            </td>
            <td><?php $nd = (string)($yc['mo_ta'] ?? ''); echo htmlspecialchars(mb_strlen($nd) > 50 ? mb_substr($nd, 0, 50) . '...' : $nd); ?></td>
            <td>
                <span class="badge <?php
                    echo match($yc['muc_do_uu_tien']) {
                        'khan_cap' => 'bg-danger',
                        'cao' => 'bg-warning text-dark',
                        'trung_binh' => 'bg-info',
                        'thap' => 'bg-success',
                        default => 'bg-secondary'
                    };
                ?>">
                    <?php
                    $ucTienMap = [
                        'khan_cap' => 'Rất cao',
                        'cao' => 'Cao',
                        'trung_binh' => 'Trung bình',
                        'thap' => 'Thấp'
                    ];
                    echo $ucTienMap[$yc['muc_do_uu_tien']] ?? $yc['muc_do_uu_tien'];
                    ?>
                </span>
            </td>
            <td>
                <span class="badge <?php
                    echo match($yc['trang_thai']) {
                        'da_giai_quyet' => 'bg-success',
                        'dang_xu_ly' => 'bg-warning text-dark',
                        'khong_the_thuc_hien' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                ?>">
                    <?php
                    $tthaiMap = [
                        'moi' => 'Mới',
                        'dang_xu_ly' => 'Đang xử lý',
                        'da_giai_quyet' => 'Hoàn tất',
                        'khong_the_thuc_hien' => 'Không xử lý'
                    ];
                    echo $tthaiMap[$yc['trang_thai']] ?? $yc['trang_thai'];
                    ?>
                </span>
            </td>
            <td><small><?php echo $yc['ngay_tao'] ? date('d/m/Y H:i', strtotime($yc['ngay_tao'])) : 'N/A'; ?></small></td>
            <td>
                <button type="button" onclick="openModal('editYeuCauModal<?php echo $yc['yeu_cau_id']; ?>')" style="background: rgba(0, 123, 255, 0.3); border: 1px solid rgba(0, 123, 255, 0.5); color: #4da3ff; padding: 6px 12px; border-radius: 4px; cursor: pointer;">
                    <i class="bi bi-pencil"></i>
                </button>
                <form method="POST" action="index.php?act=lichKhoiHanh/xoaYeuCauDacBiet" onsubmit="return confirm('Xóa yêu cầu này?');" class="d-inline">
                    <?php echo csrfField('lich_khoi_hanh_form'); ?>
                    <input type="hidden" name="id" value="<?php echo $yc['yeu_cau_id']; ?>">
                    <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-exclamation-circle fs-1 opacity-25"></i>
                                        <p class="mt-3">Chưa có yêu cầu đặc biệt nào</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Nhật ký tour -->
                    <div class="tab-pane fade" id="log" role="tabpanel">
                        <!-- Add Log Entry Form -->
                        <div class="add-form-card">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-plus-circle"></i> Thêm ghi chép nhật ký
                            </h6>
                            <form method="POST" action="index.php?act=lichKhoiHanh/themNhatKy">
                                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Loại sự kiện <span class="text-danger">*</span></label>
                                        <select name="loai_su_kien" class="form-select" required>
                                            <option value="DuocLichTrinhDuaKhach">Được lịch trình đưa khách</option>
                                            <option value="DuaKhachDenDiem">Đưa khách đến điểm</option>
                                            <option value="CoDoanLuu">Có đoàn lưu</option>
                                            <option value="DoanVeTour">Đoàn về tour</option>
                                            <option value="SuCo">Sự cố</option>
                                            <option value="YLenCap">Yêu lên cấp</option>
                                            <option value="Khac">Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Thời gian <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="thoi_gian_su_kien" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Người ghi chép</label>
                                        <input type="text" name="nguoi_ghi_chep" class="form-control" value="<?php echo htmlspecialchars($_SESSION['ho_ten'] ?? 'Admin'); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Địa điểm</label>
                                        <input type="text" name="dia_diem" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Nội dung <span class="text-danger">*</span></label>
                                        <textarea name="noi_dung" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Thêm ghi chép
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Log List -->
                        <div class="section-card card">
                            <div class="section-header">
                                <i class="bi bi-clock-history"></i> Nhật ký sự kiện tour
                            </div>
                            <div class="card-body">
                                <?php if (!empty($nhatKyTourList)): ?>
                                    <div class="timeline">
                                        <?php foreach ($nhatKyTourList as $log): ?>
                                            <div class="d-flex mb-4 pb-4 border-bottom">
                                                <div class="me-3">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        <i class="bi bi-<?php 
                                                            echo match($log['loai_su_kien'] ?? $log['loai_nhat_ky'] ?? '') {
                                                                'DuocLichTrinhDuaKhach' => 'calendar-check',
                                                                'DuaKhachDenDiem' => 'geo-alt',
                                                                'CoDoanLuu' => 'stop',
                                                                'DoanVeTour' => 'house',
                                                                'SuCo' => 'exclamation-triangle',
                                                                'YLenCap' => 'arrow-up-circle',
                                                                default => 'info-circle'
                                                            };
                                                        ?>"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-1">
                                                        <?php
                                                        $loaiMap = [
                                                            'DuocLichTrinhDuaKhach' => 'Được lịch trình đưa khách',
                                                            'DuaKhachDenDiem' => 'Đưa khách đến điểm',
                                                            'CoDoanLuu' => 'Có đoàn lưu',
                                                            'DoanVeTour' => 'Đoàn về tour',
                                                            'SuCo' => 'Sự cố',
                                                            'YLenCap' => 'Yêu lên cấp',
                                                            'Khac' => 'Khác'
                                                        ];
                                                        echo $loaiMap[$log['loai_su_kien']] ?? $log['loai_su_kien'];
                                                        ?>
                                                    </h6>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($log['noi_dung']); ?></p>
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-calendar"></i> <?php echo !empty($log['thoi_gian_su_kien'] ?? null) ? date('d/m/Y H:i', strtotime($log['thoi_gian_su_kien'])) : 'N/A'; ?>
                                                        <?php if (!empty($log['dia_diem'] ?? null)): ?>
                                                            | <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($log['dia_diem']); ?>
                                                        <?php endif; ?>
                                                        | <i class="bi bi-person"></i> <?php echo htmlspecialchars($log['nguoi_ghi_chep'] ?? 'N/A'); ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <button type="button" onclick="openModal('editNhatKyModal<?php echo $log['id']; ?>')" style="background: rgba(0, 123, 255, 0.3); border: 1px solid rgba(0, 123, 255, 0.5); color: #4da3ff; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px;">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form method="POST" action="index.php?act=lichKhoiHanh/xoaNhatKy" onsubmit="return confirm('Xóa ghi chép này?');" class="d-inline">
                                                        <?php echo csrfField('lich_khoi_hanh_form'); ?>
                                                        <input type="hidden" name="id" value="<?php echo $log['id']; ?>">
                                                        <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-clock-history fs-1 opacity-25"></i>
                                        <p class="mt-3">Chưa có ghi chép nhật ký nào</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Modal sửa nhật ký tour -->
                        <?php foreach ($nhatKyTourList as $log): ?>
                        <div class="modal-overlay" id="editNhatKyModal<?php echo $log['id']; ?>" onclick="closeModal('editNhatKyModal<?php echo $log['id']; ?>')" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; backdrop-filter: blur(5px);">
                            <div class="modal-content" onclick="event.stopPropagation()" style="background: rgba(30, 30, 30, 0.95); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0; max-width: 600px; margin: 50px auto; max-height: 90vh; overflow-y: auto;">
                                <div style="padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;">
                                    <h5 style="margin: 0; color: var(--text-light);">Sửa nhật ký tour</h5>
                                    <button type="button" onclick="closeModal('editNhatKyModal<?php echo $log['id']; ?>')" style="background: none; border: none; color: var(--text-light); font-size: 1.5rem; cursor: pointer;">&times;</button>
                                </div>
                                <form method="POST" action="index.php?act=lichKhoiHanh/suaNhatKy">
                                    <input type="hidden" name="id" value="<?php echo $log['id']; ?>">
                                    <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                    <div style="padding: 20px;">
                                            <?php
                                            // Parse loai_su_kien và noi_dung từ nội dung lưu
                                            $noiDung = $log['noi_dung'] ?? '';
                                            $loaiSuKien = '';
                                            if (preg_match('/^([^-]+)\s*-\s*(.+)$/', $noiDung, $matches)) {
                                                $loaiSuKien = trim($matches[1]);
                                                $noiDung = trim($matches[2]);
                                            }
                                            ?>
                                            <div class="mb-3">
                                                <label class="form-label">Loại sự kiện <span class="text-danger">*</span></label>
                                                <select name="loai_su_kien" class="form-select" required>
                                                    <option value="DuocLichTrinhDuaKhach" <?php echo $loaiSuKien == 'DuocLichTrinhDuaKhach' ? 'selected' : ''; ?>>Được lịch trình đưa khách</option>
                                                    <option value="DuaKhachDenDiem" <?php echo $loaiSuKien == 'DuaKhachDenDiem' ? 'selected' : ''; ?>>Đưa khách đến điểm</option>
                                                    <option value="CoDoanLuu" <?php echo $loaiSuKien == 'CoDoanLuu' ? 'selected' : ''; ?>>Có đoàn lưu</option>
                                                    <option value="DoanVeTour" <?php echo $loaiSuKien == 'DoanVeTour' ? 'selected' : ''; ?>>Đoàn về tour</option>
                                                    <option value="SuCo" <?php echo $loaiSuKien == 'SuCo' ? 'selected' : ''; ?>>Sự cố</option>
                                                    <option value="YLenCap" <?php echo $loaiSuKien == 'YLenCap' ? 'selected' : ''; ?>>Yêu lên cấp</option>
                                                    <option value="Khac" <?php echo empty($loaiSuKien) || $loaiSuKien == 'Khac' ? 'selected' : ''; ?>>Khác</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Thời gian <span class="text-danger">*</span></label>
                                                <input type="datetime-local" name="thoi_gian_su_kien" class="form-control" 
                                                       value="<?php echo $log['ngay_ghi'] ? date('Y-m-d\TH:i', strtotime($log['ngay_ghi'])) : ''; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                                <textarea name="noi_dung" class="form-control" rows="3" required><?php echo htmlspecialchars($noiDung); ?></textarea>
                                            </div>
                                        </div>
                                    <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: flex-end; gap: 10px;">
                                        <button type="button" onclick="closeModal('editNhatKyModal<?php echo $log['id']; ?>')" style="background: rgba(108, 117, 125, 0.3); border: 1px solid rgba(108, 117, 125, 0.5); color: var(--text-light); padding: 10px 20px; border-radius: 4px; cursor: pointer;">Hủy</button>
                                        <button type="submit" style="background: var(--accent-gold); color: #000; padding: 10px 20px; border-radius: 4px; border: none; cursor: pointer; font-weight: 500;">Lưu thay đổi</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <!-- Tab: Dịch vụ -->
                <div class="tab-pane" id="service" style="display: none;">
                        <?php
                        // Kiểm tra dịch vụ đã phân bổ
                        $dichVuDaPhanBo = !empty($phanBoDichVu) ? array_column($phanBoDichVu, 'loai_dich_vu') : [];
                        $dichVuCanThiet = ['Xe', 'KhachSan', 'VeMayBay']; // Các dịch vụ cơ bản
                        $dichVuThieu = array_diff($dichVuCanThiet, $dichVuDaPhanBo);
                        ?>
                        
                        <!-- Cảnh báo khi chưa có dịch vụ -->
                        <?php if (empty($phanBoDichVu)): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>CẢNH BÁO:</strong> Chưa có dịch vụ nào được phân bổ cho lịch khởi hành này. 
                                Vui lòng phân bổ các dịch vụ cần thiết như: <strong>Xe vận chuyển, Khách sạn, Vé máy bay</strong>.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php elseif (!empty($dichVuThieu)): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle"></i>
                                <strong>Gợi ý:</strong> Bạn chưa phân bổ các dịch vụ cơ bản: 
                                <strong><?php echo implode(', ', array_map(function($loai) use ($serviceTypeOptions) {
                                    return $serviceTypeOptions[$loai] ?? $loai;
                                }, $dichVuThieu)); ?></strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Add Service Form -->
                        <div class="add-form-card">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-plus-circle"></i> Thêm dịch vụ mới
                            </h6>
                            <form method="POST" action="index.php?act=lichKhoiHanh/phanBoDichVu">
                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Loại dịch vụ <span class="text-danger">*</span></label>
                                        <select name="loai_dich_vu" id="loaiDichVuSelect" class="form-select" required>
                                <?php foreach ($serviceTypeOptions as $value => $label): ?>
                                    <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Tên dịch vụ <span class="text-danger">*</span></label>
                                        <input type="text" name="ten_dich_vu" id="tenDichVuInput" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Nhà cung cấp</label>
                                        <select name="nha_cung_cap_id" id="supplierSelect" class="form-select">
                                <option value="">-- Chọn nhà cung cấp --</option>
                                <?php foreach ($nhaCungCapList as $ncc): ?>
                                    <option value="<?php echo $ncc['id_nha_cung_cap']; ?>">
                                        <?php echo htmlspecialchars($ncc['ten_don_vi'] ?? 'N/A'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Dịch vụ có sẵn</label>
                                        <select id="catalogServiceSelect" class="form-select" disabled>
                                            <option value="">-- Chọn dịch vụ có sẵn --</option>
                                        </select>
                                        <div class="form-text">Tự động điền thông tin khi chọn.</div>
                                        <input type="hidden" name="catalog_service_id" id="catalogServiceIdInput">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" name="so_luong" id="soLuongInput" class="form-control" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Đơn vị</label>
                                        <input type="text" name="don_vi" id="donViInput" class="form-control" placeholder="VD: phòng, xe...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Ngày bắt đầu</label>
                                        <input type="date" name="ngay_bat_dau" id="ngayBatDauInput" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Ngày kết thúc</label>
                                        <input type="date" name="ngay_ket_thuc" id="ngayKetThucInput" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Giờ bắt đầu</label>
                                        <input type="time" name="gio_bat_dau" id="gioBatDauInput" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Giờ kết thúc</label>
                                        <input type="time" name="gio_ket_thuc" id="gioKetThucInput" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Địa điểm</label>
                                        <input type="text" name="dia_diem" id="diaDiemInput" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Giá tiền (VNĐ)</label>
                                        <input type="number" name="gia_tien" id="giaTienInput" class="form-control" step="1000" min="0">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Ghi chú</label>
                                        <textarea name="ghi_chu" id="ghiChuInput" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-plus-circle"></i> Thêm dịch vụ
                                        </button>
                                    </div>
                                </div>
            </form>
                        </div>

                        <!-- Service List -->
                        <div class="section-card card">
                            <div class="section-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-gear"></i> Danh sách dịch vụ đã phân bổ</span>
                                <?php if (!empty($phanBoDichVu)): ?>
                                    <span class="badge bg-primary">Tổng: <?php echo number_format($tongChiPhi ?? 0); ?> VNĐ</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-0">
            <?php if (!empty($phanBoDichVu)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-custom">
                    <thead>
                        <tr>
                                                    <th>Dịch vụ</th>
                            <th>Nhà cung cấp</th>
                            <th>Số lượng</th>
                            <th>Thời gian</th>
                            <th>Địa điểm</th>
                            <th>Giá tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($phanBoDichVu as $pb): ?>
                            <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="service-icon bg-primary bg-opacity-10 text-primary">
                                                                    <i class="bi bi-<?php 
                                                                        echo match($pb['loai_dich_vu']) {
                                                                            'Xe' => 'bus-front',
                                                                            'KhachSan' => 'building',
                                                                            'VeMayBay' => 'airplane',
                                                                            'NhaHang' => 'shop',
                                                                            'DiemThamQuan' => 'pin-map',
                                                                            'Visa' => 'credit-card',
                                                                            'BaoHiem' => 'shield-check',
                                                                            default => 'gear'
                                                                        };
                                                                    ?>"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-semibold"><?php echo htmlspecialchars($pb['ten_dich_vu']); ?></div>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($pb['loai_dich_vu']); ?></small>
                                                                </div>
                                                            </div>
                                                        </td>
                                <td><?php echo htmlspecialchars($pb['ten_don_vi'] ?? 'N/A'); ?></td>
                                <td><?php echo $pb['so_luong']; ?> <?php echo htmlspecialchars($pb['don_vi'] ?? ''); ?></td>
                                <td>
                                                            <small>
                                    <?php if ($pb['ngay_bat_dau']): ?>
                                        <?php echo date('d/m/Y', strtotime($pb['ngay_bat_dau'])); ?>
                                        <?php if ($pb['gio_bat_dau']): ?>
                                                                        <?php echo substr($pb['gio_bat_dau'], 0, 5); ?>
                                        <?php endif; ?>
                                        <?php if ($pb['ngay_ket_thuc'] && $pb['ngay_ket_thuc'] != $pb['ngay_bat_dau']): ?>
                                                                        <br>- <?php echo date('d/m/Y', strtotime($pb['ngay_ket_thuc'])); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                                            </small>
                                </td>
                                                        <td><small><?php echo htmlspecialchars($pb['dia_diem'] ?? 'N/A'); ?></small></td>
                                                        <td class="fw-bold text-primary"><?php echo $pb['gia_tien'] ? number_format($pb['gia_tien']) : '0'; ?> VNĐ</td>
                                <td>
                                    <?php
                                                            $badgeClass = match($pb['trang_thai'] ?? 'ChoXacNhan') {
                                                                'DaXacNhan' => 'bg-success',
                                                                'TuChoi' => 'bg-danger',
                                                                'Huy' => 'bg-secondary',
                                                                'HoanTat' => 'bg-primary',
                                                                default => 'bg-warning text-dark'
                                                            };
                                                            $trangThaiText = match($pb['trang_thai'] ?? 'ChoXacNhan') {
                                        'DaXacNhan' => 'Đã xác nhận',
                                        'TuChoi' => 'Từ chối',
                                        'Huy' => 'Hủy',
                                                                'HoanTat' => 'Hoàn tất',
                                                                default => 'Chờ xác nhận'
                                                            };
                                                            ?>
                                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $trangThaiText; ?></span>
                                </td>
                                <td>
                                    <form method="POST" action="index.php?act=lichKhoiHanh/deleteDichVu" onsubmit="return confirm('Xóa phân bổ này?');" class="d-inline">
                                        <?php echo csrfField('lich_khoi_hanh_form'); ?>
                                        <input type="hidden" name="id" value="<?php echo $pb['id']; ?>">
                                        <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                                    </div>
            <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-gear fs-1 opacity-25"></i>
                                        <p class="mt-3">Chưa có dịch vụ nào được phân bổ</p>
                                    </div>
            <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <!-- Tab: Yêu cầu đặc biệt -->
                <div class="tab-pane" id="special-request" style="display: none;">
                        <!-- Add Request Form -->
                        <div class="add-form-card">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-plus-circle"></i> Thêm yêu cầu đặc biệt
                            </h6>
                            <form method="POST" action="index.php?act=lichKhoiHanh/themYeuCauDacBiet">
                                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Khách hàng <span class="text-danger">*</span></label>
                                        <select name="booking_id" class="form-select" required>
                                            <option value="">-- Chọn khách hàng --</option>
                                            <?php foreach ($bookingList as $b): ?>
                                                <option value="<?php echo $b['booking_id']; ?>">
                                                    <?php echo htmlspecialchars($b['ho_ten'] ?? 'N/A'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Loại yêu cầu <span class="text-danger">*</span></label>
                                        <select name="loai_yeu_cau" class="form-select" required>
                                            <option value="ThucPham">Thực phẩm / Dị ứng</option>
                                            <option value="YTe">Y tế / Sức khỏe</option>
                                            <option value="DichVu">Yêu cầu dịch vụ</option>
                                            <option value="NguNgu">Ngủ ngơi / Chỗ ở</option>
                                            <option value="AnToan">An toàn / Sự cố</option>
                                            <option value="Khac">Khác</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Mức độ ưu tiên <span class="text-danger">*</span></label>
                                        <select name="muc_do_uu_tien" class="form-select" required>
                                            <option value="Thap">Thấp</option>
                                            <option value="Trung" selected>Trung bình</option>
                                            <option value="Cao">Cao</option>
                                            <option value="RatCao">Rất cao</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold">Trạng thái</label>
                                        <select name="trang_thai" class="form-select">
                                            <option value="Moi">Mới</option>
                                            <option value="DangXuLy">Đang xử lý</option>
                                            <option value="HoanTat">Hoàn tất</option>
                                            <option value="KhongTheXuLy">Không thể xử lý</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Nội dung yêu cầu <span class="text-danger">*</span></label>
                                        <textarea name="noi_dung" class="form-control" rows="3" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Thêm yêu cầu
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="section-card card">
                            <div class="section-header">
                                <i class="bi bi-heart-pulse"></i> Yêu cầu đặc biệt của khách hàng
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($yeuCauDacBietList)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-custom">
                                            <thead>
                                                <tr>
                                                    <th>Khách hàng</th>
                                                    <th>Loại yêu cầu</th>
                                                    <th>Mô tả</th>
                                                    <th>Mức độ ưu tiên</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ghi chú HDV</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $priorityMap = [
                                                    'khan_cap' => ['label' => 'Khẩn cấp', 'badge' => 'danger'],
                                                    'cao' => ['label' => 'Cao', 'badge' => 'warning'],
                                                    'trung_binh' => ['label' => 'Trung bình', 'badge' => 'info'],
                                                    'thap' => ['label' => 'Thấp', 'badge' => 'secondary'],
                                                ];
                                                $statusMap = [
                                                    'moi' => ['label' => 'Mới', 'badge' => 'secondary'],
                                                    'dang_xu_ly' => ['label' => 'Đang xử lý', 'badge' => 'primary'],
                                                    'da_giai_quyet' => ['label' => 'Đã giải quyết', 'badge' => 'success'],
                                                    'khong_the_thuc_hien' => ['label' => 'Không thể thực hiện', 'badge' => 'danger'],
                                                ];
                                                foreach ($yeuCauDacBietList as $yc): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($yc['ho_ten'] ?? 'N/A'); ?></div>
                                                            <small class="text-muted"><?php echo htmlspecialchars($yc['email'] ?? ''); ?></small>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($yc['loai_yeu_cau'] ?? 'Khác'); ?></td>
                                                        <td>
                                                            <div class="request-note"><?php echo htmlspecialchars($yc['mo_ta'] ?? ''); ?></div>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $priority = $yc['muc_do_uu_tien'] ?? 'trung_binh';
                                                            $priorityInfo = $priorityMap[$priority] ?? $priorityMap['trung_binh'];
                                                            ?>
                                                            <span class="badge bg-<?php echo $priorityInfo['badge']; ?>">
                                                                <?php echo $priorityInfo['label']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $status = $yc['trang_thai'] ?? 'moi';
                                                            $statusInfo = $statusMap[$status] ?? $statusMap['moi'];
                                                            ?>
                                                            <span class="badge bg-<?php echo $statusInfo['badge']; ?>">
                                                                <?php echo $statusInfo['label']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small><?php echo htmlspecialchars($yc['ghi_chu_hdv'] ?? 'Chưa có'); ?></small>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editYeuCauModal<?php echo $yc['yeu_cau_id']; ?>">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form method="POST" action="index.php?act=lichKhoiHanh/xoaYeuCauDacBiet" onsubmit="return confirm('Xóa yêu cầu này?');" class="d-inline">
                                                                <?php echo csrfField('lich_khoi_hanh_form'); ?>
                                                                <input type="hidden" name="id" value="<?php echo $yc['yeu_cau_id']; ?>">
                                                                <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-heart-pulse fs-1 opacity-25"></i>
                                        <p class="mt-3">Chưa có yêu cầu đặc biệt nào cho lịch khởi hành này</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <!-- Tab: Nhật ký tour -->
                <div class="tab-pane" id="diary" style="display: none;">
                        <div class="section-card card">
                            <div class="section-header">
                                <i class="bi bi-journal-text"></i> Nhật ký tour
                            </div>
                            <div class="card-body p-0">
                                <?php if (!empty($nhatKyTourList)): ?>
                                    <div class="p-3">
                                        <?php foreach ($nhatKyTourList as $nhatKy): ?>
                                            <div class="diary-entry mb-3">
                                                <div class="entry-header">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($nhatKy['tieu_de'] ?? 'Nhật ký'); ?></h6>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar"></i> <?php echo $nhatKy['ngay_ghi'] ? date('d/m/Y H:i', strtotime($nhatKy['ngay_ghi'])) : 'N/A'; ?>
                                                            <?php if (!empty($nhatKy['hdv_ten'] ?? $nhatKy['nhan_su_ten'] ?? null)): ?>
                                                                | <i class="bi bi-person"></i> <?php echo htmlspecialchars($nhatKy['hdv_ten'] ?? $nhatKy['nhan_su_ten'] ?? ''); ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                    <span class="entry-type-badge type-<?php echo htmlspecialchars($nhatKy['loai_nhat_ky'] ?? 'hanh_trinh'); ?>">
                                                        <?php 
                                                        $loaiLabels = [
                                                            'hanh_trinh' => 'Hành trình',
                                                            'su_co' => 'Sự cố',
                                                            'phan_hoi' => 'Phản hồi',
                                                            'hoat_dong' => 'Hoạt động'
                                                        ];
                                                        echo $loaiLabels[$nhatKy['loai_nhat_ky']] ?? 'Hành trình';
                                                        ?>
                                                    </span>
                                                </div>
                                                <div class="entry-content">
                                                    <p class="mb-2"><?php echo nl2br(htmlspecialchars($nhatKy['noi_dung'] ?? '')); ?></p>
                                                    <?php if (!empty($nhatKy['cach_xu_ly'])): ?>
                                                        <div class="alert alert-info mb-0">
                                                            <strong>Cách xử lý:</strong> <?php echo nl2br(htmlspecialchars($nhatKy['cach_xu_ly'])); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($nhatKy['hinh_anh'])): ?>
                                                        <?php 
                                                        $images = json_decode($nhatKy['hinh_anh'], true);
                                                        if ($images && is_array($images)): ?>
                                                            <div class="image-gallery mt-3">
                                                                <?php foreach ($images as $img): ?>
                                                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Hình ảnh nhật ký" onclick="window.open(this.src, '_blank')">
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="bi bi-journal-text fs-1 opacity-25"></i>
                                        <p class="mt-3">Chưa có nhật ký nào cho tour này</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
    // Tab switching function
    function switchTab(tabName) {
        // Hide all tab panes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.style.display = 'none';
        });
        
        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = 'rgba(45, 45, 45, 0.3)';
            btn.style.color = 'var(--text-muted)';
            btn.style.borderBottom = 'none';
        });
        
        // Show selected tab pane
        const selectedPane = document.getElementById(tabName);
        if (selectedPane) {
            selectedPane.style.display = 'block';
        }
        
        // Add active class to selected tab button
        const selectedBtn = event.target.closest('.tab-btn');
        if (selectedBtn) {
            selectedBtn.classList.add('active');
            selectedBtn.style.background = 'rgba(45, 45, 45, 0.5)';
            selectedBtn.style.color = 'var(--text-light)';
            selectedBtn.style.borderBottom = '2px solid var(--accent-gold)';
        }
    }
    
    (function() {
        // --- Thêm/xóa form khách nhanh ---
        const guestWrapper = document.getElementById('guestFormsWrapper');
        const addGuestBtn = document.getElementById('btnAddGuestRow');

        function updateGuestIndexes() {
            if (!guestWrapper) return;
            const forms = guestWrapper.querySelectorAll('.guest-form');
            forms.forEach((form, idx) => {
                const indexSpan = form.querySelector('.guest-index');
                if (indexSpan) {
                    indexSpan.textContent = idx + 1;
                }
                const removeBtn = form.querySelector('.btn-remove-guest');
                if (removeBtn) {
                    removeBtn.style.display = forms.length > 1 ? 'inline-block' : 'none';
                }
            });
        }

        function clearGuestForm(form) {
            form.querySelectorAll('input, textarea').forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else if (input.name && !input.name.startsWith('quoc_tich')) {
                    input.value = '';
                }
            });
        }

        if (guestWrapper && addGuestBtn) {
            addGuestBtn.addEventListener('click', function() {
                const firstForm = guestWrapper.querySelector('.guest-form');
                if (!firstForm) return;

                const clone = firstForm.cloneNode(true);
                clearGuestForm(clone);

                // gắn lại event cho nút xóa của clone
                const removeBtn = clone.querySelector('.btn-remove-guest');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        if (guestWrapper.querySelectorAll('.guest-form').length > 1) {
                            clone.remove();
                            updateGuestIndexes();
                        }
                    });
                }

                guestWrapper.appendChild(clone);
                updateGuestIndexes();
            });

            // event xóa cho form đầu tiên
            const firstRemove = guestWrapper.querySelector('.guest-form .btn-remove-guest');
            if (firstRemove) {
                firstRemove.addEventListener('click', function(e) {
                    const form = e.currentTarget.closest('.guest-form');
                    if (form && guestWrapper.querySelectorAll('.guest-form').length > 1) {
                        form.remove();
                        updateGuestIndexes();
                    }
                });
            }

            updateGuestIndexes();
        }

        // --- JS cho dịch vụ & nhà cung cấp ---
        const supplierCatalog = <?php echo json_encode($catalogServicesMap, JSON_UNESCAPED_UNICODE); ?>;
        const serviceTypeLabels = <?php echo json_encode($serviceTypeOptions, JSON_UNESCAPED_UNICODE); ?>;

        const supplierSelect = document.getElementById('supplierSelect');
        const catalogSelect = document.getElementById('catalogServiceSelect');
        if (!supplierSelect || !catalogSelect) {
            return;
        }

        const loaiSelect = document.getElementById('loaiDichVuSelect');
        const tenInput = document.getElementById('tenDichVuInput');
        const donViInput = document.getElementById('donViInput');
        const giaTienInput = document.getElementById('giaTienInput');
        const ghiChuInput = document.getElementById('ghiChuInput');
        const soLuongInput = document.getElementById('soLuongInput');
        const catalogServiceIdInput = document.getElementById('catalogServiceIdInput');

        function resetCatalogSelect() {
            catalogSelect.innerHTML = '<option value="">-- Chọn dịch vụ có sẵn --</option>';
            catalogSelect.disabled = true;
            if (catalogServiceIdInput) {
                catalogServiceIdInput.value = '';
            }
        }

        function populateCatalogOptions(supplierId) {
            resetCatalogSelect();
            if (!supplierId || !supplierCatalog[supplierId] || supplierCatalog[supplierId].length === 0) {
                return;
            }
            supplierCatalog[supplierId].forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                const typeLabel = serviceTypeLabels[service.loai_dich_vu] ?? service.loai_dich_vu ?? '';
                option.textContent = service.ten_dich_vu + (typeLabel ? ` (${typeLabel})` : '');
                option.dataset.service = JSON.stringify({
                    loai: service.loai_dich_vu || '',
                    ten: service.ten_dich_vu || '',
                    donVi: service.don_vi_tinh || '',
                    gia: service.gia_tham_khao || '',
                    ghiChu: service.mo_ta || '',
                    soLuong: service.cong_suat_toi_da || ''
                });
                catalogSelect.appendChild(option);
            });
            catalogSelect.disabled = false;
        }

        function applyCatalogData(raw) {
            if (!raw) return;
            if (loaiSelect && raw.loai) {
                const hasOption = Array.from(loaiSelect.options).some(opt => opt.value === raw.loai);
                if (hasOption) {
                    loaiSelect.value = raw.loai;
                }
            }
            if (tenInput && raw.ten) {
                tenInput.value = raw.ten;
            }
            if (donViInput) {
                donViInput.value = raw.donVi || '';
            }
            if (giaTienInput) {
                giaTienInput.value = raw.gia || '';
            }
            if (ghiChuInput) {
                ghiChuInput.value = raw.ghiChu || '';
            }
            if (soLuongInput && raw.soLuong) {
                soLuongInput.value = raw.soLuong;
            }
        }

        supplierSelect.addEventListener('change', function() {
            populateCatalogOptions(this.value);
        });

        catalogSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (!selected || !selected.dataset.service) {
                if (catalogServiceIdInput) {
                    catalogServiceIdInput.value = '';
                }
                return;
            }
            try {
                const data = JSON.parse(selected.dataset.service);
                applyCatalogData(data);
                if (catalogServiceIdInput) {
                    catalogServiceIdInput.value = selected.value || '';
                }
            } catch (error) {
                console.error('Không thể đọc dữ liệu dịch vụ có sẵn', error);
            }
        });

        if (supplierSelect.value) {
            populateCatalogOptions(supplierSelect.value);
        }
    })();

    // Kiểm tra trùng lịch khi chọn nhân sự
    (function() {
        const nhanSuSelect = document.getElementById('nhanSuSelect');
        const vaiTroSelect = document.getElementById('vaiTroSelect');
        const conflictWarning = document.getElementById('conflictWarning');
        const conflictMessage = document.getElementById('conflictMessage');
        const lichKhoiHanhId = <?php echo $lichKhoiHanh['id']; ?>;

        function checkConflict() {
            const nhanSuId = nhanSuSelect?.value;
            const vaiTro = vaiTroSelect?.value;

            if (!nhanSuId || !vaiTro || vaiTro !== 'HDV') {
                if (conflictWarning) {
                    conflictWarning.classList.add('d-none');
                }
                return;
            }

            // Gọi API để kiểm tra conflict
            const formData = new FormData();
            formData.append('lich_khoi_hanh_id', lichKhoiHanhId);
            formData.append('nhan_su_id', nhanSuId);
            formData.append('vai_tro', vaiTro);

            fetch('index.php?act=lichKhoiHanh/checkConflict', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.hasConflict) {
                    if (conflictWarning && conflictMessage) {
                        conflictMessage.innerHTML = '';
                        if (data.conflicts && data.conflicts.length > 0) {
                            conflictMessage.innerHTML += '<strong>Nhân sự này đang được phân công vào các lịch trùng ngày:</strong><ul class="mb-2 mt-2">';
                            data.conflicts.forEach(conflict => {
                                conflictMessage.innerHTML += '<li>' + conflict + '</li>';
                            });
                            conflictMessage.innerHTML += '</ul><strong class="text-danger">Vẫn cho phép phân bổ, nhưng HDV cần cân nhắc tránh quá tải.</strong>';
                        } else {
                            conflictMessage.innerHTML = data.message || 'Nhân sự này có lịch trùng với lịch khác.';
                        }
                        conflictWarning.classList.remove('d-none');
                    }
                } else {
                    if (conflictWarning) {
                        conflictWarning.classList.add('d-none');
                    }
                }
            })
            .catch(error => {
                console.error('Lỗi kiểm tra trùng lịch:', error);
            });
        }

        if (nhanSuSelect && vaiTroSelect) {
            nhanSuSelect.addEventListener('change', checkConflict);
            vaiTroSelect.addEventListener('change', checkConflict);
        }
    })();
    
    // Modal functions
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
        }
    }
    
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
    </script>

    <!-- Modal sửa yêu cầu đặc biệt -->
    <?php if (!empty($yeuCauDacBietList)): ?>
        <?php foreach ($yeuCauDacBietList as $yc): ?>
        <div class="modal-overlay" id="editYeuCauModal<?php echo $yc['yeu_cau_id']; ?>" onclick="closeModal('editYeuCauModal<?php echo $yc['yeu_cau_id']; ?>')" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 1000; backdrop-filter: blur(5px);">
            <div class="modal-content" onclick="event.stopPropagation()" style="background: rgba(30, 30, 30, 0.95); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 4px; padding: 0; max-width: 600px; margin: 50px auto; max-height: 90vh; overflow-y: auto;">
                <div style="padding: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center;">
                    <h5 style="margin: 0; color: var(--text-light);">Sửa yêu cầu đặc biệt</h5>
                    <button type="button" onclick="closeModal('editYeuCauModal<?php echo $yc['yeu_cau_id']; ?>')" style="background: none; border: none; color: var(--text-light); font-size: 1.5rem; cursor: pointer;">&times;</button>
                </div>
                    <form method="POST" action="index.php?act=lichKhoiHanh/suaYeuCauDacBiet">
                        <input type="hidden" name="id" value="<?php echo $yc['yeu_cau_id']; ?>">
                        <input type="hidden" name="lich_khoi_hanh_id" value="<?php echo $lichKhoiHanh['id']; ?>">
                <div style="padding: 20px;">
                            <div class="mb-3">
                                <label class="form-label">Loại yêu cầu <span class="text-danger">*</span></label>
                                <select name="loai_yeu_cau" class="form-select" required>
                                    <option value="ThucPham" <?php echo ($yc['loai_yeu_cau'] ?? '') == 'ThucPham' ? 'selected' : ''; ?>>Thực phẩm / Dị ứng</option>
                                    <option value="YTe" <?php echo ($yc['loai_yeu_cau'] ?? '') == 'YTe' ? 'selected' : ''; ?>>Y tế / Sức khỏe</option>
                                    <option value="DichVu" <?php echo ($yc['loai_yeu_cau'] ?? '') == 'DichVu' ? 'selected' : ''; ?>>Yêu cầu dịch vụ</option>
                                    <option value="NguNgu" <?php echo ($yc['loai_yeu_cau'] ?? '') == 'NguNgu' ? 'selected' : ''; ?>>Ngủ ngơi / Chỗ ở</option>
                                    <option value="AnToan" <?php echo ($yc['loai_yeu_cau'] ?? '') == 'AnToan' ? 'selected' : ''; ?>>An toàn / Sự cố</option>
                                    <option value="Khac" <?php echo empty($yc['loai_yeu_cau']) || ($yc['loai_yeu_cau'] ?? '') == 'Khac' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mức độ ưu tiên <span class="text-danger">*</span></label>
                                <select name="muc_do_uu_tien" class="form-select" required>
                                    <option value="Thap" <?php echo ($yc['muc_do_uu_tien'] ?? '') == 'Thap' ? 'selected' : ''; ?>>Thấp</option>
                                    <option value="Trung" <?php echo empty($yc['muc_do_uu_tien']) || ($yc['muc_do_uu_tien'] ?? '') == 'Trung' ? 'selected' : ''; ?>>Trung bình</option>
                                    <option value="Cao" <?php echo ($yc['muc_do_uu_tien'] ?? '') == 'Cao' ? 'selected' : ''; ?>>Cao</option>
                                    <option value="RatCao" <?php echo ($yc['muc_do_uu_tien'] ?? '') == 'RatCao' ? 'selected' : ''; ?>>Rất cao</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" class="form-select">
                                    <option value="Moi" <?php echo empty($yc['trang_thai']) || ($yc['trang_thai'] ?? '') == 'Moi' ? 'selected' : ''; ?>>Mới</option>
                                    <option value="DangXuLy" <?php echo ($yc['trang_thai'] ?? '') == 'DangXuLy' ? 'selected' : ''; ?>>Đang xử lý</option>
                                    <option value="HoanTat" <?php echo ($yc['trang_thai'] ?? '') == 'HoanTat' ? 'selected' : ''; ?>>Hoàn tất</option>
                                    <option value="KhongTheXuLy" <?php echo ($yc['trang_thai'] ?? '') == 'KhongTheXuLy' ? 'selected' : ''; ?>>Không thể xử lý</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                <textarea name="noi_dung" class="form-control" rows="3" required><?php echo htmlspecialchars($yc['mo_ta'] ?? ''); ?></textarea>
                            </div>
                        </div>
                <div style="padding: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal('editYeuCauModal<?php echo $yc['yeu_cau_id']; ?>')" style="background: rgba(108, 117, 125, 0.3); border: 1px solid rgba(108, 117, 125, 0.5); color: var(--text-light); padding: 10px 20px; border-radius: 4px; cursor: pointer;">Hủy</button>
                    <button type="submit" style="background: var(--accent-gold); color: #000; padding: 10px 20px; border-radius: 4px; border: none; cursor: pointer; font-weight: 500;">Lưu thay đổi</button>
                </div>
                    </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
