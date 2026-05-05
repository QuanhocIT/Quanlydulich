<?php
$pageTitle = isset($entry) ? 'Sửa Nhật ký Tour' : 'Thêm Nhật ký Tour';
$currentPage = 'nhat_ky_tour';
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
    }
    .btn-secondary {
        background: rgba(108, 117, 125, 0.3);
        color: var(--text-light);
        border: 1px solid rgba(108, 117, 125, 0.5);
    }
    .btn-secondary:hover {
        background: rgba(108, 117, 125, 0.5);
    }
    .card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        backdrop-filter: blur(10px);
    }
    .card-header {
        background: rgba(0, 123, 255, 0.3);
        color: var(--text-light);
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .card-body {
        padding: 20px;
        color: var(--text-light);
    }
    .alert {
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .alert-success {
        background: rgba(40, 167, 69, 0.2);
        border: 1px solid rgba(40, 167, 69, 0.5);
        color: #5cb85c;
    }
    .alert-danger {
        background: rgba(220, 53, 69, 0.2);
        border: 1px solid rgba(220, 53, 69, 0.5);
        color: #dc3545;
    }
    .text-danger { color: #dc3545 !important; }
    .text-muted { color: var(--text-muted) !important; }
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
    .col-md-6, .col-md-8 { width: 66.666667%; }
    .mx-auto { margin-left: auto; margin-right: auto; }
    .mb-3 { margin-bottom: 1rem; }
    .d-flex { display: flex; }
    .justify-content-between { justify-content: space-between; }
    .flex-wrap { flex-wrap: wrap; }
    .gap-2 { gap: 0.5rem; }
    img {
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    @media (max-width: 768px) {
        .col-md-6, .col-md-8 {
            width: 100%;
        }
    }
</style>

<div style="padding: 20px; max-width: 900px; margin: 0 auto;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-journal-plus" style="color: var(--accent-gold);"></i> 
            <?php echo isset($entry) ? 'Sửa' : 'Thêm'; ?> Nhật ký Tour
        </h1>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h4 style="margin: 0; color: var(--text-light);">
                <i class="bi bi-journal-plus"></i> 
                <?php echo isset($entry) ? 'Sửa' : 'Thêm'; ?> Nhật ký Tour
            </h4>
        </div>
        <div class="card-body">
            <!-- Alerts -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <div><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                    <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <div><i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                    <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; font-size: 1.2rem; cursor: pointer;">&times;</button>
                </div>
            <?php endif; ?>

                        <form method="POST" action="index.php?act=admin/saveNhatKyTour" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $entry['id'] ?? ''; ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tour <span class="text-danger">*</span></label>
                                    <select class="form-select" name="tour_id" required>
                                        <option value="">-- Chọn tour --</option>
                                        <?php foreach($tours as $tour): ?>
                                        <option value="<?php echo $tour['tour_id']; ?>" 
                                                <?php echo (isset($entry) && $entry['tour_id'] == $tour['tour_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tour['ten_tour']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">HDV <span class="text-danger">*</span></label>
                                    <select class="form-select" name="nhan_su_id" required>
                                        <option value="">-- Chọn HDV --</option>
                                        <?php foreach($hdvList as $hdv): ?>
                                        <option value="<?php echo $hdv['nhan_su_id']; ?>" 
                                                <?php echo (isset($entry) && $entry['nhan_su_id'] == $hdv['nhan_su_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại nhật ký <span class="text-danger">*</span></label>
                                    <select class="form-select" name="loai_nhat_ky" id="loai_nhat_ky" required>
                                        <option value="hanh_trinh" <?php echo (isset($entry) && $entry['loai_nhat_ky']=='hanh_trinh')?'selected':''; ?>>📍 Hành trình</option>
                                        <option value="su_co" <?php echo (isset($entry) && $entry['loai_nhat_ky']=='su_co')?'selected':''; ?>>⚠️ Sự cố</option>
                                        <option value="phan_hoi" <?php echo (isset($entry) && $entry['loai_nhat_ky']=='phan_hoi')?'selected':''; ?>>💬 Phản hồi khách</option>
                                        <option value="hoat_dong" <?php echo (isset($entry) && $entry['loai_nhat_ky']=='hoat_dong')?'selected':''; ?>>🎯 Hoạt động nổi bật</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày ghi <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="ngay_ghi" 
                                           value="<?php echo isset($entry) ? date('Y-m-d', strtotime($entry['ngay_ghi'])) : date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tieu_de" 
                                       value="<?php echo isset($entry['tieu_de']) ? htmlspecialchars($entry['tieu_de']) : ''; ?>"
                                       placeholder="VD: Tham quan Vịnh Hạ Long" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nội dung chi tiết <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="noi_dung" rows="5" 
                                          placeholder="Mô tả chi tiết sự kiện, hoạt động, phản hồi của khách..." required><?php echo isset($entry['noi_dung']) ? htmlspecialchars($entry['noi_dung']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3" id="cach_xu_ly_group" style="display: <?php echo (isset($entry) && $entry['loai_nhat_ky']=='su_co')?'block':'none'; ?>;">
                                <label class="form-label">Cách xử lý (chỉ cho sự cố)</label>
                                <textarea class="form-control" name="cach_xu_ly" rows="3" 
                                          placeholder="Mô tả cách xử lý sự cố..."><?php echo isset($entry) ? htmlspecialchars($entry['cach_xu_ly'] ?? '') : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Hình ảnh (tối đa 5 ảnh)</label>
                                <input type="file" class="form-control" name="hinh_anh[]" 
                                       accept="image/*" multiple id="imageInput">
                                <small class="text-muted">Chọn nhiều ảnh bằng Ctrl + Click</small>
                                <div id="imagePreview" class="mt-3 d-flex flex-wrap gap-2"></div>
                                
                                <?php if (isset($entry) && !empty($entry['hinh_anh'])): ?>
                                    <?php 
                                    $images = json_decode($entry['hinh_anh'], true);
                                    if ($images && is_array($images) && !empty($images)):
                                    ?>
                                        <div class="mt-3">
                                            <label class="form-label">Hình ảnh hiện tại:</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($images as $img): ?>
                                                    <img src="<?php echo BASE_URL . $img; ?>" alt="Hình ảnh" 
                                                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 0.5rem; cursor: pointer;"
                                                         onclick="window.open('<?php echo BASE_URL . $img; ?>', '_blank')">
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="index.php?act=admin/quanLyNhatKyTour" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Quay lại
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Lưu nhật ký
                                </button>
                            </div>
                        </form>
        </div>
    </div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
        // Show/hide cách xử lý field
        document.getElementById('loai_nhat_ky').addEventListener('change', function() {
            var cachXuLyGroup = document.getElementById('cach_xu_ly_group');
            if (this.value === 'su_co') {
                cachXuLyGroup.style.display = 'block';
            } else {
                cachXuLyGroup.style.display = 'none';
            }
        });
        
        // Image preview
        document.getElementById('imageInput').addEventListener('change', function(e) {
            var preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            var files = Array.from(e.target.files).slice(0, 5);
            files.forEach(function(file) {
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100px';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '0.5rem';
                        img.style.margin = '0.25rem';
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
