<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký Tour - HDV</title>
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
        
        .diary-entry {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        .diary-entry:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .entry-type-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .type-hanh_trinh { background: #e3f2fd; color: #1565c0; }
        .type-su_co { background: #ffebee; color: #c62828; }
        .type-phan_hoi { background: #f3e5f5; color: #6a1b9a; }
        .type-hoat_dong { background: #e8f5e9; color: #2e7d32; }
        
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
        
        .add-entry-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            font-size: 1.5rem;
            box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.3);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .add-entry-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.75rem 1.5rem rgba(102, 126, 234, 0.4);
        }
        
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2.5rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--primary-color);
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
                        <i class="bi bi-journal-text"></i> Nhật ký Tour
                    </h3>
                    <?php if ($tour): ?>
                    <p class="mb-0 opacity-75"><?php echo htmlspecialchars($tour['ten_tour']); ?></p>
                    <?php endif; ?>
                </div>
                <a href="index.php?act=hdv/dashboard" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Trang chủ
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php 
        // Chỉ hiển thị lỗi nếu không phải lỗi về quyền
        if (isset($_SESSION['error'])): 
            $error_msg = $_SESSION['error'];
            unset($_SESSION['error']);
            // Bỏ qua thông báo lỗi về quyền
            if (stripos($error_msg, 'quyền') === false && stripos($error_msg, 'permission') === false):
        ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
            endif;
        endif; 
        ?>
        
        <!-- Tour Selector -->
        <?php if (empty($tour)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Chọn tour để xem nhật ký</h5>
                <select class="form-select" onchange="if(this.value) window.location.href='index.php?act=hdv/nhat_ky&tour_id=' + this.value">
                    <option value="">-- Chọn tour --</option>
                    <?php foreach($tours_list as $t): ?>
                    <option value="<?php echo $t['id']; ?>">
                        <?php echo htmlspecialchars($t['ten_tour']); ?> 
                        (<?php echo date('d/m/Y', strtotime($t['ngay_khoi_hanh'])); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Tour Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($tour['ten_tour']); ?></h5>
                        <div class="text-muted">
                            <i class="bi bi-calendar3"></i> 
                            <?php echo date('d/m/Y', strtotime($tour['ngay_khoi_hanh'])); ?>
                            -
                            <?php echo date('d/m/Y', strtotime($tour['ngay_ket_thuc'])); ?>
                        </div>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                        <i class="bi bi-plus-circle"></i> Thêm nhật ký
                    </button>
                </div>
            </div>
        </div>

        <!-- Diary Entries Timeline -->
        <div class="timeline">
            <?php if (!empty($nhat_ky_list)): ?>
                <?php foreach($nhat_ky_list as $entry): ?>
                <div class="timeline-item">
                    <div class="diary-entry">
                        <div class="entry-header">
                            <div>
                                <span class="entry-type-badge type-<?php echo $entry['loai_nhat_ky']; ?>">
                                    <?php 
                                    $types = [
                                        'hanh_trinh' => '📍 Hành trình',
                                        'su_co' => '⚠️ Sự cố',
                                        'phan_hoi' => '💬 Phản hồi khách',
                                        'hoat_dong' => '🎯 Hoạt động'
                                    ];
                                    echo $types[$entry['loai_nhat_ky']] ?? $entry['loai_nhat_ky'];
                                    ?>
                                </span>
                                <small class="text-muted ms-3">
                                    <i class="bi bi-clock"></i> 
                                    <?php echo date('d/m/Y H:i', strtotime($entry['ngay_ghi'])); ?>
                                </small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="index.php?act=hdv/nhat_ky&tour_id=<?php echo $tour['id']; ?>&edit_id=<?php echo $entry['id']; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="index.php?act=hdv/delete_nhat_ky" onsubmit="return confirm('Bạn có chắc muốn xóa nhật ký này?');" class="d-inline">
                                    <?php echo csrfField('hdv_form'); ?>
                                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                    <input type="hidden" name="tour_id" value="<?php echo $tour['id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <?php if (!empty($entry['tieu_de'])): ?>
                        <h5 class="mb-3"><?php echo htmlspecialchars($entry['tieu_de']); ?></h5>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <?php echo nl2br(htmlspecialchars($entry['noi_dung'] ?? '')); ?>
                        </div>
                        
                        <?php if (!empty($entry['cach_xu_ly']) && isset($entry['loai_nhat_ky']) && $entry['loai_nhat_ky'] === 'su_co'): ?>
                        <div class="alert alert-success mb-3">
                            <strong><i class="bi bi-check-circle"></i> Cách xử lý:</strong><br>
                            <?php echo nl2br(htmlspecialchars($entry['cach_xu_ly'])); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($entry['hinh_anh'])): ?>
                        <div class="image-gallery">
                            <?php 
                            $images = json_decode($entry['hinh_anh'], true);
                            if ($images && is_array($images)):
                                foreach($images as $img):
                            ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" 
                                 alt="Hình ảnh" 
                                 onclick="viewImage('<?php echo htmlspecialchars($img); ?>')">
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Chưa có nhật ký nào cho tour này. Hãy thêm nhật ký đầu tiên!
                </div>
            <?php endif; ?>
        </div>
        
        <?php endif; ?>
    </div>

    <!-- Add/Edit Entry Modal - Chỉ hiển thị khi có tour hợp lệ -->
    <?php if ($tour): ?>
    <div class="modal fade" id="addEntryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php if(isset($edit_entry)): ?>
                        <i class="bi bi-pencil"></i> Sửa nhật ký tour
                        <?php else: ?>
                        <i class="bi bi-journal-plus"></i> Thêm nhật ký tour
                        <?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?act=hdv/save_nhat_ky" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <input type="hidden" name="tour_id" value="<?php echo isset($tour['id']) ? $tour['id'] : ''; ?>" id="form_tour_id">
                    <input type="hidden" name="entry_id" value="<?php echo isset($edit_entry['id']) ? $edit_entry['id'] : ''; ?>">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại nhật ký <span class="text-danger">*</span></label>
                                <select class="form-select" name="loai_nhat_ky" id="loai_nhat_ky" required>
                                    <option value="hanh_trinh" <?php echo (isset($edit_entry) && $edit_entry['loai_nhat_ky']=='hanh_trinh')?'selected':''; ?>>📍 Hành trình</option>
                                    <option value="su_co" <?php echo (isset($edit_entry) && $edit_entry['loai_nhat_ky']=='su_co')?'selected':''; ?>>⚠️ Sự cố</option>
                                    <option value="phan_hoi" <?php echo (isset($edit_entry) && $edit_entry['loai_nhat_ky']=='phan_hoi')?'selected':''; ?>>💬 Phản hồi khách</option>
                                    <option value="hoat_dong" <?php echo (isset($edit_entry) && $edit_entry['loai_nhat_ky']=='hoat_dong')?'selected':''; ?>>🎯 Hoạt động nổi bật</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày ghi <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_ghi" 
                                       value="<?php echo isset($edit_entry) ? date('Y-m-d', strtotime($edit_entry['ngay_ghi'])) : date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tieu_de" 
                                   value="<?php echo isset($edit_entry['tieu_de']) ? htmlspecialchars($edit_entry['tieu_de']) : ''; ?>"
                                   placeholder="VD: Tham quan Vịnh Hạ Long" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung chi tiết <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="noi_dung" rows="5" 
                                      placeholder="Mô tả chi tiết sự kiện, hoạt động, phản hồi của khách..." required><?php echo isset($edit_entry['noi_dung']) ? htmlspecialchars($edit_entry['noi_dung']) : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3" id="cach_xu_ly_group" style="display: <?php echo (isset($edit_entry) && $edit_entry['loai_nhat_ky']=='su_co')?'block':'none'; ?>;">
                            <label class="form-label">Cách xử lý (chỉ cho sự cố)</label>
                            <textarea class="form-control" name="cach_xu_ly" rows="3" 
                                      placeholder="Mô tả cách xử lý sự cố..."><?php echo isset($edit_entry) ? htmlspecialchars($edit_entry['cach_xu_ly'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh (tối đa 5 ảnh)</label>
                            <input type="file" class="form-control" name="hinh_anh[]" 
                                   accept="image/*" multiple id="imageInput">
                            <small class="text-muted">Chọn nhiều ảnh bằng Ctrl + Click</small>
                            <div id="imagePreview" class="image-gallery mt-3"></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Lưu nhật ký
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Image Viewer Modal -->
    <div class="modal fade" id="imageViewerModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-0">
                    <img src="" id="viewerImage" style="width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
        
        // View image
        function viewImage(src) {
            document.getElementById('viewerImage').src = src;
            new bootstrap.Modal(document.getElementById('imageViewerModal')).show();
        }
        
        // Validate form before submit
        function validateForm() {
            var tourId = document.getElementById('form_tour_id').value;
            if (!tourId || tourId === '') {
                alert('Lỗi: Không tìm thấy thông tin tour. Vui lòng thử lại.');
                return false;
            }
            return true;
        }
        
        // Auto show modal when edit_id is present
        <?php if(isset($edit_entry)): ?>
        window.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('addEntryModal');
            if (modal) {
                new bootstrap.Modal(modal).show();
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>

