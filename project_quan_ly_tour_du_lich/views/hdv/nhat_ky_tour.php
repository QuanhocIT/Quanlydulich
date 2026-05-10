<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhật ký tour - HDV</title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/hdv.css">
</head>
<body class="hdv-body">
<?php include __DIR__ . '/partials/hdv_nav.php'; ?>
<?php
$entryEditing = $entryEditing ?? [];
$isEditing = !empty($entryEditing);
$formTourId = $isEditing
    ? (int)($entryEditing['tour_id'] ?? 0)
    : (int)($selectedTourId ?? ($lichKhoiHanhList[0]['tour_id'] ?? 0));
$getField = static function ($key) use ($entryEditing) {
    return htmlspecialchars($entryEditing[$key] ?? '');
};
$entriesCount = count($nhatKyList ?? []);
?>
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="mb-1"><i class="bi bi-journal-text"></i> Nhật ký tour của tôi</h3>
                <p class="mb-0 opacity-75">Ghi lại diễn biến vận hành, sự cố, phản hồi khách và những điều cần bàn giao lại cho đội điều hành.</p>
            </div>
            <a href="index.php?act=hdv/lichLamViec" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Quay lại lịch làm việc
            </a>
        </div>
    </div>
</div>

<div class="container hdv-shell">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($lichKhoiHanhList)): ?>
        <div class="hdv-grid cols-3 mb-4">
            <div class="hdv-card">
                <div class="hdv-kpi">
                    <div class="hdv-kpi-icon"><i class="bi bi-bookmark-check"></i></div>
                    <div>
                        <div class="hdv-kpi-label">Nhật ký hiện có</div>
                        <div class="hdv-kpi-value"><?php echo $entriesCount; ?></div>
                        <div class="hdv-kpi-meta">Số bản ghi theo tour đang chọn</div>
                    </div>
                </div>
            </div>
            <div class="hdv-card">
                <div class="hdv-kpi">
                    <div class="hdv-kpi-icon"><i class="bi bi-diagram-3"></i></div>
                    <div>
                        <div class="hdv-kpi-label">Tour phụ trách</div>
                        <div class="hdv-kpi-value"><?php echo count($lichKhoiHanhList); ?></div>
                        <div class="hdv-kpi-meta">Số tour được phép ghi nhật ký</div>
                    </div>
                </div>
            </div>
            <div class="hdv-card">
                <div class="hdv-kpi">
                    <div class="hdv-kpi-icon"><i class="bi bi-pencil-square"></i></div>
                    <div>
                        <div class="hdv-kpi-label">Chế độ biểu mẫu</div>
                        <div class="hdv-kpi-value" style="font-size:1.2rem"><?php echo $isEditing ? 'Chỉnh sửa' : 'Tạo mới'; ?></div>
                        <div class="hdv-kpi-meta">Bạn có thể ghi nhanh hoặc cập nhật bản ghi cũ</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hdv-card mb-4">
            <div class="hdv-card-body hdv-hero">
                <span class="hdv-hero-badge"><i class="bi bi-feather"></i> Tour journal</span>
                <h1 class="hdv-hero-title">Một nhật ký tốt giúp đội vận hành hiểu chính xác chuyện gì đã diễn ra trên tour, thay vì phải đoán.</h1>
                <p class="hdv-hero-text">
                    Hãy ghi thật ngắn gọn nhưng đủ ý: hoạt động nổi bật, sự cố, cách xử lý, phản hồi khách hàng và các lưu ý cho các bộ phận tiếp nhận sau tour.
                </p>
            </div>
        </div>

        <div class="hdv-card mb-4">
            <div class="hdv-card-body">
                <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <input type="hidden" name="act" value="hdv/nhatKyTour">
                    <div class="col-lg-8">
                        <label for="tour_id" class="form-label">Chọn tour</label>
                        <select name="tour_id" id="tour_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả tour tôi phụ trách</option>
                            <?php foreach ($lichKhoiHanhList as $lich): ?>
                                <option value="<?php echo (int)$lich['tour_id']; ?>" <?php echo ((int)($selectedTourId ?? 0) === (int)$lich['tour_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lich['ten_tour'] ?? 'Tour'); ?>
                                    (<?php echo !empty($lich['ngay_khoi_hanh']) ? date('d/m/Y', strtotime($lich['ngay_khoi_hanh'])) : 'N/A'; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <div class="hdv-pill"><i class="bi bi-info-circle"></i> Đổi tour để lọc lịch sử ngay</div>
                    </div>
                </form>
            </div>
        </div>

        <div class="hdv-grid cols-2">
            <div class="hdv-card">
                <div class="hdv-card-header">
                    <div>
                        <h4 class="hdv-card-title"><?php echo $isEditing ? 'Cập nhật nhật ký' : 'Ghi nhật ký mới'; ?></h4>
                        <p class="hdv-card-subtitle">Biểu mẫu chuẩn để lưu lại diễn biến thực tế của tour</p>
                    </div>
                </div>
                <div class="hdv-card-body">
                    <form method="POST" action="index.php?act=hdv/nhatKyTour" class="row g-3">
                        <?php echo csrfField('hdv_form'); ?>
                        <input type="hidden" name="journal_action" value="<?php echo $isEditing ? 'update' : 'create'; ?>">
                        <?php if ($isEditing): ?>
                            <input type="hidden" name="entry_id" value="<?php echo (int)$entryEditing['id']; ?>">
                        <?php endif; ?>

                        <div class="col-md-7">
                            <label for="form_tour_id" class="form-label">Tour</label>
                            <select name="tour_id" id="form_tour_id" class="form-select" required>
                                <?php foreach ($lichKhoiHanhList as $lich): ?>
                                    <option value="<?php echo (int)$lich['tour_id']; ?>" <?php echo ($formTourId === (int)$lich['tour_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lich['ten_tour'] ?? 'Tour'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="ngay_ghi" class="form-label">Ngày ghi</label>
                            <input type="date" name="ngay_ghi" id="ngay_ghi" class="form-control" value="<?php echo htmlspecialchars($entryEditing['ngay_ghi'] ?? date('Y-m-d')); ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="tieu_de" class="form-label">Tiêu đề / Tóm tắt</label>
                            <input type="text" name="tieu_de" id="tieu_de" class="form-control" value="<?php echo $getField('tieu_de'); ?>" placeholder="VD: Đón đoàn thuận lợi, khách phản hồi tốt, phát sinh trễ xe...">
                        </div>
                        <div class="col-12">
                            <label for="hoat_dong" class="form-label">Hoạt động nổi bật</label>
                            <textarea name="hoat_dong" id="hoat_dong" rows="3" class="form-control" placeholder="Tóm tắt những điểm nổi bật trong ngày..."><?php echo $getField('hoat_dong'); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="su_kien" class="form-label">Sự kiện / Sự cố</label>
                            <textarea name="su_kien" id="su_kien" rows="4" class="form-control" placeholder="Các vấn đề phát sinh cần ghi nhận"><?php echo $getField('su_kien'); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="cach_xu_ly" class="form-label">Cách xử lý</label>
                            <textarea name="cach_xu_ly" id="cach_xu_ly" rows="4" class="form-control" placeholder="Bạn đã xử lý hoặc đề xuất xử lý như thế nào"><?php echo $getField('cach_xu_ly'); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="phan_hoi" class="form-label">Phản hồi của khách hàng</label>
                            <textarea name="phan_hoi" id="phan_hoi" rows="4" class="form-control" placeholder="Những góp ý, cảm xúc hoặc nhận xét đáng chú ý"><?php echo $getField('phan_hoi'); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="anh_minh_hoa" class="form-label">Link ảnh / video minh họa</label>
                            <input type="text" name="anh_minh_hoa" id="anh_minh_hoa" class="form-control" value="<?php echo $getField('anh_minh_hoa'); ?>" placeholder="https://...">
                        </div>
                        <div class="col-12">
                            <label for="ghi_chu_them" class="form-label">Ghi chú thêm</label>
                            <textarea name="ghi_chu_them" id="ghi_chu_them" rows="3" class="form-control" placeholder="Thông tin bàn giao thêm cho điều hành, kế toán hoặc chăm sóc khách hàng"><?php echo $getField('ghi_chu_them'); ?></textarea>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?php echo $isEditing ? 'Cập nhật nhật ký' : 'Lưu nhật ký'; ?>
                            </button>
                            <?php if ($isEditing): ?>
                                <a href="index.php?act=hdv/nhatKyTour&tour_id=<?php echo (int)$entryEditing['tour_id']; ?>" class="btn btn-light">Hủy</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="hdv-card">
                <div class="hdv-card-header">
                    <div>
                        <h4 class="hdv-card-title">Diễn biến tour</h4>
                        <p class="hdv-card-subtitle">Lịch sử ghi chép gần nhất theo tour đang chọn</p>
                    </div>
                    <span class="hdv-soft-badge primary"><i class="bi bi-clock-history"></i> <?php echo $entriesCount; ?> bản ghi</span>
                </div>
                <div class="hdv-card-body">
                    <?php if (!empty($nhatKyList)): ?>
                        <div class="hdv-list">
                            <?php foreach ($nhatKyList as $item): ?>
                                <div class="hdv-list-item">
                                    <div class="hdv-list-head">
                                        <div>
                                            <h5 class="hdv-list-title"><?php echo htmlspecialchars($item['ten_tour'] ?? 'Tour'); ?></h5>
                                            <div class="hdv-list-meta mt-2">
                                                <span><i class="bi bi-calendar3"></i> <?php echo !empty($item['ngay_ghi']) ? date('d/m/Y', strtotime($item['ngay_ghi'])) : 'N/A'; ?></span>
                                            </div>
                                        </div>
                                        <a href="index.php?act=hdv/nhatKyTour&tour_id=<?php echo (int)$item['tour_id']; ?>&entry_id=<?php echo (int)$item['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-pencil-square"></i> Chỉnh sửa
                                        </a>
                                    </div>
                                    <p class="mb-0" style="white-space: pre-line;"><?php echo htmlspecialchars($item['noi_dung'] ?? ''); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="hdv-empty">
                            <i class="bi bi-journal-x"></i>
                            Chưa có nhật ký nào cho tour này.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="hdv-card">
            <div class="hdv-card-body">
                <div class="hdv-empty">
                    <i class="bi bi-map"></i>
                    Bạn chưa được phân công tour nào nên hiện chưa thể ghi nhật ký.
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>
