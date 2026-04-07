<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ HDV - <?php echo htmlspecialchars($hdv_info['ho_ten'] ?? 'HDV'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$status = $hdv_info['trang_thai_lam_viec'] ?? '';
$statusText = [
    'SanSang' => 'Sẵn sàng',
    'DangBan' => 'Đang bận',
    'NghiPhep' => 'Nghỉ phép',
    'TamNghi' => 'Tạm nghỉ',
];
$statusClass = match ($status) {
    'SanSang' => 'primary',
    'DangBan' => 'warning',
    'NghiPhep', 'TamNghi' => 'danger',
    default => 'neutral',
};
?>
<div class="profile-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <a href="index.php?act=hdv/dashboard" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal">
                <i class="bi bi-pencil"></i> Cập nhật thông tin
            </button>
        </div>
        <div class="row align-items-center g-4">
            <div class="col-auto">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($hdv_info['ho_ten'] ?? 'N', 0, 1)); ?>
                </div>
            </div>
            <div class="col">
                <h3 class="mb-2"><?php echo htmlspecialchars($hdv_info['ho_ten'] ?? 'N/A'); ?></h3>
                <p class="mb-3">Hướng dẫn viên du lịch với hồ sơ năng lực, chuyên tuyến và thông tin liên hệ được trình bày gọn gàng để bạn chủ động cập nhật.</p>
                <div class="hdv-pill-list">
                    <span class="hdv-pill"><i class="bi bi-person-badge"></i> Mã nhân sự: <?php echo htmlspecialchars($hdv_info['nhan_su_id'] ?? 'N/A'); ?></span>
                    <span class="hdv-pill"><i class="bi bi-briefcase"></i> Loại HDV: <?php echo htmlspecialchars($hdv_info['loai_hdv'] ?? 'N/A'); ?></span>
                    <span class="hdv-soft-badge <?php echo $statusClass; ?>"><i class="bi bi-activity"></i> <?php echo $statusText[$status] ?? 'Chưa cập nhật'; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="hdv-grid cols-3 mb-4">
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-star-fill"></i></div>
                <div>
                    <div class="hdv-kpi-label">Đánh giá trung bình</div>
                    <div class="hdv-kpi-value"><?php echo number_format((float)($hdv_info['danh_gia_tb'] ?? 0), 1); ?></div>
                    <div class="hdv-kpi-meta">Dựa trên lịch sử tour đã dẫn</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-map"></i></div>
                <div>
                    <div class="hdv-kpi-label">Số tour đã dẫn</div>
                    <div class="hdv-kpi-value"><?php echo (int)($hdv_info['so_tour_da_dan'] ?? 0); ?></div>
                    <div class="hdv-kpi-meta">Kinh nghiệm thể hiện qua số tour hoàn tất</div>
                </div>
            </div>
        </div>
        <div class="hdv-card">
            <div class="hdv-kpi">
                <div class="hdv-kpi-icon"><i class="bi bi-translate"></i></div>
                <div>
                    <div class="hdv-kpi-label">Ngôn ngữ</div>
                    <div class="hdv-kpi-value" style="font-size:1.15rem"><?php echo htmlspecialchars($hdv_info['ngon_ngu'] ?? 'N/A'); ?></div>
                    <div class="hdv-kpi-meta">Phục vụ điều hành và phân công phù hợp</div>
                </div>
            </div>
        </div>
    </div>

    <div class="hdv-grid cols-2">
        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Thông tin cá nhân</h4>
                    <p class="hdv-card-subtitle">Dữ liệu liên hệ và nhận diện cơ bản</p>
                </div>
            </div>
            <div class="hdv-card-body">
                <div class="hdv-list">
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Họ tên</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($hdv_info['ho_ten'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Email</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($hdv_info['email'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Số điện thoại</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($hdv_info['so_dien_thoai'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="hdv-card">
            <div class="hdv-card-header">
                <div>
                    <h4 class="hdv-card-title">Năng lực nghề nghiệp</h4>
                    <p class="hdv-card-subtitle">Thông tin chuyên môn phục vụ công tác điều hành</p>
                </div>
            </div>
            <div class="hdv-card-body">
                <div class="hdv-list">
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Chuyên tuyến</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($hdv_info['chuyen_tuyen'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Chứng chỉ</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($hdv_info['chung_chi'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Sức khỏe</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($hdv_info['suc_khoe'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="hdv-list-item">
                        <h5 class="hdv-list-title">Kinh nghiệm</h5>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($hdv_info['kinh_nghiem'] ?? 'N/A')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="index.php?act=hdv/update_profile">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Cập nhật thông tin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($hdv_info['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="so_dien_thoai" value="<?php echo htmlspecialchars($hdv_info['so_dien_thoai'] ?? ''); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Chứng chỉ</label>
                            <input type="text" class="form-control" name="chung_chi" value="<?php echo htmlspecialchars($hdv_info['chung_chi'] ?? ''); ?>" placeholder="VD: Chứng chỉ HDV quốc gia, TOEIC 850...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngôn ngữ</label>
                            <input type="text" class="form-control" name="ngon_ngu" value="<?php echo htmlspecialchars($hdv_info['ngon_ngu'] ?? ''); ?>" placeholder="VD: Tiếng Anh, Tiếng Nhật, Tiếng Trung...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sức khỏe</label>
                            <input type="text" class="form-control" name="suc_khoe" value="<?php echo htmlspecialchars($hdv_info['suc_khoe'] ?? ''); ?>" placeholder="VD: Tốt, Bình thường...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Kinh nghiệm</label>
                            <textarea class="form-control" name="kinh_nghiem" rows="5" placeholder="Mô tả các tuyến đã dẫn, kỹ năng nổi bật hoặc kinh nghiệm vận hành thực tế..."><?php echo htmlspecialchars($hdv_info['kinh_nghiem'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
