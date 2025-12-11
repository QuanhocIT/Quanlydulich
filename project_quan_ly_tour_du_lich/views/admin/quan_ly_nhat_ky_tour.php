<?php
$pageTitle = 'Quản lý Nhật ký Tour';
$currentPage = 'nhatKyTour';
ob_start();
?>
<style>
        .diary-entry {
            background: rgba(45, 45, 45, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--accent-gold);
        }
        
        .entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .entry-type-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .type-hanh_trinh { background: rgba(21, 101, 192, 0.3); color: #64b5f6; }
        .type-su_co { background: rgba(198, 40, 40, 0.3); color: #ef5350; }
        .type-phan_hoi { background: rgba(106, 27, 154, 0.3); color: #ba68c8; }
        .type-hoat_dong { background: rgba(46, 125, 50, 0.3); color: #66bb6a; }
        
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
        
        .filter-card {
            background: rgba(45, 45, 45, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: rgba(45, 45, 45, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-gold);
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.5rem;
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
        .btn-secondary {
            background: rgba(108, 117, 125, 0.3);
            color: var(--text-light);
            border: 1px solid rgba(108, 117, 125, 0.5);
        }
        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.5);
        }
        .btn-outline-secondary {
            background: transparent;
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-outline-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        .btn-danger {
            background: rgba(220, 53, 69, 0.3);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        .btn-danger:hover {
            background: rgba(220, 53, 69, 0.5);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-info {
            background: rgba(0, 123, 255, 0.2);
            border: 1px solid rgba(0, 123, 255, 0.5);
            color: #4da3ff;
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
        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid rgba(255, 193, 7, 0.5);
            color: #ffc107;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .alert-link {
            color: #4da3ff;
        }
    </style>

<div style="padding: 20px;">
    <div class="page-header-section" style="margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text-light);">
            <i class="bi bi-journal-text" style="color: var(--accent-gold);"></i> Quản lý Nhật ký Tour
        </h1>
        <p style="color: var(--text-muted); margin: 8px 0 0 0;">Theo dõi diễn biến, sự cố, phản hồi và hoạt động của các tour</p>
    </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; color: inherit; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Thống kê -->
        <div class="stats-card">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['tong']; ?></div>
                        <div class="stat-label">Tổng nhật ký</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['hanh_trinh']; ?></div>
                        <div class="stat-label">📍 Hành trình</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['su_co']; ?></div>
                        <div class="stat-label">⚠️ Sự cố</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['phan_hoi']; ?></div>
                        <div class="stat-label">💬 Phản hồi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="filter-card">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="act" value="admin/quanLyNhatKyTour">
                <div class="col-md-3">
                    <label class="form-label"><strong>Tour:</strong></label>
                    <select name="tour_id" class="form-select">
                        <option value="">Tất cả tour</option>
                        <?php foreach ($tours as $tour): ?>
                            <option value="<?php echo $tour['tour_id']; ?>" <?php echo $tourId == $tour['tour_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tour['ten_tour']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><strong>HDV:</strong></label>
                    <select name="hdv_id" class="form-select">
                        <option value="">Tất cả HDV</option>
                        <?php foreach ($hdvList as $hdv): ?>
                            <option value="<?php echo $hdv['nhan_su_id']; ?>" <?php echo $hdvId == $hdv['nhan_su_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($hdv['ho_ten']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><strong>Loại:</strong></label>
                    <select name="loai_nhat_ky" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="hanh_trinh" <?php echo $loaiNhatKy == 'hanh_trinh' ? 'selected' : ''; ?>>📍 Hành trình</option>
                        <option value="su_co" <?php echo $loaiNhatKy == 'su_co' ? 'selected' : ''; ?>>⚠️ Sự cố</option>
                        <option value="phan_hoi" <?php echo $loaiNhatKy == 'phan_hoi' ? 'selected' : ''; ?>>💬 Phản hồi</option>
                        <option value="hoat_dong" <?php echo $loaiNhatKy == 'hoat_dong' ? 'selected' : ''; ?>>🎯 Hoạt động</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><strong>Từ ngày:</strong></label>
                    <input type="date" name="tu_ngay" class="form-control" value="<?php echo htmlspecialchars($tuNgay); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label"><strong>Đến ngày:</strong></label>
                    <input type="date" name="den_ngay" class="form-control" value="<?php echo htmlspecialchars($denNgay); ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Lọc
                    </button>
                    <a href="index.php?act=admin/quanLyNhatKyTour" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>

        <!-- Danh sách nhật ký -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($nhatKyList)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Chưa có nhật ký nào. 
                        <a href="index.php?act=admin/formNhatKyTour" class="alert-link">Thêm nhật ký đầu tiên</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($nhatKyList as $entry): ?>
                        <div class="diary-entry">
                            <div class="entry-header">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($entry['tieu_de'] ?? 'Không có tiêu đề'); ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($entry['ngay_ghi'])); ?>
                                        <?php if ($entry['ten_tour']): ?>
                                            | <i class="bi bi-compass"></i> <?php echo htmlspecialchars($entry['ten_tour']); ?>
                                        <?php endif; ?>
                                        <?php if ($entry['hdv_ten']): ?>
                                            | <i class="bi bi-person"></i> <?php echo htmlspecialchars($entry['hdv_ten']); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="entry-type-badge type-<?php echo $entry['loai_nhat_ky']; ?>">
                                        <?php
                                        $types = [
                                            'hanh_trinh' => '📍 Hành trình',
                                            'su_co' => '⚠️ Sự cố',
                                            'phan_hoi' => '💬 Phản hồi',
                                            'hoat_dong' => '🎯 Hoạt động'
                                        ];
                                        echo $types[$entry['loai_nhat_ky']] ?? $entry['loai_nhat_ky'];
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="entry-content">
                                <p><?php echo nl2br(htmlspecialchars($entry['noi_dung'])); ?></p>
                                
                                <?php if (!empty($entry['cach_xu_ly']) && $entry['loai_nhat_ky'] === 'su_co'): ?>
                                    <div class="alert alert-warning mt-3">
                                        <strong><i class="bi bi-lightbulb"></i> Cách xử lý:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($entry['cach_xu_ly'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($entry['hinh_anh'])): ?>
                                    <?php 
                                    $images = json_decode($entry['hinh_anh'], true);
                                    if ($images && is_array($images) && !empty($images)):
                                    ?>
                                        <div class="image-gallery">
                                            <?php foreach ($images as $img): ?>
                                                <img src="<?php echo BASE_URL . $img; ?>" alt="Hình ảnh" onclick="window.open('<?php echo BASE_URL . $img; ?>', '_blank')">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="entry-actions mt-3">
                                <a href="index.php?act=admin/chiTietNhatKyTour&id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Xem chi tiết
                                </a>
                                <a href="index.php?act=admin/formNhatKyTour&id=<?php echo $entry['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Sửa
                                </a>
                                <a href="index.php?act=admin/deleteNhatKyTour&id=<?php echo $entry['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa nhật ký này?');">
                                    <i class="bi bi-trash"></i> Xóa
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>

