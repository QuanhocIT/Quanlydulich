<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phản hồi đánh giá - HDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
    <style>
        .rating-stars { display: inline-flex; gap: 5px; font-size: 24px; cursor: pointer; }
        .rating-stars i { color: #ddd; transition: color 0.2s; }
        .rating-stars i.active, .rating-stars i:hover { color: #ffc107; }
        .rating-display { display: inline-flex; gap: 3px; font-size: 18px; }
        .rating-display i { color: #ffc107; }
        .rating-display i.bi-star { color: #ddd; }
        .feedback-card { border-left: 4px solid; transition: all 0.3s; }
        .feedback-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .feedback-card.rating-5 { border-left-color: #28a745; }
        .feedback-card.rating-4 { border-left-color: #6c757d; }
        .feedback-card.rating-3 { border-left-color: #ffc107; }
        .feedback-card.rating-2 { border-left-color: #fd7e14; }
        .feedback-card.rating-1 { border-left-color: #dc3545; }
        .stats-card { border-radius: 10px; padding: 15px; text-align: center; color: white; }
        .stats-card.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stats-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stats-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stats-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .image-preview { display: inline-block; margin: 5px; }
        .image-preview img { max-width: 100px; max-height: 100px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>

    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="bi bi-chat-left-quote"></i> Phản hồi đánh giá dịch vụ</h3>
                    <?php if ($tour): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                        <i class="bi bi-plus-circle"></i> Gửi phản hồi mới
                    </button>
                    <?php endif; ?>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars((string)($_SESSION['success'] ?? ''), ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars((string)($_SESSION['error'] ?? ''), ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (!$tour): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-check" style="font-size: 48px; color: #6c757d;"></i>
                        <h5 class="mt-3">Chọn tour để xem và gửi phản hồi</h5>
                        <div class="mt-4">
                            <select class="form-select w-50 mx-auto" onchange="if(this.value) window.location.href='index.php?act=hdv/phan_hoi&tour_id='+this.value">
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
                </div>
                <?php else: ?>

                <!-- Tour hiện tại -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1"><i class="bi bi-briefcase"></i> <?php echo htmlspecialchars($tour['ten_tour']); ?></h5>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($tour['ngay_khoi_hanh'])); ?> - 
                                    <?php echo date('d/m/Y', strtotime($tour['ngay_ket_thuc'])); ?>
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <select class="form-select w-auto d-inline-block" onchange="if(this.value) window.location.href='index.php?act=hdv/phan_hoi&tour_id='+this.value">
                                    <option value="">Đổi tour...</option>
                                    <?php foreach($tours_list as $t): ?>
                                    <option value="<?php echo $t['id']; ?>" <?php echo $t['id'] == $tour_id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t['ten_tour']); ?> 
                                        (<?php echo date('d/m/Y', strtotime($t['ngay_khoi_hanh'])); ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card primary">
                            <h2 class="mb-0"><?php echo $stats['tong'] ?? 0; ?></h2>
                            <small>Tổng phản hồi</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card info">
                            <h2 class="mb-0"><?php echo $stats['moi'] ?? 0; ?></h2>
                            <small>Mới gửi</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card warning">
                            <h2 class="mb-0"><?php echo $stats['da_xem'] ?? 0; ?></h2>
                            <small>Đã xem</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card success">
                            <h2 class="mb-0"><?php echo number_format($stats['diem_tb'] ?? 0, 1); ?> <i class="bi bi-star-fill"></i></h2>
                            <small>Điểm trung bình</small>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo $tour_id; ?>" 
                               class="btn btn-sm <?php echo !$loai_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">Tất cả</a>
                            <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo $tour_id; ?>&loai=tour" 
                               class="btn btn-sm <?php echo $loai_filter == 'tour' ? 'btn-primary' : 'btn-outline-primary'; ?>">🗺️ Tour</a>
                            <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo $tour_id; ?>&loai=khach_san" 
                               class="btn btn-sm <?php echo $loai_filter == 'khach_san' ? 'btn-primary' : 'btn-outline-primary'; ?>">🏨 Khách sạn</a>
                            <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo $tour_id; ?>&loai=nha_hang" 
                               class="btn btn-sm <?php echo $loai_filter == 'nha_hang' ? 'btn-primary' : 'btn-outline-primary'; ?>">🍽️ Nhà hàng</a>
                            <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo $tour_id; ?>&loai=van_chuyen" 
                               class="btn btn-sm <?php echo $loai_filter == 'van_chuyen' ? 'btn-primary' : 'btn-outline-primary'; ?>">🚌 Vận chuyển</a>
                            <a href="index.php?act=hdv/phan_hoi&tour_id=<?php echo $tour_id; ?>&loai=nha_cung_cap" 
                               class="btn btn-sm <?php echo $loai_filter == 'nha_cung_cap' ? 'btn-primary' : 'btn-outline-primary'; ?>">👥 Nhà cung cấp</a>
                        </div>
                    </div>
                </div>

                <!-- Danh sách phản hồi -->
                <?php if (empty($phan_hoi_list)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #6c757d;"></i>
                        <p class="mt-3 text-muted">Chưa có phản hồi nào. Hãy gửi phản hồi đầu tiên!</p>
                    </div>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php 
                    $loai_icons = [
                        'tour' => 'bi-map', 'khach_san' => 'bi-building', 'nha_hang' => 'bi-shop',
                        'van_chuyen' => 'bi-bus-front', 'nha_cung_cap' => 'bi-people', 'khac' => 'bi-question-circle'
                    ];
                    foreach($phan_hoi_list as $ph): 
                        $hinh_anh = json_decode($ph['hinh_anh'] ?? '[]', true);
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card feedback-card rating-<?php echo $ph['diem_danh_gia']; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary">
                                            <i class="bi <?php echo $loai_icons[$ph['loai_danh_gia']] ?? 'bi-question-circle'; ?>"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $ph['loai_danh_gia'])); ?>
                                        </span>
                                        <span class="badge bg-<?php echo $ph['trang_thai'] == 'moi' ? 'info' : ($ph['trang_thai'] == 'da_xem' ? 'warning' : 'success'); ?> ms-2">
                                            <?php echo str_replace('_', ' ', ucfirst($ph['trang_thai'])); ?>
                                        </span>
                                    </div>
                                    <div class="rating-display">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $ph['diem_danh_gia'] ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <h6 class="mb-1"><?php echo htmlspecialchars($ph['tieu_de']); ?></h6>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-building"></i> <?php echo htmlspecialchars($ph['ten_doi_tuong']); ?>
                                </p>
                                
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($ph['noi_dung'])); ?></p>
                                
                                <?php if ($ph['diem_manh']): ?>
                                <div class="mb-2">
                                    <strong class="text-success"><i class="bi bi-check-circle"></i> Điểm mạnh:</strong>
                                    <p class="small mb-0"><?php echo nl2br(htmlspecialchars($ph['diem_manh'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($ph['diem_yeu']): ?>
                                <div class="mb-2">
                                    <strong class="text-warning"><i class="bi bi-exclamation-circle"></i> Điểm yếu:</strong>
                                    <p class="small mb-0"><?php echo nl2br(htmlspecialchars($ph['diem_yeu'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($ph['de_xuat']): ?>
                                <div class="mb-2">
                                    <strong class="text-primary"><i class="bi bi-lightbulb"></i> Đề xuất:</strong>
                                    <p class="small mb-0"><?php echo nl2br(htmlspecialchars($ph['de_xuat'])); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($hinh_anh) && is_array($hinh_anh)): ?>
                                <div class="mb-2">
                                    <strong>Hình ảnh:</strong><br>
                                    <?php foreach($hinh_anh as $img): ?>
                                    <div class="image-preview">
                                        <img src="<?php echo $img; ?>" alt="Ảnh" onclick="window.open(this.src)">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> 
                                        <?php echo date('d/m/Y H:i', strtotime($ph['ngay_tao'])); ?>
                                    </small>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick='editFeedback(<?php echo json_encode($ph, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="index.php?act=hdv/delete_phan_hoi" onsubmit="return confirm('Xác nhận xóa phản hồi này?');" class="d-inline">
                                            <?php echo csrfField('hdv_form'); ?>
                                            <input type="hidden" name="id" value="<?php echo $ph['id']; ?>">
                                            <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <?php if ($ph['ghi_chu_xu_ly']): ?>
                                <div class="alert alert-info mt-2 mb-0">
                                    <strong><i class="bi bi-info-circle"></i> Phản hồi từ quản lý:</strong>
                                    <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($ph['ghi_chu_xu_ly'])); ?></p>
                                    <?php if ($ph['ten_nguoi_xu_ly']): ?>
                                    <small class="text-muted">- <?php echo htmlspecialchars($ph['ten_nguoi_xu_ly']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="index.php?act=hdv/save_phan_hoi" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="feedback_id">
                    <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                    
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-chat-left-quote"></i> Gửi phản hồi đánh giá</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại đánh giá <span class="text-danger">*</span></label>
                                <select class="form-select" name="loai_danh_gia" id="loai_danh_gia" required>
                                    <option value="">-- Chọn loại --</option>
                                    <option value="tour">🗺️ Tour</option>
                                    <option value="khach_san">🏨 Khách sạn</option>
                                    <option value="nha_hang">🍽️ Nhà hàng</option>
                                    <option value="van_chuyen">🚌 Vận chuyển</option>
                                    <option value="nha_cung_cap">👥 Nhà cung cấp</option>
                                    <option value="khac">📌 Khác</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên đối tượng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ten_doi_tuong" id="ten_doi_tuong" 
                                       placeholder="VD: Khách sạn ABC, Nhà hàng XYZ..." required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Đánh giá <span class="text-danger">*</span></label>
                                <div class="rating-stars" id="ratingStars">
                                    <i class="bi bi-star" data-rating="1"></i>
                                    <i class="bi bi-star" data-rating="2"></i>
                                    <i class="bi bi-star" data-rating="3"></i>
                                    <i class="bi bi-star" data-rating="4"></i>
                                    <i class="bi bi-star" data-rating="5"></i>
                                </div>
                                <input type="hidden" name="diem_danh_gia" id="diem_danh_gia" value="5" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="tieu_de" id="tieu_de" required>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="noi_dung" id="noi_dung" rows="3" required></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Điểm mạnh</label>
                                <textarea class="form-control" name="diem_manh" id="diem_manh" rows="2"></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Điểm yếu</label>
                                <textarea class="form-control" name="diem_yeu" id="diem_yeu" rows="2"></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Đề xuất</label>
                                <textarea class="form-control" name="de_xuat" id="de_xuat" rows="2"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control" name="hinh_anh[]" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Gửi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
        const stars = document.querySelectorAll('#ratingStars i');
        const ratingInput = document.getElementById('diem_danh_gia');
        
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;
                updateStars(rating);
            });
            star.addEventListener('mouseenter', function() {
                updateStars(this.getAttribute('data-rating'));
            });
        });
        
        document.getElementById('ratingStars').addEventListener('mouseleave', function() {
            updateStars(ratingInput.value);
        });
        
        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('bi-star');
                    star.classList.add('bi-star-fill', 'active');
                } else {
                    star.classList.remove('bi-star-fill', 'active');
                    star.classList.add('bi-star');
                }
            });
        }
        
        function editFeedback(data) {
            document.getElementById('feedback_id').value = data.id;
            document.getElementById('loai_danh_gia').value = data.loai_danh_gia;
            document.getElementById('ten_doi_tuong').value = data.ten_doi_tuong;
            document.getElementById('tieu_de').value = data.tieu_de;
            document.getElementById('noi_dung').value = data.noi_dung;
            document.getElementById('diem_manh').value = data.diem_manh || '';
            document.getElementById('diem_yeu').value = data.diem_yeu || '';
            document.getElementById('de_xuat').value = data.de_xuat || '';
            ratingInput.value = data.diem_danh_gia;
            updateStars(data.diem_danh_gia);
            new bootstrap.Modal(document.getElementById('feedbackModal')).show();
        }
        
        document.getElementById('feedbackModal').addEventListener('hidden.bs.modal', function() {
            this.querySelector('form').reset();
            document.getElementById('feedback_id').value = '';
            updateStars(5);
        });
        
        updateStars(5);
    </script>
</body>
</html>



