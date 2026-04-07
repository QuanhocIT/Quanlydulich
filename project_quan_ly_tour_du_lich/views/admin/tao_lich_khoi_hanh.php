<?php
$pageTitle = isset($mode) && $mode === 'edit' ? 'Sửa Lịch Khởi Hành' : 'Tạo Lịch Khởi Hành';
$currentPage = 'lichKhoiHanh';
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
        .form-section {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            padding: 25px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--accent-gold);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .required-label::after {
            content: " *";
            color: #dc3545;
        }
        .info-card {
            background: rgba(45, 45, 45, 0.3);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .btn-action-group {
            position: sticky;
            bottom: 0;
            background: rgba(30, 30, 30, 0.95);
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 30px;
            backdrop-filter: blur(10px);
        }
        
        /* Form elements */
        .form-control, .form-select {
            background: rgba(30, 30, 30, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            padding: 10px 15px;
            border-radius: 4px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(30, 30, 30, 0.9);
            border-color: var(--accent-gold);
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2);
            color: var(--text-light);
        }
        .form-label {
            color: var(--text-light);
            margin-bottom: 8px;
            display: block;
        }
        .form-select-lg {
            padding: 12px 15px;
            font-size: 1rem;
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
        .btn-outline-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(108, 117, 125, 0.5);
        }
        .btn-outline-secondary:hover {
            background: rgba(108, 117, 125, 0.5);
        }
        .btn-outline-danger {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        .btn-outline-danger:hover {
            background: rgba(220, 53, 69, 0.5);
        }
        .btn-light {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-light:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .btn-lg {
            padding: 12px 24px;
            font-size: 1rem;
        }
        
        /* Badges */
        .badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .bg-info { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        .bg-success { background: rgba(40, 167, 69, 0.3); color: #5cb85c; }
        .bg-secondary { background: rgba(108, 117, 125, 0.3); color: #adb5bd; }
        
        /* Card */
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        .card-body {
            padding: 20px;
            color: var(--text-light);
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        .alert-info {
            background: rgba(0, 123, 255, 0.2);
            border: 1px solid rgba(0, 123, 255, 0.5);
            color: #4da3ff;
        }
        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid rgba(255, 193, 7, 0.5);
            color: #ffc107;
        }
        
        /* Text colors */
        .text-primary { color: #4da3ff !important; }
        .text-info { color: #4da3ff !important; }
        .text-success { color: #5cb85c !important; }
        .text-warning { color: #ffc107 !important; }
        .text-danger { color: #dc3545 !important; }
        .text-muted { color: var(--text-muted) !important; }
        
        /* Bootstrap grid compatibility */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -15px;
            margin-right: -15px;
        }
        .row > * {
            padding-left: 15px;
            padding-right: 15px;
        }
        .col-12 { width: 100%; }
        .col-md-6 { width: 50%; }
        .col-lg-4 { width: 33.333333%; }
        .col-lg-8 { width: 66.666667%; }
        .g-3 { gap: 1rem; }
        .mb-0 { margin-bottom: 0; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .me-2 { margin-right: 0.5rem; }
        .ps-3 { padding-left: 1rem; }
        .small { font-size: 0.875rem; }
        .fw-bold { font-weight: 700; }
        .fw-semibold { font-weight: 600; }
        
        /* Responsive */
        @media (max-width: 992px) {
            .col-md-6, .col-lg-4, .col-lg-8 {
                width: 100%;
            }
        }
    </style>

<div style="padding: 20px;">
    <!-- Page Header -->
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-<?php echo isset($mode) && $mode === 'edit' ? 'pencil-square' : 'plus-circle'; ?>" style="color: var(--accent-gold);"></i>
                <?php echo isset($mode) && $mode === 'edit' ? 'Sửa Lịch Khởi Hành' : 'Tạo Lịch Khởi Hành Mới'; ?>
            </h1>
            <p style="margin: 0; opacity: 0.8; color: var(--text-light);">
                <?php echo isset($mode) && $mode === 'edit' ? 'Chỉnh sửa thông tin lịch khởi hành' : 'Thêm lịch khởi hành mới cho tour'; ?>
            </p>
        </div>
        <a href="index.php?act=lichKhoiHanh/index" style="background: rgba(255, 255, 255, 0.1); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.5); color: #dc3545; padding: 15px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px;">
        <!-- Left Column: Form -->
        <div>
                <form method="POST" action="index.php?act=<?php echo (isset($mode) && $mode === 'edit') ? 'lichKhoiHanh/update' : 'lichKhoiHanh/create'; ?>">
                    <?php if (isset($mode) && $mode === 'edit' && isset($lichKhoiHanh['id'])): ?>
                        <input type="hidden" name="id" value="<?php echo (int)$lichKhoiHanh['id']; ?>">
                    <?php endif; ?>
                    <!-- Tour Selection -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-compass"></i> Chọn Tour
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label required-label fw-semibold">Tour</label>
                            <select name="tour_id" class="form-select form-select-lg" required <?php echo (isset($mode) && $mode === 'edit') ? 'disabled' : ''; ?>>
                                <option value="">-- Chọn tour --</option>
                                <?php foreach ($tours as $tour): ?>
                                    <option value="<?php echo $tour['tour_id']; ?>" <?php echo (isset($lichKhoiHanh['tour_id']) && $lichKhoiHanh['tour_id'] == $tour['tour_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tour['ten_tour']); ?>
                                        <?php if (isset($tour['gia_co_ban'])): ?>
                                            - <?php echo number_format($tour['gia_co_ban'], 0, ',', '.'); ?> VNĐ
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Schedule Details -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-calendar-range"></i> Thông tin lịch trình
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required-label fw-semibold">
                                    <i class="bi bi-calendar-event text-primary"></i> Ngày khởi hành
                                </label>
                                <input type="date" name="ngay_khoi_hanh" class="form-control" required
                                       value="<?php echo isset($lichKhoiHanh['ngay_khoi_hanh']) ? htmlspecialchars($lichKhoiHanh['ngay_khoi_hanh']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock text-info"></i> Giờ xuất phát
                                </label>
                                <input type="time" name="gio_xuat_phat" class="form-control"
                                       value="<?php echo isset($lichKhoiHanh['gio_xuat_phat']) ? htmlspecialchars($lichKhoiHanh['gio_xuat_phat']) : '07:00'; ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-check text-success"></i> Ngày kết thúc
                                </label>
                                <input type="date" name="ngay_ket_thuc" class="form-control"
                                       value="<?php echo isset($lichKhoiHanh['ngay_ket_thuc']) ? htmlspecialchars($lichKhoiHanh['ngay_ket_thuc']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-clock text-warning"></i> Giờ kết thúc
                                </label>
                                <input type="time" name="gio_ket_thuc" class="form-control"
                                       value="<?php echo isset($lichKhoiHanh['gio_ket_thuc']) ? htmlspecialchars($lichKhoiHanh['gio_ket_thuc']) : '17:00'; ?>">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt text-danger"></i> Điểm tập trung
                                </label>
                                <input type="text" name="diem_tap_trung" class="form-control" 
                                       placeholder="VD: Số 54 Trần Đại Nghĩa, Hai Bà Trưng, Hà Nội"
                                       value="<?php echo isset($lichKhoiHanh['diem_tap_trung']) ? htmlspecialchars($lichKhoiHanh['diem_tap_trung']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Capacity & Staff -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-people"></i> Sức chứa & Nhân sự
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required-label fw-semibold">
                                    <i class="bi bi-people-fill text-primary"></i> Số chỗ
                                </label>
                                <input type="number" name="so_cho" class="form-control" min="1" required
                                       value="<?php echo isset($lichKhoiHanh['so_cho']) ? (int)$lichKhoiHanh['so_cho'] : 50; ?>">
                                <small class="text-muted">Số lượng khách tối đa cho lịch khởi hành này</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person-badge text-success"></i> Hướng dẫn viên chính
                                </label>
                                <select name="hdv_id" class="form-select">
                                    <option value="">-- Chọn HDV --</option>
                                    <?php
                                    require_once 'models/NhanSu.php';
                                    $nhanSuModel = new NhanSu();
                                    $hdvList = $nhanSuModel->getByRole('HDV');
                                    foreach ($hdvList as $hdv):
                                    ?>
                                        <option value="<?php echo $hdv['nhan_su_id']; ?>" <?php echo (isset($lichKhoiHanh['hdv_id']) && $lichKhoiHanh['hdv_id'] == $hdv['nhan_su_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?>
                                            <?php if (isset($hdv['so_dien_thoai'])): ?>
                                                - <?php echo htmlspecialchars($hdv['so_dien_thoai']); ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Notes -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-info-circle"></i> Trạng thái & Ghi chú
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-toggle-on text-primary"></i> Trạng thái
                                </label>
                                <?php $currentStatus = $lichKhoiHanh['trang_thai'] ?? 'SapKhoiHanh'; ?>
                                <select name="trang_thai" class="form-select">
                                    <option value="SapKhoiHanh" <?php echo $currentStatus === 'SapKhoiHanh' ? 'selected' : ''; ?>>Sắp khởi hành</option>
                                    <option value="DangChay" <?php echo $currentStatus === 'DangChay' ? 'selected' : ''; ?>>Đang chạy</option>
                                    <option value="HoanThanh" <?php echo $currentStatus === 'HoanThanh' ? 'selected' : ''; ?>>Hoàn thành</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-chat-left-text text-info"></i> Ghi chú
                                </label>
                                <textarea name="ghi_chu" class="form-control" rows="3" 
                                          placeholder="Thêm ghi chú nếu cần..."><?php echo isset($lichKhoiHanh['ghi_chu']) ? htmlspecialchars($lichKhoiHanh['ghi_chu']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-action-group">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i>
                                <?php echo isset($mode) && $mode === 'edit' ? 'Lưu lịch khởi hành' : 'Tạo lịch khởi hành'; ?>
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
                            </button>
                            <a href="index.php?act=lichKhoiHanh/index" class="btn btn-outline-danger btn-lg">
                                <i class="bi bi-x-circle"></i> Hủy bỏ
                            </a>
                        </div>
                    </div>
                </form>
            </div>

        <!-- Right Column: Guide -->
        <div>
                <!-- Guide Card -->
                <div class="info-card">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-lightbulb text-warning"></i> Hướng dẫn
                    </h6>
                    <ul class="mb-0 ps-3 small">
                        <li class="mb-2">Chọn tour từ danh sách có sẵn</li>
                        <li class="mb-2">Nhập ngày giờ khởi hành và kết thúc chính xác</li>
                        <li class="mb-2">Điểm tập trung nên ghi rõ địa chỉ cụ thể</li>
                        <li class="mb-2">Số chỗ mặc định là 50, có thể điều chỉnh</li>
                        <li class="mb-2">HDV có thể chọn ngay hoặc phân bổ sau</li>
                        <li class="mb-2">Trạng thái mặc định là "Sắp khởi hành"</li>
                    </ul>
                </div>

            <!-- Status Info -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <h6 style="font-weight: 700; margin-bottom: 15px; color: var(--text-light);">
                        <i class="bi bi-info-circle" style="color: #4da3ff;"></i> Trạng thái
                    </h6>
                    <div style="margin-bottom: 10px;">
                        <span class="badge bg-info me-2">Sắp khởi hành</span>
                        <small class="text-muted">Chưa bắt đầu tour</small>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span class="badge bg-success me-2">Đang chạy</span>
                        <small class="text-muted">Tour đang diễn ra</small>
                    </div>
                    <div>
                        <span class="badge bg-secondary me-2">Hoàn thành</span>
                        <small class="text-muted">Tour đã kết thúc</small>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card">
                <div class="card-body">
                    <h6 style="font-weight: 700; margin-bottom: 15px; color: var(--text-light);">
                        <i class="bi bi-stars" style="color: #ffc107;"></i> Mẹo nhanh
                    </h6>
                    <div class="alert alert-info" style="margin-bottom: 10px; font-size: 0.875rem;">
                        <i class="bi bi-info-circle"></i>
                        Ngày kết thúc nên sau ngày khởi hành
                    </div>
                    <div class="alert alert-warning" style="margin-bottom: 0; font-size: 0.875rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                        Kiểm tra lại thông tin trước khi tạo
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        // Auto-fill end date based on tour duration (optional enhancement)
        document.querySelector('select[name="tour_id"]').addEventListener('change', function() {
            // Could fetch tour duration and auto-calculate end date
        });
        
        // Validate dates
        const startDate = document.querySelector('input[name="ngay_khoi_hanh"]');
        const endDate = document.querySelector('input[name="ngay_ket_thuc"]');
        
        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
    </script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
