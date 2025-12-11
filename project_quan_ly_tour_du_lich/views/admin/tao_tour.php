<?php 
$isCapNhat = isset($tour) && isset($tour['tour_id']); 
$pageTitle = $isCapNhat ? 'Sửa tour' : 'Thêm tour mới';
$currentPage = 'tour';
ob_start();
?>
<style>
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
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 4px;
            border: 2px dashed rgba(255, 255, 255, 0.2);
            padding: 10px;
        }
        .custom-file-upload {
            border: 2px dashed var(--accent-gold);
            display: inline-block;
            padding: 30px;
            cursor: pointer;
            text-align: center;
            border-radius: 4px;
            background: rgba(45, 45, 45, 0.3);
            transition: all 0.3s;
            width: 100%;
            color: var(--text-light);
        }
        .custom-file-upload:hover {
            background: rgba(45, 45, 45, 0.5);
            border-color: #ffd700;
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
        .lich-trinh-item {
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            background: rgba(45, 45, 45, 0.3);
            border-radius: 4px;
        }
        .lich-trinh-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            border-color: var(--accent-gold);
        }
        .lich-trinh-item .card-header {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .lich-trinh-item .card-header .btn-danger {
            background: rgba(220, 53, 69, 0.3);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        .lich-trinh-item .card-header .btn-danger:hover {
            background: rgba(220, 53, 69, 0.5);
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
        .input-group-text {
            background: rgba(45, 45, 45, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-light);
        }
        .list-group-item {
            background: rgba(45, 45, 45, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .img-thumbnail {
            background: rgba(30, 30, 30, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
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
        .btn-outline-primary {
            background: rgba(0, 123, 255, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(0, 123, 255, 0.5);
        }
        .btn-outline-primary:hover {
            background: rgba(0, 123, 255, 0.5);
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
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
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
        .bg-primary { background: rgba(0, 123, 255, 0.3); color: #4da3ff; }
        
        /* Alerts */
        .alert {
            background: rgba(0, 123, 255, 0.2);
            border: 1px solid rgba(0, 123, 255, 0.5);
            color: #4da3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.5);
            color: #dc3545;
        }
        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border-color: rgba(255, 193, 7, 0.5);
            color: #ffc107;
        }
        .alert-info {
            background: rgba(0, 123, 255, 0.2);
            border-color: rgba(0, 123, 255, 0.5);
            color: #4da3ff;
        }
        .alert-heading {
            color: inherit;
            margin-bottom: 10px;
        }
        
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
        
        /* Text colors */
        .text-primary { color: #4da3ff !important; }
        .text-muted { color: var(--text-muted) !important; }
        .text-danger { color: #dc3545 !important; }
        
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
        .col-md-4 { width: 33.333333%; }
        .col-md-6 { width: 50%; }
        .col-md-8 { width: 66.666667%; }
        .col-lg-4 { width: 33.333333%; }
        .col-lg-8 { width: 66.666667%; }
        .g-3 { gap: 1rem; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mt-1 { margin-top: 0.25rem; }
        .px-0 { padding-left: 0; padding-right: 0; }
        .ps-3 { padding-left: 1rem; }
        .w-100 { width: 100%; }
        .small { font-size: 0.875rem; }
        .fw-semibold { font-weight: 600; }
        .list-group-flush .list-group-item {
            border-left: none;
            border-right: none;
            border-radius: 0;
        }
        .list-group-flush .list-group-item:first-child {
            border-top: none;
        }
        .list-group-flush .list-group-item:last-child {
            border-bottom: none;
        }
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-check-input {
            width: auto;
            margin: 0;
        }
        .form-check-label {
            color: var(--text-light);
            margin: 0;
        }
        hr {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 15px 0;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            div[style*="grid-template-columns: 2fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
            .col-md-4, .col-md-6, .col-md-8, .col-lg-4, .col-lg-8 {
                width: 100%;
            }
        }
    </style>
<div style="padding: 20px;">
    <!-- Header -->
    <div class="page-header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
        <div>
            <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-<?php echo $isCapNhat ? 'pencil-square' : 'plus-circle'; ?>" style="color: var(--accent-gold);"></i>
                <?php echo $isCapNhat ? 'Sửa thông tin tour' : 'Thêm tour mới'; ?>
            </h1>
            <p style="margin: 0; opacity: 0.8; color: var(--text-light);">
                <?php echo $isCapNhat ? 'Cập nhật thông tin chi tiết của tour' : 'Điền đầy đủ thông tin để tạo tour mới'; ?>
            </p>
        </div>
        <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyTour" style="background: rgba(255, 255, 255, 0.1); color: var(--text-light); padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.2);">
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
    
    <?php if (isset($_SESSION['image_upload_error'])): ?>
        <div style="background: rgba(255, 193, 7, 0.2); border: 1px solid rgba(255, 193, 7, 0.5); color: #ffc107; padding: 15px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div><i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['image_upload_error']); unset($_SESSION['image_upload_error']); ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
    <?php endif; ?>

        <!-- Form -->



        <form method="post" enctype="multipart/form-data" 
                action="<?php echo BASE_URL; ?>index.php?act=<?php echo $isCapNhat ? 'tour/update' : 'tour/create'; ?>">
            <?php if ($isCapNhat): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($tour['tour_id']); ?>">
            <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px;">
        <div>
                    <!-- Thông tin cơ bản -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-info-circle"></i> Thông tin cơ bản
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label required-label">Tên tour</label>
                                <input type="text" name="ten_tour" class="form-control" 
                                       value="<?php echo htmlspecialchars($tour['ten_tour'] ?? ''); ?>" 
                                       placeholder="VD: Hà Nội - Hạ Long 3N2D"
                                       required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Loại tour</label>
                                <select name="loai_tour" class="form-select">
                                    <?php $loai = $tour['loai_tour'] ?? 'TrongNuoc'; ?>
                                    <option value="TrongNuoc" <?php echo $loai === 'TrongNuoc' ? 'selected' : ''; ?>>Trong nước</option>
                                    <option value="QuocTe" <?php echo $loai === 'QuocTe' ? 'selected' : ''; ?>>Quốc tế</option>
                                    <option value="TheoYeuCau" <?php echo $loai === 'TheoYeuCau' ? 'selected' : ''; ?>>Theo yêu cầu</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mô tả tour</label>
                                <textarea name="mo_ta" class="form-control" rows="4" 
                                          placeholder="Giới thiệu ngắn gọn về tour..."><?php echo htmlspecialchars($tour['mo_ta'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label required-label">Giá cơ bản (VNĐ)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" name="gia_co_ban" class="form-control" 
                                           step="1000" min="0" 
                                           value="<?php echo htmlspecialchars((string)($tour['gia_co_ban'] ?? '0')); ?>" 
                                           required>
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" class="form-select">
                                    <?php $tt = $tour['trang_thai'] ?? 'HoatDong'; ?>
                                    <option value="HoatDong" <?php echo $tt === 'HoatDong' ? 'selected' : ''; ?>>
                                        <i class="bi bi-check-circle"></i> Hoạt động
                                    </option>
                                    <option value="TamDung" <?php echo $tt === 'TamDung' ? 'selected' : ''; ?>>
                                        Tạm dừng
                                    </option>
                                    <option value="HetHan" <?php echo $tt === 'HetHan' ? 'selected' : ''; ?>>
                                        Hết hạn
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin lộ trình -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-geo-alt"></i> Thông tin lộ trình
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Điểm khởi hành</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-pin-map"></i></span>
                                    <input type="text" name="diem_khoi_hanh" class="form-control" 
                                           value="<?php echo htmlspecialchars($tour['diem_khoi_hanh'] ?? ''); ?>" 
                                           placeholder="VD: Hà Nội">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Điểm đến</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-flag"></i></span>
                                    <input type="text" name="diem_den" class="form-control" 
                                           value="<?php echo htmlspecialchars($tour['diem_den'] ?? ''); ?>" 
                                           placeholder="VD: Hạ Long, Quảng Ninh">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Thời gian</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <input type="text" name="thoi_gian" class="form-control" 
                                           value="<?php echo htmlspecialchars($tour['thoi_gian'] ?? ''); ?>" 
                                           placeholder="VD: 3 ngày 2 đêm">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Phương tiện</label>
                                <select name="phuong_tien" class="form-select">
                                    <?php $pt = $tour['phuong_tien'] ?? ''; ?>
                                    <option value="">-- Chọn phương tiện --</option>
                                    <option value="Xe" <?php echo $pt === 'Xe' ? 'selected' : ''; ?>>Xe ô tô</option>
                                    <option value="MayBay" <?php echo $pt === 'MayBay' ? 'selected' : ''; ?>>Máy bay</option>
                                    <option value="Tau" <?php echo $pt === 'Tau' ? 'selected' : ''; ?>>Tàu hỏa</option>
                                    <option value="Khac" <?php echo $pt === 'Khac' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Số chỗ tối đa</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                                    <input type="number" name="so_cho_toi_da" class="form-control" 
                                           min="1" value="<?php echo htmlspecialchars($tour['so_cho_toi_da'] ?? ''); ?>" 
                                           placeholder="VD: 30">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lịch trình chi tiết -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-calendar-week"></i> Lịch trình chi tiết
                        </div>
                        
                        <div id="lichTrinhContainer">
                            <?php if (!empty($lichTrinhList)): ?>
                                <?php foreach ($lichTrinhList as $idx => $lt): ?>
                                    <div class="lich-trinh-item card mb-3" data-index="<?php echo $idx; ?>">
                                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px;">
                                            <span style="font-weight: 600; color: var(--text-light);">
                                                <i class="bi bi-calendar-day" style="color: var(--accent-gold);"></i> 
                                                Ngày <?php echo $lt['ngay_thu'] ?? ($idx + 1); ?>
                                            </span>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="xoaLichTrinh(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <input type="hidden" name="lich_trinh[<?php echo $idx; ?>][ngay_thu]" value="<?php echo $lt['ngay_thu'] ?? ($idx + 1); ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label small">Địa điểm</label>
                                                <input type="text" name="lich_trinh[<?php echo $idx; ?>][dia_diem]" 
                                                       class="form-control" placeholder="VD: Vịnh Hạ Long, Đảo Titop" 
                                                       value="<?php echo htmlspecialchars($lt['dia_diem'] ?? ''); ?>" required>
                                            </div>
                                            
                                            <div class="mb-0">
                                                <label class="form-label small">Hoạt động</label>
                                                <textarea name="lich_trinh[<?php echo $idx; ?>][hoat_dong]" 
                                                          class="form-control" rows="3" 
                                                          placeholder="Mô tả hoạt động trong ngày..."><?php echo htmlspecialchars($lt['hoat_dong'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary w-100" onclick="themLichTrinh()">
                            <i class="bi bi-plus-circle"></i> Thêm ngày mới
                        </button>
                    </div>

                    <!-- Chính sách & điều kiện -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-file-text"></i> Chính sách & Điều kiện
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Điều kiện hủy</label>
                                <textarea name="dieu_kien_huy" class="form-control" rows="3" 
                                          placeholder="VD: Hủy trước 7 ngày: Hoàn 100%, Hủy trước 3 ngày: Hoàn 50%..."><?php echo htmlspecialchars($tour['dieu_kien_huy'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Bao gồm</label>
                                <textarea name="bao_gom" class="form-control" rows="3" 
                                          placeholder="VD: Vé tham quan, Khách sạn 4*, Bữa ăn..."><?php echo htmlspecialchars($tour['bao_gom'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Không bao gồm</label>
                                <textarea name="khong_bao_gom" class="form-control" rows="3" 
                                          placeholder="VD: Chi phí cá nhân, Bảo hiểm..."><?php echo htmlspecialchars($tour['khong_bao_gom'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Lưu ý</label>
                                <textarea name="luu_y" class="form-control" rows="3" 
                                          placeholder="VD: Mang theo CMND/CCCD, Chuẩn bị đồ bơi..."><?php echo htmlspecialchars($tour['luu_y'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
        </div>

        <div>
                <!-- Hình ảnh -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-images"></i> Hình ảnh tour
                    </div>
                    
                    <div id="hinhAnhContainer">
                        <?php if (!empty($hinhAnhList)): ?>
                            <?php foreach ($hinhAnhList as $idx => $anh): ?>
                                <div class="image-item mb-3" data-index="<?php echo $idx; ?>">
                                    <input type="hidden" name="hinh_anh[<?php echo $idx; ?>][url_anh]" value="<?php echo htmlspecialchars($anh['url_anh'] ?? ''); ?>">
                                    <input type="hidden" name="hinh_anh[<?php echo $idx; ?>][mo_ta]" value="<?php echo htmlspecialchars($anh['mo_ta'] ?? ''); ?>">
                                    <input type="hidden" name="hinh_anh[<?php echo $idx; ?>][la_anh_chinh]" value="<?php echo $anh['la_anh_chinh'] ?? 0; ?>">
                                    <?php if (!empty($anh['url_anh'])): ?>
                                        <img src="<?php echo htmlspecialchars($anh['url_anh']); ?>" class="img-thumbnail mb-2" style="max-height: 150px;">
                                    <?php endif; ?>
                                    <input type="file" name="hinh_anh_file[]" class="form-control form-control-sm mb-1" accept="image/*">
                                    <input type="text" name="hinh_anh[<?php echo $idx; ?>][mo_ta]" class="form-control form-control-sm mb-1" placeholder="Mô tả ảnh" value="<?php echo htmlspecialchars($anh['mo_ta'] ?? ''); ?>">
                                    <div class="form-check">
                                        <input type="checkbox" name="hinh_anh[<?php echo $idx; ?>][la_anh_chinh]" value="1" class="form-check-input" <?php echo ($anh['la_anh_chinh'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label small">Ảnh chính</label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger mt-1" onclick="xoaHinhAnh(this)">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                    <hr>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="themHinhAnh()">
                        <i class="bi bi-plus-circle"></i> Thêm ảnh
                    </button>
                </div>                    <!-- Thống kê (chỉ hiện khi sửa) -->
                    <?php if ($isCapNhat): ?>
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-bar-chart"></i> Thống kê
                        </div>
                        
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-hash text-muted"></i> Mã tour</span>
                                <span class="badge bg-primary">#<?php echo $tour['tour_id']; ?></span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="bi bi-calendar text-muted"></i> Ngày tạo</span>
                                <small class="text-muted">
                                    <?php echo isset($tour['ngay_tao']) ? date('d/m/Y', strtotime($tour['ngay_tao'])) : 'N/A'; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Gợi ý -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="bi bi-lightbulb"></i> Gợi ý</h6>
                        <ul class="mb-0 ps-3 small">
                            <li>Tên tour nên ngắn gọn, rõ ràng</li>
                            <li>Mô tả hấp dẫn để thu hút khách</li>
                            <li>Điền đầy đủ thông tin lộ trình</li>
                            <li>Giá cả minh bạch, rõ ràng</li>
                        </ul>
                    </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="btn-action-group">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-check-circle"></i> <?php echo $isCapNhat ? 'Cập nhật tour' : 'Tạo tour mới'; ?>
            </button>
            <button type="reset" class="btn btn-outline-secondary btn-lg">
                <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
            </button>
            <a href="<?php echo BASE_URL; ?>index.php?act=admin/quanLyTour" class="btn btn-outline-danger btn-lg">
                <i class="bi bi-x-circle"></i> Hủy bỏ
            </a>
        </div>
    </div>
    </form>
</div>

<script>
        let hinhAnhIndex = <?php echo !empty($hinhAnhList) ? count($hinhAnhList) : 0; ?>;
        
        function themHinhAnh() {
            const container = document.getElementById('hinhAnhContainer');
            const div = document.createElement('div');
            div.className = 'image-item mb-3';
            div.dataset.index = hinhAnhIndex;
            div.innerHTML = `
                <input type="hidden" name="hinh_anh[${hinhAnhIndex}][url_anh]" value="">
                <input type="file" name="hinh_anh_file[]" class="form-control form-control-sm mb-1" accept="image/*" required>
                <input type="text" name="hinh_anh[${hinhAnhIndex}][mo_ta]" class="form-control form-control-sm mb-1" placeholder="Mô tả ảnh">
                <div class="form-check">
                    <input type="checkbox" name="hinh_anh[${hinhAnhIndex}][la_anh_chinh]" value="1" class="form-check-input">
                    <label class="form-check-label small">Ảnh chính</label>
                </div>
                <button type="button" class="btn btn-sm btn-danger mt-1" onclick="xoaHinhAnh(this)">
                    <i class="bi bi-trash"></i> Xóa
                </button>
                <hr>
            `;
            container.appendChild(div);
            hinhAnhIndex++;
        }
        
        function xoaHinhAnh(btn) {
            btn.closest('.image-item').remove();
        }
        
        // Auto add first image input if empty
        if (hinhAnhIndex === 0) {
            themHinhAnh();
        }
        
        // ===== LỊCH TRÌNH CHI TIẾT =====
        let lichTrinhIndex = <?php echo !empty($lichTrinhList) ? count($lichTrinhList) : 0; ?>;
        
        function themLichTrinh() {
            lichTrinhIndex++;
            const container = document.getElementById('lichTrinhContainer');
            const div = document.createElement('div');
            div.className = 'lich-trinh-item card mb-3';
            div.dataset.index = lichTrinhIndex;
            div.innerHTML = `
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px;">
                    <span style="font-weight: 600; color: var(--text-light);">
                        <i class="bi bi-calendar-day" style="color: var(--accent-gold);"></i> 
                        Ngày ${lichTrinhIndex + 1}
                    </span>
                    <button type="button" class="btn btn-sm btn-danger" onclick="xoaLichTrinh(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <input type="hidden" name="lich_trinh[${lichTrinhIndex}][ngay_thu]" value="${lichTrinhIndex + 1}">
                    
                    <div style="margin-bottom: 15px;">
                        <label class="form-label" style="font-size: 0.875rem;">Địa điểm</label>
                        <input type="text" name="lich_trinh[${lichTrinhIndex}][dia_diem]" 
                               class="form-control" placeholder="VD: Vịnh Hạ Long, Đảo Titop" required>
                    </div>
                    
                    <div style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.875rem;">Hoạt động</label>
                        <textarea name="lich_trinh[${lichTrinhIndex}][hoat_dong]" 
                                  class="form-control" rows="3" 
                                  placeholder="Mô tả hoạt động trong ngày..."></textarea>
                    </div>
                </div>
            `;
            container.appendChild(div);
            capNhatSoNgay();
        }
        
        function xoaLichTrinh(btn) {
            if (confirm('Bạn có chắc muốn xóa ngày này?')) {
                btn.closest('.lich-trinh-item').remove();
                capNhatSoNgay();
            }
        }
        
        function capNhatSoNgay() {
            const items = document.querySelectorAll('.lich-trinh-item');
            items.forEach((item, idx) => {
                const headerText = item.querySelector('.card-header span');
                headerText.innerHTML = `<i class="bi bi-calendar-day" style="color: var(--accent-gold);"></i> Ngày ${idx + 1}`;
                const hiddenInput = item.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.value = idx + 1;
                }
            });
        }
        
        // Auto add first day if empty
        if (lichTrinhIndex === 0) {
            themLichTrinh();
        }
    </script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
