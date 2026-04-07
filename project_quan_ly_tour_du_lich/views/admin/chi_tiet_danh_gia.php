<?php
$pageTitle = 'Chi tiết Đánh giá';
$currentPage = 'danhGia';
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

    .review-detail-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        padding: 30px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .rating-large {
        font-size: 48px;
        color: var(--accent-gold);
        font-weight: 700;
    }

    .info-card {
        background: rgba(45, 45, 45, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .info-card-header {
        background: rgba(212, 175, 55, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        color: var(--accent-gold);
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
    }

    .info-card-body {
        padding: 25px;
    }

    .response-section {
        background: rgba(25, 135, 84, 0.1);
        border: 1px solid rgba(25, 135, 84, 0.3);
        border-radius: 2px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-light);
        font-size: 13px;
        font-weight: 600;
    }

    .form-group textarea {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        padding: 12px 10px;
        font-size: 13px;
        border-radius: 2px;
        transition: all 0.3s;
        width: 100%;
        font-family: inherit;
        resize: vertical;
        min-height: 120px;
    }

    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group textarea:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        border-color: var(--accent-gold);
    }

    .badge {
        padding: 6px 12px;
        border-radius: 2px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-light {
        background: rgba(255, 255, 255, 0.2);
        color: var(--text-light);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .alert {
        padding: 15px 20px;
        border-radius: 2px;
        margin-bottom: 20px;
        border: 1px solid;
    }

    .alert-success {
        background: rgba(25, 135, 84, 0.1);
        border-color: rgba(25, 135, 84, 0.3);
        color: #198754;
    }
</style>

<!-- Page Header -->
<div class="page-header-section">
    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
        <div>
            <h1>⭐ Chi tiết Đánh giá</h1>
            <p style="color: var(--text-muted); margin-top: 10px;">Xem và phản hồi đánh giá từ khách hàng</p>
        </div>
        <div>
            <a href="index.php?act=admin/danhGia" class="btn btn-secondary">
                ← Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Alerts -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        ✓ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<!-- Thông tin đánh giá -->
<div class="review-detail-card">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <div>
            <h3 style="color: var(--accent-gold); margin-bottom: 20px; font-size: 20px;">
                💬 Chi tiết Đánh giá
            </h3>
            <hr style="border-color: rgba(255, 255, 255, 0.1); margin-bottom: 20px;">
            
            <div style="margin-bottom: 20px;">
                <h5 style="color: var(--accent-gold); font-size: 14px; margin-bottom: 10px;">👤 Thông tin khách hàng</h5>
                <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                    <strong>Họ tên:</strong> <?php echo htmlspecialchars($danhGia['ten_khach_hang']); ?>
                </p>
                <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                    <strong>Email:</strong> <?php echo htmlspecialchars($danhGia['email_khach_hang']); ?>
                </p>
                <?php if (!empty($danhGia['dien_thoai_khach_hang'])): ?>
                    <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                        <strong>Điện thoại:</strong> <?php echo htmlspecialchars($danhGia['dien_thoai_khach_hang']); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h5 style="color: var(--accent-gold); font-size: 14px; margin-bottom: 10px;">ℹ️ Đối tượng đánh giá</h5>
                <?php
                $loaiText = [
                    'Tour' => 'Tour',
                    'NhaCungCap' => 'Nhà cung cấp',
                    'NhanSu' => 'Nhân sự'
                ];
                ?>
                <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                    <strong>Loại:</strong> 
                    <span class="badge badge-light">
                        <?php echo $loaiText[$danhGia['loai_danh_gia']] ?? $danhGia['loai_danh_gia']; ?>
                    </span>
                </p>
                
                <?php if ($danhGia['loai_danh_gia'] === 'Tour' && !empty($danhGia['ten_tour'])): ?>
                    <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                        <strong>Tour:</strong> <?php echo htmlspecialchars($danhGia['ten_tour']); ?>
                    </p>
                <?php elseif ($danhGia['loai_danh_gia'] === 'NhaCungCap' && !empty($danhGia['ten_nha_cung_cap'])): ?>
                    <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                        <strong>Nhà cung cấp:</strong> <?php echo htmlspecialchars($danhGia['ten_nha_cung_cap']); ?>
                    </p>
                    <?php if (!empty($danhGia['loai_dich_vu'])): ?>
                        <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                            <strong>Loại dịch vụ:</strong> <?php echo htmlspecialchars($danhGia['loai_dich_vu']); ?>
                        </p>
                    <?php endif; ?>
                <?php elseif ($danhGia['loai_danh_gia'] === 'NhanSu' && !empty($danhGia['ten_nhan_su'])): ?>
                    <p style="margin: 5px 0; color: var(--text-light); font-size: 13px;">
                        <strong>Nhân sự:</strong> <?php echo htmlspecialchars($danhGia['ten_nhan_su']); ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div>
                <h5 style="color: var(--accent-gold); font-size: 14px; margin-bottom: 10px;">📅 Thời gian</h5>
                <p style="margin: 0; color: var(--text-light); font-size: 13px;">
                    <strong>Ngày đánh giá:</strong> 
                    <?php echo date('d/m/Y H:i', strtotime($danhGia['ngay_danh_gia'])); ?>
                </p>
            </div>
        </div>
        
        <div style="text-align: center;">
            <h5 style="color: var(--accent-gold); margin-bottom: 15px; font-size: 14px;">Điểm đánh giá</h5>
            <div class="rating-large">
                <?php echo $danhGia['diem']; ?> ⭐
            </div>
            <div style="margin-top: 15px;">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <span style="font-size: 24px; color: <?php echo $i <= $danhGia['diem'] ? 'var(--accent-gold)' : 'var(--text-muted)'; ?>;">
                        ⭐
                    </span>
                <?php endfor; ?>
            </div>
            
            <?php if (!empty($danhGia['tieu_chi'])): ?>
                <div style="margin-top: 15px;">
                    <span class="badge badge-light">
                        <?php echo htmlspecialchars($danhGia['tieu_chi']); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Nội dung đánh giá -->
<div class="info-card">
    <div class="info-card-header">
        💬 Nội dung đánh giá
    </div>
    <div class="info-card-body">
        <p style="font-size: 14px; line-height: 1.8; color: var(--text-light); margin: 0;">
            <?php echo nl2br(htmlspecialchars($danhGia['noi_dung'])); ?>
        </p>
    </div>
</div>

<!-- Phản hồi -->
<div class="info-card">
    <div class="info-card-header" style="background: rgba(25, 135, 84, 0.2); color: #198754;">
        💬 Phản hồi của Admin
    </div>
    <div class="info-card-body">
        <?php if (!empty($danhGia['phan_hoi_admin'])): ?>
            <div class="response-section">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <strong style="color: #198754;">Đã phản hồi</strong>
                    <?php if (!empty($danhGia['ngay_phan_hoi'])): ?>
                        <small style="color: var(--text-muted); font-size: 11px;">
                            <?php echo date('d/m/Y H:i', strtotime($danhGia['ngay_phan_hoi'])); ?>
                        </small>
                    <?php endif; ?>
                </div>
                <p style="margin: 0; color: var(--text-light); line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($danhGia['phan_hoi_admin'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="index.php?act=admin/danhGia/traLoi">
            <input type="hidden" name="id" value="<?php echo $danhGia['danh_gia_id']; ?>">
            <div class="form-group">
                <label>
                    <?php echo !empty($danhGia['phan_hoi_admin']) ? 'Cập nhật phản hồi' : 'Thêm phản hồi'; ?>
                </label>
                <textarea name="phan_hoi_admin" class="textarea" rows="5" required><?php echo htmlspecialchars($danhGia['phan_hoi_admin'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                📤 Gửi phản hồi
            </button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
